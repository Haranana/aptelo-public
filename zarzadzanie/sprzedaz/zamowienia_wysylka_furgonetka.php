<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $api = 'FURGONETKA';

    if ( isset($_GET['id_poz']) && isset($_GET['zakladka']) ) {
        $IDPoz = $_GET['id_poz'];
        $Zakladka = $_GET['zakladka'];
    }
    if ( isset($_POST['id_poz']) && isset($_POST['zakladka']) ) {
        $IDPoz = $_POST['id_poz'];
        $Zakladka = $_POST['zakladka'];
    }

    $apiKurier       = new FurgonetkaRestApi(true, $IDPoz, $Zakladka);
    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $DostepneSerwisy = $apiKurier->commandGet('account/services', true, '', false);

        $DostepneSerwisyID = array();
        foreach ( $DostepneSerwisy->services as $Serwis ) {
            $DostepneSerwisyID[$Serwis->service] = $Serwis->id;

        }

        $params = new stdClass;
        $params->pickup = new stdClass;

        $params->pickup->name         = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_IMIE'] . ' ' . $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_NAZWISKO'];
        $params->pickup->company      = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_FIRMA'];
        $params->pickup->street       = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_ULICA'];
        $params->pickup->postcode     = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_KOD_POCZTOWY'];
        $params->pickup->city         = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_MIASTO'];
        $params->pickup->country_code = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_KRAJ'];
        $params->pickup->county       = '';
        $params->pickup->email        = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_EMAIL'];
        $params->pickup->phone        = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_TELEFON'];

        if ( $_POST['deliverytype'] == 'P2P' || $_POST['deliverytype'] == 'P2D' ) {
            if ( $_POST['operatorName'] == 'INPOST' ) {
                $params->pickup->point        = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_INPOST'];
            }
            if ( $_POST['operatorName'] == 'ORLEN' ) {
                $params->pickup->point        = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_RUCH'];
            }
            if ( $_POST['operatorName'] == 'DPD' ) {
                $params->pickup->point        = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_DPD'];
            }
            if ( $_POST['operatorName'] == 'UPSAP' ) {
                $params->pickup->point        = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_UPS'];
            }
            if ( $_POST['operatorName'] == 'POCZTA' ) {
                $params->pickup->point        = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_POCZTA'];
            }
        } else {
            $params->pickup->point        = '';
        }

        $PunktOdbioru = '';
        if ( $_POST['deliverytype'] == 'P2P' || $_POST['deliverytype'] == 'D2P' ) {
            $PunktOdbioru = $_POST['destinationCode'];
        }

        //$PunktOdbioru = 'CIE347';

        $params->receiver = new stdClass;
        $params->receiver->name         = $_POST['receiverFirstName'] . ' ' . $_POST['receiverLastName'];
        $params->receiver->company      = $_POST['receiverCompanyName'];
        $params->receiver->street       = $_POST['receiverStreet'];
        $params->receiver->postcode     = $_POST['receiverPostCode'];
        $params->receiver->city         = $_POST['receiverCity'];
        $params->receiver->country_code = $_POST['receiverCountryCode'];
        $params->receiver->county       = '';
        $params->receiver->email        = $_POST['receiverEmail'];
        $params->receiver->phone        = $_POST['receiverPhoneNumber'];
        $params->receiver->point        = $PunktOdbioru;

        if ( $_POST['operatorId'] != $DostepneSerwisyID['dpd'] && $_POST['operatorId'] != $DostepneSerwisyID['ups'] && $_POST['operatorId'] != $DostepneSerwisyID['inpostkurier'] && $_POST['operatorId'] != $DostepneSerwisyID['gls'] && $_POST['operatorId'] != $DostepneSerwisyID['orlen'] && $_POST['operatorId'] != $DostepneSerwisyID['poczta'] && $_POST['operatorId'] != $DostepneSerwisyID['inpost'] ) {
            $params->sender = new stdClass;
            $params->sender->name         = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_IMIE'] . ' ' . $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_NAZWISKO'];
            $params->sender->company      = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_FIRMA'];
            $params->sender->street       = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_ULICA'];
            $params->sender->postcode     = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_KOD_POCZTOWY'];
            $params->sender->city         = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_MIASTO'];
            $params->sender->country_code = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_KRAJ'];
            $params->sender->county       = '';
            $params->sender->email        = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_EMAIL'];
            $params->sender->phone        = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_TELEFON'];
        }

        $params->parcels = array();
        $IloscPaczek = count($_POST['parcel']['dlugosc']);
        $Waga = 0;

        for ( $i = 0, $c = $IloscPaczek; $i < $c; $i++ ) {

            $Parcel = new stdClass;
            $Parcel->type        = $_POST['packtype'];
            $Parcel->width       = $_POST['parcel']['szerokosc'][$i];
            $Parcel->depth       = $_POST['parcel']['dlugosc'][$i];
            $Parcel->height      = $_POST['parcel']['wysokosc'][$i];
            $Parcel->weight      = $_POST['parcel']['waga'][$i];
            $Parcel->value       = ( isset($_POST['parcel']['insuranceValue'][$i]) && $_POST['parcel']['insuranceValue'][$i] > 0 ? number_format($_POST['parcel']['insuranceValue'][$i], 2, '.', '') : '');
            $Parcel->description = $_POST['additionalInformation'];

            $Waga = $Waga + $_POST['parcel']['waga'][$i];

            $params->parcels[] = $Parcel;

        }

        $params->additional_services = new stdClass;

        $params->additional_services->cod = new stdClass;

        if ( isset($_POST['cod']) && $_POST['codValue'] > 0 ) {
            $params->additional_services->cod->amount = number_format($_POST['codValue'], 2, '.', '');
            $params->additional_services->cod->express = false;
            $params->additional_services->cod->iban = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NUMER_KONTA'];
            $params->additional_services->cod->name = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NAZWA_KONTA'];
        }

        $params->additional_services->rod = false;
        $params->additional_services->cud = ( isset($_POST['additional_services']['cud']) ? true : false );
        $params->additional_services->private_shipping = false;
        $params->additional_services->guarantee_0930 = false;
        $params->additional_services->guarantee_1200 = false;
        $params->additional_services->saturday_delivery = ( isset($_POST['additional_services']['saturday_delivery']) ? true : false );
        $params->additional_services->additional_handling = false;
        $params->additional_services->sending_at_point = false;
        $params->additional_services->sms_predelivery_information = ( isset($_POST['additional_services']['sms_predelivery_information']) ? true : false );
        $params->additional_services->receiver_email_notification = ( isset($_POST['additional_services']['receiver_email_notification']) ? true : false );
        $params->additional_services->documents_supply = ( isset($_POST['additional_services']['documents_supply']) ? true : false );
        $params->additional_services->saturday_sunday_delivery = ( isset($_POST['additional_services']['saturday_sunday_delivery']) ? true : false );;
        $params->additional_services->guarantee_next_day = false;
        $params->additional_services->fedex_priority = false;
        $params->additional_services->ups_saver = false;
        $params->additional_services->valuable_shipment = false;
        $params->additional_services->fragile = false;
        $params->additional_services->personal_delivery = false;
        if ( $_POST['operatorId'] == $DostepneSerwisyID['poczta'] && ( $_POST['deliverytype'] == 'D2P' || $_POST['deliverytype'] == 'D2D' ) ) {
            $params->additional_services->pocztex = true;
        } else {
            $params->additional_services->poczta_kurier24 = false;
        }
        $params->additional_services->registered_company_letter = false;
        $params->additional_services->delivery_confirmation = false;
        $params->additional_services->waiting_time = false;

        $params->user_reference_number = $_POST['reference'];
        $params->service_id            = (int)$_POST['operatorId'];
        $params->type                  = $_POST['packtype'];

        $Wynik = $apiKurier->commandPost('packages', $params);

        if ( $Wynik !== false ) {

              $pola = array(
                      array('orders_id',(int)$_POST["id"]),
                      array('orders_shipping_type',$api),
                      array('orders_shipping_number',$Wynik->package_id),
                      array('orders_shipping_weight',$Waga),
                      array('orders_parcels_quantity',$IloscPaczek),
                      array('orders_shipping_status',$Wynik->state),
                      array('orders_shipping_date_created', 'now()'),
                      array('orders_shipping_date_modified', 'now()'),
                      array('orders_shipping_comments', $_POST["operatorName"]),
                      array('orders_shipping_misc',$Wynik->package_id),
                      array('orders_dispatch_status',$_POST['deliveryType'])
              );

              $db->insert_query('orders_shipping' , $pola);
              unset($pola);

              Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));

        }

        $komunikat = '';
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');

    if ( isset($komunikat) && $komunikat != '' ) {
      //echo Okienka::pokazOkno('Błąd', $komunikat);
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
          $zamowienie     = new Zamowienie((int)$_GET['id_poz']);
          ?>

          <script type="text/javascript" src="javascript/jquery.chained.remote.js"></script>        
          <script type="text/javascript" src="javascript/paczka_furgonetka.js"></script>
          <script type="text/javascript" src="https://furgonetka.pl/js/dist/map/map.js"></script>

          <script>

            function callback(params) {
                var typ = params.point.type;
                var opis = params.point.name;
                var textString = opis.replace(/<\/?[^>]+(>|$)/g, "");
                $('#preferowany_'+typ+'').val(textString);
                $('#destinationCode_'+typ+'').val(params.point.code);
                $('#integracja_furgonetka_'+typ+'').val(params.point.code);

            }
            function PokazMape(Kurier, COD) {
                var pobranie = COD;
                var str = Kurier;
                var dostawca = str.toLowerCase();
                var miasto = $('#receiverCity').val();
                new window.Furgonetka.Map({
                    courierServices: [dostawca],
                    city: miasto,
                    callback: callback
                    }).show(); 
                    return false;
            }

          </script>

          <script>
          $(document).ready(function() {
            $.validator.addMethod("valueNotEquals", function (value, element, arg) {
              return arg != value;
            }, "Wybierz opcję");

            $("#apiForm").validate({
              rules: {
                waga         : { digits: true }
              }
            });

            $('#cod').change(function() {
                (($(this).is(':checked')) ? $("#codValue").prop('disabled', false) : $("#codValue").prop('disabled', true));
                $("#codValue").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
                $('#DostepneUslugi').slideUp();
                $('#DaneWysylki').slideUp();
            });
            $('#insurance').change(function() {
                  //$("#insuranceValue").val(($(this).is(':checked')) ? $("#insuranceValue").show() : $("#insuranceValue").hide());
                  $("#insuranceValue").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
            });

            $('#deliverytype').change(function() {
                $('#PrzyciskZatwierdz').attr('disabled','disabled');
                if ( $('#deliverytype').val() != 'D2D' ) {
                    $('.item-row:not(:first)').remove();
                    $('#hiderow').hide();
                } else {
                    $('#hiderow').show();
                }
                $('#DostepneUslugi').slideUp();
                $('#DaneWysylki').slideUp();
            });

            $('#packtype').change(function() {
                $('#DostepneUslugi').slideUp();
                $('#DaneWysylki').slideUp();
            });

            $('#checkService').click(function (){

                var Data = $('#apiForm').serialize();

                $('input[name^="parcel[waga]"]').each(function() {
                    if ( $(this).val() < 0.01 ) {
                        $.colorbox( { html:'<div id="PopUpInfo">Waga paczki musi być większa od 0</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                        return;
                    }
                });

                $('input[name^="parcel[dlugosc]"]').each(function() {
                    if ( $(this).val() < 0.01 ) {
                        $.colorbox( { html:'<div id="PopUpInfo">Długość paczki musi być większa od 0</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                        return;
                    }
                });
                
                $('input[name^="parcel[szerokosc]"]').each(function() {
                    if ( $(this).val() < 0.01 ) {
                        $.colorbox( { html:'<div id="PopUpInfo">Szerokość paczki musi być większa od 0</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                        return;
                    }
                });
                
                $('input[name^="parcel[wysokosc]"]').each(function() {
                    if ( $(this).val() < 0.01 ) {
                        $.colorbox( { html:'<div id="PopUpInfo">Wysokość paczki musi być większa od 0</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                        return;
                    }
                });

                $('#PrzyciskZatwierdz').attr('disabled','disabled');
                $('#DaneWysylki').slideUp();
                $('#DostepneUslugi').slideDown();
                $('#Uslugi').html('<div id="loader"></div>');

                $.ajax(
                    {
                        url: "ajax/furgonetka.php",
                        type: "POST",
                        data: Data,
                        success: function( data )
                        {
                            $('#Uslugi').html(data);

                        }
                    });

            
            });


          });

          </script>

          <?php
            $wymiary        = array();
            $wymiary['0'] = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_WYMIARY_DLUGOSC'];
            $wymiary['1'] = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_WYMIARY_SZEROKOSC'];
            $wymiary['2'] = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_WYMIARY_WYSOKOSC'];

            $waga_produktow = $zamowienie->waga_produktow;

            $klient = explode(' ', (string)$zamowienie->dostawa['nazwa']);

            $kodPocztowyNadawcy = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_KOD_POCZTOWY'];
            if(preg_match("/^([0-9]{2})(-[0-9]{3})?$/i",$kodPocztowyNadawcy)) {
            } else {
                $kodPocztowyNadawcy = substr((string)$kodPocztowyNadawcy,'0','2') . '-' . substr((string)$kodPocztowyNadawcy,'2','5'); 
            }
            ?>

            <form action="sprzedaz/zamowienia_wysylka_furgonetka.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="apiForm" class="cmxform"> 
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="id" value="<?php echo $_GET['id_poz']; ?>" />
                  <input type="hidden" id="zakladka" name="zakladka" value="<?php echo $_GET['zakladka']; ?>" />

                  <input type="hidden" id="reference" name="reference" value="<?php echo $_GET['id_poz']; ?>" />
                  <input type="hidden" id="additionalReference" name="additionalReference" value="<?php echo $_GET['id_poz']; ?>" />

                  <input type="hidden" id="wartosc_zamowienia_val" name="wartosc_zamowienia_val" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" />
              </div>
              
              <div class="TabelaWysylek">

                <div class="OknoPrzesylki">

                    <div class="poleForm">

                        <div class="naglowek">Informacje o przesyłce</div>

                            <p>
                                <label for="deliverytype">Rodzaj dostawy:</label>
                                <?php
                                $domyslnyDostawa = 'D2D';
                                if ( $zamowienie->info['wysylka_punkt_odbioru'] != '' ) {
                                    $domyslnyDostawa = 'P2P';
                                }
                                $tablica = $apiKurier->furgonetka_product_array(false);
                                echo Funkcje::RozwijaneMenu('deliverytype', $tablica, $domyslnyDostawa, 'id="deliverytype" style="width:326px;"');
                                unset($tablica);
                                ?>

                            </p> 

                            <p>
                                <label for="packtype">Rodzaj przesyłki:</label>
                                <?php
                                $domyslnyPaczka = 'package';
                                $tablica = $apiKurier->furgonetka_packtype_array(false);
                                echo Funkcje::RozwijaneMenu('packtype', $tablica, $domyslnyPaczka, 'id="packtype" style="width:326px;"');
                                unset($tablica);
                                ?>

                            </p> 
                            <p>
                                <label for="cod" style="height:28px; line-height:28px;">Pobranie:</label>
                                <?php if ( strpos((string)$zamowienie->info['metoda_platnosci'], 'pobranie') === false && strpos((string)$zamowienie->info['metoda_platnosci'], 'odbiorze') === false) { ?>
                                    <input type="checkbox" value="1" name="cod" id="cod" style="margin-right:20px;" <?php echo ( isset($_POST['cod']) ? 'checked="checked"' : '' ); ?> /><label class="OpisForPustyLabel" for="cod" style="margin-right:10px;"></label>
                                    <input class="kropkaPustaZero" type="text" size="20" name="codValue" id="codValue" value="<?php echo ( isset($_POST['codValue']) ? $_POST['codValue'] : '' ); ?>" disabled="disabled" />
                                <?php } else { ?>
                                    <input type="checkbox" value="1" name="cod" id="cod" style="margin-right:20px;" checked="checked" /><label class="OpisForPustyLabel" for="cod" style="margin-right:10px;"></label>
                                    <input class="kropkaPustaZero" type="text" size="20" name="codValue" id="codValue" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" />
                                <?php } ?>
                            </p> 

                            <p>
                                <label for="insurance" style="height:28px; line-height:28px;">Dodatkowe ubezpieczenie:</label>
                                <?php if ( strpos((string)$zamowienie->info['metoda_platnosci'], 'pobranie') === false && strpos((string)$zamowienie->info['metoda_platnosci'], 'odbiorze') === false) { ?>
                                    <input type="checkbox" value="1" name="insurance" id="insurance" style="margin-right:20px;" <?php echo ( isset($_POST['insurance']) ? 'checked="checked"' : '' ); ?> /><label class="OpisForPustyLabel" for="insurance" style="margin-right:10px;"></label>
                                <?php } else { ?>
                                    <input type="checkbox" value="1" name="insurance" id="insurance" style="margin-right:20px;" checked="checked" /><label class="OpisForPustyLabel" for="insurance" style="margin-right:10px;"></label>
                                <?php } ?>
                            </p>

                            <div class="naglowek" style="margin:10px 0;">Informacje o paczkach</div>

                            <table class="listing_tbl" style="border-bottom:1px dashed #cccccc; margin-bottom:15px;">
                                <tr>
                                    <td style="width:50px"></td>
                                    <td class="Paczka" style="padding-top:8px;">Długość [cm]</td>
                                    <td class="Paczka">Szerokość [cm]</td>
                                    <td class="Paczka">Wysokość [cm]</td>
                                    <td class="Paczka">Waga [kg]</td>
                                    <td class="Paczka">Wartość [zł]</td>
                                </tr>

                                <tr class="item-row" id="PaczkaGlowna">
                                    <td style="text-align:right"><div class="UsunKontener" style="display:none;"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td>
                                    <td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="<?php echo ( isset($_POST['parcel']['dlugosc']['0']) ? $_POST['parcel']['dlugosc']['0'] : $wymiary['0'] ); ?>" size="8" name="parcel[dlugosc][]" class="kropkaPustaZero required" /></td>
                                    <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['szerokosc']['0']) ? $_POST['parcel']['szerokosc']['0'] : $wymiary['1'] ); ?>" size="8" name="parcel[szerokosc][]" class="kropkaPustaZero required" /></td>
                                    <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['wysokosc']['0']) ? $_POST['parcel']['wysokosc']['0'] : $wymiary['2'] ); ?>" size="8" name="parcel[wysokosc][]" class="kropkaPustaZero required" /></td>
                                    <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['waga']['0']) ? $_POST['parcel']['waga']['0'] : ceil($waga_produktow) ); ?>" size="8" name="parcel[waga][]" class="kropkaPustaZero required" /></td>
                                    <td class="Paczka"><input type="text" id="insuranceValue" value="<?php echo ( isset($_POST['parcel']['insuranceValue']['0']) ? $_POST['parcel']['insuranceValue']['0'] : ( strpos((string)$zamowienie->info['metoda_platnosci'], 'pobranie') !== false || strpos((string)$zamowienie->info['metoda_platnosci'], 'odbiorze') !== false ? $zamowienie->info['wartosc_zamowienia_val'] : '' ) ); ?>" size="8" name="parcel[insuranceValue][]" class="kropkaPustaZero" />
                                    </td>

                                </tr>

                                <?php
                                if ( isset($_POST['parcel']['dlugosc']) && count($_POST['parcel']['dlugosc']) > 1 ) {
                                    for ( $i = 1, $c = count($_POST['parcel']['dlugosc']); $i < $c; $i++ ) {
                                      ?>
                                      <tr class="item-row">
                                        <td style="text-align:right"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td>
                                        <td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="<?php echo ( isset($_POST['parcel']['dlugosc'][$i]) ? $_POST['parcel']['dlugosc'][$i] : '' ); ?>" size="8" name="parcel[dlugosc][]" class="kropkaPustaZero required" /></td>
                                        <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['szerokosc'][$i]) ? $_POST['parcel']['szerokosc'][$i] : '' ); ?>" size="8" name="parcel[szerokosc][]" class="kropkaPustaZero required" /></td>
                                        <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['wysokosc'][$i]) ? $_POST['parcel']['wysokosc'][$i] : '' ); ?>" size="8" name="parcel[wysokosc][]" class="kropkaPustaZero required" /></td>
                                        <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['waga'][$i]) ? $_POST['parcel']['waga'][$i] : '' ); ?>" size="8" name="parcel[waga][]" class="kropkaPustaZero required" /></td>
                                        <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['insuranceValue'][$i]) ? $_POST['parcel']['insuranceValue'][$i] : '' ); ?>" size="8" name="parcel[insuranceValue][]" class="kropkaPustaZero" /></td>
                                      </tr>
                                      <?php
                                    }
                                }
                                ?>
                                <?php
                                if ( $domyslnyDostawa == 'D2D' ) { ?>
                                <tr id="hiderow">
                                    <td colspan="10" style="padding-left:25px;padding-top:10px;padding-bottom:20px;"><a id="addrow" href="javascript:void(0)" class="dodaj">dodaj paczkę</a></td>
                                </tr>
                                <?php } else { ?>
                                <tr id="hiderow" style="display:none;">
                                    <td colspan="10" style="padding-left:25px;padding-top:10px;padding-bottom:20px;"><a id="addrow" href="javascript:void(0)" class="dodaj">dodaj paczkę</a></td>
                                </tr>
                                <?php } ?>
                            </table>

                            <p>
                                <label for="additionalInformation">Informacje dodatkowe:</label>
                                <textarea cols="45" rows="2" name="additionalInformation" id="additionalInformation" ><?php echo ( isset($_POST['additionalInformation']) ? $_POST['additionalInformation'] : $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_ZAWARTOSC'] ); ?></textarea>
                            </p>

                            <p>
                                <button class="przyciskNon" type="button" id="checkService">Sprawdź dostępność usług</button>
                            </p>

                    </div>

                    <div class="poleForm" id="DostepneUslugi" style="display:none;">

                        <div class="naglowek">Dostępne oferty</div>

                        <div id="Uslugi"></div>

                    </div>

                    <div class="poleForm" id="DaneWysylki" style="display:none;">

                        <div class="naglowek">Wybrana oferta</div>

                        <div id="Wysylka"></div>

                    </div>

                </div>
                    
                <div class="OknoDodatkowe">

                    <div class="poleForm">

                        <div class="naglowek">Informacje</div>

                        <p>
                            <label class="readonly">Forma dostawy w zamówieniu:</label>
                            <input type="text" name="sposob_dostawy" value="<?php echo $zamowienie->info['wysylka_modul']; ?>" readonly="readonly" class="readonly" />
                        </p> 
                        <?php
                        if ( $zamowienie->info['wysylka_info'] != '' ) {
                                ?>
                                <p>
                                    <label class="readonly">Punkt odbioru:</label>
                                    <textarea cols="30" rows="2" name="punkt_odbioru" id="punkt_odbioru"  readonly="readonly" class="readonly"><?php echo $zamowienie->info['wysylka_info']; ?></textarea>
                                </p>
                                <?php
                                if ( $zamowienie->info['wysylka_punkt_odbioru'] != '' ) {
                                    ?>
                                    <p>
                                        <label class="readonly">Kod punktu odbioru:</label>
                                        <input type="text" name="punkt_odbioru_kod" value="<?php echo $zamowienie->info['wysylka_punkt_odbioru']; ?>" readonly="readonly" class="readonly" />
                                    </p>
                                    <?php
                                }
                        }
                        ?>
                        <p>
                            <label class="readonly">Forma płatności w zamówieniu:</label>
                            <input type="text" name="sposob_zaplaty" value="<?php echo $zamowienie->info['metoda_platnosci']; ?>" readonly="readonly" class="readonly" />
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

                        <div class="naglowek">Informacje o odbiorcy</div>

                            <p>
                                <label for="receiverCompanyName">Firma:</label>
                                <input type="text" size="40" name="receiverCompanyName" id="receiverCompanyName" value="<?php echo ( isset($_POST['receiverCompanyName']) ? $_POST['receiverCompanyName'] : $zamowienie->dostawa['firma'] ); ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="receiverFirstName">Imię:</label>
                                <input type="text" size="40" name="receiverFirstName" id="receiverFirstName" value="<?php echo ( isset($_POST['receiverFirstName']) ? $_POST['receiverFirstName'] : $klient['0'] ) ; ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="receiverLastName">Nazwisko:</label>
                                <input type="text" size="40" name="receiverLastName" id="receiverLastName" value="<?php echo ( isset($_POST['receiverLastName']) ? $_POST['receiverLastName'] : $klient['1'] ); ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="receiverStreet">Ulica:</label>
                                <input type="text" size="40" name="receiverStreet" id="receiverStreet" value="<?php echo ( isset($_POST['receiverStreet']) ? $_POST['receiverStreet'] : $zamowienie->dostawa['ulica'] ); ?>" class="klient" />
                            </p> 

                            <p>
                                <label for="receiverPostCode">Kod pocztowy:</label>
                                <input type="text" size="40" name="receiverPostCode" id="receiverPostCode" value="<?php echo ( isset($_POST['receiverPostCode']) ? $_POST['receiverPostCode'] : $zamowienie->dostawa['kod_pocztowy'] ); ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="receiverCity">Miejscowość:</label>
                                <input type="text" size="40" name="receiverCity" id="receiverCity" value="<?php echo ( isset($_POST['receiverCity']) ? $_POST['receiverCity'] : $zamowienie->dostawa['miasto'] ); ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="receiverCountryCode">Kraj:</label>
                                <?php 
                                $domyslnie = $apiKurier->getIsoCountry($zamowienie->dostawa['kraj']); 
                                $tablicaPanstw = $apiKurier->getCountrySelect($zamowienie->dostawa['kraj']); 
                                echo Funkcje::RozwijaneMenu('receiverCountryCode', $tablicaPanstw, $domyslnie, 'id="receiverCountryCode" class="klient" style="width:210px;"' ); 

                                unset($tablicaPanstw);
                                ?>
                            </p> 
                            <p>
                                <label for="receiverPhoneNumber">Numer telefonu:</label>
                                <?php 
                                if ( $zamowienie->dostawa['telefon'] != '' ) {
                                    $NumerTelefonu = $zamowienie->dostawa['telefon'];
                                } else {
                                    $NumerTelefonu = $zamowienie->klient['telefon'];
                                }
                                ?>
                                <input type="text" size="40" name="receiverPhoneNumber" id="receiverPhoneNumber" value="<?php echo preg_replace( '/[^0-9+]/', '', ( isset($_POST['receiverPhoneNumber']) ? (string)$_POST['receiverPhoneNumber'] : (string)$NumerTelefonu )); ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="receiverEmail">Adres e-mail:</label>
                                <input type="text" size="40" name="receiverEmail" id="receiverEmail" value="<?php echo ( isset($_POST['receiverEmail']) ? $_POST['receiverEmail'] : $zamowienie->klient['adres_email'] ); ?>" class="klient" />
                            </p> 
                        
                    </div>
                    
                </div>

              </div>

              <div class="przyciski_dolne">
                <input id="PrzyciskZatwierdz" type="submit" class="przyciskNon" value="Utwórz przesyłkę" disabled="disabled" />
                <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','zakladka')); ?>','sprzedaz');">Powrót</button>

              </div>
            </form>
            <?php //} else {
                //echo 'Sprawdź konfigurację modułu';
            //} ?>
        
        </div>
      </div>

    <?php } ?>
    
    </div>    
    
    <?php
    include('stopka.inc.php');    
    
} ?>
