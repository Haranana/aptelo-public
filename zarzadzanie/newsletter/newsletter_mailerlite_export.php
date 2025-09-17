<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
      
    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    
    ?>
    
    <div id="naglowek_cont">Eksport pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#newsForm").validate({
              rules: {
                lista: {
                  required: true
                }            
              },
              messages: {
                lista: {
                  required: "Pole jest wymagane."
                }      
              }
            });
          });
          </script>         

          <form action="newsletter/newsletter_mailerlite_wyslij.php" method="post" id="newsForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Eksport danych do systemu MailerLite</div>
            
            <?php if ( INTEGRACJA_MAILERLITE_WLACZONY == 'tak' ) { ?>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="wyslij" />
                
                <div class="maleInfo">
                  Jeżeli lista nie istnieje w systemie MailerLite - zostanie utworzona. Jeżeli w systemie będzie istniał eksportowany adres email - zostanie pominięty, a lista w MailerLite zostanie zaktualizowana tylko o nowe wpisy.
                </div>
                
                <?php 
                $tryb = 'wszyscy';
                $trybOpis = 'Wszystkie adresy mailowe';
                //
                if ( isset($_GET['zapisani']) ) { 
                     $tryb = 'zapisani';
                     $trybOpis = 'Tylko zapisani do newslettera';
                }
                
                // jezeli adresy z newslettera
                if ( isset($_GET['id_poz']) ) {
                    //
                    $zapytanie = "select * from newsletters where newsletters_id = '" . (int)$_GET['id_poz'] . "'";
                    $sql = $db->open_query($zapytanie);
                    
                    if ((int)$db->ile_rekordow($sql) > 0) {  
                        //
                        $info = $sql->fetch_assoc();
                        //
                        switch ($info['destination']) {
                            case "1":
                                $trybOpis = 'Do wszystkich zarejestrowanych klientów sklepu';
                                break; 
                            case "2":
                                $trybOpis = 'Tylko zarejestrowani klienci którzy wyrazili zgodę na newsletter';
                                break;                          
                            case "3":
                                $trybOpis = 'Tylko klienci którzy zapisali się do newslettera, a nie są klientami sklepu';
                                break;
                            case "4":
                                $trybOpis = 'Do wszystkich którzy zapisali się do newslettera';
                                break;                        
                            case "5":
                                $trybOpis = 'Mailing';
                                break;     
                            case "6":
                                $trybOpis = 'Tylko do określonej grupy klientów';
                                break;   
                            case "7":
                                $doKogo = 'Tylko zarejestrowani klienci z porzuconymi koszykami';
                                break;  
                            case "8":
                                $doKogo = 'Tylko klienci bez rejestracji z porzuconymi koszykami';
                                break; 
                            case "9":
                                $doKogo = 'Wszyscy klienci z porzuconymi koszykami (z kontem oraz bez rejestracji)';
                                break;                                 
                        }             
                        //
                        $tryb = 'id___' . $info['newsletters_id'];
                        //
                        unset($info);
                        //
                    }
                    
                    $db->close_query($sql);
                    unset($zapytanie);
                    //
                }
                ?>                   
                
                <div id="DaneNewslettera">
                    <div>
                      Zakres eksportu:
                      <span><?php echo $trybOpis; ?></span>
                    </div>                
                </div>
                
                <p>
                  <label class="required" for="lista">Nazwa listy odbiorców:</label>
                  <input type="text" name="lista" id="lista" value="" size="55" />
                  <em class="TipIkona"><b>Nazwa listy odbiorców w systemie MailerLite do której mają zostać wyeksportowane adresy mailowe</b></em>
                </p>

                <input type="hidden" name="tryb" value="<?php echo $tryb; ?>" />
                
                <?php
                unset($tryb, $trybOpis);
                ?>
                
                </div>
                
            </div>
            
            <?php } else { ?>
            
            <div class="pozycja_edytowana">
                
                <div style="padding:10px">Integracja z MailerLite nie jest aktywna ...</div>
                
            </div>
            
            <?php } ?>

            <div class="przyciski_dolne">
            
              <?php if ( INTEGRACJA_MAILERLITE_WLACZONY == 'tak' ) { ?>
              <input type="submit" class="przyciskNon" value="Wyślij adresy email" />
              <?php } ?>
              
              <button type="button" class="przyciskNon" onclick="cofnij('<?php echo ((isset($_GET['id_poz'])) ? 'newsletter' : 'newsletter_subskrybenci'); ?>','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','newsletter');">Powrót</button>   
              
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
?>