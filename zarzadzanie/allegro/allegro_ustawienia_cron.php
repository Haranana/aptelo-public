<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $wynik  = '';
    $zapisano = false;

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
      $zapisano = true;

      reset($_POST);
      foreach ( $_POST as $key => $value ) {
          if ( $key != 'akcja' ) {
              $pola = array(
                      array('value',$value)
              );
              $db->update_query('allegro_connect' , $pola, " params = '".strtoupper((string)$key)."'");	
              unset($pola);
          }
      }

      $wynik = '<div id="allegro_cron" class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zmienione</div>';

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Konfiguracja parametrów automatycznej synchronizacji aukcji Allegro</div>
    <div id="cont">
     
      <script>
      $(document).ready(function() {
        
          $('#cron_token').on("keyup",function() {
             $('#SciezkaCron').html( $('#SciezkaCron').attr('data-link') + '?token=' + $(this).val() );
          });
          
          $("#allegroForm").validate({
              rules: {
                cron_token: {
                  required: true,
                },
                cron_min_ilosc: {
                  required: true,
                }
              }
          });                      
      });                  
      </script>   
                
      <form action="allegro/allegro_ustawienia_cron.php" method="post" id="allegroForm" class="cmxform">
        <div class="poleForm">

            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            } 

            $zapytanie = "SELECT * FROM allegro_connect WHERE params LIKE 'CRON_%'";
            $sql = $db->open_query($zapytanie);
            
            if ( $db->ile_rekordow($sql) > 0 ) {
                
                $TablicaWartosci = array();

                while ( $info = $sql->fetch_assoc() ) {
                    $TablicaWartosci[$info['params']] = $info['value'];
                }
                ?>

                <div class="naglowek">Edycja danych</div>

                <div class="pozycja_edytowana">
                
                  <div class="info_content">
                
                  <?php echo (( isset($blad) && $blad != '') ? '<div class="ostrzezenie" style="margin:10px 0 10px 10px;">' . $blad .'</div>' : '' ); ?>

                  <input type="hidden" name="akcja" value="zapisz" />
                  
                  <p>
                    <label style="width:40%;">Czy umożliwić synchronizację Allegro poprzez zewnętrzny link:</label>
                    <input type="radio" value="0" name="cron_status" id="cron_status_nie" <?php echo ($TablicaWartosci['CRON_STATUS'] == '0' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cron_status_nie">nie</label>
                    <input type="radio" value="1" name="cron_status" id="cron_status_tak" <?php echo ($TablicaWartosci['CRON_STATUS'] == '1' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cron_status_tak">tak</label>
                  </p>        

                  <div class="EksportCronInfo">
                  
                      <p>
                        <label class="required" style="width:40%;">Token pliku:</label>
                        <input type="text" value="<?php echo $TablicaWartosci['CRON_TOKEN']; ?>" name="cron_token" id="cron_token" size="15" class="required" /> <em class="TipIkona"><b>Token (ciąg znaków z liter) do zabezpieczenia generowania pliku przez osoby nieupoważnione</b></em>
                      </p>   

                      <p>
                        <label style="width:40%;">Ścieżka pliku synchronizacji poza sklepem:</label>
                        <span id="SciezkaCron" data-link="<?php echo ADRES_URL_SKLEPU . '/allegro_synchronizacja.php'; ?>"><?php echo ADRES_URL_SKLEPU . '/allegro_synchronizacja.php?token=' . $TablicaWartosci['CRON_TOKEN']; ?></span>
                      </p>   

                      <div class="maleInfo">
                          Podany powyżej link można użyć do generowania pliku dla synchronizacji pomiędzy sklepem i Allegro poza sklepem - bezpośrednio z poziomu przeglądarki. Można go również użyć do cyklicznego wykonywania w zadaniach CRON na serwerze. 
                          Podanego skryptu <b>nie</b> można dodać do Harmonogramu zadań w sklepie (menu Narzędzia) ponieważ spowoduje to zablokowanie działania sklepu.
                       </div>

                  </div>

                  <p style="border-top: 1px dashed #c0d9e6;padding-top:10px">
                    <label style="width:40%;">Oznacz w bazie sklepu do archwium aukcje starsze niż 365 dni:</label>
                    <input type="radio" value="0" name="cron_archiwum" id="cron_archiwum_nie" <?php echo ($TablicaWartosci['CRON_ARCHIWUM'] == '0' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cron_archiwum_nie">nie<em class="TipIkona"><b>Statusy aukcji nie odnalezionych w Allegro nie będą w sklepie zmienione</b></em></label>
                    <input type="radio" value="1" name="cron_archiwum" id="cron_archiwum_tak" <?php echo ($TablicaWartosci['CRON_ARCHIWUM'] == '1' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cron_archiwum_tak">tak<em class="TipIkona"><b>Statusy aukcji nie odnalezionych w Allegro będą w sklepie oznaczone jako archiwum</b></em></label>
                  </p>                  
                  
                  <div class="maleInfo">
                    Jeżeli aukcja nie zostanie odnaleziona w Allegro, to w bazie sklepu zostanie oznaczona jako archiwum lub pozostanie bez zmian.
                  </div>

                  <p style="border-top: 1px dashed #c0d9e6;padding-top:10px">
                    <label style="width:40%;">Wyłącz aukcje jeżeli ilość produktu w sklepie jest mniejsza niż ustalona wartość:</label>
                    <input type="radio" value="0" name="cron_wylacz_produkt" id="cron_wylacz_produkt_nie" <?php echo ($TablicaWartosci['CRON_WYLACZ_PRODUKT'] == '0' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cron_wylacz_produkt_nie">nie<em class="TipIkona"><b>Aukcje w Allegro pozostaną aktywne</b></em></label>
                    <input type="radio" value="1" name="cron_wylacz_produkt" id="cron_wylacz_produkt_tak" <?php echo ($TablicaWartosci['CRON_WYLACZ_PRODUKT'] == '1' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cron_wylacz_produkt_tak">tak<em class="TipIkona"><b>Jeżeli ilość produktów w sklepie jest mniejsza niż ustalona wartość - to aukcja w Allegro zostanie wyłączona</b></em></label>
                  </p>

                  <p>
                    <label for="cron_min_ilosc" class="required" style="width:40%;">Minimalna ilość produktu:</label>
                    <input type="text" value="<?php echo $TablicaWartosci['CRON_MIN_ILOSC']; ?>" name="cron_min_ilosc" id="cron_min_ilosc" size="5" class="calkowita" /><em class="TipIkona"><b>Ilość produktów w sklepie poniżej której aukcja zostanie wyłączona</b></em>
                  </p>  

                  <div class="maleInfo">
                    Jeżeli ilość produktu w sklepie jest mniejsza niż ustalona wartość to wyłącza aukcje.
                  </div>

                  <p style="border-top: 1px dashed #c0d9e6;padding-top:10px">
                    <label style="width:40%;">Zmniejsz ilość wystawionych produktów w Allegro:</label>
                    <input type="radio" value="0" name="cron_ilosc_allegro" id="cron_ilosc_allegro_nie" <?php echo ($TablicaWartosci['CRON_ILOSC_ALLEGRO'] == '0' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cron_ilosc_allegro_nie">nie<em class="TipIkona"><b>Ilość wystawionych produków w Allegro pozostanie bez zmian</b></em></label>
                    <input type="radio" value="1" name="cron_ilosc_allegro" id="cron_ilosc_allegro_tak" <?php echo ($TablicaWartosci['CRON_ILOSC_ALLEGRO'] == '1' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cron_ilosc_allegro_tak">tak<em class="TipIkona"><b>Jeżeli ilość produktów w sklepie jest mniejsza niż na Allegro, to zmniejsza ilość w aukcji w Allegro</b></em></label>
                  </p>                  

                  <div class="maleInfo">
                    Jeżeli ilość produktu w sklepie jest mniejsza niż w Allegro, to zmniejsza ilość wystawionych ofert w aukcji.
                  </div>

                  <p style="border-top: 1px dashed #c0d9e6;padding-top:10px">
                    <label style="width:40%;">Wyłącz produkt w sklepie:</label>
                    <input type="radio" value="0" name="cron_produkt_status" id="cron_produkt_status_nie" <?php echo ($TablicaWartosci['CRON_PRODUKT_STATUS'] == '0' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cron_produkt_status_nie">nie<em class="TipIkona"><b>Status wystawionych produków w sklepie pozostanie bez zmian</b></em></label>
                    <input type="radio" value="1" name="cron_produkt_status" id="cron_produkt_status_tak" <?php echo ($TablicaWartosci['CRON_PRODUKT_STATUS'] == '1' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cron_produkt_status_tak">tak<em class="TipIkona"><b>Jeżeli aukcja w Allegro jest zakończona to status produktu w sklepie będzie ustawiony na nieaktywny</b></em></label>
                  </p>                  

                  <div class="maleInfo">
                    Jeżeli aukcja w Allegro jest zakończona z powodu sprzedania wszystkich produktów, status produktu w sklepie będzie ustawiony na nieaktywny.
                  </div>

                  <p style="border-top: 1px dashed #c0d9e6;padding-top:10px">
                    <label style="width:40%;">Zmniejsz ilość produktu w sklepie:</label>
                    <input type="radio" value="0" name="cron_produkt_ilosc" id="cron_produkt_ilosc_nie" <?php echo ($TablicaWartosci['CRON_PRODUKT_ILOSC'] == '0' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cron_produkt_ilosc_nie">nie<em class="TipIkona"><b>Nie zmniejszaj ilości produktu w sklepie</b></em></label>
                    <input type="radio" value="1" name="cron_produkt_ilosc" id="cron_produkt_ilosc_tak" <?php echo ($TablicaWartosci['CRON_PRODUKT_ILOSC'] == '1' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cron_produkt_ilosc_tak">tak<em class="TipIkona"><b>Zmniejsz ilość produktu w sklepie</b></em></label>
                  </p>                  

                  <div class="maleInfo">
                    Jeżeli ilość produktów na Allegro jest mniejsza niż w sklepie to zmniejszy stan magazynowy w sklepie do takiego jak jest w Allegro.
                  </div>
                  <div class="ostrzezenie" style="margin:5px 0 5px 10px;">
                    Z tej opcji mozna korzystać tylko wówczas jeżeli zamówienia w sklepie nie są tworzone na podstawie pobieranych transakcji z Allegro. W przeciwnym razie stany magazynowe będą też zmieniane podczs tworzenia zamówień z pobranych transakcji.
                  </div>

                  <p style="border-top: 1px dashed #c0d9e6;padding-top:10px">
                    <label style="width:40%;">Zwiększ ilość produktu w sklepie:</label>
                    <input type="radio" value="0" name="cron_allegro_ilosc" id="cron_allegro_ilosc_nie" <?php echo ($TablicaWartosci['CRON_ALLEGRO_ILOSC'] == '0' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cron_allegro_ilosc_nie">nie<em class="TipIkona"><b>Nie większaj ilości produktu w sklepie</b></em></label>
                    <input type="radio" value="1" name="cron_allegro_ilosc" id="cron_allegro_ilosc_tak" <?php echo ($TablicaWartosci['CRON_ALLEGRO_ILOSC'] == '1' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cron_allegro_ilosc_tak">tak<em class="TipIkona"><b>Zwiększ ilość produktu w sklepie</b></em></label>
                  </p>                  

                  <div class="maleInfo">
                    Jeżeli ilość produktów na Allegro jest większa niż w sklepie to zwiększy stan magazynowy w sklepie do takiego jak jest w Allegro.
                  </div>

                  <p style="border-top: 1px dashed #c0d9e6;padding-top:10px">
                    <label style="width:40%;">Wznów aukcje w Allegro:</label>
                    <input type="radio" value="0" name="cron_aukcja_wznow" id="cron_aukcja_wznow_nie" <?php echo ($TablicaWartosci['CRON_AUKCJA_WZNOW'] == '0' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cron_aukcja_wznow_nie">nie<em class="TipIkona"><b>Aukcja nie zostanie wznowiona</b></em></label>
                    <input type="radio" value="1" name="cron_aukcja_wznow" id="cron_aukcja_wznow_tak" <?php echo ($TablicaWartosci['CRON_AUKCJA_WZNOW'] == '1' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cron_aukcja_wznow_tak">tak<em class="TipIkona"><b>Aukcja zostanie wnowiona z aktualną ilości produktu</b></em></label>
                  </p>                  

                  <div class="maleInfo">
                    Jeżeli aukcja jest zakończona i produkt jest ponownie w sklepie aktywny to aukcja zostanie wznowiona z aktualną ilością produktu.
                  </div>

                  <div class="przyciski_dolne">
                    <input type="submit" class="przyciskNon" value="Zapisz dane" />
                    <?php echo ( $zapisano == true ? $wynik : '' ); ?>
                  </div>    

                  </div>
            
                </div>

            <?php 
            
            $db->close_query($sql);
            unset($zapytanie, $info);
                    
            } else {
            
                echo '<div class="pozycja_edytowana"><div class="BrakDanychInfo">Brak danych do wyświetlenia</div></div>';
            
            }
            ?>
            
        </div>
        
      </form>
      
    </div>

    
    <?php
    include('stopka.inc.php');    
    
} 


?>
