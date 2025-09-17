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
        
            if ( $porownywarki->produkty[$i]['waga_produktu'] > 0) {
                //$waga = round($porownywarki->produkty[$i]['waga_produktu'], 2);
                $waga = number_format($porownywarki->produkty[$i]['waga_produktu'], 2, ',', ' ') . ' kg';
            } else {
                $waga = '';
            }

            //Pobranie i sprawdzenie ustawienia dostepnosci produktu - specyficzne dla porownywarki
            $dostepnosc = $porownywarki->produkty[$i]['dostepnosc_produktu'];

            if ( isset($tablica_dostepnosci[$porownywarki->produkty[$i]['dostepnosc_produktu']]) && $porownywarki->produkty[$i]['dostepnosc_produktu'] != '0' && $porownywarki->produkty[$i]['dostepnosc_produktu'] != '') {
                $dostepnosc = $tablica_dostepnosci[$porownywarki->produkty[$i]['dostepnosc_produktu']];
            } else {
                $dostepnosc = $porownywarki->dotepnosc_domyslna;
            }

            $DoZapisaniaXML .= "<offer>\n";
            $DoZapisaniaXML .= "    <id>".$porownywarki->produkty[$i]['id_produktu']."</id>\n";

            $DoZapisaniaXML .= "    <name><![CDATA[".$porownywarki->produkty[$i]['nazwa_produktu']."]]></name>\n";
            $DoZapisaniaXML .= "    <brand><![CDATA[".$porownywarki->produkty[$i]['producent_produktu']."]]></brand>\n";
            $DoZapisaniaXML .= "    <description><![CDATA[".$porownywarki->produkty[$i]['opis_produktu']."]]></description>\n";
            $DoZapisaniaXML .= "    <category><![CDATA[".$porownywarki->produkty[$i]['kategoria_produktu']."]]></category>\n";
            $DoZapisaniaXML .= "    <price>".$porownywarki->produkty[$i]['cena_brutto_produktu']."</price>\n";
            if ( $porownywarki->produkty[$i]['cena_stara_produktu'] > 0 ) {
                $DoZapisaniaXML .= "    <oldPrice>".$porownywarki->produkty[$i]['cena_stara_produktu']."</oldPrice>\n";
            } else {
                $DoZapisaniaXML .= "    <oldPrice></oldPrice>\n";
            }
            $DoZapisaniaXML .= "    <url><![CDATA[".$porownywarki->produkty[$i]['url_produktu']."]]></url>\n";
            $DoZapisaniaXML .= "    <image><![CDATA[".$porownywarki->produkty[$i]['zdjecie_produktu']."]]></image>\n";

            // dodatkowe zdjecia
            if ( isset($porownywarki->produkty[$i]['dodatkowe_zdjecia']) && count($porownywarki->produkty[$i]['dodatkowe_zdjecia']) > 0 ) {
                 //
                 $DodatkoweZdjecia = $porownywarki->produkty[$i]['dodatkowe_zdjecia'];
                 $licznik = 2;
                 //
                 foreach ( $DodatkoweZdjecia as $Zdjecie ) {
                    //
                    $DoZapisaniaXML .= "    <image".$licznik."><![CDATA[".$Zdjecie."]]></image".$licznik.">\n";
                    //
                    $licznik++;
                 }
                 //
                 unset($DodatkoweZdjecia);
                 //
            }             

            $DoZapisaniaXML .= "    <attribute name=\"EAN\">".$porownywarki->produkty[$i]['numer_ean_produktu']."</attribute>\n";
            $DoZapisaniaXML .= "    <attribute name=\"code\">".$porownywarki->produkty[$i]['numer_katalogowy_produktu']."</attribute>\n";

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
                         $DoZapisaniaXML .= "    <attribute name=\"" . $key . "\">" . implode(';', (array)$wartosc_txt) . "</attribute>\n";            
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
                         $DoZapisaniaXML .= "    <attribute name=\"" . $key . "\">" . Porownywarki::TekstZamienEncje($value) . "</attribute>\n";
                    }
                    //
                }
                //
            }   
            
            $DoZapisaniaXML .= "    <availability>".$dostepnosc."</availability>\n";
            
            $DoZapisaniaXML .= "    <weight>".$waga."</weight>\n";

            $DoZapisaniaXML .= "</offer>\n";

        }
        // dane do zapisania do pliku END

        // jezeli poczatek pliku
        if ( ( isset($_POST['offset']) && (int)$_POST['offset'] == 0 ) ) {
            ///
            $CoDoZapisania = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            $CoDoZapisania .= "<okazje>\n";
            $CoDoZapisania .= "<offers>\n";

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
             if (isset($_POST['limit_max']) && (int)$_POST['limit_max'] <= (int)$_POST['offset'] + (int)$_POST['limit']) {
                 $CoDoZapisania .= "</offers>\n";
                 $CoDoZapisania .= "</okazje>";
             }
             //
        }
        
        unset($DoZapisaniaXML);
        
    }
 
    if ( isset($ImportZewnetrzny) && isset($ZakonczeniePliku) ) {
         //
         $CoDoZapisania .= "</offers>\n";
         $CoDoZapisania .= "</okazje>";
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