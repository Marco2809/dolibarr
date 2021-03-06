<?php

/* Copyright (C) 2010-2014 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2010      Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2012-2013 Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2013      Cédric Salvador      <csalvador@gpcsolutions.fr>
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
 *  \file		htdocs/core/menus/standard/eldy.lib.php
 *  \brief		Library for file eldy menus
 */
require_once DOL_DOCUMENT_ROOT . '/core/class/menubase.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/menus/standard/mymoduli.php';

if($_SESSION['tipologia']=="T"){
  if(!strstr($_SERVER[REQUEST_URI],'gestione_tecnici')) header("location: http://glvservice.fast-data.it/product/gestione_tecnici.php?type=chiusi"); ;

};

//modificato da amin
/**
 * aggiungo un metodo che conterrà i nomi dei moduli
 */
function getModuliLaterali()
{
    //l'array avrà come chive il nome del modulo e il valore url dove punta.

    $array_moduli = array();
    $root = DOL_URL_ROOT;
    $array_moduli['home'] = $root . "/index.php?mainmenu=home&amp;leftmenu=";
    $array_moduli['soggetti_terzi'] = $root . "/societe/index.php?mainmenu=companies&leftmenu=";
    //$array_moduli['contatti_servizi'];
    $array_moduli['prodotti_servizi'] = $root . "/product/index.php?mainmenu=products&leftmenu=";
    $array_moduli['membri'] = $root . '/adherents/index.php?mainmenu=members&amp;leftmenu=';
    return $array_moduli;
}

function getModuliOrizzontali()
{
    $root = DOL_URL_ROOT;
    $array_moduli = array();
    global $user;

    $array_moduli['home'] = $root . "/index.php?mainmenu=home&amp;leftmenu=";
    $array_moduli['soggetti_terzi'] = $root . "/societe/index.php?mainmenu=companies&leftmenu=";
    $array_moduli['prodotti_servizi'] = $root . "/product/index.php?mainmenu=products&leftmenu=";
    $array_moduli['asset_mngmt'] = $root . "/product/elenco_asset.php?mainmenu=products&leftmenu=";

    $array_moduli['commerciale'] = $root . "/comm/index.php?mainmenu=commercial&leftmenu=";
    $array_moduli['fatturazione'] = $root . "/compta/index.php?mainmenu=accountancy&leftmenu=";
    $array_moduli['cassa'] = $root . "/compta/bank/index.php?mainmenu=bank&leftmenu=";
    $array_moduli['commesse'] = $root . "/projet/index.php?mainmenu=project&leftmenu=";
    $array_moduli['risorse_umane'] = $root . "/compta/hrm.php?mainmenu=hrm&leftmenu=";
    $array_moduli['strumenti'] = $root . "/core/tools.php?mainmenu=tools&leftmenu=";
    $array_moduli['membri'] = $root . "/adherents/index.php?mainmenu=members&leftmenu=";
    $array_moduli['documenti'] = $root . "/ecm/index.php?idmenu=25&mainmenu=ecm&leftmenu=";
    $array_moduli['punto_vendita'] = $root . "/cashdesk/index.php?user=admin&idmenu=5&mainmenu=cashdesk&leftmenu=";
    $array_moduli['ordine_del_giorno'] = $root . "/comm/action/index.php?idmenu=6&mainmenu=agenda&leftmenu=";
    $array_moduli['ticket'] = "http://ticketglv.fast-data.it/scp/login_unificato.php?username=" . $user->login . "&password=" . $user->pass; // sistemare dopo che è stato completato la procedura
    return $array_moduli;
}

function print_eldy_menu($db, $atarget, $type_user, &$tabMenu, &$menu, $noout = 0)
{

    global $user;
    $tipologia = $user->tipologia;

    //$out.= 'href="'.DOL_URL_ROOT.'/core/menus/standard/style.css" type="text/css" media="screen" />';
    print '<head>';
    $boiler = DOL_URL_ROOT . "/core/menus/boilerplate.css";
    echo '<link href="' . $boiler . '" rel="stylesheet" type="text/css">';
    echo '<link href="' . DOL_URL_ROOT . '/core/menus/standard/style.css rel="stylesheet" type="text/css">';
    $respond_min = DOL_URL_ROOT . "/core/menus/standard/respond.min.js";
    print '<script src="' . $respond_min . '"></script>';
    print '</head>';
    print '<body>';

    print '<div class="gridContainer clearfix">';
    print '<div id="div1" class="fluid">';
    // print '<button onclick="myFunction()" class="dropbtn" style="background-color:#333333; height:100px;">';
    $bottone_js = DOL_URL_ROOT . "/core/menus/standard/img/bar.png";
    //print '<img src="' . $bottone_js . '"></button>';
    print '</div>';


    $lucchetto = DOL_URL_ROOT . "/core/menus/standard/img/lucchetto.png";
    $stampa = DOL_URL_ROOT . "/core/menus/standard/img/stampa.png";
    /* print '<div id="logout_stampa" style="float: right; margin-top: -90px; text-align: center;">';
      print '<span style="text-align: right"></span> <a href="#">root</a><br>';
      $logouttext .='<a href="' . DOL_URL_ROOT . '/user/logout.php"';
      print '<a href="#"> <img src="' . $stampa . '" style="width:36px; height:26px;"></a> <a href="' . DOL_URL_ROOT . '/user/logout.php"' . '><img src="' . $lucchetto . '"style="width:36px; height:26px;"></a></div>';
      print '</div>"';
     */
    print " <style> @import url(" . DOL_URL_ROOT . "/core/menus/standard/css.css); </style>";

    $logo = DOL_URL_ROOT . "/core/menus/standard/img/logo-reg.png";
    // print '<img src="' . $logo . '" style="margin-left:90px; margin-top: -90px; float:left;';

    print '<div class="container">';
    $user_name = $user->login;

    print '<img src="' . $logo . '" width="250" style="margin-left:40px; margin-top:2px; float:left;">
             <div id="logout_stampa" style="float: right; margin-top: 20px; text-align: center; margin-right:20px;">
        <span style="text-align: center;"></span><span style="color:#FFFFFF; font-size:16px;">Benvenuto:&nbsp;<a href="#"><span style="color:#FFFFFF; font-size:16px; text-decoration:underline;">' . $user_name . '</span></a><br>
          <a href="#" title="Stampa"><img src="' . $stampa . '" style="width:26px; height:30px;"></a><a href="' . DOL_URL_ROOT . '/user/logout.php" title="Logout"><img src="' . $lucchetto . '" style="width:46px; height:46px;"></a></div>';
    print '</div>';

    print " <style> @import url(" . DOL_URL_ROOT . "/core/menus/boilerplate.css); </style>";

    // se metto questo non mi funziona javascript
    //print '<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>';


    print '<div class="gridContainer clearfix">';
    print '<div id="div1" class="fluid">';
    $bottone_js = DOL_URL_ROOT . "/core/menus/standard/img/bar.png";
    // print '<button onclick="myFunction()" class="dropbtn" style="background-color:#333333; height:100px;"><img src="' . $bottone_js . '"></button>';
    //print '</div>';

    print '<div id="div_icone" class="fluid" >';
    print '<center>';
    print '<table width="1100px"  cellpadding="3px" cellspacing="6px">';
    print '<tr>';
    $array_moduli_orizzontali = getModuliOrizzontali(); // lista moduli (con link) da disegnare
    if ($tipologia == "T")
    {
        //print '<th width="90px" scope="col"><a style="pointer-events: none;" href="' . $array_moduli_orizzontali['home'] . '"><div class="home_hide"></div></a></th>';
        //print '<th width="90px" scope="col"><a style="pointer-events: none;" href="' . $array_moduli_orizzontali['soggetti_terzi'] . '"><div class="soggetiterzi_hide"></div></a></th>';
        print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['asset_mngmt'] . '"><div class="prodotti"></div></a></th>';
        //print '<th width="90px" scope="col"><a style="pointer-events: none;" href="' . $array_moduli_orizzontali['commerciale'] . '"><div class="commerciale_hide"></div></a></th>';
        //print '<th width="90px" scope="col"><a style="pointer-events: none;" href="' . $array_moduli_orizzontali['fatturazione'] . '"><div class="fatturazione_hide"></div></a></th>';
        //print '<th width="90px" scope="col"><a style="pointer-events: none;" href="' . $array_moduli_orizzontali['cassa'] . '"><div class="bancacassa_hide"></div></a></th>';
        //print '<th width="90px" scope="col"><a style="pointer-events: none;" href="' . $array_moduli_orizzontali['commesse'] . '"><div class="commesse_hide"></div></a></th>';
        /*print '<th width="90px" scope="col"><a style="pointer-events: none;" href="' . $array_moduli_orizzontali['risorse_umane'] . '"><div class="hr_hide"></div></a></th>';
        print '<th width="90px" scope="col"><a style="pointer-events: none;" href="' . $array_moduli_orizzontali['strumenti'] . '"><div class="strumenti_hide"></div></a></th>';
        print '<th width="90px" scope="col"><a style="pointer-events: none;" href="' . $array_moduli_orizzontali['membri'] . '"><div class="membri_hide"></div></a></th>';
        print '<th width="90px" scope="col"><a style="pointer-events: none;" href="' . $array_moduli_orizzontali['documenti'] . '"><div class="documenti_hide"></div></a></th>';
        print '<th width="90px" scope="col"><a style="pointer-events: none;" href="' . $array_moduli_orizzontali['punto_vendita'] . '" target="_blank"><div class="puntovendita_hide"></div></a></th>';
        print '<th width="90px" scope="col"><a style="pointer-events: none;" href="' . $array_moduli_orizzontali['ordine_del_giorno'] . '"><div class="ordinegiorno_hide"></div></a></th>';
        print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['ticket'] . '"><div class="ticket"></div></a></th>';*/
    } else
    {
        //print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['home'] . '"><div class="home"></div></a></th>';
        //print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['soggetti_terzi'] . '"><div class="soggetiterzi"></div></a></th>';
        print '<th width="5px" scope="col"><a href="' . $array_moduli_orizzontali['prodotti_servizi'] . '"><div class="prodotti"></div></a></th>';


        if ($tipologia == "A")
        {
            print '<th width="90px" scope="col"><a href="#"><div class="commerciale_hide"></div></a></th>';
            print '<th width="90px" scope="col"><a href="#"><div class="fatturazione_hide"></div></a></th>';

            print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['cassa'] . '"><div class="bancacassa_hide"></div></a></th>';
            print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['commesse'] . '"><div class="commesse_hide"></div></a></th>';
            print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['risorse_umane'] . '"><div class="hr_hide"></div></a></th>';
            print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['strumenti'] . '"><div class="strumenti_hide"></div></a></th>';
            print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['membri'] . '"><div class="membri_hide"></div></a></th>';
            print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['documenti'] . '"><div class="documenti_hide"></div></a></th>';
            print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['punto_vendita'] . '" target="_blank"><div class="puntovendita_hide"></div></a></th>';
            print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['ordine_del_giorno'] . '"><div class="ordinegiorno_hide"></div></a></th>';
            print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['ticket'] . '"><div class="ticket"></div></a></th>';
        } else
        {
            //print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['commerciale'] . '"><div class="commerciale"></div></a></th>';
            print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['fatturazione'] . '"><div class="fatturazione"></div></a></th>';

            //print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['cassa'] . '"><div class="bancacassa"></div></a></th>';
            //print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['commesse'] . '"><div class="commesse"></div></a></th>';
            //print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['risorse_umane'] . '"><div class="hr"></div></a></th>';
           // print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['strumenti'] . '"><div class="strumenti"></div></a></th>';
            //print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['membri'] . '"><div class="membri"></div></a></th>';
            //print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['documenti'] . '"><div class="documenti"></div></a></th>';
           // print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['punto_vendita'] . '" target="_blank"><div class="puntovendita"></div></a></th>';
            //print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['ordine_del_giorno'] . '"><div class="ordinegiorno"></div></a></th>';
            //print '<th width="90px" scope="col"><a href="' . $array_moduli_orizzontali['ticket'] . '"><div class="ticket"></div></a></th>';
        }
    }
    print ' </tr>';

    print '</table>';
    print '</center>';
    print '</div>';

    disegna_sottoMenu($tipologia);
}

function printAllSottoMenu($moduli)
{
    $array_home = $moduli->getModuloHome(); // RITRONA le etichette con i link
    //IMPOSTAZIONI
    $ingranaggi = DOL_URL_ROOT . "/core/menus/standard/img/ingranaggi.png";
    print '<li><a href="' . $array_home['impostazioni']['impostazioni'] . '"><img src="' . $ingranaggi . '" style="width:28px; height:28px;"> <strong>IMPOSTAZIONI</strong></a></li>';
    print '<li><a href="' . $array_home['impostazioni']['soc_fondazione'] . '">Società/Fondazione</a></li>';
    print '<li><a href="' . $array_home['impostazioni']['moduli'] . '">Moduli</a></li>';
    print '<li><a href="' . $array_home['impostazioni']['menu'] . '">Menu</a></li>';
    print '<li><a href="' . $array_home['impostazioni']['layout_view'] . '">Layout di visualizzazione</a></li>';
    print '<li><a href="' . $array_home['impostazioni']['traduzione'] . '">Traduzione</a></li>';
    print '<li><a href="' . $array_home['impostazioni']['caselle_rias'] . '">Caselle riassuntive</a></li>';
    print '<li><a href="' . $array_home['impostazioni']['avvisi_segnalazioni'] . '">Avvisi e segnalazioni</a></li>';
    print '<li><a href="' . $array_home['impostazioni']['sicurezza'] . '">Sicurezza</a></li>';
    print '<li><a href="' . $array_home['impostazioni']['limiti_precisione'] . '">Limiti e precisione</a></li>';
    print '<li><a href="' . $array_home['impostazioni']['pdf'] . '">PDF</a></li>';
    print '<li><a href="' . $array_home['impostazioni']['email'] . '">Email</a></li>';
    print '<li><a href="' . $array_home['impostazioni']['sms'] . '">SMS</a></li>';
    print '<li><a href="' . $array_home['impostazioni']['dictionaries'] . '">Dictionnary</a></li>';
    print '<li><a href="' . $array_home['impostazioni']['altre_impostazioni'] . '">Altre impostazioni</a></li>';

    //STRUMENTI DI GESTIONE
    $tools = DOL_URL_ROOT . "/core/menus/standard/img/tools.png";
    print ' <li><a href="' . $array_home['strumenti_gestione']['strumenti_gestione'] . '"><img src="' . $tools . '" style="width:25px; height:28px;"> <strong>STRUMENTI DI GESTIONE</strong></a></li>';
    print '<li><a href="' . $array_home['strumenti_gestione']['info_dolibarr'] . '">Informazioni su Dolibarr</a></li>';
    print '<li><a href="' . $array_home['strumenti_gestione']['info_os'] . '">Informazioni OS</a></li>';
    print '<li><a href="' . $array_home['strumenti_gestione']['info_web_server'] . '">Informazioni web server</a></li>';
    print '<li><a href="' . $array_home['strumenti_gestione']['info_php'] . '">Informazioni PHP</a></li>';
    print '<li><a href="' . $array_home['strumenti_gestione']['info_db'] . '">Informazioni database</a></li>';
    print '<li><a href="' . $array_home['strumenti_gestione']['bak'] . '">Backup</a></li>';
    print '<li><a href="' . $array_home['strumenti_gestione']['ripristino'] . '">Ripristino</a></li>';
    print '<li><a href="' . $array_home['strumenti_gestione']['migliora_estendi'] . '">Migliora/Estendi</a></li>';
    print '<li><a href="' . $array_home['strumenti_gestione']['audit'] . '">Audit</a></li>';
    print '<li><a href="' . $array_home['strumenti_gestione']['sessione_utente'] . '">Sessione utente</a></li>';
    print '<li><a href="' . $array_home['strumenti_gestione']['pulizia'] . '">Pulizia</a></li>';
    print '<li><a href="' . $array_home['strumenti_gestione']['about'] . '">About</a></li>';
    print '<li><a href="' . $array_home['strumenti_gestione']['centro_assistenza'] . '">Centro assistenza</a></li>';

    //STRUMENTI MODULI
    $strumenti_gest = DOL_URL_ROOT . "/core/menus/standard/img/strumenti-gestione.png";
    print '<li><a href="' . $array_home['strumenti_moduli']['strumenti_moduli'] . '"><img src="' . $strumenti_gest . '" style="width:28px; height:32px;"> <strong>STRUMENTI MODULI</strong></a></li>';
    print '<li><a href="' . $array_home['strumenti_moduli']['mass_vat'] . '">Centro assistenza</a></li>';


    //UTENTI E GRUPPI
    $group = DOL_URL_ROOT . "/core/menus/standard/img/group.png";
    print '<li><a href="' . $array_home['utenti_gruppi']['utenti_gruppi'] . '"><img src="' . $group . '" style="width:28px; height:32px;"> <strong>UTENTI E GRUPPI</strong></a></li>';
    print '<li><a href="' . $array_home['utenti_gruppi']['utenti'] . '">Utenti</a></li>';
    print '<li><a href="' . $array_home['utenti_gruppi']['nuovo_utente'] . '">Nuovo utente</a></li>';
    print '<li><a href="' . $array_home['utenti_gruppi']['gruppi'] . '">Gruppi</a></li>';
    print '<li><a href="' . $array_home['utenti_gruppi']['nuovi_gruppi'] . '">Nuovo gruppo</a></li>';
}

function print_moduloProdotti($moduli, $tipologia)
{
    global $user;
    global $db;
    $array_prodotti = $moduli->getModuloProdotti();
    $prodotti_servizi = DOL_URL_ROOT . "/core/menus/standard/img/magazzino_3d.png";
    if ($tipologia == "T")// se è un tecnico
    {

        require_once DOL_DOCUMENT_ROOT . '/product/myclass/myMagazzino.php';
        //$obj_mag_generico = new magazzino($db);
        //$magazzino_proprio = $obj_mag_generico->getMagazzinoUser($user->id, "mag.fk_user");
        //$id_m = $magazzino_proprio[0]['rowid'];
        // print '<li><a href="#"><img src="' . $prodotti_servizi . '" style="width:28px; height:32px;"> <strong>PRODOTTI/FAMIGLIE</strong><br></a></li>';
        //print '<li><a href="' . $array_prodotti['magazzini']['dotazione'] . $id_m . '"><img src="' . $prodotti_servizi . '" style="width:28px; height:32px;"> <strong>Dotazione</strong><br></a></li>';
    }


    if ($tipologia != "T")
    {
        print '<li><a href="' . $array_prodotti['modulo_prodotti']['carica_matricole'] . '">CARICA MATRICOLE</a></li>';
        //print '<li><a href="' . $array_prodotti['modulo_prodotti']['carica_matricole_old'] . '">CARICA MATRICOLE OLD</a></li>';
        print '<li><a href="' . $array_prodotti['modulo_prodotti']['cambia_magazzino'] . '">CAMBIA MAGAZZINO</a></li>';
        //print '<li><a href="' . $array_prodotti['modulo_prodotti']['cambia_magazzino_old'] . '">CAMBIA MAGAZZINO OLD</a></li>';
        print '<li><a href="' . $array_prodotti['modulo_prodotti']['ricerca_asset'] . '">RICERCA ASSET</a></li>';
        print '<li><a href="' . $array_prodotti['modulo_prodotti']['gestione_tecnici'] . '">GESTIONE TECNICI</a></li>';
        print '<li><a href="' . $array_prodotti['modulo_prodotti']['caricamento_ticket'] . '">CARICAMENTO TICKET NEXI</a></li>';
        print '<li><a href="' . $array_prodotti['modulo_prodotti']['caricamento_magazzino'] . '">CARICAMENTO MAGAZZINO NEXI</a></li>';
        //print '<li><a href="#"><img src="' . $prodotti_servizi . '" style="width:28px; height:32px;"> <strong>PRODOTTI/FAMIGLIE</strong><br></a></li>';
        //print '<li><a href="' . $array_prodotti['modulo_prodotti']['nuovo_prodotto'] . '">Nuovo prodotto</a></li>';
        //print '<li><a href="' . $array_prodotti['modulo_famiglia']['nuova_famiglia'] . '">Nuova famiglia</a></li>';
        //print '<li><a href="' . $array_prodotti['modulo_prodotti']['elenco'] . '">Elenco</a></li>';
        //print '<li><a href="' . $array_prodotti['modulo_prodotti']['statistiche'] . '">Statistiche</a></li>';
        //print '<li><a href="' . $array_prodotti['modulo_prodotti']['scorte'] . '">Scorte</a></li>';


    } else{
        print '<li><a href="' . $array_prodotti['modulo_prodotti']['gestione_tecnici']."&lastname=".$user->lastname."&firstname=".$user->firstname."&tipologia=".$user->tipologia . '">RICERCA TICKET</a></li>';
    }
//ASSET
    /*$icona_asset = DOL_URL_ROOT . "/core/menus/standard/img/asset.png";
    print '<li><a href="#"><img src="' . $icona_asset . '" style="width:28px; height:32px;"> <strong>ASSET</strong><br></a></li>';
    print '<li><a href="' . $array_prodotti['modulo_asset']['crea_asset'] . '">Crea</a></li>';
    print '<li><a href="' . $array_prodotti['modulo_asset']['elenco_asset'] . '">Elenco HW</a></li>';
    //print '<li><a href="' . $array_prodotti['modulo_asset']['massivo'] . '">Censimento massivo</a></li>';

     //movimentazioni
    $icona_mov = DOL_URL_ROOT . "/theme/eldy/img/movimentazioni.png";
    print '<li><a href="#"><img src="' . $icona_mov . '" style="width:28px; height:32px;"> <strong>INTERVENTO </strong><br></a></li>';
    print '<li><a href="' . $array_prodotti['intervento']['nuovo_intervento'] . '">Nuovo intervento</a></li>';
        print '<li><a href="' . $array_prodotti['intervento']['storico_interventi'] . '">Storico interventi</a></li>';
    print '<li><a href="' . $array_prodotti['intervento']['aggiungi_anagrafica'] . '">Aggiungi anagrafica</a></li>';
    print '<li><a href="' . $array_prodotti['intervento']['elenco_clienti'] . '">Elenco clienti</a></li>';*/


    /*
    //movimentazioni
    $icona_mov = DOL_URL_ROOT . "/theme/eldy/img/movimentazioni.png";
    print '<li><a href="#"><img src="' . $icona_mov . '" style="width:28px; height:32px;"> <strong>MOVIMENTI </strong><br></a></li>';
    print '<li><a href="' . $array_prodotti['movimentazione']['nuova_mov'] . '">Nuova movimentazione</a></li>';
    print '<li><a href="' . $array_prodotti['movimentazione']['da_convalidare'] . '">Da convalidare</a></li>';
    print '<li><a href="' . $array_prodotti['movimentazione']['in_transito'] . '">In transito</a></li>';
    print '<li><a href="' . $array_prodotti['movimentazione']['storico'] . '">Storico</a></li>';
    */
    //magazzini
    if ($tipologia != "T")
    {
        $icona_mov = DOL_URL_ROOT . "/theme/eldy/img/magazzino.png";
        /*print '<li><a href="#"><img src="' . $icona_mov . '" style="width:28px; height:32px;"> <strong>MAGAZZINI </strong><br></a></li>';
        print '<li><a href="' . $array_prodotti['magazzini']['elenco'] . '">Elenco</a></li>';
        print '<li><a href="' . $array_prodotti['magazzini']['nuova_movimentazione'] . '">Nuova Movimentazione</a></li>';
        print '<li><a href="' . $array_prodotti['magazzini']['movimenti_prodotti'] . '">Movimenti prodotti</a></li>';
        print '<li><a href="' . $array_prodotti['magazzini']['mov_sottoscorte'] . '">Sotto scorta</a></li>';*/
    }
}

function disegna_sottoMenu($tipologia = "")
{
    print '<div id="myDropdown" class="dropdown-content" style="margin-top:65px;">';

    $moduli = new mymoduli(DOL_URL_ROOT);


    if ($_GET['mainmenu'] == "home")
    {
        printAllSottoMenu($moduli);
    } else if ($_GET['mainmenu'] == "companies")
    {
        $array_soggetti = $moduli->getCompanySoggetti();
        $soggetti = DOL_URL_ROOT . "/core/menus/standard/img/soggettiterzi_3d.png";
        if ($tipologia != "T") {
            print '<li><a href="' . $array_soggetti['company']['company'] . '"><img src="' . $soggetti . '" style="width:28px; height:32px;"> <strong>SOGGETTI TERZI</strong><br></a></li>';
            print '<li><a href="' . $array_soggetti['company']['nuovo'] . '">Nuovo soggetto terzo</a></li>';
            print '<li><a href="' . $array_soggetti['company']['elenco_clienti_potenziali'] . '">Elenco clienti potenziali</a></li>';
            print '<li><a href="' . $array_soggetti['company']['nuovo_cliente_potenziale'] . '">Nuovo cliente potenziale</a></li>';
            print '<li><a href="' . $array_soggetti['company']['elenco_clienti'] . '">Elenco clienti</a></li>';
            print '<li><a href="' . $array_soggetti['company']['nuovo_cliente'] . '">Nuovo cliente</a></li>';
            print '<li><a href="' . $array_soggetti['company']['elenco_fornitori'] . '">Elenco fornitori</a></li>';
            print '<li><a href="' . $array_soggetti['company']['nuovo_fornitore'] . '">Nuovo fornitore</a></li>';

            print '<li><a href="' . $array_soggetti['contatti']['contatti'] . '"><img src="' . $soggetti . '" style="width:28px; height:32px;"> <strong>CONTATTI/INDIRIZZI</strong><br></a></li>';
            print '<li><a href="' . $array_soggetti['contatti']['nuovo_contatto'] . '">Nuovo contatto/indirizzo</a></li>';
            print '<li><a href="' . $array_soggetti['contatti']['elenco'] . '">Elenco</a></li>';
            print '<li><a href="' . $array_soggetti['contatti']['elenco_clienti_pot'] . '">Clienti potenziali</a></li>';
            print '<li><a href="' . $array_soggetti['contatti']['elenco_clienti'] . '">Clienti</a></li>';
            print '<li><a href="' . $array_soggetti['contatti']['elenco_fornitori'] . '">Fornitori</a></li>';
            print '<li><a href="' . $array_soggetti['contatti']['elenco_altri'] . '">Altri</a></li>';

            print '<li><a href="' . $array_soggetti['categorie_clienti']['categorie_clienti'] . '"><img src="' . $soggetti . '" style="width:28px; height:32px;"> <strong>CATEGORIE CLIENTI POTENZIALI</strong><br></a></li>';
            print '<li><a href="' . $array_soggetti['categorie_clienti']['nuova_categoria'] . '">Nuova categoria</a></li>';

            print '<li><a href="' . $array_soggetti['categorie_contatti']['categorie_contatti'] . '"><img src="' . $soggetti . '" style="width:28px; height:32px;"> <strong>CATEGORIE DI CONTATTI</strong><br></a></li>';
            print '<li><a href="' . $array_soggetti['categorie_contatti']['nuova_categoria'] . '">Nuova categoria</a></li>';

            print '<li><a href="' . $array_soggetti['categorie_fornitori']['categorie_fornitori'] . '"><img src="' . $soggetti . '" style="width:28px; height:32px;"> <strong>CATEGORIE FORNITORI</strong><br></a></li>';
            print '<li><a href="' . $array_soggetti['categorie_fornitori']['nuova_categoria'] . '">Nuova categoria</a></li>';
        }
    } else if ($_GET['mainmenu'] == "products")
    {

        print_moduloProdotti($moduli, $tipologia);
    } else if ($_GET['mainmenu'] == "commercial")
    {
        $array_commerciale = $moduli->getModuloCommerciale();
        $icona_commerciale = DOL_URL_ROOT . "/core/menus/standard/img/commerciale_3d2.png";
        if ($tipologia != "T") {
            print '<li><a href="' . $array_commerciale['preventivi_com']['preventivi_com'] . '"><img src="' . $icona_commerciale . '" style="width:28px; height:32px;"> <strong>PREVENTIVI/PROP. COMMERCIALI</strong><br></a></li>';
            print '<li><a href="' . $array_commerciale['preventivi_com']['nuova_proposta'] . '">Nuova proposta</a></li>';
            print '<li><a href="' . $array_commerciale['preventivi_com']['elenco'] . '">Elenco</a></li>';
            print '<li><a href="' . $array_commerciale['preventivi_com']['statistiche'] . '">Statistiche</a></li>';

            print '<li><a href="' . $array_commerciale['ordini_clienti']['ordini_clienti'] . '"><img src="' . $icona_commerciale . '" style="width:28px; height:32px;"> <strong>ORDINI CLIENTI</strong><br></a></li>';
            print '<li><a href="' . $array_commerciale['ordini_clienti']['nuovo_ordine'] . '">Nuovo ordine</a></li>';
            print '<li><a href="' . $array_commerciale['ordini_clienti']['elenco'] . '">Elenco</a></li>';
            print '<li><a href="' . $array_commerciale['ordini_clienti']['statistiche'] . '">Statistiche</a></li>';

            print '<li><a href="' . $array_commerciale['ordini_fornitori']['ordini_fornitori'] . '"><img src="' . $icona_commerciale . '" style="width:28px; height:32px;"> <strong>ORDINI FORNITORI</strong><br></a></li>';
            print '<li><a href="' . $array_commerciale['ordini_fornitori']['nuovo_ordine'] . '">Nuovo ordine</a></li>';
            print '<li><a href="' . $array_commerciale['ordini_fornitori']['elenco'] . '">Elenco</a></li>';
            print '<li><a href="' . $array_commerciale['ordini_fornitori']['statistiche'] . '">Statistiche</a></li>';

            //questi moduli dovrà vedere solo admin
            print '<li><a href="' . $array_commerciale['contratti']['contratti'] . '"><img src="' . $icona_commerciale . '" style="width:28px; height:32px;"> <strong>CONTRATTI</strong><br></a></li>';
            print '<li><a href="' . $array_commerciale['contratti']['nuovo_contratto'] . '">Nuovo contratto</a></li>';
            print '<li><a href="' . $array_commerciale['contratti']['elenco'] . '">Elenco</a></li>';
            print '<li><a href="' . $array_commerciale['contratti']['servizi'] . '">Servizi</a></li>';

            print '<li><a href="' . $array_commerciale['interventi']['interventi'] . '"><img src="' . $icona_commerciale . '" style="width:28px; height:32px;"> <strong>INTERVENTI</strong><br></a></li>';
            print '<li><a href="' . $array_commerciale['interventi']['nuovo_intervento'] . '">Nuovo intervento</a></li>';
            print '<li><a href="' . $array_commerciale['interventi']['elenco'] . '">Elenco</a></li>';
        }
    } else if ($_GET['mainmenu'] == "accountancy")
    {
        $array_fatturazione = $moduli->getModuloFatturazione();
        $icona_fatturazione = DOL_URL_ROOT . "/core/menus/standard/img/commerciale_3d2.png";
        $icona_fatturazione = "";
        if ($tipologia != "T") {
            print '<li><a href="' . $array_fatturazione['fatture_attive']['controllo_ticket'] . '"><img  <strong>CONTROLLO TICKET</strong><br></a></li>';
            print '<li><a href="' . $array_fatturazione['fatture_attive']['controllo_ticket_tml'] . '"><img  <strong>CONTROLLO TICKET TML</strong><br></a></li>';
            print '<li><a href="' . $array_fatturazione['fatture_attive']['controllo_ticket_doppi'] . '"><img  <strong>INTERVENTI DOPPI</strong><br></a></li>';
            print '<li><a href="' . $array_fatturazione['fatture_attive']['genera_fatture'] . '"><img  <strong>GENERA FATTURE</strong><br></a></li>';
            print '<li><a href="' . $array_fatturazione['fatture_attive']['fast_facture'] . '"><img  <strong>FATTURE RAPIDE</strong><br></a></li>';
            print '<li><a href="' . $array_fatturazione['fatture_attive']['prima_nota'] . '"><img  <strong>PRIMA NOTA</strong><br></a></li>';

            print '<li><a href="' . $array_fatturazione['fatture_attive']['fatture_attive'] . '"><img  <strong>FATTURE ATTIVE</strong><br></a></li>';
            print '<li><a href="' . $array_fatturazione['fatture_attive']['nuova_fattura'] . '">Nuova fattura</a></li>';
            //print '<li><a href="' . $array_fatturazione['fatture_attive']['ripetibili'] . '">Ripetibili</a></li>';
            //print '<li><a href="' . $array_fatturazione['fatture_attive']['non_pagato'] . '">Non pagato</a></li>';
            //print '<li><a href="' . $array_fatturazione['fatture_attive']['pagamenti'] . '">Pagamenti</a></li>';
            //print '<li><a href="' . $array_fatturazione['fatture_attive']['reportistiche'] . '">Reportistiche</a></li>';
            //print '<li><a href="' . $array_fatturazione['fatture_attive']['statisitiche'] . '">Statistiche</a></li>';

            print '<li><a href="' . $array_fatturazione['fatture_passive']['fatture_passive'] . '"><img  <strong>FATTURE PASSIVE</strong><br></a></li>';
            print '<li><a href="' . $array_fatturazione['fatture_passive']['nuova_fattura'] . '">Nuova fattura</a></li>';
            //print '<li><a href="' . $array_fatturazione['fatture_passive']['non_pagato'] . '">Non pagato</a></li>';
            //print '<li><a href="' . $array_fatturazione['fatture_passive']['pagamenti'] . '">Pagamenti</a></li>';
            //print '<li><a href="' . $array_fatturazione['fatture_passive']['statistiche'] . '">Statistiche</a></li>';
        }
    } else if ($_GET['mainmenu'] == "bank")
    {
        $array_cassa = $moduli->getModuloCassa();
        $icona_cassa = DOL_URL_ROOT . "/core/menus/standard/img/commerciale_3d2.png";
        $icona_cassa = "";
        if ($tipologia != "T") {
            print '<li><a href="' . $array_cassa['banca_cassa']['banca_cassa'] . '"><img  <strong>BANCA/CASSA</strong><br></a></li>';
            print '<li><a href="' . $array_cassa['banca_cassa']['nuovo_conto_finanziario'] . '">Nuovo conto finanziario</a></li>';
            print '<li><a href="' . $array_cassa['banca_cassa']['categorie'] . '">Categorie</a></li>';
            print '<li><a href="' . $array_cassa['banca_cassa']['elenco_transazioni'] . '">Elenco transazioni</a></li>';
            print '<li><a href="' . $array_cassa['banca_cassa']['elenco_transazioni_categoria'] . '">Elenco transazioni per categoria</a></li>';
            print '<li><a href="' . $array_cassa['banca_cassa']['bonifici_giroconti'] . '">Bonifici e giroconti</a></li>';

            print '<li><a href="' . $array_cassa['depositi_assegni']['depositi_assegni'] . '"><img  <strong>DEPOSITI/ASSEGNI</strong><br></a></li>';
            print '<li><a href="' . $array_cassa['depositi_assegni']['nuovo_depositi'] . '">Nuovo deposito</a></li>';
            print '<li><a href="' . $array_cassa['depositi_assegni']['elenco'] . '">Elenco</a></li>';
        }
    } else if ($_GET['mainmenu'] == "project")
    {
        $array_commesse = $moduli->getModuloCommesse();
        $icona_commesse = DOL_URL_ROOT . "/core/menus/standard/img/commerciale_3d2.png";
        $icona_commesse = "";
        if ($tipologia != "T") {
            print '<li><a href="' . $array_commesse['miei_progetti']['miei_progetti'] . '"><img  <strong>I MIEI PROGETTI</strong><br></a></li>';
            print '<li><a href="' . $array_commesse['miei_progetti']['nuovo_progetto'] . '">Nuovo progetto</a></li>';
            print '<li><a href="' . $array_commesse['miei_progetti']['elenco'] . '">Elenco</a></li>';

            print '<li><a href="' . $array_commesse['progetti']['progetti'] . '"><img  <strong>PROGETTI</strong><br></a></li>';
            print '<li><a href="' . $array_commesse['progetti']['nuovi_progetti'] . '">Nuovo progetto</a></li>';
            print '<li><a href="' . $array_commesse['progetti']['elenco'] . '">Elenco</a></li>';

            print '<li><a href="' . $array_commesse['miei_compiti']['miei_compiti'] . '"><img  <strong>I MIEI COMPITI</strong><br></a></li>';
            print '<li><a href="' . $array_commesse['miei_compiti']['nuovo_compito'] . '">Nuovo compito</a></li>';
            print '<li><a href="' . $array_commesse['miei_compiti']['elenco'] . '">Elenco</a></li>';
            print '<li><a href="' . $array_commesse['miei_compiti']['aggiungi_tempo_lavorato'] . '">Aggiungi tempo lavorato</a></li>';

            print '<li><a href="' . $array_commesse['compiti']['compiti'] . '"><img  <strong>COMPITI</strong><br></a></li>';
            print '<li><a href="' . $array_commesse['compiti']['nuovo_compito'] . '">Nuovo compito</a></li>';
            print '<li><a href="' . $array_commesse['compiti']['elenco'] . '">Elenco</a></li>';
            print '<li><a href="' . $array_commesse['compiti']['aggiungi_tempo_lavorato'] . '">Aggiungi tempo lavorato</a></li>';
        }
    } else if ($_GET['mainmenu'] == "hrm")
    {
        $array_hr = $moduli->getModuloHR();
        $icona_hr = DOL_URL_ROOT . "/theme/eldy/img/object_user.png";
        if ($tipologia != "T") {
            print '<li><a href="' . $array_hr['ferie']['ferie'] . '"><img src="' . $icona_hr . '" style="width:28px; height:32px;"> <strong>FERIE</strong><br></a></li>';
            print '<li><a href="' . $array_hr['ferie']['richiedi_ferie'] . '">Richiedi ferie</a></li>';
            print '<li><a href="' . $array_hr['ferie']['modifica_ferie'] . '">Modifica ferie rimanenti</a></li>';
            print '<li><a href="' . $array_hr['ferie']['storico_ferie'] . '">Vedi lo storico ferie</a></li>';
            print '<li><a href="' . $array_hr['ferie']['estratto_conto'] . '">Estratto conto mensile</a></li>';

            print '<li><a href="' . $array_hr['viaggi_spese']['viaggi_spese'] . '"><img src="' . $icona_hr . '" style="width:28px; height:32px;"> <strong>VIAGGI E SPESE</strong><br></a></li>';
            print '<li><a href="' . $array_hr['viaggi_spese']['nuovo'] . '">Nuovo</a></li>';
            print '<li><a href="' . $array_hr['viaggi_spese']['elenco'] . '">Elenco</a></li>';
            print '<li><a href="' . $array_hr['viaggi_spese']['statistiche'] . '">Statistiche</a></li>';
        }
    } else if ($_GET['mainmenu'] == "tools")
    {
        $array_strumenti = $moduli->getModuloStrumenti();
        $icona_strumenti = DOL_URL_ROOT . "/core/menus/standard/img/strumenti-gestione.png";
        if ($tipologia != "T") {
            print '<li><a href="' . $array_strumenti['invio_email']['invio_email'] . '"><img src="' . $icona_strumenti . '" style="width:28px; height:32px;"> <strong>INVII EMAIL</strong><br></a></li>';
            print '<li><a href="' . $array_strumenti['invio_email']['invio_massa'] . '">NUOVO INVIO DI MASSA</a></li>';
            print '<li><a href="' . $array_strumenti['invio_email']['elenco'] . '">ELENCO</a></li>';

            print '<li><a href="' . $array_strumenti['esportazione_assistita']['esportazione_assistita'] . '"><img src="' . $icona_strumenti . '" style="width:28px; height:32px;"> <strong>ESPORTAZIONE ASSISTITA</strong><br></a></li>';
            print '<li><a href="' . $array_strumenti['esportazione_assistita']['nuova_esportazione'] . '">NUOVA ESPORTAZIONE</a></li>';

            print '<li><a href="' . $array_strumenti['importazione_assistita']['importazione_assitita'] . '"><img src="' . $icona_strumenti . '" style="width:28px; height:32px;"> <strong>IMPORTAZIONE ASSISTITA</strong><br></a></li>';
            print '<li><a href="' . $array_strumenti['importazione_assistita']['nuova_importazione'] . '">NUOVA IMPORTAZIONE</a></li>';

            print '<li><a href="' . $array_strumenti['sondaggio']['sondaggio'] . '"><img src="' . $icona_strumenti . '" style="width:28px; height:32px;"> <strong>SONDAGGI</strong><br></a></li>';
            print '<li><a href="' . $array_strumenti['sondaggio']['nuovo_sondaggio'] . '">NUOVO SONDAGGIO</a></li>';
            print '<li><a href="' . $array_strumenti['sondaggio']['elenco'] . '">ELENCO</a></li>';
        }
    } else if ($_GET['mainmenu'] == "members")
    {
        $array_membri = $moduli->getModuloMembri();
        $icona_membri = DOL_URL_ROOT . "/core/menus/standard/img/commerciale_3d2.png";
        $icona_membri = "";
        if ($tipologia != "T") {
            print '<li><a href="' . $array_membri['membri']['membri'] . '"><img  <strong>MEMBRI</strong><br></a></li>';
            print '<li><a href="' . $array_membri['membri']['nuovo_membro'] . '">Nuovo membro</a></li>';
            print '<li><a href="' . $array_membri['membri']['elenco'] . '">Elenco</a></li>';
            print '<li><a href="' . $array_membri['membri']['elenco_membri_da_convalidare'] . '">Membri da convalidare</a></li>';
            print '<li><a href="' . $array_membri['membri']['elenco_membri_convalidati'] . '">Membri convalidati</a></li>';
            print '<li><a href="' . $array_membri['membri']['elenco_membri_aggiornati'] . '">Membri aggiornati</a></li>';
            print '<li><a href="' . $array_membri['membri']['elenco_membri_non_aggiornati'] . '">Membri non aggiornati</a></li>';
            print '<li><a href="' . $array_membri['membri']['elenco_membri_revocati'] . '">Membri revocati</a></li>';
            print '<li><a href="' . $array_membri['membri']['statistiche'] . '">Statistiche</a></li>';

            print '<li><a href="' . $array_membri['adesioni']['adesioni'] . '"><img  <strong>ADESIONI</strong><br></a></li>';
            print '<li><a href="' . $array_membri['adesioni']['nuova_adesione'] . '">Nuova adesione</a></li>';
            print '<li><a href="' . $array_membri['adesioni']['elenco'] . '">Elenco</a></li>';
            print '<li><a href="' . $array_membri['adesioni']['statistiche'] . '">Statistiche</a></li>';

            print '<li><a href="' . $array_membri['categorie']['categorie'] . '"><img  <strong>CATEGORIE</strong><br></a></li>';
            print '<li><a href="' . $array_membri['categorie']['nuova_categoria'] . '">Nuova categoria</a></li>';

            print '<li><a href="' . $array_membri['esportazioni']['esportazioni'] . '"><img  <strong>ESPORTAZIONI</strong><br></a></li>';
            print '<li><a href="' . $array_membri['esportazioni']['dati'] . '">Dati</a></li>';
            print '<li><a href="' . $array_membri['esportazioni']['file_htpasswd'] . '">File htpasswd</a></li>';
            print '<li><a href="' . $array_membri['esportazioni']['schede_membri'] . '">Schede membri</a></li>';

            print '<li><a href="' . $array_membri['tipo_membro']['tipo_membro'] . '"><img  <strong>TIPI DI MEMBRO</strong><br></a></li>';
            print '<li><a href="' . $array_membri['tipo_membro']['nuovo'] . '">Nuovo</a></li>';
            print '<li><a href="' . $array_membri['tipo_membro']['elenco'] . '">Elenco</a></li>';
        }
    } else if ($_GET['mainmenu'] == "ecm")
    {
        $array_documenti = $moduli->getModuloDocumenti();
        $icona_documenti = DOL_URL_ROOT . "/core/menus/standard/img/commerciale_3d2.png";
        $icona_documenti = "";
        if ($tipologia != "T") {
            print '<li><a href="' . $array_documenti['edm_area'] . '"><img  <strong>EDM AREA</strong><br></a></li>';
            print '<li><a href="' . $array_documenti['gerarchia_manuale'] . '">Gerarchia manuale</a></li>';
            print '<li><a href="' . $array_documenti['gerarchia_automatica'] . '">Gerarchia automatica</a></li>';
        }
    } else if ($_GET['mainmenu'] == "agenda")
    {
        $array_agenda = $moduli->getModuloordineGiorno();
        $icona_agenda = DOL_URL_ROOT . "/core/menus/standard/img/commerciale_3d2.png";
        $icona_agenda = "";
        if ($tipologia != "T") {
            print '<li><a href="' . $array_agenda['azioni'] . '"><img  <strong>AZIONI</strong><br></a></li>';
            print '<li><a href="' . $array_agenda['nuova_azione'] . '">Nuova azione/compito</a></li>';
            print '<li><a href="' . $array_agenda['calendario'] . '">Calendario</a></li>';
            print '<li><a href="' . $array_agenda['eventi_non_completati'] . '">I mie eventi non completati</a></li>';
            print '<li><a href="' . $array_agenda['eventi_passati'] . '">I miei eventi passati</a></li>';
            print '<li><a href="' . $array_agenda['tutte_azioni_incomplete'] . '">Tutte le azioni incomplete</a></li>';
            print '<li><a href="' . $array_agenda['tutte_azioni_passate'] . '">Tutte le azioni passate</a></li>';
            print '<li><a href="' . $array_agenda['elenco'] . '">Elenco</a></li>';
            print '<li><a href="' . $array_agenda['miei_eventi_non_completati'] . '">I mie eventi non completati</a></li>';
            print '<li><a href="' . $array_agenda['miei_eventi_passati'] . '">I miei eventi passati</a></li>';
            print '<li><a href="' . $array_agenda['tutte_le_azioni_incomplete'] . '">Tutte le azioni incomplete</a></li>';
            print '<li><a href="' . $array_agenda['tutte_le_azioni_passate'] . '">Tutte le azioni passate</a></li>';
            print '<li><a href="' . $array_agenda['reportistiche'] . '">Reportistiche</a></li>';
        }
    } else
    {
        if ($tipologia != "T")
            printAllSottoMenu($moduli);

        if ($tipologia == "T")
            print_moduloProdotti($moduli, $tipologia);
    }
    print '</div>';

    print '</body>';
    ?>
    <script>
        /* When the user clicks on the button,
         toggle between hiding and showing the dropdown content */
        function myFunction() {
            document.getElementById("myDropdown").classList.toggle("show");
        }

        // Close the dropdown menu if the user clicks outside of it
        window.onclick = function (event) {
            if (!event.target.matches('.dropbtn')) {

                var dropdowns = document.getElementsByClassName("dropdown-content");
                var i;
                for (i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>
    <?php

}

/**
 * Core function to output top menu eldy
 *
 * @param 	DoliDB	$db				Database handler
 * @param 	string	$atarget		Target (Example: '' or '_top')
 * @param 	int		$type_user     	0=Menu for backoffice, 1=Menu for front office
 * @param  	array	$tabMenu       If array with menu entries already loaded, we put this array here (in most cases, it's empty)
 * @param	array	$menu			Object Menu to return back list of menu entries
 * @param	int		$noout			Disable output (Initialise &$menu only).
 * @return	int						0
 */
function print_eldy_menu1($db, $atarget, $type_user, &$tabMenu, &$menu, $noout = 0)
{


    global $user, $conf, $langs, $dolibarr_main_db_name;
    $flag_user = false;
    if ($user->login == "solari")
    {
        $flag_user = true;
    } else if ($user->login == "st_solari")
    {
        $flag_user = true;
    } else if ($user->login == "pcm_napoli")
    {
        $flag_user = true;
    } else if ($user->login == "pcm_milano")
    {
        $flag_user = true;
    } else if ($user->login == "tpr")
    {
        $flag_user = true;
    }
    if ($flag_user)
    {
        //return;
    }

    $mainmenu = (empty($_SESSION["mainmenu"]) ? '' : $_SESSION["mainmenu"]);
    $leftmenu = (empty($_SESSION["leftmenu"]) ? '' : $_SESSION["leftmenu"]);

    $id = 'mainmenu';
    $listofmodulesforexternal = explode(',', $conf->global->MAIN_MODULES_FOR_EXTERNAL);

    if (empty($noout))
        print_start_menu_array();

    // Home
    $showmode = 1;
    $classname = "";
    if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "home")
    {
        $classname = 'class="tmenusel"';
        $_SESSION['idmenu'] = '';
    } else
        $classname = 'class="tmenu"';
    $idsel = 'home';

    if (empty($noout))
        print_start_menu_entry($idsel, $classname, $showmode);
    if (!$flag_user)
    {
        if (empty($noout))
            print_text_menu_entry($langs->trans("Home"), 1, DOL_URL_ROOT . '/index.php?mainmenu=home&amp;leftmenu=', $id, $idsel, $classname, $atarget);
    }
    if (empty($noout))
        print_end_menu_entry($showmode);

    $menu->add('/index.php?mainmenu=home&amp;leftmenu=', $langs->trans("Home"), 0, $showmode, $atarget, "home", '');

    // Third parties
    $tmpentry = array('enabled' => ((!empty($conf->societe->enabled) && (empty($conf->global->SOCIETE_DISABLE_PROSPECTS) || empty($conf->global->SOCIETE_DISABLE_CUSTOMERS))) || !empty($conf->fournisseur->enabled)), 'perms' => (!empty($user->rights->societe->lire) || !empty($user->rights->fournisseur->lire)), 'module' => 'societe|fournisseur');
    $showmode = dol_eldy_showmenu($type_user, $tmpentry, $listofmodulesforexternal);
    if ($showmode)
    {
        $langs->load("companies");
        $langs->load("suppliers");

        $classname = "";
        if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "companies")
        {
            $classname = 'class="tmenusel"';
            $_SESSION['idmenu'] = '';
        } else
            $classname = 'class="tmenu"';
        $idsel = 'companies';

        if (empty($noout))
            print_start_menu_entry($idsel, $classname, $showmode);
        if (!$flag_user)
        {
            if (empty($noout))
                print_text_menu_entry($langs->trans("ThirdParties"), $showmode, DOL_URL_ROOT . '/societe/index.php?mainmenu=companies&amp;leftmenu=', $id, $idsel, $classname, $atarget);
        }
        if (empty($noout))
            print_end_menu_entry($showmode);
        $menu->add('/societe/index.php?mainmenu=companies&amp;leftmenu=', $langs->trans("ThirdParties"), 0, $showmode, $atarget, "companies", '');
    }

    // Products-Services
    $tmpentry = array('enabled' => (!empty($conf->product->enabled) || !empty($conf->service->enabled)), 'perms' => (!empty($user->rights->produit->lire) || !empty($user->rights->service->lire)), 'module' => 'product|service');
    $showmode = dol_eldy_showmenu($type_user, $tmpentry, $listofmodulesforexternal);
    if ($showmode)
    {
        $langs->load("products");

        $classname = "";
        if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "products")
        {
            $classname = 'class="tmenusel"';
            $_SESSION['idmenu'] = '';
        } else
            $classname = 'class="tmenu"';
        $idsel = 'products';

        $chaine = "";
        if (!empty($conf->product->enabled))
        {
            $chaine.=$langs->trans("Products");
        }
        if (!empty($conf->product->enabled) && !empty($conf->service->enabled))
        {
            $chaine.="/";
        }
        if (!empty($conf->service->enabled))
        {
            $chaine.=$langs->trans("Services");
        }

        if (empty($noout))
            print_start_menu_entry($idsel, $classname, $showmode);
        if (empty($noout))
            print_text_menu_entry($chaine, $showmode, DOL_URL_ROOT . '/product/index.php?mainmenu=products&amp;leftmenu=', $id, $idsel, $classname, $atarget);
        if (empty($noout))
            print_end_menu_entry($showmode);
        $menu->add('/product/index.php?mainmenu=products&amp;leftmenu=', $chaine, 0, $showmode, $atarget, "products", '');
    }

    // Commercial
    $menuqualified = 0;
    if (!empty($conf->propal->enabled))
        $menuqualified++;
    if (!empty($conf->commande->enabled))
        $menuqualified++;
    if (!empty($conf->fournisseur->enabled))
        $menuqualified++;
    if (!empty($conf->contrat->enabled))
        $menuqualified++;
    if (!empty($conf->ficheinter->enabled))
        $menuqualified++;
    $tmpentry = array('enabled' => $menuqualified, 'perms' => (!empty($user->rights->societe->lire) || !empty($user->rights->societe->contact->lire)), 'module' => 'propal|commande|fournisseur|contrat|ficheinter');
    $showmode = dol_eldy_showmenu($type_user, $tmpentry, $listofmodulesforexternal);
    if ($showmode)
    {
        $langs->load("commercial");

        $classname = "";
        if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "commercial")
        {
            $classname = 'class="tmenusel"';
            $_SESSION['idmenu'] = '';
        } else
            $classname = 'class="tmenu"';
        $idsel = 'commercial';

        if (empty($noout))
            print_start_menu_entry($idsel, $classname, $showmode);
        if (!$flag_user)
        {
            if (empty($noout))
                print_text_menu_entry($langs->trans("Commercial"), $showmode, DOL_URL_ROOT . '/comm/index.php?mainmenu=commercial&amp;leftmenu=', $id, $idsel, $classname, $atarget);
        }
        if (empty($noout))
            print_end_menu_entry($showmode);
        $menu->add('/comm/index.php?mainmenu=commercial&amp;leftmenu=', $langs->trans("Commercial"), 0, $showmode, $atarget, "commercial", "");
    }

    // Financial
    $tmpentry = array('enabled' => (!empty($conf->comptabilite->enabled) || !empty($conf->accounting->enabled) || !empty($conf->facture->enabled) || !empty($conf->don->enabled) || !empty($conf->tax->enabled) || !empty($conf->salaries->enabled)),
        'perms' => (!empty($user->rights->compta->resultat->lire) || !empty($user->rights->accounting->plancompte->lire) || !empty($user->rights->facture->lire) || !empty($user->rights->don->lire) || !empty($user->rights->tax->charges->lire) || !empty($user->rights->salaries->read)),
        'module' => 'comptabilite|accounting|facture|don|tax|salaries');
    $showmode = dol_eldy_showmenu($type_user, $tmpentry, $listofmodulesforexternal);
    if ($showmode)
    {
        $langs->load("compta");

        $classname = "";
        if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "accountancy")
        {
            $classname = 'class="tmenusel"';
            $_SESSION['idmenu'] = '';
        } else
            $classname = 'class="tmenu"';
        $idsel = 'accountancy';

        if (empty($noout))
            print_start_menu_entry($idsel, $classname, $showmode);
        if (!$flag_user)
        {
            if (empty($noout))
                print_text_menu_entry($langs->trans("MenuFinancial"), $showmode, DOL_URL_ROOT . '/compta/index.php?mainmenu=accountancy&amp;leftmenu=', $id, $idsel, $classname, $atarget);
        }
        if (empty($noout))
            print_end_menu_entry($showmode);
        $menu->add('/compta/index.php?mainmenu=accountancy&amp;leftmenu=', $langs->trans("MenuFinancial"), 0, $showmode, $atarget, "accountancy", '');
    }

    // Bank
    $tmpentry = array('enabled' => (!empty($conf->banque->enabled) || !empty($conf->prelevement->enabled)),
        'perms' => (!empty($user->rights->banque->lire) || !empty($user->rights->prelevement->lire)),
        'module' => 'banque|prelevement');
    $showmode = dol_eldy_showmenu($type_user, $tmpentry, $listofmodulesforexternal);
    if ($showmode)
    {
        $langs->load("compta");
        $langs->load("banks");

        $classname = "";
        if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "bank")
        {
            $classname = 'class="tmenusel"';
            $_SESSION['idmenu'] = '';
        } else
            $classname = 'class="tmenu"';
        $idsel = 'bank';

        if (empty($noout))
            print_start_menu_entry($idsel, $classname, $showmode);
        if (!$flag_user)
        {
            if (empty($noout))
                print_text_menu_entry($langs->trans("MenuBankCash"), $showmode, DOL_URL_ROOT . '/compta/bank/index.php?mainmenu=bank&amp;leftmenu=', $id, $idsel, $classname, $atarget);
        }
        if (empty($noout))
            print_end_menu_entry($showmode);
        $menu->add('/compta/bank/index.php?mainmenu=bank&amp;leftmenu=', $langs->trans("MenuBankCash"), 0, $showmode, $atarget, "bank", '');
    }

    // Projects
    $tmpentry = array('enabled' => (!empty($conf->projet->enabled)),
        'perms' => (!empty($user->rights->projet->lire)),
        'module' => 'projet');
    $showmode = dol_eldy_showmenu($type_user, $tmpentry, $listofmodulesforexternal);
    if ($showmode)
    {
        $langs->load("projects");

        $classname = "";
        if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "project")
        {
            $classname = 'class="tmenusel"';
            $_SESSION['idmenu'] = '';
        } else
            $classname = 'class="tmenu"';
        $idsel = 'project';

        if (empty($noout))
            print_start_menu_entry($idsel, $classname, $showmode);
        if (!$flag_user)
        {
            if (empty($noout))
                print_text_menu_entry($langs->trans("Projects"), $showmode, DOL_URL_ROOT . '/projet/index.php?mainmenu=project&amp;leftmenu=', $id, $idsel, $classname, $atarget);
        }
        if (empty($noout))
            print_end_menu_entry($showmode);
        $menu->add('/projet/index.php?mainmenu=project&amp;leftmenu=', $langs->trans("Projects"), 0, $showmode, $atarget, "project", '');
    }

    // HRM
    $tmpentry = array('enabled' => (!empty($conf->holiday->enabled) || !empty($conf->deplacement->enabled)),
        'perms' => (!empty($user->rights->holiday->write) || !empty($user->rights->deplacement->lire)),
        'module' => 'holiday|deplacement');
    $showmode = dol_eldy_showmenu($type_user, $tmpentry, $listofmodulesforexternal);
    if ($showmode)
    {
        $langs->load("holiday");

        $classname = "";
        if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "hrm")
        {
            $classname = 'class="tmenusel"';
            $_SESSION['idmenu'] = '';
        } else
            $classname = 'class="tmenu"';
        $idsel = 'hrm';

        if (empty($noout))
            print_start_menu_entry($idsel, $classname, $showmode);
        if (!$flag_user)
        {
            if (empty($noout))
                print_text_menu_entry($langs->trans("HRM"), $showmode, DOL_URL_ROOT . '/compta/hrm.php?mainmenu=hrm&amp;leftmenu=', $id, $idsel, $classname, $atarget);
        }
        if (empty($noout))
            print_end_menu_entry($showmode);
        $menu->add('/compta/hrm.php?mainmenu=hrm&amp;leftmenu=', $langs->trans("HRM"), 0, $showmode, $atarget, "hrm", '');
    }



    // Tools
    $tmpentry = array('enabled' => (!empty($conf->mailing->enabled) || !empty($conf->export->enabled) || !empty($conf->import->enabled) || !empty($conf->opensurvey->enabled)),
        'perms' => (!empty($user->rights->mailing->lire) || !empty($user->rights->export->lire) || !empty($user->rights->import->run) || !empty($user->rights->opensurvey->read)),
        'module' => 'mailing|export|import|opensurvey');
    $showmode = dol_eldy_showmenu($type_user, $tmpentry, $listofmodulesforexternal);
    if ($showmode)
    {
        $langs->load("other");

        $classname = "";
        if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "tools")
        {
            $classname = 'class="tmenusel"';
            $_SESSION['idmenu'] = '';
        } else
            $classname = 'class="tmenu"';
        $idsel = 'tools';

        if (empty($noout))
            print_start_menu_entry($idsel, $classname, $showmode);
        if (!$flag_user)
        {
            if (empty($noout))
                print_text_menu_entry($langs->trans("Tools"), $showmode, DOL_URL_ROOT . '/core/tools.php?mainmenu=tools&amp;leftmenu=', $id, $idsel, $classname, $atarget);
        }
        if (empty($noout))
            print_end_menu_entry($showmode);
        $menu->add('/core/tools.php?mainmenu=tools&amp;leftmenu=', $langs->trans("Tools"), 0, $showmode, $atarget, "tools", '');
    }

    // OSCommerce 1
    $tmpentry = array('enabled' => (!empty($conf->boutique->enabled)),
        'perms' => 1,
        'module' => 'boutique');
    $showmode = dol_eldy_showmenu($type_user, $tmpentry, $listofmodulesforexternal);
    if ($showmode)
    {
        $langs->load("shop");

        $classname = "";
        if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "shop")
        {
            $classname = 'class="tmenusel"';
            $_SESSION['idmenu'] = '';
        } else
            $classname = 'class="tmenu"';
        $idsel = 'shop';

        if (empty($noout))
            print_start_menu_entry($idsel, $classname, $showmode);
        if (empty($noout))
            print_text_menu_entry($langs->trans("OSCommerce"), $showmode, DOL_URL_ROOT . '/boutique/index.php?mainmenu=shop&amp;leftmenu=', $id, $idsel, $classname, $atarget);
        if (empty($noout))
            print_end_menu_entry($showmode);
        $menu->add('/boutique/index.php?mainmenu=shop&amp;leftmenu=', $langs->trans("OSCommerce"), 0, $showmode, $atarget, "shop", '');
    }

    // Members
    $tmpentry = array('enabled' => (!empty($conf->adherent->enabled)),
        'perms' => (!empty($user->rights->adherent->lire)),
        'module' => 'adherent');
    $showmode = dol_eldy_showmenu($type_user, $tmpentry, $listofmodulesforexternal);
    if ($showmode)
    {
        $classname = "";
        if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "members")
        {
            $classname = 'class="tmenusel"';
            $_SESSION['idmenu'] = '';
        } else
            $classname = 'class="tmenu"';
        $idsel = 'members';

        if (empty($noout))
            print_start_menu_entry($idsel, $classname, $showmode);
        if (!$flag_user)
        {
            if (empty($noout))
                print_text_menu_entry($langs->trans("MenuMembers"), $showmode, DOL_URL_ROOT . '/adherents/index.php?mainmenu=members&amp;leftmenu=', $id, $idsel, $classname, $atarget);
        }
        if (empty($noout))
            print_end_menu_entry($showmode);
        $menu->add('/adherents/index.php?mainmenu=members&amp;leftmenu=', $langs->trans("MenuMembers"), 0, $showmode, $atarget, "members", '');
    }

    // Show personalized menus
    $menuArbo = new Menubase($db, 'eldy');
    $newTabMenu = $menuArbo->menuTopCharger('', '', $type_user, 'eldy', $tabMenu); // Return tabMenu with only top entries

    $num = count($newTabMenu);
    for ($i = 0; $i < $num; $i++)
    {
        $idsel = (empty($newTabMenu[$i]['mainmenu']) ? 'none' : $newTabMenu[$i]['mainmenu']);

        $showmode = dol_eldy_showmenu($type_user, $newTabMenu[$i], $listofmodulesforexternal);
        if ($showmode == 1)
        {
            $url = $shorturl = $newTabMenu[$i]['url'];
            if (!preg_match("/^(http:\/\/|https:\/\/)/i", $newTabMenu[$i]['url']))
            {
                $tmp = explode('?', $newTabMenu[$i]['url'], 2);
                $url = $shorturl = $tmp[0];
                $param = (isset($tmp[1]) ? $tmp[1] : '');

                if (!preg_match('/mainmenu/i', $url) || !preg_match('/leftmenu/i', $url))
                    $param.=($param ? '&' : '') . 'mainmenu=' . $newTabMenu[$i]['mainmenu'] . '&amp;leftmenu=';
                //$url.="idmenu=".$newTabMenu[$i]['rowid'];    // Already done by menuLoad
                $url = dol_buildpath($url, 1) . ($param ? '?' . $param : '');
                $shorturl = $shorturl . ($param ? '?' . $param : '');
            }
            $url = preg_replace('/__LOGIN__/', $user->login, $url);
            $shorturl = preg_replace('/__LOGIN__/', $user->login, $shorturl);
            $url = preg_replace('/__USERID__/', $user->id, $url);
            $shorturl = preg_replace('/__USERID__/', $user->id, $shorturl);

            // Define the class (top menu selected or not)
            if (!empty($_SESSION['idmenu']) && $newTabMenu[$i]['rowid'] == $_SESSION['idmenu'])
                $classname = 'class="tmenusel"';
            else if (!empty($_SESSION["mainmenu"]) && $newTabMenu[$i]['mainmenu'] == $_SESSION["mainmenu"])
                $classname = 'class="tmenusel"';
            else
                $classname = 'class="tmenu"';
        }
        else if ($showmode == 2)
            $classname = 'class="tmenu"';

        if (empty($noout))
            print_start_menu_entry($idsel, $classname, $showmode);
        if (empty($noout))
            print_text_menu_entry($newTabMenu[$i]['titre'], $showmode, $url, $id, $idsel, $classname, ($newTabMenu[$i]['target'] ? $newTabMenu[$i]['target'] : $atarget));
        if (empty($noout))
            print_end_menu_entry($showmode);
        $menu->add($shorturl, $newTabMenu[$i]['titre'], 0, $showmode, ($newTabMenu[$i]['target'] ? $newTabMenu[$i]['target'] : $atarget), ($newTabMenu[$i]['mainmenu'] ? $newTabMenu[$i]['mainmenu'] : $newTabMenu[$i]['rowid']), '');
    }

    $showmode = 1;
    if (empty($noout))
        print_start_menu_entry('', 'class="tmenuend"', $showmode);
    if (empty($noout))
        print_end_menu_entry($showmode);

    if (empty($noout))
        print_end_menu_array();

    return 0;
}

/**
 * Output start menu array
 *
 * @return	void
 */
function print_start_menu_array()
{
    print '<div class="tmenudiv">';
    print '<ul class="tmenu">';
}

/**
 * Output start menu entry
 *
 * @param	string	$idsel		Text
 * @param	string	$classname	String to add a css class
 * @param	int		$showmode	0 = hide, 1 = allowed or 2 = not allowed
 * @return	void
 */
function print_start_menu_entry($idsel, $classname, $showmode)
{
    if ($showmode)
    {
        print '<li ' . $classname . ' id="mainmenutd_' . $idsel . '">';
        print '<div class="tmenuleft"></div><div class="tmenucenter">';
    }
}

/**
 * Output menu entry
 *
 * @param	string	$text		Text
 * @param	int		$showmode	0 = hide, 1 = allowed or 2 = not allowed
 * @param	string	$url		Url
 * @param	string	$id			Id
 * @param	string	$idsel		Id sel
 * @param	string	$classname	Class name
 * @param	string	$atarget	Target
 * @return	void
 */
function print_text_menu_entry($text, $showmode, $url, $id, $idsel, $classname, $atarget)
{
    global $langs;

    if ($showmode == 1)
    {
        print '<a class="tmenuimage" href="' . $url . '"' . ($atarget ? ' target="' . $atarget . '"' : '') . '>';
        print '<div class="' . $id . ' ' . $idsel . '"><span class="' . $id . ' tmenuimage" id="mainmenuspan_' . $idsel . '"></span></div>';
        print '</a>';
        print '<a ' . $classname . ' id="mainmenua_' . $idsel . '" href="' . $url . '"' . ($atarget ? ' target="' . $atarget . '"' : '') . '>';
        print '<span class="mainmenuaspan">';
        print $text;
        print '</span>';
        print '</a>';
    }
    if ($showmode == 2)
    {
        print '<div class="' . $id . ' ' . $idsel . ' tmenudisabled"><span class="' . $id . '" id="mainmenuspan_' . $idsel . '"></span></div>';
        print '<a class="tmenudisabled" id="mainmenua_' . $idsel . '" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">';
        print '<span class="mainmenuaspan">';
        print $text;
        print '</span>';
        print '</a>';
    }
}

/**
 * Output end menu entry
 *
 * @param	int		$showmode	0 = hide, 1 = allowed or 2 = not allowed
 * @return	void
 */
function print_end_menu_entry($showmode)
{
    if ($showmode)
    {
        print '</div></li>';
    }
    print "\n";
}

/**
 * Output menu array
 *
 * @return	void
 */
function print_end_menu_array()
{
    print '</ul>';
    print '</div>';
    print "\n";
}

/**
 * Core function to output left menu eldy
 *
 * @param	DoliDB		$db                 Database handler
 * @param 	array		$menu_array_before  Table of menu entries to show before entries of menu handler (menu->liste filled with menu->add)
 * @param   array		$menu_array_after   Table of menu entries to show after entries of menu handler (menu->liste filled with menu->add)
 * @param	array		$tabMenu       	If array with menu entries already loaded, we put this array here (in most cases, it's empty)
 * @param	Menu		$menu				Object Menu to return back list of menu entries
 * @param	int			$noout				Disable output (Initialise &$menu only).
 * @param	string		$forcemainmenu		'x'=Force mainmenu to mainmenu='x'
 * @param	string		$forceleftmenu		'all'=Force leftmenu to '' (= all)
 * @return	int								nb of menu entries
 */
function print_left_eldy_menu($db, $menu_array_before, $menu_array_after, &$tabMenu, &$menu, $noout = 0, $forcemainmenu = '', $forceleftmenu = '')
{
    global $user, $conf, $langs, $dolibarr_main_db_name, $mysoc;

    $newmenu = $menu;

    $mainmenu = ($forcemainmenu ? $forcemainmenu : $_SESSION["mainmenu"]);
    $leftmenu = ($forceleftmenu ? '' : (empty($_SESSION["leftmenu"]) ? 'none' : $_SESSION["leftmenu"]));

    // Show logo company
    if (empty($conf->global->MAIN_MENU_INVERT) && empty($noout) && !empty($conf->global->MAIN_SHOW_LOGO))
    {
        $mysoc->logo_mini = $conf->global->MAIN_INFO_SOCIETE_LOGO_MINI;
        if (!empty($mysoc->logo_mini) && is_readable($conf->mycompany->dir_output . '/logos/thumbs/' . $mysoc->logo_mini))
        {
            $urllogo = DOL_URL_ROOT . '/viewimage.php?cache=1&amp;modulepart=companylogo&amp;file=' . urlencode('thumbs/' . $mysoc->logo_mini);
            print "\n" . '<!-- Show logo on menu -->' . "\n";
            print '<div class="blockvmenuimpair">' . "\n";
            print '<div class="menu_titre" id="menu_titre_logo"></div>';
            print '<div class="menu_top" id="menu_top_logo"></div>';
            print '<div class="menu_contenu" id="menu_contenu_logo">';
            print '<center><img title="" src="' . $urllogo . '"></center>' . "\n";
            print '</div>';
            print '<div class="menu_end" id="menu_end_logo"></div>';
            print '</div>' . "\n";
        }
    }

    /**
     * We update newmenu with entries found into database
     * --------------------------------------------------
     */
    if ($mainmenu)
    {
        /*
         * Menu HOME
         */
        if ($mainmenu == 'home')
        {
            $langs->load("users");

            if ($user->admin)
            {
                $langs->load("admin");
                $langs->load("help");

                // Setup
                $newmenu->add("/admin/index.php?mainmenu=home&amp;leftmenu=setup", $langs->trans("Setup"), 0, 1, '', $mainmenu, 'setup');
                if (empty($leftmenu) || $leftmenu == "setup")
                {
                    $warnpicto = '';
                    if (empty($conf->global->MAIN_INFO_SOCIETE_NOM) || empty($conf->global->MAIN_INFO_SOCIETE_COUNTRY))
                    {
                        $langs->load("errors");
                        $warnpicto = ' ' . img_warning($langs->trans("WarningMandatorySetupNotComplete"));
                    }
                    $newmenu->add("/admin/company.php?mainmenu=home", $langs->trans("MenuCompanySetup") . $warnpicto, 1);
                    $warnpicto = '';
                    if (count($conf->modules) <= (empty($conf->global->MAIN_MIN_NB_ENABLED_MODULE_FOR_WARNING) ? 1 : $conf->global->MAIN_MIN_NB_ENABLED_MODULE_FOR_WARNING))
                    { // If only user module enabled
                        $langs->load("errors");
                        $warnpicto = ' ' . img_warning($langs->trans("WarningMandatorySetupNotComplete"));
                    }
                    $newmenu->add("/admin/modules.php?mainmenu=home", $langs->trans("Modules") . $warnpicto, 1);
                    $newmenu->add("/admin/menus.php?mainmenu=home", $langs->trans("Menus"), 1);
                    $newmenu->add("/admin/ihm.php?mainmenu=home", $langs->trans("GUISetup"), 1);
                    if (!in_array($langs->defaultlang, array('en_US', 'en_GB', 'en_NZ', 'en_AU', 'fr_FR', 'fr_BE', 'es_ES', 'ca_ES')))
                    {
                        if (empty($leftmenu) || $leftmenu == "setup")
                            $newmenu->add("/admin/translation.php", $langs->trans("Translation"), 1);
                    }
                    $newmenu->add("/admin/boxes.php?mainmenu=home", $langs->trans("Boxes"), 1);
                    $newmenu->add("/admin/delais.php?mainmenu=home", $langs->trans("Alerts"), 1);
                    $newmenu->add("/admin/proxy.php?mainmenu=home", $langs->trans("Security"), 1);
                    $newmenu->add("/admin/limits.php?mainmenu=home", $langs->trans("MenuLimits"), 1);
                    $newmenu->add("/admin/pdf.php?mainmenu=home", $langs->trans("PDF"), 1);
                    $newmenu->add("/admin/mails.php?mainmenu=home", $langs->trans("Emails"), 1);
                    $newmenu->add("/admin/sms.php?mainmenu=home", $langs->trans("SMS"), 1);
                    $newmenu->add("/admin/dict.php?mainmenu=home", $langs->trans("Dictionary"), 1);
                    $newmenu->add("/admin/const.php?mainmenu=home", $langs->trans("OtherSetup"), 1);
                }

                // System tools
                $newmenu->add("/admin/tools/index.php?mainmenu=home&amp;leftmenu=admintools", $langs->trans("SystemTools"), 0, 1, '', $mainmenu, 'admintools');
                if (empty($leftmenu) || preg_match('/^admintools/', $leftmenu))
                {
                    $newmenu->add('/admin/system/dolibarr.php?mainmenu=home&amp;leftmenu=admintools_info', $langs->trans('InfoDolibarr'), 1);
                    if (empty($leftmenu) || $leftmenu == 'admintools_info')
                        $newmenu->add('/admin/system/modules.php?mainmenu=home&amp;leftmenu=admintools_info', $langs->trans('Modules'), 2);
                    if (empty($leftmenu) || $leftmenu == 'admintools_info')
                        $newmenu->add('/admin/triggers.php?mainmenu=home&amp;leftmenu=admintools_info', $langs->trans('Triggers'), 2);
                    $newmenu->add('/admin/system/os.php?mainmenu=home&amp;leftmenu=admintools', $langs->trans('InfoOS'), 1);
                    $newmenu->add('/admin/system/web.php?mainmenu=home&amp;leftmenu=admintools', $langs->trans('InfoWebServer'), 1);
                    $newmenu->add('/admin/system/phpinfo.php?mainmenu=home&amp;leftmenu=admintools', $langs->trans('InfoPHP'), 1);
                    //if (function_exists('xdebug_is_enabled')) $newmenu->add('/admin/system/xdebug.php', $langs->trans('XDebug'),1);
                    $newmenu->add('/admin/system/database.php?mainmenu=home&amp;leftmenu=admintools', $langs->trans('InfoDatabase'), 1);
                    if (function_exists('eaccelerator_info'))
                        $newmenu->add("/admin/tools/eaccelerator.php?mainmenu=home&amp;leftmenu=admintools", $langs->trans("EAccelerator"), 1);
                    //$newmenu->add("/admin/system/perf.php?mainmenu=home&amp;leftmenu=admintools", $langs->trans("InfoPerf"),1);
                    $newmenu->add("/admin/tools/purge.php?mainmenu=home&amp;leftmenu=admintools", $langs->trans("Purge"), 1);
                    $newmenu->add("/admin/tools/dolibarr_export.php?mainmenu=home&amp;leftmenu=admintools", $langs->trans("Backup"), 1);
                    $newmenu->add("/admin/tools/dolibarr_import.php?mainmenu=home&amp;leftmenu=admintools", $langs->trans("Restore"), 1);
                    $newmenu->add("/admin/tools/update.php?mainmenu=home&amp;leftmenu=admintools", $langs->trans("MenuUpgrade"), 1);
                    $newmenu->add("/admin/tools/listevents.php?mainmenu=home&amp;leftmenu=admintools", $langs->trans("Audit"), 1);
                    $newmenu->add("/admin/tools/listsessions.php?mainmenu=home&amp;leftmenu=admintools", $langs->trans("Sessions"), 1);
                    $newmenu->add('/admin/system/about.php?mainmenu=home&amp;leftmenu=admintools', $langs->trans('About'), 1);
                    $newmenu->add("/support/index.php?mainmenu=home&amp;leftmenu=admintools", $langs->trans("HelpCenter"), 1, 1, 'targethelp');
                }

                // Modules system tools
                if (!empty($conf->product->enabled) || !empty($conf->service->enabled) || !empty($conf->barcode->enabled) // TODO We should enabled module system tools entry without hardcoded test, but when at least one modules bringing such entries are on
                        || !empty($conf->global->MAIN_MENU_ENABLE_MODULETOOLS))
                { // Some external modules may need to force to have this entry on.
                    if (empty($user->societe_id))
                    {
                        $newmenu->add("/admin/tools/index.php?mainmenu=home&amp;leftmenu=modulesadmintools", $langs->trans("ModulesSystemTools"), 0, 1, '', $mainmenu, 'modulesadmintools');
                        // Special case: This entry can't be embedded into modules because we need it for both module service and products and we don't want duplicate lines.
                        if ((empty($leftmenu) || $leftmenu == "modulesadmintools") && $user->admin)
                        {
                            $langs->load("products");
                            $newmenu->add("/product/admin/product_tools.php?mainmenu=home&amp;leftmenu=modulesadmintools", $langs->trans("ProductVatMassChange"), 1, $user->admin);
                        }
                    }
                }
            }

            $tipologia = $user->tipologia;
            if ($user->login == "laboratorio")
            {
                if (!empty($conf->family->enabled))
                {
                    $root_my = DOL_URL_ROOT . "/product/elenco_asset.php?leftmenu=product&amp;type=4";
                    print' <div class="blockvmenuimpair">
<div class="menu_titre">
<a class="vmenu" href="' . $root_my . '">Elenco asset</a>
</div>
<div class="menu_top"></div>
<div class="menu_end"></div>
</div>';
                }
            } else if ($tipologia == "T")
            {

                $path_elenco_prod_fam = DOL_URL_ROOT . "/product/liste.php?leftmenu=product&type=0";
                $html = ' <div class="blockvmenuimpair">';

                $html .= '<div class="menu_titre">';
                $html .= ' <a class="vmenu" href="' . $path_elenco_prod_fam . '">Elenco prodotti e famiglie</a>';
                $html .= '</div>';
                $html .= '<div class="menu_top"></div>';
                $html .= '<div class="menu_end"></div>';
                $html .= ' </div>';
                print $html;


                $path_crea_asset = DOL_URL_ROOT . "/product/crea_asset.php?leftmenu=product&amp;type=4";
                $path_massivo_asset = DOL_URL_ROOT . "/product/asset_massivo.php?leftmenu=product&amp;type=4";
                $path_elenco_asset = DOL_URL_ROOT . "/product/elenco_asset.php?leftmenu=product&amp;type=4";

                $html = ' <div class="blockvmenuimpair">';

                $html .= '<div class="menu_titre">';
                $html .= ' <a class="vmenu" href="' . $path_crea_asset . '">Crea asset</a>';
                $html .= '</div>';

               /* $html .= '<div class="menu_titre">';
                $html .= ' <a class="vmenu" href="' . $path_massivo_asset . '">Censimento massivo</a>';
                $html .= '</div>'; */

                $html .= '<div class="menu_titre">';
                $html .= ' <a class="vmenu" href="' . $path_elenco_asset . '">Elenco asset</a>';
                $html .= '</div>';

                $html .= '<div class="menu_top"></div>';
                $html .= '<div class="menu_end"></div>';
                $html .= ' </div>';
                print $html;

                //movimentazione
                $path_nuova_movimentazione = DOL_URL_ROOT . "/product/movimentazione.php?leftmenu=product&amp;type=4";
                $path_convalida = DOL_URL_ROOT . "/product/daconvalidare.php?leftmenu=product&amp;type=4";
                $path_storico = DOL_URL_ROOT . "/product/storico.php?leftmenu=product&amp;type=4";

                $html = ' <div class="blockvmenuimpair">';

                $html .= '<div class="menu_titre">';
                $html .= ' <a class="vmenu" href="' . $path_nuova_movimentazione . '">Movimentazione</a>';
                $html .= '</div>';

                $html .= '<div class="menu_titre">';
                $html .= ' <a class="vmenu" href="' . $path_convalida . '">Da convalidare</a>';
                $html .= '</div>';

                $html .= '<div class="menu_titre">';
                $html .= ' <a class="vmenu" href="' . $path_storico . '">Storico</a>';
                $html .= '</div>';

                $html .= '<div class="menu_top"></div>';
                $html .= '<div class="menu_end"></div>';
                $html .= ' </div>';
                print $html;
            } else if ($tipologia == "M") //gestione tipologia magazzino
            {

            } else
            {
                $newmenu->add("/user/home.php?leftmenu=users", $langs->trans("MenuUsersAndGroups"), 0, 1, '', $mainmenu, 'users');
                if (empty($leftmenu) || $leftmenu == "users")
                {
                    $newmenu->add("/user/index.php", $langs->trans("Users"), 1, $user->rights->user->user->lire || $user->admin);
                    $newmenu->add("/user/fiche.php?action=create", $langs->trans("NewUser"), 2, $user->rights->user->user->creer || $user->admin);
                    $newmenu->add("/user/group/index.php", $langs->trans("Groups"), 1, ($conf->global->MAIN_USE_ADVANCED_PERMS ? $user->rights->user->group_advance->read : $user->rights->user->user->lire) || $user->admin);
                    $newmenu->add("/user/group/fiche.php?action=create", $langs->trans("NewGroup"), 2, ($conf->global->MAIN_USE_ADVANCED_PERMS ? $user->rights->user->group_advance->write : $user->rights->user->user->creer) || $user->admin);
                }
            }
        }


        /*
         * Menu THIRDPARTIES
         */
        if ($mainmenu == 'companies')
        {
            // Societes
            if (!empty($conf->societe->enabled))
            {
                $langs->load("companies");
                $newmenu->add("/societe/index.php?leftmenu=thirdparties", $langs->trans("ThirdParty"), 0, $user->rights->societe->lire, '', $mainmenu, 'thirdparties');

                if ($user->rights->societe->creer)
                {
                    $newmenu->add("/societe/soc.php?action=create", $langs->trans("MenuNewThirdParty"), 1);
                    if (!$conf->use_javascript_ajax)
                        $newmenu->add("/societe/soc.php?action=create&amp;private=1", $langs->trans("MenuNewPrivateIndividual"), 1);
                }
            }

            // Prospects
            if (!empty($conf->societe->enabled) && empty($conf->global->SOCIETE_DISABLE_PROSPECTS))
            {
                $langs->load("commercial");
                $newmenu->add("/comm/prospect/list.php?leftmenu=prospects", $langs->trans("ListProspectsShort"), 1, $user->rights->societe->lire, '', $mainmenu, 'prospects');

                if (empty($leftmenu) || $leftmenu == "prospects")
                    $newmenu->add("/comm/prospect/list.php?sortfield=s.datec&amp;sortorder=desc&amp;begin=&amp;stcomm=-1", $langs->trans("LastProspectDoNotContact"), 2, $user->rights->societe->lire);
                if (empty($leftmenu) || $leftmenu == "prospects")
                    $newmenu->add("/comm/prospect/list.php?sortfield=s.datec&amp;sortorder=desc&amp;begin=&amp;stcomm=0", $langs->trans("LastProspectNeverContacted"), 2, $user->rights->societe->lire);
                if (empty($leftmenu) || $leftmenu == "prospects")
                    $newmenu->add("/comm/prospect/list.php?sortfield=s.datec&amp;sortorder=desc&amp;begin=&amp;stcomm=1", $langs->trans("LastProspectToContact"), 2, $user->rights->societe->lire);
                if (empty($leftmenu) || $leftmenu == "prospects")
                    $newmenu->add("/comm/prospect/list.php?sortfield=s.datec&amp;sortorder=desc&amp;begin=&amp;stcomm=2", $langs->trans("LastProspectContactInProcess"), 2, $user->rights->societe->lire);
                if (empty($leftmenu) || $leftmenu == "prospects")
                    $newmenu->add("/comm/prospect/list.php?sortfield=s.datec&amp;sortorder=desc&amp;begin=&amp;stcomm=3", $langs->trans("LastProspectContactDone"), 2, $user->rights->societe->lire);

                $newmenu->add("/societe/soc.php?leftmenu=prospects&amp;action=create&amp;type=p", $langs->trans("MenuNewProspect"), 2, $user->rights->societe->creer);
                //$newmenu->add("/contact/list.php?leftmenu=customers&amp;type=p", $langs->trans("Contacts"), 2, $user->rights->societe->contact->lire);
            }

            // Customers/Prospects
            if (!empty($conf->societe->enabled) && empty($conf->global->SOCIETE_DISABLE_CUSTOMERS))
            {
                $langs->load("commercial");
                $newmenu->add("/comm/list.php?leftmenu=customers", $langs->trans("ListCustomersShort"), 1, $user->rights->societe->lire, '', $mainmenu, 'customers');

                $newmenu->add("/societe/soc.php?leftmenu=customers&amp;action=create&amp;type=c", $langs->trans("MenuNewCustomer"), 2, $user->rights->societe->creer);
                //$newmenu->add("/contact/list.php?leftmenu=customers&amp;type=c", $langs->trans("Contacts"), 2, $user->rights->societe->contact->lire);
            }

            // Suppliers
            if (!empty($conf->societe->enabled) && !empty($conf->fournisseur->enabled))
            {
                $langs->load("suppliers");
                $newmenu->add("/fourn/liste.php?leftmenu=suppliers", $langs->trans("ListSuppliersShort"), 1, $user->rights->fournisseur->lire, '', $mainmenu, 'suppliers');
                $newmenu->add("/societe/soc.php?leftmenu=suppliers&amp;action=create&amp;type=f", $langs->trans("MenuNewSupplier"), 2, $user->rights->societe->creer && $user->rights->fournisseur->lire);
                //$newmenu->add("/fourn/liste.php?leftmenu=suppliers", $langs->trans("List"), 2, $user->rights->societe->lire && $user->rights->fournisseur->lire);
                //$newmenu->add("/contact/list.php?leftmenu=suppliers&amp;type=f",$langs->trans("Contacts"), 2, $user->rights->societe->lire && $user->rights->fournisseur->lire && $user->rights->societe->contact->lire);
            }

            // Contacts
            $newmenu->add("/contact/list.php?leftmenu=contacts", (!empty($conf->global->SOCIETE_ADDRESSES_MANAGEMENT) ? $langs->trans("Contacts") : $langs->trans("ContactsAddresses")), 0, $user->rights->societe->contact->lire, '', $mainmenu, 'contacts');
            $newmenu->add("/contact/fiche.php?leftmenu=contacts&amp;action=create", (!empty($conf->global->SOCIETE_ADDRESSES_MANAGEMENT) ? $langs->trans("NewContact") : $langs->trans("NewContactAddress")), 1, $user->rights->societe->contact->creer);
            $newmenu->add("/contact/list.php?leftmenu=contacts", $langs->trans("List"), 1, $user->rights->societe->contact->lire);
            if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS))
                $newmenu->add("/contact/list.php?leftmenu=contacts&type=p", $langs->trans("Prospects"), 2, $user->rights->societe->contact->lire);
            if (empty($conf->global->SOCIETE_DISABLE_CUSTOMERS))
                $newmenu->add("/contact/list.php?leftmenu=contacts&type=c", $langs->trans("Customers"), 2, $user->rights->societe->contact->lire);
            if (!empty($conf->fournisseur->enabled))
                $newmenu->add("/contact/list.php?leftmenu=contacts&type=f", $langs->trans("Suppliers"), 2, $user->rights->societe->contact->lire);
            $newmenu->add("/contact/list.php?leftmenu=contacts&type=o", $langs->trans("Others"), 2, $user->rights->societe->contact->lire);
            //$newmenu->add("/contact/list.php?userid=$user->id", $langs->trans("MyContacts"), 1, $user->rights->societe->contact->lire);
            // Categories
            if (!empty($conf->categorie->enabled))
            {
                $langs->load("categories");
                if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS) && empty($conf->global->SOCIETE_DISABLE_CUSTOMERS))
                {
                    // Categories prospects/customers
                    $newmenu->add("/categories/index.php?leftmenu=cat&amp;type=2", $langs->trans("CustomersProspectsCategoriesShort"), 0, $user->rights->categorie->lire, '', $mainmenu, 'cat');
                    $newmenu->add("/categories/fiche.php?action=create&amp;type=2", $langs->trans("NewCategory"), 1, $user->rights->categorie->creer);
                }
                // Categories Contact
                $newmenu->add("/categories/index.php?leftmenu=cat&amp;type=4", $langs->trans("ContactCategoriesShort"), 0, $user->rights->categorie->lire, '', $mainmenu, 'cat');
                $newmenu->add("/categories/fiche.php?action=create&amp;type=4", $langs->trans("NewCategory"), 1, $user->rights->categorie->creer);
                // Categories suppliers
                if (!empty($conf->fournisseur->enabled))
                {
                    $newmenu->add("/categories/index.php?leftmenu=cat&amp;type=1", $langs->trans("SuppliersCategoriesShort"), 0, $user->rights->categorie->lire);
                    $newmenu->add("/categories/fiche.php?action=create&amp;type=1", $langs->trans("NewCategory"), 1, $user->rights->categorie->creer);
                }
                //if (empty($leftmenu) || $leftmenu=="cat") $newmenu->add("/categories/liste.php", $langs->trans("List"), 1, $user->rights->categorie->lire);
            }
        }

        /*
         * Menu COMMERCIAL
         */
        if ($mainmenu == 'commercial')
        {
            $langs->load("companies");

            // Propal
            if (!empty($conf->propal->enabled))
            {
                $langs->load("propal");
                $newmenu->add("/comm/propal/index.php?leftmenu=propals", $langs->trans("Prop"), 0, $user->rights->propale->lire, '', $mainmenu, 'propals');
                $newmenu->add("/comm/propal.php?action=create&amp;leftmenu=propals", $langs->trans("NewPropal"), 1, $user->rights->propale->creer);
                $newmenu->add("/comm/propal/list.php?leftmenu=propals", $langs->trans("List"), 1, $user->rights->propale->lire);
                if (empty($leftmenu) || $leftmenu == "propals")
                    $newmenu->add("/comm/propal/list.php?leftmenu=propals&viewstatut=0", $langs->trans("PropalsDraft"), 2, $user->rights->propale->lire);
                if (empty($leftmenu) || $leftmenu == "propals")
                    $newmenu->add("/comm/propal/list.php?leftmenu=propals&viewstatut=1", $langs->trans("PropalsOpened"), 2, $user->rights->propale->lire);
                if (empty($leftmenu) || $leftmenu == "propals")
                    $newmenu->add("/comm/propal/list.php?leftmenu=propals&viewstatut=2", $langs->trans("PropalStatusSigned"), 2, $user->rights->propale->lire);
                if (empty($leftmenu) || $leftmenu == "propals")
                    $newmenu->add("/comm/propal/list.php?leftmenu=propals&viewstatut=3", $langs->trans("PropalStatusNotSigned"), 2, $user->rights->propale->lire);
                if (empty($leftmenu) || $leftmenu == "propals")
                    $newmenu->add("/comm/propal/list.php?leftmenu=propals&viewstatut=4", $langs->trans("PropalStatusBilled"), 2, $user->rights->propale->lire);
                //if (empty($leftmenu) || $leftmenu=="propals") $newmenu->add("/comm/propal/list.php?leftmenu=propals&viewstatut=2,3,4", $langs->trans("PropalStatusClosedShort"), 2, $user->rights->propale->lire);
                $newmenu->add("/comm/propal/stats/index.php?leftmenu=propals", $langs->trans("Statistics"), 1, $user->rights->propale->lire);
            }

            // Customers orders
            if (!empty($conf->commande->enabled))
            {
                $langs->load("orders");
                $newmenu->add("/commande/index.php?leftmenu=orders", $langs->trans("CustomersOrders"), 0, $user->rights->commande->lire, '', $mainmenu, 'orders');
                $newmenu->add("/commande/fiche.php?action=create&amp;leftmenu=orders", $langs->trans("NewOrder"), 1, $user->rights->commande->creer);
                $newmenu->add("/commande/liste.php?leftmenu=orders", $langs->trans("List"), 1, $user->rights->commande->lire);
                if (empty($leftmenu) || $leftmenu == "orders")
                    $newmenu->add("/commande/liste.php?leftmenu=orders&viewstatut=0", $langs->trans("StatusOrderDraftShort"), 2, $user->rights->commande->lire);
                if (empty($leftmenu) || $leftmenu == "orders")
                    $newmenu->add("/commande/liste.php?leftmenu=orders&viewstatut=1", $langs->trans("StatusOrderValidated"), 2, $user->rights->commande->lire);
                if (empty($leftmenu) || $leftmenu == "orders" && !empty($conf->expedition->enabled))
                    $newmenu->add("/commande/liste.php?leftmenu=orders&viewstatut=2", $langs->trans("StatusOrderSentShort"), 2, $user->rights->commande->lire);
                if (empty($leftmenu) || $leftmenu == "orders")
                    $newmenu->add("/commande/liste.php?leftmenu=orders&viewstatut=3", $langs->trans("StatusOrderToBill"), 2, $user->rights->commande->lire);  // The translation key is StatusOrderToBill but it means StatusDelivered. TODO We should renamed this later
                if (empty($leftmenu) || $leftmenu == "orders")
                    $newmenu->add("/commande/liste.php?leftmenu=orders&viewstatut=4", $langs->trans("StatusOrderProcessed"), 2, $user->rights->commande->lire);
                if (empty($leftmenu) || $leftmenu == "orders")
                    $newmenu->add("/commande/liste.php?leftmenu=orders&viewstatut=-1", $langs->trans("StatusOrderCanceledShort"), 2, $user->rights->commande->lire);
                $newmenu->add("/commande/stats/index.php?leftmenu=orders", $langs->trans("Statistics"), 1, $user->rights->commande->lire);
            }

            // Suppliers orders
            if (!empty($conf->fournisseur->enabled))
            {
                $langs->load("orders");
                $newmenu->add("/fourn/commande/index.php?leftmenu=orders_suppliers", $langs->trans("SuppliersOrders"), 0, $user->rights->fournisseur->commande->lire, '', $mainmenu, 'orders_suppliers');
                $newmenu->add("/fourn/commande/fiche.php?action=create&amp;leftmenu=orders_suppliers", $langs->trans("NewOrder"), 1, $user->rights->fournisseur->commande->creer);
                $newmenu->add("/fourn/commande/liste.php?leftmenu=orders_suppliers", $langs->trans("List"), 1, $user->rights->fournisseur->commande->lire);

                if (empty($leftmenu) || $leftmenu == "orders_suppliers")
                    $newmenu->add("/fourn/commande/liste.php?leftmenu=orders_suppliers&statut=0", $langs->trans("StatusOrderDraftShort"), 2, $user->rights->fournisseur->commande->lire);
                if (empty($leftmenu) || $leftmenu == "orders_suppliers")
                    $newmenu->add("/fourn/commande/liste.php?leftmenu=orders_suppliers&statut=1", $langs->trans("StatusOrderValidated"), 2, $user->rights->fournisseur->commande->lire);
                if (empty($leftmenu) || $leftmenu == "orders_suppliers")
                    $newmenu->add("/fourn/commande/liste.php?leftmenu=orders_suppliers&statut=2", $langs->trans("StatusOrderApprovedShort"), 2, $user->rights->fournisseur->commande->lire);
                if (empty($leftmenu) || $leftmenu == "orders_suppliers")
                    $newmenu->add("/fourn/commande/liste.php?leftmenu=orders_suppliers&statut=3", $langs->trans("StatusOrderOnProcess"), 2, $user->rights->fournisseur->commande->lire);
                if (empty($leftmenu) || $leftmenu == "orders_suppliers")
                    $newmenu->add("/fourn/commande/liste.php?leftmenu=orders_suppliers&statut=4", $langs->trans("StatusOrderReceivedPartially"), 2, $user->rights->fournisseur->commande->lire);
                if (empty($leftmenu) || $leftmenu == "orders_suppliers")
                    $newmenu->add("/fourn/commande/liste.php?leftmenu=orders_suppliers&statut=5", $langs->trans("StatusOrderReceivedAll"), 2, $user->rights->fournisseur->commande->lire);
                if (empty($leftmenu) || $leftmenu == "orders_suppliers")
                    $newmenu->add("/fourn/commande/liste.php?leftmenu=orders_suppliers&statut=6,7", $langs->trans("StatusOrderCanceled"), 2, $user->rights->fournisseur->commande->lire);
                if (empty($leftmenu) || $leftmenu == "orders_suppliers")
                    $newmenu->add("/fourn/commande/liste.php?leftmenu=orders_suppliers&statut=9", $langs->trans("StatusOrderRefused"), 2, $user->rights->fournisseur->commande->lire);


                $newmenu->add("/commande/stats/index.php?leftmenu=orders_suppliers&amp;mode=supplier", $langs->trans("Statistics"), 1, $user->rights->fournisseur->commande->lire);
            }

            // Contrat
            if (!empty($conf->contrat->enabled))
            {
                $langs->load("contracts");
                $newmenu->add("/contrat/index.php?leftmenu=contracts", $langs->trans("Contracts"), 0, $user->rights->contrat->lire, '', $mainmenu, 'contracts');
                $newmenu->add("/contrat/fiche.php?&action=create&amp;leftmenu=contracts", $langs->trans("NewContract"), 1, $user->rights->contrat->creer);
                $newmenu->add("/contrat/liste.php?leftmenu=contracts", $langs->trans("List"), 1, $user->rights->contrat->lire);
                $newmenu->add("/contrat/services.php?leftmenu=contracts", $langs->trans("MenuServices"), 1, $user->rights->contrat->lire);
                if (empty($leftmenu) || $leftmenu == "contracts")
                    $newmenu->add("/contrat/services.php?leftmenu=contracts&amp;mode=0", $langs->trans("MenuInactiveServices"), 2, $user->rights->contrat->lire);
                if (empty($leftmenu) || $leftmenu == "contracts")
                    $newmenu->add("/contrat/services.php?leftmenu=contracts&amp;mode=4", $langs->trans("MenuRunningServices"), 2, $user->rights->contrat->lire);
                if (empty($leftmenu) || $leftmenu == "contracts")
                    $newmenu->add("/contrat/services.php?leftmenu=contracts&amp;mode=4&amp;filter=expired", $langs->trans("MenuExpiredServices"), 2, $user->rights->contrat->lire);
                if (empty($leftmenu) || $leftmenu == "contracts")
                    $newmenu->add("/contrat/services.php?leftmenu=contracts&amp;mode=5", $langs->trans("MenuClosedServices"), 2, $user->rights->contrat->lire);
            }

            // Interventions
            if (!empty($conf->ficheinter->enabled))
            {
                $langs->load("interventions");
                $newmenu->add("/fichinter/list.php?leftmenu=ficheinter", $langs->trans("Interventions"), 0, $user->rights->ficheinter->lire, '', $mainmenu, 'ficheinter');
                $newmenu->add("/fichinter/fiche.php?action=create&amp;leftmenu=ficheinter", $langs->trans("NewIntervention"), 1, $user->rights->ficheinter->creer);
                $newmenu->add("/fichinter/list.php?leftmenu=ficheinter", $langs->trans("List"), 1, $user->rights->ficheinter->lire);
            }
        }


        /*
         * Menu COMPTA-FINANCIAL
         */
        if ($mainmenu == 'accountancy')
        {
            $langs->load("companies");

            // Customers invoices
            if (!empty($conf->facture->enabled))
            {
                $langs->load("bills");
                $newmenu->add("/compta/facture/list.php?leftmenu=customers_bills", $langs->trans("BillsCustomers"), 0, $user->rights->facture->lire, '', $mainmenu, 'customers_bills');
                $newmenu->add("/compta/facture.php?action=create&amp;leftmenu=customers_bills", $langs->trans("NewBill"), 1, $user->rights->facture->creer);
                $newmenu->add("/compta/facture/fiche-rec.php?leftmenu=customers_bills", $langs->trans("Repeatables"), 1, $user->rights->facture->lire);

                $newmenu->add("/compta/facture/impayees.php?leftmenu=customers_bills", $langs->trans("Unpaid"), 1, $user->rights->facture->lire);

                $newmenu->add("/compta/paiement/liste.php?leftmenu=customers_bills_payments", $langs->trans("Payments"), 1, $user->rights->facture->lire);

                if (!empty($conf->global->BILL_ADD_PAYMENT_VALIDATION))
                {
                    $newmenu->add("/compta/paiement/avalider.php?leftmenu=customers_bills_payments", $langs->trans("MenuToValid"), 2, $user->rights->facture->lire);
                }
                $newmenu->add("/compta/paiement/rapport.php?leftmenu=customers_bills_payments", $langs->trans("Reportings"), 2, $user->rights->facture->lire);

                $newmenu->add("/compta/facture/stats/index.php?leftmenu=customers_bills", $langs->trans("Statistics"), 1, $user->rights->facture->lire);
            }

            // Suppliers
            if (!empty($conf->societe->enabled) && !empty($conf->fournisseur->enabled))
            {
                $langs->load("bills");
                $newmenu->add("/fourn/facture/list.php?leftmenu=suppliers_bills", $langs->trans("BillsSuppliers"), 0, $user->rights->fournisseur->facture->lire, '', $mainmenu, 'suppliers_bills');
                $newmenu->add("/fourn/facture/fiche.php?action=create", $langs->trans("NewBill"), 1, $user->rights->fournisseur->facture->creer);
                $newmenu->add("/fourn/facture/impayees.php", $langs->trans("Unpaid"), 1, $user->rights->fournisseur->facture->lire);
                $newmenu->add("/fourn/facture/paiement.php", $langs->trans("Payments"), 1, $user->rights->fournisseur->facture->lire);

                $newmenu->add("/compta/facture/stats/index.php?leftmenu=suppliers_bills&mode=supplier", $langs->trans("Statistics"), 1, $user->rights->fournisseur->facture->lire);
            }

            // Orders
            if (!empty($conf->commande->enabled))
            {
                $langs->load("orders");
                if (!empty($conf->facture->enabled))
                    $newmenu->add("/commande/liste.php?leftmenu=orders&amp;viewstatut=-3", $langs->trans("MenuOrdersToBill2"), 0, $user->rights->commande->lire, '', $mainmenu, 'orders');
                //                  if (empty($leftmenu) || $leftmenu=="orders") $newmenu->add("/commande/", $langs->trans("StatusOrderToBill"), 1, $user->rights->commande->lire);
            }

            // Donations
            if (!empty($conf->don->enabled))
            {
                $langs->load("donations");
                $newmenu->add("/compta/dons/index.php?leftmenu=donations&amp;mainmenu=accountancy", $langs->trans("Donations"), 0, $user->rights->don->lire, '', $mainmenu, 'donations');
                if (empty($leftmenu) || $leftmenu == "donations")
                    $newmenu->add("/compta/dons/fiche.php?action=create", $langs->trans("NewDonation"), 1, $user->rights->don->creer);
                if (empty($leftmenu) || $leftmenu == "donations")
                    $newmenu->add("/compta/dons/liste.php", $langs->trans("List"), 1, $user->rights->don->lire);
                //if ($leftmenu=="donations") $newmenu->add("/compta/dons/stats.php",$langs->trans("Statistics"), 1, $user->rights->don->lire);
            }

            // Taxes and social contributions
            if (!empty($conf->tax->enabled) || !empty($conf->salaries->enabled))
            {
                global $mysoc;

                $permtoshowmenu = ((!empty($conf->tax->enabled) && $user->rights->tax->charges->lire) || (!empty($conf->salaries->enabled) && $user->rights->salaries->read));
                $newmenu->add("/compta/charges/index.php?leftmenu=tax&amp;mainmenu=accountancy", $langs->trans("MenuSpecialExpenses"), 0, $permtoshowmenu, '', $mainmenu, 'tax');

                // Salaries
                if (!empty($conf->salaries->enabled))
                {
                    $langs->load("salaries");
                    $newmenu->add("/compta/salaries/index.php?leftmenu=tax_salary&amp;mainmenu=accountancy", $langs->trans("Salaries"), 1, $user->rights->salaries->read, '', $mainmenu, 'tax_salary');
                    if (empty($leftmenu) || preg_match('/^tax_salary/i', $leftmenu))
                        $newmenu->add("/compta/salaries/fiche.php?leftmenu=tax_salary&action=create", $langs->trans("NewPayment"), 2, $user->rights->salaries->write);
                    if (empty($leftmenu) || preg_match('/^tax_salary/i', $leftmenu))
                        $newmenu->add("/compta/salaries/index.php?leftmenu=tax_salary", $langs->trans("Payments"), 2, $user->rights->salaries->read);
                }

                // Social contributions
                if (!empty($conf->tax->enabled))
                {
                    $newmenu->add("/compta/sociales/index.php?leftmenu=tax_social", $langs->trans("MenuSocialContributions"), 1, $user->rights->tax->charges->lire);
                    if (empty($leftmenu) || preg_match('/^tax_social/i', $leftmenu))
                        $newmenu->add("/compta/sociales/charges.php?leftmenu=tax_social&action=create", $langs->trans("MenuNewSocialContribution"), 2, $user->rights->tax->charges->creer);
                    if (empty($leftmenu) || preg_match('/^tax_social/i', $leftmenu))
                        $newmenu->add("/compta/charges/index.php?leftmenu=tax_social&amp;mainmenu=accountancy&amp;mode=sconly", $langs->trans("Payments"), 2, $user->rights->tax->charges->lire);
                    // VAT
                    if (empty($conf->global->TAX_DISABLE_VAT_MENUS))
                    {
                        $newmenu->add("/compta/tva/index.php?leftmenu=tax_vat&amp;mainmenu=accountancy", $langs->trans("VAT"), 1, $user->rights->tax->charges->lire, '', $mainmenu, 'tax_vat');
                        if (empty($leftmenu) || preg_match('/^tax_vat/i', $leftmenu))
                            $newmenu->add("/compta/tva/fiche.php?leftmenu=tax_vat&action=create", $langs->trans("NewPayment"), 2, $user->rights->tax->charges->creer);
                        if (empty($leftmenu) || preg_match('/^tax_vat/i', $leftmenu))
                            $newmenu->add("/compta/tva/reglement.php?leftmenu=tax_vat", $langs->trans("Payments"), 2, $user->rights->tax->charges->lire);
                        if (empty($leftmenu) || preg_match('/^tax_vat/i', $leftmenu))
                            $newmenu->add("/compta/tva/clients.php?leftmenu=tax_vat", $langs->trans("ReportByCustomers"), 2, $user->rights->tax->charges->lire);
                        if (empty($leftmenu) || preg_match('/^tax_vat/i', $leftmenu))
                            $newmenu->add("/compta/tva/quadri_detail.php?leftmenu=tax_vat", $langs->trans("ReportByQuarter"), 2, $user->rights->tax->charges->lire);
                        global $mysoc;

                        //Local Taxes
                        if ($mysoc->country_code == 'ES' && (isset($mysoc->localtax2_assuj) && $mysoc->localtax2_assuj == "1"))
                        {
                            if (empty($leftmenu) || preg_match('/^tax_vat/i', $leftmenu))
                                $newmenu->add("/compta/localtax/index.php?leftmenu=tax_vat&amp;mainmenu=accountancy", $langs->transcountry("LT2", $mysoc->country_code), 1, $user->rights->tax->charges->lire);
                            if (empty($leftmenu) || preg_match('/^tax_vat/i', $leftmenu))
                                $newmenu->add("/compta/localtax/fiche.php?leftmenu=tax_vat&action=create", $langs->trans("NewPayment"), 2, $user->rights->tax->charges->creer);
                            if (empty($leftmenu) || preg_match('/^tax_vat/i', $leftmenu))
                                $newmenu->add("/compta/localtax/reglement.php?leftmenu=tax_vat", $langs->trans("Payments"), 2, $user->rights->tax->charges->lire);
                            if (empty($leftmenu) || preg_match('/^tax_vat/i', $leftmenu))
                                $newmenu->add("/compta/localtax/clients.php?leftmenu=tax_vat", $langs->trans("ReportByCustomers"), 2, $user->rights->tax->charges->lire);
                            //if (empty($leftmenu) || preg_match('/^tax/i',$leftmenu)) $newmenu->add("/compta/localtax/quadri_detail.php?leftmenu=tax_vat", $langs->trans("ReportByQuarter"), 2, $user->rights->tax->charges->lire);
                        }
                    }
                }
            }

            // Compta simple
            if (!empty($conf->comptabilite->enabled) && ($conf->global->MAIN_FEATURES_LEVEL >= 2))
            {
                $newmenu->add("/compta/ventilation/index.php?leftmenu=ventil", $langs->trans("Dispatch"), 0, $user->rights->compta->ventilation->lire, '', $mainmenu, 'ventil');
                if (empty($leftmenu) || $leftmenu == "ventil")
                    $newmenu->add("/compta/ventilation/liste.php", $langs->trans("ToDispatch"), 1, $user->rights->compta->ventilation->lire);
                if (empty($leftmenu) || $leftmenu == "ventil")
                    $newmenu->add("/compta/ventilation/lignes.php", $langs->trans("Dispatched"), 1, $user->rights->compta->ventilation->lire);
            }

            // Compta expert
            if (!empty($conf->accounting->enabled))
            {

            }

            // Rapports
            if (!empty($conf->comptabilite->enabled) || !empty($conf->accounting->enabled))
            {
                $langs->load("compta");

                // Bilan, resultats
                $newmenu->add("/compta/resultat/index.php?leftmenu=ca&amp;mainmenu=accountancy", $langs->trans("Reportings"), 0, $user->rights->compta->resultat->lire || $user->rights->accounting->comptarapport->lire, '', $mainmenu, 'ca');

                if (empty($leftmenu) || $leftmenu == "ca")
                    $newmenu->add("/compta/resultat/index.php?leftmenu=ca", $langs->trans("ReportInOut"), 1, $user->rights->compta->resultat->lire || $user->rights->accounting->comptarapport->lire);
                if (empty($leftmenu) || $leftmenu == "ca")
                    $newmenu->add("/compta/resultat/clientfourn.php?leftmenu=ca", $langs->trans("ByCompanies"), 2, $user->rights->compta->resultat->lire || $user->rights->accounting->comptarapport->lire);
                /* On verra ca avec module compabilite expert
                  if (empty($leftmenu) || $leftmenu=="ca") $newmenu->add("/compta/resultat/compteres.php?leftmenu=ca","Compte de resultat",2,$user->rights->compta->resultat->lire);
                  if (empty($leftmenu) || $leftmenu=="ca") $newmenu->add("/compta/resultat/bilan.php?leftmenu=ca","Bilan",2,$user->rights->compta->resultat->lire);
                 */
                if (empty($leftmenu) || $leftmenu == "ca")
                    $newmenu->add("/compta/stats/index.php?leftmenu=ca", $langs->trans("ReportTurnover"), 1, $user->rights->compta->resultat->lire || $user->rights->accounting->comptarapport->lire);

                /*
                  if (empty($leftmenu) || $leftmenu=="ca") $newmenu->add("/compta/stats/cumul.php?leftmenu=ca","Cumule",2,$user->rights->compta->resultat->lire);
                  if (! empty($conf->propal->enabled)) {
                  if (empty($leftmenu) || $leftmenu=="ca") $newmenu->add("/compta/stats/prev.php?leftmenu=ca","Previsionnel",2,$user->rights->compta->resultat->lire);
                  if (empty($leftmenu) || $leftmenu=="ca") $newmenu->add("/compta/stats/comp.php?leftmenu=ca","Transforme",2,$user->rights->compta->resultat->lire);
                  }
                 */
                if (empty($leftmenu) || $leftmenu == "ca")
                    $newmenu->add("/compta/stats/casoc.php?leftmenu=ca", $langs->trans("ByCompanies"), 2, $user->rights->compta->resultat->lire || $user->rights->accounting->comptarapport->lire);
                if (empty($leftmenu) || $leftmenu == "ca")
                    $newmenu->add("/compta/stats/cabyuser.php?leftmenu=ca", $langs->trans("ByUsers"), 2, $user->rights->compta->resultat->lire || $user->rights->accounting->comptarapport->lire);
                if (empty($leftmenu) || $leftmenu == "ca")
                    $newmenu->add("/compta/stats/cabyprodserv.php?leftmenu=ca", $langs->trans("ByProductsAndServices"), 2, $user->rights->compta->resultat->lire || $user->rights->accounting->comptarapport->lire);


                // Journaux
                //if ($leftmenu=="ca") $newmenu->add("/compta/journaux/index.php?leftmenu=ca",$langs->trans("Journaux"),1,$user->rights->compta->resultat->lire||$user->rights->accounting->comptarapport->lire);
                //journaux
                if (empty($leftmenu) || $leftmenu == "ca")
                    $newmenu->add("/compta/journal/sellsjournal.php?leftmenu=ca", $langs->trans("SellsJournal"), 1, $user->rights->compta->resultat->lire || $user->rights->accounting->comptarapport->lire);
                if (empty($leftmenu) || $leftmenu == "ca")
                    $newmenu->add("/compta/journal/purchasesjournal.php?leftmenu=ca", $langs->trans("PurchasesJournal"), 1, $user->rights->compta->resultat->lire || $user->rights->accounting->comptarapport->lire);
            }
        }


        /*
         * Menu BANK
         */
        if ($mainmenu == 'bank')
        {
            $langs->load("withdrawals");
            $langs->load("banks");
            $langs->load("bills");

            // Bank-Caisse
            if (!empty($conf->banque->enabled))
            {
                $newmenu->add("/compta/bank/index.php?leftmenu=bank&amp;mainmenu=bank", $langs->trans("MenuBankCash"), 0, $user->rights->banque->lire, '', $mainmenu, 'bank');

                $newmenu->add("/compta/bank/fiche.php?action=create", $langs->trans("MenuNewFinancialAccount"), 1, $user->rights->banque->configurer);
                $newmenu->add("/compta/bank/categ.php", $langs->trans("Rubriques"), 1, $user->rights->banque->configurer);

                $newmenu->add("/compta/bank/search.php", $langs->trans("ListTransactions"), 1, $user->rights->banque->lire);
                $newmenu->add("/compta/bank/budget.php", $langs->trans("ListTransactionsByCategory"), 1, $user->rights->banque->lire);

                $newmenu->add("/compta/bank/virement.php", $langs->trans("BankTransfers"), 1, $user->rights->banque->transfer);
            }

            // Prelevements
            if (!empty($conf->prelevement->enabled))
            {
                $newmenu->add("/compta/prelevement/index.php?leftmenu=withdraw&amp;mainmenu=bank", $langs->trans("StandingOrders"), 0, $user->rights->prelevement->bons->lire, '', $mainmenu, 'withdraw');

                //if (empty($leftmenu) || $leftmenu=="withdraw") $newmenu->add("/compta/prelevement/demandes.php?status=0&amp;mainmenu=bank",$langs->trans("StandingOrderToProcess"),1,$user->rights->prelevement->bons->lire);

                if (empty($leftmenu) || $leftmenu == "withdraw")
                    $newmenu->add("/compta/prelevement/create.php?mainmenu=bank", $langs->trans("NewStandingOrder"), 1, $user->rights->prelevement->bons->creer);


                if (empty($leftmenu) || $leftmenu == "withdraw")
                    $newmenu->add("/compta/prelevement/bons.php?mainmenu=bank", $langs->trans("WithdrawalsReceipts"), 1, $user->rights->prelevement->bons->lire);
                if (empty($leftmenu) || $leftmenu == "withdraw")
                    $newmenu->add("/compta/prelevement/liste.php?mainmenu=bank", $langs->trans("WithdrawalsLines"), 1, $user->rights->prelevement->bons->lire);
                if (empty($leftmenu) || $leftmenu == "withdraw")
                    $newmenu->add("/compta/prelevement/rejets.php?mainmenu=bank", $langs->trans("Rejects"), 1, $user->rights->prelevement->bons->lire);
                if (empty($leftmenu) || $leftmenu == "withdraw")
                    $newmenu->add("/compta/prelevement/stats.php?mainmenu=bank", $langs->trans("Statistics"), 1, $user->rights->prelevement->bons->lire);

                //if (empty($leftmenu) || $leftmenu=="withdraw") $newmenu->add("/compta/prelevement/config.php",$langs->trans("Setup"),1,$user->rights->prelevement->bons->configurer);
            }

            // Gestion cheques
            if (!empty($conf->banque->enabled) && (!empty($conf->facture->enabled)) || !empty($conf->global->MAIN_MENU_CHEQUE_DEPOSIT_ON))
            {
                $newmenu->add("/compta/paiement/cheque/index.php?leftmenu=checks&amp;mainmenu=bank", $langs->trans("MenuChequeDeposits"), 0, $user->rights->banque->cheque, '', $mainmenu, 'checks');
                $newmenu->add("/compta/paiement/cheque/fiche.php?leftmenu=checks&amp;action=new&amp;mainmenu=bank", $langs->trans("NewChequeDeposit"), 1, $user->rights->banque->cheque);
                $newmenu->add("/compta/paiement/cheque/liste.php?leftmenu=checks&amp;mainmenu=bank", $langs->trans("List"), 1, $user->rights->banque->cheque);
            }
        }

        /*
         * Menu PRODUCTS-SERVICES
         */
        if ($mainmenu == 'products')
        {
            if ($user->login != "laboratorio" && $user->login != "solari" && $user->login != "st_solari" && $user->login != "pcm_napoli" && $user->login != "pcm_milano" && $user->login != "tpr")
            {
                if (!empty($conf->product->enabled))
                {
                    $newmenu->add("/product/index.php?leftmenu=product&amp;type=0", $langs->trans("Products"), 0, $user->rights->produit->lire, '', $mainmenu, 'product');
                    $newmenu->add("/product/fiche.php?leftmenu=product&amp;action=create&amp;type=0", $langs->trans("NewProduct"), 1, $user->rights->produit->creer);
                    $newmenu->add("/product/liste.php?leftmenu=product&amp;type=0", $langs->trans("List"), 1, $user->rights->produit->lire);
                    if (!empty($conf->propal->enabled))
                    {
                        $newmenu->add("/product/popuprop.php?leftmenu=stats&amp;type=0", $langs->trans("Statistics"), 1, $user->rights->produit->lire && $user->rights->propale->lire);
                    }
                    if (!empty($conf->stock->enabled))
                    {
                        $newmenu->add("/product/reassort.php?type=0", $langs->trans("Stocks"), 1, $user->rights->produit->lire && $user->rights->stock->lire);
                    }
                }
            }
            if (!empty($conf->family->enabled))
            {
                $flag_tutti = true;
                if ($user->login == "solari")
                {
                    $crea_famiglia = "Crea famiglia";

                    $newmenu->add("/product/elenco_famiglia.php?leftmenu=product&amp;type=2", $langs->trans("Family"), 0, $user->rights->produit->lire, '', $mainmenu, 'product'); //il titolo del link a sinistra
                    $newmenu->add("/product/fiche.php?leftmenu=family&amp;action=create&amp;type=2", $crea_famiglia, 1, $user->rights->produit->creer);
                    $newmenu->add("/product/lista_famiglia.php?leftmenu=product&amp;type=2", "Cerca famiglia", 1, $user->rights->produit->lire);
                    $newmenu->add("/product/elenco_famiglia.php?leftmenu=product&amp;type=2", "Elenco famiglia", 1, $user->rights->produit->lire);

                    $newmenu->add("/product/elenco_asset.php?leftmenu=product&amp;type=4", "Asset", 0, $user->rights->produit->lire);
                    $newmenu->add("/product/crea_asset.php?leftmenu=family&amp;action=create&amp;type=3", $langs->trans("CreateAsset"), 1, $user->rights->produit->lire);
                    $newmenu->add("/product/elenco_asset.php?leftmenu=product&amp;type=4", "Elenco asset", 1, $user->rights->produit->lire);
                    $newmenu->add("/product/asset_massivo.php?leftmenu=family&amp;action=create&amp;type=3", "Censimento massivo", 1, $user->rights->produit->lire);
                    $newmenu->add("/product/movimentazione.php?leftmenu=product&amp;type=5", "Movimentazione asset", 1, $user->rights->produit->lire);

                    $newmenu->add("/product/solari_report.php?leftmenu=product&amp;type=2", "Riepilogo asset", 0, $user->rights->produit->lire);
                    $flag_tutti = false;
                } else if ($user->login == "st_solari" || $user->login == "pcm_napoli" || $user->login == "pcm_milano" || $user->login == "tpr" || $user->login == "laboratorio")
                {
                    $newmenu->add("/product/elenco_asset.php?leftmenu=product&amp;type=4", "Asset", 0, $user->rights->produit->lire);
                    $newmenu->add("/product/crea_asset.php?leftmenu=family&amp;action=create&amp;type=3", $langs->trans("CreateAsset"), 1, $user->rights->produit->lire);
                    $newmenu->add("/product/elenco_asset.php?leftmenu=product&amp;type=4", "Elenco asset", 1, $user->rights->produit->lire);
                    $newmenu->add("/product/asset_massivo.php?leftmenu=family&amp;action=create&amp;type=3", "Censimento massivo", 1, $user->rights->produit->lire);
                    $newmenu->add("/product/movimentazione.php?leftmenu=product&amp;type=5", "Movimentazione asset", 1, $user->rights->produit->lire);

                    $newmenu->add("/product/solari_report.php?leftmenu=product&amp;type=2", "Riepilogo asset", 0, $user->rights->produit->lire);

                    $flag_tutti = false;
                }
                if ($flag_tutti == true)
                {
                    //$newmenu->add("/product/elenco_famiglia.php?leftmenu=product&amp;type=2", $langs->trans("Family"), 0, $user->rights->produit->lire, '', $mainmenu, 'product');
                    $newmenu->add("/product/elenco_famiglia.php?leftmenu=product&amp;type=2", $langs->trans("Family"), 0, $user->rights->produit->lire, '', $mainmenu, 'product'); //il titolo del link a sinistra
                    $newmenu->add("/product/fiche.php?leftmenu=family&amp;action=create&amp;type=2", $langs->trans("NewFamily"), 1, $user->rights->produit->creer);
                    $newmenu->add("/product/lista_famiglia.php?leftmenu=product&amp;type=2", "Cerca famiglia", 1, $user->rights->produit->lire);
                    $newmenu->add("/product/elenco_famiglia.php?leftmenu=product&amp;type=2", "Elenco famiglia", 1, $user->rights->produit->lire);

                    $newmenu->add("/product/elenco_asset.php?leftmenu=product&amp;type=4", "Asset", 0, $user->rights->produit->lire);
                    $newmenu->add("/product/crea_asset.php?leftmenu=family&amp;action=create&amp;type=3", $langs->trans("CreateAsset"), 1, $user->rights->produit->lire);
                    $newmenu->add("/product/elenco_asset.php?leftmenu=product&amp;type=4", "Elenco asset", 1, $user->rights->produit->lire);
                    $newmenu->add("/product/asset_massivo.php?leftmenu=family&amp;action=create&amp;type=3", "Censimento massivo", 1, $user->rights->produit->lire);
                    //$newmenu->add("/product/movimentazione.php?leftmenu=product&amp;type=5", "Movimentazione", 1, $user->rights->produit->lire);
                }
                $newmenu->add("/product/movimentazione.php?leftmenu=product&amp;type=5", "Movimenti ", 0, $user->rights->produit->lire, '', $mainmenu, 'product'); //il titolo del link a sinistra
                $newmenu->add("/product/movimentazione.php?leftmenu=product&amp;type=5", "Nuova movimentazione", 1, $user->rights->produit->lire);
                $newmenu->add("/product/daconvalidare.php?leftmenu=product&type=6&id=3", "Da convalidare", 1, $user->rights->produit->lire);
                $newmenu->add("/product/storico.php?leftmenu=product&type=6&id=4", "Storico", 1, $user->rights->produit->lire);

                $newmenu->add("/product/riepilogo_generico.php", "Reportistica", 0, $user->rights->produit->lire, '', $mainmenu, 'product'); //il titolo del link a sinistra
                $newmenu->add("/product/riepilogo_magazzino.php", "Riepilogo magazzino", 1, $user->rights->produit->lire);
                $newmenu->add("/product/riepilogo_asset.php", "Riepilogo asset", 1, $user->rights->produit->lire);
                $newmenu->add("/product/riepilogo_prodotti.php", "Riepilogo prodotti", 1, $user->rights->produit->lire);
            }
            if ($user->login != "solari" && $user->login != "st_solari" && $user->login != "pcm_napoli" && $user->login != "pcm_milano" && $user->login != "tpr")
            {
                // Services
                if (!empty($conf->service->enabled))
                {
                    $newmenu->add("/product/index.php?leftmenu=service&amp;type=1", $langs->trans("Services"), 0, $user->rights->service->lire, '', $mainmenu, 'service');
                    $newmenu->add("/product/fiche.php?leftmenu=service&amp;action=create&amp;type=1", $langs->trans("NewService"), 1, $user->rights->service->creer);
                    $newmenu->add("/product/liste.php?leftmenu=service&amp;type=1", $langs->trans("List"), 1, $user->rights->service->lire);
                    if (!empty($conf->propal->enabled))
                    {
                        $newmenu->add("/product/popuprop.php?leftmenu=stats&amp;type=1", $langs->trans("Statistics"), 1, $user->rights->service->lire && $user->rights->propale->lire);
                    }
                }
            }

            // Categories
            if (!empty($conf->categorie->enabled))
            {
                $langs->load("categories");
                $newmenu->add("/categories/index.php?leftmenu=cat&amp;type=0", $langs->trans("Categories"), 0, $user->rights->categorie->lire, '', $mainmenu, 'cat');
                $newmenu->add("/categories/fiche.php?action=create&amp;type=0", $langs->trans("NewCategory"), 1, $user->rights->categorie->creer);
                //if (empty($leftmenu) || $leftmenu=="cat") $newmenu->add("/categories/liste.php", $langs->trans("List"), 1, $user->rights->categorie->lire);
            }
            if ($user->login != "solari" && $user->login != "st_solari" && $user->login != "pcm_napoli" && $user->login != "pcm_milano" && $user->login != "tpr")
            {
                // Stocks
                if (!empty($conf->stock->enabled))
                {
                    $langs->load("stocks");
                    $newmenu->add("/product/stock/index.php?leftmenu=stock", $langs->trans("Stocks"), 0, $user->rights->stock->lire, '', $mainmenu, 'stock');
                    if (empty($leftmenu) || $leftmenu == "stock")
                        $newmenu->add("/product/stock/fiche.php?action=create", $langs->trans("MenuNewWarehouse"), 1, $user->rights->stock->creer);
                    if (empty($leftmenu) || $leftmenu == "stock")
                        $newmenu->add("/product/stock/liste.php", $langs->trans("List"), 1, $user->rights->stock->lire);
                    if (empty($leftmenu) || $leftmenu == "stock")
                        $newmenu->add("/product/stock/valo.php", $langs->trans("EnhancedValue"), 1, $user->rights->stock->lire);
                    if (empty($leftmenu) || $leftmenu == "stock")
                        $newmenu->add("/product/stock/mouvement.php", $langs->trans("Movements"), 1, $user->rights->stock->mouvement->lire);
                    if ($conf->fournisseur->enabled)
                        if (empty($leftmenu) || $leftmenu == "stock")
                            $newmenu->add("/product/stock/replenish.php", $langs->trans("Replenishment"), 1, $user->rights->stock->mouvement->lire && $user->rights->fournisseur->lire);
                    if ($conf->fournisseur->enabled)
                        if (empty($leftmenu) || $leftmenu == "stock")
                            $newmenu->add("/product/stock/massstockmove.php", $langs->trans("StockTransfer"), 1, $user->rights->stock->mouvement->lire && $user->rights->fournisseur->lire);
                }

                // Expeditions
                if (!empty($conf->expedition->enabled))
                {
                    $langs->load("sendings");
                    $newmenu->add("/expedition/index.php?leftmenu=sendings", $langs->trans("Shipments"), 0, $user->rights->expedition->lire, '', $mainmenu, 'sendings');
                    $newmenu->add("/expedition/fiche.php?action=create2&amp;leftmenu=sendings", $langs->trans("NewSending"), 1, $user->rights->expedition->creer);
                    $newmenu->add("/expedition/liste.php?leftmenu=sendings", $langs->trans("List"), 1, $user->rights->expedition->lire);
                    $newmenu->add("/expedition/stats/index.php?leftmenu=sendings", $langs->trans("Statistics"), 1, $user->rights->expedition->lire);
                }
            }
        }
        /*
         * Menu SUPPLIERS
         */
        if ($mainmenu == 'suppliers')
        {
            $langs->load("suppliers");

            if (!empty($conf->societe->enabled) && !empty($conf->fournisseur->enabled))
            {
                $newmenu->add("/fourn/index.php?leftmenu=suppliers", $langs->trans("Suppliers"), 0, $user->rights->societe->lire && $user->rights->fournisseur->lire, '', $mainmenu, 'suppliers');

                // Security check
                $newmenu->add("/societe/soc.php?leftmenu=suppliers&amp;action=create&amp;type=f", $langs->trans("NewSupplier"), 1, $user->rights->societe->creer && $user->rights->fournisseur->lire);
                $newmenu->add("/fourn/liste.php", $langs->trans("List"), 1, $user->rights->societe->lire && $user->rights->fournisseur->lire);
                $newmenu->add("/contact/list.php?leftmenu=suppliers&amp;type=f", $langs->trans("Contacts"), 1, $user->rights->societe->contact->lire && $user->rights->fournisseur->lire);
                $newmenu->add("/fourn/stats.php", $langs->trans("Statistics"), 1, $user->rights->societe->lire && $user->rights->fournisseur->lire);
            }

            if (!empty($conf->facture->enabled))
            {
                $langs->load("bills");
                $newmenu->add("/fourn/facture/list.php?leftmenu=orders", $langs->trans("Bills"), 0, $user->rights->fournisseur->facture->lire, '', $mainmenu, 'orders');
                $newmenu->add("/fourn/facture/fiche.php?action=create", $langs->trans("NewBill"), 1, $user->rights->fournisseur->facture->creer);
                $newmenu->add("/fourn/facture/paiement.php", $langs->trans("Payments"), 1, $user->rights->fournisseur->facture->lire);
            }

            if (!empty($conf->fournisseur->enabled))
            {
                $langs->load("orders");
                $newmenu->add("/fourn/commande/index.php?leftmenu=suppliers", $langs->trans("Orders"), 0, $user->rights->fournisseur->commande->lire, '', $mainmenu, 'suppliers');
                $newmenu->add("/societe/societe.php?leftmenu=supplier", $langs->trans("NewOrder"), 1, $user->rights->fournisseur->commande->creer);
                $newmenu->add("/fourn/commande/liste.php?leftmenu=suppliers", $langs->trans("List"), 1, $user->rights->fournisseur->commande->lire);
            }

            if (!empty($conf->categorie->enabled))
            {
                $langs->load("categories");
                $newmenu->add("/categories/index.php?leftmenu=cat&amp;type=1", $langs->trans("Categories"), 0, $user->rights->categorie->lire, '', $mainmenu, 'cat');
                $newmenu->add("/categories/fiche.php?action=create&amp;type=1", $langs->trans("NewCategory"), 1, $user->rights->categorie->creer);
                //if (empty($leftmenu) || $leftmenu=="cat") $newmenu->add("/categories/liste.php", $langs->trans("List"), 1, $user->rights->categorie->lire);
            }
        }

        /*
         * Menu PROJECTS
         */
        if ($mainmenu == 'project')
        {
            if (!empty($conf->projet->enabled))
            {
                $langs->load("projects");

                // Project affected to user
                $newmenu->add("/projet/index.php?leftmenu=projects&mode=mine", $langs->trans("MyProjects"), 0, $user->rights->projet->lire, '', $mainmenu, 'projects');
                $newmenu->add("/projet/fiche.php?leftmenu=projects&action=create&mode=mine", $langs->trans("NewProject"), 1, $user->rights->projet->creer);
                $newmenu->add("/projet/liste.php?leftmenu=projects&mode=mine", $langs->trans("List"), 1, $user->rights->projet->lire);

                // All project i have permission on
                $newmenu->add("/projet/index.php?leftmenu=projects", $langs->trans("Projects"), 0, $user->rights->projet->lire && $user->rights->projet->lire);
                $newmenu->add("/projet/fiche.php?leftmenu=projects&action=create", $langs->trans("NewProject"), 1, $user->rights->projet->creer && $user->rights->projet->creer);
                $newmenu->add("/projet/liste.php?leftmenu=projects", $langs->trans("List"), 1, $user->rights->projet->lire && $user->rights->projet->lire);

                // Project affected to user
                $newmenu->add("/projet/activity/index.php?mode=mine", $langs->trans("MyActivities"), 0, $user->rights->projet->lire);
                $newmenu->add("/projet/tasks.php?action=create&mode=mine", $langs->trans("NewTask"), 1, $user->rights->projet->creer);
                $newmenu->add("/projet/tasks/index.php?mode=mine", $langs->trans("List"), 1, $user->rights->projet->lire);
                $newmenu->add("/projet/activity/list.php?mode=mine", $langs->trans("NewTimeSpent"), 1, $user->rights->projet->creer);

                // All project i have permission on
                $newmenu->add("/projet/activity/index.php", $langs->trans("Activities"), 0, $user->rights->projet->lire && $user->rights->projet->lire);
                $newmenu->add("/projet/tasks.php?action=create", $langs->trans("NewTask"), 1, $user->rights->projet->creer && $user->rights->projet->creer);
                $newmenu->add("/projet/tasks/index.php", $langs->trans("List"), 1, $user->rights->projet->lire && $user->rights->projet->lire);
                $newmenu->add("/projet/activity/list.php", $langs->trans("NewTimeSpent"), 1, $user->rights->projet->creer && $user->rights->projet->creer);
            }
        }

        /*
         * Menu HRM
         */
        if ($mainmenu == 'hrm')
        {
            // Holiday module
            if (!empty($conf->holiday->enabled))
            {
                $langs->load("holiday");

                $newmenu->add("/holiday/index.php?&leftmenu=hrm", $langs->trans("CPTitreMenu"), 0, $user->rights->holiday->write, '', $mainmenu, 'hrm');
                $newmenu->add("/holiday/fiche.php?&action=request", $langs->trans("MenuAddCP"), 1, $user->rights->holiday->write);
                $newmenu->add("/holiday/define_holiday.php?&action=request", $langs->trans("MenuConfCP"), 1, $user->rights->holiday->define_holiday);
                $newmenu->add("/holiday/view_log.php?&action=request", $langs->trans("MenuLogCP"), 1, $user->rights->holiday->view_log);
                $newmenu->add("/holiday/month_report.php?&action=request", $langs->trans("MenuReportMonth"), 1, $user->rights->holiday->month_report);
            }

            // Trips and expenses
            if (!empty($conf->deplacement->enabled))
            {
                $langs->load("trips");
                $newmenu->add("/compta/deplacement/index.php?leftmenu=tripsandexpenses&amp;mainmenu=hrm", $langs->trans("TripsAndExpenses"), 0, $user->rights->deplacement->lire, '', $mainmenu, 'tripsandexpenses');
                $newmenu->add("/compta/deplacement/fiche.php?action=create&amp;leftmenu=tripsandexpenses&amp;mainmenu=hrm", $langs->trans("New"), 1, $user->rights->deplacement->creer);
                $newmenu->add("/compta/deplacement/list.php?leftmenu=tripsandexpenses&amp;mainmenu=hrm", $langs->trans("List"), 1, $user->rights->deplacement->lire);
                $newmenu->add("/compta/deplacement/stats/index.php?leftmenu=tripsandexpenses&amp;mainmenu=hrm", $langs->trans("Statistics"), 1, $user->rights->deplacement->lire);
            }
        }


        /*
         * Menu TOOLS
         */
        if ($mainmenu == 'tools')
        {

            if (!empty($conf->mailing->enabled))
            {
                $langs->load("mails");

                $newmenu->add("/comm/mailing/index.php?leftmenu=mailing", $langs->trans("EMailings"), 0, $user->rights->mailing->lire, '', $mainmenu, 'mailing');
                $newmenu->add("/comm/mailing/fiche.php?leftmenu=mailing&amp;action=create", $langs->trans("NewMailing"), 1, $user->rights->mailing->creer);
                $newmenu->add("/comm/mailing/liste.php?leftmenu=mailing", $langs->trans("List"), 1, $user->rights->mailing->lire);
            }

            if (!empty($conf->export->enabled))
            {
                $langs->load("exports");
                $newmenu->add("/exports/index.php?leftmenu=export", $langs->trans("FormatedExport"), 0, $user->rights->export->lire, '', $mainmenu, 'export');
                $newmenu->add("/exports/export.php?leftmenu=export", $langs->trans("NewExport"), 1, $user->rights->export->creer);
                //$newmenu->add("/exports/export.php?leftmenu=export",$langs->trans("List"),1, $user->rights->export->lire);
            }

            if (!empty($conf->import->enabled))
            {
                $langs->load("exports");
                $newmenu->add("/imports/index.php?leftmenu=import", $langs->trans("FormatedImport"), 0, $user->rights->import->run, '', $mainmenu, 'import');
                $newmenu->add("/imports/import.php?leftmenu=import", $langs->trans("NewImport"), 1, $user->rights->import->run);
            }
        }

        /*
         * Menu MEMBERS
         */
        if ($mainmenu == 'members')
        {
            if (!empty($conf->adherent->enabled))
            {
                $langs->load("members");
                $langs->load("compta");

                $newmenu->add("/adherents/index.php?leftmenu=members&amp;mainmenu=members", $langs->trans("Members"), 0, $user->rights->adherent->lire, '', $mainmenu, 'members');
                $newmenu->add("/adherents/fiche.php?leftmenu=members&amp;action=create", $langs->trans("NewMember"), 1, $user->rights->adherent->creer);
                $newmenu->add("/adherents/liste.php?leftmenu=members", $langs->trans("List"), 1, $user->rights->adherent->lire);
                $newmenu->add("/adherents/liste.php?leftmenu=members&amp;statut=-1", $langs->trans("MenuMembersToValidate"), 2, $user->rights->adherent->lire);
                $newmenu->add("/adherents/liste.php?leftmenu=members&amp;statut=1", $langs->trans("MenuMembersValidated"), 2, $user->rights->adherent->lire);
                $newmenu->add("/adherents/liste.php?leftmenu=members&amp;statut=1&amp;filter=uptodate", $langs->trans("MenuMembersUpToDate"), 2, $user->rights->adherent->lire);
                $newmenu->add("/adherents/liste.php?leftmenu=members&amp;statut=1&amp;filter=outofdate", $langs->trans("MenuMembersNotUpToDate"), 2, $user->rights->adherent->lire);
                $newmenu->add("/adherents/liste.php?leftmenu=members&amp;statut=0", $langs->trans("MenuMembersResiliated"), 2, $user->rights->adherent->lire);
                $newmenu->add("/adherents/stats/geo.php?leftmenu=members&mode=memberbycountry", $langs->trans("MenuMembersStats"), 1, $user->rights->adherent->lire);

                $newmenu->add("/adherents/index.php?leftmenu=members&amp;mainmenu=members", $langs->trans("Subscriptions"), 0, $user->rights->adherent->cotisation->lire);
                $newmenu->add("/adherents/liste.php?leftmenu=members&amp;statut=-1,1&amp;mainmenu=members", $langs->trans("NewSubscription"), 1, $user->rights->adherent->cotisation->creer);
                $newmenu->add("/adherents/cotisations.php?leftmenu=members", $langs->trans("List"), 1, $user->rights->adherent->cotisation->lire);
                $newmenu->add("/adherents/stats/index.php?leftmenu=members", $langs->trans("MenuMembersStats"), 1, $user->rights->adherent->lire);


                if (!empty($conf->categorie->enabled))
                {
                    $langs->load("categories");
                    $newmenu->add("/categories/index.php?leftmenu=cat&amp;type=3", $langs->trans("Categories"), 0, $user->rights->categorie->lire, '', $mainmenu, 'cat');
                    $newmenu->add("/categories/fiche.php?action=create&amp;type=3", $langs->trans("NewCategory"), 1, $user->rights->categorie->creer);
                    //if (empty($leftmenu) || $leftmenu=="cat") $newmenu->add("/categories/liste.php", $langs->trans("List"), 1, $user->rights->categorie->lire);
                }

                $newmenu->add("/adherents/index.php?leftmenu=export&amp;mainmenu=members", $langs->trans("Exports"), 0, $user->rights->adherent->export, '', $mainmenu, 'export');
                if (!empty($conf->export->enabled) && (empty($leftmenu) || $leftmenu == "export"))
                    $newmenu->add("/exports/index.php?leftmenu=export", $langs->trans("Datas"), 1, $user->rights->adherent->export);
                if (empty($leftmenu) || $leftmenu == "export")
                    $newmenu->add("/adherents/htpasswd.php?leftmenu=export", $langs->trans("Filehtpasswd"), 1, $user->rights->adherent->export);
                if (empty($leftmenu) || $leftmenu == "export")
                    $newmenu->add("/adherents/cartes/carte.php?leftmenu=export", $langs->trans("MembersCards"), 1, $user->rights->adherent->export);

                // Type
                $newmenu->add("/adherents/type.php?leftmenu=setup&amp;mainmenu=members", $langs->trans("MembersTypes"), 0, $user->rights->adherent->configurer, '', $mainmenu, 'setup');
                $newmenu->add("/adherents/type.php?leftmenu=setup&amp;mainmenu=members&amp;action=create", $langs->trans("New"), 1, $user->rights->adherent->configurer);
                $newmenu->add("/adherents/type.php?leftmenu=setup&amp;mainmenu=members", $langs->trans("List"), 1, $user->rights->adherent->configurer);
            }
        }

        // Add personalized menus and modules menus
        $menuArbo = new Menubase($db, 'eldy');
        $newmenu = $menuArbo->menuLeftCharger($newmenu, $mainmenu, $leftmenu, (empty($user->societe_id) ? 0 : 1), 'eldy', $tabMenu);

        // We update newmenu for special dynamic menus
        if (!empty($user->rights->banque->lire) && $mainmenu == 'bank')
        { // Entry for each bank account
            $sql = "SELECT rowid, label, courant, rappro, courant";
            $sql.= " FROM " . MAIN_DB_PREFIX . "bank_account";
            $sql.= " WHERE entity = " . $conf->entity;
            $sql.= " AND clos = 0";
            $sql.= " ORDER BY label";

            $resql = $db->query($sql);
            if ($resql)
            {
                $numr = $db->num_rows($resql);
                $i = 0;

                if ($numr > 0)
                    $newmenu->add('/compta/bank/index.php', $langs->trans("BankAccounts"), 0, $user->rights->banque->lire);

                while ($i < $numr)
                {
                    $objp = $db->fetch_object($resql);
                    $newmenu->add('/compta/bank/fiche.php?id=' . $objp->rowid, $objp->label, 1, $user->rights->banque->lire);
                    if ($objp->rappro && $objp->courant != 2 && empty($objp->clos))
                    {  // If not cash account and not closed and can be reconciliate
                        $newmenu->add('/compta/bank/rappro.php?account=' . $objp->rowid, $langs->trans("Conciliate"), 2, $user->rights->banque->consolidate);
                    }
                    $i++;
                }
            } else
                dol_print_error($db);
            $db->free($resql);
        }
        if (!empty($conf->ftp->enabled) && $mainmenu == 'ftp')
        { // Entry for FTP
            $MAXFTP = 20;
            $i = 1;
            while ($i <= $MAXFTP)
            {
                $paramkey = 'FTP_NAME_' . $i;
                //print $paramkey;
                if (!empty($conf->global->$paramkey))
                {
                    $link = "/ftp/index.php?idmenu=" . $_SESSION["idmenu"] . "&numero_ftp=" . $i;

                    $newmenu->add($link, dol_trunc($conf->global->$paramkey, 24));
                }
                $i++;
            }
        }
    }


    // Build final $menu_array = $menu_array_before +$newmenu->liste + $menu_array_after
    //var_dump($menu_array_before);exit;
    //var_dump($menu_array_after);exit;
    $menu_array = $newmenu->liste;
    if (is_array($menu_array_before))
        $menu_array = array_merge($menu_array_before, $menu_array);
    if (is_array($menu_array_after))
        $menu_array = array_merge($menu_array, $menu_array_after);
    //var_dump($menu_array);exit;
    if (!is_array($menu_array))
        return 0;

    // Show menu
    $invert = empty($conf->global->MAIN_MENU_INVERT) ? "" : "invert";
    if (empty($noout))
    {
        $alt = 0;
        $blockvmenuopened = false;
        $num = count($menu_array);
        for ($i = 0; $i < $num; $i++)
        {
            $showmenu = true;
            if (!empty($conf->global->MAIN_MENU_HIDE_UNAUTHORIZED) && empty($menu_array[$i]['enabled']))
                $showmenu = false;

            $alt++;
            if (empty($menu_array[$i]['level']) && $showmenu)
            {
                $blockvmenuopened = true;
                if (($alt % 2 == 0))
                {
                    print '<div class="blockvmenuimpair' . $invert . '">' . "\n";
                } else
                {
                    print '<div class="blockvmenupair' . $invert . '">' . "\n";
                }
            }

            // Place tabulation
            $tabstring = '';
            $tabul = ($menu_array[$i]['level'] - 1);
            if ($tabul > 0)
            {
                for ($j = 0; $j < $tabul; $j++)
                {
                    $tabstring.='&nbsp; &nbsp; &nbsp;';
                }
            }

            // For external modules
            $tmp = explode('?', $menu_array[$i]['url'], 2);
            $url = $tmp[0];
            $param = (isset($tmp[1]) ? $tmp[1] : '');
            $url = dol_buildpath($url, 1) . ($param ? '?' . $param : '');

            $url = preg_replace('/__LOGIN__/', $user->login, $url);
            $url = preg_replace('/__USERID__/', $user->id, $url);

            print '<!-- Process menu entry with mainmenu=' . $menu_array[$i]['mainmenu'] . ', leftmenu=' . $menu_array[$i]['leftmenu'] . ', level=' . $menu_array[$i]['level'] . ' enabled=' . $menu_array[$i]['enabled'] . ' -->' . "\n";

            // Menu niveau 0
            if ($menu_array[$i]['level'] == 0)
            { // menu, viene disegnato la stringa "prodotti" con link
                if ($menu_array[$i]['enabled'])
                {
                    print '<div class="menu_titre">' . $tabstring . '<a class="vmenu" href="' . $url . '"' . ($menu_array[$i]['target'] ? ' target="' . $menu_array[$i]['target'] . '"' : '') . '>' . $menu_array[$i]['titre'] . '</a></div>' . "\n";
                } else if ($showmenu)
                {
                    print '<div class="menu_titre">' . $tabstring . '<font class="vmenudisabled">' . $menu_array[$i]['titre'] . '</font></div>' . "\n";
                }
                if ($showmenu)
                    print '<div class="menu_top"></div>' . "\n";
            }

            // Menu niveau > 0
            if ($menu_array[$i]['level'] > 0)
            {
                if ($menu_array[$i]['enabled'])
                {
                    $menu_target = $menu_array[$i]['target'];
                    $menu_titre = $menu_array[$i]['titre'];
                    $menu_url = $menu_array[$i]['url'];
                    print '<div class="menu_contenu">' . $tabstring;
                    if ($menu_array[$i]['url'])
                        print '<a class="vsmenu" href="' . $url . '"' . ($menu_target ? ' target="' . $menu_target . '"' : '') . '>';
                    print $menu_titre;
                    if ($menu_url)
                        print '</a>';
                    // If title is not pure text and contains a table, no carriage return added
                    if (!strstr($menu_titre, '<table'))
                        print '<br>';
                    print '</div>' . "\n";
                }
                else if ($showmenu)
                {
                    print '<div class="menu_contenu">' . $tabstring . '<font class="vsmenudisabled vsmenudisabledmargin">' . $menu_array[$i]['titre'] . '</font><br></div>' . "\n";
                }
            }

            // If next is a new block or if there is nothing after
            if (empty($menu_array[$i + 1]['level']))
            {
                if ($showmenu)
                    print '<div class="menu_end"></div>' . "\n";
                if ($blockvmenuopened)
                {
                    print "</div>\n";
                    $blockvmenuopened = false;
                }
            }
        }
    }

    return count($menu_array);
}

/**
 * Function to test if an entry is enabled or not
 *
 * @param	string		$type_user					0=We need backoffice menu, 1=We need frontoffice menu
 * @param	array		$menuentry					Array for menu entry
 * @param	array		$listofmodulesforexternal	Array with list of modules allowed to external users
 * @return	int										0=Hide, 1=Show, 2=Show gray
 */
function dol_eldy_showmenu($type_user, &$menuentry, &$listofmodulesforexternal)
{
    global $conf;

    //print 'type_user='.$type_user.' module='.$menuentry['module'].' enabled='.$menuentry['enabled'].' perms='.$menuentry['perms'];
    //print 'ok='.in_array($menuentry['module'], $listofmodulesforexternal);
    if (empty($menuentry['enabled']))
        return 0; // Entry disabled by condition
    if ($type_user && $menuentry['module'])
    {
        $tmploops = explode('|', $menuentry['module']);
        $found = 0;
        foreach ($tmploops as $tmploop)
        {
            if (in_array($tmploop, $listofmodulesforexternal))
            {
                $found++;
                break;
            }
        }
        if (!$found)
            return 0; // Entry is for menus all excluded to external users
    }
    if (!$menuentry['perms'] && $type_user)
        return 0;            // No permissions and user is external
    if (!$menuentry['perms'] && !empty($conf->global->MAIN_MENU_HIDE_UNAUTHORIZED))
        return 0; // No permissions and option to hide when not allowed, even for internal user, is on
    if (!$menuentry['perms'])
        return 2;               // No permissions and user is external
    return 1;
}
