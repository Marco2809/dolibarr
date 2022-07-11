<?php

header("Access-Control-Allow-Origin: *");


error_reporting(E_ALL);
ini_set('display_errors',1);

$host = "localhost";
// username dell'utente in connessione
$user = "root";
// password dell'utente
$password = "servicetech14";
// nome del database
$db = "fd_ticket";

$db_dol = "dolibarr";

$connessione = new mysqli($host, $user, $password, $db);

$connessione_dol = new mysqli($host, $user, $password, $db_dol);

require '../main.inc.php';

llxHeader('', $title, $helpurl, '');

?>



<h1>CARICAMENTO MAGAZZINO NEXI</h1>
<form action="" method="post" enctype="multipart/form-data">
    <input type="file"
           id="file" name="fileToUpload">
    <input type="submit" name="control" value="Carica">
</form>

<?php
if(isset($_POST['control'])){

    require_once "simplexlsx.class.php";

    $xlsx = new SimpleXLSX($_FILES['fileToUpload']['tmp_name']);

    list($cols,) = $xlsx->dimension();
    $tck = array();
    $tck_insert = "";
    $tck_exist = "";
    foreach( $xlsx->rows() as $k => $r) {
  
        if ($k == 0) continue; // skip first row
        $sql_control="SELECT label FROM llx_product WHERE label= '".$r[13]."'";

        $result=$connessione_dol->query($sql_control);
        if(mysqli_num_rows($result)==0){
            $sql_insert="INSERT INTO llx_product (ref, label, description) VALUES ('".$r[13]."','".$r[13]."','".$r[12]."')";
            $result=$connessione_dol->query($sql_insert);
            //echo $sql_insert;
        }

        $sql_control="SELECT matricola FROM llx_asset WHERE matricola= '".$r[1]."'";
        //echo $sql_control;
        $result=$connessione_dol->query($sql_control);
        if(mysqli_num_rows($result)==0){
            $sql_insert="INSERT INTO llx_asset (cod_famiglia, label, matricola, id_magazzino, stato_fisico, stato_tecnico, rif_bolla, data_spedizione) VALUES ('".$r[13]."','".$r[13]."','".$r[1]."','7','".$r[8]."','".$r[9]."','".$r[22]."','".$r[23]."')";
            $result=$connessione_dol->query($sql_insert);
            $tck_insert .= "Il dispositivo con matricola " . $r[1] . " è stato inserito correttamente! <br>";
            //echo $sql_insert;
        } else {
            $sql_mat="UPDATE llx_asset SET stato_fisico = '".$r[8]."',stato_tecnico = '".$r[9]."'  WHERE matricola = '".$r[1]."'";
            $result_mat=$connessione_dol->query($sql_mat);
            $tck_exist .= "Il dispositivo con matricola " . $r[1] . " è già presente a sistema! <br>";
            //echo $sql_mat;

        }
        //r[1] -> Matricola
        //r[8] -> Stato
        //r[9] -> Condizione
        //r[13] -> Prodotto
    }

}
//echo '</table>';
echo $tck_exist;
echo $tck_insert;
?>
