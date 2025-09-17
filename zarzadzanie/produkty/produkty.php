<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

// filtry ze strony glownej
if ( isset($_GET['kategoria']) ) {
     //
     unset($_SESSION['filtry']['produkty.php']);
     $_SESSION['filtry']['produkty.php']['blad'] = 'kategoria';
     //
     Funkcje::PrzekierowanieURL('produkty.php');
}
if ( isset($_GET['brutto']) ) {
     //
     unset($_SESSION['filtry']['produkty.php']);
     $_SESSION['filtry']['produkty.php']['blad'] = 'brutto';
     //
     Funkcje::PrzekierowanieURL('produkty.php');
}
if ( isset($_GET['nazwa']) ) {
     //
     unset($_SESSION['filtry']['produkty.php']);
     $_SESSION['filtry']['produkty.php']['blad'] = 'nazwa';
     //
     Funkcje::PrzekierowanieURL('produkty.php');
}
if ( isset($_GET['vat']) ) {
     //
     unset($_SESSION['filtry']['produkty.php']);
     $_SESSION['filtry']['produkty.php']['blad'] = 'vat';
     //
     Funkcje::PrzekierowanieURL('produkty.php');
}
if ( isset($_GET['wszystkie']) ) {
     //
     unset($_SESSION['filtry']['produkty.php']);
     //
     Funkcje::PrzekierowanieURL('produkty.php');
}
if ( isset($_GET['aktywne']) ) {
     //
     unset($_SESSION['filtry']['produkty.php']);
     $_SESSION['filtry']['produkty.php']['status'] = 'tak';
     //
     Funkcje::PrzekierowanieURL('produkty.php');
}
if ( isset($_GET['nieaktywne']) ) {
     //
     unset($_SESSION['filtry']['produkty.php']);
     $_SESSION['filtry']['produkty.php']['status'] = 'nie';
     //
     Funkcje::PrzekierowanieURL('produkty.php');
}
if ( isset($_GET['filtr']) && !empty($_GET['szukaj']) ) {
     //
     unset($_SESSION['filtry']['produkty.php']);
     //
     // zamienia ' na \' - jezeli nie jest wlaczony magic
     $_GET['szukaj'] = str_replace("'", "\'", (string)$_GET['szukaj']);
     //     
     if ( isset($_GET['opcja_szukania']) ) {
          if ( $_GET['opcja_szukania'] == 'nazwa' ) {
               $_SESSION['filtry']['produkty.php']['szukaj_tytul'] = '1';
          }       
          if ( $_GET['opcja_szukania'] == 'nr_katalogowy' ) {
               $_SESSION['filtry']['produkty.php']['szukaj_nrkat'] = '1';
          }
          if ( $_GET['opcja_szukania'] == 'nr_producenta' ) {
               $_SESSION['filtry']['produkty.php']['szukaj_kodprod'] = '1';
          }              
          $_SESSION['filtry']['produkty.php']['szukaj'] = rawurlencode($_GET['szukaj']);
     }
     //
     Funkcje::PrzekierowanieURL('produkty.php');
}

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    // pobieranie informacji o vat - do wyswietlania informacji jaki podatek ma produkt
    $zapytanie_vat = "select distinct * from tax_rates order by tax_rate desc";
    $sqls = $db->open_query($zapytanie_vat);
    //
    $tablicaVat = array();
    while ($infs = $sqls->fetch_assoc()) { 
        $tablicaVat[$infs['tax_rates_id']] = $infs['tax_description'];
    }
    $db->close_query($sqls);
    unset($zapytanie_vat, $infs);  
    //

    $nr_id_tmp_nr_kat = array();
    $nr_id_tmp_ean = array();
    
    $warunki_szukania = ' and p.products_id > 0 ';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && !empty($_GET['szukaj'])) {

        $_GET['szukaj'] = rawurldecode($_GET['szukaj']);
        //
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        //
        $warunki_szukania_tmp = array();
        //
        if ( isset($_GET['szukaj_tytul']) ) {
             $warunki_szukania_tmp[] = "pd.products_name like '%".$szukana_wartosc."%'";
        }
        //
        if ( isset($_GET['szukaj_opis']) ) {
             $warunki_szukania_tmp[] = "pd.products_description like '%".$szukana_wartosc."%'";
        }
        if ( isset($_GET['szukaj_nrkat']) ) {
             $warunki_szukania_tmp[] = "p.products_model like '%".$szukana_wartosc."%' {DODATKOWE_WARUNKI_NR_KAT}";
             //
             // szuka tez czy nie ma nr katalogowego w cechach
             $sql_cechy = $db->open_query("SELECT products_id FROM products_stock WHERE (products_stock_model like '%".$szukana_wartosc."%')");
             while ($info_cechy = $sql_cechy->fetch_assoc()) {
                 $nr_id_tmp_nr_kat[ $info_cechy['products_id'] ] = $info_cechy['products_id'];
             }
             $db->close_query($sql_cechy);          
        }        
        if ( isset($_GET['szukaj_kodprod']) ) {
             $warunki_szukania_tmp[] = "p.products_man_code like '%".$szukana_wartosc."%'";
        }                
        //
        if ( count($warunki_szukania_tmp) > 0 ) {
             $warunki_szukania_tmp = ' and (' . implode(' or ', $warunki_szukania_tmp) . ')';        
             $warunki_szukania .= $warunki_szukania_tmp;
        } else {
             $warunki_szukania = ' and p.products_id = 0 ';
        }
        //
        unset($szukana_wartosc);
    }   

    // jezeli jest nr kat lub id
    if (isset($_GET['id_produktu']) && (int)$_GET['id_produktu'] > 0) {
        //
        $warunki_szukania .= " and p.products_id = " . (int)$_GET['id_produktu'];
        //
    }
    
    // jezeli jest kod ean
    if (isset($_GET['ean']) && !empty($_GET['ean'])) { 
        $szukana_wartosc = $filtr->process($_GET['ean']);
        $warunki_szukania .= " and (p.products_ean like '%".$szukana_wartosc."%' {DODATKOWE_WARUNKI_EAN})";
        //
        // szuka tez czy nie ma kodu ean w cechach
        $sql_cechy = $db->open_query("SELECT products_id FROM products_stock WHERE (products_stock_ean like '%".$szukana_wartosc."%')");
        while ($info_cechy = $sql_cechy->fetch_assoc()) {
            $nr_id_tmp_ean[ $info_cechy['products_id'] ] = $info_cechy['products_id'];
        }
        $db->close_query($sql_cechy);
        unset($szukana_wartosc);
        //
    }  
    
    // jezeli jest nr referencyjny lub id zew magazynu
    if (isset($_GET['id_zew']) && !empty($_GET['id_zew'])) { 
        $szukana_wartosc = $filtr->process($_GET['id_zew']);
        $warunki_szukania .= " and (p.products_reference_number_1 like '%".$szukana_wartosc."%' or
                                    p.products_reference_number_2 like '%".$szukana_wartosc."%' or
                                    p.products_reference_number_3 like '%".$szukana_wartosc."%' or
                                    p.products_reference_number_4 like '%".$szukana_wartosc."%' or
                                    p.products_reference_number_5 like '%".$szukana_wartosc."%' or
                                    p.products_id_private like '%".$szukana_wartosc."%')";
        unset($szukana_wartosc);
    }      

    // jezeli jest filtr zdjecia
    $nr_id_tmp_zdjecia = array(0);
    //
    if (isset($_GET['zdjecia']) && !empty($_GET['zdjecia'])) { 
        //
        if ($_GET['zdjecia'] == 'bez_zdjecia') {
            $warunki_szukania .= " and ( p.products_image = '' or p.products_image is NULL )";
        }
        if ($_GET['zdjecia'] == 'bez_plikow_zdjecia') {
            //
            // zdjecia glowne produktow
            $sql_zdjecia = $db->open_query("SELECT products_id, products_image FROM products");
            while ($info_zdjecia = $sql_zdjecia->fetch_assoc()) {
                if ( !empty($info_zdjecia['products_image']) ) {
                     if ( !file_exists('../' . KATALOG_ZDJEC . '/' . $info_zdjecia['products_image']) ) {
                          $nr_id_tmp_zdjecia[ $info_zdjecia['products_id'] ] = $info_zdjecia['products_id'];
                     }
                }
            }
            $db->close_query($sql_zdjecia);
            //
            // zdjecia dodatkowe produktow
            $sql_zdjecia = $db->open_query("SELECT products_id, popup_images FROM additional_images");
            while ($info_zdjecia = $sql_zdjecia->fetch_assoc()) {
                if ( !empty($info_zdjecia['popup_images']) ) {
                     if ( !file_exists('../' . KATALOG_ZDJEC . '/' . $info_zdjecia['popup_images']) ) {
                          $nr_id_tmp_zdjecia[ $info_zdjecia['products_id'] ] = $info_zdjecia['products_id'];
                     }
                }
            }
            $db->close_query($sql_zdjecia);
            //
            $warunki_szukania .= " and p.products_id IN (" . implode(',', (array)$nr_id_tmp_zdjecia) . ")";
            //
        }
        //
    }        

    // jezeli jest wybrana grupa klienta
    if (isset($_GET['klienci']) && (int)$_GET['klienci'] > 0) {
        $id_klienta = (int)$_GET['klienci'];
        $warunki_szukania .= " and find_in_set(" . $id_klienta . ", p.customers_group_id) ";        
        unset($id_klienta);
    }    
    
    // jezeli jest zakres cen
    if (isset($_GET['cena_od']) && (float)$_GET['cena_od'] >= 0) {
        $cena = (float)$_GET['cena_od'];
        $warunki_szukania .= " and p.products_price_tax >= '".$cena."'";
        unset($cena);
    }
    if (isset($_GET['cena_do']) && (float)$_GET['cena_do'] >= 0) {
        $cena = (float)$_GET['cena_do'];
        $warunki_szukania .= " and p.products_price_tax <= '".$cena."'";
        unset($cena);
    }    

    // jezeli jest wybrana kategoria
    if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
        $id_kategorii = (int)$_GET['kategoria_id'];
        $warunki_szukania .= " and pc.categories_id = '".$id_kategorii."'";
        unset($id_kategorii);
    }
    
    // jezeli jest wybrany producent
    if (isset($_GET['producent']) && (int)$_GET['producent'] > 0) {
        $id_producenta = (int)$_GET['producent'];
        $warunki_szukania .= " and p.manufacturers_id = '".$id_producenta."'";
        unset($id_producenta);
    } 
    
    // jezeli jest wybrana waluta
    if (isset($_GET['waluta']) && (int)$_GET['waluta'] > 0) {
        $id_waluty = (int)$_GET['waluta'];
        $warunki_szukania .= " and p.products_currencies_id = '".$id_waluty."'";
        unset($id_waluty);
    }    

    // jezeli jest wybrany status
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        if ( $_GET['status'] != 'tak_listing' && $_GET['status'] != 'tak_nie_listing' ) {
             $warunki_szukania .= " and p.products_status = '".(($_GET['status'] == 'tak') ? '1' : '0')."'";
        } else if ( $_GET['status'] == 'tak_listing' ) { 
             $warunki_szukania .= " and p.products_status = '1' and p.listing_status = '0'";
        } else if ( $_GET['status'] == 'tak_nie_listing' ) { 
             $warunki_szukania .= " and p.products_status = '1' and p.listing_status = '1'";
        }
    }     
    
    // data dodania
    if ( isset($_GET['szukaj_data_dodania_od']) && $_GET['szukaj_data_dodania_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_dodania_od'] . ' 00:00:00')));
        $warunki_szukania .= " and p.products_date_added >= '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_data_dodania_do']) && $_GET['szukaj_data_dodania_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_dodania_do'] . ' 23:59:59')));
        $warunki_szukania .= " and p.products_date_added <= '".$szukana_wartosc."'";
    }    
    
    // data dostepnosci
    if ( isset($_GET['szukaj_data_dostepnosci_od']) && $_GET['szukaj_data_dostepnosci_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_dostepnosci_od'])));
        $warunki_szukania .= " and p.products_date_available >= '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_data_dostepnosci_do']) && $_GET['szukaj_data_dostepnosci_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_dostepnosci_do'])));
        $warunki_szukania .= " and p.products_date_available <= '".$szukana_wartosc."'";
    }   

    // dostepnosc produktu
    if (isset($_GET['dostep']) && (int)$_GET['dostep'] > 0) {
        $id_dostepnosci = (int)$_GET['dostep'];
        $warunki_szukania .= " and p.products_availability_id = '".$id_dostepnosci."'";
        unset($id_dostepnosci);
    }   

    // aukcje allegro
    if (isset($_GET['allegro'])) {
        if ((int)$_GET['allegro'] == 1) {
            $warunki_szukania .= " and a.auction_id != ''";
        } elseif ((int)$_GET['allegro'] == 2) {
            $warunki_szukania .= " and a.auction_id IS NULL";
        }
    }    
    
    // ilosc magazynu
    if (isset($_GET['ilosc_od'])) {
        $ilosc = (float)$_GET['ilosc_od'];
        $warunki_szukania .= " and p.products_quantity >= '".$ilosc."'";
        unset($ilosc);
    }
    if (isset($_GET['ilosc_do'])) {
        $ilosc = (float)$_GET['ilosc_do'];
        $warunki_szukania .= " and p.products_quantity <= '".$ilosc."'";
        unset($ilosc);
    }       
    
    // jezeli jest opcja
    if (isset($_GET['opcja']) && !empty($_GET['opcja'])) {
        switch ($_GET['opcja']) {
            case "nowosc":
                $warunki_szukania .= " and p.new_status = '1'";
                break;
            case "promocja":
                $warunki_szukania .= " and p.specials_status = '1'";
                break;
            case "wyprzedaz":
                $warunki_szukania .= " and p.sale_status = '1'";
                break;                
            case "hit":
                $warunki_szukania .= " and p.star_status = '1'";
                break; 
            case "polecany":
                $warunki_szukania .= " and p.featured_status = '1'";
                break;   
            case "export":
                $warunki_szukania .= " and p.export_status = '1'";
                break; 
            case "negoc":
                $warunki_szukania .= " and p.products_make_an_offer = '1'";
                break;     
            case "wysylka_gratis":
                $warunki_szukania .= " and p.free_shipping_status = '1'";
                break;             
            case "wykluczona_darmowa_wysylka":
                $warunki_szukania .= " and p.free_shipping_excluded = '1'";
                break;
            case "ikona_1":
                $warunki_szukania .= " and p.icon_1_status = '1'";
                break; 
            case "ikona_2":
                $warunki_szukania .= " and p.icon_2_status = '1'";
                break;     
            case "ikona_3":
                $warunki_szukania .= " and p.icon_3_status = '1'";
                break;     
            case "ikona_4":
                $warunki_szukania .= " and p.icon_4_status = '1'";
                break;     
            case "ikona_5":
                $warunki_szukania .= " and p.icon_5_status = '1'";
                break;                     
        }     
    } 
    
    // jezeli sa dodatkowe opcje
    if (isset($_GET['dodatkowe_opcje']) && !empty($_GET['dodatkowe_opcje'])) {
        switch ($_GET['dodatkowe_opcje']) {
            case "bez_magazynu":
                $warunki_szukania .= " and p.products_control_storage = 0";
                break; 
            case "bez_magazynu_tak":
                $warunki_szukania .= " and p.products_control_storage = 1";
                break;    
            case "bez_magazynu_ograniczony":
                $warunki_szukania .= " and p.products_control_storage = 2";
                break;                    
            case "tylko_akcesoria":
                $warunki_szukania .= " and p.products_accessory = 1";
                break;          
            case "tylko_pkt":
                $warunki_szukania .= " and p.products_points_only = 1";
                break;
            case "znizki_ilosci":
                $warunki_szukania .= " and p.products_discount != ''";
                break;  
            case "bez_znizki_ilosci":
                $warunki_szukania .= " and (p.products_discount = '' or p.products_discount = NULL)";
                break;                 
            case "wylaczone_kupowanie":
                $warunki_szukania .= " and p.products_buy = '0'";
                break; 
            case "wylaczone_kupowanie_tak":
                $warunki_szukania .= " and p.products_buy = '1'";
                break; 
            case "wylaczone_szybkie_kupowanie":
                $warunki_szukania .= " and p.products_fast_buy = '0'";
                break; 
            case "wylaczone_szybkie_kupowanie_tak":
                $warunki_szukania .= " and p.products_fast_buy = '1'";
                break;                    
            case "wylaczony_listing":
                $warunki_szukania .= " and p.listing_status = '1'";
                break;  
            case "wylaczony_listing_tak":
                $warunki_szukania .= " and p.listing_status = '0'";
                break; 
            case "wylaczone_rabaty":
                $warunki_szukania .= " and p.products_not_discount = '1'";
                break;                   
            case "dodane_wysylki":
                $warunki_szukania .= " and p.shipping_method != ''";
                break;         
            case "wykluczony_punkt_odbioru":
                $warunki_szukania .= " and p.pickup_excluded = '1'";
                break;                    
            case "bez_wysylek":
                $warunki_szukania .= " and (p.shipping_method = '' or p.shipping_method IS NULL)";
                break;     
            case "wlaczone_komentarze":
                $warunki_szukania .= " and p.products_comments = '1'";
                break;   
            case "ceny_dla_zalogowanych":
                $warunki_szukania .= " and p.products_price_login = '1'";
                break;                 
            case "wykluczenie_porownywarek":
                $warunki_szukania .= " and p.export_status_exclude = '1'";
                break;                                 
            case "ceneo_kup_teraz":
                $warunki_szukania .= " and p.export_ceneo_buy_now = '1'";
                break;                                 
        }     
    }    
    
    // jezeli sa opcje brakow
    if (isset($_GET['brakujace_opcje']) && !empty($_GET['brakujace_opcje'])) {
        switch ($_GET['brakujace_opcje']) {
            case "bez_ean":
                $warunki_szukania .= " and (p.products_ean = '' or p.products_ean IS NULL {DODATKOWE_WARUNKI_CECH})";
                break;  
            case "bez_nr_kat":
                $warunki_szukania .= " and (p.products_model = '' or p.products_model IS NULL {DODATKOWE_WARUNKI_CECH})";
                break;  
            case "bez_kod_producenta":
                $warunki_szukania .= " and (p.products_man_code = '' or p.products_man_code IS NULL)";
                break;  
            case "bez_producenta":
                $warunki_szukania .= " and (p.manufacturers_id = '0' or p.manufacturers_id IS NULL)";
                break;
            case "bez_wysylek":
                $warunki_szukania .= " and (p.shipping_method = '' or p.shipping_method IS NULL)";
                break;
            case "bez_dostepnosci":
                $warunki_szukania .= " and (p.products_availability_id = '0' or p.products_availability_id IS NULL {DODATKOWE_WARUNKI_CECH})";
                break;   
            case "bez_czas_wysylki":
                $warunki_szukania .= " and (p.products_shipping_time_id = '0' or p.products_shipping_time_id IS NULL {DODATKOWE_WARUNKI_CECH})";
                break;      
            case "bez_gwarancji":
                $warunki_szukania .= " and (p.products_warranty_products_id = '0' or p.products_warranty_products_id IS NULL)";
                break;   
            case "bez_wagi":
                $warunki_szukania .= " and (p.products_weight = '0' or p.products_weight IS NULL)";
                break;         
            case "bez_lokalizacji":
                $warunki_szukania .= " and (p.products_weight = '' or p.location IS NULL)";
                break; 
            case "bez_opisu":
                $warunki_szukania .= " and (pd.products_description = '' or pd.products_description IS NULL)";
                break;  
            case "bez_opisu_krotkiego":
                $warunki_szukania .= " and (pd.products_short_description = '' or pd.products_short_description IS NULL)";
                break;                    
            case "bez_ceny_zakupu":
                $warunki_szukania .= " and (p.products_purchase_price = 0 or p.products_purchase_price IS NULL)";
                break;                         
        }     
    }    
    
    $nr_id_cech = array();
    
    // szukanie id produktow bez parametrow jezeli wybrane sa cechy
    if (isset($_GET['dodatkowe_opcje']) && !empty($_GET['dodatkowe_opcje'])) {
        //
        if ( $_GET['dodatkowe_opcje'] == 'cechy' ) {
             //
             if ( isset($_GET['brakujace_opcje']) && !empty($_GET['brakujace_opcje']) ) {
                  //
                  $warunki_szukania_cech = '';
                  //
                  switch ($_GET['brakujace_opcje']) {
                      case "bez_ean":
                          $warunki_szukania_cech = "(products_stock_ean = '' or products_stock_ean IS NULL)";
                          break;  
                      case "bez_nr_kat":
                          $warunki_szukania_cech = "(products_stock_model = '' or products_stock_model IS NULL)";
                          break;  
                      case "bez_dostepnosci":
                           $warunki_szukania_cech = "(products_stock_availability_id = '0' or products_stock_availability_id IS NULL)";
                          break;   
                      case "bez_czas_wysylki":
                          $warunki_szukania_cech = "(products_stock_shipping_time_id = '0' or products_stock_shipping_time_id IS NULL)";
                          break;                     
                  } 
                  //
                  if ( $warunki_szukania_cech != '' ) {

                      $sql_cechy = $db->open_query("SELECT products_id FROM products_stock WHERE " . $warunki_szukania_cech);
                      while ($info_cechy = $sql_cechy->fetch_assoc()) {
                          $nr_id_cech[ $info_cechy['products_id'] ] = $info_cechy['products_id'];
                      }
                      $db->close_query($sql_cechy);
                      unset($szukana_wartosc);
                  
                  }                  
                  //
             }
             //
        } 
        //
    }

    $warunki_cech = '';
    if ( isset($_GET['cecha_nazwa']) ) {
         $warunki_cech .= ' and pa.options_id = "' . (int)$_GET['cecha_nazwa'] . '"';
    }
    if ( isset($_GET['cecha_wartosc']) ) {
         $warunki_cech .= ' and pa.options_values_id = "' . (int)$_GET['cecha_wartosc'] . '"';
    }
    
    $warunki_dod_pol = '';
    if ( isset($_GET['dod_pole_nazwa']) ) {
         $warunki_dod_pol .= ' and ptpef.products_extra_fields_id = "' . (int)$_GET['dod_pole_nazwa'] . '"';
    }
    if ( isset($_GET['dod_pole_wartosc']) ) {
         $warunki_dod_pol .= ' and (ptpef.products_extra_fields_value = "' . base64_decode($filtr->process($_GET['dod_pole_wartosc'])) . '" or ptpef.products_extra_fields_value_1 = "' . base64_decode($filtr->process($_GET['dod_pole_wartosc'])) . '" or ptpef.products_extra_fields_value_2 = "' . base64_decode($filtr->process($_GET['dod_pole_wartosc'])) . '")';
    }    
    
    // jezeli jest kod licencyjny
    if (isset($_GET['kod_licencyjny']) && !empty($_GET['kod_licencyjny'])) {
        $szukana_wartosc = $filtr->process($_GET['kod_licencyjny']);
        $warunki_szukania .= " and (p.products_code_shopping like '%".$szukana_wartosc."%')";
        unset($szukana_wartosc);
    }    
    
    // id zewnetrzne
    if (isset($_GET['id_zewnetrzne']) && !empty($_GET['id_zewnetrzne']) && PRODUKTY_LISTING_ID_ZEWNETRZNE == 'tak') {
        $szukana_wartosc = $filtr->process($_GET['id_zewnetrzne']);
        $warunki_szukania .= " and (p.products_id_private = '".$szukana_wartosc."')";
        unset($szukana_wartosc);
    }  
    
    // filtr wysylki
    if (isset($_GET['przypisana_wysylka']) && (int)$_GET['przypisana_wysylka'] > 0) {
        //
        $tab_id = array(0);            
        // ustala id produktow z ta wysylka
        $sql_wysylka = $db->open_query("SELECT products_id, shipping_method FROM products WHERE shipping_method != '' AND shipping_method IS NOT NULL");
        while ($info_wysylka = $sql_wysylka->fetch_assoc()) {
            $tmp = explode(';', (string)$info_wysylka['shipping_method']);
            if (in_array((string)$_GET['przypisana_wysylka'], $tmp)) {
                $tab_id[$info_wysylka['products_id']] = $info_wysylka['products_id'];
            }
        }
        $db->close_query($sql_wysylka);
        unset($info_wysylka);
        //
        if (count($tab_id) > 0) {
            $warunki_szukania .= ' AND p.products_id IN (' . implode(',', (array)$tab_id) . ')';
        }
    }    

    // jezeli jest blad w produktach
    if (isset($_GET['blad']) && !empty($_GET['blad'])) { 
        switch ($_GET['blad']) {
            case "brutto":
                $warunki_szukania .= " and (( p.products_price_tax = 0 and p.products_price > 0 )";
                for ($x = 2; $x <= ILOSC_CEN; $x++) {
                    $warunki_szukania .= " or ( p.products_price_tax_" . $x . " = 0 and p.products_price_" . $x . " > 0 )";
                }
                $warunki_szukania .= ")";
                break; 
            case "vat":
                $warunki_szukania .= " and (( p.products_tax = 0 and p.products_price > 0.02 )";
                for ($x = 2; $x <= ILOSC_CEN; $x++) {
                    $warunki_szukania .= " or ( p.products_tax_" . $x . " = 0 and p.products_price_" . $x . " > 0.02 )";
                }
                $warunki_szukania .= ")";                
                break;
            case "kategoria":
                $warunki_szukania .= " and (pc.categories_id is null or pc.categories_id = '0')";
                break;
            case "nazwa":
                $warunki_szukania .= " and pd.products_name = ''";
                break;                
            case "kategoriawyl":
                $warunki_szukania .= " and c.categories_status = '0'";
                break;                
            case "brakopisu":
                $warunki_szukania .= " and pd.products_description = ''";
                break;                
        }  
    }  
    
    if ( isset($nr_id_tmp_nr_kat) && count($nr_id_tmp_nr_kat) > 0 ) {
         $warunki_szukania = str_replace('{DODATKOWE_WARUNKI_NR_KAT}', ' or p.products_id IN (' . implode(',', (array)$nr_id_tmp_nr_kat) . ')', (string)$warunki_szukania);
    } else {
         $warunki_szukania = str_replace('{DODATKOWE_WARUNKI_NR_KAT}', '', (string)$warunki_szukania);
    }
    if ( isset($nr_id_tmp_ean) && count($nr_id_tmp_ean) > 0 ) {
         $warunki_szukania = str_replace('{DODATKOWE_WARUNKI_EAN}', ' or p.products_id IN (' . implode(',', (array)$nr_id_tmp_ean) . ')', (string)$warunki_szukania);
    } else {
         $warunki_szukania = str_replace('{DODATKOWE_WARUNKI_EAN}', '', (string)$warunki_szukania);
    }    
    if ( isset($nr_id_cech) && count($nr_id_cech) > 0 ) {
         $warunki_szukania = str_replace('{DODATKOWE_WARUNKI_CECH}', ' or p.products_id IN (' . implode(',', (array)$nr_id_cech) . ')', (string)$warunki_szukania);
    } else {
         $warunki_szukania = str_replace('{DODATKOWE_WARUNKI_CECH}', '', (string)$warunki_szukania);
    }         
    
    unset($nr_id_tmp_nr_kat, $nr_id_tmp_ean);
    
    $zapytanie = 'SELECT p.products_id, 
                         p.products_price_tax, 
                         p.products_tax,
                         p.products_old_price,
                         p.products_quantity,
                         p.sort_order,
                         p.customers_group_id,
                         p.manufacturers_id,
                         p.products_image, 
                         p.products_model,
                         p.products_ean,
                         p.products_man_code,
                         p.products_date_added, 
                         p.products_status, 
                         p.products_buy,
                         p.products_make_an_offer, 
                         p.new_status,
                         p.star_status,
                         p.star_date,
                         p.star_date_end,                         
                         p.specials_status,                         
                         p.specials_date,
                         p.specials_date_end,
                         p.sale_status,
                         p.featured_status,
                         p.featured_date,
                         p.featured_date_end,                         
                         p.export_status,
                         p.free_shipping_status,
                         p.free_shipping_excluded,
                         p.listing_status,
                         p.icon_1_status,
                         p.icon_2_status,
                         p.icon_3_status,
                         p.icon_4_status,
                         p.icon_5_status,
                         p.products_points_only,
                         p.products_points_value,
                         p.products_points_value_money,
                         p.products_currencies_id,
                         p.products_tax_class_id,
                         p.products_control_storage,
                         p.products_purchase_price,   
                         p.options_type,
                         p.products_id_private,
                         p.products_date_available_end,
                         pd.language_id, 
                         pd.products_name, 
                         pd.products_description, 
                         pd.products_seo_url,
                         '.((isset($_GET['kategoria_id']) || (isset($_GET['blad']) && ($_GET['blad'] == 'kategoria' || $_GET['blad'] == 'kategoriawyl'))) ? 'pc.categories_id,' : '').'
                         m.manufacturers_id,
                         m.manufacturers_name,
                         a.auction_id,
                         '.((isset($_GET['blad']) && $_GET['blad'] == 'kategoriawyl') ? 'c.categories_id, c.categories_status,' : '').'
                         pj.products_jm_quantity_type
                         ' . (( isset($_GET['dodatkowe_opcje']) && $_GET['dodatkowe_opcje'] == 'cechy' ) ? ', count(pa.products_attributes_id) as IloscCech' : '') . '
                  FROM products p
                         '.((isset($_GET['kategoria_id']) || (isset($_GET['blad']) && ($_GET['blad'] == 'kategoria' || $_GET['blad'] == 'kategoriawyl' ))) ? 'LEFT JOIN products_to_categories pc ON pc.products_id = p.products_id' : '').'
                         LEFT JOIN products_description pd ON pd.products_id = p.products_id
                         AND pd.language_id = "' . (int)$_SESSION['domyslny_jezyk']['id'] . '"
                         '.((isset($_GET['blad']) && $_GET['blad'] == 'kategoriawyl') ? 'RIGHT JOIN categories c ON c.categories_id = pc.categories_id' : '').'
                         LEFT JOIN manufacturers m ON m.manufacturers_id = p.manufacturers_id
                         LEFT JOIN products_jm pj ON p.products_jm_id = pj.products_jm_id
                         ' . (( (isset($_GET['dodatkowe_opcje']) && $_GET['dodatkowe_opcje'] == 'cechy') || isset($_GET['cecha_nazwa']) ) ? 'RIGHT JOIN products_attributes pa ON p.products_id = pa.products_id' . $warunki_cech : '') . '                         
                         ' . (( (isset($_GET['dodatkowe_opcje']) && $_GET['dodatkowe_opcje'] == 'pola') || isset($_GET['dod_pole_nazwa']) ) ? 'RIGHT JOIN products_to_products_extra_fields ptpef ON p.products_id = ptpef.products_id' . $warunki_dod_pol : '') . '
                         LEFT JOIN allegro_auctions a ON a.products_id = p.products_id AND a.auction_status = "ACTIVE" AND (auction_date_end >= now() OR auction_date_end = "1970-01-01 01:00:00") 
                         WHERE '.((isset($_GET['blad']) && ($_GET['blad'] == 'kategoriawyl' )) ? '( SELECT count(*) AS IloscKat FROM products_to_categories pc WHERE pc.products_id = p.products_id ) = 1 AND ': '') . ' p.products_set = 0 ' . $warunki_szukania . '
                         ' . ( (isset($_GET['dodatkowe_opcje']) && $_GET['dodatkowe_opcje'] == 'bez_cechy') ? ' AND p.products_id not in ( SELECT products_id FROM products_attributes )' : '') . '
                         ' . ( (isset($_GET['dodatkowe_opcje']) && $_GET['dodatkowe_opcje'] == 'bez_pola') ? ' AND p.products_id not in ( SELECT products_id FROM products_to_products_extra_fields )' : '') . '
                         GROUP BY p.products_id'; 

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ZapytanieDlaPozycji = 'SELECT p.products_id
                         FROM products p
                         '.((isset($_GET['kategoria_id']) || (isset($_GET['blad']) && ($_GET['blad'] == 'kategoria' || $_GET['blad'] == 'kategoriawyl' ))) ? 'LEFT JOIN products_to_categories pc ON pc.products_id = p.products_id' : '').'
                         LEFT JOIN products_description pd ON pd.products_id = p.products_id
                         AND pd.language_id = "' . (int)$_SESSION['domyslny_jezyk']['id'] . '"
                         '.((isset($_GET['blad']) && $_GET['blad'] == 'kategoriawyl') ? 'RIGHT JOIN categories c ON c.categories_id = pc.categories_id' : '').'
                         LEFT JOIN manufacturers m ON m.manufacturers_id = p.manufacturers_id';
                         if ( (isset($_GET['dodatkowe_opcje']) && $_GET['dodatkowe_opcje'] == 'cechy') || isset($_GET['cecha_nazwa']) ) {
                            $ZapytanieDlaPozycji .= ' RIGHT JOIN products_attributes pa ON p.products_id = pa.products_id' . $warunki_cech;
                         }
                         if ( (isset($_GET['dodatkowe_opcje']) && $_GET['dodatkowe_opcje'] == 'pola') || isset($_GET['dod_pole_nazwa']) ) {
                            $ZapytanieDlaPozycji .= ' RIGHT JOIN products_to_products_extra_fields ptpef ON p.products_id = ptpef.products_id' . $warunki_dod_pol;
                         }                         
                         if ( isset($_GET['allegro']) && ((int)$_GET['allegro'] == 1 || (int)$_GET['allegro'] == 2) ) {
                            $ZapytanieDlaPozycji .= ' LEFT JOIN allegro_auctions a ON a.products_id = p.products_id AND a.auction_status = "ACTIVE" AND (auction_date_end >= now() OR auction_date_end = "1970-01-01 01:00:00")';
                         }
    $ZapytanieDlaPozycji .= ' WHERE  '.((isset($_GET['blad']) && ($_GET['blad'] == 'kategoriawyl' )) ? '( SELECT count(*) AS IloscKat FROM products_to_categories pc WHERE pc.products_id = p.products_id ) = 1 AND ': '') . ' p.products_set = 0 ' . $warunki_szukania . 
                         ( (isset($_GET['dodatkowe_opcje']) && $_GET['dodatkowe_opcje'] == 'bez_cechy') ? ' AND p.products_id not in ( SELECT products_id FROM products_attributes )' : '') . 
                         ( (isset($_GET['dodatkowe_opcje']) && $_GET['dodatkowe_opcje'] == 'bez_pola') ? ' AND p.products_id not in ( SELECT products_id FROM products_to_products_extra_fields )' : '') . ' GROUP BY p.products_id ';

    $sql = $db->open_query($ZapytanieDlaPozycji);
    $ile_pozycji = (int)$db->ile_rekordow($sql);

    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }

    $db->close_query($sql);
    
    // jezeli jest sortowanie
    $sortowanie = $GLOBALS['DomyslneSortowanie'];
    //
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case "sort_a17":
                $sortowanie = 'pd.products_name asc, p.products_id';
                break;
            case "sort_a2":
                $sortowanie = 'pd.products_name desc, p.products_id';
                break;
            case "sort_a7":
                $sortowanie = 'p.products_model asc, p.products_id';
                break;
            case "sort_a8":
                $sortowanie = 'p.products_model desc, p.products_id';
                break;  
            case "sort_a9":
                $sortowanie = 'p.products_price_tax asc, p.products_id';
                break;
            case "sort_a10":
                $sortowanie = 'p.products_price_tax desc, p.products_id';
                break;  
            case "sort_a11":
                $sortowanie = 'p.products_quantity asc, p.products_id';
                break;
            case "sort_a12":
                $sortowanie = 'p.products_quantity desc, p.products_id';
                break;                            
            case "sort_a3":
                $sortowanie = 'p.products_status desc, pd.products_name, p.products_id';
                break;  
            case "sort_a4":
                $sortowanie = 'p.products_status asc, pd.products_name, p.products_id';
                break;
            case "sort_a5":
                $sortowanie = 'p.products_date_added asc, pd.products_name, p.products_id';
                break; 
            case "sort_a6":
                $sortowanie = 'p.products_date_added desc, pd.products_name, p.products_id';
                break; 
            case "sort_a13":
                $sortowanie = 'p.products_id desc';
                break;
            case "sort_a14":
                $sortowanie = 'p.products_id asc';
                break;    
            case "sort_a15":
                $sortowanie = 'p.sort_order desc, p.products_id';
                break;
            case "sort_a16":
                $sortowanie = 'p.sort_order asc, p.products_id';
                break;                        
        }            
    }  
    
    $zapytanie .= (($sortowanie != '') ? " order by ".$sortowanie : '');    
    
    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
        
            $zapytanie .= " limit ".$_GET['parametr'];

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array();
            $tablica_naglowek[] = array('Akcja','center');
            $tablica_naglowek[] = array('ID','center');
            $tablica_naglowek[] = array('Zdjęcie','center');  
            $tablica_naglowek[] = array('Nazwa produktu');
            $tablica_naglowek[] = array('Cena');
            $tablica_naglowek[] = array('Ilość','center');
            $tablica_naglowek[] = array('Sort','center');
            $tablica_naglowek[] = array('Status','center');
            
            echo $listing_danych->naglowek($tablica_naglowek);

            $tekst = '';

            while ($info = $sql->fetch_assoc()) {
                  
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['products_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['products_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['products_id'].'">';
                  } 

                  $tablica = array();

                  $tablica[] = array('<input type="checkbox" name="opcja[]" value="'.$info['products_id'].'" id="opcja_'.$info['products_id'].'" /><label class="OpisForPustyLabel" for="opcja_'.$info['products_id'].'"></label><input type="hidden" name="id[]" value="'.$info['products_id'].'" />','center');
                  
                  $tablica[] = array($info['products_id'],'center'); 

                  // czyszczenie z &nbsp; i zbyt dlugiej nazwy
                  $info['products_name'] = Funkcje::PodzielNazwe($info['products_name']);
                  $info['products_model'] = Funkcje::PodzielNazwe($info['products_model']);

                  if ( !empty($info['products_image']) ) {
                       //
                       $tgm = '<div id="zoom'.rand(1,99999).'" class="imgzoom" onmouseover="ZoomIn(this,event)" onmouseout="ZoomOut(this)">';
                       $tgm .= '<div class="zoom" id="duze_foto_' . $info['products_id'] . '">' . Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '250', '250') . '</div>';
                       $tgm .= '<div id="male_foto_' . $info['products_id'] . '">' . Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '40', '40', ' class="Reload"', true) . '</div>';
                       $tgm .= '</div>';
                       //
                     } else { 
                       //
                       $tgm = '-';
                       //
                  }
                  
                  $tablica[] = array($tgm, 'center');    
                  
                  // ladowanie info o produkcie z zew pliku
                  include('produkty/produkt_info_nazwa.php');
                  $tablica[] = array($tgm);
                  unset($tgm, $tgm_ajax);
                  
                  unset($do_jakich_kategorii_przypisany, $nr_kat, $kod_producenta, $prd, $allegro);
                  
                  $IkonaWyprzedaz = '';
                  if ( $info['sale_status'] == '1' && $info['specials_status'] == '0'  ) {
                     $IkonaWyprzedaz = '<em class="TipChmurka"><b>Cena jest wyprzedażą</b><img src="obrazki/wyprzedaz.png" alt="Wyprzedaż" /></em>';
                  }
                  
                  if ( ((FunkcjeWlasnePHP::my_strtotime($info['specials_date']) > time() && $info['specials_date'] != '0000-00-00 00:00:00') || (FunkcjeWlasnePHP::my_strtotime($info['specials_date_end']) < time() && $info['specials_date_end'] != '0000-00-00 00:00:00') ) ) {
                     $IkonaPromocja = '<em class="TipChmurka"><b>Promocja nie jest wyświetlana ze względu na datę rozpoczęcia lub zakończenia promocji</b><img src="obrazki/promocja_wylaczona.png" alt="Promocja nieaktywna" /></em>';
                   } else {
                     $IkonaPromocja = '<em class="TipChmurka"><b>Cena jest promocyjna</b><img src="obrazki/promocja.png" alt="Cena jest promocyjna" /></em>';
                  }

                  $tablica[] = array('<div class="cena" style="white-space:nowrap">Cena brutto: '.(($info['specials_status'] == '1' || Funkcje::czyNiePuste($info['specials_date']) || Funkcje::czyNiePuste($info['specials_date_end'])) ? $IkonaPromocja : '') . $IkonaWyprzedaz . 
                                      (($info['options_type'] == 'ceny') ? '<em class="TipChmurka"><b>Różne ceny produktu przypisane na stałe do kombinacji cech</b><img src="obrazki/cena_cechy.png" alt="Skasuj" /></em>' : '') . '<br /><input type="text" name="cena_'.$info['products_id'].'" value="'.$info['products_price_tax'].'" class="CenaProduktuPole" onchange="zamien_krp($(this))" />                              
                                      Cena poprzednia:<br />
                                      <input type="text" name="cenaold_'.$info['products_id'].'" value="'.(((float)$info['products_old_price'] == 0) ? '' : $info['products_old_price']).'" class="CenaProduktuPole" onchange="zamien_krp($(this))" />                                      
                                      </div>
                                      <div class="Waluta">Waluta: <span>'.$waluty->ZwrocSymbolWaluty($info['products_currencies_id']).'</span></div>
                                      <div class="Waluta">Podatek: <span>'.$tablicaVat[$info['products_tax_class_id']].'</span></div>'.
                                      (($info['products_points_only'] == 1) ? '<div class="TylkoPkt">' . $info['products_points_value'] . ' pkt + ' . $waluty->FormatujCene($info['products_points_value_money'],false) . '</div>' : ''));                  
                                      
                  unset($IkonaPromocja, $IkonaWyprzedaz);
                                
                  // ilosc  
                  // jezeli jednostka miary calkowita
                  if ( $info['products_jm_quantity_type'] == 1 ) {
                       $info['products_quantity'] = (int)$info['products_quantity'];
                  }                     
                  //$tablica[] = array((($info['products_quantity'] <= 0) ? '<span class="NiskiStan">'.$info['products_quantity'].'</span>' : $info['products_quantity']),'center');                                       
                  
                  // musi sprawdzic czy nie jest wlaczony stan magazynowy cech i produkt nie ma cech
                  $InputIlosc = '<input type="text" name="ilosc_'.$info['products_id'].'" value="'.$info['products_quantity'].'" class="PoleEdycja" onchange="zamien_krp($(this),0,' . $info['products_jm_quantity_type'] . ')" />';
                  if (CECHY_MAGAZYN == 'tak' && $info['products_control_storage'] == '1') {
                      $cechy = "select distinct * from products_attributes where products_id = '".$info['products_id']."'";
                      $sqlc = $db->open_query($cechy); 
                      //
                      if ($db->ile_rekordow($sqlc) > 0) {
                          $InputIlosc = '<div class="IloscCechy"><input type="text" name="ilosc_'.$info['products_id'].'" value="'.$info['products_quantity'].'" class="PoleEdycja" disabled="disabled" /><em class="TipIkona"><b>Ilość określana na podstawie sumy stanów magazynowych cech</b></em></div>';
                      }
                      //
                      $db->close_query($sqlc);
                  }                  
                  $tablica[] = array((($info['products_quantity'] <= 0) ? '<span class="NiskiStan">' . $InputIlosc . '</span>' : $InputIlosc),'center');  
                  
                  // sort
                  $tablica[] = array('<input type="text" name="sort_'.$info['products_id'].'" value="'.$info['sort_order'].'" class="sort_prod" />','center');                    
                  
                  // aktywany czy nieaktywny
                  $bezKupowania = '';
                  if ($info['products_buy'] == '0') {
                      $bezKupowania = '<div class="BezKupowania TipChmurka"><b>Produktu nie można kupować</b></div>';
                  }               
                  $bezMagazynu = '';
                  if ($info['products_control_storage'] == '0') {
                      $bezMagazynu = '<div class="BezMagazynu TipChmurka"><b>Produkt ma wyłączoną kontrolę stanu magazynowego</b></div>';
                  }                 

                  // czy jest data wylaczenia
                  $wylaczonaData = '';
                  if ( Funkcje::czyNiePuste($info['products_date_available_end']) && $info['products_status'] == 0 ) {
                       $wylaczonaData = '<div class="DataDostepnosciWylaczona TipChmurka"><b>Produkt wyłączony - data dostępności do: ' . date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($info['products_date_available_end'])) . '</b></div>';
                  }
                  
                  $tablica[] = array( $wylaczonaData . $bezKupowania . (($wylacz_status == true) ? '<div class="wylKat TipChmurka"><b>Kategoria do której należy produkt jest wyłączona</b>' : '') . '<input type="checkbox" name="status_'.$info['products_id'].'" value="1" '.(($info['products_status'] == '1') ? 'checked="checked"' : '').' id="status_'.$info['products_id'].'" /><label class="OpisForPustyLabel" for="status_'.$info['products_id'].'"></label>' . (($wylacz_status == true) ? '</div>' : '') . $bezMagazynu,'center');
                  unset($bezKupowania, $bezMagazynu); 
                  
                  $tekst .= $listing_danych->pozycje($tablica);

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.$info['products_id'];   

                  // ustala jaka ma byc tresc linku
                  $linkSeo = ((!empty($info['products_seo_url'])) ? $info['products_seo_url'] : $info['products_name']);                  
                                      
                  $tekst .= '<td class="rg_right IkonyPionowo">';                 
                  $tekst .= '<a class="TipChmurka" href="produkty/produkty_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>'; 
                  $tekst .= '<a class="TipChmurka" href="produkty/produkty_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>'; 
                  
                  if ( Funkcje::SprawdzAktywneAllegro() ) {
                       $tekst .= '<a class="TipChmurka" href="allegro/allegro_wystaw_aukcje.php'.$zmienne_do_przekazania.'"><b>Wystaw na Allegro</b><img src="obrazki/allegro_lapka.png" alt="Wystaw na Allegro" /></a> <br /><br />'; 
                  } else {
                       $tekst .= '<br />';
                  }
                  
                  $tekst .= '<a class="TipChmurka" href="produkty/produkty_duplikuj.php'.$zmienne_do_przekazania.'"><b>Duplikuj</b><img src="obrazki/duplikuj.png" alt="Duplikuj" /></a>';                   
                  $tekst .= '<a class="TipChmurka" href="produkty/produkty_kopiuj.php'.$zmienne_do_przekazania.'"><b>Kopiuj opcje do innych produktów</b><img src="obrazki/import.png" alt="Kupiuj" /></a>';                   
                  $tekst .= '<a class="TipChmurka" target="_blank" href="' . Seo::link_SEO( $linkSeo, $info['products_id'], 'produkt', '', false ) . '"><b>Zobacz w sklepie</b><img src="obrazki/zobacz.png" alt="Zobacz w sklepie" /></a>';
                  
                  $tekst .= '<br /><a class="TipChmurka" href="produkty_magazyn/produkty_magazyn_edytuj.php'.$zmienne_do_przekazania.'&produkt"><b>Magazyn</b><img src="obrazki/serwer.png" alt="Magazyn" /></a>';
                  $tekst .= '</td></tr>';   

                  $tekst .= '<tr class="pozycjaRwd"><td colspan="9" style="background:#efefef">';
                  
                      // nowosci - automatyczne czy reczne
                      $InputNowosci = '<li><input type="checkbox" name="nowosc_'.$info['products_id'].'" id="nowosc_'.$info['products_id'].'" value="1" '.(($info['new_status'] == '1') ? 'checked="checked"' : '').' /><label class="OpisFor" for="nowosc_'.$info['products_id'].'">nowość</label></li>';
                      if ( NOWOSCI_USTAWIENIA == 'automatycznie wg daty dodania' ) {
                           $InputNowosci = '<li><input type="checkbox" disabled="disabled" name="nowosc_'.$info['products_id'].'" id="nowosc_'.$info['products_id'].'" value="1" '.(($info['new_status'] == '1') ? 'checked="checked"' : '').' /> <label class="OpisFor" for="nowosc_'.$info['products_id'].'"> <span class="wylaczony">nowość</span> <em class="TipIkona"><b>Opcja nieaktywna - nowości określane na podstawie daty dodania</b></em></label></li>';
                      }
                      
                     $tekst .= '<ul class="opcje">
                                          ' . $InputNowosci . '
                                          <li><input type="checkbox" name="hit_'.$info['products_id'].'" id="hit_'.$info['products_id'].'" value="1" '.(($info['star_status'] == '1' || Funkcje::czyNiePuste($info['star_date']) || Funkcje::czyNiePuste($info['star_date_end'])) ? 'checked="checked"' : '').' /> <label class="OpisFor" for="hit_'.$info['products_id'].'">nasz hit</label></li>
                                          <li><input type="checkbox" name="promocja_'.$info['products_id'].'" id="promocja_'.$info['products_id'].'" value="1" '.(($info['specials_status'] == '1' || Funkcje::czyNiePuste($info['specials_date']) || Funkcje::czyNiePuste($info['specials_date_end'])) ? 'checked="checked"' : '').' /> <label class="OpisFor" for="promocja_'.$info['products_id'].'">promocja</label></li>
                                          <li><input type="checkbox" name="wyprzedaz_'.$info['products_id'].'" id="wyprzedaz_'.$info['products_id'].'" value="1" '.(($info['sale_status'] == '1') ? 'checked="checked"' : '').' /> <label class="OpisFor" for="wyprzedaz_'.$info['products_id'].'">wyprzedaż</label></li>
                                          <li><input type="checkbox" name="polecany_'.$info['products_id'].'" id="polecany_'.$info['products_id'].'" value="1" '.(($info['featured_status'] == '1' || Funkcje::czyNiePuste($info['featured_date']) || Funkcje::czyNiePuste($info['featured_date_end'])) ? 'checked="checked"' : '').' /> <label class="OpisFor" for="polecany_'.$info['products_id'].'">polecany</label></li>
                                          <li><input type="checkbox" name="export_'.$info['products_id'].'" id="export_'.$info['products_id'].'" value="1" '.(($info['export_status'] == '1') ? 'checked="checked"' : '').' /> <label class="OpisFor" for="export_'.$info['products_id'].'">do porównywarek </label></li>
                                          <li><input type="checkbox" name="negocjacja_'.$info['products_id'].'" id="negocjacja_'.$info['products_id'].'" value="1" '.(($info['products_make_an_offer'] == '1') ? 'checked="checked"' : '').' /> <label class="OpisFor" for="negocjacja_'.$info['products_id'].'"><span style="color:#ff0000">negocjacja ceny</span></li>
                                          <li class="OpcjeDarmowaWysylka' . (($info['free_shipping_excluded'] == 1) ? ' DarmowaUkryj' : '') . '"><input type="checkbox" name="wysylka_'.$info['products_id'].'" id="wysylka_'.$info['products_id'].'" value="1" '.(($info['free_shipping_status'] == '1') ? 'checked="checked"' : '').' /> <label class="OpisFor" for="wysylka_'.$info['products_id'].'"><span>darmowa wysyłka</span></label></li>' .
                                          (($info['free_shipping_excluded'] == 1) ? '<em class="TipChmurka WykluczonaWysylka"><b>Ten produkt jest wykluczony z darmowej wysyłki</b><img src="obrazki/uwaga.png" alt="Wykluczenie" /></em>' : '') . '
                                          <li><input type="checkbox" name="listing_'.$info['products_id'].'" id="listing_'.$info['products_id'].'" value="1" '.(($info['listing_status'] == '1') ? 'checked="checked"' : '').' /> <label class="OpisFor" for="listing_'.$info['products_id'].'">nie wyświetlaj w listingach</label></li> 
                                          <li><input type="checkbox" name="kupowanie_'.$info['products_id'].'" id="kupowanie_'.$info['products_id'].'" value="1" '.(($info['products_buy'] == '1') ? 'checked="checked"' : '').' /> <label class="OpisFor" for="kupowanie_'.$info['products_id'].'">produkt można kupować</label></li>';
                      
                      $TablicaOpcje = array(array('nr' => 1, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_1, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_1),
                                            array('nr' => 2, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_2, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_2),
                                            array('nr' => 3, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_3, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_3),
                                            array('nr' => 4, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_4, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_4),
                                            array('nr' => 5, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_5, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_5));
                      
                      foreach ( $TablicaOpcje as $Tmp ) {
                          //
                          if ( $Tmp['aktywne'] == 'tak' ) {
                               //
                               $NazwaIkonki = @unserialize($Tmp['nazwa']);
                               if ( is_array($NazwaIkonki) ) {
                                    if ( isset($NazwaIkonki[$_SESSION['domyslny_jezyk']['id']]) ) {                          
                                         $tekst .= '<li><input type="checkbox" name="ikona_' . $Tmp['nr'] . '_' . $info['products_id'] . '" id="ikona_' . $Tmp['nr'] . '_' . $info['products_id'] . '" value="1" '. (($info['icon_' . $Tmp['nr'] . '_status'] == '1') ? 'checked="checked"' : '') . ' /> <label class="OpisFor" for="ikona_' . $Tmp['nr'] . '_' . $info['products_id'] . '">' . $NazwaIkonki[$_SESSION['domyslny_jezyk']['id']] . '</label></li>';
                                    }
                               }
                                unset($NazwaIkonki);
                               //
                          }    
                          //
                      }              
                      $tekst .= '</ul>';                                      
                                          
                      unset($InputNowosci, $TablicaOpcje);

                  $tekst .= '</td></tr>';

                  unset($tablica, $linkSeo);
            } 
            $tekst .= '</table>';
            
            //
            echo $tekst;
            //
            $db->close_query($sql);
            unset($listing_danych,$tekst,$tablica,$tablica_naglowek);   
             
        }
    }  

    // ******************************************************************************************************************************************************************
    // wyswietlanie listingu
    if (!isset($_GET['parametr'])) { 

        // wczytanie naglowka HTML
        include('naglowek.inc.php');
        ?>

        <script>
        $(document).ready(function() {
          $.AutoUzupelnienie( 'szukaj', 'Podpowiedzi', 'ajax/autouzupelnienie_produkty.php', 50, 350 );
          
          $('input.datepicker').Zebra_DatePicker({
            format: 'd-m-Y',
            inside: false,
            readonly_element: false
          });     
          
          $('#pamietaj').click(function() {
             if ($(this).prop('checked') == true) {
                 createCookie("kategoria","tak");                 
               } else {
                 createCookie("kategoria","",-1);
             }
          });            
        });
        
        function edpr(id) {
          $("#edpr_"+id).html('<img src="obrazki/_loader_small.gif" alt="" />');
          $('.edpr').hide();
          $.get('ajax/produkt_szybka_edycja.php', { tok: '<?php echo Sesje::Token(); ?>', id: id }, function(data) {
              $("#edpr_"+id).html(data);
          });          
        }
        </script>     

        <div id="caly_listing">
        
            <div id="ajax"></div>
        
            <div id="naglowek_cont">Produkty</div>
            
            <div id="wyszukaj">
                <form action="produkty/produkty.php" method="post" id="poForm" class="cmxform"> 
                
                <div id="wyszukaj_text">
                    <span>Wyszukaj produkt:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="25" /> &nbsp;
                    <input type="checkbox" name="szukaj_tytul" id="szukaj_tytul" value="1" <?php echo (((isset($_GET['szukaj']) && isset($_GET['szukaj_tytul'])) || !isset($_GET['szukaj'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szukaj_tytul" style="margin-top:-3px;"> nazwa</label>
                    <input type="checkbox" name="szukaj_opis" id="szukaj_opis" value="1" <?php echo (((isset($_GET['szukaj']) && isset($_GET['szukaj_opis'])) || !isset($_GET['szukaj'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szukaj_opis" style="margin-top:-3px;"> opis</label>
                    <input type="checkbox" name="szukaj_nrkat" id="szukaj_nrkat" value="1" <?php echo (((isset($_GET['szukaj']) && isset($_GET['szukaj_nrkat'])) || !isset($_GET['szukaj'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szukaj_nrkat" style="margin-top:-3px;"> nr kat</label>
                    <input type="checkbox" name="szukaj_kodprod" id="szukaj_kodprod" value="1" <?php echo (((isset($_GET['szukaj']) && isset($_GET['szukaj_kodprod'])) || !isset($_GET['szukaj'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szukaj_kodprod" style="margin-top:-3px;"> kod producenta</label>
                </div>  

                <div class="wyszukaj_select">
                    <span>ID produktu:</span>
                    <input type="text" name="id_produktu" class="calkowita" value="<?php echo ((isset($_GET['id_produktu'])) ? (int)$_GET['id_produktu'] : ''); ?>" size="10" />
                </div>        

                <div class="wyszukaj_select">
                    <span>Kod EAN:</span>
                    <input type="text" name="ean" value="<?php echo ((isset($_GET['ean'])) ? $filtr->process($_GET['ean']) : ''); ?>" size="20" />
                </div>       

                <div class="wyszukaj_select">
                    <span>Producent:</span>                                        
                    <?php echo Funkcje::RozwijaneMenu('producent', Funkcje::TablicaProducenci('-- brak --'), ((isset($_GET['producent'])) ? $filtr->process($_GET['producent']) : '')); ?>
                </div>
                
                <div class="wyszukaj_select">
                    <span>Opcja:</span>
                    <?php
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- brak --');
                    $tablica[] = array('id' => 'nowosc', 'text' => 'Nowość');
                    $tablica[] = array('id' => 'hit', 'text' => 'Nasz hit');
                    $tablica[] = array('id' => 'promocja', 'text' => 'Promocja');
                    $tablica[] = array('id' => 'wyprzedaz', 'text' => 'Wyprzedaż');
                    $tablica[] = array('id' => 'polecany', 'text' => 'Polecany');
                    $tablica[] = array('id' => 'export', 'text' => 'Do porównywarek');
                    $tablica[] = array('id' => 'negoc', 'text' => 'Negocjacja ceny');
                    $tablica[] = array('id' => 'wysylka_gratis', 'text' => 'Darmowa wysyłka');
                    $tablica[] = array('id' => 'wykluczona_darmowa_wysylka', 'text' => 'Wykluczona darmowa wysyłka');
                    
                    $tab_opcje = array(array('nr' => 1, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_1, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_1),
                                       array('nr' => 2, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_2, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_2),
                                       array('nr' => 3, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_3, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_3),
                                       array('nr' => 4, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_4, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_4),
                                       array('nr' => 5, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_5, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_5));
                    
                    foreach ( $tab_opcje as $tmp ) {
                        //
                        if ( $tmp['aktywne'] == 'tak' ) {
                             //
                             $nazwa = @unserialize($tmp['nazwa']);
                             if ( is_array($nazwa) ) {
                                  if ( isset($nazwa[$_SESSION['domyslny_jezyk']['id']]) ) {                          
                                       $tablica[] = array('id' => 'ikona_' . $tmp['nr'], 'text' => $nazwa[$_SESSION['domyslny_jezyk']['id']]);
                                  }
                             }
                              unset($nazwa);
                             //
                        }    
                        //
                    }                                  
                    unset($tab_opcje, $tmp);
                    ?>                                          
                    <?php echo Funkcje::RozwijaneMenu('opcja', $tablica, ((isset($_GET['opcja'])) ? $filtr->process($_GET['opcja']) : '')); ?>
                </div>  
                
                <div class="wyszukaj_select">
                    <span>Grupa klientów:</span>
                    <?php                         
                    echo Funkcje::RozwijaneMenu('klienci', Klienci::ListaGrupKlientow(true), ((isset($_GET['klienci'])) ? $filtr->process($_GET['klienci']) : '')); 
                    unset($tablica);
                    ?>
                </div>                 

                <div class="wyszukaj_select">
                    <span>Nr referencyjny / id zewnętrzne:</span>
                    <input type="text" name="id_zew" value="<?php echo ((isset($_GET['id_zew'])) ? $filtr->process($_GET['id_zew']) : ''); ?>" size="20" />
                </div>                   
                
                <div class="wyszukaj_select">
                    <span>Cena brutto:</span>
                    <input type="text" name="cena_od" value="<?php echo ((isset($_GET['cena_od'])) ? $filtr->process($_GET['cena_od']) : ''); ?>" size="6" /> do
                    <input type="text" name="cena_do" value="<?php echo ((isset($_GET['cena_do'])) ? $filtr->process($_GET['cena_do']) : ''); ?>" size="6" />
                </div>
                
                <?php
                $sqls = $db->open_query("select * from currencies");  
                //
                $tablica = array();
                $tablica[] = array('id' => '', 'text' => '-- dowolna --');
                //
                while ($infs = $sqls->fetch_assoc()) { 
                    $tablica[] = array('id' => $infs['currencies_id'], 'text' => $infs['title']);
                }
                $db->close_query($sqls);
                unset($infs);  
                //             
                ?>
                <div class="wyszukaj_select">
                    <span>Waluta:</span>
                    <?php                         
                    echo Funkcje::RozwijaneMenu('waluta', $tablica, ((isset($_GET['waluta'])) ? $filtr->process($_GET['waluta']) : '')); 
                    unset($tablica);
                    ?>
                </div>                 
                <?php
                unset($tablica);
                ?>
                
                <?php  
                //
                $tablica = array();
                $tablica[] = array('id' => '', 'text' => '-- dowolny --');
                $tablica[] = array('id' => 'tak', 'text' => 'aktywne');
                $tablica[] = array('id' => 'tak_listing', 'text' => 'aktywne - wyświetlane w listingach');
                $tablica[] = array('id' => 'tak_nie_listing', 'text' => 'aktywne - nie wyświetlane w listingach');
                $tablica[] = array('id' => 'nie', 'text' => 'nieaktywne');
                //             
                ?>
                <div class="wyszukaj_select">
                    <span>Status:</span>
                    <?php                         
                    echo Funkcje::RozwijaneMenu('status', $tablica, ((isset($_GET['status'])) ? $filtr->process($_GET['status']) : ''), ' style="max-width:180px"'); 
                    unset($tablica);
                    ?>
                </div>                 
                <?php
                unset($tablica);
                ?>     

                <div class="wyszukaj_select">
                    <span>Data dodania:</span>
                    <input type="text" id="data_dodania_od" name="szukaj_data_dodania_od" value="<?php echo ((isset($_GET['szukaj_data_dodania_od'])) ? $filtr->process($_GET['szukaj_data_dodania_od']) : ''); ?>" size="8" class="datepicker" /> do 
                    <input type="text" id="data_dodania_do" name="szukaj_data_dodania_do" value="<?php echo ((isset($_GET['szukaj_data_dodania_do'])) ? $filtr->process($_GET['szukaj_data_dodania_do']) : ''); ?>" size="8" class="datepicker" />
                </div>   

                <div class="wyszukaj_select">
                    <span>Data dostępności:</span>
                    <input type="text" id="data_dostepnosci_od" name="szukaj_data_dostepnosci_od" value="<?php echo ((isset($_GET['szukaj_data_dostepnosci_od'])) ? $filtr->process($_GET['szukaj_data_dostepnosci_od']) : ''); ?>" size="8" class="datepicker" /> do 
                    <input type="text" id="data_dostepnosci_do" name="szukaj_data_dostepnosci_do" value="<?php echo ((isset($_GET['szukaj_data_dostepnosci_do'])) ? $filtr->process($_GET['szukaj_data_dostepnosci_do']) : ''); ?>" size="8" class="datepicker" />
                </div> 

                <div class="wyszukaj_select">
                    <span>Ilość magazynu:</span>
                    <input type="text" name="ilosc_od" class="calkowita" value="<?php echo ((isset($_GET['ilosc_od'])) ? $filtr->process($_GET['ilosc_od']) : ''); ?>" size="4" /> do
                    <input type="text" name="ilosc_do" class="calkowita" value="<?php echo ((isset($_GET['ilosc_do'])) ? $filtr->process($_GET['ilosc_do']) : ''); ?>" size="4" />
                </div>                

                <div class="wyszukaj_select">
                    <span>Stan dostępności:</span>                                         
                    <?php 
                    echo Funkcje::RozwijaneMenu('dostep', Produkty::TablicaDostepnosci('-- brak --'), ((isset($_GET['dostep'])) ? $filtr->process($_GET['dostep']) : '')); 
                    ?>
                </div>  

                <div class="wyszukaj_select">
                    <span>Allegro:</span>                                         
                    <?php  
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- dowolne --');
                    $tablica[] = array('id' => '1', 'text' => 'produkty z aukcjami Allegro');
                    $tablica[] = array('id' => '2', 'text' => 'produkty bez aukcji Allegro');
                    //             
                    echo Funkcje::RozwijaneMenu('allegro', $tablica, ((isset($_GET['allegro'])) ? $filtr->process($_GET['allegro']) : '')); 
                    unset($tablica);
                    ?>
                </div>   

                <div class="wyszukaj_select">
                    <span>Dodatkowe opcje:</span>
                    <?php
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- brak --');
                    $tablica[] = array('id' => 'cechy', 'text' => 'produkty z cechami');
                    $tablica[] = array('id' => 'bez_cechy', 'text' => 'produkty bez cech');
                    $tablica[] = array('id' => 'pola', 'text' => 'produkty z dodatkowymi polami');
                    $tablica[] = array('id' => 'bez_pola', 'text' => 'produkty bez dodatkowych pól');                    
                    $tablica[] = array('id' => 'znizki_ilosci', 'text' => 'produkty ze zniżkami od ilości');
                    $tablica[] = array('id' => 'bez_znizki_ilosci', 'text' => 'produkty bez zniżek od ilości');
                    $tablica[] = array('id' => 'tylko_pkt', 'text' => 'produkty tylko za punkty');
                    $tablica[] = array('id' => 'tylko_akcesoria', 'text' => 'produkty dostępne tylko jako akcesoria dodatkowe');
                    $tablica[] = array('id' => 'bez_magazynu', 'text' => 'produkty z wyłączoną obsługą magazynu');
                    $tablica[] = array('id' => 'bez_magazynu_tak', 'text' => 'produkty z włączoną obsługą magazynu');
                    $tablica[] = array('id' => 'bez_magazynu_ograniczony', 'text' => 'produkty z ograniczoną obsługą magazynu');
                    $tablica[] = array('id' => 'wylaczone_kupowanie', 'text' => 'produkty z wyłączoną opcją kupowania');
                    $tablica[] = array('id' => 'wylaczone_kupowanie_tak', 'text' => 'produkty z włączoną opcją kupowania');
                    $tablica[] = array('id' => 'wylaczone_szybkie_kupowanie', 'text' => 'produkty z wyłączoną opcją zakupu przez 1 kliknięcie');
                    $tablica[] = array('id' => 'wylaczone_szybkie_kupowanie_tak', 'text' => 'produkty z włączoną opcją zakupu przez 1 kliknięcie');                 
                    $tablica[] = array('id' => 'wylaczony_listing', 'text' => 'produkty z wyłączoną opcją wyświetlania w listingach');
                    $tablica[] = array('id' => 'wylaczony_listing_tak', 'text' => 'produkty z włączoną opcją wyświetlania w listingach');
                    $tablica[] = array('id' => 'wylaczone_rabaty', 'text' => 'produkty z wyłączoną opcją rabatów');
                    $tablica[] = array('id' => 'dodane_wysylki', 'text' => 'produkty z dodanymi wysyłkami');
                    $tablica[] = array('id' => 'wykluczony_punkt_odbioru', 'text' => 'produkty z wykluczonym odbiorem w punkcie');
                    $tablica[] = array('id' => 'wlaczone_komentarze', 'text' => 'produkty z włączoną opcją komentarzy');
                    $tablica[] = array('id' => 'ceny_dla_zalogowanych', 'text' => 'ceny produktów widoczne tylko dla zalogowanych klientów');
                    $tablica[] = array('id' => 'wykluczenie_porownywarek', 'text' => 'produkty wykluczone z porównywarek');
                    $tablica[] = array('id' => 'ceneo_kup_teraz', 'text' => 'produkty kup teraz do CENEO');
                    ?>                                          
                    <?php echo Funkcje::RozwijaneMenu('dodatkowe_opcje', $tablica, ((isset($_GET['dodatkowe_opcje'])) ? $filtr->process($_GET['dodatkowe_opcje']) : ''), ' style="max-width:200px"'); ?>                    
                </div>
                
                <div class="wyszukaj_select">
                    <span>Brakujące dane:</span>
                    <?php
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- brak --');
                    $tablica[] = array('id' => 'bez_ean', 'text' => 'produkty bez kodu EAN');
                    $tablica[] = array('id' => 'bez_nr_kat', 'text' => 'produkty bez numeru katalogowego');
                    $tablica[] = array('id' => 'bez_kod_producenta', 'text' => 'produkty bez kodu producenta');
                    $tablica[] = array('id' => 'bez_producenta', 'text' => 'produkty bez przypisanego producenta');                    
                    $tablica[] = array('id' => 'bez_wysylek', 'text' => 'produkty bez dodanych wysyłek');
                    $tablica[] = array('id' => 'bez_dostepnosci', 'text' => 'produkty bez przypisanej dostępności');
                    $tablica[] = array('id' => 'bez_czas_wysylki', 'text' => 'produkty bez przypisanego czasu wysyłki');
                    $tablica[] = array('id' => 'bez_gwarancji', 'text' => 'produkty bez przypisanej gwarancji');
                    $tablica[] = array('id' => 'bez_wagi', 'text' => 'produkty z wagą równą 0');
                    $tablica[] = array('id' => 'bez_lokalizacji', 'text' => 'produkty bez lokalizacji magazynu');
                    $tablica[] = array('id' => 'bez_opisu', 'text' => 'produkty bez opisu');
                    $tablica[] = array('id' => 'bez_opisu_krotkiego', 'text' => 'produkty bez opisu krótkiego');
                    $tablica[] = array('id' => 'bez_ceny_zakupu', 'text' => 'produkty bez ceny zakupu');
                    ?>                                          
                    <?php echo Funkcje::RozwijaneMenu('brakujace_opcje', $tablica, ((isset($_GET['brakujace_opcje'])) ? $filtr->process($_GET['brakujace_opcje']) : ''), ' style="max-width:200px"'); ?>                    
                </div>                

                <script>
                function FiltrWartoscCecha(id) {
                  var id_wart = '<?php echo ((isset($_GET['cecha_wartosc'])) ? $filtr->process($_GET['cecha_wartosc']) : ''); ?>';
                  $("#FiltrCechy").html('<img src="obrazki/_loader_small.gif" alt="" />');
                  $.get('ajax/produkt_filtr_cechy.php', { tok: '<?php echo Sesje::Token(); ?>', id: id, idwartosc: id_wart }, function(data) {
                      $("#FiltrCechy").html(data);
                  });          
                }
                </script>                
                
                <div class="wyszukaj_select">
                    <span>Z cechą:</span>
                    <?php
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- dowolna nazwa cechy --');                    
                    //
                    $sql = $db->open_query("select distinct products_options_id, products_options_name from products_options where language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' order by products_options_name asc");                        
                    while ($info = $sql->fetch_assoc()) {
                        $tablica[] = array('id' => $info['products_options_id'], 'text' => $info['products_options_name']);                    
                    }                    
                    $db->close_query($sql);
                    unset($info);
                    //
                    echo Funkcje::RozwijaneMenu('cecha_nazwa', $tablica, ((isset($_GET['cecha_nazwa'])) ? (int)$_GET['cecha_nazwa'] : ''), ' style="max-width:230px" onchange="FiltrWartoscCecha(this.value)"') . ' &nbsp; ';
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- dowolna wartość cechy --');                    
                    //                    
                    echo '<div id="FiltrCechy" style="display:inline-block">' . Funkcje::RozwijaneMenu('cecha_wartosc', $tablica, '', ' style="max-width:230px"') . '</div>';
                    unset($tablica);
                    //
                    if ( isset($_GET['cecha_nazwa']) ) {
                         echo '<script>FiltrWartoscCecha(' . (int)$_GET['cecha_nazwa'] . ');</script>';
                    }
                    ?>
                </div>     
                
                <script>
                function FiltrWartoscPole(id) {
                  var txt_wart = '<?php echo ((isset($_GET['dod_pole_wartosc'])) ? str_replace("'","\'", $filtr->process($_GET['dod_pole_wartosc'])) : ''); ?>';
                  $("#FiltrPola").html('<img src="obrazki/_loader_small.gif" alt="" />');
                  $.get('ajax/produkt_filtr_pole_opisowe.php', { tok: '<?php echo Sesje::Token(); ?>', id: id, txtwartosc: txt_wart }, function(data) {
                      $("#FiltrPola").html(data);
                  });          
                }
                </script>                   

                <div class="wyszukaj_select">
                    <span>Z dodatkowym polem:</span>
                    <?php
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- dowolna nazwa pola --');                    
                    //
                    $sql = $db->open_query("select products_extra_fields_name, products_extra_fields_id from products_extra_fields order by products_extra_fields_name");                        
                    while ($info = $sql->fetch_assoc()) {
                        $tablica[] = array('id' => $info['products_extra_fields_id'], 'text' => $info['products_extra_fields_name']);                    
                    }                    
                    $db->close_query($sql);
                    unset($info);
                    //
                    echo Funkcje::RozwijaneMenu('dod_pole_nazwa', $tablica, ((isset($_GET['dod_pole_nazwa'])) ? (int)$_GET['dod_pole_nazwa'] : ''), ' style="max-width:230px" onchange="FiltrWartoscPole(this.value)"') . ' &nbsp; ';
                    //                    
                    echo '<div id="FiltrPola" style="display:inline-block">' . Funkcje::RozwijaneMenu('dod_pole_wartosc', $tablica, '', ' style="max-width:230px"') . '</div>';
                    unset($tablica);
                    //
                    if ( isset($_GET['dod_pole_nazwa']) ) {
                         echo '<script>FiltrWartoscPole(' . (int)$_GET['dod_pole_nazwa'] . ');</script>';
                    }
                    ?>
                </div>                
                
                <div class="wyszukaj_select">
                    <span>Zdjęcia:</span>
                    <?php
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- dowolne --');
                    $tablica[] = array('id' => 'bez_zdjecia', 'text' => 'produkty bez zdjęcia głównego');
                    $tablica[] = array('id' => 'bez_plikow_zdjecia', 'text' => 'produkty z przypisanym zdjęciem do którego nie ma pliku zdjęcia na serwerze');
                    ?>                                          
                    <?php echo Funkcje::RozwijaneMenu('zdjecia', $tablica, ((isset($_GET['zdjecia'])) ? $filtr->process($_GET['zdjecia']) : ''), ' style="max-width:230px"'); ?>                    
                </div>                

                <div class="wyszukaj_select">
                    <span>Kod licencyjny:</span>
                    <input type="text" name="kod_licencyjny" value="<?php echo ((isset($_GET['kod_licencyjny'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['kod_licencyjny'])) : ''); ?>" size="20" />
                </div>            

                <?php if ( PRODUKTY_LISTING_ID_ZEWNETRZNE == 'tak' ) { ?>
                
                <div class="wyszukaj_select">
                    <span>Id zewnętrzne:</span>
                    <?php
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- brak --');                    
                    //
                    $sql = $db->open_query("select distinct products_id_private from products where products_id_private != '' and products_id_private != '0' order by products_id_private");                        
                    while ($info = $sql->fetch_assoc()) {
                        $tablica[] = array('id' => $info['products_id_private'], 'text' => $info['products_id_private']);                    
                    }                    
                    $db->close_query($sql);
                    unset($info);
                    //
                    echo Funkcje::RozwijaneMenu('id_zewnetrzne', $tablica, ((isset($_GET['id_zewnetrzne'])) ? $filtr->process($_GET['id_zewnetrzne']) : ''), ' style="max-width:230px"');
                    //
                    ?>
                </div>                  
                
                <?php } ?>
                
                <div class="wyszukaj_select">
                    <span>Dostępne wysyłki:</span>
                    <?php
                    //
                    $tablica_wysylka = array();
                    $tablica_wysylka[] = array('id' => '', 'text' => '-- brak --');                    
                    //
                    $sql = $db->open_query("SELECT id, nazwa FROM modules_shipping ORDER BY nazwa");
                    while ($info = $sql->fetch_assoc()) {
                        $tablica_wysylka[] = array('id' => $info['id'], 'text' => $info['nazwa']);
                    }
                    $db->close_query($sql);
                    unset($info);                    
                    //
                    echo Funkcje::RozwijaneMenu('przypisana_wysylka', $tablica_wysylka, ((isset($_GET['przypisana_wysylka'])) ? $filtr->process($_GET['przypisana_wysylka']) : ''), ' style="max-width:180px"');
                    unset($tablica_id);
                    //
                    ?>
                </div>                    
                
                <?php 
                // tworzy ukryte pola hidden do wyszukiwania - filtra 
                if (isset($_GET['kategoria_id'])) { 
                    echo '<div><input type="hidden" name="kategoria_id" value="'.(int)$_GET['kategoria_id'].'" /></div>';
                }   
                if (isset($_GET['sort'])) { 
                    echo '<div><input type="hidden" name="sort" value="'.$filtr->process($_GET['sort']).'" /></div>';
                }                

                // dodatkowy select do wyswietlenia produktow z bledami
                $ZapytanieBledy = "SELECT pd.products_name, p.products_price_tax, p.products_tax, pc.categories_id
                                     FROM products p
                                     LEFT JOIN products_to_categories pc ON pc.products_id = p.products_id
                                     STRAIGHT_JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'
                                    WHERE pd.products_name is null or p.products_price_tax = 0 or p.products_tax = 0 or ( pc.categories_id is null or pc.categories_id = 0 )";   

                $sqlBledy = $db->open_query($ZapytanieBledy);
                if ((int)$db->ile_rekordow($sqlBledy)) {                                   
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- wszystkie --');
                    $tablica[] = array('id' => 'brutto', 'text' => 'produkty bez uzupełnionej ceny brutto');
                    $tablica[] = array('id' => 'vat', 'text' => 'produkty bez uzupełnionej kwoty VAT');
                    $tablica[] = array('id' => 'kategoria', 'text' => 'produkty bez przypisanej kategorii');
                    $tablica[] = array('id' => 'nazwa', 'text' => 'produkty bez wpisanej nazwy');                
                    $tablica[] = array('id' => 'brakopisu', 'text' => 'produkty bez opisu głównego');                
                    $tablica[] = array('id' => 'kategoriawyl', 'text' => 'produkty w wyłączonych kategoriach');                
                    //             
                    ?>
                    <div class="wyszukaj_select">
                        <span style="color:#ff0000">Błędy w produktach:</span>
                        <?php                         
                        echo Funkcje::RozwijaneMenu('blad', $tablica, ((isset($_GET['blad'])) ? ((isset($_GET['status'])) ? $filtr->process($_GET['status']) : '') : ''), ' style="color:#ff0000"'); 
                        unset($tablica);
                        ?>
                    </div>                 
                    <?php
                    unset($tablica);
                } else {
                    unset($_SESSION['filtry']['produkty.php']['blad']);
                }
                $db->close_query($sqlBledy);
                unset($ZapytanieBledy);
                ?>
                
                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="produkty/produkty.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?> 

                <div style="clear:both"></div>
                
            </div>        
            
            <form action="produkty/produkty_akcja.php" method="post" class="cmxform">
            
            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="produkty/produkty.php?sort=sort_a1">brak</a>
                <a id="sort_a17" class="sortowanie" href="produkty/produkty.php?sort=sort_a17">nazwy rosnąco</a>
                <a id="sort_a2" class="sortowanie" href="produkty/produkty.php?sort=sort_a2">nazwy malejąco</a>
                <a id="sort_a7" class="sortowanie" href="produkty/produkty.php?sort=sort_a7">nr katalogowy rosnąco</a>
                <a id="sort_a8" class="sortowanie" href="produkty/produkty.php?sort=sort_a8">nr katalogowy malejąco</a> 
                <a id="sort_a9" class="sortowanie" href="produkty/produkty.php?sort=sort_a9">cena rosnąco</a>
                <a id="sort_a10" class="sortowanie" href="produkty/produkty.php?sort=sort_a10">cena malejąco</a>             
                <a id="sort_a3" class="sortowanie" href="produkty/produkty.php?sort=sort_a3">aktywne</a>
                <a id="sort_a4" class="sortowanie" href="produkty/produkty.php?sort=sort_a4">nieaktywne</a>
                <a id="sort_a5" class="sortowanie" href="produkty/produkty.php?sort=sort_a5">daty dodania rosnąco</a>
                <a id="sort_a6" class="sortowanie" href="produkty/produkty.php?sort=sort_a6">daty dodania malejąco</a> 
                <a id="sort_a11" class="sortowanie" href="produkty/produkty.php?sort=sort_a11">ilość rosnąco</a>
                <a id="sort_a12" class="sortowanie" href="produkty/produkty.php?sort=sort_a12">ilość malejąco</a> 
                <a id="sort_a13" class="sortowanie" href="produkty/produkty.php?sort=sort_a13">ID malejąco</a>
                <a id="sort_a14" class="sortowanie" href="produkty/produkty.php?sort=sort_a14">ID rosnąco</a>
                <a id="sort_a15" class="sortowanie" href="produkty/produkty.php?sort=sort_a15">sortowanie malejąco</a>
                <a id="sort_a16" class="sortowanie" href="produkty/produkty.php?sort=sort_a16">sortowanie rosnąco</a>
                
            </div>        
            
            <div style="clear:both;"></div>               
            
            <?php 
            if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
                $sciezka = Kategorie::SciezkaKategoriiId((int)$_GET['kategoria_id'], 'categories');
                $cSciezka = explode("_", (string)$sciezka);
               } else {
                $cSciezka = array();
            }
            ?>

            <?php
            // przycisk dodania nowego produktu
            ?>
            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="produkty/produkty_dodaj.php">dodaj nowy produkt</a>                    
                </div>         
                <?php if (isset($_GET['kategoria_id'])) { ?>
                <div>
                    <input type="checkbox" id="pamietaj" value="<?php echo (int)$_GET['kategoria_id']; ?>" <?php echo ((isset($_COOKIE['kategoria'])) ? 'checked="checked"' : ''); ?>/><label class="OpisFor" for="pamietaj" style="margin-top:-3px;">zaznaczaj automatycznie wybraną kategorię przy dodawaniu nowego produktu</label>
                </div>
                <?php } ?>
            </div>
            
            <div style="clear:both;"></div>
            
            <div class="GlownyListing">

                <div class="GlownyListingKategorie">
                
                    <div class="OknoKategoriiKontener">
                    
                        <div class="OknoNaglowek"><span class="RozwinKategorie">Kategorie</span></div>
                        <?php
                        echo '<div class="OknoKategorii"><table class="pkc">';
                        $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                        for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                            $podkategorie = false;
                            if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                            // sprawdza czy nie jest wybrana
                            $style = '';
                            if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
                                if ((int)$_GET['kategoria_id'] == $tablica_kat[$w]['id']) {
                                    $style = ' style="color:#ff0000"';
                                }
                            }
                            //
                            echo '<tr>
                                    <td class="lfp"><a href="produkty/produkty.php?kategoria_id='.$tablica_kat[$w]['id'].'" '.$style.'>'.$tablica_kat[$w]['text'].'</a></td>
                                    <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'\',\'\',\'produkty\')" />' : '').'</td>
                                  </tr>
                                  '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                        }
                        if ( count($tablica_kat) == 0 ) {
                             echo '<tr><td colspan="9" style="padding:10px">Brak wyników do wyświetlania</td></tr>';
                        }
                        echo '</table></div>';
                        unset($tablica_kat,$podkategorie,$style);
                        ?>        

                        <?php 
                        if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
                            $sciezka = Kategorie::SciezkaKategoriiId((int)$_GET['kategoria_id'], 'categories');
                            $cSciezka = explode("_", (string)$sciezka);                    
                            if (count($cSciezka) > 1) {
                                //
                                $ostatnie = strRpos($sciezka,'_');
                                $analiza_sciezki = str_replace("_", ",", substr((string)$sciezka, 0, (int)$ostatnie));
                                ?>
                                
                                <script>         
                                podkat('<?php echo $analiza_sciezki; ?>', '<?php echo $cSciezka[count($cSciezka)-1]; ?>','','','produkty');
                                </script>
                                
                            <?php
                            unset($sciezka,$cSciezka);
                            }
                        } ?>
                        
                    </div>
                    
                </div>
                
                <script>
                $(document).ready(function() {
                  
                    $('#akcja_dolna').change(function() {

                       $('#potwierdzenie_usuniecia').hide();

                       if ( this.value == '3' || this.value == '4' ) {
                            $('#usuniecie_zamowien_nie').prop('checked', true);
                            $('#usuniecie_zamowien_tak').prop('checked', false);
                            $('#potwierdzenie_usuniecia').show();
                       }
                       
                    });

                });
                </script>

                <div style="GlownyListingProdukty">
                
                    <div id="wynik_zapytania" class="WynikZapytania"></div>
                    <div id="aktualna_pozycja">1</div>
                    
                    <div id="akcja" class="AkcjaOdstep">
                    
                        <div class="lf"><img src="obrazki/strzalka.png" alt="" /></div>
                        
                        <div class="lf" style="padding-right:20px">
                            <span onclick="akcja(1)">zaznacz wszystkie</span>
                            <span onclick="akcja(2)">odznacz wszystkie</span>
                        </div>
                        
                        <div id="akc">
                        
                            Wykonaj akcje: 
                            
                            <select name="akcja_dolna" id="akcja_dolna">
                                <option value="0"></option>
                                <option value="3">usuń zaznaczone produkty</option>
                                <option value="4">usuń zaznaczone produkty (wraz ze zdjęciami z SERWERA)</option>
                            </select>
                            
                        </div>
                        
                        <div style="clear:both;"></div>
                        
                    </div>                        
                    
                    <div id="dolny_pasek_stron" class="AkcjaOdstep"></div>
                    <div id="pokaz_ile_pozycji" class="AkcjaOdstep"></div>
                    <div id="ile_rekordow" class="AkcjaOdstep"><?php echo $ile_pozycji; ?></div>
                    
                    <?php if ($ile_pozycji > 0) { ?>
                    
                    <div id="potwierdzenie_usuniecia" style="padding-bottom:10px;display:none">
                    
                        <div class="RamkaAkcji" style="display:block;padding:15px">
                    
                            <div class="rg">
                              
                                <p style="padding-right:0px">
                                    <label style="width:auto">Czy na pewno chcesz usunąć wybrane produkty ?</label>
                                    <input type="radio" value="0" name="usuniecie_produktow" id="usuniecie_produktow_nie" checked="checked" /><label class="OpisFor" for="usuniecie_produktow_nie">nie</label>
                                    <input type="radio" value="1" name="usuniecie_produktow" id="usuniecie_produktow_tak" /><label class="OpisFor" style="padding-right:0px !important" for="usuniecie_produktow_tak">tak</label>
                                </p>
                                
                            </div>
                            
                            <div class="cl"></div>
                            
                            <div class="ostrzezenie rg">Operacja usunięcia jest nieodracalna ! Produktów po usunięciu nie będzie można przywrócić !</div>
                            
                            <div class="cl"></div>
                       
                        </div>
                        
                    </div>
                
                    <div id="zapis"><input type="submit" class="przyciskBut" value="Zapisz zmiany" /></div>
                    <?php } ?>                         
                    
                </div>

            </div>
            
            </form>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('produkty/produkty.php', $zapytanie, $ile_licznika, $ile_pozycji, 'products_id', ILOSC_WYNIKOW_NA_STRONIE, ADMIN_DOMYSLNE_SORTOWANIE); ?>
            </script>                     
                
        </div>     

        <?php include('stopka.inc.php'); ?>

    <?php 
    } 
    
}?>
