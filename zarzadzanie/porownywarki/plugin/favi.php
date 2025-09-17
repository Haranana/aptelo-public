<?php
if ( !isset($ImportZewnetrzny) ) {

    chdir('../../');
    
    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php'); 
    
}

if ( (isset($_POST['plugin']) && !empty($_POST['plugin']) && isset($_POST['offset']) && (int)$_POST['offset'] > -1 && Sesje::TokenSpr()) || isset($ImportZewnetrzny) ) {

    if ( !isset($ImportZewnetrzny) ) {
      
         $porownywarki = new Porownywarki($_POST['plugin'], $_POST['offset'], $_POST['limit']);
         
    }
    
    $CoDoZapisania = '';

    $plik = KATALOG_SKLEPU . 'xml/' . $filtr->process($_POST['nazwa_pliku']);

    // uchwyt pliku, otwarcie do dopisania
    $fp = fopen($plik, "a");
    
    // blokada pliku do zapisu
    flock($fp, 2);
        
    if ( isset($porownywarki->produkty) && count($porownywarki->produkty) > 0 ) {
      
        // podziel nazwe plugin
        $podzial = explode('__', (string)$_POST['plugin']);
        $_POST['plugin'] = $podzial[0];      

        $tablica_dostepnosci = Porownywarki::TablicaDostepnosci( $_POST['plugin'] );

        $DoZapisaniaXML = '';

        // dane do zapisania do pliku START

        for ( $i = 0, $c = count($porownywarki->produkty); $i < $c; $i++ ) {
        
            // pobranie i sprawdzenie ustawienia dostepnosci produktu - specyficzne dla porownywarki
            $dostepnosc = $porownywarki->produkty[$i]['dostepnosc_produktu'];

            if ( isset($tablica_dostepnosci[$porownywarki->produkty[$i]['dostepnosc_produktu']]) && $porownywarki->produkty[$i]['dostepnosc_produktu'] != '0' && $porownywarki->produkty[$i]['dostepnosc_produktu'] != '') {
                $dostepnosc = $tablica_dostepnosci[$porownywarki->produkty[$i]['dostepnosc_produktu']];
            } else {
                $dostepnosc = $porownywarki->dotepnosc_domyslna;
            }

            if ( $porownywarki->produkty[$i]['ilosc_produktu'] > 0) {
                $ilosc = ' stock="'.round($porownywarki->produkty[$i]['ilosc_produktu'], 0).'"';
            } else {
                $ilosc = '';
            }

            if ( $porownywarki->produkty[$i]['waga_produktu'] > 0) {
                $waga = ' weight="'.round($porownywarki->produkty[$i]['waga_produktu'], 2).'"';
            } else {
                $waga = '';
            }


            $DoZapisaniaXML .= "<o id=\"".$porownywarki->produkty[$i]['id_produktu']."\" url=\"".$porownywarki->produkty[$i]['url_produktu']."\" price=\"".$porownywarki->produkty[$i]['cena_brutto_produktu']."\" avail=\"".$dostepnosc."\" set=\"0\" ".$waga." ".$ilosc." >\n";

            $DoZapisaniaXML .= "<cat>\n";
            $DoZapisaniaXML .= "    <![CDATA[".$porownywarki->produkty[$i]['kategoria_produktu']."]]>\n";
            $DoZapisaniaXML .= "</cat>\n";
            $DoZapisaniaXML .= "<name>\n";
            $DoZapisaniaXML .= "    <![CDATA[".$porownywarki->produkty[$i]['nazwa_produktu']."]]>\n";
            $DoZapisaniaXML .= "</name>\n";
            $DoZapisaniaXML .= "<imgs>\n";
            $DoZapisaniaXML .= "    <main url=\"".$porownywarki->produkty[$i]['zdjecie_produktu']."\" />\n";
            
            // dodatkowe zdjecia
            if ( isset($porownywarki->produkty[$i]['dodatkowe_zdjecia']) && count($porownywarki->produkty[$i]['dodatkowe_zdjecia']) > 0 ) {
                 //
                 $DodatkoweZdjecia = $porownywarki->produkty[$i]['dodatkowe_zdjecia'];
                 //
                 foreach ( $DodatkoweZdjecia as $Zdjecie ) {
                    //
                    $DoZapisaniaXML .= "    <i url=\"" . $Zdjecie . "\" />\n";
                    //
                 }
                 //
                 unset($DodatkoweZdjecia);
                 //
            }             
            
            $DoZapisaniaXML .= "</imgs>\n";
            $DoZapisaniaXML .= "<desc>\n";
            $DoZapisaniaXML .= "    <![CDATA[".$porownywarki->produkty[$i]['opis_produktu']."]]>\n";
            $DoZapisaniaXML .= "</desc>\n";
            $DoZapisaniaXML .= "<attrs>\n";
            $DoZapisaniaXML .= "    <a name=\"Producent\">\n";
            $DoZapisaniaXML .= "        <![CDATA[".$porownywarki->produkty[$i]['producent_produktu']."]]>\n";
            $DoZapisaniaXML .= "    </a>\n";
            $DoZapisaniaXML .= "    <a name=\"Kod_producenta\">\n";
            $DoZapisaniaXML .= "        <![CDATA[".$porownywarki->produkty[$i]['kod_producenta_produktu']."]]>\n";
            $DoZapisaniaXML .= "    </a>\n";
            $DoZapisaniaXML .= "    <a name=\"EAN\">\n";
            $DoZapisaniaXML .= "        <![CDATA[".$porownywarki->produkty[$i]['numer_ean_produktu']."]]>\n";
            $DoZapisaniaXML .= "    </a>\n";
            
            // cechy produktu
            if ( isset($porownywarki->produkty[$i]['cechy']) && count($porownywarki->produkty[$i]['cechy']) > 0 ) {

                foreach ( $porownywarki->produkty[$i]['cechy'] as $key => $value ) {
                    //
                    $wartosc_txt = array();
                    $wartosci = explode(',', (string)$value[1]);
                    foreach ( $wartosci as $wartosc ) {
                        //
                        $wartosc_txt[] = Porownywarki::TekstZamienEncje($porownywarki->PokazWartoscCechy($wartosc));
                        //
                    }
                    //
                    if ( count($wartosc_txt) > 0 ) {
                         $DoZapisaniaXML .= "    <a name=\"" . $key . "\">\n";
                         $DoZapisaniaXML .= "        <![CDATA[" . implode(';', (array)$wartosc_txt) . "]]>\n";
                         $DoZapisaniaXML .= "    </a>\n";               
                    }            
                    //
                    unset($wartosc_txt, $wartosci);
                    //
                }
                //
            }

            // dodatkowe pola do produktu
            if ( isset($porownywarki->produkty[$i]['pola']) && count($porownywarki->produkty[$i]['pola']) > 0 ) {
                //
                foreach ( $porownywarki->produkty[$i]['pola'] as $key => $value ) {
                    //        
                    if ( !empty($value) ) {
                         $DoZapisaniaXML .= "    <a name=\"" . $key . "\">\n";
                         $DoZapisaniaXML .= "        <![CDATA[" . Porownywarki::TekstZamienEncje($value) . "]]>\n";
                         $DoZapisaniaXML .= "    </a>\n";
                    }
                    //
                }
                //
            }            

            $DoZapisaniaXML .= "</attrs>\n";
            $DoZapisaniaXML .= "</o>\n";
            
        }
        // dane do zapisania do pliku END

        // jezeli poczatek pliku
        if ( ( isset($_POST['offset']) && (int)$_POST['offset'] == 0 ) ) {
            ///
            $CoDoZapisania = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
            $CoDoZapisania .= "<offers xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" version=\"1\">\n";
            $CoDoZapisania .= "<group name=\"other\">\n";

            $CoDoZapisania .= $DoZapisaniaXML;
            //
        } else {
            //
            $CoDoZapisania = $DoZapisaniaXML;
            //
        }
        //
        
        // koniec pliku
        if ( !isset($ImportZewnetrzny) ) {
              //
              if ( isset($_POST['limit_max']) && (int)$_POST['limit_max'] <= (int)$_POST['offset'] + (int)$_POST['limit'] ) {
                   $CoDoZapisania .= "</group>\n";
                   $CoDoZapisania .= "</offers>";
              }
              //
        }
        
        unset($DoZapisaniaXML);

    }
    
    if ( isset($ImportZewnetrzny) && isset($ZakonczeniePliku) ) {
         //
         $CoDoZapisania .= "</group>\n";
         $CoDoZapisania .= "</offers>";  
         //
    }        
    
    fwrite($fp, $CoDoZapisania);

    // zapisanie danych do pliku
    flock($fp, 3);
    // zamkniecie pliku
    fclose($fp);

    unset($CoDoZapisania);

}
echo 'OK';

?>