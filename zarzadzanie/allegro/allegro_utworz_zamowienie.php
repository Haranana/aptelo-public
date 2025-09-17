<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);
if ($prot->wyswietlStrone) {

    if ( Funkcje::SprawdzAktywneAllegro() ) {

        $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );

        $i18n     = new Translator($db, '1');
        $GLOBALS['tlumacz'] = $i18n->tlumacz( array('WYSYLKI','PODSUMOWANIE_ZAMOWIENIA','PLATNOSCI'), null, true );

        if ( isset($_POST['ajax']) && isset($_POST['id_poz']) ) {
             //
             $_GET['id_poz'] = $_POST['id_poz'];
             //
        }

        if ( !isset($_GET['id_poz']) ) {
          $_GET['id_poz'] = 0;
        }

        $zapytanie = "SELECT * FROM allegro_transactions WHERE allegro_transaction_id = '".(int)$_GET['id_poz']."'";

        $sql = $db->open_query($zapytanie);

        if ( $db->ile_rekordow($sql) > 0 ) {
              
            while ($info = $sql->fetch_assoc()) {

                $numer_transakcji = $info['transaction_id'];

                if ( $info['orders_id'] != '' && $info['orders_id'] != '0' ) {
                
                    // jezeli jest ajax
                    if ( isset($_POST['ajax']) ) {
                         //
                         $tekst = '<span>Dla transakcji o numerze: <b>' . $numer_transakcji . '</b> zamówienie było już wygenerowane - nie można tego wykonać powtórnie</span>';
                         //
                      } else {
                         //
                         $tekst = '<div class="ostrzezenie" style="margin:10px">Zamówienie już było wygenerowane - nie można tego wykonać powtórnie</div>';
                         //
                    }
                                  
                } else {

                    //aktualizacja tablic customers* i address_book #############################################
                    $zapytanie_cust = "SELECT customers_id, customers_default_address_id FROM customers WHERE customers_email_address = '".$info['buyer_email_address']."'";
                    $sql_cust = $db->open_query($zapytanie_cust);

                    $ImieNazwisko = explode(' ', (string)$info['shipping_post_buy_form_adr_full_name']);
                    $zarejestrowany_uzytkownik = false;
                    
                    if ( $db->ile_rekordow($sql_cust) > 0 ) {

                        $info_cust =$sql_cust->fetch_assoc();
                        $zarejestrowany_uzytkownik = true;
                        $id_klienta_w_sklepie = $info_cust['customers_id'];

                    } else {

                        $zakodowane_haslo = Funkcje::zakodujHaslo($info['buyer_id']);

                        $pola = array(
                              array('customers_nick',$info['buyer_name']),
                              array('customers_firstname',$ImieNazwisko['0']),
                              array('customers_lastname',$ImieNazwisko['1']),
                              array('customers_email_address',$info['buyer_email_address']),
                              array('customers_telephone',$info['buyer_phone']),
                              array('customers_fax',''),
                              array('customers_password',$zakodowane_haslo),
                              array('customers_newsletter','0'),
                              array('customers_discount','0'),
                              array('customers_groups_id','1'),
                              array('customers_status','0'),
                              array('customers_dod_info','klient z Allegro'),
                              array('customers_guest_account','1'),
                              array('language_id','1')
                        );
                        $db->insert_query('customers' , $pola);
                        $id_klienta_w_sklepie = $db->last_id_query();
                        unset($pola);

                        $pola = array(
                              array('customers_info_id',(int)$id_klienta_w_sklepie),
                              array('customers_info_number_of_logons','0'),
                              array('customers_info_date_account_created','now()'),
                              array('customers_info_date_account_last_modified','now()')
                        );
                        $db->insert_query('customers_info' , $pola);
                        unset($pola);

                        $pola = array(
                              array('customers_id',(int)$id_klienta_w_sklepie),
                              array('entry_company',$info['billing_post_buy_form_adr_company']),
                              array('entry_nip',$info['billing_post_buy_form_adr_nip']),
                              array('entry_pesel',''),
                              array('entry_firstname',$ImieNazwisko['0']),
                              array('entry_lastname',$ImieNazwisko['1']),
                              array('entry_street_address',$info['shipping_post_buy_form_adr_street']),
                              array('entry_postcode',$info['shipping_post_buy_form_adr_postcode']),
                              array('entry_city',$info['shipping_post_buy_form_adr_city']),
                              array('entry_country_id',$AllegroRest->IdKraju($info['shipping_post_buy_form_adr_country'])),
                              array('entry_zone_id','0')
                        );

                        $db->insert_query('address_book' , $pola);
                        $id_dodanej_pozycji = $db->last_id_query();
                        unset($pola);

                        $pola = array(
                              array('customers_default_address_id',$id_dodanej_pozycji)
                        );
                        $db->update_query('customers' , $pola, " customers_id = '".(int)$id_klienta_w_sklepie."'");
                        unset($pola);

                        // dane do newslettera
                        $db->delete_query('subscribers' , " customers_id = '".(int)$id_klienta_w_sklepie."'");   

                        $pola = array(
                                array('customers_id',(int)$id_klienta_w_sklepie),
                                array('subscribers_email_address',$info['buyer_email_address']),
                                array('customers_newsletter','0'),
                                array('date_added','now()')
                        );

                        $db->insert_query('subscribers' , $pola);
                        unset($pola, $id_dodanej_pozycji);
                    }


                    #############################################################################################


                    //aktualizacja tablicy orders
                    $WysylkaInfo = '';
                    $FormaPlatnosci = '';
                    if ( $info['post_buy_form_pay_type'] == 'ONLINE' ) {
                        if ( $info['post_buy_form_pay_provider'] == '' ) {
                            $info['post_buy_form_pay_provider'] = 'Online';
                        }
                        $FormaPlatnosci = $info['post_buy_form_pay_provider'];
                    } elseif ( $info['post_buy_form_pay_type'] == 'CASH_ON_DELIVERY' ) {
                        $FormaPlatnosci = 'Płatność przy odbiorze';
                    } else {
                        if ( $info['post_buy_form_pay_provider'] == '' ) {
                            $info['post_buy_form_pay_provider'] = 'Inna';
                        }
                        $FormaPlatnosci = $info['post_buy_form_pay_provider'];
                    }

                    // waluta z aukcji
                    $waluta_aukcja = (($info['post_buy_form_currency'] != '') ? $info['post_buy_form_currency'] : $_SESSION['domyslna_waluta']['kod']);
                    
                    $zapytanie_tmp = "select currencies_id from currencies where code = '" . $waluta_aukcja . "'";
                    $sql_tmp = $db->open_query($zapytanie_tmp);
                    
                    if ((int)$db->ile_rekordow($sql_tmp) == 0) {                                
                    
                        $waluta_aukcja = $_SESSION['domyslna_waluta']['kod'];
                        
                    }
                    
                    $db->close_query($sql_tmp);
                    unset($zapytanie_tmp); 
                                
                    // aktualizacja tablicy orders
                    $pola_info = array(
                                 array('invoice_dokument',$info['post_buy_form_invoice_option']),
                                 array('customers_id',(int)$id_klienta_w_sklepie),
                                 array('customers_name',$info['shipping_post_buy_form_adr_full_name']),
                                 array('customers_company',$info['shipping_post_buy_form_adr_company']),
                                 array('customers_nip',$info['shipping_post_buy_form_adr_nip']),
                                 array('customers_pesel',''),
                                 array('customers_street_address',$info['shipping_post_buy_form_adr_street']),
                                 array('customers_city',$info['shipping_post_buy_form_adr_city']),
                                 array('customers_postcode',$info['shipping_post_buy_form_adr_postcode']),
                                 array('customers_state', '0'),
                                 array('customers_country',( $info['shipping_post_buy_form_adr_country'] != '' ? $AllegroRest->NazwaKraju($info['shipping_post_buy_form_adr_country']) : 'Polska' ) ),
                                 array('customers_telephone',$info['shipping_post_buy_form_adr_phone']),
                                 array('customers_email_address',$info['buyer_email_address']),
                                 array('customers_dummy_account', ( $zarejestrowany_uzytkownik ? '0' : '1' )),
                                 array('last_modified','now()'),
                                 array('date_purchased',date('Y-m-d H:i:s', $info['post_buy_form_created_date'])),
                                 array('orders_status',$AllegroRest->polaczenie['CONF_ORDERS_STATUS']),
                                 array('orders_source','3'),
                                 array('currency',$waluta_aukcja),
                                 array('currency_value',(float)$_SESSION['domyslna_waluta']['przelicznik']),
                                 array('payment_method',$FormaPlatnosci),
                                 array('payment_info',$info['post_buy_form_pay_provider']),
                                 array('shipping_module',$info['post_buy_form_shipment_id']),
                                 array('shipping_info',$info['post_buy_form_shipment_info']),
                                 array('shipping_destinationcode',$info['post_buy_form_shipping_destinationcode']),
                                 array('reference','http://www.allegro.pl'),
                                 array('allegro_nick',$info['buyer_name'])
                    );                                 

                    $pola_dostawa = array(
                                 array('delivery_name',$info['shipping_post_buy_form_adr_full_name']),
                                 array('delivery_company',$info['shipping_post_buy_form_adr_company']),
                                 array('delivery_nip',$info['shipping_post_buy_form_adr_nip']),
                                 array('delivery_pesel',''),
                                 array('delivery_street_address',$info['shipping_post_buy_form_adr_street']),
                                 array('delivery_city',$info['shipping_post_buy_form_adr_city']),
                                 array('delivery_postcode',$info['shipping_post_buy_form_adr_postcode']),
                                 array('delivery_state', '' ),
                                 array('delivery_country',( $info['shipping_post_buy_form_adr_country'] != '' ? $AllegroRest->NazwaKraju($info['shipping_post_buy_form_adr_country']) : 'Polska' ) ),
                                 array('delivery_telephone',$info['shipping_post_buy_form_adr_phone']),

                    );

                   if ( $info['post_buy_form_invoice_option'] == '0' ) {
                        
                      $pola_platnik = array(
                                      array('billing_name',$info['shipping_post_buy_form_adr_full_name']),
                                      array('billing_company',$info['shipping_post_buy_form_adr_company']),
                                      array('billing_nip',$info['shipping_post_buy_form_adr_nip']),
                                      array('billing_pesel',''),
                                      array('billing_street_address',$info['shipping_post_buy_form_adr_street']),
                                      array('billing_city',$info['shipping_post_buy_form_adr_city']),
                                      array('billing_postcode',$info['shipping_post_buy_form_adr_postcode']),
                                      array('billing_state', '' ),
                                      array('billing_country',( $info['shipping_post_buy_form_adr_country'] != '' ? $AllegroRest->NazwaKraju($info['shipping_post_buy_form_adr_country']) : 'Polska' ) )
                      );
                                          
                   } else {
                        
                      $pola_platnik = array(
                                      array('billing_name',$info['billing_post_buy_form_adr_full_name']),
                                      array('billing_company',$info['billing_post_buy_form_adr_company']),
                                      array('billing_nip',$info['billing_post_buy_form_adr_nip']),
                                      array('billing_pesel',''),
                                      array('billing_street_address',$info['billing_post_buy_form_adr_street']),
                                      array('billing_city',$info['billing_post_buy_form_adr_city']),
                                      array('billing_postcode',$info['billing_post_buy_form_adr_postcode']),
                                      array('billing_state', '' ),
                                      array('billing_country',( $info['billing_post_buy_form_adr_country'] != '' ? $AllegroRest->NazwaKraju($info['billing_post_buy_form_adr_country']) : 'Polska' ) )
                       );
                                          
                   }

                   $pola = Array();
                   $pola = array_merge( $pola_info, $pola_dostawa, $pola_platnik );

                   $db->insert_query('orders' , $pola);
                   $id_dodanej_pozycji_zamowienie = $db->last_id_query();
                   unset($pola);

                   // wyszukanie aukcji do wybranej tranzakcji
                   $zapytanie_aukcje = "
                        SELECT *
                          FROM allegro_auctions_sold
                          WHERE transaction_id = '".$_GET['id_poz']."'";

                   $sql_aukcje = $db->open_query($zapytanie_aukcje);

                   $aukcje_w_tranzakcji = '';

                   $wartosc_produktow_w_tranzakcji = 0;

                   while ($info_aukcje = $sql_aukcje->fetch_assoc()) {

                       $wartosc_produktow_w_tranzakcji += ( $info_aukcje['auction_price'] * $info_aukcje['auction_quantity'] );

                       $aukcje_w_tranzakcji .= $info_aukcje['auction_id'].', ';
                   }

                   $pola = array(
                           array('orders_id',$id_dodanej_pozycji_zamowienie)
                   );
                   $db->update_query('allegro_transactions' , $pola, " allegro_transaction_id = '".$_GET['id_poz']."'");	
                   unset($pola);
                   
                   // konto sprzedajacego
                   $sprzedajacy = '-- brak danych --';
                   //
                   $zapytanieUser = "SELECT allegro_user_login FROM allegro_users where allegro_user_id = " . (int)$info['auction_seller'];
                   $sqlUser = $db->open_query($zapytanieUser);
                                    
                   if ((int)$db->ile_rekordow($sqlUser) > 0) {
                                    
                       $infoUser = $sqlUser->fetch_assoc();
                       $sprzedajacy = $infoUser['allegro_user_login'];

                   }
                  
                    $db->close_query($sqlUser);
                    unset($zapytanieUser, $infoUser);                     

                    // komentarz do zamowienia
                    $komentarz = 'Zamówienie z Allegro; Konto: ' . $sprzedajacy . '; Dotyczy transakcji: ' . $numer_transakcji . '<br />';
                    $komentarz .= 'Aukcje: ' . substr((string)$aukcje_w_tranzakcji, 0, -2);
                    // dodaje id aukcji w zamowieniu
                    $pola = array(array('allegro_id',substr((string)$aukcje_w_tranzakcji, 0, -2)));                    
                    $db->update_query('orders' , $pola, " orders_id = '" . $id_dodanej_pozycji_zamowienie . "'");
                    unset($pola);
                    
                    $komentarz .= "<br />".'Nick kupującego: ' . $info['buyer_name'];
                    
                    if ( isset($info['post_buy_form_msg_to_seller']) && $info['post_buy_form_msg_to_seller'] != '' ) {
                      $komentarz .= "<br />".'Informacja od kupującego: ' . $info['post_buy_form_msg_to_seller'];
                    }
                    
                    $pola = array(
                            array('orders_id ',(int)$id_dodanej_pozycji_zamowienie),
                            array('orders_status_id',(int)$AllegroRest->polaczenie['CONF_ORDERS_STATUS']),
                            array('date_added','now()'),
                            array('customer_notified ','0'),
                            array('customer_notified_sms','0'),
                            array('comments',$komentarz),
                            array('admin_id',(int)$_SESSION['userID']));

                    $db->insert_query('orders_status_history' , $pola);
                    unset($pola, $komentarz);

                    // aktualizacja tablicy orders_total ####################################################
                    $zamowienie = new Zamowienie($id_dodanej_pozycji_zamowienie);
                    $suma = new SumaZamowienia();
                    $tablica_modulow = $suma->przetwarzaj_moduly();

                    foreach ( $tablica_modulow as $podsumowanie ) {

                        $tekst_zamowienia = $waluty->FormatujCene($podsumowanie['wartosc'], false, $waluta_aukcja);

                        $pola = array(
                                array('orders_id',(int)$id_dodanej_pozycji_zamowienie),
                                array('title', $podsumowanie['text'] ),
                                array('text', $tekst_zamowienia ),
                                array('value', (float)$podsumowanie['wartosc'] ),
                                array('prefix', $podsumowanie['prefix'] ),
                                array('class', $podsumowanie['klasa'] ),
                                array('sort_order', (int)$podsumowanie['sortowanie'] ));
                                
                        unset($tekst_zamowienia);
                                
                        if ( isset($podsumowanie['vat_id']) && isset($podsumowanie['vat_stawka']) ) {
                            //
                            $pola[] = array('tax',(float)$podsumowanie['vat_stawka']);
                            $pola[] = array('tax_class_id',(int)$podsumowanie['vat_id']);
                            //
                        } else {
                            $vat_domyslny              = Funkcje::domyslnyPodatekVat();
                            //
                            $pola[] = array('tax',$vat_domyslny['stawka']);
                            $pola[] = array('tax_class_id',$vat_domyslny['id']);
                            //
                        }

                        $db->insert_query('orders_total' , $pola);
                        unset($pola);
                        
                    }
                    unset($_SESSION['koszyk']);

                    //Zapisanie informacji o kosztach przesylki
                    $pola = array(
                            array('title','Koszt wysyłki'),
                            array('text', $waluty->FormatujCene($info['post_buy_form_postage_amount'], false, $waluta_aukcja) ),
                            array('value', (float)$info['post_buy_form_postage_amount'] ),
                    );
                    $db->update_query('orders_total' , $pola, " orders_id = '".(int)$id_dodanej_pozycji_zamowienie."' AND class = 'ot_shipping'");	
                    unset($pola);

                    //Zapisanie informacji o wartosci zamowienia
                    $WartoscTransakcji = $info['post_buy_form_it_amount'] + $info['post_buy_form_postage_amount'];
                    $pola = array(
                            array('text', $waluty->FormatujCene($WartoscTransakcji, false, $waluta_aukcja) ),
                            array('value', (float)$WartoscTransakcji ),
                    );
                    $db->update_query('orders_total' , $pola, " orders_id = '".(int)$id_dodanej_pozycji_zamowienie."' AND class = 'ot_total'");	
                    unset($pola);

                    //Zapisanie informacji o wartosci produktow
                    $pola = array(
                            array('text', $waluty->FormatujCene($info['post_buy_form_it_amount'], false, $waluta_aukcja) ),
                            array('value', (float)$info['post_buy_form_it_amount'] ),
                    );
                    $db->update_query('orders_total' , $pola, " orders_id = '".(int)$id_dodanej_pozycji_zamowienie."' AND class = 'ot_subtotal'");	
                    unset($pola);

                    $zapytanie_produkty = "
                        SELECT aa.auction_quantity, aa.auction_price, aa.auction_product_local_id, a.products_id, a.products_stock_attributes, a.products_name, a.products_image, pd.products_name as nazwa_sklep, p.products_model, p.products_man_code, p.products_control_storage, p.products_pkwiu, p.products_gtu, p.products_ean, p.products_tax_class_id, p.products_quantity, p.products_set, p.products_set_products
                          FROM allegro_auctions_sold aa
                          LEFT JOIN allegro_auctions a ON a.auction_id = aa.auction_id
                          LEFT JOIN products p ON p.products_id = a.products_id
                          LEFT JOIN products_description pd ON pd.products_id = a.products_id AND pd.language_id =  '1'
                          WHERE aa.transaction_id = '".$_GET['id_poz']."'";
                          

                    $sql_produkty = $db->open_query($zapytanie_produkty);

                    $wartosc_vat_razem = 0;

                    while ($info_produkty = $sql_produkty->fetch_assoc()) {

                        $ilosc_produktow    = $info_produkty['auction_quantity'];
                        $wartosc_brutto     = $info_produkty['auction_price'] * $info_produkty['auction_quantity'];
                        $cena_brutto        = $info_produkty['auction_price'];
                        $stawka_vat         = Produkty::PokazStawkeVAT( $info_produkty['products_tax_class_id'] );
                        $kwota_vat          = round(($cena_brutto * ( $stawka_vat / (100 + $stawka_vat ))), 2 );
                        $cena_netto         = $cena_brutto - $kwota_vat;
                        $wartosc_vat_razem += round(($kwota_vat * $info_produkty['auction_quantity']), 2 );
                      
                        $nazwa_produktu = '';
                        if ( $AllegroRest->polaczenie['CONF_INVOICE_PRODUCTS_NAME'] == 'tak' && $info_produkty['nazwa_sklep'] != '' ) {
                            $nazwa_produktu = $info_produkty['products_name'];
                        } else {
                            $nazwa_produktu = $info_produkty['nazwa_sklep'];
                        }

                        $pola = array(
                              array('orders_id',(int)$id_dodanej_pozycji_zamowienie),
                              array('products_id', (int)$info_produkty['products_id'] ),
                              array('products_ean', $info_produkty['products_ean'] ),
                              array('products_model', $info_produkty['products_model'] ),
                              array('products_man_code', $info_produkty['products_man_code'] ),
                              array('products_pkwiu', $info_produkty['products_pkwiu'] ),
                              array('products_gtu', $info_produkty['products_gtu'] ),
                              array('products_name', $nazwa_produktu ),
                              array('products_price', (float)$cena_netto ),
                              array('products_price_tax', (float)$cena_brutto ),
                              array('final_price', (float)$cena_netto ),
                              array('final_price_tax', (float)$cena_brutto ),
                              array('products_tax', (float)$stawka_vat ),
                              array('products_tax_class_id', (int)$info_produkty['products_tax_class_id'] ),
                              array('products_quantity', (float)$ilosc_produktow ),
                              array('products_stock_attributes', str_replace('x', ',', (string)$info_produkty['products_stock_attributes'] )));

                        $db->insert_query('orders_products' , $pola);
                        $id_dodanego_produktu = $db->last_id_query();
                        unset($pola, $nazwa_produktu);

                        //aktualizacja stanu magazynowego produktu
                        if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && ($info_produkty['products_control_storage'] == 1 || $info_produkty['products_control_storage'] == 2) ) {

                            $zaktualizowana_ilosc_produktu = $info_produkty['products_quantity'] - $ilosc_produktow;
                            $pola = array(
                                    array('products_quantity', (float)$zaktualizowana_ilosc_produktu ),
                            );

                            if ( $zaktualizowana_ilosc_produktu <= 0 && MAGAZYN_WYLACZ_PRODUKT == 'tak' ) {
                                $pola[] = array('products_status', '0' );
                            }
                            $db->update_query('products' , $pola, " products_id = '".(int)$info_produkty['products_id']."'");	
                            unset($pola);
                        }

                        if ( $info_produkty['products_stock_attributes'] != '' ) {

                            $tablica_kombinacji_cech = explode('x', (string)$info_produkty['products_stock_attributes']);

                            for ( $t = 0, $c = count($tablica_kombinacji_cech); $t < $c; $t++ ) {

                                $tablica_wartosc_cechy = explode('-', (string)$tablica_kombinacji_cech[$t]);

                                $zapytanie_nazwa_cechy = "SELECT 
                                          * 
                                          FROM products_options
                                          WHERE products_options_id = '" . (int)$tablica_wartosc_cechy['0']. "' 
                                          AND language_id =  '1'";
                                      
                                $sql_nazwa_cechy = $db->open_query($zapytanie_nazwa_cechy);

                                if ((int)$db->ile_rekordow($sql_nazwa_cechy) > 0) {

                                    $info_nazwa_cechy = $sql_nazwa_cechy->fetch_assoc();
                                    $nazwa_cechy = $info_nazwa_cechy['products_options_name'];

                                }

                                $zapytanie_wartosc_cechy = "SELECT 
                                              * 
                                              FROM products_options_values
                                              WHERE products_options_values_id = '" . (int)$tablica_wartosc_cechy['1']. "' 
                                              AND language_id =  '1'";
                                              
                                $sql_wartosc_cechy = $db->open_query($zapytanie_wartosc_cechy);

                                if ((int)$db->ile_rekordow($sql_wartosc_cechy) > 0) {
                                    $info_wartosc_cechy = $sql_wartosc_cechy->fetch_assoc();
                                    $nazwa_wartosci_cechy = $info_wartosc_cechy['products_options_values_name'];
                                }

                                $pola = array(
                                      array('orders_id',(float)$id_dodanej_pozycji_zamowienie),
                                      array('orders_products_id',(float)$id_dodanego_produktu),
                                      array('products_options',$nazwa_cechy),
                                      array('products_options_id',(float)$tablica_wartosc_cechy['0']),
                                      array('products_options_values',$nazwa_wartosci_cechy),
                                      array('products_options_values_id',(float)$tablica_wartosc_cechy['1']),
                                      array('options_values_price','0'),
                                      array('options_values_tax','0'),
                                      array('options_values_price_tax','0'),
                                      array('price_prefix','+'));

                                $db->insert_query('orders_products_attributes' , $pola);
                                unset($pola);

                            }

                            //aktualizacja stanu magazynowego cech produktu
                            $zapytanie_magazyn_cechy = "SELECT products_stock_id, products_stock_quantity, products_stock_attributes, products_stock_ean, products_stock_model FROM products_stock WHERE products_id = '" . (int)$info_produkty['products_id']. "' AND products_stock_attributes = '" . str_replace('x', ',', (string)$info_produkty['products_stock_attributes'] ) . "'"; 

                            $sql_magazyn_cechy = $db->open_query($zapytanie_magazyn_cechy);

                            if ( (int)$db->ile_rekordow($sql_magazyn_cechy) > 0 ) {

                                $info_magazyn_cechy = $sql_magazyn_cechy->fetch_assoc();

                                if ( CECHY_MAGAZYN == 'tak' ) {
                                    //
                                    $ilosc_cech = $info_magazyn_cechy['products_stock_quantity'] - $ilosc_produktow;
                                    $pola = array(
                                            array('products_stock_quantity', (float)$ilosc_cech ));
                                      
                                    $db->update_query('products_stock' , $pola, " products_stock_id = '" . $info_magazyn_cechy['products_stock_id'] . "'");	
                                    unset($pola);                      
                                }

                                if ( $info_magazyn_cechy['products_stock_ean'] != '' ) {
                                    $pola = array(
                                            array('products_ean', $info_magazyn_cechy['products_stock_ean'] ));

                                    $db->update_query('orders_products' , $pola, " orders_products_id = '" . $id_dodanego_produktu . "'");	
                                }
                                if ( $info_magazyn_cechy['products_stock_model'] != '' ) {
                                    $pola = array(
                                            array('products_model', $info_magazyn_cechy['products_stock_model'] ));

                                    $db->update_query('orders_products' , $pola, " orders_products_id = '" . $id_dodanego_produktu . "'");	
                                }

                                $db->close_query($sql_magazyn_cechy);
                                unset($info_magazyn_cechy, $pola, $ilosc_cech);
                            }
                        
                        }

                    }

                    // jezeli jest ajax
                    if ( isset($_POST['ajax']) ) {
                        //
                        $tekst = '<span>Utworzono zamówienie numer: <b>' . $id_dodanej_pozycji_zamowienie . '</b>; na podstawie transakcji numer: <b>' .$info['transaction_id']. '</b></span>';
                           //
                    } else {
                        //
                        $tekst = '<div id="zaimportowano">Utworzono zamówienie numer: ' . $id_dodanej_pozycji_zamowienie . '; na podstawie transakcji numer: ' .$info['transaction_id']. '</div>';
                        //
                    }              
                      
                  
                }
                
            }

          }

        
        if ( isset($_POST['ajax']) ) {
             //
             echo $tekst;
             //
        }    

        if ( !isset($_POST['ajax']) ) {

            // wczytanie naglowka HTML
            include('naglowek.inc.php');
            ?>
            
            <div id="naglowek_cont">Generowanie zamówień</div>
            <div id="cont">
                  
                <div class="poleForm">
                
                  <div class="naglowek">Zamówienie dla transakcji: <?php echo ( $db->ile_rekordow($sql) > 0 ? $numer_transakcji : '' ); ?></div>
                  
                      <?php if ( $db->ile_rekordow($sql) > 0 ) { ?>
                      
                      <div class="pozycja_edytowana">
                      
                          <p>
                            <?php echo $tekst; ?>
                          </p>   
                       
                      </div>
                      
                      <?php } else { ?>
                      
                      <div class="pozycja_edytowana">
                      
                          <div class="ostrzezenie" style="margin:10px">
                            Brak danych do przetworzenia.
                          </div>   
                       
                      </div>
                      
                      <?php } ?>

                      <div class="przyciski_dolne">
                        <?php if ( isset($_GET['aukcja_id']) ) { ?>
                            <button type="button" class="przyciskNon" onclick="cofnij('allegro_sprzedaz_tranzakcja','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','allegro');">Powrót</button> 
                        <?php } else { ?>
                            <button type="button" class="przyciskNon" onclick="cofnij('allegro_sprzedaz','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','allegro');">Powrót</button> 
                        <?php } ?>
                      </div>


                </div>                      

            </div>    
            
            <?php
            include('stopka.inc.php');
              
        }
        
    } else {
      
        Funkcje::PrzekierowanieURL('allegro_sprzedaz.php');
        
    }
    
}
?>