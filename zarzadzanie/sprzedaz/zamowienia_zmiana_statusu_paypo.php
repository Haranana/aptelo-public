<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
  
    if ( isset($_GET['tryb']) && ($_GET['tryb'] == 'wyslane' || $_GET['tryb'] == 'anulowane' || $_GET['tryb'] == 'zwrot' || $_GET['tryb'] == 'zwrot_caly') ) {
      
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
            $zapytanie = "select paypo_order_id, paypo_order_value, paypo_order_new_value, paypo_order_status, orders_status from orders where orders_id = '" . (int)$id_poz . "'";
            $sql = $db->open_query($zapytanie);       
            //
            $info = $sql->fetch_assoc();  
            //
            if ( $info['paypo_order_id'] != '' ) {
                //
                $UUID = $info['paypo_order_id'];
                $wartosc_zamowienia = (int)$info['paypo_order_new_value'];
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

            $data = array();

            if ( $_GET['tryb'] == 'wyslane' ) {
                 //
                 $data = array("status" => "COMPLETED");
                 $aurl = $url . "transactions/" . $UUID;
                 //
            }
            if ( $_GET['tryb'] == 'anulowane' ) {
                 //
                 $data = array("status" => "CANCELED");
                 $aurl = $url . "transactions/" . $UUID;
                 //
            }  
            if ( $_GET['tryb'] == 'zwrot_caly' ) {
                 //
                 $zamowienie = new Zamowienie((int)$id_poz);
                 $wartosc_zamowienia_aktualna = (int)number_format((($zamowienie->info['wartosc_zamowienia_val'] / $zamowienie->info['waluta_kurs']) * 100), 0, ".", "");                 
                 $data = array("amount" => (int)$wartosc_zamowienia_aktualna,
                               "referenceRefundId" => $id_poz . '-' . time()
                         );
                 $aurl = $url . "transactions/" . $UUID . "/refunds";
                 //
            }              
            if ( $_GET['tryb'] == 'zwrot' ) {
                 //
                 $zamowienie = new Zamowienie((int)$id_poz);
                 $wartosc_zamowienia_aktualna = (int)number_format((($zamowienie->info['wartosc_zamowienia_val'] / $zamowienie->info['waluta_kurs']) * 100), 0, ".", "");
                 $kwota_zwrotu = $wartosc_zamowienia - $wartosc_zamowienia_aktualna;
                 $data = array("amount" => (int)$kwota_zwrotu,
                               "referenceRefundId" => $id_poz . '-' . time()
                         );
                 $aurl = $url . "transactions/" . $UUID . "/refunds";
                 //
            }              
            $dane = json_encode($data);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
            curl_setopt($ch, CURLOPT_URL, $aurl);
            if ( $_GET['tryb'] == 'wyslane' || $_GET['tryb'] == 'anulowane' ) {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
            }
            if ( $_GET['tryb'] == 'zwrot' ) {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dane);    
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $wynik_json = curl_exec($ch);
            
            $wynik = json_decode($wynik_json,true);
            $komentarz = '';
            
            if ( isset($wynik) && $wynik['code'] == '200' ) {
            
                $_SESSION['info_paypo'] = "<div id='PopUpInfo'>Zmiana statusu w systemie PayPo przebiegła poprawnie.</div>";
                
            } elseif ( isset($wynik) && $wynik['code'] == '201' ) {
            
                $_SESSION['info_paypo'] = "<div id='PopUpInfo'>Zmiana w PayPo przebiegła poprawnie.</div>";
                
            } else {

                $bledy = '';
                //
                $bledy = '<br /><br />Błąd PayPo: ' . $wynik['code'] . ((isset($wynik['message'])) ? ' <br /> Status: ' . $wynik['message'] : '');
                //
                $_SESSION['info_paypo'] = "<div id='PopUpInfo'>Wystąpił problem podczas zmiany statusu w systemie PayPo !" . $bledy . "</div>";
                //
                unset($bledy);
            }

            Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz=' . $id_poz . '&zakladka=3');

        } else {
        
            Funkcje::PrzekierowanieURL('zamowienia.php');
        
        }
    
    } else {
    
        Funkcje::PrzekierowanieURL('zamowienia.php');
    
    }
    
}
?>