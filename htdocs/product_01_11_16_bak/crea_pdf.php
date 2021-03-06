<?php

class myPdf
{

    private $dati = null;
    private $db = null;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function setDati($dati_form)
    {
        $this->dati = $dati_form;
    }

    public function getCrea()
    {
        require_once DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php';

        $mymagazzino = new magazzino($this->db);
        $id_magsorgente = $this->dati['mag_sorgente'];
        $info_magsorgente = $mymagazzino->getMagazzino($id_magsorgente);

        $id_magdestinatario = $this->dati['mag_dest'];
        $magazzino_dest_nome = $mymagazzino->getMagazzino($id_magdestinatario);
        $magazzino_dest_nome = $magazzino_dest_nome[0]['label'];


        if ($magazzino_dest_nome == "Altro")
        {
            $info_magdestinatario = $this->dati['mag_generico']['nome_gen'];
            $dati_mag_generico = "";
            if (isset($this->dati['mag_generico']['rag_sociale']))
            {
                if (!empty($this->dati['mag_generico']['rag_sociale']))
                {
                    $dati_mag_generico = "Ragione sociale: " . $this->dati['mag_generico']['rag_sociale'] . "\n";
                }
            }
            if (isset($this->dati['mag_generico']['indirizzo_gen']))
            {
                if (!empty($this->dati['mag_generico']['indirizzo_gen']))
                {
                    $dati_mag_generico .= "Indirizzo: " . $this->dati['mag_generico']['indirizzo_gen'] . "\n";
                }
            }
            if (isset($this->dati['mag_generico']['citta_gen']))
            {
                if (!empty($this->dati['mag_generico']['citta_gen']))
                {
                    $dati_mag_generico .= "Citta: " . $this->dati['mag_generico']['citta_gen'];
                }
            }
            if (isset($this->dati['mag_generico']['cap_gen']))
            {
                if (!empty($this->dati['mag_generico']['cap_gen']))
                {
                    $dati_mag_generico .= "  " . $this->dati['mag_generico']['cap_gen'];
                }
            }
            if (isset($this->dati['mag_generico']['prov_gen']))
            {
                if (!empty($this->dati['mag_generico']['prov_gen']))
                {
                    $dati_mag_generico .= "  " . $this->dati['mag_generico']['prov_gen'];
                }
            }
            $dati_mag_generico .= "\n";
        } else
        {
            $info_magdestinatario = $mymagazzino->getMagazzino($id_magdestinatario);
        }
        $myasset = new asset($this->db);

        $gli_asset = $this->dati['checkbox_asset'];
        $array_asset = array();
        for ($i = 0; $i < count($gli_asset); $i++)
        {
            $code_asset = $gli_asset[$i];
            $array_asset[] = $myasset->getMyAsset($code_asset);
        }

        //require_once DOL_DOCUMENT_ROOT.'admin/livraison.php';
        $pdf = pdf_getInstance();
        $heightforinfotot = 0; // Height reserved to output the info and total part
        //$heightforfreetext = (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT) ? $conf->global->MAIN_PDF_FREETEXT_HEIGHT : 5); // Height reserved to output the free text on last page
        $pdf->SetAutoPageBreak(1, 0);
        $pdf->AddPage();

        $pdf->SetTextColor(0, 0, 0, 0, false, "white");
        $pdf->MultiCell(0, 0, 'DOCUMENTO DI TRASPORTO', "LTRB", "C", true, 1);
// ---------------------------------------------------------
        $pdf->SetTextColor(0, 0, 0);
// set font

        $pdf->Ln();
// Multicell test
// set color for background
        $x = $pdf->GetX() + 95;
        $y = $pdf->GetY();
        $pdf->MultiCell(90, 7, 'Mittente', "LTR", 1);
// Fit text on cell by reducing font size
//$pdf->MultiCell(90, 7, "Documento di trasporto", "LRT", 1,false,1,$x,$y);
//$pdf->MultiCell(90, 7, "Documento di trasporto", "RL", 1,false,1,$x);
//$pdf->MultiCell(90, 7, "Documento di trasporto", "LR", 1,false,1,$x,$y);
        //$mittente = "Mittente ovvero il magazzino, ?? un testo lungo\ndi conseguenza devi andare a capo, altrimenti ti rompo le scatole";
        $mittente = $info_magsorgente[0]['label'] . "\n" . $info_magsorgente[0]['address'] . "\n" . $info_magsorgente[0]['town'] . " " . $info_magsorgente[0]['zip'];
        $pdf->MultiCell(90, 20, $mittente, "LBR", 1);

        $pdf->Ln(2);

        $causale = "CAUSALE DEL TRAPORTO";
        if (empty($this->dati['causale_trasp']))
        {

            $causale .= "\nMovimentazione per scorta";
        } else
        {
            $causale .= "\n" . $this->dati['causale_trasp'];
        }
        $pdf->MultiCell(90, 12, $causale, "TLBR", 1);

        $pdf->Ln(2);

        $luogo_destinazione = "LUOGO DI DESTINAZIONE";
        $luogo_destinazione .= "\n" . $this->dati['luogo_dest'];
        $pdf->MultiCell(190, 12, $luogo_destinazione, "TLBR", 1);

        $pdf->Ln(2);

        $n = $this->dati['id_ddt'];
        $data = $this->dati['data_oggi'];
        $doc_trasporto = "N: " . $n . " DEL " . $data;

        $pdf->MultiCell(95, 12, "Documento di trasporto" . "\n" . $doc_trasporto, 1, '', 0, 1, 105, 20, true);
        $pdf->Ln(2);
        $y = $pdf->GetY();

        $destinatario = "DESTINATARIO";

        if ($magazzino_dest_nome == "Altro")
        {
            $destinatario .= "\n" . $info_magdestinatario . "\n" . $dati_mag_generico;
        } else
        {
            $destinatario .= "\n" . $info_magdestinatario[0]['label'] . "\n" . $info_magdestinatario[0]['address'] . "\n" . $info_magdestinatario[0]['town'] . " " . $info_magdestinatario[0]['zip'];
        }
        $pdf->MultiCell(95, 28, $destinatario, "TLBR", 1, false, 1, $pdf->GetX() + 95, $y);

        $pdf->Ln(20);

//$pdf->SetFont('timesB', '',10);
        $pdf->SetFont('', '', 10);
        $html = '<table border ="1">';
        $html .= "<tr>";
        $html .= '<td align="center" width="30%">';
        $html .= "<strong>Codice</strong>";
        $html .= "</td>";
        $html .= '<td align="center" width="65%" >';
        $html .= "<strong>Descrizione</strong>";
        $html .= "</td>";
        $html .= '<td align="center" width="5%">';
        $html .= "<strong>Qt</strong>";
        $html .= "</td>";
        $html .= "</tr>";

        $n_asset = count($array_asset);
        for ($i = 0; $i < $n_asset; $i++)
        {
            $asset = $array_asset[$i];
            $html .= '<tr>';
            $html .= '<td height="15">';
            $html .= " " . $asset['cod_asset'];
            $html .= "</td>";

            $html .= '<td height="15">';
            $html .= " " . $asset['label'];
            $html .= "</td>";

            $html .= '<td height="15">';
            $html .= " 1";
            $html .= "</td>";
            $html .= "</tr>";
        }
        $n_prodotti = isset($this->dati['prod_movimentare']) ? count($this->dati['prod_movimentare']) : 0;
        for ($i = 0; $i < $n_prodotti; $i++)
        {
            $mioProdotto = $this->dati['prod_movimentare'][$i];

            $html .= '<tr>';
            $html .= '<td height="15">';
            $html .= " " . $mioProdotto->ref;
            $html .= "</td>";

            $html .= '<td height="15">';
            $html .= " " . $mioProdotto->label;
            $html .= "</td>";

            $html .= '<td height="15">';
            $html .= $mioProdotto->scorta_richiesto;
            $html .= "</td>";
            $html .= "</tr>";
        }
        $n_righe = isset($this->dati['checkbox_altro']) ? count($this->dati['checkbox_altro']) : 0;
        if (isset($this->dati['checkbox_altro']))
        {
            foreach ($this->dati['checkbox_altro'] as $key => $riga)
            {
                $myriga = $this->dati['checkbox_altro'][$key];

                $html .= '<tr>';
                $html .= '<td height="15">';
                $html .= " " . $myriga[0];
                $html .= "</td>";

                $html .= '<td height="15">';
                $html .= " " . $myriga[1];
                $html .= "</td>";

                $html .= '<td height="15">';
                $html .= " " .$myriga[2];
                $html .= "</td>";
                $html .= "</tr>";
            }
        }
        $righe_tot = $n_asset + $n_prodotti + $n_righe;
        $righe_vuote = 15;
        if ( $righe_tot == 15 )
        {
            $righe_vuote = 5;
        }
        else if ($righe_tot > 15 )
        {
            $righe_vuote = 0;
        }
        //aggiungere righe vuote
        for ($i = 0; $i < $righe_vuote; $i++)
        { // stampa righe vuote
            $html .= '<tr>';
            $html .= '<td height="15">';

            $html .= "</td>";

            $html .= '<td height="15">';

            $html .= "</td>";

            $html .= '<td height="15">';

            $html .= "</td>";
            $html .= "</tr>";
        }
        $html .= "</table>";
        $pdf->writeHTML($html, true, false, false);
//$html = "<br><br><br><br><br><br><br><br><br><br>";
//$pdf->SetFont('timesB', '',8);
        $pdf->SetFont('', '', 8);
        $html = '<table>';
        $html .= '<tr>';
        $html .= "<td>";
        $html .= "TRASPORTO A MEZZO: ";
        $html .= "</td>";
        $html .= "<td>";
        $html .= "</td>";
        $html .= '<td>';
        $html .= "DATA RITIRO: " . $this->dati['data_ritiro'];
        $html .= "</td>";
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= "<td>";
        $html .= "</td>";
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= "<td>";
        $html .= "</td>";
        $html .= '</tr>';

        $checked_uno = "";
        $checked_due = "";
        $checked_tre = "";

        switch ($this->dati['trasporto_mezzo'])
        {
            case "1":
                $checked_uno = ' checked="checked"';
                break;
            case "2":
                $checked_due = ' checked="checked"';
                break;
            case "3":
                $checked_tre = ' checked="checked"';
                break;
        }

        $html .= '<tr>';
        $html .= "<td>";
        $html .= '<input type="checkbox" name="mittente" value=' . '"mittente"' . $checked_uno . '>' . " MITTENTE";
        $html .= '<input type="checkbox" name="vettore" value=' . '"vettore"' . $checked_due . '>' . " VETTORE ";
        $html .= '<input type="checkbox" name="destinazione" value=' . '"destinatario"' . $checked_tre . '>' . " DESTINATARIO ";
        $html .= "</td>";
        $html .= '</tr>';
        $html .= '</table>';
        $pdf->writeHTMLCell(0, 0, $pdf->GetX(), $pdf->GetY(), $html, "LTRB", 1);


        $html = '<table>';
        $html .= '<tr>';
        $html .= "<td>";
        $html .= "VETTORE: " . $this->dati['vettore_nota'];
        $html .= "</td>";
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= "<td>";
        $html .= "</td>";
        $html .= '</tr>';
        $html .= '</table>';
        $pdf->writeHTMLCell(0, 0, $pdf->GetX(), $pdf->GetY(), $html, "LTRB", 1);


        $html = '<table>';
        $html .= '<tr>';
        $html .= "<td>";
        $html .= "ANNOTAZIONI: ";
        $html .= $this->dati['annotazioni'];
        $html .= "</td>";



        $html .= '</tr>';
        $html .= '<tr>';
        $html .= "<td>";
        $html .= "</td>";
        $html .= '</tr>';
        $html .= '</table>';
        $pdf->writeHTMLCell(0, 0, $pdf->GetX(), $pdf->GetY(), $html, "LTRB", 1);


        $html = '<table>';
        $html .= '<tr>';
        $html .= "<td>";
        $html .= "FIRMA MITTENTE: ";
        $html .= "</td>";
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= "<td>";
        $html .= "</td>";
        $html .= '</tr>';
        $html .= '</table>';
        $pdf->writeHTMLCell(0, 0, $pdf->GetX(), $pdf->GetY(), $html, "LTRB", 1);

        $html = '<table>';
        $html .= '<tr>';
        $html .= "<td>";
        $html .= "FIRMA VETTORE: ";
        $html .= "</td>";
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= "<td>";
        $html .= "</td>";
        $html .= '</tr>';
        $html .= '</table>';
        $pdf->writeHTMLCell(0, 0, $pdf->GetX(), $pdf->GetY(), $html, "LTRB", 1);

        $html = '<table>';
        $html .= '<tr>';
        $html .= "<td>";
        $html .= "DATA CONSEGNA: ";
        $html .= "</td>";
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= "<td>";
        $html .= "</td>";
        $html .= '</tr>';
        $html .= '</table>';
        $pdf->writeHTMLCell(0, 0, $pdf->GetX(), $pdf->GetY(), $html, "LTRB", 1);


        $html = '<table>';
        $html .= '<tr>';
        $html .= "<td>";
        $html .= "FIRMA DESTINATARIO: ";
        $html .= "</td>";
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= "<td>";
        $html .= "</td>";
        $html .= '</tr>';
        $html .= '</table>';
        $pdf->writeHTMLCell(0, 0, $pdf->GetX(), $pdf->GetY(), $html, "LTRB", 1);

        return $pdf;
    }

}
