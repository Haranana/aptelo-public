<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('manufacturers' , " manufacturers_id = '".(int)$_POST["id"]."'");  
        $db->delete_query('manufacturers_info' , " manufacturers_id = '".(int)$_POST["id"]."'");
        //
        
        // szuka produktow przypisanych do danego producenta
        if ( isset($_POST['produkty']) && (int)$_POST['produkty'] == 1 ) {
            //
            $zapytanie = "select products_id from products where manufacturers_id = '" . (int)$_POST["id"] . "'";
            $sql = $db->open_query($zapytanie);                
            //
            while ($info = $sql->fetch_assoc()) {
                if ( isset($_POST['produkty_serwer']) && $_POST['produkty_serwer'] == 1 ) {
                     Produkty::SkasujProdukt($info['products_id'], true);  
                } else {
                     Produkty::SkasujProdukt($info['products_id']);  
                }
            }
            //
            $db->close_query($sql);
            unset($zapytanie); 
            //
        } else {        
            // czyszczenie id w produktach
            $pola = array(
                    array('manufacturers_id',''));
            
            $sql = $db->update_query('products' , $pola, " manufacturers_id = '".(int)$_POST["id"]."'");
            //
        }
        
        // czyszczenie w rabatach dla producentow
        // szuka czy kategoria nie jest uzywana w znizkach
        $zapytanie = "select discount_id, discount_manufacturers_id from discount_manufacturers";
        $sqld = $db->open_query($zapytanie);  
        //
        while ($info = $sqld->fetch_assoc()) {
            //
            if ( in_array((string)$_POST["id"], explode(',', (string)$info['discount_manufacturers_id']) ) ) {
                 //
                 $nowaTablica = explode(',', (string)$info['discount_manufacturers_id']);
                 foreach ( $nowaTablica as $id => $wartosc ) {
                    //
                    if ( $wartosc == (int)$_POST["id"] || $wartosc == '' ) {
                         unset( $nowaTablica[$id] );
                    }
                    //
                 }                     
                 //
                 $pola = array( array('discount_manufacturers_id', implode(',', (array)$nowaTablica)) );
                 $db->update_query('discount_manufacturers' , $pola, " discount_id = '".$info['discount_id']."'");
                 unset($pola);
                 //
            }
            //
        }
        //
        $db->close_query($sqld);
        unset($zapytanie); 
        //      
        
        $db->delete_query('location' , " manufacturers_id = '".(int)$_POST["id"]."'"); 

        Funkcje::PrzekierowanieURL('producenci.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="producenci/producenci_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from manufacturers where manufacturers_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      Czy skasować producenta ?                    
                    </p>   
                    
                    <?php
                    $zapytanie_ile = "select products_id from products where manufacturers_id = '" . (int)$_GET['id_poz'] . "'";
                    $sql_ile = $db->open_query($zapytanie_ile);
                    //
                    $ile_ma_produktow = (int)$db->ile_rekordow($sql_ile);
                    //
                    $db->close_query($sql_ile);
                    unset($zapytanie_ile);                       
                    //
                    ?>
                    
                    <?php if ( $ile_ma_produktow > 0 ) { ?>
                    
                    <script>
                    $(document).ready(function() {
                        $('#produkty').click(function() {
                           //
                           if ( $(this).prop('checked') == true ) {
                                $('#usun_zdjecia').slideDown();
                           } else {
                                $('#usun_zdjecia').slideUp();
                           }
                           //
                        });
                    });
                    </script>                     
                    
                    <p>
                        <br /><span class="ostrzezenie">Producent zawiera <b><?php echo $ile_ma_produktow; ?></b> produktów wciąż powiązanych z tym producentem - <span style="color:#ff0000">po usunięciu producenta dane tego producenta zostaną usunięte z produktów</span> !</span>
                    </p>
                    
                    <p>
                        <br /><input type="checkbox" name="produkty" value="1" id="produkty" /><label class="OpisFor" for="produkty">usuń całkowicie z bazy produkty przypisane do danego producenta</label>
                    </p> 

                    <p id="usun_zdjecia" style="display:none">
                        <input type="checkbox" name="produkty_serwer" id="tryb_usun_serwer" value="1" /> <label class="OpisFor" for="tryb_usun_serwer">usuń także zdjęcia produktów z serwera</label>
                    </p>                       
                    
                    <?php } ?>
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('producenci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
                </div>

            <?php
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