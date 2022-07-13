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
$db = "dolibarr";

$connessione = new mysqli($host, $user, $password, $db);

/**
 *  \file       htdocs/product/liste.php
 *  \ingroup    produit
 *  \brief      Page to list products and services
 */
require '../main.inc.php';

llxHeader('', $title, $helpurl, '');


print '<div class="fiche">';
print '<div class="tabs" data-type="horizontal" data-role="controlgroup">';
print '<a class="tabTitle">
<img border="0" title="" alt="" src="' . $root . '"/theme/eldy/img/object_product.png">
Caricamento Asset
</a>';

print '<div class="inline-block tabsElem">
<a id="card"';

if (!isset($_GET['type']) || $_GET['type'] == "new")
    print 'class="tabactive tab inline-block" ';
else
    print 'class="tab inline-block" ';
print ' href="' . $root . '/product/script_magazzino.php?mainmenu=products&type=new" data-role="button">New</a>
</div>';


print '<div class="inline-block tabsElem">
<a id="card"';
if ($_GET['type'] == "old")
    print 'class="tabactive tab inline-block" ';
else
    print 'class="tab inline-block" ';
print 'href="' . $root . '/product/script_magazzino.php?mainmenu=products&type=old" data-role="button">Old</a>
</div>';

print '</div>';

if (!isset($_GET['type']) || $_GET['type'] == "new")
{

?>



<h1>CREAZIONE ASSET</h1>
<form action="" method="POST">
<table>
<tr><td><strong>Magazzino:</strong></td><td><select name="magazzino">
  <option value=''></option>
	<option value="6">GLV-Abruzzo</option>
    <option value="7" >GLV-Lazio</option>
</select></td></tr>
<tr><td><strong>Prodotto:</strong></td><td><select name="famiglia">
  <option value=''></option>
<?php
    $sql_fact1="SELECT ref,rowid,label FROM llx_product" ;
    $result_fact1=$connessione->query($sql_fact1);

    while($obj_fact1=mysqli_fetch_object($result_fact1)){
        //echo 'ciao';
       if(!strstr($obj_fact1->ref,"COD")) echo "<option value='".$obj_fact1->ref."'>".$obj_fact1->ref."</option>";
    }
?>
</select></td></tr>
<tr><td><strong>Riferimento Bolla:</strong></td><td><input type="text" name="rif_bolla"></td></tr>
<tr><td><strong>Data Spedizione:</strong></td><td><input type="date" name="data_sped" placeholder="dd/mm/YYYY"></td></tr>
<tr><td><strong>Data Ricezione:</strong></td><td><input type="date" name="data_ric" placeholder="dd/mm/YYYY"></td></tr>
<tr><td colspan="2"><textarea name="matricole" value="" cols="100" rows="20">
</textarea></td></tr>
<tr><td colspan="2"><input type="submit" name="control" value="Carica a Magazzino"></td></tr>
</table>
	</form>

<?php
if(isset($_POST['control'])){

$matricole = str_replace("-","",$_POST['matricole']);
//echo "La riga di controllo è la seguente:<br><br>".nl2br($matricole);

$matricole=explode("\n",$matricole);
foreach ($matricole as $m){
    if(trim($m)=="") continue;
    $sql="SELECT matricola FROM llx_asset WHERE matricola LIKE '".trim($m)."%'";
    $result = $connessione->query($sql);
    //echo $sql."<br>";
    //echo "NUMERO RISULTATI: ".mysqli_num_rows($result)."<br>";
    if(mysqli_num_rows($result)>0) {
        echo "MATRICOLA GIA' PRESENTE: ".trim($m)."<br>";
        $sql_fact1="UPDATE llx_asset SET rif_bolla = '".$_POST['rif_bolla']."', data_spedizione = '".$_POST['data_sped']."', data_ricezione = '".$_POST['data_ric']."' WHERE matricola LIKE '".trim($m)."%'";
        $result_fact1=$connessione->query($sql_fact1);
        continue;
    } //echo $pt_number.": PRESENTE<br>";
    $sql_fact1="INSERT INTO llx_asset (cod_famiglia,scorta_tot,label, matricola,id_magazzino,rif_bolla, data_spedizione, data_ricezione) VALUES ('".$_POST['famiglia']."','1','".$_POST['famiglia']."','".$m."','".$_POST['magazzino']."','".$_POST['rif_bolla']."','".$_POST['data_sped']."','".$_POST['data_ric']."')" ;
    $result_fact1=$connessione->query($sql_fact1);
    echo "MATRICOLA INSERITA: ".trim($m)."<br>";
    //echo $sql_fact1;
}

//echo 'SPLIT:<br><br>';

}
} else if ($_GET['type'] == "old")
{
?>
<h1>CREAZIONE ASSET</h1>
<form action="" method="POST">
<table>
<tr><td><strong>Magazzino:</strong></td><td><select name="magazzino">
<option value=''></option>
	<option value="6">GLV-Abruzzo</option>
    <option value="7">GLV-Lazio</option>
</select></td></tr>
<tr><td><strong>Prodotto:</strong></td><td><select name="famiglia">
<option value=''></option>
<?php
    $sql_fact1="SELECT ref,rowid,label,description FROM llx_product" ;
    $result_fact1=$connessione->query($sql_fact1);
    while($obj_fact1=mysqli_fetch_object($result_fact1)){
        //echo 'ciao';
        if(strstr($obj_fact1->ref,"COD")||strstr($obj_fact1->ref,"SA")) echo "<option value='".$obj_fact1->ref."'>".$obj_fact1->ref."-".$obj_fact1->description."</option>";
    }
?>
</select></td></tr>
<tr><td><strong>Quantità:</strong></td><td><input type="number" name="tot"></td></tr>
<tr><td><strong>Riferimento Bolla:</strong></td><td><input type="text" name="rif_bolla"></td></tr>
<tr><td><strong>Data Spedizione:</strong></td><td><input type="date" name="data_sped" placeholder="dd/mm/YYYY"></td></tr>
<tr><td><strong>Data Ricezione:</strong></td><td><input type="date" name="data_ric" placeholder="dd/mm/YYYY"></td></tr>
<tr><td colspan="2"><input type="submit" name="control" value="Carica a Magazzino"></td></tr>
</table>
	</form>

<?php
if(isset($_POST['control'])){

for ($i=0;$i<$_POST['tot'];$i++){
    $sql_fact1="INSERT INTO llx_asset (cod_famiglia,scorta_tot,label, matricola,id_magazzino,rif_bolla, data_spedizione, data_ricezione) VALUES ('".$_POST['famiglia']."','1','".$_POST['famiglia']."','','".$_POST['magazzino']."','".$_POST['rif_bolla']."','".$_POST['data_sped']."','".$_POST['data_ric']."')" ;
    $result_fact1=$connessione->query($sql_fact1);
    echo "MATRICOLA INSERITA: ".$_POST['famiglia']."<br>";
    //echo $sql_fact1;
}

//echo 'SPLIT:<br><br>';

}
}
?>
