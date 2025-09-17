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
        <div class="naglowek">Edycja danych - Firma kurierska Siódemka</div>

        <div class="pozycja_edytowana"> 

          <script>
          $(document).ready(function() {
            $("#form-siodemka").validate({
              rules: {
                integracja_siodemka_api_pin: {required: function() {var wynik = true; if ( $("input[name='integracja_siodemka_wlaczony']:checked", "#form-siodemka").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_siodemka_klient_id: {required: function() {var wynik = true; if ( $("input[name='integracja_siodemka_wlaczony']:checked", "#form-siodemka").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_siodemka_kurier_id: {required: function() {var wynik = true; if ( $("input[name='integracja_siodemka_wlaczony']:checked", "#form-siodemka").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_siodemka_potwierdzenie_podpis: {required: function() {var wynik = true; if ( $("input[name='integracja_siodemka_wlaczony']:checked", "#form-siodemka").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_siodemka_nadawca_telefon: {required: function() {var wynik = true; if ( $("input[name='integracja_siodemka_wlaczony']:checked", "#form-siodemka").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_siodemka_nadawca_email: {required: function() {var wynik = true; if ( $("input[name='integracja_siodemka_wlaczony']:checked", "#form-siodemka").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_siodemka_numer_konta: {required: function() {var wynik = true; if ( $("input[name='integracja_siodemka_zwrot_pobrania']:checked", "#form-siodemka").val() == "P" ) { wynik = false; } return wynik; }},
                integracja_siodemka_wymiary_dlugosc: { digits: true },
                integracja_siodemka_wymiary_szerokosc: { digits: true },
                integracja_siodemka_wymiary_wysokosc: { digits: true }
                }
            });

            setTimeout(function() {
              $('#<?php echo $system; ?>').fadeOut();
            }, 3000);

          });
          </script>  

          <div class="Sledzenie">

            <form action="integracje/konfiguracja_wysylki_siodemka.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" name="form_siodemka" id="form-siodemka" class="cmxform"> 
            
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="siodemka" />
                
                <table>
                
                  <tr><td colspan="2" class="SledzenieOpis">
                    <div>Integracja z firmą kurierską Siódemka umożliwiająca nadwanie paczek bezpośrednio z poziomu edycji zamówienia w sklepie.</div>
                    <img src="obrazki/logo/logo_siodemka.png" alt="" />
                  </td></tr>                   

                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Włącz integrację Siódemka:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SIODEMKA_WLACZONY']['1'], $parametr['INTEGRACJA_SIODEMKA_WLACZONY']['0'], 'integracja_siodemka_wlaczony', $parametr['INTEGRACJA_SIODEMKA_WLACZONY']['2'], '', $parametr['INTEGRACJA_SIODEMKA_WLACZONY']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_siodemka_klient_id">Numer klienta w WebMobile7:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_siodemka_klient_id" id="integracja_siodemka_klient_id" value="'.$parametr['INTEGRACJA_SIODEMKA_KLIENT_ID']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SIODEMKA_KLIENT_ID']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_siodemka_api_pin">Kod API:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_siodemka_api_pin" id="integracja_siodemka_api_pin" value="'.$parametr['INTEGRACJA_SIODEMKA_API_PIN']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SIODEMKA_API_PIN']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_siodemka_kurier_id">Nr kuriera:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_siodemka_kurier_id" id="integracja_siodemka_kurier_id" value="'.$parametr['INTEGRACJA_SIODEMKA_KURIER_ID']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SIODEMKA_KURIER_ID']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_siodemka_potwierdzenie_podpis">Podpis nadawcy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_siodemka_potwierdzenie_podpis" id="integracja_siodemka_potwierdzenie_podpis" value="'.$parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_PODPIS']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_PODPIS']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_siodemka_nadawca_telefon">Numer telefonu:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_siodemka_nadawca_telefon" name="integracja_siodemka_nadawca_telefon" value="'.$parametr['INTEGRACJA_SIODEMKA_NADAWCA_TELEFON']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SIODEMKA_NADAWCA_TELEFON']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_siodemka_nadawca_email">Adres e-mail:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_siodemka_nadawca_email" name="integracja_siodemka_nadawca_email" value="'.$parametr['INTEGRACJA_SIODEMKA_NADAWCA_EMAIL']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SIODEMKA_NADAWCA_EMAIL']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Domyślny rodzaj przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SIODEMKA_RODZAJ_PRZESYLKI']['1'], $parametr['INTEGRACJA_SIODEMKA_RODZAJ_PRZESYLKI']['0'], 'integracja_siodemka_rodzaj_przesylki', $parametr['INTEGRACJA_SIODEMKA_RODZAJ_PRZESYLKI']['2'], 'krajowa,zagraniczna,lokalna', $parametr['INTEGRACJA_SIODEMKA_RODZAJ_PRZESYLKI']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Kto płaci za usługę:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SIODEMKA_PLATNIK']['1'], $parametr['INTEGRACJA_SIODEMKA_PLATNIK']['0'], 'integracja_siodemka_platnik', $parametr['INTEGRACJA_SIODEMKA_PLATNIK']['2'], 'nadawca,odbiorca,trzeci płatnik', $parametr['INTEGRACJA_SIODEMKA_PLATNIK']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Forma płatności za przesyłkę:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SIODEMKA_FORMA_PLATNOSCI']['1'], $parametr['INTEGRACJA_SIODEMKA_FORMA_PLATNOSCI']['0'], 'integracja_siodemka_forma_platnosci', $parametr['INTEGRACJA_SIODEMKA_FORMA_PLATNOSCI']['2'], 'gotówka,przelew', $parametr['INTEGRACJA_SIODEMKA_FORMA_PLATNOSCI']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Potwierdzenie doręczenia:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DORECZENIA']['1'], $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DORECZENIA']['0'], 'integracja_siodemka_potwierdzenie_doreczenia', $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DORECZENIA']['2'], 'brak,PD email,PD kurier,PD email i PD kurier', $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DORECZENIA']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Forma zwrotu pobrania:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SIODEMKA_ZWROT_POBRANIA']['1'], $parametr['INTEGRACJA_SIODEMKA_ZWROT_POBRANIA']['0'], 'integracja_siodemka_zwrot_pobrania', $parametr['INTEGRACJA_SIODEMKA_ZWROT_POBRANIA']['2'], 'przekaz pocztowy,przelew bankowy,pobranie NextDay', $parametr['INTEGRACJA_SIODEMKA_ZWROT_POBRANIA']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_siodemka_numer_konta">Numer konta bankowego:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_siodemka_numer_konta" name="integracja_siodemka_numer_konta" value="'.$parametr['INTEGRACJA_SIODEMKA_NUMER_KONTA']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SIODEMKA_NUMER_KONTA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Dokumenty zwrotne:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SIODEMKA_DOKUMENTY_ZWROTNE']['1'], $parametr['INTEGRACJA_SIODEMKA_DOKUMENTY_ZWROTNE']['0'], 'integracja_siodemka_dokumenty_zwrotne', $parametr['INTEGRACJA_SIODEMKA_DOKUMENTY_ZWROTNE']['2'], 'nie,tak', $parametr['INTEGRACJA_SIODEMKA_DOKUMENTY_ZWROTNE']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Potwierdzenie nadania przesyłki na podany adres e-mail:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_NADANIA_EMAIL']['1'], $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_NADANIA_EMAIL']['0'], 'integracja_siodemka_potwierdzenie_nadania_email', $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_NADANIA_EMAIL']['2'], 'nie,tak', $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_NADANIA_EMAIL']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Potwierdzenie dostarczenia przesyłki na adres e-mail:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DOSTARCZENIA_EMAIL']['1'], $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DOSTARCZENIA_EMAIL']['0'], 'integracja_siodemka_potwierdzenie_dostarczenia_email', $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DOSTARCZENIA_EMAIL']['2'], 'nie,tak', $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DOSTARCZENIA_EMAIL']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Potwierdzenie dostarczenia przesyłki SMS:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DOSTARCZENIA_SMS']['1'], $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DOSTARCZENIA_SMS']['0'], 'integracja_siodemka_potwierdzenie_dostarczenia_sms', $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DOSTARCZENIA_SMS']['2'], 'nie,tak', $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DOSTARCZENIA_SMS']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_siodemka_wymiary_dlugosc">Preferowane wymiary przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo 'długość: <input type="text" name="integracja_siodemka_wymiary_dlugosc" id="integracja_siodemka_wymiary_dlugosc" value="'.$parametr['INTEGRACJA_SIODEMKA_WYMIARY_DLUGOSC']['0'].'" size="12" />';
                      echo ' &nbsp; szerokość: <input type="text" name="integracja_siodemka_wymiary_szerokosc" value="'.$parametr['INTEGRACJA_SIODEMKA_WYMIARY_SZEROKOSC']['0'].'" size="12" />';
                      echo ' &nbsp; wysokość: <input type="text" name="integracja_siodemka_wymiary_wysokosc" value="'.$parametr['INTEGRACJA_SIODEMKA_WYMIARY_WYSOKOSC']['0'].'" size="12" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_siodemka_zawartosc">Domyślna zawartość przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_siodemka_zawartosc" name="INTEGRACJA_SIODEMKA_ZAWARTOSC" value="'.$parametr['INTEGRACJA_SIODEMKA_ZAWARTOSC']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SIODEMKA_ZAWARTOSC']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_siodemka_kwota_ubezpieczenia">Domyślna wartość ubezpieczenia:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_siodemka_kwota_ubezpieczenia" name="INTEGRACJA_SIODEMKA_KWOTA_UBEZPIECZENIA" value="'.$parametr['INTEGRACJA_SIODEMKA_KWOTA_UBEZPIECZENIA']['0'].'" size="30" class="kropkaPusta" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SIODEMKA_KWOTA_UBEZPIECZENIA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr>
                    <td colspan="2">
                      <div class="przyciski_dolne">
                        <button type="button" class="przyciskNon" onclick="cofnij('konfiguracja_wysylki','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','integracje');">Powrót</button>
                        <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'siodemka' ? $wynik : '' ); ?>
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
