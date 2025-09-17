<?php
chdir('../../../../');

require_once('ustawienia/init.php');

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_BLUEMEDIA_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    if (!defined($info['kod'])) {
        define($info['kod'], $info['wartosc']);
    }
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);

if ( isset($_POST) && !empty($_POST['transactions'])) {

    //domyślnie status niepotwierdzony
    $status = "NOTCONFIRMED";

    //zbieram informacje o itnie
    $transaction = base64_decode((string)$_POST['transactions']);
    $transaction_xml = simplexml_load_string($transaction);

    //przygotowuję dane do zapisu do bazy
    $transaction_array = (array)$transaction_xml->transactions->transaction;

    $time = FunkcjeWlasnePHP::my_strtotime($transaction_array['paymentDate']);

    $komentarz = 'Numer transakcji: ' . $transaction_array['remoteID'] . '<br />';
    $komentarz .= 'Kwota: ' . $transaction_array['amount'] . ' ' . $transaction_array['currency'] . '<br />';
    $komentarz .= 'Data transakcji: ' . date('Y-m-d H:i:s', $time) . '<br />';
    $komentarz .= 'Status transakcji: ' . $transaction_array['paymentStatus'] . '<br />';
    if ( isset($transaction_array['paymentStatusDetails']) && $transaction_array['paymentStatusDetails'] != '' ) {
        $komentarz .= 'Szczegoly transakcji: ' . $transaction_array['paymentStatusDetails'];
    }

    $zapytanie = "SELECT orders_id FROM orders WHERE orders_id = '" . (int)$transaction_array['orderID'] . "' ORDER BY date_purchased DESC LIMIT 1";

    $sql = $db->open_query($zapytanie);

    if ($GLOBALS['db']->ile_rekordow($sql) > 0 ) {

      $info = $sql->fetch_assoc();

      if ($transaction_array['paymentStatus'] == 'SUCCESS') {

        if ( PLATNOSC_BLUEMEDIA_STATUS_ZAMOWIENIA > 0 ) {
            $status_zamowienia_id = PLATNOSC_BLUEMEDIA_STATUS_ZAMOWIENIA;
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
                array('transaction_id',$transaction_array['remoteID']),
                array('transaction_date',date('Y-m-d H:i:s', $time)),
                array('transaction_status',$transaction_array['paymentStatus'])
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

      } else {

        $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia('BLUEMEDIA');
        $pola = array(
                array('orders_id',(int)$info['orders_id']),
                array('orders_status_id',(int)$status_zamowienia_id),
                array('date_added','now()'),
                array('customer_notified','0'),
                array('customer_notified_sms','0'),
                array('comments',$komentarz),
                array('transaction_id',$transaction_array['remoteID']),
                array('transaction_date',date('Y-m-d H:i:s', $time)),
                array('transaction_status',$transaction_array['paymentStatus'])
        );
        $GLOBALS['db']->insert_query('orders_status_history' , $pola);
        unset($pola);

      }

      $status = "CONFIRMED";

    }
    $GLOBALS['db']->close_query($sql);

    unset($zapytanie, $info, $sql);

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

    //przygotowuję XMLa
    $service_id = PLATNOSC_BLUEMEDIA_IDSKLEPU;
    $order_id = $transaction_array['orderID'];
    $key = PLATNOSC_BLUEMEDIA_KLUCZ;
    $separator = '|';
    $encrypt_method = 'sha256';

    //przygtowuję hash
    $hash_data = array(
        'service_id' => $service_id,
        'order_id' => $order_id,
        'status' => $status,
        'key' => $key
    );

    $hash = hash(strtolower($encrypt_method), implode((string)$separator, (array)$hash_data));
    $xml = new SimpleXMLElement('<confirmationList/>');

    $xml_service_id = $xml->addChild('serviceID',$service_id);

    $xml_transactionsConfirmations = $xml->addChild('transactionsConfirmations')->addChild('transactionConfirmed');
    $xml_transactionsConfirmations_transactionConfirmed = $xml_transactionsConfirmations->addChild('orderID',$order_id);
    $xml_transactionsConfirmations_transactionConfirmed = $xml_transactionsConfirmations->addChild('confirmation',$status);

    $xml->addChild('hash',$hash);

    Header('Content-type: text/xml');
    print($xml->asXML());

} else {
    print "AP-OSC PROBLEM:";
    exit;
}
?>