<?php

if ( isset($pobierzFunkcje) ) {

    // szuka czy dana kombinacja cech nie ma unikalnego nr katalogowego
    $NrKatalogowyCechy = $this->info['nr_katalogowy'];
    
    // wysylka produktu
    $CzasWysylkiCechy = $this->ProduktCzasWysylki();
    
    // kod ean
    $KodEan = $this->info['ean'];
    
    // zdjecie 
    $ZdjecieCechy = $this->infoSql['products_image'];
    
    if ( !empty($cechy) && strpos((string)$cechy, '-') > -1 && strpos((string)$cechy, '-gratis') == false ) {
      
        if ( KOSZYK_SPOSOB_DODAWANIA == 'tak' ) {
             //
             $cechy = substr((string)$cechy, 0, strpos((string)$cechy, 'U'));
             //
        }
        
        // zdjecie cechy z products_attributes
        $tab_cech = explode('x',(string)$cechy);
        //
        foreach ( $tab_cech as $tmp_cecha ) {
            //
            $podziel_tmp = explode('-', (string)$tmp_cecha);
            //
            if ( count($podziel_tmp) == 2 ) {
                 //
                 $zapytanie_attr = "select options_values_image from products_attributes where products_id = '" . $this->info['id'] . "' and options_values_id = '" . (int)$podziel_tmp[1] . "' and options_values_image != ''";
                 $sql_attr = $GLOBALS['db']->open_query($zapytanie_attr);   
                 //
                 if ((int)$GLOBALS['db']->ile_rekordow($sql_attr) > 0) {
                     // 
                     $infa = $sql_attr->fetch_assoc();
                     //
                     if ( isset($infa['options_values_image']) && $infa['options_values_image'] != '' && file_exists(KATALOG_ZDJEC . '/' . $infa['options_values_image']) ) {
                          //                    
                          $ZdjecieCechy = $infa['options_values_image'];
                          //
                     }
                     //
                }
                //
                $GLOBALS['db']->close_query($sql_attr);   
                unset($zapytanie_attr);                
                //
            }
            //
            unset($podziel_tmp);
            //
        }        
    
        // dane z products_stock
        $zapytanie_cechy = "SELECT products_stock_model, products_stock_ean, products_stock_shipping_time_id,	products_stock_image,	products_stock_quantity FROM products_stock WHERE products_stock_attributes = '" . str_replace('x', ',', (string)$cechy) . "' and products_id = '" . $this->info['id'] . "'";
        $sql_nr_kat_cechy = $GLOBALS['db']->open_query($zapytanie_cechy);
        //
        if ((int)$GLOBALS['db']->ile_rekordow($sql_nr_kat_cechy) > 0) {
            //
            $info_dane_cechy = $sql_nr_kat_cechy->fetch_assoc();
            //
            // nr katalogowy
            if (!empty($info_dane_cechy['products_stock_model'])) {
                $NrKatalogowyCechy = $info_dane_cechy['products_stock_model'];
            }
            //
            // czas wysylki
            if (!empty($info_dane_cechy['products_stock_shipping_time_id'])) {
                $CzasWysylkiCechy = $this->ProduktCzasWysylki( $info_dane_cechy['products_stock_shipping_time_id'] );
            }            
            // jezeli jest stan = 0 i id czasu wysylki dla stanu 0
            if ( (float)$this->infoSql['products_quantity'] < 0.01 && (int)$this->infoSql['products_shipping_time_zero_quantity_id'] > 0 ) {
                 //
                 $CzasWysylkiCechy = $this->ProduktCzasWysylki( $this->infoSql['products_shipping_time_zero_quantity_id'] );
                 //
            }              
            // jezeli jest magazyn cech
            if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && CECHY_MAGAZYN == 'tak' && $this->infoSql['products_control_storage'] > 0 ) {
                 //
                 if ( (float)$info_dane_cechy['products_stock_quantity'] < 0.01 && (int)$this->infoSql['products_shipping_time_zero_quantity_id'] > 0 ) {
                      //
                      $CzasWysylkiCechy = $this->ProduktCzasWysylki( $this->infoSql['products_shipping_time_zero_quantity_id'] );
                      //
                 }                   
                 //
            }            
            //
            // kod ean
            if (!empty($info_dane_cechy['products_stock_ean'])) {
                $KodEan = $info_dane_cechy['products_stock_ean'];
            } 
            //
            // zdjecie cechy
            if (!empty($info_dane_cechy['products_stock_image'])) {
                $ZdjecieCechy = $info_dane_cechy['products_stock_image'];
            }                
            //
            unset($info_dane_cechy);
        }   
        //
        $GLOBALS['db']->close_query($sql_nr_kat_cechy);  
        //   
    
    }

}  

?>