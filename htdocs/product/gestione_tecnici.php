<?php
/*
header("Access-Control-Allow-Origin: *");

echo ini_get('display_errors');

if (!ini_get('display_errors')) {
    ini_set('display_errors', '1');
}

echo ini_get('display_errors');
*/
//require '../main.inc.php';


//echo $_SESSION['firstname']." ".$_SESSION['lastname']." " .$_SESSION['tipologia'];

$host = "localhost";
// username dell'utente in connessione
$user = "admin";
// password dell'utente
$password = "Iniziale1!?";
// nome del database
$db = "fd_ticket";
$db1 = "dolibarr";

$connessione = new mysqli($host, $user, $password, $db);
$connessione2 = new mysqli($host, $user, $password, $db1);


require '../main.inc.php';

function export_xls($xls_testo,$magazzino)
{

    $filename = "reportTecnici/" . $magazzino . ".xls";
    //mail("marco.salmi89@gmail.com","Prova",$magazzino);
    $fp = fopen($filename, 'w');
    fwrite($fp, $xls_testo);
    fclose($fp);
}

if(isset($_POST['attiva']))
{
  $sql = "UPDATE ost_staff SET isactive=1 WHERE staff_id =".$_POST['id_staff'];
  $result = $connessione->query($sql);
  //echo $sql;
} else if(isset($_POST['disattiva']))
{
  $sql = "UPDATE ost_staff SET isactive=0 WHERE staff_id =".$_POST['id_staff'];
  $result = $connessione->query($sql);
  //echo $sql;
}

if(isset($_POST['online']))
{
  $sql = "UPDATE ost_config SET value=1 WHERE `key`='isonline'";
  $result = $connessione->query($sql);
  //echo $sql;
} else if(isset($_POST['offline']))
{
  $sql = "UPDATE ost_config SET value=0 WHERE `key`='isonline'";
  $result = $connessione->query($sql);
  //echo $sql;
}

if(isset($_POST['attiva']))
{
  $sql = "UPDATE ost_config SET value=1 WHERE `key`='isopen'";
  $result = $connessione->query($sql);
  echo $sql;
} else if(isset($_POST['disattiva']))
{
  $sql = "UPDATE ost_config SET value=0 WHERE `key`='isopen'";
  $result = $connessione->query($sql);
  echo $sql;
}


$sql_online = "SELECT value as isonline FROM ost_config WHERE `key` ='isonline'";
    $result_online = $connessione->query($sql_online);
    $table2 = "<table>";
    while ($obj_prod = mysqli_fetch_object($result_online))
    {
        if($obj_prod->isonline==0){
          $style='style="color:red;"';
          $val = "OFFLINE";
        }
        else {
          $style="";
          $val="ONLINE";
        }
        $table2 .= "<tr><td ".$style.">STATO: ".$val."</td><td><form action='' method='post'>";
        if($obj_prod->isonline==0) $table2.="<input type='submit' value='Metti Online' name='online' id='online'>";
        else $table2.="<input type='submit' value='Metti Offline' name='offline' id='offline'>";
        $table2 .= "";
        $table2.= "</form></td></tr>";
    }
    $table2.= "</table>";

    $sql_isopen = "SELECT value as isopen FROM ost_config WHERE `key` ='isopen'";
        $result_isopen = $connessione->query($sql_isopen);
        $table3 = "<table>";
        while ($obj_prod = mysqli_fetch_object($result_isopen))
        {
            if($obj_prod->isopen==0){
              $style='style="color:red;"';
              $val = "DISATTIVA";
            }
            else {
              $style="";
              $val="ATTIVA";
            }
            $table3 .= "<tr><td ".$style.">STATO: ".$val."</td><td><form action='' method='post'>";
            if($obj_prod->isopen==0) $table3.="<input type='submit' value='Attiva' name='attiva' id='attiva'>";
            else $table3.="<input type='submit' value='Disattiva' name='disattiva' id='disattiva'>";
            $table3 .= "";
            $table3.= "</form></td></tr>";
        }
        $table3.= "</table>";


$sql = "SELECT staff_id, firstname, lastname, isactive FROM ost_staff WHERE staff_id NOT IN(1,3,5,14,63,77,78,79,80) ORDER BY lastname ASC";
    $result = $connessione->query($sql);
    $table = "<table>";
    while ($obj_prod = mysqli_fetch_object($result))
    {
        if($obj_prod->isactive==0) $style='style="color:red;"';
        else $style="";
        $table .= "<tr><td ".$style.">".$obj_prod->lastname." ".$obj_prod->firstname."</td><td><form action='' method='post'><input type='hidden' name='id_staff' id='id_staff' value='".$obj_prod->staff_id."'>";
        if($obj_prod->isactive==0) $table.="<input type='submit' value='Attiva' name='attiva' id='attiva'>";
        else $table.="<input type='submit' value='Disattiva' name='disattiva' id='disattiva'>";
        $table .= "";
        $table.= "</form></td></tr>";
    }
    $table.= "</table>";


llxHeader('', $title, $helpurl, '');

print '<div class="fiche">';
print '<div class="tabs" data-type="horizontal" data-role="controlgroup">';
print '<a class="tabTitle">
<img border="0" title="" alt="" src="' . $root . '"/theme/eldy/img/object_product.png">
Gestione Tecnici
</a>';

print '<div class="inline-block tabsElem">
<a id="card"';

if($_SESSION['tipologia']!="T") {
    if ($_GET['type'] == "gestione")
        print 'class="tabactive tab inline-block" ';
    else
        print 'class="tab inline-block" ';
    print ' href="' . $root . '/product/gestione_tecnici.php?mainmenu=products&type=gestione" data-role="button">Gestione</a>
</div>';

}
print '<div class="inline-block tabsElem">
<a id="card"';

if ((!isset($_GET['type'])&&$_SESSION['tipologia']!="T") || $_GET['type'] == "assegnazioni")
    print 'class="tabactive tab inline-block" ';
else
    print 'class="tab inline-block" ';
print ' href="' . $root . '/product/gestione_tecnici.php?mainmenu=products&type=assegnazioni" data-role="button">Assegnazioni</a>
</div>';


print '<div class="inline-block tabsElem">
<a id="card"';

if (!isset($_GET['type']) || $_GET['type'] == "chiusi")
    print 'class="tabactive tab inline-block" ';
else
    print 'class="tab inline-block" ';
print ' href="' . $root . '/product/gestione_tecnici.php?mainmenu=products&type=chiusi" data-role="button">Ticket Chiusi</a>
</div>';




print '</div>';
if ($_GET['type'] == "gestione")
{
?>
<br><br>
<h1 style="text-align:left;">STATO SISTEMA</h1>
<?php echo $table2; ?>
<br><br>
<h1 style="text-align:left;">FATTURAZIONE TECNICI</h1>
<?php echo $table3; ?>
<br><br>

<h1 style="text-align:left;">GESTIONE TECNICI</h1>
<?php echo $table; ?>

<?php


} else if (!isset($_GET['type']) || $_GET['type'] == "assegnazioni")
{

?>

<h1>ASSEGNAZIONI TECNICI</h1>

<?php
//$connessione->select_db('dolibarr');
//$codice_famiglia = !empty($_GET['cod_famiglia']) ? $_GET['cod_famiglia'] : null;


$data_da = strtotime("first day of last month midnight");
$data_da_ora = strtotime("first day of last month midnight");
$data_a = strtotime("last day of last month midnight");
$data_a_ora = strtotime("last day of last month midnight");

$_POST['data_da'] = $data_da;
$_POST['data_a'] = $data_a;

$_POST['status_id'] = 23;

if($_SESSION['tipologia']=="T")

{
  $sql_log = "SELECT DISTINCT staff_id,lastname,firstname FROM ost_staff WHERE isactive = 1 AND lastname LIKE '%".$_SESSION['dol_login']."%'  ORDER BY lastname ASC";
  if($_SESSION['dol_login']=="delorenzo") $sql_log = "SELECT DISTINCT staff_id,lastname,firstname FROM ost_staff WHERE isactive = 1 AND lastname LIKE '%de lorenzo%'  ORDER BY lastname ASC";
  $result_log = $connessione->query($sql_log);
  $obj_log = mysqli_fetch_object($result_log);
  $id_user = $obj_log->staff_id;
  $lastname = $obj_log->lastname;
  $firstname = $obj_log->firstname;

} else {
  $sql_log = "SELECT DISTINCT staff_id,lastname,firstname FROM ost_staff WHERE isactive = 1 AND staff_id = '".$_GET['id_tec']."'  ORDER BY lastname ASC";
  $result_log = $connessione->query($sql_log);
  $obj_log = mysqli_fetch_object($result_log);
  $id_user = $obj_log->staff_id;
  $lastname = $obj_log->lastname;
  $firstname = $obj_log->firstname;
}



if($_POST['status_id']==2) $sql = "SELECT t.ticket_id, tc.zz_dt_clmghw as closed, tc.ref_num, t.created, h.topic, t.duedate, tc.cr,tc.customer_location_l_addr2,tc.customer_location_l_addr3, tc.customer_middle_name, tc.customer_location_l_addr1,tc.customer_location_l_addr7,th.created as assegnato FROM ost_ticket as t
LEFT JOIN ost_ticket__cdata as tc ON t.ticket_id = tc.ticket_id
LEFT JOIN ost_help_topic as h ON h.topic_id = t.topic_id
LEFT JOIN ost_province as p ON tc.customer_location_l_addr1 = p.siglaprovincia";
else $sql = "SELECT t.ticket_id, MAX(th.created) as assegnato, tc.ref_num, t.created, h.topic, t.duedate, tc.cr,tc.customer_location_l_addr2,tc.customer_location_l_addr3, tc.customer_middle_name, tc.customer_location_l_addr1,tc.customer_location_l_addr7, tc.customer_phone_number FROM ost_ticket as t, ost_ticket__cdata as tc, ost_help_topic as h, ost_ticket_thread as th WHERE h.topic_id = t.topic_id AND t.ticket_id = tc.ticket_id ";





if($_SESSION['tipologia']!="T")
{
  print '<div class="fiche">';
  print '<div class="tabs" data-type="horizontal" data-role="controlgroup">';



  $sql_tec = "SELECT DISTINCT staff_id,lastname,firstname FROM ost_staff WHERE isactive = 1 AND staff_id NOT IN (1,3,5,14,63,77,78,79)  ORDER BY lastname ASC";

  $result_tec = $connessione->query($sql_tec);
  $i=0;
  while($obj_tec = mysqli_fetch_object($result_tec))
  {
    $i++;

    print '<div class="inline-block tabsElem">
    <a id="card"';
      if ($_GET['id_tec'] == $obj_tec->staff_id || (!isset($_GET['id_tec'])&&$i==1))
      {
          print 'class="tabactive tab inline-block" ';

        }
      else
          print 'class="tab inline-block" ';
      print ' href="' . $root . '/product/gestione_tecnici.php?mainmenu=products&type=assegnazioni&id_tec='.$obj_tec->staff_id.'&tecnico='.$obj_tec->lastname.' data-role="button">'.$obj_tec->lastname.'</a>
  </div>';
if(!isset($_GET['id_tec'])&&$i==1) $_GET['id_tec'] = $obj_tec->staff_id;
}


  print '</div>';
}



if($_SESSION['tipologia']!='T')$id_user = $_GET['id_tec'];

if($_POST['status_id']==23) $sql.= " AND t.ticket_id = th.ticket_id ";
$sql.= " AND t.staff_id ='".$id_user."'";
if($_POST['status_id']==23) $sql.= " AND t.status_id ='".$_POST['status_id']."' AND (th.title LIKE '%Ticket assegnato a $lastname%' OR th.title LIKE '%Ticket assegnato a $firstname $lastname%')";

if($_POST['status_id']==23) $sql.=" GROUP BY tc.cr,tc.ref_num ORDER BY assegnato DESC, tc.customer_location_l_addr2 ASC";
else $sql.=" ORDER BY closed ASC";


$result = $connessione->query($sql);
$num_tot = mysqli_num_rows($result);
while ($obj_prod = mysqli_fetch_object($result))
{
   if(substr($obj_prod->assegnato,0,10)!="") $first_line = date('d/m/Y',strtotime(substr($obj_prod->assegnato,0,10)));
   else $first_line = date('d/m/Y',$obj_prod->closed);
    //$select_max_id = "SELECT MAX(id) FROM ost_ticket_thread WHERE ticket_id='".$obj_prod->ticket_id."' AND ";
    $string .= "<tr><td>". $first_line ."</td>";
    if(isset($_SESSION['tipologia'])&&$_SESSION['tipologia']!="T") $string .="<td>".date('d/m/Y',strtotime(substr($obj_prod->duedate,0,10)))."</td>";
    $string .= "<td>";
    if(isset($_SESSION['tipologia'])&&$_SESSION['tipologia']!="T") $string.= "<a target='_blank' style='text-decoration: none;' href='http://ticketglv.fast-data.it/scp/tickets.php?id=".$obj_prod->ticket_id."'>";
      $string .= $obj_prod->ref_num;
      if(isset($_SESSION['tipologia'])&&$_SESSION['tipologia']!="T") $string.= "</a>";
      $string .= "</td><td>".$obj_prod->cr."</td><td>".$obj_prod->topic."</td><td>".$obj_prod->customer_middle_name."</td><td>".$obj_prod->customer_location_l_addr2.", ".$obj_prod->customer_location_l_addr3."</td><td>".$obj_prod->customer_location_l_addr7."</td><td>".$obj_prod->customer_location_l_addr1."</td><td><a href='tel:".$obj_prod->customer_phone_number."'>".$obj_prod->customer_phone_number."</a></td></tr>";
}
//echo $sql."<br>";
//echo $_POST['data_da']."-".$_POST['data_a'];

  $string_final =   '<table border="1"><tr><td>';
  if(isset($_POST['status_id'])&&$_POST['status_id']=="2") $string_final .=  '<b>Data Chiusura</b>';
  else $string_final .=  '<b>Data Assegnazione</b>';
  if(isset($_SESSION['tipologia'])&&$_SESSION['tipologia']!="T") $string_final .=  '</td><td><b>Scadenza</b></td>';
  $string_final .=  '</td><td><b>Ordine</b></td><td><b>TML</b></td><td><b>Tipologia</b></td><td><b>Insegna</b></td><td><b>Indirizzo</b></td><td><b>Luogo</b></td><td><b>Provincia</b></td><tr>';
  $string_final .=  $string;
  $string_final .=  '</table>';

  $export = DOL_URL_ROOT . "/theme/eldy/img/export.png";
  $img = ' <img style="width:50px; height:40px;" src="' . $export . '">';
  $down = DOL_URL_ROOT . '/product/reportTecnici/' . $_SESSION['dol_login'] . ".xls";
  $link = '<a href="' . $down . '">' . "Esporta  riepilogo" . '</td>';
  //print "<center>" . $link . "<br>" . $img . "</a></center>";
  print '<br>';
  print '<b>Totale</b>: '.$num_tot.'<br>';
  echo '<table border="1"><tr><td>';
  if(isset($_POST['status_id'])&&$_POST['status_id']=="2") echo '<b>Data Chiusura</b>';
  else echo '<b>Data Assegnazione</b>';
  echo '</td>';
  if(isset($_SESSION['tipologia'])&&$_SESSION['tipologia']!="T") echo '<td><b>Scadenza</b></td>';
  echo '<td><b>Ordine</b></td><td><b>TML</b></td><td><b>Tipologia</b></td><td><b>Insegna</b></td><td><b>Indirizzo</b></td><td><b>Luogo</b></td><td><b>Provincia</b></td><td><b>Telefono</b></td></tr>';
  echo $string;
  echo '</table>';


} else if (!isset($_GET['type']) || $_GET['type'] == "chiusi")
{

?>

<h1>CHIUSURE TECNICI</h1>

<?php
$today = date("d");

if($_SESSION['tipologia']=="T")

{
  $sql_log = "SELECT DISTINCT staff_id,lastname,firstname FROM ost_staff WHERE isactive = 1 AND lastname LIKE '%".$_SESSION['dol_login']."%'  ORDER BY lastname ASC";
  if($_SESSION['dol_login']=="delorenzo") $sql_log = "SELECT DISTINCT staff_id,lastname,firstname FROM ost_staff WHERE isactive = 1 AND lastname LIKE '%de lorenzo%'  ORDER BY lastname ASC";
  $result_log = $connessione->query($sql_log);
  $obj_log = mysqli_fetch_object($result_log);
  $id_user = $obj_log->staff_id;
  $lastname = $obj_log->lastname;
  $firstname = $obj_log->firstname;
} else {
  $sql_log = "SELECT DISTINCT staff_id,lastname,firstname FROM ost_staff WHERE isactive = 1 AND lastname LIKE '%".$_GET['tecnico']."%'  ORDER BY lastname ASC";
  $result_log = $connessione->query($sql_log);
  $obj_log = mysqli_fetch_object($result_log);
  $id_user = $obj_log->staff_id;
  $lastname = $obj_log->lastname;
  $firstname = $obj_log->firstname;
}

if($_SESSION['tipologia']!="T")
{
print '<div class="fiche">';
print '<div class="tabs" data-type="horizontal" data-role="controlgroup">';



$sql_tec = "SELECT DISTINCT staff_id,lastname,firstname FROM ost_staff WHERE isactive = 1 AND staff_id NOT IN (1,3,5,14,63,77,78,79)  ORDER BY lastname ASC";

$result_tec = $connessione->query($sql_tec);
$i=0;
while($obj_tec = mysqli_fetch_object($result_tec))
{
  $i++;

  print '<div class="inline-block tabsElem">
  <a id="card"';
    if ($_GET['id_tec'] == $obj_tec->staff_id || (!isset($_GET['id_tec'])&&$i==1))
    {
        print 'class="tabactive tab inline-block" ';

      }
    else
        print 'class="tab inline-block" ';
    print ' href="' . $root . '/product/gestione_tecnici.php?mainmenu=products&type=chiusi&id_tec='.$obj_tec->staff_id.'&tecnico='.$obj_tec->lastname.' data-role="button">'.$obj_tec->lastname.'</a>
</div>';
if(!isset($_GET['id_tec'])&&$i==1) $_GET['id_tec'] = $obj_tec->staff_id;
}


print '</div>';
}

$sql = "SELECT value FROM ost_config WHERE `key` = 'isopen'";
$result = $connessione->query($sql);
$obj_prod = mysqli_fetch_object($result);
$open = $obj_prod->value;



if($open==1)
{





$sql = "SELECT DISTINCT staff_id,lastname,firstname FROM ost_staff WHERE isactive = 1 AND staff_id NOT IN (1,3,5,14,63,77,78,79)  ORDER BY lastname ASC";

if($_SESSION['tipologia']=="T") $sql = "SELECT DISTINCT staff_id,lastname,firstname FROM ost_staff WHERE isactive = 1 AND lastname LIKE '%".$_SESSION['dol_login']."%'  ORDER BY lastname ASC";
if($_SESSION['dol_login']=="delorenzo") $sql = "SELECT DISTINCT staff_id,lastname,firstname FROM ost_staff WHERE isactive = 1 AND lastname LIKE '%de lorenzo%'  ORDER BY lastname ASC";

    $result = $connessione->query($sql);
print '<form method="post" action="">';
$select_prod = "<select name='staff_id'>";
if($_SESSION['tipologia']!="T") $select_prod.="<option value=''></option>";
$selected = " ";
while ($obj_prod = mysqli_fetch_object($result))
{
    if ($staff_id == $obj_prod->staff_id)
        $selected = " selected";
    else
        $selected = " ";
    $select_prod.= '<option value=' . $obj_prod->staff_id . $selected . '>' . $obj_prod->lastname. ' '.$obj_prod->firstname. '</option>';
}
$select_prod .= "</select>";

$sql = "SELECT * FROM ost_ticket_status WHERE id IN(2,23) ORDER BY id ASC";
$result = $connessione->query($sql);
if($_SESSION['tipologia']!="T")
{

$select_mag = "<select name='status_id'>";
$select_mag.="<option value=''></option>";
$selected = " ";
while ($obj_prod = mysqli_fetch_object($result))
{
    if ($status_id == $obj_prod->id)
        $selected = " selected";
    else
        $selected = " ";

    $select_mag.= '<option value=' . $obj_prod->id . $selected . '>' . $obj_prod->name . '</option>';
}
$select_mag .= "</select>";

} else if($_SESSION['tipologia']=="T")
{
  $select_mag.= '<select><option value="23">Assegnato</option></selected>';
}

$surname = "SELECT firstname,lastname FROM ost_staff WHERE staff_id=".$_POST['staff_id'];
$result_surname = $connessione->query($surname);
$obj_surname = mysqli_fetch_object($result_surname);


/*$data_da = $_POST['data_da'];
$data_da_ora = $_POST['data_da']." 00:00:00";
$data_a = $_POST['data_a'];
$data_a_ora = $_POST['data_a']." 00:00:00";
*/
$data_da = strtotime("first day of last month midnight");
$data_da_ora = strtotime("first day of last month midnight");
$data_a = strtotime("last day of last month midnight");
$data_a_ora = strtotime("last day of last month midnight");

$_POST['data_da'] = $data_da;
$_POST['data_a'] = $data_a;



if($_POST['status_id']==23) $sql = "SELECT t.ticket_id, tc.zz_dt_clmghw as closed, tc.ref_num, t.created, h.topic, t.duedate, tc.cr,tc.customer_location_l_addr2,tc.customer_location_l_addr3, tc.customer_middle_name, tc.customer_location_l_addr1, r.nomeregione,th.created as assegnato FROM ost_ticket as t, ost_ticket__cdata as tc, ost_ticket_thread as th, ost_help_topic as h,ost_province as p, ost_regioni as r WHERE  tc.customer_location_l_addr1 = p.siglaprovincia AND p.idregione = r.idregione AND h.topic_id = t.topic_id AND t.ticket_id = tc.ticket_id";
else $sql = "SELECT t.ticket_id, tc.zz_dt_clmghw as closed, tc.ref_num, t.created, h.topic, t.duedate, tc.cr,tc.customer_location_l_addr2,tc.customer_location_l_addr3, tc.customer_middle_name, tc.customer_location_l_addr1 FROM ost_ticket as t, ost_ticket__cdata as tc, ost_help_topic as h WHERE h.topic_id = t.topic_id AND t.ticket_id = tc.ticket_id";

$_POST['status_id'] = 2;

if($_SESSION['tipologia']=="T") $sql_log = "SELECT DISTINCT staff_id,lastname,firstname FROM ost_staff WHERE isactive = 1 AND lastname LIKE '%".$_SESSION['dol_login']."%'  ORDER BY lastname ASC";
if($_SESSION['dol_login']=="delorenzo") $sql_log = "SELECT DISTINCT staff_id,lastname,firstname FROM ost_staff WHERE isactive = 1 AND lastname LIKE '%de lorenzo%'  ORDER BY lastname ASC";

$result_log = $connessione->query($sql_log);
$obj_log = mysqli_fetch_object($result_log);
$id_user = $obj_log->staff_id;

if($_SESSION['tipologia']!='T')$id_user = $_GET['id_tec'];

if($_POST['status_id']==23) $sql.= " AND t.ticket_id = th.ticket_id ";
$sql.= " AND t.staff_id ='".$id_user."'";
if($_POST['status_id']==2||$_POST['status_id']== 3) {
    $sql .= " AND t.status_id ='" . $_POST['status_id'] . "'";
    if($_POST['data_da']!=""&&$_POST['data_a']!="") $sql.= " AND tc.zz_dt_clmghw BETWEEN '" . $data_da_ora . "' AND '" . $data_a_ora . "'";
    else if($_POST['data_da']!=""&&$_POST['data_a']=='')  $sql.= " AND tc.zz_dt_clmghw LIKE '%" . $data_da . "%'";
}
else if($_POST['status_id']==23&&$_POST['data_a']=="") $sql.= " AND t.status_id ='".$_POST['status_id']."' AND (th.title LIKE '%Ticket assegnato a $obj_surname->lastname%' OR th.title LIKE '%Ticket assegnato a $obj_surname->firstname $obj_surname->lastname%') AND th.created LIKE '%".$data_da."%'";
else if($_POST['status_id']==23&&$_POST['data_a']!="") $sql.= " AND t.status_id ='".$_POST['status_id']."' AND (th.title LIKE '%Ticket assegnato a $obj_surname->lastname%' OR th.title LIKE '%Ticket assegnato a $obj_surname->firstname $obj_surname->lastname%') AND th.created BETWEEN '".$data_da_ora."' AND '".$data_a_ora."'";

if($_POST['status_id']==23) $sql.=" ORDER BY assegnato ASC";
else $sql.=" ORDER BY closed ASC";

$result = $connessione->query($sql);
$num_tot = mysqli_num_rows($result);
while ($obj_prod = mysqli_fetch_object($result))
{
   if(substr($obj_prod->assegnato,0,10)!="") $first_line = date('d/m/Y',$obj_prod->assegnato);
   else $first_line = date('d/m/Y',$obj_prod->closed);
    //$select_max_id = "SELECT MAX(id) FROM ost_ticket_thread WHERE ticket_id='".$obj_prod->ticket_id."' AND ";
    $string .= "<tr><td>". $first_line ."</td>";
    if(isset($_SESSION['tipologia'])&&$_SESSION['tipologia']!="T") $string .="<td>".date('d/m/Y',strtotime(substr($obj_prod->duedate,0,10)))."</td>";
    $string .= "<td>";
    if(isset($_SESSION['tipologia'])&&$_SESSION['tipologia']!="T") $string.= "<a target='_blank' style='text-decoration: none;' href='http://ticketglv.fast-data.it/scp/tickets.php?id=".$obj_prod->ticket_id."'>";
      $string .= $obj_prod->ref_num;
      if(isset($_SESSION['tipologia'])&&$_SESSION['tipologia']!="T") $string.= "</a>";
      $string .= "</td><td>".$obj_prod->cr."</td><td>".$obj_prod->topic."</td><td>".$obj_prod->customer_middle_name."</td><td>".$obj_prod->customer_location_l_addr2.", ".$obj_prod->customer_location_l_addr3."</td><td>".$obj_prod->customer_location_l_addr1."</td></tr>";
}
//echo $sql."<br>";
//echo $_POST['data_da']."-".$_POST['data_a'];

  $string_final =   '<table border="1"><tr><td>';
  if(isset($_POST['status_id'])&&$_POST['status_id']=="2") $string_final .=  '<b>Data Chiusura</b>';
  else $string_final .=  '<b>Data Assegnazione</b>';
  if(isset($_SESSION['tipologia'])&&$_SESSION['tipologia']!="T") $string_final .=  '</td><td><b>Scadenza</b></td>';
  $string_final .=  '</td><td><b>Ordine</b></td><td><b>TML</b></td><td><b>Tipologia</b></td><td><b>Insegna</b></td><td><b>Indirizzo</b></td><td><b>Provincia</b></td><tr>';
  $string_final .=  $string;
  $string_final .=  '</table>';

export_xls($string_final,$_SESSION['dol_login']);


  $export = DOL_URL_ROOT . "/theme/eldy/img/export.png";
  $img = ' <img style="width:50px; height:40px;" src="' . $export . '">';
  $down = DOL_URL_ROOT . '/product/reportTecnici/' . $_SESSION['dol_login'] . ".xls";
  $link = '<a href="' . $down . '">' . "Esporta  riepilogo" . '</td>';
  //print "<center>" . $link . "<br>" . $img . "</a></center>";
  print '<br>';
  print '<b>Totale</b>: '.$num_tot.'<br>';
  echo '<table border="1"><tr><td>';
  if(isset($_POST['status_id'])&&$_POST['status_id']=="2") echo '<b>Data Chiusura</b>';
  else echo '<b>Data Assegnazione</b>';
  echo '</td>';
  if(isset($_SESSION['tipologia'])&&$_SESSION['tipologia']!="T") echo '<td><b>Scadenza</b></td>';
  echo '<td><b>Ordine</b></td><td><b>TML</b></td><td><b>Tipologia</b></td><td><b>Insegna</b></td><td><b>Indirizzo</b></td><td><b>Provincia</b></td></tr>';
  echo $string;
  echo '</table>';

} else

print '<b>Non è più possibile consultare i ticket chiusi nel mese precedente</b>';

}

?>
