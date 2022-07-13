<?php

/**
 *  \file       htdocs/product/liste.php
 *  \ingroup    produit
 *  \brief      Page to list products and services
 */
 $host = "localhost";
 // username dell'utente in connessione
 $user = "admin";
 // password dell'utente
 $password = "Iniziale1!?";
 // nome del database
 $db = "fd_ticket";

 $connessione = new mysqli($host, $user, $password, $db);

 $host = "localhost";
 // username dell'utente in connessione
 $user = "admin";
 // password dell'utente
 $password = "Iniziale1!?";
 // nome del database
 $db = "dolibarr";

 $connessione_dol = new mysqli($host, $user, $password, $db);





 $sql="SELECT CONCAT(firstname,' ',lastname) as nome FROM ost_staff WHERE isactive =1 AND staff_id NOT IN(1,3,5,14,63,77,78,79,80)";
 $result = $connessione->query($sql);
 $array_tecnici = array();
 while ($obj_prod = mysqli_fetch_object($result))
 {
   $array_tecnici[] = $obj_prod->nome;
 }

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/product/myclass/myMagazzino.php';

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
$bolla = $_REQUEST['bolla'];
$ticket = $_REQUEST['ticket'];

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

/*
 * Actions
 */

print '<div class="fiche">';
print '<div class="tabs" data-type="horizontal" data-role="controlgroup">';
print '<a class="tabTitle">
<img border="0" title="" alt="" src="' . $root . '"/theme/eldy/img/object_product.png">
Ricerca Asset
</a>';

print '<div class="inline-block tabsElem">
<a id="card"';

if (!isset($_GET['type']) || $_GET['type'] == "riepilogo")
    print 'class="tabactive tab inline-block" ';
else
    print 'class="tab inline-block" ';
print ' href="' . $root . '/product/ricerca_asset.php?mainmenu=products&type=riepilogo" data-role="button">Scorte</a>
</div>';


print '<div class="inline-block tabsElem">
<a id="card"';
if ($_GET['type'] == "ricerca")
    print 'class="tabactive tab inline-block" ';
else
    print 'class="tab inline-block" ';
print 'href="' . $root . '/product/ricerca_asset.php?mainmenu=products&type=ricerca" data-role="button">Ricerca</a>
</div>';

print '<div class="inline-block tabsElem">
<a id="card"';
if ($_GET['type'] == "ricerca_matricole")
    print 'class="tabactive tab inline-block" ';
else
    print 'class="tab inline-block" ';
print 'href="' . $root . '/product/ricerca_asset.php?mainmenu=products&type=ricerca_matricole" data-role="button">Ricerca Matricole</a>
</div>';

/*print '<div class="inline-block tabsElem">
<a id="card"';
if ($_GET['type'] == "ricerca_matricole_mag")
    print 'class="tabactive tab inline-block" ';
else
    print 'class="tab inline-block" ';
print 'href="' . $root . '/product/ricerca_asset.php?mainmenu=products&type=ricerca_matricole_mag" data-role="button">Ricerca Matricole MAG</a>
</div>';*/

print '<div class="inline-block tabsElem">
<a id="card"';
if ($_GET['type'] == "ricerca_ticket")
    print 'class="tabactive tab inline-block" ';
else
    print 'class="tab inline-block" ';
print 'href="' . $root . '/product/ricerca_asset.php?mainmenu=products&type=ricerca_ticket" data-role="button">Link Ticket</a>
</div>';


print '</div>';

if (!isset($_GET['type']) || $_GET['type'] == "riepilogo")
{


    $sql = "SELECT * FROM llx_product WHERE hidden = 0 ORDER BY (CASE WHEN ref LIKE '%COD%' THEN 2 ELSE 1 END),ref ASC ";
    $result = $db->query($sql);
    print '<div class="tabBar">';
    print '<table class="border" width="100%">';
    print '<tr>';
    print '<td><strong>Cod Famiglia</strong></td>';
    print '<td><strong>Tot</strong></td>';



    $sql_mag = "SELECT * FROM llx_entrepot WHERE rowid = '7'";
    $result_mag = $db->query($sql_mag);
    $result_m= $db->fetch_object($result_mag);
    print '<td><strong>'.$result_m->label.'</strong></td>';

    $sql_mag = "SELECT * FROM llx_entrepot WHERE rowid = '6'";
    $result_mag = $db->query($sql_mag);
    $result_m= $db->fetch_object($result_mag);
    print '<td><strong>'.$result_m->label.'</strong></td>';


    $sql_mag = "SELECT * FROM llx_entrepot WHERE rowid != '6' AND rowid != '7' AND rowid != '8' AND rowid != '9' AND rowid != '17' AND statut=1 ORDER BY rowid ASC";
    $result_mag = $db->query($sql_mag);
    while($result_m= $db->fetch_object($result_mag))
    {
        if(in_array($result_m->label,$array_tecnici)) print '<td><strong>'.$result_m->label.'</strong></td>';
    }
    print '</tr>';
    while ($obj_prod = $db->fetch_object($result))
    {
        $sql_mag = "SELECT * FROM llx_entrepot WHERE rowid != '8' AND rowid != '9' AND rowid != '22' AND rowid != '17' AND rowid != '33' AND statut=1 ORDER BY rowid ASC";
        $result_mag = $db->query($sql_mag);
        if(strstr($obj_prod->ref,"COD")) $color = "style='color:blue;'";
        print '<tr><td '.$color.'>' . $obj_prod->ref;
        if(strstr($obj_prod->ref,"COD")) print " - ".$obj_prod->description;
        print '</td>';
        $sql1 = "SELECT COUNT(id) as tot FROM llx_asset WHERE id_magazzino != '8' AND id_magazzino != '9' AND id_magazzino != '22' AND id_magazzino != '33' AND id_magazzino != '17' AND cod_famiglia='" . $obj_prod->ref . "'";
        //echo $sql1;
        $result1 = $db->query($sql1);
        $obj_good = $db->fetch_object($result1);

        print '<td>'.$obj_good->tot.'</td>';
        $i=0;
        while($result_m= $db->fetch_object($result_mag))
        {
            $i++;
            if($i==1)
            {
                $sql1 = "SELECT COUNT(id) as tot FROM llx_asset WHERE id_magazzino = '7' AND cod_famiglia='" . $obj_prod->ref . "'";
                $result1 = $db->query($sql1);
                //echo $sql1."<br>";
                $obj_good = $db->fetch_object($result1);
                if($obj_good->tot==0) $color = " color: white; background-color: red; ";
                else if ($obj_good->tot<=5) $color = " color: black; background-color: yellow; ";
                else $color = " ";
                print '<td style="' . $color . '">' . $obj_good->tot . '</td>';
            }
            else if($i==2)
            {
                $sql1 = "SELECT COUNT(id) as tot FROM llx_asset WHERE id_magazzino = '6' AND cod_famiglia='" . $obj_prod->ref . "'";
                //echo $sql1."<br>";
                $result1 = $db->query($sql1);
                $obj_good = $db->fetch_object($result1);
                if($obj_good->tot==0) $color = " color: white; background-color: red; ";
                else if ($obj_good->tot<=5) $color = " color: black; background-color: yellow; ";
                else $color = " ";
                print '<td style="' . $color . '">' . $obj_good->tot . '</td>';
            } else {
                if(in_array($result_m->label,$array_tecnici)){
                $sql1 = "SELECT COUNT(id) as tot FROM llx_asset WHERE id_magazzino = '".$result_m->rowid."' AND cod_famiglia='" . $obj_prod->ref . "'";
                $result1 = $db->query($sql1);
                $obj_good = $db->fetch_object($result1);
                print '<td>' . $obj_good->tot . '</td>';
              }
            }

        }
        print '</tr>';

    }
    print '</table>';
} else if ($_GET['type'] == "ricerca")
{

    //valori da input
    $codice_famiglia = !empty($_GET['cod_famiglia']) ? $_GET['cod_famiglia'] : null;
    $matricola = !empty($_GET['matricola']) ? $_GET['matricola'] : null;
    $id_magazzino = !empty($_GET['id_magazzino']) ? $_GET['id_magazzino'] : null;


    $sql = "SELECT DISTINCT * FROM llx_product WHERE ref NOT LIKE '%COD%' AND hidden = 0 ORDER BY ref ASC";
     $result = $db->query($sql);
    $sql_2 = "SELECT DISTINCT * FROM llx_product WHERE ref LIKE '%COD%' AND hidden = 0 ORDER BY ref ASC";
    $result_2 = $db->query($sql_2);
    print '<form method="get" action="' . $_SERVER["PHP_SELF"] . '">';
    $select_prod = "<select name='cod_famiglia'>";
    $select_prod.="<option value=''></option>";
    $selected = " ";
    while ($obj_prod = $db->fetch_object($result))
    {
        if ($codice_famiglia == $obj_prod->ref)
            $selected = " selected";
        else
            $selected = " ";
        $select_prod.= '<option value=' . $obj_prod->ref . $selected . '>' . $obj_prod->ref . '</option>';
    }
    while ($obj_prod = $db->fetch_object($result_2))
    {
        if ($codice_famiglia == $obj_prod->ref)
            $selected = " selected";
        else
            $selected = " ";
        $select_prod.= '<option value=' . $obj_prod->ref . $selected . '>' . $obj_prod->ref . '</option>';
    }
    $select_prod .= "</select>";

    $sql = "SELECT DISTINCT rowid,label FROM llx_entrepot WHERE entity = '1' ORDER BY label ASC";
    $result = $db->query($sql);
    $select_mag = "<select name='id_magazzino'>";
    $select_mag.="<option value=''></option>";
    $selected = " ";
    while ($obj_prod = $db->fetch_object($result))
    {
        if ($id_magazzino == $obj_prod->rowid)
            $selected = " selected";
        else
            $selected = " ";

        $select_mag.= '<option value=' . $obj_prod->rowid . $selected . '>' . $obj_prod->label . '</option>';
    }
    $select_mag .= "</select>";


    print '<div class="tabBar">';
    print '<table class="border" width="100%">';
    print '<tr><td><strong>Cod Famiglia</strong></td><td>' . $select_prod . '</td></tr>';
    print '<tr><td><strong>Matricola</strong></td><td><input type="text" name="matricola" value="' . $matricola . '"></td></tr>';
    print '<tr><td><strong>Bolla</strong></td><td><input type="text" name="bolla" value="' . $bolla . '"></td></tr>';
    print '<tr><td><strong>Ticket</strong></td><td><input type="text" name="ticket" value="' . $ticket . '"></td></tr>';
    print '<tr><td><strong>Magazzino</strong></td><td>' . $select_mag . '</td></tr>';
    print '</table>';
    print ' <input type="hidden" name="type" value="ricerca"> ';
    print ' <input type="hidden" name="mainmenu" value="products"> ';
    print '</div>';
    print '<center><input type="submit" class="button" name="action"  value="' . "Cerca" . '"> </center>';
    print '</form>';
    print '<br>';

    if ($action == "Cerca")
    {


        $array_condizione = array();
        if (!empty($codice_famiglia))
        {
            $array_condizione [] = "a.cod_famiglia = '" . $codice_famiglia . "'";
        }
        if (!empty($matricola))
        {
            $array_condizione [] = "a.matricola LIKE '%" . trim($matricola) . "%'";
        }
        if (!empty($bolla))
        {
            $array_condizione [] = "a.rif_bolla = '" . $bolla . "'";
        }
        if (!empty($pt_number))
        {
            $array_condizione [] = "a.pt_number LIKE '%" . $pt_number . "%'";
        }
        if (!empty($serial_number))
        {
            $array_condizione [] = "a.serial_number LIKE '%" . $serial_number . "%'";
        }
        if (!empty($imiei_number))
        {
            $array_condizione [] = "a.imei_number LIKE '%" . $imiei_number . "%'";
        }

        if (!empty($id_regione))
        {
            $array_condizione [] = "a.id_regione LIKE '" . $id_regione . "'";
        }

        if (!empty($id_magazzino))
        {
            $array_condizione [] = "a.id_magazzino = '" . $id_magazzino . "'";
        }

        if (!empty($ticket))
        {
            $array_condizione [] = "a.numero_ticket = '" . $ticket . "'";
        }


        $merge_condizioni = implode(" AND ", $array_condizione);
        $where = empty($merge_condizioni) ? "" : " WHERE " . $merge_condizioni;
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "asset as a";
        $sql .= $where;
        $sql_disp = $sql. " AND id_magazzino!='8' AND id_magazzino !='9' " ;
        $sql .= " ORDER BY a.id_magazzino DESC";

        //echo $sql;

        if (!empty($where)) // esegui la ricerca solo se è stato inserito un campo di ricerca
        {
            $res = $db->query($sql);
            $xls_testo = "";
            $n_asset = 0;
            if ($res)
            {

                $n_asset = $db->num_rows($res);
            }
            $res_disp = $db->query($sql_disp);
            if ($res_disp)
            {

                $n_asset_disp = $db->num_rows($res_disp);
            }
            if ($n_asset > 0)
            {

                $obj_magazzino = new magazzino($db);
                    $nome_magazzino = $obj_magazzino->getMagazzino($_GET['id_magazzino']);
                    $nome_magazzino = $nome_magazzino[0]['label'];
                    //echo "SQL:".$sql;
                       $data_mag = date("d-m-Y_h:i:s");
                $export = DOL_URL_ROOT . "/theme/eldy/img/export.png";
                $img = ' <img style="width:50px; height:40px;" src="' . $export . '">';
                $down = DOL_URL_ROOT . '/product/reportMagazzini/' . $data_mag . ".xls";
                $link = '<a href="' . $down . '">' . "Esporta  riepilogo del magazzino" . '</td>';
                print "<center>" . $link . "<br>" . $img . "</a></center>";
                print '<br>';

                print 'QUANTITA TOTALE: '. $n_asset;
                print '<br>QUANTITA DISPONIBILE: '. $n_asset_disp;
                $xls_testo .= '<table border="1"  class="liste" width="100%">';
                $xls_testo .='<tr class="liste_titre">';
                $xls_testo .= '<td><strong>Codice Famiglia</strong></td>';
                $xls_testo .= '<td><strong>Matricola</strong></td>';
                $xls_testo .= '<td><strong>Magazzino</strong></td>';
                $xls_testo .= '<td><strong>Rif. Bolla</strong></td>';
                $xls_testo .= '<td><strong>Data Spedizione</strong></td>';
                $xls_testo .= '<td><strong>Data Ricezione</strong></td>';
                $xls_testo .= '<td><strong>Numero Ticket</strong></td>';
                $xls_testo .= '<td><strong>Tipologia</strong></td>';
                $xls_testo .= '<td><strong>TML</strong></td>';
                $xls_testo .= '<td><strong>Data Chiusura</strong></td>';
                $xls_testo .= '</tr>';
                $var = true;
                $db2 = $db;
                while ($obj_asset = $db->fetch_object($res))
                {

                    $var = !$var;
                    $xls_testo .= '<tr ' . $bc[$var] . '>';

                    $view_anteprima = $obj_asset->descrizione . "\nNota: " . $obj_asset->note;
                    $tag_title = 'title=' . '"' . $view_anteprima . '"';
                    //$link_asset = '<a href="' . DOL_URL_ROOT . '/product/scheda_asset.php?mainmenu=products&cod_asset=' . $obj_asset->cod_asset . '" ' . $tag_title . '>' . $obj_asset->cod_asset . '</a></td>';
                    $xls_testo .= '<td>' . $obj_asset->cod_famiglia . '</td>';
                    $xls_testo .= "<td>'" . $obj_asset->matricola . "</td>";
                    if($obj_asset->data_chiusura!="") $data_chiusura = date('d-m-Y',$obj_asset->data_chiusura);
                    else $data_chiusura = '';

                    $sql_tck = "SELECT ticket_id FROM ost_ticket__cdata WHERE ref_num ='".$obj_asset->numero_ticket."'";
                    $result_tck = $connessione->query($sql_tck);
                    $obj_tck=mysqli_fetch_object($result_tck);


                    $obj_magazzino = new magazzino($db);
                    $nome_magazzino = $obj_magazzino->getMagazzino($obj_asset->id_magazzino);
                    $nome_magazzino = $nome_magazzino[0]['label'];
                    $xls_testo .= '<td>' . $nome_magazzino . '</td>';
                    $xls_testo .= '<td>' . $obj_asset->rif_bolla . '</td>';
                    $xls_testo .= '<td>' . date("d-m-Y", strtotime($obj_asset->data_spedizione)) . '</td>';
                    $xls_testo .= '<td>' . date("d-m-Y", strtotime($obj_asset->data_ricezione)) . '</td>';
                    $xls_testo .= '<td><a target="_blank" style="text-decoration: none;" href="http://ticketglv.fast-data.it/scp/tickets.php?id='.$obj_tck->ticket_id.'">' . $obj_asset->numero_ticket . '</a></td>';
                    $xls_testo .= '<td>' . $obj_asset->tipologia_intervento . '</td>';
                    $xls_testo .= '<td>' . $obj_asset->termid . '</td>';
                    $xls_testo .= '<td>' . $data_chiusura . '</td>';


                    $xls_testo .= '</tr>';
                }
                $xls_testo .='</table>';
                //stampa

                export_xls($xls_testo,$data_mag);
                $xls_testo = str_replace("<table border=", "<table ", $xls_testo);
                print $xls_testo;
            }
        } else {
            $sql = "SELECT * FROM llx_asset WHERE id_magazzino !='8' AND id_magazzino != '9' AND id_magazzino > 5 ORDER BY id_magazzino ASC";
            $res = $db->query($sql);
            $xls_testo = "";
            $n_asset = 0;
            if ($res)
            {

                $n_asset = $db->num_rows($res);
            }
            if ($n_asset > 0)
            {

                $obj_magazzino = new magazzino($db);
                    $nome_magazzino = $obj_magazzino->getMagazzino($_GET['id_magazzino']);
                    $nome_magazzino = $nome_magazzino[0]['label'];
                    //echo "SQL:".$sql;
                       $data_mag = date("d-m-Y_h:i:s");
                $export = DOL_URL_ROOT . "/theme/eldy/img/export.png";
                $img = ' <img style="width:50px; height:40px;" src="' . $export . '">';
                $down = DOL_URL_ROOT . '/product/reportMagazzini/' . $data_mag . ".xls";
                $link = '<a href="' . $down . '">' . "Esporta  riepilogo del magazzino" . '</td>';
                print "<center>" . $link . "<br>" . $img . "</a></center>";
                print '<br>';

                print 'QUANTITA TOTALE: '. $n_asset;
                $xls_testo .= '<table width="100%" border="1"  class="liste">';
                $xls_testo .='<tr class="liste_titre">';
                $xls_testo .= '<td><strong>Codice Famiglia</strong></td>';
                $xls_testo .= '<td><strong>Matricola</strong></td>';
                $xls_testo .= '<td><strong>Magazzino</strong></td>';
                $xls_testo .= '<td><strong>Rif. Bolla</strong></td>';
                $xls_testo .= '<td><strong>Data Spedizione</strong></td>';
                $xls_testo .= '<td><strong>Data Ricezione</strong></td>';
                $xls_testo .= '<td><strong>Numero Ticket</strong></td>';
                $xls_testo .= '<td><strong>Tipologia</strong></td>';
                $xls_testo .= '<td><strong>TML</strong></td>';
                $xls_testo .= '<td><strong>Data Chiusura</strong></td>';
                $xls_testo .= '</tr>';
                $var = true;
                $db2 = $db;
                while ($obj_asset = $db->fetch_object($res))
                {

                    $var = !$var;
                    $xls_testo .= '<tr ' . $bc[$var] . '>';

                    $view_anteprima = $obj_asset->descrizione . "\nNota: " . $obj_asset->note;
                    $tag_title = 'title=' . '"' . $view_anteprima . '"';
                    //$link_asset = '<a href="' . DOL_URL_ROOT . '/product/scheda_asset.php?mainmenu=products&cod_asset=' . $obj_asset->cod_asset . '" ' . $tag_title . '>' . $obj_asset->cod_asset . '</a></td>';
                    $xls_testo .= '<td>' . $obj_asset->cod_famiglia . '</td>';
                    $xls_testo .= "<td>'" . $obj_asset->matricola . "</td>";

                    $obj_magazzino = new magazzino($db);
                    $nome_magazzino = $obj_magazzino->getMagazzino($obj_asset->id_magazzino);
                    $nome_magazzino = $nome_magazzino[0]['label'];
                    $xls_testo .= '<td>' . $nome_magazzino . '</td>';
                    $xls_testo .= '<td>' . $obj_asset->rif_bolla . '</td>';
                    $xls_testo .= '<td>' . $obj_asset->data_spedizione . '</td>';
                    $xls_testo .= '<td>' . $obj_asset->data_ricezione . '</td>';
                    $xls_testo .= '<td>' . $obj_asset->numero_ticket . '</td>';
                    $xls_testo .= '<td>' . $obj_asset->tipologia . '</td>';
                    $xls_testo .= '<td>' . $obj_asset->termid . '</td>';
                    $xls_testo .= '<td>' . $obj_asset->data_chiusura . '</td>';
                    $xls_testo .= '</tr>';
                }
                $xls_testo .='</table>';
                //stampa

                export_xls($xls_testo,$data_mag);
                $xls_testo = str_replace("<table border=", "<table ", $xls_testo);
                print $xls_testo;
            }
        }
    }
} else if ($_GET['type'] == "ricerca_matricole")
{


    ?>
    <br>
    <h1 style="text-align:left;">CONTROLLO MATRICOLE UTILIZZATE NEI TICKET</h1>
    <form action="" method="post">
        <br>
        <textarea name="pt_control" value="" cols="100" rows="20">
  </textarea>
        <br><br>
        <input type="submit" name="control" value="Cerca Matricole nei Ticket">
    </form>

    <?php
    if(isset($_POST['control'])){

        $pt=explode("\n",$_POST['pt_control']);
        //echo "La riga di controllo è la seguente:<br><br>".nl2br($_POST['pt_control']);
        //echo 'SPLIT:<br><br>';
        $string="";
        $i=0;
        echo '<b>RISULTATO DELLA RICERCA:</b><br><br>';
        echo '<table border="1"><tr><td style="padding:5px;"><b>Matricola</b></td><td style="padding:5px;"><b>Ticket</b></td><td><b>Magazzino</b></td></tr>';
        $pt = array_unique($pt);
        foreach ($pt as $pt_number) {
            //echo $pt_number.'<br>';
            if($pt_number=="") continue;
            $i++;
            $string.="'".trim($pt_number)."'";

            $sql="SELECT * FROM ost_ticket__cdata as tc, ost_ticket as t WHERE tc.ticket_id = t.ticket_id AND (tc.affected_resource_zz_wam_string2 LIKE '%".trim($pt_number)."%' OR tc.zz_desc_op_eff LIKE '%".trim($pt_number)."%') AND t.status_id IN(2,3)";
            //echo $sql;
            $result = $connessione->query($sql);
            $sql_dol="SELECT e.label as mag FROM llx_entrepot as e, llx_asset as a WHERE e.rowid = a.id_magazzino AND a.matricola LIKE '%".trim($pt_number)."%'";
            $result_dol = $connessione_dol->query($sql_dol);
            $obj_dol=mysqli_fetch_object($result_dol);
            $mag = $obj_dol->mag;

            //if($pt_number=="128497C") echo $sql;
            //echo "NUMERO RISULTATI: ".mysqli_num_rows($result)."<br>";
            $obj_id=mysqli_fetch_object($result);

            if(mysqli_num_rows($result)>0) { echo '<tr><td style="padding:5px;">'.$pt_number.'</td><td style="padding:5px;"><a target="_blank" style="text-decoration: none;" href="http://ticketglv.fast-data.it/scp/tickets.php?id='.$obj_id->ticket_id.'">'.$obj_id->ref_num.'</a></td><td>'.$mag.'</td><tr></tr>'; } //echo $pt_number.": PRESENTE<br>";
            else echo '<tr><td style="padding:5px;">'.$pt_number.'</td><td style="padding:5px;">Nessun Ticket Trovato</td><td>'.$mag.'</td></tr>';

        }
        echo '</table>';
    }
} else if ($_GET['type'] == "ricerca_matricole_mag")
{

    $sql = "SELECT DISTINCT rowid,label FROM llx_entrepot WHERE entity = '1' ORDER BY label ASC";
    $result = $db->query($sql);
    $select_mag = "<select name='id_mag'>";
    $select_mag.="<option value=''></option>";
    $selected = " ";
    while ($obj_prod = $db->fetch_object($result))
    {
        if ($id_magazzino == $obj_prod->rowid)
            $selected = " selected";
        else
            $selected = " ";

        $select_mag.= '<option value=' . $obj_prod->rowid . $selected . '>' . $obj_prod->label . '</option>';
    }
    $select_mag .= "</select>";
    ?>
    <br>
    <h1 style="text-align:left;">CONTROLLO MATRICOLE PRESENTI NEI MAGAZZINI</h1>
    <form action="" method="post">
        <?php echo $select_mag; ?>
        <br>
        <textarea name="pt_control" value="" cols="100" rows="20">
  </textarea>
        <br><br>
        <input type="submit" name="control" value="Cerca Matricole nei Magazzini">
    </form>

    <?php
    if(isset($_POST['control'])){

        $pt=explode("\n",$_POST['pt_control']);
        //echo "La riga di controllo è la seguente:<br><br>".nl2br($_POST['pt_control']);
        //echo 'SPLIT:<br><br>';
        $string="";
        $i=0;
        echo '<b>RISULTATO DELLA RICERCA:</b><br><br>';
        echo '<table border="1"><tr><td style="padding:5px;"><b>Matricola</b></td><td style="padding:5px;"><b>Stato</b></td></tr>';
        $pt = array_unique($pt);
        //var_dump($pt);
        $lista="";
        $i=0;
        foreach ($pt as $pt_number) {
            $i++;
            $lista .= "'".trim($pt_number)."'";
            if($i!=count($pt)) $lista.=",";
        }
        $sql="SELECT * FROM llx_asset WHERE matricola NOT IN (".$lista.") AND id_magazzino = '".$_POST['id_mag']."' AND id_magazzino AND matricola!= ''";
        $result = $db->query($sql);
        //echo $sql;
        while($obj_id=$db->fetch_object($result))
        {
            echo '<tr><td style="padding:5px;">'.$obj_id->matricola.'</td><td style="padding:5px;">NON PRESENTE NELLA LISTA</td></tr>';
        }
        /*foreach ($pt as $pt_number) {
            //echo $pt_number.'<br>';
            if($pt_number=="") continue;
            $i++;
            $string.="'".trim($pt_number)."'";

            $sql="SELECT * FROM llx_asset WHERE matricola LIKE '%".trim($pt_number)."%' AND id_magazzino = '".$_POST['id_mag']."'";
            //echo $sql;
            $result = $connessione->query($sql);
            //if($pt_number=="128497C") echo $sql;
            //echo "NUMERO RISULTATI: ".mysqli_num_rows($result)."<br>";
            $obj_id=mysqli_fetch_object($result);
            if(mysqli_num_rows($result)>0) { echo '<tr><td style="padding:5px;">'.$pt_number.'</td><td style="padding:5px;">PRESENTE</td><tr></tr>'; } //echo $pt_number.": PRESENTE<br>";
            else echo '<tr><td style="padding:5px;">'.$pt_number.'</td><td style="padding:5px;">NON PRESENTE</td></tr>';

        }*/
        echo '</table>';
    }
} else if ($_GET['type'] == "ricerca_ticket")
{

print '<div class="tabBar">';
print '<form action="" method="post">';
print '<table class="border" width="100%">';
print '<tr><td><strong>Ordine</strong></td><td><input type="text" name="ordine" value="' . $_POST['ordine'] . '"></td></tr>';
print '<tr><td><strong>Matricola</strong></td><td><input type="text" name="matricola" value="' . $_POST['matricola'] . '"></td></tr>';
print '</table>';
print ' <input type="hidden" name="type" value="ricerca"> ';
print ' <input type="hidden" name="mainmenu" value="products"> ';
print '</div>';
print '<center><input type="submit" class="button" name="action"  value="' . "Cerca" . '"> </center>';
print '</form>';
print '<br>';

    if(isset($_POST['action'])){


        echo '<b>RISULTATO DELLA RICERCA:</b><br><br>';
        echo '<table border="1"><tr><td style="padding:5px;"><b>Numero Ordine</b></td></tr>';
        $sql="SELECT * FROM ost_ticket__cdata WHERE ";
        if(isset($_POST['ordine']) && $_POST['ordine']!="") $sql.=" ref_num LIKE '%".$_POST['ordine']."%' ";
        if(isset($_POST['ordine'])&&$_POST['ordine']!=""&&isset($_POST['matricola'])&&$_POST['matricola']!="") $sql.=" AND (tc.affected_resource_zz_wam_string2 LIKE '%".trim($_POST['matricola'])."%' OR tc.zz_desc_op_eff LIKE '%".trim($_POST['matricola'])."%')";
        else if(isset($_POST['matricola'])&&$_POST['matricola']!="") $sql.=" (tc.affected_resource_zz_wam_string2 LIKE '%".trim($_POST['matricola'])."%' OR tc.zz_desc_op_eff LIKE '%".trim($_POST['matricola'])."%' ";
        $result = $connessione->query($sql);
        //echo $sql;
        while($obj_id=$db->fetch_object($result))
        {
            echo '<tr><td style="padding:5px;"><a target="_blank" href="http://ticketglv.fast-data.it/scp/tickets.php?id='.$obj_id->ticket_id.'">'.$obj_id->ref_num.'</a></td></tr>';
        }

        echo '</table>';
        echo $sql;
    }

}

function export_xls($xls_testo,$magazzino)
{

    $filename = "reportMagazzini/" . $magazzino . ".xls";
    //mail("marco.salmi89@gmail.com","Prova",$magazzino);
    $fp = fopen($filename, 'w');
    fwrite($fp, $xls_testo);
    fclose($fp);
}

function getNomeRegione($id_regione)
{
    global $db;
    $sql = "SELECT nom FROM llx_c_regions ";
    $sql .= "WHERE rowid = " . $id_regione;
    $res = $db->query($sql);
    $nome_regione = "";
    if ($res)
    {
        $obj_regione = $db->fetch_object($res);
        $nome_regione = $obj_regione->nom;
    }
    return $nome_regione;
}

function getPtNumber($cod_asset)
{
    global $db;
    $sql = "SELECT pt_number FROM llx_asset ";
    $sql .= "WHERE cod_asset = '" . $cod_asset."'";
    $res = $db->query($sql);
    $nome_regione = "";
    if ($res)
    {
        $obj_regione = $db->fetch_object($res);
        $nome_regione = $obj_regione->pt_number;
    }
    return $nome_regione;
}

?>
