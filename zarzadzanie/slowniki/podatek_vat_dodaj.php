<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        if ($_POST["domyslny"] == '1') {
            $pola = array(array('tax_default','0'));
            $db->update_query('tax_rates' , $pola);	        
        }
        //    
        $pola = array(
                array('tax_rate',(float)$_POST["wartosc"]),
                array('tax_description',$filtr->process($_POST["opis"])),
                array('tax_short_description',$filtr->process($_POST["opis_skrocony"])),
                array('tax_default',(int)$_POST["domyslny"]),
                array('sort_order',(int)$_POST["kolejnosc"]),
                );
        //			
        $db->insert_query('tax_rates' , $pola);	
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('podatek_vat.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('podatek_vat.php');
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
                kolejnosc: {
                  required: true,
                  range: [0, 100]
                },
                wartosc: {
                  required: true,
                  range: [0, 100]
                },
                opis: {
                  required: true
                },
                opis_skrocony: {
                  required: true
                }                 
              },
              messages: {
                kolejnosc: {
                  required: "Pole jest wymagane."
                },
                wartosc: {
                  required: "Pole jest wymagane."
                },
                opis: {
                  required: "Pole jest wymagane."
                },
                opis_skrocony: {
                  required: "Pole jest wymagane."
                }                 
              }
            });
          });
          </script>         

          <form action="slowniki/podatek_vat_dodaj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />

                <p>
                  <label class="required" for="wartosc">Wartość w %:</label>
                  <input type="text" name="wartosc" id="wartosc" value="" size="5" class="kropka" /><em class="TipIkona"><b>liczba z zakresu 0 do 100</b></em>
                </p>
                
                <p>
                  <label class="required" for="kolejnosc">Kolejność:</label>
                  <input type="text" name="kolejnosc" id="kolejnosc" value="" size="5" /><em class="TipIkona"><b>liczba z zakresu 0 do 100</b></em>
                </p>

                <p>
                  <label class="required" for="opis">Opis:</label>
                  <input type="text" name="opis" id="opis" value="" size="15" />
                </p>   
                
                <p>
                  <label class="required" for="opis_skrocony">Opis skrócony:</label>
                  <input type="text" name="opis_skrocony" id="opis_skrocony" value="" size="15" />
                </p>

                <p>
                  <label>Czy podatek jest domyślnym:</label>
                  <input type="radio" value="0" name="domyslny" id="domyslny_nie" checked="checked" /><label class="OpisFor" for="domyslny_nie">nie</label>
                  <input type="radio" value="1" name="domyslny" id="domyslny_tak" /><label class="OpisFor" for="domyslny_tak">tak</label>                       
                </p>                 

                </div>
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('podatek_vat','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}