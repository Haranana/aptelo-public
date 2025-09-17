<?php
if ( ( isset($_POST['akcja_dolna']) && (int)$_POST['akcja_dolna'] == 0 ) || !isset($_POST['akcja_dolna']) ) {
    header('Location: allegro_aukcje.php');
    exit;
}
    
if ( (int)$_POST['akcja_dolna'] == 2 ) {
    //
    include('allegro_aukcje_akcja_wznow.php');
    //
}              
if ( (int)$_POST['akcja_dolna'] == 4 ) {
    //
    include('allegro_aukcje_akcja_zakoncz.php');
    //
}   
if ( (int)$_POST['akcja_dolna'] == 5 ) {
    //
    include('allegro_aukcje_akcja_ilosc.php');
    //
} 
if ( (int)$_POST['akcja_dolna'] == 6 ) {
    //
    include('allegro_aukcje_akcja_cena.php');
    //
} 
if ( (int)$_POST['akcja_dolna'] == 10 ) {
    //
    include('allegro_aukcje_akcja_cena_wszystkie.php');
    //
} 
if ( (int)$_POST['akcja_dolna'] == 11 ) {
    //
    include('allegro_aukcje_akcja_ilosc_wszystkie.php');
    //
}
if ( (int)$_POST['akcja_dolna'] == 12 ) {
    //
    include('allegro_aukcje_akcja_usun.php');
    //
}
?>