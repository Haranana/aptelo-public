<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (Sesje::TokenSpr()) {
  
    // usuwanie produktu z zapisanego koszyka
    if (isset($_POST['id']) && (int)$_POST['id'] > 0) {
      
        if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {
      
            $GLOBALS['db']->delete_query('basket_save_products' , " basket_products_id = '" . (int)$_POST['id'] . "'");
            
        }        
      
    }

}

?>