<?php

if ( isset($pobierzFunkcje) ) {
    
    $DodatkoweZdjecia = array();
    
    $WynikCache = $GLOBALS['cache']->odczytaj('Produkt_Id_' . $this->id_produktu . '_dodatkowe_zdjecia', CACHE_INNE);    
    
    if ( !$WynikCache && !is_array($WynikCache) ) {
    
        $zapytanieFoto = "SELECT DISTINCT images_description, popup_images FROM additional_images WHERE products_id = '" . $this->id_produktu . "' order by sort_order";
        $sql = $GLOBALS['db']->open_query($zapytanieFoto);

        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        
            while ($foto = $sql->fetch_assoc()) {
                $DodatkoweZdjecia[] = array( 'zdjecie' => $foto['popup_images'], 'alt' => $foto['images_description'] );
            }
            
            unset($info);
            
        }
        
        $GLOBALS['cache']->zapisz('Produkt_Id_' . $this->id_produktu . '_dodatkowe_zdjecia', $DodatkoweZdjecia, CACHE_INNE);
        
        $GLOBALS['db']->close_query($sql); 
        unset($zapytanieFoto); 
        
    } else {
      
        $DodatkoweZdjecia = $WynikCache;
      
    }

}
       
?>