<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of myScortaProdotto
 *
 * @author utente
 */
class myScortaprodotto
{

    private $db = null;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function getScorta($fk_prodotto, $fk_magazzino)
    {
        $query = "SELECT * ";
        $query .= " FROM " . MAIN_DB_PREFIX . "product_stock";
        $query .= " WHERE fk_product = " . $fk_prodotto . " AND fk_entrepot = " . $fk_magazzino;
        $res = $this->db->query($query);
        if ($res)
        {
            $obj_stock = $this->db->fetch_object(MYSQLI_ASSOC);
            return $obj_stock;
        }
        return null;
    }

    public function execMovimentazioneProdotto($fk_prodotto, $fk_magazzino_sorgente, $fk_magazzino_destinatario, $qt_richiesta, $user)
    {
        $scorta_prodotto_sorgente = $this->getScorta($fk_prodotto, $fk_magazzino_sorgente);
        $scorta_prodotto_destinatario = $this->getScorta($fk_prodotto, $fk_magazzino_destinatario);
        $post_decremento = (int) ($scorta_prodotto_sorgente->reel - $qt_richiesta);
        if ($post_decremento < 0)
        {
            return false; // non è stato decrementato poiché la scorta richiesta supera la scorta disponibile
        }
        $inc_stock_magdestinatario = (int) ($scorta_prodotto_destinatario->reel + $qt_richiesta);
        // eseguo l'update nella tabella product_stock
        $num = $scorta_prodotto_sorgente->reel;
        if ($num > 0)
        {
            $sql = "UPDATE " . MAIN_DB_PREFIX . "product_stock SET reel = reel + " . $qt_richiesta;
            $sql.= " WHERE fk_entrepot = " . $fk_magazzino_destinatario . " AND fk_product = " . $fk_prodotto;
            $res_update1 = $this->db->query($sql);
            if ($res_update1)
            {
                $sql = "UPDATE " . MAIN_DB_PREFIX . "product_stock SET reel = reel - " . $qt_richiesta;
                $sql.= " WHERE fk_entrepot = " . $fk_magazzino_sorgente . " AND fk_product = " . $fk_prodotto;
                $res_update2 = $this->db->query($sql);
            }
        }
        $price = 0;
        $label = "movimentazione scorta";
        $type = 1; // varia dopo diventa 0
        $fk_origin = 0;
        // ora inserisco i due record (movimentati) nella tabella stock_mouvement
        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "stock_mouvement";
        $sql.= " (datem, fk_product, fk_entrepot, value, type_mouvement, fk_user_author, label, price, fk_origin)";
        $sql.= " VALUES (CURRENT_TIMESTAMP(), " . $fk_prodotto . ", " . $fk_magazzino_sorgente . ", " . "-".$qt_richiesta . ", " . $type . ",";
        $sql.= " " . $user->id . ",";
        $sql.= " '" . $this->db->escape($label) . "',";
        $sql.= " '" . price2num($price) . "',";
        $sql.= " '" . $fk_origin . "'";
        $sql.= ")";
        $result_move1 = $this->db->query($sql);

        if ($result_move1)
        {
            $type = 0;
            $sql = "INSERT INTO " . MAIN_DB_PREFIX . "stock_mouvement";
            $sql.= " (datem, fk_product, fk_entrepot, value, type_mouvement, fk_user_author, label, price, fk_origin)";
            $sql.= " VALUES (CURRENT_TIMESTAMP(), " . $fk_prodotto . ", " . $fk_magazzino_destinatario . ", " .  $qt_richiesta . ", " . $type . ",";
            $sql.= " " . $user->id . ",";
            $sql.= " '" . $this->db->escape($label) . "',";
            $sql.= " '" . price2num($price) . "',";
            $sql.= " '" . $fk_origin . "'";
            $sql.= ")";
            $result_move1 = $this->db->query($sql);
        }
        return true;
    }

}
