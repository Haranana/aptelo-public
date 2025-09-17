<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_POST['akcja_dolna']) && (int)$_POST['akcja_dolna'] > 0) {
        
        if ( isset($_POST['opcja']) && count($_POST['opcja']) > 0 ) {
        
            // jezeli jest usuwanie zamowien
            if ( (int)$_POST['akcja_dolna'] == 1 && (int)$_POST['usuniecie_zwrotu'] == 1 ) {

                foreach ($_POST['opcja'] as $pole) {
                  
                    // sprawdzi czy nie ma zdjecia
                    $zapytanie = "select * from return_list where return_id = '" . (int)$pole . "'";
                    $sql = $db->open_query($zapytanie);
                    
                    if ((int)$db->ile_rekordow($sql) > 0) {
                      
                        $info = $sql->fetch_assoc(); 
                        
                        if ( !empty($info['return_image_1']) ) {
                             
                             if ( file_exists('../grafiki_inne/' . $info['return_image_1']) ) {
                                  unlink('../grafiki_inne/' . $info['return_image_1']);
                             }
                             
                        }

                    }
                    
                    $db->close_query($sql);        

                    $db->delete_query('return_list', " return_id = '" . (int)$pole . "'");  
                    $db->delete_query('return_products', " return_id = '" . (int)$pole . "'");  
                    $db->delete_query('return_status_history', " return_id = '" . (int)$pole . "'");                 

                }
                
            }

        }
     
    }
    
    Funkcje::PrzekierowanieURL('zwroty.php');
    
}
?>