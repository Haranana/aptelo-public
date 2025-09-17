<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( !isset($_GET['id_poz']) ) {
         $_GET['id_poz'] = 0;
    }   

    $zapytanie = "select * from coupons where coupons_id = '" . (int)$_GET['id_poz'] . "'";
    $sql = $db->open_query($zapytanie);
    
    $kupon = $sql->fetch_assoc();

    if ((int)$db->ile_rekordow($sql) > 0) {

        // wyszukuje w ilu zamowieniach wystapil kupon
        $zapytanie_w_zamowieniach = "select * from coupons_to_orders where coupons_id = '" . (int)$_GET['id_poz'] . "' order by orders_id";
        $sqls = $db->open_query($zapytanie_w_zamowieniach);
        
        if ((int)$db->ile_rekordow($sqls) > 0) {
        
            $ciag_do_zapisu = '';
            
            $ciag_do_zapisu .= 'Nr zamówienia;Data zamówienia;Wartość zamówienia;Wartość kuponu;Klient;Status zamówienia' ."\n";
        
            while ($info = $sqls->fetch_assoc()) {
            
                $rabat = '';
                $zamowienie = new Zamowienie($info['orders_id']);
                
                if ( Funkcje::multiInArray('ot_discount_coupon', $zamowienie->podsumowanie) ) {
                    foreach ( $zamowienie->podsumowanie as $podsumowanie ) {
                        if ( $podsumowanie['klasa'] == 'ot_discount_coupon' ) {
                            $rabat = $podsumowanie['tekst'];
                        }
                    }
                }

                if ( isset($zamowienie->info) && !empty($zamowienie->info) ) {

                    $ciag_do_zapisu .= $info['orders_id'] . ';';
                    $ciag_do_zapisu .= $zamowienie->info['data_zamowienia'] . ';';
                    $ciag_do_zapisu .= $zamowienie->info['wartosc_zamowienia'] . ';';
                    $ciag_do_zapisu .= $rabat . ';';
                    $ciag_do_zapisu .= ((!empty($zamowienie->klient['firma'])) ? $zamowienie->klient['firma'] . ', ' : '') . 
                                       $zamowienie->klient['nazwa'] . ', '.
                                       $zamowienie->klient['ulica'] . ', '.
                                       $zamowienie->klient['kod_pocztowy'] . ' '. $zamowienie->klient['miasto'] . ';';
                    $ciag_do_zapisu .= strip_tags(Sprzedaz::pokazNazweStatusuZamowienia($zamowienie->info['status_zamowienia'], $_SESSION['domyslny_jezyk']['id']));
                    
                    $ciag_do_zapisu .= "\n";

                }
                
                unset($zamowienie);
                
            }
            
            header("Content-Type: application/force-download\n");
            header("Cache-Control: cache, must-revalidate");   
            header("Pragma: public");
            header("Content-Disposition: attachment; filename=statystyka_kupon_".$kupon['coupons_name']."_" . date("d-m-Y") . ".csv");
            print $ciag_do_zapisu;
            exit;              
            
        }
            
    }

    $db->close_query($sql);
    unset($kupon);

}
?>
