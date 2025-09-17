<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_POST['akcja_dolna']) && (int)$_POST['akcja_dolna'] > 0) {
        
        if ( isset($_POST['opcja']) && count($_POST['opcja']) > 0 ) {
        
            // jezeli zmiana statusu zamowienia
            if ( (int)$_POST['akcja_dolna'] == 1 && (int)$_POST['status'] > 0 ) {

                $zapytanie_tresc = "SELECT t.sender_name, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".(int)$_POST["jezyk"]."' WHERE t.email_text_id = '2'";
                $sql = $db->open_query($zapytanie_tresc);

                $tresc = $sql->fetch_assoc();

                define('STATUS_ZAMOWIENIA', Sprzedaz::pokazNazweStatusuZamowienia( (int)$_POST['status'], (int)$_POST["jezyk"] ));

                if ( $tresc['email_file'] != '' ) {
                  $tablicaZalacznikow = explode(';', (string)$tresc['email_file']);
                } else {
                  $tablicaZalacznikow = array();
                }

                foreach ($_POST['opcja'] as $pole) {
                
                    $komentarz = (string)$filtr->process($_POST['komentarz']);
                    $tresc_maila = $tresc['description'];
                    $tresc_tytulu = $tresc['email_title'];
                                    
                    //
                    $pola = array(
                            array('orders_status',(int)$_POST['status']),
                            array('last_modified','now()'));
                            
                    // sprawdzi rodzaj statusu - czy nie jest zamkniete (niezrealizowane) - np anulowane
                    $sqls = $db->open_query("SELECT orders_status_type FROM orders_status WHERE orders_status_id = '" . (int)$_POST['status'] . "'");
                    //
                    if ( (int)$db->ile_rekordow($sqls) > 0) {
                          //
                          $infs = $sqls->fetch_assoc();   
                          //
                          if ( (int)$infs['orders_status_type'] == 4 ) {
                               $pola[] = array('status_update_products',0);
                          }
                          //
                          unset($infs);  
                          //
                    }
                    //
                    $db->close_query($sqls);
                    
                    if ( isset($_POST['zmiana_uwagi']) && (int)$_POST['zmiana_uwagi'] == 1 ) {
                         //
                         if ( isset($_POST['dodanie_uwag']) && (int)$_POST['dodanie_uwag'] == 1  ) {
                              //
                              $tresc_uwag = '';
                              //
                              // sprawdzi poprzednie uwagi zamowienia
                              $sqls = $db->open_query("SELECT orders_adminnotes FROM orders WHERE orders_id = '" . (int)$pole . "'");
                              //
                              if ( (int)$db->ile_rekordow($sqls) > 0) {
                                    //
                                    $infs = $sqls->fetch_assoc();   
                                    //
                                    $tresc_uwag = $infs['orders_adminnotes'];
                                    //
                                    unset($infs);  
                                    //
                              }
                              //
                              $db->close_query($sqls);                              
                              //
                              $pola[] = array('orders_adminnotes', $tresc_uwag . (($tresc_uwag != '') ? "\n\n" : '') . date('d-m-Y', time()) . "\n" . '-----------------------------------' . "\n" . (string)$filtr->process($_POST['tresc_uwag']));
                              //
                         } else {
                              //
                              $pola[] = array('orders_adminnotes', (string)$filtr->process($_POST['tresc_uwag']));
                              //
                         }                              
                         //
                    }
                                   
                    $db->update_query('orders' , $pola, " orders_id  = '" . (int)$pole . "'");	
                    unset($pola);
                                        
                    // dane zamowienia 
                    $zamowienie = new Zamowienie((int)$pole);
                    
                    // podstawia dane pod zmienne w statusie zamowienia
                    
                    // nr przesylki
                    $komentarz = str_replace('{NR_PRZESYLKI}', (($zamowienie->dostawy_nr_przesylki != '') ? $zamowienie->dostawy_nr_przesylki : ''), (string)$komentarz);                    
                    $tresc_maila = str_replace('{NR_PRZESYLKI}', (($zamowienie->dostawy_nr_przesylki != '') ? $zamowienie->dostawy_nr_przesylki : ''), (string)$tresc_maila);
                    $tresc_tytulu = str_replace('{NR_PRZESYLKI}', (($zamowienie->dostawy_nr_przesylki != '') ? $zamowienie->dostawy_nr_przesylki : ''), (string)$tresc_tytulu);                    
                    
                    // link sledzenia
                    $komentarz = str_replace('{LINK_SLEDZENIA_PRZESYLKI}', (($zamowienie->dostawy_link_sledzenia != '') ? $zamowienie->dostawy_link_sledzenia : ''), (string)$komentarz);                     
                    $tresc_maila = str_replace('{LINK_SLEDZENIA_PRZESYLKI}', (($zamowienie->dostawy_link_sledzenia != '') ? $zamowienie->dostawy_link_sledzenia : ''), (string)$tresc_maila); 
                    $tresc_tytulu = str_replace('{LINK_SLEDZENIA_PRZESYLKI}', (($zamowienie->dostawy_link_sledzenia != '') ? $zamowienie->dostawy_link_sledzenia : ''), (string)$tresc_tytulu);                          
                    
                    // wartosc zamowienia
                    $komentarz = str_replace('{WARTOSC_ZAMOWIENIA}', (string)$zamowienie->info['wartosc_zamowienia'], (string)$komentarz);  
                    $tresc_maila = str_replace('{WARTOSC_ZAMOWIENIA}', (string)$zamowienie->info['wartosc_zamowienia'], (string)$tresc_maila);  
                    $tresc_tytulu = str_replace('{WARTOSC_ZAMOWIENIA}', (string)$zamowienie->info['wartosc_zamowienia'], (string)$tresc_tytulu);
                    
                    // nr zamowienia
                    $komentarz = str_replace('{NUMER_ZAMOWIENIA}', (string)$zamowienie->info['id_zamowienia'], (string)$komentarz);                     
                    $tresc_maila = str_replace('{NUMER_ZAMOWIENIA}', (string)$zamowienie->info['id_zamowienia'], (string)$tresc_maila);   
                    $tresc_tytulu = str_replace('{NUMER_ZAMOWIENIA}', (string)$zamowienie->info['id_zamowienia'], (string)$tresc_tytulu);                    

                    // ilosc punktow
                    $komentarz = str_replace('{ILOSC_PUNKTOW}', (string)$zamowienie->ilosc_punktow, (string)$komentarz);  
                    $tresc_maila = str_replace('{ILOSC_PUNKTOW}', (string)$zamowienie->ilosc_punktow, (string)$tresc_maila);  
                    $tresc_tytulu = str_replace('{ILOSC_PUNKTOW}', (string)$zamowienie->ilosc_punktow, (string)$tresc_tytulu);  
                    
                    // dokument sprzedazy
                    $komentarz = str_replace('{DOKUMENT_SPRZEDAZY}', (string)$zamowienie->info['dokument_zakupu_nazwa'], (string)$komentarz);  
                    $tresc_maila = str_replace('{DOKUMENT_SPRZEDAZY}', (string)$zamowienie->info['dokument_zakupu_nazwa'], (string)$tresc_maila);  
                    $tresc_tytulu = str_replace('{DOKUMENT_SPRZEDAZY}', (string)$zamowienie->info['dokument_zakupu_nazwa'], (string)$tresc_tytulu); 
                   
                    // forma platnosci
                    $komentarz = str_replace('{FORMA_PLATNOSCI}', (string)$zamowienie->info['metoda_platnosci'], (string)$komentarz);  
                    $tresc_maila = str_replace('{FORMA_PLATNOSCI}', (string)$zamowienie->info['metoda_platnosci'], (string)$tresc_maila);  
                    $tresc_tytulu = str_replace('{FORMA_PLATNOSCI}', (string)$zamowienie->info['metoda_platnosci'], (string)$tresc_tytulu);  
                      
                    // forma wysylki
                    $komentarz = str_replace('{FORMA_WYSYLKI}', (string)$zamowienie->info['wysylka_modul'], (string)$komentarz);  
                    $tresc_maila = str_replace('{FORMA_WYSYLKI}', (string)$zamowienie->info['wysylka_modul'], (string)$tresc_maila);  
                    $tresc_tytulu = str_replace('{FORMA_WYSYLKI}', (string)$zamowienie->info['wysylka_modul'], (string)$tresc_tytulu); 
                    
                    // status zamowienia
                    $komentarz = str_replace('{STATUS_ZAMOWIENIA}', Sprzedaz::pokazNazweStatusuZamowienia( (int)$_POST['status'], (int)$zamowienie->klient['jezyk'] ), (string)$komentarz);  
                    $tresc_maila = str_replace('{STATUS_ZAMOWIENIA}', Sprzedaz::pokazNazweStatusuZamowienia( (int)$_POST['status'], (int)$zamowienie->klient['jezyk'] ), (string)$tresc_maila);  
                    $tresc_tytulu = str_replace('{STATUS_ZAMOWIENIA}', Sprzedaz::pokazNazweStatusuZamowienia( (int)$_POST['status'], (int)$zamowienie->klient['jezyk'] ), (string)$tresc_tytulu);                     
                    
                    // tworzy kupon rabatowy
                    if ( strpos((string)$komentarz, '{KUPON_RABATOWY_PO_ZAMOWIENIU}') > -1 ) {
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
                                 array('coupons_description','Kupon za zamówienie nr: ' . (int)$pole . ', email: ' . $zamowienie->klient['adres_email']),
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
                         $komentarz = str_replace('{KUPON_RABATOWY_PO_ZAMOWIENIU}', (string)$KodKuponu, (string)$komentarz);
                         //
                         unset($KodKuponu, $DopuszczalneZnaki);
                         //
                    }      

                    // lista produktow
                    $lista_produktow = array();                 
                    foreach ( $zamowienie->produkty as $tmp ) {
                      
                       $lista_produktow[] = $tmp['nazwa'];
                      
                    }
                    
                    $tresc_maila = str_replace('{LISTA_PRODUKTOW}', implode('<br />', (array)$lista_produktow), (string)$tresc_maila);                      
                    
                    unset($lista_produktow);
                    
                    // hash
                    $hashKod = '';
                    if ( STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak' ) {
                         $hashKod = '/zamowienie=' . hash("sha1", $zamowienie->info['id_zamowienia'] . ';' . $zamowienie->info['data_zamowienia'] . ';' . $zamowienie->klient['adres_email'] . ';' . $zamowienie->klient['id']);
                    } 
                    
                    $i18n = new Translator($db, $_SESSION['domyslny_jezyk']['id']);
                    $tlumacz = $i18n->tlumacz( array('ZAMOWIENIE_REALIZACJA') );
                    
                    $link_elektroniczny = '<a style="text-decoration:underline;word-break:break-word" href="' . ADRES_URL_SKLEPU . '/' . $zamowienie->sprzedaz_online_link . $hashKod . '">' . $GLOBALS['tlumacz']['POBRANIE_PLIKOW_ZAMOWIENIA_LINK'] . '</a>';
                    
                    // link plikow elektronicznych
                    $komentarz = str_replace('{LINK_PLIKOW_ELEKTRONICZNYCH}', $link_elektroniczny, (string)$komentarz);  
                    $tresc_maila = str_replace('{LINK_PLIKOW_ELEKTRONICZNYCH}', $link_elektroniczny, (string)$tresc_maila);  
                    $tresc_tytulu = str_replace('{LINK_PLIKOW_ELEKTRONICZNYCH}', $link_elektroniczny, (string)$tresc_tytulu);
                    
                    unset($link_elektroniczny, $tlumacz);     
                    
                    // generowanie kodow dla sprzedazy kodow licencyjnych
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
                                 $db->update_query('orders' , $pola, 'orders_id = ' . (int)$pole);
                                 unset($pola);                      
                                 //
                             }
                             //
                             unset($idCart);
                             //
                        }
            
                    }
                    unset($dopuszczalne_statusy);                  

                    if ( isset($_POST['info_mail']) ) {

                        $powiadomienie_mail = $_POST['info_mail'];

                        $nadawca_email   = Funkcje::parsujZmienne($tresc['sender_email']);
                        $nadawca_nazwa   = Funkcje::parsujZmienne($tresc['sender_name']);
                        $cc              = Funkcje::parsujZmienne($tresc['dw']);

                        $adresat_email   = $zamowienie->klient['adres_email'];
                        $adresat_nazwa   = $zamowienie->klient['nazwa'];

                        $temat           = strip_tags(str_replace('{NUMER_ZAMOWIENIA}', (string)$pole, (string)$tresc_tytulu));

                        $tekst           = str_replace('{NUMER_ZAMOWIENIA}', (string)$pole, (string)$tresc_maila);
                        $tekst           = str_replace('{DATA_ZAMOWIENIA}', date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia'])), (string)$tekst);
                        
                        if ( $zamowienie->klient['gosc'] == '0' || STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak' ) {
                            $tekst       = str_replace('{LINK}', Seo::link_SEO('zamowienia_szczegoly.php',(int)$pole,'zamowienie','',true) . $hashKod, (string)$tekst); 
                        } else {
                            $i18n = new Translator($db, $_SESSION['domyslny_jezyk']['id']);
                            $tlumacz = $i18n->tlumacz( array('ZAMOWIENIE_REALIZACJA') );
                            $tekst       = str_replace('{LINK}', (string)$tlumacz['BRAK_DOSTEPU_DO_HISTORII'], (string)$tekst);
                            unset($tlumacz);
                        }

                        if ( isset($_POST["dolacz_komentarz"]) ) {
                            $tekst = str_replace('{KOMENTARZ}', $komentarz, (string)$tekst);
                        } else {
                            $tekst = str_replace('{KOMENTARZ}', '', (string)$tekst);
                        }                        
                        
                        $zalaczniki      = $tablicaZalacznikow;
                        $szablon         = $tresc['template_id'];
                        $jezyk           = (int)$_POST["jezyk"];

                        // usuwa znaczniki dla allegro
                        if ( $zamowienie->info['zrodlo'] == '3' ) {
                             $tekst = preg_replace('/'.preg_quote('{POMIN_ALLEGRO_START}').'[\s\S]+?'.preg_quote('{POMIN_ALLEGRO_KONIEC}').'/', '', (string)$tekst);
                        }
          
                        $tekst = Funkcje::parsujZmienne($tekst);
                        $tekst = preg_replace('#(<br */?>\s*)+#i', '<br /><br />', (string)$tekst);

                        $email = new Mailing;

                        $email->wyslijEmail($nadawca_email,$nadawca_nazwa,$adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki);
                        
                        //unset($nadawca_email,$nadawca_nazwa,$adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki);
                    
                    } else {
                    
                        $powiadomienie_mail = '0';
                      
                    }
                    
                    if ( SMS_WLACZONE == 'tak' && SMS_ZMIANA_STATUSU_ZAMOWIENIA == 'tak' && isset($_POST['info_sms']) ) {

                        if ( Klienci::CzyNumerGSM($zamowienie->klient['telefon']) ) {
                        
                            $adresat   = $zamowienie->klient['telefon'];

                            $tekst = str_replace('{NR_PRZESYLKI}', ((isset($zamowienie->dostawy_nr_przesylki) && $zamowienie->dostawy_nr_przesylki != '') ? $zamowienie->dostawy_nr_przesylki : ''), (string)$tresc['description_sms']);
                            $tekst = str_replace('{NUMER_ZAMOWIENIA}', (string)$pole, (string)$tekst);

                            if ( $zamowienie->klient['gosc'] == '0' || STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak' ) {
                                $tekst       = str_replace('{LINK}', Seo::link_SEO('zamowienia_szczegoly.php',(int)$pole,'zamowienie','',true) . $hashKod, (string)$tekst); 
                            } else {
                                $tekst       = str_replace('{LINK}', '', (string)$tekst);
                            }
                            $tekst = strip_tags(Funkcje::parsujZmienne($tekst));

                            SmsApi::wyslijSms($adresat, $tekst);

                            $powiadomienie_sms = $_POST['info_sms'];
                            unset($adresat, $tekst);

                        } else {
                        
                            $powiadomienie_sms = '0';
                          
                        }
                      
                    } else {
                    
                      $powiadomienie_sms = '0';
                      
                    }
                    unset($hashKod);   

                    //
                    $pola = array(
                            array('orders_id ',(int)$pole),
                            array('orders_status_id',(int)$_POST['status']),
                            array('date_added','now()'),
                            array('customer_notified ',(int)$powiadomienie_mail),
                            array('customer_notified_sms',(int)$powiadomienie_sms),
                            array('comments',$filtr->process($komentarz)),
                            array('admin_id',(int)$_SESSION['userID'])
                    );

                    $db->insert_query('orders_status_history' , $pola);
                    unset($pola);
                    
                    // zatwierdzenie punktow z zakupy
                    if ( SYSTEM_PUNKTOW_STATUS == 'tak' ) {
                        //
                        if ( isset($_POST['zatwierdz_punkty']) && (int)$_POST['zatwierdz_punkty'] == 1 ) {
                            //
                            if ( $zamowienie->punkty_id > 0 && $zamowienie->ilosc_punktow_dodania > 0 ) {
                                //                        
                                Klienci::dodajPunktyKlienta( $zamowienie->klient['id'], '2', (int)$pole, $zamowienie->ilosc_punktow, 1, $zamowienie->punkty_id );
                                //
                            }
                            //
                        }
                    }           

                    unset($zamowienie, $komentarz, $tresc_maila, $tresc_tytulu);

                }   

            }
            
            // jezeli generowanie zamowien pdf
            if ( (int)$_POST['akcja_dolna'] == 2 ) {
            
                require_once('../tcpdf/config/lang/pol.php');
                require_once('../tcpdf/tcpdf.php');            
                
                $i18n = new Translator($db, $_SESSION['domyslny_jezyk']['id']);
                $tlumacz = $i18n->tlumacz( array('WYGLAD', 'KLIENCI', 'KLIENCI_PANEL', 'PRODUKT', 'ZAMOWIENIE_REALIZACJA', 'KOSZYK') );

                class MYPDF extends TCPDF {

                    public function Footer() {
                      global $tlumacz;
                        $this->SetY(-15);
                        $this->SetFont('helvetica', 'I', 8);
                        $this->Cell(0, 0, $tlumacz['WYGENEROWANO_W_PROGRAMIE'], 'T', false, 'L', 0, '', 0, false, 'T', 'M');
                        $this->Cell(0, 0, $tlumacz['LISTING_STRONA'].' '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
                    }
                    
                }

                $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

                $pdf->SetCreator('shopGold');
                $pdf->SetAuthor('shopGold');
                $pdf->SetTitle($tlumacz['DRUKUJ_ZAMOWIENIE']);
                $pdf->SetSubject($tlumacz['DRUKUJ_ZAMOWIENIE']);
                $pdf->SetKeywords($tlumacz['DRUKUJ_ZAMOWIENIE']);

                if (PDF_PLIK_NAGLOWKA != '' && file_exists(KATALOG_SKLEPU . KATALOG_ZDJEC . '/'.PDF_PLIK_NAGLOWKA)) {
                    //
                    $plik_naglowka = PDF_PLIK_NAGLOWKA;
                    $szerokosc_pliku_naglowka = PDF_PLIK_NAGLOWKA_SZEROKOSC;
                    //
                } else {
                    //
                    $plik_naglowka = '';
                    $szerokosc_pliku_naglowka = '';
                    //
                }

                $daneFirmy = explode(PHP_EOL, (string)PDF_DANE_FIRMY);
                $pozostaleDaneFirmy = '';
                for ( $y = 1; $y < count($daneFirmy); $y++ ) {  
                    $pozostaleDaneFirmy .= $daneFirmy[$y] . "\n";
                }
                $pdf->SetHeaderData($plik_naglowka, $szerokosc_pliku_naglowka, trim((string)$daneFirmy[0]), $pozostaleDaneFirmy);
                unset($daneFirmy, $pozostaleDaneFirmy);                

                $pdf->SetFont('dejavusans', '', 6);

                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', '6'));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                          
                include('programy/barcode/BarcodeGenerator.php');
                include('programy/barcode/BarcodeGeneratorPNG.php');

                $generatorPNG = new Picqer\Barcode\BarcodeGeneratorPNG();                

                // ---------------------------------------------------------
                
                foreach ($_POST['opcja'] as $pole) {

                    $pdf->AddPage();
                    $pdf->SetFont('dejavusans', '', 8);
                    //
                    $zamowienie = new Zamowienie((int)$pole);
                    //
                    $text = PDFZamowienie::WydrukZamowieniaPDF($generatorPNG);
                    
                    // zamiana https na http
                    $text = str_replace('src="https', 'src="http', (string)$text);                    
                    
                    $pdf->writeHTML($text, true, false, false, false, '');
                    //
                    unset($text, $zamowienie);
                    
                }

                $pdf->Output('zestawienie_zamowien_'.time().'.pdf', 'D');
                
                exit;

            }
            
            // jezeli zestawienie zamowien pdf
            if ( (int)$_POST['akcja_dolna'] == 5 ) {
            
                require_once('../tcpdf/config/lang/pol.php');
                require_once('../tcpdf/tcpdf.php');            
                
                $i18n = new Translator($db, $_SESSION['domyslny_jezyk']['id']);
                $tlumacz = $i18n->tlumacz( array('WYGLAD', 'KLIENCI', 'KLIENCI_PANEL', 'PRODUKT', 'ZAMOWIENIE_REALIZACJA', 'KOSZYK') );

                class MYPDF extends TCPDF {

                    public function Footer() {
                      global $tlumacz;
                        $this->SetY(-15);
                        $this->SetFont('helvetica', 'I', 8);
                        $this->Cell(0, 0, $tlumacz['WYGENEROWANO_W_PROGRAMIE'], 'T', false, 'L', 0, '', 0, false, 'T', 'M');
                        $this->Cell(0, 0, $tlumacz['LISTING_STRONA'].' '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
                    }
                    
                }

                $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

                $pdf->SetCreator('shopGold');
                $pdf->SetAuthor('shopGold');
                $pdf->SetTitle($tlumacz['DRUKUJ_ZAMOWIENIE']);
                $pdf->SetSubject($tlumacz['DRUKUJ_ZAMOWIENIE']);
                $pdf->SetKeywords($tlumacz['DRUKUJ_ZAMOWIENIE']);

                $pdf->SetFont('dejavusans', '', 6);

                // $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', '6'));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                $pdf->SetMargins(PDF_MARGIN_LEFT, 5 , PDF_MARGIN_RIGHT);
                $pdf->SetPrintHeader(false);

                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                          
                include('programy/barcode/BarcodeGenerator.php');
                include('programy/barcode/BarcodeGeneratorPNG.php');

                $generatorPNG = new Picqer\Barcode\BarcodeGeneratorPNG();                

                // ---------------------------------------------------------
                
                $pdf->AddPage();
                $pdf->SetFont('dejavusans', '', 8);
                //        
                $text = '';
                //
                foreach ($_POST['opcja'] as $pole) {

                    $zamowienie = new Zamowienie((int)$pole);
                    $text .= PDFZamowieniaLista::WydrukZamowieniaListaPDF($generatorPNG);
                    unset($zamowienie);
                    
                }

                // zamiana https na http
                $text = str_replace('src="https', 'src="http', (string)$text); 

                $pdf->writeHTML($text, true, false, false, false, '');
                
                $pdf->Output('zestawienie_zamowien_lista_'.time().'.pdf', 'D');
                
                exit;

            }            
            
            // jezeli jest laczenie zamowien
            if ( (int)$_POST['akcja_dolna'] == 3 ) {
                  
                  Funkcje::PrzekierowanieURL('zamowienia_laczenie.php?id=' . base64_encode(implode(',', (array)$_POST['opcja'])));
                  
            }
            
            // jezeli jest pobieranie zamowien csv
            if ( (int)$_POST['akcja_dolna'] == 4 ) {
                  
                  Funkcje::PrzekierowanieURL('zamowienia_pobierz.php?id=' . base64_encode(implode(',', (array)$_POST['opcja'])));
                  
            }            

            // jezeli jest usuwanie zamowien
            if ( (int)$_POST['akcja_dolna'] == 6 && $_POST['usuniecie_zamowien'] == '1' ) {

                foreach ($_POST['opcja'] as $pole) {

                    $db->delete_query('orders' , " orders_id = '".(int)$pole."'");  
                    $db->delete_query('orders_status_history' , " orders_id = '".(int)$pole."'");  
                    $db->delete_query('orders_total' , " orders_id = '".(int)$pole."'");  

                    $db->delete_query('orders_products' , " orders_id = '".(int)$pole."'");  
                    $db->delete_query('orders_products_attributes' , " orders_id = '".(int)$pole."'");  
                    $db->delete_query('orders_to_extra_fields' , " orders_id = '".(int)$pole."'");  
                    $db->delete_query('orders_shipping' , " orders_id = '".(int)$pole."'");  
                    $db->delete_query('orders_file_shopping' , " orders_id = '".(int)$pole."'");  

                    $db->delete_query('invoices' , " orders_id = '".(int)$pole."'");  
                    $db->delete_query('invoices_products' , " orders_id = '".(int)$pole."'");  
                    $db->delete_query('invoices_total' , " orders_id = '".(int)$pole."'");  

                    $pola = array(
                            array('orders_id','0'));
                            
                    $db->update_query('allegro_transactions' , $pola, " orders_id = '".(int)$pole."'");	
                    unset($pola);                
                    
                }
                
            }
            
            // jezeli jest zmiana daty wysylki
            if ( (int)$_POST['akcja_dolna'] == 7 && isset($_POST['nowa_data_wysylki']) && $_POST['nowa_data_wysylki'] != '' ) {

                foreach ($_POST['opcja'] as $pole) {
                  
                    $pola = array(
                            array('shipping_date',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($_POST['nowa_data_wysylki']))));
                            
                    $db->update_query('orders' , $pola, " orders_id = '".(int)$pole."'");	
                    unset($pola); 
                    
                }
                
            }            

            // jezeli zestawienie produktow z zamowien pdf
            if ( (int)$_POST['akcja_dolna'] == 8 || (int)$_POST['akcja_dolna'] == 9 ) {
            
                require_once('../tcpdf/config/lang/pol.php');
                require_once('../tcpdf/tcpdf.php');            
                
                $i18n = new Translator($db, $_SESSION['domyslny_jezyk']['id']);
                $tlumacz = $i18n->tlumacz( array('WYGLAD', 'KLIENCI_PANEL', 'PRODUKT') );

                class MYPDF extends TCPDF {

                    public function Footer() {
                      global $tlumacz;
                        $this->SetY(-15);
                        $this->SetFont('helvetica', 'I', 8);
                        $this->Cell(0, 0, $tlumacz['WYGENEROWANO_W_PROGRAMIE'], 'T', false, 'L', 0, '', 0, false, 'T', 'M');
                        $this->Cell(0, 0, $tlumacz['LISTING_STRONA'].' '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
                    }
                    
                }

                $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

                $pdf->SetCreator('shopGold');
                $pdf->SetAuthor('shopGold');
                $pdf->SetTitle($tlumacz['DRUKUJ_ZAMOWIENIE_PRODUKTY']);
                $pdf->SetSubject($tlumacz['DRUKUJ_ZAMOWIENIE_PRODUKTY']);
                $pdf->SetKeywords($tlumacz['DRUKUJ_ZAMOWIENIE_PRODUKTY']);

                $pdf->SetFont('dejavusans', '', 6);

                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                $pdf->SetMargins(PDF_MARGIN_LEFT, 5 , PDF_MARGIN_RIGHT);
                $pdf->SetPrintHeader(false);

                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                          
                // ---------------------------------------------------------
                
                $pdf->AddPage();
                $pdf->SetFont('dejavusans', '', 8);
                //        
                if ( (int)$_POST['akcja_dolna'] == 9 ) {
                     //
                     $text = PDFZamowieniaProdukty::WydrukPDFZamowieniaProduktyPDF($_POST['opcja'], false);
                     //
                } else {
                     //
                     $text = PDFZamowieniaProdukty::WydrukPDFZamowieniaProduktyPDF($_POST['opcja']);
                     //
                }

                $pdf->writeHTML($text, true, false, false, false, '');
                
                $pdf->Output('zestawienie_produktow_zamowien_'.time().'.pdf', 'D');
                
                exit;

            } 
            
        }
     
    }
    
    Funkcje::PrzekierowanieURL('zamowienia.php');
    
}
?>