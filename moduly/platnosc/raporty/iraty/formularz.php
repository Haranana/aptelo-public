<?php

function PowtorzPlatnosc( $parametry, $zamowienie_id ) {

    $zamowienie = new Zamowienie($zamowienie_id);
    $termin = time() - strtotime($zamowienie->info['data_zamowienia']);

    $zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_IRATY_%'";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    while ($info = $sql->fetch_assoc()) {
        define($info['kod'], $info['wartosc']);
    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $sql);

    // Sprawdza maksymalny czas na wykonanie platnosci
    if ( $termin > (PLATNOSC_IRATY_TERMIN_PATNOSCI *24 * 3600) ) {
       $pola = array(
               array('payment_method_array','#')
       );
       $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$zamowienie_id . "'");
       unset($pola);
       return;
    }

    $formularz = '';
    foreach ( $parametry as $key => $value ) {
        if ( $key != 'rodzaj_platnosci' ) {
            $formularz .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';
        }
    }

    $tekst = '<form action="https://www.platformaratalna.pl/integracja" method="post" name="payform" class="cmxform">
              <div style="text-align:center;padding:5px;">{__TLUMACZ:PRZEJDZ_DO_WNIOSKU_RATALNEGO}:<br /><br />';
              $tekst .= $formularz;
              $tekst .= '   <input class="przyciskZaplac" type="submit" id="submitButton" value="{__TLUMACZ:PRZEJDZ_DO_WNIOSKU_RATALNEGO}" />
              </div>
              </form>';

    return $tekst;
}
?>