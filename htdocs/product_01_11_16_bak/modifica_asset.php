<?php


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


    $texte = "Modifica asset";


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
        // mie modifiche
        if (GETPOST('delprod'))
            dol_htmloutput_mesg($langs->trans("ProductDeleted", GETPOST('delprod')));

        $param = "&amp;sref=" . $sref . ($sbarcode ? "&amp;sbarcode=" . $sbarcode : "") . "&amp;snom=" . $snom . "&amp;sall=" . $sall . "&amp;tosell=" . $tosell . "&amp;tobuy=" . $tobuy;
        $param.=($fourn_id ? "&amp;fourn_id=" . $fourn_id : "");
        $param.=($search_categ ? "&amp;search_categ=" . $search_categ : "");
        $param.=isset($type) ? "&amp;type=" . $type : "";

        print_barre_liste($texte, $page, "liste.php", $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords);

        if ($action == "Modifica") {
            $cod_asset = $etichetta = (isset($_POST['cod_asset'])) ? $_POST['cod_asset']  : null;
            $etichetta = (isset($_POST['desc'])) ? "'" . $_POST['desc'] . "'" : null;
            $stato_fisico = $_POST['stato_fisico'];
            $stato_tecnico = $_POST['stato_tecnico'];
            $descrizione = (isset($_POST['desc'])) ? "'" . $_POST['desc'] . "'" : null;
            $corridoio = (isset($_POST['corridoio'])) ? "'" . $_POST['corridoio'] . "'" : null;
            $scaffali = (isset($_POST['scaffali'])) ? "'" . $_POST['scaffali'] . "'" : null;
            $ripiano = (isset($_POST['ripiano'])) ? "'" . $_POST['ripiano'] . "'" : null;
            $note = (isset($_POST['note'])) ? "'" . $_POST['note'] . "'" : null;
            $data_modifica = date("d-m-y");

            $sql = "UPDATE " . MAIN_DB_PREFIX . "asset as a SET ";
            $sql .= " a.label = " . $etichetta . "," . " a.stato_fisico = " . $stato_fisico . ",";
            $sql .= " a.stato_tecnico = " . $stato_tecnico . "," . " a.descrizione = " . $descrizione . ",";
            $sql .= " a.corridoio = " . $corridoio . "," . " a.scaffali = " . $scaffali . ",";
            $sql .= " a.ripiano = " . $ripiano . "," . " a.note = " . $note . ",";
            $sql .= " a.data_modifica = " ."'" . $data_modifica ."'";

            $sql .= " WHERE a.cod_asset= " . "'" . $cod_asset . "'";
            $result = $db->query($sql);
        }

        $sql = "SELECT  * ";
        $sql.= " FROM " . MAIN_DB_PREFIX . "asset a";
        $sql.= " WHERE cod_asset = " . "'" . $cod_asset . "'";
        $res = $db->query($sql);
        $rec = $db->fetch_object($rec);
        $redir = DOL_URL_ROOT . '/product/elenco_asset.php';

        print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
        print '<table class="border" width="100%">';

        print '<tr><td width="20%">' . "Codice asset" . '</td>'; // non modificabile
        print '<td><input name="cod_asset" size="16" value="' . $rec->cod_asset . '"readonly>';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Codice famiglia" . '</td>'; // non modificabile
        print '<td><input name="cod_famiglia" size="16" value="' . $rec->cod_famiglia . '"readonly>';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Etichetta" . '</td>'; // non modificabile
        print '<td><input name="label" size="25" value="' . $rec->label . '">';
        print '</td></tr>';


        print '<tr><td class="fieldrequired">' . "Stato Fisico" . '</td><td colspan="3">';
        $statutarray = array('1' => "Giacenza", '2' => "In uso", '3' => "In transito", '4' => "In lab", "5" => "Dismesso");
        print $form->selectarray('stato_fisico', $statutarray, GETPOST('stato_fisico'));
        print '</td></tr>';

        print '<tr><td class="fieldrequired">' . "Stato Tecnico" . '</td><td colspan="3">';
        $statutarray = array('1' => "Nuovo", '2' => "Ricondizionato", '3' => "Guasto", '4' => "Sconosciuto");
        print $form->selectarray('stato_tecnico', $statutarray, GETPOST('stato_tecnico'));
        print '</td></tr>';

        print '<tr><td valign="top">' . "Descrizione" . '</td><td colspan="3">';
        $descrizione = empty($rec->descrizione) ? "" : $rec->descrizione;
        print '<textarea id="desc" class="flat" cols="80" rows="4" name="desc">' . $descrizione . '</textarea>';

        print '<tr><td width="20%">' . "Proprietario" . '</td>'; // non modificabile
        print '<td><input name="proprietario" size="16" value="' . $rec->proprietario . '"readonly>';

        print '</td></tr>';

        print '<tr><td width="20%">' . "Corridoio" . '</td>';
        print '<td><input name="corridoio" size="16" value="' . $rec->corridoio . '">';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Scaffali" . '</td>';
        print '<td><input name="scaffali" size="16" value="' . $rec->scaffali . '">';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Ripiano" . '</td>';
        print '<td><input name="ripiano" size="16" value="' . $rec->ripiano . '">';
        print '</td></tr>';

        print '<tr><td>' . "Marca" . '</td>';
        print '<td><input name="brand" size="16" value="' . $rec->brand . '"readonly>';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Modello" . '</td>';
        print '<td><input name="model" size="16" value="' . $rec->model . '"readonly>';
        print '</td></tr>';

        print '<tr><td valign="top">' . "Nota" . '</td><td colspan="3">';
        $nota = empty($rec->note) ? "" : $rec->note;
        print '<textarea id="desc" class="flat" cols="80" rows="4" name="note">' . $nota . '</textarea>';
        print "</td></tr>";

        print '</table>';
        print '<br>';
        print ' <center> <input type="submit" class="button" name="action" value="' . "Modifica" . '">';
        print '<input type="submit" class="button" name="action" value="' . "Annulla" . '"></center> ';

        print '</form>';
    }
}
