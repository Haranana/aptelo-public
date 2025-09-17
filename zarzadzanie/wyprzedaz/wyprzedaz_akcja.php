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
                    $pola = array(array('products_status',(int)$status));
                    $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                    unset($pola, $status);
                
                }
            
            }
            
        } else {

            if (isset($_POST['opcja']) && count($_POST['opcja']) > 0) {
            
                foreach ($_POST['opcja'] as $pole) {
        
                    if ( (int)$_POST['akcja_dolna'] == 1 ) {

                        // usuwa z produktu zaznaczenie ze jest wyprzedaza ------------ ** -------------
                        $pola = array(array('sale_status','0'),
                                      array('products_old_price','0'));                          

                        for ($x = 2; $x <= ILOSC_CEN; $x++) {
                             //
                             $pola[] = array('products_old_price_'.$x,'0');
                             //
                         }                                              
                         $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                         unset($pola);    

                         $zapytanie = "select distinct * from products_stock where products_id = '".$pole."'";
                         $sql = $db->open_query($zapytanie);   
                        
                         while ( $info = $sql->fetch_assoc() ) {
                            //                            
                            if ( $info['products_stock_old_price'] > 0 ) {
                              
                                $pola = array(array('products_stock_old_price','0')); 

                                // ceny dla pozostalych poziomow cen
                                for ( $x = 2; $x <= ILOSC_CEN; $x++ ) {
                                      //
                                      $pola[] = array('products_stock_old_price_'.$x,'0');
                                      //
                                }      

                                $sqlr = $db->update_query('products_stock' , $pola, " products_id = '" . $pole . "' and products_stock_id = '" . $info['products_stock_id'] . "'");
                                unset($pola);
                                
                            }
                          
                         }     

                         $db->close_query($sql);
                         unset($info);                              
                            
                    }
                    
                }
                
            }
            
    }
    
    Funkcje::PrzekierowanieURL('wyprzedaz.php');
    
}
?>