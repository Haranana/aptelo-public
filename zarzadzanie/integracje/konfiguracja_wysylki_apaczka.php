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
      $db->update_query('settings' , $pola, " code LIKE 'INTEGRACJA_APACZKAV2_%'");	
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
      $db->update_query('settings' , $pola, " code = 'INTEGRACJA_APACZKAV2_POINT_INPOST'");
      unset($pola);
      $pola = array(
              array('description',$filtr->process($_POST['preferowany_ups']))
      );
      $db->update_query('settings' , $pola, " code = 'INTEGRACJA_APACZKAV2_POINT_UPS'");
      unset($pola);
      $pola = array(
              array('description',$filtr->process($_POST['preferowany_poczta']))
      );
      $db->update_query('settings' , $pola, " code = 'INTEGRACJA_APACZKAV2_POINT_POCZTA'");
      unset($pola);
      $pola = array(
              array('description',$filtr->process($_POST['preferowany_dpd']))
      );
      $db->update_query('settings' , $pola, " code = 'INTEGRACJA_APACZKAV2_POINT_DPD'");
      unset($pola);
      $pola = array(
              array('description',$filtr->process($_POST['preferowany_dhl_parcel']))
      );
      $db->update_query('settings' , $pola, " code = 'INTEGRACJA_APACZKAV2_POINT_DHL_PARCEL'");
      unset($pola);
      $pola = array(
              array('description',$filtr->process($_POST['preferowany_pwr']))
      );
      $db->update_query('settings' , $pola, " code = 'INTEGRACJA_APACZKAV2_POINT_ORLEN'");
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
        <div class="naglowek">Edycja danych - Firma wysyłkowa apaczka V2</div>

        <div class="pozycja_edytowana"> 

          <script>
          $(document).ready(function() {
            $("#form-apaczka").validate({
              rules: {
                integracja_apaczkav2_app_id: {required: function() {var wynik = true; if ( $("input[name='integracja_apaczkav2_wlaczony']:checked", "#form-apaczka").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_apaczkav2_app_secret: {required: function() {var wynik = true; if ( $("input[name='integracja_apaczkav2_wlaczony']:checked", "#form-apaczka").val() == "nie" ) { wynik = false; } return wynik; }},

                integracja_apaczkav2_nadawca_nazwa: {required: function() {var wynik = true; if ( $("input[name='integracja_apaczkav2_wlaczony']:checked", "#form-apaczka").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_apaczkav2_nadawca_adres1: {required: function() {var wynik = true; if ( $("input[name='integracja_apaczkav2_wlaczony']:checked", "#form-apaczka").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_apaczkav2_nadawca_kod_pocztowy: {required: function() {var wynik = true; if ( $("input[name='integracja_apaczkav2_wlaczony']:checked", "#form-apaczka").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_apaczkav2_nadawca_miasto: {required: function() {var wynik = true; if ( $("input[name='integracja_apaczkav2_wlaczony']:checked", "#form-apaczka").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_apaczkav2_nadawca_telefon: {required: function() {var wynik = true; if ( $("input[name='integracja_apaczkav2_wlaczony']:checked", "#form-apaczka").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_apaczkav2_nadawca_email: {required: function() {var wynik = true; if ( $("input[name='integracja_apaczkav2_wlaczony']:checked", "#form-apaczka").val() == "nie" ) { wynik = false; } return wynik; }},

                }
            });

            setTimeout(function() {
              $('#<?php echo $system; ?>').fadeOut();
            }, 3000);

          });
          </script>  

          <script src="https://mapa.apaczka.pl/client/apaczka.map.js"></script>
          <style>
          .apaczkaMapPopup .popupButtons { z-index:99; }
          </style>

          <script>
            function PokazMape(Kurier) {
                var dostawca = Kurier;
                var kur = dostawca.toLowerCase();
                var app_id = $('#integracja_apaczkav2_app_id').val();

                var apaczkaMap = new ApaczkaMap({
                    app_id : app_id,
                    criteria : [
                        {field: 'services_sender', operator: 'eq', value: true}
                    ],
                    onChange : function( record) {
                        if (record) {
                            $('#preferowany_' + kur + '').val(record.name + ' ' + record.street + ', ' + record.postal_code + ' ' + record.city);
                            $('#integracja_apaczkav2_point_' + kur + '').val(record.foreign_access_point_id);
                        }
                    }
                });
                apaczkaMap.setSupplier(dostawca);
                apaczkaMap. setFilterSupplierAllowed(
                    ['DHL_PARCEL', 'DPD', 'INPOST', 'POCZTA', 'UPS', 'PWR'],
                    [dostawca]
                );
                apaczkaMap.show({});
            }
          </script>

          <div class="Sledzenie">

            <form action="integracje/konfiguracja_wysylki_apaczka.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" name="form_apaczka" id="form-apaczka" class="cmxform"> 

                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="apaczka" />
                
                <table>
                
                  <tr><td colspan="2" class="SledzenieOpis">
                    <div>Integracja z firmą apaczka daje możliwość składania zleceń na przesyłki kurierskie BEZPOŚREDNIO z panelu zarządzania sklepu, dane odbiorców są automatycznie eksportowane do formularza wysyłkowego, co pozwala oszczędzić czas potrzebny na wpisywanie danych.</div>
                    <img src="obrazki/logo/logo_apaczka.png" alt="" />
                  </td></tr>                   

                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Włącz integrację Apaczka:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_APACZKAV2_WLACZONY']['1'], $parametr['INTEGRACJA_APACZKAV2_WLACZONY']['0'], 'integracja_apaczkav2_wlaczony', $parametr['INTEGRACJA_APACZKAV2_WLACZONY']['2'], '', $parametr['INTEGRACJA_APACZKAV2_WLACZONY']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_apaczkav2_app_id">App ID w apaczka:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_apaczkav2_app_id" id="integracja_apaczkav2_app_id" value="'.$parametr['INTEGRACJA_APACZKAV2_APP_ID']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_APACZKAV2_APP_ID']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_apaczkav2_app_secret">App Secret w serwisie apaczka:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_apaczkav2_app_secret" id="integracja_apaczkav2_app_secret" value="'.$parametr['INTEGRACJA_APACZKAV2_APP_SECRET']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_APACZKAV2_APP_SECRET']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_apaczkav2_osoba_nadajaca">Imię i nazwisko osoby kontaktowej:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_apaczkav2_osoba_nadajaca" id="integracja_apaczkav2_osoba_nadajaca" value="'.$parametr['INTEGRACJA_APACZKAV2_OSOBA_NADAJACA']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_APACZKAV2_OSOBA_NADAJACA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_apaczkav2_numer_konta">Rachunek bankowy pobrania:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_apaczkav2_numer_konta" name="integracja_apaczkav2_numer_konta" value="'.$parametr['INTEGRACJA_APACZKAV2_NUMER_KONTA']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_APACZKAV2_NUMER_KONTA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_apaczkav2_wymiary_dlugosc">Preferowane wymiary przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo 'długość: <input type="text" name="integracja_apaczkav2_wymiary_dlugosc" id="integracja_apaczkav2_wymiary_dlugosc" value="'.$parametr['INTEGRACJA_APACZKAV2_WYMIARY_DLUGOSC']['0'].'" size="12" />';
                      echo ' &nbsp; szerokość: <input type="text" name="integracja_apaczkav2_wymiary_szerokosc" id="integracja_apaczkav2_wymiary_szerokosc" value="'.$parametr['INTEGRACJA_APACZKAV2_WYMIARY_SZEROKOSC']['0'].'" size="12" />';
                      echo ' &nbsp; wysokość: <input type="text" name="integracja_apaczkav2_wymiary_wysokosc" id="integracja_apaczkav2_wymiary_wysokosc" value="'.$parametr['INTEGRACJA_APACZKAV2_WYMIARY_WYSOKOSC']['0'].'" size="12" />';
                      echo ' &nbsp; waga: <input type="text" name="integracja_apaczkav2_wymiary_waga" id="integracja_apaczkav2_wymiary_waga" value="'.$parametr['INTEGRACJA_APACZKAV2_WYMIARY_WAGA']['0'].'" size="12" />';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_apaczkav2_zawartosc">Domyślna zawartość przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_APACZKAV2_ZAWARTOSC']['1'], $parametr['INTEGRACJA_APACZKAV2_ZAWARTOSC']['0'], 'integracja_apaczkav2_zawartosc', $parametr['INTEGRACJA_APACZKAV2_ZAWARTOSC']['2'], '', $parametr['INTEGRACJA_APACZKAV2_ZAWARTOSC']['3'], '', '', 'id="integracja_apaczkav2_zawartosc"' );
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Typ adresu:</label>
                    </td>
                    <td>
                      <input type="radio" name="integracja_apaczkav2_nadawca_typ_adresu" id="pole_integracja_apaczkav2_nadawca_typ_adresu_0" value="0" <?php echo ( $parametr['INTEGRACJA_APACZKAV2_NADAWCA_TYP_ADRESU']['0'] == '0' ? 'checked="checked"' : '' ); ?> ><label class="OpisFor" for="pole_integracja_apaczkav2_nadawca_typ_adresu_0">Firmowy</label>
                      <input type="radio" name="integracja_apaczkav2_nadawca_typ_adresu" id="pole_integracja_apaczkav2_nadawca_typ_adresu_1" value="1" <?php echo ( $parametr['INTEGRACJA_APACZKAV2_NADAWCA_TYP_ADRESU']['0'] == '1' ? 'checked="checked"' : '' ); ?>><label class="OpisFor" for="pole_integracja_apaczkav2_nadawca_typ_adresu_1">Prywatny</label>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_apaczkav2_nadawca_nazwa">Nazwa nadawcy (nazwa firmy):</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_apaczkav2_nadawca_nazwa" id="integracja_apaczkav2_nadawca_nazwa" value="'.$parametr['INTEGRACJA_APACZKAV2_NADAWCA_NAZWA']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_APACZKAV2_NADAWCA_NAZWA']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_apaczkav2_nadawca_adres1">Pierwsza linia adresu:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_apaczkav2_nadawca_adres1" id="integracja_apaczkav2_nadawca_adres1" value="'.$parametr['INTEGRACJA_APACZKAV2_NADAWCA_ADRES1']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_APACZKAV2_NADAWCA_ADRES1']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label for="integracja_apaczkav2_nadawca_adres2">Druga linia adresu:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_apaczkav2_nadawca_adres2" id="integracja_apaczkav2_nadawca_adres2" value="'.$parametr['INTEGRACJA_APACZKAV2_NADAWCA_ADRES2']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_APACZKAV2_NADAWCA_ADRES2']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_apaczkav2_nadawca_kod_pocztowy">Kod pocztowy nadawcy przesyłek:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_apaczkav2_nadawca_kod_pocztowy" id="integracja_apaczkav2_nadawca_kod_pocztowy" value="'.$parametr['INTEGRACJA_APACZKAV2_NADAWCA_KOD_POCZTOWY']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_APACZKAV2_NADAWCA_KOD_POCZTOWY']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_apaczkav2_nadawca_miasto">Miasto nadawcy przesyłek:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_apaczkav2_nadawca_miasto" id="integracja_apaczkav2_nadawca_miasto" value="'.$parametr['INTEGRACJA_APACZKAV2_NADAWCA_MIASTO']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_APACZKAV2_NADAWCA_MIASTO']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_apaczkav2_nadawca_email">E-mail nadawcy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_apaczkav2_nadawca_email" id="integracja_apaczkav2_nadawca_email" value="'.$parametr['INTEGRACJA_APACZKAV2_NADAWCA_EMAIL']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_APACZKAV2_NADAWCA_EMAIL']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label class="required" for="integracja_apaczkav2_nadawca_telefon">Numer telefonu nadawcy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_apaczkav2_nadawca_telefon" id="integracja_apaczkav2_nadawca_telefon" value="'.$parametr['INTEGRACJA_APACZKAV2_NADAWCA_TELEFON']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_APACZKAV2_NADAWCA_TELEFON']['2'].'</b></em>';
                      ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                        <label for="integracja_apaczkav2_point_inpost">Paczkomat, w którym będą nadawane przesyłki:</label>
                    </td>
                    <td>
                        <?php
                        echo '<input type="text" class="przyciskPaczkomatu" value="Wybierz paczkomat" readonly="readonly" onclick="PokazMape(\'INPOST\')" />';
                        echo '<input type="text" id="integracja_apaczkav2_point_inpost" name="integracja_apaczkav2_point_inpost" value="'.$parametr['INTEGRACJA_APACZKAV2_POINT_INPOST']['0'].'" size="20" /></em>';
                        echo '<input type="text" id="preferowany_inpost" value="'.$parametr['INTEGRACJA_APACZKAV2_POINT_INPOST']['2'].'" name="preferowany_inpost" style="margin-left:10px;" size="73" />';
                        ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                        <label for="integracja_apaczkav2_point_ups">Punkt UPS, w którym będą nadawane przesyłki:</label>
                    </td>
                    <td>
                        <?php
                        echo '<input type="text" class="przyciskPaczkomatu" value="Wybierz punkt" readonly="readonly" onclick="PokazMape(\'UPS\')" />';
                        echo '<input type="text" id="integracja_apaczkav2_point_ups" name="integracja_apaczkav2_point_ups" value="'.$parametr['INTEGRACJA_APACZKAV2_POINT_UPS']['0'].'" size="20" /></em>';
                        echo '<input type="text" id="preferowany_ups" value="'.$parametr['INTEGRACJA_APACZKAV2_POINT_UPS']['2'].'" name="preferowany_ups" style="margin-left:10px;" size="73" />';
                        ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                        <label for="integracja_apaczkav2_point_dhl_parcel">Punkt DHL, w którym będą nadawane przesyłki:</label>
                    </td>
                    <td>
                        <?php
                        echo '<input type="text" class="przyciskPaczkomatu" value="Wybierz punkt" readonly="readonly" onclick="PokazMape(\'DHL_PARCEL\')" />';
                        echo '<input type="text" id="integracja_apaczkav2_point_dhl_parcel" name="integracja_apaczkav2_point_dhl_parcel" value="'.$parametr['INTEGRACJA_APACZKAV2_POINT_DHL_PARCEL']['0'].'" size="20" /></em>';
                        echo '<input type="text" id="preferowany_dhl_parcel" value="'.$parametr['INTEGRACJA_APACZKAV2_POINT_DHL_PARCEL']['2'].'" name="preferowany_dhl_parcel" style="margin-left:10px;" size="73" />';
                        ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                        <label for="integracja_apaczkav2_point_dpd">Punkt DPD, w którym będą nadawane przesyłki:</label>
                    </td>
                    <td>
                        <?php
                        echo '<input type="text" class="przyciskPaczkomatu" value="Wybierz punkt" readonly="readonly" onclick="PokazMape(\'DPD\')" />';
                        echo '<input type="text" id="integracja_apaczkav2_point_dpd" name="integracja_apaczkav2_point_dpd" value="'.$parametr['INTEGRACJA_APACZKAV2_POINT_DPD']['0'].'" size="20" /></em>';
                        echo '<input type="text" id="preferowany_dpd" value="'.$parametr['INTEGRACJA_APACZKAV2_POINT_DPD']['2'].'" name="preferowany_dpd" style="margin-left:10px;" size="73" />';
                        ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                        <label for="integracja_apaczkav2_point_poczta">Punkt Poczty, w którym będą nadawane przesyłki:</label>
                    </td>
                    <td>
                        <?php
                        echo '<input type="text" class="przyciskPaczkomatu" value="Wybierz punkt" readonly="readonly" onclick="PokazMape(\'POCZTA\')" />';
                        echo '<input type="text" id="integracja_apaczkav2_point_poczta" name="integracja_apaczkav2_point_poczta" value="'.$parametr['INTEGRACJA_APACZKAV2_POINT_POCZTA']['0'].'" size="20" /></em>';
                        echo '<input type="text" id="preferowany_poczta" value="'.$parametr['INTEGRACJA_APACZKAV2_POINT_POCZTA']['2'].'" name="preferowany_poczta" style="margin-left:10px;" size="73" />';
                        ?>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                        <label for="integracja_apaczkav2_point_poczta">Punkt ORLEN, w którym będą nadawane przesyłki:</label>
                    </td>
                    <td>
                        <?php
                        echo '<input type="text" class="przyciskPaczkomatu" value="Wybierz punkt" readonly="readonly" onclick="PokazMape(\'PWR\')" />';
                        echo '<input type="text" id="integracja_apaczkav2_point_pwr" name="integracja_apaczkav2_point_orlen" value="'.$parametr['INTEGRACJA_APACZKAV2_POINT_ORLEN']['0'].'" size="20" /></em>';
                        echo '<input type="text" id="preferowany_pwr" value="'.$parametr['INTEGRACJA_APACZKAV2_POINT_ORLEN']['2'].'" name="preferowany_pwr" style="margin-left:10px;" size="73" />';
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
