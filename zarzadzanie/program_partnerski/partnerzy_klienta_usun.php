<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $id_klienta = (int)$_POST['id_klient'];
        //			
        $pola = array(array('pp_id_customers',0),
                      array('pp_id_customers_coupon',0),
                      array('pp_statistics',0), 
                      array('pp_id_customers_coupon_code',''));
                      
        $sql = $db->update_query('customers', $pola, 'customers_id = ' . $id_klienta);
        unset($pola); 
        //
        Funkcje::PrzekierowanieURL('partnerzy_klienci.php?id_poz='.(int)$_POST['id_poz']);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="program_partnerski/partnerzy_klienta_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            if ( !isset($_GET['id_klient']) ) {
                 $_GET['id_klient'] = 0;
            }               
            
            $zapytanie = "select distinct * from customers where customers_id = '".(int)$_GET["id_klient"]."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id_poz" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    <input type="hidden" name="id_klient" value="<?php echo (int)$_GET['id_klient']; ?>" />

                    <p>
                      Czy skasować powiązanie klienta z Programem Parterskim ?
                    </p>   

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń powiązanie" />
                  <button type="button" class="przyciskNon" onclick="cofnij('partnerzy_klienci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','program_partnerski');">Powrót</button>     
                </div>

                <?php

            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            $db->close_query($sql);
            unset($zapytanie);               
            ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}