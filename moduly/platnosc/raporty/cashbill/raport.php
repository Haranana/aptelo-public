<?php
chdir('../../../../');

require_once('ustawienia/init.php');

$e = array();

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_CASHBILL_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    if (!defined($info['kod'])) {
        define($info['kod'], $info['wartosc']);
    }
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);

if ( !isset($_POST['service']) || !isset($_POST['orderid']) || !isset($_POST['amount']) || !isset($_POST['userdata']) || !isset($_POST['status']) ) {
    die('ERROR: BRAK WSZYSTKICH PARAMETROW'); //-- brak wszystkich parametrow
}

$service    = PLATNOSC_CASHBILL_ID;
$key        = PLATNOSC_CASHBILL_SECRET;

try 
{

    if( check_sign( $_POST, $key, $_POST['sign'] ) && $_POST['service'] == $service ) {


        $komentarz  = 'Numer transakcji: ' . $_POST['orderid'] . '<br />';
        $komentarz .= 'Status transakcji: ' . $_POST['status'];

        $zapytanie = "SELECT orders_id FROM orders WHERE orders_id = '" . (int)$_POST['userdata'] . "' LIMIT 1";
        $sql = $db->open_query($zapytanie);

        if ($GLOBALS['db']->ile_rekordow($sql) > 0 ) {

            $info = $sql->fetch_assoc();

            if ( $_POST['status'] == 'ok' ) {

                if ( PLATNOSC_CASHBILL_STATUS_ZAMOWIENIA > 0 ) {
                    $status_zamowienia_id = PLATNOSC_CASHBILL_STATUS_ZAMOWIENIA;
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
                        array('transaction_id',$_POST['orderid']),
                        array('transaction_date',date('Y-m-d H:i:s',time())),
                        array('transaction_status',$_POST['status'])
                );
                $GLOBALS['db']->insert_query('orders_status_history' , $pola);
                unset($pola);

                // zmiana statusu zamowienia
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

                $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia('CASHBILL');
                $pola = array(
                        array('orders_id',(int)$info['orders_id']),
                        array('orders_status_id',(int)$status_zamowienia_id),
                        array('date_added','now()'),
                        array('customer_notified','0'),
                        array('customer_notified_sms','0'),
                        array('comments',$komentarz),
                        array('transaction_id',$_POST['orderid']),
                        array('transaction_date',date('Y-m-d H:i:s',time())),
                        array('transaction_status',$_POST['status'])
                );
                $GLOBALS['db']->insert_query('orders_status_history' , $pola);
                unset($pola);
            }

        }
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie, $info, $sql);

        echo 'OK';

    }
}
catch (Exception $exception)
{
    echo 'ERROR: '.$exception->getMessage();
}

function check_sign($data, $key, $sign) {
    if ( md5( $data['service'].$data['orderid'].$data['amount'].$data['userdata'].$data['status'].$key ) == $sign ) {
        return true;
    } else {
        return false;
    }
}

?>