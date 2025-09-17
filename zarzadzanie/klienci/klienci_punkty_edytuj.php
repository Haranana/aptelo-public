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
        $db->update_query('customers_points', $pola, 'unique_id = ' . $id_edytowanej_pozycji);
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

        if ( isset($_GET['pkt']) ) {
            Funkcje::PrzekierowanieURL('punkty_do_zatwierdzenia.php?id_poz='.(int)$id_edytowanej_pozycji);
          } else {   
            Funkcje::PrzekierowanieURL('klienci_edytuj.php?id_poz='.(int)$id_klienta.Funkcje::Zwroc_Wybrane_Get(array('zakladka'),true));
        }        

    }   

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="klienci/klienci_punkty_edytuj.php<?php echo Funkcje::Zwroc_Wybrane_Get(array('zakladka','pkt')); ?>" method="post" id="klienciForm" class="cmxform"> 
          
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

                    <?php if ( isset($_GET['pkt']) ) { ?>
                    <div style="display:none">
                    <?php } ?>
                    
                    <p>
                        <label>Zmiana punktów klienta:</label>
                        <input type="radio" value="1" name="tryb" id="tryb1" /><label class="OpisFor" for="tryb1">dodaj lub odejmij punkty klientowi<em class="TipIkona"><b>Ogólna ilość punktów klienta zostanie zmieniona</b></em></label>
                        <input type="radio" value="2" name="tryb" id="tryb2" checked="checked" /><label class="OpisFor" for="tryb2">nie zmieniaj ilości punktów<em class="TipIkona"><b>Ogólna ilość punktów klienta pozostanie bez zmian</b></em></label>
                    </p>  

                    <?php if ( isset($_GET['pkt']) ) { ?>
                    </div>
                    <?php } ?>

                    </div>
                    
                </div>
                
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <?php if ( isset($_GET['pkt']) ) { ?>
                  <button type="button" class="przyciskNon" onclick="cofnij('punkty_do_zatwierdzenia','<?php echo Funkcje::Zwroc_Get(array('pkt','id_poz','x','y')); ?>','klienci');">Powrót</button> 
                  <?php } else { ?>
                  <button type="button" class="przyciskNon" onclick="cofnij('klienci_edytuj','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','zakladka')); ?>','klienci');">Powrót</button> 
                  <?php } ?>                    
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