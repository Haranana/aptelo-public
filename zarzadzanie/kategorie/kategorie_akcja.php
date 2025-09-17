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
                $pola = array(array('sort_order',$sort));
                $sql = $db->update_query('categories' , $pola, " categories_id = '".$pole."'");
                unset($sort);
                
            }

            unset($pola);

        }
        
        Funkcje::PrzekierowanieURL('kategorie.php');

    }
}
?>