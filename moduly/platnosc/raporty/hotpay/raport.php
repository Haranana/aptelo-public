<?php
chdir('../../../../');

require_once('ustawienia/init.php');

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_HOTPAY_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    if ( !defined($info['kod']) ) {
        define($info['kod'], $info['wartosc']);
    }
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);

$komentarz = '';
$platnoscZakonczona = false;

if ( !empty($_POST) ) {

    if ( !empty($_POST["KWOTA"]) &&
         !empty($_POST["ID_PLATNOSCI"]) &&
         !empty($_POST["ID_ZAMOWIENIA"]) &&
         !empty($_POST["STATUS"]) &&
         !empty($_POST["SEKRET"]) &&
         !empty($_POST["SECURE"]) &&
         !empty($_POST["HASH"])
        ) {

        if ( hash("sha256",PLATNOSC_HOTPAY_HASLO.";".$_POST["KWOTA"].";".$_POST["ID_PLATNOSCI"].";".$_POST["ID_ZAMOWIENIA"].";".$_POST["STATUS"].";".$_POST["SECURE"].";".$_POST["SEKRET"]) == $_POST["HASH"] ) {

            /*
            $message = '';
            if ( isset($_POST) ) {
                $message .= 'HASLO: ' . PLATNOSC_HOTPAY_HASLO . "\n";
                foreach ($_POST as $k => $v) {
                    $message .= $k . ': ' . $v . "\n";
                }
                mail('info@oscgold.com', 'Test HotPay - raport', $message);
            }
            */

            $komentarz .= 'Numer transakcji: ' . $_POST["ID_PLATNOSCI"] . '<br />';
            $komentarz .= 'Data transakcji: ' . date('Y-m-d H:i:s') . '<br />';
            $komentarz .= 'Kwota transakcji: ' . $_POST["KWOTA"] . '<br />';

            //komunikacja poprawna
            if ($_POST["STATUS"] == "SUCCESS") {
                //płatność zaakceptowana
                $komentarz .= 'Status transakcji: Płatność została poprawnie opłacona';
            } else if ($_POST["STATUS"] == "FAILURE") {
                //odrzucone
                $komentarz .= 'Status transakcji: Płatność zakończyła się błędem';
            } else if ($_POST["STATUS"] == "PENDING") {
                //odrzucone
                $komentarz .= 'Status transakcji: Płatność oczekuje na realizacje';
            }

            $zapytanie = "SELECT orders_id FROM orders WHERE orders_id = '" . (int)$_POST["ID_ZAMOWIENIA"] . "' ORDER BY date_purchased DESC LIMIT 1";
            $sql = $db->open_query($zapytanie);

            if ($GLOBALS['db']->ile_rekordow($sql) > 0 ) {

                $info = $sql->fetch_assoc();

                if ( $_POST["STATUS"] == "SUCCESS" ) {

                    if ( PLATNOSC_HOTPAY_STATUS_ZAMOWIENIA > 0 ) {
                        $status_zamowienia_id = PLATNOSC_HOTPAY_STATUS_ZAMOWIENIA;
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
                            array('transaction_id',$_POST["ID_PLATNOSCI"]),
                            array('transaction_date',date('Y-m-d H:i:s',time())),
                            array('transaction_status',$_POST["STATUS"])   
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

                } elseif ( $_POST["STATUS"] == 'FAILURE' || $_POST["STATUS"] == 'PENDING' ) {

                    $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia('HOTPAY');
                    $pola = array(
                            array('orders_id',(int)$info['orders_id']),
                            array('orders_status_id',(int)$status_zamowienia_id),
                            array('date_added','now()'),
                            array('customer_notified','0'),
                            array('customer_notified_sms','0'),
                            array('comments',$komentarz),
                            array('transaction_id',$_POST["ID_PLATNOSCI"]),
                            array('transaction_date',date('Y-m-d H:i:s',time())),
                            array('transaction_status',$_POST["STATUS"])   
                    );
                    $GLOBALS['db']->insert_query('orders_status_history' , $pola);
                    unset($pola);

                }

            }

            $GLOBALS['db']->close_query($sql);
            unset($zapytanie, $info, $sql);

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

    } else {
        echo "BRAK WYMAGANYCH DANYCH";
    }

}                        


















echo 'OK';
?>