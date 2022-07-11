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
if ($page == -1) {
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
if (!empty($canvas)) {
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

if (isset($_POST["button_removefilter_x"])) {
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

if (is_object($objcanvas) && $objcanvas->displayCanvasExists($action)) {
    $objcanvas->assign_values('list');       // This must contains code to load data (must call LoadListDatas($limit, $offset, $sortfield, $sortorder))
    $objcanvas->display_canvas('list');    // This is code to show template
} else {
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
    if ($conf->global->MAIN_MULTILANGS) { // si l'option est active
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "product_lang as pl ON pl.fk_product = p.rowid AND pl.lang = '" . $langs->getDefaultLang() . "'";
    }
    $sql.= ' WHERE p.entity IN (' . getEntity('product', 1) . ')';
    if ($sall) {
        // For natural search
        $params = array('p.ref', 'p.label', 'p.description', 'p.note');
        // multilang
        if ($conf->global->MAIN_MULTILANGS) { // si l'option est active
            $params[] = 'pl.description';
            $params[] = 'pl.note';
        }
        if (!empty($conf->barcode->enabled)) {
            $params[] = 'p.barcode';
        }
        $sql .= natural_search($params, $sall);
    }
    // if the type is not 1, we show all products (type = 0,2,3)
    if (dol_strlen($type)) {
        if ($type == 1)
            $sql.= " AND p.fk_product_type = '1'";
        else
            $sql.= " AND p.fk_product_type <> '1'";
    }
    if ($sref)
        $sql .= natural_search('p.ref', $sref);
    if ($sbarcode)
        $sql .= natural_search('p.barcode', $sbarcode);
    if ($snom) {
        $params = array('p.label');
        // multilang
        if ($conf->global->MAIN_MULTILANGS) { // si l'option est active
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
    if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
        $result = $db->query($sql);
        $nbtotalofrecords = $db->num_rows($result);
    }

    $sql.= $db->order($sortfield, $sortorder);
    $sql.= $db->plimit($limit + 1, $offset);

    dol_syslog("product:list.php: sql=" . $sql);
    $resql = $db->query($sql);
    if ($resql) {
        $num = $db->num_rows($resql);

        $i = 0;

        if ($num == 1 && ($sall || $snom || $sref || $sbarcode) && $action != 'list') {
            $objp = $db->fetch_object($resql);
            header("Location: fiche.php?id=" . $objp->rowid);
            exit;
        }

        $helpurl = '';
        if (isset($type)) {
            if ($type == 0) {
                $helpurl = 'EN:Module_Products|FR:Module_Produits|ES:M&oacute;dulo_Productos';
            } else if ($type == 1) {
                $helpurl = 'EN:Module_Services_En|FR:Module_Services|ES:M&oacute;dulo_Servicios';
            }
        }

        llxHeader('', $title, $helpurl, '');
        $root = DOL_URL_ROOT;
        print '<div class="fiche">';
        print '<div class="tabs" data-type="horizontal" data-role="controlgroup">';
        print '<a class="tabTitle">
            
<img border="0" title="" alt="" src="'.$root.'"/theme/eldy/img/object_product.png">
Movimentazione asset
</a>';

        print '<div class="inline-block tabsElem">
<a id="card" class="tabactive tab inline-block" href="' . $root . '/product/movimentazione.php?leftmenu=product&type=5" data-role="button">Nuova movimentazione</a>
</div>';


        print '<div class="inline-block tabsElem">
<a id="price" class="tab inline-block" href="' . $root . '/product/daconvalidare.php?leftmenu=product&type=6&id=3" data-role="button">Da convalidare</a>
</div>';

        print '<div class="inline-block tabsElem">
<a id="price" class="tab inline-block" href="' . $root . '/product/storico.php?leftmenu=product&type=6&id=4" data-role="button">Storico</a>
</div>';

        print '</div>';

        // mie modifiche
        if (GETPOST('delprod'))
            dol_htmloutput_mesg($langs->trans("ProductDeleted", GETPOST('delprod')));

        $param = "&amp;sref=" . $sref . ($sbarcode ? "&amp;sbarcode=" . $sbarcode : "") . "&amp;snom=" . $snom . "&amp;sall=" . $sall . "&amp;tosell=" . $tosell . "&amp;tobuy=" . $tobuy;
        $param.=($fourn_id ? "&amp;fourn_id=" . $fourn_id : "");
        $param.=($search_categ ? "&amp;search_categ=" . $search_categ : "");
        $param.=isset($type) ? "&amp;type=" . $type : "";

        print_barre_liste($texte, $page, "liste.php", $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords);

        $sql = "SELECT  * ";
        $sql.= " FROM " . MAIN_DB_PREFIX . "asset a";
        $sql.= " WHERE cod_asset = " . "'" . $cod_asset . "'";
        $res = $db->query($sql);
        $rec = $db->fetch_object($rec);
        $redir = DOL_URL_ROOT . '/product/elenco_asset.php';

        if ($action == "Aggiungi asset") {
            $setco = "d";
            $mag_sorgente = empty($_GET['mag_sorgente']) ? null : $_GET['mag_sorgente'];
            $mag_destinatario = empty($_GET['mag_dest']) ? null : $_GET['mag_dest'];
            $causale_tmp = empty($_GET['dati']['causale_trasp']) ? null : $_GET['dati']['causale_trasp'];
            $luogodest_tmp = empty($_GET['dati']['luogo_dest']) ? null : $_GET['dati']['luogo_dest'];
            $data_tmp = empty($_GET['dati']['data_ritiro']) ? null : $_GET['dati']['data_ritiro'];
            $annotazioni_tmp = empty($_GET['dati']['annotazioni']) ? null : $_GET['dati']['annotazioni'];
            if (!empty($mag_sorgente)) {
                $query = "DROP TABLE IF EXISTS tmp_form";
                $eliminato = $db->query($query);

                $sql = "CREATE TABLE  tmp_form(temp_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, tmp_magsorg VARCHAR(250), tmp_magdest VARCHAR(250),tmp_causale VARCHAR(250), tmp_luogo VARCHAR(250), tmp_data VARCHAR(250), tmp_annotazioni VARCHAR(250))";
                $result = $db->query($sql);
                if ($result) {
                    $encode = json_encode($_REQUEST['checkbox_asset']);
                    $sql = "INSERT INTO tmp_form(";
                    $sql.= "tmp_magsorg,";
                    $sql.= "tmp_magdest,";
                    $sql.= "tmp_causale,";
                    $sql.= "tmp_luogo,";
                    $sql.= "tmp_data,";
                    $sql.= "tmp_annotazioni";
                    $sql.= ") VALUES (";
                    $sql.= "'" . $mag_sorgente . "'" . ",";
                    $sql.= "'" . $mag_destinatario . "'" . ",";
                    $sql.= "'" . $causale_tmp . "'" . ",";
                    $sql.= "'" . $luogodest_tmp . "'" . ",";
                    $sql.= "'" . $data_tmp . "'" . ",";
                    $sql.= "'" . $annotazioni_tmp . "'";
                    $sql.= ")";
                    $inserito = $db->query($sql);
                }
            }
        } else if ($action == "Annulla") {
            // svuota la tabella temporanea
            $sql = "TRUNCATE tmp_form";
            $svuotato = $db->query($sql);
            $query = "DROP TABLE IF EXISTS tmp_asset";
            $eliminato = $db->query($query);
        }

        print '<form method="GET" action="' . $_SERVER["PHP_SELF"] . '">';
        print '<table class="border" width="100%">';
        $mag = new magazzino($db);
        $lista_magazzino = $mag->getTuttiMagazzino();
        for ($i = 0; $i < count($lista_magazzino); $i++) {
            $magazzino = $lista_magazzino[$i];
            $elem = $magazzino['label'];
            $statutarray [$magazzino['rowid']] = $elem;
        }
        print '<tr><td class="fieldrequired">' . "Magazzino di origine" . '</td><td colspan="3">';
        $select = '<select id="mag_sorgente" class="flat" name="mag_sorgente">';

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

        foreach ($statutarray as $key => $valore) {
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

        print '<tr><td class="fieldrequired">' . "Magazzino di destinazione" . '</td><td colspan="3">';
        $select_due = '<select id="mag_sorgente" class="flat" name="mag_dest">';

        foreach ($statutarray as $key => $valore) {
            if ($key == $mag_destSelezionato)
                $selected = " selected";
            else
                $selected = " ";
            $select_due .= '<option value=' . $key . $selected . '>' . $valore . '</option>';
        }
        $select_due .= '</select>';

        //print $form->selectarray('mag_sorgente', $statutarray, GETPOST('mag_sorgente'));
        $select_due .= '</td></tr>';
        print $select_due;

        //print $form->selectarray('mag_dest', $statutarray, GETPOST('mag_dest'));

        print '<tr><td width="20%">' . "Causale del trasporto" . '</td>'; // non modificabile
        print '<td><input name="dati[causale_trasp]" size="16" value="' . $cauale_tmp . '">';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Luogo di destinazione" . '</td>'; // non modificabile
        print '<td><input name="dati[luogo_dest]" size="16" value="' . $luogo_tmp . '" >';
        print '</td></tr>';

        $trasporto = array("1" => "Mittente", "2" => "Vettore", "3" => "Destinatario");
        print '<tr><td>' . "Trasporto a mezzo" . '</td><td colspan="3">';
        print $form->selectarray('trasp_mezzo', $trasporto, GETPOST('trasp_mezzo'));
        print '<input name="vettore_nota" size="16" value="">';
        print '</td></tr>';

        print'<script>
  $(function() {
    $( "#datepicker" ).datepicker();
  });
  </script>';
        print '<tr><td>' . "Data di ritiro" . '</td><td colspan="3">';
        print '<input type="text" name="dati[data_ritiro]" id="datepicker" value="' . $data_tmp . '" ></td>';


        print '<tr><td width="20%">' . "Annota" . '</td>'; //oni non modificabile
        print '<td><input name="dati[annotazioni]" size="16" value="' . $annotazioni_tmp . '" >';
        print '</td></tr>';

        print '</table>';
        print '<br>';
        $path_pop = DOL_URL_ROOT . "/product/popup_asset.php";
        print "<script>
function inviaform(){
        var path = '$path_pop';
	window.open(path,'popupname','width=400,height=400');
	document.getElementById('nomeform').submit();
}
</script>";
        print '<center> <input type="submit" class="button" name="action"  value="' . "Aggiungi asset" . '" onclick="inviaform();"">';
        print '<input type="submit"  class="button"  name="action" value="' . "Annulla" . '"></center> ';
    }

    $sql = "SELECT * FROM tmp_asset";
    $result = $db->query($sql);
    if ($result) {
        $obj_asset = $db->fetch_array(MYSQLI_ASSOC);
        $asset_popup = json_decode($obj_asset['tmp_idasset']);

        print '<br><table class="noborder" width="100%">';

        print '<tr class="liste_titre"><td width="10%" colspan="4">' . "Seleziona asset" . '</td>';
        print '<td align="right">' . "Codice asset" . '</td>';
        print '<td align="right">' . "Etichetta" . '</td>';
        print '<td align="right">' . "Stato fisico" . '</td>';
        print '<td align="right">' . "Stato tecnico" . '</td>';

        print '</tr>';
        $obj_asset = new asset($db);
        if ($action == "Elimina") {
            $daEliminare = empty($_GET['checkbox_asset']) ? "" : $_GET['checkbox_asset'];
            $asset_popup = @array_diff($asset_popup, $daEliminare);
            $asset_popup = array_values($asset_popup); // allinea gli indici
            if (empty($asset_popup)) {
                $query = "DROP TABLE IF EXISTS tmp_asset";
                $eliminato = $db->query($query);
            } else {
                $encode = json_encode($asset_popup);
                $sql = "UPDATE tmp_asset SET tmp_idasset=" . "'" . $encode . "'";
                $sql .= " WHERE temp_id = 1";
                $db->query($sql);
            }
        }
        if (!empty($asset_popup)) {
            for ($i = 0; $i < count($asset_popup); $i++) {
                $asset = $asset_popup[$i];
                $arra_db_asset = $obj_asset->getAsset("", $asset);
                $db_asset = $arra_db_asset[0];
                print '<tr ' . true . '>';
                $etichetta = $db_asset['label'];
                $stato_fisico = $db_asset['stato_fisico'];
                $str_stato_fisico = "";
                switch ($stato_fisico) {
                    case "1":
                        $str_stato_fisico = "Giacenza";
                        break;
                    case "2":
                        $str_stato_fisico = "In uso";
                        break;
                    case "3":
                        $str_stato_fisico = "In transito";
                        break;
                    case "4":
                        $str_stato_fisico = "In lab";
                        break;
                    case "5":
                        $str_stato_fisico = "Dismesso";
                        break;
                }
                $str_stato_tecnico = "";
                switch ($stato_fisico) {
                    case "1":
                        $str_stato_tecnico = "Nuovo";
                        break;
                    case "2":
                        $str_stato_tecnico = "Ricondizionato";
                        break;
                    case "3":
                        $str_stato_tecnico = "Guasto";
                        break;
                    case "4":
                        $str_stato_tecnico = "Sconosciuto";
                        break;
                }
                $stato_tecnico = $db_asset['stato_tecnico'];
                $riemi_checkbox = '<input type="checkbox" name="checkbox_asset[]" value=' . '"' . $asset . '"' . '> <br>';

                print '<td colspan="4">' . $riemi_checkbox . '</td>';
                print '<td align="right">' . $asset . '</td>';
                print '<td align="right">' . $etichetta . '</td>';
                print '<td align="right">' . $str_stato_fisico . '</td>';
                print '<td align="right">' . $str_stato_tecnico . '</td>';
                print '</tr>';
            }
            print '</table>';
            print '<br>';
            print '<center> <input type="submit" class="button" name="action"  value="' . "Aggiungi asset" . '" onclick="inviaform()">';

            print '<input type="submit" class="button" name="action"  value="' . "Elimina" . '">';
            print '<input type="submit" class="button" name="action"  value="' . "Annulla" . '">';
            print '<input type="submit" class="button" name="action"  value="' . "Fine" . '"> </center>';

            print '</form>';
        }
        // $query = "DROP TABLE IF EXISTS tmp_asset";
        //$eliminato = $db->query($query);
    }
}

// processo l'operazione

if ($action == "Fine") {

    $dati_form = array();
    $annotazioni = $_GET['dati']['annotazioni'];
    $causale_trasp = $_GET['dati']['causale_trasp'];
    $data_ritiro = $_GET['dati']['data_ritiro'];
    $luogo_dest = $_GET['dati']['luogo_dest'];
    $trasporto_mezzo = $_GET['trasp_mezzo'];
    $vettore_nota = $_GET['vettore_nota'];
    $mag_sorgente = $_GET['mag_sorgente'];
    $mag_dest = $_GET['mag_dest'];

    $dati_form['annotazioni'] = $annotazioni;
    $dati_form['causale_trasp'] = $causale_trasp;
    $dati_form['luogo_dest'] = $luogo_dest;
    $dati_form['trasporto_mezzo'] = $trasporto_mezzo;
    $dati_form['vettore_nota'] = $vettore_nota;
    $dati_form['mag_sorgente'] = $mag_sorgente;
    $dati_form['mag_dest'] = $mag_dest;
    $dati_form['data_ritiro'] = $data_ritiro;

    $query = "SELECT tmp_idasset FROM tmp_asset ";
    $res = $db->query($query);
    $obj = $db->fetch_object($res);
    $array_assetDB = json_decode($obj->tmp_idasset);
    $dati_form['checkbox_asset'] = $array_assetDB;
    // impostare gli asset come stato fisico a transito
    for ($i = 0; $i < count($dati_form['checkbox_asset']); $i++) {
        $cod_asset_damodificare = "'%" . $dati_form['checkbox_asset'][$i] . "%'"; // per ciascun asset
        $sql = "UPDATE " . MAIN_DB_PREFIX . "asset SET tmp_stato_fisico=stato_fisico"; // salvo lo stato fisico in una variabile temporanea in modo che si possa ripristinare
        $sql .= " WHERE cod_asset LIKE " . $cod_asset_damodificare;
        $aggiornato = $db->query($sql);

        $sql = "UPDATE " . MAIN_DB_PREFIX . "asset SET stato_fisico=" . "'3'"; //imposto lo stato fisico in transito
        $sql .= " WHERE cod_asset LIKE " . $cod_asset_damodificare;
        $aggiornato = $db->query($sql);

        //aggiorno anche l'id del magazzino dove deve essere spostato
        $sql = "SELECT id_magazzino FROM " . MAIN_DB_PREFIX . "asset ";
        $sql .= " WHERE cod_asset LIKE " . $cod_asset_damodificare;
        $ris = $db->query($sql);
        $obj_idmag = $db->fetch_object($ris);
        $id_magSorgente = $obj_idmag->id_magazzino;

        $sql = "UPDATE " . MAIN_DB_PREFIX . "asset SET id_magazzino=" . $dati_form['mag_dest']; // salvo lo stato fisico in una variabile temporanea in modo che si possa ripristinare
        $sql .= " WHERE cod_asset LIKE " . $cod_asset_damodificare;
        $aggiornato = $db->query($sql);

        $sql = "UPDATE " . MAIN_DB_PREFIX . "asset SET tmp_magdest=" . $id_magSorgente; // salvo id_magazzino originale, in modo da ripristinare successivamente 
        $sql .= " WHERE cod_asset LIKE " . $cod_asset_damodificare;
        $aggiornato = $db->query($sql);
    }

    /*


      $id_movimentati = $movimentazione->getIdMovimentazione();

     */
    $movimentazione = new assetmovement($db);
    $movimentazione->setDatiMovimentazioneAsset($dati_form);
    $insert_form = $movimentazione->storeForm();

    if ($insert_form) {
        //$obj_ddt = new myddt($db);
        //$id_ddt = $obj_ddt->insertMyDDT(); // "ast-numero

        $query = "SELECT * FROM " . MAIN_DB_PREFIX . "form_assetmove";
        $query .= " WHERE id =" . $insert_form;
        $ris_search = $db->query($query);
        if ($ris_search) {
            $obj_assetmove = $db->fetch_object($ris_search);
            $id_ddt = $obj_assetmove->id_ddt;
        }
        // associioo all'asset id movimentazione
        $assoc_assetMovim = array();
        // disegno del pdf
        $myddt = new myPdf($db);
        $dati_form['id_ddt'] = $id_ddt;
        $data_odierna = date("d-m-Y");
        $dati_form['data_oggi'] = $data_odierna;
        $myddt->setDati($dati_form);
        $pdf = $myddt->getCrea();

        $file = DOL_DOCUMENT_ROOT . "/product/ddt/" . $id_ddt . ".pdf"; // crea l'asset con l'id ddt, in questo modo sarà univoco

        $pdf->Output($file, 'F');

        $query = "DROP TABLE tmp_asset";
        $res = $db->query($query);

        $sql = "TRUNCATE tmp_form";
        $svuotato = $db->query($sql);

        $path = DOL_URL_ROOT . '/product/daconvalidare.php';
        print '<META HTTP-EQUIV="Refresh" CONTENT="0; url=' . $path . '">';
    }
}
 