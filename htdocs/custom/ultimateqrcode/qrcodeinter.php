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
 *   \file       htdocs/ultimateqrcode/qrcodeinter.php
 *   \brief      Tab for intervention QR-code
 *   \ingroup    ultimateqrcode
 */

$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/fichinter/class/fichinter.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/fichinter.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
include_once("./lib/ultimateqrcode.lib.php");
require_once("./includes/phpqrcode/qrlib.php"); 

$id=GETPOST('id','int'); 
$ref = GETPOST('ref','alpha');
$action	= GETPOST('action','alpha');

$langs->load("companies");
$langs->load("interventions");
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
	$object = new Fichinter($db);
	$ret=$object->fetch($id, $ref);
	if ($ret > 0) $ret=$object->fetch_thirdparty();
	if ($ret < 0) dol_print_error('',$object->error);
	

/*
 * Affichage onglets
 */
    if ($conf->notification->enabled) $langs->load("mails");

	$head = fichinter_prepare_head($object);

	dol_fiche_head($head, 'tabqrcodeinter', $langs->trans("InterventionCard"),0, 'intervention');
	
	$png_web_dir = 'temp/';
	$tempDir = dirname(__FILE__).DIRECTORY_SEPARATOR.$png_web_dir; 
	if (!file_exists($tempDir))
        mkdir($tempDir);  

	// we building raw data
    $codeContents .= 'REF:'.$object->ref."\n";
    $codeContents .= 'LABEL:'.$object->client->name."\n";
	$codeContents .= 'DESCRIPTION:'.dol_string_nohtmltag($object->description)."\n";
	
	$filename = $tempDir.md5($codeContents).'.inter.png';
	// generating
    QRcode::png($codeContents, $filename, QR_ECLEVEL_L, 2); 
	
	print '<table class="border" width="100%">';
	
	$linkback = '<a href="'.DOL_URL_ROOT.'/fichinter/list.php'.(! empty($socid)?'?socid='.$socid:'').'">'.$langs->trans("BackToList").'</a>';

    // Ref
    print '<tr><td width="25%">'.$langs->trans("Ref").'</td><td>';
	print $form->showrefnav($object, 'ref', $linkback, 1, 'ref', 'ref');
	print '</td></tr>';

	// displaying QRcode
	$htmlqrcode='';
	$rowspan=4;
	$htmlqrcode.='<td rowspan="'.$rowspan.'" style="text-align: center;" width="25%">';
	if ($filename)   $htmlqrcode.='<img src="'.$png_web_dir.basename($filename).'" />';
	$htmlqrcode.='</td>';

    // Third party
	print "<tr><td>".$langs->trans("Company").'</td><td colspan="3">'.$object->client->getNomUrl(1);
	print '</td>';
	print $htmlqrcode; 
	print '</tr>';
	
	// Description
    print '<tr><td valign="top">'.$langs->trans("Description").'</td><td colspan="3">'.(dol_textishtml($object->description)?$object->description:dol_nl2br($object->description,1,true)).'</td></tr>';
	
	print '</table>';
	
	dol_fiche_end();
}


/*
 * Boutons actions
 */


llxFooter();

$db->close();
?>
