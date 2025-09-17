<?php

// plik
$WywolanyPlik = 'cennik';

include('start.php');

// sprawdzi czy jest wlaczony box cennika
$zapytanie = "SELECT box_status FROM theme_box WHERE box_status = '1' AND box_file = 'cennik.php'";
$sql = $GLOBALS['db']->open_query($zapytanie);

if ( (int)$GLOBALS['db']->ile_rekordow($sql) == 0 ) {
     //
     Funkcje::PrzekierowanieURL('brak-strony.html'); 
     // 
}

$GLOBALS['db']->close_query($sql); 
unset($zapytanie);

//
// wyglad srodkowy
$srodek = new Szablony( $Wyglad->TrescLokalna($WywolanyPlik) ); 
//

$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
// meta tagi
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta);

$Format = '';
// cennik html
if ( isset($_GET['typ']) ) {
    //
    if ( ( !defined('BOX_CENNIK_HTML') || BOX_CENNIK_HTML == 'tak' ) && strtolower((string)$_GET['typ']) == 'html' ) {
        //
        $Format = 'Html';
        //
    }
    if ( ( !defined('BOX_CENNIK_PDF') || BOX_CENNIK_PDF == 'tak' ) && strtolower((string)$_GET['typ']) == 'pdf' ) {
        //
        $Format = 'PDF';
        //
    }    
    if ( ( !defined('BOX_CENNIK_HTML') || BOX_CENNIK_XLS == 'tak' ) && strtolower((string)$_GET['typ']) == 'xls' ) {
        //
        $Format = 'XLS';
        //
    } 
}    

// jezeli nie jest zaden z formatow
if ( $Format == '' ) {
     //
     Funkcje::PrzekierowanieURL('brak-strony.html'); 
     // 
}

// generowanie pliku do pobrania
if ( isset($_GET['id']) && (int)$_GET['id'] > 0 ) {

    $zapytanie = Produkty::SqlProduktyCennik( (int)$_GET['id'] ); 
    
    // cennik html
    if ( strtolower((string)$Format) == 'html' ) {
    
        Cennik::CennikHtml( $zapytanie, (int)$_GET['id'] );
        
    }
    
    // cennik pdf
    if ( strtolower((string)$Format) == 'pdf' ) {
    
        Cennik::CennikPdf( $zapytanie, (int)$_GET['id'] );

    }   

    // cennik xls
    if ( strtolower((string)$Format) == 'xls' ) {
    
        Cennik::CennikXls( $zapytanie, (int)$_GET['id'] );

    }       

}

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_CENNIK'] . ' ' . $Format);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

$TablicaKategorii = '<ul>';
foreach(Kategorie::DrzewoKategorii() as $IdKategorii => $Tablica) {
    $TablicaKategorii .= Kategorie::WyswietlKategorieCennik($Tablica, strtolower((string)$Format), '');
}
$TablicaKategorii .= '</ul>';

$srodek->dodaj('__NAGLOWEK_CENNIK', $GLOBALS['tlumacz']['NAGLOWEK_CENNIK'] . ' ' . $Format);   

$srodek->dodaj('__LISTA_KATEGORII', $TablicaKategorii);   

unset($TablicaKategorii, $Format); 

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
unset($srodek, $WywolanyPlik);

include('koniec.php');

?>