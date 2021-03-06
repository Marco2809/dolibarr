<?php

/* Copyright (C) 2014 Philippe Grand  <philippe.grand@atoo-net.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 *   \file       htdocs/ultimateqrcode/qrcodeproduct.php
 *   \brief      Tab for product QR-code
 *   \ingroup    ultimateqrcode
 */
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

$id = GETPOST('id', 'string');
$ref = GETPOST('ref', 'alpha');

$langs->load("products");
$langs->load("companies");
$langs->load("ultimateqrcode@ultimateqrcode");

// Security check
//queste istruzioni non permettono l'accesso qrcode degli utenti con privileggi minori
/*if (!$user->rights->ultimateqrcode->read)
    accessforbidden();
*/

/*
 * 	View
 */

$form = new Form($db);
llxHeader('', $langs->trans("UltimateqrcodeQrcode"));

$r = "3";



$root = DOL_URL_ROOT;
print '<div class="fiche">';
print '<div class="tabs" data-type="horizontal" data-role="controlgroup">';
print '<a class="tabTitle">
<img border="0" title="" alt="" src="'.$root.'"/theme/eldy/img/object_product.png">
Dati della famiglia
</a>';
print '<div class="inline-block tabsElem">
<a id="card" class="tab inline-block" href="' . $root . '/product/scheda_asset.php?mainmenu=products&cod_asset=' . $id . '"' . 'data-role="button">Scheda</a>
</div>';

            print '<div class="inline-block tabsElem">
<a id="card" class="tab inline-block" href="' . $root . '/product/tracking_asset.php?mainmenu=products&cod_asset=' . $id . '"' . 'data-role="button">Tracking</a>
</div>';

print '<div class="inline-block tabsElem">
<a id="price" class="tabactive tab inline-block" href="' . $root .'/custom/ultimateqrcode/qrcodeasset.php?mainmenu=products&id=' . $id . '"' . 'data-role="button">Asset QR code</a>
</div>';


print '</div>';


if (!empty($id) || !empty($ref)) {


    require_once DOL_DOCUMENT_ROOT . '/product/myclass/myAsset.php';

    $obj_asset = new asset($db);
    $asset = $obj_asset->getMyAsset($id);
    /*
     * Affichage onglets
     */


    $png_web_dir = 'temp/';
    $tempDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . $png_web_dir;
    if (!file_exists($tempDir))
        mkdir($tempDir);

    // we building raw data
    $codeContents = $asset['cod_asset'];

    $qrcode_codice = md5($codeContents);

    $filename = $tempDir . md5($codeContents) . '.png';
    // generating
    QRcode::png($codeContents, $filename, QR_ECLEVEL_L, 2);

    print '<br>';
    print '<table class="border" width="100%">';

    // Ref
    print '<td width="15%">' . "Codice asset" . '</td><td colspan="3">';

    print $asset['cod_asset'];
    //print $form->showrefnav($object,'ref','',1,'ref');
    print '</td>';

    // displaying QRcode
    $htmlqrcode = '';
    $rowspan = 4;
    $htmlqrcode.='<td rowspan="' . $rowspan . '" style="text-align: center;" width="25%">';
    if ($filename)
        $htmlqrcode.='<img src="' . $png_web_dir . basename($filename) . '" />';
    $htmlqrcode.='</td>';

    // Label
    print '<tr><td>' . $langs->trans("Label") . '</td><td colspan="2">' . $asset['label'];
    print '</td>';
    print $htmlqrcode;
    print '</tr>';



    print '</table>';
    print '<br><br><br>';
    $path_pop = DOL_URL_ROOT."/custom/ultimateqrcode/asset_qrcode.php?qrcode_codice=".$codeContents."";
    print "<script>
function inviaform(){
        var path = '$path_pop';
	window.open(path,'popupname','width=200,height=100,toolbar=yes, location=no,status=yes,menubar=yes,scrollbars=yes');
	document.getElementById('nomeform').submit();
}
</script>";
    

    $path_redirect = DOL_URL_ROOT . "/custom/ultimateqrcode/asset_qrcode.php";
  //  print '<form action=' . $path_redirect . '>';
    $reload = DOL_URL_ROOT . "/custom/ultimateqrcode/qrcodeasset.php?id=".$id;
 print '<form method="post" action="' . $reload . '">';
    print '<center> <input type="submit" name="action" class="button" onclick="inviaform();" value="stampa">';
    print '<input type="hidden" name="qrcode_codice" value="' . $qrcode_codice . '">';
    print '</form>';

    // we building raw data
    $codeContents2 .= dirname($_SERVER['SERVER_PROTOCOL']) . "://" . $_SERVER['HTTP_HOST'] . '/product/fiche.php?id=' . $object->id;

    $filename2 = $tempDir . md5($codeContents2) . '.product_link.png';

    // generating
    // QRcode::png($codeContents2, $filename2, QR_ECLEVEL_L, 2); 

    print '</table>';

    $query = "SELECT mod5_asset FROM " . MAIN_DB_PREFIX . "barcode_table WHERE code_asset LIKE '" . $asset['cod_asset'] . "'";
    $result = $db->query($query);
    $rec = $db->fetch_array($result);

    if (is_null($rec)) {
        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "barcode_table (";
        $sql .= "code_asset";
        $sql .= ", mod5_asset";
        $sql .= ") VALUES (";
        $sql .= "'" . $asset['cod_asset'] . "'";
        $sql .= ", '" . md5($codeContents) . '.png' . "'";
        $sql .= ")";
        $result = $db->query($sql);
    }
    
    dol_fiche_end();
    
}


/*
 * Boutons actions
 */


llxFooter();

function getQrcode() {
    return "ciao";
}

$db->close();
?>
