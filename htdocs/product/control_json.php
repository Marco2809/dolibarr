<?php

for($i=2000;$i<4000;$i++)
{
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://www.italyformovies.it/api/v1/locations/".$i,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache",
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {

  echo "cURL Error #:" . $err;

} else {
  if (json_last_error() === JSON_ERROR_NONE) {
    //echo "JSON VALIDO:" .$i."<br>";
} else { echo "JSON NON VALIDO:" .$i."<br>";}

if(strstr($response,"&nbsp;")||strstr($response,"<a ")) echo "JSON CON HTML:" .$i."<br>";

}
}
?>