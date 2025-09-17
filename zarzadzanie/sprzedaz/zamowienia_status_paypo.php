<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
  
    if (!isset($_GET['id_poz'])) {
        $_GET['id_poz'] = 0;
    }
    $id_poz = $_GET['id_poz'];

    if ((int)$id_poz > 0) {  

        $zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_PAYPO_%'";
        $sql = $db->open_query($zapytanie);

        while ($info = $sql->fetch_assoc()) {
            define($info['kod'], $info['wartosc']);
        }
        $db->close_query($sql);
        unset($zapytanie, $info, $sql);

        $UUID = '';
        $wartosc_zamowienia = 0;
        $status_paypo_id = '';
        $status_zamowienia_id = 1;
        //
        $zapytanie = "select paypo_order_id, paypo_order_value, paypo_order_status, orders_status from orders where orders_id = '" . (int)$id_poz . "'";
        $sql = $db->open_query($zapytanie);       
        //
        $info = $sql->fetch_assoc();  
        //
        if ( $info['paypo_order_id'] != '' ) {
            //
            $UUID = $info['paypo_order_id'];
            $wartosc_zamowienia = (int)$info['paypo_order_value'];
            $status_paypo_id = $info['paypo_order_status'];
            $status_zamowienia_id = $info['orders_status'];
            //
        }
        //
        $db->close_query($sql);
        unset($zapytanie, $info);                     
        //

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

        $headers = [
                   'Content-Type: application/json',
                   'Authorization: Bearer ' . $token
                   ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_URL, $url . "transactions/" . $UUID);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $wynik_json = curl_exec($ch);
        
        $paypoResp = json_decode($wynik_json,true);

        $komentarz = '';

        if ( isset($paypoResp['code']) ) {
            $komentarz .= $paypoResp['code'] . ' : ' . $paypoResp['message'] . '<br />';
        }

        if ( isset($paypoResp['transactionId']) ) {

            $komentarz .= 'Numer transakcji: ' . $paypoResp['transactionId'] . '<br />';
            $komentarz .= 'Status transakcji: ' . $paypoResp['transactionStatus'] . '<br />';
            $komentarz .= 'Status płatności PAYPO: ' . $paypoResp['settlementStatus'] . '<br />';
            $komentarz .= 'Wartość zamówienia w PAYPO: ' . number_format(($paypoResp['amount'] / 100), 2, ',', '') . '<br />';

            // zmiana w bazie jezeli jest inny status niz zapisany

            if ( $paypoResp['transactionStatus'] != $status_paypo_id ) {
            
                 $komentarz_status = 'Numer transakcji: ' . $paypoResp['transactionId'] . '<br />';
                 $komentarz_status .= 'Data zmiany statusu PayPo: ' . $paypoResp['lastUpdate'] . '<br />';
                 $komentarz_status .= 'Status transakcji: ' . $paypoResp['transactionStatus'] . '<br />';
                 $komentarz_status .= 'Status płatności PAYPO: ' . $paypoResp['settlementStatus'] ;            
 
                 $pola = array(
                         array('orders_id',(int)$id_poz),
                         array('orders_status_id',$status_zamowienia_id),
                         array('date_added','now()'),
                         array('customer_notified','0'),
                         array('customer_notified_sms','0'),
                         array('comments',$komentarz_status)
                 );
                 $db->insert_query('orders_status_history' , $pola);
                 unset($pola);
                
                 if (isset($wynik['knk_status']) && $wynik['knk_status'] == 'OK') {
                     //
                     // zmiana statusu paypo
                     $pola = array(array('paypo_order_status', $paypoResp['transactionStatus']));
                     $db->update_query('orders' , $pola, "orders_id = '" . (int)$id_poz . "'");
                     unset($pola);                      
                     //
                 }    

            }

        }
        if ( isset($paypoResp['transactionId']) ) {
            //
            $_SESSION['info_paypo'] = "<div id='PopUpInfo'>Szczegóły statusu zamówienia w systemie PayPo:" . (($komentarz != '') ? '<br /><br />' . $komentarz : $komentarz) . "</div>";
            //
        }
        if ( isset($paypoResp['code']) ) {
            //
            $bledy = '';
            //
            $bledy = '<br /><br />Błąd PayPo:<br />' . $komentarz;
            //
            $_SESSION['info_paypo'] = "<div id='PopUpInfo'>Wystąpił problem podczas sprawdzania statusu zamówienia w systemie PayPo !" . $bledy . "</div>";
            //
            unset($bledy);
            //
        }
        if ( count((array)$paypoResp) == 0 ) {
            //
            $bledy = '';
            //
            $bledy = '<br /><br />Błąd PayPo:<br />' . $wynik_json;
            //
            $_SESSION['info_paypo'] = "<div id='PopUpInfo'>Wystąpił problem podczas sprawdzania statusu zamówienia w systemie PayPo !" . $bledy . "</div>";
            //
            unset($bledy);
            //
        }

        Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz=' . $id_poz . '&zakladka=3');

    } else {
    
        Funkcje::PrzekierowanieURL('zamowienia.php');
    
    }
    
}
?>