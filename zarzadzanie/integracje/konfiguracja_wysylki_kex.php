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
      $db->update_query('settings' , $pola, " code LIKE 'INTEGRACJA_KEX_%'");	
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
        <div class="naglowek">Edycja danych - Firma kurierska K-EX</div>

        <div class="pozycja_edytowana"> 

          <script>
          $(document).ready(function() {
            $("#form-kex").validate({
              rules: {
                integracja_kex_api_key: {required: function() {var wynik = true; if ( $("input[name='integracja_kex_wlaczony']:checked", "#form-kex").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_kex_klient_ck: {required: function() {var wynik = true; if ( $("input[name='integracja_kex_wlaczony']:checked", "#form-kex").val() == "nie" ) { wynik = false; } return wynik; }},
                }
            });

            setTimeout(function() {
              $('#<?php echo $system; ?>').fadeOut();
            }, 3000);

          });
          </script>  

          <div class="Sledzenie">

            <form action="integracje/konfiguracja_wysylki_kex.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" name="form_kex" id="form-kex" class="cmxform"> 
            
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="kex" />
                
                <table>
                
                  <tr><td colspan="2" class="SledzenieOpis">
                    <div>Integracja z firmą kurierską K-EX umożliwiająca nadawanie paczek bezpośrednio z poziomu edycji zamówienia w sklepie.</div>
                    <img src="obrazki/logo/logo_kex.png" alt="" />
                  </td></tr>                   

                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Włącz integrację K-EX:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KEX_WLACZONY']['1'], $parametr['INTEGRACJA_KEX_WLACZONY']['0'], 'integracja_kex_wlaczony', $parametr['INTEGRACJA_KEX_WLACZONY']['2'], '', $parametr['INTEGRACJA_KEX_WLACZONY']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Włącz tryb testowy:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KEX_SANDBOX']['1'], $parametr['INTEGRACJA_KEX_SANDBOX']['0'], 'integracja_kex_sandbox', $parametr['INTEGRACJA_KEX_SANDBOX']['2'], '', $parametr['INTEGRACJA_KEX_SANDBOX']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_kex_klient_ck">Numer CK klienta (zleceniodawcy):</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_kex_klient_ck" id="integracja_kex_klient_ck" value="'.$parametr['INTEGRACJA_KEX_KLIENT_CK']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KEX_KLIENT_CK']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_kex_api_key">Kod API:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_kex_api_key" id="integracja_kex_api_key" value="'.$parametr['INTEGRACJA_KEX_API_KEY']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KEX_API_KEY']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Powiadamienie e-mail::</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KEX_POWIADOM_EMAIL']['1'], $parametr['INTEGRACJA_KEX_POWIADOM_EMAIL']['0'], 'integracja_kex_powiadom_email', $parametr['INTEGRACJA_KEX_POWIADOM_EMAIL']['2'], '', $parametr['INTEGRACJA_KEX_POWIADOM_EMAIL']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_kex_osoba_nadajaca">Osoba nadająca:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_kex_osoba_nadajaca" id="integracja_kex_osoba_nadajaca" value="'.$parametr['INTEGRACJA_KEX_OSOBA_NADAJACA']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KEX_OSOBA_NADAJACA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_kex_adres_email">E-mail nadawcy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_kex_adres_email" id="integracja_kex_adres_email" value="'.$parametr['INTEGRACJA_KEX_ADRES_EMAIL']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KEX_ADRES_EMAIL']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_kex_telefon_stacjonarny">Tel. stacjonarny nadawcy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_kex_telefon_stacjonarny" id="integracja_kex_telefon_stacjonarny" value="'.$parametr['INTEGRACJA_KEX_TELEFON_STACJONARNY']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KEX_TELEFON_STACJONARNY']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_kex_telefon_gsm">Tel. komórkowy nadawcy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_kex_telefon_gsm" id="integracja_kex_telefon_gsm" value="'.$parametr['INTEGRACJA_KEX_TELEFON_GSM']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KEX_TELEFON_GSM']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Domyślny rodzaj usługi:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KEX_RODZAJ_USLUGI']['1'], $parametr['INTEGRACJA_KEX_RODZAJ_USLUGI']['0'], 'integracja_kex_rodzaj_uslugi', $parametr['INTEGRACJA_KEX_RODZAJ_USLUGI']['2'], 'Express,LTL', $parametr['INTEGRACJA_KEX_RODZAJ_USLUGI']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Kto płaci za usługę:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KEX_PLATNIK']['1'], $parametr['INTEGRACJA_KEX_PLATNIK']['0'], 'integracja_kex_platnik', $parametr['INTEGRACJA_KEX_PLATNIK']['2'], 'gotówką nadawca,gotówką odbiorca,zleceniodawca wg umowy,strona trzecia', $parametr['INTEGRACJA_KEX_PLATNIK']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_kex_numer_konta">Rachunek bankowy pobrania:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_kex_numer_konta" name="integracja_kex_numer_konta" value="'.$parametr['INTEGRACJA_KEX_NUMER_KONTA']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KEX_NUMER_KONTA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Awizacja odbioru:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KEX_ODBIOR_POWIADOMIENIE']['1'], $parametr['INTEGRACJA_KEX_ODBIOR_POWIADOMIENIE']['0'], 'integracja_kex_odbior_powiadomienie', $parametr['INTEGRACJA_KEX_ODBIOR_POWIADOMIENIE']['2'], 'SMS,E-mail,Telefon', $parametr['INTEGRACJA_KEX_ODBIOR_POWIADOMIENIE']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Awizacja dostawy:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KEX_DOSTAWA_POWIADOMIENIE']['1'], $parametr['INTEGRACJA_KEX_DOSTAWA_POWIADOMIENIE']['0'], 'integracja_kex_dostawa_powiadomienie', $parametr['INTEGRACJA_KEX_DOSTAWA_POWIADOMIENIE']['2'], 'SMS,E-mail,Telefon', $parametr['INTEGRACJA_KEX_DOSTAWA_POWIADOMIENIE']['3'] );
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Potwierdzenie dostawy:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KEX_DOSTAWA_POTWIERDZENIE']['1'], $parametr['INTEGRACJA_KEX_DOSTAWA_POTWIERDZENIE']['0'], 'integracja_kex_dostawa_potwierdzenie', $parametr['INTEGRACJA_KEX_DOSTAWA_POTWIERDZENIE']['2'], 'SMS,E-mail', $parametr['INTEGRACJA_KEX_DOSTAWA_POTWIERDZENIE']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_kex_zawartosc">Domyślna zawartość przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KEX_ZAWARTOSC']['1'], $parametr['INTEGRACJA_KEX_ZAWARTOSC']['0'], 'integracja_kex_zawartosc', $parametr['INTEGRACJA_KEX_ZAWARTOSC']['2'], '', $parametr['INTEGRACJA_KEX_ZAWARTOSC']['3'], '', '', 'id="integracja_kex_zawartosc"' );
                      ?>
                    </td>
                  </tr>

                  <tr>
                    <td colspan="2">
                      <div class="przyciski_dolne">
                        <button type="button" class="przyciskNon" onclick="cofnij('konfiguracja_wysylki','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','integracje');">Powrót</button>
                        <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'kex' ? $wynik : '' ); ?>
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
