<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja_dolna']) && (int)$_POST['akcja_dolna'] > 0) {
    
        switch ((int)$_POST['akcja_dolna']) {
            case 1:
                //
                if (isset($_POST['opcja']) && count($_POST['opcja']) > 0) {

                    foreach ($_POST['opcja'] as $pole) {
                        // kasowanie kuponow wybranych ------------ ** -------------  
                        $db->delete_query('coupons' , " coupons_id = '".$pole."'");     
                        $db->delete_query('coupons_to_orders' , " coupons_id = '".$pole."'");  
                        $db->delete_query('coupons_to_customers' , " coupons_id = '".$pole."'");  
                    }
                }
                break;
                ///
            case 2:
                //
                // kasowanie wszystkich kuponow  ------------ ** -------------  
                $db->delete_query('coupons','');     
                $db->delete_query('coupons_to_orders','');      
                $db->delete_query('coupons_to_customers','');   
                break;
                /// 
            case 3:
                //
                $zapytanie = "select * from coupons where coupons_status = '0'";
                $sql = $db->open_query($zapytanie);                

                while ($info = $sql->fetch_assoc()) {
                    // kasowanie wszystkich nieaktywnych kuponow wybranych ------------ ** -------------  
                    $db->delete_query('coupons' , " coupons_id = '".$info['coupons_id']."'");     
                    $db->delete_query('coupons_to_orders' , " coupons_id = '".$info['coupons_id']."'");  
                    $db->delete_query('coupons_to_customers' , " coupons_id = '".$info['coupons_id']."'");
                }
                
                $db->close_query($sql);
                unset($info);                
                break;
                /// 
            case 4:
                //
                // kasowanie wszystkich kuponow z danym prefixem ------------ ** -------------  
                if (isset($_POST['profix']) && !empty($_POST['profix'])) {
                    //
                    $Prefix = $filtr->process($_POST['profix']);
                    $dlugosc = strlen((string)$Prefix);
                    //
                    $zapytanie = "select * from coupons where SUBSTR(coupons_name,1,".$dlugosc.") = '" .$Prefix. "'";
                    $sql = $db->open_query($zapytanie);                      
                    //
                    while ($info = $sql->fetch_assoc()) {
                        $db->delete_query('coupons' , " coupons_id = '".$info['coupons_id']."'");     
                        $db->delete_query('coupons_to_orders' , " coupons_id = '".$info['coupons_id']."'");
                        $db->delete_query('coupons_to_customers' , " coupons_id = '".$info['coupons_id']."'");
                    }
                    //
                
                    $db->close_query($sql);
                    unset($info);

                }
                break;
                ///                 
        }          

    }
    
    Funkcje::PrzekierowanieURL('kupony.php');
    
}
?>