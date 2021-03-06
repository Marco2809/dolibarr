<?php

class magazzino
{

    private $db = null;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function getAssetFromMagazzino($cod_mag, $cod_famiglia)
    {
        if (empty($cod_mag))
        {
            return null;
        }
        if (empty($cod_famiglia))
        {
            return null;
        }
        $sql = "SELECT * ";
        $sql .= " FROM " . MAIN_DB_PREFIX . "asset as a ";
        $sql .= "WHERE a.id_magazzino = " . "'" . $cod_mag . "'" . " AND " . "a.cod_famiglia=" . "'" . $cod_famiglia . "'";
        $res = $this->db->query($sql);
        $assetFamily = array();
        if ($res)
        {
            while ($obj_stTec = $res->fetch_array(MYSQLI_ASSOC))
            {
                $assetFamily[] = $obj_stTec;
            }
        }
        return $assetFamily;
    }

    // metodo che ritorna le statische di un magazizino
    public function getStatMagazzino($cod_mag, $cod_famiglia)
    {
        if (empty($cod_mag))
        {
            return null;
        }
        if (empty($cod_famiglia))
        {
            return null;
        }
        $sql = "SELECT stato_tecnico ";
        $sql .= " FROM " . MAIN_DB_PREFIX . "asset as a ";
        $sql .= " INNER JOIN " . MAIN_DB_PREFIX . "product as f ON a.cod_famiglia = " . "'" . $cod_famiglia . "'";
        $sql .= " WHERE a.id_magazzino = " . "'" . $cod_mag . "'";

        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "asset as a ";
        $sql .= " WHERE a.id_magazzino = " . "'" . $cod_mag . "'" . " AND a.cod_famiglia = " . "'" . $cod_famiglia . "'";

        $res = $this->db->query($sql);
        $stat_tec = array();
        if ($res)
        {
            while ($obj_stTec = $res->fetch_array(MYSQLI_ASSOC))
            {
                $stat_tec[] = $obj_stTec['stato_tecnico'];
            }
            $n = count($stat_tec);
            if ($n == 0)
            {
                return null;
            }
            $nuovo = 0;
            $ricondizionato = 0;
            $guasto = 0;
            $sconosciuto = 0;
            for ($i = 0; $i < $n; $i++)
            {
                $row = $stat_tec[$i];
                switch ($row[0])
                {
                    case '1':
                        $nuovo++;
                        break;
                    case '2':
                        $ricondizionato++;
                        break;
                    case '3':
                        $guasto++;
                        break;
                    case '4':
                        $sconosciuto++;
                        break;
                }
            }
        }

        $rec = array();
        $rec_famiglia = array();
        $rec['nuovo'] = $nuovo;
        $rec['ricondizionato'] = $ricondizionato;
        $rec['guasto'] = $guasto;
        $rec['sconosciuto'] = $sconosciuto;
        //$rec_famiglia = array($cod_famiglia =>$rec);
        return $rec;
    }

    public function getTuttiMagazzino()
    {
        $sql = "SELECT mag.label,mag.rowid ";
        $sql .= " FROM " . MAIN_DB_PREFIX . "entrepot as mag ";
        $sql .= " ORDER BY mag.label asc ";
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

    public function getMagazziniSelect($select = "*", $where_condition = "", $order_by = "")
    {
        if (empty($select))
        {
            $select = "*";
        }
        $sql = "SELECT $select ";
        $sql .= " FROM " . MAIN_DB_PREFIX . "entrepot ";
        $sql .= $where_condition;
        $sql .= $order_by;
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

    /**
     * metodo che ritorner?? soltanto il magazzino dell'user
     */
    public function getMagazzinoUser($da_cercare = "", $campo = "")
    {
        if (empty($campo))
        {
            $campo = "label";
        }
        $sql = "SELECT mag.label,mag.rowid,mag.fk_user ";
        $sql .= " FROM " . MAIN_DB_PREFIX . "entrepot as mag ";
        $sql .= " WHERE $campo LIKE '$da_cercare'";
        $res = $this->db->query($sql);
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
