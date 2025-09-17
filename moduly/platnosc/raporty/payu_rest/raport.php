<?php
chdir('../../../../');
require_once('ustawienia/init.php');

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_PAYU_REST_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    define($info['kod'], $info['wartosc']);
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);

$message = '';
$Sygnatura = '';

if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr((string)$name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr((string)$name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

$headers =  array_change_key_case(getallheaders(), CASE_LOWER);

// Ustalenie przeslanej sygnatury
if ( isset($headers['openpayu-signature']) ) {
    $tablica = explode(';', $headers['openpayu-signature']);
    $Sygnatura = substr($tablica['1'], strpos($tablica['1'], "=") + 1);
    unset($tablica);
}

$PobraneDane = file_get_contents("php://input");
$DanezPayU = json_decode($PobraneDane);

if ( isset($DanezPayU->order) ) {

    if ( $DanezPayU->order->currencyCode == 'EUR' ) {
        $key = PLATNOSC_PAYU_REST_POS_AUTH_KEY_EUR;
    } else {
        $key = PLATNOSC_PAYU_REST_POS_AUTH_KEY;
    }

    $concatenated = $PobraneDane.$key;
    $sygnaturaWyliczona = md5($concatenated);

    if ( $sygnaturaWyliczona != $Sygnatura ) {

        print "AP-OSC PROBLEM: NIEWLASCIWA SYGNATURA";
        exit;

    } else {

        $komentarz = '';
        $zamowienie = array();

        $zamowienie = explode(':', $DanezPayU->order->extOrderId);

        $KwotaPlatnosci = $DanezPayU->order->totalAmount / 100;
        $KwotaPlatnosci = number_format($KwotaPlatnosci, 2, ".", "");

        $komentarz .= 'ID transakcji : ' . $DanezPayU->order->orderId . '<br />';
        $komentarz .= 'Data utworzenia : ' . date('Y-m-d G:i:s',strtotime($DanezPayU->order->orderCreateDate)) . '<br />';
        $komentarz .= 'Kwota płatności : ' . $KwotaPlatnosci . '<br />';
        $komentarz .= 'Waluta : ' . $DanezPayU->order->currencyCode . '<br />';
        if ( isset($DanezPayU->order->payMethod->type) ) {
            $komentarz .= 'Metoda płatności : ' . $DanezPayU->order->payMethod->type . '<br />';
        }
        $komentarz .= 'Status płatności : ' . $DanezPayU->order->status . '<br />';

        $zapytanie = "SELECT orders_id, payment_method_array FROM orders WHERE orders_id = '" . (int)$zamowienie['0'] . "' ORDER BY date_purchased DESC LIMIT 1";
        $sql = $db->open_query($zapytanie);

        if ($GLOBALS['db']->ile_rekordow($sql) > 0 ) {

            $info = $sql->fetch_assoc();

            if ( $DanezPayU->order->status == 'COMPLETED' ) {

                if ( isset($DanezPayU->properties) ) {
                    foreach ($DanezPayU->properties as $Tablica) {
                       $komentarz .= $Tablica->name . ' : ' . $Tablica->value . "\n";
                    }
                }
                if ( PLATNOSC_PAYU_REST_STATUS_ZAMOWIENIA > 0 ) {
                    $status_zamowienia_id = PLATNOSC_PAYU_REST_STATUS_ZAMOWIENIA;
                } else {
                    $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
                }

                $pola = array(
                        array('orders_id',(int)$info['orders_id']),
                        array('orders_status_id',(int)$status_zamowienia_id),
                        array('date_added','now()'),
                        array('customer_notified','0'),
                        array('customer_notified_sms','0'),
                        array('comments',$komentarz),
                        array('transaction_id',$DanezPayU->order->orderId),
                        array('transaction_date',date('Y-m-d G:i:s',strtotime($DanezPayU->order->orderCreateDate))),
                        array('transaction_status',$DanezPayU->order->status)
                );
                $GLOBALS['db']->insert_query('orders_status_history' , $pola);
                unset($pola);

                // zmiana statusu zamowienia
                $pola = array(
                        array('orders_status',(int)$status_zamowienia_id),
                        array('paid_info','1'),
                        array('payment_method_array','#')
                );
                $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$info['orders_id'] . "'");
                unset($pola);
                
                $platnoscZakonczona = true;

            } elseif ( ( $DanezPayU->order->status == 'CANCELED' || $DanezPayU->order->status == 'PENDING' ) && $info['payment_method_array'] != '#' ) {

                $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();

                $pola = array(
                        array('orders_id',(int)$info['orders_id']),
                        array('orders_status_id',(int)$status_zamowienia_id),
                        array('date_added','now()'),
                        array('customer_notified','0'),
                        array('customer_notified_sms','0'),
                        array('comments',$komentarz),
                        array('transaction_id',$DanezPayU->order->orderId),
                        array('transaction_date',date('Y-m-d G:i:s',strtotime($DanezPayU->order->orderCreateDate))),
                        array('transaction_status',$DanezPayU->order->status)
                );
                $GLOBALS['db']->insert_query('orders_status_history' , $pola);
                unset($pola);

                // zmiana statusu zamowienia
                $pola = array(
                        array('orders_status',(int)$status_zamowienia_id),
                );
                $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$info['orders_id'] . "'");
                unset($pola);

                if ( $DanezPayU->order->status == 'CANCELED' ) {
                    // zmiana statusu zamowienia
                    $pola = array(
                            array('payment_method_array','#')
                    );
                    $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$info['orders_id'] . "'");
                    unset($pola);
                }


            }

        }
        $GLOBALS['db']->close_query($sql);
        
        if ( isset($platnoscZakonczona) && $platnoscZakonczona == true ) {

            if ( INTEGRACJA_AUTOMATER_WLACZONY == 'tak' ) {
                 //
                 $IdZamowienia = (int)$info['orders_id'];
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

        unset($zapytanie, $info, $sql);

        echo 'OK';

    }

} else {
    print "AP-OSC PROBLEM: BRAK DANYCH";
    exit;
}

?>