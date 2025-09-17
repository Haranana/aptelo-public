<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('currencies' , " currencies_id = '".(int)$_POST["id"]."'");  
        //
        Funkcje::PrzekierowanieURL('waluty.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="slowniki/waluty_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) || (int)$_GET['id_poz'] == 1 ) {
                 $_GET['id_poz'] = 0;
            }    
            
            // musi sprawdzic czy nie ma produktow ktore maja przypisana taka walute
            $zapytanie = "select products_id from products where products_currencies_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ( (int)$db->ile_rekordow($sql) == 0 ) {
            
                $zapytanie = "select * from currencies where currencies_id = '" . (int)$_GET['id_poz'] . "'";
                $sql = $db->open_query($zapytanie);
                
                if ((int)$db->ile_rekordow($sql) > 0) {
                    ?>            
                
                    <div class="pozycja_edytowana">
                    
                        <input type="hidden" name="akcja" value="zapisz" />
                    
                        <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                        
                        <p>
                          Czy skasować pozycje ?
                        </p>   
                     
                    </div>

                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Usuń dane" />
                      <button type="button" class="przyciskNon" onclick="cofnij('waluty','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button> 
                    </div>

                <?php
                } else {
                
                    echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
                
                }
            
            } else { 
            
                ?>
                
                <div class="pozycja_edytowana">
                    <p>
                        Do tej waluty jest przypisane w sklepie <b><?php echo (int)$db->ile_rekordow($sql); ?></b> produktów. <br /><br />
                        <span class="ostrzezenie">Nie można usunąć waluty dopóki będą do niej przypisane produkty. </span>
                    </p>
                </div>
                
                <div class="przyciski_dolne">
                  <button type="button" class="przyciskNon" onclick="cofnij('waluty','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button> 
                </div>                
                
                <?php
                
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