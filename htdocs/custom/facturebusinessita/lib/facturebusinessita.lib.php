<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 Claudio Aschieri <c.aschieri@19.coop>
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
 *	\file		lib/facturebusinessita.lib.php
 *	\ingroup	facturebusinessita
 *	\brief		Module library
 *				
 */


dol_include_once('/facturebusinessita/class/facturebusinessita.class.php');


function facturebusinessitaAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("facturebusinessita@facturebusinessita");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/facturebusinessita/admin/admin_facturebusinessita.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;
	$head[$h][0] = dol_buildpath("/facturebusinessita/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@facturebusinessita:/facturebusinessita/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@facturebusinessita:/facturebusinessita/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'facturebusinessita');

	return $head;
}


/**
 * 
 * @param unknown $description
 * @return mixed
 */

function facturebusinessitaRemoveEsenzioni($description,$socid='')
{
	global $db,$conf;
	
	switch ($conf->global->FACTUREBUSINESSITA_ESENZIONE_TO_PDF){
		case 'S':
			$active = true;
			break;
		case 'F':
			if($socid){
				if(FactureBusinessITA::isClienteConFatturaElettronica($socid)) $active = true;
				else $active = false;
			} else {
				$active = false;
			}	
			break;
		default:
			$active = false;
			break;	
	}
	
	if($active) {
		$sql = "SELECT if(description!='',description,label) as description FROM ".MAIN_DB_PREFIX."facturebusinessita_iva_esenzioni";
		dol_syslog("template_eFattura::select sql=".$sql);
		$resql = $db->query($sql);
		$num = $db->num_rows($resql);
		$i = 0;
		while ($i < $num)
		{	
			$obj = $db->fetch_object($resql);
			$text_to_remove = "\n\n".$obj->description;
			$description = str_replace($text_to_remove, '', $description);
			$i++;
		}
	}
	
	return $description;
}
