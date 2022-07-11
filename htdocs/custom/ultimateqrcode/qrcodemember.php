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
 *   \file       htdocs/ultimateqrcode/qrcodemember.php
 *   \brief      Tab for member QR-code
 *   \ingroup    ultimateqrcode
 */

$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

include_once(DOL_DOCUMENT_ROOT."/core/lib/company.lib.php");
require_once DOL_DOCUMENT_ROOT.'/core/lib/member.lib.php';
require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
include_once("./lib/ultimateqrcode.lib.php");
require_once("./includes/phpqrcode/qrlib.php"); 

$action = GETPOST("action");
$rowid=GETPOST('rowid','int');;  

$langs->load("members");
$langs->load("companies");
$langs->load("ultimateqrcode@ultimateqrcode");

// Security check
$result=restrictedArea($user,'adherent',$rowid,'','','fk_soc', 'rowid', '');

if (!$user->rights->ultimateqrcode->read) accessforbidden();


/*
 *	View
 */

$form = new Form($db);
llxHeader('',$langs->trans("UltimateqrcodeQrcode"));

if ($rowid > 0)
{
    $object = new Adherent($db);
    $object->fetch($rowid);

/*
 * Affichage onglets
 */
    if ($conf->notification->enabled) $langs->load("mails");

	$head = member_prepare_head($object);
	
	dol_fiche_head($head, 'tabqrcodemember', $langs->trans("Member"),0,'user');
	
	$png_web_dir = 'temp/';
	$tempDir = dirname(__FILE__).DIRECTORY_SEPARATOR.$png_web_dir; 
	if (!file_exists($tempDir))
        mkdir($tempDir);  
	
	// we building raw data
    $codeContents  = 'BEGIN:VCARD'."\n";
	$codeContents .= 'FN:'.$object->firstname."\n";
    $codeContents .= 'LN:'.$object->lastname."\n";
    $codeContents .= 'TEL;PERSO;VOICE:'.$object->phone_perso."\n";
	$codeContents .= 'ADR;TYPE=perso;'.
        'LABEL="'.$addressLabel.'":'
        .$object->address.';'
        .$object->town.';'
        .$object->zip.';'
        .$object->country
    ."\n";
	$codeContents .= 'EMAIL:'.$object->email."\n"; 
    $codeContents .= 'END:VCARD';

	$filename = $tempDir.md5($codeContents).'.member.png';
	// generating
    QRcode::png($codeContents, $filename, QR_ECLEVEL_L, 2); 
	
	print '<table class="border" width="100%">';	
	
	$linkback = '<a href="'.DOL_URL_ROOT.'/adherents/liste.php">'.$langs->trans("BackToList").'</a>';
	
	// Ref
	print '<tr><td width="25%">'.$langs->trans("Ref").'</td>';
	print '<td class="valeur" colspan="3">';
	print $form->showrefnav($object, 'rowid', $linkback);
	print '</td></tr>';
	
	// displaying QRcode
	$htmlqrcode='';
	$rowspan=4;
	$htmlqrcode.='<td rowspan="'.$rowspan.'" style="text-align: center;" width="25%">';
	if ($filename)   $htmlqrcode.='<img src="'.$png_web_dir.basename($filename).'" />';
	$htmlqrcode.='</td>';
	
	// Firstname
    print '<tr><td width="25%">'.$langs->trans('Firstname').'</td>';
    print '<td colspan="3">'.$object->firstname.'&nbsp;';
    print '</td>';
	print $htmlqrcode; 
	print '</tr>';
	
    // Lastname
    print '<tr><td width="25%">'.$langs->trans('Lastname').'</td>';
    print '<td colspan="3">'.$object->lastname.'&nbsp;</td>';
    print '</tr>';
	
	// Address
    print "<tr><td valign=\"top\">".$langs->trans('Address').'</td><td colspan="3">';
    dol_print_address($object->address,'gmap','member',$object->id);
    print '</td></tr>';
	
	// Zip / Town
    print '<tr><td width="25%">'.$langs->trans('Zip').' / '.$langs->trans("Town").'</td><td colspan="3">';
    print $object->zip.($object->zip && $object->town?" / ":"").$object->town;
    print '</td></tr>';

    // Country
    print '<tr><td>'.$langs->trans("Country").'</td><td colspan="3" class="nowrap">';
    if (! empty($object->country_code))
    {
        $img=picto_from_langcode($object->country_code);
        if ($object->isInEEC()) print $form->textwithpicto(($img?$img.' ':'').$object->country,$langs->trans("CountryIsInEEC"),1,0);
        else print ($img?$img.' ':'').$object->country;
    }
    print '</td></tr>';
	
	// Tel perso
	print '<tr><td>'.$langs->trans("PhonePerso").'</td><td colspan="3">'.dol_print_phone($object->phone_perso,$object->country_code,0,$object->fk_soc,1).'</td></tr>';
	
	// EMail
    print '<tr><td>'.$langs->trans('EMail').'</td><td colspan="3">';
    print dol_print_email($object->email,0,$object->rowid,'AC_EMAIL');
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
