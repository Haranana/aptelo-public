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
        
            $DoZapisaniaXML .= "<produkt>\n";
            $DoZapisaniaXML .= "    <grupa1>\n";

            $DoZapisaniaXML .= "        <nazwa><![CDATA[".$porownywarki->produkty[$i]['nazwa_produktu']."]]></nazwa>\n";
            $DoZapisaniaXML .= "        <producent><![CDATA[".$porownywarki->produkty[$i]['producent_produktu']."]]></producent>\n";
            $DoZapisaniaXML .= "        <opis><![CDATA[".$porownywarki->produkty[$i]['opis_produktu']."]]></opis>\n";
            $DoZapisaniaXML .= "        <id>".$porownywarki->produkty[$i]['id_produktu']."</id>\n";
            $DoZapisaniaXML .= "        <url>".$porownywarki->produkty[$i]['url_produktu']."</url>\n";
            $DoZapisaniaXML .= "        <foto><![CDATA[".$porownywarki->produkty[$i]['zdjecie_produktu']."]]></foto>\n";
            $DoZapisaniaXML .= "        <kategoria>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['kategoria_produktu'])."</kategoria>\n";
            $DoZapisaniaXML .= "        <cena>".$porownywarki->produkty[$i]['cena_brutto_produktu']."</cena>\n";

            $DoZapisaniaXML .= "    </grupa1>\n";
            $DoZapisaniaXML .= "</produkt>\n";

        }
        // dane do zapisania do pliku END

        // jezeli poczatek pliku
        if ( ( isset($_POST['offset']) && (int)$_POST['offset'] == 0 ) ) {
            ///
            $CoDoZapisania = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            $CoDoZapisania .= "<radar wersja=\"1.0\">\n";
            $CoDoZapisania .= "<oferta>\n";

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
                 $CoDoZapisania .= "</oferta>\n";
                 $CoDoZapisania .= "</radar>";
             }
             //
        }
        
        unset($DoZapisaniaXML);
        
    }
 
    if ( isset($ImportZewnetrzny) && isset($ZakonczeniePliku) ) {
         //
         $CoDoZapisania .= "</oferta>\n";
         $CoDoZapisania .= "</radar>";
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