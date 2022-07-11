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

$id_movimentazione = GETPOST("id_movimentazione");

if (empty($id_movimentazione)) // se non ha messo un codice movimentazione 
{
    return; // non eseguo nulla
}

$query = "SELECT * FROM " . MAIN_DB_PREFIX . "form_assetmove as fa WHERE fa.codice_mov LIKE '" . $id_movimentazione . "'";
$res = $db->query($query);
if ($res)
{
    $obj = $db->fetch_object($res);
    //$array_tmp_asset[] = $rec['tmp_codeasset'] . " - " . $rec['tmp_etichetta'];

    $codici_asset = $obj->checkbox_asset;
    if (!empty($codici_asset)) // se ci sono codici asset
    {
        $array_codici_asset = explode(",", $codici_asset);
        if (!empty($array_codici_asset))
        {
            for ($i = 0; $i<count($array_codici_asset); $i++)
            {
                $asset = $array_codici_asset[$i];
                $png_web_dir = 'temp/';
                $tempDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . $png_web_dir;
                $qrcode_codice = md5($asset);

                $filename = $tempDir . $qrcode_codice . '.png';
                QRcode::png($asset, $filename, QR_ECLEVEL_L, 2); // generating
                $id = $asset;
                $asset = md5($asset);
                if (!empty($asset))
                {
                    $filename = "temp/" . $asset . ".png"; // vado a trovare il file che mi serve per poter stampare

                    print "<table>";
                    print "<tr>";
                    print '<td>';
                    print '<img src=' . '"' . $filename . '"' . 'onclick="window.print()">'; // stampa il qrcode
                    print '</td>';
                    print '</tr>';
                    $separa_progressivo = explode("-", $id);
                    print "<tr>";
                    print '<td><FONT SIZE=1>';
                    print $separa_progressivo[0] . "<br>" . $separa_progressivo[1];
                    print '</font>';
                    print '</td>';

                    print '</tr>';
                    print "</table>";
                    $rr = '<img src=' . '"' . $filename . '"' . 'onclick="window.print()">';
                    $rr = '<img src=' . '"' . $filename . '"' . 'onclick="window.print()">';
                }
            }
        }
    }
}