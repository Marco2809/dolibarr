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
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myMovimentazione.php';
require_once DOL_DOCUMENT_ROOT . '/product/assetmovement.php';
require_once DOL_DOCUMENT_ROOT . '/product/crea_pdf.php'; // serve per il ddt
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myddt.php'; // serve per il ddt
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myScortaprodotto.php'; // serve per il ddt
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

//$limit = $conf->liste_limit;
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


    $texte = "Riepilogo per asset";

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

        // Displays product removal confirmation
        if (GETPOST('delprod'))
            dol_htmloutput_mesg($langs->trans("ProductDeleted", GETPOST('delprod')));

        $param = "&amp;sref=" . $sref . ($sbarcode ? "&amp;sbarcode=" . $sbarcode : "") . "&amp;snom=" . $snom . "&amp;sall=" . $sall . "&amp;tosell=" . $tosell . "&amp;tobuy=" . $tobuy;
        $param.=($fourn_id ? "&amp;fourn_id=" . $fourn_id : "");
        $param.=($search_categ ? "&amp;search_categ=" . $search_categ : "");
        $param.=isset($type) ? "&amp;type=" . $type : "";

        print_barre_liste($texte, $page, "liste.php", $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords);


        // va fatto una query
        //$object->getCodFamily();
        $sql = "SELECT magazzino.rowid, magazzino.label ";
        $sql.= " FROM " . MAIN_DB_PREFIX . "entrepot as magazzino";
        $sql.= " ORDER by rowid";
        $res = $db->query($sql);
        $statutarray = array();
        $label = array();
        if ($res)
        {
            export_in_excell($res, $db); // serve per esportare
        }



        // faccio una query per ricercare il magazzino nella tabella asset
    }
}

function export_in_excell($res, $db)
{

    print '<br><table class="noborder" width="100%">';

    print '<tr class="liste_titre"><td width="25%" colspan="4">' . "Magazzino" . '</td>';

    print '<td align="right">' . "Giacenza nuovo" . '</td>';
    print '<td align="right">' . "Giacenza ricondizionato" . '</td>';
    print '<td align="right">' . "Altro" . '</td>';
    print '</tr>';
    $flag = 0;
    while ($rec = $db->fetch_array($res))
    {
        $label = $rec['label'];
        $id_mayMagazzino = $rec['rowid'];
        $query = "SELECT * ";
        $query .= " FROM " . MAIN_DB_PREFIX . "asset ";
        $query .= " WHERE id_magazzino = " . $id_mayMagazzino;
        $ris = $db->query($query);


        $obj_asset = new magazzino($db);
        $magazzino_nome = $obj_asset->getMagazzino($id_mayMagazzino);
        $magazzino_nome = $magazzino_nome[0]['label'];


        $obj_asset = new asset($db);
        $array_asset = $obj_asset->getAssetFromMagazzino($id_mayMagazzino); //ottengo tutti gli asset del magazzino selezionato
        if (!empty($array_asset))
        {

            $down = DOL_URL_ROOT . '/product/reportMagazzini/' . $magazzino_nome . ".xls";
            $link = '<a href="' . $down . '">' . $magazzino_nome . '</a></td>';
            $filename = "reportMagazzini/" . $magazzino_nome . ".xls";
            $fp = fopen($filename, 'w');

            print '<tr ' . true . '>';
            print '<td colspan="4">' . $link . '</td>';
            print '<td align="right">' . $array_asset['giacenza_nuovo'] . '</td>';
            print '<td align="right">' . $array_asset['giacenza_ricondizionato'] . '</td>';
            print '<td align="right">' . $array_asset['altro'] . '</td>';
            print '</tr>';
        }

        if ($ris)
        {
            $html = '<br><table border="1" class="noborder">';
            $html .= '<tr>';
            $html .= '<td>' . "Codice asset" . '</td>';
            $html .= '<td>' . "Codice famiglia" . '</td>';
            $html .= '<td>' . "Etichetta" . '</td>';
            $html .= '<td>' . "Descrizione famiglia" . '</td>';
            $html .= '<td>' . "Magazzino" . '</td>';
            $html .= '<td>' . "Numero bolla" . '</td>';
            $html .= '<td>' . "Stato fisico" . '</td>';
            $html .= '<td>' . "Stato tecnico" . '</td>';
            $html .= '<td>' . "Corridoio" . '</td>';
            $html .= '<td>' . "Scaffali" . '</td>';
            $html .= '<td>' . "Ripiano" . '</td>';
            $html .= '<td>' . "Marca" . '</td>';
            $html .= '<td>' . "Modello" . '</td>';
            $html .= '</tr>';
            while ($myasset = $db->fetch_array($ris))
            {

                $stato_fisico = "";
                $stato_tecnico = "";
                switch ($myasset['stato_fisico'])
                {
                    case "1":
                        $stato_fisico = "Giacenza";
                        break;
                    case "2":
                        $stato_fisico = "In uso";
                        break;
                    case "3":
                        $stato_fisico = "In transito";
                        break;
                    case "4":
                        $stato_fisico = "In lab";
                        break;
                }
                switch ($myasset['stato_tecnico'])
                {
                    case "1":
                        $stato_tecnico = "Nuovo";
                        break;
                    case "2":
                        $stato_tecnico = "Ricondizionato";
                        break;
                    case "3":
                        $stato_tecnico = "Guasto";
                        break;
                    case "4":
                        $stato_tecnico = "Sconosciuto";
                        break;
                }

                $obj_asset = new magazzino($db);
                $magazzino_nome = $obj_asset->getMagazzino($myasset['id_magazzino']);
                $magazzino_nome = $magazzino_nome[0]['label'];

                $my_where_condition = " WHERE mag_dest = " . $myasset['id_magazzino'] . " AND flag=1 ORDER BY id DESC";
                $obj_movimentazione = new myMovimentazione($db);
                $movimentati = $obj_movimentazione->getMovimentazioniFormMagazzino($myasset['id_magazzino'], $my_where_condition);
                $numero_bolla = "";
                if (!empty($movimentati))
                {

                    for ($j = 0; $j < count($movimentati); $j++)
                    {
                        $movimentazione = $movimentati[$j];
                        $encode_checkbox = explode(",", $movimentazione['checkbox_asset']);
                        if (!empty($encode_checkbox))
                        {
                            $codice_asset_cur = $myasset['cod_asset'];
                            for ($a = 0; $a < count($encode_checkbox); $a++)
                            {
                                $codice_mov = $encode_checkbox[$a];
                                if ($codice_mov == $codice_asset_cur)
                                {
                                    $numero_bolla = $movimentazione['id_ddt'];
                                    $flag = 1;
                                    break;
                                }
                            }
                        }
                    }
                }
                $html .= '<tr>';
                $html .= '<td>' . $myasset['cod_asset'] . '</td>';
                $html .= '<td>' . $myasset['cod_famiglia'] . '</td>';
                $html .= '<td>' . $myasset['label'] . '</td>';
                $html .= '<td>' . $myasset['descrizione'] . '</td>';
                $html .= '<td>' . $magazzino_nome . '</td>';
                $html .= '<td>' . $numero_bolla . '</td>';
                $html .= '<td>' . $stato_fisico . '</td>';
                $html .= '<td>' . $stato_tecnico . '</td>';
                $html .= '<td>' . $myasset['corridoio'] . '</td>';
                $html .= '<td>' . $myasset['scaffali'] . '</td>';
                $html .= '<td>' . $myasset['ripiano'] . '</td>';
                $html .= '<td>' . $myasset['brand'] . '</td>';
                $html .= '<td>' . $myasset['model'] . '</td>';
                $html .= '</tr>';
            }
            $flag;
            fwrite($fp, $html);
        }
    }

    $html .='</table>';
    print '</table>';
}
