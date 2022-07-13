<?php
header("Access-Control-Allow-Origin: *");

$host = "localhost";
// username dell'utente in connessione
$user = "admin";
// password dell'utente
$password = "Iniziale1!?";
// nome del database
$db = "dolibarr";

$connessione = new mysqli($host, $user, $password, $db);


$sql = "SELECT id, titolo, luoghi FROM oggetto_film WHERE tipologia='itinerario' AND permessi_lettura !='H' AND luoghi != ''";
$result = $connessione->query($sql);
while($obj_id=mysqli_fetch_object($result)){

  echo $obj_id->id." - <b>".$obj_id->titolo."</b><br>";

  $luoghi = strip_tags($obj_id->luoghi,"<h4><img><b>");
  $luoghi = str_replace("<h4></h4>","",$luoghi);
  $luoghi = str_replace("&nbsp;"," ",$luoghi);
  $luoghi = preg_replace("/[\n\r\t]/","",$luoghi);

  $places = explode("<h4>",$luoghi);

  //preg_match_all("(<h4>(.*?)<h4>)", $luoghi , $places);
  //return $luoghi;
  //return print_r($places);
  $p=0;
  foreach($places as $place)
  {
      preg_match_all('(<img src="(.*?)")', $place , $immagini_places[$p]);
      $p++;
  }

  $p=0;
  foreach($immagini_places as $image)
  {
    if(isset($image[1][1])) $image_remove[$p] = $image[1][1];
    $p++;
  }

  print_r($image_remove);
  /*$p = 0;
  foreach($places as $place)
  {
      preg_match('(<img src="(.*?)")', $place[0] , $immagini_places[$p]);
      $p++;
  }*/

  //return print_r($places);

  preg_match_all('(<img src="(.*?)")', $luoghi , $immagini_luoghi);
  $luoghi = strip_tags($luoghi,"<h4>");
  preg_match_all("(<h4>(.*?)</h4>)", $luoghi , $titoli);
  $luoghi = str_replace("</h4>","</h4>$$",$luoghi);
  $luoghi = preg_replace('/<h4[^>]*>([\s\S]*?)<\/h4[^>]*>/', '', $luoghi);
  $luoghi = stripslashes($luoghi);
  //$luoghi = addslashes($luoghi);
  //$luoghi = str_replace(' "', ' \"', $luoghi);
  $luoghi = str_replace("\'","'",$luoghi);
  $testo = explode("$$",$luoghi);
  $luoghi_array = "";
  if(isset($image_remove))
  {
  foreach($image_remove as $remove)
  {
    $pos = array_search($remove, $immagini_luoghi[1]);
    unset($immagini_luoghi[0][$pos]);
    unset($immagini_luoghi[1][$pos]);
    $immagini_luoghi[0] = array_values($immagini_luoghi[0]);
    $immagini_luoghi[1] = array_values($immagini_luoghi[1]);
  }
  }


  //print_r($immagini_luoghi);

  for($i=0;$i<count($immagini_luoghi[0]);$i++)
  {

    if(!isset($titoli[1][$i])) $titoli[1][$i] = "";
    if(!isset($testo[$i+1])) $testo[$i+1]="";
    if(!isset($immagini_luoghi[1][$i])) $immagini_luoghi[1][$i]="";
      $luoghi_array .= '{"titolo":"'.trim($titoli[1][$i]).'","immagine":"'.trim(str_replace("http:","https:",$immagini_luoghi[1][$i])).'","testo":"'.preg_replace("/\.([^ ])/", ". $1", str_replace('"', '\"', stripslashes(str_replace("\'","'",addslashes(trim(preg_replace("/[\n\r]/","",str_replace("\'","'",str_replace("’","'",$testo[$i+1]))))))))).'"}';
      if($i<count($immagini_luoghi[0])-1) $luoghi_array.=",";

      $titolo = trim($titoli[1][$i]);
      $immagine = trim(str_replace("http:","https:",$immagini_luoghi[1][$i]));
      $testo1 = preg_replace("/\.([^ ])/", ". $1", str_replace('"', '\"', stripslashes(str_replace("\'","'",addslashes(trim(preg_replace("/[\n\r]/","",str_replace("\'","'",str_replace("’","'",$testo[$i+1])))))))));


      echo trim($titoli[1][$i])."<br>".trim(str_replace("http:","https:",$immagini_luoghi[1][$i]))."<br>".preg_replace("/\.([^ ])/", ". $1", str_replace('"', '\"', stripslashes(str_replace("\'","'",addslashes(trim(preg_replace("/[\n\r]/","",str_replace("\'","'",str_replace("’","'",$testo[$i+1])))))))))."<br>";

      $testo1 =  str_replace("'","\'",$testo1);
      $testo1 =  str_replace("’","\'",$testo1);
      $sql_in = "INSERT INTO luoghi (id_itinerario, titolo, immagine, testo) VALUES (".$obj_id->id.",'".$titolo."','".$immagine."','".$testo1."')";
      $result_in = $connessione->query($sql_in);
      echo $sql_in."<br><br>";
  }

}

//echo $luoghi_array;

 ?>
