<?php

// plik
$WywolanyPlik = 'zmiana_hasla';

include('start.php');

if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {

    $tablica = array();
    //
    $zapytanie = "SELECT customers_id, customers_firstname, customers_lastname, customers_email_address, customers_password FROM customers WHERE customers_id = '".(int)$_SESSION['customer_id']."' AND customers_guest_account = '0' AND customers_status = '1'";

    $sql = $GLOBALS['db']->open_query($zapytanie); 
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
    
        $info = $sql->fetch_assoc();
        
        $tablica = array('klient_id' => $info['customers_id'],
                         'imie' => $info['customers_firstname'],
                         'nazwisko' => $info['customers_lastname'],
                         'email' => $info['customers_email_address'],
                         'haslo' => $info['customers_password']);
                         
        unset($info);
                       
    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);

} else {

    Funkcje::PrzekierowanieSSL( 'logowanie.html' );

}

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI','KLIENCI_PANEL','REJESTRACJA') ), $GLOBALS['tlumacz'] );

$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
// meta tagi
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta);

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['PANEL_KLIENTA'],Seo::link_SEO('panel_klienta.php', '', 'inna'));
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_ZMIANA_HASLA']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

// wyglad srodkowy
$srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik));

foreach ($tablica as $key => $value) {
    $srodek->dodaj('__'.strtoupper((string)$key), $value);
}

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

unset($srodek, $WywolanyPlik, $tablica);

include('koniec.php');

?>