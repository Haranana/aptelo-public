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
        <div class="naglowek">Edycja danych - Firma DHL</div>

        <div class="pozycja_edytowana"> 
        
          <script type="text/javascript" src="javascript/jquery.populate.js"></script>

          <script>
          $(document).ready(function() {

            setTimeout(function() {
              $('#<?php echo $system; ?>').fadeOut();
            }, 3000);

            $("#FormDhl").validate({
              rules: {
                integracja_dhl_login: {required: function() {var wynik = true; if ( $("input[name='integracja_dhl_wlaczony']:checked", "#FormDhl").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_dhl_haslo: {required: function() {var wynik = true; if ( $("input[name='integracja_dhl_wlaczony']:checked", "#FormDhl").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_dhl_nadawca_kod_pocztowy: { number: true }
              },
              messages: {
                integracja_dhl_nadawca_kod_pocztowy: { number: 'Prosze wpisać tylko cyfry' }
              }
            });

          });
          </script>  

          <div class="Sledzenie">

            <form action="integracje/konfiguracja_wysylki_dhl.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" name="form_dhl" id="FormDhl" class="cmxform">
            
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="dhl" />
                
                <table>
                
                  <tr><td colspan="2" class="SledzenieOpis">
                    <div>DHL24 WebAPI to usługa sieciowa, która umożliwia wymianę informacji między serwisem DHL24 a zewnętrznym oprogramowaniem naszych klientów. Rozwiązanie to umożliwia integrację własnego oprogramowania z mechanizmami serwisu DHL24. W ramach DHL24 WebAPI udostępniamy szereg metod, które odpowiadają najważniejszym funkcjom aplikacji DHL24, w tym tworzenie przesyłek i zamawianie kuriera.</div>
                    <img src="obrazki/logo/logo_dhl.png" alt="" />
                  </td></tr>                    

                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Włącz integrację z DHL:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_DHL_WLACZONY']['1'], $parametr['INTEGRACJA_DHL_WLACZONY']['0'], 'integracja_dhl_wlaczony', $parametr['INTEGRACJA_DHL_WLACZONY']['2'], '', $parametr['INTEGRACJA_DHL_WLACZONY']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Włącz tryb testowy:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_DHL_SANDBOX']['1'], $parametr['INTEGRACJA_DHL_SANDBOX']['0'], 'integracja_dhl_sandbox', $parametr['INTEGRACJA_DHL_SANDBOX']['2'], '', $parametr['INTEGRACJA_DHL_SANDBOX']['3'] );
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_dhl_login">Login do API:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dhl_login" id="integracja_dhl_login" value="'.$parametr['INTEGRACJA_DHL_LOGIN']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DHL_LOGIN']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_dhl_haslo">Hasło do API:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dhl_haslo" id="integracja_dhl_haslo" value="'.$parametr['INTEGRACJA_DHL_HASLO']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DHL_HASLO']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dhl_sap">Numer SAP:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dhl_sap" id="integracja_dhl_sap" value="'.$parametr['INTEGRACJA_DHL_SAP']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DHL_SAP']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dhl_platnik">Płatność za usługę:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_DHL_PLATNIK']['1'], $parametr['INTEGRACJA_DHL_PLATNIK']['0'], 'integracja_dhl_platnik', $parametr['INTEGRACJA_DHL_PLATNIK']['2'], '', $parametr['INTEGRACJA_DHL_PLATNIK']['3'], '', '', 'id="integracja_dhl_platnik"' );
                      echo '<em class="TipIkona"><b>'. $parametr['INTEGRACJA_DHL_PLATNIK']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_dhl_wydruk_format">Domyślny typ plików:</label>
                        </td>
                        <td>
                        <?php
                        $domyslna = $parametr['INTEGRACJA_DHL_WYDRUK_FORMAT']['0'];
                        ?>
                        <select name="integracja_dhl_wydruk_format" id="integracja_dhl_wydruk_format">
                            <option value="BLP" <?php echo ( $domyslna == 'BLP' ? 'selected="selected"' : '' ); ?>>PDF</option>
                            <option value="ZBLP" <?php echo ( $domyslna == 'ZBLP' ? 'selected="selected"' : '' ); ?>>Zebra</option>
                         </select><em class="TipIkona"><b><?php echo $parametr['INTEGRACJA_DHL_WYDRUK_FORMAT']['2']; ?></b></em>
                        </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dhl_wymiary_dlugosc">Preferowane wymiary przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo 'długość: <input type="text" name="integracja_dhl_wymiary_dlugosc" id="integracja_dhl_wymiary_dlugosc" value="'.$parametr['INTEGRACJA_DHL_WYMIARY_DLUGOSC']['0'].'" size="12" />';
                      echo ' &nbsp; szerokość: <input type="text" name="integracja_dhl_wymiary_szerokosc" id="integracja_dhl_wymiary_szerokosc" value="'.$parametr['INTEGRACJA_DHL_WYMIARY_SZEROKOSC']['0'].'" size="12" />';
                      echo ' &nbsp; wysokość: <input type="text" name="integracja_dhl_wymiary_wysokosc" id="integracja_dhl_wymiary_wysokosc" value="'.$parametr['INTEGRACJA_DHL_WYMIARY_WYSOKOSC']['0'].'" size="12" />';
                      echo ' &nbsp; waga: <input type="text" name="integracja_dhl_wymiary_waga" id="integracja_dhl_wymiary_waga" value="'.$parametr['INTEGRACJA_DHL_WYMIARY_WAGA']['0'].'" size="12" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dhl_zawartosc">Domyślna zawartość przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_DHL_ZAWARTOSC']['1'], $parametr['INTEGRACJA_DHL_ZAWARTOSC']['0'], 'integracja_dhl_zawartosc', $parametr['INTEGRACJA_DHL_ZAWARTOSC']['2'], '', $parametr['INTEGRACJA_DHL_ZAWARTOSC']['3'], '', '', 'id="integracja_dhl_zawartosc"' );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_dhl_nadawca_firma">Nazwa firmy lub imię i nazwisko:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_dhl_nadawca_firma" name="integracja_dhl_nadawca_firma" value="'.$parametr['INTEGRACJA_DHL_NADAWCA_FIRMA']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DHL_NADAWCA_FIRMA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_dhl_nadawca_ulica">Ulica:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_dhl_nadawca_ulica" name="integracja_dhl_nadawca_ulica" value="'.$parametr['INTEGRACJA_DHL_NADAWCA_ULICA']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DHL_NADAWCA_ULICA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dhl_nadawca_ulica_dom">Numer domu:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_dhl_nadawca_ulica_dom" name="integracja_dhl_nadawca_ulica_dom" value="'.$parametr['INTEGRACJA_DHL_NADAWCA_ULICA_DOM']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DHL_NADAWCA_ULICA_DOM']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dhl_nadawca_ulica_mieszkanie">Numer mieszkania:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_dhl_nadawca_ulica_mieszkanie" name="integracja_dhl_nadawca_ulica_mieszkanie" value="'.$parametr['INTEGRACJA_DHL_NADAWCA_ULICA_MIESZKANIE']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DHL_NADAWCA_ULICA_MIESZKANIE']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_dhl_nadawca_kod_pocztowy">Kod pocztowy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_dhl_nadawca_kod_pocztowy" name="integracja_dhl_nadawca_kod_pocztowy" value="'.$parametr['INTEGRACJA_DHL_NADAWCA_KOD_POCZTOWY']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DHL_NADAWCA_KOD_POCZTOWY']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_dhl_nadawca_miasto">Miejscowość:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_dhl_nadawca_miasto" name="integracja_dhl_nadawca_miasto" value="'.$parametr['INTEGRACJA_DHL_NADAWCA_MIASTO']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DHL_NADAWCA_MIASTO']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dhl_nadawca_telefon">Numer telefonu nadawcy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dhl_nadawca_telefon" id="integracja_dhl_nadawca_telefon" value="'.$parametr['INTEGRACJA_DHL_NADAWCA_TELEFON']['0'].'" size="50" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DHL_NADAWCA_TELEFON']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dhl_nadawca_email">Adres e-mail nadawcy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dhl_nadawca_email" id="integracja_dhl_nadawca_email" value="'.$parametr['INTEGRACJA_DHL_NADAWCA_EMAIL']['0'].'" size="50" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DHL_NADAWCA_EMAIL']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_dhl_osoba_kontaktowa">Osoba kontaktowa:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dhl_osoba_kontaktowa" id="integracja_dhl_osoba_kontaktowa" value="'.$parametr['INTEGRACJA_DHL_OSOBA_KONTAKTOWA']['0'].'" size="50" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DHL_OSOBA_KONTAKTOWA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                   <tr>
                    <td colspan="2">
                      <div class="przyciski_dolne">
                        <button type="button" class="przyciskNon" onclick="cofnij('konfiguracja_wysylki','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','integracje');">Powrót</button>
                        <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'dhl' ? $wynik : '' ); ?>
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
