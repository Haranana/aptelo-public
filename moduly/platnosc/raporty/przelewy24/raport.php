<?php
chdir('../../../../');

require_once('ustawienia/init.php');

$e = array();

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_PRZELEWY24_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    if (!defined($info['kod'])) {
        define($info['kod'], $info['wartosc']);
    }
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);

$DanezPrzelewy = array();
$PobraneDane = file_get_contents("php://input");
$DanezPrzelewy = json_decode($PobraneDane);

//####################################################################
/*
$message = "Zawartosc maila\n";
if ( isset($_POST) ) {
    foreach ((array)$DanezPrzelewy as $k => $v) {
        $message .= $k . ': ' . $v . "\n";
    }
    $message .= "INPUT\n\n";
    $message .= $PobraneDane . "\n\n";

    mail('info@oscgold.com', 'Test Przelewy24 - raport', $message);
}
*/
//####################################################################

if ( !isset($DanezPrzelewy->merchantId) || !isset($DanezPrzelewy->sessionId) || !isset($DanezPrzelewy->amount) || !isset($DanezPrzelewy->sign) || !isset($DanezPrzelewy->statement) ) {
    die('ERROR: EMPTY PARAMETERS'); //-- brak wszystkich parametrow
}

if ( $DanezPrzelewy->merchantId != PLATNOSC_PRZELEWY24_ID ) {
    $e[]=1;
}

$sign   = array(
          'merchantId'    => (int)$DanezPrzelewy->merchantId,
          'posId'         => (int)$DanezPrzelewy->posId,
          'sessionId'     => (string)$DanezPrzelewy->sessionId,
          'amount'        => (int)$DanezPrzelewy->amount,
          'originAmount'  => (int)$DanezPrzelewy->originAmount,
          'currency'      => (string)$DanezPrzelewy->currency,
          'orderId'       => (int)$DanezPrzelewy->orderId,
          'methodId'      => (int)$DanezPrzelewy->methodId,
          'statement'     => (string)$DanezPrzelewy->statement,
          'crc'           => (string)PLATNOSC_PRZELEWY24_CRC
);
$string     = json_encode( $sign, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
$p24_sign   = hash( 'sha384', $string );
unset($sign, $string);

if ( $p24_sign != $DanezPrzelewy->sign ) {
    $e[]=2;
}

if ( count($e) > 0 ) {

    print "AP-OSC PROBLEM: $e[0]";
    exit;

} else {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
    curl_setopt($ch, CURLOPT_URL, "https://" . ( PLATNOSC_PRZELEWY24_SANDBOX == '1' ? 'sandbox.przelewy24.pl' : 'secure.przelewy24.pl' ) . "/api/v1/payment/methods/pl");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, PLATNOSC_PRZELEWY24_ID.":".PLATNOSC_PRZELEWY24_API_KEY);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $kanaly_json = curl_exec($ch);
    curl_close($ch);

    $kanaly = json_decode($kanaly_json);

    $SlownikKanalow = array();

    foreach ( $kanaly->data as $kanal ) {

        echo $kanal->id . ' - ' . $kanal->name . '<br>';
        $SlownikKanalow[$kanal->id] = $kanal->name;
    }

    $KwotaPlatnosci = $DanezPrzelewy->amount / 100;
    $KwotaPlatnosci = number_format($KwotaPlatnosci, 2, ".", "");

    $komentarz = 'Numer transakcji: ' . $DanezPrzelewy->orderId . '<br />';
    $komentarz .= 'Metoda płatności: ' . ( isset($SlownikKanalow[$DanezPrzelewy->methodId]) ? $SlownikKanalow[$DanezPrzelewy->methodId] : $DanezPrzelewy->methodId ) . '<br />';
    $komentarz .= 'Tytuł transakcji: ' . $DanezPrzelewy->statement . '<br />';
    $komentarz .= 'Kwota transakcji: ' . number_format($KwotaPlatnosci, 2, ".", "") . '<br />';
    $komentarz .= 'Waluta transakcji: ' . $DanezPrzelewy->currency . '<br />';
    $komentarz .= 'Data transakcji: ' . date("d-m-Y H:i:s") . '<br />';

    $zapytanie = "SELECT orders_id FROM orders WHERE p24_session_id = '" . $DanezPrzelewy->sessionId . "'";

    $sql = $db->open_query($zapytanie);

    if ($GLOBALS['db']->ile_rekordow($sql) > 0 ) {

        $info = $sql->fetch_assoc();

        if ( PLATNOSC_PRZELEWY24_STATUS_ZAMOWIENIA > 0 ) {
            $status_zamowienia_id = PLATNOSC_PRZELEWY24_STATUS_ZAMOWIENIA;
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
                array('transaction_id',$DanezPrzelewy->orderId),
                array('transaction_date',date('Y-m-d H:i:s',time())),
                array('transaction_status',$DanezPrzelewy->statement)
        );
        $GLOBALS['db']->insert_query('orders_status_history' , $pola);
        unset($pola);

        // zmina statusu zamowienia
        $pola = array(
                array('orders_status',(int)$status_zamowienia_id),
                array('paid_info','1'),
                array('payment_method_array','#'),
        );
        $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$info['orders_id'] . "'");
        unset($pola);
        
        $platnoscZakonczona = true;
        $IdZamowienia = (int)$info['orders_id'];

    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $sql);


    $signVerify   = array(
                      'sessionId'    => (string)$DanezPrzelewy->sessionId,
                      'orderId'      => (int)$DanezPrzelewy->orderId,
                      'amount'       => (int)$DanezPrzelewy->amount,
                      'currency'     => (string)$DanezPrzelewy->currency,
                      'crc'          => (string)PLATNOSC_PRZELEWY24_CRC
    );
    $stringVerify     = json_encode( $signVerify, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
    $p24_signVerify   = hash( 'sha384', $stringVerify );
    unset($signVerify, $stringVerify);

    $DaneWejsciowe = array(
                     "merchantId"     => (int)PLATNOSC_PRZELEWY24_ID,
                     "posId"          => (int)PLATNOSC_PRZELEWY24_ID,
                     "sessionId"      => (string)$DanezPrzelewy->sessionId,
                     "amount"         => (int)$DanezPrzelewy->amount,
                     "currency"       => (string)$DanezPrzelewy->currency,
                     "orderId"        => (int)$DanezPrzelewy->orderId,
                     "sign"           => (string)$p24_signVerify
    );

    $headers = [
        'Content-Type: application/json'
    ];

    $data_json = json_encode($DaneWejsciowe, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_URL, "https://" . ( PLATNOSC_PRZELEWY24_SANDBOX == '1' ? 'sandbox.przelewy24.pl' : 'secure.przelewy24.pl' ) . "/api/v1/transaction/verify");
    curl_setopt($ch, CURLOPT_USERPWD, PLATNOSC_PRZELEWY24_ID.":".PLATNOSC_PRZELEWY24_API_KEY);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);    
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $WynikJson = curl_exec($ch);

    curl_close($ch);

    if ( isset($platnoscZakonczona) && $platnoscZakonczona == true ) {

        if ( INTEGRACJA_AUTOMATER_WLACZONY == 'tak' ) {
             //
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

    exit;

}

?>