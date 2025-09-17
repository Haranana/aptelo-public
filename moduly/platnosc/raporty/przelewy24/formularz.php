<?php

function PowtorzPlatnosc( $parametry, $zamowienie_id ) {

    $tekst  = '';
    $token  = '';
    $Waluta = 'PLN';
    $Jezyk = 'pl';
    $BlednaPlatnosc = false;

    $zamowienie = new Zamowienie($zamowienie_id);
    $termin = time() - FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia']);

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
            $parametry                  = serialize($Wynik);

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

    return $tekst;

}
?>