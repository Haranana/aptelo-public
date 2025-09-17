<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['opcja'])) {
        //
        if (count($_POST['opcja']) > 0) {
    
            foreach ($_POST['opcja'] as $pole) {
    
                switch ((int)$_POST['akcja_dolna']) {
                    case 1:
                        // kasowanie klientow ------------ ** -------------
                        
                        //
                        // usuniecie klienta z salesmanago
                        if ( INTEGRACJA_SALESMANAGO_WLACZONY == 'tak' ) {
                             //
                             $zapytanie = "select subscribers_email_address from subscribers where customers_id = '" . $pole . "'";
                             $sql = $db->open_query($zapytanie);     
                             
                             if ((int)$db->ile_rekordow($sql) > 0) { 

                                 $info = $sql->fetch_assoc();    
                                 
                                 $salesmanago = new SalesManago();
                                 // sprawdzi czy jest klient                             
                                 $SmKlient = $salesmanago->CzyJestKlient( array('email' => $info['subscribers_email_address']), 'tak' );
                                 //
                                 if ( $SmKlient != '' ) {
                                      //
                                      $dane = array('email' => $info['subscribers_email_address']);
                                      //
                                      $sm = $salesmanago->UsunKlienta( $dane );
                                      //
                                      unset($dane);
                                      //
                                 }
                                 //
                                 
                             }
                             
                             $db->close_query($sql);
                             unset($zapytanie);               
                        }   
                                        
                        // usuniecie z newslettera w systemie Freshmail
                        if ( INTEGRACJA_FRESHMAIL_WLACZONY == 'tak' ) {
                             //
                             $zapytanie = "select subscribers_email_address from subscribers where customers_id = '" . $pole . "'";
                             $sql = $db->open_query($zapytanie);     
                             
                             if ((int)$db->ile_rekordow($sql) > 0) { 

                                 $info = $sql->fetch_assoc();              
                                 //
                                 $freshMail = new FreshMail();
                                 $freshMail->UsunSubskrybenta($info['subscribers_email_address']);
                                 unset($freshMail);
                                 //
                                 unset($info);
                                 //
                             }
                             
                             $db->close_query($sql);
                             unset($zapytanie);             
                             
                        }   
                        
                        // usuniecie z newslettera w systemie MailerLite
                        if ( INTEGRACJA_MAILERLITE_WLACZONY == 'tak' ) {
                             //
                             $zapytanie = "select subscribers_email_address from subscribers where customers_id = '" . $pole . "'";
                             $sql = $db->open_query($zapytanie);     
                             
                             if ((int)$db->ile_rekordow($sql) > 0) { 

                                 $info = $sql->fetch_assoc();              
                                 //
                                 $mailerLite = new MailerLite();
                                 $mailerLite->UsunSubskrybenta($info['subscribers_email_address']);
                                 unset($mailerLite);
                                 //
                                 unset($info);
                                 //
                             }
                             
                             $db->close_query($sql);
                             unset($zapytanie);             
                             
                        }   
                        
                        // usuniecie z newslettera w systemie Ecomail
                        if ( INTEGRACJA_ECOMAIL_WLACZONY == 'tak' ) {
                             //
                             $zapytanie = "select subscribers_email_address from subscribers where customers_id = '" . $pole . "'";
                             $sql = $db->open_query($zapytanie);     
                             
                             if ((int)$db->ile_rekordow($sql) > 0) { 

                                 $info = $sql->fetch_assoc();              
                                 //
                                 $ecomail = new Ecomail();
                                 $ecomail->UsunSubskrybenta($info['subscribers_email_address']);
                                 unset($ecomail);
                                 //
                                 unset($info);
                                 //
                             }
                             
                             $db->close_query($sql);
                             unset($zapytanie);             
                             
                        }   
                        
                        // usuniecie z newslettera w systemie Mailjet
                        if ( INTEGRACJA_MAILJET_WLACZONY == 'tak' ) {
                             //
                             $zapytanie = "select subscribers_email_address from subscribers where customers_id = '" . $pole . "'";
                             $sql = $db->open_query($zapytanie);     
                             
                             if ((int)$db->ile_rekordow($sql) > 0) { 

                                 $info = $sql->fetch_assoc();              
                                 //
                                 $mailjet = new Mailjet();
                                 $mailjet->UsunSubskrybenta($info['subscribers_email_address']);
                                 unset($mailjet);
                                 //
                                 unset($info);
                                 //
                             }
                             
                             $db->close_query($sql);
                             unset($zapytanie);             
                             
                        }   

                        // usuniecie z newslettera w systemie Getall
                        if ( INTEGRACJA_GETALL_WLACZONY == 'tak' ) {
                             //
                             $zapytanie = "select subscribers_email_address from subscribers where customers_id = '" . $pole . "'";
                             $sql = $db->open_query($zapytanie);     
                             
                             if ((int)$db->ile_rekordow($sql) > 0) { 

                                 $info = $sql->fetch_assoc();              
                                 //
                                 $getall = new GetAll(INTEGRACJA_GETALL_APIKEY); 
                                 $getall->UsunSubskrybenta($info['subscribers_email_address']);
                                 unset($getall);
                                 //
                                 unset($info);
                                 //
                             }
                             
                             $db->close_query($sql);
                             unset($zapytanie);             
                             
                        }             
                        //
                        $db->delete_query('customers' , " customers_id = '".$pole."'");  
                        $db->delete_query('customers_info' , " customers_info_id = '".$pole."'");  
                        $db->delete_query('address_book' , " customers_id = '".$pole."'"); 
                        $db->delete_query('subscribers' , " customers_id = '".$pole."'"); 
                        $db->delete_query('discount_manufacturers' , " discount_customers_id = '".$pole."'");
                        $db->delete_query('discount_categories' , " discount_customers_id = '".$pole."'");       
                        $db->delete_query('discount_categories_manufacturers' , " discount_customers_id = '".$pole."'");       
                        $db->delete_query('discount_products' , " discount_customers_id = '".$pole."'");         
                        $db->delete_query('customers_points' , " customers_id = '".$pole."'");         
                        $db->delete_query('customers_to_extra_fields' , " customers_id = '".$pole."'");     
                        $db->delete_query('blacklist' , " blacklist_customers_id = '".$pole."'"); 
                        //

                        $zapytanie = "select basket_id from basket_save where customers_id = '" . $pole . "'";
                        $sql = $db->open_query($zapytanie);                                
                        while ( $info = $sql->fetch_assoc() ) {
                                $db->delete_query('basket_save_products' , " basket_id = '" . $info['basket_id'] . "'");
                        }                        
                        $db->close_query($sql);
                        unset($zapytanie, $info);        
                        
                        // usuwa zapisane koszyki klienta
                        $db->delete_query('basket_save' , " customers_id = '".$pole."'");
                                                
                        //                                                   
                        break;                          
                }          

            }
        
        }
        //
    }
            
    Funkcje::PrzekierowanieURL('klienci.php');
    
}
?>