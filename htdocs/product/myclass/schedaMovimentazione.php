<?php

class movimentazione {

    private $db = null;

    public function __construct($database) {

        $this->db = $database;
    }

    /** 
     * metodo che ritorna l'istanza della movimentrazione specificato dal codice movimentazione
     * @param type $codice_mov
     * @return boolean
     */
    public function getMovimentazione($codice_mov) {
        if (empty($codice_mov)) {
            return false;
        }
        $query = "SELECT * FROM " . MAIN_DB_PREFIX . "form_intervento_zoccali";
        $query .= " WHERE codice_mov LIKE '$codice_mov'";
        $res = $this->db->query($query);
        if ($res) {
            $obj = $this->db->fetch_object($res);
            return $obj;
        }
        return null;
    }

}
