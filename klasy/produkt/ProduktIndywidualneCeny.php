<?php

if ( isset($pobierzFunkcje) ) {

    // indywidualne ceny produktow
    if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
         //
         // sprawdzi czy sa jakies indywidualne ceny dla tego produktu
         $WynikCacheCeny = $GLOBALS['cache']->odczytaj('Produkt_Ceny', CACHE_INNE);
         //
         if ( !$WynikCacheCeny && !is_array($WynikCacheCeny) ) {
              //
              $zapisz = array();
              //
              $zapytanieCeny = "SELECT cp.cp_products_id as id_produktu, 
                                       cp.cp_groups_id as id_grupy, 
                                       cp.cp_customers_id as id_klienta, 
                                       cp.cp_price as cena_netto, 
                                       cp.cp_price_tax as cena_brutto, 
                                       cp.cp_tax as podatek_vat
                                  FROM customers_price cp
                             LEFT JOIN products_description pd on pd.products_id = cp.cp_products_id and pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'                                        
                              ORDER BY pd.products_name, id_klienta, cena_brutto";
                              
              $sqlCeny = $GLOBALS['db']->open_query($zapytanieCeny);
              //
              if ( (int)$GLOBALS['db']->ile_rekordow($sqlCeny) > 0 ) {

                  while ( $inft = $sqlCeny->fetch_assoc() ) {
                      //
                      $zapisz[] = $inft;
                      //
                  }
                  
                  unset($inft);
                  
              }
              //
              $GLOBALS['cache']->zapisz('Produkt_Ceny', $zapisz, CACHE_INNE);
              //
              $GLOBALS['db']->close_query($sqlCeny);
              //
              $WynikCacheCeny = $zapisz;
              //
              unset($zapytanieCeny, $zapisz);
              //
         }                 
         //
         $TablicaCen = array_reverse($WynikCacheCeny);
         //
         foreach ( $TablicaCen as $CenyIndProduktu ) {
            //
            if ( $CenyIndProduktu['id_produktu'] == $this->id_produktu && ($CenyIndProduktu['id_klienta'] == (int)$_SESSION['customer_id'] || $CenyIndProduktu['id_grupy'] == (int)$_SESSION['customers_groups_id']) ) {
                 //
                 // ceny netto
                 if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' ) {
                     //
                     $CenyIndProduktu['cena_brutto'] = $CenyIndProduktu['cena_netto'];
                     //
                 }
                 //                 
                 $this->CenaIndywidualna = true;
                 //
                 $this->ikonki['cena_specjalna'] = '1';
                 // jezeli ma byc wyswietlane jako promocja
                 // $this->infoSql['specials_status'] = 1;
                 //
                 // jezeli produkt za punkty to wylaczy
                 $this->infoSql['products_points_only'] = '0';                         
                 //
                 $this->infoSql['products_old_price'] = $this->infoSql['products_price_tax'];
                 $this->infoSql['products_price'] = $CenyIndProduktu['cena_netto'];
                 $this->infoSql['products_price_tax'] = $CenyIndProduktu['cena_brutto'];
                 //
                 break;
            }
            //
         }
         //
         unset($TablicaCen, $WynikCacheCeny);
         //
         
         if ( $this->CenaIndywidualna == true ) {
              $this->ikonki['cena_specjalna'] = '1';
         }         
         
    }

}
    
?>