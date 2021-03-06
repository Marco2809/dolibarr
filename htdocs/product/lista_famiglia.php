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

        // Displays product removal confirmation
        if (GETPOST('delprod'))
            dol_htmloutput_mesg($langs->trans("ProductDeleted", GETPOST('delprod')));

        $param = "&amp;sref=" . $sref . ($sbarcode ? "&amp;sbarcode=" . $sbarcode : "") . "&amp;snom=" . $snom . "&amp;sall=" . $sall . "&amp;tosell=" . $tosell . "&amp;tobuy=" . $tobuy;
        $param.=($fourn_id ? "&amp;fourn_id=" . $fourn_id : "");
        $param.=($search_categ ? "&amp;search_categ=" . $search_categ : "");
        $param.=isset($type) ? "&amp;type=" . $type : "";

        print_barre_liste($texte, $page, "liste.php", $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords);

        $html .= '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
        print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
        print "Seleziona il magazzino";
        // va fatto una query
        //$object->getCodFamily();
        $sql = "SELECT magazzino.rowid, magazzino.label ";
        $sql.= " FROM " . MAIN_DB_PREFIX . "entrepot as magazzino";
        $res = $db->query($sql);
        $statutarray = array();
        $label = array();
        if ($res) {
            $prods = array();
            while ($rec = $db->fetch_array($res)) {
                $statutarray[] = $rec['rowid'];
                $label [] = $rec['label'];
            }
        }
        //print $form->selectarray('sel_magazzino', $statutarray, GETPOST('sel_magazzino'), 1);
        print '<select name="magazzino">';
        for ($i = 0; $i < count($statutarray); $i++) {
            $valore = $statutarray[$i];
            $etich = $label[$i];
            print '<option value=' . "$valore" . '>' . $etich . '</option>';
        }
        print '<center><input type="submit" name= "sel_magazzino" class="button" value="OK"></center>';

        print "</select>";
        print '</form>';

        $sel_magazzino = $_POST['sel_magazzino'];
        // faccio una query per ricercare il magazzino nella tabella asset
        $sql = "SELECT DISTINCT f.ref FROM  " . MAIN_DB_PREFIX .
                "product as f inner JOIN " . MAIN_DB_PREFIX . "asset as a ON  f.ref = a.cod_famiglia ";

        $result = $db->query($sql);
        if ($result) {
            //$obj_famiglia = $result->fetch_array(MYSQLI_NUM);
            $cod_f = array();
            while ($obj_famiglia = $result->fetch_array(MYSQLI_NUM)) {
                $cod_f[] = $obj_famiglia;
            }
        }
        if ($action == "create_excell") {
            /*
              $filename = "sheet.xls";
              header("Content-Type: application/vnd.ms-excel");
              header("Content-Disposition: inline; filename=$filename");
             */
            $get_IDfamiglia = $_GET['id_famiglia'];
            if (isset($get_IDfamiglia)) {
                $ogg_asset = new asset($db);
                $assets = $ogg_asset->getAsset($get_IDfamiglia);
            }
        }
        if (isset($sel_magazzino)) {
            $id_magazzino = $_POST['magazzino'];
            print '<br>';
            $obj_mag = new magazzino($db);
           $mio_magazzino =  $obj_mag->getMagazzino($id_magazzino); // servir?? per stampare il magazzino selezionato
           $nome_magazzino = $mio_magazzino[0]['label'];
           print "Magazzino selezionato: "."<strong>".$nome_magazzino."</strong>". "<br>";
            $dati_magazzino = array();
            $dati_asset_damagazzino = array();
            for ($i = 0; $i < count($cod_f); $i++) {
                $cod_fam = $cod_f[$i];
                $dati_magazzino[] = $obj_mag->getStatMagazzino($id_magazzino, $cod_fam[0]);
                $dati_asset_damagazzino[$cod_fam[0]] = $obj_mag->getAssetFromMagazzino($id_magazzino, $cod_fam[0]);
            }
            //print $dati_magazzino['label'];
            print '<br><table class="noborder" width="100%">';

            print '<tr class="liste_titre"><td width="40%" colspan="4">' . "Famiglia" . '</td>';
            print '<td align="right">' . "Nuovo" . '</td>';
            print '<td align="right">' . "Ricondizionato" . '</td>';
            print '<td align="right">' . "Guasto" . '</td>';
            print '<td align="right">' . "Sconosciuto" . '</td>';
            print '<td align="right">' . "Totale utizzabile" . '</td>';
            print '<td align="right">' . "Totale" . '</td>';
            print '</tr>';
            print '<tr ' . true . '>';

            $tot_nuovo = 0;
            $tot_ricondizionato = 0;
            $tot_guasto = 0;
            $tot_sconosciuto = 0;
            $totUtilizzabile_finale = 0;
            $tot_finale = 0;
            $join_html = "";
            for ($i = 0; $i < count($dati_magazzino); $i++) {
                if (empty($dati_magazzino[$i])) {
                    continue;
                }
                $down = DOL_URL_ROOT . '/product/tmpxls/' . $cod_f[$i][0] . ".xls";

                $link = '<a href="' . $down . '">' . $cod_f[$i][0] . '</a></td>';
                $filename = "tmpxls/".$cod_f[$i][0] . ".xls";
                
                // if (!file_exists($filename)) {
                $fp = fopen($filename, 'w');
                $ogg_mag = new magazzino($db);
                $html = '<br><table border="1" class="noborder">';

                $html .= '<tr>';
                $html .= '<td>' . "Codice asset" . '</td>';
                $html .= '<td>' . "Stato fisico" . '</td>';
                $html .= '<td>' . "Stato tecnico" . '</td>';
                $html .= '<td>' . "Corridoio" . '</td>';
                $html .= '<td>' . "Scaffali" . '</td>';
                $html .= '<td>' . "Ripiano" . '</td>';
                $html .= '<td>' . "Marca" . '</td>';
                $html .= '<td>' . "Modello" . '</td>';
                $html .= '</tr>';
                $assets = $ogg_mag->getAssetFromMagazzino($id_magazzino, $cod_f[$i][0]);
                for ($j = 0; $j < count($assets); $j++) {
                    $asset = $assets[$j];
                    switch ($asset['stato_fisico']) {
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
                    switch ($asset['stato_tecnico']) {
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
                    $html .= '<tr>';
                    $html .= '<td>' . $asset['cod_asset'] . '</td>';
                    $html .= '<td>' . $stato_fisico . '</td>';
                    $html .= '<td>' . $stato_tecnico . '</td>';
                    $html .= '<td>' . $asset['corridoio'] . '</td>';
                    $html .= '<td>' . $asset['scaffali'] . '</td>';
                    $html .= '<td>' . $asset['ripiano'] . '</td>';
                    $html .= '<td>' . $asset['brand'] . '</td>';
                    $html .= '<td>' . $asset['model'] . '</td>';
                    $html .= '</tr>';
                }
                fwrite($fp, $html);
                fclose($fp);

                $join_html .= $html;
                print '<td colspan="4">' . $link . " - " . $obj->ref . '</td>';
                $nuovo = (int) $dati_magazzino[$i]['nuovo'];
                $ricondizionato = (int) $dati_magazzino[$i]['ricondizionato'];
                $guasto = (int) $dati_magazzino[$i]['guasto'];
                $sconosciuto = (int) $dati_magazzino[$i]['sconosciuto'];
                $totale = (int) $nuovo + $ricondizionato + $guasto + $sconosciuto;
                $tot_utizzabile = (int) ($nuovo + $ricondizionato);
                print '<td align="right">' . $nuovo . '</td>';
                print '<td align="right">' . $ricondizionato . '</td>';
                print '<td align="right">' . $guasto . '</td>';
                print '<td align="right">' . $sconosciuto . '</td>';
                print '<td align="right">' . $tot_utizzabile . '</td>';
                print '<td align="right">' . $totale . '</td>';

                if ($nuovo > 0)
                    $tot_nuovo +=$nuovo;
                if ($ricondizionato > 0)
                    $tot_ricondizionato +=$ricondizionato;
                if ($guasto > 0)
                    $tot_guasto +=$guasto;
                if ($sconosciuto > 0)
                    $tot_sconosciuto +=$sconosciuto;
                if ($tot_utizzabile > 0)
                    $totUtilizzabile_finale = $tot_nuovo + $tot_ricondizionato;
                if ($totale > 0)
                    $tot_finale +=$totale;

                print '</tr>';
            }

            //  print '<tr>';
            //print '<tr class="liste_titre"><td width="40%" colspan="4">' . "Famiglia" ;
            $downTot = DOL_URL_ROOT . '/product/' . "total_dati" . ".xls";
            $link = '<a href="' . $downTot . '">' . "Totale" . '</a></td>';
            print '<td colspan="4">' . $link . '</td>';

            $file_name = "total_dati" . ".xls";
            if (file_exists($file_name)) {
                //unlink($filename);
            }

            $fpTot = fopen($file_name, 'w');
            fwrite($fpTot, $join_html);
            fclose($fpTot);


            //$join_html
            print '<td align="right">' . $tot_nuovo . '</td>';
            print '<td align="right">' . $tot_ricondizionato . '</td>';
            print '<td align="right">' . $tot_guasto . '</td>';
            print '<td align="right">' . $tot_sconosciuto . '</td>';
            print '<td align="right">' . $totUtilizzabile_finale . '</td>';
            print '<td align="right">' . $tot_finale . '</td>';
            print '</tr>';
            print "</table>";

            print '<br>';

            print '<br><table class="noborder" width="100%">';

            // print '<tr class="liste_titre"><td width="40%" colspan="4">' . "Magazzino" . '</td>';
            print '<tr class="liste_titre">';
            print '<td>' . "Magazzino" . '</td>';
            print '<td>' . "Codice asset" . '</td>';
            print '<td align="right">' . "Etichetta" . '</td>';
            print '</tr>';
            print '<tr ' . true . '>';

            $print_html = "";
            //  $print_html .= "<table>";
            $print_html = '<br><table border="1" class="noborder">';
            $print_html .= '<tr>';
            $print_html .= '<td>' . "Codice asset" . '</td>';
            $print_html .= '<td>' . "Stato fisico" . '</td>';
            $print_html .= '<td>' . "Stato tecnico" . '</td>';
            $print_html .= '<td>' . "Corridoio" . '</td>';
            $print_html .= '<td>' . "Scaffali" . '</td>';
            $print_html .= '<td>' . "Ripiano" . '</td>';
            $print_html .= '<td>' . "Marca" . '</td>';
            $print_html .= '<td>' . "Modello" . '</td>';
            $print_html .= '</tr>';
            foreach ($dati_asset_damagazzino as $famiglia) {
                $lista_famiglia = $famiglia;
                for ($i = 0; $i < count($lista_famiglia); $i++) {
                    $asset = $lista_famiglia[$i];
                    print '<tr>';
                    $obj_magazzino = new magazzino($db);
              $nome_mag =   $obj_magazzino->getMagazzino($asset['id_magazzino']);
                    print '<td>' . $nome_mag[0]['label']. '</td>';

                    $url = DOL_URL_ROOT . '/product/scheda_asset.php?cod_asset=' . $asset['cod_asset'];

                    $link = '<a href="' . $url . '">' . $asset['cod_asset'] . '</a></td>';


                    print '<td>' . $link . '</td>';
                    print '<td align="right">' . $asset['label'] . '</td>';
                    print '</tr>';

                    $print_html .= '<tr>';
                    $print_html .= '<td>' . $asset['cod_asset'] . '</td>';

                    $stato_fisico_mag = "";
                    switch ($asset['stato_fisico']) {
                        case "1":
                            $stato_fisico_mag = "Giacenza";
                            break;
                        case "2":
                            $stato_fisico_mag = "In uso";
                            break;
                        case "3":
                            $stato_fisico_mag = "In transito";
                            break;
                        case "4":
                            $stato_fisico_mag = "In lab";
                            break;
                    }
                    $print_html .= '<td>' . $stato_fisico_mag . '</td>';

                    $stato_tecnico_mag = "";
                    switch ($asset['stato_tecnico']) {
                        case "1":
                            $stato_tecnico_mag = "Nuovo";
                            break;
                        case "2":
                            $stato_tecnico_mag = "Ricondizionato";
                            break;
                        case "3":
                            $stato_tecnico_mag = "Guasto";
                            break;
                        case "4":
                            $stato_tecnico_mag = "Sconosciuto";
                            break;
                    }

                    $print_html .= '<td>' . $stato_tecnico_mag . '</td>';
                    $print_html .= '<td>' . $asset['corridoio'] . '</td>';
                    $print_html .= '<td>' . $asset['scaffali'] . '</td>';
                    $print_html .= '<td>' . $asset['ripiano'] . '</td>';
                    $print_html .= '<td>' . $asset['brand'] . '</td>';
                    $print_html .= '<td>' . $asset['model'] . '</td>';
                    $print_html .= '</tr>';
                }
            }

            $print_html .= "</table>";
            print "</table>";
            $filemame_mag = "tot_magazzino.xls";
            $fp = fopen($filemame_mag, 'w');
            $down_mag = DOL_URL_ROOT . '/product/' . $filemame_mag;
            $link = '<a href="' . $down_mag . '">' . "Esporta dati" . '</a></td>';
            fwrite($fp, $print_html);
            fclose($fp);


            print $link;
        }
    }
}