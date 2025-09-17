<?php

function PowtorzPlatnosc( $parametry, $zamowienie_id ) {

    $tekst  = '';
    $token  = '';
    $Waluta = 'PLN';
    $Jezyk = 'pl';

    $zamowienie = new Zamowienie($zamowienie_id);
    $termin = time() - strtotime($zamowienie->info['data_zamowienia']);

    $zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE 'PLATNOSC_PAYU_REST_%'";

    $sql = $GLOBALS['db']->open_query($zapytanie);

    while ($info = $sql->fetch_assoc()) {
        define($info['kod'], $info['wartosc']);
    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $sql);

    // Sprawdza maksymalny czas na wykonanie platnosci
    if ( $termin > (PLATNOSC_PAYU_REST_TERMIN_PATNOSCI *24 * 3600) ) {
       $pola = array(
               array('payment_method_array','#')
       );
       $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$zamowienie_id . "'");
       unset($pola);
       return;
    }

    if ( $termin < 86400 && isset($parametry['redirectUri']) && isset($parametry['status']) && $parametry['status']['statusCode'] == 'SUCCESS' ) {

        $tekst .= '<div style="text-align:center;padding:5px;">';
            $tekst .= '<div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:</div>';

            $tekst .= '<a class="przyciskZaplac" style="display:inline-block;" href="'.$parametry['redirectUri'].'">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}</a>';

        $tekst .= '</div>';

    } else {

        // pobranie tokena - START
        if ( (int)PLATNOSC_PAYU_REST_SANDBOX == 1 ) {
            $api_url = 'https://secure.snd.payu.com/api/v2_1/';
            $urlAuth = 'https://secure.snd.payu.com/pl/standard/user/oauth/authorize';
        } else {
            $api_url = 'https://secure.payu.com/api/v2_1/';
            $urlAuth = 'https://secure.payu.com/pl/standard/user/oauth/authorize';
        }
        if ( $zamowienie->info['waluta'] == 'EUR' && PLATNOSC_PAYU_REST_OAUTH_ID_EUR != '' && PLATNOSC_PAYU_REST_OAUTH_SECRET_EUR != '' ) {
            $par_token = 'grant_type=client_credentials&client_id='.PLATNOSC_PAYU_REST_OAUTH_ID_EUR.'&client_secret='.PLATNOSC_PAYU_REST_OAUTH_SECRET_EUR;
        } else {
            $par_token = 'grant_type=client_credentials&client_id='.PLATNOSC_PAYU_REST_OAUTH_ID.'&client_secret='.PLATNOSC_PAYU_REST_OAUTH_SECRET;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlAuth);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $par_token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        $AuthToken = json_decode($result);

        if ( isset($AuthToken->error) ) {
            return $AuthToken->error_description;
        } else {
            $token = $AuthToken->access_token;
        }

        unset($AuthToken);
        // pobranie tokena - KONIEC

        $headers = [
                  'Content-Type: application/json',
                  'Authorization: Bearer ' . $token 
        ];

        $PodzielImieNazwisko = explode(' ', preg_replace('/\s+/', ' ', $zamowienie->klient['nazwa']));
            
        $Produkty = array();

        foreach ( $zamowienie->produkty as $Produkt ) {
            $CenaProduktu = $Produkt['cena_koncowa_brutto'];

            if ( strtoupper((string)$zamowienie->info['waluta']) == 'EUR' && PLATNOSC_PAYU_REST_POS_ID_EUR != '' ) {
                    $CenaProduktu = $Produkt['cena_koncowa_brutto'];
            } else {
                if ( strtoupper((string)$zamowienie->info['waluta']) == 'PLN' ) {
                    $CenaProduktu = $Produkt['cena_koncowa_brutto'];
                } else {
                    // sprawdzenie marzy
                    $marza = 1;
                    if ( isset($GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]) && $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza'] > 0 ) {
                        $marza = (100 + (float)$GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza']) / 100;
                    }
                    if ( $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik'] >= 1 ) {
                        $CenaProduktu = (($Produkt['cena_koncowa_brutto'] / $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik']) * $marza);
                    } else {
                        $CenaProduktu = (($Produkt['cena_koncowa_brutto'] / $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik']) * $marza);
                    }
                }
            }

            $Produkty[] = array("name"      => ( $Produkt['nazwa'] != '' ? $Produkt['nazwa'] : 'Produkt' ),
                                "unitPrice" => (int)number_format((float)$CenaProduktu * 100, 0, "", ""),
                                "quantity"  => $Produkt['ilosc']);
        }

        $Jezyk = strtolower((string)$_SESSION['domyslnyJezyk']['kod']);
        if ( $zamowienie->info['waluta'] == 'EUR' && PLATNOSC_PAYU_REST_POS_ID_EUR != '' && PLATNOSC_PAYU_REST_OAUTH_SECRET_EUR != '' ) {
            $Waluta = 'EUR';
        }

        $zamowienie->info['wartosc_zamowienia_val'] = (float)$zamowienie->info['wartosc_zamowienia_val'];

        if ( strtoupper((string)$zamowienie->info['waluta']) == 'EUR' && PLATNOSC_PAYU_REST_POS_ID_EUR != '' ) {
                $Kwota = number_format($zamowienie->info['wartosc_zamowienia_val'] * 100, 0, "", "");
        } else {
            if ( strtoupper((string)$zamowienie->info['waluta']) == 'PLN' ) {
                $Kwota = number_format($zamowienie->info['wartosc_zamowienia_val'] * 100, 0, "", "");
            } else {
                // sprawdzenie marzy
                $marza = 1;
                if ( isset($GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]) && $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza'] > 0 ) {
                    $marza = (100 + (float)$GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['marza']) / 100;
                }
                if ( $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik'] >= 1 ) {
                    $Kwota = number_format((($zamowienie->info['wartosc_zamowienia_val'] / $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik']) * $marza) * 100, 0, "", "");
                } else {
                    $Kwota = number_format((($zamowienie->info['wartosc_zamowienia_val'] / $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik']) * $marza) * 100, 0, "", "");
                }
            }
        }

        $DaneWejsciowe = array(
                      "notifyUrl"     => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/moduly/platnosc/raporty/payu_rest/raport.php',
                      "customerIp"    => $_SERVER["REMOTE_ADDR"],
                      "merchantPosId" => ( $zamowienie->info['waluta'] == 'EUR' && PLATNOSC_PAYU_REST_POS_ID_EUR != '' && PLATNOSC_PAYU_REST_OAUTH_SECRET_EUR != '' ? PLATNOSC_PAYU_REST_POS_ID_EUR : PLATNOSC_PAYU_REST_POS_ID ),
                      "extOrderId"    => $zamowienie_id . ':' . time(),
                      "description"   => 'Numer zamowienia: ' . $zamowienie_id,
                      "currencyCode"  => $Waluta,
                      "totalAmount"   => (int)$Kwota,
                      "continueUrl"   => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=payu_rest&zamowienie_id=' . $zamowienie_id,
                      "validityTime"  => '3600',
                      "buyer" => array("email"     => $zamowienie->klient['adres_email'],
                                       "phone"     => $zamowienie->klient['telefon'],
                                       "firstName" => trim($PodzielImieNazwisko[0]),
                                       "lastName"  => trim($PodzielImieNazwisko[count($PodzielImieNazwisko)-1]),
                                       "language"  => $Jezyk

                                      ),
                      "products" => $Produkty

                     );
        unset($PodzielImieNazwisko, $Produkty, $Kwota, $marza);

        $DaneWejscioweJson = json_encode($DaneWejsciowe);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $api_url . "orders");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $DaneWejscioweJson);    
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $WynikJson = curl_exec($ch);
        curl_close($ch);

        $Wynik = json_decode($WynikJson,true);

        if ( isset($Wynik['redirectUri']) && isset($Wynik['status']) && $Wynik['status']['statusCode'] == 'SUCCESS' ) {

            $parametry                      = serialize($Wynik);

            $pola = array(
                    array('payment_method_array',$parametry)
            );

            $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$zamowienie_id . "'");
            unset($pola);

            $tekst .= '<div style="text-align:center;padding:5px;">';
            $tekst .= '<div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:</div>';

            $tekst .= '<a class="przyciskZaplac" style="display:inline-block;" href="'.$Wynik['redirectUri'].'">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}</a>';
            $tekst .= '</div>';

        } else {

            $tekst .= '{__TLUMACZ:PLATNOSCI_BLAD_TOKEN}';
            if ( isset($Wynik['status']) && isset($Wynik['status']['statusDesc']) ) {
                $tekst .= ' : ' . $Wynik['status']['statusDesc'] . '<br />';
            }

        }

    }

    return $tekst;

}
?>