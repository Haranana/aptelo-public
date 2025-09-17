<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $api = 'APACZKA';
    $apiKurier = new ApaczkaApiV2();
    $komunikat = '';
    $blad = true;

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $PunktNadania = '';

        if ( $_POST['Pickup'] == 'POINT' ) {
            if ( $_POST['operatorName'] == 'UPS' ) {
                $PunktNadania = $apiKurier->polaczenie['INTEGRACJA_APACZKAV2_POINT_UPS'];
            } elseif ( $_POST['operatorName'] == 'INPOST' && $_POST['operatorId'] != '42' ) {
                $PunktNadania = $apiKurier->polaczenie['INTEGRACJA_APACZKAV2_POINT_INPOST'];
            } elseif ( $_POST['operatorName'] == 'POCZTA' ) {
                $PunktNadania = $apiKurier->polaczenie['INTEGRACJA_APACZKAV2_POINT_POCZTA'];
            } elseif ( $_POST['operatorName'] == 'DHL_PARCEL' ) {
                $PunktNadania = $apiKurier->polaczenie['INTEGRACJA_APACZKAV2_POINT_DHL_PARCEL'];
            } elseif ( $_POST['operatorName'] == 'DPD' ) {
                $PunktNadania = $apiKurier->polaczenie['INTEGRACJA_APACZKAV2_POINT_DPD'];
            } elseif ( $_POST['operatorName'] == 'PWR' ) {
                $PunktNadania = $apiKurier->polaczenie['INTEGRACJA_APACZKAV2_POINT_ORLEN'];
            }
        }

        $DaneWejsciowe = [
            'service_id'     => $_POST['operatorId'],
            'address'        => [
                'sender'   => [
                    'country_code'   => 'PL',
                    'name'           => $apiKurier->polaczenie['INTEGRACJA_APACZKAV2_NADAWCA_NAZWA'],
                    'line1'          => $apiKurier->polaczenie['INTEGRACJA_APACZKAV2_NADAWCA_ADRES1'],
                    'line2'          => '',
                    'postal_code'    => $apiKurier->polaczenie['INTEGRACJA_APACZKAV2_NADAWCA_KOD_POCZTOWY'],
                    'city'           => $apiKurier->polaczenie['INTEGRACJA_APACZKAV2_NADAWCA_MIASTO'],
                    'is_residential' => $apiKurier->polaczenie['INTEGRACJA_APACZKAV2_NADAWCA_TYP_ADRESU'],
                    'contact_person' => $apiKurier->polaczenie['INTEGRACJA_APACZKAV2_OSOBA_NADAJACA'],
                    'email'          => $apiKurier->polaczenie['INTEGRACJA_APACZKAV2_NADAWCA_EMAIL'],
                    'phone'          => $apiKurier->polaczenie['INTEGRACJA_APACZKAV2_NADAWCA_TELEFON'],
                    'foreign_address_id' => $PunktNadania
                ],
                'receiver' => [
                    'country_code'   => $_POST['receiver']['countryId'],
                    'name'           => $_POST['receiver']['name'],
                    'line1'          => $_POST['receiver']['addressLine1'],
                    'line2'          => '',
                    'postal_code'    => $_POST['receiver']['postalCode'],
                    'city'           => $_POST['receiver']['city'],
                    'is_residential' => $_POST['receiver']['dor_osoba_pryw'],
                    'contact_person' => $_POST['receiver']['contactName'],
                    'email'          => $_POST['receiver']['email'],
                    'phone'          => str_replace('+48', '', (string)$_POST['receiver']['phone']),
                    'foreign_address_id' => ( isset($_POST['integracja_apaczka_'.strtolower((string)$_POST['operatorName'])]) ? $_POST['integracja_apaczka_'.strtolower((string)$_POST['operatorName'])] : '' )
                ]
            ],
            'option'         => ( isset($_POST['option']) ? $_POST['option'] : array() ),
            'shipment_value' => ( isset($_POST['insurance']) ? $_POST['insuranceValue'] * 100 : '' ),
            'cod' => [
                'amount' => ( isset($_POST['cod']) ? $_POST['codValue'] * 100 : '' ),
                'bankaccount' => ( isset($_POST['cod']) ? $apiKurier->polaczenie['INTEGRACJA_APACZKAV2_NUMER_KONTA'] : '' ),
            ],
            'pickup'         => [
                'type' => $_POST['Pickup'],//, COURIER, SELF, BOX_MACHINE, POCZTA
                'date' => (isset($_POST['pickup_date']) && $_POST['Pickup'] == 'COURIER' ? substr((string)$_POST['pickup_date'], 0, 10) : ''), // Y-m-d,
                'hours_from' => (isset($_POST['pickup_date_hours_from']) && $_POST['Pickup'] == 'COURIER' ? $_POST['pickup_date_hours_from'] : ''),
                'hours_to' => (isset($_POST['pickup_date_hours_to']) && $_POST['Pickup'] == 'COURIER' ? $_POST['pickup_date_hours_to'] : '')
            ],
            'shipment'       => [
                [
                    'dimension1' => $_POST['parcel']['dlugosc']['0'],
                    'dimension2' => $_POST['parcel']['szerokosc']['0'],
                    'dimension3' => $_POST['parcel']['wysokosc']['0'],
                    'weight'     => $_POST['parcel']['waga']['0'],
                    'is_nstd'    => 0,
                    'shipment_type_code' => $_POST['packtype']
                ]
            ],
            'comment'        => $_POST['comment'],
            'content'        => $_POST['additionalInformation']
        ];

        $Wynik = $apiKurier->order_send($DaneWejsciowe);
        $Wynik = json_decode($Wynik);

        if ( isset($Wynik->status) && $Wynik->status == '200' ) {

            $pola = array(
                    array('orders_id',$filtr->process($_POST["id"])),
                    array('orders_shipping_type',$api),
                    array('orders_shipping_number',$Wynik->response->order->waybill_number),
                    array('orders_shipping_weight',$_POST['parcel']['waga']['0']),
                    array('orders_parcels_quantity',$Wynik->response->order->shipments_count),
                    array('orders_shipping_status',$Wynik->response->order->status),
                    array('orders_shipping_date_created', $Wynik->response->order->created),
                    array('orders_shipping_date_modified', $Wynik->response->order->created),
                    array('orders_shipping_comments', $Wynik->response->order->service_name),
                    array('orders_shipping_misc', $Wynik->response->order->id),
                    array('orders_shipping_link', $Wynik->response->order->tracking_url)
              );

              $db->insert_query('orders_shipping' , $pola);
              unset($pola);

              Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));
        }

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');

    if ( isset($komunikat) && $komunikat != '' ) {
      echo Okienka::pokazOkno('Błąd', str_replace('"', '', (string)$komunikat));
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

            <script src="programy/timepicker/jquery.timepicker.js"></script>
            <link rel="stylesheet" type="text/css" href="programy/timepicker/jquery.timepicker.css" />

            <script src="https://mapa.apaczka.pl/client/apaczka.map.js"></script>
            <style>
              .apaczkaMapPopup .popupButtons { z-index:99; }
            </style>

            <script>
                function PokazMape(Kurier) {
                    var dostawca = Kurier;
                    var kur = dostawca.toLowerCase();
                    var app_id = '<?php echo $apiKurier->polaczenie['INTEGRACJA_APACZKAV2_APP_ID']; ?>';

                    var apaczkaMap = new ApaczkaMap({
                        app_id : app_id,
                        criteria : [
                            {field: 'services_receiver', operator: 'eq', value: true}
                        ],
                        onChange : function( record) {
                            if (record) {
                                $('#preferowany_' + kur + '').val(record.name + ' ' + record.street + ', ' + record.postal_code + ' ' + record.city);
                                $('#integracja_apaczka_' + kur + '').val(record.foreign_access_point_id);
                            }
                        }
                    });
                    apaczkaMap.setSupplier(dostawca);
                    apaczkaMap. setFilterSupplierAllowed(
                        [dostawca],
                        [dostawca]
                    );
                    apaczkaMap.show({});
                }
            </script>


            <script>
            $(document).ready(function() {

              $('input.datepicker').Zebra_DatePicker({
                format: 'Y-m-d',
                inside: false,
                readonly_element: false
              });                

              $("#apiForm").validate({
                  rules: {
                    waga         : { digits: true }
                  }
              });

              $('#cod').change(function() {
                (($(this).is(':checked')) ? $("#codValue").prop('disabled', false) : $("#codValue").prop('disabled', true));
                $("#codValue").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
              });
              $('#insurance').change(function() {
                  //$("#insuranceValue").val(($(this).is(':checked')) ? $("#insuranceValue").show() : $("#insuranceValue").hide());
                  $("#insuranceValue").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
              });

              $('#deliverytype').change(function() {
                $('#PrzyciskZatwierdz').attr('disabled','disabled');
                $('#DostepneUslugi').slideUp();
                $('#DaneWysylki').slideUp();
                $('#Uslugi').html('');
                $('#Wysylka').html('');
              });

              $('#packtype').change(function() {
                $('#DostepneUslugi').slideUp();
                $('#DaneWysylki').slideUp();
                $('#Uslugi').html('');
                $('#Wysylka').html('');
              });

              $(':checkbox').change(function() {
                $('#DostepneUslugi').slideUp();
                $('#DaneWysylki').slideUp();
                $('#Uslugi').html('');
                $('#Wysylka').html('');
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
                            url: "ajax/apaczka.php",
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
            if ( isset($_POST['id']) ) $_GET['id_poz'] = $_POST['id'];
            $zamowienie = new Zamowienie((int)$_GET['id_poz']);

            $wymiary        = array();
            $wymiary['0'] = $apiKurier->polaczenie['INTEGRACJA_APACZKAV2_WYMIARY_DLUGOSC'];
            $wymiary['1'] = $apiKurier->polaczenie['INTEGRACJA_APACZKAV2_WYMIARY_SZEROKOSC'];
            $wymiary['2'] = $apiKurier->polaczenie['INTEGRACJA_APACZKAV2_WYMIARY_WYSOKOSC'];

            $waga_produktow = $zamowienie->waga_produktow;
            if ( $waga_produktow == '0' ) {
                $waga_produktow = $apiKurier->polaczenie['INTEGRACJA_APACZKAV2_WYMIARY_WAGA'];
            }

            ?>

            <form action="sprzedaz/zamowienia_wysylka_apaczka.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="apiForm" class="cmxform"> 
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="id" value="<?php echo $_GET['id_poz']; ?>" />
                  <input type="hidden" name="zakladka" value="<?php echo $_GET['zakladka']; ?>" />
                  <input type="hidden" name="klient_id" value="<?php echo $zamowienie->klient['id']; ?>" />
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
                                $tablica = $apiKurier->apaczka_product_array(false);
                                echo Funkcje::RozwijaneMenu('deliverytype', $tablica, $domyslnyDostawa, 'id="deliverytype" style="width:326px;"');
                                unset($tablica);
                                ?>

                            </p> 

                            <p>
                                <label for="packtype">Rodzaj przesyłki:</label>
                                <?php
                                $domyslnyPaczka = 'package';
                                $tablica = $apiKurier->apaczka_packtype_array(false);
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
                                <label for="insurance" style="height:28px; line-height:28px;">Deklarowana wartość:</label>
                                <?php if ( strpos((string)$zamowienie->info['metoda_platnosci'], 'pobranie') === false && strpos((string)$zamowienie->info['metoda_platnosci'], 'odbiorze') === false) { ?>
                                    <input type="checkbox" value="1" name="insurance" id="insurance" style="margin-right:20px;" <?php echo ( isset($_POST['insurance']) ? 'checked="checked"' : '' ); ?> /><label class="OpisForPustyLabel" for="insurance" style="margin-right:10px;"></label>
                                    <input class="kropkaPustaZero" type="text" size="20" name="insuranceValue" id="insuranceValue" value="<?php echo ( isset($_POST['insuranceValue']) ? $_POST['insuranceValue'] : '' ); ?>" />
                                <?php } else { ?>
                                    <input type="checkbox" value="1" name="insurance" id="insurance" style="margin-right:20px;" checked="checked" /><label class="OpisForPustyLabel" for="insurance" style="margin-right:10px;"></label>
                                    <input class="kropkaPustaZero" type="text" size="20" name="insuranceValue" id="insuranceValue" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" />
                                <?php } ?>
                            </p>

                            <p>
                                <label for="comment">Dodatkowy komentarz:</label>
                                <input type="text" name="comment" id="comment" value="<?php echo ( isset($_POST['comment']) ? $_POST['comment'] :  (int)$_GET['id_poz']); ?>" size="53" />
                            </p>

                            <div class="naglowek" style="margin:10px 0;">Usługi dodatkowe</div>

                            <div style="display:flex;flex-wrap:wrap;align-items: center;justify-content: space-between;">
                            <?php
                            if ( isset($apiKurier->struktury_serwisu->response->options) && count((array)$apiKurier->struktury_serwisu->response->options) > 0 ) {
                                foreach ( $apiKurier->struktury_serwisu->response->options as $klucz => $parametr ) {
                                    echo '<div><label for="option'.$klucz.'">'.$parametr->name.':</label> ';
                                    echo '<input type="checkbox" value="1" name="option['.$klucz.']" id="option'.$klucz.'" style="margin-right:20px;" /><label class="OpisForPustyLabel" for="option'.$klucz.'" style="margin-right:10px;"></label>'.( isset($parametr->desc) && $parametr->desc != '' ? '<em class="TipIkona"><b>'. $parametr->desc.'</b></em>' : '<em class="TipIkona"><b>Brak informacji</b></em>' ).'</div>';
                                }
                            }
                            ?>
                            </div>

                            <div class="naglowek" style="margin:10px 0;">Informacje o paczce</div>

                            <table class="listing_tbl" style="border-bottom:1px dashed #cccccc; margin-bottom:15px;">
                                <tr>
                                    <td style="width:50px"></td>
                                    <td class="Paczka" style="padding-top:8px;">Długość [cm]</td>
                                    <td class="Paczka">Szerokość [cm]</td>
                                    <td class="Paczka">Wysokość [cm]</td>
                                    <td class="Paczka">Waga [kg]</td>
                                </tr>

                                <tr class="item-row" id="PaczkaGlowna">
                                    <td style="width:50px"></td>
                                    <td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="<?php echo ( isset($_POST['parcel']['dlugosc']['0']) ? $_POST['parcel']['dlugosc']['0'] : $wymiary['0'] ); ?>" size="8" name="parcel[dlugosc][]" oninput="this.value=this.value.replace(/[^0-9]/g,'');" class="required" /></td>
                                    <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['szerokosc']['0']) ? $_POST['parcel']['szerokosc']['0'] : $wymiary['1'] ); ?>" size="8" name="parcel[szerokosc][]" oninput="this.value=this.value.replace(/[^0-9]/g,'');" class="required" /></td>
                                    <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['wysokosc']['0']) ? $_POST['parcel']['wysokosc']['0'] : $wymiary['2'] ); ?>" size="8" name="parcel[wysokosc][]" oninput="this.value=this.value.replace(/[^0-9]/g,'');" class="required" /></td>
                                    <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['waga']['0']) ? $_POST['parcel']['waga']['0'] : $waga_produktow ); ?>" size="8" name="parcel[waga][]" class="kropkaPustaZero required" /></td>
                                    </td>

                                </tr>

                            </table>

                            <p>
                                <label for="additionalInformation">Zawartość:</label>
                                <textarea cols="45" rows="2" name="additionalInformation" id="additionalInformation" ><?php echo ( isset($_POST['additionalInformation']) ? $_POST['additionalInformation'] : $apiKurier->polaczenie['INTEGRACJA_APACZKAV2_ZAWARTOSC'] ); ?></textarea>
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
                        if ( $zamowienie->info['wysylka_punkt_odbioru'] != '' ) {
                            ?>
                            <p>
                                <label class="readonly">Kod punktu odbioru:</label>
                                <input type="text" size="34" name="punkt_odbioru_kod" id="punkt_odbioru_kod" value="<?php echo $zamowienie->info['wysylka_punkt_odbioru']; ?>" readonly="readonly" class="readonly" />
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

                                <div class="naglowek">Informacje o odbiorcy</div>

                                <p>
                                    <label for="name">Nazwa odbiorcy (nazwa firmy):</label>
                                    <input type="text" size="40" name="receiver[name]" id="name" value="<?php echo ( $zamowienie->dostawa['firma'] != '' ? Funkcje::formatujTekstInput($zamowienie->dostawa['firma']) : $zamowienie->dostawa['nazwa'] ); ?>" class="klient" />
                                </p> 

                                <p>
                                    <label>Typ adresu odbiorcy</label>
                                    <input type="radio" style="border:0px" name="receiver[dor_osoba_pryw]" value="0" id="dor_osoba_F" <?php echo ( $zamowienie->dostawa['firma'] != '' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="dor_osoba_F">Firmowy </label>
                                    <input type="radio" style="border:0px" name="receiver[dor_osoba_pryw]" value="1" id="dor_osoba_P" <?php echo ( $zamowienie->dostawa['firma'] == '' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="dor_osoba_P">Prywatny</label>
                                </p> 


                                <p>
                                    <label for="addressLine1">Pierwsza linia adresu:</label>
                                    <input type="text" size="40" name="receiver[addressLine1]" id="addressLine1" value="<?php echo $zamowienie->dostawa['ulica']; ?>" class="klient" />
                                </p> 

                                <p>
                                    <label for="addressLine2">Druga linia adresu:</label>
                                    <input type="text" size="40" name="receiver[addressLine2]" id="addressLine2" value="" class="klient" />
                                </p> 

                                <p>
                                    <label for="postalCode">Kod pocztowy:</label>
                                    <input type="text" size="40" name="receiver[postalCode]" id="postalCode" value="<?php echo $zamowienie->dostawa['kod_pocztowy']; ?>" class="klient" />
                                </p> 

                                <p>
                                    <label for="city">Miejscowość:</label>
                                    <input type="text" size="40" name="receiver[city]" id="city" value="<?php echo $zamowienie->dostawa['miasto']; ?>" class="klient" />
                                </p> 

                                <p>
                                    <label for="countryId">Kraj:</label>
                                    <?php 
                                    $domyslnie = Klienci::pokazISOPanstwa($zamowienie->dostawa['kraj'], $zamowienie->klient['jezyk']); 
                                    $tablicaPanstw = Klienci::ListaPanstwISO(); 
                                    echo Funkcje::RozwijaneMenu('receiver[countryId]', $tablicaPanstw, $domyslnie, 'id="countryId" class="klient" style="width:230px;"' ); 

                                    unset($tablicaPanstw);
                                    ?>
                                </p> 

                                <p>
                                    <label for="contactName">Imię i nazwisko osoby kontaktowej:</label>
                                    <input type="text" size="40" name="receiver[contactName]" id="contactName" value="<?php echo $zamowienie->dostawa['nazwa']; ?>" class="klient" />
                                </p> 

                                <p>
                                    <label for="phone">Numer telefonu:</label>
                                    <?php 
                                    if ( $zamowienie->dostawa['telefon'] != '' ) {
                                        $NumerTelefonu = $zamowienie->dostawa['telefon'];
                                    } else {
                                        $NumerTelefonu = $zamowienie->klient['telefon'];
                                    }
                                    ?>
                                    <input type="text" size="40" name="receiver[phone]" id="phone" value="<?php echo preg_replace( '/[^0-9+]/', '', ( isset($_POST['receiver']['phone']) ? (string)$_POST['receiver']['phone'] : (string)$NumerTelefonu )); ?>" class="klient" />
                                </p> 

                                <p>
                                    <label for="email">Adres e-mail:</label>
                                    <input type="text" size="40" name="receiver[email]" id="email" value="<?php echo $zamowienie->klient['adres_email']; ?>"  class="klient" />
                                </p> 

                            </div>

                </div>

              </div>

              <div class="przyciski_dolne">
                <input id="PrzyciskZatwierdz" type="submit" class="przyciskNon" value="Utwórz przesyłkę" disabled="disabled" />
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
