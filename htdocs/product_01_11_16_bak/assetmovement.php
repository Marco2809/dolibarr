<?php

class assetmovement
{

    private $dati_form = null;
    private $user = null;
    private $name_fattura = null;

    public function setDatiMovimentazioneAsset($dati, $utente)
    {
        $this->dati_form = $dati;
        $this->user = $utente->login;
        $this->name_fattura = "AST";
        if ($this->user == "solari" || $this->user == "st_solari" || $this->user == "pcm_napoli" || $this->user == "pcm_milano" || $this->user == "tpr")
        {
            $this->name_fattura = "SOL";
        }
    }

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function storeForm()
    {

        //recupero l'ultimo record
        $query = "SELECT max(id) as id FROM " . MAIN_DB_PREFIX . "form_assetmove ";
        $res = $this->db->query($query);
        $obj_lastId = $this->db->fetch_object($res);
        $id = $obj_lastId->id;
        if (empty($id))
        {
            $cod_mov = "M-1";
        } else
        {
            $query = "SELECT codice_mov FROM " . MAIN_DB_PREFIX . "form_assetmove ";
            $query .= " where id = " . $id;
            $res = $this->db->query($query);
            $obj_codemov = $this->db->fetch_object($res);
            $cod_mov = $obj_codemov->codice_mov;
            $cod_mov = substr($cod_mov, 2);
            $cod_mov++;
            $cod_mov = "M-" . $cod_mov;
            //tolgo i primi due caratteri ("m" e "-") e lo assegno ad una varibile, dopo la var va incrementato di uno
        }
        $info_mag_gen = "";
        if (!empty($this->dati_form['mag_generico']))
        {
            $info_mag_gen = json_encode($this->dati_form['mag_generico']);
        }

        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "form_assetmove (";
        $encode_checkbox = implode(",", $this->dati_form['checkbox_asset']);
        $encode_checkbox = "'" . $encode_checkbox . "'";

        $sql.= "mag_sorgente";
        $sql.= ", mag_dest";
        $sql.= ", testo_libero";
        $sql.= ", causale_trasp";
        $sql.= ", trasporto_mezzo";
        $sql.= ", luogo_dest";
        $sql.= ", data_ritiro";
        $sql.= ", annotazioni";
        $sql.= ", checkbox_asset";
        $sql.= ", tipo_vettore";
        $sql.= ", flag";
        $sql.= ", info_mag_altro";

        $sql.= ") VALUES (";
        $sql.= "'" . $this->dati_form['mag_sorgente'] . "'";
        $sql.= ", '" . $this->dati_form['mag_dest'] . "'";
        $sql.= ", '" . $this->dati_form['testo_libero'] . "'";
        $sql.= ", '" . $this->dati_form['causale_trasp'] . "'";
        $sql.= ", '" . $this->dati_form['trasporto_mezzo'] . "'";
        $sql.= ", '" . $this->dati_form['luogo_dest'] . "'";
        $sql.= ", '" . $this->dati_form['data_ritiro'] . "'";
        $sql.= ", '" . $this->dati_form['annotazioni'] . "'";
        $sql.= "," . $encode_checkbox;
        $sql.= ", '" . $this->dati_form['tipo_vettore'] . "'";
        $sql.= ", '" . "0" . "'";
        $sql.= ", '" . $info_mag_gen . "'";
        $sql.= ")";
        $res_insert = $this->db->query($sql);

        if ($res_insert)
        {
            $query = "SELECT LAST_INSERT_ID() as last_id;";
            $res = $this->db->query($query);
            $lastIdAsset = $this->db->fetch_object($res);
            $lastIdAsset = (int) $lastIdAsset->last_id;
            //update la tabella con il codice_movimentazione
            $query = "UPDATE " . MAIN_DB_PREFIX . "form_assetmove SET codice_mov=" . "'" . $cod_mov . "'"; // salvo lo stato fisico in una variabile temporanea in modo che si possa ripristinare
            $query .= " WHERE id  = " . $lastIdAsset;
            $res_insert = $this->db->query($query); //esito aggiornamento

            $last_id = 0;
            if ($lastIdAsset > 0)
            {
                $query = "SELECT id_ddt FROM " . MAIN_DB_PREFIX . "form_assetmove";
                $query .= " WHERE id_ddt LIKE '%$this->name_fattura%'";
                $query .= " ORDER BY id DESC";

                $ris = $this->db->query($query);
                if ($ris)
                {
                    $obj = $this->db->fetch_object($ris);
                    $facnumber = $obj->id_ddt;
                    $last_id_ddt = 0;
                    if (!empty($facnumber))
                    {
                        $sotto = str_replace($this->name_fattura, "", $facnumber);
                        $sotto = str_replace("-", "", $sotto);
                        $sotto = substr($sotto, 0, -4);
                        $last_id_ddt = $sotto;
                    }
                    $last_id_ddt++;
                    $facnumber = $this->name_fattura . "-" . $last_id_ddt . "-" . date("Y");
                    $sql = "UPDATE " . MAIN_DB_PREFIX . "form_assetmove SET id_ddt=" . "'" . $facnumber . "'"; // salvo lo stato fisico in una variabile temporanea in modo che si possa ripristinare
                    $sql .= " WHERE id LIKE '" . $lastIdAsset . "'";
                    $aggiornato = $this->db->query($sql); // ho ripristinato lo stato 
                }
            }

            return $lastIdAsset;
        }
        return 0;
    }

    public function getIdMovimentazione()
    {
        $id_magsorg = $this->dati_form['mag_sorgente'];
        $id_magdest = $this->dati_form['mag_dest'];
        $asset_damovimentare = $this->dati_form['checkbox_asset'];
        $n = count($asset_damovimentare);
        $obj_asset = new asset($this->db);
        $arr_Idmovimentazione = array();
        for ($i = 0; $i < $n; $i++)
        {
            $code_asset_damuovere = $asset_damovimentare[$i];
            $last_insert_id = (int) $this->movimentaAsset($code_asset_damuovere);
            if ($last_insert_id > 0)
            {
                $arr_Idmovimentazione[] = $last_insert_id; // metodo che provveder?? alla movimentazione e torner?? id record inserito
            }
        }
        return $arr_Idmovimentazione; // ritorna un array con gli id delle delle movimentazioni
    }

    private function movimentaAsset($asset_daspostare)
    {
        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "stock_mouvement (";
        $sql.= "tms";
        $sql.= ", fk_product";
        $sql.= ", fk_entrepot";
        $sql.= ", fk_origin";
        $sql.= ") VALUES (";
        $sql.= "CURRENT_TIMESTAMP()";
        $sql.= ", '" . $asset_daspostare . "'";
        $sql.= ", '" . $this->dati_form['mag_dest'] . "'";
        $sql.= ", '" . $this->dati_form['mag_sorgente'] . "'";
        $sql.= ")";
        //CURRENT_TIMESTAMP()
        $res_insert = $this->db->query($sql);
        if ($res_insert)
        {
            $query = "SELECT LAST_INSERT_ID() as last_id;";
            $res = $this->db->query($query);
            $lastIdAsset = $this->db->fetch_object($res);
            $lastIdAsset = $lastIdAsset->last_id;
            return $lastIdAsset;
        }
        return 0;
    }

    public function storeInterventoZoc()
    {
        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "form_intervento_zoccali (";
        $encode_checkbox = implode(",", $this->dati_form['checkbox_asset']);
        $encode_checkbox = "'" . $encode_checkbox . "'";

        $sql.= "codice_mov";
        $sql.= ",mag_sorgente";
        $sql.= ", mag_dest";
        $sql.= ", testo_libero";
        $sql.= ", causale_trasp";
        $sql.= ", trasporto_mezzo";
        $sql.= ", luogo_dest";
        $sql.= ", data_ritiro";
        $sql.= ", annotazioni";
        $sql.= ", checkbox_asset";
        $sql.= ", tipo_vettore";
        $sql.= ", flag";

        $sql.= ") VALUES (";
        $sql.= "'" . $this->dati_form['numero_ordine'] . "'";
        $sql.= ", '" . $this->dati_form['mag_sorgente'] . "'";
        $sql.= ", '" . $this->dati_form['mag_dest'] . "'";
        $sql.= ", '" . $this->dati_form['testo_libero'] . "'";
        $sql.= ", '" . $this->dati_form['causale_trasp'] . "'";
        $sql.= ", '" . $this->dati_form['trasporto_mezzo'] . "'";
        $sql.= ", '" . $this->dati_form['luogo_dest'] . "'";
        $sql.= ", '" . $this->dati_form['data_ritiro'] . "'";
        $sql.= ", '" . $this->dati_form['annotazioni'] . "'";
        $sql.= "," . $encode_checkbox;
        $sql.= ", '" . $this->dati_form['tipo_vettore'] . "'";
        $sql.= ", '" . "1" . "'";
        $sql.= ")";
        $res_insert = $this->db->query($sql);
        return $res_insert;
    }

}
