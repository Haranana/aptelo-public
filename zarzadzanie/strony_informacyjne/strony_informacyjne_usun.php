<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('pages' , " pages_id = '".(int)$_POST["id"]."'");  
        $db->delete_query('pages_description' , " pages_id = '".(int)$_POST["id"]."'");
        
        // kasowanie z boxow
        $sql = $db->open_query("select * from theme_box where box_pages_id = '" .(int)$_POST["id"] . "'");
        $info = $sql->fetch_assoc();
        
        if ( isset($info['box_id']) ) {            
            $db->delete_query('theme_box' , " box_id = '" . (int)$info['box_id'] . "'");
            $db->delete_query('theme_box_description' , " box_id = '" . (int)$info['box_id'] . "'");
        }
        
        $db->close_query($sql);
        
        // kasowanie z modulow
        $sql = $db->open_query("select * from theme_modules where modul_pages_id = '" . (int)$_POST["id"] . "'");
        $info = $sql->fetch_assoc();
        
        if ( isset($info['modul_id']) ) {    
            $db->delete_query('theme_modules' , " modul_id = '" . (int)$info['modul_id'] . "'");
            $db->delete_query('theme_modules_description' , " modul_id = '" . (int)$info['modul_id'] . "'");
        }
        
        $db->close_query($sql);
        
        // funkcja usuwa rowniez wpis w gornym i dolnym menu i stopkach
        Funkcje::UsuwanieWygladu('strona',(int)$_POST["id"]);
        
        //
        Funkcje::PrzekierowanieURL('strony_informacyjne.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="strony_informacyjne/strony_informacyjne_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from pages where pages_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      Czy skasować stronę ?                    
                    </p>   
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('strony_informacyjne','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
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