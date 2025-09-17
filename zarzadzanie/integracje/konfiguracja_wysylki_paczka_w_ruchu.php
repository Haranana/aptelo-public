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

      $pola = array(
              array('value','')
      );
      $db->update_query('settings' , $pola, " code LIKE 'INTEGRACJA_PACZKARUCH_%'");	
      unset($pola);

      foreach ( $_POST as $key => $value ) {
        if ( $key != 'akcja' ) {
          if ( is_array($value) ) {
              $wartosc = implode(';', (array)$value);
          } else {
              $wartosc = $value;
          }
          $pola = array(
                  array('value',$wartosc)
          );
          $db->update_query('settings' , $pola, " code = '".strtoupper((string)$key)."'");	
          unset($pola,$wartosc);
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
        <div class="naglowek">Edycja danych - ORLEN Paczka</div>

        <div class="pozycja_edytowana"> 

          <script>
          $(document).ready(function() {
            $("#form-paczkaruch").validate({
              rules: {
                integracja_paczkaruch_id: {required: function() {var wynik = true; if ( $("input[name='integracja_paczkaruch_wlaczony']:checked", "#form-paczkaruch").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_paczkaruch_key: {required: function() {var wynik = true; if ( $("input[name='integracja_paczkaruch_wlaczony']:checked", "#form-paczkaruch").val() == "nie" ) { wynik = false; } return wynik; }},
                }
            });

            setTimeout(function() {
              $('#<?php echo $system; ?>').fadeOut();
            }, 3000);

          });
          </script>  

          <div class="Sledzenie">

            <form action="integracje/konfiguracja_wysylki_paczka_w_ruchu.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" name="form_paczkaruch" id="form-paczkaruch" class="cmxform"> 
            
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="paczkaruch" />
                
                <table>
                
                  <tr><td colspan="2" class="SledzenieOpis">
                    <div>Integracja ORLEN Paczka umożliwiająca nadawanie paczek bezpośrednio z poziomu edycji zamówienia w sklepie.</div>
                    <img src="obrazki/logo/logo_paczkaruch.png" alt="" />
                  </td></tr>                   

                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Włącz integrację ORLEN Paczka:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_PACZKARUCH_WLACZONY']['1'], $parametr['INTEGRACJA_PACZKARUCH_WLACZONY']['0'], 'integracja_paczkaruch_wlaczony', $parametr['INTEGRACJA_PACZKARUCH_WLACZONY']['2'], '', $parametr['INTEGRACJA_PACZKARUCH_WLACZONY']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Włącz tryb testowy:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_PACZKARUCH_SANDBOX']['1'], $parametr['INTEGRACJA_PACZKARUCH_SANDBOX']['0'], 'integracja_paczkaruch_sandbox', $parametr['INTEGRACJA_PACZKARUCH_SANDBOX']['2'], '', $parametr['INTEGRACJA_PACZKARUCH_SANDBOX']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_paczkaruch_id">Identyfikator Klienta:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_paczkaruch_id" id="integracja_paczkaruch_id" value="'.$parametr['INTEGRACJA_PACZKARUCH_ID']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_PACZKARUCH_ID']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_paczkaruch_key">Hasło Klienta:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_paczkaruch_key" id="integracja_paczkaruch_key" value="'.$parametr['INTEGRACJA_PACZKARUCH_KEY']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_PACZKARUCH_KEY']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_paczkaruch_nadawca_nazwa">Firma nadawcy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_paczkaruch_nadawca_nazwa" id="integracja_paczkaruch_nadawca_nazwa" value="'.$parametr['INTEGRACJA_PACZKARUCH_NADAWCA_NAZWA']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_PACZKARUCH_NADAWCA_NAZWA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_PACZKARUCH_nadawca_imie">Imię nadawcy:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_PACZKARUCH_nadawca_imie" id="integracja_PACZKARUCH_nadawca_imie" value="'.$parametr['INTEGRACJA_PACZKARUCH_NADAWCA_IMIE']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_PACZKARUCH_NADAWCA_IMIE']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_paczkaruch_nadawca_nazwisko">Nazwisko nadawcy:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_paczkaruch_nadawca_nazwisko" id="integracja_paczkaruch_nadawca_nazwisko" value="'.$parametr['INTEGRACJA_PACZKARUCH_NADAWCA_NAZWISKO']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_PACZKARUCH_NADAWCA_NAZWISKO']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_paczkaruch_nadawca_ulica">Ulica nadawcy:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_paczkaruch_nadawca_ulica" id="integracja_paczkaruch_nadawca_ulica" value="'.$parametr['INTEGRACJA_PACZKARUCH_NADAWCA_ULICA']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_PACZKARUCH_NADAWCA_ULICA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_paczkaruch_nadawca_dom">Numer domu nadawcy:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_paczkaruch_nadawca_dom" id="integracja_paczkaruch_nadawca_dom" value="'.$parametr['INTEGRACJA_PACZKARUCH_NADAWCA_DOM']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_PACZKARUCH_NADAWCA_DOM']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_paczkaruch_nadawca_lokal">Numer mieszkania nadawcy:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_paczkaruch_nadawca_lokal" id="integracja_paczkaruch_nadawca_lokal" value="'.$parametr['INTEGRACJA_PACZKARUCH_NADAWCA_LOKAL']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_PACZKARUCH_NADAWCA_LOKAL']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_paczkaruch_nadawca_kod_pocztowy">Kod pocztowy:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_paczkaruch_nadawca_kod_pocztowy" id="integracja_paczkaruch_nadawca_kod_pocztowy" value="'.$parametr['INTEGRACJA_PACZKARUCH_NADAWCA_KOD_POCZTOWY']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_PACZKARUCH_NADAWCA_KOD_POCZTOWY']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_paczkaruch_nadawca_miasto">Miejscowość:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_paczkaruch_nadawca_miasto" id="integracja_paczkaruch_nadawca_miasto" value="'.$parametr['INTEGRACJA_PACZKARUCH_NADAWCA_MIASTO']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_PACZKARUCH_NADAWCA_MIASTO']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_paczkaruch_nadawca_email">E-mail nadawcy:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_paczkaruch_nadawca_email" id="integracja_paczkaruch_nadawca_email" value="'.$parametr['INTEGRACJA_PACZKARUCH_NADAWCA_EMAIL']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_PACZKARUCH_NADAWCA_EMAIL']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_paczkaruch_nadawca_telefon">Telefon kontaktowy:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_paczkaruch_nadawca_telefon" id="integracja_paczkaruch_nadawca_telefon" value="'.$parametr['INTEGRACJA_PACZKARUCH_NADAWCA_TELEFON']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_PACZKARUCH_NADAWCA_TELEFON']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr>
                    <td colspan="2">
                      <div class="przyciski_dolne">
                        <button type="button" class="przyciskNon" onclick="cofnij('konfiguracja_wysylki','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','integracje');">Powrót</button>
                        <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'paczkaruch' ? $wynik : '' ); ?>
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
