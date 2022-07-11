<?php
/* Copyright (C) 2010-2011 Laurent Destailleur <ely@users.sourceforge.net>
 * Copyright (C) 2015			 Paolo Selis				 <p.selis@19.coop>

 * This program is free software; you can redistribute it and/or modify
//  * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 *	\file       htdocs/core/modules/facture/doc/pdf_fatturaelettronica_fbi.modules.php
 *	\ingroup    facturebusinessita
 *	\brief      File of class to build ODT documents for third parties
 *	\author	    Paolo Selis
 */


/**
 * TODO: 
 *  - Regimi fiscali -> da aggiungere in altre impostazionie
 *  - Sezione anagrafica CedentePrestatore: Codice EORI 
 *  - Sezione anagrafica CedentePrestatore: Campi relativi all'albo professionale
 *  
 *  
 *  - Validazione
 *  
 * 
 */


require_once DOL_DOCUMENT_ROOT.'/core/modules/facture/modules_facture.php';
require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
dol_include_once('/facturebusinessita/lib/facturebusinessita.lib.php');



/**
 *	\class      pdf_fatturaelettronica_fbi
 *	\brief      Class to build documents using ODF templates generator
 */
class pdf_fatturaelettronica_fbi extends ModelePDFFactures
{
	var $emetteur;	// Objet societe qui emet

	var $phpmin = array(5,2,0);	// Minimum version of PHP required by module
	var $version = 'dolibarr';

	var $dati_pagamento;
	var $error =  array();
	
	var $idfiscale;
	var $idfiscaletr;
	var $cf;
	var $esigibilita_iva;
	var $rif_ad_126;
	
	
	private $fc;
	/**
	 *		\brief  Constructor
	 *		\param	db		Database handler
	 */
	function pdf_fatturaelettronica_fbi($db)
	{
		global $conf,$langs,$mysoc;

		$langs->load("main");
		$langs->load("companies");

		$this->db = $db;
		$this->name = "Fattura Elettronica v. 1.1";
		$this->description = "Template XML per la generazione della Fattura Elettronica verso la PA: versione specifiche tecniche 1.1 ";

		// Get source company
		$this->emetteur=$mysoc;
		$this->type = 'xml';
		
		
	}



	/**
	 *	Function to build a document on disk using the generic odt module.
	 *	@param	    object				Object source to build document
	 *	@param		outputlangs			Lang output object
	 * 	@param		srctemplatepath	    Full path of source filename for generator using a template file
	 *	@return	    int         		1 if OK, <=0 if KO
	 */
	function write_file($object,$outputlangs,$srctemplatepath='',$hidedetails=0,$hidedesc=0,$hideref=0)
	{
		global $user,$langs,$conf,$mysoc;

		$langs->Load("facturebusinessita@facturebusinessita");
		
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
		$fatturapa_path = $res."/lib/fatturapa/";
		
		
		
		
		
		
		
		if (! is_object($outputlangs)) $outputlangs=$langs;
		$sav_charset_output=$outputlangs->charset_output;
		$outputlangs->charset_output='UTF-8';

		$outputlangs->load("main");
		$outputlangs->load("dict");
		$outputlangs->load("companies");
		$outputlangs->load("bills");

		if ($conf->facture->dir_output) {
			if (! is_object($object)) 	{
					$id = $object;
					$object = new Facture($this->db);
					$object->fetch($id);
			}
			/* mi serve il codice della società */				
			if($mysoc->tva_intra)	$soc = $mysoc->tva_intra;
			else $soc = $mysoc->country_code.$mysoc->idprof4;
				
			/* qua c'è un po di casino a seconda che 
			 * la partita iva sia VIES o no*/
			if(substr($soc,0,2) == $mysoc->country_code){
				$this->idfiscale=substr($soc,2,strlen($soc));
				$this->idfiscaletr= $this->idfiscale;
			} else { 
				$this->idfiscale=$soc;
				$this->idfiscaletr=$mysoc->idprof4;
			} 
					
			/*Codice fiscale*/	
			$this->cf = $mysoc->idprof4;

			/* Esigibilità iva*/
			if($object->array_options['options_esigibilita_iva_facturebusinessita']){
				$sql = 'SELECT code FROM '.MAIN_DB_PREFIX.'c_facturebusinessita_esigibilitaiva  WHERE rowid = ' .$object->array_options['options_esigibilita_iva_facturebusinessita'];;
				dol_syslog("template_eFattura::select sql=".$sql);
				$resql = $this->db->query($sql);
				$obj = $this->db->fetch_object($resql);
				$this->esigibilita_iva = $obj->code;
			}	
			
			
			
			/* RiferimentoAmministrazione 1.2.6 */
			$this->rif_ad_126 =  $object->array_options['options_rifadm126_facturebusinessita'];
			
			
			$nfatt = $object->ref; 
				
			/* mi serve anche il progressivo */
			$progressivo = $conf->global->FACTUREBUSINESSITA_PROGRESSIVO_FE;
								
			/* questo sarà il nome del file. deve essere fatto in questo modo altrimenti
			 * non verrà accettato dal sistema di interscambio*/
			$nomefatt = $mysoc->country_code.$this->idfiscale."_".sprintf("%04d", $progressivo).".xml";
					
			$objectref = dol_sanitizeFileName($object->ref);
			$dir = $conf->facture->dir_output ."/" . $objectref;
			$file = $dir . "/".$nomefatt;
			
			if (! file_exists($dir))	// se il file non esiste allora creo la cartella relativa (per il clone della fattura)
			{
				if (dol_mkdir($dir) < 0)
				{
					$this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
					return 0;
				}
			}
		
			$file_err = $dir . "/" . $objectref . "/errori.txt";
				
			/* Inizio a creare l'xml con la radice */
			$this->fc =  simplexml_load_string('<p:FatturaElettronica versione="1.1" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:p="http://www.fatturapa.gov.it/sdi/fatturapa/v1.1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"></p:FatturaElettronica> ','SimpleXMLElement', LIBXML_NOCDATA);
			/* creo un DOMElements dal SimpleXMLElements */		
			$domdocument = dom_import_simplexml($this->fc);
		
				
			/* Creo la sezione header che a sua volta è fatta di varie parti */
			$header = simplexml_load_string('<FatturaElettronicaHeader></FatturaElettronicaHeader>','SimpleXMLElement', LIBXML_NOCDATA);
			$headerdoc = dom_import_simplexml($header);
			
			$node =  dom_import_simplexml($this->getDatiTrasmissione($object->client->array_options['options_codice_univoco_ufficio_facturebusinessita'], $progressivo));
			$node  = $headerdoc->ownerDocument->importNode($node, TRUE);
			$headerdoc->appendChild($node);
		
			$node2 =  dom_import_simplexml($this->getCedentePrestatore());
			$node2  = $headerdoc->ownerDocument->importNode($node2, TRUE);
			$headerdoc->appendChild($node2);
		
			$node3 =  dom_import_simplexml($this->getCessionarioCommittente($object->client));
			$node3  = $headerdoc->ownerDocument->importNode($node3, TRUE);
			$headerdoc->appendChild($node3);
		
				
			/* Creo la sezione body che a sua volta è fatta di varie parti */
		
			$body = simplexml_load_string('<FatturaElettronicaBody></FatturaElettronicaBody>','SimpleXMLElement', LIBXML_NOCDATA);
			$bodydoc = dom_import_simplexml($body);
		
			$dg = simplexml_load_string('<DatiGenerali></DatiGenerali>','SimpleXMLElement', LIBXML_NOCDATA);
			$dgdoc = dom_import_simplexml($dg);
				
			$node4 =  dom_import_simplexml($this->getDatiGeneraliDocumento($object,$nfatt));
			$node4  = $dgdoc->ownerDocument->importNode($node4, TRUE);
			$dgdoc->appendChild($node4);
		
		
			$ret = $this->getDatiDatiOrdineAcquisto($object);
	
			if($ret){
         foreach($ret as $r){
		     	$dgr = simplexml_load_string($r,'SimpleXMLElement', LIBXML_NOCDATA);
			    $node4bis =  dom_import_simplexml($dgr);
			    $node4bis  = $dgdoc->ownerDocument->importNode($node4bis, TRUE);
			    $dgdoc->appendChild($node4bis);
         }
			}
				
				
			$dati_generali = $bodydoc->ownerDocument->importNode($dgdoc, TRUE);
			$bodydoc->appendChild($dati_generali);
				
			$dbsdoc =  dom_import_simplexml($this->getDettaglioLinee($object->lines));
      
			//$node6 =  dom_import_simplexml($this->getDatiRiepilogo());
      
			$ret = $this->getDatiRiepilogo();
			
			if($ret){
				foreach($ret as $r){
					$dtr = simplexml_load_string($r,'SimpleXMLElement', LIBXML_NOCDATA);
					$node6bis =  dom_import_simplexml($dtr);
					$node6bis  = $dbsdoc->ownerDocument->importNode($node6bis, TRUE);
					$dbsdoc->appendChild($node6bis);
				}
			}
 			//$node6  = $dbsdoc->ownerDocument->importNode($node6, TRUE);
  		//$dbsdoc->appendChild($node6);

			$dati_beniservizi = $bodydoc->ownerDocument->importNode($dbsdoc, TRUE);
			$bodydoc->appendChild($dati_beniservizi);
		
			$node7 =  dom_import_simplexml($this->getDatiPagamento($object));
			$node7  = $bodydoc->ownerDocument->importNode($node7, TRUE);
			$bodydoc->appendChild($node7);

			$upload_dir = $conf->facture->dir_output.'/'.dol_sanitizeFileName($object->ref);
			$sortfield="name";
			$exclude[] = "\.meta$";
			$exclude[] = "\.xml$";
			//$exclude[] = "\.p7m$";
			$exclude[] = dol_sanitizeFileName($object->ref).".pdf$";
		
			$filearray = dol_dir_list($upload_dir,"files",0,'',$exclude,$sortfield,(strtolower($sortorder)=='desc'?SORT_DESC:SORT_ASC),1);
		
			foreach ($filearray as $fileall){
			  $ret = $this->getAllegato($fileall);
			  if($ret){
			    $node8 =  dom_import_simplexml($ret);
			    $node8  = $bodydoc->ownerDocument->importNode($node8, TRUE);
			    $bodydoc->appendChild($node8);
			  }
			}

				
			/* importo l'header nel doc principale*/
			$h  = $domdocument->ownerDocument->importNode($headerdoc, TRUE);
			$domdocument->appendChild($h);
		
			/* importo il body nel doc principale*/
			$b  = $domdocument->ownerDocument->importNode($bodydoc, TRUE);
			$domdocument->appendChild($b);
		
			$tmp = new DOMDocument( "1.0", "UTF-8" );
			$domdocument  = $tmp->importNode($domdocument, TRUE);
			$tmp->appendChild( $domdocument );
			$tmp->formatOutput = true;
			$tmp->preserveWhiteSpace = false;

			/* Rimuovo i tag vuoti */
			$xpath = new DOMXPath($tmp);
			foreach( $xpath->query('//*[not(node())]') as $node ) {
				$node->parentNode->removeChild($node);
			}
			foreach( $xpath->query('//*[not(node())]') as $node ) {
				$node->parentNode->removeChild($node);
			}
				
			$xml = new DOMDocument( "1.0", "UTF-8" );
			$xml->formatOutput = true;
			$xml->preserveWhiteSpace = false;
			$xml->loadXML($tmp->saveXML());
			
			foreach ($this->error as $err)
				$object->error[] = $err;
				
			libxml_use_internal_errors(true);
			
			if (!$xml->schemaValidate($fatturapa_path.'fatturapa_v1.1.xsd')) {
				
				$object->error[] = "Il file non è conforme con le specifiche della fattura pa v1.1";

				$errors = libxml_get_errors();
				
				foreach ($errors as $error) {

					if(!empty($conf->global->FACTUREBUSINESSITA_XML_VERBOSE_ERROR)){
						if($conf->global->FACTUREBUSINESSITA_XML_VERBOSE_ERROR == 1)
							$object->error[] = $error->message;
					}
					$e = explode(":",$error->message);
					$e1 = str_replace('Element ', '', $e[0]);
					$error_string[$e1] = " - ". $langs->trans('ErrorXMLElement')." ".$e1;
					
				}
				
				foreach ($error_string as $k=>$v)
					$object->error[] = $v;
				
				
				
			} else {
				$xmlout = $xml->saveXML();
			}
				

				
			if(sizeof($object->error)){
				return -1;
			} else {
				file_put_contents($file, $xmlout);
				return 1;
			}			
					

		}
		return 0;
	}
	
	
	
	/**
	 * 
	 * @param unknown $codicePA
	 * @param string $progressivo
	 */
	private function getDatiTrasmissione($codicePA,$progressivo='0001') {
    if($codicePA=="") $this->error[] = "Manca il codice Destinatario";
			$header = "
			<DatiTrasmissione>
			 <IdTrasmittente>
			  <IdPaese>".$this->emetteur->country_code."</IdPaese>
			  <IdCodice>".$this->idfiscaletr."</IdCodice>
			 </IdTrasmittente>
			 <ProgressivoInvio>".$progressivo."</ProgressivoInvio>
			 <FormatoTrasmissione>SDI11</FormatoTrasmissione>
			 <CodiceDestinatario>".$codicePA."</CodiceDestinatario>
			 <ContattiTrasmittente>
			  <Telefono>".$this->emetteur->phone."</Telefono>
			  <Email>".$this->emetteur->email."</Email>
			 </ContattiTrasmittente>
			</DatiTrasmissione>";
		return simplexml_load_string($header,'SimpleXMLElement', LIBXML_NOCDATA);
	}
	
	/**
	 * 
	 */
	private function getCedentePrestatore() {
		global $conf;
		
		/* Vado a leggere il campo rea */
		/* Se è stato scritto qualcosa allora mi vado a prendere anche altri valori*/
		$rea = $this->emetteur->idprof2;
		if($rea){
			/* il campo rea per funzionare deve essere compilato con provincia + numero ex: GE34556 */
			$rea_uff = substr($rea, 0,2);
			$rea_num = trim(str_replace("-", "", substr($rea,2)));
			/* il capitale sociale che si trova nell'anagrafica azienda*/
			$cap_soc = !empty($this->emetteur->capital) ? number_format($this->emetteur->capital,2, '.', '') : '';
			/* e i campi sato liquidazione e socio unico che invece sanno nella conf del modulo */
			$su = $conf->global->FACTUREBUSINESSITA_SOCIO_UNICO;
			$sl = $conf->global->FACTUREBUSINESSITA_STATO_LIQUIDAZIONE;
			
		}
		
		if(!empty($conf->global->FACTUREBUSINESSITA_REGIME_FISCALE)){
			$sql = 'SELECT code FROM '.MAIN_DB_PREFIX.'c_facturebusinessita_regimifiscali WHERE rowid = '.$conf->global->FACTUREBUSINESSITA_REGIME_FISCALE;
			dol_syslog("template_eFattura::select sql=".$sql);
			$resql = $this->db->query($sql);
			$obj = $this->db->fetch_object($resql);
			$rf = $obj->code;
		}
		
			$xml="
			<CedentePrestatore>
			  <DatiAnagrafici>
			    <IdFiscaleIVA>
			      <IdPaese>".$this->emetteur->country_code."</IdPaese>
			      <IdCodice>".$this->idfiscale."</IdCodice>
			    </IdFiscaleIVA>
			    <CodiceFiscale>".$this->cf."</CodiceFiscale>
			    <Anagrafica>
			     <Denominazione>".$this->emetteur->name."</Denominazione>
			    </Anagrafica>
			    <RegimeFiscale>".$rf."</RegimeFiscale>
			  </DatiAnagrafici>
			   <Sede>
			    <Indirizzo>".$this->emetteur->address."</Indirizzo>
			    <CAP>".$this->emetteur->zip."</CAP>
			    <Comune>".$this->emetteur->town."</Comune>
			    <Provincia>".getState($this->emetteur->state_id,2)."</Provincia>
			    <Nazione>".$this->emetteur->country_code."</Nazione>
			   </Sede>
			   <IscrizioneREA><Ufficio>".$rea_uff."</Ufficio><NumeroREA>".$rea_num."</NumeroREA><CapitaleSociale>".$cap_soc."</CapitaleSociale><SocioUnico>".$su."</SocioUnico><StatoLiquidazione>".$sl."</StatoLiquidazione></IscrizioneREA>
			   <RiferimentoAmministrazione>". substr($this->removeInvalidChar($this->rif_ad_126),0,19) ."</RiferimentoAmministrazione> 					
				</CedentePrestatore>";

		return simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA);
	}
	
	/**
	 * 
	 * @param unknown $cliente
	 */
	private function getCessionarioCommittente($cliente) {

		
		$idfiscale_cliente = str_replace($cliente->country_code, '', $cliente->tva_intra);
		$cf = $cliente->idprof4;

	  $xml="
		<CessionarioCommittente>
		  <DatiAnagrafici>";
	     if($idfiscale_cliente != ""){ 
	      $xml .="
	     <IdFiscaleIVA>
		    <IdPaese>".$cliente->country_code."</IdPaese>
		    <IdCodice>".$idfiscale_cliente."</IdCodice>
		   </IdFiscaleIVA>";
	     }
	    if($cf){  		
		 	$xml .=" 
		 		<CodiceFiscale>".$cf."</CodiceFiscale>";
	    }
		  $xml .= 
		  "<Anagrafica>
		   <Denominazione>".$cliente->name."</Denominazione>
		  </Anagrafica>
		 </DatiAnagrafici>
		 <Sede>
		  <Indirizzo>".$cliente->address."</Indirizzo>
		  <CAP>".$cliente->zip."</CAP>
		  <Comune>".$cliente->town."</Comune>
		  <Provincia>".getState($cliente->state_id,2)."</Provincia>
		  <Nazione>".$cliente->country_code."</Nazione>
		 </Sede>
		</CessionarioCommittente>";
	
		return simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA);
	}
	
	/**
	 * 
	 * @param unknown $object
	 */
	private function getDatiGeneraliDocumento($object,$nfatt){
	    global $conf,$langs;
		$xml="<DatiGeneraliDocumento>";
		  if($object->type=='2')
		    $xml .= "<TipoDocumento>TD04</TipoDocumento>";
		  else
		    $xml .= "<TipoDocumento>TD01</TipoDocumento>";
		  
		 $xml .="<Divisa>".$conf->currency."</Divisa>
		    <Data>".dol_print_date($object->date,"%Y-%m-%d")."</Data>
		    <Numero>".$nfatt."</Numero>
		    <ImportoTotaleDocumento>".number_format($object->total_ttc,2, '.', '')."</ImportoTotaleDocumento>";

		//ServiceTech
		//if($object->note_public)
		    //$xml .="<Causale>". substr($this->removeInvalidChar($object->note_public),0,199)."</Causale>";
		if (! empty($object->array_options['options_cig']) or !empty($object->array_options['options_num_ordine']) or !empty($object->array_options['options_oggetto_fatt']))
                {
			$causale="Num. Ordine: ".$object->array_options['options_num_ordine']." CIG: ".$object->array_options['options_cig']." Oggetto: ".$object->array_options['options_oggetto_fatt'];
			$xml .="<Causale>". substr($this->removeInvalidChar($causale),0,199)."</Causale>";
		}		    
		 if($conf->global->FACTUREBUSINESSITA_ADDON == 'mod_facturebusinessita_ironman' || $conf->global->FACTUREBUSINESSITA_ADDON == 'mod_facturebusinessita_thor' )
			$xml .="<Art73>SI</Art73>";

		 $xml .="</DatiGeneraliDocumento>";
		return simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA);
	}
	
	
	/**
	 * 
	 * @param unknown $object
	 */
	private function getDatiDatiOrdineAcquisto($object){

	
		/* Vedo se esiste una corrispondenza con l'oggetto commande */
		//$sql = 'SELECT fk_source FROM '.MAIN_DB_PREFIX.'element_element WHERE fk_target = ' .$object->id. ' and targettype=\'facture\' ';
		//dol_syslog("template_eFattura::select sql=".$sql);
		//$resql = $this->db->query($sql);
		//$obj = $this->db->fetch_object($resql);
		//$idcommande = $obj->fk_source;
		
		//if($idcommande){
		 /* se esiste un collegamento con l'ordine allora potrebbe esistere un collegamento con le righe*/
					    
		    /* mi servono anchglobal $conf;e alcuni riferimenti dell'ordine*/
		 //   $ordine = new Commande($this->db);
		 //   $ordine->fetch($idcommande);
		//}
		    
		/* mi muovo in base alle righe della fattura*/
		$lines = $object->lines;
		$rang_increment = false;
		
		unset($xml);
		
		foreach ($lines as $line){
		    	
			$line->fetch_optionals($line->rowid);
			
			$cup = $line->array_options['options_codicecup_facturebusinessita'];
			$cig = $line->array_options['options_codicecig_facturebusinessita'];
			$ndoc = $line->array_options['options_ndoc_facturebusinessita'];
			$tipodoc = $line->array_options['options_tipodoc_facturebusinessita'];
			$datadoc = $line->array_options['options_datadoc_facturebusinessita'];
			
      if($tipodoc=='1') $x1="DatiContratto";
      else if($tipodoc=='2') $x1="DatiConvenzione";
      else if($tipodoc=='3') $x1="DatiOrdineAcquisto";
      else $x1="";

      // faccio un controllo manuale 
      if($x1 == "" && ($cup || $cig || $ndoc || $tipodoc || $datadoc )){
      	$this->error[] = "E' necessario specificare il tipo di documento (Contratto/Ordine/Convenzione)";
      	return;
      }
      if($x1 !=""){ 
      /* incremento il numero di linea se questo parte da zero */
			if($line->rang == 0 || $rang_increment){
				$line->rang++;
				$rang_increment = true;
			}
      	      
   	   $xml[$line->rang] = "
      		<".$x1.">
      			<RiferimentoNumeroLinea>".$line->rang."</RiferimentoNumeroLinea>
      			<IdDocumento>".$ndoc."</IdDocumento>
      			<Data>".dol_print_date($datadoc,"%Y-%m-%d")."</Data>
      			<CodiceCUP>".$cup."</CodiceCUP>
      			<CodiceCIG>".$cig."</CodiceCIG>				
      		</".$x1.">";
	      }
	     }
	      
	  	if($xml) return $xml;
      else return ;
	}

	
	
	
	private function getDettaglioLinee($lines){
		global $langs;
		
		/*numero di linee */
		$nblignes = sizeof($lines);	
		$rang_increment = false;
		
		/* Faccio un ciclo di tutte le linee */

		foreach ($lines as $line){
		
			$line->fetch_optionals($line->rowid);
			
			/* incremento il numero di linea se questo parte da zero */
			if($line->rang == 0 || $rang_increment){
				$line->rang++;
				$rang_increment = true;
			}

			
			
			if($line->array_options['options_fk_iva_esenzioni_facturebusinessita']){
				$sql = 'SELECT code FROM '.MAIN_DB_PREFIX.'facturebusinessita_iva_esenzioni  WHERE rowid = ' .$line->array_options['options_fk_iva_esenzioni_facturebusinessita'];;
				dol_syslog("template_eFattura::select sql=".$sql);
				$resql = $this->db->query($sql);
				$obj = $this->db->fetch_object($resql);
				$natura = $obj->code;
			}else $natura = '';
			
			/* metto daglobal $conf; parte alcuni dati*/
			$this->dati_pagamento[$line->tva_tx.$natura]["aliquota"] = $line->tva_tx;
			$this->dati_pagamento[$line->tva_tx.$natura]["totale_iva"] += $line->total_tva;
			$this->dati_pagamento[$line->tva_tx.$natura]["totale_imponibile"] += $line->total_ht;
			$this->dati_pagamento[$line->tva_tx.$natura]["natura"] = $natura;
			$this->dati_pagamento[$line->tva_tx.$natura]["natura_rif"] = $natura;
				
	    $linea="
		      <DettaglioLinee>
						<NumeroLinea>".$line->rang."</NumeroLinea>
						<Descrizione>".substr($this->removeInvalidChar(facturebusinessitaRemoveEsenzioni($line->desc)),0,999)."</Descrizione>
						<Quantita>".number_format($line->qty,2, '.', '')."</Quantita>
						<PrezzoUnitario>".number_format($line->subprice,2, '.', '')."</PrezzoUnitario>";
		    
		    if($line->remise_percent > 0){
		    	$remise = (float)(($line->subprice * $line->qty) - $line->total_ht);
		    	$linea.="<ScontoMaggiorazione>
											<Tipo>SC</Tipo>
											<Percentuale>".number_format($line->remise_percent ,2 , '.', '')."</Percentuale>
										  <Importo>".number_format($remise ,2 , '.', '') ."</Importo>
									 </ScontoMaggiorazione>";
		    }
				$linea.="<PrezzoTotale>".number_format($line->total_ht,2, '.', '')."</PrezzoTotale>
						<AliquotaIVA>".number_format($line->tva_tx,2, '.', '')."</AliquotaIVA>
					  <Natura>".$natura."</Natura>
					  <RiferimentoAmministrazione>".substr($this->removeInvalidChar($line->array_options['options_rifadm22115_facturebusinessita']),0,29)."</RiferimentoAmministrazione>";

				$linea.="</DettaglioLinee>";
		    $linee .= $linea;
		}
		
		
		return simplexml_load_string("<DatiBeniServizi>".$linee."</DatiBeniServizi>",'SimpleXMLElement', LIBXML_NOCDATA);
	}
	
	private function getDatiRiepilogo(){
	    global $conf;
	    unset($xml);
	    $this->esigibilita_iva = ($this->esigibilita_iva == '') ? 'S' : $this->esigibilita_iva; 
			$rif = ($this->esigibilita_iva == 'S') ? 'scissione dei pagamenti ai sensi art. 17-ter del D.P.R. 633/1972' : '';
	    
	    foreach($this->dati_pagamento as $dati){
		    	$dr ="
		    	<DatiRiepilogo>
		       <AliquotaIVA>".number_format($dati[aliquota],2, '.', '') ."</AliquotaIVA>
		       <Natura>".$dati['natura'] ."</Natura>
		       <ImponibileImporto>".number_format($dati[totale_imponibile],2, '.', '') ."</ImponibileImporto>
		       <Imposta>".number_format($dati[totale_iva],2, '.', '') ."</Imposta>
		       <EsigibilitaIVA>".$this->esigibilita_iva."</EsigibilitaIVA> 		
		       <RiferimentoNormativo>".$rif."</RiferimentoNormativo>		
		     </DatiRiepilogo>";
		    	$xml[] = $dr;
		  }
		  
		  
		 // echo $xml;
		//return simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA);
		return $xml;
	}

	/**
	 * 
	 * @param Facture $object
	 * @return SimpleXMLElement
	 */
	private function getDatiPagamento(Facture $object){
		
	
		
		$idbanca = $object->array_options['options_fk_bank_account_facturebusinessita'];
		
		
	  if($idbanca) {
	  	$account = new Account($this->db);
	  	$account->fetch($idbanca);
	  }	

	  /* Modalità di pagamento*/
	  switch ($object->mode_reglement_code){
	  	case 'VIR': $mp = 'MP05'; break;
	  	case 'TIP':	$mp = 'MP09'; break;
  		case 'LIQ': $mp = 'MP01'; break;
  		case 'CHQ': $mp = 'MP02'; break;
  		case 'PRE': $mp = 'MP16'; break;
  		case 'CB':  $mp = 'MP08'; break;
	  }
	  
	  if($object->cond_reglement_code != 'NN'){
	  	$data_scadenza_pag = dol_print_date($object->date_lim_reglement,"%Y-%m-%d");
	  }
	   /*modificato da zavattolo domenico il 18 giugno 2015 per la scissione dell'iva
		$xml="<DatiPagamento>
		    	<CondizioniPagamento>TP02</CondizioniPagamento>
			    <DettaglioPagamento>
			    <ModalitaPagamento>$mp</ModalitaPagamento>
			    <DataScadenzaPagamento>$data_scadenza_pag</DataScadenzaPagamento>
			    <ImportoPagamento>".number_format($object->total_ttc,2, '.', '')."</ImportoPagamento>";
		*/
		
		if($this->esigibilita_iva == 'S') {$valore_importopagamento=number_format($object->total_ttc*(100/122),2, '.', '');}else{$valore_importopagamento=number_format($object->total_ttc,2, '.', '');}
	    
	    /*Beneficiario tag introdotto il 20 ottobre 2015*/
			
			$sql1 = "SELECT benef";
		    $sql1.= " FROM llx_facture_extrafields";
		    $sql1.= " WHERE fk_object=".$object->id;
            $myquery=$this->db->query($sql1);
            $beneficiario = $this->db->fetch_array($myquery);
	    
	    if ($beneficiario[0]){
	    $xml="<DatiPagamento>          
		    	<CondizioniPagamento>TP02</CondizioniPagamento>
			    <DettaglioPagamento>
			    <Beneficiario>".$beneficiario[0]."</Beneficiario>
			    <ModalitaPagamento>".$mp."</ModalitaPagamento>
			    <DataScadenzaPagamento>".$data_scadenza_pag."</DataScadenzaPagamento>
			    <ImportoPagamento>".$valore_importopagamento."</ImportoPagamento>";	  
			}else{
			$xml="<DatiPagamento>
		    	<CondizioniPagamento>TP02</CondizioniPagamento>
			    <DettaglioPagamento>
			    <ModalitaPagamento>".$mp."</ModalitaPagamento>
			    <DataScadenzaPagamento>".$data_scadenza_pag."</DataScadenzaPagamento>
			    <ImportoPagamento>".$valore_importopagamento."</ImportoPagamento>";
			} 
			    
		if($account->iban){
				$xml.= "<IBAN>".$account->iban."</IBAN>";
		}    		
		$xml.="</DettaglioPagamento></DatiPagamento>";
	
		return simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA);
	}

	private function getAllegato($file){

					
		$dafafile = explode('.',$file['name']);
	
		$xml.="<Allegati>
		       <NomeAttachment>".$file['name']."</NomeAttachment>
		       <FormatoAttachment>".strtoupper($dafafile[1])."</FormatoAttachment>
		       <DescrizioneAttachment>".$dafafile[0]."</DescrizioneAttachment>
		       <Attachment>".base64_encode(file_get_contents($file['fullname']))."</Attachment>
		       </Allegati>";
	
      return simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA);
	
	}
	
	/**
	 * Rimuove i caratteri speciali non accettati dalla codifica previste nella fattura pa
	 * Prima di rimuoverli comunque cerca di salvare le accentate o altr caratteri particolari
	 * optando per una sostituzione
	 * @param unknown $value
	 * @return mixed
	 */
	function removeInvalidChar($value) {
	  $ret =  str_replace(array('’','à','è','ì','ò','ù'),array("'","a'","e'","i'","o'","u'"),$value);
	  $ret = preg_replace('/[^\00-\255]+/u', '', $ret);
	  return $ret;
	}	
		
}

?>
