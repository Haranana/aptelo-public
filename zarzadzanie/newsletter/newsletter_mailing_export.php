<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

        $zapytanie = "select mailing_email_address from mailing";
        $sql = $db->open_query($zapytanie);
        
        //
        if ((int)$db->ile_rekordow($sql) > 0) {
        
            $ciag_do_zapisu = '';
            
            while ($info = $sql->fetch_assoc()) {
            
                $ciag_do_zapisu .= $info['mailing_email_address'] . "\n";

            }
            
            //
            $db->close_query($sql);
            unset($info);      

            header("Content-Type: application/force-download\n");
            header("Cache-Control: cache, must-revalidate");   
            header("Pragma: public");
            header("Content-Disposition: attachment; filename=eksport_mailing_email_" . date("d-m-Y") . ".txt");
            print $ciag_do_zapisu;
            exit;   
            
        }
        
        $db->close_query($sql);        

}