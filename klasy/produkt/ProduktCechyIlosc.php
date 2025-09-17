<?php

if ( isset($pobierzFunkcje) ) {

    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('Produkt_Id_' . $this->id_produktu . '_cechy', CACHE_PRODUKTY);
    
    if ( !$WynikCache ) {
        //
        $zapytanie = "SELECT DISTINCT options_id FROM products_attributes WHERE products_id = '" . $this->id_produktu . "'";      
        $sql = $GLOBALS['db']->open_query($zapytanie);
        //
        // obejscie zeby 0 nie bylo tozsame z false
        $zapisz = (int)$GLOBALS['db']->ile_rekordow($sql);
        if ( $zapisz == 0 ) {
             $zapisz = 'xx';
        }
        //
        $GLOBALS['cache']->zapisz('Produkt_Id_' . $this->id_produktu . '_cechy', $zapisz, CACHE_PRODUKTY);
        $this->cechyIlosc = (int)$GLOBALS['db']->ile_rekordow($sql);
        //
        $GLOBALS['db']->close_query($sql);
        //
        unset($zapisz, $zapytanie);
        //
        
    } else {
    
        $this->cechyIlosc = (int)$WynikCache;
    } 
    
}
       
?>