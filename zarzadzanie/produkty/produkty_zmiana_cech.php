<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
  
    if ( isset($_POST['id_produktu_dla_cech']) && (int)$_POST['id_produktu_dla_cech'] > 0 ) {
      
         if ( isset($_POST['akcja_zmiana_cech']) && $_POST['akcja_zmiana_cech'] != 'brak' && isset($_POST['zmiana_cechy']) ) {
           
              // stawka vat produktu
              $zapytanie_vat = "select tx.tax_rate from tax_rates tx, products p where p.products_id = '" . (int)$_POST['id_produktu_dla_cech'] . "' and p.products_tax_class_id = tx.tax_rates_id";
              $sql_vat = $db->open_query($zapytanie_vat);                 
             
              $infv = $sql_vat->fetch_assoc();
              $stawka_vat = (float)$infv['tax_rate'];

              $db->close_query($sql_vat);
              unset($zapytanie_vat, $infv);               

              foreach ( $_POST['zmiana_cechy'] as $cecha ) {
                
                  $zapytanie = "select * from products_stock where products_id = '" . (int)$_POST['id_produktu_dla_cech'] . "' and products_stock_attributes = '" . $filtr->process($cecha) . "'";
                  $sql = $db->open_query($zapytanie);                 
                  //
                  $jest_cecha = false;
                  //
                  if ( (int)$db->ile_rekordow($sql) > 0 ) {
                       //
                       $info = $sql->fetch_assoc();
                       $jest_cecha = true;
                       //
                  }
                  //
                  $db->close_query($sql);

                  if ( $_POST['akcja_zmiana_cech'] == 'zmiana_cena_glowna' && $jest_cecha == true ) {
                      //
                      for ( $x = 1; $x <= ILOSC_CEN; $x++ ) {
                            //
                            if ( $info['products_stock_price_tax' . (($x > 1) ? '_' . $x : '')] > 0 && (float)$_POST['zmiana_cena_glowna_wartosc_' . $x] > 0 ) {
                                 //
                                 $cena = $info['products_stock_price_tax' . (($x > 1) ? '_' . $x : '')];
                                 $cena_brutto = 0;
                                 //
                                 if ( $_POST['zmiana_cena_glowna_rodzaj_' . $x] == 'procent' ) {
                                      //
                                      if ( $_POST['zmiana_glowna_cena_prefix_' . $x] == '+' ) {
                                           //
                                           $cena_brutto = $cena * ((100 + (float)$_POST['zmiana_cena_glowna_wartosc_' . $x]) / 100);
                                           //
                                      }
                                      if ( $_POST['zmiana_glowna_cena_prefix_' . $x] == '-' ) {
                                           //
                                           if ( (float)$_POST['zmiana_cena_glowna_wartosc_' . $x] > 100 ) {
                                                 $cena_brutto = 0;
                                           } else {
                                                 $cena_brutto = $cena * ((100 - (float)$_POST['zmiana_cena_glowna_wartosc_' . $x]) / 100);
                                           }
                                           //
                                      }                                 
                                      //
                                 }
                                 //
                                 if ( $_POST['zmiana_cena_glowna_rodzaj_' . $x] == 'kwota' ) {
                                      //
                                      if ( $_POST['zmiana_glowna_cena_prefix_' . $x] == '+' ) {
                                           //
                                           $cena_brutto = $cena + (float)$_POST['zmiana_cena_glowna_wartosc_' . $x];
                                           //
                                      }
                                      if ( $_POST['zmiana_glowna_cena_prefix_' . $x] == '-' ) {
                                           //
                                           $cena_brutto = $cena - (float)$_POST['zmiana_cena_glowna_wartosc_' . $x];
                                           //
                                      }                                 
                                      //
                                 }
                                 //
                                 $cena_netto = number_format(($cena_brutto / ((100 + $stawka_vat) / 100)),2,'.','');
                                 $cena_vat = $cena_brutto - $cena_netto;
                                 //                                 
                                 $pola = array(array('products_stock_price_tax' . (($x > 1) ? '_' . $x : ''), $cena_brutto),
                                               array('products_stock_price' . (($x > 1) ? '_' . $x : ''), $cena_netto),
                                               array('products_stock_tax' . (($x > 1) ? '_' . $x : ''), $cena_vat));
                                               
                                 $db->update_query('products_stock', $pola, 'products_stock_id = ' . $info['products_stock_id']);		
                                 unset($pola);                                      
                                 //  
                            }
                            //
                      }

                  }
                  
                  if ( $_POST['akcja_zmiana_cech'] == 'zmiana_cena_katalogowa' && $jest_cecha == true ) {
                      //
                      for ( $x = 1; $x <= ILOSC_CEN; $x++ ) {
                            //
                            if ( $info['products_stock_retail_price' . (($x > 1) ? '_' . $x : '')] > 0 && (float)$_POST['zmiana_cena_katalogowa_wartosc_' . $x] > 0 ) {
                                 //
                                 $cena = $info['products_stock_retail_price' . (($x > 1) ? '_' . $x : '')];
                                 $cena_brutto = 0;
                                 //
                                 if ( $_POST['zmiana_cena_katalogowa_rodzaj_' . $x] == 'procent' ) {
                                      //
                                      if ( $_POST['zmiana_cena_katalogowa_prefix_' . $x] == '+' ) {
                                           //
                                           $cena_brutto = $cena * ((100 + (float)$_POST['zmiana_cena_katalogowa_wartosc_' . $x]) / 100);
                                           //
                                      }
                                      if ( $_POST['zmiana_cena_katalogowa_prefix_' . $x] == '-' ) {
                                           //
                                           if ( (float)$_POST['zmiana_cena_katalogowa_wartosc_' . $x] > 100 ) {
                                                 $cena_brutto = 0;
                                           } else {
                                                 $cena_brutto = $cena * ((100 - (float)$_POST['zmiana_cena_katalogowa_wartosc_' . $x]) / 100);
                                           }
                                           //
                                      }                                 
                                      //
                                      
                                 }
                                 //
                                 if ( $_POST['zmiana_cena_katalogowa_rodzaj_' . $x] == 'kwota' ) {
                                      //
                                      if ( $_POST['zmiana_cena_katalogowa_prefix_' . $x] == '+' ) {
                                           //
                                           $cena_brutto = $cena + (float)$_POST['zmiana_cena_katalogowa_wartosc_' . $x];
                                           //
                                      }
                                      if ( $_POST['zmiana_cena_katalogowa_prefix_' . $x] == '-' ) {
                                           //
                                           $cena_brutto = $cena - (float)$_POST['zmiana_cena_katalogowa_wartosc_' . $x];
                                           //
                                      }                                 
                                      //
                                 }
                                 //                                 
                                 $pola = array(array('products_stock_retail_price' . (($x > 1) ? '_' . $x : ''), $cena_brutto));
                                 $db->update_query('products_stock', $pola, 'products_stock_id = ' . $info['products_stock_id']);		
                                 unset($pola);                                      
                                 //  
                            }
                            //
                      }
                      
                  }

                  if ( $_POST['akcja_zmiana_cech'] == 'zmiana_cena_poprzednia' && $jest_cecha == true ) {
                      //
                      for ( $x = 1; $x <= ILOSC_CEN; $x++ ) {
                            //
                            if ( $info['products_stock_old_price' . (($x > 1) ? '_' . $x : '')] > 0 && (float)$_POST['zmiana_cena_poprzednia_wartosc_' . $x] > 0 ) {
                                 //
                                 $cena = $info['products_stock_old_price' . (($x > 1) ? '_' . $x : '')];
                                 $cena_brutto = 0;
                                 //
                                 if ( $_POST['zmiana_cena_poprzednia_rodzaj_' . $x] == 'procent' ) {
                                      //
                                      if ( $_POST['zmiana_cena_poprzednia_prefix_' . $x] == '+' ) {
                                           //
                                           $cena_brutto = $cena * ((100 + (float)$_POST['zmiana_cena_poprzednia_wartosc_' . $x]) / 100);
                                           //
                                      }
                                      if ( $_POST['zmiana_cena_poprzednia_prefix_' . $x] == '-' ) {
                                           //
                                           if ( (float)$_POST['zmiana_cena_poprzednia_wartosc_' . $x] > 100 ) {
                                                 $cena_brutto = 0;
                                           } else {
                                                 $cena_brutto = $cena * ((100 - (float)$_POST['zmiana_cena_poprzednia_wartosc_' . $x]) / 100);
                                           }
                                           //
                                      }                                 
                                      //
                                      
                                 }
                                 //
                                 if ( $_POST['zmiana_cena_poprzednia_rodzaj_' . $x] == 'kwota' ) {
                                      //
                                      if ( $_POST['zmiana_cena_poprzednia_prefix_' . $x] == '+' ) {
                                           //
                                           $cena_brutto = $cena + (float)$_POST['zmiana_cena_poprzednia_wartosc_' . $x];
                                           //
                                      }
                                      if ( $_POST['zmiana_cena_poprzednia_prefix_' . $x] == '-' ) {
                                           //
                                           $cena_brutto = $cena - (float)$_POST['zmiana_cena_poprzednia_wartosc_' . $x];
                                           //
                                      }                                 
                                      //
                                 }
                                 //                                 
                                 $pola = array(array('products_stock_old_price' . (($x > 1) ? '_' . $x : ''), $cena_brutto));
                                 $db->update_query('products_stock', $pola, 'products_stock_id = ' . $info['products_stock_id']);		
                                 unset($pola);                                      
                                 //  
                            }
                            //
                      }

                  }     

                  if ( $_POST['akcja_zmiana_cech'] == 'zmiana_dostepnosci' ) {
                       //
                       $pola = array(array('products_stock_availability_id', (int)$_POST['dostepnosci_cechy']));
                       //
                       if ( $jest_cecha == true ) {
                            //
                            $db->update_query('products_stock', $pola, 'products_stock_id = ' . $info['products_stock_id']);		
                            //
                       }
                       if ( $jest_cecha == false ) {
                            //
                            $pola[] = array('products_id', (int)$_POST['id_produktu_dla_cech']);
                            $pola[] = array('products_stock_attributes', $filtr->process($cecha));
                            //
                            $db->insert_query('products_stock', $pola);		
                            //
                       }                            
                       unset($pola);                                      
                       //  
                  }      

                  if ( $_POST['akcja_zmiana_cech'] == 'zmiana_czasu_wysylki' ) {
                       //
                       $pola = array(array('products_stock_shipping_time_id', (int)$_POST['czas_wysylki_cechy']));
                       //
                       if ( $jest_cecha == true ) {
                            //
                            $db->update_query('products_stock', $pola, 'products_stock_id = ' . $info['products_stock_id']);		
                            //
                       }
                       if ( $jest_cecha == false ) {
                            //
                            $pola[] = array('products_id', (int)$_POST['id_produktu_dla_cech']);
                            $pola[] = array('products_stock_attributes', $filtr->process($cecha));
                            //
                            $db->insert_query('products_stock', $pola);		
                            //
                       }                            
                       unset($pola);                                      
                       //  
                  }         
                  
                  if ( $_POST['akcja_zmiana_cech'] == 'zmiana_ilosci' ) {
                       //
                       $pola = array(array('products_stock_quantity', (float)$_POST['zmiana_ilosci_wartosc']));
                       //
                       if ( $jest_cecha == true ) {
                            //
                            $db->update_query('products_stock', $pola, 'products_stock_id = ' . $info['products_stock_id']);		
                            //
                       }
                       if ( $jest_cecha == false ) {
                            //
                            $pola[] = array('products_id', (int)$_POST['id_produktu_dla_cech']);
                            $pola[] = array('products_stock_attributes', $filtr->process($cecha));
                            //
                            $db->insert_query('products_stock', $pola);		
                            //
                       }                            
                       unset($pola);                                      
                       //  
                  } 
                  
                  unset($zapytanie, $info);
                  
              }

         }

         Funkcje::PrzekierowanieURL('/zarzadzanie/produkty/produkty_edytuj.php?id_poz=' . (int)$_POST['id_produktu_dla_cech'] . '&zakladka=5');
      
    }
  
}

?>