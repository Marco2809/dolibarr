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
 *   \file       htdocs/ultimateqrcode/qrcodeorder.php
 *   \brief      Tab for orders QR-code
 *   \ingroup    ultimateqrcode
 */

$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/fichinter/class/fichinter.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/order.lib.php';
require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
include_once("./lib/ultimateqrcode.lib.php");
require_once("./includes/phpqrcode/qrlib.php"); 

$id=GETPOST('id','int'); 
$ref = GETPOST('ref','alpha');
$action	= GETPOST('action','alpha');

$langs->load("companies");
$langs->load("orders");
$langs->load("ultimateqrcode@ultimateqrcode");

// Security check

if (!$user->rights->ultimateqrcode->read) accessforbidden();


/*
 *	View
 */

$form = new Form($db);
llxHeader('',$langs->trans("UltimateqrcodeQrcode"));

// Load object
if ($id > 0 || ! empty($ref))
{
	$object = new Commande($db);
	$ret=$object->fetch($id, $ref);
	if ($ret > 0) $ret=$object->fetch_thirdparty();
	if ($ret < 0) dol_print_error('',$object->error);
	

/*
 * Affichage onglets
 */

	$head = commande_prepare_head($object);
        $head = getUrlMenu($head);
	dol_fiche_head($head, 'tabqrcodeorder', $langs->trans("CustomerOrder"),0, 'order');
	
	$png_web_dir = 'temp/';
	$tempDir = dirname(__FILE__).DIRECTORY_SEPARATOR.$png_web_dir; 
	if (!file_exists($tempDir))
        mkdir($tempDir);  

	// we building raw data
	// Define $urlwithroot
	$urlwithouturlroot=preg_replace('/'.preg_quote(DOL_URL_ROOT,'/').'$/i','',trim($dolibarr_main_url_root));
	$urlwithroot=$urlwithouturlroot.DOL_URL_ROOT;  // This is to use external domain name found into config file
	//$urlwithroot=DOL_MAIN_URL_ROOT;     // This is to use same domain name than current
	$codeContents=$urlwithroot.'/commande/fiche.php?id='.$object->id;

	$filename = $tempDir.md5($codeContents).'.order.png';
	// generating
    QRcode::png($codeContents, $filename, QR_ECLEVEL_L, 2); 
	
	print '<table class="border" width="100%">';
	
	$linkback = '<a href="'.DOL_URL_ROOT.'/commande/liste.php'.(! empty($socid)?'?socid='.$socid:'').'">'.$langs->trans("BackToList").'</a>';

    // Ref
    print '<tr><td width="25%">'.$langs->trans("Ref").'</td><td colspan="3">';
	print $form->showrefnav($object, 'ref', $linkback, 1, 'ref', 'ref');
	print '</td></tr>';

	// displaying QRcode
	$htmlqrcode='';
	$rowspan=3;
	$htmlqrcode.='<td rowspan="'.$rowspan.'" style="text-align: center;" width="25%">';
	if ($filename)   $htmlqrcode.='<img src="'.$png_web_dir.basename($filename).'" />';
	$htmlqrcode.='</td>';

    // Third party
	print "<tr><td>".$langs->trans("QrcodeOrderUrl").'</td><td colspan="3">'.$codeContents;
	print '</td>';
	print $htmlqrcode; 
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
        $elem = $link[0]."&mainmenu=commercial";
        $head[$i][0] = $elem;
    }
    
    return $head;
    
}

llxFooter();

$db->close();
?>
