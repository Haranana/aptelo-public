<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('newsdesk_categories' , " categories_id = '".(int)$_POST["id"]."'");  
        $db->delete_query('newsdesk_categories_description' , " categories_id = '".(int)$_POST["id"]."'");
        //
        $sqls = $db->open_query("select distinct * from newsdesk_to_categories where categories_id = '".(int)$_POST["id"]."'");  
        while ($art = $sqls->fetch_assoc()) { 
            //
            // funkcja usuwa wpis w gornym i dolnym menu i stopkach
            Funkcje::UsuwanieWygladu('artykul',$art['newsdesk_id']);          
            //
            $db->delete_query('newsdesk' , " newsdesk_id = '".$art['newsdesk_id']."'");  
            $db->delete_query('newsdesk_description' , " newsdesk_id = '".$art['newsdesk_id']."'");   
            $db->delete_query('newsdesk_to_categories' , " newsdesk_id = '".$art['newsdesk_id']."' and categories_id = '".(int)$_POST["id"]."'");            
            //
        }
        // 
        $db->close_query($sqls);
        unset($art);    
        
        // funkcja usuwa rowniez wpis w gornym i dolnym menu i stopkach
        Funkcje::UsuwanieWygladu('kategoria',(int)$_POST["id"]);        
        Funkcje::UsuwanieWygladu('artkategorie',(int)$_POST["id"]); 
         
        //
        Funkcje::PrzekierowanieURL('aktualnosci.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="aktualnosci/aktualnosci_kategorie_usun.php" method="post" id="kategorieForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['kat_id']) ) {
                 $_GET['kat_id'] = 0;
            }    
            
            $zapytanie = "select * from newsdesk_categories where categories_id= '" . (int)$_GET['kat_id'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['kat_id']; ?>" />
                    
                    <p>
                      Czy skasować kategorię ?

                      <?php
                      $ile_ma_artykulow = 0;
                      $zapytanie = "select * from newsdesk_to_categories where categories_id= '" . (int)$_GET['kat_id'] . "'";
                      $sql = $db->open_query($zapytanie);
                      $ile_ma_artykulow = (int)$db->ile_rekordow($sql);                   
                      
                      if ($ile_ma_artykulow > 0) { ?>
                      <br /><br /><span class="ostrzezenie">Kategoria zawiera <b><?php echo $ile_ma_artykulow; ?></b> artykułów wciąż powiązanych z tą kategorią - <span style="color:#ff0000">zostaną one również skasowane</span> !</span>
                      <?php } ?>                      
                    </p>   
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('aktualnosci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>
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