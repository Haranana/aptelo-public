<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $pola = array(array('products_viewed','0'));
        $sql = $db->update_query('products_description' , $pola);      
        //
        unset($pola);      
        //
        Funkcje::PrzekierowanieURL('wyswietlenia_produktow.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="statystyki/wyswietlenia_produktow_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <p>
                      Czy skasować wszystkie statystyki wyświetleń produktów ?                    
                    </p>   
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('wyswietlenia_produktow','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','statystyki');">Powrót</button>    
                </div>

          </div>  
          
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}