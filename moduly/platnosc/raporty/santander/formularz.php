<?php

function PowtorzPlatnosc( $parametry, $zamowienie_id ) {

    $tekst = '';

    $zamowienie = new Zamowienie($zamowienie_id);
    $termin = time() - FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia']);

    $zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_SANTANDER_%'";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    while ($info = $sql->fetch_assoc()) {
        define($info['kod'], $info['wartosc']);
    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $sql);

    // Sprawdza maksymalny czas na wykonanie platnosci
    if ( $termin > (PLATNOSC_SANTANDER_TERMIN_PATNOSCI *24 * 3600) ) {
       $pola = array(
               array('payment_method_array','#')
       );
       $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$zamowienie_id . "'");
       unset($pola);
       return;
    }

    if ( PLATNOSC_SANTANDER_STATUS_ZAMOWIENIA_START > 0 ) {
        $status_zamowienia_id = PLATNOSC_SANTANDER_STATUS_ZAMOWIENIA_START;
    } else {
        $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
    }

    $formularz = '';

    if ( $zamowienie->info['status_zamowienia'] == $status_zamowienia_id ) {
        foreach ( $parametry as $key => $value ) {
            if ( $key != 'rodzaj_platnosci' ) {
                $formularz .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';
            }
        }

        $tekst = '<form action="https://wniosek.eraty.pl/formularz/" method="post" name="payform" class="cmxform">
                  <div style="text-align:center;padding:5px;">{__TLUMACZ:PRZEJDZ_DO_WNIOSKU_RATALNEGO}:<br /><br />';
                  $tekst .= $formularz;
                  $tekst .= '   <input class="przyciskZaplac" type="submit" id="submitButton" value="{__TLUMACZ:PRZYCISK_KUPUJE_Z_SANTANDER}" />
                  </div>
                  </form>';
    } else {
        // zmiana statusu zamowienia
        $pola = array(
                array('payment_method_array','#')
        );
        $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$zamowienie_id . "'");
        unset($pola);

    }

    return $tekst;
}
?>