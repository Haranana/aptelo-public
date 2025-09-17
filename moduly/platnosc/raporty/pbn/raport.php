<?php
chdir('../../../../');

require_once('ustawienia/init.php');
$e = array();

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_PBN_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    if (!defined($info['kod'])) {
        define($info['kod'], $info['wartosc']);
    }
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);

if ( !isset($_POST['newStatus']) || !isset($_POST['paymentId']) ) {
    die('ERROR: EMPTY PARAMETERS'); //-- brak wszystkich parametrow
}

$hash = sha1($_POST['newStatus'] . $_POST['transAmount'] . $_POST['paymentId'] . PLATNOSC_PBN_HASLO );

if ( $hash == $_POST['hash'] ) {

    $zakonczona = false;

    $status = get_status($_POST['newStatus']);

    $komentarz = 'Data transakcji: ' . date("d-m-Y H:i:s") . '<br />';
    $komentarz .= 'Status transakcji: ' . $status['message'] . '<br />';
    $komentarz .= 'Zamowienie ID: ' . ltrim((string)$_POST['paymentId'], '0') . '<br />';

    if ( $_POST['newStatus'] == '2203' || $_POST['newStatus'] == '2303' ) {

        $zakonczona = true;

        if ( PLATNOSC_PBN_STATUS_ZAMOWIENIA > 0 ) {
            $status_zamowienia_id = PLATNOSC_PBN_STATUS_ZAMOWIENIA;
        } else {
             $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia('PBN');
        }

    } else {
         $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
    }

    $pola = array(
            array('orders_id',(int)ltrim((string)$_POST['paymentId'], '0')),
            array('orders_status_id',(int)$status_zamowienia_id),
            array('date_added','now()'),
            array('customer_notified','0'),
            array('customer_notified_sms','0'),
            array('comments',$komentarz),
            array('transaction_id',''),
            array('transaction_date',date('Y-m-d H:i:s',time())),
            array('transaction_status',$status['message'])
    );
    $GLOBALS['db']->insert_query('orders_status_history' , $pola);
    unset($pola);

    // zmina statusu zamowienia
    $pola = array(
            array('orders_status',(int)$status_zamowienia_id),
            array('paid_info', ( $zakonczona ? '1' : '0' )),
            array('payment_method_array','#'),
    );
    $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)ltrim((string)$_POST['paymentId'], '0') . "'");
    unset($pola);
    
    if ( INTEGRACJA_AUTOMATER_WLACZONY == 'tak' ) {
         //
         $IdZamowienia = (int)ltrim((string)$_POST['paymentId'], '0');
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

    echo 'OK';
    exit;

} else {

    echo "PROBLEM";
    exit;
}

function get_status($status){

  switch ($status) {
    case 2203: return array('code' => $status, 'message' => 'transakcja zatwierdzona'); break;
    case 2303: return array('code' => $status, 'message' => 'transakcja zatwierdzona'); break;
    case 2202: return array('code' => $status, 'message' => 'transakcja odrzucona'); break;
    case 2302: return array('code' => $status, 'message' => 'transakcja odrzucona'); break;
    case 2201: return array('code' => $status, 'message' => 'transakcja przeterminowana'); break;
    case 2301: return array('code' => $status, 'message' => 'transakcja przeterminowana'); break;
    default: return array('code' => false, 'message' => 'brak statusu'); break;
  }
}

?>