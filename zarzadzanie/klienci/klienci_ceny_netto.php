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

        $zapytanie = "select vat_netto from customers where customers_id = ".(int)$_GET['id_poz'] . " and vat_netto_forced = 0";
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) { 

            $info = $sql->fetch_assoc();
            
            if ($info['vat_netto'] == '1') {
                $pola = array(array('vat_netto','0'));
                $db->update_query('customers' , $pola, " customers_id = '".(int)$_GET['id_poz']."'");
                unset($pola);                           
            }
            
            if ($info['vat_netto'] == '0') {
                $pola = array(array('vat_netto','1'));
                $db->update_query('customers' , $pola, " customers_id = '".(int)$_GET['id_poz']."'");
                unset($pola);                 
            }

            unset($info);

        }
        
        $db->close_query($sql);    
        
        Funkcje::PrzekierowanieURL('klienci.php?id_poz='.(int)$id_poz);
    
    } else {
    
        Funkcje::PrzekierowanieURL('klienci.php');
    
    }
}
?>