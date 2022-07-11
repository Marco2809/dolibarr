<?php
/* Copyright (C) 2002-2006 Rodolphe Quiedeville  <rodolphe@quiedeville.org>
 * Copyright (C) 2004      Eric Seigne           <eric.seigne@ryxeo.com>
 * Copyright (C) 2004-2012 Laurent Destailleur   <eldy@users.sourceforge.net>
 * Copyright (C) 2005      Marc Barilley / Ocebo <marc@ocebo.com>
 * Copyright (C) 2005-2013 Regis Houssin         <regis.houssin@capnetworks.com>
 * Copyright (C) 2006      Andre Cianfarani      <acianfa@free.fr>
 * Copyright (C) 2010-2012 Juanjo Menent         <jmenent@2byte.es>
 * Copyright (C) 2012      Christophe Battarel   <christophe.battarel@altairis.fr>
 * Copyright (C) 2013      Florian Henry		  	<florian.henry@open-concept.pro>
 * Copyright (C) 2013      Cédric Salvador       <csalvador@gpcsolutions.fr>
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
 *	\file       htdocs/compta/facture/list.php
 *	\ingroup    facture
 *	\brief      Page to create/see an invoice
 */

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/facture/modules_facture.php';
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/discount.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/invoice.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
if (! empty($conf->commande->enabled)) require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
if (! empty($conf->projet->enabled))
{
	require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
}


$langs->load('bills');
$langs->load('companies');
$langs->load('products');
$langs->load('main');

$sall=trim(GETPOST('sall'));
$projectid=(GETPOST('projectid')?GETPOST('projectid','int'):0);

$id=(GETPOST('id','int')?GETPOST('id','int'):GETPOST('facid','int'));  // For backward compatibility
$ref=GETPOST('ref','alpha');
$socid=GETPOST('socid','int');
$action=GETPOST('action','alpha');
$confirm=GETPOST('confirm','alpha');
$lineid=GETPOST('lineid','int');
$userid=GETPOST('userid','int');

if (! $sortorder) $sortorder='DESC';
if (! $sortfield) $sortfield='f.datef';
$limit = $conf->liste_limit;


$search_user = GETPOST('search_user','int');
$search_sale = GETPOST('search_sale','int');
$day	= GETPOST('day','int');
$month	= GETPOST('month','int');
$year	= GETPOST('year','int');
$day_i	= GETPOST('dayi','int');
$month_i	= GETPOST('monthi','int');
$year_i	= GETPOST('yeari','int');

$data_inizio=$_POST['data_inizio'];

if($data_inizio!=""){
    $datei=explode("/",$data_inizio);

    $day_i = $datei[0];
    $month_i = $datei[1];
    $year_i = $datei[2];

} else {
    $day_i = "01";
    $month_i = "01";
    $year_i = "2015";
}

$data_fine=$_POST['data_fine'];
if($data_fine!=""){
    $datef=explode("/",$data_fine);
    $day = $datef[0];
    $month = $datef[1];
    $year = $datef[2];
} else {
    $day= date("d");
    $month= date("m");
    $year= date("Y");
}

$filtre	= GETPOST('filtre');

// Security check
$fieldid = (! empty($ref)?'facnumber':'rowid');
if (! empty($user->societe_id)) $socid=$user->societe_id;
$result = restrictedArea($user, 'facture', $id,'','','fk_soc',$fieldid);

$object=new Facture($db);

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array('invoicelist'));

$now=dol_now();

/*
 * Actions
 */

$parameters=array('socid'=>$socid);
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks

/*
 * View
 */

llxHeader('',$langs->trans('Bill'),'EN:Customers_Invoices|FR:Factures_Clients|ES:Facturas_a_clientes');

$form = new Form($db);
$formother = new FormOther($db);
$formfile = new FormFile($db);
$bankaccountstatic=new Account($db);
$facturestatic=new Facture($db);
  $saldo_cassa=0;

if($user->rights->facture->creer){

/*$sql = 'SELECT';
$sql.= ' b.rowid, b.datev, b.amount, b.fk_account, b.emetteur';
$sql.= ' FROM '.MAIN_DB_PREFIX.'bank as b';
$sql.= ' WHERE ';
*/
$sql ='SELECT *  FROM (
    SELECT rowid,ref as facnumber,datef, total, total_ttc  FROM llx_facture_fourn';
if ($month > 0)
{
    $sql.= " WHERE datef BETWEEN '".$year_i. '-'. $month_i.'-'. $day_i."' AND '".$year. '-'. $month.'-'. $day."'";

}
$sql .= ' UNION SELECT rowid,facnumber,datef, total, total_ttc  FROM llx_facture ';
if ($month > 0)
{
    $sql.= " WHERE datef BETWEEN '".$year_i. '-'. $month_i.'-'. $day_i."' AND '".$year. '-'. $month.'-'. $day."'";

}
$sql .= ') as a order by datef,facnumber DESC';

//dol_print_error($db, $object->error);

/*$sql = 'SELECT';
$sql.= ' b.rowid, b.datev, b.amount, b.fk_account, b.emetteur,';
$sql.= ' p.fk_bank, p.note,p.rowid as paifk';
//$sql.= ' a.bank';
$sql.= ' FROM '.MAIN_DB_PREFIX.'bank as b';
$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'paiement as p ON b.rowid = p.fk_bank';
$sql.= ' WHERE ';
if ($month > 0)
{
    $sql.= " b.dateo BETWEEN '". $year_i. $month_i. $day_i."' AND '".$year. $month. $day."'";

}
$sql.= " ORDER BY b.datev ASC";
*/

$nbtotalofrecords = 0;
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST))
{
	$result = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($result);
}
//print $sql;
$resql = $db->query($sql);
if ($resql)
{


    $num = $db->num_rows($resql);

    $param='&socid='.$socid;
    $param.='&mainmenu=accountancy';
    if ($month)              $param.='&month='.$month;
    if ($year)               $param.='&year=' .$year;
    //print_barre_liste($langs->trans('BillsCustomers').' '.($socid?' '.$soc->nom:''),$page,$_SERVER["PHP_SELF"],$param,$sortfield,$sortorder,'',$num,$nbtotalofrecords);

    $i = 0;


    print '<form method="post" action="'.$_SERVER["PHP_SELF"]."?mainmenu=accountancy".'">'."\n";
    print '<table>';

    print '<tr><td class="fieldrequired">' . $langs->trans('Data Inizio') . '</td><td colspan="2">';
    $datefacture = dol_mktime(12, 0, 0, $month_i, $day_i, $year_i);
    $form->select_date($datefacture ? $datefacture : $data_inizio, 'data_inizio', '', '', '', "add", 1, 1);
    print '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans('Data Fine') . '</td><td colspan="2">';
    $datefacturef = dol_mktime(12, 0, 0, $month, $day, $year);
    $form->select_date($datefacturef ? $datefacturef : $data_fine, 'data_fine', '', '', '', "add", 1, 1);
    print '</td></tr>';
        print '<tr><td><input type="submit" name="genera" value="Genera"></td></tr>';
    print '</table>';
    print '</form>';
    if(isset($_POST['genera'])){
    print'<form action="./export_xml.php" method="post">';
    print '<table><tr><td align="right"><input type="submit" value="Esporta"><input type="hidden" name="data_inizio" value="'.$data_inizio.'"><input type="hidden" name="data_fine" value="'.$data_fine.'"></td></tr></table>';
    print '</form>';
    }
    print '<table class="liste" border="1" width="100%">';

    print '<tr class="liste_titre"><td colspan="3"></td>'
            . '<td class="nowrap" align="center" colspan="3">Cassa</td>'
            . '<td class="nowrap" align="center"></td>'
            //. '<td class="nowrap" align="center"></td>'
            . '</tr>';
     print '<tr class="liste_titre"><td align="center">DATA</td>'
     . '<td class="nowrap" align="center">Fattura</td>'
     . '<td class="nowrap" align="center">Oggetto</td>'
            . '<td class="nowrap" align="center">Entrate<br>Cassa</td>'
            . '<td class="nowrap" align="center">Uscita<br>Cassa</td>'
            . '<td class="nowrap" align="center">Saldo<br>Cassa</td>'
             . '<td class="nowrap" align="center">Saldo generale</td>'
             //. '<td class="nowrap" align="center">Causale</td>'
            . '</tr>';

  $saldo_generale=$saldo_cassa;

        while($objp = $db->fetch_object($resql))
        {
            if(substr($objp->facnumber,0,2)=="FA"){
                $sql_oggetto = "SELECT oggetto_fatt as oggetto FROM llx_facture_extrafields WHERE fk_object ='".$objp->rowid."'";
                $resql_ogg = $db->query($sql_oggetto);
                $obj_ogg = $db->fetch_object($resql_ogg);
            }
            else {
                $sql_oggetto = "SELECT fornitore_oggetto as oggetto FROM llx_facture_fourn_extrafields WHERE fk_object ='".$objp->rowid."'";
                $resql_ogg = $db->query($sql_oggetto);
                $obj_ogg = $db->fetch_object($resql_ogg);
            }

            $var=!$var;

            if(substr($objp->facnumber,0,2)=="FA") $saldo_generale+=$objp->total_ttc;
            else $saldo_generale-=$objp->total_ttc;

            if(substr($objp->facnumber,0,2)=="FA") $saldo_cassa=$saldo_cassa+$objp->total_ttc;
            else $saldo_cassa=$saldo_cassa-$objp->total_ttc;

            print '<tr '.$bc[$var].'>';


			// Data Pagamento
			print '<td class="nowrap">';
			print $objp->datef;
            print '</td>';

            // Numero Fattura
			print '<td class="nowrap">';
			print $objp->facnumber;
            print '</td>';

            // Oggetto
			print '<td class="nowrap">';
			print wordwrap($obj_ogg->oggetto, 80 , "<br />");
			print '</td>';

                        // Cassa

         if(substr($objp->facnumber,0,2)=="FA")  print '<td align="right" style="color:blue;">'.price($objp->total_ttc,0,$langs).'</td>';
         else print '<td align="right"></td>';
         if(substr($objp->facnumber,0,2)=="SI")  print '<td align="right" style="color:red;">'.price(-$objp->total_ttc,0,$langs).'</td>';
         else print '<td align="right"></td>';

            print '<td align="right">'.price($saldo_cassa,0,$langs).'</td>';

        print '<td align="right">'.price($saldo_generale,0,$langs).'</td>';
        //print '<td align="center">'.$objp->note.'</td>';
        }

    print "</table>\n";
    print "</form>\n";
    $db->free($resql);


llxFooter();
$db->close();
}

} else {

print "Non disponi dei privilegi necessari per visualizzare questa pagina";

}
