<?php
chdir('../../../../');

require_once('ustawienia/init.php');

    $zapytanie = "SELECT * FROM modules_payment_params WHERE kod LIKE '%_PAYPAL_%'";
    $sql = $db->open_query($zapytanie);

    while ($info = $sql->fetch_assoc()) {
        define($info['kod'], $info['wartosc']);
    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $sql);

    $zapytanie = "SELECT o.orders_id, o.currency, ot.value FROM orders o
                  LEFT JOIN orders_total ot ON o.orders_id = ot.orders_id AND ot.class='ot_total' WHERE o.orders_id = '".(int)$_GET['zamowienie_id']."'";
    $sql = $db->open_query($zapytanie);

    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

        $info = $sql->fetch_assoc();

        $parameters = array();
        $parameters['USER'] = PLATNOSC_PAYPAL_ID;
        $parameters['PWD'] = PLATNOSC_PAYPAL_PASSWORD;
        $parameters['SIGNATURE'] = PLATNOSC_PAYPAL_SIGNATURE;
        $parameters['PAYMENTREQUEST_0_PAYMENTACTION'] = 'Sale'; 
        $parameters['VERSION'] = '114.0'; 
        $parameters['PAYERID'] = $_GET['PayerID'];
        $parameters['TOKEN'] = $_GET['token'];
        $parameters['PAYMENTREQUEST_0_INVNUM'] = $_GET['zamowienie_id'] . '-' . PLATNOSC_PAYPAL_NR_SKLEPU;
        $parameters['PAYMENTREQUEST_0_AMT'] = $info['value'];
        $parameters['PAYMENTREQUEST_0_CURRENCYCODE'] = $info['currency'];
        $parameters['PAYMENTREQUEST_0_NOTIFYURL'] = ADRES_URL_SKLEPU . '/moduly/platnosc/raporty/paypal/raport.php';
        $parameters['BUTTONSOURCE'] = 'OscGold_ShoppingCart_EC_PL';
        $parameters['METHOD'] = 'DoExpressCheckoutPayment'; 

        $GLOBALS['db']->close_query($sql);
        unset($zapytanie, $info, $sql);

        $post_str = ''; 

        foreach ( $parameters as $key=>$val ) {
            $post_str .= $key.'='.$val.'&'; 
        } 
        $post_str = substr((string)$post_str, 0, -1); 

        $ch = curl_init(); 

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        if ( PLATNOSC_PAYPAL_SANDBOX == '1' ) {
            curl_setopt($ch, CURLOPT_URL, "https://api-3t.sandbox.paypal.com/nvp");
        } else {
            curl_setopt($ch, CURLOPT_URL, "https://api-3t.paypal.com/nvp");
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,20);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 180);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);

        $result = curl_exec($ch); 
        curl_close($ch); 

        $odpowiedz = array();
        $odpowiedz = explode('&', (string)$result);

        for ( $i=0; $i < count($odpowiedz); $i++ ) {
            $tmp = explode('=', (string)$odpowiedz[$i]);
            $odpowiedz[$i] = array($tmp[0] => ( isset($tmp[1]) ? $tmp[1] : '' ) );
        }

        $wynik = array();

        foreach ( $odpowiedz as $rekord ) {
            foreach ( $rekord as $key => $value ) {
                $wynik[$key] = urldecode($value);
            }
        }

        if ( isset($wynik['ACK']) && strtoupper((string)$wynik['ACK']) == 'SUCCESS' ) {

            unset($_SESSION['paypal_token']);

            $payPalURL = ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=paypal&status=OK&zamowienie_id=' . (int)$_GET['zamowienie_id'];
            header("Location: ".$payPalURL);

        } else {

            if ( $wynik['L_ERRORCODE0'] == '10486' ) {
                if ( PLATNOSC_PAYPAL_SANDBOX == '1' ) {
                    $payPalURL ='https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$_GET['token'].'&useraction=commit';
                } else { 
                    $payPalURL ='https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$_GET['token'].'&useraction=commit';
                }
                header("Location: ". $payPalURL);
            } else {
                $payPalURL = ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=paypal&status=FAIL&zamowienie_id=' . (int)$_GET['zamowienie_id'] . '&error=' . $wynik['L_LONGMESSAGE0'];
                header("Location: ". $payPalURL);
            }

        }

    } else {
        header("Location: ". ADRES_URL_SKLEPU);
    }

?>
