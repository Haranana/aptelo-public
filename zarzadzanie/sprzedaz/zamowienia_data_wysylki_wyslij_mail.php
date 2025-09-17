<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        if ( isset($_POST['adresat']) && $_POST['adresat'] != '') {
        
            $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '" . (int)$_POST['jezyk'] . "' WHERE t.email_var_id = 'EMAIL_ZMIANA_DATY_WYSYLKI'";
            $sql = $db->open_query($zapytanie_tresc);
            $tresc = $sql->fetch_assoc();    

            $email = new Mailing;
            
            $nadawca_email   = Funkcje::parsujZmienne($tresc['sender_email']);
            $nadawca_nazwa   = Funkcje::parsujZmienne($tresc['sender_name']); 

            $adresat_email   = $filtr->process($_POST['adresat']);
            $adresat_nazwa   = $filtr->process($_POST['adresat_nazwa']);
            
            $cc              = $filtr->process($_POST["cc"]);

            $temat           = $filtr->process($_POST['temat']);
            $tekst           = str_replace('{NOWA_DATA_WYSYLKI}', (string)$filtr->process($_POST['data_wysylki']), (string)$filtr->process($_POST['wiadomosc']));
            $zalaczniki      = array();
            $szablon         = $tresc['template_id'];
            $jezyk           = $_POST['jezyk'];  
            
            $email->wyslijEmail($nadawca_email, $nadawca_nazwa, $adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki);
            
            $pola = array(array('shipping_date',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($_POST['data_wysylki']))),
                          array('shipping_date_mail_send','now()'));
                          
            $db->update_query('orders' , $pola, "orders_id = '" . (int)$_POST["id_poz"] . "'");
            unset($pola);            

            $db->close_query($sql);
            unset($tresc, $zapytanie_tresc, $nadawca_email, $nadawca_nazwa, $adresat_email, $cc, $adresat_nazwa, $temat, $tekst, $szablon, $jezyk);           

        }

        Funkcje::PrzekierowanieURL('zamowienia.php?id_poz=' . (int)$_POST["id_poz"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Wysłanie wiadomości e-mail z informacją o zmianie czasu wysyłki produktów</div>
    <div id="cont">
    
        <?php
        if ( !isset($_GET['id_poz']) ) {
             $_GET['id_poz'] = 0;
        }    
        if ( !isset($_GET['jezyk']) ) {
             $_GET['jezyk'] = $_SESSION['domyslny_jezyk']['id'];
        }
        
        $zapytanie = "select * from orders where orders_id = '" . (int)$_GET['id_poz'] . "' and shipping_date != '0000-00-00'";
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) {

            $info = $sql->fetch_assoc();
            ?>  
            
            <form action="sprzedaz/zamowienia_data_wysylki_wyslij_mail.php" method="post" id="emailForm" class="cmxform">          

              <div class="poleForm">

                <div class="naglowek">Wysłanie wiadomości e-mail z informacją o zmianie czasu wysyłki produktów</div>
                
                <div class="pozycja_edytowana">

                    <div class="info_content">

                      <input type="hidden" name="akcja" value="zapisz" />
                      <input type="hidden" name="id_poz" value="<?php echo $_GET['id_poz']; ?>" />
                      <input type="hidden" id="jezyk" name="jezyk" value="<?php echo (int)$_GET['jezyk']; ?>" />

                      <?php
                      $zapytanieTresc = "select * from email_text et, email_text_description etd where et.email_var_id = 'EMAIL_ZMIANA_DATY_WYSYLKI' and et.email_text_id = etd.email_text_id and etd.language_id = '" . (int)$_GET['jezyk'] . "'";
                      $sqlTresc = $db->open_query($zapytanieTresc);
                      //
                      $inft = $sqlTresc->fetch_assoc();
                      ?>
                      
                      <input type="hidden" name="szablon" value="<?php echo $inft['template_id']; ?>" />

                      <p class="JezykiMailaDataWysylki">
                        <label>Wersja językowa szablonu:</label>
                        <?php
                        $sqlJezykow = $db->open_query("SELECT * FROM languages WHERE status = '1' ORDER BY sort_order");
                        while ($infe = $sqlJezykow->fetch_assoc()) {
                            echo '<a ' . (($infe['languages_id'] == (int)$_GET['jezyk']) ? 'class="AktywnyJezyk"' : '') . ' href="sprzedaz/zamowienia_data_wysylki_wyslij_mail.php?id_poz=' . (int)$_GET['id_poz'] . '&jezyk=' . $infe['languages_id'] . '">' . $infe['name'] . '</a>';
                        }
                        $db->close_query($sqlJezykow);
                        unset($infe);
                        ?>
                      </p>

                      <p>
                        <label class="required" for="temat">Temat:</label>
                        <input type="text" name="temat" id="temat" size="83" value="<?php echo str_replace('{NUMER_ZAMOWIENIA}', (string)$_GET['id_poz'], (string)$inft['email_title']); ?>" />
                      </p>

                      <p>
                        <label for="adresat">Wyślij na maila:</label>
                        <input type="text" name="adresat" id="adresat" size="83" value="<?php echo $info['customers_email_address']; ?>" />
                        <input type="hidden" name="adresat_nazwa" value="<?php echo (($info['customers_company'] != '') ? $info['customers_company'] : $info['customers_name']); ?>" />
                      </p>
                      
                      <p>
                        <label for="cc">Do wiadomości:</label>
                        <input type="text" name="cc" id="cc" size="83" value="<?php echo $inft['dw']; ?>" /><em class="TipIkona"><b>Rozdzielone przecinkami adresy e-mail na które ma zostać przesłana kopia wiadomości</b></em>
                      </p>       

                      <p>
                        <label style="color:#0283f0;font-weight:bold" for="data_wysylki">Nowy termin wysyłki:</label>
                        <input type="text" name="data_wysylki" value="<?php echo date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($info['shipping_date'])); ?>" size="10" style="width:100px !important" class="datepicker" />
                        <script>
                        $(document).ready(function() {
                          $('input.datepicker').Zebra_DatePicker({
                             format: 'd-m-Y',
                             inside: false,
                             readonly_element: true
                          });                
                        });  
                        </script>                        
                      </p>
                      
                      <?php
                      //
                      $tekst = $inft['description'];
                      $tekst = str_replace('{NOWA_DATA_WYSYLKI}', '#NOWA_DATA_WYSYLKI#', (string)$tekst);
                      //
                      $i18n = new Translator($db, (int)$_GET['jezyk']);
                      $GLOBALS['tlumacz'] = $i18n->tlumacz( array('ZAMOWIENIE_REALIZACJA','PRODUKT','WYGLAD'), null, true );
                      //
                      // podmiana danych
                      define('NUMER_ZAMOWIENIA', (int)$_GET['id_poz']);
                      //
                      $tekst = Funkcje::parsujZmienne($tekst);
                      $tekst = preg_replace("{(<br[\\s]*(>|\/>)\s*){2,}}i", "<br /><br />", (string)$tekst);                    
                      //
                      $tekst = str_replace('#NOWA_DATA_WYSYLKI#', '{NOWA_DATA_WYSYLKI}', (string)$tekst);
                      ?>
                    
                      <p>
                        <label>Treść wiadomości:<em class="TipIkona"><b>Wpisz tylko treść wiadomości - pozostałe elementy zostaną dołączone z domyślnego szablonu wiadomości email</b></em></label>
                        <textarea id="wiadomosc" name="wiadomosc" class="wysiwyg" cols="150" rows="10"><?php echo $tekst; ?></textarea>
                      </p>

                      <?php
                      $db->close_query($sqlTresc);
                      unset($zapytanieTresc, $inft);                        
                      ?>
                      
                    </div>

                    <div class="przyciski_dolne">
                      <input id="form_submit" type="submit" class="przyciskNon" value="Wyślij wiadomość e-mail" />
                      <button type="button" class="przyciskNon" onclick="cofnij('zamowienia','?id_poz=<?php echo $_GET['id_poz']; ?>','sprzedaz');">Powrót</button> 
                    </div>

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
        unset($zapytanie, $info);
        ?>

    </div>
    
    <?php
    include('stopka.inc.php');

}
