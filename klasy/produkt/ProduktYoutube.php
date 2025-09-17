<?php

if ( isset($pobierzFunkcje) ) {

    $zapytanie = "SELECT * FROM products_youtube WHERE products_id = '" . $this->id_produktu . "' AND language_id = '" . $this->jezykDomyslnyId . "' ORDER BY products_film_id";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

        while ($info = $sql->fetch_assoc()) {
            //
            if ( !empty($info['products_film_url']) && !empty($info['products_film_url']) ) {
                //
                $SzerokoscFilmu = (((int)$info['products_film_width'] > 0) ? (int)$info['products_film_width'] : 640);
                $WysokoscFilmu = (((int)$info['products_film_height'] > 0) ? (int)$info['products_film_height'] : 360);
                //
                $this->Youtube[] = array( 'id_film'   => $this->id_produktu . '_' . $info['products_film_id'],
                                          'nazwa'     => $info['products_film_name'],
                                          'opis'      => $info['products_film_description'],
                                          'film'      => $info['products_film_url'],
                                          'szerokosc' => $SzerokoscFilmu,
                                          'wysokosc'  => $WysokoscFilmu);
                //
                unset($SzerokoscFilmu, $WysokoscFilmu);
            }            
        }
        
        unset($info);
        
    }
    
    $GLOBALS['db']->close_query($sql); 

    unset($zapytanie);
     
}

?>