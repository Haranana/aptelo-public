<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $komunikat = '';
    $api = 'KurierInpost';
    $apiKurier = new InPostKurierApi();
    
    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      $weight_total      = 0;
      $ShipFrom          = array();
      $ShipTo            = array();
      $COD               = array();
      $Parcels['Parcel'] = array();

      $DaneWejsciowe = array();

      $ShipFrom = array (
                        'Address'      => $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_NADAWCA_ULICA'],
                        'City'         => $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_NADAWCA_MIASTO'],
                        'Contact'      => $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_NADAWCA_TELEFON'],
                        'CountryCode'  => 'PL',
                        'Email'        => $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_NADAWCA_EMAIL'],
                        'Name'         => $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_NADAWCA_NAZWA'],
                        'Person'       => $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_NADAWCA_IMIE_NAZWISKO'],
                        'PostCode'     => $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_NADAWCA_KOD_POCZTOWY']
      );
      $ShipTo   = array (
                        'IsPrivatePerson' => ( isset($_POST['odbiorca_firma']) ? true : false ),
                        'Address'         => $_POST['adresat_ulica'],
                        'City'            => $_POST['adresat_miasto'],
                        'Contact'         => $_POST['adresat_telefon'],
                        'CountryCode'     => $_POST['receiver_country_code'],
                        'Email'           => $_POST['adresat_mail'],
                        'Name'            => $_POST['adresat_firma'],
                        'Person'          => $_POST['adresat_nazwisko_i_imie'],
                        'PostCode'        => $_POST['adresat_kod_pocztowy']
      );

      if ( isset($_POST['pobranie']) ) {
        $COD = array (
                      'Amount'            => $_POST['inpost_pobranie'],
                      'RetAccountNo'      => str_replace(array(' ', '-'), array('', ''), (string)$_POST['inpost_numer_konta'])
        );
      }

      for ( $i = 0, $c = count($_POST['parcel']['dlugosc']); $i < $c; $i++ ) {

        $Parcel['Type']       = $_POST['parcel']['typ'][$i];
        $Parcel['D']          = $_POST['parcel']['dlugosc'][$i];
        $Parcel['S']          = $_POST['parcel']['szerokosc'][$i];
        $Parcel['W']          = $_POST['parcel']['wysokosc'][$i];
        $Parcel['Weight']     = $_POST['parcel']['waga'][$i];
        if ( isset($_POST['parcel']['standard'][$i]) && $_POST['parcel']['standard'][$i] == '1' ) {
            $Parcel['IsNST']      = 'true';
        }
        array_push($Parcels['Parcel'], $Parcel);

        $weight_total = $weight_total + $_POST['parcel']['waga'][$i];
      }

      $DaneWejsciowe['ShipmentRequest']['ServiceId'] = $_POST['service'];
      $DaneWejsciowe['ShipmentRequest']['ReadyDate'] = time();
      $DaneWejsciowe['ShipmentRequest']['LabelFormat'] = $_POST['format_etykiety'];
      $DaneWejsciowe['ShipmentRequest']['ShipFrom'] = $ShipFrom;
      $DaneWejsciowe['ShipmentRequest']['ShipTo'] = $ShipTo;
      $DaneWejsciowe['ShipmentRequest']['Parcels'] = $Parcels;
      if ( isset($_POST['pobranie']) ) {
        $DaneWejsciowe['ShipmentRequest']['COD'] = $COD;
      }
      if ( isset($_POST['u_ubezp']) ) {
        $DaneWejsciowe['ShipmentRequest']['InsuranceAmount'] = $_POST['u_wart_ubezp'];
      } else {
        $DaneWejsciowe['ShipmentRequest']['InsuranceAmount'] = 0;
      }

      $DaneWejsciowe['ShipmentRequest']['ContentDescription'] = $_POST['ubezpieczenie_opis'];

      $DaneWejsciowe['ShipmentRequest']['AdditionalServices']['AdditionalService'] = array();
      /*
      $DodatkoweUslugiSMS = array('Code' =>  'SMS');
      array_push($DaneWejsciowe['ShipmentRequest']['AdditionalServices']['AdditionalService'], $DodatkoweUslugiSMS);
      $DodatkoweUslugiEMAIL = array('Code' =>  'EMAIL');
      array_push($DaneWejsciowe['ShipmentRequest']['AdditionalServices']['AdditionalService'], $DodatkoweUslugiEMAIL);
      */

      if ( isset($_POST['powiadom_sms']) && $_POST['powiadom_sms'] == '1' ) {
          $DodatkoweUslugiSMS = array('Code' =>  'SMS');
          array_push($DaneWejsciowe['ShipmentRequest']['AdditionalServices']['AdditionalService'], $DodatkoweUslugiSMS);
      }

      if ( isset($_POST['powiadom_email']) && $_POST['powiadom_email'] == '1' ) {
        $DodatkoweUslugiEMAIL = array('Code' => 'EMAIL');
        array_push($DaneWejsciowe['ShipmentRequest']['AdditionalServices']['AdditionalService'], $DodatkoweUslugiEMAIL);
      }

      $noweZamowienie = $apiKurier->DoCreateShipment($DaneWejsciowe);

      $ParcelsSer = serialize($Parcels);

      //echo '<pre>';
      //echo print_r($DaneWejsciowe);
      //echo '</pre>';


      if ( is_object($noweZamowienie) && $noweZamowienie->CreateShipmentResult->responseDescription == 'Success' ) {

        if ( is_array($noweZamowienie->CreateShipmentResult->ParcelData->Label) ) {

              if (!file_exists(KATALOG_SKLEPU . 'zarzadzanie/tmp/inPost/'.$noweZamowienie->CreateShipmentResult->PackageNo)) {
                mkdir(KATALOG_SKLEPU . 'zarzadzanie/tmp/inPost/'.$noweZamowienie->CreateShipmentResult->PackageNo, 0777, true);
              }

              for ( $i = 0, $c = count($noweZamowienie->CreateShipmentResult->ParcelData->Label); $i < $c; $i++ ) {

                  $binaryPDF = $noweZamowienie->CreateShipmentResult->ParcelData->Label[$i]->MimeData;
                  $nazwaPDF = $noweZamowienie->CreateShipmentResult->ParcelData->Label[$i]->ParcelID;

                  file_put_contents(KATALOG_SKLEPU . 'zarzadzanie/tmp/inPost/'.$noweZamowienie->CreateShipmentResult->PackageNo.'/'.$nazwaPDF.'.pdf', $binaryPDF);

                  unset($binaryPDF, $nazwaPDF);

              }

        } else {

              if (!file_exists(KATALOG_SKLEPU . 'zarzadzanie/tmp/inPost/'.$noweZamowienie->CreateShipmentResult->PackageNo)) {
                mkdir(KATALOG_SKLEPU . 'zarzadzanie/tmp/inPost/'.$noweZamowienie->CreateShipmentResult->PackageNo, 0777, true);
              }

              $binaryPDF = $noweZamowienie->CreateShipmentResult->ParcelData->Label->MimeData;
              $nazwaPDF = $noweZamowienie->CreateShipmentResult->ParcelData->Label->ParcelID;

              file_put_contents(KATALOG_SKLEPU . 'zarzadzanie/tmp/inPost/'.$noweZamowienie->CreateShipmentResult->PackageNo.'/'.$nazwaPDF.'.pdf', $binaryPDF);

        }

        $pola = array(
                array('orders_id',$filtr->process($_POST["id"])),
                array('orders_shipping_type',$api),
                array('orders_shipping_number',$noweZamowienie->CreateShipmentResult->PackageNo),
                array('orders_shipping_weight',$weight_total),
                array('orders_parcels_quantity',count($_POST['parcel']['dlugosc'])),
                array('orders_shipping_status','PPN'),
                array('orders_shipping_date_created', 'now()'),
                array('orders_shipping_date_modified', 'now()'),
                array('orders_shipping_comments', $ParcelsSer),
        );

        $db->insert_query('orders_shipping' , $pola);
        unset($pola);

        Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));

      } else {
        $komunikat = $noweZamowienie;
      }

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');

    if ( isset($komunikat) && $komunikat != '' ) {
      echo Okienka::pokazOkno('Błąd', $komunikat);
    }
    ?>

    <div id="naglowek_cont">Tworzenie wysyłki</div>
    <div id="cont">
    
    <?php
    if ( !isset($_GET['id_poz']) ) {
         $_GET['id_poz'] = 0;
    }     
    if ( !isset($_GET['zakladka']) ) {
         $_GET['zakladka'] = '0';
    }      
    
    if ( (int)$_GET['id_poz'] == 0 ) {
    ?>
       
      <div class="poleForm"><div class="naglowek">Wysyłka</div>
        <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
      </div>      
      
    <?php
    } else {
    ?>    

      <div class="poleForm">
        <div class="naglowek">Wysyłka za pośrednictwem firmy <?php echo $api; ?> - zamówienie numer : <?php echo $_GET['id_poz']; ?></div>

        <div class="pozycja_edytowana">  

            <?php
            $tablica_wysylek = $apiKurier->inpost_post_parcel_array(false);

            $tekst = '<select style="width:100px;" name="parcel[typ][]" class="valid">';
            foreach ( $tablica_wysylek as $produkt ) {
              $tekst .= '<option value="'.$produkt['id'].'">'.$produkt['text'].'</option>';
            }
            $tekst .= '</select>';
            ?>

            <script>
            $(document).ready(function() {
              
              $("#addrow").click(function() {
                
                var id = $(".UsunPozycjeListy").length;

                $(".item-row:last").after('<tr class="item-row"><td style="text-align:center"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td><td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><?php echo $tekst; ?></td><td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="" size="8" name="parcel[dlugosc][]" class="kropkaPustaZero required" /></td><td class="Paczka"><input type="text" value="" size="8" name="parcel[szerokosc][]" class="kropkaPustaZero required" /></td><td class="Paczka"><input type="text" value="" size="8" name="parcel[wysokosc][]" class="kropkaPustaZero required" /></td><td class="Paczka"><input type="text" value="" size="4" name="parcel[waga][]" class="kropkaPusta required" /></td><td class="Paczka"><input type="checkbox" value="1" name="parcel[standard][]" id="standard" /><label class="OpisForPustyLabel" for="standard"></label></td></tr>');

                $(".kropkaPustaZero").change(		
                  function () {
                    var type = this.type;
                    var tag = this.tagName.toLowerCase();
                    if (type == 'text' && tag != 'textarea' && tag != 'radio' && tag != 'checkbox') {
                        //
                        if ($(this).val() != '') {
                            zamien_krp($(this), '0.00');
                        }
                        //
                    }
                  }
                ); 
                $(".kropkaPusta").change(		
                  function () {
                    var type = this.type;
                    var tag = this.tagName.toLowerCase();
                    if (type == 'text' && tag != 'textarea' && tag != 'radio' && tag != 'checkbox') {
                        //
                        zamien_krp($(this),'');
                        //
                    }
                  }
                ); 

                pokazChmurki();
                
                if ($(".UsunPozycjeListy").length > 1) $(".UsunPozycjeListy").show();
                
              });

              $('body').on('click', '.UsunPozycjeListy', function() {
                var row = $(this).parents('.item-row');
                $(this).parents('.item-row').remove();
                if ($(".UsunPozycjeListy").length < 2) $(".UsunPozycjeListy").hide();
              });

              $.validator.addMethod("valueNotEquals", function (value, element, arg) {
                return arg != value;
              }, "Wybierz opcję");

              $("#apiForm").validate({
                rules: {
                  szerokosc    : { required: true },
                  dlugosc      : { required: true },
                  wysokosc     : { required: true },
                  zawartosc    : { required: true },
                  waga         : { digits: true }
                }
              });

              $('#ubezpieczenie').change(function() {
                  $("#ubezpieczenie_wartosc").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
              });

              $('#pobranie').change(function() {
                  $("#inpost_pobranie").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
                  if ( $(this).is(':checked') ) {
                      $("#PobranieAkapit").slideDown();
                  } else {
                      $("#PobranieAkapit").slideUp();
                  }
              });

              // wycena paczki
              $('#form_uslugi').click(function(){

                  var frm = $("#apiForm");
                  var response_text = $('#wystawianie');
                  var response_form = $('#wynik');
                  var dane = frm.serialize();
                  var daneTbl = frm.serializeArray();
                  var proceed = true;

                  response_text.hide();
     
                  if (proceed == true) {
                  
                    response_text.html('<img src="obrazki/_loader.gif">').show();

                    $.post('ajax/kurier_inpost_uslugi.php?tok=<?php echo Sesje::Token(); ?>', dane, function(data){
                      response_form.slideUp();
                      response_text.html(data);
                      $('#UtworzPrzesylke').show();
                      if ( data.indexOf("Brak") < 0 && data.indexOf("Błąd") < 0  ) {
                          $('#PrzyciskZatwierdz').removeAttr('disabled');
                      } else {
                          $('#PrzyciskZatwierdz').attr('disabled','disabled');
                      }
                    });
                  }

                  return false;
              });

            });
            </script>

            <?php
            $zamowienie     = new Zamowienie((int)$_GET['id_poz']);
            $waga_produktow = $zamowienie->waga_produktow;
            $wymiary        = array();

            $wymiary['0'] = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_WYMIARY_DLUGOSC'];
            $wymiary['1'] = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_WYMIARY_SZEROKOSC'];
            $wymiary['2'] = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_WYMIARY_WYSOKOSC'];

            ?>

            <form action="sprzedaz/zamowienia_wysylka_kurier_inpost.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="apiForm" class="cmxform">

              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="id" value="<?php echo $_GET['id_poz']; ?>" />
                  <input type="hidden" name="zakladka" value="<?php echo $_GET['zakladka']; ?>" />
                  <input type="hidden" id="wartosc_zamowienia_val" name="wartosc_zamowienia_val" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" />
              </div>
              
              <div class="TabelaWysylek">

                <div class="OknoPrzesylki">

                    <div class="poleForm">

                        <div class="naglowek">Informacje o przesyłce</div>

                        <p>
                            <label for="pobranie">Pobranie:</label>
                            <input type="checkbox" value="1" name="pobranie" id="pobranie" style="margin-right:20px;" <?php echo ( isset($_POST['pobranie']) ? 'checked="checked"' : '' ); ?> /><label class="OpisForPustyLabel" for="pobranie" style="margin-right:10px;"></label>
                            <input class="kropkaPustaZero" type="text" size="20" name="inpost_pobranie" id="inpost_pobranie" value="<?php echo ( isset($_POST['inpost_pobranie']) ? $_POST['inpost_pobranie'] : '' ); ?>" />
                        </p> 

                        <div id="PobranieAkapit" <?php echo ( isset($_POST['pobranie']) ? '' : 'style="display:none"' ); ?>>

                            <p>
                                <label for="inpost_numer_konta">Numer konta w formacie IBAN:</label>
                                <input type="text" size="46" name="inpost_numer_konta" id="inpost_numer_konta" value="<?php echo ( isset($_POST['inpost_numer_konta']) ? $_POST['inpost_numer_konta'] : $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_NUMER_KONTA'] ); ?>" />
                            </p> 

                        </div>

                        <p>
                          <label for="ubezpieczenie">Ubezpieczenie: Wartość [PLN]:</label>
                          <input id="ubezpieczenie" value="1" type="checkbox" name="u_ubezp" style="margin-right:20px;" <?php echo ( isset($_POST['u_ubezp']) ? 'checked="checked"' : '' ); ?>><label class="OpisForPustyLabel" style="margin-right:10px;" for="ubezpieczenie"></label> 
                          <input class="kropkaPustaZero" type="text" size="20" name="u_wart_ubezp" id="ubezpieczenie_wartosc" value="<?php echo ( isset($_POST['u_wart_ubezp']) ? $_POST['u_wart_ubezp'] : '' ); ?>" />
                        </p> 

                        <p>
                            <label for="ubezpieczenie_opis">Opis zawartości przesyłki:</label>
                            <textarea cols="45" rows="2" name="ubezpieczenie_opis" id="ubezpieczenie_opis"><?php echo ( isset($_POST['ubezpieczenie_opis']) ? $_POST['ubezpieczenie_opis'] : $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_ZAWARTOSC'] ); ?></textarea>
                        </p>

                        <p>
                            <label for="format_etykiety">Format etykiety:</label>
                            <?php
                            $tablicaEtykiet = array();
                            $domyslnie = ''; 
                            $tablicaEtykiet = array(
                                                    array('id' => 'PDF', 'text' => 'Format PDF')
                                                    );

                            echo Funkcje::RozwijaneMenu('format_etykiety', $tablicaEtykiet, $domyslnie, 'id="format_etykiety" ' ); 

                            unset($tablicaEtykiet);
                            ?>
                        </p>

                        <p>
                          <label for="powiadom_email">Powiadomienie e-mail:</label>
                          <input id="powiadom_email" value="1" type="checkbox" name="powiadom_email" style="margin-right:20px;" <?php echo ( isset($_POST['powiadom_email']) ? 'checked="checked"' : '' ); ?>><label class="OpisForPustyLabel" style="margin-right:10px;" for="powiadom_email"></label> 
                        </p> 

                        <?php if ( Klienci::CzyNumerGSM($apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_NADAWCA_TELEFON']) ) { ?>
                            <p>
                              <label for="powiadom_sms">Powiadomienie SMS:</label>
                              <input id="powiadom_sms" value="1" type="checkbox" name="powiadom_sms" style="margin-right:20px;" <?php echo ( isset($_POST['powiadom_sms']) ? 'checked="checked"' : '' ); ?>><label class="OpisForPustyLabel" style="margin-right:10px;" for="powiadom_sms"></label> 
                            </p>
                        <?php } ?>

                    </div>

                    <div class="poleForm">

                        <div class="naglowek">Informacje o paczkach</div>

                        <table class="listing_tbl">
                          <tr>
                            <td style="width:50px"></td>
                            <td class="Paczka" style="padding-top:8px;">Rodzaj paczki</td>
                            <td class="Paczka">Długość [cm]</td>
                            <td class="Paczka">Szerokość [cm]</td>
                            <td class="Paczka">Wysokość [cm]</td>
                            <td class="Paczka">Waga [kg]</td>
                            <td class="Paczka">Wym. niest.</td>
                          </tr>

                          <tr class="item-row">
                            <td style="text-align:right"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td>
                            <td class="Paczka" style="padding-top:10px; padding-bottom:8px;">
                              <?php
                              $tablica = $apiKurier->inpost_post_parcel_array(false);
                              echo Funkcje::RozwijaneMenu('parcel[typ][]', $tablica, 'Package', 'style="width:100px;"');
                              unset($tablica);
                              ?>
                            </td>
                            <td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="<?php echo ( isset($_POST['parcel']['dlugosc']['0']) ? $_POST['parcel']['dlugosc']['0'] : $wymiary['0'] ); ?>" size="8" name="parcel[dlugosc][]" class="kropkaPustaZero required" /></td>
                            <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['szerokosc']['0']) ? $_POST['parcel']['szerokosc']['0'] : $wymiary['1'] ); ?>" size="8" name="parcel[szerokosc][]" class="kropkaPustaZero required" /></td>
                            <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['wysokosc']['0']) ? $_POST['parcel']['wysokosc']['0'] : $wymiary['2'] ); ?>" size="8" name="parcel[wysokosc][]" class="kropkaPustaZero required" /></td>
                            <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['waga']['0']) ? $_POST['parcel']['waga']['0'] : ceil($waga_produktow) ); ?>" size="4" name="parcel[waga][]" class="kropkaPustaZero required" /></td>
                            <td class="Paczka">
                            <input type="checkbox" value="1" name="parcel[standard][]" id="standard" /><label class="OpisForPustyLabel" for="standard"></label>
                            </td>

                          </tr>

                          <?php
                          if ( isset($_POST['parcel']['dlugosc']) && count($_POST['parcel']['dlugosc']) > 1 ) {
                            for ( $i = 1, $c = count($_POST['parcel']['dlugosc']); $i < $c; $i++ ) {
                              ?>
                              <tr class="item-row">
                                <td style="text-align:right"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td>
                                <td class="Paczka" style="padding-top:10px; padding-bottom:8px;">
                                  <?php
                                  $tablica = $apiKurier->inpost_post_parcel_array(false);
                                  echo Funkcje::RozwijaneMenu('parcel[typ][]', $tablica, ( isset($_POST['parcel']['typ'][$i]) ? $_POST['parcel']['typ'][$i] : '' ), 'style="width:100px;"');
                                  unset($tablica);
                                  ?>
                                </td>
                                <td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="<?php echo ( isset($_POST['parcel']['dlugosc'][$i]) ? $_POST['parcel']['dlugosc'][$i] : '' ); ?>" size="8" name="parcel[dlugosc][]" class="kropkaPustaZero required" /></td>
                                <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['szerokosc'][$i]) ? $_POST['parcel']['szerokosc'][$i] : '' ); ?>" size="8" name="parcel[szerokosc][]" class="kropkaPustaZero required" /></td>
                                <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['wysokosc'][$i]) ? $_POST['parcel']['wysokosc'][$i] : '' ); ?>" size="8" name="parcel[wysokosc][]" class="kropkaPustaZero required" /></td>
                                <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['waga'][$i]) ? $_POST['parcel']['waga'][$i] : '' ); ?>" size="4" name="parcel[waga][]" class="kropkaPustaZero required" /></td>
                                <td class="Paczka">
                                <input type="checkbox" value="1" name="parcel[standard][]" id="standard" /><label class="OpisForPustyLabel" for="standard"></label>
                                </td>
                              </tr>
                              <?php
                            }
                          }
                          ?>

                          <tr id="hiderow">
                            <td colspan="10" style="padding-left:10px;padding-top:10px;padding-bottom:10px;"><a id="addrow" href="javascript:void(0)" class="dodaj">dodaj paczkę</a></td>
                          </tr>
                        
                          </table>

                    </div>

                    <div class="poleForm">

                        <div class="naglowek">Dostępne usługi</div>

                        <div style="overflow:hidden;padding-bottom:10px;">

                            <div class="InfoWycena">
                                <div id="wystawianie" style="display:none;"></div>
                                <div id="wynik" style="padding-bottom:20px;display:none;"></div>
                            </div>

                            <div class="przyciski_dolne">
                                <div id="przycisk_wycen" style="float:left"><input id="form_uslugi" type="submit" class="przyciskNon" value="Sprawdź dostępne usługi" /></div>
                            </div>

                        </div>
                    </div>

                </div>

                <div class="OknoDodatkowe">

                    <div class="poleForm">
                    
                        <div class="naglowek">Informacje</div>

                        <p>
                            <label class="readonly">Forma dostawy w zamówieniu:</label>
                            <input type="text" size="34" name="sposob_dostawy" value="<?php echo $zamowienie->info['wysylka_modul']; ?>" readonly="readonly" class="readonly" />
                        </p> 
                        <?php
                        if ( $zamowienie->info['wysylka_info'] != '' ) {
                                ?>
                                <p>
                                    <label class="readonly">Punkt odbioru:</label>
                                    <textarea cols="30" rows="2" name="punkt_odbioru" id="punkt_odbioru"  readonly="readonly" class="readonly"><?php echo $zamowienie->info['wysylka_info']; ?></textarea>
                                </p>
                                <?php
                        }
                        ?>
                        <p>
                            <label class="readonly">Forma płatności w zamówieniu:</label>
                            <input type="text" size="34" name="sposob_zaplaty" value="<?php echo $zamowienie->info['metoda_platnosci']; ?>" readonly="readonly" class="readonly" />
                        </p> 
                        <p>
                            <label class="readonly">Wartość zamówienia:</label>
                            <input type="text" name="wartosc_zamowienia" value="<?php echo $waluty->FormatujCene($zamowienie->info['wartosc_zamowienia_val'], false, $zamowienie->info['waluta']); ?>" readonly="readonly" class="readonly" />
                        </p> 
                        <p>
                            <label class="readonly">Waga produktów:</label>
                            <input type="text" name="waga_zamowienia" value="<?php echo $waga_produktow; ?>" readonly="readonly" class="readonly" />
                        </p> 

                    </div>

                    <div class="poleForm">
                    
                        <div class="naglowek">Informacje odbiorcy</div>

                        <p>
                            <label>Czy odbiorcą jest firma:</label>
                            <?php
                            $zaznaczony = '0';
                            if ( $zamowienie->dostawa['firma'] != '' ) {
                                $zaznaczony = '1';
                            }
                            echo Konfiguracja::Dopuszczalne_Wartosci_Auto('1,0', ( isset($_POST['odbiorca_firma']) ? $_POST['odbiorca_firma'] : $zaznaczony ), 'odbiorca_firma', '', 'tak,nie', '2' );
                            unset($zaznaczony);
                            ?>
                        </p> 

                        <p>
                            <label for="adresat_firma">Nazwa:</label>
                            <input type="text" size="40" name="adresat_firma" id="adresat_firma" value="<?php echo ( $zamowienie->dostawa['firma'] != '' ? Funkcje::formatujTekstInput($zamowienie->dostawa['firma']) : '---'); ?>" class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_nazwisko_i_imie">Nazwisko i imię:</label>
                            <input type="text" size="40" name="adresat_nazwisko_i_imie" id="adresat_nazwisko_i_imie" value="<?php echo preg_replace('!\s+!', ' ', (string)$zamowienie->dostawa['nazwa']); ?>"  class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_ulica">Ulica i numer domu:</label>
                            <input type="text" size="40" name="adresat_ulica" id="adresat_ulica" value="<?php echo $zamowienie->dostawa['ulica']; ?>" class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_kod_pocztowy">Kod pocztowy:</label>
                            <input type="text" size="40" name="adresat_kod_pocztowy" id="adresat_kod_pocztowy" value="<?php echo $zamowienie->dostawa['kod_pocztowy']; ?>"  class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_miasto">Miejscowość:</label>
                            <input type="text" size="40" name="adresat_miasto" id="adresat_miasto" value="<?php echo $zamowienie->dostawa['miasto']; ?>"  class="klient" />
                        </p> 
                        <p>
                            <label for="receiver_country_code">Kraj:</label>
                            <?php 
                            $tablicaPanstw = array();
                            $domyslnie = ''; 

                            $panstwa = "
                              SELECT cd.countries_id, cd.countries_name, c.countries_iso_code_2  
                                FROM countries c
                                LEFT JOIN countries_description cd ON c.countries_id = cd.countries_id 
                                AND cd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'
                                ORDER BY countries_name
                            ";

                            $sql = $db->open_query($panstwa);

                            while ($wartosciPanstw = $sql->fetch_assoc()) {
                              $tablicaPanstw[] = array('id' => $wartosciPanstw['countries_iso_code_2'],
                                                       'text' => $wartosciPanstw['countries_name']);
                              if ( $wartosciPanstw['countries_name'] == $zamowienie->dostawa['kraj'] ) {
                                $domyslnie = $wartosciPanstw['countries_iso_code_2']; 
                              }
                            }
                            $db->close_query($sql);
                            unset($wartosciPanstw, $panstwa);
                            
                            echo Funkcje::RozwijaneMenu('receiver_country_code', $tablicaPanstw, $domyslnie, 'id="receiver_country_code" class="klient" style="width:210px;"' ); 

                            unset($tablicaPanstw);
                            ?>
                        </p> 
                        <p>
                            <label for="adresat_telefon">Numer telefonu:</label>
                            <?php 
                            if ( $zamowienie->dostawa['telefon'] != '' ) {
                                $NumerTelefonu = $zamowienie->dostawa['telefon'];
                            } else {
                                $NumerTelefonu = $zamowienie->klient['telefon'];
                            }
                            ?>

                            <input type="text" size="40" name="adresat_telefon" id="adresat_telefon" value="<?php echo $NumerTelefonu; ?>"  class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_mail">Adres e-mail:</label>
                            <input type="text" size="40" name="adresat_mail" id="adresat_mail" value="<?php echo $zamowienie->klient['adres_email']; ?>"  class="klient" />
                        </p> 
                        
                    </div>
                    
                </div>

              </div>

              <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Utwórz przesyłkę" id="PrzyciskZatwierdz" disabled="disabled" />
                <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','zakladka')); ?>','sprzedaz');">Powrót</button>           
              </div>
            </form>

        </div>
      </div>

    <?php } ?>
    
    </div>  

    <?php
    include('stopka.inc.php');    
    
} ?>
