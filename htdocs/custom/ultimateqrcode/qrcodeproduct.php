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

$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

include_once(DOL_DOCUMENT_ROOT."/core/lib/product.lib.php");
include_once(DOL_DOCUMENT_ROOT."/core/lib/functions.lib.php");
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
include_once("./lib/ultimateqrcode.lib.php");
require_once("./includes/phpqrcode/qrlib.php"); 

$id=GETPOST('id','int'); 
$ref = GETPOST('ref','alpha');

$langs->load("products");
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
	$object = new Product($db);
	$object->fetch($id, $ref);


/*
 * Affichage onglets
 */
    if ($conf->notification->enabled) $langs->load("mails");

	$head = product_prepare_head($object, $user);
        $head = getUrlMenu($head);
	$titre=$langs->trans("CardProduct".$object->type);
	$picto = ($object->type==1?'service':'product');
	dol_fiche_head($head, 'tabqrcodeproduct', $titre,0,$picto);
	
	$png_web_dir = 'temp/';
	$tempDir = dirname(__FILE__).DIRECTORY_SEPARATOR.$png_web_dir; 
	if (!file_exists($tempDir))
        mkdir($tempDir);  

	// we building raw data
    $codeContents .= 'REF:'.$object->ref."\n";
    $codeContents .= 'LABEL:'.$object->label."\n";
	$codeContents .= 'DESCRIPTION:'.dol_string_nohtmltag($object->description)."\n";
	$codeContents .= 'PRICE:'.price($object->price_ttc)."\n";
	
	$filename = $tempDir.md5($codeContents).'.product.png';
	// generating
    QRcode::png($codeContents, $filename, QR_ECLEVEL_L, 2); 
	
	print '<table class="border" width="100%">';

    // Ref
    print '<td width="15%">'.$langs->trans("Ref").'</td><td colspan="3">';
    print $form->showrefnav($object,'ref','',1,'ref');
    print '</td>';

	// displaying QRcode
	$htmlqrcode='';
	$rowspan=4;
	$htmlqrcode.='<td rowspan="'.$rowspan.'" style="text-align: center;" width="25%">';
	if ($filename)   $htmlqrcode.='<img src="'.$png_web_dir.basename($filename).'" />';
	$htmlqrcode.='</td>';

    // Label
    print '<tr><td>'.$langs->trans("Label").'</td><td colspan="2">'.$object->libelle;
	print '</td>';
	print $htmlqrcode;
	print '</tr>';
	
	// Description
    print '<tr><td valign="top">'.$langs->trans("Description").'</td><td colspan="2">'.(dol_textishtml($object->description)?$object->description:dol_nl2br($object->description,1,true)).'</td></tr>';
	
	// Price
	print '<tr><td>'.$langs->trans("SellingPrice").'</td><td>';
	if ($object->price_base_type == 'TTC')
	{
		print price($object->price_ttc).' '.$langs->trans($object->price_base_type);
	}
	else
	{
		print price($object->price).' '.$langs->trans($object->price_base_type);
	}
	print '</td></tr>';
	
	print '</table>';
	print '<br><br><br>';
	
	// we building raw data
    $codeContents2 .= dirname($_SERVER['SERVER_PROTOCOL']) . "://" . $_SERVER['HTTP_HOST'] .'/product/fiche.php?id='.$object->id;
	
	$filename2 = $tempDir.md5($codeContents2).'.product_link.png';

	// generating
    QRcode::png($codeContents2, $filename2, QR_ECLEVEL_L, 2); 
	print '<table class="border" width="100%">';
	
	// displaying QRcode
	$htmlqrcode2='';
	$rowspan=4;
	$htmlqrcode2.='<td rowspan="'.$rowspan.'" style="text-align: center;" width="25%">';
	if ($filename)   $htmlqrcode2.='<img src="'.$png_web_dir.basename($filename2).'" />';
	$htmlqrcode2.='</td>';
	
	// Ref
    print '<td width="15%">'.$langs->trans("UrlProductFile").'</td><td colspan="3">';
	print $object->getNomUrl(0);
	print '</td>';
	print $htmlqrcode2;
	print '</tr>';
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
