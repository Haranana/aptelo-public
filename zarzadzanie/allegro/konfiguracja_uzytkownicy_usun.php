<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('allegro_users' , " allegro_user_id = '".(int)$_POST["id"]."'");  

      Funkcje::PrzekierowanieURL('konfiguracja_uzytkownicy.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="allegro/konfiguracja_uzytkownicy_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from allegro_users where allegro_user_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $zapytanie_user = "select * from allegro_auctions where auction_seller = '" . (int)$_GET['id_poz'] . "'";
                $sql_user = $db->open_query($zapytanie_user);

                if ((int)$db->ile_rekordow($sql_user) > 0) {
                    ?>
                    <div class="pozycja_edytowana">
                    
                        <p>
                            <span class="ostrzezenie">
                              Użytkownik ma wystawione aukcje - nie można usunąc tej pozycji
                            </span>
                        </p>
                     
                    </div>

                    <div class="przyciski_dolne">
                      <button type="button" class="przyciskNon" onclick="cofnij('konfiguracja_uzytkownicy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
                    </div>

                    <?php
                } else {
                    ?>

                    <div class="pozycja_edytowana">
                    
                        <input type="hidden" name="akcja" value="zapisz" />
                    
                        <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                        
                        <p>
                          Czy skasować pozycje ?
                        </p>   
                     
                    </div>

                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Usuń dane" />
                      <button type="button" class="przyciskNon" onclick="cofnij('konfiguracja_uzytkownicy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
                    </div>
                    <?php
                }

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