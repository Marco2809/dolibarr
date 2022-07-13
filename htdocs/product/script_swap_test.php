<?php 

header("Access-Control-Allow-Origin: *");
/*echo ini_get('display_errors');

if (!ini_get('display_errors')) {
    ini_set('display_errors', '0');
}

echo ini_get('display_errors');*/
$host = "localhost";
// username dell'utente in connessione
$user = "admin";
// password dell'utente
$password = "Iniziale1!?";
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
        if($m=="") continue;
        //if(strstr($m,"COD")) continue;
        $m = str_replace("'","",$m);
        $m = trim($m);
        mail("marco.salmi89@gmail.com","PROVA SWAP",$m);
        if(strstr($m,"COD"))
        {  
            $sql="SELECT cod_famiglia FROM llx_asset WHERE id_magazzino = '7' AND cod_famiglia LIKE '".$m."%'";
            $result = $connessione->query($sql);
        } else {
            $sql="SELECT matricola FROM llx_asset WHERE matricola LIKE '".$m."%'";
            $result = $connessione->query($sql);
        }
        //echo "NUMERO RISULTATI: ".mysqli_num_rows($result)."<br>";
        if(mysqli_num_rows($result)==0) { 
            $errore .= "_".$m;
        }
    } 
    if($errore=="Errore"){
        foreach($matricole as $m){
            if(strstr($m,"COD")){
                $sql = "SELECT MIN(id) as id FROM llx_asset WHERE cod_famiglia = '".$m."' AND id_magazzino != '8'";
                $result=$connessione->query($sql);
                $obj_prod = mysqli_fetch_object($result);
                $id_min = $obj_prod->id;
                //mail("marco.salmi89@gmail.com","PROVA ID MIN",$id_min);
                $sql='UPDATE llx_asset SET id_magazzino = "8" WHERE id = "'.$id_min.'"';
                //mail("marco.salmi89@gmail.com","OLD SWAP",$sql);
                $result=$connessione->query($sql);
                if($result)
                {
                    $errore .= '';
                } else $errore .= "_".$m;
            } else {
                $sql='UPDATE llx_asset SET id_magazzino = "8" WHERE matricola LIKE "'.$m.'%"';
                $result=$connessione->query($sql);
                if($result)
                {
                    $errore .= '';
                } else $errore .= "_".$m;
            }
        }
    } //else echo $errore;

    if($errore!="Errore") echo $errore;
    else echo '1';
}



?>