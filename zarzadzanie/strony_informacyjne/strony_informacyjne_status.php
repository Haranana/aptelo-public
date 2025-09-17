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

    if ((int)$id_poz > 0) {

        $zapytanie = "select status from pages where pages_id = ".(int)$_GET['id_poz'];
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) { 

            $info = $sql->fetch_assoc();
            
            // jezeli jest wlaczona - wylaczy ja
            if ($info['status'] == '1') {
                $pola = array(array('status','0'));
                $db->update_query('pages' , $pola, " pages_id = '".(int)$_GET['id_poz']."'");                
            }
            
            // jezeli jest wylaczona - wlaczy ja
            if ($info['status'] == '0') {
                $pola = array(array('status','1'));
                $db->update_query('pages' , $pola, " pages_id = '".(int)$_GET['id_poz']."'");                 
            }

            unset($pola,$info);

        }
        
        $db->close_query($sql);    
        
        Funkcje::PrzekierowanieURL('strony_informacyjne.php?id_poz='.(int)$id_poz);
    
    } else {
    
        Funkcje::PrzekierowanieURL('strony_informacyjne.php');
    
    }
}
?>