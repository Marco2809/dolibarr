<?php

// Con public ottengo errore in PHP protected è senza nulla.
class SearchEngineWS
{
private $con;

    public function __construct()
    {

        $this->con = (is_null($this->con)) ? self :: connect() : $this->con;
    }

    static function connect()
    {
        $con = mysql_connect('localhost', 'admin', 'Iniziale1!?');
        // $con = mysql_connect('localhost', 'root', '');
        $db = mysql_select_db('dolibarr', $con);

        return $con;
    }
    
    private function getIdUser($username)
    {
        $sql = "SELECT rowid FROM llx_user WHERE login = '" . $username . "'";
        $qry = mysql_query($sql, $this->con);
        if ($qry)
        {
            $res = mysql_fetch_array($qry);
            $id_user = (int) $res['rowid']; // id record

        }

     return $id_user; // se zero, vuol dire che non è stato trovato, altrimenti l'id del record
    }
    
private function assetsFromMagazzino($id_mag,$stato_fisico)
    {
        $array_assets = array();
        if (empty($id_mag)) // se non riceve nulla in input, non deve eseguire la query
        {
            return $array_assets;
        }
        //stato fisico in giacenza e la condizione
        if($stato_fisico==10) $sql = $sql = "SELECT * FROM llx_asset WHERE stato_fisico = 1 AND (stato_tecnico = 1 OR stato_tecnico=2) AND id_magazzino =".  $id_mag;
        else $sql = "SELECT * FROM llx_asset WHERE stato_fisico = $stato_fisico AND id_magazzino =  $id_mag";
        $qry = mysql_query($sql, $this->con);
        if ($qry)
        {
            $i=0;
            while ($res = mysql_fetch_array($qry))
            {
                if($res['stato_fisico']==1) $res['stato_fisico']= "Giacenza";
                else if($res['stato_fisico']==2) $res['stato_fisico']= "In Uso";
                 else if($res['stato_fisico']==3) $res['stato_fisico']= "In Transito";
                  else if($res['stato_fisico']==4) $res['stato_fisico']= "In Lab";
                   else if($res['stato_fisico']==5) $res['stato_fisico']= "Dismesso";

                if($res['stato_tecnico']==1) $res['stato_tecnico']= "Nuovo";
                else if($res['stato_tecnico']==2) $res['stato_tecnico']= "Ricondizionato";
                 else if($res['stato_tecnicov']==3) $res['stato_tecnico']= "Guasto";
                  else if($res['stato_tecnico']==4) $res['stato_tecnico']= "Sconosciuto";
             
                if($res['data_modifica']=="") $res['data_modifica'] = "NULL";
                $array_assets[$i] = "ASSET?".$res["id"].'?'.$res['cod_asset'].'?'.$res['cod_famiglia'].'?'.$res['label'].'?'.$res['stato_fisico'].'?'.$res['stato_tecnico'].'?'.$res['data_creazione'].'?'.$res['data_modifica'];
                $i++;
            }
        }
        return $array_assets; // elenco degli asset estratti dalla query
    }

      private function prodottiFromMagazzino($id_mag)
    {
    
        $array_products = array();
        if (empty($id_mag)) // se non riceve nulla in input, non deve eseguire la query
        {
            return $array_products;
        }
        //stato fisico in giacenza e la condizione
        $sql = "SELECT * FROM llx_product WHERE fk_product_type = 0 AND stock > 0 AND fk_user_author =  $id_mag";
        $qry = mysql_query($sql, $this->con);
        if ($qry)
        {
            $i=0;
            while ($res = mysql_fetch_array($qry))
            {
             
                if($res['tms']=="") $res['tms'] = "NULL";
                if($res['stock']=="") $res['stock'] = "NULL";
                if($res['desiredstock']=="") $res['desiredstock'] = "NULL";
                $array_products[$i] = "PRODOTTO?".$res['rowid'].'?'.$res['ref'].'?'.$res['tms'].'?'.htmlentities($res['label']).'?'.$res['fk_user_author'].'?'.$res['stock'].'?'.$res['desiredstock'];
                $i++;
            }
        }
        return $array_products; // elenco degli asset estratti dalla query
    }

     public function getAssetConv($username)
    {

        $id_user = $this->getIdUser($username); //ritorna id user (username)
        //$array_id_magazzini_figli = $this->getIdMagazzino($id_user); // ritorna in un array i magazzini (figli del magazzino username)
        $str_asset = "";
        $array_magazzino = $this->assetsFromMagazzino($id_user,3); // ritorna tutti gli asset del magazzino padre
        $str_asset .= implode(";", $array_magazzino); // concatena tutti gli asset come se fossero stringa
        //if(!empty($array_magazzino)) $str_asset .= ",";
        /*if (!empty($array_id_magazzini_figli)) // se non ha figli allora restituisce stringa vuota
        {
            $array_magazzini_figli = array();

            for ($i = 0; $i < count($array_id_magazzini_figli); $i++) // per ogni figlio del magazzino root
            {
                $id_mag_figlio = $array_id_magazzini_figli[$i]; // id magazzino figlio
                $array_magazzini_figli [$id_mag_figlio] = $this->assetsFromMagazzino($id_mag_figlio,3); // ritorna tutti gli asset del figlio
                $str_asset .= implode(",", $array_magazzini_figli [$id_mag_figlio]); // concatena tutti gli asset come se fossero stringa
                $arr=$array_magazzini_figli [$id_mag_figlio];
                if(count($arr)==1) $str_asset .= ",";
            }
        }*/
        if(substr($str_asset, -1, 1)==",") $str_asset=substr($str_asset, 0, -1);
          //return count($array_magazzini_figli);
        return $str_asset; // ritorna gli asset del magazzino padre e figli

        
    }

     public function addToTck($parametri)
    {
        //etichetta,username,riferimento,descrizione,cod_asset,cod_famiglia,sostituisci,cliente,commessa,id_ticket

        //setTracking(label,action,username,riferimento,old,new,descrizione,cod_asset,cod_famiglia,cliente,commessa)

        $arraypar= explode("|",$parametri);
        $id_user = $this->getIdUser($arraypar[1]);
        $sql = "SELECT label FROM llx_entrepot WHERE fk_user = ".$id_user;
        $qry = mysql_query($sql, $this->con);
        $res = mysql_fetch_array($qry);
        $nome_mag = $res['label'];
        $sql = "UPDATE llx_asset SET stato_fisico = 2, id_magazzino = 76 WHERE cod_asset = '" . $arraypar[4] . "'";
        $qry = mysql_query($sql, $this->con);
        if($qry)
        {
		 //setTracking(label,action,username,riferimento,old,new,descrizione,cod_asset,cod_famiglia,cliente,commessa)
        $set1=$this->setTracking($arraypar[0]."|Modifica Stato Fisico|".$arraypar[1]."|".$arraypar[2]."|In Giacenza|In Uso|".$arraypar[3]."|".$arraypar[4]."|".$arraypar[5]."|".$arraypar[7]."|".$arraypar[8]."|".$arraypar[9]);
        $set2=$this->setTracking($arraypar[0]."|Modifica Magazzino|".$arraypar[1]."|".$arraypar[2]."|".$nome_mag."|Utilizzati|".$arraypar[3]."|".$arraypar[4]."|".$arraypar[5]."|".$arraypar[7]."|".$arraypar[8]."|".$arraypar[9]);
    
        if($arraypar[6]==1) {
        $newass=$this->newAsset($arraypar[0]."|Censito Guasto da Ticket|".$arraypar[1]."|".$arraypar[2]."| | |".$arraypar[3]."|".$arraypar[4]."|".$arraypar[5]."|".$arraypar[7]."|".$arraypar[8]"|".$arraypar[9]);
        }
        if(!isset($newass))
        {
            if($set1=="Ok" && $set2=="Ok") $result = "Operazione conclusa";
        }
        else if(isset($newass)&&$newass!="Errore")
        {
            $result = $newass;
        }
        else return "Errore";

        return $result;

        }
        else 

            $result = "Errore query";

/*else if ($arraypar[0]=="PRODOTTO") {

        $sql = "UPDATE llx_product SET stock = stock - 1 WHERE ref = '" . $arraypar[1] . "'";
        $qry = mysql_query($sql, $this->con);
        if($qry)
        {
            $result = "Operazione conclusa";

        $this->setTracking($parametri);
        $this->moveToUtilizzati($parametri);

            
        }
        else 

            $result = "Errore query";

    }
    */
 }

      public function searchP_F_A($label)
    {
        $arraylab=explode(",",$label);
        if($arraylab[0]=="label")
        {
            $sql = "SELECT DISTINCT label FROM llx_asset WHERE label LIKE '%".$arraylab[1]."%'";
            $qry = mysql_query($sql, $this->con);
            if($qry)
            {
                $i=0;
                while ($res = mysql_fetch_array($qry))
                {
                
                    $labels .= $res['label']."?";
                    $i++;
                }
                if(substr($labels, -1, 1)=="?") $labels=substr($labels, 0, -1);
                return $labels;
            }
            else 

                return "Errore query";
        }
        else if($arraylab[0]=="cod_famiglia")
        {
            $sql = "SELECT DISTINCT label FROM llx_asset WHERE cod_famiglia LIKE '%".$arraylab[1]."%'";
            $qry = mysql_query($sql, $this->con);
            if($qry)
            {
               $i=0;
               while ($res = mysql_fetch_array($qry))                
               {
                    $labels .= $res['label']."?";
                    $i++;
                }
                if(substr($labels, -1, 1)=="?") $labels=substr($labels, 0, -1);
                return $labels;
            }
            else 

                 return "Errore query";
            }
            else if($arraylab[0]=="cod_asset")
            {
                $sql = "SELECT * FROM llx_asset WHERE cod_asset = '".$arraylab[1]."'";
            $qry = mysql_query($sql, $this->con);
            if($qry)
            {
               $i=0;
               while ($res = mysql_fetch_array($qry))                
               {
                    $labels .= "update?".$res['label']."?".$res['cod_asset']."?".$res['cod_famiglia']."?".$res['stato_fisico'];
                    $i++;
                }
                return $labels;
            }
            else 

                 return "Errore query";
            }
    }


    public function searchP_F_A2($parametri)
    {



            $arraypar= explode(",",$addasset);
            $username = $arraypar[1];
            if($arraypar[0]=="label")
            {

                $sql = "SELECT max(id),cod_famiglia FROM llx_asset WHERE label = '" . $arraypar[2] . "'";
                $qry = mysql_query($sql, $this->con);

                $res1 = mysql_fetch_array($qry);

      
                $sql1 = "SELECT cod_famiglia, cod_asset,descrizione FROM llx_asset WHERE id = '" . $res1['max(id)'] . "'";
                $qry1 = mysql_query($sql1, $this->con);
            
                $res = mysql_fetch_array($qry1);

                $numerico = 0;
                $max = 0;
                $my_codAsset = $res['cod_asset'];
                $numerico = substr($my_codAsset, -6);
                $numerico = (int) $numerico;

                if ($max == 0) 
                {
                    $max = $numerico;
                }

                if ($max > $numerico) 
                {
                    $numerico = $max;
                }

                $numerico++;
                $prog_num = str_pad($numerico, 6, "0", STR_PAD_LEFT);
                $anno_corrente = date("Y");
                $newCodAsset = $res['cod_famiglia'] . "-" . $anno_corrente . $prog_num;
                $data= date("d-m-Y");
                /*
                $qry2="INSERT INTO llx_asset (cod_famiglia,cod_asset,label,stato_fisico,stato_tecnico,descrizione,id_magazzino,data_creazione) 
                VALUES('".$res['cod_famiglia']."','".$newCodAsset."','".$etichetta."',5,'".$stato_tecnico."','".$res['descrizione']."',$id_mag,'".$data."')";
                */
                return $res['cod_famiglia']."-".$arraypar[1]."-".$newCodAsset."-5-".$arraypar[2]."-".$res['descrizione']."-".$arraypar[0]."-".$data;
            }
            else if ($arraypar[0]=="cod_asset")
            {
                //ricerca puntuale asset in DB su nome asset

            }
    }


    public function getAllFromMag($username)
    {
        $id_user = $this->getIdUser($username); //ritorna id user (usernmae)
        //$array_id_magazzini_figli = $this->getIdMagazzino($id_user); // ritorna in un array i magazzini (figli del magazzino username)
        $str_asset = "";
        if($id_user=="") return "Errore";
        $sql = "SELECT rowid FROM llx_entrepot WHERE fk_user = ".$id_user;
        $qry = mysql_query($sql, $this->con);
        $res = mysql_fetch_array($qry);
        $id_magazzino = $res['rowid'];
        // concatena tutti gli asset come se fossero stringa
         $array_magazzino = $this->assetsFromMagazzino($id_magazzino,10); // ritorna tutti gli asset del magazzino padre
        $str_asset .= implode(";", $array_magazzino);

   if (!empty($array_magazzino)) $str_asset .= ";";
         //$array_prodotti = $this->prodottiFromMagazzino($id_user); 
      
         //$str_asset .= implode(";", $array_prodotti); // concatena tutti gli asset come se fossero stringa
        /*if (!empty($array_id_magazzini_figli)) // se ha figli allora restituisce stringa vuota
        {
            $array_magazzini_figli = array();

            for ($i = 0; $i < count($array_id_magazzini_figli); $i++) // per ogni figlio del magazzino root
            {
                $id_mag_figlio = $array_id_magazzini_figli[$i]; // id magazzino figlio
                $array_magazzini_figli [$id_mag_figlio] = $this->assetsFromMagazzino($id_mag_figlio,1); // ritorna tutti gli asset del figlio
                $str_asset .= implode(",", $array_magazzini_figli [$id_mag_figlio]); // concatena tutti gli asset come se fossero stringa
                $arr=$array_magazzini_figli [$id_mag_figlio];
                if(count($arr)==1) $str_asset .= ",";
            }
        }
        */
        if(substr($str_asset, -1, 1)==","||substr($str_asset, -1, 1)==";") $str_asset=substr($str_asset, 0, -1);
        //$str_asset .= ";";
        return $str_asset; // ritorna gli asset del magazzino padre e figli
    }

    public function newAsset($addasset)
    {
     
         //setTracking(label,action,username,riferimento,old,new,descrizione,cod_asset,cod_famiglia,cliente,commessa)
            $arraypar= explode("|",$addasset);
            if(count($arraypar)>1)
        {
            $sql = "SELECT max(id),cod_famiglia FROM llx_asset WHERE label = '" . $arraypar[0] . "'";
        $qry = mysql_query($sql, $this->con);

        $res1 = mysql_fetch_array($qry);

      
        $sql1 = "SELECT cod_famiglia, cod_asset,descrizione,label FROM llx_asset WHERE id = '" . $res1['max(id)'] . "'";
         $qry1 = mysql_query($sql1, $this->con);
            
             $res = mysql_fetch_array($qry1);

                 $numerico = 0;
                $max = 0;
                    $my_codAsset = $res['cod_asset'];
                    $numerico = substr($my_codAsset, -6);
                    $numerico = (int) $numerico;
                    if ($max == 0) {
                        $max = $numerico;
                    }
                    if ($max > $numerico) {
                        $numerico = $max;
                    }
            $numerico++;
            $prog_num = str_pad($numerico, 6, "0", STR_PAD_LEFT);
            $anno_corrente = date("Y");
            $newCodAsset = $res['cod_famiglia'] . "-" . $anno_corrente . $prog_num;
            $data= date("d-m-Y");
            $id_user = $this->getIdUser($arraypar[2]);
            $sql2 = "SELECT rowid FROM llx_entrepot WHERE fk_user = ".$id_user;
            $qry2 = mysql_query($sql2, $this->con);
            $res2 = mysql_fetch_array($qry2);
            $id_mag = $res2['rowid'];
            
            $sql3="INSERT INTO llx_asset (cod_famiglia,cod_asset,label,stato_fisico,stato_tecnico,descrizione,id_magazzino,data_creazione) 
            VALUES('".$res['cod_famiglia']."','".$newCodAsset."','".$res['label']."',1,3,'".$res['descrizione']."',$id_mag,'".$data."')";
			$qry3 = mysql_query($sql3, $this->con);
            if($qry1&&$qry2&&$qry3){
                       //newAsset($arraypar[0]."|Censito Guasto da Ticket|".$arraypar[1]."|".$arraypar[2]."| | |".$arraypar[3]."|".$arraypar[4]."|".$arraypar[5]."|".$arraypar[7]."|".$arraypar[8]"|".$arraypar[9]);
                //etichetta,username,riferimento,descrizione,cod_asset,cod_famiglia,sostituisci,cliente,commessa,id_ticket
                 $track=$this->setTracking($arraypar[0]."|Censito Guasto da Ticket|".$arraypar[2]."|".$arraypar[3]."| | |".$arraypar[6]."|".$newCodAsset."|".$res['cod_famiglia']."|".$arraypar[10]."|".$arraypar[11]."| ");
                 //setTracking(label,action,username,riferimento,old,new,descrizione,cod_asset,cod_famiglia,cliente,commessa)
                if($track=="Ok") return "Ok,".$newCodAsset;
                else return $track;
            } else return "Errore";


            //return $res['cod_famiglia']."?".$arraypar[0]."?".$newCodAsset."?5?".$res['descrizione']."?".$data;
        //}
        //else if(count($arraypar)>1)
        //{
            /* 
            la stringa in input conterrà tutti i valori da salvare nel DB dopo aver popolato i campi con il primo if di newAsset

            $qry2="INSERT INTO llx_asset (cod_famiglia,cod_asset,label,stato_fisico,stato_tecnico,descrizione,id_magazzino,data_creazione) 
            VALUES('".$res['cod_famiglia']."','".$newCodAsset."','".$etichetta."',5,'".$stato_tecnico."','".$res['descrizione']."',$id_mag,'".$data."')";

            setTracking();
            movetoUtilizzati();
            */
            //return $arraypar[0]."?".$arraypar[1]."?".$arraypar[2]."?".$arraypar[3]."?".$arraypar[4];
        }
        
    }

    public function updateAsset($parametri)
    {
                $arraypar= explode(",",$parametri);
                /*
                $username=$arraypar[0];
                $id_user = $this->getIdUser($username);
                
                $sql = "UPDATE llx_asset SET stato_fisico = 2 AND stato_tecnico = $arraypar[2] AND id_mag = $id_user WHERE cod_asset = '" . $arraypar[1] . "'";
                $qry = mysql_query($sql, $this->con);
                if($qry)
                {
                    $salutation5 = "Operazione conclusa";
                    return $salutation5;
                }
                
                setTracking();
                moveToUtilizzati();
                */
                return $arraypar[0]."?".$arraypar[1]."?".$arraypar[2];

    }

     public function searchByMag($string)
    {

             $arr=explode(",", $string);
        $id_user = $this->getIdUser($arr[0]); //ritorna id user (username)
        //$array_id_magazzini_figli = $this->getIdMagazzino($id_user); // ritorna in un array i magazzini (figli del magazzino username)
        $str_asset = "";
    
        $array_magazzino = $this->assetsFromMagazzinoRicerca($id_user,$arr[1],$arr[2]); // ritorna tutti gli asset del magazzino padre
        $str_asset .= implode(";", $array_magazzino);
         if (!empty($array_magazzino)) $str_asset .= ";";
        //$array_prodotti = $this->prodottiFromMagazzinoRicerca($id_user,$arr[1],$arr[2]); 
        //$str_asset .= implode(";", $array_prodotti);  // concatena tutti gli asset come se fossero stringa
        //if(!empty($array_magazzino)) $str_asset .= ",";
        /*if (!empty($array_id_magazzini_figli)) // se non ha figli allora restituisce stringa vuota
        {
            $array_magazzini_figli = array();

            for ($i = 0; $i < count($array_id_magazzini_figli); $i++) // per ogni figlio del magazzino root
            {
                $id_mag_figlio = $array_id_magazzini_figli[$i]; // id magazzino figlio
                $array_magazzini_figli [$id_mag_figlio] = $this->assetsFromMagazzino($id_mag_figlio,3); // ritorna tutti gli asset del figlio
                $str_asset .= implode(",", $array_magazzini_figli [$id_mag_figlio]); // concatena tutti gli asset come se fossero stringa
                $arr=$array_magazzini_figli [$id_mag_figlio];
                if(count($arr)==1) $str_asset .= ",";
            }
        }*/
        if(substr($str_asset, -1, 1)==","||substr($str_asset, -1, 1)==";") $str_asset=substr($str_asset, 0, -1);
          //return count($array_magazzini_figli);
        return $str_asset; // ritorna gli asset del magazzino padre e figli
    }

    private function assetsFromMagazzinoRicerca($id_mag,$ricerca,$parametro)
    {
        $array_assets = array();
        if (empty($id_mag)) // se non riceve nulla in input, non deve eseguire la query
        {
            return $array_assets;
        }
        //stato fisico in giacenza e la condizione
        $sql = "SELECT * FROM llx_asset WHERE stato_fisico = 1 AND (stato_tecnico = 1 OR stato_tecnico=2) AND id_magazzino =  $id_mag AND $ricerca LIKE '%".$parametro."%'";
        $qry = mysql_query($sql, $this->con);
        if ($qry)
        {
            $i=0;
            while ($res = mysql_fetch_array($qry))
            {
                if($res['stato_fisico']==1) $res['stato_fisico']= "Giacenza";
                else if($res['stato_fisico']==2) $res['stato_fisico']= "In Uso";
                 else if($res['stato_fisico']==3) $res['stato_fisico']= "In Transito";
                  else if($res['stato_fisico']==4) $res['stato_fisico']= "In Lab";
                   else if($res['stato_fisico']==5) $res['stato_fisico']= "Dismesso";

                if($res['stato_tecnico']==1) $res['stato_tecnico']= "Nuovo";
                else if($res['stato_tecnico']==2) $res['stato_tecnico']= "Ricondizionato";
                 else if($res['stato_tecnicov']==3) $res['stato_tecnico']= "Guasto";
                  else if($res['stato_tecnico']==4) $res['stato_tecnico']= "Sconosciuto";
             
                if($res['data_modifica']=="") $res['data_modifica'] = "NULL";
                $array_assets[$i] = "ASSET?".$res['id'].'?'.$res['cod_asset'].'?'.$res['cod_famiglia'].'?'.$res['label'].'?'.$res['stato_fisico'].'?'.$res['stato_tecnico'].'?'.$res['data_creazione'].'?'.$res['data_modifica'];
                $i++;
            }
        }
        return $array_assets; // elenco degli asset estratti dalla query
    }

    private function prodottiFromMagazzinoRicerca($id_mag,$ricerca,$parametro)
    {
        if($ricerca == "cod_asset") return "";
        else if ($ricerca == "cod_famiglia") $ricerca = "ref";
        $array_products = array();
        if (empty($id_mag)) // se non riceve nulla in input, non deve eseguire la query
        {
            return $array_products;
        }
        //stato fisico in giacenza e la condizione
        $sql = "SELECT * FROM llx_product WHERE fk_product_type = 0 AND fk_user_author =  $id_mag AND stock > 0 AND $ricerca LIKE '%".$parametro."%'";
        $qry = mysql_query($sql, $this->con);
        if ($qry)
        {
            $i=0;
            while ($res = mysql_fetch_array($qry))
            {
             
                if($res['tms']=="") $res['tms'] = "NULL";
                if($res['stock']=="") $res['stock'] = "NULL";
                if($res['desiredstock']=="") $res['desiredstock'] = "NULL";
                $array_products[$i] = "PRODOTTO?".$res['rowid'].'?'.$res['ref'].'?'.$res['tms'].'?'.htmlentities($res['label']).'?'.$res['fk_user_author'].'?'.$res['stock'].'?'.$res['desiredstock'];
                $i++;
            }
        }
        return $array_products; // elenco degli asset estratti dalla query
    }

    public function setTracking($parametri)
    {
        $arr=explode("|", $parametri);
        $id_mag = $this->getIdUser($arr[2]);
        $action = $arr[1];
        $riferimento = $arr[3];
        $old = $arr[4];
        $label = $arr[0];
        $new = $arr[5];
        $descrizione = $arr[6];
        $cod_asset = $arr[7];
        $cod_famiglia = $arr[8];
        $cliente = $arr[9];
        $commessa = $arr[10];
        $data=  date("d-m-Y");
        $ora= date("H:i:s");
        $id_ticket=$arr[11];
  
        $qry="INSERT INTO llx_tracking (azione,user,riferimento,old,new,descrizione,codice_asset,codice_famiglia,etichetta,data,ora,cliente,commessa,id_ticket) 
            VALUES('".$action."','".$id_mag."','".$riferimento."','".$old."','".$new."','".$descrizione."','".$cod_asset."','".$cod_famiglia."','".$label."','".$data."','".$ora."','".$cliente."',$commessa,'".$id_ticket."')";
        $sql_query = mysql_query($qry, $this->con);

        if($qry)
        {
            return "Ok";
        }
        else return "Errore";
    }
/*
        public function moveToUtilizzati($parametri)
    {
        $arr=explode(",", $parametri);
        $id_mag = $this->getIdUser($arr[0]);
        $id_cliente = $arr[1];
        $cliente = $arr[2];
        $idcommessa = $arr[3];
        $data_inizio = $arr[4];
        $data_fine = $arr[5];
        $cod_pro_fam = $arr[6];
        $cod_asset = $arr[7];
        $quantita = $arr[8];
        $id_tracking = $arr[9];

        
        $qry="INSERT INTO storico (id_mag,id_asset,descrizione,id_ticket) 
            VALUES($id_mag,$id_asset,'".$descrizione."',$id_ticket)";
        $sql_query = mysql_query($qry, $this->con);
        
        return $id_mag." - ".$id_asset." - ".$id_cliente." - ".$commessa;
    }
*/
}

/* OPZIONALMENTE: Definire la versione del messaggio soap. Il secondo parametro non è obbligatorio. */
$server = new SoapServer("wsdlengine.wsdl", array('soap_version' => SOAP_1_2, "cache_wsdl" => WSDL_CACHE_NONE));
//$server=new SoapServer("search_engine.wsdl");
$server->setClass("SearchEngineWS");
// Infine la funzione handle processa una richiesta SOAP e manda un messaggio di ritorno 
// al client che l’ha richiesta.
$server->addFunction(SOAP_FUNCTIONS_ALL);
$server->handle();
?>
