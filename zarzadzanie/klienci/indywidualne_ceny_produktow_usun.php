<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('customers_price' , " cp_id = '".(int)$_POST["id"]."'");  
        //
        if ( isset($_POST['id_klient']) ) {
             //
             Funkcje::PrzekierowanieURL('klienci_edytuj.php?id_poz='.(int)$_POST["id_klient"].'&zakladka=11');
             //
          } else {
             //
             Funkcje::PrzekierowanieURL('indywidualne_ceny_produktow.php');
             //
        }        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="klienci/indywidualne_ceny_produktow_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from customers_price where cp_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <?php
                    if ( isset($_GET['id_klient']) && (int)$_GET['id_klient'] ) {
                         echo '<input type="hidden" name="id_klient" value="' . (int)$_GET['id_klient'] . '" />';
                    }
                    ?>
                    
                    <p>
                      Czy skasować pozycje ?
                    </p>   
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />

                  <?php if ( isset($_GET['id_klient']) && (int)$_GET['id_klient'] ) { ?>
                  
                      <button type="button" class="przyciskNon" onclick="cofnij('klienci_edytuj','?id_poz=<?php echo (int)$_GET['id_klient']; ?>&zakladka=11','klienci');">Powrót</button>   
                  
                  <?php } else { ?>
                  
                      <button type="button" class="przyciskNon" onclick="cofnij('indywidualne_ceny_produktow','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','klienci');">Powrót</button>   
                      
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