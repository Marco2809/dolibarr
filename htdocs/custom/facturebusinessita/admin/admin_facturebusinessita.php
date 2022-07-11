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
 * 	\file		admin/facturebusinessita.php
 * 	\ingroup	facturebusinessita
 * 	\brief		Module setup page
 * 				
 */


$res = @include "../../main.inc.php"; // From htdocs directory
if (! $res) {
	$res = @include "../../../main.inc.php"; // From "custom" directory
}

global $langs, $user;

require_once '../lib/facturebusinessita.lib.php';
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once "../class/facturebusinessita.class.php";
require_once "../class/html.formfacturebusinessita.class.php";


$langs->load("facturebusinessita@facturebusinessita");


// Access control
if (! $user->admin) {	accessforbidden(); }

// Parameters
$action = GETPOST('action', 'alpha');
$value = GETPOST('value','alpha');
$label = GETPOST('label','alpha');
$scandir = GETPOST('scandir','alpha');
$type = 'facturebusinessita';

$type_const=array('yesno','texte','chaine');


/*
 * Actions
 */

if ($action == 'update' || $action == 'add')
{
	$constname=GETPOST('constname','alpha');
	$constvalue=(GETPOST('constvalue_'.$constname) ? GETPOST('constvalue_'.$constname) : GETPOST('constvalue'));

	$consttype=GETPOST('consttype','alpha');
	$constnote=GETPOST('constnote');
	$res=dolibarr_set_const($db,$constname,$constvalue,$type_const[$consttype],0,$constnote,$conf->entity);

	if (! $res > 0) $error++;

	if (! $error)
	{
		$mesg = '<div class="ok">'.$langs->trans("SetupSaved").'</div>';
	}
	else
	{
		$mesg = '<div class="error">'.$langs->trans("Error").'</div>';
	}
}

else if ($action == 'updateMask')
{
	$maskconstfacturebusinessita=GETPOST('maskconstfacturebusinessita','alpha');
	$maskfacturebusinessita=GETPOST('maskfacturebusinessita','alpha');

	if ($maskconstfacturebusinessita) $res = dolibarr_set_const($db,$maskconstfacturebusinessita,$maskfacturebusinessita,'chaine',0,'',$conf->entity);

	if (! $res > 0) $error++;

	if (! $error)
	{
		setEventMessage($langs->trans("SetupSaved"));
	}
	else
	{
		setEventMessage($langs->trans("Error"),'errors');
	}
}

else if ($action == 'specimen')
{
	$modele=GETPOST('module','alpha');

	$facturebusinessita = new FactureBusinessITA($db);

	// Search template files
	$file=''; $classname=''; $filefound=0;
	$dirmodels=array_merge(array('/'),(array) $conf->modules_parts['models']);
	foreach($dirmodels as $reldir)
	{
		$file=dol_buildpath($reldir."/facturebusinessita/doc/pdf_".$modele.".modules.php",0);
		if (file_exists($file))
		{
			$filefound=1;
			$classname = "pdf_".$modele;
			break;
		}
	}

	if ($filefound)
	{
		require_once $file;

		$module = new $classname($db);

		if ($module->write_file($facturebusinessita,$langs) > 0)
		{
			header("Location: ".DOL_URL_ROOT."/document.php?modulepart=facturebusinessita&file=SPECIMEN.pdf");
			return;
		}
		else
		{
			setEventMessage($module->error,'errors');
			dol_syslog($module->error, LOG_ERR);
		}
	}
	else
	{
		setEventMessage($langs->trans("ErrorModuleNotFound"),'errors');
		dol_syslog($langs->trans("ErrorModuleNotFound"), LOG_ERR);
	}
}

// Activate a model
if ($action == 'set')
{
	$ret = addDocumentModel($value, $type, $label, $scandir);
}

else if ($action == 'del')
{
	$ret = delDocumentModel($value, $type);
	if ($ret > 0)
	{
		if ($conf->global->FACTUREBUSINESSITA_ADDON_PDF == "$value") dolibarr_del_const($db, 'FACTUREBUSINESSITA_ADDON_PDF',$conf->entity);
	}
}

// Set default model
else if ($action == 'setdoc')
{
	if (dolibarr_set_const($db, "FACTUREBUSINESSITA_ADDON_PDF",$value,'chaine',0,'',$conf->entity))
	{
		// La constante qui a ete lue en avant du nouveau set
		// on passe donc par une variable pour avoir un affichage coherent
		$conf->global->FACTUREBUSINESSITA_ADDON_PDF = $value;
	}

	// On active le modele
	$ret = delDocumentModel($value, $type);
	if ($ret > 0)
	{
		$ret = addDocumentModel($value, $type, $label, $scandir);
	}
}

else if ($action == 'setmod')
{
	// TODO Verifier si module numerotation choisi peut etre active
	// par appel methode canBeActivated

	dolibarr_set_const($db, "FACTUREBUSINESSITA_ADDON",$value,'chaine',0,'',$conf->entity);
}





/*
 * View
 */
$form=new FormFactureBusinessITA($db);
$dirmodels=array_merge(array('/'),(array) $conf->modules_parts['models']);

$page_name = "FactureBusinessITASetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
	. $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = facturebusinessitaAdminPrepareHead();
dol_fiche_head(
	$head,
	'settings',
	$langs->trans("Module190800Name"),
	0,
	"facturebusinessita@facturebusinessita"
);


/*
 * FactureBusinessITA Numbering model
*/

print_fiche_titre($langs->trans('FactureBusinessITAsNumberingModules'),'','');

print $langs->trans('FactureBusinessITAsNumberingModulesDescription');

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Name").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print '<td class="nowrap">'.$langs->trans("Example").'</td>';
print '<td align="center" width="60">'.$langs->trans("Status").'</td>';
print '<td align="center" width="16">'.$langs->trans("ShortInfo").'</td>';
print '</tr>'."\n";

clearstatcache();


foreach ($dirmodels as $reldir)
{
	$dir = dol_buildpath($reldir."facturebusinessita/core/modules/facturebusinessita/");
	
	if (is_dir($dir))
	{
		$handle = opendir($dir);
		if (is_resource($handle))
		{
			$var=true;

			while (($file = readdir($handle))!==false)
			{ 
				if (substr($file, 0, 23) == 'mod_facturebusinessita_' && substr($file, dol_strlen($file)-3, 3) == 'php')
				{
					$file = substr($file, 0, dol_strlen($file)-4);

					require_once $dir.$file.'.php';

					$module = new $file($db);

					// Show modules according to features level
					if ($module->version == 'development'  && $conf->global->MAIN_FEATURES_LEVEL < 2) continue;
					if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) continue;

					if ($module->isEnabled())
					{
						$var=!$var;
						print '<tr '.$bc[$var].'><td>'.$module->nom."</td><td>\n";
						print $module->info();
						print '</td>';

						// Show example of numbering model
						print '<td class="nowrap">';
						$tmp=$module->getExample();
						if (preg_match('/^Error/',$tmp)) print '<div class="error">'.$langs->trans($tmp).'</div>';
						elseif ($tmp=='NotConfigured') print $langs->trans($tmp);
						else print $tmp;
						print '</td>'."\n";

						print '<td align="center">';
						if ($conf->global->FACTUREBUSINESSITA_ADDON == $file)
						{
							print img_picto($langs->trans("Activated"),'switch_on');
						}
						else
						{
							print '<a href="'.$_SERVER["PHP_SELF"].'?action=setmod&amp;value='.$file.'">';
							print img_picto($langs->trans("Disabled"),'switch_off');
							print '</a>';
						}
						print '</td>';

						$facturebusinessita=new FactureBusinessITA($db);
						$facturebusinessita->initAsSpecimen();

						// Info
						$htmltooltip='';
						$htmltooltip.=''.$langs->trans("Version").': <b>'.$module->getVersion().'</b><br>';
						$facturebusinessita->type=0;
						$nextval=$module->getNextValue($mysoc,$facturebusinessita);
						if ("$nextval" != $langs->trans("NotAvailable"))	// Keep " on nextval
						{
							$htmltooltip.=''.$langs->trans("NextValue").': ';
							if ($nextval)
							{
								$htmltooltip.=$nextval.'<br>';
							}
							else
							{
								$htmltooltip.=$langs->trans($module->error).'<br>';
							}
						}

						print '<td align="center">';
						print $form->textwithpicto('',$htmltooltip,1,0);
						print '</td>';

						print "</tr>\n";
					}
				}
			}
			closedir($handle);
		}
	}
}
print "</table><br>\n";


/*
 * Document templates generators
 */

print_titre($langs->trans("FactureBusinessITAsModelModule"));

// Load array def with activated templates
$def = array();
$sql = "SELECT nom";
$sql.= " FROM ".MAIN_DB_PREFIX."document_model";
$sql.= " WHERE type = '".$type."'";
$sql.= " AND entity = ".$conf->entity;
$resql=$db->query($sql);
if ($resql)
{
	$i = 0;
	$num_rows=$db->num_rows($resql);
	while ($i < $num_rows)
	{
		$array = $db->fetch_array($resql);
		array_push($def, $array[0]);
		$i++;
	}
}
else
{
	dol_print_error($db);
}


print "<table class=\"noborder\" width=\"100%\">\n";
print "<tr class=\"liste_titre\">\n";
print '<td>'.$langs->trans("Name").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print '<td align="center" width="60">'.$langs->trans("Status")."</td>\n";
print '<td align="center" width="60">'.$langs->trans("Default")."</td>\n";
print '<td align="center" width="38">'.$langs->trans("ShortInfo").'</td>';
print '<td align="center" width="38">'.$langs->trans("Preview").'</td>';
print "</tr>\n";

clearstatcache();

$var=true;
foreach ($dirmodels as $reldir)
{
	foreach (array('','/doc') as $valdir)
	{
		$dir = dol_buildpath($reldir."/facturebusinessita".$valdir);
		if (is_dir($dir))
		{
			$handle=opendir($dir);
			if (is_resource($handle))
			{
				while (($file = readdir($handle))!==false)
				{
					$filelist[]=$file;
				}
				closedir($handle);
				arsort($filelist);

				foreach($filelist as $file)
				{
					if (preg_match('/\.modules\.php$/i',$file) && preg_match('/^(pdf_|doc_)/',$file))
					{

						if (file_exists($dir.'/'.$file))
						{
							$name = substr($file, 4, dol_strlen($file) -16);
							$classname = substr($file, 0, dol_strlen($file) -12);

							require_once $dir.'/'.$file;
							$module = new $classname($db);

							$modulequalified=1;
							if ($module->version == 'development'  && $conf->global->MAIN_FEATURES_LEVEL < 2) $modulequalified=0;
							if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) $modulequalified=0;

							if ($modulequalified)
							{
								$var = !$var;
								print '<tr '.$bc[$var].'><td width="100">';
								print (empty($module->name)?$name:$module->name);
								print "</td><td>\n";
								if (method_exists($module,'info')) print $module->info($langs);
								else print $module->description;
								print '</td>';

								// Active
								if (in_array($name, $def))
								{
									print '<td align="center">'."\n";
									print '<a href="'.$_SERVER["PHP_SELF"].'?action=del&value='.$name.'">';
									print img_picto($langs->trans("Enabled"),'switch_on');
									print '</a>';
									print '</td>';
								}
								else
								{
									print '<td align="center">'."\n";
									print '<a href="'.$_SERVER["PHP_SELF"].'?action=set&value='.$name.'&amp;scandir='.$module->scandir.'&amp;label='.urlencode($module->name).'">'.img_picto($langs->trans("Disabled"),'switch_off').'</a>';
									print "</td>";
								}

								// Defaut
								print '<td align="center">';
								if ($conf->global->FACTUREBUSINESSITA_ADDON_PDF == $name)
								{
									print img_picto($langs->trans("Default"),'on');
								}
								else
								{
									print '<a href="'.$_SERVER["PHP_SELF"].'?action=setdoc&value='.$name.'&amp;scandir='.$module->scandir.'&amp;label='.urlencode($module->name).'" alt="'.$langs->trans("Default").'">'.img_picto($langs->trans("Disabled"),'off').'</a>';
								}
								print '</td>';

								// Info
								$htmltooltip =    ''.$langs->trans("Name").': '.$module->name;
								$htmltooltip.='<br>'.$langs->trans("Type").': '.($module->type?$module->type:$langs->trans("Unknown"));
								if ($module->type == 'pdf')
								{
									$htmltooltip.='<br>'.$langs->trans("Width").'/'.$langs->trans("Height").': '.$module->page_largeur.'/'.$module->page_hauteur;
								}
								$htmltooltip.='<br><br><u>'.$langs->trans("FeaturesSupported").':</u>';
								$htmltooltip.='<br>'.$langs->trans("Logo").': '.yn($module->option_logo,1,1);
								$htmltooltip.='<br>'.$langs->trans("PaymentMode").': '.yn($module->option_modereg,1,1);
								$htmltooltip.='<br>'.$langs->trans("PaymentConditions").': '.yn($module->option_condreg,1,1);
								$htmltooltip.='<br>'.$langs->trans("MultiLanguage").': '.yn($module->option_multilang,1,1);
								//$htmltooltip.='<br>'.$langs->trans("Discounts").': '.yn($module->option_escompte,1,1);
								//$htmltooltip.='<br>'.$langs->trans("CreditNote").': '.yn($module->option_credit_note,1,1);
								$htmltooltip.='<br>'.$langs->trans("WatermarkOnDraftFactureBusinessITAs").': '.yn($module->option_draft_watermark,1,1);


								print '<td align="center">';
								print $form->textwithpicto('',$htmltooltip,1,0);
								print '</td>';

								// Preview
								print '<td align="center">';
								if ($module->type == 'pdf')
								{
									print '<a href="'.$_SERVER["PHP_SELF"].'?action=specimen&module='.$name.'">'.img_object($langs->trans("Preview"),'bill').'</a>';
								}
								else
								{
									print img_object($langs->trans("PreviewNotAvailable"),'generic');
								}
								print '</td>';

								print "</tr>\n";
							}
						}
					}
				}
			}
		}
	}
}

print '</table>';
print "<br>";


// Options
print_fiche_titre($langs->trans("FactureBusinessITAMainOptions"),'','');
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Description").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print '<td align="center">'.$langs->trans("Action").'</td>';
print "</tr>\n";


$var=!$var;
print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="update">';
print '<input type="hidden" name="constname" value="FACTUREBUSINESSITA_USE_FE">';
print '<tr '.$bc[$var].'><td>'.$langs->trans("FactureBusinessITAUseEFacture").'</td><td>';
print $form->selectyesno('constvalue',(! empty($conf->global->FACTUREBUSINESSITA_USE_FE)?$conf->global->FACTUREBUSINESSITA_USE_FE:0),1);
print '</td><td align="center" width="80">';
print '<input type="submit" class="button" value="'.$langs->trans("Update").'" name="Button">';
print "</td></tr>\n";
print '</form>';

$var=!$var;
print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="update">';
print '<input type="hidden" name="constname" value="FACTUREBUSINESSITA_USE_CI">';
print '<tr '.$bc[$var].'><td>'.$langs->trans("FactureBusinessITAUseCIPart").'</td><td>';
print $form->selectyesno('constvalue',(! empty($conf->global->FACTUREBUSINESSITA_USE_CI)?$conf->global->FACTUREBUSINESSITA_USE_CI:0),1);
print '</td><td align="center" width="80">';
print '<input type="submit" class="button" value="'.$langs->trans("Update").'" name="Button">';
print "</td></tr>\n";
print '</form>';

$var=!$var;
print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="update">';
print '<input type="hidden" name="constname" value="FACTUREBUSINESSITA_ESENZIONE_TO_PDF">';
print '<tr '.$bc[$var].'><td>'.$langs->trans("FactureBusinessITAESENZIONETOPDF").'</td><td>';
print $form->select_esenzione_to_pdf($conf->global->FACTUREBUSINESSITA_ESENZIONE_TO_PDF,'constvalue', 1, '', 0, '', '', 0);
print '</td><td align="center" width="80">';
print '<input type="submit" class="button" value="'.$langs->trans("Update").'" name="Button">';
print "</td></tr>\n";
print '</form>';


print '</table>';
print '<br>';


print_fiche_titre($langs->trans("FactureBusinessITAXMLOptions"),'','');
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Description").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print '<td align="center">'.$langs->trans("Action").'</td>';
print "</tr>\n";

$var=!$var;
print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="update">';
print '<input type="hidden" name="constname" value="FACTUREBUSINESSITA_REGIME_FISCALE">';
print '<tr '.$bc[$var].'><td>'.$langs->trans("FactureBusinessITARegimeFiscale").'</td><td>';
print $form->select_regimi_fiscali_fbi($conf->global->FACTUREBUSINESSITA_REGIME_FISCALE,'constvalue', 1, '', 0, '', '', 0);
print '</td><td align="center" width="80">';
print '<input type="submit" class="button" value="'.$langs->trans("Update").'" name="Button">';
print "</td></tr>\n";
print '</form>';



$var=!$var;
print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="update">';
print '<input type="hidden" name="constname" value="FACTUREBUSINESSITA_SOCIO_UNICO">';
print '<tr '.$bc[$var].'><td>'.$langs->trans("FactureBusinessITASocioUnico");
print $form->textwithtooltip("",$langs->trans("FactureBusinessITASocioUnicoTooltip"),2,1,img_info(''));
print '</td><td>';
print '<input type="text" name="constvalue" value="'.(! empty($conf->global->FACTUREBUSINESSITA_SOCIO_UNICO)?$conf->global->FACTUREBUSINESSITA_SOCIO_UNICO:'').'"  maxlength="2">';
print '</td><td align="center" width="80">';
print '<input type="submit" class="button" value="'.$langs->trans("Update").'" name="Button">';
print "</td></tr>\n";
print '</form>';


$var=!$var;
print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="update">';
print '<input type="hidden" name="constname" value="FACTUREBUSINESSITA_STATO_LIQUIDAZIONE">';
print '<tr '.$bc[$var].'><td>'.$langs->trans("FactureBusinessITAStatoLiquidazione");
print $form->textwithtooltip("",$langs->trans("FactureBusinessITAStatoLiquidazioneTooltip"),2,1,img_info(''));
print '</td><td>';
print '<input type="text" name="constvalue" value="'.(! empty($conf->global->FACTUREBUSINESSITA_STATO_LIQUIDAZIONE)?$conf->global->FACTUREBUSINESSITA_STATO_LIQUIDAZIONE:'').'"  maxlength="2">';
print '</td><td align="center" width="80">';
print '<input type="submit" class="button" value="'.$langs->trans("Update").'" name="Button">';
print "</td></tr>\n";
print '</form>';
print '</table>';
print '<br>';


$db->close();


// Page end
dol_fiche_end();
llxFooter();

