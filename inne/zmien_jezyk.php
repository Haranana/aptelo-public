<?php
chdir('../'); 

// Ustalanie domyslnego jezyka
if ( isset($_POST['jezyk']) ) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {

        if (isset($_POST['jezyk']) && (int)$_POST['jezyk'] > 0 ) {
            $jezyk = new Jezyki((int)$_POST['jezyk']);
          } else {
            $jezyk = new Jezyki();
        }
        $_SESSION['domyslnyJezyk'] = $jezyk->tablicaJezyka;
        
        // przy zmianie jezyka zmienia rowniez walute na domyslna
        $id = $_SESSION['domyslnyJezyk']['waluta'];
        
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
        unset($jezyk, $id, $kod);
        //
        // jezeli sa pkt w koszyku to je usunie
        if ( isset($_SESSION['punktyKlienta']) ) {
             unset($_SESSION['punktyKlienta']);
        }
        //
    }
    
}

?>