<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_POST['akcja_dolna']) && isset($_POST['opcja']) && count($_POST['opcja']) > 0 ) {

        if ( isset($_POST['akcja']) && $_POST['akcja'] == 'status' ) {

            foreach ( $_POST['opcja'] as $klucz => $id_aukcji_allegro ) {
              
                $pola = array(array('archiwum_allegro',0));  
                $db->update_query('allegro_auctions' , $pola, " allegro_id = '" . $id_aukcji_allegro . "'");              

            }

            Funkcje::PrzekierowanieURL('allegro_aukcje.php');

        }
        
        // wczytanie naglowka HTML
        include('naglowek.inc.php');
        ?>
        
        <div id="naglowek_cont">Zmiana statusu aukcji Allegro z archiwalnej na trwającą</div>
        
        <div id="cont">
              
            <form action="allegro/allegro_aukcje_akcja_status.php" method="post" class="cmxform">          

            <div class="poleForm">

              <div class="naglowek">Zmiana statusu aukcji</div>
              
              <div class="pozycja_edytowana">

                  <input type="hidden" name="akcja" value="status" />
                  <input type="hidden" name="akcja_dolna" value="status" />                      
              
                  <p>
                    Czy zmienić status poniższych aukcji na trwające ?
                  </p> 

                  <p class="ListaAukcji">
                    <?php
                    $idAukcji = implode(',', (array)$_POST['opcja']);

                    $zapytanie = "SELECT * FROM allegro_auctions WHERE allegro_id IN (" . $idAukcji . ")";
                    $sql = $db->open_query($zapytanie);
                    
                    $zmiana = false;
                    
                    while ( $info = $sql->fetch_assoc() ) {

                        if ( $info['archiwum_allegro'] == '1' ) {
                          
                            $link = '';
                            if ( Allegro::SerwerAllegro() == 'nie' ) {
                               $link = 'http://allegro.pl/i' . $info['auction_id'] . '.html';
                            } else {
                               $link = 'http://allegro.pl.allegrosandbox.pl/i' . $info['auction_id'] . '.html';
                            }                          

                            echo '<input type="hidden" name="opcja[]" value="'.$info['auction_id'].'" />';
                            
                            echo '<a href="' . $link . '" target="_blank">' . $info['auction_id'] . '</a> - ' . $info['products_name'] . '<br />';
                            
                            $zmiana = true;
                            
                            unset($link);
                            
                        }
                        
                    }
                    
                    $db->close_query($sql);
                    unset($zapytanie, $idAukcji);                          
                    ?>
                  </p> 
                  
                  <?php
                  if ( $zmiana == false ) {
                    
                      echo '<span class="ostrzezenie" style="margin:5px 0px 5px 9px">Nie znaleziono aukcji do zmiany statusu</span>';
                    
                  }
                  ?>

              </div>

              <div class="przyciski_dolne">
                
                <?php if ( $zmiana == true ) { ?>
                
                <input type="submit" class="przyciskNon" value="Zmień status" />
                
                <?php } ?>
                
                <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
                
              </div>
              
              <?php
              unset($zmiana);
              ?>

            </div>

            </form>

        </div>    
        
        <?php
        include('stopka.inc.php');

    } else {
    
        Funkcje::PrzekierowanieURL('allegro_aukcje.php');
        
    }
    
}
?>