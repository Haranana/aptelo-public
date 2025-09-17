<?php

if ( isset($pobierzFunkcje) ) {

    // generuje tablice globalne z nazwami cech
    Funkcje::TabliceCech();       

    $TablicaCechy = Funkcje::CechyProduktuPoId( $cechy, true );
    
    $CenaCechBrutto = 0;
    $CenaCechNetto = 0;
    $WagaCechy = 0;
    
    $WspolczynnikRabatu = 1;
    if ( $this->info['rabat_produktu'] > 0 && RABATY_CECHY == 'tak' ) {
         $WspolczynnikRabatu = (100 - $this->info['rabat_produktu']) / 100;
    }       
    
    // dla prefixu mnoznika - najpierw sprawdzi czy sa jakies cechy z mnoznikiem
    $sqlSpr = $GLOBALS['db']->open_query("SELECT DISTINCT pa.products_attributes_id FROM products_attributes pa WHERE pa.products_id = '" . $this->id_produktu . "' AND pa.price_prefix = '*'");
    
    // dla prefixu mnoznika - sprawdzi czy nie ma tez w cechach globalnie dla wszystkich produktow
    $sqlSprGlobal = $GLOBALS['db']->open_query("SELECT DISTINCT pov.products_options_values_id FROM products_options_values pov WHERE pov.global_price_prefix = '*'");
    
    if ((int)$GLOBALS['db']->ile_rekordow($sqlSpr) > 0 || (int)$GLOBALS['db']->ile_rekordow($sqlSprGlobal) > 0) {
      
        $CenaMnoznikaBrutto = 0;
        $CenaMnoznikaNetto = 0;
                                      
        for ($g = 0, $n = count($TablicaCechy); $g < $n; $g++) {
        
            $zapytanie = "SELECT DISTINCT pa.options_values_weight, 
                                          pa.options_values_price, 
                                          pa.options_values_tax, 
                                          pa.options_values_price_tax, 
                                          pa.price_prefix,
                                          po.products_options_value
                                     FROM products_attributes pa, products_options po
                                    WHERE pa.options_id = po.products_options_id AND 
                                          pa.products_id = '" . $this->id_produktu . "' AND 
                                          pa.options_id = '" . $TablicaCechy[$g]['cecha'] . "' AND 
                                          pa.options_values_id = '" . $TablicaCechy[$g]['wartosc'] . "'";
            
            $sql = $GLOBALS['db']->open_query($zapytanie);
            
            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
               
                $cecha = $sql->fetch_assoc();
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
                if ( $cecha['price_prefix'] == '*' ) {
                    //
                    $TmpBrutto = (($cenaPoZnizkachBrutto == 0) ? $this->info['cena_brutto_bez_formatowania'] : $cenaPoZnizkachBrutto);
                    $TmpNetto = (($cenaPoZnizkachNetto == 0) ? $this->info['cena_netto_bez_formatowania'] : $cenaPoZnizkachNetto);
                    //
                    $CenaMnoznikaBrutto += round(($TmpBrutto * $cecha['options_values_price_tax']), 2);
                    $CenaMnoznikaNetto += round(($TmpNetto * $cecha['options_values_price_tax']), 2);
                    //                
                }  

                unset($cecha);
                
            }
            unset($zapytanie);
            //
            $GLOBALS['db']->close_query($sql);         

        }  
        
        if ( $CenaMnoznikaBrutto > 0 ) {
             //
             $CenaCechBrutto = $CenaMnoznikaBrutto;
             $CenaCechNetto = $CenaMnoznikaNetto;
             //
        }
        
    }
    
    $GLOBALS['db']->close_query($sqlSpr);         
    
    // jezeli byly jakies cechy z mnoznikiem
    if ( $CenaCechBrutto > 0 && $CenaCechNetto > 0 ) {
         //
         $CenaCechBrutto -= (($cenaPoZnizkachBrutto == 0) ? $this->info['cena_brutto_bez_formatowania'] : $cenaPoZnizkachBrutto);
         $CenaCechNetto -= (($cenaPoZnizkachNetto == 0) ? $this->info['cena_netto_bez_formatowania'] : $cenaPoZnizkachNetto);
         //
    }
    
    for ($g = 0, $n = count($TablicaCechy); $g < $n; $g++) {
    
        $zapytanie = "SELECT DISTINCT pa.options_values_weight, 
                                      pa.options_values_price, 
                                      pa.options_values_tax, 
                                      pa.options_values_price_tax, 
                                      pa.price_prefix,
                                      po.products_options_value
                                 FROM products_attributes pa, products_options po
                                WHERE pa.options_id = po.products_options_id AND 
                                      pa.products_id = '" . $this->id_produktu . "' AND 
                                      pa.options_id = '" . $TablicaCechy[$g]['cecha'] . "' AND 
                                      pa.options_values_id = '" . $TablicaCechy[$g]['wartosc'] . "'";
        
        $sql = $GLOBALS['db']->open_query($zapytanie);
        
        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
          
            $cecha = $sql->fetch_assoc();  

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
            //
            // jezeli jest cecha kwotowa przeliczy waluty na domyslna
            if ( $cecha['products_options_value'] == 'kwota' ) {
                //
                // zwraca tablice z cenna netto i brutto
                $cenyCech = $GLOBALS['waluty']->FormatujCene($cecha['options_values_price_tax'], $cecha['options_values_price'], 0, $this->infoSql['products_currencies_id'], false );                
                //
                $cecha['options_values_price_tax'] = $cenyCech['brutto'];
                $cecha['options_values_price'] = $cenyCech['netto'];
                //
                unset($cenyCech);
                //
            }
            //
            if ( empty($cecha['price_prefix']) || $cecha['price_prefix'] == '+' ) {
                //
                if ( $cecha['products_options_value'] == 'kwota' ) {
                    //
                    // dodaje rabaty do produktu do wartosci cech
                    $CenaCechBrutto += round(($cecha['options_values_price_tax'] * $WspolczynnikRabatu), 2);
                    $CenaCechNetto += round(($cecha['options_values_price'] * $WspolczynnikRabatu), 2);
                    //
                }
                if ( $cecha['products_options_value'] == 'procent' ) {
                    //
                    $CenaCechBrutto += round(((($cenaPoZnizkachBrutto == 0) ? $this->info['cena_brutto_bez_formatowania'] : $cenaPoZnizkachBrutto) * ($cecha['options_values_price_tax'] / 100)), 2);
                    $CenaCechNetto += round(((($cenaPoZnizkachNetto == 0) ? $this->info['cena_netto_bez_formatowania'] : $cenaPoZnizkachNetto) * ($cecha['options_values_price_tax'] / 100)), 2);
                    //
                } 
                //              
            }        
            if ( $cecha['price_prefix'] == '-' ) {
                //
                if ( $cecha['products_options_value'] == 'kwota' ) {
                    //
                    // dodaje rabaty do produktu do wartosci cech
                    $CenaCechBrutto -= round(($cecha['options_values_price_tax'] * $WspolczynnikRabatu), 2);
                    $CenaCechNetto -= round(($cecha['options_values_price'] * $WspolczynnikRabatu), 2);
                    //
                }
                if ( $cecha['products_options_value'] == 'procent' ) {
                    //
                    $CenaCechBrutto -= round(((($cenaPoZnizkachBrutto == 0) ? $this->info['cena_brutto_bez_formatowania'] : $cenaPoZnizkachBrutto) * ($cecha['options_values_price_tax'] / 100)), 2);
                    $CenaCechNetto -= round(((($cenaPoZnizkachNetto == 0) ? $this->info['cena_netto_bez_formatowania'] : $cenaPoZnizkachNetto) * ($cecha['options_values_price_tax'] / 100)), 2);
                    //
                }                
                //
            }
          
            // dodawanie wagi
            
            // wartosci domyslne cech - waga cechy
            if ( $cecha['options_values_weight'] > 0 ) {
                 //
                 $WagaCechy += $cecha['options_values_weight'];
                 //
            } else if ( $GLOBALS['WartosciCech'][$TablicaCechy[$g]['wartosc']]['waga'] > 0 ) {
                 //
                 $WagaCechy += $GLOBALS['WartosciCech'][$TablicaCechy[$g]['wartosc']]['waga'];
                 //
            } 

            //
            unset($cecha);
            //
            
        }
        
        unset($zapytanie);
        
        $GLOBALS['db']->close_query($sql);         

    }
    
    unset($WspolczynnikRabatu);

    $TablicaCen = $GLOBALS['waluty']->FormatujCene( $CenaCechBrutto, $CenaCechNetto, 0, $_SESSION['domyslnaWaluta']['id'], false );

}
       
?>