<?php

if ( isset($pobierzFunkcje) ) {

    $TablicaProduktGwarancja = array();
    //
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('ProduktProduktGwarancja_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);      

    if ( !$WynikCache && !is_array($WynikCache) ) {
    
        $zapytanie = "select wp.products_warranty_id, wpd.products_warranty_name, wpd.products_warranty_link from products_warranty wp, products_warranty_description wpd where wp.products_warranty_id = wpd.products_warranty_id and wpd.language_id = '" . $this->jezykDomyslnyId . "'";
        $sqls = $GLOBALS['db']->open_query($zapytanie);
        //
        if ( (int)$GLOBALS['db']->ile_rekordow($sqls) > 0 ) {

            while ($infs = $sqls->fetch_assoc()) {
                $TablicaProduktGwarancja[$infs['products_warranty_id']] = array( 'nazwa' => $infs['products_warranty_name'],
                                                                                 'link' => $infs['products_warranty_link'] );
            }
            
            unset($infs);
            
        }
        $GLOBALS['db']->close_query($sqls);    
        unset($zapytanie);            
        
        $GLOBALS['cache']->zapisz('ProduktProduktGwarancja_' . $_SESSION['domyslnyJezyk']['kod'], $TablicaProduktGwarancja, CACHE_INNE);
        
      } else {

        $TablicaProduktGwarancja = $WynikCache;     
        
    }            
        
    if ( isset( $TablicaProduktGwarancja[$this->infoSql['products_warranty_products_id']] ) ) {
         //
         $this->gwarancja = (( $TablicaProduktGwarancja[$this->infoSql['products_warranty_products_id']]['link'] != '' ) ? '<a href="' . $TablicaProduktGwarancja[$this->infoSql['products_warranty_products_id']]['link'] . '">' . $TablicaProduktGwarancja[$this->infoSql['products_warranty_products_id']]['nazwa'] . '</a>' : $TablicaProduktGwarancja[$this->infoSql['products_warranty_products_id']]['nazwa'] );         
         //
    }
    
    unset($TablicaProduktGwarancja, $WynikCache);  
        
}
       
?>