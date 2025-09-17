<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        if ( isset($_POST['email']) && $_POST['email'] != '') {
        
            $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' WHERE t.email_var_id = 'RECENZJA_O_PRODUKTACH'";
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
            $cc              = Funkcje::parsujZmienne($tresc['dw']);

            $adresat_email = $filtr->process($_POST['email']);
            $adresat_nazwa = $filtr->process($_POST['adresat_nazwa']);
            
            $temat         = $filtr->process($_POST['temat']);
            $tekst         = $filtr->process($_POST['wiadomosc']);
            $zalaczniki    = $tablicaZalacznikow;
            $szablon       = $tresc['template_id'];
            $jezyk         = $_SESSION['domyslny_jezyk']['id'];  

            $email->wyslijEmail($nadawca_email, $nadawca_nazwa, $adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki);
    
            $db->close_query($sql);
            unset($tresc, $zapytanie_tresc, $nadawca_email, $nadawca_nazwa, $adresat_email, $kopia_maila, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk);           
            
            // zapisuje dane w bazie o wyslaniu
            $pola = array(array('reviews_products_date','now()'));
            $db->update_query('orders' , $pola, " orders_id = '" . (int)$_POST["id"] . "'");	
                    
        }

        Funkcje::PrzekierowanieURL('zamowienia_wyslij_email_o_recenzje.php?id_poz=' . (int)$_POST["id"] . '&wyslano');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Wysłanie wiadomości e-mail z prośbą o recenzje o produktach</div>
    <div id="cont">
    
        <?php
        if ( isset($_GET['wyslano']) ) {
        ?>
          
            <div class="poleForm">
        
                <div class="naglowek">Wysłanie wiadomości e-mail z prośbą o recenzje o produktach</div>

                <div class="pozycja_edytowana">

                  <div class="MailWyslano">
                      Mail został wysłany ...
                  </div>    
                  
                  <div class="przyciski_dolne">
                    <button type="button" class="przyciskNon" onclick="cofnij('zamowienia','<?php echo Funkcje::Zwroc_Get(array('x','y','wyslano')); ?>','sprzedaz');">Powrót</button> 
                  </div>

                </div>     

            </div>
            
        <?php
        
        } else {

            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
              
            $zapytanie = "select * from orders where orders_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);

            if ((int)$db->ile_rekordow($sql) > 0) {
            ?>
              
              <form action="sprzedaz/zamowienia_wyslij_email_o_recenzje.php" method="post" id="emailForm" class="cmxform">    

                <script>           
                $(document).ready(function(){
                    ckedit('wiadomosc','99%','400');

                    $("#emailForm").validate({
                      rules: {
                        temat: { required: true},
                        email: { required: true, email: true},
                      }
                    });                    
                });
                </script>               

                <div class="poleForm">

                  <div class="naglowek">Wysłanie wiadomości e-mail z prośbą o recenzje o produktach</div>

                  <div class="pozycja_edytowana">

                    <div class="info_content">

                      <input type="hidden" name="akcja" value="zapisz" />

                      <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />

                      <?php
                      $zamowienie = new Zamowienie((int)$_GET['id_poz']);
                      
                      $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' WHERE t.email_var_id = 'RECENZJA_O_PRODUKTACH'";
                      $sql_tresc = $db->open_query($zapytanie_tresc);
                      $tresc = $sql_tresc->fetch_assoc();                      
                      ?>            

                      <p>
                        <label class="required" for="temat">Temat:</label>
                        <input type="text" name="temat" id="temat" size="83" value="<?php echo str_replace('{NUMER_ZAMOWIENIA}', (string)$zamowienie->info['id_zamowienia'], (string)$tresc['email_title']); ?>" />
                        <input type="hidden" name="adresat_nazwa" value="<?php echo $zamowienie->klient['nazwa']; ?>" />
                      </p>       

                      <p>
                        <label for="email">Wyślij na maila:</label>
                        <input type="text" size="35" name="email" id="email" value="<?php echo $zamowienie->klient['adres_email']; ?>" />
                      </p>  
                      
                      <?php
                      //
                      $tekst = $tresc['description'];
                      //
                      $db->close_query($sql_tresc);
                      unset($zapytanie_tresc);  
                      //
                      $i18n = new Translator($db, $_SESSION['domyslny_jezyk']['id']);
                      $GLOBALS['tlumacz'] = $i18n->tlumacz( array('WYGLAD'), null, true );
                      //
                      // podmiana danych
                      if ( SYSTEM_PUNKTOW_STATUS == 'tak' ) {
                           define('ILOSC_PKT_ZA_RECENZJE', SYSTEM_PUNKTOW_PUNKTY_RECENZJE);
                        } else {
                           define('ILOSC_PKT_ZA_RECENZJE', '');
                      }
                      
                      $hashKod = '/nr=' . $zamowienie->info['id_zamowienia'] . '/zamowienie=' . hash("sha1", $zamowienie->info['id_zamowienia'] . ';' . $zamowienie->info['data_zamowienia'] . ';' . $zamowienie->klient['adres_email'] . ';' . $zamowienie->klient['id']);

                      $LinkiRecenzji = array();
                      foreach ( $zamowienie->produkty as $id => $produkt ) {
                          //
                          if ( $produkt['id_produktu'] > 0 ) {
                               //
                               if ( isset($zamowienie->link_recenzji[$produkt['id_produktu']]) ) {
                                    $LinkiRecenzji[ $produkt['id_produktu'] ] = '<a href="' . ADRES_URL_SKLEPU . '/napisz-recenzje-rw-' . $produkt['id_produktu'] . '.html/recenzja=' . $zamowienie->link_recenzji[$produkt['id_produktu']] . $hashKod . '">' . $produkt['nazwa'] . '</a>';
                               }
                               //
                          }
                          //
                      }
                      
                      unset($hashKod);
                      
                      define('LINKI_DO_RECENZJI', implode('<br />', (array)$LinkiRecenzji));
                      //
                      $tekst = Funkcje::parsujZmienne($tekst);
                      $tekst = preg_replace("{(<br[\\s]*(>|\/>)\s*){2,}}i", "<br /><br />", (string)$tekst);                    
                      //                      
                      ?>

                      <?php if ( count($LinkiRecenzji) ) { ?>
                      
                      <p>
                        <label>Treść wiadomości:</label>
                        <textarea id="wiadomosc" name="wiadomosc" cols="150" rows="10"><?php echo $tekst; ?></textarea>
                      </p>
                      
                      <?php } else { ?>
                      
                      <div class="maleInfo" style="margin-top:10px;margin-bottom:10px">Brak produktów do wystawienia recenzji ...</div>
                      
                      <?php } ?>

                    </div>

                    <?php if ( count($LinkiRecenzji) ) { ?>
                    
                    <div class="przyciski_dolne">                      
                      <input type="submit" class="przyciskNon" value="Wyślij wiadomość e-mail" />                      
                      <button type="button" class="przyciskNon" onclick="cofnij('zamowienia','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','sprzedaz');">Powrót</button> 
                    </div>
                    
                    <?php } else { ?>
                    
                    <div class="przyciski_dolne" style="padding-left:0px">                                         
                      <button type="button" class="przyciskNon" onclick="cofnij('zamowienia','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','sprzedaz');">Powrót</button> 
                    </div>                    
                    
                    <?php } ?>

                  </div>

                </div>

              </form>
              
              <?php
              
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
