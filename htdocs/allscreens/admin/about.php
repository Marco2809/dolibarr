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
 * 	\file		admin/about.php
 * 	\ingroup	mymodule
 * 	\brief		This file is an example about page
 * 				Put some comments here
 */
// Dolibarr environment
$res=@include("../../main.inc.php");					// For root directory
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/main.inc.php"))
	$res=@include($_SERVER['DOCUMENT_ROOT']."/main.inc.php"); // Use on dev env only
if (! $res) $res=@include("../../../main.inc.php");		// For "custom" directory

//global $langs, $user;

// Libraries
//require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once DOL_DOCUMENT_ROOT . '/allscreens/lib/allscreens.lib.php';

// Use the .inc variant because we don't have autoloading support
require_once '../lib/php-markdown/Michelf/Markdown.inc.php';

use \Michelf\Markdown;

//require_once "../class/myclass.class.php";
// Translations
$langs->load("allscreens@allscreens");

// Access control
if (! $user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');

/*
 * Actions
 */

/*
 * View
 */
$page_name = "AllScreensAboutPage";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'	. $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = allscreens_admin_prepare_head();
dol_fiche_head(
	$head,
	'about',
	$langs->trans("Module500500Desc"),
	0,
	'logo@allscreens'
);

// About page goes here
//echo $langs->trans("AllScreensAboutPage");
echo '<br>',
'<img width=30 height=30 src="' . dol_buildpath('/allscreens/img/logo.png', 1) . '"/>',
'<span style="vertical-align:middle;margin-left:0.5em;font-size:2em;font-weight:bold;">Module AllScreens</span><br><br>',
'Version: 1.3.3<br>',
'Dernière mise à jour: 30/08/2015<br><br>',
'Description: Ajoute à Dolibarr la fonctionnalité Responsive, permettant de l\'utiliser sur toute taille d\'écran.<br>',
'La taille et la position des éléments s\'ajustent automatiquement.<br><br>',
'Vidéo de démonstration: ','<a href="http://msmobile.fr/allscreens-v1-3-0">Démo AllScreens</a><br><br>',
'Développé par ',
'<img width=120 height=40 src="' . dol_buildpath('/allscreens/img/msm_logo_600x200.png', 1) . '"/><br>',
'Auteur: Serge Azout<br>',
'Site Internet: ','<a href="http://msmobile.fr">msmobile.fr</a><br>',
'Email: contact@msmobile.fr<br><br>'
;


// Page end
dol_fiche_end();
llxFooter();
