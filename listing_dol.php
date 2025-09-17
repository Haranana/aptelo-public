<?php
// stronicowanie
$srodek->dodaj('__STRONICOWANIE', '');
//
if ( isset($IloscProduktowSzukaj) ) {
     //
     $IloscProduktow = $IloscProduktowSzukaj;
     //
  } else {
     //
     $IloscProduktow = (int)$GLOBALS['db']->ile_rekordow($sql);
     //
}

$IloscProduktowIntegracje = $IloscProduktow;

$srodek->dodaj('__ILOSC_PRODUKTOW_OGOLEM', $IloscProduktow);

$LinkPrev = '';
$LinkNext = '';

if ($IloscProduktow > 0) { 
    //
    $Strony = Stronicowanie::PokazStrony( ((isset($IloscProduktowSzukaj)) ? $IloscProduktowSzukaj : $sql), $LinkDoPrzenoszenia, 0, LISTING_PRODUKTOW_OSTATNIA_POZYCJA );
    //
    $LinkPrev = ((!empty($Strony[2])) ? "\n" . $Strony[2] : '');
    $LinkNext = ((!empty($Strony[3])) ? "\n" . $Strony[3] : '');    
    //
    $LinkiDoStron = $Strony[0];
    $LimitSql = $Strony[1];
    //
    $srodek->dodaj('__STRONICOWANIE', $LinkiDoStron);
    //
    // zabezpieczenie zeby nie mozna bylo wyswietlic wiecej niz ilosc na stronie x 3
    if ( $_SESSION['listing_produktow'] > LISTING_PRODUKTOW_NA_STRONIE * 3 ) {
         $_SESSION['listing_produktow'] = LISTING_PRODUKTOW_NA_STRONIE * 3;
    }
    //
    if ( LISTING_PRODUKTOW_OSTATNIA_POZYCJA == 'nie' ) {
      
         $zapytanie = $zapytanie . " LIMIT " . $LimitSql . "," . $_SESSION['listing_produktow'];
         
    } else {
      
         $KtoraStrona = (int)($LimitSql / LISTING_PRODUKTOW_NA_STRONIE);

         $zapytanie = $zapytanie . " LIMIT " . (($LimitSql > 0) ? ($LimitSql - $KtoraStrona) : 0) . "," . ($_SESSION['listing_produktow'] - 1);

         $KolejnaStronaOkno = '';
        
         if ( $LinkNext != '' ) {
              if ( isset($Strony[4]) && $Strony[4] != '' ) {
                   $KolejnaStronaOkno = $Strony[4];
              }
         }
         
         unset($KtoraStrona);
        
    }

    //
    // zapytanie dla integracji jezeli nie bedzie informacji z listingu
    $zapytanieIntegracje = $zapytanie;
    //
    // jezeli nie jest to strona szukaj.php
    if ( !isset($ZnalezionoProdukty) ) {
         $GLOBALS['db']->close_query($sql);
    }
    //            
    $sql = $GLOBALS['db']->open_query($zapytanie);
    //
    unset($Strony, $LinkiDoStron, $LimitSql);
    //
    $IloscProduktow = (int)$GLOBALS['db']->ile_rekordow($sql);
    //
}
//

// przycisk usuniecia filtrow
if (isset($WarunkiFiltrowania) && $WarunkiFiltrowania != '') {
    $srodek->dodaj('__LINK_USUNIECIA_FILTROW', '<a href="' . $LinkDoPrzenoszenia . '">' . $GLOBALS['tlumacz']['LISTING_USUN_FILTRY'] . '</a>');
} else {
    $srodek->dodaj('__LINK_USUNIECIA_FILTROW', '');
}

// dane produktow listingu
$WyswietlaneProdukty = array();
   
ob_start();

// jezeli sposob wyswietlania okienka
if ($SposobWyswietlania == 1) {
    //
    if (in_array( 'listing_okienka.php', $Wyglad->PlikiListingiLokalne )) {
        require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_okienka.php');
      } else {
        require('listingi/listing_okienka.php');
    }
    //
}

// jezeli sposob wyswietlania wiersze
if ($SposobWyswietlania == 2) {
    //
    if (in_array( 'listing_wiersze.php', $Wyglad->PlikiListingiLokalne )) {
        require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_wiersze.php');
      } else {
        require('listingi/listing_wiersze.php');
    }
    //
}

// jezeli sposob wyswietlania lista
if ($SposobWyswietlania == 3) {
    //
    if (in_array( 'listing_lista.php', $Wyglad->PlikiListingiLokalne )) {
        require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_lista.php');
      } else {
        require('listingi/listing_lista.php');
    }
    //
}

$ListaProduktow = ob_get_contents();
ob_end_clean();        

$srodek->dodaj('__LISTA_PRODUKTOW', $ListaProduktow);

// jezeli w listingu nie bylo generowania tablicy produktow
if ( $IloscProduktowIntegracje > 0 && count($WyswietlaneProdukty) == 0 ) {
     //
     $sql = $GLOBALS['db']->open_query($zapytanieIntegracje);
     //
     while ($info = $sql->fetch_assoc()) {
        //
        $Produkt = new Produkt( $info['products_id'] );
        $WyswietlaneProdukty[ $info['products_id'] ] = $Produkt;
        unset($Produkt);
        //
     }
     //
     $GLOBALS['db']->close_query($sql);
     unset($info);
     //
}

unset($zapytanieIntegracje);

// produkty do remarketingu Google i google analytics
$wynikGoogle = IntegracjeZewnetrzne::GoogleAnalyticsRemarketingListingDol( $WyswietlaneProdukty, $WywolanyPlik );
$tpl->dodaj('__GOOGLE_KONWERSJA', $wynikGoogle['konwersja']);
$tpl->dodaj('__GOOGLE_ANALYTICS', $wynikGoogle['analytics']);
unset($wynikGoogle);

// integracja z DomodiPixel
$srodek->dodaj('__DOMODI_PIXEL', IntegracjeZewnetrzne::DomodiPixelListingDol( $WyswietlaneProdukty ));

// integracja z WP Pixel
$srodek->dodaj('__WP_PIXEL_KOD', IntegracjeZewnetrzne::WpPixelListingDol( $WyswietlaneProdukty ));
   
unset($WyswietlaneProdukty, $IloscProduktowIntegracje);
   
// czy jest kolejna strona
$TablicaGet = array();
$TablicaGet = $_GET;
$ParametrStrony = '';

$CzyParametry = Funkcje::CzySaParametry( $TablicaGet );

if ( !$CzyParametry || SEO_CANONICAL == 'tak' ) {

    if ( ( isset($_GET['s']) && (int)$_GET['s'] > 1 ) && !$CzyParametry ) {
         //
         $ParametrStrony = '/s=' . (int)$_GET['s'];
         //
    }

    $tpl->dodaj('__LINK_CANONICAL', '<link rel="canonical" href="' . ADRES_URL_SKLEPU . '/' . ((isset($LinkKanonicznyKategoria) && $LinkKanonicznyKategoria != '') ? $LinkKanonicznyKategoria : $LinkDoPrzenoszenia . $ParametrStrony) . '" />' . $LinkPrev . $LinkNext);

} else {

    $tpl->dodaj('__LINK_CANONICAL', '');

}

unset($TablicaGet, $CzyParametry);

unset($ParametrStrony, $LinkDoPrzenoszenia, $IloscProduktow, $ListaProduktow, $Sortowanie, $TablicaSortowania, $SposobWyswietlania);

// wyglad srodkowy
$tpl->dodaj('__SRODKOWA_KOLUMNA',$srodek->uruchom());
unset($srodek);

?>