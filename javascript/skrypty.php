<?php
chdir('../');

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init_ajax.php');

if ( !isset($_SESSION['domyslnyJezyk']['kod']) ) { $_SESSION['domyslnyJezyk']['kod'] = 'pl'; }
if ( !isset($_SESSION['domyslnyJezyk']['id']) ) { $_SESSION['domyslnyJezyk']['id'] = '1'; }
if ( !isset($_SESSION['mobile_urzadzenie']) ) { $_SESSION['mobile_urzadzenie'] = 'tak'; }

$PlikCacheJs = 'cache/js/skrypty_' . $_SESSION['domyslnyJezyk']['kod'] . '.jcs';

// jezeli nie ma pliku cache lub cache jest wylaczone
if (!file_exists($PlikCacheJs) || CACHE_JS == 'nie') {

    include 'klasy/Jezyki.php';
    include 'klasy/Translator.php';
    include 'klasy/jsMin.php';

    $kod = '';
    $kod .= file_get_contents('programy/slickSlider/slick.min.js');

    $kod .= file_get_contents('programy/jBox/jBox.all.js');

    $kod .= file_get_contents('javascript/jquery.validate.jcs');
    
    if ( PRELOAD_OBRAZKOW == 'tak' ) {
        $kod .= file_get_contents('javascript/img_loader.jcs');    
    }    
    
    $kod .= file_get_contents('javascript/dostepnosc.jcs');
    $kod .= file_get_contents('javascript/skrypty.jcs');
    $kod .= file_get_contents('javascript/scrollTo.js');
    $kod .= file_get_contents('javascript/autouzupelnienie.jcs');
    
    // laduje tylko dla starych szablonow
    if ( strpos((string)DOMYSLNY_SZABLON, '.rwd.v') === false ) {
         //
         $kod .= file_get_contents('javascript/animacje.jcs');         
         //
    }

    if ( file_exists( 'szablony/' . DOMYSLNY_SZABLON . '/funkcje_mobilne.js' ) ) {
         $kod .= file_get_contents('szablony/' . DOMYSLNY_SZABLON . '/funkcje_mobilne.js');
    }
    
    if ( file_exists( 'szablony/' . DOMYSLNY_SZABLON . '/js/funkcje.js' ) ) {
         $kod .= file_get_contents('szablony/' . DOMYSLNY_SZABLON . '/js/funkcje.js');
    }
    
    $kod .= file_get_contents('javascript/moduly.jcs');
    
    if ( LISTING_POROWNYWARKA_PRODUKTOW == 'tak' ) {
        // porownywarka produktow
        $kod .= file_get_contents('javascript/porownywarka.jcs');
    }

    // walidacja newslettera
    $kod .= file_get_contents('javascript/newsletter.jcs');
    
    // walidacja boxu ankiety
    $kod .= file_get_contents('javascript/ankiety.jcs');

    // powiekszanie zdjecia po najechaniu kursorem 
    if ( ZDJECIE_LISTING_POWIEKSZENIE == 'tak' ) {
        $kod .= file_get_contents('javascript/oknoZdjecia.jcs');
    }

    // podkategorie przewijane 
    if ( strpos((string)DOMYSLNY_SZABLON, '.rwd.v') !== false && LISTING_PODKATEGORIE_PRZEWIJANIE == 'tak' ) {
        $kod .= file_get_contents('javascript/kategorie_slider.jcs');
    }

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
         // wysuwane zakladki
         $kod .= file_get_contents('javascript/zakladki.jcs');
         //
         foreach ( $StaleDefinicjeJs as $Wartosc ) {
            $kod = str_replace( '{__' . $Wartosc['kod'] . '}', (string)preg_replace('/\s+/', ' ', (string)$Wartosc['wartosc']), (string)$kod );
         }
         // wielkosci obrazkow dla indywidualnych zakladek
         if ( ZAKLADKA_PIERWSZA_IKONA != '' && file_exists(KATALOG_ZDJEC . '/' . ZAKLADKA_PIERWSZA_IKONA) ) {
              //
              list($szerokosc, $wysokosc) = getimagesize(KATALOG_ZDJEC . '/' . ZAKLADKA_PIERWSZA_IKONA);
              $kod = str_replace( '{__ZAKLADKA_PIERWSZA_IKONA_SZEROKOSC}', (string)$szerokosc, (string)$kod );
              $kod = str_replace( '{__ZAKLADKA_PIERWSZA_IKONA_WYSOKOSC}', (string)$wysokosc, (string)$kod );
              unset($szerokosc, $wysokosc);
              //
         }
         if ( ZAKLADKA_DRUGA_IKONA != '' && file_exists(KATALOG_ZDJEC . '/' . ZAKLADKA_DRUGA_IKONA) ) {
              //
              list($szerokosc, $wysokosc) = getimagesize(KATALOG_ZDJEC . '/' . ZAKLADKA_DRUGA_IKONA);
              $kod = str_replace( '{__ZAKLADKA_DRUGA_IKONA_SZEROKOSC}', (string)$szerokosc, (string)$kod );
              $kod = str_replace( '{__ZAKLADKA_DRUGA_IKONA_WYSOKOSC}', (string)$wysokosc, (string)$kod );
              unset($szerokosc, $wysokosc);
              //
         }
         if ( ZAKLADKA_TRZECIA_IKONA != '' && file_exists(KATALOG_ZDJEC . '/' . ZAKLADKA_TRZECIA_IKONA) ) {
              //
              list($szerokosc, $wysokosc) = getimagesize(KATALOG_ZDJEC . '/' . ZAKLADKA_TRZECIA_IKONA);
              $kod = str_replace( '{__ZAKLADKA_TRZECIA_IKONA_SZEROKOSC}', (string)$szerokosc, (string)$kod );
              $kod = str_replace( '{__ZAKLADKA_TRZECIA_IKONA_WYSOKOSC}', (string)$wysokosc, (string)$kod );
              unset($szerokosc, $wysokosc);
              //
         }         
         //
         $kod = str_replace( '{__DOMYSLNY_JEZYK}', (string)$_SESSION['domyslnyJezyk']['id'], (string)$kod );
         $kod = str_replace( '{__WYSUWANE_ZAKLADKI_WYSWIETLANIE}', (string)WYSUWANE_ZAKLADKI_WYSWIETLANIE, (string)$kod );
         $kod = str_replace( '{__WYSUWANE_ZAKLADKI_ODLEGLOSC_PX}', (string)WYSUWANE_ZAKLADKI_PX, (string)$kod );
         //
    }

    // tlumaczenia
    $i18n = new Translator($_SESSION['domyslnyJezyk']['id']);
    $tlumacz = $i18n->tlumacz( array('FORMULARZ','PRODUKT','WYGLAD','PRZYCISKI','LOGOWANIE') );
    
    // zamienia linki SSL
    $preg = preg_match_all('|{__SSL:([0-9a-zA-Z-._?/]+?)}|', $kod, $matches);
    foreach ($matches[1] as $Link) {
        //
        if ( WLACZENIE_SSL == 'tak' ) {
            $kod = str_replace('{__SSL:' . $Link . '}', ADRES_URL_SKLEPU_SSL . '/' . $Link, (string)$kod);
          } else {
            $kod = str_replace('{__SSL:' . $Link . '}', (string)$Link, (string)$kod);
        }
    }     

    // konwersja danych jezykowych
    $preg = preg_match_all('|{__TLUMACZ:([0-9A-Z_]+?)}|', $kod, $matches);
    foreach ($matches[1] as $WartoscJezykowa) {
        $kod = str_replace('{__TLUMACZ:' . $WartoscJezykowa . '}', str_replace("'", "&apos;", (string)nl2br($tlumacz[$WartoscJezykowa])), (string)$kod);
    }
    
    /*
    if ( CZAT_WLACZONY == 'tak' ) {
        // czat z klientami
        $teksty_czat = '{__TLUMACZ:CZAT_JA}##{__TLUMACZ:CZAT_KONSULTANT}##{__TLUMACZ:CZAT_ZAPRASZAMY}##{__TLUMACZ:CZAT_WITAJ}##{__TLUMACZ:CZAT_WYSLIJ}##{__TLUMACZ:CZAT_NAPISZ_WIADOMOSC}##{__TLUMACZ:CZAT_NIEDOSTEPNY}##{__TLUMACZ:CZAT_FORMULARZ}';
        // konwersja danych jezykowych
        $preg = preg_match_all('|{__TLUMACZ:([0-9A-Z_]+?)}|', $teksty_czat, $matches);
        foreach ($matches[1] as $WartoscJezykowa) {
            $teksty_czat = str_replace('{__TLUMACZ:' . $WartoscJezykowa . '}', nl2br($tlumacz[$WartoscJezykowa]), $teksty_czat);
        }        
        //
        $kod .= 'var czat_info = "' . base64_encode((string)$teksty_czat) . '";';
        $kod .= 'var czat_foto = "' . base64_encode(KATALOG_ZDJEC . '/' . CZAT_LOGO) . '";';                                         
        $kod .= 'var czat_link = "' . base64_encode(CZAT_LINK) . '";';
        $kod .= 'var czat_tryb = "' . CZAT_NIEAKTYWNY . '";'; 
        //
        $kod .= file_get_contents('javascript/czat.jcs');
        //
        $kod = str_replace( '{__DIV_CZAT}', '<div id="Czat" class="cmxform" data-id="' . Sesje::Token() . '" data-ip="' . $_SESSION['ippp'] . '"></div>', $kod );
        //
    }    
    */
    
    unset($i18n, $tlumacz);

    $kod = jsMin::minify($kod);

    if ( CACHE_JS == 'tak' ) {
        // zapis cache js do pliku
        $plikKlucz = fopen($PlikCacheJs,'a+');
        flock($plikKlucz,LOCK_EX);
        fseek($plikKlucz,0);
        ftruncate($plikKlucz,0);
        fwrite($plikKlucz, $kod);
        fclose($plikKlucz);    
    }
    
} else {

    // odczyt cache js z pliku
    $plikKlucz = fopen($PlikCacheJs,'r');
    flock($plikKlucz,LOCK_SH);
    
    if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ){
        header ("HTTP/1.0 304 Not Modified");
        exit;
    } 
          
    $kod = file_get_contents($PlikCacheJs);
    fclose($plikKlucz);          

}

unset($PlikCacheJs);

// zamiana tokenu bezpieczenstwa
$kod = str_replace( '{__DOMYSLNY_SZABLON}', (string)DOMYSLNY_SZABLON, (string)$kod );
$kod = str_replace( '{__AKTYWNY_SSL}', ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '') ? 'tak' : 'nie' ), (string)$kod );
$kod = str_replace( '{__KATALOG_ZDJEC}', (string)KATALOG_ZDJEC, (string)$kod );
$kod = str_replace( '{__UKRYWANIE_INPUTOW_ILOSCI}', (string)PRODUKT_KUPOWANIE_ILOSC, (string)$kod );
$kod = str_replace( '{__KOSZYK_ANIMACJA}', (string)KOSZYK_ANIMACJA, (string)$kod );
$kod = str_replace( '{__SZEROKOSC_TIP}', (string)ZDJECIE_LISTING_POWIEKSZENIE_SZEROKOSC, (string)$kod );
$kod = str_replace( '{__WYSOKOSC_TIP}', (string)ZDJECIE_LISTING_POWIEKSZENIE_WYSOKOSC, (string)$kod );
$kod = str_replace( '{__ZDJECIE_POWIEKSZANIE}', (string)ZDJECIE_LISTING_POWIEKSZENIE, (string)$kod );
$kod = str_replace( '{__AKCJA_KOSZYKA_DODANIE}', (string)PRODUKT_OKNO_POPUP, (string)$kod );  
$kod = str_replace( '{__AKCJA_KOSZYKA_PRZELICZ}', (string)PRODUKT_OKNO_POPUP_PRZELICZ, (string)$kod ); 
$kod = str_replace( '{__AKCJA_SCHOWKA}', (string)PRODUKT_OKNO_SCHOWEK_POPUP, (string)$kod ); 
$kod = str_replace( '{__POPUP_RODZAJ}', (string)PRODUKT_OKNO_PRODUKTY_SPOSOB_WYSWIETLANIA, (string)$kod ); 
$kod = str_replace( '{__AUTOPODPOWIEDZI}', (string)WYSZUKIWANIE_PODPOWIEDZI, (string)$kod ); 
$kod = str_replace( '{__LISTING_PODKATEGORIE_KOLUMNY}', (string)LISTING_PODKATEGORIE_KOLUMNY, (string)$kod );
$kod = str_replace( '{__TOKEN_NEWSLETTER}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_ANKIETA}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_POROWNYWARKA}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_JEZYK}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_WALUTA}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_SCHOWEK_DODAJ}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_SCHOWEK_USUN}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_KOSZYK_DODAJ}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_KOSZYK_GRATIS}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_KOSZYK_DODAJ_PRZELICZ}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_KOSZYK_DODAJ_ILOSC}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_AUTOUZUPELNIENIE}', (string)Sesje::Token(), (string)$kod ); 
$kod = str_replace( '{__TOKEN_OBRAZEK}', (string)Sesje::Token(), (string)$kod ); 
$kod = str_replace( '{__TOKEN_OPINIE}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__SESJA_ID}', (string)substr(session_id(), 0, -2), (string)$kod );
$kod = str_replace( '{__TYP_SZABLONU}', ((strpos((string)DOMYSLNY_SZABLON, '.rwd.v') > -1) ? 'tak' : 'nie'), (string)$kod );

// integracja - logowanie z fb
$logowanie_social = 0;
//
if ( isset($_SESSION['social']['fb']['id']) && $_SESSION['social']['fb']['id'] != '' ) {
     $logowanie_social = 1;
}
if ( isset($_SESSION['social']['google']['id']) && $_SESSION['social']['google']['id'] != '' ) {
     $logowanie_social = 1;
}
$kod = str_replace( '{__LOGOWANIE_SOCIAL}', $logowanie_social, (string)$kod );
//
$powrot = '';
//
if ( isset($_SESSION['social']['powrot']) && $_SESSION['social']['powrot'] == 'logowanie') {
     $powrot = 'logowanie';
}
if ( isset($_SESSION['social']['powrot']) && $_SESSION['social']['powrot'] == 'rejestracja') {
     $powrot = 'rejestracja';
}
//
$kod = str_replace( '{__LOGOWANIE_SOCIAL_POWROT}', $powrot, (string)$kod );

unset($logowanie_social, $powrot);

// integracja fb i google - logowanie
$kod = str_replace( '{__TOKEN_SOCIAL_OKNO}', (string)Sesje::Token(), (string)$kod );

echo $kod;

unset($kod, $db, $session);

?>