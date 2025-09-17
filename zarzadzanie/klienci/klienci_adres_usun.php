<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        // sprawdzi czy adres jest ustawiony jako domyslny
        $zapytanie = "SELECT c.customers_default_shipping_address_id FROM customers c WHERE customers_id = " . (int)$_POST["id_poz"];
        $sql = $db->open_query($zapytanie);
        //
        $info = $sql->fetch_assoc();
        //
        if ( (int)$info['customers_default_shipping_address_id'] == (int)$_POST["id"] ) {
              //
              $pola = array(array('customers_default_shipping_address_id','0'));
              $db->update_query('customers', $pola, "customers_id = '" . (int)$_POST["id_poz"] . "'");
              unset($pola);
              //        
        }
        //
        $db->close_query($sql);
        unset($zapytanie, $info);        
        //
        $db->delete_query('address_book' , " address_book_id = '" . (int)$_POST["id"] . "'");  
        //
        Funkcje::PrzekierowanieURL('klienci_edytuj.php?id_poz=' . (int)$_POST["id_poz"] . '&zakladka=1');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="klienci/klienci_adres_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            // sprawdzi czy adres jest ustawiony jako domyslny
            $zapytanie = "SELECT c.customers_default_address_id FROM customers c WHERE customers_id = " . (int)$_GET["id_poz"];
            $sql = $db->open_query($zapytanie);
            //
            $info = $sql->fetch_assoc();
            //
            if ( (int)$info['customers_default_address_id'] == (int)$_GET["id"] ) {
                  //
                  $_GET['id_poz'] = 0;
                  //        
            }       

            $db->close_query($sql);
            unset($zapytanie, $info);            
            
            $zapytanie = "select * from address_book where address_book_id = '" . (int)$_GET['id'] . "' and customers_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id']; ?>" />
                    <input type="hidden" name="id_poz" value="<?php echo (int)$_GET['id_poz']; ?>" />                    
                    
                    <p>
                      Czy skasować pozycje ?
                    </p>   
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('klienci_edytuj','<?php echo Funkcje::Zwroc_Wybrane_Get(array('zakladka','id_poz')); ?>','klienci');">Powrót</button> 
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