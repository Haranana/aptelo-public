<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');
// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ( isset($_GET['filtr']) ) {
     //
     unset($_SESSION['filtry']['zamowienia.php']);
     //
     Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz=' . $_GET['id_poz']);
}

if ($prot->wyswietlStrone) {

    $JestZamowienie = false;
    
    if ( isset($_POST['id']) && (int)$_POST['id'] > 0 ) {
         $_GET['id_poz'] = (int)$_POST['id'];
    }
    
    if ( ( isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0 ) ) {

        // aktualizacja informacji o wysylce FURGONETKA
        if ( INTEGRACJA_FURGONETKA_WLACZONY == 'tak' ) {

            $zapytaniePrzesylki = "SELECT * FROM orders_shipping WHERE orders_id = '" . (int)$_GET['id_poz'] . "' AND DATE(orders_shipping_date_modified) > DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND orders_shipping_type = 'FURGONETKA'";
            $sqlPrzesylki = $db->open_query($zapytaniePrzesylki);

            if ((int)$db->ile_rekordow($sqlPrzesylki) > 0) {

                $apiKurierFurgonetka = new FurgonetkaRestApi(true, $_GET['id_poz'], '1');

                while ($infoPrzesylki = $sqlPrzesylki->fetch_assoc()) {

                    $WynikFurgonetka = $apiKurierFurgonetka->commandGet('packages/'.$infoPrzesylki['orders_shipping_misc'], true, '', true);
                                
                    if ( $WynikFurgonetka ) {

                        $NumeryPrzesylek = '';
                        $NumerProtokolu = '';

                        if ( is_array($WynikFurgonetka->parcels) ) {
                            foreach ( $WynikFurgonetka->parcels as $Przesylka ) {
                                if ( $Przesylka->package_no != '' ) {
                                    $NumeryPrzesylek .= $Przesylka->package_no . ',';
                                }
                            }
                        }
                        if ( isset($WynikFurgonetka->state) && ( $WynikFurgonetka->state != 'waiting' && $WynikFurgonetka->state != 'cancelled' ) ) {

                            $pola = array();
                            $pola = array(
                                    array('orders_shipping_number',substr((string)$NumeryPrzesylek, 0, -1)),
                                    array('orders_shipping_status',$WynikFurgonetka->state)
                            );

                            if ( isset($WynikFurgonetka->pickup_number) ) {
                                $pola[] = array('orders_shipping_protocol',$WynikFurgonetka->pickup_number);
                            }
                            if ( $WynikFurgonetka->state == 'ordered' ) {
                                $pola[] = array('orders_shipping_uuid_order','');
                            }

                            $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".(int)$infoPrzesylki['orders_shipping_id']."'");
                            unset($pola);

                       }

                       if ( (isset($WynikFurgonetka->state) && $WynikFurgonetka->state == 'cancelled') ) {
                            $pola = array(
                                    array('orders_shipping_uuid_cancel',''),
                                    array('orders_shipping_status',$WynikFurgonetka->state),
                                    array('orders_shipping_protocol','')
                            );

                            $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".(int)$infoPrzesylki['orders_shipping_id']."'");
                            unset($pola);

                       }

                       unset($WynikFurgonetka);

                    }
                }
            }

            $db->close_query($sqlPrzesylki);
            unset($zapytaniePrzesylki);

        }

        if ( INTEGRACJA_KURIER_INPOST_SHIPX_WLACZONY == 'tak' ) {

            $zapytaniePrzesylki = "SELECT * FROM orders_shipping WHERE orders_id = '" . (int)$_GET['id_poz'] . "' AND DATE(orders_shipping_date_modified) > DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND orders_shipping_type = 'INPOST' AND orders_shipping_misc = 'SHIPX'";
            $sqlPrzesylki = $db->open_query($zapytaniePrzesylki);

            if ((int)$db->ile_rekordow($sqlPrzesylki) > 0) {

                $apiKurierInpost = new InPostShipX();

                while ($infoPrzesylki = $sqlPrzesylki->fetch_assoc()) {

                    $wynikInPost = $apiKurierInpost->GetRequest('v1/organizations', $apiKurierInpost->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_ORGANIZATION_ID'].'/shipments?id='.$infoPrzesylki['orders_shipping_protocol']);
                    if ( isset($wynikInPost) && isset($wynikInPost->count) ) {
                        foreach ( $wynikInPost->items as $Przesylka ) {

                            if ( $Przesylka->tracking_number != '' ) {

                                $pola = array();
                                $pola = array(
                                              array('orders_shipping_number',$Przesylka->tracking_number),
                                              array('orders_shipping_status',$Przesylka->status),
                                              array('orders_shipping_date_modified',date('Y-m-d G:i:s', FunkcjeWlasnePHP::my_strtotime($Przesylka->updated_at)))
                                        );

                                $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".(int)$infoPrzesylki['orders_shipping_id']."'");
                                unset($pola);
                            }

                        }
                    }

                    if ( $infoPrzesylki['orders_shipping_dispatch_id'] != '' ) {
                        $wynikInPostOdbior = $apiKurierInpost->GetRequest('v1/dispatch_orders/'.$infoPrzesylki['orders_shipping_dispatch_id'] ,'');

                        $pola = array();
                        if ( isset($wynikInPostOdbior) && isset($wynikInPostOdbior->status) ) {

                            $pola = array(
                                          array('orders_dispatch_status',$wynikInPostOdbior->status)
                            );
                            $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".(int)$infoPrzesylki['orders_shipping_id']."'");
                            unset($pola);

                        }

                    }

                }

            }

            $db->close_query($sqlPrzesylki);
            unset($zapytaniePrzesylki);
        }

        if ( INTEGRACJA_APACZKAV2_WLACZONY == 'tak' ) {

            $zapytaniePrzesylki = "SELECT * FROM orders_shipping WHERE orders_id = '" . (int)$_GET['id_poz'] . "' AND DATE(orders_shipping_date_modified) > DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND orders_shipping_type = 'APACZKA'";
            $sqlPrzesylki = $db->open_query($zapytaniePrzesylki);

            if ((int)$db->ile_rekordow($sqlPrzesylki) > 0) {

                $apiKurierApaczka = new ApaczkaApiV2();

                while ($infoPrzesylki = $sqlPrzesylki->fetch_assoc()) {
                    if ( time() - FunkcjeWlasnePHP::my_strtotime($infoPrzesylki['orders_shipping_date_created']) > 120 && $infoPrzesylki['orders_shipping_status'] != 'CANCELLED' ) {
                        $wynikApaczka = $apiKurierApaczka->order($infoPrzesylki['orders_shipping_misc']);
                        $WynikApaczka = json_decode($wynikApaczka);

                        if ( isset($WynikApaczka) && $WynikApaczka->status == '200' ) {

                            $pola = array(
                                    array('orders_shipping_status', $WynikApaczka->response->order->status),
                                    array('orders_shipping_protocol', $WynikApaczka->response->order->pickup_number),
                                    array('orders_dispatch_status', $WynikApaczka->response->order->delivered),
                                    array('orders_shipping_date_modified','now()'),
                            );

                            $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".(int)$infoPrzesylki['orders_shipping_id']."'");

                        }
                    }

                }

            }

            $db->close_query($sqlPrzesylki);
            unset($zapytaniePrzesylki);
        }

        $jezyk = $_SESSION['domyslny_jezyk']['kod'];

        if ( isset($_GET['id_poz']) && $_GET['id_poz'] != '' ) {
          $zamowienie = new Zamowienie((int)$_GET['id_poz']);
        }

        if ( isset($zamowienie->info['id_zamowienia']) && $zamowienie->info['id_zamowienia'] > 0 ) {
        
            $i18n = new Translator($db, $zamowienie->klient['jezyk']);

            unset($_SESSION['waluta_zamowienia'], $_SESSION['waluta_zamowienia_symbol']);
            $_SESSION['waluta_zamowienia'] = $zamowienie->info['waluta'];
            $_SESSION['waluta_zamowienia_symbol'] = $waluty->ZwrocSymbolWalutyKod($zamowienie->info['waluta']);
            
            $JestZamowienie = true;
            
        }
        
    }
    
    // zmiana planowanej daty wysylki
    if (isset($_POST['id_data_wysylki']) && (int)$_POST['id_data_wysylki'] > 0 && isset($_POST['data_wysylki']) && ZAMOWIENIA_PLANOWANA_DATA_WYSYLKI == 'tak') {

        if ( FunkcjeWlasnePHP::my_strtotime($_POST['data_wysylki']) > time() - 86400 ) {
             //
             $pola = array(array('shipping_date',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($_POST['data_wysylki']))));
             //
        } else {
             //
             $pola = array(array('shipping_date',NULL));
             //
        }          
        
        $db->update_query('orders' , $pola, " orders_id  = '" . (int)$_POST['id_data_wysylki'] . "'");	
        unset($pola);        
        
        Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz=' . (int)$_POST['id_data_wysylki']);
        
    }

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        //
        $pola = array(
                array('orders_status',(int)$_POST['status']),
                array('last_modified','now()')
        );
        
        // sprawdzi rodzaj statusu - czy nie jest zamkniete (niezrealizowane) - np anulowane
        $sqls = $db->open_query("SELECT orders_status_type FROM orders_status WHERE orders_status_id = '" . (int)$_POST['status'] . "'");
        $infs = $sqls->fetch_assoc();   
        //
        if ( $infs['orders_status_type'] == 4 ) {
             $pola[] = array('status_update_products',0);
        }
        //
        $db->close_query($sqls);
        unset($infs);         

        $db->update_query('orders' , $pola, " orders_id  = '".(int)$_POST["id"]."'");	
        unset($pola);
    
        $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".(int)$_POST["jezyk"]."' WHERE t.email_var_id = 'EMAIL_ZMIANA_STATUSU_ZAMOWIENIA'";
        $sql = $db->open_query($zapytanie_tresc);
        $tresc = $sql->fetch_assoc();

        define('NUMER_ZAMOWIENIA', (int)$_POST["id"]);
        
        // hash
        $hashKod = '';
        if ( STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak' ) {
             $hashKod = '/zamowienie=' . hash("sha1", $zamowienie->info['id_zamowienia'] . ';' . $zamowienie->info['data_zamowienia'] . ';' . $zamowienie->klient['adres_email'] . ';' . $zamowienie->klient['id']);
        }
        
        if ( $zamowienie->klient['gosc'] == '0' || STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak' ) {
            define('LINK', Seo::link_SEO('zamowienia_szczegoly.php',$zamowienie->info['id_zamowienia'],'zamowienie','',true) . $hashKod); 
        } else {
            $tlumacz = $i18n->tlumacz( array('ZAMOWIENIE_REALIZACJA') );
            define('LINK', $tlumacz['BRAK_DOSTEPU_DO_HISTORII']); 
            unset($tlumacz);
        }
        
        unset($hashKod);        
        
        define('STATUS_ZAMOWIENIA', Sprzedaz::pokazNazweStatusuZamowienia( (int)$_POST['status'], (int)$_POST["jezyk"] ));
        define('DATA_ZAMOWIENIA', date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia'])) );
        
        // nr przesylki
        define('NR_PRZESYLKI', (($zamowienie->dostawy_nr_przesylki != '') ? $zamowienie->dostawy_nr_przesylki : ''));
        
        // link sledzenia
        define('LINK_SLEDZENIA_PRZESYLKI', (($zamowienie->dostawy_link_sledzenia != '') ? $zamowienie->dostawy_link_sledzenia : ''));          
        
        // wartosc zamowienia
        define('WARTOSC_ZAMOWIENIA', $zamowienie->info['wartosc_zamowienia']);  

        // ilosc punktow
        define('ILOSC_PUNKTOW', $zamowienie->ilosc_punktow);  

        // dokument sprzedazy
        define('DOKUMENT_SPRZEDAZY', $zamowienie->info['dokument_zakupu_nazwa']);  
       
        // forma platnosci
        define('FORMA_PLATNOSCI', $zamowienie->info['metoda_platnosci']);  
          
        // forma wysylki
        define('FORMA_WYSYLKI', $zamowienie->info['wysylka_modul']);          

        // tworzy kupon rabatowy
        if ( strpos((string)$_POST['komentarz'], '{KUPON_RABATOWY_PO_ZAMOWIENIU}') > -1 ) {
             //
             $DopuszczalneZnaki = '1234567890QWERTYUIOPASDFGHJKKLZXCVBNM';
             $KodKuponu = KUPON_ZAMOWIENIE_PREFIX;
             //
             for ($i = 0; $i <= (int)KUPON_ZAMOWIENIE_ILOSC_ZNAKOW; $i++) {
                 $KodKuponu .= $DopuszczalneZnaki[rand()%(strlen((string)$DopuszczalneZnaki))];
             }     
             unset($DopuszczalneZnaki);
             
             //            
             $pola = array(
                     array('coupons_status','1'),
                     array('coupons_name',$KodKuponu),
                     array('coupons_description','Kupon za zamówienie nr: ' . (int)$_POST["id"] . ', email: ' . $zamowienie->klient['adres_email']),
                     array('coupons_discount_type',(( KUPON_ZAMOWIENIE_RODZAJ != 'procent' ) ? 'fixed' : 'percent' )),   
                     array('coupons_discount_value',(float)KUPON_ZAMOWIENIE_WARTOSC),
                     array('coupons_min_order',(int)KUPON_ZAMOWIENIE_MIN_WARTOSC_ZAMOWIENIA),
                     array('coupons_min_quantity',(int)KUPON_ZAMOWIENIE_MIN_ILOSC_PRODUKTOW),
                     array('coupons_max_order','0'),
                     array('coupons_max_quantity','0'),                     
                     array('coupons_quantity','1'),
                     array('coupons_specials',(( KUPON_ZAMOWIENIE_PROMOCJE == 'tak' ) ? '1' : '0' )),
                     array('coupons_date_added','now()'),
                     array('coupons_email',''),
                     array('coupons_customers_groups_id',''),
                     array('coupons_date_end',date('Y-m-d', (time() + (int)KUPON_ZAMOWIENIE_WAZNOSC * 86400))),
                     array('coupons_date_start','0000-00-00'),
                    
             );
             //			
             $GLOBALS['db']->insert_query('coupons' , $pola);	
             unset($pola);             
             //
             $_POST['komentarz'] = str_replace('{KUPON_RABATOWY_PO_ZAMOWIENIU}', (string)$KodKuponu, (string)$_POST['komentarz']);
             //
             unset($KodKuponu, $DopuszczalneZnaki);
             //
        }
        
        // lista produktow
        $lista_produktow = array();                 
        foreach ( $zamowienie->produkty as $tmp ) {
          
           $lista_produktow[] = $tmp['nazwa'];
          
        }
        
        define('LISTA_PRODUKTOW', implode('<br />', (array)$lista_produktow));          
        
        unset($lista_produktow);
        
        if ( isset($_POST["dolacz_komentarz"]) ) {
          define('KOMENTARZ', $filtr->process($_POST['komentarz']));
        } else {
          define('KOMENTARZ', '');
        }
        
        $zapytanie_mail = "SELECT customers_email_address, customers_telephone FROM orders WHERE orders_id = '".(int)$_POST["id"]."'";
        $sql_mail = $db->open_query($zapytanie_mail);

        $info_mail = $sql_mail->fetch_assoc();

        if ( isset($_POST['info_mail']) ) {

          $email = new Mailing;

          $powiadomienie_mail = $_POST['info_mail'];

          $tablicaZalacznikow = array();
          if ( (int)$_POST['rodzaj_tresci_mail'] == 0 ) {
            
              if ( $tresc['email_file'] != '' ) {
                  $tablicaZalacznikow = explode(';', (string)$tresc['email_file']);
              }

              $nadawca_email   = Funkcje::parsujZmienne($tresc['sender_email']);
              $nadawca_nazwa   = Funkcje::parsujZmienne($tresc['sender_name']);
              $cc              = Funkcje::parsujZmienne($tresc['dw']);
              $szablon         = $tresc['template_id'];
              $tekst           = $tresc['description'];
              $temat           = strip_tags(Funkcje::parsujZmienne($tresc['email_title']));
              
          } else {

              $nadawca_email   = $filtr->process($_POST['nadawca_email']);
              $nadawca_nazwa   = $filtr->process($_POST['nadawca_nazwa']);
              $cc              = '';
              $szablon         = (int)$_POST['szablon'];
              $tekst           = $filtr->process($_POST['komentarz']);
              $temat           = strip_tags(Funkcje::parsujZmienne($filtr->process($_POST['temat'])));              
            
          }

          $adresat_email   = $info_mail['customers_email_address'];
          $adresat_nazwa   = $filtr->process($_POST['nazwa_klienta']);

          $zalaczniki_tpl  = $tablicaZalacznikow;          
          $jezyk           = (int)$_POST["jezyk"];

          $zalaczniki_file = ((isset($_FILES) && count($_FILES) > 0) ? $_FILES : array());
          
          $zalaczniki_multi = array( 'szablon' => $zalaczniki_tpl, 'pliki' => $zalaczniki_file );

          // usuwa znaczniki dla allegro
          if ( $zamowienie->info['zrodlo'] == '3' ) {
               $tekst = preg_replace('/'.preg_quote('{POMIN_ALLEGRO_START}').'[\s\S]+?'.preg_quote('{POMIN_ALLEGRO_KONIEC}').'/', '', (string)$tekst);
          }

          $tekst = Funkcje::parsujZmienne($tekst);
          $tekst = preg_replace('#(<br */?>\s*)+#i', '<br /><br />', (string)$tekst);

          $email->wyslijEmail($nadawca_email,$nadawca_nazwa,$adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki_multi, false);

        } else {
        
          $powiadomienie_mail = '0';
          
        }

        if ( SMS_WLACZONE == 'tak' && SMS_ZMIANA_STATUSU_ZAMOWIENIA == 'tak' && isset($_POST['info_sms']) ) {

          $adresat   = $info_mail['customers_telephone'];
          $wiadomosc = strip_tags(Funkcje::parsujZmienne($tresc['description_sms']));

          SmsApi::wyslijSms($adresat, $wiadomosc);

          $powiadomienie_sms = $_POST['info_sms'];

        } else {
        
          $powiadomienie_sms = '0';
          
        }
                   
        $db->close_query($sql);
        unset($zapytanie_tresc);        

        $db->close_query($sql_mail);
        unset($zapytanie_mail);
        
        // pliki do maila
        if ( isset($_FILES) && count($_FILES) > 0 ) {
        
            $zalaczone_pliki = array();
            foreach ( array_keys($_FILES['file']['name']) as $plik ) {
                //
                if ( !empty($_FILES['file']['name'][$plik]) ) {
                     $zalaczone_pliki[] = $_FILES['file']['name'][$plik];
                }
                //
            }

            if ( implode(', ', (array)$zalaczone_pliki) != '' ) {
                 $_POST['komentarz'] = $_POST['komentarz'] . ((trim((string)$_POST['komentarz']) != '') ? '<br /><br />' : '') . 'Zostały dołączone pliki: ' . implode(', ', (array)$zalaczone_pliki);
            }
        
            unset($zalaczone_pliki);
            
        }

        //
        $pola = array(
                array('orders_id',(int)$_POST["id"]),
                array('orders_status_id',(int)$_POST['status']),
                array('date_added','now()'),
                array('customer_notified',(int)$powiadomienie_mail),
                array('customer_notified_sms',(int)$powiadomienie_sms),
                array('comments',$filtr->process($_POST['komentarz'])),
                array('admin_id',(int)$_SESSION['userID'])
        );

        $db->insert_query('orders_status_history' , $pola);
        unset($pola);
        
        // zatwierdzenie punktow z zakupy
        if ( SYSTEM_PUNKTOW_STATUS == 'tak' ) {
            //
            if ( isset($_POST['punkty']) && (int)$_POST['punkty'] == 1 ) {
                //
                Klienci::dodajPunktyKlienta( $zamowienie->klient['id'], (int)$_POST['status_punktow'], $zamowienie->info['id_zamowienia'], (int)$_POST['ilosc_punktow'], $_POST['tryb'], (int)$_POST['pkt_id'] );
                //
            }
            //
            // zatwierdzenie punktow z programu partnerskiego
            if ( PP_STATUS == 'tak' ) {
                //
                if ( isset($_POST['punkty_pp']) && (int)$_POST['punkty_pp'] == 1 ) {
                    //
                    Klienci::dodajPunktyKlienta( (int)$_POST['klient_pp'], (int)$_POST['status_punktow_pp'], $zamowienie->info['id_zamowienia'], (int)$_POST['ilosc_punktow_pp'], 1, (int)$_POST['pkt_id_pp']  );
                    //       
                }
                //
            }
            //
        }
        
        // generowanie kodow dla sprzedazy kodow licencyjnych i integracja z automater
        
        $dopuszczalne_statusy = explode(',', (string)SPRZEDAZ_ONLINE_STATUSY_ZAMOWIEN);
        //
        if ( in_array((string)$_POST['status'], $dopuszczalne_statusy) ) {

            foreach ( $zamowienie->produkty as $produkt ) {
                //
                if ( $produkt['kody_elektroniczne_wszystkie'] != '' && empty($produkt['kody_elektroniczne']) && $produkt['id_produktu'] > 0 ) {
                     //
                     // jakie kody sa dostepne
                     $sql_kody = $GLOBALS['db']->open_query("SELECT products_code_shopping FROM products WHERE products_id = '" . (int)$produkt['products_id'] . "'"); 
                     $info_kody = $sql_kody->fetch_assoc();
                     $db->close_query($sql_kody);
                     
                     if ( $info_kody['products_code_shopping'] != '' ) {
                          //
                          $kody_do_zapisania = array();
                          //
                          // znajdzie kod dla produktu
                          $lista_kodow = explode(PHP_EOL, (string)$info_kody['products_code_shopping']);
                          //
                          for ( $x = 0; $x < (int)$produkt['ilosc']; $x++ ) {
                                //
                                if ( isset($lista_kodow[$x]) ) {
                                     //
                                     $kody_do_zapisania[] = $lista_kodow[$x];
                                     unset($lista_kodow[$x]);
                                     //
                                }
                                //
                          }
                          //
                          if ( count($kody_do_zapisania) > 0 ) { 
                              //
                              $pola = array(array('products_code_shopping', implode('<br />', (array)$kody_do_zapisania)));
                              $db->update_query('orders_products' , $pola, 'orders_products_id = ' . $produkt['orders_products_id']);
                              unset($pola);                 
                              //
                              // aktualizuje kody w produkcie
                              $pola = array(array('products_code_shopping', implode(PHP_EOL, (array)$lista_kodow)));
                              $db->update_query('products' , $pola, 'products_id = ' . $produkt['products_id']);
                              unset($pola); 
                              //
                          }
                          //
                     }
                     //
                     unset($info_kody);
                     //
                }
                //
            }
            
            if ( INTEGRACJA_AUTOMATER_WLACZONY == 'tak' && $zamowienie->info['automater_id_cart'] > 0 && $zamowienie->info['automater_wyslane'] == 0 ) {
                 //
                 $idCart = Automater::WyslanieCart( $zamowienie );
                 //
                 if ( (int)$idCart > 0 ) {
                     //
                     $pola = array(array('automater_id_cart_send', 1));
                     $db->update_query('orders' , $pola, 'orders_id = ' . (int)$_POST["id"]);
                     unset($pola);                      
                     //
                 }
                 //
                 unset($idCart);
                 //
            }
            
        }
        unset($dopuszczalne_statusy);

        if ( isset($_POST['powrot']) && (int)$_POST['powrot'] == 1 ) {
            //
            Funkcje::PrzekierowanieURL('zamowienia.php?id_poz='.(int)$_POST["id"]);
            //
          } else {
            //
            Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));
            //
        }

    }
    
    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz_dodatkowe_informacje') {
      
        // dodatkowe informacje w panelu klienta
        if ( isset($_POST['ile_pol']) ) {
            //
            $db->delete_query('orders_account_fields' , " orders_id = '".(int)$_POST["id"]."'");       
            $db->delete_query('orders_account_fields_description' , " orders_id = '".(int)$_POST["id"]."'");       
            //
            $ile_jezykow = Funkcje::TablicaJezykow();
            //
            for ($w = 1, $c = count($ile_jezykow); $w <= $c; $w++) {
                //
                for ($x = 1; $x < 99; $x++ ) {
                    //
                    if ( isset($_POST['tytul_' . (($w * 100) + $x)]) && isset($_POST['wartosc_' . (($w * 100) + $x)]) ) {
                         //
                         if ( !empty($_POST['tytul_' . (($w * 100) + $x)]) && !empty($_POST['wartosc_' . (($w * 100) + $x)]) ) {
                            //
                            $pola = array(
                                    array('orders_id',(int)$_POST["id"]),
                                    array('orders_account_fields_type',$filtr->process($_POST['rodzaj_' . (($w * 100) + $x)])));    
                            //
                            $sql = $db->insert_query('orders_account_fields' , $pola);
                            $id_dodanej_pozycji = $db->last_id_query();
                            unset($pola);                             
                            //
                            $pola = array(
                                    array('orders_account_fields_id',(int)$id_dodanej_pozycji),
                                    array('orders_account_fields_name',$filtr->process($_POST['tytul_' . (($w * 100) + $x)])),
                                    array('orders_account_fields_text',$filtr->process($_POST['wartosc_' . (($w * 100) + $x)])),
                                    array('language_id',(int)$ile_jezykow[$w - 1]['id']),
                                    array('orders_id',(int)$_POST["id"]));    
                            //
                            $sql = $db->insert_query('orders_account_fields_description' , $pola);
                            unset($pola, $id_dodanej_pozycji);                             
                            //                            
                         }
                         //
                    }
                    //
                }
                //
            }
            //
            unset($ile_jezykow);
            //
        }      
        
        Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));
      
    }
    
    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz_wysylke') {
      
        if ( isset($_POST['nr_przesylki']) && $_POST['nr_przesylki'] != '' && isset($_POST['firma_wysylkowa']) && (int)$_POST['firma_wysylkowa'] > 0 ) {
             //
             $zapytanie_wysylka = "select distinct * from delivery_company dc, delivery_company_description dcd where dc.delivery_company_id = dcd.delivery_company_id and dc.delivery_company_id = '" . (int)$_POST['firma_wysylkowa'] . "' and language_id = '" . $_SESSION['domyslny_jezyk']['id'] . "'";
             $sql_wysylka = $db->open_query($zapytanie_wysylka); 
             //
             if ((int)$db->ile_rekordow($sql_wysylka) > 0) {
                 //
                 $info = $sql_wysylka->fetch_assoc();
                 //
                 $pola = array(
                         array('orders_id',(int)$_POST["id"]),
                         array('orders_shipping_type', $info['delivery_company_name']),
                         array('orders_shipping_number', $filtr->process($_POST['nr_przesylki'])),
                         array('orders_shipping_link', str_replace('{NR_PRZESYLKI}', (string)$filtr->process($_POST['nr_przesylki']), (string)$info['delivery_company_link'])),
                         array('orders_shipping_weight', 0),
                         array('orders_parcels_quantity', 0),
                         array('orders_shipping_status', 0),
                         array('orders_shipping_date_created', 'now()'),
                         array('orders_shipping_date_modified', 'now()'),
                         array('orders_shipping_comments', 'reczna'),
                         array('orders_shipping_packages', ''),
                         array('orders_shipping_misc', '')
                 );

                 $db->insert_query('orders_shipping' , $pola);
                 unset($pola, $info);             
                 //
             }
             //
             $db->close_query($sql_wysylka);   
             unset($zapytanie_wysylka);
             //
        }

        Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz=' . (int)$_POST["id" ] . '&zakladka=1');
      
    }    

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

    <?php if ( $JestZamowienie == true && isset($zamowienie->klient['id']) && !empty($zamowienie->klient['id']) ) { ?>
    
        <script type="text/javascript" src="javascript/jquery.jeditable.js"></script>
        
        <script>
        <?php include('zamowienia_szczegoly.js.php'); ?>
        </script>
      
        <div class="cmxform"> 
        
          <div class="poleForm">
          
              <div class="naglowek">Informacje o zamówieniu - zamówienie nr <?php echo $_GET['id_poz']; ?></div>

              <div id="ZakladkiEdycji">
              
                  <div id="LeweZakladki">
                  
                      <a href="javascript:gold_tabs_horiz('0','')" class="a_href_info_zakl" id="zakl_link_0">Podstawowe dane</a>
                      <a href="javascript:gold_tabs_horiz('1','')" class="a_href_info_zakl" id="zakl_link_1">Wysyłki [<?php echo count($zamowienie->dostawy); ?>]</a>
                      
                      <?php if ( PRODUKTY_SZCZEGOLY_ZAMOWIENIA == 'dodatkowa zakładka' ) { ?>
                      <a href="javascript:gold_tabs_horiz('2','')" class="a_href_info_zakl" id="zakl_link_2">Produkty [<?php echo count($zamowienie->produkty); ?>]</a>
                      <?php } ?>
                      
                      <a href="javascript:gold_tabs_horiz('3','')" class="a_href_info_zakl" id="zakl_link_3">Historia zamówienia [<?php echo count($zamowienie->statusy); ?>]</a>                        

                      <?php
                      // oblicza ile jest pozycji w tabeli dodatkowych pol w panelu klienta
                      $zapytanie_informacje = "SELECT orders_account_fields_id FROM orders_account_fields WHERE orders_id = '" . $zamowienie->info['id_zamowienia'] . "'";
                      $sql_informacje = $db->open_query($zapytanie_informacje);
                      $ile_poz = (int)$db->ile_rekordow($sql_informacje);                            
                      ?>
                      <a href="javascript:gold_tabs_horiz('6','')" class="a_href_info_zakl" id="zakl_link_6">Dodatkowe informacje <br /> w Panelu klienta [<?php echo $ile_poz; ?>]</a>
                      <?php
                      $db->close_query($sql_informacje);
                      unset($ile_poz, $zapytanie_informacje, $sql_informacje);                         
                      ?>                      
                      
                      <?php if ( $zamowienie->info['ilosc_pobran_plikow'] > 0 ) { ?>
                      <a href="javascript:gold_tabs_horiz('5','')" class="a_href_info_zakl" id="zakl_link_5">Historia pobrań plików [<?php echo $zamowienie->info['ilosc_pobran_plikow']; ?>]</a>
                      <?php } ?>
                      
                      <?php if ( $zamowienie->sprzedaz_online_kody == true || ($zamowienie->sprzedaz_online_automater == true && INTEGRACJA_AUTOMATER_WLACZONY == 'tak' ) ) { ?>
                      <a href="javascript:gold_tabs_horiz('7','')" class="a_href_info_zakl" id="zakl_link_7">Elektroniczne kody licencyjne</a>
                      <?php } ?>                      
                      
                      <?php
                      // oblicza ile jest reklamacji do zamowienia
                      $zapytanie_reklamacje = "SELECT complaints_id, complaints_customers_orders_id FROM complaints cus WHERE complaints_customers_orders_id = '" . $zamowienie->info['id_zamowienia'] . "'";
                      $sql_reklamacje = $db->open_query($zapytanie_reklamacje);
                      $ile_reklamacji = (int)$db->ile_rekordow($sql_reklamacje);
                      if ( $ile_reklamacji > 0 ) {
                          ?>
                          <a href="javascript:gold_tabs_horiz('8','')" class="a_href_info_zakl" id="zakl_link_4">Reklamacje [<?php echo $ile_reklamacji; ?>]</a>      
                          <?php
                      }
                      $db->close_query($sql_reklamacje);
                      unset($zapytanie_reklamacje, $sql_reklamacje);                         
                      ?>                      

                      <a href="javascript:gold_tabs_horiz('4','')" class="a_href_info_zakl" id="zakl_link_4">Uwagi <?php echo ($zamowienie->klient['uwagi'] != '' || $zamowienie->info['uwagi'] != '' ? '[!]' : ''); ?></a>      
                      
                  </div>
                  
                  <div id="PrawaStrona" class="DodatkoweObramowanie">
                
                      <?php $licznik_zakladek = 0; ?>

                      <?php
                      $toks = 'zamowienie';
                      
                      // informacje ogolne
                      include('zamowienia_szczegoly_zakl_info.php');
                      
                      // wysylki do zamowienia
                      include('zamowienia_szczegoly_zakl_wysylki.php');
                      
                      if ( PRODUKTY_SZCZEGOLY_ZAMOWIENIA == 'dodatkowa zakładka' ) {
                      
                           // produkty zamowienia
                           include('zamowienia_szczegoly_zakl_produkty.php');
                           
                      }
                      
                      // historia zamowienia
                      include('zamowienia_szczegoly_zakl_historia.php');

                      // reklamacje do zamowienia
                      if ( $ile_reklamacji > 0 ) {
                        include('zamowienia_szczegoly_zakl_reklamacje.php');
                      }

                      // historia pobran elektronicznych
                      if ( $zamowienie->info['ilosc_pobran_plikow'] > 0 ) {       
                           include('zamowienia_szczegoly_zakl_online.php');
                      }
                      
                      // kody elektroniczne
                      if ( $zamowienie->sprzedaz_online_kody == true || ($zamowienie->sprzedaz_online_automater == true && INTEGRACJA_AUTOMATER_WLACZONY == 'tak' ) ) {     
                           include('zamowienia_szczegoly_zakl_online_kody.php');
                      }                      
                      
                      // dodatkowe informacje w panelu klienta
                      include('zamowienia_szczegoly_zakl_dodatkowe_informacje.php');                      

                      // uwagi zamowienia
                      include('zamowienia_szczegoly_zakl_uwagi.php');                      
                      
                      unset($toks);

                      $zakladka = '0';
                      if (isset($_GET['zakladka'])) $zakladka = (int)$_GET['zakladka'];
                      ?>
                      <script>
                      gold_tabs_horiz(<?php echo $zakladka; ?>,'0');
                      </script>                         
                  
                  </div>
                    
              </div>

          </div>
          
        </div>
        
        <div class="przyciski_dolne">
             <button type="button" class="przyciskNon" onclick="cofnij('zamowienia','<?php echo Funkcje::Zwroc_Get(array((( empty($zamowienie->klient['id']) ) ? 'id_poz' : 'c'),'typ','zakladka','x','y')); ?>', 'sprzedaz');">Powrót</button>    
        </div>            

        <?php

    } else {

        echo '<div class="poleForm">
                  <div class="naglowek">Edycja danych</div>
                  <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
              </div>';
          
    }
    
    unset($JestZamowienie);
    ?>

    </div>
    <script>
    $('.download').click(function() {
      setTimeout(function() {
        window.location = 'sprzedaz/zamowienia_szczegoly.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&zakladka=1';
      }, 2000);
    });
    </script>
        
    <?php if ( isset($_SESSION['info']) && $_SESSION['info'] != '' ) { ?>
      
    <script>  
    $.colorbox( { html:'<div id="PopUpInfo"><?php echo $_SESSION['info']; ?></div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
    </script>
    
    <?php unset($_SESSION['info']); } ?>
    
    <?php
    include('stopka.inc.php');    
  
} ?>
