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

require '../main.inc.php';

llxHeader('', $title, $helpurl, '');

$sql = "SELECT DISTINCT rowid,label FROM llx_entrepot WHERE entity = '1' ORDER BY label ASC";
$result = $db->query($sql);
$select_mag = "<select name='id_magazzino'>";
$select_mag.="<option value=''></option>";
$selected = " ";
while ($obj_prod = $db->fetch_object($result))
{
    if ($id_magazzino == $obj_prod->rowid)
        $selected = " selected";
    else
        $selected = " ";

    $select_mag.= '<option value=' . $obj_prod->rowid . $selected . '>' . $obj_prod->label . '</option>';
}
$select_mag .= "</select>";


$sql = "SELECT DISTINCT * FROM llx_product ORDER BY ref ASC";
$result = $db->query($sql);
$select_prod = "<select name='cod_famiglia'>";
$select_prod.="<option value=''></option>";
$selected = " ";
while ($obj_prod = $db->fetch_object($result))
{
   if(strstr($obj_prod->ref,"COD")||strstr($obj_prod->ref,"SA")) $select_prod.= '<option value=' . $obj_prod->ref . '>' . $obj_prod->ref . '</option>';
}
$select_prod .= "</select>";
?>



<h1>CAMBIO MAGAZZINO</h1>
<form action="" method="POST">
<table>
<tr><td><strong>Prodotto:</strong></td><td><?php echo $select_prod; ?></td></tr>
<tr><td><strong>Magazzino Destinazione:</strong></td><td><?php echo $select_mag; ?></td></tr>
<tr><td><strong>Quantità:</strong></td><td><input type="number" name="tot" value=""></td></tr>
<tr><td colspan="2"><input type="submit" name="control" value="Cambia Magazzino"></td></tr>
</table>
	</form>

<?php
if(isset($_POST['control'])){

    $sql_c = "SELECT COUNT(id) as totale FROM llx_asset WHERE cod_famiglia = '".$_POST['cod_famiglia']."' AND id_magazzino = '7'";
    $result_c = $connessione->query($sql_c);
    $obj_c = mysqli_fetch_object($result_c);
    if($obj_c->totale>=$_POST['tot']) { 
        for ($i=0;$i<$_POST['tot'];$i++){
            $sql="SELECT MIN(id) as id FROM llx_asset WHERE cod_famiglia = '".$_POST['cod_famiglia']."' AND id_magazzino = '7'";
            $result = $connessione->query($sql);
            $obj_res = mysqli_fetch_object($result);
            $sql_fact1="UPDATE llx_asset SET id_magazzino = '".$_POST['id_magazzino']."' WHERE id = '".$obj_res->id."'";
            $result_fact1=$connessione->query($sql_fact1);
            echo "MAGAZZINO MODIFICATO ALL'ASSET: ".trim($obj_res->id)."<br>";
        }
    } else echo "NUMERO DI MATRICOLE NON PRESENTI NEL MAGAZZINO GLV LAZIO";
}


?>

