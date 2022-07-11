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
        $cod_famiglia = $_GET['id'];

        $root = DOL_URL_ROOT;
        print '<div class="fiche">';
        print '<div class="tabs" data-type="horizontal" data-role="controlgroup">';
        print '<a class="tabTitle">
<img border="0" title="" alt="" src=""' . $root . '"/theme/eldy/img/object_product.png">
Dati della famiglia
</a>';
        print '<div class="inline-block tabsElem">
<a id="card" class="tab inline-block" href="' . $root . '/product/scheda_famiglia.php?mainmenu=products&id=' . $cod_famiglia . '"' . 'data-role="button">Scheda</a>
</div>';

        print '<div class="inline-block tabsElem">
<a id="price" class="tabactive tab inline-block" href="' . $root . '/product/scorta_famiglia.php?mainmenu=products&id=' . $cod_famiglia . '"' . 'data-role="button">Scorta</a>
</div>';


        print '</div>';
        $where_mag_utente = "";
        if ($user->tipologia == "T")
        {
            $mag_obj = new magazzino($db);
            $user_id = $user->id;
            $where_condition = " WHERE fk_user = $user_id ";
            $lista_magazzini = $mag_obj->getMagazziniSelect("*", $where_condition);
            $id_mag_utente = $lista_magazzini[0]['rowid'];
            $where_mag_utente = " AND id_magazzino=" . $id_mag_utente;
        }
        $query_stock = "UPDATE " . MAIN_DB_PREFIX . "product "
                . "SET stock=(SELECT count(*) FROM " . MAIN_DB_PREFIX . "asset"
                . " WHERE cod_famiglia LIKE '$cod_famiglia' AND stato_fisico != 2 AND stato_fisico != 5 $where_mag_utente )"
                . " WHERE ref LIKE '$cod_famiglia'";
        $res_update_stock = $db->query($query_stock);

        // faccio la query per recuperare la famiglia

        $sql = "SELECT  * FROM  " . MAIN_DB_PREFIX . "product as f ";
        $sql .= " WHERE f.ref LIKE '" . $cod_famiglia . "'";
        $result = $db->query($sql);
        if ($result)
        {

            print '<div class="tabBar">';
            print '<table class="border" width="100%">';
            print '<tbody>';
            $cod_f = $db->fetch_array(MYSQLI_ASSOC);
            print '<tr>';
            print '<td width="15%">Rif.</td>';
            print '<td class="nobordernopadding">' . $cod_f['ref'] . '</td>';
            print '</tr>';

            print '<tr>';
            print '<td width="15%">Etichetta</td>';
            print '<td class="nobordernopadding">' . $cod_f['label'] . '</td>';
            print '</tr>';

            print '<tr>';
            print '<td width="15%">Stato (Vendite) </td>';
            print'<td>';
            print'<img border="0" title="In vendita" alt="In vendita" src="' . $root . '"/theme/eldy/img/statut4.png"> In vendita';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td width="15%">Stato (Acquisti) </td>';
            print '<td>';
            print '<img border="0" title="In vendita" alt="In vendita" src="' . $root . '"/theme/eldy/img/statut4.png"> Acquistabile';
            print '</td>';
            print '</tr>';

            // facico una query sulla tabella asset per ricare la quantità di asset in uso.
            $query_uso = "SELECT count(*) as in_uso  FROM " . MAIN_DB_PREFIX . "asset"
                    . " WHERE cod_famiglia LIKE '$cod_famiglia' AND stato_fisico = 2";
            $res_update_uso = $db->query($query_uso);
            if ($res_update_uso)
            {
                $in_uso_obj = $db->fetch_object($res_update_uso);
                $qt_in_uso = (int) $in_uso_obj->in_uso;
                if ($qt_in_uso > 0)
                {
                    print '<tr>';
                    print '<td width="15%">In uso</td>';
                    print '<td class="nobordernopadding">' . $qt_in_uso . '</td>';
                    print '</tr>';
                }
            }
            $query_dismesso = "SELECT count(*) as dismessi  FROM " . MAIN_DB_PREFIX . "asset"
                    . " WHERE cod_famiglia LIKE '$cod_famiglia' AND stato_fisico = 5";
            $res_update_uso = $db->query($query_dismesso);
            if ($res_update_uso)
            {

                $in_uso_obj = $db->fetch_object($res_update_uso);
                $qt_dismessi = (int) $in_uso_obj->dismessi;
                if ($qt_dismessi > 0)
                {
                    print '<tr>';
                    print '<td width="15%">Dismessi</td>';
                    print '<td class="nobordernopadding">' . $qt_dismessi . '</td>';
                    print '</tr>';
                }
            }
            print '<tr>';
            print '<td width="15%">Scorta fisica</td>';
            print '<td class="nobordernopadding">' . $cod_f['stock'] . '</td>';
            print '</tr>';
            print '</tbody>';
            print '</table>';
            print '</div>';

            print '<br><br>';
        }
        print '<br><table class="noborder" width="100%">';

        print '<tr class="liste_titre"><td width="10%" colspan="4">' . "Magazzino" . '</td>';
        print '<td align="right">' . "Codice produttore" . '</td>';
        print '<td align="right">' . "Giacenza nuovo" . '</td>';
        print '<td align="right">' . "Giacenza ricondizionato" . '</td>';
        print '<td align="right">' . "Altro" . '</td>';
        print '<td align="right">' . "In uso" . '</td>';
        print '<td align="right">' . "Dismesso" . '</td>';
        print '</tr>';

        $user_id = $user->id;
        $where_cond = "";
        if ($user->tipologia == "T")
        {
            $where_cond = " WHERE magazzino.fk_user = " . $user_id;
        }

        $sql = "SELECT magazzino.rowid, magazzino.label ";
        $sql.= " FROM " . MAIN_DB_PREFIX . "entrepot as magazzino ";
        $sql.= $where_cond;
        $sql.= " ORDER by rowid";
        $res = $db->query($sql);
        if ($res)
        {
            while ($rec = $db->fetch_array($res))
            {
                $id_mayMagazzino = $rec['rowid'];
                $query .= " FROM " . MAIN_DB_PREFIX . "asset ";
                $query .= " WHERE id_magazzino = " . $id_mayMagazzino;
                $ris = $db->query($query);

                $obj_asset = new magazzino($db);
                $magazzino_nome = $obj_asset->getMagazzino($id_mayMagazzino);
                $magazzino_nome = $magazzino_nome[0]['label'];

                $down = DOL_URL_ROOT . '/product/view_asset_magazzino.php?mainmenu=products&id_magazzino=' . $id_mayMagazzino . "&codice_famiglia=" . $cod_famiglia;
                $link = '<a href="' . $down . '">' . $magazzino_nome . '</a></td>';


                $obj_asset = new asset($db);
                $array_asset = $obj_asset->getAssetFromMagazzino($id_mayMagazzino, $cod_famiglia);

                $obj_uso = new asset($db);
                $tot_in_uso = $obj_uso->getTotInUso($cod_famiglia, $id_mayMagazzino);

                $obj_dismesso = new asset($db);
                $tot_dismesso = $obj_dismesso->getTotDismesso($cod_famiglia, $id_mayMagazzino);

                if (empty($array_asset['giacenza_nuovo']) && empty($array_asset['giacenza_ricondizionato']) && empty($array_asset['altro']) && $tot_in_uso == 0 && $tot_dismesso == 0)
                {
                    continue;
                }

                print '<td colspan="4">' . $link . '</td>';
                print '<td align="right">' . $cod_f['customcode'] . '</td>';
                print '<td align="right">' . $array_asset['giacenza_nuovo'] . '</td>';
                print '<td align="right">' . $array_asset['giacenza_ricondizionato'] . '</td>';
                print '<td align="right">' . $array_asset['altro'] . '</td>';
                print '<td align="right">' . $tot_in_uso . '</td>';
                print '<td align="right">' . $tot_dismesso . '</td>';

                print '</tr>';
            }
        }


        print '</table>';
    }
}