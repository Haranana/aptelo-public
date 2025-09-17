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

        $zapytanie = "SELECT * FROM reviews WHERE reviews_id = ".(int)$id_poz;
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) { 

            $info = $sql->fetch_assoc();
            
            // jezeli jest wlaczona - wylaczy ja
            if ($info['reviews_confirm'] == '1') {
                $pola = array(array('reviews_confirm','0'));
                $db->update_query('reviews' , $pola, " reviews_id = '".(int)$_GET['id_poz']."'");
                unset($pola);                           
            }
            
            // jezeli jest wylaczona - wlaczy ja
            if ($info['reviews_confirm'] == '0') {
                $pola = array(array('reviews_confirm','1'));
                $db->update_query('reviews' , $pola, " reviews_id = '".(int)$_GET['id_poz']."'");
                unset($pola);     
            }

            unset($info);

        }
        
        $db->close_query($sql);    
        
        if ( isset($_GET['zakladka']) && isset($_GET['produkt']) ) {
        
             Funkcje::PrzekierowanieURL('/zarzadzanie/produkty/produkty_edytuj.php?id_poz=' . (int)$_GET['produkt'] . '&zakladka=' . (int)$_GET['zakladka']);
             
        } else {
          
             Funkcje::PrzekierowanieURL('recenzje.php?id_poz='.(int)$id_poz);
             
        }        

    } else {
    
        Funkcje::PrzekierowanieURL('recenzje.php');
    
    }
}
?>