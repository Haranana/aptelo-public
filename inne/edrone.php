<?php
chdir('../');   

$TablicaPost = array('email', 'subscriber_status', 'event_date', 'event_id', 'app_id', 'signature');
$Dostep = true;

for ( $x = 0; $x < count($TablicaPost); $x++ ) {
     //
     if ( !isset($_POST[$TablicaPost[$x]]) ) {
          //
          $Dostep = false;
          //
     }
     //
}

if (isset($_POST['email']) && !empty($_POST['email']) && $Dostep == true) {
    
    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');  
    
    if ( INTEGRACJA_EDRONE_WLACZONY == 'tak' ) {
  
         if ( $_POST['app_id'] == INTEGRACJA_EDRONE_API ) {
         
              // sprawdza czy jest adres w bazie
              $zapytanie = "SELECT subscribers_email_address, customers_id FROM subscribers WHERE subscribers_email_address = '" . $filtr->process($_POST['email']) . "'";
              $sql = $GLOBALS['db']->open_query($zapytanie); 
                            
              if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
              
                  $info = $sql->fetch_assoc();
              
                  // wypisanie z newslettera
                  if ( (int)$_POST['subscriber_status'] == 0 ) {
                    
                       $pola = array(array('customers_newsletter','0'), 
                                     array('customers_newsletter_group',''));
                       $GLOBALS['db']->update_query('subscribers' , $pola, " subscribers_email_address = '" . $filtr->process($_POST['email']) . "'");	
                       unset($pola);
                       
                       $pola = array(array('customers_newsletter','0'), 
                                     array('customers_newsletter_group',''));
                       $GLOBALS['db']->update_query('customers' , $pola, " customers_id = '" . (int)$info['customers_id'] . "'");	
                       unset($pola);      

                  }

                  // zapisanie do newslettera
                  if ( (int)$_POST['subscriber_status'] == 1 ) {
                  
                       $pola = array(array('customers_newsletter','1'),
                                     array('date_added','now()'));
                       $GLOBALS['db']->update_query('subscribers' , $pola, " subscribers_email_address = '" . $filtr->process($_POST['email']) . "'");	
                       unset($pola);
                       
                       $pola = array(array('customers_newsletter','1'));
                       $GLOBALS['db']->update_query('customers' , $pola, " customers_id = '" . (int)$info['customers_id'] . "'");	
                       unset($pola);                    
                       
                  }
                    
              }
              
              $GLOBALS['db']->close_query($sql);
              unset($zapytanie);      

         }              
         
    }

}
?>