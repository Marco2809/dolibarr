<?php

$qr_codice = isset($_GET['qrcode_codice']) ? $_GET['qrcode_codice'] : ""; // acquistico il codice asset (sotto forma di md5) dalla varibile get
//$codice_asset =  $_GET['qrcode_codice']; //codice asset
$id = $qr_codice;
$qr_codice = md5($qr_codice);
if (!empty($qr_codice)) {
    $filename = "temp/" . $qr_codice . ".png"; // vado a trovare il file che mi serve per poter stampare

    print "<table>";
    print "<tr>";
    print '<td>';
    print '<img src=' . '"' . $filename . '"' . 'onclick="window.print()">'; // stampa il qrcode
    print '</td>';
     print '</tr>';
    $separa_progressivo = explode ("-",$id);
      print '<tr>';
    print '<td><FONT SIZE=1>';
    print $separa_progressivo[0]."<br>".$separa_progressivo[1];
     print '</font>';
    print '</td>';
   
    
     print '</tr>';
    print "</table>";
    $rr = '<img src=' . '"' . $filename . '"' . 'onclick="window.print()">';
    $rr = '<img src=' . '"' . $filename . '"' . 'onclick="window.print()">';

    $annulla;

    //$path_redirect = DOL_URL_ROOT . "/custom/ultimateqrcode/asset_qrcode.php";
    // print ' <input type="submit"  class="button"  name="popoup_annull" value="' . "Annulla" . '"></center> ';
    //  $redir = "/dolibarr/htdocs/custom/ultimateqrcode/qrcodeasset.php?id=" . $id;
    //echo '<script>opener.location.href=' . '"' . $redir . '"' . ';self.close();</script>';
//$path = "/dolibarr/htdocs/custom/ultimateqrcode/qrcodeasset.php?"."id=".$codice_asset;
    //      print '<META HTTP-EQUIV="Refresh" CONTENT="0; url=' . $path . '">';
//print $r;
}