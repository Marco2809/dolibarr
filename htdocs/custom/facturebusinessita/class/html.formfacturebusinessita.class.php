<?php
/* Copyright (c) 2015	Claudio Aschieri <c.aschieri@19.coop>
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
 *	\file       facturebusinessita/core/class/html.formfacturebusinessita.class.php
 *  \ingroup    facturebusinessita
 *	\brief      File of class with all html predefined components
 */


/**
 *	Class to manage generation of HTML components
 *	Only common components must be here.
 */
class FormFactureBusinessITA extends Form
{
    /**
     * Constructor
     *
     * @param		DoliDB		$db      Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     *      Load list of Regimi fiscali 
     *
     *      @return     int             Nb lignes chargees, 0 si deja chargees, <0 si ko
     */
    function load_cache_regimi_fiscali_fbi()
    {
        global $langs;

        if (count($this->cache_regimi_fiscali_fbi)) return 0;    // Cache 

        $sql = "SELECT rowid, code, label";
        $sql.= " FROM ".MAIN_DB_PREFIX.'c_facturebusinessita_regimifiscali';
        $sql.= " WHERE active=1";
        $sql.= " ORDER BY rowid";
        dol_syslog(get_class($this).'::load_cache_regimi_fiscali sql='.$sql,LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql)
        {
            $num = $this->db->num_rows($resql);
            $i = 0;
            while ($i < $num)
            {
                $obj = $this->db->fetch_object($resql);
                $this->cache_regimi_fiscali_fbi[$obj->rowid]['code'] =$obj->code;
                $this->cache_regimi_fiscali_fbi[$obj->rowid]['label']=$obj->label;
                $i++;
            }
            return 1;
        }
        else {
            dol_print_error($this->db);
            return -1;
        }
    }
    
    /**
     *      Return list of Regimi Fiscali
     *
     *      @param	string	$selected        Id du type de paiement pre-selectionne
     *      @param  string	$htmlname        Nom de la zone select
     *      @param  string	$filtertype      Pour filtre
     *		@param	int		$addempty		Ajoute entree vide
     *		@return	void
     */
    function select_regimi_fiscali_fbi($selected='',$htmlname='regimi_fiscali_fbi_id',$filtertype=-1,$addempty=0)
    {
    	global $langs,$user;
    
    	$this->load_cache_regimi_fiscali_fbi();
    
    	print '<select class="flat" name="'.$htmlname.'">';
    	if ($addempty) print '<option value="0">&nbsp;</option>';
    	foreach($this->cache_regimi_fiscali_fbi as $id => $arrayconditions)
    	{
    		if ($selected == $id)
    		{
    			print '<option value="'.$id.'" selected="selected">';
    		}
    		else
    		{
    			print '<option value="'.$id.'">';
    		}
    		print $arrayconditions['label'];
    		print '</option>';
    	}
    	print '</select>';
    	//if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
    }

    function select_esenzione_to_pdf($selected='',$htmlname='esenzione_to_pdf_id',$filtertype=-1,$addempty=0)
    {
    	global $langs,$user;
    
    	    	
    	$array_c['S'] = array('label'=>$langs->trans("FactureBusinessITAESENZIONETOPDFalways"));
    	$array_c['M'] = array('label'=>$langs->trans("FactureBusinessITAESENZIONETOPDFnever"));
    	$array_c['F'] = array('label'=>$langs->trans("FactureBusinessITAESENZIONETOPDFnormal"));
    	 
    	
    	print '<select class="flat" name="'.$htmlname.'">';
    	if ($addempty) print '<option value="0">&nbsp;</option>';
    	foreach($array_c as $id => $arrayconditions)
    	{
    		if ($selected == $id)
    		{
    			print '<option value="'.$id.'" selected="selected">';
    		}
    		else
    		{
    			print '<option value="'.$id.'">';
    		}
    		print $arrayconditions['label'];
    		print '</option>';
    	}
    	print '</select>';
    	//if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
    }
    
    
}

