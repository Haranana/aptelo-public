<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja_dolna']) && $_POST['akcja_dolna'] == '0') {
    
            if (isset($_POST['id']) && count($_POST['id']) > 0) {
            
                foreach ($_POST['id'] as $pole) {
                
                    // zmiana statusu ------------ ** -------------
                    if (isset($_POST['status_' . $pole])) {
                        $status = (int)$_POST['status_' . $pole];
                      } else {
                        $status = 0;
                    }
                    $status = (($status == 1) ? '1' : '0');
                    $pola = array(array('newsdesk_status',$status));
                    $sql = $db->update_query('newsdesk' , $pola, " newsdesk_id = '".$pole."'");
                    unset($pola, $status);
                
                }
            
            }
            
        } else {

            if (isset($_POST['opcja'])) {
                //
                if (count($_POST['opcja']) > 0) {
            
                    foreach ($_POST['opcja'] as $pole) {
            
                        switch ((int)$_POST['akcja_dolna']) {
                            case 1:
                                // zmiana statusu na nieaktywny ------------ ** -------------
                                $pola = array(array('newsdesk_status','0'));
                                $sql = $db->update_query('newsdesk' , $pola, " newsdesk_id = '".$pole."'");
                                unset($pola);                             
                                break; 
                            case 2:
                                // zmiana statusu na aktywny ------------ ** -------------
                                $pola = array(array('newsdesk_status','1'));
                                $sql = $db->update_query('newsdesk' , $pola, " newsdesk_id = '".$pole."'");
                                unset($pola);                             
                                break; 
                            case 3:
                                // usuniecie artykulow ------------ ** -------------
                                $db->delete_query('newsdesk' , " newsdesk_id = '".$pole."'");  
                                $db->delete_query('newsdesk_description' , " newsdesk_id = '".$pole."'");
                                $db->delete_query('newsdesk_to_categories' , " newsdesk_id = '".$pole."'");                         
                                break;                               
                        }          

                    }
                
                }
                //
            }
            
    }
    
    Funkcje::PrzekierowanieURL('aktualnosci.php');
    
}
?>