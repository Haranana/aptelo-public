<?php

function PowtorzPlatnosc( $parametry, $zamowienie_id ) {

    $zamowienie = new Zamowienie($zamowienie_id);
    $termin = time() - FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia']);

    $zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_PAYU_%'";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    while ($info = $sql->fetch_assoc()) {
        define($info['kod'], $info['wartosc']);
    }

    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $sql);

    // Sprawdza maksymalny czas na wykonanie platnosci
    if ( $termin > (PLATNOSC_PAYU_TERMIN_PATNOSCI *24 * 3600) ) {
       $pola = array(
               array('payment_method_array','#')
       );
       $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$zamowienie_id . "'");
       unset($pola);
       return;
    }

    $WarunekSzukania = 'PLATNOSC_PAYU_KEY_2';

    if ( $parametry['waluta'] == 'EUR' ) {
        $WarunekSzukania = 'PLATNOSC_PAYU_KEY_2_EUR';
    }
    $zapytanie = "SELECT wartosc FROM modules_payment_params WHERE kod = '".$WarunekSzukania."'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    while ($info = $sql->fetch_assoc()) {
        $klucz1 = $info['wartosc'];
    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $sql);

    $sygnatura = '';
    $ts = time();
    $session_id = session_id() . '-' . $parametry['order_id'] . '-'. substr(md5(time()), 16);
    $parameters = array();

    $parameters['pos_id'] = $parametry['pos_id'];
    $parameters['session_id'] = $session_id;
    $parameters['pos_auth_key'] = $parametry['pos_auth_key'];
    $parameters['amount'] = $parametry['amount'];
    $parameters['desc'] = $parametry['desc'];
    $parameters['desc2'] = $parametry['desc2'];
    $parameters['order_id'] = $parametry['order_id'];
    $parameters['first_name'] = $parametry['first_name'];
    $parameters['last_name'] = $parametry['last_name'];
    $parameters['street'] = $parametry['street'];
    $parameters['city'] = $parametry['city'];
    $parameters['post_code'] = $parametry['post_code'];
    $parameters['country'] = $parametry['country'];
    $parameters['email'] = $parametry['email'];
    $parameters['phone'] = $parametry['phone'];
    $parameters['language'] = $parametry['language'];
    $parameters['client_ip'] = $parametry['client_ip'];
    $parameters['ts'] = $ts;

    ksort($parameters);

    foreach ( $parameters as $key => $value ) {
        if ( $key != 'rodzaj_platnosci' && $key != 'waluta' ) {
            $sygnatura .= $key . '=' . urlencode($value) . '&';
        }
    }
    $sygnatura .= $klucz1;

    $parameters['sig']              = hash('sha256', $sygnatura);

    $parameters['sig'] = hash('sha256', $sygnatura);

    $formularz = '';
    foreach ( $parameters as $key => $value ) {
        $formularz .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';
    }


    $tekst = '<form action="https://secure.payu.com/paygw/UTF/NewPayment" method="post" name="payform" class="cmxform">
                   <div style="text-align:center;padding:5px;">
                      {__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:<br /><br />';
    $tekst .= $formularz;
    $tekst .= '   <input class="przyciskZaplac" type="submit" id="submitButton" value="{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}" /><br />
                   </div>
              </form>';

    return $tekst;

}
?>