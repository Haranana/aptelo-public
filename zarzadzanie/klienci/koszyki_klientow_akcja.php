<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['opcja'])) {
        //
        if (count($_POST['opcja']) > 0) {
    
            foreach ($_POST['opcja'] as $pole) {
    
                switch ((int)$_POST['akcja_dolna']) {
                  
                    case 1:
                    
                       // ustali ip
                       $zapytanie = "SELECT customers_ip, customers_id FROM customers_basket WHERE customers_basket_id = '" . $pole . "'";
                       $sql = $db->open_query($zapytanie);
                      
                       if ( (int)$db->ile_rekordow($sql) > 0 ) {
                             //
                             $info = $sql->fetch_assoc();
                             //    
                             $pattern = '/\b((25[0-5]|2[0-4][0-9]|1?[0-9]{1,2})\.){3}(25[0-5]|2[0-4][0-9]|1?[0-9]{1,2})\b/';
                             $nrip = '';
                             //
                             if (preg_match($pattern, $info['customers_ip'], $match)) {
                                 $nrip = $match[0];
                             }          
                             //
                             $info['customers_ip'] = $nrip;
                             // 
                             $db->delete_query('customers_basket', "customers_ip = '" . $info['customers_ip'] . "' and customers_id = '" . $info['customers_id'] . "'");
                             //
                       }
                      
                       $db->close_query($sql);                    
                       unset($zapytanie);                
                      
                }          

            }
        
        }
        //
    }
            
    Funkcje::PrzekierowanieURL('koszyki_klientow.php');
    
}
?>