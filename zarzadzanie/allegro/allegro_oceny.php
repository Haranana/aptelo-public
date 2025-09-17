<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
  
    $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );
  
    $DaneAukcji = $AllegroRest->commandGetBeta('users/' . ZAKLADKA_ALLEGRO_OPINIE_ID . '/ratings-summary');
  
    if ( isset($DaneAukcji->recommended) && count((array)$DaneAukcji->recommended) ) {
        
         $pola = array(
                 array('value',serialize($DaneAukcji)));
                
         $db->update_query('settings' , $pola, " code = 'ZAKLADKA_ALLEGRO_OPINIE_TABLICA'");	
         unset($pola);
         
         $pola = array(
                 array('value',date('d-m-Y H:i', time())));
                
         $db->update_query('settings' , $pola, " code = 'ZAKLADKA_ALLEGRO_OPINIE_DATA_POBRANIA'");	
         unset($pola);         
    
         Funkcje::PrzekierowanieURL('/zarzadzanie/integracje/konfiguracja_zakladki.php?oceny_aktualizacja');
    
    } else {
        
         $pola = array(
                 array('value',''));
                
         $db->update_query('settings' , $pola, " code = 'ZAKLADKA_ALLEGRO_OPINIE_TABLICA'");	
         unset($pola);
         
         $pola = array(
                 array('value',''));
                
         $db->update_query('settings' , $pola, " code = 'ZAKLADKA_ALLEGRO_OPINIE_DATA_POBRANIA'");	
         unset($pola);         
      
         Funkcje::PrzekierowanieURL('/zarzadzanie/integracje/konfiguracja_zakladki.php?oceny_aktualizacja_blad');
         
    }

}

?>