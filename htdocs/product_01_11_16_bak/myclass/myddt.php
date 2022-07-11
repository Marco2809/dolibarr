<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of myddt
 *
 * @author utente
 */
class myddt {

    private $db = null;

    public function __construct($database) {

        $this->db = $database;
    }

    public function insertMyDDT() {

        /* if (empty($dati)) {
          return false;
          }

         */
        $query = "SELECT MAX(rowid) as last_id,facnumber FROM " . MAIN_DB_PREFIX . "facture";
        $query .= " WHERE facnumber LIKE '%AST%'";
        $res = $this->db->query($query);
        $obj = $this->db->fetch_object($res);
        $last_id = 0;
        if (!is_null($obj->last_id)) {
            $facnumber = $obj->facnumber;
            $sotto = str_replace("AST", "", $facnumber);
            $sotto = str_replace("-", "", $sotto);
            $sotto = substr($sotto, 0, -4);
            $last_id = $sotto;
        }
        $last_id++;
        $facnumber = "AST-" . $last_id . "-" . date("Y");
        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "facture(";
        $sql.= "facnumber";
        $sql.= ",entity";
        $sql.= ",fk_soc";
        $sql.= ", datec";
        $sql.= ", datef";
        $sql.= ", model_pdf";
        $sql.= ") VALUES (";
        $sql.= "'" . $facnumber . "'";
        $sql.= ", '2'";
        $sql.= ", '1'";
        $sql.= ", CURRENT_TIMESTAMP()";
        $sql.= "," . date("Y-m-d");
        $sql.= ",'crabe'";
        $sql.= ")";
        $res_insert = $this->db->query($sql);

        if ($res_insert) { // se è stato insetito il record nella tabella fattura
            $query = "SELECT MAX(rowid) as last_id FROM " . MAIN_DB_PREFIX . "facture";
            $result = $this->db->query($query);
            $obj = $this->db->fetch_object($result); // ricavao l'ultima id inserita
            $id_fature = $obj->last_id;
            $sql = "INSERT INTO " . MAIN_DB_PREFIX . "facturedet(";
            $sql.= "fk_facture";
            $sql.= ", description";
            $sql.= ", date_start";
            $sql.= ") VALUES (";
            $sql.= "'" . $id_fature . "'";
            $sql.= ", 'Movimentazione asset'";
            $sql.= ", CURRENT_TIMESTAMP()";
            $sql.= ")";
            $res_insert = $this->db->query($sql);
        }
        if ($res_insert) { // se l'inserimento è andato a buon file, allora
            return $facnumber; // restituisce l'id che è stato inserito 
        }

        return 0; // se l'inserimento del record ha dato un esito negativo
    }

}
