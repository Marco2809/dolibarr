<?php

/**
 * Description of myTracking
 *
 * @author utente
 */
class myUtilizzati
{

    private $db = null;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function nuovo_utilizzati($array_tracking)
    {
        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "utilizzati (";
        $sql.= "azione";
        if (isset($array_tracking['old']) && !empty($array_tracking['old']))
        {
            $sql.= ", old";
        }
        if (isset($array_tracking['new']) && !empty($array_tracking['new']))
        {
            $sql.= ", new";
        }
        $sql.= ", user";
        if (isset($array_tracking['riferimento']) && !empty($array_tracking['riferimento']))
        {
            $sql.= ", riferimento";
        }
        if (isset($array_tracking['descrizione']) && !empty($array_tracking['descrizione']))
        {
            $sql.= ", descrizione";
        }
        $sql.= ", codice_asset";
        if (isset($array_tracking['codice_famiglia']) && !empty($array_tracking['codice_famiglia']))
        {
            $sql.= ", codice_famiglia";
        }
        if (isset($array_tracking['etichetta']) && !empty($array_tracking['etichetta']))
        {
            $sql.= ", etichetta";
        }
        $sql.= ", data";
        $sql.= ", ora";
        $sql.= ") VALUES (";
        $sql.= "'" . $array_tracking['azione'] . "'";
        if (isset($array_tracking['old']) && !empty($array_tracking['old']))
        {
            $sql.= ", '" . $array_tracking['old'] . "'";
        }
        if (isset($array_tracking['new']) && !empty($array_tracking['new']))
        {
            $sql.= ", '" . $array_tracking['new'] . "'";
        }

        $sql.= ", '" . $array_tracking['user'] . "'";
        if (isset($array_tracking['riferimento']) && !empty($array_tracking['riferimento']))
        {
            $sql.= ", '" . $array_tracking['riferimento'] . "'";
        }
        if (isset($array_tracking['descrizione']) && !empty($array_tracking['descrizione']))
        {
            $sql.= ", '" . $array_tracking['descrizione'] . "'";
        }

        $sql.= ", '" . $array_tracking['codice_asset'] . "'";
        if (isset($array_tracking['codice_famiglia']) && !empty($array_tracking['codice_famiglia']))
        {
            $sql.= ", '" . $array_tracking['codice_famiglia'] . "'";
        }
        if (isset($array_tracking['etichetta']) && !empty($array_tracking['etichetta']))
        {
            $sql.= ", '" . $array_tracking['etichetta'] . "'";
        }
        $sql.= ", '" . date("d-m-Y") . "'";
        $sql.= ",  CURTIME() ";

        $sql.= ")";
        $res_insert = $this->db->query($sql);
        return $res_insert;
    }
    
    
}