<?php
if ( ( isset($_POST['akcja_dolna']) && (int)$_POST['akcja_dolna'] == 0 ) || !isset($_POST['akcja_dolna']) ) {
    header('Location: allegro_sprzedaz.php');
    exit;
}
    
if ( (int)$_POST['akcja_dolna'] == 1 ) {
    //
    include('allegro_akcja_komentarze.php');
    //
}
if ( (int)$_POST['akcja_dolna'] == 2 ) {
    //
    include('allegro_akcja_utworz_zamowienia.php');
    //
}              
?>