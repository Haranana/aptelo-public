<?php
// wczytanie ustawien inicjujacych system
// SPOSOB DZIALANIA
// 1. przedluza waznosc sesji uzytownika jesli do konca zostalo mniej niz 30 minut
// 2. oznacza w bazie sklepu do archwium aukcje starsze nie 365 dni i nie sa aktywne
// 3. jezeli ilosc produktow w sklepie jest < XXX - to wylacza aukcje w Allegro
// 4. jezeli ilosc produktow w sklepie jest mniejsza niz na Allegro, to zmniejsza ilosc ofert w aukcji w Allegro
// 5. jezeli aukcja w Allegro jest zakonczona sprzedaza i ilosc produktow w sklepie jest wieksza niz ilosc wystawiona w Allegro to wznawia aukcje
// 6. jezeli aukcja w Allegro jest zakonczona sprzedaza i ilosc w allegro jest wieksza niz ilosc produktow w sklepie to wylacza produkt w sklepie
// 7. jezeli ilosc produktow w sklepie jest mniejsza niz na Allegro, to zwieksza stan magazynowy w sklepie do stanu w Allegro
// 8. jezeli ilosc produktow w sklepie jest wieksza niz na Allegro, to zmniejsza stan magazynowy w sklepie do stanu w Allegro

if ( isset($_GET['token']) ) {

    chdir('zarzadzanie/');
   
    // wczytanie ustawien inicjujacych system
    require_once( getcwd() . '/ustawienia/init.php' );
    
    // ladowanie danych konfiguracyjnych allegro - start
    
    $zapytanie = "SELECT params, value FROM allegro_connect WHERE params LIKE 'CRON_%'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    while ($info = $sql->fetch_assoc()) { 
    
        if ( !defined($info['params']) ) {
            define($info['params'], $info['value']);
        }
        
    }
    
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info);
    
    if ( CRON_STATUS == 1 ) {
    
        // ladowanie danych konfiguracyjnych - koniec
        
        $czas_wykonywania = time();

        // parametry do wywolania
        
        $AktIloscAllegro        = CRON_ILOSC_ALLEGRO;
        $AukcjeWznow            = CRON_AUKCJA_WZNOW;
        $WylaczAukcjeAllegro    = CRON_WYLACZ_PRODUKT;
        $MinimalnaIloscProduktu = CRON_MIN_ILOSC;
        $AukcjeDoArchiwum       = CRON_ARCHIWUM;
        $AktStatusSklep         = CRON_PRODUKT_STATUS;
        $ZmniejszIloscSklep     = CRON_PRODUKT_ILOSC;
        $ZwiekszIloscSklep      = CRON_ALLEGRO_ILOSC;

        // przedluzenie logowania uzytkownika allegro - start
        
        $zapytanie = "SELECT * FROM allegro_users WHERE allegro_user_status = '1' AND allegro_token_expires < now() AND allegro_user_clientid != ''";
        $sql = $GLOBALS['db']->open_query($zapytanie);

        $TablicaUzytkownikow = array();

        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

            while ($info = $sql->fetch_assoc()) {

                $TablicaUzytkownikow[] = array( 'id' => $info['allegro_user_id'],
                                                'konto' => $info['allegro_user_login'] );

                $ZnacznikCzasu = time() + 1800;
                
                if ( $info['allegro_token_expires'] <= $ZnacznikCzasu ) {

                    $AllegroRest = new AllegroRest( array('allegro_user' => $info['allegro_user_id']) );
                    try
                        {
                        $wynik = $AllegroRest->tokenRefresh($AllegroRest->ParametryPolaczenia['RefreshToken']);

                        if ( count((array)$wynik) > 0 ) {
                            $DataWaznosciSesji = time() + $wynik->expires_in;

                            $pola = array(
                                    array('allegro_user_authorizationtoken',$wynik->access_token),
                                    array('allegro_user_refreshtoken',$wynik->refresh_token),
                                    array('allegro_token_expires',$DataWaznosciSesji));

                            $GLOBALS['db']->update_query('allegro_users' , $pola, " allegro_user_id = '".(int)$info['allegro_user_id']."'");

                         }
                    }

                    catch(Exception $e)
                        {
                        $raport = $e->getMessage();
                    }
                    unset($wynik, $DataWaznosciSesji, $AllegroRest);

                }

            }

        }

        $GLOBALS['db']->close_query($sql);
        unset($zapytanie, $info);
        
        // przedluzenie logowania uzytkownika allegro - koniec
        
            // przetwarzanie aukcji dla wszystkich uzytkownikow po kolei - start
            
            $Komunikat = '';
            
            foreach ( $TablicaUzytkownikow  as $key => $value ) {

                $AllegroRest = new AllegroRest( array('allegro_user' => $value['id']) );

                if ( $AukcjeDoArchiwum == '1' ) {

                    // OZNACZENIE AUKCJI DO ARCHIWUM - START
                    $AukcjeArchiwum = time() - 31536000;

                    $zapytanie = "
                        SELECT allegro_id, auction_id, auction_last_modification 
                        FROM allegro_auctions 
                        WHERE auction_seller = '" . $value['id'] . "' AND archiwum_allegro != '1' AND auction_last_modification < '" . $AukcjeArchiwum . "'";

                    $sql = $GLOBALS['db']->open_query($zapytanie);

                    if ( $GLOBALS['db']->ile_rekordow($sql) > 0 ) {

                        while ($info = $sql->fetch_assoc()) {

                            $DaneWejsciowe = $info['auction_id']; 

                            $PrzetwarzanaAukcja = $AllegroRest->commandGet('sale/product-offers/'.$DaneWejsciowe);


                            if ( count((array)$PrzetwarzanaAukcja ) > 0 ) {

                                if ( isset($PrzetwarzanaAukcja->errors) ) {

                                    $val = 'NOT_FOUND';
                                    
                                    foreach($PrzetwarzanaAukcja->errors as $obj) {
                                      
                                        if ($val == $obj->code) {

                                            $pola = array(
                                                    array('auction_uuid',''),
                                                    array('archiwum_allegro','1'),
                                                    array('auction_status','NOT_FOUND'));
                                                    
                                            $GLOBALS['db']->update_query('allegro_auctions' , $pola, " auction_id = '".$info['auction_id']."'");
                                            $Komunikat .= "<div style=\"padding:2px 0 2px 0\">Użytkownik <b>" . $value['konto'] . "</b>; aukcja numer: <b>" . $info['auction_id'] . "</b> - oznaczona do archiwum</div>";
                                            
                                        }

                                    }

                                }

                                if ( isset($PrzetwarzanaAukcja->publication->status) && $PrzetwarzanaAukcja->publication->status == 'ARCHIVED' ) {

                                    $pola = array(
                                            array('auction_uuid',''),
                                            array('auction_date_end',( isset($PrzetwarzanaAukcja->publication->endingAt) && !empty($PrzetwarzanaAukcja->publication->endingAt) ? date('Y-m-d G:i:s',FunkcjeWlasnePHP::my_strtotime($PrzetwarzanaAukcja->publication->endingAt)) : '' )),
                                            array('auction_status',$PrzetwarzanaAukcja->publication->status),
                                            array('allegro_ended_by',( isset($PrzetwarzanaAukcja->publication->endedBy) && !empty($PrzetwarzanaAukcja->publication->endedBy) ? $PrzetwarzanaAukcja->publication->endedBy : '')),
                                            array('archiwum_allegro','1'));
                                            
                                    $GLOBALS['db']->update_query('allegro_auctions' , $pola, " auction_id = '".$info['auction_id']."'");

                                }

                                if ( isset($PrzetwarzanaAukcja->publication->status) && $PrzetwarzanaAukcja->publication->status == 'INACTIVE' ) {

                                    $pola = array(
                                            array('auction_uuid',''),
                                            array('auction_status',$PrzetwarzanaAukcja->publication->status),
                                            array('archiwum_allegro','1'));
                                            
                                    $GLOBALS['db']->update_query('allegro_auctions' , $pola, " auction_id = '".$info['auction_id']."'");
                                    
                                    $Komunikat .= "<div style=\"padding:2px 0 2px 0\">Użytkownik <b>" . $value['konto'] . "</b>; aukcja numer <b>" . $info['auction_id'] . "</b> - oznaczona do archiwum</div>";

                                }

                            }

                            unset($DaneWejsciowe, $PrzetwarzanaAukcja);

                        }
                    }
                    
                    $GLOBALS['db']->close_query($sql);
                    unset($zapytanie, $info);
                    
                    // oznaczenie aukcji do archiwum - koniec

                }

                ///////////////////////////////////////////////////////////////////////////////

                $limit = 500;

                $TablicaAukcjiAllegro = array();
                $TablicaAukcjiAllegroID = array();
                $Modification = time();
                $TablicaIlosciProduktowSklep = array();
                $TablicaProduktowSklep = array();

                // pobranie danych dotyczacych ilosci produktow w sklepie - start
                
                $zapytanie_tmp = "SELECT a.products_id, a.auction_id, a.auction_quantity, a.products_quantity, a.products_stock_attributes, a.products_date_end, a.auction_last_modification, p.products_quantity, p.products_status FROM allegro_auctions a LEFT JOIN products p ON p.products_id = a.products_id WHERE a.auction_seller = '" . $value['id'] . "' AND a.archiwum_allegro != '1'";
                $sql_tmp = $GLOBALS['db']->open_query($zapytanie_tmp);

                while ( $info_tmp = $sql_tmp->fetch_assoc() ) {

                    $TablicaProduktowSklep[$info_tmp['auction_id']]['id_prod'] = $info_tmp['products_id'];
                    
                    if ( isset($info_tmp['products_stock_attributes']) && $info_tmp['products_stock_attributes'] != '' && CECHY_MAGAZYN == 'tak' ) {

                        $ilosc_magazyn = 0;

                        $cechy_produktu = str_replace('x', ',' , (string)$info_tmp['products_stock_attributes']);
                                  
                        $zapytanie_ilosc_cechy = "SELECT products_stock_quantity 
                                                    FROM products_stock
                                                    WHERE products_id = '" . (int)$info_tmp['products_id']. "' 
                                                    AND products_stock_attributes = '".$cechy_produktu."'";
                                                               
                        $sql_ilosc_cechy = $GLOBALS['db']->open_query($zapytanie_ilosc_cechy);

                        if ((int)$GLOBALS['db']->ile_rekordow($sql_ilosc_cechy) > 0) {
                                  
                            $info_ilosc_cechy = $sql_ilosc_cechy->fetch_assoc();
                            $ilosc_magazyn = (float)$info_ilosc_cechy['products_stock_quantity'];
                                      
                        }
                                  
                        $GLOBALS['db']->close_query($sql_ilosc_cechy);
                                  
                        unset($zapytanie_ilosc_cechy, $info_ilosc_cechy, $cechy_produktu);
                                  
                        $TablicaIlosciProduktowSklep[$info_tmp['auction_id']] = $ilosc_magazyn;
                        $TablicaProduktowSklep[$info_tmp['auction_id']]['ilosc_sklep'] = $ilosc_magazyn;

                    } else {

                        $TablicaIlosciProduktowSklep[$info_tmp['auction_id']] = (float)$info_tmp['products_quantity'];
                        $TablicaProduktowSklep[$info_tmp['auction_id']]['ilosc_sklep'] = (float)$info_tmp['products_quantity'];

                    }
                    
                    $TablicaProduktowSklep[$info_tmp['auction_id']]['ilosc_allegro'] = $info_tmp['auction_quantity'];
                    $TablicaProduktowSklep[$info_tmp['auction_id']]['ilosc_ostatnio_wystawiona'] = $info_tmp['products_quantity'];
                    $TablicaProduktowSklep[$info_tmp['auction_id']]['data'] = $info_tmp['auction_last_modification'];
                    $TablicaProduktowSklep[$info_tmp['auction_id']]['status_prod'] = $info_tmp['products_status'];
                }
                
                $GLOBALS['db']->close_query($sql_tmp);
                unset($zapytanie_tmp, $info_tmp);
                
                // pobranie danych dotyczacych ilosci produktow w sklepie - koniec

                if ( count($TablicaIlosciProduktowSklep) > 0 ) {

                    $TablicaAukcjiSklep = $AllegroRest->TablicaWszystkichAukcjiSklep($value['id']);

                    $ilosc_rekordow = $AllegroRest->IloscWystawionychAllegro();

                    $przebiegi = $ilosc_rekordow / $limit;

                    for ( $i = 0, $c = ceil($przebiegi); $i < $c; $i++ ) {

                        $offset = $limit * $i;

                        // pobranie porcji aukcji z allegro
                        
                        $TablicaAukcjiAllegro = $AllegroRest->TablicaWszystkichAukcjiAllegro( $limit, $offset );
                        
                        if ( isset($TablicaAukcjiAllegro) && count($TablicaAukcjiAllegro) < 1 ) {
                            break;
                        }

                        foreach ( $TablicaAukcjiAllegro as $PrzetwarzanaAukcja ) {

                            $TablicaAukcjiAllegroID[] = $PrzetwarzanaAukcja->id;

                            if ( in_array($PrzetwarzanaAukcja->id, $TablicaAukcjiSklep) ) {
                          
                                $KomunikatTmp = "<div style=\"padding:2px 0 2px 0\">Uzytkownik <b>" . $value['konto'] . "</b>; aukcja numer <b>" . $PrzetwarzanaAukcja->id . "</b> - została przetworzona</div>";
                          
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
                                        array('auction_last_modification',$Modification));

                                $GLOBALS['db']->update_query('allegro_auctions' , $pola, " auction_id = '".$PrzetwarzanaAukcja->id."'");
                                unset($pola);

                                if ( $DataStart != '' ) {
                                  
                                    $pola = array(
                                            array('auction_date_start',date('Y-m-d G:i:s',FunkcjeWlasnePHP::my_strtotime($DataStart))));
                                            
                                    $GLOBALS['db']->update_query('allegro_auctions' , $pola, " auction_id = '".$PrzetwarzanaAukcja->id."'");
                                }
                                if ( $DataEnd != '' ) {
                                  
                                    $pola = array(
                                            array('auction_date_end',date('Y-m-d G:i:s',FunkcjeWlasnePHP::my_strtotime($DataEnd))));
                                            
                                    $GLOBALS['db']->update_query('allegro_auctions' , $pola, " auction_id = '".$PrzetwarzanaAukcja->id."'");
                                }
                                
                                // aktualizacja w sklepie dla zakonczonych aukcji- start
                                
                                if ( $PrzetwarzanaAukcja->publication->status == 'ENDED' ) {

                                    // aktualizacja statusu produktow w sklepie dla zakonczonych aukcji- start
                                    
                                    if ( $AktStatusSklep == '1' ) {
                                      
                                        if ( $TablicaProduktowSklep[$PrzetwarzanaAukcja->id]['ilosc_allegro'] >= $TablicaProduktowSklep[$PrzetwarzanaAukcja->id]['ilosc_sklep'] ) {
                                          
                                            if ( FunkcjeWlasnePHP::my_strtotime($PrzetwarzanaAukcja->publication->endedAt) > $TablicaProduktowSklep[$PrzetwarzanaAukcja->id]['data'] ) {
                                              
                                                if ( $PrzetwarzanaAukcja->stock->available == 0 && ( $PrzetwarzanaAukcja->publication->endingAt != $PrzetwarzanaAukcja->publication->endedAt ) ) {
                                                  
                                                    $pola = array(
                                                            array('products_status','0'));
                                                            
                                                    $GLOBALS['db']->update_query('products' , $pola, " products_id = '".(int)$TablicaProduktowSklep[$PrzetwarzanaAukcja->id]['id_prod']."'");
                                                    unset($pola);
                                                    
                                                    $TablicaProduktowSklep[$PrzetwarzanaAukcja->id]['status_prod'] = '0';
                                                    
                                                    $KomunikatTmp = "<div style=\"padding:2px 0 2px 0\">Uzytkownik <b>" . $value['konto'] . "</b>; aukcja numer <b>" . $PrzetwarzanaAukcja->id . "</b> - produkt w sklepie zostal wylaczony: " . $PrzetwarzanaAukcja->stock->available . "</div>";
                                                    
                                                }
                                                
                                            }
                                            
                                        }
                                        
                                    }

                                    // wznowienie aukcji jesli ilosc produktow > 0 i produkt jest aktywny - start
                                    
                                    if ( $AukcjeWznow == '1' ) {
                                      
                                        if ( $TablicaProduktowSklep[$PrzetwarzanaAukcja->id]['status_prod'] == '1' && $TablicaProduktowSklep[$PrzetwarzanaAukcja->id]['ilosc_sklep'] > 0 ) {

                                            $id_aukcji = floatval($PrzetwarzanaAukcja->id);
                                            $AktualizowanaAukcja = $AllegroRest->commandRequest('sale/product-offers', $id_aukcji, '' );

                                            if ( !isset($AktualizowanaAukcja->errors) ) {

                                                if ( $AktualizowanaAukcja->publication->endedBy != 'EXPIRATION' ) {

                                                    $IloscDoWznowienia = $TablicaProduktowSklep[$PrzetwarzanaAukcja->id]['ilosc_allegro'];

                                                    if ( $TablicaProduktowSklep[$PrzetwarzanaAukcja->id]['ilosc_allegro'] == 0 && $TablicaProduktowSklep[$PrzetwarzanaAukcja->id]['ilosc_ostatnio_wystawiona'] > 0 ) {
                                                        $IloscDoWznowienia = $TablicaProduktowSklep[$PrzetwarzanaAukcja->id]['ilosc_sklep'];
                                                    }

                                                    if ( $IloscDoWznowienia > 0 ) {
                                                      
                                                        $DaneDoAktualizacji = new stdClass();
                                                        $DaneDoAktualizacji->stock = new stdClass();
                                                        $DaneDoAktualizacji->stock->available = floor($IloscDoWznowienia);

                                                        $rezultat = $AllegroRest->commandPatch('sale/product-offers/'.$id_aukcji, $DaneDoAktualizacji );

                                                        if ( !isset($rezultat->errors) ) {
                                                          
                                                            if ( isset($rezultat->stock) && $rezultat->stock->available && $rezultat->stock->available > 0 ) {

                                                                $pola = array(
                                                                        array('auction_quantity',floor($IloscDoWznowienia)),
                                                                        array('products_quantity',floor($IloscDoWznowienia)));
                                                                
                                                                $GLOBALS['db']->update_query('allegro_auctions' , $pola, " auction_id = '".$PrzetwarzanaAukcja->id."'");
                                                                unset($pola);
                                                                
                                                            }
                                                            
                                                        }

                                                        unset($rezultat, $id_aukcji, $AktualizowanaAukcja, $DaneDoAktualizacji);

                                                        $UUID = $AllegroRest->UUIDv4();

                                                        $DaneDoWyslania = new stdClass();
                                                        $DaneDoWyslania->publication = new stdClass();
                                                        $DaneDoWyslania->offerCriteria = array();
                                                        $DaneDoWyslania->offerCriteria['0'] = new stdClass();
                                                        $DaneDoWyslania->offerCriteria['0']->offers = array();
                                                        $DaneDoWyslania->offerCriteria['0']->offers['0'] = new stdClass();

                                                        $DaneDoWyslania->publication->action = 'ACTIVATE';
                                                        $DaneDoWyslania->offerCriteria['0']->offers['0']->id = $PrzetwarzanaAukcja->id;
                                                        $DaneDoWyslania->offerCriteria['0']->type = "CONTAINS_OFFERS";

                                                        $wynik = $AllegroRest->commandPut('sale/offer-publication-commands/'.$UUID, $DaneDoWyslania );

                                                        if ( is_object($wynik) && isset($wynik->id) ) {
                                                      
                                                            $pola = array(
                                                                    array('products_quantity',$IloscDoWznowienia),
                                                                    array('auction_uuid',$wynik->id),
                                                                    array('auction_status', 'ACTIVE'));
                                                                  
                                                            $GLOBALS['db']->update_query('allegro_auctions' , $pola, " auction_id = '".$PrzetwarzanaAukcja->id."'");	
                                                            unset($pola);

                                                        }
                                                        
                                                        $KomunikatTmp = "<div style=\"padding:2px 0 2px 0\">Użytkownik <b>" . $value['konto'] . "</b>; aukcja numer <b>" . $PrzetwarzanaAukcja->id . "</b> - zostala wznowiona z iloscia: " . $IloscDoWznowienia . "</div>";
                                                        
                                                    }

                                                }

                                            }

                                        }
                                        
                                    }

                                }
                                
                                // aktualizacja w sklepie dla zakonczonych aukcji- koniec

                                // aktualizacja ilosci wystawionych produktow na allegro i w sklepie - start
                                if ( $PrzetwarzanaAukcja->publication->status == 'ACTIVE' ) {

                                    // jezeli ilosc produktow w sklepie jest mniejsza niz x to wylacza aukcje - start
                                    
                                    if ( $WylaczAukcjeAllegro == '1' ) {
                                            
                                        if ( $TablicaIlosciProduktowSklep[$PrzetwarzanaAukcja->id] < $MinimalnaIloscProduktu ) {
                                          
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

                                            if ( is_object($wynik) && isset($wynik->id) ) {
                                          
                                                $pola = array(
                                                        array('auction_uuid',$wynik->id),
                                                        array('auction_status','ENDED'));
                                                      
                                                $GLOBALS['db']->update_query('allegro_auctions' , $pola, " auction_id = '".$PrzetwarzanaAukcja->id."'");	
                                                unset($pola);
                                                
                                                $KomunikatTmp = "<div style=\"padding:2px 0 2px 0\">Użytkownik <b>" . $value['konto'] . "</b>; aukcja numer <b>" . $PrzetwarzanaAukcja->id . "</b> - zostala wylaczona w Allegro</div>";

                                            }
                                            
                                            unset($UUID, $DaneDoWyslania, $wynik);
                                            
                                        }

                                    }
                                    
                                    // jezeli ilosc produktow w sklepie jest mniejsza niz 1 to wylacza aukcje - koniec

                                    // jezeli ilosc produktow w sklepie jest mniejsza niz na allegro to zmniejsza ilosc aukcji w allegro - start
                                    
                                    if ( $AktIloscAllegro == '1' ) {
                                            
                                        if ( $PrzetwarzanaAukcja->stock->available > $TablicaIlosciProduktowSklep[$PrzetwarzanaAukcja->id] ) {

                                            $id_aukcji = floatval($PrzetwarzanaAukcja->id);

                                            $AktualizowanaAukcja = $AllegroRest->commandRequest('sale/product-offers', $id_aukcji, '' );

                                            if ( isset($AktualizowanaAukcja->errors) ) {
                                              
                                                foreach ( $AktualizowanaAukcja->errors as $Blad ) {
                                                  
                                                    $KomunikatTmp = "<div style=\"padding:2px 0 2px 0;color:#ff0000\">Użytkownik <b>" . $value['konto'] . "</b>; blad pobrania danych aukcji numer <b>" . $PrzetwarzanaAukcja->id . "</b> w Allegro: " . $Blad->userMessage . "</div>";
                                                    
                                                }

                                            } else {

                                                $DaneDoAktualizacji = new stdClass();
                                                $DaneDoAktualizacji->stock = new stdClass();
                                                $DaneDoAktualizacji->stock->available = floor($TablicaIlosciProduktowSklep[$PrzetwarzanaAukcja->id]);

                                                $rezultat = $AllegroRest->commandPatch('sale/product-offers/'.$id_aukcji, $DaneDoAktualizacji );

                                                if ( isset($rezultat->errors) ) {
                                                  
                                                    foreach ( $rezultat->errors as $Blad ) {
                                                      
                                                        $KomunikatTmp = "<div style=\"padding:2px 0 2px 0;color:#ff0000\">Użytkownik <b>" . $value['konto'] . "</b>; blad aktualizacji ilosci aukcji numer <b>" . $PrzetwarzanaAukcja->id . "</b> w Allegro: " . $Blad->userMessage . "</div>";
                                                        
                                                    }
                                                    
                                                } else {
                                                  
                                                    if ( isset($rezultat->stock) && $rezultat->stock->available && $rezultat->stock->available > 0 ) {

                                                        $pola = array(
                                                                array('auction_quantity',floor($TablicaIlosciProduktowSklep[$PrzetwarzanaAukcja->id])),
                                                                array('products_quantity',floor($TablicaIlosciProduktowSklep[$PrzetwarzanaAukcja->id])));
                                                                
                                                        $GLOBALS['db']->update_query('allegro_auctions' , $pola, " auction_id = '".$PrzetwarzanaAukcja->id."'");
                                                        unset($pola);
                                                        
                                                        $KomunikatTmp = "<div style=\"padding:2px 0 2px 0\">Użytkownik <b>" . $value['konto'] . "</b>; aukcja numer <b>" . $PrzetwarzanaAukcja->id . "</b> - zostala zmieniona ilosc wystawionych produktów: " . $TablicaIlosciProduktowSklep[$PrzetwarzanaAukcja->id] . "</div>";
                                                        
                                                    }
                                                    
                                                }

                                            }

                                            unset($id_aukcji, $AktualizowanaAukcja);

                                        }

                                    }
                                    
                                    // jezeli ilosc produktow w sklepie jest mniejsza niz na allegro to zmniejsza ilosc aukcji w allegro - koniec

                                }
                                
                                // aktualizacja ilosci wystawionych produktow na allegro i w sklepie - koniec

                                // aktualizacja ilosci produktow w sklepie - start
                                // jezeli ilosc produktow w sklepie jest wieksza niz na allegro to zmniejsza ilosc - start
                                if ( $ZmniejszIloscSklep == '1' ) {

                                    if ( $PrzetwarzanaAukcja->stock->available < $TablicaIlosciProduktowSklep[$PrzetwarzanaAukcja->id] ) {

                                        $pola = array(
                                                array('products_quantity',$PrzetwarzanaAukcja->stock->available)
                                        );
                                                    
                                        $db->update_query('products' , $pola, " products_id = '".(int)$TablicaProduktowSklep[$PrzetwarzanaAukcja->id]['id_prod']."'");	
                                        unset($pola);

                                        $Komunikat .= "Użytkownik " . $value['konto'] . "; aukcja numer : " . $PrzetwarzanaAukcja->id . " - zostala zmniejszona ilosc produktu w sklepie\n";

                                            
                                    }

                                    if ( $PrzetwarzanaAukcja->publication->status == 'ENDED' ) {

                                        $pola = array(
                                                array('products_quantity','0')
                                        );
                                                    
                                        $db->update_query('products' , $pola, " products_id = '".(int)$TablicaProduktowSklep[$PrzetwarzanaAukcja->id]['id_prod']."'");	
                                        unset($pola);

                                        $Komunikat .= "Użytkownik " . $value['konto'] . "; aukcja numer : " . $PrzetwarzanaAukcja->id . " - zostala zmniejszona ilosc produktu w sklepie\n";

                                    }

                                }
                                // jezeli ilosc produktow w sklepie jest wieksza niz na allegro to zmniejsza ilosc - koniec

                                // jezeli ilosc produktow w sklepie jest mniejsza niz na allegro to zwieksza ilosc - start
                                if ( $ZwiekszIloscSklep == '1' ) {

                                    if ( ($PrzetwarzanaAukcja->stock->available > $TablicaIlosciProduktowSklep[$PrzetwarzanaAukcja->id]) && $PrzetwarzanaAukcja->publication->status == 'ACTIVE' ) {

                                        $pola = array(
                                                array('products_quantity',$PrzetwarzanaAukcja->stock->available)
                                        );
                                                    
                                        $db->update_query('products' , $pola, " products_id = '".(int)$TablicaProduktowSklep[$PrzetwarzanaAukcja->id]['id_prod']."'");	
                                        unset($pola);

                                        $Komunikat .= "Użytkownik " . $value['konto'] . "; aukcja numer : " . $PrzetwarzanaAukcja->id . " - zostala zmniejszona ilosc produktu w sklepie\n";

                                            
                                    }

                                }
                                // jezeli ilosc produktow w sklepie jest mniejsza niz na allegro to zwieksza ilosc - koniec
                                
                                
                                
                                // aktualizacja ilosci produktow w sklepie - koniec

                                $Komunikat .= $KomunikatTmp;

                            }

                        }

                    }

                } else {
                    
                    $Komunikat .= 'Brak danych do przetworzenia...';
                  
                }

                // wstrzymanie wykonania skryptu - start
                
                $czas_chwilowy = time();
                
                if ( $czas_chwilowy - $czas_wykonywania > 25 ) {
                  
                    set_time_limit(25);
                    $czas_wykonywania = time();
                    
                }
                
                unset($TablicaIlosciProduktowSklep);

            }

            // aktualizacja daty synchronizacji w bazie - start
            
            $Znacznik = time();

            $pola = array(
                    array('value',$Znacznik));
                                                
            $GLOBALS['db']->update_query('allegro_connect' , $pola, " params = 'CONF_LAST_SYNCHRONIZATION'");
            
            // aktualizacja daty synchronizacji w bazie - koniec
            
        echo '<div style="font-size:13px;font-family:Arial,Tahoma;border:1px solid #ccc;text-align:left;padding:20px;">' . $Komunikat . '</div>';
        
    } else {
      
        echo '<div style="font-size:13px;font-family:Arial,Tahoma;position:absolute;top:20%;left:50%;margin-left:-170px;width:300px;border:1px solid #ccc;text-align:center;padding:20px;">Brak autoryzacji ....</div>';      
      
    }
    
} else {
  
    echo '<div style="font-size:13px;font-family:Arial,Tahoma;position:absolute;top:20%;left:50%;margin-left:-170px;width:300px;border:1px solid #ccc;text-align:center;padding:20px;">Brak autoryzacji ....</div>';      
  
}
?>