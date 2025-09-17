<?php
chdir('../'); 

if (isset($_POST['id']) && isset($_POST['cechy'])) {

    $PodzielId = explode('_', (string)$_POST['id']);

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (!Sesje::TokenSpr() && (int)$PodzielId[1] > 0) {
        echo 'false';
        exit;
    }
    
    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KOSZYK') ), $GLOBALS['tlumacz'] );

    if ( !empty($_POST['cechy']) ) {
         //
         $Produkt = new Produkt( (int)$PodzielId[1] );
         //
         $cechy = substr(str_replace('x', ',', (string)$filtr->process($_POST['cechy'])), 1, strlen((string)$filtr->process($_POST['cechy'])));
         //
         $zapytanie  = "SELECT products_stock_size 
                          FROM products_stock WHERE products_id = '" . (int)$PodzielId[1] . "' and products_stock_attributes = '" . $cechy . "' and products_stock_size > 0";

         $sql = $GLOBALS['db']->open_query($zapytanie); 

         if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
             //
             $info = $sql->fetch_assoc();
             //
             echo $Produkt->ProduktWielkoscPojemnosc( $info['products_stock_size'], (float)$_POST['cena'] );
             //
             unset($info);
             //
         } else {
             //
             echo $Produkt->ProduktWielkoscPojemnosc( $Produkt->info['rozmiar'], (float)$_POST['cena'] );
             //
         }
        
         $GLOBALS['db']->close_query($sql);
         unset($zapytanie, $Produkt);                          
          
    }
    
}

?>