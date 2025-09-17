<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    //$api = new FurgonetkaRestApi(false, '', '');

    $wynik  = '';
    $system = ( isset($_POST['system']) ? $_POST['system'] : '' );

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      $pola = array(
              array('value','')
      );
      $db->update_query('settings' , $pola, " code LIKE 'INTEGRACJA_FURGONETKA_%'");	
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

      $pola = array(
              array('description',$filtr->process(strip_tags((string)$_POST['preferowany_inpost'])))
      );
      $db->update_query('settings' , $pola, " code = 'INTEGRACJA_FURGONETKA_INPOST'");
      unset($pola);
      $pola = array(
              array('description',$filtr->process(strip_tags((string)$_POST['preferowany_poczta'])))
      );
      $db->update_query('settings' , $pola, " code = 'INTEGRACJA_FURGONETKA_POCZTA'");
      unset($pola);
      $pola = array(
              array('description',$filtr->process(strip_tags((string)$_POST['preferowany_dpd'])))
      );
      $db->update_query('settings' , $pola, " code = 'INTEGRACJA_FURGONETKA_DPD'");
      unset($pola);
      $pola = array(
              array('description',$filtr->process(strip_tags((string)$_POST['preferowany_orlen'])))
      );
      $db->update_query('settings' , $pola, " code = 'INTEGRACJA_FURGONETKA_RUCH'");
      unset($pola);
      $pola = array(
              array('description',$filtr->process(strip_tags((string)$_POST['preferowany_ups'])))
      );
      $db->update_query('settings' , $pola, " code = 'INTEGRACJA_FURGONETKA_UPS'");
      unset($pola);
      $pola = array(
              array('description',$filtr->process(strip_tags((string)$_POST['preferowany_fedex'])))
      );
      $db->update_query('settings' , $pola, " code = 'INTEGRACJA_FURGONETKA_FEDEX'");
      unset($pola);
      $pola = array(
              array('description',$filtr->process(strip_tags((string)$_POST['preferowany_gls'])))
      );
      $db->update_query('settings' , $pola, " code = 'INTEGRACJA_FURGONETKA_GLS'");
      unset($pola);

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
        <div class="naglowek">Edycja danych - Firma FURGONETKA</div>

        <div class="pozycja_edytowana"> 

          <script>
          $(document).ready(function() {
            $("#form-furgonetka").validate({
              rules: {
                integracja_furgonetka_api_key: {required: function() {var wynik = true; if ( $("input[name='integracja_furgonetka_wlaczony']:checked", "#form-furgonetka").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_furgonetka_klient_ck: {required: function() {var wynik = true; if ( $("input[name='integracja_furgonetka_wlaczony']:checked", "#form-furgonetka").val() == "nie" ) { wynik = false; } return wynik; }},
                }
            });

            setTimeout(function() {
              $('#<?php echo $system; ?>').fadeOut();
            }, 3000);

          });
          </script>
          
          <script type="text/javascript"src="https://furgonetka.pl/js/dist/map/map.js"></script>

          <script>

            function callback(params) {
                var typ = params.point.type;
                var opis = params.point.name;
                var textString = opis.replace(/<\/?[^>]+(>|$)/g, "");
                $('#preferowany_'+typ+'').val(textString);
                $('#integracja_furgonetka_'+typ+'').val(params.point.code);

            }
            function PokazMape(Kurier) {
                var str = Kurier;
                var dostawca = str.toLowerCase();
                var miasto = $('#integracja_furgonetka_nadawca_miasto').val();
                new window.Furgonetka.Map({
                    courierServices: [dostawca],
                    city: miasto,
                    callback: callback
                    }).show(); 
                    return false;
            }


          </script>
          
          <div class="Sledzenie">

            <form action="integracje/konfiguracja_wysylki_furgonetka.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="form-furgonetka" class="cmxform"> 
            
                <div>
                    <input type="hidden" name="akcja" value="zapisz" />
                    <input type="hidden" name="system" value="furgonetka" />
                </div>

                <table>
                
                  <tr><td colspan="2" class="SledzenieOpis">
                    <div>Serwis Furgonetka.pl umożliwia korzystanie z szerokiego wachlarza usług kurierskich w bardzo atrakcyjnych cenach bez ograniczeń i potrzeby podpisywania umów. Oferujemy wygodne narzędzia, które pozwalają zarówno firmom, jak i osobom prywatnym, szybko i wygodnie zamówić kuriera.</div>
                    <img src="obrazki/logo/logo_furgonetka.png" alt="" />
                  </td></tr>                   

                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Włącz integrację FURGONETKA:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FURGONETKA_WLACZONY']['1'], $parametr['INTEGRACJA_FURGONETKA_WLACZONY']['0'], 'integracja_furgonetka_wlaczony', $parametr['INTEGRACJA_FURGONETKA_WLACZONY']['2'], '', $parametr['INTEGRACJA_FURGONETKA_WLACZONY']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Włącz tryb testowy:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FURGONETKA_SANDBOX']['1'], $parametr['INTEGRACJA_FURGONETKA_SANDBOX']['0'], 'integracja_furgonetka_sandbox', $parametr['INTEGRACJA_FURGONETKA_SANDBOX']['2'], '', $parametr['INTEGRACJA_FURGONETKA_SANDBOX']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_furgonetka_email">Adres do logowania:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_furgonetka_email" id="integracja_furgonetka_email" value="'.$parametr['INTEGRACJA_FURGONETKA_EMAIL']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FURGONETKA_EMAIL']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_furgonetka_password">Hasło do logowania:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_furgonetka_password" id="integracja_furgonetka_password" value="'.$parametr['INTEGRACJA_FURGONETKA_PASSWORD']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FURGONETKA_PASSWORD']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                        <label class="required" for="integracja_furgonetka_clientid">Client Id:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_furgonetka_clientid" id="integracja_furgonetka_clientid" value="'.$parametr['INTEGRACJA_FURGONETKA_CLIENTID']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FURGONETKA_CLIENTID']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                      
                  <tr class="SledzeniePozycja">
                    <td>
                        <label class="required" for="integracja_furgonetka_clientsecret">Client Secret:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_furgonetka_clientsecret" id="integracja_furgonetka_clientsecret" value="'.$parametr['INTEGRACJA_FURGONETKA_CLIENTSECRET']['0'].'" size="100" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FURGONETKA_CLIENTSECRET']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_furgonetka_wydruk_format">Domyślny typ plików:</label>
                        </td>
                        <td>
                        <?php
                        $domyslna = $parametr['INTEGRACJA_FURGONETKA_WYDRUK_FORMAT']['0'];
                        ?>
                        <select name="integracja_furgonetka_wydruk_format" id="integracja_furgonetka_wydruk_format">
                            <option value="pdf" <?php echo ( $domyslna == 'pdf' ? 'selected="selected"' : '' ); ?>>PDF</option>
                            <option value="zpl" <?php echo ( $domyslna == 'zpl' ? 'selected="selected"' : '' ); ?>>ZPL</option>
                            <option value="epl" <?php echo ( $domyslna == 'epl' ? 'selected="selected"' : '' ); ?>>EPL</option>
                         </select><em class="TipIkona"><b><?php echo $parametr['INTEGRACJA_FURGONETKA_WYDRUK_FORMAT']['2']; ?></b></em>
                        </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_furgonetka_wydruk_typ">Domyślny format wydruków:</label>
                        </td>
                        <td>
                        <?php
                        $domyslna = $parametr['INTEGRACJA_FURGONETKA_WYDRUK_TYP']['0'];
                        ?>
                        <select name="integracja_furgonetka_wydruk_typ" id="integracja_furgonetka_wydruk_typ">
                            <option value="a4" <?php echo ( $domyslna == 'a4' ? 'selected="selected"' : '' ); ?>>A4</option>
                            <option value="a6" <?php echo ( $domyslna == 'a6' ? 'selected="selected"' : '' ); ?>>A6</option>
                         </select><em class="TipIkona"><b><?php echo $parametr['INTEGRACJA_FURGONETKA_WYDRUK_TYP']['2']; ?></b></em>
                        </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_furgonetka_rodzaj_uslugi">Domyślny rodzaj przesyłki:</label>
                    </td>
                    <td>
                    <?php
                    $domyslna = $parametr['INTEGRACJA_FURGONETKA_RODZAJ_USLUGI']['0'];
                    ?>
                    <select name="integracja_furgonetka_rodzaj_uslugi" id="integracja_furgonetka_rodzaj_uslugi">
                        <option value="package" <?php echo ( $domyslna == 'package' ? 'selected="selected"' : '' ); ?>>Paczka</option>
                        <option value="dox" <?php echo ( $domyslna == 'dox' ? 'selected="selected"' : '' ); ?>>Koperta</option>
                        <option value="pallette" <?php echo ( $domyslna == 'pallette' ? 'selected="selected"' : '' ); ?>>Paleta</option>
                     </select><em class="TipIkona"><b><?php echo $parametr['INTEGRACJA_FURGONETKA_RODZAJ_USLUGI']['2']; ?></b></em>
                    </td>
                  </tr>
                  <?php unset($domyslna); ?>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_furgonetka_wymiary_dlugosc">Preferowane wymiary przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo 'długość: <input type="text" name="integracja_furgonetka_wymiary_dlugosc" id="integracja_furgonetka_wymiary_dlugosc" value="'.$parametr['INTEGRACJA_FURGONETKA_WYMIARY_DLUGOSC']['0'].'" size="12" />';
                      echo ' &nbsp; szerokość: <input type="text" name="integracja_furgonetka_wymiary_szerokosc" id="integracja_furgonetka_wymiary_szerokosc" value="'.$parametr['INTEGRACJA_FURGONETKA_WYMIARY_SZEROKOSC']['0'].'" size="12" />';
                      echo ' &nbsp; wysokość: <input type="text" name="integracja_furgonetka_wymiary_wysokosc" id="integracja_furgonetka_wymiary_wysokosc" value="'.$parametr['INTEGRACJA_FURGONETKA_WYMIARY_WYSOKOSC']['0'].'" size="12" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_furgonetka_zawartosc">Domyślna zawartość przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FURGONETKA_ZAWARTOSC']['1'], $parametr['INTEGRACJA_FURGONETKA_ZAWARTOSC']['0'], 'integracja_furgonetka_zawartosc', $parametr['INTEGRACJA_FURGONETKA_ZAWARTOSC']['2'], '', $parametr['INTEGRACJA_FURGONETKA_ZAWARTOSC']['3'], '', '', 'id="integracja_furgonetka_zawartosc"' );
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_furgonetka_numer_konta">Rachunek bankowy pobrania:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_furgonetka_numer_konta" name="integracja_furgonetka_numer_konta" value="'.$parametr['INTEGRACJA_FURGONETKA_NUMER_KONTA']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FURGONETKA_NUMER_KONTA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_furgonetka_nazwa_konta">Właścicel rachunku bankowego:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_furgonetka_nazwa_konta" name="integracja_furgonetka_nazwa_konta" value="'.$parametr['INTEGRACJA_FURGONETKA_NAZWA_KONTA']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FURGONETKA_NAZWA_KONTA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_furgonetka_nadawca_firma">Nazwa firmy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_furgonetka_nadawca_firma" name="integracja_furgonetka_nadawca_firma" value="'.$parametr['INTEGRACJA_FURGONETKA_NADAWCA_FIRMA']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FURGONETKA_NADAWCA_FIRMA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required"  for="integracja_furgonetka_nadawca_imie">Imię:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_furgonetka_nadawca_imie" id="integracja_furgonetka_nadawca_imie" value="'.$parametr['INTEGRACJA_FURGONETKA_NADAWCA_IMIE']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FURGONETKA_NADAWCA_IMIE']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required"  for="integracja_furgonetka_nadawca_imie">Nazwisko</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_furgonetka_nadawca_nazwisko" value="'.$parametr['INTEGRACJA_FURGONETKA_NADAWCA_NAZWISKO']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FURGONETKA_NADAWCA_NAZWISKO']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_furgonetka_nadawca_ulica">Ulica i numer domu:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_furgonetka_nadawca_ulica" id="integracja_furgonetka_nadawca_ulica" value="'.$parametr['INTEGRACJA_FURGONETKA_NADAWCA_ULICA']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FURGONETKA_NADAWCA_ULICA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_furgonetka_nadawca_kod_pocztowy">Kod pocztowy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_furgonetka_nadawca_kod_pocztowy" id="integracja_furgonetka_nadawca_kod_pocztowy" value="'.$parametr['INTEGRACJA_FURGONETKA_NADAWCA_KOD_POCZTOWY']['0'].'" size="25" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FURGONETKA_NADAWCA_KOD_POCZTOWY']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_furgonetka_nadawca_kod_pocztowy">Miejscowość:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_furgonetka_nadawca_miasto" id="integracja_furgonetka_nadawca_miasto" value="'.$parametr['INTEGRACJA_FURGONETKA_NADAWCA_MIASTO']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FURGONETKA_NADAWCA_MIASTO']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_furgonetka_nadawca_kraj">Kraj:</label>
                    </td>
                    <td>
                    <?php
                    $domyslna = $parametr['INTEGRACJA_FURGONETKA_NADAWCA_KRAJ']['0'];
                    ?>
                    <select name="integracja_furgonetka_nadawca_kraj" id="integracja_furgonetka_nadawca_kraj">
                        <option value="PL">Polska</option>
                     </select>
                    </td>
                  </tr>
                  <?php unset($domyslna); ?>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_furgonetka_nadawca_email">Adres e-mail:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_furgonetka_nadawca_email" id="integracja_furgonetka_nadawca_email" value="'.$parametr['INTEGRACJA_FURGONETKA_NADAWCA_EMAIL']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FURGONETKA_NADAWCA_EMAIL']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_furgonetka_nadawca_telefon">Numer telefonu:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_furgonetka_nadawca_telefon" id="integracja_furgonetka_nadawca_telefon" value="'.$parametr['INTEGRACJA_FURGONETKA_NADAWCA_TELEFON']['0'].'" size="73" class="required" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FURGONETKA_NADAWCA_TELEFON']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  

                  <tr class="SledzeniePozycja">
                    <td>
                        <label for="integracja_furgonetka_inpost">Paczkomat, w którym będą nadawane przesyłki:</label>
                    </td>
                    <td>
                        <?php
                        echo '<input type="text" class="przyciskPaczkomatu" value="Wybierz paczkomat" readonly="readonly" onclick="PokazMape(\'inpost\')" />';

                        echo '<input type="text" id="integracja_furgonetka_inpost" name="integracja_furgonetka_inpost" value="'.$parametr['INTEGRACJA_FURGONETKA_INPOST']['0'].'" size="20" readonly="readonly" /><em class="TipIkona"><b>'. strip_tags((string)$parametr['INTEGRACJA_FURGONETKA_INPOST']['2']).'</b></em>';

                        echo '<input type="text" id="preferowany_inpost" value="'.strip_tags((string)$parametr['INTEGRACJA_FURGONETKA_INPOST']['2']).'" name="preferowany_inpost" readonly="readonly"  style="margin-left:10px;" size="73" />';

                        ?>
                    </td>
                  </tr>


                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_furgonetka_punktnadania">Punkt Poczty, w którym będą nadawane przesyłki:</label>
                    </td>
                    <td>
                        <?php
                        echo '<input type="text" class="przyciskPaczkomatu" value="Wybierz punkt" readonly="readonly" onclick="PokazMape(\'poczta\')" />';

                        echo '<input type="text" id="integracja_furgonetka_poczta" name="integracja_furgonetka_poczta" value="'.$parametr['INTEGRACJA_FURGONETKA_POCZTA']['0'].'" size="20" readonly="readonly" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FURGONETKA_POCZTA']['2'].'</b></em>';

                        echo '<input type="text" id="preferowany_poczta" value="'.$parametr['INTEGRACJA_FURGONETKA_POCZTA']['2'].'" name="preferowany_poczta" readonly="readonly"  style="margin-left:10px;" size="73" />';

                        ?>
                    </td>
                  </tr>
                   
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_furgonetka_dpd">Punkt DPD, w którym będą nadawane przesyłki:</label>
                    </td>
                    <td>
                        <?php
                        echo '<input type="text" class="przyciskPaczkomatu" value="Wybierz punkt" readonly="readonly" onclick="PokazMape(\'dpd\')" />';

                        echo '<input type="text" id="integracja_furgonetka_dpd" name="integracja_furgonetka_dpd" value="'.$parametr['INTEGRACJA_FURGONETKA_DPD']['0'].'" size="20" readonly="readonly" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FURGONETKA_DPD']['2'].'</b></em>';

                        echo '<input type="text" id="preferowany_dpd" value="'.$parametr['INTEGRACJA_FURGONETKA_DPD']['2'].'" name="preferowany_dpd" readonly="readonly"  style="margin-left:10px;" size="73" />';

                        ?>
                    </td>
                  </tr>
                   
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_furgonetka_orlen">Punkt ORLEN, w którym będą nadawane przesyłki:</label>
                    </td>
                    <td>
                        <?php
                        echo '<input type="text" class="przyciskPaczkomatu" value="Wybierz punkt" readonly="readonly" onclick="PokazMape(\'ruch\')" />';

                        echo '<input type="text" id="integracja_furgonetka_orlen" name="integracja_furgonetka_ruch" value="'.$parametr['INTEGRACJA_FURGONETKA_RUCH']['0'].'" size="20" readonly="readonly" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FURGONETKA_RUCH']['2'].'</b></em>';

                        echo '<input type="text" id="preferowany_orlen" value="'.$parametr['INTEGRACJA_FURGONETKA_RUCH']['2'].'" name="preferowany_orlen" readonly="readonly"  style="margin-left:10px;" size="73" />';

                        ?>
                    </td>
                  </tr>
                   
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_furgonetka_ups">Punkt UPS, w którym będą nadawane przesyłki:</label>
                    </td>
                    <td>
                        <?php
                        echo '<input type="text" class="przyciskPaczkomatu" value="Wybierz punkt" readonly="readonly" onclick="PokazMape(\'ups\')" />';

                        echo '<input type="text" id="integracja_furgonetka_ups" name="integracja_furgonetka_ups" value="'.$parametr['INTEGRACJA_FURGONETKA_UPS']['0'].'" size="20" readonly="readonly" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FURGONETKA_UPS']['2'].'</b></em>';

                        echo '<input type="text" id="preferowany_ups" value="'.$parametr['INTEGRACJA_FURGONETKA_UPS']['2'].'" name="preferowany_ups" readonly="readonly"  style="margin-left:10px;" size="73" />';

                        ?>
                    </td>
                  </tr>
                   
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_furgonetka_fedex">Punkt FedEx, w którym będą nadawane przesyłki:</label>
                    </td>
                    <td>
                        <?php
                        echo '<input type="text" class="przyciskPaczkomatu" value="Wybierz punkt" readonly="readonly" onclick="PokazMape(\'fedex\')" />';

                        echo '<input type="text" id="integracja_furgonetka_fedex" name="integracja_furgonetka_fedex" value="'.$parametr['INTEGRACJA_FURGONETKA_FEDEX']['0'].'" size="20" readonly="readonly" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FURGONETKA_FEDEX']['2'].'</b></em>';

                        echo '<input type="text" id="preferowany_fedex" value="'.$parametr['INTEGRACJA_FURGONETKA_FEDEX']['2'].'" name="preferowany_fedex" readonly="readonly"  style="margin-left:10px;" size="73" />';

                        ?>
                    </td>
                  </tr>
                   
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_furgonetka_fedex">Punkt GLS, w którym będą nadawane przesyłki:</label>
                    </td>
                    <td>
                        <?php
                        echo '<input type="text" class="przyciskPaczkomatu" value="Wybierz punkt" readonly="readonly" onclick="PokazMape(\'gls\')" />';

                        echo '<input type="text" id="integracja_furgonetka_gls" name="integracja_furgonetka_gls" value="'.$parametr['INTEGRACJA_FURGONETKA_GLS']['0'].'" size="20" readonly="readonly" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FURGONETKA_GLS']['2'].'</b></em>';

                        echo '<input type="text" id="preferowany_gls" value="'.$parametr['INTEGRACJA_FURGONETKA_GLS']['2'].'" name="preferowany_gls" readonly="readonly"  style="margin-left:10px;" size="73" />';

                        ?>
                    </td>
                  </tr>
                   
                  <tr>
                    <td colspan="2">
                      <div class="przyciski_dolne">
                        <button type="button" class="przyciskNon" onclick="cofnij('konfiguracja_wysylki','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','integracje');">Powrót</button>
                        <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'furgonetka' ? $wynik : '' ); ?>
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
