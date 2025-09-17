<?php

// plik
$WywolanyPlik = 'adresy_dostawy';

include('start.php');

// usuniecie adresu
if (isset($_GET['usun']) && (int)$_GET['usun'] > 0) {

    if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {
      
        // sprawdzi czy dany adres nalezy do tego uzytkownika
        $zapytanie = "SELECT ab.customers_id, 
                             ab.address_book_id 
                        FROM address_book ab 
                   LEFT JOIN customers c ON ab.customers_id = c.customers_id                        
                       WHERE ab.address_book_id != c.customers_default_address_id AND ab.customers_id = '" . (int)$_SESSION['customer_id'] . "' and ab.address_book_id = '" . (int)$_GET['usun'] . "'";
                       
        $sql = $GLOBALS['db']->open_query($zapytanie); 
        
        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {   

            // sprawdzi czy adres jest ustawiony jako domyslny
            $zapytanie = "SELECT c.customers_default_shipping_address_id FROM customers c WHERE customers_id = " . (int)$_SESSION['customer_id'];
            $sql = $GLOBALS['db']->open_query($zapytanie);
            //
            if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0  ) {
                  //
                  $info = $sql->fetch_assoc();
                  //
                  if ( (int)$info['customers_default_shipping_address_id'] == (int)$_GET['usun'] ) {
                        //
                        $pola = array(array('customers_default_shipping_address_id','0'));
                        $GLOBALS['db']->update_query('customers', $pola, "customers_id = '" . (int)$_SESSION['customer_id'] . "'");
                        unset($pola);
                        //        
                  }
                  //
                  unset($info);
                  //
            }
            //
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie);         

            $GLOBALS['db']->delete_query('address_book' , ' address_book_id = ' . (int)$_GET['usun']);
            
        }
        
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie);        

        Funkcje::PrzekierowanieSSL( 'adresy-dostawy.html' );

    } else {
    
        Funkcje::PrzekierowanieSSL( 'logowanie.html' );
        
    }

}

// ustawienie jako domyslnego
if (isset($_GET['domyslny']) && (int)$_GET['domyslny'] > 0) {

    if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {
      
        // sprawdzi czy dany adres nalezy do tego uzytkownika
        $zapytanie = "SELECT ab.customers_id, 
                             ab.address_book_id 
                        FROM address_book ab 
                   LEFT JOIN customers c ON ab.customers_id = c.customers_id                        
                       WHERE ab.customers_id = '" . (int)$_SESSION['customer_id'] . "' and ab.address_book_id = '" . (int)$_GET['domyslny'] . "'";
                       
        $sql = $GLOBALS['db']->open_query($zapytanie); 
        
        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 

            $pola = array(array('customers_default_shipping_address_id', (int)$_GET['domyslny']));
            $GLOBALS['db']->update_query('customers', $pola, "customers_id = '" . (int)$_SESSION['customer_id'] . "'");
            unset($pola);        

        }
        
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie);        

        Funkcje::PrzekierowanieSSL( 'adresy-dostawy.html' );

    } else {
    
        Funkcje::PrzekierowanieSSL( 'logowanie.html' );
        
    }

}

// po wypelnieniu formularza
if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

    if ( Sesje::TokenSpr(true) ) {
      
        if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' && (int)$_POST['id_klienta'] == (int)$_SESSION['customer_id']) {

            $pola = array(array('customers_id',(int)$_SESSION['customer_id']),
                          array('entry_company',$filtr->process($_POST['nazwa_firmy'])),
                          array('entry_firstname',$filtr->process($_POST['imie'])),
                          array('entry_lastname',$filtr->process($_POST['nazwisko'])),
                          array('entry_street_address',$filtr->process($_POST['ulica'])),
                          array('entry_postcode',$filtr->process($_POST['kod_pocztowy'])),
                          array('entry_city',$filtr->process($_POST['miasto'])),
                          array('entry_country_id',(int)$_POST['panstwo']),
                          array('entry_zone_id',(( isset($_POST['wojewodztwo'])) ? (int)$_POST['wojewodztwo'] : '0' )),
                          array('entry_telephone',(( isset($_POST['telefon'])) ? $filtr->process($_POST['telefon']) : '' )));

            $GLOBALS['db']->insert_query('address_book' , $pola);
            unset($pola);

            Funkcje::PrzekierowanieSSL( 'adresy-dostawy.html' );
            
        } else {
          
            Funkcje::PrzekierowanieURL('brak-strony.html');
          
        }                   

    } else {
    
        Funkcje::PrzekierowanieURL( 'brak-strony.html' );
        
    }

}

if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {

    $TablicaAdresow = array();
    //
    $zapytanie = "SELECT c.customers_id, 
                         a.address_book_id, 
                         a.entry_company, 
                         a.entry_firstname, 
                         a.entry_lastname, 
                         a.entry_street_address, 
                         a.entry_postcode, 
                         a.entry_city, 
                         a.entry_country_id, 
                         a.entry_zone_id,
                         a.entry_telephone,
                         c.customers_default_address_id,
                         c.customers_default_shipping_address_id
                    FROM customers c 
               LEFT JOIN address_book a ON a.customers_id = c.customers_id
                   WHERE c.customers_id = '".$_SESSION['customer_id']."' AND c.customers_guest_account = '0' AND c.customers_status = '1'";

    $sql = $GLOBALS['db']->open_query($zapytanie); 
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 1) {
    
        while ( $info = $sql->fetch_assoc() ) {
        
              $TablicaAdresow[] = array( 'adres_id' => $info['address_book_id'],
                                         'imie' => FunkcjeWlasnePHP::my_htmlentities($info['entry_firstname']),
                                         'nazwisko' => FunkcjeWlasnePHP::my_htmlentities($info['entry_lastname']),
                                         'id_klienta' => (int)$info['customers_id'],
                                         'ulica' => FunkcjeWlasnePHP::my_htmlentities($info['entry_street_address']),
                                         'kod_pocztowy' => $info['entry_postcode'],
                                         'miasto' => FunkcjeWlasnePHP::my_htmlentities($info['entry_city']),
                                         'kraj' => Klient::pokazNazwePanstwa($info['entry_country_id']),
                                         'wojewodztwo' => Klient::pokazNazweWojewodztwa($info['entry_zone_id']),
                                         'nazwa_firmy' => FunkcjeWlasnePHP::my_htmlentities($info['entry_company']),
                                         'telefon' => FunkcjeWlasnePHP::my_htmlentities($info['entry_telephone']),
                                         'glowny_adres' => (((int)$info['customers_default_address_id'] == (int)$info['address_book_id']) ? 'tak' : 'nie'),
                                         'domyslny_adres_dostawy' => (((int)$info['customers_default_shipping_address_id'] > 0 && (int)$info['customers_default_shipping_address_id'] == (int)$info['address_book_id']) ? 'tak' : 'nie') );
                    
        }
        
        unset($info);
      
    }
    
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);

} else {

    Funkcje::PrzekierowanieSSL( 'logowanie.html' );
    
}

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI','KLIENCI_PANEL','REJESTRACJA') ), $GLOBALS['tlumacz'] );

// meta tagi
$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta);

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['PANEL_KLIENTA'],Seo::link_SEO('panel_klienta.php', '', 'inna'));
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_ADRESY_DOSTAWY']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

// wyglad srodkowy
$srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $TablicaAdresow);

if ( WYBOR_KRAJU_DOSTAWY == 'tak' ) {
    $tablicaPanstw = Klient::ListaPanstw();
} else {
    $tablicaPanstw = Klient::ListaPanstwDymyslna();
}
$srodek->dodaj('__LISTA_PANSTW', Funkcje::RozwijaneMenu('panstwo', $tablicaPanstw, $_SESSION['krajDostawy']['id'], 'id="wybor_panstwo" style="width:80%"'));

$srodek->dodaj('__ID_KLIENTA', (int)$_SESSION['customer_id']);

$tablicaWojewodztw = Klient::ListaWojewodztw($_SESSION['krajDostawy']['id']);
$srodek->dodaj('__LISTA_WOJEWODZTW', '<span id="wybor_wojewodztwo_wynik">'.Funkcje::RozwijaneMenu('wojewodztwo', $tablicaWojewodztw, '', 'id="wybor_wojewodztwo" style="width:80%"').'</span>');

$srodek->dodaj('__TOKEN',Sesje::Token());

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

unset($srodek, $WywolanyPlik, $TablicaAdresow);

include('koniec.php');

?>