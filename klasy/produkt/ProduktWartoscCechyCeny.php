<?php

if ( isset($pobierzFunkcje) ) {
  
    if ( KOSZYK_SPOSOB_DODAWANIA == 'tak' ) {
         //
         $cechy = substr((string)$cechy, 0, strpos((string)$cechy, 'U'));
         //
    }

    // dodatkowo ustalanie wagi produktu z kombinacja cech
    $TablicaCechy = Funkcje::CechyProduktuPoId( $cechy, true );
    $WagaCechy = 0;
    
    for ($g = 0, $n = count($TablicaCechy); $g < $n; $g++) {
    
        $zapytanie = "SELECT DISTINCT options_values_weight
                                 FROM products_attributes
                                WHERE products_id = '" . $this->id_produktu . "' AND 
                                      options_id = '" . $TablicaCechy[$g]['cecha'] . "' AND 
                                      options_values_id = '" . $TablicaCechy[$g]['wartosc'] . "'";
        
        $sql = $GLOBALS['db']->open_query($zapytanie);
        
        if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
          
            $cecha = $sql->fetch_assoc();
            //
            $WagaCechy += $cecha['options_values_weight'];
            //
            unset($zapytanie, $cecha);

        }            
        
        $GLOBALS['db']->close_query($sql);
    
    } 

    unset($TablicaCechy);
    
    // dzieli na tablice
    $cechyTb = explode('x', (string)$cechy);
    $cechyTmp = array();
    for ($g = 1; $g < count($cechyTb); $g++) {
        $cechyTmp[] = $cechyTb[$g];
    }
    $cechy = implode(',', (array)$cechyTmp);
    unset($cechyTb, $cechyTmp);

    $DodatkoweCeny = '';
    if ( (int)ILOSC_CEN > 1 ) {
        //
        for ($n = 2; $n <= (int)ILOSC_CEN; $n++) {
            //
            $DodatkoweCeny .= 'products_stock_price_' . $n . ', products_stock_price_tax_' . $n . ', products_stock_old_price_' . $n . ', ';
            //
        }
        //
    }               

    // szuka cech produktu do ustalenia ceny produktu z cechami    
    $zapytanieCechy = "SELECT DISTINCT " . $DodatkoweCeny . " products_stock_attributes, products_stock_price, products_stock_price_tax, products_stock_tax, products_stock_old_price 
                                  FROM products_stock
                                 WHERE products_id = '" . $this->id_produktu . "' and products_stock_attributes = '" . $cechy . "'";

    unset($cechy, $DodatkoweCeny);

    $sql = $GLOBALS['db']->open_query($zapytanieCechy);
    
    if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
      
        $cecha = $sql->fetch_assoc();
        
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
        }          
        
        // jezeli nie ma indywidualnej cechy dla kombinacji cech przyjmuje cene domyslna produktu
        if ( isset($cecha['products_stock_price_tax']) && $cecha['products_stock_price_tax'] == 0 ) {
        
            $cecha['products_stock_price'] = $this->infoSql['products_price'];   
            $cecha['products_stock_price_tax'] = $this->infoSql['products_price_tax'];  
        
        } else {
          
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

                unset($CenaRabatyCechy);
                
            } else {
              
                $RabatProduktDnia = 1;
                if ( isset($GLOBALS['produkt_dnia'][date('Y-m-d', time())]) ) {
                     //
                     $RabatProduktDnia = (100 - (float)$GLOBALS['produkt_dnia'][date('Y-m-d', time())]['rabat']) / 100;
                     //
                }
              
                $cecha['products_stock_price'] =  $cecha['products_stock_price'] * $RabatProduktDnia;
                $cecha['products_stock_price_tax'] = $cecha['products_stock_price_tax'] * $RabatProduktDnia;
                
                unset($RabatProduktDnia);
              
            }            
            
        }

    }
    
    $GLOBALS['db']->close_query($sql);  
    unset($zapytanieCechy);

    if ( isset($cecha['products_stock_price_tax']) ) {
      
         $TablicaCen = $GLOBALS['waluty']->FormatujCene( $cecha['products_stock_price_tax'], $cecha['products_stock_price'], 0, $this->infoSql['products_currencies_id'], false );
         
    } else {
      
         $TablicaCen = $GLOBALS['waluty']->FormatujCene( 0, 0, 0, $this->infoSql['products_currencies_id'], false );
      
    }

}
       
?>