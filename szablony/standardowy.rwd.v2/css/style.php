<?php
if ( !defined('DOMYSLNY_SZABLON') ) {
     //
     die('Brak odczytu ...');
     //
}

// nazwa pliku cache - str glowna 
$NazwaPlikuCache = 'cache/Cache_CssSzablonGlowna.css';

// jakie pliki do zapisu
$CssDoZaladowania = array('font.css',
                          'style.css',
                          'moduly.css',
                          'banner_popup.css');
                          
// jezeli nie jest strona glowna                          
if ( $GLOBALS['stronaGlowna'] != true ) {
            
     // nazwa pliku cache 
     $NazwaPlikuCache = 'cache/Cache_CssSzablonPodstrony.css';
     
     // jakie pliki do zapisu
     $CssDoZaladowania[] = 'listingi.css';
     $CssDoZaladowania[] = 'podstrony.css';
     $CssDoZaladowania[] = 'produkt.css';
     
}

// jezeli jest wlaczona lewa lub prawa kolumna z boxami
if ( CZY_WLACZONA_LEWA_KOLUMNA == 'tak' || CZY_WLACZONA_PRAWA_KOLUMNA == 'tak' ) {
     //
     $CssDoZaladowania[] = 'boxy.css';
     //
}

// jezeli jest wlaczony modul opinii
if ( OPINIE_STATUS == 'tak' ) {
     //
     $CssDoZaladowania[] = 'opinie.css';
     //
}

// jezeli jest wlaczona integracja z easyprotect
if ( INTEGRACJA_EASYPROTECT_WLACZONY == 'tak' ) {
     //
     $CssDoZaladowania[] = 'easyprotect.css';
     //
}

// pliki css modulow stalych
if ( isset($Wyglad->PlikiModulyStalePliki) ) {
     //
     if ( count($Wyglad->PlikiModulyStalePliki) > 0 ) {
          //
          $DostepneCss = array();
          //
          foreach ( $Wyglad->PlikiModulyStalePliki as $CssPlik ) {
              //
              if ( file_exists('szablony/' . DOMYSLNY_SZABLON . '/css/' . $CssPlik . '.css') ) {
                   //
                   $CssDoZaladowania[] = $CssPlik . '.css';
                   //
              }
              //
          }
          //         
          unset($DostepneCss);
          //
     }
     //
}

// wyglad zakladek wysuwanych
if ( ( ZAKLADKA_FACEBOOK_WLACZONA == 'tak' ||
       ZAKLADKA_GG_WLACZONA == 'tak' ||
       ZAKLADKA_YOUTUBE_WLACZONA == 'tak' ||
       ZAKLADKA_PINTEREST_WLACZONA == 'tak' ||
       ZAKLADKA_TWITTER_WLACZONA == 'tak' ||
       ZAKLADKA_INSTAGRAM_WLACZONA == 'tak' ||
       ZAKLADKA_ALLEGRO_OPINIE_WLACZONA == 'tak' ||
       ZAKLADKA_PIERWSZA_WLACZONA == 'tak' ||
       ZAKLADKA_DRUGA_WLACZONA == 'tak' ||
       ZAKLADKA_TRZECIA_WLACZONA == 'tak' || 
       ZAKLADKA_OPINIE_WLACZONA == 'tak' ) ) {
       //
       $CssDoZaladowania[] = 'wysuwane_zakladki.css';
       //
}

// jezeli sa pliki
if (count($CssDoZaladowania) > 0) {

    $cacheFile    = $NazwaPlikuCache;
    $cacheMissing = !file_exists($cacheFile);
    $cacheMTime   = $cacheMissing ? 0 : filemtime($cacheFile);

    // Zbuduj listę wszystkich plików, które trafiają do cache
    $srcPaths = [];

    // 1) CSS szablonu
    foreach ($CssDoZaladowania as $Plik) {
        $p = 'szablony/' . DOMYSLNY_SZABLON . '/css/' . $Plik;
        $srcPaths[] = $p;
    }

    // 2) CSS-y zewnętrzne dołączane w funkcji (też muszą wpływać na rebuild)
    $srcPaths[] = 'programy/zebraDatePicker/css/zebra_datepicker.css';
    $srcPaths[] = 'programy/slickSlider/slick.css';
    $srcPaths[] = 'programy/slickSlider/slick-theme.css';
    $srcPaths[] = 'programy/jBox/jBox.all.css';

    // Najnowsza modyfikacja wśród źródeł
    $latestSrcMTime = 0;
    foreach ($srcPaths as $p) {
        if (file_exists($p)) {
            $latestSrcMTime = max($latestSrcMTime, filemtime($p));
        }
    }

    // Rebuild, gdy cache nie istnieje lub jest starszy niż którykolwiek plik źródłowy
    $needsRebuild = $cacheMissing || ($cacheMTime < $latestSrcMTime);

    if ($needsRebuild) {
        SzablonZapiszCacheCss($cacheFile, $CssDoZaladowania, $tpl);
    }
}

function SzablonZapiszCacheCss($NazwaPlikuCache, $CssDoZaladowania, $tpl) {
    $parts = [];

    foreach ($CssDoZaladowania as $Plik) {
        $p = 'szablony/' . DOMYSLNY_SZABLON . '/css/' . $Plik;
        if (file_exists($p)) {
            $parts[] = "/* === $Plik === */\n" . file_get_contents($p);
        }
    }

    // zewnętrzne CSS-y
    $ext = [
        'programy/zebraDatePicker/css/zebra_datepicker.css',
        'programy/slickSlider/slick.css',
        'programy/slickSlider/slick-theme.css',
        'programy/jBox/jBox.all.css',
    ];
    //sd/
    foreach ($ext as $p) {
        if (file_exists($p)) {
            $parts[] = "/* === $p === */\n" . file_get_contents($p);
        }
    }

    $WynikCss = implode("\n\n", $parts);

    // kompresja
    $WynikCss = $tpl->cssCompress($WynikCss, (KOMPRESJA_CSS === 'tak'));

    // ZAPIS ATOMOWY (bez 'a+'):
    $tmp = $NazwaPlikuCache . '.tmp.' . uniqid('', true);
    file_put_contents($tmp, $WynikCss, LOCK_EX);
    rename($tmp, $NazwaPlikuCache);
    clearstatcache(true, $NazwaPlikuCache);
}       

?>