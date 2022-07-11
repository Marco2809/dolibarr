<?php

class clientiZoccali
{

    private $db = null;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function getTuttiClienti()
    {
        $sql = "SELECT anag.INSEGNA,anag.TERMID ";
        $sql .= " FROM " . MAIN_DB_PREFIX . "zoccali_interventi as anag ";
        $sql .= " ORDER BY anag.INSEGNA asc ";
        $res = $this->db->query($sql);
        $array_magazzino = array();
        if ($res)
        {
            while ($obj_stTec = $res->fetch_array(MYSQLI_ASSOC))
            {
                $array_magazzino[] = $obj_stTec;
            }
        }
        return $array_magazzino;
    }

    public function getCliente($terID)
    {
        $sql = "SELECT * ";
        $sql .= " FROM " . MAIN_DB_PREFIX . "zoccali_interventi as anag ";
        $sql .= " WHERE anag.TERMID LIKE '" . $terID . "'";
        $res = $this->db->query($sql);
        $mymag = array();
        if ($res)
        {
            while ($obj_stTec = $res->fetch_array(MYSQLI_ASSOC))
            {
                $mymag[] = $obj_stTec;
            }
        }
        return $mymag;
    }

    public function getMagazzino($id_mag)
    {
        $sql = "SELECT * ";
        $sql .= " FROM " . MAIN_DB_PREFIX . "entrepot as mag ";
        $sql .= " WHERE mag.rowid = " . $id_mag;
        $res = $this->db->query($sql);
        $mymag = array();
        if ($res)
        {
            while ($obj_stTec = $res->fetch_array(MYSQLI_ASSOC))
            {
                $mymag[] = $obj_stTec;
            }
        }
        return $mymag;
    }

}
