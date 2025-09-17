<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (Sesje::TokenSpr()) {
  
    if ( !isset($_POST['id']) ) {
      
        if ( isset($_SESSION['koszyk']) ) {
          
            // koszyk w cookies
            if ( isset($_COOKIE['koszykGold']) ) {
                 //
                 @setcookie("koszykGold", '', time() - 86400, '/');
                 @setcookie("koszykGoldID", '', time() - 86400, '/');
                 //
            }          
          
            foreach ( $_SESSION['koszyk'] As $TablicaWartosci ) {
                //
                $GLOBALS['koszykKlienta']->UsunZKoszyka( $TablicaWartosci['id'], false ); 
                //
            }
            
            $GLOBALS['koszykKlienta']->PrzeliczKoszyk();
             
        }
    
    }
    
    // usuwanie zapisanego koszyka
    if (isset($_POST['id']) && (int)$_POST['id'] > 0) {
      
        if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {
          
            // sprawdzi zgodnosc id koszyka i klienta
            $zapytanie = "SELECT * FROM basket_save";
            $sql = $GLOBALS['db']->open_query($zapytanie);      

            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 

                while ($info = $sql->fetch_assoc()) {
                       //
                       if ( $info['basket_id'] == (int)$_POST['id'] && $info['customers_id'] == (int)$_SESSION['customer_id'] ) {
                            //
                            $GLOBALS['db']->delete_query('basket_save' , " basket_id = '" . $info['basket_id'] . "' and customers_id = '" . $info['customers_id'] . "'");
                            $GLOBALS['db']->delete_query('basket_save_products' , " basket_id = '" . $info['basket_id'] . "'");
                            //
                       }
                       //          
                }
                
                unset($info);
                
            }
                                                    
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie);            

        }        
      
    }

}

?>