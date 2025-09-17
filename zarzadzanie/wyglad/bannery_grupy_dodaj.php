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
                array('banners_group_code',$filtr->process($_POST['kod'])),
                array('banners_group_title',$filtr->process($_POST['opis'])));
             
        $sql = $db->insert_query('banners_group' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);

        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('bannery_grupy.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('bannery_grupy.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">
          
          <script type="text/javascript" src="javascript/jquery.bestupper.min.js"></script>        

          <script>
          $(document).ready(function() {
            $('.bestupper').bestupper();

            $("#wygladForm").validate({
              rules: {
                kod: {
                  required: true,
                  remote: "ajax/sprawdz_czy_zmienna_grupy_bannerow.php"
                },
                opis: {
                  required: true
                }                
              },
              messages: {
                kod: {
                  required: "Pole jest wymagane.",
                  remote: "Grupa o takiej nazwie już istnieje."
                },
                opis: {
                  required: "Pole jest wymagane."
                }                 
              }
            }); 
          });        

          function updateKey() {
              var key=$("#kod").val();
              key=key.replace(" ","_");
              $("#kod").val(key);
          }
          </script>     

          <form action="wyglad/bannery_grupy_dodaj.php" method="post" id="wygladForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />

                <p>
                    <label class="required" for="kod">Kod grupy:</label>
                    <input type="text" name="kod" id="kod" value="" size="40" class="bestupper" value="" onkeyup="updateKey();" /><em class="TipIkona"><b>Kod banneru jaki będzie używany w szablonach - nie może zawierać spacji i polskich znaków - musi być unikalny - np BANNERY_ANIMACJA</b></em>
                </p>
                
                <p>
                    <label class="required" for="opis">Opis grupy:</label>
                    <input type="text" name="opis" id="opis" value="" size="80" /><em class="TipIkona"><b>Opis będzie wyświetlany przy dodawaniu nowych bannerów</b></em>
                </p>

                </div>

            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('bannery_grupy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','wyglad');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
