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

        $tablica_dostepnosci = Porownywarki::TablicaDostepnosci( $_POST['plugin'] );

        $DoZapisaniaXML = '';

        // dane do zapisania do pliku START

        for ( $i = 0, $c = count($porownywarki->produkty); $i < $c; $i++ ) {
        
            // pobranie i sprawdzenie ustawienia dostepnosci produktu - specyficzne dla porownywarki
            $dostepnosc = $porownywarki->produkty[$i]['dostepnosc_produktu'];

            if ( isset($tablica_dostepnosci[$porownywarki->produkty[$i]['dostepnosc_produktu']]) && $porownywarki->produkty[$i]['dostepnosc_produktu'] != '0' && $porownywarki->produkty[$i]['dostepnosc_produktu'] != '') {
                $dostepnosc = $tablica_dostepnosci[$porownywarki->produkty[$i]['dostepnosc_produktu']];
            } else {
                $dostepnosc = $porownywarki->dotepnosc_domyslna;
            }

            if ( $porownywarki->produkty[$i]['ilosc_produktu'] > 0) {
                $ilosc = ' stock="'.round($porownywarki->produkty[$i]['ilosc_produktu'], 0).'"';
            } else {
                $ilosc = ' stock="0"';
            }

            if ( $porownywarki->produkty[$i]['waga_produktu'] > 0) {
                $waga = ' weight="'.round($porownywarki->produkty[$i]['waga_produktu'], 2).'"';
            } else {
                $waga = '';
            }
            
            if ( $porownywarki->ceneo_kup_teraz == 1 ) {
                $kup_teraz = ' basket="1"';
            } elseif ( $porownywarki->ceneo_kup_teraz == 2 && $porownywarki->produkty[$i]['ceneo_kup_teraz'] == 1 ) {
                $kup_teraz = ' basket="1"';
            } else {
                $kup_teraz = '';
            }

            $DoZapisaniaXML .= "  <o id=\"".$porownywarki->produkty[$i]['id_produktu']."\" url=\"".$porownywarki->produkty[$i]['url_produktu']."\" price=\"".$porownywarki->produkty[$i]['cena_brutto_produktu']."\" avail=\"".$dostepnosc."\" set=\"0\" ".$waga.$kup_teraz.$ilosc." >\n";

            $DoZapisaniaXML .= "    <cat>\n";
            $DoZapisaniaXML .= "      <![CDATA[".$porownywarki->produkty[$i]['kategoria_produktu']."]]>\n";
            $DoZapisaniaXML .= "    </cat>\n";
            $DoZapisaniaXML .= "    <name>\n";
            $DoZapisaniaXML .= "      <![CDATA[".$porownywarki->produkty[$i]['nazwa_produktu']."]]>\n";
            $DoZapisaniaXML .= "    </name>\n";
            $DoZapisaniaXML .= "    <imgs>\n";
            $DoZapisaniaXML .= "      <main url=\"".$porownywarki->produkty[$i]['zdjecie_produktu']."\" />\n";
            
            // dodatkowe zdjecia
            if ( isset($porownywarki->produkty[$i]['dodatkowe_zdjecia']) && count($porownywarki->produkty[$i]['dodatkowe_zdjecia']) > 0 ) {
                 //
                 $DodatkoweZdjecia = $porownywarki->produkty[$i]['dodatkowe_zdjecia'];
                 //
                 foreach ( $DodatkoweZdjecia as $Zdjecie ) {
                    //
                    $DoZapisaniaXML .= "      <i url=\"" . $Zdjecie . "\" />\n";
                    // 
                 }
                 //
                 unset($DodatkoweZdjecia);
                 //
            }             
            
            $DoZapisaniaXML .= "    </imgs>\n";
            $DoZapisaniaXML .= "    <desc>\n";
            $DoZapisaniaXML .= "       <![CDATA[".$porownywarki->produkty[$i]['opis_produktu']."]]>\n";
            $DoZapisaniaXML .= "    </desc>\n";
            $DoZapisaniaXML .= "    <attrs>\n";
            $DoZapisaniaXML .= "      <a name=\"Producent\">\n";
            $DoZapisaniaXML .= "          <![CDATA[".$porownywarki->produkty[$i]['producent_produktu']."]]>\n";
            $DoZapisaniaXML .= "      </a>\n";
            $DoZapisaniaXML .= "      <a name=\"Kod producenta\">\n";
            $DoZapisaniaXML .= "          <![CDATA[".$porownywarki->produkty[$i]['kod_producenta_produktu']."]]>\n";
            $DoZapisaniaXML .= "      </a>\n";
            $DoZapisaniaXML .= "      <a name=\"EAN\">\n";
            $DoZapisaniaXML .= "          <![CDATA[".$porownywarki->produkty[$i]['numer_ean_produktu']."]]>\n";
            $DoZapisaniaXML .= "      </a>\n";
            $DoZapisaniaXML .= "      <a name=\"Jednostka\">\n";
            $DoZapisaniaXML .= "          <![CDATA[".$porownywarki->PokazJednostkeMiary($porownywarki->produkty[$i]['jm_id'])."]]>\n";
            $DoZapisaniaXML .= "      </a>\n";
            
            // rozporzadzenie gpsr
            $DoZapisaniaXML .= "      <a name=\"Producent odpowiedzialny\">\n";
            $DoZapisaniaXML .= "          <![CDATA[".((isset($porownywarki->produkty[$i]['producent'])) ? $porownywarki->produkty[$i]['producent']['producent_nazwa'] : '')."]]>\n";
            $DoZapisaniaXML .= "      </a>\n";            
            $DoZapisaniaXML .= "      <a name=\"Podmiot odpowiedzialny\">\n";
            $DoZapisaniaXML .= "          <![CDATA[".((isset($porownywarki->produkty[$i]['importer'])) ? $porownywarki->produkty[$i]['importer']['importer_nazwa'] : '')."]]>\n";
            $DoZapisaniaXML .= "      </a>\n";                        
            $DoZapisaniaXML .= "      <a name=\"Informacje o bezpieczeństwie\">\n";
            $DoZapisaniaXML .= "          <![CDATA[".$porownywarki->produkty[$i]['o_bezpieczenstwie']."]]>\n";
            $DoZapisaniaXML .= "      </a>\n";
            
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
                         $DoZapisaniaXML .= "      <a name=\"" . $key . "\">\n";
                         $DoZapisaniaXML .= "          <![CDATA[" . implode(';', (array)$wartosc_txt) . "]]>\n";
                         $DoZapisaniaXML .= "      </a>\n";               
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
                         $DoZapisaniaXML .= "      <a name=\"" . $key . "\">\n";
                         $DoZapisaniaXML .= "          <![CDATA[" . Porownywarki::TekstZamienEncje($value) . "]]>\n";
                         $DoZapisaniaXML .= "      </a>\n";
                    }
                    //
                }
                //
            }            

            $DoZapisaniaXML .= "    </attrs>\n";
            $DoZapisaniaXML .= "  </o>\n";
            
        }
        // dane do zapisania do pliku END

        // jezeli poczatek pliku
        if ( ( isset($_POST['offset']) && (int)$_POST['offset'] == 0 ) ) {
            ///
            $CoDoZapisania = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
            $CoDoZapisania .= "<offers xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" version=\"1\">\n";
            //
            // dane producentow
            $Producenci = array();
            //
            $ProduktyProducenci = new Porownywarki($_POST['plugin'], 0, 150000);
            //
            if ( isset($ProduktyProducenci->produkty) && count($ProduktyProducenci->produkty) > 0 ) {
                 //
                 for ( $i = 0, $c = count($ProduktyProducenci->produkty); $i < $c; $i++ ) {
                       //
                       if ( isset($ProduktyProducenci->produkty[$i]['producent']) ) {
                            //
                            $Producenci[ $ProduktyProducenci->produkty[$i]['producent']['producent_id'] ] = array('producent' => $ProduktyProducenci->produkty[$i]['producent'],
                                                                                                                  'importer' => $ProduktyProducenci->produkty[$i]['importer']);
                       }
                       //
                 }
                 //
            }
            //
            if ( count($Producenci) > 0 ) {
                 //
                 $CoDoZapisaniaTablica = array();
                 //
                 foreach ( $Producenci as $Producent ) {
                      //
                      $CoDoZapisaniaTmp = '    <p id="' . $Producent['producent']['producent_nazwa'] . '">' . "\n";
                      $CoDoZapisaniaTmp .= '      <name>' . $Producent['producent']['producent_nazwa'] . '</name>' . "\n"; 
                      $CoDoZapisaniaTmp .= '      <address>' . "\n";
                      $CoDoZapisaniaTmp .= '        <countryCode>' . $Producent['producent']['producent_kraj'] . '</countryCode>' . "\n";
                      $CoDoZapisaniaTmp .= '        <street>' . $Producent['producent']['producent_ulica'] . '</street>' . "\n";
                      $CoDoZapisaniaTmp .= '        <postalCode>' . $Producent['producent']['producent_kod_pocztowy'] . '</postalCode>' . "\n";
                      $CoDoZapisaniaTmp .= '        <city>' . $Producent['producent']['producent_miasto'] . '</city>' . "\n";
                      $CoDoZapisaniaTmp .= '      </address>' . "\n";
                      $CoDoZapisaniaTmp .= '      <contact>' . "\n";
                      $CoDoZapisaniaTmp .= '        <email>' . $Producent['producent']['producent_email'] . '</email>' . "\n";
                      $CoDoZapisaniaTmp .= '        <phoneNumber>' . $Producent['producent']['producent_telefon'] . '</phoneNumber>' . "\n";
                      $CoDoZapisaniaTmp .= '      </contact>' . "\n";
                      $CoDoZapisaniaTmp .= '    </p>';
                      //
                      $CoDoZapisaniaTablica['responsibleProducers'][] = $CoDoZapisaniaTmp;
                      //
                      $CoDoZapisaniaTmp = '    <p id="' . $Producent['importer']['importer_nazwa'] . '">' . "\n";
                      $CoDoZapisaniaTmp .= '      <name>' . $Producent['importer']['importer_nazwa'] . '</name>' . "\n"; 
                      $CoDoZapisaniaTmp .= '      <address>' . "\n";
                      $CoDoZapisaniaTmp .= '        <countryCode>' . $Producent['importer']['importer_kraj'] . '</countryCode>' . "\n";
                      $CoDoZapisaniaTmp .= '        <street>' . $Producent['importer']['importer_ulica'] . '</street>' . "\n";
                      $CoDoZapisaniaTmp .= '        <postalCode>' . $Producent['importer']['importer_kod_pocztowy'] . '</postalCode>' . "\n";
                      $CoDoZapisaniaTmp .= '        <city>' . $Producent['importer']['importer_miasto'] . '</city>' . "\n";
                      $CoDoZapisaniaTmp .= '      </address>' . "\n";
                      $CoDoZapisaniaTmp .= '      <contact>' . "\n";
                      $CoDoZapisaniaTmp .= '        <email>' . $Producent['importer']['importer_email'] . '</email>' . "\n";
                      $CoDoZapisaniaTmp .= '        <phoneNumber>' . $Producent['importer']['importer_telefon'] . '</phoneNumber>' . "\n";
                      $CoDoZapisaniaTmp .= '      </contact>' . "\n";
                      $CoDoZapisaniaTmp .= '    </p>';
                      //
                      $CoDoZapisaniaTablica['responsiblePersons'][] = $CoDoZapisaniaTmp;
                      //
                 }
                 //
                 $CoDoZapisania .= "\n" . '  <responsibleProducers>' . "\n" . implode("\n",  $CoDoZapisaniaTablica['responsibleProducers']) . "\n" . '  </responsibleProducers>' . "\n\n" . '  <responsiblePersons>' . "\n" . implode("\n",  $CoDoZapisaniaTablica['responsiblePersons']) . "\n" . '  </responsiblePersons>' . "\n\n";
                 //
            }
            //
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
              if ( isset($_POST['limit_max']) && (int)$_POST['limit_max'] <= (int)$_POST['offset'] + (int)$_POST['limit'] ) {
                   $CoDoZapisania .= "\n</offers>";
              }
              //
        }
        
        unset($DoZapisaniaXML);

    }
    
    if ( isset($ImportZewnetrzny) && isset($ZakonczeniePliku) ) {
         //
         $CoDoZapisania .= "\n</offers>";  
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