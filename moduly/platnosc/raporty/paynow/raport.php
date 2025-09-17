<?php
chdir('../../../../');

require_once('ustawienia/init.php');

$e = array();

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_PAYNOW_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    if (!defined($info['kod'])) {
        define($info['kod'], $info['wartosc']);
    }
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);

$key = PLATNOSC_PAYNOW_SIGNATURE_KEY;
$message = '';

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

$headers =  getallheaders();

if ( isset($headers['signature']) ) {
    $headers['Signature'] = $headers['signature'];
}

if ( isset($headers['Signature']) ) {
    //$message .= 'Sygnatura przeslana : ' . $headers['Signature'] . "\n\n";
} else {
    print "AP-OSC PROBLEM:";
    exit;
}

$PobraneDane = file_get_contents("php://input");
$data = json_decode($PobraneDane,true);

$sygnatura = base64_encode(hash_hmac("sha256", json_encode($data, JSON_UNESCAPED_SLASHES), $key, true));

if ( $sygnatura != $headers['Signature'] ) {

    print "AP-OSC PROBLEM:";
    exit;

} else {

    $komentarz = 'Numer transakcji: ' . $data['paymentId'] . '<br />';
    $komentarz .= 'Data transakcji: ' . $data['modifiedAt'] . '<br />';
    $komentarz .= 'Status transakcji: ' . $data['status'];

    $zapytanie = "SELECT orders_id FROM orders WHERE orders_id = '" . (int)$data['externalId'] . "' ORDER BY date_purchased DESC LIMIT 1";
    $sql = $db->open_query($zapytanie);

    if ($GLOBALS['db']->ile_rekordow($sql) > 0 ) {

        $info = $sql->fetch_assoc();

        if ( $data['status'] == 'CONFIRMED' ) {

            if ( PLATNOSC_PAYNOW_STATUS_ZAMOWIENIA > 0 ) {
                $status_zamowienia_id = PLATNOSC_PAYNOW_STATUS_ZAMOWIENIA;
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
                    array('transaction_id',$data['paymentId']),
                    array('transaction_date',date('Y-m-d H:i:s',strtotime($data['modifiedAt']))),
                    array('transaction_status',$data['status'])
            );
            $GLOBALS['db']->insert_query('orders_status_history' , $pola);
            unset($pola);

            // zmina statusu zamowienia
            $pola = array(
                    array('orders_status',(int)$status_zamowienia_id),
                    array('paid_info','1'),
                    array('payment_method_array','#'),
                    array('paynow_idempotency',''),
            );
            $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$info['orders_id'] . "'");
            unset($pola);
            
            $platnoscZakonczona = true;

        } elseif ( $data['status'] == 'NEW' || $data['status'] == 'REJECTED' || $data['status'] == 'PENDING' ) {

            $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia('PAYNOW');
            $pola = array(
                    array('orders_id',(int)$info['orders_id']),
                    array('orders_status_id',(int)$status_zamowienia_id),
                    array('date_added','now()'),
                    array('customer_notified','0'),
                    array('customer_notified_sms','0'),
                    array('comments',$komentarz),
                    array('transaction_id',$data['paymentId']),
                    array('transaction_date',date('Y-m-d H:i:s',strtotime($data['modifiedAt']))),
                    array('transaction_status',$data['status'])                    
            );
            $GLOBALS['db']->insert_query('orders_status_history' , $pola);
            unset($pola);

            if ( $data['status'] == 'REJECTED' ) {

                $parametry = unserialize($info['payment_method_array']);

                $parametry['status'] = 'REJECTED';
                $update              = serialize($parametry);

                $pola = array(
                        array('payment_method_array',$update)
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

?>