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

        $zapytanie = "select status from banners where banners_id = ".(int)$_GET['id_poz'];
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) { 

            $info = $sql->fetch_assoc();
            
            if ($info['status'] == '1') {
                $pola = array(array('status','0'));
                $db->update_query('banners' , $pola, " banners_id = '".(int)$_GET['id_poz']."'");
                unset($pola);            
            }
            
            if ($info['status'] == '0') {
                $pola = array(array('status','1'));
                $db->update_query('banners' , $pola, " banners_id = '".(int)$_GET['id_poz']."'");
                unset($pola);                            
            }

            unset($pola,$info);

        }
        
        $db->close_query($sql);    
        
        Funkcje::PrzekierowanieURL('bannery_zarzadzanie.php?id_poz='.(int)$id_poz);
    
    } else {
    
        Funkcje::PrzekierowanieURL('bannery_zarzadzanie.php');
    
    }
}
?>