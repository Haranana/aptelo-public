<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $id_klienta = (int)$_POST['id_poz'];
        //			
        $db->delete_query('customers_points' , " unique_id = '".(int)$_POST["id"]."' AND customers_id = '".(int)$_POST["id_poz"]."'");
        //
        if ( isset($_GET['pkt']) ) {
            Funkcje::PrzekierowanieURL('punkty_do_zatwierdzenia.php');
          } else {   
            Funkcje::PrzekierowanieURL('klienci_edytuj.php?id_poz='.(int)$id_klienta.Funkcje::Zwroc_Wybrane_Get(array('zakladka'),true));
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="klienci/klienci_punkty_usun.php<?php echo Funkcje::Zwroc_Wybrane_Get(array('zakladka','pkt')); ?>" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
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
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id']; ?>" />
                    
                    <input type="hidden" name="id_poz" value="<?php echo (int)$_GET['id_poz']; ?>" />

                    <p>
                      Czy skasować pozycje ?
                    </p>   

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <?php if ( isset($_GET['pkt']) ) { ?>
                  <button type="button" class="przyciskNon" onclick="cofnij('punkty_do_zatwierdzenia','<?php echo Funkcje::Zwroc_Get(array('pkt','id_poz','x','y')); ?>','klienci');">Powrót</button> 
                  <?php } else { ?>
                  <button type="button" class="przyciskNon" onclick="cofnij('klienci_edytuj','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','zakladka')); ?>','klienci');">Powrót</button> 
                  <?php } ?>
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