<?php

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/product/stock/class/entrepot.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.product.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myFamily.php';
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myAsset.php';
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myMagazzino.php';
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myTracking.php';
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myUtilizzati.php';

if (!empty($conf->categorie->enabled))
    require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';

$langs->load("products");
$langs->load("stocks");
$langs->load("suppliers");

$action = GETPOST('action');
$action_crea = GETPOST('action_crea');

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

$limit = $conf->liste_limit;

$canvas = GETPOST("canvas");
$objcanvas = '';
if (!empty($canvas))
{
    require_once DOL_DOCUMENT_ROOT . '/core/class/canvas.class.php';
    $objcanvas = new Canvas($db, $action);
    $objcanvas->getCanvas('product', 'list', $canvas);
}
/*
  // Security check
  if ($type == '0')
  $result = restrictedArea($user, 'produit', '', '', '', '', '', $objcanvas);
  else if ($type == '1')
  $result = restrictedArea($user, 'service', '', '', '', '', '', $objcanvas);
  else
  $result = restrictedArea($user, 'produit|service', '', '', '', '', '', $objcanvas);
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
    $title = "Creazione asset";


    $texte = "Lista famiglia";


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
        // modifiche mie ********
        $codice_famiglia = $_POST['cod_famiglia'];
        //recupero l'istanza dal database
        if (isset($codice_famiglia))
        {
            $fam = new family($db);
            $famiglia = $fam->getRowFamily($codice_famiglia);
        }

        $redir = DOL_URL_ROOT . '/product/elenco_asset.php?mainmenu=products';
        $server = $_SERVER["PHP_SELF"];
        // $redirict = ($action == " ass asset") ? $redir : (($action == "create") ? $server : "");




        if ($type == 3)
        {
            $title = $langs->trans("CreateAsset");
        }
        print_fiche_titre($title);

        $num_ticket_interno = isset($_POST['num_ticket_interno']) ? $_POST['num_ticket_interno'] : "";


        if ($action_crea == "Crea asset")
        {

            $newCodAsset = $_POST['cod_asset'];
            $asset_corretto = checkAsset($newCodAsset);
            $obj_assets = new asset($db);
            $num_ticket_interno = $_POST['num_ticket_interno'];
            $num_ticket_cliente = $_POST['num_ticket_cliente'];
            $etichetta = (isset($_POST['libelle'])) ? $_POST['libelle'] : null;
            $stato_fisico = $_POST['stato_fisico'];
            $stato_tecnico = $_POST['stato_tecnico'];
            $descrizione = (isset($_POST['desc'])) ? $_POST['desc'] : null;
            $proprietario = $_POST['proprietario'];
            $codice_produttore = (isset($_POST['customcode'])) ? $_POST['customcode'] : null;
            $corridoio = (isset($_POST['corridoio'])) ? $_POST['corridoio'] : null;
            $scaffale = (isset($_POST['scaffale'])) ? $_POST['scaffale'] : null;
            $ripiano = (isset($_POST['ripiano'])) ? $_POST['ripiano'] : null;
            $marca = (isset($_POST['brand'])) ? $_POST['brand'] : null;
            $modello = (isset($_POST['model'])) ? $_POST['model'] : null;
            $note = (isset($_POST['note'])) ? $_POST['note'] : null;
            $magazzino = $_POST['magazzino'];
            $data_creazione = date("d-m-y");
            $error = 0;
            if ($stato_fisico == 2)
            {
                if (empty($num_ticket_interno))
                {
                    print '<div class="jnotify-container">';
                    print '<div class="jnotify-notification jnotify-notification-error">';
                    print '<div class="jnotify-background"></div>';
                    print '<a class="jnotify-close">??</a>';
                    print '<div class="jnotify-message">';
                    print '<div>';
                    print 'Il campo ';
                    print '<b>numero ticket interno </b>';
                    print '?? obbligatorio';
                    print '</div>';
                    print '</div>';
                    print '</div>';
                    print '</div>';
                    $error = 1;
                }
            }

            if (empty($error) && !empty($asset_corretto))
            {
                $sql = "INSERT INTO " . MAIN_DB_PREFIX . "asset (";
                $sql.= " cod_asset";
                $sql.= ", label";
                $sql.= ", stato_fisico";
                $sql.= ", stato_tecnico";
                $sql.= ", num_ticket_interno";
                $sql.= ", num_ticket_cliente";
                $sql.= ", descrizione";
                $sql.= ", proprietario";
                $sql.= ",codice_produttore";
                $sql.= ", corridoio";
                $sql.= ", scaffali";
                $sql.= ", ripiano";
                $sql.= ", brand";
                $sql.= ", model";
                $sql.= ", note";
                $sql.= ", id_magazzino";
                $sql.= ", data_creazione";

                $sql.= ") VALUES (";
                $sql.= "'" . $newCodAsset . "'";
                $sql.= ", '" . $etichetta . "'";
                $sql.= ", '" . $stato_fisico . "'";
                $sql.= ", '" . $stato_tecnico . "'";
                $sql.= ", '" . $num_ticket_interno . "'";
                $sql.= ", '" . $num_ticket_cliente . "'";
                $sql.= ", '" . $descrizione . "'";
                $sql.= ", '" . $proprietario . "'";
                $sql.= ", '" . $codice_produttore . "'";
                $sql.= ", '" . $corridoio . "'";
                $sql.= ", '" . $scaffale . "'";
                $sql.= ", '" . $ripiano . "'";
                $sql.= ", '" . $marca . "'";
                $sql.= ", '" . $modello . "'";
                $sql.= ", '" . $note . "'";
                $sql.= ", '" . $magazzino . "'";
                $sql.= ", '" . $data_creazione . "'";
                $sql.= ")";
                $result = $db->query($sql);
                if ($result)
                { // se ?? stato salvato il record// occorre aggiornare il campo cod_asset(facendo la concatenazione cod_faiglia-l'ultimo id
                    //$lastIdAsset = $result->lastId;
                    // aggiorno il contatore della famiglia (incremento o decremento)
                    //ricavo il valore della scorta (attuale)
                    $query = "SELECT LAST_INSERT_ID() as last_id;";
                    $res = $db->query($query);
                    $lastIdAsset = $db->fetch_object($res);
                    $lastIdAsset = $lastIdAsset->last_id;

                    $query = "SELECT f.stock as scorta ";
                    $query .= " FROM " . MAIN_DB_PREFIX . "product as f ";
                    $query .= "WHERE f.ref = " . "'" . $cod_famiglia . "'";
                    $result = $db->query($query);
                    if ($result)
                    {
                        $obj = $db->fetch_object($result);
                        $tot_scorte = (int) $obj->scorta;
                    }
                    $tot_scorte++; // incremento poich?? ho aggiunto un asset
                    if ($stato_fisico == 2 || $stato_fisico == 5)
                    { // se lo stato fisico ?? in uso o dismesso
                        $tot_scorte = (int) ($tot_scorte - 1); // decremento poiche lo stato fisico ?? in uso o dismesso
                    }
                    $query = "UPDATE " . MAIN_DB_PREFIX . "product ";
                    $query .= "SET stock = " . $tot_scorte;
                    $query .= " WHERE ref = " . "'" . $cod_famiglia . "'";
                    $result = $db->query($query);
                    if ($result != false)
                    { // l'asset non ?? stato creato allora ricarica la pagina
                        $sql = "SELECT * ";
                        $sql .= " FROM " . MAIN_DB_PREFIX . "asset as a ";
                        $sql .= " WHERE id = " . $lastIdAsset;
                        $res = $db->query($sql);
                        if ($res)
                        {
                            $obj_asset = $db->fetch_object($res);
                            $code_newAsset = $obj_asset->cod_asset;
                            //aggiorna la tabella tracking
                            $obj_track = new myTracking($db); // tracking
                            $array_tracking = array();
                            $array_tracking['azione'] = "creato";
                            $array_tracking['user'] = $user->id;
                            $array_tracking['riferimento'] = $num_ticket_interno;
                            $array_tracking['codice_asset'] = $code_newAsset;
                            $array_tracking['codice_famiglia'] = $cod_famiglia;
                            $array_tracking['etichetta'] = $etichetta;

                            $obj_track->nuovo_tracking($array_tracking);

                            if ($stato_fisico == 2) // solo se lo stato fisico dell'asset ?? in uso
                            {
                                $obj_utilizzati = new myUtilizzati($db);
                                $obj_utilizzati->nuovo_utilizzati($array_tracking);
                            }
                        }

                        $path = DOL_URL_ROOT . '/product/scheda_asset.php?mainmenu=products&cod_asset=' . $code_newAsset;
                        print '<META HTTP-EQUIV="Refresh" CONTENT="0; url=' . $path . '">';
                    }
                } else
                { //altrimenti
                    //altrimenti, se l'asset ?? stato creato, ridirezione nella pagina elenco asset
                    $path = DOL_URL_ROOT . '/product/elenco_asset.php?mainmenu=products';
                    print '<META HTTP-EQUIV="Refresh" CONTENT="0; url=' . $path . '">';
                }
            }
        }
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
        

        print '<tr><td class="fieldrequired">' . "HW" . '</td>';
        print '<td><input name="cod_asset" size="40" "></td></tr>';

        print '<tr><td class="fieldrequired">' . "DIT" . '</td><td colspan="3"><input name="libelle" size="40" maxlength="255" value="' . $famiglia->label . '"></td></tr>';
        print '<tr><td class="fieldrequired">' . "Stato Fisico" . '</td><td colspan="3">';
        $statutarray = array('1' => "Giacenza", '2' => "In uso", '3' => "In transito", '4' => "In lab", "5" => "Dismesso");
        print $form->selectarray('stato_fisico', $statutarray, GETPOST('stato_fisico'));
        print '</td></tr>';

        print '<tr><td class="fieldrequired">' . "Stato Tecnico" . '</td><td colspan="3">';
        $statutarray = array('1' => "Nuovo", '2' => "Ricondizionato", '3' => "Guasto", '4' => "Sconosciuto");
        print $form->selectarray('stato_tecnico', $statutarray, GETPOST('stato_tecnico'));
        print '</td></tr>';


        print '<tr><td class="fieldrequired">' . "Numero ticket interno" . '</td>';
        print '<td><input name="num_ticket_interno" size="30" "></tr>';

        print '<tr><td valign="top">' . "Numero ticket cliente" . '</td>';
        print '<td><input name="num_ticket_cliente" size="30" "></tr>';

        print '<tr><td valign="top">' . "Descrizione" . '</td><td colspan="3">';
        $descrizione = empty($famiglia->description) ? "" : $famiglia->description;
        print '<textarea id="desc" class="flat" cols="80" rows="4" name="desc">' . $descrizione . '</textarea>';

        print '<tr><td>' . "Proprietario" . '</td><td colspan="2">' . '<input type="text" name="proprietario" size="30" value="">' . '</td>';
        print '</tr>';


        print '<tr><td width="20%">' . "Codice produttore" . '</td>';
        print '<td><input name="customcode" size="16" value="' . $famiglia->customcode . '">';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Corridoio" . '</td>';
        print '<td><input name="corridoio" size="16" ">';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Scaffale" . '</td>';
        print '<td><input name="scaffale" size="16" ">';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Ripiano" . '</td>';
        print '<td><input name="ripiano" size="16" ">';
        print '</td></tr>';

        print '<tr><td>' . "Marca" . '</td>';
        print '<td><input name="brand" size="16" value="' . $famiglia->brand . '">';
        print '</td></tr>';

        print '<tr><td width="20%">' . "Modello" . '</td>';
        print '<td><input name="model" size="16" value="' . $famiglia->model . '">';
        print '</td></tr>';

        $obj_mag_generico = new magazzino($db);
        $where = "";
        if ($user->login == "solari")
        {
            $magazzino_proprio = $obj_mag_generico->getMagazzinoUser("solari");
            $cod_mag = $magazzino_proprio[0]['rowid'];
            $where = " WHERE rowid = " . $cod_mag;
        }
        if ($user->login == "st_solari")
        {
            $magazzino_proprio = $obj_mag_generico->getMagazzinoUser("st_solari");
            $cod_mag = $magazzino_proprio[0]['rowid'];
            $where = " WHERE rowid = " . $cod_mag;
        }
        if ($user->login == "pcm_napoli")
        {
            $magazzino_proprio = $obj_mag_generico->getMagazzinoUser("pcm_napoli");
            $cod_mag = $magazzino_proprio[0]['rowid'];
            $where = " WHERE rowid = " . $cod_mag;
        }
        if ($user->login == "pcm_milano")
        {
            $magazzino_proprio = $obj_mag_generico->getMagazzinoUser("pcm_milano");
            $cod_mag = $magazzino_proprio[0]['rowid'];
            $where = " WHERE rowid = " . $cod_mag;
        }
        if ($user->login == "tpr")
        {
            $magazzino_proprio = $obj_mag_generico->getMagazzinoUser("Tpr");
            $cod_mag = $magazzino_proprio[0]['rowid'];
            $where = " WHERE rowid = " . $cod_mag;
        }
        if ($user->tipologia == "T" || $user->tipologia == "M")
        {
            $magazzino_proprio = $obj_mag_generico->getMagazzinoUser($user->id, "fk_user");
            $cod_mag = $magazzino_proprio[0]['rowid'];

            $where = " WHERE rowid = " . $cod_mag;
        }

        print '<tr><td valign="top">' . "Nota" . '</td><td colspan="3">';
        $nota = empty($famiglia->note) ? "" : $famiglia->note;
        print '<textarea id="desc" class="flat" cols="80" rows="4" name="note">' . $nota . '</textarea>';
        print "</td></tr>";
        print '<tr><td>' . "Seleziona il magazzino" . '</td><td colspan="3">';
        // va fatto una query
        //$object->getCodFamily();
        $sql = "SELECT magazzino.rowid, magazzino.label ";
        $sql.= " FROM " . MAIN_DB_PREFIX . "entrepot as magazzino " . $where;

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
        print '<select name="magazzino">';
        for ($i = 0; $i < count($statutarray); $i++)
        {
            $valore = $statutarray[$i];
            $etich = $label[$i];
            print '<option value=' . "$valore" . '>' . $etich . '</option>';
        }
        print "</select>";

        print '</td></tr>';


        print '</table>';
        print '<br>';
        print '<center> <input class="butAction" type="submit" name = "action_crea" value="Crea asset"> </center>';
        //print '<center><input type="submit" class="button" name="action" value="' . $langs->trans("CreateAsset") . '"></center>';

        print '</form>';
    }
}

function checkAsset($newCodAsset = "")
{
    global $db;
    if (empty($newCodAsset)) // verifica se ?? non ?? stato messo una stringa vuota
    {
        print '<div class="jnotify-container">';
        print '<div class="jnotify-notification jnotify-notification-error">';
        print '<div class="jnotify-background"></div>';
        print '<a class="jnotify-close">??</a>';
        print '<div class="jnotify-message">';
        print '<div>';
        print '<b>Inserire il codice asset </b>';
        print '</div>';
        print '</div>';
        print '</div>';
        print '</div>';
        return 0;
    }
    // verificare che il numero dell'asset inserito non sia gi?? esistente
    $sql = "SELECT count(*) as count ";
    $sql .= " FROM " . MAIN_DB_PREFIX . "asset as a ";
    $sql .= " WHERE cod_asset LIKE '" . $newCodAsset . "'";
    $res = $db->query($sql);
    $obj_count = $db->fetch_object($res);
    $numero_rec = (int) $obj_count->count;
    if (!empty($numero_rec)) // Se il record esiste gi??
    {

        print '<div class="jnotify-container">';
        print '<div class="jnotify-notification jnotify-notification-error">';
        print '<div class="jnotify-background"></div>';
        print '<a class="jnotify-close">??</a>';
        print '<div class="jnotify-message">';
        print '<div>';
        print '<b>Codice asset gi?? esistente </b>';
        print '</div>';
        print '</div>';
        print '</div>';
        print '</div>';

        return 0;
    }

    $char_non_valid = "&%^??#@,.:";
    for ($i = 0; $i < strlen($newCodAsset); $i++)
    {
        $char_str = $newCodAsset[$i];
        if (strstr($char_non_valid, $char_str))
        {
            print '<div class="jnotify-container">';
            print '<div class="jnotify-notification jnotify-notification-error">';
            print '<div class="jnotify-background"></div>';
            print '<a class="jnotify-close">??</a>';
            print '<div class="jnotify-message">';
            print '<div>';
            print "<b>Il carattere inserito ".$char_str ." non ?? valido</b>";
            print '</div>';
            print '</div>';
            print '</div>';
            print '</div>';

            return 0;
        }
    }



    return 1;
}
