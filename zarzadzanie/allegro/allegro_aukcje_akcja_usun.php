<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_POST['akcja_dolna']) && isset($_POST['opcja']) && count($_POST['opcja']) > 0 ) {

        if ( isset($_POST['akcja']) && $_POST['akcja'] == 'usun' ) {

            foreach ( $_POST['opcja'] as $klucz => $id_aukcji_allegro ) {
            
                $db->delete_query('allegro_auctions' , " auction_id = '".$id_aukcji_allegro."'");  
                $db->delete_query('allegro_auctions_sold' , " auction_id = '".$id_aukcji_allegro."'");  
                $db->delete_query('allegro_transactions' , " auction_id = '".$id_aukcji_allegro."'"); 
                
            }

            Funkcje::PrzekierowanieURL('allegro_aukcje.php');

        }
        
        // wczytanie naglowka HTML
        include('naglowek.inc.php');
        ?>
        
        <div id="naglowek_cont">Usuwanie aukcji Allegro</div>
        
        <div id="cont">
              
            <form action="allegro/allegro_aukcje_akcja_usun.php" method="post" class="cmxform">          

            <div class="poleForm">

              <div class="naglowek">Usuwanie aukcji</div>
              
              <div class="pozycja_edytowana">

                  <input type="hidden" name="akcja" value="usun" />
                  <input type="hidden" name="akcja_dolna" value="usun" />                      
              
                  <p>
                    Czy usunąć poniższe aukcje z bazy danych sklepu ?
                  </p> 

                  <p class="ListaAukcji">
                    <?php
                    $idAukcji = implode(',', (array)$_POST['opcja']);

                    $zapytanie = "SELECT * FROM allegro_auctions WHERE allegro_id IN (" . $idAukcji . ")";
                    $sql = $db->open_query($zapytanie);
                    
                    while ( $info = $sql->fetch_assoc() ) {
                    
                        echo '<input type="hidden" name="opcja[]" value="'.$info['auction_id'].'" />';
                        
                        echo '<b>' . $info['auction_id'] . '</b> - ' . $info['products_name'] . '<br />';
                        
                    }
                    
                    $db->close_query($sql);
                    unset($zapytanie, $idAukcji);                          
                    ?>
                  </p> 
                  
                  <span class="ostrzezenie" style="margin:12px 0px 5px 9px">Dane aukcji zostaną usunięte tylko z bazy sklepu - aukcje nie zostaną usunięte bezpośrednio w Allegro</span>                  

              </div>

              <div class="przyciski_dolne">
                
                <input type="submit" class="przyciskNon" value="Usuń dane" />
                <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
                
              </div>

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