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
        
        $tablica_dostepnosci = Porownywarki::TablicaDostepnosci('googleshopping');

        $tablica_dostepnosci_tmp = Porownywarki::TablicaDostepnosciNiezdefiniowanych('googleshopping');
        foreach ( $tablica_dostepnosci_tmp as $rekord ) {
            $dostepnosc_tmp[$rekord['id']] = $rekord['text'];
        }
        unset($tablica_dostepnosci_tmp);

        if ( $porownywarki->stan_domyslny == '1' ) {
            $stan_produktu = 'new';
        } elseif ( $porownywarki->stan_domyslny == '2' ) {
            $stan_produktu = 'used';
        } elseif ( $porownywarki->stan_domyslny == '3' ) {
            $stan_produktu = 'refurbished';
        }

        $DoZapisaniaCsv = array();
        
        // jezeli poczatek pliku
        if ( ( isset($_POST['offset']) && (int)$_POST['offset'] == 0 ) ) {
            //
            $DoZapisaniaCsv[] = array('id','google_product_category','availability','condition','description','image_link','additional_image_link','link','title','price','sale_price','gtin','mpn','brand','inventory');
            //
        }

        // dane do zapisania do pliku START

        for ( $i = 0, $c = count($porownywarki->produkty); $i < $c; $i++ ) {
        
            // pobranie i sprawdzenie ustawienia dostepnosci produktu - specyficzne dla porownywarki
            $dostepnosc = $porownywarki->produkty[$i]['dostepnosc_produktu'];

            if ( isset($tablica_dostepnosci[$porownywarki->produkty[$i]['dostepnosc_produktu']]) && $porownywarki->produkty[$i]['dostepnosc_produktu'] != '0' && $porownywarki->produkty[$i]['dostepnosc_produktu'] != '') {
                $dostepnosc = $tablica_dostepnosci[$porownywarki->produkty[$i]['dostepnosc_produktu']];
            } else {
                $dostepnosc = $tablica_dostepnosci[$porownywarki->dotepnosc_domyslna];
            }
            
            $DoZapisaniaCsvTmp = array();

            $DoZapisaniaCsvTmp[] = $porownywarki->produkty[$i]['id_produktu'];
            $DoZapisaniaCsvTmp[] = $porownywarki->produkty[$i]['kategoria_google'];
            $DoZapisaniaCsvTmp[] = $dostepnosc_tmp[$dostepnosc];
            $DoZapisaniaCsvTmp[] = $stan_produktu;
            $DoZapisaniaCsvTmp[] = strip_tags(Funkcje::przytnijTekst( str_replace(array("\r\n", "\n\r", "\n", "\r"), '', (string)$porownywarki->produkty[$i]['opis_produktu']), 5000 ));
            $DoZapisaniaCsvTmp[] = $porownywarki->produkty[$i]['zdjecie_produktu'];
            
            $e = 1;
            $ZdjeciaDodatkowe = array();
            if ( isset($porownywarki->produkty[$i]['dodatkowe_zdjecia']) && count($porownywarki->produkty[$i]['dodatkowe_zdjecia']) > 0 ) {
                foreach ( $porownywarki->produkty[$i]['dodatkowe_zdjecia'] as $key => $value ) {
                    $ZdjeciaDodatkowe[] = $value;
                    if ( $e > 8 ) {
                         break;
                    }
                    $e++;
                }
            }  
            $DoZapisaniaCsvTmp[] = implode(',', (array)$ZdjeciaDodatkowe);            
            unset($ZdjeciaDodatkowe);
            
            $DoZapisaniaCsvTmp[] = $porownywarki->produkty[$i]['url_produktu'];
            $DoZapisaniaCsvTmp[] = Funkcje::przytnijTekst($porownywarki->produkty[$i]['nazwa_produktu'], 100);
            //$DoZapisaniaCsvTmp[] = $porownywarki->produkty[$i]['cena_brutto_produktu'] . ' ' . $_SESSION['domyslna_waluta']['kod'];
            
            if ( $porownywarki->produkty[$i]['promocja'] == 'tak' && $porownywarki->produkty[$i]['cena_brutto_produktu'] < $porownywarki->produkty[$i]['cena_stara_produktu'] ) {
                 //
                 $DoZapisaniaCsvTmp[] = $porownywarki->produkty[$i]['cena_stara_produktu'] . ' ' . $_SESSION['domyslna_waluta']['kod'];
                 $DoZapisaniaCsvTmp[] = $porownywarki->produkty[$i]['cena_brutto_produktu'] . ' ' . $_SESSION['domyslna_waluta']['kod'];
                 //
            } else {
                 //
                 $DoZapisaniaCsvTmp[] = $porownywarki->produkty[$i]['cena_brutto_produktu'] . ' ' . $_SESSION['domyslna_waluta']['kod'];
                 $DoZapisaniaCsvTmp[] = '';
                 //
            }
                
            $DoZapisaniaCsvTmp[] = $porownywarki->produkty[$i]['numer_ean_produktu'];
            $DoZapisaniaCsvTmp[] = $porownywarki->produkty[$i]['kod_producenta_produktu'];
            $DoZapisaniaCsvTmp[] = Funkcje::przytnijTekst($porownywarki->produkty[$i]['producent_produktu'], 70);
            $DoZapisaniaCsvTmp[] = (($porownywarki->produkty[$i]['ilosc_produktu'] < 0) ? 0 : (int)$porownywarki->produkty[$i]['ilosc_produktu']);
            
            $DoZapisaniaCsv[] = $DoZapisaniaCsvTmp;
            
            unset($DoZapisaniaCsvTmp);
            
        }
        
        // dane do zapisania do pliku END

    }
    
    if ( isset($DoZapisaniaCsv) && count($DoZapisaniaCsv) > 0 ) {
        foreach ($DoZapisaniaCsv as $Linia) {
            fputcsv($fp, $Linia, ',', '"');
        }
    }
       
    //fwrite($fp, $CoDoZapisania);

    // zapisanie danych do pliku
    flock($fp, 3);
    // zamkniecie pliku
    fclose($fp);

    unset($CoDoZapisania);

}
echo 'OK';

?>