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
             
        $sql = $db->insert_query('newsletters_group', $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);

        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('newsletter_grupy.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('newsletter_grupy.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
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

          <form action="newsletter/newsletter_grupy_dodaj.php" method="post" id="newsletterForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />

                <p>
                    <label class="required" for="nazwa">Nazwa grupy:</label>
                    <input type="text" name="nazwa" id="nazwa" value="" size="40" />
                </p>
                
                <p>
                    <label for="opis">Opis grupy:</label>
                    <textarea name="opis" id="opis" cols="60" rows="5"></textarea>
                </p>

                </div>

            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('newsletter_grupy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','newsletter');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
