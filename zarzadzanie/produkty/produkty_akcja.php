<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja_dolna']) && $_POST['akcja_dolna'] == '0') {
    
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

            if (isset($_POST['id']) && count($_POST['id']) > 0) {
            
                foreach ($_POST['id'] as $pole) {
                
                    // zmiana sortowania ------------ ** -------------
                    if (isset($_POST['sort_' . $pole]) && (int)$_POST['sort_' . $pole] > 0) {
                        $sort = (int)$_POST['sort_' . $pole];
                        $sort = (($sort < 0) ? $sort * -1 : $sort);
                        $pola = array(array('sort_order',$sort));
                        $sql = $db->update_query('products' , $pola, " products_id = '".(int)$pole."'");

                    }
                    unset($pola, $sort);
                    
                    // zmiana statusu ------------ ** -------------
                    if (isset($_POST['status_' . $pole])) {
                        $status = (int)$_POST['status_' . $pole];
                      } else {
                        $status = 0;
                    }
                    $status = (($status == 1) ? '1' : '0');
                    $pola = array(array('products_status',(int)$status));
                    $sql = $db->update_query('products' , $pola, " products_id = '".(int)$pole."'");
                    unset($pola, $status);
                    
                    // czy nowosc ------------ ** -------------
                    if (isset($_POST['nowosc_' . $pole])) {
                        $wartosc = (int)$_POST['nowosc_' . $pole];
                      } else {
                        $wartosc = 0;
                    }                
                    $wartosc = (($wartosc == 1) ? '1' : '0');
                    $pola = array(array('new_status',$wartosc));
                    $sql = $db->update_query('products' , $pola, " products_id = '".(int)$pole."'");
                    unset($pola, $wartosc);

                    // czy nasz hit ------------ ** -------------
                    if (isset($_POST['hit_' . $pole])) {
                        $wartosc = (int)$_POST['hit_' . $pole];
                      } else {
                        $wartosc = 0;
                    }                     
                    $wartosc = (($wartosc == 1) ? '1' : '0');
                    $pola = array();
                    $pola[] = array('star_status',$wartosc);                    
                    if ($wartosc == '0') {
                        $pola[] = array('star_date','0000-00-00');
                        $pola[] = array('star_date_end','0000-00-00');
                    }
                    $sql = $db->update_query('products' , $pola, " products_id = '".(int)$pole."'");
                    unset($pola, $wartosc);
                    
                    // czy promocja ------------ ** -------------
                    if (isset($_POST['promocja_' . $pole])) {
                        $wartosc = (int)$_POST['promocja_' . $pole];
                      } else {
                        $wartosc = 0;
                    }                     
                    $wartosc = (($wartosc == 1) ? '1' : '0');
                    $pola = array();
                    $pola[] = array('specials_status',$wartosc);
                    if ($wartosc == '0') {
                        $pola[] = array('products_old_price','0');
                        $pola[] = array('specials_date','0000-00-00');
                        $pola[] = array('specials_date_end','0000-00-00');
                        for ($x = 2; $x <= ILOSC_CEN; $x++) {
                            //
                            $pola[] = array('products_old_price_'.$x,'0');
                            //
                        }                        
                    }                    
                    $sql = $db->update_query('products' , $pola, " products_id = '".(int)$pole."'");
                    unset($pola, $wartosc);     

                    // czy wyprzedaz ------------ ** -------------
                    if (isset($_POST['wyprzedaz_' . $pole])) {
                        $wartosc = (int)$_POST['wyprzedaz_' . $pole];
                      } else {
                        $wartosc = 0;
                    }                     
                    $wartosc = (($wartosc == 1) ? '1' : '0');
                    $pola = array();
                    $pola[] = array('sale_status',$wartosc);
                    if ($wartosc == '0') {
                        $pola[] = array('products_old_price','0');
                        for ($x = 2; $x <= ILOSC_CEN; $x++) {
                            //
                            $pola[] = array('products_old_price_'.$x,'0');
                            //
                        }                        
                    }                    
                    $sql = $db->update_query('products' , $pola, " products_id = '".(int)$pole."'");
                    unset($pola, $wartosc);
                    
                    // aktualizowanie dla cech przy wylaczonej promocji i wyprzedazy
                    if (!isset($_POST['wyprzedaz_' . $pole]) && !isset($_POST['promocja_' . $pole])) {
                        $pola = array();                      
                        $pola[] = array('products_stock_old_price','0');
                        for ($x = 2; $x <= ILOSC_CEN; $x++) {
                            //
                            $pola[] = array('products_stock_old_price_'.$x,'0');
                            //
                        }   
                        $sql = $db->update_query('products_stock' , $pola, " products_id = '".(int)$pole."'");
                        unset($pola);
                    }                                                            

                    // czy polecany ------------ ** -------------
                    if (isset($_POST['polecany_' . $pole])) {
                        $wartosc = (int)$_POST['polecany_' . $pole];
                      } else {
                        $wartosc = 0;
                    }                     
                    $wartosc = (($wartosc == 1) ? '1' : '0');
                    $pola = array();
                    $pola[] = array('featured_status',$wartosc);                    
                    if ($wartosc == '0') {
                        $pola[] = array('featured_date','0000-00-00');
                        $pola[] = array('featured_date_end','0000-00-00');
                    }                      
                    $sql = $db->update_query('products' , $pola, " products_id = '".(int)$pole."'");
                    unset($pola, $wartosc);

                    // czy do porownywarek ------------ ** -------------
                    if (isset($_POST['export_' . $pole])) {
                        $wartosc = (int)$_POST['export_' . $pole];
                      } else {
                        $wartosc = 0;
                    }                     
                    $wartosc = (($wartosc == 1) ? '1' : '0');
                    $pola = array(array('export_status',$wartosc));
                    $sql = $db->update_query('products' , $pola, " products_id = '".(int)$pole."'");
                    unset($pola, $wartosc);
                    
                    // do negocacji ceny ------------ ** -------------
                    if (isset($_POST['negocjacja_' . $pole])) {
                        $wartosc = (int)$_POST['negocjacja_' . $pole];
                      } else {
                        $wartosc = 0;
                    }                     
                    $wartosc = (($wartosc == 1) ? '1' : '0');
                    $pola = array(array('products_make_an_offer',$wartosc));
                    $sql = $db->update_query('products' , $pola, " products_id = '".(int)$pole."'");
                    unset($pola, $wartosc);
                    
                    // do darmowa wysylka ------------ ** -------------
                    if (isset($_POST['wysylka_' . $pole])) {
                        $wartosc = (int)$_POST['wysylka_' . $pole];
                      } else {
                        $wartosc = 0;
                    }                     
                    $wartosc = (($wartosc == 1) ? '1' : '0');
                    $pola = array(array('free_shipping_status',$wartosc));
                    //
                    if ( $wartosc == '0' ) {
                         $pola[] = array('free_shipping_status_customers_group_id','');
                    }
                    //
                    $sql = $db->update_query('products' , $pola, " products_id = '".(int)$pole."'");
                    unset($pola, $wartosc); 

                    // wyswietlanie w listingu ------------ ** -------------
                    if (isset($_POST['listing_' . $pole])) {
                        $wartosc = (int)$_POST['listing_' . $pole];
                      } else {
                        $wartosc = 0;
                    }                     
                    $wartosc = (($wartosc == 1) ? '1' : '0');
                    $pola = array(array('listing_status',$wartosc));
                    $sql = $db->update_query('products' , $pola, " products_id = '".(int)$pole."'");
                    unset($pola, $wartosc);   

                    // czy produkt mozna kupowac ------------ ** -------------
                    if (isset($_POST['kupowanie_' . $pole])) {
                        $wartosc = (int)$_POST['kupowanie_' . $pole];
                      } else {
                        $wartosc = 0;
                    }                     
                    $wartosc = (($wartosc == 1) ? '1' : '0');
                    $pola = array(array('products_buy',$wartosc));
                    $sql = $db->update_query('products' , $pola, " products_id = '".(int)$pole."'");
                    unset($pola, $wartosc);                     

                    // cena glowna produktu ------------ ** -------------
                    if (isset($_POST['cena_' . $pole]) && (int)$_POST['cena_' . $pole] > -0.01) {
                        //
                        // musi ustalic podatek vat
                        $zapytanie_vat_produktu = "select products_tax_class_id from products where products_id = '".$pole."'";
                        $sqls = $db->open_query($zapytanie_vat_produktu);
                        $infs = $sqls->fetch_assoc();
                        $db->close_query($sqls);
                        unset($zapytanie_vat_produktu);  
                        //                        
                        $wartosc = (float)$_POST['cena_' . $pole];
                        $netto = round(($wartosc / (1 + ($tablicaVat[$infs['products_tax_class_id']]/100))), 2);
                        $podatek = $wartosc - $netto;
                        //
                        unset($infs);
                        //
                        $pola = array(array('products_price_tax',(float)$wartosc),
                                      array('products_price',(float)$netto),
                                      array('products_tax',(float)$podatek));
                        $sql = $db->update_query('products' , $pola, " products_id = '".(int)$pole."'");
                        //
                        unset($pola, $wartosc, $netto, $podatek);
                    }        

                    // poprzednia cena glowna produktu ------------ ** -------------
                    if (isset($_POST['cenaold_' . $pole]) && (int)$_POST['cenaold_' . $pole] > 0 && (isset($_POST['promocja_' . $pole]) || isset($_POST['wyprzedaz_' . $pole]))) {
                        $wartosc = (float)$_POST['cenaold_' . $pole];
                        $pola = array(array('products_old_price',(float)$wartosc));
                        if ( isset($_POST['promocja_' . $pole]) ) {
                             $pola[] = array('specials_status','1');
                        }
                        if ( isset($_POST['wyprzedaz_' . $pole]) ) {
                             $pola[] = array('sale_status','1');
                        }                        
                        $sql = $db->update_query('products' , $pola, " products_id = '".(int)$pole."'");
                        unset($pola, $wartosc);
                    } else {
                        $pola = array(array('products_old_price','0'));
                        if ( isset($_POST['promocja_' . $pole]) ) {
                             $pola[] = array('specials_status','0');
                        }                        
                        $sql = $db->update_query('products' , $pola, " products_id = '".(int)$pole."'");
                        unset($pola, $wartosc);
                    }
                    
                    // ilosc produktu ------------ ** -------------
                    if (isset($_POST['ilosc_' . $pole])) {
                        $wartosc = (int)$_POST['ilosc_' . $pole];
                        $pola = array(array('products_quantity',(float)$wartosc));
                        $sql = $db->update_query('products' , $pola, " products_id = '".(int)$pole."'");
                        unset($pola, $wartosc);
                    } 
                    
                    $tablica = array(array('nr' => 1, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_1),
                                     array('nr' => 2, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_2),
                                     array('nr' => 3, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_3),
                                     array('nr' => 4, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_4),
                                     array('nr' => 5, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_5));                    
                               
                    foreach ( $tablica as $tmp ) {
                        //
                        if ( $tmp['aktywne'] == 'tak' ) {
                             //
                             $wartosc = 0;
                             //
                             if ( isset($_POST['ikona_' . $tmp['nr'] . '_' . $pole]) ) {
                                  //
                                  $wartosc = (int)$_POST['ikona_' . $tmp['nr'] . '_' . $pole];
                                  //
                             }
                             //
                             $pola = array(array('icon_' . $tmp['nr'] . '_status',(int)$wartosc));
                             $sql = $db->update_query('products' , $pola, " products_id = '".(int)$pole."'");
                             unset($pola, $wartosc);                             
                             //
                        }    
                        //
                    } 

                    unset($tablica);

                }
                
            }

        } else if (isset($_POST['akcja_dolna']) && (int)$_POST['akcja_dolna'] > 0) {
        
            if (isset($_POST['opcja'])) {
                //
                if (count($_POST['opcja']) > 0) {
            
                    foreach ($_POST['opcja'] as $pole) {
            
                        switch ((int)$_POST['akcja_dolna']) {
                            case 3:
                                // usuniecie produktow ------------ ** -------------
                                if ( (int)$_POST['usuniecie_produktow'] == 1 ) {
                                     Produkty::SkasujProdukt($pole);                      
                                }
                                break; 
                            case 4:
                                // usuniecie produktow i zdjec ------------ ** -------------
                                if ( (int)$_POST['usuniecie_produktow'] == 1 ) {
                                     Produkty::SkasujProdukt($pole, true);                      
                                }
                                break;                                  
                        }          

                    }
                
                }
                //
            }
            
    }
    
    if ( !isset($_GET['zestaw']) ) {  
    
         Funkcje::PrzekierowanieURL('produkty.php');
         
    } else {
      
        Funkcje::PrzekierowanieURL('zestawy_produktow.php');
        
    }
    
}
?>