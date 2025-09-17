<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
  
    if ( isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0 ) {
      
        $pola = array(array('archiwum_allegro',0));  
        $db->update_query('allegro_auctions' , $pola, " allegro_id = '" . (int)$_GET["id_poz"] . "'");
      
    }
    
    Funkcje::PrzekierowanieURL('allegro_aukcje.php?id_poz=' . (int)$_GET["id_poz"]);

}          
?>