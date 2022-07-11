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


-- CREATE TABLE FACTURE FOURN DET EXTRAFIELDS
CREATE TABLE IF NOT EXISTS llx_facture_fourn_det_extrafields (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  tms timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  fk_object integer NOT NULL,
  import_key varchar(14) DEFAULT NULL,
  KEY idx_facturefourndet_extrafields (fk_object)
) ENGINE=InnoDB;



-- VIEW FOR BANK ACCOUNT (filter on entity field)
CREATE VIEW llx_bank_account_view_facturebusinessita___ENTITY__ AS select llx_bank_account.rowid AS rowid, llx_bank_account.label AS label FROM llx_bank_account WHERE llx_bank_account.entity = '__ENTITY__';


-- TABLE SOCIETE
ALTER TABLE llx_societe_extrafields	ADD  attiva_xml_facturebusinessita text;
ALTER TABLE llx_societe_extrafields	ADD  codice_univoco_ufficio_facturebusinessita VARCHAR ( 10 );	


-- TABLE COMMANDE DET
ALTER TABLE  llx_commandedet_extrafields	ADD  codicecig_facturebusinessita VARCHAR( 255 );
ALTER TABLE  llx_commandedet_extrafields	ADD  codicecup_facturebusinessita VARCHAR( 255 );


-- TABLE FACTURE
ALTER TABLE  llx_facture_extrafields 	ADD esigibilita_iva_facturebusinessita text;
ALTER TABLE  llx_facture_extrafields 	ADD data_competenza_da_facturebusinessita date;
ALTER TABLE  llx_facture_extrafields 	ADD data_competenza_a_facturebusinessita date;
ALTER TABLE  llx_facture_extrafields 	ADD fk_bank_account_facturebusinessita text;
ALTER TABLE  llx_facture_extrafields 	ADD rifadm126_facturebusinessita VARCHAR( 20 );


-- TABLE FACTUREDET
ALTER TABLE  llx_facturedet_extrafields 	ADD fk_pianoconti_attivi_facturebusinessita text;
ALTER TABLE  llx_facturedet_extrafields 	ADD fk_uproduttiva_facturebusinessita text;
ALTER TABLE  llx_facturedet_extrafields 	ADD fk_iva_esenzioni_facturebusinessita text;
ALTER TABLE  llx_facturedet_extrafields 	ADD codicecig_facturebusinessita VARCHAR( 255 );
ALTER TABLE  llx_facturedet_extrafields 	ADD codicecup_facturebusinessita VARCHAR( 255 );
ALTER TABLE  llx_facturedet_extrafields 	ADD ndoc_facturebusinessita VARCHAR( 255 );
ALTER TABLE  llx_facturedet_extrafields 	ADD tipodoc_facturebusinessita text;
ALTER TABLE  llx_facturedet_extrafields 	ADD datadoc_facturebusinessita date;
ALTER TABLE  llx_facturedet_extrafields 	ADD rifadm22115_facturebusinessita VARCHAR( 20 );


-- TABLE FACTUREFOURN 
ALTER TABLE  llx_facture_fourn_extrafields ADD ritenuta_acconto_facturebusinessita double(24,8) DEFAULT NULL;
ALTER TABLE  llx_facture_fourn_extrafields ADD netto_da_pagare_facturebusinessita double(24,8) DEFAULT NULL;
ALTER TABLE  llx_facture_fourn_extrafields ADD esigibilita_iva_facturebusinessita text;
ALTER TABLE  llx_facture_fourn_extrafields ADD intracomunitaria_facturebusinessita text;
ALTER TABLE  llx_facture_fourn_extrafields ADD data_competenza_da_facturebusinessita date;
ALTER TABLE  llx_facture_fourn_extrafields ADD data_competenza_a_facturebusinessita date;
ALTER TABLE  llx_facture_fourn_extrafields ADD fk_bank_account_facturebusinessita text;



-- TABLE FACTUREFOURNDET
ALTER TABLE  llx_facture_fourn_det_extrafields 	ADD fk_ritenuta_acconto_facturebusinessita text;
ALTER TABLE  llx_facture_fourn_det_extrafields 	ADD fk_pianoconti_facturebusinessita text;
ALTER TABLE  llx_facture_fourn_det_extrafields 	ADD fk_uproduttiva_facturebusinessita text;
ALTER TABLE  llx_facture_fourn_det_extrafields 	ADD fk_iva_esenzioni_facturebusinessita text;

-- TABLE BANK
CREATE TABLE IF NOT EXISTS llx_bank_extrafields (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  tms timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  fk_object integer NOT NULL,
  import_key varchar(14) DEFAULT NULL,
  KEY idx_bank_extrafields (fk_object)
) ENGINE=InnoDB;

ALTER TABLE  llx_bank_extrafields 	ADD fk_causali_facturebusinessita VARCHAR(255);
ALTER TABLE  llx_bank_extrafields		ADD descrizione_causale_facturebusinessita text;
ALTER TABLE  llx_bank_extrafields 	ADD fk_pianoconti_facturebusinessita text;
ALTER TABLE  llx_bank_extrafields 	ADD esporta_facturebusinessita text;

-- TABLE BANK ACCOUNT
CREATE TABLE IF NOT EXISTS llx_bank_account_extrafields (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  tms timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  fk_object integer NOT NULL,
  import_key varchar(14) DEFAULT NULL,
  KEY idx_bank_account_extrafields (fk_object)
) ENGINE=InnoDB;


-- OTHERS TABLES
CREATE TABLE IF NOT EXISTS llx_facturebusinessita_ritenuta_acconto (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  fk_facture_fourn integer NOT NULL,
  fk_facture_fourn_det integer NOT NULL,
  valore_aliquota float(3,2) NOT NULL,
  imponibile_riga double(24,8) NOT NULL,
  ritenuta_acconto_valore double(24,8) NOT NULL,
  UNIQUE KEY fk_facture_fourn_det_UNIQUE (fk_facture_fourn_det)
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS llx_facturebusinessita_aliquota_ritenuta_acconto (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  entity integer NOT NULL DEFAULT  '1',
  label varchar(50) NOT NULL,
  value double NOT NULL
) ENGINE=InnoDB;

CREATE VIEW llx_facturebusinessita_aliquota_ritenuta_acconto___ENTITY__ AS select llx_facturebusinessita_aliquota_ritenuta_acconto.rowid AS rowid, llx_facturebusinessita_aliquota_ritenuta_acconto.label AS label, llx_facturebusinessita_aliquota_ritenuta_acconto.value AS value FROM llx_facturebusinessita_aliquota_ritenuta_acconto WHERE (llx_facturebusinessita_aliquota_ritenuta_acconto.entity = '__ENTITY__');


CREATE TABLE IF NOT EXISTS llx_facturebusinessita_iva_esenzioni (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  entity integer NOT NULL DEFAULT  '1',
  label varchar(50) NOT NULL,
  code varchar(10) NOT NULL,
  description varchar(100) NOT NULL
) ENGINE=InnoDB;

CREATE VIEW llx_facturebusinessita_iva_esenzioni___ENTITY__ AS select llx_facturebusinessita_iva_esenzioni.rowid AS rowid, llx_facturebusinessita_iva_esenzioni.label AS label, llx_facturebusinessita_iva_esenzioni.label AS description FROM llx_facturebusinessita_iva_esenzioni WHERE (llx_facturebusinessita_iva_esenzioni.entity = '__ENTITY__');



CREATE TABLE IF NOT EXISTS llx_facturebusinessita_pianoconti_attivi (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  entity integer NOT NULL DEFAULT  '1',
  label varchar(150) CHARACTER SET utf8 DEFAULT NULL,
  account char(10) DEFAULT NULL,
  KEY account (account)
) ENGINE=InnoDB;

CREATE VIEW llx_facturebusinessita_pianoconti_attivi___ENTITY__ AS select llx_facturebusinessita_pianoconti_attivi.rowid AS rowid, llx_facturebusinessita_pianoconti_attivi.label AS label, llx_facturebusinessita_pianoconti_attivi.account AS account FROM llx_facturebusinessita_pianoconti_attivi WHERE (llx_facturebusinessita_pianoconti_attivi.entity = '__ENTITY__');


CREATE TABLE IF NOT EXISTS llx_facturebusinessita_pianoconti (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  entity integer NOT NULL DEFAULT  '1',
  label varchar(150) DEFAULT NULL, 
  account char(10) NOT NULL,
  KEY account (account)
) ENGINE=InnoDB;

CREATE VIEW llx_facturebusinessita_pianoconti___ENTITY__ AS select llx_facturebusinessita_pianoconti.rowid AS rowid, llx_facturebusinessita_pianoconti.label AS label, llx_facturebusinessita_pianoconti.account AS account FROM llx_facturebusinessita_pianoconti WHERE (llx_facturebusinessita_pianoconti.entity = '__ENTITY__');


CREATE TABLE IF NOT EXISTS llx_facturebusinessita_unita_produttive (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  entity integer NOT NULL DEFAULT  '1',
  label varchar(200) NOT NULL,
  date_import datetime DEFAULT NULL,
  type varchar(1) NOT NULL,
  trash varchar(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB;

CREATE VIEW llx_facturebusinessita_unita_produttive___ENTITY__ AS select llx_facturebusinessita_unita_produttive.rowid AS rowid, llx_facturebusinessita_unita_produttive.label AS label, llx_facturebusinessita_unita_produttive.date_import AS date_import, llx_facturebusinessita_unita_produttive.type AS type, llx_facturebusinessita_unita_produttive.trash AS trash FROM llx_facturebusinessita_unita_produttive WHERE (llx_facturebusinessita_unita_produttive.entity = '__ENTITY__');



CREATE TABLE IF NOT EXISTS llx_facturebusinessita_causali_teamsystem (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  entity integer NOT NULL DEFAULT  '1',
  label varchar(200) DEFAULT NULL
) ENGINE=InnoDB;

CREATE VIEW llx_facturebusinessita_causali_teamsystem___ENTITY__ AS select llx_facturebusinessita_causali_teamsystem.rowid AS rowid, llx_facturebusinessita_causali_teamsystem.label AS label FROM llx_facturebusinessita_causali_teamsystem WHERE (llx_facturebusinessita_causali_teamsystem.entity = '__ENTITY__');

ALTER TABLE llx_facture_fourn_extrafields ADD CONSTRAINT fk_object_unique_facturebusinessita UNIQUE(fk_object);

