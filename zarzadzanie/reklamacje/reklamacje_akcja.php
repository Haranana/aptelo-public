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
            if ( (int)$_POST['akcja_dolna'] == 1 && (int)$_POST['usuniecie_reklamacji'] == 1 ) {

                foreach ($_POST['opcja'] as $pole) {
                  
                    // sprawdzi czy nie ma zdjecia
                    $zapytanie = "select *from complaints where complaints_id = '".(int)$pole."'";
                    $sql = $db->open_query($zapytanie);
                    
                    if ((int)$db->ile_rekordow($sql) > 0) {
                      
                        $info = $sql->fetch_assoc(); 
                        
                        for ( $x = 1; $x < 4; $x++ ) {

                            if ( !empty($info['complaints_image_' . $x]) ) {
                                 
                                 if ( file_exists('../grafiki_inne/' . $info['complaints_image_' . $x]) ) {
                                      unlink('../grafiki_inne/' . $info['complaints_image_' . $x]);
                                 }
                                 
                            }
                            
                        }
                          
                    }
                    
                    $db->close_query($sql);                     
                  
                    $db->delete_query('complaints' , " complaints_id = '".(int)$pole."'");  
                    $db->delete_query('complaints_status_history' , " complaints_id = '".(int)$pole."'");                   

                }
                
            }

        }
     
    }
    
    Funkcje::PrzekierowanieURL('reklamacje.php');
    
}
?>