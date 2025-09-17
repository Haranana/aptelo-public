<?php

if ( isset($pobierzFunkcje) ) {

    $this->ProduktCechyIlosc();
    $this->ProduktDodatkowePolaTekstowe();
    
    // jezeli jest podane id z cechami
    // sprawdzi jaki jest stan magazynowy danej kombinacji cech
    
    $NajnizszaCenaKomunikat = '';
    
    if ( $id != '' ) {
        //
        
        // sformatuj cechy
        $cechy = str_replace('x', ',', (string)$id);
        $cechy = substr((string)$cechy, 1, strlen((string)$cechy));        
        
        if ( strpos((string)$id, 'U') > -1 ) {

            $cechy = substr((string)$cechy, 0, strpos((string)$cechy, 'U'));
        
        }        
        
        if ( $cechy != '' ) {
        
            // cache zapytania
            $WynikCache = $GLOBALS['cache']->odczytaj('Produkt_Id_' . $this->id_produktu . '_Cechy_' . md5($cechy), CACHE_PRODUKTY);   
            
            if ( !$WynikCache && !is_array($WynikCache) ) { 
              
                 // szuka wartosci dla okreslonych cech w tablicy stock z cechami
                 $zapytanieCechy = "SELECT products_stock_quantity, products_stock_availability_id, products_stock_shipping_time_id, products_stock_model, products_stock_ean, products_stock_price_tax, products_stock_old_price, products_stock_min_price_30_day, products_stock_min_price_30_day_date FROM products_stock WHERE products_id = '" . $this->id_produktu . "' and products_stock_attributes = '" . $cechy . "'";
                 $sqlCecha = $GLOBALS['db']->open_query($zapytanieCechy); 
                 
                 $StanCechy = array();

                 if ( (int)$GLOBALS['db']->ile_rekordow($sqlCecha) > 0 ) {
                      //
                      $infs = $sqlCecha->fetch_assoc();
                      $StanCechy = $infs;
                      unset($infs);
                      //
                 }
                 
                 $GLOBALS['db']->close_query($sqlCecha);                          
                 $GLOBALS['cache']->zapisz('Produkt_Id_' . $this->id_produktu . '_Cechy_' . md5($cechy), $StanCechy, CACHE_PRODUKTY);   
                 
                 unset($zapytanieCechy, $sqlCecha, $infs);         
                 
            } else {
              
                 $StanCechy = $WynikCache;
                  
            }
            
        }
        
        // generowanie komunikatu o najnizszej cenie w 30 dni - dla cech z przypisanymi cenami
        if ( HISTORIA_CEN == 'tak' && $_SESSION['poziom_cen'] == 1 && $this->infoSql['options_type'] == 'ceny' ) {
          
             $NajnizszaCenaKomunikat = $GLOBALS['tlumacz']['HISTORIA_CENY_BRAK'];
             //
             if ( (float)$this->infoSql['products_min_price_30_day'] > 0 ) {
                  //
                  $info_historia = $GLOBALS['tlumacz']['HISTORIA_CENY_KOMUNIKAT'];
                  //
                  if ( $this->produktDnia == true || (float)$this->info['cena_poprzednia_bez_formatowania']  > 0 ) {
                       $info_historia = $GLOBALS['tlumacz']['HISTORIA_CENY_KOMUNIKAT_PROMOCJA'];
                  }
                  //
                  $NajnizszaCenaKomunikat = str_replace('{DATA}', date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($this->info['cena_najnizsza_30_dni_data'])), str_replace('{CENA}', $this->info['cena_najnizsza_30_dni'], $info_historia));
                  unset($info_historia);             
                  //
             }
             //
             if ( $cechy != '' ) {
               
                 if (isset($StanCechy['products_stock_min_price_30_day']) && isset($StanCechy['products_stock_min_price_30_day_date']) && (float)$StanCechy['products_stock_price_tax'] > 0) {
                    //
                    $IloscDni = 30;
                    //
                    if ( $this->infoSql['products_min_price_30_day_date_created'] != '0000-00-00' ) {
                         //
                         $IloscDni += ceil((time() - FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_min_price_30_day_date_created'])) / 86400);
                         //
                    }  
                    //
                    if ( $StanCechy['products_stock_min_price_30_day'] > 0 && FunkcjeWlasnePHP::my_strtotime($StanCechy['products_stock_min_price_30_day_date']) > (time() - (86400 * $IloscDni)) ) {
                         //
                         $CenaNajnizszaTmp = $GLOBALS['waluty']->FormatujCene( $StanCechy['products_stock_min_price_30_day'], 0, 0, $this->infoSql['products_currencies_id'] );
                         //
                         $info_historia = $GLOBALS['tlumacz']['HISTORIA_CENY_KOMUNIKAT'];
                         //
                         if ( $this->produktDnia == true || (float)$StanCechy['products_stock_old_price']  > 0 ) {
                              $info_historia = $GLOBALS['tlumacz']['HISTORIA_CENY_KOMUNIKAT_PROMOCJA'];
                         }
                         //
                         $NajnizszaCenaKomunikat = str_replace('{DATA}', date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($StanCechy['products_stock_min_price_30_day_date'])), str_replace('{CENA}', $CenaNajnizszaTmp['brutto'], $info_historia));
                         unset($info_historia, $CenaNajnizszaTmp);
                         //
                    }
                    //
                    unset($IloscDni);
                    //
                 }
                 
             }
             
        }

        if ( $cechy != '' ) {

            //        
            // jezeli jest magazyn cech to jako ilosc produktu przyjmie ilosc cechy
            if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && CECHY_MAGAZYN == 'tak' && $this->infoSql['products_control_storage'] > 0 ) {
                 //
                 if ( isset($StanCechy['products_stock_quantity']) ) {
                      $this->infoSql['products_quantity'] = $StanCechy['products_stock_quantity'];
                      // jezeli stan magazynowy nizszy o 0
                      if ( STAN_MAGAZYNOWY_PONIZEJ_ZERO == 'tak' && (float)$this->infoSql['products_quantity'] < 0 ) {              
                           //
                           $this->infoSql['products_quantity'] = 0;
                           //
                      }                      
                 } else {
                      $this->infoSql['products_quantity'] = 0;
                 }
                 //
            }
            //
            // podstawi nr katalogowy cechy
            if ( isset($StanCechy['products_stock_model']) && !empty($StanCechy['products_stock_model']) ) {
                 //
                 $this->infoSql['products_model'] = $StanCechy['products_stock_model'];
                 //
            }
            // podstawi kod ean
            if ( isset($StanCechy['products_stock_ean']) && !empty($StanCechy['products_stock_ean']) ) {
                 //
                 $this->infoSql['products_ean'] = $StanCechy['products_stock_ean'];
                 //
            }        
            // jezeli cecha ma dosteponosc to produkt przyjmie jej id
            if ( isset($StanCechy['products_stock_availability_id']) && (int)$StanCechy['products_stock_availability_id'] > 0 ) {
                 //
                 $this->infoSql['products_availability_id'] = $StanCechy['products_stock_availability_id'];
                 //
            }
            // jezeli cecha ma czas wysylki to produkt przyjmie jej id
            if ( isset($StanCechy['products_stock_shipping_time_id']) && (int)$StanCechy['products_stock_shipping_time_id'] > 0 ) {
                 //
                 $this->infoSql['products_shipping_time_id'] = $StanCechy['products_stock_shipping_time_id'];
                 //
            }        
            //
        } else {
            //
            $this->infoSql['products_quantity'] = $this->infoSql['products_quantity'];
            //
            // jezeli jest magazyn cech to jako ilosc produktu przyjmie ilosc cechy
            if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && CECHY_MAGAZYN == 'tak' && $this->infoSql['products_control_storage'] > 0 ) {
                 $this->infoSql['products_quantity'] = 0;
            }            
            //
            $this->infoSql['products_model'] = $this->infoSql['products_model'];
            $this->infoSql['products_ean'] = $this->infoSql['products_ean'];
            $this->infoSql['products_availability_id'] = $this->infoSql['products_availability_id'];
            $this->infoSql['products_shipping_time_id'] = $this->infoSql['products_shipping_time_id'];
            //
        }

    }

    // ustala co bedzie wpisane domyslnie w polu INPUT ilosci produktu
    $IloscZakupu = 1;

    // sprawdza czy jest przyrost ilosci
    if ($this->infoSql['products_quantity_order'] != 0) {
        $IloscZakupu = $this->infoSql['products_quantity_order'];
    }
        
    // jezeli jest puste pola min ilosc zakupu
    if ($this->infoSql['products_minorder'] != 0) {
    
        if ( $this->infoSql['products_minorder'] > $this->infoSql['products_quantity_order'] ) {
            // jezeli nie jest puste pola min zakupu
            $IloscZakupu = $this->infoSql['products_minorder'];
        }
        
    }        
    
    $MinIlosc = ((float)$this->infoSql['products_minorder'] > 0 ? $this->infoSql['products_minorder'] : 0);
    $MaxIlosc = ((float)$this->infoSql['products_maxorder'] > 0 ? $this->infoSql['products_maxorder'] : 0);
    
    $PrzyrostIlosci = ((float)$this->infoSql['products_quantity_order'] > 0 ? $this->infoSql['products_quantity_order'] : 0);
             
    // tworzy pusta tablice dla zakupow
    $this->zakupy = array('mozliwe_kupowanie'   => 'nie',
                          'pokaz_koszyk'        => ( ($this->cechyIlosc > 0) ? 'tak' : 'nie' ),
                          'minimalna_ilosc'     => '',
                          'maksymalna_ilosc'    => '',
                          'przyrost_ilosci'     => '',    
                          'input_ilosci'        => '',
                          'jednostka_miary'     => '',
                          'ilosc_magazyn'       => '',
                          'ilosc_magazyn_jm'    => '',
                          'nr_kat_cechy'        => '',
                          'nr_ean_cechy'        => '',
                          'id_dostep_cechy'     => '',
                          'nazwa_dostepnosci'   => '',
                          'id_czasu_wys_cechy'  => '',
                          'nazwa_czasu_wysylki' => '',
                          'przycisk_kup'        => '',
                          'przycisk_szczegoly'  => '',
                          'id_unikat'           => '',
                          'ilosc_gratisu'       => '',                             
                          'input_ilosci_gratis' => '',
                          'przycisk_kup_gratis' => '',
                          'ma_cechy'            => '',
                          'ma_pola_tekstowe'    => '',
                          'info_cena_30_dni'    => ''
    );                                               
                               
    // sprawdza czy moze wyswietlic przycisk dodania do koszyka
    $PozwolNaZakup = true;
    
    // sprawdza czy mozna wyswietlic przycisk koszyka ( w listingu produktow )
    $PokazKoszyk = ( ($this->cechyIlosc > 0) ? true : false );

    // jezeli jest wlaczony stan magazynowy i wylaczone kupowanie mimo brakow to stan magazynowy musi byc wiekszy od 0    
    if ( $this->infoSql['products_control_storage'] == 1 ) {
      
        if ( $MinIlosc > 0 ) {
            //
            if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && $this->infoSql['products_quantity'] < $MinIlosc ) {
                $PozwolNaZakup = false;
                $PokazKoszyk = false;
            }
            //
          } else {            
            //
            if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && $this->infoSql['products_quantity'] <= 0 ) {
                $PozwolNaZakup = false;
                $PokazKoszyk = false;
            }
            //
        }
        
    }
    
    // jezeli produkt nie ma ceny to wylacza mozliwosc zakupu
    if ( $this->infoSql['products_price_tax'] <= 0 && $PozwolNaZakup == true ) {
        $PozwolNaZakup = false;
        $PokazKoszyk = false;
    }
    // jezeli ceny sa tylko widoczne dla klientow zalogowanych
    if ( CENY_DLA_WSZYSTKICH == 'nie' && ((int)$_SESSION['customer_id'] == 0 || $_SESSION['gosc'] == '1') && $PozwolNaZakup == true ) {
        $PozwolNaZakup = false;
        $PokazKoszyk = false;
    }
    // ukrycie ceny dla niezalogowanych tylko dla konkretnego produktu
    if ( (int)$this->infoSql['products_price_login'] == 1 ) {
         //
         if ( ((int)$_SESSION['customer_id'] == 0 || $_SESSION['gosc'] == '1')) {
              $PozwolNaZakup = false;
              $PokazKoszyk = false;
         }
         //
    }    
    // ukrycie cen dla wszystkich
    if ( UKRYJ_CENY == 'nie' ) {
        $PozwolNaZakup = false;
        $PokazKoszyk = false;
    }              
    
    // jezeli produkt ma wylaczone kupowanie
    if ( $this->infoSql['products_buy'] == '0' && $PozwolNaZakup == true ) {
        $PozwolNaZakup = false;
        $PokazKoszyk = false;
    }
    
    // sprawdzi czy produkt nie jest zestawem 
    if ( $this->infoSql['products_set'] == 1 ) {
         //
         $TablicaPrdZestaw = $this->zestawProdukty;
         foreach ( $TablicaPrdZestaw as $Id => $Dane ) {
             //
             // jezeli jest wylaczone kupowanie jednego produktu ze zestawu
             if ( $Dane['status_kupowania'] == 'nie' ) {
                   $PozwolNaZakup = false;
                   $PokazKoszyk = false;
                   break;
             }
             //
         }
         //
         unset($TablicaPrdZestaw);
         //
    }      
    
    // jezeli jest wogole w sklepie wylaczone kupowanie - sklep jako katalog produktow
    if ( PRODUKT_KUPOWANIE_STATUS == 'nie' && $PozwolNaZakup == true ) {
        $PozwolNaZakup = false;
        $PokazKoszyk = false;
    }        
    
    // sprawdza czy mozna kupowac przy dostepnosci produktu
    //
    $NazwaDostepnosci = '';
    //

    if ( $this->infoSql['products_availability_id'] > 0 ) {
        //
        // jezeli jest automatyczna dostepnosc
        if ( $this->infoSql['products_availability_id'] == '99999' ) {
            $Kupowanie = 'tak';
            $Automatyczna = $this->PokazIdDostepnosciAutomatycznych( $this->infoSql['products_quantity']);
            if ( $Automatyczna != '0' ) {
                 $Kupowanie = $GLOBALS['dostepnosci'][ $Automatyczna ]['kupowanie'];
                 // jezeli dostepnosc jest w formie obrazka
                 if ( !empty($GLOBALS['dostepnosci'][ $Automatyczna ]['foto']) ) {
                     $NazwaDostepnosci = '<img src="' . KATALOG_ZDJEC . '/' . $GLOBALS['dostepnosci'][ $Automatyczna ]['foto'] . '" alt="' . $GLOBALS['dostepnosci'][ $Automatyczna ]['dostepnosc'] . '" />';
                   } else {
                     $NazwaDostepnosci = $GLOBALS['dostepnosci'][ $Automatyczna ]['dostepnosc'];
                 }                
                 //                     
            }
           } else {
            $Kupowanie = 'nie';
            if ( isset($GLOBALS['dostepnosci'][ $this->infoSql['products_availability_id'] ]) ) {
                 //
                 $Kupowanie = $GLOBALS['dostepnosci'][ $this->infoSql['products_availability_id'] ]['kupowanie'];
                 //
                 // jezeli dostepnosc jest w formie obrazka
                 if ( !empty($GLOBALS['dostepnosci'][ $this->infoSql['products_availability_id'] ]['foto']) ) {
                     $NazwaDostepnosci = '<img src="' . KATALOG_ZDJEC . '/' . $GLOBALS['dostepnosci'][ $this->infoSql['products_availability_id'] ]['foto'] . '" alt="' . $GLOBALS['dostepnosci'][ $this->infoSql['products_availability_id'] ]['dostepnosc'] . '" />';
                   } else {
                     $NazwaDostepnosci = $GLOBALS['dostepnosci'][ $this->infoSql['products_availability_id'] ]['dostepnosc'];
                 }                
                 //
            }
        }  
        //
        if ( $Kupowanie == 'nie' ) {
             $PozwolNaZakup = false;
             // jezeli jest dostepnosc bez kupowania to nie pokaze koszyka
             // usuniecie wpisu spowoduje ze koszyk bedzie pokazywal sie zawsze jezeli produkt
             // bedzie mial cechy - niezaleznie od dostepnosci
             $PokazKoszyk = false;
        }
        //
    }
    //
    
    // jezeli jest stan = 0 i id czasu wysylki dla stanu 0
    if ( (float)$this->infoSql['products_quantity'] < 0.01 && (int)$this->infoSql['products_shipping_time_zero_quantity_id'] > 0 ) {
         //
         $this->infoSql['products_shipping_time_id'] = (int)$this->infoSql['products_shipping_time_zero_quantity_id'];
         //
    }    
    
    $NazwaCzasuWysylki = '';
    
    if ( (int)$this->infoSql['products_shipping_time_id'] > 0 ) {
         //
         $TablicaCzasWysylki = array();
         //
         // cache zapytania
         $WynikCache = $GLOBALS['cache']->odczytaj('ProduktCzasWysylki_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);      

         if ( !$WynikCache && !is_array($WynikCache) ) { 

            $zapytanie = "select s.products_shipping_time_id, s.products_shipping_time_day, sd.products_shipping_time_name from products_shipping_time s, products_shipping_time_description sd where s.products_shipping_time_id = sd.products_shipping_time_id and sd.language_id = '" . $this->jezykDomyslnyId . "' order by s.products_shipping_time_day";
            $sqls = $GLOBALS['db']->open_query($zapytanie);
            
            if ( (int)$GLOBALS['db']->ile_rekordow($sqls) > 0 ) {
              
                 while ($infs = $sqls->fetch_assoc()) {
                    $TablicaCzasWysylki[$infs['products_shipping_time_id']] = array( 'nazwa' => $infs['products_shipping_time_name'],
                                                                                       'ilosc_dni' => $infs['products_shipping_time_day'] );
                 }
                 
                 unset($infs);
                 
            }
            
            $GLOBALS['db']->close_query($sqls);  
            unset($zapytanie);
            
            $GLOBALS['cache']->zapisz('ProduktCzasWysylki_' . $_SESSION['domyslnyJezyk']['kod'], $TablicaCzasWysylki, CACHE_INNE);
            
          } else {

            $TablicaCzasWysylki = $WynikCache;     
            
         }

         if ( isset( $TablicaCzasWysylki[$this->infoSql['products_shipping_time_id']] ) ) {
             //
             $NazwaCzasuWysylki = $TablicaCzasWysylki[$this->infoSql['products_shipping_time_id']]['nazwa'];             
             //
         }
    }    
    
    //
    $MinIlosc = (( $this->info['jednostka_miary_typ'] == '0' ) ? $MinIlosc : (int)$MinIlosc);
    $MaxIlosc = (( $this->info['jednostka_miary_typ'] == '0' ) ? $MaxIlosc : (int)$MaxIlosc);
    $IloscZakupu = (( $this->info['jednostka_miary_typ'] == '0' ) ? number_format($IloscZakupu, 2, '.', '') : (int)$IloscZakupu);
    //

    // inne opcje jezeli produkt ma byc tylko za PUNKTY
    if ( SYSTEM_PUNKTOW_STATUS == 'tak' && SYSTEM_PUNKTOW_STATUS_KUPOWANIA == 'tak' && Punkty::PunktyAktywneDlaKlienta() ) {
         //
         if ( $this->infoSql['products_points_only'] == 1 && ((!isset($_SESSION['customer_id']) || (int)$_SESSION['customer_id'] == 0) || $_SESSION['gosc'] == '1') ) {
            //
            $PozwolNaZakup = false;
            $PokazKoszyk = false;
            //
         }
         //
    }
    
    // czy produkt jest dodany do koszyka
    $CssDodanyKoszyk = '';
    if ( isset($GLOBALS['koszykKlienta']) && $GLOBALS['koszykKlienta']->KoszykIloscProduktow() > 0 ) {
         //
         foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
            //
            if ( isset($TablicaZawartosci['id']) ) {
                 //
                 if ( $this->id_produktu == Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ) ) {
                      //
                      $CssDodanyKoszyk = ' DoKoszykaDodany';
                      //
                 }
                 //
            }
            //
         }
         //
    }

    $this->zakupy = array('mozliwe_kupowanie'   => (( $PozwolNaZakup == true ) ? 'tak' : 'nie' ),
                          'pokaz_koszyk'        => (( $PokazKoszyk == true  ) ? 'tak' : 'nie' ),
                          'minimalna_ilosc'     => $MinIlosc,
                          'maksymalna_ilosc'    => $MaxIlosc,
                          'przyrost_ilosci'     => $PrzyrostIlosci,    
                          'domyslna_ilosc'      => $IloscZakupu,
                          'input_ilosci'        => '<input type="number" id="ilosc_' . $this->idUnikat . $this->id_produktu . '" value="' . $IloscZakupu . '" class="InputIlosc" lang="en_EN" pattern="[0-9]+([\.][0-9]+)?" step="' . ( $this->info['przyrost'] > '0' ? $this->info['przyrost'] : '1' ) . '" min="' . ( $MinIlosc > 0 ? $MinIlosc : '1' ). '" onchange="SprIlosc(this,' . $MinIlosc . ',' . $this->info['jednostka_miary_typ'] . ',\'' . $this->idUnikat . $this->id_produktu . '\',\'' . $this->info['przyrost'] . '\')" name="ilosc" aria-label="' . $GLOBALS['tlumacz']['ILOSC_PRODUKTOW'] . '" />',
                          'jednostka_miary'     => $this->info['jednostka_miary'],
                          'ilosc_magazyn'       => $this->infoSql['products_quantity'],
                          'ilosc_magazyn_jm'    => (( $this->info['jednostka_miary_typ'] == '0' ) ? $this->infoSql['products_quantity'] : (int)$this->infoSql['products_quantity']) . ' ' . $this->info['jednostka_miary'],
                          'nr_kat_cechy'        => $this->infoSql['products_model'],
                          'nr_ean_cechy'        => $this->infoSql['products_ean'],
                          'id_dostep_cechy'     => $this->infoSql['products_availability_id'],    
                          'nazwa_dostepnosci'   => $NazwaDostepnosci,
                          'id_czasu_wys_cechy'  => $this->infoSql['products_shipping_time_id'],    
                          'nazwa_czasu_wysylki' => $NazwaCzasuWysylki,                          
                          'przycisk_kup'        => '<span class="' . $this->cssKoszyka . $CssDodanyKoszyk . ' ToolTip" role="button" tabindex="0" onclick="return DoKoszyka(\'' . $this->idUnikat . $this->id_produktu . '\',\'dodaj\',' . $this->cechyIlosc . ',1)" aria-label="' . (($CssDodanyKoszyk == '') ? $GLOBALS['tlumacz']['LISTING_DODAJ_DO_KOSZYKA'] . ' ' . str_replace('"', '', (string)$this->info['nazwa']) : $GLOBALS['tlumacz']['LISTING_PRODUKT_DODANY_DO_KOSZYKA']) . '" title="' . (($CssDodanyKoszyk == '') ? $GLOBALS['tlumacz']['LISTING_DODAJ_DO_KOSZYKA'] . ' ' . str_replace('"', '', (string)$this->info['nazwa']) : $GLOBALS['tlumacz']['LISTING_PRODUKT_DODANY_DO_KOSZYKA']) . '">' . $this->cssKoszykaTekst . '</span>',
                          'przycisk_szczegoly'  => '<a class="DoKoszykaLink ToolTip" href="' . Seo::link_SEO( ((trim((string)$this->info['nazwa_seo']) != '') ? $this->info['nazwa_seo'] : $this->info['nazwa']), $this->id_produktu, 'produkt' ) . '" aria-label="' . (($CssDodanyKoszyk == '') ? $GLOBALS['tlumacz']['PRODUKT_WYBIERZ_OPCJE'] . ' ' . str_replace('"', '', (string)$this->info['nazwa']) : $GLOBALS['tlumacz']['LISTING_PRODUKT_DODANY_DO_KOSZYKA']) . '" title="' . (($CssDodanyKoszyk == '') ? $GLOBALS['tlumacz']['PRODUKT_WYBIERZ_OPCJE'] . ' ' . str_replace('"', '', (string)$this->info['nazwa']) : $GLOBALS['tlumacz']['LISTING_PRODUKT_DODANY_DO_KOSZYKA']) . '"><span class="' . $this->cssKoszyka . ' Wybor' . $CssDodanyKoszyk . '">' . $GLOBALS['tlumacz']['PRODUKT_WYBIERZ_OPCJE'] . '</span></a>',
                          'przycisk_kup_karta'  => '<span class="' . $this->cssKoszyka . $CssDodanyKoszyk . '" role="button" tabindex="0" onclick="return DoKoszyka(\'' . $this->idUnikat . $this->id_produktu . '\',\'dodaj\',0,0)" aria-label="' . (($CssDodanyKoszyk == '') ? $GLOBALS['tlumacz']['LISTING_DODAJ_DO_KOSZYKA'] . ' ' . str_replace('"', '', (string)$this->info['nazwa']) : $GLOBALS['tlumacz']['LISTING_PRODUKT_DODANY_DO_KOSZYKA']) . '" title="' . (($CssDodanyKoszyk == '') ? $GLOBALS['tlumacz']['LISTING_DODAJ_DO_KOSZYKA'] . ' ' . str_replace('"', '', (string)$this->info['nazwa']) : $GLOBALS['tlumacz']['LISTING_PRODUKT_DODANY_DO_KOSZYKA']) . '">' . $this->cssKoszykaTekst . '</span>',
                          'id_unikat'           => $this->idUnikat,
                          'ilosc_gratisu'       => $IloscZakupu,
                          'input_ilosci_gratis' => '<input type="hidden" id="ilosc_' . $this->idUnikat . $this->id_produktu . '" value="' . $IloscZakupu . '" name="ilosc" />',
                          'przycisk_kup_gratis' => '<span class="' . $this->cssKoszyka . ' ToolTip" role="button" tabindex="0" onclick="return DoKoszyka(\'' . $this->idUnikat . $this->id_produktu . '\',\'gratis\',' . $this->cechyIlosc . ',0)" aria-label="' . $GLOBALS['tlumacz']['LISTING_DODAJ_DO_KOSZYKA'] . ' ' . str_replace('"', '', (string)$this->info['nazwa']) . '" title="' . $GLOBALS['tlumacz']['LISTING_DODAJ_DO_KOSZYKA'] . ' ' . str_replace('"', '', (string)$this->info['nazwa']) . '">' . $this->cssKoszykaTekst . '</span>',
                          'ma_cechy'            => ( $this->cechyIlosc > 0 ? '1' : '0' ),
                          'ma_pola_tekstowe'    => ( is_array($this->dodatkowePolaTekstowe) && count($this->dodatkowePolaTekstowe) > 0 ? '1' : '0' ),
                          'info_cena_30_dni'    => $NajnizszaCenaKomunikat
    );                                               
    //
    
    unset($NazwaDostepnosci, $PozwolNaZakup, $IloscZakupu, $MinIlosc, $MaxIlosc, $PrzyrostIlosci, $nrKatCechy, $idDostepnosciCechy, $NajnizszaCenaKomunikat, $CssDodanyKoszyk);

}
       
?>