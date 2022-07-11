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
 *   \file       htdocs/ultimateqrcode/qrcodethirdparty.php
 *   \brief      Tab for thirdparty QR-code
 *   \ingroup    ultimateqrcode
 */

$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

include_once(DOL_DOCUMENT_ROOT."/core/lib/company.lib.php");
include_once("./lib/ultimateqrcode.lib.php");
require_once("./includes/phpqrcode/qrlib.php"); 


$langs->load("companies");
$langs->load("ultimateqrcode@ultimateqrcode");

// Security check
$socid = GETPOST('socid','int');
if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'societe', $socid);

if (!$user->rights->ultimateqrcode->read) accessforbidden();


/*
 *	View
 */

$form = new Form($db);
llxHeader('',$langs->trans("UltimateqrcodeQrcode"));

if ($socid > 0)
{
    $societe = new Societe($db);
    $societe->fetch($socid);

/*
 * Affichage onglets
 */
    if ($conf->notification->enabled) $langs->load("mails");

	$head = societe_prepare_head($societe);
	
	dol_fiche_head($head, 'tabqrcodethirdparty', $langs->trans("ThirdParty"),0,'company');
	
	$png_web_dir = 'temp/';
	$tempDir = dirname(__FILE__).DIRECTORY_SEPARATOR.$png_web_dir; 
	if (!file_exists($tempDir))
        mkdir($tempDir);  
	
	// we building raw data
    $codeContents  = 'BEGIN:VCARD'."\n";
    $codeContents .= 'FN:'.$societe->name."\n";
    $codeContents .= 'TEL;WORK;VOICE:'.$societe->phone."\n";
	$codeContents .= 'ADR;TYPE=work;'.
        'LABEL="'.$addressLabel.'":'
        .$societe->address.';'
        .$societe->town.';'
        .$societe->zip.';'
        .$societe->country
    ."\n";
	$codeContents .= 'EMAIL:'.$societe->email."\n"; 
    $codeContents .= 'END:VCARD';

	$filename = $tempDir.md5($codeContents).'.VCARD.png';
	// generating
    QRcode::png($codeContents, $filename, QR_ECLEVEL_L, 2); 
	
	print '<table class="border" width="100%">';	
	
    // Name
    print '<tr><td width="25%">'.$langs->trans('ThirdPartyName').'</td>';
    print '<td colspan="3">';
    print $form->showrefnav($societe, 'socid', '', ($user->societe_id?0:1), 'rowid', 'nom');
    print '</td>';
    print '</tr>';
		
	// displaying QRcode
	$htmlqrcode='';
	$rowspan=4;
	$htmlqrcode.='<td rowspan="'.$rowspan.'" style="text-align: center;" width="25%">';
	if ($filename)   $htmlqrcode.='<img src="'.$png_web_dir.basename($filename).'" />';
	$htmlqrcode.='</td>';
	
	// Address
    print "<tr><td valign=\"top\">".$langs->trans('Address').'</td><td colspan="3">';
    dol_print_address($societe->address,'gmap','thirdparty',$societe->id);
    print '</td>';
	print $htmlqrcode; 
	print '</tr>';
	
	// Zip / Town
    print '<tr><td width="25%">'.$langs->trans('Zip').' / '.$langs->trans("Town").'</td><td colspan="3">';
    print $societe->zip.($societe->zip && $societe->town?" / ":"").$societe->town;
    print '</td></tr>';

    // Country
    print '<tr><td>'.$langs->trans("Country").'</td><td colspan="3" class="nowrap">';
    if (! empty($societe->country_code))
    {
        $img=picto_from_langcode($societe->country_code);
        if ($societe->isInEEC()) print $form->textwithpicto(($img?$img.' ':'').$societe->country,$langs->trans("CountryIsInEEC"),1,0);
        else print ($img?$img.' ':'').$societe->country;
    }
    print '</td></tr>';
	
	// EMail
    print '<tr><td>'.$langs->trans('EMail').'</td><td colspan="3">';
    print dol_print_email($societe->email,0,$societe->id,'AC_EMAIL');
    print '</td></tr>';
	
	print '</table>';
	
	dol_fiche_end();
}


/*
 * Boutons actions
 */


llxFooter();

$db->close();
?>
