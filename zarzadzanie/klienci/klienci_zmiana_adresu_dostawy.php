<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_GET['id_poz'])) {
        $id_poz = (int)$_GET['id_poz'];
       } else {
        $id_poz = 0;
    }
    if (!isset($_GET["id"])) {
        $_GET["id"] = 0;
    }    
    
    $pola = array(array('customers_default_shipping_address_id',(int)$_GET["id"]));
    $db->update_query('customers', $pola, "customers_id = '" . $id_poz . "'");
    unset($pola);

    Funkcje::PrzekierowanieURL('klienci_edytuj.php?id_poz=' . (int)$id_poz.Funkcje::Zwroc_Wybrane_Get(array('zakladka'),true));
    
}
?>