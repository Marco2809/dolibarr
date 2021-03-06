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
require_once DOL_DOCUMENT_ROOT . '/product/myclass/schedaMovimentazione.php';
require_once DOL_DOCUMENT_ROOT . '/product/crea_pdf.php'; // serve per il ddt
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myddt.php'; // serve per il ddt
require_once DOL_DOCUMENT_ROOT . '/product/log_movimentazione.php'; // serve per il ddt
if (!empty($conf->categorie->enabled))
    require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';

$langs->load("products");
$langs->load("stocks");
$langs->load("suppliers");

$action = GETPOST('action');
$codice = GETPOST("sref");
$sbarcode = GETPOST("sbarcode");
$stato = GETPOST("stato");
$mag_sorgente = GETPOST("mag_sorgente");
$mag_dest = GETPOST("mag_dest");
$mov_in_out = GETPOST("mov_in_out");

$data_convalida = GETPOST("data_convalida");
$flag = GETPOST("flag");
$sall = GETPOST("sall");
$type = GETPOST("type", "int");
$search_sale = GETPOST("search_sale");
$search_categ = GETPOST("search_categ", 'int');
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
    $sortfield = "a.cod_asset";
if (!$sortorder)
    $sortorder = "desc";

$limit = $conf->liste_limit;
$limit = 50; // limite per la pagina (modificato da amin)
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
    $codice = "";
    $sbarcode = "";
    $stato = "";
    $search_categ = 0;
}

if ($user->tipologia == "T")
{
    $obj_mag_generico = new magazzino($db);
    $magazzino_proprio = $obj_mag_generico->getMagazzinoUser($user->id, "fk_user");
    $id_m = $magazzino_proprio[0]['rowid'];
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

    $texte = "";

    //lista magazzino della select
    $mag_obj = new magazzino($db);
    $user_id = $user->id;
    $where_condition = ""; // nel caso delgli user specifici
    if ($user->tipologia == "T")
        $where_condition = " WHERE fk_user = $user_id "; // nel caso delgli user specifici
    $order_by;
    $select;
    $lista_magazzini = $mag_obj->getMagazziniSelect("*", $where_condition);
    $statutarray = array();
    $user_id_form_mag = 0;
    for ($i = 0; $i < count($lista_magazzini); $i++)
    {
        $magazzino = $lista_magazzini[$i];
        $elem = $magazzino['label'];
        $statutarray [$magazzino['rowid']] = $elem;
        $user_id_form_mag = $magazzino['rowid'];
    }

    //magazzino destinatario
    $statutarray_dest = array();
    $lista_magazzini_dest = $mag_obj->getMagazziniSelect();
    for ($i = 0; $i < count($lista_magazzini_dest); $i++)
    {
        $magazzino = $lista_magazzini_dest[$i];
        $elem = $magazzino['label'];
        $statutarray_dest [$magazzino['rowid']] = $elem;
    }

    $array_condizione = array();

    if (isset($id_magazzino))
    {
        if (!empty($id_magazzino))
        {
            if ($id_magazzino != -1)
            {
                $array_condizione [] = "a.id_magazzino = " . $id_magazzino;
            }
        }
    }


    if (isset($codice))
    {
        if (!empty($codice))
        {
            if ($user->tipologia == "T")
            {
                $array_condizione [] = "m.mag_sorgente = " . $id_m. " and m.codice_mov LIKE '%" . $codice . "%'";
            }

           else
           {
               $array_condizione [] = "m.codice_mov LIKE '" . $codice . "'";
           }
        }
    }


    $flag_convalidazione = " and l.stato = 'Convalidato' ";
    if (isset($stato))
    {
        if (!empty($stato))
        {
            if ($stato != -1)
            {
                $flag_stato = (int) ($stato == 1 ? 1 : 0); // se 1 convalidato altrimenti non convalidato
                if ($flag_stato == 0) // se zero, ovvero non ?? convalidato
                {// occore poter selezionare anche i non convalidati
                    $flag_convalidazione = " ";
                }
                $array_condizione [] = "m.flag = " . $flag_stato;
            }
        }
    }

    if (isset($mag_sorgente))
    {
        if (!empty($mag_sorgente))
        {
            if ($mag_sorgente != -1)
            {
                $array_condizione [] = "m.mag_sorgente = " . $mag_sorgente;
            }
        }
    }
    if (isset($mag_dest))
    {
        if (!empty($mag_dest))
        {
            if ($mag_dest != -1)
            {
                if ($user->tipologia == "T")
                {
                    $array_condizione [] = "m.mag_sorgente = " . $id_m . " and m.mag_dest = " . $mag_dest;
                } else
                    $array_condizione [] = "m.mag_dest = " . $mag_dest;
            }
        }
    }
    if (isset($data_convalida))
    {
        if (!empty($data_convalida))
        {
            $array_condizione [] = "m.data_convalida LIKE '" . $data_convalida . "'";
        }
    }




    $condizione_utenza = "";

    if ($user->tipologia == "T" || $user->tipologia == "M" || $user->tipologia == "A")
    {


        // $array_condizione[] = "  mag_sorgente = " . $id_m;
        if (isset($mov_in_out))
        {
            if (!empty($mov_in_out))
            {
                if ($mov_in_out != -1)
                {
                    if ($mov_in_out == 1)
                    {
                        $array_condizione [] = "m.mag_dest = " . $id_m;
                    } else if ($mov_in_out == 2)
                    {
                        $array_condizione [] = "m.mag_sorgente = " . $id_m;
                    }
                } else
                {
                    if (empty($array_condizione))
                        $array_condizione [] = "m.mag_dest = " . $id_m . " OR " . "m.mag_sorgente = " . $id_m;
                }
            } else
            {
                if (empty($array_condizione))
                    $array_condizione [] = "m.mag_dest = " . $id_m . " OR " . "m.mag_sorgente = " . $id_m;
            }
        }
    }
    $merge_condizioni = implode(" AND ", $array_condizione);
    $where = empty($merge_condizioni) ? "" : " WHERE " . $merge_condizioni;

    //export in excel
    $down = DOL_URL_ROOT . '/product/reportMagazzini/' . "mov_export_completo" . ".xls";
    $link = '<a href="' . $down . '">' . "Esporta tutto in excel" . '</a>';
    $filename = "reportMagazzini/" . "mov_export_completo" . ".xls";
    $fp = fopen($filename, 'w');

    //tipologia di utenti

    $query = "SELECT * FROM " . MAIN_DB_PREFIX . "form_assetmove as  m INNER JOIN " . MAIN_DB_PREFIX . "log_movimentazione as l ON l.id_movimentazione = m.codice_mov  $flag_convalidazione ";
    $query .= $where . " ";

    $query.= $db->order("l.data_movimentazione", $sortorder);
    $query.= $db->plimit($limit + 1, $offset);
    $res_query = $db->query($query);

    $excel = colonne_export();

    if ($res_query)
    {
        $num = $db->num_rows($res_query);
        $i = 0;

        if ($num == 1 && ($sall || $stato || $codice || $sbarcode) && $action != 'list')
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
        $root_tab = DOL_URL_ROOT;
        print '<div class="fiche">';
        print '<div class="tabs" data-type="horizontal" data-role="controlgroup">';
        print '<a class="tabTitle"> <img border="0" title="" alt="" src="' . $root_tab . '/theme/eldy/img/movimentazioni.png"> Storico movimentazioni</a>';
        print '<div class="inline-block tabsElem"> <a id="card" class="tab inline-block" href="' . $root_tab . '/product/movimentazione.php?mainmenu=products&leftmenu=product&type=5" data-role="button">Nuova movimentazione</a> </div>';
        print '<div class="inline-block tabsElem">
<a id="price" class="tab inline-block" href="' . $root_tab . '/product/transito.php?mainmenu=products&leftmenu=product&type=6&id=3" data-role="button">In transito</a>
</div>';

        print '<div class="inline-block tabsElem"> <a id="price" class="tab inline-block" href="' . $root_tab . '/product/daconvalidare.php?mainmenu=products&leftmenu=product&type=6&id=3" data-role="button">Da convalidare</a> </div>';
        print '<div class="inline-block tabsElem"> <a id="price" class="tabactive tab inline-block" href="' . $root_tab . '/product/storico.php?mainmenu=products&leftmenu=product&type=6&id=4" data-role="button">Storico</a> </div>';
        print '</div>';
        print '</div>';

        // Displays product removal confirmation
        if (GETPOST('delprod'))
            dol_htmloutput_mesg($langs->trans("ProductDeleted", GETPOST('delprod')));

        $param = "&amp;sref=" . $codice . ($sbarcode ? "&amp;sbarcode=" . $sbarcode : "") . "&amp;stato=" . $stato . "&amp;sall=" . $sall . "&amp;tosell=" . $tosell . "&amp;tobuy=" . $tobuy;
        $param.=($fourn_id ? "&amp;fourn_id=" . $fourn_id : "");
        $param.=($search_categ ? "&amp;search_categ=" . $search_categ : "");
        $param.=isset($type) ? "&amp;type=" . $type : "";
        $param .=!empty($mag_sorgente) ? "&amp;mag_sorgente=$mag_sorgente" : "";
        $param .="&mainmenu=products";
        $param .=!empty($stato_tecnico) ? "&amp;mag_dest=$mag_dest" : "";
        print_barre_liste($texte, $page, "storico.php", $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords, "asset.png");

        esporta($db, $where);
        $export = DOL_URL_ROOT . "/theme/eldy/img/export.png";
        print "<div id='ways' align='left'>";
        // print $link;
        $etichetta_esportazione = "Esporta tutto in excel";
        if (!empty($where))
        {
            $etichetta_esportazione = "Esporta la ricerca";
            $export = DOL_URL_ROOT . "/theme/eldy/img/export_ric.png";
        }

        print' <a href="' . $down . '">';
        print'<img style="width:35px; height:35px;" src="' . $export . '">';
        print'<strong>' . $etichetta_esportazione . '</strong>';
        print'</a>';


        print '</div>';

        if (!empty($catid))
        {
            print "<div id='ways'>";
            $c = new Categorie($db);
            $ways = $c->print_all_ways(' &gt; ', 'product/liste.php');
            print " &gt; " . $ways[0] . "<br>\n";
            print "</div><br>";
        }

        if (!empty($canvas) && file_exists(DOL_DOCUMENT_ROOT . '/product/canvas/' . $canvas . '/actions_card_' . $canvas . '.class.php'))
        {
            $fieldlist = $object->field_list;
            $datas = $object->list_datas;
            $picto = 'title.png';
            $title_picto = img_picto('', $picto);
            $title_text = $title;

            // Default templates directory
            $template_dir = DOL_DOCUMENT_ROOT . '/product/canvas/' . $canvas . '/tpl/';
            // Check if a custom template is present
            if (file_exists(DOL_DOCUMENT_ROOT . '/theme/' . $conf->theme . '/tpl/product/' . $canvas . '/list.tpl.php'))
            {
                $template_dir = DOL_DOCUMENT_ROOT . '/theme/' . $conf->theme . '/tpl/product/' . $canvas . '/';
            }

            include $template_dir . 'list.tpl.php'; // Include native PHP templates
        } else
        {
            $url_form = $_SERVER["PHP_SELF"] . "?mainmenu=products";
            print '<form action="' . $url_form . '" method="post" name="formulaire">';
            print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
            print '<input type="hidden" name="action" value="list">';
            print '<input type="hidden" name="sortfield" value="' . $sortfield . '">';
            print '<input type="hidden" name="sortorder" value="' . $sortorder . '">';
            print '<input type="hidden" name="type" value="' . $type . '">';

            print '<table class="liste" width="100%">';

            // Filter on categories
            $moreforfilter = '';
            $colspan = 6;
            if (!empty($conf->barcode->enabled))
                $colspan++;
            if (!empty($conf->service->enabled) && $type != 0)
                $colspan++;
            if (empty($conf->global->PRODUIT_MULTIPRICES))
                $colspan++;
            if ($user->rights->fournisseur->lire)
                $colspan++;
            if (!empty($conf->stock->enabled) && $user->rights->stock->lire && $type != 1)
                $colspan+=2;

            /*
              if (!empty($conf->categorie->enabled))
              {
              $moreforfilter.=$langs->trans('Categories') . ': ';
              $moreforfilter.=$htmlother->select_categories(0, $search_categ, 'search_categ', 1);
              $moreforfilter.=' &nbsp; &nbsp; &nbsp; ';
              }

             */
            if ($moreforfilter)
            {
                print '<tr class="liste_titre">';
                print '<td class="liste_titre" colspan="' . $colspan . '">';
                print $moreforfilter;
                print '</td></tr>';
            }

            // Lignes des titres
            print '<tr class="liste_titre">';
            print_liste_field_titre("Tipo", $_SERVER["PHP_SELF"], "m.mag_sorgente", $param, "", 'align="left"', $sortfield, $sortorder);
            print_liste_field_titre("Codice", $_SERVER["PHP_SELF"], "m.codice_mov", $param, "", "", $sortfield, $sortorder);
            print_liste_field_titre("Stato", $_SERVER["PHP_SELF"], "m.flag", $param, "", "", $sortfield, $sortorder);
            print_liste_field_titre("Magazzino di partenza", $_SERVER["PHP_SELF"], "m.mag_sorgente", $param, "", 'align="left"', $sortfield, $sortorder);
            print_liste_field_titre("Magazzino destinazione", $_SERVER["PHP_SELF"], "m.mag_dest", $param, "", 'align="center"', $sortfield, $sortorder);
            print_liste_field_titre("Doc.Trasporto", $_SERVER["PHP_SELF"], "m.id_ddt", $param, "", 'align="center"', $sortfield, $sortorder);


            // print '<td class="liste_titre" align="right">' . "Data creazione" . '</td>';
            print '<td width="10%">&nbsp;</td>';

            print_liste_field_titre("Data movimentazione", $_SERVER["PHP_SELF"], "m.data_convalida", $param, "", 'align="center"', $sortfield, $sortorder);
            // print '<td>' . "Data movimentazione" . '</td>';



            print '<td width="10%">&nbsp;</td>';
            print '<td width="10%">&nbsp;</td>';
            print "</tr>\n";

            // i box di ricerca
            print '<tr class="liste_titre">';

            print '<td align="center">';
            print $form->selectarray('mov_in_out', array('1' => "In", '2' => "Out"), $mov_in_out, 1);
            print '</td >';

            print '<td class="liste_titre" align="left">';
            print '<input class="flat" type="text" name="sref" size="10" value="' . $codice . '">';
            print '</td>';

            print '<td align="center">';
            print $form->selectarray('stato', array('1' => "Convalidato", '2' => "Non convalidato"), $flag, 1);
            print '</td >';

            print '<td align="center">';
            print $form->selectarray('mag_sorgente', $statutarray, $mag_sorgente, 1);
            print '</td >';

            print '<td align="center">';
            print $form->selectarray('mag_dest', $statutarray_dest, $mag_dest, 1);
            print '</td >';

            $icona_pdf = DOL_URL_ROOT . "/theme/eldy/img/pdf2.png";
            print '<td align="center">';
            print'<img  src="' . $icona_pdf . '">';
            print '</td>';
            print '<td class="liste_titre">';
            print '&nbsp;';
            print '</td>';

            print'<script>
  $(function() {
    $( "#datepicker" ).datepicker();
  });
  </script>';

            print '<td><input type="text" name="data_convalida" id="datepicker" value="' . $data_convalida . '" ></td>';


            //combo data da aggiungere con jquery
            /*
              print '<td align="center">';
              print $form->selectarray('stato_fisico', array('1' => "Giacenza", '2' => "In uso", '3' => "Transito", '4' => "In lab", '5' => "Dismesso"), $stato_fisico, 1);
              print '</td >';
             */

            // Sell price
            if (empty($conf->global->PRODUIT_MULTIPRICES))
            {
                print '<td class="liste_titre">';
                print '&nbsp;';
                print '</td>';
            }
            // Minimum buying Price
            if ($user->rights->fournisseur->lire)
            {
                print '<td class="liste_titre">';
                print '&nbsp;';
                print '</td>';
            }

            //disegna lente di ricerca
            print '<td class="liste_titre" align="right">';
            print '<input type="image" class="liste_titre" name="button_search" src="' . img_picto($langs->trans("Search"), 'search.png', '', '', 1) . '" value="' . dol_escape_htmltag($langs->trans("Search")) . '" title="' . dol_escape_htmltag($langs->trans("Search")) . '">';
            print '<input type="image" class="liste_titre" name="button_removefilter" src="' . img_picto($langs->trans("Search"), 'searchclear.png', '', '', 1) . '" value="' . dol_escape_htmltag($langs->trans("Search")) . '" title="' . dol_escape_htmltag($langs->trans("Search")) . '">';
            print '</td>';
            print '</tr>';

            $product_static = new Product($db);
            $product_fourn = new ProductFournisseur($db);
            $var = true;
            while ($i < min($num, $limit))
            {
                $obj_mov = $db->fetch_object($res_query);
                $var = !$var;
                print '<tr ' . $bc[$var] . '>';
                if ($obj_mov->mag_dest == $user_id_form_mag)
                {
                    print "<td>In</td>";
                }
                if ($obj_mov->mag_sorgente == $user_id_form_mag)
                {
                    print "<td>Out</td>";
                }
                print '<td class="nowrap">';
                $link_mov = '<a href="' . DOL_URL_ROOT . '/product/scheda_movimentazione.php?mainmenu=products&id=' . $obj_mov->codice_mov . '" target="_blank">' . $obj_mov->codice_mov . '</a></td>';
                print $link_mov;
                print "</td>\n";

                $stato_convalida = ((int) $obj_mov->flag) == 1 ? "Convalidato" : "Non convalidato";
                print '<td>' . $stato_convalida . '</td>';

                $obj_magazzino = new magazzino($db);
                $magazzino_nome_sorgente = $obj_magazzino->getMagazzino($obj_mov->mag_sorgente);
                $magazzino_nome_sorgente = $magazzino_nome_sorgente[0]['label'];
                print '<td>' . $magazzino_nome_sorgente . '</td>';

                $magazzino_nome_dest = $obj_magazzino->getMagazzino($obj_mov->mag_dest);
                $magazzino_nome_dest = $magazzino_nome_dest[0]['label'];
                print '<td>' . $magazzino_nome_dest . '</td>';


                $link = DOL_URL_ROOT . "/product/ddt/" . $obj_mov->id_ddt . ".pdf";
                print'<td> <a href="' . $link . '" target="_blank">';
                print'<strong>' . $obj_mov->id_ddt . '</strong>';
                print'</a></td>';

                print '<td>' . "" . '</td>';

                print '<td>' . $obj_mov->data_convalida . '</td>';
                print "</tr>\n";
                $i++;
            }
            //print $excel;

            $param = "&amp;sref=" . $codice . ($sbarcode ? "&amp;sbarcode=" . $sbarcode : "") . "&amp;stato=" . $stato . "&amp;sall=" . $sall . "&amp;tosell=" . $tosell . "&amp;tobuy=" . $tobuy;
            $param.=($fourn_id ? "&amp;fourn_id=" . $fourn_id : "");
            $param.=($search_categ ? "&amp;search_categ=" . $search_categ : "");
            $param .=!empty($mag_sorgente) ? "&amp;mag_sorgente=$mag_sorgente" : "";
            $param .=!empty($mag_dest) ? "&amp;mag_dest=$mag_dest" : "";
            print_barre_liste('', $page, "storico.php", $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords);

            $db->free($resql);

            print "</table>";
            print '</form>';
        }
    } else
    {
        dol_print_error($db);
    }
}

function esporta($db, $where_condition)
{

    $query = "SELECT * FROM " . MAIN_DB_PREFIX . "form_assetmove as m " . $where_condition;
    $res_query = $db->query($query);
    $down = DOL_URL_ROOT . '/product/reportMagazzini/' . "mov_export_movimentazione" . ".xls";
    $link = '<a href="' . $down . '">' . "mov_export_completo" . '</a></td>';
    $filename = "reportMagazzini/" . "mov_export_completo" . ".xls";
    $fp = fopen($filename, 'w');
    if ($res_query)
    {
        $excel = colonne_export();
        //procedura per recuperare il numero bolla
        $obj_movimentazione = new myMovimentazione($db);

        while ($obj_mov = $db->fetch_object($res_query))
        {
            $obj_magazzino = new magazzino($db);
            $magazzino_nome_sorgente = $obj_magazzino->getMagazzino($obj_mov->mag_sorgente);
            $magazzino_nome_sorgente = $magazzino_nome_sorgente[0]['label'];

            $magazzino_nome_dest = $obj_magazzino->getMagazzino($obj_mov->mag_dest);
            $magazzino_nome_dest = $magazzino_nome_dest[0]['label'];

            $causale = $obj_mov->causale_trasp;
            $luogo_dest = $obj_mov->luogo_dest;
            $trasp_mezzo = "";
            switch ($obj_mov->trasporto_mezzo)
            {
                case 1:
                    $trasp_mezzo = "Mittente";
                    break;
                case 2:
                    $trasp_mezzo = "Vettore";
                    break;
                case 3:
                    $trasp_mezzo = "Destinatario";
                    break;
            }
            $data_ritiro = $obj_mov->data_ritiro;
            $data_convalidazione = $obj_mov->data_convalida;
            $annotazioni = $obj_mov->annotazioni;
            $codici_asset = $obj_mov->checkbox_asset;
            $altro_righe = $array_righe = json_decode($obj_mov->righe_altro, true);
            $righe_altro_excel = array();
            $ciao = "";
            if (!empty($array_righe))
            {
                foreach ($array_righe AS $chiave => $riga)
                {
                    $ciao = "ciao";
                    $codice = empty($riga[0]) ? "" : " <strong>cod:</strong>" . $riga[0];
                    $desc = empty($riga[1]) ? "" : " <strong>desc:</strong>" . $riga[1];
                    $qt = empty($riga[2]) ? "" : " <strong>qt:</strong>" . $riga[2];
                    $righe_altro_excel [] = $codice . $desc . $qt;
                }
            }
            $array_mag_generico = json_decode($obj_mov->info_mag_altro, true);
            $info_mag_generico = "";
            if (!empty($array_mag_generico))
            {

                $nome_magazzino = empty($array_mag_generico['nome_gen']) ? "" : " <strong>nome mag.</strong>" . $array_mag_generico['nome_gen'];
                $rag_sociale = empty($riga['rag_sociale']) ? "" : " <strong>ragione sociale:</strong>" . $array_mag_generico['rag_sociale'];
                $indirizzo_gen = empty($array_mag_generico['indirizzo_gen']) ? "" : " <strong>indirizzo:</strong>" . $array_mag_generico['indirizzo_gen'];
                $citta_gen = empty($array_mag_generico['citta_gen']) ? "" : " <strong>citta:</strong>" . $array_mag_generico['citta_gen'];
                $cap_gen = empty($array_mag_generico['cap_gen']) ? "" : " <strong>cap:</strong>" . $array_mag_generico['cap_gen'];
                $prov_gen = empty($array_mag_generico['prov_gen']) ? "" : " <strong>prov:</strong>" . $array_mag_generico['prov_gen'];

                $info_mag_generico.="<br>" . $nome_magazzino . $rag_sociale . $indirizzo_gen . $citta_gen . $prov_gen;
            }
            //riempo excell
            $excel .= '<tr>';
            $excel .= '<td>' . $magazzino_nome_sorgente . '</td>';
            $excel .= '<td>' . $magazzino_nome_dest . $info_mag_generico . '</td>';
            $excel .= '<td>' . $causale . '</td>';
            $excel .= '<td>' . $luogo_dest . '</td>';
            $excel .= '<td>' . $trasp_mezzo . '</td>';
            $excel .= '<td>' . $data_ritiro . '</td>';
            $excel .= '<td>' . $data_convalidazione . '</td>';
            $excel .= '<td>' . $annotazioni . '</td>';
            $excel .= '<td>' . $codici_asset . '</td>';
            $excel .= '<td>' . implode(";", $righe_altro_excel) . '</td>';
            $excel .= '</tr>';
        }
    }


    $excel .="</table>";

    fwrite($fp, $excel);
}

function colonne_export()
{
    $html = '<br><table border="1" class="noborder">';
    $html .= '<tr>';
    $html .= '<td>' . "<strong>Magazzino di partenza</strong>" . '</td>';
    $html .= '<td>' . "<strong>Magazzino di destinazione </strong>" . '</td>';
    $html .= '<td>' . "<strong>Causale del trasporto </strong>" . '</td>';
    $html .= '<td>' . "<strong>Luogo di destinazione </strong>" . '</td>';
    $html .= '<td>' . "<strong>Trasporto a mezzo </strong>" . '</td>';
    $html .= '<td>' . "<strong>Data di ritiro </strong>" . '</td>';
    $html .= '<td>' . "<strong>Data di convalidazione </strong>" . '</td>';
    $html .= '<td>' . "<strong>Annotazioni </strong>" . '</td>';
    $html .= '<td>' . "<strong>Codice asset movimentati </strong>" . '</td>';
    $html .= '<td>' . "<strong>Altro </strong>" . '</td>';

    $html .= '</tr>';
    return $html;
}

llxFooter();
$db->close();
