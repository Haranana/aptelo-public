<?php

// plik
$WywolanyPlik = 'producenci';

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
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PRODUCENCI']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

$TablicaProducentow = Producenci::TablicaProducenci();

ob_start();

if (in_array( 'listing_producenci.php', $Wyglad->PlikiListingiLokalne )) {
    require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_producenci.php');
  } else {
    require('listingi/listing_producenci.php');
}

$ListaProducentow = ob_get_contents();
ob_end_clean();        

$srodek->dodaj('__LISTA_PRODUCENTOW', $ListaProducentow);   

unset($ListaProducentow, $IloscProducentow); 

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
unset($srodek, $WywolanyPlik);

include('koniec.php');

?>