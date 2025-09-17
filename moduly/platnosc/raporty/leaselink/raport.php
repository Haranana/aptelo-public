<?php
chdir('../../../../');

require_once('ustawienia/init.php');

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_LEASELINK_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    define($info['kod'], $info['wartosc']);
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);

$DanezLeaselink = array();
$PobraneDane = file_get_contents("php://input");
$DanezLeaselink = json_decode($PobraneDane);

//####################################################################
$message = "";

if ( isset($_POST) ) {
    if ( isset($DanezLeaselink->TransactionId) ) {

        $komentarz = '';

        $zamowienie = $DanezLeaselink->CustomerExternalDocument;

        $komentarz .= 'ID transakcji : ' . $DanezLeaselink->TransactionId . '<br />';
        $komentarz .= 'Data utworzenia : ' . $DanezLeaselink->OperationDate . ' ' . $DanezLeaselink->OperationTime . '<br />';
        $komentarz .= 'Status płatności : ' . $DanezLeaselink->StatusName . '<br />';

        //foreach ((array)$DanezLeaselink as $k => $v) {
        //    $message .= $k . ': ' . $v . "\n";
        //}
        //$message .= "INPUT\n\n";
        //$message .= $komentarz . "\n\n";
        //mail('info@oscgold.com', 'Test Leaselink - raport', $message);

        if ( $DanezLeaselink->StatusName == 'PROCESSING' || $DanezLeaselink->StatusName == 'SIGN_CONTRACT' ) {

            $zapytanie = "SELECT orders_id, payment_method_array FROM orders WHERE orders_id = '" . (int)$zamowienie . "' ORDER BY date_purchased DESC LIMIT 1";
            $sql = $db->open_query($zapytanie);

            if ($GLOBALS['db']->ile_rekordow($sql) > 0 ) {

                if ( $DanezLeaselink->StatusName == 'SIGN_CONTRACT' ) {
                    if ( PLATNOSC_LEASELINK_STATUS_ZAMOWIENIA > 0 ) {
                        $status_zamowienia_id = PLATNOSC_LEASELINK_STATUS_ZAMOWIENIA;
                    } else {
                        $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
                    }
                    $platnoscZakonczona = true;
                } else {
                        $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
                }

                $info = $sql->fetch_assoc();

                $pola = array(
                        array('orders_id',(int)$info['orders_id']),
                        array('orders_status_id',(int)$status_zamowienia_id),
                        array('date_added','now()'),
                        array('customer_notified','0'),
                        array('customer_notified_sms','0'),
                        array('comments',$komentarz),
                        array('transaction_id',$DanezLeaselink->TransactionId),
                        array('transaction_date',$DanezLeaselink->OperationDate . ' ' . $DanezLeaselink->OperationTime),
                        array('transaction_status',$DanezLeaselink->StatusName)
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

            }

            $GLOBALS['db']->close_query($sql);
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

    }
}

echo 'OK';

//####################################################################

?>