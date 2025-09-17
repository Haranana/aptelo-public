<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $zapytanie_kategorie = "select categories_id from categories";
        $sqlk = $db->open_query($zapytanie_kategorie);                
        //
        while ($infk = $sqlk->fetch_assoc()) {

            $db->delete_query('products_accesories', "pacc_products_id_master = '" . (int)$infk['categories_id'] . "' and pacc_type = 'kategoria'");
            
            // szuka produktow przypisanych do danej kategorii
            if ( isset($_POST['produkty']) && (int)$_POST['produkty'] == 1 ) {
                //
                $zapytanie = "select products_id from products_to_categories where categories_id = '" . (int)$infk['categories_id'] . "'";
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
            } 
            
            // funkcja usuwa rowniez wpis w gornym i dolnym menu i stopkach      
            Funkcje::UsuwanieWygladu(array('prodkategorie','kategproduktow'),(int)$infk['categories_id']);  
            
            $db->delete_query('location', "categories_id = '".(int)$infk['categories_id']."'"); 
            
        }
        //
        $db->close_query($sqlk);
        unset($zapytanie_kategorie); 
        //
          
        $db->truncate_query('categories');  
        $db->truncate_query('categories_description');
        $db->truncate_query('products_to_categories');
        $db->truncate_query('discount_categories');
        $db->truncate_query('discount_categories_manufacturers');
        
        Funkcje::PrzekierowanieURL('kategorie.php');
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
        <form action="kategorie/kategorie_usun_wszystkie.php" method="post" id="kategorieForm" class="cmxform">          

        <div class="poleForm">
          <div class="naglowek">Usuwanie danych</div>
          
            <div class="pozycja_edytowana">
            
                <input type="hidden" name="akcja" value="zapisz" />
            
                <p>
                    Czy skasować <b>wszystkie</b> kategorie ?                     
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
                    <br /><input type="checkbox" name="produkty" id="tryb_usun" value="1" /> <label class="OpisFor" for="tryb_usun">usuń całkowicie z bazy produkty przypisane do kategorii</label>
                </p>
                
                <p id="usun_zdjecia" style="display:none">
                    <input type="checkbox" name="produkty_serwer" id="tryb_usun_serwer" value="1" /> <label class="OpisFor" for="tryb_usun_serwer">usuń także zdjęcia produktów z serwera</label>
                </p>                    
                             
                <div class="ostrzezenie" style="margin:10px">Operacja usunięcia jest nieodracalna ! Danych po usunięciu nie będzie można przywrócić !</div>

            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Usuń dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('kategorie','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>');">Powrót</button>
            </div>

        </div>  
        
        </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}