

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
            print '<th><span style="color:#8ec25a;">Hardware</span></th>';
            print '<th><span style="color:#8ec25a;">DIT</span></th>';
            print '<th><span style="color:#8ec25a;">Totale pezzi disponibile</span></th>';
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
              
                $scorta_totale = $db_asset['scorta_tot'];
                //$riemi_checkbox = '<input type="checkbox" name="checkbox_asset[]" value=' . '"' . $codice_asset . '"' . '> <br>';
                //$riemi_checkbox =  $codice_asset . '"' . '> <br>';

                //print '<td>' . $riemi_checkbox . '</td>';
                print '<td>' . $codice_asset . '</td>';
                print '<td>' . $etichetta . '</td>';
                print '<td>' . $scorta_totale . '</td>';
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
        $html .= '<th><span style="color:#8ec25a;">Hardware</span></th>';
        $html .= '<th><span style="color:#8ec25a;">DIT</span></th>';
        $html .= '<th><span style="color:#8ec25a;">Totale pezzi disponibile</span></th>';
        $html .= '</tr>';
        $html .='</thead>';
    }

    for ($i = 0; $i < count($assets); $i++)
    {
        $asset = $assets[$i];
        $scorta_totale = $asset['scorta_tot'];
        if ($scorta_totale == 0)
        { // se è in transito non occorre selezionare 
            continue;
        }
        $cod_asset = $asset['cod_asset'];
        $etichetta = $asset['label'];
        $scorta_tot = $asset['scorta_tot'];
     

        $riemi_checkbox = '<input type="checkbox" name="checkbox_asset[]" value=' . '"' . $cod_asset . '"' . '> <br>';
        $html .= '<td>' . $riemi_checkbox . '</td>' . '<td>' . $cod_asset . '</td>' . '<td>' . $etichetta . '</td>' . '<td>' . $scorta_tot . '</td>';
        $html .= "</tr>";
    }

    return $html;
}
