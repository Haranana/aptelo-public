<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        if ( $_SESSION['domyslny_uzytkownik_allegro'] == $_POST['sprzedawca'] ) {

            $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );

            $UUID = $AllegroRest->UUIDv4();

            $DaneDoWyslania = new stdClass();
            $DaneDoWyslania->publication = new stdClass();
            $DaneDoWyslania->offerCriteria = array();
            $DaneDoWyslania->offerCriteria['0'] = new stdClass();
            $DaneDoWyslania->offerCriteria['0']->offers = array();
            $DaneDoWyslania->offerCriteria['0']->offers['0'] = new stdClass();

            $DaneDoWyslania->publication->action = 'END';
            $DaneDoWyslania->offerCriteria['0']->offers['0']->id = $_POST['aukcja_id'];
            $DaneDoWyslania->offerCriteria['0']->type = "CONTAINS_OFFERS";

            $wynik = $AllegroRest->commandPut('sale/offer-publication-commands/'.$UUID, $DaneDoWyslania );

            //

            if ( is_object($wynik) && isset($wynik->id) ) {
          
              $pola = array(
                    array('auction_uuid',$wynik->id)
              );
                    
              $db->update_query('allegro_auctions' , $pola, " allegro_id = '".(int)$_POST["id"]."'");	
              unset($pola);

              Funkcje::PrzekierowanieURL('allegro_aukcje.php?id_poz='.(int)$_POST["id"].'');
           }

          
        }

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    if (isset($_POST["id"]) ) $_GET['id_poz'] = $_POST["id"];
    ?>
    
    <div id="naglowek_cont">Obsługa aukcji</div>
    <div id="cont">
    
          <form action="allegro/allegro_aukcja_zakoncz.php" method="post" class="cmxform" id="allegroForm" >          

          <div class="poleForm">
            <div class="naglowek">Zakończenie aukcji</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "SELECT * FROM allegro_auctions WHERE allegro_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();

                if ( $_SESSION['domyslny_uzytkownik_allegro'] == $info['auction_seller'] ) {
                    ?>            
            
                    <div class="pozycja_edytowana">
                    
                        <div class="info_content">
                    
                        <input type="hidden" name="akcja" value="zapisz" />
                    
                        <input type="hidden" name="aukcja_id" value="<?php echo $info['auction_id']; ?>" />
                        <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                        <input type="hidden" name="sprzedawca" value="<?php echo $info['auction_seller']; ?>" />
                        
                        <p>
                          Czy zakończyć aukcję numer <?php echo $info['auction_id']; ?> ?
                        </p>                    
                        
                        </div>

                    </div>

                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zakończ aukcję" />
                      <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
                    </div>

                    <?php
                } else {
            
                    echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
                }
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