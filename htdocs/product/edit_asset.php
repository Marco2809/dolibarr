
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
        $sql = "SELECT  * FROM  " . MAIN_DB_PREFIX . "asset a ";
        $sql .= " WHERE a.cod_asset LIKE '" . $code_asset . "'";
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
            print '<td class="fieldrequired">HW</td>';
            print '<td colspan="3">';
            print '<input value="' . $code_asset . '" maxlength="255" size="40" name="code_asset" disabled="disabled">';
            print '</td>';

            //print '<td>' . "ciao\nprova\nprova" . '</td>';
            print '</tr>';

            print '<tr>';
            print '<td class="fieldrequired">DIT</td>';
            print '<td colspan="3">';
            print '<input value="' . $asset['label'] . '" maxlength="255" size="40" name="label">';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td width="20%">Quantita totale </td>';
            print '<td>';
            print '<input type="text" value="' . $asset['scorta_tot'] . '" size="16" name="scorta_tot" readonly>';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td width="20%">Aggiungi pezzi HW </td>';
            print '<td>';
            print '<input id="nights" type="number" name="scorta_add" maxlength="3" value="0" step="1"  min="0">';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td width="20%">Rimuovi  pezzi HW </td>';
            print '<td>';
            print '<input id="nights" type="number" name="scorta_delete" maxlength="3" value="0" step="1"  min="0">';
            print '</td>';
            print '</tr>';




            print '<tr>';
            print '<td valign="top">Descrizione</td>';
            print '<td colspan="3">';
            print '<textarea id="desc" class="flat" name="desc" rows="4" cols="80">' . $asset['descrizione'] . '</textarea>';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td>Proprietario</td>';
            print '<td colspan="2">';
            print '<input type="text" value="' . $asset['proprietario'] . '" size="30" name="proprietario" readonly>';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td>Codice produttore</td>';
            print '<td colspan="2">';
            print '<input type="text" value="' . $asset['codice_produttore'] . '" size="30" name="codice_produttore">';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td width="20%">Corridoio</td>';
            print '<td>';
            print '<input type="text" value="' . $asset['corridoio'] . '" size="16" name="corridoio">';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td width="20%">Scaffale</td>';
            print '<td>';
            print '<input type="text" value="' . $asset['scaffali'] . '" size="16" name="scaffali">';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td width="20%">Ripiano</td>';
            print '<td>';
            print '<input type="text" value="' . $asset['ripiano'] . '" size="16" name="ripiano">';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td>Marca</td>';
            print '<td>';
            print '<input type="text" value="' . $asset['brand'] . '" size="16" name="brand">';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td width="20%">Modello</td>';
            print '<td>';
            print '<input type="text" value="' . $asset['model'] . '" size="16" name="model">';
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<td valign="top">Nota</td>';
            print '<td colspan="3">';
            print '<textarea id="desc" class="flat" name="note" rows="4" cols="80">' . $asset['note'] . '</textarea>';
            print '</td>';
            print '</tr>';

            $obj_mag_generico = new magazzino($db);
            $where = "";
            print '<tr><td>' . "Seleziona il magazzino" . '</td><td colspan="3">';
            // va fatto una query
            //$object->getCodFamily();
            $sql = "SELECT magazzino.rowid, magazzino.label ";
            $sql.= " FROM " . MAIN_DB_PREFIX . "entrepot as magazzino" . $where;
            $res = $db->query($sql);
            $statutarray = array();
            $label = array();
            if ($res)
            {
                $prods = array();
                while ($rec = $db->fetch_array($res))
                {
                    $statutarray[] = $rec['rowid'];
                    $label [] = $rec['label'];
                }
            }
            //print $form->selectarray('sel_magazzino', $statutarray, GETPOST('sel_magazzino'), 1);
            $magazzino_selezionato = $asset['id_magazzino'];
            print '<select name="id_magazzino">';
            $selected = "";
            for ($i = 0; $i < count($statutarray); $i++)
            {
                $valore = $statutarray[$i];
                if ($magazzino_selezionato == $valore)
                {
                    $selected = " selected";
                } else
                {
                    $selected = " ";
                }
                $etich = $label[$i];
                print '<option value=' . "$valore" . $selected . '>' . $etich . '</option>';
            }
            print "</select>";
            print '</td></tr>';
            print '</tbody>';
            print '</table>';

            $root = DOL_URL_ROOT;
            print '<div class="tabsAction">';
            print '<center>';
            print '<div class="inline-block divButAction">';
            //print '<a class="butAction" href="/dolibarr/htdocs/product/edit_asset.php?action=salva&id=' .$code_asset .'"'. '>Salva</a>';
            print '<a class="butAction" href="' . $root . '/product/scheda_asset.php?mainmenu=products&cod_asset=' . $code_asset . '"' . '>Annulla</a>';
            print '<input class="butAction" type="submit" name = "salva" value="salva">';

            print '</center>';
            print '</div>';
            print '</form>';
        }
        
        if ($salva_button == "salva")
        {
            $code_asset = (isset($_POST['code_asset'])) ? $_POST['code_asset'] : $code_asset;
            // verifico se ?? da incrementare
            $etichetta = (isset($_POST['label'])) ? "'" . $_POST['label'] . "'" : null;

            $scorta_totale = (int) $asset['scorta_tot'];
            $scorta_add = (int) $_POST['scorta_add'];
            $scorta_delete = (int) $_POST['scorta_delete'];

            $flag_qt_uno = true;
            if ($scorta_totale < $scorta_delete) // se la quantit?? da eliminare ?? maggiore  della quantit?? totale
            {
                $str_msg = '<div> Quantita pezzi HW da rimuovere ?? <b> inferiore alla quantita totale</b></div>';
                msgErrore($str_msg);
                $flag_qt_uno = false;
                $path = DOL_URL_ROOT . '/product/edit_asset.php?mainmenu=products&id=' . $code_asset;
                print '<META HTTP-EQUIV="Refresh" CONTENT="4; url=' . $path . '">';
            }
            if ($flag_qt_uno)
            {
                if ($scorta_add > 0 && $scorta_delete > 0)
                {
                   $scorta_totale = ($scorta_totale +  $scorta_add) - $scorta_delete;
                }
                else if ($scorta_add > 0)
                {
                    $scorta_totale = $scorta_totale + $scorta_add;
                }
                else if ( $scorta_delete > 0)
                {
                    $scorta_totale = $scorta_totale - $scorta_delete;
                }
                $descrizione = (isset($_POST['desc'])) ? "'" . $_POST['desc'] . "'" : null;
                $codice_produttore = (isset($_POST['codice_produttore'])) ? "'" . $_POST['codice_produttore'] . "'" : null;
                $corridoio = (isset($_POST['corridoio'])) ? "'" . $_POST['corridoio'] . "'" : null;
                $scaffali = (isset($_POST['scaffali'])) ? "'" . $_POST['scaffali'] . "'" : null;
                $ripiano = (isset($_POST['ripiano'])) ? "'" . $_POST['ripiano'] . "'" : null;
                $marca = (isset($_POST['brand'])) ? "'" . $_POST['brand'] . "'" : null;
                $modello = (isset($_POST['model'])) ? "'" . $_POST['model'] . "'" : null;
                $note = (isset($_POST['note'])) ? "'" . $_POST['note'] . "'" : null;
                $data_modifica = " CURRENT_TIMESTAMP()";
                $id_magazzino = (isset($_POST['id_magazzino'])) ? "'" . $_POST['id_magazzino'] . "'" : null;

                $sql = "UPDATE " . MAIN_DB_PREFIX . "asset as a SET ";
                $sql .= " a.label = " . $etichetta . ",";
                $sql .= " a.scorta_tot = " . $scorta_totale . ",";
                $sql .= " a.descrizione = " . $descrizione . ",";
                $sql .= " a.corridoio = " . $corridoio . "," . " a.scaffali = " . $scaffali . ",";
                $sql .= " a.ripiano = " . $ripiano . "," . " a.note = " . $note . ",";
                $sql .= " a.brand = " . $marca . "," . " a.model = " . $modello . ",";
                $sql .= " a.id_magazzino = " . $id_magazzino . ",";
                $sql .= " a.codice_produttore = " . $codice_produttore . ",";
                $sql .= " a.data_modifica = " . $data_modifica;
                $sql .= " WHERE a.cod_asset= " . "'" . $code_asset . "'";
                $result = $db->query($sql);
                if ($result)
                {
                    $obj_asset = new asset($db);

                    if ($valIncOrDec > 0)
                    {
                        $obj_asset->incrementa_scorta($code_asset);
                    } else if ($valIncOrDec < 0)
                    {
                        $obj_asset->decrementa_scorta($code_asset);
                    }
                }


                $path = DOL_URL_ROOT . '/product/scheda_asset.php?mainmenu=products&cod_asset=' . $code_asset;
                print '<META HTTP-EQUIV="Refresh" CONTENT="0; url=' . $path . '">';
            }
        }
        
        
    }
}

function msgErrore($str_msg)
{
    print '<div class="jnotify-container">';
    print '<div class="jnotify-notification jnotify-notification-error">';
    print '<div class="jnotify-background"></div>';
    print '<a class="jnotify-close">??</a>';
    print '<div class="jnotify-message">';
    print $str_msg;
    print '</div>';
    print '</div>';
    print '</div>';
}
