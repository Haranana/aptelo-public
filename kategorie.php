<?php

// plik
$WywolanyPlik = 'kategorie';

include('start.php');

//
// wyglad srodkowy
$srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik));
//

$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
// meta tagi
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta);

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_KATEGORIE']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

$TablicaKategorii = Kategorie::DrzewoKategorii(0);

ob_start();

if (in_array( 'listing_kategorie.php', $Wyglad->PlikiListingiLokalne )) {
    require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_kategorie.php');
  } else {
    require('listingi/listing_kategorie.php');
}  

$TablicaKategorii = ob_get_contents();
ob_end_clean();        

$srodek->dodaj('__LISTA_KATEGORII', $TablicaKategorii);   

unset($TablicaKategorii, $IloscProducentow); 

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
unset($srodek, $WywolanyPlik);

include('koniec.php');

?>