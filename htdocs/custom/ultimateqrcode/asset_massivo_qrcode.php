<?php

$res = 0;
if (!$res && file_exists("../main.inc.php"))
    $res = @include("../main.inc.php");
if (!$res && file_exists("../../main.inc.php"))
    $res = @include("../../main.inc.php");
if (!$res && file_exists("../../../main.inc.php"))
    $res = @include("../../../main.inc.php");
if (!$res)
    die("Include of main fails");

include_once(DOL_DOCUMENT_ROOT . "/core/lib/product.lib.php");
include_once(DOL_DOCUMENT_ROOT . "/core/lib/functions.lib.php");
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
include_once("./lib/ultimateqrcode.lib.php");
require_once("./includes/phpqrcode/qrlib.php");


$query = "SELECT * FROM tmp_assetmassivo";
$res = $db->query($query);
if ($res)
{
    while ($rec = $db->fetch_array($res))
    {
        //$array_tmp_asset[] = $rec['tmp_codeasset'] . " - " . $rec['tmp_etichetta'];
        $qr_codice = isset($_GET['qrcode_codice']) ? $_GET['qrcode_codice'] : ""; // acquistico il codice asset (sotto forma di md5) dalla varibile get
        $qr_codice = $rec['tmp_codeasset'];
        $obj->tmp_codeasset;
        $png_web_dir = 'temp/';
        $tempDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . $png_web_dir;
        $qrcode_codice = md5($qr_codice);

        $filename = $tempDir . $qrcode_codice . '.png';
// generating
        QRcode::png($qr_codice, $filename, QR_ECLEVEL_L, 2);

//$codice_asset =  $_GET['qrcode_codice']; //codice asset
        $id = $qr_codice;
        $qr_codice = md5($qr_codice);
        if (!empty($qr_codice))
        {
            $filename = "temp/" . $qr_codice . ".png"; // vado a trovare il file che mi serve per poter stampare

            print "<table>";
            print "<tr>";
            print '<td>';
            print '<img src=' . '"' . $filename . '"' . 'onclick="window.print()">'; // stampa il qrcode
            print '</td>';
            print '</tr>';
            $separa_progressivo = explode("-", $id);
            print '<tr>';
            print '<td><FONT SIZE=1>';
            print $separa_progressivo[0] . "<br>" . $separa_progressivo[1];
            print '</font>';
            print '</td>';
            print '</tr>';
            print "</table>";
            $rr = '<img src=' . '"' . $filename . '"' . 'onclick="window.print()">';
            $rr = '<img src=' . '"' . $filename . '"' . 'onclick="window.print()">';

            $query_delete = "TRUNCATE tmp_assetmassivo";
            $res_delete = $db->query($query_delete);

            //$path_redirect = DOL_URL_ROOT . "/custom/ultimateqrcode/asset_qrcode.php";
            // print ' <input type="submit"  class="button"  name="popoup_annull" value="' . "Annulla" . '"></center> ';
            //  $redir = "/dolibarr/htdocs/custom/ultimateqrcode/qrcodeasset.php?id=" . $id;
            //echo '<script>opener.location.href=' . '"' . $redir . '"' . ';self.close();</script>';
//$path = "/dolibarr/htdocs/custom/ultimateqrcode/qrcodeasset.php?"."id=".$codice_asset;
            //      print '<META HTTP-EQUIV="Refresh" CONTENT="0; url=' . $path . '">';
//print $r;
        }
    }
}