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
require_once DOL_DOCUMENT_ROOT . '/product/myclass/schedaMovimentazione.php';
require_once DOL_DOCUMENT_ROOT . '/product/assetmovement.php';
require_once DOL_DOCUMENT_ROOT . '/product/crea_pdf.php'; // serve per il ddt
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myddt.php'; // serve per il ddt
require_once DOL_DOCUMENT_ROOT . '/product/log_movimentazione.php'; // serve per il ddt
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myClientiZoccali.php'; // serve per il ddt
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myTracking.php'; // serve per il ddt

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
        $root = DOL_URL_ROOT;
        llxHeader('', $title, $helpurl, '');
        print '<div class="fiche">';
        print '<div class="tabs" data-type="horizontal" data-role="controlgroup">';
        print '<a class="tabTitle">
<img border="0" title="" alt="" src="' . $root . '"/theme/eldy/img/object_product.png">
Movimentazione asset
</a>';
        print '<div class="inline-block tabsElem">
<a id="card" class="tab inline-block" href="' . $root . '/product/nuovo_intervento.php?leftmenu=product&type=5" data-role="button">Nuovo intervento</a>
</div>';


        print '<div class="inline-block tabsElem">
<a id="price" class="tabactive tab inline-block" href="' . $root . '/product/storico_intervento.php?leftmenu=product&type=6&id=4" data-role="button">Storico</a>
</div>';



        $cod_movimentazione = $_GET['codice_mov'];
        print '<form action="#" method="POST" >';
        print"<br><br><br>";
        print "<strong>Codice movimentazione:  </strong>" . $cod_movimentazione;
        print "<br><br>";

        /** SCHEDA - breve descrizione */
        $obj_movimentazione = new movimentazione($db);
        $movimentazione = $obj_movimentazione->getMovimentazione($cod_movimentazione);
        $obj_magazzino = new magazzino($db);

        $magazzino_sorgente = $obj_magazzino->getMagazzino($movimentazione->mag_sorgente);
        // $magazzino_destinatario = $obj_magazzino->getMagazzino($movimentazione->mag_dest);

        $obj_cliente = new clientiZoccali($db);
        $obj_mag_dest = $obj_cliente->getCliente($movimentazione->mag_dest);
        $magazzino_nome_dest = $obj_mag_dest[0]['INSEGNA'];



        $tabella_riepilogo = "";
        $tabella_riepilogo .= '<div class="tabBar">';
        $tabella_riepilogo .= '<table class="border" width="100%">';
        $tabella_riepilogo .= '<tbody>';

        $tabella_riepilogo .= '<tr>';
        $tabella_riepilogo .= '<td width="15%"><strong>Codice movimentazione</strong></td>';
        $tabella_riepilogo .= '<td class="nobordernopadding">' . $cod_movimentazione . '</td>';
        $tabella_riepilogo .= '</tr>';

        print $html;


        print '<table class="border" width="100%">';
        $mag = new magazzino($db);
        $lista_magazzino = $mag->getTuttiMagazzino();
        for ($i = 0; $i < count($lista_magazzino); $i++)
        {
            $magazzino = $lista_magazzino[$i];
            $elem = $magazzino['label'];
            $statutarray [$magazzino['rowid']] = $elem;
        }
        print '<tr><td class="fieldrequired">' . "Magazzino di origine" . '</td><td colspan="3">';
        $select = '<select id="mag_sorgente" class="flat" name="mag_sorgente" disabled="disabled" >';

        $selected = " ";
        $query = "SELECT * FROM tmp_form";
        $ris = $db->query($query);
        $obj_tmp = $db->fetch_object($ris);
        $mag_sorgSelezionato = $obj_tmp->tmp_magsorg;
        $mag_destSelezionato = $obj_tmp->tmp_magdest;
        $cauale_tmp = $obj_tmp->tmp_causale;
        $luogo_tmp = $obj_tmp->tmp_luogo;
        $data_tmp = $obj_tmp->tmp_data;
        $annotazioni_tmp = $obj_tmp->tmp_annotazioni;


        $select .= '<option value=' . $key . $selected . '>' . $magazzino_sorgente[0]['label'] . '</option>';

        $select .= '</select>';

        //print $form->selectarray('mag_sorgente', $statutarray, GETPOST('mag_sorgente'));
        $select .= '</td></tr>';
        print $select;

        print '<tr><td class="fieldrequired">' . "Cliente" . '</td><td colspan="3">';
        $select_due = '<select id="mag_sorgente" class="flat" name="mag_dest" disabled="disabled">';
        $select_due .= '<option value=' . $key . $selected . '>' . $magazzino_nome_dest . '</option>';
        $select_due .= '</select>';

        //print $form->selectarray('mag_sorgente', $statutarray, GETPOST('mag_sorgente'));
        $select_due .= '</td></tr>';
        print $select_due;

        //print $form->selectarray('mag_dest', $statutarray, GETPOST('mag_dest'));

        print '<tr><td width="20%">' . "Causale del trasporto" . '</td>'; // non modificabile
        print '<td><input name="dati[causale_trasp]" size="60" value="' . $movimentazione->causale_trasp . '" disabled="disabled">';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Luogo di destinazione" . '</td>'; // non modificabile
        print '<td><input name="dati[luogo_dest]" size="60" value="' . $movimentazione->luogo_dest . '" disabled="disabled">';
        print '</td></tr>';

        $trasporto = array("1" => "Mittente", "2" => "Vettore", "3" => "Destinatario");
        print '<tr><td>' . "Trasporto a mezzo" . '</td><td colspan="3">';
        switch ($movimentazione->trasporto_mezzo)
        {
            case 1 :
                $trasporto_mezzo = "Mittente";
                break;
            case 2 :
                $trasporto_mezzo = "Vettore";
                break;

            case 3 :
                $trasporto_mezzo = "Destinatario";
                break;
        }
        print '<input name="trasp_mezzo" size="60" value="' . $trasporto_mezzo . '" disabled="disabled">'; //$form->selectarray('trasp_mezzo', $trasporto, GETPOST('trasp_mezzo'));
        print '<input name="vettore_nota" size="60" value="' . $movimentazione->annotazioni . '"disabled="disabled">';
        print '</td></tr>';


        print '<tr><td>' . "Data di ritiro" . '</td><td colspan="3">';
        print '<input type="text" name="dati[data_ritiro]"  value="' . $movimentazione->data_ritiro . '" disabled="disabled"></td>';

        print '<tr><td>' . "Data di convalidazione" . '</td><td colspan="3">';
        print '<input type="text" name="dati[data_convalida]"  value="' . $movimentazione->data_convalida . '" disabled="disabled"></td>';

        print '<tr><td width="20%">' . "Annotazioni" . '</td>'; //oni non modificabile
        print '<td><input name="dati[annotazioni]" size="60" value="' . $movimentazione->annotazioni . '" disabled="disabled">';
        print '</td></tr>';



        print '</table>';
        print '<br>';
        require 'track_view.php';
        print '<br>';


        //scarica il ddt (documentio di trasporto)

        $nome_ddt = $movimentazione->codice_mov . ".pdf";
        $down_mag = DOL_URL_ROOT . '/product/ordini/' . $nome_ddt;
        $link = '<a href="' . $down_mag . '">' . "Scarica il documento di trasporto >>" . '</a></td>';
        print $link;
    }
}