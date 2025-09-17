<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr()) {
  
    if ( isset($_POST['dane']) ) {
      
        $pola = array(
                array('value',$filtr->process($_POST['dane'])));

        $sql = $db->update_query('settings', $pola, " code = 'STRONY_KOLUMNY_BOX'");	
        unset($pola);          
      
    }

}
?>
