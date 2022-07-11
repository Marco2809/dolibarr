<?php

class logmovimentazione
{
    private $db = null;

    public function __construct($database) {
        $this->db = $database;
    }
    
    public function crealog($array_log, $ddt_code)
    {
        if (empty($array_log))
        {
            return false;
        }
        foreach ($array_log as $code_asset => $code_move)
        {
            $this->insertLog($code_move, $ddt_code);
        }
        return true;
    }
    
    private function insertLog($code_move, $id_ddt )
    {
        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "log_movimentazione(";
        $sql.= "id_movimentazione";
        $sql.= ", id_ddt";
        $sql.= ", data_movimentazione";
        $sql.= ") VALUES (";
        $sql.= "'" . $code_move . "'";
        $sql.= ", '" . $id_ddt . "'";
        $sql.= ", CURRENT_TIMESTAMP()";
        $sql.= ")";
        $res_insert = $this->db->query($sql);
        return $res_insert;
    }
    
}