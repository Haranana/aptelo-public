<?php
// ************** czesc kodu wymagana w przypadku zadan harmonogramu zadan **************

// wczytanie ustawien inicjujacych system
// SPOSOB DZIALANIA
// 1. przedluza waznosc sesji uzytownika jesli do konca zostalo mniej niz 30 minut
// 2. oznacza w bazie sklepu do archwium aukcje starsze nie 365 dni i nie sa aktywne
// 3. jezeli ilosc produktow w sklepie jest < XXX - to wylacza aukcje w Allegro
// 4. jezeli ilosc produktow w sklepie jest mniejsza niz na Allegro, to zmniejsza ilosc ofert w aukcji w Allegro
// 5. jezeli aukcja w Allegro jest zakonczona sprzedaza i ilosc produktow w sklepie jest wieksza niz ilosc wystawiona w Allegro to wznawia aukcje
// 6. jezeli aukcja w Allegro jest zakonczona sprzedaza i ilosc w allegro jest wieksza niz ilosc produktow w sklepie to wylacza produkt w sklepie

//
set_time_limit(25);

$dzialanie = false;

define('POKAZ_ILOSC_ZAPYTAN', false);
define('DLUGOSC_SESJI', '9000');
define('NAZWA_SESJI', 'eGold');

require_once(dirname(dirname(__DIR__)) . '/ustawienia/ustawienia_db.php');
include(dirname(__DIR__) . '/klasy/Bazadanych.php');
$db = new Bazadanych();
include(dirname(__DIR__) . '/klasy/Funkcje.php');
include(dirname(__DIR__) . '/klasy/FunkcjeWlasnePHP.php');
include(dirname(__DIR__) . '/klasy/AllegroRest.php');
        
// LADOWANIE DANYCH KONFIGURACYJNYCH ALLEGRO- START
$zapytanie = "SELECT params, value FROM allegro_connect WHERE params LIKE 'CRON_%'";
$sql = $db->open_query($zapytanie);
while ($info = $sql->fetch_assoc()) { 
    if ( !defined($info['params']) ) {
        define($info['params'], $info['value']);
    }
}
$db->close_query($sql);
unset($zapytanie, $info);
// LADOWANIE DANYCH KONFIGURACYJNYCH - KONIEC


if ( isset($_GET['token']) && $_GET['token'] == CRON_TOKEN ) {
    $dzialanie = true;
} else {
    die();
}

if ( $dzialanie == true ) {

    $czas_wykonywania     = time();

    // PARAMETRY DO WYWOLANIA
    $AktIloscAllegro        = CRON_ILOSC_ALLEGRO;
    $AukcjeWznow            = CRON_AUKCJA_WZNOW;
    $WylaczAukcjeAllegro    = CRON_WYLACZ_PRODUKT;
    $MinimalnaIloscProduktu = CRON_MIN_ILOSC;
    $AukcjeDoArchiwum       = CRON_ARCHIWUM;
    $AktStatusSklep         = CRON_PRODUKT_STATUS;

    // LADOWANIE DANYCH KONFIGURACYJNYCH - START
    $zapytanie = 'SELECT code, value FROM settings';
    $sql = $db->open_query($zapytanie);
    while ($info = $sql->fetch_assoc()) { 
        if ( !defined($info['code']) ) {
            define($info['code'], $info['value']);
        }
    }
    $db->close_query($sql);
    unset($zapytanie, $info);
    // LADOWANIE DANYCH KONFIGURACYJNYCH - KONIEC

    // PRZEDLUZENIE LOGOWANIA UZYTKOWNIKA ALLEGRO - START
    $zapytanie = "SELECT * FROM allegro_users WHERE allegro_user_status = '1'";
    $sql = $db->open_query($zapytanie);

    $TablicaUzytkownikow = array();

    if ((int)$db->ile_rekordow($sql) > 0) {

        while ($info = $sql->fetch_assoc()) {

            $TablicaUzytkownikow[] = $info['allegro_user_id'];
            ///////////////////////////////////////

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
                                array('allegro_token_expires',$DataWaznosciSesji)
                        );

                        $db->update_query('allegro_users' , $pola, " allegro_user_id = '".(int)$info['allegro_user_id']."'");

                     }
                }

                catch(Exception $e)
                    {
                    $raport = $e->getMessage();
                }
                unset($wynik, $DataWaznosciSesji, $AllegroRest);

            }

            ///////////////////////////////////////
        }

    }

    $db->close_query($sql);
    unset($zapytanie, $info);
    // PRZEDLUZENIE LOGOWANIA UZYTKOWNIKA ALLEGRO - KONIEC


    // PRZETWARZANIE AUKCJI DLA WSZYSTKICH UZYTKOWNIKOW PO KOLEI - START
    $Komunikat = '';
    foreach ( $TablicaUzytkownikow  as $key => $value ) {

        $AllegroRest = new AllegroRest( array('allegro_user' => $value) );

        if ( $AukcjeDoArchiwum == '1' ) {

            // OZNACZENIE AUKCJI DO ARCHIWUM - START
            $AukcjeArchiwum = time() - 31536000;

            $zapytanie = "
                SELECT allegro_id, auction_id, auction_last_modification 
                FROM allegro_auctions 
                WHERE auction_seller = '".$value."' AND archiwum_allegro != '1' AND auction_last_modification < '".$AukcjeArchiwum."'
            ";

            $sql = $db->open_query($zapytanie);

            if ( $db->ile_rekordow($sql) > 0 ) {

                while ($info = $sql->fetch_assoc()) {

                    $DaneWejsciowe = $info['auction_id']; 

                    $PrzetwarzanaAukcja = $AllegroRest->commandRequest('sale/offers', $DaneWejsciowe, '' );

                    if ( count((array)$PrzetwarzanaAukcja ) > 0 ) {

                        if ( isset($PrzetwarzanaAukcja->errors) ) {

                            $val = 'NOT_FOUND';
                            foreach($PrzetwarzanaAukcja->errors as $obj) {
                                if ($val == $obj->code) {

                                    $pola = array(
                                            array('auction_uuid',''),
                                            array('archiwum_allegro','1'),
                                            array('auction_status','NOT_FOUND')
                                    );
                                    $db->update_query('allegro_auctions' , $pola, " auction_id = '".$info['auction_id']."'");
                                    $Komunikat .= "Użytkownik " . $value . "; aukcja numer : " . $info['auction_id'] . " - oznaczona do archiwum\n";
                                }

                            }

                        }

                        if ( isset($PrzetwarzanaAukcja->publication->status) && $PrzetwarzanaAukcja->publication->status == 'ARCHIVED' ) {

                            $pola = array(
                                    array('auction_uuid',''),
                                    array('auction_date_end',( isset($PrzetwarzanaAukcja->publication->endingAt) && !empty($PrzetwarzanaAukcja->publication->endingAt) ? date('Y-m-d G:i:s',FunkcjeWlasnePHP::my_strtotime($PrzetwarzanaAukcja->publication->endingAt)) : '' )),
                                    array('auction_status',$PrzetwarzanaAukcja->publication->status),
                                    array('allegro_ended_by',( isset($PrzetwarzanaAukcja->publication->endedBy) && !empty($PrzetwarzanaAukcja->publication->endedBy) ? $PrzetwarzanaAukcja->publication->endedBy : '')),
                                    array('archiwum_allegro','1')
                            );
                            $db->update_query('allegro_auctions' , $pola, " auction_id = '".$info['auction_id']."'");

                        }

                        if ( isset($PrzetwarzanaAukcja->publication->status) && $PrzetwarzanaAukcja->publication->status == 'INACTIVE' ) {

                            $pola = array(
                                    array('auction_uuid',''),
                                    array('auction_status',$PrzetwarzanaAukcja->publication->status),
                                    array('archiwum_allegro','1')
                            );
                            $db->update_query('allegro_auctions' , $pola, " auction_id = '".$info['auction_id']."'");
                            $Komunikat .= "Użytkownik " . $value . "; aukcja numer : " . $info['auction_id'] . " - oznaczona do archiwum\n";

                        }

                    }

                    unset($DaneWejsciowe, $PrzetwarzanaAukcja);

                }
            }
            $db->close_query($sql);
            unset($zapytanie, $info);
            // OZNACZENIE AUKCJI DO ARCHIWUM - KONIEC

        }

        ///////////////////////////////////////////////////////////////////////////////

        $limit = 500;

        $TablicaAukcjiAllegro = array();
        $TablicaAukcjiAllegroID = array();
        $Modification = time();
        $TablicaIlosciProduktowSklep = array();
        $TablicaProduktowSklep = array();

        // POBRANIE DANYCH DOTYCZACYCH ILOSCI PRODUKTOW W SKLEPIE - START
        $zapytanie_tmp = "SELECT a.products_id, a.auction_id, a.auction_quantity, a.products_quantity, a.products_stock_attributes, a.products_date_end, a.auction_last_modification, p.products_quantity, p.products_status FROM allegro_auctions a
        LEFT JOIN products p ON p.products_id = a.products_id WHERE a.auction_seller = '".$value."' AND a.archiwum_allegro != '1'";
        $sql_tmp = $db->open_query($zapytanie_tmp);

        while ( $info_tmp = $sql_tmp->fetch_assoc() ) {

            $TablicaProduktowSklep[$info_tmp['auction_id']]['id_prod'] = $info_tmp['products_id'];
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
                    $ilosc_magazyn = (float)$info_ilosc_cechy['products_stock_quantity'];
                              
                }
                          
                $db->close_query($sql_ilosc_cechy);
                          
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
        $db->close_query($sql_tmp);
        unset($zapytanie_tmp, $info_tmp);
        // POBRANIE DANYCH DOTYCZACYCH ILOSCI PRODUKTOW W SKLEPIE - KONIEC

        if ( count($TablicaIlosciProduktowSklep) > 0 ) {

            $TablicaAukcjiSklep = $AllegroRest->TablicaWszystkichAukcjiSklep($value);

            $ilosc_rekordow = $AllegroRest->IloscWystawionychAllegro();

            $przebiegi = $ilosc_rekordow / $limit;

            for ( $i = 0, $c = ceil($przebiegi); $i < $c; $i++ ) {

                $offset = $limit * $i;

                // POBRANIE PORCJI AUKCJI Z ALLEGRO
                $TablicaAukcjiAllegro = $AllegroRest->TablicaWszystkichAukcjiAllegro( $limit, $offset );
                if ( isset($TablicaAukcjiAllegro) && count($TablicaAukcjiAllegro) < 1 ) {
                    break;
                }

                foreach ( $TablicaAukcjiAllegro as $PrzetwarzanaAukcja ) {
                    $TablicaAukcjiAllegroID[] = $PrzetwarzanaAukcja->id;

                    if ( in_array($PrzetwarzanaAukcja->id, $TablicaAukcjiSklep) ) {

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
                        // AKTUALIZACJA W SKLEPIE DLA ZAKONCZONYCH AUKCJI- START
                        if ( $PrzetwarzanaAukcja->publication->status == 'ENDED' ) {

                            // AKTUALIZACJA STATUSU PRODUKTOW W SKLEPIE DLA ZAKONCZONYCH AUKCJI- START
                            if ( $AktStatusSklep == '1' ) {
                                if ( $TablicaProduktowSklep[$PrzetwarzanaAukcja->id]['ilosc_allegro'] >= $TablicaProduktowSklep[$PrzetwarzanaAukcja->id]['ilosc_sklep'] ) {
                                    if ( FunkcjeWlasnePHP::my_strtotime($PrzetwarzanaAukcja->publication->endedAt) > $TablicaProduktowSklep[$PrzetwarzanaAukcja->id]['data'] ) {
                                        if ( $PrzetwarzanaAukcja->stock->available == 0 && ( $PrzetwarzanaAukcja->publication->endingAt != $PrzetwarzanaAukcja->publication->endedAt ) ) {
                                            $pola = array(
                                                    array('products_status','0')
                                            );
                                            $db->update_query('products' , $pola, " products_id = '".(int)$TablicaProduktowSklep[$PrzetwarzanaAukcja->id]['id_prod']."'");
                                            unset($pola);
                                            $TablicaProduktowSklep[$PrzetwarzanaAukcja->id]['status_prod'] = '0';
                                            $Komunikat .= "Uzytkownik " . $value . "; aukcja numer : " . $PrzetwarzanaAukcja->id . " - produkt w sklepie zostal wylaczony : " . $PrzetwarzanaAukcja->stock->available . "\n";
                                        }
                                    }
                                }
                            }

                            // WZNOWIENIE AUKCJI JESLI ILOSC PRODUKTOW > 0 I PRODUKT JEST AKTYWNY - START
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
                                                $AktualizowanaAukcja->stock->available = floor($IloscDoWznowienia);

                                                $rezultat = $AllegroRest->commandPatch('sale/product-offers/'.$id_aukcji, $AktualizowanaAukcja );

                                                if ( !isset($rezultat->errors) ) {
                                                    if ( isset($rezultat->stock) && $rezultat->stock->available && $rezultat->stock->available > 0 ) {

                                                        $pola = array(
                                                                array('auction_quantity',floor($IloscDoWznowienia)),
                                                                array('products_quantity',floor($IloscDoWznowienia))
                                                        );
                                                        $db->update_query('allegro_auctions' , $pola, " auction_id = '".$PrzetwarzanaAukcja->id."'");
                                                        unset($pola);
                                                    }
                                                }

                                                unset($rezultat, $id_aukcji, $AktualizowanaAukcja);

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
                                                          array('auction_status', 'ACTIVE')
                                                  );
                                                        
                                                  $db->update_query('allegro_auctions' , $pola, " auction_id = '".$PrzetwarzanaAukcja->id."'");	
                                                  unset($pola);

                                                }
                                                $Komunikat .= "Użytkownik " . $value . "; aukcja numer : " . $PrzetwarzanaAukcja->id . " - zostala wznowiona z iloscia : " . $IloscDoWznowienia . "\n";
                                            }
                                        }
                                    }

                                }
                            }

                        }
                        // AKTUALIZACJA W SKLEPIE DLA ZAKONCZONYCH AUKCJI- KONIEC

                        // AKTUALIZACJA ILOSCI WYSTAWIONYCH PRODUKTOW NA ALLEGRO I W SKLEPIE - START
                        if ( $PrzetwarzanaAukcja->publication->status == 'ACTIVE' ) {

                            // JEZELI ILOSC PRODUKTOW W SKLEPIE JEST MNIEJSZA NIZ X TO WYLACZA AUKCJE - START
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

                                    //

                                    if ( is_object($wynik) && isset($wynik->id) ) {
                                  
                                      $pola = array(
                                              array('auction_uuid',$wynik->id),
                                              array('auction_status','ENDED')
                                      );
                                            
                                      $db->update_query('allegro_auctions' , $pola, " auction_id = '".$PrzetwarzanaAukcja->id."'");	
                                      unset($pola);
                                      $Komunikat .= "Użytkownik " . $value . "; aukcja numer : " . $PrzetwarzanaAukcja->id . " - zostala wylaczona w Allegro\n";

                                    }
                                    unset($UUID, $DaneDoWyslania, $wynik);
                                    
                                }

                            }
                            // JEZELI ILOSC PRODUKTOW W SKLEPIE JEST MNIEJSZA NIZ 1 TO WYLACZA AUKCJE - KONIEC

                            // JEZELI ILOSC PRODUKTOW W SKLEPIE JEST MNIEJSZA NIZ NA ALLEGRO TO ZMNIEJSZA ILOSC AUKCJI W ALLEGRO - START
                            if ( $AktIloscAllegro == '1' ) {
                                    
                                if ( $PrzetwarzanaAukcja->stock->available > $TablicaIlosciProduktowSklep[$PrzetwarzanaAukcja->id] ) {

                                    $id_aukcji = floatval($PrzetwarzanaAukcja->id);

                                    $AktualizowanaAukcja = $AllegroRest->commandRequest('sale/product-offers', $id_aukcji, '' );

                                    if ( isset($AktualizowanaAukcja->errors) ) {
                                        foreach ( $AktualizowanaAukcja->errors as $Blad ) {
                                            echo 'Blad pobrania danych aukcji w Allegro : ' . $Blad->userMessage . '<br>';
                                        }

                                    } else {

                                        $AktualizowanaAukcja->stock->available = floor($TablicaIlosciProduktowSklep[$PrzetwarzanaAukcja->id]);

                                        $rezultat = $AllegroRest->commandPatch('sale/product-offers/'.$id_aukcji, $AktualizowanaAukcja );

                                        if ( isset($rezultat->errors) ) {
                                            foreach ( $rezultat->errors as $Blad ) {
                                                echo 'Blad aktualizacji ilosci w Allegro : ' . $Blad->userMessage . '<br>';
                                            }
                                        } else {
                                            if ( isset($rezultat->stock) && $rezultat->stock->available && $rezultat->stock->available > 0 ) {

                                                $pola = array(
                                                        array('auction_quantity',floor($TablicaIlosciProduktowSklep[$PrzetwarzanaAukcja->id])),
                                                        array('products_quantity',floor($TablicaIlosciProduktowSklep[$PrzetwarzanaAukcja->id]))
                                                );
                                                $db->update_query('allegro_auctions' , $pola, " auction_id = '".$PrzetwarzanaAukcja->id."'");
                                                unset($pola);
                                                $Komunikat .= "Użytkownik " . $value . "; aukcja numer : " . $PrzetwarzanaAukcja->id . " - zostala zmieniona ilosc wystawionych produktów : " . $TablicaIlosciProduktowSklep[$PrzetwarzanaAukcja->id] . "\n";
                                            }
                                        }

                                    }

                                    unset($id_aukcji, $AktualizowanaAukcja);

                                }

                            }
                            // JEZELI ILOSC PRODUKTOW W SKLEPIE JEST MNIEJSZA NIZ NA ALLEGRO TO ZMNIEJSZA ILOSC AUKCJI W ALLEGRO - KONIEC

                        }
                        // AKTUALIZACJA ILOSCI WYSTAWIONYCH PRODUKTOW NA ALLEGRO I W SKLEPIE - KONIEC


                    }

                }

            }

        }


        //#########################################################
        //WSTRZYMANIE WYKONANIA SKRYPTU - START
        $czas_chwilowy = time();
        if ( $czas_chwilowy - $czas_wykonywania > 25 ) {
            set_time_limit(25);
            $czas_wykonywania = time();
        }
        unset($TablicaIlosciProduktowSklep);

    }

    // AKTUALIZACJA DATY SYNCHRONIZACJI W BAZIE - START
    $Znacznik = time();

    $pola = array(
            array('value',$Znacznik));
                                        
    $db->update_query('allegro_connect' , $pola, " params = 'CONF_LAST_SYNCHRONIZATION'");
    // AKTUALIZACJA DATY SYNCHRONIZACJI W BAZIE - KONIEC

    // PRZETWARZANIE AUKCJI DLA WSZYSTKICH UZYTKOWNIKOW PO KOLEI - KONIEC
    if ( $Komunikat != '' ) {
         mail('info@oscgold.com', 'Raport z synchronizacji aukcji', $Komunikat);
    }
    //echo 'KONIEC';

} else {

    echo 'brak tokena do wywolania';

}
?>