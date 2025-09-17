<?php
if ( isset($_GET['token']) ) {

    chdir('zarzadzanie/');
   
    // wczytanie ustawien inicjujacych system
    require_once( getcwd() . '/ustawienia/init.php' );
    
    // sprawdzi czy jest poprawny plugin i token
    
    $parametry = array();
    $polaczenie_klucz = '';
    
    $porownywarki = array();
    //
    $zapytanie = "SELECT * FROM settings WHERE code LIKE '%INTEGRACJA_ZAUFANEPL%'";
    $sql = $GLOBALS['db']->open_query($zapytanie); 
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

        while ( $info = $sql->fetch_assoc() ) {
          
            $polaczenie_klucz = $info['code'];
            $parametry[$polaczenie_klucz] = $info['value'];
            
        }
        
        unset($info);
        
    }
    
    $db->close_query($sql);
    unset($zapytanie);

    if ( $parametry['INTEGRACJA_ZAUFANEPL_CRON'] == 'tak' && $parametry['INTEGRACJA_ZAUFANEPL_TOKEN'] == $_GET['token'] ) {

        $czas_start = explode(' ', microtime());
        $nazwa_pliku = time();

        $plik = KATALOG_SKLEPU . 'xml/' . $nazwa_pliku . '.csv';
        $fp = fopen($plik, "w");
        flock($fp, 2);

        $NaglowekCsv = 'DATE;ORDER_ID;EMAIL;FIRST_NAME;LAST_NAME;PRODUCT_ID;PRODUCT_NAME'. "\n";
        $CoDoZapisania = '';
        $Separator = ';';
        $Licznik = 0;
        $Wysylka = 'Brak danych do wysłania';
        
        fwrite($fp, $NaglowekCsv);

        $warunki_szukania = '';

        $szukana_wartosc = $filtr->process($parametry['INTEGRACJA_ZAUFANEPL_STATUS_ZAMOWIEN']);

        $warunki_szukania .= " and o.orders_status IN (".$szukana_wartosc.") and osh.orders_status_id IN (".$szukana_wartosc.")";

        $czas_utworzenia_zamowien = time() - ( $filtr->process($parametry['INTEGRACJA_ZAUFANEPL_STATUS_CZAS']) * 3600);
        $szukana_wartosc = date('Y-m-d H:i:s', $czas_utworzenia_zamowien);
        $warunki_szukania .= " and osh.date_added >= '".$szukana_wartosc."'";

        if ( $warunki_szukania != '' ) {
            $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
        }
    
        $ZapytanieZamowienia = "SELECT * FROM orders o
                    LEFT JOIN orders_status_history osh ON osh.orders_id = o.orders_id
                    ".$warunki_szukania." GROUP BY o.orders_id";

        $sqlZamowienia = $db->open_query($ZapytanieZamowienia);
        
        if ((int)$GLOBALS['db']->ile_rekordow($sqlZamowienia) > 0) {

            while ( $infc = $sqlZamowienia->fetch_assoc() ) {

                $WartosciPol = '';

                $zamowienie = new Zamowienie($infc['orders_id']);

                $klient = explode(' ', (string)$zamowienie->klient['nazwa']);

                $eksport = array( 'DATE'                 => strtotime($zamowienie->info['data_zamowienia']),
                                  'ORDER_ID'             => $infc['orders_id'],
                                  'EMAIL'                => $zamowienie->klient['adres_email'],
                                  'FIRST_NAME'           => $klient['0'],
                                  'LAST_NAME'             => $klient['1']
                );  

                $wynik = array();

                foreach ( $zamowienie->produkty as $Klucz => $produkt ) {
          
                    $produkt = array( 'PRODUCT_ID'      => $produkt['products_id'],
                                      'PRODUCT_NAME'    => $produkt['nazwa'] . "\n" );

                    $wynik[] = array_merge($eksport, $produkt);
                }

                foreach ( $wynik as $Key => $pola ) {
                    $WartosciPol .= implode(';', (array)$pola);
                }


                $Licznik++;

                if ( $WartosciPol != '' ) {
                    $CoDoZapisania = $WartosciPol;
                    fwrite($fp, $CoDoZapisania);
                }
                unset($CoDoZapisania, $WartosciPol, $wynik, $produkt);

            }
            
            unset($infc);

        }
        
        $db->close_query($sqlZamowienia);
        
        fclose($fp);

        if ( filesize(KATALOG_SKLEPU .'xml/'.$nazwa_pliku.'.csv') == 0 ) {
            unlink(KATALOG_SKLEPU .'xml/'.$nazwa_pliku.'.csv');
        }

        if ( $Licznik > 0 && $parametry['INTEGRACJA_ZAUFANEPL_FTP'] == 'tak' ) {
            $ch = curl_init();
            $localfile = KATALOG_SKLEPU .'xml/'.$nazwa_pliku.'.csv';
            $remotefile = $nazwa_pliku.'.csv';
            $url = 'ftp://'.rawurlencode($parametry['INTEGRACJA_ZAUFANEPL_FTPLOGIN']).':'.rawurlencode($parametry['INTEGRACJA_ZAUFANEPL_FTPHASLO']).'@'.$parametry['INTEGRACJA_ZAUFANEPL_FTPHOST'].'/'.$remotefile;
            $fpf = fopen($localfile, 'r');
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_UPLOAD, 1);
            curl_setopt($ch, CURLOPT_INFILE, $fpf);
            curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localfile));
            curl_exec ($ch);
            $error_no = curl_errno($ch);
            curl_close ($ch);
            if ($error_no == 0) {
                $Wysylka = 'Plik został wysłany do seriwsu Zaufane.pl';
            } else {
                $Wysylka = 'Błąd przesyłania pliku.';
            }
        }

        echo '<div style="font-size:13px;font-family:Arial,Tahoma;position:absolute;top:20%;left:50%;margin-left:-170px;width:300px;border:1px solid #ccc;text-align:center;padding:20px;line-height:1.5">';
        
        echo '<b style="font-size:18px">Zakonczono generowanie ...</b> <br /> plik <a href="' . ADRES_URL_SKLEPU . '/xml/' . $nazwa_pliku . '.csv">' . $nazwa_pliku . '.csv</a>';
             
        
        echo '<br /><br />Ilosc wyeksportowanych zamówień: ' . $Licznik;   
        
        echo '<br /><br />' . $Wysylka;   

        $czas_koniec = explode(' ', microtime());
        echo '<br /><br />Czas generowania pliku: ' . number_format((($czas_koniec[1] + $czas_koniec[0]) - ($czas_start[1] + $czas_start[0])), 3, '.', '') . ' sek';   
        
        echo '</div>';
                
        unset($Licznik);
        
    } else {
      
        echo '<div style="font-size:13px;font-family:Arial,Tahoma;position:absolute;top:20%;left:50%;margin-left:-170px;width:300px;border:1px solid #ccc;text-align:center;padding:20px;">Brak autoryzacji ....</div>';      
      
    }
    
} else {
  
    echo '<div style="font-size:13px;font-family:Arial,Tahoma;position:absolute;top:20%;left:50%;margin-left:-170px;width:300px;border:1px solid #ccc;text-align:center;padding:20px;">Brak autoryzacji ....</div>';      
  
}
?>