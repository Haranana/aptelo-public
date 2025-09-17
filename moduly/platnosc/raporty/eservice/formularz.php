<?php

function PowtorzPlatnosc( $parametry, $zamowienie_id ) {
  

    $zamowienie = new Zamowienie($zamowienie_id);
    $termin = time() - strtotime($zamowienie->info['data_zamowienia']);

    $zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE 'PLATNOSC_ESERVICE_%'";

    $sql = $GLOBALS['db']->open_query($zapytanie);

    while ($info = $sql->fetch_assoc()) {
        define($info['kod'], $info['wartosc']);
    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $sql);

    // Sprawdza maksymalny czas na wykonanie platnosci
    if ( $termin > (PLATNOSC_ESERVICE_TERMIN_PATNOSCI *24 * 3600) ) {
       $pola = array(
               array('payment_method_array','#')
       );
       $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$zamowienie_id . "'");
       unset($pola);
       return;
    }

    $zapytanie_parametry = "SELECT kod, wartosc FROM modules_payment_params WHERE kod in ('PLATNOSC_ESERVICE_CLIENTID','PLATNOSC_ESERVICE_HASLO','PLATNOSC_ESERVICE_SANDBOX')";
    $sql_parametry = $GLOBALS['db']->open_query($zapytanie_parametry);
    
    $parametry_conf = array();
    
    while ($info_parametry = $sql_parametry->fetch_assoc()) {
        //
        $parametry_conf[$info_parametry['kod']] = $info_parametry['wartosc'];
        //
    }
    
    $GLOBALS['db']->close_query($sql_parametry);
    unset($zapytanie_parametry, $info_parametry);  
    
    $nr_tmp = explode('-', (string)$parametry['OrderId']);
    
    $zamowienie = new Zamowienie( (int)$nr_tmp[0] );
    
    $suma_zamowienia = number_format($zamowienie->info['wartosc_zamowienia_val'], 2, ".", "");
    
    $waluta = $zamowienie->info['waluta'];
    
    $waluta_zamowienia = '985';
    
    // tablica walut
    $tablica_walut = array(array('985','PLN'),
                           array('978','EUR'),
                           array('840','USD'),
                           array('826','GBP'),
                           array('756','CHF'),
                           array('208','DKK'),
                           array('124','CAD'),
                           array('578','NOK'),
                           array('752','SEK'),
                           array('643','RUB'),
                           array('440','LTL'),
                           array('946','RON'),
                           array('203','CZK'),
                           array('392','JPY'),
                           array('348','HUF'),
                           array('191','HRK'),
                           array('980','UAH'),
                           array('949','TRY'));    

    foreach ( $tablica_walut as $tmp ) {
        //
        if ( $tmp[1] == $waluta ) {
             $waluta_zamowienia = $tmp[0];
        }
        //
    }    
    
    $nr_rand_zam = time();
  
    // pobranie tokena
    $par_token = "ClientId=" . $parametry_conf['PLATNOSC_ESERVICE_CLIENTID'] . "&Password=" . $parametry_conf['PLATNOSC_ESERVICE_HASLO'] . "&OrderId=" . $zamowienie->info['id_zamowienia'] . '-' . $nr_rand_zam . "&Total=" . $suma_zamowienia . "&Currency=" . $waluta_zamowienia;

    $ch = curl_init();
    if ( (int)$parametry_conf['PLATNOSC_ESERVICE_SANDBOX'] == 1 ) {
         curl_setopt($ch, CURLOPT_URL, 'https://testvpos.eservice.com.pl/pg/token');
    } else {
         curl_setopt($ch, CURLOPT_URL, 'https://pay.eservice.com.pl/pg/token');
    }
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $par_token);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    
    $odpowiedz = array();
    $odpowiedz = explode('&', (string)$result);

    for ( $i=0; $i < count($odpowiedz); $i++ ) {
        $tmp = explode('=', (string)$odpowiedz[$i]);
        $odpowiedz[$i] = array($tmp[0] => ( isset($tmp[1]) ? $tmp[1] : '' ));
        unset($tmp);
    }        
    
    $wynik = array();

    foreach ( $odpowiedz as $rekord ) {
        foreach ( $rekord as $key => $value ) {
            $wynik[$key] = urldecode($value);
        }
    }        
    
    $token = '';        
    if ( isset($wynik['status']) && strtoupper((string)$wynik['status']) == 'OK' ) {      
        $token = $wynik['msg'];
    }   

    $parameters['ClientId']         = $parametry_conf['PLATNOSC_ESERVICE_CLIENTID'];
    $parameters['StoreType']        = '3d_pay_hosting';
    $parameters['Token']            = $token;
    $parameters['TranType']         = 'Auth';
    $parameters['Total']            = $suma_zamowienia;
    $parameters['Currency']         = $waluta_zamowienia;
    
    $parameters['OrderId']          = $zamowienie->info['id_zamowienia'] . '-' . $nr_rand_zam;
    
    $PodzielImieNazwisko = explode(' ', (string)$zamowienie->klient['nazwa']);
    
    $parameters['ConsumerName']     = $PodzielImieNazwisko[0];
    $parameters['ConsumerSurname']  = $PodzielImieNazwisko[count($PodzielImieNazwisko)-1];
    
    unset($PodzielImieNazwisko);        
    
    $parameters['okUrl']            = ADRES_URL_SKLEPU . "/platnosc_koniec.php?typ=eservice&status=OK";            
    $parameters['failUrl']          = ADRES_URL_SKLEPU . "/platnosc_koniec.php?typ=eservice&status=FAIL";
    $parameters['pendingUrl']       = ADRES_URL_SKLEPU . "/platnosc_koniec.php?typ=eservice&status=OK";
    $parameters['callbackUrl']      = ADRES_URL_SKLEPU . "/moduly/platnosc/raporty/eservice/raport.php";
    
    $parameters['lang']             = (($_SESSION['domyslnyJezyk']['kod'] != 'pl') ? 'en' : 'pl');

    $parameters['hashAlgorithm']    = 'ver2';
    
    if ( $zamowienie->platnik['nazwa'] != '' ) {
         $parameters['BillToName']       = $zamowienie->platnik['nazwa'];
    }
    if ( $zamowienie->platnik['firma'] != '' ) {
         $parameters['BillToCompany']    = $zamowienie->platnik['firma'];
    }
    $parameters['BillToPostalCode'] = $zamowienie->platnik['kod_pocztowy'];
    $parameters['BillToStreet1']    = $zamowienie->platnik['ulica'];
    $parameters['BillToCity']       = $zamowienie->platnik['miasto'];    
    $parameters['BillToCountry']    = Funkcje::kodISOKrajuDostawy( $zamowienie->platnik['kraj'] );      
    
    if ( $zamowienie->dostawa['nazwa'] != '' ) {
         $parameters['ShipToName']       = $zamowienie->dostawa['nazwa'];
    }
    if ( $zamowienie->dostawa['firma'] != '' ) {
         $parameters['ShipToCompany']    = $zamowienie->dostawa['firma'];
    }
    $parameters['ShipToPostalCode'] = $zamowienie->dostawa['kod_pocztowy'];
    $parameters['ShipToStreet1']    = $zamowienie->dostawa['ulica'];
    $parameters['ShipToCity']       = $zamowienie->dostawa['miasto'];    
    $parameters['ShipToCountry']    = Funkcje::kodISOKrajuDostawy( $zamowienie->dostawa['kraj'] );        

    $formularz = '';
    foreach ( $parameters as $key => $value ) {
        if ( $key != 'rodzaj_platnosci' ) {
            $formularz .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';
        }
    }

    $tekst = '<form action="' . (((int)$parametry_conf['PLATNOSC_ESERVICE_SANDBOX'] == 1) ? 'https://testvpos.eservice.com.pl/fim/eservicegate' : 'https://pay.eservice.com.pl/fim/eservicegate') . '" method="post" name="eserviceform" class="cmxform">
                   <div style="text-align:center;padding:5px;">
                      {__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:<br /><br />';
    $tekst .= $formularz;
    $tekst .= '   <input class="przyciskZaplac" type="submit" id="submitButton" value="{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}" /><br />
                   </div>
              </form>';

    return $tekst;

}
?>