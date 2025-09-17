<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
  
    if (isset($_GET['przesylka']) && isset($_GET["id_poz"])) {
        //			
        $db->delete_query('orders_shipping' , " orders_shipping_id = '" . $filtr->process($_GET['przesylka']) . "'");  
        //
        Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz=' . (int)$_GET['id_poz'] . '&zakladka=1');
    }

}

?>