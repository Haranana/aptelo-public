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
        <div class="naglowek">Edycja danych - Firma Kurier InPost</div>

        <div class="pozycja_edytowana"> 

          <script>
          $(document).ready(function() {
            $("#form-inpost").validate({
              rules: {
                integracja_kurier_inpost_login: {required: function() {var wynik = true; if ( $("input[name='integracja_kurier_inpost_wlaczony']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_kurier_inpost_login_haslo: {required: function() {var wynik = true; if ( $("input[name='integracja_kurier_inpost_wlaczony']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_kurier_inpost_nadawca_nazwa: {required: function() {var wynik = true; if ( $("input[name='integracja_kurier_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_kurier_inpost_nadawca_ulica: {required: function() {var wynik = true; if ( $("input[name='integracja_kurier_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_kurier_inpost_nadawca_dom: {required: function() {var wynik = true; if ( $("input[name='integracja_kurier_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_kurier_inpost_nadawca_kod_pocztowy: {required: function() {var wynik = true; if ( $("input[name='integracja_kurier_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_kurier_inpost_nadawca_miasto: {required: function() {var wynik = true; if ( $("input[name='integracja_kurier_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_kurier_inpost_nadawca_email: {required: function() {var wynik = true; if ( $("input[name='integracja_kurier_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_kurier_inpost_nadawca_telefon: {required: function() {var wynik = true; if ( $("input[name='integracja_kurier_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }}
                }
            });

            setTimeout(function() {
              $('#<?php echo $system; ?>').fadeOut();
            }, 3000);

          });
          </script>  

          <div class="Sledzenie">

                <form action="integracje/konfiguracja_wysylki_kurier_inpost.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" name="form_inpost" id="form-inpost" class="cmxform">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    <input type="hidden" name="system" value="inpost" />
                    
                    <table>
                    
                      <tr><td colspan="2" class="SledzenieOpis">
                        <div>Wybierając usługi kurierskie InPost – wysyłasz paczki w dowolne miejsce na terenie kraju oraz do jednego z 18 krajów Europy. Wszystkie usługi w serwisie krajowym i międzynarodowym realizowane są z dostawą do drzwi odbiorcy lub do jednego z naszych Paczkomatów.</div>
                        <img src="obrazki/logo/logo_kurier_inpost.png" alt="" />
                      </td></tr>                    
                    
                      <tr class="SledzeniePozycja">
                        <td>
                          <label>Włącz integrację Kurier InPost:</label>
                        </td>
                        <td>
                          <?php
                          echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KURIER_INPOST_WLACZONY']['1'], $parametr['INTEGRACJA_KURIER_INPOST_WLACZONY']['0'], 'integracja_kurier_inpost_wlaczony', $parametr['INTEGRACJA_KURIER_INPOST_WLACZONY']['2'], '', $parametr['INTEGRACJA_KURIER_INPOST_WLACZONY']['3'] );
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label>Tryb testowy:</label>
                        </td>
                        <td>
                          <?php
                          echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KURIER_INPOST_SANDBOX']['1'], $parametr['INTEGRACJA_KURIER_INPOST_SANDBOX']['0'], 'integracja_kurier_inpost_sandbox', $parametr['INTEGRACJA_KURIER_INPOST_SANDBOX']['2'], '', $parametr['INTEGRACJA_KURIER_INPOST_SANDBOX']['3'] );
                          ?>
                        </td>
                      </tr>

                      <tr class="SledzeniePozycja">
                        <td>
                          <label class="required" for="integracja_kurier_inpost_login">Login:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" name="integracja_kurier_inpost_login" id="integracja_kurier_inpost_login" value="'.$parametr['INTEGRACJA_KURIER_INPOST_LOGIN']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_LOGIN']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label class="required" for="integracja_kurier_inpost_login_haslo">Hasło:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="password" name="integracja_kurier_inpost_login_haslo" id="integracja_kurier_inpost_login_haslo" value="'.$parametr['INTEGRACJA_KURIER_INPOST_LOGIN_HASLO']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_LOGIN_HASLO']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_wymiary_dlugosc">Domyślny rozmiar paczki:</label>
                        </td>
                        <td>
                          <?php
                          echo 'długość: <input type="text" name="integracja_kurier_inpost_wymiary_dlugosc" id="integracja_kurier_inpost_wymiary_dlugosc" value="'.$parametr['INTEGRACJA_KURIER_INPOST_WYMIARY_DLUGOSC']['0'].'" size="12" />';
                          echo ' &nbsp; szerokość: <input type="text" name="integracja_kurier_inpost_wymiary_szerokosc" value="'.$parametr['INTEGRACJA_KURIER_INPOST_WYMIARY_SZEROKOSC']['0'].'" size="12" />';
                          echo ' &nbsp; wysokość: <input type="text" name="integracja_kurier_inpost_wymiary_wysokosc" value="'.$parametr['INTEGRACJA_KURIER_INPOST_WYMIARY_WYSOKOSC']['0'].'" size="12" />';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_nadawca_nazwa">Domyślna zawartość przesyłki:</label>
                        </td>
                        <td>
                          <?php
                          echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KURIER_INPOST_ZAWARTOSC']['1'], $parametr['INTEGRACJA_KURIER_INPOST_ZAWARTOSC']['0'], 'integracja_kurier_inpost_zawartosc', $parametr['INTEGRACJA_KURIER_INPOST_ZAWARTOSC']['2'], '', $parametr['INTEGRACJA_KURIER_INPOST_ZAWARTOSC']['3'], '', '', 'id="integracja_kurier_inpost_zawartosc"' );
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_nadawca_nazwa">Nadawca:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_kurier_inpost_nadawca_nazwa" name="integracja_kurier_inpost_nadawca_nazwa" value="'.$parametr['INTEGRACJA_KURIER_INPOST_NADAWCA_NAZWA']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_NADAWCA_NAZWA']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_nadawca_ulica">Ulica i numer domu:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_kurier_inpost_nadawca_ulica" name="integracja_kurier_inpost_nadawca_ulica" value="'.$parametr['INTEGRACJA_KURIER_INPOST_NADAWCA_ULICA']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_NADAWCA_ULICA']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_nadawca_kod_pocztowy">Kod pocztowy:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_kurier_inpost_nadawca_kod_pocztowy" name="integracja_kurier_inpost_nadawca_kod_pocztowy" value="'.$parametr['INTEGRACJA_KURIER_INPOST_NADAWCA_KOD_POCZTOWY']['0'].'" size="20" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_NADAWCA_KOD_POCZTOWY']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_nadawca_miasto">Miejscowość:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_kurier_inpost_nadawca_miasto" name="integracja_kurier_inpost_nadawca_miasto" value="'.$parametr['INTEGRACJA_KURIER_INPOST_NADAWCA_MIASTO']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_NADAWCA_MIASTO']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_nadawca_telefon">Numer telefonu:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_kurier_inpost_nadawca_telefon" name="integracja_kurier_inpost_nadawca_telefon" value="'.$parametr['INTEGRACJA_KURIER_INPOST_NADAWCA_TELEFON']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_NADAWCA_TELEFON']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_nadawca_email">Adres e-mail:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_kurier_inpost_nadawca_email" name="integracja_kurier_inpost_nadawca_email" value="'.$parametr['INTEGRACJA_KURIER_INPOST_NADAWCA_EMAIL']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_NADAWCA_EMAIL']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_nadawca_imie_nazwisko">Imię i nazwisko:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_kurier_inpost_nadawca_imie_nazwisko" name="integracja_kurier_inpost_nadawca_imie_nazwisko" value="'.$parametr['INTEGRACJA_KURIER_INPOST_NADAWCA_IMIE_NAZWISKO']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_NADAWCA_IMIE_NAZWISKO']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_nadawca_imie_nazwisko">Numer konta bankowego:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_kurier_inpost_numer_konta" name="integracja_kurier_inpost_numer_konta" value="'.$parametr['INTEGRACJA_KURIER_INPOST_NUMER_KONTA']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_NUMER_KONTA']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_nadawca_nazwa">Czas odbioru przesyłki:</label>
                        </td>
                        <td>
                          <?php
                          echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KURIER_INPOST_MAX_ODBIOR']['1'], $parametr['INTEGRACJA_KURIER_INPOST_MAX_ODBIOR']['0'], 'integracja_kurier_inpost_max_odbior', $parametr['INTEGRACJA_KURIER_INPOST_MAX_ODBIOR']['2'], '', $parametr['INTEGRACJA_KURIER_INPOST_MAX_ODBIOR']['3'], '', '', 'id="integracja_kurier_inpost_max_odbior"' );
                          echo '<em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_MAX_ODBIOR']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>

                      <tr>
                        <td colspan="2">
                          <div class="przyciski_dolne">
                            <button type="button" class="przyciskNon" onclick="cofnij('konfiguracja_wysylki','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','integracje');">Powrót</button>
                            <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'inpost' ? $wynik : '' ); ?>
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
