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

        $tablica_stawek_podatku = $porownywarki->TablicaStawekPodatkowych();
            
        $DoZapisaniaXML = '';

        // dane do zapisania do pliku START

        for ( $i = 0, $c = count($porownywarki->produkty); $i < $c; $i++ ) {
        
            $DoZapisaniaXML .= "<PRODUKT>\n";
            $DoZapisaniaXML .= "     <ID>".$porownywarki->produkty[$i]['id_produktu']."</ID>\n";
            $DoZapisaniaXML .= "     <KATEGORIA>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['kategoria_produktu'])."</KATEGORIA>\n";
            $DoZapisaniaXML .= "     <TYP>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['nazwa_produktu'])."</TYP>\n";
            $DoZapisaniaXML .= "     <MARKA>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['producent_produktu'])."</MARKA>\n";
            $DoZapisaniaXML .= "     <MODEL></MODEL>\n";
            $DoZapisaniaXML .= "     <CENA>".$porownywarki->produkty[$i]['cena_brutto_produktu']."</CENA>\n";
            $DoZapisaniaXML .= "     <CENA_NETTO>".$porownywarki->produkty[$i]['cena_netto_produktu']."</CENA_NETTO>\n";
            $DoZapisaniaXML .= "     <VAT>".$tablica_stawek_podatku[$porownywarki->produkty[$i]['stawka_podatku_id']]."</VAT>\n";
            $DoZapisaniaXML .= "     <URL_IMG>".$porownywarki->produkty[$i]['zdjecie_produktu']."</URL_IMG>\n";
            $DoZapisaniaXML .= "     <URL_PROD>".$porownywarki->produkty[$i]['url_produktu']."</URL_PROD>\n";
            $DoZapisaniaXML .= "     <OPIS>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['opis_produktu'])."</OPIS>\n";
            $DoZapisaniaXML .= "</PRODUKT>\n";

        }
        // dane do zapisania do pliku END

        // jezeli poczatek pliku
        if ( ( isset($_POST['offset']) && (int)$_POST['offset'] == 0 ) ) {
            ///
            $CoDoZapisania    = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            $CoDoZapisania .= "<!DOCTYPE PRODUKTY SYSTEM \"pkt.dtd\">\n";
            $CoDoZapisania .= "<PRODUKTY>\n";

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
                 $CoDoZapisania .= "</PRODUKTY>";
             }
             //
        }
        
        unset($DoZapisaniaXML);
        
    }
 
    if ( isset($ImportZewnetrzny) && isset($ZakonczeniePliku) ) {
         //
         $CoDoZapisania .= "</PRODUKTY>";
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