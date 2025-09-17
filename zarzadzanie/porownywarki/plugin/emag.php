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
      
        $tablica_stawek_podatku = $porownywarki->TablicaStawekPodatkowych();

        $DoZapisaniaXML = '';

        // dane do zapisania do pliku START

        for ( $i = 0, $c = count($porownywarki->produkty); $i < $c; $i++ ) {
        
            $KategoriaProduktu = explode('/', (string)$porownywarki->produkty[$i]['kategoria_produktu']);

            $waga = '';
            if ( $porownywarki->produkty[$i]['waga_produktu'] > 0 ) {
                $waga = number_format($porownywarki->produkty[$i]['waga_produktu'], 0, '.', ' ');
            }

            $DoZapisaniaXML .= "<product>\n";

            $DoZapisaniaXML .= "    <vendor_ext_id>".$porownywarki->produkty[$i]['id_produktu']."</vendor_ext_id>\n";
            $DoZapisaniaXML .= "    <category><![CDATA[".((isset($KategoriaProduktu[ count($KategoriaProduktu) - 1])) ? $KategoriaProduktu[ count($KategoriaProduktu) - 1] : '')."]]></category>\n";
            $DoZapisaniaXML .= "    <name><![CDATA[".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['nazwa_produktu'])."]]></name>\n";
            $DoZapisaniaXML .= "    <ean>".$porownywarki->produkty[$i]['numer_ean_produktu']."</ean>\n";
            $DoZapisaniaXML .= "    <part_number>".$porownywarki->produkty[$i]['numer_katalogowy_produktu']."</part_number>\n";
            $DoZapisaniaXML .= "    <brand><![CDATA[".$porownywarki->produkty[$i]['producent_produktu']."]]></brand>\n";
            $DoZapisaniaXML .= "    <main_image_url>".$porownywarki->produkty[$i]['zdjecie_produktu']."</main_image_url>\n";

            // dodatkowe zdjecia
            if ( count($porownywarki->produkty[$i]['dodatkowe_zdjecia']) > 0 ) {
                 //
                 $DodatkoweZdjecia = $porownywarki->produkty[$i]['dodatkowe_zdjecia'];
                 //
                 $licznik = 1;
                 foreach ( $DodatkoweZdjecia as $Zdjecie ) {
                    //
                    if ( $licznik < 6 ) {
                        $DoZapisaniaXML .= "     <images_url_".$licznik.">".$Zdjecie."</images_url_".$licznik.">\n";
                    }
                    //
                    $licznik++;
                 }
                 //
                 unset($DodatkoweZdjecia);
                 //
            }             
            $DoZapisaniaXML .= "    <url>".$porownywarki->produkty[$i]['url_produktu']."</url>\n";
            $DoZapisaniaXML .= "    <description><![CDATA[".$porownywarki->produkty[$i]['opis_produktu']."]]></description>\n";
            $DoZapisaniaXML .= "    <sale_price>".$porownywarki->produkty[$i]['cena_netto_produktu']."</sale_price>\n";
            $DoZapisaniaXML .= "    <vat_rate>".number_format(($tablica_stawek_podatku[$porownywarki->produkty[$i]['stawka_podatku_id']]/100), 2, '.', ' ')."</vat_rate>\n";
            $DoZapisaniaXML .= "    <weight>".$waga."</weight>\n";
            $DoZapisaniaXML .= "    <stock>".(int)$porownywarki->produkty[$i]['ilosc_produktu']."</stock>\n";

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
                         $DoZapisaniaXML .= "    <property name=\"" . $key . "\">" . implode(';', (array)$wartosc_txt) . "</property>\n";            
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
                         $DoZapisaniaXML .= "    <property name=\"" . $key . "\">" . Porownywarki::TekstZamienEncje($value) . "</property>\n";
                    }
                    //
                }
                //
            }  


            $DoZapisaniaXML .= "</product>\n";

        }
        // dane do zapisania do pliku END
        
        unset($tablica_stawek_podatku);

        // jezeli poczatek pliku
        if ( ( isset($_POST['offset']) && (int)$_POST['offset'] == 0 ) ) {
            ///
            $CoDoZapisania = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            $CoDoZapisania .= "<shop>\n";

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
                 $CoDoZapisania .= "</shop>";
             }
             //
        }
        
        unset($DoZapisaniaXML);
        
    }
 
    if ( isset($ImportZewnetrzny) && isset($ZakonczeniePliku) ) {
         //
         $CoDoZapisania .= "</shop>";
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