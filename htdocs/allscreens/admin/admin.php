<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) <year>  <name of author>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file		admin/admin.php
 * 	\ingroup	allscreens
 * 	\brief		This file is an example module setup page
 * 				Put some comments here
 */
// Dolibarr environment
$res=@include("../../main.inc.php");					// For root directory
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/main.inc.php"))
	$res=@include($_SERVER['DOCUMENT_ROOT']."/main.inc.php"); // Use on dev env only
if (! $res) $res=@include("../../../main.inc.php");		// For "custom" directory

// Libraries
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/allscreens/lib/allscreens.lib.php';


// Translations
$langs->load("admin");
$langs->load("allscreens@allscreens");

// Access control
if (! $user->admin) accessforbidden();



/*
 * Actions
 */
$mesg="";
$action = GETPOST('action', 'alpha');

if ($action == 'onoff') {
  $name = GETPOST ( 'name', 'text' );
  $value = GETPOST ( 'value', 'int' );
	
	if ($value) {
		$res = dolibarr_set_const($db, $name, 1, 'yesno', 0, '', $conf->entity);
	} else {
		$res = dolibarr_set_const($db, $name, 0, 'yesno', 0, '', $conf->entity);
	}
	
	if (! $res > 0)	$error ++;
	
	if (! $error) {
		setEventMessage($langs->trans("SetupSaved"), 'mesgs');
	} else {
		setEventMessage($langs->trans("Error"), 'errors');
	}
} elseif ($action == 'defaultscolors') {
	$mesg = "<font class='ok'>".$langs->trans("DefaultsColorsMsg")."</font>";
	$source = '../css/style.min.css.default';
	$dest = '../css/style.min.css';
	copy($source,$dest);
	$col1="#5999A7";dolibarr_set_const($db, "ALLSCREENS_COL1", $col1,'chaine',0,'',$conf->entity);
	$col2="#F07B6E";dolibarr_set_const($db, "ALLSCREENS_COL2", $col2,'chaine',0,'',$conf->entity);
	$col3="#D0D0D0";dolibarr_set_const($db, "ALLSCREENS_COL_BODY_BCKGRD", $col3,'chaine',0,'',$conf->entity);
} elseif ($action == 'set') { // set colors
	$name = GETPOST ( 'name', 'text' );
	$value = GETPOST ( 'value', 'text' );
	$mesg = "<font class='ok'>".$langs->trans("SetupSaved")."</font>";
	$file = '../css/style.min.css';

	$oldvalue = dolibarr_get_const($db, $name);
	$newvalue = '#' . strtoupper($value);
	dolibarr_set_const($db, $name, $newvalue ,'chaine',0,'',$conf->entity);
	$file_contents = file_get_contents($file);

	if ( $name == 'ALLSCREENS_COL_BODY_BCKGRD' ) {
		$oldvalue = 'body{background-color:' . $oldvalue;
		$newvalue = 'body{background-color:' . $newvalue;
	}

	$file_contents = str_replace($oldvalue,$newvalue,$file_contents);
	file_put_contents($file,$file_contents);
}

// Get colors
$col1=$conf->global->ALLSCREENS_COL1;
$col2=$conf->global->ALLSCREENS_COL2;
$col3=$conf->global->ALLSCREENS_COL_BODY_BCKGRD;

/*
 * View
 */
$page_name = "AllScreensSetup";
llxHeader('', $langs->trans($page_name),'','','','', array('/allscreens/js/jscolor.js'),'' );

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'	. $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = allscreens_admin_prepare_head();
dol_fiche_head(
	$head,
	'settings',
	$langs->trans("Module500500Name"),
	0,
	"logo@allscreens"
);

dol_htmloutput_mesg($mesg);


// Setup page goes here

print '<script type="text/javascript">';
print 'r(function(){';
print '	var els = document.getElementsByTagName("link");';
print '	var els_length = els.length;';
print '	for (var i = 0, l = els_length; i < l; i++) {';
print '    var el = els[i];';
print '	   if (el.href.search("style.min.css") >= 0) {';
print '        el.href += "?" + Math.floor(Math.random() * 100);';
print '    }';
print '	}';
print '});';
print 'function r(f){/in/.test(document.readyState)?setTimeout("r("+f+")",9):f()}';
print '</script>';

print '<br>';

// Colors
print '<div class="subsetting-title">' . $langs->trans("AS_SettingsColors") . '</div>';
print '<table class="noborder as-settings-colors" width="100%">';

print '<tr class="liste_titre">';
print 	'<th>' . $langs->trans("Name") . '</td>';
print 	'<th>' . $langs->trans("Value") . '</td>';
print "</tr>\n";

print '<tr>';
print 	'<td>' . $langs->trans("PrimaryColor") . '</td>';
print 	'<td>';
print 		'<form id="col1-form" method="post" action="admin.php">';
print 			'<input type="hidden" name="action" value="set">';
print 			'<input type="hidden" name="name" value="ALLSCREENS_COL1">';
print 			'<input id="col1" class="color" type=text name="value" value="' . $col1 . '">';
print 			'<input type="submit" class="button" value="Valider">';
print 		'</form>';
print 	'</td>';
print "</tr>\n";

print '<tr>';
print 	'<td>' . $langs->trans("SecondaryColor") . '</td>';
print 	'<td>';
print 		'<form id="col2-form" method="post" action="admin.php">';
print 			'<input type="hidden" name="action" value="set">';
print 			'<input type="hidden" name="name" value="ALLSCREENS_COL2">';
print 			'<input id="col2" class="color" type=text name="value" value="' . $col2 . '">';
print 			'<input type="submit" class="button" value="Valider">';
print 		'</form>';
print 	'</td>';
print "</tr>\n";

print '<tr>';
print 	'<td>' . $langs->trans("BodyBckgrdColor") . '</td>';
print 	'<td>';
print 		'<form id="col3-form" method="post" action="admin.php">';
print 			'<input type="hidden" name="action" value="set">';
print 			'<input type="hidden" name="name" value="ALLSCREENS_COL_BODY_BCKGRD">';
print 			'<input id="col3" class="color" type=text name="value" value="' . $col3 . '">';
print 			'<input type="submit" class="button" value="Valider">';
print 		'</form>';
print 	'</td>';
print "</tr>\n";

print '</table>';

print '<br>';
print '<form id="as-def-colors" method="post" action="admin.php">';
print '<input type="hidden" name="action" value="defaultscolors">';
print '<label for="defcolors">' . $langs->trans("DefaultsColors") . ' : </label>';
print '<input type="submit" class="button" value="Valider">';
print '</form>';
print '<br>';
print '<br>';



// options
print '<div class="subsetting-title">' . $langs->trans("AS_SettingsOptions") . '</div>';
print '<table class="noborder as-settings-options" width="100%">';

print '<tr class="liste_titre">';
print 	'<th>' . $langs->trans("Name") . '</td>';
print 	'<th>' . $langs->trans("Description") . '</td>';
print 	'<th>' . $langs->trans("Value") . '</td>';
print "</tr>\n";

print '<tr class="pair">';
print 	'<td>'.$langs->trans('FixedMenu').'</td>';
print 	'<td>'.'</td>';
$name='ALLSCREENS_FIXED_MENU';
if (! empty ( $conf->global->ALLSCREENS_FIXED_MENU )) {
print 	'<td><a href="' . $_SERVER ['PHP_SELF'] . '?action=onoff&name='.$name.'&value=0">';
print img_picto ( $langs->trans ( "Enabled" ), 'switch_on' );
print 	"</a></td>";
} else {
print 	'<td><a href="' . $_SERVER ['PHP_SELF'] . '?action=onoff&name='.$name.'&value=1">';
print img_picto ( $langs->trans ( "Disabled" ), 'switch_off' );
print 	"</a></td>";
}
print "</tr>\n";

print '</table>';

print '<br>';


// Page end
dol_fiche_end();
llxFooter();
$db->close();
?>