
<?php 

if (! $res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}

if (! $res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (! $res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
dol_include_once('/compta/facture/class/facture.class.php');


/* Controllo che esista l'extensione xls*/
llxHeader('',$title);
if (!extension_loaded('xsl')) {
	
	echo "PHP XSL library is not installed on your web server";
	llxFooter();
	$db->close();
	die();
}


$idfa = GETPOST('id',"int");

if ($user->societe_id) $socid = $user->societe_id;
$result = restrictedArea($user, 'facture', $idfa, '', '', 'fk_soc', $fieldid);


$object = new Facture($db);
$object->fetch($idfa);

$objectref = dol_sanitizeFileName($object->ref);
$dir = $conf->facture->dir_output;
$file = $dir . "/" . $objectref . "/" .'*.xml';

$g = glob($file);

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
$xls = $res."/lib/fatturapa/fatturapa_v1.1.xsl";


//$xls_file = (file_exists(DOL_DOCUMENT_ROOT.'/facturebusinessita')?DOL_URL_ROOT.'/facturebusinessita/view_fatturapa.php':DOL_URL_ROOT.'/custom/facturebusinessita/view_fatturapa.php')

$XML = new DOMDocument();
$XML->load($g[0]);

# START XSLT
$xslt = new XSLTProcessor();
$XSL = new DOMDocument();
$XSL->load($xls);
$xslt->importStylesheet( $XSL );
print $xslt->transformToXML( $XML );
llxFooter();
$db->close();
die();
?>