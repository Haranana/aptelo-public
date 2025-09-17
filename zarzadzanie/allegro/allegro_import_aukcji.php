<?php
chdir('../');

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr() && Funkcje::SprawdzAktywneAllegro()) { 

    // POBRANIE DANYCH DOTYCZACYCH ILOSCI PRODUKTOW W SKLEPIE - START
    
    $czas_start = explode(' ', microtime());

    $zapytanie_tmp = "SELECT a.products_id, a.auction_id, a.products_stock_attributes, p.products_quantity FROM allegro_auctions a
                   LEFT JOIN products p ON p.products_id = a.products_id WHERE a.archiwum_allegro != '1'";
                   
    $sql_tmp = $db->open_query($zapytanie_tmp);

    while ( $info_tmp = $sql_tmp->fetch_assoc() ) {

        if ( isset($info_tmp['products_stock_attributes']) && $info_tmp['products_stock_attributes'] != '' && CECHY_MAGAZYN == 'tak' ) {

            $ilosc_magazyn = 0;

            $cechy_produktu = str_replace('x', ',' , (string)$info_tmp['products_stock_attributes']);
                      
            $zapytanie_ilosc_cechy = "SELECT products_stock_quantity 
                                        FROM products_stock
                                        WHERE products_id = '" . (int)$info_tmp['products_id']. "' 
                                        AND products_stock_attributes = '".$cechy_produktu."'";
                                                   
            $sql_ilosc_cechy = $db->open_query($zapytanie_ilosc_cechy);

            if ((int)$db->ile_rekordow($sql_ilosc_cechy) > 0) {
                      
                $info_ilosc_cechy = $sql_ilosc_cechy->fetch_assoc();
                $ilosc_magazyn = $info_ilosc_cechy['products_stock_quantity'];
                          
            }
                      
            $db->close_query($sql_ilosc_cechy);
                      
            unset($zapytanie_ilosc_cechy, $info_ilosc_cechy, $cechy_produktu);
                      
            $TablicaIlosciProduktowSklep[$info_tmp['auction_id']] = $ilosc_magazyn;

        } else {

            $TablicaIlosciProduktowSklep[$info_tmp['auction_id']] = $info_tmp['products_quantity'];

        }
    }
    $db->close_query($sql_tmp);
    unset($zapytanie_tmp, $info_tmp);
    
    // POBRANIE DANYCH DOTYCZACYCH ILOSCI PRODUKTOW W SKLEPIE - KONIEC

    $wynikDoAjaxa = '';
    $TablicaAukcji = array();
    $IloscPrzetworzonych = 0;
    $Modification = time();

    $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );

    $TablicaAukcjiAllegro = $AllegroRest->TablicaWszystkichAukcjiAllegro( $_POST['limit'], $_POST['offset'] );

    $TablicaAukcjiSklep = $AllegroRest->TablicaWszystkichAukcjiSklep();

    foreach ( $TablicaAukcjiAllegro as $PrzetwarzanaAukcja ) {
      
        $wylaczona = '';

        if ( in_array((string)$PrzetwarzanaAukcja->id, (array)$TablicaAukcjiSklep) ) {

            $DataStart = '';
            $DataEnd = '1970-01-01 01:00:00';

            if ( isset($PrzetwarzanaAukcja->publication->startingAt) && !empty($PrzetwarzanaAukcja->publication->startingAt) ) {
                $DataStart = $PrzetwarzanaAukcja->publication->startingAt;
            }
            if ( isset($PrzetwarzanaAukcja->publication->startedAt) && !empty($PrzetwarzanaAukcja->publication->startedAt) ) {
                $DataStart = $PrzetwarzanaAukcja->publication->startedAt;
            }

            if ( isset($PrzetwarzanaAukcja->publication->endingAt) && !empty($PrzetwarzanaAukcja->publication->endingAt) ) {
                $DataEnd = $PrzetwarzanaAukcja->publication->endingAt;
            }
            if ( isset($PrzetwarzanaAukcja->publication->endedAt) && !empty($PrzetwarzanaAukcja->publication->endedAt) ) {
                $DataEnd = $PrzetwarzanaAukcja->publication->endedAt;
            }

            $pola = array(
                    array('products_name',$PrzetwarzanaAukcja->name),
                    array('allegro_category',(int)$PrzetwarzanaAukcja->category->id),
                    array('auction_price',(float)$PrzetwarzanaAukcja->sellingMode->price->amount),
                    array('auction_quantity',(int)$PrzetwarzanaAukcja->stock->available),
                    array('auction_status',$PrzetwarzanaAukcja->publication->status),
                    array('products_buy_now_price',(float)$PrzetwarzanaAukcja->sellingMode->price->amount),
                    array('auction_hits',(int)$PrzetwarzanaAukcja->stats->visitsCount),
                    array('auction_bids',(int)$PrzetwarzanaAukcja->saleInfo->biddersCount),
                    array('products_sold',(int)$PrzetwarzanaAukcja->stock->sold),
                    array('auction_watching',(int)$PrzetwarzanaAukcja->stats->watchersCount),
                    array('auction_uuid',''),
                    array('synchronization','1'),
                    array('auction_last_modification',$Modification)
            );

            $db->update_query('allegro_auctions' , $pola, " auction_id = '".$PrzetwarzanaAukcja->id."'");
            unset($pola);

            if ( $DataStart != '' ) {
                unset($pola);
                $pola = array(
                        array('auction_date_start',date('Y-m-d G:i:s',FunkcjeWlasnePHP::my_strtotime($DataStart)))
                );
                $db->update_query('allegro_auctions' , $pola, " auction_id = '".$PrzetwarzanaAukcja->id."'");
            }
            if ( $DataEnd != '' ) {
                unset($pola);
                $pola = array(
                        array('auction_date_end',date('Y-m-d G:i:s',FunkcjeWlasnePHP::my_strtotime($DataEnd)))
                );
                $db->update_query('allegro_auctions' , $pola, " auction_id = '".$PrzetwarzanaAukcja->id."'");
            }

            // AKTUALIZACJA ILOSCI WYSTAWIONYCH PRODUKTOW NA ALLEGRO - START

            if ( $PrzetwarzanaAukcja->publication->status == 'ACTIVE' || $PrzetwarzanaAukcja->publication->status == 'ACTIVATING' ) {

                if ( $TablicaIlosciProduktowSklep[$PrzetwarzanaAukcja->id] < 1 && isset($_POST['synch_zero']) && (int)$_POST['synch_zero'] == 1 ) {

                    $UUID = $AllegroRest->UUIDv4();

                    $DaneDoWyslania = new stdClass();
                    $DaneDoWyslania->publication = new stdClass();
                    $DaneDoWyslania->offerCriteria = array();
                    $DaneDoWyslania->offerCriteria['0'] = new stdClass();
                    $DaneDoWyslania->offerCriteria['0']->offers = array();
                    $DaneDoWyslania->offerCriteria['0']->offers['0'] = new stdClass();

                    $DaneDoWyslania->publication->action = 'END';
                    $DaneDoWyslania->offerCriteria['0']->offers['0']->id = $PrzetwarzanaAukcja->id;
                    $DaneDoWyslania->offerCriteria['0']->type = "CONTAINS_OFFERS";

                    $wynik = $AllegroRest->commandPut('sale/offer-publication-commands/'.$UUID, $DaneDoWyslania );

                    //

                    if ( is_object($wynik) && isset($wynik->id) ) {
                      
                        $pola = array(
                                array('auction_uuid',$wynik->id)
                        );
                                
                        $db->update_query('allegro_auctions' , $pola, " auction_id = '".$PrzetwarzanaAukcja->id."'");	
                        unset($pola);
                        
                        $wylaczona = ' - AUKCJA WYŁĄCZONA';

                    }
                    unset($UUID, $DaneDoWyslania, $wynik);

                } else {
                  
                    /*
                    
                    if ( $PrzetwarzanaAukcja->stock->available > $TablicaIlosciProduktowSklep[$PrzetwarzanaAukcja->id] ) {

                        $id_aukcji = floatval($PrzetwarzanaAukcja->id);

                        $AktualizowanaAukcja = $AllegroRest->commandRequest('sale/offers', $id_aukcji, '' );

                        if ( !isset($AktualizowanaAukcja->errors) ) {

                            $AktualizowanaAukcja->stock->available = floor($TablicaIlosciProduktowSklep[$PrzetwarzanaAukcja->id]);

                            $rezultatIlosc = $AllegroRest->commandPut('sale/offers/'.$id_aukcji, $AktualizowanaAukcja );

                            if ( isset($rezultatIlosc->stock) && count($rezultatIlosc->stock) > 0) {

                                $pola = array(
                                        array('auction_quantity',floor($TablicaIlosciProduktowSklep[$PrzetwarzanaAukcja->id])),
                                        array('products_quantity',floor($TablicaIlosciProduktowSklep[$PrzetwarzanaAukcja->id]))
                                );

                                $db->update_query('allegro_auctions' , $pola, " auction_id = '".$PrzetwarzanaAukcja->id."'");
                          
                                unset($pola);

                            }
                        }

                        unset($id_aukcji);

                    }
                    
                    */
                    
                }

            }
            // AKTUALIZACJA ILOSCI WYSTAWIONYCH PRODUKTOW NA ALLEGRO - KONIEC

            $link = '';
            
            if ( $wylaczona != '' ) {
            
                $wynikDoAjaxa .= '<a href="' . $link . '" target="_blank">' . $PrzetwarzanaAukcja->id . '</a> - aukcja została przetworzona' . '<br />';
                
            }
            
            $IloscPrzetworzonych++;

            unset($link, $pola, $wylaczona, $DataStart, $DataEnd);
            
            // pobieranie danych o prowizji
            
            if ( isset($_POST['synch_prowizja']) && (int)$_POST['synch_prowizja'] > 0 ) {
                 //
                 
                 $Prowizja = 0;

                 if ( (int)$_POST['synch_prowizja'] == 2 ) {
                   
                     $DaneAukcji = $AllegroRest->commandGet('sale/product-offers/' . $PrzetwarzanaAukcja->id);
                     
                     if ( isset($DaneAukcji->id) ) { 

                         $AllegroTablica = array();
                         $AllegroTablica['offer'] = get_object_vars($DaneAukcji);

                         $DaneProwizji = $AllegroRest->commandPost('pricing/offer-fee-preview', $AllegroTablica);

                         if ( isset($DaneProwizji->commissions) && count($DaneProwizji->commissions) > 0 ) {

                            for ( $x = 0; $x < count($DaneProwizji->commissions); $x++ ) {
                                  //
                                  if ( isset($DaneProwizji->commissions[$x]->fee->amount) ) {
                                       //
                                       $Prowizja = $Prowizja + $DaneProwizji->commissions[$x]->fee->amount;
                                       //
                                  }
                                  //
                            }    
                            
                         }       

                         unset($AllegroTablica);
                     
                         if ( $Prowizja > 0 ) {
                              //
                              $pola = array(array('auction_cost',$Prowizja));
                              $db->update_query('allegro_auctions' , $pola, " auction_id = '" . $PrzetwarzanaAukcja->id . "'");
                              unset($pola);
                              //
                         }
                         
                     }
                     
                 }
                 
                 if ( (int)$_POST['synch_prowizja'] == 1 ) {
                      //
                      $pola = array(array('auction_cost','-1'));
                      $db->update_query('allegro_auctions' , $pola);
                      unset($pola);
                      //                   
                 }
                 
                 unset($Prowizja);
                 
            }
            
            // aktualizowanie danych promowania
            
            if ( isset($_POST['synch_promowanie']) && (int)$_POST['synch_promowanie'] > 0 ) {
            
                 $DanePromowania = $AllegroRest->commandGet('sale/offers/' . $PrzetwarzanaAukcja->id . '/promo-options');
 
                 $JakiePromowania = array();
 
                 if ( isset($DanePromowania->basePackage) ) {

                      $DanePromowaniaAktualne = get_object_vars($DanePromowania->basePackage);
                   
                      if ( isset($DanePromowaniaAktualne['id']) && $DanePromowaniaAktualne['id'] == 'emphasized10d' ) { $JakiePromowania[] = 'emphasized10d'; }
                      if ( isset($DanePromowaniaAktualne['id']) && $DanePromowaniaAktualne['id'] == 'emphasized1d' ) { $JakiePromowania[] = 'emphasized1d'; }
                      if ( isset($DanePromowaniaAktualne['id']) && $DanePromowaniaAktualne['id'] == 'promoPackage' ) { $JakiePromowania[] = 'promoPackage'; }

                      if ( is_array($DanePromowania->extraPackages) && isset($DanePromowania->extraPackages[0]) ) {
                          $DanePromowaniaAktualne = get_object_vars((object)$DanePromowania->extraPackages[0]);
                      }
                  
                      if ( isset($DanePromowaniaAktualne['id']) && $DanePromowaniaAktualne['id'] == 'departmentPage' ) { $JakiePromowania[] = 'departmentPage'; } 
                    
                 }

                 $pola = array(array('allegro_options', implode(',', (array)$JakiePromowania)));            
                 $db->update_query('allegro_auctions' , $pola, " auction_id = '" . $PrzetwarzanaAukcja->id . "'");              
                 unset($pola); 
 
            }                
            
        }
        
    }
  
    $wynikDoAjaxa .= 'rek_'.$IloscPrzetworzonych;

    echo $wynikDoAjaxa;
    unset($wynikDoAjaxa);

}
?>