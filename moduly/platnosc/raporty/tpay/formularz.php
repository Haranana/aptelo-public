<?php

function PowtorzPlatnosc( $parametry, $zamowienie_id ) {

    $tekst  = '';
    $Waluta = 'PLN';
    $Jezyk = 'pl';
    $Jezyk = strtolower((string)$_SESSION['domyslnyJezyk']['kod']);
    $Kwota = 0;

    $zamowienie = new Zamowienie($zamowienie_id);
    $termin = time() - FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia']);

    $zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE 'PLATNOSC_TPAY_%'";

    $sql = $GLOBALS['db']->open_query($zapytanie);

    while ($info = $sql->fetch_assoc()) {
        define($info['kod'], $info['wartosc']);
    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $sql);

    // Sprawdza maksymalny czas na wykonanie platnosci
    if ( $termin > (PLATNOSC_TPAY_TERMIN_PATNOSCI *24 * 3600) ) {
       $pola = array(
               array('payment_method_array','#')
       );
       $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$zamowienie_id . "'");
       unset($pola);
       return;
    }

    if ( $termin < 86400 && isset($parametry['transactionPaymentUrl']) && isset($parametry['status']) && $parametry['status'] == 'pending' ) {

        $tekst .= '<div style="text-align:center;padding:5px;">';
            $tekst .= '<div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:</div>';

            $tekst .= '<a class="przyciskZaplac" style="display:inline-block;" href="'.$parametry['transactionPaymentUrl'].'">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}</a>';

        $tekst .= '</div>';

    } else {

        // pobranie tokena - START
        if ( PLATNOSC_TPAY_SANDBOX == '1' ) {
            $api_url = 'https://openapi.sandbox.tpay.com/transactions';
            $urlAuth = 'https://openapi.sandbox.tpay.com/oauth/auth';
        } else {
            $api_url = 'https://api.tpay.com/transactions';
            $urlAuth = 'https://api.tpay.com/oauth/auth';
        }
        
        $headers = [
                   'Content-Type: application/x-www-form-urlencoded',
                   'accept: application/json'
                   ];

        $DataAuth = array(
                      "client_id" => PLATNOSC_TPAY_CLIENT_ID,
                      "client_secret" => PLATNOSC_TPAY_SECRET,
                      "scope" => ''
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_URL, $urlAuth);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($DataAuth));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $WynikJsonAuth = curl_exec($ch);

        curl_close($ch);

        $WynikAuth = json_decode($WynikJsonAuth,true);

        if ( isset($WynikAuth['error']) ) {
            $tekst .= $WynikAuth['error_description'] . '<br/>';
            return $tekst;
        }

        if ( isset($WynikAuth['access_token']) && $WynikAuth['access_token'] != '' ) {
            $AuthToken = $WynikAuth['access_token'];

            unset($WynikAuth, $WynikJsonAuth, $headers, $DataAuth, $urlAuth );
            // pobranie tokena - KONIEC

            $headers = [
                   'Content-Type: application/json',
                   'accept: application/json',
                   'Authorization: Bearer ' . $AuthToken
            ];

            if ( strtoupper((string)$zamowienie->info['waluta']) == 'PLN' ) {
                $Kwota = number_format($zamowienie->info['wartosc_zamowienia_val'], 2, ".", "");
            } else {
                // sprawdzenie marzy
                $marza = 1;
                if ( isset($GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]) && $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza'] > 0 ) {
                    $marza = (100 + (float)$GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza']) / 100;
                }
                if ( $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik'] >= 1 ) {
                    $Kwota = number_format((($zamowienie->info['wartosc_zamowienia_val'] / $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik']) * $marza), 2, ".", "");
                } else {
                    $Kwota = number_format((($zamowienie->info['wartosc_zamowienia_val'] / $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik']) * $marza), 2, ".", "");
                }
            }

            $DaneWejsciowe = array(
                             "amount" => $Kwota,
                             "description" => 'Numer zamowienia: ' . $zamowienie_id,
                             "hiddenDescription" => 'zam_'.$zamowienie_id,
                             "lang" => $Jezyk,
                             "payer" => array(
                                             "email" => trim($zamowienie->klient['adres_email']),
                                             "name" => $zamowienie->klient['nazwa'],
                                             "phone" => trim($zamowienie->klient['telefon']),
                                             "address" => $zamowienie->platnik['ulica'],
                                             "code" => $zamowienie->platnik['kod_pocztowy'],
                                             "city" => $zamowienie->platnik['miasto'],
                                             "country" => Funkcje::kodISOKrajuDostawy( $zamowienie->platnik['kraj'] )
                             ),
                             "callbacks" => array(
                                                  "payerUrls" => array(
                                                                       "success" => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=tpay&status=OK&zamowienie_id=' . $zamowienie_id,
                                                                       "error" => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=tpay&status=FAIL&zamowienie_id=' . $zamowienie_id
                                                  ),
                                                  "notification" => array(
                                                                         "url" => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/moduly/platnosc/raporty/tpay/raport.php',
                                                                         "email" => INFO_EMAIL_SKLEPU
                                                  )
                              )
            );

            $DaneWejscioweJson = json_encode($DaneWejsciowe);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $DaneWejscioweJson);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $WynikJson = curl_exec($ch);
            curl_close($ch);
            $Wynik = json_decode($WynikJson,true);

            if ( isset($Wynik['transactionPaymentUrl']) && isset($Wynik['result']) && $Wynik['result'] == 'success' ) {

                $parametry                      = serialize($Wynik);

                $pola = array(
                        array('payment_method_array',$parametry)
                );

                $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$zamowienie_id . "'");
                unset($pola);

                $tekst .= '<div style="text-align:center;padding:5px;">';
                $tekst .= '<div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:</div>';

                $tekst .= '<a class="przyciskZaplac" style="display:inline-block;" href="'.$Wynik['transactionPaymentUrl'].'">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}</a>';
                $tekst .= '</div>';

            } else {

                    $tekst .= '{__TLUMACZ:PLATNOSCI_BLAD_TOKEN}';
                    if ( isset($Wynik['errors']) && isset($Wynik['result']) && $Wynik['result'] == 'failed' ) {
                        foreach ( $Wynik['errors'] as $Blad ) {
                            $tekst .= ' : ' . $Blad['errorMessage'] . '<br />';
                        }
                    }

            }
        } else {
            $tekst = 'Niestety, nie udało się wygenerować płatności';
        }
    }

    return $tekst;

}
?>