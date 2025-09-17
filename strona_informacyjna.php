<?php

// plik
$WywolanyPlik = 'strona_informacyjna';

include('start.php');

$Strona = StronyInformacyjne::StronaInfoId( (int)$_GET['id'] );

if (!empty($Strona)) { 
    //
    // sprawdzenie linku SEO z linkiem w przegladarce
    Seo::link_Spr( Seo::link_SEO( $Strona['tytul'], $Strona['id'], 'strona_informacyjna' ) );    

    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    // meta tagi
    $tpl->dodaj('__META_TYTUL', ((empty($Strona['meta_tytul'])) ? $Meta['tytul'] : $Strona['meta_tytul']));
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', ((empty($Strona['meta_slowa'])) ? $Meta['slowa'] : $Strona['meta_slowa']));
    $tpl->dodaj('__META_OPIS', ((empty($Strona['meta_opis'])) ? $Meta['opis'] : $Strona['meta_opis']));
    unset($Meta);

    // breadcrumb
    $nawigacja->dodaj($Strona['tytul']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik));
    //
    $srodek->dodaj('__NAGLOWEK_STRONY',$Strona['tytul']);
    //
    // czy pokazywac skrocony opis strony
    if ( STRONY_INFO_SKROCONY_OPIS == 'tak' ) {
        $srodek->dodaj('__TRESC_STRONY_KROTKI',$Strona['opis_krotki'] . '<br /><br />');
      } else {
        $srodek->dodaj('__TRESC_STRONY_KROTKI','');
    }
    //
    $srodek->dodaj('__TRESC_STRONY',$Strona['opis']);
    //
    $indeksowanie_meta = (($Strona['nofollow'] == 1) ? true : false);
    //
  } else {
    //
    unset($WywolanyPlik);    
    //
    Funkcje::PrzekierowanieURL('brak-strony.html', true); 
    //
}

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
unset($srodek, $WywolanyPlik, $Strona);

include('koniec.php');

?>