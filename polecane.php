<?php

// plik
$WywolanyPlik = 'polecane';

include('start.php');

$LinkDoPrzenoszenia = Seo::link_SEO('polecane.php', '', 'inna');

// *****************************
// jezeli byla zmiana sposobu wyswietlania, sortowanie lub zmiana ilosci produktow na stronie - musi przeladowac strone
if (isset($_POST['wyswietlanie']) || isset($_POST['sortowanie']) || isset($_POST['ilosc_na_stronie'])) {
    Funkcje::PrzekierowanieURL($LinkDoPrzenoszenia . Funkcje::Zwroc_Get(array('s'), false, '/'));
}    
// *****************************   

include('listing_gora.php');

$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
// meta tagi
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta);

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_POLECANE']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

$zapytanie = Produkty::SqlPolecaneZlozone($WarunkiFiltrowania, $Sortowanie);                           
//
$sql = $GLOBALS['db']->open_query($zapytanie);
//

// filtr nowosci
if (POKAZUJ_FILTRY_NOWOSCI == 'tak') {
    $srodek->dodaj('__FILTRY_NOWOSCI', Filtry::FiltrNowosciSelect('', 'polecane'));
} else {
    $srodek->dodaj('__FILTRY_NOWOSCI', '');
}

// filtr promocji
if (POKAZUJ_FILTRY_PROMOCJE == 'tak') {
    $srodek->dodaj('__FILTRY_PROMOCJE', Filtry::FiltrPromocjeSelect('', 'polecane'));        
} else {
    $srodek->dodaj('__FILTRY_PROMOCJE', '');
}

// filtry cech
if (POKAZUJ_FILTRY_CECH == 'tak') {
    $srodek->dodaj('__FILTRY_PO_CECHACH', Filtry::FiltrSelect( Filtry::FiltrCech('', 'polecane'), 'c' ));
} else {
    $srodek->dodaj('__FILTRY_PO_CECHACH', '');
}

// filtry dodatkowych pol
if (POKAZUJ_FILTRY_DODATKOWE_POLA == 'tak') {
    $srodek->dodaj('__FILTRY_PO_DODATKOWYCH_POLACH', Filtry::FiltrSelect( Filtry::FiltrDodatkowePola('', 'polecane'), 'p' ));     
} else {
    $srodek->dodaj('__FILTRY_PO_DODATKOWYCH_POLACH', ''); 
}

// filtr kategorii
if (POKAZUJ_FILTRY_KATEGORIE == 'tak') {
    $srodek->dodaj('__FILTRY_KATEGORIA', Filtry::FiltrKategoriiSelect( '', 'polecane' ));
} else {
    $srodek->dodaj('__FILTRY_KATEGORIA', '');
}

// filtr producenta
if (POKAZUJ_FILTRY_PRODUCENCI == 'tak') {
    $srodek->dodaj('__FILTRY_PRODUCENT', Filtry::FiltrProducentaSelect( '', 'polecane' ));
} else {
    $srodek->dodaj('__FILTRY_PRODUCENT', '');
}

// filtr dostepnosci
if (POKAZUJ_FILTRY_DOSTEPNOSCI == 'tak') {
    $srodek->dodaj('__FILTRY_DOSTEPNOSCI', Filtry::FiltrDostepnoscSelect( '', 'polecane' ));
} else {
    $srodek->dodaj('__FILTRY_DOSTEPNOSCI', '');
}    

// filtr czasu wysylki
if (POKAZUJ_FILTRY_CZAS_WYSYLKI == 'tak') {
    $srodek->dodaj('__FILTRY_CZAS_WYSYLKI', Filtry::FiltrCzasWysylkiSelect( '', 'polecane' ));
} else {
    $srodek->dodaj('__FILTRY_CZAS_WYSYLKI', '');
}  

include('listing_dol.php');

include('koniec.php');

?>