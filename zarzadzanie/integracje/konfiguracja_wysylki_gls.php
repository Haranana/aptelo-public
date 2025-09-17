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
      $db->update_query('settings' , $pola, " code LIKE 'INTEGRACJA_GLS_%'");	
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
        <div class="naglowek">Edycja danych - Firma kurierska GLS</div>

        <div class="pozycja_edytowana"> 

          <script>
          $(document).ready(function() {
            $("#form-gls").validate({
              rules: {
                integracja_gls_api_key: {required: function() {var wynik = true; if ( $("input[name='integracja_gls_wlaczony']:checked", "#form-gls").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_gls_klient_ck: {required: function() {var wynik = true; if ( $("input[name='integracja_gls_wlaczony']:checked", "#form-gls").val() == "nie" ) { wynik = false; } return wynik; }},
                }
            });

            setTimeout(function() {
              $('#<?php echo $system; ?>').fadeOut();
            }, 3000);

          });
          </script>  

          <div class="Sledzenie">

            <form action="integracje/konfiguracja_wysylki_gls.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" name="form_gls" id="form-gls" class="cmxform"> 
            
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="gls" />
                
                <table>
                
                  <tr><td colspan="2" class="SledzenieOpis">
                    <div>Integracja z firmą kurierską GLS umożliwiająca nadawanie paczek bezpośrednio z poziomu edycji zamówienia w sklepie.</div>
                    <img src="obrazki/logo/logo_gls.png" alt="" />
                  </td></tr>                   

                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Włącz integrację GLS:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_GLS_WLACZONY']['1'], $parametr['INTEGRACJA_GLS_WLACZONY']['0'], 'integracja_gls_wlaczony', $parametr['INTEGRACJA_GLS_WLACZONY']['2'], '', $parametr['INTEGRACJA_GLS_WLACZONY']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Włącz tryb testowy:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_GLS_SANDBOX']['1'], $parametr['INTEGRACJA_GLS_SANDBOX']['0'], 'integracja_gls_sandbox', $parametr['INTEGRACJA_GLS_SANDBOX']['2'], '', $parametr['INTEGRACJA_GLS_SANDBOX']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_gls_login">Login do API:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_gls_login" id="integracja_gls_login" value="'.$parametr['INTEGRACJA_GLS_LOGIN']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_GLS_LOGIN']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required"  for="integracja_gls_password">Hasło:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_gls_password" id="integracja_gls_password" value="'.$parametr['INTEGRACJA_GLS_PASSWORD']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_GLS_PASSWORD']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_gls_senderaddress_name1">Pierwsza część nazwy nadawcy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input class="required" type="text" name="integracja_gls_senderaddress_name1" id="integracja_gls_senderaddress_name1" value="'.$parametr['INTEGRACJA_GLS_SENDERADDRESS_NAME1']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_GLS_SENDERADDRESS_NAME1']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_gls_senderaddress_name2">Druga część nazwy nadawcy:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_gls_senderaddress_name2" id="integracja_gls_senderaddress_name2" value="'.$parametr['INTEGRACJA_GLS_SENDERADDRESS_NAME2']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_GLS_SENDERADDRESS_NAME2']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_gls_senderaddress_name3">Trzecia część nazwy nadawcy:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_gls_senderaddress_name3" id="integracja_gls_senderaddress_name3" value="'.$parametr['INTEGRACJA_GLS_SENDERADDRESS_NAME3']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_GLS_SENDERADDRESS_NAME3']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_gls_senderaddress_zipcode">Kod pocztowy nadawcy:</label>
                    <td>
                      <?php
                      echo '<input class="required" type="text" name="integracja_gls_senderaddress_zipcode" id="integracja_gls_senderaddress_zipcode" value="'.$parametr['INTEGRACJA_GLS_SENDERADDRESS_ZIPCODE']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_GLS_SENDERADDRESS_ZIPCODE']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_gls_senderaddress_city">Miejscowość nadawcy:</label>
                    <td>
                      <?php
                      echo '<input class="required" type="text" name="integracja_gls_senderaddress_city" id="integracja_gls_senderaddress_city" value="'.$parametr['INTEGRACJA_GLS_SENDERADDRESS_CITY']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_GLS_SENDERADDRESS_CITY']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_gls_senderaddress_street">Ulica nadawcy:</label>
                    <td>
                      <?php
                      echo '<input class="required" type="text" name="integracja_gls_senderaddress_street" id="integracja_gls_senderaddress_street" value="'.$parametr['INTEGRACJA_GLS_SENDERADDRESS_STREET']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_GLS_SENDERADDRESS_STREET']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_gls_senderaddress_phone">Telefon kontaktowy:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_gls_senderaddress_phone" id="integracja_gls_senderaddress_phone" value="'.$parametr['INTEGRACJA_GLS_SENDERADDRESS_PHONE']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_GLS_SENDERADDRESS_PHONE']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Potwierdzenie nadania:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_GLS_GETRECEIPT_MODE']['1'], $parametr['INTEGRACJA_GLS_GETRECEIPT_MODE']['0'], 'integracja_gls_getreceipt_mode', $parametr['INTEGRACJA_GLS_GETRECEIPT_MODE']['2'], 'plik PDF, z kodami kreskowymi,plik PDF, skondensowany,plik PDF, skondensowany z opisem potwierdzenia', $parametr['INTEGRACJA_GLS_GETRECEIPT_MODE']['3'] );
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_gls_getlabels_mode">Etykiety:</label>
                    </td>
                    <td>
                    <?php
                    $domyslna = $parametr['INTEGRACJA_GLS_GETLABELS_MODE']['0'];
                    ?>
                    <select name="integracja_gls_getlabels_mode" id="integracja_gls_getlabels_mode">
                        <optgroup label="Papier formatu A4 (pliki PDF)">
                            <option value="one_label_on_a4_lt_pdf" <?php echo ( $domyslna == 'one_label_on_a4_lt_pdf' ? 'selected="selected"' : '' ); ?>>jedna etykieta na A4, lewy, górny narożnik</option>
                            <option value="one_label_on_a4_rt_pdf" <?php echo ( $domyslna == 'one_label_on_a4_rt_pdf' ? 'selected="selected"' : '' ); ?>>jedna etykieta na A4, prawy, górny narożnik</option>
                            <option value="one_label_on_a4_lb_pdf" <?php echo ( $domyslna == 'one_label_on_a4_lb_pdf' ? 'selected="selected"' : '' ); ?>>jedna etykieta na A4, lewy, dolny narożnik</option>
                            <option value="one_label_on_a4_rb_pdf" <?php echo ( $domyslna == 'one_label_on_a4_rb_pdf' ? 'selected="selected"' : '' ); ?>>jedna etykieta na A4, prawy, dolny narożnik</option>
                            <option value="one_label_on_a4_pdf" <?php echo ( $domyslna == 'one_label_on_a4_pdf' ? 'selected="selected"' : '' ); ?>>jedna etykieta na A4</option>
                            <option value="four_labels_on_a4_pdf" <?php echo ( $domyslna == 'four_labels_on_a4_pdf' ? 'selected="selected"' : '' ); ?>>cztery etykiety na A4, pierwsza etykieta drukowana od lewej</option>
                            <option value="four_labels_on_a4_right_pdf" <?php echo ( $domyslna == 'four_labels_on_a4_right_pdf' ? 'selected="selected"' : '' ); ?>>cztery etykiety na A4, pierwsza etykieta drukowana od prawej</option>
                        </optgroup>
                        <optgroup label="Etykiety z rolki (160mm x 100mm)">
                            <option value="roll_160x100_pdf" <?php echo ( $domyslna == 'roll_160x100_pdf' ? 'selected="selected"' : '' ); ?>>plik PDF</option>
                            <option value="roll_160x100_datamax" <?php echo ( $domyslna == 'roll_160x100_datamax' ? 'selected="selected"' : '' ); ?>>plik w języku DPL (na drukarki termiczne)</option>
                            <option value="roll_160x100_zebra" <?php echo ( $domyslna == 'roll_160x100_zebra' ? 'selected="selected"' : '' ); ?>>plik w języku ZPL (na drukarki termiczne)</option>
                            <option value="roll_160x100_zebra_epl" <?php echo ( $domyslna == 'roll_160x100_zebra_epl' ? 'selected="selected"' : '' ); ?>>plik w języku EPL (na drukarki termiczne)</option>
                        </optgroup>
                     </select><em class="TipIkona"><b><?php echo $parametr['INTEGRACJA_GLS_GETLABELS_MODE']['2']; ?></b></em>
                    </td>
                  </tr>
                  

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_gls_default_weight">Domyślna waga paczki:</label>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_gls_default_weight" id="integracja_gls_default_weight" value="'.$parametr['INTEGRACJA_GLS_DEFAULT_WEIGHT']['0'].'" size="20" class="kropkaPusta" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_GLS_DEFAULT_WEIGHT']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr>
                    <td colspan="2">
                      <div class="przyciski_dolne">
                        <button type="button" class="przyciskNon" onclick="cofnij('konfiguracja_wysylki','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','integracje');">Powrót</button>
                        <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'gls' ? $wynik : '' ); ?>
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
