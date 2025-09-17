<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $id_opinii = (int)$_POST['id'];
        
        if ( isset($_POST['odpowiedz']) && (int)$_POST['odpowiedz'] == 1 ) {
          
            $pola = array(array('comments_answers',''));
            $db->update_query('reviews_shop' , $pola, " reviews_shop_id = '".$id_opinii."'");
            unset($pola);            
          
        } else {        
    
            // kasuje rekordy w tablicy
            $db->delete_query('reviews_shop' , " reviews_shop_id = '".$id_opinii."'");      
        
        }
        
        Funkcje::PrzekierowanieURL('opinie.php');
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="opinie/opinie_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from reviews_shop where reviews_shop_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <?php if ( isset($_GET['odpowiedz']) ) { ?>
                    
                    <input type="hidden" name="odpowiedz" value="1" />
                    
                    <p>
                      Czy skasować odpowiedź ?                    
                    </p>   
                    
                    <?php } else { ?>

                    <p>
                      Czy skasować opinię ?                    
                    </p>                       
                    
                    <?php } ?> 
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('opinie','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
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