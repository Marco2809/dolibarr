<?php

header("Access-Control-Allow-Origin: *");
/*echo ini_get('display_errors');

if (!ini_get('display_errors')) {
    ini_set('display_errors', '0');
}

echo ini_get('display_errors');*/
$host = "localhost";
// username dell'utente in connessione
$user = "root";
// password dell'utente
$password = "servicetech14";
// nome del database
$db = "dolibarr";

$connessione = new mysqli($host, $user, $password, $db);

if(isset($_POST['matricola'])&&$_POST['matricola']!="")
{
    /*if(strstr($_POST['matricola'],"COD")){
        echo "1";
        exit();
    } */

    $matricole = explode(",",$_POST['matricola']);
    $errore = "Errore";
    foreach($matricole as $m)
    {
        $tecnico = "no";
        //if(strstr($m,"COD")) continue;
        if(strstr($m,"$")) $tecnico = "ok";
        $m = str_replace("$","",$m);
        //mail("marco.salmi89@gmail.com","PROVA SWAP",$m."-".$tecnico."-".$_POST['propostachiusura']."-".$_POST['tipologia']);
        $m = str_replace("'","",$m);
        $m = trim($m);
        if($m=="") continue;

        //mail("marco.salmi89@gmail.com","PROVA SWAP",$m."-".$_POST['lastname']."-".$tecnico);
        if(strstr($m,"COD"))
        {
            if($_POST['lastname']=="Levino") $sql="SELECT cod_famiglia FROM llx_asset WHERE id_magazzino = '6' AND cod_famiglia LIKE '".$m."%' ";
            else if($tecnico=="ok") $sql="SELECT cod_famiglia FROM llx_asset WHERE id_magazzino = (SELECT rowid FROM llx_entrepot WHERE label LIKE '%".$_POST['lastname']."%') AND cod_famiglia LIKE '".$m."%' ";
            else $sql="SELECT cod_famiglia FROM llx_asset WHERE id_magazzino = '7' AND cod_famiglia LIKE '".$m."%' ";
            $result = $connessione->query($sql);
            if(mysqli_num_rows($result)==0) {

                $errore .= "_".$m;

            }
        } else {
            $sql="SELECT matricola FROM llx_asset WHERE id_magazzino!='8' AND matricola LIKE '".$m."%'";
            $result = $connessione->query($sql);
            if(mysqli_num_rows($result)==0||mysqli_num_rows($result)>1) {

                $errore .= "_".$m;

            }
        }
        //mail("marco.salmi89@gmail.com","SQL",$sql);
        //echo "NUMERO RISULTATI: ".mysqli_num_rows($result)."<br>";

    }
    if($errore=="Errore"){
        foreach($matricole as $m){
            $tecnico = "no";
            //if(strstr($m,"COD")) continue;
            $m = str_replace("'","",$m);
            $m = trim($m);
            if(strstr($m,"$")) $tecnico = "ok";
            $m = str_replace("$","",$m);
            if($m=="") continue;

            if(strstr($m,"COD")){
                if($_POST['lastname']=="Levino") $sql = "SELECT MIN(id) as id FROM llx_asset WHERE cod_famiglia = '".$m."' AND id_magazzino = '6'";
                else if($tecnico=="ok") $sql = "SELECT MIN(id) as id FROM llx_asset WHERE cod_famiglia = '".$m."' AND id_magazzino = (SELECT rowid FROM llx_entrepot WHERE label LIKE '%".$_POST['lastname']."%')";
                else $sql = "SELECT MIN(id) as id FROM llx_asset WHERE cod_famiglia = '".$m."' AND id_magazzino = '7'";
                //mail("marco.salmi89@gmail.com","OLD SWAP",$sql);
                $result=$connessione->query($sql);
                $obj_prod = mysqli_fetch_object($result);
                $id_min = $obj_prod->id;
                //mail("marco.salmi89@gmail.com","PROVA ID MIN",$id_min);
                $sql='UPDATE llx_asset SET id_magazzino = "8",numero_ticket="'.$_POST['number'].'", data_chiusura="'.$_POST['propostachiusura'].'",tipologia_intervento="'.$_POST['tipologia'].'",termid="'.$_POST['termid'].'" WHERE id = "'.$id_min.'"';
                //mail("marco.salmi89@gmail.com","OLD SWAP",$sql);
                $result=$connessione->query($sql);
                if($result)
                {
                    $errore .= '';
                } else $errore .= "_".$m;
            } else {
                $m = ereg_replace("[^A-Za-z0-9 ]", "", $m );
                if($m=="") return;
                $sql='UPDATE llx_asset SET id_magazzino = "8",numero_ticket="'.$_POST['number'].'", data_chiusura="'.$_POST['propostachiusura'].'",tipologia_intervento="'.$_POST['tipologia'].'",termid="'.$_POST['termid'].'" WHERE matricola LIKE "'.$m.'%"';
                //mail("marco.salmi89@gmail.com","SQL",$sql);
                $result=$connessione->query($sql);
                if($result)
                {
                    $errore .= '';
                } else $errore .= "_".$m;
            }
        }
    } //else echo $errore;
    //echo "Errore_matricola";
    if($errore!="Errore") echo $errore;
    else echo '1';
}



?>
