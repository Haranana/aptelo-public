<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array(array('pp_description',$filtr->process($_POST['nazwa'])),
                      array('pp_image',$filtr->process($_POST['zdjecie'])),
                      array('pp_image_alt',$filtr->process($_POST['alt'])));
        
        $sql = $db->insert_query('pp_banners', $pola);
        unset($pola);        

        Funkcje::PrzekierowanieURL('bannery.php');

    }   

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="program_partnerski/bannery_dodaj.php" method="post" id="ppForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />

                <script>
                $(document).ready(function() {
                  $("#ppForm").validate({
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

                <p>
                  <label class="required" for="nazwa">Opis banneru:</label>
                  <textarea cols="62" rows="7" name="nazwa" id="nazwa"></textarea> 
                </p>                  

                <p>
                  <label for="foto">Ścieżka obrazka:</label>           
                  <input type="text" name="zdjecie" size="95" value="" class="obrazek" ondblclick="openFilePPBrowser('foto')" id="foto" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki plików</b></em>
                </p>      

                <p>
                    <label for="alt">Opis obrazka:</label>
                    <input type="text" name="alt" id="alt" value="" size="90" />
                </p>                

                </div>             
               
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('bannery','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','program_partnerski');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
