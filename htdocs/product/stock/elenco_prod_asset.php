<?php

require_once DOL_DOCUMENT_ROOT . '/product/myclass/myMagazzino.php';
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myAsset.php';
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myMovimentazione.php';
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myScortaprodotto.php'; // serve per il ddt
$langs->load("products");
$langs->load("stocks");
$langs->load("suppliers");

$action = GETPOST('action');
$codice_prodotto = GETPOST("ref");
$codice = GETPOST("sref");
$sbarcode = GETPOST("sbarcode");
$etichetta = GETPOST("snom");
$id_magazzino = $id; // GETPOST("id_magazzino");
$sall = GETPOST("sall");
$type = GETPOST("type", "int");
$search_sale = GETPOST("search_sale");
$search_categ = GETPOST("search_categ", 'int');
$stato_fisico = GETPOST("stato_fisico");
$stato_tecnico = GETPOST("stato_tecnico");
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
$limit = 25; // limite per la pagina (modificato da amin) asset
$limit_prod = 25;
$limit_tot = (int) ($limit + $limit_prod);
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


$title = $langs->trans("ProductsAndServices");


$texte = "Elenco asset e prodotti";

//lista magazzino della select
$mag_obj = new magazzino($db);
$user_id = $user->id;
$where_condition = "";

$where_condition = " WHERE rowid = $id "; // nel caso delgli user specifici
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
$where_prod = "";


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

if (isset($stato_fisico))
{
    if (!empty($stato_fisico))
    {
        if ($stato_fisico != -1)
        {
            $array_condizione [] = "a.stato_fisico = " . $stato_fisico;
        }
    }
}

if (isset($codice))
{
    if (!empty($codice))
    {
        $array_condizione [] = "a.matricola LIKE '%" . $codice . "%'";
    }
}
$flag_stat_fisico = false;
if (isset($stato_fisico))
{
    if (!empty($stato_fisico))
    {
        if ($stato_fisico != -1)
        {
            $array_condizione [] = "a.stato_fisico = " . $stato_fisico;
            $flag_stat_fisico = true;
        }
    }
}
$flag_stat_tecnico = false;
if (isset($stato_tecnico))
{
    if (!empty($stato_tecnico))
    {
        if ($stato_tecnico != -1)
        {
            $array_condizione [] = "a.stato_tecnico = " . $stato_tecnico;
            $flag_stat_tecnico = true;
        }
    }
}
if (isset($etichetta))
{
    if (!empty($etichetta))
    {

        $array_condizione [] = "a.label LIKE '%" . $etichetta . "%'";
        //$where_prod [] = " AND p.label LIKE '%" . $etichetta . "%'";
    }
}


$merge_condizioni = implode(" AND ", $array_condizione);
$where = empty($merge_condizioni) ? "" : " WHERE " . $merge_condizioni;

//export in excel
$down = DOL_URL_ROOT . '/product/stock/reportMagazzini/' . "export_completo_asset" . ".xls";
$down_prod = DOL_URL_ROOT . '/product/stock/reportMagazzini/' . "export_completo_prodotti" . ".xls";
$link = '<a href="' . $down . '">' . "Esporta tutto in excel" . '</a>';
$filename = "reportMagazzini/" . "export_completo" . ".xls";
$fp = fopen($filename, 'w');

//print $link;


$query = "SELECT * ";
$query .= " FROM " . MAIN_DB_PREFIX . "asset as a " . $where . " ";
$query.= $db->order("a.cod_asset", $sortorder);
$query.= $db->plimit($limit + 1, $offset);
$res_query = $db->query($query);

$flag_ref_prod = false;
if (isset($codice_prodotto))
{
    if (!empty($codice_prodotto))
    {
        $flag_ref_prod = true;
        $res_query = false;
        $where_prod = " AND p.ref LIKE '%" . $codice_prodotto . "%'";
    }
}

/*
  $sql_prod = "SELECT * ";
  $sql_prod .= "FROM " . MAIN_DB_PREFIX . "product_stock as sp INNER JOIN " . MAIN_DB_PREFIX . "product as p ON p.rowid = sp.fk_product";
  $sql_prod .= " WHERE sp.fk_product = p.rowid and sp.fk_entrepot = " . $id . $where_prod; // prendo tutti prodotti che si trovano nel magazzizo selezionato
  $sql_prod.= $db->order("sp.tms", $sortorder);
  $sql_prod.= $db->plimit($limit + 1, $offset);
 */

$sql_prod = "SELECT * ";
$sql_prod .= " FROM " . MAIN_DB_PREFIX . "product as p ";
$sql_prod .= " WHERE p.fk_product_type=0 " . $where_prod;
$sql_prod.= $db->plimit($limit_prod + 1, $offset);


$res_sql_prod = $db->query($sql_prod);
if ($flag_stat_tecnico)
{
    $res_sql_prod = false;
}
if ($flag_stat_fisico)
{
    $res_sql_prod = false;
}


$excel = colonne_export();
$num = 0;


 $num = $db->num_rows($res_query);
$num_prod = $db->num_rows($res_sql_prod);
$tot_num = (int) ($num + $num_prod);

$i = 0;

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

//llxHeader('', $title, $helpurl, '');


$param = "&id=" . $id;
$param .= "&amp;ref=" . $codice_prodotto . "&amp;sref=" . $codice . ($sbarcode ? "&amp;sbarcode=" . $sbarcode : "") . "&amp;snom=" . $etichetta . "&amp;sall=" . $sall . "&amp;tosell=" . $tosell . "&amp;tobuy=" . $tobuy;
$param.=($fourn_id ? "&amp;fourn_id=" . $fourn_id : "");
$param.=($search_categ ? "&amp;search_categ=" . $search_categ : "");
$param.=isset($type) ? "&amp;type=" . $type : "";
$param .=!empty($id_magazzino) ? "&amp;id_magazzino=$id_magazzino" : "";
$param .=!empty($stato_fisico) ? "&amp;stato_fisico=$stato_fisico" : "";
$param .="&mainmenu=products";

$param .=!empty($stato_tecnico) ? "&amp;stato_tecnico=$stato_tecnico" : "";
print_barre_liste($texte, $page, "fiche.php", $param, $sortfield, $sortorder, '', $tot_num, $nbtotalofrecords, "asset.png");

$export = DOL_URL_ROOT . "/theme/eldy/img/export.png";
print "<div id='ways' align='left'>";
// print $link;
$etichetta_esportazione = "Esportazione asset";
$etichetta_prodotto = "Esportazione prodotto";
if (!empty($where))
{
    $etichetta_esportazione = "Esporta gli asset del magazzino";
    $export = DOL_URL_ROOT . "/theme/eldy/img/export_ric.png";
}
if (!empty($where_prod))
{
    $etichetta_prodotto = "Esporta i prodotti del  magazzino";
    $export = DOL_URL_ROOT . "/theme/eldy/img/export_ric.png";
}


print'<a href="' . $down . '">';
print'<img style="width:35px; height:35px;" src="' . $export . '">';
print'<td><strong>' . $etichetta_esportazione . '</strong>';
print'</a>';

print'<a href="' . $down_prod . '">';
print'<img style="width:35px; height:35px;" src="' . $export . '">';
print'<td><strong>' . $etichetta_prodotto . '</strong>';
print'</a>';


esporta($db, $where);
esporta_prodotti($db, $where_prod, $id);
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
    $url_form = $_SERVER["PHP_SELF"] . "?mainmenu=products&id=" . $id;
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
    print_liste_field_titre("Rif.", $_SERVER["PHP_SELF"], "a.cod_asset", $param, "", "", $sortfield, $sortorder);
    //print_liste_field_titre("Qt", $_SERVER["PHP_SELF"], "a.cod_asset", $param, "", "", $sortfield, $sortorder);

    print_liste_field_titre("Matricola", $_SERVER["PHP_SELF"], "a.matricola", $param, "", "", $sortfield, $sortorder);
    //print_liste_field_titre($langs->trans("Label"), $_SERVER["PHP_SELF"], "a.label", $param, "", "", $sortfield, $sortorder);
    if (empty($conf->global->PRODUIT_MULTIPRICES))
        print_liste_field_titre("Magazzino", $_SERVER["PHP_SELF"], "a.id_magazzino", $param, "", 'align="left"', $sortfield, $sortorder);
    //print_liste_field_titre("Stato fisico", $_SERVER["PHP_SELF"], "a.stato_fisico", $param, "", 'align="center"', $sortfield, $sortorder);
    //print_liste_field_titre("Stato tecnico", $_SERVER["PHP_SELF"], "a.stato_tecnico", $param, "", 'align="center"', $sortfield, $sortorder);

    if (!empty($conf->barcode->enabled))
        print_liste_field_titre($langs->trans("BarCode"), $_SERVER["PHP_SELF"], "p.barcode", $param, '', '', $sortfield, $sortorder);


    // print '<td class="liste_titre" align="right">' . "Data creazione" . '</td>';
    print_liste_field_titre("Rif. Bolla", $_SERVER["PHP_SELF"], "a.rif_bolla", $param, "", 'align="center"', $sortfield, $sortorder);
    print_liste_field_titre("Data Spedizione", $_SERVER["PHP_SELF"], "a.data_spedizione", $param, "", 'align="center"', $sortfield, $sortorder);

    print_liste_field_titre("Data Ricezione", $_SERVER["PHP_SELF"], "a.data_ricezione", $param, "", 'align="center"', $sortfield, $sortorder);


    print '<td width="1%">&nbsp;</td>';
    print "</tr>\n";

    // Lignes des champs de filtre
    print '<tr class="liste_titre">';

    print '<td class="liste_titre" align="left">';
    print '<input class="flat" type="text" name="ref" size="20" value="' . $codice_prodotto . '">';
    print '</td>';

    /*print '<td class="liste_titre">';
    print '&nbsp;';
    print '</td>';*/

    print '<td class="liste_titre" align="left">';
    print '<input class="flat" type="text" name="sref" size="20" value="' . $matricola . '">';
    print '</td>';


    /*print '<td class="liste_titre" align="left">';
    print '<input class="flat" type="text" name="snom" size="15" value="' . $etichetta . '">';
    print '</td>';
    */

    /*
      // Date modification
      print '<td class="liste_titre">';
      print '&nbsp;';
      print '</td>';

     */

    print '<td align="center">';
    print $form->selectarray('id_magazzino', $statutarray, $id_magazzino, 1);
    print '</td >';

    /*print '<td align="center">';
    print $form->selectarray('stato_fisico', array('1' => "Giacenza", '2' => "In uso", '3' => "Transito", '4' => "In lab", '5' => "Dismesso"), $stato_fisico, 1);
    print '</td >';

    print '<td align="center">';
    print $form->selectarray('stato_tecnico', array('1' => "Nuovo", '2' => "Ricondizionato", '3' => "Guasto", '4' => "Sconosciuto"), $stato_tecnico, 1);
    print '</td>';*/

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

    $var = true;
    $flag = 0;
    while ($i < min($tot_num, $limit_tot))
    {
        $flag = 1;
        if ($res_query)
            $obj_asset = $db->fetch_object($res_query);
        else
            $obj_asset = array();
        while ($obj_prod = $db->fetch_object($res_sql_prod))
        {

            $var = !$var;

            if (!is_null($obj_prod))
            {


                //     $prodotto = getProdotto($db, $obj_prod->fk_product);

                /*
                  print '<td class="nowrap">';
                  $link_asset = '<a href="' . DOL_URL_ROOT . '/product/fiche.php?mainmenu=products&id=' . $obj_prod->fk_product . '" >' . $prodotto->ref . '</a></td>';
                  print $link_asset;
                  print "</td>\n";

                 */
                $fk_prodotto = $obj_prod->rowid;
                $obj_prod_view = getScortaProdoto($db, $fk_prodotto, $id);

                if (!empty($obj_prod_view))
                {
                    print '<tr ' . $bc[$var] . '>';
                    print "<td>" . $obj_prod->ref . "</td>";
                    print "<td>" . $obj_prod_view[0]['reel'] . "</td>";
                    print "<td> </td>";
                    print "<td>" . $obj_prod->label . "</td>";
                    $obj_magazzino = new magazzino($db);
                    $magazzino_nome = $obj_magazzino->getMagazzino($obj_prod_view[0]['fk_entrepot']);
                    $magazzino_nome = $magazzino_nome[0]['label'];
                    print '<td>' . $magazzino_nome . '</td>';
                    print "<td> </td>";
                    print "<td> </td>";

                    print "</tr>\n";
                    $var = !$var;
                }
            }
        }
        if (!empty($obj_asset))
        {
            
            print '<tr ' . $bc[$var] . '>';
            print '<td>' . $obj_asset->cod_famiglia . '</td>';
            //print "<td> 1 </td>";
            print '<td class="nowrap">';
            //$link_asset = '<a href="' . DOL_URL_ROOT . '/product/scheda_asset.php?mainmenu=products&cod_asset=' . $obj_asset->cod_asset . '" >' . $obj_asset->cod_asset . '</a></td>';
            print $obj_asset->matricola;
            print "</td>\n";

            //print '<td>' . $obj_asset->label . '</td>';

            $obj_magazzino = new magazzino($db);
            $magazzino_nome = $obj_magazzino->getMagazzino($obj_asset->id_magazzino);
            $magazzino_nome = $magazzino_nome[0]['label'];
            print '<td>' . $magazzino_nome . '</td>';
            /*$str_stato_fisico = "";
            switch ($obj_asset->stato_fisico)
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
            print '<td>' . $str_stato_fisico . '</td>';
            $str_stato_tecnico = "";
            switch ($obj_asset->stato_tecnico)
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
            print '<td>' . $str_stato_tecnico . '</td>';*/

            print '<td>' . $obj_asset->rif_bolla . '</td>';

            print '<td>' . $obj_asset->data_spedizione . '</td>';

            print '<td>' . $obj_asset->data_ricezione . '</td>';
            print "</tr>\n";
        }
         
        $i++;
    }
   
    //print $excel;
    $param = "&id=" . $id;
    $param .= "&amp;ref=" . $codice_prodotto . "&amp;sref=" . $codice . ($sbarcode ? "&amp;sbarcode=" . $sbarcode : "") . "&amp;snom=" . $etichetta . "&amp;sall=" . $sall . "&amp;tosell=" . $tosell . "&amp;tobuy=" . $tobuy;
    $param.=($fourn_id ? "&amp;fourn_id=" . $fourn_id : "");
    $param.=($search_categ ? "&amp;search_categ=" . $search_categ : "");
    $param .=!empty($id_magazzino) ? "&amp;id_magazzino=$id_magazzino" : "";
    $param .=!empty($stato_fisico) ? "&amp;stato_fisico=$stato_fisico" : "";
    $param .=!empty($stato_tecnico) ? "&amp;stato_tecnico=$stato_tecnico" : "";
    print_barre_liste('', $page, "fiche.php", $param, $sortfield, $sortorder, '', $tot_num, $nbtotalofrecords);

    $db->free($resql);

    print "</table>";
    print '</form>';
}

function esporta($db, $where_condition)
{

    $query = "SELECT * ";
    $query .= " FROM " . MAIN_DB_PREFIX . "asset as a " . $where_condition . " ";
    $res_query = $db->query($query);
    $down = DOL_URL_ROOT . '/product/stock/reportMagazzini/' . "export_completo_asset" . ".xls";
    $link = '<a href="' . $down . '">' . "export_completo" . '</a></td>';
    $filename = DOL_DOCUMENT_ROOT . "/product/stock/reportMagazzini/" . "export_completo_asset" . ".xls";

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
            $codice_famiglia = $obj_asset->cod_famiglia;
            $matricola = $obj_asset->matricola;
            $etichetta = $obj_asset->label;
            $desc = $obj_asset->descrizione;
            $str_stato_fisico = "";
            switch ($obj_asset->stato_fisico)
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
            $str_stato_tecnico = "";
            switch ($obj_asset->stato_tecnico)
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

            $marca = $obj_asset->brand;
            $modello = $obj_asset->model;
            $data_modifica = $obj_asset->data_modifica;
            $my_where_condition = " WHERE mag_dest = " . $obj_asset->id_magazzino . " AND flag=1 ORDER BY id DESC";
            $numero_bolla = $obj_movimentazione->getNumeroBolla($obj_asset->cod_asset, $obj_asset->id_magazzino, $my_where_condition);
            $corridoio = $obj_asset->corridoio;
            $scaffali = $obj_asset->scaffali;
            $ripiano = $obj_asset->ripiano;

            //riempo excell
            $excel .= '<tr>';
            $excel .= '<td>' . $magazzino_nome . '</td>';
            $excel .= '<td>' . $codice_famiglia . '</td>';
            $excel .= '<td>' . $codice_asset . '</td>';
            $excel .= '<td>' . $desc . '</td>';
            $excel .= '<td>' . $desc . '</td>';
            $excel .= '<td>' . $str_stato_fisico . '</td>';
            $excel .= '<td>' . $str_stato_tecnico . '</td>';
            $excel .= '<td>' . $marca . '</td>';
            $excel .= '<td>' . $modello . '</td>';
            $excel .= '<td>' . $data_modifica . '</td>';

            $excel .= '<td>' . $numero_bolla . '</td>';
            $excel .= '<td>' . $corridoio . '</td>';
            $excel .= '<td>' . $scaffali . '</td>';
            $excel .= '<td>' . $ripiano . '</td>';
            $excel .= '</tr>';
        }
    }


    $excel .="</table>";

    fwrite($fp, $excel);
    fclose($fp);
}

function esporta_prodotti($db, $where_prod, $id)
{
    $sql_prod = "SELECT * ";
    $sql_prod .= " FROM " . MAIN_DB_PREFIX . "product as p ";
    $sql_prod .= " WHERE p.fk_product_type=0 " . $where_prod;
    $res = $db->query($sql_prod);
    
    $down = DOL_URL_ROOT . '/product/stock/reportMagazzini/' . "export_completo_prodotti" . ".xls";
    $link = '<a href="' . $down . '">' . "export_prodotti" . '</a></td>';
    $filename = DOL_DOCUMENT_ROOT . "/product/stock/reportMagazzini/" . "export_completo_prodotti" . ".xls";

    $fp = fopen($filename, 'w');
    if ($res)
    {
        $excel = col_prodotti();
        while ($obj_prod = $db->fetch_object($res))
        {
            if (!is_null($obj_prod))
            {
                $fk_prodotto = $obj_prod->rowid;
                $obj_prod_view = getScortaProdoto($db, $fk_prodotto, $id);

                if (!empty($obj_prod_view))
                {

                    $excel .= "<td>" . $obj_prod->ref . "</td>";
                    $excel .= "<td>" . $obj_prod->label . "</td>";
                    $excel .= "<td>" . $obj_prod_view[0]['reel'] . "</td>";
                    $obj_magazzino = new magazzino($db);
                    $magazzino_nome = $obj_magazzino->getMagazzino($obj_prod_view[0]['fk_entrepot']);
                    $magazzino_nome = $magazzino_nome[0]['label'];
                    $excel .= "<td>" . $magazzino_nome . "</td>";
                    $excel .= "</tr>";
                }
            }
             
        }
       $excel .="</table>";
         fwrite($fp, $excel);
         fclose($fp);
    }
}

    function col_prodotti()
    {
        $html = '<br><table border="1" class="noborder">';
        $html .= '<tr>';
        $html .= '<td>' . "<strong>Codice</strong>" . '</td>';
        $html .= '<td>' . "<strong>Etichetta</strong>" . '</td>';
        $html .= '<td>' . "<strong>QT utilizzato dal magazzino</strong>" . '</td>';
        $html .= '<td>' . "<strong>Magazzino</strong>" . '</td>';
        $html .= '</tr>';
        return $html;
    }

    function colonne_export()
    {
        $html = '<br><table border="1" class="noborder">';
        $html .= '<tr>';
        $html .= '<td>' . "<strong>Magazzino</strong>" . '</td>';
        $html .= '<td>' . "<strong>Codice famiglia</strong>" . '</td>';
        $html .= '<td>' . "<strong>Codice asset</strong>" . '</td>';
        $html .= '<td>' . "<strong>Etichetta</strong>" . '</td>';
        $html .= '<td>' . "<strong>Descrizione famiglia</strong>" . '</td>';
        $html .= '<td>' . "<strong>Stato fisico</strong>" . '</td>';
        $html .= '<td>' . "<strong>Stato tecnico</strong>" . '</td>';
        $html .= '<td>' . "<strong>Marca</strong>" . '</td>';
        $html .= '<td>' . "<strong>Modello</strong>" . '</td>';
        $html .= '<td>' . "<strong>Data Ultima Modifica</strong>" . '</td>';
        $html .= '<td>' . "<strong>Numero bolla</strong>" . '</td>';
        $html .= '<td>' . "<strong>Corridoio</strong>" . '</td>';
        $html .= '<td>' . "<strong>Scaffali</strong>" . '</td>';
        $html .= '<td>' . "<strong>Ripiano</strong>" . '</td>';
        $html .= '</tr>';
        return $html;
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

    function getScortaProdoto($db, $fk_prodotto, $fk_magazzino)
    {
        $query = "SELECT * ";
        $query .= " FROM " . MAIN_DB_PREFIX . "product_stock";
        $query .= " WHERE fk_product = " . $fk_prodotto . " AND fk_entrepot = " . $fk_magazzino;
        $res = $db->query($query);
        if ($res)
        {
            $obj_prodotti = array();
            while ($arr_asset = $res->fetch_array(MYSQLI_ASSOC))
            {
                $obj_prodotti[] = $arr_asset;
            }
            return $obj_prodotti;
        }
        return null;
    }
    