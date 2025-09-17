<?php

class PlatnosciElektroniczne {

    //funkcja zwraca blad tranzakcji w serwisie PayU
    public static function payu_tablicaBledow($kodBledu) {

        $tablica = array(
                  "100" => "brak parametru pos id",
                  "101" => "brak parametru session id",
                  "102" => "brak parametru ts",
                  "103" => "brak parametru sig",
                  "104" => "brak parametru desc",
                  "105" => "brak parametru client ip",
                  "106" => "brak parametru first name",
                  "107" => "brak parametru last name",
                  "108" => "brak parametru street",
                  "109" => "brak parametru city",
                  "110" => "brak parametru post code",
                  "111" => "brak parametru amount",
                  "112" => "błędny numer konta bankowego",
                  "113" => "brak parametru email",
                  "114" => "brak pnumeru telefonu",
                  "200" => "inny chwilowy błąd",
                  "201" => "inny chwilowy błąd bazy danych",
                  "202" => "POS o podanym identyfikatorze jest zablokowany",
                  "203" => "niedozwolona wartość pay_type dla danego pos_id",
                  "204" => "podana metoda płatności (wartość pay_type) jest chwilowo zablokowana dla danego pos_id, np. przerwa konserwacyjna bramki płatniczej",
                  "205" => "kwota transakcji mniejsza od wartości minimalnej",
                  "206" => "kwota transakcji większa od wartości maksymalnej",
                  "207" => "przekroczona wartość wszystkich transakcji dla jednego klienta w ostatnim przedziale czasowym",
                  "208" => "POS działa w wariancie ExpressPayment lecz nie nastapiła aktywacja tego wariantu współpracy (czekamy na zgode działu obsługi klienta)",
                  "209" => "błedny numer pos_id lub pos_auth_key",
                  "500" => "transakcja nie istnieje",
                  "501" => "brak autoryzacji dla danej transakcji",
                  "502" => "transakcja rozpoczęta wcześniej",
                  "503" => "autoryzacja do transakcji była juz przeprowadzana",
                  "504" => "transakcja anulowana wczesniej",
                  "505" => "transakcja przekazana do odbioru wcześniej",
                  "506" => "transakcja już odebrana",
                  "507" => "błąd podczas zwrotu środków do klienta",
                  "508" => "niewypełniony formularz",
                  "599" => "błędny stan transakcji, np. nie można uznać transakcji kilka razy lub inny, prosimy o kontakt",
                  "999" => "inny błąd krytyczny - prosimy o kontakt	"
        );

        return $tablica[$kodBledu];

    }
    
  
    // funkcja sprawdza HASH dla eservie
    public static function HashEservice( $tablica ) {  
      
        // sprawdzanie HASHA
        
        // storeKey sklepu
        $zapytanie_storekey = "SELECT wartosc FROM modules_payment_params WHERE kod = 'PLATNOSC_ESERVICE_STOREKEY'";
        $sql_storekey = $GLOBALS['db']->open_query($zapytanie_storekey);
        
        $storeKey = '';
        
        if ( (int)$GLOBALS['db']->ile_rekordow($sql_storekey) > 0 ) {
          
            $info_storekey = $sql_storekey->fetch_assoc();
            $storeKey = $info_storekey['wartosc'];
            unset($info_storekey);
            
        }

        $GLOBALS['db']->close_query($sql_storekey);
        unset($zapytanie_storekey);              

        //
        
        $hashParamsVal = '';
        $sep = "|";
        $hashParams = explode($sep, (string)$tablica['HASHPARAMS']);
        $secureCount = 0;

        foreach ($hashParams as $hashParam) {
            //
            if( strtolower((string)$hashParam) == "clientid" || strtolower((string)$hashParam) == "response" || strtolower((string)$hashParam) == "orderid" ) {
                $secureCount++;
            }
            if ( isset($tablica[$hashParam]) ) {
                 $hashParamsVal .= $tablica[$hashParam];
                 $hashParamsVal .= $sep;
            }
            //
        }
        
        $blad = false;

        // jezeli hash sie nie zgadza to przenosi na brak-strony i nic nie zapisuje
        if( ($hashParamsVal != $tablica['HASHPARAMSVAL'] . $sep) || base64_encode(hash('sha512', $hashParamsVal . $storeKey, true)) != $tablica['HASH'] || $secureCount != 3 || $tablica['TranType'] != 'Auth') {
            //  
            $blad = true;        
            //
        }          
    
    }
    
    // funkcja zmiany statusu dla eservice
    public static function StatusEservice( $tablica, $zwroc_info = false ) {
        
        // zmiana statusu

        $zapytanie_stale = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_ESERVICE_%'";
        $sql_stale = $GLOBALS['db']->open_query($zapytanie_stale);
        
        if ( (int)$GLOBALS['db']->ile_rekordow($sql_stale) > 0 ) {

            while ($info_stale = $sql_stale->fetch_assoc()) {
                //
                if ( !defined($info_stale['kod']) ) {
                     define($info_stale['kod'], $info_stale['wartosc']);
                }
                //
            }
            
            unset($info_stale);
            
        }
        
        $GLOBALS['db']->close_query($sql_stale);
        unset($zapytanie_stale);
        
        $komentarz = '';

        if ( isset($tablica['OrderId']) && $tablica['OrderId'] != '' ) {
          
            $nr_tmp = explode('-', (string)$tablica['OrderId']);
            
            // sprawdzenie czy istnieje zamowienie w bazie danych
            $zapytanie = "SELECT orders_id, orders_status FROM orders WHERE orders_id = '" . (int)$nr_tmp[0] . "'";
            $sql = $GLOBALS['db']->open_query($zapytanie);
            
            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();        

                if ( isset($tablica['TransId']) ) {
                     $komentarz = 'Numer transakcji: ' . $tablica['TransId'] . '<br />';
                }
                
                $status = 'brak statusu';
                
                if ( isset($tablica['Response']) ) {
                  
                    switch (strtoupper((string)$tablica['Response'])) {
                        case "APPROVED":
                            $status = 'płatność zatwierdzona';
                            break;
                        case "DECLINED":
                            $status = 'płatność odrzucona';
                            break;                 
                        case "ERROR":
                            $status = 'błędny status';
                            break;
                        case "PENDING":
                            $status = 'w oczekiwaniu na potwierdzenie';
                            break;                               
                    }        

                }
                
                $komentarz .= 'Status transakcji: ' . $status;

                // zapisanie informacji w historii statusow zamowien w przypadku zakonczenia transakcji
                if ( strtoupper((string)$tablica['Response']) == "APPROVED" ) {

                    if ( PLATNOSC_ESERVICE_STATUS_ZAMOWIENIA > 0 ) {
                        $status_zamowienia_id = PLATNOSC_ESERVICE_STATUS_ZAMOWIENIA;
                    } else {
                        $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
                    }
                    //
                    if ( $info['orders_status'] != $status_zamowienia_id ) {
                        //
                        $pola = array(
                                array('orders_id',(int)$nr_tmp[0]),
                                array('orders_status_id',(int)$status_zamowienia_id),
                                array('date_added','now()'),
                                array('customer_notified','0'),
                                array('customer_notified_sms','0'),
                                array('comments',$komentarz)
                        );
                        $GLOBALS['db']->insert_query('orders_status_history' , $pola);
                        unset($pola);
                        //
                    }
                    
                    // zmina statusu zamowienia
                    $pola = array(
                            array('orders_status',(int)$status_zamowienia_id),
                            array('paid_info','1'),
                            array('payment_method_array','#'),
                    );
                    $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$nr_tmp[0] . "'");
                    unset($pola);
                    
                    if ( INTEGRACJA_AUTOMATER_WLACZONY == 'tak' ) {
                         //
                         $IdZamowienia = (int)$nr_tmp[0];
                         $zamowienie = new Zamowienie( $IdZamowienia );
                         //
                         if ( count($zamowienie->info) > 0 ) {
                           
                             if ( $zamowienie->info['automater_id_cart'] > 0 && $zamowienie->info['automater_wyslane'] == 0 ) {
                                  //
                                  include_once 'programy/automater/vendor/autoload.php';

                                  $client = new \AutomaterSDK\Client\Client(INTEGRACJA_AUTOMATER_API, INTEGRACJA_AUTOMATER_API_SECRET);

                                  $paymentRequest = new \AutomaterSDK\Request\PaymentRequest();
                                  $paymentRequest->setPaymentId($zamowienie->info['metoda_platnosci']);
                                  $paymentRequest->setCurrency($zamowienie->info['waluta']);
                                  $paymentRequest->setAmount($zamowienie->info['wartosc_zamowienia_val']);
                                  $paymentRequest->setDescription('Zamówienie nr ' . $zamowienie->info['id_zamowienia']);
                                  $paymentRequest->setCustom('Płatność ze sklepu internetowego - zamówienie ' . $zamowienie->info['id_zamowienia']);

                                  $cartId = $zamowienie->info['automater_id_cart'];

                                  try {
                                      $paymentResponse = $client->postPayment($cartId, $paymentRequest);
                                  } catch (\AutomaterSDK\Exception\UnauthorizedException $exception) {
                                      die('<span style="color:red">Błedny API key</span>');
                                  } catch (\AutomaterSDK\Exception\TooManyRequestsException $exception) {
                                      die('<span style="color:red">Zbyt wiele próśb do Automater: ' . $exception->getMessage() . '</span>');
                                  } catch (\AutomaterSDK\Exception\NotFoundException $exception) {
                                      die('<span style="color:red">Nie znaleziono - nieprawidłowe parametry</span>');
                                  } catch (\AutomaterSDK\Exception\ApiException $exception) {
                                      die($exception->getMessage());
                                  }

                                  $wynikId = $paymentResponse->getCartId(); 

                                  if ( (int)$wynikId > 0 ) {
                                      //
                                      $pola = array(array('automater_id_cart_send', 1));
                                      $GLOBALS['db']->update_query('orders' , $pola, 'orders_id = ' . $IdZamowienia);
                                      unset($pola);                      
                                      //
                                  }
                                  //
                                  unset($cartId, $wynikId);
                                  //                  
                             }
                             
                         }
                         //
                         unset($zamowienie, $IdZamowienia);
                         //          
                    }    

                } else {
                  
                    $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia('ESERVICE');

                    $zapytanie_komentarz = "SELECT comments FROM orders_status_history WHERE comments = '".$komentarz."'";
                    $sql_komentarz = $GLOBALS['db']->open_query($zapytanie_komentarz);

                    if ($GLOBALS['db']->ile_rekordow($sql_komentarz) > 0 ) {
                    } else {
                        //
                        $pola = array(
                                array('orders_id',(int)$nr_tmp[0]),
                                array('orders_status_id',(int)$status_zamowienia_id),
                                array('date_added','now()'),
                                array('customer_notified','0'),
                                array('customer_notified_sms','0'),
                                array('comments',$komentarz)
                        );
                        $GLOBALS['db']->insert_query('orders_status_history' , $pola);
                        unset($pola);
                        //
                        
                        // zmina statusu zamowienia
                        $pola = array(
                                array('orders_status',(int)$status_zamowienia_id),
                        );
                        $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$nr_tmp[0] . "'");
                        unset($pola);
                    }
                    $GLOBALS['db']->close_query($sql_komentarz);
                    unset($zapytanie_komentarz, $sql_komentarz);

                }
                
                if ( $zwroc_info == true ) {
                  
                    echo 'Approved';
                    exit;

                }
                
            }
            
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie);         

        }        
      
    }    

    public static function urlencode_recursive($data) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = Platnoscielektroniczne::urlencode_recursive($value); // zaglebiamy się
            } elseif (is_string($value)) {
                $data[$key] = urlencode(trim($value));
            }
        }
        return $data;
    }

    public static function format_postcode($postcode) {
        // Usuwamy wszystkie znaki poza cyframi
        $digits = preg_replace('/\D/', '', $postcode);

        // Jesli mamy dokladnie 5 cyfr, formatujemy jako NN-NNN
        if (strlen($digits) === 5) {
            return substr($digits, 0, 2) . '-' . substr($digits, 2);
        }

        // Jesli nie spelnia wymagan
        return '';
    }


}

?>