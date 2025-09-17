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
            $DoZapisaniaXML .= "   <id>" . $porownywarki->produkty[$i]['id_produktu'] . "</id>\n";
            $DoZapisaniaXML .= "   <name>" . $porownywarki->produkty[$i]['nazwa_produktu'] . "</name>\n";
            $DoZapisaniaXML .= "   <EAN>" . $porownywarki->produkty[$i]['numer_ean_produktu'] . "</EAN>\n";
            $DoZapisaniaXML .= "   <unit>" . $porownywarki->PokazJednostkeMiary($porownywarki->produkty[$i]['jm_id']) . "</unit>\n";
            $DoZapisaniaXML .= "   <price>" . $porownywarki->produkty[$i]['cena_brutto_produktu'] . "</price>\n";
            $DoZapisaniaXML .= "   <quantity>" . $porownywarki->produkty[$i]['ilosc_produktu'] . "</quantity>\n";
            
            // promocja
            if ( $porownywarki->produkty[$i]['promocja'] == 'tak' ) {
                 //
                 $DoZapisaniaXML .= "   <promotion>1</promotion>\n";
                 $DoZapisaniaXML .= "   <promotion_old_price>" . $porownywarki->produkty[$i]['cena_stara_produktu'] . "</promotion_old_price>\n";
                 //
              } else {
                 //
                 $DoZapisaniaXML .= "   <promotion>0</promotion>\n";
                 $DoZapisaniaXML .= "   <promotion_old_price>0</promotion_old_price>\n";
                 //
            }            
            
            $DoZapisaniaXML .= "   <brand>" . $porownywarki->produkty[$i]['producent_produktu'] . "</brand>\n";
            $DoZapisaniaXML .= "   <category>" . $porownywarki->produkty[$i]['kategoria_produktu'] . "</category>\n";

            $DoZapisaniaXML .= "   <description>\n";
            $DoZapisaniaXML .= "       <![CDATA[" . $porownywarki->produkty[$i]['opis_produktu'] . "]]>\n";
            $DoZapisaniaXML .= "   </description>\n";
            
            $DoZapisaniaXML .= "   <urlImage>" . $porownywarki->produkty[$i]['zdjecie_produktu'] . "</urlImage>\n";
            $DoZapisaniaXML .= "   <urlProduct>" . $porownywarki->produkty[$i]['url_produktu'] . "</urlProduct>\n";

            $DoZapisaniaXML .= "</product>\n";


        }
        //dane do zapisania do pliku END

        // jezeli poczatek pliku
        if ( ( isset($_POST['offset']) && (int)$_POST['offset'] == 0 ) ) {
            ///
            $CoDoZapisania = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
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