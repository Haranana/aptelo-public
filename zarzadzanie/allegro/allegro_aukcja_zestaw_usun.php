<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
  
    $wynik = '';
    
    if ( Funkcje::SprawdzAktywneAllegro() ) {
        
        if (isset($_POST['akcja']) && $_POST['akcja'] == 'usun') {
            //		
            $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );

            $PrzetwarzanaAukcja = $AllegroRest->commandDelete('sale/loyalty/promotions/' . $filtr->process($_POST['id_zestawu']));
            
            $wynik = '<div id="zaimportowano">Zestaw aukcji został usunięty</div>'; 
            //
            $db->delete_query('allegro_benefits_set' , " allegro_benefits_set_id_set = '" . $filtr->process($_POST['id_zestawu']) . "'");                        
            //
            $_GET['id_zestaw_usuniety'] = $filtr->process($_POST['id_zestawu']);
            //
        }        

        // wczytanie naglowka HTML
        include('naglowek.inc.php');
        ?>
        
        <div id="naglowek_cont">Usuwanie zestawu aukcji</div>
        <div id="cont">
              
        <form action="allegro/allegro_aukcja_zestaw_usun.php" id="rabatForm" method="post" class="cmxform">          

        <div class="poleForm">
          <div class="naglowek">Usuwanie danych</div>
          
          <?php if ( isset($_GET['id_zestaw']) ) { ?>
          
              <?php
              $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );
              
              $PrzetwarzanaAukcja = $AllegroRest->commandGet('sale/loyalty/promotions/' . $_GET['id_zestaw']);

              if ( !isset($PrzetwarzanaAukcja->errors) ) { ?>

                  <div class="pozycja_edytowana">
                  
                      <div class="info_content">
                  
                          <input type="hidden" name="akcja" value="usun" />

                          <input type="hidden" name="id_zestawu" value="<?php echo $_GET['id_zestaw']; ?>" />

                          <p>
                              Czy chcesz go usunąć zestaw aukcji ?
                          </p>

                      </div>
                      
                  </div>

                  <div class="przyciski_dolne">
                    <input type="submit" class="przyciskNon" value="Usuń zestaw" />
                    <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
                  </div>   

              <?php } else { ?>
              
                  <?php if ( isset($PrzetwarzanaAukcja->errors) ) {
                    
                      $wynik = '<div class="ostrzezenie" style="margin:15px 0px 10px 10px;display:block">Wystąpił błąd podczas usuwania zestawu !</div>';
                    
                  } ?>
              
                  <div class="pozycja_edytowana">
              
                      <?php echo $wynik; ?>

                  </div>
                  
                  <div class="przyciski_dolne">
                    <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
                  </div>                       
              
              <?php               
              } 
              unset($wynik, $PrzetwarzanaAukcja);
              ?>
                      
          <?php } else {
          
              if ( isset($_GET['id_zestaw_usuniety']) ) {
                
                  ?>
                  
                  <div class="pozycja_edytowana">
              
                      <?php echo $wynik; ?>

                  </div>
                  
                  <div class="przyciski_dolne">
                    <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
                  </div>                     
                  
                  <?php
                
              } else {
          
                  echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
                  
              }
          
          }             
          ?>

        </div>                      
        </form>

        </div>    
    
        <?php
        include('stopka.inc.php');

    } else {
    
      Funkcje::PrzekierowanieURL('index.php');
      
    }
      
}