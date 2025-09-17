<?php
chdir('../../../../');

require_once('ustawienia/init.php');

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_PAYPAL_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    if (!defined($info['kod'])) {
        define($info['kod'], $info['wartosc']);
    }
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);

include('moduly/platnosc/raporty/paypal/ipnlistener.php');
$listener = new IpnListener();

if ( PLATNOSC_PAYPAL_SANDBOX == '1' ) {
    $listener->use_sandbox = true;
} else {
    $listener->use_sandbox = false;
}

try {
    $listener->requirePostMethod();
    $verified = $listener->processIpn();
} catch (Exception $e) {
    exit(0);
}

$parameters = array();
$parameters = $listener->getTextReport();
$status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia('PAYPAL');
$komentarz = '';

if ($verified) {
    foreach ( $parameters as $key => $value ) {
        $raport .= $key . '=' . $value . "\n";
    }

    $komentarz  .= 'Numer transakcji: ' . $parameters['txn_id'] . '<br />';
    $komentarz  .= 'Status transakcji: ' . $parameters['payment_status'] . '<br />';
    $komentarz  .= 'Data transakcji: ' . $parameters['payment_date'] . '<br />';
    $komentarz  .= 'Kwota wpłaty: ' . $parameters['mc_gross'] . ' ' .  $parameters['mc_currency'];

    if ($parameters['payment_status'] == 'Pending') {
        $komentarz .= '<br />' . $parameters['pending_reason'];
    } elseif ( ($parameters['payment_status'] == 'Reversed') || ($parameters['payment_status'] == 'Refunded') ) {
        $komentarz .= '<br />' . $parameters['reason_code'];
    }

    if ( ($parameters['payment_status'] == 'Completed' || $parameters['payment_status'] == 'Zakończona' ) && PLATNOSC_PAYPAL_STATUS_ZAMOWIENIA > 0 ) {
        $status_zamowienia_id = PLATNOSC_PAYPAL_STATUS_ZAMOWIENIA;
    }

    $pola = array(
            array('orders_id',(int)$parameters['invoice']),
            array('orders_status_id',(int)$status_zamowienia_id),
            array('date_added','now()'),
            array('customer_notified','0'),
            array('customer_notified_sms','0'),
            array('comments',$komentarz),
            array('transaction_id',$parameters['txn_id']),
            array('transaction_date',date('Y-m-d H:i:s',strtotime($parameters['payment_date']))),
            array('transaction_status',$parameters['payment_status'])
    );
    $GLOBALS['db']->insert_query('orders_status_history' , $pola);
    unset($pola);

    // zmina statusu zamowienia
    $pola = array(
            array('orders_status',(int)$status_zamowienia_id),
            array('payment_method_array','#'),
    );
    $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$parameters['invoice'] . "'");
    unset($pola);

    if ( INTEGRACJA_AUTOMATER_WLACZONY == 'tak' ) {
         //
         $IdZamowienia = (int)$parameters['invoice'];
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

} else {

    reset($parameters);

    $komentarz  .= 'Numer transakcji: ' . $parameters['txn_id'] . '<br />';
    $komentarz  .= 'Status transakcji: ' . $parameters['payment_status'] . '<br />';
    $komentarz  .= 'Data transakcji: ' . $parameters['payment_date'] . '<br />';
    $komentarz  .= 'Uwagi: ' . $parameters['pending_reason'] . ' ' .  $parameters['reason_code'];


    $pola = array(
            array('orders_id',(int)$parameters['invoice']),
            array('orders_status_id',(int)$status_zamowienia_id),
            array('date_added','now()'),
            array('customer_notified','0'),
            array('customer_notified_sms','0'),
            array('comments',$komentarz),
            array('transaction_id',$parameters['txn_id']),
            array('transaction_date',date('Y-m-d H:i:s',strtotime($parameters['payment_date']))),
            array('transaction_status',$parameters['payment_status'])
    );

    $GLOBALS['db']->insert_query('orders_status_history' , $pola);
    unset($pola);

}

?>
