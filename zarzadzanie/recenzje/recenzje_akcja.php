<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja_dolna']) && $_POST['akcja_dolna'] == '0') {
    
            if (isset($_POST['id_prod']) && count($_POST['id_prod']) > 0) {
            
                foreach ($_POST['id_prod'] as $pole) {
                
                    // zmiana statusu ------------ ** -------------
                    if (isset($_POST['status_' . $pole])) {
                        $status = (int)$_POST['status_' . $pole];
                      } else {
                        $status = 0;
                    }
                    $status = (($status == 1) ? '1' : '0');
                    $pola = array(array('products_status',$status));
                    $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
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
                                // kasowanie recenzji ------------ ** -------------
                                $db->delete_query('reviews' , " reviews_id = '".$pole."'");     
                                $db->delete_query('reviews_description' , " reviews_id = '".$pole."'");                            
                                break;                                  
                        }          

                    }
                
                }
                //
            }
            
    }
    
    Funkcje::PrzekierowanieURL('recenzje.php');
    
}
?>