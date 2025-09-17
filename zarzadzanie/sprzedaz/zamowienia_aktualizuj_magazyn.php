<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    //			
    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz' && isset($_POST['id']) && (int)$_POST['id'] > 0) {

        $zapytanie = "SELECT * FROM orders_products WHERE orders_id = '" . (int)$_POST['id']. "'";
                      
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) {
          
            while ( $info = $sql->fetch_assoc() ) {
              
                if ( (int)$info["products_id"] > 0 ) {

                    $ilosc_produktow = Sprzedaz::IloscProduktowAktualna($info["products_id"],$info["products_quantity"]);

                    $pola = array(array('products_quantity',(float)$ilosc_produktow));
                    $db->update_query('products' , $pola, " products_id = '".(int)$info["products_id"]."'");	
                    unset($pola);

                    $cechy = '';
                    $zapytanie_cechy = "SELECT 
                                        opa.products_options_id, opa.products_options_values_id 
                                        FROM orders_products_attributes opa
                                        LEFT JOIN products_options po ON opa.products_options_id = po.products_options_id AND po.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'
                                        WHERE orders_id = '" . (int)$_POST['id']. "' AND orders_products_id = '".(int)$info["orders_products_id"]."' 
                                        ORDER BY opa.products_options_id";
                                        
                    $sql_cechy = $db->open_query($zapytanie_cechy);

                    if ((int)$db->ile_rekordow($sql_cechy) > 0) {
                      
                        while ( $info_cechy = $sql_cechy->fetch_assoc() ) {
                          $cechy .= $info_cechy['products_options_id'].'-'.$info_cechy['products_options_values_id'].',';
                        }
                        $cechy = substr((string)$cechy, 0, -1);

                        $ilosc_produktow_cechy = Sprzedaz::IloscProduktowCechyAktualna($info["products_id"],$cechy,$info["products_quantity"]);

                        $pola = array(array('products_stock_quantity',(float)$ilosc_produktow_cechy));
                        $db->update_query('products_stock' , $pola, " products_id = '".(int)$info["products_id"]."' AND products_stock_attributes = '".$cechy."'");	
                        unset($pola);
                      
                    }
                    
                    $pola = array(array('status_update_products',1));
                    $db->update_query('orders' , $pola, " orders_id = '" . (int)$_POST['id'] . "'");	
                    unset($pola);
                    
                    $db->close_query($sql_cechy);
                    
                }
              
            }
          
        }

        if ( isset($_POST['klient_id']) && $_POST['klient_id'] != '' ) {
          Funkcje::PrzekierowanieURL('zamowienia.php?id_poz=' . (int)$_POST['id']);
        } else {
          Funkcje::PrzekierowanieURL('zamowienia.php');
        } 
        
    }
    
    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Aktualizowanie stanu magazynowego zamówienia po zmianie statusu</div>
    <div id="cont">
          
        <form action="sprzedaz/zamowienia_aktualizuj_magazyn.php" method="post" class="cmxform">          

        <div class="poleForm">
          <div class="naglowek">Aktualizacja danych</div>
          
          <?php
          
          if ( !isset($_GET['id_poz']) ) {
               $_GET['id_poz'] = 0;
          }    
          
          $zapytanie = "SELECT o.status_update_products FROM orders o LEFT JOIN orders_status os ON os.orders_status_id = o.orders_status WHERE o.orders_id = '" . (int)$_GET['id_poz'] . "' AND os.orders_status_type = '4'";
          $sql = $db->open_query($zapytanie);
          
          if ((int)$db->ile_rekordow($sql) > 0) {

              ?>            
          
              <div class="pozycja_edytowana">
              
                  <input type="hidden" name="akcja" value="zapisz" />
              
                  <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />

                  <p>
                    Czy zaktualizować stany magazynowe produktów zamówienia ?
                    <span class="maleInfo" style="margin-left:0px">Do stanu magazynowego produktów w sklepie zostaną dodane ilości produktów zakupione w przedmiotowym zamówieniu</span>
                  </p>   
                                  
              </div>

              <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Aktualizuj dane" />
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