<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('languages' , " languages_id = '".(int)$_POST["id"]."'"); 

        // dla language_id
        $tablice_jezykowe = array('categories_description',
                                  'complaints_status_description',
                                  'countries_description',
                                  'customers_points_status_description',
                                  'customers_searches',
                                  'customers_to_extra_fields',
                                  'email_templates_description',
                                  'email_text_description',
                                  'form_description',
                                  'form_field',
                                  'gallery_description',
                                  'gallery_image',
                                  'headertags',
                                  'headertags_default',
                                  'newsdesk_categories_description',
                                  'newsdesk_description',
                                  'newsletters',
                                  'orders_status_description',
                                  'pages_description',
                                  'poll_description',
                                  'poll_field',
                                  'products_availability_description',
                                  'products_description',
                                  'products_file',
                                  'products_film',
                                  'products_info',
                                  'products_jm_description',
                                  'products_link',
                                  'products_options',
                                  'products_options_values',
                                  'products_youtube',
                                  'products_shipping_time_description',
                                  'products_warranty_description',
                                  'products_condition_description',
                                  'theme_box_description',
                                  'theme_modules_description',
                                  'translate_value',
                                  );
                                  
        for ($x = 0, $c = count($tablice_jezykowe); $x < $c; $x++) {
            $db->delete_query($tablice_jezykowe[$x] , " language_id = '".$filtr->process($_POST["id"])."'"); 
        }
                                  
        // dla languages_id
        $tablice_jezykowe = array('banners',
                                  'customers_extra_fields_info',
                                  'manufacturers_info',
                                  'products_extra_fields',
                                  'reviews_description',
                                  'standard_complaints_comments_description',
                                  'standard_order_comments_description',
                                  );   

        for ($x = 0, $c = count($tablice_jezykowe); $x < $c; $x++) {
            $db->delete_query($tablice_jezykowe[$x] , " languages_id = '".(int)$_POST["id"]."'"); 
        }                                  
        
        //
        Funkcje::PrzekierowanieURL('jezyki.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="slowniki/jezyki_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) || (int)$_GET['id_poz'] == 1 ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from languages where languages_id = '" . (int)$_GET['id_poz'] . "'";
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
                  <button type="button" class="przyciskNon" onclick="cofnij('jezyki','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button> 
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