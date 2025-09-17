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
                      
        $sql = $db->update_query('reviews_shop', $pola, 'reviews_shop_id = ' . (int)$_POST['id_opinii']);
        unset($pola);        

        Funkcje::PrzekierowanieURL('opinie.php?id_poz='.(int)$_POST['id_opinii']);
    }   

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Komentarz do opinii o sklepie</div>
    <div id="cont">

          <form action="opinie/opinie_odpowiedz.php" method="post" id="opinieForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Komentarz do opinii o sklepie</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct * from reviews_shop where reviews_shop_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                ?>            
            
                <div class="pozycja_edytowana">    
                
                    <input type="hidden" name="akcja" value="zapisz" />

                    <input type="hidden" name="id_opinii" value="<?php echo $info['reviews_shop_id']; ?>" />
                    
                    <div class="info_content">
                    
                    <p>
                      <b style="display:block; margin-bottom:8px">Treść opinii klienta</b> 
                      <span style="color:#838383"><?php echo $info['comments']; ?></span>
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
                  <button type="button" class="przyciskNon" onclick="cofnij('opinie','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>     
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