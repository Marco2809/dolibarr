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
 * 	\defgroup	facturebusinessita	FactureBusinessITA module
 * 	\brief		FactureBusinessITA module descriptor.
 * 	\file		core/modules/modFactureBusinessITA.class.php
 * 	\ingroup	facturebusinessita
 * 	\brief		Description and activation file for module FactureBusinessITA
 */
include_once DOL_DOCUMENT_ROOT . "/core/modules/DolibarrModules.class.php";

/**
 * Description and activation class for module FactureBusinessITA
 */
class modFactureBusinessITA extends DolibarrModules
{

	/**
	 * 	Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * 	@param	DoliDB		$db	Database handler
	 */
	public function __construct($db)
	{
		global $langs, $conf;

		$this->db = $db;

		$this->numero = 190800;	// Id for module (must be unique).
		$this->rights_class = 'facturebusinessita';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		$this->family = "other";
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		$this->description = "Description of module FactureBusinessITA";
		
		// Possible values for version are: 'development', 'experimental' or version
		$this->version = '3.6.x+2.1.0';
		$this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);
		
		$this->special = 1;	// (0=common,1=interface,2=others,3=very specific)
		
		$this->picto = 'facturebusinessita@facturebusinessita'; // mypicto@facturebusinessita
		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		// for default path (eg: /facturebusinessita/core/xxxxx) (0=disable, 1=enable)
		// for specific path of parts (eg: /facturebusinessita/core/modules/barcode)
		// for specific css file (eg: /facturebusinessita/css/facturebusinessita.css.php)
		$this->module_parts = array(
			// Set this to 1 if module has its own trigger directory
			'triggers' => 1,
			'models' => 0,
			'tpl' => 1,
			'hooks' => array('invoicesuppliercard','invoicecard', 'paymentsupplier'),
				'css' => array('facturebusinessita/css/module_facturebusinessita.css.php'),
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/facturebusinessita/temp");
		$this->dirs = array();

		// Config pages. Put here list of php pages
		// stored into facturebusinessita/admin directory, used to setup module.
		$this->config_page_url = array("admin_facturebusinessita.php@facturebusinessita");

		// Dependencies
		// A condition to hide module
		$this->hidden = false;
		// List of modules class name as string that must be enabled if this module is enabled
		// Example : $this->depends('modAnotherModule', 'modYetAnotherModule')
		$this->depends = array('modFacture','modBanque','modAgenda');
		// List of modules id to disable if this one is disabled
		$this->requiredby = array('');
		// List of modules id this module is in conflict with
		$this->conflictwith = array();
		// Minimum version of PHP required by module
		$this->phpmin = array(5, 3);
		// Minimum version of Dolibarr required by module
		$this->need_dolibarr_version = array(3, 6);
		// Language files list (langfiles@facturebusinessita)
		$this->langfiles = array("facturebusinessita@facturebusinessita");
		// Constants
		// List of particular constants to add when module is enabled
		// (name, type ['chaine' or ?], value, description, visibility, entity ['current' or 'allentities'], delete on unactive)
		// Example:
		$this->const = array(
				0 => array(
					'FACTUREBUSINESSITA_PROGRESSIVO_FE',
					'chaine',
					0,
					'Mantiene un progressivo (da non modificare) dei file xml generati per tutte le fatture elettroniche presenti nel sistema. Impedisce la generazione di 2 file xml con lo stesso nome.',
					1,
			    'current',
			    0,
				)
			//	1 => array(
			//		'MYMODULE_MYNEWCONST2',
			//		'chaine',
			//		'myvalue',
			//		'This is another constant to add',
			//		0,
			//	)
		);

		// Array to add new pages in new tabs
		// Example:
		$this->tabs = array(
			//	// To add a new tab identified by code tabname1
			//	'objecttype:+tabname1:Title1:langfile@facturebusinessita:$user->rights->facturebusinessita->read:/facturebusinessita/mynewtab1.php?id=__ID__',
			//	// To add another new tab identified by code tabname2
			//	'objecttype:+tabname2:Title2:langfile@facturebusinessita:$user->rights->othermodule->read:/facturebusinessita/mynewtab2.php?id=__ID__',
			//	// To remove an existing tab identified by code tabname
			//	'objecttype:-tabname'
		);
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
		// 'contact'          to add a tab in contact view
		// 'contract'         to add a tab in contract view
		// 'group'            to add a tab in group view
		// 'intervention'     to add a tab in intervention view
		// 'invoice'          to add a tab in customer invoice view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'member'           to add a tab in fundation member view
		// 'opensurveypoll'	  to add a tab in opensurvey poll view
		// 'order'            to add a tab in customer order view
		// 'order_supplier'   to add a tab in supplier order view
		// 'payment'		  to add a tab in payment view
		// 'payment_supplier' to add a tab in supplier payment view
		// 'product'          to add a tab in product view
		// 'propal'           to add a tab in propal view
		// 'project'          to add a tab in project view
		// 'stock'            to add a tab in stock view
		// 'thirdparty'       to add a tab in third party view
		// 'user'             to add a tab in user view

		// Dictionaries
		if (! isset($conf->facturebusinessita->enabled)) {
			$conf->facturebusinessita=new stdClass();
			$conf->facturebusinessita->enabled = 0;
		}
		$this->dictionaries = array();
		
		$this->dictionaries=array(
				'langs'=>'facturebusinessita@facturebusinessita',
				'tabname'=>array(MAIN_DB_PREFIX."c_facturebusinessita_regimifiscali",MAIN_DB_PREFIX."c_facturebusinessita_esigibilitaiva"),
				'tablib'=>array("FactureBusinessITARegimiFiscali","FactureBusinessITAEsigibilitaIVA"),
				'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'c_facturebusinessita_regimifiscali as f','SELECT e.rowid as rowid, e.code, e.label, e.active, e.use_default FROM '.MAIN_DB_PREFIX.'c_facturebusinessita_esigibilitaiva as e'),
				'tabsqlsort'=>array("code ASC","code ASC"),
				'tabfield'=>array("code,label","code,label,use_default"),
				'tabfieldvalue'=>array("code,label","code,label,use_default"),
				'tabfieldinsert'=>array("code,label","code,label,use_default"),
				'tabrowid'=>array("rowid","rowid"),
				'tabcond'=>array($conf->facturebusinessita->enabled,$conf->facturebusinessita->enabled)
		);
	

		// Boxes
		// Add here list of php file(s) stored in core/boxes that contains class to show a box.
		$this->boxes = array(); // Boxes list
		

		// Permissions
		$this->rights = array(); // Permission array used by this module
		$r = 0;

	}

	/**
	 * Function called when module is enabled.
	 * The init function add constants, boxes, permissions and menus
	 * (defined in constructor) into Dolibarr database.
	 * It also creates data directories
	 *
	 * 	@param		string	$options	Options when enabling module ('', 'noboxes')
	 * 	@return		int					1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		
		
		$sql = array();
		//$this->error = DOL_URL_ROOT.$dolibarr_main_url_root_alt;
		
		if(!$this->initFatturapa()){
			$this->error = "Errore nella creazione del file xsd. <br/>la cartella facturebusinessita/lib/fatturapa deve essere scrivibile" ;
			return 0;
		}
		
		$result = $this->loadTables();
    
		return $this->_init($sql, $options);
	}
	
	
	/**
	 * Function called when module is enabled.
	 * @return int  1 if OK, 0 if KO
	 */
	private function initFatturapa(){
		
		global $conf;
		$url_fatturapa = "/lib/fatturapa/";
		$path= 'facturebusinessita';
		
		foreach ($conf->file->dol_document_root as $key => $dirroot)	// ex: array(["main"]=>"/home/main/htdocs", ["alt0"]=>"/home/dirmod/htdocs", ...)
		{
			if ($key == 'main') continue;
			if (file_exists($dirroot.'/'.$path))
			{
				$res=$dirroot.'/'.$path;
				break;
			}
		}
		
		// Questa è la url "locale del file xmldsig-core-schema.xsd
		// che va sostituita all'interno del file fatturapa_v1.1.xsd 		
	  $url = DOL_MAIN_URL_ROOT . str_replace(DOL_DOCUMENT_ROOT, "", $res).$url_fatturapa."xmldsig-core-schema.xsd";
		$xsd_orig = $res.$url_fatturapa."fatturapa_v1.1.xsd.orig";
		$xsd_final = $res.$url_fatturapa."fatturapa_v1.1.xsd";

		$xsd_string = file_get_contents($xsd_orig);
	  
		$ret = file_put_contents($xsd_final, str_replace('DOL_PATH_XMLDSIG', $url, $xsd_string));
		
		if($ret === FALSE) 
			return 0;
		else 
			return 1;	
	  
	}
	
	

	/**
	 * Function called when module is disabled.
	 * Remove from database constants, boxes and permissions from Dolibarr database.
	 * Data directories are not deleted
	 *
	 * 	@param		string	$options	Options when enabling module ('', 'noboxes')
	 * 	@return		int					1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();

		return $this->_remove($sql, $options);
	}

	/**
	 * Create tables, keys and data required by module
	 * Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
	 * and create data commands must be stored in directory /facturebusinessita/sql/
	 * This function is called by this->init
	 *
	 * 	@return		int		<=0 if KO, >0 if OK
	 */
	private function loadTables()
	{
		return $this->_load_tables('/facturebusinessita/sql/');
	}
}
