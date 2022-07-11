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
$db = "dolibarr";

$connessione = new mysqli($host, $user, $password, $db);

/**
 *  \file       htdocs/product/liste.php
 *  \ingroup    produit
 *  \brief      Page to list products and services
 */
require '../main.inc.php';

llxHeader('', $title, $helpurl, '');

?>



<h1>CREAZIONE ASSET</h1>
<form action="" method="POST">
<table>
<tr><td><strong>Magazzino:</strong></td><td><select name="magazzino">
	<option value="6">GLV-Abruzzo</option>
    <option value="7" selected>GLV-Lazio</option>
</select></td></tr>
<tr><td><strong>Prodotto:</strong></td><td><select name="famiglia">
<?php 
    $sql_fact1="SELECT ref,rowid,label FROM llx_product" ;
    $result_fact1=$connessione->query($sql_fact1);
    while($obj_fact1=mysqli_fetch_object($result_fact1)){
        //echo 'ciao';
        if(strstr($obj_fact1->ref,"COD")) echo "<option value='".$obj_fact1->ref."'>".$obj_fact1->ref."</option>";
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

?>

