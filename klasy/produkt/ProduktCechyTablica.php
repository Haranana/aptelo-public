<?php

if ( isset($pobierzFunkcje) ) {
  
    // generuje tablice globalne z nazwami cech
    Funkcje::TabliceCech();    

    $TablicaCechy = Funkcje::CechyProduktuPoId( $cechy, true );
    
    for ($g = 0, $n = count($TablicaCechy); $g < $n; $g++) {
    
        $zapytanie = "SELECT DISTINCT pa.options_values_weight, 
                                      pa.options_values_price, 
                                      pa.options_values_tax, 
                                      pa.options_values_price_tax, 
                                      pa.price_prefix,
                                      po.products_options_value,
                                      po.products_options_name,
                                      po.products_options_id,
                                      pv.products_options_values_name,
                                      pv.products_options_values_id
                                 FROM products_attributes pa, products_options po, products_options_values pv
                                WHERE pa.options_id = po.products_options_id AND 
                                      pv.products_options_values_id =  '" . $TablicaCechy[$g]['wartosc'] . "' AND pv.language_id = '" . $this->jezykDomyslnyId . "' AND
                                      pa.products_id = '" . $this->id_produktu . "' AND 
                                      pa.options_id = '" . $TablicaCechy[$g]['cecha'] . "' AND 
                                      pa.options_values_id = '" . $TablicaCechy[$g]['wartosc'] . "'
                                  AND po.language_id = '" . $this->jezykDomyslnyId . "'";
        
        $sql = $GLOBALS['db']->open_query($zapytanie);
        
        if ( (int)$GLOBALS['db']->ile_rekordow($sql) ) {
          
            $cecha = $sql->fetch_assoc();
            //
            $WspolczynnikRabatu = 1;
            if ( $this->info['rabat_produktu'] > 0 && RABATY_CECHY == 'tak' ) {
                 $WspolczynnikRabatu = (100 - $this->info['rabat_produktu']) / 100;
            }          
            //
            
            // wartosci domyslne cech
            if ( $GLOBALS['WartosciCech'][$TablicaCechy[$g]['wartosc']]['wartosc'] > 0 && $cecha['options_values_price'] == 0 ) {
                 //
                 $cecha_brutto = $GLOBALS['WartosciCech'][$TablicaCechy[$g]['wartosc']]['wartosc'];
                 $cecha_netto = round(($cecha_brutto / (1 + ($this->vat_podstawa/100))), 2);
                 $cecha_vat = $cecha_brutto - $cecha_netto;
                 //
                 $cecha['options_values_price'] = $cecha_netto;
                 $cecha['options_values_tax'] = $cecha_vat;
                 $cecha['options_values_price_tax'] = $cecha_brutto;
                 //
                 unset($cecha_brutto, $cecha_netto, $cecha_vat);
                 //
                 $cecha['price_prefix'] = $GLOBALS['WartosciCech'][$TablicaCechy[$g]['wartosc']]['prefix'];
                 //
            }           
            
            //
            if ( $cecha['products_options_value'] == 'kwota' ) {
                //
                // dodaje rabaty do produktu do wartosci cech
                $CenaCechBrutto = round(($cecha['options_values_price_tax'] * $WspolczynnikRabatu), 2);
                $CenaCechNetto = round(($cecha['options_values_price'] * $WspolczynnikRabatu), 2);
                //
            }
            if ( $cecha['products_options_value'] == 'procent' ) {
                //
                $CenaCechBrutto = round(($this->info['cena_brutto_bez_formatowania'] * ($cecha['options_values_price_tax'] / 100)), 2);
                $CenaCechNetto = round(($this->info['cena_netto_bez_formatowania'] * ($cecha['options_values_price_tax'] / 100)), 2);
                //
            }        

            $TablicaCech[$TablicaCechy[$g]['cecha']]['id_cechy'] = $cecha['products_options_id'];
            $TablicaCech[$TablicaCechy[$g]['cecha']]['nazwa_cechy'] = $cecha['products_options_name'];
            $TablicaCech[$TablicaCechy[$g]['cecha']]['id_wartosci'] = $cecha['products_options_values_id'];
            $TablicaCech[$TablicaCechy[$g]['cecha']]['nazwa_wartosci'] = $cecha['products_options_values_name'];
            
            if ( $cecha['price_prefix'] != '*' ) {
                 //
                 $TablicaCech[$TablicaCechy[$g]['cecha']]['cena'] = $GLOBALS['waluty']->FormatujCene( $CenaCechBrutto, $CenaCechNetto, 0, $_SESSION['domyslnaWaluta']['id'], false );
                 $TablicaCech[$TablicaCechy[$g]['cecha']]['kwota_vat'] = $GLOBALS['waluty']->FormatujCene( $CenaCechBrutto - $CenaCechNetto, 0, 0, $_SESSION['domyslnaWaluta']['id'], false );
                 //
            } else {
                 //
                 $TablicaCech[$TablicaCechy[$g]['cecha']]['cena'] = array( 'brutto' => $cecha['options_values_price_tax'], 'netto' => 0 );
                 $TablicaCech[$TablicaCechy[$g]['cecha']]['kwota_vat'] = $GLOBALS['waluty']->FormatujCene( 0, 0, 0, $_SESSION['domyslnaWaluta']['id'], false );         
                 //
            }
            
            $TablicaCech[$TablicaCechy[$g]['cecha']]['prefix'] = $cecha['price_prefix'];
            
            unset($WspolczynnikRabatu, $cecha);

        }
        
        $GLOBALS['db']->close_query($sql);         

        unset($zapytanie);

    }

}
       
?>