<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $apiKurier       = new DhlApi();

    $TablicaId = array();
    if ( isset($_GET['IDs']) && $_GET['IDs'] != '' ) {
        $zapytanie = $db->open_query("SELECT orders_shipping_id, orders_shipping_number, orders_shipping_misc, orders_shipping_comments
                    FROM orders_shipping WHERE orders_shipping_id in (".$_GET['IDs'].")");


            $DostepneDaty = Funkcje::DostepneDatyPlus(date('Y-m-d'), '3');

            for ( $i = 0; $i < count($DostepneDaty); $i++ ) {
                $DostepneSerwisy = new stdClass();
                $DostepneSerwisy = $apiKurier->PostalCodeServices($apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_KOD_POCZTOWY'], $DostepneDaty[$i], $apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_MIASTO'], $apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_ULICA'], $apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_ULICA_DOM'], $apiKurier->polaczenie['INTEGRACJA_DHL_NADAWCA_ULICA_MIESZKANIE'] );
                if ( $DostepneSerwisy->getPostalCodeServicesResult->drPickupFrom != 'brak' ) {
                    if ( $DostepneDaty[$i] == date('Y-m-d') ) {
                        $GodzinaBiezaca = date('H');
                        if ( $GodzinaBiezaca >= intval($DostepneSerwisy->getPostalCodeServicesResult->drPickupFrom) ) {
                            $DostepneSerwisy->getPostalCodeServicesResult->drPickupFrom = $GodzinaBiezaca + 1;
                        }
                    }
                    $OdbiorSelect[] = array('id' => $DostepneDaty[$i].'|'.intval($DostepneSerwisy->getPostalCodeServicesResult->drPickupFrom).'|'.intval($DostepneSerwisy->getPostalCodeServicesResult->drPickupTo).'|2', 'text' => $DostepneDaty[$i]);
                    $Odbior[$DostepneDaty[$i]] = array();
                    $Odbior[$DostepneDaty[$i]]['day'] = $DostepneDaty[$i];
                    $Odbior[$DostepneDaty[$i]]['timefrom'] = intval($DostepneSerwisy->getPostalCodeServicesResult->drPickupFrom);
                    $Odbior[$DostepneDaty[$i]]['timeto'] = intval($DostepneSerwisy->getPostalCodeServicesResult->drPickupTo);
                    $Odbior[$DostepneDaty[$i]]['interval'] = '2';
                }
            }

            $DzienPierwszyTablica = reset($Odbior);
            ?>

            <script src="programy/timepicker/jquery.timepicker.js"></script>
            <link rel="stylesheet" type="text/css" href="programy/timepicker/jquery.timepicker.css" />


        <script>
            $(document).ready(function() {

                $("#DHLAkcjaDolna").validate({
                    rules: {
                        pickup_date_hours_from: {
                             required: true
                        },
                        pickup_date_hours_to: {
                             required: true
                        }

                    }
                });

                var CzasMinFrom = <?php echo $DzienPierwszyTablica['timefrom']; ?>;
                var CzasMaxFrom = <?php echo $DzienPierwszyTablica['timeto']; ?> - <?php echo $DzienPierwszyTablica['interval']; ?>;

                var CzasMinTo = <?php echo $DzienPierwszyTablica['timefrom']; ?> + <?php echo $DzienPierwszyTablica['interval']; ?>;
                var CzasMaxTo = <?php echo $DzienPierwszyTablica['timeto']; ?>;

                //intialize///
                $('.pickup_date_hours_from').timepicker({
                  timeFormat: 'HH:mm',
                  interval: 60,
                  minTime: "'"+CzasMinFrom+":00'",
                  maxTime: "'"+CzasMaxFrom+":00'",
                  dynamic: false,
                  dropdown: true,
                  scrollbar: true,
                  change: ZmienZakresDo
                });
                $('.pickup_date_hours_to').timepicker({
                  timeFormat: 'HH:mm',
                  interval: 60,
                  minTime: "'"+CzasMinTo+":00'",
                  maxTime: "'"+CzasMaxTo+":00'",
                  dynamic: false,
                  dropdown: true,
                  scrollbar: true
                });

                $(document).on('change', '#pickup_date', function() {
                    $('#pickup_date_hours_from').val('');
                    $('#pickup_date_hours_to').val('');

                    var pickdate = $('#pickup_date').val();
                    var Tablica = pickdate.split("|");

                    var CzasMinFrom = Number(Tablica[1]);
                    var CzasMaxFrom = Number(Tablica[2]) - Number(Tablica[3]);

                    var CzasMinTo = Number(Tablica[1]) + Number(Tablica[3]);
                    var CzasMaxTo = Number(Tablica[2]);

                    var minTimeFrom = "'"+CzasMinFrom+":00'";
                    var maxTimeFrom = "'"+CzasMaxFrom+":00'";
                    var minTimeTo   = "'"+CzasMinTo+":00'";
                    var maxTimeTo   = "'"+CzasMaxTo+":00'";

                    $('#pickup_date_hours_from').val(CzasMinFrom+":00");
                    $('#pickup_date_hours_to').val(CzasMinTo+":00");

                    $('.pickup_date_hours_from').timepicker('option', 'minTime', minTimeFrom);
                    $('.pickup_date_hours_from').timepicker('option', 'maxTime', maxTimeFrom);
                    $('.pickup_date_hours_to').timepicker('option', 'minTime', minTimeTo);
                    $('.pickup_date_hours_to').timepicker('option', 'maxTime', maxTimeTo);
                });

                $(".datepicker").trigger("change") //on load of page call this

                function ZmienZakresDo () {
                    var pickdate = $('#pickup_date').val();
                    var Tablica = pickdate.split("|");
                    var startTime = $("#pickup_date_hours_from").val();
                    var Godzina = startTime.split(":");
                    var minTime = Number(Godzina[0]) + Number(Tablica[3]);
                    var minTimeTo   = "'"+minTime+":00'";

                    if ( startTime != '' ) {
                        $('#pickup_date_hours_to').val(minTime+":00");
                        $('.pickup_date_hours_to').timepicker('option', 'minTime', minTimeTo);
                    }
                };

            })

        </script>

        <?php

        if ( is_array($Odbior) && count($Odbior) > 0 ) {
            ?>
            <div id="PickupCOURIER" style="margin:10px;">
                <div style="margin-left:10px; padding:10px; font-size:120%; font-weight:bold;">Zamów podjazd kuriera</div>
                <div style="display:flex;padding:10px;">
                <label>Termin:</label>
                    <div style=";margin-right:15px;">
                        <?php
                        $TablicaTMP = array();
                        $wynik = '<select name="pickup_date" id ="pickup_date" class="datepicker">';
                        foreach ($OdbiorSelect as $OdbiorSelectTMP ) {
                            $wynik .= '<option value="' . $OdbiorSelectTMP['id'] . '">'.$OdbiorSelectTMP['text'].'</option>';
                        }
                        $wynik .= '</select>';
                        echo $wynik;
                        ?>
                    </div>
                    <div style="margin-right:15px;">
                        <input id="pickup_date_hours_from" class="pickup_date_hours_from" value="" name="pickup_date_hours_from" class="required" readonly="readonly" />
                    </div>
                    <div>
                        <input id="pickup_date_hours_to" class="pickup_date_hours_to" value="" name="pickup_date_hours_to"  class="required" readonly="readonly" />
                    </div>
                </div>
                <p>
                    <label for="comment">Dodatkowe informacje dla kuriera [max. 50 znaków]:</label>
                    <input type="text" name="comment" id="comment" value="" size="53" />
                </p>
                <p>
                    <label for="courierWithLabel">Czy kurier ma przyjechać z etykietą:</label>
                    <input type="checkbox" value="1" name="courierWithLabel" id="courierWithLabel" /><label class="OpisForPustyLabel" for="courierWithLabel" style="margin-right:10px;">
                </p>
            </div>

            <?php
        }

    } else {
        ?>
        <div class="EdycjaOdstep">
            <div class="pozycja_edytowana">
                <div class="info_content">
                Nie wybrano zadnych wysyłek
                </div>
            </div>
        </div>

        <?php
    }
     
  }

?>