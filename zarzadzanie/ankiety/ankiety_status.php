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

        $zapytanie = "select poll_status from poll where id_poll = ".(int)$_GET['id_poz'];
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) { 

            $info = $sql->fetch_assoc();
            
            // jezeli jest wlaczona - wylaczy ja
            if ($info['poll_status'] == '1') {
                $pola = array(array('poll_status','0'));
                $db->update_query('poll' , $pola, " id_poll = '".(int)$_GET['id_poz']."'");                
            }
            
            // jezeli jest wylaczona - wlaczy ja
            if ($info['poll_status'] == '0') {
                $pola = array(array('poll_status','1'));
                $db->update_query('poll' , $pola, " id_poll = '".(int)$_GET['id_poz']."'");                 
            }

            unset($pola,$info);

        }
        
        $db->close_query($sql);    
        
        Funkcje::PrzekierowanieURL('ankiety.php?id_poz='.(int)$id_poz);
    
    } else {
    
        Funkcje::PrzekierowanieURL('ankiety.php');
    
    }
}
?>