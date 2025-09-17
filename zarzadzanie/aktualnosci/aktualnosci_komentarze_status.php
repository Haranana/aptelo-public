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

        $zapytanie = "SELECT * FROM newsdesk_comments WHERE newsdesk_comments_id = ".(int)$_GET['id_poz'];
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) { 

            $info = $sql->fetch_assoc();
            
            // jezeli jest wlaczony - wylaczy
            if ($info['status'] == '1') {
                $pola = array(array('status','0'));
                $db->update_query('newsdesk_comments' , $pola, " newsdesk_comments_id = '".(int)$_GET['id_poz']."'");
                unset($pola);                           
            }
            
            // jezeli jest wylaczona - wlaczy ja
            if ($info['status'] == '0') {
                $pola = array(array('status','1'));
                $db->update_query('newsdesk_comments' , $pola, " newsdesk_comments_id = '".(int)$_GET['id_poz']."'");
                unset($pola); 
            }

            unset($info);

        }
        
        $db->close_query($sql);    
        
        Funkcje::PrzekierowanieURL('aktualnosci_komentarze.php?id_poz='.(int)$id_poz.((isset($_GET['art_id']) && (int)$_GET['art_id'] > 0) ? '&art_id='.(int)$_GET['art_id'] : ''));
    
    } else {
    
        Funkcje::PrzekierowanieURL('aktualnosci_komentarze.php');
    
    }
}
?>