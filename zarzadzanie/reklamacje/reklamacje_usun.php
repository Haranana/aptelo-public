<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {	
        
        // sprawdzi czy nie ma zdjecia
        $zapytanie = "select *from complaints where complaints_id = '".(int)$_POST["id"]."'";
        $sql = $db->open_query($zapytanie);
        
        if ((int)$db->ile_rekordow($sql) > 0) {
          
            $info = $sql->fetch_assoc(); 
            
            for ( $x = 1; $x < 4; $x++ ) {

                if ( !empty($info['complaints_image_' . $x]) ) {
                     
                     if ( file_exists('../grafiki_inne/' . $info['complaints_image_' . $x]) ) {
                          unlink('../grafiki_inne/' . $info['complaints_image_' . $x]);
                     }
                     
                }
                
            }
              
        }
        
        $db->close_query($sql);        
        
        $db->delete_query('complaints', " complaints_id = '".(int)$_POST["id"]."'");  
        $db->delete_query('complaints_status_history', " complaints_id = '".(int)$_POST["id"]."'");  

        Funkcje::PrzekierowanieURL('reklamacje.php');
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="reklamacje/reklamacje_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from complaints where complaints_id = '" . (int)$_GET['id_poz'] . "'";
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
                  <button type="button" class="przyciskNon" onclick="cofnij('reklamacje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button> 
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