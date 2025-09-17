<?php
$tablica_opisu = array();

$nr_wiersza = 1;

for ( $x = 1; $x <= 100; $x++ ) {
  
    if ( isset($_POST['opis_txt'][$x]) ) {
         $_POST['opis_txt'][$x] = str_replace('<b>[OP', '[OP', (string)$_POST['opis_txt'][$x]);
         $_POST['opis_txt'][$x] = str_replace('IS]</b>', 'IS]', (string)$_POST['opis_txt'][$x]);  
    }     
 
    if ( isset($_POST['opis_sekcja'][$x]) ) {
      
         // tylko sam listing
         if ( $_POST['opis_sekcja'][$x] == 'listing' ) {
              // 
              if ( !empty($_POST['opis_txt'][$x]) ) {
                   $tablica_opisu[$nr_wiersza] = array( $_POST['opis_sekcja'][$x], array( trim((string)preg_replace('/\s\s+/', '', (string)$filtr->process($_POST['opis_txt'][$x]))) ) );            
              }
              // 
         }
         
         // tylko zdjecie
         if ( $_POST['opis_sekcja'][$x] == 'zdjecie' ) {
              // 
              if ( !empty($_POST['opis_img'][$x]) ) {
                   $tablica_opisu[$nr_wiersza] = array( $_POST['opis_sekcja'][$x], array( $_POST['opis_img'][$x] ) );                     
              }
              // 
         }    

         // zdjecie i listing  
         if ( $_POST['opis_sekcja'][$x] == 'zdjecie_listing' ) {
              // 
              if ( !empty($_POST['opis_img'][$x]) && !empty($_POST['opis_txt'][$x]) ) {
                   $tablica_opisu[$nr_wiersza] = array( $_POST['opis_sekcja'][$x], array( $_POST['opis_img'][$x], trim((string)preg_replace('/\s\s+/', ' ', (string)$filtr->process($_POST['opis_txt'][$x]))) ) );                   
              }
              // 
         }
         
         // listing i zdjecie
         if ( $_POST['opis_sekcja'][$x] == 'listing_zdjecie' ) {
              // 
              if ( !empty($_POST['opis_img'][$x]) && !empty($_POST['opis_txt'][$x]) ) {
                   $tablica_opisu[$nr_wiersza] = array( $_POST['opis_sekcja'][$x], array( trim((string)preg_replace('/\s\s+/', ' ', (string)$filtr->process($_POST['opis_txt'][$x]))), $_POST['opis_img'][$x] ) );                       
              }
              // 
         }      

         // zdjecie i zdjecie
         if ( $_POST['opis_sekcja'][$x] == 'zdjecie_zdjecie' ) {
              // 
              if ( !empty($_POST['opis_img'][$x][1]) && !empty($_POST['opis_img'][$x][2]) ) {
                   $tablica_opisu[$nr_wiersza] = array( $_POST['opis_sekcja'][$x], array( $_POST['opis_img'][$x][1], $_POST['opis_img'][$x][2] ) );                       
              }
              // 
         } 

         $nr_wiersza++;

    }        
  
}

unset($nr_wiersza);

$opis_allegro = '';

if ( isset($tablica_opisu) && count($tablica_opisu) > 0 ) {

    $opis_allegro = serialize($tablica_opisu);
    
}

?>