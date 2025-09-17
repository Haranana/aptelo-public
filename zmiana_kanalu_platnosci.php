<?php
chdir('../');

if (isset($_POST['data']) && !empty($_POST['data'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    if (Sesje::TokenSpr()) {

        $_POST['data'] = $filtr->process($_POST['data']);

        $_SESSION['rodzajPlatnosci']['platnosc_kanal'] = $_POST['data'];

        echo json_encode($_POST['data']);

    }
    
}

?>