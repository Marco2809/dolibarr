<?php

/* Copyright (C) 2001-2006 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2012-2013 Marcos García        <marcosgdf@gmail.com>
 * Copyright (C) 2013      Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2013      Raphaël Doursenaud   <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2013      Jean Heimburger   	<jean@tiaris.info>
 * Copyright (C) 2013      Cédric Salvador      <csalvador@gpcsolutions.fr>
 * Copyright (C) 2013      Florian Henry        <florian.henry@open-concept.pro>
 * Copyright (C) 2013      Adolfo segura        <adolfo.segura@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file       htdocs/product/liste.php
 *  \ingroup    produit
 *  \brief      Page to list products and services
 */
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.product.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myFamily.php';

if (!empty($conf->categorie->enabled))
    require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';

$langs->load("products");
$langs->load("stocks");
$langs->load("suppliers");

$action = GETPOST('action');
$sref = GETPOST("sref");
$sbarcode = GETPOST("sbarcode");
$snom = GETPOST("snom");
$sall = GETPOST("sall");
$type = GETPOST("type", "int");
$search_sale = GETPOST("search_sale");
$search_categ = GETPOST("search_categ", 'int');
$tosell = GETPOST("tosell");
$tobuy = GETPOST("tobuy");
$fourn_id = GETPOST("fourn_id", 'int');
$catid = GETPOST('catid', 'int');

$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');
$page = GETPOST("page", 'int');
if ($page == -1) {
    $page = 0;
}
$offset = $conf->liste_limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (!$sortfield)
    $sortfield = "p.ref";
if (!$sortorder)
    $sortorder = "ASC";

$limit = $conf->liste_limit;


// Get object canvas (By default, this is not defined, so standard usage of dolibarr)
$canvas = GETPOST("canvas");
$objcanvas = '';
if (!empty($canvas)) {
    require_once DOL_DOCUMENT_ROOT . '/core/class/canvas.class.php';
    $objcanvas = new Canvas($db, $action);
    $objcanvas->getCanvas('product', 'list', $canvas);
}

// Security check
if ($type == '0')
    $result = restrictedArea($user, 'produit', '', '', '', '', '', $objcanvas);
else if ($type == '1')
    $result = restrictedArea($user, 'service', '', '', '', '', '', $objcanvas);
else
    $result = restrictedArea($user, 'produit|service', '', '', '', '', '', $objcanvas);


/*
 * Actions
 */

if (isset($_POST["button_removefilter_x"])) {
    $sref = "";
    $sbarcode = "";
    $snom = "";
    $search_categ = 0;
}


/*
 * View
 */

$htmlother = new FormOther($db);
$form = new Form($db);

if (is_object($objcanvas) && $objcanvas->displayCanvasExists($action)) {
    $objcanvas->assign_values('list');       // This must contains code to load data (must call LoadListDatas($limit, $offset, $sortfield, $sortorder))
    $objcanvas->display_canvas('list');    // This is code to show template
} else {
    $title = $langs->trans("ProductsAndServices");

    if (isset($type)) {
        if ($type == 1) {
            $texte = $langs->trans("Services");
        }
        if ($type == 2) {
            $texte = "Famiglia";
        } else {
            $texte = $langs->trans("Products");
        }
    } else {
        $texte = $langs->trans("ProductsAndServices");
    }

    $sql = 'SELECT DISTINCT p.rowid, p.ref, p.label, p.barcode, p.price, p.price_ttc, p.price_base_type,';
    $sql.= ' p.fk_product_type, p.tms as datem,';
    $sql.= ' p.duration, p.tosell, p.tobuy, p.seuil_stock_alerte,';
    $sql.= ' MIN(pfp.unitprice) as minsellprice';
    $sql .= ', p.desiredstock';
    $sql.= ' FROM ' . MAIN_DB_PREFIX . 'product as p';
    if (!empty($search_categ) || !empty($catid))
        $sql.= ' LEFT JOIN ' . MAIN_DB_PREFIX . "categorie_product as cp ON p.rowid = cp.fk_product"; // We'll need this table joined to the select in order to filter by categ
    $sql.= " LEFT JOIN " . MAIN_DB_PREFIX . "product_fournisseur_price as pfp ON p.rowid = pfp.fk_product";
    // multilang
    if ($conf->global->MAIN_MULTILANGS) { // si l'option est active
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "product_lang as pl ON pl.fk_product = p.rowid AND pl.lang = '" . $langs->getDefaultLang() . "'";
    }
    $sql.= ' WHERE p.entity IN (' . getEntity('product', 1) . ')';
    if ($sall) {
        // For natural search
        $params = array('p.ref', 'p.label', 'p.description', 'p.note');
        // multilang
        if ($conf->global->MAIN_MULTILANGS) { // si l'option est active
            $params[] = 'pl.description';
            $params[] = 'pl.note';
        }
        if (!empty($conf->barcode->enabled)) {
            $params[] = 'p.barcode';
        }
        $sql .= natural_search($params, $sall);
    }
    // if the type is not 1, we show all products (type = 0,2,3)
    if (dol_strlen($type)) {
        if ($type == 1)
            $sql.= " AND p.fk_product_type = '1'";
        else
            $sql.= " AND p.fk_product_type <> '1'";
    }
    if ($sref)
        $sql .= natural_search('p.ref', $sref);
    if ($sbarcode)
        $sql .= natural_search('p.barcode', $sbarcode);
    if ($snom) {
        $params = array('p.label');
        // multilang
        if ($conf->global->MAIN_MULTILANGS) { // si l'option est active
            $params[] = 'pl.label';
        }
        $sql .= natural_search($params, $snom);
    }
    if (isset($tosell) && dol_strlen($tosell) > 0 && $tosell != -1)
        $sql.= " AND p.tosell = " . $db->escape($tosell);
    if (isset($tobuy) && dol_strlen($tobuy) > 0 && $tobuy != -1)
        $sql.= " AND p.tobuy = " . $db->escape($tobuy);
    if (dol_strlen($canvas) > 0)
        $sql.= " AND p.canvas = '" . $db->escape($canvas) . "'";
    if ($catid > 0)
        $sql.= " AND cp.fk_categorie = " . $catid;
    if ($catid == -2)
        $sql.= " AND cp.fk_categorie IS NULL";
    if ($search_categ > 0)
        $sql.= " AND cp.fk_categorie = " . $search_categ;
    if ($search_categ == -2)
        $sql.= " AND cp.fk_categorie IS NULL";
    if ($fourn_id > 0)
        $sql.= " AND pfp.fk_soc = " . $fourn_id;
    $sql.= " GROUP BY p.rowid, p.ref, p.label, p.barcode, p.price, p.price_ttc, p.price_base_type,";
    $sql.= " p.fk_product_type, p.tms,";
    $sql.= " p.duration, p.tosell, p.tobuy, p.seuil_stock_alerte";
    $sql .= ', p.desiredstock';
    //if (GETPOST("toolowstock")) $sql.= " HAVING SUM(s.reel) < p.seuil_stock_alerte";    // Not used yet

    $nbtotalofrecords = 0;
    if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
        $result = $db->query($sql);
        $nbtotalofrecords = $db->num_rows($result);
    }

    $sql.= $db->order($sortfield, $sortorder);
    $sql.= $db->plimit($limit + 1, $offset);

    dol_syslog("product:list.php: sql=" . $sql);
    $resql = $db->query($sql);
    if ($resql) {
        $num = $db->num_rows($resql);

        $i = 0;

        if ($num == 1 && ($sall || $snom || $sref || $sbarcode) && $action != 'list') {
            $objp = $db->fetch_object($resql);
            header("Location: fiche.php?id=" . $objp->rowid);
            exit;
        }

        $helpurl = '';
        if (isset($type)) {
            if ($type == 0) {
                $helpurl = 'EN:Module_Products|FR:Module_Produits|ES:M&oacute;dulo_Productos';
            } else if ($type == 1) {
                $helpurl = 'EN:Module_Services_En|FR:Module_Services|ES:M&oacute;dulo_Servicios';
            }
        }

        llxHeader('', $title, $helpurl, '');
        $code_famiglia = $_GET['id'];
        $famiglia = new family($db);
        $obj_family = $famiglia->getRowFamily($code_famiglia);
        if ($action == "modifica") { // riceve dalla post i dati della famiglia 
            $codice_famiglia = $etichetta = $_GET['id'];

            $etichetta = "'" . $_POST['libelle'] . "'";
            $desc = "'" . $_POST['desc'] . "'";
            $nature = $_POST['finished']; // natura
            $peso = $_POST['weight'];
            $lunghezza = $_POST['size'];
            $superficie = $_POST['surface'];
            $volume = $_POST['volume'];
            $nota = "'" . $_POST['note'] . "'";
            $marca = "'" . $_POST['brand'] . "'";
            $modello = "'" . $_POST['model'] . "'";
            $codice_produttore = "'" . $_POST['customcode'] . "'";

            $sql = "UPDATE " . MAIN_DB_PREFIX . "product as f SET ";
            $sql .= " f.label = " . $etichetta . "," . " f.description = " . $desc . ",";
            $sql .= " f.finished = " . $nature . "," . " f.weight = " . $peso . ",";
            $sql .= " f.length = " . $lunghezza . "," . " f.surface = " . $superficie . ",";
            $sql .= " f.volume = " . $volume . "," . " f.note = " . $nota . ",";
            $sql .= " f.brand = " . $marca . "," . " f.model = " . $modello . ",";
            $sql .= " f.customcode = " . $codice_produttore . "," . " f.tms = CURRENT_TIMESTAMP()"; // data della modifica

            $sql .= " WHERE f.ref LIKE " . "'" . $codice_famiglia . "'";
            $result = $db->query($sql); // aggiornato;
            $path = DOL_URL_ROOT . '/product/scheda_famiglia.php?mainmenu=products&id=' . $codice_famiglia;
            print '<META HTTP-EQUIV="Refresh" CONTENT="0; url=' . $path . '">';
        }


        print '<form action="#" METHOD="POST">';
        print '<table class="border" width="100%">';
        print '<tbody>';
        print '<tr>';
        print '<td class="fieldrequired" width="20%">Cod.famiglia</td>';
        print '<td colspan="3">';
        print '<input value="' . $code_famiglia . '" maxlength="6" size="20" name="ref" disabled="disabled">';
        print '</td>';
        print '</tr>';

        print '<tr>';
        print '<td class="fieldrequired">Etichetta</td>';
        print '<td colspan="3">';
        print '<input value="' . $obj_family->label . '" maxlength="255" size="40" name="libelle">';
        print '</td>';
        print '</tr>';

        print '<tr>';
        print '<td valign="top">Descrizione</td>';
        print '<td colspan="3">';
        print '<textarea id="desc" class="flat" cols="80" rows="4" name="desc">' . $obj_family->description . '</textarea>';
        print '</td>';
        print '</tr>';

        print '<tr>';
        print '<td>Codice produttore</td>';
        print '<td>';
        print '<input value="' . $obj_family->customcode . '" size="16" name="customcode">';
        print '</td>';
        print '</tr>';

        print '<tr>'; //natura
        print '<td>Natura</td>';
        print '<td colspan="3">';
        print '<select id="finished" class="flat" name="finished">';
        print '<option value="-1"> </option>';
        print '<option value="1">Prodotto creato</option>';
        print '<option value="0">Materia prima</option>';
        print '</select>';
        print '</td>';
        print '</tr>';

        print '<tr>'; //peso
        print '<td>Peso</td>';
        print '<td colspan="3">';
        print '<input value="' . $obj_family->weight . '" size="4" name="weight">';
        print '<select class="flat" name="weight_units">';
        print '<option value="-6">mg</option>';
        print '<option value="-3">g</option>';
        print '<option selected="selected" value="0">kg</option>';
        print '<option value="3">t</option>';
        print '<option value="99">pound</option>';
        print '</select>';
        print '</td>';
        print '</tr>';

        print '<tr>'; // lunghezza
        print '<td>Lunghezza</td>';
        print '<td colspan="3">';
        print '<input value="' . $obj_family->length . '" size="4" name="size">';
        print '<select class="flat" name="size_units">';
        print '<option value="-3">mm</option>';
        print '<option value="-2">cm</option>';
        print '<option value="-1">dm</option>';
        print '<option selected="selected" value="0">m</option>';
        print '<option value="98">piede</option>';
        print '<option value="99">pollice</option>';
        print '</select>';
        print '</td>';
        print '</tr>';

        print '<tr>'; // superficie
        print '<td>Superficie</td>';
        print '<td colspan="3">';
        print '<input value="' . $obj_family->surface . '" size="4" name="surface">';
        print '<select class="flat" name="surface_units">';
        print '<option value="-6">mm2</option>';
        print '<option value="-4">cm2</option>';
        print '<option value="-2">dm2</option>';
        print '<option selected="selected" value="0">m2</option>';
        print '<option value="98">ft2</option>';
        print '<option value="99">in2</option>';
        print '</select>';
        print '</td>';
        print '</tr>';

        print '<tr>';
        print '<td>Volume</td>';
        print '<td colspan="3">';
        print '<input value="' . $obj_family->volume . '" size="4" name="volume">';
        print '<select class="flat" name="volume_units">';
        print '<option value="-9">mm3</option>';
        print '<option value="-6">cm3</option>';
        print '<option value="-3">dm3</option>';
        print '<option selected="selected" value="0">m3</option>';
        print '<option value="88">ft3</option>';
        print '<option value="89">in3</option>';
        print '<option value="97">oncia</option>';
        print '<option value="99">gallone</option>';
        print '</select>';
        print '</td>';
        print '</tr>';

        print '<tr>';
        print '<td valign="top">Nota (non visibile su fatture, proposte ...)</td>';
        print '<td colspan="3">';
        print '<textarea id="note" class="flat" cols="70" rows="8" name="note">' . $obj_family->note . '</textarea>';
        print '</td>';
        print '</tr>';

        //marca
        print '<tr>';
        print '<td>Marca</td>';
        print '<td>';
        print '<input value="' . $obj_family->brand . '" size="16" name="brand">';
        print '</td>';
        print '</tr>';

        print '<tr>';
        print '<td width="20%">Modello</td>';
        print '<td>';
        print '<input value="' . $obj_family->model . '" size="16" name="model">';
        print '</td>';
        print '</tr>';


        print '</tbody>';
        print '</table>';
        print '<br><br>';


        print '<div class="inline-block divButAction">';
        print '<input class="butAction" type="submit" name = "action" value="modifica">';
        print '</div>';
        $root_dol = DOL_URL_ROOT;
        print '<div class="inline-block divButAction">';
        print '<a class="butAction" href="' . $root_dol . '/product/scheda_famiglia.php?mainmenu=products&id=' . $code_famiglia . '"' . '>Annulla</a>';
        print '</div>';

        print '</form>';
    }
}