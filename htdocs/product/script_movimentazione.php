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

    $sql = "SELECT DISTINCT rowid,label FROM llx_entrepot WHERE entity = '1' ORDER BY label ASC";
    $result = $db->query($sql);
    $select_mag_p = "<select name='id_magazzino_p'>";
    $select_mag_p.="<option value=''></option>";
    $selected = " ";
    while ($obj_prod = $db->fetch_object($result))
    {
        if ($id_magazzino_p == $obj_prod->rowid)
            $selected = " selected";
        else
            $selected = " ";

        $select_mag_p.= '<option value=' . $obj_prod->rowid . $selected . '>' . $obj_prod->label . '</option>';
    }
    $select_mag_p .= "</select>";

llxHeader('', $title, $helpurl, '');

print '<div class="fiche">';
print '<div class="tabs" data-type="horizontal" data-role="controlgroup">';
print '<a class="tabTitle">
<img border="0" title="" alt="" src="' . $root . '"/theme/eldy/img/object_product.png">
Cambio Magazzino
</a>';

print '<div class="inline-block tabsElem">
<a id="card"';

if (!isset($_GET['type']) || $_GET['type'] == "new")
    print 'class="tabactive tab inline-block" ';
else
    print 'class="tab inline-block" ';
print ' href="' . $root . '/product/script_movimentazione.php?mainmenu=products&type=new" data-role="button">New</a>
</div>';


print '<div class="inline-block tabsElem">
<a id="card"';
if ($_GET['type'] == "old")
    print 'class="tabactive tab inline-block" ';
else
    print 'class="tab inline-block" ';
print 'href="' . $root . '/product/script_movimentazione.php?mainmenu=products&type=old" data-role="button">Old</a>
</div>';

print '</div>';

if (!isset($_GET['type']) || $_GET['type'] == "new")
{
?>

<h1>CAMBIO MAGAZZINO</h1>
<form action="" method="POST">
<table>
<tr><td><strong>Magazzino Origine:</strong></td><td><?php echo $select_mag_p; ?></td></tr>
<tr><td><strong>Magazzino Destinazione:</strong></td><td><?php echo $select_mag; ?></td></tr>
<tr><td colspan="2"><textarea name="matricole" value="" cols="100" rows="20">
</textarea></td></tr>
<tr><td colspan="2"><input type="submit" name="control" value="Cambia Magazzino"></td></tr>
</table>
	</form>

<?php
if(isset($_POST['control'])){

$matricole = str_replace("-","",$_POST['matricole']);
//echo "La riga di controllo è la seguente:<br><br>".nl2br($matricole);

$matricole=explode("\n",$matricole);
foreach ($matricole as $m){
    if(trim($m)=="") continue;
    $sql="SELECT matricola FROM llx_asset WHERE matricola LIKE '".$m."%'";
    $result = $connessione->query($sql);
    //echo "NUMERO RISULTATI: ".mysqli_num_rows($result)."<br>";
    if(mysqli_num_rows($result)==0) { 
        echo "MATRICOLA NON PRESENTE: ".trim($m)."<br>"; 
        continue;
    } //echo $pt_number.": PRESENTE<br>";
    $sql_fact1="UPDATE llx_asset SET id_magazzino = '".$_POST['id_magazzino']."' WHERE matricola LIKE '".$m."%'" ;
    $result_fact1=$connessione->query($sql_fact1);
    echo "MAGAZZINO MODIFICATO ALLA MATRICOLA: ".trim($m)."<br>";
    //echo $sql_fact1;
}

//echo 'SPLIT:<br><br>';

}
} else if ($_GET['type'] == "old")
{
    
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

$sql = "SELECT DISTINCT rowid,label FROM llx_entrepot WHERE entity = '1' ORDER BY label ASC";
$result = $db->query($sql);
$select_mag_p = "<select name='id_magazzino_p'>";
$select_mag_p.="<option value=''></option>";
$selected = " ";
while ($obj_prod = $db->fetch_object($result))
{
    if ($id_magazzino_p == $obj_prod->rowid)
        $selected = " selected";
    else
        $selected = " ";

    $select_mag_p.= '<option value=' . $obj_prod->rowid . $selected . '>' . $obj_prod->label . '</option>';
}
$select_mag_p .= "</select>";


$sql = "SELECT DISTINCT * FROM llx_product ORDER BY ref ASC";
$result = $db->query($sql);
$select_prod = "<select name='cod_famiglia'>";
$select_prod.="<option value=''></option>";
$selected = " ";
while ($obj_prod = $db->fetch_object($result))
{
   if(strstr($obj_prod->ref,"COD")||strstr($obj_prod->ref,"SA")) $select_prod.= '<option value=' . $obj_prod->ref . '>' . $obj_prod->ref . ' - '.$obj_prod->description.'</option>';
}
$select_prod .= "</select>";
?>



<h1>CAMBIO MAGAZZINO</h1>
<form action="" method="POST">
<table>
<tr><td><strong>Prodotto:</strong></td><td><?php echo $select_prod; ?></td></tr>
<tr><td><strong>Magazzino Origine:</strong></td><td><?php echo $select_mag_p; ?></td></tr>
<tr><td><strong>Magazzino Destinazione:</strong></td><td><?php echo $select_mag; ?></td></tr>
<tr><td><strong>Quantità:</strong></td><td><input type="number" name="tot" value=""></td></tr>
<tr><td colspan="2"><input type="submit" name="control" value="Cambia Magazzino"></td></tr>
</table>
	</form>

<?php
if(isset($_POST['control'])){

    $sql_c = "SELECT COUNT(id) as totale FROM llx_asset WHERE cod_famiglia = '".$_POST['cod_famiglia']."' AND id_magazzino = '".$_POST['id_magazzino_p']."'";
    $result_c = $connessione->query($sql_c);
    $obj_c = mysqli_fetch_object($result_c);
    if($obj_c->totale>=$_POST['tot']) { 
        for ($i=0;$i<$_POST['tot'];$i++){
            $sql="SELECT MIN(id) as id FROM llx_asset WHERE cod_famiglia = '".$_POST['cod_famiglia']."' AND id_magazzino = '".$_POST['id_magazzino_p']."'";
            $result = $connessione->query($sql);
            $obj_res = mysqli_fetch_object($result);
            $sql_fact1="UPDATE llx_asset SET id_magazzino = '".$_POST['id_magazzino']."' WHERE id = '".$obj_res->id."'";
            $result_fact1=$connessione->query($sql_fact1);
            echo "MAGAZZINO MODIFICATO ALL'ASSET: ".trim($obj_res->id)."<br>";
        }
    } else echo "NUMERO DI MATRICOLE NON PRESENTI NEL MAGAZZINO GLV LAZIO";
}
}
?>

