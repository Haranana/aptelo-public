<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
  
    if ( isset($_POST['akcja_dolna']) && $_POST['akcja_dolna'] == '1' ) {
      
         $zapis = array();
      
         if (isset($_POST['opcja'])) {

             if (count($_POST['opcja']) > 0) {
         
                 foreach ($_POST['opcja'] as $pole) {      
                 
                     $zapytanie = "select * from theme_modules where modul_id = '" . (int)$pole . "'";
                     $sql = $db->open_query($zapytanie);
                     //
                     if ($db->ile_rekordow($sql) > 0) { 
                         //
                         $info = $sql->fetch_assoc();
                         //
                         $pola = array();
                         //
                         foreach ( $info as $klucz => $wartosc ) {
                              //
                              if ( $klucz != 'modul_id' ) {
                                   //
                                   if ( $klucz == 'modul_status' ) {
                                        //
                                        $pola[] = array( 'modul_status', 0 );
                                        //                            
                                   } else {
                                        //
                                        $pola[] = array( $klucz, $wartosc );
                                        //
                                   }
                                   //
                              }
                              //
                         }
                         //
                         $zapis[(int)$pole]['theme_modules'] = $pola;
                         unset($pola); 
                         //
                         $db->close_query($sql);
                         //        
                     }           
                 
                 }
                 
             }
             
         }

         if ( count($zapis) > 0 ) {
      
              header("Content-Type: application/force-download\n");
              header("Cache-Control: cache, must-revalidate");   
              header("Pragma: public");
              header("Content-Disposition: attachment; filename=eksport_moduly_" . date("d-m-Y") . ".data");
              print base64_encode(serialize($zapis));
              exit;       
              
         } else {
           
              Funkcje::PrzekierowanieURL('srodek.php');
              
         }
            
    } else {
 
         Funkcje::PrzekierowanieURL('srodek.php');
          
    }
    
}
?>