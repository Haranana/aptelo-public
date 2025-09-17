<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('return_status' , " return_status_id = '".(int)$_POST["id"]."'");  
        $db->delete_query('return_status_description' , " return_status_id = '".(int)$_POST["id"]."'"); 
        //
        Funkcje::PrzekierowanieURL('zwroty_statusy.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="zwroty/zwroty_statusy_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from return_status where return_status_id = '" . (int)$_GET['id_poz'] . "' and return_status_default = '0'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                // sprawdza dodatkowo czy status nie wystepuje w jakims zamowieniu
                $zapytanie_zwroty = "select * from return_status_history where return_status_id = '" . (int)$_GET['id_poz'] . "'";
                $sql_zwroty = $db->open_query($zapytanie_zwroty);
                
                if ((int)$db->ile_rekordow($sql_zwroty) == 0) {
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
                  <button type="button" class="przyciskNon" onclick="cofnij('zwroty_statusy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','zwroty');">Powrót</button> 
                </div>
                
                <?php } else { ?>
                
                <div class="pozycja_edytowana">
                    
                    <p>
                      <span class="ostrzezenie">Status był już używany w zwrotach klientów !!</span>                          
                    </p>                  
                
                    <p>
                        Tego statusu nie można usunąć !!
                    </p>
                 
                </div>

                <div class="przyciski_dolne">
                  <button type="button" class="przyciskNon" onclick="cofnij('zwroty_statusy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','zwroty');">Powrót</button> 
                </div>                
                
                <?php } 
                
                $db->close_query($sql_zwroty);
                unset($zapytanie_zwroty);                 
                
                ?>

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