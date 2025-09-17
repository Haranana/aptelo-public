<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        if ( isset($_POST['email_1']) && $_POST['email_1'] != '') {
          
            $pola = array(array('customers_status','1'));
            $db->update_query('customers' , $pola, " customers_id = '" . (int)$_POST["id"] . "'");
            unset($pola);             
        
            $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '" . (int)$_POST['jezyk'] . "' WHERE t.email_var_id = 'EMAIL_AKTYWACJA_KONTA'";
            $sql = $db->open_query($zapytanie_tresc);
            $tresc = $sql->fetch_assoc();    
        
            $email = new Mailing;
            
            if ( $tresc['email_file'] != '' ) {
              $tablicaZalacznikow = explode(';', (string)$tresc['email_file']);
            } else {
              $tablicaZalacznikow = array();
            }

            $nadawca_email = Funkcje::parsujZmienne($tresc['sender_email']);
            $nadawca_nazwa = Funkcje::parsujZmienne($tresc['sender_name']); 

            $adresat_email = $filtr->process($_POST['email_1']);
            $adresat_nazwa = $filtr->process($_POST['adresat_nazwa']);
            
            $kopia_maila = array();
            for ( $t = 2; $t < 4; $t++ ) {
                //
                if ( isset($_POST['email_' . $t]) && $_POST['email_' . $t] != '') {
                     $kopia_maila[] = $filtr->process($_POST['email_' . $t]);
                }
                //
            }

            $temat           = $filtr->process($_POST['temat']);
            $tekst           = $filtr->process($_POST['wiadomosc']);
            $zalaczniki      = $tablicaZalacznikow;
            $szablon         = $tresc['template_id'];
            $jezyk           = $_SESSION['domyslny_jezyk']['id'];  

            $email->wyslijEmail($nadawca_email, $nadawca_nazwa, $adresat_email, $adresat_nazwa, implode(',', (array)$kopia_maila), $temat, $tekst, $szablon, $jezyk, $zalaczniki);

            $db->close_query($sql);
            unset($tresc, $zapytanie_tresc, $nadawca_email, $nadawca_nazwa, $adresat_email, $kopia_maila, $adresat_nazwa, $temat, $tekst, $szablon, $jezyk);           

        }

        Funkcje::PrzekierowanieURL('klienci_status_email.php?id_poz=' . (int)$_POST["id"] . '&wyslano');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Wysłanie wiadomości e-mail o aktywacji konta do klienta</div>
    <div id="cont">
    
        <?php
        if ( isset($_GET['wyslano']) ) {
        ?>
          
            <div class="poleForm">
        
                <div class="naglowek">Wysyłanie wiadomości o aktywacji konta</div>

                <div class="pozycja_edytowana">

                  <div class="MailWyslano">
                      Mail został wysłany oraz konto klienta zostało aktywowane ...
                  </div>    
                  
                  <div class="przyciski_dolne">
                    <button type="button" class="przyciskNon" onclick="cofnij('klienci','<?php echo Funkcje::Zwroc_Get(array('x','y','wyslano')); ?>','klienci');">Powrót</button> 
                  </div>

                </div>     

            </div>
            
        <?php
        
        } else {

            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            if ( !isset($_GET['jezyk']) ) {
                 $_GET['jezyk'] = $_SESSION['domyslny_jezyk']['id'];
            }
            
            $zapytanie = "select * from customers where customers_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);

            if ((int)$db->ile_rekordow($sql) > 0) {
              
              $info = $sql->fetch_assoc();
              ?>
              
              <form action="klienci/klienci_status_email.php" method="post" id="emailForm" class="cmxform">    

                <script>           
                $(document).ready(function(){
                    ckedit('wiadomosc','99%','300');
                    
                    // Skrypt do walidacji formularza
                    $("#emailForm").validate({
                      rules: {
                        temat: { required: true},
                        email_1: { required: true, email: true},
                        email_2: { email: true},
                        email_3: { email: true}
                      }
                    });                    
                });
                </script>               

                <div class="poleForm">

                  <div class="naglowek">Wysyłanie wiadomości o aktywacji konta</div>

                  <div class="pozycja_edytowana">

                    <div class="info_content">

                      <input type="hidden" name="akcja" value="zapisz" />
                      <input type="hidden" id="jezyk" name="jezyk" value="<?php echo (int)$_GET['jezyk']; ?>" />

                      <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />

                      <?php
                      $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '" . (int)$_GET['jezyk'] . "' WHERE t.email_var_id = 'EMAIL_AKTYWACJA_KONTA'";
                      $sql_tresc = $db->open_query($zapytanie_tresc);
                      $tresc = $sql_tresc->fetch_assoc();                      
                      ?>            

                      <p class="JezykiMailaZmiana">
                          <label>Wersja językowa maila:</label>
                          <?php
                          $sqlJezykow = $db->open_query("SELECT * FROM languages WHERE status = '1' ORDER BY sort_order");
                          while ($infe = $sqlJezykow->fetch_assoc()) {
                              echo '<a ' . (($infe['languages_id'] == (int)$_GET['jezyk']) ? 'class="AktywnyJezyk"' : '') . ' href="klienci/klienci_status_email.php?id_poz=' . (int)$_GET['id_poz'] . '&jezyk=' . $infe['languages_id'] . '">' . $infe['name'] . '</a>';
                          }
                          $db->close_query($sqlJezykow);
                          unset($infe);
                          ?>
                      </p>

                      <p>
                        <label class="required" for="temat">Temat:</label>
                        <input type="text" name="temat" id="temat" size="83" value="<?php echo $tresc['email_title']; ?>" />
                        <input type="hidden" id="adresat_nazwa" name="adresat_nazwa" value="<?php echo $info['customers_firstname'].' '.$info['customers_lastname']; ?>" />
                      </p>       

                      <br />
                      
                      <table class="WyslijMail">
                          <tr>
                              <td><label for="email_1">Wyślij na maile:</label></td>
                              <td>
                                <input type="text" size="35" name="email_1" id="email_1" value="<?php echo $info['customers_email_address']; ?>" /> <br />
                                <input type="text" size="35" name="email_2" id="email_2" value="" /> <br />
                                <input type="text" size="35" name="email_3" id="email_3" value="" />
                              </td>
                          </tr>
                      </table>  
                      
                      <?php
                      //
                      $tekst = $tresc['description'];
                      //
                      $db->close_query($sql_tresc);
                      unset($zapytanie_tresc);  

                      if ( WLACZENIE_SSL == 'tak' ) {
                          define('LINK', ADRES_URL_SKLEPU_SSL."/logowanie.html"); 
                      } else {
                          define('LINK', ADRES_URL_SKLEPU."/logowanie.html");
                      }

                      //
                      $tekst = Funkcje::parsujZmienne($tekst);
                      $tekst = preg_replace("{(<br[\\s]*(>|\/>)\s*){2,}}i", "<br /><br />", (string)$tekst);                    
                      //
                      ?>

                      <p>
                        <label>Treść wiadomości:</label>
                        <textarea id="wiadomosc" name="wiadomosc" cols="150" rows="10"><?php echo $tekst; ?></textarea>
                      </p>

                    </div>

                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Wyślij wiadomość e-mail i aktywuj konto klienta" />
                      <button type="button" class="przyciskNon" onclick="cofnij('klienci','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','klienci');">Powrót</button> 
                    </div>

                  </div>

                </div>

              </form>
              
              <?php
              
              unset($info);
              
            } else {
            
                echo '<div class="poleForm">
                        <div class="naglowek">Wysyłanie wiadomości</div>
                        <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
                      </div>';

            }
            
            $db->close_query($sql);
            unset($zapytanie);
        
        }
        ?>

    </div>
    
    <?php
    include('stopka.inc.php');

}
