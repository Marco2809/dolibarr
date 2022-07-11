<?php

class myMovimentazione
{

    private $db = null;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function getMovimentazione($codice_mov = "", $id_ddt = "", $codice_asset = "")
    {
        $where = "";
        if (!empty($codice_mov))
        {
            $where = "codice_mov LIKE " . "'" . $codice_mov . "'";
        } else if (!empty($id_ddt))
        {
            $where = "codice_mov LIKE " . "'" . $codice_mov . "'";
        } else if (!empty($codice_asset))
        {
            $where = "codice_mov LIKE " . "'" . $codice_mov . "'";
        }

        $query = "SELECT * "; // prendere lo stato fisico
        $query .= " FROM " . MAIN_DB_PREFIX . "form_assetmove ";
        $result = $this->db->query($query);
    }

    public function getAllMovimentazioni()
    {
        $query = "SELECT * "; // prendere lo stato fisico
        $query .= " FROM " . MAIN_DB_PREFIX . "form_assetmove ";
        $result = $this->db->query($query);
        if ($result)
        {
            $array_movimentati = array();
            while ($obj_stTec = $res->fetch_array(MYSQLI_ASSOC))
            {
                $array_movimentati[] = $obj_stTec;
            }
            return $array_movimentati;
        }
        return null;
    }

    public function getMovimentazioniFormMagazzino($id_magazzino, $my_codition = "")
    {
        $query = "SELECT * "; // prendere lo stato fisico
        $query .= " FROM " . MAIN_DB_PREFIX . "form_assetmove ";
        if (!empty($my_codition))
        {
            $query .= $my_codition;
        } else
        {
            $query .= " WHERE mag_sorgente = " . $id_magazzino;
        }
        $result = $this->db->query($query);
        if ($result)
        {
            $array_movimentati = array();
            while ($obj_stTec = $result->fetch_array(MYSQLI_ASSOC))
            {
                $array_movimentati[] = $obj_stTec;
            }
            return $array_movimentati;
        }
    }

    public function getNumeroBolla($codice_asset = "",$id_magazzino = 0, $my_where_condition = "")
    {
        $flag = 0;
        $numero_bolla = "";
        $movimentati = $this->getMovimentazioniFormMagazzino($id_magazzino, $my_where_condition);
        $numero_bolla = "";
        if (!empty($movimentati))
        {
            for ($j = 0; $j < count($movimentati); $j++)
            {
                $movimentazione = $movimentati[$j];
                $encode_checkbox = explode(",", $movimentazione['checkbox_asset']);
                if (!empty($encode_checkbox))
                {
                    $codice_asset_cur =$codice_asset;
                    for ($a = 0; $a < count($encode_checkbox); $a++)
                    {
                        $codice_mov = $encode_checkbox[$a];
                        if ($codice_mov == $codice_asset_cur)
                        {
                            $numero_bolla = $movimentazione['id_ddt'];
                            $flag = 1;
                            break;
                        }
                    }
                }
            }
        }
        return $numero_bolla;
    }

}
