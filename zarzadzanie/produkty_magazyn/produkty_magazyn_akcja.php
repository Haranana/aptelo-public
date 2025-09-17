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

                    $pola = array(
                                  array('products_availability_id',(int)$_POST['dostepnosc_'.$pole]),
                                  array('products_shipping_time_id',(int)$_POST['wysylka_'.$pole]),
                                  array('products_shipping_time_zero_quantity_id',(int)$_POST["wysylka_zero_".$pole]),
                                  array('products_status',$status)
                                  );
                                  
                    if (isset($_POST['ilosc_' . $pole])) {
                        $pola[] = array('products_quantity',(float)$_POST['ilosc_'.$pole]);
                    }
                
                    $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                
                    unset($pola);

                }
                
            }
        
        } else { 
        
            if (isset($_POST['opcja'])) {
                //
                if (count($_POST['opcja']) > 0) {
            
                    foreach ($_POST['opcja'] as $pole) {
            
                        switch ((int)$_POST['akcja_dolna']) { 
                            case 4:
                                // zmiana statusu dostepnosci ------------ ** -------------
                                $pola = array(array('products_availability_id',$filtr->process($_POST['dostepnosc'])));
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola);                             
                                break;
                            case 5:
                                // zmiana czasu wysylki ------------ ** -------------
                                $pola = array(array('products_shipping_time_id',$filtr->process($_POST['wysylka'])));
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola);                             
                                break;  
                            case 6:
                                // zmiana czasu wysylki dla stanu 0 ------------ ** -------------
                                $pola = array(array('products_shipping_time_zero_quantity_id',$filtr->process($_POST['wysylka_zero'])));
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola);                             
                                break;                                     
                        }          

                    }
                
                }
                //
            }       

    }
    
    Funkcje::PrzekierowanieURL('produkty_magazyn.php');
    
}
?>