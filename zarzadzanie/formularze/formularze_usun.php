<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('form' , " id_form = '".(int)$_POST["id"]."'");  
        $db->delete_query('form_description' , " id_form = '".(int)$_POST["id"]."'");
        $db->delete_query('form_field' , " id_form = '".(int)$_POST["id"]."'");
        
        // funkcja usuwa rowniez wpis w gornym i dolnym menu i stopkach
        Funkcje::UsuwanieWygladu('formularz',(int)$_POST["id"]);
        
        //
        Funkcje::PrzekierowanieURL('formularze.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="formularze/formularze_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            // id ktorych nie mozna skasowac
            $wykluczoneId = array(1,2,3,4);
            
            if ( !isset($_GET['id_poz']) || in_array((string)$_GET['id_poz'], $wykluczoneId) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            unset($wykluczoneId);
            
            $zapytanie = "select * from form where id_form = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      Czy skasować formularz ?                    
                    </p>   
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('formularze','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
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