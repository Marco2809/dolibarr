<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) <year>  <name of author>
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
 * 	\defgroup	AllScreens	AllScreens module
 * 	\brief		AllScreens module descriptor.
 * 	\file		core/modules/modAllScreens.class.php
 * 	\ingroup	allscreens
 * 	\brief		Description and activation file for module AllScreens
 */

include_once DOL_DOCUMENT_ROOT . "/core/modules/DolibarrModules.class.php";

/**
 * Description and activation class for module AllScreens
 */
class modAllScreens extends DolibarrModules
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
		$this->numero = 500500;
		$this->rights_class = 'allscreens';

		$this->family = "other";
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		$this->description = "Affichage responsive";
		$this->version = '1.3.3';
		$this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);
		$this->special = 0;
		$this->picto = 'thumb@allscreens';
		$this->module_parts = array(
			//'triggers' => 1,
			// Set this to 1 if module has its own login method directory
			//'login' => 0,
			// Set this to 1 if module has its own substitution function file
			//'substitutions' => 0,
			// Set this to 1 if module has its own menus handler directory
			'menus' => 1,
			// Set this to 1 if module has its own theme directory (theme)
			//'theme' => 1,
			// Set this to 1 if module overwrite template dir (core/tpl)
			// 'tpl' => 0,
			// Set this to 1 if module has its own barcode directory
			//'barcode' => 0,
			// Set this to 1 if module has its own models directory
			//'models' => 0,
			// Set this to relative path of css if module has its own css file
			'css' => array('allscreens/css/style.min.css'),
			// Set this to relative path of js file if module must load a js on all pages
			'js' => array('allscreens/js/custom.min.js'),
			// Set here all hooks context managed by module
			'hooks' => array('toprightmenu'),
			// To force the default directories names
			// 'dir' => array('output' => 'othermodulename'),
			// Set here all workflow context managed by module
			// Don't forget to depend on modWorkflow!
			// The description translation key will be descWORKFLOW_MODULE1_YOURACTIONTYPE_MODULE2
			// You will be able to check if it is enabled with the $conf->global->WORKFLOW_MODULE1_YOURACTIONTYPE_MODULE2 constant
			// Implementation is up to you and is usually done in a trigger.
			// 'workflow' => array(
			//     'WORKFLOW_MODULE1_YOURACTIONTYPE_MODULE2' => array(
			//         'enabled' => '! empty($conf->module1->enabled) && ! empty($conf->module2->enabled)',
			//         'picto' => 'yourpicto@allscreens',
			//         'warning' => 'WarningTextTranslationKey',
			//      ),
			// ),
		);

		$this->dirs = array();

		$this->config_page_url = array("admin.php@allscreens");

		$this->hidden = false;
		$this->depends = array();
		$this->requiredby = array();
		$this->conflictwith = array();
		$this->phpmin = array(5, 3);
		$this->need_dolibarr_version = array(3, 6);
		$this->langfiles = array("allscreens@allscreens");

		// Dictionaries
		if (! isset($conf->allscreens->enabled)) {
			$conf->allscreens=new stdClass();
			$conf->allscreens->enabled = 0;
		}
		$this->dictionaries = array();

		$this->boxes = array(); // Boxes list

		// Permissions
		$this->rights = array(); // Permission array used by this module
		$r = 0;

		// Exports
		$r = 0;

		// Constants
		$this->const = array ();
		$r = 0;
		
		$r ++;
		$this->const [$r] [0] = "MAIN_FORCETHEME";	// name
		$this->const [$r] [1] = "chaine";			// type
		$this->const [$r] [2] = 'allscreens';		// def value
		$this->const [$r] [3] = '';					// note
		$this->const [$r] [4] = 0;					// visible
		$this->const [$r] [5] = 0;
		
		$r ++;
		$this->const [$r] [0] = "MAIN_MENU_STANDARD_FORCED";
		$this->const [$r] [1] = "chaine";
		$this->const [$r] [2] = 'allscreens_menu.php';
		$this->const [$r] [3] = '';
		$this->const [$r] [4] = 0;
		$this->const [$r] [5] = 0;
		
		$r ++;
		$this->const [$r] [0] = "MAIN_MENUFRONT_STANDARD_FORCED";
		$this->const [$r] [1] = "chaine";
		$this->const [$r] [2] = 'allscreens_menu.php';
		$this->const [$r] [3] = '';
		$this->const [$r] [4] = 0;
		$this->const [$r] [5] = 0;
		
		$r ++;
		$this->const [$r] [0] = "MAIN_MENU_SMARTPHONE_FORCED";
		$this->const [$r] [1] = "chaine";
		$this->const [$r] [2] = 'allscreens_menu.php';
		$this->const [$r] [3] = '';
		$this->const [$r] [4] = 0;
		$this->const [$r] [5] = 0;
		
		$r ++;
		$this->const [$r] [0] = "MAIN_MENUFRONT_SMARTPHONE_FORCED";
		$this->const [$r] [1] = "chaine";
		$this->const [$r] [2] = 'allscreens_menu.php';
		$this->const [$r] [3] = '';
		$this->const [$r] [4] = 0;
		$this->const [$r] [5] = 0;
		
		$r ++;
		$this->const [$r] [0] = "DOL_VERSION";
		$this->const [$r] [1] = "chaine";
		$this->const [$r] [2] = '';
		$this->const [$r] [3] = 'Dolibarr version';
		$this->const [$r] [4] = 0;
		$this->const [$r] [5] = 0;
		
		$r ++;
		$this->const [$r] [0] = "ALLSCREENS_FIXED_MENU";
		$this->const [$r] [1] = "yesno";
		$this->const [$r] [2] = '0';
		$this->const [$r] [3] = '';
		$this->const [$r] [4] = 0;
		$this->const [$r] [5] = 0;

		$r ++;
		$this->const [$r] [0] = "ALLSCREENS_TEST";
		$this->const [$r] [1] = "chaine";
		$this->const [$r] [2] = '';
		$this->const [$r] [3] = '';
		$this->const [$r] [4] = 0;
		$this->const [$r] [5] = 0;
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

		$result = $this->loadTables();

		$installed_ver = dolibarr_get_const($this->db,'MAIN_VERSION_LAST_INSTALL',0);
		$upgraded_ver = dolibarr_get_const($this->db,'MAIN_VERSION_LAST_UPGRADE',0);
		if ($upgraded_ver!="") {
			$dol_version = $upgraded_ver;
		} else {
			$dol_version = $installed_ver;
		}
		dolibarr_set_const($this->db,'DOL_VERSION',$dol_version,'chaine',0,'Dolibarr version',1);
		dolibarr_set_const($this->db,'MAIN_THEME','allscreens','chaine',0,'Sets AllScreens Theme',1);
		dolibarr_set_const($this->db,'MAIN_MENU_STANDARD','allscreens_menu.php','chaine',0,'',1);
		dolibarr_set_const($this->db,'MAIN_MENUFRONT_STANDARD','allscreens_menu.php','chaine',0,'',1);
		dolibarr_set_const($this->db,'MAIN_MENU_SMARTPHONE','allscreens_menu.php','chaine',0,'',1);
		dolibarr_set_const($this->db,'MAIN_MENUFRONT_SMARTPHONE','allscreens_menu.php','chaine',0,'',1);

		dolibarr_set_const($this->db,'ALLSCREENS_FIXED_MENU',0,'yesno',0,'',1);

		// replace weather images
		$source = dol_buildpath('/allscreens/img/weather.new');
		$dest = dol_buildpath('/theme/common/weather');
		cpy($source,$dest);

	    // create allscreens theme folder
		$source = dol_buildpath('/allscreens/inst/theme');
		$dest = dol_buildpath('/theme');

		if (!file_exists(dol_buildpath('/theme/allscreens'))) {
			cpy($source,$dest);
		}

	    /* set old settings when new version activated */
	    $new = dol_buildpath('/allscreens/inst/.new');
	    if (file_exists($new)) {
	    	$def_col1 = "#5999A7";
	    	$def_col2 = "#F07B6E";
	    	$def_col_body_bckgrd = "#D0D0D0";

			$file = dol_buildpath('/allscreens/css/style.min.css');

	    	$col1 = dolibarr_get_const($this->db,'ALLSCREENS_COL1');

	    	$col2 = dolibarr_get_const($this->db,'ALLSCREENS_COL2');

	    	$col_body_bckgrd = dolibarr_get_const($this->db,'ALLSCREENS_COL_BODY_BCKGRD');

			if ( !empty($col1) || !empty($col2) || !empty($col_body_bckgrd) )	{
				$file_contents = file_get_contents($file);
				if ( !empty($col1) ) $file_contents = str_replace($def_col1,$col1,$file_contents);
				if ( !empty($col2) ) $file_contents = str_replace($def_col2,$col2,$file_contents);
				if ( !empty($col_body_bckgrd) ) $file_contents = str_replace($def_col_body_bckgrd,$col_body_bckgrd,$file_contents);
				file_put_contents($file,$file_contents);
			}
	    	if(empty($col1)) dolibarr_set_const($this->db,'ALLSCREENS_COL1',$def_col1,'chaine',0,'',1);
	    	if(empty($col2)) dolibarr_set_const($this->db,'ALLSCREENS_COL2',$def_col2,'chaine',0,'',1);
	    	if(empty($col_body_bckgrd)) dolibarr_set_const($this->db,'ALLSCREENS_COL_BODY_BCKGRD',$def_col_body_bckgrd,'chaine',0,'',1);
			unlink($new);
	    }

		return $this->_init($sql, $options);
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

		dolibarr_set_const($this->db,'MAIN_THEME','eldy','chaine',0,'',1);
		dolibarr_set_const($this->db,'MAIN_MENU_STANDARD','eldy_menu.php','chaine',0,'',1);
		dolibarr_set_const($this->db,'MAIN_MENUFRONT_STANDARD','eldy_menu.php','chaine',0,'',1);
		dolibarr_set_const($this->db,'MAIN_MENU_SMARTPHONE','eldy_menu.php','chaine',0,'',1);
		dolibarr_set_const($this->db,'MAIN_MENUFRONT_SMARTPHONE','eldy_menu.php','chaine',0,'',1);
		dolibarr_del_const($this->db,'MAIN_FORCETHEME');
		dolibarr_del_const($this->db,'MAIN_MENU_STANDARD_FORCED');
		dolibarr_del_const($this->db,'MAIN_MENUFRONT_STANDARD_FORCED');
		dolibarr_del_const($this->db,'MAIN_MENU_SMARTPHONE_FORCED');
		dolibarr_del_const($this->db,'MAIN_MENUFRONT_SMARTPHONE_FORCED');
		dolibarr_del_const($this->db,'ALLSCREENS_FIXED_MENU');

		$source = dol_buildpath('/allscreens/img/weather.org');
		$dest = dol_buildpath('/theme/common/weather');
		cpy($source,$dest);

		return $this->_remove($sql, $options);
	}

	/**
	 * Create tables, keys and data required by module
	 * Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
	 * and create data commands must be stored in directory /allscreens/sql/
	 * This function is called by this->init
	 *
	 * 	@return		int		<=0 if KO, >0 if OK
	 */
	private function loadTables()
	{
		return $this->_load_tables('/allscreens/sql/');
	}

}

// copy recursive
function cpy($source, $dest){
    if(is_dir($source)) {
        $dir_handle=opendir($source);
        while($file=readdir($dir_handle)){
            if($file!="." && $file!=".."){
                if(is_dir($source."/".$file)){
                    if(!is_dir($dest."/".$file)){
                        mkdir($dest."/".$file);
                    }
                    cpy($source."/".$file, $dest."/".$file);
                } else {
                    copy($source."/".$file, $dest."/".$file);
                }
            }
        }
        closedir($dir_handle);
    } else {
        copy($source, $dest);
    }
}
