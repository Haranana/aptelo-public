<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $id_edytowanej_pozycji = (int)$_POST['id'];
        $id_klienta = (int)$_POST['id_poz'];
        //
        $pola = array(array('points',(int)$_POST['pkt']));
        //	
        $sql = $db->update_query('customers_points', $pola, 'unique_id = ' . $id_edytowanej_pozycji);
        unset($pola);     

        // czy ma zmniejszyc ogolna ilosc punktow klienta
        if ($_POST['tryb'] == '1') {   
        
            // aktualizacja stanu punktow klienta
            $zapytanie = "select * from customers where customers_id = '".(int)$_POST["id_poz"]."'";
            $sql = $db->open_query($zapytanie);
                
            $info = $sql->fetch_assoc();
            $aktualnaIloscPunktow = $info['customers_shopping_points'];
            $roznicaPunktow = (int)$_POST['pkt'] - (int)$_POST['stara_ilosc'];
            $nowaIloscPunktow = $aktualnaIloscPunktow + $roznicaPunktow;
            $pola = array(array('customers_shopping_points',( $nowaIloscPunktow > 0 ? $nowaIloscPunktow : '0' )));
            //
            $db->update_query('customers', $pola, 'customers_id = ' . (int)$_POST["id_poz"]);
            unset($pola); 

        }        
        
        Funkcje::PrzekierowanieURL('partnerzy_operacje.php?id_poz='.(int)$id_klienta);

    }   

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="program_partnerski/partnerzy_operacje_edytuj.php" method="post" id="klienciForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            if ( !isset($_GET['id']) ) {
                 $_GET['id'] = 0;
            }               
            
            $zapytanie = "select distinct * from customers_points where unique_id = '".(int)$_GET["id"]."' and customers_id = '".(int)$_GET["id_poz"]."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                ?>            
            
                <div class="pozycja_edytowana">    
                
                    <input type="hidden" name="akcja" value="zapisz" />

                    <input type="hidden" name="id" value="<?php echo (int)$info['unique_id']; ?>" />
                    <input type="hidden" name="id_poz" value="<?php echo (int)$info['customers_id']; ?>" />
                    <input type="hidden" name="stara_ilosc" value="<?php echo $info['points']; ?>" />

                    <div class="info_content">

                    <script>
                    $(document).ready(function() {
                    
                        $("#klienciForm").validate({
                          rules: {
                            pkt: {
                              required: true,
                              range: [-100000, 100000],
                              number: true
                            }                    
                          },
                          messages: {
                            pkt: {
                              required: "Pole jest wymagane."
                            }                       
                          }
                        });                

                    });
                    </script>  
                    
                    <p>
                        <label class="required" for="pkt">Ilość punktów:</label>
                        <input type="text" name="pkt" id="pkt" value="<?php echo $info['points']; ?>" size="30" />                                        
                    </p>   

                    <p>
                        <label>Zmiana punktów klienta:</label>
                        <input type="radio" value="1" name="tryb" id="tryb_dodaj" /><label class="OpisFor" for="tryb_dodaj">dodaj lub odejmij punkty klientowi<em class="TipIkona"><b>Ogólna ilość punktów klienta zostanie zmieniona</b></em></label>
                        <input type="radio" value="2" name="tryb" id="tryb_bezzmian"  checked="checked" /> <label class="OpisFor" for="tryb_bezzmian">nie zmieniaj ilości punktów<em class="TipIkona"><b>Ogólna ilość punktów klienta pozostanie bez zmian</b></em></label>                                
                    </p>                     

                    </div>
                    
                </div>
                
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('partnerzy_operacje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','program_partnerski');">Powrót</button>                     
                </div>

            <?php 
            $db->close_query($sql);
            unset($info);

            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>                    
            
          </div>

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>