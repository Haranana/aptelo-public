<?php
chdir('../../../../');

require_once('ustawienia/init.php');

if ( isset($_SESSION['customer_id']) && $_POST['PAYMENTREQUEST_0_EMAIL'] == $_SESSION['customer_email']) {

    $zapytanie = "SELECT * FROM modules_payment_params WHERE kod LIKE '%_PAYPAL_%'";
    $sql = $db->open_query($zapytanie);

    while ($info = $sql->fetch_assoc()) {
        define($info['kod'], $info['wartosc']);
    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $sql);

    $post_str = ''; 
    $post_str = 'USER='.PLATNOSC_PAYPAL_ID . '&PWD=' . PLATNOSC_PAYPAL_PASSWORD . '&SIGNATURE=' . PLATNOSC_PAYPAL_SIGNATURE . '&'; 

    foreach ( $_POST as $key=>$val ) {
        if ( $key != 'rodzaj_platnosci' && $key != 'tryb_dzialania' ) {
            $post_str .= $key.'='.urlencode($val).'&'; 
        }
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
        $odpowiedz[$i] = array($tmp[0] => ( isset($tmp[1]) ? $tmp[1] : '' ));
        unset($tmp);
    }

    $wynik = array();

    foreach ( $odpowiedz as $rekord ) {
        foreach ( $rekord as $key => $value ) {
            $wynik[$key] = urldecode($value);
        }
    }

    if ( isset($wynik['ACK']) && strtoupper((string)$wynik['ACK']) == 'SUCCESS' ) {

        $_SESSION['paypal_token'] = $wynik['TOKEN'];
        if ( PLATNOSC_PAYPAL_SANDBOX == '1' ) {
            $payPalURL ='https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token='.$wynik['TOKEN'];
        } else { 
            $payPalURL ='https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token='.$wynik['TOKEN'];
        }
        header("Location: ".$payPalURL);

    } else {

        $payPalURL = $_POST['CANCELURL'];
        header("Location: ".$payPalURL);

    }

}

?>