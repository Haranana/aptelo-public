<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Informacje o wynikach ankiety</div>
    <div id="cont">
          
          <div class="poleForm">
            <div class="naglowek">Informacje szczegółowe</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from poll where id_poll = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>            
            
                <div class="pozycja_edytowana" style="padding:15px">
                
                <?php                
                $ile_jezykow = Funkcje::TablicaJezykow();
                
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                
                    // ile jest w sumie glosow
                    $ile_glosow = $db->open_query("select SUM(poll_result) as ile_glosow, COUNT('poll_result') as ile_pozycji from poll_field where id_poll = '".(int)$_GET['id_poz']."' and language_id = '".$ile_jezykow[$w]['id']."'");
                    $infr = $ile_glosow->fetch_assoc();
                    $db->close_query($ile_glosow);  

                    if ($infr['ile_pozycji'] > 0) {
                    
                        echo '<div class="AnkietaInfo">';
                        echo '<div class="naglowek" style="border-bottom:1px solid #cccccc; margin-bottom:20px">'.$ile_jezykow[$w]['text'].'</div>';
                    
                        // odpowiedzi
                        $wyniki_ankiety = '<div class="OdpowiedziTlo"><table class="Odpowiedzi">';
                        $odpowiedzi = $db->open_query("select * from poll_field where id_poll = '".(int)$_GET['id_poz']."' and language_id = '".$ile_jezykow[$w]['id']."' order by poll_field_sort");
                        $poz = 1;
                        while ($infs = $odpowiedzi->fetch_assoc()) {
                            //
                            // szerokosc pixela w slupku
                            $szerokosc_slupka = 0;
                            $ilosc_procent = 0;
                            if ($infs['poll_result'] > 0) {
                                $szerokosc_slupka = ((int)(($infs['poll_result'] / $infr['ile_glosow']) * 145) + 3);
                                $ilosc_procent = (int)(($infs['poll_result'] / $infr['ile_glosow']) * 100);
                            }
                            //
                            $czyDacPadding = '';
                            if ($poz == $infr['ile_pozycji']) {
                                $czyDacPadding = ' style="padding-bottom:30px"';
                            }
                            //
                            $wyniki_ankiety .= '<tr>
                                                    <td class="Odpowiedz" '.$czyDacPadding.'>'.$infs['poll_field'].'</td>
                                                    <td class="Slupek" '.$czyDacPadding.'><div style="width:'.$szerokosc_slupka.'px"></div></td>
                                                    <td class="Procent" '.$czyDacPadding.'>'.$infs['poll_result'].' głosów <span>('.$ilosc_procent.'%)</span></td>
                                                </tr>';
                            $poz++;
                        }
                        $db->close_query($odpowiedzi);
                        $wyniki_ankiety .= '</table></div>'; 

                        echo $wyniki_ankiety;
                        echo '</div>';
                        
                    }
                    
                    unset($infr, $infs, $wyniki_ankiety);
                    
                }
                
                ?>
                 
                </div>

                <div class="przyciski_dolne">
                  <button type="button" class="przyciskNon" onclick="cofnij('ankiety','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
                </div>

            <?php
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>

          </div>  
          
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}