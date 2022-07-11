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
if (!empty($conf->categorie->enabled))
    require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';

$langs->load("products");
$langs->load("stocks");
$langs->load("suppliers");

$action = GETPOST('action');
$codice = GETPOST("sref");
$cod_famiglia = GETPOST("cod_famiglia");
$matricola = GETPOST("matricola");
$sbarcode = GETPOST("sbarcode");
$etichetta = GETPOST("snom");
$id_magazzino = GETPOST("id_magazzino");
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
    $sortorder = "ASC";

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
    $etichetta = "";
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


    $texte = "Elenco HW";

    //lista magazzino della select
    $mag_obj = new magazzino($db);
    $user_id = $user->id;
    $where_condition = "";
    if ($user->tipologia == "T")
        $where_condition = " WHERE fk_user = $user_id "; // nel caso delgli user specifici
    $order_by;
    $select;
    $lista_magazzini = $mag_obj->getMagazziniSelect("*", $where_condition);
    $statutarray = array();
    for ($i = 0; $i < count($lista_magazzini); $i++)
    {
        $magazzino = $lista_magazzini[$i];
        $elem = $magazzino['label'];
        $statutarray [$magazzino['rowid']] = $elem;
    }

    $array_condizione = array();
    //tipologia di magazzino
    if ($user->tipologia == "T")
    {
        $id_magazzino_tecnico = $lista_magazzini[0]['rowid'];
        if (!empty($id_magazzino_tecnico))
        {
            $array_condizione [] = "a.id_magazzino = " . $id_magazzino_tecnico;
        }
    }


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
            $posizione = strpos($codice, "'");
            $apostrofo = $codice[$posizione];
            if ($apostrofo === "'")
            {
                $codice[$posizione] = "-";
            }
            $array_condizione [] = "a.cod_asset LIKE '%" . $codice . "%'";
        }
    }

    if (isset($cod_famiglia))
    {
        if (!empty($cod_famiglia))
        {
            $posizione = strpos($cod_famiglia, "'");
            $apostrofo = $cod_famiglia[$posizione];
            if ($apostrofo === "'")
            {
                $cod_famiglia[$posizione] = "-";
            }
            $array_condizione [] = "a.cod_famiglia LIKE '%" . $cod_famiglia . "%'";
        }
    }



    if (isset($etichetta))
    {
        if (!empty($etichetta))
        {

            $array_condizione [] = "a.label LIKE '%" . $etichetta . "%'";
        }
    }
    $merge_condizioni = implode(" AND ", $array_condizione);
    $where = empty($merge_condizioni) ? "" : " WHERE " . $merge_condizioni;

    //export in excel
    $down = DOL_URL_ROOT . '/product/reportMagazzini/' . "export_completo" . ".xls";
    $link = '<a href="' . $down . '">' . "Esporta tutto in excel" . '</a>';
    $filename = "reportMagazzini/" . "export_completo" . ".xls";
    $fp = fopen($filename, 'w');

    //print $link;


    $query = "SELECT * ";
    $query .= " FROM " . MAIN_DB_PREFIX . "asset as a " . $where . " ";
    $query.= $db->order("a.cod_famiglia", $sortorder);
    $query.= $db->plimit($limit + 1, $offset);
    $res_query = $db->query($query);

    $excel = colonne_export();

    if ($res_query)
    {
        $num = $db->num_rows($res_query);
        $i = 0;

        if ($num == 1 && ($sall || $etichetta || $codice || $sbarcode) && $action != 'list')
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

        // Displays product removal confirmation
        if (GETPOST('delprod'))
            dol_htmloutput_mesg($langs->trans("ProductDeleted", GETPOST('delprod')));

        $param = "&amp;sref=" . $codice . ($sbarcode ? "&amp;sbarcode=" . $sbarcode : "") . "&amp;snom=" . $etichetta . "&amp;sall=" . $sall . "&amp;tosell=" . $tosell . "&amp;tobuy=" . $tobuy;
        $param.=($fourn_id ? "&amp;fourn_id=" . $fourn_id : "");
        $param.=($search_categ ? "&amp;search_categ=" . $search_categ : "");
        $param.=isset($type) ? "&amp;type=" . $type : "";
        $param .=!empty($id_magazzino) ? "&amp;id_magazzino=$id_magazzino" : "";
        $param .="&mainmenu=products";

        print_barre_liste($texte, $page, "elenco_asset.php", $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords, "asset.png");

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

        esporta($db, $where);
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
            print_liste_field_titre("Cod Famiglia", $_SERVER["PHP_SELF"], "a.cod_famiglia", $param, "", "", $sortfield, $sortorder);

            //print_liste_field_titre("Matricola", $_SERVER["PHP_SELF"], "a.matricola", $param, "", "", $sortfield, $sortorder);
            if (empty($conf->global->PRODUIT_MULTIPRICES))
                print_liste_field_titre("Magazzino", $_SERVER["PHP_SELF"], "a.id_magazzino", $param, "", 'align="left"', $sortfield, $sortorder);

            /*if (!empty($conf->barcode->enabled))
                print_liste_field_titre($langs->trans("BarCode"), $_SERVER["PHP_SELF"], "p.barcode", $param, '', '', $sortfield, $sortorder);
*/
            print_liste_field_titre("Totale pezzi HW", $_SERVER["PHP_SELF"], "a.scorta_tot", $param, "", 'align="center"', $sortfield, $sortorder);

            // print '<td class="liste_titre" align="right">' . "Data creazione" . '</td>';
            //print_liste_field_titre("Totale pezzi HW", $_SERVER["PHP_SELF"], "a.scorta_tot", $param, "", 'align="center"', $sortfield, $sortorder);

        print_liste_field_titre("pezzi HW dal cliente", $_SERVER["PHP_SELF"], "a.scorta_utilizzati", $param, "", 'align="center"', $sortfield, $sortorder);

print '<td width="1%">&nbsp;</td>';
print '<td width="1%">&nbsp;</td>';

            print '<td width="1%">&nbsp;</td>';
            print "</tr>\n";

            // Lignes des champs de filtre
            print '<tr class="liste_titre">';
            print '<td class="liste_titre" align="left">';
            print '<input class="flat" type="text" name="cod_famiglia" size="20" value="' . $cod_famiglia . '">';
            print '</td>';
            print '<td class="liste_titre" align="left">';
            print '<input class="flat" type="text" name="snom" size="15" value="' . $etichetta . '">';
            print '</td>';

            print '<td class="liste_titre" align="left">';
            print '<input class="flat" type="text" name="snom" size="15" value="' . $matricola . '">';
            print '</td>';

            /*
              // Date modification
              print '<td class="liste_titre">';
              print '&nbsp;';
              print '</td>';

             */
            print '<td align="center">';
            print $form->selectarray('id_magazzino', $statutarray, $id_magazzino, 1);
            print '</td >';

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
                $obj_asset = $db->fetch_object($res_query);

                $var = !$var;
                print '<tr ' . $bc[$var] . '>';

                /*print '<td class="nowrap">';
                $link_asset = '<a href="' . DOL_URL_ROOT . '/product/scheda_asset.php?mainmenu=products&cod_asset=' . $obj_asset->cod_asset . '" >' . $obj_asset->cod_asset . '</a></td>';
                print $link_asset;
                print "</td>\n";
*/
                print '<td>' . $obj_asset->cod_famiglia . '</td>';
                //print '<td align="center">' . $obj_asset->matricola . '</td>';
                $obj_magazzino = new magazzino($db);
                $magazzino_nome = $obj_magazzino->getMagazzino($obj_asset->id_magazzino);
                $magazzino_nome = $magazzino_nome[0]['label'];
                

                print '<td>' . $magazzino_nome . '</td>';

                print '<td align="center">' . $obj_asset->scorta_tot . '</td>';

                print '<td align="center">' . $obj_asset->scorta_utilizzati . '</td>';
                print '<td></td>';
                print '<td></td>';
                print '<td></td>';
                print "</tr>\n";
                $i++;
            }
            //print $excel;

            $param = "&amp;sref=" . $codice . ($sbarcode ? "&amp;sbarcode=" . $sbarcode : "") . "&amp;snom=" . $etichetta . "&amp;sall=" . $sall . "&amp;tosell=" . $tosell . "&amp;tobuy=" . $tobuy;
            $param.=($fourn_id ? "&amp;fourn_id=" . $fourn_id : "");
            $param.=($search_categ ? "&amp;search_categ=" . $search_categ : "");
            $param.=isset($type) ? "&amp;type=" . $type : "ciao";
            $param .=!empty($id_magazzino) ? "&amp;id_magazzino=$id_magazzino" : "";
            print_barre_liste('', $page, "elenco_asset.php", $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords);

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

    $query = "SELECT * ";
    $query .= " FROM " . MAIN_DB_PREFIX . "asset as a " . $where_condition . " ";
    $res_query = $db->query($query);
    $down = DOL_URL_ROOT . '/product/reportMagazzini/' . "export_completo" . ".xls";
    $link = '<a href="' . $down . '">' . "export_completo" . '</a></td>';
    $filename = "reportMagazzini/" . "export_completo" . ".xls";
    $fp = fopen($filename, 'w');
    if ($res_query)
    {
        $excel = colonne_export();
        //procedura per recuperare il numero bolla
        $obj_movimentazione = new myMovimentazione($db);

        while ($obj_asset = $db->fetch_object($res_query))
        {
            $obj_magazzino = new magazzino($db);
            $magazzino_nome = $obj_magazzino->getMagazzino($obj_asset->id_magazzino);
            $magazzino_nome = $magazzino_nome[0]['label'];
            $cod_famiglia = $obj_asset->cod_famiglia;
            $codice_asset = $obj_asset->cod_asset;
            $etichetta = $obj_asset->label;
            $desc = $obj_asset->descrizione;
            $matricola = $obj_asset->matricola;


            $marca = $obj_asset->brand;
            $modello = $obj_asset->model;
            $scorta_tot = $obj_asset->scorta_tot;
            $scorta_utilizzati = $obj_asset->scorta_utilizzati;
            $corridoio = $obj_asset->corridoio;
            $scaffali = $obj_asset->scaffali;
            $ripiano = $obj_asset->ripiano;

            //riempo excell
            $excel .= '<tr>';
            $excel .= '<td>' . $magazzino_nome . '</td>';
            $excel .= '<td>' . $codice_asset . '</td>';
            $excel .= '<td>' . $etichetta . '</td>';
            $excel .= '<td>' . $scorta_tot . '</td>';
            $excel .= '<td>' . $scorta_utilizzati . '</td>';
            $excel .= '<td>' . $desc . '</td>';
            $excel .= '<td>' . $marca . '</td>';
            $excel .= '<td>' . $modello . '</td>';
            $excel .= '<td>' . $corridoio . '</td>';
            $excel .= '<td>' . $scaffali . '</td>';
            $excel .= '<td>' . $ripiano . '</td>';
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
    $html .= '<td>' . "<strong>Magazzino</strong>" . '</td>';
    $html .= '<td>' . "<strong>Hardware</strong>" . '</td>';
    $html .= '<td>' . "<strong>DIT</strong>" . '</td>';
    $html .= '<td>' . "<strong>Totale numero pezzi</strong>" . '</td>';
    $html .= '<td>' . "<strong>Totale pezzi dal cliente</strong>" . '</td>';
    $html .= '<td>' . "<strong>Descrizione</strong>" . '</td>';
    $html .= '<td>' . "<strong>Marca</strong>" . '</td>';
    $html .= '<td>' . "<strong>Modello</strong>" . '</td>';
    $html .= '<td>' . "<strong>Corridoio</strong>" . '</td>';
    $html .= '<td>' . "<strong>Scaffali</strong>" . '</td>';
    $html .= '<td>' . "<strong>Ripiano</strong>" . '</td>';
    $html .= '</tr>';
    return $html;
}

llxFooter();
$db->close();
