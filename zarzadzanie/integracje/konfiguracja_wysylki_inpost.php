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

      if ( $_POST['integracja_inpost_nadawanie'] == 'tak' ) { $_POST['integracja_inpost_paczkomat'] = ''; } 
      
      if ( $_POST['integracja_inpost_nadawca_etykieta'] == 'nie' ) { 
        $_POST['integracja_inpost_nadawca_imie'] = ''; 
        $_POST['integracja_inpost_nadawca_nazwisko'] = ''; 
        $_POST['integracja_inpost_nadawca_ulica'] = ''; 
        $_POST['integracja_inpost_nadawca_dom'] = ''; 
        $_POST['integracja_inpost_nadawca_kod_pocztowy'] = ''; 
        $_POST['integracja_inpost_nadawca_miasto'] = ''; 
        $_POST['integracja_inpost_nadawca_telefon'] = ''; 
        $_POST['integracja_inpost_nadawca_email'] = ''; 
      } 

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
      $pola = array(
              array('description',$filtr->process($_POST['paczkomat_preferowany']))
      );
      $db->update_query('settings' , $pola, " code = 'INTEGRACJA_INPOST_PACZKOMAT'");

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

    <style type="text/css">
    .nadawca_etykieta { <?php echo ( $parametr['INTEGRACJA_INPOST_NADAWCA_ETYKIETA']['0'] == 'tak' ? '' : 'display: none;'); ?> }
    </style>

    <div id="naglowek_cont">Konfiguracja parametrów systemów wysyłkowych</div>
    <div id="cont">

      <div class="poleForm">
        <div class="naglowek">Edycja danych - Firma Paczkomaty InPost</div>

        <div class="pozycja_edytowana"> 

          <script>
          $(document).ready(function() {
            $("#form-inpost").validate({
              rules: {
                integracja_inpost_login_email: {required: function() {var wynik = true; if ( $("input[name='integracja_inpost_wlaczony']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_inpost_login_haslo: {required: function() {var wynik = true; if ( $("input[name='integracja_inpost_wlaczony']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_inpost_nadawca_imie: {required: function() {var wynik = true; if ( $("input[name='integracja_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_inpost_nadawca_nazwisko: {required: function() {var wynik = true; if ( $("input[name='integracja_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_inpost_nadawca_ulica: {required: function() {var wynik = true; if ( $("input[name='integracja_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_inpost_nadawca_dom: {required: function() {var wynik = true; if ( $("input[name='integracja_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_inpost_nadawca_kod_pocztowy: {required: function() {var wynik = true; if ( $("input[name='integracja_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_inpost_nadawca_miasto: {required: function() {var wynik = true; if ( $("input[name='integracja_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_inpost_nadawca_email: {required: function() {var wynik = true; if ( $("input[name='integracja_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_inpost_nadawca_telefon: {required: function() {var wynik = true; if ( $("input[name='integracja_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }}
                }
            });

            setTimeout(function() {
              $('#<?php echo $system; ?>').fadeOut();
            }, 3000);

          });
          </script>

          <script async src="https://geowidget.easypack24.net/js/sdk-for-javascript.js"></script>
          <link rel="stylesheet" href="https://geowidget.easypack24.net/css/easypack.css"/>

          <script type="text/javascript">
            window.easyPackAsyncInit = function () {
                easyPack.init({
                    defaultLocale: 'pl',
                    mapType: 'osm',
                    searchType: 'osm',
                    points: {
                        types: ['parcel_locker', 'pop']
                    },
                    map: {
                        initialTypes: ['parcel_locker', 'pop']
                    }
                });

                window.onload = function() {
                    var szerokosc = 600;
                    var wysokosc = 600;
                    var button = document.getElementById("WidgetButton");
                    var szerokoscEkranu = $("#StrGlowna").outerWidth();

                    if ( szerokoscEkranu < 440 ) {
                        szerokosc = 320;
                        wysokosc = 380;
                    }

                    button.onclick = function() {

                        var map1 = easyPack.modalMap(function(point) {

                            $("#widget-modal").unwrap();
                            $("#widget-modal").remove();

                            $("#paczkomat_preferowany").val(point.address["line1"] + ", " + point.address["line2"]);
                            $("#integracja_inpost_paczkomat").val(point.name);

                            }, {width: szerokosc, height: wysokosc });
                    }
                }

          };
          </script>


          <?php
          $adres_firmy  = Funkcje::PrzeksztalcAdres(DANE_ADRES_LINIA_1);

          $inpost = new InPostApi();
          ?>
          <div class="Sledzenie">

                <form action="integracje/konfiguracja_wysylki_inpost.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" name="form_inpost" id="form-inpost" class="cmxform">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    <input type="hidden" name="system" value="inpost" />
                    
                    <table>
                    
                      <tr><td colspan="2" class="SledzenieOpis">
                        <div>Paczkomaty InPost to system skrytek pocztowych, służący do odbierania  paczek 24 godziny na dobę przez 7 dni w tygodniu. Osoba robiąca zakupy przez Internet – po zamówieniu przesyłki do Paczkomatu InPost - otrzyma SMS i e-mail z kodem odbioru. Aby odebrać przesyłkę wystarczy wpisać na panelu Paczkomatu InPost numer telefonu komórkowego oraz otrzymany kod odbioru, a skrytka z oczekiwaną przesyłką otworzy się. W ciągu 2 dni roboczych od momentu nadania paczki, przesyłka znajdzie się w paczkomacie. Odbiór paczki jest możliwy o dowolnej porze dnia czy nocy.</div>
                        <img src="obrazki/logo/logo_inpost.png" alt="" />
                      </td></tr>                    
                    
                      <tr class="SledzeniePozycja">
                        <td>
                          <label>Włącz integrację Paczkomaty InPost:</label>
                        </td>
                        <td>
                          <?php
                          echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_INPOST_WLACZONY']['1'], $parametr['INTEGRACJA_INPOST_WLACZONY']['0'], 'integracja_inpost_wlaczony', $parametr['INTEGRACJA_INPOST_WLACZONY']['2'], '', $parametr['INTEGRACJA_INPOST_WLACZONY']['3'] );
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label class="required" for="integracja_inpost_login_email">Adres e-mail:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" name="integracja_inpost_login_email" id="integracja_inpost_login_email" value="'.$parametr['INTEGRACJA_INPOST_LOGIN_EMAIL']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_INPOST_LOGIN_EMAIL']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label class="required" for="integracja_inpost_login_haslo">Hasło:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="password" name="integracja_inpost_login_haslo" id="integracja_inpost_login_haslo" value="'.$parametr['INTEGRACJA_INPOST_LOGIN_HASLO']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_INPOST_LOGIN_HASLO']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_inpost_wymiary">Domyślny rozmiar paczki:</label>
                        </td>
                        <td>
                          <?php
                          $tablica = $inpost->inpost_post_parcel_array(false);
                          echo Funkcje::RozwijaneMenu('integracja_inpost_wymiary', $tablica, $parametr['INTEGRACJA_INPOST_WYMIARY']['0'], 'id="integracja_inpost_wymiary" style="width:250px;"');
                          echo '<em class="TipIkona"><b>'. $parametr['INTEGRACJA_INPOST_WYMIARY']['2'].'</b></em>';
                          unset($tablica);
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label>Nadawanie przesyłek w oddziale lub odbiór przez kuriera:</label>
                        </td>
                        <td>
                          <input type="radio" value="tak" name="integracja_inpost_nadawanie" id="nadawanie_tak" onclick="$('#paczkomat_domyslny').slideUp();" <?php echo ($parametr['INTEGRACJA_INPOST_NADAWANIE']['0'] == 'tak' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="nadawanie_tak">tak<em class="TipIkona"><b><?php echo $parametr['INTEGRACJA_INPOST_NADAWANIE']['2']; ?></b></em></label>
                          <input type="radio" value="nie" name="integracja_inpost_nadawanie" id="nadawanie_nie" onclick="$('#paczkomat_domyslny').slideDown();" <?php echo ($parametr['INTEGRACJA_INPOST_NADAWANIE']['0'] == 'nie' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="nadawanie_nie">nie<em class="TipIkona"><b><?php echo $parametr['INTEGRACJA_INPOST_NADAWANIE']['2']; ?></b></em></label>
                        </td>
                      </tr>


                      <tr class="SledzeniePozycja"  id="paczkomat_domyslny" <?php echo ($parametr['INTEGRACJA_INPOST_NADAWANIE']['0'] == 'tak' ? 'style="display:none;"' : '' ); ?>>
                        <td>
                          <label for="integracja_inpost_paczkomat">Paczkomat, w którym będą nadawane przesyłki:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" class="przyciskPaczkomatu" id="WidgetButton" value="Wybierz paczkomat" readonly="readonly" />';

                          echo '<input type="text" id="integracja_inpost_paczkomat" name="integracja_inpost_paczkomat" value="'.$parametr['INTEGRACJA_INPOST_PACZKOMAT']['0'].'" size="10" readonly="readonly" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_INPOST_PACZKOMAT']['2'].'</b></em>';

                          echo '<input type="text" id="paczkomat_preferowany" value="'.$parametr['INTEGRACJA_INPOST_PACZKOMAT']['2'].'" name="paczkomat_preferowany" readonly="readonly"  style="margin-left:10px;" size="73" />';

                          ?>
                        </td>
                      </tr>

                      <tr class="SledzeniePozycja">
                        <td>
                          <label>Inne dane nadawcy na etykiecie:</label>
                        </td>
                        <td>
                          <input type="radio" value="tak" name="integracja_inpost_nadawca_etykieta" id="etykieta_tak" onclick="$('.nadawca_etykieta').slideDown();" <?php echo ($parametr['INTEGRACJA_INPOST_NADAWCA_ETYKIETA']['0'] == 'tak' ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="etykieta_tak">tak<em class="TipIkona"><b><?php echo $parametr['INTEGRACJA_INPOST_NADAWCA_ETYKIETA']['2']; ?></b></em></label>
                          <input type="radio" value="nie" name="integracja_inpost_nadawca_etykieta" id="etykieta_nie" onclick="$('.nadawca_etykieta').slideUp();" <?php echo ($parametr['INTEGRACJA_INPOST_NADAWCA_ETYKIETA']['0'] == 'nie' ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="etykieta_nie">nie<em class="TipIkona"><b><?php echo $parametr['INTEGRACJA_INPOST_NADAWCA_ETYKIETA']['2']; ?></b></em></label>
                        </td>
                      </tr>
                      
                      <tr class="nadawca_etykieta SledzeniePozycja" >
                        <td>
                          <label for="integracja_inpost_nadawca_imie">Imię nadawcy:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_inpost_nadawca_imie" name="integracja_inpost_nadawca_imie" value="'.$parametr['INTEGRACJA_INPOST_NADAWCA_IMIE']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_INPOST_NADAWCA_IMIE']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="nadawca_etykieta SledzeniePozycja">
                        <td>
                          <label for="integracja_inpost_nadawca_nazwisko">Nazwisko nadawcy:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_inpost_nadawca_nazwisko" name="integracja_inpost_nadawca_nazwisko" value="'.$parametr['INTEGRACJA_INPOST_NADAWCA_NAZWISKO']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_INPOST_NADAWCA_NAZWISKO']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="nadawca_etykieta SledzeniePozycja">
                        <td>
                          <label for="integracja_inpost_nadawca_ulica">Ulica:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_inpost_nadawca_ulica" name="integracja_inpost_nadawca_ulica" value="'.$parametr['INTEGRACJA_INPOST_NADAWCA_ULICA']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_INPOST_NADAWCA_ULICA']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="nadawca_etykieta SledzeniePozycja">
                        <td>
                          <label for="integracja_inpost_nadawca_dom">Numer domu / lokalu:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_inpost_nadawca_dom" name="integracja_inpost_nadawca_dom" value="'.$parametr['INTEGRACJA_INPOST_NADAWCA_DOM']['0'].'" size="20" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_INPOST_NADAWCA_DOM']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="nadawca_etykieta SledzeniePozycja">
                        <td>
                          <label for="integracja_inpost_nadawca_kod_pocztowy">Kod pocztowy:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_inpost_nadawca_kod_pocztowy" name="integracja_inpost_nadawca_kod_pocztowy" value="'.$parametr['INTEGRACJA_INPOST_NADAWCA_KOD_POCZTOWY']['0'].'" size="20" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_INPOST_NADAWCA_KOD_POCZTOWY']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="nadawca_etykieta SledzeniePozycja">
                        <td>
                          <label for="integracja_inpost_nadawca_miasto">Miejscowość:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_inpost_nadawca_miasto" name="integracja_inpost_nadawca_miasto" value="'.$parametr['INTEGRACJA_INPOST_NADAWCA_MIASTO']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_INPOST_NADAWCA_MIASTO']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="nadawca_etykieta SledzeniePozycja">
                        <td>
                          <label for="integracja_inpost_nadawca_telefon">Numer telefonu:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_inpost_nadawca_telefon" name="integracja_inpost_nadawca_telefon" value="'.$parametr['INTEGRACJA_INPOST_NADAWCA_TELEFON']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_INPOST_NADAWCA_TELEFON']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="nadawca_etykieta SledzeniePozycja">
                        <td>
                          <label for="integracja_inpost_nadawca_email">Adres e-mail:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_inpost_nadawca_email" name="integracja_inpost_nadawca_email" value="'.$parametr['INTEGRACJA_INPOST_NADAWCA_EMAIL']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_INPOST_NADAWCA_EMAIL']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label>Format wydruków:</label>
                        </td>
                        <td>
                          <?php
                          echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_INPOST_FORMAT_WYDRUKU']['1'], $parametr['INTEGRACJA_INPOST_FORMAT_WYDRUKU']['0'], 'integracja_inpost_format_wydruku', $parametr['INTEGRACJA_INPOST_FORMAT_WYDRUKU']['2'], '', $parametr['INTEGRACJA_INPOST_FORMAT_WYDRUKU']['3'] );
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label>Format generowanych plików:</label>
                        </td>
                        <td>
                          <?php
                          echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_INPOST_FORMAT_PLIKU']['1'], $parametr['INTEGRACJA_INPOST_FORMAT_PLIKU']['0'], 'integracja_inpost_format_pliku', $parametr['INTEGRACJA_INPOST_FORMAT_PLIKU']['2'], '', $parametr['INTEGRACJA_INPOST_FORMAT_PLIKU']['3'] );
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
