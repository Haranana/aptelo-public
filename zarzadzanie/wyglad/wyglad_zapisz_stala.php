<?php
chdir('../');     

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr()) {

    if ($_POST['stala'] == 'NAGLOWEK') {

        $pola = array(
                array('value',htmlspecialchars((string)$_POST['wart'])));
                
      } else {
      
        $pola = array(
                array('value',$filtr->process($_POST['wart'])));

    }
    
    $sql = $db->update_query('settings', $pola, " code = '".$filtr->process($_POST['stala'])."'");	
    unset($pola); 
    
    // jezeli wylaczanie bannero to usuwac grupe bannerow
    if ( $_POST['stala'] == 'STOPKA_BANNERY' ) {
         //
         if ( $_POST['wart'] == 'nie' ) {
              //
              $pola_bannery = array(array('value',''));       
              $sql_bannery = $db->update_query('settings', $pola_bannery, " code = 'STOPKA_BANNERY_GRUPA'");	
              //
         }
         //
    }    
    
}
?>