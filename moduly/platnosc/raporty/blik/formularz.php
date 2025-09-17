<?php

function PowtorzPlatnosc( $parametry, $zamowienie_id ) {

    $tekst  = '';
    $token  = '';
    $Waluta = 'PLN';
    $Jezyk = 'pl';

    $zamowienie = new Zamowienie($zamowienie_id);
    $termin = time() - strtotime($zamowienie->info['data_zamowienia']);

    if ( (isset($parametry['platnosc_system']) && $parametry['platnosc_system'] != '') || (isset($parametry['p24_session_id']) && $parametry['p24_session_id'] != '') ) {

        //#############################################################
        // platnosc PayU REST
        if ( isset($parametry['platnosc_system']) && $parametry['platnosc_system'] == 'platnosc_payu_rest' ) {

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

                    $Wynik['platnosc_id']     = $parametry['platnosc_id'];
                    $Wynik['platnosc_system'] = $parametry['platnosc_system'];

                    $parametryWynik                    = serialize($Wynik);

                    $pola = array(
                            array('payment_method_array',$parametryWynik)
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

        }

        //#############################################################
        // platnosc PayNow
        if ( isset($parametry['platnosc_system']) && $parametry['platnosc_system'] == 'platnosc_paynow' ) {

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

            if ( $termin < 86400 && isset($parametry['redirectUrl']) && isset($parametry['status']) && $parametry['status'] == 'NEW' ) {
                    
                $tekst .= '<div style="text-align:center;padding:5px;">';
                    $tekst .= '<div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:</div>';

                    $tekst .= '<a class="przyciskZaplac" style="display:inline-block;" href="'.$parametry['redirectUrl'].'">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}</a>';

                $tekst .= '</div>';

            } else {

                $idempotency_key = $zamowienie_id . '-' . time();
                $parsedParameters = array();

                $zamowienie->info['wartosc_zamowienia_val'] = (float)$zamowienie->info['wartosc_zamowienia_val'];

                if ( $zamowienie->info['waluta'] == 'PLN' ) {

                    $kwota = number_format($zamowienie->info['wartosc_zamowienia_val'] * 100, 0, "", "");

                } else {

                    $przelicznikWaluty = $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik'];
                    $kwota = number_format(($zamowienie->info['wartosc_zamowienia_val'] / $przelicznikWaluty) * 100, 0, "", "");

                }

                $opis = 'Numer zamowienia: ' . $zamowienie_id;
                $adres_email = $zamowienie->klient['adres_email'];

                $buyer = array("email" => $adres_email,
                               "locale" => (string)$Jezyk
                               );

                $data = array("amount" => (int)$kwota,
                              "currency"   => (string)$Waluta,
                              "externalId" => $zamowienie_id,
                              "description" => $opis,
                              "continueUrl" => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=paynow&zamowienie_id=' . $zamowienie_id,
                              "buyer" => $buyer
                              );

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

                $data_json = json_encode($data);

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

                    $wynik['platnosc_id']     = $parametry['platnosc_id'];
                    $wynik['platnosc_system'] = $parametry['platnosc_system'];

                    $parametryWynik                      = serialize($wynik);

                    $pola = array(
                            array('payment_method_array',$parametryWynik),
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

        }

        //#############################################################
        // platnosc PayU Classic API
        if ( isset($parametry['platnosc_system']) && $parametry['platnosc_system'] == 'platnosc_payu' ) {

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

            $zapytanie = "SELECT wartosc FROM modules_payment_params WHERE kod = 'PLATNOSC_PAYU_KEY_2'";
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
            $parameters['pay_type'] = $parametry['pay_type'];
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

        }

        //#############################################################
        // platnosc Tpay
        if ( isset($parametry['platnosc_system']) && $parametry['platnosc_system'] == 'platnosc_tpay' ) {

            $zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%PLATNOSC_TPAY_%'";
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

                $AuthToken = $WynikAuth['access_token'];

                unset($WynikAuth, $WynikJsonAuth, $DataAuth, $urlAuth );
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
                                  ),
                                  "pay" => array(
                                            "groupId" => 150
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

                    $Wynik['platnosc_id']     = $parametry['platnosc_id'];
                    $Wynik['platnosc_system'] = $parametry['platnosc_system'];

                    $parametryWynik                      = serialize($Wynik);

                    $pola = array(
                            array('payment_method_array',$parametryWynik)
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

            }

        }

        //#############################################################
        // platnosc Przelewy24
        if ( isset($parametry['platnosc_system']) && (isset($parametry['p24_session_id']) && $parametry['p24_session_id'] != '') ) {
            $token  = '';
            $Waluta = 'PLN';
            $Jezyk = 'pl';
            $BlednaPlatnosc = false;

            $zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_PRZELEWY24_%'";
            $sql = $GLOBALS['db']->open_query($zapytanie);

            while ($info = $sql->fetch_assoc()) {
                define($info['kod'], $info['wartosc']);
            }
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie, $info, $sql);

            // Sprawdza maksymalny czas na wykonanie platnosci
            if ( $termin > (PLATNOSC_PRZELEWY24_TERMIN_PATNOSCI *24 * 3600) ) {
               $pola = array(
                       array('payment_method_array','#')
               );
               $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$zamowienie_id . "'");
               unset($pola);
               return;
            }

            if ( $zamowienie->info['p24_session_id'] != '' ) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
                curl_setopt($ch, CURLOPT_URL, "https://" . ( PLATNOSC_PRZELEWY24_SANDBOX == '1' ? 'sandbox.przelewy24.pl' : 'secure.przelewy24.pl' ) . "/api/v1/transaction/by/sessionId/".$zamowienie->info['p24_session_id']);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, PLATNOSC_PRZELEWY24_ID.":".PLATNOSC_PRZELEWY24_API_KEY);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);

                $wynik_json = curl_exec($ch);
                curl_close($ch);

                $json = json_decode($wynik_json);
                if ( isset($json->data) && isset($json->data->status) && $json->data->status == '0' ) {
                    $BlednaPlatnosc = true;
                }
                unset($wynik_json, $json);
            }

            if ( isset($parametry->data) && isset($parametry->data->token) && $BlednaPlatnosc == false ) {

                $tekst .= '<div style="text-align:center;padding:5px;">';
                    $tekst .= '<div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:</div>';

                    $tekst .= '<a class="przyciskZaplac" style="display:inline-block;" href="https://'.( PLATNOSC_PRZELEWY24_SANDBOX == '1' ? 'sandbox.przelewy24.pl' : 'secure.przelewy24.pl' ).'/trnRequest/'.$parametry->data->token.'">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}</a>';

                $tekst .= '</div>';

            } else {

                $tekst  = '';
                $Waluta = strtoupper((string)$zamowienie->info['waluta']);
                $Jezyk = 'pl';
                $Jezyk = strtolower((string)$_SESSION['domyslnyJezyk']['kod']);
                $Kwota = 0;
                $KanalyPlatnosci = 0;

                $secretId         = PLATNOSC_PRZELEWY24_API_KEY;
                $posId            = PLATNOSC_PRZELEWY24_ID;

                $parameters                         = array();

                $zamowienie->info['wartosc_zamowienia_val'] = (float)$zamowienie->info['wartosc_zamowienia_val'];

                $kwota                              = number_format(($zamowienie->info['wartosc_zamowienia_val']) * 100, 0, "", "");
                $p24_session_id                     = session_id() . '-'. substr(md5(time()), 16);

                $sign   = array(
                        'sessionId'  => (string)$p24_session_id,
                        'merchantId' => (int)PLATNOSC_PRZELEWY24_ID,
                        'amount'     => (int)$kwota,
                        'currency'   => (string)$Waluta,
                        'crc'        => (string)PLATNOSC_PRZELEWY24_CRC,
                );
                $string     = json_encode( $sign, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
                $p24_sign   = hash( 'sha384', $string );

                $KanalyPlatnosciArr = explode(';', PLATNOSC_PRZELEWY24_CHANNEL);
                $KanalyPlatnosci = array_sum($KanalyPlatnosciArr);
                $LimitCzasu = 0;

                $DaneWejsciowe = array(
                                  "merchantId"     => (int)PLATNOSC_PRZELEWY24_ID,
                                  "posId"          => (int)PLATNOSC_PRZELEWY24_ID,
                                  "sessionId"      => (string)$p24_session_id,
                                  "amount"         => (int)$kwota,
                                  "currency"       => (string)$Waluta,
                                  "description"    => 'Numer zamowienia: ' . $zamowienie_id,
                                  "email"          => (string)$zamowienie->klient['adres_email'],
                                  "client"         => (string)( $zamowienie->platnik['nazwa'] != '' ? $zamowienie->platnik['nazwa'] : $zamowienie->klient['nazwa'] ),
                                  "address"        => (string)trim($zamowienie->platnik['ulica']),
                                  "zip"            => (string)trim($zamowienie->platnik['kod_pocztowy']),
                                  "city"           => (string)trim($zamowienie->platnik['miasto']),
                                  "country"        => Funkcje::kodISOKrajuDostawy($zamowienie->platnik['kraj']),
                                  "phone"          => (string)trim($zamowienie->klient['telefon']),
                                  "language"       => (string)$Jezyk,
                                  "method"         => null,
                                  "urlStatus"      => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/moduly/platnosc/raporty/przelewy24/raport.php',
                                  "urlReturn"      => ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=przelewy24&zamowienie_id=' . $zamowienie_id,
                                  "timeLimit"      => (int)$LimitCzasu,
                                  "waitForResult"  => true,
                                  "transferLabel"  => 'Nr zamowienia: ' . $zamowienie_id,
                                  "sign"           => (string)$p24_sign,
                                  "encoding"       => 'UTF-8'
                );

                if ( $KanalyPlatnosci > 0 ) {
                    $DaneWejsciowe['channel']  = (int)$KanalyPlatnosci;
                }

                $headers = [
                    'Content-Type: application/json'
                ];

                $data_json = json_encode($DaneWejsciowe, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, "https://" . ( PLATNOSC_PRZELEWY24_SANDBOX == '1' ? 'sandbox.przelewy24.pl' : 'secure.przelewy24.pl' ) . "/api/v1/transaction/register");
                curl_setopt($ch, CURLOPT_USERPWD, $posId.":".$secretId);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);    
                curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);

                $WynikJson = curl_exec($ch);

                curl_close($ch);
                $Wynik = json_decode($WynikJson);

                if ( isset($Wynik->error) ) {
                    $tekst .= '{__TLUMACZ:PLATNOSCI_BLAD_TOKEN}';
                    $tekst .= ' : ' . $Wynik->error . '<br />';
                    return $tekst;
                }

                if ( isset($Wynik->data) && isset($Wynik->data->token) && $Wynik->data->token != '' ) {

                    $Wynik->p24_session_id    = $p24_session_id;
                    $Wynik->platnosc_system   = $parametry['platnosc_system'];
                    $parametry                  = serialize((array)$Wynik);

                    $pola = array(
                            array('payment_method_array',$parametry),
                            array('p24_session_id',$p24_session_id)
                    );

                    $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . $zamowienie_id . "'");
                    unset($pola);

                    $tekst .= '<div style="text-align:center;padding:5px;">';
                    $tekst .= '<div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:</div>';

                    $tekst .= '<a class="przyciskZaplac" style="display:inline-block;" href="https://'.( PLATNOSC_PRZELEWY24_SANDBOX == '1' ? 'sandbox.przelewy24.pl' : 'secure.przelewy24.pl' ).'/trnRequest/'.$Wynik->data->token.'">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}</a>';

                    $tekst .= '</div>';

                }

            }

        }

    } else {

        return;

    }
    return $tekst;

}
?>