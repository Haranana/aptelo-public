<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja_dolna']) && (int)$_POST['akcja_dolna'] > 0) {
    
        if (isset($_POST['opcja'])) {
            //
            if (count($_POST['opcja']) > 0) {
        
                foreach ($_POST['opcja'] as $pole) {
        
                    switch ((int)$_POST['akcja_dolna']) {
                        case 1:
                            // kasowanie pozycji ------------ ** -------------
                            $db->delete_query('products_notifications' , "products_notifications_id = '".$pole."'");                               
                            break;                          
                    }          

                }
            
            }
            //
        }
            
    }
    
    Funkcje::PrzekierowanieURL('klienci_powiadomienia.php');
    
}
?>