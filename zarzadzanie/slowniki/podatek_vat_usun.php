<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('tax_rates' , " tax_rates_id = '".(int)$_POST["id"]."'");  
        //
        // usuwa wpis w produktach
        $pola = array(
                array('products_tax_class_id','')
        );        
        $db->update_query('products' , $pola, " products_tax_class_id = '".(int)$_POST["id"]."'");
        //
        Funkcje::PrzekierowanieURL('podatek_vat.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="slowniki/podatek_vat_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            // id ktorych nie mozna skasowac
            $wykluczoneId = array(1,2,3,4,5);
            
            if ( !isset($_GET['id_poz']) || in_array((string)$_GET['id_poz'], $wykluczoneId) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            unset($wykluczoneId);  
            
            $zapytanie = "select * from tax_rates where tax_rates_id = '" . (int)$_GET['id_poz'] . "' and tax_default = '0'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      Czy skasować pozycje ?
                    </p>   
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('podatek_vat','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button> 
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