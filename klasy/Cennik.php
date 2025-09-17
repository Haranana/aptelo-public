<?php

class Cennik {

  public static function CennikHtml( $zapytanie, $idKategorii ) {
  
    $NazwaKategorii = Kategorie::NazwaKategoriiId( $idKategorii );
  
    // generowanie html
    $CiagWynikowy = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
                     <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">
                     <head>
                        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
                        <style type="text/css">
                            BODY { color:#504f4f; font-size:11px; font-family: Arial, Tahoma, Verdana, sans-serif; margin: 0px; font-weight:normal; }
                            table { width:800px; margin:0px auto; margin-top:10px; border:1px solid #ccc; }
                            a { color:#504f4f; text-decoration:none; font-weight:normal; }
                            a:hover { text-decoration:underline; color:#504f4f; }                            
                            table td { text-align:center; padding:3px; }
                            .naglowek { background:#e7e7e7; font-size:11px; font-weight:bold; }
                            .poprzednia { text-decoration: line-through; }
                            .daneSklepu { font-weight:bold; font-size:120%; }
                            .nazwaKategorii { background:#e7e7e7; font-size:140%; font-weight:bold; text-align:center; }
                        </style>                        
                      </head>
                      <body>';
                     
    $CiagWynikowy .= '<table>';

    // dane firmy
    $CiagWynikowy .= '<tr>
                        <td colspan="4" class="daneSklepu" style="text-align:left">' . DANE_NAZWA_FIRMY_SKROCONA . '</td>
                      </tr>
                      <tr>
                        <td colspan="4" class="daneSklepu" style="text-align:left"><a href="' . ADRES_URL_SKLEPU . '">' . ADRES_URL_SKLEPU . '</a></td>
                      </tr> 
                      <tr>
                        <td colspan="4" class="daneSklepu" style="text-align:left"><a href="mailto:' . INFO_EMAIL_SKLEPU . '">' . INFO_EMAIL_SKLEPU . '</a> <br /> &nbsp;</td>
                      </tr>
                      <tr>
                        <td colspan="4" style="text-align:left">' . $GLOBALS['tlumacz']['CENNIK_NA_DZIEN'] . ' ' . date('d-m-Y H:i',time()) . ' <br /> &nbsp;</td>
                      </tr>'; 

    // nazwa kategorii
    $CiagWynikowy .= '<tr>
                        <td colspan="4" class="nazwaKategorii">' . $NazwaKategorii . '</td>
                      </tr>';                      

    // naglowek tabeli
    $CiagWynikowy .= '<tr>
                        <td class="naglowek">' . $GLOBALS['tlumacz']['NAZWA_PRODUKTU'] . '</td>
                        <td class="naglowek">' . $GLOBALS['tlumacz']['CENA_BRUTTO'] . '</td>
                        <td class="naglowek">' . $GLOBALS['tlumacz']['CENA_NETTO'] . '</td>
                        <td class="naglowek">' . $GLOBALS['tlumacz']['CENA_POPRZEDNIA'] . '</td>
                      </tr>';


    $sql = $GLOBALS['db']->open_query($zapytanie);

    while ($info = $sql->fetch_assoc()) {

        $Produkt = new Produkt( $info['products_id'] );
        
        $CiagWynikowy .= '<tr>
                            <td style="text-align:left">' . $Produkt->info['link_z_domena'] . '</td>';
                            
                            // jezeli produkt ma cene i jest wlaczone wyswietlanie cen dla niezalogowanych
                            if ($Produkt->info['jest_cena'] == 'tak') {
                                
                                $CiagWynikowy .= '
                                <td>' . $GLOBALS['waluty']->WyswietlFormatCeny( $Produkt->info['cena_brutto_bez_formatowania'], $_SESSION['domyslnaWaluta']['id'], true, false) . '</td>
                                <td>' . $GLOBALS['waluty']->WyswietlFormatCeny( $Produkt->info['cena_netto_bez_formatowania'], $_SESSION['domyslnaWaluta']['id'], true, false) . '</td>
                                <td>';

                                if ( $Produkt->info['cena_poprzednia_bez_formatowania'] > 0 ) {
                                    //
                                    $CiagWynikowy .= '<span class="poprzednia">' . $GLOBALS['waluty']->WyswietlFormatCeny( $Produkt->info['cena_poprzednia_bez_formatowania'], $_SESSION['domyslnaWaluta']['id'], true, false) . '</span>';
                                    //
                                } else {
                                    $CiagWynikowy .= '-';
                                }
                                
                                $CiagWynikowy .= '</td>';
                                
                            } else {
                            
                                $CiagWynikowy .= '
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>';                            
                            
                            }
                            
                         $CiagWynikowy .= '</tr>';

        unset($Produkt);

    }

    $CiagWynikowy .= '</table>';

    $CiagWynikowy .= '</body></html>';  
    
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info);    

    header('Content-disposition: attachment; filename=cennik-' . Seo::link_SEO($NazwaKategorii) . '-' . date("d-m-Y") . '.html');
    header('Content-type: text/html');  

    echo $CiagWynikowy;

    unset($CiagWynikowy, $NazwaKategorii);
    
    exit;
  }
  
  public static function CennikPdf( $zapytanie, $idKategorii ) {
  
    $NazwaKategorii = Kategorie::NazwaKategoriiId( $idKategorii );
  
    // generowanie html
    $CiagWynikowy = '<style type="text/css">
                        a { color:#504f4f; text-decoration:none; font-weight:normal; }
                        a:hover { text-decoration:underline; color:#504f4f; }                            
                        table td { text-align:center; padding:3px; }
                        .naglowek { background-color:#e7e7e7; font-weight:bold; }
                        .poprzednia { text-decoration: line-through; }
                        .nazwaKategorii { background-color:#e7e7e7; font-size:140%; font-weight:bold; text-align:center; }
                     </style>';
                     
    $CiagWynikowy .= $GLOBALS['tlumacz']['CENNIK_NA_DZIEN'] . ' ' . date('d-m-Y H:i',time()) . '<br /> <br />';

    // nazwa kategorii
    $CiagWynikowy .= '<table cellspacing="0" cellpadding="3" border="0" style="width:670px">
                          <tr>
                            <td class="nazwaKategorii">' . $NazwaKategorii . '</td>
                          </tr>
                      </table> <br />';                      
                      
    $CiagWynikowy .= '<table cellspacing="0" cellpadding="3" border="0" style="width:670px">';

    // naglowek tabeli
    $CiagWynikowy .= '<tr>
                        <td class="naglowek" style="text-align:left; width:340px">' . $GLOBALS['tlumacz']['NAZWA_PRODUKTU'] . '</td>
                        <td class="naglowek" style="width:110px">' . $GLOBALS['tlumacz']['CENA_BRUTTO'] . '</td>
                        <td class="naglowek" style="width:110px">' . $GLOBALS['tlumacz']['CENA_NETTO'] . '</td>
                        <td class="naglowek" style="width:110px">' . $GLOBALS['tlumacz']['CENA_POPRZEDNIA'] . '</td>
                      </tr>';


    $sql = $GLOBALS['db']->open_query($zapytanie);

    while ($info = $sql->fetch_assoc()) {

        $Produkt = new Produkt( $info['products_id'] );
        
        $CiagWynikowy .= '<tr>
                            <td style="text-align:left; width:340px">' . $Produkt->info['link_z_domena'] . '</td>';
                            
                            // jezeli produkt ma cene i jest wlaczone wyswietlanie cen dla niezalogowanych
                            if ($Produkt->info['jest_cena'] == 'tak') {
                            
                                $CiagWynikowy .= '
                                <td style="width:110px">' . $GLOBALS['waluty']->WyswietlFormatCeny( $Produkt->info['cena_brutto_bez_formatowania'], $_SESSION['domyslnaWaluta']['id'], true, false) . '</td>
                                <td style="width:110px">' . $GLOBALS['waluty']->WyswietlFormatCeny( $Produkt->info['cena_netto_bez_formatowania'], $_SESSION['domyslnaWaluta']['id'], true, false) . '</td>
                                <td style="width:110px">';

                                if ( $Produkt->info['cena_poprzednia_bez_formatowania'] > 0 ) {
                                    //
                                    $CiagWynikowy .= '<span class="poprzednia">' . $GLOBALS['waluty']->WyswietlFormatCeny( $Produkt->info['cena_poprzednia_bez_formatowania'], $_SESSION['domyslnaWaluta']['id'], true, false) . '</span>';
                                    //
                                } else {
                                    $CiagWynikowy .= '-';
                                }
                                
                                $CiagWynikowy .= '</td>';
                            
                            } else {
                            
                                $CiagWynikowy .= '
                                <td style="width:110px">-</td>
                                <td style="width:110px">-</td>
                                <td style="width:110px">-</td>';                            
                            
                            }
                            
                         $CiagWynikowy .= '</tr>';

        unset($Produkt);

    }

    $CiagWynikowy .= '</table>';

    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info);    

    require_once('tcpdf/config/lang/pol.php');
    require_once('tcpdf/tcpdf.php'); 
    
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator('shopGold');
    $pdf->SetAuthor('shopGold');
    $pdf->SetTitle($GLOBALS['tlumacz']['CENNIK']);
    $pdf->SetSubject($GLOBALS['tlumacz']['CENNIK']);
    $pdf->SetKeywords($GLOBALS['tlumacz']['CENNIK']);

    $daneFirmy = explode(PHP_EOL, (string)PDF_DANE_FIRMY);
    $pozostaleDaneFirmy = '';
    for ( $y = 1; $y < count($daneFirmy); $y++ ) {  
        $pozostaleDaneFirmy .= $daneFirmy[$y] . "\n";
    }
    $pdf->SetHeaderData('', '', trim((string)$daneFirmy[0]), $pozostaleDaneFirmy);
    unset($daneFirmy, $pozostaleDaneFirmy);    
    
    $pdf->SetFont('dejavusans', '', 6);

    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', '6'));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    $pdf->AddPage();

    $pdf->SetFont('dejavusans', '', 8);
    
    $pdf->writeHTML($CiagWynikowy, true, false, false, false, '');

    $pdf->Output( 'cennik-' . Seo::link_SEO($NazwaKategorii) . '-' . date("d-m-Y") . '.pdf', 'D');       

    unset($CiagWynikow, $NazwaKategorii);
    
    exit;

  }  
  
  public static function CennikXls( $zapytanie, $idKategorii ) {
  
    $NazwaKategorii = Kategorie::NazwaKategoriiId( $idKategorii );
    
    include_once('programy/Spreadsheet/Excel/Writer.php');

    // tworzymy "plik"
    $workbook = new Spreadsheet_Excel_Writer();
    $workbook->setVersion(8);

    // plik wysyłamy do przeglądarki (generujemy nagłówki)
    $workbook->send( 'cennik-' . Seo::link_SEO($NazwaKategorii) . '-' . date("d-m-Y") . '.xls' );    
    
    // tworzmy arkusz
    $worksheet = $workbook->addWorksheet( $NazwaKategorii );
    $worksheet->setInputEncoding('utf-8');    
    
    $worksheet->setColumn(0,4,25);   

    // formatowanie tekstu danych firmy
    $format_firma = $workbook->addFormat();
    $format_firma->setBold();   
    $format_firma->setSize(12);   

    // nazwa sklepu
    $worksheet->write(0, 0, DANE_NAZWA_FIRMY_SKROCONA, $format_firma);
    
    // adres sklepu
    $worksheet->write(1, 0, ADRES_URL_SKLEPU, $format_firma);
    
    // adres email sklepu
    $worksheet->write(2, 0, INFO_EMAIL_SKLEPU, $format_firma);

    unset($naglowek);
    
    // formatowanie tekstu
    $format_bold = $workbook->addFormat();
    $format_bold->setBold();   
    $format_bold->setHAlign('center');
    $format_bold->setBgColor('gray');
    $format_bold->setColor('white');
    $format_bold->setPattern(6);
    
    // naglowek dla nazwy produktu
    $format_nazwa = $workbook->addFormat(); 
    $format_nazwa->setBold();   
    $format_nazwa->setBgColor('gray');
    $format_nazwa->setColor('white');
    $format_nazwa->setPattern(6);

    // naglowek z nazwami
    $worksheet->write(4, 0, $GLOBALS['tlumacz']['NAZWA_PRODUKTU'], $format_nazwa);
    $worksheet->write(4, 1, $GLOBALS['tlumacz']['CENA_BRUTTO'], $format_bold);
    $worksheet->write(4, 2, $GLOBALS['tlumacz']['CENA_NETTO'], $format_bold);
    $worksheet->write(4, 3, $GLOBALS['tlumacz']['CENA_POPRZEDNIA'], $format_bold);
    
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    $wiersz = 5;
    
    // formatowanie tekstu - centrowanie
    $format_center = $workbook->addFormat(); 
    $format_center->setHAlign('center');    

    // formatowanie tekstu - cena poprzednia - przekreslona
    $format_center_poprzednia = $workbook->addFormat(); 
    $format_center_poprzednia->setHAlign('center');    
    $format_center_poprzednia->setStrikeOut();      

    while ($info = $sql->fetch_assoc()) {

        $Produkt = new Produkt( $info['products_id'] );
        
        if ( $Produkt->info['cena_poprzednia_bez_formatowania'] > 0 ) {
            //
            $CenaPoprzednia = $GLOBALS['waluty']->WyswietlFormatCeny( $Produkt->info['cena_poprzednia_bez_formatowania'], $_SESSION['domyslnaWaluta']['id'], true, false);
            //
        } else {
            $CenaPoprzednia = '-';
        }        
        
        $worksheet->writeUrl($wiersz, 0, ADRES_URL_SKLEPU . '/' . $Produkt->info['adres_seo'], $Produkt->info['nazwa']);
        
        // jezeli produkt ma cene i jest wlaczone wyswietlanie cen dla niezalogowanych
        if ($Produkt->info['jest_cena'] == 'tak') {
                            
            $worksheet->write($wiersz, 1, $GLOBALS['waluty']->WyswietlFormatCeny( $Produkt->info['cena_brutto_bez_formatowania'], $_SESSION['domyslnaWaluta']['id'], true, false), $format_center);
            $worksheet->write($wiersz, 2, $GLOBALS['waluty']->WyswietlFormatCeny( $Produkt->info['cena_netto_bez_formatowania'], $_SESSION['domyslnaWaluta']['id'], true, false), $format_center);
            $worksheet->write($wiersz, 3, $CenaPoprzednia, $format_center_poprzednia); 

          } else {
          
            $worksheet->write($wiersz, 1, '-', $format_center);
            $worksheet->write($wiersz, 2, '-', $format_center);
            $worksheet->write($wiersz, 3, '-', $format_center_poprzednia);  

        }
                           
        unset($CenaPoprzednia);

        unset($Produkt);
        
        $wiersz++;

    }

    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info);      

    $workbook->close();

  }    

} 

?>