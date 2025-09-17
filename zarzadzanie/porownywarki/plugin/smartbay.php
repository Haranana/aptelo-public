<?php
if ( !isset($ImportZewnetrzny) ) {

    chdir('../../');
    
    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
}

if ( (isset($_POST['plugin']) && !empty($_POST['plugin']) && isset($_POST['offset']) && (int)$_POST['offset'] > -1 && Sesje::TokenSpr()) || isset($ImportZewnetrzny) ) {

    if ( !isset($ImportZewnetrzny) ) {
      
         $porownywarki = new Porownywarki($_POST['plugin'], $_POST['offset'], $_POST['limit'], ' / ');
         
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
        
            //Pobranie i sprawdzenie ustawienia dostepnosci produktu - specyficzne dla porownywarki
            $dostepnosc = $porownywarki->produkty[$i]['dostepnosc_produktu'];

            if ( isset($tablica_dostepnosci[$porownywarki->produkty[$i]['dostepnosc_produktu']]) && $porownywarki->produkty[$i]['dostepnosc_produktu'] != '0' && $porownywarki->produkty[$i]['dostepnosc_produktu'] != '') {
                $dostepnosc = $tablica_dostepnosci[$porownywarki->produkty[$i]['dostepnosc_produktu']];
            } else {
                $dostepnosc = $porownywarki->dotepnosc_domyslna;
            }

            $DoZapisaniaXML .= "<product>\n";
            $DoZapisaniaXML .= "    <id>".$porownywarki->produkty[$i]['id_produktu']."</id>\n";
            $DoZapisaniaXML .= "    <name><![CDATA[".$porownywarki->produkty[$i]['nazwa_produktu']."]]></name>\n";
            $DoZapisaniaXML .= "    <producer><![CDATA[".$porownywarki->produkty[$i]['producent_produktu']."]]></producer>\n";
            $DoZapisaniaXML .= "    <code><![CDATA[".$porownywarki->produkty[$i]['numer_katalogowy_produktu']."]]></code>\n";
            $DoZapisaniaXML .= "    <description><![CDATA[".$porownywarki->produkty[$i]['opis_produktu']."]]></description>\n";
            $DoZapisaniaXML .= "    <url><![CDATA[".$porownywarki->produkty[$i]['url_produktu']."]]></url>\n";
            $DoZapisaniaXML .= "    <price><![CDATA[".$porownywarki->produkty[$i]['cena_brutto_produktu']."]]></price>\n";
            $DoZapisaniaXML .= "    <category><![CDATA[".$porownywarki->produkty[$i]['kategoria_produktu']."]]></category>\n";
            $DoZapisaniaXML .= "    <image><![CDATA[".$porownywarki->produkty[$i]['zdjecie_produktu']."]]></image>\n";
            $DoZapisaniaXML .= "    <availability>".$dostepnosc."</availability>\n";
            
            if ( (isset($porownywarki->produkty[$i]['cechy']) && count($porownywarki->produkty[$i]['cechy']) > 0) || (isset($porownywarki->produkty[$i]['pola']) && count($porownywarki->produkty[$i]['pola']) > 0) ) {
              
                $DoZapisaniaXML .= "    <params>\n";
              
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
                             $DoZapisaniaXML .= "        <parm_name><![CDATA[" . $key . "]]></parm_name>\n";
                             $DoZapisaniaXML .= "        <parm_value><![CDATA[" . implode(';', (array)$wartosc_txt) . "]]></parm_value>\n";           
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
                             $DoZapisaniaXML .= "        <parm_name><![CDATA[" . $key . "]]></parm_name>\n";
                             $DoZapisaniaXML .= "        <parm_value><![CDATA[" . Porownywarki::TekstZamienEncje($value) . "]]></parm_value>\n";   
                        }
                        //
                    }
                    //
                }     
                
                $DoZapisaniaXML .= "    </params>\n";

            }                

            $DoZapisaniaXML .= "</product>\n";

        }
        // dane do zapisania do pliku END

        // jezeli poczatek pliku
        if ( ( isset($_POST['offset']) && (int)$_POST['offset'] == 0 ) ) {
            ///
            $CoDoZapisania = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
            $CoDoZapisania .= "<offers>\n";
            $CoDoZapisania .= "<stat>\n";
            $CoDoZapisania .= "<num>".$_POST['ilosc_rekordow']."</num>\n";
            $CoDoZapisania .= "<ver>2.1</ver>\n";
            $CoDoZapisania .= "</stat>\n";

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