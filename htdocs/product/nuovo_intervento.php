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
        print '<div class="tabs" data-type="horizontal" data-role="controlgroup">';
        print '<a class="tabTitle">
            
<img border="0" title="" alt="" src="' . $root . '"/theme/eldy/img/object_product.png">
Movimentazione asset
</a>';

        print '<div class="inline-block tabsElem">
<a id="card" class="tabactive tab inline-block" href="' . $root . '/product/nuovo_intervento.php?mainmenu=products&leftmenu=product&type=5" data-role="button">Nuovo intervento</a>
</div>';

        print '<div class="inline-block tabsElem">
<a id="card" class="tab inline-block" href="' . $root . '/product/storico_intervento.php?mainmenu=products&leftmenu=product&type=5" data-role="button">Storico intervento</a>
</div>';





        print '</div>';

        // mie modifiche
        if (GETPOST('delprod'))
            dol_htmloutput_mesg($langs->trans("ProductDeleted", GETPOST('delprod')));

        $param = "&amp;sref=" . $sref . ($sbarcode ? "&amp;sbarcode=" . $sbarcode : "") . "&amp;snom=" . $snom . "&amp;sall=" . $sall . "&amp;tosell=" . $tosell . "&amp;tobuy=" . $tobuy;
        $param.=($fourn_id ? "&amp;fourn_id=" . $fourn_id : "");
        $param.=($search_categ ? "&amp;search_categ=" . $search_categ : "");
        $param.=isset($type) ? "&amp;type=" . $type : "";

        //print_barre_liste($texte, $page, "liste.php", $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords);

        $sql = "SELECT  * ";
        $sql.= " FROM " . MAIN_DB_PREFIX . "asset a";
        $sql.= " WHERE cod_asset = " . "'" . $cod_asset . "'";
        $res = $db->query($sql);
        $rec = $db->fetch_object($rec);
        $redir = DOL_URL_ROOT . '/product/elenco_asset.php';

        if ($action == "Aggiungi HW" || $action == "Chiudi")
        {
            $mag_sorgente = empty($_GET['mag_sorgente']) ? null : $_GET['mag_sorgente'];
            $mag_destinatario = empty($_GET['mag_dest']) ? null : $_GET['mag_dest'];
            $numero_ordine = empty($_GET['dati']['numero_ordine']) ? null : $_GET['dati']['numero_ordine'];
            $causale_tmp = empty($_GET['dati']['causale_trasp']) ? null : $_GET['dati']['causale_trasp'];
            $luogodest_tmp = empty($_GET['dati']['luogo_dest']) ? null : $_GET['dati']['luogo_dest'];
            $trasp_mezzo = empty($_GET['dati']['trasp_mezzo']) ? null : $_GET['dati']['trasp_mezzo'];
            $testo_libero_trasp = empty($_GET['dati']['vettore_nota']) ? null : $_GET['dati']['vettore_nota'];
            $data_tmp = empty($_GET['dati']['data_ritiro']) ? null : $_GET['dati']['data_ritiro'];
            $annotazioni_tmp = empty($_GET['dati']['annotazioni']) ? null : $_GET['dati']['annotazioni'];

            $sql = "CREATE TABLE  tmp_form(temp_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, tmp_magsorg VARCHAR(250),tmp_numero_ordine VARCHAR(250), tmp_magdest VARCHAR(250),tmp_causale VARCHAR(250), tmp_luogo VARCHAR(250),tmp_trasp_mezzo VARCHAR(250), tmp_txt_trasp_mezzo VARCHAR(250), tmp_data VARCHAR(250), tmp_annotazioni VARCHAR(250),tmp_mag_gen VARCHAR(250), tmp_nome_gen VARCHAR(250), tmp_rag_sociale VARCHAR(250), tmp_cap_gen VARCHAR(250),tmp_citta_gen VARCHAR(250),tmp_prov_gen VARCHAR(250),tmp_indirizzo_gen VARCHAR(250), id_user VARCHAR(100))";
            $result = $db->query($sql);
            $query_form = "SELECT COUNT(*) AS count FROM tmp_form WHERE id_user = " . $user_id;
            $res_form = $db->query($query_form);
            if ($res_form)
            {
                $obj_tot = $db->fetch_object($res_form);
                if ($obj_tot->count == 0)
                {

                    $sql = "INSERT INTO tmp_form(";
                    $sql.= "tmp_magsorg";
                    $sql.= ",tmp_magdest";
                    $sql.= ",tmp_numero_ordine";
                    $sql.= ",tmp_causale";
                    $sql.= ",tmp_luogo";
                    $sql.= ",tmp_trasp_mezzo";
                    $sql.= ",tmp_txt_trasp_mezzo";
                    $sql.= ",tmp_data";
                    $sql.= ",tmp_annotazioni";
                    $sql.= ",id_user";

                    $sql.= ") VALUES (";
                    $sql.= "'" . $mag_sorgente . "'" . ",";
                    $sql.= "'" . $mag_destinatario . "'" . ",";
                    $sql.= "'" . $numero_ordine . "'" . ",";
                    $sql.= "'" . $causale_tmp . "'" . ",";
                    $sql.= "'" . $luogodest_tmp . "'" . ",";
                    $sql.= "'" . $trasp_mezzo . "'" . ",";
                    $sql.= "'" . $testo_libero_trasp . "'" . ",";
                    $sql.= "'" . $data_tmp . "'" . ",";
                    $sql.= "'" . $annotazioni_tmp . "'";
                    $sql.= ",'" . $user_id . "'";

                    $sql.= ")";
                    $inserito = $db->query($sql);
                } else
                { // vuol dire che un record per l'utente è stato già inserito, quindi basta solo aggiornare
                    $sql = "UPDATE tmp_form SET"
                            . " tmp_magsorg=" . "'" . $mag_sorgente . "'"
                            . " ,tmp_magdest=" . "'" . $mag_destinatario . "'"
                            . " ,tmp_numero_ordine=" . "'" . $numero_ordine . "'"
                            . " ,tmp_causale=" . "'" . $causale_tmp . "'"
                            . " ,tmp_luogo=" . "'" . $luogodest_tmp . "'"
                            . " ,tmp_trasp_mezzo=" . "'" . $trasp_mezzo . "'"
                            . " ,tmp_txt_trasp_mezzo=" . "'" . $testo_libero_trasp . "'"
                            . " ,tmp_data=" . "'" . $data_tmp . "'"
                            . " ,tmp_annotazioni=" . "'" . $annotazioni_tmp . "'";
                    $sql .= " WHERE id_user = " . $user_id;
                    $aggiornato = $db->query($sql);
                }
            }
        } else if ($action == "Annulla")
        {
            // svuota la tabella temporanea
            //  $svuotato = $db->query($sql);

            $sql = "DELETE FROM tmp_form WHERE id_user = " . $user_id;
            $svuotato = $db->query($sql);

            //$query = "DROP TABLE IF EXISTS tmp_asset";
            //$eliminato = $db->query($query);

            $query = "DELETE FROM tmp_asset WHERE id_user = " . $user_id;
            $eliminato = $db->query($query);

            // $sql = "DROP TABLE tmp_prodotti";
            $sql = "DELETE FROM tmp_prodotti WHERE id_user = " . $user_id;
            $svuotato = $db->query($sql);

            // $query = "DROP TABLE IF EXISTS tmp_prodotti";
            //  $eliminato = $db->query($query);
            if ($user->tipologia != "T")
            {
                $query = "DROP TABLE tmp_altro";
                $res = $db->query($query);
            }
            $main_menu = DOL_URL_ROOT . '/product/nuovo_intervento.php?mainmenu=products';
            print '<META HTTP-EQUIV="Refresh" CONTENT="0; url=' . $main_menu . '">';
        }

        print '<form method="GET" action="' . $_SERVER["PHP_SELF"] . '">';
        print '<table class="border" width="100%">';
        $mag = new magazzino($db);
        $str_mag_sorg = "Spedizione da";

        $lista_magazzino = $mag->getTuttiMagazzino();
        $str_mag_sorg = "Magazzino";

        for ($i = 0; $i < count($lista_magazzino); $i++)
        {
            $magazzino = $lista_magazzino[$i];
            $elem = $magazzino['label'];
            if ($elem == "Zoccali")
            {
                $statutarray = array();
                $statutarray [$magazzino['rowid']] = $elem;
                break;
            }
            $statutarray [$magazzino['rowid']] = $elem;
        }

        print '<tr><td class="fieldrequired">' . $str_mag_sorg . '</td><td colspan="3">';
        $select = '<select id="mag_sorgente" class="flat" name="mag_sorgente">';

        $selected = " ";
        $query = "SELECT * FROM tmp_form WHERE id_user = " . $user_id;
        $ris = $db->query($query);
        $obj_tmp = @$db->fetch_object($ris); // da verificare 

        $trasp_mezzo_option = $obj_tmp->tmp_trasp_mezzo;
        $trasp_mezzo_mittente = "";
        $trasp_mezzo_vettore = "";
        $trasp_mezzo_destinatatio = "";
        switch ($trasp_mezzo_option)
        {
            case 1:
                $trasp_mezzo_mittente = " selected ";
                break;
            case 2:
                $trasp_mezzo_vettore = " selected ";
                break;
            case 3:
                $trasp_mezzo_destinatario = " selected ";
                break;
        }

        $trasp_mezzo_txt = $obj_tmp->tmp_txt_trasp_mezzo;

        $mag_sorgSelezionato = $obj_tmp->tmp_magsorg;
        $mag_destSelezionato = $obj_tmp->tmp_magdest;
        $mag_generico_attivo = $obj_tmp->tmp_mag_gen;
        $select_magGen = "";
        if ($mag_generico_attivo == 2)
        {
            $select_magGen = " selected";
        }
        $testo_libero = "";
        if ($mag_destSelezionato == -1)
        {
            $testo_libero = $obj_tmp->tmp_txt_trasp_mezzo;
        }
        $numero_ordine_tmp = $obj_tmp->tmp_numero_ordine;
        $cauale_tmp = $obj_tmp->tmp_causale;

        $luogo_tmp = $obj_tmp->tmp_luogo;
        $data_tmp = $obj_tmp->tmp_data;
        $annotazioni_tmp = $obj_tmp->tmp_annotazioni;

        foreach ($statutarray as $key => $valore)
        {
            if ($key == $mag_sorgSelezionato)
                $selected = " selected";
            else
                $selected = " ";
            $select .= '<option value=' . $key . $selected . '>' . $valore . '</option>';
        }
        $select .= '</select>';

        //print $form->selectarray('mag_sorgente', $statutarray, GETPOST('mag_sorgente'));
        $select .= '</td></tr>';
        print $select;

        $str_magdest = "Cliente";
        $obj_clienti = new clientiZoccali($db);
        $lista_magazzino = $obj_clienti->getTuttiClienti();

        print '<tr><td class="fieldrequired">' . $str_magdest . '</td><td colspan="3">';
        $select_due = '<select id="mag_dest" class="flat" name="mag_dest">';
        $statutarray_cliente = array();
        for ($i = 0; $i < count($lista_magazzino); $i++)
        {
            $magazzino = $lista_magazzino[$i];
            $elem = $magazzino['INSEGNA'];
            $statutarray_cliente [$magazzino['TERMID']] = $elem;
        }
        //$statutarray['-1'] = "Altro";
        foreach ($statutarray_cliente as $key => $valore)
        {

            if ($key == $mag_destSelezionato)
                $selected = " selected";
            else
                $selected = " ";
            $select_due .= '<option value=' . $key . $selected . '>' . $valore . " - TERMID: " . $key . '</option>';
        }

        $select_due .= '</select>';

        //print $form->selectarray('mag_sorgente', $statutarray, GETPOST('mag_sorgente'));
        /* $altro = " Magazzino generico"; // non modificabile
          $altro .= '<input name="dati[testo_magazzino]" size="40" value="' . $testo_libero . '">';


          $$altro .= '</td></tr>'; */
        print $select_due;



        print '<tr><td class="fieldrequired"  width="20%">' . "Numero ordine" . '</td>'; // non modificabile
        print '<td><input name="dati[numero_ordine]" size="40" value="' . $numero_ordine_tmp . '" required>';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Causale del trasporto" . '</td>'; // non modificabile
        print '<td><input name="dati[causale_trasp]" size="40" value="' . $cauale_tmp . '">';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Luogo di destinazione" . '</td>'; // non modificabile
        print '<td><input name="dati[luogo_dest]" size="40" value="' . $luogo_tmp . '" >';
        print '</td></tr>';

        print '<tr><td>' . "Trasporto a mezzo" . '</td><td colspan="3">';
        print '<select id="trasp_mezzo" class="flat" name="dati[trasp_mezzo]">';
        print'<option value="1" ' . $trasp_mezzo_mittente . '>Mittente</option>';
        print'<option value="2" ' . $trasp_mezzo_vettore . '>Vettore</option>';
        print'<option value="3" ' . $trasp_mezzo_destinatario . '>Destinatario</option>';
        print '</select>';
        print '<input name="dati[vettore_nota]" size="16" value="' . $trasp_mezzo_txt . '">';
        print '</td></tr>';

        print'<script>
  $(function() {
    $( "#datepicker" ).datepicker();
  });
  </script>';
        print '<tr><td>' . "Data di ritiro" . '</td><td colspan="3">';
        print '<input type="text" name="dati[data_ritiro]" id="datepicker" value="' . $data_tmp . '" ></td>';


        print '<tr><td width="20%">' . "Annotazione" . '</td>'; //oni non modificabile
        print '<td><input name="dati[annotazioni]" size="40" value="' . $annotazioni_tmp . '" >';
        print '</td></tr>';

        print '</table>';
        print '<br>';
        $path_pop = DOL_URL_ROOT . "/product/popup_hw.php?mainmenu=products";
        print "<script>
function inviaform(){
        var path = '$path_pop';
	window.open(path,'popupname','width=900,height=500,toolbar=no, location=yes,status=no,menubar=no,scrollbars=yes');
	document.getElementById('nomeform').submit();
}
</script>";


        $path_pop_prodotti = DOL_URL_ROOT . "/product/popup_prodotti.php";
        print "<script>
function inviaprodotti(){
        var path = '$path_pop_prodotti';
	window.open(path,'popupname','width=900,height=500,toolbar=no, location=yes,status=no,menubar=no,scrollbars=yes');
	document.getElementFById('nomeform').submit();
}
</script>";

        print '<center> <input type="submit" class="button" name="action"  value="' . "Aggiungi HW" . '" onclick="inviaform();"">';


        print '<input type="submit"  class="button"  name="action" value="' . "Annulla" . '"></center> ';


        if ($action != "Fine")
        {
            if ($user->tipologia != "T")
            {
                if ($action == "Elimina riga")
                {
                    $altro_daEliminare = empty($_GET['checkbox_altro']) ? "" : $_GET['checkbox_altro'];
                    if (!empty($altro_daEliminare))
                    {
                        $sql = "SELECT temp_id FROM tmp_altro";
                        $result_tmp_altro = $db->query($sql);
                        if ($result_tmp_altro)
                        {
                            $j = 0;
                            $array_righe_db = array();
                            $k = 0;
                            while ($obj_altro = $db->fetch_array(MYSQLI_ASSOC))
                            {
                                $array_righe_db[$k] = $obj_altro['temp_id'];
                                $k++;
                            }

                            $righe_altro = $array_righe_db;
                        }
                        $righe_altro = @array_diff($righe_altro, $altro_daEliminare);
                        $righe_altro = array_values($righe_altro); // allinea gli indici
                        if (empty($righe_altro))
                        {
                            $query = "DROP TABLE IF EXISTS tmp_altro";
                            $eliminato = $db->query($query);
                        } else
                        {

                            $my_where = "";
                            $n = count($altro_daEliminare);
                            for ($i = 0; $i < $n; $i++)
                            {
                                $row_id = $altro_daEliminare[$i];
                                $OR = " ";
                                if (($i + 1) == $n)
                                {
                                    $my_where .= "temp_id = " . $row_id . $ORS;
                                } else
                                {
                                    $my_where .= "temp_id = " . $row_id . " OR ";
                                }
                            }

                            $sql = "DELETE FROM tmp_altro";
                            $sql .= " WHERE $my_where";
                            $db->query($sql);
                        }
                    }
                }
            }

            $sql = "SELECT * FROM tmp_asset WHERE id_user = " . $user_id;
            $result_tmp_asset = $db->query($sql);
            $flag_asset_sel = 1;
            if ($result_tmp_asset)
            {

                $obj_asset = $db->fetch_array(MYSQLI_ASSOC);
                if (empty($obj_asset))
                {
                    $flag_asset_sel = 0;
                }
                $asset_popup = json_decode($obj_asset['tmp_idasset']);

                print '<br><table class="noborder" width="100%">';

                if ($flag_asset_sel == 1)
                {
                    print '<tr class="liste_titre"><td width="10%" colspan="5">' . "Seleziona asset" . '</td>';
                    print '<td align="right">' . "Codice asset" . '</td>';
                    print '<td align="right">' . "Etichetta" . '</td>';
                    print '<td align="right">' . "Tot.pezzi HW" . '</td>';
                    print '<td align="right">' . "Seleziona quantita" . '</td>';
                    print '</tr>';
                }

                $obj_asset = new asset($db);
                if ($action == "Elimina")
                {
                    $daEliminare = empty($_GET['checkbox_asset']) ? "" : $_GET['checkbox_asset'];
                    if (!empty($daEliminare))
                    {
                        $asset_popup = @array_diff($asset_popup, $daEliminare);
                        $asset_popup = array_values($asset_popup); // allinea gli indici

                        $encode = json_encode($asset_popup);
                        $sql = "UPDATE tmp_asset SET tmp_idasset=" . "'" . $encode . "'";
                        $sql .= " WHERE id_user = " . $user_id;
                        $db->query($sql);
                    }
                }
                if (!empty($asset_popup))
                {
                    for ($i = 0; $i < count($asset_popup); $i++)
                    {
                        $asset = $asset_popup[$i];
                        if ($user->tipologia == "T")
                        {

                            $obj_mag_generico = new magazzino($db);
                            $magazzino_proprio = $obj_mag_generico->getMagazzinoUser($user->id, "fk_user");
                            $id_m = $magazzino_proprio[0]['rowid']; // id magazzino tecnico

                            $obj_mag_asset = new asset($db);
                            $asset_suo = $obj_mag_asset->assetTecnici($asset, $id_m);
                            if ($asset_suo == 0) // se è zero non è suo
                            {
                                continue;
                            }
                        }
                        $arra_db_asset = $obj_asset->getAsset("", $asset);
                        $db_asset = $arra_db_asset[0];
                        print '<tr ' . true . '>';
                        $codice_asset = $db_asset['cod_asset'];
                        $etichetta = $db_asset['label'];
                        $scorta_tot = $db_asset['scorta_tot'];
                        $riemi_checkbox = '<input type="checkbox" name="checkbox_asset[]" value=' . '"' . $asset . '"' . '> <br>';

                        print '<td colspan="5">' . $riemi_checkbox . '</td>';
                        print '<td align="right">' . $asset . '</td>';
                        print '<td align="right">' . $etichetta . '</td>';
                        print '<td align="right">' . $scorta_tot . '</td>';
                        //  print '<td align="right"><input id="nights" type="number" name="scorta_richiesta[' . $prodotto->rowid . ']" maxlength="3" value="1" step="1" max="'.$stock_product->reel.'" min="1"></td>';
                        print '<td align="right"><input id="nights" type="number" name="scorta_richiesta[' . $codice_asset . ']" maxlength="3" value="1" step="1" max="' . $scorta_tot . '" min="1"></td>';

                        print '</tr>';
                    }
                    print '</table>';
                    print '<br>';
                }


                // $query = "DROP TABLE IF EXISTS tmp_asset";
                //$eliminato = $db->query($query);
            } else
            {
                $flag_asset_sel = 0;
            }
            $query = "SELECT tmp_codiceprodotto FROM tmp_prodotti WHERE id_user = " . $user_id;
            $res = $db->query($query);
            $flag_res = 1;
            if ($res)
            {
                $obj_prodotti = $db->fetch_array(MYSQLI_ASSOC);

                $codice_prodotti_selez = json_decode($obj_prodotti['tmp_codiceprodotto']);
                if (empty($codice_prodotti_selez))
                {
                    $flag_res = 0;
                }

                if ($action == "Elimina")
                {
                    $daEliminare = empty($_GET['checkbox_prodotti']) ? "" : $_GET['checkbox_prodotti'];
                    if (!empty($daEliminare))
                    {
                        $codice_prodotti_selez = @array_diff($codice_prodotti_selez, $daEliminare);
                        $codice_prodotti_selez = array_values($codice_prodotti_selez); // allinea gli indici
                        if (empty($codice_prodotti_selez))
                        {
                            $query = "DROP TABLE IF EXISTS tmp_prodotti";
                            $eliminato = $db->query($query);
                        } else
                        {
                            $encode = json_encode($codice_prodotti_selez);
                            $sql = "UPDATE tmp_prodotti SET tmp_codiceprodotto=" . "'" . $encode . "'";
                            $sql .= " WHERE id_user = " . $user_id;
                            $db->query($sql);
                        }
                    }
                }


                if (!empty($codice_prodotti_selez))
                {
                    print '<br><table class="noborder" width="100%">';

                    print '<tr class="liste_titre"><td width="10%" colspan="4">' . "Seleziona prodotto" . '</td>';
                    print '<td align="right">' . "Codice prodotto" . '</td>';
                    print '<td align="right">' . "Etichetta" . '</td>';
                    print '<td align="right">' . "Scorta disponibile" . '</td>';
                    print '<td align="right">' . "N° di unità da movimentare" . '</td>';

                    print '</tr>';
                    for ($i = 0; $i < count($codice_prodotti_selez); $i++)
                    {
                        $id_prodotto = $codice_prodotti_selez[$i];
                        $obj_scorta_prodotto = new myScortaprodotto($db);
                        $stock_product = $obj_scorta_prodotto->getScorta($id_prodotto, $mag_sorgSelezionato);
                        $prodotto = getProdotto($db, $id_prodotto);
                        $riemi_checkbox_prodotti = '<input type="checkbox" name="checkbox_prodotti[]" value=' . '"' . $prodotto->rowid . '"' . '> <br>';
                        print '<td colspan="4">' . $riemi_checkbox_prodotti . '</td>';
                        print '<td align="right">' . $prodotto->ref . '</td>';
                        print '<td align="right">' . $prodotto->label . '</td>';
                        print '<td align="right">' . $stock_product->reel . '</td>';
                        print '<td align="right"><input id="nights" type="number" name="scorta_richiesta[' . $prodotto->rowid . ']" maxlength="3" value="1" step="1" max="' . $stock_product->reel . '" min="1"></td>';
                        // print '<td align="right">' . '<input type="text" name="scorta_richiesta[' . $prodotto->rowid . ']">' . '</td>';
                        print '</tr>';
                    }
                }
                print '</table>';
            } else
            {
                $flag_res = 0;
            }
            $flag_righe = 0;



            if ($flag_res == 1 || $flag_asset_sel == 1 || $flag_righe == 1)
            {
                // print $flag_res . "  " . $flag_asset_sel . "  " . $flag_righe;
                print '<br>';
                print '<center> <input type="submit" class="button" name="action"  value="' . "Aggiungi HW" . '" onclick="inviaform()">';

                if ($user->tipologia != "T")
                //  print '<input type="submit" class="button" name="action"  value="' . "Altro" . '">';
                    print '<input type="submit" class="button" name="action"  value="' . "Elimina" . '">';
                print '<input type="submit" class="button" name="action"  value="' . "Annulla" . '">';
                print '<input type="submit" class="button" name="action"  value="' . "Fine" . '"> </center>';
            }
            print '</form>';
        }
    }
}

// processo l'operazione

if ($action == "Fine")
{

    $dati_form = array();
    $numero_ordine = $_GET['dati']['numero_ordine'];
    $causale_trasp = $_GET['dati']['causale_trasp'];
    $luogo_dest = $_GET['dati']['luogo_dest'];
    $trasporto_mezzo = $_GET['dati']['trasp_mezzo'];
    $vettore_nota = $_GET['dati']['vettore_nota'];
    $data_ritiro = $_GET['dati']['data_ritiro'];
    $annotazioni = $_GET['dati']['annotazioni'];

    $mag_sorgente = $_GET['mag_sorgente'];
    $mag_dest = $_GET['mag_dest'];

    $dati_form['numero_ordine'] = $numero_ordine;
    $dati_form['causale_trasp'] = $causale_trasp;
    $dati_form['luogo_dest'] = $luogo_dest;
    $dati_form['trasporto_mezzo'] = $trasporto_mezzo;
    $dati_form['vettore_nota'] = $vettore_nota;
    $dati_form['mag_sorgente'] = $mag_sorgente;
    $dati_form['mag_dest'] = $mag_dest;
    $dati_form['data_ritiro'] = $data_ritiro;
    $dati_form['annotazioni'] = $annotazioni;

    $query = "SELECT tmp_idasset FROM tmp_asset WHERE id_user = " . $user_id;

    $res = $db->query($query);
    if ($res)
    {
        $obj = $db->fetch_object($res);
        $array_assetDB = json_decode($obj->tmp_idasset);
        $dati_form['checkbox_asset'] = $array_assetDB;
        // impostare gli asset come stato fisico a transito
        $scorta_richiesta_GET = isset($_GET['scorta_richiesta']) ? $_GET['scorta_richiesta'] : "";
        $array_scorte_richieste = array();
        foreach ($scorta_richiesta_GET as $id_asset => $scorta)
        {
            if (empty($scorta))
            {
                continue;
            }
            $array_scorte_richieste[$id_asset] = $scorta;
        }
        $encode = json_encode($array_scorte_richieste); // serve per salvare nella tabella movintazioni
        $dati_form['scorta_richieste'] = $encode;
        $dati_form['scorta_richieste_pdf'] = $array_scorte_richieste;
        for ($i = 0; $i < count($dati_form['checkbox_asset']); $i++)
        {
            $code_asset = $dati_form['checkbox_asset'][$i]; // per ciascun asset
            $obj_asset = new asset($db);
            $array_asset = $obj_asset->getMyAsset($code_asset);
            $scorta_totale = $array_asset['scorta_tot'];
            $tot_scorta_utilizzati = $array_asset['scorta_utilizzati'];
            $scorta_richiesta = $array_scorte_richieste[$code_asset];
            $nuova_scorta_totale = (int) ($scorta_totale - $scorta_richiesta); // pezzi rimanti

            $sql = "UPDATE " . MAIN_DB_PREFIX . "asset SET scorta_tot=" . $nuova_scorta_totale; // salvo lo stato fisico in una variabile temporanea in modo che si possa ripristinare
            $sql .= " WHERE cod_asset LIKE '" . $code_asset . "'";
            $aggiornato = $db->query($sql);

            $nuovo_tot_scorta_utilizzati = $tot_scorta_utilizzati + $scorta_richiesta; // totale complessivo di scorta utilizzati
            $sql = "UPDATE " . MAIN_DB_PREFIX . "asset SET scorta_utilizzati=" . $nuovo_tot_scorta_utilizzati; // salvo lo stato fisico in una variabile temporanea in modo che si possa ripristinare
            $sql .= " WHERE cod_asset LIKE '" . $code_asset . "'";
            $aggiornato = $db->query($sql);
        }
    }
    $data_odierna = date("d-m-Y");
    $dati_form['data_oggi'] = $data_odierna;
    $dati_form['data_convalida'] = date("d/m/Y");
    ;

    $movimentazione = new assetmovement($db);
    $movimentazione->setDatiMovimentazioneAsset($dati_form, $user);
    $insert_form = $movimentazione->storeInterventoZoc();


    $myddt = new myPdfIntervento($db);
    $myddt->setDati($dati_form);
    $pdf = $myddt->getCrea();

    $file = DOL_DOCUMENT_ROOT . "/product/ordini/" . $dati_form['numero_ordine'] . ".pdf"; // crea l'asset con l'id ddt, in questo modo sarà univoco

    $pdf->Output($file, 'F');

    // gestione tracking
    $array_tracking = array();
    //$array_tracking['codice_asset'] = $array_scorte_richieste;
    if (!empty($array_scorte_richieste))
    {

        foreach ($array_scorte_richieste as $key => $val)
        {

            $array_tracking['azione'] = "modifica magazzino";
            $array_tracking['user'] = $user->id;
            $array_tracking['riferimento'] = $dati_form['numero_ordine'];
            $array_tracking['codice_asset'] = $key;
            $array_tracking['quantita'] = $val;
            $array_tracking['cliente'] = $mag_dest;
            $obj_track = new myTracking($db);
            $track_creato = $obj_track->zoccali_nuovoTracking($array_tracking);
        }
    }



    $query = "DELETE FROM tmp_asset WHERE id_user = " . $user_id;
    $res = $db->query($query);

    $sql = "DELETE FROM tmp_form WHERE id_user = " . $user_id;
    $svuotato = $db->query($sql);


    //$path = DOL_URL_ROOT . '/product/confconvalida.php?mainmenu=products' . "&id=" . $codice_movimentazione;
    $path = DOL_URL_ROOT . '/product/storico_intervento.php?mainmenu=products';

    print '<META HTTP-EQUIV="Refresh" CONTENT="0; url=' . $path . '">';
}

function getProdotto($db, $id_prodotto = 0)
{
    if (empty($id_prodotto))
    {
        return false;
    }
    $sql = "SELECT * ";
    $sql .= " FROM " . MAIN_DB_PREFIX . "product as p ";
    $sql .= " WHERE p.rowid = " . $id_prodotto;
    $ris = $db->query($sql);
    if ($ris)
    {
        $obj_prodotto = $db->fetch_object(MYSQLI_ASSOC);
        if (empty($obj_prodotto))
        {
            return false;
        }
        return $obj_prodotto;
    }
    return false;
}

?>
  