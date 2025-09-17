<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
  
    if ( ( isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0 && isset($_GET['typ']) ) && INTEGRACJA_FAKTUROWNIA_WLACZONY == 'tak' ) {
      
        $zamowienie = new Zamowienie((int)$_GET['id_poz']);

        if ( $zamowienie->info['id_zamowienia'] > 0 ) {      
            
             $fakturownia = new Fakturownia($zamowienie->info['id_zamowienia'], false);
             
             if ( (int)$_GET['typ'] == 1 ) {
             
                  $fakturownia->DodajFakture($zamowienie);
                  
             }
             
             if ( (int)$_GET['typ'] == 2 ) {
             
                  $fakturownia->PobierzFakturePdf();
                  exit;
                  
             }      
             
             // pobranie 
             if ( (int)$_GET['typ'] == 6 && isset($_GET['id']) && (int)$_GET['id'] > 0 ) {
             
                  $fakturownia->PobierzFakturePdf((int)$_GET['id']);
                  exit;
                  
             }                   

             if ( (int)$_GET['typ'] == 3 ) {
             
                  $fakturownia->WyslijFakturePdf();
                  
             }  

             if ( (int)$_GET['typ'] == 4 ) {
             
                  $fakturownia->UsunFakture();
                  
             } 

             if ( $_GET['typ'] == 'wystawiona' || $_GET['typ'] == 'oplacona' ) {
             
                  $fakturownia->ZmienStatusFaktury($_GET['typ']);
                  
             }                   
             
        }
        
        unset($zamowienie);
        
        Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz=' . (int)$_GET["id_poz"] . ((isset($_GET["zakladka"])) ? '&zakladka=' . $filtr->process($_GET["zakladka"]) : ''));
        
    } else {
      
        Funkcje::PrzekierowanieURL('zamowienia.php');
        
    }

}