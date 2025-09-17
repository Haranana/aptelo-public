<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {


    $apiKurier = new BliskapaczkaApi('','');

    ?>
    <script>
        $(document).ready(function() {

            $('input.datepicker').Zebra_DatePicker({
                format: 'd-m-Y',
                inside: false,
                readonly_element: false
            });                
                            
        });
    </script>  

    <?php
    $TablicaId = array();
    if ( isset($_GET['IDs']) && $_GET['IDs'] != '' ) {
        ?>

        <div class="EdycjaOdstep">

            <div class="pozycja_edytowana">

                <div class="info_content">

                    <?php
                    $zapytanie = $db->open_query("SELECT orders_shipping_id, orders_shipping_number, orders_shipping_misc, orders_shipping_comments
                    FROM orders_shipping WHERE orders_shipping_id in (".$_GET['IDs'].") AND orders_shipping_status = 'READY_TO_SEND'");    

                    $Tablica_tmp = array();
                    $TablicaPrzewoznikow = array();
                    $licznik = 1;

                    while ($info = $zapytanie->fetch_assoc()) {

                        $TablicaPrzewoznikow[] = $info['orders_shipping_comments'];

                        echo '<input type="hidden" name="paczki[]" value="'.$info['orders_shipping_number'].'" />';
                        $Wynik = $apiKurier->commandGet('v2/order/pickup?orderNumbers='.$info['orders_shipping_number']);

                        foreach ( $Wynik as $PickUp ) {
                            if ( $licznik == 1 ) {
                                if ( $PickUp->availablePickups != '' ) {
                                    $Tablica_tmp = $PickUp->availablePickups;
                                }
                            }
                            $licznik++;
                        }

                        unset($Wynik);

                    }
                    $db->close_query($zapytanie);    
                    unset($zapytanie, $info);

                    $TablicaZamowien = array_unique($TablicaPrzewoznikow);

                    if ( count($TablicaZamowien) == 1 && count($Tablica_tmp) > 0 ) {
                        ?>

                        <input type="hidden" name="akcja" value="zapisz" />

                        <p>
                            <label>Data odbioru:</label>
                            <ul class="servicesTresc">
                                 <?php
                                 $licznik = 1;
                                 foreach ( $Tablica_tmp as $Odbior ) {
                                    echo '<li>';
                                        echo '<input name="odbior" id="odbior_'.$licznik.'" class="terms" type="radio" value="'.$Odbior->date.'|'.$Odbior->timeFrom.'|' .$Odbior->timeTo.'" '.($licznik == 1 ? 'checked="checked"' : '' ) . ' /><label class="OpisFor" for="odbior_'.$licznik.'">'.$Odbior->date.' : '.$Odbior->timeFrom.' - ' .$Odbior->timeTo.'</label>';
                                    echo '</li>';
                                    $licznik++;
                                 }
                                 ?>
                            </ul>
                       </p>

                    <?php } else { ?>
                    
                        <div><span class="maleInfo">Nie można zamówić podjazdu dla wybranych przesyłek. Wybrano przesyłki, które już miały zlecony podjazd lub są anulowane</span></div>
                        
                    <?php } ?>
                </div>

            </div>
            
        </div>

        <?php
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