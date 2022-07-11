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
require_once DOL_DOCUMENT_ROOT . '/product/stock/class/entrepot.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.product.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myMagazzino.php';
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myAsset.php';
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
$cod_asset = $_REQUEST['cod_asset'];

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


    $texte = "Movimentazione asset";


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
        //inizio scrittura menu movimentazione
        print '<div class="fiche">';
        print '<div class="tabs" data-type="horizontal" data-role="controlgroup">';
        print '<a class="tabTitle">
<img border="0" title="" alt="" src="/dolibarr/htdocs/theme/eldy/img/object_product.png">
Movimentazione asset
</a>';
        print '<div class="inline-block tabsElem">
<a id="card" class="tabactive tab inline-block" href="/dolibarr/htdocs/product/movimentazione.php?leftmenu=product&type=5" data-role="button">Nuova movimentazione</a>
</div>';



        print '<div class="inline-block tabsElem">
<a id="price" class="tab inline-block" href="/dolibarr/htdocs/product/daconvalidare.php?leftmenu=product&type=6&id=3" data-role="button">Da convalidare</a>
</div>';

        print '<div class="inline-block tabsElem">
<a id="price" class="tab inline-block" href="/dolibarr/htdocs/product/movimentazione.php?leftmenu=product&type=6&id=4" data-role="button">Storico</a>
</div>';


        print '</div>';
        /*         * * tolbarr * */

        

            $id = $_GET['id'];
            if ($id == 3) { // lista da attivare
                print '<br><table class="noborder" width="100%">';

                print '<tr class="liste_titre"><td width="20%" colspan="4">' . "Seleziona" . '</td>';
                print '<td align="right">' . "Magazzino Sorgente" . '</td>';
                print '<td align="right">' . "Magazzino destinatario" . '</td>';
                print '<td align="right">' . "Elenco asset" . '</td>';

                print '</tr>';
                print '<tr ' . true . '>';

                $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "form_assetmove WHERE flag LIKE '%0%'";
                $result = $db->query($sql);
                if ($result) {
                    $array_damovimentare = array();
                    while ($array_assetMove = $result->fetch_array(MYSQLI_ASSOC)) {
                        $array_damovimentare[] = $array_assetMove;
                    }
                    $html = '<form action = "/dolibarr/htdocs/product/movimentazione.php?leftmenu=product&type=6&id=3">';
                    $n = count($array_damovimentare);
                    for ($i = 0; $i < $n; $i++) {
                        $html .="<tr>";
                        $convalida_record = $array_damovimentare[$i];
                        $id_movimentazione = $convalida_record['id'];
                        $riemi_checkbox = '<input type="checkbox" name="movimentazione[]" value=' . '"' . $id_movimentazione . '"' . '> <br>';
                        $code_asset_decode = json_encode($convalida_record['checkbox_asset']);
                        $html .= '<td colspan="4">' . $riemi_checkbox . '</td>' . '<td align="right">' . $convalida_record['mag_sorgente'] . '</td>' . '<td align="right">' . $convalida_record['mag_dest'] . '</td>' . '<td align="right">' . $code_asset_decode . '</td>';
                        $html .= "</tr>";
                    }
                    $html .= "<table>";
                    $html .= "<br>";
                    $html .= '<script>
                            $(function() {
                              $( "#datepicker" ).datepicker();
                            });
                            </script>';
                    $html .= 'Inserisci la data per convalidare <input type="text" name="data_convalida" id="datepicker"></td>';

                    $html .= '<input type="submit" class="button" name="action"  value="' . "Convalida" . '">';
                    $html .= '<br><br>Elimina la movimentazione<input type="submit" class="button" name="action"  value="' . "Elimina" . '">';
                    $html .= '<br><br>';

                    $html .='<table class = "liste formdoc noborder" width = "100%" summary = "listofdocumentstable">
                    <tbody>
                    <tr class = "liste_titre">
                    <th class = "formdoc liste_titre" align = "center">
                    <span class = "hideonsmartphone">Modello </span>
                    <select id = "model" class = "flat" name = "model">
                    <option selected = "selected" value = "baleine">DDT</option>
                    </select>
                    </th>
                    <th class = "formdoc liste_titre" align = "center"> </th>
                    <th class = "formdocbutton liste_titre" align = "center" colspan = "2">
                    <input id = "builddoc_generatebutton" class = "button" type = "submit" value = "Genera" name = "action">
                    </th>
                    </tr>
                    <tr class = "impair">
                    </tbody>
                    </table >';

                    $html .= "</form>";
                    print $html;
                    if ($action == "Genera") {
                        $html_ddt = '<table id = "builddoc_generatebutton">';
                        $html_ddt .= "<tr>";
                        $html_ddt .= "<td>";
                        $html_ddt .= "ciao";
                        $html_ddt .= "</td>";
                        $html_ddt .= "</tr>";
                        $html_ddt = '<table>';
                        print $html_ddt;
                    }
                }
            
        }
        if ($action == "Convalida") {
            // occorre aggiornare lo stato degli asset
            // impostare il flag a 1
        }

        if ($action == "Elimina") {
            // occorre aggiornare lo stato degli asset
            // impostare il flag a 1
            $query = "DELETE FROM" . MAIN_DB_PREFIX . "form_assetmove";
            $mov_selezionati = $_GET['movimentazione'];
            $query .= "WHERE = " . $mov_selezionati[0];
            $res = $db->query($query);

            $query = "DELETE FROM" . MAIN_DB_PREFIX . "stock_mouvement";
            $query .= "WHERE = " . $mov_selezionati[0];
            $res = $db->query($query);
        }



        /*
          print '<div class="tabBar">';
          print '<table class="border" width="100%">';
          print '<tbody>';
          print '<tr>
          <td width="15%">Rif.</td>';
         */
    }
}