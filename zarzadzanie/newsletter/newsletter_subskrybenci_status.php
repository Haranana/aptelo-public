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

        // sprawdza czy jest klient z bazy czy spoza
        $zapytanie = "select customers_id, customers_newsletter from subscribers where subscribers_id = ".(int)$_GET['id_poz'];
        $sql = $db->open_query($zapytanie);

        $info = $sql->fetch_assoc();
        
        if ($info['customers_newsletter'] == '1') {
            $pola = array(array('customers_newsletter','0'),
                          array('date_added','0000-00-00'));
            $db->update_query('subscribers' , $pola, " subscribers_id = '".(int)$_GET['id_poz']."'");
            unset($pola);                           
            
            // jezeli jest to klient wylaczy tez w customers
            if ((int)$info['customers_id'] > 0) {
                $pola = array(array('customers_newsletter','0'));
                $db->update_query('customers' , $pola, " customers_id = '".(int)$info['customers_id']."'");
                unset($pola);            
            }
        }
        
        if ($info['customers_newsletter'] == '0') {
            $pola = array(array('customers_newsletter','1'),
                          array('date_added','now()'));
            $db->update_query('subscribers' , $pola, " subscribers_id = '".(int)$_GET['id_poz']."'");
            unset($pola);  
            
            // jezeli jest to klient wlaczy tez w customers
            if ((int)$info['customers_id'] > 0) {
                $pola = array(array('customers_newsletter','1'));
                $db->update_query('customers' , $pola, " customers_id = '".(int)$info['customers_id']."'");
                unset($pola);            
            }            
        }

        unset($info);

        $db->close_query($sql);    
        
        Funkcje::PrzekierowanieURL('newsletter_subskrybenci.php?id_poz='.(int)$id_poz);
    
    } else {
    
        Funkcje::PrzekierowanieURL('newsletter_subskrybenci.php');
    
    }
}
?>