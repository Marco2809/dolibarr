-- Copyright (C) 2015 Claudio Aschieri <c.aschieri@19.coop>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License

-- along with this program.  If not, see <http://www.gnu.org/licenses/>.

-- NEW PAYMENT TYPE
INSERT INTO llx_c_paiement (id, code, libelle, type, active, module) VALUES (1919, 'RIT', 'Ritenuta d''acconto', 2, 1, NULL);



-- NEW PAYMENT TERM
INSERT INTO llx_c_payment_term (rowid, code, active, libelle, libelle_facture, fdm, nbjour, decalage, module) VALUES (1919,'NN', 1, 'Nessun termine', 'Nessun termine', 0, 0, NULL, NULL);


-- CONSTANT REGIMI FISCALI
INSERT INTO llx_const (name, entity, value, type, visible, note) VALUES ('FACTUREBUSINESSITA_REGIME_FISCALE', '__ENTITY__', '1', 'chaine', 0, 'Regimi fiscali');
INSERT INTO llx_const (name, entity, value, type, visible, note) VALUES ('FACTUREBUSINESSITA_STATO_LIQUIDAZIONE', '__ENTITY__', 'LN', 'chaine', 0, 'Stato liquidazione');
INSERT INTO llx_const (name, entity, value, type, visible, note) VALUES ('FACTUREBUSINESSITA_PROGRESSIVO_FE', '__ENTITY__', '1', 'chaine', 1, 'Mantiene un progressivo (da non modificare) dei file xml generati per tutte le fatture elettroniche presenti nel sistema. Impedisce la generazione di 2 file xml con lo stesso nome.');
INSERT INTO llx_const (name, entity, value, type, visible, note) VALUES ('FACTUREBUSINESSITA_ESENZIONE_TO_PDF', '__ENTITY__', 'F', 'chaine', 0, 'Concatena l'esenzione iva alla descrizione della riga di fattura attiva');


-- ALIQUOTA RITENUTA
INSERT INTO llx_facturebusinessita_aliquota_ritenuta_acconto (rowid, entity, label, value) VALUES (1, '__ENTITY__',  '0%', 0); 
INSERT INTO llx_facturebusinessita_aliquota_ritenuta_acconto (rowid, entity, label, value) VALUES (2, '__ENTITY__',  '20%', 0.2); 
INSERT INTO llx_facturebusinessita_aliquota_ritenuta_acconto (rowid, entity, label, value) VALUES (3, '__ENTITY__',  '30%', 0.3);



-- ESENZIONI IVA
INSERT INTO llx_facturebusinessita_iva_esenzioni (rowid, entity, label, code, description) VALUES (1, '__ENTITY__', 'Escluse ex art. 15', 'N1', '');
INSERT INTO llx_facturebusinessita_iva_esenzioni (rowid, entity, label, code, description) VALUES (2, '__ENTITY__', 'Non soggette', 'N2', '');
INSERT INTO llx_facturebusinessita_iva_esenzioni (rowid, entity, label, code, description) VALUES (3, '__ENTITY__', 'Non imponibili', 'N3', '');
INSERT INTO llx_facturebusinessita_iva_esenzioni (rowid, entity, label, code, description) VALUES (4, '__ENTITY__', 'Esenti', 'N4', '');
INSERT INTO llx_facturebusinessita_iva_esenzioni (rowid, entity, label, code, description) VALUES (5, '__ENTITY__', 'Regime del margine', 'N5', '');
INSERT INTO llx_facturebusinessita_iva_esenzioni (rowid, entity, label, code, description) VALUES (6, '__ENTITY__', 'Inversione contabile (reverse charge)', 'N6', '');


-- REGIMI FISCALI
INSERT INTO llx_c_facturebusinessita_regimifiscali (rowid, code, label, active) VALUES(1, 'RF01', 'Ordinario', 1);
INSERT INTO llx_c_facturebusinessita_regimifiscali (rowid, code, label, active) VALUES(2, 'RF02', 'Contribuenti minimi (art.1, c.96-117, L. 244/07)', 1);
INSERT INTO llx_c_facturebusinessita_regimifiscali (rowid, code, label, active) VALUES(3, 'RF03', 'Nuove iniziative produttive (art.13, L. 388/00)', 1);
INSERT INTO llx_c_facturebusinessita_regimifiscali (rowid, code, label, active) VALUES(4, 'RF04', 'Agricoltura e attività connesse e pesca (artt.34 e 34-bis, DPR 633/72)', 1);
INSERT INTO llx_c_facturebusinessita_regimifiscali (rowid, code, label, active) VALUES(5, 'RF05', 'Vendita sali e tabacchi (art.74, c.1, DPR. 633/72)', 1);
INSERT INTO llx_c_facturebusinessita_regimifiscali (rowid, code, label, active) VALUES(6, 'RF06', 'Commercio fiammiferi (art.74, c.1, DPR 633/72)', 1);
INSERT INTO llx_c_facturebusinessita_regimifiscali (rowid, code, label, active) VALUES(7, 'RF07', 'Editoria (art.74, c.1, DPR 633/72)', 1);
INSERT INTO llx_c_facturebusinessita_regimifiscali (rowid, code, label, active) VALUES(8, 'RF08', 'Gestione servizi telefonia pubblica (art.74, c.1, DPR 633/72)', 1);
INSERT INTO llx_c_facturebusinessita_regimifiscali (rowid, code, label, active) VALUES(9, 'RF09', 'Rivendita documenti di trasporto pubblico e di sosta (art.74, c.1, DPR 633/72)', 1);
INSERT INTO llx_c_facturebusinessita_regimifiscali (rowid, code, label, active) VALUES(10, 'RF10', 'Intrattenimenti, giochi e altre attività di cui alla tariffa allegata al DPR 640/72 (art.74, c.6, DPR 633', 1);
INSERT INTO llx_c_facturebusinessita_regimifiscali (rowid, code, label, active) VALUES(11, 'RF11', 'Agenzie viaggi e turismo (art.74-ter, DPR 633/72)', 1);
INSERT INTO llx_c_facturebusinessita_regimifiscali (rowid, code, label, active) VALUES(12, 'RF12', 'Agriturismo (art.5, c.2, L. 413/91)', 1);
INSERT INTO llx_c_facturebusinessita_regimifiscali (rowid, code, label, active) VALUES(13, 'RF13', 'Vendite a domicilio (art.25-bis, c.6, DPR 600/73)', 1);
INSERT INTO llx_c_facturebusinessita_regimifiscali (rowid, code, label, active) VALUES(14, 'RF14', 'Rivendita beni usati, oggetti d’arte, d’antiquariato o da collezione (art.36, DL 41/95)', 1);
INSERT INTO llx_c_facturebusinessita_regimifiscali (rowid, code, label, active) VALUES(15, 'RF15', 'Agenzie di vendite all’asta di oggetti d’arte, antiquariato o da collezione (art.40-bis, DL 41/95)', 1);
INSERT INTO llx_c_facturebusinessita_regimifiscali (rowid, code, label, active) VALUES(16, 'RF16', 'IVA per cassa P.A. (art.6, c.5, DPR 633/72)', 1);
INSERT INTO llx_c_facturebusinessita_regimifiscali (rowid, code, label, active) VALUES(17, 'RF17', 'IVA per cassa (art. 32-bis, DL 83/2012)', 1);
INSERT INTO llx_c_facturebusinessita_regimifiscali (rowid, code, label, active) VALUES(18, 'RF18', 'Altro', 1);
INSERT INTO llx_c_facturebusinessita_regimifiscali (rowid, code, label, active) VALUES(19, 'RF19', 'Regime forfettario (art.1, c.54-89, L. 190/2014)', 1);


-- ESIGIBILITA IVA
INSERT INTO llx_c_facturebusinessita_esigibilitaiva (rowid, code, label, active, use_default) VALUES(1, 'I', 'I.V.A. ad esigibilità immediata', 1, 1);
INSERT INTO llx_c_facturebusinessita_esigibilitaiva (rowid, code, label, active, use_default) VALUES(2, 'D', 'I.V.A. ad esigibilità differita', 1, 0);
INSERT INTO llx_c_facturebusinessita_esigibilitaiva (rowid, code, label, active, use_default) VALUES(3, 'S', 'Scissione dei pagamenti', 1, 0);


-- EXTRAFIELDS

-- CUSTOMER ORDER
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('codicecig_facturebusinessita', '__ENTITY__', 'commandedet', 'Codice CIG', 'varchar', '255', 0, 0, 0, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('codicecup_facturebusinessita', '__ENTITY__', 'commandedet', 'Codice CUP', 'varchar', '255', 0, 0, 0, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}');


-- SOCIETE
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('fatturazione_elettronica_separatore_facturebusinessita', '__ENTITY__', 'societe', 'Fatturazione Elettronica', 'separate', '', 0, 0, 1, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('attiva_xml_facturebusinessita', '__ENTITY__', 'societe', 'Attivare la generazione del file xml?', 'select', '', 0, 0, 2, 'a:1:{s:7:"options";a:2:{s:2:"Si";s:2:"Si";s:2:"No";s:2:"No";}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('codice_univoco_ufficio_facturebusinessita', '__ENTITY__', 'societe', 'Codice univoco ufficio', 'varchar', '10', 0, 0, 3, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('altro_facturebusinessita', '__ENTITY__', 'societe', 'Altro', 'separate', '', 0, 0, 4, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}');



-- FACTURE
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('esigibilita_iva_facturebusinessita', '__ENTITY__', 'facture', 'Esigibilità I.V.A.', 'sellist', '', 0, 0, 1, 'a:1:{s:7:"options";a:1:{s:47:"c_facturebusinessita_esigibilitaiva:label:rowid";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('data_competenza_a_facturebusinessita', '__ENTITY__', 'facture', 'Data competenza a', 'date', '', 0, 0, 2, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('data_competenza_da_facturebusinessita', '__ENTITY__', 'facture', 'Data competenza da', 'date', '', 0, 0, 3, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('fk_bank_account_facturebusinessita', '__ENTITY__', 'facture', 'Banca', 'sellist', '', 0, 0, 4, 'a:1:{s:7:"options";a:1:{s:50:"bank_account_view_facturebusinessita___ENTITY__:label:rowid";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('rifadm126_facturebusinessita', '__ENTITY__', 'facture', 'RiferimentoAmministrazione (1.2.6)', 'varchar', '20', 0, 0, 5, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}');


-- FACTURE DET
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('fk_uproduttiva_facturebusinessita', '__ENTITY__', 'facturedet', 'Unità produttiva', 'sellist', '', 0, 0, 1, 'a:1:{s:7:"options";a:1:{s:49:"facturebusinessita_unita_produttive___ENTITY__:label:rowid";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('fk_pianoconti_attivi_facturebusinessita', '__ENTITY__', 'facturedet', 'Piano dei conti', 'sellist', '', 0, 0, 2, 'a:1:{s:7:"options";a:1:{s:50:"facturebusinessita_pianoconti_attivi___ENTITY__:label:rowid";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('fk_iva_esenzioni_facturebusinessita', '__ENTITY__', 'facturedet', 'Esenzione IVA', 'sellist', '', 0, 0, 3, 'a:1:{s:7:"options";a:1:{s:46:"facturebusinessita_iva_esenzioni___ENTITY__:label:rowid";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('tipodoc_facturebusinessita', '__ENTITY__', 'facturedet', 'Tipo documento di riferimento', 'select', '', 0, 0, 4, 'a:1:{s:7:"options";a:3:{i:1;s:9:"Contratto";i:2;s:11:"Convenzione";i:3;s:6:"Ordine";}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('ndoc_facturebusinessita', '__ENTITY__', 'facturedet', 'Numero documento', 'varchar', '255', 0, 0, 5, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('datadoc_facturebusinessita', '__ENTITY__', 'facturedet', 'Data del documento di riferimento', 'date', '', 0, 0, 6, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('codicecig_facturebusinessita', '__ENTITY__', 'facturedet', 'Codice CIG', 'varchar', '255', 0, 0, 7, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('codicecup_facturebusinessita', '__ENTITY__', 'facturedet', 'Codice CUP', 'varchar', '255', 0, 0, 8, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('rifadm22115_facturebusinessita', '__ENTITY__', 'facturedet', 'RiferimentoAmministrazione (2.2.1.15)', 'varchar', '20', 0, 0, 9, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}');



-- FACTURE FOURN 
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('ritenuta_acconto_facturebusinessita', '__ENTITY__', 'facture_fourn',  'Ritenuta d''acconto', 'price', '', 0, 0, 1, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('netto_da_pagare_facturebusinessita', '__ENTITY__', 'facture_fourn', 'Netto da pagare', 'price', '', 0, 0, 2, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('esigibilita_iva_facturebusinessita', '__ENTITY__', 'facture_fourn', 'Esigibilità I.V.A.', 'sellist', '', 0, 0, 3, 'a:1:{s:7:"options";a:1:{s:47:"c_facturebusinessita_esigibilitaiva:label:rowid";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('intracomunitaria_facturebusinessita', '__ENTITY__', 'facture_fourn', 'Intracomunitaria', 'select', '', 0, 0, 4, 'a:1:{s:7:"options";a:2:{s:2:"No";s:2:"No";s:2:"Si";s:2:"Si";}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('data_competenza_da_facturebusinessita', '__ENTITY__', 'facture_fourn', 'Data competenza da', 'date', '', 0, 0, 5, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('data_competenza_a_facturebusinessita', '__ENTITY__', 'facture_fourn', 'Data competenza a', 'date', '', 0, 0, 6, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('fk_bank_account_facturebusinessita', '__ENTITY__', 'facture_fourn', 'Banca', 'sellist', '', 0, 0, 7, 'a:1:{s:7:"options";a:1:{s:50:"bank_account_view_facturebusinessita___ENTITY__:label:rowid";N;}}');



-- FACTURE FOURN DET
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('fk_ritenuta_acconto_facturebusinessita', '__ENTITY__', 'facture_fourn_det', 'Aliquota RA', 'sellist', '', 0, 0, 1, 'a:1:{s:7:"options";a:1:{s:58:"facturebusinessita_aliquota_ritenuta_acconto___ENTITY__:label:rowid";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('fk_pianoconti_facturebusinessita', '__ENTITY__', 'facture_fourn_det', 'Piano dei conti', 'sellist', '', 0, 0, 2, 'a:1:{s:7:"options";a:1:{s:43:"facturebusinessita_pianoconti___ENTITY__:label:rowid";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('fk_uproduttiva_facturebusinessita', '__ENTITY__', 'facture_fourn_det', 'Unità produttiva', 'sellist', '', 0, 0, 3, 'a:1:{s:7:"options";a:1:{s:49:"facturebusinessita_unita_produttive___ENTITY__:label:rowid";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('fk_iva_esenzioni_facturebusinessita', '__ENTITY__', 'facture_fourn_det', 'Esenzione IVA', 'sellist', '', 0, 0, 4, 'a:1:{s:7:"options";a:1:{s:46:"facturebusinessita_iva_esenzioni___ENTITY__:label:rowid";N;}}');


-- BANK
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('fk_causali_facturebusinessita', '__ENTITY__', 'bank', 'Causale', 'sellist', '', 0, 0, 1, 'a:1:{s:7:"options";a:1:{s:51:"facturebusinessita_causali_teamsystem___ENTITY__:rowid:label";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('descrizione_causale_facturebusinessita', '__ENTITY__', 'bank', 'Descrizione causale', 'varchar', '150', 0, 0, 2, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('fk_pianoconti_facturebusinessita', '__ENTITY__', 'bank', 'Piano dei conti', 'sellist', '', 0, 0, 3, 'a:1:{s:7:"options";a:1:{s:41:"facturebusinessita_pianoconti___ENTITY__:label:rowid";N;}}');
INSERT INTO llx_extrafields (name, entity, elementtype, label, type, size, fieldunique, fieldrequired, pos, param) VALUES ('esporta_facturebusinessita', '__ENTITY__', 'bank', 'Esporta', 'select', '', 0, 0, 4, 'a:1:{s:7:"options";a:2:{s:2:"Si";s:2:"Si";s:2:"No";s:2:"No";}}');




-- CAUSALI TEAMSYSTEM
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (1, '__ENTITY__', 'FATT. EMESSA');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (2, '__ENTITY__', 'N.C. A CLIENTE');		
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (3, '__ENTITY__', 'RETT.FT.CLIENTI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (4, '__ENTITY__', 'N.D. A CLIENTE');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (5, '__ENTITY__', 'N.D. DA CLIENTE');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (6, '__ENTITY__', 'N.VAR.A CLIENTE');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (7, '__ENTITY__', 'AUTO FT ART.17');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (8, '__ENTITY__', 'INC/PG.SOSP.IMP');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (9, '__ENTITY__', 'FT.VEND.INTRA.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (10, '__ENTITY__', 'PAG.FATT.RITEN.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (11, '__ENTITY__', 'FATT. ACQUISTO');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (12, '__ENTITY__', 'N.C. DA FORN.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (13, '__ENTITY__', 'N.D.DA FORNIT.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (14, '__ENTITY__', 'N.D. DA FORN.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (15, '__ENTITY__', 'N.D. A FORN. A');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (16, '__ENTITY__',  'N.VAR.DA FORN.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (17, '__ENTITY__', 'FT.ACQ.S.MARIN');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (18, '__ENTITY__', 'FT.ACQ.AGRICOL');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (19, '__ENTITY__', 'FT. ACQ.INTRA.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (20, '__ENTITY__', 'CORRISPETTIVI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (21, '__ENTITY__', 'BOLLA DOGANALE');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (22, '__ENTITY__', 'FATT.CON CORR.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (23, '__ENTITY__', 'PAGAMENTO R.A.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (24, '__ENTITY__', 'CORRISPETTIVI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (25, '__ENTITY__','SALDO APERTURA');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (26, '__ENTITY__', 'SALDO CHIUSURA');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (27, '__ENTITY__', 'PAG. FATTURA');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (28, '__ENTITY__', 'ABBUONO');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (29, '__ENTITY__','REG.RIC.R.A.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (30, '__ENTITY__', 'PAGAMENTO F24');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (31, '__ENTITY__', 'VERSAMENTO C/C');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (32, '__ENTITY__', 'PRELEVAMEN. CC');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (33, '__ENTITY__','SP.POSTALI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (34, '__ENTITY__', 'PAG.INPS 10-12%');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (35, '__ENTITY__', 'RISC.FATT.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (36, '__ENTITY__', 'T.F.R.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (37, '__ENTITY__','INCASSO FATTUR');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (38, '__ENTITY__', 'CONS.FUORI COM');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (39, '__ENTITY__', '');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (40, '__ENTITY__', 'RILEV.STIPENDI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (41, '__ENTITY__','RILEV.STIP.DIP');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (42, '__ENTITY__', 'ONERI BANCARI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (43, '__ENTITY__', 'PREST.COLLAB.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (44, '__ENTITY__', 'RILEV.STIPENDI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (45, '__ENTITY__','STIP.DIPENDENT');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (46, '__ENTITY__', 'ONERI PR.DIPEN');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (47, '__ENTITY__', 'ONERI PREV.SOC');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (48, '__ENTITY__', 'IMPOSTA BOLLO');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (49, '__ENTITY__','CONTRIB.DIP.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (50, '__ENTITY__', 'RIMBORSI SPESE');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (51, '__ENTITY__', 'PAG.STIPENDI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (52, '__ENTITY__', 'RIL.TFR');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (53, '__ENTITY__','RETRIBUZIONI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (54, '__ENTITY__', 'PREST.OCCASION.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (55, '__ENTITY__', 'F 24');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (56, '__ENTITY__', 'RISCOSSA PARCEL');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (57, '__ENTITY__','NOTA ACCR.A CL.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (58, '__ENTITY__', 'FATT. DA EMETT.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (59, '__ENTITY__', 'ADD.RATA FINANZ');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (60, '__ENTITY__', 'ADDEBITO MUTUO');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (61, '__ENTITY__','PAG.IMPOSTE F24');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (62, '__ENTITY__', 'PAG.FITTI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (63, '__ENTITY__', 'G/C CASSA');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (64, '__ENTITY__', 'G/C RIT.C/TERZI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (65, '__ENTITY__','PREL. DA BANCA');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (66, '__ENTITY__', 'G/C RIT.ACC.LOR');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (67, '__ENTITY__', 'G/C RIT.ACC.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (68, '__ENTITY__', 'FT.DA RICEVERE');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (69, '__ENTITY__','INC.CORR.CRED.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (70, '__ENTITY__', 'VALORI BOLLATI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (71, '__ENTITY__', 'INCASSO RETTE');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (72, '__ENTITY__', 'INCASSO CANONE');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (73, '__ENTITY__','GIROCONTO');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (74, '__ENTITY__', 'PAG.CARTA SI''-');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (75, '__ENTITY__', 'PAG. I.V.A.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (76, '__ENTITY__', 'PAG.NOTA SPESE');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (77, '__ENTITY__','ANTICIP.C/TERZI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (78, '__ENTITY__', 'INC.CORR.FATT.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (79, '__ENTITY__', 'INCASSO CORRISP');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (80, '__ENTITY__', 'RIMB. TERRENO');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (81, '__ENTITY__','RIMBORSI CHILOM');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (82, '__ENTITY__', 'MATERIALE D''USO');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (83, '__ENTITY__', 'COMP.COLLABORAT');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (84, '__ENTITY__',  'MATERIALE DIDAT');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (85, '__ENTITY__','COLL.COORD.CONT');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (86, '__ENTITY__', 'VERSAMENTI SOCI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (87, '__ENTITY__', 'PAG. FATTURA');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (88, '__ENTITY__', 'INC. PARCELLA');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (89, '__ENTITY__','BORSE LAVORO');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (90, '__ENTITY__', 'ACC.RB SC.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (91, '__ENTITY__', 'COMP.OBIETTORE');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (92, '__ENTITY__', 'TESSERA SANIT.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (93, '__ENTITY__','POCKET MONEY');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (94, '__ENTITY__', 'PEDAGGI E POST.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (95, '__ENTITY__', 'INC. BANCOMAT');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (96, '__ENTITY__', 'ALIMENTARI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (97, '__ENTITY__','FARMACIA');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (98, '__ENTITY__', 'MAGG DETR IVA 6');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (99, '__ENTITY__', 'CLIENTI ATT.FT.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (100, '__ENTITY__', 'AMMORTAMENTI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (101, '__ENTITY__','FT.VEND.CESPITI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (102, '__ENTITY__', 'PAG.SPESE VARIE');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (103, '__ENTITY__', 'VERSAM.IN BANCA');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (104, '__ENTITY__', 'PRELEV.DA BANCA');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (105, '__ENTITY__','ANTICIP.C/TERZI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (106, '__ENTITY__', 'RIMBOR. C/TERZI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (107, '__ENTITY__', 'STOR.FT.CLIENTE');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (108, '__ENTITY__', 'STORNO FT.FORN.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (109, '__ENTITY__','G/C INCASSI POS');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (110, '__ENTITY__', 'COMMISSIONI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (111, '__ENTITY__', 'ACQ.CESPITI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (112, '__ENTITY__', 'RICREATIVE');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (113, '__ENTITY__','POSTALI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (114, '__ENTITY__', 'Pag.to Irpef');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (115, '__ENTITY__', 'Giroconto IVA');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (116, '__ENTITY__', 'CARBURANTI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (117, '__ENTITY__','ABBIGLIAMENTO');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (118, '__ENTITY__', 'Pag.contr.Ascom');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (119, '__ENTITY__', 'Pag.contr.sind.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (120, '__ENTITY__', 'G/C BOLLA DOG.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (121, '__ENTITY__','ACQ.CESP.B.D.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (122, '__ENTITY__', 'G/C IVA B.D.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (123, '__ENTITY__', 'Pag. INPS dip.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (124, '__ENTITY__', 'Rata fin.CARIGE');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (125, '__ENTITY__','Pag.rata FIDIC.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (126, '__ENTITY__', 'Pag.acq.azienda');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (127, '__ENTITY__', 'Giroconto');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (128, '__ENTITY__', 'Incasso POS');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (129, '__ENTITY__','BIL.APER.GEAZ');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (130, '__ENTITY__', 'BIL.CHIUS.GEAZ');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (131, '__ENTITY__', 'SPESE RAPPRES.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (132, '__ENTITY__', 'CARNET ASSEGNI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (133, '__ENTITY__','ONERI PREV.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (134, '__ENTITY__', 'F.PROF.SOG.C.P.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (135, '__ENTITY__', 'FATT DIFF.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (136, '__ENTITY__', 'F.VEN SUBFORN.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (137, '__ENTITY__','ANTICIPO SOCI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (138, '__ENTITY__', 'Pag.to R.A.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (139, '__ENTITY__', 'Pag.to INPS 10%');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (140, '__ENTITY__', 'PRES.EFF.SBF');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (141, '__ENTITY__','Pag.to R.A.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (142, '__ENTITY__', 'SOTT.CAPITALE');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (143, '__ENTITY__', 'VERS.QUOTE SOC.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (144, '__ENTITY__', 'ACQ.REV.CHARGE');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (148, '__ENTITY__','Pag.to Enasarco');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (149, '__ENTITY__', 'RIMB. ANT.SOCI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (150, '__ENTITY__', 'INSOLUTO CLI.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (151, '__ENTITY__', 'CHIUS.EFF.CLI.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (152, '__ENTITY__','PG.F.DO CASELLA');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (153, '__ENTITY__', 'Fatt. leasing ');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (154, '__ENTITY__', 'RICARICA CELLUL');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (155, '__ENTITY__', 'CAUZIONI PASS.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (156, '__ENTITY__','Eserc. automez.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (157, '__ENTITY__', 'CONTRIB.UTENTI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (158, '__ENTITY__', 'AUTO FT ART 17');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (160, '__ENTITY__', 'RIMBORSO INPS');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (161, '__ENTITY__','RIMB.CONC.GOVER');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (169, '__ENTITY__', 'Acqui.da priva.');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (170, '__ENTITY__', 'SCORPORO IVA');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (171, '__ENTITY__', 'DESTINAZIONE UT');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (172, '__ENTITY__','POSTALI');
INSERT INTO llx_facturebusinessita_causali_teamsystem (rowid, entity, label) VALUES (173, '__ENTITY__', 'COSTI D''ESER.');
