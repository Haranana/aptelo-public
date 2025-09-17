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
        <div class="naglowek">Edycja danych - Firma KurJerzy.pl</div>

        <div class="pozycja_edytowana"> 

          <script>
          $(document).ready(function() {
            $("#form-kurjerzy").validate({
              rules: {
                integracja_kurjerzy_api_key: {required: function() {var wynik = true; if ( $("input[name='integracja_kurjerzy_wlaczony']:checked", "#form-kurjerzy").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_kurjerzy_api_pin: {required: function() {var wynik = true; if ( $("input[name='integracja_kurjerzy_wlaczony']:checked", "#form-kurjerzy").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_kurjerzy_wymiary_dlugosc: { digits: true },
                integracja_kurjerzy_wymiary_szerokosc: { digits: true },
                integracja_kurjerzy_wymiary_wysokosc: { digits: true }
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

            <form action="integracje/konfiguracja_wysylki_kurjerzy.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" name="form_kurjerzy" id="form-kurjerzy" class="cmxform">
            
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="kurjerzy" />
                
                <table>
                
                  <tr><td colspan="2" class="SledzenieOpis">
                    <div>Serwis KurJerzy.pl to pośrednik między Klientami a firmami kurierskimi. Oferujemy rozwiązania szybkie, bezpieczne oraz konkurencyjne cenowo. Współpracujemy z powszechnie uznanymi na rynku przesyłek kurierskich firmami: DHL, UPS oraz FedEx. KurJerzy.pl to tani kurier zapewniający: maksymalnie uproszczony proces składania zamówień, wiele możliwości bezpiecznej i szybkiej zapłaty za usługę, pomoc konsultanta oraz odpowiedzialność firmy w momencie wystąpienia jakichkolwiek problemów z przesyłką kurierską. Dodatkowo, Serwis oferuje takie mechanizmy, jak np.: Prepaid, zniżki dla sklepów, wyszukiwarka kodów pocztowych, możliwość śledzenia przesyłki kurierskiej oraz wiele innych.</div>
                    <img src="obrazki/logo/logo_kurjerzy.png" alt="" />
                  </td></tr>                    

                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Włącz integrację KurJerzy.pl:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KURJERZY_WLACZONY']['1'], $parametr['INTEGRACJA_KURJERZY_WLACZONY']['0'], 'integracja_kurjerzy_wlaczony', $parametr['INTEGRACJA_KURJERZY_WLACZONY']['2'], '', $parametr['INTEGRACJA_KURJERZY_WLACZONY']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_kurjerzy_api_key">Klucz API:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input class="required" type="text" name="integracja_kurjerzy_api_key" id="integracja_kurjerzy_api_key" value="'.$parametr['INTEGRACJA_KURJERZY_API_KEY']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURJERZY_API_KEY']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_kurjerzy_api_pin">PIN API:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input class="required" type="text" name="integracja_kurjerzy_api_pin" id="integracja_kurjerzy_api_pin" value="'.$parametr['INTEGRACJA_KURJERZY_API_PIN']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURJERZY_API_PIN']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_kurjerzy_wymiary_dlugosc">Preferowane wymiary przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo 'długość: <input type="text" name="integracja_kurjerzy_wymiary_dlugosc" id="integracja_kurjerzy_wymiary_dlugosc" value="'.$parametr['INTEGRACJA_KURJERZY_WYMIARY_DLUGOSC']['0'].'" size="12" class="required" />';
                      echo ' &nbsp; szerokość: <input type="text" name="integracja_kurjerzy_wymiary_szerokosc" id="integracja_kurjerzy_wymiary_szerokosc" value="'.$parametr['INTEGRACJA_KURJERZY_WYMIARY_SZEROKOSC']['0'].'" size="12" class="required" />';
                      echo ' &nbsp; wysokość: <input type="text" name="integracja_kurjerzy_wymiary_wysokosc" id="integracja_kurjerzy_wymiary_wysokosc" value="'.$parametr['INTEGRACJA_KURJERZY_WYMIARY_WYSOKOSC']['0'].'" size="12" class="required" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_kurjerzy_zawartosc">Preferowana zawartość przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_kurjerzy_zawartosc" id="integracja_kurjerzy_zawartosc" value="'.$parametr['INTEGRACJA_KURJERZY_ZAWARTOSC']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURJERZY_ZAWARTOSC']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_kurjerzy_nadawca_nazwa">Nadawca:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_kurjerzy_nadawca_nazwa" name="integracja_kurjerzy_nadawca_nazwa" value="'.$parametr['INTEGRACJA_KURJERZY_NADAWCA_NAZWA']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURJERZY_NADAWCA_NAZWA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_kurjerzy_nadawca_ulica">Ulica:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_kurjerzy_nadawca_ulica" name="integracja_kurjerzy_nadawca_ulica" value="'.$parametr['INTEGRACJA_KURJERZY_NADAWCA_ULICA']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURJERZY_NADAWCA_ULICA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_kurjerzy_nadawca_dom">Numer domu / lokalu:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_kurjerzy_nadawca_dom" name="integracja_kurjerzy_nadawca_dom" value="'.$parametr['INTEGRACJA_KURJERZY_NADAWCA_DOM']['0'].'" size="20" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURJERZY_NADAWCA_DOM']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_kurjerzy_nadawca_kod_pocztowy">Kod pocztowy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_kurjerzy_nadawca_kod_pocztowy" name="integracja_kurjerzy_nadawca_kod_pocztowy" value="'.$parametr['INTEGRACJA_KURJERZY_NADAWCA_KOD_POCZTOWY']['0'].'" size="20" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURJERZY_NADAWCA_KOD_POCZTOWY']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_kurjerzy_nadawca_miasto">Miejscowość:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_kurjerzy_nadawca_miasto" name="integracja_kurjerzy_nadawca_miasto" value="'.$parametr['INTEGRACJA_KURJERZY_NADAWCA_MIASTO']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURJERZY_NADAWCA_MIASTO']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_kurjerzy_nadawca_telefon">Numer telefonu:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_kurjerzy_nadawca_telefon" name="integracja_kurjerzy_nadawca_telefon" value="'.$parametr['INTEGRACJA_KURJERZY_NADAWCA_TELEFON']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURJERZY_NADAWCA_TELEFON']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_kurjerzy_nadawca_email">Adres e-mail:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_kurjerzy_nadawca_email" name="integracja_kurjerzy_nadawca_email" value="'.$parametr['INTEGRACJA_KURJERZY_NADAWCA_EMAIL']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURJERZY_NADAWCA_EMAIL']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr>
                    <td colspan="2">
                      <div class="przyciski_dolne">
                        <button type="button" class="przyciskNon" onclick="cofnij('konfiguracja_wysylki','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','integracje');">Powrót</button>
                        <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'kurjerzy' ? $wynik : '' ); ?>
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
