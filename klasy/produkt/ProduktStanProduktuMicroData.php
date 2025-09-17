<?php

if ( isset($pobierzFunkcje) ) {

    $StanProduktuMicroData = array('new');

    if ( $this->infoSql['products_condition_products_id'] != '0' ) {
        //
        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('ProduktMicroDane_' . $this->id_produktu, CACHE_INNE);

        if ( !$WynikCache && !is_array($WynikCache) ) {
        
            $zapytanie = "SELECT products_condition_id, products_condition_googleshopping FROM products_condition WHERE products_condition_id = '".$this->infoSql['products_condition_products_id']."'";
            $sqls = $GLOBALS['db']->open_query($zapytanie);
            
            if ((int)$GLOBALS['db']->ile_rekordow($sqls) > 0) {
                //
                $infs = $sqls->fetch_assoc();            
                $StanProduktuMicroData = array($infs['products_condition_googleshopping']);
                unset($infs);    
                //
            }

            $GLOBALS['db']->close_query($sqls);    
            unset($zapytanie);            
            
            $GLOBALS['cache']->zapisz('ProduktMicroDane_' . $this->id_produktu . '', $StanProduktuMicroData, CACHE_INNE);
           
        } else {

            $StanProduktuMicroData = $WynikCache;     
            
        }

        unset($WynikCache);

    } else {

        $StanProduktuMicroData = array('new');

    }
    
    $StanProduktuMicroData = implode('', (array)$StanProduktuMicroData);

    switch($StanProduktuMicroData) {
        case 'used':   $this->stan_produktu_microdata = 'UsedCondition'; $this->stan_produktu_opengraph = 'used'; break;
        case 'refurbished':   $this->stan_produktu_microdata = 'RefurbishedCondition'; $this->stan_produktu_opengraph = 'refurbished';  break;
        case 'new':   $this->stan_produktu_microdata = 'NewCondition'; $this->stan_produktu_opengraph = 'new';  break;
        default:    $this->stan_produktu_microdata = 'NewCondition'; $this->stan_produktu_opengraph = 'new';  break;
    }
    //
    
    unset($StanProduktuMicroData);
        
}
       
?>