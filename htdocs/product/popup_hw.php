<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Aggiungi HW da movimentare</title>
  </head>
  <style>
    body {
        background: #fafafa ;
        color: #444;
        font: 100%/30px 'Helvetica Neue', helvetica, arial, sans-serif;
        text-shadow: 0 1px 0 #fff;
    }

    strong {
        font-weight: bold; 
    }

    em {
        font-style: italic; 
    }

    table {
        background: #f5f5f5;
        border-collapse: separate;
        box-shadow: inset 0 1px 0 #fff;
        font-size: 12px;
        line-height: 24px;
        margin: 30px auto;
        text-align: left;
        width: 800px;
    }	

    th {
        background: linear-gradient(#777, #444);
        border-left: 1px solid #555;
        border-right: 1px solid #777;
        border-top: 1px solid #555;
        border-bottom: 1px solid #333;
        box-shadow: inset 0 1px 0 #999;
        color: #fff;
        font-weight: bold;
        padding: 10px 10px;
        position: relative;
        text-shadow: 0 1px 0 #000;	
    }


    th:first-child {
        border-left: 1px solid #777;	
        box-shadow: inset 1px 1px 0 #999;
    }

    th:last-child {
        box-shadow: inset -1px 1px 0 #999;
    }

    td {
        border-right: 1px solid #fff;
        border-left: 1px solid #e8e8e8;
        border-top: 1px solid #fff;
        border-bottom: 1px solid #e8e8e8;
        padding: 10px 15px;
        position: relative;
        transition: all 300ms;
    }

    td:first-child {
        box-shadow: inset 1px 0 0 #fff;
    }	

    td:last-child {
        border-right: 1px solid #e8e8e8;
        box-shadow: inset -1px 0 0 #fff;
    }	

    tr {
        background: url(http://jackrugile.com/images/misc/noise-diagonal.png);	
    }

    tr:nth-child(odd) td {
        background: #f1f1f1 url(http://jackrugile.com/images/misc/noise-diagonal.png);	
    }

    tr:last-of-type td {
        box-shadow: inset 0 -1px 0 #fff; 
    }

    tr:last-of-type td:first-child {
        box-shadow: inset 1px -1px 0 #fff;
    }	

    tr:last-of-type td:last-child {
        box-shadow: inset -1px -1px 0 #fff;
    }	

  </style>

  <body>
    <form action="">
      <table style="margin-bottom:-20px;">
        <thead>
          <tr>
            <th><span style="font-size:20px; color:#8ec25a;">Allega a Movimento - HW</span></th>
          </tr>
        </thead>
      </table>
      <table>
        <thead>
          <tr>
            <th><span style="color:#8ec25a;">Campo di ricerca</span></th>
            <th><span style="color:#8ec25a;">Ricerca</span></th>
          </tr>
        <td>
          <select  name="ricerca_per" style="margin-top:-25px;">
            <option value="ric_codeasset">Hardware</option>
            <option value="ric_libera">Ricerca Libera</option>
         <!--   <option value = "ric_etichetta">Etichetta</option> -->

          </select>
        </td>
        <td>
          <input id="nome" class="shadows" placeholder="" type="text" name="run_ricerca"style="width:60%;"/>
          <input type="submit"   value="Cerca"/>
        </td>
        <tr>
          <td><input type="submit" name="annulla" value="Annulla" style="height:30px; width:200px;"/></td>
          <td><input type="submit" name="fine" value="Fine" style="height:30px; width:200px;"/></td>
        </tr>
        </thead>
      </table>
        <center>  <td> <input  type="submit" name="add_asset" value="Aggiungi selezione" style="height:30px; width:200px;"/></td>   </center>

      <?php
      require '../main.inc.php';
      $user_id = $user->id;
      
      require DOL_DOCUMENT_ROOT . '/product/myclass/myAsset.php';
      require DOL_DOCUMENT_ROOT . '/product/tmpl_popup/tmpl_asset_ric.php';
      $asset_selezionati = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
      if (!empty($asset_selezionati)) // se sono stati già inseriti asset
      {
          if ($asset_selezionati == "asset_select")
          {
              vedi_asset_selezionati($db, $user_id); // permetti di visualizzare
          }
      }
      //pulsante annulla
      $pulsante_annulla = isset($_REQUEST['annulla']) ? $_REQUEST['annulla'] : null;
      if (!empty($pulsante_annulla)) // se sono stati già inseriti asset
      {
          if ($pulsante_annulla == "Annulla")
          {
              $var = "";
              $redir = DOL_URL_ROOT . '/product/nuovo_intervento.php?mainmenu=products';
              echo '<script>opener.location.href=' . '"' . $redir . $var . '"' . ';self.close();</script>';
          }
      }

      $pulsante_chiudi = isset($_REQUEST['fine']) ? $_REQUEST['fine'] : null;
      if (!empty($pulsante_chiudi)) // se sono stati già inseriti asset
      {
          if ($pulsante_chiudi == "Fine")
          {
              $var = "";
              $redir = DOL_URL_ROOT . '/product/nuovo_intervento.php?mainmenu=products';
              echo '<script>opener.location.href=' . '"' . $redir . $var . '"' . ';self.close();</script>';
          }
      }


      //aggiungi selezione
      $add_asset = isset($_REQUEST['add_asset']) ? $_REQUEST['add_asset'] : null;
      if (!empty($add_asset)) // se sono stati già inseriti asset
      {
          if ($add_asset == "Aggiungi selezione")
          {
              $path = DOL_URL_ROOT . '/product/popup_hw.php?mainmenu=products';
              print '<META HTTP-EQUIV="Refresh" CONTENT="0; url=' . $path . '">';
          }
      }


      $tipo_ricerca = isset($_REQUEST['run_ricerca']) ? $_REQUEST['run_ricerca'] : null;
      if (isset($tipo_ricerca)) // se è stato fatto una ricerca
      {
          //setcookie("codice",$tipo_ricerca);
          //ricavo il codice magazzino
          $sql = "SELECT * FROM tmp_form WHERE id_user = " . $user_id; // id del magazzino del form 
          $result = $db->query($sql);
          $obj = $db->fetch_object($result);
          $cod_magazzino_scelto = $obj->tmp_magsorg;

          //action ricerca
          $action_tipo_ricerca = isset($_REQUEST['ricerca_per']) ? $_REQUEST['ricerca_per'] : "";
          $html = "";
          $run_ricerca = $_REQUEST['run_ricerca'];

          $posizione = strpos($run_ricerca, "'");
          $apostrofo = $run_ricerca[$posizione];
          if ($apostrofo === "'")
          {
              $run_ricerca[$posizione] = "-";
          }

          if (!empty($action_tipo_ricerca))
          {
              //$cod_magazzino_scelto = 43; // da cancellare
              switch ($action_tipo_ricerca)
              {
                  case "ric_codeasset":
                      $html_ricerca .= getAsset_from_ricerca($db, $action_tipo_ricerca, $run_ricerca, $cod_magazzino_scelto);
                      break;

                  case "ric_codefamiglia":
                      $html_ricerca .= getAsset_from_ricerca($db, $action_tipo_ricerca, $run_ricerca, $cod_magazzino_scelto);
                      break;

                  case "ric_etichetta":
                      $html_ricerca .= getAsset_from_ricerca($db, $action_tipo_ricerca, $run_ricerca, $cod_magazzino_scelto);
                      break;

                  case "ric_libera":
                      $html_ricerca .= getAsset_from_ricerca($db, $action_tipo_ricerca, $run_ricerca, $cod_magazzino_scelto);
                      break;
              }
          }

          // $html_ricerca = getAsset_from_ricerca($db, "ric_codeasset", "familytest12-2016000006", 43);
          print $html_ricerca;
      }


      $sql = "CREATE TABLE  tmp_asset(temp_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,id_user VARCHAR(100), tmp_idasset VARCHAR(1000))";
      $result = $db->query($sql);
      $query = "SELECT COUNT(*) AS count FROM tmp_asset WHERE id_user = " . $user_id;
      $res = $db->query($query);
      if ($res)
      {
          $obj_tot = $db->fetch_object($res);
          if (isset($_REQUEST['checkbox_asset']))
          {
              if ($obj_tot->count == 0)
              {
                  $encode = json_encode($_REQUEST['checkbox_asset']);
                  $sql = "INSERT INTO tmp_asset(";
                  $sql.= "tmp_idasset";
                  $sql.= ",id_user";
                  $sql.= ") VALUES (";
                  $sql.= "'" . $encode . "'";
                  $sql.= ",'" . $user_id . "'";
                  $sql.= ")";
                  $inserito = $db->query($sql);
              } else
              { // esiste già la tabella, occorre fare update 
                  $query = "SELECT tmp_idasset FROM tmp_asset WHERE id_user = " . $user_id;
                  $res = $db->query($query);
                  $obj = $db->fetch_object($res);
                  $array_assetDB = json_decode($obj->tmp_idasset);
                  $encode = json_encode($_REQUEST['checkbox_asset']);
                  $merge_checkbox = array_merge($array_assetDB, $_REQUEST['checkbox_asset']);
                  $merge_checkbox = array_unique($merge_checkbox);
                  $merge_checkbox = array_values($merge_checkbox);

                  $encode = json_encode($merge_checkbox);

                  $sql = "UPDATE tmp_asset SET tmp_idasset=" . "'" . $encode . "'";
                  $sql .= " WHERE id_user = " . $user_id;
                  $db->query($sql);
              }
          }
          if ($obj_tot->count > 0)
          {
              //require DOL_DOCUMENT_ROOT . '/product/tmpl_popup/tmpl_asset_ric.php';
             vedi_asset_selezionati($db, $user_id); // permetti di visualizzare

          }
      }
      ?>

    </form>

  </body>
</html>
