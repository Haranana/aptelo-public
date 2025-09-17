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
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie, $info, $sql);

        $knk_order_id = '';
        $kwota_poprzednia = 0;
        $kwota_zmian = 0;
        $paypo_status = '';
        //
        $zapytanie = "select paypo_order_id, paypo_order_value, paypo_order_new_value, paypo_order_status from orders where orders_id = '" . (int)$id_poz . "'";
        $sql = $db->open_query($zapytanie);       
        //
        $info = $sql->fetch_assoc();  
        //
        if ( $info['paypo_order_id'] != '' ) {
            //
            $knk_order_id = $info['paypo_order_id'];
            $kwota_poprzednia = (int)$info['paypo_order_value'];
            $kwota_zmian = (int)$info['paypo_order_new_value'];
            $paypo_status = $info['paypo_order_status'];
            //
        }
        //
        $db->close_query($sql);
        unset($zapytanie, $info);                     
        //
        
        if ( $paypo_status == 'PROCESSING' ) {

            $zamowienie = new Zamowienie((int)$id_poz);
            $wartosc_zamowienia = number_format((($zamowienie->info['wartosc_zamowienia_val'] / $zamowienie->info['waluta_kurs']) * 100), 0, ".", "");

            $headers = array('Content-Type: application/json"');

            $data = array("knk_merchant_id" => PLATNOSC_PAYPO_ID,
                          "knk_foreign_id" => (int)$zamowienie->info['id_zamowienia'],
                          "knk_order_id" => $knk_order_id,
                          "knk_new_order_amount" => $wartosc_zamowienia,
                          "knk_order_crc" => md5(PLATNOSC_PAYPO_ID . "|" . (int)$zamowienie->info['id_zamowienia'] . "|" . $kwota_poprzednia . "|" . PLATNOSC_PAYPO_API));
                          
            $dane = json_encode($data);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, "https://" . (PLATNOSC_PAYPO_SANDBOX == '1' ? 'sandbox.paypo.pl/api/v2/' : 'paypo.pl/api/v2/') . "order/correct");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dane);    
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $wynik_json = curl_exec($ch);
            
            $wynik = json_decode($wynik_json,true);

            if (isset($wynik['knk_status']) && $wynik['knk_status'] == 'OK') {
                //
                // zmiana statusu paypo
                $pola = array(array('paypo_order_new_value',$wartosc_zamowienia));
                $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$zamowienie->info['id_zamowienia'] . "'");
                unset($pola);
                //
                $_SESSION['info_paypo'] = "<div id='PopUpInfo'>Korekta wartości zamówienia w systemie PayPo wykonana poprawnie.</div>";
                //            
            } else {
                //
                $komunikat = '';
                //
                if ( $wartosc_zamowienia > $kwota_zmian ) {
                     //
                     $komunikat = "<br /><br /><b style='color:#ff0000'>Ze względu na specyfikę usługi nie jest możliwe zwiększenie kwoty transakcji, dozwolona jest jedynie operacja pomniejszenia kwoty !!</b>";
                     //
                }
                //
                $bledy = '';
                //
                if ( isset($wynik['error']) ) {
                     //
                     $bledy = '<br /><br />Błąd PayPo: ' . $wynik['error'] . ((isset($wynik['status'])) ? ' <br /> Status: ' . $wynik['status'] : '');
                     //
                }
                //
                $_SESSION['info_paypo'] = "<div id='PopUpInfo'>Wystąpił problem podczas korekty wartości zamówienia w systemie PayPo !" . $komunikat . $bledy . "</div>";
                //
                unset($bledy);
                //
            }
            
        }

        Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz=' . $id_poz . '&zakladka=3');
    
    } else {
    
        Funkcje::PrzekierowanieURL('zamowienia.php');
    
    }
    
}
?>