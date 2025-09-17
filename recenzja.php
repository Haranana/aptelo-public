<?php

// plik
$WywolanyPlik = 'recenzja';

include('start.php');

if ( RECENZJE_STATUS == 'nie' ) {

    Funkcje::PrzekierowanieURL('brak-strony.html'); 

}

$sql = $GLOBALS['db']->open_query( Produkty::SqlRecenzja((int)$_GET['id']) );

if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 

    // breadcrumb
    $nawigacja->dodaj($GLOBALS['tlumacz']['OPINIA_O_PRODUKCIE']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));
    //
    $info = $sql->fetch_assoc();

    // klasa produktu
    $Produkt = new Produkt( $info['products_id'] );
    // recenzje produktu
    $Produkt->ProduktRecenzje();
    
    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $Produkt->recenzje[(int)$_GET['id']]['recenzja_odpowiedz']);
    //
    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('SYSTEM_PUNKTOW') ), $GLOBALS['tlumacz'] );

    // sprawdzenie linku SEO z linkiem w przegladarce
    Seo::link_Spr(Seo::link_SEO($Produkt->info['nazwa_seo'], (int)$_GET['id'], 'recenzja'));

    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    
    // meta tagi
    if ( $Meta['nazwa_pliku'] != null ) { 
         //     
         $tpl->dodaj('__META_TYTUL', MetaTagi::MetaTagiRecenzjaPodmien('tytul', $Produkt, $Meta['tytul'], (int)$_GET['id']));
         $tpl->dodaj('__META_SLOWA_KLUCZOWE', MetaTagi::MetaTagiRecenzjaPodmien('slowa', $Produkt, $Meta['slowa'], (int)$_GET['id']));
         $tpl->dodaj('__META_OPIS', MetaTagi::MetaTagiRecenzjaPodmien('opis', $Produkt, $Meta['opis'], (int)$_GET['id']));
         //
      } else {
         //
         $tpl->dodaj('__META_TYTUL', $GLOBALS['tlumacz']['OPINIA_O_PRODUKCIE'] . ' ' . $Produkt->info['nazwa'] . ' ' . $GLOBALS['tlumacz']['OPINIA_O_PRODUKCIE_NAPISANA_PRZEZ'] . ' ' . $Produkt->recenzje[(int)$_GET['id']]['recenzja_oceniajacy']);
         $tpl->dodaj('__META_SLOWA_KLUCZOWE', ((empty($Produkt->meta_tagi['slowa'])) ? $Meta['slowa'] : $Produkt->meta_tagi['slowa']));
         $tpl->dodaj('__META_OPIS', ((empty($Produkt->meta_tagi['opis'])) ? $Meta['opis'] : $Produkt->meta_tagi['opis']));
         //         
    }    
    unset($Meta);    
    
    // link kanoniczny dla produktu
    if ( $Produkt->metaTagi['link_kanoniczny'] != '' ) {
         //
         $tpl->dodaj('__LINK_CANONICAL', '<link rel="canonical" href="' . ADRES_URL_SKLEPU . '/' . $Produkt->metaTagi['link_kanoniczny'] . '" />');    
         //
    } else {
         //
         $tpl->dodaj('__LINK_CANONICAL', '<link rel="canonical" href="' . ADRES_URL_SKLEPU . '/' . $Produkt->info['adres_seo'] . '" />');  
         //
    }    
    //
    $srodek->dodaj('__NAZWA_PRODUKTU', $Produkt->info['nazwa']);
    $srodek->dodaj('__TRESC_RECENZJI', $Produkt->recenzje[(int)$_GET['id']]['recenzja_tekst']);
    $srodek->dodaj('__TRESC_ODPOWIEDZI_SKLEPU', $Produkt->recenzje[(int)$_GET['id']]['recenzja_odpowiedz']);
    $srodek->dodaj('__ZDJECIE_PRODUKTU', $Produkt->fotoGlowne['zdjecie_link_ikony']);
    $srodek->dodaj('__AUTOR_RECENZJI', $Produkt->recenzje[(int)$_GET['id']]['recenzja_oceniajacy']);
    $srodek->dodaj('__DATA_DODANIA', $Produkt->recenzje[(int)$_GET['id']]['recenzja_data_dodania']);
    $srodek->dodaj('__DATA_DODANIA_TIME', date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($Produkt->recenzje[(int)$_GET['id']]['recenzja_data_dodania'])));
    //
    // ocena produktu
    $srodek->dodaj('__OCENA_RECENZJI_TEKST', '<span>'.$Produkt->recenzje[(int)$_GET['id']]['recenzja_ocena'].'</span>' . '/5');
    $srodek->dodaj('__OCENA_RECENZJI_GWIAZDKI', $Produkt->recenzje[(int)$_GET['id']]['recenzja_ocena_obrazek']);
    //
    // srednia ocena
    $srodek->dodaj('__SREDNIA_OCENA_RECENZJI_TEKST', $Produkt->recenzjeSrednia['srednia_ocena'] . '/5');
    $srodek->dodaj('__SREDNIA_OCENA_RECENZJI_GWIAZDKI', $Produkt->recenzjeSrednia['srednia_ocena_obrazek']);
    $srodek->dodaj('__ILOSC_WSZYSTKICH_RECENZJI', $Produkt->recenzjeSrednia['ilosc_glosow']);
    //
    // potwierdzony zakup
     $srodek->dodaj('__POTWIERDZONY_ZAKUP', '');
    if ( $Produkt->recenzje[(int)$_GET['id']]['potwierdzony_zakup'] == 'tak' ) {
         $srodek->dodaj('__POTWIERDZONY_ZAKUP', '<div class="InformacjaOk RecenzjaPotwierdzonyZakup" style="margin-bottom:20px;font-weight:bold">' . (string)$GLOBALS['tlumacz']['RECENZJA_POTWIERDZONA'] . '</div>');
    }
    //
    $srodek->dodaj('__LINK_DO_PRODUKTU', $Produkt->info['adres_seo']);
    //
    // system punktow
    $srodek->dodaj('__INFO_O_PUNKTACH_RECENZJI', '');
    if ( SYSTEM_PUNKTOW_STATUS == 'tak' && (int)SYSTEM_PUNKTOW_PUNKTY_RECENZJE > 0 && Punkty::PunktyAktywneDlaKlienta() ) {
        $srodek->dodaj('__INFO_O_PUNKTACH_RECENZJI', str_replace('{ILOSC_PUNKTOW}', (string)SYSTEM_PUNKTOW_PUNKTY_RECENZJE, (string)$GLOBALS['tlumacz']['PUNKTY_RECENZJE']));
    }
    //    
    $GLOBALS['db']->close_query($sql); 
    unset($Produkt, $info);
    //
    $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
    //
  } else {
    //
    $GLOBALS['db']->close_query($sql); 
    //
    header('HTTP/1.1 404 Not Found');
    //
    // plik
    $WywolanyPlik = 'brak_strony';

    // ponowne wywolanie modulow - czy sa jakies do wyswietlania na stronie braku-strony
 
    $tpl->dodaj('__MODULY_SRODKOWE_GORA', $Wyglad->SrodekSklepu( 'gora', array(1,3,4)));
    $tpl->dodaj('__MODULY_SRODKOWE_DOL', $Wyglad->SrodekSklepu( 'dol', array(1,3,4) ));

    $tpl->dodaj('__MODULY_SRODKOWE_PODSTRONA_GORA', $Wyglad->SrodekSklepu( 'srodek', array(1,3,4), 'gora' ));
    $tpl->dodaj('__MODULY_SRODKOWE_PODSTRONA_DOL', $Wyglad->SrodekSklepu( 'srodek', array(1,3,4), 'dol' ));

    $Meta = MetaTagi::ZwrocMetaTagi( 'brak_strony.php' );
    // meta tagi
    $tpl->dodaj('__META_TYTUL', $Meta['tytul']);
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
    $tpl->dodaj('__META_OPIS', $Meta['opis']);
    unset($Meta);

    // wyglad srodkowy
    $srodek = new Szablony( $Wyglad->TrescLokalna($WywolanyPlik) ); 

    $srodek->dodaj('__NAGLOWEK_INFORMACJI', $GLOBALS['tlumacz']['BRAK_DANYCH_DO_WYSWIETLENIA']);

    $srodek->dodaj('__KOMUNIKAT',$GLOBALS['tlumacz']['BLAD_NIE_ZNALEZIONO_RECENZJI']);
    $nawigacja->dodaj($GLOBALS['tlumacz']['BLAD_NIE_ZNALEZIONO_RECENZJI']);

    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));
    
    $tpl->dodaj('__JS_PLIK', '');

    $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

    unset($srodek, $WywolanyPlik); 
    
}

unset($srodek, $WywolanyPlik);

include('koniec.php');

?>