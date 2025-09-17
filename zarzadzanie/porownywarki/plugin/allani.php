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
      
        $DoZapisaniaXML = '';

        // dane do zapisania do pliku START

        for ( $i = 0, $c = count($porownywarki->produkty); $i < $c; $i++ ) {

            $DoZapisaniaXML .= "<product>\n"; 
            $DoZapisaniaXML .= "    <category><![CDATA[" . $porownywarki->produkty[$i]['kategoria_produktu'] . "]]></category>\n";
            $DoZapisaniaXML .= "    <brand><![CDATA[" . $porownywarki->produkty[$i]['producent_produktu'] . "]]></brand>\n";
            $DoZapisaniaXML .= "    <price>" . $porownywarki->produkty[$i]['cena_brutto_produktu'] . "</price>\n";
            $DoZapisaniaXML .= "    <image><![CDATA[" . $porownywarki->produkty[$i]['zdjecie_produktu'] . "]]></image>\n";
            $DoZapisaniaXML .= "    <url><![CDATA[" . $porownywarki->produkty[$i]['url_produktu'] . "]]></url>\n";
            $DoZapisaniaXML .= "    <name><![CDATA[" . $porownywarki->produkty[$i]['nazwa_produktu'] . "]]></name>\n";
            $DoZapisaniaXML .= "    <product_id>" . $porownywarki->produkty[$i]['id_produktu'] . "</product_id>\n";
            $DoZapisaniaXML .= "    <description><![CDATA[" . $porownywarki->produkty[$i]['opis_produktu'] . "]]></description>\n";
            
            // promocja
            if ( $porownywarki->produkty[$i]['promocja'] == 'tak' ) {
                 //
                 $DoZapisaniaXML .= "    <previous_price>" . $porownywarki->produkty[$i]['cena_stara_produktu'] . "</previous_price>\n";
                 //
            }        

            if ( isset($porownywarki->produkty[$i]['numer_ean_produktu']) && !empty($porownywarki->produkty[$i]['numer_ean_produktu']) ) {
                 //
                 $DoZapisaniaXML .= "    <ean><![CDATA[" . $porownywarki->produkty[$i]['numer_ean_produktu'] . "]]></ean>\n";
                 //
            }
            
            if ( isset($porownywarki->produkty[$i]['numer_katalogowy_produktu']) && !empty($porownywarki->produkty[$i]['numer_katalogowy_produktu']) ) {
                 //
                 $DoZapisaniaXML .= "    <sku><![CDATA[" . $porownywarki->produkty[$i]['numer_katalogowy_produktu'] . "]]></sku>\n";
                 //
            }          

            // cechy produktu
            if ( isset($porownywarki->produkty[$i]['cechy']) && count($porownywarki->produkty[$i]['cechy']) > 0 ) {

                foreach ( $porownywarki->produkty[$i]['cechy'] as $key => $value ) {
                    //
                    $wartosc_txt = array();
                    $wartosci = explode(',', (string)$value[1]);
                    foreach ( $wartosci as $wartosc ) {
                        //
                        $wartosc_txt[] = Porownywarki::TekstZamienEncje(Funkcje::WartoscCechy($wartosc));
                        //
                    }
                    //
                    if ( count($wartosc_txt) > 0 ) {
                         $DoZapisaniaXML .= "    <" . Porownywarki::TekstPlZnaki($key) . "><![CDATA[" . implode(',', (array)$wartosc_txt) . "]]></" . Porownywarki::TekstPlZnaki($key) . ">\n";              
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
                         $DoZapisaniaXML .= "    <" . Porownywarki::TekstPlZnaki($key) . "><![CDATA[" . Porownywarki::TekstZamienEncje($value) . "]]></" . Porownywarki::TekstPlZnaki($key) . ">\n";
                    }
                    //
                }
                //
            }
            
            $DoZapisaniaXML .= "</product>\n";


        }
        //dane do zapisania do pliku END

        // jezeli poczatek pliku
        if ( ( isset($_POST['offset']) && (int)$_POST['offset'] == 0 ) ) {
            ///
            $CoDoZapisania = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
            $CoDoZapisania .= "<products lang=\"pl\">\n";

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
                  $CoDoZapisania .= "</products>";
              }
              //
        }
        
        unset($DoZapisaniaXML);
        
    }
 
    if ( isset($ImportZewnetrzny) && isset($ZakonczeniePliku) ) {
         //
         $CoDoZapisania .= "</products>"; 
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