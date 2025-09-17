<?php

if ( isset($pobierzFunkcje) ) {

    $zapytanie = "SELECT products_mp3_id, products_mp3_name, products_mp3_file FROM products_mp3 WHERE products_id = '" . $this->id_produktu . "' ORDER BY products_mp3_id";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {

        while ($info = $sql->fetch_assoc()) {
            //
            if ( !empty($info['products_mp3_name']) && !empty($info['products_mp3_file']) ) {
                //
                $this->Mp3[] = array( 'id_mp3' => $this->id_produktu . '_' . $info['products_mp3_id'],
                                      'nazwa'  => $info['products_mp3_name'],
                                      'plik'   => $info['products_mp3_file']);
                // 
            }            
        }
        
        unset($info);
        
    }
    
    $GLOBALS['db']->close_query($sql); 

    unset($zapytanie);

}
    
?>