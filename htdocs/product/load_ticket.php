<?php

header("Access-Control-Allow-Origin: *");


error_reporting(E_ALL);
ini_set('display_errors',1);

$host = "localhost";
// username dell'utente in connessione
$user = "admin";
// password dell'utente
$password = "Iniziale1!?";
// nome del database
$db = "fd_ticket";

$db_dol = "dolibarr";

$connessione = new mysqli($host, $user, $password, $db);

$connessione_dol = new mysqli($host, $user, $password, $db_dol);

require '../main.inc.php';

llxHeader('', $title, $helpurl, '');

?>



<h1>CARICAMENTO TICKET</h1>
<form action="" method="post" enctype="multipart/form-data">
  <input type="file"
         id="file" name="fileToUpload">
<input type="submit" name="control" value="Carica">
	</form>

<?php
if(isset($_POST['control'])){

  function mres($value)
  {
      $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
      $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");

      return str_replace($search, $replace, $value);
  }

  require_once "simplexlsx.class.php";

  $xlsx = new SimpleXLSX($_FILES['fileToUpload']['tmp_name']);

  list($cols,) = $xlsx->dimension();
  $tck = array();
    $tck_insert = "";
    $tck_exist = "";
  foreach( $xlsx->rows() as $k => $r) {



      if ($k == 0) continue; // skip first row
      $sql_control="SELECT ref_num FROM ost_ticket__cdata WHERE ref_num= '".$r[3]."' AND termid= '".$r[1]."'";
      //echo $sql_control."<br>";
      //echo $sql_tecnico;
      $result=$connessione->query($sql_control);
      if(mysqli_num_rows($result)>0){
              $tck_exist .= "Il ticket ".$r[3] ." con Termid ".$r[1] . " risulta già è inserito a sistema! <br>";
              continue;
          }
      $nome= explode(" ",$r[15]);
      if($nome[1]=="De") $nome[1] = "De Lorenzo";
          $sql_tecnico="SELECT staff_id FROM ost_staff WHERE lastname= '".$nome[1]."'";
          //echo $sql_tecnico;
          $result=$connessione->query($sql_tecnico);
          $id_tecnico=mysqli_fetch_object($result);

          if($id_tecnico->staff_id==""||$id_tecnico->staff_id==79) $id_tecnico->staff_id= 80;
          //INSTALLAZIONE -> 13
          //DISINSTALLAZIONE -> 12
          //MANUTENZIONE -> 17
          //SOSTITUZIONE -> 14
          if($r[12]=="DIS"){
            $topic_id = 12;
            $costo_ext = 15;
            $costo_int = 8;
            $prezzo = 7;
            if($id_tecnico->staff_id == 80){
              $costo_int = 0;
              $prezzo = 15;
            }
          }
          else if($r[12]=="INO"||$r[12]=="INU"||$r[12]=="CRS"){
            $topic_id = 13;
            $costo_ext = 20;
            $costo_int = 11;
            $prezzo = 9;
            if($id_tecnico->staff_id == 80){
              $costo_int = 0;
              $prezzo = 20;
            }
          }
          else if($r[12]=="MAO"||$r[12]=="MAS"||$r[12]=="MAU"){
            $topic_id = 17;
            $costo_ext = 20;
            $costo_int = 10;
            $prezzo = 10;
            if($id_tecnico->staff_id == 80){
              $costo_int = 0;
              $prezzo = 20;
            }
          }
          else if($r[12]=="LAM"||$r[12]=="SOP"||$r[12]=="SPE"||$r[12]=="SUP"||$r[12]=="TEL"){
            $topic_id = 44;
            $costo_ext = 0;
            $costo_int = 0;
            $prezzo = 0;
            if($id_tecnico->staff_id == 80){
              $costo_int = 0;
              $prezzo = 0;
            }
          }
          else if($r[12]=="MIG"){
            $topic_id = 15;
            $costo_ext = 16;
            $costo_int = 10;
            $prezzo = 6;
            if($id_tecnico->staff_id == 80){
              $costo_int = 0;
              $prezzo = 16;
            }
          }
          else if($r[12]=="ODV"){
            $topic_id = 43;
            $costo_ext = 15;
            $costo_int = 8;
            $prezzo = 7;
            if($id_tecnico->staff_id == 80){
              $costo_int = 0;
              $prezzo = 15;
            }
          }
          else if($r[12]=="SOS"||$r[12]=="SOU"){
            $topic_id = 14;
            $costo_ext = 20;
            $costo_int = 11;
            $prezzo = 9;
            if($id_tecnico->staff_id == 80){
              $costo_int = 0;
              $prezzo = 20;
            }
          }
          else{
            $topic_id = 17;
            $costo_ext = 20;
            $costo_int = 10;
            $prezzo = 10;
            if($id_tecnico->staff_id == 80){
              $costo_int = 0;
              $prezzo = 20;
            }
          }

          $data_chiusura = substr($r[11],6,4)."-".substr($r[11],3,2)."-".substr($r[11],0,2)." ".substr($r[11],11,8);
          $data_apertura = substr($r[5],6,4)."-".substr($r[5],3,2)."-".substr($r[5],0,2)." ".substr($r[5],11,8);
          $data_scadenza = substr($r[6],6,4)."-".substr($r[6],3,2)."-".substr($r[6],0,2)." ".substr($r[6],11,8);
          /*echo $data_chiusura."<br>";
          echo $topic_id."<br>";
          echo $id_tecnico->staff_id."<br><br>";*/

          $r[27] = str_replace("'","\'",$r[27]);
          $r[28] = str_replace("'","\'",$r[28]);
          $r[40] = str_replace("'","\'",$r[40]);
          $r[41] = str_replace("'","\'",$r[41]);
          $r[42] = str_replace("'","\'",$r[42]);

          $sql="select id from ost_user where name = '".mres($r[27])."'";
  //echo $sql."<br><br>";
  //else
  //$sql="select ref_num from ost_ticket__cdata natural join ost_ticket where status_id!=2";
  $result=$connessione->query($sql);
  $num = mysqli_num_rows($result);

  //echo $num."<br><br>";


  if($num<1){

    $sql_user = "SELECT MAX(id) as id_user FROM ost_user";
    $result_id =$connessione->query($sql_user);
    $id_user = mysqli_fetch_array($result_id);
    $id_user = $id_user['id_user']+1;

    $sql_user = "SELECT MAX(id) as id_email FROM ost_user_email";
    $result_id =$connessione->query($sql_user);
    $id_mail = mysqli_fetch_array($result_id);
    $id_mail = $id_mail['id_email']+1;
    $mail = preg_replace("/[^A-Za-z0-9]/",'',$r[24])."@service-tech.org";

    $sql_1 = "INSERT INTO ost_user (id, org_id, default_email_id,status, name) VALUES ($id_user,0,$id_mail,0,'".mres($r[27])."')";
    $result =$connessione->query($sql_1);
    //echo $sql_1;
    $sql_2 = "INSERT INTO ost_user_email (id, user_id, address) VALUES ($id_mail,$id_user,'".$mail."')";
    $result =$connessione->query($sql_2);

    $sql_3 = "INSERT INTO ost_commesse (user_id, codice, nome, cliente, gruppo) VALUES ($id_user,$id_user,'".mres($r[24])."','".mres($r[27])."',$id_user)";
    $result =$connessione->query($sql_3);

    $comm_id = $connessione->insert_id;
    //echo $sql_2;
  } else{
    $sql_user = "SELECT id as id_user FROM ost_user WHERE name = '".mres($r[27])."'";
    $result_id =$connessione->query($sql_user);
    $id_user = mysqli_fetch_array($result_id);
    $id_user = $id_user['id_user'];

      $sql_user = "SELECT id as id_email FROM ost_user_email WHERE user_id = ".$id_user;
      $result_id =$connessione->query($sql_user);
      $id_mail = mysqli_fetch_array($result_id);
      $id_mail = $id_mail['id_email'];

    $sql_comm = "SELECT comm_id FROM ost_commesse WHERE nome = '".mres($r[27])."'";
    $result_id_comm =$connessione->query($sql_comm);
    $id_comm = mysqli_fetch_array($result_id_comm);
    $comm_id = $id_comm['comm_id'];

    //echo $sql_comm;

  }

     $sql_number = 'SELECT number FROM ost_ticket WHERE ticket_id = (SELECT ids FROM (SELECT MAX(ticket_id) as ids FROM ost_ticket as t) as tmp)';
    $result_number=$connessione->query($sql_number);
    $number = mysqli_fetch_array($result_number);
    $number = $number['number'] + 1;

          $sql_fact="INSERT INTO ost_ticket (number, user_id,status_id,dept_id,sla_id,topic_id,staff_id,ip_address,source,isoverdue,isanswered,duedate,closed,created,updated) VALUES (".$number.",$id_user,2,6,1,".$topic_id.",".$id_tecnico->staff_id.",'2.38.111.70','Other',0,0,'".$data_scadenza."','".$data_chiusura."','".$data_apertura."','".$data_apertura."')";
        	$result_fact=$connessione->query($sql_fact);
          //echo $sql_fact."<br><br>";

          $last_id = $connessione->insert_id;
          //echo $last_id;
          $timestamp = strtotime($data_chiusura);
          $sql_fact="INSERT INTO ost_ticket__cdata (ticket_id,cr,customer_middle_name,ref_num,customer_location_l_addr2,customer_location_l_addr7,customer_location_l_addr3,customer_location_l_addr1,costo_ext,costo_int,prezzo,zz_dt_clmghw,customer_phone_number, comm_id) VALUES (".$last_id.",'".$r[1]."','".$r[27]."','".$r[3]."','".$r[28]."','".$r[29]."','".$r[31]."','".$r[30]."','".$costo_ext."','".$costo_int."','".$prezzo."',".$timestamp.",'".$r[34]."',".$comm_id.")";
        	$result_fact=$connessione->query($sql_fact);
          //echo $sql_fact;
          //if($r[2]=='4369211')  echo $sql_fact;

          $descrizione= "Pos New: ".$r[47]." - Pos Old: ".$r[49]."<br>Chiuso il: ".$r[11]."<br>Assegnato a: ".$r[15]."<br>".$r[41]."<br>".$r[40]."<br>Banca: ". $r[42]."<br>Termid: ".$r[1]."<br>Pinpad Old: ".$r[53]." - Pinpad New: ". $r[51]."<br>Sim New: ". $r[55]." - Sim Old:". $r[56];
          $sql_des="INSERT INTO ost_ticket_thread (id, pid, ticket_id, staff_id, user_id, thread_type, poster, source, title, body, format, ip_address, created, updated) VALUES (NULL, '0', '".$last_id."', '77', '0', 'M', 'Sistema', 'Other', 'DESCRIZIONE', '".$descrizione."', 'html', '2.38.111.78', '".date('Y-m-d H:i:s')."', '".date('Y-m-d H:i:s')."')";
          $result_des=$connessione->query($sql_des);

          $last_id_thread = $connessione->insert_id;

          $sql_fact="INSERT INTO ost__search (object_type, object_id, content) VALUES ('H','".$last_id_thread."','".$r[3]." ".$r[1]." ".$r[27]." ".$r[47]." ".$r[49]." ".$r[51]." ".$r[53]." ".$r[55]." ".$r[56]."')";
          $result_fact=$connessione->query($sql_fact);

//echo $sql_fact;
          /*$sql_fact="INSERT INTO ost__search (object_type, object_id, content) VALUES ('T',".$last_id.",'".$r[38]."')";
          $result_fact=$connessione->query($sql_fact);
//echo $sql_fact;
          $sql_fact="INSERT INTO ost__search (object_type, object_id, content) VALUES ('T',".$last_id.",'".$r[40]."')";
          $result_fact=$connessione->query($sql_fact);
//echo $sql_fact;
          $sql_fact="INSERT INTO ost__search (object_type, object_id, content) VALUES ('T',".$last_id.",'".$r[23]."')";
          $result_fact=$connessione->query($sql_fact);
          //echo $sql_fact;$result_fact=$connessione->query($sql_fact);
//echo $sql_fact;
          $sql_fact="INSERT INTO ost__search (object_type, object_id, content) VALUES ('T',".$last_id.",'".$r[1]."')";
          $result_fact=$connessione->query($sql_fact);*/
          //echo $sql_fact;$result_fact=$connessione->query($sql_fact);
//echo $sql_fact;
          //echo $sql_des;
      if($r['47']!=''&&$r['51']!=''&&$r['55']!='') {
          $sql_mat = "UPDATE llx_asset SET id_magazzino = 33, numero_ticket = '" . $r[3] . "', data_chiusura= '" . $r[11] . "', tipologia_intervento = '" . $r[12] . "', termid='" . $r[1] . "' WHERE matricola = '" . $r['47'] . "' OR matricola = '" . $r['51'] . "' OR matricola = '" . $r['55'] . "'";
          $result_mat = $connessione_dol->query($sql_mat);
      }

      $tck_insert .= "Il ticket ".$r[3] ." con Termid ".$r[1] . " è stato inserito correttamente! <br>";

          //r[1] -> Termid
          //r[2] -> Ref Num -> r[3]
          //r[4] -> Data Apertura -> r[5]
          //r[5] -> Data Scadenza -> r[6]
          //r[10] -> Data Chiusura -> r[11]
          //r[11] -> Tipologia -> r[12]
          //r[14] -> Tecnico -> r[15]
          //r[20] -> Modello Chiusura -> r[22]
          //r[24] -> Insegna -> r[27]
          //r[25] -> Indirizzo -> r[28]
          //r[26] -> Città -> r[29]
          //r[27] -> Provincia -> r[30]
          //r[28] -> CAP -> r[31]
          //r[29] -> Telefono -> r[34]
          //r[41] -> Pos Old -> r[49]
          //r[39] -> Pos New -> r[47]
          //r[32] -> Segnalazione Breve  -> r[40]
          //r[33] -> Segnalazione Estesa -> r[41]
          //r[34] -> Banca -> r[42]
          //r[45] -> Pinpad Old -> r[53]
          //r[43] -> PinPad New -> r[51]
          //r[47] -> Sim New -> r[55]
          //r[48] -> Sim Old -> r[56]
            //echo '<td>'.$i."-".( (isset($r[$i])) ? $r[$i] : '&nbsp;' ).'</td>';
        }

}
  //echo '</table>';
echo $tck_exist;
echo $tck_insert;
?>
