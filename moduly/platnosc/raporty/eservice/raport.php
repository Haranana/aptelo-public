<?php
chdir('../../../../');
require_once('ustawienia/init.php');

if ( isset($_POST) ) {
  
     if ( isset($_POST['HASHPARAMS']) && isset($_POST['HASHPARAMSVAL']) ) {

          if ( PlatnosciElektroniczne::HashEservice($_POST) == true ) {
               //
               Funkcje::PrzekierowanieURL('brak-strony.html');
               //
          } else {
               //
               PlatnosciElektroniczne::StatusEservice($_POST, true);
               //
          }
          
     } else {
       
          //
          Funkcje::PrzekierowanieURL('brak-strony.html');
          //
        
     }

     //
     Funkcje::PrzekierowanieURL('brak-strony.html');
     //     
     
}     
?>