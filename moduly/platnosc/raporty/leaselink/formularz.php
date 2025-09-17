<?php

function PowtorzPlatnosc( $parametry, $zamowienie_id ) {

    $tekst = '';

    $zamowienie = new Zamowienie($zamowienie_id);
    $termin = time() - FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia']);

    $zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_LEASELINK_%'";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    while ($info = $sql->fetch_assoc()) {
        define($info['kod'], $info['wartosc']);
    }

    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $sql);

    // Sprawdza maksymalny czas na wykonanie platnosci
    if ( $termin > (PLATNOSC_LEASELINK_TERMIN_PATNOSCI *24 * 3600) ) {
       $pola = array(
               array('payment_method_array','#')
       );
       $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$zamowienie_id . "'");
       unset($pola);
       return;
    }

    if ( PLATNOSC_LEASELINK_SANDBOX == '1' ) {
        $URLPlatnosc  = 'https://onlinetest.leaselink.pl';
    } else {
        $URLPlatnosc  = 'https://online.leaselink.pl';
    }

    if ( $termin < 2592000 && isset($parametry['CalculationUrl']) && $parametry['CalculationUrl'] != '' ) {
            
        $tekst .= '<div style="text-align:center;padding:5px;">';
            $tekst .= '<div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:PRZEJDZ_DO_WNIOSKU}:</div>';

            $tekst .= '<a class="przyciskZaplac" target="_blank" style="display:inline-block;" href="'.$URLPlatnosc.$parametry['CalculationUrl'].'">{__TLUMACZ:PRZEJDZ_DO_WNIOSKU_LEASINGOWEGO}</a>';

        $tekst .= '</div>';

    }
    
    return $tekst;

}
?>