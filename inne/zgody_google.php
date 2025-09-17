<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (isset($_POST['id']) && (int)$_POST['id'] > 0) {

    if (Sesje::TokenSpr()) {

        echo IntegracjeZewnetrzne::GoogleZgoda(true);
        
    }
    
}
?>