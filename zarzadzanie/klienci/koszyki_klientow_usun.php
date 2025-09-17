<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        // ustali ip
        $zapytanie = "SELECT customers_ip, customers_id FROM customers_basket WHERE customers_basket_id = '" . (int)$_POST["id_poz"] . "'";
        $sql = $db->open_query($zapytanie);
        
        if ( (int)$db->ile_rekordow($sql) > 0 ) {
              //
              $info = $sql->fetch_assoc();
              //    
              $pattern = '/\b((25[0-5]|2[0-4][0-9]|1?[0-9]{1,2})\.){3}(25[0-5]|2[0-4][0-9]|1?[0-9]{1,2})\b/';
              $nrip = '';
              //
              if (preg_match($pattern, $info['customers_ip'], $match)) {
                  $nrip = $match[0];
              }          
              //
              $info['customers_ip'] = $nrip;
              //
              $db->delete_query('customers_basket', "customers_ip = '" . $info['customers_ip'] . "' and customers_id = '" . $info['customers_id'] . "'");
              //
        }
        
        $db->close_query($sql);
        unset($zapytanie);
        //
        Funkcje::PrzekierowanieURL('koszyki_klientow.php');
        //
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="klienci/koszyki_klientow_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }       
            
            $zapytanie = "select distinct * from customers_basket where customers_basket_id = '" . (int)$_GET["id_poz"] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id_poz" value="<?php echo (int)$_GET['id_poz']; ?>" />

                    <p>
                      Czy skasować pozycje ?
                    </p>   

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('koszyki_klientow','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','zakladka')); ?>','klienci');">Powrót</button> 
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