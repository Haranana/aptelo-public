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

        $zapytanie = "select products_status from products where products_id = ".(int)$_GET['id_poz'];
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) { 

            $info = $sql->fetch_assoc();
            
            // jezeli jest wlaczony - wylaczy go
            if ($info['products_status'] == '1') {
                $pola = array(array('products_status','0'));
                $db->update_query('products' , $pola, " products_id = '".(int)$_GET['id_poz']."'");
                unset($pola);                       
            }
            
            // jezeli jest wylaczony - wlaczy go
            if ($info['products_status'] == '0') {
                $pola = array(array('products_status','1'));
                $db->update_query('products' , $pola, " products_id = '".(int)$_GET['id_poz']."'");
                unset($pola);                         
            }

            unset($pola,$info);

        }
        
        $db->close_query($sql);    
        
        Funkcje::PrzekierowanieURL('produkty.php' . Funkcje::Zwroc_Get(array('status')));
    
    } else {
    
        Funkcje::PrzekierowanieURL('produkty.php');
    
    }

}
?>