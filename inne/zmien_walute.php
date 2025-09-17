<?php
chdir('../'); 

// Ustalanie domyslnej waluty
if ( isset($_POST['waluta']) ) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
      
        if ( !isset($_SESSION['domyslnyJezyk']['waluta']) ) {
             exit;
        }

        $id = $_SESSION['domyslnyJezyk']['waluta'];

        if ( isset($_POST['jezyk']) ) {
            $id = $_SESSION['domyslnyJezyk']['waluta'];
          } elseif ( isset($_POST['waluta']) ) {
            $id = (int)$_POST['waluta'];
        }
        
        if ( !isset($GLOBALS['waluty']->waluty_id[$id]['code']) ) {
             exit;
        }
                
        $kod = $GLOBALS['waluty']->waluty_id[$id]['code'];
        
        if ( !isset($GLOBALS['waluty']->waluty[$kod]['nazwa']) ) {
             exit;
        }

        $waluta = array('id'          => $id,
                        'nazwa'       => $GLOBALS['waluty']->waluty[$kod]['nazwa'],
                        'kod'         => $kod,
                        'symbol'      => $GLOBALS['waluty']->waluty[$kod]['symbol'],
                        'separator'   => $GLOBALS['waluty']->waluty[$kod]['separator'],
                        'przelicznik' => $GLOBALS['waluty']->waluty[$kod]['przelicznik'],
                        'marza'       => $GLOBALS['waluty']->waluty[$kod]['marza']);

        $_SESSION['domyslnaWaluta'] = $waluta;
        //
        // usuwa gratisy przy zmianie jezyka
        if ( isset($_SESSION['koszyk']) ) {
             //
             foreach ( $_SESSION['koszyk'] As $Klucz => $ProduktyKoszyka ) {
                //
                if ( isset($ProduktyKoszyka['rodzaj_ceny']) && $ProduktyKoszyka['rodzaj_ceny'] == 'gratis' ) {
                     //
                     unset($_SESSION['koszyk'][$Klucz]);
                     //
                }
                //
             }
             //
        }        
        //
        // przelicza koszyk na nowa walute
        $GLOBALS['koszykKlienta']->PrzeliczKoszyk();
        //
        unset($id, $kod);
        //
        // jezeli sa pkt w koszyku to je usunie
        if ( isset($_SESSION['punktyKlienta']) ) {
             unset($_SESSION['punktyKlienta']);
        }

    }
    
}

?>