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
        
            $DoZapisaniaXML .= "<product>\n";

            $DoZapisaniaXML .= "<id>".$porownywarki->produkty[$i]['id_produktu']."</id>\n";
            $DoZapisaniaXML .= "<ean>".$porownywarki->produkty[$i]['numer_ean_produktu']."</ean>\n";
            $DoZapisaniaXML .= "<name>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['nazwa_produktu'])."</name>\n";
            $DoZapisaniaXML .= "<description>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['opis_produktu'])."</description>\n";
            $DoZapisaniaXML .= "<image>".$porownywarki->produkty[$i]['zdjecie_produktu']."</image>\n";
            $DoZapisaniaXML .= "<category>10177</category>\n";
            $DoZapisaniaXML .= "<producer>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['producent_produktu'])."</producer>\n";
            $DoZapisaniaXML .= "<brand></brand>\n";
            $DoZapisaniaXML .= "<size></size>\n";
            $DoZapisaniaXML .= "<size_unit></size_unit>\n";
            $DoZapisaniaXML .= "<price>".number_format($porownywarki->produkty[$i]['cena_netto_produktu'], 2, '.', '')."</price>\n";
            $DoZapisaniaXML .= "<vat>".number_format($tablica_stawek_podatku[$porownywarki->produkty[$i]['stawka_podatku_id']], 0, '.', ' ')."</vat>\n";
            $DoZapisaniaXML .= "<quantity>".(int)$porownywarki->produkty[$i]['ilosc_produktu']."</quantity>\n";
            $DoZapisaniaXML .= "<min_order>".$porownywarki->produkty[$i]['min_ilosc']."</min_order>\n";
            $DoZapisaniaXML .= "<multiplicity>".$porownywarki->produkty[$i]['ilosc_zbiorcza']."</multiplicity>\n";

            $DoZapisaniaXML .= "</product>\n";

        }
        // dane do zapisania do pliku END

        // jezeli poczatek pliku
        if ( ( isset($_POST['offset']) && (int)$_POST['offset'] == 0 ) ) {
            ///
            $CoDoZapisania    = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            $CoDoZapisania .= "<hurtum>\n";
            $CoDoZapisania .= "<products>\n";

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
                 $CoDoZapisania .= "</hurtum>\n";
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