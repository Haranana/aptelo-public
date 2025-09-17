<?php
if ( !isset($ImportZewnetrzny) ) {

    chdir('../../');
    
    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
}

if ( (isset($_POST['plugin']) && !empty($_POST['plugin']) && isset($_POST['offset']) && (int)$_POST['offset'] > -1 && Sesje::TokenSpr()) || isset($ImportZewnetrzny) ) {

    if ( !isset($ImportZewnetrzny) ) {
      
         $porownywarki = new Porownywarki($_POST['plugin'], $_POST['offset'], $_POST['limit'], '/');
         
    }
    
    $CoDoZapisania = '';

    $plik = KATALOG_SKLEPU . 'xml/' . $filtr->process($_POST['nazwa_pliku']);

    // uchwyt pliku, otwarcie do dopisania
    $fp = fopen($plik, "a");
    
    // blokada pliku do zapisu
    flock($fp, 2);

    if ( isset($porownywarki->produkty) && count($porownywarki->produkty) > 0 ) {

        // pobieranie informacji o czasach dostawy
        $zapytanie_tmp = "select products_shipping_time_id, products_shipping_time_day from products_shipping_time";
        $sqls = $db->open_query($zapytanie_tmp);
        //
        $tablica_czas = array();

        while ($infs = $sqls->fetch_assoc()) { 
            $tablica_czas[ $infs['products_shipping_time_id'] ] = $infs['products_shipping_time_day'];
        }
        $db->close_query($sqls); 
        unset($zapytanie_tmp, $infs);    
        //  
    
        $DoZapisaniaXML = '';

        // dane do zapisania do pliku START

        for ( $i = 0, $c = count($porownywarki->produkty); $i < $c; $i++ ) {

            if ( $porownywarki->produkty[$i]['stan_produktu'] != '0' ) {
                $stan_produktu = $porownywarki->ProduktStanProduktuPorownywarki($porownywarki->produkty[$i]['stan_produktu'], $_POST['plugin']);
            } else {
                if ( $porownywarki->stan_domyslny == '1' ) {
                    $stan_produktu = '11';
                } elseif ( $porownywarki->stan_domyslny == '2' ) {
                    $stan_produktu = '1';
                } elseif ( $porownywarki->stan_domyslny == '3' ) {
                    $stan_produktu = '4';
                }
            }

            $DoZapisaniaXML .= "<offer>\n";

            $DoZapisaniaXML .= "    <sku>" . $porownywarki->produkty[$i]['numer_katalogowy_produktu'] . "</sku>\n";
            $DoZapisaniaXML .= "    <product-id>" . $porownywarki->produkty[$i]['numer_ean_produktu'] . "</product-id>\n";
            $DoZapisaniaXML .= "    <product-id-type>EAN</product-id-type>\n";
            $DoZapisaniaXML .= "    <price>" . $porownywarki->produkty[$i]['cena_brutto_produktu'] . "</price>\n";
            $DoZapisaniaXML .= "    <quantity>" . $porownywarki->produkty[$i]['ilosc_produktu'] . "</quantity>\n";
            $DoZapisaniaXML .= "    <state>".$stan_produktu."</state>\n";
            
            $zapytanie_czas = "select products_shipping_time_id from products where products_id = '" . $porownywarki->produkty[$i]['id_produktu'] . "'";
            $sqls = $db->open_query($zapytanie_czas);   

            $czas = 0;
            
            if ((int)$GLOBALS['db']->ile_rekordow($sqls) > 0) {
                            
                $infs = $sqls->fetch_assoc();
                if ( isset($tablica_czas[ $infs['products_shipping_time_id'] ]) ) {
                     $czas = $tablica_czas[ $infs['products_shipping_time_id'] ];
                }
                
            }
            
            if ( $czas > 0 ) {
              
                 $DoZapisaniaXML .= "    <leadtime-to-ship>" . $czas . "</leadtime-to-ship>\n";
                 
            }

            $DoZapisaniaXML .= "</offer>\n";
            
        }
        // dane do zapisania do pliku END

        // jezeli poczatek pliku
        if ( ( isset($_POST['offset']) && (int)$_POST['offset'] == 0 ) ) {
            ///
            $CoDoZapisania = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
            $CoDoZapisania .= "<import>\n";
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
                 $CoDoZapisania .= "</import>";
             }
             //
        }
        
        unset($DoZapisaniaXML);
        
    }
 
    if ( isset($ImportZewnetrzny) && isset($ZakonczeniePliku) ) {
         //
         $CoDoZapisania .= "</offers>\n";
         $CoDoZapisania .= "</import>";
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