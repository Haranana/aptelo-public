<?php

if ( !isset($_GET['kod']) ) {
     $KodBledu = 'BLAD_404';
  } else {
     $KodBledu = $_GET['kod'];
}

//
switch($KodBledu) {
  case 'BLAD_400':
      header('HTTP/1.1 400 Bad Request');
      break;
  case 'BLAD_401':
      header('HTTP/1.1 401 Unauthorized');
      break;
  case 'BLAD_403':
      header('HTTP/1.1 403 Forbidden');
      break;
  case 'BLAD_404':
      header('HTTP/1.1 404 Not Found');
      break;
  case 'BLAD_500':
      header('HTTP/1.1 500 Internal Server Error');
      break;
  case 'BLAD_503':
      header('HTTP/1.1 503 Service Unavailable');
      break;
  default:
      header('HTTP/1.1 404 Not Found');
      break;
}
  
// plik
$WywolanyPlik = 'brak_strony';

include('start.php');

if ( isset($_GET['kod']) && $_GET['kod'] != '' ) {
    $kodBledu = $_GET['kod'];
}
$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
// meta tagi
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta);

// wyglad srodkowy
$srodek = new Szablony( $Wyglad->TrescLokalna($WywolanyPlik) ); 

$srodek->dodaj('__NAGLOWEK_INFORMACJI', $GLOBALS['tlumacz']['BRAK_DANYCH_DO_WYSWIETLENIA']);

if ( isset($_GET['produkt']) ) {
     $srodek->dodaj('__KOMUNIKAT',$GLOBALS['tlumacz']['BLAD_NIE_ZNALEZIONO_PRODUKTU']);
     $nawigacja->dodaj($GLOBALS['tlumacz']['BLAD_NIE_ZNALEZIONO_PRODUKTU']);
}

if ( isset($kodBledu) ) {
     //
     if (isset($GLOBALS['tlumacz'][$kodBledu])) {
         //
         $srodek->dodaj('__KOMUNIKAT',$GLOBALS['tlumacz'][$kodBledu]);
         $nawigacja->dodaj($GLOBALS['tlumacz'][$kodBledu]);
         //
      } else {
         //
         Funkcje::PrzekierowanieURL('brak-strony.html'); 
         //
     }
}
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

unset($srodek, $WywolanyPlik);

include('koniec.php');

?>