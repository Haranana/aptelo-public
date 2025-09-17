<?php

if ( isset($pobierzFunkcje) ) {

    // generuje tablice globalne z nazwami cech
    Funkcje::TabliceCech();       

    $Wynik = '';
    
    if ( (isset($GLOBALS['NazwyCech']) && count($GLOBALS['NazwyCech'])) && (isset($GLOBALS['WartosciCech']) && count($GLOBALS['WartosciCech'])) ) {

        // szuka cech produktu        
        $zapytanieCechy = "SELECT DISTINCT pa.options_id 
                                      FROM products_attributes pa, products_options po 
                                     WHERE pa.products_id = '" . $this->id_produktu . "' AND
                                           pa.options_id = po.products_options_id
                                  ORDER BY po.products_options_sort_order";
                                        
        $sql = $GLOBALS['db']->open_query($zapytanieCechy);
        
        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        
            $TablicaStock = array();
            if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && CECHY_MAGAZYN == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && $this->infoSql['products_control_storage'] > 0 && KARTA_PRODUKTU_CECHY_STAN_ZERO == 'tak' ) {
                $ZapytanieStock = "SELECT products_stock_id, products_stock_attributes, products_stock_quantity FROM products_stock WHERE products_id = '".$this->id_produktu."' AND products_stock_quantity > 0";
                $sqlStock = $GLOBALS['db']->open_query($ZapytanieStock);
                if ((int)$GLOBALS['db']->ile_rekordow($sqlStock) > 0) {
                    while ($stock = $sqlStock->fetch_assoc()) {
                        $TablicaStock[] = $stock['products_stock_attributes'];
                    }
                }
                $GLOBALS['db']->close_query($sqlStock); 
                unset($ZapytanieStock);
            }

            $Wynik = '';

            while ($cecha = $sql->fetch_assoc()) {
            
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
                
                while ($wartosc = $sqlWartosc->fetch_assoc()) {
                  
                    $PokazCechy = false;
                    $CechaStock = $cecha['options_id'] . '-'. $wartosc['options_values_id'];

                    if ( count($TablicaStock) > 0 ) {
                        foreach ($TablicaStock as $key => $value ) {
                            if (strpos((string)$value, (string)$CechaStock) !== false) {
                                $PokazCechy = true;
                            }
                        }
                    } else {
                        $PokazCechy = true;
                    }

                    if ( $PokazCechy ) {
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
                            
                            $CiagTekstu = $GLOBALS['WartosciCech'][$wartosc['options_values_id']]['nazwa'] . ' ';
                            
                            if ( $this->info['typ_cech'] == 'cechy' ) {
                              
                                if ( KARTA_PRODUKTU_CECHY_WARTOSC == 'tak' ) {
                            
                                    if ( $wartosc['options_values_price_tax'] > 0 ) {
                                        //
                                        $CiagTekstu .= '(';
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
                                                 $CiagTekstu .= $Prefix . ' ' . $TablicaCenyProduktu['brutto'] . ' ';
                                              } else {
                                                 $CiagTekstu .= $Prefix . ' ' . $wartosc['options_values_price_tax'];
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
                                            $CiagTekstu .= $Prefix . ' ' . $wartosc['options_values_price_tax'];
                                            //
                                            if ( $wartosc['price_prefix'] != '*' ) {
                                                 $CiagTekstu .= '% ';
                                            }
                                            //
                                            unset($Prefix);
                                            //
                                        }
                                        //
                                        $CiagTekstu .= ')';
                                        //
                                    }  
                                    
                                }

                            }

                            $CiagTekstu .= ', ';

                            $TablicaDoWyboru[] = array('text' => $CiagTekstu);

                            unset($CiagTekstu);
                            
                        }
                    }
                    
                }
                
                $GLOBALS['db']->close_query($sqlWartosc);                     
                unset($zapytanieWartosci);

                if ( count($TablicaDoWyboru) > 0 ) {
                
                    $Wynik .= '<b>' . $GLOBALS['NazwyCech'][$cecha['options_id']]['nazwa'] . '</b>: ';

                    $SameWartosci = '';
                    
                    foreach ($TablicaDoWyboru As $Wartosc) {
                    
                        $SameWartosci .= $Wartosc['text'];

                    }
                    
                    $Wynik .= substr((string)$SameWartosci, 0, -2);
                    unset($SameWartosci);
                    
                    $Wynik .= '<br />';
                
                }
                
                unset($TablicaDoWyboru);

            }

        }
        
        $GLOBALS['db']->close_query($sql); 
        
        unset($zapytanieCechy);
        
        if ( strpos((string)$Wynik, '<br') === false ) {
            $Wynik = '';
        }
        
    }

}

?>