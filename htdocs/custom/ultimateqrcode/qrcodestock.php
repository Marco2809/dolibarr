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
 *   \file       htdocs/ultimateqrcode/qrcodestock.php
 *   \brief      Tab for warehouse QR-code
 *   \ingroup    ultimateqrcode
 */

$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/product/stock/class/entrepot.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/stock.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
include_once("./lib/ultimateqrcode.lib.php");
require_once("./includes/phpqrcode/qrlib.php"); 

$id=GETPOST('id','int'); 

$langs->load("products");
$langs->load("stocks");
$langs->load("companies");
$langs->load("ultimateqrcode@ultimateqrcode");

// Security check

if (!$user->rights->ultimateqrcode->read) accessforbidden();


/*
 *	View
 */

$form = new Form($db);
llxHeader('',$langs->trans("UltimateqrcodeQrcode"));

if ($id > 0 || ! empty($ref))
{
	$object = new Entrepot($db);
	$result = $object->fetch($id);


/*
 * Affichage onglets
 */
    if ($conf->notification->enabled) $langs->load("mails");

	$head = stock_prepare_head($object);
	$head = getUrlMenu($head);
	dol_fiche_head($head, 'tabqrcodestock', $langs->trans("Warehouse"),0,'stock');
	
	$png_web_dir = 'temp/';
	$tempDir = dirname(__FILE__).DIRECTORY_SEPARATOR.$png_web_dir; 
	if (!file_exists($tempDir))
        mkdir($tempDir);   

	// we building raw data
	$addressLabel=$langs->trans("Warehouse");
    $codeContents .= 'REF:'.$object->libelle."\n";
    $codeContents .= 'LABEL:'.$object->lieu."\n";
	$codeContents .= 'DESCRIPTION:'.dol_string_nohtmltag($object->description)."\n";
	$codeContents .= 'ADR;TYPE=work;'.
        'LABEL="'.$addressLabel.'":'
        .$object->address.';'
        .$object->town.';'
        .$object->zip.';'
        .$object->country
    ."\n";
	
	$filename = $tempDir.md5($codeContents).'.stock.png';
	// generating
    QRcode::png($codeContents, $filename, QR_ECLEVEL_L, 2); 

	//Test
	/*$version = 0;
	$eccLevel = QR_ECLEVEL_L;
	$encodingHint   = QR_MODE_8;
	$caseSensitive  = false; 	
	$code = new QRcode();
	$result=$code->encodeString($codeContents, $version, $eccLevel, $encodingHint, $caseSensitive);
	echo '<pre>';
    foreach ($code->data as $line) {
        echo bin2hex($line);
        echo '<br/>';
    }
    echo '</pre>'; 	*/
	
	
	print '<table class="border" width="100%">';

    // Ref
    print '<td width="15%">'.$langs->trans("Ref").'</td><td colspan="3">';
    print $form->showrefnav($object,'id', $linkback, 1, 'rowid', 'libelle');
    print '</td>';

	// displaying QRcode
	$htmlqrcode='';
	$rowspan=4;
	$htmlqrcode.='<td rowspan="'.$rowspan.'" style="text-align: center;" width="25%">';
	if ($filename)   $htmlqrcode.='<img src="'.$png_web_dir.basename($filename).'" />';
	$htmlqrcode.='</td>';

    // Label
    print '<tr><td>'.$langs->trans("LocationSummary").'</td><td colspan="3">'.$object->lieu;
	print '</td>';
	print $htmlqrcode;
	print '</tr>';
	
	// Description
    print '<tr><td valign="top">'.$langs->trans("Description").'</td><td colspan="3">'.(dol_textishtml($object->description)?$object->description:dol_nl2br($object->description,1,true)).'</td></tr>';
	
	// Address
	print '<tr><td>'.$langs->trans('Address').'</td><td colspan="3">';
	print $object->address;
	print '</td></tr>';

	// Zip / Town
    print '<tr><td width="25%">'.$langs->trans('Zip').' / '.$langs->trans("Town").'</td><td colspan="3">';
    print $object->zip.($object->zip && $object->town?" / ":"").$object->town;
    print '</td></tr>';

	// Country
	print '<tr><td>'.$langs->trans('Country').'</td><td colspan="3">';
	if (! empty($object->country_code)) 
	{
		$img=picto_from_langcode($object->country_code);
		print ($img?$img.' ':'');
	}
	print $object->country;
	print '</td></tr>';
	
	print '</table>';
	
	dol_fiche_end();
}


/*
 * Boutons actions
 */
function getUrlMenu($head)
{
    for ($i = 0; $i<count($head);$i++)
    {
        $link = $head[$i];
        $elem = $link[0]."&mainmenu=products";
        $head[$i][0] = $elem;
    }
    
    return $head;
    
}

llxFooter();

$db->close();
?>
