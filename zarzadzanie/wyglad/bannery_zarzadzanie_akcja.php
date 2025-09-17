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
                
                    // zmiana sortowania ------------ ** -------------
                    
                    if (isset($_POST['sort_' . $pole]) && (int)$_POST['sort_' . $pole] > 0) {
                    
                        $sort = (int)$_POST['sort_' . $pole];
                        $sort = (($sort < 0) ? $sort * -1 : $sort);
                        $pola = array(array('sort_order',$sort));
                        $sql = $db->update_query('banners' , $pola, " banners_id = '".$pole."'");

                    }

                    unset($pola);
          
                }

            }

        } else {

            if (isset($_POST['opcja'])) {
                //
                if (count($_POST['opcja']) > 0) {
                  
                    if ( (int)$_POST['akcja_dolna'] == 1 || (int)$_POST['akcja_dolna'] == 2 ) {
                            
                        foreach ($_POST['opcja'] as $pole) {
                
                            switch ((int)$_POST['akcja_dolna']) {
                                case 1:
                                    // kasowanie bannerow ------------ ** -------------
                                    $db->delete_query('banners' , " banners_id = '".$pole."'");                               
                                    break;   
                                case 2:
                                    // zerowanie licznika ------------ ** -------------
                                    $pola = array(array('banners_clicked',0));
                                    $db->update_query('banners' , $pola, " banners_id = '".$pole."'");                       
                                    unset($pola);
                                    break;
                                
                            }          

                        }
                        
                    }
                    
                    // eksport bannerow
                    if ( (int)$_POST['akcja_dolna'] == 3 ) {
                      
                         $zapis = array();
                         $grupy = array();
                         
                         foreach ($_POST['opcja'] as $pole) {      
                         
                             $zapytanie = "select * from banners where banners_id = '" . (int)$pole . "'";
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
                                      if ( $klucz == 'banners_group' ) {
                                           //
                                           $grupy[ $wartosc ] = $wartosc;
                                           //
                                      }
                                      //
                                      if ( $klucz != 'banners_id' && $klucz != 'banners_clicked' && $klucz != 'date_added' && $klucz != 'only_categories_id' && $klucz != 'banners_customers_group_id' ) {
                                           //
                                           $pola[] = array( $klucz, $wartosc );
                                           //
                                      } else {
                                           //
                                           if ( $klucz == 'date_added' ) {
                                                //
                                                $pola[] = array( 'date_added', 'now()' );
                                                //
                                           }
                                           //
                                      }
                                      //
                                 }
                                 //
                                 $zapis['banners'][(int)$pole] = $pola;
                                 unset($pola, $info); 
                                 //
                                 $db->close_query($sql);
                                 //        
                             }           
                         
                         }      
                         
                         // jakie grupy
                         if ( count($grupy) > 0 ) {
                           
                             foreach ( $grupy as $wartosc ) {
                           
                                 $zapytanie = "select * from banners_group where banners_group_code = '" . $wartosc . "'";
                                 $sql = $db->open_query($zapytanie);
                                 //
                                 if ($db->ile_rekordow($sql) > 0) { 
                                     //
                                     $info = $sql->fetch_assoc();
                                     //
                                     $zapis['banners_group'][] = array( 'kod' => $info['banners_group_code'], 'opis' => $info['banners_group_title'] );
                                     //
                                     unset($info);
                                     //
                                 }
                                 //
                                 $db->close_query($sql);
                                 
                             }
                           
                         }

                         if ( count($zapis) > 0 ) {
                      
                              header("Content-Type: application/force-download\n");
                              header("Cache-Control: cache, must-revalidate");   
                              header("Pragma: public");
                              header("Content-Disposition: attachment; filename=eksport_bannery_" . date("d-m-Y") . ".data");
                              print base64_encode(serialize($zapis));
                              exit;

                         }                              
                      
                    }
                
                }
                //
            }
            
    }  

    Funkcje::PrzekierowanieURL('bannery_zarzadzanie.php');
    
}
?>