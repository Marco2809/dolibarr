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
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myTracking.php';


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
<a id="card" class="tab inline-block" href="' . $root . '/product/movimentazione.php?leftmenu=product&type=5" data-role="button">Nuova movimentazione</a>
</div>';

        print '<div class="inline-block tabsElem">
<a id="price" class="tab inline-block" href="' . $root . '/product/transito.php?mainmenu=products&leftmenu=product&type=6&id=3" data-role="button">In transito</a>
</div>';

        print '<div class="inline-block tabsElem">
<a id="price" class="tabactive tab inline-block" href="' . $root . '/product/daconvalidare.php?leftmenu=product&type=6&id=3" data-role="button">Da convalidare</a>
</div>';

        print '<div class="inline-block tabsElem">
<a id="price" class="tab inline-block" href="' . $root . '/product/storico.php?leftmenu=product&type=6&id=4" data-role="button">Storico</a>
</div>';



        $cod_movimentazione = $_GET['id'];
        print '<form action="#" method="POST" >';
        print"<br><br><br>";
        print "<strong>Codice movimentazione:  </strong>" . $cod_movimentazione;
        print "<br><br>";

        /** SCHEDA - breve descrizione */
        $obj_movimentazione = new movimentazione($db);
        $movimentazione = $obj_movimentazione->getMovimentazione($cod_movimentazione);
        $obj_magazzino = new magazzino($db);

        $magazzino_sorgente = $obj_magazzino->getMagazzino($movimentazione->mag_sorgente);
        $magazzino_destinatario = $obj_magazzino->getMagazzino($movimentazione->mag_dest);

        $tabella_riepilogo = "";
        $tabella_riepilogo .= '<div class="tabBar">';
        $tabella_riepilogo .= '<table class="border" width="100%">';
        $tabella_riepilogo .= '<tbody>';

        $tabella_riepilogo .= '<tr>';
        $tabella_riepilogo .= '<td width="15%"><strong>Codice movimentazione</strong></td>';
        $tabella_riepilogo .= '<td class="nobordernopadding">' . $cod_movimentazione . '</td>';
        $tabella_riepilogo .= '</tr>';


        $tabella_riepilogo .= '<tr>';
        $tabella_riepilogo .= '<td width="15%"><strong>Magazzino Mittente</strong></td>';
        $tabella_riepilogo .= '<td class="nobordernopadding">' . $magazzino_sorgente[0]['label'] . '</td>';
        $tabella_riepilogo .= '</tr>';

        $tabella_riepilogo .= '<tr>';
        $tabella_riepilogo .= '<td width="15%"><strong>Magazzino Destinatario</strong></td>';
        $tabella_riepilogo .= '<td class="nobordernopadding">' . $magazzino_destinatario[0]['label'] . '</td>';
        $tabella_riepilogo .= '</tr>';

        $tabella_riepilogo .= '<tr>';
        $tabella_riepilogo .= '<td width="15%"><strong>Causale del trasporto</strong></td>';
        $tabella_riepilogo .= '<td class="nobordernopadding">' . $movimentazione->causale_trasp . '</td>';
        $tabella_riepilogo .= '</tr>';

        $tabella_riepilogo .= '<tr>';
        $tabella_riepilogo .= '<td width="15%"><strong>Luogo di destinazione</strong></td>';
        $tabella_riepilogo .= '<td class="nobordernopadding">' . $movimentazione->luogo_dest . '</td>';
        $tabella_riepilogo .= '</tr>';

        $tabella_riepilogo .= '<tr>';
        $tabella_riepilogo .= '<td width="15%"><strong>Trasporto a mezzo</strong></td>';
        $trasporto_mezzo = "";
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
        $tabella_riepilogo .= '<td class="nobordernopadding">' . $trasporto_mezzo . '</td>';
        $tabella_riepilogo .= '</tr>';

        $tabella_riepilogo .= '<tr>';
        $tabella_riepilogo .= '<td width="15%"><strong>Annotazioni</strong></td>';
        $tabella_riepilogo .= '<td class="nobordernopadding">' . $movimentazione->annotazioni . '</td>';
        $tabella_riepilogo .= '</tr>';


        $tabella_riepilogo .= '<tr>';
        $tabella_riepilogo .= '<td width="15%"><strong>Codice asset in transito</strong></td>';
        $tabella_riepilogo .= '<td class="nobordernopadding">' . $movimentazione->checkbox_asset . '</td>';
        $tabella_riepilogo .= '</tr>';

        $tabella_riepilogo .= '</tbody>';
        $tabella_riepilogo .= '</table>';
        $tabella_riepilogo .= '</div>';
        print $tabella_riepilogo;

        // estraggo le righe (se ci sono)
        $array_righe = array();
        if (isset($movimentazione->righe_altro))
        {
            if (!empty($movimentazione->righe_altro))
            {
                $array_righe = json_decode($movimentazione->righe_altro, true); // con true trasformo l'oggetto in array
                if (count($array_righe) == 1)
                {
                    print '<strong>Riga inserita: </strong>';
                } else
                {
                    print '<strong>Righe inserite: </strong>';
                }

                print '<div class="tabBar">';
                print '<table class="border" width="100%">';
                print '<tr>';
                print '<td width="20%">' . "<strong>Codice</strong>" . "</td>";
                print '<td width="70%">' . "<strong>Descrizione</strong>" . "</td>";
                print '<td>' . "<strong>Quantità</strong>" . "</td>";
                print '</tr>';
            }
        }
        if (!empty($array_righe))
        {
            foreach ($array_righe AS $chiave => $riga)
            {
                print '<tr>';
                $codice = $riga[0];
                $descrizione = $riga[1];
                $qt = $riga[2];
                print '<td>' . $codice . "</td>";
                print '<td>' . $descrizione . "</td>";
                print '<td>' . $qt . "</td>";
                print '</tr>';
            }
        }
        print '</table>';
        print '</div>';

        //cancellazione o convalida
        print '<script>
                            $(function() {
                              $( "#datepicker" ).datepicker();
                            });
                            </script>';

        $obj_mag_generico = new magazzino($db);
        $magazzino_proprio = $obj_mag_generico->getMagazzinoUser($user->id, "fk_user");
        $id_m = $magazzino_proprio[0]['rowid'];


        if ($movimentazione->mag_dest == $id_m)
        {
            $data_odierna = date("d/m/Y");
            print 'Inserisci la data per convalidare <input type="text" name="data_convalida" value="' . $data_odierna . '" id="datepicker"></td>';
            print'<input type="submit" class="button" name="action"  value="' . "Convalida" . '">';
        }

        if ($user->tipologia != "T")
        {
            $data_odierna = date("d/m/Y");
            print 'Inserisci la data per convalidare <input type="text" name="data_convalida" value="' . $data_odierna . '" id="datepicker"></td>';
            print'<input type="submit" class="button" name="action"  value="' . "Convalida" . '">';

            print '<br><br>Elimina la movimentazione<input type="submit" class="button" name="action"  value="' . "Elimina" . '">';
        } else if ($user->tipologia == "T")
        {
            if ($movimentazione->mag_sorgente == $id_m)
            {
                print '<br><br>Elimina la movimentazione<input type="submit" class="button" name="action"  value="' . "Elimina" . '">';
                // '<br><br>Modifica la movimentazione<input type="submit" class="button" name="action"  value="' . "Modifica" . '">';
            }
        }
        $path_pop = DOL_URL_ROOT . "/custom/ultimateqrcode/asset_transito_qrcode.php?id_movimentazione=" . $movimentazione->codice_mov . "";
        print "<script>
function inviaform(){
        var path = '$path_pop';
	window.open(path,'popupname','width=200,height=200,toolbar=yes, location=no,status=yes,menubar=yes,scrollbars=yes');
	document.getElementById('nomeform').submit();
}
</script>";
        print '<br><br>Stampa i codici asset in transito<input type="submit" name="action" class="button" onclick="inviaform();" value="stampa">';
        print "</form>";
        print '<br><br>';

        print $html;

        $obj_movimentazione = new movimentazione($db);
        $movimentazione = $obj_movimentazione->getMovimentazione($cod_movimentazione);
        $nome_ddt = $movimentazione->id_ddt . ".pdf";
        $down_mag = DOL_URL_ROOT . '/product/ddt/' . $nome_ddt;
        $link = '<a href="' . $down_mag . '"target="_blank">' . "Scarica il documento di trasporto >>" . '</a></td>';
        print $link;

        if ($action == "Elimina")
        { // ripristino tutto
            $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "form_assetmove as fa ";
            $sql .= " WHERE fa.codice_mov LIKE '" . $cod_movimentazione . "'";
            $result = $db->query($sql);
            $asset_decode = array();
            if ($result)
            {
                $obj = $db->fetch_object($result);
                $cod_movimentazione = $obj->codice_mov;
                $asset_decode = explode(",", $obj->checkbox_asset);
                for ($j = 0; $j < count($asset_decode); $j++)
                { // per ogni asset della movimentazione selezionata
                    $query = "SELECT a.tmp_stato_fisico, a.tmp_magdest FROM " . MAIN_DB_PREFIX . "asset as a";
                    $query .= " WHERE cod_asset LIKE '" . $asset_decode[$j] . "'";
                    $result = $db->query($query);
                    if ($result)
                    {
                        $obj_asset = $db->fetch_object($result);
                        $stat_fisico = $obj_asset->tmp_stato_fisico;
                        $id_mag = $obj_asset->tmp_magdest;
                    }
                    //imposto lo stato dell'asset in giacenza
                    // $stat_fisico = 1; //giacenza
                    $sql = "UPDATE " . MAIN_DB_PREFIX . "asset SET stato_fisico=" . $stat_fisico . "," . "id_magazzino = " . $id_mag; // salvo lo stato fisico in una variabile temporanea in modo che si possa ripristinare
                    $sql .= " WHERE cod_asset LIKE '" . $asset_decode[$j] . "'";
                    $aggiornato = $db->query($sql);
                }
                //elimino anche il ddt
                $id_ddt = $obj->id_ddt;
                $file = DOL_URL_ROOT . "/product/ddt/" . $id_ddt . ".pdf";
                $file = DOL_DOCUMENT_ROOT . "/product/ddt/" . $id_ddt . ".pdf"; // crea l'asset con l'id ddt, in questo modo sarà univoco
                unlink($file);

                /* //elimina dalla tabella facture e faqcture dat
                  $query = "DELETE FROM " . MAIN_DB_PREFIX . "facture";
                  $query .= " WHERE facnumber LIKE "."'".$id_ddt."'";
                  $result = $db->query($query);

                 */

                $query = "DELETE FROM " . MAIN_DB_PREFIX . "form_assetmove";
                $query .= " WHERE codice_mov LIKE '" . $cod_movimentazione . "'";
                $result = $db->query($query);

                $sql = "INSERT INTO " . MAIN_DB_PREFIX . "log_movimentazione(";
                $sql.= "id_movimentazione,stato,data_movimentazione";
                $sql.= ") VALUES (";
                $sql.= "'" . $cod_movimentazione . "'" . ",";
                $sql.= "'Cancellato'" . ",";
                $sql.= "CURRENT_TIMESTAMP()";
                $sql.= ")";
                $ris = $db->query($sql);
                if ($result)
                {
                    $path = DOL_URL_ROOT . '/product/daconvalidare.php?mainmenu=products';
                    print '<META HTTP-EQUIV="Refresh" CONTENT="0; url=' . $path . '">';
                    return;
                }
            }
            //gestione eliminzione dei prodotti
            $sql = "SELECT fa.mag_sorgente,fa.mag_dest,prod.tmp_jsonscorte FROM " . MAIN_DB_PREFIX . "form_assetmove as fa INNER JOIN tmp_scortaprodotti as prod ON prod.codice_movimentazione = " . $cod_movimentazione;
            $sql .= " WHERE fa.codice_mov LIKE '" . $cod_movimentazione . "'";
            $result_Q = $db->query($sql);
            if ($result_Q)
            {
                $obj = $db->fetch_object($result);
            }
        }


        if ($action == "Convalida")
        { // porto nello stato di giacenza
            $data_convalida = $_POST['data_convalida'];
            //OCCORRE impostare il flag a 1
            $uno = 1;
            $sql = "UPDATE " . MAIN_DB_PREFIX . "form_assetmove SET flag=" . $uno . "," . "data_convalida = " . "'" . $data_convalida . "'"; // salvo lo stato fisico in una variabile temporanea in modo che si possa ripristinare
            $sql .= " WHERE codice_mov LIKE '" . $cod_movimentazione . "'";
            $res = $db->query($sql);

            $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "form_assetmove as fa ";
            $sql .= " WHERE fa.codice_mov LIKE '" . $cod_movimentazione . "'";
            $result = $db->query($sql);
            $asset_decode = array();
            if ($result)
            {
                $obj = $db->fetch_object($result);
                $cod_movimentazione = $obj->codice_mov;
                $asset_decode = explode(",", $obj->checkbox_asset);
                for ($j = 0; $j < count($asset_decode); $j++)
                { // per ogni asset della movimentazione selezionata
                    $uno = 1;
                    $sql = "UPDATE " . MAIN_DB_PREFIX . "asset SET stato_fisico=" . $uno; // salvo lo stato fisico in una variabile temporanea in modo che si possa ripristinare
                    $sql .= " WHERE cod_asset LIKE '" . $asset_decode[$j] . "'";
                    $res = $db->query($sql);
                    //tracking
                    $obj_track = new myTracking($db);
                    $array_tracking = array();
                    $array_tracking['azione'] = "modifica magazzino";
                    $array_tracking['old'] = $obj->mag_sorgente;
                    $array_tracking['new'] = $obj->mag_dest;

                    $array_tracking['user'] = $user->id;
                    $array_tracking['riferimento'] = $cod_movimentazione;
                    $array_tracking['codice_asset'] = $asset_decode[$j];
                    $obj_magazzino = new magazzino($db);

                    $magazzino_nome_sorg = $obj_magazzino->getMagazzino($obj->mag_sorgente);
                    $magazzino_nome_sorg = $magazzino_nome_sorg[0]['label'];
                    $magazzino_nome_dest = $obj_magazzino->getMagazzino($obj->mag_dest);
                    $magazzino_nome_dest = $magazzino_nome_dest[0]['label'];
                    $array_tracking['descrizione'] = "movimentazione effettuato dal magazzino $magazzino_nome_sorg al magazzino $magazzino_nome_dest";

                    $obj_track->nuovo_tracking($array_tracking);
                }
            }
            $sql = "INSERT INTO " . MAIN_DB_PREFIX . "log_movimentazione(";
            $sql.= "id_movimentazione,stato,data_movimentazione";
            $sql.= ") VALUES (";
            $sql.= "'" . $cod_movimentazione . "'" . ",";
            $sql.= "'Convalidato'" . ",";
            $sql.= "CURRENT_TIMESTAMP()";
            $sql.= ")";
            $ris = $db->query($sql);

            $path = DOL_URL_ROOT . '/product/storico.php';
            print '<META HTTP-EQUIV="Refresh" CONTENT="0; url=' . $path . '">';
        }
        if ($action == "Modifica")
        {
            $id = isset($_GET['id']) ? $_GET['id'] : 0;

            $path = DOL_URL_ROOT . '/product/modifica_movimentazione.php?id=' . $id;
            print '<META HTTP-EQUIV="Refresh" CONTENT="0; url=' . $path . '">';
        }
    }
}
