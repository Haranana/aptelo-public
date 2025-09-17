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
      $db->update_query('settings' , $pola, " code LIKE 'INTEGRACJA_KURIER_INPOST_SHIPX_%'");	
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

          unset($pola);
        }
      }
      $pola = array(
              array('description',$filtr->process($_POST['paczkomat_preferowany']))
      );
      $db->update_query('settings' , $pola, " code = 'INTEGRACJA_KURIER_INPOST_SHIPX_PACZKOMAT'");

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
                integracja_kurier_inpost_shipx_organization_id: {required: function() {var wynik = true; if ( $("input[name='integracja_kurier_inpost_shipx_wlaczony']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_kurier_inpost_shipx_access_token: {required: function() {var wynik = true; if ( $("input[name='integracja_kurier_inpost_shipx_wlaczony']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }}
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
                            $("#integracja_kurier_inpost_shipx_paczkomat").val(point.name);

                            }, {width: szerokosc, height: wysokosc });
                    }
                }

          };
          </script>

          <div class="Sledzenie">

                <form action="integracje/konfiguracja_wysylki_inpost_shipx.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" name="form_inpost" id="form-inpost" class="cmxform">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    <input type="hidden" name="system" value="shipx" />
                    
                    <table>
                    
                      <tr><td colspan="2" class="SledzenieOpis">
                        <div>Idealne rozwiązanie dla sklepów internetowych. Umożliwia generowanie etykiet, tworzenie wysyłek i zarządzanie rozliczeniami, pozwala na nadawanie indywidualnych numerów paczek i tworzenie własnych etykiet.</div>
                        <img src="obrazki/logo/logo_kurier_inpost.png" alt="" />
                      </td></tr>                    
                    
                      <tr class="SledzeniePozycja">
                        <td>
                          <label>Włącz integrację InPost:</label>
                        </td>
                        <td>
                          <?php
                          echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KURIER_INPOST_SHIPX_WLACZONY']['1'], $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_WLACZONY']['0'], 'integracja_kurier_inpost_shipx_wlaczony', $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_WLACZONY']['2'], '', $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_WLACZONY']['3'] );
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label>Tryb testowy:</label>
                        </td>
                        <td>
                          <?php
                          echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KURIER_INPOST_SHIPX_SANDBOX']['1'], $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_SANDBOX']['0'], 'integracja_kurier_inpost_shipx_sandbox', $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_SANDBOX']['2'], '', $parametr['INTEGRACJA_KURIER_INPOST_SANDBOX']['3'] );
                          ?>
                        </td>
                      </tr>

                      <tr class="SledzeniePozycja">
                        <td>
                          <label class="required" for="integracja_kurier_inpost_shipx_organization_id">ID organizacji:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" name="integracja_kurier_inpost_shipx_organization_id" id="integracja_kurier_inpost_shipx_organization_id" value="'.$parametr['INTEGRACJA_KURIER_INPOST_SHIPX_ORGANIZATION_ID']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_ORGANIZATION_ID']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label class="required" for="integracja_kurier_inpost_shipx_access_token">Klucz autoryzacyjny</label>
                        </td>
                        <td>
                          <?php
                          echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KURIER_INPOST_SHIPX_ACCESS_TOKEN']['1'], $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_ACCESS_TOKEN']['0'], 'integracja_kurier_inpost_shipx_access_token', $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_ACCESS_TOKEN']['2'], '', $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_ACCESS_TOKEN']['3'], '', '', 'id="integracja_kurier_inpost_shipx_access_token"' );
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_shipx_zawartosc">Domyślna zawartość przesyłki:</label>
                        </td>
                        <td>
                          <?php
                          echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KURIER_INPOST_SHIPX_ZAWARTOSC']['1'], $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_ZAWARTOSC']['0'], 'integracja_kurier_inpost_shipx_zawartosc', $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_ZAWARTOSC']['2'], '', $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_ZAWARTOSC']['3'], '', '', 'id="integracja_kurier_inpost_shipx_zawartosc"' );
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_shipx_nadawca" class="required">Imię i nazwisko:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input class="required" type="text" id="integracja_kurier_inpost_shipx_nadawca" name="integracja_kurier_inpost_shipx_nadawca" value="'.$parametr['INTEGRACJA_KURIER_INPOST_SHIPX_NADAWCA']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_NADAWCA']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_shipx_firma" class="required">Nazwa firmy:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input class="required" type="text" id="integracja_kurier_inpost_shipx_firma" name="integracja_kurier_inpost_shipx_firma" value="'.$parametr['INTEGRACJA_KURIER_INPOST_SHIPX_FIRMA']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_FIRMA']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_shipx_ulica" class="required">Ulica:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input class="required" type="text" id="integracja_kurier_inpost_shipx_ulica" name="integracja_kurier_inpost_shipx_ulica" value="'.$parametr['INTEGRACJA_KURIER_INPOST_SHIPX_ULICA']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_ULICA']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_shipx_building" class="required">Numer domu:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input class="required" type="text" id="integracja_kurier_inpost_shipx_building" name="integracja_kurier_inpost_shipx_building" value="'.$parametr['INTEGRACJA_KURIER_INPOST_SHIPX_BUILDING']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_BUILDING']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>

                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_shipx_kod_pocztowy" class="required">Kod pocztowy:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input class="required" type="text" id="integracja_kurier_inpost_shipx_kod_pocztowy" name="integracja_kurier_inpost_shipx_kod_pocztowy" value="'.$parametr['INTEGRACJA_KURIER_INPOST_SHIPX_KOD_POCZTOWY']['0'].'" size="20" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_KOD_POCZTOWY']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_shipx_miasto" class="required">Miejscowość:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input class="required" type="text" id="integracja_kurier_inpost_shipx_miasto" name="integracja_kurier_inpost_shipx_miasto" value="'.$parametr['INTEGRACJA_KURIER_INPOST_SHIPX_MIASTO']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_MIASTO']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_shipx_telefon" class="required">Numer telefonu:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input class="required" type="text" id="integracja_kurier_inpost_shipx_telefon" name="integracja_kurier_inpost_shipx_telefon" value="'.$parametr['INTEGRACJA_KURIER_INPOST_SHIPX_TELEFON']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_TELEFON']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_shipx_email" class="required">Adres e-mail:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input class="required" type="text" id="integracja_kurier_inpost_shipx_email" name="integracja_kurier_inpost_shipx_email" value="'.$parametr['INTEGRACJA_KURIER_INPOST_SHIPX_EMAIL']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_EMAIL']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_shipx_numer_konta" class="required">Numer konta bankowego:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input class="required" type="text" id="integracja_kurier_inpost_shipx_numer_konta" name="integracja_kurier_inpost_shipx_numer_konta" value="'.$parametr['INTEGRACJA_KURIER_INPOST_SHIPX_NUMER_KONTA']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_NUMER_KONTA']['2'].'</b></em>';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_shipx_paczkomat">Paczkomat, w którym będą nadawane przesyłki:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" class="przyciskPaczkomatu" id="WidgetButton" value="Wybierz paczkomat" readonly="readonly" />';

                          echo '<input type="text" id="integracja_kurier_inpost_shipx_paczkomat" name="integracja_kurier_inpost_shipx_paczkomat" value="'.$parametr['INTEGRACJA_KURIER_INPOST_SHIPX_PACZKOMAT']['0'].'" size="10" readonly="readonly" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_PACZKOMAT']['2'].'</b></em>';

                          echo '<input type="text" id="paczkomat_preferowany" value="'.$parametr['INTEGRACJA_KURIER_INPOST_SHIPX_PACZKOMAT']['2'].'" name="paczkomat_preferowany" readonly="readonly"  style="margin-left:10px;" size="73" />';


                          ?>
                        </td>
                      </tr>

                      <tr class="SledzeniePozycja">
                        <td>
                          <label>Powiadomienie odbiorcy</label>
                        </td>
                        <td>
                          <?php
                          echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KURIER_INPOST_SHIPX_POWIADOMIENIE']['1'], $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_POWIADOMIENIE']['0'], 'integracja_kurier_inpost_shipx_powiadomienie', $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_POWIADOMIENIE']['2'], 'SMS,E-mail', $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_POWIADOMIENIE']['3'] );
                          ?>
                        </td>
                      </tr>


                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_shipx_nadanie">Domyślny sposób nadania:</label>
                        </td>
                        <td>
                        <?php
                        $domyslna = $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_NADANIE']['0'];
                        ?>
                        <select name="integracja_kurier_inpost_shipx_nadanie" id="integracja_kurier_inpost_shipx_nadanie">
                            <option value="">- wybierz -</option>
                            <option value="parcel_locker" <?php echo ( $domyslna == 'parcel_locker' ? 'selected="selected"' : '' ); ?>>Nadam przesyłkę w Paczkomacie</option>
                            <option value="dispatch_order" <?php echo ( $domyslna == 'dispatch_order' ? 'selected="selected"' : '' ); ?>>Utworzę zlecenie odbioru - przesyłkę odbierze kurier InPost</option>
                            <option value="branch" <?php echo ( $domyslna == 'branch' ? 'selected="selected"' : '' ); ?>>Dostarczę przesyłkę do Oddziału InPost</option>
                            <option value="pop" <?php echo ( $domyslna == 'pop' ? 'selected="selected"' : '' ); ?>>Nadam przesyłkę w POP</option>
                         </select><em class="TipIkona"><b><?php echo $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_NADANIE']['2']; ?></b></em>
                        </td>
                      </tr>

                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_shipx_wydruk_format">Domyślny format wydruków:</label>
                        </td>
                        <td>
                        <?php
                        $domyslna = $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_WYDRUK_FORMAT']['0'];
                        ?>
                        <select name="integracja_kurier_inpost_shipx_wydruk_format" id="integracja_kurier_inpost_shipx_wydruk_format">
                            <option value="pdf" <?php echo ( $domyslna == 'pdf' ? 'selected="selected"' : '' ); ?>>PDF</option>
                            <option value="zpl" <?php echo ( $domyslna == 'zpl' ? 'selected="selected"' : '' ); ?>>ZPL</option>
                            <option value="epl" <?php echo ( $domyslna == 'epl' ? 'selected="selected"' : '' ); ?>>EPL</option>
                         </select><em class="TipIkona"><b><?php echo $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_WYDRUK_FORMAT']['2']; ?></b></em>
                        </td>
                      </tr>

                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_shipx_wydruk_typ">Domyślny typ wydruków:</label>
                        </td>
                        <td>
                        <?php
                        $domyslna = $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_WYDRUK_TYP']['0'];
                        ?>
                        <select name="integracja_kurier_inpost_shipx_wydruk_typ" id="integracja_kurier_inpost_shipx_wydruk_typ">
                            <option value="normal" <?php echo ( $domyslna == 'normal' ? 'selected="selected"' : '' ); ?>>normal</option>
                            <option value="A6" <?php echo ( $domyslna == 'A6' ? 'selected="selected"' : '' ); ?>>A6</option>
                         </select><em class="TipIkona"><b><?php echo $parametr['INTEGRACJA_KURIER_INPOST_SHIPX_WYDRUK_TYP']['2']; ?></b></em>
                        </td>
                      </tr>

                      <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_kurier_inpost_shipx_dlugosc">Domyślny rozmiar paczki [mm]:</label>
                        </td>
                        <td>
                          <?php
                          echo 'długość: <input type="text" name="integracja_kurier_inpost_shipx_dlugosc" id="integracja_kurier_inpost_shipx_dlugosc" value="'.$parametr['INTEGRACJA_KURIER_INPOST_SHIPX_DLUGOSC']['0'].'" size="12" />';
                          echo ' &nbsp; szerokość: <input type="text" name="integracja_kurier_inpost_shipx_szerokosc" value="'.$parametr['INTEGRACJA_KURIER_INPOST_SHIPX_SZEROKOSC']['0'].'" size="12" />';
                          echo ' &nbsp; wysokość: <input type="text" name="integracja_kurier_inpost_shipx_wysokosc" value="'.$parametr['INTEGRACJA_KURIER_INPOST_SHIPX_WYSOKOSC']['0'].'" size="12" />';
                          ?>
                        </td>
                      </tr>
                      

                      <tr>
                        <td colspan="2">
                          <div class="przyciski_dolne">
                            <button type="button" class="przyciskNon" onclick="cofnij('konfiguracja_wysylki','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','integracje');">Powrót</button>
                            <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'shipx' ? $wynik : '' ); ?>
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
