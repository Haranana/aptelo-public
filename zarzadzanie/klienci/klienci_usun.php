<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        // usuniecie klienta z salesmanago
        if ( INTEGRACJA_SALESMANAGO_WLACZONY == 'tak' ) {
             //
             $zapytanie = "select subscribers_email_address from subscribers where customers_id = '" . (int)$_POST['id'] . "'";
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
             $zapytanie = "select subscribers_email_address from subscribers where customers_id = '" . (int)$_POST['id'] . "'";
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
             $zapytanie = "select subscribers_email_address from subscribers where customers_id = '" . (int)$_POST['id'] . "'";
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
             $zapytanie = "select subscribers_email_address from subscribers where customers_id = '" . (int)$_POST['id'] . "'";
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
             $zapytanie = "select subscribers_email_address from subscribers where customers_id = '" . (int)$_POST['id'] . "'";
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
             $zapytanie = "select subscribers_email_address from subscribers where customers_id = '" . (int)$_POST['id'] . "'";
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
        $db->delete_query('customers' , " customers_id = '".(int)$_POST['id']."'");  
        $db->delete_query('customers_info' , " customers_info_id = '".(int)$_POST['id']."'");  
        $db->delete_query('address_book' , " customers_id = '".(int)$_POST['id']."'"); 
        $db->delete_query('subscribers' , " customers_id = '".(int)$_POST['id']."'"); 
        $db->delete_query('discount_manufacturers' , " discount_customers_id = '".(int)$_POST['id']."'");
        $db->delete_query('discount_categories' , " discount_customers_id = '".(int)$_POST['id']."'");       
        $db->delete_query('discount_categories_manufacturers' , " discount_customers_id = '".(int)$_POST['id']."'");       
        $db->delete_query('discount_products' , " discount_customers_id = '".(int)$_POST['id']."'");         
        $db->delete_query('customers_points' , " customers_id = '".(int)$_POST['id']."'");         
        $db->delete_query('customers_to_extra_fields' , " customers_id = '".(int)$_POST['id']."'");     
        $db->delete_query('blacklist' , " blacklist_customers_id = '".(int)$_POST['id']."'"); 
        //

        $zapytanie = "select basket_id from basket_save where customers_id = '" . (int)$_POST['id'] . "'";
        $sql = $db->open_query($zapytanie);        
        while ( $info = $sql->fetch_assoc() ) {
                $db->delete_query('basket_save_products' , " basket_id = '" . $info['basket_id'] . "'");
        }    
        $db->close_query($sql);
        unset($zapytanie, $info);  

        // usuwa zapisane koszyki klienta
        $db->delete_query('basket_save' , " customers_id = '".(int)$_POST['id']."'");
                    
        //
        Funkcje::PrzekierowanieURL('klienci.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="klienci/klienci_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from customers where customers_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      Czy skasować pozycje ?
                    </p>   
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('klienci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','klienci');">Powrót</button> 
                </div>

            <?php
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            $db->close_query($sql);
            unset($zapytanie);
            ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}