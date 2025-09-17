<?php

function PowtorzPlatnosc( $parametry, $zamowienie_id ) {

    $tekst = '';
    $zamowienie = new Zamowienie($zamowienie_id);
    $termin = time() - FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia']);

    $zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_BLUEMEDIA_%'";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    while ($info = $sql->fetch_assoc()) {
        define($info['kod'], $info['wartosc']);
    }

    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $sql);

    // Sprawdza maksymalny czas na wykonanie platnosci
    if ( $termin > (PLATNOSC_BLUEMEDIA_TERMIN_PATNOSCI *24 * 3600) ) {
       $pola = array(
               array('payment_method_array','#')
       );
       $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$zamowienie_id . "'");
       unset($pola);
       return;
    }

    $DanePlatnosci = simplexml_load_string($parametry);

    if ( $termin < 518400 && isset($DanePlatnosci->redirecturl) && isset($DanePlatnosci->status) && $DanePlatnosci->status == 'PENDING' ) {
            
        $tekst .= '<div style="text-align:center;padding:5px;">';
            $tekst .= '<div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:</div>';

            $tekst .= '<a class="przyciskZaplac" style="display:inline-block;" href="'.$DanePlatnosci->redirecturl.'">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}</a>';

        $tekst .= '</div>';

    } else {

        // sprawdzenie marzy
        $marza = 1;
        if ( $zamowienie->info['waluta'] == PLATNOSC_BLUEMEDIA_WALUTA ) {

            $kwota = number_format($zamowienie->info['wartosc_zamowienia_val'], 2, ".", "");
            $waluta = PLATNOSC_BLUEMEDIA_WALUTA;

        } else {

            if ( isset($GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]) && $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza'] > 0 ) {
                $marza = (100 + (float)$GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza']) / 100;
            }
            $kwota = number_format((($zamowienie->info['wartosc_zamowienia_val'] / $zamowienie->info['waluta_kurs']) * $marza), 2, ".", "");
            $waluta = $zamowienie->info['waluta'];

        }

            $data = array(
                    'ServiceID' => PLATNOSC_BLUEMEDIA_IDSKLEPU,
                    'OrderID' => $zamowienie_id,
                    'Amount' => $kwota,
                    'Currency' => $waluta,
                    'CustomerEmail' => trim((string)$zamowienie->klient['adres_email'])
            );

            //ustawiam hash oraz go dodaję do parametrów curla
            $hash_data = $data;
            $hash = hash('sha256', implode('|', (array)$hash_data) . '|' . PLATNOSC_BLUEMEDIA_KLUCZ);
            $data['Hash'] = $hash;

            //łączę się z serwerem bm.pl
            $fields = (is_array($data)) ? http_build_query($data) : $data;

            $curl = curl_init(PLATNOSC_BLUEMEDIA_URL);

            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); 
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('BmHeader: pay-bm-continue-transaction-url'));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 5);

            $curlResponse = curl_exec($curl);

            curl_close($curl);

            $response_bm = htmlspecialchars_decode($curlResponse);
            $response_bm = simplexml_load_string($response_bm);

            //jeżeli jest wszystko ok to przekierowuję na do panelu płatności bm.pl
            // link ten jest zwracany w xmlu
            $tekst .= '<div style="text-align:center;padding:5px;">';
            if (isset($response_bm->status) && $response_bm->status == 'PENDING') {
                //$parametry                      = serialize($curlResponse);

                $pola = array(
                        array('payment_method_array',$response_bm->asXML()),
                        array('paynow_idempotency',$response_bm->remoteID)
                );

                $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . $zamowienie_id . "'");

                unset($pola);

                $tekst .= '<div style="text-align:center;padding:5px;">';
                
                $tekst .= '<div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:</div>';

                //przekieruję do serwisu bm.pl
                $tekst .= '<a class="przyciskZaplac" style="display:inline-block;" href="'.$response_bm->redirecturl.'">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}</a>';

                $tekst .= '</div>';
            } else {
                $tekst .= '{__TLUMACZ:PLATNOSCI_BLAD_TOKEN}' . '<br />';

                if ( isset($wynik['errors']) && isset($wynik['errors']) > 0 ) {
                    foreach ( $wynik['errors'] as $blad ) {
                        $tekst .= $blad['message'] . '<br />';
                    }
                }
            }
            $tekst .= '</div>';

    }

    return $tekst;

}
?>