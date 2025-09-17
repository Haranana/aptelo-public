<?php
if ( !isset($ImportZewnetrzny) ) {

    chdir('../../');
    
    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
}

if ( (isset($_POST['plugin']) && !empty($_POST['plugin']) && isset($_POST['offset']) && (int)$_POST['offset'] > -1 && Sesje::TokenSpr()) || isset($ImportZewnetrzny) ) {

    if ( !isset($ImportZewnetrzny) ) {
      
         $porownywarki = new Porownywarki($_POST['plugin'], $_POST['offset'], $_POST['limit'], ' > ' );
         
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
        
            $DoZapisaniaXML .= "<product id=\"".$porownywarki->produkty[$i]['id_produktu']."\">\n";

            $DoZapisaniaXML .= "<name>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['nazwa_produktu'])."</name>\n";
            $DoZapisaniaXML .= "<url>".$porownywarki->produkty[$i]['url_produktu']."</url>\n";
            $DoZapisaniaXML .= "<brand>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['producent_produktu'])."</brand>\n";
            $DoZapisaniaXML .= "<categories>\n";
            $DoZapisaniaXML .= "    <category>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['kategoria_produktu'])."</category>\n";
            $DoZapisaniaXML .= "</categories>\n";
            $DoZapisaniaXML .= "<photo>".$porownywarki->produkty[$i]['zdjecie_produktu']."</photo>\n";
            $DoZapisaniaXML .= "<description>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['opis_produktu'])."</description>\n";
            $DoZapisaniaXML .= "<price>".number_format($porownywarki->produkty[$i]['cena_brutto_produktu'], 2, ',', '')."</price>\n";

            $DoZapisaniaXML .= "<attributes>\n";
            $DoZapisaniaXML .= "    <attr>\n";
            $DoZapisaniaXML .= "        <name>EAN</name>\n";
            $DoZapisaniaXML .= "        <value>".$porownywarki->produkty[$i]['numer_ean_produktu']."</value>\n";
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
                         $DoZapisaniaXML .= "    <attr>\n";
                         $DoZapisaniaXML .= "        <name>" . $key . "</name>\n";
                         $DoZapisaniaXML .= "        <value>" . implode(';', (array)$wartosc_txt) . "</value>\n";
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
                         $DoZapisaniaXML .= "    <attr>\n";
                         $DoZapisaniaXML .= "        <name>" . $key . "</name>\n";
                         $DoZapisaniaXML .= "        <value>" . Porownywarki::TekstZamienEncje($value) . "</value>\n";
                         $DoZapisaniaXML .= "    </attr>\n";   
                    }
                    //
                }
                //
            }               

            $DoZapisaniaXML .= "</attributes>\n";
            $DoZapisaniaXML .= "</product>\n";

        }
        // dane do zapisania do pliku END

        // jezeli poczatek pliku
        if ( ( isset($_POST['offset']) && (int)$_POST['offset'] == 0 ) ) {
            ///
            $CoDoZapisania    = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            $CoDoZapisania .= "<products";
            $CoDoZapisania .= "     xmlns=\"http://www.sklepy24.pl\"\n";
            $CoDoZapisania .= "     xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n";
            $CoDoZapisania .= "     xsi:schemaLocation=\"http://www.sklepy24.pl http://www.sklepy24.pl/formats/products.xsd\"\n";
            $CoDoZapisania .= "     date=\"".date("Y-m-d")."\">\n";

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