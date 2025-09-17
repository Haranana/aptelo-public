<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( !isset($_GET['id_poz']) ) {
         $_GET['id_poz'] = 0;
    }    
    
    $zapytanie = "select * from orders where orders_id = '" . (int)$_GET['id_poz'] . "'";
    $sql = $db->open_query($zapytanie);
    
    $_SESSION['info'] = 'Zamówienie nie zostało zakwalifikowane jako "podejrzane"';
    
    if ((int)$db->ile_rekordow($sql) > 0) {
    
        if ( Klienci::SprawdzZamowienieCzarnaLista( (int)$_GET['id_poz'] ) != false ) {
          
             $_SESSION['info'] = '<b style="color:#ff0000">Zamówienie zostało zakwalifikowane jako "podejrzane"</b>';
             
             $pola = array(array('orders_black_list',2));
             $db->update_query('orders', $pola, "orders_id = '" . (int)$_GET['id_poz'] . "'");           
             unset($pola);
          
        }

    }
    
    $db->close_query($sql);
    unset($zapytanie, $info); 

    Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz=' . (int)$_GET['id_poz']);    

}
?>