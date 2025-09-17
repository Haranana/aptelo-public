<?php
chdir('../');            

if (isset($_POST['id']) && !empty($_POST['id'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        if ( isset($_POST['komentarz']) ) {
    
             $_POST['komentarz'] = strip_tags((string)$_POST['komentarz']);
    
             $GLOBALS['koszykKlienta']->AktualizujKomentarz( $filtr->process($_POST['id']), $filtr->process($_POST['komentarz']) );
             
        }

    }
    
}
?>