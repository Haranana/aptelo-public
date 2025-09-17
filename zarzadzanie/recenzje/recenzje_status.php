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

        $zapytanie = "SELECT * FROM reviews WHERE reviews_id = ".(int)$_GET['id_poz'];
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) { 

            $info = $sql->fetch_assoc();
            
            // jezeli jest wlaczona - wylaczy ja
            if ($info['approved'] == '1') {
                $pola = array(array('approved','0'));
                $db->update_query('reviews' , $pola, " reviews_id = '".(int)$_GET['id_poz']."'");
                unset($pola);                           
            }
            
            // jezeli jest wylaczona - wlaczy ja
            if ($info['approved'] == '0') {
                $pola = array(array('approved','1'));
                $db->update_query('reviews' , $pola, " reviews_id = '".(int)$_GET['id_poz']."'");
                unset($pola);     
                //
                // jezeli jest system punktow zatwierdzi punkty
                //
                if ( SYSTEM_PUNKTOW_STATUS == 'tak' && (int)SYSTEM_PUNKTOW_PUNKTY_RECENZJE > 0 ) {
                    //
                    // sprawdzi czy juz nie bylo operacji na punktach za ta recenzje - czy status jest oczekujacy
                    $zapytanie_spr = "select * from customers_points where customers_id = '" . (int)$_GET['id'] . "' and reviews_id = '" . (int)$_GET['id_poz'] . "' and points_status = '1'";
                    $sql_spr = $db->open_query($zapytanie_spr);               

                    if ((int)$db->ile_rekordow($sql_spr) > 0) {
                    
                        $pola = array(array('points_status','2'),
                                      array('date_confirm','now()'));
                        $db->update_query('customers_points', $pola, "customers_id = '" . (int)$_GET['id'] . "' and reviews_id = '" . (int)$_GET['id_poz'] . "'");
                        unset($pola);                 
                        //
                        // aktualizuje status punktow klienta
                        //
                        // ile klient ma punktow
                        $zapytanie = "select distinct customers_shopping_points from customers where customers_id = '" . (int)$_GET['id'] . "'";
                        $sqlc = $db->open_query($zapytanie);       
                        $info = $sqlc->fetch_assoc();
                        $IleMaPkt = $info['customers_shopping_points'];
                        $db->close_query($sqlc);
                        unset($info, $zapytanie);            
                        //
                        $LiczbaPkt = (int)$IleMaPkt + (int)SYSTEM_PUNKTOW_PUNKTY_RECENZJE;
                        if ($LiczbaPkt < 0) {
                            $LiczbaPkt = 0;
                        }
                        //
                        $pola = array(array('customers_shopping_points', $LiczbaPkt));
                        //	
                        $db->update_query('customers', $pola, 'customers_id = ' . (int)$_GET['id']);
                        unset($pola, $LiczbaPkt);            
                        //

                    }
                    
                    $db->close_query($sql_spr);
                    unset($zapytanie_spr);
                    //
                }
                //
            }

            unset($info);

        }
        
        $db->close_query($sql);    
        
        Funkcje::PrzekierowanieURL('recenzje.php?id_poz='.(int)$id_poz);
    
    } else {
    
        Funkcje::PrzekierowanieURL('recenzje.php');
    
    }
}
?>