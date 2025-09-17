<?php
chdir('../'); 
//

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

ini_set('display_errors', 0);

// jezeli nie ma id klienta
if ( !isset($_SESSION['customer_id']) || (int)$_SESSION['customer_id'] == 0) {
    Funkcje::PrzekierowanieURL('/');
}

// jezeli koszyk jest pusty
if ( $GLOBALS['koszykKlienta']->KoszykIloscProduktow() <= 0 ) {
    Funkcje::PrzekierowanieURL('/');
}

// jezeli nie ma wybranej metody wysylki
if ( !isset($_SESSION['rodzajDostawy']) ) {
    Funkcje::PrzekierowanieURL('koszyk.html');
}

// jezeli nie ma formy wysylki
if ( (int)$_SESSION['rodzajDostawy']['wysylka_id'] == 0 ) {
    Funkcje::PrzekierowanieURL('koszyk.html');
}

// jezeli nie ma wybranej metody platnosci
if ( !isset($_SESSION['rodzajPlatnosci']) ) {
    Funkcje::PrzekierowanieURL('koszyk.html');
}

// jezeli nie ma adresu wysylki
if ( !isset($_SESSION['adresDostawy']) ) {
    Funkcje::PrzekierowanieURL('koszyk.html');
}

// sprawdzi pkt za produkty
if ( isset($_SESSION['zloz_zamowienie_pkt']) && $_SESSION['zloz_zamowienie_pkt'] == false ) {
    Funkcje::PrzekierowanieURL('koszyk.html'); 
}

// sprawdzi czy nie zmienila sie wartosc koszyka 
if ( !isset($_SESSION['podsumowanieZamowienia']['ot_subtotal']) ) {
    Funkcje::PrzekierowanieURL('koszyk.html'); 
}

$rodzajPlatnosciOpis = '';
if ( isset($_SESSION['rodzajPlatnosci']['opis']) ) {
    $rodzajPlatnosciOpis = $_SESSION['rodzajPlatnosci']['opis'];
}

//
$GLOBALS['koszykKlienta']->PrzeliczKoszyk(false); 
//

if ( isset($_SESSION['podsumowanieZamowienia']['ot_subtotal']) && $_SESSION['podsumowanieZamowienia']['ot_subtotal']['wartosc'] != $GLOBALS['koszykKlienta']->KoszykWartoscProduktow() ) {
    Funkcje::PrzekierowanieURL('koszyk.html'); 
}

// jezeli nie ma formy wysylki sprawdzi czy jest w POST
if ( !isset($_SESSION['rodzajDostawy']['opis']) || (isset($_SESSION['rodzajDostawy']['opis']) && $_SESSION['rodzajDostawy']['opis'] == '') ) {
     if ( isset($_POST['lokalizacjaRuch']) ) {
          $_SESSION['rodzajDostawy']['opis'] = $filtr->process($_POST['lokalizacjaRuch']);
     }
}
if ( !isset($_SESSION['rodzajDostawy']['punktodbioru']) || (isset($_SESSION['rodzajDostawy']['punktodbioru']) && $_SESSION['rodzajDostawy']['punktodbioru'] == '') ) {
     if ( isset($_POST['lokalizacjaRuch']) ) {
          $_SESSION['rodzajDostawy']['punktodbioru'] = $filtr->process($_POST['ShippingDestinationCode']);
     }
}

// sprawdzenie czy jest wpisany kupon rabatowy i czy nadal spelnia warunki przyznania
if ( isset($_SESSION['kuponRabatowy']) ) {
     //
     $kupon = new Kupony($_SESSION['kuponRabatowy']['kupon_kod']);
     //
     $TablicaKuponu = $kupon->kupon;
     //
     if ( count($TablicaKuponu) > 0 ) {
          //
          if ( !$TablicaKuponu['kupon_status'] ) {
               unset($_SESSION['kuponRabatowy']);
               Funkcje::PrzekierowanieURL('koszyk.html'); 
          }
          //
     } else {
          //
          unset($_SESSION['kuponRabatowy']);
          Funkcje::PrzekierowanieURL('koszyk.html'); 
          //
     }
     //
     unset($kupon, $TablicaKuponu);
     //
}  

// sprawdzi czy nie zmienil sie stan magazynowy produktu lub produkt nie jest wylaczony
$stanKoszyka = false;
$stanKoszykaOgolne = false;
foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
    //
    $stanKoszyka = $GLOBALS['koszykKlienta']->SprawdzIloscProduktuMagazyn( $TablicaZawartosci['id'], true );
    //
    if ( $stanKoszyka == true ) {
         $stanKoszykaOgolne = true;
    }
    //
}
if ( $stanKoszykaOgolne == true ) {
    //
    Funkcje::PrzekierowanieURL('koszyk.html');
    //
}
unset($stanKoszyka, $stanKoszykaOgolne);
//

// pobierze dane podstawowe klienta - z domyslnego adresu klienta
$zapytanie_klient = "SELECT c.customers_id, 
                            c.customers_telephone,
                            c.service,
                            a.address_book_id, 
                            a.entry_company, 
                            a.entry_nip,
                            a.entry_pesel,
                            a.entry_firstname, 
                            a.entry_lastname, 
                            a.entry_street_address, 
                            a.entry_postcode, 
                            a.entry_city, 
                            a.entry_country_id, 
                            a.entry_zone_id
                       FROM customers c 
                  LEFT JOIN address_book a ON a.customers_id = c.customers_id
                      WHERE a.address_book_id = c.customers_default_address_id AND c.customers_id = '" . (int)$_SESSION['customer_id'] . "' AND c.customers_status = '1'";

$sql_klient = $GLOBALS['db']->open_query($zapytanie_klient);
$info_klient = $sql_klient->fetch_assoc();

// uzywane w mailu o zamowieniu w tytule maila
$NazwaKlienta = $info_klient['entry_firstname'] . ' ' . $info_klient['entry_lastname'];

// zapisanie informacji do tablicy orders
$pola_info = array(
             array('invoice_dokument',((isset($_SESSION['adresFaktury']['dokument'])) ? $_SESSION['adresFaktury']['dokument'] : '')),
             array('customers_id',(int)$_SESSION['customer_id']),
             array('customers_name',$info_klient['entry_firstname'] . ' ' . $info_klient['entry_lastname']),
             array('customers_company',$info_klient['entry_company']),
             array('customers_nip',$info_klient['entry_nip']),
             array('customers_pesel',$info_klient['entry_pesel']),
             array('customers_street_address',$info_klient['entry_street_address']),
             array('customers_city',$info_klient['entry_city']),
             array('customers_postcode',$info_klient['entry_postcode']),
             array('customers_state', Klient::pokazNazweWojewodztwa($info_klient['entry_zone_id'])),
             array('customers_country',Klient::pokazNazwePanstwa($info_klient['entry_country_id'])),
             array('customers_telephone',$info_klient['customers_telephone']),
             array('customers_email_address',$filtr->process($_SESSION['customer_email'])),
             array('customers_dummy_account',$filtr->process($_SESSION['gosc'])),
             array('last_modified','now()'),
             array('date_purchased','now()'),
             array('orders_status',Funkcje::PokazDomyslnyStatusZamowienia( ((isset($_SESSION['rodzajPlatnosci']['platnosc_klasa'])) ? $_SESSION['rodzajPlatnosci']['platnosc_klasa'] : '') )),
             array('orders_source', ( $_SESSION['gosc'] == '1' ? '2' : '1' )),
             array('currency',$_SESSION['domyslnaWaluta']['kod']),
             array('currency_value',(float)$_SESSION['domyslnaWaluta']['przelicznik'] * (1 + ((float)$_SESSION['domyslnaWaluta']['marza'] / 100))),
             array('payment_method',$filtr->process($_SESSION['rodzajPlatnosci']['platnosc_nazwa'])),
             array('payment_method_class',$filtr->process($_SESSION['rodzajPlatnosci']['platnosc_klasa'])),
             array('payment_info',$rodzajPlatnosciOpis),
             array('shipping_module',$filtr->process($_SESSION['rodzajDostawy']['wysylka_nazwa'])),
             array('shipping_module_id',(int)$_SESSION['rodzajDostawy']['wysylka_id']),
             array('shipping_info',( isset($_SESSION['rodzajDostawy']['opis']) ? $_SESSION['rodzajDostawy']['opis'] : '' )),
             array('shipping_destinationcode',( isset($_SESSION['rodzajDostawy']['punktodbioru']) ? $_SESSION['rodzajDostawy']['punktodbioru'] : '' )),
             array('reference', ((isset($_SESSION['referencja'])) ? $filtr->process($_SESSION['referencja']) : '')),             
             array('tracker_ip', $filtr->process($_SESSION['ippp'])),
             array('device', ((isset($_SESSION['urzedzenie'])) ? $filtr->process($_SESSION['urzedzenie']) : '')),
             array('service',$info_klient['service']));
             
$GLOBALS['db']->close_query($sql_klient);

$mail_opiekuna = '';
if ( $info_klient['service'] > 0 ) {
     //
     $zapytanie_tmp = "select distinct admin_email_address from admin where admin_id = " . (int)$info_klient['service'];
     $sqls = $GLOBALS['db']->open_query($zapytanie_tmp);          
     //
     if ( (int)$GLOBALS['db']->ile_rekordow($sqls) > 0  ) {
          $infs = $sqls->fetch_assoc();
          $mail_opiekuna = $infs['admin_email_address'];
     }
     //
     $GLOBALS['db']->close_query($sqls);
     //
}

// do maila o zamowieniu
define('TELEFON_KUPUJACEGO', $info_klient['customers_telephone']);

unset($zapytanie_klient);             

$pola_dostawa = array(
                array('delivery_name',$filtr->process($_SESSION['adresDostawy']['imie']) . ' ' . $filtr->process($_SESSION['adresDostawy']['nazwisko'])),
                array('delivery_company',$filtr->process($_SESSION['adresDostawy']['firma'])),
                array('delivery_nip',''),
                array('delivery_pesel',''),
                array('delivery_street_address',$filtr->process($_SESSION['adresDostawy']['ulica'])),
                array('delivery_city',$filtr->process($_SESSION['adresDostawy']['miasto'])),
                array('delivery_postcode',$filtr->process($_SESSION['adresDostawy']['kod_pocztowy'])),
                array('delivery_state',Klient::pokazNazweWojewodztwa($_SESSION['adresDostawy']['wojewodztwo'])),
                array('delivery_country',Klient::pokazNazwePanstwa($_SESSION['adresDostawy']['panstwo'])),
                array('delivery_telephone',$_SESSION['adresDostawy']['telefon']));

// jezeli jest wybrany paragon przyjmuje dane klienta
if ( isset($_SESSION['adresFaktury']['dokument']) && $_SESSION['adresFaktury']['dokument'] == '0' ) {
     //
     $dane_nip = '';
     //
     if ( isset($_POST['nip_paragon']) && trim((string)$_POST['nip_paragon']) != '' ) {       
          $dane_nip = $_POST['nip_paragon'];
     }
     //
     $pola_platnik = array(
                     array('billing_name',$info_klient['entry_firstname'] . ' ' . $info_klient['entry_lastname']),
                     array('billing_company',''),
                     array('billing_nip',$filtr->process($dane_nip)),
                     array('billing_pesel',''),
                     array('billing_street_address',$info_klient['entry_street_address']),
                     array('billing_city',$info_klient['entry_city']),
                     array('billing_postcode',$info_klient['entry_postcode']),
                     array('billing_state',Klient::pokazNazweWojewodztwa($_SESSION['adresFaktury']['wojewodztwo'])),
                     array('billing_country',Klient::pokazNazwePanstwa($info_klient['entry_country_id'])));
     //
     unset($dane_nip);
     //
  } else {     
     //
     $pola_platnik = array(
                     array('billing_name',$filtr->process(trim((string)$_SESSION['adresFaktury']['imie'])) . ' ' . $filtr->process(trim((string)$_SESSION['adresFaktury']['nazwisko']))),
                     array('billing_company',$filtr->process($_SESSION['adresFaktury']['firma'])),
                     array('billing_nip',$filtr->process($_SESSION['adresFaktury']['nip'])),
                     array('billing_pesel',(isset($_SESSION['adresFaktury']['pesel']) ? $filtr->process($_SESSION['adresFaktury']['pesel']) : '')),
                     array('billing_street_address',$filtr->process($_SESSION['adresFaktury']['ulica'])),
                     array('billing_city',$filtr->process($_SESSION['adresFaktury']['miasto'])),
                     array('billing_postcode',$filtr->process($_SESSION['adresFaktury']['kod_pocztowy'])),
                     array('billing_state',Klient::pokazNazweWojewodztwa($info_klient['entry_zone_id'])),
                     array('billing_country',Klient::pokazNazwePanstwa($_SESSION['adresFaktury']['panstwo'])));   
     //
}

unset($info_klient);

$pola = array();
$pola = array_merge( $pola_info, $pola_dostawa, $pola_platnik );

// planowany czas wysylki
if ( isset($_POST['planowany_czas_wysylki']) && (int)$_POST['planowany_czas_wysylki'] > 0 ) {
     //
     if ( ZAMOWIENIA_PLANOWANA_DATA_WYSYLKI_SPOSOB != 'ręcznie' ) {
          //
          $startDate = date('Y-m-d', time());
          $endDate = date('Y-m-d', time() + ((int)$_POST['planowany_czas_wysylki'] * 86400));
          $DzienTygodniaWysylki = date('N', strtotime($endDate));
          if ( $DzienTygodniaWysylki == 6 ) {
            $_POST['planowany_czas_wysylki'] = $_POST['planowany_czas_wysylki'] + 2;
          }
          if ( $DzienTygodniaWysylki == 7 ) {
            $_POST['planowany_czas_wysylki'] = $_POST['planowany_czas_wysylki'] + 1;
          }
          $pola[] = array('shipping_date', date('Y-m-d', time() + ((int)$_POST['planowany_czas_wysylki'] * 86400)));
          //
     }
     //
}

// zapisany koszyk
if ( isset($_SESSION['koszyk_id']) ) {
     //
     $pola[] = array('basket_save_id', $_SESSION['koszyk_id']);
     //
}

$GLOBALS['db']->insert_query('orders' , $pola);
$id_dodanej_pozycji_zamowienia = $GLOBALS['db']->last_id_query();
unset($pola);

$PodsumowanieTablica = array();
$PunktyZaZakup = 0;

// wartosc zamowienia dla pkt
$WartoscZamowieniaDlaPkt = 0;

// zapisanie informacji do tablicy orders_total
foreach ( $_SESSION['podsumowanieZamowienia'] as $podsumowanie ) {

    $pola = array(
            array('orders_id',(int)$id_dodanej_pozycji_zamowienia),
            array('title',$podsumowanie['text']),
            array('text',$GLOBALS['waluty']->PokazCeneSymbol(round($podsumowanie['wartosc'], CENY_MIEJSCA_PO_PRZECINKU), $_SESSION['domyslnaWaluta']['kod'])),
            array('value',round($podsumowanie['wartosc'], CENY_MIEJSCA_PO_PRZECINKU)),
            array('prefix',$podsumowanie['prefix']),
            array('class',$podsumowanie['klasa']),
            array('sort_order',(int)$podsumowanie['sortowanie']));
            
    if ( isset($podsumowanie['vat_id']) && isset($podsumowanie['vat_stawka']) ) {
        //
        $pola[] = array('tax',(float)$podsumowanie['vat_stawka']);
        $pola[] = array('tax_class_id',(int)$podsumowanie['vat_id']);
        //
    } else {
        if ( $podsumowanie['klasa'] != 'ot_payment' ) {
            $vat_domyslny = Funkcje::domyslnyPodatekVat();
            $pola[] = array('tax',$vat_domyslny['stawka']);
            $pola[] = array('tax_class_id',$vat_domyslny['id']);
        }
    }
    
    if ( isset($podsumowanie['kod_gtu']) ) {
        //
        $pola[] = array('gtu',$podsumowanie['kod_gtu']);
        //
    }    
    
    // jezeli jest koszt platnosci przyjmie vat z wysylki
    if ( $podsumowanie['klasa'] == 'ot_payment' ) {
        //
        if ( isset($_SESSION['rodzajDostawy']['wysylka_vat_id']) && isset($_SESSION['rodzajDostawy']['wysylka_vat_stawka']) ) {
            //
            $pola[] = array('tax',(float)$_SESSION['rodzajDostawy']['wysylka_vat_stawka']);
            $pola[] = array('tax_class_id',(int)$_SESSION['rodzajDostawy']['wysylka_vat_id']);
            //        
        }
        //
    }

    // naliczanie punktow za zakup jezeli jest zarejestrowany klient
    if ( $_SESSION['gosc'] == '0' && $podsumowanie['klasa'] == 'ot_subtotal' ) {
      
        if ( SYSTEM_PUNKTOW_STATUS == 'tak' && ( PP_STATUS == 'tak' || SYSTEM_PUNKTOW_STATUS_NALICZANIA == 'tak' ) ) {
            //
            $WartoscZamowieniaDlaPkt = $podsumowanie['wartosc'];
            //
            // jezeli jest wykluczenie produktow w promocji
            if ( SYSTEM_PUNKTOW_PROMOCJE == 'nie' ) {
                  //
                  foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
                      //
                      if ( $TablicaZawartosci['promocja'] == 'tak' ) {
                           $WartoscZamowieniaDlaPkt -= ($TablicaZawartosci['cena_brutto'] * $TablicaZawartosci['ilosc']);
                      }
                      //
                  }
                  //
            }
            //
            // sprawdzi czy produkt nie jest wylaczony z naliczania pkt
            foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
                //
                $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ) );
                //
                if ( $Produkt->info['pkt_naliczanie'] == 'nie' ) {                
                     $WartoscZamowieniaDlaPkt -= ($TablicaZawartosci['cena_brutto'] * $TablicaZawartosci['ilosc']);
                }
                //
                unset($Produkt);
                //
            }   
            //
        }            
    
        if ( SYSTEM_PUNKTOW_STATUS == 'tak' && SYSTEM_PUNKTOW_STATUS_NALICZANIA == 'tak' && Punkty::PunktyAktywneDlaKlienta() ) {
            //
            if ( $WartoscZamowieniaDlaPkt > 0 ) {
                 // 
                 if ( isset($_SESSION['punktyKlienta']) ) {
                    //
                    if ( SYSTEM_PUNKTOW_PUNKTY_ZA_PLATNOSCI_PUNKTAMI == 'tak' ) {
                        if ( $podsumowanie['prefix'] == '1' && $podsumowanie['klasa'] != 'ot_shipping' && $podsumowanie['klasa'] != 'ot_payment' ) {
                            $PunktyZaZakup += ceil(($WartoscZamowieniaDlaPkt/$_SESSION['domyslnaWaluta']['przelicznik']) * SYSTEM_PUNKTOW_WARTOSC);
                        } elseif ( $podsumowanie['prefix'] == '0' ) {
                            $PunktyZaZakup -= ceil(($WartoscZamowieniaDlaPkt/$_SESSION['domyslnaWaluta']['przelicznik']) * SYSTEM_PUNKTOW_WARTOSC);
                        }
                    }
                    //
                } else {
                    //
                    if ( $podsumowanie['prefix'] == '1' && $podsumowanie['klasa'] != 'ot_shipping' && $podsumowanie['klasa'] != 'ot_payment' ) {
                        $PunktyZaZakup += ceil(($WartoscZamowieniaDlaPkt/$_SESSION['domyslnaWaluta']['przelicznik']) * SYSTEM_PUNKTOW_WARTOSC);
                    } elseif ( $podsumowanie['prefix'] == '0' ) {
                        $PunktyZaZakup -= ceil(($WartoscZamowieniaDlaPkt/$_SESSION['domyslnaWaluta']['przelicznik']) * SYSTEM_PUNKTOW_WARTOSC);
                    }
                    //
                 }
                 //
            }
            //
        }
        
    }

    $GLOBALS['db']->insert_query('orders_total' , $pola);
    unset($pola);
    
    // generowanie do maila
    $PodsumowanieTablica[$podsumowanie['sortowanie']] = array( 'nazwa'   => $podsumowanie['text'],
                                                               'wartosc' => $podsumowanie['wartosc'],
                                                               'klasa'   => $podsumowanie['klasa'] );

}

// zapisanie informacji o produkcie

// generuje tablice globalne z nazwami cech
Funkcje::TabliceCech();         
//

$CechyProdukty = array();
$IdProduktowZamowienia = array();
$ProduktyEasyProtect = array();

foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
    //

    $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ) );
    
    $Produkt->ProduktStanProduktu();
    $Produkt->ProduktGwarancja();
        
    // sprawdzi czy dodawany produkt nie jest zestawem - jezeli tak rozbije go na elementy
    if ( $Produkt->info['zestaw'] == 'tak' ) {
      
        // aktualizacja stanu magazynowego zestawu
        if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && $Produkt->info['kontrola_magazynu'] > 0 ) {
                    
            $AktualnaIloscProduktu = $Produkt->info['ilosc'];
            $IloscProdukuPoSprzedazy = $AktualnaIloscProduktu - $TablicaZawartosci['ilosc'];

            $pola = array(array('products_quantity',(float)$IloscProdukuPoSprzedazy));
            
            $GLOBALS['db']->update_query('products' , $pola, "products_id = '" . (int)$Produkt->info['id'] . "'");
            unset($pola, $AktualnaIloscProduktu, $IloscProdukuPoSprzedazy);

        }      
        
        // dane o zestawie
        $pola = array(
                array('orders_id',(int)$id_dodanej_pozycji_zamowienia),
                array('products_set_id',(int)$Produkt->info['id']),
                array('products_set_name',$Produkt->info['nazwa']),
                array('products_set_quantity',(float)$TablicaZawartosci['ilosc']));        
                
        $GLOBALS['db']->insert_query('orders_products_set', $pola);
        unset($pola);                
      
        foreach ($Produkt->zestawProdukty as $IdProduktuZestawu => $DaneZestawu ) {
          
            //
            $ProduktZestawu = new Produkt( $IdProduktuZestawu );
            $ProduktZestawu->ProduktCzasWysylki();
            
            // przeliczenie cen zestawu na walute
            $DaneZestawu['cena_netto'] = $GLOBALS['waluty']->PokazCeneBezSymbolu((float)$DaneZestawu['cena_netto'], '' ,true);
            $DaneZestawu['cena_brutto'] = $GLOBALS['waluty']->PokazCeneBezSymbolu((float)$DaneZestawu['cena_brutto'], '' ,true);
            
            $pola = array(
                    array('orders_id',(int)$id_dodanej_pozycji_zamowienia),
                    array('products_id',(int)$IdProduktuZestawu),
                    array('products_name',$ProduktZestawu->info['nazwa']),
                    array('products_set_id',(int)$Produkt->info['id']),
                    array('products_set_name',$Produkt->info['nazwa']),
                    array('products_image',$DaneZestawu['zdjecie']),
                    array('products_model',$ProduktZestawu->info['nr_katalogowy']),
                    array('products_man_code',$ProduktZestawu->info['kod_producenta']),
                    array('products_ean',$ProduktZestawu->info['ean']),
                    array('products_pkwiu',$ProduktZestawu->info['pkwiu']),
                    array('products_gtu',$ProduktZestawu->info['gtu']),
                    array('products_quantity',$TablicaZawartosci['ilosc'] * $DaneZestawu['ilosc']),
                    array('products_shipping_time',$Produkt->czas_wysylki),
                    array('products_warranty',strip_tags((string)$Produkt->gwarancja)),
                    array('products_condition',$Produkt->stan_produktu),
                    array('products_tax',$ProduktZestawu->info['stawka_vat']),
                    array('products_tax_class_id',(int)$ProduktZestawu->info['stawka_vat_id']),
                    array('products_price',(float)$DaneZestawu['cena_netto']),
                    array('products_price_tax',(float)$DaneZestawu['cena_brutto']),
                    array('products_price_points',0),
                    array('products_discount',(float)$Produkt->info['rabat_produktu']),
                    array('final_price',(float)$DaneZestawu['cena_netto']),
                    array('final_price_tax',(float)$DaneZestawu['cena_brutto']),
                    array('products_comments',$filtr->process($TablicaZawartosci['komentarz'])),
                    array('products_text_fields',$filtr->process($TablicaZawartosci['pola_txt'], false, true)),
                    array('products_stock_attributes',''),
                    array('specials_status', (($TablicaZawartosci['promocja'] == 'tak') ? 1 : 0)));

            //
            $GLOBALS['db']->insert_query('orders_products', $pola);
            unset($pola);
            
            if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && $ProduktZestawu->info['kontrola_magazynu'] > 0 ) {
            
                // aktualizacja stanu magazynowego produktu wchodzacego w zestaw
                $AktualnaIloscProduktu = $ProduktZestawu->info['ilosc'];
                $IloscProdukuPoSprzedazy = $AktualnaIloscProduktu - ($TablicaZawartosci['ilosc'] * $DaneZestawu['ilosc']);

                $pola = array(array('products_quantity',(float)$IloscProdukuPoSprzedazy));
                
                $GLOBALS['db']->update_query('products' , $pola, "products_id = '" . (int)$IdProduktuZestawu . "'");
                unset($pola);

            }

            // aktualizacja ilosci sprzedanych produktow
            $zapytanie_sprzedane = "SELECT products_ordered FROM products WHERE products_id = '" . (int)$IdProduktuZestawu . "'";
            $sql_sprzedane = $GLOBALS['db']->open_query($zapytanie_sprzedane);
            $sprzedane = $sql_sprzedane->fetch_assoc();  
            $sprzedane_akt = $sprzedane['products_ordered'] + ($TablicaZawartosci['ilosc'] * $DaneZestawu['ilosc']);

            $pola = array(
                    array('products_ordered',(float)$sprzedane_akt));
                    
            $GLOBALS['db']->update_query('products' , $pola, "products_id = '" . (int)$IdProduktuZestawu . "'");

            $GLOBALS['db']->close_query($sql_sprzedane);         
            unset($zapytanie_sprzedane, $sprzedane, $pola);            
            
            unset($ProduktZestawu);
            //
            
            $IdProduktowZamowienia[] = $IdProduktuZestawu;
          
        }
      
    } else { 
    
        $IdProduktowZamowienia[] = Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] );
        $CechyProduktu = '';

        $AktualnaIloscProduktu = $Produkt->info['ilosc'];
        $IloscProdukuPoSprzedazy = $AktualnaIloscProduktu - $TablicaZawartosci['ilosc'];

        // zapisanie informacji do tablicy orders_products
        $KombinacjaCech = explode('x', (string)$TablicaZawartosci['id']);
        $IdProduktu = $KombinacjaCech['0'];

        array_shift($KombinacjaCech);
        if ( count($KombinacjaCech) > 0 ) {
            $CechyProduktu = implode(",", (array)$KombinacjaCech);
            if ( KOSZYK_SPOSOB_DODAWANIA == 'tak' ) {
                 //
                 $CechyProduktu = substr((string)$CechyProduktu, 0, strpos((string)$CechyProduktu, 'U'));
                 //
            }        
        }

        $pola = array(
                array('orders_id',(int)$id_dodanej_pozycji_zamowienia),
                array('products_id',(int)$IdProduktu),
                array('products_name',$Produkt->info['nazwa']),
                array('products_image',$TablicaZawartosci['zdjecie']),
                array('products_model',$TablicaZawartosci['nr_katalogowy']),
                array('products_man_code',$Produkt->info['kod_producenta']),
                array('products_ean',$TablicaZawartosci['ean']),
                array('products_pkwiu',$Produkt->info['pkwiu']),
                array('products_gtu',$Produkt->info['gtu']),
                array('products_quantity',$TablicaZawartosci['ilosc']),
                array('products_shipping_time',$TablicaZawartosci['czas_wysylki_nazwa']),
                array('products_warranty',strip_tags((string)$Produkt->gwarancja)),
                array('products_condition',$Produkt->stan_produktu),
                array('products_tax',(float)$Produkt->info['stawka_vat']),
                array('products_tax_class_id',(int)$Produkt->info['stawka_vat_id']),
                array('products_price',(float)$TablicaZawartosci['cena_bazowa_netto']),
                array('products_price_tax',(float)$TablicaZawartosci['cena_bazowa_brutto']),
                array('products_price_points',(float)$TablicaZawartosci['cena_punkty']),
                array('products_discount',(float)$Produkt->info['rabat_produktu']),
                array('final_price',(float)$TablicaZawartosci['cena_netto_bez_wariantow']),
                array('final_price_tax',(float)$TablicaZawartosci['cena_brutto_bez_wariantow']),
                array('products_comments',$filtr->process($TablicaZawartosci['komentarz'])),
                array('products_text_fields',$filtr->process($TablicaZawartosci['pola_txt'], false, true)),
                array('products_stock_attributes',$CechyProduktu),
                array('specials_status', (($TablicaZawartosci['promocja'] == 'tak') ? 1 : 0)));

        //
        $GLOBALS['db']->insert_query('orders_products', $pola);
        $id_dodanej_pozycji_produkt = $GLOBALS['db']->last_id_query();
        unset($pola);

        // aktualizacja stanu magazynowego produktu
        if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && $Produkt->info['kontrola_magazynu'] > 0 ) {
        
            $AktualnaIloscProduktu = $Produkt->info['ilosc'];
            $IloscProdukuPoSprzedazy = $AktualnaIloscProduktu - $TablicaZawartosci['ilosc'];

            $pola = array(array('products_quantity',(float)$IloscProdukuPoSprzedazy));
            
            $GLOBALS['db']->update_query('products' , $pola, "products_id = '" . (int)$IdProduktu . "'");
            unset($pola);
            
        }

        // zapisanie informacji do tablicy orders_products_attributes
        if ( count($KombinacjaCech) > 0 ) {

            $TablicaWybranychCech = $Produkt->ProduktCechyTablica($TablicaZawartosci['id']);

            foreach ( $TablicaWybranychCech as $Cecha ) {
            
                $CechyProdukty[ $TablicaZawartosci['id'] ][] = array( 'cecha'   => $Cecha['nazwa_cechy'],
                                                                      'wartosc' => $Cecha['nazwa_wartosci'] );
                                                                      
                $pola = array(
                        array('orders_id',(int)$id_dodanej_pozycji_zamowienia),
                        array('orders_products_id',(int)$id_dodanej_pozycji_produkt),
                        array('products_options',$Cecha['nazwa_cechy']),
                        array('products_options_id',(int)$Cecha['id_cechy']),
                        array('products_options_values',$Cecha['nazwa_wartosci']),
                        array('products_options_values_id',(int)$Cecha['id_wartosci']),
                        array('options_values_price',(($TablicaZawartosci['cena_punkty'] > 0) ? 0 : (float)$Cecha['cena']['netto'])),
                        array('options_values_price_tax',(($TablicaZawartosci['cena_punkty'] > 0) ? 0 : (float)$Cecha['cena']['brutto'])),
                        array('options_values_tax',(($TablicaZawartosci['cena_punkty'] > 0) ? 0 : (float)$Cecha['kwota_vat']['brutto'])),
                        array('price_prefix',$Cecha['prefix']));

                $GLOBALS['db']->insert_query('orders_products_attributes' , $pola);
                unset($pola);

            }
     
            // aktualizacja stanu magazynowego cech produktu
            if ( CECHY_MAGAZYN == 'tak' && $Produkt->info['kontrola_magazynu'] > 0 ) {
            
                $zapytanie = "SELECT products_stock_id, products_stock_quantity 
                              FROM products_stock 
                              WHERE products_id = '".$IdProduktu."' 
                              AND products_stock_attributes = '".$CechyProduktu."'";

                $sql = $GLOBALS['db']->open_query($zapytanie);

                if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0  ) {

                    $cecha = $sql->fetch_assoc();

                    $AktualnaIloscCechProduktu = $cecha['products_stock_quantity'];
                    $IloscCechProdukuPoSprzedazy = $AktualnaIloscCechProduktu - $TablicaZawartosci['ilosc'];
                
                    // jezeli klient kupi wiecej niz stan magazynowy to zrobi wartosc ujemna ilosci
                    // if ( $IloscCechProdukuPoSprzedazy > 0 ) {
                    $pola = array(array('products_stock_quantity',$IloscCechProdukuPoSprzedazy));
                    $GLOBALS['db']->update_query('products_stock' , $pola, "products_stock_id = '" . (int)$cecha['products_stock_id'] . "'");
                    // } else {
                    //    $pola = array(array('products_stock_quantity',0));
                    //    $GLOBALS['db']->update_query('products_stock' , $pola, "products_stock_id = '" . (int)$cecha['products_stock_id'] . "'");
                    // }
                    unset($cecha, $pola, $AktualnaIloscCechProduktu, $IloscCechProdukuPoSprzedazy);

                }

                $GLOBALS['db']->close_query($sql);         
                unset($zapytanie);
                
            }

        }

        // aktualizacja ilosci sprzedanych produktow 
        $zapytanie_sprzedane = "SELECT products_ordered FROM products WHERE products_id = '".(int)$IdProduktu."'";
        $sql_sprzedane = $GLOBALS['db']->open_query($zapytanie_sprzedane);
        $sprzedane = $sql_sprzedane->fetch_assoc();  
        $sprzedane_akt = $sprzedane['products_ordered'] + $TablicaZawartosci['ilosc'];

        $pola = array(
                array('products_ordered',(float)$sprzedane_akt));
        //        
        $GLOBALS['db']->update_query('products' , $pola, "products_id = '" . (int)$IdProduktu . "'");

        $GLOBALS['db']->close_query($sql_sprzedane);         
        unset($zapytanie_sprzedane, $sprzedane, $pola, $id_dodanej_pozycji_produkt);           

    }
    
    // integracja z easyprotect
    if ( isset($TablicaZawartosci['wariant']['ubezpieczenie']) && count($TablicaZawartosci['wariant']['ubezpieczenie']) > 0 ) {
         //
         $ProduktyEasyProtect[(int)$Produkt->info['id']] = $TablicaZawartosci['ilosc'] . ' x ' . $GLOBALS['tlumacz']['EASYPROTECT_NAZWA'] . ' ' . $Produkt->info['nazwa'] . ' - ' . $GLOBALS['tlumacz']['OKRES_OCHRONY'] . ': ' . $TablicaZawartosci['wariant']['ubezpieczenie']['ile_lat'] . ' ' . (($TablicaZawartosci['wariant']['ubezpieczenie']['ile_lat'] == 1) ? $GLOBALS['tlumacz']['ROK'] : $GLOBALS['tlumacz']['LATA']);
         //
         $NrKatEasy = '';
         //
         if ( (int)$TablicaZawartosci['wariant']['ubezpieczenie']['ile_lat'] == 1 ) {
              //
              $NrKatEasy = 'EasyProtect1rok';
              //
         }
         if ( (int)$TablicaZawartosci['wariant']['ubezpieczenie']['ile_lat'] == 2 ) {
              //
              $NrKatEasy = 'EasyProtect2lata';
              //
         }
         if ( (int)$TablicaZawartosci['wariant']['ubezpieczenie']['ile_lat'] == 3 ) {
              //
              $NrKatEasy = 'EasyProtect3lata';
              //
         }         
         //
         $pola = array(
                 array('orders_id',(int)$id_dodanej_pozycji_zamowienia),
                 array('products_id',0),
                 array('products_name',$GLOBALS['tlumacz']['EASYPROTECT_NAZWA'] . ' ' . $Produkt->info['nazwa'] . ' - ' . $GLOBALS['tlumacz']['OKRES_OCHRONY'] . ': ' . $TablicaZawartosci['wariant']['ubezpieczenie']['ile_lat'] . ' ' . (($TablicaZawartosci['wariant']['ubezpieczenie']['ile_lat'] == 1) ? $GLOBALS['tlumacz']['ROK'] : $GLOBALS['tlumacz']['LATA'])),
                 array('products_quantity',$TablicaZawartosci['ilosc']),
                 array('products_gtu',''),
                 array('products_model',$NrKatEasy),
                 array('products_tax',(float)$Produkt->info['stawka_vat']),
                 array('products_tax_class_id',(int)$Produkt->info['stawka_vat_id']),                     
                 array('products_price',(float)$TablicaZawartosci['wariant']['ubezpieczenie']['cena_netto']),
                 array('products_price_tax',(float)$TablicaZawartosci['wariant']['ubezpieczenie']['cena_brutto']),
                 array('final_price',((float)$TablicaZawartosci['wariant']['ubezpieczenie']['cena_netto'])),
                 array('final_price_tax',(float)$TablicaZawartosci['wariant']['ubezpieczenie']['cena_brutto']));

        //
        $GLOBALS['db']->insert_query('orders_products', $pola);             
        //
        unset($NrKatEasy);
        //
    }    

}

$GLOBALS['cache']->UsunCacheProduktow();

// zapisanie informacji do tablicy customers_points
//
if ( SYSTEM_PUNKTOW_STATUS_NALICZANIA == 'tak' && $PunktyZaZakup > 0 && $_SESSION['gosc'] == '0' ) {

    if ( isset($_SESSION['rodzajPlatnosci']['platnosc_punkty']) && $_SESSION['rodzajPlatnosci']['platnosc_punkty'] == 'tak' ) {

        $pola = array(
                array('customers_id',(int)$_SESSION['customer_id']),
                array('orders_id',(int)$id_dodanej_pozycji_zamowienia),
                array('points',(int)$PunktyZaZakup),
                array('date_added','now()'),
                array('points_status','1'),
                array('points_type','SP')
        );
        $GLOBALS['db']->insert_query('customers_points' , $pola);
        unset($pola);
        
    }
    
}

// zapisanie informacji w historii statusow zamowien
//
$pola = array(
        array('orders_id ',(int)$id_dodanej_pozycji_zamowienia),
        array('orders_status_id',Funkcje::PokazDomyslnyStatusZamowienia( ((isset($_SESSION['rodzajPlatnosci']['platnosc_klasa'])) ? $_SESSION['rodzajPlatnosci']['platnosc_klasa'] : '') )),
        array('date_added','now()'),
        array('customer_notified ','1'),
        array('customer_notified_sms','0'),
        array('comments',$filtr->process($_POST['komentarz'])));
        
$GLOBALS['db']->insert_query('orders_status_history' , $pola);
unset($pola);

// zapisanie informacji o wykorzystaniu kuponu rabatowego
//
if ( isset($_SESSION['kuponRabatowy']) ) {
    $pola = array(
            array('coupons_id ',(int)$_SESSION['kuponRabatowy']['kupon_id']),
            array('orders_id',(int)$id_dodanej_pozycji_zamowienia));
            
    $GLOBALS['db']->insert_query('coupons_to_orders' , $pola);
    unset($pola);
    // aktualizacja informacji w bazie kuponow
    //
    $zapytanie = "SELECT coupons_id, coupons_quantity 
                          FROM coupons 
                          WHERE coupons_id = '".$filtr->process($_SESSION['kuponRabatowy']['kupon_id'])."'";

    $sql = $GLOBALS['db']->open_query($zapytanie);
    $kupon = $sql->fetch_assoc();
    $AktualnaIloscKuponow = $kupon['coupons_quantity'];
    $IloscKuponowPoSprzedazy = $AktualnaIloscKuponow - 1;
    
    if ( $IloscKuponowPoSprzedazy > 0 ) {
        $pola = array(array('coupons_quantity',$IloscKuponowPoSprzedazy));
        $GLOBALS['db']->update_query('coupons' , $pola, "coupons_id = '" . (int)$_SESSION['kuponRabatowy']['kupon_id'] . "'");
    } else {
        $pola = array(
                array('coupons_quantity',$IloscKuponowPoSprzedazy),
                array('coupons_status','0'));
        $GLOBALS['db']->update_query('coupons' , $pola, "coupons_id = '" . (int)$_SESSION['kuponRabatowy']['kupon_id'] . "'");
    }
    
    $GLOBALS['db']->close_query($sql);         
    unset($zapytanie, $kupon, $pola);

    $pola = array(
            array('coupons_id',(int)$_SESSION['kuponRabatowy']['kupon_id']),
            array('customers_id',(int)$_SESSION['customer_id']),
            array('orders_id',(int)$id_dodanej_pozycji_zamowienia));
            
    $GLOBALS['db']->insert_query('coupons_to_customers' , $pola);
    unset($pola);

}

if ( $GLOBALS['koszykKlienta']->KoszykWartoscProduktowZaPunkty() > 0 || isset($_SESSION['punktyKlienta']) ) {
  
    $punkty = new Punkty((int)$_SESSION['customer_id']);

    // odjecie punktow wykorzystanych w tym zamowieniu
    //    
    $AktualnaIloscPunktow = $punkty->suma;
    
    $IloscPunktowPoSprzedazy = $AktualnaIloscPunktow;
    
    // jezeli sa punkty za produkty to odejmuje
    if ( $GLOBALS['koszykKlienta']->KoszykWartoscProduktowZaPunkty() > 0 ) {
         //    
         $IloscPunktowPoSprzedazy -= $GLOBALS['koszykKlienta']->KoszykWartoscProduktowZaPunkty();
         //
    }
    
    if ( isset($_SESSION['punktyKlienta']) && (int)$_SESSION['punktyKlienta'] > 0 ) {
         //
         $IloscPunktowPoSprzedazy -= (int)$_SESSION['punktyKlienta']['punkty_ilosc'];
         //
    }
    
    if ( $IloscPunktowPoSprzedazy < 0 ) {
         $IloscPunktowPoSprzedazy = 0;
    }
    
    $pola = array(
            array('customers_shopping_points',(int)$IloscPunktowPoSprzedazy));
            
    $GLOBALS['db']->update_query('customers' , $pola, "customers_id = '" . (int)$_SESSION['customer_id'] . "'");

    // zapisanie informacji do tablicy customers_points
    //
    $pola = array(
            array('customers_id',(int)$_SESSION['customer_id']),
            array('orders_id',(int)$id_dodanej_pozycji_zamowienia),
            array('date_added','now()'),
            array('date_confirm','now()'),
            array('points_status','4'),
            array('points_type','SC'));
            
    $WykorzystanaIloscPkt = 0;
            
    if ( $GLOBALS['koszykKlienta']->KoszykWartoscProduktowZaPunkty() > 0 ) {
         $WykorzystanaIloscPkt += $GLOBALS['koszykKlienta']->KoszykWartoscProduktowZaPunkty();
    }
    if ( isset($_SESSION['punktyKlienta']) && (int)$_SESSION['punktyKlienta'] > 0 ) {
         $WykorzystanaIloscPkt += $_SESSION['punktyKlienta']['punkty_ilosc'];
    }
    
    $pola[] = array('points',$WykorzystanaIloscPkt);
            
    $GLOBALS['db']->insert_query('customers_points' , $pola);
    unset($pola);

    unset($_SESSION['punktyKlienta']);  

}

// usuniecie rekordu z tablicy koszyka
if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) { 
    //
    $GLOBALS['db']->delete_query('customers_basket', "customers_id = '" . (int)$_SESSION['customer_id'] . "'");   
    //
} else {
    //
    $GLOBALS['db']->delete_query('customers_basket', "session_id = '" . (string)session_id() . "'");	   
    //
}

// dodatkowe pola klientow
$DodatkowePolaZamowienia = "SELECT oe.fields_id, oe.fields_input_type 
                                FROM orders_extra_fields oe 
                               WHERE oe.fields_status = '1'";

$sql = $GLOBALS['db']->open_query($DodatkowePolaZamowienia);

if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0  ) {

  while ( $dodatkowePola = $sql->fetch_assoc() ) {
  
    $wartosc = '';
    $pola = array();
    if ( $dodatkowePola['fields_input_type'] != '3' ) {
        //
        if ( isset($_POST['fields_' . $dodatkowePola['fields_id']]) ) {
          //
          $pola = array(
                  array('orders_id',(int)$id_dodanej_pozycji_zamowienia),
                  array('fields_id',(int)$dodatkowePola['fields_id']),
                  array('value',$filtr->process($_POST['fields_' . $dodatkowePola['fields_id']])));
        }
        //                
    } else {
        //
        if ( isset($_POST['fields_' . $dodatkowePola['fields_id']]) ) {
          //
          foreach ($_POST['fields_' . $dodatkowePola['fields_id']] as $key => $value) {
            $wartosc .= $value . "\n";
          }
          $pola = array(
                  array('orders_id',(int)$id_dodanej_pozycji_zamowienia),
                  array('fields_id',(int)$dodatkowePola['fields_id']),
                  array('value',$filtr->process($wartosc)));
        }
        //
    }

    if ( count($pola) > 0 ) {
      $pola[] = array('language_id', (int)$_SESSION['domyslnyJezyk']['id']);
      $GLOBALS['db']->insert_query('orders_to_extra_fields' , $pola);
    }
    unset($pola);
    
  }
  
}

$GLOBALS['db']->close_query($sql);

// zapisanie do sesji informacji o zgodzie na przekazanie danych
if ( KLIENT_ZGODY_OPINIE == 'tak' ) {
     //
     $_SESSION['zgodaNaPrzekazanieDanych'] = ( isset($_POST['zgoda_opinie']) && $_POST['zgoda_opinie'] == '1' ? '1' : '0' );
     //
   } else {
     //
     $_SESSION['zgodaNaPrzekazanieDanych'] = '1';
     //
}
   
if ( (isset($_POST['zgoda_opinie']) && $_POST['zgoda_opinie'] == '1') || KLIENT_ZGODY_OPINIE == 'nie' ) {
     //
     $pola = array(array('customers_reviews','1')); 
     $GLOBALS['db']->update_query('customers' , $pola, "customers_id = '" . (int)$_SESSION['customer_id'] . "'");
     //
}

// wyslanie maila
$jezyk_maila = (int)$_SESSION['domyslnyJezyk']['id'];

$zapytanie_tresc = "SELECT t.email_service, t.email_send, t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".(int)$jezyk_maila."' WHERE t.email_var_id = 'EMAIL_ZAMOWIENIE'";
$sql = $GLOBALS['db']->open_query($zapytanie_tresc);

$tresc = $sql->fetch_assoc();  

if ( $tresc['email_file'] != '' ) {
    $tablicaZalacznikow = explode(';', (string)$tresc['email_file']);
} else {
    $tablicaZalacznikow = array();
}

$nadawca_email   = Funkcje::parsujZmienne($tresc['sender_email']);
$nadawca_nazwa   = Funkcje::parsujZmienne($tresc['sender_name']);

if ( $mail_opiekuna != '' && (int)$tresc['email_service'] == 1 ) {
     $kopia_maila = $mail_opiekuna;
} else {
     $kopia_maila = Funkcje::parsujZmienne($tresc['dw']);
}

$adresat_email   = $filtr->process($_SESSION['customer_email']);
$adresat_nazwa   = $NazwaKlienta;

define('NUMER_ZAMOWIENIA', $id_dodanej_pozycji_zamowienia); 

$temat           = Funkcje::parsujZmienne($tresc['email_title']);
$tekst           = $tresc['description'];
$zalaczniki      = $tablicaZalacznikow;
$szablon         = $tresc['template_id'];
$jezyk           = (int)$jezyk_maila;

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('ZAMOWIENIE_REALIZACJA', 'KOSZYK') ), $GLOBALS['tlumacz'] );

define('IMIE_NAZWISKO_KUPUJACEGO', $NazwaKlienta); 
define('ADRES_EMAIL_ZAMAWIAJACEGO', $filtr->process($_SESSION['customer_email'])); 

define('EMAIL_KUPUJACEGO', $filtr->process($_SESSION['customer_email']));
define('TELEFON_DOSTAWY', $_SESSION['adresDostawy']['telefon']);

/* waga produktow */
$ZawartoscKoszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();
define('WAGA_PRODUKTOW', number_format($ZawartoscKoszyka['waga'], 3, ',', ''));
unset($ZawartoscKoszyka, $NazwaKlienta);

define('DATA_ZAMOWIENIA', date("d-m-Y H:i"));
if ( KOSZYK_WYBOR_DOKUMENTU_SPRZEDAZY == 'tak' ) {
     define('DOKUMENT_SPRZEDAZY', ( (int)$_POST['dokument'] == 1 ? $GLOBALS['tlumacz']['DOKUMENT_SPRZEDAZY_FAKTURA'] : $GLOBALS['tlumacz']['DOKUMENT_SPRZEDAZY_PARAGON'] ));
  } else {
     define('DOKUMENT_SPRZEDAZY', '-');
}
define('FORMA_PLATNOSCI', $filtr->process($_SESSION['rodzajPlatnosci']['platnosc_nazwa'])); 
define('FORMA_WYSYLKI', $filtr->process($_SESSION['rodzajDostawy']['wysylka_nazwa']));
 
$WysylkaInformacja = '';

if ( isset($_SESSION['rodzajDostawy']['opis']) && !empty($_SESSION['rodzajDostawy']['opis']) ) {
    $WysylkaInformacja .= '<br />' . $_SESSION['rodzajDostawy']['opis'];
}

if ( isset($_POST['OpisPunktuOdbioru']) )  {
    if ( $WysylkaInformacja != '' ) {
        $WysylkaInformacja .= '<br />';
    }
    $WysylkaInformacja .= $filtr->process($_POST['OpisPunktuOdbioru']);
}

if ( isset($_SESSION['rodzajDostawy']['informacja']) )  {
    if ( $WysylkaInformacja != '' ) {
        $WysylkaInformacja .= '<br />';
    }
    $WysylkaInformacja .= $_SESSION['rodzajDostawy']['informacja'];
}


define('OPIS_FORMY_WYSYLKI', $WysylkaInformacja );
define('OPIS_FORMY_PLATNOSCI', $rodzajPlatnosciOpis);

$ListaProduktowPozycje = array();
$ListaProduktow = '<table style="width:100%;border-collapse: collapse; border-spacing:0;">';

foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
    //
    $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ) );
       
    // elementy kupowania
    $Produkt->ProduktKupowanie();   
    // stan produktu
    if ( ZAMOWIENIE_POKAZ_STAN_PRODUKTU == 'tak' ) {
         $Produkt->ProduktStanProduktu();
    }  
    // gwarancja produktu
    if ( ZAMOWIENIE_POKAZ_GWARANCJA == 'tak' ) {
         $Produkt->ProduktGwarancja();
    }     
    //    
    // czy produkt ma komentarz
    $KomentarzProduktu = '';
    if ( $TablicaZawartosci['komentarz'] != '' ) {
        //
        $KomentarzProduktu = '<br />' . $GLOBALS['tlumacz']['KOMENTARZ_PRODUKTU'] . ' ' . $TablicaZawartosci['komentarz'];
        //
    }
    // czy sa pola tekstowe
    $PolaTekstowe = '';
    if ( $TablicaZawartosci['pola_txt'] != '' ) {
        //
        $TblPolTxt = Funkcje::serialCiag($TablicaZawartosci['pola_txt']);
        foreach ( $TblPolTxt as $WartoscTxt ) {
            //
            // jezeli pole to plik
            if ( $WartoscTxt['typ'] == 'plik' ) {
                $PolaTekstowe .= '<br />' . $WartoscTxt['nazwa'] . ': <a style="word-break:break-word" href="' . ADRES_URL_SKLEPU . '/inne/wgranie.php?src=' . base64_encode(str_replace('.', ';', (string)$WartoscTxt['tekst'])) . '">' . $GLOBALS['tlumacz']['WGRYWANIE_PLIKU_PLIK'] . '</a>';
              } else {
                $PolaTekstowe .= '<br />' . $WartoscTxt['nazwa'] . ': ' . $WartoscTxt['tekst'];
            }            
        }
        unset($TblPolTxt);
        //
    }  

    // stan produktu
    $StanProduktu = '';
    if ( !empty($Produkt->stan_produktu && ZAMOWIENIE_POKAZ_STAN_PRODUKTU == 'tak') ) {
         $StanProduktu = '<br />' .  $GLOBALS['tlumacz']['STAN_PRODUKTU'] . ': ' . $Produkt->stan_produktu;
    } 
    // gwarancja
    $GwarancjaProduktu = '';
    if ( !empty($Produkt->gwarancja && ZAMOWIENIE_POKAZ_GWARANCJA == 'tak') ) {
         //
         // jezeli gwarancja jest linkiem
         if ( strpos((string)$Produkt->gwarancja, 'href=') > -1 && strpos((string)$Produkt->gwarancja, 'href="htt') === false ) {
             //
             $Produkt->gwarancja = str_replace('href="', 'href="' . ADRES_URL_SKLEPU . '/', (string)$Produkt->gwarancja);
             //
         }
         //
         $GwarancjaProduktu = '<br />' .  $GLOBALS['tlumacz']['GWARANCJA'] . ': ' . str_replace('<a ', '<a style="word-break:break-word" ',(string)$Produkt->gwarancja);
    }      
    //    
    // czas wysylki produktu
    $CzasWysylkiProduktu = '';
    if ( !empty($TablicaZawartosci['czas_wysylki_nazwa']) && ZAMOWIENIE_POKAZ_CZAS_WYSYLKI == 'tak' ) {
         $CzasWysylkiProduktu = '<br />' .  $GLOBALS['tlumacz']['CZAS_WYSYLKI'] . ': ' . $TablicaZawartosci['czas_wysylki_nazwa'];
    }   
    
    $JakieCechy = '';

    if ( $Produkt->info['zestaw'] == 'tak' ) {
      
         $IloscZestawow = $TablicaZawartosci['ilosc'];
      
         foreach ($Produkt->zestawProdukty as $IdProduktuZestawu => $DaneZestawu ) {
           
            $ProduktZestawu = new Produkt( $IdProduktuZestawu );

            // jezeli jest kupowanie na wartosci ulamkowe to sformatuje liczbe
            $IloscProduktu = $IloscZestawow * $DaneZestawu['ilosc'];
            if ( $ProduktZestawu->info['jednostka_miary_typ'] == '0' ) {
                 $IloscProduktu = number_format( $IloscProduktu, 2, '.', '' );
            }
            //  
            
            // producent produktu - pusty dla zestawow
            $ProducentProduktu = ''; 

            $ProduktNazwaTmp = $ProduktZestawu->info['link_z_domena'] . 
                               '<br />(' . $GLOBALS['tlumacz']['PRODUKT_Z_ZESTAWU'] . ' ' . $Produkt->info['link_z_domena'] . ')' .
                               $PolaTekstowe . 
                               $KomentarzProduktu . 
                               $ProducentProduktu .
                               $CzasWysylkiProduktu .
                               $StanProduktu .
                               $GwarancjaProduktu;

            $ListaProduktow .= '<tr>';
            $ListaProduktow .= '<td style="width:50%;padding:5px">' . $ProduktNazwaTmp . '</td>';

            $ListaProduktow .= '<td style="width:15%;padding:5px;text-align:center">' . ((ZAMOWIENIE_POKAZ_NUMER_KATALOGOWY == 'tak') ? $ProduktZestawu->info['nr_katalogowy'] : '') . '</td>';
            //
            // przeliczenie cen zestawu na walute
            $DaneZestawu['cena_brutto'] = $GLOBALS['waluty']->PokazCeneBezSymbolu((float)$DaneZestawu['cena_brutto'], '' ,true);
            //
            $CenaJednostkowaProduktu = $GLOBALS['waluty']->WyswietlFormatCeny($DaneZestawu['cena_brutto'], $_SESSION['domyslnaWaluta']['id'], true, false);
            $ListaProduktow .= '<td style="width:15%;padding:5px;text-align:center">' . $CenaJednostkowaProduktu . '</td>';

            $ListaProduktow .= '<td style="width:5%;padding:5px;text-align:center">' . $IloscProduktu . ' ' . $ProduktZestawu->info['jednostka_miary'] . '</td>';
            
            $WartoscProduktu = $GLOBALS['waluty']->WyswietlFormatCeny($DaneZestawu['cena_brutto'] * $IloscProduktu, $_SESSION['domyslnaWaluta']['id'], true, false);
            $ListaProduktow .= '<td style="width:15%;padding:5px;text-align:center">' . $WartoscProduktu . '</td>';

            $ListaProduktow .= '</tr>'; 
            
            // lista pojedynczych produktow
            $TekstProdukty = substr($tekst, strpos($tekst, '{ZAMOWIONE_PRODUKTY_START}'), strlen($tekst));
            $TekstProdukty = substr($TekstProdukty, 0, strpos($TekstProdukty, '{ZAMOWIONE_PRODUKTY_KONIEC}') + strlen('{ZAMOWIONE_PRODUKTY_KONIEC}'));
            
            $TekstProdukty = str_replace('{ZAMOWIONE_PRODUKTY_START}', '<div style="word-break:break-word">', $TekstProdukty);
            $TekstProdukty = str_replace('{ZAMOWIONE_PRODUKTY_KONIEC}', '</div>', $TekstProdukty);            
            $TekstProdukty = str_replace('{NAZWA_PRODUKTU}', $ProduktNazwaTmp, $TekstProdukty);
            $TekstProdukty = str_replace('{PRODUKT_NR_KATALOGOWY}', (string)$ProduktZestawu->info['nr_katalogowy'], $TekstProdukty);
            $TekstProdukty = str_replace('{PRODUKT_KOD_EAN}', (string)$ProduktZestawu->info['ean'], $TekstProdukty);
            $TekstProdukty = str_replace('{PRODUKT_CENA_JEDNOSTKOWA_BRUTTO}', $CenaJednostkowaProduktu, $TekstProdukty);
            $TekstProdukty = str_replace('{PRODUKT_WARTOSC_BRUTTO}', $WartoscProduktu, $TekstProdukty);
            $TekstProdukty = str_replace('{PRODUKT_KUPIONA_ILOSC}', $IloscProduktu . ' ' . (string)$ProduktZestawu->info['jednostka_miary'], $TekstProdukty);
            
            $ListaProduktowPozycje[] = $TekstProdukty;
            
            unset($ProduktNazwaTmp, $ProduktZestawu, $IloscProduktu, $CenaJednostkowaProduktu, $WartoscProduktu, $TekstProdukty);

         }
         
         unset($IloscZestawow);
         
    } else {

        // jezeli jest kupowanie na wartosci ulamkowe to sformatuje liczbe
        if ( $Produkt->info['jednostka_miary_typ'] == '0' ) {
             $TablicaZawartosci['ilosc'] = number_format( $TablicaZawartosci['ilosc'] , 2, '.', '' );
        }
        //
        
        if ( isset($CechyProdukty[ $TablicaZawartosci['id'] ]) ) {
            //
            foreach ( $CechyProdukty[ $TablicaZawartosci['id'] ] As $CechaProduktu ) {
                //
                $JakieCechy .= '<br />' . $CechaProduktu['cecha'] . ': ' . $CechaProduktu['wartosc'] . '';
                //
            }
            //
        }
  
        // producent produktu
        $ProducentProduktu = '';
        if ( !empty($Produkt->info['nazwa_producenta']) && ZAMOWIENIE_POKAZ_PRODUCENT == 'tak' ) {
             $ProducentProduktu = '<br />' .  $GLOBALS['tlumacz']['PRODUCENT'] . ': ' . $Produkt->info['nazwa_producenta'] . '';
        }
        
        $ProduktNazwaTmp = $Produkt->info['link_z_domena'] . 
                           $JakieCechy . 
                           $PolaTekstowe . 
                           $KomentarzProduktu . 
                           $ProducentProduktu .
                           $CzasWysylkiProduktu .
                           $StanProduktu .
                           $GwarancjaProduktu;

        $ListaProduktow .= '<tr>';
        $ListaProduktow .= '<td style="width:50%;padding:5px">' . $ProduktNazwaTmp . '</td>';
        
        $ListaProduktow .= '<td style="width:15%;padding:5px;text-align:center">' . ((ZAMOWIENIE_POKAZ_NUMER_KATALOGOWY == 'tak') ? $TablicaZawartosci['nr_katalogowy'] : '') . '</td>';
        //
        // cena produktu - czy produkt jest za PUNKTY czy tylko kwotowy
        if ( $TablicaZawartosci['cena_punkty'] > 0 ) {
             //
             $CenaJednostkowaProduktu = $GLOBALS['waluty']->PokazCenePunkty( $TablicaZawartosci['cena_punkty'], $TablicaZawartosci['cena_brutto_bez_wariantow'], false );
             //
          } else {
             //
             $CenaJednostkowaProduktu = $GLOBALS['waluty']->WyswietlFormatCeny($TablicaZawartosci['cena_brutto_bez_wariantow'], $_SESSION['domyslnaWaluta']['id'], true, false);
             //
        }
        $ListaProduktow .= '<td style="width:15%;padding:5px;text-align:center">' . $CenaJednostkowaProduktu . '</td>';
        
        $ListaProduktow .= '<td style="width:5%;padding:5px;text-align:center">' . $TablicaZawartosci['ilosc'] . ' ' . $Produkt->info['jednostka_miary'] . '</td>';
        
        // wartosc produktu - czy produkt jest za PUNKTY czy tylko kwotowy
        if ( $TablicaZawartosci['cena_punkty'] > 0 ) {
             //
             $WartoscProduktu = $GLOBALS['waluty']->PokazCenePunkty( $TablicaZawartosci['cena_punkty'] * $TablicaZawartosci['ilosc'], $TablicaZawartosci['cena_brutto'] * $TablicaZawartosci['ilosc'], false );
             //
          } else {
             //
             $WartoscProduktu = $GLOBALS['waluty']->WyswietlFormatCeny($TablicaZawartosci['cena_brutto_bez_wariantow'] * $TablicaZawartosci['ilosc'], $_SESSION['domyslnaWaluta']['id'], true, false);
             //
        }    
        $ListaProduktow .= '<td style="width:15%;padding:5px;text-align:center">' . $WartoscProduktu . '</td>';
        
        $ListaProduktow .= '</tr>';

        // lista pojedynczych produktow
        $TekstProdukty = substr($tekst, strpos($tekst, '{ZAMOWIONE_PRODUKTY_START}'), strlen($tekst));
        $TekstProdukty = substr($TekstProdukty, 0, strpos($TekstProdukty, '{ZAMOWIONE_PRODUKTY_KONIEC}') + strlen('{ZAMOWIONE_PRODUKTY_KONIEC}'));

        $TekstProdukty = str_replace('{ZAMOWIONE_PRODUKTY_START}', '<div style="word-break:break-word">', $TekstProdukty);
        $TekstProdukty = str_replace('{ZAMOWIONE_PRODUKTY_KONIEC}', '</div>', $TekstProdukty);            
        $TekstProdukty = str_replace('{NAZWA_PRODUKTU}', $ProduktNazwaTmp, $TekstProdukty);
        $TekstProdukty = str_replace('{PRODUKT_NR_KATALOGOWY}', (string)$TablicaZawartosci['nr_katalogowy'], $TekstProdukty);
        $TekstProdukty = str_replace('{PRODUKT_KOD_EAN}', (string)$TablicaZawartosci['ean'], $TekstProdukty);
        $TekstProdukty = str_replace('{PRODUKT_CENA_JEDNOSTKOWA_BRUTTO}', $CenaJednostkowaProduktu, $TekstProdukty);
        $TekstProdukty = str_replace('{PRODUKT_WARTOSC_BRUTTO}', $WartoscProduktu, $TekstProdukty);
        $TekstProdukty = str_replace('{PRODUKT_KUPIONA_ILOSC}', (string)$TablicaZawartosci['ilosc'] . ' ' . (string)$Produkt->info['jednostka_miary'], $TekstProdukty);

        $ListaProduktowPozycje[] = $TekstProdukty;
        
        unset($ProduktNazwaTmp, $ProduktZestawu, $IloscProduktu, $CenaJednostkowaProduktu, $WartoscProduktu, $TekstProdukty);

        // integracja z easyprotect
        if ( isset($TablicaZawartosci['wariant']['ubezpieczenie']) && count($TablicaZawartosci['wariant']['ubezpieczenie']) > 0 ) {

             $ProduktNazwaTmp = $GLOBALS['tlumacz']['EASYPROTECT_NAZWA'] . ' ' . $Produkt->info['nazwa'] . ' - ' . $GLOBALS['tlumacz']['OKRES_OCHRONY'] . ': ' . $TablicaZawartosci['wariant']['ubezpieczenie']['ile_lat'] . ' ' . (($TablicaZawartosci['wariant']['ubezpieczenie']['ile_lat'] == 1) ? $GLOBALS['tlumacz']['ROK'] : $GLOBALS['tlumacz']['LATA']);

             $ListaProduktow .= '<tr>';
             $ListaProduktow .= '<td style="width:50%;padding:5px">' . $ProduktNazwaTmp . '</td>';
             $ListaProduktow .= '<td style="width:15%;padding:5px;text-align:center"></td>';
             //
             $CenaJednostkowaProduktu = $GLOBALS['waluty']->WyswietlFormatCeny((float)$TablicaZawartosci['wariant']['ubezpieczenie']['cena_brutto'], $_SESSION['domyslnaWaluta']['id'], true, false);
             $ListaProduktow .= '<td style="width:15%;padding:5px;text-align:center">' . $CenaJednostkowaProduktu . '</td>';
            
             $ListaProduktow .= '<td style="width:5%;padding:5px;text-align:center">' . $TablicaZawartosci['ilosc'] . '</td>';
            
             $WartoscProduktu = $GLOBALS['waluty']->WyswietlFormatCeny((float)$TablicaZawartosci['wariant']['ubezpieczenie']['cena_brutto'] * $TablicaZawartosci['ilosc'], $_SESSION['domyslnaWaluta']['id'], true, false);  
             $ListaProduktow .= '<td style="width:15%;padding:5px;text-align:center">' . $WartoscProduktu . '</td>';
            
             $ListaProduktow .= '</tr>';
             
             // lista pojedynczych produktow
             $TekstProdukty = substr($tekst, strpos($tekst, '{ZAMOWIONE_PRODUKTY_START}'), strlen($tekst));
             $TekstProdukty = substr($TekstProdukty, 0, strpos($TekstProdukty, '{ZAMOWIONE_PRODUKTY_KONIEC}') + strlen('{ZAMOWIONE_PRODUKTY_KONIEC}'));
            
             $TekstProdukty = str_replace('{ZAMOWIONE_PRODUKTY_START}', '<div style="word-break:break-word">', $TekstProdukty);
             $TekstProdukty = str_replace('{ZAMOWIONE_PRODUKTY_KONIEC}', '</div>', $TekstProdukty);            
             $TekstProdukty = str_replace('{NAZWA_PRODUKTU}', $ProduktNazwaTmp, $TekstProdukty);
             $TekstProdukty = str_replace('{PRODUKT_NR_KATALOGOWY}', '', $TekstProdukty);
             $TekstProdukty = str_replace('{PRODUKT_KOD_EAN}', '', $TekstProdukty);
             $TekstProdukty = str_replace('{PRODUKT_CENA_JEDNOSTKOWA_BRUTTO}', $CenaJednostkowaProduktu, $TekstProdukty);
             $TekstProdukty = str_replace('{PRODUKT_WARTOSC_BRUTTO}', $WartoscProduktu, $TekstProdukty);
             $TekstProdukty = str_replace('{PRODUKT_KUPIONA_ILOSC}', $TablicaZawartosci['ilosc'], $TekstProdukty);
             
             $ListaProduktowPozycje[] = $TekstProdukty;
            
             unset($ProduktNazwaTmp, $ProduktZestawu, $IloscProduktu, $CenaJednostkowaProduktu, $WartoscProduktu, $TekstProdukty);             
    
        }         

    }
    
    unset($CechaPrd, $JakieCechy, $KomentarzProduktu, $PolaTekstowe, $ProducentProduktu, $CzasWysylkiProduktu, $StanProduktu, $GwarancjaProduktu);
} 

$ListaProduktow .= '</table>';

// pozycje pojedyncze
$ListaProduktowPojedyncze = '';
//
if ( strpos($tekst, '{ZAMOWIONE_PRODUKTY_START}') > -1 ) {
     //
     $tekst = str_replace('{ZAMOWIONE_PRODUKTY_START}', '{LISTA_PRODUKTOW_POJEDYNCZE}{ZAMOWIONE_PRODUKTY_START}', $tekst);
     //
     $tekst = preg_replace("/{ZAMOWIONE_PRODUKTY_START}.*{ZAMOWIONE_PRODUKTY_KONIEC}/si", ' ', $tekst);
     //
     $ListaProduktowPojedyncze = implode('', $ListaProduktowPozycje);
     //
}

define('LISTA_PRODUKTOW_POJEDYNCZE', $ListaProduktowPojedyncze); 
define('LISTA_PRODUKTOW', $ListaProduktow); 

unset($ListaProduktow, $ListaProduktowPojedyncze, $CechyProdukty);

$PodsumowanieTekst = '';
$KoncowaWartoscZamowienia = 0;
foreach ( $PodsumowanieTablica as $Podsuma ) {
    //
    if ( $Podsuma['klasa'] != 'ot_total' ) {
         $PodsumowanieTekst .= $Podsuma['nazwa'] . ': ' . $GLOBALS['waluty']->WyswietlFormatCeny($Podsuma['wartosc'], $_SESSION['domyslnaWaluta']['id'], true, false) . '<br />';
       } else {
         $PodsumowanieTekst .= '<span style="font-size:120%;font-weight:bold">' . $Podsuma['nazwa'] . ': <span style="font-size:140%">' . $GLOBALS['waluty']->WyswietlFormatCeny($Podsuma['wartosc'], $_SESSION['domyslnaWaluta']['id'], true, false) . '</span></span><br />';
         $KoncowaWartoscZamowienia = $Podsuma['wartosc'];
    }
    //
}
define('MODULY_PODSUMOWANIA', $PodsumowanieTekst); 
unset($PodsumowanieTablica, $PodsumowanieTekst);

$ByloNaliczaniePP = false;

// jezeli jest wlazony program partnerski z kodami rabatowymi
if ( SYSTEM_PUNKTOW_STATUS == 'tak' && PP_KOD_STATUS == 'tak' ) {
    //
    if ( isset($_SESSION['kuponRabatowy']) ) {
         //
         // okreslanie id partnera
         $zapytanieKupon = "select cp.coupons_pp_id, c.customers_groups_id from coupons cp, customers c where cp.coupons_name = '" . $filtr->process($_SESSION['kuponRabatowy']['kupon_kod']) . "' and cp.coupons_pp_id = c.customers_id";
         $sqlKupon = $GLOBALS['db']->open_query($zapytanieKupon);
         $infk = $sqlKupon->fetch_assoc(); 
         //
         // jezeli id partnera jest inne niz klienta
         if ( isset($infk['coupons_pp_id']) && ((int)$infk['coupons_pp_id'] != 0 && $infk['coupons_pp_id'] != (int)$_SESSION['customer_id']) ) {
              //
              // sprawdzi czy grupa klientow do ktorej nalezy partner jest objeta systemem punktow
              if ( in_array((int)$infk['customers_groups_id'], explode(',', (string)SYSTEM_PUNKTOW_GRUPY_KLIENTOW)) || SYSTEM_PUNKTOW_GRUPY_KLIENTOW == '' ) {

                  // wartosc prowizji
                  if ( PP_KOD_SPOSOB_NALICZANIA == 'procent' ) {
                       $IloscPunktow = ceil(( ($KoncowaWartoscZamowienia/$_SESSION['domyslnaWaluta']['przelicznik']) * (PP_KOD_PROWIZJA_PROCENT/100) ) * SYSTEM_PUNKTOW_WARTOSC);
                  } else {
                       $IloscPunktow = PP_KOD_PROWIZJA;
                  }

                  $pola = array(
                          array('customers_id',(int)$infk['coupons_pp_id']),
                          array('orders_id',(int)$id_dodanej_pozycji_zamowienia),
                          array('points',(int)$IloscPunktow),
                          array('date_added','now()'),
                          array('points_status','1'),
                          array('points_type','PP'));
                          
                  unset($IloscPunktow);
                          
                  $GLOBALS['db']->insert_query('customers_points' , $pola);
                  unset($pola);
                  
                  $ByloNaliczaniePP = true;

                  // jezeli jest naliczanie pkt za kolejne zakupy
                  if ( PP_KOD_PRZYPISANIE_KLIENTA == 'tak' ) {
                       //
                       $pola = array(array('pp_id_customers_coupon', $infk['coupons_pp_id']),
                                     array('pp_id_customers_coupon_code',$filtr->process($_SESSION['kuponRabatowy']['kupon_kod'])));		
                       $GLOBALS['db']->update_query('customers' , $pola, " customers_id = '" . (int)$_SESSION['customer_id'] . "'");	
                       unset($pola);                               
                       //
                  }
                  //

              }                     
              //
         }
         //
         $GLOBALS['db']->close_query($sqlKupon);
         unset($infk, $zapytanieKupon);            
         //
    } else {
         //
         // sprawdzi czy klient wczesniej nie skladal zamowienia z uzyciem kodu rabatowego PP - czy jest przypisany ID partnera
         if ( isset($_SESSION['pp_id_coupon']) && (int)$_SESSION['pp_id_coupon'] > 0 ) {
              //
              // jezeli id partnera jest inne niz klienta
              if ( (int)$_SESSION['pp_id_coupon'] != (int)$_SESSION['customer_id'] ) {
                    //
                    // wartosc prowizji
                    if ( PP_KOD_SPOSOB_NALICZANIA == 'procent' ) {
                         $IloscPunktow = ceil(( ($KoncowaWartoscZamowienia/$_SESSION['domyslnaWaluta']['przelicznik']) * (PP_KOD_PROWIZJA_PROCENT/100) ) * SYSTEM_PUNKTOW_WARTOSC);
                    } else {
                         $IloscPunktow = PP_KOD_PROWIZJA;
                    }

                    $pola = array(
                            array('customers_id',(int)$_SESSION['pp_id_coupon']),
                            array('orders_id',(int)$id_dodanej_pozycji_zamowienia),
                            array('points',(int)$IloscPunktow),
                            array('date_added','now()'),
                            array('points_status','1'),
                            array('points_type','PP'));
                            
                    unset($IloscPunktow);
                            
                    $GLOBALS['db']->insert_query('customers_points' , $pola);
                    unset($pola);
                    
                    $ByloNaliczaniePP = true;                    
                    //
              }              
              //
         }         
         //
    }
    //
}

if ( isset($_SESSION['kuponRabatowy']) ) {
     unset($_SESSION['kuponRabatowy']);
}

// jezeli jest wlaczony program partnerski dodanie punktow do konta klienta
if ( SYSTEM_PUNKTOW_STATUS == 'tak' && PP_STATUS == 'tak' && $ByloNaliczaniePP == false ) {
    //
    $IdPartnera = 0;
    // ustalenie czy jest id partnera w bazie klienta lub cookie
    // pierwszenstwo maja dane pobrane z bazy
    if ( isset($_COOKIE['pp']) && (int)$_COOKIE['pp'] > 0 ) {
         $IdPartnera = (int)$_COOKIE['pp'];
         //
         // jezeli jest ciasteczko musi sprawdzic czy jest to pierwsze zamowienie klienta
         if ( PP_NALICZANIE == 'pierwsze' ) {
              //
              // jezeli byly zeruje id partnera
              if ( Klient::IloscZamowien( $_SESSION['customer_email'], 'mail', $id_dodanej_pozycji_zamowienia ) > 0 ) {
                   $IdPartnera = 0;
              }
              //
         }
         //
    }
    if ( isset($_SESSION['pp_id']) && (int)$_SESSION['pp_id'] > 0 && PP_NALICZANIE == 'wszystkie' ) {
         $IdPartnera = (int)$_SESSION['pp_id'];
    }

    // musi sprawdzic czy jest ciastko z nr id klienta (partnera) oraz czy id partnera 
    // nie jest takie same jak klienta - zeby sam sobie nie robil zamowien
    if ( $IdPartnera > 0 && $IdPartnera != (int)$_SESSION['customer_id'] ) {
      
        // sprawdzi to jakiej grupy klientow nalezy partner
        $zapytanie_grupa = "select customers_groups_id from customers where customers_id = '" . (int)$IdPartnera . "'";
        $sql_grupa = $GLOBALS['db']->open_query($zapytanie_grupa);
        $info_grupa = $sql_grupa->fetch_assoc();

        // sprawdzi czy grupa klientow do ktorej nalezy partner jest objeta systemem punktow
        if ( in_array((int)$info_grupa['customers_groups_id'], explode(',', (string)SYSTEM_PUNKTOW_GRUPY_KLIENTOW)) || SYSTEM_PUNKTOW_GRUPY_KLIENTOW == '' ) {

            // wartosc prowizji
            if ( PP_SPOSOB_NALICZANIA == 'procent' ) {
                //                         
                $IloscPunktow = ceil(( ($WartoscZamowieniaDlaPkt/$_SESSION['domyslnaWaluta']['przelicznik']) * (PP_PROWIZJA_PROCENT/100) ) * SYSTEM_PUNKTOW_WARTOSC);
                //
              } else {
                //
                $IloscPunktow = PP_PROWIZJA;
                //
            }
        
            $pola = array(
                    array('customers_id',(int)$IdPartnera),
                    array('orders_id',(int)$id_dodanej_pozycji_zamowienia),
                    array('points',(int)$IloscPunktow),
                    array('date_added','now()'),
                    array('points_status','1'),
                    array('points_type','PP'));
                    
            unset($IloscPunktow);
                    
            $GLOBALS['db']->insert_query('customers_points' , $pola);
            unset($pola);
            
            // przypisuje id partnera do klienta 
            // jezeli bedzie wlaczone przyznawanie punktow za kolejne zamowienia to sklep bedzie widzial jakie jest id
            $pola = array(array('pp_id_customers', (int)$IdPartnera));		
            $GLOBALS['db']->update_query('customers' , $pola, " customers_id = '" . (int)$_SESSION['customer_id'] . "'");	
            unset($pola);         
            
            // id partnera do programu partnerskiego
            if ( PP_NALICZANIE == 'wszystkie' && !isset($_SESSION['pp_id']) ) {
                 $_SESSION['pp_id'] = $IdPartnera;
            }    
            // usuwa ciasteczko
            if ( isset($_COOKIE['pp']) ) {
                 setcookie("pp", "", time() - 3600, '/');
            }
            
        }        
                
        $GLOBALS['db']->close_query($sql_grupa);
        unset($info_grupa, $zapytanie_grupa);             
    
    }

}

unset($ByloNaliczaniePP, $WartoscZamowieniaDlaPkt);

if ( !empty($_POST['komentarz']) ) {
     define('KOMENTARZ_DO_ZAMOWIENIA', $GLOBALS['tlumacz']['KOMENTARZ_DO_ZAMOWIENIA'] . '<br />' .nl2br($filtr->process($_POST['komentarz'])) . '<br />'); 
   } else {
     define('KOMENTARZ_DO_ZAMOWIENIA', '');
}
 
$dane_do_faktury = '';
$dane_do_faktury .= $_SESSION['adresFaktury']['imie'] . ' ' . $_SESSION['adresFaktury']['nazwisko'];
if ( trim((string)$dane_do_faktury) != '' ) {
   $dane_do_faktury .= '<br />';
}
if ( $_SESSION['adresFaktury']['firma'] != '' ) {
    //
    $dane_do_faktury .= $_SESSION['adresFaktury']['firma'] . '<br />';
    $dane_do_faktury .= $_SESSION['adresFaktury']['nip'] . '<br />';
    //
}
$dane_do_faktury .= $_SESSION['adresFaktury']['ulica'] . '<br />';
$dane_do_faktury .= $_SESSION['adresFaktury']['kod_pocztowy'] . ' ' . $_SESSION['adresFaktury']['miasto'] . '<br />';
if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
    //
    $tmp_wojewodztwo = Klient::pokazNazweWojewodztwa($_SESSION['adresFaktury']['wojewodztwo']);
    if ( !empty($tmp_wojewodztwo) ) {
         $dane_do_faktury .= $tmp_wojewodztwo . '<br />';
    }
    unset($tmp_wojewodztwo);
    //
}
$dane_do_faktury .= Klient::pokazNazwePanstwa($_SESSION['adresFaktury']['panstwo']); 
define('ADRES_ZAMAWIAJACEGO', $dane_do_faktury); 
unset($dane_do_faktury);

$dane_do_wysylki = '';
$dane_do_wysylki .= $_SESSION['adresDostawy']['imie'] . ' ' . $_SESSION['adresDostawy']['nazwisko'];
if ( trim((string)$dane_do_wysylki) != '' ) {
   $dane_do_wysylki .= '<br />';
}
if ( $_SESSION['adresDostawy']['firma'] != '' ) {
    //
    $dane_do_wysylki .= $_SESSION['adresDostawy']['firma'] . '<br />';
    //
}
$dane_do_wysylki .= $_SESSION['adresDostawy']['ulica'] . '<br />';
$dane_do_wysylki .= $_SESSION['adresDostawy']['kod_pocztowy'] . ' ' . $_SESSION['adresDostawy']['miasto'] . '<br />';
if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
    //
    $tmp_wojewodztwo = Klient::pokazNazweWojewodztwa($_SESSION['adresDostawy']['wojewodztwo']);
    if ( !empty($tmp_wojewodztwo) ) {
         $dane_do_wysylki .= $tmp_wojewodztwo . '<br />';
    }
    unset($tmp_wojewodztwo);
    //
}
$dane_do_wysylki .= Klient::pokazNazwePanstwa($_SESSION['adresDostawy']['panstwo']);

define('ADRES_DOSTAWY', $dane_do_wysylki); 
unset($dane_do_wysylki);

// tworzenie klasy zamowienia
$zamowienie = new Zamowienie( $id_dodanej_pozycji_zamowienia ); 

// hash
$hashKod = '';
if ( STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak' ) {
  
     $hashKod = '/zamowienie=' . hash("sha1", $zamowienie->info['id_zamowienia'] . ';' . $zamowienie->info['data_zamowienia'] . ';' . $zamowienie->klient['adres_email'] . ';' . $zamowienie->klient['id']);
     
}

if ( $zamowienie->sprzedaz_online_pliki == true || $zamowienie->sprzedaz_online_kody == true ) {

    define('LINK_PLIKOW_ELEKTRONICZNYCH', '<br /><b>' . $GLOBALS['tlumacz']['POBRANIE_PLIKOW_ZAMOWIENIA'] . ' <a style="text-decoration:underline;word-break:break-word" href="' . ADRES_URL_SKLEPU . '/' . $zamowienie->sprzedaz_online_link . $hashKod . '">' . $GLOBALS['tlumacz']['POBRANIE_PLIKOW_ZAMOWIENIA_LINK'] . '</a></b><br />'); 

  } else {
  
    define('LINK_PLIKOW_ELEKTRONICZNYCH', ''); 
  
}

// integracja z automater
if ( INTEGRACJA_AUTOMATER_WLACZONY == 'tak' ) {
 
     $tablica_automater = array();
     
     foreach ($zamowienie->produkty as $produkt_tmp ) {
         //
         if ( $produkt_tmp['automater_id'] > 0 ) {
              $tablica_automater[] = array( 'id' => $produkt_tmp['automater_id'], 'ilosc' => $produkt_tmp['ilosc'], 'cena' => $produkt_tmp['cena_koncowa_brutto'] ); 
         }
         //
     }
     
     if ( count($tablica_automater) > 0 ) {
     
          include_once 'programy/automater/vendor/autoload.php';
          //
          $client = new \AutomaterSDK\Client\Client(INTEGRACJA_AUTOMATER_API, INTEGRACJA_AUTOMATER_API_SECRET);

          $transactionRequest = new \AutomaterSDK\Request\TransactionRequest();
          $transactionRequest->setEmail($zamowienie->klient['adres_email']);
          $transactionRequest->setLanguage(\AutomaterSDK\Request\TransactionRequest::LANGUAGE_PL);
          $transactionRequest->setPhone($zamowienie->klient['telefon']);
          $transactionRequest->setSendStatusEmail(\AutomaterSDK\Request\TransactionRequest::SEND_STATUS_EMAIL_TRUE);
          $transactionRequest->setCustom('Zamówienie nr ' . $id_dodanej_pozycji_zamowienia);

          $r = 0;
          $transactionProduct = array();
          
          foreach ( $tablica_automater as $tmp ) {
            
              $transactionProduct[$r] = new \AutomaterSDK\Request\Entity\TransactionProduct();
              $transactionProduct[$r]->setId( $tmp['id'] ); 
              $transactionProduct[$r]->setQuantity( $tmp['ilosc'] ); 
              
              $transactionProduct[$r]->setPrice( $tmp['cena'] );
              $transactionProduct[$r]->setCurrency( $_SESSION['domyslnaWaluta']['kod'] );              
              
              $r++;
              
          }
          
          $transactionRequest->setProducts( $transactionProduct );

          $transactionResponse = $client->createTransaction($transactionRequest);
          $idCart = (int)$transactionResponse->getCartId();
          
          if ( $idCart > 0 ) {
               //
               $pola = array(array('automater_id_cart',(int)$idCart));        
               $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . $id_dodanej_pozycji_zamowienia . "'");
               unset($pola);               
               //
          }
          
          unset($client, $transactionRequest, $transactionProduct, $transactionResponse);

     }          
  
}

// link do szczegolow zamowienia

if ( $_SESSION['gosc'] == '0' || STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak' ) {
    define('LINK', '<a style="word-break:break-word" href="' . ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/' . Seo::link_SEO('zamowienia_szczegoly.php',$id_dodanej_pozycji_zamowienia,'zamowienie') . $hashKod . '">' . ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/' . Seo::link_SEO('zamowienia_szczegoly.php',$id_dodanej_pozycji_zamowienia,'zamowienie') . $hashKod . '</a>'); 
} else {
    define('LINK', $GLOBALS['tlumacz']['BRAK_DOSTEPU_DO_HISTORII']); 
}

unset($hashKod, $zamowienie, $IdProduktowZamowienia);

$tekst = Funkcje::parsujZmienne($tekst);
$tekst = preg_replace("{(<br[\\s]*(>|\/>)\s*){2,}}i", "<br /><br />", (string)$tekst);

// czy ma wysylac maila

if ( $tresc['email_send'] == 1 ) {
       
    $email = new Mailing;
    $email->wyslijEmail($nadawca_email, $nadawca_nazwa, $adresat_email, $adresat_nazwa, $kopia_maila, $temat, $tekst, $szablon, $jezyk, $zalaczniki);
    unset($email);
    
}

if ( count($ProduktyEasyProtect) > 0 && INTEGRACJA_EASYPROTECT_MAIL == 'tak' ) {
  
    $tekst = '<div style="font-size:20px;padding-bottom:20px">Zakupiono gwarancję w sklepie: ' . ADRES_URL_SKLEPU . '</div>';
    $tekst .= '<div style="font-size:14px;padding-bottom:10px">Zamówienie nr: ' . $id_dodanej_pozycji_zamowienia . '</div>';
    
    $tekst .= '<div style="font-size:14px">';
    
    foreach ( $ProduktyEasyProtect as $IdKlucz => $Tmp ) {
       $tekst .= '<div style="padding:5px 0 5px 0">Id produktu: ' . $IdKlucz . ', produkt: ' . $Tmp . '</div>';
    }
    
    $tekst .= '</div>';
    
    $email = new Mailing;
    $email->wyslijEmail($nadawca_email,$nadawca_nazwa, 'shopgold@easyprotect.pl', 'EasyProtect', '', 'Dodatkowa gwarancja w sklepie: ' . ADRES_URL_SKLEPU, $tekst, 1, $_SESSION['domyslnyJezykStaly']['id'], array());
    
    unset($email, $tekst);
    
}

unset($ProduktyEasyProtect);

$GLOBALS['db']->close_query($sql);
 
/* nieuzywane - dodatkowo do przeslania do administratora sklepu zamowienia w formacie csv
unset($email);
$email = new Mailing;
// kopia z zalacznikiem dla administratora sklepu
ob_start();
$_SESSION['pobranieZamowienia'] = 'tak';
require('zarzadzanie/sprzedaz/zamowienia_pobierz.php');
$wynikPliku = ob_get_contents();
ob_end_clean();                        
$te = array ( array('ciag' => $wynikPliku, 'plik' => 'zamowienie_nr_' . $id_dodanej_pozycji_zamowienia . '.csv', 'typ' => 'text/plain') );
$wiadomosc = $email->wyslijEmail($nadawca_email,$nadawca_nazwa,INFO_EMAIL_SKLEPU, $adresat_nazwa, '', $temat, $tekst, $szablon, $jezyk, $zalaczniki, $te);
*/

if ( SMS_WLACZONE == 'tak' && SMS_NOWE_ZAMOWIENIE == 'tak' && SMS_ODBIORCA != '' ) {

    $adresat   = SMS_ODBIORCA;
    $wiadomosc = strip_tags(Funkcje::parsujZmienne($tresc['description_sms']));

    SmsApi::wyslijSms($adresat, $wiadomosc);

}

unset($wiadomosc, $tresc, $zapytanie_tresc, $nadawca_email, $nadawca_nazwa, $adresat_email, $adresat_nazwa, $temat, $tekst, $zalaczniki, $szablon, $jezyk, $adresat); 

// wylaczenie produktu po zakupie
if ( MAGAZYN_WYLACZ_PRODUKT == 'tak' ) {

    foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
    
        $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ) );
        
        if ( $Produkt->info['kontrola_magazynu'] > 0 ) {
        
        // sprawdzi czy dodawany produkt nie jest zestawem - jezeli tak rozbije go na elementy
        if ( $Produkt->info['zestaw'] == 'tak' ) {
          
            foreach ($Produkt->zestawProdukty as $IdProduktuZestawu => $DaneZestawu ) { 
            
                $ProduktZestawu = new Produkt( $IdProduktuZestawu );

                $AktualnaIloscProduktu = $ProduktZestawu->info['ilosc'];
                
                if ( $AktualnaIloscProduktu <= 0 ) {
                  
                    $pola = array(array('products_status','0'));        
                    $GLOBALS['db']->update_query('products' , $pola, "products_id = '" . $ProduktZestawu->info['id'] . "'");
                    unset($pola);
                    
                    // musi sprawdzic czy wylaczany produkt nie wystepuje w zestawach - jezeli tak to wylaczy zestaw
                    //
                    $zapytanie_zestaw = 'SELECT products_set_products, products_id FROM products WHERE products_set = 1';        
                    $sql_zestaw = $db->open_query($zapytanie_zestaw);
                    //
                    if ( (int)$db->ile_rekordow($sql_zestaw) > 0 ) {
                         //
                         while ($info = $sql_zestaw->fetch_assoc()) {
                              //
                              $id_produktow = unserialize($info['products_set_products']);
                              foreach ( $id_produktow as $id => $dane ) {
                                  //
                                  if ( $ProduktZestawu->info['id'] == $id ) {
                                       //
                                       $pola = array(array('products_status','0'));        
                                       $GLOBALS['db']->update_query('products' , $pola, "products_id = '" . $info['products_id'] . "'");
                                       unset($pola);                                     
                                       //
                                  }
                                  //
                              }
                              unset($id_produktow);
                              //
                         }
                         //
                    }
                    $db->close_query($sql_zestaw);
                    unset($zapytanie_zestaw); 
                
                }
                
                unset($ProduktZestawu, $AktualnaIloscProduktu);            
            
            }
            
            $AktualnaIloscProduktu = $Produkt->info['ilosc'];
            
            if ( $AktualnaIloscProduktu <= 0 ) {
                $pola = array(array('products_status','0'));        
                $GLOBALS['db']->update_query('products' , $pola, "products_id = '" . $Produkt->info['id'] . "'");
                unset($pola);
            }
            
            unset($AktualnaIloscProduktu);            
            
        } else {
        
            $AktualnaIloscProduktu = $Produkt->info['ilosc'];
            
            if ( $AktualnaIloscProduktu <= 0 ) {
              
                $pola = array(array('products_status','0'));        
                $GLOBALS['db']->update_query('products' , $pola, "products_id = '" . $Produkt->info['id'] . "'");
                unset($pola);
                
                // musi sprawdzic czy wylaczany produkt nie wystepuje w zestawach - jezeli tak to wylaczy zestaw
                //
                $zapytanie_zestaw = 'SELECT products_set_products, products_id FROM products WHERE products_set = 1';        
                $sql_zestaw = $db->open_query($zapytanie_zestaw);
                //
                if ( (int)$db->ile_rekordow($sql_zestaw) > 0 ) {
                     //
                     while ($info = $sql_zestaw->fetch_assoc()) {
                          //
                          $id_produktow = unserialize($info['products_set_products']);
                          foreach ( $id_produktow as $id => $dane ) {
                              //
                              if ( $Produkt->info['id'] == $id ) {
                                   //
                                   $pola = array(array('products_status','0'));        
                                   $GLOBALS['db']->update_query('products' , $pola, "products_id = '" . $info['products_id'] . "'");
                                   unset($pola);                                     
                                   //
                              }
                              //
                          }
                          unset($id_produktow);
                          //
                     }
                     //
                }
                $db->close_query($sql_zestaw);
                unset($zapytanie_zestaw); 
                
            }
            
            unset($AktualnaIloscProduktu);
            
        }
        
        }
        
        unset($Produkt);
        
    }

}

// integracja z Freshmail
IntegracjeZewnetrzne::FreshmailDoZamowienieRealizacja();

// integracja z MailerLite
IntegracjeZewnetrzne::MailerLiteDoZamowienieRealizacja();

// integracja z Ecomail
IntegracjeZewnetrzne::EcomailDoZamowienieRealizacja();

// integracja z Mailjet
IntegracjeZewnetrzne::MailjetDoZamowienieRealizacja();

// integracja z Getall
IntegracjeZewnetrzne::GetallZamowienieRealizacja();

// zapisanie do sesji id nowego zamowienia
$_SESSION['zamowienie_id'] = $id_dodanej_pozycji_zamowienia;

if ( PDF_ZAPISANIE_ZAMOWIENIA == 'tak' ) {
    include_once('pdf/zamowienie_plik.php');
}

Funkcje::PrzekierowanieSSL( '/zamowienie-podsumowanie.html' );

?>