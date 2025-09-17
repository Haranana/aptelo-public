<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_GET['id_poz'])) {
        $poz_id = (int)$_GET['id_poz'];
       } else {
        $poz_id = 0;
    }

    if ((int)$poz_id > 0) {

        if ($filtr->process($_GET['sortowanie']) != '' && (int)$_GET['sortowanie'] != 0) {
            $sort = (int)$_GET['sortowanie'];
            $sort = (($sort < 0) ? $sort * -1 : $sort);
            $pola = array(array('sort_order',$sort));
            $sql = $db->update_query('products' , $pola, " products_id = '".(int)$_GET['id_poz']."'");
        }
        
        Funkcje::PrzekierowanieURL('produkty.php' . Funkcje::Zwroc_Get(array('sortowanie')));
        
    }
    
}
?>