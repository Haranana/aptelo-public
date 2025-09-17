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

        $zapytanie = "SELECT paid_info FROM orders WHERE orders_id = ".(int)$_GET['id_poz'];
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) { 

            $info = $sql->fetch_assoc();
            
            if ((int)$info['paid_info'] == '1') {
                $pola = array(array('paid_info','0'));
                $db->update_query('orders' , $pola, " orders_id = '".(int)$_GET['id_poz']."'");
                unset($pola);                           
            }
            
            if ((int)$info['paid_info'] == '0') {
                $pola = array(array('paid_info','1'));
                $db->update_query('orders' , $pola, " orders_id = '".(int)$_GET['id_poz']."'");
                unset($pola);                           
            }            

            unset($info);

        }
        
        $db->close_query($sql);    
        
        if ( !isset($_GET['zakladka']) ) {
            Funkcje::PrzekierowanieURL('zamowienia.php?id_poz='.(int)$id_poz);
        } else {
            Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$id_poz.'&zakladka='.(int)$_GET['zakladka']);
        }
    
    } else {
    
        Funkcje::PrzekierowanieURL('zamowienia.php');
    
    }
}
?>