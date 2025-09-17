<?php

class Gratisy {

    public static function TablicaGratisow( $sprawdzKoszyk = 'tak' ) {

        $ZawartoscKoszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();
        
        $SumaWartosciProduktow = $ZawartoscKoszyka['brutto_baza'];

        $TablicaGratisow = array();        
        
        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('Gratisy', CACHE_INNE);      
        
        $TablicaWszystkichGratisow = array();

        if ( !$WynikCache && !is_array($WynikCache) ) { 
        
            // szuka wszystkich gratisow w bazie
            $zapytanie = "SELECT gift_products_id, 
                                 gift_value_of, 
                                 gift_value_for, 
                                 gift_value_exclusion,
                                 gift_price, 
                                 gift_min_quantity,
                                 gift_exclusion,
                                 gift_exclusion_id,
                                 customers_group_id,
                                 gift_only_one
                            FROM products_gift 
                           WHERE gift_status = '1'";

            $sql = $GLOBALS['db']->open_query($zapytanie);
            
            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
            
                while ($info = $sql->fetch_assoc()) {
                    //
                    $TablicaWszystkichGratisow[] = $info;
                    //
                }
                
                unset($info);
                
            }
            
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie);        
            
            $GLOBALS['cache']->zapisz('Gratisy', $TablicaWszystkichGratisow, CACHE_INNE);
    
          } else {
         
            $TablicaWszystkichGratisow = $WynikCache;
        
        }    
                       
        foreach ( $TablicaWszystkichGratisow as $info ) { 
            //
            $WarunekGratisKwoty = true;
            $WarunekGratisIlosc = true;
            $WarunekGratisGrupyKlientow = true;
            //
            
            // --- warunek grupy klientow ---
            if ( $info['customers_group_id'] != '' ) {
                 //
                 if ( isset($_SESSION['customers_groups_id']) && (int)$_SESSION['customers_groups_id'] > 0 ) {
                      //
                      $PodzielGrupe = explode(',', (string)$info['customers_group_id']);
                      //
                      if ( !in_array((int)$_SESSION['customers_groups_id'], $PodzielGrupe) ) {
                          //
                          $WarunekGratisGrupyKlientow = false;
                          //
                      }
                      //
                      unset($PodzielGrupe);
                      //
                 } else {
                      //
                      // jezeli jest wpisana grupa a klient nie jest zalogowany
                      $WarunekGratisGrupyKlientow = false;
                      //
                      // dodatkowo sprawdzi czy nie jest dla niezarejestrowanych
                      $PodzielGrupe = explode(',', (string)$info['customers_group_id']);
                      //
                      if ( in_array('0', $PodzielGrupe) ) {
                          //
                          $WarunekGratisGrupyKlientow = true;
                          //
                      }
                      //
                      unset($PodzielGrupe);
                      //
                      //                 
                 }
                 //
            }

            // --- warunek ilosci produktow w koszyku --- bez innych warunkow
            
            if ( $info['gift_min_quantity'] > 0 && trim((string)$info['gift_exclusion']) == '' ) {
                 //
                 $SumaProduktowBezGratisow = 0;
                 //
                 foreach ( $_SESSION['koszyk'] as $rekord ) {
                    //
                    if ( $rekord['rodzaj_ceny'] != 'gratis' ) {
                         //
                         $SumaProduktowBezGratisow += $rekord['ilosc'];
                         //                    
                    }
                    //
                 }
                 //
                 if ( $SumaProduktowBezGratisow < $info['gift_min_quantity'] ) {
                      //
                      $WarunekGratisIlosc = false;
                      //
                 }
                 //
                 unset($SumaProduktowBezGratisow);
                 //
            }
            
            // --- warunek dla kategorii, producentow i produktow + sprawdzenie ilosci
            
            $SumaWartosciProduktowDlaWarunkow = 0;
            
            // ograniczenia tylko dla konkretnych kategorii, producentow i produktow
            if ( !empty($info['gift_exclusion']) && !empty($info['gift_exclusion_id']) ) {
                 //
                 $SumaProduktowDlaWarunkow = 0;
                 //
                 foreach ( $_SESSION['koszyk'] as $rekord ) { 

                    if ( $rekord['rodzaj_ceny'] != 'gratis' ) {
                 
                        // jezeli jest tylko dla kategorii
                        if ( $info['gift_exclusion'] == 'kategorie' ) {
                             //
                             // do jakich kategorii nalezy produkt
                             $tablica = Kategorie::ProduktKategorie( Funkcje::SamoIdProduktuBezCech( $rekord['id'] ) );
                             //
                             $nalezyDoKategorii = false;
                             foreach ( $tablica as $id ) {
                                // sprawdza czy dane id nalezy do tablicy dozwolnych kategorii
                                if ( in_array($id, explode(',', (string)$info['gift_exclusion_id']) ) ) {
                                     $SumaProduktowDlaWarunkow += $rekord['ilosc'];
                                     $SumaWartosciProduktowDlaWarunkow += ($rekord['ilosc'] * $rekord['cena_brutto'] );
                                }
                             }
                             //
                             unset($tablica);
                        }
                        
                        // jezeli jest tylko dla producenta
                        if ( $info['gift_exclusion'] == 'producenci' ) {
                             //
                             // do jakich producentow nalezy produkt
                             $id = Producenci::ProduktProducent( Funkcje::SamoIdProduktuBezCech( $rekord['id'] ) );
                             //
                             $nalezyDoProducenta = false;
                             // sprawdza czy dane id nalezy do tablicy dozwolnych producentow
                             if ( in_array($id, explode(',', (string)$info['gift_exclusion_id']) ) ) {
                                  $SumaProduktowDlaWarunkow += $rekord['ilosc'];
                                  $SumaWartosciProduktowDlaWarunkow += ($rekord['ilosc'] * $rekord['cena_brutto'] );
                             }
                             //
                             unset($id, $nalezyDoProducenta);
                        }  
                        
                        // jezeli jest tylko dla kategorii i producenta
                        if ( $info['gift_exclusion'] == 'kategorie_producenci' ) {
                             //
                             $nalezyDoKategoriiTmp = false;
                             $nalezyDoProducentaTmp = false;
                             //
                             $podzielId = explode('|', (string)$info['gift_exclusion_id']);
                             //
                             // do jakich kategorii nalezy produkt
                             $tablica = Kategorie::ProduktKategorie( Funkcje::SamoIdProduktuBezCech( $rekord['id'] ) );
                             //
                             // sprawdzi czy nalezy do kategorii
                             foreach ( $tablica as $id ) {
                                // sprawdza czy dane id nalezy do tablicy dozwolnych kategorii
                                if ( isset($podzielId[0]) ) {
                                    if ( in_array($id, explode(',', (string)$podzielId[0]) ) ) {
                                         $nalezyDoKategoriiTmp = true;
                                    }
                                }
                             }
                             //
                             unset($tablica);
                             //
                             // do jakich producentow nalezy produkt
                             $id = Producenci::ProduktProducent( Funkcje::SamoIdProduktuBezCech( $rekord['id'] ) );
                             //
                             $nalezyDoProducenta = false;
                             // sprawdza czy dane id nalezy do tablicy dozwolnych producentow
                             if ( in_array($id, explode(',', (string)$podzielId[1]) ) ) {
                                 if ( isset($podzielId[0]) ) {
                                      $nalezyDoProducentaTmp = true;
                                 }
                             } 
                             //
                             unset($tablica);
                             //
                             if ( $nalezyDoKategoriiTmp == true && $nalezyDoProducentaTmp == true ) {
                                  $SumaProduktowDlaWarunkow += $rekord['ilosc'];
                                  $SumaWartosciProduktowDlaWarunkow += ($rekord['ilosc'] * $rekord['cena_brutto'] );
                             }
                             //
                             unset($nalezyDoKategoriiTmp, $nalezyDoProducentaTmp);
                        }                        

                        // jezeli jest tylko dla produktow
                        if ( $info['gift_exclusion'] == 'produkty' ) {
                             //
                             $nalezyDoProduktow = false;
                             // sprawdza czy dane id nalezy do tablicy dozwolnych produktow
                             if ( in_array( Funkcje::SamoIdProduktuBezCech( $rekord['id'] ), explode(',', (string)$info['gift_exclusion_id']) ) ) {
                                  $SumaProduktowDlaWarunkow += $rekord['ilosc'];
                                  $SumaWartosciProduktowDlaWarunkow += ($rekord['ilosc'] * $rekord['cena_brutto'] );
                             }
                             //
                             unset($nalezyDoProduktow);
                        }   

                    }
                    
                 }
                 //
                 if ( $SumaProduktowDlaWarunkow == 0 ) {
                     //
                     $WarunekGratisIlosc = false;
                     //
                 }
                 //
                 // jezeli jest ustawiona minimalna ilosc produktow
                 if ( $info['gift_min_quantity'] > 1 ) {
                      //
                      if ( $SumaProduktowDlaWarunkow < $info['gift_min_quantity'] ) {
                           //
                           $WarunekGratisIlosc = false;
                           //
                      }
                      //
                 }
                 //
            }            
            
            // --- warunek wartosci zamowienia ---
            
            // przelicza na walute w sklepie
            $info['gift_value_of'] = $GLOBALS['waluty']->PokazCeneBezSymbolu($info['gift_value_of'],'',true);
            $info['gift_value_for'] = $GLOBALS['waluty']->PokazCeneBezSymbolu($info['gift_value_for'],'',true);
            
            $SumaWartosciProduktowGratisu = $SumaWartosciProduktow;

            if ( (int)$info['gift_value_exclusion'] == 1 ) {
                  //
                  $SumaWartosciProduktowGratisu = $SumaWartosciProduktowDlaWarunkow;
                  //
            }            
            //
            if ( $SumaWartosciProduktowGratisu <= $info['gift_value_of'] || $SumaWartosciProduktowGratisu >= $info['gift_value_for'] ) {
                 //
                 $WarunekGratisKwoty = false;
                 //
            }
            
            if ( $WarunekGratisKwoty && $WarunekGratisIlosc && $WarunekGratisGrupyKlientow ) {
                //
                // sprawdzi czy takiego gratisu nie ma juz w koszyku
                $NieMaGratisu = false;
                //
                if ( $sprawdzKoszyk == 'tak' ) {
                    //
                    foreach ( $_SESSION['koszyk'] As $ProduktyKoszyka ) {
                        //
                        if ( $ProduktyKoszyka['id'] == (int)$info['gift_products_id'] . '-gratis' && $ProduktyKoszyka['rodzaj_ceny'] == 'gratis' ) {
                            $NieMaGratisu = true;
                        }
                        //
                    }
                    //
                }
                //
                if ( $NieMaGratisu == false ) {
                    //
                    // musi sprawdzic czy produkt jest i czy jest aktywny
                    $Produkt = new Produkt( (int)$info['gift_products_id'] ); 
                    //
                    if ($Produkt->CzyJestProdukt == true) {
                        //
                        $TablicaGratisow[$info['gift_products_id']] = array('id_gratisu'   => $info['gift_products_id'],
                                                                            'cena_gratisu' => $info['gift_price'],
                                                                            'tylko_jeden'  => $info['gift_only_one']);
                        //
                    }
                    //
                }
                //
                // sprawdzi czy w koszyku nie ma gratisu ktory jest mozliwy do dodania tylko jeden
                if ( $sprawdzKoszyk == 'tak' ) {
                foreach ( $_SESSION['koszyk'] As $ProduktyKoszyka ) {
                    //
                    if ( $ProduktyKoszyka['id'] == $info['gift_products_id'] . '-gratis' && $ProduktyKoszyka['rodzaj_ceny'] == 'gratis' ) {
                         //
                         if ( $info['gift_only_one'] == 1 ) {
                              return array();
                         }
                         //
                    }
                    //
                }
                }
                //                
                //
                unset($Produkt, $NieMaGratisu);
                //
            }
            //        
            unset($WarunekGratisKwoty, $WarunekGratisIlosc, $WarunekGratisGrupyKlientow, $SumaWartosciProduktowGratisu, $SumaWartosciProduktowDlaWarunkow);
            //
        }
        
        unset($TablicaWszystkichGratisow, $ZawartoscKoszyka, $SumaWartosciProduktow);    

        return $TablicaGratisow;

    }
  
} 

?>