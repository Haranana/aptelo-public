<?php

// plik
$WywolanyPlik = 'haslo_przypomnienie';

include('start.php');

if (( (!isset($_SESSION['customer_id']) || (int)$_SESSION['customer_id'] == 0) && $_SESSION['gosc'] == '1' ) || ( (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' )) {

    $Zalogowany = 'nie';
    
    if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
         $Zalogowany = 'tak';
    }

    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('LOGOWANIE') ), $GLOBALS['tlumacz'] );

    // meta tagi
    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    $tpl->dodaj('__META_TYTUL', $Meta['tytul']);
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
    $tpl->dodaj('__META_OPIS', $Meta['opis']);
    unset($Meta);

    // breadcrumb
    $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PRZYPOMNIENIE_HASLA']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $Zalogowany);

    $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

    unset($srodek, $WywolanyPlik, $Zalogowany);
    
} else {

    Funkcje::PrzekierowanieSSL( '' );

}    

include('koniec.php');

?>