<?php

// plik
$WywolanyPlik = 'wyszukiwanie_zaawansowane';

include('start.php');

// sprawdzenie czy sa dodatkowe pola do wyszukiwania
$DodatkowePola = array();

// cache zapytania
$WynikCache = $GLOBALS['cache']->odczytaj('DodatkowePolaWyszukiwanie_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);      

if ( !$WynikCache && !is_array($WynikCache) ) { 

    $zapytanie = "SELECT products_extra_fields_id, products_extra_fields_name FROM products_extra_fields WHERE products_extra_fields_status = '1' AND products_extra_fields_search = '1' AND (languages_id = '0' OR languages_id = '".(int)$_SESSION['domyslnyJezyk']['id']."') ORDER BY products_extra_fields_order";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
    
        while ($info = $sql->fetch_assoc()) {
            $DodatkowePola[$info['products_extra_fields_id']] = $info['products_extra_fields_name'];
        }
        
        unset($info);
        
    }

    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);
    
    $GLOBALS['cache']->zapisz('DodatkowePolaWyszukiwanie_' . $_SESSION['domyslnyJezyk']['kod'], $DodatkowePola, CACHE_INNE);
    
  } else {
 
   $DodatkowePola = $WynikCache;

}     

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('WYSZUKIWANIE_ZAAWANSOWANE') ), $GLOBALS['tlumacz'] );

$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
// meta tagi
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta);

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_WYSZUKIWANIE_ZAAWANSOWANE']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

// wyglad srodkowy
$srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $DodatkowePola);

// select z producentami
$srodek->dodaj('__WYBOR_PRODUCENTA', Funkcje::RozwijaneMenu('producent', Producenci::TablicaProducenciSelect(), '', ' id="producent"'));

// select z kategoriami
$srodek->dodaj('__WYBOR_KATEGORIA', Funkcje::RozwijaneMenu('kategoria', Kategorie::TablicaKategorieParent(0, $GLOBALS['tlumacz']['LISTING_WYBIERZ_OPCJE']), '', ' id="kategoria"'));

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

unset($srodek, $WywolanyPlik);

include('koniec.php');

?>