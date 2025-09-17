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
                                // usuwa z produktu zaznaczenie ze polecanym ------------ ** -------------
                                $pola = array(array('featured_status','0'),
                                              array('featured_date',''),
                                              array('featured_date_end',''));
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola);                             
                                break;                          
                            case 2:
                                // zmiana statusu na nieaktywny ------------ ** -------------
                                $pola = array(array('products_status','0'));
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola);                             
                                break; 
                            case 3:
                                // zmiana statusu na aktywny ------------ ** -------------
                                $pola = array(array('products_status','1'));
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola);                             
                                break; 
                            case 4:
                                // usuniecie produktow ------------ ** -------------
                                //Produkty::SkasujProdukt($pole);                    
                                break; 
                            case 5:
                                // wyzerowanie daty rozpoczecia ------------ ** -------------
                                $pola = array(array('featured_date','0000-00-00'));
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola);                             
                                break;    
                            case 6:
                                // wyzerowanie daty zakonczenia ------------ ** -------------
                                $pola = array(array('featured_date_end','0000-00-00'));
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola);                             
                                break;
                            case 7:
                                // dodaj/odejmij iloœæ dni do daty rozpoczêcia ------------ ** -------------
                                if (isset($_POST['wartosc']) && !empty($_POST['wartosc'])) {
                                    $wskaznikObliczenia = (int)$filtr->process($_POST['wartosc']) * 86400; // 86400 - ilosc sekund na dzien
                                }
                                // pobiera wartosc daty dla danego produktu
                                $zapytanie = "select distinct products_id, featured_date from products where products_id = '".$pole."'";
                                $sql = $db->open_query($zapytanie); 
                                $info = $sql->fetch_assoc(); 
                                //
                                if (!empty($info['featured_date'])) {
                                    if (FunkcjeWlasnePHP::my_strtotime($info['featured_date']) > time() && (FunkcjeWlasnePHP::my_strtotime($info['featured_date']) + $wskaznikObliczenia) > time() ) {
                                         $pola = array(array('featured_date',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($info['featured_date']) + $wskaznikObliczenia)));
                                      } else {
                                         $pola = array(array('featured_date','0000-00-00'));
                                    }
                                }
                                //
                                $db->close_query($sql);
                                unset($info);
                                //
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola, $wskaznikObliczenia);                                 
                                break; 
                            case 8:
                                // dodaj/odejmij iloœæ dni do daty zakonczenia ------------ ** -------------
                                if (isset($_POST['wartosc']) && !empty($_POST['wartosc'])) {
                                    $wskaznikObliczenia = (int)$filtr->process($_POST['wartosc']) * 86400; // 86400 - ilosc sekund na dzien
                                }
                                // pobiera wartosc daty dla danego produktu
                                $zapytanie = "select distinct products_id, featured_date_end from products where products_id = '".$pole."'";
                                $sql = $db->open_query($zapytanie); 
                                $info = $sql->fetch_assoc(); 
                                //
                                if (!empty($info['featured_date_end'])) {
                                    if (FunkcjeWlasnePHP::my_strtotime($info['featured_date_end']) > time() && (FunkcjeWlasnePHP::my_strtotime($info['featured_date_end']) + $wskaznikObliczenia) > time() ) {
                                         $pola = array(array('featured_date_end',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($info['featured_date_end']) + $wskaznikObliczenia)));
                                      } else {
                                         $pola = array(array('featured_date_end','0000-00-00'));
                                    }
                                }
                                //
                                $db->close_query($sql);
                                unset($info);
                                //
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola, $wskaznikObliczenia);                                  
                                break;                               
                        }          

                    }
                
                }
                //
            }
            
    }
    
    Funkcje::PrzekierowanieURL('polecane.php');
    
}
?>