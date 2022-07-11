<?php

header("Access-Control-Allow-Origin: *");

$host = "localhost";
// username dell'utente in connessione
$user = "root";
// password dell'utente
$password = "servicetech14";
// nome del database
$db = "fd_ticket";

$connessione = new mysqli($host, $user, $password, $db);

require '../main.inc.php';

llxHeader('', $title, $helpurl, '');

?>



<h1>CONTROLLO TICKET</h1>
<form action="" method="post">
	<select name="mese" >
		<option value=""></option>
		<option <?php if(date('m')=="01"){ ?>  <?php } ?> value="01">Gennaio</option>
		<option <?php if(date('m')=="02"){ ?>  <?php } ?> value="02">Febbraio</option>
		<option <?php if(date('m')=="03"){ ?>  <?php } ?> value="03">Marzo</option>
		<option <?php if(date('m')=="04"){ ?>  <?php } ?> value="04">Aprile</option>
		<option <?php if(date('m')=="05"){ ?>  <?php } ?> value="05">Maggio</option>
		<option <?php if(date('m')=="06"){ ?>  <?php } ?> value="06">Giugno</option>
		<option <?php if(date('m')=="07"){ ?>  <?php } ?> value="07">Luglio</option>
		<option <?php if(date('m')=="08"){ ?>  <?php } ?> value="08">Agosto</option>
		<option <?php if(date('m')=="09"){ ?>  <?php } ?> value="09">Settembre</option>
		<option <?php if(date('m')=="10"){ ?>  <?php } ?> value="10">Ottobre</option>
		<option <?php if(date('m')=="11"){ ?>  <?php } ?> value="11">Novembre</option>
		<option <?php if(date('m')=="12"){ ?>  <?php } ?> value="12">Dicembre</option>
	</select>
	<select name="anno">
		<?php for($i=date('Y');$i>=date('Y')-3;$i--){ ?>

		<option value="<?php echo $i;?>"><?php echo $i;?></option>

		<?php } ?>
	</select>
<input type="submit" name="control" value="Controlla">
	</form>

<?php
if(isset($_POST['control'])){

$anno = $_POST['anno'];
$mese = $_POST['mese'];

echo '<b>TICKET APERTI PER LO STESSO CLIENTE LO STESSO GIORNO:</b><br><br>';

echo '<table align="center" border="1"><tr style="background-color:yellow;"><td><b>CLIENTE</b></td><td><b>NOME</b></td><td><b>INDIRIZZO</b></td><td><b>CITTA\'</b></td><td><b>DATA</b></td><td><b>NUMERO INTERVENTI</b></td><td><b>TICKET</b></td></tr>';
	/*
	$sql="SELECT tc.comm_id,count(tc.comm_id) as tot,tc.customer_middle_name,tc.customer_location_l_addr2,tc.customer_location_l_addr7,SUBSTR(t.closed,1,10) as data, t.topic_id FROM ost_ticket__cdata as tc
          INNER JOIN ost_ticket as t ON t.ticket_id = tc.ticket_id
          WHERE t.closed LIKE '%".$anno."-".$mese."%' GROUP BY tc.comm_id,SUBSTR(t.closed,1,10),tc.customer_location_l_addr2 HAVING COUNT(tc.comm_id) >1 ORDER BY data";
*/

$sql="SELECT tc.comm_id,count(tc.comm_id) as tot,tc.customer_middle_name,tc.customer_location_l_addr2,tc.customer_location_l_addr7,SUBSTR(t.closed,1,10) as data, t.topic_id FROM ost_ticket__cdata as tc
          INNER JOIN ost_ticket as t ON t.ticket_id = tc.ticket_id
          WHERE t.closed LIKE '%".$anno."-".$mese."-%' GROUP BY tc.comm_id,SUBSTR(t.closed,1,10),tc.customer_location_l_addr2 HAVING COUNT(tc.comm_id) >1 ORDER BY data";

//echo $sql."<br>";
    $result = $connessione->query($sql);
	$i=0;
    while($obj= mysqli_fetch_object($result)){
		$ticket="";
		$sql_t="SELECT tc.ref_num FROM ost_ticket as t, ost_ticket__cdata as tc WHERE tc.ticket_id= t.ticket_id AND t.closed LIKE '%".$obj->data."%' AND tc.customer_middle_name='".$obj->customer_middle_name."'";
		$result_t = $connessione->query($sql_t);
		//echo $sql_t."<br>";
		if(mysqli_num_rows($result_t)<=1) continue;
		$t=0;
		while($obj_t= mysqli_fetch_object($result_t)){
			$t++;
			$ticket .= $obj_t->ref_num;
			if($t<mysqli_num_rows($result_t)) $ticket.=" ";
		}
        $i++;
        if($obj->topic_id=="12"||$obj->topic_id=="13"||$obj->topic_id=="14"||$obj->topic_id=="15"||$obj->topic_id=="16"||$obj->topic_id=="17") $commessa = "CARTASI";
        else $commessa = "COOPERSYSTEM";

		if($commessa=="COOPERSYSTEM"){
        if($i%2==0) $style="background-color:#eee";
        else $style= "background-color:#fff";
		echo "<tr style='".$style."'><td>".$commessa."</td><td>".$obj->customer_middle_name."</td><td>".$obj->customer_location_l_addr2."</td><td>".$obj->customer_location_l_addr7."</td>
		<td>".$obj->data."</td><td align='center'>".$obj->tot."</td><td>".$ticket."</td></tr>";
		}
    }

}

?>
