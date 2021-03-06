<?php

class asset
{

    private $db = null;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function getAsset($cod_famiglia = "", $cod_asset = "", $id_magazzino = "")
    {

        if (empty($cod_famiglia) && empty($cod_asset))
        {
            return null;
        }
        $sql = "SELECT * ";
        $sql .= " FROM " . MAIN_DB_PREFIX . "asset as a ";
        if (!empty($cod_famiglia))
        {
            $magazzino = "";
            if (!empty($id_magazzino))
            {
                $magazzino = " AND a.id_magazzino = " . $id_magazzino;
            }
            $sql .= "WHERE a.cod_famiglia LIKE " . "'%" . $cod_famiglia . "%'" . $magazzino;
        } else if (!empty($cod_asset))
        {

            $magazzino = "";
            if (!empty($id_magazzino))
            {
                $magazzino = " AND a.id_magazzino = " . $id_magazzino;
            }
            $sql .= "WHERE a.cod_asset LIKE " . "'%" . $cod_asset . "%'" . $magazzino;
        }
        $assets = array();
        $res = $this->db->query($sql);
        if ($res)
        {
            while ($obj_stTec = $res->fetch_array(MYSQLI_ASSOC))
            {
                $assets[] = $obj_stTec;
            }
        }
        return $assets;
    }

    public function getAssetFromFamily($codice_famiglia)
    {
        if (empty($codice_famiglia))
        {
            return null;
        }
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "asset as a ";
        $sql .= " WHERE a.cod_famiglia LIKE '" . $codice_famiglia . "'";
        $sql .= " ORDER BY a.id";
        $res = $this->db->query($sql);
        $assets = array();
        if ($res)
        {
            while ($arr_asset = $res->fetch_array(MYSQLI_ASSOC))
            {
                $assets[] = $arr_asset;
            }
            return $assets;
        }
        return null;
    }

    public function getMyAsset($code_asset = "")
    {
        if (empty($code_asset))
        {
            return false;
        }
        $sql = "SELECT * ";
        $sql .= " FROM " . MAIN_DB_PREFIX . "asset as a ";
        $sql .= " WHERE cod_asset LIKE '" . $code_asset . "'";
        $res = $this->db->query($sql);
        if ($res)
        {
            $obj_asset = $res->fetch_array(MYSQLI_ASSOC);
            return $obj_asset;
        }
        return false;
    }

    public function getAssetFromRicerca($nome_campo = "", $val_ricerca = "", $id_magazzino = "")
    {
        if (empty($nome_campo))
        {
            return false;
        }
        $where_magazzino = "";
        if (!empty($id_magazzino))
        {
            $where_magazzino.= " WHERE a.id_magazzino = " . $id_magazzino;
        }

        if ($nome_campo == "ric_libera")
        {
            $sql = "SELECT * ";
            $sql .= " FROM " . MAIN_DB_PREFIX . "asset as a ";
            $sql .= $where_magazzino;
            if (!empty($val_ricerca))
            {
                $and = " AND ( ";
                $sql .= $and . " label LIKE '%" . $val_ricerca . "%'";
                $sql .= " OR cod_asset LIKE '%" . $val_ricerca . "%'";
                $sql .= " OR cod_famiglia LIKE '%" . $val_ricerca . "%' )";
            }


            $res = $this->db->query($sql);
            if ($res)
            {
                $obj_asset = array();
                while ($arr_asset = $res->fetch_array(MYSQLI_ASSOC))
                {
                    $obj_asset[] = $arr_asset;
                }
                return $obj_asset;
            }
        } else
        {

            // $val_ricerca = "%".$val_ricerca."%";
            $sql = "SELECT * ";
            $sql .= " FROM " . MAIN_DB_PREFIX . "asset as a ";
            $sql .= $where_magazzino;
            if (!empty($nome_campo))
            {
                $sql .= " AND ".$nome_campo . " LIKE '%" . $val_ricerca . "%'";
            }
            $res = $this->db->query($sql);
            if ($res)
            {
                while ($arr_asset = $res->fetch_array(MYSQLI_ASSOC))
                {
                    $obj_asset[] = $arr_asset;
                }
                return $obj_asset;
            }
        }

        return false;
    }

    /**
     * elimina gli asset della famiglia
     * @param type $codice_famiglia il codice della famiglia
     * @return boolean
     */
    public function deleteAsset_fromFamily($codice_famiglia)
    {
        if (empty($codice_famiglia))
        {
            return false;
        }
        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "asset ";
        $sql .= " WHERE cod_famiglia LIKE '" . $codice_famiglia . "'";
        $res = $this->db->query($sql);
        return $res;
    }

    public function eliminaAsset($code_asset)
    {
        if (empty($code_asset))
        {
            return false;
        }
        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "asset ";
        $sql .= " WHERE cod_asset LIKE '" . $code_asset . "'";
        $res = $this->db->query($sql);
        return $res;
    }

    public function deleteAsset_decFamiglia($code_asset)
    {
        if (empty($code_asset))
        {
            return false;
        }
        $asset = $this->getMyAsset($code_asset);
        $stato_fisico_asset = $asset['stato_fisico']; // mi ottengo lo stato fisico per vedere se ?? in uso o dismesso, in uno dei due casi non occore decrementare
        $eliminato = $this->eliminaAsset($code_asset);
        if ($eliminato == false)
        { // se l'asset non ?? stato eliminato 
            return false; // ritorna false;
        }
        $codice_famiglia = $asset['cod_famiglia'];
        // ottengo il valore della scorta.
        if ($stato_fisico_asset != 2 || $stato_fisico_asset != 5)
        { // se non ?? in uso o dismesso, allora decrementa
            $query = "SELECT f.stock as scorta ";
            $query .= " FROM " . MAIN_DB_PREFIX . "product as f ";
            $query .= "WHERE f.ref = " . "'" . $codice_famiglia . "'";
            $result = $this->db->query($query);
            if ($result)
            {
                $obj = $this->db->fetch_object($result);
                $tot_scorte = (int) $obj->scorta; // ottengo il valore della scorta
                if ($tot_scorte > 0)
                { // se il valore della scorta ?? maggiore di zero (ovvero non ?? il primo record)
                    $tot_scorte--; // decremento il valore della scorta
                    $query = "UPDATE " . MAIN_DB_PREFIX . "product ";
                    $query .= "SET stock = " . $tot_scorte;
                    $query .= " WHERE ref LIKE " . "'" . $codice_famiglia . "'";
                    $res = $this->db->query($query); // aggiorno la scorta decrementato di 1
                    return $res;
                }
            }
        }
        return false; // errore aggiornamento
    }

    /*
     * Metodo che controlla lo stato fisico dell'asset.
     * se lo stato fisico ?? 1 o 5 allora non decrementa (return false)
     * 
     */

    /**
     * return 1 (incremento), 0 invariato, -1 (decremento)
     * @param type $stato_fisico_inserito
     */
    public function seIncOrDec($codice_asset, $stato_fisico_inserito)
    {

        if (empty($codice_asset))
        {
            return 0;
        }
        $query = "SELECT a.stato_fisico "; // prendere lo stato fisico
        $query .= " FROM " . MAIN_DB_PREFIX . "asset as a ";
        $query .= "WHERE a.cod_asset LIKE " . "'" . $codice_asset . "'";
        $result = $this->db->query($query);
        if ($result)
        {
            $obj = $this->db->fetch_object($result);
            $stato_fisico_db = (int) $obj->stato_fisico;
        } else
        { // se la query fallisce
            return 0;
        }
        // se arriva qui vuol dire che la query ?? stata eseguita

        if ($stato_fisico_inserito == 2 || $stato_fisico_inserito == 5)
        {
            if ($stato_fisico_db == $stato_fisico_inserito)
            { // se i valori sono uguali vuol dire che non ha modigicato il casmpo dello stato fisico
                return 0;
            } else if ($stato_fisico_db == 2 || $stato_fisico_db == 5)
            {
                return 0;
            }
            return -1;
        } else if ($stato_fisico_db == 2 || $stato_fisico_db == 5)
        { // se lo stato nel db ?? in uso e dismesso // ma quello inserito ?? un altro stato
            if ($stato_fisico_db != $stato_fisico_inserito)
            {
                return 1;
            }
        }


        return 0; // tutti gli altri casi
    }

    public function decrementa_scorta($code_asset)
    {
        $asset = $this->getMyAsset($code_asset);
        $codice_famiglia = $asset['cod_famiglia'];
        $query = "SELECT f.stock as scorta ";
        $query .= " FROM " . MAIN_DB_PREFIX . "product as f ";
        $query .= "WHERE f.ref = " . "'" . $codice_famiglia . "'";
        $result = $this->db->query($query);
        if ($result)
        {
            $obj = $this->db->fetch_object($result);
            $tot_scorte = (int) $obj->scorta; // ottengo il valore della scorta
            if ($tot_scorte > 0)
            { // se il valore della scorta ?? maggiore di zero (ovvero non ?? il primo record)
                $tot_scorte--; // decremento il valore della scorta
                $query = "UPDATE " . MAIN_DB_PREFIX . "product ";
                $query .= "SET stock = " . $tot_scorte;
                $query .= " WHERE ref LIKE " . "'" . $codice_famiglia . "'";
                $res = $this->db->query($query); // aggiorno la scorta decrementato di 1
                return $res;
            }
        }
        return false;
    }

    public function incrementa_scorta($code_asset)
    {

        $asset = $this->getMyAsset($code_asset);
        $codice_famiglia = $asset['cod_famiglia'];
        $query = "SELECT f.stock as scorta ";
        $query .= " FROM " . MAIN_DB_PREFIX . "product as f ";
        $query .= "WHERE f.ref = " . "'" . $codice_famiglia . "'";
        $result = $this->db->query($query);
        if ($result)
        {
            $obj = $this->db->fetch_object($result);
            $tot_scorte = (int) $obj->scorta; // ottengo il valore della scorta
            if ($tot_scorte >= 0)
            { // se il valore della scorta ?? maggiore di zero (ovvero non ?? il primo record)
                $tot_scorte++; // decremento il valore della scorta
                $query = "UPDATE " . MAIN_DB_PREFIX . "product ";
                $query .= "SET stock = " . $tot_scorte;
                $query .= " WHERE ref LIKE " . "'" . $codice_famiglia . "'";
                $res = $this->db->query($query); // aggiorno la scorta decrementato di 1
                return $res;
            }
        }
        return false;
    }

    public function getAssetFromMagazzino($id_magazzino = 0, $codice_famiglia = "")
    {
        if (empty($id_magazzino))
        {
            return null;
        }
        $sql = "SELECT * ";
        $sql .= " FROM " . MAIN_DB_PREFIX . "asset as a ";
        $sql .= " WHERE id_magazzino = " . $id_magazzino;
        if (!empty($codice_famiglia))
        {
            $sql .= " AND cod_famiglia LIKE '" . $codice_famiglia . "'";
        }
        $res = $this->db->query($sql);
        $assets = array();
        if ($res)
        {
            $nuovo = 0;
            $ricondizionato = 0;
            $altro = 0;
            while ($row = $res->fetch_object())
            {
                if ($row->stato_fisico == 1)
                {
                    if ($row->stato_tecnico == 1)
                    {
                        $nuovo++;
                    } else if ($row->stato_tecnico == 2)
                    {
                        $ricondizionato++;
                    } else
                    {
                        $altro++;
                    }
                } else if ($row->stato_fisico != 5 && $row->stato_fisico != 2)
                {
                    $altro++;
                }
                $assets[] = $row;
            }
            if (!empty($assets))
            {

                $array_asset['asset'] = $assets;
                $array_asset['giacenza_nuovo'] = $nuovo;
                $array_asset['giacenza_ricondizionato'] = $ricondizionato;
                $array_asset['altro'] = $altro;
            }
        }
        return $array_asset;
    }

    /**
     * prende come parametro il codice famiglia e restituisce il tot. degli asset in uso.
     * se id_magazzino ?? >0 allora prende il tot. degli asset in uso del magazzino scelto
     * @param type $cod_famiglia
     * @param type $id_magazzino
     * @return boolean
     */
    public function getTotInUso($cod_famiglia = "", $id_magazzino = "")
    {
        if (empty($cod_famiglia))
        {
            return false;
        }
        $query_uso = "SELECT count(*) as in_uso  FROM " . MAIN_DB_PREFIX . "asset"
                . " WHERE cod_famiglia LIKE '$cod_famiglia' AND stato_fisico = 2 ";

        if (!empty($id_magazzino))
        {
            $query_uso .= " AND id_magazzino = " . $id_magazzino;
        }
        $res_update_uso = $this->db->query($query_uso);
        if ($res_update_uso)
        {
            $in_uso_obj = $this->db->fetch_object($res_update_uso);
            $qt_in_uso = (int) $in_uso_obj->in_uso;
            return $qt_in_uso;
        }
    }

    /**
     * prende come parametro il codice famiglia e restituisce il tot. degli asset dismesso.
     * se id_magazzino ?? >0 allora prende il tot. degli asset in uso del magazzino scelto
     * @param type $cod_famiglia
     * @param type $id_magazzino
     * @return boolean
     */
    public function getTotDismesso($cod_famiglia = "", $id_magazzino = "")
    {
        if (empty($cod_famiglia))
        {
            return false;
        }
        $query_uso = "SELECT count(*) as dismesso  FROM " . MAIN_DB_PREFIX . "asset"
                . " WHERE cod_famiglia LIKE '$cod_famiglia' AND stato_fisico = 5 ";

        if (!empty($id_magazzino))
        {
            $query_uso .= " AND id_magazzino = " . $id_magazzino;
        }
        $res_update_dismesso = $this->db->query($query_uso);
        if ($res_update_dismesso)
        {
            $in_uso_obj = $this->db->fetch_object($res_update_dismesso);
            $qt_in_uso = (int) $in_uso_obj->dismesso;
            return $qt_in_uso;
        }
    }

    /**
     * restituisce tutti gli asset che sono presenti nel magazzino
     * @param type $id_magazzino
     */
    public function getTotAssetFromMagazzino($id_magazzino)
    {
        if (empty($id_magazzino))
        {
            return false;
        }
        $query = "SELECT count(*) AS num_asset FROM " . MAIN_DB_PREFIX . "asset";
        $query .= " WHERE id_magazzino = " . $id_magazzino;
        $res = $this->db->query($query);
        if ($res)
        {
            $num_asset_obj = $this->db->fetch_object($res);
            $qt_asset = (int) $num_asset_obj->num_asset;
            return $qt_asset;
        }
        return false;
    }

    public function assetTecnici($codice_asset = "", $id_magazzino)
    {
        if (empty($codice_asset))
        {
            return false;
        }
        if (empty($id_magazzino))
        {
            return false;
        }
        $sql = "SELECT COUNT(*) as count ";
        $sql .= " FROM " . MAIN_DB_PREFIX . "asset as a ";
        $sql .= " WHERE id_magazzino = " . $id_magazzino . " AND cod_asset LIKE '" . $codice_asset . "'";
        $res = $this->db->query($sql);
        if ($res)
        {
            $obj = $this->db->fetch_object($res);
            return $obj->count;
        }
        return false;
    }

    public function qt_asset_stato($codice_famiglia, $where_condition = "")
    {
        if (empty($codice_famiglia))
        {
            return false;
        }
        $sql = "SELECT COUNT(*) as count ";
        $sql .= " FROM " . MAIN_DB_PREFIX . "asset as a ";
        $sql .= $where_condition;
        $res = $this->db->query($sql);
        if ($res)
        {
            $obj = $this->db->fetch_object($res);
            return $obj->count;
        }
        return false;
    }

}
