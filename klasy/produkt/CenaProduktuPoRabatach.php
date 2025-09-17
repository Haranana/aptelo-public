<?php

if ( isset($pobierzFunkcje) ) {

    // ustalanie rabatow dla klienta
    
    $cenaNetto = $netto;
    $cenaBrutto = $brutto;
    
    $Rabat = 0;
    $wszystkieZnizki = array();
    
    if ( $this->infoSql['products_not_discount'] == 0 ) {
    
        if ( $this->CenaIndywidualna == false ) {

            if ( isset($_SESSION['znizkiKlienta']) ) {
              
                // czy produkt nie jest zestawem i nie sa wylaczone rabaty dla zestawow
                $RabatyZestaw = false;
                if ( ($this->infoSql['products_set'] == 1 && RABATY_ZESTAWY == 'tak') || $this->infoSql['products_set'] == 0 ) {
                      $RabatyZestaw = true;
                }

                // jezeli klient jest zalogowany
                if ( ( $this->infoSql['specials_status'] != '1' || ($this->infoSql['specials_status'] == '1' && RABATY_PROMOCJE == 'tak') || RABATY_PROMOCJE_WYSWIETLAJ == 'tak' ) && $RabatyZestaw == true ) {
                
                    // szuka wszystkich kategorii do jakich jest przypisany produkt
                    $JakieKategorieMaProdukt = Kategorie::ProduktKategorie( $this->infoSql['products_id'] );
                    //
                    
                    if ( RABAT_SUMOWANIE_KATEGORII == 'nie' ) {
                      
                        // sprawdza osobno kategorie - jezeli produkt nalezy do kilku kategorii zeby nie sumowac rabatow kategorii
                        $znizkiKategorii = array();
                        foreach( $_SESSION['znizkiKlienta'] as $znizkiKlienta ) {
                          //
                          if ( $znizkiKlienta['0'] == 'Kategoria' && in_array($znizkiKlienta['1'], $JakieKategorieMaProdukt) ) {
                            $znizkiKategorii[] = abs(floatval($znizkiKlienta['2']));
                          }                  
                          //                  
                        }
                        if ( count($znizkiKategorii) > 0 ) {
                          $wszystkieZnizki[] = max($znizkiKategorii);
                        }
                        unset($znizkiKategorii);

                    }
                    
                    foreach( $_SESSION['znizkiKlienta'] as $znizkiKlienta ) {
                      
                      if ( $znizkiKlienta['0'] == 'Indywidualna' ) {
                        $wszystkieZnizki[] = abs(floatval($znizkiKlienta['2']));
                      }
                      if ( $znizkiKlienta['0'] == 'Grupa klientów' ) {
                        $wszystkieZnizki[] = abs(floatval($znizkiKlienta['2']));
                      }
                      if ( $znizkiKlienta['0'] == 'Producent' && $znizkiKlienta['1'] == $this->infoSql['manufacturers_id'] ) {
                        $wszystkieZnizki[] = abs(floatval($znizkiKlienta['2']));
                      }
                      
                      // znizka producent i kategoria
                      if ( $znizkiKlienta['0'] == 'Kategoria/Producent' && $znizkiKlienta['4'] == $this->infoSql['manufacturers_id'] && in_array($znizkiKlienta['3'], $JakieKategorieMaProdukt) ) {
                          $wszystkieZnizki[] = abs(floatval($znizkiKlienta['2']));
                          $produktZnizki[] = abs(floatval($znizkiKlienta['2']));
                      }                       
                      
                      if ( RABAT_SUMOWANIE_KATEGORII == 'tak' ) {
                          if ( $znizkiKlienta['0'] == 'Kategoria' && in_array($znizkiKlienta['1'], $JakieKategorieMaProdukt) ) {
                            $wszystkieZnizki[] = abs(floatval($znizkiKlienta['2']));
                          }   
                      }
                      
                      if ( $znizkiKlienta['0'] == 'Produkt' && $znizkiKlienta['1'] == $this->infoSql['products_id'] ) {
                        $wszystkieZnizki[] = abs(floatval($znizkiKlienta['2']));
                      }
                    }
                    
                    unset($JakieKategorieMaProdukt);
                    
                }

                // sprawdzenie czy znizki maja byc sumowane czy nie
                if ( count($wszystkieZnizki) > 0 ) {
                
                  if ( RABAT_SUMOWANIE == 'tak' ) {
                    foreach ( $wszystkieZnizki as $wartosc ) {
                      $Rabat = $Rabat + floatval($wartosc);
                    }
                  } else {
                    $znizka = max($wszystkieZnizki);
                    $Rabat = floatval($znizka);
                  }
                  
                }

                // sprawdzenie czy rabat nie przekracza maksymalnej wartosci
                if ( $Rabat > 0 ) {
                  if ( $Rabat > abs(floatval(RABAT_MAKSYMALNA_WARTOSC)) ) $Rabat = abs(floatval(RABAT_MAKSYMALNA_WARTOSC));
                  //
                  $this->ikonki['rabat'] = '1';
                  $this->ikonki['rabat_wartosc'] = $Rabat;
                  //               
                }

                // ustalenie cen z rabatem
                if ( $this->infoSql['products_set'] == 0 ) {
                     //
                     $cenaBrutto = $cenaBrutto - ( $cenaBrutto * $Rabat/100 );
                     $cenaNetto = $cenaNetto - ( $cenaNetto * $Rabat/100 );
                     //
                }
                
                unset($RabatyZestaw);

            } else {
              
                // jezeli klient nie jest zalogowany
                if ( NARZUT_NIEZALOGOWANI != '' && floatval(NARZUT_NIEZALOGOWANI) != 0 ) {
                     //
                     $CzyProduktPromocja = '0';
                     if ( FunkcjeWlasnePHP::my_strtotime($this->infoSql['specials_date']) < time() || ((int)FunkcjeWlasnePHP::my_strtotime($this->infoSql['specials_date_end']) > 0 && FunkcjeWlasnePHP::my_strtotime($this->infoSql['specials_date_end']) < time()) ) {
                          $CzyProduktPromocja = $this->infoSql['specials_status'];         
                     }                   
                     //
                     if ( NARZUT_NIEZALOGOWANI_PROMOCJE == 'nie' && $CzyProduktPromocja == '1' ) {
                          //
                          $cenaBrutto = $cenaBrutto;
                          $cenaNetto = $cenaNetto;
                          //
                      } else {
                          //
                          $cenaBrutto = $cenaBrutto + ( $cenaBrutto * floatval(NARZUT_NIEZALOGOWANI)/100 );
                          $cenaNetto = $cenaNetto + ( $cenaNetto * floatval(NARZUT_NIEZALOGOWANI)/100 );
                          //
                     }
                     //
                     unset($CzyProduktPromocja);
                     //
                }
              
            }
        
        }
        
    }
    
}
       
?>