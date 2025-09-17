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

        $zapytanie = "select orders_id, invoices_payment_status from invoices where invoices_id = ".(int)$_GET['id_poz'];
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) { 

            $info = $sql->fetch_assoc();
            
            if ($info['invoices_payment_status'] == '1') {
                $pola = array(array('invoices_payment_status','0'));
                $db->update_query('invoices' , $pola, " invoices_id = '".(int)$_GET['id_poz']."'");
                unset($pola);                           
            }
            
            if ($info['invoices_payment_status'] == '0') {
                $pola = array(array('invoices_payment_status','1'));
                $db->update_query('invoices' , $pola, " invoices_id = '".(int)$_GET['id_poz']."'");
                unset($pola);                 
            }
            
            if ( INTEGRACJA_FAKTUROWNIA_WLACZONY == 'tak' ) {
            
                // fakturownia pl
                //
                $fakturownia = new Fakturownia($info['orders_id']);
                //
                if ( $info['invoices_payment_status'] == '0' ) {
                     //
                     $fakturownia->ZmienStatusFaktury('oplacona', false);
                     //
                }
                if ( $info['invoices_payment_status'] == '1' ) {
                     //
                     $fakturownia->ZmienStatusFaktury('wystawiona', false);
                     //
                }           
                //
                unset($fakturownia);
                // 

            }                

            unset($info);

        }
        
        $db->close_query($sql);    
        
        Funkcje::PrzekierowanieURL('zamowienia_faktury.php?id_poz='.(int)$id_poz);
    
    } else {
    
        Funkcje::PrzekierowanieURL('zamowienia_faktury.php');
    
    }
}
?>