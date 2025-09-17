<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('pages_group' , " pages_group_id = '".(int)$_POST["id"]."'"); 
        
        // usuwanie stron informacyjnych dla grupy
        $zapytanie = "select distinct * from pages where pages_group = '".$filtr->process($_POST["kod"])."'";
        $sql = $db->open_query($zapytanie);
        
        if ((int)$db->ile_rekordow($sql) > 0) {
        
            while ($info = $sql->fetch_assoc()) {
                //
                $db->delete_query('pages' , " pages_id = '".$info['pages_id']."'");  
                $db->delete_query('pages_description' , " pages_id = '".$info['pages_id']."'");
                
                // kasowanie z boxow
                $sql_spr = $db->open_query("select * from theme_box where box_pages_id = '" . $filtr->process($_POST["id"]) . "'");
                $info_spr = $sql_spr->fetch_assoc();
                
                if ( isset($info_spr['box_id']) ) {            
                    $db->delete_query('theme_box' , " box_id = '" . (int)$info_spr['box_id'] . "'");
                    $db->delete_query('theme_box_description' , " box_id = '" . (int)$info_spr['box_id'] . "'");
                }
                
                $db->close_query($sql_spr);                
                
                // kasowanie z modulow
                $sql_spr = $db->open_query("select * from theme_modules where modul_pages_id = '" . $filtr->process($_POST["id"]) . "'");
                $info_spr = $sql_spr->fetch_assoc();
                
                if ( isset($info_spr['modul_id']) ) {    
                    $db->delete_query('theme_modules' , " modul_id = '" . (int)$info_spr['modul_id'] . "'");
                    $db->delete_query('theme_modules_description' , " modul_id = '" . (int)$info_spr['modul_id'] . "'");
                }
                
                $db->close_query($sql_spr);

                // funkcja usuwa rowniez wpis w gornym i dolnym menu i stopkach
                Funkcje::UsuwanieWygladu('strona',$info['pages_id']);                
                //
            }
        
        }
            
        $db->close_query($sql);
        unset($zapytanie);   

        // funkcja usuwa rowniez wpis w gornym i dolnym menu i stopkach
        Funkcje::UsuwanieWygladu('grupainfo',$filtr->process($_POST["id"]));            
            
        //
        Funkcje::PrzekierowanieURL('strony_informacyjne_grupy.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="strony_informacyjne/strony_informacyjne_grupy_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "SELECT * FROM pages_group WHERE pages_group_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <input type="hidden" name="kod" value="<?php echo $info['pages_group_code']; ?>" />
                    
                    <p>
                      Czy skasować pozycje ? <br /><br />
                      <span class="ostrzezenie">Jeżeli do grupy są przypisane strony zostaną one usunięte razem z grupą.</span>   
                    </p>   
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('strony_informacyjne_grupy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','strony_informacyjne');">Powrót</button> 
                </div>

                <?php
                
                unset($info);
                
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