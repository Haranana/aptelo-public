<?php
chdir('../../../../');

require_once('ustawienia/init.php');

$e = array();

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_TRANSFERUJ_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    if (!defined($info['kod'])) {
        define($info['kod'], $info['wartosc']);
    }
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);

$AdresyIP = array();
$AdresyIP = explode(',', (string)PLATNOSC_TRANSFERUJ_IP);
$AdresyIPBezSpacji = array_map('trim', $AdresyIP);

if(!empty($_POST) && in_array($_SERVER['REMOTE_ADDR'],$AdresyIPBezSpacji)) {

    $sid = PLATNOSC_TRANSFERUJ_ID;
    $tr_id = $_POST['tr_id'];
    $tr_amount = $_POST['tr_amount'];
    $tr_crc = $_POST['tr_crc'];
    $kod = PLATNOSC_TRANSFERUJ_CRC;

    if ( !isset($_POST['tr_id']) || !isset($_POST['tr_amount']) || !isset($_POST['tr_crc']) ) {
        die('ERROR: EMPTY PARAMETERS'); //-- brak wszystkich parametrow
    }

    if ( md5($sid.$tr_id.$tr_amount.$tr_crc.$kod) == $_POST['md5sum'] ) {

        $tr_paid = $_POST['tr_paid'];
        $order_id = base64_decode((string)$_POST['tr_crc']);
        $status_transakcji = $_POST['tr_status'];

        if ($status_transakcji == 'TRUE') {

            $komentarz  = 'Numer transakcji: ' . $_POST['tr_id'] . '<br />';
            $komentarz .= 'Data transakcji: ' . $_POST['tr_date'] . '<br />';
            $komentarz .= 'Status transakcji: wykonana';
            if ( isset($_POST['tr_error']) && $_POST['tr_error'] == 'overpay' ) {
                $komentarz .= ' (nadpłata) wpłacona kwota: ' . $tr_amount;
            } elseif ( isset($_POST['tr_error']) && $_POST['tr_error'] == 'surcharge' ) {
                $komentarz .= ' (niedopłata) wpłacona kwota: ' . $tr_amount;
            }

            if ( PLATNOSC_TRANSFERUJ_STATUS_ZAMOWIENIA > 0 ) {
                $status_zamowienia_id = PLATNOSC_TRANSFERUJ_STATUS_ZAMOWIENIA;
            } else {
                $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
            }

            $pola = array(
                    array('orders_id',(int)$order_id),
                    array('orders_status_id',(int)$status_zamowienia_id),
                    array('date_added','now()'),
                    array('customer_notified','0'),
                    array('customer_notified_sms','0'),
                    array('comments',$komentarz),
                    array('transaction_id',$_POST['tr_id']),
                    array('transaction_date',date('Y-m-d H:i:s',strtotime($_POST['tr_date']))),
                    array('transaction_status',$status_transakcji)
            );
            $GLOBALS['db']->insert_query('orders_status_history' , $pola);
            unset($pola);

            // zmiana statusu zamowienia
            $pola = array(
                    array('orders_status',(int)$status_zamowienia_id),
                    array('paid_info','1'),
                    array('payment_method_array','#'),
            );
            $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$order_id . "'");
            unset($pola);
            
            if ( INTEGRACJA_AUTOMATER_WLACZONY == 'tak' ) {
                 //
                 $IdZamowienia = (int)$order_id;
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
            
            echo "TRUE";
            exit;
        }

    } else {

        echo "FALSE";
        exit;

    }

} else {
    echo "FALSE";
    exit;
}

?>