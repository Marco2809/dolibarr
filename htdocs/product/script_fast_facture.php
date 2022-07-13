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

require '../main.inc.php';

llxHeader('', $title, $helpurl, '');
$errore="";
if(isset($_POST['control'])&&$_POST['control']!="")
{
    $data=explode("\n",$_POST['data_fattura']);
    $oggetto=explode("\n",$_POST['oggetto']);
    $importo=explode("\n",$_POST['importo']);
    if(count($data)==count($oggetto)&&count($oggetto)==count($importo))
    {
        $i=0;
        foreach($data as $d)
        {
            $d = trim($d);
            if($d=="") continue;
            $importo[$i] = str_replace("€","",trim($importo[$i]));
            $importo[$i] = str_replace(",",".",trim($importo[$i]));
            echo "DATA:".trim($d)."<br>Oggetto:".trim($oggetto[$i])."<br>Importo:".trim($importo[$i])."<br><br>";

            if($importo[$i]>0)
            {
                $mese_ok = substr($d,3,2);
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

                $tot = $importo[$i];
                $iva = $tot - ($tot / 1.22);
                $data_fattura = substr($d,6,4)."-".substr($d,3,2)."-".substr($d,0,2)." 00:00:00";
                $data_fattura_2 = substr($d,6,4)."-".substr($d,3,2)."-".substr($d,0,2);
                $date_lim = date('Y-m-d',strtotime($data_fattura_2 . ' +1 month'));

                $sql_facture="INSERT INTO llx_facture (facnumber,fk_soc,entity,datec,datef,paye,tva,total,total_ttc,fk_cond_reglement,date_lim_reglement)
                VALUES ('".$new_ref."','0','1','".$data_fattura."','".$data_fattura_2."','0',
                '".$iva."','".($tot-$iva)."','".($tot)."','1926','".$date_lim."')";
                //CREAZONE FATTURA
                $result_facture = $connessione->query($sql_facture);

                //echo $sql_facture;

                $sql_id = "SELECT MAX(rowid) as id FROM llx_facture";
                $result_id = $connessione->query($sql_id);
                $obj_id= mysqli_fetch_object($result_id);
                $max_id= $obj_id->id;

                $sql_facture="INSERT INTO llx_facture_extrafields (fk_object,oggetto_fatt)
                VALUES ('".$max_id."','".$oggetto[$i]."')";
                $result_facture = $connessione->query($sql_facture);
                //echo "<br>".$sql_facture;

            } else if ($importo[$i] < 0)
            {
                $mese_ok = substr($d,3,2);
                $anno = substr($d,6,4);
                $import = str_replace("-","",$importo[$i]);
                $sql_ref = "SELECT MAX(ref) as refe FROM llx_facture_fourn WHERE substr(ref,1,6)='SI".date('y').$mese_ok."'";
                //echo $sql_ref."<br>";
                $result_ref = $connessione->query($sql_ref);
                $obj_ref= mysqli_fetch_object($result_ref);
                $new_ref= substr($obj_ref->refe,strlen($obj_ref->refe)-4,strlen($obj_ref->refe));
                $new_ref= str_pad($new_ref+1, 4, "0", STR_PAD_LEFT);
                $new_ref = "SI".date('y').$mese_ok."-".$new_ref;

                $tot = $import;
                $iva = $tot - ($tot / 1.22);
                $data_fattura = substr($d,6,4)."-".substr($d,3,2)."-".substr($d,0,2)." 00:00:00";
                $data_fattura_2 = substr($d,6,4)."-".substr($d,3,2)."-".substr($d,0,2);
                $date_lim = date('Y-m-d',strtotime($data_fattura_2 . ' +1 month'));
                $sql_disable = "SET UNIQUE_CHECKS=0";
                $result_disable = $connessione->query($sql_disable);
                $sql_facture="INSERT INTO llx_facture_fourn (ref,ref_supplier,fk_soc,entity,datec,datef,paye,tva,total,total_ht,total_tva,total_ttc,fk_cond_reglement,date_lim_reglement)
				VALUES('".$new_ref."','".$mese_ok."-".$anno."-".$new_ref."','0','1','".$data_fattura."','".$data_fattura_2."','0','0.00000000','0.00000000','".($tot-$iva)."',
                '".$iva."','".$tot."','1926','".$date_lim."')";
                //CREAZONE FATTURA
                $result_facture = $connessione->query($sql_facture);
                echo $sql_facture;
                $sql_enable = "SET UNIQUE_CHECKS=0";
                $result_enable = $connessione->query($sql_enable);

                $sql_id = "SELECT MAX(rowid) as id FROM llx_facture_fourn";
                $result_id = $connessione->query($sql_id);
                $obj_id= mysqli_fetch_object($result_id);
                $max_id= $obj_id->id;

                $sql_facture="INSERT INTO llx_facture_fourn_extrafields (fk_object,fornitore_oggetto)
                VALUES('".$max_id."','".$oggetto[$i]."')";
                $result_facture = $connessione->query($sql_facture);
                echo $sql_facture;
            }
            $i++;
        }
    } else $errore = "Numero di oggetti non corrispondente.";

}

?>



<h1>CONTROLLO TICKET</h1>
<form action="" method="post">
<table>
<tr>
    <td><strong>Data Fattura:</strong></td>
    <td><strong>Oggetto:</strong></td>
    <td><strong>Importo:</strong></td>
</tr>
<tr>
    <td><textarea name="data_fattura" value="<?php echo $_POST['data_fattura']; ?>" cols="20" rows="20"></textarea></td>
    <td><textarea name="oggetto" value="<?php echo $_POST['oggetto']; ?>" cols="50" rows="20"></textarea></td>
    <td><textarea name="importo" value="<?php echo $_POST['importo']; ?>" cols="20" rows="20"></textarea></td>
</tr>
</table>

<input type="submit" name="control" value="Crea Fatture">
	</form>

<?php

?>
