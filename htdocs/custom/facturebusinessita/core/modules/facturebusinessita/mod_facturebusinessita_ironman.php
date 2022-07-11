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
 * \file       htdocs/core/modules/facturebusinessita/mod_facturebusinessita_ironman.php
 * \ingroup    facturebusinessita
 * \brief      Fichier contenant la classe du modele de numerotation de reference de facturebusinessita Ironman
 */

dol_include_once('/facturebusinessita/core/modules/facturebusinessita/modules_facturebusinessita.php');


/**
 *	Class to manage customer facturebusinessita numbering rules Ironman
 */
class mod_facturebusinessita_ironman extends ModeleNumRefFactureBusinessITAs
{
	var $version='dolibarr';		// 'development', 'experimental', 'dolibarr'
	var $error = '';
	var $nom = 'Ironman';


    /**
     *  Renvoi la description du modele de numerotation
     *
     *  @return     string      Texte descripif
     */
	function info()
    {
    	global $conf,$langs;

		$langs->load("bills");
		$langs->load("admin");
		$langs->load("facturebusinessita@facturebusinessita");

		$form = new Form($this->db);

		$texte = $langs->trans('GenericNumRefModelDescFBI')."<br>\n";
		$texte.= '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
		$texte.= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		$texte.= '<input type="hidden" name="action" value="updateMask">';
		$texte.= '<input type="hidden" name="maskconstfacturebusinessita" value="FACTUREBUSINESSITA_IRONMAN_MASK">';
		$texte.= '<table class="nobordernopadding" width="100%">';

		   $tooltip=$langs->trans("GenericMaskCodes",$langs->transnoentities("Invoice"),$langs->transnoentities("Invoice"));
        $tooltip.=$langs->trans("GenericMaskCodes2");
        $tooltip.=$langs->trans("GenericMaskCodes3");
        $tooltip.=$langs->trans("GenericMaskCodes4a",$langs->transnoentities("Invoice"),$langs->transnoentities("Invoice"));
        $tooltip.=$langs->trans("GenericMaskCodes5");

		// Parametrage du prefix
		$texte.= '<tr><td>'.$langs->trans("Mask").' ('.$langs->trans("InvoiceStandard").'):</td>';
		$texte.= '<td align="right">'.$form->textwithpicto('<input type="text" class="flat" size="24" name="maskfacturebusinessita" value="'.$conf->global->FACTUREBUSINESSITA_IRONMAN_MASK.'">',$tooltip,1,1).'</td>';

		$texte.= '<td align="left" rowspan="2">&nbsp; <input type="submit" class="button" value="'.$langs->trans("Modify").'" name="Button"></td>';

		$texte.= '</tr>';

		$texte.= '</table>';
		$texte.= '</form>';

		return $texte;
    }

    /**
     *  Renvoi un exemple de numerotation
     *
     *  @return     string      Example
     */
    function getExample()
    {
     	global $conf,$langs,$mysoc;

    	$old_code_client=$mysoc->code_client;
    	$mysoc->code_client='CCCCCCCCCC';
     	$numExample = $this->getNextValue($mysoc,'');
     	$mysoc->code_client=$old_code_client;

		if (! $numExample)
		{
			$numExample = $langs->trans('NotConfigured');
		}
		return $numExample;
    }

	/**
	 * 	Return next free value
	 *
	 *  @param	Societe		$objsoc     Object thirdparty
	 *  @param  Object		$object		Object we need next value for
	 *  @return string      			Value if KO, <0 if KO
	 */
    function getNextValue($objsoc,$object)
    {
		global $db,$conf;

		require_once DOL_DOCUMENT_ROOT .'/core/lib/functions2.lib.php';

		// We get cursor rule
		$mask=$conf->global->FACTUREBUSINESSITA_IRONMAN_MASK;

		if (! $mask)
		{
			$this->error='NotConfigured';
			return 0;
		}
		
		//ServiceTech
		//ho dovuto inserire 'last' per evitare il numero progressivo (default 'next') successivo generato dal popup nel file mod_facture_mercure.php
		$numFinal=get_next_value($db,$mask,'facture','facnumber','',$objsoc->code_client,$object->date,'last');

		return  $numFinal;
	}


	/**
	 *  Return next free value
	 *
	 *  @param	Societe		$objsoc     Object third party
	 * 	@param	string		$objforref	Object for number to search
	 *  @return string      			Next free value
     */
    function getNumRef($objsoc,$objforref)
    {
        return $this->getNextValue($objsoc,$objforref);
    }

}

