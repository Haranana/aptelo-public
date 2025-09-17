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

            $KategoriaProduktu = explode('/', (string)$porownywarki->produkty[$i]['kategoria_produktu']);

            // pobranie i sprawdzenie ustawienia dostepnosci produktu - specyficzne dla porownywarki
            $dostepnosc = $porownywarki->produkty[$i]['dostepnosc_produktu'];

            if ( isset($tablica_dostepnosci[$porownywarki->produkty[$i]['dostepnosc_produktu']]) && $porownywarki->produkty[$i]['dostepnosc_produktu'] != '0' && $porownywarki->produkty[$i]['dostepnosc_produktu'] != '') {
                $dostepnosc = $tablica_dostepnosci[$porownywarki->produkty[$i]['dostepnosc_produktu']];
            } else {
                $dostepnosc = $porownywarki->dotepnosc_domyslna;
            }

            $DoZapisaniaXML .= "     <item>\n";
            $DoZapisaniaXML .= "     <compid>".$porownywarki->produkty[$i]['id_produktu']."</compid>\n";
            $DoZapisaniaXML .= "     <catpath><![CDATA[".$porownywarki->produkty[$i]['kategoria_produktu']."]]></catpath>\n";
            $DoZapisaniaXML .= "     <catname><![CDATA[".((isset($KategoriaProduktu[ count($KategoriaProduktu) - 1])) ? $KategoriaProduktu[ count($KategoriaProduktu) - 1] : '')."]]></catname>\n";
            $DoZapisaniaXML .= "     <photo>".$porownywarki->produkty[$i]['zdjecie_produktu']."</photo>\n";

            // dodatkowe zdjecia
            if ( isset($porownywarki->produkty[$i]['dodatkowe_zdjecia']) && count($porownywarki->produkty[$i]['dodatkowe_zdjecia']) > 0 ) {
                 //
                 $DodatkoweZdjecia = $porownywarki->produkty[$i]['dodatkowe_zdjecia'];
                 //
                 foreach ( $DodatkoweZdjecia as $Zdjecie ) {
                    //
                    $DoZapisaniaXML .= "     <photo>".$Zdjecie."</photo>\n";
                    //
                 }
                 //
                 unset($DodatkoweZdjecia);
                 //
            }             

            $DoZapisaniaXML .= "     <url>".$porownywarki->produkty[$i]['url_produktu']."</url>\n";
            $DoZapisaniaXML .= "     <vendor>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['producent_produktu'])."</vendor>\n";
            $DoZapisaniaXML .= "     <name><![CDATA[".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['nazwa_produktu'])."]]></name>\n";
            $DoZapisaniaXML .= "     <price>".$porownywarki->produkty[$i]['cena_brutto_produktu']."</price>\n";
            $DoZapisaniaXML .= "     <desclong><![CDATA[".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['opis_produktu'])."]]></desclong>\n";
            $DoZapisaniaXML .= "     <ean>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['numer_ean_produktu'])."</ean>\n";
            $DoZapisaniaXML .= "     <partnr>".$porownywarki->produkty[$i]['numer_katalogowy_produktu']."</partnr>\n";
            $DoZapisaniaXML .= "     <availability>".$dostepnosc."</availability>\n";
            
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
                         $DoZapisaniaXML .= "    <attribute name=\"" . $key . "\">" . implode(',', (array)$wartosc_txt) . "</attribute>\n";            
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

            $DoZapisaniaXML .= "     </item>\n";

        }
        // dane do zapisania do pliku END
        
        unset($tablica_dostepnosci);

        // jezeli poczatek pliku
        if ( ( isset($_POST['offset']) && (int)$_POST['offset'] == 0 ) ) {
            ///
            $CoDoZapisania    = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
            $CoDoZapisania .= "<XMLDATA>\n";
            $CoDoZapisania   .= "<version>13.0</version>\n";
            $CoDoZapisania   .= "<time>".date('Y-m-d-H-i',time())."</time>\n";
            $CoDoZapisania   .= "<data>\n";

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
                 $CoDoZapisania .= "</data>\n";
                 $CoDoZapisania .= "</XMLDATA>\n";
             }
             //
        }
        
        unset($DoZapisaniaXML);
        
    }
 
    if ( isset($ImportZewnetrzny) && isset($ZakonczeniePliku) ) {
         //
         $CoDoZapisania .= "</data>\n";
         $CoDoZapisania .= "</XMLDATA>\n";
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