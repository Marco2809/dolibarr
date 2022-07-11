<?php
/*
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
 * 	\file			class/facturebusinessita.class.php
 * 	\ingroup	facturebusinessita
 * 	\brief		Class Facture Business ITA
 */

class FactureBusinessITA  extends CommonObject
{

	protected $db; 
	public $error; 
	public $errors = array(); 
	//public $element='';	
	//public $table_element='';	
	public $id_facture;
	public $id_facture_det;
	public $valore_aliquota;
	public $imponibile_riga;
	public $ritenuta_acconto;

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
	 * Il cliente ha la fatturazione elettronica attiva?
	 * 
	 * @param int $socid: id societe
	 * return 1 if client has electronic billing, 0 if not
	 */
	public static function isClienteConFatturaElettronica($socid='') 
	{
		global $db;
		if(!empty($socid)) 
		{
			$societe_static = new Societe($db);
			$societe_static->fetch($socid);
			return ($societe_static->array_options['options_attiva_xml_facturebusinessita'] == "Si") ? true : false;	// è un cliente con fattura elettronica
		}
		else 
			return 0;
	}
	
	/**
	 *      Return next reference of customer invoice not already used (or last reference)
	 *      according to numbering module defined into constant FACTURE_ADDON
	 *
	 *      @param	   Society		$soc		object company
	 *      @param     string		$mode		'next' for next value or 'last' for last value
	 *      @return    string					free ref or last ref
	 */
	public static function getNextNumRef($soc,$mode='next')
	{
		global $conf, $db, $langs;
		$langs->load("bills");
	
		// Clean parameters (if not defined or using deprecated value)
		if (empty($conf->global->FACTUREBUSINESSITA_ADDON)) $conf->global->FACTURE_ADDON='mod_facturebusinessita_thor';
		
		$mybool=false;
	
		$file = $conf->global->FACTUREBUSINESSITA_ADDON.".php";
		$classname = $conf->global->FACTUREBUSINESSITA_ADDON;
		
		// Include file with class
		foreach ($conf->file->dol_document_root as $dirroot)
		{
			$dir = $dirroot."/facturebusinessita/core/modules/facturebusinessita/";
			// Load file with numbering class (if found)
			$mybool|=@include_once $dir.$file;
		}
	
		if (! $mybool)
		{
			dol_print_error('',"Failed to include file ".$file);
			return '';
		}
	
		$obj = new $classname();
		$numref = "";
		$numref = $obj->getNumRef($soc,$this,$mode);
	
		if ($numref != "")
		{
			return $numref;
		}
		else
		{
			//dol_print_error($db,get_class($this)."::getNextNumRef ".$obj->error);
			return false;
		}
	}
	
	
	/**
	 * Create object into database
	 *
	 * 	@param		User	$user		User that create
	 * 	@param		int		$notrigger	0=launch triggers after, 1=disable triggers
	 * 	@return		int					<0 if KO, Id of created object if OK
	 */
	public function create_ritenuta_acconto($user, $notrigger = 0)
	{
		global $conf, $langs;
		$error = 0;
				
		// Insert/update request
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "facturebusinessita_ritenuta_acconto (fk_facture_fourn, fk_facture_fourn_det, valore_aliquota, imponibile_riga, ritenuta_acconto_valore";
		$sql.= ") VALUES (";
		$sql.= " '" . $this->id_facture . "',";
		$sql.= " '" . $this->id_facture_det . "',";
		$sql.=   		  price2num($this->valore_aliquota) .", ";
		$sql.=   		  price2num($this->imponibile_riga) .", ";
		$sql.=   		  price2num($this->ritenuta_acconto) ." ";
		$sql.= ") ON DUPLICATE KEY UPDATE
					    	ritenuta_acconto_valore = " .price2num($this->ritenuta_acconto) 			.",
					    	valore_aliquota					= " .price2num($this->valore_aliquota)				.",
					    	imponibile_riga					=	"	.price2num($this->imponibile_riga)				.",
					    	fk_facture_fourn 				= '" .$this->id_facture 			."',
					    	fk_facture_fourn_det  	= '" .$this->id_facture_det		."'";
	
		$this->db->begin();

		dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors[] = "Error " . $this->db->lasterror();
		}

		// Commit or rollback
		if ($error) 
		{
			foreach ($this->errors as $errmsg) 
			{
				dol_syslog(__METHOD__ . " " . $errmsg, LOG_ERR);
				$this->error.=($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback();

			return -1 * $error;
		} 
		else 
		{
			$this->db->commit();
			return $this->id;		// 0 quando fa l'update
		}
	}

	/**
	 * Load ritenuta acconto in memory from database
	 *
	 * 	@param		int		$id	Id object
	 * 	@return		int			<0 if KO, >0 if OK
	 */
	public function fetch_aliquota_ritenuta_acconto($id)
	{
		global $langs,$conf;
		
		if($id == 0) return 0;	// se id= 0 è stato selezionato vuoto
		
		$sql = "SELECT a.value as value";
		$sql.= " FROM " . MAIN_DB_PREFIX . "facturebusinessita_aliquota_ritenuta_acconto_" .$conf->entity ." as a";
		$sql.= " WHERE a.rowid = " . $id;

		
		dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);

				$val = $obj->value;
			}
			$this->db->free($resql);
			return $val;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(__METHOD__ . " " . $this->error, LOG_ERR);

			return -1;
		}
	}


	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * 	@return		void
	 */
	public function initAsSpecimen()
	{
		$this->id_facture = 1;
		$this->id_facture_det = 1;
		$this->valore_aliquota = 0.2;
		$this->imponibile_riga = 1000.00;
		$this->ritenuta_acconto = 200.00;
	}
}
