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

        $zapytanie = "SELECT newsdesk_comments_status FROM newsdesk WHERE newsdesk_id = ".(int)$_GET['id_poz'];
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) { 

            $info = $sql->fetch_assoc();
            
            // jezeli jest wlaczony - wylaczy
            if ($info['newsdesk_comments_status'] == '1') {
                $pola = array(array('newsdesk_comments_status','0'));
                $db->update_query('newsdesk' , $pola, " newsdesk_id = '".(int)$_GET['id_poz']."'");
                unset($pola);                           
            }
            
            // jezeli jest wylaczona - wlaczy ja
            if ($info['newsdesk_comments_status'] == '0') {
                $pola = array(array('newsdesk_comments_status','1'));
                $db->update_query('newsdesk' , $pola, " newsdesk_id = '".(int)$_GET['id_poz']."'");
                unset($pola); 
            }

            unset($info);

        }

        $db->close_query($sql);    
        
        Funkcje::PrzekierowanieURL('aktualnosci.php?id_poz='.(int)$id_poz);
    
    } else {
    
        Funkcje::PrzekierowanieURL('aktualnosci_komentarze.php');
    
    }
}
?>