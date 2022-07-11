<?php

header("Access-Control-Allow-Origin: *");
/*
echo ini_get('display_errors');

if (!ini_get('display_errors')) {
    ini_set('display_errors', '1');
}

echo ini_get('display_errors');
*/
$host = "localhost";
// username dell'utente in connessione
$user = "root";
// password dell'utente
$password = "servicetech14";
// nome del database
$db = "fd_ticket";

$connessione = new mysqli($host, $user, $password, $db);



//require '../main.inc.php';
$yesterday = strtotime("-1 day");
$yesterday =  date('Y-m-d', $yesterday);



   $sql = "SELECT * FROM ost_ticket__cdata as tc, ost_ticket as t WHERE tc.ticket_id = t.ticket_id AND tc.affected_resource_zz_wam_string1 LIKE '%POYNT%' AND t.closed LIKE '%".$yesterday."%' AND t.status_id IN (2,3) AND t.topic_id IN (13,14) AND tc.costo_ext != '28'";
    $result=$connessione->query($sql);
    echo $sql;

$sql_cron = "UPDATE ost_cron SET last_update = '".date('d-m-Y')."', query='".addslashes($sql)."' WHERE cron ='POYNT'";
$result_cron=$connessione->query($sql_cron);
echo $sql_cron;
    //echo "<br><b>CARTASI</b><br>";

    while ($obj_prod = mysqli_fetch_object($result))
    {
      //echo "STAFF:".$obj_prod->staff_id."-".$obj_prod->ticket_id."<br>";
      if($obj_prod->staff_id!=80)
      {
        $sql_update = "UPDATE ost_ticket__cdata SET costo_ext = 28, costo_int = 11, prezzo = 17 WHERE ticket_id = ".$obj_prod->ticket_id;
        $result_update=$connessione->query($sql_update);
          echo '<br>'.$sql_update;
          $sqlsql .= $sql_update;
      }
      else if($obj_prod->staff_id==80)
      {
        $sql_update = "UPDATE ost_ticket__cdata SET costo_ext = 28, costo_int = 0, prezzo = 28 WHERE ticket_id = ".$obj_prod->ticket_id;
        echo '<br>'.$sql_update;
        $result_update=$connessione->query($sql_update);
          $sqlsql .= $sql_update;
      }
      //echo $sql_update."<br>";
      //echo '<br>'.$obj_prod->ticket_id."-".$obj_prod->number."-".$obj_prod->topic_id."-".$obj_prod->affected_resource_zz_wam_string1."-".$obj_prod->costo_ext."<br>";

      //$sql_update = "UPDATE ost_ticket__cdata SET costo_ext = 28, costo_int = 11, prezzo = 18 WHERE ticket_id = ".$obj_prod->ticket_id;


    }

mail("marco.salmi89@gmail.com","TEST POYNT",$sql."<br>".$sqlsql);

    /*function nuovascadenza($from, $days, $holidays) {
        $workingDays = [1, 2, 3, 4, 5, 6]; # date format = N (1 = Monday, ...)
        $holidayDays = ['*-12-25', '*-01-01','*-01-06','*-04-25','*-05-01','*-06-02','*-08-15','*-11-01','2019-01-01', '2019-01-06', '2019-04-22', '2019-04-25', '2019-05-01'];# variable and fixed holidays
        $d="";
        $from = new DateTime($from);
        while ($days) {
            $from->modify('+1 day');
            if (!in_array($from->format('N'), $workingDays)) continue;
            if (in_array($from->format('Y-m-d'), $holidayDays)) continue;
            if (in_array($from->format('*-m-d'), $holidayDays)) continue;
            $days--;
            //$d.= "GIORNI:".$days."-".$from->format('Y-m-d')."<br>";
        }
        //return $d;
        return $from->format('Y-m-d'); #  or just return DateTime object
    }

    $scadenza = nuovascadenza('2019-10-23',4,'');
    echo $scadenza;
    //echo 'ciao';
*/

?>
