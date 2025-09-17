<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('products_extra_fields' , " products_extra_fields_id = '".(int)$_POST["id"]."'");  
        $db->delete_query('products_to_products_extra_fields' , " products_extra_fields_id = '".(int)$_POST["id"]."'");  
        $db->delete_query('products_extra_fields_book' , " products_extra_fields_id = '".(int)$_POST["id"]."'");  
        //

        // usuniecie pol z ustawien dla porownywarek
        $zapytanie = "SELECT * FROM comparisons";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $tablica = array();
            $tablica_pol = array();
            $lista_pol = '';

            if ( $info['comparisons_extra_fields'] != '' ) {

                $tablica = explode(',', (string)$info['comparisons_extra_fields']);

                for ($i=0; $i< count($tablica); $i++) {
                     $podtablica = explode(":", (string)$tablica[$i]);
                     $tablica_pol[$podtablica[0]] = $podtablica[1];
                }

                if ( isset($tablica_pol[$_POST["id"]]) ) {
                    unset($tablica_pol[$_POST["id"]]);
                }

                foreach ( $tablica_pol as $klucz => $wartosc) {
                        $pole = $klucz.':'.$wartosc;
                        $lista_pol .= $pole . ',';
                }
                $pola = array(
                        array('comparisons_extra_fields',substr((string)$lista_pol,0,-1)),
                );

                $db->update_query('comparisons', $pola, " comparisons_id = '".(int)$info["comparisons_id"]."'");
                unset($pola);

            }
        }

        Funkcje::PrzekierowanieURL('dodatkowe_pola.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="slowniki/dodatkowe_pola_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from products_extra_fields where products_extra_fields_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {        
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      Czy skasować pozycje ?
                      
                      <br /><br /><span class="ostrzezenie">Dodatkowe pole zostanie również usunięte wraz z wartościami z produktów w których zostało wypełnione.
                      
                      <?php
                      $sprawdzenie = "select distinct products_id from products_to_products_extra_fields where products_extra_fields_id = '" . (int)$_GET['id_poz'] . "'";
                      $wynik = $db->open_query($sprawdzenie);
                      if ((int)$db->ile_rekordow($wynik) > 0) {
                         echo '<span class="czerwony">To pole jest przypisane do '.(int)$db->ile_rekordow($wynik).' produktów. Po usunięciu dane zostaną utracone.</span><br />';
                      }  
                      unset($sprawdzenie, $wynik);
                      ?>                  
                      
                      </span>
                    </p>   
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('dodatkowe_pola','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button> 
                </div>

            <?php
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