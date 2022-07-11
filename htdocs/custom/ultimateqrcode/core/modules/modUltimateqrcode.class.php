<?php
/* Copyright (C) 2010-2011 Regis Houssin  <regis.houssin@capnetworks.com>
 * Copyright (C) 2011-2014 Philippe Grand <philippe.grand@atoo-net.com>
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
 */

/**
 *		\defgroup   ultimateqrcode     Module ultimateqrcode
 *		\brief      Pdf Designs management
 *		\file       htdocs/custom/ultimateqrcode/core/modules/modUltimatepdf.class.php
 *		\ingroup    ultimateqrcode
 *		\brief      Fichier de description et activation du module ultimateqrcode
 */

include_once(DOL_DOCUMENT_ROOT ."/core/modules/DolibarrModules.class.php");


/**
 *	\class      modultimateqrcode
 *	\brief      Classe de description et activation du module Ultimateqrcode
 */
class modUltimateqrcode extends DolibarrModules
{

	/**
	 *	Constructor.
	 *
	 *	@param	DoliDB	$db		Database handler
	 */
	function __construct($db)
	{
		$this->db = $db ;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 300400 ;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'ultimateqrcode';

		$this->family = "other";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i','',get_class($this));
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = "QR code management";
		// Can be enabled / disabled only in the main company with superadmin account
		$this->core_enabled = 0;
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '3.5.x';
		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)
		$this->special = 0;
		// Name of png file (without png) used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto='ultimateqrcode@ultimateqrcode';

		// Data directories to create when module is enabled
		$this->dirs = array("/ultimateqrcode/temp");

		// Config pages. Put here list of php page names stored in admmin directory used to setup module.
		$this->config_page_url = 'ultimateqrcode.php@ultimateqrcode';

		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array();

		// Dependencies
		$this->depends = array();		// List of modules id that must be enabled if this module is enabled
		$this->requiredby = array();	// List of modules id to disable if this one is disabled
		$this->phpmin = array(5,2);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(3,2);	// Minimum version of Dolibarr required by module
		$this->langfiles = array('ultimateqrcode@ultimateqrcode');

		// Constants
		// List of particular constants to add when module is enabled
		$this->const=array();
		
		 // Array to add new pages in new tabs
        $this->tabs = array(
                        'thirdparty:+tabqrcodethirdparty:QrcodeThirdParty:ultimateqrcode@ultimateqrcode:/ultimateqrcode/qrcodethirdparty.php?socid=__ID__',
						'product:+tabqrcodeproduct:QrcodeProduct:ultimateqrcode@ultimateqrcode:/ultimateqrcode/qrcodeproduct.php?id=__ID__',
						'stock:+tabqrcodestock:QrcodeStock:ultimateqrcode@ultimateqrcode:/ultimateqrcode/qrcodestock.php?id=__ID__',
						'intervention:+tabqrcodeinter:QrcodeInter:ultimateqrcode@ultimateqrcode:/ultimateqrcode/qrcodeinter.php?id=__ID__',
						
						'order:+tabqrcodeorder:QrcodeOrder:ultimateqrcode@ultimateqrcode:/ultimateqrcode/qrcodeorder.php?id=__ID__',
						'member:+tabqrcodemember:QrcodeMember:ultimateqrcode@ultimateqrcode:/ultimateqrcode/qrcodemember.php?rowid=__ID__'
                    );

		// Boxes
		$this->boxes = array();

		// Permissions
		$this->rights = array();
		$r=0;
		
		// Add here list of permission defined by an id, a label, a boolean and two constant strings.
        // Example:
        $this->rights[$r][0] = 300401;               // Permission id (must not be already used)
        $this->rights[$r][1] = 'Read qrcode outcomes';      // Permission label
        $this->rights[$r][3] = 0;                    // Permission by default for new user (0/1)
        $this->rights[$r][4] = 'read';               // In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
        $this->rights[$r][5] = '';                   // In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
        $r++;

        $this->rights[$r][0] = 300402;               // Permission id (must not be already used)
        $this->rights[$r][1] = 'Create/Modify qrcode outcomes';      // Permission label
        $this->rights[$r][3] = 0;                    // Permission by default for new user (0/1)
        $this->rights[$r][4] = 'write';              // In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
        $this->rights[$r][5] = '';                   // In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
        $r++;

		// Main menu entries
		$this->menus = array();			// List of menus to add
		$r=0;

	}


	/**
     *		Function called when module is enabled.
     *		The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
     *		It also creates data directories.
     *
	 *      @return     int             1 if OK, 0 if KO
     */
	function init()
	{		
		$sql = array();
		
		//$result=$this->load_tables();

		return $this->_init($sql);
	}

	/**
	 *		Function called when module is disabled.
 	 *      Remove from database constants, boxes and permissions from Dolibarr database.
 	 *		Data directories are not deleted.
 	 *
	 *      @return     int             1 if OK, 0 if KO
 	 */
	function remove()
	{
		$sql = array();

		return $this->_remove($sql);
	}
	
	/**
	 *		Create tables and keys required by module
	 *		This function is called by this->init.
	 *
	 * 		@return		int		<=0 if KO, >0 if OK
	 */
	function load_tables()
	{
		return $this->_load_tables('/ultimateqrcode/sql/');
	}

}
?>
