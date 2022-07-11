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
$db1 = "dolibarr";

$connessione = new mysqli($host, $user, $password, $db);

$connessione1 = new mysqli($host, $user, $password, $db1);


$sql = "SELECT matricola from llx_asset WHERE matricola != '' AND id_magazzino IN (6,7) AND cod_famiglia NOT LIKE '%SIM%'";
$result=$connessione1->query($sql);
while ($obj = mysqli_fetch_object($result))
{
  $sql_t ="SELECT ref_num, zz_desc_op_eff, affected_resource_zz_wam_string2 FROM ost_ticket__cdata WHERE zz_desc_op_eff LIKE '%".trim($obj->matricola)."%' OR affected_resource_zz_wam_string2 LIKE '%".trim($obj->matricola)."%'";
  //echo $sql_t."<br>";
  $result_t=$connessione->query($sql_t);
  $obj_t = mysqli_fetch_object($result_t);
  if($obj_t->ref_num!="") echo "MATRICOLA: ".$obj->matricola . " trovata nel ticket " .$obj_t->ref_num."<br>";

}



?>
