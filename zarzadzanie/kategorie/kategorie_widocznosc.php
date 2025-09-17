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

        $zapytanie = "select categories_view from categories where categories_id = ".(int)$_GET['id_poz'];
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) { 

            $info = $sql->fetch_assoc();
            
            // jezeli jest wlaczona - wylaczy ja
            if ($info['categories_view'] == '1') {
            
                $pola = array(array('categories_view','0'));
                $db->update_query('categories' , $pola, " categories_id = '".(int)$_GET['id_poz']."'");
                unset($pola);

            }
            
            // jezeli jest wylaczona - wlaczy ja
            if ($info['categories_view'] == '0') {
                
                $pola = array(array('categories_view','1'));
                $db->update_query('categories' , $pola, " categories_id = '".(int)$_GET['id_poz']."'");
                unset($pola);            

            }

        }
        
        $db->close_query($sql);    
        
        Funkcje::PrzekierowanieURL('kategorie.php?id_poz='.(int)$id_poz);
    
    } else {
    
        Funkcje::PrzekierowanieURL('kategorie.php');
    
    }
}
?>