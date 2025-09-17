<?php
chdir('../../../../');

require_once('ustawienia/init.php');

$e = array();

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_PAYEEZY_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    if (!defined($info['kod'])) {
        define($info['kod'], $info['wartosc']);
    }
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);

if ( !isset($_POST['pos_id']) || !isset($_POST['controlData']) || !isset($_POST['order_id']) ) {
    die('ERROR: EMPTY PARAMETERS'); //-- brak wszystkich parametrow
}

if ( $_POST['pos_id'] != PLATNOSC_PAYEEZY_POS_ID ) {
    $e[]=1;
}


$controlData = '';
$hash = '';
$salt = PLATNOSC_PAYEEZY_SHARED_KEY;
$parameters                     = array();

$parameters['pos_id']           = ( isset($_POST['pos_id']) ? $_POST['pos_id'] : '' );
$parameters['order_id']         = ( isset($_POST['order_id']) ? $_POST['order_id'] : '' );
$parameters['session_id']       = ( isset($_POST['session_id']) ? $_POST['session_id'] : '' );
$parameters['amount']           = ( isset($_POST['amount']) ? $_POST['amount'] : '' );
$parameters['response_code']    = ( isset($_POST['response_code']) ? $_POST['response_code'] : '' );
$parameters['transaction_id']   = ( isset($_POST['transaction_id']) ? $_POST['transaction_id'] : '' );
$parameters['cc_number_hash']   = ( isset($_POST['cc_number_hash']) ? $_POST['cc_number_hash'] : '' );
$parameters['bin']              = ( isset($_POST['bin']) ? $_POST['bin'] : '' );
$parameters['card_type']        = ( isset($_POST['card_type']) ? $_POST['card_type'] : '' );
$parameters['auth_code']        = ( isset($_POST['auth_code']) ? $_POST['auth_code'] : '' );

foreach ( $parameters as $key => $value ) {
    if ( $value != '' ) {
        $controlData .= $key .'=' . $value . '&';
    }
}
$controlData = substr((string)$controlData, 0, -1);

$saltTab = str_split($salt);
$hexLenght = strlen((string)$salt);

$saltBin = '';

for ( $x=1; $x <= $hexLenght/2; $x++ ) {
    $saltBin .= ( pack("H*", substr((string)$salt,2 * $x -2,2)) );
}

$hash = hash("sha256", $controlData.$saltBin);

if ( $hash != $_POST['controlData'] ) {
    $e[]=2;
}

if ( count($e) > 0 ) {

    $message = '';

    foreach ($_POST as $k => $v) {
        $message .= $k . ' ' . $v . "\n";
    }
    //mail('info@oscgold.com', 'Test Payeezy - raport', $message);

    print "AP-OSC PROBLEM: $e[0]";
    exit;

} else {

    $status = get_status($_POST['response_code']);

    $komentarz = 'Numer transakcji: ' . $_POST['transaction_id'] . '<br />';
    $komentarz .= 'Data transakcji: ' . date("d-m-Y H:i:s") . '<br />';
    $komentarz .= 'Status transakcji: ' . $status['message'];

    $zapytanie = "SELECT orders_id FROM orders WHERE orders_id = '" . $_POST['order_id'] . "'";
    $sql = $db->open_query($zapytanie);
    if ($GLOBALS['db']->ile_rekordow($sql) > 0 ) {
        $info = $sql->fetch_assoc();
        if ( $_POST['response_code'] == '35' || $_POST['response_code'] == '30' || $_POST['response_code'] == '50' ) {

            if ( PLATNOSC_PAYEEZY_STATUS_ZAMOWIENIA > 0 ) {
                $status_zamowienia_id = PLATNOSC_PAYEEZY_STATUS_ZAMOWIENIA;
            } else {
                $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
            }

            $pola = array(
                    array('orders_id',(int)$_POST['order_id']),
                    array('orders_status_id',(int)$status_zamowienia_id),
                    array('date_added','now()'),
                    array('customer_notified','0'),
                    array('customer_notified_sms','0'),
                    array('comments',$komentarz),
                    array('transaction_id',$_POST['transaction_id']),
                    array('transaction_date',date('Y-m-d H:i:s',time())),
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
            $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$info['orders_id'] . "'");
            unset($pola);
            
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

        } else {

            $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia('PAYEEZY');
            $pola = array(
                    array('orders_id',(int)$info['orders_id']),
                    array('orders_status_id',(int)$status_zamowienia_id),
                    array('date_added','now()'),
                    array('customer_notified','0'),
                    array('customer_notified_sms','0'),
                    array('comments',$komentarz),
                    array('transaction_id',$_POST['transaction_id']),
                    array('transaction_date',date('Y-m-d H:i:s',time())),
                    array('transaction_status',$status['message'])
            );
            $GLOBALS['db']->insert_query('orders_status_history' , $pola);
            unset($pola);
        }

    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $sql);

    echo 'OK';
    exit;

}


function get_status($status){

  switch ($status) {
    case 30: return array('code' => $status, 'message' => 'Zakończona'); break;
    case 35: return array('code' => $status, 'message' => 'Gotowa do rozliczenia'); break;
    case 40: return array('code' => $status, 'message' => 'Odrzucona'); break;
    case 21: return array('code' => $status, 'message' => 'Nieudana'); break;
    case 20: return array('code' => $status, 'message' => 'Zainicjowana'); break;
    default: return array('code' => false, 'message' => 'brak statusu'); break;
  }
}
?>