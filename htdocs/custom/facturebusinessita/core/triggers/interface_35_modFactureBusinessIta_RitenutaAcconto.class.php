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
 * 	\file		core/triggers/interface_30_modFactureBusinessITA_RitenutaAcconto.class.php
 * 	\ingroup	facturebusinessita
 * 	\brief		Sample trigger
 * 	\remarks	You can create other triggers by copying this one
 * 				- File name should be either:
 * 					interface_30_modFactureBusinessITA_RitenutaAcconto.class.php
 * 				- The file must stay in core/triggers
 * 				- The class name must be InterfaceRitenutaAcconto
 * 				- The constructor method must be named InterfaceRitenutaAcconto
 * 				- The name property name must be RitenutaAcconto
 */

require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';


// Extend DolibarrTriggers from Dolibarr 3.7
$dolibarr_version = versiondolibarrarray();
if ($dolibarr_version[0] < 3 || ($dolibarr_version[0] == 3 && $dolibarr_version[1] < 7)) { // DOL_VERSION < 3.7
	/**
	 * Class RitenutaAcconto
	 */
	abstract class RitenutaAcconto
	{
	}
} else {
	/**
	 * Class RitenutaAcconto
	 */
	abstract class RitenutaAcconto extends DolibarrTriggers
	{
	}
}

/**
 * Class InterfaceRitenutaAcconto
 */
class InterfaceRitenutaAcconto extends RitenutaAcconto
{
	/**
	 * @var DoliDB Database handler
	 */
	private $db;

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;

		$this->name = preg_replace('/^Interface/i', '', get_class($this));
		$this->family = "module";
		$this->description = "Triggers of this module manages Ritenuta Acconto for italian supplier invoice.";
		// 'development', 'experimental', 'dolibarr' or version
		$this->version = 'dolibarr';
		$this->picto = 'facturebusinessita@facturebusinessita';
	}

	/**
	 * Trigger name
	 *
	 * @return string Name of trigger file
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Trigger description
	 *
	 * @return string Description of trigger file
	 */
	public function getDesc()
	{
		return $this->description;
	}

	/**
	 * Trigger version
	 *
	 * @return string Version of trigger file
	 */
	public function getVersion()
	{
		global $langs;
		$langs->load("admin");

		if ($this->version == 'development') {
			return $langs->trans("Development");
		} elseif ($this->version == 'experimental')

				return $langs->trans("Experimental");
		elseif ($this->version == 'dolibarr') return DOL_VERSION;
		elseif ($this->version) return $this->version;
		else {
			return $langs->trans("Unknown");
		}
	}

	/**
	 * Compatibility trigger function for Dolibarr < 3.7
	 *
	 * @param int           $action Trigger action
	 * @param CommonObject  $object Object trigged from
	 * @param User          $user   User that trigged
	 * @param Translate     $langs  Translations handler
	 * @param Conf          $conf   Configuration
	 * @return int                  <0 if KO, 0 if no triggered ran, >0 if OK
	 * @deprecated Replaced by DolibarrTriggers::runTrigger()
	 */
	public function run_trigger($action, $object, $user, $langs, $conf)
	{
		return $this->runTrigger($action, $object, $user, $langs, $conf);
	}

	/**
	 * Function called when a Dolibarrr business event is done.
	 * All functions "runTrigger" are triggered if file
	 * is inside directory core/triggers
	 *
	 * @param string    $action Event action code
	 * @param Object    $object Object
	 * @param User      $user   Object user
	 * @param Translate $langs  Object langs
	 * @param Conf      $conf   Object conf
	 * @return int              <0 if KO, 0 if no triggered ran, >0 if OK
	 */
	public function runTrigger($action, $object, $user, $langs, $conf)
	{
		$lineid = GETPOST('lineid');
		$action_interna = GETPOST('action');
		$id=GETPOST('id'); 
		
		if (($action_interna == 'addline') || ($action_interna == 'confirm_clone') || ($action_interna == 'update_line')) 
		{
			$object->lineid = $object->rowid;		// serve in fase di creazione e di cancellazione per far salvare i campi facture_ita
			$object->action = $action_interna;	// addline o edit_line
			$object->original_factureid = GETPOST('id');	// mi serve con il CLONE per sapere quale è la fattura originale
		}
		
		// ho la stessa action sia per una riga nuova che per una modificata
		if (($action == 'LINEBILL_SUPPLIER_CREATE') 	|| 	($action == 'LINEBILL_SUPPLIER_UPDATE') 	||	($action == 'LINEBILL_SUPPLIER_DELETE')  	||		($action == 'BILL_SUPPLIER_DELETE')) 							
		{
			$this->db->begin();
			switch($action)
			{
				case 'LINEBILL_SUPPLIER_UPDATE': 
				case 'LINEBILL_SUPPLIER_CREATE':
					$ret = $this->updateRitenutaAccontoRigaFP($object); 
					break;
				case 'BILL_SUPPLIER_DELETE':
					$ret = $this->deleteAllRitenutaAccontoRigaFP($id);
					break;
				case 'LINEBILL_SUPPLIER_DELETE':
					$ret = $this->deleteRitenutaAccontoRigaFP($lineid);
					break;
				default:
					return 0;
			}
		
			 
			if($ret)
				$ret =  $this->updatePriceFatturePassive($object,$action,$lineid);		// aggiornamento totali fattura
			
			if($ret) 
			{
				$this->db->commit();
				return 1;
			}
			else
			{
				$this->db->rollback();
				return -1;
			}
		}	 
	return 0;
	}	// close function
		
	
	
	
	/**
	 * Salva il valore della ritenuta della singola riga: valore in euro più altri dati utili della riga
	 * @param object: oggetto fattura con le linee
	 */
	function updateRitenutaAccontoRigaFP($obj_facture_fourn_ligne)
	{
		dol_include_once('/facturebusinessita/class/facturebusinessita.class.php');
		$fbi = new FactureBusinessITA($this->db);
		
		$action = $obj_facture_fourn_ligne->action;
		
		//echo "<pre>"; print_r($obj_facture_fourn_ligne); exit;
		
		// controllo l'azione
		if(($action  == 'update_line') || ($action == 'addline') || ($action == 'confirm_clone')) // update line or add new line
		{			
			$DESCRIZIONE_ALIQUOTA_ACCONTO_FBI = $obj_facture_fourn_ligne->array_options['options_fk_ritenuta_acconto_facturebusinessita'];
			
			$fbi->imponibile_riga = $obj_facture_fourn_ligne->total_ht;			 
			$fbi->id_facture	 		= $obj_facture_fourn_ligne->fk_facture_fourn; 		// id fattura fornitori modificata
			$fbi->id_facture_det 	= $obj_facture_fourn_ligne->rowid; 								// id riga modificata
		}
		
		/////////////////////////////////////////// add/update/clone facture
		if((($action == 'addline') || ($action == 'add') || ($action == 'update_line') || ($action == 'confirm_clone')) && ($obj_facture_fourn_ligne->lineid != ''))
		{
			// carico il valore della percentuale selezionata della RA (es: 0.2 - 0.3 - 0)
			if(isset($DESCRIZIONE_ALIQUOTA_ACCONTO_FBI) && ($DESCRIZIONE_ALIQUOTA_ACCONTO_FBI >= 0) )
			{ 
				$fbi->valore_aliquota 	= $fbi->fetch_aliquota_ritenuta_acconto($DESCRIZIONE_ALIQUOTA_ACCONTO_FBI);
				$fbi->ritenuta_acconto 	= $fbi->valore_aliquota * $fbi->imponibile_riga;	// valore RA in Euro della singola riga
			}
			else
			{
				$fbi->valore_aliquota = 0;
				$fbi->ritenuta_acconto = 0;
			}
			
			
			$ret = $fbi->create_ritenuta_acconto($user);	// Salvo i valori nella tabella apposita della ritenuta d'acconto
			if ($ret < 0)
			{
				dol_print_error($this->db);
				return -1;
			}
		}
		return 1;
	}
	
	
	/**
	 * Elimino la riga dalla tabella facture_fourn_ritenuta_acconto
	 * @param int $id_line_delete: fk_facture_fourn_det da eliminare
	 */
	
	function deleteRitenutaAccontoRigaFP($id_line_delete) 	{
	
		global $langs, $conf;
		
		$sql = 'DELETE FROM '.MAIN_DB_PREFIX.'facturebusinessita_ritenuta_acconto ';
		$sql .= ' WHERE fk_facture_fourn_det = '.$id_line_delete.';';
	
		dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);

		if ($this->db->query($sql) < 0)
		{
			$error++;
			$this->errors=$interface->errors;
			$this->db->rollback();
			return -1;
		}
		else 
		{
			$this->db->commit();
			return 1;
		}
			
	}
	
	/**
	 * Elimino le riga dalla tabella facture_fourn_ritenuta_acconto dopo aver eliminato la fattura
	 * @param int $id_line_delete: fk_facture_fourn_det da eliminare
	 */
	
	function deleteAllRitenutaAccontoRigaFP($id_facture_delete) 	{
	
		$sql = 'DELETE FROM '.MAIN_DB_PREFIX.'facturebusinessita_ritenuta_acconto ';
		$sql .= ' WHERE fk_facture_fourn = '.$id_facture_delete.';';
	
		dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
	
		if (! $resql)
		{
			dol_print_error($this->db);
			return 0;
		}
		else
			return 1;
	}

	
	
	
	
	

	/**
	 * Aggiorna il netto da pagare e l'importo totale della fattura dopo l'aggioranmento della ritenuta d'acconto
	 *  
	 * @param unknown $object: supplier invoice line 
	 * @param unknown $act
	 * @param string $id_riga_delete
	 * @return number
	 */
	function updatePriceFatturePassive($object, $action, $id_riga_delete='')
	{
		if(!empty($object->lineid) || !empty($id_riga_delete)) 	// da verificare
		{
			$id_facture = !empty($object->original_factureid) ? $object->original_factureid : $object->fk_facture_fourn; 
	
			//echo "<br>ID_FATTURA: ".$id_facture ." action: " .$action; //exit;
			//echo "<pre>";	print_r($object); exit;
	
			// 2. Calcolo il totale della ritenuta della fattura
			$sql = 'SELECT SUM(ritenuta_acconto_valore) as totale_ritenuta
		      		FROM '.MAIN_DB_PREFIX .'facturebusinessita_ritenuta_acconto
		      		WHERE  fk_facture_fourn = '.$id_facture.'
		      		GROUP BY fk_facture_fourn';
				
	    dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
	    $resql = $this->db->query($sql);
		  $obj = $this->db->fetch_object($resql);
	
		  $ritenuta_acconto_totale = ($obj->totale_ritenuta > 0) ? $obj->totale_ritenuta : 0;
		    	 
		  // 3. Carico il totale della fattura appena aggiornata da update_price
			// se vengo dall'eliminazione della riga però devo caricare il totale della fattura ed eliminare il totale della riga perchè
			// non è ancora passato nell'update price
	
			// se ho appena eliminato una riga e ho la riga eliminata
		  $f = new FactureFournisseur($this->db);
		  $f->fetch($id_facture);
		  $result=$f->update_price('','auto','');
		  
		  // echo "<pre>"; print_r($f);
		  
			if($action == 'LINEBILL_SUPPLIER_DELETE' && !empty($id_riga_delete))
			{
		   	$netto_da_pagare = $f->total_ttc - $ritenuta_acconto_totale;
		  }
	    else
    	{			  
		    $sql = 'SELECT total_ttc as totale_fattura	FROM '.MAIN_DB_PREFIX .'facture_fourn		WHERE  rowid = '.$id_facture;
			  
		    dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
		    $resql = $this->db->query($sql);
		    $obj = $this->db->fetch_object($resql);
			    	 
		    $netto_da_pagare = $obj->totale_fattura - $ritenuta_acconto_totale;
		    //echo "<br>NETTO DA PAGARE: $netto_da_pagare"; //exit;	//echo "<pre>";print_r($object);
    	}
    	
    	// 4. aggiorno il valore della ritenuta d'acconto scrivendolo nel campo facturebusiness_ita apposito
    	$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'facture_fourn_extrafields (netto_da_pagare_facturebusinessita, ritenuta_acconto_facturebusinessita, fk_object)
    					VALUES (' .price2num($netto_da_pagare) .', '
	    									.price2num($ritenuta_acconto_totale) .', '
	    									.$id_facture 	.') ON DUPLICATE KEY UPDATE
	    									ritenuta_acconto_facturebusinessita 	= ' .price2num($ritenuta_acconto_totale) .',
	    									netto_da_pagare_facturebusinessita 		= ' .price2num($netto_da_pagare) .', 
	    									fk_object 														= ' .$id_facture;
	    									dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);

    	//echo "<br>LAST QUERY: $sql"; exit;
    	$resql = $this->db->query($sql);
	    if ($resql)	return 1;
	    else return -1;
		}
		return 1;	// per l'update price chiamato alla creazione della fattura
  }

	

}	// close class