<?php
chdir('../'); 

if (isset($_POST['id']) && (int)$_POST['id'] > 0) { 

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        if ( isset($_POST['cechy']) && !empty($_POST['cechy']) ) {
             //
             if ( strpos((string)$_POST['cechy'], 'x') > -1 ) {
                  //
                  if ( strpos((string)$_POST['cechy'], 'U') > -1 ) {
                       //
                       $_POST['cechy'] = substr((string)$_POST['cechy'], 0, strpos((string)$_POST['cechy'], 'U'));
                       //
                  }      
                  $SameCechy = substr((string)$_POST['cechy'], strpos((string)$_POST['cechy'], 'x') + 1);
                  //
                  
                  $GrafikaCechy = false;
                  
                  // szuka w bazie obrazka cechy
                  $zapytanieZdjecieCechy = "select products_stock_image from products_stock where products_id = '" . (int)$_POST['id'] . "' and products_stock_attributes = '" . str_replace('x',',', (string)$filtr->process($SameCechy)) . "'";
                  $sqlZdjecieCechy = $GLOBALS['db']->open_query($zapytanieZdjecieCechy);   

                  if ( (int)$GLOBALS['db']->ile_rekordow($sqlZdjecieCechy) > 0 ) { 
                  
                       $infoZdjecieCechy = $sqlZdjecieCechy->fetch_assoc(); 
                       //
                       if ( isset($infoZdjecieCechy['products_stock_image']) && $infoZdjecieCechy['products_stock_image'] != '' ) {  
                            //
                            $Produkt = new Produkt( (int)$_POST['id'] );
                            //                            
                            echo Funkcje::pokazObrazek($infoZdjecieCechy['products_stock_image'], $Produkt->fotoGlowne['opis_zdjecia'], ZDJECIE_LISTING_POWIEKSZENIE_SZEROKOSC, ZDJECIE_LISTING_POWIEKSZENIE_WYSOKOSC);
                            //
                            $GrafikaCechy = true;
                            //
                            unset($Produkt);   
                            //
                        }

                  }

                  $GLOBALS['db']->close_query($sqlZdjecieCechy);   
                  unset($infoZdjecieCechy, $zapytanieZdjecieCechy); 
                  
                  if ( $GrafikaCechy == false ) {
                    
                      // zdjecie cechy z products_attributes
                      $TabCech = explode('x',(string)$SameCechy);
                      //
                      foreach ( $TabCech as $TmpCecha ) {
                          //
                          $PodzielTmp = explode('-', (string)$TmpCecha);
                          //
                          if ( count($PodzielTmp) == 2 ) {
                               //
                               $zapytanieAtrributes = "select options_values_image from products_attributes where products_id = '" . (int)$_POST['id'] . "' and options_values_id = '" . (int)$PodzielTmp[1] . "' and options_values_image != ''";
                               $sqlAtrributes = $GLOBALS['db']->open_query($zapytanieAtrributes);   
                               //
                               if ((int)$GLOBALS['db']->ile_rekordow($sqlAtrributes) > 0) {
                                   // 
                                   $infoZdjecieCechy = $sqlAtrributes->fetch_assoc();
                                   //
                                   if ( isset($infoZdjecieCechy['options_values_image']) && $infoZdjecieCechy['options_values_image'] != '' ) {
                                        //
                                        $Produkt = new Produkt( (int)$_POST['id'] );
                                        //                    
                                        echo Funkcje::pokazObrazek($infoZdjecieCechy['options_values_image'], $Produkt->fotoGlowne['opis_zdjecia'], ZDJECIE_LISTING_POWIEKSZENIE_SZEROKOSC, ZDJECIE_LISTING_POWIEKSZENIE_WYSOKOSC);
                                        //
                                        $GrafikaCechy = true;
                                        //
                                        unset($Produkt);  
                                        //
                                   }
                                   //
                              }
                              //
                              $GLOBALS['db']->close_query($sqlAtrributes);   
                              unset($zapytanieAtrributes);                
                              //
                          }
                          //
                          unset($PodzielTmp);
                          //
                      }     
                  
                  }                  
                  
                  if ( $GrafikaCechy == false ) {
                       //
                       $Produkt = new Produkt( (int)$_POST['id'] );
                       //
                       echo Funkcje::pokazObrazek($Produkt->fotoGlowne['plik_zdjecia'], $Produkt->fotoGlowne['opis_zdjecia'], ZDJECIE_LISTING_POWIEKSZENIE_SZEROKOSC, ZDJECIE_LISTING_POWIEKSZENIE_WYSOKOSC);
                       //
                       unset($Produkt);   
                  }
                  
                  unset($GrafikaCechy);
                  //
             } else {
                  //
                  $Produkt = new Produkt( (int)$_POST['id'] );
                  //    
                  echo Funkcje::pokazObrazek($Produkt->fotoGlowne['plik_zdjecia'], $Produkt->fotoGlowne['opis_zdjecia'], ZDJECIE_LISTING_POWIEKSZENIE_SZEROKOSC, ZDJECIE_LISTING_POWIEKSZENIE_WYSOKOSC);
                  //
                  unset($Produkt);               
                  //
             }
             //
        } else {
             //
             $Produkt = new Produkt( (int)$_POST['id'] );
             //    
             if ( !isset($_POST['nr_zdjecia']) || (isset($_POST['nr_zdjecia']) && (int)$_POST['nr_zdjecia'] == 1) ) {
                  //
                  echo Funkcje::pokazObrazek($Produkt->fotoGlowne['plik_zdjecia'], $Produkt->fotoGlowne['opis_zdjecia'], ZDJECIE_LISTING_POWIEKSZENIE_SZEROKOSC, ZDJECIE_LISTING_POWIEKSZENIE_WYSOKOSC);
                  //
             } else {
                  //
                  // zdjecie podmiana po najechaniu na zdjecie
                  $DrugieZdjecieTablica = $Produkt->ProduktDodatkoweZdjecia();
                  //
                  if ( count($DrugieZdjecieTablica) > 0 ) {
                       // 
                       echo Funkcje::pokazObrazek($DrugieZdjecieTablica[0]['zdjecie'], $Produkt->fotoGlowne['opis_zdjecia'], ZDJECIE_LISTING_POWIEKSZENIE_SZEROKOSC, ZDJECIE_LISTING_POWIEKSZENIE_WYSOKOSC);
                       //
                  } else {
                       //
                       echo Funkcje::pokazObrazek($Produkt->fotoGlowne['plik_zdjecia'], $Produkt->fotoGlowne['opis_zdjecia'], ZDJECIE_LISTING_POWIEKSZENIE_SZEROKOSC, ZDJECIE_LISTING_POWIEKSZENIE_WYSOKOSC);
                       //
                  }
                  //
                  unset($DrugieZdjecieTablica);
                  //
             }
             //
             unset($Produkt);
        }
  
    }
    
}

?>