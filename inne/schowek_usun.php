<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (Sesje::TokenSpr()) {
  
    if ( isset($_SESSION['schowek']) ) {
      
         unset($_SESSION['schowek']);
         
    }

}

?>