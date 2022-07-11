<?php
/* Copyright (C) 2014 Philippe Grand  <philippe.grand@atoo-net.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 * or see http://www.gnu.org/
 */

/**
 *	    \file       /ultimateqrcode/lib/ultimateqrcode.lib.php
 *		\brief      Ensemble de fonctions de base pour le module ultimateqrcode
 *      \ingroup    ultimateqrcode
 */


function ultimateqrcode_prepare_head()
{
	global $langs, $conf;
	$langs->load("bills");
	$langs->load("orders");
	$langs->load("propal");
	$langs->load('ultimateqrcode@ultimateqrcode');

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/ultimateqrcode/admin/ultimateqrcode.php",1);
	$head[$h][1] = $langs->trans("UltimateqrcodeQrcode");
	$head[$h][2] = 'qrcodes';
	$h++;
	
	$head[$h][0] = dol_buildpath("/ultimateqrcode/admin/about.php",1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to remove a tab
    complete_head_from_modules($conf,$langs,$object,$head,$h,'ultimateqrcode');

	return $head;
}

?>