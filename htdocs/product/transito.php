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
require_once DOL_DOCUMENT_ROOT . '/product/assetmovement.php';
require_once DOL_DOCUMENT_ROOT . '/product/crea_pdf.php'; // serve per il ddt
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myddt.php'; // serve per il ddt
require_once DOL_DOCUMENT_ROOT . '/product/log_movimentazione.php'; // serve per il ddt


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
        $root = DOL_URL_ROOT;
        print '<div class="fiche">';
        print '<div class="tabs" data-type="horizontal" data-role="controlgroup">';
        print '<a class="tabTitle">
<img border="0" title="" alt="" src="' . $root . '"/theme/eldy/img/object_product.png">
Movimentazione asset
</a>';
        $root = DOL_URL_ROOT;
        print '<div class="inline-block tabsElem">
<a id="card" class="tab inline-block" href="' . $root . '/product/movimentazione.php?mainmenu=products&leftmenu=product&type=5" data-role="button">Nuova movimentazione</a>
</div>';
        
          print '<div class="inline-block tabsElem">
<a id="price" class="tabactive tab inline-block" href="' . $root . '/product/transito.php?mainmenu=products&leftmenu=product&type=6&id=3" data-role="button">In transito</a>
</div>';

        print '<div class="inline-block tabsElem">
<a id="price" class="tab inline-block" href="' . $root . '/product/daconvalidare.php?mainmenu=products&leftmenu=product&type=6&id=3" data-role="button">Da convalidare</a>
</div>';

        print '<div class="inline-block tabsElem">
<a id="price" class="tab inline-block" href="' . $root . '/product/storico.php?mainmenu=products&leftmenu=product&type=6&id=4" data-role="button">Storico</a>
</div>';


        print '</div>';

        // lista da attivare
        print '<br><table class="noborder" width="100%">';

        print '<tr class="liste_titre"><td width="30%" colspan="4">' . "Seleziona codice movimentazione" . '</td>';
        print '<td align="right">' . "Magazzino Sorgente" . '</td>';
        print '<td align="right">' . "Magazzino destinatario" . '</td>';
        print '<td align="right">' . "Numero asset in transito" . '</td>';
          print '<td align="right">' . "Stato" . '</td>';

        print '</tr>';
        print '<tr ' . true . '>';

        $obj_mag_generico = new magazzino($db);
        $user_login = $user->login;
        $and_condition = "";
        if ($user_login == "solari")
        {
            $magazzino_proprio = $obj_mag_generico->getMagazzinoUser("solari");
            $id_m = $magazzino_proprio[0]['rowid'];
            $and_condition = " AND mag_dest = " . $id_m;
        } else if ($user_login == "st_solari")
        {
            $magazzino_proprio = $obj_mag_generico->getMagazzinoUser("st_solari");
            $id_m = $magazzino_proprio[0]['rowid'];
            $and_condition = " AND mag_dest = " . $id_m;
        } else if ($user_login == "pcm_napoli")
        {
            $magazzino_proprio = $obj_mag_generico->getMagazzinoUser("pcm_napoli");
            $id_m = $magazzino_proprio[0]['rowid'];
            $and_condition = " AND mag_dest = " . $id_m;
        } else if ($user_login == "pcm_milano")
        {
            $magazzino_proprio = $obj_mag_generico->getMagazzinoUser("pcm_milano");
            $id_m = $magazzino_proprio[0]['rowid'];
            $and_condition = " AND mag_dest = " . $id_m;
        } else if ($user_login == "tpr")
        {
            $magazzino_proprio = $obj_mag_generico->getMagazzinoUser("Tpr");
            $id_m = $magazzino_proprio[0]['rowid'];
            $and_condition = " AND mag_dest = " . $id_m;
        }
        else if ($user->tipologia == "T" || $user->tipologia == "M" || $user->tipologia == "A")
        {
            $magazzino_proprio = $obj_mag_generico->getMagazzinoUser($user->login);
            $id_m = $magazzino_proprio[0]['rowid'];
            $and_condition = " AND mag_sorgente = " . $id_m;
        }

        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "form_assetmove WHERE flag LIKE '%0%'" . $and_condition;
        $result = $db->query($sql);
        if ($result)
        {
            $array_damovimentare = array();
            while ($array_assetMove = $result->fetch_array(MYSQLI_ASSOC))
                {
                $array_damovimentare[] = $array_assetMove;
                }
            $html = '<form action = "#">';
            $n = count($array_damovimentare);
            for ($i = 0; $i < $n; $i++)
            {
                $html .="<tr>";
                $convalida_record = $array_damovimentare[$i];
                $id_movimentazione = $convalida_record['id'];
                $obj_magazzino = new magazzino($db);
                $nome_magSorg = $obj_magazzino->getMagazzino($convalida_record['mag_sorgente']);
                $nome_magDest = $obj_magazzino->getMagazzino($convalida_record['mag_dest']);
                $riemi_checkbox = '<input type="checkbox" name="movimentazione[]" value=' . '"' . $id_movimentazione . '"' . '> <br>';
                $down = DOL_URL_ROOT . '/product/' . "confconvalida.php?id=" . $convalida_record['codice_mov'];
                $link_codMov = '<a href="' . $down . '">' . $convalida_record['codice_mov'] . '</a>' . '</td>';
                //print $link_codMov;
                $code_asset_decode = json_encode($convalida_record['checkbox_asset']);
                $asset_count = count(explode(",",$convalida_record['checkbox_asset']));
                $icon_inactive = DOL_URL_ROOT . "/core/menus/standard/img/inactive.png";
                $html .= '<td colspan="4">' . $link_codMov . '</td>' . '<td align="center">' . $nome_magSorg[0]['label'] . '</td>' . '<td align="center">' . $nome_magDest[0]['label'] . '</td>' . '<td align="center">' . $asset_count . '</td>'.'<td align="right">' . '<img src="' . $icon_inactive  . '"></td>';
                $html .= "</tr>";
            }
            $html .= "<table>";
            $html .= "<br>";


            $html_ddt = "";
            if ($action == "Genera")
            {
                // query dalla tabella facture per estrarre i nomi facturenumber

                $and_condition = "";
                $fatt_name = "%AST%";
                if ($user_login == "solari")
                {
                    $magazzino_proprio = $obj_mag_generico->getMagazzinoUser("solari");
                    $id_m = $magazzino_proprio[0]['rowid'];
                    $and_condition = " AND mag_dest = " . $id_m;
                    $fatt_name = "%SOL%";
                } else if ($user_login == "st_solari")
                {
                    $magazzino_proprio = $obj_mag_generico->getMagazzinoUser("st_solari");
                    $id_m = $magazzino_proprio[0]['rowid'];
                    $and_condition = " AND mag_dest = " . $id_m;
                    $fatt_name = "%SOL%";
                } else if ($user_login == "pcm_napoli")
                {
                    $magazzino_proprio = $obj_mag_generico->getMagazzinoUser("pcm_napoli");
                    $id_m = $magazzino_proprio[0]['rowid'];
                    $and_condition = " AND mag_dest = " . $id_m;
                    $fatt_name = "%SOL%";
                } else if ($user_login == "pcm_milano")
                {
                    $magazzino_proprio = $obj_mag_generico->getMagazzinoUser("pcm_milano");
                    $id_m = $magazzino_proprio[0]['rowid'];
                    $and_condition = " AND mag_dest = " . $id_m;
                    $fatt_name = "%SOL%";
                } else if ($user_login == "tpr")
                {
                    $magazzino_proprio = $obj_mag_generico->getMagazzinoUser("Tpr");
                    $id_m = $magazzino_proprio[0]['rowid'];
                    $and_condition = " AND mag_dest = " . $id_m;
                    $fatt_name = "%SOL%";
                }
                else if ($user->tipologia == "T" || $user->tipologia == "M" || $user->tipologia == "A")
                {
                    $magazzino_proprio = $obj_mag_generico->getMagazzinoUser($user->login);
                    $id_m = $magazzino_proprio[0]['rowid'];
                    $and_condition = " AND mag_sorgente = " . $id_m;
                    $fatt_name = "%AST%";
                }
                $sql = "SELECT id_ddt ";
                $sql.= " FROM " . MAIN_DB_PREFIX . "form_assetmove ";
                $sql.= " WHERE id_ddt LIKE '$fatt_name'". $and_condition;
                $res = $db->query($sql);
                if ($res)
                {
                    while ($rec = $db->fetch_array($res))
                        {
                        $factnumber_array[] = $rec['id_ddt'];
                        }
                }
                for ($i = 0; $i < count($factnumber_array); $i++)
                {
                    $num_fattura = $factnumber_array[$i];
                    $down = DOL_URL_ROOT . "/product/ddt/" . $num_fattura . ".pdf";

                    $html_ddt .= "<tr>";
                    $pdf_icona = DOL_URL_ROOT . "/theme/common/mime/pdf.png";
                    $html_ddt .= "<td>";


                    $link = '<a  href="' . $down . '">' . '<img border="0" src=' . $pdf_icona . '>' . $num_fattura . '</a></td>';
                    $html_ddt .= $link;
                    $html_ddt .= "</td>";
                    $html_ddt .= "</tr>";
                }
                // print $html_ddt;
            }
         
            print $html;
        }
    }


   
    
}