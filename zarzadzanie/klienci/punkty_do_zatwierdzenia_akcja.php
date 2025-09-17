<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja_dolna'])) {

        if (isset($_POST['opcja'])) {
            //
            if (count($_POST['opcja']) > 0) {
        
                foreach ($_POST['opcja'] as $pole) {
        
                    switch ((int)$_POST['akcja_dolna']) {
                        case 1:
                            // usuniecie
                            $db->delete_query('customers_points' , " unique_id = '".$pole."'");                        
                            break;
                        case 2:
                        
                            //
                            $zapytaniePkt = "select distinct * from customers_points where unique_id = '".$pole."'";
                            $sqlPkt = $db->open_query($zapytaniePkt);       
                            $infoPkt = $sqlPkt->fetch_assoc();                                
                            //                        
                        
                            // czy ma dodac punkty do klienta
                            if (isset($_POST['dodajPkt']) && $_POST['dodajPkt'] == 'tak') {
                                //
                                // ile klient ma punktow
                                $zapytanie = "select distinct customers_shopping_points from customers where customers_id = '".$infoPkt['customers_id']."'";
                                $sqlc = $db->open_query($zapytanie);       
                                $info = $sqlc->fetch_assoc();
                                $IleMaPkt = $info['customers_shopping_points'];
                                $db->close_query($sqlc);
                                unset($info, $zapytanie);            
                                //
                                $LiczbaPkt = (int)$IleMaPkt + $infoPkt['points'];
                                if ($LiczbaPkt < 0) {
                                    $LiczbaPkt = 0;
                                }
                                //
                                $pola = array(array('customers_shopping_points', $LiczbaPkt));
                                //	
                                $sql = $db->update_query('customers', $pola, 'customers_id = ' . $infoPkt['customers_id']);
                                unset($pola, $LiczbaPkt);            
                                //
                            }     
                            
                            // jezeli ma wyslac do klienta maila
                            if (isset($_POST['mail']) && $_POST['mail'] == 'tak') {  
                            
                                $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".(int)$_POST["jezyk"]."' WHERE t.email_var_id = 'EMAIL_ZMIANA_STATUSU_PUNKTOW'";
                                $sql = $db->open_query($zapytanie_tresc);
                                $tresc = $sql->fetch_assoc();        
                            
                                $zapytanie_klient = "SELECT * FROM customers WHERE customers_id = '".$infoPkt['customers_id']."'";
                                $sql_klient = $db->open_query($zapytanie_klient);
                                $info_klient = $sql_klient->fetch_assoc();       

                                $zapytanie_punkty = "SELECT * FROM customers_points WHERE unique_id = '".$pole."'";
                                $sql_punkty = $db->open_query($zapytanie_punkty);
                                $info_punkty = $sql_punkty->fetch_assoc();             

                                $email = new Mailing;

                                if ( $tresc['email_file'] != '' ) {
                                    $tablicaZalacznikow = explode(';', (string)$tresc['email_file']);
                                } else {
                                    $tablicaZalacznikow = array();
                                }

                                $nadawca_email   = Funkcje::parsujZmienne($tresc['sender_email']);
                                $nadawca_nazwa   = Funkcje::parsujZmienne($tresc['sender_name']);
                                $cc              = Funkcje::parsujZmienne($tresc['dw']);

                                $adresat_email   = $info_klient['customers_email_address'];
                                $adresat_nazwa   = $info_klient['customers_firstname'] . ' ' . $info_klient['customers_lastname'];

                                $temat           = Funkcje::parsujZmienne($tresc['email_title']);
                                $tekst           = $tresc['description'];
                                
                                // zamiana stalych
                                $tekst           = str_replace('{STATUS_PUNKTOW}', Klienci::pokazNazweStatusuPunktow( (int)$_POST['status'], (int)$_POST["jezyk"] ), (string)$tekst);
                                $tekst           = str_replace('{DATA_PUNKTOW}', date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info_punkty['date_added'])), (string)$tekst);
                                $tekst           = str_replace('{ILOSC_PUNKTOW}', (string)$info_punkty['points'], (string)$tekst);
                                $tekst           = str_replace('{OGOLNA_ILOSC_PUNKTOW}', (string)$info_klient['customers_shopping_points'], (string)$tekst);
                                $tekst           = str_replace('{KOMENTARZ}', '', (string)$tekst);
                                
                                $zalaczniki      = $tablicaZalacznikow;
                                $szablon         = $tresc['template_id'];
                                $jezyk           = (int)$_POST["jezyk"];

                                //$tekst = Funkcje::parsujZmienne($tekst);
                                $tekst = preg_replace('#(<br */?>\s*)+#i', '<br /><br />', (string)$tekst);

                                $email->wyslijEmail($nadawca_email,$nadawca_nazwa,$adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki, false);
                                
                                $db->close_query($sql_klient);
                                $db->close_query($sql_punkty);
                                $db->close_query($sql);
                                unset($zapytanie_punkty, $info_punkty, $zapytanie_klient, $info_klient, $tresc, $zapytanie_tresc);             

                            }                            

                            $db->close_query($sqlPkt);
                            unset($infoPkt, $zapytaniePkt);                                 
                            //                            
                        
                            // zmiana statusu
                            $pola = array(array('points_status',(int)$_POST['status']));
                            
                            // jezeli status anulowane lub zatwierdzone
                            if ( (int)$_POST['status'] == 2 || (int)$_POST['status'] == 3 ) {
                                $pola[] = array('date_confirm','now()');
                              } else {
                                $pola[] = array('date_confirm','0000-00-00 00:00:00');
                            }                            
                            
                            $sql = $db->update_query('customers_points' , $pola, " unique_id = '".$pole."'");
                            unset($pola);                             
                            break;                                 
                    }          

                }
            
            }
            //
        }       

    }
    
    Funkcje::PrzekierowanieURL('punkty_do_zatwierdzenia.php');
    
}
?>