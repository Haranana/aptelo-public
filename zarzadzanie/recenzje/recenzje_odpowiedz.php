<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array(array('comments_answers',$filtr->process($_POST['komentarz'])));
                      
        $sql = $db->update_query('reviews', $pola, ' reviews_id = ' . (int)$_POST['id_recenzji']);
        unset($pola);        

        if ( isset($_POST['zakladka']) && isset($_POST['produkt']) ) {
        
             Funkcje::PrzekierowanieURL('/zarzadzanie/produkty/produkty_edytuj.php?id_poz=' . (int)$_POST['produkt'] . '&zakladka=' . (int)$_POST['zakladka']);
             
        } else {
          
             Funkcje::PrzekierowanieURL('recenzje.php?id_poz='.(int)$_POST['id_recenzji']);
             
        }

    }   

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Komentarz do recenzji o produkcie</div>
    <div id="cont">

          <form action="recenzje/recenzje_odpowiedz.php" method="post" id="recenzjeForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Komentarz do recenzji o produkcie</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct * from reviews r, reviews_description rd where r.reviews_id = '" . (int)$_GET['id_poz'] . "' and r.reviews_id = rd.reviews_id";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                ?>            
            
                <div class="pozycja_edytowana">    
                
                    <input type="hidden" name="akcja" value="zapisz" />

                    <input type="hidden" name="id_recenzji" value="<?php echo $info['reviews_id']; ?>" />
                    
                    <?php if ( isset($_GET['zakladka']) ) { ?>
                    
                    <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                    
                    <?php } ?>
                    
                    <?php if ( isset($_GET['produkt']) ) { ?>
                    
                    <input type="hidden" name="produkt" value="<?php echo (int)$_GET['produkt']; ?>" />
                    
                    <?php } ?>   
                    
                    <div class="info_content">
                    
                    <p>
                      <b style="display:block; margin-bottom:8px">Treść recenzji klienta</b> 
                      <span style="color:#838383"><?php echo $info['reviews_text']; ?></span>
                    </p>
                    
                    <br />

                    <p>
                        <label for="komentarz">Odpowiedź:</label> <br />
                        <textarea name="komentarz" id="komentarz" rows="10" cols="50" style="width:50%"><?php echo $info['comments_answers']; ?></textarea><em class="TipIkona"><b>Treść opinii - bez tagów HTML</b></em>
                    </p>

                    </div>
                    
                </div>
                
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <?php if ( isset($_GET['zakladka']) && isset($_GET['produkt']) ) { ?>
                  <button type="button" class="przyciskNon" onclick="cofnij('produkty_edytuj','?id_poz=<?php echo (int)$_GET['produkt']; ?>&zakladka=<?php echo (int)$_GET['zakladka']; ?>','produkty');">Powrót</button>    
                  <?php } else { ?>
                  <button type="button" class="przyciskNon" onclick="cofnij('recenzje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>      
                  <?php } ?>
                </div>

            <?php 
            $db->close_query($sql);
            unset($info);

            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>                    
            
          </div>

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>