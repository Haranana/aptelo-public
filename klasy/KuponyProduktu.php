<?php

class KuponyProduktu {
  
  // czy sa kupony
  public static function ListaKuponow() {

    $data = date('Y-m-d');

    $zapytanieKupon = "SELECT * FROM coupons
                       WHERE coupons_status = '1' AND 
                       coupons_hidden = 0 AND 
                       coupons_quantity > 0 AND 
                       ((('" . $data . "' >= coupons_date_start AND coupons_date_start != '0000-00-00') OR coupons_date_start = '0000-00-00') AND
                       ((coupons_date_end >= '" . $data . "' AND coupons_date_end != '0000-00-00') OR coupons_date_end = '0000-00-00'))";
                       
    $sqlKupon = $GLOBALS['db']->open_query($zapytanieKupon);  
    
    $wynik = '';
    
    if ( (int)$GLOBALS['db']->ile_rekordow($sqlKupon) > 0 ) {
      
         $wynik = array();
      
         while ( $info = $sqlKupon->fetch_assoc() ) {
           
              $wynik[] = $info;
           
         }
      
    }
    
    $GLOBALS['db']->close_query($sqlKupon);
    unset($data, $zapytanieKupon);

    return $wynik;
  
  }

  // funkcja zwracajaca kupony rabatowe dla danego produktu
  public static function ProduktKupon( $ProduktKupon = array(), $ListaKuponow = '' ) {

    // Kupon rabatowy na karcie produktu - START
    $TablicaKuponow = array();

    if ( is_array($ListaKuponow) ) {

        foreach ( $ListaKuponow as $infKupon ) {
          
            $MaBycKupon = true;
            
            // jezeli jest inna waluta
            if ( $infKupon['coupons_currency'] != '' ) {
                 //
                 if ( $infKupon['coupons_currency'] != $_SESSION['domyslnaWaluta']['kod'] ) {
                      //
                      $MaBycKupon = false;
                      //
                 }
                 //
            }

            if ( $infKupon['coupons_min_order'] != 0 && $ProduktKupon->info['cena_brutto_bez_formatowania'] < $infKupon['coupons_min_order'] ) {
                 //
                 $MaBycKupon = false;
                 //
            }
            
            if ( $infKupon['coupons_max_order'] != 0 && $ProduktKupon->info['cena_brutto_bez_formatowania'] > $infKupon['coupons_max_order'] ) {
                 //
                 $MaBycKupon = false;
                 //
            }

            // jezeli jest dostepny tylko w powiazaniu z wybranymi produktami
            if ( $infKupon['coupons_products_only'] == '1' && $infKupon['coupons_products_only_id'] != '' ) {
                 //
                 $JakieIdPowiazane = explode(',', (string)$infKupon['coupons_products_only_id']);
                 //
                 if ( !in_array($ProduktKupon->info['id'], $JakieIdPowiazane) ) {
                      $MaBycKupon = false;
                 }
                 //
                 unset($JakieIdPowiazane);
                 //
            }

            if ( $MaBycKupon ) {
              
                if ( $infKupon['coupons_exclusion'] == 'producenci' && in_array( $ProduktKupon->info['id_producenta'], explode(',', (string)$infKupon['coupons_exclusion_id']) ) ) {
                  
                    if ( $infKupon['coupons_discount_type'] == 'percent' ) {
                         //
                         $Rabat = round(($ProduktKupon->info['cena_brutto_bez_formatowania'] * ( $infKupon['coupons_discount_value'] / 100 )),2);
                         $CenaPoRabacie = $GLOBALS['waluty']->PokazCeneBezSymbolu(($ProduktKupon->info['cena_brutto_bez_formatowania'] - $Rabat),'',false);
                         //
                    } elseif ( $infKupon['coupons_discount_type'] == 'fixed' ) {
                         //              
                         $Rabat = $GLOBALS['waluty']->PokazCeneBezSymbolu($infKupon['coupons_discount_value'],'',true);
                         //
                         if ( $infKupon['coupons_currency'] != '' ) {
                              //
                              if ( $infKupon['coupons_currency'] == $_SESSION['domyslnaWaluta']['kod'] ) {
                                   $Rabat = $GLOBALS['waluty']->PokazCeneBezSymbolu($infKupon['coupons_discount_value'],'',false);
                              }
                              //
                         }
                         //
                         $infKupon['coupons_discount_value'] = $Rabat;
                         //
                         $CenaPoRabacie = $GLOBALS['waluty']->PokazCeneBezSymbolu(round(($ProduktKupon->info['cena_brutto_bez_formatowania'] - $Rabat),2),'',false);
                         //
                    } else {
                         //
                         $CenaPoRabacie = $ProduktKupon->info['cena_brutto_bez_formatowania'];
                         //
                    }
                    
                    $TablicaKuponow[] = array('kupon_id' => $infKupon['coupons_id'],
                                              'kupon_kod' => $infKupon['coupons_name'],
                                              'kupon_typ' => $infKupon['coupons_discount_type'],
                                              'kupon_wartosc' => $infKupon['coupons_discount_value'],
                                              'warunek_promocja' => $infKupon['coupons_specials'],
                                              'grupa_klientow' => $infKupon['coupons_customers_groups_id'],
                                              'cena_brutto' => $ProduktKupon->info['cena_brutto_bez_formatowania'],
                                              'cena_netto' => $ProduktKupon->info['cena_netto_bez_formatowania'],
                                              'cena_netto_po_rabacie' => round(($CenaPoRabacie / (1 + ($ProduktKupon->info['stawka_vat']/100))), 2),
                                              'cena_brutto_po_rabacie' => $CenaPoRabacie,
                                              'typ' => $infKupon['coupons_discount_type'],
                                              'wysylki' => $infKupon['coupons_modules_shipping']);
                }

                if ( $infKupon['coupons_exclusion'] == 'produkty' && in_array( $ProduktKupon->info['id'], explode(',', (string)$infKupon['coupons_exclusion_id']) ) ) {
                     //
                     if ( $infKupon['coupons_discount_type'] == 'percent' ) {
                          //
                          $Rabat = round(($ProduktKupon->info['cena_brutto_bez_formatowania'] * ( $infKupon['coupons_discount_value'] / 100 )),2);
                          $CenaPoRabacie = $GLOBALS['waluty']->PokazCeneBezSymbolu(($ProduktKupon->info['cena_brutto_bez_formatowania'] - $Rabat),'',false);
                          //
                    } elseif ( $infKupon['coupons_discount_type'] == 'fixed' ) {
                          //              
                          $Rabat = $GLOBALS['waluty']->PokazCeneBezSymbolu($infKupon['coupons_discount_value'],'',true);
                          //
                          if ( $infKupon['coupons_currency'] != '' ) {
                               //
                               if ( $infKupon['coupons_currency'] == $_SESSION['domyslnaWaluta']['kod'] ) {
                                    $Rabat = $GLOBALS['waluty']->PokazCeneBezSymbolu($infKupon['coupons_discount_value'],'',false);
                               }
                               //
                          }
                          //
                          $infKupon['coupons_discount_value'] = $Rabat;
                          //
                          $CenaPoRabacie = $GLOBALS['waluty']->PokazCeneBezSymbolu(round(($ProduktKupon->info['cena_brutto_bez_formatowania'] - $Rabat),2),'',false);
                          //
                    } else {
                          //
                          $CenaPoRabacie = $ProduktKupon->info['cena_brutto_bez_formatowania'];
                          //
                    }
                    
                    $TablicaKuponow[] = array('kupon_id' => $infKupon['coupons_id'],
                                              'kupon_kod' => $infKupon['coupons_name'],
                                              'kupon_typ' => $infKupon['coupons_discount_type'],
                                              'kupon_wartosc' => $infKupon['coupons_discount_value'],
                                              'warunek_promocja' => $infKupon['coupons_specials'],
                                              'grupa_klientow' => $infKupon['coupons_customers_groups_id'],
                                              'cena_brutto' => $ProduktKupon->info['cena_brutto_bez_formatowania'],
                                              'cena_netto' => $ProduktKupon->info['cena_netto_bez_formatowania'],
                                              'cena_netto_po_rabacie' => round(($CenaPoRabacie / (1 + ($ProduktKupon->info['stawka_vat']/100))), 2),
                                              'cena_brutto_po_rabacie' => $CenaPoRabacie,
                                              'typ' => $infKupon['coupons_discount_type'],
                                              'wysylki' => $infKupon['coupons_modules_shipping']);
                }

                if ( $infKupon['coupons_exclusion'] == 'kategorie' ) {

                    $TablicaKategoriiKuponu = explode(',', (string)$infKupon['coupons_exclusion_id']);
                    $TablicaWszystkichKategorii = Kategorie::ProduktKategorie($ProduktKupon->info['id']);
                    $JestKupon = array_intersect($TablicaWszystkichKategorii, $TablicaKategoriiKuponu);

                    if ( count($JestKupon) > 0 ) {
                         //
                         if ( $infKupon['coupons_discount_type'] == 'percent' ) {
                              //
                              $Rabat = round(($ProduktKupon->info['cena_brutto_bez_formatowania'] * ( $infKupon['coupons_discount_value'] / 100 )),2);
                              $CenaPoRabacie = $GLOBALS['waluty']->PokazCeneBezSymbolu(($ProduktKupon->info['cena_brutto_bez_formatowania'] - $Rabat),'',false);
                              //
                        } elseif ( $infKupon['coupons_discount_type'] == 'fixed' ) {
                              //              
                              $Rabat = $GLOBALS['waluty']->PokazCeneBezSymbolu($infKupon['coupons_discount_value'],'',true);
                              //
                              if ( $infKupon['coupons_currency'] != '' ) {
                                   //
                                   if ( $infKupon['coupons_currency'] == $_SESSION['domyslnaWaluta']['kod'] ) {
                                        $Rabat = $GLOBALS['waluty']->PokazCeneBezSymbolu($infKupon['coupons_discount_value'],'',false);
                                   }
                                   //
                              }
                              //
                              $infKupon['coupons_discount_value'] = $Rabat;
                              //
                              $CenaPoRabacie = $GLOBALS['waluty']->PokazCeneBezSymbolu(round(($ProduktKupon->info['cena_brutto_bez_formatowania'] - $Rabat),2),'',false);
                              //
                        } else {
                              //
                              $CenaPoRabacie = $ProduktKupon->info['cena_brutto_bez_formatowania'];
                              //
                        }
                        
                        $TablicaKuponow[] = array('kupon_id' => $infKupon['coupons_id'],
                                                  'kupon_kod' => $infKupon['coupons_name'],
                                                  'kupon_typ' => $infKupon['coupons_discount_type'],
                                                  'kupon_wartosc' => $infKupon['coupons_discount_value'],
                                                  'warunek_promocja' => $infKupon['coupons_specials'],
                                                  'grupa_klientow' => $infKupon['coupons_customers_groups_id'],
                                                  'cena_brutto' => $ProduktKupon->info['cena_brutto_bez_formatowania'],
                                                  'cena_netto' => $ProduktKupon->info['cena_netto_bez_formatowania'],
                                                  'cena_netto_po_rabacie' => round(($CenaPoRabacie / (1 + ($ProduktKupon->info['stawka_vat']/100))), 2),
                                                  'cena_brutto_po_rabacie' => $CenaPoRabacie,
                                                  'typ' => $infKupon['coupons_discount_type'],
                                                  'wysylki' => $infKupon['coupons_modules_shipping']);
                                                  
                    }
                    
                }
                
                if ( $infKupon['coupons_exclusion'] == 'kategorie_producenci' ) {
                  
                    $podzielTmp = explode('#', (string)$infKupon['coupons_exclusion_id']);
                    
                    if ( count($podzielTmp) == 2 ) {
                        
                         $jestAktywny = true;
                  
                         // czy jest dla producenta
                         if ( !in_array( $ProduktKupon->info['id_producenta'], explode(',', (string)$podzielTmp[0]) ) ) {
                              //
                              $jestAktywny = false;                              
                              //
                         }
                         // czy jest dla kategorii
                         $TablicaKategoriiKuponu = explode(',', (string)$podzielTmp[1]);
                         $TablicaWszystkichKategorii = Kategorie::ProduktKategorie($ProduktKupon->info['id']);
                         $JestKupon = array_intersect($TablicaWszystkichKategorii, $TablicaKategoriiKuponu);
                         //
                         if ( count($JestKupon) == 0 ) {                         
                              //
                              $jestAktywny = false;
                              //
                         }
                         
                         if ( $jestAktywny == true ) {
                  
                              if ( $infKupon['coupons_discount_type'] == 'percent' ) {
                                   //
                                   $Rabat = round(($ProduktKupon->info['cena_brutto_bez_formatowania'] * ( $infKupon['coupons_discount_value'] / 100 )),2);
                                   $CenaPoRabacie = $GLOBALS['waluty']->PokazCeneBezSymbolu(($ProduktKupon->info['cena_brutto_bez_formatowania'] - $Rabat),'',false);
                                   //
                              } elseif ( $infKupon['coupons_discount_type'] == 'fixed' ) {
                                   //              
                                   $Rabat = $GLOBALS['waluty']->PokazCeneBezSymbolu($infKupon['coupons_discount_value'],'',true);
                                   //
                                   if ( $infKupon['coupons_currency'] != '' ) {
                                        //
                                        if ( $infKupon['coupons_currency'] == $_SESSION['domyslnaWaluta']['kod'] ) {
                                             $Rabat = $GLOBALS['waluty']->PokazCeneBezSymbolu($infKupon['coupons_discount_value'],'',false);
                                        }
                                        //
                                   }
                                   //
                                   $infKupon['coupons_discount_value'] = $Rabat;
                                   //
                                   $CenaPoRabacie = $GLOBALS['waluty']->PokazCeneBezSymbolu(round(($ProduktKupon->info['cena_brutto_bez_formatowania'] - $Rabat),2),'',false);
                                   //
                              } else {
                                   //
                                   $CenaPoRabacie = $ProduktKupon->info['cena_brutto_bez_formatowania'];
                                   //
                              }
                              
                              $TablicaKuponow[] = array('kupon_id' => $infKupon['coupons_id'],
                                                        'kupon_kod' => $infKupon['coupons_name'],
                                                        'kupon_typ' => $infKupon['coupons_discount_type'],
                                                        'kupon_wartosc' => $infKupon['coupons_discount_value'],
                                                        'warunek_promocja' => $infKupon['coupons_specials'],
                                                        'grupa_klientow' => $infKupon['coupons_customers_groups_id'],
                                                        'cena_brutto' => $ProduktKupon->info['cena_brutto_bez_formatowania'],
                                                        'cena_netto' => $ProduktKupon->info['cena_netto_bez_formatowania'],
                                                        'cena_netto_po_rabacie' => round(($CenaPoRabacie / (1 + ($ProduktKupon->info['stawka_vat']/100))), 2),
                                                        'cena_brutto_po_rabacie' => $CenaPoRabacie,
                                                        'typ' => $infKupon['coupons_discount_type'],
                                                        'wysylki' => $infKupon['coupons_modules_shipping']);
                                                        
                         }
                         
                    }
                    
                }                
                
            }
            
        }
        
    }

    if ( count($TablicaKuponow) > 0 ) {
         //
         $TablicaCenZKodem = array_column($TablicaKuponow, 'cena_brutto_po_rabacie');
         $IndexMin = array_search(min($TablicaCenZKodem), $TablicaCenZKodem, true);
         //
         $TablicaKuponow = $TablicaKuponow[$IndexMin];
         //
    }

    return $TablicaKuponow;
    
  }   

} 

?>