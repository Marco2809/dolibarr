<?php

class mymoduli
{

    private $root = null;
    private $array_home = null;
    private $array_company = null;
    private $array_mod_magazzino = null;
    private $array_commerciale = null;
    private $array_fatturazione = null;
    private $array_cassa = null;
    private $array_commesse = null;
    private $array_hr = null;
    private $array_strumenti = null;
    private $array_membri = null;
    private $array_documenti = null;
    private $array_ord_giorno = null;

    public function __construct($root) // percorso root (doll_...ecc.)
    {
        $this->root = $root;
    }

    public function getModuloHome()
    {
        if (is_null($this->array_home))
        {
            $array_impostazioni = array();
            $array_impostazioni['impostazioni'] = $this->root . "/admin/index.php?mainmenu=home&leftmenu=setup";
            $array_impostazioni['soc_fondazione'] = $this->root . "/admin/company.php?mainmenu=home";
            $array_impostazioni['moduli'] = $this->root . "/admin/modules.php?mainmenu=home";
            $array_impostazioni['menu'] = $this->root . "/admin/menus.php?mainmenu=home";
            $array_impostazioni['layout_view'] = $this->root . "/admin/ihm.php?mainmenu=home";
            $array_impostazioni['traduzione'] = $this->root . "/admin/translation.php?mainmenu=home";
            $array_impostazioni['caselle_rias'] = $this->root . "/admin/boxes.php?mainmenu=home";
            $array_impostazioni['avvisi_segnalazioni'] = $this->root . "/admin/delais.php?mainmenu=home";
            $array_impostazioni['sicurezza'] = $this->root . "/admin/proxy.php?mainmenu=home";
            $array_impostazioni['limiti_precisione'] = $this->root . "/admin/limits.php?mainmenu=home";
            $array_impostazioni['pdf'] = $this->root . "/admin/pdf.php?mainmenu=home";
            $array_impostazioni['email'] = $this->root . "/admin/mails.php?mainmenu=home";
            $array_impostazioni['sms'] = $this->root . "/admin/sms.php?mainmenu=home";
            $array_impostazioni['dictionaries'] = $this->root . "/admin/dict.php?mainmenu=home";
            $array_impostazioni['altre_impostazioni'] = $this->root . "/admin/const.php?mainmenu=home";

            $array_strumenti_gestione = array();
            $array_strumenti_gestione['strumenti_gestione'] = $this->root . "/admin/tools/index.php?mainmenu=home&leftmenu=admintools";
            $array_strumenti_gestione['info_dolibarr'] = $this->root . "/admin/system/dolibarr.php?mainmenu=home&leftmenu=admintools_info";
            $array_strumenti_gestione['info_os'] = $this->root . "/admin/system/os.php?mainmenu=home&leftmenu=admintools";
            $array_strumenti_gestione['info_web_server'] = $this->root . "/admin/system/web.php?mainmenu=home&leftmenu=admintools";
            $array_strumenti_gestione['info_php'] = $this->root . "/admin/system/phpinfo.php?mainmenu=home&leftmenu=admintools";
            $array_strumenti_gestione['info_db'] = $this->root . "/admin/system/database.php?mainmenu=home&leftmenu=admintools";
            $array_strumenti_gestione['pulizia'] = $this->root . "/admin/tools/purge.php?mainmenu=home&leftmenu=admintools";
            $array_strumenti_gestione['bak'] = $this->root . "/admin/tools/dolibarr_export.php?mainmenu=home&leftmenu=admintools";
            $array_strumenti_gestione['ripristino'] = $this->root . "/admin/tools/dolibarr_import.php?mainmenu=home&leftmenu=admintools";
            $array_strumenti_gestione['migliora_estendi'] = $this->root . "/admin/tools/update.php?mainmenu=home&leftmenu=admintools";
            $array_strumenti_gestione['audit'] = $this->root . "/admin/tools/listevents.php?mainmenu=home&leftmenu=admintools";
            $array_strumenti_gestione['sessione_utente'] = $this->root . "/admin/tools/listsessions.php?mainmenu=home&leftmenu=admintools";
            $array_strumenti_gestione['about'] = $this->root . "/admin/system/about.php?mainmenu=home&leftmenu=admintools";
            $array_strumenti_gestione['centro_assistenza'] = $this->root . "/support/index.php?mainmenu=home&leftmenu=admintools";

            $array_strumenti_moduli = array();
            $array_strumenti_moduli['strumenti_moduli'] = $this->root . "/admin/tools/index.php?mainmenu=home&leftmenu=modulesadmintools";
            $array_strumenti_moduli['mass_vat'] = $this->root . "/product/admin/product_tools.php?mainmenu=home&leftmenu=modulesadmintools";

            $array_gruppi_utenti = array();
            $array_gruppi_utenti['utenti_gruppi'] = $this->root . "/user/home.php?mainmenu=home&leftmenu=users";
            $array_gruppi_utenti['utenti'] = $this->root . "/user/index.php?mainmenu=home";
            $array_gruppi_utenti['nuovo_utente'] = $this->root . "/user/fiche.php?mainmenu=home&action=create";
            $array_gruppi_utenti['gruppi'] = $this->root . "/user/group/index.php?mainmenu=home";
            $array_gruppi_utenti['nuovi_gruppi'] = $this->root . "/user/group/fiche.php?mainmenu=home&action=create";

            $array_merge = array();
            $array_merge['impostazioni'] = $array_impostazioni;
            $array_merge['strumenti_gestione'] = $array_strumenti_gestione;
            $array_merge['strumenti_moduli'] = $array_strumenti_moduli;
            $array_merge['utenti_gruppi'] = $array_gruppi_utenti;

            $this->array_home = $array_merge;
        }
        return $this->array_home;
    }

    public function getCompanySoggetti()
    {
        if (is_null($this->array_company))
        {
            $array_company = array();
            $array_company['company'] = $this->root . "/societe/index.php?mainmenu=companies&leftmenu=thirdparties";
            $array_company['nuovo'] = $this->root . "/societe/soc.php?mainmenu=companies&action=create";
            $array_company['elenco_clienti_potenziali'] = $this->root . "/comm/prospect/list.php?mainmenu=companies&leftmenu=prospects";
            $array_company['nuovo_cliente_potenziale'] = $this->root . "/societe/soc.php?mainmenu=companies&leftmenu=prospects&action=create&type=p";
            $array_company['elenco_clienti'] = $this->root . "/comm/list.php?mainmenu=companies&leftmenu=customers";
            $array_company['nuovo_cliente'] = $this->root . "/societe/soc.php?mainmenu=companies&leftmenu=customers&action=create&type=c";
            $array_company['elenco_fornitori'] = $this->root . "/fourn/liste.php?mainmenu=companies&leftmenu=suppliers";
            $array_company['nuovo_fornitore'] = $this->root . "/societe/soc.php?mainmenu=companies&leftmenu=suppliers&action=create&type=f";

            $array_contatti = array();
            $array_contatti['contatti'] = $this->root . "/contact/list.php?mainmenu=companies&leftmenu=contacts";
            $array_contatti['nuovo_contatto'] = $this->root . "/contact/fiche.php?mainmenu=companies&leftmenu=contacts&action=create";
            $array_contatti['elenco'] = $this->root . "/contact/list.php?mainmenu=companies&leftmenu=contacts&type=c";
            $array_contatti['elenco_clienti_pot'] = $this->root . "/contact/list.php?mainmenu=companies&leftmenu=contacts&type=p";
            $array_contatti['elenco_clienti'] = $this->root . "/contact/list.php?mainmenu=companies&leftmenu=contacts&type=c";
            $array_contatti['elenco_fornitori'] = $this->root . "/contact/list.php?mainmenu=companies&leftmenu=contacts&type=f";
            $array_contatti['elenco_altri'] = $this->root . "/contact/list.php?mainmenu=companies&leftmenu=contacts&type=o";

            $array_categorie_clienti = array();
            $array_categorie_clienti['categorie_clienti'] = $this->root . "/categories/index.php?mainmenu=companies&leftmenu=cat&type=2";
            $array_categorie_clienti['nuova_categoria'] = $this->root . "/categories/fiche.php?mainmenu=companies&action=create&type=2";

            $array_categorie_contratti = array();
            $array_categorie_contratti['categorie_contatti'] = $this->root . "/categories/index.php?mainmenu=companies&leftmenu=cat&type=4";
            $array_categorie_contratti['nuova_categoria'] = $this->root . "/categories/fiche.php?mainmenu=companies&action=create&type=4";


            $array_categorie_fornitori = array();
            $array_categorie_fornitori['categorie_fornitori'] = $this->root . "/categories/index.php?mainmenu=companies&leftmenu=cat&type=1";
            $array_categorie_fornitori['nuova_categoria'] = $this->root . "/categories/fiche.php?mainmenu=companies&action=create&type=1";

            $merge_array = array();
            $merge_array['company'] = $array_company;
            $merge_array['contatti'] = $array_contatti;
            $merge_array['categorie_clienti'] = $array_categorie_clienti;
            $merge_array['categorie_contatti'] = $array_categorie_contratti;
            $merge_array['categorie_fornitori'] = $array_categorie_fornitori;


            $this->array_company = $merge_array;
        }
        return $this->array_company;
    }

    public function getModuloProdotti()
    {
        if (is_null($this->array_mod_magazzino))
        {
            $array_prodotti = array();
            $array_prodotti['carica_matricole'] = $this->root . "/product/script_magazzino.php?mainmenu=products&leftmenu=product";
            $array_prodotti['carica_matricole_old'] = $this->root . "/product/script_magazzino_old.php?mainmenu=products&leftmenu=product";
            $array_prodotti['cambia_magazzino'] = $this->root . "/product/script_movimentazione.php?mainmenu=products&leftmenu=product";
            $array_prodotti['cambia_magazzino_old'] = $this->root . "/product/script_movimentazione_old.php?mainmenu=products&leftmenu=product";
            $array_prodotti['ricerca_asset'] = $this->root . "/product/ricerca_asset.php?mainmenu=products&leftmenu=product";
            $array_prodotti['gestione_tecnici'] = $this->root . "/product/gestione_tecnici.php?mainmenu=products&leftmenu=product";
            $array_prodotti['caricamento_ticket'] = $this->root . "/product/load_ticket.php?mainmenu=products&leftmenu=product";
            $array_prodotti['caricamento_magazzino'] = $this->root . "/product/load_mag.php?mainmenu=products&leftmenu=product";
            //$array_prodotti['prodotti'] = $this->root . "/index.php?mainmenu=products&leftmenu=product&type=0";
            //$array_prodotti['nuovo_prodotto'] = $this->root . "/product/fiche.php?mainmenu=products&leftmenu=product&action=create&type=0";
            //$array_prodotti['famiglia'] = $this->root . "/product/fiche.php?mainmenu=products&leftmenu=family&action=create&type=2";

            //$array_prodotti['elenco'] = $this->root . "/product/liste.php?mainmenu=products&leftmenu=product&type=0";
            //$array_prodotti['statistiche'] = $this->root . "/product/popuprop.php?mainmenu=products&leftmenu=stats&type=0";
            //$array_prodotti['scorte'] = $this->root . "/product/reassort.php?mainmenu=products&type=0";

            $array_famiglie = array();
            //$array_famiglie['famiglia'] = $this->root . "/product/elenco_famiglia.php?leftmenu=product&type=2";
            //$array_famiglie['nuova_famiglia'] = $this->root . "/product/fiche.php?mainmenu=products&leftmenu=family&action=create&type=2";
            //$array_famiglie['cerca_famiglia'] = $this->root . "/product/lista_famiglia.php?leftmenu=product&type=2";
            //$array_famiglie['elenco_famiglia'] = $this->root . "/product/elenco_famiglia.php?leftmenu=product&type=2";

            $array_asset = array();
            //$array_asset['asset'] = $this->root . "/product/elenco_asset.php?mainmenu=products&leftmenu=product&type=4";
            //$array_asset['crea_asset'] = $this->root . "/product/crea_asset.php?mainmenu=products&leftmenu=family&action=create&type=3";
            //$array_asset['elenco_asset'] = $this->root . "/product/elenco_asset.php?mainmenu=products&leftmenu=product&type=4";
            //$array_asset['massivo'] = $this->root . "/product/asset_massivo.php?mainmenu=products&leftmenu=family&action=create&type=3";

            $array_magazzini = array();
            //$array_magazzini['scorte'] = $this->root . "/product/stock/index.php?mainmenu=products&leftmenu=stock";
            //$array_magazzini['elenco'] = $this->root . "/product/stock/liste.php?mainmenu=products";
            //$array_magazzini['incremento'] = $this->root . "/product/stock/valo.php?mainmenu=products";
            //$array_magazzini['movimenti'] = $this->root . "/product/stock/mouvement.php?mainmenu=products";
            //$array_magazzini['nuova_movimentazione'] = $this->root . "/product/stock/massstockmove.php?mainmenu=products&";
            //$array_magazzini['movimenti_prodotti'] = $this->root . "/product/stock/mouvement.php?mainmenu=products&";
            //$array_magazzini['mov_sottoscorte'] = $this->root . "/product/stock/replenish.php?mainmenu=products&";




            //$array_magazzini['dotazione'] = $this->root . "/product/stock/fiche.php?id=";

            //Intervento
            //$array_intervento = array();
           // $array_intervento['nuovo_intervento'] = $this->root . "/product/nuovo_intervento.php?mainmenu=products&leftmenu=product&type=5";
            //$array_intervento['storico_interventi'] = $this->root . "/product/storico_intervento.php?mainmenu=products&leftmenu=product&type=5";
            //$array_intervento['aggiungi_anagrafica'] = $this->root . "/product/aggiungi_anagrafica.php?mainmenu=products&leftmenu=product&type=5";
           // $array_intervento['elenco_clienti'] = $this->root . "/product/elenco_clienti.php?mainmenu=products&leftmenu=product&type=5";


            //movimentazioni
            $array_movimentazioni = array();
            //$array_movimentazioni['movimenti'] = $this->root . "/product/movimentazione.php?mainmenu=products&leftmenu=product&type=5";
            //$array_movimentazioni['nuova_mov'] = $this->root . "/product/movimentazione.php?mainmenu=products&leftmenu=product&type=5";
            //$array_movimentazioni['da_convalidare'] = $this->root . "/product/daconvalidare.php?mainmenu=products&leftmenu=product&type=6&id=3";
            //$array_movimentazioni['in_transito'] = $this->root . "/product/transito.php?mainmenu=products&leftmenu=product&type=6&id=3";

            //$array_movimentazioni['storico'] = $this->root . "/product/storico.php?mainmenu=products&leftmenu=product&type=6&id=4";

            $merge_array = array();
            $merge_array['modulo_prodotti'] = $array_prodotti;
            $merge_array['modulo_famiglia'] = $array_famiglie;
            $merge_array['modulo_asset'] = $array_asset;

            $merge_array['intervento'] = $array_intervento;
            $merge_array['movimentazione'] = $array_movimentazioni;
            $merge_array['magazzini'] = $array_magazzini;

            $this->array_mod_magazzino = $merge_array;
        }
        return $this->array_mod_magazzino;
    }

    public function getModuloCommerciale()
    {
        if (is_null($this->array_commerciale))
        {
            //visibili al magazzino o comunque ad utenti con certi privileggi
            $array_preventivi_comm = array();
            $array_preventivi_comm['preventivi_com'] = $this->root . "/comm/propal/index.php?mainmenu=commercial&leftmenu=propals";
            $array_preventivi_comm['nuova_proposta'] = $this->root . "/comm/propal.php?action=create&mainmenu=commercial&leftmenu=propals";
            $array_preventivi_comm['elenco'] = $this->root . "/comm/propal/list.php?mainmenu=commercial&leftmenu=propals";
            $array_preventivi_comm['statistiche'] = $this->root . "/comm/propal/stats/index.php?mainmenu=commercial&leftmenu=propals";

            $array_ordini_clienti = array();
            $array_ordini_clienti['ordini_clienti'] = $this->root . "/commande/index.php?mainmenu=commercial&leftmenu=orders";
            $array_ordini_clienti['nuovo_ordine'] = $this->root . "/commande/fiche.php?action=create&mainmenu=commercial&leftmenu=orders";
            $array_ordini_clienti['elenco'] = $this->root . "/commande/liste.php?mainmenu=commercial&leftmenu=orders";
            $array_ordini_clienti['statistiche'] = $this->root . "/commande/stats/index.php?mainmenu=commercial&leftmenu=orders";

            $array_ordini_fornitori = array();
            $array_ordini_fornitori['ordini_fornitori'] = $this->root . "/fourn/commande/index.php?mainmenu=commercial&leftmenu=orders_suppliers";
            $array_ordini_fornitori['nuovo_ordine'] = $this->root . "/fourn/commande/fiche.php?action=create&mainmenu=commercial&leftmenu=orders_suppliers";
            $array_ordini_fornitori['elenco'] = $this->root . "/fourn/commande/liste.php?mainmenu=commercial&leftmenu=orders_suppliers";
            $array_ordini_fornitori['statistiche'] = $this->root . "/commande/stats/index.php?mainmenu=commercial&leftmenu=orders_suppliers&mode=supplier";

            //moduli visibili solo ad admin
            $array_contratti = array();
            $array_contratti['contratti'] = $this->root . "/contrat/index.php?mainmenu=commercial&leftmenu=contracts";
            $array_contratti['nuovo_contratto'] = $this->root . "/contrat/fiche.php?&action=create&mainmenu=commercial&leftmenu=contracts";
            $array_contratti['elenco'] = $this->root . "/contrat/liste.php?mainmenu=commercial&leftmenu=contracts";
            $array_contratti['servizi'] = $this->root . "/contrat/services.php?mainmenu=commercial&leftmenu=contracts";

            $array_interventi = array();
            $array_interventi['interventi'] = $this->root . "/fichinter/list.php?mainmenu=commercial&leftmenu=ficheinter";
            $array_interventi['nuovo_intervento'] = $this->root . "/fichinter/fiche.php?action=create&mainmenu=commercial&leftmenu=ficheinter";
            $array_interventi['elenco'] = $this->root . "/fichinter/list.php?mainmenu=commercial&leftmenu=ficheinter";

            $merge_commerciale = array();
            $merge_commerciale['preventivi_com'] = $array_preventivi_comm;
            $merge_commerciale['ordini_clienti'] = $array_ordini_clienti;
            $merge_commerciale['ordini_fornitori'] = $array_ordini_fornitori;
            $merge_commerciale['contratti'] = $array_contratti;
            $merge_commerciale['interventi'] = $array_interventi;
            $this->array_commerciale = $merge_commerciale;
        }
        return $this->array_commerciale;
    }

    public function getModuloFatturazione()
    {
        if (is_null($this->array_fatturazione))
        {
            $array_fatture_attive = array();
            $array_fatture_attive['controllo_ticket_doppi'] = $this->root . "/product/script_interventi.php?mainmenu=accountancy&leftmenu=customers_bills";
            $array_fatture_attive['controllo_ticket'] = $this->root . "/product/script_control.php?mainmenu=accountancy&leftmenu=customers_bills";
            $array_fatture_attive['controllo_ticket_tml'] = $this->root . "/product/script_control_tml.php?mainmenu=accountancy&leftmenu=customers_bills";
            $array_fatture_attive['genera_fatture'] = $this->root . "/product/script_fatture.php?mainmenu=accountancy&leftmenu=customers_bills";
            $array_fatture_attive['fast_facture'] = $this->root . "/product/script_fast_facture.php?mainmenu=accountancy&leftmenu=customers_bills";
            $array_fatture_attive['prima_nota'] = $this->root . "/compta/prima_nota.php?mainmenu=accountancy&leftmenu=customers_bills";
            $array_fatture_attive['fatture_attive'] = $this->root . "/compta/facture/list.php?mainmenu=accountancy&leftmenu=customers_bills";
            $array_fatture_attive['nuova_fattura'] = $this->root . "/compta/facture.php?mainmenu=accountancy&action=create&leftmenu=customers_bills";
            $array_fatture_attive['ripetibili'] = $this->root . "/compta/facture/fiche-rec.php?mainmenu=accountancy&leftmenu=customers_bills";
            $array_fatture_attive['non_pagato'] = $this->root . "/compta/facture/impayees.php?mainmenu=accountancy&leftmenu=customers_bills";
            $array_fatture_attive['pagamenti'] = $this->root . "/compta/paiement/liste.php?mainmenu=accountancy&leftmenu=customers_bills_payments";
            $array_fatture_attive['reportistiche'] = $this->root . "/compta/paiement/rapport.php?mainmenu=accountancy&leftmenu=customers_bills_payments";
            $array_fatture_attive['statisitiche'] = $this->root . "/compta/facture/stats/index.php?mainmenu=accountancy&leftmenu=customers_bills";

            $array_fatture_passive = array();
            $array_fatture_passive['fatture_passive'] = $this->root . "/fourn/facture/list.php?mainmenu=accountancy&leftmenu=suppliers_bills";
            $array_fatture_passive['nuova_fattura'] = $this->root . "/fourn/facture/fiche.php?mainmenu=accountancy&action=create";
            $array_fatture_passive['non_pagato'] = $this->root . "/fourn/facture/impayees.php?mainmenu=accountancy";
            $array_fatture_passive['pagamenti'] = $this->root . "/fourn/facture/paiement.php?mainmenu=accountancy";
            $array_fatture_passive['statistiche'] = $this->root . "/compta/facture/stats/index.php?mainmenu=accountancy&leftmenu=suppliers_bills&mode=supplier";

            $merge_array = array();
            $merge_array['fatture_attive'] = $array_fatture_attive;
            $merge_array['fatture_passive'] = $array_fatture_passive;

            $this->array_fatturazione = $merge_array;
        }
        return $this->array_fatturazione;
    }

    public function getModuloCassa()
    {
        if (is_null($this->array_cassa))
        {
            $array_banca_cassa = array();
            $array_banca_cassa['banca_cassa'] = $this->root . "/compta/bank/index.php?leftmenu=bank&mainmenu=bank";
            $array_banca_cassa['nuovo_conto_finanziario'] = $this->root . "/compta/bank/fiche.php?action=create&mainmenu=bank";
            $array_banca_cassa['categorie'] = $this->root . "/compta/bank/categ.php?mainmenu=bank";
            $array_banca_cassa['elenco_transazioni'] = $this->root . "/compta/bank/search.php?mainmenu=bank";
            $array_banca_cassa['elenco_transazioni_categoria'] = $this->root . "/compta/bank/budget.php?mainmenu=bank";
            $array_banca_cassa['bonifici_giroconti'] = $this->root . "/compta/bank/virement.php?mainmenu=bank";

            $array_depositi_assegni = array();
            $array_depositi_assegni['depositi_assegni'] = $this->root . "/compta/paiement/cheque/index.php?leftmenu=checks&mainmenu=bank";
            $array_depositi_assegni['nuovo_depositi'] = $this->root . "/compta/paiement/cheque/fiche.php?leftmenu=checks&action=new&mainmenu=bank";
            $array_depositi_assegni['elenco'] = $this->root . "/compta/paiement/cheque/liste.php?leftmenu=checks&mainmenu=bank";

            $array_merge = array();
            $array_merge['banca_cassa'] = $array_banca_cassa;
            $array_merge['depositi_assegni'] = $array_depositi_assegni;
            $this->array_cassa = $array_merge;
        }
        return $this->array_cassa;
    }

    public function getModuloCommesse()
    {
        if (is_null($this->array_commesse))
        {
            $array_miei_progetti = array();
            $array_miei_progetti['miei_progetti'] = $this->root . "/projet/index.php?mainmenu=project&leftmenu=projects&mode=mine";
            $array_miei_progetti['nuovo_progetto'] = $this->root . "/projet/fiche.php?mainmenu=project&leftmenu=projects&action=create&mode=mine";
            $array_miei_progetti['elenco'] = $this->root . "/projet/liste.php?mainmenu=project&leftmenu=projects&mode=mine";

            $array_progetti = array();
            $array_progetti['progetti'] = $this->root . "/projet/index.php?mainmenu=project&leftmenu=projects";
            $array_progetti['nuovi_progetti'] = $this->root . "/projet/fiche.php?mainmenu=project&leftmenu=projects&action=create";
            $array_progetti['elenco'] = $this->root . "/projet/liste.php?mainmenu=project&leftmenu=projects";

            $array_miei_compiti = array();
            $array_miei_compiti['miei_compiti'] = $this->root . "/projet/activity/index.php?mainmenu=project&mode=mine";
            $array_miei_compiti['nuovo_compito'] = $this->root . "/projet/tasks.php?mainmenu=project&action=create&mode=mine";
            $array_miei_compiti['elenco'] = $this->root . "/projet/tasks/index.php?mainmenu=project&mode=mine";
            $array_miei_compiti['aggiungi_tempo_lavorato'] = $this->root . "/projet/activity/list.php?mainmenu=project&mode=mine";

            $array_compiti = array();
            $array_compiti['compiti'] = $this->root . "/projet/activity/index.php?mainmenu=project";
            $array_compiti['nuovo_compito'] = $this->root . "/projet/tasks.php?mainmenu=project&action=create";
            $array_compiti['elenco'] = $this->root . "/projet/tasks/index.php?mainmenu=project";
            $array_compiti['aggiungi_tempo_lavorato'] = $this->root . "/projet/activity/list.php?mainmenu=project";

            $array_merge = array();
            $array_merge['miei_progetti'] = $array_miei_progetti;
            $array_merge['progetti'] = $array_progetti;
            $array_merge['miei_compiti'] = $array_miei_compiti;
            $array_merge['compiti'] = $array_compiti;
            $this->array_commesse = $array_merge;
        }
        return $this->array_commesse;
    }

    public function getModuloHR()
    {
        if (is_null($this->array_hr))
        {
            $array_ferie = array();
            $array_ferie['ferie'] = $this->root . "/holiday/index.php?mainmenu=hrm&leftmenu=hrm";
            $array_ferie['richiedi_ferie'] = $this->root . "/holiday/fiche.php?&action=request&mainmenu=hrm";
            $array_ferie['modifica_ferie'] = $this->root . "/holiday/define_holiday.php?&action=request&mainmenu=hrm";
            $array_ferie['storico_ferie'] = $this->root . "/holiday/view_log.php?&action=request&mainmenu=hrm";
            $array_ferie['estratto_conto'] = $this->root . "/holiday/month_report.php?&action=request&mainmenu=hrm";

            $array_viaggi_spese = array();
            $array_viaggi_spese['viaggi_spese'] = $this->root . "/compta/deplacement/index.php?leftmenu=tripsandexpenses&mainmenu=hrm";
            $array_viaggi_spese['nuovo'] = $this->root . "/compta/deplacement/fiche.php?action=create&leftmenu=tripsandexpenses&mainmenu=hrm";
            $array_viaggi_spese['elenco'] = $this->root . "/compta/deplacement/list.php?leftmenu=tripsandexpenses&mainmenu=hrm";
            $array_viaggi_spese['statistiche'] = $this->root . "/compta/deplacement/stats/index.php?leftmenu=tripsandexpenses&mainmenu=hrm";

            $merge_array_hr = array();
            $merge_array_hr['ferie'] = $array_ferie;
            $merge_array_hr['viaggi_spese'] = $array_viaggi_spese;

            $this->array_hr = $merge_array_hr;
        }
        return $this->array_hr;
    }

    public function getModuloStrumenti()
    {
        if (is_null($this->array_strumenti))
        {
            $array_invio_email = array();
            $array_invio_email['invio_email'] = $this->root . "/comm/mailing/index.php?mainmenu=tools&leftmenu=mailing";
            $array_invio_email['invio_massa'] = $this->root . "/comm/mailing/fiche.php?mainmenu=tools&leftmenu=mailing&action=create";
            $array_invio_email['elenco'] = $this->root . "/comm/mailing/liste.php?mainmenu=tools&leftmenu=mailing";

            $array_export_assistita = array();
            $array_export_assistita['esportazione_assistita'] = $this->root . "/exports/index.php?mainmenu=tools&leftmenu=export";
            $array_export_assistita['nuova_esportazione'] = $this->root . "/exports/export.php?mainmenu=tools&leftmenu=export";

            $array_importazione_assistita = array();
            $array_importazione_assistita['importazione_assistita'] = $this->root . "/imports/index.php?mainmenu=tools&leftmenu=import";
            $array_importazione_assistita['nuova_importazione'] = $this->root . "/imports/import.php?mainmenu=tools&leftmenu=import";

            $array_sondaggio = array();
            $array_sondaggio['sondaggio'] = $this->root . "/opensurvey/index.php?mainmenu=tools&leftmenu=opensurvey&idmenu=49";
            $array_sondaggio['nuovo_sondaggio'] = $this->root . "/opensurvey/wizard/index.php?idmenu=50&mainmenu=tools";
            $array_sondaggio['elenco'] = $this->root . "/opensurvey/list.php?idmenu=51&mainmenu=tools";

            $merge_strumenti = array();
            $merge_strumenti['invio_email'] = $array_invio_email;
            $merge_strumenti['esportazione_assistita'] = $array_export_assistita;
            $merge_strumenti['importazione_assistita'] = $array_importazione_assistita;
            $merge_strumenti['sondaggio'] = $array_sondaggio;
            $this->array_strumenti = $merge_strumenti;
        }
        return $this->array_strumenti;
    }

    public function getModuloMembri()
    {
        if (is_null($this->array_membri))
        {
            $array_membri = array();
            $array_membri['membri'] = $this->root . "/adherents/index.php?leftmenu=members&mainmenu=members";
            $array_membri['nuovo_membro'] = $this->root . "/adherents/fiche.php?mainmenu=members&leftmenu=members&action=create";
            $array_membri['elenco'] = $this->root . "/adherents/liste.php?mainmenu=members&leftmenu=members";
            $array_membri['elenco_membri_da_convalidare'] = $this->root . "/adherents/liste.php?mainmenu=members&leftmenu=members&statut=-1";
            $array_membri['elenco_membri_convalidati'] = $this->root . "/adherents/liste.php?mainmenu=members&leftmenu=members&statut=1";
            $array_membri['elenco_membri_aggiornati'] = $this->root . "/adherents/liste.php?mainmenu=members&leftmenu=members&statut=1&filter=uptodate";
            $array_membri['elenco_membri_non_aggiornati'] = $this->root . "/adherents/liste.php?mainmenu=members&leftmenu=members&statut=1&filter=outofdate";
            $array_membri['elenco_membri_revocati'] = $this->root . "/adherents/liste.php?mainmenu=members&leftmenu=members&statut=0";
            $array_membri['statistiche'] = $this->root . "/adherents/stats/geo.php?mainmenu=members&leftmenu=members&mode=memberbycountry";

            $array_adesioni = array();
            $array_adesioni['adesioni'] = $this->root . "/adherents/index.php?leftmenu=members&mainmenu=members";
            $array_adesioni['nuova_adesione'] = $this->root . "/adherents/liste.php?leftmenu=members&statut=-1,1&mainmenu=members";
            $array_adesioni['elenco'] = $this->root . "/adherents/cotisations.php?mainmenu=members&leftmenu=members";
            $array_adesioni['statistiche'] = $this->root . "/adherents/stats/index.php?mainmenu=members&leftmenu=members";


            $array_categoria = array();
            $array_categoria['categorie'] = $this->root . "/categories/index.php?mainmenu=members&leftmenu=cat&type=3";
            $array_categoria['nuova_categoria'] = $this->root . "/categories/fiche.php?mainmenu=members&action=create&type=3";

            $array_esportazioni = array();
            $array_esportazioni['esportazioni'] = $this->root . "/adherents/index.php?leftmenu=export&mainmenu=members";
            $array_esportazioni['dati'] = $this->root . "/exports/index.php?mainmenu=members&leftmenu=export";
            $array_esportazioni['file_htpasswd'] = $this->root . "/adherents/htpasswd.php?mainmenu=members&leftmenu=export";
            $array_esportazioni['schede_membri'] = $this->root . "/adherents/cartes/carte.php?mainmenu=members&leftmenu=export";

            $array_tipo_membro = array();
            $array_tipo_membro['tipo_membro'] = $this->root . "/adherents/type.php?leftmenu=setup&mainmenu=members";
            $array_tipo_membro['nuovo'] = $this->root . "/adherents/type.php?leftmenu=setup&mainmenu=members&action=create";
            $array_tipo_membro['elenco'] = $this->root . "/adherents/type.php?leftmenu=setup&mainmenu=members";

            $array_merge = array();
            $array_merge['membri'] = $array_membri;
            $array_merge['adesioni'] = $array_adesioni;
            $array_merge['categorie'] = $array_categoria;
            $array_merge['esportazioni'] = $array_esportazioni;
            $array_merge['tipo_membro'] = $array_tipo_membro;

            $this->array_membri = $array_merge;
        }


        return $this->array_membri;
    }

    public function getModuloDocumenti()
    {
        if (is_null($this->array_documenti))
        {
            $array_edm_area = array();
            $array_edm_area['edm_area'] = $this->root . "/ecm/index.php?mainmenu=ecm&leftmenu=ecm&idmenu=31";
            $array_edm_area['gerarchia_manuale'] = $this->root . "/ecm/index.php?action=file_manager&mainmenu=ecm&leftmenu=ecm&idmenu=32";
            $array_edm_area['gerarchia_automatica'] = $this->root . "/ecm/index_auto.php?action=file_manager&mainmenu=ecm&leftmenu=ecm&idmenu=33";

            $this->array_documenti = $array_edm_area;
        }
        return $this->array_documenti;
    }

    public function getModuloordineGiorno()
    {
        if (is_null($this->array_ord_giorno))
        {
            $array_ord_giorno = array();
            $array_ord_giorno['azioni'] = $this->root . "/comm/action/index.php?mainmenu=agenda&leftmenu=agenda&idmenu=36";
            $array_ord_giorno['nuova_azione'] = $this->root . "/comm/action/fiche.php?mainmenu=agenda&leftmenu=agenda&action=create&idmenu=37";
            $array_ord_giorno['calendario'] = $this->root . "/comm/action/index.php?mainmenu=agenda&leftmenu=agenda&idmenu=38";
            $array_ord_giorno['eventi_non_completati'] = $this->root . "/comm/action/index.php?mainmenu=agenda&leftmenu=agenda&status=todo&filter=mine&idmenu=39";
            $array_ord_giorno['eventi_passati'] = $this->root . "/comm/action/index.php?mainmenu=agenda&leftmenu=agenda&status=done&filter=mine&idmenu=40";
            $array_ord_giorno['tutte_azioni_incomplete'] = $this->root . "/comm/action/index.php?mainmenu=agenda&leftmenu=agenda&status=todo&idmenu=41";
            $array_ord_giorno['tutte_azioni_passate'] = $this->root . "/comm/action/index.php?mainmenu=agenda&leftmenu=agenda&status=done&idmenu=42";
            $array_ord_giorno['elenco'] = $this->root . "/comm/action/listactions.php?mainmenu=agenda&leftmenu=agenda&idmenu=43";
            $array_ord_giorno['miei_eventi_non_completati'] = $this->root . "/comm/action/listactions.php?mainmenu=agenda&leftmenu=agenda&status=todo&filter=mine&idmenu=44";
            $array_ord_giorno['miei_eventi_passati'] = $this->root . "/comm/action/listactions.php?mainmenu=agenda&leftmenu=agenda&status=done&filter=mine&idmenu=45";
            $array_ord_giorno['tutte_le_azioni_incomplete'] = $this->root . "/comm/action/listactions.php?mainmenu=agenda&leftmenu=agenda&status=todo&idmenu=46";
            $array_ord_giorno['tutte_le_azioni_passate'] = $this->root . "/comm/action/listactions.php?mainmenu=agenda&leftmenu=agenda&status=done&idmenu=47";
            $array_ord_giorno['reportistiche'] = $this->root . "/comm/action/rapport/index.php?mainmenu=agenda&leftmenu=agenda&idmenu=48";
            $this->array_ord_giorno = $array_ord_giorno;
        }
        return $this->array_ord_giorno;
    }

}
