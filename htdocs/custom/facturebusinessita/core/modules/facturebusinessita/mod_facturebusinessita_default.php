<?php
/* Copyright (C) 2015 Claudio Aschieri <c.aschieri@19.coop>
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
 *  \file       htdocs/core/modules/facturebusinessita/mod_facturebusinessita_default.php
 *  \ingroup    facturebusinessita
 *  \brief      File of class to manage customer order numbering rules default
 */

dol_include_once('/facturebusinessita/core/modules/facturebusinessita/modules_facturebusinessita.php');

/**
 *	Class to manage customer order numbering rules default
 */
class mod_facturebusinessita_default extends ModeleNumRefFactureBusinessITAs
{
	var $version='dolibarr';		// 'development', 'experimental', 'dolibarr'
	var $prefix='';
	var $error='';
	var $nom='Standard Modulo Fatture';


    /**
     *  Return description of numbering module
     *
     *  @return     string      Text with description
     */
    function info()
    {
    	global $langs;
      return $langs->trans("DefaultNumRefModelDesc",$this->prefix);
    }


	/**
	 *  Renvoi un exemple de numerotation
	 *
	 *  @return     string      Example
	 */
	function getExample()
	{
		return $this->prefix."";
	}


	/**
	 *  Test si les numeros deje en vigueur dans la base ne provoquent pas de
	 *  de conflits qui empechera cette numerotation de fonctionner.
	 *
	 *  @return     boolean     false si conflit, true si ok
	 */
	function canBeActivated()
	{
		global $conf,$langs,$db;

		$coyymm=''; $max='';

		$posindice=8;
		$sql = "SELECT MAX(CAST(SUBSTRING(facnumber FROM ".$posindice.") AS SIGNED)) as max";
		$sql.= " FROM ".MAIN_DB_PREFIX."facture";
		$sql.= " WHERE facnumber LIKE '".$this->prefix."____-%'";
		$sql.= " AND entity = ".$conf->entity;

		$resql=$db->query($sql);
		if ($resql)
		{
			$row = $db->fetch_row($resql);
			if ($row) { $coyymm = substr($row[0],0,6); $max=$row[0]; }
		}
		if ($coyymm && ! preg_match('/'.$this->prefix.'[0-9][0-9][0-9][0-9]/i',$coyymm))
		{
			$langs->load("errors");
			$this->error=$langs->trans('ErrorNumRefModel', $max);
			return false;
		}

		return true;
	}

	/**
	 * 	Return next free value
	 *
	 *  @param	Societe		$objsoc     Object thirdparty
	 *  @param  Object		$object		Object we need next value for
	 *  @return string      			Value if KO, <0 if KO
	 */
	function getNextValue($objsoc,$object)
	{
		global $langs;
		
		return $langs->trans('FactureBusinessITASameAsFacture');
	}


	/**
	 *  Return next free value
	 *
	 *  @param	Societe		$objsoc     Object third party
	 * 	@param	string		$objforref	Object for number to search
	 *  @return string      			Next free value
	 */
	function facturebusinessita_get_num($objsoc,$objforref)
	{
		return $this->getNextValue($objsoc,$objforref);
	}
	
	/**
	 *  Return next free value
	 *
	 *  @param	Societe		$objsoc     Object third party
	 * 	@param	string		$objforref	Object for number to search
	 *  @return string      			Next free value
	 */
	function getNumRef($objsoc,$objforref)
	{
		return $this->getNextValue($objsoc,$objforref);
	}

}
