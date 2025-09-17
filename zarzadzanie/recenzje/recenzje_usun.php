<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $id_recenzji = (int)$_POST['id'];
    
        if ( isset($_POST['odpowiedz']) && (int)$_POST['odpowiedz'] == 1 ) {
          
            $pola = array(array('comments_answers',''));
            $db->update_query('reviews', $pola, " reviews_id = '".$id_recenzji."'");
            unset($pola);            
          
        } else {
          
            // sprawdzi czy nie ma zdjecia
            $zapytanie = "select distinct reviews_image from reviews where reviews_id = '".$id_recenzji."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
              
                $info = $sql->fetch_assoc(); 
    
                if ( !empty($info['reviews_image']) ) {
                     
                     if ( file_exists('../grafiki_inne/' . $info['reviews_image']) ) {
                          unlink('../grafiki_inne/' . $info['reviews_image']);
                     }
                     
                }
              
            }
            
            $db->close_query($sql);
          
            // kasuje rekordy w tablicy
            $db->delete_query('reviews', " reviews_id = '".$id_recenzji."'");     
            $db->delete_query('reviews_description', " reviews_id = '".$id_recenzji."'");  
            
        }
        
        if ( isset($_POST['zakladka']) && isset($_POST['produkt']) ) {
        
             Funkcje::PrzekierowanieURL('/zarzadzanie/produkty/produkty_edytuj.php?id_poz=' . (int)$_POST['produkt'] . '&zakladka=' . (int)$_POST['zakladka']);
             
        } else {
          
             Funkcje::PrzekierowanieURL('recenzje.php');
             
        }
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="recenzje/recenzje_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from reviews where reviews_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <?php if ( isset($_GET['zakladka']) ) { ?>
                    
                    <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                    
                    <?php } ?>
                    
                    <?php if ( isset($_GET['produkt']) ) { ?>
                    
                    <input type="hidden" name="produkt" value="<?php echo (int)$_GET['produkt']; ?>" />
                    
                    <?php } ?>                    
                    
                    <?php if ( isset($_GET['odpowiedz']) ) { ?>
                    
                    <input type="hidden" name="odpowiedz" value="1" />
                    
                    <p>
                      Czy skasować odpowiedź ?                    
                    </p>   
                    
                    <?php } else { ?>

                    <p>
                      Czy skasować recenzję ?                    
                    </p>                       
                    
                    <?php } ?>
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <?php if ( isset($_GET['zakladka']) && isset($_GET['produkt']) ) { ?>
                  <button type="button" class="przyciskNon" onclick="cofnij('produkty_edytuj','?id_poz=<?php echo (int)$_GET['produkt']; ?>&zakladka=<?php echo (int)$_GET['zakladka']; ?>','produkty');">Powrót</button>    
                  <?php } else { ?>
                  <button type="button" class="przyciskNon" onclick="cofnij('recenzje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
                  <?php } ?>
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