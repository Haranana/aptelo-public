<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array(
                array('newsletters_group_name',$filtr->process($_POST['nazwa'])),
                array('newsletters_group_title',$filtr->process($_POST['opis'])));
                
        //			
        $db->update_query('newsletters_group' , $pola, " newsletters_group_id = '".(int)$_POST["id"]."'");	
        unset($pola);
        
        //
        Funkcje::PrzekierowanieURL('newsletter_grupy.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#newsletterForm").validate({
              rules: {
                nazwa: {
                  required: true
                }                
              },
              messages: {
                nazwa: {
                  required: "Pole jest wymagane."
                }              
              }
            });
          });        
          </script>         

          <form action="newsletter/newsletter_grupy_edytuj.php" method="post" id="newsletterForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from newsletters_group where newsletters_group_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">
                
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                        <label class="required" for="nazwa">Nazwa grupy:</label>
                        <input type="text" name="nazwa" id="nazwa" size="40" value="<?php echo $info['newsletters_group_name']; ?>" />
                    </p>
                    
                    <p>
                        <label for="opis">Opis grupy:</label>
                        <textarea name="opis" id="opis" cols="60" rows="5"><?php echo $info['newsletters_group_title']; ?></textarea>
                    </p>                    

                    </div>

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('newsletter_grupy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','newsletter');">Powrót</button>      
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
