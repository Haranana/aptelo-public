<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola[] = array('orders_black_list',1);
        $db->update_query('orders', $pola, "orders_id = '" . (int)$_POST["id"] . "'"); 
        unset($pola);
        //			
        Funkcje::PrzekierowanieURL('zamowienia.php?id_poz=' . (int)$_POST["id"] . '');

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie informacji</div>
    <div id="cont">
          
        <form action="sprzedaz/zamowienia_czarna_lista.php" method="post" class="cmxform">          

        <div class="poleForm">
          <div class="naglowek">Usuwanie informacji</div>
          
          <?php
          
          if ( !isset($_GET['id_poz']) ) {
               $_GET['id_poz'] = 0;
          }    
          
          $zapytanie = "select * from orders where orders_id = '" . (int)$_GET['id_poz'] . "'";
          $sql = $db->open_query($zapytanie);
          
          if ((int)$db->ile_rekordow($sql) > 0) {
          
              $info = $sql->fetch_assoc();
              ?>            
          
              <div class="pozycja_edytowana">
              
                  <input type="hidden" name="akcja" value="zapisz" />
              
                  <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />

                  <p>
                    Czy usunąć status zamówienia jako "Czarna lista" ?
                  </p>   
                    
              </div>

              <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Usuń status" />
                <button type="button" class="przyciskNon" onclick="cofnij('zamowienia','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','sprzedaz');">Powrót</button> 
              </div>

          <?php
          } else {
          
              echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
          
          }
          $db->close_query($sql);
          unset($zapytanie, $info);            
          ?>

        </div>                      
        </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}