<?php

if ( isset($pobierzFunkcje) ) {
  
    $TablicaDodatkoweOpisy = array();
    
    $WynikCache = $GLOBALS['cache']->odczytaj('Produkt_Id_' . $this->id_produktu . '_dodatkowe_opisy_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);      

    if ( !$WynikCache && !is_array($WynikCache) ) {  

         $zapytanie = "SELECT products_info_description_1, products_info_description_2 FROM products_description_additional WHERE products_id = '" . $this->id_produktu . "' AND language_id = '" . $this->jezykDomyslnyId . "'";    
         $sql = $GLOBALS['db']->open_query($zapytanie);
        
         if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        
            while ($info = $sql->fetch_assoc()) {

                if ( !empty($info['products_info_description_1']) ) {
                     //
                     $TablicaDodatkoweOpisy[1] = $info['products_info_description_1'];
                     //
                }

                if ( !empty($info['products_info_description_2']) ) {
                     //
                     $TablicaDodatkoweOpisy[2] = $info['products_info_description_2'];
                     //
                }
          
            }
            
            unset($info);
            
         }
         
         $GLOBALS['db']->close_query($sql); 
         unset($zapytanie);
         
         $GLOBALS['cache']->zapisz('Produkt_Id_' . $this->id_produktu . '_dodatkowe_opisy_' . $_SESSION['domyslnyJezyk']['kod'], $TablicaDodatkoweOpisy, CACHE_PRODUKTY);
         
      } else {

        $TablicaDodatkoweOpisy = $WynikCache;     
        
    }          

}
       
?>