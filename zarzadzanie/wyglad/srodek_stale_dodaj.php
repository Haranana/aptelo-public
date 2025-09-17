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
                array('modul_status',$filtr->process($_POST['status'])),
                array('modul_file',$filtr->process($_POST['plik'])),
                array('modul_title',$filtr->process($_POST['nazwa'])),
                array('modul_description',$filtr->process($_POST['opis'])));
             
        $sql = $db->insert_query('theme_modules_fixed' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('srodek_stale.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('srodek_stale.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#modForm").validate({
              rules: {
                nazwa: {
                  required: true
                },
                plik: {
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

          <form action="wyglad/srodek_stale_dodaj.php" method="post" id="modForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />
    
                <p>
                    <label class="required" for="nazwa">Nazwa modułu:</label>
                    <input type="text" name="nazwa" size="45" value="" id="nazwa" />
                </p>
                
                <p>
                    <label for="opis">Opis modułu:</label>
                    <textarea name="opis" id="opis" rows="5" cols="70"></textarea><em class="TipIkona"><b>Opis co będzie wyświetlał moduł - informacja tylko dla administratora sklepu</b></em>
                </p> 

                <p>
                    <label class="required" for="plik">Nazwa pliku modułu:</label>
                    <input type="text" name="plik" id="plik" value="" size="40" /><em class="TipIkona"><b>Nazwa pliku definiującego wygląd modułu (pliki muszą znajdować się w katalogu /moduly_stale</b></em>
                </p>    

                <p>
                    <label>Czy moduł ma być włączony ?</label>
                    <input type="radio" value="1" name="status" id="wlaczony_tak" checked="checked" /><label class="OpisFor" for="wlaczony_tak">tak</label>
                    <input type="radio" value="0" name="status" id="wlaczony_nie" /><label class="OpisFor" for="wlaczony_nie">nie</label>
                </p> 

                </div>

            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('srodek_stale','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','wyglad');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
