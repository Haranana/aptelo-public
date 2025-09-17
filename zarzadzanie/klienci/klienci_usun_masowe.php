<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        
        $warunki_szukania = '';

        if ( isset($_POST['data_od']) && $_POST['data_od'] != '' ) {
            //     
            $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_od'] . ' 00:00:00')));
            $warunki_szukania .= " and ci.customers_info_date_account_created >= '".$szukana_wartosc."'";
            unset($szukana_wartosc);
            //
        }
        
        if ( isset($_POST['data_do']) && $_POST['data_do'] != '' ) {
            //     
            $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_do'] . ' 23:59:59')));
            $warunki_szukania .= " and ci.customers_info_date_account_created <= '".$szukana_wartosc."'";
            unset($szukana_wartosc);
            //
        }     

        if ( isset($_POST['klient_typ']) && $_POST['klient_typ'] != '0' ) {
            if ( (int)$_POST['klient_typ'] == 1 ) {
                  $warunki_szukania .= " and c.customers_guest_account = '1'";
            }
            if ( (int)$_POST['klient_typ'] == 2 ) {
                  $warunki_szukania .= " and c.customers_guest_account = '0'";
            }            
        }   
        
        if ( $warunki_szukania != '' ) {
          $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
        }        

        $zapytanie = "SELECT c.customers_id
                      FROM customers c
                      LEFT JOIN customers_info ci ON ci.customers_info_id = c.customers_id " . $warunki_szukania;         
                      
        $sql = $db->open_query($zapytanie);
        
        $ile_usunieto = 0;

        if ((int)$db->ile_rekordow($sql) > 0) {
          
            while ( $info = $sql->fetch_assoc() ) {
              
                // usuniecie klienta z salesmanago
                if ( INTEGRACJA_SALESMANAGO_WLACZONY == 'tak' ) {
                     //
                     $zapytanie_integracja = "select subscribers_email_address from subscribers where customers_id = '" . (int)$info['customers_id'] . "'";
                     $sql_integracja = $db->open_query($zapytanie_integracja);     
                     
                     if ((int)$db->ile_rekordow($sql_integracja) > 0) { 

                         $info_integracja = $sql_integracja->fetch_assoc();    
                         
                         $salesmanago = new SalesManago();
                         // sprawdzi czy jest klient                             
                         $SmKlient = $salesmanago->CzyJestKlient( array('email' => $info_integracja['subscribers_email_address']), 'tak' );
                         //
                         if ( $SmKlient != '' ) {
                              //
                              $dane = array('email' => $info_integracja['subscribers_email_address']);
                              //
                              $sm = $salesmanago->UsunKlienta( $dane );
                              //
                              unset($dane);
                              //
                         }
                         //
                         
                     }
                     
                     $db->close_query($sql_integracja);
                     unset($zapytanie_integracja);               
                }   
                                
                // usuniecie z newslettera w systemie Freshmail
                if ( INTEGRACJA_FRESHMAIL_WLACZONY == 'tak' ) {
                     //
                     $zapytanie_integracja = "select subscribers_email_address from subscribers where customers_id = '" . (int)$info['customers_id'] . "'";
                     $sql_integracja = $db->open_query($zapytanie_integracja);     
                     
                     if ((int)$db->ile_rekordow($sql_integracja) > 0) { 

                         $info_integracja = $sql_integracja->fetch_assoc();              
                         //
                         $freshMail = new FreshMail();
                         $freshMail->UsunSubskrybenta($info_integracja['subscribers_email_address']);
                         unset($freshMail);
                         //
                         unset($info_integracja);
                         //
                     }
                     
                     $db->close_query($sql_integracja);
                     unset($zapytanie_integracja);             
                     
                }   

                // usuniecie z newslettera w systemie Freshmail
                if ( INTEGRACJA_MAILERLITE_WLACZONY == 'tak' ) {
                     //
                     $zapytanie_integracja = "select subscribers_email_address from subscribers where customers_id = '" . (int)$info['customers_id'] . "'";
                     $sql_integracja = $db->open_query($zapytanie_integracja);     
                     
                     if ((int)$db->ile_rekordow($sql_integracja) > 0) { 

                         $info_integracja = $sql_integracja->fetch_assoc();              
                         //
                         $mailerLite = new MailerLite();
                         $mailerLite->UsunSubskrybenta($info_integracja['subscribers_email_address']);
                         unset($mailerLite);
                         //
                         unset($info_integracja);
                         //
                     }
                     
                     $db->close_query($sql_integracja);
                     unset($zapytanie_integracja);             
                     
                }
                        
                // usuniecie z newslettera w systemie Ecomail
                if ( INTEGRACJA_ECOMAIL_WLACZONY == 'tak' ) {
                     //
                     $zapytanie_integracja = "select subscribers_email_address from subscribers where customers_id = '" . (int)$info['customers_id'] . "'";
                     $sql_integracja = $db->open_query($zapytanie_integracja);     
                     
                     if ((int)$db->ile_rekordow($sql_integracja) > 0) { 

                         $info_integracja = $sql_integracja->fetch_assoc();              
                         //
                         $ecomail = new Ecomail();
                         $ecomail->UsunSubskrybenta($info_integracja['subscribers_email_address']);
                         unset($ecomail);
                         //
                         unset($info_integracja);
                         //
                     }
                     
                     $db->close_query($sql_integracja);
                     unset($zapytanie_integracja);             
                     
                }   
                        
                // usuniecie z newslettera w systemie Mailjet
                if ( INTEGRACJA_MAILJET_WLACZONY == 'tak' ) {
                     //
                     $zapytanie_integracja = "select subscribers_email_address from subscribers where customers_id = '" . (int)$info['customers_id'] . "'";
                     $sql_integracja = $db->open_query($zapytanie_integracja);     
                     
                     if ((int)$db->ile_rekordow($sql_integracja) > 0) { 

                         $info_integracja = $sql_integracja->fetch_assoc();              
                         //
                         $mailjet = new Mailjet();
                         $mailjet->UsunSubskrybenta($info_integracja['subscribers_email_address']);
                         unset($mailjet);
                         //
                         unset($info_integracja);
                         //
                     }
                     
                     $db->close_query($sql_integracja);
                     unset($zapytanie_integracja);             
                     
                }   

                // usuniecie z newslettera w systemie Getall
                if ( INTEGRACJA_GETALL_WLACZONY == 'tak' ) {
                     //
                     $zapytanie_integracja = "select subscribers_email_address from subscribers where customers_id = '" . (int)$info['customers_id'] . "'";
                     $sql_integracja = $db->open_query($zapytanie_integracja);     
                     
                     if ((int)$db->ile_rekordow($sql_integracja) > 0) { 

                         $info_integracja = $sql_integracja->fetch_assoc();              
                         //
                         $getall = new GetAll(INTEGRACJA_GETALL_APIKEY); 
                         $getall->UsunSubskrybenta($info_integracja['subscribers_email_address']);
                         unset($getall);
                         //
                         unset($info_integracja);
                         //
                     }
                     
                     $db->close_query($sql_integracja);
                     unset($zapytanie_integracja);             
                     
                }               
              
                $db->delete_query('customers' , " customers_id = '".(int)$info['customers_id']."'");  
                $db->delete_query('customers_info' , " customers_info_id = '".(int)$info['customers_id']."'");  
                $db->delete_query('address_book' , " customers_id = '".(int)$info['customers_id']."'"); 
                $db->delete_query('subscribers' , " customers_id = '".(int)$info['customers_id']."'"); 
                $db->delete_query('discount_manufacturers' , " discount_customers_id = '".(int)$info['customers_id']."'");
                $db->delete_query('discount_categories' , " discount_customers_id = '".(int)$info['customers_id']."'");       
                $db->delete_query('discount_categories_manufacturers' , " discount_customers_id = '".(int)$info['customers_id']."'");       
                $db->delete_query('discount_products' , " discount_customers_id = '".(int)$info['customers_id']."'");         
                $db->delete_query('customers_points' , " customers_id = '".(int)$info['customers_id']."'");         
                $db->delete_query('customers_to_extra_fields' , " customers_id = '".(int)$info['customers_id']."'");     
                $db->delete_query('blacklist' , " blacklist_customers_id = '".(int)$info['customers_id']."'"); 
                //

                $zapytanie_koszyk = "select basket_id from basket_save where customers_id = '" . (int)$info['customers_id'] . "'";
                $sql_koszyk = $db->open_query($zapytanie_koszyk);        
                while ( $info_koszyk = $sql_koszyk->fetch_assoc() ) {
                        $db->delete_query('basket_save_products' , " basket_id = '" . $info_koszyk['basket_id'] . "'");
                }    
                $db->close_query($sql_koszyk);
                unset($zapytanie_koszyk, $info_koszyk);  

                // usuwa zapisane koszyki klienta
                $db->delete_query('basket_save' , " customers_id = '".(int)$info['customers_id']."'");              

                $ile_usunieto++;
                
            }
            
            $db->close_query($sql);
            
        }

        Funkcje::PrzekierowanieURL('klienci_usun_masowe.php?skasowane=' . $ile_usunieto);
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
        <form action="klienci/klienci_usun_masowe.php" method="post" class="cmxform" id="usunKlienci">          

        <div class="poleForm">
          <div class="naglowek">Masowe usuwanie klientów</div>
          
          <div class="pozycja_edytowana">
          
              <div class="info_content">
              
                  <?php if ( !isset($_GET['skasowane']) ) { ?>
          
                  <script>
                  $(document).ready(function() {  

                      $("#usunKlienci").validate({
                        rules: {
                          data_od: {
                            required: true
                          },
                          data_do: {
                            required: true             
                          }                
                        }
                      });
            
                      $('input.datepicker').Zebra_DatePicker({
                         format: 'd-m-Y',
                         inside: false,
                         readonly_element: true
                      });       

                  })
                  </script>
              
                  <input type="hidden" name="akcja" value="zapisz" />
                  
                  <div class="maleInfo">Wybierz paremetry usuwanych kont klientów</div>
                  
                  <p>
                    <label for="data_od">Data rejestracji początkowa:</label>
                    <input type="text" name="data_od" id="data_od" value="" size="20" class="datepicker" />                                        
                    <label for="data_od" generated="true" class="error" style="display:none">Pole jest wymagane.</label>
                  </p>

                  <p>
                    <label for="data_do">Data rejestracji końcowa:</label>
                    <input type="text" name="data_do" id="data_do" value="" size="20" class="datepicker" /> 
                    <label for="data_do" generated="true" class="error" style="display:none">Pole jest wymagane.</label>                    
                  </p>           
                  
                  <p>
                    <label for="zamowienie_typ">Rodzaj klienta:</label>
                    <?php
                    $tablica_typ = Array();
                    $tablica_typ[] = array('id' => '0', 'text' => '--- wszyscy klienci ---');
                    $tablica_typ[] = array('id' => '1', 'text' => 'tylko bez rejestracji konta');
                    $tablica_typ[] = array('id' => '2', 'text' => 'tylko z rejestracją konta');
                    //
                    echo Funkcje::RozwijaneMenu('klient_typ', $tablica_typ, '', ' style="max-width:300px" id="klient_typ"'); ?>                    
                  </p>     

                  <?php } else { ?>
                  
                  <div class="maleInfo">Usunięto klientów: <?php echo (int)$_GET['skasowane']; ?></div>
                  
                  <?php } ?>
                  
              </div>
              
          </div>
          
          <?php if ( !isset($_GET['skasowane']) ) { ?>
          
          <div class="ostrzezenie" style="margin:15px">Operacja usunięcia jest nieodracalna ! Klientów po usunięciu nie będzie można przywrócić !</div>
          
          <?php } ?>

          <div class="przyciski_dolne">
            <?php if ( !isset($_GET['skasowane']) ) { ?>
            <input type="submit" class="przyciskNon" value="Usuń dane" />
            <?php } ?>
            <button type="button" class="przyciskNon" onclick="cofnij('klienci','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','klienci');">Powrót</button> 
          </div>

        </div>                      
        </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}