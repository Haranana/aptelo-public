<?php

if ( isset($pobierzFunkcje) ) {

    // generuje tablice globalne z nazwami cech
    Funkcje::TabliceCech();       

    $Wynik = '';
    $CiagJsKombinacjeCech = '';
    $CiagJs = '';
    $NowySzablon = Wyglad::TypSzablonu();
    
    if ( (isset($GLOBALS['NazwyCech']) && count($GLOBALS['NazwyCech'])) && (isset($GLOBALS['WartosciCech']) && count($GLOBALS['WartosciCech'])) ) {
    
        $TablicaCechyProduktu = array();
        
        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('ProduktCechy_' . $this->id_produktu, CACHE_INNE);      

        if ( !$WynikCache && !is_array($WynikCache) ) { 
         
            // szuka cech produktu        
            $zapytanieCechy = "SELECT DISTINCT pa.options_id 
                                          FROM products_attributes pa, products_options po 
                                         WHERE pa.products_id = '" . $this->id_produktu . "' AND
                                               pa.options_id = po.products_options_id
                                      ORDER BY po.products_options_sort_order";
                                            
            $sqls = $GLOBALS['db']->open_query($zapytanieCechy);
            
            if ((int)$GLOBALS['db']->ile_rekordow($sqls) > 0) {
            
                while ($infs = $sqls->fetch_assoc()) {
                     //
                     $TablicaCechyProduktu[] = $infs;
                     //
                }
                
                unset($infs);
                
            }
            
            $GLOBALS['db']->close_query($sqls);  
            unset($zapytanieCechy);            
            
            $GLOBALS['cache']->zapisz('ProduktCechy_' . $this->id_produktu, $TablicaCechyProduktu, CACHE_INNE);
            
         } else {

            $TablicaCechyProduktu = $WynikCache;     
          
        }            
        
        $IleCech = count($TablicaCechyProduktu);
        
        if ($IleCech > 0) {

            $Wynik = ( $NowySzablon ? '<div>' : '<table>');    
            
            // jezeli jest kontrola magazynowa cech to utworzy tablice zeby sprawdzac czy cecha jest w stock zeby nie wyswietlac cech ktorych nie ma w magazynie
            $TablicaStock = array();
            $TablicaStockPelna = array();
            
            if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && CECHY_MAGAZYN == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && $this->infoSql['products_control_storage'] > 0 && KARTA_PRODUKTU_CECHY_STAN_ZERO == 'tak' ) {
              
                $TablicaCechyStockProduktu = array();

                // cache zapytania
                $WynikCache = $GLOBALS['cache']->odczytaj('ProduktCechyStock_' . $this->id_produktu, CACHE_INNE);      

                if ( !$WynikCache && !is_array($WynikCache) ) { 
                 
                    // szuka cech produktu        
                    $zapytanieStock = "SELECT DISTINCT products_stock_attributes, products_stock_quantity, products_stock_availability_id FROM products_stock WHERE products_stock_quantity > 0 and products_id = '" . $this->id_produktu . "'";    
                                                    
                    $sqls = $GLOBALS['db']->open_query($zapytanieStock);

                    if ((int)$GLOBALS['db']->ile_rekordow($sqls) > 0) {

                        while ($infs = $sqls->fetch_assoc()) {
                             //
                             $TablicaCechyStockProduktu[] = $infs;
                             //
                        }
                        
                        unset($infs);

                    }
                    $GLOBALS['db']->close_query($sqls);  
                    unset($zapytanieCechy);            
                    
                    $GLOBALS['cache']->zapisz('ProduktCechyStock_' . $this->id_produktu, $TablicaCechyStockProduktu, CACHE_INNE);
                    
                 } else {

                    $TablicaCechyStockProduktu = $WynikCache;     
                  
                }                        
                foreach ( $TablicaCechyStockProduktu as $stock ) {
                    //
                    // jezeli kombinacja ma dostepnosc trzeba sprawdzic czy mozna kupowac
                    $MoznaKupicCechy = 'tak';
                    //
                    if ( $stock['products_stock_availability_id'] > 0 ) {
                         //
                         if ( $stock['products_stock_availability_id'] == '99999' ) {
                              //
                              $TmpDostepnosc = $this->PokazIdDostepnosciAutomatycznych( $stock['products_stock_availability_id'] );
                              if ( $TmpDostepnosc  != '0' ) {
                                   //
                                   $MoznaKupicCechy = $GLOBALS['dostepnosci'][ $TmpDostepnosc  ]['kupowanie'];              
                                   //                     
                              }
                              unset($TmpDostepnosc);
                              //
                           } else {
                              //
                              $MoznaKupicCechy = 'nie';
                              //
                              if ( isset($GLOBALS['dostepnosci'][ $stock['products_stock_availability_id'] ]) ) {
                                 //
                                 $MoznaKupicCechy = $GLOBALS['dostepnosci'][ $stock['products_stock_availability_id'] ]['kupowanie'];
                                 //               
                              }
                              //
                         }                           
                         //
                    }
                    //
                    if ( $MoznaKupicCechy == 'tak' ) {
                         //
                         $TablicaStockPelna[] = $stock['products_stock_attributes'];
                         //
                         $podzielTb = explode(',', (string)$stock['products_stock_attributes']);
                         foreach ($podzielTb as $podzial) {
                             $TablicaStock[] = $podzial;
                         }
                         //
                    }
                    //
                }

            }
            
            if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && CECHY_MAGAZYN == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && $this->infoSql['products_control_storage'] > 0 && KARTA_PRODUKTU_CECHY_STAN_ZERO == 'nie' ) {
              
                $CiagDoTablic = array();
                $KombinacjeCech = array();
                
                $zapytanieCechy = "select options_id, options_values_id from products_attributes where products_id = '" .  $this->id_produktu . "'";
                $sqlCechy = $GLOBALS['db']->open_query($zapytanieCechy);  

                if ((int)$GLOBALS['db']->ile_rekordow($sqlCechy) > 0) {
                  
                    while ($infc = $sqlCechy->fetch_assoc()) {
                        //
                        $CiagDoTablic[ $infc['options_id'] ][] = $infc['options_values_id'];
                        //
                    }
                    
                    unset($infc);
                    
                }

                $GLOBALS['db']->close_query($sqlCechy);
                unset($zapytanieCechy);

                if ( count($CiagDoTablic) > 1 ) {

                    $tab = Funkcje::Permutations($CiagDoTablic);

                    foreach ($tab as $tablica) {
                        $ciag = '';
                        ksort($tablica);
                        $r = 0;
                        //
                        foreach ( $tablica as $klucz => $wartosc ) {
                            //
                            $ciag .= $klucz . '-' . $wartosc . ',';
                            $TablicaStock[] = $klucz . '-' . $wartosc;
                            $r++;
                            if ( $r == count($CiagDoTablic) ) {
                                 //
                                 $KombinacjeCech[] = substr((string)$ciag,0,-1);
                                 $ciag = '';
                                 $r = 0;
                                 //
                            }
                            //
                        }
                    }  

                } else if ( count($CiagDoTablic) == 1 ) {
                    
                    foreach ( $CiagDoTablic as $key => $wart ) {
                        //
                        for ( $d = 0; $d < count($wart); $d++) {
                              //
                              $KombinacjeCech[] = $key . '-' . $wart[$d];
                              $TablicaStock[] = $key . '-' . $wart[$d];
                              //
                        }
                        //
                    }

                }              
              
                $TablicaStockPelna = $KombinacjeCech;
                
                unset($CiagDoTablic);

            }
            
            $KolejnyNr = 1;
        
            foreach ( $TablicaCechyProduktu as $cecha ) {

                $Wynik .= ( $NowySzablon ? '<div class="CechaProduktu CechaGrupa-' . $cecha['options_id'] . '">' : '' );
                // szuka wartosci dla cechy

/*
                $zapytanieWartosci = "SELECT * FROM products_attributes pa, products_options_values_to_products_options pop 
                                              WHERE pa.products_id = '" . $this->id_produktu . "' and pa.options_id = '" . $cecha['options_id'] . "' AND
                                                    pa.options_id = pop.products_options_id AND
                                                    pa.options_values_id = pop.products_options_values_id
                                           ORDER BY pop.products_options_values_sort_order";
*/
                $zapytanieWartosci = "SELECT * FROM products_attributes pa
                                          LEFT JOIN products_options_values_to_products_options pop ON pa.options_id = pop.products_options_id AND pa.options_values_id = pop.products_options_values_id
                                          LEFT JOIN products_options_values pov ON pop.products_options_values_id = pov.products_options_values_id AND pov.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                                      WHERE pa.products_id = '" . (int)$this->id_produktu . "' and pa.options_id = '" . $cecha['options_id'] . "' 
                                      ORDER BY pop.products_options_values_sort_order, pov.products_options_values_name COLLATE utf8_polish_ci";

                $sqlWartosc = $GLOBALS['db']->open_query($zapytanieWartosci);
                
                $TablicaDoWyboru = array();
                
                if ( KARTA_PRODUKTU_CECHY_WYBOR == 'tak' ) {
                     $TablicaDoWyboru[] = array('id' => '', 'text' => $GLOBALS['tlumacz']['LISTING_WYBIERZ_OPCJE'], 'id_wartosci' => 0, 'domyslna' => 0);
                }
                
                if ((int)$GLOBALS['db']->ile_rekordow($sqlWartosc) > 0) {
                
                    while ($wartosc = $sqlWartosc->fetch_assoc()) {
                      
                        if ( isset($GLOBALS['WartosciCech'][$wartosc['options_values_id']]) && $GLOBALS['WartosciCech'][$wartosc['options_values_id']]['wartosc'] > 0 && $wartosc['options_values_price'] == 0 ) {
                             //
                             $cecha_brutto = $GLOBALS['WartosciCech'][$wartosc['options_values_id']]['wartosc'];
                             $cecha_netto = round(($cecha_brutto / (1 + ($this->vat_podstawa/100))), 2);
                             $cecha_vat = $cecha_brutto - $cecha_netto;
                             //
                             $wartosc['options_values_price'] = $cecha_netto;
                             $wartosc['options_values_tax'] = $cecha_vat;
                             $wartosc['options_values_price_tax'] = $cecha_brutto;
                             //
                             unset($cecha_brutto, $cecha_netto, $cecha_vat);
                             //
                             $wartosc['price_prefix'] = $GLOBALS['WartosciCech'][$wartosc['options_values_id']]['prefix'];
                             //
                        }

                        // jezeli produkt jest za punkty to zeruje wartosci cech
                        if ( $this->infoSql['products_points_only'] == 1 && SYSTEM_PUNKTOW_STATUS == 'tak' && SYSTEM_PUNKTOW_STATUS_KUPOWANIA == 'tak' && Punkty::PunktyAktywneDlaKlienta() ) {
                             //
                             $wartosc['options_values_price'] = 0;
                             $wartosc['options_values_tax'] = 0;
                             $wartosc['options_values_price_tax'] = 0;
                             //
                        }
                        
                        // jezeli produkt nie ma ceny zeruje wartosci cech
                        if ( $this->info['jest_cena'] == 'nie' ) {
                             //
                             $wartosc['options_values_price'] = 0;
                             $wartosc['options_values_tax'] = 0;
                             $wartosc['options_values_price_tax'] = 0;
                             //                      
                        }
                                            
                        // sprawdza status wartosci cechy
                        if ( isset($GLOBALS['WartosciCech'][$wartosc['options_values_id']]) && $GLOBALS['WartosciCech'][$wartosc['options_values_id']]['status'] == 'tak' ) {
                    
                            // ceny netto
                            if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' ) {
                                 //
                                 if ( $GLOBALS['NazwyCech'][$cecha['options_id']]['rodzaj'] == 'kwota') {
                                      //
                                      $wartosc['options_values_price_tax'] = $wartosc['options_values_price'];
                                      //
                                 }
                                 //
                            }                  
                                            
                            $CiagDoId = '';
                            
                            // jezeli produkt ma cechy ktore wplywaja na wartosc
                            if ( $this->info['typ_cech'] == 'cechy' ) {
                            
                                // wartosc 
                                if ($GLOBALS['NazwyCech'][$cecha['options_id']]['rodzaj'] == 'kwota') {
                                    //
                                    $WspolczynnikRabatu = 1;
                                    if ( $this->info['rabat_produktu'] > 0 && RABATY_CECHY == 'tak' ) {
                                         $WspolczynnikRabatu = (100 - $this->info['rabat_produktu']) / 100;
                                    }
                                    // dodawanie rabatu do cech
                                    if ( $wartosc['price_prefix'] != '*' ) {
                                         //
                                         $wartosc['options_values_price_tax'] = round(($wartosc['options_values_price_tax'] * $WspolczynnikRabatu), 2);
                                         $wartosc['options_values_price'] = round(($wartosc['options_values_price'] * $WspolczynnikRabatu), 2);
                                         //
                                    }
                                    //
                                    if ( $wartosc['price_prefix'] != '*' ) {
                                         //
                                         $TablicaCenyProduktu = $GLOBALS['waluty']->FormatujCene( $wartosc['options_values_price_tax'], $wartosc['options_values_price'], 0, $this->infoSql['products_currencies_id'], false );
                                         $CiagDoId .= $TablicaCenyProduktu['netto'] . ',' . $TablicaCenyProduktu['brutto'] . ',';
                                         unset($TablicaCenyProduktu);
                                         //
                                    } else {
                                         //
                                         $CiagDoId .= $wartosc['options_values_price'] . ',' . $wartosc['options_values_price_tax'] . ',';
                                         //
                                    }
                                    //
                                    unset($WspolczynnikRabatu);
                                    //
                                  } else {
                                    //
                                    $CiagDoId .= '0,' . $wartosc['options_values_price_tax'] . ',';
                                    //
                                }
                                
                                // prefix
                                $Prefix = '+';
                                if ( $wartosc['price_prefix'] == '-' ) { $Prefix = '-'; }
                                if ( $wartosc['price_prefix'] == '*' ) { $Prefix = '*'; }
                                //            
                                $CiagDoId .= $Prefix . ',';
                                // rodzaj - procent czy kwota
                                $CiagDoId .= (($GLOBALS['NazwyCech'][$cecha['options_id']]['rodzaj'] == 'kwota') ? '$' : '%') . ',';
                                //
                                unset($Prefix);
                            }
                            
                            // id
                            $CiagDoId .= $wartosc['options_values_id'];
                            
                            $CiagTekstu = '';
                            // nazwa
                            $CiagTekstu .= $GLOBALS['WartosciCech'][$wartosc['options_values_id']]['nazwa'];

                            if ( $this->info['typ_cech'] == 'cechy' ) {
                                                        
                                if ( KARTA_PRODUKTU_CECHY_WARTOSC == 'tak' ) {
                            
                                    // cena
                                    if ( $wartosc['options_values_price_tax'] > 0 ) {
                                        //
                                        if ($GLOBALS['NazwyCech'][$cecha['options_id']]['rodzaj'] == 'kwota') {
                                            //
                                            $TablicaCenyProduktu = $GLOBALS['waluty']->FormatujCene( $wartosc['options_values_price_tax'], $wartosc['options_values_price'], 0, $this->infoSql['products_currencies_id'], true );
                                            //
                                            $Prefix = '+';
                                            if ( $wartosc['price_prefix'] == '-' ) { $Prefix = '-'; }
                                            if ( $wartosc['price_prefix'] == '*' ) { $Prefix = '*'; }
                                            //
                                            if ( $wartosc['price_prefix'] != '*' ) {
                                                 $CiagTekstu .= ' ' . $Prefix . ' ' . $TablicaCenyProduktu['brutto'] . ' ';
                                              } else {
                                                 $CiagTekstu .= ' ' . $Prefix . ' ' . $wartosc['options_values_price_tax'];
                                            }                                        
                                            //
                                            unset($TablicaCenyProduktu, $Prefix);
                                            //
                                          } else {
                                            //
                                            $Prefix = '+';
                                            if ( $wartosc['price_prefix'] == '-' ) { $Prefix = '-'; }
                                            if ( $wartosc['price_prefix'] == '*' ) { $Prefix = '*'; }
                                            //
                                            $CiagTekstu .= ' ' . $Prefix . ' ' . $wartosc['options_values_price_tax'];
                                            //
                                            if ( $wartosc['price_prefix'] != '*' ) {
                                                 $CiagTekstu .= '% ';
                                            }
                                            //
                                            unset($Prefix);
                                            //
                                        }
                                        //
                                    }
                                    
                                }
                                
                            }
                            
                            if ( in_array( $cecha['options_id'] . '-' . $wartosc['options_values_id'], $TablicaStock ) || MAGAZYN_SPRAWDZ_STANY == 'nie' || ( MAGAZYN_SPRAWDZ_STANY == 'tak' && CECHY_MAGAZYN == 'nie' ) || MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'tak' || $this->infoSql['products_control_storage'] == 0 ) {
                            
                                 $TablicaDoWyboru[] = array('id' => $CiagDoId, 'text' => $CiagTekstu, 'id_wartosci' => $wartosc['options_values_id'], 'domyslna' => $wartosc['options_default']);
                                 
                            }

                            unset($CiagDoId, $CiagTekstu);
                        
                        }
                        
                    }
                    
                }
                
                $GLOBALS['db']->close_query($sqlWartosc);                     
                unset($zapytanieWartosci);                    
                
                $DodLabelStart = '';
                $DodLabelKoniec = '';
                
                if ( $GLOBALS['NazwyCech'][$cecha['options_id']]['typ'] == 'lista' ) {
                     //
                     $DodLabelStart = '<label class="formSpan" for="nr_cechy_' . $KolejnyNr . '">';
                     $DodLabelKoniec = '</label>';
                     //
                }
                
                // jezeli jest opis do cechy
                if ( trim((string)$GLOBALS['NazwyCech'][$cecha['options_id']]['opis']) != '' ) {
                     //
                     if ( $NowySzablon == true ) {
                        $Wynik .= '<strong class="CechaProduktuEtykieta CechaOpis" id="CechaOpis_' . $GLOBALS['NazwyCech'][$cecha['options_id']]['id'] . '">' . $DodLabelStart . $GLOBALS['NazwyCech'][$cecha['options_id']]['nazwa'] . $DodLabelKoniec . '</strong>';
                     } else {
                        $Wynik .= '<tr><td><div class="CechaOpis" id="CechaOpis_' . $GLOBALS['NazwyCech'][$cecha['options_id']]['id'] . '">' . $DodLabelStart . $GLOBALS['NazwyCech'][$cecha['options_id']]['nazwa'] . $DodLabelKoniec . '</label></div></td><td>';
                     }
                     //
                } else {
                     //
                     if ( $NowySzablon == true ) {
                        $Wynik .= '<strong class="CechaProduktuEtykieta">' . $DodLabelStart . $GLOBALS['NazwyCech'][$cecha['options_id']]['nazwa'] . $DodLabelKoniec . '</label></strong>';
                     } else {
                        $Wynik .= '<tr><td>' . $DodLabelStart . $GLOBALS['NazwyCech'][$cecha['options_id']]['nazwa'] . $DodLabelKoniec . '</label></td><td>';
                     }
                     //
                }                    

                if ( (count($TablicaDoWyboru) > 1 && KARTA_PRODUKTU_CECHY_WYBOR == 'tak') || (count($TablicaDoWyboru) > 0 && KARTA_PRODUKTU_CECHY_WYBOR == 'nie') ) {
                    
                    // jezeli jest select
                    if ( $GLOBALS['NazwyCech'][$cecha['options_id']]['typ'] == 'lista' ) {
                        //

                        if ( $NowySzablon == true ) {
                            $Wynik .= '<div class="CechaWyboru">';
                        }

                        $SelectWyboru = '<select class="SelectCechyProduktu" name="cecha_' . $cecha['options_id'] . '" id="nr_cechy_' . $KolejnyNr . '" data-typ="lista" data-id="' . $cecha['options_id'] . '" onchange="ZmienCeche(\'' . $this->idUnikat . $this->id_produktu . '\', this, ' . $KolejnyNr . ')">';
                        //
                        // ktore do zaznaczenia
                        $DomyslnyWybor = 0;
                        //
                        if ( KARTA_PRODUKTU_CECHY_WYBOR != 'tak' ) {
                             //
                             for ($i = 0, $n = count($TablicaDoWyboru); $i < $n; $i++) {
                                 //
                                 if ( (int)$TablicaDoWyboru[$i]['domyslna'] == 1 ) {
                                      $DomyslnyWybor = $i;
                                 }
                                 //
                             }                        
                             //
                        }
                        //
                        for ($i = 0, $n = count($TablicaDoWyboru); $i < $n; $i++) {
                            //
                            if ( $TablicaDoWyboru[$i]['id_wartosci'] > 0 ) {
                                 //
                                 $SelectWyboru .= '<option class="SelectCechyProduktuWartosci" id="id_wartosc_cechy_' . $TablicaDoWyboru[$i]['id_wartosci'] . '" data-id="' . $TablicaDoWyboru[$i]['id_wartosci'] . '" value="' . $TablicaDoWyboru[$i]['id'] . '" ' . (($DomyslnyWybor == $i) ? 'selected="selected"' : '') . '>' . $TablicaDoWyboru[$i]['text'] . '</option>';
                                 //
                            } else {
                                 //
                                 $SelectWyboru .= '<option class="SelectCechyProduktuWartosci SelectCechyDomyslny" id="id_wartosc_cechy_' . $KolejnyNr . '_' . $TablicaDoWyboru[$i]['id_wartosci'] . '" data-id="0" value="">' . $TablicaDoWyboru[$i]['text'] . '</option>';
                                 //
                            }
                            //
                        }
                        //
                        unset($DomyslnyWybor);
                        //
                        $SelectWyboru .= '</select>';
                        //
                        $Wynik .= $SelectWyboru;
                        //
                        if ( $NowySzablon == true ) {
                            $Wynik .= '</div>';
                        }

                        unset($SelectWyboru);
                        //
                    }
                    // jezeli jest radio
                    if ( $GLOBALS['NazwyCech'][$cecha['options_id']]['typ'] == 'radio' ) {
                        //
                        $Wynik .= '<div class="CechaWyboru" id="nr_cechy_' . $KolejnyNr . '" data-typ="pole" data-id="' . $cecha['options_id'] . '">';
                        //
                        // ktore do zaznaczenia
                        $DomyslnyWybor = 0;
                        //
                        for ($i = 0, $n = count($TablicaDoWyboru); $i < $n; $i++) {
                            //
                            if ( (int)$TablicaDoWyboru[$i]['domyslna'] == 1 ) {
                                 $DomyslnyWybor = $i;
                            }
                            //
                        }                        
                        //
                        for ($i = 0, $n = count($TablicaDoWyboru); $i < $n; $i++) {
                            //
                            if ( $TablicaDoWyboru[$i]['id'] != '' ) {
                                 //
                                 $Wynik .= '<div class="Radio PoleWyboruCechy" id="id_wartosc_cechy_' . $TablicaDoWyboru[$i]['id_wartosci'] . '">';
                                 $Wynik .= '   <label for="wartosc_' . $TablicaDoWyboru[$i]['id_wartosci'] . '_'.$cecha['options_id'].'">';
                                 $Wynik .= '      <input aria-label="' . $GLOBALS['NazwyCech'][$cecha['options_id']]['nazwa'] . ': ' . str_replace('"', '', $TablicaDoWyboru[$i]['text']) . '" type="radio" data-id="' . $TablicaDoWyboru[$i]['id_wartosci'] . '" value="' . $TablicaDoWyboru[$i]['id'] . '" onchange="ZmienCeche(\'' . $this->idUnikat . $this->id_produktu . '\', \'\', ' . $KolejnyNr . ')" id="wartosc_' . $TablicaDoWyboru[$i]['id_wartosci'] . '_'.$cecha['options_id'].'" name="cecha_' . $cecha['options_id'] . '" ' . (($DomyslnyWybor == $i) ? 'checked="checked" ' : '') . '/>';
                                 $Wynik .= '      <span id="radio_' . $TablicaDoWyboru[$i]['id_wartosci'] . '">' . $TablicaDoWyboru[$i]['text'] . '</span>';
                                 $Wynik .= '    </label>';
                                 $Wynik .= '</div>';
                                 //
                            }
                            //
                        }
                        //
                        unset($DomyslnyWybor);
                        //
                        if ( KARTA_PRODUKTU_CECHY_WYBOR == 'tak' && KARTA_PRODUKTU_CECHY_STAN_ZERO == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && $this->infoSql['products_control_storage'] > 0 ) {
                             //
                             $Wynik .= '<div class="SelectCechyDomyslny" style="width:100%"><select><option>' . $GLOBALS['tlumacz']['LISTING_WYBIERZ_OPCJE'] . '</option></select></div>';
                             //
                        }
                        //
                        unset($LicznikRadio);
                        //
                        $Wynik .= '</div>';
                        //
                    }                
                    // jezeli sa obrazki
                    if ( $GLOBALS['NazwyCech'][$cecha['options_id']]['typ'] == 'foto' ) {
                        //
                        $Wynik .= '<div class="CechaWyboru" id="nr_cechy_' . $KolejnyNr . '" data-typ="pole" data-id="' . $cecha['options_id'] . '">';
                        //
                        // ktore do zaznaczenia
                        $DomyslnyWybor = 0;
                        //
                        for ($i = 0, $n = count($TablicaDoWyboru); $i < $n; $i++) {
                            //
                            if ( (int)$TablicaDoWyboru[$i]['domyslna'] == 1 ) {
                                 $DomyslnyWybor = $i;
                            }
                            //
                        }                        
                        //
                        for ($i = 0, $n = count($TablicaDoWyboru); $i < $n; $i++) {
                            //
                            if ( $TablicaDoWyboru[$i]['id'] != '' ) {
                                //
                                $Wynik .= '<div class="Foto PoleWyboruCechy" id="id_wartosc_cechy_' . $TablicaDoWyboru[$i]['id_wartosci'] . '">';
                                //
                                // ustala sam nr id wartosci cechy
                                if ( $this->info['typ_cech'] == 'cechy' ) { 
                                     //
                                     // jezeli sa cechy to musi podzielic ciag na tablice
                                     $SamoIdTb = explode(',', (string)$TablicaDoWyboru[$i]['id']);
                                     $SamoId = $SamoIdTb[4];
                                     unset($SamoIdTb);
                                     //
                                } else {
                                     //
                                     // jezeli jest ceny dla kombinacji to mozna bezposrednio pobrac id
                                     $SamoId = $TablicaDoWyboru[$i]['id'];
                                     //
                                }
                                //

                                if ( $NowySzablon == true ) {
                                  
                                    $Wynik .= '<label for="wartosc_'.$cecha['options_id'].'_'.$TablicaDoWyboru[$i]['id_wartosci'].'">';
                                    $Wynik .= '<input aria-label="' . str_replace('"', '', $TablicaDoWyboru[$i]['text']) . '" type="radio" data-id="' . $TablicaDoWyboru[$i]['id_wartosci'] . '" value="' . $TablicaDoWyboru[$i]['id'] . '" onchange="ZmienCeche(\'' . $this->idUnikat . $this->id_produktu . '\', \'\', ' . $KolejnyNr . ')" id="wartosc_'.$cecha['options_id'].'_'.$TablicaDoWyboru[$i]['id_wartosci'].'" name="cecha_' . $cecha['options_id'] . '" ' . (($DomyslnyWybor == $i) ? 'checked="checked" ' : '') . '/>';
                                    $Wynik .= '<span class="PoleCechy">';
                                    
                                    $PlikFoto = '';

                                    if (!empty($GLOBALS['WartosciCech'][$SamoId]['foto'])) {
                                        //
                                        // jezeli jest wlaczona mozliwosc powiekszenia obrazka cechy
                                        if ( KARTA_PRODUKTU_CECHY_OBRAZ_POWIEKSZENIE == 'tak' ) {
                                             //
                                             if ( TEKST_COPYRIGHT_POKAZ == 'tak' || OBRAZ_COPYRIGHT_POKAZ == 'tak' ) {
                                                $ZdjecieCechy = Funkcje::pokazObrazekWatermark($GLOBALS['WartosciCech'][$SamoId]['foto']);
                                             } else {
                                                $ZdjecieCechy = KATALOG_ZDJEC . '/' . $GLOBALS['WartosciCech'][$SamoId]['foto'];
                                             }                                         
                                             //
                                             $Wynik .= '<span class="ZdjecieCechy"><a href="'.$ZdjecieCechy.'" data-jbox-image="galeria_cech" title="'.$TablicaDoWyboru[$i]['text'].'">' . Funkcje::pokazObrazek($GLOBALS['WartosciCech'][$SamoId]['foto'], $TablicaDoWyboru[$i]['text'], SZEROKOSC_CECH, WYSOKOSC_CECH, array(), ' title="' . $TablicaDoWyboru[$i]['text'] . '"', 'maly') . '</a></span>';
                                             //
                                             $PlikFoto = $ZdjecieCechy;
                                             //
                                             unset($ZdjecieCechy);
                                             //
                                        } else {  
                                             //
                                             $Wynik .= '<span class="ZdjecieCechy">' . Funkcje::pokazObrazek($GLOBALS['WartosciCech'][$SamoId]['foto'], $TablicaDoWyboru[$i]['text'], SZEROKOSC_CECH, WYSOKOSC_CECH, array(), ' title="' . $TablicaDoWyboru[$i]['text'] . '" ', 'maly') . '</span>';
                                             //
                                             if ( TEKST_COPYRIGHT_POKAZ == 'tak' || OBRAZ_COPYRIGHT_POKAZ == 'tak' ) {
                                                $PlikFoto = Funkcje::pokazObrazekWatermark($GLOBALS['WartosciCech'][$SamoId]['foto']);
                                             } else {
                                                $PlikFoto = KATALOG_ZDJEC . '/' . $GLOBALS['WartosciCech'][$SamoId]['foto'];
                                             }                                         
                                             //                                            
                                        }
                                        //
                                    } else {
                                        //
                                        $Wynik .= '<span class="ZdjecieCechy">' . Funkcje::pokazObrazek(KATALOG_ZDJEC . '/domyslny.webp', $TablicaDoWyboru[$i]['text'], SZEROKOSC_CECH, WYSOKOSC_CECH, array(), ' title="' . $TablicaDoWyboru[$i]['text'] . '" ', 'maly') . '</span>';
                                        //
                                        $PlikFoto = KATALOG_ZDJEC . '/domyslny.webp';
                                        //                                         
                                    }
                                    //
                                    
                                    // data-src do zdjecia
                                    $Wynik = str_replace('<span class="PoleCechy">', '<span class="PoleCechy" data-title="' . str_replace('"', '', (string)$TablicaDoWyboru[$i]['text']) . '" data-src="' . str_replace('"', '', (string)$PlikFoto) . '">', (string)$Wynik);
                                    
                                    $Wynik .= '<span class="radio" id="radio_'.$cecha['options_id'].'_'.$TablicaDoWyboru[$i]['id_wartosci'].'">' . $TablicaDoWyboru[$i]['text'] . '</span>';
                                    
                                    $Wynik .= '</span></label>';
                                    
                                    unset($PlikFoto);

                                } else {
                                  
                                    $WynikTmp = '';

                                    if (!empty($GLOBALS['WartosciCech'][$SamoId]['foto'])) {
                                        //
                                        // jezeli jest wlaczona mozliwosc powiekszenia obrazka cechy
                                        if ( KARTA_PRODUKTU_CECHY_OBRAZ_POWIEKSZENIE == 'tak' && $_SESSION['mobile'] != 'tak' ) {
                                             //
                                             if ( TEKST_COPYRIGHT_POKAZ == 'tak' || OBRAZ_COPYRIGHT_POKAZ == 'tak' ) {
                                                $ZdjecieCechy = Funkcje::pokazObrazekWatermark($GLOBALS['WartosciCech'][$SamoId]['foto']);
                                             } else {
                                                $ZdjecieCechy = KATALOG_ZDJEC . '/' . $GLOBALS['WartosciCech'][$SamoId]['foto'];
                                             }                                         
                                             //
                                             $WynikTmp .= '<div class="PoleZdjecieCechy"><a class="ZdjecieCechy" title="' . $TablicaDoWyboru[$i]['text'] . '" href="' . $ZdjecieCechy . '" data-jbox-image="galeria_cech" data-caption="' . $TablicaDoWyboru[$i]['text'] . '">' . Funkcje::pokazObrazek($GLOBALS['WartosciCech'][$SamoId]['foto'], $TablicaDoWyboru[$i]['text'], SZEROKOSC_CECH, WYSOKOSC_CECH, array(), ' title="' . $TablicaDoWyboru[$i]['text'] . '"', 'maly') . '</a></div>';
                                             //
                                             $PlikFoto = $ZdjecieCechy;
                                             //
                                             unset($ZdjecieCechy);
                                             //
                                           } else {
                                             //
                                             $WynikTmp .= '<div class="PoleZdjecieCechy">' . Funkcje::pokazObrazek($GLOBALS['WartosciCech'][$SamoId]['foto'], $TablicaDoWyboru[$i]['text'], SZEROKOSC_CECH, WYSOKOSC_CECH, array(), ' title="' . $TablicaDoWyboru[$i]['text'] . '" ', 'maly') . '</div>';
                                             //
                                             if ( TEKST_COPYRIGHT_POKAZ == 'tak' || OBRAZ_COPYRIGHT_POKAZ == 'tak' ) {
                                                $PlikFoto = Funkcje::pokazObrazekWatermark($GLOBALS['WartosciCech'][$SamoId]['foto']);
                                             } else {
                                                $PlikFoto = KATALOG_ZDJEC . '/' . $GLOBALS['WartosciCech'][$SamoId]['foto'];
                                             }                                         
                                             // 
                                        }
                                        //
                                    }

                                    // data-src do zdjecia
                                    $WynikTmp = str_replace('<div class="PoleZdjecieCechy"', '<div class="PoleZdjecieCechy" data-title="' . str_replace('"', '', (string)$TablicaDoWyboru[$i]['text']) . '" data-src="' . str_replace('"', '', (string)$PlikFoto) . '"', (string)$WynikTmp);
                                                                        
                                    $Wynik .= $WynikTmp . '<div class="PoleRadioCechy"><input type="radio" data-id="' . $TablicaDoWyboru[$i]['id_wartosci'] . '" value="' . $TablicaDoWyboru[$i]['id'] . '" onchange="ZmienCeche(\'' . $this->idUnikat . $this->id_produktu . '\', \'\', ' . $KolejnyNr . ')" name="cecha_' . $cecha['options_id'] . '" ' . (($DomyslnyWybor == $i) ? 'checked="checked" ' : '') . '/> ' . $TablicaDoWyboru[$i]['text'] . '</div>';
                                    
                                    unset($WynikTmp);
                                    //
                                }
                                $Wynik .= '</div>';
                                //
                                unset($SamoId, $PlikFoto);
                                //
                            }
                            //
                        }
                        //
                        unset($DomyslnyWybor);
                        //
                        if ( KARTA_PRODUKTU_CECHY_WYBOR == 'tak' && KARTA_PRODUKTU_CECHY_STAN_ZERO == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && $this->infoSql['products_control_storage'] > 0 ) {
                             //
                             $Wynik .= '<div class="SelectCechyDomyslny" style="width:100%"><select><option>' . $GLOBALS['tlumacz']['LISTING_WYBIERZ_OPCJE'] . '</option></select></div>';
                             //
                        }
                        //
                        unset($LicznikRadio);
                        //
                        $Wynik .= '</div>';
                        //
                    }    

                } else {

                    // jezeli jest select
                    if ( $GLOBALS['NazwyCech'][$cecha['options_id']]['typ'] == 'lista' ) {
                        //
                        if ( $NowySzablon == true ) {
                            $Wynik .= '<div class="CechaWyboru" id="nr_cechy_' . $KolejnyNr . '" data-typ="lista" data-id="' . $cecha['options_id'] . '">';
                        }
                        $Wynik .= Funkcje::RozwijaneMenu('cecha_' . $cecha['options_id'], array( array('id' => '', 'text' => $GLOBALS['tlumacz']['BRAK_WARTOSCI_CECH']) ) );
                        if ( $NowySzablon == true ) {
                            $Wynik .= '</div>';
                        }
                        //
                    }
                    // jezeli jest radio
                    if ( $GLOBALS['NazwyCech'][$cecha['options_id']]['typ'] == 'radio' || $GLOBALS['NazwyCech'][$cecha['options_id']]['typ'] == 'foto' ) {
                        //
                        $Wynik .= '<div class="CechaWyboru" id="nr_cechy_' . $KolejnyNr . '" data-typ="pole" data-id="' . $cecha['options_id'] . '">';

                        $Wynik .= '<div class="Radio PoleWyboruCechy"><label><input type="radio" value="" name="cecha_' . $cecha['options_id'] . '" checked="checked" /> ' . $GLOBALS['tlumacz']['BRAK_WARTOSCI_CECH'] . '<span class="radio" id="radio_' . $cecha['options_id'] . '"></span></label></div>';
                        $Wynik .= '</div>';
                        //
                    }         
                    
                    $Wynik .= ( $NowySzablon ? '' : '</td></tr>' );
                
                }
                
                unset($TablicaDoWyboru);
                
                $KolejnyNr++;

                $Wynik .= ( $NowySzablon ? '</div>' : '' );

            }
            
            unset($TablicaStock);
                     
            $Wynik .= ( $NowySzablon ? '</div>' : '</table>' );

            if ( $NowySzablon == false && strpos((string)$Wynik, '<td>') === false ) {
                $Wynik = '';
            } elseif ( $NowySzablon == true && strpos((string)$Wynik, 'CechaProduktuEtykieta') === false ) {
                $Wynik = '';
            }
            
            $Wynik = '<input type="hidden" value="' . $IleCech . '" id="IleCechProduktu" />' . "\n" . $Wynik;
        
            if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && CECHY_MAGAZYN == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && $this->infoSql['products_control_storage'] > 0 && KARTA_PRODUKTU_CECHY_STAN_ZERO == 'tak' ) {
            
                if ( isset($TablicaStockPelna) ) {
                     //
                     $Wynik = $Wynik . '<input type="hidden" value="nie" id="CechyStanZero" />' . "\n";
                     //
                     $CiagJsKombinacjeCech = '<script>' . "\n";
                     $CiagJsKombinacjeCech .= 'var cechy_kombinacje = [];' . "\n";
                    
                     $nr = 0;
                     foreach ( $TablicaStockPelna as $Tmp ) {
                         //
                         if ( count(explode(',', (string)$Tmp)) == $IleCech ) {
                              //
                              $CiagJsKombinacjeCech .= 'cechy_kombinacje[' . $nr . '] = new Array("' . implode('","', explode(',', (string)$Tmp)) . '");' . "\n";
                              $nr++;
                              //
                         }
                         //
                     }
                     unset($nr);
                     //
                     $CiagJsKombinacjeCech .= '</script>' . "\n";
                     
                }
                
            }
            
        }
        
    }

    // generowanie tablicy javascript w przypadku jezeli produkt z cechami ma osobne ceny
    
    $Wynik = '<input type="hidden" value="' . $this->info['typ_cech'] . '" id="TypCechy" />'  . "\n" . $CiagJsKombinacjeCech . $Wynik;
    
    if ( $this->info['typ_cech'] == 'ceny' ) {
    
        $CiagJs .= '<script>' . "\n";
        
        $CiagJs .= 'var opcje = [];' . "\n";
        
        // jezeli produkt ma cene
        if ( $this->info['jest_cena'] == 'tak' ) { 
       
            if ( $this->CenaIndywidualna == false ) {
            
                $DodatkoweCeny = '';
                if ( (int)ILOSC_CEN > 1 ) {
                    //
                    for ($n = 2; $n <= (int)ILOSC_CEN; $n++) {
                        //
                        $DodatkoweCeny .= 'products_stock_price_' . $n . ', products_stock_price_tax_' . $n . ', products_stock_retail_price_' . $n . ', products_stock_old_price_' . $n . ', ';
                        //
                    }
                    //
                }            
                
                // szuka cech produktu        
                $zapytanieCechy = "SELECT DISTINCT " . $DodatkoweCeny . " products_stock_attributes, products_stock_price, products_stock_price_tax, products_stock_retail_price, products_stock_old_price 
                                              FROM products_stock
                                             WHERE products_id = '" . $this->id_produktu . "'";
                unset($DodatkoweCeny);

                $sql = $GLOBALS['db']->open_query($zapytanieCechy);
                
                $RabatProduktDnia = 1;
                if ( isset($GLOBALS['produkt_dnia'][date('Y-m-d', time())]) ) {
                     //
                     $RabatProduktDnia = (100 - (float)$GLOBALS['produkt_dnia'][date('Y-m-d', time())]['rabat']) / 100;
                     //
                }

                $i = 0;
                
                if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
                  
                    while ($cecha = $sql->fetch_assoc()) {
                     
                        // ustawienia promocji - sprawdzi czy produkt nie jest cena promocyjna z datami - jezeli daty nie lapia sie na aktualny czas to przyjmie cene poprzednia
                        if ( ((FunkcjeWlasnePHP::my_strtotime($this->infoSql['specials_date']) > time() && $this->infoSql['specials_date'] != '0000-00-00 00:00:00') || (FunkcjeWlasnePHP::my_strtotime($this->infoSql['specials_date_end']) < time() && $this->infoSql['specials_date_end'] != '0000-00-00 00:00:00') ) && $cecha['products_stock_old_price'] > 0 ) {
                            //
                            $cecha['products_stock_price_tax'] = $cecha['products_stock_old_price'];
                            // 
                            // obliczanie netto i vatu             
                            $netto = round(($cecha['products_stock_price_tax'] / (1 + ($this->vat_podstawa/100))), 2);
                            $cecha['products_stock_price'] = $netto;
                            $cecha['products_stock_old_price'] = 0;
                            unset($netto);
                            //
                            if ( $_SESSION['poziom_cen'] > 1 ) {
                                 //
                                 if ( $cecha['products_stock_old_price_' . $_SESSION['poziom_cen']] > 0 ) {
                                      //
                                      $cecha['products_stock_price_tax_' . $_SESSION['poziom_cen']] = $cecha['products_stock_old_price_' . $_SESSION['poziom_cen']];
                                      // 
                                      // obliczanie netto i vatu             
                                      $netto = round(($cecha['products_stock_price_tax_' . $_SESSION['poziom_cen']] / (1 + ($this->vat_podstawa/100))), 2);
                                      $cecha['products_stock_price_' . $_SESSION['poziom_cen']] = $netto;
                                      $cecha['products_stock_old_price_' . $_SESSION['poziom_cen']] = 0;
                                      unset($netto);
                                      //
                                 }
                                 //
                            }
                            //
                        }                  
                      
                        // ceny netto
                        if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' ) {
                             //
                             $cecha['products_stock_price_tax'] = $cecha['products_stock_price'];
                             
                             // cena katalogowa
                             if ( $cecha['products_stock_retail_price'] > 0 ) {
                                  $cecha['products_stock_retail_price'] = round(($cecha['products_stock_retail_price'] / (1 + ($this->vat_podstawa/100))), 2);
                             }
                             
                             // cena poprzednia
                             if ( $cecha['products_stock_old_price'] > 0 ) {
                                  $cecha['products_stock_old_price'] = round(($cecha['products_stock_old_price'] / (1 + ($this->vat_podstawa/100))), 2);                         
                             }
                             //
                        }                            
                         
                        // jezeli klient ma inny poziom cen
                        if ( $_SESSION['poziom_cen'] > 1 ) {
                            //
                            // jezeli cena w innym poziomie nie jest pusta
                            if ( $cecha['products_stock_price_' . $_SESSION['poziom_cen']] > 0 ) {
                                //
                                if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' ) {
                                     //
                                     $cecha['products_stock_price_tax'] = $cecha['products_stock_price_' . $_SESSION['poziom_cen']];                           
                                     //
                                  } else {
                                     //
                                     $cecha['products_stock_price_tax'] = $cecha['products_stock_price_tax_' . $_SESSION['poziom_cen']];
                                     //
                                }
                                //                            
                                $cecha['products_stock_price'] = $cecha['products_stock_price_' . $_SESSION['poziom_cen']];
                                //
                            }
                            //
                            
                            // cena katalogowa
                            // if ( $cecha['products_stock_retail_price_' . $_SESSION['poziom_cen']] > 0 ) {
                                 //
                                 if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' ) {
                                      //
                                      $cecha['products_stock_retail_price'] = round(($cecha['products_stock_retail_price_' . $_SESSION['poziom_cen']] / (1 + ($this->vat_podstawa/100))), 2);
                                      //
                                 } else {
                                      //
                                      $cecha['products_stock_retail_price'] = $cecha['products_stock_retail_price_' . $_SESSION['poziom_cen']];
                                      //
                                 }
                                 //
                            // }
                            //
                            
                            // cena poprzednia
                            // if ( $cecha['products_stock_old_price_' . $_SESSION['poziom_cen']] > 0 ) {
                                 //
                                 if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' ) {
                                      //
                                      $cecha['products_stock_old_price'] = round(($cecha['products_stock_old_price_' . $_SESSION['poziom_cen']] / (1 + ($this->vat_podstawa/100))), 2); 
                                      //
                                 } else {
                                      //
                                      $cecha['products_stock_old_price'] = $cecha['products_stock_old_price_' . $_SESSION['poziom_cen']];    
                                      //
                                 }
                                 //
                            // }
                            //                        
                        }                   

                        //
                        if ( $cecha['products_stock_price_tax'] > 0 ) {
                          
                            if ( $this->info['produkt_dnia'] == 'nie' ) {
                              
                                // rabaty klienta od ceny produktu
                                $CenaRabatyCechy = $this->CenaProduktuPoRabatach( $cecha['products_stock_price'], $cecha['products_stock_price_tax'] ); 
                        
                                if ( $CenaRabatyCechy['rabat'] != 0 && RABATY_PROMOCJE == 'nie' && RABATY_PROMOCJE_WYSWIETLAJ == 'tak' && $this->infoSql['specials_status_cechy'] == '1' && $cecha['products_stock_old_price'] > 0 && $this->infoSql['products_set'] == 0 ) {
                                    //
                                    $CenaRabatyCechy = $this->CenaProduktuPoRabatach( round(($cecha['products_stock_old_price'] / (1 + ($this->vat_podstawa/100))), 2), $cecha['products_stock_old_price'] );
                                    //
                                    $cecha['products_stock_price'] = $CenaRabatyCechy['netto'];
                                    $cecha['products_stock_price_tax'] = $CenaRabatyCechy['brutto']; 
                                    //            
                                } else {
                                    //
                                    $cecha['products_stock_price'] = $CenaRabatyCechy['netto'];
                                    $cecha['products_stock_price_tax'] = $CenaRabatyCechy['brutto'];
                                    //
                                }                    
                        
                                $cecha['products_old_price'] = 0;
                                
                            } else {
                              
                                $cecha['products_old_price'] = $cecha['products_stock_price_tax'];
                                $cecha['products_stock_price'] =  $cecha['products_stock_price'] * $RabatProduktDnia;
                                $cecha['products_stock_price_tax'] = $cecha['products_stock_price_tax'] * $RabatProduktDnia;
                              
                            }

                            // ceny bez formatowania - same kwoty po przeliczeniu
                            $TablicaCenyProduktuCechy = $GLOBALS['waluty']->FormatujCene( $cecha['products_stock_price_tax'], $cecha['products_stock_price'], $cecha['products_old_price'], $this->infoSql['products_currencies_id'], false );
                            
                            // cena poprzednia i cena katalogowa
                            $TablicaCenaPoprzedniaKatalogowa = $GLOBALS['waluty']->FormatujCene( $cecha['products_stock_retail_price'], $cecha['products_stock_old_price'], 0, $this->infoSql['products_currencies_id'], false );

                            // cena poprzednia
                            $TmpPoprzednia = 0;
                            if ( $this->ikonki['promocja'] == '1' ) {
                                 //
                                 $TmpPoprzednia = $TablicaCenaPoprzedniaKatalogowa['netto'];
                                 //
                            }
                            if ( $this->ikonki['wyprzedaz'] == '1' && $this->ikonki['promocja'] == '0' ) {
                                 //
                                 $TmpPoprzednia = $TablicaCenaPoprzedniaKatalogowa['netto'];
                                 //
                            }                        
                            
                            $CiagJs .= 'opcje[\'x' . str_replace(',', 'x', (string)$cecha['products_stock_attributes']) . '\'] = \'' . $TablicaCenyProduktuCechy['netto'] . ';' . $TablicaCenyProduktuCechy['brutto'] . ';' . (float)$TablicaCenyProduktuCechy['promocja'] . ';' . $TmpPoprzednia . ';' . $TablicaCenaPoprzedniaKatalogowa['brutto'] . '\';' . "\n";
                            $i++;
                            
                            unset($CenaRabatyCechy, $TablicaCenyProduktuCechy, $TmpPoprzednia);
                            
                        }
                        
                    }
                    
                }
                
                $GLOBALS['db']->close_query($sql); 
                unset($i, $RabatProduktDnia);
                
            }
        
        }
        
        $CiagJs .= '</script>' . "\n";
    
    }
  
}
       
?>