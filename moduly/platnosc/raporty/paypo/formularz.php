<?php

function PowtorzPlatnosc( $parametry, $zamowienie_id ) {

    $tekst = '';
    $zamowienie = new Zamowienie($zamowienie_id);
    $termin = time() - FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia']);

    $zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_PAYPO_%'";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    while ($info = $sql->fetch_assoc()) {
        define($info['kod'], $info['wartosc']);
    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $sql);

    // Sprawdza maksymalny czas na wykonanie platnosci
    if ( $termin > (PLATNOSC_PAYPO_TERMIN_PATNOSCI *24 * 3600) ) {
       $pola = array(
               array('payment_method_array','#')
       );
       $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$zamowienie_id . "'");
       unset($pola);
       return;
    }

    if ( $termin < 259200 && isset($parametry['linkToPayPo']) ) {
            
        $tekst .= '<div style="text-align:center;padding:5px;">';
            $tekst .= '<div style="margin:10px 0; overflow:hidden;">{__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:</div>';

            $tekst .= '<a class="przyciskZaplac" style="display:inline-block;" href="'.$parametry['linkToPayPo'].'">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}</a>';

        $tekst .= '</div>';

    } else {

        // pobranie tokena - START
        $par_token = 'grant_type=client_credentials&client_id='.PLATNOSC_PAYPO_ID.'&client_secret='.PLATNOSC_PAYPO_API;
        if ( PLATNOSC_PAYPO_SANDBOX == '1' ) {
            $url = 'https://api.sandbox.paypo.pl/v3/';
        } else {
            $url = 'https://api.paypo.pl/v3/';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . 'oauth/tokens');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $par_token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $wynik = curl_exec($ch);

        curl_close($ch);

        $AuthToken = json_decode($wynik);

        if ( isset($AuthToken->error) ) {
            $tekst .= $AuthToken->error_description;
            return $tekst;
        } else {
            $token = $AuthToken->access_token;
        }

        unset($AuthToken, $par_token, $wynik);
        // pobranie tokena - KONIEC

        // pobranie linku do platnosci - START
        $headers = [
                   'Content-Type: application/json',
                   'Authorization: Bearer ' . $token
                   ];

        $parametry['platnosc']['id'] = Funkcje::UUIDv4();
        $parametry['platnosc']['order']['referenceId'] = $parametry['platnosc']['order']['referenceId'] . '-'. time();
        $DaneWejscioweJson = json_encode($parametry['platnosc']);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url . 'transactions');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $DaneWejscioweJson);    
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $wynik_json = curl_exec($ch);

        curl_close($ch);

        $paypoResp = json_decode($wynik_json,true);

        if ( isset($paypoResp['code']) ) {
            $tekst .= '<div style="text-align:center;padding:5px;">';
            $tekst .= $paypoResp['code'] . ' : ' . $paypoResp['message'] . '<br />';
            $tekst .= '</div>';
            return $tekst;
        }

        if ( isset($paypoResp['transactionId']) && isset($paypoResp['redirectUrl']) ) {

            $linkToPayPo = "";
            $linkToPayPo = $paypoResp["redirectUrl"];

            $parametry['linkToPayPo'] = $linkToPayPo;
            $parametry['uuid'] = $paypoResp['transactionId'];
            $parametry['platnosc']['order']['referenceId'] = $zamowienie_id;

            $parametry                      = serialize($parametry);

            $tekst .= '<div style="text-align:center;padding:5px;">';
            $tekst .= '   <a href="'. $linkToPayPo .'" class="przyciskZaplac" type="submit" id="submitButton">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}</a><br /><br />';

            $tekst .= '</div>';

            $pola = array(
                    array('payment_method_array',$parametry),
            );

            $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$zamowienie_id . "'");
            unset($pola);
        } else {
            $tekst .= '{__TLUMACZ:PLATNOSCI_BLAD}' . '<br />';
            if ( isset($paypoResp['code']) ) {
                $tekst .= $paypoResp['code'] . ' : ' . $paypoResp['message'] . '<br />';
            }
        }

        return $tekst;

    }

    return $tekst;

}
?>