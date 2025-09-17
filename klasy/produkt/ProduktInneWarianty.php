<?php

if ( isset($pobierzFunkcje) ) {
  
    $WynikCache = $GLOBALS['cache']->odczytaj('Produkt_Id_' . $this->id_produktu . '_innewarianty' . '_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);      

    if ( !$WynikCache && !is_array($WynikCache) ) {  

         if ( trim((string)$this->infoSql['products_other_variant_text']) != '' ) {
           
              $warunek_wariant = '';
              
              switch($this->infoSql['products_other_variant_range']) {
                  case 'ean':   
                      $warunek_wariant = 'p.products_ean';
                      break;
                  case 'nazwa_produktu':   
                      $warunek_wariant = 'pd.products_name';
                      break;    
                  case 'id_zewnetrzne':   
                      $warunek_wariant = 'p.products_id_private';
                      break;                       
                  case 'nr_katalogowy':   
                      $warunek_wariant = 'p.products_model';
                      break;
                  case 'kod_producenta':
                      $warunek_wariant = 'p.products_man_code';
                      break;
                  case 'nr_referencyjny_1':
                      $warunek_wariant = 'p.products_reference_number_1';
                      break;
                  case 'nr_referencyjny_2':
                      $warunek_wariant = 'p.products_reference_number_2';
                      break;
                  case 'nr_referencyjny_3':
                      $warunek_wariant = 'p.products_reference_number_3';
                      break;
                  case 'nr_referencyjny_4':
                      $warunek_wariant = 'p.products_reference_number_4';
                      break;
                  case 'nr_referencyjny_5':
                      $warunek_wariant = 'p.products_reference_number_5';
                      break;                      
              }              
              
              $WariantyProduktu = array();
              
              $WariantyProduktu[ $this->infoSql['products_id'] ] = array( 'id' => $this->infoSql['products_id'] );
         
              if ( $this->infoSql['products_other_variant_method'] == 'dokladnie' ) {
                   //
                   $zapytanie_warianty = "select distinct p.products_id 
                                                     from products p 
                                                left join products_description pd ON pd.products_id = p.products_id and pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                                                    where " . $warunek_wariant . " = '" . addslashes($this->infoSql['products_other_variant_text']) . "' 
                                                      and p.products_status = '1' 
                                                      and p.listing_status = '0' " . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' and p.products_quantity > 0 ' : '' ) . "";
                   //
              } else {
                   //
                   $zapytanie_warianty = "select distinct p.products_id 
                                                     from products p 
                                                left join products_description pd ON pd.products_id = p.products_id and pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'                                                     
                                                    where " . $warunek_wariant . " LIKE '%" . str_replace('%', '\%', str_replace('_', '\_', addslashes($this->infoSql['products_other_variant_text']))) . "%' 
                                                     and p.products_status = '1' 
                                                     and p.listing_status = '0' " . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' and p.products_quantity > 0 ' : '' ) . "";
                   //
              }

              $sqls_tmp = $GLOBALS['db']->open_query($zapytanie_warianty);
              //
              if ( (int)$GLOBALS['db']->ile_rekordow($sqls_tmp) > 0 ) {
                   //
                   while ($wariant_tmp = $sqls_tmp->fetch_assoc()) {                       
                       //
                       $WariantyProduktu[ $wariant_tmp['products_id'] ] = array( 'id' => $wariant_tmp['products_id'] );
                       //
                   }
                   //
                   unset($wariant_tmp);
                   //
                   $this->InneWarianty = $WariantyProduktu;
                   //
              }
            
              $GLOBALS['db']->close_query($sqls_tmp); 
              unset($zapytanie_warianty);                            
              //            
              unset($WariantyProduktu);
              //
        }
        
        $GLOBALS['cache']->zapisz('Produkt_Id_' . $this->id_produktu . '_innewarianty' . '_' . $_SESSION['domyslnyJezyk']['kod'], $this->InneWarianty, CACHE_PRODUKTY);
        
      } else {

        $this->InneWarianty = $WynikCache;     
        
    } 

    unset($InneWarianty);

}
    
?>