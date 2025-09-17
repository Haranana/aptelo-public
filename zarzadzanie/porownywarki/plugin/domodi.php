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

            $dostepnosc = $porownywarki->produkty[$i]['dostepnosc_produktu'];
            
            if ( isset($tablica_dostepnosci[$porownywarki->produkty[$i]['dostepnosc_produktu']]) && $porownywarki->produkty[$i]['dostepnosc_produktu'] != '0' && $porownywarki->produkty[$i]['dostepnosc_produktu'] != '') {
                 $dostepnosc = $tablica_dostepnosci[$porownywarki->produkty[$i]['dostepnosc_produktu']];
              } else {
                 $dostepnosc = $porownywarki->dotepnosc_domyslna;
            }
            
            $DoZapisaniaXML .= "<offer>\n"; 
            $DoZapisaniaXML .= "<id><![CDATA[" . $porownywarki->produkty[$i]['id_produktu'] . "]]></id>\n";
            $DoZapisaniaXML .= "<url><![CDATA[" . $porownywarki->produkty[$i]['url_produktu'] . "]]></url>\n";
            $DoZapisaniaXML .= "<price><![CDATA[" . $porownywarki->produkty[$i]['cena_brutto_produktu'] . "]]></price>\n";
            $DoZapisaniaXML .= "<brand><![CDATA[" . $porownywarki->produkty[$i]['producent_produktu'] . "]]></brand>\n";
            $DoZapisaniaXML .= "<avail><![CDATA[" . $dostepnosc . "]]></avail>\n";
            $DoZapisaniaXML .= "<cat><![CDATA[" . $porownywarki->produkty[$i]['kategoria_produktu'] . "]]></cat>\n";
            $DoZapisaniaXML .= "<name><![CDATA[" . $porownywarki->produkty[$i]['nazwa_produktu'] . "]]></name>\n";
            
            $DoZapisaniaXML .= "<imgs>\n";
            $DoZapisaniaXML .= "    <img default=\"true\">\n";
            $DoZapisaniaXML .= "         <![CDATA[" . $porownywarki->produkty[$i]['zdjecie_produktu'] . "]]>\n";
            $DoZapisaniaXML .= "    </img>\n";

            // dodatkowe zdjecia
            if ( isset($porownywarki->produkty[$i]['dodatkowe_zdjecia']) && count($porownywarki->produkty[$i]['dodatkowe_zdjecia']) > 0 ) {
                 //
                 $DodatkoweZdjecia = $porownywarki->produkty[$i]['dodatkowe_zdjecia'];
                 //
                 foreach ( $DodatkoweZdjecia as $Zdjecie ) {
                    //
                    $DoZapisaniaXML .= "    <img>\n";
                    $DoZapisaniaXML .= "         <![CDATA[" . $Zdjecie . "]]>\n";
                    $DoZapisaniaXML .= "    </img>\n"; 
                    //
                 }
                 //
                 unset($DodatkoweZdjecia);
                 //
            }             
            
            $DoZapisaniaXML .= "</imgs>\n";

            // promocja
            if ( $porownywarki->produkty[$i]['promocja'] == 'tak' ) {
                 //
                 $DoZapisaniaXML .= "<isPromoted>1</isPromoted>\n";
                 $DoZapisaniaXML .= "<oldprice><![CDATA[" . $porownywarki->produkty[$i]['cena_stara_produktu'] . "]]></oldprice>\n";
                 //
              } else {
                 //
                 $DoZapisaniaXML .= "<isPromoted>0</isPromoted>\n";
                 $DoZapisaniaXML .= "<oldprice><![CDATA[0]]></oldprice>\n";
                 //
            }
                 
            $DoZapisaniaXML .= "<desc>\n";
            $DoZapisaniaXML .= "    <![CDATA[" . $porownywarki->produkty[$i]['opis_produktu'] . "]]>\n";
            $DoZapisaniaXML .= "</desc>\n";

            $DoZapisaniaXML .= "<attrs>\n";
            $DoZapisaniaXML .= "    <attr name=\"Kod_producenta\">\n";
            $DoZapisaniaXML .= "        <![CDATA[" . $porownywarki->produkty[$i]['kod_producenta_produktu'] . "]]>\n";
            $DoZapisaniaXML .= "    </attr>\n";
            
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
                         $DoZapisaniaXML .= "    <attr name=\"" . $key . "\">\n";
                         $DoZapisaniaXML .= "        <![CDATA[" . implode(';', (array)$wartosc_txt) . "]]>\n";
                         $DoZapisaniaXML .= "    </attr>\n";               
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
                         $DoZapisaniaXML .= "    <attr name=\"" . $key . "\">\n";
                         $DoZapisaniaXML .= "        <![CDATA[" . Porownywarki::TekstZamienEncje($value) . "]]>\n";
                         $DoZapisaniaXML .= "    </attr>\n";
                    }
                    //
                }
                //
            }               

            $DoZapisaniaXML .= "</attrs>\n";

            $DoZapisaniaXML .= "</offer>\n";

        }
        // dane do zapisania do pliku END

        // jezeli poczatek pliku
        if ( ( isset($_POST['offset']) && (int)$_POST['offset'] == 0 ) ) {
            ///
            $CoDoZapisania = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
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
                 $CoDoZapisania .= "</offers>";
             }
             //
        }
        
        unset($DoZapisaniaXML);
        
    }
 
    if ( isset($ImportZewnetrzny) && isset($ZakonczeniePliku) ) {
         //
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