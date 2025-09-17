<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['id']) && count($_POST['id']) > 0) {
    
        foreach ($_POST['id'] as $pole) {
        
            // zmiana sortowania ------------ ** -------------
            
            if (isset($_POST['sort_' . $pole]) && (int)$_POST['sort_' . $pole] > 0) {
            
                $sort = (int)$_POST['sort_' . $pole];
                $sort = (($sort < 0) ? $sort * -1 : $sort);
                $pola = array(array('products_extra_fields_order',$sort));
                $sql = $db->update_query('products_extra_fields' , $pola, " products_extra_fields_id = '".$pole."'");

            }

            unset($pola);

        }

    }
    
    Funkcje::PrzekierowanieURL('dodatkowe_pola.php');
    
}
?>