<?php

function vedi_prodotti_selezionati($db, $user_id, $id_magazzino)
{


    $query = "SELECT tmp_codiceprodotto FROM tmp_prodotti WHERE id_user = " . $user_id;
    $res = $db->query($query);
    if ($res)
    {

        $obj = $db->fetch_object($res);
        $array_prodottiDB = json_decode($obj->tmp_codiceprodotto);
        if (!empty($array_prodottiDB))
        {
            print '<table>';
            print '<thead>';
            print "<tr>";

            print '<th><span style="color:#8ec25a;">Codice prodotto</span></th>';
            print '<th><span style="color:#8ec25a;">Codice produttore</span></th>';
            print '<th><span style="color:#8ec25a;">Etichetta</span></th>';
            print '<th><span style="color:#8ec25a;">Scorta disponibile</span></th>';
            print"</tr>";
            print '</thead>';
            for ($i = 0; $i < count($array_prodottiDB); $i++)
            {
                $codice_prodotto = $array_prodottiDB[$i];
                $prodotto = getProdotto($db, "rowid", (int) $codice_prodotto);

                $prodotto = $prodotto[0];
                if ($id_magazzino != 0)
                {
                    $obj_scorta_prod = new myScortaprodotto($db);

                    $scorta_mag = $obj_scorta_prod->getScorta($prodotto['rowid'], $id_magazzino);
                    if ($scorta_mag <= 0)
                    {
                        continue;
                    }
                    $etichetta = $prodotto['label'];
                    $codice_produttore = $prodotto['customcode'];
                    // $scorta_disponibile = $prodotto['stock'];
                    $scorta_disponibile = empty($scorta_mag->reel) ? $prodotto['stock'] : $scorta_mag->reel;

                    print '<tr>';
                    print '<td>' . $prodotto['ref'] . '</td>';
                    print '<td>' . $codice_produttore . '</td>';
                    print '<td>' . $etichetta . '</td>';
                    print '<td>' . $scorta_disponibile . '</td>';
                    print '</tr>';
                }
            }
        }
    }
}

function getProdotti_from_ricerca($db, $action_tipo_ricerca, $valore_ricercato, $mag_sorg = 0)
{

    if ($action_tipo_ricerca == "ric_prodotto")
    {
        $prodotti = getProdotto($db, "ref", $valore_ricercato);
        //$assets = $obj_asset->getAsset("", $valore_ricercato, $cod_magazzino_scelto);
    } else if ($action_tipo_ricerca == "ric_produttore")
    {
        $prodotti = getProdotto($db, "customcode", $valore_ricercato);
    } else if ($action_tipo_ricerca == "ric_etichetta")
    {
        $prodotti = getProdotto($db, "label", $valore_ricercato);
    } else if ($action_tipo_ricerca == "ric_libera")
    {
        $prodotti = getProdotto($db, "ric_libera", $valore_ricercato);
    }

    $riemi_checkbox = "";
    $html = "";
    if (!empty($prodotti))
    {
        print '<table>';
        print '<thead>';
        print "<tr>";
        print '<th><span style="color:#8ec25a;">Seleziona</span></th>';
        print '<th><span style="color:#8ec25a;">Codice prodotto</span></th>';
        print '<th><span style="color:#8ec25a;">Codice produttore</span></th>';
        print '<th><span style="color:#8ec25a;">Etichetta</span></th>';
        print '<th><span style="color:#8ec25a;">Scorta disponibile</span></th>';
        print"</tr>";
        print '</thead>';
    }

    for ($i = 0; $i < count($prodotti); $i++)
    {
        $prodotto = $prodotti[$i];
        if ($mag_sorg != 0)
        {
            $obj_scorta_prod = new myScortaprodotto($db);

            $scorta_mag = $obj_scorta_prod->getScorta($prodotto['rowid'], $mag_sorg);
            if ($scorta_mag <= 0)
            {
                continue;
            }
            $etichetta = $prodotto['label'];
            $codice_produttore = $prodotto['customcode'];
            // $scorta_disponibile = $prodotto['stock'];
            $scorta_disponibile = empty($scorta_mag->reel) ? $prodotto['stock'] : $scorta_mag->reel;
            if ($scorta_disponibile <=0) // la scorta sta a limite (cioè minore di 0) non mostrare
            {
                continue;
            }
            $riemi_checkbox = '<input type="checkbox" name="checkbox_prodotti[]" value=' . '"' . $prodotto['rowid'] . '"' . '> <br>';
            $html .= '<td>' . $riemi_checkbox . '</td>' . '<td>' . $prodotto['ref'] . '</td>' . '<td>' . $codice_produttore . '</td>' . '<td>' . $etichetta . '</td>' . '<td>' . $scorta_disponibile . '</td>';
            $html .= "</tr>";
        }
    }
    return $html;
}

function getProdotto($db, $nome_campo = "", $val_ricerca = "")
{
    if (empty($nome_campo))
    {
        return false;
    }
    $like_assegnazione = "LIKE '%" . $val_ricerca . "%'";
    if ($nome_campo == "ric_libera")
    {
        $like_assegnazione = "LIKE '%" . $val_ricerca . "%'";
        $nome_campo = "ref";
    }
    if ($nome_campo == "rowid")
    {
        $like_assegnazione = " = " . $val_ricerca;
    }
    $sql = "SELECT * ";
    $sql .= " FROM " . MAIN_DB_PREFIX . "product as p ";
    $sql .= " WHERE p.$nome_campo $like_assegnazione";
    $res = $db->query($sql);
    if ($res)
    {
        $obj_prodotti = array();
        while ($arr_asset = $res->fetch_array(MYSQLI_ASSOC))
        {
            $obj_prodotti[] = $arr_asset;
        }
        return $obj_prodotti;
    }
    return false;
}
