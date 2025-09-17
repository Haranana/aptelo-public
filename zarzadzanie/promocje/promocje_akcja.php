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

            if (isset($_POST['opcja'])) {
                //
                if (count($_POST['opcja']) > 0) {
                
                    // jezeli usuwanie promocji i przywrocenie ceny to ustawi tablice vat
                    if ( (int)$_POST['akcja_dolna'] == 11 ) {
                        //
                        // pobieranie informacji o vat - tworzy tablice ze stawkami
                        $zapytanie_vat = "select distinct * from tax_rates order by tax_rate desc";
                        $sqls = $db->open_query($zapytanie_vat);
                        //
                        $tablicaVat = array();
                        while ($infs = $sqls->fetch_assoc()) { 
                            $tablicaVat[$infs['tax_rates_id']] = $infs['tax_rate'];
                        }
                        $db->close_query($sqls);
                        unset($zapytanie_vat, $infs);  
                        //                
                    }
                    
                    foreach ($_POST['opcja'] as $pole) {
            
                        switch ((int)$_POST['akcja_dolna']) {
                            case 1:
                                // usuwa z produktu zaznaczenie ze jest promocja ------------ ** -------------
                                $pola = array(array('specials_status','0'),
                                              array('specials_date','0000-00-00 00:00:00'),
                                              array('specials_date_end','0000-00-00 00:00:00'),
                                              array('products_old_price','0'));
                                //
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
                                $pola = array(array('specials_date','0000-00-00'));
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola);                             
                                break;    
                            case 6:
                                // wyzerowanie daty zakonczenia ------------ ** -------------
                                $pola = array(array('specials_date_end','0000-00-00'));
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola);                             
                                break;
                            case 7:
                                // dodaj/odejmij iloœæ dni do daty rozpoczêcia ------------ ** -------------
                                if (isset($_POST['wartosc']) && !empty($_POST['wartosc'])) {
                                    $wskaznikObliczenia = (int)$_POST['wartosc'] * 86400; // 86400 - ilosc sekund na dzien
                                }
                                // pobiera wartosc daty dla danego produktu
                                $zapytanie = "select distinct products_id, specials_date from products where products_id = '".$pole."'";
                                $sql = $db->open_query($zapytanie); 
                                $info = $sql->fetch_assoc(); 
                                //
                                if (!empty($info['specials_date']) && $info['specials_date'] != '0000-00-00 00:00:00') {
                                    $pola = array(array('specials_date',date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($info['specials_date']) + $wskaznikObliczenia)));
                                    $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                    unset($pola);                                 
                                }
                                if ($info['specials_date'] == '0000-00-00 00:00:00') {
                                    $DataBiezaca = time();
                                    $pola = array(array('specials_date',date('Y-m-d H:i:s', $DataBiezaca + $wskaznikObliczenia)));
                                    $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                    unset($pola, $DataBiezaca);                                 
                                }
                                //
                                $db->close_query($sql);
                                unset($info, $wskaznikObliczenia);
                                //
                                break; 
                            case 8:
                                // dodaj/odejmij iloœæ dni do daty zakonczenia ------------ ** -------------
                                if (isset($_POST['wartosc']) && !empty($_POST['wartosc'])) {
                                    $wskaznikObliczenia = (int)$_POST['wartosc'] * 86400; // 86400 - ilosc sekund na dzien
                                }
                                // pobiera wartosc daty dla danego produktu
                                $zapytanie = "select distinct products_id, specials_date_end from products where products_id = '".$pole."'";
                                $sql = $db->open_query($zapytanie); 
                                $info = $sql->fetch_assoc(); 
                                //
                                if (!empty($info['specials_date_end']) && $info['specials_date_end'] != '0000-00-00 00:00:00') {
                                    $pola = array(array('specials_date_end',date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($info['specials_date_end']) + $wskaznikObliczenia)));
                                    $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                    unset($pola);                                  
                                }
                                if ($info['specials_date_end'] == '0000-00-00 00:00:00') {
                                    $DataBiezaca = time();
                                    $pola = array(array('specials_date_end',date('Y-m-d H:i:s', $DataBiezaca + $wskaznikObliczenia)));
                                    $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                    unset($pola, $DataBiezaca);                                  
                                }
                                //
                                $db->close_query($sql);
                                unset($info, $wskaznikObliczenia);
                                //
                                break;       
                            case 11:
                                // usuwa promocje i ustawia cene poprzednia jako glowna ------------ ** -------------
                                //            
                                Funkcje::AktualizujHistorieCenProduktowPromocji($pole);
                                //
                                $pola = array(array('specials_status','0'),
                                              array('products_old_price','0'),
                                              array('specials_date',''),
                                              array('specials_date_end',''));            
                                //
                                $zapytanie = "select distinct * from products where products_id = '".$pole."'";
                                $sql = $db->open_query($zapytanie);    
                                $info = $sql->fetch_assoc();  
                                //
                                $vat_produktu = $info['products_tax_class_id'];
                                //                            
                                $wartosc = $info['products_old_price'];
                                $netto = round(($wartosc / (1 + ($tablicaVat[$vat_produktu]/100))), 2);
                                $podatek = $wartosc - $netto;
                                //
                                $pola[] = array('products_price_tax',(float)$wartosc);
                                $pola[] = array('products_price',(float)$netto);
                                $pola[] = array('products_tax',(float)$podatek);  
                                //
                                unset($wartosc, $netto, $podatek);
                                //
                                // ceny dla pozostalych poziomow cen
                                for ($x = 2; $x <= ILOSC_CEN; $x++) {
                                    // cena poprzednia
                                    if ( $info['products_old_price_'.$x] > 0 ) {
                                        //
                                        $wartosc = $info['products_old_price_'.$x];
                                        $netto = round(($wartosc / (1 + ($tablicaVat[$vat_produktu]/100))), 2);
                                        $podatek = $wartosc - $netto;    
                                        //
                                        $pola[] = array('products_old_price_'.$x,'0');
                                        $pola[] = array('products_price_tax_'.$x,(float)$wartosc);
                                        $pola[] = array('products_price_'.(float)$x,$netto);
                                        $pola[] = array('products_tax_'.$x,(float)$podatek);
                                        //    
                                        unset($wartosc, $netto, $podatek); 
                                        //                
                                    }
                                    //
                                }             
                                //            
                                $db->close_query($sql);
                                unset($info);
                                // 
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola);
                                
                                // ########## aktualizacja cech produktow ##########
                                
                                $zapytanie = "select distinct * from products_stock where products_id = '" . $pole . "'";
                                $sql = $db->open_query($zapytanie);   
                                //
                                while ( $info = $sql->fetch_assoc() ) {
                                  
                                    $pola = array(array('products_stock_old_price','0'));       
                                    //                         
                                    if ( $info['products_stock_old_price'] > 0 ) {
                                         //
                                         $wartosc = $info['products_stock_old_price'];
                                         $netto = round(($wartosc / (1 + ($tablicaVat[$vat_produktu]/100))), 2);
                                         $podatek = $wartosc - $netto;
                                         //
                                         $pola[] = array('products_stock_price_tax',(float)$wartosc);
                                         $pola[] = array('products_stock_price',(float)$netto);
                                         $pola[] = array('products_stock_tax',(float)$podatek);  
                                         //
                                         unset($wartosc, $netto, $podatek);
                                         //                                
                                    }
                                    
                                    // ceny dla pozostalych poziomow cen
                                    for ($x = 2; $x <= ILOSC_CEN; $x++) {
                                        //
                                        // cena poprzednia
                                        if ( $info['products_stock_old_price_'.$x] > 0 ) {
                                            //
                                            $wartosc = $info['products_stock_old_price_'.$x];
                                            $netto = round(($wartosc / (1 + ($tablicaVat[$vat_produktu]/100))), 2);
                                            $podatek = $wartosc - $netto;    
                                            //
                                            $pola[] = array('products_stock_old_price_'.$x,'0');
                                            $pola[] = array('products_stock_price_tax_'.$x,(float)$wartosc);
                                            $pola[] = array('products_stock_price_'.$x,(float)$netto);
                                            $pola[] = array('products_stock_tax_'.$x,(float)$podatek);
                                            //    
                                            unset($wartosc, $netto, $podatek); 
                                            //                
                                        }
                                        //
                                    }                                      
                                    
                                    if ( count($pola) > 0 ) {
                                      
                                         $sql_wynik = $db->update_query('products_stock' , $pola, " products_id = '" . $pole . "' and products_stock_id = '" . $info['products_stock_id'] . "'");
                                         
                                    }

                                    unset($pola);
                                    
                                }
                                
                                $db->close_query($sql);
                                unset($info, $vat_produktu);   
                                     
                                break;                                  
                        }          

                    }
                
                }
                //
            }
            
    }
    
    Funkcje::PrzekierowanieURL('promocje.php');
    
}
?>