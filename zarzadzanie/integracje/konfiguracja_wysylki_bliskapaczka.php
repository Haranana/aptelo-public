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
      $db->update_query('settings' , $pola, " code LIKE 'INTEGRACJA_BLISKAPACZKA_%'");	
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
              array('description',$filtr->process($_POST['preferowany_inpost']))
      );
      $db->update_query('settings' , $pola, " code = 'INTEGRACJA_BLISKAPACZKA_INPOST'");
      unset($pola);
      $pola = array(
              array('description',$filtr->process($_POST['preferowany_poczta']))
      );
      $db->update_query('settings' , $pola, " code = 'INTEGRACJA_BLISKAPACZKA_POCZTA'");
      unset($pola);
      $pola = array(
              array('description',$filtr->process($_POST['preferowany_dpd']))
      );
      $db->update_query('settings' , $pola, " code = 'INTEGRACJA_BLISKAPACZKA_DPD'");
      unset($pola);
      $pola = array(
              array('description',$filtr->process($_POST['preferowany_ruch']))
      );
      $db->update_query('settings' , $pola, " code = 'INTEGRACJA_BLISKAPACZKA_RUCH'");
      unset($pola);
      $pola = array(
              array('description',$filtr->process($_POST['preferowany_fedex']))
      );
      $db->update_query('settings' , $pola, " code = 'INTEGRACJA_BLISKAPACZKA_FEDEX'");
      unset($pola);
      $pola = array(
              array('description',$filtr->process($_POST['preferowany_ups']))
      );
      $db->update_query('settings' , $pola, " code = 'INTEGRACJA_BLISKAPACZKA_UPS'");
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
        <div class="naglowek">Edycja danych - Firma wysyłkowa Bliskapaczka</div>

        <div class="pozycja_edytowana"> 

          <div class="MapaUkryta" id="WybierzMape">
            <div id="WyborMapaWysylka">
                <div id="MapaKontener">
                    <div id="MapaZamknij">X</div>
                    <div id="WidokMapy"></div>
                </div>
            </div>
          </div>

          <script>
          $(document).ready(function() {
            $("#form-bliskapaczka").validate({
              rules: {
                integracja_bliskapaczka_api_key: {required: function() {var wynik = true; if ( $("input[name='integracja_bliskapaczka_wlaczony']:checked", "#form-bliskapaczka").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_bliskapaczka_api_google: {required: function() {var wynik = true; if ( $("input[name='integracja_bliskapaczka_wlaczony']:checked", "#form-bliskapaczka").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_bliskapaczka_osoba_nadajaca_imie: {required: function() {var wynik = true; if ( $("input[name='integracja_bliskapaczka_wlaczony']:checked", "#form-bliskapaczka").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_bliskapaczka_osoba_nadajaca_nazwisko: {required: function() {var wynik = true; if ( $("input[name='integracja_bliskapaczka_wlaczony']:checked", "#form-bliskapaczka").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_bliskapaczka_nadawca_kod_pocztowy: {required: function() {var wynik = true; if ( $("input[name='integracja_bliskapaczka_wlaczony']:checked", "#form-bliskapaczka").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_bliskapaczka_nadawca_miasto: {required: function() {var wynik = true; if ( $("input[name='integracja_bliskapaczka_wlaczony']:checked", "#form-bliskapaczka").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_bliskapaczka_nadawca_telefon: {required: function() {var wynik = true; if ( $("input[name='integracja_bliskapaczka_wlaczony']:checked", "#form-bliskapaczka").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_bliskapaczka_nadawca_email: {required: function() {var wynik = true; if ( $("input[name='integracja_bliskapaczka_wlaczony']:checked", "#form-bliskapaczka").val() == "nie" ) { wynik = false; } return wynik; }},

                }
            });

            setTimeout(function() {
              $('#<?php echo $system; ?>').fadeOut();
            }, 3000);

          });
          </script>  

          <script type="text/javascript" src="https://widget.bliskapaczka.pl/v8.1/main.js"></script>
          <link rel="stylesheet" href="https://widget.bliskapaczka.pl/v8.1/main.css" />

          <script>
            $(document).ready(function() {
              

                $("#WybierzMape").css({
                    top: ( -210 ),
                    left: ( ($("#StronaPanel").outerWidth() - $("#MapaKontener").outerWidth()) / 2 )
                });

                $(window).scroll(function() {
                    var fromTop = $(window).scrollTop() - 210;

                    if ( $(window).scrollTop() > 0 ) {

                    $("#WybierzMape").css({
                        top: ( fromTop ),
                        left: ($("#StronaPanel").outerWidth() - $("#MapaKontener").outerWidth()) / 2
                    });
                    } else {
                    $("#WybierzMape").css({
                        top: ( -210 ),
                        left: ($("#StronaPanel").outerWidth() - $("#MapaKontener").outerWidth()) / 2
                    });

                    }

                });

                $('#MapaZamknij').click(function() {
                   $('#WybierzMape').removeClass('MapaWidoczna').addClass('MapaUkryta');
                   $("#BPWidget").remove();
                   $("#MapaWidocznaTlo").fadeOut(500, function() {
                       $("#MapaWidocznaTlo").remove();
                   });
                   enableScroll();
                });

            });

            function PokazMape(Kurier) {
                var str = Kurier;
                var dostawca = str.toLowerCase();
                disableScroll();
                $("#WybierzMape").append('<div id="MapaWidocznaTlo"></div>');
                $('#WybierzMape').removeClass('MapaUkryta').addClass('MapaWidoczna').show();
                BPWidget.init(
                        document.getElementById('WidokMapy'),
                        {
                            callback: function(point) {
                                $('#preferowany_' + dostawca + '').val(point.city + ' - ' + point.street);
                                $('#integracja_bliskapaczka_' + dostawca + '').val(point.code);
                                $('#WybierzMape').removeClass('MapaWidoczna').addClass('MapaUkryta');
                                $("#BPWidget").remove();
                                $("#MapaWidocznaTlo").fadeOut(500, function() {
                                   $("#MapaWidocznaTlo").remove();
                                });
                                enableScroll();
                            },
                            posType: 'DELIVERY',
                            operators: [{operator: Kurier}],
                            initialAddress: $('#integracja_bliskapaczka_nadawca_miasto').val()

                        }
                );

            }

            function disableScroll() { 
                scrollTop = window.pageYOffset || document.documentElement.scrollTop; 
                scrollLeft = window.pageXOffset || document.documentElement.scrollLeft, 
  
                window.onscroll = function() { 
                    window.scrollTo(scrollLeft, scrollTop); 
                }; 
            } 
  
            function enableScroll() { 
                window.onscroll = function() {}; 
            } 

          </script>

          <div class="Sledzenie">

            <form action="integracje/konfiguracja_wysylki_bliskapaczka.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" name="form_bliskapaczka" id="form-bliskapaczka" class="cmxform"> 
            
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="apaczka" />
                
                <table>
                
                  <tr><td colspan="2" class="SledzenieOpis">
                    <div> Bliskapaczka.pl. Przesyłki w punkt. Wygodne nadawanie i odbieranie przesyłek w ponad 20000 punktów w całej Polsce.</div>
                    <img src="obrazki/logo/logo_bliskapaczka.png" alt="" />
                  </td></tr>                   

                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Włącz integrację Bliskapaczka:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_BLISKAPACZKA_WLACZONY']['1'], $parametr['INTEGRACJA_BLISKAPACZKA_WLACZONY']['0'], 'integracja_bliskapaczka_wlaczony', $parametr['INTEGRACJA_BLISKAPACZKA_WLACZONY']['2'], '', $parametr['INTEGRACJA_BLISKAPACZKA_WLACZONY']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Tryb testowy:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_BLISKAPACZKA_SANDBOX']['1'], $parametr['INTEGRACJA_BLISKAPACZKA_SANDBOX']['0'], 'integracja_bliskapaczka_sandbox', $parametr['INTEGRACJA_BLISKAPACZKA_SANDBOX']['2'], '', $parametr['INTEGRACJA_BLISKAPACZKA_SANDBOX']['3'] );
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_bliskapaczka_api_key">Klucz API:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_bliskapaczka_api_key" id="integracja_bliskapaczka_api_key" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_API_KEY']['0'].'" size="73" /><em class="TipIkona"><b>Indywidualny klucz umożliwiający dostęp do API bliskapaczka.pl</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_bliskapaczka_numer_konta">Rachunek bankowy pobrania:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_bliskapaczka_numer_konta" name="integracja_bliskapaczka_numer_konta" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_NUMER_KONTA']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_BLISKAPACZKA_NUMER_KONTA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_bliskapaczka_wymiary_dlugosc">Preferowane wymiary przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo 'długość: <input type="text" name="integracja_bliskapaczka_wymiary_dlugosc" id="integracja_bliskapaczka_wymiary_dlugosc" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_WYMIARY_DLUGOSC']['0'].'" size="12" />';
                      echo ' &nbsp; szerokość: <input type="text" name="integracja_bliskapaczka_wymiary_szerokosc" id="integracja_bliskapaczka_wymiary_szerokosc" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_WYMIARY_SZEROKOSC']['0'].'" size="12" />';
                      echo ' &nbsp; wysokość: <input type="text" name="integracja_bliskapaczka_wymiary_wysokosc" id="integracja_bliskapaczka_wymiary_wysokosc" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_WYMIARY_WYSOKOSC']['0'].'" size="12" />';
                      echo ' &nbsp; waga: <input type="text" name="integracja_bliskapaczka_wymiary_waga" id="integracja_bliskapaczka_wymiary_waga" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_WYMIARY_WAGA']['0'].'" size="12" />';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_bliskapaczka_zawartosc">Informacje dodatkowe (opcjonalne):</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_BLISKAPACZKA_ZAWARTOSC']['1'], $parametr['INTEGRACJA_BLISKAPACZKA_ZAWARTOSC']['0'], 'integracja_bliskapaczka_zawartosc', $parametr['INTEGRACJA_BLISKAPACZKA_ZAWARTOSC']['2'], '', $parametr['INTEGRACJA_BLISKAPACZKA_ZAWARTOSC']['3'], '', '', 'id="integracja_bliskapaczka_zawartosc"' );
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_bliskapaczka_inpost">Paczkomat, w którym będą nadawane przesyłki:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" class="przyciskPaczkomatu" value="Wybierz paczkomat" readonly="readonly" onclick="PokazMape(\'INPOST\')" />';

                          echo '<input type="text" id="integracja_bliskapaczka_inpost" name="integracja_bliskapaczka_inpost" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_INPOST']['0'].'" size="20" readonly="readonly" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_BLISKAPACZKA_INPOST']['2'].'</b></em>';

                          echo '<input type="text" id="preferowany_inpost" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_INPOST']['2'].'" name="preferowany_inpost" readonly="readonly"  style="margin-left:10px;" size="73" />';

                          ?>
                        </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_bliskapaczka_poczta">Punkt Poczty, w którym będą nadawane przesyłki:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" class="przyciskPaczkomatu" value="Wybierz punkt" readonly="readonly" onclick="PokazMape(\'POCZTA\')" />';

                          echo '<input type="text" id="integracja_bliskapaczka_poczta" name="integracja_bliskapaczka_poczta" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_POCZTA']['0'].'" size="20" readonly="readonly" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_BLISKAPACZKA_POCZTA']['2'].'</b></em>';

                          echo '<input type="text" id="preferowany_poczta" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_POCZTA']['2'].'" name="preferowany_poczta" readonly="readonly"  style="margin-left:10px;" size="73" />';

                          ?>
                        </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_bliskapaczka_dpd">Punkt DPD, w którym będą nadawane przesyłki:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" class="przyciskPaczkomatu" value="Wybierz punkt" readonly="readonly" onclick="PokazMape(\'DPD\')" />';

                          echo '<input type="text" id="integracja_bliskapaczka_dpd" name="integracja_bliskapaczka_dpd" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_DPD']['0'].'" size="20" readonly="readonly" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_BLISKAPACZKA_DPD']['2'].'</b></em>';

                          echo '<input type="text" id="preferowany_dpd" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_DPD']['2'].'" name="preferowany_dpd" readonly="readonly"  style="margin-left:10px;" size="73" />';

                          ?>
                        </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_bliskapaczka_ruch">Punkt RUCH, w którym będą nadawane przesyłki:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" class="przyciskPaczkomatu" value="Wybierz punkt" readonly="readonly" onclick="PokazMape(\'RUCH\')" />';

                          echo '<input type="text" id="integracja_bliskapaczka_ruch" name="integracja_bliskapaczka_ruch" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_RUCH']['0'].'" size="20" readonly="readonly" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_BLISKAPACZKA_RUCH']['2'].'</b></em>';

                          echo '<input type="text" id="preferowany_ruch" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_RUCH']['2'].'" name="preferowany_ruch" readonly="readonly"  style="margin-left:10px;" size="73" />';

                          ?>
                        </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_bliskapaczka_ups">Punkt UPS, w którym będą nadawane przesyłki:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" class="przyciskPaczkomatu" value="Wybierz punkt" readonly="readonly" onclick="PokazMape(\'UPS\')" />';

                          echo '<input type="text" id="integracja_bliskapaczka_ups" name="integracja_bliskapaczka_ups" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_UPS']['0'].'" size="20" readonly="readonly" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_BLISKAPACZKA_UPS']['2'].'</b></em>';

                          echo '<input type="text" id="preferowany_ups" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_UPS']['2'].'" name="preferowany_ups" readonly="readonly"  style="margin-left:10px;" size="73" />';

                          ?>
                        </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                        <td>
                          <label for="integracja_bliskapaczka_fedex">Punkt FEDEX, w którym będą nadawane przesyłki:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" class="przyciskPaczkomatu" value="Wybierz punkt" readonly="readonly" onclick="PokazMape(\'FEDEX\')" />';

                          echo '<input type="text" id="integracja_bliskapaczka_fedex" name="integracja_bliskapaczka_fedex" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_FEDEX']['0'].'" size="20" readonly="readonly" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_BLISKAPACZKA_FEDEX']['2'].'</b></em>';

                          echo '<input type="text" id="preferowany_fedex" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_FEDEX']['2'].'" name="preferowany_fedex" readonly="readonly"  style="margin-left:10px;" size="73" />';

                          ?>
                        </td>
                  </tr>


                  <tr><td colspan="2">
                  <div id="bpWidget" style="height:800px; width:1024px; display:none;"></div>
                  </td></tr>
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_bliskapaczka_osoba_nadajaca_firma">Firma nadawcy (opcjonalnie):</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_bliskapaczka_osoba_nadajaca_firma" id="integracja_bliskapaczka_osoba_nadajaca_firma" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_OSOBA_NADAJACA_FIRMA']['0'].'" size="73" maxlength="50" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_BLISKAPACZKA_OSOBA_NADAJACA_FIRMA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_bliskapaczka_osoba_nadajaca_imie">Imię nadawcy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_bliskapaczka_osoba_nadajaca_imie" id="integracja_bliskapaczka_osoba_nadajaca_imie" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_OSOBA_NADAJACA_IMIE']['0'].'" size="73" maxlength="30" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_BLISKAPACZKA_OSOBA_NADAJACA_IMIE']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_bliskapaczka_osoba_nadajaca_nazwisko">Nazwisko nadawcy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_bliskapaczka_osoba_nadajaca_nazwisko" id="integracja_bliskapaczka_osoba_nadajaca_nazwisko" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_OSOBA_NADAJACA_NAZWISKO']['0'].'" size="73" maxlength="30" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_BLISKAPACZKA_OSOBA_NADAJACA_NAZWISKO']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_bliskapaczka_nadawca_ulica">Ulica:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_bliskapaczka_nadawca_ulica" id="integracja_bliskapaczka_nadawca_ulica" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_NADAWCA_ULICA']['0'].'" size="50" maxlength="30" />';
                      echo ' &nbsp; Numer budynku: <input type="text" name="integracja_bliskapaczka_nadawca_dom" id="integracja_bliskapaczka_nadawca_dom" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_NADAWCA_DOM']['0'].'" size="12" maxlength="10" />';
                      echo ' &nbsp; Numer mieszkania (opcjonalnie): <input type="text" name="integracja_bliskapaczka_nadawca_mieszkanie" id="integracja_bliskapaczka_nadawca_mieszkanie" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_NADAWCA_MIESZKANIE']['0'].'" size="12" maxlength="10" />';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_bliskapaczka_nadawca_kod_pocztowy">Kod pocztowy nadawcy przesyłek:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_bliskapaczka_nadawca_kod_pocztowy" id="integracja_bliskapaczka_nadawca_kod_pocztowy" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_NADAWCA_KOD_POCZTOWY']['0'].'" size="73" maxlength="30" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_BLISKAPACZKA_NADAWCA_KOD_POCZTOWY']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_bliskapaczka_nadawca_miasto">Miasto nadawcy przesyłek:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_bliskapaczka_nadawca_miasto" id="integracja_bliskapaczka_nadawca_miasto" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_NADAWCA_MIASTO']['0'].'" size="73" maxlength="30" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_BLISKAPACZKA_NADAWCA_MIASTO']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_bliskapaczka_nadawca_email">E-mail nadawcy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_bliskapaczka_nadawca_email" id="integracja_bliskapaczka_nadawca_email" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_NADAWCA_EMAIL']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_BLISKAPACZKA_NADAWCA_EMAIL']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_bliskapaczka_nadawca_telefon">Numer telefonu nadawcy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_bliskapaczka_nadawca_telefon" id="integracja_bliskapaczka_nadawca_telefon" value="'.$parametr['INTEGRACJA_BLISKAPACZKA_NADAWCA_TELEFON']['0'].'" size="73" maxlength="30" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_BLISKAPACZKA_NADAWCA_TELEFON']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr>
                    <td colspan="2">
                      <div class="przyciski_dolne">
                        <button type="button" class="przyciskNon" onclick="cofnij('konfiguracja_wysylki','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','integracje');">Powrót</button>
                        <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'apaczka' ? $wynik : '' ); ?>
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
