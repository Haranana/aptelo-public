<?php
chdir('../'); 
//
if (isset($_POST['value'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        if ( isset($_SESSION['adresFaktury']['dokument']) ) {
            unset($_SESSION['adresFaktury']['dokument']);
        }
        $_SESSION['adresFaktury']['dokument'] = $filtr->process($_POST['value']);

    }
    
}

?>