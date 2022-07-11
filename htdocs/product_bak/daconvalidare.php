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
<a id="price" class="tab inline-block" href="' . $root . '/product/transito.php?mainmenu=products&leftmenu=product&type=6&id=3" data-role="button">In transito</a>
</div>';


        print '<div class="inline-block tabsElem">
<a id="price" class="tabactive tab inline-block" href="' . $root . '/product/daconvalidare.php?mainmenu=products&leftmenu=product&type=6&id=3" data-role="button">Da convalidare</a>
</div>';

        print '<div class="inline-block tabsElem">
<a id="price" class="tab inline-block" href="' . $root . '/product/storico.php?mainmenu=products&leftmenu=product&type=6&id=4" data-role="button">Storico</a>
</div>';


        print '</div>';

        // lista da attivare
        print '<br><table class="noborder" width="100%">';

        print '<tr class="liste_titre"><td width="20%" colspan="4">' . "Seleziona" . '</td>';
        print '<td align="right">' . "Codice movimentazione" . '</td>';
        print '<td align="right">' . "Magazzino Sorgente" . '</td>';
        print '<td align="right">' . "Magazzino destinatario" . '</td>';
        print '<td align="right">' . "Elenco asset" . '</td>';

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
            $magazzino_proprio = $obj_mag_generico->getMagazzinoUser($user->id, "fk_user");
            $id_m = $magazzino_proprio[0]['rowid'];
            
            $and_condition = " AND mag_dest = " . $id_m;
            
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
                $down = DOL_URL_ROOT . '/product/' . "confconvalida.php?mainmenu=products&id=" . $convalida_record['codice_mov'];
                $link_codMov = '<a href="' . $down . '">' . $convalida_record['codice_mov'] . '</a>' . '</td>';
                //print $link_codMov;
                $code_asset_decode = json_encode($convalida_record['checkbox_asset']);
                $html .= '<td colspan="4">' . $link_codMov . '</td>' . '<td align="right">' . $convalida_record['codice_mov'] . '</td>' . '<td align="right">' . $nome_magSorg[0]['label'] . '</td>' . '<td align="right">' . $nome_magDest[0]['label'] . '</td>' . '<td align="right">' . $code_asset_decode . '</td>';
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
                    ' . $html_ddt . '
                    <tr class = "impair">
                    </tbody>
                    </table >';

            $html .= "</form>";
            print $html;
        }
    }
    if ($action == "Convalida")
    {
        // aggiornare lo stato dell'asset in giacenza
        // impostare il flag a 1 della form_asset
        $data_convalida = $_GET['data_convalida'];
        $mov_selezionati = $_GET['movimentazione']; // gli asset selezionato dalla form
        for ($i = 0; $i < count($mov_selezionati); $i++)
        { // di solito si convalida ad una ad uno, ma può decidere di concalidare un blocco di movimentazioni
            $sql = "SELECT checkbox_asset FROM " . MAIN_DB_PREFIX . "form_assetmove";
            $sql .= " WHERE id = " . $mov_selezionati[$i];
            $result = $db->query($sql);
            $asset_decode = array();
            if ($result)
            {
                $obj = $db->fetch_object($result);
                $asset_decode = explode(",", $obj->checkbox_asset);
                for ($j = 0; $j < count($asset_decode); $j++)
                { // per ogni asset della movimentazione selezionata
                    //imposto lo stato dell'asset in giacenza
                    $stat_fisico = 1; //giacenza
                    $sql = "UPDATE " . MAIN_DB_PREFIX . "asset SET stato_fisico=" . $stat_fisico; // salvo lo stato fisico in una variabile temporanea in modo che si possa ripristinare
                    $sql .= " WHERE cod_asset LIKE '" . $asset_decode[$j] . "'";
                    $aggiornato = $db->query($sql);
                }
                // ora imposto il flag a 1 (significa che la movimentazione è processato)
                $convalida = 1;
                $sql = "UPDATE " . MAIN_DB_PREFIX . "form_assetmove SET flag=" . $convalida; // salvo lo stato fisico in una variabile temporanea in modo che si possa ripristinare
                $sql .= " WHERE id = '" . $mov_selezionati[$i] . "'";
                $agg = $db->query($sql);

                $sql = "UPDATE " . MAIN_DB_PREFIX . "form_assetmove SET data_convalida=" . $data_convalida; // salvo lo stato fisico in una variabile temporanea in modo che si possa ripristinare
                $sql .= " WHERE id = '" . $mov_selezionati[$i] . "'";
                $agg = $db->query($sql);
            }
        }
    }

    if ($action == "Elimina")
    {
        // occorre aggiornare lo stato degli asset
        // impostare il flag a 1
        $mov_selezionati = $_GET['movimentazione'];
        for ($i = 0; $i < count($mov_selezionati); $i++)
        { // per ogni select selezionati 
            $sql = "SELECT checkbox_asset FROM " . MAIN_DB_PREFIX . "form_assetmove";
            $sql .= " WHERE id = " . $mov_selezionati[$i];
            $result = $db->query($sql);
            $asset_decode = array();
            if ($result)
            {
                $obj = $db->fetch_object($result);
                $asset_decode = explode(",", $obj->checkbox_asset);
            }
            $query = "DELETE FROM " . MAIN_DB_PREFIX . "form_assetmove"; // eliminno il record dalla tabella form_asset
            $query .= " WHERE id = " . $mov_selezionati[$i];
            $res = $db->query($query);
            for ($j = 0; $j < count($asset_decode); $j++)
            {
                //recupero la movimentazione, cosi poi elimino anche il ddt
                $query = "SELECT rowid FROM " . MAIN_DB_PREFIX . "stock_mouvement";
                $query .= " WHERE fk_product LIKE '" . $asset_decode[$j] . "'";
                $result = $db->query($query);
                if ($result)
                { // se ha eseguito la query per ricercare l'd della movimentazione 
                    $obj_id = $db->fetch_object($result);
                    $id_movimentazione = $obj_id->rowid;
                    // a questo punto elimino il record della tabella ddt la tabella ddt
                    $query = "DELETE FROM " . MAIN_DB_PREFIX . "log_movimentazione"; // elimino i recod con i cod_prdo della moviemntazione
                    $query .= " WHERE id_movimentazione = '" . $id_movimentazione . "'";
                    $res = $db->query($query);
                }
                $query = "DELETE FROM " . MAIN_DB_PREFIX . "stock_mouvement"; // elimino i recod con i cod_prdo della moviemntazione
                $query .= " WHERE fk_product LIKE '" . $asset_decode[$j] . "'";
                $res = $db->query($query);
                //riporto gli stati dell'asset come erano prima
                $sql = "SELECT tmp_stato_fisico,tmp_magdest FROM " . MAIN_DB_PREFIX . "asset";
                $sql .= " WHERE cod_asset LIKE '" . $asset_decode[$j] . "'";
                $r = $db->query($sql);
                if ($r)
                {
                    $obj = $db->fetch_object($r);
                    $tmp_stato_fisico = $obj->tmp_stato_fisico; // ho recuperato lo stato fisico iniziale
                    $sql = "UPDATE " . MAIN_DB_PREFIX . "asset SET stato_fisico=" . $tmp_stato_fisico; // salvo lo stato fisico in una variabile temporanea in modo che si possa ripristinare
                    $sql .= " WHERE cod_asset LIKE '" . $asset_decode[$j] . "'";
                    $agg_statofisico = $db->query($sql); // ho ripristinato lo stato


                    $tmp_mag = $obj->tmp_magdest;
                    $sql = "UPDATE " . MAIN_DB_PREFIX . "asset SET id_magazzino=" . $tmp_mag; // salvo lo stato fisico in una variabile temporanea in modo che si possa ripristinare
                    $sql .= " WHERE cod_asset LIKE '" . $asset_decode[$j] . "'";
                    $ripristinato_mag = $db->query($sql); // ho ripristinato lo stato
                }
                //ripristino anche il magazzino di origine
            }
        }
    }
}