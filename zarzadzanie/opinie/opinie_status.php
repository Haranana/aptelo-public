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
    
    if (!isset($_GET['id'])) {
        $_GET['id'] = 0;
    }    

    if ((int)$id_poz > 0) {

        $zapytanie = "SELECT * FROM reviews_shop WHERE reviews_shop_id = ".(int)$_GET['id_poz'];
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) { 

            $info = $sql->fetch_assoc();
            
            // jezeli jest wlaczona - wylaczy ja
            if ($info['approved'] == '1') {
                $pola = array(array('approved','0'));
                $db->update_query('reviews_shop' , $pola, " reviews_shop_id = '".(int)$_GET['id_poz']."'");
                unset($pola);                           
            }
            
            // jezeli jest wylaczona - wlaczy ja
            if ($info['approved'] == '0') {
                $pola = array(array('approved','1'));
                $db->update_query('reviews_shop' , $pola, " reviews_shop_id = '".(int)$_GET['id_poz']."'");
                unset($pola);     
            }

            unset($info);

        }
        
        $db->close_query($sql);    
        
        Funkcje::PrzekierowanieURL('opinie.php?id_poz='.(int)$id_poz);
    
    } else {
    
        Funkcje::PrzekierowanieURL('opinie.php');
    
    }
}
?>