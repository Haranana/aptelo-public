<?php

function PowtorzPlatnosc( $parametry, $zamowienie_id ) {

    $tekst = '';
    $zamowienie = new Zamowienie($zamowienie_id);
    $termin = time() - FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia']);

    $zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_PAYNOW_%'";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    while ($info = $sql->fetch_assoc()) {
        define($info['kod'], $info['wartosc']);
    }

    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $sql);

    // Sprawdza maksymalny czas na wykonanie platnosci
    if ( $termin > (PLATNOSC_PAYNOW_TERMIN_PATNOSCI *24 * 3600) ) {
       $pola = array(
               array('payment_method_array','#')
       );
       $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$zamowienie_id . "'");
       unset($pola);
       return;
    }

    if ( $termin < 864000 && isset($parametry['redirectUrl']) && isset($parametry['status']) && $parametry['status'] == 'NEW' ) {
            
        $tekst .= '<div style="text-align:center;padding:5px;">';
            $tekst .= '<div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:</div>';

            $tekst .= '<a class="przyciskZaplac" style="display:inline-block;" href="'.$parametry['redirectUrl'].'">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}</a>';

        $tekst .= '</div>';

    } else {

        $sygnatura        = '';
        $Waluta           = 'PLN';
        $Jezyk            = 'pl-PL';
        $Kwota            = 0;
        $Waluta           = strtoupper((string)$zamowienie->info['waluta']);
        $parsedParameters = array();

        $idempotency_key  = $zamowienie_id . '-' . time();

        $zamowienie->info['wartosc_zamowienia_val'] = (float)$zamowienie->info['wartosc_zamowienia_val'];

        if ( strtoupper($zamowienie->info['waluta']) == 'PLN' ) {

            $Kwota = number_format($zamowienie->info['wartosc_zamowienia_val'] * 100, 0, "", "");

        } elseif ( strtoupper($zamowienie->info['waluta']) == 'EUR' || strtoupper($zamowienie->info['waluta']) == 'USD' || strtoupper($zamowienie->info['waluta']) == 'GBP' ) {

            $Kwota = number_format($zamowienie->info['wartosc_zamowienia_val'] * 100, 0, "", "");
            $Jezyk = 'en-GB';

        } else {

            $Waluta = 'PLN';

            $przelicznikWaluty = $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik'];

            $marza = 1;
            if ( isset($GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]) && $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza'] > 0 ) {
                $marza = (100 + (float)$GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza']) / 100;
            }

            $Kwota = number_format(($zamowienie->info['wartosc_zamowienia_val'] / ($przelicznikWaluty * $marza)) * 100, 0, "", "");

        }

        $opis          = 'Numer zamowienia: ' . $zamowienie_id;
        $adres_email   = $zamowienie->klient['adres_email'];

        $AdresPlatnika = Funkcje::rozbijAdres(trim(str_replace(['"',"'"], " ", (string)$zamowienie->platnik['ulica'])));
        $AdresWysylki  = Funkcje::rozbijAdres(trim(str_replace(['"',"'"], " ", (string)$zamowienie->dostawa['ulica'])));

        $PodzielImieNazwisko = explode(' ', preg_replace('/\s+/', ' ', (string)$zamowienie->klient['nazwa']));

        $buyer_address = array(
                               "billing" => array(
                                   "street" => (string)$AdresPlatnika['ulica'],
                                   "houseNumber" => (string)$AdresPlatnika['numer_domu'],
                                   "apartmentNumber" => (string)$AdresPlatnika['numer_mieszkania'],
                                   "zipcode" => PlatnosciElektroniczne::format_postcode($zamowienie->platnik['kod_pocztowy']),
                                   "city" => (string)$zamowienie->platnik['miasto'],
                                   "country" => (string)Funkcje::kodISOKrajuDostawy($zamowienie->platnik['kraj'])
                                ),
                                "shipping" => array(
                                   "street" => (string)$AdresWysylki['ulica'],
                                   "houseNumber" => (string)$AdresWysylki['numer_domu'],
                                   "apartmentNumber" => (string)$AdresWysylki['numer_mieszkania'],
                                   "zipcode" => PlatnosciElektroniczne::format_postcode($zamowienie->dostawa['kod_pocztowy']),
                                   "city" => (string)$zamowienie->dostawa['miasto'],
                                   "country" => (string)Funkcje::kodISOKrajuDostawy($zamowienie->dostawa['kraj'])
                                )
                           );

        $buyer_addres_encoded = PlatnosciElektroniczne::urlencode_recursive($buyer_address);

        $buyer = array("email" => $adres_email,
                       "firstName" => urlencode((string)$PodzielImieNazwisko[0]),
                       "lastName" => urlencode((string)$PodzielImieNazwisko[count($PodzielImieNazwisko)-1]),
                       "address" => $buyer_addres_encoded,
                       "locale" => (string)$Jezyk
                       );

        $data = array("amount" => (int)$Kwota,
                      "currency"   => (string)$Waluta,
                      "externalId" => $zamowienie_id,
                      "description" => $opis,
                      "continueUrl" => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=paynow&zamowienie_id=' . $zamowienie_id,
                      "buyer" => $buyer
                      );

        unset($PodzielImieNazwisko, $AdresPlatnika, $AdresWysylki);

        $signatureBody = [
                       'headers' => [
                           'Api-Key' => PLATNOSC_PAYNOW_API_KEY,
                           'Idempotency-Key' => $idempotency_key,
                       ],
                       'parameters' => $parsedParameters ?: new \stdClass(),
                       'body' => $data ? json_encode($data, JSON_UNESCAPED_SLASHES) : ''
            ];

        $sygnatura = base64_encode(hash_hmac('sha256', json_encode($signatureBody, JSON_UNESCAPED_SLASHES), PLATNOSC_PAYNOW_SIGNATURE_KEY, true));

        $headers = [
                        'Content-Type: application/json',
                        'Api-Key: ' . PLATNOSC_PAYNOW_API_KEY . '',
                        'Signature: ' . (string)$sygnatura . '',
                        'Idempotency-Key: ' . (string)$idempotency_key . '' 
        ];

        $ch = curl_init();

        curl_setopt_array($ch, array(
              CURLOPT_URL => 'https://' . ( PLATNOSC_PAYNOW_SANDBOX == "1" ? "api.sandbox.paynow.pl" : "api.paynow.pl" ) . '/v3/payments',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_TIMEOUT => 0,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
              CURLOPT_HTTPHEADER => $headers,
            ));

        $wynik_json = curl_exec($ch);
        curl_close($ch);

        $wynik = json_decode($wynik_json,true);

        $tekst .= '<div style="text-align:center;padding:5px;">';
        if ( !isset($wynik['errors']) && isset($wynik['redirectUrl']) && isset($wynik['status']) && $wynik['status'] == 'NEW' ) {
            $parametry                      = serialize($wynik);

            $pola = array(
                    array('payment_method_array',$parametry),
                    array('paynow_idempotency',$idempotency_key)
            );

            $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . $zamowienie_id . "'");
            unset($pola);

            $tekst .= '<div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:</div>';
            $tekst .= '<a class="przyciskZaplac" style="display:inline-block;" href="'.$wynik['redirectUrl'].'">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}</a>';

        } else {
            $tekst .= '{__TLUMACZ:PLATNOSCI_BLAD_TOKEN}';
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