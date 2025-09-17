<?php

if ( isset($pobierzFunkcje) ) {

    $TablicaZestawowProduktu = array();

    if ( $this->infoSql['products_set'] != 1 ) {
      
        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('ZestawyProduktow', CACHE_INNE);     

        $TablicaZestawow = array();

        if ( !$WynikCache && !is_array($WynikCache) ) { 
         
            $zapytanie = "SELECT products_id, products_price, products_price_tax, products_currencies_id, products_set_products FROM products WHERE products_set_products != '' and products_set_products is not NULL";
            $sql = $GLOBALS['db']->open_query($zapytanie);
            
            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
              
                while ($info = $sql->fetch_assoc()) {
                  
                    $TablicaZestawow[] = $info;
                  
                }
                
            }
            
            $GLOBALS['db']->close_query($sql); 
            unset($zapytanie);
            
            $GLOBALS['cache']->zapisz('ZestawyProduktow', $TablicaZestawow, CACHE_INNE);
    
        } else {
          
            $TablicaZestawow = $WynikCache;  
          
        }
        
        foreach ( $TablicaZestawow as $info ) {
                  
             $DodajZestaw = false;
          
             $JakieId = unserialize($info['products_set_products']);
             
             foreach ( $JakieId as $id => $dane ) {
               
                  if ( $id == $this->infoSql['products_id'] ) {
                      
                       $DodajZestaw = true;
                       
                  }
               
             }
                     
             if ( $DodajZestaw == true ) {

                  $ProduktTmp = new Produkt( $info['products_id'] );
                  
                  if ($ProduktTmp->CzyJestProdukt == true) {
          
                      $tablica_produktu = array( 'cena_netto' => $info['products_price'],
                                                 'cena_brutto' => $info['products_price_tax'],
                                                 'dane_serialize' => $info['products_set_products'],
                                                 'waluta_produktu' => $info['products_currencies_id'] );
                                          
                      // zmiana kolejnosci - wyswietlany produkt na poczatku
                      
                      $TablicaKoncowa = array();
                      
                      $TablicaTmp = $this->ProduktZestawy($tablica_produktu);
                      
                      $TablicaKoncowa['taniej_kwota'] = $TablicaTmp['taniej_kwota'];
                      $TablicaKoncowa['taniej_wartosc'] = $TablicaTmp['taniej_wartosc'];
                      $TablicaKoncowa['cena'] = $ProduktTmp->info['cena'];
                      $TablicaKoncowa['link'] = $ProduktTmp->info['link'];
                      $TablicaKoncowa['adres_seo'] = $ProduktTmp->info['adres_seo'];

                      foreach ( $TablicaTmp['produkty'] as $klucz => $dane ) {
                           //
                           if ( $klucz == $this->infoSql['products_id'] ) {
                                //
                                $TablicaKoncowa['produkty'][$klucz] = $dane;
                                //
                           }
                           //
                      }
                      
                      foreach ( $TablicaTmp['produkty'] as $klucz => $dane ) {
                           //
                           if ( $klucz != $this->infoSql['products_id'] ) {
                                //
                                $TablicaKoncowa['produkty'][$klucz] = $dane;
                                //
                           }
                           //
                      }
                      
                      $TablicaZestawowProduktu[ $info['products_id'] ] = $TablicaKoncowa;
                      
                      unset($tablica_produktu, $TablicaTmp, $TablicaKoncowa);
                      
                  }
                 
                  unset($ProduktTmp);
                  
             }
             
             unset($DodajZestaw);

        }
        
    }
    
    return $TablicaZestawowProduktu;
    
}
       
?>