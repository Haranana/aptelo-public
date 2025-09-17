<?php

if ( isset($pobierzFunkcje) ) {

    $TablicaCzasWysylki = array();
    //
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('ProduktCzasWysylki_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);      

    if ( !$WynikCache && !is_array($WynikCache) ) { 

        $zapytanie = "select s.products_shipping_time_id, s.products_shipping_time_day, sd.products_shipping_time_name from products_shipping_time s, products_shipping_time_description sd where s.products_shipping_time_id = sd.products_shipping_time_id and sd.language_id = '" . $this->jezykDomyslnyId . "' order by s.products_shipping_time_day";
        $sqls = $GLOBALS['db']->open_query($zapytanie);
        
        if ((int)$GLOBALS['db']->ile_rekordow($sqls) > 0) {
          
            while ($infs = $sqls->fetch_assoc()) {
              
                $TablicaCzasWysylki[$infs['products_shipping_time_id']] = array( 'nazwa' => $infs['products_shipping_time_name'],
                                                                                 'ilosc_dni' => $infs['products_shipping_time_day'] );
                                                                                 
            }
            
            unset($infs);
            
        }
        
        $GLOBALS['db']->close_query($sqls);  
        unset($zapytanie);
        
        $GLOBALS['cache']->zapisz('ProduktCzasWysylki_' . $_SESSION['domyslnyJezyk']['kod'], $TablicaCzasWysylki, CACHE_INNE);
        
      } else {

        $TablicaCzasWysylki = $WynikCache;     
        
    }
    
    $WysylkaTablica = array();
    
    if ( $id_wysylki > 0 ) {
      
        if ( isset( $TablicaCzasWysylki[$id_wysylki] ) ) {
             //
             $WysylkaTablica = array( 'nazwa' => $TablicaCzasWysylki[$id_wysylki]['nazwa'], 'dni' => $TablicaCzasWysylki[$id_wysylki]['ilosc_dni'] ); 
             //
        }   

    } else {        
    
        if ( isset( $TablicaCzasWysylki[$this->infoSql['products_shipping_time_id']] ) ) {
             //
             $this->czas_wysylki = $TablicaCzasWysylki[$this->infoSql['products_shipping_time_id']]['nazwa'];
             $this->czas_wysylki_dni = $TablicaCzasWysylki[$this->infoSql['products_shipping_time_id']]['ilosc_dni'];             
             //
             $WysylkaTablica = array( 'nazwa' => $TablicaCzasWysylki[$this->infoSql['products_shipping_time_id']]['nazwa'], 'dni' => $TablicaCzasWysylki[$this->infoSql['products_shipping_time_id']]['ilosc_dni'] ); 
             //
        }
    
    }    
    
    unset($TablicaCzasWysylki, $WynikCache);
   
}
       
?>