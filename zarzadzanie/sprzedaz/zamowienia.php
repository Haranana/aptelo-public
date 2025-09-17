<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if ( isset($_GET['zakladka']) ) unset($_GET['zakladka']);
if ( isset($_SESSION['waluta_zamowienia']) ) unset($_SESSION['waluta_zamowienia']);
if ( isset($_SESSION['waluta_zamowienia_symbol']) ) unset($_SESSION['waluta_zamowienia_symbol']);

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

// wyswietla tylko nowe zamowienia
if ( isset($_GET['nowe']) ) {
     //
     unset($_SESSION['filtry']['zamowienia.php']);
     //
     $_SESSION['filtry']['zamowienia.php']['rodzaj_zam'] = '1';
     //
     Funkcje::PrzekierowanieURL('zamowienia.php');
}

// wyswietla tylko z data wysylki
if ( isset($_GET['data_wysylki']) ) {
     //
     unset($_SESSION['filtry']['zamowienia.php']);
     //
     $_SESSION['filtry']['zamowienia.php']['szukaj_data_wysylki_do'] = $filtr->process($_GET['data_wysylki']);
     $_SESSION['filtry']['zamowienia.php']['rodzaj_zam_multi'] = '3,4';
     //
     Funkcje::PrzekierowanieURL('zamowienia.php');
}

if ($prot->wyswietlStrone) {
  
    // sprawdzanie zamowien czarnej listy
    $zapytanie_czarna_lista = "SELECT orders_id FROM orders WHERE orders_black_list = 0";
    $sql_czarna_lista = $db->open_query($zapytanie_czarna_lista);    
    
    if ( (int)$db->ile_rekordow($sql_czarna_lista) > 0) {
      
        while ( $info_czarna_lista = $sql_czarna_lista->fetch_assoc() ) {
          
            $pola = array();
            
            if ( Klienci::SprawdzZamowienieCzarnaLista($info_czarna_lista['orders_id']) != false ) {
              
                $pola[] = array('orders_black_list',2);

            } else {
              
                $pola[] =  array('orders_black_list',1);
              
            }
            
            $db->update_query('orders', $pola, "orders_id = '" . $info_czarna_lista['orders_id'] . "'");              
            
            unset($pola);
          
        }
        
        unset($info_czarna_lista);
      
    }
    
    $db->close_query($sql_czarna_lista);
    unset($zapytanie_czarna_lista);    

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    // sprawdza wyszukiwanie po cechach
    if ( isset($_GET['szukaj_cecha']) ) {
         //
         if ( (isset($_GET['szukaj_cecha_nazwa']) && (int)$_GET['szukaj_cecha_nazwa'] == 0) || !isset($_GET['szukaj_cecha_nazwa']) ) {
               //
               unset($_SESSION['filtry']['zamowienia.php']['szukaj_cecha']);
               unset($_SESSION['filtry']['zamowienia.php']['szukaj_cecha_nazwa']);
               unset($_SESSION['filtry']['zamowienia.php']['szukaj_cecha_wartosc']);
               //
               Funkcje::PrzekierowanieURL('zamowienia.php');
               //
         }
         //         
    }
        
    // id zamowien
    $ZamowieniaId = array();
    $ZamowieniaId[] = 0;
    
    // jezeli jest wyszukiwanie zamowien ze zmiana statusu
    if ( ( isset($_GET['szukaj_data_statusu_od']) && $_GET['szukaj_data_statusu_od'] != '' ) || ( isset($_GET['szukaj_data_statusu_do']) && $_GET['szukaj_data_statusu_do'] != '' ) ) {
    
         $warunki_szukania = '';

         if ( isset($_GET['szukaj_data_statusu_od']) && $_GET['szukaj_data_statusu_od'] != '' ) {
            $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_statusu_od'])));
            $warunki_szukania .= " and date_added >= '".$szukana_wartosc."'";
         }

         if ( isset($_GET['szukaj_data_statusu_do']) && $_GET['szukaj_data_statusu_do'] != '' ) {
            $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_statusu_do'])));
            $warunki_szukania .= " and date_added <= '".$szukana_wartosc."'";
         }      

         if ( isset($_GET['szukaj_status']) && $_GET['szukaj_status'] != '0' ) {
            $warunki_szukania .= " and orders_status_id = '".(int)$_GET['szukaj_status']."'";
         }

         $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
         
         $zapytanie = "SELECT orders_id FROM orders_status_history " . $warunki_szukania;
         $sql = $db->open_query($zapytanie);

         while ($info = $sql->fetch_assoc()) {
            $ZamowieniaId[] = $info['orders_id'];
         }
         
         $db->close_query($sql);
         unset($szukana_wartosc, $warunki_szukania, $zapytanie, $sql);
         
    }

    // kupon rabatowy
    if ( KUPON_LISTING == 'tak' ) {
         //
         if (isset($_GET['kod_rabatowy']) && $_GET['kod_rabatowy'] != '') {
             //
             $szukana_wartosc = $filtr->process($_GET['kod_rabatowy']);
             //
             $zapytanie = "SELECT orders_id, title FROM orders_total WHERE class = 'ot_discount_coupon'";             
             $sql = $db->open_query($zapytanie);
            
             while ($info = $sql->fetch_assoc()) {
               //
               $sam_kod = explode(':', (string)$info['title']); 
               unset($sam_kod[0]);
               //
               if ( count($sam_kod) > 0 ) {
                    //
                    if ( @preg_match('/' . $szukana_wartosc . '/ui', implode(':', (array)$sam_kod) ) ) {                  
                         $ZamowieniaId[] = $info['orders_id'];
                    }
                    //
               }
               //
               unset($sam_kod);
               //
             }
            
             $db->close_query($sql);
             unset($szukana_wartosc, $zapytanie, $sql);
             //
         }
         //
    }
    
    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && $_GET['szukaj'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " and CONCAT_WS(' ', o.customers_telephone, o.customers_email_address, o.customers_name, o.customers_street_address, o.customers_city, o.customers_postcode, o.customers_company, o.customers_nip, o.delivery_name, o.delivery_company, o.billing_name, o.billing_company ) LIKE '%".$szukana_wartosc."%'";
        unset($szukana_wartosc);
    }
    
    if (isset($_GET['szukaj_produkt']) && $_GET['szukaj_produkt'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_produkt']);
        $warunki_szukania .= " and (op.products_name LIKE '%".$szukana_wartosc."%' or op.products_model LIKE '%".$szukana_wartosc."%')";
        unset($szukana_wartosc);
    }    
    
    if (isset($_GET['szukaj_uwagi']) && $_GET['szukaj_uwagi'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_uwagi']);
        $warunki_szukania .= " and (o.orders_adminnotes LIKE '%".$szukana_wartosc."%')";
        unset($szukana_wartosc);
    }        

    if (isset($_GET['nick_allegro']) && $_GET['nick_allegro'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['nick_allegro']);
        $warunki_szukania .= " and o.allegro_nick = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }    

    if (isset($_GET['nr_aukcji']) && $_GET['nr_aukcji'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['nr_aukcji']);
        $warunki_szukania .= " and o.allegro_id LIKE '%".$szukana_wartosc."%'";
        unset($szukana_wartosc);
    }      

    if ( isset($_GET['szukaj_data_zamowienia_od']) && $_GET['szukaj_data_zamowienia_od'] != '' ) {
        //
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_zamowienia_od'])));
        $warunki_szukania .= " and o.date_purchased >= '".$szukana_wartosc."'";
        unset($szukana_wartosc, $godzina, $minuty);
    }
    
    if ( isset($_GET['szukaj_data_zamowienia_do']) && $_GET['szukaj_data_zamowienia_do'] != '' ) {
        //
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_zamowienia_do'])));
        $warunki_szukania .= " and o.date_purchased <= '".$szukana_wartosc."'";
        unset($szukana_wartosc, $godzina, $minuty);
    }

    if ( isset($_GET['szukaj_status']) && $_GET['szukaj_status'] != '0' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_status']);
        $warunki_szukania .= " and o.orders_status = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }
    
    if ( ZAPLACONE_LISTING == 'tak' ) {
        if ( isset($_GET['zaplacone']) && $_GET['zaplacone'] != '0' ) {
            $szukana_wartosc = (int)$_GET['zaplacone'];
            $warunki_szukania .= " and o.paid_info = '".(($szukana_wartosc == 2) ? 0 : $szukana_wartosc)."'";
            unset($szukana_wartosc);
        }    
    }
    
    if ( isset($_GET['rodzaj_zam']) && (int)$_GET['rodzaj_zam'] != '0' ) {
        $szukana_wartosc = (int)$_GET['rodzaj_zam'];
        $warunki_szukania .= " and os.orders_status_type = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }    
    
    if ( isset($_GET['rodzaj_zam_multi']) ) {
        $warunki_szukania .= " and os.orders_status_type NOT IN (3,4)";
    }    

    if ( isset($_GET['szukaj_wysylka']) && $_GET['szukaj_wysylka'] != '0' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_wysylka']);
        $warunki_szukania .= " and o.shipping_module = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }

    if ( isset($_GET['szukaj_platnosc']) && $_GET['szukaj_platnosc'] != '0' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_platnosc']);
        $warunki_szukania .= " and o.payment_method = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }

    if ( isset($_GET['klient_id']) && (int)$_GET['klient_id'] != '' ) {
        $szukana_wartosc = (int)$_GET['klient_id'];
        $warunki_szukania .= " and o.customers_id = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }
    
    if ( isset($_GET['szukaj_wartosc_zamowienia_od']) && (float)$_GET['szukaj_wartosc_zamowienia_od'] > 0 ) {
        $szukana_wartosc = (float)$_GET['szukaj_wartosc_zamowienia_od'];
        $warunki_szukania .= " and (ot.class = 'ot_total' and ot.value >= '".$szukana_wartosc."')";
        unset($szukana_wartosc);
    }  

    if ( isset($_GET['szukaj_wartosc_zamowienia_do']) && (float)$_GET['szukaj_wartosc_zamowienia_do'] > 0 ) {
        $szukana_wartosc = (float)$_GET['szukaj_wartosc_zamowienia_do'];
        $warunki_szukania .= " and (ot.class = 'ot_total' and ot.value <= '".$szukana_wartosc."')";
        unset($szukana_wartosc);
    }

    if ( isset($_GET['opiekun']) && (int)$_GET['opiekun'] > 0 ) {
        $szukana_wartosc = (int)$_GET['opiekun'];
        $warunki_szukania .= " and o.service = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }   

    if ( isset($_GET['typ_zam']) && (int)$_GET['typ_zam'] > 0 ) {
        $szukana_wartosc = (int)$_GET['typ_zam'];
        if ( $szukana_wartosc < 5 ) {
             $warunki_szukania .= " and o.orders_source = '".$szukana_wartosc."'";
          } else if ( $szukana_wartosc == 5 ) {
             $warunki_szukania .= " and o.orders_source != '3'";
        }
        unset($szukana_wartosc);
    }
    
    if ( isset($_GET['szukaj_numer']) && (int)$_GET['szukaj_numer'] > 0 ) {
        $szukana_wartosc = (int)$_GET['szukaj_numer'];
        $warunki_szukania .= " and o.orders_id = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }
    
    if ( count($ZamowieniaId) > 1 ) {
        $warunki_szukania .= " and o.orders_id in (" . implode(',', (array)$ZamowieniaId) . ")";
    } else {
        if ( ( isset($_GET['szukaj_data_statusu_od']) && $_GET['szukaj_data_statusu_od'] != '' ) || ( isset($_GET['szukaj_data_statusu_do']) && $_GET['szukaj_data_statusu_do'] != '' ) || ( isset($_GET['kod_rabatowy']) && $_GET['kod_rabatowy'] != '' ) ) {
            $warunki_szukania .= " and o.orders_id in (0)";
        }
    }
    
    // jezeli jest kod licencyjny
    if (isset($_GET['kod_licencyjny']) && !empty($_GET['kod_licencyjny'])) {
        $szukana_wartosc = $filtr->process($_GET['kod_licencyjny']);
        $warunki_szukania = " and (op.products_code_shopping like '%".$szukana_wartosc."%')";
        unset($szukana_wartosc);
    }      
    
    if (isset($_GET['przesylka']) && (int)$_GET['przesylka'] == 0) {
        unset($_GET['przesylka']);
    }
    
    if (isset($_GET['przesylka'])) {
        if (isset($_GET['nr_przesylki'])) {
            unset($_GET['nr_przesylki']);
        }
        if ((int)$_GET['przesylka'] == 1) {
            $warunki_szukania .= " and o.orders_id IN ( SELECT srt.orders_id FROM orders_shipping srt )";
        }            
        if ((int)$_GET['przesylka'] == 2) {
            $warunki_szukania .= " and o.orders_id NOT IN ( SELECT srt.orders_id FROM orders_shipping srt )";
        }        
    }

    if (isset($_GET['nr_przesylki']) && $_GET['nr_przesylki'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['nr_przesylki']);
        $warunki_szukania .= " and (osh.orders_shipping_number LIKE '%".$szukana_wartosc."%')";
        unset($szukana_wartosc);
    }        
    
    if ( isset($_GET['szukaj_data_wysylki_od']) && $_GET['szukaj_data_wysylki_od'] != '' ) {
        //      
        $szukana_wartosc = date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_wysylki_od'])));
        $warunki_szukania .= " and o.shipping_date >= '".$szukana_wartosc."' and o.shipping_date != '0000-00-00'";
        unset($szukana_wartosc);
        //
    }    
    
    if ( isset($_GET['szukaj_data_wysylki_do']) && $_GET['szukaj_data_wysylki_do'] != '' ) {
        //      
        $szukana_wartosc = date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_wysylki_do'])));
        $warunki_szukania .= " and o.shipping_date <= '".$szukana_wartosc."' and o.shipping_date != '0000-00-00'";
        unset($szukana_wartosc);
        //
    }        
    
    if ( isset($_GET['kraj_dostawy']) && (int)$_GET['kraj_dostawy'] > 0 ) {
        $szukana_wartosc = (int)$_GET['kraj_dostawy'];
        $warunki_szukania .= " and o.delivery_country IN (SELECT countries_name FROM countries_description WHERE countries_id = " . $szukana_wartosc . ")";
        unset($szukana_wartosc);
    }      
    
    if ( KOSZYK_WYBOR_DOKUMENTU_SPRZEDAZY == 'tak' ) {

        if ( isset($_GET['dokument_sprzedazy']) && $_GET['dokument_sprzedazy'] != '' ) {
            if ($_GET['dokument_sprzedazy'] == 'f') {
                $warunki_szukania .= " and o.invoice_dokument = '1'";
            }            
            if ($_GET['dokument_sprzedazy'] == 'p') {
                $warunki_szukania .= " and o.invoice_dokument = '0'";
            }       
        } 

        if ( isset($_GET['dokument_sprzedazy_wystawiony']) && $_GET['dokument_sprzedazy_wystawiony'] != '' ) {
            //
            $ZamowieniaIdDokument = array(0);
            $ZamowieniaIdDokumentWykluczone = array(0);
            //
            if ($_GET['dokument_sprzedazy_wystawiony'] == 'f') {
                $zapytanie = "SELECT orders_id as nr_zamowienia_faktura FROM invoices"; 
            }            
            if ($_GET['dokument_sprzedazy_wystawiony'] == 'p') {
                $zapytanie = "SELECT orders_id as nr_zamowienia_paragon FROM receipts"; 
            }       
            if ($_GET['dokument_sprzedazy_wystawiony'] == 'bez') {
                $zapytanie = "SELECT r.orders_id as nr_zamowienia_paragon, f.orders_id as nr_zamowienia_faktura FROM receipts r, invoices f"; 
            }                   
            //
            $sql = $db->open_query($zapytanie);
            
            while ($info = $sql->fetch_assoc()) {
               //
               if ( $_GET['dokument_sprzedazy_wystawiony'] == 'f') {
                    $ZamowieniaIdDokument[] = $info['nr_zamowienia_faktura'];
               }
               if ( $_GET['dokument_sprzedazy_wystawiony'] == 'p') {
                    $ZamowieniaIdDokument[] = $info['nr_zamowienia_paragon'];
               }               
               if ( $_GET['dokument_sprzedazy_wystawiony'] == 'bez') {
                    $ZamowieniaIdDokumentWykluczone[] = $info['nr_zamowienia_paragon'];
                    $ZamowieniaIdDokumentWykluczone[] = $info['nr_zamowienia_faktura'];
               }                              
               //
            }
            
            $db->close_query($sql);
            unset($zapytanie, $sql);
            //         
            if ( count($ZamowieniaIdDokument) > 1 ) {
                $warunki_szukania .= " and o.orders_id in (" . implode(',', (array)$ZamowieniaIdDokument) . ")";
            }
            if ( count($ZamowieniaIdDokumentWykluczone) > 1 ) {
                $warunki_szukania .= " and o.orders_id not in (" . implode(',', (array)$ZamowieniaIdDokumentWykluczone) . ")";
            }
            
        } 
        
    }        
    
    if (isset($_GET['szukaj_waluta']) && $_GET['szukaj_waluta'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_waluta']);
        $warunki_szukania .= " and currency = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }     
    
    $warunki_cech = '';
    if ( isset($_GET['szukaj_cecha_nazwa']) && (int)$_GET['szukaj_cecha_nazwa'] > 0 && isset($_GET['szukaj_cecha']) && (int)$_GET['szukaj_cecha'] == 1 ) {
         $warunki_cech .= ' and opa.products_options_id = "' . (int)$_GET['szukaj_cecha_nazwa'] . '"';
    }
    if ( isset($_GET['szukaj_cecha_wartosc']) && (int)$_GET['szukaj_cecha_wartosc'] > 0 && isset($_GET['szukaj_cecha']) && (int)$_GET['szukaj_cecha'] == 1 ) {
         $warunki_cech .= ' and opa.products_options_values_id = "' . (int)$_GET['szukaj_cecha_wartosc'] . '"';
    }    
    
    if ( $warunki_szukania != '' ) {
      //$warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }
    
    // statusy reklamacji - tylko zamkniete
    $zapytanie_reklamacja = "SELECT complaints_status_id FROM complaints_status WHERE complaints_status_type = 1 or complaints_status_type = 2";
    $sql_reklamacja = $db->open_query($zapytanie_reklamacja);

    $statusy_reklamacji = array(99);
    
    while ($id_statusu = $sql_reklamacja->fetch_assoc()) {
         //
         $statusy_reklamacji[] = $id_statusu['complaints_status_id'];
         //
    }
    $db->close_query($sql_reklamacja);
    unset($zapytanie_reklamacja);      
    
    // tworzy tablice z reklamacjami ktore nie sa zamkniete
    $tablica_reklamacji = array();
    
    $zapytanie_reklamacja = "SELECT complaints_id, complaints_customers_orders_id FROM complaints WHERE complaints_status_id IN (" . implode(',', (array)$statusy_reklamacji) . ")";
    $sql_reklamacja = $db->open_query($zapytanie_reklamacja);
    
    while ($reklamacja = $sql_reklamacja->fetch_assoc()) {
         //
         $tablica_reklamacji[ $reklamacja['complaints_customers_orders_id'] ] = $reklamacja['complaints_id'];
         //
    }
    $db->close_query($sql_reklamacja);
    unset($zapytanie_reklamacja);     
    
    if ( isset($_GET['status_reklamacji']) && (int)$_GET['status_reklamacji'] > 0 ) {
        //
        $tmp = array(0);
        foreach ( $tablica_reklamacji as $id => $wartosc ) {
            //
            if ( (int)$id > 0 && !in_array((string)$id, $tmp) ) {
                 $tmp[] = (int)$id;
            }
            //
        }
        //
        $warunki_szukania .= " and o.orders_id IN (" . implode(',', (array)$tmp) . ")";
        //
        unset($tmp);
    }        

    $zapytanie = "SELECT o.orders_id, o.invoice_proforma_date, o.customers_email_address, o.customers_telephone, o.paid_info, o.customers_name, o.customers_id, o.payment_method, o.date_purchased, o.allegro_nick, o.last_modified, o.currency, o.currency_value, o.customers_dummy_account, o.customers_company, o.customers_street_address, o.customers_postcode, o.customers_city, o.customers_country, o.orders_status, o.orders_source, o.service, o.shipping_module, o.orders_adminnotes, o.review_date, o.review_date_customer, o.reviews_products_date, o.orders_black_list, o.status_update_products, o.shipping_date, o.shipping_date_mail_send, ot.value, ot.class, ot.text as order_total, c.customers_dod_info, c.customers_newsletter, c.customers_reviews, c.customers_groups_id, o.invoice_dokument,
                         r.receipts_id, r.receipts_nr as nr_paragonu, r.receipts_date_generated, f.invoices_id, f.invoices_nr as nr_faktury, f.invoices_date_generated, os.orders_status_type
                  FROM orders_total ot
                  RIGHT JOIN orders o ON o.orders_id = ot.orders_id 
                  LEFT JOIN orders_status os ON os.orders_status_id = o.orders_status
                  LEFT JOIN customers c ON c.customers_id = o.customers_id
                  LEFT JOIN receipts r ON r.orders_id = o.orders_id
                  LEFT JOIN invoices f ON f.orders_id = o.orders_id AND f.invoices_type = '2'
                  " . ((isset($_GET['szukaj_produkt']) || isset($_GET['kod_licencyjny'])) ? 'LEFT JOIN orders_products op ON op.orders_id = o.orders_id' : '') . "
                  " . ((isset($_GET['nr_przesylki']) && !isset($_GET['przesylka'])) ? 'LEFT JOIN orders_shipping osh ON osh.orders_id = o.orders_id' : '') . "
                  " . (($warunki_cech != '') ? 'RIGHT JOIN orders_products_attributes opa ON opa.orders_id = o.orders_id' . $warunki_cech : '') . "                         
                  WHERE ot.class = 'ot_total' " . $warunki_szukania . ' GROUP BY o.orders_id'; 

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ZapytanieDlaPozycji = "SELECT o.orders_id
                            FROM orders o 
                            LEFT JOIN orders_total ot ON o.orders_id = ot.orders_id AND ot.class = 'ot_total' LEFT JOIN orders_status os ON os.orders_status_id = o.orders_status
                            " . ((isset($_GET['szukaj_produkt']) || isset($_GET['kod_licencyjny'])) ? 'LEFT JOIN orders_products op ON op.orders_id = o.orders_id' : '') . "
                            " . ((isset($_GET['nr_przesylki']) && !isset($_GET['przesylka'])) ? 'LEFT JOIN orders_shipping osh ON osh.orders_id = o.orders_id' : '') . "
                            " . (($warunki_cech != '') ? 'RIGHT JOIN orders_products_attributes opa ON opa.orders_id = o.orders_id' . $warunki_cech : '') . "    
                            " . $warunki_szukania;
                            
    $sql = $db->open_query($ZapytanieDlaPozycji);
    $ile_pozycji = (int)$db->ile_rekordow($sql);
  
    unset($statusy_reklamacji);

    $sql = $db->open_query($zapytanie);

    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }

    $db->close_query($sql);
    
    // jezeli jest sortowanie
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case "sort_a1":
                $sortowanie = 'o.orders_id desc';
                break;
            case "sort_a2":
                $sortowanie = 'o.orders_id asc';
                break;                 
            case "sort_a3":
                $sortowanie = 'o.date_purchased desc';
                break;
            case "sort_a4":
                $sortowanie = 'o.date_purchased asc';
                break;                 
            case "sort_a5":
                $sortowanie = 'o.shipping_date desc';
                break;                 
            case "sort_a6":
                $sortowanie = 'o.shipping_date asc';
                break;                                 
        }            
    } else { $sortowanie = 'orders_id desc'; }    
    
    $zapytanie .= " ORDER BY ".$sortowanie;    

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {
                        
        // opiekun zamowienia - tablica
        $tablica_opiekunow = array();
        //
        $zapytanie_tmp = "select distinct * from admin";
        $sqls = $db->open_query($zapytanie_tmp);
        //
        if ((int)$db->ile_rekordow($sqls) > 0) {
            //
            while ($infs = $sqls->fetch_assoc()) {
                  $tablica_opiekunow[ $infs['admin_id'] ] = $infs['admin_firstname'] . ' ' . $infs['admin_lastname'];
            }
            //
        }
        unset($zapytanie_tmp, $infs);  
        $db->close_query($sqls);
        //      

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr'];    

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Info', 'center', '', 'class="ListingSchowajMobile"'),
                                      array('Akcja', 'center'),
                                      array('ID', 'center'));
            
            if (FAKTURA_PARAGON_LISTING == 'tak') {
                $tablica_naglowek[] = array('Faktura <br /> Paragon', 'center', '', 'class="ListingSchowajMobile"');
            }
        
            $tablica_naglowek[] = array('Klient');
            $tablica_naglowek[] = array('Data zamówienia', 'center');
            $tablica_naglowek[] = array('Wartość', 'center');
            $tablica_naglowek[] = array('Płatność', 'center', '', 'class="ListingSchowaj"');
            $tablica_naglowek[] = array('Dostawa', 'center', '', 'class="ListingSchowaj"');
            $tablica_naglowek[] = array('Status', 'center');
            $tablica_naglowek[] = array('Typ', 'center', 'white-space:nowrap;');
                                      
            if ( OPINIE_STATUS == 'tak' || RECENZJE_STATUS == 'tak' ) {
                 $tablica_naglowek[] = array('Opinia <br /> Recenzje', 'center');
            }

            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            
            $statusy = Sprzedaz::ListaStatusowZamowienKolor();
            
            while ($info = $sql->fetch_assoc()) {
              
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['orders_id']) {
                     $tekst .= '<tr class="pozycja_on' . (($info['orders_black_list'] == 2) ? ' CzarnaLista' : '') . '" id="sk_'.$info['orders_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off' . (($info['orders_black_list'] == 2) ? ' CzarnaLista' : '') . '" id="sk_'.$info['orders_id'].'">';
                  }         

                  $tablica = array();

                  // informacje o uwagach na koncie klienta
                  $uwagi = '';
                  
                  // poszuka uwag do podobnych klientow - wg maila i nr telefonu
                  $uwagaZew = array();
                  //
                  if ( ZAMOWIENIE_UWAGI_INNYCH_KLIENTOW == 'tak' ) {
                       //
                       $zapytanie_uwagi = "SELECT customers_id, customers_telephone, customers_email_address FROM customers WHERE customers_dod_info != '' AND customers_id != " . $info['customers_id'];
                       $sql_uwagi = $GLOBALS['db']->open_query($zapytanie_uwagi);   
                      
                       if ( (int)$db->ile_rekordow($sql_uwagi) > 0 ) {
                             //
                             while ($infz = $sql_uwagi->fetch_assoc()) {
                                 //
                                 // mail
                                 if ( $infz['customers_email_address'] == $info['customers_email_address'] ) {
                                      $uwagaZew[$infz['customers_id']] = 'mail';
                                 }
                                 //
                                 $telefon = preg_replace('/\D/', '', str_replace('+48', '', (string)$infz['customers_telephone']));
                                 $telefon_klient = preg_replace('/\D/', '', str_replace('+48', '', (string)$info['customers_telephone']));
                                 //
                                 if ( $telefon == $telefon_klient ) {
                                      $uwagaZew[$infz['customers_id']] = 'telefon';
                                 }
                                 //
                                 unset($telefon, $telefon_klient);
                                 //
                             }
                             //
                       }
                       //
                       $GLOBALS['db']->close_query($sql_uwagi);
                       unset($zapytanie_uwagi);                            
                       //                  
                  }
                  //
                  if ( $info['customers_dod_info'] != '' || $info['orders_adminnotes'] != '' || count($uwagaZew) > 0 ) {

                      $uwagi = '<em class="TipChmurka"><b>Dodatkowa informacja obsługi sklepu (';
                      
                      $uwagiTmp = array();
                      
                      if ( $info['customers_dod_info'] != '' || count($uwagaZew) > 0 ) {
                           $uwagiTmp[] = 'dotycząca klienta';
                      }
                      if ( $info['orders_adminnotes'] != '' ) {
                           $uwagiTmp[] = ((count($uwagiTmp) > 0) ? ' oraz ' : '') . ' dotycząca zamówienia';
                      }
                      
                      $uwagi .= implode('', (array)$uwagiTmp) . ')</b><img style="float:none" src="obrazki/uwaga_mala.png" alt="Informacja" /></em>';

                  }
                  
                  if ( isset($tablica_reklamacji[$info['orders_id']]) ) {
                      
                      $uwagi .= (($uwagi != '') ? '<br />' : '') . '<a href="reklamacje/reklamacje_szczegoly.php?id_poz=' . $tablica_reklamacji[$info['orders_id']] . '" target="_blank" class="TipChmurka"><b>To zamówienie ma nierozpatrzoną reklamację</b><img style="float:none" src="obrazki/ochrona.png" alt="Reklamacja" /></a>';
                      
                  }

                  $tablica[] = array((($info['orders_black_list'] == 2) ? '<div class="CzarnaListaId"><em class="TipChmurka"><b>Zamówienie zostało zakwalifikowane jako "podejrzane"</b><img src="obrazki/czarna_lista.png" alt="Czarna lista" /></em></div>' : '') . '<div id="zamowienie_'.$info['orders_id'].'" class="zmzoom_zamowienie"><div class="podglad_zoom"></div><img src="obrazki/info_duze.png" alt="Szczegóły" /></div>' . $uwagi, 'center', '', 'class="ListingSchowajMobile"');
  
                  unset($uwagi);
                  
                  $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" id="opcja_'.$info['orders_id'].'" value="'.$info['orders_id'].'" /><label class="OpisForPustyLabel" for="opcja_'.$info['orders_id'].'"></label><input type="hidden" name="id[]" value="'.$info['orders_id'].'" />','center');

                  // pobranie faktury proforma
                  $proforma = '';
                  if ( $info['invoice_proforma_date'] > 0 ) {

                      $proforma = '<div class="MalaProforma"><em class="TipChmurka"><b>Proforma pobrana przez klienta: ' . date('d-m-Y H:i', $info['invoice_proforma_date']) . '</b><img src="obrazki/maly_dolar.png" alt="Proforma" /></em></div>';

                  }

                  $tablica[] = array($info['orders_id'] . $proforma,'center');   

                  if ( FAKTURA_PARAGON_LISTING == 'tak' ) {
                                 
                      $jaki_dokument = '';  
                      if ( KOSZYK_WYBOR_DOKUMENTU_SPRZEDAZY == 'tak' ) {
                           //
                           if ( $info['invoice_dokument'] == '1' ) {
                                $jaki_dokument = '<div class="WyborFaktura"><span>F</span></div>';   
                           } else {
                                $jaki_dokument = '<div class="WyborParagon"><span>P</span></div>';   
                           }
                           //
                      }
                      
                      $nr_faktura_paragon = '';   
                      if ( !empty($info['nr_paragonu']) ) {
                           //
                           $nr_faktura_paragon = '<span>paragon</span>' . $info['nr_paragonu'] . FunkcjeWlasnePHP::my_strftime((string)NUMER_PARAGONU_SUFFIX, FunkcjeWlasnePHP::my_strtotime($info['receipts_date_generated']));
                           $nr_faktura_paragon .= '<br /><a href="sprzedaz/zamowienia_paragon_pdf.php?id_poz=' . $info['orders_id'] . '&amp;id=' . $info['receipts_id'] . '"><img src="obrazki/pdf.png" alt="Wydrukuj" /></a>';
                           //
                      }
                      if ( !empty($info['nr_faktury']) ) {
                           //
                           $nr_faktura_paragon = '<span>faktura</span>' . $info['nr_faktury'] . FunkcjeWlasnePHP::my_strftime((string)NUMER_FAKTURY_SUFFIX, FunkcjeWlasnePHP::my_strtotime($info['invoices_date_generated']));
                           $nr_faktura_paragon .= '<br /><a href="sprzedaz/zamowienia_faktura_pdf.php?id_poz=' . $info['orders_id'] . '&amp;id=' . $info['invoices_id'] . '"><img src="obrazki/pdf.png" alt="Wydrukuj" /></a>';
                           //
                      }                  
                      $tablica[] = array($jaki_dokument . $nr_faktura_paragon, 'center', '', 'class="ListingSchowajMobile FakturaPragon"');
                      unset($nr_faktura_paragon, $jaki_dokument);
                  
                  }
                  
                  $wyswietlana_nazwa = '';
                  if ( $info['customers_id'] > 0 ) {
                       $wyswietlana_nazwa = '<a class="KontoKlienta" href="klienci/klienci_edytuj.php?id_poz=' . $info['customers_id'] . '">';
                  }
                  
                  if ( $info['customers_company'] != '' ) {
                       $wyswietlana_nazwa .= '<span class="Firma">'.$info['customers_company'] . '</span><br />';
                  }
                  
                  $wyswietlana_nazwa .= $info['customers_name'] . '<br />';
                  $wyswietlana_nazwa .= $info['customers_street_address']. '<br />';
                  $wyswietlana_nazwa .= $info['customers_postcode']. ' ' . $info['customers_city'] . '<br />';
                  
                  if ( isset($_SESSION['krajDostawy']['nazwa']) && $_SESSION['krajDostawy']['nazwa'] != $info['customers_country'] ) {
                       //
                       $wyswietlana_nazwa .= $info['customers_country'] . '<br />';
                       //
                  }                  
                  
                  if (ZAMOWIENIA_KLIENT_GRUPA == 'tak' && $info['customers_id'] > 0 && $info['customers_dummy_account'] != '1') {
                      $wyswietlana_nazwa .= '<span class="ZamGrupaKlienta">' . Klienci::pokazNazweGrupyKlientow($info['customers_groups_id']) . '</span>';
                  }
                  
                  if (!empty($info['customers_email_address'])) {
                      $wyswietlana_nazwa .= '<span class="MalyMail">' . $info['customers_email_address'] . '</span>';
                  }
                  if (!empty($info['customers_telephone'])) {
                      $wyswietlana_nazwa .= '<span class="MalyTelefon">' . $info['customers_telephone'] . '</span>';
                  }                 

                  if ( $info['customers_id'] > 0 ) {
                       $wyswietlana_nazwa .= '</a>';
                  }    
                  
                  // jezeli staly klient
                  $iloscZam = (int)Klienci::pokazIloscZamowienKlienta($info['customers_id'], 0, true);
                  if ( $iloscZam > 1 ) {

                       $wyswietlana_nazwa = '<em class="TipChmurka" style="float:right"><b>Stały klient - ilość zamówień: ' . $iloscZam . '</b><img src="obrazki/medal.png" alt="Stały klient" /></em>' . $wyswietlana_nazwa;

                  }
                  unset($iloscZam);

                  // jezeli jest gosc wyswietli ikonke
                  if ( $info['customers_dummy_account'] == '1' ) { 

                       $wyswietlana_nazwa = '<em class="TipChmurka" style="float:right"><b>Klient bez rejestracji</b><img src="obrazki/gosc.png" alt="Klient bez rejestracji" /></em>' . $wyswietlana_nazwa;

                  }
                  
                  // jezeli zamowienie z allegro wyswietli NICK
                  if ( $info['orders_source'] == 3 && $info['allegro_nick'] != '' ) {
                       $wyswietlana_nazwa .= '<div class="NickAllegro">Allegro: <b>' . $info['allegro_nick'] . '</b></div>';
                  }
                  
                  $tablica[] = array($wyswietlana_nazwa,'','line-height:1.5');        
                  unset($wyswietlana_nazwa);

                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['date_purchased'])),'center');
                  
                  $info_kupon = '';
                  
                  if ( KUPON_LISTING == 'tak' ) {
                  
                      $zapytanie_kupon = "SELECT title FROM orders_total WHERE orders_id = '" . (int)$info['orders_id'] . "' AND class = 'ot_discount_coupon'";
                      $sql_kupon = $db->open_query($zapytanie_kupon);

                      if ((int)$db->ile_rekordow($sql_kupon) > 0) {
                          $infs = $sql_kupon->fetch_assoc();
                          //
                          $sam_kod = explode(':', (string)$infs['title']);
                          unset($sam_kod[0]);
                          //
                          if ( count($sam_kod) > 0 ) {
                               //
                               $info_kupon = '<div style="margin-top:8px"><em class="TipChmurka"><b>W zamówieniu użyto kuponu rabatowego: <strong>' . implode(':', (array)$sam_kod) . '</strong></b><img src="obrazki/kupon.png" alt="Kupon rabatowy" style="width:30px;height:21px" /></em></div>';
                               //
                          }
                          //
                          unset($sam_kod);
                          //
                      }
                      
                      $db->close_query($sql_kupon);
                      unset($sql_kupon, $zapytanie_kupon);                     

                  }
                  
                  $info_promocja = '';
                  
                  if ( PROMOCJE_LISTING == 'tak' ) {
                  
                      $zapytanie_promocja = "SELECT orders_id FROM orders_products WHERE orders_id = '" . (int)$info['orders_id'] . "' AND specials_status = 1";
                      $sql_promocja = $db->open_query($zapytanie_promocja);

                      if ((int)$db->ile_rekordow($sql_promocja) > 0) {
                          //
                          $info_promocja = '<div style="margin-top:8px"><em class="TipChmurka"><b>W zamówieniu są produkty, które były w promocji</b><img src="obrazki/promocja.png" alt="Promocje" style="width:24px;height:24px" /></em></div>';
                          //
                      }
                      
                      $db->close_query($sql_promocja);
                      unset($sql_promocja, $zapytanie_promocja);                     

                  }                  
                  
                  $tablica[] = array('<span class="InfoCena" style="white-space:nowrap">'.$info['order_total'].'</span>' . $info_kupon . $info_promocja,'right', '');
                  
                  unset($info_kupon);
                  
                  // zaplacone
                  $tgh = '';
                  if ( ZAPLACONE_LISTING == 'tak' ) {
                       if ($info['paid_info'] == '1') { $tgh = '<a href="sprzedaz/zamowienia_zaplacone.php?id_poz='.(int)$info['orders_id'].'" class="Zaplacone">zapłacone</a>'; } else { $tgh = '<a href="sprzedaz/zamowienia_zaplacone.php?id_poz='.(int)$info['orders_id'].'" class="Niezaplacone">niezapłacone</a>'; }   
                  }
                  $tablica[] = array($info['payment_method'] . '<br />' . $tgh,'center', '', 'class="ListingSchowaj"');
                  unset($tgh);

                  $zapytanie_dostawy = "SELECT orders_shipping_type, orders_shipping_number, orders_shipping_link, orders_shipping_allegro FROM orders_shipping WHERE orders_id = '" . (int)$info['orders_id'] . "' ORDER BY orders_shipping_date_created DESC";
                  $sql_dostawy = $db->open_query($zapytanie_dostawy);

                  $wysylka_operator = '';
                  $wysylka_nr = '';

                  if ((int)$db->ile_rekordow($sql_dostawy) > 0) {
                    $infs = $sql_dostawy->fetch_assoc();

                    if ( $infs['orders_shipping_link'] != '' ) {
                        $wysylka_operator .= '<a href="'.$infs['orders_shipping_link'].'" target="_blank">';
                    }
                    $wysylka_operator .= $info['shipping_module'];
                    if ( $infs['orders_shipping_link'] != '' ) {
                        $wysylka_operator .= '</a>';
                    }

                    $wysylka_nr = '<br /><div class="NrPrzesylki TipChmurka"><b>Wygenerowany numer przesyłki: ' . $infs['orders_shipping_number'] . '</b>' . $infs['orders_shipping_type'] . '</div>';
                    
                    if ( (int)$infs['orders_shipping_allegro'] == 1 ) {
                          $wysylka_nr .= '<br /><div class="NrPrzesylkiAllegro TipChmurka"><b>Wysłano nr przesyłki do Allegro</b><small>nr wysłany</small></div>';
                    }                          
                    
                    unset($infs);
                  } else {
                    $wysylka_operator = $info['shipping_module'];
                  }
                  $db->close_query($sql_dostawy);
                  unset($sql_dostawy, $zapytanie_dostawy);      
                  
                  // planowana data wysylki
                  $planowana_wysylka = '';
                  if ( Funkcje::CzyNiePuste($info['shipping_date']) && ZAMOWIENIA_PLANOWANA_DATA_WYSYLKI == 'tak' ) {
                       //
                       $ile_dni = ceil((FunkcjeWlasnePHP::my_strtotime($info['shipping_date']) - time()) / 86400);
                       //
                       if ( $ile_dni <= ZAMOWIENIA_PLANOWANA_DATA_ILOSC_DNI && $info['orders_status_type'] != 3 && $info['orders_status_type'] != 4 ) {
                            $planowana_wysylka = '<div class="PlanowanaWysylkaPrzetrminowna">Planowana wysyłka <br /><b>' . date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($info['shipping_date'])) . '</b><br /><a href="sprzedaz/zamowienia_data_wysylki_wyslij_mail.php?id_poz=' . $info['orders_id'] . '">wyślij mail</a></div>';
                            if ( Funkcje::czyNiePuste($info['shipping_date_mail_send']) ) {
                                 $planowana_wysylka .= '<em class="TipChmurka"><b>Mail wysłano ' . date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['shipping_date_mail_send'])) . '</b><img src="obrazki/tak.png" alt="Wysłano" /></em>';
                            }
                       } else {
                            $planowana_wysylka = '<div class="PlanowanaWysylka">Planowana wysyłka <br /><b>' . date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($info['shipping_date'])) . '</b></div>';
                       }
                  }                  

                  $tablica[] = array($wysylka_operator . $wysylka_nr . $planowana_wysylka,'center', '', 'class="ListingSchowaj"');
                  
                  // opiekun zamowienia
                  if (isset($tablica_opiekunow[(int)$info['service']])) {
                      $opiekun = '<span class="Opiekun">Opiekun:<span>' . $tablica_opiekunow[(int)$info['service']] . '</span></span>';
                     } else {
                      $opiekun = '';
                  }   

                  $selectStatus = '';
                  $kolorDomyslny = '';
                  foreach ($statusy as $status) {
                      $selectStatus .= '<option value="' . $status['id'] . '"' . (($status['kolor'] != '') ? ' data-kolor="' . $status['kolor'] . '" style="color:#' . $status['kolor'] . '"' : '') . (($status['id'] == $info['orders_status']) ? ' selected="selected"' : '') . '>' . $status['text'] . '</option>';
                      //
                      if ( $status['id'] == $info['orders_status'] && $status['kolor'] != '' ) {
                           $kolorDomyslny = 'style="color:#' . $status['kolor'] . '"';
                      }
                      //
                  }
                  $selectStatus = '<div class="ZmianaStatusu" id="zmiana_'.$info['orders_id'].'" data-id="'.$info['orders_id'].'"><select data-id="'.$info['orders_id'].'" ' . $kolorDomyslny . '>' . $selectStatus . '</select><div><table><tr><td><input type="checkbox" value="1" id="mail_'.$info['orders_id'].'" /> <label class="OpisFor" for="mail_'.$info['orders_id'].'">e-mail</label></td> ' . (( SMS_WLACZONE == 'tak' && SMS_ZMIANA_STATUSU_ZAMOWIENIA == 'tak' ) ? '<td><input type="checkbox" value="1" id="sms_'.$info['orders_id'].'" /> <label class="OpisFor" for="sms_'.$info['orders_id'].'">SMS</label></td>' : '') . '</tr></table></div></div>';                  
                  
                  $tablica[] = array($selectStatus . $opiekun,'center');

                  unset($opiekun, $kolorDomyslny, $selectStatus);
                  
                  // 1 - zamowienie ze sklepu z rejestracja
                  // 2 - zamowienie ze sklepu bez rejestracji
                  // 3 - zamowienie z Allegro
                  // 4 - zamowienie dodane przez admina
                  
                  $TypZamowienia = '';
                  switch ($info['orders_source']) {
                    case "3":
                        $TypZamowienia = '<em class="TipChmurka"><b>Zamówienie z Allegro</b><img src="obrazki/allegro_lapka.png" alt="Zamówienie z Allegro" /></em>';
                        break;                 
                    case "4":
                        $TypZamowienia = '<em class="TipChmurka"><b>Zamówienie ręczne</b><img src="obrazki/raczka.png" alt="Zamówienie ręczne" /></em>';
                        break;             
                  }                     

                  $tablica[] = array($TypZamowienia,'center');
                  
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['orders_id']; 
                  $tgm = '';
                  $linia = false;
                  
                  if ( OPINIE_STATUS == 'tak' ) {
                       //
                       $tgm = '<div class="OpiniaRecenzja">';
                       if ( Funkcje::CzyNiePuste($info['review_date_customer']) ) {
                             $tgm .= '<div class="KlientOpinia"><em class="TipChmurka"><b>Mail wysłano ' . date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['review_date'])) . ', klient napisał opinie ' . date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['review_date_customer'])) . '</b><img src="obrazki/klient.png" alt="Klient napisał opinie" /></em></div>';
                             $linia = true;
                       } else if ( Funkcje::CzyNiePuste($info['review_date']) ) {
                             $tgm .= '<div><em class="TipChmurka"><b>Mail wysłano ' . date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['review_date'])) . '</b><img src="obrazki/tak.png" alt="Wysłano" /></em></div>';
                             $linia = true;
                       }            
                       if ( $info['customers_reviews'] == 1 || Funkcje::CzyNiePuste($info['review_date']) ) {
                             $tgm .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wyslij_email_o_opinie.php'.$zmienne_do_przekazania.'"><b>Wyślij e-mail z prośbą o opinie o sklepie</b><img src="obrazki/opinia.png" alt="Wyślij o opinie" /></a>';
                             $linia = true;
                       }
                       $tgm .= '</div>';
                       //
 
                  }      

                  if ( RECENZJE_STATUS == 'tak' ) {
                       //
                       if ( OPINIE_STATUS == 'tak' && $linia == true ) {
                            $tgm .= '<div class="LiniaOpinie"></div>';
                       }
                       //
                       if ( Funkcje::CzyNiePuste($info['reviews_products_date']) ) {
                             $tgm .= '<div class="OpiniaRecenzja"><div><em class="TipChmurka"><b>Mail wysłano ' . date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['reviews_products_date'])) . '</b><img src="obrazki/tak.png" alt="Wysłano" /></em></div>';
                       }
                       if ( $info['customers_reviews'] == 1 || Funkcje::CzyNiePuste($info['reviews_products_date']) ) {
                             $tgm .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wyslij_email_o_recenzje.php'.$zmienne_do_przekazania.'"><b>Wyślij e-mail z prośbą o recenzje o produktach</b><img src="obrazki/opinie_produkty.gif" alt="Wyślij o recenzje" /></a>';
                       }
                       $tgm .= '</div>';
                       //
                  }     

                  if ( OPINIE_STATUS == 'tak' || RECENZJE_STATUS == 'tak' ) {
                       $tablica[] = array($tgm, 'center');
                  }
                  
                  unset($tgm);
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $tekst .= '<div class="ZakladkiSzczegolowe">';
                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_szczegoly.php'.$zmienne_do_przekazania.'"><b>Szczegóły zamówienia</b><img src="obrazki/zobacz.png" alt="Szczegóły zamówienia" /></a>';
                  $tekst .= '<a class="ListingSchowajMobile TipChmurka" href="sprzedaz/zamowienia_szczegoly.php'.$zmienne_do_przekazania.'&zakladka=1"><b>Generowanie wysyłek</b><img src="obrazki/wysylki.png" alt="Generowanie wysyłek" /></a>';
                  
                  if ( PRODUKTY_SZCZEGOLY_ZAMOWIENIA == 'dodatkowa zakładka' ) {
                       $tekst .= '<a class="ListingSchowajMobile TipChmurka" href="sprzedaz/zamowienia_szczegoly.php'.$zmienne_do_przekazania.'&zakladka=2"><b>Zakupione produkty</b><img src="obrazki/produkty.png" alt="Zakupione produkty" /></a>';
                  }
                  
                  $tekst .= '<a class="ListingSchowajMobile TipChmurka" href="sprzedaz/zamowienia_szczegoly.php'.$zmienne_do_przekazania.'&zakladka=3"><b>Historia zamówień</b><img src="obrazki/historia.png" alt="Historia zamówień" /></a>';
                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wyslij_email.php'.$zmienne_do_przekazania.'"><b>Wyślij e-mail z zamówieniem</b><img src="obrazki/wyslij_mail.png" alt="Wyślij e-mail z zamówieniem" /></a>';                  
                  $tekst .= '</div>';
                  
                  $tekst .= '<div class="ZakladkiOdstep">';
                  $tekst .= '<a class="ListingSchowajMobile TipChmurka" href="sprzedaz/zamowienia_wz_pdf.php'.$zmienne_do_przekazania.'"><b>Wydane z magazynu</b><img src="obrazki/pdf_2.png" alt="Wz" /></a>';
                  $tekst .= '<a class="ListingSchowajMobile TipChmurka" href="sprzedaz/zamowienia_zamowienie_pdf.php'.$zmienne_do_przekazania.'"><b>Wydruk zamówienia</b><img src="obrazki/zamowienie_pdf.png" alt="Wydruk zamówienia" /></a>';
                  $tekst .= '<a class="ListingSchowajMobile TipChmurka" href="sprzedaz/zamowienia_faktura_proforma.php'.$zmienne_do_przekazania.'"><b>Wydruk faktury proforma</b><img src="obrazki/proforma_pdf.png" alt="Wydruk faktury proforma" /></a>'; 
                  $tekst .= '</div>';
                  
                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';                  
                  $tekst .= '<a class="ListingSchowajMobile TipChmurka" href="sprzedaz/zamowienia_pobierz.php'.$zmienne_do_przekazania.'"><b>Pobierz</b><img src="obrazki/export.png" alt="Pobierz" /></a>'; 
                  $tekst .= '<em class="TipChmurka" style="cursor:pointer" id="widok_' . $info['orders_id'] . '" onclick="produkty(' . $info['orders_id'] . ')"><b>Rozwiń listę <br /> zakupionych <br /> produktów</b><img src="obrazki/rozwin.png" alt="Lista produktów" /></em>'; 
                  
                  if ( $info['orders_status_type'] == 4 && $info['status_update_products'] == 0 ) {
                       $tekst .= '<div class="UzupelnijMagazyn"><a href="sprzedaz/zamowienia_aktualizuj_magazyn.php'.$zmienne_do_przekazania.'">Aktualizuj magazyn</a></div>';
                  }
                  
                  $tekst .= '</td></tr>';
                  
                  $ColSpan = 12;
                  
                  if ( FAKTURA_PARAGON_LISTING == 'nie' ) {
                       $ColSpan -= 1;
                  }
                  if ( OPINIE_STATUS == 'tak' || RECENZJE_STATUS == 'tak' ) {
                       $ColSpan += 1;
                  }

                  $tekst .= '<tr><td colspan="' . $ColSpan . '"><div id="produkty_' . $info['orders_id'] . '"></div></td></tr>';
                  
                  unset($ColSpan);
                  
            } 
            $tekst .= '</table>';
            //
            echo $tekst;
            //
            $db->close_query($sql);
            unset($listing_danych,$tekst,$tablica,$tablica_naglowek,$statusy);        

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
            $.AutoUzupelnienie( 'szukaj', 'Podpowiedzi', 'ajax/autouzupelnienie_zamowienia.php', 50, 400 );
            
            $.AutoUzupelnienie( 'szukaj_produkt', 'PodpowiedziMale', 'ajax/autouzupelnienie_produkty_zamowienie.php', 50, 300 );
            
            $('input.datepicker').Zebra_DatePicker({
              format: 'd-m-Y',
              inside: false,
              readonly_element: false
            });             

            $('input.datepickerPelny').Zebra_DatePicker({
              format: 'd-m-Y H:i',
              inside: false,
              readonly_element: false
            });             

            $('#akcja_dolna').change(function() {
               $("#page").hide();
               $('#potwierdzenie_usuniecia').hide();
               if ( this.value != '1' ) {                                     
                    $("#page").html('');                 
               }
               if ( this.value == '1' ) {
                    $('#zmian_daty_wysylki').hide();   
                    $('#potwierdzenie_usuniecia').hide();  
                    //
                    $('#ekr_preloader').css('display','block');
                    $("#page").load('sprzedaz/zamowienia_zmien_status_multi.php', function() {
                       //
                       $('#ekr_preloader').css('display','none');
                       $("#page").show();
                       pokazChmurki();
                       //
                    });                 
               }
               if ( this.value == '7' ) {
                    $("#page").html(''); 
                    $('#zmian_daty_wysylki').show();   
                    $('#potwierdzenie_usuniecia').hide();                    
               }               
               if ( this.value == '6' ) {
                    $('#usuniecie_zamowien_nie').prop('checked', true);
                    $('#usuniecie_zamowien_tak').prop('checked', false);
                    $('#potwierdzenie_usuniecia').show();
                    $('#zmian_daty_wysylki').hide();  
                    $("#page").html(''); 
               }
            });

        });
        
        function produkty(id) {
            //
            if ( $('#produkty_' + id).html() != '' ) {
                 //
                 $('#widok_' + id).find('img').attr('src','obrazki/rozwin.png');
                 $('#widok_' + id).find('b').html('Rozwiń listę <br /> zakupionych <br /> produktów');
                 //              
                 $('#produkty_' + id).slideUp('fast', function() {
                    $('#produkty_' + id).html('');
                 });
              } else {
                $('#produkty_' + id).html('<div class="TloObramowania"><img src="obrazki/_loader_small.gif" alt="" /></div>');
                $.post("ajax/zamowienie_produkty.php?tok=" + $('#tok').val(),
                    { id: id },
                    function(data) { 
                      //
                      $('#widok_' + id).find('img').attr('src','obrazki/zwin.png');
                      $('#widok_' + id).find('b').html('Zwiń listę <br /> zakupionych <br /> produktów');
                      //
                      $('#produkty_' + id).hide()
                      $('#produkty_' + id).html(data);
                      $('#produkty_' + id).slideDown('fast');
                      //
                      $(".ZdjecieProduktu").colorbox({ maxWidth:'90%', maxHeight:'90%' });
                    }           
                );  
            }
            //
        }          
        </script>

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Zamówienia</div>

            <div id="wyszukaj">
                <form action="sprzedaz/zamowienia.php" method="post" id="zamowieniaForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj: <em class="TipIkona"><b>Wyszukiwanie po danych klienta, firmy, danych adresowych, adresie email</b></em></span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="25" />
                </div>  
                
                <div class="wyszukaj_select">
                    <span>Wyszukaj produkt: <em class="TipIkona"><b>Wyszukiwanie zamówień z szukanym produktem (po nazwie produktu lub numerze katalogowym)</b></em></span>
                    <input type="text" name="szukaj_produkt" id="szukaj_produkt" value="<?php echo ((isset($_GET['szukaj_produkt'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj_produkt'])) : ''); ?>" size="25" />
                </div>    

                <script>
                function FiltrNazwaCecha(id) {
                  if ( parseInt(id) == 0 ) {
                       $('#FiltrCechyZamowienie').hide();
                       $("#FiltrCechySelect").html('');
                       $('#FiltrCechyWartosciZamowienie').hide();
                       $("#FiltrCechyWartosciSelect").html('');                       
                  } else {
                       $('#FiltrCechyZamowienie').show();
                       $("#FiltrCechySelect").html('<img src="obrazki/_loader_small.gif" alt="" />');
                       $.get('ajax/zamowienie_filtr_cechy.php', { tok: '<?php echo Sesje::Token(); ?>', cechy: 'tak', id_nazwa: '<?php echo ((isset($_GET['szukaj_cecha_nazwa'])) ? (int)$_GET['szukaj_cecha_nazwa'] : ''); ?>' }, function(data) {
                           $("#FiltrCechySelect").html(data);
                       });          
                  }
                }
                function FiltrWartoscCecha(id) {
                  if ( parseInt(id) == 0 ) {
                       $('#FiltrCechyWartosciZamowienie').hide();
                       $("#FiltrCechyWartosciSelect").html('');
                  } else {
                       $('#FiltrCechyWartosciZamowienie').show();
                       $("#FiltrCechyWartosciSelect").html('<img src="obrazki/_loader_small.gif" alt="" />');
                       $.get('ajax/zamowienie_filtr_cechy.php', { tok: '<?php echo Sesje::Token(); ?>', cechy: 'tak', wartosc: 'tak', id_nazwa: id, id_wartosc: '<?php echo ((isset($_GET['szukaj_cecha_wartosc'])) ? (int)$_GET['szukaj_cecha_wartosc'] : ''); ?>' }, function(data) {
                           $("#FiltrCechyWartosciSelect").html(data);
                       });          
                  }
                }                
                </script>                   
                
                <div class="wyszukaj_select">
                    <span>Wyszukaj w cechach: <em class="TipIkona"><b>Wyszukiwanie zamówień z produktami o określonych cechach</b></em></span>
                    <?php
                    $tablica_cechy = array();
                    $tablica_cechy[] = array('id' => '0', 'text' => 'nie');
                    $tablica_cechy[] = array('id' => '1', 'text' => 'tak');
                    echo Funkcje::RozwijaneMenu('szukaj_cecha', $tablica_cechy, ((isset($_GET['szukaj_cecha'])) ? $filtr->process($_GET['szukaj_cecha']) : '0'), ' onchange="FiltrNazwaCecha(this.value)"'); 
                    //
                    if ( isset($_GET['szukaj_cecha']) && $_GET['szukaj_cecha'] == '1' ) {
                         if ( isset($_GET['szukaj_cecha_nazwa']) && (int)$_GET['szukaj_cecha_nazwa'] > 0 ) { 
                              echo '<script>$(document).ready(function() { FiltrNazwaCecha(' . (int)$_GET['szukaj_cecha_nazwa'] . ') })</script>';
                              echo '<script>$(document).ready(function() { FiltrWartoscCecha(' . (int)$_GET['szukaj_cecha_nazwa'] . ') })</script>';
                         }
                    }                    
                    ?>
                </div>  

                <div class="wyszukaj_select" id="FiltrCechyZamowienie" style="display:none">
                    <span>Z cechą:</span>
                    <div id="FiltrCechySelect" style="display:inline-block"></div>
                </div>                  

                <div class="wyszukaj_select" id="FiltrCechyWartosciZamowienie" style="display:none">
                    <div id="FiltrCechyWartosciSelect" style="display:inline-block"></div>
                </div>             
          
                <div class="wyszukaj_select">
                    <span>Wyszukaj w uwagach: <em class="TipIkona"><b>Wyszukiwanie w uwagach do zamówienia - niewidoczne dla klientów</b></em></span>
                    <input type="text" name="szukaj_uwagi" id="szukaj_uwagi" value="<?php echo ((isset($_GET['szukaj_uwagi'])) ? $filtr->process($_GET['szukaj_uwagi']) : ''); ?>" size="20" />
                </div>           
                
                <div class="wyszukaj_select">
                    <span>Numer zamówienia:</span>
                    <input type="text" id="numer" name="szukaj_numer" value="<?php echo ((isset($_GET['szukaj_numer'])) ? $filtr->process($_GET['szukaj_numer']) : ''); ?>" size="6" />
                </div>  

                <div class="wyszukaj_select">
                    <span>Data złożenia:</span>
                    <input type="text" id="data_zamowienia_od" name="szukaj_data_zamowienia_od" value="<?php echo ((isset($_GET['szukaj_data_zamowienia_od'])) ? $filtr->process($_GET['szukaj_data_zamowienia_od']) : ''); ?>" size="20" class="datepickerPelny" />

                    do&nbsp;
                    
                    <input type="text" id="data_zamowienia_do" name="szukaj_data_zamowienia_do" value="<?php echo ((isset($_GET['szukaj_data_zamowienia_do'])) ? $filtr->process($_GET['szukaj_data_zamowienia_do']) : ''); ?>" size="20" class="datepickerPelny" />
                    
                </div>  

                <div class="wyszukaj_select">
                    <span>Status:</span>
                    <?php
                    $tablica_status= array();
                    $tablica_status = Sprzedaz::ListaStatusowZamowien(true);
                    echo Funkcje::RozwijaneMenu('szukaj_status', $tablica_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : ''), ' style="max-width:200px"'); ?>
                </div>                 
                
                <?php if ( ZAPLACONE_LISTING == 'tak' ) { ?>

                <div class="wyszukaj_select">
                    <span>Zapłacone:</span>
                    <?php
                    $tablica_zaplacone = array();
                    $tablica_zaplacone[] = array('id' => '0', 'text' => 'dowolne');
                    $tablica_zaplacone[] = array('id' => '1', 'text' => 'zapłacone');
                    $tablica_zaplacone[] = array('id' => '2', 'text' => 'niezapłacone');
                    echo Funkcje::RozwijaneMenu('zaplacone', $tablica_zaplacone, ((isset($_GET['zaplacone'])) ? $filtr->process($_GET['zaplacone']) : '0'), ' style="max-width:150px"'); ?>
                </div>                  
                
                <?php } ?>
                
                <div class="wyszukaj_select">
                    <span>Rodzaj wysyłki:</span>
                    <?php
                    $zapytanie_tmp = 'select distinct shipping_module from orders';
                    $sqls = $db->open_query($zapytanie_tmp);
                    //
                    $tablica_typ = array();
                    $tablica_typ[] = array('id' => 0, 'text' => 'dowolny');
                    while ($infs = $sqls->fetch_assoc()) { 
                           if (!empty($infs['shipping_module'])) {
                               $tablica_typ[] = array('id' => $infs['shipping_module'], 'text' => $infs['shipping_module']);
                           }
                    }
                    $db->close_query($sqls); 
                    unset($zapytanie_tmp, $infs);    
                    //
                    usort($tablica_typ, function ($a, $b) {
                        return strcmp($a['id'], $b['id']);
                    });
                    //
                    echo Funkcje::RozwijaneMenu('szukaj_wysylka', $tablica_typ, ((isset($_GET['szukaj_wysylka'])) ? $filtr->process($_GET['szukaj_wysylka']) : ''), ' style="max-width:180px"'); ?>
                </div>  
                
                <div class="wyszukaj_select">
                    <span>Przesyłka:</span>
                    <?php
                    $tablica_przesylka = array();
                    $tablica_przesylka[] = array('id' => '0', 'text' => 'dowolne');
                    $tablica_przesylka[] = array('id' => '1', 'text' => 'przesyłka utworzona');
                    $tablica_przesylka[] = array('id' => '2', 'text' => 'brak przesyłki');
                    echo Funkcje::RozwijaneMenu('przesylka', $tablica_przesylka, ((isset($_GET['przesylka'])) ? $filtr->process($_GET['przesylka']) : '0'), ' style="max-width:150px"'); ?>
                </div>                   

                <div class="wyszukaj_select">
                    <span>Rodzaj płatności:</span>
                    <?php
                    $zapytanie_tmp = 'select distinct payment_method from orders';
                    $sqls = $db->open_query($zapytanie_tmp);
                    //
                    $tablica_typ = array();
                    $tablica_typ[] = array('id' => 0, 'text' => 'dowolny');
                    while ($infs = $sqls->fetch_assoc()) { 
                           if (!empty($infs['payment_method'])) {
                               $tablica_typ[] = array('id' => $infs['payment_method'], 'text' => $infs['payment_method']);
                           }
                    }
                    $db->close_query($sqls); 
                    unset($zapytanie_tmp, $infs);    
                    //
                    usort($tablica_typ, function ($a, $b) {
                        return strcmp($a['id'], $b['id']);
                    });
                    //
                    echo Funkcje::RozwijaneMenu('szukaj_platnosc', $tablica_typ, ((isset($_GET['szukaj_platnosc'])) ? $filtr->process($_GET['szukaj_platnosc']) : ''), ' style="max-width:180px"'); ?>
                </div>  
                
                <div class="wyszukaj_select">
                    <span>Opiekun:</span>
                    <?php
                    // pobieranie informacji od uzytkownikach
                    $zapytanie_tmp = "select * from admin order by admin_lastname, admin_firstname";
                    $sqls = $db->open_query($zapytanie_tmp);
                    //
                    $tablica_user = array();
                    $tablica_user[] = array('id' => 0, 'text' => 'dowolny');
                    while ($infs = $sqls->fetch_assoc()) { 
                           $tablica_user[] = array('id' => $infs['admin_id'], 'text' => $infs['admin_firstname'] . ' ' . $infs['admin_lastname']);
                    }
                    $db->close_query($sqls); 
                    unset($zapytanie_tmp, $infs);    
                    //
                    echo Funkcje::RozwijaneMenu('opiekun', $tablica_user, ((isset($_GET['opiekun'])) ? $filtr->process($_GET['opiekun']) : ''), ' style="max-width:150px"'); ?>
                </div>

                <div class="wyszukaj_select">
                    <span>Typ zamówienia:</span>
                    <?php
                    $tablica_typ = array();
                    $tablica_typ = Sprzedaz::TypyZamowien( true );
                    $tablica_typ[] = array('id' => '5', 'text' => 'zamówienia bez zamówień z Allegro');
                    echo Funkcje::RozwijaneMenu('typ_zam', $tablica_typ, ((isset($_GET['typ_zam'])) ? $filtr->process($_GET['typ_zam']) : '99'), ' style="max-width:200px"'); ?>
                </div>
                
                <div class="wyszukaj_select">
                    <span>Rodzaj zamówienia:</span>
                    <?php
                    $tablica_rodzaj = array();
                    $tablica_rodzaj[] = array('id' => '0', 'text' => 'dowolny');
                    $tablica_rodzaj[] = array('id' => '1', 'text' => 'Nowe');
                    $tablica_rodzaj[] = array('id' => '2', 'text' => 'W realizacji');
                    $tablica_rodzaj[] = array('id' => '3', 'text' => 'Zamknięte (zrealizowane)');
                    $tablica_rodzaj[] = array('id' => '4', 'text' => 'Zamknięte (niezrealizowane)');
                    echo Funkcje::RozwijaneMenu('rodzaj_zam', $tablica_rodzaj, ((isset($_GET['rodzaj_zam'])) ? $filtr->process($_GET['rodzaj_zam']) : '0'), ' style="max-width:220px"'); ?>
                </div>     

                <div class="wyszukaj_select">
                    <span>Wartość zamówienia:</span>
                    <input type="text" name="szukaj_wartosc_zamowienia_od" value="<?php echo ((isset($_GET['szukaj_wartosc_zamowienia_od'])) ? $filtr->process($_GET['szukaj_wartosc_zamowienia_od']) : ''); ?>" size="6" /> do
                    <input type="text" name="szukaj_wartosc_zamowienia_do" value="<?php echo ((isset($_GET['szukaj_wartosc_zamowienia_do'])) ? $filtr->process($_GET['szukaj_wartosc_zamowienia_do']) : ''); ?>" size="6" />
                </div> 

                <div class="wyszukaj_select">
                    <span>Waluta zamówienia:</span>
                    <?php
                    $tablica_waluta = array();
                    $tablica_waluta[] = array('id' => '', 'text' => 'dowolna');
                    $sql_waluta = $db->open_query("select * from currencies");
                    while ( $infw = $sql_waluta->fetch_assoc() ) {
                        $tablica_waluta[] = array('id' => $infw['code'], 'text' => $infw['code']);
                    }
                    $db->close_query($sql_waluta); 
                    unset($infw);    
                    //
                    echo Funkcje::RozwijaneMenu('szukaj_waluta', $tablica_waluta, ((isset($_GET['szukaj_waluta'])) ? $filtr->process($_GET['szukaj_waluta']) : ''), ' style="max-width:100px"');
                    unset($tablica_waluta);
                    ?>
                </div> 
                
                <div class="wyszukaj_select">
                    <span>Nr przesyłki: </span>
                    <input type="text" name="nr_przesylki" id="nr_przesylki" value="<?php echo ((isset($_GET['nr_przesylki'])) ? $filtr->process($_GET['nr_przesylki']) : ''); ?>" size="20" />
                </div>                 

                <div class="wyszukaj_select">
                    <span>Zmiana statusu:</span>
                    <input type="text" id="data_statusu_od" name="szukaj_data_statusu_od" value="<?php echo ((isset($_GET['szukaj_data_statusu_od'])) ? $filtr->process($_GET['szukaj_data_statusu_od']) : ''); ?>" size="20" class="datepickerPelny" />
                    
                    do&nbsp;
                    
                    <input type="text" id="data_statusu_do" name="szukaj_data_statusu_do" value="<?php echo ((isset($_GET['szukaj_data_statusu_do'])) ? $filtr->process($_GET['szukaj_data_statusu_do']) : ''); ?>" size="20" class="datepickerPelny" />

                </div>    

                <div class="wyszukaj_select">
                    <span>Nr aukcji: <em class="TipIkona"><b>Nr aukcji Allegro - jeżeli zamówienie pochodzi z Allegro</b></em></span>
                    <input type="text" name="nr_aukcji" id="nr_aukcji" value="<?php echo ((isset($_GET['nr_aukcji'])) ? $filtr->process($_GET['nr_aukcji']) : ''); ?>" size="15" />
                </div> 

                <div class="wyszukaj_select">
                    <span>Nick allegro:</span>
                    <input type="text" name="nick_allegro" id="nick_allegro" value="<?php echo ((isset($_GET['nick_allegro'])) ? $filtr->process($_GET['nick_allegro']) : ''); ?>" size="15" />
                </div>  
                
                <div class="wyszukaj_select">
                    <span>Kod licencyjny:</span>
                    <input type="text" name="kod_licencyjny" value="<?php echo ((isset($_GET['kod_licencyjny'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['kod_licencyjny'])) : ''); ?>" size="20" />
                </div>                   

                <div class="wyszukaj_select">
                    <span>Reklamacje:</span>
                    <?php
                    $tablica_rodzaj = array();
                    $tablica_rodzaj[] = array('id' => '0', 'text' => 'dowolny');
                    $tablica_rodzaj[] = array('id' => '1', 'text' => 'z nierozpatrzonymi reklamacjami');
                    echo Funkcje::RozwijaneMenu('status_reklamacji', $tablica_rodzaj, ((isset($_GET['status_reklamacji'])) ? $filtr->process($_GET['status_reklamacji']) : '0'), ' style="max-width:200px"'); ?>
                </div>                  
                                
                <?php if ( ZAMOWIENIA_PLANOWANA_DATA_WYSYLKI == 'tak' ) { ?>
                
                <div class="wyszukaj_select">
                    <span>Planowana data wysyłki:</span>
                    <input type="text" id="data_wysylki_od" name="szukaj_data_wysylki_od" value="<?php echo ((isset($_GET['szukaj_data_wysylki_od'])) ? $filtr->process($_GET['szukaj_data_wysylki_od']) : ''); ?>" size="10" class="datepicker" />
                    
                    &nbsp;do&nbsp;
                    
                    <input type="text" id="data_wysylki_do" name="szukaj_data_wysylki_do" value="<?php echo ((isset($_GET['szukaj_data_wysylki_do'])) ? $filtr->process($_GET['szukaj_data_wysylki_do']) : ''); ?>" size="10" class="datepicker" />

                </div>                  
                
                <?php } ?>
                
                <?php if ( KUPON_LISTING == 'tak' ) { ?>

                <div class="wyszukaj_select">
                    <span>Kod rabatowy:</span>
                    <input type="text" name="kod_rabatowy" value="<?php echo ((isset($_GET['kod_rabatowy'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['kod_rabatowy'])) : ''); ?>" size="30" />
                </div>   
                
                <?php } ?>
                
                <div class="wyszukaj_select">
                    <span>Kraj dostawy:</span>
                    <?php
                    $tablica_panstw = array();
                    $tablica_panstw[] = array('id' => 0, 'text' => 'dowolny');   
                    $tablica_panstw = array_merge($tablica_panstw, Klienci::ListaPanstw());
                    echo Funkcje::RozwijaneMenu('kraj_dostawy', $tablica_panstw, ((isset($_GET['kraj_dostawy'])) ? (int)$_GET['kraj_dostawy'] : ''));
                    unset($tablica_panstw);
                    ?>
                </div>     

                <?php if ( KOSZYK_WYBOR_DOKUMENTU_SPRZEDAZY == 'tak' ) { ?>

                <div class="wyszukaj_select">
                    <span>Dokument sprzedaży:</span>
                    <?php
                    $tablica_dokument = array();
                    $tablica_dokument[] = array('id' => '', 'text' => 'dowolny');
                    $tablica_dokument[] = array('id' => 'f', 'text' => 'faktura');
                    $tablica_dokument[] = array('id' => 'p', 'text' => 'paragon');
                    echo Funkcje::RozwijaneMenu('dokument_sprzedazy', $tablica_dokument, ((isset($_GET['dokument_sprzedazy'])) ? $filtr->process($_GET['dokument_sprzedazy']) : '')); ?>
                </div>                   
                
                <div class="wyszukaj_select">
                    <span>Wystawiony dokument sprzedaży:</span>
                    <?php
                    $tablica_dokument = array();
                    $tablica_dokument[] = array('id' => '', 'text' => 'dowolny');
                    $tablica_dokument[] = array('id' => 'f', 'text' => 'faktura');
                    $tablica_dokument[] = array('id' => 'p', 'text' => 'paragon');
                    $tablica_dokument[] = array('id' => 'bez', 'text' => 'brak wystawionego dokumentu sprzedaży');
                    echo Funkcje::RozwijaneMenu('dokument_sprzedazy_wystawiony', $tablica_dokument, ((isset($_GET['dokument_sprzedazy_wystawiony'])) ? $filtr->process($_GET['dokument_sprzedazy_wystawiony']) : '')); ?>
                </div>    
                
                <?php } ?>

                <?php 
                // tworzy ukryte pola hidden do wyszukiwania - filtra 
                if (isset($_GET['sort'])) { 
                    echo '<div><input type="hidden" name="sort" value="'.$filtr->process($_GET['sort']).'" /></div>';
                }                
                ?>                

                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="sprzedaz/zamowienia.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?> 

                <div style="clear:both"></div>
            </div>        
            
            <form action="sprzedaz/zamowienia_akcja.php" method="post" class="cmxform">

            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="sprzedaz/zamowienia.php?sort=sort_a1">numeru malejąco</a>
                <a id="sort_a2" class="sortowanie" href="sprzedaz/zamowienia.php?sort=sort_a2">numeru rosnąco</a>
                <a id="sort_a3" class="sortowanie" href="sprzedaz/zamowienia.php?sort=sort_a3">daty zamówienia malejąco</a>
                <a id="sort_a4" class="sortowanie" href="sprzedaz/zamowienia.php?sort=sort_a4">daty zamówienia rosnąco</a>
                
                <?php if ( ZAMOWIENIA_PLANOWANA_DATA_WYSYLKI == 'tak' ) { ?>

                <a id="sort_a5" class="sortowanie" href="sprzedaz/zamowienia.php?sort=sort_a5">daty planowanej wysyłki malejąco</a>
                <a id="sort_a6" class="sortowanie" href="sprzedaz/zamowienia.php?sort=sort_a6">daty planowanej wysyłki rosnąco</a>
                  
                <?php } ?>
            
            </div>             

            <div id="PozycjeIkon">
            
                <div>
                  <a class="dodaj" href="sprzedaz/zamowienia_dodaj.php<?php echo Funkcje::Zwroc_Wybrane_Get(array('klient_id')); ?>">dodaj nowe zamówienie</a>
                  <a style="margin-left:10px" class="usun" href="sprzedaz/zamowienia_usun_masowe.php">usuń zamówienia</a>
                </div>    
                
                <div id="Legenda" style="float:right">
                  <span class="PobranaProforma"> klient pobrał proformę</span>
                  <span class="StalyKlient"> stały klient</span>
                  <span class="Gosc"> klient bez rejestracji</span>
                  <span class="ZamowienieAllegro"> zamówienie z Allegro</span>
                  <span class="ZamowienieReczne"> zamówienie ręczne</span>
                </div> 
                
            </div>

            <div class="cl"></div>               
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>

            <div id="akcja">
            
                <div class="lf"><img src="obrazki/strzalka.png" alt="" /></div>
                
                <div class="lf" style="padding-right:20px">
                  <span onclick="akcja(1)">zaznacz wszystkie</span>
                  <span onclick="akcja(2)">odznacz wszystkie</span>
                </div>
                
                <div id="akc">
                  Wykonaj akcje: 
                  <select name="akcja_dolna" id="akcja_dolna">
                    <option value="0"></option>
                    <option value="1">zmień status zaznaczonych</option>
                    <option value="2">wydruk zamówienia PDF</option>
                    <option value="5">zestawienie zamówień PDF</option>
                    <option value="8">zestawienie produktów z zamówień PDF (z kategoriami)</option>
                    <option value="9">zestawienie produktów z zamówień PDF (z nr kat)</option>
                    <option value="3">połącz wybrane zamowienia</option>
                    <option value="4">pobierz zamówienia w formacie CSV</option>
                    <option value="6">usuń wybrane zamówienia</option>
                    <?php if ( ZAMOWIENIA_PLANOWANA_DATA_WYSYLKI == 'tak' ) { ?>
                    <option value="7">zmiana planowanej daty wysyłki</option>
                    <?php } ?>
                  </select>
                </div>
                
                <div class="cl"></div>
              
            </div>
            
            <div class="cl"></div>
                      
            <div id="page" class="RamkaAkcji"></div>
            
            <div class="cl"></div>

            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>

            <?php if ($ile_pozycji > 0) { ?>
            
                <?php if (isset($_GET['klient_id']) && $_GET['klient_id'] != '' ) { ?>
                  <div class="lf"><button type="button" class="przyciskNon" onclick="cofnij('klienci','?id_poz=<?php echo (int)$_GET['klient_id']; ?><?php echo Funkcje::Zwroc_Get(array('x','y','id_poz','klient_id')); ?>', 'klienci');">Powrót</button></div>
                <?php } ?>               

                <div id="potwierdzenie_usuniecia" style="padding-bottom:10px;display:none">
                
                    <div class="RamkaAkcji" style="display:block;padding:15px">
                
                        <div class="rg">
                          
                            <p style="padding-right:0px">
                                <label style="width:auto">Czy na pewno chcesz usunąć wybrane zamówienia ?</label>
                                <input type="radio" value="0" name="usuniecie_zamowien" id="usuniecie_zamowien_nie" checked="checked" /><label class="OpisFor" for="usuniecie_zamowien_nie">nie</label>
                                <input type="radio" value="1" name="usuniecie_zamowien" id="usuniecie_zamowien_tak" /><label class="OpisFor" style="padding-right:0px !important" for="usuniecie_zamowien_tak">tak</label>
                            </p>
                            
                        </div>
                        
                        <div class="cl"></div>
                        
                        <div class="ostrzezenie rg">Operacja usunięcia jest nieodracalna ! Zamówień po usunięciu nie będzie można przywrócić !</div>
                        
                        <div class="cl"></div>
                   
                    </div>
                    
                </div>
                
                <?php if ( ZAMOWIENIA_PLANOWANA_DATA_WYSYLKI == 'tak' ) { ?>
                
                <div id="zmian_daty_wysylki" style="padding-bottom:10px;display:none">
                
                    <div class="RamkaAkcji" style="display:block;padding:15px">
                
                        <div class="rg">
                          
                            <p style="padding-right:0px">
                                <label style="width:auto">Podaj nową datę wysyłki:</label>
                                <input type="text" id="nowa_data_wysylki" name="nowa_data_wysylki" value="" size="10" class="datepicker" />
                            </p>
                            
                        </div>

                        <div class="cl"></div>
                        
                    </div>
                    
                </div>                
                
                <?php } ?>
                
                <div class="rg"><input type="submit" class="przyciskBut" value="Zapisz zmiany" /></div>

            <?php } else { ?> 
            
              <?php if (isset($_GET['klient_id']) && $_GET['klient_id'] != '' ) { ?>
                <div class="lf"><button type="button" class="przyciskNon" onclick="cofnij('klienci','?id_poz=<?php echo (int)$_GET['klient_id']; ?><?php echo Funkcje::Zwroc_Get(array('x','y','id_poz','klient_id')); ?>', 'klienci');">Powrót</button></div>             
              <?php } ?> 
              
            <?php } ?> 

            <div class="cl"></div>

            </form>

            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('sprzedaz/zamowienia.php', $zapytanie, $ile_licznika, $ile_pozycji, 'orders_id'); ?>
            </script>              
            
        </div>

        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
