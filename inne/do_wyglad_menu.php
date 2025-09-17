<?php
chdir('../'); 
//
if (isset($_POST['id']) && (int)$_POST['id'] > -1 && isset($_COOKIE['eGold'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    $Wyglad = new Wyglad();
    
    echo $Wyglad->Linki('gorne_menu', '<li aria-haspopup="true">', '</li>', false, 1, (int)$_POST['id']);

}
?>