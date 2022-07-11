<?php

class family {

    private $db = null;

    public function __construct($database) {
        $this->db = $database;
    }

    public function getRowFamily($cod_famiglia) {
        if (empty($cod_famiglia)) {
            return null;
        }
        $sql = "SELECT * ";
        $sql .= " FROM " . MAIN_DB_PREFIX . "product as f ";
        $sql .= "WHERE f.ref = " . "'" . $cod_famiglia . "'";
        $res = $this->db->query($sql);
        $record_famiglia = array();
        if ($res) {
            $record_famiglia = $this->db->fetch_object($res);
            return $record_famiglia;
        }
    }
    
    public function eliminaFamiglia($codice_famiglia)
    {
        if (empty($codice_famiglia)) {
            return false;
        }
        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "product";
        $sql .= " WHERE ref LIKE '" . $codice_famiglia . "'";
        $res = $this->db->query($sql);
        return $res;
    }
    
    

}
