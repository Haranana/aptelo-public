<?php

// plik
$WywolanyPlik = 'schowek';

include('start.php');

if ( PRODUKT_SCHOWEK_STATUS == 'nie' ) {

    Funkcje::PrzekierowanieURL('brak-strony.html'); 

}

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('SCHOWEK') ), $GLOBALS['tlumacz'] );

//
// wyglad srodkowy
$srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik));
//

// style css
$tpl->dodaj('__CSS_PLIK', ',listingi');

$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
// meta tagi
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta);

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_SCHOWEK']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

// stronicowanie
$srodek->dodaj('__STRONICOWANIE', '');
//
$IloscProduktow = 0;
if ( isset($GLOBALS['schowekKlienta']) ) {
     $IloscProduktow = $GLOBALS['schowekKlienta']->IloscProduktow;
}
//

// porownywanie produktow
$srodek->dodaj('__PRODUKTY_DO_POROWNANIA', '');
$srodek->dodaj('__CSS_POROWNANIE', 'style="display:none"');
$srodek->dodaj('__CSS_PRZYCISK_POROWNANIE', 'style="display:none"');
if ( isset($_SESSION['produktyPorownania']) && count($_SESSION['produktyPorownania']) > 0 && LISTING_POROWNYWARKA_PRODUKTOW == 'tak' ) {
    //
    $DoPorownaniaId = '';
    foreach ($_SESSION['produktyPorownania'] AS $Id) {
        $DoPorownaniaId .= $Id . ',';
    }
    $DoPorownaniaId = substr((string)$DoPorownaniaId, 0, -1);
    //
    $zapNazwy = Produkty::SqlPorownanieProduktow($DoPorownaniaId);
    //
    $sqlNazwy = $GLOBALS['db']->open_query($zapNazwy);
    //
    $DoPorownaniaLinki = '';
    
    if ((int)$GLOBALS['db']->ile_rekordow($sqlNazwy) > 0) {
      
        while ($infc = $sqlNazwy->fetch_assoc()) {
            //
            // ustala jaka ma byc tresc linku
            $linkSeo = ((!empty($infc['products_seo_url'])) ? $infc['products_seo_url'] : $infc['products_name']);
            //
            $DoPorownaniaLinki .= '<div class="PozycjaDoPorownania"><span role="button" tabindex="0" onclick="Porownaj(' . $infc['products_id'] . ',\'wy\')"></span><a href="' . Seo::link_SEO( $linkSeo, $infc['products_id'], 'produkt' ) . '">' . $infc['products_name'] . '</a></div>';
            //    
            unset($linkSeo);
            //
            // sprawdza czy produkt nie zostal wylaczony - jezeli tak usunie go z porownania
            if ( $infc['products_status'] == '0' ) {
                 unset($_SESSION['produktyPorownania'][$infc['products_id']]);
                 Funkcje::PrzekierowanieURL('schowek.html');
            }
            //        
        }
        
        unset($infc);
        
    }
    
    $GLOBALS['db']->close_query($sqlNazwy); 
    unset($zapNazwy, $DoPorownaniaId);      
    //
    $srodek->dodaj('__PRODUKTY_DO_POROWNANIA', $DoPorownaniaLinki);
    $srodek->dodaj('__CSS_POROWNANIE', '');
    //
    unset($DoPorownaniaLinki);
    //
    // jezeli jest wiecej niz 1 produkt do porownania to pokaze przycisk
    if ( isset($_SESSION['produktyPorownania']) && count($_SESSION['produktyPorownania']) > 1) {
        $srodek->dodaj('__CSS_PRZYCISK_POROWNANIE', 'style="display:block"');
    }
    //
}

ob_start();

if (in_array( 'listing_schowek.php', $Wyglad->PlikiListingiLokalne )) {
    require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_schowek.php');
  } else {
    require('listingi/listing_schowek.php');
}
    
$ListaProduktow = ob_get_contents();
ob_end_clean();        

// jezeli ceny tylko dla zalogowanych
if ( CENY_DLA_WSZYSTKICH == 'nie' && ((int)$_SESSION['customer_id'] == 0 || $_SESSION['gosc'] == '1')) {

    $srodek->dodaj('__WARTOSC_PRODUKTOW_SCHOWKA', '<span class="CenaDlaZalogowanych">' . $GLOBALS['tlumacz']['CENA_TYLKO_DLA_ZALOGOWANYCH'] . '</span>');

  } else {

    $WartoscSchowka = 0;
    if ( isset($GLOBALS['schowekKlienta']) ) {
         $WartoscSchowka = $GLOBALS['schowekKlienta']->WartoscProduktowSchowka();
    }
    $srodek->dodaj('__WARTOSC_PRODUKTOW_SCHOWKA', $GLOBALS['waluty']->PokazCene($WartoscSchowka['brutto'], $WartoscSchowka['netto'], 0, $_SESSION['domyslnaWaluta']['id'], CENY_BRUTTO_NETTO, false));
    unset($WartoscSchowka);
    
}

$srodek->dodaj('__LISTA_PRODUKTOW', $ListaProduktow);   

unset($IloscProduktow, $ListaProduktow); 

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
unset($srodek, $WywolanyPlik);

include('koniec.php');

?>