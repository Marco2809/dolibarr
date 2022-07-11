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
*/

/**
 *	\file       htdocs/compta/facture/class/facture.class.php
*	\ingroup    facture
*	\brief      File of class to manage invoices
*/

/**
 *	\class      	ExtrafieldsFBI
 *	\brief      	Class to trunc price fields on extra fields
 *
 */
class ExtrafieldsFBI  extends ExtraFields
{
	var $db;

	/**
	 *  Constructor
	 *
	 *  @param	DoliDB		$db		Database handler
	 */
	function __construct($db)
	{
		$this->db = $db;
	}

	
	/**
	 * Return HTML string to put an output field into a page
	 *
	 * @param   string	$key            Key of attribute
	 * @param   string	$value          Value to show
	 * @param	string	$moreparam		More param
	 * @return	string					Formated value
	 */
	function showOutputField($key,$value,$moreparam='')
	{
		global $conf,$langs;
	
		$label=$this->attribute_label[$key];
		$type=$this->attribute_type[$key];
		$size=$this->attribute_size[$key];
		$elementtype=$this->attribute_elementtype[$key];
		$unique=$this->attribute_unique[$key];
		$required=$this->attribute_required[$key];
		$params=$this->attribute_param[$key];
	
		if ($type == 'date')
		{
			$showsize=10;
			$value=dol_print_date($value,'day');
		}
		elseif ($type == 'datetime')
		{
			$showsize=19;
			$value=dol_print_date($value,'dayhour');
		}
		elseif ($type == 'int')
		{
			$showsize=10;
		}
		elseif ($type == 'double')
		{
			if (!empty($value)) {
				$value=price($value);
			}
		}
		elseif ($type == 'boolean')
		{
			$checked='';
			if (!empty($value)) {
				$checked=' checked="checked" ';
			}
			$value='<input type="checkbox" '.$checked.' '.($moreparam?$moreparam:'').' readonly="readonly" disabled="disabled">';
		}
		elseif ($type == 'mail')
		{
			$value=dol_print_email($value);
		}
		elseif ($type == 'phone')
		{
			$value=dol_print_phone($value);
		}
		elseif ($type == 'price')
		{
			// diciannove
			$value=price($value,0,$langs,1,1,-1,$conf->currency);
		}
		elseif ($type == 'select')
		{
			$value=$params['options'][$value];
		}
		elseif ($type == 'sellist')
		{
			$param_list=array_keys($params['options']);
			$InfoFieldList = explode(":", $param_list[0]);
	
			$selectkey="rowid";
			$keyList='rowid';
	
			if (count($InfoFieldList)>=3)
			{
				$selectkey = $InfoFieldList[2];
				$keyList=$InfoFieldList[2].' as rowid';
			}
	
			$fields_label = explode('|',$InfoFieldList[1]);
			if(is_array($fields_label)) {
				$keyList .=', ';
				$keyList .= implode(', ', $fields_label);
			}
	
			$sql = 'SELECT '.$keyList;
			$sql.= ' FROM '.MAIN_DB_PREFIX .$InfoFieldList[0];
			if (strpos($InfoFieldList[4], 'extra')!==false)
			{
				$sql.= ' as main';
			}
			$sql.= " WHERE ".$selectkey."='".$this->db->escape($value)."'";
			//$sql.= ' AND entity = '.$conf->entity;
	
			dol_syslog(get_class($this).':showOutputField:$type=sellist sql='.$sql);
			$resql = $this->db->query($sql);
			if ($resql)
			{
				$value='';	// value was used, so now we reste it to use it to build final output
	
				$obj = $this->db->fetch_object($resql);
	
				// Several field into label (eq table:code|libelle:rowid)
				$fields_label = explode('|',$InfoFieldList[1]);
	
				if(is_array($fields_label) && count($fields_label)>1)
				{
					foreach ($fields_label as $field_toshow)
					{
						$translabel='';
						if (!empty($obj->$field_toshow)) {
							$translabel=$langs->trans($obj->$field_toshow);
						}
						if ($translabel!=$field_toshow) {
							$value.=dol_trunc($translabel,18).' ';
						}else {
							$value.=$obj->$field_toshow.' ';
						}
					}
				}
				else
				{
					$translabel='';
					if (!empty($obj->$InfoFieldList[1])) {
						$translabel=$langs->trans($obj->$InfoFieldList[1]);
					}
					if ($translabel!=$obj->$InfoFieldList[1]) {
						$value=dol_trunc($translabel,18);
					}else {
						$value=$obj->$InfoFieldList[1];
					}
				}
			}
			else dol_syslog(get_class($this).'::showOutputField error '.$this->db->lasterror(), LOG_WARNING);
		}
		elseif ($type == 'radio')
		{
			$value=$params['options'][$value];
		}
		elseif ($type == 'checkbox')
		{
			$value_arr=explode(',',$value);
			$value='';
			if (is_array($value_arr))
			{
				foreach ($value_arr as $keyval=>$valueval) {
					$value.=$params['options'][$valueval].'<br>';
				}
			}
		}
		else
		{
			$showsize=round($size);
			if ($showsize > 48) $showsize=48;
		}
		//print $type.'-'.$size;
		$out=$value;
		return $out;
	}
}

