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
$user = "admin";
// password dell'utente
$password = "Iniziale1!?";
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
<br>
<select name="commessa">
	<option value=""></option>
	<option value="1">Coopersystem</option>
	<option value="2">Cartasi</option>
	<option value="3">Sisal</option>
</select>
<br><br>
<textarea name="pt_control" value="" cols="100" rows="20">
</textarea>
<br><br>
<input type="submit" name="control" value="Controlla">
	</form>

<?php
if(isset($_POST['control'])){

$data_controllo="01-".$_POST['mese']."-".$_POST['anno'];
$anno = $_POST['anno'];
$mese = $_POST['mese'];
$numeroDiGiorni = date("t",strtotime($anno."-".$mese));

$data=strtotime($anno."-".$mese."-01");
$data1=strtotime('+1 day',strtotime($anno."-".$mese."-".$numeroDiGiorni));

$data = strtotime('-1 minutes',$data);
$data1 = strtotime('-1 minutes',$data1);

//echo $data." ".$data1;

$pt=explode("\n",$_POST['pt_control']);
$pt = array_map('trim', $pt);
$vals = array_count_values($pt);
//echo 'No. of NON Duplicate Items: '.count($vals).'<br><br>';
echo "<br><b>DUPLICATI</b>:<br><br>";
foreach ($vals as $key => $value) {
	// code...
		$sql="SELECT COUNT(ref_num) as c FROM ost_ticket__cdata as tc, ost_ticket as t WHERE tc.ticket_id = t.ticket_id AND tc.ref_num ='".trim($key)."' AND t.status_id IN(2,3) AND tc.zz_dt_clmghw >=  ".$data." AND tc.zz_dt_clmghw <=  ".$data1;
		//echo $sql;
		$result = $connessione->query($sql);
		$obj_id=mysqli_fetch_object($result);
		//print_r($obj_id);

		if($obj_id->c>1) echo "TICKET: ".$key."<br>CONTROLLO: ".$value."<br>CHIUSI: ".$obj_id->c."<br><br>";


}
//echo "La riga di controllo è la seguente:<br><br>".nl2br($_POST['pt_control']);
//echo 'SPLIT:<br><br>';

$string="";
$i=0;
echo '<br><b>TICKET NON PRESENTI SU FASTDATA:</b><br><br>';
$pt = array_unique($pt);
foreach ($pt as $pt_number) {
//echo $pt_number.'<br>';

$i++;
$string.="'".trim($pt_number)."'";
$bis= trim($pt_number)."bis";
if($i<count($pt))  $string.=",";

$sql="SELECT * FROM ost_ticket__cdata as tc, ost_ticket as t WHERE tc.ticket_id = t.ticket_id AND (tc.ref_num ='".trim($pt_number)."' OR tc.ref_num ='".trim($bis)."') AND t.status_id IN(2,3) AND tc.zz_dt_clmghw >=  ".$data." AND tc.zz_dt_clmghw <=  ".$data1;
//echo $sql;
$result = $connessione->query($sql);
//if($pt_number=="128497C") echo $sql;
//echo "NUMERO RISULTATI: ".mysqli_num_rows($result)."<br>";
$obj_id=mysqli_fetch_object($result);
if(mysqli_num_rows($result)>0) {  } //echo $pt_number.": PRESENTE<br>";
else echo $pt_number."<br>";

}

//echo $string;
if($_POST['commessa']==1)
{
	$sql_fact1="SELECT tc.ref_num,hp.topic FROM ost_ticket__cdata as tc, ost_ticket as t, ost_help_topic as hp WHERE t.topic_id=hp.topic_id AND tc.ticket_id = t.ticket_id AND REPLACE(tc.ref_num,'bis','') NOT IN($string) AND t.status_id IN(2,3) AND tc.zz_dt_clmghw >=  ".$data." AND tc.zz_dt_clmghw <=  ".$data1." AND t.topic_id NOT IN (12,13,14,15,16,17,39,40,41,42,43)" ;
	$result_fact1=$connessione->query($sql_fact1);
	echo "<br><b>TICKET NON PRESENTI NEL FILE COOPERSYSTEM:</b><br>";
} else if($_POST['commessa']==2)
{
	$sql_fact1="SELECT tc.ref_num,hp.topic FROM ost_ticket__cdata as tc, ost_ticket as t, ost_help_topic as hp WHERE t.topic_id=hp.topic_id AND tc.ticket_id = t.ticket_id AND REPLACE(tc.ref_num,'bis','') NOT IN($string) AND t.status_id IN(2,9) AND tc.zz_dt_clmghw >=  ".$data." AND tc.zz_dt_clmghw <=  ".$data1." AND t.topic_id IN (12,13,14,15,16,17,39,43)" ;
	$result_fact1=$connessione->query($sql_fact1);
	echo "<br><b>TICKET NON PRESENTI NEL FILE CARTASI:</b><br>";
} else if($_POST['commessa']==3)
{
	$sql_fact1="SELECT tc.ref_num,hp.topic FROM ost_ticket__cdata as tc, ost_ticket as t, ost_help_topic as hp WHERE t.topic_id=hp.topic_id AND tc.ticket_id = t.ticket_id AND REPLACE(tc.ref_num,'bis','') NOT IN($string) AND t.status_id IN(2,9) AND tc.zz_dt_clmghw >=  ".$data." AND tc.zz_dt_clmghw <=  ".$data1." AND t.topic_id IN (40,41,42)" ;
	$result_fact1=$connessione->query($sql_fact1);
	echo "<br><b>TICKET NON PRESENTI NEL FILE SISAL:</b><br>";
}
while($obj_fact1=mysqli_fetch_object($result_fact1)){
echo "<br>".$obj_fact1->ref_num." - ".$obj_fact1->topic;
}
}
?>
