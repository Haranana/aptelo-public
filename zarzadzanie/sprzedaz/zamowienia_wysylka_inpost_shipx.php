<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $komunikat = '';
    $api = 'InPostShipX';
    $apiKurier = new InPostShipX();

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        // DO USUNIECIA
        if ( $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_SANDBOX'] == 'tak' ) {
            $_POST['target_point'] = 'BBI02A';
            $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_PACZKOMAT'] = 'CZE322';
        }
        // DO USUNIECIA

        $przesylka = array();
        $Parcels = array();

        $KodPocztowy = preg_replace("/[^0-9]/", "", (string)$_POST['postal_code'] );
        $KodPocztowy = substr((string)$KodPocztowy, 0 ,2) .'-'. substr((string)$KodPocztowy, 2 ,3);

        $przesylka['mpk'] = '';
        $przesylka['reference'] = $_POST['reference_number'];

        if ( $_POST['parcel_delivery_typestandard'] != 'AMK' && $_POST['parcel_delivery_typestandard'] != 'KC2' ) {
            $przesylka['receiver'] = array( 'first_name' => $_POST['first_name'],
                                            'last_name' => $_POST['last_name'],
                                            'company_name' => $_POST['company_name'], 
                                            'email' => $_POST['email'], 
                                            'phone' => $_POST['phone']
            ); 
            if ( $_POST['parcel_delivery_typestandard'] != 'P' && $_POST['parcel_delivery_typestandard'] != 'AP' ) {
                $przesylka['receiver']['address'] = array( 'city' => $_POST['city'], 
                                                           'street' => $_POST['street'],
                                                           'building_number' => $_POST['building_number'],
                                                           'post_code' => $KodPocztowy,
                                                           'country_code' => 'PL'
                );
            }
        } elseif ( $_POST['parcel_delivery_typestandard'] == 'AMK' ) {
            $przesylka['receiver'] = array( 'first_name' => $_POST['first_name_letter'],
                                            'last_name' => $_POST['last_name_letter'],
                                            'company_name' => $_POST['company_name_letter'], 
                                            'email' => $_POST['email'], 
                                            'phone' => $_POST['phone']
            ); 

            $przesylka['receiver']['address'] = array( 'city' => $_POST['city_letter'], 
                                                       'street' => $_POST['street_letter'],
                                                       'building_number' => $_POST['building_number_letter'],
                                                       'post_code' => $KodPocztowy,
                                                       'country_code' => 'PL'
            );
        } elseif ( $_POST['parcel_delivery_typestandard'] == 'KC2' ) {
            $przesylka['receiver'] = array( 'first_name' => $_POST['first_name_c2c_kurier'],
                                            'last_name' => $_POST['last_name_c2c_kurier'],
                                            'company_name' => $_POST['company_name_c2c_kurier'], 
                                            'email' => $_POST['email'], 
                                            'phone' => $_POST['phone']
            ); 

            $przesylka['receiver']['address'] = array( 'city' => $_POST['city_c2c_kurier'], 
                                                       'street' => $_POST['street_c2c_kurier'],
                                                       'building_number' => $_POST['building_c2c_kurier'],
                                                       'post_code' => $KodPocztowy,
                                                       'country_code' => 'PL'
            );

        
        }

        if ( isset($_POST['insurance']) && $_POST['insurance'] > 0 ) {
            $przesylka['insurance'] = array( 'amount' => $_POST['insurance'], 'currency' => 'PLN' ); 
        }

        if ( isset($_POST['cod']) && $_POST['cod'] > 0 ) {
            $przesylka['cod'] = array( 'amount' => $_POST['cod'], 'currency' => 'PLN' ); 
        }

        if ( $_POST['parcel_delivery_typestandard'] == 'P' || $_POST['parcel_delivery_typestandard'] == 'AP' ) {

            $przesylka['parcels'] = array(
                array( 'template' => $_POST['parcel_size_paczkomat'], 'is_non_standard' => false )
            );

            $przesylka['custom_attributes']['target_point'] = $_POST['target_point'];
            $przesylka['custom_attributes']['sending_method'] = $_POST['send_options_paczkomat'];

            if ( $_POST['send_options_paczkomat'] == 'parcel_locker' || $_POST['send_options_paczkomat'] == 'any_point' ) {
                $przesylka['custom_attributes']['dropoff_point'] = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_PACZKOMAT'];
            }
            if ( $_POST['parcel_delivery_typestandard'] == 'P' ) {
                if ( isset($_POST['end_of_week_collection']) && $_POST['end_of_week_collection'] == '1' ) {
                    $przesylka['end_of_week_collection'] = true;
                }

                if ( $przesylka['custom_attributes']['dropoff_point'] != $przesylka['custom_attributes']['target_point'] ) {
                    $przesylka['service'] = 'inpost_locker_standard';
                } else {
                    $przesylka['service'] = 'inpost_locker_pass_thru';
                }
            } elseif ( $_POST['parcel_delivery_typestandard'] == 'AP' ) {
                $przesylka['service'] = 'inpost_locker_allegro';
            }

        } elseif ( $_POST['parcel_delivery_typestandard'] == 'K' || $_POST['parcel_delivery_typestandard'] == 'AK' ) {

            $przesylka['parcels'] = array();

            for ( $i = 0, $c = count($_POST['parcel']['dlugosc']); $i < $c; $i++ ) {

                $Parcel['id']                      = $_POST['reference_number'] . '_' . $i;
                $Parcel['dimensions']['length']    = $_POST['parcel']['dlugosc'][$i];
                $Parcel['dimensions']['width']     = $_POST['parcel']['szerokosc'][$i];
                $Parcel['dimensions']['height']    = $_POST['parcel']['wysokosc'][$i];
                $Parcel['weight']['amount']        = $_POST['parcel']['waga'][$i];
                if ( isset($_POST['parcel']['standard'][$i]) && $_POST['parcel']['standard'][$i] == '1' ) {
                    $Parcel['is_non_standard']     = 'true';
                }
                array_push($Parcels, $Parcel);
            }
            $przesylka['parcels'] = $Parcels;

            $przesylka['additional_services'] = array();

            if ( isset($_POST['powiadom_sms']) && $_POST['powiadom_sms'] == '1' ) {
                array_push($przesylka['additional_services'], 'sms');
            }

            if ( isset($_POST['powiadom_email']) && $_POST['powiadom_email'] == '1' ) {
                array_push($przesylka['additional_services'], 'email');
            }
            $przesylka['custom_attributes']['sending_method'] = $_POST['send_options_kurier'];

            if ( $_POST['send_options_kurier'] == 'parcel_locker' || $_POST['send_options_kurier'] == 'any_point' ) {
                $przesylka['custom_attributes']['dropoff_point'] = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_PACZKOMAT'];
            }

            if ( $_POST['parcel_delivery_typestandard'] == 'K' ) {
                $przesylka['service'] = 'inpost_courier_standard';
            } elseif ( $_POST['parcel_delivery_typestandard'] == 'AK' ) {
                $przesylka['service'] = 'inpost_courier_allegro';
            }

        } elseif ( $_POST['parcel_delivery_typestandard'] == 'AMK' ) {

            $przesylka['parcels'] = array(
                array( 'template' => $_POST['parcel_size_letter'], 'is_non_standard' => false )
            );

            $przesylka['custom_attributes']['sending_method'] = $_POST['send_options_letter'];
            $przesylka['service'] = 'inpost_letter_allegro';

        } elseif ( $_POST['parcel_delivery_typestandard'] == 'KC2' ) {

            $przesylka['parcels'] = array(
                array( 'template' => $_POST['parcel_size_c2c_paczkomat'], 'is_non_standard' => false )
            );
            if ( $_POST['send_options_paczkomat'] == 'parcel_locker' || $_POST['send_options_paczkomat'] == 'any_point' ) {
                $przesylka['custom_attributes']['dropoff_point'] = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_PACZKOMAT'];
            }
            $przesylka['custom_attributes']['sending_method'] = $_POST['send_options_c2c_kurier'];
            $przesylka['service'] = 'inpost_courier_c2c';

        }

        $dane_przesylki = $apiKurier->PostRequest('v1/organizations/' . $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_ORGANIZATION_ID'].'/shipments', $przesylka);         
        $komunikat = '';
        $blad = '';
        if ( isset($dane_przesylki->error) ) {
            $komunikat = $dane_przesylki->message . '<br /><br />';
            foreach ( $dane_przesylki->details as $pole => $bledy ) {
                $blad .= $pole . ' - ';
                foreach( $bledy as $tablica) {
                    if ( is_array($tablica) && count($tablica) > 0 ) {
                        foreach ( $tablica as $key => $value ) {
                            $blad .= $key . ' : ';
                            if ( is_array($value) && count($value) > 0 ) {
                                foreach ( $value as $key1 => $value1 ) {
                                    if ( is_string($value1) ) {
                                        $blad .= $value1;
                                    }
                                }
                            } elseif ( is_object($value) ) {
                                Funkcje::object2array($value);
                                foreach ( $value as $key1 => $value1 ) {
                                    if ( is_string($value1) ) {
                                        $blad .= $value1;
                                    }
                                }
                            } else {
                                $blad .= $value;
                            }
                            $blad .='<br />';
                        }
                    } else {
                            foreach ( $tablica as $key => $value ) {
                                $blad .= $key . ' : ';
                                if ( is_array($value) && count($value) > 0 ) {
                                    foreach ( $value as $key1 => $value1 ) {
                                        if ( is_string($value1) ) {
                                            $blad .= $value1;
                                        }
                                    }
                                } elseif ( is_object($tablica) ) {
                                    if ( is_string($value) ) {
                                        $blad .= $value;
                                    }
                                } else {
                                    $blad .= $tablica;
                                }
                                $blad .='<br />';
                            }
                    }
                } 
            }

            $komunikat .= $blad;
        }

        if ( isset($dane_przesylki) && !isset($dane_przesylki->error) ) {

            $Service = 'Paczkomaty';
            if ( $_POST['parcel_delivery_typestandard'] == 'P' ) {
                $Service = 'Paczkomaty';
            } elseif ( $_POST['parcel_delivery_typestandard'] == 'K' || $_POST['parcel_delivery_typestandard'] == 'KC2' ) {
                $Service = 'Kurier';
            } elseif ( $_POST['parcel_delivery_typestandard'] == 'AP' ) {
                $Service = 'Allegro Paczkomaty';
            } elseif ( $_POST['parcel_delivery_typestandard'] == 'AK' ) {
                $Service = 'Allegro Kurier24';
            } elseif ( $_POST['parcel_delivery_typestandard'] == 'AMK' ) {
                $Service = 'Allegro MiniKurier24';
            }

            $pola = array(
                    array('orders_id',(int)$_POST["id"]),
                    array('orders_shipping_comments', $Service),
                    array('orders_shipping_type','INPOST'),
                    array('orders_shipping_number', $dane_przesylki->id),
                    array('orders_shipping_weight','0'),
                    array('orders_parcels_quantity', count($dane_przesylki->parcels)),
                    array('orders_shipping_status', 'created'),
                    array('orders_shipping_date_created', date('Y-m-d G:i:s', FunkcjeWlasnePHP::my_strtotime($dane_przesylki->created_at))),
                    array('orders_shipping_date_modified', date('Y-m-d G:i:s', FunkcjeWlasnePHP::my_strtotime($dane_przesylki->updated_at))),
                    array('orders_shipping_protocol', $dane_przesylki->id),
                    array('orders_shipping_packages', ( $_POST['parcel_delivery_typestandard'] == 'P' || $_POST['parcel_delivery_typestandard'] == 'AP' ? $_POST['send_options_paczkomat'] : $_POST['send_options_kurier']) ),
                    array('orders_shipping_misc', 'SHIPX')
            );

            $db->insert_query('orders_shipping' , $pola);
            unset($pola);
            $id_dodanej_pozycji = $db->last_id_query();

            sleep(5);

            $wynikInPost = $apiKurier->GetRequest('v1/organizations', $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_ORGANIZATION_ID'].'/shipments?id='.$dane_przesylki->id);
            if ( isset($wynikInPost) && $wynikInPost->count ) {
                foreach ( $wynikInPost->items as $Przesylka ) {

                    if ( $Przesylka->tracking_number != '' ) {

                        $pola = array();
                        $pola = array(
                                array('orders_shipping_number',$Przesylka->tracking_number),
                                array('orders_shipping_status',$Przesylka->status),
                                array('orders_shipping_date_modified',date('Y-m-d G:i:s', FunkcjeWlasnePHP::my_strtotime($Przesylka->updated_at)))
                        );

                        $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".(int)$id_dodanej_pozycji."'");
                        unset($pola);
                    }

               }
            }

            Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.(int)$_POST["zakladka"]);

        }

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');

    if ( isset($komunikat) && $komunikat != '' ) {
      echo Okienka::pokazOkno('Błąd podczas tworzenia przesyłki', $komunikat);
    }
    ?>

    <div id="naglowek_cont">Tworzenie wysyłki</div>
    <div id="cont">
    

      <div class="poleForm">
        <div class="naglowek">
            Wysyłka za pośrednictwem firmy Inpost - zamówienie numer : <?php echo $_GET['id_poz']; ?>
            <?php
            if ( $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_SANDBOX'] == 'tak' ) {
                echo ' - TRYB TESTOWY';
            }
            ?>
        </div>

        <?php

        $zamowienie     = new Zamowienie((int)$_GET['id_poz']);
        $waga_produktow = $zamowienie->waga_produktow;
        $transaction_id = '';

        if ( $zamowienie->info['zrodlo'] == '3' ) {
            $zapytanie_allegro = "SELECT transaction_id FROM allegro_transactions WHERE orders_id = '".(int)$zamowienie->info['id_zamowienia']."'";
            $sql_allegro = $db->open_query($zapytanie_allegro);    
            
            if ( (int)$db->ile_rekordow($sql_allegro) > 0) {
              
                while ( $info_allegro = $sql_allegro->fetch_assoc() ) {
                  $transaction_id = $info_allegro['transaction_id'];
                }
                unset($info_allegro);
            }
            
            $db->close_query($sql_allegro);
            unset($zapytanie_allegro);    
        }

        if ( $zamowienie->dostawa['telefon'] != '' ) {
            $NumerTelefonu = $zamowienie->dostawa['telefon'];
        } else {
            $NumerTelefonu = $zamowienie->klient['telefon'];
        }
        $NumerTelefonu = preg_replace("/\([0-9]+?\)/", "", (string)$NumerTelefonu);
        $NumerTelefonu = preg_replace("/[^0-9]/", "", (string)$NumerTelefonu);
        $NumerTelefonu = substr((string)$NumerTelefonu , -9);

        $AdresOK = true;
        $adres_klienta  = Funkcje::PrzeksztalcAdres($zamowienie->dostawa['ulica']);
        $adres_dom_lokal = Funkcje::PrzeksztalcAdresDomu($adres_klienta['dom']);

        $PrzeksztalconyAdres = implode(' ', $adres_klienta); 
        if ( $PrzeksztalconyAdres != $zamowienie->dostawa['ulica'] ) {
            $AdresOK = false;
        }

        $imie_nazwisko_klienta = preg_replace('!\s+!', ' ', (string)$zamowienie->dostawa['nazwa']);
        $klient = explode(' ', (string)$imie_nazwisko_klienta);

        $wymiary        = array();
        $wymiary['0'] = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_DLUGOSC'];
        $wymiary['1'] = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_SZEROKOSC'];
        $wymiary['2'] = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_WYSOKOSC'];

        ?>

        <div class="pozycja_edytowana">  

            <?php
            $tablica_wysylek = $apiKurier->TypyPrzesylek(false);

            $tekst = '<select style="width:100px;" name="parcel[typ][]" class="valid">';
            foreach ( $tablica_wysylek as $produkt ) {
              $tekst .= '<option value="'.$produkt['id'].'">'.$produkt['text'].'</option>';
            }
            $tekst .= '</select>';
            ?>

            <script async src="https://geowidget.easypack24.net/js/sdk-for-javascript.js"></script>
            <link rel="stylesheet" href="https://geowidget.easypack24.net/css/easypack.css"/>

            <script type="text/javascript">
                window.easyPackAsyncInit = function () {
                    easyPack.init({
                        defaultLocale: 'pl',
                        mapType: 'osm',
                        searchType: 'osm',
                        points: {
                            types: ['parcel_locker'],
                            functions: ['parcel_collect']
                        },
                        map: {
                            initialTypes: ['parcel_locker']
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

                                $("#PaczkomatWybrany").val(point.address["line1"] + ", " + point.address["line2"]);
                                $("#target_point").val(point.name);

                                }, {width: szerokosc, height: wysokosc });
                                map1.searchPlace("<?php echo $zamowienie->dostawa['miasto']; ?>");
                        }
                    }

                };
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

                  $("#addrow").click(function() {
                    
                    var id = $(".UsunPozycjeListy").length;

                    $(".item-row:last").after('<tr class="item-row"><td style="text-align:center"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)" style="margin-right:8px; text-align:right;margin-top:10px;"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td><td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="" size="8" name="parcel[dlugosc][]" class="kropkaPustaZero required" /></td><td class="Paczka"><input type="text" value="" size="8" name="parcel[szerokosc][]" class="kropkaPustaZero required" /></td><td class="Paczka"><input type="text" value="" size="8" name="parcel[wysokosc][]" class="kropkaPustaZero required" /></td><td class="Paczka"><input type="text" value="" size="4" name="parcel[waga][]" class="kropkaPusta required" /></td><td class="Paczka"><input type="checkbox" value="1" name="parcel[standard][]" id="standard_'+id+'" /><label class="OpisForPustyLabel" for="standard_'+id+'"></label></td></tr>');

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
                    phone: { required: true }, 
                    target_point: { required: '#parcel_delivery_typestandard_parcel[value="P"]:checked' }, 
                    ubezpieczenie: { digits: true, },
                    szerokosc    : { required: true },
                    dlugosc      : { required: true },
                    wysokosc     : { required: true },
                    zawartosc    : { required: true },
                    waga         : { digits: true }
                  }
                });

                  $('#pobranie').change(function() {
                      (($(this).is(':checked')) ? $("#inpost_pobranie").prop('disabled', false) : $("#inpost_pobranie").prop('disabled', true));
                      $("#inpost_pobranie").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
                  });

                  $('input[type=radio][name=parcel_delivery_typestandard]').change(function() {
                    var value = $(this).val();
                    if ( value == 'P' ) {
                        $('input[type=radio][name=parcel_size_kurier]').prop('checked', false);
                        $('input[type=radio][name=parcel_size_paczkomat]').prop('checked', false);
                        $('input[type=radio][name=parcel_size_letter]').prop('checked', false);
                        $('input[type=radio][name=parcel_size_c2c_paczkomat]').prop('checked', false);
                        $('#PrzyciskZatwierdz').attr('disabled','disabled');
                        $("#formularzPobranie").show();
                        $("#formularzKurier").slideUp();
                        $("#formularzMini").slideUp();
                        $("#formularzKurierC2").slideUp();
                        $("#formularzPaczkomat").slideDown();
                    } else if ( value == 'AP' ) {
                        $('input[type=radio][name=parcel_size_kurier]').prop('checked', false);
                        $('input[type=radio][name=parcel_size_paczkomat]').prop('checked', false);
                        $('input[type=radio][name=parcel_size_letter]').prop('checked', false);
                        $('input[type=radio][name=parcel_size_c2c_paczkomat]').prop('checked', false);
                        $('#PrzyciskZatwierdz').attr('disabled','disabled');
                        $("#formularzPobranie").show();
                        $("#formularzKurier").slideUp();
                        $("#formularzMini").slideUp();
                        $("#formularzKurierC2").slideUp();
                        $("#formularzPaczkomat").slideDown();
                    } else if ( value == 'AMK' ) {
                        $('input[type=radio][name=parcel_size_kurier]').prop('checked', false);
                        $('input[type=radio][name=parcel_size_paczkomat]').prop('checked', false);
                        $('input[type=radio][name=parcel_size_letter]').prop('checked', false);
                        $('input[type=radio][name=parcel_size_c2c_paczkomat]').prop('checked', false);
                        $('#PrzyciskZatwierdz').attr('disabled','disabled');
                        $("#formularzPobranie").hide();
                        $("#formularzKurier").slideUp();
                        $("#formularzPaczkomat").slideUp();
                        $("#formularzKurierC2").slideUp();
                        $("#formularzMini").slideDown();
                    } else if ( value == 'KC2' ) {
                        $('input[type=radio][name=parcel_size_kurier]').prop('checked', false);
                        $('input[type=radio][name=parcel_size_paczkomat]').prop('checked', false);
                        $('input[type=radio][name=parcel_size_letter]').prop('checked', false);
                        $('#PrzyciskZatwierdz').attr('disabled','disabled');
                        $("#formularzPobranie").show();
                        $("#formularzKurier").slideUp();
                        $("#formularzPaczkomat").slideUp();
                        $("#formularzMini").slideUp();
                        $("#formularzKurierC2").slideDown();
                    } else if ( value == 'K' ) {
                        $('input[type=radio][name=parcel_size_paczkomat]').prop('checked', false);
                        $('input[type=radio][name=parcel_size_letter]').prop('checked', false);
                        $('input[type=radio][name=parcel_size_c2c_paczkomat]').prop('checked', false);
                        $('#PrzyciskZatwierdz').removeAttr('disabled');
                        $("#formularzPobranie").show();
                        $("#formularzPaczkomat").slideUp();
                        $("#formularzMini").slideUp();
                        $("#formularzKurier").slideDown();
                        $("#formularzKurierC2").slideUp();
                        $("#powiadom_sms").removeAttr('disabled');
                        $("#powiadom_email").removeAttr('disabled');
                    } else if ( value == 'AK' ) {
                        $('input[type=radio][name=parcel_size_paczkomat]').prop('checked', false);
                        $('input[type=radio][name=parcel_size_letter]').prop('checked', false);
                        $('input[type=radio][name=parcel_size_c2c_paczkomat]').prop('checked', false);
                        $('#PrzyciskZatwierdz').removeAttr('disabled');
                        $("#formularzPobranie").show();
                        $("#formularzPaczkomat").slideUp();
                        $("#formularzMini").slideUp();
                        $("#formularzKurier").slideDown();
                        $("#formularzKurierC2").slideUp();
                        $("#powiadom_sms").attr('disabled','disabled');
                        $("#powiadom_email").attr('disabled','disabled');

                    }
                  });

                  $('input[type=radio][name=parcel_size_paczkomat]').change(function() {
                    $('#PrzyciskZatwierdz').removeAttr('disabled');
                  });
                  $('input[type=radio][name=parcel_size_letter]').change(function() {
                    $('#PrzyciskZatwierdz').removeAttr('disabled');
                  });
                  $('input[type=radio][name=parcel_size_kurier]').change(function() {
                    $('#PrzyciskZatwierdz').removeAttr('disabled');
                  });
                  $('input[type=radio][name=parcel_size_c2c_paczkomat]').change(function() {
                    $('#PrzyciskZatwierdz').removeAttr('disabled');
                  });

                  if ($("input[type=radio][name=parcel_delivery_typestandard]:checked").val()) {
                    $('#PrzyciskZatwierdz').removeAttr('disabled');
                  }
                  if ($("input[type=radio][name=parcel_size_paczkomat]:checked").val()) {
                    $('#PrzyciskZatwierdz').removeAttr('disabled');
                  }
                  if ($("input[type=radio][name=parcel_size_letter]:checked").val()) {
                    $('#PrzyciskZatwierdz').removeAttr('disabled');
                  }
                  if ($("input[type=radio][name=parcel_size_c2c_paczkomat]:checked").val()) {
                    $('#PrzyciskZatwierdz').removeAttr('disabled');
                  }

                });

            </script>

            <form action="sprzedaz/zamowienia_wysylka_inpost_shipx.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="apiForm" class="cmxform">

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
                            <label for="email">Adres e-mail odbiorcy:</label>
                            <input type="text" size="46" name="email" id="email" value="<?php echo ( isset($_POST['email']) ? $_POST['email'] : $zamowienie->klient['adres_email'] ); ?>" />
                        </p> 

                        <p>
                            <label for="phone">Numer telefonu odbiorcy:</label>
                            <input type="text" size="46" name="phone" id="phone" value="<?php echo ( isset($_POST['phone']) ? $_POST['phone'] : $NumerTelefonu ); ?>" />
                        </p> 

                        <p style="border-bottom:1px dashed #ccc; margin-bottom:10px; padding-bottom:10px;">
                            <label>Rodzaj przesyłki:</label>
                            <input type="radio" style="border:0px" name="parcel_delivery_typestandard" value="P" id="parcel_delivery_typestandard_parcel" <?php echo ( isset($_POST['parcel_delivery_typestandard']) && $_POST['parcel_delivery_typestandard'] == 'P' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="parcel_delivery_typestandard_parcel" >InPost Paczkomaty</label>
                            <span class="Radio"><input type="radio" style="border:0px" name="parcel_delivery_typestandard" value="K" id="parcel_delivery_typecourier_parcel" <?php echo ( isset($_POST['parcel_delivery_typestandard']) && $_POST['parcel_delivery_typestandard'] == 'K' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="parcel_delivery_typecourier_parcel">InPost Kurier Standard (klient biznesowy)</label></span>
                            <span class="Radio"><input type="radio" style="border:0px" name="parcel_delivery_typestandard" value="KC2" id="parcel_delivery_typecourier_c2c_parcel" <?php echo ( isset($_POST['parcel_delivery_typestandard']) && $_POST['parcel_delivery_typestandard'] == 'KC2' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="parcel_delivery_typecourier_c2c_parcel">InPost Kurier C2C</label></span>
                            <span class="Radio"><input type="radio" style="border:0px" name="parcel_delivery_typestandard" value="AP" id="parcel_delivery_typeallegro_parcel" <?php echo ( isset($_POST['parcel_delivery_typestandard']) && $_POST['parcel_delivery_typestandard'] == 'AP' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="parcel_delivery_typeallegro_parcel">Allegro Paczkomaty InPost</label></span>
                            <span class="Radio"><input type="radio" style="border:0px" name="parcel_delivery_typestandard" value="AMK" id="parcel_delivery_typeletterallegro_parcel" <?php echo ( isset($_POST['parcel_delivery_typestandard']) && $_POST['parcel_delivery_typestandard'] == 'AMK' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="parcel_delivery_typeletterallegro_parcel">Allegro MiniKurier24 InPost</label></span>
                            <span class="Radio"><input type="radio" style="border:0px" name="parcel_delivery_typestandard" value="AK" id="parcel_delivery_typecourierallegro_parcel" <?php echo ( isset($_POST['parcel_delivery_typestandard']) && $_POST['parcel_delivery_typestandard'] == 'AK' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="parcel_delivery_typecourierallegro_parcel">Allegro Kurier24 InPost</label></span>


                        </p> 

                        <div id="formularzPaczkomat" <?php echo ( isset($_POST['parcel_delivery_typestandard']) && ( $_POST['parcel_delivery_typestandard'] == 'P' || $_POST['parcel_delivery_typestandard'] == 'AP' ) ? '' : 'style="display:none;"' ); ?>>

                            <p>
                                <label>Rozmiar:</label>
                                <input type="radio" style="border:0px" name="parcel_size_paczkomat" id="parcel_size_A" value="small" <?php echo ( isset($_POST['parcel_size_paczkomat']) && $_POST['parcel_size_paczkomat'] == 'small' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="parcel_size_A">Gabaryt A</label><em class="TipIkona"><b>8 x 38 x 64 cm</b></em>
                                <input type="radio" style="border:0px" name="parcel_size_paczkomat" id="parcel_size_B" value="medium" <?php echo ( isset($_POST['parcel_size_paczkomat']) && $_POST['parcel_size_paczkomat'] == 'medium' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="parcel_size_B">Gabaryt B</label><em class="TipIkona"><b>19 x 38 x 64 cm</b></em>
                                <input type="radio" style="border:0px" name="parcel_size_paczkomat" id="parcel_size_C" value="large" <?php echo ( isset($_POST['parcel_size_paczkomat']) && $_POST['parcel_size_paczkomat'] == 'large' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="parcel_size_C">Gabaryt C</label><em class="TipIkona"><b>41 x 38 x 64 cm</b></em>
                            </p> 

                            <p>
                                <label for="target_point" class="required" >Do Paczkomatu InPost:</label>
                                <?php 
                                echo '<input type="text" class="przyciskPaczkomatu" id="WidgetButton" value="Wybierz paczkomat" readonly="readonly" />';

                                echo '<input type="text" size="40" id="PaczkomatWybrany" value="'.$zamowienie->info['wysylka_info'].'" name="lokalizacjaPaczkomat" readonly="readonly" id="wybor_paczkomatu" />';

                                echo '<input type="text" id="target_point" value="'.$zamowienie->info['wysylka_punkt_odbioru'].'" name="target_point" readonly="readonly"  style="margin-left:10px;" size="15" />';

                                ?>
                            </p>

                            <p>
                              <label for="send_options_paczkomat">Sposób nadania:</label>
                                <?php
                                $tablicaNadaniaPaczkomat = $apiKurier->SposobNadania('inpost_locker_standard');
                                $domyslnie = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_NADANIE'];
                                if ( isset($_POST['send_options_paczkomat']) ) {
                                    $domyslnie = $_POST['send_options_paczkomat'];
                                }
                                echo Funkcje::RozwijaneMenu('send_options_paczkomat', $tablicaNadaniaPaczkomat, $domyslnie, 'id="send_options_paczkomat" style="width:450px;"' ); 
                                unset($tablicaNadaniaPaczkomat, $domyslnie);

                                ?>
                            </p>

                            <p>
                              <label for="end_of_week_collection">Paczka w Weekend:</label>
                              <input type="checkbox" value="1" name="end_of_week_collection" id="end_of_week_collection" style="margin-right:20px;" <?php echo ( isset($_POST['end_of_week_collection']) ? 'checked="checked"' : '' ); ?> /><label class="OpisForPustyLabel" for="end_of_week_collection" style="margin-right:10px;"></label><em class="TipIkona"><b>dostępna w określonym oknie czasowym, od czwartku od godziny 20:00 do soboty do godziny 13:00</b></em>
                            </p>

                        </div>

                        <div id="formularzKurier" <?php echo ( isset($_POST['parcel_delivery_typestandard']) && ( $_POST['parcel_delivery_typestandard'] == 'K' || $_POST['parcel_delivery_typestandard'] == 'AK' ) ? '' : 'style="display:none;"' ); ?> >

                            <div class="naglowek">Informacje o paczkach</div>

                            <table class="listing_tbl" style="border-bottom:1px dashed #cccccc; margin-bottom:15px; ">
                                <tr>
                                    <td style="width:50px"></td>
                                    <td class="Paczka" style="padding-top:8px;">Długość [mm]</td>
                                    <td class="Paczka">Szerokość [mm]</td>
                                    <td class="Paczka">Wysokość [mm]</td>
                                    <td class="Paczka">Waga [kg]</td>
                                    <td class="Paczka">Wym. niest.</td>
                                </tr>

                                <tr class="item-row">
                                    <td style="text-align:right"><div class="UsunKontener" style="display:none;"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td>
                                    <td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="<?php echo ( isset($_POST['parcel']['dlugosc']['0']) ? $_POST['parcel']['dlugosc']['0'] : $wymiary['0'] ); ?>" size="8" name="parcel[dlugosc][]" class="kropkaPustaZero required" /></td>
                                    <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['szerokosc']['0']) ? $_POST['parcel']['szerokosc']['0'] : $wymiary['1'] ); ?>" size="8" name="parcel[szerokosc][]" class="kropkaPustaZero required" /></td>
                                    <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['wysokosc']['0']) ? $_POST['parcel']['wysokosc']['0'] : $wymiary['2'] ); ?>" size="8" name="parcel[wysokosc][]" class="kropkaPustaZero required" /></td>
                                    <td class="Paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['waga']['0']) ? $_POST['parcel']['waga']['0'] : ceil($waga_produktow) ); ?>" size="4" name="parcel[waga][]" class="kropkaPustaZero required" /></td>
                                    <td class="Paczka">
                                    <input type="checkbox" value="1" name="parcel[standard][]" id="standard_0" /><label class="OpisForPustyLabel" for="standard_0"></label>
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
                                    <td colspan="10" style="padding-left:25px;padding-top:10px;padding-bottom:10px;"><a id="addrow" href="javascript:void(0)" class="dodaj">dodaj paczkę</a></td>
                                </tr>
                        
                            </table>

                            <p>
                              <label for="powiadom_email">Powiadomienie e-mail:</label>
                              <input id="powiadom_email" value="1" type="checkbox" name="powiadom_email" style="margin-right:20px;" <?php echo ( isset($_POST['powiadom_email']) ? 'checked="checked"' : ( strpos((string)$apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_POWIADOMIENIE'], 'E') !== false ? 'checked="checked"' : '' ) ); ?>><label class="OpisForPustyLabel" style="margin-right:10px;" for="powiadom_email"></label><em class="TipIkona"><b>Powiadomienie o przesyłce via e-mail</b></em>
                            </p> 

                            <?php if ( Klienci::CzyNumerGSM($apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_TELEFON']) ) { ?>
                                <p>
                                  <label for="powiadom_sms">Powiadomienie SMS:</label>
                                  <input id="powiadom_sms" value="1" type="checkbox" name="powiadom_sms" style="margin-right:20px;" <?php echo ( isset($_POST['powiadom_sms']) ? 'checked="checked"' : ( strpos((string)$apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_POWIADOMIENIE'], 'S') !== false ? 'checked="checked"' : '' ) ); ?>><label class="OpisForPustyLabel" style="margin-right:10px;" for="powiadom_sms"></label><em class="TipIkona"><b>Powiadomienie o przesyłce via SMS</b></em>
                                </p>
                            <?php } ?>

                            <p>
                                <label for="company_name">Nazwa firmy:</label>
                                <input type="text" size="40" name="company_name" id="company_name" value="<?php echo ( $zamowienie->dostawa['firma'] != '' ? Funkcje::formatujTekstInput($zamowienie->dostawa['firma']) : ''); ?>" />
                            </p> 
                            <p>
                                <label for="first_name">Nazwisko:</label>
                                <input type="text" size="40" name="first_name" id="first_name" value="<?php echo $klient['1']; ?>" />
                            </p> 
                            <p>
                                <label for="first_name">Imię:</label>
                                <input type="text" size="40" name="last_name" id="first_name" value="<?php echo $klient['0']; ?>" />
                            </p> 
                            <p>
                                <label for="street">Ulica:</label>
                                <input type="text" size="40" name="street" id="street" value="<?php echo $adres_klienta['ulica']; ?>" />
                            </p>
                            <p>
                                <label for="building_number">Numer domu:</label>
                                <input type="text" size="40" name="building_number" id="building_number" value="<?php echo $adres_klienta['dom']; ?>" />
                            </p> 
                            <p>
                                <label for="postal_code">Kod pocztowy:</label>
                                <input type="text" size="40" name="postal_code" id="postal_code" value="<?php echo $zamowienie->dostawa['kod_pocztowy']; ?>" />
                            </p> 
                            <p>
                                <label for="city">Miejscowość:</label>
                                <input type="text" size="40" name="city" id="city" value="<?php echo $zamowienie->dostawa['miasto']; ?>" />
                            </p> 
                        
                            <p>
                              <label for="send_options_kurier">Sposób nadania:</label>
                                <?php

                                $tablicaNadaniaKurier = $apiKurier->SposobNadania('inpost_courier_standard');
                                $domyslnie = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_NADANIE'];
                                if ( isset($_POST['send_options_kurier']) ) {
                                    $domyslnie = $_POST['send_options_kurier'];
                                }
                                echo Funkcje::RozwijaneMenu('send_options_kurier', $tablicaNadaniaKurier, $domyslnie, 'id="send_options_kurier" style="width:450px;"' ); 
                                unset($tablicaNadaniaKurier, $domyslnie);

                                ?>
                            </p>

                        </div>

                        <div id="formularzMini" <?php echo ( isset($_POST['parcel_delivery_typestandard']) && ( $_POST['parcel_delivery_typestandard'] == 'AMK' ) ? '' : 'style="display:none;"' ); ?>>

                            <p>
                                <label>Rozmiar:</label>
                                <input type="radio" style="border:0px" name="parcel_size_letter" id="letter_size_A" value="letter_a" <?php echo ( isset($_POST['parcel_size_letter']) && $_POST['parcel_size_letter'] == 'letter_a' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="letter_size_A">Gabaryt A</label><em class="TipIkona"><b>8 x 38 x 64 cm</b></em>
                                <input type="radio" style="border:0px" name="parcel_size_letter" id="letter_size_B" value="letter_b" <?php echo ( isset($_POST['parcel_size_letter']) && $_POST['parcel_size_letter'] == 'letter_b' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="letter_size_B">Gabaryt B</label><em class="TipIkona"><b>19 x 38 x 64 cm</b></em>
                                <input type="radio" style="border:0px" name="parcel_size_letter" id="letter_size_C" value="letter_c" <?php echo ( isset($_POST['parcel_size_letter']) && $_POST['parcel_size_letter'] == 'letter_c' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="letter_size_C">Gabaryt C</label><em class="TipIkona"><b>suma wymiarów: 160 cm</b></em>
                            </p> 

                            <p>
                                <label for="company_name_letter">Nazwa firmy:</label>
                                <input type="text" size="40" name="company_name_letter" id="company_name_letter" value="<?php echo ( $zamowienie->dostawa['firma'] != '' ? Funkcje::formatujTekstInput($zamowienie->dostawa['firma']) : ''); ?>" />
                            </p> 
                            <p>
                                <label for="first_name_letter">Nazwisko:</label>
                                <input type="text" size="40" name="first_name_letter" id="first_name_letter" value="<?php echo $klient['1']; ?>" />
                            </p> 
                            <p>
                                <label for="first_name_letter">Imię:</label>
                                <input type="text" size="40" name="last_name_letter" id="last_name_letter" value="<?php echo $klient['0']; ?>" />
                            </p> 
                            <p>
                                <label for="street_letter">Ulica:</label>
                                <input type="text" size="40" name="street_letter" id="street_letter" value="<?php echo $adres_klienta['ulica']; ?>" />
                            </p>
                            <p>
                                <label for="building_number_letter">Numer domu:</label>
                                <input type="text" size="40" name="building_number_letter" id="building_number_letter" value="<?php echo $adres_klienta['dom']; ?>" />
                            </p> 
                            <p>
                                <label for="postal_code_letter">Kod pocztowy:</label>
                                <input type="text" size="40" name="postal_code_letter" id="postal_code_letter" value="<?php echo $zamowienie->dostawa['kod_pocztowy']; ?>" />
                            </p> 
                            <p>
                                <label for="city">Miejscowość:</label>
                                <input type="text" size="40" name="city_letter" id="city_letter" value="<?php echo $zamowienie->dostawa['miasto']; ?>" />
                            </p> 
                        
                            <p>
                              <label for="send_options_letter">Sposób nadania:</label>
                                <?php
                                $tablicaNadaniaPaczkomat = $apiKurier->SposobNadania('inpost_letter_allegro');
                                $domyslnie = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_NADANIE'];
                                if ( isset($_POST['send_options_letter']) ) {
                                    $domyslnie = $_POST['send_options_letter'];
                                }
                                echo Funkcje::RozwijaneMenu('send_options_letter', $tablicaNadaniaPaczkomat, $domyslnie, 'id="send_options_letter" style="width:450px;"' ); 
                                unset($tablicaNadaniaPaczkomat, $domyslnie);

                                ?>
                            </p>
                        </div>

                        <div id="formularzKurierC2" <?php echo ( isset($_POST['parcel_delivery_typestandard']) && ( $_POST['parcel_delivery_typestandard'] == 'KC2' ) ? '' : 'style="display:none;"' ); ?>>

                            <p>
                                <label>Rozmiar:</label>
                                <input type="radio" style="border:0px" name="parcel_size_c2c_paczkomat" id="letter_size_c2c_A" value="small" <?php echo ( isset($_POST['parcel_size_c2c_paczkomat']) && $_POST['parcel_size_c2c_paczkomat'] == 'small' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="letter_size_c2c_A">Gabaryt A</label><em class="TipIkona"><b>8 x 38 x 64 cm</b></em>
                                <input type="radio" style="border:0px" name="parcel_size_c2c_paczkomat" id="letter_size_c2c_B" value="medium" <?php echo ( isset($_POST['parcel_size_c2c_paczkomat']) && $_POST['parcel_size_c2c_paczkomat'] == 'medium' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="letter_size_c2c_B">Gabaryt B</label><em class="TipIkona"><b>19 x 38 x 64 cm</b></em>
                                <input type="radio" style="border:0px" name="parcel_size_c2c_paczkomat" id="letter_size_c2c_C" value="large" <?php echo ( isset($_POST['parcel_size_c2c_paczkomat']) && $_POST['parcel_size_c2c_paczkomat'] == 'large' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="letter_size_c2c_C">Gabaryt C</label><em class="TipIkona"><b>41 x 38 x 64</b></em>
                                <input type="radio" style="border:0px" name="parcel_size_c2c_paczkomat" id="letter_size_c2c_D" value="xlarge" <?php echo ( isset($_POST['parcel_size_c2c_paczkomat']) && $_POST['parcel_size_c2c_paczkomat'] == 'xlarge' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="letter_size_c2c_D">Gabaryt D</label><em class="TipIkona"><b>50 x 50 x 80 cm</b></em>
                            </p> 

                            <p>
                                <label for="company_name_c2c_kurier">Nazwa firmy:</label>
                                <input type="text" size="40" name="company_name_c2c_kurier" id="company_name_c2c_kurier" value="<?php echo ( $zamowienie->dostawa['firma'] != '' ? Funkcje::formatujTekstInput($zamowienie->dostawa['firma']) : ''); ?>" />
                            </p> 
                            <p>
                                <label for="first_name_c2c_kurier">Nazwisko:</label>
                                <input type="text" size="40" name="first_name_c2c_kurier" id="first_name_c2c_kurier" value="<?php echo $klient['1']; ?>" />
                            </p> 
                            <p>
                                <label for="first_name_c2c_kurier">Imię:</label>
                                <input type="text" size="40" name="last_name_c2c_kurier" id="last_name_c2c_kurier" value="<?php echo $klient['0']; ?>" />
                            </p> 
                            <p>
                                <label for="street_c2c_kurier">Ulica:</label>
                                <input type="text" size="40" name="street_c2c_kurier" id="street_c2c_kurier" value="<?php echo $adres_klienta['ulica']; ?>" />
                            </p>
                            <p>
                                <label for="building_c2c_kurier">Numer domu:</label>
                                <input type="text" size="40" name="building_c2c_kurier" id="building_c2c_kurier" value="<?php echo $adres_klienta['dom']; ?>" />
                            </p> 
                            <p>
                                <label for="postal_code_c2c_kurier">Kod pocztowy:</label>
                                <input type="text" size="40" name="postal_code_c2c_kurier" id="postal_code_c2c_kurier" value="<?php echo $zamowienie->dostawa['kod_pocztowy']; ?>" />
                            </p> 
                            <p>
                                <label for="city_c2c_kurier">Miejscowość:</label>
                                <input type="text" size="40" name="city_c2c_kurier" id="city_c2c_kurier" value="<?php echo $zamowienie->dostawa['miasto']; ?>" />
                            </p> 
                        
                            <p>
                              <label for="send_options_c2c_kurier">Sposób nadania:</label>
                                <?php
                                $tablicaNadaniaPaczkomat = $apiKurier->SposobNadania('inpost_courier_c2c');
                                $domyslnie = $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_NADANIE'];
                                if ( isset($_POST['send_options_c2c_kurier']) ) {
                                    $domyslnie = $_POST['send_options_c2c_kurier'];
                                }
                                echo Funkcje::RozwijaneMenu('send_options_c2c_kurier', $tablicaNadaniaPaczkomat, $domyslnie, 'id="send_options_c2c_kurier" style="width:450px;"' ); 
                                unset($tablicaNadaniaPaczkomat, $domyslnie);

                                ?>
                            </p>
                        </div>

                        <p id="formularzPobranie">
                            <label for="pobranie">Pobranie:</label>
                                <?php if ( strpos((string)$zamowienie->info['metoda_platnosci'], 'pobranie') === false && strpos((string)$zamowienie->info['metoda_platnosci'], 'odbiorze') === false) { ?>
                                    <input type="checkbox" value="1" name="pobranie" id="pobranie" style="margin-right:20px;" <?php echo ( isset($_POST['pobranie']) ? 'checked="checked"' : '' ); ?> /><label class="OpisForPustyLabel" for="pobranie" style="margin-right:10px;"></label>
                                    <input class="kropkaPustaZero" type="text" size="20" name="cod" id="inpost_pobranie" value="<?php echo ( isset($_POST['cod']) ? $_POST['cod'] : '' ); ?>"  disabled="disabled" />
                                <?php } else { ?>
                                    <input type="checkbox" value="1" name="pobranie" id="pobranie" style="margin-right:20px;" checked="checked" /><label class="OpisForPustyLabel" for="pobranie" style="margin-right:10px;"></label>
                                    <input class="kropkaPustaZero" type="text" size="20" name="cod" id="inpost_pobranie" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" /><em class="TipIkona"><b>Jeżeli chcesz utworzyć przesyłkę za pobraniem wpisz kwotę</b></em>
                                <?php } ?>
                        </p> 

                        <p id="formularzUbezpieczenie">
                            <label for="insurance">Ubezpieczenie:</label>
                            <input type="text" size="46" name="insurance" id="insurance" value="<?php echo ( isset($_POST['insurance']) ? $_POST['insurance'] : '' ); ?>" /><em class="TipIkona"><b>Minimum 1 mniej niż 10000000</b></em>
                        </p>

                        <p>
                            <label for="reference_number">Numer referencyjny:</label>
                            <input type="text" size="46" name="reference_number" id="reference_number" value="<?php echo ( isset($_POST['reference_number']) ? $_POST['reference_number'] : $_GET['id_poz'] ); ?>" /><em class="TipIkona"><b>Wpisz dowolną wartość, która pozwoli zidentyfikować przesyłkę</b></em>
                        </p>

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
                                        <input type="text" name="punkt_odbioru_opis" value="<?php echo $zamowienie->info['wysylka_punkt_odbioru']; ?>" readonly="readonly" class="readonly" />
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
                            <input type="text" name="waga_zamowienia" value="<?php echo $waga_produktow; ?>" readonly="readonly" class="readonly" />
                        </p> 

                    </div>

                    <div class="poleForm">

                        <div class="naglowek">Informacje o odbiorcy podane w zamówieniu</div>

                        <p>
                            <label for="adresat">Adresat:</label>
                            <input type="text" size="40" name="adresat" id="adresat" value="<?php echo ( $zamowienie->dostawa['firma'] != '' ? Funkcje::formatujTekstInput($zamowienie->dostawa['firma']) : $zamowienie->dostawa['nazwa']); ?>" class="klient" />
                        </p> 
                        <p>
                            <label for="adresatKontakt">Osoba kontaktowa:</label>
                            <input type="text" size="40" name="adresatKontakt" id="adresatKontakt" value="<?php echo $zamowienie->dostawa['nazwa']; ?>" class="klient" />
                        </p> 
                        <p id="AdresOdbiorcy">
                            <label for="adresat_ulica">Ulica:</label>
                            <input type="text" size="40" name="adresat_ulica" id="adresat_ulica" value="<?php echo Funkcje::formatujTekstInput($adres_klienta['ulica']); ?>" class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_dom">Numer domu:</label>
                            <input type="text" size="40" name="adresat_dom" id="adresat_dom" value="<?php echo Funkcje::formatujTekstInput($adres_klienta['dom']); ?>" class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_kod_pocztowy">Kod pocztowy:</label>
                            <input type="text" size="40" name="adresat_kod_pocztowy" id="adresat_kod_pocztowy" value="<?php echo $zamowienie->dostawa['kod_pocztowy']; ?>" class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_miasto">Miejscowość:</label>
                            <input type="text" size="40" name="adresat_miasto" id="adresat_miasto" value="<?php echo $zamowienie->dostawa['miasto']; ?>" class="klient" />
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

                            <input type="text" size="40" name="adresat_telefon" id="adresat_telefon" value="<?php echo preg_replace("/[^+0-9]/", "", (string)$NumerTelefonu); ?>" class="klient" />
                        </p> 
                        <p>
                            <label for="adresat_mail">Adres e-mail:</label>
                            <input type="text" size="40" name="adresat_mail" id="adresat_mail" value="<?php echo $zamowienie->klient['adres_email']; ?>" class="klient" />
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

    
    </div>  

    <?php
    include('stopka.inc.php');    
    
} 
?>
