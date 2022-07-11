

<?php

function vedi_asset_selezionati($db, $user_id = 0)
{
    $query = "SELECT tmp_idasset FROM tmp_asset WHERE id_user = " . $user_id;
    $res = $db->query($query);
    if ($res)
    {
        $obj = $db->fetch_object($res);
        $array_assetDB = json_decode($obj->tmp_idasset);
        if (!empty($array_assetDB))
        {
            print '<table>';
            print '<thead>';
            print '<tr>';
           // print '<th><span style="color:#8ec25a;">Seleziona</span></th>';
            print '<th><span style="color:#8ec25a;">Codice</span></th>';
            print '<th><span style="color:#8ec25a;">Etichetta</span></th>';
            print '<th><span style="color:#8ec25a;">Stato Fisico</span></th>';
            print '<th><span style="color:#8ec25a;">Stato Tecnico</span></th>';
            print '</tr>';
            print '</thead>';
            $obj_asset = new asset($db);
            for ($i = 0; $i < count($array_assetDB); $i++)
            {
                $codice_asset = $array_assetDB[$i];
                $array_db_asset = $obj_asset->getAsset("", $codice_asset);
                $db_asset = $array_db_asset[0];
                print '<tr ' . true . '>';
                $etichetta = $db_asset['label'];
                $stato_fisico = $db_asset['stato_fisico'];
                $str_stato_fisico = "";
                switch ($stato_fisico)
                {
                    case "1":
                        $str_stato_fisico = "Giacenza";
                        break;
                    case "2":
                        $str_stato_fisico = "In uso";
                        break;
                    case "3":
                        $str_stato_fisico = "In transito";
                        break;
                    case "4":
                        $str_stato_fisico = "In lab";
                        break;
                    case "5":
                        $str_stato_fisico = "Dismesso";
                        break;
                }
                $str_stato_tecnico = "";
                switch ($stato_fisico)
                {
                    case "1":
                        $str_stato_tecnico = "Nuovo";
                        break;
                    case "2":
                        $str_stato_tecnico = "Ricondizionato";
                        break;
                    case "3":
                        $str_stato_tecnico = "Guasto";
                        break;
                    case "4":
                        $str_stato_tecnico = "Sconosciuto";
                        break;
                }
                $stato_tecnico = $db_asset['stato_tecnico'];
                //$riemi_checkbox = '<input type="checkbox" name="checkbox_asset[]" value=' . '"' . $codice_asset . '"' . '> <br>';
                //$riemi_checkbox =  $codice_asset . '"' . '> <br>';

                //print '<td>' . $riemi_checkbox . '</td>';
                print '<td>' . $codice_asset . '</td>';
                print '<td>' . $etichetta . '</td>';
                print '<td>' . $str_stato_fisico . '</td>';
                print '<td>' . $str_stato_tecnico . '</td>';
                print '</tr>';
            }
            print '</table>';
        }
    }
}

function getAsset_from_ricerca($db, $action_tipo_ricerca, $valore_ricercato, $cod_magazzino_scelto)
{
    $obj_asset = new asset($db);
    if ($action_tipo_ricerca == "ric_codeasset")
    {
        $assets = $obj_asset->getAsset("", $valore_ricercato, $cod_magazzino_scelto);
    } else if ($action_tipo_ricerca == "ric_codefamiglia")
    {
        $assets = $obj_asset->getAsset($valore_ricercato, "", $cod_magazzino_scelto);
    } else if ($action_tipo_ricerca == "ric_etichetta")
    {
        $assets = $obj_asset->getAssetFromRicerca("label", $valore_ricercato, $cod_magazzino_scelto);
    } else if ($action_tipo_ricerca == "ric_libera")
    {
        $assets = $obj_asset->getAssetFromRicerca("ric_libera", $valore_ricercato, $cod_magazzino_scelto);
    }
    $riemi_checkbox = "";
    $html = "";
    if (!empty($assets))
    {

        $html = "<table>";
        $html .="<thead>";
        $html .= "<tr>";
        $html .= '<th><span style="color:#8ec25a;">Seleziona</span></th>';
        $html .= '<th><span style="color:#8ec25a;">Codice</span></th>';
        $html .= '<th><span style="color:#8ec25a;">Etichetta</span></th>';
        $html .= '<th><span style="color:#8ec25a;">Stato Fisico</span></th>';
        $html .= '<th><span style="color:#8ec25a;">Stato Tecnico</span></th>';
        $html .= '</tr>';
        $html .='</thead>';
    }

    for ($i = 0; $i < count($assets); $i++)
    {
        $asset = $assets[$i];
        $val_stato_fisico = $asset['stato_fisico'];
        if ($val_stato_fisico == 3)
        { // se è in transito non occorre selezionare 
            continue;
        }
        $cod_asset = $asset['cod_asset'];
        $etichetta = $asset['label'];
        $stato_fisico = "";
        switch ($val_stato_fisico)
        {
            case "1":
                $stato_fisico = "Giacenza";
                break;
            case "2":
                $stato_fisico = "In uso";
                break;
            case "3":
                $stato_fisico = "In transito";
                break;
            case "4":
                $stato_fisico = "In lab";
                break;
        }
        $stato_tecnico = "";
        $val_stato_tecnico = $asset['stato_tecnico'];
        switch ($val_stato_tecnico)
        {
            case "1":
                $stato_tecnico = "Nuovo";
                break;
            case "2":
                $stato_tecnico = "Ricondizionato";
                break;
            case "3":
                $stato_tecnico = "Guasto";
                break;
            case "4":
                $stato_tecnico = "Sconosciuto";
                break;
        }

        $riemi_checkbox = '<input type="checkbox" name="checkbox_asset[]" value=' . '"' . $cod_asset . '"' . '> <br>';
        $html .= '<td>' . $riemi_checkbox . '</td>' . '<td>' . $cod_asset . '</td>' . '<td>' . $etichetta . '</td>' . '<td>' . $stato_fisico . '</td>' . '<td>' . $stato_tecnico . '</td>';
        $html .= "</tr>";
    }

    return $html;
}
