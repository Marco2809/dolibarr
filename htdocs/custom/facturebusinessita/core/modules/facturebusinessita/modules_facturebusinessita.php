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
 *  \file			modules_facturebusinessita.php
 *  \ingroup		facturebusinessita
 *  \brief			Generation pdf for Material Requisition
 *  					
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commondocgenerator.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';	// requis car utilise par les classes qui heritent
require_once DOL_DOCUMENT_ROOT.'/core/class/discount.class.php';


/**
 *	Classe mere des modeles de facturebusinessitas
 */
abstract class ModeleMaterialrequisition extends CommonDocGenerator
{
	var $error='';

	/**
	 *  Return list of active generation modules
	 *
     *  @param	DoliDB	$db     			Database handler
     *  @param  string	$maxfilenamelength  Max length of value to show
     *  @return	array						List of templates
	 */
	static function liste_modeles($db,$maxfilenamelength=0)
	{
		global $conf;

		$type='facturebusinessita';
		$liste=array();

		include_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
		$liste=getListOfModels($db,$type,$maxfilenamelength);

		return $liste;
	}
}



/**
 *  \class      ModeleNumRefFactureBusinessITAs
 *  \brief      Classe mere des modeles de numerotation des references de facturebusinessitas
 */

abstract class ModeleNumRefFactureBusinessITAs
{
	var $error='';

	/**
	 *	Return if a module can be used or not
	 *
	 *	@return		boolean     true if module can be used
	 */
	function isEnabled()
	{
		return true;
	}

	/**
	 *	Renvoie la description par defaut du modele de numerotation
	 *
	 *	@return     string      Texte descripif
	 */
	function info()
	{
		global $langs;
		$langs->load("facturebusinessitas");
		return $langs->trans("NoDescription");
	}

	/**
	 *	Renvoie un exemple de numerotation
	 *
	 *	@return     string      Example
	 */
	function getExample()
	{
		global $langs;
		$langs->load("facturebusinessitas");
		return $langs->trans("NoExample");
	}

	/**
	 *	Test si les numeros deja en vigueur dans la base ne provoquent pas de conflits qui empecheraient cette numerotation de fonctionner.
	 *
	 *	@return     boolean     false si conflit, true si ok
	 */
	function canBeActivated()
	{
		return true;
	}

	/**
	 *	Renvoie prochaine valeur attribuee
	 *
	 *	@param	Societe		$objsoc     Object thirdparty
	 *	@param	Object		$object		Object we need next value for
	 *	@return	string      Valeur
	 */
	function getNextValue($objsoc,$object)
	{
		global $langs;
		return $langs->trans("NotAvailable");
	}

	/**
	 *	Renvoie version du module numerotation
	 *
	 *	@return     string      Valeur
	 */
	function getVersion()
	{
		global $langs;
		$langs->load("admin");

		if ($this->version == 'development') return $langs->trans("VersionDevelopment");
		if ($this->version == 'experimental') return $langs->trans("VersionExperimental");
		if ($this->version == 'dolibarr') return DOL_VERSION;
		return $langs->trans("NotAvailable");
	}
}


/**
 *  Create a document onto disk accordign to template module.
 *
 *  @param	    DoliDB		$db  			Database handler
 *  @param	    Object		$object			Object facturebusinessita
 *  @param	    string		$modele			Force le modele a utiliser ('' to not force)
 *  @param		Translate	$outputlangs	objet lang a utiliser pour traduction
 *  @param      int			$hidedetails    Hide details of lines
 *  @param      int			$hidedesc       Hide description
 *  @param      int			$hideref        Hide ref
 *  @return     int         				0 if KO, 1 if OK
 */
function facturebusinessita_pdf_create($db, $object, $modele, $outputlangs, $hidedetails=0, $hidedesc=0, $hideref=0)
{
	global $conf,$user,$langs;
	
	$langs->load("bills");
	
	$error=0;
	
	// Increase limit for PDF build
	$err=error_reporting();
	error_reporting(0);
	@set_time_limit(120);
	error_reporting($err);
	
	$srctemplatepath='';
	
	// Positionne le modele sur le nom du modele a utiliser
	if (! dol_strlen($modele))
	{
		if (! empty($conf->global->FACTUREBUSINESSITA_ADDON_PDF))
		{
			$modele = $conf->global->FACTUREBUSINESSITA_ADDON_PDF;
		}
		else
		{
			$modele = 'fatturaelettronica_fbi';
		}
	}
	
	
	// If selected modele is a filename template (then $modele="modelname:filename")
	$tmp=explode(':',$modele,2);
	if (! empty($tmp[1]))
	{
		$modele=$tmp[0];
		$srctemplatepath=$tmp[1];
	}
	
	// Search template files
	$file=''; $classname=''; $filefound=0;
	$dirmodels=array('/');
	if (is_array($conf->modules_parts['models'])) $dirmodels=array_merge($dirmodels,$conf->modules_parts['models']);
	foreach($dirmodels as $reldir)
	{
		foreach(array('doc','pdf') as $prefix)
		{
			$file = $prefix."_".$modele.".modules.php";
	
			// On verifie l'emplacement du modele
			$file=dol_buildpath($reldir."facturebusinessita/doc/".$file,0);
			if (file_exists($file))
			{
				$filefound=1;
				$classname=$prefix.'_'.$modele;
				break;
			}
		}
		if ($filefound) break;
	}
	
	// Charge le modele
	if ($filefound)
	{
		require_once $file;
	
		$obj = new $classname($db);
	
		// We save charset_output to restore it because write_file can change it if needed for
		// output format that does not support UTF8.
		$sav_charset_output=$outputlangs->charset_output;
		if ($obj->write_file($object, $outputlangs, $srctemplatepath, $hidedetails, $hidedesc, $hideref) > 0)
		{
			$outputlangs->charset_output=$sav_charset_output;
	
			// We delete old previewgetDatiRiepilogo
			require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
			dol_delete_preview($object);
	
			// Success in building document. We build meta file.
			dol_meta_create($object);
	
			// Appel des triggers
			include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
			$interface=new Interfaces($db);
			$result=$interface->run_triggers('BILL_BUILDDOC',$object,$user,$langs,$conf);
			if ($result < 0) { $error++; $errors=$interface->errors; }
			// Fin appel triggers
	
			return 1;
		}
		else	// errore nella creazione del file xml per errori sui dati
		{
			return -1;
		}
	
	}
	else
	{
		dol_print_error('',$langs->trans("Error")." ".$langs->trans("ErrorFileDoesNotExists",$file));
		return -1;
	}
	
	
	
	
	/*
	global $conf,$user,$langs,$hookmanager;
	$langs->load("facturebusinessitas");

	$error=0;

	$srctemplatepath='';

	// Positionne le modele sur le nom du modele a utiliser
	if (! dol_strlen($modele))
	{
	    if (! empty($conf->global->FACTUREBUSINESSITA_ADDON_PDF))
	    {
	        $modele = $conf->global->FACTUREBUSINESSITA_ADDON_PDF;
	    }
	    else
	    {
	        $modele = 'material';
	    }
	}

    // If selected modele is a filename template (then $modele="modelname:filename")
	$tmp=explode(':',$modele,2);
    if (! empty($tmp[1]))
    {
        $modele=$tmp[0];
        $srctemplatepath=$tmp[1];
    }

	// Search template files
	$file=''; $classname=''; $filefound=0;
	$dirmodels=array('/');
	if (is_array($conf->modules_parts['models'])) $dirmodels=array_merge($dirmodels,$conf->modules_parts['models']);
	foreach($dirmodels as $reldir)
	{
    	foreach(array('doc','pdf') as $prefix)
    	{
    	    $file = $prefix."_".$modele.".modules.php";

    		// On verifie l'emplacement du modele
	        $file=dol_buildpath($reldir."/facturebusinessita/doc/".$file,0);
    		if (file_exists($file))
    		{
    			$filefound=1;
    			$classname=$prefix.'_'.$modele;
    			break;
    		}
    	}
    	if ($filefound) break;
    }

	// Charge le modele
	if ($filefound)
	{
		require_once $file;

		$obj = new $classname($db);
		//$obj->message = $message;

		// We save charset_output to restore it because write_file can change it if needed for
		// output format that does not support UTF8.
		$sav_charset_output=$outputlangs->charset_output;
		if ($obj->write_file($object, $outputlangs, $srctemplatepath, $hidedetails, $hidedesc, $hideref) > 0)
		{
			$outputlangs->charset_output=$sav_charset_output;

			// We delete old preview
			require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
			dol_delete_preview($object);

			// Success in building document. We build meta file.
			dol_meta_create($object);

			// Appel des triggers
			include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
			$interface=new Interfaces($db);
			$result=$interface->run_triggers('FACTUREBUSINESSITA_BUILDDOC',$object,$user,$langs,$conf);
			if ($result < 0) { $error++; $obj->errors=$interface->errors; }
			// Fin appel triggers

			return 1;
		}
		else
		{
			$outputlangs->charset_output=$sav_charset_output;
			dol_print_error($db,"facturebusinessita_pdf_create Error: ".$obj->error);
			return -1;
		}

	}
	else
	{
		dol_print_error('',$langs->trans("Error")." ".$langs->trans("ErrorFileDoesNotExists",$file));
		return -1;
	}*/
}
