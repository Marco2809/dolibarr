
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
if ($user->login != "laboratorio")
{
// Security check
    if ($type == '0')
        $result = restrictedArea($user, 'produit', '', '', '', '', '', $objcanvas);
    else if ($type == '1')
        $result = restrictedArea($user, 'service', '', '', '', '', '', $objcanvas);
    else
        $result = restrictedArea($user, 'produit|service', '', '', '', '', '', $objcanvas);
}
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
        $cod_asset = $_GET['cod_asset'];
        $obj_tracking = new myTracking($db);
        $tracking = $obj_tracking->getAssetTracking($cod_asset);

        $root = DOL_URL_ROOT;
        print '<div class="fiche">';
        print '<div class="tabs" data-type="horizontal" data-role="controlgroup">';
        print '<a class="tabTitle">
<img border="0" title="" alt="" src=""' . $root . '"/theme/eldy/img/object_product.png">
Dati asset
</a>';
        $path = DOL_URL_ROOT;
        print '<div class="inline-block tabsElem">
<a id="card" class="tab inline-block" href="' . $path . '/product/scheda_asset.php?cod_asset=' . $cod_asset . '"' . 'data-role="button">Scheda</a>
</div>';

        print '<div class="inline-block tabsElem">
<a id="card" class="tabactive tab inline-block" href="' . $path . '/product/tracking_asset.php?cod_asset=' . $cod_asset . '"' . 'data-role="button">Tracking</a>
</div>';

        print '<div class="inline-block tabsElem">
<a id="price" class="tab inline-block" href="' . $path . '/custom/ultimateqrcode/qrcodeasset.php?id=' . $cod_asset . '"' . 'data-role="button">Asset QR code</a>
</div>';

        print '</div>';

        print '<div class="tabBar">';
        print '<tbody>';
        print '<table class="border" width="100%">';
        print '<tr>';
        print '<td>Codice asset</td>';
        print '<td>' . $tracking[0]['codice_asset'] . '</td>';
        print '</tr>';

        print '<tr>';
        print '<td>Etichetta</td>';
        print '<td>' . $tracking[0]['etichetta'] . '</td>';
        print '</tr>';

        print '<tr>';
        print '<td>Data creazione asset</td>';
        print '<td>' . $tracking[0]['data'] . '</td>';
        print '</tr>';

        print '<tr>';
        print '<td>Ora</td>';
        print '<td>' . $tracking[0]['ora'] . '</td>';
        print '</tr>';

        print '</tbody>';
        print '</table>';
        print '</div>';
    }
}

//stampa i record della tabella tracking



print' <table class="noborder" width="100%">';
print'<tbody>';

print '<tr class="liste_titre">';
print "<td>Data</td>";
print "<td>Modificato dall'utente</td>";
print "<td>Azione</td>";
print "<td>Old</td>";
print "<td>New</td>";
print "<td>Riferimento</td>";

print"</tr>";

for ($i = 0; $i < count($tracking); $i++)
{
    print "<tr>";

    $utente = getUser($db, $tracking[$i]['user']);
    print "<td>" . $tracking[$i]['data'] . " " . $tracking[$i]['ora'] . "</td>";
    print "<td>" . $utente->login . "</td>";
    print "<td>" . $tracking[$i]['azione'] . "</td>";

    if (strcasecmp($tracking[$i]['azione'], "modifica stato fisico") == 0)
    {
        $stato_fisico_prec = getStrStato_fisico($tracking[$i]['old']);
        $stato_fisico_agg = getStrStato_fisico($tracking[$i]['new']);

        $stato_fisico_prec = empty($stato_fisico_prec) ? $tracking[$i]['old'] : $stato_fisico_prec;
        $stato_fisico_agg = empty($stato_fisico_agg) ? $tracking[$i]['new'] : $stato_fisico_agg;

        print "<td>" . $stato_fisico_prec . "</td>";
        print "<td>" . $stato_fisico_agg . "</td>";
        if (!empty($tracking[$i]['id_ticket']))
        {
            $url_ticket = "http://ticket.service-tech.org/scp/tickets.php?id=" . $tracking[$i]['id_ticket'];
            print "tck ".$tracking[$i]['riferimento']."</td>";
           // $link_ticket = '<a href=' . $url_ticket . ' >' . $tracking[$i]['riferimento'] . '</a></td>';
          //  print $link_ticket;
        }
        //print "<td></td>";
        print "<td></td>";
    } else if (strcasecmp($tracking[$i]['azione'], "modifica stato tecnico") == 0)
    {
        $stato_tecnico_prec = getStrStato_tecnico($tracking[$i]['old']);
        $stato_tecnico_agg = getStrStato_tecnico($tracking[$i]['new']);

        $stato_tecnico_prec = empty($stato_tecnico_prec) ? $tracking[$i]['old'] : $stato_tecnico_prec;
        $stato_tecnico_agg = empty($stato_tecnico_agg) ? $tracking[$i]['new'] : $stato_tecnico_agg;

        if (!empty($tracking[$i]['id_ticket']))
        {
            $url_ticket = "http://ticket.service-tech.org/scp/tickets.php?id=" . $tracking[$i]['id_ticket'];
            print "tck ".$tracking[$i]['riferimento']."</td>";
           // $link_ticket = '<a href=' . $url_ticket . ' >' . $tracking[$i]['riferimento'] . '</a></td>';
          //  print $link_ticket;
        }
       // print "<td>" . $stato_tecnico_prec . "</td>";
        print "<td>" . $stato_tecnico_agg . "</td>";
    } else if (strcasecmp($tracking[$i]['azione'], "modifica del magazzino") == 0)
    {
        $obj_magazzino = new magazzino($db);
        $magazzino_nome_old = $obj_magazzino->getMagazzino($tracking[$i]['old']);
        $magazzino_nome_old = $magazzino_nome_old[0]['label'];
        $magazzino_nome_old = empty($magazzino_nome_old) ? $tracking[$i]['old'] : $magazzino_nome_old;

        $magazzino_nome_new = $obj_magazzino->getMagazzino($tracking[$i]['new']);
        $magazzino_nome_new = $magazzino_nome_new[0]['label'];
        $magazzino_nome_new = empty($magazzino_nome_new) ? $tracking[$i]['new'] : $magazzino_nome_new;

        print "<td>" . $magazzino_nome_old . "</td>";
        print "<td>" . $magazzino_nome_new . "</td>";
    } else if (strcasecmp($tracking[$i]['azione'], "modifica magazzino") == 0) // movimentazione
    {

        $obj_magazzino = new magazzino($db);
        $magazzino_nome_old = $obj_magazzino->getMagazzino($tracking[$i]['old']);
        $magazzino_nome_old = $magazzino_nome_old[0]['label'];
        $magazzino_nome_old = empty($magazzino_nome_old) ? $tracking[$i]['old'] : $magazzino_nome_old;

        $magazzino_nome_new = $obj_magazzino->getMagazzino($tracking[$i]['new']);
        $magazzino_nome_new = $magazzino_nome_new[0]['label'];
        $magazzino_nome_new = empty($magazzino_nome_new) ? $tracking[$i]['new'] : $magazzino_nome_new;
        print "<td>" . $magazzino_nome_old . "</td>";
        print "<td>" . $magazzino_nome_new . "</td>";

        print '<td class="nowrap">';
        //$link_ticket = '<a href="' . DOL_URL_ROOT . '/product/scheda_movimentazione.php?mainmenu=products&id=' . $tracking[$i]['riferimento'] . '" >' . $tracking[$i]['riferimento'] . '</a></td>';

       if (!empty($tracking[$i]['id_ticket']))
        {
            $url_ticket = "http://ticket.service-tech.org/scp/tickets.php?id=" . $tracking[$i]['id_ticket'];
            print "tck ".$tracking[$i]['riferimento']."</td>";
           // $link_ticket = '<a href=' . $url_ticket . ' >' . $tracking[$i]['riferimento'] . '</a></td>';
          //  print $link_ticket;
        }
        else
        {
            
            $link_mov = '<a href="' . DOL_URL_ROOT . '/product/scheda_movimentazione.php?mainmenu=products&id=' . $tracking[$i]['riferimento'] . '" >' . $tracking[$i]['riferimento'] . '</a></td>';
            print $link_mov;
        }


        print "</td>\n";
        print "</tr>";
    }
    //print "<td>" . $tracking[$i]['riferimento'] . "</td>";
}

print '</tbody>';

print '</table>';

function getUser($db, $id_user)
{
    $query = "SELECT login ";
    $query .= " FROM " . MAIN_DB_PREFIX . "user as u ";
    $query .= " WHERE u.rowid = " . $id_user;
    $res_query = $db->query($query);
    if ($res_query)
    {
        $obj_user = $db->fetch_object($res_query);
        return $obj_user;
    }
    return false;
}

function getStrStato_fisico($stato_fisico)
{
    $str_stato_fisico = "";
    switch ($stato_fisico)
    {
        case 1:
            $str_stato_fisico = "Giacenza";
            break;
        case 2:
            $str_stato_fisico = "In uso";
            break;
        case 3:
            $str_stato_fisico = "in Transito";
            break;
        case 4:
            $str_stato_fisico = "In lab";
            break;
        case 5:
            $str_stato_fisico = "Dismesso";
            break;
    }
    return $str_stato_fisico;
}

function getStrStato_tecnico($stato_tecnico)
{
    $str_stato_tecnico = "";
    switch ($stato_tecnico)
    {
        case 1:
            $str_stato_tecnico = "Nuovo";
            break;
        case 2:
            $str_stato_tecnico = "Ricondizionato";
            break;
        case 3:
            $str_stato_tecnico = "Guasto";
            break;
        case 4:
            $str_stato_tecnico = "Sconosciuto";
            break;
    }
    return $str_stato_tecnico;
}
