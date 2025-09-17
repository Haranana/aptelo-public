<?php
chdir('../../../../');
require_once('ustawienia/init.php');

$server = 'www.platnosci.pl';
$server_script = '/paygw/UTF/Payment/get';

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_PAYU_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    if (!defined($info['kod'])) {
        define($info['kod'], $info['wartosc']);
    }
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);


if (!isset($_POST['pos_id']) || !isset($_POST['session_id']) || !isset($_POST['ts']) || !isset($_POST['sig'])) {
    die('ERROR: EMPTY PARAMETERS'); //-- brak wszystkich parametrow
}

if ($_POST['pos_id'] != PLATNOSC_PAYU_POS_ID_EUR) {
    die('ERROR: WRONG POS ID');  //--- bledny numer POS
}

$sig = md5( $_POST['pos_id'] . $_POST['session_id'] . $_POST['ts'] . PLATNOSC_PAYU_KEY_2_EUR);

if ($_POST['sig'] != $sig) {
    die('ERROR: WRONG SIGNATURE');  //--- bledny podpis
}

$ts = time();
$sig = md5( PLATNOSC_PAYU_POS_ID_EUR . $_POST['session_id'] . $ts . PLATNOSC_PAYU_KEY_1_EUR);

$parameters = "pos_id=" . PLATNOSC_PAYU_POS_ID_EUR . "&session_id=" . $_POST['session_id'] . "&ts=" . $ts . "&sig=" . $sig;

$curl = true;
$status = false;
$data = '';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://' . $server . $server_script);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$platnosci_response = curl_exec($ch);
curl_close($ch);

$tablicaOdpowiedzi = Funkcje::Xml2Array($platnosci_response, 'trans');

$status = get_status($tablicaOdpowiedzi);

if ($status['code'] == 99 || ($status['code'] > 0 && $status['code'] <= 7 )) { //--- rozpoznany status transakcji

    if ( isset($tablicaOdpowiedzi['order_id']) && is_numeric($tablicaOdpowiedzi['order_id']) && ($tablicaOdpowiedzi['order_id'] > 0) ) {

        $Kwota = 0;
        if ( isset($tablicaOdpowiedzi['amount']) ) {
            $Kwota = $tablicaOdpowiedzi['amount'] / 100;
            $Kwota = number_format($Kwota, 2, ".", "");
        }

        $komentarz = '';
        if ( !is_array($tablicaOdpowiedzi['init']) ) {
            $data .= 'Data rozpoczęcia: ' . $tablicaOdpowiedzi['init'] . '<br />';
        }
        if ( !is_array($tablicaOdpowiedzi['sent']) ) {
            $data .= 'Data wysłania: ' . $tablicaOdpowiedzi['sent'] . '<br />';
        }
        if ( !is_array($tablicaOdpowiedzi['recv']) ) {
            $data .= 'Data odbioru: ' . $tablicaOdpowiedzi['recv'] . '<br />';
        }
        if ( !is_array($tablicaOdpowiedzi['cancel']) ) {
            $data .= 'Data anulowania: ' . $tablicaOdpowiedzi['cancel'] . '<br />';
        }
        $komentarz .= 'Numer transakcji: ' . $tablicaOdpowiedzi['id'] . '<br />';
        $komentarz .= 'Kwota transakcji: ' . $Kwota . '<br />';
        $komentarz .= $data . 'Status transakcji: ' . $status['message'];

        // zapisanie informacji w historii statusow zamowien w przypadku zakonczenia tranzkcji
        if ( $status['code'] == 99 ) {

            if ( PLATNOSC_PAYU_STATUS_ZAMOWIENIA > 0 ) {
                $status_zamowienia_id = PLATNOSC_PAYU_STATUS_ZAMOWIENIA;
            } else {
                $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
            }
            //
            $pola = array(
                    array('orders_id',(int)$tablicaOdpowiedzi['order_id']),
                    array('orders_status_id',(int)$status_zamowienia_id),
                    array('date_added','now()'),
                    array('customer_notified','0'),
                    array('customer_notified_sms','0'),
                    array('comments',$komentarz)
            );
            $GLOBALS['db']->insert_query('orders_status_history' , $pola);
            unset($pola);

            // zmina statusu zamowienia
            $pola = array(
                    array('orders_status',(int)$status_zamowienia_id),
                    array('paid_info','1'),
                    array('payment_method_array','#'),
            );
            $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$tablicaOdpowiedzi['order_id'] . "'");
            unset($pola);
            
            if ( INTEGRACJA_AUTOMATER_WLACZONY == 'tak' ) {
                 //
                 $IdZamowienia = (int)$tablicaOdpowiedzi['order_id'];
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

        // zapisanie informacji w historii statusow zamowien w przypadku anulowania lub odrzucenia tranzakcji
        if ( $status['code'] == 2 || $status['code'] == 3 || $status['code'] == 6 || $status['code'] == 7 ) {

            $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia('PAYU');

            $zapytanie = "SELECT comments  FROM orders_status_history WHERE comments = '".$komentarz."'";
            $sql = $db->open_query($zapytanie);

            if ($GLOBALS['db']->ile_rekordow($sql) > 0 ) {
            } else {
                //
                $pola = array(
                        array('orders_id',(int)$tablicaOdpowiedzi['order_id']),
                        array('orders_status_id',(int)$status_zamowienia_id),
                        array('date_added','now()'),
                        array('customer_notified','0'),
                        array('customer_notified_sms','0'),
                        array('comments',$komentarz)
                );
                $GLOBALS['db']->insert_query('orders_status_history' , $pola);
                unset($pola);

                // zmina statusu zamowienia
                $pola = array(
                        array('orders_status',(int)$status_zamowienia_id),
                );
                $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$tablicaOdpowiedzi['order_id'] . "'");
                unset($pola);
            }
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie, $sql);

        }
    }
    echo 'OK';
    exit;

} else {

    echo "res: " . print_r($status,true) ."<br>";
    echo "ERROR in Response";
}


function get_status($parts){

  if ($parts['pos_id'] != PLATNOSC_PAYU_POS_ID_EUR) return array('code' => false,'message' => 'błędny numer POS');	//--- bledny numer POS
  $sig = md5($parts['pos_id'].$parts['session_id'].$parts['order_id'].$parts['status'].$parts['amount'].$parts['desc'].$parts['ts'].PLATNOSC_PAYU_KEY_2_EUR);
  if ($parts['sig'] != $sig) return array('code' => false,'message' => 'błędny podpis'); //--- bledny podpis
  switch ($parts['status']) {
    case 1: return array('code' => $parts['status'], 'message' => 'nowa'); break;
    case 2: return array('code' => $parts['status'], 'message' => 'anulowana'); break;
    case 3: return array('code' => $parts['status'], 'message' => 'odrzucona'); break;
    case 4: return array('code' => $parts['status'], 'message' => 'rozpoczęta'); break;
    case 5: return array('code' => $parts['status'], 'message' => 'oczekuje na odbiór'); break;
    case 6: return array('code' => $parts['status'], 'message' => 'autoryzacja odmowna'); break;
    case 7: return array('code' => $parts['status'], 'message' => 'płatność odrzucona'); break;
    case 99: return array('code' => $parts['status'], 'message' => 'płatność odebrana - zakończona'); break;
    case 888: return array('code' => $parts['status'], 'message' => 'błędny status'); break;
    default: return array('code' => false, 'message' => 'brak statusu'); break;
  }
}

?>