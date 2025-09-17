<?php
if ( !isset($ImportZewnetrzny) ) {

    chdir('../../');
    
    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
}

if ( (isset($_POST['plugin']) && !empty($_POST['plugin']) && isset($_POST['offset']) && (int)$_POST['offset'] > -1 && Sesje::TokenSpr()) || isset($ImportZewnetrzny) ) {

    if ( !isset($ImportZewnetrzny) ) {
      
         $porownywarki = new Porownywarki($_POST['plugin'], $_POST['offset'], $_POST['limit'], '>');
         
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

        $tablica_dostepnosci_tmp = Porownywarki::TablicaDostepnosciNiezdefiniowanych($_POST['plugin']);
        foreach ( $tablica_dostepnosci_tmp as $rekord ) {
            $dostepnosc_tmp[$rekord['id']] = $rekord['text'];
        }
        unset($tablica_dostepnosci_tmp);

        $DoZapisaniaXML = '';

        // dane do zapisania do pliku START

        for ( $i = 0, $c = count($porownywarki->produkty); $i < $c; $i++ ) {
        
            //if ( $porownywarki->produkty[$i]['kategoria_google'] != '' ) {

                if ( $porownywarki->produkty[$i]['stan_produktu'] != '0' ) {
                    $stan_produktu = $porownywarki->ProduktStanProduktuPorownywarki($porownywarki->produkty[$i]['stan_produktu'], $_POST['plugin']);
                } else {
                    if ( $porownywarki->stan_domyslny == '1' ) {
                        $stan_produktu = 'new';
                    } elseif ( $porownywarki->stan_domyslny == '2' ) {
                        $stan_produktu = 'used';
                    } elseif ( $porownywarki->stan_domyslny == '3' ) {
                        $stan_produktu = 'refurbished';
                    }
                }

                // pobranie i sprawdzenie ustawienia dostepnosci produktu - specyficzne dla porownywarki
                $dostepnosc = $porownywarki->produkty[$i]['dostepnosc_produktu'];

                if ( isset($tablica_dostepnosci[$porownywarki->produkty[$i]['dostepnosc_produktu']]) && $porownywarki->produkty[$i]['dostepnosc_produktu'] != '0' && $porownywarki->produkty[$i]['dostepnosc_produktu'] != '') {
                    $dostepnosc = $tablica_dostepnosci[$porownywarki->produkty[$i]['dostepnosc_produktu']];
                } else {
                    $dostepnosc = $tablica_dostepnosci[$porownywarki->dotepnosc_domyslna];
                }

                $DoZapisaniaXML .= "<item>\n";

                $DoZapisaniaXML .= "    <g:id>".$porownywarki->produkty[$i]['id_produktu']."</g:id>\n";
                $DoZapisaniaXML .= "    <g:title><![CDATA[".$porownywarki->produkty[$i]['nazwa_produktu']."]]></g:title>\n";
                $DoZapisaniaXML .= "    <g:description><![CDATA[".$porownywarki->produkty[$i]['opis_produktu']."]]></g:description>\n";
                $DoZapisaniaXML .= "    <g:google_product_category><![CDATA[".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['kategoria_google'])."]]></g:google_product_category>\n";
                $DoZapisaniaXML .= "    <g:product_type><![CDATA[".$porownywarki->produkty[$i]['kategoria_produktu']."]]></g:product_type>\n";
                $DoZapisaniaXML .= "    <link><![CDATA[".$porownywarki->produkty[$i]['url_produktu']."]]></link>\n";
                $DoZapisaniaXML .= "    <g:image_link><![CDATA[".$porownywarki->produkty[$i]['zdjecie_produktu']."]]></g:image_link>\n";

                if ( isset($porownywarki->produkty[$i]['dodatkowe_zdjecia']) && count($porownywarki->produkty[$i]['dodatkowe_zdjecia']) > 0 ) {
                    foreach ( $porownywarki->produkty[$i]['dodatkowe_zdjecia'] as $key => $value ) {
                        $DoZapisaniaXML .= "    <g:additional_image_link><![CDATA[".$value."]]></g:additional_image_link>\n";
                    }

                }

                if ( $porownywarki->produkty[$i]['promocja'] == 'tak' && $porownywarki->produkty[$i]['cena_brutto_produktu'] < $porownywarki->produkty[$i]['cena_stara_produktu'] ) {
                    $DoZapisaniaXML .= "    <g:price>".$porownywarki->produkty[$i]['cena_stara_produktu'] . ' ' .$_SESSION['domyslna_waluta']['kod']."</g:price>\n";
                    $DoZapisaniaXML .= "    <g:sale_price>".$porownywarki->produkty[$i]['cena_brutto_produktu'] . ' ' .$_SESSION['domyslna_waluta']['kod']."</g:sale_price>\n";

                    if ( $porownywarki->produkty[$i]['promocja_koniec'] != '0000-00-00 00:00:00' ) {
                        if ( $porownywarki->produkty[$i]['promocja_start'] == '0000-00-00 00:00:00' ) {
                            $dataPromocjiStart = date("c", time());
                        } else {
                            $dataPromocjiStart = date("c", FunkcjeWlasnePHP::my_strtotime($porownywarki->produkty[$i]['promocja_start']));
                        }
                        $dataPromocjiKoniec = date("c", FunkcjeWlasnePHP::my_strtotime($porownywarki->produkty[$i]['promocja_koniec']));

                        $DoZapisaniaXML .= "    <g:sale_price_effective_date>".$dataPromocjiStart . '/' .$dataPromocjiKoniec."</g:sale_price_effective_date>\n";
                        
                    }
                } else {
                    $DoZapisaniaXML .= "    <g:price>".$porownywarki->produkty[$i]['cena_brutto_produktu'] . ' ' .$_SESSION['domyslna_waluta']['kod']."</g:price>\n";
                }
                $DoZapisaniaXML .= "    <g:brand><![CDATA[".$porownywarki->produkty[$i]['producent_produktu']."]]></g:brand>\n";
                $DoZapisaniaXML .= "    <g:gtin><![CDATA[".$porownywarki->produkty[$i]['numer_ean_produktu']."]]></g:gtin>\n";
                $DoZapisaniaXML .= "    <g:mpn><![CDATA[".$porownywarki->produkty[$i]['kod_producenta_produktu']."]]></g:mpn>\n";

                if ( $stan_produktu == 'new' ) {
                    if ( ( $porownywarki->produkty[$i]['producent_produktu'] == '' && $porownywarki->produkty[$i]['numer_ean_produktu'] == '' ) || ( $porownywarki->produkty[$i]['producent_produktu'] == '' && $porownywarki->produkty[$i]['kod_producenta_produktu'] == '' ) ) {
                        $DoZapisaniaXML .= "    <g:identifier_exists>false</g:identifier_exists>\n";
                    }
                }

                $DoZapisaniaXML .= "    <g:shipping_weight>".round($porownywarki->produkty[$i]['waga_produktu'], 2)." kg</g:shipping_weight>\n";

                $DoZapisaniaXML .= "    <g:availability>".$dostepnosc_tmp[$dostepnosc]."</g:availability>\n";
                
                if ( $dostepnosc_tmp[$dostepnosc] == 'preorder' && $porownywarki->produkty[$i]['data_dostepnosci'] != '' ) {
                     $DoZapisaniaXML .= "    <g:availability_date>".$porownywarki->produkty[$i]['data_dostepnosci']."</g:availability_date>\n";
                }
                
                $DoZapisaniaXML .= "    <g:condition>".$stan_produktu."</g:condition>\n";
                $DoZapisaniaXML .= "    <g:shipping>\n";
                $DoZapisaniaXML .= "    <g:country>".strtoupper((string)$_SESSION['krajDostawy']['kod'])."</g:country>\n";
                if ( $porownywarki->produkty[$i]['nazwa_wysylki'] != '' ) {
                    $DoZapisaniaXML .= "    <g:service>".$porownywarki->produkty[$i]['nazwa_wysylki']."</g:service>\n";
                }
                $DoZapisaniaXML .= "    <g:price>".$porownywarki->produkty[$i]['koszt_wysylki']."</g:price>\n";
                $DoZapisaniaXML .= "    </g:shipping>\n";
                
                // odbior lokalny
                if ( $porownywarki->produkty[$i]['odbior_osobisty'] == true ) {
                     //
                     $DoZapisaniaXML .= "    <g:pickup_method>ship_to_store</g:pickup_method>\n";
                     //
                     if ( $porownywarki->odbior_osobisty_czas != '' ) {
                          $DoZapisaniaXML .= "    <g:pickup_sla>" . $porownywarki->odbior_osobisty_czas . "</g:pickup_sla>\n";
                     }
                     //
                } else {
                     //
                     $DoZapisaniaXML .= "    <g:pickup_method>not_supported</g:pickup_method>\n";
                     //                  
                }
                
                // klasa energetyczna
                if ( $porownywarki->produkty[$i]['klasa_energetyczna'] != '' ) {
                     //
                     $DoZapisaniaXML .= "    <g:energy_efficiency_class>" . $porownywarki->produkty[$i]['klasa_energetyczna'] . "</g:energy_efficiency_class>\n";
                     //
                     if ( $porownywarki->produkty[$i]['klasa_energetyczna_min'] != '' ) {
                          $DoZapisaniaXML .= "    <g:min_energy_efficiency_class>" . $porownywarki->produkty[$i]['klasa_energetyczna_min'] . "</g:min_energy_efficiency_class>\n";
                     }
                     if ( $porownywarki->produkty[$i]['klasa_energetyczna_max'] != '' ) {
                          $DoZapisaniaXML .= "    <g:max_energy_efficiency_class>" . $porownywarki->produkty[$i]['klasa_energetyczna_max'] . "</g:max_energy_efficiency_class>\n";
                     }
                     //
                }
                
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
                             $DoZapisaniaXML .= "    <g:" . $key . ">". implode(';', (array)$wartosc_txt) . "</g:" . $key . ">";
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
                             $DoZapisaniaXML .= "    <g:" . $key . ">" . Porownywarki::TekstZamienEncje($value) . "</g:" . $key . ">\n";
                        }
                        //
                    }
                    //
                }            

                $DoZapisaniaXML .= "</item>\n";

                unset($dostepnosc, $stan_produktu);

            //}

        }
        // dane do zapisania do pliku END

        // jezeli poczatek pliku
        if ( ( isset($_POST['offset']) && (int)$_POST['offset'] == 0 ) ) {
            ///
            $CoDoZapisania  = "<?xml version=\"1.0\"?>\n";
            $CoDoZapisania .= "<rss version=\"2.0\" xmlns:g=\"http://base.google.com/ns/1.0\">\n";
            $CoDoZapisania .= "<channel>\n";
            $CoDoZapisania .= "<title>".DANE_NAZWA_FIRMY_PELNA."</title>\n";
            $CoDoZapisania .= "<link>".ADRES_URL_SKLEPU."</link>\n";
            $CoDoZapisania .= "<description>Google Shopping Feed</description>\n";

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
                  $CoDoZapisania .= "</channel>";
                  $CoDoZapisania .= "</rss>";
              }
              //
        }
        
        unset($DoZapisaniaXML);
        
    }
 
    if ( isset($ImportZewnetrzny) && isset($ZakonczeniePliku) ) {
         //
         $CoDoZapisania .= "</channel>";
         $CoDoZapisania .= "</rss>";
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