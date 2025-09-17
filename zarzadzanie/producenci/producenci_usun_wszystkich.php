<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $zapytanie_producenci = "select manufacturers_id from manufacturers";
        $sqlk = $db->open_query($zapytanie_producenci);                
        //
        while ($infk = $sqlk->fetch_assoc()) {

            // szuka produktow przypisanych do danego producenta
            if ( isset($_POST['produkty']) && (int)$_POST['produkty'] == 1 ) {
                //
                $zapytanie = "select products_id from products where manufacturers_id = '" . (int)$infk['manufacturers_id'] . "'";
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
                
                $sql = $db->update_query('products', $pola, " manufacturers_id = '" . (int)$infk['manufacturers_id'] . "'");
                //
            }

            $db->delete_query('location' , " manufacturers_id = '" . (int)$infk['manufacturers_id'] . "'"); 
            
        }
        //
        $db->close_query($sqlk);
        unset($zapytanie_producenci); 
        //
          
        $db->truncate_query('manufacturers');  
        $db->truncate_query('manufacturers_info');
        $db->truncate_query('discount_manufacturers');
        $db->truncate_query('discount_categories_manufacturers');
        
        Funkcje::PrzekierowanieURL('producenci.php');
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
        <form action="producenci/producenci_usun_wszystkich.php" method="post" id="producenciForm" class="cmxform">              

        <div class="poleForm">
          <div class="naglowek">Usuwanie danych</div>
          
            <div class="pozycja_edytowana">
            
                <input type="hidden" name="akcja" value="zapisz" />
            
                <p>
                    Czy skasować <b>wszystkich</b> producentów ?                   
                </p>  

                <script>
                $(document).ready(function() {
                    $('#tryb_usun').click(function() {
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
                    <br /><input type="checkbox" name="produkty" id="tryb_usun" value="1" /> <label class="OpisFor" for="tryb_usun">usuń całkowicie z bazy produkty przypisane do producentów</label>
                </p>
                
                <p id="usun_zdjecia" style="display:none">
                    <input type="checkbox" name="produkty_serwer" id="tryb_usun_serwer" value="1" /> <label class="OpisFor" for="tryb_usun_serwer">usuń także zdjęcia produktów z serwera</label>
                </p>                    
                             
                <div class="ostrzezenie" style="margin:10px">Operacja usunięcia jest nieodracalna ! Danych po usunięciu nie będzie można przywrócić !</div>

            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Usuń dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('producenci','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>');">Powrót</button>
            </div>

        </div>  
        
        </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}