<?php

  // plik
  $WywolanyPlik = 'zamowienia_szczegoly';

  include('start.php');
  
  $AktywnyHash = false;
  $hashKod = '';
  
  if ( isset($_GET['zamowienie']) && STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak' && isset($_GET['id']) && (int)$_GET['id'] > 0) {
    
      $zamowienie = new Zamowienie((int)$_GET['id']);

      if ( count($zamowienie->info) == 0 ) {
           //
           Funkcje::PrzekierowanieURL('brak-strony.html'); 
           //
      }

      // ilosc dni od zlozenia zamowienia
      $IloscDni = round(((time() - FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia'])) / 86400), 0);
      
      if ( $IloscDni <= STR_PANELU_KLIENTA_BEZ_LOGOWANIA_WAZNOSC ) {
      
          $hashKod = hash("sha1", $zamowienie->info['id_zamowienia'] . ';' . $zamowienie->info['data_zamowienia'] . ';' . $zamowienie->klient['adres_email'] . ';' . $zamowienie->klient['id']);
          
          if ( $_GET['zamowienie'] === $hashKod ) {
               $AktywnyHash = true;
               if ( !isset($_SESSION['customer_email']) ) {
                    $_SESSION['customer_email'] = $zamowienie->klient['adres_email'];
               }
          }

      } else {
        
          $_SESSION['bladDniToken'] = true;
          Funkcje::PrzekierowanieSSL( 'logowanie.html', true );
        
      }
 
      unset($zamowienie);
    
  }

  if ( ((isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') || $AktywnyHash == true) && isset($_GET['id']) && (int)$_GET['id'] > 0 ) {
  
    $blad = false;
    if ( isset($_GET['id']) && (int)$_GET['id'] > 0 ) {
    
        $zapytanie = "SELECT customers_id FROM orders WHERE orders_id = '" . (int)$_GET['id'] . "' LIMIT 1";
        $sql = $GLOBALS['db']->open_query($zapytanie);

        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
            $info = $sql->fetch_assoc();
            if ( (int)$info['customers_id'] == (int)$_SESSION['customer_id'] || $AktywnyHash == true ) {
                $blad = false;
            } else {
                $blad = true;
            }
            unset($info);
        } else {
            $blad = true;
        }

        $GLOBALS['db']->close_query($sql);
        unset($zapytanie);

    } else {
      
        $blad = true;
        
    }

    if ( $blad ) {
        Funkcje::PrzekierowanieURL('brak-strony.html', true); 
    }

    unset($blad);  

    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI', 'KLIENCI_PANEL', 'PLATNOSCI', 'PRZYCISKI', 'REKLAMACJE', 'ZAMOWIENIE_REALIZACJA') ), $GLOBALS['tlumacz'] );

    // meta tagi
    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    $tpl->dodaj('__META_TYTUL', $Meta['tytul']);
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
    $tpl->dodaj('__META_OPIS', $Meta['opis']);
    unset($Meta);

    // breadcrumb
    $nawigacja->dodaj($GLOBALS['tlumacz']['PANEL_KLIENTA'],Seo::link_SEO('panel_klienta.php', '', 'inna'));
    $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_HISTORIA_ZAMOWIEN'],Seo::link_SEO('zamowienia_przegladaj.php', '', 'inna'));
    $nawigacja->dodaj($GLOBALS['tlumacz']['KLIENT_SZCZEGOLY_ZAMOWIENIA']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

    $zamowienie = new Zamowienie((int)$_GET['id']);
    
    if ( count($zamowienie->info) == 0 ) {
         //
         Funkcje::PrzekierowanieURL('brak-strony.html', true); 
         //
    }    
    
    // duplikowanie zamowien
    
    if ( isset($_GET['dodaj']) ) {

        // usuwa zawartosc koszyka przed wczytaniem
        if ( isset($_SESSION['koszyk']) ) {
          
            foreach ( $_SESSION['koszyk'] As $TablicaWartosci ) {
                //
                $GLOBALS['koszykKlienta']->UsunZKoszyka( $TablicaWartosci['id'] ); 
                //
            }      
            
        }

        $_SESSION['dodano_wszystkie'] = true;
        //
        foreach ( $zamowienie->produkty as $ProduktTmp ) {
            //
            $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( (int)$ProduktTmp['id_produktu'] ) );
            //
            if ($Produkt->CzyJestProdukt == true) {
            
                $Cech = array();
                
                if ( isset($ProduktTmp['attributes']) ) {

                    if ( count($ProduktTmp['attributes']) > 0 ) {
                      
                         foreach ( $ProduktTmp['attributes'] as $te ) {
                           
                            $Cech[ $te['id_cechy'] ] = 'x' . $te['id_cechy'] . '-' . $te['id_wartosci'];
                            
                         }
                         
                    }   

                }       

                ksort($Cech);
            
                $_SESSION['koszyk'][(int)$ProduktTmp['id_produktu'] . implode('', $Cech)] = array('id'          => $ProduktTmp['id_produktu'] . implode('', $Cech),
                                                                                                  'ilosc'       => (int)$ProduktTmp['ilosc'],
                                                                                                  'komentarz'   => '',
                                                                                                  'pola_txt'    => '',
                                                                                                  'rodzaj_ceny' => 'baza');

                $SprMagazyn = $GLOBALS['koszykKlienta']->SprawdzIloscProduktuMagazyn( $ProduktTmp['id_produktu'] . implode('', $Cech), false );
                
                if ( $SprMagazyn == true ) { 
                     $_SESSION['dodano_wszystkie'] = false;
                }
                
                unset($SprMagazyn);

            } else {
              
                $_SESSION['dodano_wszystkie'] = false;
             
            }
            //
            unset($Produkt);
            //
        }           
        //
        $GLOBALS['koszykKlienta']->PrzeliczKoszyk();            
        //   

        if ( count($zamowienie->produkty) != count($_SESSION['koszyk']) ) {
             $_SESSION['dodano_wszystkie'] = false;
        }

        Funkcje::PrzekierowanieSSL('zamowienia-szczegoly-zs-' . $zamowienie->info['id_zamowienia'] . '.html' . ((isset($_GET['zamowienie'])) ? '/zamowienie=' . $_GET['zamowienie'] : ''));
        
        exit;

    }
    
    $InformacjaPopup = '';

    if ( isset($_SESSION['dodano_wszystkie']) ) {

        if ( $_SESSION['dodano_wszystkie'] == false ) {
        
             $InformacjaPopup = $GLOBALS['tlumacz']['NIE_WSZYSTKIE_PRODUKTY_WCZYTANE'];

        } else {
          
             $InformacjaPopup = $GLOBALS['tlumacz']['PRODUKTY_WCZYTANE'];

        }
        
        unset($_SESSION['dodano_wszystkie']);

    }    

    $DodatkoweInformacje = array();
    
    // dodatkowe dane klienta
    $zapytanie = "SELECT * FROM customers c, customers_info ci where c.customers_id = '" . $zamowienie->klient['id'] . "' and c.customers_id = ci.customers_info_id";
    $sql = $db->open_query($zapytanie);  

    $DaneKlienta = $sql->fetch_assoc();

    $GLOBALS['db']->close_query($sql); 
    unset($zapytanie);     
    
    // dodatkowe informacje w panelu klienta
    $zapytanie = "SELECT cafd.orders_account_fields_id,
                         cafd.orders_account_fields_name, 
                         cafd.orders_account_fields_text, 
                         caf.orders_account_fields_type
                    FROM orders_account_fields caf, 
                         orders_account_fields_description cafd 
                   WHERE caf.orders_id = '" . $zamowienie->info['id_zamowienia'] . "' AND caf.orders_account_fields_id = cafd.orders_account_fields_id AND cafd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'";
     
    $sql = $db->open_query($zapytanie);  

    while ( $info = $sql->fetch_assoc() ) {    
        //
        $Tytul = '<b>' . $info['orders_account_fields_name'] . '</b>';
        $Wartosc = $info['orders_account_fields_text'];
        //
        if ( $info['orders_account_fields_type'] == 1 ) {
             //
             $Tytul = '';
             $Wartosc = '<a href="' . $Wartosc . '" target="_blank"><b>' . $info['orders_account_fields_name'] . '</b></a>';
             //
        }
        if ( $info['orders_account_fields_type'] == 2 ) {
             //
             $Tytul = '';
             //
             $UniqId = ($info['orders_account_fields_id'] * $info['orders_account_fields_id']);
             //
             $KluczSesji = base64_encode(serialize(array('tok' => Sesje::Token(), 'data' => $DaneKlienta['customers_info_date_account_created'], 'id' => $DaneKlienta['customers_id'], 'nr' => $UniqId, 'zamowienie' => true)));
             //
             $LinkPobierz = 'panel-klienta-pobierz-' . $KluczSesji . '.html';
             //
             unset($UniqId, $KluczSesji);             
             //
             $Wartosc = '<a href="' . $LinkPobierz . '" target="_blank"><b>' . $info['orders_account_fields_name'] . '</b></a>';
             //
             unset($LinkPobierz);
             //
        }
        //
        $DodatkoweInformacje[] = array('tytul' => $Tytul,
                                       'wartosc' => $Wartosc);
        //
        unset($Tytul, $Wartosc);
        //
    }

    $GLOBALS['db']->close_query($sql); 
    unset($zapytanie, $info, $DaneKlienta);    
    
    // czy mozna napisac zwrot
    
    $MozliwyZwrot = false;
    
    if ( ZWROTY_STATUS == 'tak' ) {
    
        // data ostatniej zmiany statusu
        $data_statusu = FunkcjeWlasnePHP::my_strtotime('1990-12-31 23:59:59');
        
        $zapytanie_status = "SELECT date_added FROM orders_status_history WHERE orders_id = '" . (int)$zamowienie->info['id_zamowienia'] . "' ORDER BY date_added DESC LIMIT 1";
        $sql_status = $GLOBALS['db']->open_query($zapytanie_status);    
          
        if ( (int)$GLOBALS['db']->ile_rekordow($sql_status) > 0 ) {
             //
             $infs = $sql_status->fetch_assoc();
             //
             $data_statusu = FunkcjeWlasnePHP::my_strtotime($infs['date_added']);
             //
             unset($infs);
             //
        }
        unset($zapytanie_status);  
        $GLOBALS['db']->close_query($sql_status);   

        if ( $data_statusu + (ZWROTY_ILE_DNI * 86400) >= time() ) {    
        
             // sprawdzi czy nie bylo zwrotu
             if ( $zamowienie->zwrot == false ) {  
                     
                  $MozliwyZwrot = true;
                  
             }

        }
        
    }
    
    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $zamowienie, $DodatkoweInformacje, $AktywnyHash, ((Funkcje::czyNiePuste($zamowienie->info['data_wysylki'])) ? 'tak' : 'nie'), $InformacjaPopup);
    
    $infoPlatnosc = '';

    if ( $zamowienie->info['platnosc_klasa'] == 'platnosc_payu' || 
         $zamowienie->info['platnosc_klasa'] == 'platnosc_payu_rest' || 
         $zamowienie->info['platnosc_klasa'] == 'platnosc_blik' || 
         $zamowienie->info['platnosc_klasa'] == 'platnosc_dotpay' || 
         $zamowienie->info['platnosc_klasa'] == 'platnosc_przelewy24' || 
         $zamowienie->info['platnosc_klasa'] == 'platnosc_pbn' || 
         $zamowienie->info['platnosc_klasa'] == 'platnosc_payeezy' ||  
         $zamowienie->info['platnosc_klasa'] == 'platnosc_paypal' || 
         $zamowienie->info['platnosc_klasa'] == 'platnosc_cashbill' || 
         $zamowienie->info['platnosc_klasa'] == 'platnosc_transferuj' || 
         $zamowienie->info['platnosc_klasa'] == 'platnosc_tpay' || 
         $zamowienie->info['platnosc_klasa'] == 'platnosc_imoje' || 
         $zamowienie->info['platnosc_klasa'] == 'platnosc_ileasing' || 
         $zamowienie->info['platnosc_klasa'] == 'platnosc_iraty' || 
         $zamowienie->info['platnosc_klasa'] == 'platnosc_eservice' ||
         $zamowienie->info['platnosc_klasa'] == 'platnosc_paynow' ||
         $zamowienie->info['platnosc_klasa'] == 'platnosc_hotpay' ||
         $zamowienie->info['platnosc_klasa'] == 'platnosc_bluemedia' ||
         $zamowienie->info['platnosc_klasa'] == 'platnosc_comfino' ||
         $zamowienie->info['platnosc_klasa'] == 'platnosc_leaselink' ||
         $zamowienie->info['platnosc_klasa'] == 'platnosc_paypo' ||
         $zamowienie->info['platnosc_klasa'] == 'platnosc_lukas' ||
         $zamowienie->info['platnosc_klasa'] == 'platnosc_santander' ) {

         if ( isset($_SESSION['platnoscElektroniczna']) ) unset($_SESSION['platnoscElektroniczna']);
    
         if ( $zamowienie->info['platnosc_tablica'] != '#' && $zamowienie->info['platnosc_klasa'] != 'platnosc_comfino' ) {
           
            $platnosci = new Platnosci( 1, $zamowienie->info['id_zamowienia'] );
            $platnosci->Podsumowanie( 0, $zamowienie->info['platnosc_klasa'], $zamowienie->info['id_zamowienia'] );
            
            // po aktualizacji trzeba odnowic w klasie ciag tablicy
            $sqlTablica = $GLOBALS['db']->open_query("SELECT payment_method_array FROM orders WHERE orders_id = '" . (int)$zamowienie->info['id_zamowienia'] . "'");
            //
            $platnoscTablica = $sqlTablica->fetch_assoc();              
            $zamowienie->info['platnosc_tablica'] = $platnoscTablica['payment_method_array'];
            //
            $GLOBALS['db']->close_query($sqlTablica);
            unset($platnoscTablica);
        
            require_once('moduly/platnosc/raporty/' . str_replace('platnosc_', '', (string)$zamowienie->info['platnosc_klasa']) . '/formularz.php');
            
            $platnoscTablica = @unserialize($zamowienie->info['platnosc_tablica']);
            $formularzPlatnosci = PowtorzPlatnosc($platnoscTablica, (int)$_GET['id']);
            unset($platnoscTablica);

            $infoPlatnosc .= ((trim((string)$zamowienie->info['platnosc_info']) != '') ? '' : '') . $formularzPlatnosci;
            
            unset($formularzPlatnosci);
            
         }
         
         if ( $zamowienie->info['platnosc_tablica'] != '#' && $zamowienie->info['platnosc_klasa'] == 'platnosc_comfino' ) {
           
            // po aktualizacji trzeba odnowic w klasie ciag tablicy
            $sqlTablica = $GLOBALS['db']->open_query("SELECT payment_method_array FROM orders WHERE orders_id = '" . (int)$zamowienie->info['id_zamowienia'] . "'");
            //
            $platnoscTablica = $sqlTablica->fetch_assoc();              
            $zamowienie->info['platnosc_tablica'] = $platnoscTablica['payment_method_array'];
            //
            $GLOBALS['db']->close_query($sqlTablica);
            unset($platnoscTablica);
        
            require_once('moduly/platnosc/raporty/' . str_replace('platnosc_', '', $zamowienie->info['platnosc_klasa']) . '/formularz.php');
            
            $platnoscTablica = @unserialize($zamowienie->info['platnosc_tablica']);
            $formularzPlatnosci = PowtorzPlatnosc($platnoscTablica, (int)$_GET['id']);
            unset($platnoscTablica);

            $infoPlatnosc .= $formularzPlatnosci;
            
            unset($formularzPlatnosci);
            
         }
    }
    
    $srodek->dodaj('__PLATNOSC_INFO', $infoPlatnosc);

    $srodek->dodaj('__NUMER_ZAMOWIENIA', (int)$_GET['id']);
    $srodek->dodaj('__METODA_PLATNOSCI', $zamowienie->info['metoda_platnosci']);
    $srodek->dodaj('__WYSYLKA_MODUL', $zamowienie->info['wysylka_modul'] . ( $zamowienie->info['wysylka_info'] != '' ? ' ('.$zamowienie->info['wysylka_info'].')' : ''));
    $srodek->dodaj('__DATA_ZAMOWIENIA', date('d-m-Y H:i:s',FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia'])));
    $srodek->dodaj('__STATUS_ZAMOWIENIA', Funkcje::pokazNazweStatusuZamowienia($zamowienie->info['status_zamowienia'],(int)$_SESSION['domyslnyJezyk']['id']));
    $srodek->dodaj('__OPIEKUN_ZAMOWIENIA', Funkcje::PokazOpiekuna($zamowienie->info['opiekun']));
    
    $srodek->dodaj('__DATA_WYSYLKI', ((Funkcje::czyNiePuste($zamowienie->info['data_wysylki'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_wysylki'])) : ''));
    
    $srodek->dodaj('__DOKUMENT_SPRZEDAZY_NAZWA', $zamowienie->info['dokument_zakupu_nazwa']);

    foreach ($zamowienie->dostawa as $key => $value) {
        $srodek->dodaj('__DOSTAWA_'.strtoupper((string)$key), $value);
    }
    
    foreach ($zamowienie->platnik as $key => $value) {
        $srodek->dodaj('__PLATNIK_'.strtoupper((string)$key), $value);
    }
    
    $hashKod = '';    
    if ( STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak' ) {
         $hashKod = '/zamowienie=' . hash("sha1", $zamowienie->info['id_zamowienia'] . ';' . $zamowienie->info['data_zamowienia'] . ';' . $zamowienie->klient['adres_email'] . ';' . $zamowienie->klient['id']);
    }    
    // jezeli klient jest zalogowany to nie potrzeba hasha
    if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {
         $hashKod = '';
    }

    $srodek->dodaj('__PDF_ZAMOWIENIE', '<a class="pdfIkona" href="zamowienia-szczegoly-pdf-' . (int)$_GET['id'] . '.html' . $hashKod . '">' . $GLOBALS['tlumacz']['DRUKUJ_ZAMOWIENIE'] . '</a>');
    
    $srodek->dodaj('__PDF_FAKTURA', '');
    if ( FAKTURA_POBIERANIE == 'tak' ) { 
         $srodek->dodaj('__PDF_FAKTURA', '<a class="pdfIkona" href="zamowienia-faktura-pdf-' . (int)$_GET['id'] . '.html' . $hashKod . '">' . $GLOBALS['tlumacz']['DRUKUJ_FAKTURE'] . '</a>');
    }

    // sprzedaz elektroniczna
    $srodek->dodaj('__LINK_POBRANIA_PLIKOW', $zamowienie->sprzedaz_online_link . $hashKod);    
    
    $srodek->dodaj('__LISTA_REKLAMACJI', '<div class="TrescSekcji">' . $GLOBALS['tlumacz']['BRAK_DANYCH_DO_WYSWIETLENIA'] . '</div>');  

    $IloscReklamacji = 0;
    
    if ( REKLAMACJE_STATUS == 'tak' ) {
      
        // lista reklamacji do zamowienia 
        $zapytanieReklamacja = "SELECT * FROM complaints where complaints_customers_orders_id = '" . (int)$_GET['id'] . "'";
        $sqlReklamacja = $db->open_query($zapytanieReklamacja);  

        $ZlozoneReklamacje = '';
        
        if ((int)$GLOBALS['db']->ile_rekordow($sqlReklamacja) > 0) {

            while ($info = $sqlReklamacja->fetch_assoc()) {
                //
                $hashReklamacja = '';
                if ( STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak' ) {
                     //
                     $hashReklamacja = '/reklamacja=' . hash("sha1", $info['complaints_rand_id'] . ';' . $info['complaints_date_created'] . ';' . $info['complaints_customers_email'] . ';' . $info['complaints_customers_id'] . ';' . $info['complaints_customers_orders_id']);
                     //
                }
                // jezeli klient jest zalogowany nie jest potrzebny hash
                if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {
                    $hashReklamacja = '';
                }
                //
                $ReklamacjaSzczegoly = '<a href="' . ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU )."/reklamacje-szczegoly-rs-" . $info['complaints_rand_id'] . ".html" . $hashReklamacja . '"><b>' . $info['complaints_rand_id'] . '</b></a>';         
                //
                $ZlozoneReklamacje .= '<div class="TrescSekcji">' . $GLOBALS['tlumacz']['BYLA_REKLAMACJA_NR'] . ' ' . $ReklamacjaSzczegoly . '</div>';
                //
                unset($ReklamacjaSzczegoly, $hashReklamacja);
                //
                $IloscReklamacji++;
                //
            }
            
        }
        
        if ( $ZlozoneReklamacje != '' ) {
             //
             $srodek->dodaj('__LISTA_REKLAMACJI', $ZlozoneReklamacje);    
             //
        }

        $GLOBALS['db']->close_query($sqlReklamacja); 
        unset($zapytanieReklamacja);     
    
        unset($ZlozoneReklamacje);
        
    }
    
    $srodek->dodaj('__LISTA_ZWROTOW', '<div class="TrescSekcji">' . $GLOBALS['tlumacz']['BRAK_DANYCH_DO_WYSWIETLENIA'] . '</div>');  

    $IloscZwrotow = 0;    
    
    if ( ZWROTY_STATUS == 'tak' ) {
    
        // lista zwrotow do zamowienia 
        $zapytanieZwrot = "SELECT * FROM return_list where return_customers_orders_id = '" . (int)$_GET['id'] . "'";
        $sqlZwrot = $db->open_query($zapytanieZwrot);  

        $ZlozonyZwrot = '';
        
        if ((int)$GLOBALS['db']->ile_rekordow($sqlZwrot) > 0) {
          
            while ($info = $sqlZwrot->fetch_assoc()) {
                //
                $hashZwrot = '';
                if ( STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak' ) {
                     //
                     $hashZwrot = '/zwrot=' . hash("sha1", $info['return_rand_id'] . ';' . $info['return_date_created'] . ';' . $info['return_customers_id'] . ';' . $info['return_customers_orders_id']);
                     //
                }
                // jezeli klient jest zalogowany nie jest potrzebny hash
                if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {
                    $hashZwrot = '';
                }
                //
                $ZwrotSzczegoly = '<a href="' . ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU )."/zwroty-produktow-szczegoly-zp-" . $info['return_rand_id'] . ".html" . $hashZwrot . '"><b>' . $info['return_rand_id'] . '</b></a>';         
                //
                $ZlozonyZwrot .= '<div class="TrescSekcji">' . $GLOBALS['tlumacz']['BYL_ZWROT_NR'] . ' ' . $ZwrotSzczegoly . '</div>';
                //
                unset($ZwrotSzczegoly, $hashZwrot);
                //
                $IloscZwrotow++;
                //
            }
            
        }
        
        if ( $ZlozonyZwrot != '' ) {
             //
             $srodek->dodaj('__LISTA_ZWROTOW', $ZlozonyZwrot);    
             //
        }

        $GLOBALS['db']->close_query($sqlZwrot); 
        unset($zapytanieZwrot);   

    }        
    
    $srodek->dodaj('__NAPISANIE_REKLAMACJI', '');
    
    if ( $IloscReklamacji < (int)REKLAMACJE_ILOSC_ZGLOSZEN && (FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia']) + ((int)REKLAMACJE_ILE_DNI * 86400)) >= time() ) {
         //
         $srodek->dodaj('__NAPISANIE_REKLAMACJI', '<a class="przycisk" style="margin-top:10px" href="' . ((WLACZENIE_SSL == 'tak') ? ADRES_URL_SKLEPU_SSL . '/' : '') . 'reklamacje-napisz.html/id=' . $zamowienie->info['id_zamowienia'] . $hashKod . '">' . $GLOBALS['tlumacz']['REKLAMACJA_DO_ZAMOWIENIA'] . '</a>');
         //
    }
    
    $srodek->dodaj('__NAPISANIE_ZWROTU', '');
    
    if ( $IloscZwrotow == 0 && $MozliwyZwrot == true ) {
         //
         $srodek->dodaj('__NAPISANIE_ZWROTU', '<a class="przycisk" style="margin-top:10px" href="' . ((WLACZENIE_SSL == 'tak') ? ADRES_URL_SKLEPU_SSL . '/' : '') . 'zwroty-produktow-napisz.html/id=' . $zamowienie->info['id_zamowienia'] . $hashKod . '">' . $GLOBALS['tlumacz']['ZGLOS_ZWROT'] . '</a>');
         //
    }
    
    // dodanie do koszyka produktow
    $srodek->dodaj('__DODANIE_DO_KOSZYKA', '<a class="przycisk" style="margin-top:20px;line-height:normal" href="' . ((WLACZENIE_SSL == 'tak') ? ADRES_URL_SKLEPU_SSL . '/' : '') . 'zamowienia-szczegoly-zs-' . $zamowienie->info['id_zamowienia'] . '.html' . $hashKod . '/dodaj=tak">'.$GLOBALS['tlumacz']['DODAJ_PONOWNIE_DO_KOSZYKA'].'</a>');

    $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

    unset($srodek, $WywolanyPlik, $DodatkoweInformacje);

    include('koniec.php');

  } else {

    Funkcje::PrzekierowanieSSL( 'logowanie.html', true );

  }
?>