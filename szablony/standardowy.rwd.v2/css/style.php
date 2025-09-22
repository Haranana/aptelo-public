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
if ( count($CssDoZaladowania) > 0 ) {

    if ( !file_exists($NazwaPlikuCache) ) {
         //
         SzablonZapiszCacheCss($NazwaPlikuCache, $CssDoZaladowania, $DaneSzablonu = $tpl);
         //
    } else {
         //
         if ( file_exists($NazwaPlikuCache) ) {                 
              //
              if ( filemtime($NazwaPlikuCache) < (time() - (60)) ) {
                   //
                   SzablonZapiszCacheCss($NazwaPlikuCache, $CssDoZaladowania, $DaneSzablonu = $tpl);
                   //
              }
              //
         }
         //
     }
}

function SzablonZapiszCacheCss($NazwaPlikuCache, $CssDoZaladowania, $DaneSzablonu) {
    //
    $NazwaPlikuCache = fopen($NazwaPlikuCache,'a+');
    flock($NazwaPlikuCache, LOCK_EX);
    //
    fseek($NazwaPlikuCache, 0);
    //
    ftruncate($NazwaPlikuCache,0);
    //
    ob_start();
    //
    foreach ( $CssDoZaladowania as $Plik ) {
       //
       include( 'szablony/' . DOMYSLNY_SZABLON . '/css/' . $Plik );
       //
    }
    
    include('programy/zebraDatePicker/css/zebra_datepicker.css');
    
    include('programy/slickSlider/slick.css');
    include('programy/slickSlider/slick-theme.css');
    include('programy/jBox/jBox.all.css');

    $WynikCss = ob_get_contents();

    ob_end_clean(); 

    // jezeli jest wlaczona kompresja
    $WynikCss = $DaneSzablonu->cssCompress($WynikCss, ((KOMPRESJA_CSS == 'tak') ? true : false));         
    //
    if ( fwrite($NazwaPlikuCache, $WynikCss) === false ) {
        //
        throw new Exception('Nie moge zapisac cache');
        //
    }
    //
    fclose($NazwaPlikuCache);               
    //
}               

?>