<?php
chdir('../');  

if (isset($_POST['id']) && (int)$_POST['id'] > 0) {
  
    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');  
   
    $Wyglad = new Wyglad();
    
    $WynikTxt = preg_replace('/<!--(.*)-->/Uis', '', (string)$Wyglad->SrodekSklepu( 'srodek', array(), '', (int)$_POST['id'] ));
    
    echo $WynikTxt;

}
?>