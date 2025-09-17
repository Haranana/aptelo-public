<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('customers_groups' , " customers_groups_id = '".(int)$_POST["id"]."'");
        $db->delete_query('discount_manufacturers' , " discount_groups_id = '".(int)$_POST["id"]."'");
        $db->delete_query('discount_categories' , " discount_groups_id = '".(int)$_POST["id"]."'"); 
        $db->delete_query('discount_categories_manufacturers' , " discount_groups_id = '".(int)$_POST["id"]."'");
        $db->delete_query('discount_products' , " discount_groups_id = '".(int)$_POST["id"]."'"); 
        
        // klienci
        $pola = array(array('customers_groups_id','1'));
        $sql = $db->update_query('customers' , $pola, " customers_groups_id = '".(int)$_POST["id"]."'"); 
        
        $usuniecia = array( array('products_id', 'customers_group_id', 'products'),
                            array('products_id', 'not_customers_group_id', 'products'),
                            array('coupons_id', 'coupons_customers_groups_id', 'coupons'),
                            array('id_form', 'form_customers_group_id', 'form'),
                            array('id_gallery', 'gallery_customers_group_id', 'gallery'),
                            array('newsdesk_id', 'newsdesk_customers_group_id', 'newsdesk'),
                            array('pages_id', 'pages_customers_group_id', 'pages'),
                            array('id_gift', 'customers_group_id', 'products_gift'),
                            array('discount_id', 'discount_groups_id', 'discount_manufacturers'),
                            array('discount_id', 'discount_groups_id', 'discount_products'),
                            array('discount_id', 'discount_groups_id', 'discount_categories'),
                            array('discount_id', 'discount_groups_id', 'discount_categories_manufacturers')
                            );
        
        // usuniecia tam gdzie są grupy klientow do wyboru
        
        foreach ( $usuniecia as $usun ) {
        
            $zapytanie = "select " . $usun[0] . ", " . $usun[1] . " from " . $usun[2] . " where find_in_set(" . (int)$_POST["id"] . ", " . $usun[1] . ")";
            $sql = $db->open_query($zapytanie);       

            while ( $info = $sql->fetch_assoc() ) {
                //
                $grupy = explode(',', (string)$info[$usun[1]]);
                foreach ( $grupy as $klucz => $grupa ) {
                    if ( $grupa == (int)$_POST["id"] ) {
                         unset( $grupy[$klucz] );
                    }
                }
                //
                // jezeli nie ma grup to wstawi jako domyslnie 0
                if ( count($grupy) == 0 ) {
                     $grupy[] = 0;
                }
                //
                if ( $usun[2] == 'discount_manufacturers' || $usun[2] == 'discount_products' || $usun[2] == 'discount_categories' || $usun[2] == 'discount_categories_manufacturers' ) {
                     //
                     $ciag = str_replace(',,', ',', ',' . implode(',', (array)$grupy) . ',');
                     if ( $info[$usun[1]] == ',' . (int)$_POST["id"] . ',' ) {                
                          $db->delete_query($usun[2] , " " . $usun[1] . " = '," . (int)$_POST["id"] . ",'");
                     } else {
                          $pola = array(array($usun[1], $ciag));                          
                          $db->update_query($usun[2] , $pola, " " . $usun[0] . " = '" . $info[$usun[0]] . "'"); 
                          unset($pola);                          
                     }
                     unset($ciag);
                } else {
                     $pola = array(array($usun[1], implode(',', (array)$grupy)));
                     $db->update_query($usun[2] , $pola, " " . $usun[0] . " = '" . $info[$usun[0]] . "'"); 
                     unset($pola);
                }

                unset($grupy);
                //
            }
            
            $db->close_query($sql);
            unset($zapytanie);
            
        }
        
        unset($usuniecia);
        
        // dla wysylek
        $zapytanie = "select id, wartosc from modules_shipping_params where kod = 'WYSYLKA_GRUPA_KLIENTOW'";
        $sql = $db->open_query($zapytanie);       

        while ( $info = $sql->fetch_assoc() ) {
            //
            $grupy = explode(';', (string)$info['wartosc']);
            foreach ( $grupy as $klucz => $grupa ) {
                if ( $grupa == (int)$_POST["id"] ) {
                     unset( $grupy[$klucz] );
                }
            }
            //
            $pola = array(array('wartosc', implode(';', (array)$grupy)));
            $db->update_query('modules_shipping_params' , $pola, " id = '" . $info['id'] . "'"); 
            unset($pola, $grupy);
            //
        }
        
        $db->close_query($sql);
        unset($zapytanie);     

        $zapytanie = "select id, wartosc from modules_shipping_params where kod = 'WYSYLKA_GRUPA_KLIENTOW_WYLACZENIE'";
        $sql = $db->open_query($zapytanie);       

        while ( $info = $sql->fetch_assoc() ) {
            //
            $grupy = explode(';', (string)$info['wartosc']);
            foreach ( $grupy as $klucz => $grupa ) {
                if ( $grupa == (int)$_POST["id"] ) {
                     unset( $grupy[$klucz] );
                }
            }
            //
            $pola = array(array('wartosc', implode(';', (array)$grupy)));
            $db->update_query('modules_shipping_params' , $pola, " id = '" . $info['id'] . "'"); 
            unset($pola, $grupy);
            //
        }
        
        $db->close_query($sql);
        unset($zapytanie);        

        // newsletter
        $pola = array(array('customers_group_id','0'));
        $sql = $db->update_query('newsletters' , $pola, " customers_group_id = '".(int)$_POST["id"]."'");         
        
        Funkcje::PrzekierowanieURL('grupy_klienci.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="klienci/grupy_klienci_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) || (int)$_GET['id_poz'] == 1 ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from customers_groups where customers_groups_id = '" . (int)$_GET['id_poz'] . "'";
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
                  <button type="button" class="przyciskNon" onclick="cofnij('grupy_klienci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','klienci');">Powrót</button> 
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