<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('countries' , " countries_id = '".(int)$_POST["id"]."'");  
        $db->delete_query('countries_description' , " countries_id = '".(int)$_POST["id"]."'");
        //
        $db->delete_query('zones' , " zone_country_id = '".(int)$_POST["id"]."'");

      Funkcje::PrzekierowanieURL('kraje.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="slowniki/kraje_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            // najpierw sprawdzi czy wogole jest chociaz jeden kraj - jak nie to nie pozowoli skasowac
            $zapytanie = "select * from countries";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) == 0) {
            
                ?>
                
                <div class="pozycja_edytowana">
                    <p>
                        W sklepie musi pozostać chociaż jeden kraj. <br /><br />
                        <span class="ostrzezenie">Nie można usunąć tego kraju. </span>
                    </p>
                </div>
                
                <div class="przyciski_dolne">
                  <button type="button" class="przyciskNon" onclick="cofnij('kraje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button> 
                </div>                   

            <?php } else {
            
                if ( !isset($_GET['id_poz']) ) {
                     $_GET['id_poz'] = 0;
                }    
                
                $zapytanie = "select * from countries where countries_id = '" . (int)$_GET['id_poz'] . "' and countries_default = '0'";
                $sqla = $db->open_query($zapytanie);
                
                if ((int)$db->ile_rekordow($sqla) > 0) {
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
                      <button type="button" class="przyciskNon" onclick="cofnij('kraje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button> 
                    </div>

                <?php
                } else {
                
                    echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
                
                }
                
                $db->close_query($sqla);
                unset($zapytanie);                  
                
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