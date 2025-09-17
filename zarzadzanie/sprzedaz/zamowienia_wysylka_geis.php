<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $komunikat = '';
    $api = 'GEIS';
    $JestPodjazd = false;
    $LimitZapytan = false;
    $ZamowienieOK = false;

    $weight_total = 0;
    $parcel = array();

    if ( !isset($_POST['akcja']) ) {
        $apiKurier = new GeisApi((int)$_GET['id_poz']);
    }

    if (isset($_POST['akcja_kurier']) && $_POST['akcja_kurier'] == 'zapisz') {

        $apiKurier = new GeisApi();
        $Wynik = $apiKurier->doCreatePickUp($_POST['CountItems'], $_POST['TotalWeight'], date("Y-m-d\TH:i:s", FunkcjeWlasnePHP::my_strtotime($_POST['DateFrom'])), date("Y-m-d\TH:i:s", FunkcjeWlasnePHP::my_strtotime($_POST['DateTo'])), $_POST['Note'] );

        if ( isset($Wynik->CreatePickUpResult->ErrorCode) && $Wynik->CreatePickUpResult->ErrorCode != '0000' ) {
            $ZamowienieOK = false;
        } else {
            $ZamowienieOK = true;
        }
    }

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $apiKurier = new GeisApi();

        if ( $_POST['Uslugi'] == '20' ) {
            $OrderRequest['DeliveryAddress']['City'] = $filtr->process($_POST['DeliveryAddress']['City']);
            $OrderRequest['DeliveryAddress']['Country'] = $filtr->process($_POST['DeliveryAddress']['Country']);
            $OrderRequest['DeliveryAddress']['Name'] = $filtr->process($_POST['DeliveryAddress']['Name']);
            $OrderRequest['DeliveryAddress']['Name1'] = $filtr->process($_POST['DeliveryAddress']['Name1']);
            $OrderRequest['DeliveryAddress']['Street'] = $filtr->process($_POST['DeliveryAddress']['Street']);
            $OrderRequest['DeliveryAddress']['ZipCode'] = $filtr->process($_POST['DeliveryAddress']['ZipCode']);

            $OrderRequest['DeliveryContact']['Email'] = $filtr->process($_POST['DeliveryContact']['Email']);
            $OrderRequest['DeliveryContact']['FullName'] = $filtr->process($_POST['DeliveryContact']['FullName']);
            $OrderRequest['DeliveryContact']['Phone'] = $filtr->process($_POST['DeliveryContact']['Phone']);
        }

        if ( $_POST['Uslugi'] == '21' ) {
            $OrderRequest['DeliveryAddress']['City'] = $apiKurier->polaczenie['INTEGRACJA_GEIS_NADAWCA_MIASTO'];
            $OrderRequest['DeliveryAddress']['Country'] = 'PL';
            $OrderRequest['DeliveryAddress']['Name'] = $apiKurier->polaczenie['INTEGRACJA_GEIS_NADAWCA_NAZWA'];
            $OrderRequest['DeliveryAddress']['Street'] = $apiKurier->polaczenie['INTEGRACJA_GEIS_NADAWCA_ULICA'];
            $OrderRequest['DeliveryAddress']['ZipCode'] = $apiKurier->polaczenie['INTEGRACJA_GEIS_NADAWCA_KOD_POCZTOWY'];

            $OrderRequest['DeliveryAddress']['Email'] = $apiKurier->polaczenie['INTEGRACJA_GEIS_NADAWCA_EMAIL'];
            $OrderRequest['DeliveryAddress']['FullName'] = $apiKurier->polaczenie['INTEGRACJA_GEIS_NADAWCA_IMIE_NAZWISKO'];
            $OrderRequest['DeliveryAddress']['Phone'] = $apiKurier->polaczenie['INTEGRACJA_GEIS_NADAWCA_TELEFON'];
        }

        $OrderRequest['DistributionChannel'] = '2';

        $OrderRequest['ExportItems']['ExportItem'] = array();

        if ( count($_POST['parcels']) > 1 ) {
            foreach ( $_POST['parcels'] as $Paczka ) {
                $Przesylka['CountItems'] = '1';
                $Przesylka['Description'] = $filtr->process($Paczka['zawartosc']);
                $Przesylka['Height'] = round($Paczka['wysokosc'], 2);
                $Przesylka['Length'] = round($Paczka['dlugosc'], 2);
                $Przesylka['Type'] = $filtr->process($Paczka['Type']);
                $Przesylka['Weight'] = round($Paczka['waga'], 2);
                $Przesylka['Width'] = round($Paczka['dlugosc'], 2);
                array_push($OrderRequest['ExportItems']['ExportItem'], $Przesylka);
                $weight_total += $_POST['parcels']['0']['waga'];
                unset($Przesylka);
            }
        } else {
            $OrderRequest['ExportItems']['ExportItem']['CountItems'] = '1';
            $OrderRequest['ExportItems']['ExportItem']['Description'] = $filtr->process($_POST['parcels']['0']['zawartosc']);
            $OrderRequest['ExportItems']['ExportItem']['Height'] = round($_POST['parcels']['0']['wysokosc'], 2);
            $OrderRequest['ExportItems']['ExportItem']['Length'] = round($_POST['parcels']['0']['dlugosc'], 2);
            $OrderRequest['ExportItems']['ExportItem']['Type'] = $filtr->process($_POST['parcels']['0']['Type']);
            $OrderRequest['ExportItems']['ExportItem']['Weight'] = round($_POST['parcels']['0']['waga'], 2);
            $OrderRequest['ExportItems']['ExportItem']['Width'] = round($_POST['parcels']['0']['dlugosc'], 2);
            $weight_total += $_POST['parcels']['0']['waga'];
        }

        $OrderRequest['ExportServices']['ExportService'] = array();
        
        if ( isset($_POST['Service']) ) {
            foreach($_POST['Service'] as $key => $value) {
                $Serwis['Code'] = $key;
                if ( is_array($value) ) {
                    $Serwis['Parameter_1'] = $value['param1'];
                }
                if ( $key == 'COD' || $key == 'POJ' ) {
                    $Serwis['Parameter_2'] = 'PLN';
                }
                array_push($OrderRequest['ExportServices']['ExportService'], $Serwis);
                unset($Serwis);
            }
        }
        
        $OrderRequest['Note'] = $filtr->process($_POST['Note']);
        $OrderRequest['NoteDriver'] = $filtr->process($_POST['NoteDriver']);

        $OrderRequest['PickUpDate'] = $_POST['PickupDatePaczki'];

        $OrderRequest['Reference'] = $filtr->process($_POST['Reference']);

        if ( $_POST['Uslugi'] == '20' ) {
            $OrderRequest['SenderAddress']['City'] = $apiKurier->polaczenie['INTEGRACJA_GEIS_NADAWCA_MIASTO'];
            $OrderRequest['SenderAddress']['Country'] = 'PL';
            $OrderRequest['SenderAddress']['Name'] = $apiKurier->polaczenie['INTEGRACJA_GEIS_NADAWCA_NAZWA'];
            $OrderRequest['SenderAddress']['Street'] = $apiKurier->polaczenie['INTEGRACJA_GEIS_NADAWCA_ULICA'];
            $OrderRequest['SenderAddress']['ZipCode'] = $apiKurier->polaczenie['INTEGRACJA_GEIS_NADAWCA_KOD_POCZTOWY'];

            $OrderRequest['SenderContact']['Email'] = $apiKurier->polaczenie['INTEGRACJA_GEIS_NADAWCA_EMAIL'];
            $OrderRequest['SenderContact']['FullName'] = $apiKurier->polaczenie['INTEGRACJA_GEIS_NADAWCA_IMIE_NAZWISKO'];
            $OrderRequest['SenderContact']['Phone'] = $apiKurier->polaczenie['INTEGRACJA_GEIS_NADAWCA_TELEFON'];
        }
        if ( $_POST['Uslugi'] == '21' ) {
            $OrderRequest['SenderAddress']['City'] = $filtr->process($_POST['DeliveryAddress']['City']);
            $OrderRequest['SenderAddress']['Country'] = $filtr->process($_POST['DeliveryAddress']['Country']);
            $OrderRequest['SenderAddress']['Name'] = $filtr->process($_POST['DeliveryAddress']['Name']);
            $OrderRequest['SenderAddress']['Name1'] = $filtr->process($_POST['DeliveryAddress']['Name1']);
            $OrderRequest['SenderAddress']['Street'] = $filtr->process($_POST['DeliveryAddress']['Street']);
            $OrderRequest['SenderAddress']['ZipCode'] = $filtr->process($_POST['DeliveryAddress']['ZipCode']);

            $OrderRequest['SenderAddress']['Email'] = $filtr->process($_POST['DeliveryContact']['Email']);
            $OrderRequest['SenderAddress']['FullName'] = $filtr->process($_POST['DeliveryContact']['FullName']);
            $OrderRequest['SenderAddress']['Phone'] = $filtr->process($_POST['DeliveryContact']['Phone']);
        }
        
        // Tworzy przesyłkę typu zamówienie dla produktu Cargo. Zamówienie może być utworzone najwcześniej dla następnego roboczego dnia

        if ( $_POST['Uslugi'] == '20' ) {
            $Wynik = $apiKurier->doInsertExport( $OrderRequest );
        }
        if ( $_POST['Uslugi'] == '21' ) {
            $Wynik = $apiKurier->doInsertOrder( $OrderRequest );
        }

        if ( $Wynik !== false ) {

            if ( $_POST['Uslugi'] == '20' ) {
                if ( isset($Wynik->InsertExportResult->ResponseObject->PackNumber) ) {

                    $pola = array(
                            array('orders_id',(int)$_POST["id"]),
                            array('orders_shipping_type',$api),
                            array('orders_shipping_number',$Wynik->InsertExportResult->ResponseObject->PackNumber),
                            array('orders_shipping_weight',$weight_total),
                            array('orders_parcels_quantity',count($_POST['parcels'])),
                            array('orders_shipping_status',$Wynik->InsertExportResult->Status),
                            array('orders_shipping_date_created', 'now()'),
                            array('orders_shipping_date_modified', 'now()'),
                            array('orders_shipping_comments', 'export'),
                            array('orders_shipping_protocol', $filtr->process($_POST['PickupDatePaczki'])),
                            array('orders_shipping_misc', ''),
                            array('orders_shipping_to_country', $_POST['DeliveryAddress']['Country']),

                    );

                    $db->insert_query('orders_shipping' , $pola);
                    unset($pola);

                }
            }

            if ( $_POST['Uslugi'] == '21' ) {
                if ( isset($Wynik->InsertOrderResult->ResponseObject->PackNumber) ) {

                    $pola = array(
                            array('orders_id',(int)$_POST["id"]),
                            array('orders_shipping_type',$api),
                            array('orders_shipping_number',$Wynik->InsertOrderResult->ResponseObject->PackNumber),
                            array('orders_shipping_weight',$weight_total),
                            array('orders_parcels_quantity',count($_POST['parcels'])),
                            array('orders_shipping_status',$Wynik->InsertOrderResult->Status),
                            array('orders_shipping_date_created', 'now()'),
                            array('orders_shipping_date_modified', 'now()'),
                            array('orders_shipping_comments', 'order'),
                            array('orders_shipping_protocol', $filtr->process($_POST['PickupDatePaczki'])),
                            array('orders_shipping_misc', ''),
                            array('orders_shipping_to_country', $_POST['DeliveryAddress']['Country']),

                    );

                    $db->insert_query('orders_shipping' , $pola);
                    unset($pola);

                }
            }

            Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));

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
        $waga_produktow = $zamowienie->waga_produktow;
        $wymiary        = array();

        $adres_klienta  = Funkcje::PrzeksztalcAdres($zamowienie->dostawa['ulica']);
        $wymiary['0'] = $apiKurier->polaczenie['INTEGRACJA_GEIS_WYMIARY_DLUGOSC'];
        $wymiary['1'] = $apiKurier->polaczenie['INTEGRACJA_GEIS_WYMIARY_SZEROKOSC'];
        $wymiary['2'] = $apiKurier->polaczenie['INTEGRACJA_GEIS_WYMIARY_WYSOKOSC'];

        $KrajDostawy = Klienci::pokazISOPanstwa($zamowienie->dostawa['kraj'], $zamowienie->klient['jezyk']);
        $NumerKatalogowy = '';

        if ( count($zamowienie->produkty) == 1 ) {
            foreach ( $zamowienie->produkty as $produkt ) {
                $NumerKatalogowy = $produkt['model'];
            }
        }
        ?>    

        <div class="poleForm">
            <div class="naglowek">Wysyłka za pośrednictwem firmy <?php echo $api; ?> - zamówienie numer : <?php echo $_GET['id_poz']; ?></div>

            <div class="pozycja_edytowana">  

                <?php
                $tablicaOpakowan = Array();

                $DostepneOpakowania = $apiKurier->doWrapList();

                if ( $DostepneOpakowania !== false ) {
                    if ( isset($DostepneOpakowania->WrapListResult->ResponseObject->Wrap) && count($DostepneOpakowania->WrapListResult->ResponseObject->Wrap) > 0 ) {

                        foreach ( $DostepneOpakowania->WrapListResult->ResponseObject->Wrap as $Opakowanie ) {

                            $tablicaOpakowan[] = array('id' => trim($Opakowanie->Code), 'text' => $Opakowanie->Description);

                        }
                    }
                }
                unset($DostepneOpakowania);
                $OpakowaniaSelect = Funkcje::RozwijaneMenu('parcels[][Type]', $tablicaOpakowan, 'HP', '');

                ?>

                <!-- skrypty do formularza paczki -->
                <script>
                    $(document).ready(function() {

                      if ($(".UsunPozycjeListy").length < 2) $(".UsunPozycjeListy").hide();
                      $("#addrow").click(function() {
                        
                        var id = $(".UsunPozycjeListy").length;

                        var WyborOpak = '<?php echo $OpakowaniaSelect; ?>';
                        WyborOpak = WyborOpak.replace("parcels[][Type]", "parcels["+id+"][Type]");

                        $(".item-row:last").after('<tr class="item-row"><td style="text-align:center"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td><td class="Paczka" style="padding-top:10px; padding-bottom:8px;">'+WyborOpak+'</td><td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="<?php echo $wymiary["0"]; ?>" size="8" name="parcels['+id+'][dlugosc]" class="kropkaPustaZero required" /></td><td class="Paczka"><input type="text" value="<?php echo $wymiary["1"]; ?>" size="8" name="parcels['+id+'][szerokosc]" class="kropkaPustaZero required" /></td><td class="Paczka"><input type="text" value="<?php echo $wymiary["2"]; ?>" size="8" name="parcels['+id+'][wysokosc]" class="kropkaPustaZero required" /></td><td class="Paczka"><input type="text" value="<?php echo ceil($waga_produktow); ?>" size="4" name="parcels['+id+'][waga]" class="kropkaPusta required" /></td><td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="<?php echo INTEGRACJA_GEIS_ZAWARTOSC; ?>" size="40" name="parcels['+id+'][zawartosc]" /></td></tr>');

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
                          zawartosc    : { required: true },
                          waga         : { digits: true }
                        }
                      });

                    });
                </script>

                <!-- skrypty dostepnych serwisow do formularza paczki -->
                <script>

                    $(document).ready(function() {
                        var dataBiezaca = new Date();
                        var dBiezacy = dataBiezaca.getDate();
                        var gBiezacy = dataBiezaca.getHours();
                        var PoleDaty = $('#PickupDate').val();
                        PoleDatyTab = PoleDaty.split('-');
                        if ( PoleDatyTab[2] == dBiezacy && gBiezacy > 10 ) {
                            $('#PrzyciskSubmitPickup').attr('disabled', 'disabled');
                        }
                    });

                    $(function() {

                        $("input[name='Uslugi']").bind('change', function(ev) {
                             var WybranySerwis = $("input[name='Uslugi']:checked").val();
                             var KrajDostawy = $('#adresat_CountryCode').val();
                             var id = <?php echo (int)$_GET['id_poz']; ?>;
                             var JestPodjazd = false;
                             if ( $("#SzczegolyPodjazdu p").length > 0 ) {
                                JestPodjazd = true;
                             }
                             if ( WybranySerwis == '21' ) {
                                 var date = new Date();
                                 date.setDate(date.getDate() + 1);
                                 var d = date.getDate();
                                 var m =  date.getMonth();
                                 m += 1;  // JavaScript months are 0-11
                                 if ( m < 10 ) {
                                     m = '0' + m;
                                 }
                                 var y = date.getFullYear();
                                 $("#SzczegolyPodjazdu").slideUp();
                                 $("#FormularzPodjazdu").slideUp();
                                 $("#Uslugi").val('21');
                                 $("#PickupDate").val(y + "-" + m + "-" + d);
                                 $('#PickupDatePaczki').val(y + "-" + m + "-" + d);

                             } else {
                                 var date = new Date();
                                 var d = date.getDate();
                                 var m =  date.getMonth();
                                 m += 1;  // JavaScript months are 0-11
                                 if ( m < 10 ) {
                                     m = '0' + m;
                                 }
                                 var y = date.getFullYear();
                                 $("#SzczegolyPodjazdu").slideDown();
                                 if ( JestPodjazd == false ) {
                                    $("#FormularzPodjazdu").slideDown();
                                 }
                                 $("#Uslugi").val('20');
                                 $("#PickupDate").val(y + "-" + m + "-" + d);
                                 $('#PickupDatePaczki').val(y + "-" + m + "-" + d);
                             }

                             $("#DostepneSerwisy").empty();

                             $("#DostepneSerwisy").html('<div style="margin:10px;margin-top:20px;text-align:center;"><img src="obrazki/_loader.gif"></div>');

                             $.ajax({
                                type: "POST",
                                url:  "ajax/geis_uslugi.php",
                                data: {Akcja: 'serwisy', Serwis: WybranySerwis, krajDostawy: KrajDostawy, ID: id },
                                success: function(msg){
                                        $("#DostepneSerwisy").html(msg).show(); 
                                        $(".kropka").change(
                                          function () {
                                            var type = this.type;
                                            var tag = this.tagName.toLowerCase();
                                            if (type == 'text' && tag != 'textarea' && tag != 'radio' && tag != 'checkbox') {
                                                //
                                                zamien_krp($(this),'0.00');
                                                //
                                            }
                                          }
                                        ); 
                                },
                             });

                        });
                    });
                </script>

                <!-- skrypty dostepnych uslug dodatkowych do formularza paczki -->
                <script>
                    $(document).ready(function() {
                        $('#COD').change(function() {
                            (($(this).is(':checked')) ? $("#COD_parametr1").prop('disabled', false) : $("#COD_parametr1").prop('disabled', true));
                            $("#COD_parametr1").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
                        });
                        $('#POJ').change(function() {
                            (($(this).is(':checked')) ? $("#POJ_parametr1").prop('disabled', false) : $("#POJ_parametr1").prop('disabled', true));
                            $("#POJ_parametr1").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
                        });
                    });
                </script>
                
                <!-- skrypty do formularza podjazdow -->
                <script>
                    $(document).ready(function() {
                        $('input.datepicker').Zebra_DatePicker({
                           format: 'Y-m-d H:i',
                           inside: false,
                           readonly_element: true,
                           show_clear_date: false,
                           enabled_minutes: [00],
                           disabled_dates: ['* * * 0,6'],
                           direction: <?php if ( date('H') > 10 ) { echo '1'; } else { echo 'true'; } ?>
                        });    
                        $('#PickupDate').Zebra_DatePicker({
                           format: 'Y-m-d',
                           inside: false,
                           readonly_element: true,
                           show_clear_date: false,
                           disabled_dates: ['* * * 0,6'],
                           //direction: true,
                           onSelect: function() {
                               var podjazd = $('#PickupDate').val();
                               if ( $('#service_20').is(':checked') ) {
                                   var id = <?php echo (int)$_GET['id_poz']; ?>;

                                   $('#PickupDatePaczki').val(podjazd);
                                   $('#DateFrom').val(podjazd + ' 08:00');
                                   $('#DateTo').val(podjazd + ' 18:00');
                                   $("#Przetwarzanie").html('<div style="margin:10px;margin-top:10px;text-align:center;"><img src="obrazki/_loader.gif"></div>');
                                   $.ajax({
                                    type: "POST",
                                    url:  "ajax/geis_uslugi.php",
                                    data: {Akcja: 'podjazdy', DataPodjazdu: podjazd, ID: id },
                                    success: function(msg){
                                            $("#Przetwarzanie").empty();
                                            if ( msg == 'false' ) {
                                                $('#SzczegolyPodjazdu').slideUp();
                                                $('#FormularzPodjazdu').slideDown();
                                                $('#PrzyciskSubmit').attr('disabled', 'disabled');
                                                let dataBiezaca = new Date();
                                                let dBiezacy = dataBiezaca.getDate();
                                                let gBiezacy = dataBiezaca.getHours();
                                                let PoleDaty = $('#PickupDate').val();
                                                PoleDatyTab = PoleDaty.split('-');
                                                $('#PrzyciskSubmitPickup').removeAttr('disabled');
                                                if ( PoleDatyTab[2] == dBiezacy && gBiezacy > 10 ) {
                                                    $('#PrzyciskSubmitPickup').attr('disabled', 'disabled');
                                                }
                                            } else {
                                                $('#FormularzPodjazdu').slideUp();
                                                $('#SzczegolyPodjazdu').html(msg).show();
                                                $('#PrzyciskSubmit').removeAttr('disabled');

                                            }
                                    },
                                   });
                               } else {
                                   $('#PickupDatePaczki').val(podjazd);
                               }
                           }
                        });    
                        $("#apiFormKurier").validate({
                        rules: {
                          CountItems    : { required: true },
                          TotalWeight   : { required: true }
                        }
                        });
                        setTimeout(function() {
                          $('#ZamowienieOK').fadeOut();
                        }, 3000);
                    });

                    function ZamowienieRozwin() {

                        $('#ButZamowienieKuriera').slideUp();
                        $('#ZamowienieKuriera').slideDown();
                    }
                </script> 

                <?php
                $data = date('Y-m-d');
                $Zamowienia = $apiKurier->doPickupInfo( $data );
                if ( $Zamowienia !== false ) {
                    if ( isset($Zamowienia->PickupInfoResult->ResponseObject->Pickups) ) {
                        $JestPodjazd = true;
                    }
                }
                ?>

                <form action="sprzedaz/zamowienia_wysylka_geis.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="apiFormKurier" class="cmxform">

                    <div>
                        <input type="hidden" name="akcja_kurier" value="zapisz" />
                        <input type="hidden" name="id_kurier" value="<?php echo $_GET['id_poz']; ?>" />
                    </div>

                    <div class="TabelaWysylek">
                        <div class="OknoPrzesylki" style="width:100%;">
                            <div class="poleForm" style="margin-bottom:0;">

                                <div class="naglowek">Dostępne usługi</div>

                                <p style="border-bottom:1px dashed #ccc; margin-bottom:10px; padding-bottom:10px;">
                                    <label>Dostępne serwisy:</label>

                                    <?php
                                    $DostepneSerwisy = $apiKurier->doServiceList();
                                    if ( $DostepneSerwisy !== false ) {
                                        if ( isset($DostepneSerwisy->ServiceListResult->ResponseObject->Service) && count($DostepneSerwisy->ServiceListResult->ResponseObject->Service) > 0 ) {
                                            $Domyslny = true;
                                            foreach ( $DostepneSerwisy->ServiceListResult->ResponseObject->Service as $Serwis ) {

                                                echo '<input type="radio" style="border:0px" name="Uslugi" value="'.$Serwis->Code.'" id="service_'.$Serwis->Code.'" ' .( $Domyslny == true ? 'checked="checked"' : '' ) . ' /><label class="OpisFor" for="service_'.$Serwis->Code.'" >'.$Serwis->Name.'</label>';
                                                $Domyslny = false;

                                            }
                                        } else {
                                            echo 'Brak dostępnych serwisów';
                                        }
                                    }
                                    unset($DostepneSerwisy, $Domyslny);
                                    ?>
                                </p>
                            
                                <p id="PoleDatyOdbioru">
                                    <label for="PickupDate">Data odbioru:</label>
                                    <input type="text" name="PickupDate" id="PickupDate" value="<?php echo date('Y-m-d'); ?>" class="datepickerOdbior">
                                </p>
                                <div id="Przetwarzanie"></div>
                                <?php
                                if ( isset($Zamowienia) && is_string($Zamowienia) ) {
                                    $JestPodjazd = false;
                                    $LimitZapytan = true;
                                    echo '<div class="ostrzezenie" style="margin:10px 0 0 25px" id="Ostrzezenie">'.$Zamowienia.'</div>';
                                }

                                ?>
                                <div id="SzczegolyPodjazdu" <?php echo ( $JestPodjazd == false ? 'style="display:none;"' : '' ); ?>>
                                    <?php
                                        if ( $ZamowienieOK == true ) {
                                            echo '<div class="maleInfo" style="margin-left:25px" id="ZamowienieOK">Zamówienie zostało utworzone</div>';
                                        }
                                    ?>

                                    <?php


                                    if ( isset($Zamowienia->PickupInfoResult->ResponseObject->Pickups) ) {
                                        if ( is_array($Zamowienia->PickupInfoResult->ResponseObject->Pickups->PickupInfoItem) && count($Zamowienia->PickupInfoResult->ResponseObject->Pickups->PickupInfoItem) > 0 ) {
                                            $date = date_create($Zamowienia->PickupInfoResult->ResponseObject->Pickups->PickupInfoItem['0']->Date);
                                            echo '<p><label>Data podjazdu:</label>' . date_format($date, 'Y-m-d H:i') . '</p>';
                                            echo '<p><label>Status podjazdu:</label>' . $Zamowienia->PickupInfoResult->ResponseObject->Pickups->PickupInfoItem['0']->State . '</p>';
                                        } else {
                                            $date = date_create($Zamowienia->PickupInfoResult->ResponseObject->Pickups->PickupInfoItem->Date);
                                            echo '<p><label>Data podjazdu:</label>' . date_format($date, 'Y-m-d H:i') . '</p>';
                                            echo '<p><label>Status podjazdu:</label>' . $Zamowienia->PickupInfoResult->ResponseObject->Pickups->PickupInfoItem->State . '</p>';
                                        }
                                    }
                                    ?>
                                </div>
                                <div id="FormularzPodjazdu" <?php echo ( $JestPodjazd == true || $LimitZapytan == true ? 'style="display:none;"' : '' ); ?>>

                                    <?php

                                        $BiezacaGodzina = date('H', time());
                                        if ( $BiezacaGodzina < 10 ) {
                                            $DataPodjazduOd = date('Y-m-d H', time());
                                            $DataPodjazduDo = date('Y-m-d 18');
                                        } else {
                                            $DataPodjazduOd = date('Y-m-d 8', FunkcjeWlasnePHP::my_strtotime("+1 day", time()));
                                            $DataPodjazduDo = date('Y-m-d 18', FunkcjeWlasnePHP::my_strtotime("+1 day", time()));
                                        }
                                        ?>

                                        <div style="margin-left:15px;" id="ZamowienieKurieraInfo"><span class="maleInfo">Brak zamówienia odbioru dla wybranego dnia</span></div>

                                        <div id="ZamowienieKuriera">
                                            <p>
                                                <label for="DateFrom">Data podjazdu od:</label>
                                                <input type="text" name="DateFrom" id="DateFrom" value="<?php echo $DataPodjazduOd . ':00'; ?>" class="datepicker">
                                            </p>
                                            <p>
                                                <label for="DateTo">Data podjazdu do:</label>
                                                <input type="text" name="DateTo" id="DateTo" value="<?php echo $DataPodjazduDo . ':00'; ?>" class="datepicker">
                                            </p>
                                            <p>
                                                <label for="Note">Uwagi dla kuriera:</label>
                                                <textarea cols="45" rows="2" name="Note" id="Note" ></textarea>
                                            </p>
                                            <p>
                                                <label for="CountItems" class="required">Ilość sztuk [szt]:</label>
                                                <input type="text" name="CountItems" id="CountItems" value="" class="required">
                                            </p>
                                            <p>
                                                <label for="TotalWeight" class="required">Waga całkowita [kg]:</label>
                                                <input type="text" name="TotalWeight" id="TotalWeight" value="" class="kropkaPustaZero" class="required">
                                            </p>

                                            <div class="przyciski_dolne">
                                                <input type="submit" class="przyciskNon" id="PrzyciskSubmitPickup" value="Zamów kuriera" />
                                                <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','zakladka')); ?>','sprzedaz');">Powrót</button>           
                                            </div>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>




                </form>

                <form action="sprzedaz/zamowienia_wysylka_geis.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="apiForm" class="cmxform">

                  <div>
                      <input type="hidden" name="akcja" value="zapisz" />
                      <input type="hidden" name="id" value="<?php echo $_GET['id_poz']; ?>" />
                      <input type="hidden" name="zakladka" value="<?php echo $_GET['zakladka']; ?>" />
                      <input type="hidden" id="Uslugi" name="Uslugi" value="20" />
                      <input type="hidden" id="wartosc_zamowienia_val" name="wartosc_zamowienia_val" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" />
                      <input type="hidden" name="PickupDatePaczki" id="PickupDatePaczki" value="<?php echo date('Y-m-d'); ?>">

                  </div>
                  
                  <div class="TabelaWysylek">

                    <div class="OknoPrzesylki">

                        <div class="poleForm">


                            <div class="naglowek">Informacje o przesyłce</div>

                            <?php
                            echo '<div id="DostepneSerwisy">';
                                $DodatkoweUslugi = $apiKurier->doAddServiceList( '20', $KrajDostawy );
                                if ( $DodatkoweUslugi !== false ) {
                                    if ( isset($DodatkoweUslugi->AddServiceListResult->ResponseObject->AddService) && count($DodatkoweUslugi->AddServiceListResult->ResponseObject->AddService) > 0 ) {
                                        foreach ( $DodatkoweUslugi->AddServiceListResult->ResponseObject->AddService as $Usluga ) {

                                            echo '<p>
                                                    <label for="'.$Usluga->Abbreviation.'">'.$Usluga->Description.':</label>
                                                    <input type="checkbox" value="1" name="Service['.$Usluga->Abbreviation.']" id="'.$Usluga->Abbreviation.'" /><label class="OpisForPustyLabel" for="'.$Usluga->Abbreviation.'" style="margin-right:10px;"></label>';

                                                    if ( $Usluga->Abbreviation == 'COD' || $Usluga->Abbreviation == 'POJ' ) {
                                                        echo '<input class="kropkaPustaZero" type="text" size="20" name="Service['.$Usluga->Abbreviation.'][param1]" id="'.$Usluga->Abbreviation.'_parametr1" value="" disabled="disabled" />';
                                                    }

                                            echo '</p>';

                                        }
                                    } else {
                                        echo 'Brak dostępnych usług dodatkowych';
                                    }

                                }
                           echo '</div>';
                           unset($DodatkoweUslugi);
                           ?>
                           <p>
                             <label for="Reference">Referencja:</label>
                             <input type="text" name="Reference" id="Reference" size="35" value="<?php echo $_GET['id_poz'] . ( $NumerKatalogowy != '' ? ' / '. $NumerKatalogowy : '' ); ?>" />
                           </p>
                           <p>
                             <label for="Note">Uwagi:</label>
                             <textarea cols="45" rows="2" name="Note" id="Note" ></textarea>
                           </p>

                           <p>
                             <label for="NoteDriver">Uwagi dla kierowcy:</label>
                             <textarea cols="45" rows="2" name="NoteDriver" id="NoteDriver" ></textarea>
                           </p>


                        </div>

                        <div class="poleForm">

                            <div class="naglowek">Informacje o paczkach</div>

                            <table class="listing_tbl">
                              <tr>
                                <td style="width:50px"></td>
                                <td class="Paczka">Jednostka</td>
                                <td class="Paczka">Długość[m]</td>
                                <td class="Paczka">Szerokość[m]</td>
                                <td class="Paczka">Wysokość[m]</td>
                                <td class="Paczka">Waga [kg]</td>
                                <td class="Paczka">Zawartość</td>
                              </tr>

                              <tr class="item-row">
                                <td style="text-align:right"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td>
                                <td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><?php echo Funkcje::RozwijaneMenu('parcels[0][Type]', $tablicaOpakowan, 'HP', ''); ?></td>
                                <td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="<?php echo $wymiary['0']; ?>" size="8" name="parcels[0][dlugosc]" class="kropkaPustaZero required" /></td>
                                <td class="Paczka"><input type="text" value="<?php echo $wymiary['1']; ?>" size="8" name="parcels[0][szerokosc]" class="kropkaPustaZero required" /></td>
                                <td class="Paczka"><input type="text" value="<?php echo $wymiary['2']; ?>" size="8" name="parcels[0][wysokosc]" class="kropkaPustaZero required" /></td>
                                <td class="Paczka"><input type="text" value="<?php echo ceil($waga_produktow); ?>" size="4" name="parcels[0][waga]" class="kropkaPustaZero required" /></td>
                                <td class="Paczka"><input type="text" value="<?php echo INTEGRACJA_GEIS_ZAWARTOSC; ?>" size="40" name="parcels[0][zawartosc]"  /></td>
                              </tr>

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
                            <?php
                            $AdresatFirma = $zamowienie->dostawa['firma'];
                            if ( preg_replace('/[^a-z\d ]/i', '', $zamowienie->dostawa['firma']) == '' ) {
                                $AdresatFirma = '';
                            }
                            ?>
                            <p>
                                <label for="adresat_Name">Nazwa firmy:</label>
                                <input type="text" size="40" name="DeliveryAddress[Name1]" id="adresat_Name1" value="<?php echo preg_replace('!\s+!', ' ', (string)$AdresatFirma); ?>"  class="klient" />
                            </p> 
                            <p>
                                <label for="adresat_Name">Nazwa odbiorcy:</label>
                                <input type="text" size="40" name="DeliveryAddress[Name]" id="adresat_Name" value="<?php echo preg_replace('!\s+!', ' ', (string)$zamowienie->dostawa['nazwa']); ?>"  class="klient" />
                            </p> 
                            <p>
                                <label for="adresat_Address">Ulica:</label>
                                <input type="text" size="40" name="DeliveryAddress[Street]" id="adresat_Address" value="<?php echo $zamowienie->dostawa['ulica']; ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="adresat_PostalCode">Kod pocztowy:</label>
                                <input type="text" size="40" name="DeliveryAddress[ZipCode]" id="adresat_PostalCode" value="<?php echo Klienci::KodPocztowyDostawy( (string)$zamowienie->dostawa['kod_pocztowy'], $KrajDostawy); ?>"  class="klient" />
                            </p> 
                            <p>
                                <label for="adresat_City">Miejscowość:</label>
                                <input type="text" size="40" name="DeliveryAddress[City]" id="adresat_City" value="<?php echo $zamowienie->dostawa['miasto']; ?>"  class="klient" />
                            </p> 

                            <p>
                                <label for="adresat_CountryCode">Kraj:</label>
                                    <?php 
                                    $tablicaPanstw = $apiKurier->getCountrySelect($zamowienie->dostawa['kraj']); 
                                    echo Funkcje::RozwijaneMenu('DeliveryAddress[Country]', $tablicaPanstw, $KrajDostawy, 'id="adresat_CountryCode" class="klient" style="width:210px;"' ); 

                                    unset($tablicaPanstw);
                                    ?>
                            </p> 

                            <p>
                                <label for="adresat_Phone">Numer telefonu:</label>
                                <?php 
                                if ( $zamowienie->dostawa['telefon'] != '' ) {
                                    $NumerTelefonu = preg_replace( '/[^0-9+]/', '', $zamowienie->dostawa['telefon']);
                                } else {
                                    $NumerTelefonu = preg_replace( '/[^0-9+]/', '', $zamowienie->klient['telefon']);
                                }
                                ?>

                                <input type="text" size="40" name="DeliveryContact[Phone]" id="adresat_Phone" value="<?php echo preg_replace('~^(?:0|\+48)?~', '+48', $NumerTelefonu); ?>"  class="klient" />
                            </p> 
                            <p>
                                <label for="adresat_Email">Adres e-mail:</label>
                                <input type="text" size="40" name="DeliveryContact[Email]" id="adresat_Email" value="<?php echo $zamowienie->klient['adres_email']; ?>"  class="klient" />
                            </p> 

                            <p>
                                <label for="adresat_FullName">Osoba kontaktowa:</label>
                                <input type="text" size="40" name="DeliveryContact[FullName]" id="adresat_FullName" value="<?php echo preg_replace('!\s+!', ' ', (string)$zamowienie->dostawa['nazwa']); ?>" class="klient" />
                            </p>                         
                        </div>
                        
                    </div>

                  </div>

                  <div class="przyciski_dolne">
                    <input type="submit" class="przyciskNon" id="PrzyciskSubmit" value="Utwórz przesyłkę" <?php echo ( $JestPodjazd == false ? 'disabled="disabled"' : '' ); ?>/>
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
