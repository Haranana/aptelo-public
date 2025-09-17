<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $id_komentarza = (int)$_POST['id'];
    
        // kasuje rekordy w tablicy
        $db->delete_query('newsdesk_comments' , " newsdesk_comments_id = '".(int)$id_komentarza."'");     
        
        Funkcje::PrzekierowanieURL('aktualnosci_komentarze.php'.(((int)$_POST['art_id'] > 0) ? '?art_id='.(int)$_POST['art_id'] : ''));
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="aktualnosci/aktualnosci_komentarze_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from newsdesk_comments where newsdesk_comments_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <input type="hidden" name="art_id" value="<?php echo ((isset($_GET['art_id'])) ? (int)$_GET['art_id'] : ''); ?>" />
                    
                    <p>
                      Czy skasować komentarz ?                    
                    </p>   
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('aktualnosci_komentarze','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','art_id')); ?>','aktualnosci');">Powrót</button>  
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