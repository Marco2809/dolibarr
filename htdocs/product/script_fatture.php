<?php
header("Access-Control-Allow-Origin: *");
/*echo ini_get('display_errors');

if (!ini_get('display_errors')) {
    ini_set('display_errors', '0');
}

echo ini_get('display_errors');*/


require '../main.inc.php';

llxHeader('', $title, $helpurl, '');
$host = "localhost";
// username dell'utente in connessione
$user = "root";
// password dell'utente
$password = "servicetech14";
// nome del database
$db = "fd_ticket";
?>
<div style="color:black;">
<h1>GENERA FATTURE</h1>
<form action="" method="post">
<select name="mese">
	<option value=""></option>
<option <?php if(date('m')=="02"){ ?>  <?php } ?> value="01">Gennaio</option>
<option <?php if(date('m')=="03"){ ?>  <?php } ?> value="02">Febbraio</option>
<option <?php if(date('m')=="04"){ ?>  <?php } ?> value="03">Marzo</option>
<option <?php if(date('m')=="05"){ ?>  <?php } ?> value="04">Aprile</option>
<option <?php if(date('m')=="06"){ ?>  <?php } ?> value="05">Maggio</option>
<option <?php if(date('m')=="07"){ ?>  <?php } ?> value="06">Giugno</option>
<option <?php if(date('m')=="08"){ ?>  <?php } ?> value="07">Luglio</option>
<option <?php if(date('m')=="09"){ ?>  <?php } ?> value="08">Agosto</option>
<option <?php if(date('m')=="10"){ ?>  <?php } ?> value="09">Settembre</option>
<option <?php if(date('m')=="11"){ ?>  <?php } ?> value="10">Ottobre</option>
<option <?php if(date('m')=="12"){ ?>  <?php } ?> value="11">Novembre</option>
<option <?php if(date('m')=="01"){ ?>  <?php } ?> value="12">Dicembre</option>
</select>
<select name="anno">
<option value="<?php echo date('Y')-1;?>"><?php echo date('Y')-1;?></option>
<option selected value="<?php echo date('Y');?>"><?php echo date('Y');?></option>
<option value="<?php echo date('Y')+1;?>"><?php echo date('Y')+1;?></option>
</select>
<br>
<table>
	<tr><td></td><td><b>COOPERSYSTEM</b></td><td></td><td><b>CARTASI</b></td><td></td><td><b>SISAL</b></td></tr>
<tr><td>Ordini Pending:</td><td><input type="text" name="differenza_coop"></td><td>Ordini Pending:</td><td><input type="text" name="differenza_nexi"></td><td>Ordini Pending:</td><td><input type="text" name="differenza_sisal"></td></tr>
<tr><td>UAV:</td><td><input type="text" name="uav_coop"></td><td>UAV:</td><td><input type="text" name="uav_nexi"></td><td>UAV:</td><td><input type="text" name="uav_sisal"></td></tr>
<tr><td>Urgenza:</td><td><input type="text" name="urgenza_coop"></td><td>Urgenza:</td><td><input type="text" name="urgenza_nexi"></td><td>Urgenza:</td><td><input type="text" name="urgenza_sisal"></td></tr>
</table>
<input type="submit" name="genera" value="Genera">
</form>
<br><br>
<?php
if(isset($_POST['genera'])||isset($_POST['genera_pdf'])){

echo '<form action="" method="post">';
echo '<input type="submit" name="genera_pdf" value="Genera PDF">';
echo '<input type="hidden" name="anno" value="'.$_POST['anno'].'">';
echo '<input type="hidden" name="mese" value="'.$_POST['mese'].'">';
echo '</form><br>';

$anno = $_POST['anno'];
$mese = $_POST['mese'];
//$mese = date('m',strtotime($data_controllo . ' -1 month'));
$mese_ok = $mese;
$numeroDiGiorni = date("t",strtotime($anno."-".$mese));



$data=strtotime($anno."-".$mese."-01");
$data1=strtotime("+1 day",strtotime($anno."-".$mese."-".$numeroDiGiorni));
$data_fattura = $anno."-".$mese."-".$numeroDiGiorni." 00:00:00";
$data_fattura_2 = $anno."-".$mese."-".$numeroDiGiorni;
$connessione = new mysqli($host, $user, $password, $db);

//echo $data."<br>".$data1."<br><br>";

$array_cartasi = array();
$array_cooper  = array();
$array_sisal  = array();
$array_totale  = array();
//$data= strtotime("2018-02-01");
//$data1=strtotime("2018-02-28");

//echo "<br>DATA:".$data."<br>";
//echo "<br>DATA1:".$data1."<br>";

//CREAZIONE FATTURE

$sql='SELECT staff_id, firstname, lastname FROM ost_staff';
$result=$connessione->query($sql);
//echo "<br><b>CARTASI</b><br>";
while($obj= mysqli_fetch_object($result)){

$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_int ) AS tot,SUM( a.costo_ext ) AS tot_est, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE b.staff_id =".$obj->staff_id."
AND a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_int >=0
AND b.status_id IN (2,3)
AND b.topic_id IN (12,13,14,15,16,17,39,43,44)
GROUP BY b.staff_id";
$result_fact=$connessione->query($sql_fact);
//echo $sql_fact;

while($obj_fact= mysqli_fetch_object($result_fact)){


$array_cartasi[$obj->firstname." ".$obj->lastname]['nome'] = $obj->firstname." ".$obj->lastname;
$array_cartasi[$obj->firstname." ".$obj->lastname]['totale'] = $obj_fact->tot;
$array_cartasi[$obj->firstname." ".$obj->lastname]['totale_ext'] = $obj_fact->tot_est;
$array_totale[$obj->firstname." ".$obj->lastname]['nome'] = $obj->firstname." ".$obj->lastname;
$array_totale[$obj->firstname." ".$obj->lastname]['totale'] += $obj_fact->tot;
$array_totale[$obj->firstname." ".$obj->lastname]['totale_ext'] += $obj_fact->tot_est;
/*
echo "<br>NOME: ".$obj->firstname." ".$obj->lastname."<br>";
echo "COMMESSA: ".$obj_fact->comm_id."<br>";
echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "TOTALE EURO: ".$obj_fact->tot."<br>";
*/
/*mysqli_select_db($connessione,'dolibarr');

$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "'.$obj->firstname." ".$obj->lastname.'"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";
*/
$nome = $obj->firstname." ".$obj->lastname;
if($nome=="Giuseppe Spinelli")
	$vat= ($obj_fact->tot * 22)/100;
else $vat=0;
$tot = $obj_fact->tot+$vat;
$array_cartasi[$obj->firstname." ".$obj->lastname]['iva'] = $vat;
$array_cartasi[$obj->firstname." ".$obj->lastname]['totale_iva'] = $tot;
$array_cartasi[$obj->firstname." ".$obj->lastname]['qty'] = $obj_fact->tot_tck;
$array_totale[$obj->firstname." ".$obj->lastname]['iva'] += $vat;
$array_totale[$obj->firstname." ".$obj->lastname]['totale+iva'] += $tot;
/*echo "IVA:".$vat."<br>";
echo "TOTALE:".$tot."<br>";
echo "QTY:".$obj_fact->tot_tck."<br>";
*/
$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","SERVIZI PER CARTASI","'.$obj_fact->tot.'","'.$tot.'","'.$obj_fact->tot_tck.'","22.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');
//echo "_________________________________________________<br>";
}

}


$sql='SELECT staff_id, firstname, lastname FROM ost_staff';
$result=$connessione->query($sql);
//echo "<br><b>COOPERSYSTEM</b><br>";
while($obj= mysqli_fetch_object($result)){

$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_int ) AS tot,SUM( a.costo_ext ) AS tot_est, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE b.staff_id =".$obj->staff_id."
AND a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_int >=0
AND b.status_id IN (2,3)
AND b.topic_id NOT IN (12,13,14,15,16,17,39,40,41,42,43)
GROUP BY b.staff_id";
$result_fact=$connessione->query($sql_fact);

//echo $sql_fact;
while($obj_fact= mysqli_fetch_object($result_fact)){

/*
echo "<br>NOME: ".$obj->firstname." ".$obj->lastname."<br>";
echo "COMMESSA: ".$obj_fact->comm_id."<br>";
echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "TOTALE EURO: ".$obj_fact->tot."<br>";
*/
$array_cooper[$obj->firstname." ".$obj->lastname]['nome'] = $obj->firstname." ".$obj->lastname;
$array_cooper[$obj->firstname." ".$obj->lastname]['totale'] = $obj_fact->tot;
$array_cooper[$obj->firstname." ".$obj->lastname]['totale_ext'] = $obj_fact->tot_est;
$array_totale[$obj->firstname." ".$obj->lastname]['nome'] = $obj->firstname." ".$obj->lastname;
$array_totale[$obj->firstname." ".$obj->lastname]['totale'] += $obj_fact->tot;
$array_totale[$obj->firstname." ".$obj->lastname]['totale_ext'] += $obj_fact->tot_est;

/*mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "'.$obj->firstname." ".$obj->lastname.'"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;
*/
//echo "FATTURA:".$ref."<br>";
$nome = $obj->firstname." ".$obj->lastname;
if($nome=="Giuseppe Spinelli")
	$vat= ($obj_fact->tot * 22)/100;
else $vat=0;
$tot = $obj_fact->tot+$vat;
$array_cooper[$obj->firstname." ".$obj->lastname]['iva'] = $vat;
$array_cooper[$obj->firstname." ".$obj->lastname]['totale_iva'] = $tot;
$array_cooper[$obj->firstname." ".$obj->lastname]['qty'] = $obj_fact->tot_tck;
$array_totale[$obj->firstname." ".$obj->lastname]['iva'] += $vat;
$array_totale[$obj->firstname." ".$obj->lastname]['totale+iva'] += $tot;
/*echo "IVA:".$vat."<br>";
echo "TOTALE:".$tot."<br>";
echo "QTY:".$obj_fact->tot_tck."<br>";
*/
$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","SERVIZI PER COOPERSYSTEM","'.$obj_fact->tot.'","'.$tot.'","'.$obj_fact->tot_tck.'","22.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');
//echo "_________________________________________________<br>";
}

}


$obj_fact= "";
$result_fact = "";
$sql='SELECT staff_id, firstname, lastname FROM ost_staff';
$result=$connessione->query($sql);
//echo "<br><b>SISAL</b><br>";
while($obj= mysqli_fetch_object($result)){

$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_int ) AS tot,SUM( a.costo_ext ) AS tot_est, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE b.staff_id =".$obj->staff_id."
AND a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_int >=0
AND b.status_id IN (2,3)
AND b.topic_id IN (41,42)
GROUP BY b.staff_id";
$result_fact=$connessione->query($sql_fact);

//echo $sql_fact;
while($obj_fact= mysqli_fetch_object($result_fact)){

/*
echo "<br>NOME: ".$obj->firstname." ".$obj->lastname."<br>";
echo "COMMESSA: ".$obj_fact->comm_id."<br>";
echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "TOTALE EURO: ".$obj_fact->tot."<br>";
*/
$array_sisal[$obj->firstname." ".$obj->lastname]['nome'] = $obj->firstname." ".$obj->lastname;
$array_sisal[$obj->firstname." ".$obj->lastname]['totale'] = $obj_fact->tot;
$array_sisal[$obj->firstname." ".$obj->lastname]['totale_ext'] = $obj_fact->tot_est;
$array_totale[$obj->firstname." ".$obj->lastname]['nome'] = $obj->firstname." ".$obj->lastname;
$array_totale[$obj->firstname." ".$obj->lastname]['totale'] += $obj_fact->tot;
$array_totale[$obj->firstname." ".$obj->lastname]['totale_ext'] += $obj_fact->tot_est;

/*mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "'.$obj->firstname." ".$obj->lastname.'"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;
*/
//echo "FATTURA:".$ref."<br>";
$nome = $obj->firstname." ".$obj->lastname;
if($nome=="Giuseppe Spinelli")
	$vat= ($obj_fact->tot * 22)/100;
else $vat=0;
$tot = $obj_fact->tot+$vat;
$array_sisal[$obj->firstname." ".$obj->lastname]['iva'] = $vat;
$array_sisal[$obj->firstname." ".$obj->lastname]['totale_iva'] = $tot;
$array_sisal[$obj->firstname." ".$obj->lastname]['qty'] = $obj_fact->tot_tck;
$array_totale[$obj->firstname." ".$obj->lastname]['iva'] += $vat;
$array_totale[$obj->firstname." ".$obj->lastname]['totale+iva'] += $tot;
/*echo "IVA:".$vat."<br>";
echo "TOTALE:".$tot."<br>";
echo "QTY:".$obj_fact->tot_tck."<br>";
*/
$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","SERVIZI PER SISAL","'.$obj_fact->tot.'","'.$tot.'","'.$obj_fact->tot_tck.'","22.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');
//echo "_________________________________________________<br>";
}

}
//print "CARTASI:<br>";
//print_r($array_cartasi);
//print "<br>COOPERSYSTEM:<br>";
//print_r($array_cooper);
//print "<br>TOTALE:<br>";
//print_r($array_totale);
//print_r($array_cooper);
//print_r($array_cooper);
//print_r($array_sisal);


foreach($array_cooper as $key)
{
	if($key['nome']!="Alessandro Matilli"&&$key['nome']!="Giovanna Matilli")
	{
		echo "<table width=500 border=1><tr><td colspan=5 style='text-align:center;'><b>". $key['nome']."</b></td></tr>";
		echo "<tr><td></td><td>COOPERSYSTEM</td><td>CARTASI</td><td>SISAL</td><td>TOTALE</td></tr>";
		echo "<tr><td>QTY</td><td> ". $key['qty']."</td><td>".$array_cartasi[$key['nome']]['qty']."</td><td>".$array_sisal[$key['nome']]['qty']."</td><td>".($array_cartasi[$key['nome']]['qty']+$array_sisal[$key['nome']]['qty']+$key['qty'])."</td></tr>";
		if($key['nome']=="Ditta Ditta"){
		echo "<tr><td>IMPONIBILE</td><td> ". $key['totale_ext']." &euro;</td><td>".round($array_cartasi[$key['nome']]['totale_ext'])." &euro;</td><td>".round($array_sisal[$key['nome']]['totale_ext'])." &euro;</td><td>".$array_totale[$key['nome']]['totale_ext']." &euro;</td></tr>";
		}
		else {
		echo "<tr><td>IMPONIBILE</td><td> ". $key['totale']." &euro;</td><td>".round($array_cartasi[$key['nome']]['totale'])." &euro;</td><td>".round($array_sisal[$key['nome']]['totale'])." &euro;</td><td>".$array_totale[$key['nome']]['totale']." &euro;</td></tr>";
		}
		if($key['nome']=="Giuseppe Spinelli"){
		//echo '<tr><td colspan="4">&nbsp;</td></tr>';
		echo "<tr><td>IVA</td><td></td><td></td><td>".$array_totale[$key['nome']]['iva']." &euro;</td></tr>";
		echo "<tr><td>TOTALE</td><td></td><td></td><td>".(($key['totale']+$key['iva'])+($array_cartasi[$key['nome']]['iva']+$array_cartasi[$key['nome']]['totale']+$array_sisal[$key['nome']]['iva']+$array_sisal[$key['nome']]['totale']))." &euro;</td></tr>";
		}
		echo "</table><br><br>";
	}

}

foreach($array_cartasi as $key)
{
	if($key['nome']!="Alessandro Matilli"&&$key['nome']!="Giovanna Matilli")
	{
		if($key['nome']!=$array_cooper[$key['nome']]['nome']&&($array_cooper[$key['nome']]['totale_iva']>0||$key['totale_iva']>0)) {
		echo "<table width=500 border=1><tr><td colspan=4 style='text-align:center;'><b>". $key['nome']."</b></td></tr>";
		echo "<tr><td></td><td>COOPERSYSTEM</td><td>CARTASI</td><td>SISAL</td><td>TOTALE</td></tr>";
		echo "<tr><td>QTY</td><td> ". $array_cooper[$key['nome']]['qty']."</td><td>".$key['qty']."</td><td>".$array_sisal[$key['nome']]['qty']."</td><td>".($array_cooper[$key['nome']]['qty']+$array_sisal[$key['nome']]['qty']+$key['qty'])."</td></tr>";
		echo "<tr><td>IMPONIBILE</td><td> ". $key['totale']."</td><td>".$array_cooper[$key['nome']]['totale']."</td><td>".$array_sisal[$key['nome']]['totale']."</td><td>".$array_totale[$key['nome']]['totale']."</td></tr>";
		if($key['nome']=="Giuseppe Spinelli")
		{
			echo "<tr><td>IVA</td><td> ". $key['totale_iva']."</td><td>".$array_cooper[$key['nome']]['totale_iva']."</td><td>".$array_totale[$key['nome']]['totale+iva']."</td></tr>";
			echo "<tr><td>TOTALE</td><td> ". ($key['totale']+$key['totale_iva'])."</td><td>".($array_cooper[$key['nome']]['totale_iva']+$array_cooper[$key['nome']]['totale'])."</td><td>".(($key['totale']+$key['totale_iva'])+($array_cooper[$key['nome']]['totale_iva']+$array_cooper[$key['nome']]['totale']+$array_sisal[$key['nome']]['totale_iva']+$array_sisal[$key['nome']]['totale']))."</td></tr>";
		}
		echo "</table><br><br>";
		}
	}
}

foreach($array_sisal as $key)
{
	if($key['nome']!="Alessandro Matilli"&&$key['nome']!="Giovanna Matilli")
	{
		if($key['nome']!=$array_cooper[$key['nome']]['nome']&&($array_cooper[$key['nome']]['totale_iva']>0||$key['totale_iva']>0)) {
		echo "<table width=500 border=1><tr><td colspan=4 style='text-align:center;'><b>". $key['nome']."</b></td></tr>";
		echo "<tr><td></td><td>COOPERSYSTEM</td><td>CARTASI</td><td>SISAL</td><td>TOTALE</td></tr>";
		echo "<tr><td>QTY</td><td> ". $key['qty']."</td><td>".$array_cooper[$key['nome']]['qty']."</td><td>".($array_cooper[$key['nome']]['qty']+$key['qty'])."</td></tr>";
		echo "<tr><td>IMPONIBILE</td><td> ". $key['totale']."</td><td>".$array_cooper[$key['nome']]['totale']."</td><td>".$array_totale[$key['nome']]['totale']."</td></tr>";
		if($key['nome']=="Giuseppe Spinelli")
		{
			echo "<tr><td>IVA</td><td> ". $key['totale_iva']."</td><td>".$array_cooper[$key['nome']]['totale_iva']."</td><td>".$array_totale[$key['nome']]['totale+iva']."</td></tr>";
			echo "<tr><td>TOTALE</td><td> ". ($key['totale']+$key['totale_iva'])."</td><td>".($array_cooper[$key['nome']]['totale_iva']+$array_cooper[$key['nome']]['totale'])."</td><td>".(($key['totale']+$key['totale_iva'])+($array_cooper[$key['nome']]['totale_iva']+$array_sisal[$key['nome']]['totale']))."</td></tr>";
		}
		echo "</table><br><br>";
		}
	}
}
mysqli_select_db($connessione,'dolibarr');

//print_r($array_totale);

foreach ($array_totale as $key) {

	$sql_soc = "SELECT rowid FROM llx_societe WHERE nom = '".$key['nome']."'";
	$result_soc = $connessione->query($sql_soc);
	$obj_soc= mysqli_fetch_object($result_soc);

	$sql_ref = "SELECT MAX(ref) as refe FROM llx_facture_fourn WHERE substr(ref,1,6)='SI".date('y').$mese_ok."'";
	//echo $sql_ref."<br>";
	$result_ref = $connessione->query($sql_ref);
	$obj_ref= mysqli_fetch_object($result_ref);
	$new_ref= substr($obj_ref->refe,strlen($obj_ref->refe)-4,strlen($obj_ref->refe));
	$new_ref= str_pad($new_ref+1, 4, "0", STR_PAD_LEFT);
	$new_ref = "SI".date('y').$mese_ok."-".$new_ref;
	$total_ttc = $key['iva']+$key['totale'];
	//echo 'NUMERO REF:'.$new_ref."<br>";
	//echo 'ID:'.$obj_soc->rowid."<br>";
	$date_lim = $mese = date('Y-m-d',strtotime($data_controllo . ' +1 month'));
	if($key['totale']>0)
	{
		$sql_facture="INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,entity,datec,datef,paye,tva,total,total_ht,total_tva,total_ttc,fk_statut,fk_user_valid,fk_cond_reglement,date_lim_reglement)
				VALUES('".$new_ref."','".$mese_ok."-".$anno."','".$obj_soc->rowid."','1','".$data_fattura."','".$data_fattura_2."','0','0.00000000','0.00000000','".$key['totale']."',
				'".$key['iva']."','".$total_ttc."','1','1','1926','".$date_lim."')";
				//CREAZIONE FATTURA PDF
		if(isset($_POST['genera_pdf'])) $result_det = $connessione->query($sql_facture);

	//echo $sql_facture."<br><br>";

	$sql_id = "SELECT MAX(rowid) as id FROM llx_facture_fourn";
	$result_id = $connessione->query($sql_id);
	$obj_id= mysqli_fetch_object($result_id);
	$max_id= $obj_id->id;

	$oggetto = "Fattura Passiva ".$key['nome']." - ".$data_fattura;
	$sql_facture="INSERT INTO llx_facture_extrafields (fk_object,oggetto_fatt)
                VALUES('".$max_id."','".$oggetto."')";
    $result_facture = $connessione->query($sql_facture);

	$cartasi = $array_cartasi[$key['nome']]['totale']+$array_cartasi[$key['nome']]['iva'];

	$coopersystem = $array_cooper[$key['nome']]['totale']+$array_cooper[$key['nome']]['iva'];

	if($coopersystem!=0){
	$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
	("'.$max_id.'","SERVIZI PER COOPERSYSTEM","'.$array_cooper[$key['nome']]['totale'].'","'.$coopersystem.'","1","0.000000","'.$array_cooper[$key['nome']]['totale'].'","'.$array_cooper[$key['nome']]['iva'].'","'.$coopersystem.'")';
	}
	//echo $sql_insert_det."<br><br>";
	if(isset($_POST['genera_pdf'])) $result_det = $connessione->query($sql_insert_det);

	if($cartasi!=0){
	$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
	("'.$max_id.'","SERVIZI PER CARTASI","'.$array_cartasi[$key['nome']]['totale'].'","'.$cartasi.'","1","0.000000","'.$array_cartasi[$key['nome']]['totale'].'","'.$array_cartasi[$key['nome']]['iva'].'","'.$cartasi.'")';
	}
	//CREAZONE FATTURA PDF
	if(isset($_POST['genera_pdf'])) $result_det = $connessione->query($sql_insert_det);
	//echo $sql_insert_det."<br><br>";
}
}

//FATTURA CARTASI

$array_cartasi_per = array();
$array_coopersystem = array();
$array_totale = array();
mysqli_select_db($connessione,'fd_ticket');

echo "<br><b>FATTURA ATTIVA CARTASI</b><br>";

$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot,a.costo_ext, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext >=0
AND b.status_id IN (2,3)
AND b.topic_id IN (17) GROUP BY costo_ext";
$result_fact=$connessione->query($sql_fact);

while($obj_fact= mysqli_fetch_object($result_fact)){



echo "<br><table border='1'><tr><td><b>R</b> - POS INTERVENTI MAN ONSITE: ".$obj_fact->tot."</td></tr>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "<tr><td>TOTALE EURO: ".$obj_fact->tot."</td></tr>";
echo "<tr><td>PREZZO UNITARIO:".$obj_fact->costo_ext."</td></tr>";

mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "NEXI PAYMENTS SPA"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot;

//echo "IVA:".$vat."<br>";
//echo "TOTALE:".$tot."<br>";
echo "<tr><td>QTY:".$obj_fact->tot_tck."</td></tr></table>";
//echo "PREZZO TOT CARTASI:".$tot_qty_cartasi."<br>";
$tot_qty_cartasi += $obj_fact->tot_tck;
if($obj_fact->tot_tck>0){
$array_cartasi_per['POS INTERVENTI MAN ONSITE']['iva'] = $vat;
$array_cartasi_per['POS INTERVENTI MAN ONSITE']['totale_iva'] = $tot;
$array_cartasi_per['POS INTERVENTI MAN ONSITE']['qty'] = $obj_fact->tot_tck;
$array_cartasi_per['POS INTERVENTI MAN ONSITE']['pu'] = $obj_fact->costo_ext;
$array_totale['CARTASI']['totale'] += $tot;
}
$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","POS INTERVENTI MAN ONSITE","'.$obj_fact->tot.'","'.$tot.'","'.$obj_fact->tot_tck.'","0.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');
//echo "_________________________________________________<br>";
}

$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot,a.costo_ext, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext >=0
AND b.status_id IN (2,3)
AND b.topic_id IN (12) GROUP BY costo_ext";
$result_fact=$connessione->query($sql_fact);



while($obj_fact= mysqli_fetch_object($result_fact)){



echo "<br><table border='1'><tr><td><b>D</b> - POS DISINTALLAZIONI: ".$obj_fact->tot."</td></tr>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "<tr><td>TOTALE EURO: ".$obj_fact->tot."</td></tr>";
echo "<tr><td>PREZZO UNITARIO:".$obj_fact->costo_ext."</td></tr>";
//echo "PREZZO TOT CARTASI:".$tot_qty_cartasi."<br>";
mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "NEXI PAYMENTS SPA"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot;

//echo "IVA:".$vat."<br>";
//echo "TOTALE:".$tot."<br>";
echo "<tr><td>QTY:".$obj_fact->tot_tck."</td></tr></table>";
//echo "PREZZO TOT CARTASI:".$tot_qty_cartasi."<br>";
$tot_qty_cartasi += $obj_fact->tot_tck;
if($obj_fact->tot_tck>0){
$array_cartasi_per['POS DISINSTALLAZIONI']['iva'] = $vat;
$array_cartasi_per['POS DISINSTALLAZIONI']['totale_iva'] = $tot;
$array_cartasi_per['POS DISINSTALLAZIONI']['qty'] = $obj_fact->tot_tck;
$array_cartasi_per['POS DISINSTALLAZIONI']['pu'] = $obj_fact->costo_ext;
$array_totale['CARTASI']['totale'] += $tot;
}
$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","POS DISINSTALLAZIONI","'.$obj_fact->tot.'","'.$tot.'","'.$obj_fact->tot_tck.'","0.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');

//echo "_________________________________________________<br>";
}



$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot,a.costo_ext, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext >=0
AND b.status_id IN (2,3)
AND b.topic_id IN (13) GROUP BY costo_ext";
$result_fact=$connessione->query($sql_fact);



while($obj_fact= mysqli_fetch_object($result_fact)){



echo "<br><table border='1'><tr><td><b>I</b> - POS INSTALLAZIONE: ".$obj_fact->tot."</td></tr>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "<tr><td>TOTALE EURO: ".$obj_fact->tot."</td></tr>";
echo "<tr><td>PREZZO UNITARIO:".$obj_fact->costo_ext."</td></tr>";

mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "NEXI PAYMENTS SPA"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot;

//echo "IVA:".$vat."<br>";
//echo "TOTALE:".$tot."<br>";
echo "<tr><td>QTY:".$obj_fact->tot_tck."</td></tr></table>";
//echo "PREZZO TOT CARTASI:".$tot_qty_cartasi."<br>";
$tot_qty_cartasi += $obj_fact->tot_tck;
if($obj_fact->tot_tck>0){
$array_cartasi_per['POS INSTALLAZIONE']['iva'] = $vat;
$array_cartasi_per['POS INSTALLAZIONE']['totale_iva'] = $tot;
$array_cartasi_per['POS INSTALLAZIONE']['qty'] = $obj_fact->tot_tck;
$array_cartasi_per['POS INSTALLAZIONE']['pu'] = $obj_fact->costo_ext;
$array_totale['CARTASI']['totale'] += $tot;
}
$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","POS INSTALLAZIONE","'.$obj_fact->tot.'","'.$tot.'","'.$obj_fact->tot_tck.'","0.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');

//echo "_________________________________________________<br>";
}

$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot,a.costo_ext, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext >=0
AND b.status_id IN (2,3)
AND b.topic_id IN (14) GROUP BY costo_ext";
$result_fact=$connessione->query($sql_fact);



while($obj_fact= mysqli_fetch_object($result_fact)){



echo "<br><table border='1'><tr><td><b>S</b> - POS SOSTITUZIONE: ".$obj_fact->tot."</td></tr>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "<tr><td>TOTALE EURO: ".$obj_fact->tot."</td></tr>";
echo "<tr><td>PREZZO UNITARIO:".$obj_fact->costo_ext."</td></tr>";

mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "NEXI PAYMENTS SPA"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot;

//echo "IVA:".$vat."<br>";
//echo "TOTALE:".$tot."<br>";
echo "<tr><td>QTY:".$obj_fact->tot_tck."</td></tr></table>";
//echo "PREZZO TOT CARTASI:".$tot_qty_cartasi."<br>";
$tot_qty_cartasi += $obj_fact->tot_tck;
if($obj_fact->tot_tck>0){
$array_cartasi_per['POS SOSTITUZIONE']['iva'] = $vat;
$array_cartasi_per['POS SOSTITUZIONE']['totale_iva'] = $tot;
$array_cartasi_per['POS SOSTITUZIONE']['qty'] = $obj_fact->tot_tck;
$array_cartasi_per['POS SOSTITUZIONE']['pu'] = $obj_fact->costo_ext;
$array_totale['CARTASI']['totale'] += $tot;
}
$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","POS SOSTITUZIONE","'.$obj_fact->tot.'","'.$tot.'","'.$obj_fact->tot_tck.'","0.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');

//echo "_________________________________________________<br>";
}

$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot,a.costo_ext, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext >=0
AND b.status_id IN (2,3)
AND b.topic_id IN (43) GROUP BY costo_ext";
$result_fact=$connessione->query($sql_fact);



while($obj_fact= mysqli_fetch_object($result_fact)){



echo "<br><table border='1'><tr><td><b>O</b> - POS ORDINI DIVERSI: ".$obj_fact->tot."</td></tr>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "<tr><td>TOTALE EURO: ".$obj_fact->tot."</td></tr>";
echo "<tr><td>PREZZO UNITARIO:".$obj_fact->costo_ext."</td></tr>";
//echo "PREZZO TOT CARTASI:".$tot_qty_cartasi."<br>";
mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "NEXI PAYMENTS SPA"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot;

//echo "IVA:".$vat."<br>";
//echo "TOTALE:".$tot."<br>";
echo "<tr><td>QTY:".$obj_fact->tot_tck."</td></tr></table>";
//echo "PREZZO TOT CARTASI:".$tot_qty_cartasi."<br>";
$tot_qty_cartasi += $obj_fact->tot_tck;
if($obj_fact->tot_tck>0){
$array_cartasi_per['POS ORDINI DIVERSI']['iva'] = $vat;
$array_cartasi_per['POS ORDINI DIVERSI']['totale_iva'] = $tot;
$array_cartasi_per['POS ORDINI DIVERSI']['qty'] = $obj_fact->tot_tck;
$array_cartasi_per['POS ORDINI DIVERSI']['pu'] = $obj_fact->costo_ext;
$array_totale['CARTASI']['totale'] += $tot;
}
$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","POS ORDINI DIVERSI","'.$obj_fact->tot.'","'.$tot.'","'.$obj_fact->tot_tck.'","0.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');

//echo "_________________________________________________<br>";
}

$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot,a.costo_ext, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext >=0
AND b.status_id IN (2,3)
AND b.topic_id IN (15) GROUP BY costo_ext";
$result_fact=$connessione->query($sql_fact);



while($obj_fact= mysqli_fetch_object($result_fact)){



echo "<br><table border='1'><tr><td><b>Z</b> - POS UPDGRADE MASSIVO: ".$obj_fact->tot."</td></tr>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "<tr><td>TOTALE EURO: ".$obj_fact->tot."</td></tr>";
echo "<tr><td>PREZZO UNITARIO:".$obj_fact->costo_ext."</td></tr>";

mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "NEXI PAYMENTS SPA"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot;

//echo "IVA:".$vat."<br>";
//echo "TOTALE:".$tot."<br>";
echo "<tr><td>QTY:".$obj_fact->tot_tck."</td></tr></table>";
//echo "PREZZO TOT CARTASI:".$tot_qty_cartasi."<br>";
$tot_qty_cartasi += $obj_fact->tot_tck;
if($obj_fact->tot_tck>0){
$array_cartasi_per['POS UPDGRADE MASSIVO']['iva'] = $vat;
$array_cartasi_per['POS UPDGRADE MASSIVO']['totale_iva'] = $tot;
$array_cartasi_per['POS UPDGRADE MASSIVO']['qty'] = $obj_fact->tot_tck;
$array_cartasi_per['POS UPDGRADE MASSIVO']['pu'] = $obj_fact->costo_ext;
$array_totale['CARTASI']['totale'] += $tot;
}
$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","POS UPDGRADE MASSIVO","'.$obj_fact->tot.'","'.$tot.'","'.$obj_fact->tot_tck.'","0.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');

//echo "_________________________________________________<br>";
}

$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot,a.costo_ext, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext >=0
AND b.status_id IN (2,3)
AND b.topic_id IN (16) GROUP BY costo_ext";
$result_fact=$connessione->query($sql_fact);

while($obj_fact= mysqli_fetch_object($result_fact)){



echo "<br><table border='1'><tr><td><b>F</b> - POS CAMBIO GESTORE: ".$obj_fact->tot."</td></tr>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "<tr><td>TOTALE EURO: ".$obj_fact->tot."</td></tr>";
echo "<tr><td>PREZZO UNITARIO:".$obj_fact->costo_ext."</td></tr>";

mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "CARTASI"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot;

//echo "IVA:".$vat."<br>";
//echo "TOTALE:".$tot."<br>";
echo "<tr><td>QTY:".$obj_fact->tot_tck."</td></tr></table>";
//echo "PREZZO TOT CARTASI:".$tot_qty_cartasi."<br>";
$tot_qty_cartasi += $obj_fact->tot_tck;
if($obj_fact->tot_tck>0){
$array_cartasi_per['POS CAMBIO GESTORE']['iva'] = $vat;
$array_cartasi_per['POS CAMBIO GESTORE']['totale_iva'] = $tot;
$array_cartasi_per['POS CAMBIO GESTORE']['qty'] = $obj_fact->tot_tck;
$array_cartasi_per['POS CAMBIO GESTORE']['pu'] = $obj_fact->costo_ext;
$array_totale['CARTASI']['totale'] += $tot;
}
$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","POS CAMBIO GESTORE","'.$obj_fact->tot.'","'.$tot.'","'.$obj_fact->tot_tck.'","0.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');
//echo "_________________________________________________<br>";
}

//AGGIORNAMENTI ETHERNET

$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot,a.costo_ext, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext >=0
AND b.status_id IN (2,3)
AND b.topic_id IN (39) GROUP BY costo_ext";
$result_fact=$connessione->query($sql_fact);



while($obj_fact= mysqli_fetch_object($result_fact)){



echo "<br><table border='1'><tr><td><b>U</b> - POS AGGIORNAMENTO ETHERNET: ".round($obj_fact->tot,2)."</td></tr>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "<tr><td>TOTALE EURO: ".round($obj_fact->tot,2)."</td></tr>";
echo "<tr><td>PREZZO UNITARIO:".$obj_fact->costo_ext."</td></tr>";

mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "NEXI PAYMENTS SPA"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot;

//echo "IVA:".round($vat,2)."<br>";
//echo "TOTALE:".round($tot,2)."<br>";
echo "<tr><td>QTY:".$obj_fact->tot_tck."</td></tr></table>";
//echo "PREZZO TOT CARTASI:".$tot_qty_cartasi."<br>";
//$tot_qty_cartasi_et = $tot_qty_cartasi + $obj_fact->tot_tck;
$tot_qty_cartasi += $obj_fact->tot_tck;
if($obj_fact->tot_tck>0){
$array_cartasi_per['POS AGGIORNAMENTO ETHERNET']['iva'] = $vat;
$array_cartasi_per['POS AGGIORNAMENTO ETHERNET']['totale_iva'] = $tot;
$array_cartasi_per['POS AGGIORNAMENTO ETHERNET']['qty'] = $obj_fact->tot_tck;
$array_cartasi_per['POS AGGIORNAMENTO ETHERNET']['pu'] = $obj_fact->costo_ext;
$array_totale['CARTASI']['totale'] += $tot;
}
$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","POS AGGIORNAMENTO ETHERNET","'.$obj_fact->tot.'","'.$tot.'","'.$obj_fact->tot_tck.'","22.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');

//echo "_________________________________________________<br>";
}
if(!isset($_POST['uav_nexi'])) $_POST['uav_nexi'] = 0;
if(!isset($_POST['urgenza_nexi'])) $_POST['urgenza_nexi'] = 0;
if(!isset($_POST['differenza_nexi'])) $_POST['differenza_nexi'] = 0;
//$uav = 15*$_POST['uav'];
//$urgenza=5*$_POST['urgenza'];

echo "_________________________________________________<br>";
echo "QUANTITA': ".$tot_qty_cartasi."<br>";
echo "ORDINI PENDING: ".$_POST['differenza_mexi']."<br>";
echo "UAV: ".$_POST['uav_nexi']."<br>";
echo "URGENZA: ".$_POST['urgenza_nexi']."<br>";
echo "TOTALE: ".$array_totale['CARTASI']['totale']."<br>";
echo "IVA: ".(($array_totale['CARTASI']['totale']*22)/100)."<br>";
echo "TOTALE+IVA: ".((($array_totale['CARTASI']['totale']*22)/100)+$array_totale['CARTASI']['totale'])."<br>";




//FATTURA ATTIVA COOPERSYSTEM
echo "<br><b>FATTURA ATTIVA COOPERSYSTEM</b><br>";
//echo '<b>ZONA 1</b><br>';

//MANUTENZIONE
$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot,a.costo_ext, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext >=0
AND b.status_id IN ('2','3')
AND b.topic_id IN ('26','27')
GROUP BY costo_ext";
//echo $sql_fact;
$result_fact=$connessione->query($sql_fact);

//echo $sql_fact;
while($obj_fact= mysqli_fetch_object($result_fact)){

/*
echo "<br>POS INTERVENTI MANUTENZIONI: ".$obj_fact->tot."<br>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "TOTALE EURO: ".$obj_fact->tot."<br>";
echo "PREZZO UNITARIO:".$obj_fact->costo_ext."<br>";
*/
mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "COOPERSYSTEM SC"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot+$vat;

$pu_vat = (24 * 22)/100;
/*
echo "IVA:".$vat."<br>";
echo "TOTALE:".$tot."<br>";
echo "QTY:".$obj_fact->tot_tck."<br>";
*/

//$tot_qty += $obj_fact->tot_tck;

if($obj_fact->costo_ext<10) $obj_fact->cost_ext.= "0".$obj_fact->costo_ext;
if($obj_fact->tot_tck>0){
$array_coopersystem['POS INTERVENTI MANUTENZIONI '.$obj_fact->costo_ext]['iva'] = $vat;
$array_coopersystem['POS INTERVENTI MANUTENZIONI '.$obj_fact->costo_ext]['totale_iva'] = $tot;
$array_coopersystem['POS INTERVENTI MANUTENZIONI '.$obj_fact->costo_ext]['qty'] = $obj_fact->tot_tck;
$array_coopersystem['POS INTERVENTI MANUTENZIONI '.$obj_fact->costo_ext]['pu'] = $obj_fact->costo_ext;
$array_totale['COOPERSYSTEM']['iva'] += $vat;
$array_totale['COOPERSYSTEM']['totale+iva'] += $tot;
}
$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","POS INTERVENTI MANUTENZIONI","24.00000000","'.$pu_vat.'","'.$obj_fact->tot_tck.'","22.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');
echo "<br><table border='1'><tr><td>POS INTERVENTI MANUTENZIONI: ".$obj_fact->tot."</td></tr>";
echo "<tr><td>TOTALE EURO: ".$obj_fact->tot."</td></tr>";
echo "<tr><td>PREZZO UNITARIO:".$obj_fact->costo_ext."</td></tr>";
//echo "IVA:".$vat."<br>";
//echo "TOTALE:".$tot."<br>";
echo "<tr><td>QTY:".$obj_fact->tot_tck."</td></tr></table>";
$tot_qty += $obj_fact->tot_tck;

//echo "_________________________________________________<br>";
}

//INSTALLAZIONI
$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot,a.costo_ext, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext >=0
AND b.status_id IN ('2','3')
AND b.topic_id IN ('18','19','20','21')
GROUP BY costo_ext";
$result_fact=$connessione->query($sql_fact);


while($obj_fact= mysqli_fetch_object($result_fact)){

/*
echo "<br>POS INTERVENTI INSTALLAZIONI: ".$obj_fact->tot."<br>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "TOTALE EURO: ".$obj_fact->tot."<br>";
echo "PREZZO UNITARIO:".$obj_fact->costo_ext."<br>";
*/
mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "COOPERSYSTEM SC"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot+$vat;

$pu_vat = (12 * 22)/100;
/*
echo "IVA:".$vat."<br>";
echo "TOTALE:".$tot."<br>";
echo "QTY:".$obj_fact->tot_tck."<br>";
*/
if($obj_fact->costo_ext<10) $obj_fact->cost_ext.= "0".$obj_fact->costo_ext;
if($obj_fact->tot_tck>0){
$array_coopersystem['POS INTERVENTI INSTALLAZIONI  '.$obj_fact->costo_ext]['iva'] = $vat;
$array_coopersystem['POS INTERVENTI INSTALLAZIONI  '.$obj_fact->costo_ext]['totale_iva'] = $tot;
$array_coopersystem['POS INTERVENTI INSTALLAZIONI  '.$obj_fact->costo_ext]['qty'] = $obj_fact->tot_tck;
$array_coopersystem['POS INTERVENTI INSTALLAZIONI  '.$obj_fact->costo_ext]['pu'] = $obj_fact->costo_ext;
$array_totale['COOPERSYSTEM']['iva'] += $vat;
$array_totale['COOPERSYSTEM']['totale+iva'] += $tot;
}
$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","POS INTERVENTI INSTALLAZIONI","12.00000000","'.$pu_vat.'","'.$obj_fact->tot_tck.'","22.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');
echo "<br><table border='1'><tr><td>POS INTERVENTI INSTALLAZIONI: ".$obj_fact->tot."</td></tr>";
echo "<tr><td>TOTALE EURO: ".$obj_fact->tot."</td></tr>";
echo "<tr><td>PREZZO UNITARIO:".$obj_fact->costo_ext."</td></tr>";
//echo "IVA:".$vat."<br>";
//echo "TOTALE:".$tot."<br>";
echo "<tr><td>QTY:".$obj_fact->tot_tck."</td></tr></table>";
$tot_qty += $obj_fact->tot_tck;
//echo "_________________________________________________<br>";
}


$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot,a.costo_ext, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext >=0
AND b.status_id IN ('2','3')
AND b.topic_id IN ('22','23')
GROUP BY costo_ext";
$result_fact=$connessione->query($sql_fact);


while($obj_fact= mysqli_fetch_object($result_fact)){

/*
echo "<br>POS INTERVENTI SOSTITUZIONI: ".$obj_fact->tot."<br>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "TOTALE EURO: ".$obj_fact->tot."<br>";
echo "PREZZO UNITARIO:".$obj_fact->costo_ext."<br>";
*/
mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "COOPERSYSTEM SC"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot+$vat;

$pu_vat = (24 * 22)/100;
/*
echo "IVA:".$vat."<br>";
echo "TOTALE:".$tot."<br>";
echo "QTY:".$obj_fact->tot_tck."<br>";
*/
if($obj_fact->costo_ext<10) $obj_fact->cost_ext.= "0".$obj_fact->costo_ext;
if($obj_fact->tot_tck>0){
$array_coopersystem['POS INTERVENTI SOSTITUZIONI '.$obj_fact->costo_ext]['iva'] = $vat;
$array_coopersystem['POS INTERVENTI SOSTITUZIONI '.$obj_fact->costo_ext]['totale_iva'] = $tot;
$array_coopersystem['POS INTERVENTI SOSTITUZIONI '.$obj_fact->costo_ext]['qty'] = $obj_fact->tot_tck;
$array_coopersystem['POS INTERVENTI SOSTITUZIONI '.$obj_fact->costo_ext]['pu'] = $obj_fact->costo_ext;
$array_totale['COOPERSYSTEM']['iva'] += $vat;
$array_totale['COOPERSYSTEM']['totale+iva'] += $tot;
}
$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","POS INTERVENTI SOSTITUZIONI","24.00000000","'.$pu_vat.'","'.$obj_fact->tot_tck.'","22.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');
echo "<br><table border='1'><tr><td>POS INTERVENTI SOSTITUZIONI: ".$obj_fact->tot."</td></tr>";
echo "<tr><td>TOTALE EURO: ".$obj_fact->tot."</td></tr>";
echo "<tr><td>PREZZO UNITARIO:".$obj_fact->costo_ext."</td></tr>";
//echo "IVA:".$vat."<br>";
//echo "TOTALE:".$tot."<br>";
echo "<tr><td>QTY:".$obj_fact->tot_tck."</td></tr></table>";
$tot_qty += $obj_fact->tot_tck;
//echo "_________________________________________________<br>";
}




$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot,a.costo_ext, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext >=0
AND b.status_id IN ('2','3')
AND b.topic_id IN ('36')
GROUP BY costo_ext";
$result_fact=$connessione->query($sql_fact);


while($obj_fact= mysqli_fetch_object($result_fact)){

/*
echo "<br>POS INTERVENTI MIGRAZIONE: ".$obj_fact->tot."<br>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "TOTALE EURO: ".$obj_fact->tot."<br>";
echo "PREZZO UNITARIO:".$obj_fact->costo_ext."<br>";
*/
mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "COOPERSYSTEM SC"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot+$vat;

$pu_vat = (19 * 22)/100;
/*echo "IVA:".$vat."<br>";
echo "TOTALE:".$tot."<br>";
echo "QTY:".$obj_fact->tot_tck."<br>";*/
if($obj_fact->costo_ext<10) $obj_fact->cost_ext.= "0".$obj_fact->costo_ext;

if($obj_fact->tot_tck>0){
$array_coopersystem['POS INTERVENTI MIGRAZIONE '.$obj_fact->costo_ext]['iva'] = $vat;
$array_coopersystem['POS INTERVENTI MIGRAZIONE '.$obj_fact->costo_ext]['totale_iva'] = $tot;
$array_coopersystem['POS INTERVENTI MIGRAZIONE '.$obj_fact->costo_ext]['qty'] = $obj_fact->tot_tck;
$array_coopersystem['POS INTERVENTI MIGRAZIONE '.$obj_fact->costo_ext]['pu'] = $obj_fact->costo_ext;
$array_totale['COOPERSYSTEM']['iva'] += $vat;
$array_totale['COOPERSYSTEM']['totale+iva'] += $tot;
}
$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","POS INTERVENTI MIGRAZIONE","19.00000000","'.$pu_vat.'","'.$obj_fact->tot_tck.'","22.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');

echo "<br><table border='1'><tr><td>POS INTERVENTI MIGRAZIONE: ".$obj_fact->tot."</td></tr>";
echo "<tr><td>TOTALE EURO: ".$obj_fact->tot."</td></tr>";
echo "<tr><td>PREZZO UNITARIO:".$obj_fact->costo_ext."</td></tr>";
//echo "IVA:".$vat."<br>";
//echo "TOTALE:".$tot."<br>";
echo "<tr><td>QTY:".$obj_fact->tot_tck."</td></tr></table>";
$tot_qty += $obj_fact->tot_tck;
//echo "_________________________________________________<br>";
}


$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot,a.costo_ext, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext >=0
AND b.status_id IN ('2','3')
AND b.topic_id IN ('38')
GROUP BY costo_ext";

//echo $sql_fact;
$result_fact=$connessione->query($sql_fact);


while($obj_fact= mysqli_fetch_object($result_fact)){

/*
echo "<br>POS INTERVENTI MIGRAZIONE: ".$obj_fact->tot."<br>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "TOTALE EURO: ".$obj_fact->tot."<br>";
echo "PREZZO UNITARIO:".$obj_fact->costo_ext."<br>";
*/
mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "COOPERSYSTEM SC"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot+$vat;

$pu_vat = (19 * 22)/100;
/*echo "IVA:".$vat."<br>";
echo "TOTALE:".$tot."<br>";
echo "QTY:".$obj_fact->tot_tck."<br>";*/
if($obj_fact->costo_ext<10) $obj_fact->cost_ext.= "0".$obj_fact->costo_ext;

if($obj_fact->tot_tck>0){
$array_coopersystem['POS INTERVENTI SOSTITUZIONI MASSIVE '.$obj_fact->costo_ext]['iva'] = $vat;
$array_coopersystem['POS INTERVENTI SOSTITUZIONI MASSIVE '.$obj_fact->costo_ext]['totale_iva'] = $tot;
$array_coopersystem['POS INTERVENTI SOSTITUZIONI MASSIVE '.$obj_fact->costo_ext]['qty'] = $obj_fact->tot_tck;
$array_coopersystem['POS INTERVENTI SOSTITUZIONI MASSIVE '.$obj_fact->costo_ext]['pu'] = $obj_fact->costo_ext;
$array_totale['COOPERSYSTEM']['iva'] += $vat;
$array_totale['COOPERSYSTEM']['totale+iva'] += $tot;
}
$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","POS INTERVENTI SOSTITUZIONI MASSIVE","19.00000000","'.$pu_vat.'","'.$obj_fact->tot_tck.'","22.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');
echo "<br><table border='1'><tr><td>POS INTERVENTI SOSTITUZIONI MASSIVE: ".$obj_fact->tot."</td></tr>";
echo "<tr><td>TOTALE EURO: ".$obj_fact->tot."</td></tr>";
echo "<tr><td>PREZZO UNITARIO:".$obj_fact->costo_ext."</td></tr>";
//echo "IVA:".$vat."<br>";
//echo "TOTALE:".$tot."<br>";
echo "<tr><td>QTY:".$obj_fact->tot_tck."</td></tr></table>";
$tot_qty += $obj_fact->tot_tck;
//echo "_________________________________________________<br>";
}

$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot,a.costo_ext, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext >=0
AND b.status_id IN ('2','3')
AND b.topic_id IN ('24','25')
group by costo_ext";
//echo $sql_fact;
$result_fact=$connessione->query($sql_fact);

//1619820000 1622498400
while($obj_fact= mysqli_fetch_object($result_fact)){

/*
echo "<br>POS INTERVENTI DISINSTALLAZIONE: ".$obj_fact->tot."<br>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "TOTALE EURO: ".$obj_fact->tot."<br>";
echo "PREZZO UNITARIO:".$obj_fact->costo_ext."<br>";
*/
mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "COOPERSYSTEM SC"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot+$vat;

$pu_vat = (24 * 22)/100;
/*
echo "IVA:".$vat."<br>";
echo "TOTALE:".$tot."<br>";
echo "QTY:".$obj_fact->tot_tck."<br>";
*/
if($obj_fact->costo_ext<10) $obj_fact->cost_ext.= "0".$obj_fact->costo_ext;

if($obj_fact->tot_tck>0){
	$array_coopersystem['POS INTERVENTI DISINSTALLAZIONI '.$obj_fact->costo_ext]['iva'] = $vat;
	$array_coopersystem['POS INTERVENTI DISINSTALLAZIONI '.$obj_fact->costo_ext]['totale_iva'] = $tot;
	$array_coopersystem['POS INTERVENTI DISINSTALLAZIONI '.$obj_fact->costo_ext]['qty'] = $obj_fact->tot_tck;
	$array_coopersystem['POS INTERVENTI DISINSTALLAZIONI '.$obj_fact->costo_ext]['pu'] = $obj_fact->costo_ext;
	$array_totale['COOPERSYSTEM']['iva'] += $vat;
	$array_totale['COOPERSYSTEM']['totale+iva'] += $tot;
	}

$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","POS INTERVENTI DISINSTALLAZIONI","24.00000000","'.$pu_vat.'","'.$obj_fact->tot_tck.'","22.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');
echo "<br><table border='1'><tr><td>POS INTERVENTI DISINSTALLAZIONI: ".$obj_fact->tot."</td></tr>";
echo "<tr><td>TOTALE EURO: ".$obj_fact->tot."</td></tr>";
echo "<tr><td>PREZZO UNITARIO:".$obj_fact->costo_ext."</td></tr>";
//echo "IVA:".$vat."<br>";
//echo "TOTALE:".$tot."<br>";
echo "<tr><td>QTY:".$obj_fact->tot_tck."</td></tr></table>";
$tot_qty += $obj_fact->tot_tck;
echo "_________________________________________________<br>";
}



$due_percento = (($array_totale['COOPERSYSTEM']['totale+iva']-$array_totale['COOPERSYSTEM']['iva']) * 2)/100;
$iva_duepercento = (($due_percento*22)/100);
echo "QUANTITA': ".$tot_qty."<br>";
echo "ORDINI PENDING: ".$_POST['differenza_coop']."<br>";
echo "UAV: ".$_POST['uav_coop']."<br>";
echo "URGENZA: ".$_POST['urgenza_coop']."<br>";
echo "TOTALE: ".($array_totale['COOPERSYSTEM']['totale+iva']-$array_totale['COOPERSYSTEM']['iva'])."<br>";
echo "2%:".$due_percento."<br>";
echo "IVA:".($array_totale['COOPERSYSTEM']['iva']+$iva_duepercento)."<br>";
echo "TOTALE+IVA:".($array_totale['COOPERSYSTEM']['totale+iva']+$due_percento+$iva_duepercento)."<br>";
//echo '<b>ZONA 2</b><br>';

/*
//MANUTENZIONE
$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot, a.costo_ext,a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext = 27
AND b.topic_id IN ('26','27')";
$result_fact=$connessione->query($sql_fact);


while($obj_fact= mysqli_fetch_object($result_fact)){


echo "<br>POS INTERVENTI MANUTENZIONI: ".$obj_fact->tot."<br>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "TOTALE EURO: ".$obj_fact->tot."<br>";
echo "PREZZO UNITARIO:".$obj_fact->costo_ext."<br>";
mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "COOPERSYSTEM"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot+$vat;

$pu_vat = (27 * 22)/100;
echo "IVA:".$vat."<br>";
echo "TOTALE:".$tot."<br>";
echo "QTY:".$obj_fact->tot_tck."<br>";

if($obj_fact->tot_tck>0){
	$array_coopersystem['POS INTERVENTI DISINSTALLAZIONI ZONA 2']['iva'] = $vat;
	$array_coopersystem['POS INTERVENTI DISINSTALLAZIONI ZONA 2']['totale_iva'] = $tot;
	$array_coopersystem['POS INTERVENTI DISINSTALLAZIONI ZONA 2']['qty'] = $obj_fact->tot_tck;
	$array_coopersystem['POS INTERVENTI DISINSTALLAZIONI ZONA 2']['pu'] = $obj_fact->costo_ext;
	$array_totale['COOPERSYSTEM']['iva'] += $vat;
	$array_totale['COOPERSYSTEM']['totale+iva'] += $tot;
	}

$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","POS INTERVENTI MANUTENZIONI","27.00000000","'.$pu_vat.'","'.$obj_fact->tot_tck.'","22.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');
echo "_________________________________________________<br>";
}
*/

//FATTURA ATTIVA SISAL
echo "<br><b>FATTURA ATTIVA SISAL</b><br>";
//RCH

$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot,a.costo_ext, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext >=0
AND b.status_id IN (2,3)
AND b.topic_id IN (41) GROUP BY costo_ext";
$result_fact=$connessione->query($sql_fact);



while($obj_fact= mysqli_fetch_object($result_fact)){



echo "<br><table border='1'><tr><td>RCH: ".round($obj_fact->tot,2)."</td></tr>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "<tr><td>TOTALE EURO: ".round($obj_fact->tot,2)."</td></tr>";
echo "<tr><td>PREZZO UNITARIO:".$obj_fact->costo_ext."</td></tr>";

mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "SISAL"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot;

//echo "IVA:".round($vat,2)."<br>";
//echo "TOTALE:".round($tot,2)."<br>";
echo "<tr><td>QTY:".$obj_fact->tot_tck."</td></tr></table>";
//echo "PREZZO TOT CARTASI:".$tot_qty_cartasi."<br>";
//$tot_qty_cartasi_et = $tot_qty_cartasi + $obj_fact->tot_tck;
$tot_qty_sisal += $obj_fact->tot_tck;
if($obj_fact->tot_tck>0){
$array_sisal_per['SISAL']['iva'] = $vat;
$array_sisal_per['SISAL']['totale_iva'] = $tot;
$array_sisal_per['SISAL']['qty'] = $obj_fact->tot_tck;
$array_sisal_per['SISAL']['pu'] = $obj_fact->costo_ext;
$array_totale['SISAL']['totale'] += $tot;
}
$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","RCH","'.$obj_fact->tot.'","'.$tot.'","'.$obj_fact->tot_tck.'","22.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
//mysqli_select_db($connessione,'fd_ticket');

//echo "_________________________________________________<br>";
}

//RCH

$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot,a.costo_ext, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext >=0
AND b.status_id IN (2,3)
AND b.topic_id IN (42) GROUP BY costo_ext";
$result_fact=$connessione->query($sql_fact);



while($obj_fact= mysqli_fetch_object($result_fact)){



echo "<br><table border='1'><tr><td>SWAP: ".round($obj_fact->tot,2)."</td></tr>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "<tr><td>TOTALE EURO: ".round($obj_fact->tot,2)."</td></tr>";
echo "<tr><td>PREZZO UNITARIO:".$obj_fact->costo_ext."</td></tr>";

mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "SISAL"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot;

//echo "IVA:".round($vat,2)."<br>";
//echo "TOTALE:".round($tot,2)."<br>";
echo "<tr><td>QTY:".$obj_fact->tot_tck."</td></tr></table>";
//echo "PREZZO TOT CARTASI:".$tot_qty_cartasi."<br>";
//$tot_qty_cartasi_et = $tot_qty_cartasi + $obj_fact->tot_tck;
$tot_qty_sisal += $obj_fact->tot_tck;
if($obj_fact->tot_tck>0){
$array_sisal_per['SISAL']['iva'] = $vat;
$array_sisal_per['SISAL']['totale_iva'] = $tot;
$array_sisal_per['SISAL']['qty'] = $obj_fact->tot_tck;
$array_sisal_per['SISAL']['pu'] = $obj_fact->costo_ext;
$array_totale['SISAL']['totale'] += $tot;
}
$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","SWAP","'.$obj_fact->tot.'","'.$tot.'","'.$obj_fact->tot_tck.'","22.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
//mysqli_select_db($connessione,'fd_ticket');

//echo "_________________________________________________<br>";
}
if(!isset($_POST['uav_sisal'])) $_POST['uav_sisal'] = 0;
if(!isset($_POST['urgenza_sisal'])) $_POST['urgenza_sisal'] = 0;
if(!isset($_POST['differenza_sisal'])) $_POST['differenza_sisal'] = 0;

echo "_________________________________________________<br>";
echo "QUANTITA': ".$tot_qty_sisal."<br>";
echo "ORDINI PENDING: ".$_POST['differenza_sisal']."<br>";
echo "UAV: ".$_POST['uav_sisal']."<br>";
echo "URGENZA: ".$_POST['urgenza_sisal']."<br>";
echo "TOTALE: ".$array_totale['SISAL']['totale']."<br>";
echo "IVA: ".(($array_totale['SISAL']['totale']*22)/100)."<br>";
echo "TOTALE+IVA: ".((($array_totale['SISAL']['totale']*22)/100)+$array_totale['SISAL']['totale'])."<br>";


/*
//INSTALLAZIONI 24
$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot,a.costo_ext, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext = 27
AND b.topic_id IN ('18','19','20','21')";
$result_fact=$connessione->query($sql_fact);


while($obj_fact= mysqli_fetch_object($result_fact)){


echo "<br>POS INTERVENTI INSTALLAZIONI: ".$obj_fact->tot."<br>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "TOTALE EURO: ".$obj_fact->tot."<br>";
echo "PREZZO UNITARIO:".$obj_fact->costo_ext."<br>";
mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "COOPERSYSTEM"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot+$vat;

$pu_vat = (27 * 22)/100;
echo "IVA:".$vat."<br>";
echo "TOTALE:".$tot."<br>";
echo "QTY:".$obj_fact->tot_tck."<br>";

if($obj_fact->tot_tck>0){
	$array_coopersystem['POS INTERVENTI INSTALLAZIONI ZONA 2']['iva'] = $vat;
	$array_coopersystem['POS INTERVENTI INSTALLAZIONI ZONA 2']['totale_iva'] = $tot;
	$array_coopersystem['POS INTERVENTI INSTALLAZIONI ZONA 2']['qty'] = $obj_fact->tot_tck;
	$array_coopersystem['POS INTERVENTI INSTALLAZIONI ZONA 2']['pu'] = $obj_fact->costo_ext;
	$array_totale['COOPERSYSTEM']['iva'] += $vat;
	$array_totale['COOPERSYSTEM']['totale+iva'] += $tot;
	}

$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","POS INTERVENTI INSTALLAZIONI","27.00000000","'.$pu_vat.'","'.$obj_fact->tot_tck.'","22.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');
echo "_________________________________________________<br>";
}

//INSTALLAZIONI 12
$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot,a.costo_ext, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext = 13.5
AND b.topic_id IN ('18','19','20','21')";
$result_fact=$connessione->query($sql_fact);


while($obj_fact= mysqli_fetch_object($result_fact)){


echo "<br>POS INTERVENTI INSTALLAZIONI: ".$obj_fact->tot."<br>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "TOTALE EURO: ".$obj_fact->tot."<br>";
echo "PREZZO UNITARIO:".$obj_fact->costo_ext."<br>";
mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "COOPERSYSTEM"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot+$vat;

$pu_vat = (13.5 * 22)/100;
echo "IVA:".$vat."<br>";
echo "TOTALE:".$tot."<br>";
echo "QTY:".$obj_fact->tot_tck."<br>";

if($obj_fact->tot_tck>0){
$array_coopersystem['POS INTERVENTI INSTALLAZIONI_2 ZONA 2']['iva'] = $vat;
$array_coopersystem['POS INTERVENTI INSTALLAZIONI_2 ZONA 2']['totale_iva'] = $tot;
$array_coopersystem['POS INTERVENTI INSTALLAZIONI_2 ZONA 2']['qty'] = $obj_fact->tot_tck;
$array_coopersystem['POS INTERVENTI INSTALLAZIONI_2 ZONA 2']['pu'] = $obj_fact->costo_ext;
$array_totale['COOPERSYSTEM']['iva'] += $vat;
$array_totale['COOPERSYSTEM']['totale+iva'] += $tot;
}

$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","POS INTERVENTI INSTALLAZIONI","13.50000000","'.$pu_vat.'","'.$obj_fact->tot_tck.'","22.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');
echo "_________________________________________________<br>";
}


$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot,a.costo_ext, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext = 27
AND b.topic_id IN ('22','23')";
$result_fact=$connessione->query($sql_fact);


while($obj_fact= mysqli_fetch_object($result_fact)){


echo "<br>POS INTERVENTI SOSTITUZIONI: ".$obj_fact->tot."<br>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "TOTALE EURO: ".$obj_fact->tot."<br>";
echo "PREZZO UNITARIO:".$obj_fact->costo_ext."<br>";
mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "COOPERSYSTEM"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot+$vat;

$pu_vat = (27 * 22)/100;
echo "IVA:".$vat."<br>";
echo "TOTALE:".$tot."<br>";
echo "QTY:".$obj_fact->tot_tck."<br>";

if($obj_fact->tot_tck>0){
	$array_coopersystem['POS INTERVENTI SOSTITUZIONI ZONA 2']['iva'] = $vat;
	$array_coopersystem['POS INTERVENTI SOSTITUZIONI ZONA 2']['totale_iva'] = $tot;
	$array_coopersystem['POS INTERVENTI SOSTITUZIONI ZONA 2']['qty'] = $obj_fact->tot_tck;
	$array_coopersystem['POS INTERVENTI SOSTITUZIONI ZONA 2']['pu'] = $obj_fact->costo_ext;
	$array_totale['COOPERSYSTEM']['iva'] += $vat;
	$array_totale['COOPERSYSTEM']['totale+iva'] += $tot;
	}

$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","POS INTERVENTI SOSTITUZIONI","27.00000000","'.$pu_vat.'","'.$obj_fact->tot_tck.'","22.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');
echo "_________________________________________________<br>";
}


$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot,a.costo_ext, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext = 13.5
AND b.topic_id IN ('22','23')";
$result_fact=$connessione->query($sql_fact);


while($obj_fact= mysqli_fetch_object($result_fact)){


echo "<br>POS INTERVENTI SOSTITUZIONI: ".$obj_fact->tot."<br>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "TOTALE EURO: ".$obj_fact->tot."<br>";
echo "PREZZO UNITARIO:".$obj_fact->costo_ext."<br>";
mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "COOPERSYSTEM"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot+$vat;

$pu_vat = (13.5 * 22)/100;
echo "IVA:".$vat."<br>";
echo "TOTALE:".$tot."<br>";
echo "QTY:".$obj_fact->tot_tck."<br>";

if($obj_fact->tot_tck>0){
	$array_coopersystem['POS INTERVENTI SOSTITUZIONI_2 ZONA 2']['iva'] = $vat;
	$array_coopersystem['POS INTERVENTI SOSTITUZIONI_2 ZONA 2']['totale_iva'] = $tot;
	$array_coopersystem['POS INTERVENTI SOSTITUZIONI_2 ZONA 2']['qty'] = $obj_fact->tot_tck;
	$array_coopersystem['POS INTERVENTI SOSTITUZIONI_2 ZONA 2']['pu'] = $obj_fact->costo_ext;
	$array_totale['COOPERSYSTEM']['iva'] += $vat;
	$array_totale['COOPERSYSTEM']['totale+iva'] += $tot;
	}

$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","POS INTERVENTI SOSTITUZIONI","13.50000000","'.$pu_vat.'","'.$obj_fact->tot_tck.'","22.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');
echo "_________________________________________________<br>";
}

$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot,a.costo_ext, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext = 17
AND b.topic_id IN ('36')";
$result_fact=$connessione->query($sql_fact);


while($obj_fact= mysqli_fetch_object($result_fact)){


echo "<br>POS INTERVENTI MIGRAZIONE: ".$obj_fact->tot."<br>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "TOTALE EURO: ".$obj_fact->tot."<br>";
echo "PREZZO UNITARIO:".$obj_fact->costo_ext."<br>";
mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "COOPERSYSTEM"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot+$vat;

$pu_vat = (17 * 22)/100;
echo "IVA:".$vat."<br>";
echo "TOTALE:".$tot."<br>";
echo "QTY:".$obj_fact->tot_tck."<br>";

if($obj_fact->tot_tck>0){
	$array_coopersystem['POS INTERVENTI MIGRAZIONI ZONA 2']['iva'] = $vat;
	$array_coopersystem['POS INTERVENTI MIGRAZIONI ZONA 2']['totale_iva'] = $tot;
	$array_coopersystem['POS INTERVENTI MIGRAZIONI ZONA 2']['qty'] = $obj_fact->tot_tck;
	$array_coopersystem['POS INTERVENTI MIGRAZIONI ZONA 2']['pu'] = $obj_fact->costo_ext;
	$array_totale['COOPERSYSTEM']['iva'] += $vat;
	$array_totale['COOPERSYSTEM']['totale+iva'] += $tot;
	}

$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","POS INTERVENTI MIGRAZIONI","17.00000000","'.$pu_vat.'","'.$obj_fact->tot_tck.'","22.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');
echo "_________________________________________________<br>";
}


$sql_fact = "SELECT COUNT(a.ticket_id) as tot_tck,SUM( a.costo_ext ) AS tot, a.comm_id, b.user_id
FROM ost_ticket AS b, ost_ticket__cdata AS a
WHERE a.zz_dt_clmghw >=  ".$data." AND a.zz_dt_clmghw <  ".$data1."
AND a.ticket_id = b.ticket_id
AND a.costo_ext = 13
AND b.topic_id IN ('24','25')";
$result_fact=$connessione->query($sql_fact);


while($obj_fact= mysqli_fetch_object($result_fact)){


echo "<br>POS INTERVENTI DISINSTALLAZIONE: ".$obj_fact->tot."<br>";
//echo "COMMESSA: ".$obj_fact->comm_id."<br>";
//echo "TOTALE TICKET: ".mysqli_num_rows($result_fact)."<br>";
echo "TOTALE EURO: ".$obj_fact->tot."<br>";
echo "PREZZO UNITARIO:".$obj_fact->costo_ext."<br>";
mysqli_select_db($connessione,'dolibarr');
$sql_soc='SELECT rowid FROM llx_societe WHERE nom = "COOPERSYSTEM"';
$result_soc=$connessione->query($sql_soc);
$obj_soc= mysqli_fetch_object($result_soc);

$societe= $obj_soc->rowid;

$sql_soc='SELECT rowid FROM llx_facture_fourn WHERE fk_soc="'.$societe.'" ORDER BY rowid DESC';
$result_soc=$connessione->query($sql_soc);
$obj_num= mysqli_fetch_object($result_soc);

$num= $obj_num->rowid;

//echo "FATTURA:".$ref."<br>";

$vat= ($obj_fact->tot * 22)/100;
$tot = $obj_fact->tot+$vat;

$pu_vat = (13 * 22)/100;
echo "IVA:".$vat."<br>";
echo "TOTALE:".$tot."<br>";
echo "QTY:".$obj_fact->tot_tck."<br>";

if($obj_fact->tot_tck>0){
	$array_coopersystem['POS INTERVENTI DISINSTALLAZIONI ZONA 2']['iva'] = $vat;
	$array_coopersystem['POS INTERVENTI DISINSTALLAZIONI ZONA 2']['totale_iva'] = $tot;
	$array_coopersystem['POS INTERVENTI DISINSTALLAZIONI ZONA 2']['qty'] = $obj_fact->tot_tck;
	$array_coopersystem['POS INTERVENTI DISINSTALLAZIONI ZONA 2']['pu'] = $obj_fact->costo_ext;
	$array_totale['COOPERSYSTEM']['iva'] += $vat;
	$array_totale['COOPERSYSTEM']['totale+iva'] += $tot;
	}

$sql_insert_det='INSERT INTO llx_facture_fourn_det (fk_facture_fourn,description,pu_ht,pu_ttc,qty,tva_tx,total_ht,tva,total_ttc) VALUES
("'.$num.'","POS INTERVENTI DISINSTALLAZIONI","13.00000000","'.$pu_vat.'","'.$obj_fact->tot_tck.'","22.000000","'.$obj_fact->tot.'","'.$vat.'","'.$tot.'")';
//$sql_insert='INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,datec,datef,total_ht,total_tva,total_ttc,fk_statut) VALUES ';
//$result_insert=$connessione->query($sql_insert);
mysqli_select_db($connessione,'fd_ticket');
echo "_________________________________________________<br>";
}
*/
/*echo "<br><br>";
print_r($array_cartasi);
echo "<br><br>";
print_r($array_coopersystem);
echo "<br><br>";
print_r($array_totale);
echo "<br><br>";*/

mysqli_select_db($connessione,'dolibarr');

	$sql_soc = "SELECT rowid FROM llx_societe WHERE nom = 'NEXI PAYMENTS SPA'";
	$result_soc = $connessione->query($sql_soc);
	$obj_soc= mysqli_fetch_object($result_soc);

	//echo $sql_soc."<br>";

	$sql_ref = "SELECT MAX(facnumber) as refe FROM llx_facture WHERE substr(facnumber,1,6)='FA".date('y').$mese_ok."'";
	//echo $sql_ref."<br>";

	$result_ref = $connessione->query($sql_ref);
	$obj_ref= mysqli_fetch_object($result_ref);
	if($obj_ref->refe!=""){
		$new_ref= substr($obj_ref->refe,strlen($obj_ref->refe)-4,strlen($obj_ref->refe));
		$new_ref= str_pad($new_ref+1, 4, "0", STR_PAD_LEFT);
		$new_ref = "FA".date('y').$mese_ok."-".$new_ref;
		}
		else $new_ref= "FA".date('y').$mese_ok."-0001";
	$totale = $array_totale['CARTASI']['totale'];
	$vat = ($totale*22)/100;
	$total_ttc = $totale+$vat;
	//echo 'NUMERO REF:'.$new_ref."<br>";
	//echo 'ID:'.$obj_soc->rowid."<br>";
	$date_lim = $mese = date('Y-m-d',strtotime($data_controllo . ' +1 month'));


	$sql_facture="INSERT INTO llx_facture (facnumber,fk_soc,entity,datec,datef,paye,tva,total,total_ttc,fk_statut,fk_user_valid,fk_cond_reglement,date_lim_reglement)
				VALUES('".$new_ref."','".$obj_soc->rowid."','1','".$data_fattura."','".$data_fattura_2."','0',
				'".$vat."','".$totale."','".$total_ttc."','1','1','1926','".$date_lim."')";
	//CREAZONE FATTURA PDF
	if(isset($_POST['genera_pdf'])) $result_facture = $connessione->query($sql_facture);
	//echo $sql_facture."<br><br>";

	$sql_id = "SELECT MAX(rowid) as id FROM llx_facture";
	$result_id = $connessione->query($sql_id);
	$obj_id= mysqli_fetch_object($result_id);
	$max_id= $obj_id->id;
	$oggetto = "Fattura Attiva CARTASI - ".$data_fattura;
	$sql_facture="INSERT INTO llx_facture_extrafields (fk_object,oggetto_fatt)
                VALUES('".$max_id."','".$oggetto."')";
    $result_facture = $connessione->query($sql_facture);

	foreach($array_cartasi as $key=>$value){

	$vat = ($value['totale_iva'] * 22)/100;
	$tot= $value['totale_iva'] + $vat;

	$val = $value['pu'];
	$pu_vat = ($tot * 22)/100;

	$sql_insert_det='INSERT INTO llx_facturedet (fk_facture,label,description,tva_tx,subprice,qty,total_ht,total_tva,total_ttc) VALUES
	("'.$max_id.'","'.$key.'","'.$key.'","0.000","'.$value['totale_iva'].'","1","'.$value['totale_iva'].'","'.$vat.'","'.$tot.'")';
	//CREAZONE FATTURA PDF
	if(isset($_POST['genera_pdf'])) $result_det = $connessione->query($sql_insert_det);

		//echo $sql_insert_det."<br><br>";

	}




	$sql_soc = "SELECT rowid FROM llx_societe WHERE nom = 'COOPERSYSTEM SC'";
	$result_soc = $connessione->query($sql_soc);
	$obj_soc= mysqli_fetch_object($result_soc);

	//echo $sql_soc."<br>";

	$sql_ref = "SELECT MAX(facnumber) as refe FROM llx_facture WHERE substr(facnumber,1,6)='FA".date('y').$mese_ok."'";
	//echo $sql_ref."<br>";

	$result_ref = $connessione->query($sql_ref);
	$obj_ref= mysqli_fetch_object($result_ref);

	if($obj_ref->refe!=""){
	$new_ref= substr($obj_ref->refe,strlen($obj_ref->refe)-4,strlen($obj_ref->refe));
	$new_ref= str_pad($new_ref+1, 4, "0", STR_PAD_LEFT);
	$new_ref = "FA".date('y').$mese_ok."-".$new_ref;
	}
	else $new_ref= "FA".date('y').$mese_ok."-0001";
	//$total_ttc = $array_totale['CARTASI']['iva']+$array_totale['CARTASI']['totale'];
	//echo 'NUMERO REF:'.$new_ref."<br>";
	//echo 'ID:'.$obj_soc->rowid."<br>";
	$date_lim = $mese = date('Y-m-d',strtotime($data_controllo . ' +1 month'));
	$diff= $array_totale['COOPERSYSTEM']['totale+iva'] - $array_totale['COOPERSYSTEM']['iva'];
	$due_percento = ($diff * 2) / 100;
	$tot = $diff+$due_percento;

	$sql_facture="INSERT INTO llx_facture (facnumber,fk_soc,entity,datec,datef,paye,tva,total,total_ttc,fk_statut,fk_user_valid,fk_cond_reglement,date_lim_reglement)
				VALUES('".$new_ref."','".$obj_soc->rowid."','1','".$data_fattura."','".$data_fattura_2."','0',
				'".$array_totale['COOPERSYSTEM']['iva']."','".$tot."','".($due_percento+$array_totale['COOPERSYSTEM']['totale+iva'])."','1','1','1926','".$date_lim."')";
	//CREAZONE FATTURA PDF
	if(isset($_POST['genera_pdf'])) $result_facture = $connessione->query($sql_facture);
	//echo $sql_facture."<br><br>";

	$sql_id = "SELECT MAX(rowid) as id FROM llx_facture";
	$result_id = $connessione->query($sql_id);
	$obj_id= mysqli_fetch_object($result_id);
	if($obj_id->id!="") $max_id= $obj_id->id;
	else $max_id = 1;

	$oggetto = "Fattura Attiva COOPERSYSTEM - ".$data_fattura;
	$sql_facture="INSERT INTO llx_facture_extrafields (fk_object,oggetto_fatt)
                VALUES('".$max_id."','".$oggetto."')";
    $result_facture = $connessione->query($sql_facture);

	foreach($array_coopersystem as $key=>$value){


	$tot= $value['totale_iva'] - $value['iva'];
	$pu_vat = ($tot * 22)/100;
	$prezzo_unitario = $tot / $value['qty'];
	$vat = $value['iva'];
	$sql_insert_det='INSERT INTO llx_facturedet (fk_facture,label,description,tva_tx,subprice,qty,total_ht,total_tva,total_ttc) VALUES
	("'.$max_id.'","'.substr($key,0,strlen($key)-3).'","'.substr($key,0,strlen($key)-3).'","0.000","'.$prezzo_unitario.'","'.$value['qty'].'","'.$tot.'","'.$vat.'","'.$value['totale_iva'].'")';
	//CREAZONE FATTURA PDF
	if(isset($_POST['genera_pdf'])) $result_det = $connessione->query($sql_insert_det);

		//echo $sql_insert_det."<br><br>";

	}
	mysqli_select_db($connessione,'fd_ticket');
	$sql='SELECT staff_id, firstname, lastname FROM ost_staff';
	$result=$connessione->query($sql);
	//var_dump($array_totale);
	while($obj= mysqli_fetch_object($result)){
	$nome = $obj->firstname ." ".$obj->lastname;
	//echo $nome."<br>";
	$totale_finale_cooper += $array_cooper[$nome]['totale'];
	$totale_finale_cartasi += $array_cartasi[$nome]['totale'];
	$totale_finale_sisal += $array_sisal[$nome]['totale'];
	}
	$tot_ricavo = $array_totale['CARTASI']['totale'] + ($due_percento+$array_totale['COOPERSYSTEM']['totale+iva']-$array_totale['COOPERSYSTEM']['iva'])+ ($array_totale['SISAL']['totale+iva']-$array_totale['SISAL']['iva']);
	$tot_pagamenti = ($totale_finale_cartasi+$totale_finale_cooper+$due_percento+$totale_finale_sisal);
	echo "<br><h1>TOTALE FINALE</h1>";
	echo '<table width="100%" border="1">';
	echo '<tr><td>CARTASI: '.$totale_finale_cartasi.'</td>';
	echo '<td>COOPER: '.$totale_finale_cooper.'</td>';
	echo '<td>SISAL: '.$totale_finale_sisal.'</td>';
	echo'<td></td></tr>';
	echo '<tr><td colspan="4">TOTALE: '.($totale_finale_cartasi+$totale_finale_cooper+$totale_finale_sisal).'</td></tr>';
	echo '<tr><td>'.$tot_qty_cartasi_et.'</td><td>'.$tot_qty.'</td><td></td><td>'.($tot_qty_cartasi_et+$tot_qty).'</td></tr>';
	echo '<tr><td>'.$array_totale['CARTASI']['totale'].'</td><td>'.($due_percento+$array_totale['COOPERSYSTEM']['totale+iva']-$array_totale['COOPERSYSTEM']['iva']).'</td><td></td><td>RICAVO</td></tr>';
	echo '<tr><td colspan="2">'.$tot_ricavo.'</td><td>'.$tot_pagamenti.'</td><td>'.($tot_ricavo-$tot_pagamenti).'</td></tr>';
	echo '</table>';

}

?>
</div>
