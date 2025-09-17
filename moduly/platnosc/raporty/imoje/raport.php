<?php
chdir('../../../../');

require_once('ustawienia/init.php');

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_IMOJE_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    if (!defined($info['kod'])) {
        define($info['kod'], $info['wartosc']);
    }
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);


if (isset($_SERVER['CONTENT_TYPE'], $_SERVER['HTTP_X_IMOJE_SIGNATURE']) && strpos((string)$_SERVER['CONTENT_TYPE'], 'application/json') === 0 ) {

        $header = $_SERVER['HTTP_X_IMOJE_SIGNATURE'];
        $header = (explode(';', (string)$header));

        $headerSignature = explode('=', (string)$header[2]);
        $headerSignature = $headerSignature[1];

        $algoFromNotification = explode('=', (string)$header[3]);
        $algoFromNotification = $algoFromNotification[1];

        $payload = file_get_contents('php://input', true);

        $ownSignature = hash($algoFromNotification, $payload . PLATNOSC_IMOJE_PRIVATE_KEY);

        if ($headerSignature === $ownSignature ) {

          $payloadDecoded = json_decode($payload, true);

          if ( isset($payloadDecoded['transaction']['orderId']) && is_numeric($payloadDecoded['transaction']['orderId']) && ($payloadDecoded['transaction']['orderId'] > 0) ) {

            $komentarz = '';
            $status = get_status($payloadDecoded['transaction']['status']);

            $KwotaPlatnosci = $payloadDecoded['transaction']['amount'] / 100;
            $KwotaPlatnosci = number_format($KwotaPlatnosci, 2, ".", "");

            $komentarz = 'Numer transakcji: ' . $payloadDecoded['transaction']['id'] . '<br />';
            if ( isset($payloadDecoded['transaction']['type']) ) {
                if ( $payloadDecoded['transaction']['type'] == 'sale' ) {
                    $komentarz .= 'Rodzaj operacji: WPŁATA<br />';
                } elseif ( $payloadDecoded['transaction']['type'] == 'refund' ){
                    $komentarz .= 'Rodzaj operacji: ZWROT<br />';
                }
            }
            $komentarz .= 'Data utworzenia zamówienia: ' . date('d-m-Y H:i:s', $payloadDecoded['transaction']['created']) . '<br />';
            $komentarz .= 'Data ostatniej zmiany statusu transakcji: ' . date('d-m-Y H:i:s', $payloadDecoded['transaction']['modified']) . '<br />';
            $komentarz .= 'Kwota transakcji: ' . $KwotaPlatnosci . ' ' . $payloadDecoded['transaction']['currency'] . '<br />';
            $komentarz .= 'Status transakcji: ' . $status['message'] . '<br />';
            $komentarz .= 'Metoda: ' . $payloadDecoded['transaction']['paymentMethod'] . '<br />';
            $komentarz .= 'Kanał: ' . $payloadDecoded['transaction']['paymentMethodCode'];

            // zapisanie informacji w historii statusow zamowien w przypadku zakonczenia tranzkcji
            if ( $payloadDecoded['transaction']['status'] == 'settled' ) {

                if ( PLATNOSC_IMOJE_STATUS_ZAMOWIENIA > 0 ) {
                    $status_zamowienia_id = PLATNOSC_IMOJE_STATUS_ZAMOWIENIA;
                } else {
                    $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
                }
                //
                $pola = array(
                        array('orders_id',(int)$payloadDecoded['transaction']['orderId']),
                        array('orders_status_id',(int)$status_zamowienia_id),
                        array('date_added','now()'),
                        array('customer_notified','0'),
                        array('customer_notified_sms','0'),
                        array('comments',$komentarz),
                        array('transaction_id',$payloadDecoded['transaction']['id']),
                        array('transaction_date',date('Y-m-d H:i:s',$payloadDecoded['transaction']['modified'])),
                        array('transaction_status',$status['message'])
                );
                $GLOBALS['db']->insert_query('orders_status_history' , $pola);
                unset($pola);

                // zmina statusu zamowienia
                $pola = array(
                        array('orders_status',(int)$status_zamowienia_id),
                        array('paid_info','1'),
                        array('payment_method_array','#'),
                );
                $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$payloadDecoded['transaction']['orderId'] . "'");
                unset($pola);
                
                if ( INTEGRACJA_AUTOMATER_WLACZONY == 'tak' ) {
                     //
                     $IdZamowienia = (int)$payloadDecoded['transaction']['orderId'];
                     $zamowienie = new Zamowienie( $IdZamowienia );
                     //
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
                     //
                     unset($zamowienie, $IdZamowienia);
                     //          
                }              
                
            }

            // zapisanie informacji w historii statusow zamowien w przypadku anulowania lub odrzucenia tranzakcji
            if ( $payloadDecoded['transaction']['status'] == 'pending' || $payloadDecoded['transaction']['status'] == 'rejected' || $payloadDecoded['transaction']['status'] == 'error' || $payloadDecoded['transaction']['status'] == 'cancelled' ) {

                $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia('IMOJE');

                $zapytanie = "SELECT comments  FROM orders_status_history WHERE comments = '".$komentarz."'";
                $sql = $db->open_query($zapytanie);

                if ($GLOBALS['db']->ile_rekordow($sql) > 0 ) {
                } else {
                    //
                    $pola = array(
                            array('orders_id',(int)$payloadDecoded['transaction']['orderId']),
                            array('orders_status_id',(int)$status_zamowienia_id),
                            array('date_added','now()'),
                            array('customer_notified','0'),
                            array('customer_notified_sms','0'),
                            array('comments',$komentarz),
                            array('transaction_id',$payloadDecoded['transaction']['id']),
                            array('transaction_date',date('Y-m-d H:i:s',$payloadDecoded['transaction']['modified'])),
                            array('transaction_status',$status['message'])
                    );
                    $GLOBALS['db']->insert_query('orders_status_history' , $pola);
                    unset($pola);

                    // zmina statusu zamowienia
                    $pola = array(
                            array('orders_status',(int)$status_zamowienia_id),
                    );
                    $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$payloadDecoded['transaction']['orderId'] . "'");
                    unset($pola);
                }
                $GLOBALS['db']->close_query($sql);
                unset($zapytanie, $sql);

            }

          }

          echo 'OK';
          exit;

        } else {
          header("HTTP/1.0 404 Not Found");
          echo 'BLAD';
        }

} else {
    header("HTTP/1.0 404 Not Found");
    echo 'BLAD';
}

function get_status($status){

  switch ($status) {
    case 'settled': return array('code' => $status, 'message' => 'zaakceptowana'); break;
    case 'pending': return array('code' => $status, 'message' => 'oczekująca'); break;
    case 'rejected': return array('code' => $status, 'message' => 'odrzucona'); break;
    case 'cancelled': return array('code' => $status, 'message' => 'anulowana'); break;
    case 'error': return array('code' => $status, 'message' => 'błąd w serwisie'); break;
    default: return array('code' => false, 'message' => 'brak statusu'); break;
  }
}

?>