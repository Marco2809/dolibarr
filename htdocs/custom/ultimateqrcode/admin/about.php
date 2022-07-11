<?php
/* Copyright (C) 2014	Philippe Grand	<philippe.grand@atoo-net.com>
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
 * 	\file		htdocs/custom/ultimateqrcode/admin/about.php
 * 	\ingroup	ultimateqrcode
 * 	\brief		about page
 */

// Dolibarr environment
$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");		// For root directory
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");	// For "custom" directory


// Libraries
require_once("../lib/ultimateqrcode.lib.php");
require_once("../lib/PHP_Markdown/markdown.php");


// Translations
$langs->load("ultimateqrcode@ultimateqrcode");

// Access control
if (!$user->admin)
	accessforbidden();

/*
 * View
 */

llxHeader();

// Subheader
$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("UltimateqrcodeSetup"),$linkback,'ultimateqrcode@ultimateqrcode');

// Configuration header
$head = ultimateqrcode_prepare_head();
dol_fiche_head($head, 'about', $langs->trans("Module300400Desc"), 0, "ultimateqrcode@ultimateqrcode");

// About page goes here

print '<br>';

$buffer = file_get_contents(dol_buildpath('/ultimateqrcode/README.md',0));
print Markdown($buffer);

print '<br>';
print $langs->trans("MoreModules").'<br>';
print '&nbsp; &nbsp; &nbsp; '.$langs->trans("MoreModulesLink").'<br>';
$url='http://www.dolistore.com/search.php?search_query=atoo.net';
print '<a href="'.$url.'" target="_blank"><img border="0" width="180" src="'.DOL_URL_ROOT.'/theme/dolistore_logo.png"></a><br><br><br>';

print '<a href="'.dol_buildpath('/ultimateqrcode/COPYING',1).'">';
print '<img src="'.dol_buildpath('/ultimateqrcode/img/gplv3.png',1).'"/>';
print '</a>';

llxFooter();
$db->close();
?>
