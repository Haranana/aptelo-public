<?php
chdir('../../../../');

require_once('ustawienia/init.php');

$komentarz = '';
$platnoscZakonczona = false;

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_COMFINO_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    if (!defined($info['kod'])) {
        define($info['kod'], $info['wartosc']);
    }
}

$GLOBALS['db']->close_query($sql);

unset($zapytanie, $info, $sql);

if ( PLATNOSC_COMFINO_SANDBOX == "1" ) {
    $api_url = 'https://api-ecommerce.ecraty.pl/v1/';
} else {
    $api_url = 'https://api-ecommerce.comfino.pl/v1/';
}

$body = file_get_contents('php://input');

if (!valid_signature($body)) {

    exit;

} else {

    $data = json_decode($body, true);

    $komentarz .= 'Data transakcji: ' . date('d-m-Y H:i:s', $data['changedAt']) . '<br />';

    $zapytanie = "SELECT orders_id FROM orders WHERE orders_id = '" . (int)$data['externalId'] . "' ORDER BY date_purchased DESC LIMIT 1";
    $sql = $db->open_query($zapytanie);

    if ($GLOBALS['db']->ile_rekordow($sql) > 0 ) {

        $info = $sql->fetch_assoc();

        //dla wyplaty na konto
        if ( $data["status"] == "PAID" ) {

            $komentarz .= 'Status wniosku Comfino: ' . $data['status'] . '<br />';

            if ( PLATNOSC_COMFINO_STATUS_ZAMOWIENIA_PAID > 0 ) {
                $status_zamowienia_id = PLATNOSC_COMFINO_STATUS_ZAMOWIENIA_PAID;
            } else {
                $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
            }

            $pola = array(
                array('orders_id',(int)$info['orders_id'] ),
                array('orders_status_id',(int)$status_zamowienia_id ),
                array('date_added','now()'),
                array('customer_notified','0'),
                array('customer_notified_sms','0'),
                array('comments',$komentarz),
                array('transaction_id',''),
                array('transaction_date',date('d-m-Y H:i:s', $data['changedAt'])),
                array('transaction_status',$data["status"])   
            );
            $db->insert_query('orders_status_history' , $pola);
            unset($pola);

            // zmina statusu zamowienia
            $pola = array(
                array('orders_status',(int)$status_zamowienia_id ),
                array('paid_info','1'),
                array('payment_method_array','#'),
            );
            $db->update_query('orders' , $pola, "orders_id = '" . (int)$info['orders_id'] . "'");
            unset($pola);
                
            $platnoscZakonczona = true;
        }

        //dla akceptacji
        if ( $data["status"] == "ACCEPTED" || $data["status"] == "WAITING_FOR_PAYMENT" ) {

            $komentarz .= 'Status wniosku Comfino: ' . $data['status'] . '<br />';

            if ( PLATNOSC_COMFINO_STATUS_ZAMOWIENIA_ACCEPTED > 0 ) {
                $status_zamowienia_id = PLATNOSC_COMFINO_STATUS_ZAMOWIENIA_ACCEPTED;
            } else {
                $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
            }

            $pola = array(
                array('orders_id',(int)$info['orders_id'] ),
                array('orders_status_id',(int)$status_zamowienia_id ),
                array('date_added','now()'),
                array('customer_notified','0'),
                array('customer_notified_sms','0'),
                array('comments',$komentarz),
                array('transaction_id',''),
                array('transaction_date',date('d-m-Y H:i:s', $data['changedAt'])),
                array('transaction_status',$data["status"])   
            );
            $db->insert_query('orders_status_history' , $pola);
            unset($pola);

            // zmina statusu zamowienia
            $pola = array(
                array('orders_status',(int)$status_zamowienia_id ),
                array('payment_method_array','#'),
            );
            $db->update_query('orders' , $pola, "orders_id = '" . (int)$info['orders_id'] . "'");
            unset($pola);
                
            $platnoscZakonczona = true;
        }

        //dla CANCELLED, REJECTED
        if ( $data["status"] == "CANCELLED" || $data["status"] == "REJECTED" || $data["status"] == "RESIGN" ) {

            $komentarz .= 'Status wniosku Comfino: ' . $data['status'] . '<br />';

            if ( PLATNOSC_COMFINO_STATUS_ZAMOWIENIA_ODMOWA > 0 ) {
                $status_zamowienia_id = PLATNOSC_COMFINO_STATUS_ZAMOWIENIA_ODMOWA;
            } else {
                $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
            }


            $pola = array(
                array('orders_id',(int)$info['orders_id'] ),
                array('orders_status_id',(int)$status_zamowienia_id ),
                array('date_added','now()'),
                array('customer_notified','0'),
                array('customer_notified_sms','0'),
                array('comments',$komentarz)
            );
            $db->insert_query('orders_status_history' , $pola);
            unset($pola);

            // zmina statusu zamowienia
            $pola = array(
                array('orders_status',(int)$status_zamowienia_id ),
                array('paid_info','0'),
                array('payment_method_array','#'),
            );
            $db->update_query('orders' , $pola, "orders_id = '" . (int)$info['orders_id'] . "'");
            unset($pola);
        }

    }

}

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

function get_signature() {

    $signature = '';

    foreach ($_SERVER as $key => $value) {
        if ($key === 'HTTP_CR_SIGNATURE') {
            $signature = $value;
            break;
        }
    }

    return $signature;
}

function valid_signature( $jsonData ) {
    return get_signature() === hash('sha3-256', PLATNOSC_COMFINO_KLUCZ . $jsonData);
}

?>