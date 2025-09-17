<?php

// plik
$WywolanyPlik = 'dane_adresowe';

include('start.php');

// po wypelnieniu formularza
if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz' && (isset($_POST['przetwarzanie']) && (int)$_POST['przetwarzanie'] == 1) && (isset($_POST['przetwarzanie_dodatkowe']) && (int)$_POST['przetwarzanie_dodatkowe'] == 1)) {

    if ( Sesje::TokenSpr(true) ) {
      
        if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' && (int)$_POST['id_klienta'] == (int)$_SESSION['customer_id']) {
          
            $pola = array(array('entry_company',($_POST['osobowosc'] == '0' ? $filtr->process($_POST['nazwa_firmy']) : '')),
                          array('entry_nip',($_POST['osobowosc'] == '0' ? $filtr->process($_POST['nip_firmy']) : '')),
                          array('entry_regon',($_POST['osobowosc'] == '0' ? ((isset($_POST['regon_firmy'])) ? $filtr->process($_POST['regon_firmy']) : '') : '')),
                          array('entry_pesel',($_POST['osobowosc'] == '1' ? ((isset($_POST['pesel'])) ? $filtr->process($_POST['pesel']) : '') : '')),
                          array('entry_firstname',$filtr->process($_POST['imie'])),
                          array('entry_lastname',$filtr->process($_POST['nazwisko'])),
                          array('entry_street_address',$filtr->process($_POST['ulica'])),
                          array('entry_postcode',$filtr->process($_POST['kod_pocztowy'])),
                          array('entry_city',$filtr->process($_POST['miasto'])),
                          array('entry_country_id',(int)$_POST['panstwo']),
                          array('entry_zone_id',(( isset($_POST['wojewodztwo'])) ? (int)$_POST['wojewodztwo'] : '0' )));

            $GLOBALS['db']->update_query('address_book' , $pola, " address_book_id = '".(int)$_POST['adres_id']."'");
            unset($pola);

            unset($_SESSION['adresDostawy']);

            $_SESSION['adresDostawy'] = array('imie' => $filtr->process($_POST['imie']),
                                              'nazwisko' => $filtr->process($_POST['nazwisko']),
                                              'firma' => $filtr->process($_POST['nazwa_firmy']),
                                              'ulica' => $filtr->process($_POST['ulica']),
                                              'kod_pocztowy' => $filtr->process($_POST['kod_pocztowy']),
                                              'miasto' => $filtr->process($_POST['miasto']),
                                              'telefon' => ( isset($_POST['telefon']) ? $filtr->process($_POST['telefon']) : '' ),
                                              'panstwo' => (int)$_POST['panstwo'],
                                              'wojewodztwo' => (( isset($_POST['wojewodztwo'])) ? (int)$_POST['wojewodztwo'] : '' ));

            unset($_SESSION['adresFaktury']);
            
            // jezeli osoba fizyczna
            $imie = ''; $nazwisko = ''; $pesel = '';
            if ( isset($_POST['osobowosc']) && $_POST['osobowosc'] == '1' ) {
                $imie = $filtr->process($_POST['imie']);
                $nazwisko = $filtr->process($_POST['nazwisko']);
                $pesel = ( (isset($_POST['pesel'])) ? $filtr->process($_POST['pesel']) : '');
            }
            $firma = ''; $nip = '';
            if ( isset($_POST['osobowosc']) && $_POST['osobowosc'] == '0' ) {
                $firma = $filtr->process($_POST['nazwa_firmy']);
                $nip = $filtr->process($_POST['nip_firmy']);
            }              
            // 
          
            $_SESSION['adresFaktury'] = array('imie' => $imie,
                                              'nazwisko' => $nazwisko,
                                              'pesel' => $pesel,
                                              'firma' => $firma,
                                              'nip' => $nip,  
                                              'ulica' => $filtr->process($_POST['ulica']),
                                              'kod_pocztowy' => $filtr->process($_POST['kod_pocztowy']),
                                              'miasto' => $filtr->process($_POST['miasto']),
                                              'panstwo' => (int)$_POST['panstwo'],
                                              'wojewodztwo' => (( isset($_POST['wojewodztwo'])) ? (int)$_POST['wojewodztwo'] : '' ));
                                              
            unset($imie, $nazwisko, $firma, $nip);                                      

            $pola = array(
                    array('customers_telephone',( isset($_POST['telefon']) ? $filtr->process($_POST['telefon']) : '' )),
                    array('customers_firstname',$filtr->process($_POST['imie'])),
                    array('customers_lastname',$filtr->process($_POST['nazwisko'])),
                    array('customers_email_address',$filtr->process($_POST['email'])),
                    array('customers_newsletter',( isset($_POST['biuletyn']) ? '1' : '0')));
                    
            if ( KLIENT_ZGODY_OPINIE == 'tak' ) {
                 $pola[] = array('customers_reviews',( isset($_POST['zgoda_opinie']) ? '1' : '0'));
            }
          
            if ( KLIENT_ZGODY_MARKETING == 'tak' ) {
                 $pola[] = array('customers_marketing',( isset($_POST['zgoda_marketing']) ? '1' : '0'));
            }
          
            if ( KLIENT_POKAZ_NICK == 'tak' ) {
                 $pola[] = array('customers_nick',$filtr->process($_POST['nick']));
            }
            
            if ( KLIENT_POKAZ_DATE_URODZENIA == 'tak' ) {
                 $pola[] = array('customers_dob', date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_urodzenia']))));
            }
            
            // integracja z SalesForce - zmiana adresu email
            if (  isset($_POST['biuletyn']) ) {
                if ( $_SESSION['customer_email'] != $filtr->process($_POST['email']) ) {
                    IntegracjeZewnetrzne::SalesForceDaneAdresoweZmianaEmail( $_SESSION['customer_email'], $filtr->process($_POST['email']), $filtr->process($_POST['imie']), $filtr->process($_POST['nazwisko']) );
                }
            }

            unset($_SESSION['customer_email']);
            $_SESSION['customer_email'] = $filtr->process($_POST['email']);
                    
            $GLOBALS['db']->update_query('customers' , $pola, " customers_id = '" . (int)$_SESSION['customer_id'] . "'");
            
            unset($pola);

            $pola = array(
                    array('customers_info_date_account_last_modified','now()'));
                    
            $GLOBALS['db']->update_query('customers_info' , $pola, " customers_info_id = '" . (int)$_SESSION['customer_id'] . "'");	
            
            unset($pola);

            // Dodatkowe pola klientow
            $GLOBALS['db']->delete_query('customers_to_extra_fields' , " customers_id = '" . (int)$_SESSION['customer_id'] . "'");  

            $dodatkowe_pola_klientow = "SELECT ce.fields_id, ce.fields_input_type 
                                        FROM customers_extra_fields ce 
                                        WHERE ce.fields_status = '1'";

            $sql = $GLOBALS['db']->open_query($dodatkowe_pola_klientow);

            if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0  ) {

              while ( $dodatkowePola = $sql->fetch_assoc() ) {
                    $pola = array();
                    $wartosc = '';
                    if ( $dodatkowePola['fields_input_type'] != '3' ) {
                    
                         $pola = array(
                                 array('customers_id',(int)$_SESSION['customer_id']),
                                 array('fields_id',(int)$dodatkowePola['fields_id']),
                                 array('value',$filtr->process($_POST['fields_' . $dodatkowePola['fields_id']]))
                         );
                         
                    } else {
                    
                         if ( isset($_POST['fields_' . $dodatkowePola['fields_id']]) ) {
                         
                            foreach ($_POST['fields_' . $dodatkowePola['fields_id']] as $key => $value) {
                              $wartosc .= $value . "\n";
                            }
                            
                            $pola = array(
                                    array('customers_id',(int)$_SESSION['customer_id']),
                                    array('fields_id',(int)$dodatkowePola['fields_id']),
                                    array('value',( $wartosc != '' ? rtrim($filtr->process($wartosc)) : '' )));
                                    
                         }

                    }

                    if ( count($pola) > 0 ) {
                    
                        $pola[] = array('language_id', (int)$_SESSION['domyslnyJezyk']['id']);
                        $GLOBALS['db']->insert_query('customers_to_extra_fields' , $pola);
                        unset($pola);
                      
                    }
                  }

            }
            
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie);

            // dane do newslettera
            //    
            $pola = array(
                    array('customers_id',(int)$_SESSION['customer_id']),
                    array('customers_newsletter',( isset($_POST['biuletyn']) ? '1' : '0')));
                    
            $GLOBALS['db']->update_query('subscribers' , $pola, " customers_id = '" . (int)$_SESSION['customer_id'] . "'");
            unset($pola);
            
            // integracja z SALESmanago
            IntegracjeZewnetrzne::SalesManagoDaneAdresowe( $_POST );
            
            // integracja z Freshmail
            IntegracjeZewnetrzne::FreshmailDaneAdresowe( $_POST );

            // integracja z MailerLite
            IntegracjeZewnetrzne::MailerLiteDaneAdresowe( $_POST );

            // integracja z Ecomail
            IntegracjeZewnetrzne::EcomailDaneAdresowe( $_POST );

            // integracja z Mailjet
            IntegracjeZewnetrzne::MailjetDaneAdresowe( $_POST );

            // integracja z Getall
            IntegracjeZewnetrzne::GetallDaneAdresowe( $_POST );
            
            // integracja z edrone
            IntegracjeZewnetrzne::EdroneDaneAdresowe( $_POST );                     
            
            // integracja z sare
            IntegracjeZewnetrzne::SareDaneAdresowe( $_POST );                     

            Funkcje::PrzekierowanieSSL( 'panel-klienta.html' );

        } else {
          
            Funkcje::PrzekierowanieURL('brak-strony.html');
          
        }
        
    } else {
    
        Funkcje::PrzekierowanieURL('brak-strony.html');
        
    }

}

$UsuniecieZgody = true;
if ( isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz' && (!isset($_POST['przetwarzanie']) || !isset($_POST['przetwarzanie_dodatkowe'])) ) {
     //
     $UsuniecieZgody = false;
     //
}

// usuniecie konta lub brak zgody na przetwarzanie danych
if ( isset($_POST['akcja']) && $_POST['akcja'] == 'usun_konto' ) {

    if ( Sesje::TokenSpr(true) ) {
      
        if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' && (int)$_POST['id_klienta'] == (int)$_SESSION['customer_id']) {
          
            // usuniecie klienta z SALESmanago
            IntegracjeZewnetrzne::SalesManagoDaneAdresoweUsuniecie();
                            
            // usuniecie z newslettera w systemie Freshmail
            IntegracjeZewnetrzne::FreshmailDaneAdresoweUsuniecie();
            
            // usuniecie z newslettera w systemie MailerLite
            IntegracjeZewnetrzne::MailerLiteDaneAdresoweUsuniecie();
            
            // usuniecie z newslettera w systemie Getall
            IntegracjeZewnetrzne::GetallDaneAdresoweUsuniecie();
            
            //
            $db->delete_query('customers' , " customers_id = '" . (int)$_SESSION['customer_id'] . "'");  
            $db->delete_query('customers_info' , " customers_info_id = '" . (int)$_SESSION['customer_id'] . "'");  
            $db->delete_query('address_book' , " customers_id = '" . (int)$_SESSION['customer_id'] . "'"); 
            $db->delete_query('subscribers' , " customers_id = '" . (int)$_SESSION['customer_id'] . "'"); 
            $db->delete_query('discount_manufacturers' , " discount_customers_id = '" . (int)$_SESSION['customer_id'] . "'");
            $db->delete_query('discount_categories' , " discount_customers_id = '" . (int)$_SESSION['customer_id'] . "'");       
            $db->delete_query('discount_categories_manufacturers' , " discount_customers_id = '" . (int)$_SESSION['customer_id'] . "'");       
            $db->delete_query('discount_products' , " discount_customers_id = '" . (int)$_SESSION['customer_id'] . "'");         
            $db->delete_query('customers_points' , " customers_id = '" . (int)$_SESSION['customer_id'] . "'");         
            $db->delete_query('customers_to_extra_fields' , " customers_id = '" . (int)$_SESSION['customer_id'] . "'");     
            $db->delete_query('blacklist' , " blacklist_customers_id = '" . (int)$_SESSION['customer_id'] . "'"); 
            //
            
            $zapytanie = "select basket_id from basket_save where customers_id = '" . (int)$_SESSION['customer_id'] . "'";
            $sql = $db->open_query($zapytanie);    

            if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0  ) {
  
                while ( $info = $sql->fetch_assoc() ) {
                        $db->delete_query('basket_save_products' , " basket_id = '" . $info['basket_id'] . "'");
                }
                
                unset($info);
                
            }
            
            $db->close_query($sql);
            unset($zapytanie);        
            //
        
            // usuwa zapisane koszyki klienta
            $db->delete_query('basket_save' , " customers_id = '" . (int)$_SESSION['customer_id'] . "'");
        
            // usuwa sesje schowka
            if ( isset($GLOBALS['schowekKlienta']) ) {
                $GLOBALS['schowekKlienta']->WyczyscSesjeSchowekZalogowanego();
            }
            // usuwa sesje koszyka
            $GLOBALS['koszykKlienta']->WyczyscSesjeKoszykZalogowanego();    
            //    
            Klient::WylogujKlienta();       

            $_SESSION['konto_usuniete'] = true;
          
            Funkcje::PrzekierowanieSSL( 'wylogowanie.html' );

        } else {
          
            Funkcje::PrzekierowanieURL('brak-strony.html');
          
        }
        
    } else {
    
        Funkcje::PrzekierowanieURL('brak-strony.html');
        
    }

}        

if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {

    $TablicaAdresow = array();
    //
    $zapytanie = "SELECT c.customers_id, c.customers_email_address, c.customers_nick, c.customers_dob, c.customers_default_address_id, c.customers_telephone, c.customers_fax, c.customers_newsletter, c.customers_reviews, c.customers_marketing, c.customers_guest_account, a.address_book_id, a.entry_company, a.entry_nip, a.entry_regon, a.entry_pesel, a.entry_firstname, a.entry_lastname, a.entry_street_address, a.entry_postcode, a.entry_city, a.entry_country_id, a.entry_zone_id
    FROM customers c LEFT JOIN address_book a ON a.customers_id = c.customers_id AND a.address_book_id = c.customers_default_address_id WHERE c.customers_id = '" . (int)$_SESSION['customer_id'] . "' AND c.customers_guest_account = '0' AND c.customers_status = '1'";

    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
    
        while ( $info = $sql->fetch_assoc() ) {
        
              $TablicaAdresow = array( 'adres_id' => $info['address_book_id'],
                                       'imie' => FunkcjeWlasnePHP::my_htmlentities($info['entry_firstname']),
                                       'nazwisko' => FunkcjeWlasnePHP::my_htmlentities($info['entry_lastname']),
                                       'data_urodzenia' => date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($info['customers_dob'])),
                                       'id_klienta' => (int)$info['customers_id'],
                                       'nick' => $info['customers_nick'],
                                       'adres_email' => $info['customers_email_address'],
                                       'telefon' => $info['customers_telephone'],
                                       'fax' => $info['customers_fax'],
                                       'ulica' => FunkcjeWlasnePHP::my_htmlentities($info['entry_street_address']),
                                       'kod_pocztowy' => $info['entry_postcode'],
                                       'miasto' => FunkcjeWlasnePHP::my_htmlentities($info['entry_city']),
                                       'kraj' => $info['entry_country_id'],
                                       'wojewodztwo' => $info['entry_zone_id'],
                                       'nazwa_firmy' => FunkcjeWlasnePHP::my_htmlentities($info['entry_company']),
                                       'nip' => $info['entry_nip'],
                                       'regon' => $info['entry_regon'],
                                       'biuletyn' => $info['customers_newsletter'],
                                       'zgoda_opinie' => $info['customers_reviews'],
                                       'zgoda_marketing' => $info['customers_marketing'],
                                       'pesel' => $info['entry_pesel']);
                    
        }
        
        unset($info);
        
    }
    
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);

} else {

    Funkcje::PrzekierowanieSSL( 'logowanie.html' );
    
}

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI','KLIENCI_PANEL','REJESTRACJA','ZAMOWIENIE_REALIZACJA') ), $GLOBALS['tlumacz'] );

// meta tagi
$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta);

// css do kalendarza
$tpl->dodaj('__CSS_PLIK', ',zebra_datepicker');
// dla wersji mobilnej
$tpl->dodaj('__CSS_KALENDARZ', ',zebra_datepicker');

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['PANEL_KLIENTA'],Seo::link_SEO('panel_klienta.php', '', 'inna'));
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_DANE_ADRESOWE']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

// wyglad srodkowy
$srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $TablicaAdresow, $UsuniecieZgody);

// definicje pol
foreach ($TablicaAdresow as $key => $value) {
  $srodek->dodaj('__'.strtoupper((string)$key), $value);
}

$srodek->dodaj('__DODATKOWE_POLA_KLIENTOW', Klient::pokazDodatkowePolaKlientow($_SESSION['customer_id'],(int)$_SESSION['domyslnyJezyk']['id']));

$tablicaPanstw = Klient::ListaPanstw();
$srodek->dodaj('__LISTA_PANSTW', Funkcje::RozwijaneMenu('panstwo', $tablicaPanstw, $TablicaAdresow['kraj'], 'id="wybor_panstwo" style="width:80%"'));

$tablicaWojewodztw = Klient::ListaWojewodztw($TablicaAdresow['kraj']);
$srodek->dodaj('__LISTA_WOJEWODZTW', '<span id="wybor_wojewodztwo_wynik">'.Funkcje::RozwijaneMenu('wojewodztwo', $tablicaWojewodztw, $TablicaAdresow['wojewodztwo'], ' style="width:80%" id="wybor_wojewodztwo"').'</span>');

$srodek->dodaj('__TOKEN',Sesje::Token());

// czy konto klienta jest polaczone z google
if ( isset($_SESSION['google_id']) && $_SESSION['google_id'] != '0' && $_SESSION['google_id'] != '' ) {
     //
     $srodek->dodaj('__GOOGLE_AKCJA', 'rozlacz');
     $srodek->dodaj('__GOOGLE_INFO', $GLOBALS['tlumacz']['ROZLACZENIE_GOOGLE']);
     //
  } else {
     //
     $srodek->dodaj('__GOOGLE_AKCJA', 'polacz');
     $srodek->dodaj('__GOOGLE_INFO', $GLOBALS['tlumacz']['POLACZENIE_GOOGLE']);    
     //    
}
// czy konto klienta jest polaczone z facebook
if ( isset($_SESSION['fb_id']) && $_SESSION['fb_id'] != '0' && $_SESSION['fb_id'] != '' ) {
     //
     $srodek->dodaj('__FB_AKCJA', 'rozlacz');
     $srodek->dodaj('__FB_INFO', $GLOBALS['tlumacz']['ROZLACZENIE_FACEBOOK']);
     //
  } else {
     //
     $srodek->dodaj('__FB_AKCJA', 'polacz');
     $srodek->dodaj('__FB_INFO', $GLOBALS['tlumacz']['POLACZENIE_FACEBOOK']);    
     //    
}

// tekst zgody na wysylanie maili o opiniach
$TekstZgody = str_replace('{INFO_NAZWA_SKLEPU}', (string)DANE_NAZWA_FIRMY_PELNA, (string)$GLOBALS['tlumacz']['ZGODA_NA_PRZEKAZANIE_DANYCH']);
$srodek->dodaj('__TEKST_ZGODY', $TekstZgody);

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

unset($srodek, $WywolanyPlik, $TablicaAdresow, $TekstZgody);

include('koniec.php');

?>