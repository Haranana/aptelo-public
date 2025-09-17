<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $id_edytowanej_pozycji = (int)$_POST['id'];
        //
        $pola = array(array('comments_answers',$filtr->process($_POST['komentarz'])));
                      
        $sql = $db->update_query('newsdesk_comments', $pola, 'newsdesk_comments_id = ' . (int)$id_edytowanej_pozycji);
        unset($pola);        

        Funkcje::PrzekierowanieURL('aktualnosci_komentarze.php?id_poz='.(int)$id_edytowanej_pozycji.(((int)$_POST['art_id'] > 0) ? '&art_id='.(int)$_POST['art_id'] : ''));
    }   

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Odpowiedź do komentarza o artykule</div>
    <div id="cont">

          <form action="aktualnosci/aktualnosci_komentarze_odpowiedz.php" method="post" id="aktualnosciForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Odpowiedź do komentarza o artykule</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct * from newsdesk_comments where newsdesk_comments_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                ?>            
            
                <div class="pozycja_edytowana">    
                
                    <input type="hidden" name="akcja" value="zapisz" />

                    <input type="hidden" name="id" value="<?php echo $info['newsdesk_comments_id']; ?>" />
                    
                    <input type="hidden" name="art_id" value="<?php echo ((isset($_GET['art_id'])) ? (int)$_GET['art_id'] : ''); ?>" />
                    
                    <div class="info_content">
                    
                    <p>
                      <b style="display:block; margin-bottom:8px">Treść komentarza klienta</b> 
                      <span style="color:#838383"><?php echo $info['comments']; ?></span>
                    </p>
                    
                    <br />

                    <p>
                        <label for="komentarz">Odpowiedź:</label> <br />
                        <textarea name="komentarz" id="komentarz" rows="10" cols="50" style="width:50%"><?php echo $info['comments_answers']; ?></textarea><em class="TipIkona"><b>Treść odpowiedzi - bez tagów HTML</b></em>
                    </p>

                    </div>
                    
                </div>
                
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('aktualnosci_komentarze','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','art_id')); ?>','aktualnosci');">Powrót</button>     
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