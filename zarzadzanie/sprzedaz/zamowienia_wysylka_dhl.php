<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $komunikat = '';
    $api = 'DHL';

    if ( !isset($_POST['akcja']) ) {
        $apiKurier = new DhlApi((int)$_GET['id_poz']);
    }
    $weight_total = 0;
    $parcel = array();

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $apiKurier = new DhlApi((int)$_POST['id']);

        $shipment = array();
        $shipmentInfo = array();
        $shipmentInfo['dropOffType'] = 'REGULAR_PICKUP';

        $shipmentInfo['serviceType'] = $_POST['serviceType'];

        // ###########################################
        if ( isset($_POST['ParcelShop']) && $_POST['ParcelShop'] == '1' ) {
            $shipmentInfo['serviceType'] = 'SP';
        }
        // ###########################################

        $billing = array();
        $billing['shippingPaymentType'] = $_POST['billing']['shippingPaymentType'];
        $billing['paymentType'] = 'BANK_TRANSFER';
        $billing['billingAccountNumber'] = $apiKurier->polaczenie['INTEGRACJA_DHL_SAP'];

        $shipmentInfo['billing'] = $billing;

        $shipmentInfo['labelType'] = $apiKurier->polaczenie['INTEGRACJA_DHL_WYDRUK_FORMAT'];

        $specialServices = array();
        if (isset($_POST['insurance']) && $_POST['insurance'] == '1') {
            $item = array();
            $item['serviceType'] = 'UBEZP';
            if (isset($_POST['insuranceValue'])) {
                $item['serviceValue'] = str_replace(',', '.', (string)$_POST['insuranceValue']);
            }
            $specialServices[] = $item;
        }
        if (isset($_POST['collectOnDelivery']) && $_POST['collectOnDelivery'] == '1') {
            $item = array();
            $item['serviceType'] = 'COD';
            if (isset($_POST['collectOnDeliveryValue'])) {
                $item['serviceValue'] = str_replace(',', '.', (string)$_POST['collectOnDeliveryValue']);
            }
            $item['collectOnDeliveryForm'] = 'BANK_TRANSFER';
            $specialServices[] = $item;
        }
        if (isset($_POST['predeliveryInformation']) && $_POST['predeliveryInformation'] == '1') {
            $item = array();
            $item['serviceType'] = 'PDI';
            $specialServices[] = $item;
        }
        if (isset($_POST['returnOnDelivery']) && $_POST['returnOnDelivery'] == '1') {
            $item = array();
            $item['serviceType'] = 'ROD';
            $specialServices[] = $item;
        }
        if (isset($_POST['proofOfDelivery']) && $_POST['proofOfDelivery'] == '1') {
            $item = array();
            $item['serviceType'] = 'POD';
            $specialServices[] = $item;
        }
        if (isset($_POST['deliveryEvening']) && $_POST['deliveryEvening'] == '1') {
            $item = array();
            $item['serviceType'] = '1722';
            $specialServices[] = $item;
        }
        if (isset($_POST['deliveryOnSaturday']) && $_POST['deliveryOnSaturday'] == '1') {
            $item = array();
            $item['serviceType'] = 'SOBOTA';
            $specialServices[] = $item;
        }
        if (isset($_POST['pickupOnSaturday']) && $_POST['pickupOnSaturday'] == '1') {
            $item = array();
            $item['serviceType'] = 'NAD_SOBOTA';
            $specialServices[] = $item;
        }

        $shipmentInfo['specialServices'] = $specialServices;
        $shipmentTime = array();

        if (isset($_POST['shipmentTime'])) {
            $shipmentTime['shipmentDate'] = $_POST['shipmentTime'];
        }

        $shipmentInfo['shipmentTime'] = $shipmentTime;

        $shipment['shipmentInfo'] = $shipmentInfo;
        if (!empty($_POST['content'])) {
            $shipment['content'] = $_POST['content'];
        }
        if (!empty($_POST['comment'])) {
            $shipment['comment'] = $_POST['comment'];
        }


        $shipment['shipmentInfo'] = $shipmentInfo;

        $params = array(
            "authData" => array('username' => $apiKurier->polaczenie['INTEGRACJA_DHL_LOGIN'], 'password' => $apiKurier->polaczenie['INTEGRACJA_DHL_HASLO']),
        );


        $ship = array();
        $shipper = array();
        $shipperContact = array();

        if (!empty($apiKurier->polaczenie['INTEGRACJA_DHL_OSOBA_KONTAKTOWA'])) {
            $shipperContact['personName']      = $apiKurier->polaczenie['INTEGRACJA_DHL_OSOBA_KONTAKTOWA'];
        }
        if (!empty($apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_TELEFON'])) {
            $shipperContact['phoneNumber']     = $apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_TELEFON'];
        }
        if (!empty($apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_EMAIL'])) {
            $shipperContact['emailAddress']    = $apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_EMAIL'];
        }
        $shipper['contact'] = $shipperContact;

        $shipperAddress = array();
        $shipperAddress['name']                = $apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_FIRMA'];
        $shipperAddress['postalCode']          = $apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_KOD_POCZTOWY'];
        $shipperAddress['city']                = $apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_MIASTO'];
        $shipperAddress['street']              = $apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_ULICA'];
        $shipperAddress['houseNumber']         = $apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_ULICA_DOM'];
        if (!empty($apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_ULICA_MIESZKANIE'])) {
            $shipperAddress['apartmentNumber'] = $apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_ULICA_MIESZKANIE'];
        }
        $shipperAddress['country'] = 'PL';

        $shipper['address'] = $shipperAddress;
        $ship['shipper'] = $shipper;

        $receiver = array();
        $receiverContact = array();
        $receiverContact['personName']   = $_POST['receiver']['personName'];
        $receiverContact['phoneNumber']  = $_POST['receiver']['contactPhone'];
        $receiverContact['emailAddress'] = $_POST['receiver']['contactEmail'];

        $receiver['contact'] = $receiverContact;

        $receiverAddress = array();

        $receiverAddress['name'] = $_POST['receiver']['name'];
        $receiverAddress['addressType'] = $_POST['receiver']['addressType'];
        $receiverAddress['postalCode'] = $_POST['receiver']['postalCode'];
        $receiverAddress['city'] = $_POST['receiver']['city'];
        $receiverAddress['street'] = $_POST['receiver']['street'];
        $receiverAddress['houseNumber'] = $_POST['receiver']['houseNumber'];
        if (!empty($_POST['receiver']['apartmentNumber'])) {
            $receiverAddress['apartmentNumber'] = $_POST['receiver']['apartmentNumber'];
        }
        $receiverAddress['country'] = $_POST['receiver']['country'];
        $receiver['address'] = $receiverAddress;
        $ship['receiver'] = $receiver;

        // ###########################################
        if ( isset($_POST['ParcelShop']) && $_POST['ParcelShop'] == '1' ) {
            $ship['servicePointAccountNumber'] = $_POST['punkt_odbioru_dhl'];
        }
        // ########################################### '4501583'

        $weight_total = 0;
        $quantity_total = 0;

        $pieceList = array();
        if (isset($_POST['pieceList'])&& is_array($_POST['pieceList']) && count($_POST['pieceList']) > 0) {
            foreach ($_POST['pieceList'] as $package) {
                $_package = array();
                if (isset($package['type'])) {
                    if ($package['type'] == 'ENVELOPE') {
                        $_package['type'] = $package['type'];
                        if (isset($package['quantity'])) {
                            $_package['quantity'] = $package['quantity'];
                        }
                    } else {
                        $_package['type'] = $package['type'];
                        if (isset($package['weight'])) {
                            $_package['weight'] = $package['weight'];
                        }
                        if (isset($package['width'])) {
                            $_package['width'] = $package['width'];
                        }
                        if (isset($package['height'])) {
                            $_package['height'] = $package['height'];
                        }
                        if (isset($package['length'])) {
                            $_package['length'] = $package['length'];
                        }
                        if (isset($package['quantity'])) {
                            $_package['quantity'] = $package['quantity'];
                        }
                        if (isset($package['nonStandard'])) {
                            $_package['nonStandard'] = $package['nonStandard'];
                        }
                        $weight_total = $weight_total + $package['weight'];
                    }
                    $quantity_total = $quantity_total + $package['quantity'];

                }
                if (count($_package) > 0) {
                    $pieceList[] = $_package;
                }
            }
        }
        $shipment['pieceList'] = $pieceList;

        $shipment['ship'] = $ship;
        $params['shipment'] = $shipment;

        $Wynik = $apiKurier->CreateShipmentAction($params);

        if ( $Wynik != false ) {
            if ( isset($Wynik->createShipmentResult->shipmentTrackingNumber) && $Wynik->createShipmentResult->shipmentTrackingNumber != '' ) {

                $pola = array(
                        array('orders_id',(int)$_POST["id"]),
                        array('orders_shipping_type',$api),
                        array('orders_shipping_number',$Wynik->createShipmentResult->shipmentTrackingNumber),
                        array('orders_shipping_weight',$weight_total),
                        array('orders_parcels_quantity',$quantity_total),
                        array('orders_shipping_status','Utworzona'),
                        array('orders_shipping_date_created', 'now()'),
                        array('orders_shipping_date_modified', 'now()'),
                        array('orders_shipping_comments', ''),
                        array('orders_shipping_packages', ''),
                        array('orders_shipping_misc', '')
                );

                $db->insert_query('orders_shipping' , $pola);
                unset($pola);

                Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.(int)$_POST["zakladka"]);

            }
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

    $zamowienie     = new Zamowienie((int)$_GET['id_poz']);

    if ( $zamowienie->waga_produktow > 0 ) {
        $waga_produktow = $zamowienie->waga_produktow;
    } else {
        $waga_produktow = $apiKurier->polaczenie['INTEGRACJA_DHL_WYMIARY_WAGA'];
    }

    $wymiary        = array();

    $kodIsoPanstwa = Klienci::pokazISOPanstwa($zamowienie->dostawa['kraj'], $zamowienie->klient['jezyk']);

    $AdresOK = true;
    $adres_klienta  = Funkcje::PrzeksztalcAdres($zamowienie->dostawa['ulica']);
    $adres_dom_lokal = Funkcje::PrzeksztalcAdresDomu($adres_klienta['dom']);

    $wymiary['0'] = $apiKurier->polaczenie['INTEGRACJA_DHL_WYMIARY_DLUGOSC'];
    $wymiary['1'] = $apiKurier->polaczenie['INTEGRACJA_DHL_WYMIARY_SZEROKOSC'];
    $wymiary['2'] = $apiKurier->polaczenie['INTEGRACJA_DHL_WYMIARY_WYSOKOSC'];

    $PrzeksztalconyAdres = implode(' ', $adres_klienta); 
    if ( $PrzeksztalconyAdres != $zamowienie->dostawa['ulica'] ) {
        $AdresOK = false;
    }

    ?>    

      <div class="poleForm">
        <div class="naglowek">Wysyłka za pośrednictwem firmy <?php echo $api; ?> - zamówienie numer : <?php echo $_GET['id_poz']; ?></div>

        <div class="pozycja_edytowana"> 

          <?php

          $UrlMapy = 'https://parcelshop.dhl.pl/mapa';

          if ( $apiKurier->polaczenie['INTEGRACJA_DHL_SANDBOX'] == 'tak' ) {
            $UrlMapy = 'https://psm-sandbox.dhl24.com.pl/mapa';
          }

          ?>
        
          <div class="MapaUkryta" id="WybierzMape">
            <div id="WyborMapaWysylka">
                <div id="MapaKontener">
                    <div id="MapaZamknij">X</div>
                    <div id="WidokMapy"><iframe frameborder="0" src="<?php echo $UrlMapy . ( $kodIsoPanstwa != 'PL' ? '?country='.$kodIsoPanstwa.'' : '' ); ?>" style="width:100%; height:100%;"></iframe></div>
                </div>
            </div>
          </div>

          <script type="text/javascript" src="javascript/jquery.chained.remote.js"></script>        


            <script>
                $(document).ready(function() {

                    function IsJsonString(str) {
                      try {
                        var json = JSON.parse(str);
                        return (typeof json === 'object');
                      } catch (e) {
                        return false;
                      }
                    }            

                    function listenMessage(msg) {
                      
                        if ( IsJsonString(msg.data) ) {
                    
                            var point = JSON.parse(msg.data);
                                            
                            $('#punkt_odbioru_dhl').val(point.sap);
                            $('#PunktWybrany').val(point.street + " " + point.streetNo + ", " + point.zip + " " + point.city + ", " + point.name);
                                            
                            $('#WybierzMape').removeClass('MapaWidoczna').addClass('MapaUkryta');
                            $("#MapaWidocznaTlo").fadeOut(500, function() {
                                $("#MapaWidocznaTlo").remove();
                            });
                            enableScroll();

                       }
                    };

                    if (window.addEventListener) {
                        window.addEventListener("message", listenMessage, false);
                    } else {
                        window.attachEvent("onmessage", listenMessage);
                    }

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
                       $("#MapaWidocznaTlo").fadeOut(500, function() {
                           $("#MapaWidocznaTlo").remove();
                       });
                    });

                });

                function PokazMape() {
                    $("#WybierzMape").append('<div id="MapaWidocznaTlo"></div>');
                    $('#WybierzMape').removeClass('MapaUkryta').addClass('MapaWidoczna').show();

                }

            </script>

            <script>
            $(document).ready(function() {

              <?php
              if ( !$AdresOK ) {
                ?>
                $( "<p style='padding:10px 25px 5px 25px;'><span class='ostrzezenie'>Sprawdź adres odbiorcy</span></p><p style='padding:0 20px 5px 25px;'><span><?php echo addslashes($zamowienie->dostawa['ulica']); ?></span></p>" ).insertBefore("#AdresOdbiorcy");
                <?php
              }
              ?>

              if ($(".UsunPozycjeListy").length < 2) $(".UsunPozycjeListy").hide();

              $("#addrow").click(function() {
                
                var id = $(".UsunPozycjeListy").length;
                var kolejny_wiersz = $("#ilosc_paczek").val();
                var ile_pol = parseInt($("#ilosc_paczek").val()) + 1;

                $(".item-row:last").after('<tr class="item-row" style="padding-top:10px;"><td style="text-align:center"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td><td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><select name="pieceList['+kolejny_wiersz+'][type]"><option value="ENVELOPE">koperta</option><option value="PACKAGE" selected="selected">paczka</option><option value="PALLET">paleta</option></select><td class="Paczka"><input type="text" value="<?php echo $apiKurier->polaczenie['INTEGRACJA_DHL_WYMIARY_DLUGOSC']; ?>" size="8" name="pieceList['+kolejny_wiersz+'][length]" class="kropkaPustaZero required" /></td><td class="Paczka"><input type="text" value="<?php echo $apiKurier->polaczenie['INTEGRACJA_DHL_WYMIARY_SZEROKOSC']; ?>" size="8" name="pieceList['+kolejny_wiersz+'][width]" class="kropkaPustaZero required" /></td><td class="Paczka"><input type="text" value="<?php echo $apiKurier->polaczenie['INTEGRACJA_DHL_WYMIARY_WYSOKOSC']; ?>" size="8" name="pieceList['+kolejny_wiersz+'][height]" class="kropkaPustaZero required" /></td><td class="Paczka"><input type="text" value="1" size="4" name="pieceList['+kolejny_wiersz+'][weight]" class="kropkaPusta required" /></td><td class="Paczka"><input type="checkbox" value="1" name="pieceList['+kolejny_wiersz+'][nonStandard]" id="standard_0" /><label class="OpisForPustyLabel" for="standard_0"></label></td><td class="Paczka"><input type="text" value="1" size="2" name="pieceList['+kolejny_wiersz+'][quantity]" class="kropkaPustaZero required" /></td></tr>');

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

                $("#ilosc_paczek").val(ile_pol);
                if ($(".UsunPozycjeListy").length > 1) $(".UsunPozycjeListy").show();
                
              });

              $('body').on('click', '.UsunPozycjeListy', function() {
                var ile_pol = parseInt($("#ilosc_paczek").val()) - 1;
                $("#ilosc_paczek").val(ile_pol);
                var row = $(this).parents('.item-row');
                $(this).parents('.item-row').remove();
                if ($(".UsunPozycjeListy").length < 2) $(".UsunPozycjeListy").hide();
              });

              $.validator.addMethod("valueNotEquals", function (value, element, arg) {
                return arg != value;
              }, "Wybierz opcję");

              $("#apiForm").validate({
                rules: {
                  zawartosc    : { required: true },
                  waga         : { digits: true }
                }
              });

              $('#pickup_date').change(function() {
                $('#Uslugi').html('<div id="loader"></div>');
                $.ajax(
                    {
                        url: "ajax/dhl.php",
                        type: "POST",
                        data: {
                            action: 'uslugi',
                            data: this.value,
                            kod_pocztowy: '<?php echo $apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_KOD_POCZTOWY']; ?>',
                            miasto: '<?php echo $apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_MIASTO']; ?>',
                            ulica: '<?php echo $apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_ULICA']; ?>',
                            dom: '<?php echo $apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_ULICA_DOM']; ?>',
                            mieszkanie: '<?php echo $apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_ULICA_MIESZKANIE']; ?>'
                        },
                        success: function( data )
                        {
                            $('#Uslugi').html(data);

                        }
                    });

              });

              $('#serviceValue_Check').change(function() {
                 (($(this).is(':checked')) ? $("#serviceValue_Wartosc").prop('disabled', false) : $("#serviceValue_Wartosc").prop('disabled', true));
                 $("#serviceValue_Wartosc").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
              });

              $('#pobranie').change(function() {
                  (($(this).is(':checked')) ? $("#dpd_pobranie").prop('disabled', false) : $("#dpd_pobranie").prop('disabled', true));
                  $("#dpd_pobranie").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
                  $("#serviceValue_Wartosc").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");

                  (($(this).is(':checked')) ? $("#serviceValue_Wartosc").prop('disabled', false) : $("#serviceValue_Wartosc").prop('disabled', true));
                  (($(this).is(':checked')) ? $("#serviceValue_Check").prop('checked', true) : $("#serviceValue_Check").prop('checked', false));

              });

            });
            </script>

            <?php
            $DostepneSerwisy = new stdClass();
            $DostepneSerwisy = $apiKurier->PostalCodeServices($apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_KOD_POCZTOWY'], date('Y-m-d'));
            ?>

            <form action="sprzedaz/zamowienia_wysylka_dhl.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="apiForm" class="cmxform">

              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="id" value="<?php echo $_GET['id_poz']; ?>" />
                  <input type="hidden" name="zakladka" value="<?php echo $_GET['zakladka']; ?>" />
                  <input type="hidden" id="wartosc_zamowienia_val" name="wartosc_zamowienia_val" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" />
                  <input type="hidden" name="billing[billingAccountNumber]" value="<?php echo $apiKurier->polaczenie['INTEGRACJA_DHL_SAP']; ?>" />
                  <input type="hidden" id="ilosc_paczek" name="ilosc_paczek" value="1" />
              </div>
              
              <div class="TabelaWysylek">

                <div class="OknoPrzesylki">

                    <?php if ( $zamowienie->info['wysylka_punkt_odbioru'] != '' ) { ?>

                        <div class="poleForm">
                            <div class="naglowek">W zamówieniu wybrano formę dostawy do punktu</div>
                            <?php
                            if ( stripos($zamowienie->info['wysylka_modul'], 'DHL') === false ) {
                                ?>
                                <p>
                                    <span class="ostrzezenie" style="margin:10px 15px;">Wybrana przesyłka jest prawdopobnie inna niż DHL Parcel</span>
                                </p>
                                <?php
                            }
                            ?>
                            <p>
                                <label for="ParcelShop">Dostawa ParcelShop:</label>
                                <input type="checkbox" value="1" name="ParcelShop" id="ParcelShop" <?php echo ( stripos($zamowienie->info['wysylka_modul'], 'DHL') === false ? '' : 'checked="checked"') ; ?>><label class="OpisForPustyLabel" for="ParcelShop"></label>
                            </p> 

                            <p>
                                <label for="punkt_odbioru_dhl" class="required" >Do Punktu:</label>
                                <?php 
                                echo '<input type="text" class="przyciskPaczkomatu" id="WidgetButton" value="Wybierz punkt" readonly="readonly" onclick="PokazMape()" />';

                                echo '<input type="text" size="40" id="PunktWybrany" value="'.$zamowienie->info['wysylka_info'].'" name="lokalizacjaPunkt" readonly="readonly" id="wybor_punktu" />';

                                echo '<input type="text" id="punkt_odbioru_dhl" value="'.$zamowienie->info['wysylka_punkt_odbioru'].'" name="punkt_odbioru_dhl" style="margin-left:10px;" size="15" />';

                                ?>
                            </p>
                        </div>

                    <?php } ?>

                    <div class="poleForm">

                        <div class="naglowek">Informacje o przesyłce</div>

                        <p>
                            <label>Zleca:</label>
                            <?php
                            //shippingPaymentType
                            echo Konfiguracja::Dopuszczalne_Wartosci_Auto('SHIPPER,RECEIVER,USER', ( isset($_POST['billing[shippingPaymentType]']) ? $_POST['billing[shippingPaymentType]'] : $apiKurier->polaczenie['INTEGRACJA_DHL_PLATNIK'] ), 'billing[shippingPaymentType]', '', 'nadawca,odbiorca,strona trzecia', '2' );
                            ?>
                        </p> 

                        <p>
                            <label>Data nadania:</label>
                            <?php
                            $DataStart = date('Y-m-d');
                            $OdbiorSelect = Funkcje::DostepneDatyPlus($DataStart, '3');
                            $wynik = '<select name="shipmentTime" id ="pickup_date">';
                            for ( $i = 0; $i < count($OdbiorSelect); $i++ ) {
                                $wynik .= '<option value="' . $OdbiorSelect[$i] . '">'.$OdbiorSelect[$i].'</option>';
                            }
                            $wynik .= '</select>';
                            echo $wynik;
                            ?>
                        </p> 

                        <p>
                            <label for="pobranie">Pobranie:</label>
                            <?php if ( strpos((string)$zamowienie->info['metoda_platnosci'], 'pobranie') === false && strpos((string)$zamowienie->info['metoda_platnosci'], 'odbiorze') === false) { ?>
                                <input type="checkbox" value="1" name="collectOnDelivery" id="pobranie" style="margin-right:20px;" <?php echo ( isset($_POST['collectOnDelivery']) ? 'checked="checked"' : '' ); ?> /><label class="OpisForPustyLabel" for="pobranie" style="margin-right:10px;"></label>
                                <input class="kropkaPustaZero" type="text" size="20" name="collectOnDeliveryValue" id="dpd_pobranie" value="<?php echo ( isset($_POST['collectOnDeliveryValue']) && $_POST['collectOnDeliveryValue'] != '' ? $_POST['scollectOnDeliveryValue'] : '' ); ?>" <?php echo ( isset($_POST['collectOnDeliveryValue']) && $_POST['collectOnDeliveryValue'] != '' ? '' : 'disabled="disabled"' ); ?> />
                            <?php } else { ?>
                                <input type="checkbox" value="1" name="collectOnDelivery" id="pobranie" style="margin-right:20px;" checked="checked" /><label class="OpisForPustyLabel" for="pobranie" style="margin-right:10px;"></label>
                                <input class="kropkaPustaZero" type="text" size="20" name="collectOnDeliveryValue" id="dpd_pobranie" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" />
                            <?php } ?>
                        </p> 

                        <p>
                          <label for="serviceValue_Check">Deklarowana wartość:</label>
                          <input id="serviceValue_Check" value="1" type="checkbox" name="insurance" style="margin-right:20px;" <?php echo ( isset($_POST['insurance']) ? 'checked="checked"' : '' ); ?>><label class="OpisForPustyLabel" style="margin-right:10px;" for="serviceValue_Check"></label> 
                          <input class="kropkaPustaZero" type="text" size="20" name="insuranceValue" id="serviceValue_Wartosc" value="<?php echo ( isset($_POST['insuranceValue']) && $_POST['insuranceValue'] != '' ?  $_POST['insuranceValue'] : '' ); ?>" <?php echo ( isset($_POST['insuranceValue']) && $_POST['insuranceValue'] != '' ? '' : 'disabled="disabled"' ); ?> /><em class="TipIkona"><b>Deklarowana wartość, wymagana podczas zamawiania usług ubezpieczenie lub zwrot pobrania</b></em>
                        </p> 

                        <p>
                            <label class="required" for="content">Zawartość [max. 30 znaków]:</label>
                            <input type="text" name="content" id="content" class="required" value="<?php echo ( isset($_POST['content']) ? $_POST['content'] : $apiKurier->polaczenie['INTEGRACJA_DHL_ZAWARTOSC'] ); ?>" size="50" />
                        </p> 

                        <p>
                            <label for="comment">Uwagi [max. 100 znaków]:</label>
                            <textarea cols="45" rows="2" name="comment" id="comment" ><?php echo ( isset($_POST['comment']) ? $_POST['comment'] : 'Zamówienie numer: ' . $_GET['id_poz'] ); ?></textarea>
                        </p> 

                        <div id="Uslugi">
                            <div class="naglowek" style="margin:10px 0;">Typ usługi przewozowej</div>
                            <div style="display:flex;flex-wrap:wrap;align-items: center;justify-content:flex-start;">
                                <?php
                                    echo '<div><label for="serviceType_AH">przesyłka krajowa</label> ';
                                    echo '<input checked="checked" type="radio" value="AH" name="serviceType" id="serviceType_AH" style="margin-right:20px;" /><label class="OpisForPustyLabel" for="serviceType_AH" style="margin-right:10px;"></label></div>';

                                    if ( is_object($DostepneSerwisy->getPostalCodeServicesResult) && $DostepneSerwisy->getPostalCodeServicesResult->domesticExpress9 ) {
                                        echo '<div><label for="serviceType_09">Domestic 09</label> ';
                                        echo '<input type="radio" value="09" name="serviceType" id="serviceType_09" style="margin-right:20px;" /><label class="OpisForPustyLabel" for="serviceType_09" style="margin-right:10px;"></label></div>';
                                    }

                                    if ( is_object($DostepneSerwisy->getPostalCodeServicesResult) && $DostepneSerwisy->getPostalCodeServicesResult->domesticExpress12 ) {
                                        echo '<div><label for="serviceType_12">Domestic 12</label> ';
                                        echo '<input type="radio" value="12" name="serviceType" id="serviceType_12" style="margin-right:20px;" /><label class="OpisForPustyLabel" for="serviceType_12" style="margin-right:10px;"></label></div>';
                                    }

                                    if ( is_object($DostepneSerwisy->getPostalCodeServicesResult) && $DostepneSerwisy->getPostalCodeServicesResult->deliveryEvening ) {
                                        echo '<div><label for="serviceType_DW">z doręczeniem wieczornym</label> ';
                                        echo '<input type="radio" value="DW" name="serviceType" id="serviceType_DW" style="margin-right:20px;" /><label class="OpisForPustyLabel" for="serviceType_DW" style="margin-right:10px;"></label></div>';
                                    }

                                    echo '<div><label for="serviceType_EK">przesyłka Connect</label> ';
                                    echo '<input type="radio" value="EK" name="serviceType" id="serviceType_EK" style="margin-right:20px;" /><label class="OpisForPustyLabel" for="serviceType_EK" style="margin-right:10px;"></label></div>';
                                    
                                    echo '<div><label for="serviceType_PI">przesyłka International</label> ';
                                    echo '<input type="radio" value="PI" name="serviceType" id="serviceType_PI" style="margin-right:20px;" /><label class="OpisForPustyLabel" for="serviceType_PI" style="margin-right:10px;"></label></div>';
                                    
                                    echo '<div><label for="serviceType_PR">produkt Premium</label> ';
                                    echo '<input type="radio" value="PR" name="serviceType" id="serviceType_PR" style="margin-right:20px;" /><label class="OpisForPustyLabel" for="serviceType_PR" style="margin-right:10px;"></label></div>';

                                    echo '<div><label for="serviceType_CP">przesyłka Connect Plus</label> ';
                                    echo '<input type="radio" value="CP" name="serviceType" id="serviceType_CP" style="margin-right:20px;" /><label class="OpisForPustyLabel" for="serviceType_CP" style="margin-right:10px;"></label></div>';

                                    echo '<div><label for="serviceType_CM">przesyłka Connect Plus Pallet</label> ';
                                    echo '<input type="radio" value="CM" name="serviceType" id="serviceType_CM" style="margin-right:20px;" /><label class="OpisForPustyLabel" for="serviceType_CM" style="margin-right:10px;"></label></div>';

                                ?>
                            </div>

                            <div class="naglowek" style="margin:10px 0;">Usługi dodatkowe</div>

                            <div style="display:flex;flex-wrap:wrap;align-items: center;justify-content:flex-start;">
                                    <?php
                                    echo '<div><label for="deliveryEvening">Doręczenie w godzinach 18-22</label> ';
                                    echo '<input type="checkbox" value="1" name="deliveryEvening" id="deliveryEvening" style="margin-right:20px;"'.( isset($_POST['deliveryEvening']) ? 'checked="checked"' : '' ).' /><label class="OpisForPustyLabel" for="deliveryEvening" style="margin-right:10px;"></label></div>';

                                    if ( is_object($DostepneSerwisy->getPostalCodeServicesResult) && $DostepneSerwisy->getPostalCodeServicesResult->deliverySaturday ) {
                                        echo '<div><label for="deliveryOnSaturday">Doręczenie w sobotę</label> ';
                                        echo '<input type="checkbox" value="1" name="deliveryOnSaturday" id="deliveryOnSaturday" style="margin-right:20px;"'.( isset($_POST['deliveryOnSaturday']) ? 'checked="checked"' : '' ).' /><label class="OpisForPustyLabel" for="deliveryOnSaturday" style="margin-right:10px;"></label></div>';
                                    }

                                    if ( is_object($DostepneSerwisy->getPostalCodeServicesResult) && $DostepneSerwisy->getPostalCodeServicesResult->pickupOnSaturday ) {
                                        echo '<div><label for="pickupOnSaturday">Nadanie w sobotę</label> ';
                                        echo '<input type="checkbox" value="1" name="pickupOnSaturday" id="pickupOnSaturday" style="margin-right:20px;"'.( isset($_POST['pickupOnSaturday']) ? 'checked="checked"' : '' ).' /><label class="OpisForPustyLabel" for="pickupOnSaturday" style="margin-right:10px;"></label></div>';
                                    }

                                    echo '<div><label for="predeliveryInformation">Informacje przed doręczeniem</label> ';
                                    echo '<input type="checkbox" value="1" name="predeliveryInformation" id="predeliveryInformation" style="margin-right:20px;"'.( isset($_POST['predeliveryInformation']) ? 'checked="checked"' : '' ).' /><label class="OpisForPustyLabel" for="predeliveryInformation" style="margin-right:10px;"></label></div>';

                                    echo '<div><label for="returnOnDelivery">Zwrot potwierdzonych dokumentów</label> ';
                                    echo '<input type="checkbox" value="1" name="returnOnDelivery" id="returnOnDelivery" style="margin-right:20px;"'.( isset($_POST['returnOnDelivery']) ? 'checked="checked"' : '' ).' /><label class="OpisForPustyLabel" for="returnOnDelivery" style="margin-right:10px;"></label></div>';

                                    echo '<div><label for="proofOfDelivery">Potwierdzenie doręczenia</label> ';
                                    echo '<input type="checkbox" value="1" name="proofOfDelivery" id="proofOfDelivery" style="margin-right:20px;"'.( isset($_POST['proofOfDelivery']) ? 'checked="checked"' : '' ).' /><label class="OpisForPustyLabel" for="proofOfDelivery" style="margin-right:10px;"></label></div>';

                                    ?>
                            </div>
                        </div>

                    </div>

                    <div class="poleForm">
                        <?php
                        $TablicaRodzajowPaczek = array();
                        $RodzajePaczek = '';
                        $RodzajePaczek .= '<option value="ENVELOPE">koperta</option>
                                           <option value="PACKAGE" selected="selected">paczka</option>
                                           <option value="PALLET">paleta</option>';


                        ?>
                        <div class="naglowek">Informacje o paczkach</div>

                        <table class="listing_tbl">
                          <tr>
                            <td style="width:50px"></td>
                            <td class="Paczka">Rodzaj paczki</td>
                            <td class="Paczka">Długość [cm]</td>
                            <td class="Paczka">Szerokość [cm]</td>
                            <td class="Paczka">Wysokość [cm]</td>
                            <td class="Paczka">Waga [kg]</td>
                            <td class="Paczka">Wym. niest.</td>
                            <td class="Paczka">Ilość</td>
                          </tr>

                          <tr class="item-row">
                            <td style="text-align:right"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td>
                            <td class="Paczka"><select name="pieceList[0][type]"><?php echo $RodzajePaczek; ?></select></td>
                            <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['pieceList']['0']['length']) ? $_POST['pieceList']['0']['length'] : $wymiary['0'] ); ?>" size="8" name="pieceList[0][length]" class="kropkaPustaZero required" /></td>
                            <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['pieceList']['0']['width']) ? $_POST['pieceList']['0']['width'] : $wymiary['1'] ); ?>" size="8" name="pieceList[0][width]" class="kropkaPustaZero required" /></td>
                            <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['pieceList']['0']['height']) ? $_POST['pieceList']['0']['height'] : $wymiary['2'] ); ?>" size="8" name="pieceList[0][height]" class="kropkaPustaZero required" /></td>
                            <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['pieceList']['0']['weight']) ? $_POST['pieceList']['0']['weight'] : ceil($waga_produktow) ); ?>" size="4" name="pieceList[0][weight]" class="kropkaPustaZero required" /></td>
                            <td class="Paczka"><input type="checkbox" value="1" name="pieceList[0][nonStandard]" id="standard_0" /><label class="OpisForPustyLabel" for="standard_0"></label></td>
                            <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['pieceList']['0']['quantity']) ? $_POST['pieceList']['0']['quantity'] : '1' ); ?>" size="2" name="pieceList[0][quantity]" class="kropkaPustaZero required" /></td>
                          </tr>

                          <?php
                          if ( isset($_POST['pieceList']) && count($_POST['pieceList']) > 1 ) {
                            for ( $i = 1, $c = count($_POST['pieceList']); $i < $c; $i++ ) {
                              ?>
                              <tr class="item-row">
                                <td style="text-align:right"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td>
                                <td class="Paczka"><select name="pieceList[<?php echo $i; ?>][type]"><?php echo $RodzajePaczek; ?></select></td>
                                <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['pieceList'][$i]['length']) ? $_POST['pieceList'][$i]['length'] : '' ); ?>" size="8" name="pieceList[<?php echo $i; ?>][length]" class="kropkaPustaZero required" /></td>
                                <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['pieceList'][$i]['width']) ? $_POST['pieceList'][$i]['width'] : '' ); ?>" size="8" name="pieceList[<?php echo $i; ?>][width]" class="kropkaPustaZero required" /></td>
                                <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['pieceList'][$i]['height']) ? $_POST['pieceList'][$i]['height'] : '' ); ?>" size="8" name="pieceList[<?php echo $i; ?>][height]" class="kropkaPustaZero required" /></td>
                                <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['pieceList'][$i]['weight']) ? $_POST['pieceList'][$i]['weight'] : '' ); ?>" size="4" name="pieceList[<?php echo $i; ?>][weight]" class="kropkaPustaZero required" /></td>
                                <td class="Paczka"><input type="checkbox" value="1" name="pieceList[<?php echo $i; ?>][nonStandard]" id="standard_<?php echo $i; ?>" /><label class="OpisForPustyLabel" for="standard_<?php echo $i; ?>"></label></td>
                                <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['pieceList'][$i]['quantity']) ? $_POST['pieceList'][$i]['quantity'] : '1' ); ?>" size="2" name="pieceList[<?php echo $i; ?>][quantity]" class="kropkaPustaZero required" /></td>
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
                            <input type="text" size="34" name="sposob_zaplaty" value="<?php echo $zamowienie->info['metoda_platnosci']; ?>" readonly="readonly" class="readonly" />
                        </p> 
                        <p>
                            <label class="readonly">Wartość zamówienia:</label>
                            <input type="text" name="wartosc_zamowienia" value="<?php echo $waluty->FormatujCene($zamowienie->info['wartosc_zamowienia_val'], false, $zamowienie->info['waluta']); ?>" readonly="readonly" class="readonly" />
                        </p> 
                        <p>
                            <label class="readonly">Waga produktów:</label>
                            <input type="text" name="waga_zamowienia" value="<?php echo $zamowienie->waga_produktow; ?>" readonly="readonly" class="readonly" />
                        </p> 

                    </div>

                    <div class="poleForm">
                    
                        <div class="naglowek">Informacje odbiorcy</div>

                        <p>
                            <label for="adresat_name">Nazwa firmy lub imię i nazwisko:</label>
                            <?php
                            if ( $zamowienie->dostawa['firma'] != '' ) {
                                ?>
                                <input type="text" size="40" name="receiver[name]" id="adresat_name" value="<?php echo Funkcje::formatujTekstInput($zamowienie->dostawa['firma']); ?>" class="klient" />
                                <?php
                                } else {
                                ?>
                                    <input type="text" size="40" name="receiver[name]" id="adresat_name" value="<?php echo preg_replace('!\s+!', ' ', (string)$zamowienie->dostawa['nazwa']); ?>"  class="klient" />
                                <?php
                            }
                            ?>
                        </p>
                        <p>
                            <label for="adresat_name">Osoba do kontaktu:</label>
                            <input type="text" size="40" name="receiver[personName]" id="adresat_name" value="<?php echo preg_replace('!\s+!', ' ', (string)$zamowienie->dostawa['nazwa']); ?>"  class="klient" />
                        </p>
                        <p>
                            <label>Typ adresu odbiorcy:</label>
                            <input type="radio" style="border:0px" name="receiver[addressType]" value="B" id="dor_osoba_F" <?php echo ( $zamowienie->dostawa['firma'] != '' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="dor_osoba_F">Firmowy </label>
                            <input type="radio" style="border:0px" name="receiver[addressType]" value="C" id="dor_osoba_P" <?php echo ( $zamowienie->dostawa['firma'] == '' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="dor_osoba_P">Prywatny</label>
                        </p> 
                        <p id="AdresOdbiorcy">
                            <label for="adresat_street">Ulica:</label>
                            <input type="text" size="40" name="receiver[street]" id="adresat_street" value="<?php echo $adres_klienta['ulica']; ?>" class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_houseNumber">Nr domu:</label>
                            <input type="text" size="40" name="receiver[houseNumber]" id="adresat_houseNumber" value="<?php echo $adres_dom_lokal['dom']; ?>" class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_apartmentNumber">Nr mieszkania:</label>
                            <input type="text" size="40" name="receiver[apartmentNumber]" id="adresat_apartmentNumber" value="<?php echo $adres_dom_lokal['mieszkanie']; ?>" class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_postalCode">Kod pocztowy:</label>
                            <input type="text" size="40" name="receiver[postalCode]" id="adresat_postalCode" value="<?php echo Klienci::KodPocztowyDostawy( (string)$zamowienie->dostawa['kod_pocztowy'], $kodIsoPanstwa); ?>"  class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_city">Miejscowość:</label>
                            <input type="text" size="40" name="receiver[city]" id="adresat_city" value="<?php echo $zamowienie->dostawa['miasto']; ?>"  class="klient" />
                        </p> 

                        <p>
                            <label for="adresat_cuntry">Kraj:</label>
                            <?php 
                            $tablicaPanstw = Klienci::ListaPanstwISO();
                            echo Funkcje::RozwijaneMenu('receiver[country]', $tablicaPanstw, $kodIsoPanstwa, 'id="adresat_cuntry"'); ?>
                        </p> 

                        <p>
                            <label for="adresat_contactPhone">Numer telefonu:</label>
                            <?php 
                            if ( $zamowienie->dostawa['telefon'] != '' ) {
                                $NumerTelefonu = $zamowienie->dostawa['telefon'];
                            } else {
                                $NumerTelefonu = $zamowienie->klient['telefon'];
                            }
                            ?>

                            <input type="text" size="40" name="receiver[contactPhone]" id="adresat_contactPhone" value="<?php echo $NumerTelefonu; ?>"  class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_contactEmail">Adres e-mail:</label>
                            <input type="text" size="40" name="receiver[contactEmail]" id="adresat_contactEmail" value="<?php echo $zamowienie->klient['adres_email']; ?>"  class="klient" />
                        </p> 
                        
                    </div>
                    
                </div>

              </div>

              <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Utwórz przesyłkę" <?php //echo ( $zamowienie->info['wysylka_punkt_odbioru'] != '' ? 'disabled="disabled"' : '' ); ?>/>
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
