<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $wynik  = '';
    $system = ( isset($_POST['system']) ? $_POST['system'] : '' );

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      reset($_POST);
      foreach ( $_POST as $key => $value ) {
        if ( $key != 'akcja' ) {
          $pola = array(
                  array('value',$filtr->process($value))
          );
          $db->update_query('settings' , $pola, " code = '".strtoupper((string)$key)."'");	
          unset($pola);
        }
      }

      $wynik = '<div id="'.$system.'" class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zmienione</div>';

    }

    $zapytanie = "SELECT * FROM settings WHERE type = 'wysylki' ORDER BY sort ";
    $sql = $db->open_query($zapytanie);

    $parametr = array();

    if ( $db->ile_rekordow($sql) > 0 ) {
      while ($info = $sql->fetch_assoc()) {
        $parametr[$info['code']] = array($info['value'], $info['limit_values'], $info['description'], $info['form_field_type']);
      }
    }
    $db->close_query($sql);
    unset($zapytanie, $info);

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Konfiguracja parametrów systemów wysyłkowych</div>
    <div id="cont">

      <div class="poleForm">
        <div class="naglowek">Edycja danych - Firma SendIt</div>

        <div class="pozycja_edytowana"> 

          <script>
          $(document).ready(function() {
            $("#form-sendit").validate({
              rules: {
                integracja_sendit_api_key: {required: function() {var wynik = true; if ( $("input[name='integracja_sendit_wlaczony']:checked", "#form-sendit").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_sendit_api_pin: {required: function() {var wynik = true; if ( $("input[name='integracja_sendit_wlaczony']:checked", "#form-sendit").val() == "nie" ) { wynik = false; } return wynik; }}
                }
            });

            setTimeout(function() {
              $('#<?php echo $system; ?>').fadeOut();
            }, 3000);

          });
          </script>  

          <?php
          $adres_firmy  = Funkcje::PrzeksztalcAdres(DANE_ADRES_LINIA_1);
          ?>

          <div class="Sledzenie">

            <form action="integracje/konfiguracja_wysylki_sendit.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" name="form_sendit" id="form-sendit" class="cmxform">
            
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="sendit" />
                
                <table>
                
                  <tr><td colspan="2" class="SledzenieOpis">
                    <div>Sendit.pl to platforma wysyłkowa, dzięki której szybko i wygodnie zamówisz krajowe oraz międzynarodowe usługi kurierskie w atrakcyjnych cenach.Korzystając z usług Sendit.pl masz pewność, że zadbamy o Twoje zamówienie na każdym etapie jego realizacji. Jeżeli pojawi się taka potrzeba, pomożemy także w procesie reklamacji. Z nami wysyłka jest pewna i bezpieczna.</div>
                    <img src="obrazki/logo/logo_sendit.png" alt="" />
                  </td></tr>                    

                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Włącz integrację SendIt:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SENDIT_WLACZONY']['1'], $parametr['INTEGRACJA_SENDIT_WLACZONY']['0'], 'integracja_sendit_wlaczony', $parametr['INTEGRACJA_SENDIT_WLACZONY']['2'], '', $parametr['INTEGRACJA_SENDIT_WLACZONY']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Tryb testowy:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SENDIT_SANBOX']['1'], $parametr['INTEGRACJA_SENDIT_SANBOX']['0'], 'integracja_sendit_sanbox', $parametr['INTEGRACJA_SENDIT_SANBOX']['2'], '', $parametr['INTEGRACJA_SENDIT_SANBOX']['3'] );
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_sendit_api_key">Klucz API:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_sendit_api_key" id="integracja_sendit_api_key" value="'.$parametr['INTEGRACJA_SENDIT_API_KEY']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SENDIT_API_KEY']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_sendit_api_login">Login API:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_sendit_api_login" id="integracja_sendit_api_login" value="'.$parametr['INTEGRACJA_SENDIT_API_LOGIN']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SENDIT_API_LOGIN']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_sendit_api_haslo">Hasło API:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_sendit_api_haslo" id="integracja_sendit_api_haslo" value="'.$parametr['INTEGRACJA_SENDIT_API_HASLO']['0'].'" size="73"  class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SENDIT_API_HASLO']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_sendit_nadawca_nazwa">Nadawca:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_sendit_nadawca_nazwa" name="INTEGRACJA_SENDIT_NADAWCA_NAZWA" value="'.$parametr['INTEGRACJA_SENDIT_NADAWCA_NAZWA']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SENDIT_NADAWCA_NAZWA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_sendit_nadawca_ulica">Adres:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_sendit_nadawca_ulica" name="integracja_sendit_nadawca_ulica" value="'.$parametr['INTEGRACJA_SENDIT_NADAWCA_ULICA']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SENDIT_NADAWCA_ULICA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_sendit_nadawca_kod_pocztowy">Kod pocztowy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_sendit_nadawca_kod_pocztowy" name="integracja_sendit_nadawca_kod_pocztowy" value="'.$parametr['INTEGRACJA_SENDIT_NADAWCA_KOD_POCZTOWY']['0'].'" size="20" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SENDIT_NADAWCA_KOD_POCZTOWY']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_sendit_nadawca_miasto">Miejscowość:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_sendit_nadawca_miasto" name="integracja_sendit_nadawca_miasto" value="'.$parametr['INTEGRACJA_SENDIT_NADAWCA_MIASTO']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SENDIT_NADAWCA_MIASTO']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_sendit_nadawca_kraj">Kraj:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_sendit_nadawca_kraj" name="integracja_sendit_nadawca_kraj" value="'.$parametr['INTEGRACJA_SENDIT_NADAWCA_KRAJ']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SENDIT_NADAWCA_KRAJ']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_sendit_nadawca_telefon">Numer telefonu:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_sendit_nadawca_telefon" name="integracja_sendit_nadawca_telefon" value="'.$parametr['INTEGRACJA_SENDIT_NADAWCA_TELEFON']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SENDIT_NADAWCA_TELEFON']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_sendit_nadawca_email">Adres e-mail:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_sendit_nadawca_email" name="integracja_sendit_nadawca_email" value="'.$parametr['INTEGRACJA_SENDIT_NADAWCA_EMAIL']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SENDIT_NADAWCA_EMAIL']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_sendit_nadawca_kontakt">Osoba kontaktowa:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_sendit_nadawca_kontakt" name="integracja_sendit_nadawca_kontakt" value="'.$parametr['INTEGRACJA_SENDIT_NADAWCA_KONTAKT']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SENDIT_NADAWCA_KONTAKT']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr>
                    <td colspan="2">
                      <div class="przyciski_dolne">
                        <button type="button" class="przyciskNon" onclick="cofnij('konfiguracja_wysylki','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','integracje');">Powrót</button>
                        <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'sendit' ? $wynik : '' ); ?>
                      </div>
                    </td>
                  </tr>
                  
                </table>
                
            </form>

          </div>

        </div>
      </div>
    </div>

    
    <?php
    include('stopka.inc.php');    
    
} ?>
