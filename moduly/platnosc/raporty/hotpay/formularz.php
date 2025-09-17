<?php

function PowtorzPlatnosc( $parametry, $zamowienie_id ) {

    $tekst = '';
    $zamowienie = new Zamowienie($zamowienie_id);
    $termin = time() - strtotime($zamowienie->info['data_zamowienia']);

    $zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE 'PLATNOSC_HOTPAY_%'";

    $sql = $GLOBALS['db']->open_query($zapytanie);

    while ($info = $sql->fetch_assoc()) {
        define($info['kod'], $info['wartosc']);
    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $sql);

    // Sprawdza maksymalny czas na wykonanie platnosci
    if ( $termin > (PLATNOSC_HOTPAY_TERMIN_PATNOSCI *24 * 3600) ) {
       $pola = array(
               array('payment_method_array','#')
       );
       $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$zamowienie_id . "'");
       unset($pola);
       return;
    }

//    $DanePlatnosci = simplexml_load_string($parametry);

    if ( isset($parametry['DATA']) ) {
        $termin = time() - $parametry['DATA'];
    } else {
        $termin = 3600;
    }

    if ( $termin < 3600 && isset($parametry['STATUS']) && isset($parametry['STATUS']) && $parametry['STATUS'] == true ) {
            
        $tekst .= '<div style="text-align:center;padding:5px;">';
            $tekst .= '<div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:</div>';

            $tekst .= '<a class="przyciskZaplac" style="display:inline-block;" href="'.$parametry['URL'].'">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}</a>';

        $tekst .= '</div>';

    } else {

        $zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_HOTPAY_%'";
        $sql = $GLOBALS['db']->open_query($zapytanie);

        while ($info = $sql->fetch_assoc()) {
            define($info['kod'], $info['wartosc']);
        }

        $GLOBALS['db']->close_query($sql);
        unset($zapytanie, $info, $sql);

        if ( $zamowienie->info['waluta'] == 'PLN' ) {

            $kwota = number_format($zamowienie->info['wartosc_zamowienia_val'], 2, ".", "");

        } else {

            $przelicznikWaluty = $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik'];
            $kwota = number_format(($zamowienie->info['wartosc_zamowienia_val'] / $przelicznikWaluty), 2, ".", "");

        }

        $Sekret         = PLATNOSC_HOTPAY_SEKRET;
        $Haslo          = PLATNOSC_HOTPAY_HASLO;
        $NazwaFirmy     = ( DANE_NAZWA_FIRMY_SKROCONA != '' ? DANE_NAZWA_FIRMY_SKROCONA : DANE_NAZWA_FIRMY );

        $FORMULARZ = array(
            "SEKRET" => $Sekret,
            "KWOTA" => (float)$kwota,
            "NAZWA_USLUGI" => addslashes((string)$NazwaFirmy),
            "ADRES_WWW" => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . "/platnosc_koniec.php?typ=hotpay&zamowienie_id=".$zamowienie_id,
            "ID_ZAMOWIENIA" => $zamowienie_id,
            "EMAIL" => $zamowienie->klient['adres_email'],
            "DANE_OSOBOWE" => $zamowienie->klient['nazwa'],
            "TYP" => "INIT",
            "ADRES_NOTYFIKACJE" => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . "/moduly/platnosc/raporty/hotpay/raport.php"
        );

        $FORMULARZ["HASH"] = hash("sha256", $Haslo . ";" . $FORMULARZ["KWOTA"] . ";" . $FORMULARZ["NAZWA_USLUGI"] . ";" . $FORMULARZ["ADRES_WWW"] . ";" . $FORMULARZ["ID_ZAMOWIENIA"] . ";" . $Sekret);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_URL,"https://platnosc.hotpay.pl/");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$FORMULARZ);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $wynik_json = curl_exec($ch);

        curl_close ($ch);

        $tekst .= '<div style="text-align:center;padding:5px;">';
        if ( !empty($wynik_json) ) {

            $wynik=json_decode($wynik_json,true);

            if ( !empty($wynik["STATUS"]) && $wynik["STATUS"] == true && isset($wynik['URL']) ) {

                $wynik['DATA'] = time();

                $parametry                      = serialize($wynik);

                $pola = array(
                        array('payment_method_array',$parametry)
                );

                $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . $zamowienie_id . "'");
                unset($pola);

                $tekst .= '<div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:</div>';

                $tekst .= '<a class="przyciskZaplac" style="display:inline-block;" href="'.$wynik['URL'].'">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}</a>';

            } else {

                $tekst .= '{__TLUMACZ:PLATNOSCI_BLAD_TOKEN}' . '<br />';

                if ( isset($wynik['STATUS']) && isset($wynik['STATUS']) == false ) {
                    $tekst .= $wynik["WIADOMOSC"] . '<br />';
                }
            }
        } else {
            $tekst .= '{__TLUMACZ:PLATNOSCI_BLAD_TOKEN}' . '<br />';
        }

        $tekst .= '</div>';

    }
    return $tekst;

}
?>