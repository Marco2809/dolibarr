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
 * \file    class/actions_facturebusinessita.class.php
 * \ingroup facturebusinessita
 * \brief   Hook for supplier invoice card e invoice card
 */

/**
 * Class ActionsFactureBusinessITA
 */
class ActionsFactureBusinessITA
{
	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function formObjectOptions($parameters, &$object, &$action, $hookmanager)
	{
		global $db, $langs, $user, $_POST;
		$error = 0; // Error counter
		
		if (in_array('invoicesuppliercard', explode(':', $parameters['context'])) && $action == 'create') 
		{			
			// Extrafields
			$reshook='';
			dol_include_once('/facturebusinessita/class/extrafieldsFBI.class.php');
			$extrafields = new ExtraFieldsFBI($db);	// nuova classe extrafields del modulo FactureBusinessITA
			$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);
			
			//print $object->showOptionals($extrafields, 'edit');
			$mode = 'edit';
			if (count($extrafields->attribute_label) > 0)
			{
				$out .= "\n";
				$out .= '<!-- showOptionalsInput --> ';
				$out .= "\n";
			
				$e = 0;
				foreach($extrafields->attribute_label as $key=>$label)
				{
					$mode = (($key == 'ritenuta_acconto_facturebusinessita') || ($key == 'netto_da_pagare_facturebusinessita')) ? "view" : "edit";
					
					if($action == '')
					{
						$mode = "view";
					}
					
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
							$value = isset($_POST["options_".$key])?dol_mktime($_POST["options_".$key."hour"], $_POST["options_".$key."min"], 0, $_POST["options_".$key."month"], $_POST["options_".$key."day"], $_POST["options_".$key."year"]):$db->jdate($this->array_options['options_'.$key]);
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
			print $out;
			return 1;
		}
		else 
			if (in_array('invoicesuppliercard', explode(':', $parameters['context'])) && ($action != 'create')) 
			{
				// Extrafields
				$reshook='';
				dol_include_once('/facturebusinessita/class/extrafieldsFBI.class.php');
				$extrafields = new ExtraFieldsFBI($db);	// nuova classe extrafields del modulo FactureBusinessITA
				$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);
					
				if (empty($reshook) && ! empty($extrafields->attribute_label)) {
				
					foreach ($extrafields->attribute_label as $key => $label) {
						if ($action == 'edit_extras') {
							$value = (isset($_POST["options_" . $key]) ? $_POST["options_" . $key] : $object->array_options ["options_" . $key]);
						} else {
							$value = $object->array_options ["options_" . $key];
						}
							
						if ($extrafields->attribute_type [$key] == 'separate') {
							print $extrafields->showSeparator($key);
						} else {
							print '<tr><td';
							if (! empty($extrafields->attribute_required [$key]))
								print ' class="fieldrequired"';
							print '>' . $label . '</td><td colspan="5">';
							// Convert date into timestamp format
							if (in_array($extrafields->attribute_type [$key], array('date','datetime'))) {
								$value = isset($_POST["options_" . $key]) ? dol_mktime($_POST["options_" . $key . "hour"], $_POST["options_" . $key . "min"], 0, $_POST["options_" . $key . "month"], $_POST["options_" . $key . "day"], $_POST["options_" . $key . "year"]) : $db->jdate($object->array_options ['options_' . $key]);
							}
								
							if ($action == 'edit_extras' && $user->rights->fournisseur->facture->creer && GETPOST('attribute') == $key) {
								print '<form enctype="multipart/form-data" action="' . $_SERVER["PHP_SELF"] . '" method="post" name="formsoc">';
								print '<input type="hidden" name="action" value="update_extras">';
								print '<input type="hidden" name="attribute" value="' . $key . '">';
								print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
								print '<input type="hidden" name="id" value="' . $object->id . '">';
									
								print $extrafields->showInputField($key, $value);
									
								print '<input type="submit" class="button" value="' . $langs->trans('Modify') . '">';
								print '</form>';
							}
							else
							{
								print $extrafields->showOutputField($key, $value);
								if ($object->statut == 0  && $user->rights->fournisseur->facture->creer)
								{
									if(($key != 'ritenuta_acconto_facturebusinessita') && ($key != 'netto_da_pagare_facturebusinessita'))
										print '<a href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=edit_extras&attribute=' . $key . '">' . img_picto('', 'edit') . ' ' . $langs->trans('Modify') . '</a>';
								}
							}
							print '</td></tr>' . "\n";
						}
					}
				}
				return 1;
			}


		if (! $error)
		{
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error display message';
			return -1;
		}
	}
	
	
	
	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function printObjectLine($parameters, &$object, &$action, $hookmanager)
	{
		global $db, $langs, $user;
		$error = 0; 	// Error counter
		
		if (in_array('invoicesuppliercard', explode(':', $parameters['context'])))
		{
			// Extrafields
			$extrafieldsline = new ExtraFields($db);
			$extralabelsline = $extrafieldsline->fetch_name_optionals_label($object->table_element_line);
			
			dol_include_once('/fourn/class/fournisseur.facture.class.php');
			$facturesupplierligne = new FactureSupplierLigne($db);
			$facturesupplierligne->id = $parameters['line']->rowid;			
			$facturesupplierligne->fetch_optionals($facturesupplierligne->id,$extralabelslines);

			print $facturesupplierligne->showOptionals($extrafieldsline, 'view', array('style'=>$bcnd[$var], 'colspan'=>7, 'colspanlabel'=>3));			
			
		}
		
		if (in_array('invoicecard', explode(':', $parameters['context'])))
		{
			// Extrafields
			$extrafieldsline = new ExtraFields($db);
			$extralabelsline = $extrafieldsline->fetch_name_optionals_label($object->table_element_line);
						
			dol_include_once('/facturebusinessita/class/facturebusinessitaligne.class.php');
			$facturebusinessitaligne = new FactureBusinessITALigne($db);
			$facturebusinessitaligne->id = $parameters['line']->id;
			$facturebusinessitaligne->socid = $object->socid;
			$facturebusinessitaligne->fetch_optionals($facturebusinessitaligne->id,$extralabelslines);
			
			print $facturebusinessitaligne->showOptionals($extrafieldsline, 'view');	
		}
	
		if (! $error)
		{
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error display message';
			return -1;
		}
	}
	
	
	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function formAddObjectLine($parameters, &$object, &$action, $hookmanager)
	{
		global $db, $langs, $user;
		$error = 0; // Error counter
		
		if (in_array('invoicesuppliercard', explode(':', $parameters['context'])))
		{
			// Extrafields
			$extrafieldsline = new ExtraFields($db);
			$extralabelsline = $extrafieldsline->fetch_name_optionals_label($object->table_element_line);
			$array_option = $extrafieldsline->getOptionalsFromPost($extralabelsline, '');
			print $object->showOptionals($extrafieldsline,'edit');
		}
	
		if (! $error)
		{
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error display message';
			return -1;
		}
	}
	
	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function formEditProductOptions($parameters, &$object, &$action, $hookmanager)
	{
		global $db, $langs, $user;
		$error = 0; // Error counter

		if (in_array('invoicesuppliercard', explode(':', $parameters['context'])))
		{
			// Extrafields
			$extrafieldsline = new ExtraFields($db);
			$extralabelsline = $extrafieldsline->fetch_name_optionals_label($object->table_element_line);
			
			
			dol_include_once('/fourn/class/fournisseur.facture.class.php');
			$facturesupplierligne = new FactureSupplierLigne($db);
			$facturesupplierligne->id = $parameters['line']->rowid;
			$facturesupplierligne->fetch_optionals($facturesupplierligne->id,$extralabelslines);
				
		  print $facturesupplierligne->showOptionals($extrafieldsline, 'edit');			
		}
		
		if (in_array('invoicecard', explode(':', $parameters['context'])))
		{
			// Extrafields
			$extrafieldsline = new ExtraFields($db);
			$extralabelsline = $extrafieldsline->fetch_name_optionals_label($object->table_element_line);
				
			dol_include_once('/facturebusinessita/class/facturebusinessitaligne.class.php');
			$facturebusinessitaligne = new FactureBusinessITALigne($db);
			$facturebusinessitaligne->id = $parameters['line']->rowid;
			$facturebusinessitaligne->socid = $object->socid;
			$facturebusinessitaligne->fetch_optionals($facturebusinessitaligne->id,$extralabelslines);
		
			print $facturebusinessitaligne->showOptionals($extrafieldsline, 'edit');
		}
	
		if (! $error)
		{
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error display extrafields';
			return -1;
		}
	}
	
	
	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager)
	{
		global $db, $langs, $user;
		$langs->Load("facturebusinessita@facturebusinessita");
		$error = 0; // Error counter

		if (in_array('invoicecard', explode(':', $parameters['context'])))
		{
			if(($object->statut == 1) &&(FactureBusinessITA::isClienteConFatturaElettronica($object->socid)))
			{
				  // nell'url dell'indirizzo non metto il parametro '&amp;socid=' . $object->socid
				      print '<div class="inline-block divButAction"><a target="_blank" class="butAction" href="'
				        .(file_exists(DOL_DOCUMENT_ROOT.'/facturebusinessita')?DOL_URL_ROOT.'/facturebusinessita/view_fatturapa.php':DOL_URL_ROOT.'/custom/facturebusinessita/view_fatturapa.php')
				        . '?id=' . $object->id . '">' . $langs->trans("ButtonViewFatturaPA") . '</a></div>';
			}
			
		}
		
		if (! $error)
		{
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error display action button';
			return -1;
		} 
	}
	
	
	/**
	 * Write form in paiement.php to pay fornisseur facture with Ritenuta d'acconto
	 * @param array $parameters: array('facid'=>$facid, 'ref'=>$ref, 'objcanvas'=>$objcanvas);
	 * @param unknown $object: facture fornisseur
	 * @param string $action:
	 * @return number: OK > 0 and KO < 0
	 */
	function paymentsupplierinvoices($parameters, $object, $action) {
		global $db, $conf,$langs;
		
		if(!$conf->global->MAIN_MODULE_FACTUREBUSINESSITA) 
		{
			  $sql = 'SELECT f.rowid as facid, f.ref, f.ref_supplier, f.total_ht, f.total_ttc, f.datef as df';
			  $sql.= ', SUM(pf.amount) as am';
			  $sql.= ' FROM '.MAIN_DB_PREFIX.'facture_fourn as f';
			  $sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'paiementfourn_facturefourn as pf ON pf.fk_facturefourn = f.rowid';
			  $sql.= " WHERE f.entity = ".$conf->entity;
			  $sql.= ' AND f.fk_soc = '.$object->socid;
			  $sql.= ' AND f.paye = 0';
			  $sql.= ' AND f.fk_statut = 1';  // Statut=0 => non validee, Statut=2 => annulee
			  $sql.= ' GROUP BY f.rowid, f.ref, f.ref_supplier, f.total_ht, f.total_ttc, f.datef';
		}
		else // Estrazione dati pagamenti parziali ritenuta d'acconto
		{ 
			$sql = 'SELECT f.rowid as facid,f.rowid as ref,f.ref_supplier,f.total_ttc, f.datef as df';
			$sql .= ', sum(pf.amount) as am';
			$sql .= ', fc.ritenuta_acconto_facturebusinessita as total_ra, (f.total_ttc - COALESCE(fc.ritenuta_acconto_facturebusinessita,0)) as total_netto';
			$sql .= ', sum(if(b.fk_type = "RIT",pf.amount,0)) as pagato_ra';
			$sql .= ', sum(if(b.fk_type = "RIT",0,pf.amount)) as pagato_netto';
			$sql .= ' FROM '.MAIN_DB_PREFIX.'facture_fourn as f';
			$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'paiementfourn_facturefourn as pf ON f.rowid = pf.fk_facturefourn';
			$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'paiementfourn as p ON p.rowid = pf.fk_paiementfourn';
			$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'bank as b ON b.rowid = p.fk_bank';
			$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX .'facture_fourn_extrafields as fc ON f.rowid = fc.fk_object';
			$sql .= ' WHERE f.fk_soc = '.$object->socid;
			$sql .= ' AND f.entity = '.$conf->entity;
			$sql .= ' AND f.paye = 0';
			$sql .= ' AND f.fk_statut = 1';  // Statut=0 => non validee, Statut=2 => annulee
			$sql .= ' GROUP BY f.rowid,f.ref,f.ref_supplier,f.total_ttc,f.datef';
		}
		
		$resql = $db->query($sql);
		if ($resql)
		{
			$num = $db->num_rows($resql);
			if ($num > 0)
			{
				$i = 0;
				print '<br>';
	
				// Diciannove Soc. Coop: Estrazione dati pagamenti parziali ritenuta d'acconto
				if(!$conf->global->MAIN_MODULE_FACTUREBUSINESSITA) 
				{
					print $langs->trans('Invoices').'<br>';
					print '<table class="noborder" width="100%">';
					print '<tr class="liste_titre">';
					print '<td>'.$langs->trans('Ref').'</td>';
					print '<td>'.$langs->trans('RefSupplier').'</td>';
					print '<td align="center">'.$langs->trans('Date').'</td>';
					print '<td align="right">'.$langs->trans('AmountTTC').'</td>';
					print '<td align="right">'.$langs->trans('AlreadyPaid').'</td>';
	
				}
				else {
					// Diciannove Soc. Coop: Intestazione colonne tabella pagamenti
					print $langs->trans('Invoices').'<br>';
					print '<table class="noborder" width="100%">';
					print '<tr class="liste_titre">';
					print '<td>'.$langs->trans('Ref').'</td>';
					print '<td>'.$langs->trans('RefSupplier').'</td>';
					print '<td align="center">'.$langs->trans('Date').'</td>';
					 
					print '<td align="right">'.$langs->trans('Netto').'</td>';
					print '<td align="right">'.$langs->trans('Ritenuta').'</td>';
					print '<td align="right">'.$langs->trans('Totale').'</td>';
					 
					print '<td align="right">'.$langs->trans('Netto pagato').'</td>';
					print '<td align="right">'.$langs->trans('Ritenuta pagata').'</td>';
					print '<td align="right">'.$langs->trans('AlreadyPaid').'</td>';
					 
					print '<td align="right">'.$langs->trans('Netto da pagare').'</td>';
					print '<td align="right">'.$langs->trans('Ritenuta da pagare').'</td>';
				}
	
				print '<td align="right">'.$langs->trans('RemainderToPay').'</td>';
				print '<td align="center">'.$langs->trans('Amount').'</td>';
				print '</tr>';
	
				$var=True;
				$total=0;
				$total_ttc=0;
				$totalrecu=0;
				while ($i < $num)
				{
					$objp = $db->fetch_object($resql);
					$var=!$var;
					print '<tr '.$bc[$var].'>';
					print '<td><a href="fiche.php?facid='.$objp->facid.'">'.img_object($langs->trans('ShowBill'),'bill').' '.$objp->ref;
					print '</a></td>';
					print '<td>'.$objp->ref_supplier.'</td>';
					if ($objp->df > 0 )
					{
						print '<td align="center">';
						print dol_print_date($db->jdate($objp->df)).'</td>';
					}
					else
					{
						print '<td align="center"><b>!!!</b></td>';
					}
	
					// Diciannove Soc. Coop: campi da visualizzare
					if(!$conf->global->MAIN_MODULE_FACTUREBUSINESSITA) 
					{
						print '<td align="right">'.price($objp->total_ttc).'</td>';
						print '<td align="right">'.price($objp->am).'</td>';
						print '<td align="right">'.price($objp->total_ttc - $objp->am).'</td>';
					}
					else 
					{
						print '<td align="right" class="p_totale">'.price($objp->total_netto).'</td>';
						print '<td align="right" class="p_totale">'.price($objp->total_ra).'</td>';
						print '<td align="right" class="p_totale">'.price($objp->total_ttc).'</td>';
	
						print '<td align="right" class="p_pagato">'.price($objp->pagato_netto).'</td>';
						print '<td align="right" class="p_pagato">'.price($objp->pagato_ra).'</td>';
						print '<td align="right" class="p_pagato">'.price($objp->am).'</td>';
	
						print '<td align="right" class="p_dapagare">'.price($objp->total_netto - $objp->pagato_netto).'</td>';
						print '<td align="right" class="p_dapagare">'.price($objp->total_ra - $objp->pagato_ra).'</td>';
						print '<td align="right" class="p_dapagare">'.price($objp->total_ttc - $objp->am).'</td>';
					}
	
					print '<td align="center">';
					$namef = 'amount_'.$objp->facid;
					print '<input type="text" size="8" name="'.$namef.'" value="'.GETPOST($namef).'">';
					print "</td></tr>\n";
					$total+=$objp->total_ht;
					$total_ttc+=$objp->total_ttc;
					$totalrecu+=$objp->am;
					$i++;
				}
				if ($i > 1)
				{
					// Diciannove Soc. Coop:  Print total
					$colspan = $conf->global->MAIN_MODULE_FACTUREFOURNITA || $conf->global->MAIN_MODULE_FACTUREBUSINESSITA ? 'colspan="3"' : '';
					print '<tr class="liste_total">';
					print '<td colspan="3" align="left">'.$langs->trans('TotalTTC').':</td>';
					print '<td '.$colspan .' align="right"><b>'.price($total_ttc).'</b></td>';
					print '<td '.$colspan .' align="right"><b>'.price($totalrecu).'</b></td>';
					print '<td '.$colspan .' align="right"><b>'.price($total_ttc - $totalrecu).'</b></td>';
					print '<td '.$colspan .' align="center">&nbsp;</td>';
					print "</tr>\n";
				}
				print "</table>\n";
			}
			$db->free($resql);
		}
		else
		{
			dol_print_error($db);
			return -1;
		}
		return 1;
	}
	
	
	
	
	
}
