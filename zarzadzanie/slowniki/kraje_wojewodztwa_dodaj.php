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
                array('zone_name',$filtr->process($_POST["nazwa"])),
                array('zone_code',$filtr->process($_POST["kod"])),
                array('zone_country_id',$filtr->process($_POST["kraj_id"])),
        );
        //	
        $db->insert_query('zones' , $pola);	
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('kraje_wojewodztwa.php?id_poz='.(int)$id_dodanej_pozycji.'&kraj_id='.(int)$_POST["kraj_id"]);
        } else {
            Funkcje::PrzekierowanieURL('kraje_wojewodztwa.php?kraj_id='.(int)$_POST["kraj_id"]);
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <script>
          $(document).ready(function() {
            $("#slownikForm").validate({
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

          <form action="slowniki/kraje_wojewodztwa_dodaj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <div>
                    <input type="hidden" name="akcja" value="zapisz" />
                    <input type="hidden" name="kraj_id" value="<?php echo ((isset($_GET["kraj_id"])) ? $_GET["kraj_id"] : '0'); ?>" />
                </div>
                
                <p>
                  <label class="required" for="nazwa">Nazwa:</label>
                  <input type="text" name="nazwa" size="53" value="" id="nazwa" />
                </p>

                <p>
                  <label for="kod">Kod:</label>
                  <input type="text" name="kod" size="5" value="" id="kod" />
                </p>
                
                </div>

            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('kraje_wojewodztwa','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','slowniki');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}