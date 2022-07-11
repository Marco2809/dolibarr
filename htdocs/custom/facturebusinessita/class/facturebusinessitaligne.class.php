<?php
/* Copyright (C) 2015 Claudio Aschieri <c.aschieri@19.coop>
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
 * 	\file		class/facturebusinessita.class.php
 * 	\ingroup	facturebusinessita
 * 	\brief		Print line facture
 * 				
 */

dol_include_once('/facturebusinessita/class/facturebusinessita.class.php');

class FactureBusinessITALigne extends CommonInvoiceLine
{

	protected $db; //!< To store db handler
	public $error; //!< To return error code (or message)
	public $errors = array(); //!< To return several error codes (or messages)
	public $socid;	// id societe
	public $table_element='facturedet';	// obbligatorio altrimenti gli extrafields falliscono

	/**
	 * Constructor
	 *
	 * 	@param	DoliDb		$db		Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;

		return 1;
	}
	
	
	/**
	 * Function to show lines of extrafields with output datas
	 *
	 * @param	object	$extrafields	Extrafield Object
	 * @param	string	$mode			Show output (view) or input (edit) for extrafield
	 * @param	array	$params			Optionnal parameters
	 * @param	string	$keyprefix		Prefix string to add into name and id of field (can be used to avoid duplicate names)
	 *
	 * @return string
	 */
	function showOptionals($extrafields, $mode='view', $params=0, $keyprefix='')
	{
		global $_POST, $conf;
		
		$out = '';
		$view_fe=false; // visualizza campi per la fatturazione elettronica
		$view_ci=!empty($conf->global->FACTUREBUSINESSITA_USE_CI) ? true : false; 	// visualizza campi per contabilità industriale
	
		// controllo se il cliente deve vedere i campi della fatturazione elettronica 
		if(!empty($this->socid)) 
		{		 
			if(FactureBusinessITA::isClienteConFatturaElettronica($this->socid) && $conf->global->FACTUREBUSINESSITA_USE_FE) 
			{
				$view_fe = true;
			}
		}
		
		if (count($extrafields->attribute_label) > 0)
		{
			$out .= "\n";
			$out .= '<!-- showOptionalsInput --> ';
			$out .= "\n";
	
			$e = 0;
			foreach($extrafields->attribute_label as $key=>$label)
			{
				$view_single_field = true;	// visualizza il singolo campo di default
				switch($key) 
				{
					case 'codicecup_facturebusinessita': 	
					case 'codicecig_facturebusinessita': 
					case 'ndoc_facturebusinessita':
					case 'tipodoc_facturebusinessita':		
					case 'datadoc_facturebusinessita':
					case 'rifadm22115_facturebusinessita':
						if(!$view_fe) $view_single_field = false;	
						break;
						
					case 'fk_pianoconti_attivi_facturebusinessita': 
					case 'fk_uproduttiva_facturebusinessita': 
						if(!$view_ci) $view_single_field = false;	
						break;
					case 'fk_iva_esenzioni_facturebusinessita':
						if(!$view_ci && !$view_fe) $view_single_field = false;
						break;
					default:
						$view_single_field = true;	
						break;
				}

				if($view_single_field)
				{	
					if (is_array($params) && count($params)>0) {
						if (array_key_exists('colspan',$params)) {
							$colspan=$params['colspan'];
						}
					}else {
						$colspan='3';
					}
					switch($mode) {
						case "view":
							$value=$this->array_options["options_".$key];
							break;
						case "edit":
							$value=(isset($_POST["options_".$key])?$_POST["options_".$key]:$this->array_options["options_".$key]);
							break;
					}
					if ($extrafields->attribute_type[$key] == 'separate')
					{
						$out .= $extrafields->showSeparator($key);
					}
					else
					{
						$csstyle='';
						if (is_array($params) && count($params)>0) {
							if (array_key_exists('style',$params)) {
								$csstyle=$params['style'];
							}
						}
						if ( !empty($conf->global->MAIN_EXTRAFIELDS_USE_TWO_COLUMS) && ($e % 2) == 0)
						{
							$out .= '<tr '.$csstyle.'>';
							$colspan='0';
						}
						else
						{
							$out .= '<tr '.$csstyle.'>';
						}
						// Convert date into timestamp format
						if (in_array($extrafields->attribute_type[$key],array('date','datetime')))
						{
							$value = isset($_POST["options_".$key])?dol_mktime($_POST["options_".$key."hour"], $_POST["options_".$key."min"], 0, $_POST["options_".$key."month"], $_POST["options_".$key."day"], $_POST["options_".$key."year"]):$this->db->jdate($this->array_options['options_'.$key]);
						}
		
						if($extrafields->attribute_required[$key])
							$label = '<span class="fieldrequired">'.$label.'</span>';
		
						$out .= '<td>'.$label.'</td>';
						$out .='<td'.($colspan?' colspan="'.$colspan.'"':'').'>';
		
						switch($mode) {
							case "view":
								$out .= $extrafields->showOutputField($key,$value);
								break;
							case "edit":
								$out .= $extrafields->showInputField($key,$value,'',$keyprefix);
								break;
						}
		
						$out .= '</td>'."\n";
		
						if (! empty($conf->global->MAIN_EXTRAFIELDS_USE_TWO_COLUMS) && (($e % 2) == 1)) $out .= '</tr>';
						else $out .= '</tr>';
						$e++;
					}
				}
			}
			$out .= "\n";
			$out .= '<!-- /showOptionalsInput --> ';
			$out .= '
				<script type="text/javascript">
				    jQuery(document).ready(function() {
				    	function showOptions(child_list, parent_list)
				    	{
				    		var val = $("select[name=\"options_"+parent_list+"\"]").val();
				    		var parentVal = parent_list + ":" + val;
							if(val > 0) {
					    		$("select[name=\""+child_list+"\"] option[parent]").hide();
					    		$("select[name=\""+child_list+"\"] option[parent=\""+parentVal+"\"]").show();
							} else {
								$("select[name=\""+child_list+"\"] option").show();
							}
				    	}
						function setListDependencies() {
					    	jQuery("select option[parent]").parent().each(function() {
					    		var child_list = $(this).attr("name");
								var parent = $(this).find("option[parent]:first").attr("parent");
								var infos = parent.split(":");
								var parent_list = infos[0];
								$("select[name=\"options_"+parent_list+"\"]").change(function() {
									showOptions(child_list, parent_list);
								});
					    	});
						}
	
						setListDependencies();
				    });
				</script>';
		}
		return $out;
	}
	
}