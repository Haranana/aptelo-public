<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {


    $apiKurier       = new FurgonetkaRestApi(true, '', '');

    $TablicaId = array();
    if ( isset($_GET['IDs']) && $_GET['IDs'] != '' ) {
        ?>

                    <?php
                    $zapytanie = $db->open_query("SELECT orders_shipping_id, orders_shipping_number, orders_shipping_misc, orders_shipping_comments
                    FROM orders_shipping WHERE orders_shipping_id in (".$_GET['IDs'].")");    

                    $params = new stdClass;
                    $params->packages = array();

                    $Tablica_tmp = array();
                    $licznik = 1;
                    $Operator = '';

                    while ($info = $zapytanie->fetch_assoc()) {

                        echo '<input type="hidden" name="paczki[]" value="'.$info['orders_shipping_misc'].'" />';

                        if ( $info['orders_shipping_misc'] != '' ) {
                            $Parcel             = new stdClass;
                            $Parcel->id         = $info['orders_shipping_misc'];
                            $params->packages[] = $Parcel;

                            $Tablica_tmp[$info['orders_shipping_misc']] = $info['orders_shipping_comments'];
                        }
                        if ( $info['orders_shipping_comments'] == 'INPOST' ) {
                            $Operator = 'INPOST';
                        }

                    }
                    $db->close_query($zapytanie);    
                    unset($zapytanie, $info);
                    $TablicaZamowien = array_unique($Tablica_tmp);

                    if ( count($TablicaZamowien) == 1 ) {

                        $params->ready_date = date('Y-m-d');

                        $Wynik = $apiKurier->commandPost('packages/pickup-date-proposals', $params);

                        $WynikPaczki = $Wynik->packages;

                        if ( $Wynik ) {

                            $DostepneDaty = array();
                            $MinimalnaData = date('Y-m-d');

                            ?>

                            <input type="hidden" name="akcja" value="zapisz" />

                            <p>

                                <?php
                                foreach ( $WynikPaczki as $przesylka ) {
                                    if ( isset($przesylka->proposals) && count($przesylka->proposals) > 0 ) {
                                        foreach ( $przesylka->proposals as $data ) {
                                            $DostepneDaty[] = $data->date;
                                        }
                                    }
                                }
                                if ( count($DostepneDaty) > 0 ) {
                                    ?>
                                    <label style="font-size:120%; font-weight:bold;">Data odbioru:</label>
                                    <ul class="servicesTresc" style="padding-left:25px;">
                                     <?php
                                     $licznik = 1;
                                     foreach ( $WynikPaczki as $przesylka ) {
                                        if ( isset($przesylka->proposals) && count($przesylka->proposals) > 0 ) {
                                            foreach ( $przesylka->proposals as $data ) {
                                                echo '<li>';
                                                    echo '<input name="odbior" id="odbior_'.$licznik.'" class="terms" type="radio" value="'.$data->date.'|'.$data->min_time.'|' .$data->max_time.'" '.($licznik == 1 ? 'checked="checked"' : '' ) . ' /><label class="OpisFor" for="odbior_'.$licznik.'">'.$data->date.' : '.$data->min_time.' - ' .$data->max_time.'</label>';
                                                echo '</li>';
                                                $licznik++;
                                            }
                                        }
                                     }
                                     ?>


                                    </ul>
                                    <?php
                                } else {
                                    ?>
                                    <div class="maleInfo">Wybrany przewoźnik nie udostępnia wyboru daty odbioru</div>
                                    <?php
                                    if ( $Operator == 'INPOST' ) {
                                        echo '<div class="maleInfo" style="font-weight:bold;">Kurier odbiera przesyłki w dni robocze w godzinach 09:00 - 16:00</div>';
                                    }
                                }
                                ?>
                           </p>

                           <?php 
                         } else {
                            echo Okienka::pokazOkno('Błąd', 'Nie można zamówić podjazdu dla wybranych przesyłek. Wybrano przesyłki, które już miały zlecony podjazd lub są anulowane.', 'sprzedaz/zamowienia_wysylki_furgonetka.php');
                         }

                    } else {
                        echo Okienka::pokazOkno('Błąd', 'Nie można zamówić podjazdu dla wybranych przesyłek - proszę wybrać przesyłki jednego operatora.', 'sprzedaz/zamowienia_wysylki_furgonetka.php');

                    }
                    ?>

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