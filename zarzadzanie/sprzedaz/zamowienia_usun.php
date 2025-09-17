<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('orders' , " orders_id = '".(int)$_POST["id"]."'");  
        $db->delete_query('orders_status_history' , " orders_id = '".(int)$_POST["id"]."'");  
        $db->delete_query('orders_total' , " orders_id = '".(int)$_POST["id"]."'");  

        if ( isset($_POST['magazyn']) && $_POST['magazyn'] == '1' ) {

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
                        
                        $db->close_query($sql_cechy);
                        
                    }
                  
                }
              
            }
          
        }

        $db->delete_query('orders_products' , " orders_id = '".(int)$_POST["id"]."'");  
        $db->delete_query('orders_products_set' , " orders_id = '".(int)$_POST["id"]."'");  
        $db->delete_query('orders_products_attributes' , " orders_id = '".(int)$_POST["id"]."'");  
        $db->delete_query('orders_to_extra_fields' , " orders_id = '".(int)$_POST["id"]."'");  
        $db->delete_query('orders_shipping' , " orders_id = '".(int)$_POST["id"]."'");  
        $db->delete_query('orders_file_shopping' , " orders_id = '".(int)$_POST["id"]."'");  

        $db->delete_query('invoices' , " orders_id = '".(int)$_POST["id"]."'");  
        $db->delete_query('invoices_products' , " orders_id = '".(int)$_POST["id"]."'");  
        $db->delete_query('invoices_total' , " orders_id = '".(int)$_POST["id"]."'");  

        $pola = array(
                array('orders_id','0'),
        );
        $db->update_query('allegro_transactions' , $pola, " orders_id = '".(int)$_POST["id"]."'");	
        unset($pola);

        //
        if ( isset($_POST['klient_id']) && $_POST['klient_id'] != '' ) {
          Funkcje::PrzekierowanieURL('zamowienia.php?klient_id='.$_POST['klient_id'].'');
        } else {
          Funkcje::PrzekierowanieURL('zamowienia.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
        <form action="sprzedaz/zamowienia_usun.php" method="post" class="cmxform">          

        <div class="poleForm">
          <div class="naglowek">Usuwanie danych</div>
          
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
                  <?php if ( isset($_GET['klient_id']) && $_GET['klient_id'] != '' ) { ?>
                    <input type="hidden" name="klient_id" value="<?php echo (int)$_GET['klient_id']; ?>" />
                  <?php } ?>

                  <p>
                    Czy skasować pozycje ?
                  </p>   
               
                  <p style="margin-left:-15px">
                    <label>Aktualizuj stany magazynowe:</label>
                    <input type="radio" value="1" name="magazyn" checked="checked" id="magazyn_tak" /> <label class="OpisFor" for="magazyn_tak">tak<em class="TipIkona"><b>Czy po usunięciu produktu zamówienia stan magazynowy ?</b></em></label>
                    <input type="radio" value="0" name="magazyn" id="magazyn_nie" /> <label class="OpisFor" for="magazyn_nie">nie<em class="TipIkona"><b>Czy po usunięciu zamówienia przywrócić stan magazynowy ?</b></em></label>
                  </p>                     
              </div>

              <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Usuń dane" />
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