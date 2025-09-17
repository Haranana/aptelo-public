<?php
chdir('../../../../');

require_once('ustawienia/init.php');
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_PAYPO_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    if (!defined($info['kod'])) {
        define($info['kod'], $info['wartosc']);
    }
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);

$merchant_api_key = (string)PLATNOSC_PAYPO_API;
$komentarz = '';

if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr((string)$name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr((string)$name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

$headers =  array_change_key_case(getallheaders(), CASE_LOWER);

$OtrzymaneDane = array();

$PobraneDane = file_get_contents("php://input");

$OtrzymaneDane = json_decode($PobraneDane,true);

if ( isset($PobraneDane['amount']) ) {
    $OtrzymaneDane['amount'] = (int)$OtrzymaneDane['amount'];
}
if ( isset($PobraneDane['amount']) ) {
    $OtrzymaneDane['referenceId'] = (string)$OtrzymaneDane['referenceId'];
}

if ( isset($headers['x-paypo-signature']) ) {
    $header = $headers['x-paypo-signature'];
    $json = json_encode($OtrzymaneDane, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $method = 'POST';
    $DaneWeryfikacyjne = $method . '+/moduly/platnosc/raporty/paypo/raport.php+' . $json;

    $hash = base64_encode(hash_hmac('sha256', $DaneWeryfikacyjne, $merchant_api_key, true));

    if ( $hash == $headers['x-paypo-signature'] ) {

        $order_id_array = explode('-',$OtrzymaneDane['referenceId']);
        $order_id = $order_id_array['0'];

        $komentarz .= 'Numer transakcji: ' . $OtrzymaneDane['transactionId'] . '<br />';
        $komentarz .= 'Data transakcji: ' . $OtrzymaneDane['lastUpdate'] . '<br />';
        $komentarz .= 'Status transakcji: ' . $OtrzymaneDane['transactionStatus'] . '<br />';
        $komentarz .= 'Kwota: ' . number_format(($OtrzymaneDane['amount'] / 100), 2, ".", "") . '<br />';
        $komentarz .= 'Opis transakcji: ' . $OtrzymaneDane['message'];            

        if ( PLATNOSC_PAYPO_STATUS_ZAMOWIENIA > 0 ) {
            $status_zamowienia_id = PLATNOSC_PAYPO_STATUS_ZAMOWIENIA;
        } else {
            $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
        }

        $pola = array(
                array('orders_id',(int)$order_id),
                array('orders_status_id',$status_zamowienia_id),
                array('date_added','now()'),
                array('customer_notified','0'),
                array('customer_notified_sms','0'),
                array('comments',$komentarz),
                array('transaction_id',$OtrzymaneDane['transactionId']),
                array('transaction_date',date('Y-m-d H:i:s',strtotime($OtrzymaneDane['lastUpdate']))),
                array('transaction_status',$OtrzymaneDane['transactionStatus'])
        );
        $GLOBALS['db']->insert_query('orders_status_history' , $pola);
        unset($pola);

        // zmiana statusu zamowienia
        if ( $OtrzymaneDane['transactionStatus'] == 'NEW' || $OtrzymaneDane['transactionStatus'] == 'PENDING' || $OtrzymaneDane['transactionStatus'] == 'ACCEPTED' ) {
            $pola = array(
                    array('paypo_order_id',$OtrzymaneDane['transactionId']),
                    array('paypo_order_status',$OtrzymaneDane['transactionStatus']),
                    array('paid_info', ( $OtrzymaneDane['transactionStatus'] == 'ACCEPTED' ? '1' : '0' )),
                    array('paypo_order_value',$OtrzymaneDane['amount']),
                    array('paypo_order_new_value',$OtrzymaneDane['amount']),
                    array('payment_method_array','#'),
            );
        } else {
            $pola = array(
                    array('paypo_order_id',$OtrzymaneDane['transactionId']),
                    array('paypo_order_status',$OtrzymaneDane['transactionStatus']),
                    array('paid_info', ( $OtrzymaneDane['transactionStatus'] == 'COMPLETED' ? '1' : '0' )),
                    array('paypo_order_new_value',$OtrzymaneDane['amount']),
                    array('payment_method_array','#'),
            );
        }

        $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$order_id . "'");
        unset($pola);

        if ( $OtrzymaneDane['transactionStatus'] == 'ACCEPTED' ) {

            $pola = array(
                    array('orders_status',$status_zamowienia_id)
            );
            $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$order_id . "'");
            unset($pola);
        }

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

        echo 'OK';

    } else {

        echo 'FALSE';
    }

} else {
    echo 'FALSE';
}
?>