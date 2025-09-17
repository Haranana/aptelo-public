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
      $db->update_query('settings' , $pola, " code LIKE 'INTEGRACJA_DPD_%'");	
      unset($pola);

      reset($_POST);
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
        <div class="naglowek">Edycja danych - Firma kurierska DPD</div>

        <div class="pozycja_edytowana"> 

          <script>
          $(document).ready(function() {
            $("#form-dpd").validate({
              rules: {
                integracja_dpd_api_key: {required: function() {var wynik = true; if ( $("input[name='integracja_dpd_wlaczony']:checked", "#form-dpd").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_dpd_klient_ck: {required: function() {var wynik = true; if ( $("input[name='integracja_dpd_wlaczony']:checked", "#form-dpd").val() == "nie" ) { wynik = false; } return wynik; }},
                }
            });

            setTimeout(function() {
              $('#<?php echo $system; ?>').fadeOut();
            }, 3000);

          });
          </script>  

          <div class="Sledzenie">

            <form action="integracje/konfiguracja_wysylki_dpd.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" name="form_dpd" id="form-dpd" class="cmxform"> 
            
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="dpd" />
                
                <table>
                
                  <tr><td colspan="2" class="SledzenieOpis">
                    <div>Integracja z firmą kurierską DPD umożliwiająca nadawanie paczek bezpośrednio z poziomu edycji zamówienia w sklepie.</div>
                    <img src="obrazki/logo/logo_dpd.png" alt="" />
                  </td></tr>                   

                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Włącz integrację DPD:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_DPD_WLACZONY']['1'], $parametr['INTEGRACJA_DPD_WLACZONY']['0'], 'integracja_dpd_wlaczony', $parametr['INTEGRACJA_DPD_WLACZONY']['2'], '', $parametr['INTEGRACJA_DPD_WLACZONY']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Włącz tryb testowy:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_DPD_SANDBOX']['1'], $parametr['INTEGRACJA_DPD_SANDBOX']['0'], 'integracja_dpd_sandbox', $parametr['INTEGRACJA_DPD_SANDBOX']['2'], '', $parametr['INTEGRACJA_DPD_SANDBOX']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Powiadomienia w komunikatach:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_DPD_POWIADOMIENIA']['1'], $parametr['INTEGRACJA_DPD_POWIADOMIENIA']['0'], 'integracja_dpd_powiadomienia', $parametr['INTEGRACJA_DPD_POWIADOMIENIA']['2'], '', $parametr['INTEGRACJA_DPD_POWIADOMIENIA']['3'] );
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Płatnik za przesyłkę:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_DPD_PLATNIK']['1'], $parametr['INTEGRACJA_DPD_PLATNIK']['0'], 'integracja_dpd_platnik', $parametr['INTEGRACJA_DPD_PLATNIK']['2'], '', $parametr['INTEGRACJA_DPD_PLATNIK']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Ubezpieczenie przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_DPD_UBEZPIECZENIE']['1'], $parametr['INTEGRACJA_DPD_UBEZPIECZENIE']['0'], 'integracja_dpd_ubezpieczenie', $parametr['INTEGRACJA_DPD_UBEZPIECZENIE']['2'], '', $parametr['INTEGRACJA_DPD_UBEZPIECZENIE']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_dpd_login">Login do API:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dpd_login" id="integracja_dpd_login" value="'.$parametr['INTEGRACJA_DPD_LOGIN']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DPD_LOGIN']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dpd_password">Hasło:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dpd_password" id="integracja_dpd_password" value="'.$parametr['INTEGRACJA_DPD_PASSWORD']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DPD_PASSWORD']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dpd_fid">Master FID:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dpd_fid" id="integracja_dpd_fid" value="'.$parametr['INTEGRACJA_DPD_FID']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DPD_FID']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dpd_nadawca_nazwa">Firma:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dpd_nadawca_nazwa" id="integracja_dpd_nadawca_nazwa" value="'.$parametr['INTEGRACJA_DPD_NADAWCA_NAZWA']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DPD_NADAWCA_NAZWA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dpd_nadawca_imie_nazwisko">Nazwisko i imię:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dpd_nadawca_imie_nazwisko" id="integracja_dpd_nadawca_imie_nazwisko" value="'.$parametr['INTEGRACJA_DPD_NADAWCA_IMIE_NAZWISKO']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DPD_NADAWCA_IMIE_NAZWISKO']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dpd_nadawca_ulica">Ulica i numer domu:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dpd_nadawca_ulica" id="integracja_dpd_nadawca_ulica" value="'.$parametr['INTEGRACJA_DPD_NADAWCA_ULICA']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DPD_NADAWCA_ULICA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dpd_nadawca_kod_pocztowy">Kod pocztowy:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dpd_nadawca_kod_pocztowy" id="integracja_dpd_nadawca_kod_pocztowy" value="'.$parametr['INTEGRACJA_DPD_NADAWCA_KOD_POCZTOWY']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DPD_NADAWCA_KOD_POCZTOWY']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dpd_nadawca_miasto">Miejscowość:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dpd_nadawca_miasto" id="integracja_dpd_nadawca_miasto" value="'.$parametr['INTEGRACJA_DPD_NADAWCA_MIASTO']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DPD_NADAWCA_MIASTO']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dpd_nadawca_email">Adres e-mail:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dpd_nadawca_email" id="integracja_dpd_nadawca_email" value="'.$parametr['INTEGRACJA_DPD_NADAWCA_EMAIL']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DPD_NADAWCA_EMAIL']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dpd_nadawca_telefon">Telefon kontaktowy:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dpd_nadawca_telefon" id="integracja_dpd_nadawca_telefon" value="'.$parametr['INTEGRACJA_DPD_NADAWCA_TELEFON']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DPD_NADAWCA_TELEFON']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dpd_drugi_fid">FID dodatkowy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dpd_drugi_fid" id="integracja_dpd_drugi_fid" value="'.$parametr['INTEGRACJA_DPD_DRUGI_FID']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DPD_DRUGI_FID']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dpd_drugi_nadawca_ulica">Ulica i numer domu:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dpd_drugi_nadawca_ulica" id="integracja_dpd_drugi_nadawca_ulica" value="'.$parametr['INTEGRACJA_DPD_DRUGI_NADAWCA_ULICA']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DPD_DRUGI_NADAWCA_ULICA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dpd_drugi_nadawca_kod_pocztowy">Kod pocztowy:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dpd_drugi_nadawca_kod_pocztowy" id="integracja_dpd_drugi_nadawca_kod_pocztowy" value="'.$parametr['INTEGRACJA_DPD_DRUGI_NADAWCA_KOD_POCZTOWY']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DPD_DRUGI_NADAWCA_KOD_POCZTOWY']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dpd_drugi_nadawca_miasto">Miejscowość:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dpd_drugi_nadawca_miasto" id="integracja_dpd_drugi_nadawca_miasto" value="'.$parametr['INTEGRACJA_DPD_DRUGI_NADAWCA_MIASTO']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DPD_DRUGI_NADAWCA_MIASTO']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>


                  
                  
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Format wydruków:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_DPD_FORMAT_WYDRUKU']['1'], $parametr['INTEGRACJA_DPD_FORMAT_WYDRUKU']['0'], 'integracja_dpd_format_wydruku', $parametr['INTEGRACJA_DPD_FORMAT_WYDRUKU']['2'], '', $parametr['INTEGRACJA_DPD_FORMAT_WYDRUKU']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Format generowanych plików:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_DPD_FORMAT_PLIKU']['1'], $parametr['INTEGRACJA_DPD_FORMAT_PLIKU']['0'], 'integracja_dpd_format_pliku', $parametr['INTEGRACJA_DPD_FORMAT_PLIKU']['2'], '', $parametr['INTEGRACJA_DPD_FORMAT_PLIKU']['3'] );
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dpd_wymiary_dlugosc">Preferowane wymiary przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo 'długość: <input type="text" name="integracja_dpd_wymiary_dlugosc" id="integracja_dpd_wymiary_dlugosc" value="'.$parametr['INTEGRACJA_DPD_WYMIARY_DLUGOSC']['0'].'" size="12" />';
                      echo ' &nbsp; szerokość: <input type="text" name="integracja_dpd_wymiary_szerokosc" value="'.$parametr['INTEGRACJA_DPD_WYMIARY_SZEROKOSC']['0'].'" size="12" />';
                      echo ' &nbsp; wysokość: <input type="text" name="integracja_dpd_wymiary_wysokosc" value="'.$parametr['INTEGRACJA_DPD_WYMIARY_WYSOKOSC']['0'].'" size="12" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dpd_zawartosc">Domyślna zawartość przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_dpd_zawartosc" name="INTEGRACJA_DPD_ZAWARTOSC" value="'.$parametr['INTEGRACJA_DPD_ZAWARTOSC']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DPD_ZAWARTOSC']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr>
                    <td colspan="2">
                      <div class="przyciski_dolne">
                       <button type="button" class="przyciskNon" onclick="cofnij('konfiguracja_wysylki','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','integracje');">Powrót</button>
                       <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'dpd' ? $wynik : '' ); ?>
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
