
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
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myFamily.php';
require_once DOL_DOCUMENT_ROOT . '/product/assetmovement.php';
require_once DOL_DOCUMENT_ROOT . '/product/crea_pdf.php'; // serve per il ddt
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myddt.php'; // serve per il ddt
require_once DOL_DOCUMENT_ROOT . '/product/log_movimentazione.php'; // serve per il ddt
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myTracking.php'; // classe che esegue il tracking

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
if ($page == -1)
{
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
if (!empty($canvas))
{
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

if (isset($_POST["button_removefilter_x"]))
{
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

if (is_object($objcanvas) && $objcanvas->displayCanvasExists($action))
{
    $objcanvas->assign_values('list');       // This must contains code to load data (must call LoadListDatas($limit, $offset, $sortfield, $sortorder))
    $objcanvas->display_canvas('list');    // This is code to show template
} else
{
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
    if ($conf->global->MAIN_MULTILANGS)
    { // si l'option est active
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "product_lang as pl ON pl.fk_product = p.rowid AND pl.lang = '" . $langs->getDefaultLang() . "'";
    }
    $sql.= ' WHERE p.entity IN (' . getEntity('product', 1) . ')';
    if ($sall)
    {
// For natural search
        $params = array('p.ref', 'p.label', 'p.description', 'p.note');
// multilang
        if ($conf->global->MAIN_MULTILANGS)
        { // si l'option est active
            $params[] = 'pl.description';
            $params[] = 'pl.note';
        }
        if (!empty($conf->barcode->enabled))
        {
            $params[] = 'p.barcode';
        }
        $sql .= natural_search($params, $sall);
    }
// if the type is not 1, we show all products (type = 0,2,3)
    if (dol_strlen($type))
    {
        if ($type == 1)
            $sql.= " AND p.fk_product_type = '1'";
        else
            $sql.= " AND p.fk_product_type <> '1'";
    }
    if ($sref)
        $sql .= natural_search('p.ref', $sref);
    if ($sbarcode)
        $sql .= natural_search('p.barcode', $sbarcode);
    if ($snom)
    {
        $params = array('p.label');
// multilang
        if ($conf->global->MAIN_MULTILANGS)
        { // si l'option est active
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
    if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST))
    {
        $result = $db->query($sql);
        $nbtotalofrecords = $db->num_rows($result);
    }

    $sql.= $db->order($sortfield, $sortorder);
    $sql.= $db->plimit($limit + 1, $offset);

    dol_syslog("product:list.php: sql=" . $sql);
    $resql = $db->query($sql);
    if ($resql)
    {
        $num = $db->num_rows($resql);

        $i = 0;

        if ($num == 1 && ($sall || $snom || $sref || $sbarcode) && $action != 'list')
        {
            $objp = $db->fetch_object($resql);
            header("Location: fiche.php?id=" . $objp->rowid);
            exit;
        }

        $helpurl = '';
        if (isset($type))
        {
            if ($type == 0)
            {
                $helpurl = 'EN:Module_Products|FR:Module_Produits|ES:M&oacute;dulo_Productos';
            } else if ($type == 1)
            {
                $helpurl = 'EN:Module_Services_En|FR:Module_Services|ES:M&oacute;dulo_Servicios';
            }
        }

        llxHeader('', $title, $helpurl, '');
        $code_asset = $_GET['id'];
        $salva_button = $_POST['salva'];

        $root_path = DOL_URL_ROOT;
        $link = "' . $root_path . '/product/edit_asset.php?mainmenu=products&action=salva&id=" . '"' . $code_asset . '"';
        $sql = "SELECT  * FROM  " . MAIN_DB_PREFIX . "zoccali_interventi a ";
        $sql .= " WHERE a.TERMID LIKE '" . $code_asset . "'";
        $result = $db->query($sql);

        if ($result)
        {
            $asset = $db->fetch_array(MYSQLI_ASSOC);
// $form = '<form action='.'"'.$link.'"'.'>';
//  print $form;
            print '<form action="#" METHOD="POST">';
//print '<form action='.$link.'>';
            print '<table class="border" width="100%">';
            print '<tbody>';

            print '<tr>';
            print '<td class="fieldrequired">TERMID</td>';
            print '<td colspan="3">';
            print '<input value="' . $code_asset . '" maxlength="255" size="40" name="TERMID" disabled="disabled">';
            print '</td>';

//print '<td>' . "ciao\nprova\nprova" . '</td>';
            print '</tr>';

            print '<tr>';
            print '<td class="fieldrequired">INSEGNA</td>';
            print '<td colspan="3">';
            print '<input value="' . $asset['INSEGNA'] . '" maxlength="255" size="40" name="label" disabled="disabled">';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td width="20%">Cod_sia </td>';
            print '<td>';
            print '<input type="text" value="' . $asset['COD_SIA'] . '" size="40" maxlength="255" name="COD_SIA" readonly>';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td width="20%">Indirizzo </td>';
            print '<td>';
            print '<input type="text" value="' . $asset['INDIRIZZO'] . '" size="40" maxlength="255" name="INDIRIZZO" >';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td width="20%">CITTA </td>';
            print '<td>';
            print '<input type="text" value="' . $asset['CITTA'] . '" size="40" maxlength="255" name="CITTA" >';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td width="20%">Provincia </td>';
            print '<td>';
            print '<input type="text" value="' . $asset['PROV'] . '" size="40" maxlength="255" name="PROV" >';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td width="20%">Telefono </td>';
            print '<td>';
            print '<input type="text" value="' . $asset['TEL'] . '" size="40" maxlength="255" name="TEL">';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td width="20%">Nome rif. </td>';
            print '<td>';
            print '<input type="text" value="' . $asset['NOME_RIF'] . '" size="40" maxlength="255" name="NOME_RIF">';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td width="20%">Cap </td>';
            print '<td>';
            print '<input type="text" value="' . $asset['CAP'] . '" size="40" maxlength="255" name="CAP" >';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td width="20%">Centro_S </td>';
            print '<td>';
            print '<input type="text" value="' . $asset['CENTRO_S'] . '" size="40" maxlength="255" name="CENTRO_S" >';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td width="20%">Abipro </td>';
            print '<td>';
            print '<input type="text" value="' . $asset['ABIPRO'] . '" size="40" maxlength="255" name="ABIPRO">';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td width="20%">Banca </td>';
            print '<td>';
            print '<input type="text" value="' . $asset['BANCA'] . '" size="40" maxlength="255" name="BANCA">';
            print '</td>';
            print '</tr>';
            
            print '<tr>';
            print '<td width="20%">Ragione sociale </td>';
            print '<td>';
            print '<input type="text" value="' . $asset['RAG_SOC'] . '" size="40" maxlength="255" name="RAG_SOC">';
            print '</td>';
            print '</tr>';


            print '</tbody>';
            print '</table>';

            $root = DOL_URL_ROOT;
            print '<div class="tabsAction">';
            print '<center>';
            print '<div class="inline-block divButAction">';
//print '<a class="butAction" href="/dolibarr/htdocs/product/edit_asset.php?action=salva&id=' .$code_asset .'"'. '>Salva</a>';
            print '<a class="butAction" href="' . $root . '/product/scheda_cliente.php?mainmenu=products&TERMID=' . $code_asset . '"' . '>Annulla</a>';
            print '<input class="butAction" type="submit" name = "salva" value="salva">';

            print '</center>';
            print '</div>';
            print '</form>';
        }

        if ($salva_button == "salva")
        {
            $TERMID = (isset($_POST['TERMID'])) ? $_POST['TERMID'] : $code_asset;
// verifico se è da incrementare
            $cod_sia = (isset($_POST['COD_SIA'])) ? "'" . $_POST['COD_SIA'] . "'" : null;
            $indirizzo = (isset($_POST['INDIRIZZO'])) ? "'" . $_POST['INDIRIZZO'] . "'" : null;
            $citta = (isset($_POST['CITTA'])) ? "'" . $_POST['CITTA'] . "'" : null;
            $prov = (isset($_POST['PROV'])) ? "'" . $_POST['PROV'] . "'" : null;
            $tel = (isset($_POST['TEL'])) ? "'" . $_POST['TEL'] . "'" : null;
            $nome_rif = (isset($_POST['NOME_RIF'])) ? "'" . $_POST['NOME_RIF'] . "'" : null;
            $cap = (isset($_POST['CAP'])) ? "'" . $_POST['CAP'] . "'" : null;
            $centro_s = (isset($_POST['CENTRO_S'])) ? "'" . $_POST['CENTRO_S'] . "'" : null;
            $abipro = (isset($_POST['ABIPRO'])) ? "'" . $_POST['ABIPRO'] . "'" : null;
            $banca = (isset($_POST['BANCA'])) ? "'" . $_POST['BANCA'] . "'" : null;
            $rag_soc = (isset($_POST['RAG_SOC'])) ? "'" . $_POST['RAG_SOC'] . "'" : null;


            $sql = "UPDATE " . MAIN_DB_PREFIX . "zoccali_interventi as a SET ";
            $sql .= " a.COD_SIA = " . $cod_sia . ",";
            $sql .= " a.INDIRIZZO = " . $indirizzo . ",";
            $sql .= " a.CITTA = " . $citta . ",";
            $sql .= " a.PROV = " . $prov . ",";
            $sql .= " a.TEL = " . $tel . ",";
            $sql .= " a.NOME_RIF = " . $nome_rif . ",";
            $sql .= " a.CAP = " . $cap . ",";
            $sql .= " a.CENTRO_S = " . $centro_s . ",";
            $sql .= " a.ABIPRO = " . $abipro . ",";
            $sql .= " a.BANCA = " . $banca . ",";
            $sql .= " a.RAG_SOC = " . $rag_soc;
            $sql .= "  WHERE a.TERMID LIKE '".$code_asset. "'";
            $result = $db->query($sql);


            $path = DOL_URL_ROOT . '/product/scheda_cliente.php?mainmenu=products&TERMID=' . $TERMID;
            print '<META HTTP-EQUIV="Refresh" CONTENT="0; url=' . $path . '">';
        }
    }
}

function msgErrore($str_msg)
{
    print '<div class="jnotify-container">';
    print '<div class="jnotify-notification jnotify-notification-error">';
    print '<div class="jnotify-background"></div>';
    print '<a class="jnotify-close">×</a>';
    print '<div class="jnotify-message">';
    print $str_msg;
    print '</div>';
    print '</div>';
    print '</div>';
}
