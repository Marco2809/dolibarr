<!doctype html>

<head>
  <meta charset="utf-8">
  <title>Aggiungi prodotti da movimentare</title>
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
          <th><span style="font-size:20px; color:#8ec25a;">Allega a Movimento - Prodotti</span></th>
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
        <select name="ricerca_per" style="margin-top:-25px;">
          <option value="ric_prodotto">Codice prodotto</option>
          <option value="ric_libera">Ricerca libera</option>
          <option value="ric_produttore">Codice produttore</option>
          <!--  <option value="ric_etichetta">Etichetta</option> -->
        </select>
      </td>
      <td>
        <input id="nome" class="shadows" placeholder="" type="text" name="run_ricerca"style="width:60%;"/>
        <input type="submit" value="Cerca"/>
      </td>
      <tr>
        <td><input type="submit" name="annulla" value="Annulla" style="height:30px; width:200px;"/></td>
        <td><input type="submit" name="fine" value="Fine" style="height:30px; width:200px;"/></td>
      </tr>
      </thead>
    </table>
     <center>  <td><input type="submit" name="add_prod" value="Aggiungi selezione" style="height:30px; width:200px;"/></td></center>

    <?php
    require '../main.inc.php';
    $user_id = $user->id;
    require_once DOL_DOCUMENT_ROOT . '/product/myclass/myScortaprodotto.php'; // serve per il ddt
    require DOL_DOCUMENT_ROOT . '/product/tmpl_popup/tmpl_prodotti_ric.php';

    $action_tipo_ricerca = isset($_REQUEST['ricerca_per']) ? $_REQUEST['ricerca_per'] : null;

    $prod_selezionati = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
    if (!empty($prod_selezionati)) // se sono stati gi?? inseriti asset
    {
        if ($prod_selezionati == "prodotti_select")
        {
            $sql_1 = "SELECT * FROM tmp_form WHERE id_user = " . $user_id;
            $result = $db->query($sql_1);
            $obj_prod = $db->fetch_object($result);
            vedi_prodotti_selezionati($db, $user_id, $obj_prod->tmp_magsorg); // permetti di visualizzare
        }
    }

    //aggiungi selezione
    $add_asset = isset($_REQUEST['add_prod']) ? $_REQUEST['add_prod'] : null;
    if (!empty($add_asset)) // se sono stati gi?? inseriti asset
    {
        if ($add_asset == "Aggiungi selezione")
        {
            $path = DOL_URL_ROOT . '/product/popup_prodotti.php?mainmenu=products';
            print '<META HTTP-EQUIV="Refresh" CONTENT="0; url=' . $path . '">';
        }
    }


    $pulsante_annulla = isset($_REQUEST['annulla']) ? $_REQUEST['annulla'] : null;
    if (!empty($pulsante_annulla)) // se sono stati gi?? inseriti asset
    {
        if ($pulsante_annulla == "Annulla")
        {
            $var = "";
            $redir = DOL_URL_ROOT . '/product/movimentazione.php?mainmenu=products';
            echo '<script>opener.location.href=' . '"' . $redir . $var . '"' . ';self.close();</script>';
        }
    }
    
    $pulsante_chiudi = isset($_REQUEST['fine']) ? $_REQUEST['fine'] : null;
      if (!empty($pulsante_chiudi)) // se sono stati gi?? inseriti asset
      {
          if ($pulsante_chiudi == "Fine")
          {
              $var = "";
              $redir = DOL_URL_ROOT . '/product/movimentazione.php?mainmenu=products';
              echo '<script>opener.location.href=' . '"' . $redir . $var . '"' . ';self.close();</script>';
          }
      }

    if (isset($action_tipo_ricerca))
    {
        //setcookie("codice",$find_cod_prodotto);
        //ricavo il codice magazzino
        $sql = "SELECT * FROM tmp_form WHERE id_user = " . $user_id;
        $result = $db->query($sql);
        $obj = $db->fetch_object($result);
        $run_ricerca = $_REQUEST['run_ricerca'];

        $html_prodotti.= getProdotti_from_ricerca($db, $action_tipo_ricerca, $run_ricerca, $obj->tmp_magsorg);

        $html_prodotti.= "</table>";


        print $html_prodotti;
        $r = 2;
    }

    $redir = DOL_URL_ROOT . '/product/movimentazione.php';
    // prende i selezionati
    $sql = "CREATE TABLE  tmp_prodotti(temp_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,id_user VARCHAR(100), tmp_codiceprodotto VARCHAR(250))";
    $result = $db->query($sql);
    $query = "SELECT COUNT(*) AS count FROM tmp_prodotti WHERE id_user = " . $user_id;
    $res = $db->query($query);
    if ($res)
    {
        $obj_tot = $db->fetch_object($res);
        if (isset($_REQUEST['checkbox_prodotti']))
        {
            if ($obj_tot->count == 0)
            {
                $user_id = $user->id;
                $encode = json_encode($_REQUEST['checkbox_prodotti']);
                $sql = "INSERT INTO tmp_prodotti(";
                $sql.= "tmp_codiceprodotto";
                $sql.= ",id_user";
                $sql.= ") VALUES (";
                $sql.= "'" . $encode . "'";
                $sql.= ",'" . $user_id . "'";
                $sql.= ")";
                $inserito = $db->query($sql);
            } else
            { // esiste gi?? la tabella, occorre fare update 
                $query = "SELECT tmp_codiceprodotto FROM tmp_prodotti ";
                $res = $db->query($query);
                $obj = $db->fetch_object($res);
                $array_assetDB = json_decode($obj->tmp_codiceprodotto);
                $encode = json_encode($_REQUEST['checkbox_prodotti']);
                $merge_checkbox = array_merge($array_assetDB, $_REQUEST['checkbox_prodotti']);
                $merge_checkbox = array_unique($merge_checkbox);
                $merge_checkbox = array_values($merge_checkbox);

                $encode = json_encode($merge_checkbox);

                $sql = "UPDATE tmp_prodotti SET tmp_codiceprodotto=" . "'" . $encode . "'";
                $sql .= " WHERE id_user = " . $user_id;
                $db->query($sql);
            }
        }
        if ($obj_tot->count > 0)
        {
            $root = DOL_URL_ROOT;
            $sql_2 = "SELECT * FROM tmp_form WHERE id_user = " . $user_id;
            $result = $db->query($sql_2);
            $obj_prod_2 = $db->fetch_object($result);
            vedi_prodotti_selezionati($db, $user_id, $obj_prod_2->tmp_magsorg);
        }
    }
    ?>

  </form>
</body>

