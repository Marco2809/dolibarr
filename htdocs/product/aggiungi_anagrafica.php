<?php

/**
 * Cliente Zoccali
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
require_once DOL_DOCUMENT_ROOT . '/product/assetmovement.php';
require_once DOL_DOCUMENT_ROOT . '/product/crea_pdf.php'; // serve per il ddt
require_once DOL_DOCUMENT_ROOT . '/product/crea_pdf_intervento.php'; // serve per il ddt
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myddt.php'; // serve per il ddt
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myScortaprodotto.php'; // serve per il ddt
require_once DOL_DOCUMENT_ROOT . '/product/log_movimentazione.php'; // serve per il ddt
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myClientiZoccali.php'; // serve per il ddt

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
$action_crea = $_REQUEST['action_crea'];

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
$user_id = $user->id; // conterrà l'user id
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
        $root = DOL_URL_ROOT;
        print '<div class="fiche">';

        print '<form action="#" method="POST">';
        print '<table class="border" width="100%">';
        // va fatto una query
        //$object->getCodFamily();
        $sql = "SELECT p.ref, p.label";
        $sql.= " FROM " . MAIN_DB_PREFIX . "product as p";
        $sql.= " WHERE p.fk_product_type=2";
        $res = $db->query($sql);
        $statutarray = array();
        if ($res)
        {
            $prods = array();
            while ($rec = $db->fetch_array($res))
            {
                $statutarray[] = $rec['ref'] . " - " . $rec['label'];
            }
        }
        print '<tr><td class="fieldrequired">' . "TERMID" . '</td>';
        print '<td><input name="termid" size="40" "></td></tr>';

        print '<tr><td class="fieldrequired">' . "Insegna" . '</td><td colspan="3"><input name="insegna" size="40" maxlength="255" value="' . $famiglia->label . '"></td></tr>';

        print '<tr><td valign="top">' . "Cod_sia" . '</td>';
        print '<td><input name="cod_sia" size="40" maxlength="255" "></tr>';

        print '<tr><td width="20%">' . "Indirizzo" . '</td>';
        print '<td><input name="indirizzo" size="40" maxlength="255" ">';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Citta" . '</td>';
        print '<td><input name="citta" size="40" maxlength="255" ">';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Prov" . '</td>';
        print '<td><input name="prov" size="40" maxlength="255" ">';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Tel." . '</td>';
        print '<td><input name="tel" size="40" maxlength="255" ">';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Nome rif." . '</td>';
        print '<td><input name="nome_rif" size="40" maxlength="255" ">';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Cap" . '</td>';
        print '<td><input name="cap" size="40" maxlength="255" ">';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Centro_S" . '</td>';
        print '<td><input name="centro_s" size="40" maxlength="255" ">';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Abipro" . '</td>';
        print '<td><input name="abipro" size="40" maxlength="255" ">';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Banca" . '</td>';
        print '<td><input name="banca" size="40" maxlength="255" ">';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Ragione sociale" . '</td>';
        print '<td><input name="rag_sociale" size="40" maxlength="255" ">';
        print '</td></tr>';

        print '</table>';
        print '<br>';
        print '<center> <input class="butAction" type="submit" name = "action_crea" value="Crea anagrafica"> </center>';
        //print '<center><input type="submit" class="button" name="action" value="' . $langs->trans("CreateAsset") . '"></center>';

        print '</form>';
    }

    if ($action_crea == "Crea anagrafica")
    {
        // insert into
        $termid = $_POST['termid'];
        $str_termid_err = '<div> Il campo <b>TERMID è vuoto</b></div>';
        $campo_non_inserito = msgErrore($termid, $str_termid_err);
        if ($campo_non_inserito)
        {
            $termid_true = checkTermid($termid, $db);
            if ($termid_true)
            {
                $str_msg_insegna = '<div> Il campo <b>Insegna è vuoto</b></div>';
                $insegna = $_POST['insegna'];
                $insegna_true = msgErrore($insegna, $str_msg_insegna);
                if ($insegna_true)
                {
                    $cod_sia = $_POST['cod_sia'];
                    $indirizzo = $_POST['indirizzo'];
                    $citta = $_POST['citta'];
                    $prov = $_POST['prov'];
                    $tel = $_POST['tel'];
                    $nome_rif = $_POST['nome_rif'];
                    $cap = $_POST['cap'];
                    $centro_s = $_POST['centro_s'];
                    $abipro = $_POST['abipro'];
                    $banca = $_POST['banca'];
                    $rag_sociale = $_POST['rag_sociale'];

                    $sql = "INSERT INTO " . MAIN_DB_PREFIX . "zoccali_interventi (";
                    $sql.= " TERMID";
                    $sql.= ", INSEGNA";
                    $sql.= ", COD_SIA";
                    $sql.= ", INDIRIZZO";
                    $sql.= ", CITTA";
                    $sql.= ", PROV";
                    $sql.= ", TEL";
                    $sql.= ", NOME_RIF";
                    $sql.= ",CAP";
                    $sql.= ", CENTRO_S";
                    $sql.= ",ABIPRO";
                    $sql.= ", BANCA";
                    $sql.= ", RAG_SOC";

                    $sql.= ") VALUES (";
                    $sql.= "'" . mysql_real_escape_string($termid) . "'";
                    $sql.= ", '" . mysql_real_escape_string($insegna) . "'";
                    $sql.= ", '" . mysql_real_escape_string($cod_sia) . "'";
                    $sql.= ", '" . mysql_real_escape_string($indirizzo) . "'";
                    $sql.= ", '" . mysql_real_escape_string($citta) . "'";
                    $sql.= ", '" . mysql_real_escape_string($prov) . "'";
                    $sql.= ", '" . mysql_real_escape_string($tel) . "'";
                    $sql.= ", '" . mysql_real_escape_string($nome_rif) . "'";
                    $sql.= ", '" . mysql_real_escape_string($cap) . "'";
                    $sql.= ", '" . mysql_real_escape_string($centro_s) . "'";
                    $sql.= ", '" . mysql_real_escape_string($abipro) . "'";
                    $sql.= ", '" . mysql_real_escape_string($banca) . "'";
                    $sql.= ", '" . mysql_real_escape_string($rag_sociale) . "'";
                    $sql.= ")";
                    $result = $db->query($sql);
                    if ($result)
                    {
                        $path = DOL_URL_ROOT . '/product/scheda_cliente.php?mainmenu=products&TERMID='.$termid;
                        print '<META HTTP-EQUIV="Refresh" CONTENT="0; url=' . $path . '">';
                    }
                    else{
                        $path = DOL_URL_ROOT . '/product/elenco_clienti.php?mainmenu=products';
                        print '<META HTTP-EQUIV="Refresh" CONTENT="0; url=' . $path . '">';
                    }
                }
            }
        }
    }
}

function msgErrore($campo, $str_msg)
{
    if (empty($campo))
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
        return false;
    }
    return true;
}

function checkTermid($termid, $db)
{
    // ESegui la query per verificare la dupplicazione della chiave
    $sql = "SELECT COUNT(*) as tot";
    $sql.= " FROM " . MAIN_DB_PREFIX . "zoccali_interventi as c";
    $sql.= " WHERE c.TERMID LIKE " . "'" . $termid . "'";
    $res = $db->query($sql);
    if ($res != FALSE)
    {
        $rec = $db->fetch_object($res);

        if ($rec->tot > 0)
        {
            print '<div class="jnotify-container">';
            print '<div class="jnotify-notification jnotify-notification-error">';
            print '<div class="jnotify-background"></div>';
            print '<a class="jnotify-close">×</a>';
            print '<div class="jnotify-message">';
            print '<div>';
            print 'Il campo ';
            print '<b>TERMID inserito</b>';
            print ' esiste già';
            print '</div>';
            print '</div>';
            print '</div>';
            print '</div>';
            return false;
        }
    }
    return true;
}
