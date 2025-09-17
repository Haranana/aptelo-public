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

        $zapytanie = "select products_extra_fields_allegro from products_extra_fields where products_extra_fields_id = ".(int)$_GET['id_poz'];
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) { 

            $info = $sql->fetch_assoc();
            
            // jezeli jest widoczne
            if ($info['products_extra_fields_allegro'] == '1') {
                $pola = array(array('products_extra_fields_allegro','0'));
                $db->update_query('products_extra_fields' , $pola, " products_extra_fields_id = '".(int)$_GET['id_poz']."'");
                unset($pola);                           
            }
            
            // jezeli nie jest widoczne
            if ($info['products_extra_fields_allegro'] == '0') {
                $pola = array(array('products_extra_fields_allegro','1'));
                $db->update_query('products_extra_fields' , $pola, " products_extra_fields_id = '".(int)$_GET['id_poz']."'");
                unset($pola);                 
            }

            unset($info);

        }
        
        $db->close_query($sql);    
        
        Funkcje::PrzekierowanieURL('dodatkowe_pola.php?id_poz='.(int)$id_poz);
    
    } else {
    
        Funkcje::PrzekierowanieURL('dodatkowe_pola.php');
    
    }
}
?>