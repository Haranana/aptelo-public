<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $wynik = '';

    if ( Funkcje::SprawdzAktywneAllegro() ) {
      
      if ( $_GET['ilosc'] > 0 && (int)$_GET['ilosc'] > 0 ) {

          $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );

          $zapytanie = "SELECT * FROM allegro_auctions WHERE allegro_id = '".(int)$_GET["id_poz"]."'";
          $sql = $db->open_query($zapytanie);

          if ( $db->ile_rekordow($sql) > 0 ) {
          
            $info = $sql->fetch_assoc();
          
            $id_aukcji = floatval($info['auction_id']);

            $PrzetwarzanaAukcja = $AllegroRest->commandGet('sale/product-offers/'.$id_aukcji);
            
            if ( !isset($PrzetwarzanaAukcja->errors) || ( isset($PrzetwarzanaAukcja->errors) && count($PrzetwarzanaAukcja->errors) < 1 )  ) {

                $DaneDoAktualizacji = new stdClass();
                $DaneDoAktualizacji->stock = new stdClass();
                $DaneDoAktualizacji->stock->available = floor($_GET['ilosc']);

                $rezultat = $AllegroRest->commandPatch('sale/product-offers/'.$id_aukcji, $DaneDoAktualizacji);

                if ( isset($rezultat->stock) && $rezultat->stock->available && $rezultat->stock->available > 0) {
                
                  $pola = array(
                            array('auction_quantity',floor($_GET['ilosc'])),
                            array('products_quantity',floor($_GET['ilosc']))
                  );

                  $db->update_query('allegro_auctions' , $pola, " allegro_id = '".(int)$_GET["id_poz"]."'");
                  
                  unset($pola);
                  $wynik = '<div id="zaimportowano">Ilość przedmiotów na aukcji została zaktualizowana</div>';          
                  
                } else {
                
                  if ( isset($rezultat->errors) && count($rezultat->errors) > 0 ) {
                    $wynik = '<div class="ostrzezenie" style="margin:20px 20px 10px 20px">Wystąpił problem w Allegro :<br />';
                    foreach ( $rezultat->errors as $Error ) {

                        $wynik .= '<b>'.$Error->message . '</b><br />';

                    }
                    $wynik .= '</div>';
                  } else {
                      $wynik = '<div class="ostrzezenie" style="margin:10px">Aukcja o nr <b>' . $id_aukcji . '</b> nie została odnaleziona</div>';
                  }
                  
                }

            } else {

                    $wynik = '<div class="ostrzezenie" style="margin:20px 20px 10px 20px">Wystąpił problem w Allegro :<br />';
                    foreach ( $PrzetwarzanaAukcja->errors as $Blad ) {

                        $wynik .= '<b>'.$Blad->message . '</b><br />';

                    }
                    $wynik .= '</div>';

            }

            unset($info);
            
          }
          
          $db->close_query($sql);
          unset($zapytanie);      
          
          unset($AllegroRest);
      } else {

          $wynik = '<div class="ostrzezenie" style="margin:10px">Ilość produktów musi być większa niż 0</div>';
      }

    }
    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Obsługa aukcji</div>
    <div id="cont">

      <div class="poleForm">
      
        <div class="naglowek">Aktualizacja ilości przedmiotów</div>
        
        <div class="pozycja_edytowana">
          <?php
          if ( $wynik != '' ) {
            echo $wynik;
          }
          ?>
        </div>

        <div class="przyciski_dolne" id="przyciski" style="padding-left:0px;">
          <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
        </div>                    

      </div>
      
    </div>
    
    <?php
    include('stopka.inc.php');

}