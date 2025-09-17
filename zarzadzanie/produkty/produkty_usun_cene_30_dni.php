<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_GET['id_poz'])) {
        $poz_id = (int)$_GET['id_poz'];
       } else {
        $poz_id = 0;
    }
    
    if (isset($_GET['id_cechy'])) {
        $cecha_id = (int)$_GET['id_cechy'];
       } else {
        $cecha_id = 0;
    }

    if (isset($_GET['id'])) {
        $prod_id = (int)$_GET['id'];
       } else {
        $prod_id = 0;
    }
    
    if ((int)$poz_id > 0 || (int)$cecha_id > 0) {
      
        if ( (int)$poz_id > 0 ) {
          
            $pola = array(array('products_min_price_30_day', 0),
                          array('products_min_price_30_day_date', '0000-00-00'),
                          array('products_min_price_30_day_date_created', '0000-00-00'));

            $db->update_query('products' , $pola, 'products_id = ' . $poz_id);
            unset($pola); 
            
            Funkcje::PrzekierowanieURL('produkty_edytuj.php?id_poz=' . (int)$poz_id);
            
        }
        
        if ( (int)$cecha_id > 0 && (int)$prod_id > 0 ) {
          
            $pola = array(array('products_stock_min_price_30_day', 0),
                          array('products_stock_min_price_30_day_date', '0000-00-00'));

            $db->update_query('products_stock' , $pola, 'products_stock_id = ' . $cecha_id);
            unset($pola); 
            
            Funkcje::PrzekierowanieURL('produkty_edytuj.php?id_poz=' . (int)$prod_id . '&zakladka=5');
            
        }        
                                      
        Funkcje::PrzekierowanieURL('produkty_edytuj.php?id_poz=' . (int)$poz_id);
        
    } else {
        
        Funkcje::PrzekierowanieURL('produkty.php');
        
    }
    
}
?>