<?php
/* Copyright (C) 2005-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2014 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2015			 Claudio Aschieri			<c.aschieri@19.coop>
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
 *  \file       htdocs/core/triggers/interface_90_modFactureBusinessITA_ValidateFacture.class.php
 *  \ingroup    core
 *  \brief      Validate invoice and set new ref number for electronic billing
 *  \remarks    
 */
dol_include_once('/facturebusinessita/class/facturebusinessita.class.php');

/**
 *  Class of triggers for demo module
 */
class InterfaceValidateFacture
{
    var $db;
    
    /**
     *   Constructor
     *
     *   @param		DoliDB		$db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;
    
        $this->name = preg_replace('/^Interface/i','',get_class($this));
        $this->family = "facturebusinessita";
        $this->description = "Validate invoice, create xml file for electronic billing and set correct invoice ref number";
        $this->version = 'dolibarr';            // 'development', 'experimental', 'dolibarr' or version
        $this->picto = 'facturebusinessita@facturebusinessita';
    }
    
    
    /**
     *   Return name of trigger file
     *
     *   @return     string      Name of trigger file
     */
    function getName()
    {
        return $this->name;
    }
    
    /**
     *   Return description of trigger file
     *
     *   @return     string      Description of trigger file
     */
    function getDesc()
    {
        return $this->description;
    }

    /**
     *   Return version of trigger file
     *
     *   @return     string      Version of trigger file
     */
    function getVersion()
    {
        global $langs;
        $langs->load("admin");

        if ($this->version == 'development') return $langs->trans("Development");
        elseif ($this->version == 'experimental') return $langs->trans("Experimental");
        elseif ($this->version == 'dolibarr') return DOL_VERSION;
        elseif ($this->version) return $this->version;
        else return $langs->trans("Unknown");
    }
    
    /**
     *      Function called when a Dolibarrr business event is done.
     *      All functions "run_trigger" are triggered if file is inside directory htdocs/core/triggers
     *
     *      @param	string		$action		Event action code
     *      @param  Object		$object     Object
     *      @param  User		$user       Object user
     *      @param  Translate	$langs      Object langs
     *      @param  conf		$conf       Object conf
     *      @return int         			<0 if KO, 0 if no triggered ran, >0 if OK
     */
	function run_trigger($action,$object,$user,$langs,$conf)
    {
        if ($action == 'BILL_VALIDATE')	// convalida fattura
        {
        	dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        	return $this->validateFactureFactureBusinessITA($action,$object,$user,$langs,$conf);
        }
        else if ($action == 'BILL_UNVALIDATE')         // bozza fattura
        {
        	dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        	return $this->unvalidateFactureFactureBusinessITA($action,$object,$user,$langs,$conf);
        }
        else if ($action == 'LINEBILL_INSERT')         // inserimento riga di fattura attiva
        {
        	dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        	return $this->validateEsenzioneIVA($action,$object,$user,$langs,$conf);
        }
        else if ($action == 'LINEBILL_UPDATE')         //  modifica riga di fattura attiva
        {
        	dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        	return $this->validateEsenzioneIVA($action,$object,$user,$langs,$conf);
        }
        else if ($action == 'LINEBILL_SUPPLIER_UPDATE')	// aggiornamento riga fattura passiva
        {
        	dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        	return $this->validateEsenzioneIVA($action,$object,$user,$langs,$conf);
        }
        else if ($action == 'LINEBILL_SUPPLIER_CREATE')         // inserimento riga fattura passiva
        {
        	dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        	return $this->validateEsenzioneIVA($action,$object,$user,$langs,$conf);
        }
		
		return 0;
    }
    
    

    
    /**
     *      Validate invoice line: check if IVA = 0 when esenzioni is not empty 
     *       
     *      @param	string		$action		Event action code
     *      @param  Object		$object   Object facture
     *      @param  User		$user       Object user
     *      @param  Translate	$langs    Object langs
     *      @param  conf		$conf       Object conf
     *      @return int         			<0 if KO, 0 if no triggered ran, >0 if OK
     */
  function validateEsenzioneIVA($action,$object,$user,$langs,$conf) 
  {
  	$tva = $object->tva_tx;
  	$esenzione = $object->array_options['options_fk_iva_esenzioni_facturebusinessita'];
  	$langs->load("facturebusinessita@facturebusinessita");

  	
  	if($tva != 0) 	// se l'iva è selezionata
  	{
  		if(!empty($esenzione))	// e anche l'esenzione è selezionata
  		{
  			$object->error = $langs->trans("ErrorEsenzioneIVA");
  			return -1;
  		}
  	}
  	
  	if(!empty($esenzione) && ($object->action != 'confirm_clone'))
  	{
  		switch ($conf->global->FACTUREBUSINESSITA_ESENZIONE_TO_PDF)
  		{
	  		case 'S':
	  			$active = true;
	  			break;
	  		case 'F':
	  			/* mi serve l'oggetto fattura per recuperare l'id :( */
	  			$fatt = new Facture($this->db);
	  			$fatt->fetch($object->fk_facture);
	  			if(FactureBusinessITA::isClienteConFatturaElettronica($fatt->socid)) $active = true;
	  			else $active = false;
	  			break;
	  		default:
	  			$active = false;
	  			break;
	  	}
  	
	   /* Questa operazione (Se attiva) va fatta sempre ad ogni salvataggio
	  	* se ho selezionato una esenzione
	  	* Questa aggiunta ha una seconda controparte. ovvero la stringa aggiunta in questo passaggio
	  	* va rimossa quando si edita nuovamente la riga
	  	* */
	  	if($active){
	  		
				/* Recupero la descrizione dell'esenzion */  		
	  		$sql = "SELECT if(description!='',description,label) as description 
	  			   	  FROM ".MAIN_DB_PREFIX."facturebusinessita_iva_esenzioni  
	  			   	  WHERE rowid = " .$esenzione;
	  		dol_syslog("template_eFattura::select sql=".$sql);
	  		$resql = $this->db->query($sql);
	  		$obj = $this->db->fetch_object($resql);
	  		$new_text = "\n\n".$obj->description;
	
	  		/* Faccio una query di update per aggiornale la descrizione della riga */
	  		if(!empty($obj->description)){
	  				
		  		$sql = 'UPDATE '.MAIN_DB_PREFIX.'facturedet 
		  				    SET description = concat(description,"'.$this->db->escape($new_text).'")
		    					WHERE rowid = ' .$object->rowid;
		  		dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
		  		
		  		$resql = $this->db->query($sql);
		  		if (!$resql)	return -1;
	  		}
	  	 }
  	}
  	
		return 1;
  }  
    
    
    /**     
     *      Validate invoice, create xml file for electronic billing and set correct invoice ref number
     *
     *      @param	string		$action		Event action code
     *      @param  Object		$object   Object facture
     *      @param  User		$user       Object user
     *      @param  Translate	$langs    Object langs
     *      @param  conf		$conf       Object conf
     *      @return int         			<0 if KO, 0 if no triggered ran, >0 if OK
     */
  function validateFactureFactureBusinessITA($action,Facture $object,$user,$langs,$conf) 
  {
  	$hidedetails=''; $hidedesc=''; $hideref=''; $ret=''; $error=0;
  	
  	$socid = $object->socid;
  	if(!empty($socid))
  	{
  		dol_include_once('facturebusinessita/class/facturebusinessita.class.php');
  		dol_include_once('facturebusinessita/core/modules/facturebusinessita/modules_facturebusinessita.php');
	  	
	  	// genero l'xml della fattura
	  	if(FactureBusinessITA::isClienteConFatturaElettronica($socid) && !empty($conf->global->FACTUREBUSINESSITA_USE_FE) && !empty($conf->global->FACTUREBUSINESSITA_ADDON_PDF))
	  	{
	  		// eseguo l'update del facnumber solo se ho selezionato uno dei due template personalizzati
	  		if($conf->global->FACTUREBUSINESSITA_ADDON == 'mod_facturebusinessita_ironman' || $conf->global->FACTUREBUSINESSITA_ADDON == 'mod_facturebusinessita_thor' )
	  		{
	  			if(empty($conf->global->MAIN_MODULE_AGENDA)) 
	  			{
	  				setEventMessage($langs->trans("ErrorFailToCreateFileXMLAgendaModuleRequired"), 'errors');
	  				return -1;
	  			}
	  			// controllo la tabella action per capire se è la prima convalida
	  			$sql = "SELECT id FROM " .MAIN_DB_PREFIX .'actioncomm WHERE elementtype = "invoice" AND fk_element = ' .$object->id;
	  			$resql=$this->db->query($sql);
	  			$num = $this->db->num_rows($resql);
	  				
	  			if($num == 1) // aggiorno la numerazione solo se è la prima convalida
	  			{
	  				if($object->type == 2){
					//ServiceTech numerazione nota di credito elettronica	
	  			    $sql ="SELECT MAX(SUBSTRING(facnumber, -9,4)) FROM ".MAIN_DB_PREFIX ."facture WHERE facnumber like 'FE%".date('Y', time())."' or facnumber like 'NE%".date('Y', time())."'";
	  			    $resql=$this->db->query($sql);
	  			    $obj = $this->db->fetch_row($resql);
	  			    $num = str_pad($obj[0],4, 0, STR_PAD_LEFT);
       	            $ref = 'NE '.$num.'/'.date('Y', time());
				    }else{
					//ServiceTech numerazione fattura elettronica	
					$sql ="SELECT MAX(SUBSTRING(facnumber, -9,4)) FROM ".MAIN_DB_PREFIX ."facture WHERE facnumber like 'FE%".date('Y', time())."' or facnumber like 'NE%".date('Y', time())."'";
	  			    $resql=$this->db->query($sql);
	  			    $obj = $this->db->fetch_row($resql);
	  			    $num = str_pad($obj[0],4, 0, STR_PAD_LEFT);
       	            $ref = 'FE '.$num.'/'.date('Y', time());
					
					
					//$ref = FactureBusinessITA::getNextNumRef($object->client,'next');
					}
	  				
	  				
	  				
	  				 
	  				
	  				
	  				if($ref)
	  				{
	  					
	  					 //nota di credito elettronica
	  					//$ref = str_replace('FE','NE',$ref);
	  					
	  					$sql = "UPDATE ".MAIN_DB_PREFIX."facture SET facnumber='".$ref ."' WHERE rowid = ".$object->id;
	  					dol_syslog(get_class($this)."::setInvoiceFacnumber sql=".$sql);
	  					$resql=$this->db->query($sql);
	  					 
	  					if (! $resql)
	  					{
	  						dol_syslog(get_class($this)."::setInvoiceFacnumber update error sql=".$sql, LOG_ERR);
	  						dol_print_error($this->db);
	  						return -1;
	  					}
	  					else 
	  					{
	  						$object->facnumber = $ref;
	  						$object->ref = $ref;
	  					}
	  					
	  				}
	  				else
	  					return -1;
	  			}
	  		}
	  		
	  		
	  		// creo il file xml perchè l'aggiornamento del ref è andato bene
	  		$object->modelpdf = $conf->global->FACTUREBUSINESSITA_ADDON_PDF;
	  		$ret = facturebusinessita_pdf_create($this->db, $object, $object->modelpdf, $langs, $hidedetails, $hidedesc, $hideref);
	  		
	  		// aumento il progressivo generico di 1
	  		if($ret > 0)
	  		{
	  			$langs->load("facturebusinessita@facturebusinessita");
	  			setEventMessage($langs->trans("FileXMLWasGenerate"));
	  			
	  			$constname='FACTUREBUSINESSITA_PROGRESSIVO_FE';
	  			$constvalue=$conf->global->FACTUREBUSINESSITA_PROGRESSIVO_FE;
	  			$constvalue++;
	  			$consttype='chaine';
	  			$constnote='';
	  			
	  			$res=dolibarr_set_const($this->db,$constname,$constvalue,$consttype,0,$constnote,$conf->entity);
	  		
	  			// sistemo il riferimento della fattura in accordo con la configurazione del modulo
	  			if($res) 
	  			{
	  				
	  			}
	  			else 
	  				$error++;
	  		
	  		}	 
	  			else 
	  				$error++;
	  	}
  	}

  	
  		return (!$error) ? 1 : -1;
  }  
  
  /**
   *      unValidate invoice and delete xml file
   *
   *      @param	string		$action		Event action code
   *      @param  Object		$object   Object facture
   *      @param  User		$user       Object user
   *      @param  Translate	$langs    Object langs
   *      @param  conf		$conf       Object conf
   *      @return int         			<0 if KO, 0 if no triggered ran, >0 if OK
   */
  function unvalidateFactureFactureBusinessITA($action,$object,$user,$langs,$conf)
  {
  	$hidedetails=''; $hidedesc=''; $hideref='';
  	
  	$socid = $object->socid;
  	if(!empty($socid))
  	{
  		dol_include_once('facturebusinessita/class/facturebusinessita.class.php');
  		dol_include_once('facturebusinessita/core/modules/facturebusinessita/modules_facturebusinessita.php');
  		
  		// genero l'xml della fattura
  		if(FactureBusinessITA::isClienteConFatturaElettronica($socid) && !empty($conf->global->FACTUREBUSINESSITA_USE_FE) && !empty($conf->global->FACTUREBUSINESSITA_ADDON_PDF))
  		{
				require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
				$langs->load("facturebusinessita@facturebusinessita");
				
				$objectref = dol_sanitizeFileName($object->ref);
				$dir = $conf->facture->dir_output;
				$file = $dir . "/" . $objectref . "/" .'*.xml';
				
				$ret = dol_delete_file($file, 0, 0, 0, $object);
				
				if ($ret)
					setEventMessage($langs->trans("FileXMLWasRemoved"));
				else
					setEventMessage($langs->trans("ErrorFailToDeleteFileXML"), 'errors');
  		}
	  }
  	 
  	return 1;
  }

}
?>
