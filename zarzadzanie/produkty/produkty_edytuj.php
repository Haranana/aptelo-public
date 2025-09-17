<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz_edycje') {
    
        // ustalanie ilosci zdjec
        $pola_zdjec = array();
        for ($r = 1; $r < 200; $r++) {
            if (isset($_POST['zdjecie_'.$r]) && !empty($_POST['zdjecie_'.$r])) {
                $pola_zdjec[] = array('zdjecie' => $filtr->process($_POST['zdjecie_'.$r]),
                                      'alt' => $filtr->process($_POST['alt_'.$r]),
                                      'sort' => (int)$_POST['sort_'.$r]);
            }
        }
        
        $ilosc_produktow = $filtr->process($_POST['ilosc']);
        
        // sprawdzanie czy nie wylaczyc produktu jezeli sa daty dostepnosci
        if ( (int)$_POST['status'] == 1 ) {
             //
             if ((int)$_POST['data_dostepnosci_status'] == 1) {
                 //
                 if ( date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_dostepnosci']))) > date('Y-m-d') ) {
                      //
                      $_POST['status'] = 0;
                      //
                 }
                 //
             }
             if (!empty($_POST['data_dostepnosci_koniec'])) {
                 //
                 if ( date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_dostepnosci_koniec']))) < date('Y-m-d') ) {
                      //
                      $_POST['status'] = 0;
                      //
                 }
                 //
             }                 
             //
        }        
    
        $pola = array(
                array('products_status',(int)$_POST['status']),
                array('listing_status',(int)$_POST['listing']), 
                array('products_buy',(int)$_POST['kupowanie']),
                array('products_fast_buy',(int)$_POST['szybki_zakup']),
                array('products_price_login',(int)$_POST['cena_zalogowanych']),
                array('products_counting_points',(int)$_POST['pkt_naliczanie']),
                array('products_not_discount',(int)$_POST['rabaty']),
                array('products_control_storage',(int)$_POST['magazyn']),
                array('products_accessory',(int)$_POST['akcesoria']),                
                array('products_points_only',(((int)$_POST['ilosc_pkt'] > 0 && (int)$_POST['kupowanie_pkt'] == 1) ? 1 : 0)),
                array('products_points_value',(((int)$_POST['kupowanie_pkt'] == 1) ? (int)$_POST['ilosc_pkt'] : 0)),               
                array('products_points_value_money',(((int)$_POST['kupowanie_pkt'] == 1) ? (((float)$_POST['stala_kwota'] < 0.01) ? 0.01 : (float)$_POST['stala_kwota']) : 0)),               
                array('products_points_purchase',(int)$_POST['zakup_pkt']),
                array('products_date_added',((trim((string)$_POST['data_dodania']) != '') ? date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_dodania']))) : 'now()')),
                array('customers_group_id',((isset($_POST['grupa_klientow'])) ? implode(',', (array)$_POST['grupa_klientow']) : 0)),
                array('not_customers_group_id',((isset($_POST['nie_grupa_klientow'])) ? implode(',', (array)$_POST['nie_grupa_klientow']) : 0)),
                array('sort_order',(int)$_POST['sort']),
                array('products_model',$filtr->process($_POST['nr_kat'])),
                array('products_man_code',$filtr->process($_POST['kod_producenta'])),
                array('products_id_private',$filtr->process($_POST['nr_kat_klienta'])),
                array('products_ean',$filtr->process($_POST['nr_ean'])),
                array('products_pkwiu',$filtr->process($_POST['pkwiu'])),
                array('products_gtu',$filtr->process($_POST['gtu'])),
                array('products_safety_information',$filtr->process($_POST['link_o_bezpieczenstwie'])),
                array('products_plu_code',$filtr->process($_POST['kod_plu'])),
                array('products_weight',(float)$_POST['waga']),
                array('products_weight_width',(int)$_POST['waga_szerokosc']),
                array('products_weight_height',(int)$_POST['waga_wysokosc']),
                array('products_weight_length',(int)$_POST['waga_dlugosc']),
                array('products_date_available',((!empty($_POST['data_dostepnosci'])) ? date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_dostepnosci']))) : '0000-00-00')),
                array('products_date_available_end',((!empty($_POST['data_dostepnosci_koniec'])) ? date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_dostepnosci_koniec']))) : '0000-00-00')),
                array('products_date_available_buy',((!empty($_POST['data_sprzedazy'])) ? date('Y-m-d H:i', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_sprzedazy']))) : '0000-00-00')),
                array('products_date_available_clock',(int)$_POST['zegar_sprzedaz']),
                array('products_date_available_status',(int)$_POST['data_dostepnosci_status']),
                array('manufacturers_id',(int)$_POST['producent']),
                array('products_availability_id',(int)$_POST['dostepnosci']),
                array('products_shipping_time_id',(int)$_POST['wysylka']),
                array('products_condition_products_id',(int)$_POST['stan_produktu']),
                array('products_warranty_products_id',(int)$_POST['gwarancja']),
                array('products_type',$filtr->process($_POST['rodzaj_produktu'])),
                array('products_quantity',(float)$ilosc_produktow),               
                array('products_quantity_alarm',(float)$_POST['alarm_ilosc']),
                array('products_quantity_max_alarm',(float)$_POST['alarm_max_ilosc']),
                array('location',$filtr->process($_POST['pozycja_magazyn'])),
                array('products_jm_id',$filtr->process($_POST['jednostka_miary'])),
                array('products_size',(float)$_POST['wielkosc_produktu']),
                array('products_size_type',$filtr->process($_POST['wielkosc_produktu_jm'])),
                array('products_pack_type',(int)$_POST['gabaryt']),
                array('products_separate_package',(int)$_POST['osobna_paczka']),
                array('products_separate_package_quantity',(int)$_POST['osobna_paczka_ilosc']),
                array('products_comments',$filtr->process($_POST['komentarz'])),
                array('products_minorder',(float)$_POST['min_ilosc']),
                array('products_maxorder',(float)$_POST['max_ilosc']),
                array('products_quantity_order',(float)$_POST['ilosc_zbiorcza']),
                array('shipping_cost',(float)$_POST['koszt_wysylki']),
                array('shipping_cost_quantity',(int)$_POST['koszt_wysylki_ilosc']),
                array('shipping_cost_delivery',(float)$_POST['koszt_wysylki_pobranie']),
                array('products_adminnotes',$filtr->process($_POST['notatki'])),
                array('inpost_size',$filtr->process($_POST['inpost_rodzaj_gabarytu'])),
                array('inpost_quantity',(((int)$_POST['inpost_ilosc_paczka'] > 0) ? (int)$_POST['inpost_ilosc_paczka'] : 1)),                
                array('products_other_variant_text',$filtr->process($_POST['inny_wariant_text'])),
                array('products_other_variant_range',$filtr->process($_POST['inny_wariant'])),
                array('products_other_variant_method',$filtr->process($_POST['inny_wariant_sposob'])),
                array('products_other_variant_image',((isset($_POST['inny_wariant_foto']) ? (int)$_POST['inny_wariant_foto'] : '0'))),
                array('products_other_variant_name',((isset($_POST['inny_wariant_nazwa']) ? (int)$_POST['inny_wariant_nazwa'] : '0'))),
                array('products_other_variant_name_type',((isset($_POST['inny_wariant_nazwa_typ']) ? (int)$_POST['inny_wariant_nazwa_typ'] : '0'))),
                array('products_other_variant_price',((isset($_POST['inny_wariant_cena']) ? (int)$_POST['inny_wariant_cena'] : '0'))));                   
           
        if ( (int)$_POST['min_ilosc_czas_wybor'] == 1 && (float)$_POST['min_ilosc_czas'] > 0 && Funkcje::czyNiePuste($_POST['data_min_ilosc_od']) && Funkcje::czyNiePuste($_POST['data_min_ilosc_do']) ) {
             //
             $pola[] = array('products_minorder_time',(float)$_POST['min_ilosc_czas']);
             $pola[] = array('products_minorder_date',date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_min_ilosc_od']))));
             $pola[] = array('products_minorder_date_end',date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_min_ilosc_do']))));
             //
        } else {
             //
             $pola[] = array('products_minorder_time',0);
             $pola[] = array('products_minorder_date','0000-00-00 00:00:00');
             $pola[] = array('products_minorder_date_end','0000-00-00 00:00:00');
             //
        }
        
        if ( (int)$_POST['max_ilosc_czas_wybor'] == 1 && (float)$_POST['max_ilosc_czas'] > 0 && Funkcje::czyNiePuste($_POST['data_max_ilosc_od']) && Funkcje::czyNiePuste($_POST['data_max_ilosc_do']) ) {
             //
             $pola[] = array('products_maxorder_time',(float)$_POST['max_ilosc_czas']);
             $pola[] = array('products_maxorder_date',date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_max_ilosc_od']))));
             $pola[] = array('products_maxorder_date_end',date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_max_ilosc_do']))));
             //
        } else {
             //
             $pola[] = array('products_maxorder_time',0);
             $pola[] = array('products_maxorder_date','0000-00-00 00:00:00');
             $pola[] = array('products_maxorder_date_end','0000-00-00 00:00:00');
             //
        }
        
        if ( isset($_POST['klasa_energetyczna']) ) {
             //
             $pola[] = array('products_energy',$filtr->process($_POST['klasa_energetyczna']));
             $pola[] = array('products_min_energy',(($_POST['klasa_energetyczna'] != '') ? $filtr->process($_POST['klasa_energetyczna_min']) : ''));
             $pola[] = array('products_max_energy',(($_POST['klasa_energetyczna'] != '') ? $filtr->process($_POST['klasa_energetyczna_max']) : ''));
             $pola[] = array('products_energy_img',(($_POST['klasa_energetyczna'] != '') ? $filtr->process($_POST['klasa_energetyczna_etykieta']) : ''));
             $pola[] = array('products_energy_pdf',(($_POST['klasa_energetyczna'] != '') ? $filtr->process($_POST['klasa_energetyczna_karta']) : ''));
             //
        }
        
        if ( isset($_POST['kody_cyfrowe']) ) {
             //
             $pola[] = array('products_code_shopping',$filtr->process($_POST['kody_cyfrowe']));
             //
        }
                
        // nr referencyjne
        for ($r = 1; $r < 6; $r++) {
            if (isset($_POST['nr_referencyjny_'.$r])) {
                $pola[] = array('products_reference_number_' . $r, $filtr->process($_POST['nr_referencyjny_'.$r]));
                if ( !empty($_POST['nr_referencyjny_'.$r]) ) {
                     $pola[] = array('products_reference_number_' . $r . '_description', $filtr->process($_POST['nr_referencyjny_'.$r.'_opis']));
                } else {
                     $pola[] = array('products_reference_number_' . $r . '_description', '');
                }
            }
        } 
                
        // id podatku
        $stawka_vat = explode('|', (string)$filtr->process($_POST['vat']));
        $pola[] = array('products_tax_class_id',$stawka_vat[1]);
        //         
                
                
        // pierwsze zdjecie produktu
        if (count($pola_zdjec) > 0) {
            $pola[] = array('products_image',$pola_zdjec[0]['zdjecie']);
            $pola[] = array('products_image_description',$pola_zdjec[0]['alt']);
          } else {
            $pola[] = array('products_image','');
            $pola[] = array('products_image_description','');          
        }
        
        // ceny produktu
        $pola[] = array('products_price',(float)$_POST['cena_1']);
        $pola[] = array('products_tax',(float)$_POST['v_at_1']);
        $pola[] = array('products_price_tax',(float)$_POST['brut_1']);        
        $pola[] = array('products_retail_price',(float)$_POST['cena_katalogowa_1']); 
        $pola[] = array('products_purchase_price',(float)$_POST['cena_zakupu']);
      
        // ceny
        for ($x = 2; $x <= ILOSC_CEN; $x++) {
            if (isset($_POST['cena_'.$x]) && isset($_POST['v_at_'.$x]) && isset($_POST['brut_'.$x])) {
                $pola[] = array('products_price_'.$x,(float)$_POST['cena_'.$x]);
                $pola[] = array('products_tax_'.$x,(float)$_POST['v_at_'.$x]);
                $pola[] = array('products_price_tax_'.$x,(float)$_POST['brut_'.$x]);
            }
            $pola[] = array('products_retail_price_'.$x,(float)$_POST['cena_katalogowa_'.$x]);
        }
        
        $pola[] = array('products_currencies_id',(int)$_POST['waluta']);
        // nowosci
        if (isset($_POST['nowosc'])) {
            $pola[] = array('new_status',(int)$_POST['nowosc']);
        } else {
            $pola[] = array('new_status','0');
        }
        // nasz hit
        if (isset($_POST['hit']) && $_POST['hit'] == '1') {
            $pola[] = array('star_status',$filtr->process($_POST['hit']));
            if (!empty($_POST['data_hit_od'])) {
                $pola[] = array('star_date',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_hit_od']))));
              } else {
                $pola[] = array('star_date','0000-00-00');                     
            }
            if (!empty($_POST['data_hit_do'])) {
                $pola[] = array('star_date_end',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_hit_do']))));
              } else {
                $pola[] = array('star_date_end','0000-00-00');                  
            }
        } else {
            $pola[] = array('star_status','0');
            $pola[] = array('star_date','0000-00-00');
            $pola[] = array('star_date_end','0000-00-00');        
        }
        // polecany
        if (isset($_POST['polecany']) && $_POST['polecany'] == '1') {
            $pola[] = array('featured_status',(int)$_POST['polecany']);
            if (!empty($_POST['data_polecany_od'])) {
                $pola[] = array('featured_date',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_polecany_od']))));
              } else {
                $pola[] = array('featured_date','0000-00-00');                  
            }
            if (!empty($_POST['data_polecany_do'])) {
                $pola[] = array('featured_date_end',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_polecany_do']))));  
              } else {
                $pola[] = array('featured_date_end','0000-00-00');                     
            }
        } else {
            $pola[] = array('featured_status','0');
            $pola[] = array('featured_date','0000-00-00');
            $pola[] = array('featured_date_end','0000-00-00');      
        }
        // promocja
        $byla_poprzednia = false;
        //        
        if (isset($_POST['promocja']) && !empty($_POST['cena_poprzednia']) && $_POST['promocja'] == '1') {
        
            $pola[] = array('products_old_price',(float)$_POST['cena_poprzednia']);
            $byla_poprzednia = true;
            
            // ceny dla pozostalych poziomow cen
            for ($x = 2; $x <= ILOSC_CEN; $x++) {
                if (isset($_POST['cena_poprzednia_'.$x])) {
                    $pola[] = array('products_old_price_'.$x,(float)$_POST['cena_poprzednia_'.$x]);
                }
            }            
            
            $pola[] = array('specials_status',(int)$_POST['promocja']);
            if (!empty($_POST['data_promocja_od'])) {
                $pola[] = array('specials_date',date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_promocja_od']))));
              } else {
                $pola[] = array('specials_date','0000-00-00');                    
            }
            if (!empty($_POST['data_promocja_do'])) {
                $pola[] = array('specials_date_end',date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_promocja_do']))));
              } else {
                $pola[] = array('specials_date_end','0000-00-00');                  
            }
        } else {      
            $pola[] = array('specials_status','0');
            $pola[] = array('specials_date','0000-00-00 00:00:00');
            $pola[] = array('specials_date_end','0000-00-00 00:00:00'); 
        }
        
        // wyprzedaz
        if (isset($_POST['wyprzedaz']) && $_POST['wyprzedaz'] == '1') {

            $pola[] = array('sale_status',(int)$_POST['wyprzedaz']);
            
            if ( $byla_poprzednia == false ) {
              
                $pola[] = array('products_old_price',(float)$_POST['cena_poprzednia']);
                
                // ceny dla pozostalych poziomow cen
                for ($x = 2; $x <= ILOSC_CEN; $x++) {
                    if (isset($_POST['cena_poprzednia_'.$x])) {
                        $pola[] = array('products_old_price_'.$x,(float)$_POST['cena_poprzednia_'.$x]);
                    }
                } 
                
            }
            
        } else {
          
            $pola[] = array('sale_status','0');
          
        }
        unset($byla_poprzednia);
        
        // usunie tez dane w products_stock jezeli nie ma promocji i wyprzedazy
        if ( !isset($_POST['wyprzedaz']) && !isset($_POST['promocja']) ) {
                    
            $pola_stock = array(array('products_stock_old_price','0'));
            //
            for ($x = 2; $x <= ILOSC_CEN; $x++) {
                //
                $pola_stock[] = array('products_stock_old_price_'.$x,'0');
                //
            }
            //
            
            $db->update_query('products_stock' , $pola_stock, " products_id = '" . (int)$_POST['id_produktu'] . "'");             
            
            // usuwa ceny poprzednie z produktu
            $pola[] = array('products_old_price','0');
            //
            // ceny dla pozostalych poziomow cen
            for ($x = 2; $x <= ILOSC_CEN; $x++) {
                if (isset($_POST['cena_poprzednia_'.$x])) {
                    $pola[] = array('products_old_price_'.$x,'0');
                }
            }               
            
        }            
        
        // porownywarki
        if (isset($_POST['export'])) {
            $pola[] = array('export_status',$filtr->process($_POST['export']));   
            $pola[] = array('export_status_exclude','0');  
            if ( isset($_POST['porownywarka']) && count($_POST['porownywarka']) > 0 ) {
                 $pola[] = array('export_id',',' . implode(',', (array)$_POST['porownywarka']) . ',');
            } else {
                 $pola[] = array('export_id','');  
            }
        } else {
            $pola[] = array('export_status','0');  
            $pola[] = array('export_id','');  
        }
        if (isset($_POST['porownywarki_wykluczony']) && !isset($_POST['export'])) {
            $pola[] = array('export_status','0');  
            $pola[] = array('export_id','');  
            $pola[] = array('export_status_exclude','1');  
        } else {
            $pola[] = array('export_status_exclude','0');  
        }
        if (isset($_POST['porownywarki_ceneo_kup_teraz']) && (int)$_POST['porownywarki_ceneo_kup_teraz'] == 1 ) {
            $pola[] = array('export_ceneo_buy_now','1');  
        } else {
            $pola[] = array('export_ceneo_buy_now','0');  
        }

        // negocjacja
        if (isset($_POST['negocjacja'])) {
            $pola[] = array('products_make_an_offer',(int)$_POST['negocjacja']);         
        } else {
            $pola[] = array('products_make_an_offer','0');           
        }
        // darmowa dostawa
        if (isset($_POST['darmowa_dostawa'])) {
            $pola[] = array('free_shipping_status',(int)$_POST['darmowa_dostawa']); 
            $pola[] = array('free_shipping_status_customers_group_id',((isset($_POST['darmowa_wysylka_grupa_klientow'])) ? implode(',', (array)$_POST['darmowa_wysylka_grupa_klientow']) : ''));
        } else {
            $pola[] = array('free_shipping_status','0');               
            $pola[] = array('free_shipping_status_customers_group_id','');
        }
        // wykluczenie darmowej dostawy
        if (isset($_POST['darmowa_dostawa_wykluczona'])) {
            $pola[] = array('free_shipping_excluded',(int)$_POST['darmowa_dostawa_wykluczona']); 
        } else {
            $pola[] = array('free_shipping_excluded','0');               
        }        
        // wykluczenie punktu odbioru
        if (isset($_POST['odbior_punkt_wykluczony'])) {
            $pola[] = array('pickup_excluded',$filtr->process($_POST['odbior_punkt_wykluczony']));               
        } else {
            $pola[] = array('pickup_excluded','0');               
        } 
        
        // znizki zalezne od ilosci
        $znizki_do_zapisu = array();
        //
        if ( $_POST['rodzaj_znizki'] == 'procent' ) {
            //
            for ($w = 1; $w < 100; $w++) {
                if (isset($_POST['znizki_od_'.$w]) && isset($_POST['znizki_do_'.$w]) && isset($_POST['znizki_wart_'.$w])) {
                    if ((float)$_POST['znizki_od_'.$w] > 0 && (float)$_POST['znizki_do_'.$w] > 0 && (float)$_POST['znizki_wart_'.$w] > 0) {
                        $znizki_do_zapisu[] = (float)$_POST['znizki_od_'.$w] . ":" . (float)$_POST['znizki_do_'.$w] . ":" . (float)$_POST['znizki_wart_'.$w];
                    }
                }
            }
            //
        }
        if ( $_POST['rodzaj_znizki'] == 'cena' ) {
            //
            for ($w = 1; $w < 100; $w++) {
                if (isset($_POST['znizki_od_'.$w]) && isset($_POST['znizki_do_'.$w]) && (float)$_POST['znizki_od_'.$w] > 0 && (float)$_POST['znizki_do_'.$w] > 0) {
                    //
                    $zapisz = true;
                    for ($x = 1; $x <= ILOSC_CEN; $x++) {
                         if ( !isset($_POST['znizki_wart_'.$x.'_'.$w]) || (float)$_POST['znizki_wart_'.$x.'_'.$w] <= 0 ) {
                              $zapisz = false;
                         }
                    }
                    //
                    if ( $zapisz == true ) {
                         //
                         $znizki_do_zapisu_tmp = $filtr->process($_POST['znizki_od_'.$w]) . ":" . $filtr->process($_POST['znizki_do_'.$w]);
                         //
                         for ($x = 1; $x <= ILOSC_CEN; $x++) {
                              $znizki_do_zapisu_tmp .= ':' . $filtr->process($_POST['znizki_wart_'.$x.'_'.$w]);
                         }
                         //
                         $znizki_do_zapisu[] = $znizki_do_zapisu_tmp;
                         unset($znizki_do_zapisu_tmp);
                         //
                    }
                }
            }
            //
        }        
        $pola[] = array('products_discount',implode(';', (array)$znizki_do_zapisu));         
        unset($znizki_do_zapisu);
        //
        $pola[] = array('products_discount_type',$filtr->process($_POST['rodzaj_znizki'])); 
        //
        if (!empty($_POST['data_znizki_ilosci_od'])) {
            $pola[] = array('products_discount_date',date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_znizki_ilosci_od']))));
          } else {
            $pola[] = array('products_discount_date','0000-00-00');                    
        }
        if (!empty($_POST['data_znizki_ilosci_do'])) {
            $pola[] = array('products_discount_date_end',date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_znizki_ilosci_do']))));
          } else {
            $pola[] = array('products_discount_date_end','0000-00-00');                    
        }
        //
        $pola[] = array('products_discount_group_id',((isset($_POST['znizki_grupy_klientow'])) ? implode(',', (array)$_POST['znizki_grupy_klientow']) : ''));    
        
        // dostepne wysylki
        if (isset($_POST['metody_wysylki'])) {
          $dostepne_wysylki = implode(';', (array)$filtr->process($_POST['metody_wysylki']));
          $pola[] = array('shipping_method',$dostepne_wysylki);         
        } else {
          $pola[] = array('shipping_method','');         
        }
        
        // dane do zestawu produktow --------------------------------
        if ( isset($_POST['id_zestawu']) && isset($_POST['id_zestaw']) && count($_POST['id_zestaw']) > 0 ) {
             //
             $tablica_zestawu = array();
             //
             foreach ( $_POST['id_zestaw'] as $idtmp ) {
                //
                $tablica_zestawu[ $idtmp ] = array('rabat_kwota' => $_POST['rabat_kwota_' . $idtmp], 'rabat_procent' => $_POST['rabat_procent_' . $idtmp], 'rabat_ilosc' => $_POST['rabat_ilosc_' . $idtmp]); 
                //
             }
             //            
             $pola[] = array('products_set_products', serialize($tablica_zestawu));
             $pola[] = array('products_set', 1);
             //
             unset($tablica_zestawu);
             //
        }
        // dane do zestawu produktow --------------------------------
        
        // integracja z automater
        if ( INTEGRACJA_AUTOMATER_WLACZONY == 'tak' ) {
             //
             $pola[] = array('automater_products_id', (int)$_POST['produkt_automater']);
             //
        }

        $sql = $db->update_query('products' , $pola, 'products_id = ' . (int)$_POST['id_produktu']);
        $id_edytowanej_pozycji = (int)$_POST['id_produktu'];
        unset($pola);
        
        $ile_jezykow = Funkcje::TablicaJezykow();
        
        // ---------------------------------- description
        
        // kasuje rekordy w tablicy
        $db->delete_query('products_description' , " products_id = '".$id_edytowanej_pozycji."'");           
        $nazwa_domyslna = '';
        
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            // jezeli nazwa w innym jezyku nie jest wypelniona
            if ( $w > 0 ) {
                if (empty($_POST['nazwa_'.$w])) {
                    $_POST['nazwa_'.$w] = $_POST['nazwa_0'];
                }
            }
            //       
            $pola = array(
                    array('products_id',(int)$id_edytowanej_pozycji),
                    array('language_id',(int)$ile_jezykow[$w]['id']),
                    array('products_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('products_name_info',$filtr->process($_POST['nazwa_info_'.$w])),
                    array('products_name_short',$filtr->process($_POST['nazwa_krotka_'.$w])),
                    array('products_description',$filtr->process($_POST['opis_'.$w])),
                    array('products_short_description',$filtr->process($_POST['opis_krotki_'.$w])),        
                    array('products_meta_title_tag',$filtr->process($_POST['tytul_meta_'.$w])),
                    array('products_meta_desc_tag',$filtr->process($_POST['opis_meta_'.$w])),
                    array('products_meta_keywords_tag',$filtr->process($_POST['slowa_meta_'.$w])),
                    array('products_seo_url',$filtr->process($_POST['url_meta_'.$w])),
                    array('products_link_canonical',$filtr->process($_POST['link_kanoniczny_'.$w])),
                    array('products_search_tag',$filtr->process($_POST['slowa_szukaj_'.$w])));

            if ( $w == 0 ) {
                 //
                 $pola[] = array('products_review_summary',((isset($_POST['podsumowanie_recenzji'])) ? $filtr->process($_POST['podsumowanie_recenzji']) : ''));
                 //
            }

            if ( trim((string)$_POST['og_title_'.$w]) != '' && trim((string)$_POST['og_description_'.$w]) ) {
                 //
                 $pola[] = array('products_og_title',$filtr->process($_POST['og_title_'.$w]));
                 $pola[] = array('products_og_description',$filtr->process($_POST['og_description_'.$w]));
                 //
            }                    
                    
            $sql = $db->insert_query('products_description' , $pola);
            unset($pola);
            //
            if ( $_SESSION['domyslny_jezyk']['id'] == $ile_jezykow[$w]['id'] ) {
                 $nazwa_domyslna = ((!empty($_POST['url_meta_'.$w])) ? $filtr->process($_POST['url_meta_'.$w]) : $filtr->process($_POST['nazwa_'.$w]));
            }
            //            
        }
        
        // ---------------------------------- description_additional
        
        // kasuje rekordy w tablicy
        $db->delete_query('products_description_additional' , " products_id = '".$id_edytowanej_pozycji."'");           

        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //    
            $pola = array(
                    array('products_id',(int)$id_edytowanej_pozycji),
                    array('language_id',(int)$ile_jezykow[$w]['id']),
                    array('products_info_description_1',$filtr->process($_POST['opis_dodatkowy_1_'.$w])),
                    array('products_info_description_2',$filtr->process($_POST['opis_dodatkowy_2_'.$w])));                  
                    
            $sql = $db->insert_query('products_description_additional' , $pola);
            unset($pola);
            //          
        }        
        
        // ---------------------------------- pytania i odpowiedzi faq
        
        if ( isset($_POST['ile_faq_0']) ) {
        
            // kasuje rekordy w tablicy
            $db->delete_query('faq' , " faq_type = 'produkt' and faq_type_id = '".$id_edytowanej_pozycji."'");          
            
            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                //
                for ($q = 1; $q <= (int)($_POST['ile_faq_'.$w]); $q++) {
                    //
                    if ((!empty($_POST['pytanie_'.$q.'_'.$w]) && !empty($_POST['pytanie_'.$q.'_'.$w])) && (!empty($_POST['odpowiedz_'.$q.'_'.$w]) && !empty($_POST['odpowiedz_'.$q.'_'.$w]))) {
                        //               
                        $pola = array(
                                array('faq_type','produkt'),
                                array('faq_type_id',$id_edytowanej_pozycji),
                                array('language_id',$ile_jezykow[$w]['id']),
                                array('sort',(int)$_POST['pytanie_sort_'.$q.'_'.$w]),
                                array('faq_question',$filtr->process($_POST['pytanie_'.$q.'_'.$w])),
                                array('faq_reply',$filtr->process($_POST['odpowiedz_'.$q.'_'.$w]))
                                );        
                        $sql = $db->insert_query('faq', $pola);
                        unset($pola);
                    }
                } 
                //
            } 
            
        }        
        
        // -------------------------------- przekierowanie ze starego sklepu i przekierowanie jak nieaktywny
        
        // kasuje rekordy w tablicy
        $db->delete_query('location', " products_id = '".$id_edytowanej_pozycji."' and forwarding = '0'");           
        
        if ( !empty($_POST['url_stary']) ) {
             //
             $pola = array(
                     array('urlf',$filtr->process($_POST['url_stary'])),
                     array('urlt',Seo::link_SEO( $nazwa_domyslna, $id_edytowanej_pozycji, 'produkt', '', false, false )),
                     array('url_type','produkt'),
                     array('products_id',$id_edytowanej_pozycji),
                     array('forwarding',0));
             $sql = $db->insert_query('location' , $pola);
             //
             unset($pola);             
             //
        }
        
        $db->delete_query('location', " products_id = '".$id_edytowanej_pozycji."' and forwarding = '1'");           
        
        if ( $_POST['url_przekierowanie'] != '404' && !empty($_POST['adres_przekierowania']) ) {
             //
             $pola = array(
                     array('urlf',Seo::link_SEO( $nazwa_domyslna, $id_edytowanej_pozycji, 'produkt', '', false, false )),
                     array('urlt',$filtr->process($_POST['adres_przekierowania'])),                     
                     array('url_type','produkt'),
                     array('products_id',$id_edytowanej_pozycji),
                     array('type',$filtr->process($_POST['url_przekierowanie'])),
                     array('forwarding',1));
             $sql = $db->insert_query('location' , $pola);
             //
             unset($pola);             
             //
        }        
        
        unset($nazwa_domyslna);        
        
        // ---------------------------------- products_allegro_info
        
        if ( isset($_POST['dane_allegro']) ) {
          
            // kasuje rekordy w tablicy
            $db->delete_query('products_allegro_info' , " products_id = '".(int)$id_edytowanej_pozycji."'");   

            include('produkty_opis_allegro.php');
            
            // array('products_description_allegro',$filtr->process($_POST['opis_allegro'])),
            $pola = array(
                    array('products_id',(int)$id_edytowanej_pozycji),
                    array('products_description_allegro',$opis_allegro),
                    array('products_name_allegro',$filtr->process($_POST['nazwa_allegro'])),
                    array('products_image_allegro',$filtr->process($_POST['zdjecie_allegro'])),
                    array('products_weight_allegro',$filtr->process($_POST['waga_allegro'])),
                    array('products_cat_id_allegro',(int)$_POST['kategoria_allegro']),
                    array('products_price_allegro',$filtr->process($_POST['cena_brutto_allegro'])));        
            $sql = $db->insert_query('products_allegro_info' , $pola);
            unset($pola);   
            
            unset($opis_allegro);
            
        }

        // ---------------------------------- products to categories
        
        // kasuje rekordy w tablicy
        $db->delete_query('products_to_categories' , " products_id = '".(int)$id_edytowanej_pozycji."'");    
        
        if (!isset($_POST['id_kat'])) {
            $pola = array(
                    array('products_id',$id_edytowanej_pozycji),
                    array('categories_id','0'));        
            $sql = $db->insert_query('products_to_categories' , $pola);              
            //
          } else {
            $tablica_kat = $_POST['id_kat'];
            for ($q = 0, $c = count($tablica_kat); $q < $c; $q++) {
                $pola = array(
                        array('products_id',$id_edytowanej_pozycji),
                        array('categories_id',$tablica_kat[$q]));   
                //
                if ( isset($_POST['id_glowna']) && (int)$_POST['id_glowna'] > 0 ) {
                    //
                    if ( (int)$_POST['id_glowna'] == $tablica_kat[$q] ) {
                         $pola[] = array('categories_default', '1');
                    }
                    //
                }
                //
                $sql = $db->insert_query('products_to_categories' , $pola);        
            }
        }
        unset($tablica_kat, $pola); 
        
        // ---------------------------------- additional images
        
        // kasuje rekordy w tablicy
        $db->delete_query('additional_images' , " products_id = '".$id_edytowanej_pozycji."'");           
        
        for ($w = 1, $c = count($pola_zdjec); $w < $c; $w++) {
            $pola = array(
                    array('products_id',$id_edytowanej_pozycji),
                    array('images_description',$pola_zdjec[$w]['alt']),
                    array('popup_images',$pola_zdjec[$w]['zdjecie']),
                    array('sort_order',$pola_zdjec[$w]['sort']));        
            $sql = $db->insert_query('additional_images' , $pola);
            unset($pola);
        }

        // ---------------------------------- extra fields  

        // kasuje rekordy w tablicy
        $db->delete_query('products_to_products_extra_fields' , " products_id = '".$id_edytowanej_pozycji."'");         

        // pola tekstowe dla wszystkich jezykow
        $zapytanie_pola = "select * from products_extra_fields where languages_id = '0' and products_extra_fields_image = '0' order by products_extra_fields_order";
        $sqls = $db->open_query($zapytanie_pola);
        //
        if ($db->ile_rekordow($sqls) > 0) { 
            //
            while ($infs = $sqls->fetch_assoc()) { 
                if (!empty($_POST['pole_999_'.$infs['products_extra_fields_id'].'_1'])) {
                    $pola = array(
                            array('products_id',$id_edytowanej_pozycji),
                            array('products_extra_fields_id',$infs['products_extra_fields_id']),
                            array('products_extra_fields_value',$filtr->process($_POST['pole_999_'.$infs['products_extra_fields_id'].'_1'])),
                            array('products_extra_fields_value_1',$filtr->process($_POST['pole_999_'.$infs['products_extra_fields_id'].'_2'])),
                            array('products_extra_fields_value_2',$filtr->process($_POST['pole_999_'.$infs['products_extra_fields_id'].'_3'])),
                            array('products_extra_fields_link',$filtr->process($_POST['pole_url_999_'.$infs['products_extra_fields_id']])));        
                    $sql = $db->insert_query('products_to_products_extra_fields' , $pola);
                    unset($pola);                
                }                
            }
            //
        }
        $db->close_query($sqls);
        unset($zapytanie_pola); 
        //
        // pola graficzne dla wszystkich jezykow
        $zapytanie_pola = "select * from products_extra_fields where languages_id = '0' and products_extra_fields_image = '1' order by products_extra_fields_order";
        $sqls = $db->open_query($zapytanie_pola);
        //
        if ($db->ile_rekordow($sqls) > 0) { 
            //
            while ($infs = $sqls->fetch_assoc()) { 
                if (!empty($_POST['pole_999_zdjecie_'.$infs['products_extra_fields_id']])) {
                    $pola = array(
                            array('products_id',$id_edytowanej_pozycji),
                            array('products_extra_fields_id',$infs['products_extra_fields_id']),
                            array('products_extra_fields_value',$filtr->process($_POST['pole_999_zdjecie_'.$infs['products_extra_fields_id']])),
                            array('products_extra_fields_link',$filtr->process($_POST['pole_url_999_zdjecie_'.$infs['products_extra_fields_id']])));        
                    $sql = $db->insert_query('products_to_products_extra_fields' , $pola);
                    unset($pola);                
                }                
            }
            //
        }   
        $db->close_query($sqls);
        unset($zapytanie_pola); 
        //     
        // pola tekstowe i graficzne dla poszczegolnych jezykow
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {        
            //
            // pola tekstowe
            $zapytanie_pola = "select * from products_extra_fields where languages_id = '" . $ile_jezykow[$w]['id'] . "' and products_extra_fields_image = '0' order by products_extra_fields_order";
            $sqls = $db->open_query($zapytanie_pola);
            //
            if ($db->ile_rekordow($sqls) > 0) { 
                //
                while ($infs = $sqls->fetch_assoc()) { 
                    if (isset($_POST['pole_'.$infs['products_extra_fields_id'].'_1']) && !empty($_POST['pole_'.$infs['products_extra_fields_id'].'_1'])) {
                        $pola = array(
                                array('products_id',$id_edytowanej_pozycji),
                                array('products_extra_fields_id',$infs['products_extra_fields_id']),
                                array('products_extra_fields_value',$filtr->process($_POST['pole_'.$infs['products_extra_fields_id'].'_1'])),
                                array('products_extra_fields_value_1',$filtr->process($_POST['pole_'.$infs['products_extra_fields_id'].'_2'])),
                                array('products_extra_fields_value_2',$filtr->process($_POST['pole_'.$infs['products_extra_fields_id'].'_3'])),
                                array('products_extra_fields_link',$filtr->process($_POST['pole_url_'.$infs['products_extra_fields_id']])));        
                        $sql = $db->insert_query('products_to_products_extra_fields' , $pola);
                        unset($pola);                
                    }                
                }
                //
            }
            $db->close_query($sqls);
            unset($zapytanie_pola); 
            //
            // pola graficzne
            $zapytanie_pola = "select * from products_extra_fields where languages_id = '" . $ile_jezykow[$w]['id'] . "' and products_extra_fields_image = '1' order by products_extra_fields_order";
            $sqls = $db->open_query($zapytanie_pola);
            //
            if ($db->ile_rekordow($sqls) > 0) { 
                //
                while ($infs = $sqls->fetch_assoc()) { 
                    if (isset($_POST['pole_zdjecie_'.$infs['products_extra_fields_id']]) && !empty($_POST['pole_zdjecie_'.$infs['products_extra_fields_id']])) {
                        $pola = array(
                                array('products_id',$id_edytowanej_pozycji),
                                array('products_extra_fields_id',$infs['products_extra_fields_id']),
                                array('products_extra_fields_value',$filtr->process($_POST['pole_zdjecie_'.$infs['products_extra_fields_id']])),
                                array('products_extra_fields_link',$filtr->process($_POST['pole_url_zdjecie_'.$infs['products_extra_fields_id']])));        
                        $sql = $db->insert_query('products_to_products_extra_fields' , $pola);
                        unset($pola);                
                    }                
                }
                //
            }   
            $db->close_query($sqls);
            unset($zapytanie_pola);         
            //
        }
        
        // ---------------------------------- pola tekstowe 

        // kasuje rekordy w tablicy
        $db->delete_query('products_to_text_fields' , " products_id = '".$id_edytowanej_pozycji."'");         

        // pola tekstowe dla wszystkich jezykow
        $zapytanie_pola = "select products_text_fields_id from products_text_fields_info where languages_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sqls = $db->open_query($zapytanie_pola);
        //
        if ($db->ile_rekordow($sqls) > 0) { 
            //
            while ($infs = $sqls->fetch_assoc()) { 
                if (isset($_POST['pole_txt_'.$infs['products_text_fields_id']])) {
                    $pola = array(
                            array('products_id',$id_edytowanej_pozycji),
                            array('products_text_fields_id',$infs['products_text_fields_id']));        
                    $sql = $db->insert_query('products_to_text_fields' , $pola);
                    unset($pola);                
                }                
            }
            //
        }
        $db->close_query($sqls);
        unset($zapytanie_pola); 
        //        
        
        // ---------------------------------- info (zakladki)
        
        // kasuje rekordy w tablicy
        $db->delete_query('products_info' , " products_id = '".$id_edytowanej_pozycji."'");         
        
        for ($q = 1; $q < 5; $q++) {
            //
            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                //           
                $pola = array(
                        array('products_id',$id_edytowanej_pozycji),
                        array('products_info_id',$q),
                        array('language_id',$ile_jezykow[$w]['id']),
                        array('products_info_name',$filtr->process($_POST['nazwa_zakladki_'.$q.'_'.$w])),
                        array('products_info_description',$filtr->process($_POST['dod_zakladka_'.$q.'_'.$w])));        
                $sql = $db->insert_query('products_info', $pola);
                unset($pola);
            } 
            //
        }
        
        // ---------------------------------- linki
        
        if ( isset($_POST['link_1_0']) ) {        
        
            // kasuje rekordy w tablicy
            $db->delete_query('products_link' , " products_id = '".$id_edytowanej_pozycji."'");         
            
            for ($q = 1; $q < 5; $q++) {
                //
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    //
                    if (!empty($_POST['link_'.$q.'_'.$w]) && !empty($_POST['link_url_'.$q])) {
                        //               
                        $pola = array(
                                array('products_id',$id_edytowanej_pozycji),
                                array('products_link_id',$q),
                                array('language_id',$ile_jezykow[$w]['id']),
                                array('products_link_name',$filtr->process($_POST['link_'.$q.'_'.$w])),
                                array('products_link_description',$filtr->process($_POST['link_opis_'.$q.'_'.$w])),
                                array('products_link_url',$filtr->process($_POST['link_url_'.$q])));        
                        $sql = $db->insert_query('products_link', $pola);
                        unset($pola);
                    }
                } 
                //
            } 

        }
        
        // ---------------------------------- file
        
        if ( isset($_POST['plik_1']) ) {
        
            // kasuje rekordy w tablicy
            $db->delete_query('products_file' , " products_id = '".$id_edytowanej_pozycji."'");          
            
            for ($q = 1; $q < 11; $q++) {
                //
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    //
                    if (!empty($_POST['plik_'.$q]) && !empty($_POST['plik_nazwa_'.$q.'_'.$w])) {
                        //               
                        $pola = array(
                                array('products_id',$id_edytowanej_pozycji),
                                array('products_file_id',$q),
                                array('language_id',$ile_jezykow[$w]['id']),
                                array('products_file_name',$filtr->process($_POST['plik_nazwa_'.$q.'_'.$w])),
                                array('products_file',$filtr->process($_POST['plik_'.$q])),
                                array('products_file_description',$filtr->process($_POST['plik_opis_'.$q.'_'.$w])),
                                array('products_file_login',$filtr->process($_POST['plik_klient_'.$q]))
                                );        
                        $sql = $db->insert_query('products_file', $pola);
                        unset($pola);
                    }
                } 
                //
            } 

        }
        
        // ---------------------------------- pliki elektroniczne
        
        if ( isset($_POST['ile_plikow_0']) ) {
        
            // kasuje rekordy w tablicy
            $db->delete_query('products_file_shopping' , " products_id = '".$id_edytowanej_pozycji."'");          
            
            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                //
                for ($q = 1; $q <= (int)($_POST['ile_plikow_'.$w]); $q++) {
                    //
                    if (!empty($_POST['plik_elektroniczny_nazwa_'.$q.'_'.$w]) && !empty($_POST['plik_elektroniczny_'.$q.'_'.$w])) {
                        //               
                        $pola = array(
                                array('products_id',$id_edytowanej_pozycji),
                                array('language_id',$ile_jezykow[$w]['id']),
                                array('products_file_shopping_name',$filtr->process($_POST['plik_elektroniczny_nazwa_'.$q.'_'.$w])),
                                array('products_file_shopping',$filtr->process($_POST['plik_elektroniczny_'.$q.'_'.$w]))
                                );        
                        $sql = $db->insert_query('products_file_shopping', $pola);
                        unset($pola);
                    }
                } 
                //
            } 
            
        }

        // ---------------------------------- youtube
        
        if ( isset($_POST['film_url_1']) ) {
        
            // kasuje rekordy w tablicy
            $db->delete_query('products_youtube' , " products_id = '".$id_edytowanej_pozycji."'");          
            
            for ($q = 1; $q < 5; $q++) {
                //
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    //
                    if (!empty($_POST['film_url_'.$q]) && !empty($_POST['film_nazwa_'.$q.'_'.$w])) {
                        //               
                        $pola = array(
                                array('products_id',$id_edytowanej_pozycji),
                                array('products_film_id',$q),
                                array('language_id',$ile_jezykow[$w]['id']),
                                array('products_film_name',$filtr->process($_POST['film_nazwa_'.$q.'_'.$w])),
                                array('products_film_url',$filtr->process($_POST['film_url_'.$q])),
                                array('products_film_description',$filtr->process($_POST['film_opis_'.$q.'_'.$w])),
                                array('products_film_width',$filtr->process($_POST['film_szerokosc_'.$q])),
                                array('products_film_height',$filtr->process($_POST['film_wysokosc_'.$q]))
                                );        
                        $sql = $db->insert_query('products_youtube', $pola);
                        unset($pola);
                    }
                } 
                //
            }

        }
        
        // ---------------------------------- filmy flv
        
        if ( isset($_POST['flv_plik_1']) ) {
        
            // kasuje rekordy w tablicy
            $db->delete_query('products_film' , " products_id = '".$id_edytowanej_pozycji."'");          
            
            for ($q = 1; $q < 5; $q++) {
                //
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    //
                    if (!empty($_POST['flv_plik_'.$q]) && !empty($_POST['flv_nazwa_'.$q.'_'.$w])) {
                        //               
                        $pola = array(
                                array('products_id',$id_edytowanej_pozycji),
                                array('products_film_id',$q),
                                array('language_id',$ile_jezykow[$w]['id']),
                                array('products_film_name',$filtr->process($_POST['flv_nazwa_'.$q.'_'.$w])),
                                array('products_film_file',$filtr->process($_POST['flv_plik_'.$q])),
                                array('products_film_description',$filtr->process($_POST['flv_opis_'.$q.'_'.$w])),
                                array('products_film_width',$filtr->process($_POST['flv_szerokosc_'.$q])),
                                array('products_film_height',$filtr->process($_POST['flv_wysokosc_'.$q]))
                                );        
                        $sql = $db->insert_query('products_film', $pola);
                        unset($pola);
                    }
                } 
                //
            }    

        }
        
        // ---------------------------------- muzyka mp3
        
        if ( isset($_POST['ile_pol_mp3']) ) {
        
            // ustalanie ilosci zdjec
            $pola_mp3 = array();
            for ($r = 1; $r < 100; $r++) {
                if (isset($_POST['utwor_mp3_'.$r]) && !empty($_POST['utwor_mp3_'.$r])) {
                    $pola_mp3[] = array('plik_mp3' => $filtr->process($_POST['utwor_mp3_'.$r]),
                                        'nazwa_mp3' => $filtr->process($_POST['nazwa_mp3_'.$r]));
                }
            }        
            
            // kasuje rekordy w tablicy
            $db->delete_query('products_mp3' , " products_id = '".$id_edytowanej_pozycji."'");          
                
            if ( count($pola_mp3) > 0 ) {

                for ($w = 0, $c = count($pola_mp3); $w < $c; $w++) {
                    $pola = array(
                            array('products_id',$id_edytowanej_pozycji),
                            array('products_mp3_id',($w + 1)),
                            array('products_mp3_name',$pola_mp3[$w]['nazwa_mp3']),
                            array('products_mp3_file',$pola_mp3[$w]['plik_mp3']));          
                    $sql = $db->insert_query('products_mp3' , $pola);
                    unset($pola);
                } 

            }
            
        }
        
        // ---------------------------------- linki powiazane
        
        if ( isset($_POST['ile_grup_linkow_0']) ) {
        
            // kasuje rekordy w tablicy

            $db->delete_query('products_related_links_group' , " products_id = '" . $id_edytowanej_pozycji . "'");
            $db->delete_query('products_related_links' , " products_id = '" . $id_edytowanej_pozycji . "'");

            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                //
                for ($q = 1; $q <= (int)($_POST['ile_grup_linkow_'.$w]); $q++) {
                    //
                    if (!empty($_POST['grupa_linkow_powiazanych_nazwa_'.$q.'_'.$w])) {
                        //
                        $pola = array(
                                array('products_id',$id_edytowanej_pozycji),
                                array('language_id',$ile_jezykow[$w]['id']),
                                array('products_related_links_group_name',$filtr->process($_POST['grupa_linkow_powiazanych_nazwa_'.$q.'_'.$w])),
                                array('products_related_links_group_description',$filtr->process($_POST['grupa_linkow_powiazanych_opis_'.$q.'_'.$w]))); 
                                
                        $id_dodanej_grupy_linkow = $db->insert_query('products_related_links_group', $pola, '', false, true);
                        unset($pola);                    
                        //
                        for ($r = 0; $r <= 20; $r++) {
                             //
                             if (isset($_POST['link_powiazany_nazwa_'.$q.'_'.$w.'_'.$r]) && !empty($_POST['link_powiazany_nazwa_'.$q.'_'.$w.'_'.$r])) {
                                 //               
                                 $pola = array(
                                         array('products_related_links_group_id',$id_dodanej_grupy_linkow),
                                         array('language_id',$ile_jezykow[$w]['id']),
                                         array('products_id',$id_edytowanej_pozycji),
                                         array('products_related_links_name',$filtr->process($_POST['link_powiazany_nazwa_'.$q.'_'.$w.'_'.$r])),
                                         array('products_related_links_foto',$filtr->process($_POST['link_powiazany_foto_'.$q.'_'.$w.'_'.$r])),
                                         array('products_related_links_url',$filtr->process($_POST['link_powiazany_adres_'.$q.'_'.$w.'_'.$r])));
                                         
                                 $sql = $db->insert_query('products_related_links', $pola);
                                 unset($pola);
                             }
                             //
                        }
                        //
                        unset($id_dodanej_grupy_linkow);
                        //
                    }
                    //
                } 
                //
            } 
            
        }           

        // ---------------------------------- obliczanie ilosci produktu na podstawie stanu magazynowego cech
        
        if (CECHY_MAGAZYN == 'tak' && !isset($_POST['zestaw'])) {
            //
            $ogolna_ilosc = 0;
            $zapytanie_pola = "select distinct * from products_stock where products_id = '" . $id_edytowanej_pozycji . "'";
            $sqls = $db->open_query($zapytanie_pola);     
            //
            if ((int)$db->ile_rekordow($sqls) > 0) {
                //
                while ($infs = $sqls->fetch_assoc()) { 
                    $ogolna_ilosc = $ogolna_ilosc + $infs['products_stock_quantity'];
                }
                $db->close_query($sqls);
                //
                $pola = array(array('products_quantity',$ogolna_ilosc));
                $sql = $db->update_query('products', $pola, "products_id = '".$id_edytowanej_pozycji."'");
                //
            }
            //
            unset($zapytanie_pola, $ogolna_ilosc, $infs, $pola); 
            //
        }

        //
        // jezeli jest filtr kategoria
        $kat = '';
        if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
            //
            $tablica_kat = $_POST['id_kat'];
            for ($q = 0, $c = count($tablica_kat); $q < $c; $q++) {
                //
                if ((int)$tablica_kat[$q] == (int)$_GET['kategoria_id']) {
                    $_GET['kategoria_id'] = (int)$_GET['kategoria_id'];
                    break;
                }
                //
            }       
            //
        }
        unset($tablica_kat);
        //        
        
        // plik pozastandardowy - dla zapisow poza standardowymi funkcjami sklepu - indywidualne modyfikacje
        
        $id_pozycji = $id_edytowanej_pozycji;
        include('produkty/dodatkowe_zapisy.php');
        unset($id_pozycji);
        
        if ( isset($_POST['powrot']) && (int)$_POST['powrot'] == 1 ) {
            //            
            Funkcje::PrzekierowanieURL('produkty_edytuj.php?id_poz=' . $id_edytowanej_pozycji . ((isset($_POST['zakladka']) && (int)$_POST['zakladka'] > 0) ? '&zakladka=' . (int)$_POST['zakladka'] : '') . ((isset($_POST['id_zestawu'])) ? '&zestaw' : ''));
            //
          } else {
            //
            if ( !isset($_POST['zamowienie_id']) ) {
                 //            
                 if ( isset($_POST['id_zestawu']) ) {
                      Funkcje::PrzekierowanieURL('zestawy_produktow.php?id_poz=' . $id_edytowanej_pozycji);
                   } else {
                      Funkcje::PrzekierowanieURL('produkty.php?id_poz=' . $id_edytowanej_pozycji);
                 }
                 //
            } else {
                 //
                 Funkcje::PrzekierowanieURL('/zarzadzanie/sprzedaz/zamowienia_szczegoly.php?id_poz=' . (int)$_POST['zamowienie_id'] . ((isset($_POST['zakladka_zamowienie'])) ? '&zakladka=' . (int)$_POST['zakladka_zamowienie'] : ''));
                 //
            }
            //
        }        
        
    }
    
    // sprawdzenie czy produkt nie jest zestawem
    $zestaw = false;
    //
    if ( isset($_GET['id_poz']) ) {
         //
         $zapytanie = "select products_set from products where products_id = '".(int)$_GET['id_poz']."'";
         $sql = $db->open_query($zapytanie);
         //
         $prod = $sql->fetch_assoc();
         //
         if ( isset($prod['products_set']) && (int)$prod['products_set'] == 1 ) {
               $zestaw = true;
         }
         //
         $db->close_query($sql);
         unset($zapytanie, $prod);
         //
    } else {  
         //
         if ( isset($_GET['zestaw']) ) {
              $zestaw = true;
         }
         //
    }
    
    // wczytanie naglowka HTML
    include('naglowek.inc.php');      
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

        <script type="text/javascript" src="produkty/cechy.js"></script>   

        <form action="produkty/produkty_edytuj.php" method="post" id="poForm" class="cmxform" onsubmit="return sprFormularz()" enctype="multipart/form-data">   

        <div class="poleForm">
            <div class="naglowek">Edycja <?php echo (($zestaw) ? 'zestawu' : 'produktu'); ?> <b id="NazwaProduktu"></b></div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from products where products_id = '".(int)$_GET['id_poz']."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                ?>              
            
                <input type="hidden" name="akcja" value="zapisz_edycje" />
                
                <?php if ( $zestaw ) { ?>
                <input type="hidden" name="zestaw" value="1" />
                <?php } ?>                
                
                <input type="hidden" name="id_produktu" value="<?php echo (int)$_GET['id_poz']; ?>" />
                
                <?php if ( isset($_GET['zamowienie_id']) ) { ?>
                <input type="hidden" name="zamowienie_id" value="<?php echo (int)$_GET['zamowienie_id']; ?>" />
                <?php } ?>
                
                <?php if ( isset($_GET['zakladka_zamowienie']) ) { ?>
                <input type="hidden" name="zakladka_zamowienie" value="<?php echo (int)$_GET['zakladka_zamowienie']; ?>" />
                <?php } ?>                

                <?php       
                $zadanieDuplikacja = false;
                $id_produktu = (int)$_GET['id_poz']; 
                ?>

                <?php 
                $ile_jezykow = Funkcje::TablicaJezykow(); 
                $jezyk_szt = count($ile_jezykow);
                ?>

                <script>
                $(document).ready(function() {

                    $("#poForm").validate({
                      focusCleanup: true,
                      focusInvalid: false,
                      ignoreTitle: true,
                      rules: {
                        nazwa_0: {
                          required: true
                        },
                        koszt_wysylki_ilosc: { min: 1 },
                        inpost_ilosc_paczka: { min: 1 }
                      },
                      messages: {
                        nazwa_0: {
                          required: "Pole jest wymagane."
                        },
                        koszt_wysylki_ilosc: "minimalna wartosc 1",
                        inpost_ilosc_paczka: "minimalna wartosc 1"
                      }
                    });

                    $('input.datepicker').Zebra_DatePicker({
                       format: 'd-m-Y',
                       inside: false,
                       readonly_element: true
                    });
                    
                    $('input.datepickerPelny').Zebra_DatePicker({
                       format: 'd-m-Y H:i',
                       inside: false,
                       readonly_element: true,
                       enabled_minutes: [00, 10, 20, 30, 40, 50]
                    });
                    
                    $('input.datepickerMinuta').Zebra_DatePicker({
                       format: 'd-m-Y H:i',
                       inside: false,
                       readonly_element: true
                    });                    

                    $('.a_href_info_zakl').click(function() {
                        var id_zakl = $(this).attr('id').replace('zakl_link_','');
                        $('#zakladka').val(id_zakl);
                    });

                });
                
                function sprFormularz() {
                    var zaz = 0;
                    var blad = '';
                    $('input:checkbox').each( function() {
                        nazwaKat = $(this).attr('name');
                        if ( nazwaKat == 'id_kat[]' && $(this).is(':checked') ) {
                             zaz++;
                        }
                    });
                    if ( zaz == 0 ) {
                         blad = '<div id="PopUpInfo">Nie została wybrana kategoria do jakiej ma być przypisany produkt.</div>';
                    }
                    <?php if ( $zestaw ) { ?>
                    if ( $('#id_zestawu').val() == '' ) {
                         blad = '<div id="PopUpInfo">Nie zostały wybrane produkty wchodzące w zestaw.</div>';
                    }                    
                    <?php } ?>
                    if ( blad != '' ) {
                         $.colorbox( { html:blad, initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                         return false;
                    }
                    return true;
                }                                 
                
                function szukajTbl(tablica, szuk) {
                  for (var i = 0; i < tablica.length; i++) {
                      if (tablica[i] == szuk) return true;
                  }
                }   
                
                function pokaz_dane( poleId, nr, idTab ) {
                  //                  
                  var pole = $('#ajax_zakladki').val();
                  var sprawdz = pole.split(',');
                  //
                  if ( !szukajTbl(sprawdz, nr) ) {
                      //
                      $('#ekr_preloader').css('display','block');
                      //
                      var pamietaj_html = $("#" + poleId).html();
                      $.get('produkty/produkty_dodaj_zakl_' + poleId + '.php',
                            { tok: '<?php echo Sesje::Token(); ?>', id_produktu: '<?php echo $id_produktu; ?>', id_tab: idTab, zestaw: '<?php echo (($zestaw) ? '1' : '0'); ?>' }, function(data) {
                            if (data != '') {
                                $("#" + poleId).html(data);
                              } else {
                                $("#" + poleId).html(pamietaj_html);
                            }
                            $('#ekr_preloader').delay(100).fadeOut('fast');
                            //
                            pokazChmurki();                            
                            usunPlikZdjecie();                            
                            //
                      });
                      $('#ajax_zakladki').val( $('#ajax_zakladki').val() + ',' + nr );
                      //                      
                  } else {
                      $('.WierszeOpisu').find('textarea').each(function() {
                        //
                        ckeditAllegro( $(this).attr('id') ,'100%','200');
                        //
                      });                    
                  }
                };                    
                </script>           

                <input type="hidden" id="ajax_zakladki" value="" />

                <div id="ZakladkiEdycji">
                
                    <div id="LeweZakladki">
                    
                        <span onclick="gold_tabs_horiz('0','<?php echo $tab_0 = rand(0,999999); ?>')" class="a_href_info_zakl" id="zakl_link_0">Podstawowe dane</span> 
                        <span onclick="gold_tabs_horiz('14')" class="a_href_info_zakl" id="zakl_link_14">Przypisane kategorie</span>                        
                        <span onclick="gold_tabs_horiz('1','<?php echo $tab_1 = rand(0,999999); ?>','opis_')" class="a_href_info_zakl" id="zakl_link_1">Opis</span>   
                        <span onclick="gold_tabs_horiz('2','<?php echo $tab_2 = rand(0,999999); ?>','opis_krotki_')" class="a_href_info_zakl" id="zakl_link_2">Krótki opis</span>
                        <span onclick="gold_tabs_horiz('24');gold_tabs('<?php echo $tab_24 = rand(0,999999); ?>','opis_dodatkowy_',100,'','opis_dodatkowy_2_')" class="a_href_info_zakl" id="zakl_link_24">Dodatkowe opisy</span>
                        <span onclick="gold_tabs_horiz('25','<?php echo $tab_25 = rand(0,999999); ?>')" class="a_href_info_zakl" id="zakl_link_25">Pytania i odpowiedzi</span>
                        
                        <?php if ( !$zestaw ) { ?>
                        <span onclick="gold_tabs_horiz('21','<?php echo $tab_21 = rand(0,999999); ?>');pokaz_dane('allegro','21','<?php echo $tab_21; ?>')" class="a_href_info_zakl" id="zakl_link_21">Dane <span></span></span>                          
                        <?php } ?>
                        
                        <span onclick="gold_tabs_horiz('3')" class="a_href_info_zakl" id="zakl_link_3">Zdjęcia <?php echo (($zestaw) ? 'zestawu' : 'produktu'); ?></span>   
                        <span onclick="gold_tabs_horiz('4','<?php echo $tab_4 = rand(0,999999); ?>')" class="a_href_info_zakl" id="zakl_link_4">Dodatkowe pola opisowe</span>  
                        <span onclick="gold_tabs_horiz('19')" class="a_href_info_zakl" id="zakl_link_19">Dodatkowe pola tekstowe</span> 

                        <?php if ( !$zestaw ) { ?>
                        <span onclick="gold_tabs_horiz('5')" class="a_href_info_zakl" id="zakl_link_5">Cechy produktu</span>    
                        <?php } ?>
                        
                        <span onclick="gold_tabs_horiz('23')" class="a_href_info_zakl" id="zakl_link_23">Inne warianty produktu</span>    
                        
                        <span onclick="gold_tabs_horiz('6','<?php echo $tab_6 = rand(0,999999); ?>')" class="a_href_info_zakl" id="zakl_link_6">Pozycjonowanie / tagi</span>   
                        <span onclick="gold_tabs_horiz('7','<?php echo $tab_7 = rand(0,999999); ?>','dod_zakladka_')" class="a_href_info_zakl" id="zakl_link_7">Dodatkowa zakładka #1</span>  
                        <span onclick="gold_tabs_horiz('8','<?php echo $tab_8 = rand(0,999999); ?>','dod_zakladka_')" class="a_href_info_zakl" id="zakl_link_8">Dodatkowa zakładka #2</span> 
                        <span onclick="gold_tabs_horiz('9','<?php echo $tab_9 = rand(0,999999); ?>','dod_zakladka_')" class="a_href_info_zakl" id="zakl_link_9">Dodatkowa zakładka #3</span>
                        <span onclick="gold_tabs_horiz('10','<?php echo $tab_10 = rand(0,999999); ?>','dod_zakladka_')" class="a_href_info_zakl" id="zakl_link_10">Dodatkowa zakładka #4</span>
                        <span onclick="gold_tabs_horiz('11','<?php echo $tab_11 = rand(0,999999); ?>');pokaz_dane('dod_linki','11','<?php echo $tab_11; ?>')" class="a_href_info_zakl" id="zakl_link_11">Linki</span>
                        <span onclick="gold_tabs_horiz('12','<?php echo $tab_12 = rand(0,999999); ?>');pokaz_dane('pliki','12','<?php echo $tab_12; ?>')" class="a_href_info_zakl" id="zakl_link_12">Pliki</span>
                        <span <?php echo (( $zestaw ) ? 'style="display:none"' : ''); ?> onclick="gold_tabs_horiz('20','<?php echo $tab_20 = rand(0,999999); ?>');pokaz_dane('pliki_elektroniczne','20','<?php echo $tab_20; ?>')" class="a_href_info_zakl" id="zakl_link_20">Sprzedaż elektroniczna</span>
                        <span onclick="gold_tabs_horiz('16','<?php echo $tab_16 = rand(0,999999); ?>');pokaz_dane('youtube','16','<?php echo $tab_16; ?>')" class="a_href_info_zakl" id="zakl_link_16">Filmy YouTube</span>
                        <span onclick="gold_tabs_horiz('17','<?php echo $tab_17 = rand(0,999999); ?>');pokaz_dane('filmy','17','<?php echo $tab_17; ?>')" class="a_href_info_zakl" id="zakl_link_17">Filmy MP4</span>
                        <span onclick="gold_tabs_horiz('18');pokaz_dane('mp3','18')" class="a_href_info_zakl" id="zakl_link_18">Pliki MP3</span>
                        <span onclick="gold_tabs_horiz('15','')" class="a_href_info_zakl" id="zakl_link_15">Dostępne wysyłki</span>   

                        <?php
                        // produkty podobne
                        $zapytanie_powiazane = "select count(*) as ile_podobnych from products_options_products where pop_products_id_master = '" . $id_produktu . "'";
                        $sql_podobne = $db->open_query($zapytanie_powiazane);
                        $infs = $sql_podobne->fetch_assoc();
                        $db->close_query($sql_podobne);
                        //
                        if ( $infs['ile_podobnych'] > 0 ) {
                             echo '<a href="/zarzadzanie/podobne/podobne_edytuj.php?id_poz=' . $id_produktu . '&edycja=produkt" class="a_href_info_zakl LinkPodobne">Produkty podobne (' . $infs['ile_podobnych'] . ')</a>';
                        } else {
                             echo '<a href="/zarzadzanie/podobne/podobne_dodaj.php?id_poz=' . $id_produktu . '&edycja=produkt" class="a_href_info_zakl LinkPodobne">Produkty podobne (0)</a>';
                        }
                        unset($infs, $zapytanie_powiazane);
                        
                        // produkty powiazane
                        $zapytanie_powiazane = "select count(*) as ile_powiazanych from products_related_products where prp_products_id_master = '" . $id_produktu . "'";
                        $sql_podobne = $db->open_query($zapytanie_powiazane);
                        $infs = $sql_podobne->fetch_assoc();
                        $db->close_query($sql_podobne);
                        //
                        if ( $infs['ile_powiazanych'] > 0 ) {
                             echo '<a href="/zarzadzanie/powiazane/powiazane_edytuj.php?id_poz=' . $id_produktu . '&edycja=produkt" class="a_href_info_zakl LinkPowiazane">Produkty powiązane (' . $infs['ile_powiazanych'] . ')</a>';
                        } else {
                             echo '<a href="/zarzadzanie/powiazane/powiazane_dodaj.php?id_poz=' . $id_produktu . '&edycja=produkt" class="a_href_info_zakl LinkPowiazane">Produkty powiązane (0)</a>';
                        }
                        unset($infs, $zapytanie_powiazane);    

                        // akcesoria dodatkowe
                        $zapytanie_powiazane = "select count(*) as ile_produktow from products_accesories where pacc_products_id_master = '" . $id_produktu . "' and pacc_type = 'produkt'";
                        $sql_akcesoria_dodatkowe = $db->open_query($zapytanie_powiazane);
                        $infs = $sql_akcesoria_dodatkowe->fetch_assoc();
                        $db->close_query($sql_akcesoria_dodatkowe);
                        //
                        if ( $infs['ile_produktow'] > 0 ) {
                             echo '<a href="/zarzadzanie/akcesoria_dodatkowe/akcesoria_dodatkowe_edytuj.php?id_poz=' . $id_produktu . '&edycja=produkt" class="a_href_info_zakl LinkAkcesoria">Akcesoria dodatkowe <br /> przypisane do produktu (' . $infs['ile_produktow'] . ')</a>';
                        } else {
                             echo '<a href="/zarzadzanie/akcesoria_dodatkowe/akcesoria_dodatkowe_dodaj.php?id_poz=' . $id_produktu . '&edycja=produkt" class="a_href_info_zakl LinkAkcesoria">Akcesoria dodatkowe <br /> przypisane do produktu (0)</a>';
                        }
                        unset($infs, $zapytanie_powiazane);                           
                        ?>
                        
                        <span onclick="gold_tabs_horiz('22','<?php echo $tab_22 = rand(0,999999); ?>');pokaz_dane('powiazane_linki','22','<?php echo $tab_22; ?>')" class="a_href_info_zakl" id="zakl_link_22">Powiązane linki</span>         
                        
                        <?php
                        // recenzje
                        $zapytanie_recenzje = "select count(*) as ile_recenzji from reviews where products_id = '" . $id_produktu . "'";
                        $sql_recenzje = $db->open_query($zapytanie_recenzje);
                        $infs = $sql_recenzje->fetch_assoc();
                        $db->close_query($sql_recenzje);
                        ?>
                        <span onclick="gold_tabs_horiz('27','<?php echo $tab_27 = rand(0,999999); ?>');pokaz_dane('recenzje','27','<?php echo $tab_27; ?>')" class="a_href_info_zakl" id="zakl_link_27">Recenzje (<?php echo (int)$infs['ile_recenzji']; ?>)</span>       
                        <?php unset($infs, $zapytanie_powiazane); ?>
                        
                    </div>
                    
                    <div id="PrawaStrona">

                        <?php 
                        // Informacje ogolne
                        include('produkty_dodaj_zakl_infor_ogolne.php');
                        
                        // Kategorie
                        include('produkty_dodaj_zakl_kategorie.php');                        
                        
                        // Opis
                        include('produkty_dodaj_zakl_opis.php');
                        
                        // Opis krotki
                        include('produkty_dodaj_zakl_opis_krotki.php'); 
                        
                        // Dodatkowe opisy
                        include('produkty_dodaj_zakl_opis_dodatkowy.php'); 
                        
                        // Faq
                        include('produkty_dodaj_zakl_faq.php'); 
                        
                        // Opis allegro
                        ?>
                        
                        <?php if ( $zestaw ) { '<div style="display:none">'; } ?>
                        <div id="zakl_id_21" style="display:none;">
                            <div id="allegro">
                                <span class="padAjax">Brak danych ...</span>
                            </div>
                        </div>              
                        <?php if ( $zestaw ) { '</div>'; } ?> 
                        
                        <?php                                               

                        // Zdjecia
                        include('produkty_dodaj_zakl_zdjecia.php');   

                        // Dodatkowe pola
                        include('produkty_dodaj_zakl_dodatkowe_pola.php');                                   

                        // Dodatkowe pola tekstowe
                        include('produkty_dodaj_zakl_dodatkowe_pola_tekstowe.php');                                                

                        // Inne warianty
                        include('produkty_dodaj_zakl_inne_warianty.php');    
                        
                        // Cechy produktu
                        if ( !$zestaw ) {
                            include('produkty_dodaj_zakl_cechy.php');
                        }
                        
                        // Meta tagi
                        include('produkty_dodaj_zakl_meta_tagi.php');                        
                      
                        // Dodatkowe zakladki
                        include('produkty_dodaj_zakl_dod_zakladki.php');  
                        
                        // Linki
                        ?>
                        <div id="zakl_id_11" style="display:none;">
                            <div id="dod_linki">
                                <span class="padAjax">Brak danych ...</span>
                            </div>
                        </div>                        
                        <?php
                        
                        // Pliki
                        ?>
                        <div id="zakl_id_12" style="display:none;">
                            <div id="pliki">
                                <span class="padAjax">Brak danych ...</span>
                            </div>
                        </div>                        
                        <?php 

                        // Pliki elektroniczne
                        ?>
                        
                        <?php if ( $zestaw ) { '<div style="display:none">'; } ?>
                        <div id="zakl_id_20" style="display:none;">
                            <div id="pliki_elektroniczne">
                                <span class="padAjax">Brak danych ...</span>
                            </div>
                        </div>                        
                        <?php if ( $zestaw ) { '</div>'; } ?>
                        
                        <?php                         

                        // Youtube
                        ?>
                        <div id="zakl_id_16" style="display:none;">
                            <div id="youtube">
                                <span class="padAjax">Brak danych ...</span>
                            </div>
                        </div>                        
                        <?php    

                        // Filmy FLV
                        ?>
                        <div id="zakl_id_17" style="display:none;">
                            <div id="filmy">
                                <span class="padAjax">Brak danych ...</span>
                            </div>
                        </div>                        
                        <?php                    

                        // Pliki Mp3
                        ?>
                        <div id="zakl_id_18" style="display:none;">
                            <div id="mp3">
                                <span class="padAjax">Brak danych ...</span>
                            </div>
                        </div>
                        <?php

                        // Wysylki
                        include('produkty_dodaj_zakl_wysylki.php');

                        // Linki powiazane
                        ?>
                        <div id="zakl_id_22" style="display:none;">
                            <div id="powiazane_linki">
                                <span class="padAjax">Brak danych ...</span>
                            </div>
                        </div>      

                        <?php
                        // Recenzje
                        ?>
                        <div id="zakl_id_27" style="display:none;">
                            <div id="recenzje">
                                <span class="padAjax">Brak danych ...</span>
                            </div>
                        </div>                           
                        
                        <script>
                        <?php if ( isset($_GET['zakladka']) && (int)$_GET['zakladka'] > 0 ) { ?>
                        
                        var evl = $('#zakl_link_<?php echo $_GET['zakladka']; ?>').attr('onclick');
                        eval(evl);
                        
                        <?php } else { ?>
                        
                        gold_tabs_horiz('0','<?php echo $tab_0; ?>');
                        
                        <?php } ?>                        
                        </script>                         
                    
                    </div>
                
                </div>        
            
                <?php 
              
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>  
            
        </div>
        
        <?php if ((int)$db->ile_rekordow($sql) > 0) { ?>
        
        <br />
        
        <div class="przyciski_dolne">
          <input type="hidden" name="powrot" id="powrot" value="0" />
          <input type="hidden" name="zakladka" id="zakladka" value="<?php echo ((isset($_GET['zakladka']) && (int)$_GET['zakladka'] > 0) ? (int)$_GET['zakladka'] : 0); ?>" />
          <input type="submit" class="przyciskNon" value="Zapisz dane" />                  
          <?php if ( !isset($_GET['zamowienie_id']) ) { ?>
          <input type="submit" class="przyciskNon" value="Zapisz dane i pozostań w edycji" onclick="$('#powrot').val(1)" />                                    
          <?php } ?>
          <?php if ( !isset($_GET['zamowienie_id']) ) { ?>
          <button type="button" class="przyciskNon" onclick="cofnij('<?php echo (($zestaw) ? 'zestawy_produktow' : 'produkty'); ?>','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','produkty');">Powrót</button>                      
          <?php } else { ?>
          <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','?id_poz=<?php echo (int)$_GET['zamowienie_id']; ?><?php echo ((isset($_GET['zakladka_zamowienie'])) ? '&zakladka=' . (int)$_GET['zakladka_zamowienie'] : ''); ?>','sprzedaz');">Powrót do zamówienia</button>                      
          <?php } ?>
          <a target="_blank" class="ZobaczSklep" href="<?php echo Seo::link_SEO( $nazwa_produktu, $id_produktu, 'produkt', '', false ); ?>">Zobacz w sklepie</a>
        </div>            
        
        <?php }
        
        unset($nazwa_produktu);
        
        $db->close_query($sql);        
        
        ?>
        
        </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>