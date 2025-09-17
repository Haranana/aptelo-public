<?php

if ( isset($pobierzFunkcje) ) {

    $zapytanie = "SELECT products_link_name, products_link_url, products_link_description FROM products_link WHERE products_id = '" . $this->id_produktu . "' AND language_id = '" . $this->jezykDomyslnyId . "' ORDER BY products_link_id";    
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
    
        while ($info = $sql->fetch_assoc()) {
            //
            if ( !empty($info['products_link_name']) && !empty($info['products_link_url']) ) {
                //
                $this->Linki[] = array( 'nazwa' => $info['products_link_name'],
                                        'opis'  => $info['products_link_description'],
                                        'link'  => $info['products_link_url'] );
                //
            }            
        }
        
        unset($info);
        
    }
    
    $GLOBALS['db']->close_query($sql); 
    
    unset($zapytanie);
        
}
       
?>