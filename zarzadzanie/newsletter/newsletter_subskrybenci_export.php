<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

        if ( !isset($_GET['filtry']) ) {
          
            if ( isset($_GET['zapisani']) ) {
                $zapytanie = "select s.subscribers_email_address from subscribers s where customers_newsletter = '1'";
              } else {
                $zapytanie = "select s.subscribers_email_address from subscribers s";
            }
            
        } else {
          
            $warunki_szukania = " and s.subscribers_email_address != ''";
            //

            if ( isset($_SESSION['filtry']['newsletter_subskrybenci.php']) && count($_SESSION['filtry']['newsletter_subskrybenci.php']) > 0 ) {
                 //
                 foreach ( $_SESSION['filtry']['newsletter_subskrybenci.php'] as $klucz => $wartosc ) {
                    //
                    // sprawdza czy nie ma get - wtedy ma to pierwszenstwo
                    if ( !isset($_GET[$klucz]) ) {
                        //
                        $_GET[$klucz] = $wartosc;
                        //
                    }
                    //
                 }
                 //
            }    
            
            // zamienia ' na \' do zapytan sql
            foreach ( $_GET as $klucz => $wartosc ) {
                //
                // sprawdza czy jest wylaczone magic_quotes_gpc
                $_GET[$klucz] = str_replace("'", "\'", (string)$wartosc);
                //
            }

            include('newsletter/newsletter_filtry.php');  
            
            if (( isset($_GET['grupa']) && (int)$_GET['grupa'] > 0 ) || ( isset($_GET['grupa_newslettera']) && (string)$_GET['grupa_newslettera'] != '' )) {
                 $zapytanie = "select s.subscribers_email_address from subscribers s, customers c " . $warunki_szukania;
            } else {
                 $zapytanie = "select s.subscribers_email_address from subscribers s " . $warunki_szukania;
            }            
            
        }
        
        $sql = $db->open_query($zapytanie);
        
        //
        if ((int)$db->ile_rekordow($sql) > 0) {
        
            $ciag_do_zapisu = '';
            
            while ($info = $sql->fetch_assoc()) {
            
                $ciag_do_zapisu .= $info['subscribers_email_address'] . "\n";

            }
            
            //
            $db->close_query($sql);
            unset($info);      

            header("Content-Type: application/force-download\n");
            header("Cache-Control: cache, must-revalidate");   
            header("Pragma: public");
            header("Content-Disposition: attachment; filename=eksport_newsletter_email_" . date("d-m-Y") . ".txt");
            print $ciag_do_zapisu;
            exit;   
            
        }
        
        $db->close_query($sql);        

}