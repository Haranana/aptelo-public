<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja_dolna']) && $_POST['akcja_dolna'] != '0') {
    
        if (isset($_POST['opcja'])) {
            //
            if (count($_POST['opcja']) > 0) {
        
                foreach ($_POST['opcja'] as $pole) {
        
                    switch ((int)$_POST['akcja_dolna']) {
                        case 1:
                            // kasowanie ------------ ** -------------
                            // sprawdza czy trzeba tez wylaczyc klientowi newslettera
                            $sql = $db->open_query("select customers_id, subscribers_email_address from subscribers where subscribers_id = '".$pole."'");
                            //                            
                            $info = $sql->fetch_assoc();   
                            
                            if ((int)$info['customers_id'] > 0) {
                                $pola = array(array('customers_newsletter','0'),
                                              array('customers_newsletter_group',''));  
                                $db->update_query('customers' , $pola, " customers_id = '".(int)$info['customers_id']."'");	
                                unset($pola);  
                                
                                $pola = array(array('customers_newsletter','0'));
                                $db->update_query('subscribers' , $pola, " subscribers_id = '".$pole."'");
                                unset($pola);                                  
                            }

                            $db->close_query($sql);
                            //
                            // usuwa tylko jezeli klient nie ma konta w sklepie
                            if ((int)$info['customers_id'] == 0) {
                                $db->delete_query('subscribers' , " subscribers_id = '".$pole."'");                               
                            }
                            //
                            break;    
                        case 2:
                            // wlaczanie statusu ------------ ** -------------
                            $pola = array(array('customers_newsletter','1'));
                            $sql = $db->update_query('subscribers' , $pola, " subscribers_id = '".$pole."'");
                            unset($pola);        

                            // sprawdza czy jest klient z bazy czy spoza
                            $zapytanie = "select customers_id, customers_newsletter from subscribers where subscribers_id = ".$pole;
                            $sql = $db->open_query($zapytanie);
                            
                            $info = $sql->fetch_assoc();
                            
                            // jezeli jest to klient wylaczy tez w customers
                            if ((int)$info['customers_id'] > 0) {
                                $pola = array(array('customers_newsletter','1'));
                                $db->update_query('customers' , $pola, " customers_id = '".(int)$info['customers_id']."'");
                                unset($pola);            
                            }         

                            unset($info);
                            $db->close_query($sql);                              
                            
                            break; 
                        case 3:
                            // wylaczanie statusu ------------ ** -------------
                            $pola = array(array('customers_newsletter','0'));
                            $sql = $db->update_query('subscribers' , $pola, " subscribers_id = '".$pole."'");
                            unset($pola);  

                            // sprawdza czy jest klient z bazy czy spoza
                            $zapytanie = "select customers_id, customers_newsletter from subscribers where subscribers_id = ".$pole;
                            $sql = $db->open_query($zapytanie);
                            
                            $info = $sql->fetch_assoc();
                            
                            // jezeli jest to klient wylaczy tez w customers
                            if ((int)$info['customers_id'] > 0) {
                                $pola = array(array('customers_newsletter','0'),
                                              array('customers_newsletter_group',''));
                                $db->update_query('customers' , $pola, " customers_id = '".(int)$info['customers_id']."'");
                                unset($pola);            
                            }         

                            unset($info);
                            $db->close_query($sql);    
                            
                            break;                                 
                    }          

                }
            
            }
            //
        }
            
    }
    
    Funkcje::PrzekierowanieURL('newsletter_subskrybenci.php');
    
}
?>