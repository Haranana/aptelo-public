<?php
chdir('../../../../');

require_once('ustawienia/init.php');

$e = array();

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_DOTPAY_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    if (!defined($info['kod'])) {
        define($info['kod'], $info['wartosc']);
    }
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);

$sign=
PLATNOSC_DOTPAY_PIN.
PLATNOSC_DOTPAY_ID.
( isset($_POST['operation_number']) ? $_POST['operation_number'] : '' ).
( isset($_POST['operation_type']) ? $_POST['operation_type'] : '' ).
( isset($_POST['operation_status']) ? $_POST['operation_status'] : '' ).
( isset($_POST['operation_amount']) ? $_POST['operation_amount'] : '' ).
( isset($_POST['operation_currency']) ? $_POST['operation_currency'] : '' ).
( isset($_POST['operation_original_amount']) ? $_POST['operation_original_amount'] : '' ).
( isset($_POST['operation_original_currency']) ? $_POST['operation_original_currency'] : '' ).
( isset($_POST['operation_datetime']) ? $_POST['operation_datetime'] : '' ).
( isset($_POST['control']) ? $_POST['control'] : '' ).
( isset($_POST['description']) ? $_POST['description'] : '' ).
( isset($_POST['email']) ? $_POST['email'] : '' ).
( isset($_POST['p_info']) ? $_POST['p_info'] : '' ).
( isset($_POST['p_email']) ? $_POST['p_email'] : '' ).
( isset($_POST['channel']) ? $_POST['channel'] : '' );


$signature = hash('sha256', urldecode($sign));

if ( !isset($_POST['id']) ) {
    die('ERROR: EMPTY PARAMETERS'); //-- brak wszystkich parametrow
}

if ( $_POST['id'] != PLATNOSC_DOTPAY_ID ) {
    $e[]=1;
}

$orginal_amount  = $_POST['operation_amount'];
$kwota           = $_POST['control'];

if ( number_format((double)$orginal_amount,2, '.', '') != number_format((double)$kwota, 2, '.', '') ) {
    $e[]=2;
}

if ( strlen((string)$_POST['operation_number']) < 5 ) {
    $e[]=3;
}

if ( $signature != $_POST['signature'] ) {
    $e[]=5;
}


if ( count($e) > 0 ) {

    print "AP-OSC PROBLEM: $e[0]";
    exit;

} else {

    $status = get_status($_POST['operation_status']);

    $komentarz = 'Numer transakcji: ' . $_POST['operation_number'] . '<br />';
    $komentarz .= 'Data transakcji: ' . $_POST['operation_datetime'] . '<br />';
    $komentarz .= 'Status transakcji: ' . $status['message'];

    $zapytanie = "SELECT orders_id FROM orders WHERE customers_email_address = '" . $_POST['email'] . "' ORDER BY date_purchased DESC LIMIT 1";
    $sql = $db->open_query($zapytanie);

    if ($GLOBALS['db']->ile_rekordow($sql) > 0 ) {

        $info = $sql->fetch_assoc();
        if ( $_POST['operation_status'] == 'completed' ) {

            if ( PLATNOSC_DOTPAY_STATUS_ZAMOWIENIA > 0 ) {
                $status_zamowienia_id = PLATNOSC_DOTPAY_STATUS_ZAMOWIENIA;
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
                    array('transaction_id',$_POST['operation_number']),
                    array('transaction_date',date('Y-m-d H:i:s',strtotime($_POST['operation_datetime']))),
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
            
            $platnoscZakonczona = true;

        } else {

            $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia('DOTPAY');
            $pola = array(
                    array('orders_id',(int)$info['orders_id']),
                    array('orders_status_id',(int)$status_zamowienia_id),
                    array('date_added','now()'),
                    array('customer_notified','0'),
                    array('customer_notified_sms','0'),
                    array('comments',$komentarz),
                    array('transaction_id',$_POST['operation_number']),
                    array('transaction_date',date('Y-m-d H:i:s',strtotime($_POST['operation_datetime']))),
                    array('transaction_status',$status['message'])   
            );
            $GLOBALS['db']->insert_query('orders_status_history' , $pola);
            unset($pola);
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
    exit;

}

function get_status($status){

  switch ($status) {
    case 'new': return array('code' => $status, 'message' => 'NOWA'); break;
    case 'completed': return array('code' => $status, 'message' => 'WYKONANA'); break;
    case 'processing': return array('code' => $status, 'message' => 'OCZEKUJE NA WPŁATĘ'); break;
    case 'rejected': return array('code' => $status, 'message' => 'ODRZUCONA'); break;
    case 'processing_realization': return array('code' => $status, 'message' => 'REALIZOWANA'); break;
    case 'processing_realization_waiting': return array('code' => $status, 'message' => 'OCZEKUJE NA REALIZACJĘ'); break;
    default: return array('code' => false, 'message' => 'brak statusu'); break;
  }
}
?>