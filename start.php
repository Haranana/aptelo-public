<?php
// wczytanie ustawien inicjujacych system
require_once(dirname(__FILE__).'/ustawienia/init.php');

// jezeli sklep jest wylaczony
if ( INFO_WYLACZ_SKLEP == 'tak') {
     Wylaczenie::WylaczSklep();
}

// zmienna do okreslania ze nie jest aktualnie wyswietlana strona glowna
if (isset($WywolanyPlik) && $WywolanyPlik == 'strona_glowna') {
    $GLOBALS['stronaGlowna'] = true;
}

// !new! wpisywanie jaki szablon jest uzywany w error logach oraz w naglowku, oraz debug czy start
//poprawnie sie uruchomil

error_log("Start was opened succesfuly");

if (defined('DOMYSLNY_SZABLON')) {
    error_log('DOMYSLNY_SZABLON=' . DOMYSLNY_SZABLON);
}else{
     error_log('DOMYSLNY_SZABLON is not defined!');
}

if (defined('DOMYSLNY_SZABLON')) {
    header('X-Debug-Template: ' . DOMYSLNY_SZABLON);
}


// sprawdzi czy nie zmienil sie stan magazynowy produktu lub produkt nie jest wylaczony - musi wtedy zmienic wartosc koszyka
if ( isset($_SESSION['koszyk']) && count($_SESSION['koszyk']) > 0 && $WywolanyPlik != 'zamowienie_podsumowanie' && $WywolanyPlik != 'zamowienia_szczegoly' ) {
    //
    $stanKoszyka = false;
    $stanKoszykaOgolne = false;
    foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
        //
        if ( isset($TablicaZawartosci['id']) ) {
            $stanKoszyka = $GLOBALS['koszykKlienta']->SprawdzIloscProduktuMagazyn( $TablicaZawartosci['id'], true );
            //
            if ( $stanKoszyka == true ) {
                 $stanKoszykaOgolne = true;
            }
        }
        //
    }
    if ( $stanKoszykaOgolne == true ) {
        //
        Funkcje::PrzekierowanieURL('koszyk.html');
        //
    }
    unset($stanKoszyka, $stanKoszykaOgolne);
    //
}

// definiowanie szablonu
$tpl = new Szablony('szablony/'.DOMYSLNY_SZABLON.'/strona_glowna.tp');

// wczytanie danych/funkcji dodatkowych
require_once(dirname(__FILE__).'/inne/start.php');

// pusta deklaracja
$tpl->dodaj('__GRAFIKA_PODSTRONY', '');
$tpl->dodaj('__LINK_CANONICAL', '');

// link kanoniczny dla strony glownej
if ( $WywolanyPlik == 'strona_glowna' ) {
     //
     $tpl->dodaj('__LINK_CANONICAL', '<link rel="canonical" href="' . ADRES_URL_SKLEPU . '/" />');
     //
}

// glowne definicje z sekcji head
$tpl->dodaj('__DOMYSLNY_SZABLON', DOMYSLNY_SZABLON);

$tpl->dodaj('__JEZYK_STRONY', $_SESSION['domyslnyJezyk']['kod']);

// strona glowna
$isHTTPS = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $isHTTPS = true;
} elseif ((!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strpos((string)$_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') > -1) || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')) {
    $isHTTPS = true;
}
if ( $isHTTPS ) {
    $tpl->dodaj('__DOMENA', ADRES_URL_SKLEPU_SSL);
} else {
    $tpl->dodaj('__DOMENA', ADRES_URL_SKLEPU);
}


// plik javascript do wyswietlanego pliku
if (file_exists('javascript/'.$WywolanyPlik.'.jcs')) {
    //
    $PlikPoczatkowy = '';
    // plik logowania google
    if ( INTEGRACJA_GOOGLE_LOGOWANIE_WLACZONY == 'tak' ) {
         if ( $WywolanyPlik == 'logowanie' || $WywolanyPlik == 'dane_adresowe' || $WywolanyPlik == 'zamowienie_logowanie' || $WywolanyPlik == 'rejestracja' ) {
              $PlikPoczatkowy = '<script src="https://apis.google.com/js/platform.js?onload=onLoadGoogleCallback"></script>' . "\n";
         }
    }
    //
    $tpl->dodaj('__JS_PLIK', $PlikPoczatkowy . '<script src="javascript/' . $WywolanyPlik . '.php"></script>');
    //
    unset($PlikPoczatkowy);
    //    
  } else {
    $tpl->dodaj('__JS_PLIK', '');
}

// kod zgody google
$tpl->dodaj('__GOOGLE_ZGODA', IntegracjeZewnetrzne::GoogleZgoda());

// kod weryfikacyjny Google dla webmasterow
$tpl->dodaj('__GOOGLE_WERYFIKACJA', IntegracjeZewnetrzne::GoogleDlaWebmasterowStart());

// modul Google Tag Manager HEAD
$tpl->dodaj('__GOOGLE_GTM_DATALAYER', '');
$tpl->dodaj('__GOOGLE_GTM_HEAD', IntegracjeZewnetrzne::Google_GTM_HeadStart());

// modul Google Tag Manager BODY
$tpl->dodaj('__GOOGLE_GTM_NOSCRIPT', IntegracjeZewnetrzne::Google_GTM_NoscriptStart());

// modul Google Analytics
$tpl->dodaj('__GOOGLE_ANALYTICS', IntegracjeZewnetrzne::GoogleAnalyticsStart() . IntegracjeZewnetrzne::GoogleAnalytics_4_Start());

// widget Trusted Shops
$tpl->dodaj('__WIDGET_TRUSTEDSHOPS', IntegracjeZewnetrzne::TrustedShopsStart());

// integracja z pixel Facebook
$tpl->dodaj('__PIKSEL_FB', IntegracjeZewnetrzne::PixelFacebookStart());

// integracja z Pinterest Tag
$tpl->dodaj('__PINTEREST_TAG', IntegracjeZewnetrzne::PinterestTagStart());

// integracja z CRITEO
$tpl->dodaj('__CRITEO', IntegracjeZewnetrzne::CriteoStart( $WywolanyPlik ));

// integracja z edrone
$tpl->dodaj('__EDRONE', IntegracjeZewnetrzne::EdroneStart( $WywolanyPlik ));

// integracja z NOKAUT sledzenie konwersji
$tpl->dodaj('__NOKAUT_SLEDZENIE', IntegracjeZewnetrzne::NokautKonwersjaStart());

// integracja z ALLANI I DOMODI i integracja z DomodiPixel
$tpl->dodaj('__ALLANIDOMODI_SLEDZENIE', IntegracjeZewnetrzne::AllaniDomodiStart());

// integracja z WP Pixel
$tpl->dodaj('__WP_PIXEL', IntegracjeZewnetrzne::WpPixelStart());

// integracja z SALESmanago
$tpl->dodaj('__SALESMANAGO', IntegracjeZewnetrzne::SalesManagoStart());

// integracja z Opinie konsumenckie Google
$tpl->dodaj('__WIDGET_GOOGLE_OPINIE', IntegracjeZewnetrzne::OpinieKonsumenckieGoogleStart());

// wczytanie skryptu TRUSTISTO
$tpl->dodaj('__TRUSTISTO', IntegracjeZewnetrzne::TrustistoStart( $WywolanyPlik ));

// integracja z shopeneo.network
$tpl->dodaj('__SHOPENEO_NETWORK', IntegracjeZewnetrzne::ShopeneoNetworkStart());

// integracja z CENEO ZAUFANE OPINIE
$tpl->dodaj('__CENEO_OPINIE', IntegracjeZewnetrzne::CeneoOpinieStart());

// integracja z TrustPilot
$tpl->dodaj('__TRUSTPILOT', IntegracjeZewnetrzne::TrustPilotStart());

// integracja z Callback24
$tpl->dodaj('__CALLBACK24', IntegracjeZewnetrzne::Callback24Start());

// kod Google remarketing dynamiczny
$tpl->dodaj('__GOOGLE_KONWERSJA', '');
$tpl->dodaj('__GOOGLE_TAG_MANAGER', IntegracjeZewnetrzne::GoogleRemarketingStart());

// integracja z KLAVIYO sledzenie konwersji
$tpl->dodaj('__KLAVIYO', IntegracjeZewnetrzne::KlaviyoStart());

// plik css do kalendarza - tylko dla wersji mobilnej
$tpl->dodaj('__CSS_KALENDARZ', '');

// Breadcrumb
$nawigacja = new Nawigacja;
$nawigacja->dodaj($GLOBALS['tlumacz']['STRONA_GLOWNA'], ADRES_URL_SKLEPU, 0);

$Wyglad = new Wyglad();

// dostepne formularze
$_SESSION['formularze'] = $Wyglad->Formularze; 

// baner popup
$tpl->dodaj('__JS_POPUP', '');
$tpl->dodaj('__CSS_POPUP', '');
$tpl->dodaj('__TRESC_POPUP', '');
  
if ( BANNER_POPUP_WLACZONY == 'tak' ) {
  
    if (( isset($GLOBALS['bannery']->info['POPUP']) && count($GLOBALS['bannery']->info['POPUP']) > 0 ) && ( !isset($_COOKIE['popup']) || (int)$_COOKIE['popup'] > 0 ))  {
      
        // uruchomi popup jezeli nie jest wlaczony modul cookie rozszerzony
        if ( count($GLOBALS['wykluczeniaIntegracje']) == 0 || ( count($GLOBALS['wykluczeniaIntegracje']) > 0 && isset($_COOKIE['akceptCookie']) ) ) {
             //
             $tpl->dodaj('__JS_POPUP', '<script src="javascript/banner_popup.php"></script>');
             $tpl->dodaj('__CSS_POPUP', ',banner_popup');
             $tpl->dodaj('__TRESC_POPUP', $GLOBALS['bannery']->bannerWyswietlPopUp());
             //
        }

    }
    
}

// style css
// style dodatkowe np listingow
$tpl->dodaj('__CSS_PLIK', '');
// jezeli nie jest strona glowna laduje css z podstronami
$tpl->dodaj('__CSS_PLIK_GLOWNY', '');
if ($GLOBALS['stronaGlowna'] == false) {
    $tpl->dodaj('__CSS_PLIK_GLOWNY', ',podstrony');
}

// wysuwany widget CENEO
if ( ZAKLADKA_CENEO_WLACZONA == 'tak' && ZAKLADKA_CENEO_KOD != '') {
    $tpl->dodaj('__WIDGET_CENEO', ZAKLADKA_CENEO_KOD);
} else {
    $tpl->dodaj('__WIDGET_CENEO', '');
}

// wysuwany widget Okazje.info
if ( ZAKLADKA_OKAZJE_INFO_WLACZONA == 'tak' && ZAKLADKA_OKAZJE_INFO_KOD != '') {
    $tpl->dodaj('__WIDGET_OKAZJE_INFO', ZAKLADKA_OKAZJE_INFO_KOD);
} else {
    $tpl->dodaj('__WIDGET_OKAZJE_INFO', '');
}

// wysuwany widget OPINEO
if ( ZAKLADKA_OPINEO_WLACZONA == 'tak' && ZAKLADKA_OPINEO_KOD != '') {
    $tpl->dodaj('__WIDGET_OPINEO', ZAKLADKA_OPINEO_KOD);
} else {
    $tpl->dodaj('__WIDGET_OPINEO', '');
}

// widgety Trustmate.io
if ( INTEGRACJA_TRUSTMATE_WLACZONY == 'tak' && INTEGRACJA_TRUSTMATE_KEY != '') {
    $TrustmateWidgety = '';

    $WidgetMuskart = explode('|', INTEGRACJA_TRUSTMATE_WIDGET_MUSKART_ID);
    if ( $WidgetMuskart['0'] == '1' && $WidgetMuskart['1'] != '' ) {
        $TrustmateWidgety .= '<div id="'.$WidgetMuskart['1'].'"></div><script defer src="https://trustmate.io/widget/api/'.$WidgetMuskart['1'].'/script"></script>'."\n";
    }
    $WidgetLemur = explode('|', INTEGRACJA_TRUSTMATE_WIDGET_LEMUR_ID);
    if ( $WidgetLemur['0'] == '1' && $WidgetLemur['1'] != '' ) {
        $TrustmateWidgety .= '<div id="'.$WidgetLemur['1'].'"></div><script defer src="https://trustmate.io/widget/api/'.$WidgetLemur['1'].'/script"></script>'."\n";
    }
    $WidgetDodo = explode('|', INTEGRACJA_TRUSTMATE_WIDGET_DODO_ID);
    if ( $WidgetDodo['0'] == '1' && $WidgetDodo['1'] != '' ) {
        $TrustmateWidgety .= '<div id="'.$WidgetDodo['1'].'"></div><script defer src="https://trustmate.io/widget/api/'.$WidgetDodo['1'].'/script"></script>'."\n";
    }

    if ( $GLOBALS['stronaGlowna'] == true && Wyglad::TypSzablonu() == true ) {
        $WidgetMultiHornet = explode('|', INTEGRACJA_TRUSTMATE_WIDGET_MULTIHORNET_ID);
        if ( $WidgetMultiHornet['0'] == '1' && $WidgetMultiHornet['1'] != '' ) {
            $TrustmateWidgety .= '<script defer src="https://trustmate.io/widget/api/'.$WidgetMultiHornet['1'].'/multihornet"></script>'."\n";
        }
    }

    $tpl->dodaj('__WIDGET_TRUSTMATE', $TrustmateWidgety);
    unset($TrustmateWidgety, $WidgetMuskart, $WidgetLemur, $WidgetDodo, $WidgetMultiHornet);

} else {
    $tpl->dodaj('__WIDGET_TRUSTMATE', '');
}

// --------------------- definicje wygladu -----------------------------
// ustalanie tla sklepu
if (TLO_SKLEPU_RODZAJ == 'obraz') {
    $tpl->dodaj('__TLO_SKLEPU', "style=\"background:url('" . KATALOG_ZDJEC . "/" . TLO_SKLEPU . "') " . TLO_SKLEPU_POWTARZANIE . "; " . ((TLO_SKLEPU_FIXED == 'fixed') ? 'background-attachment:fixed' : '') . "\"");
  } else { 
    $tpl->dodaj('__TLO_SKLEPU', ( TLO_SKLEPU != '' ? 'style="background:#' . strtolower(TLO_SKLEPU) . '"' : ''));
}

// wcag kontrast
if (strpos((string)DOMYSLNY_SZABLON, '.rwd.v') > -1) {
    //
    if ( isset($_COOKIE['wcagk']) ) {
         $tpl->dodaj_dodatkowo('__TLO_SKLEPU',' class="Kontrast"');
    }
    //
}

// szerokosc sklepu
$tpl->dodaj('__SZEROKOSC_SKLEPU', ((is_numeric(SZEROKOSC_SKLEPU)) ? SZEROKOSC_SKLEPU : 1200));
// szerokosc lewej kolumny + 15 na margines
$tpl->dodaj('__SZEROKOSC_LEWEJ_KOLUMNY', ((is_numeric(SZEROKOSC_LEWEJ_KOLUMNY)) ? SZEROKOSC_LEWEJ_KOLUMNY + 15 : 200));
// szerokosc prawej kolumny + 15 na margines
$tpl->dodaj('__SZEROKOSC_PRAWEJ_KOLUMNY', ((is_numeric(SZEROKOSC_PRAWEJ_KOLUMNY)) ? SZEROKOSC_PRAWEJ_KOLUMNY + 15 : 200));

if ( SZEROKOSC_SKLEPU != '' ) {
    $SzerokoscSrodek = ((is_numeric(SZEROKOSC_SKLEPU)) ? SZEROKOSC_SKLEPU : 1200);
} else {
    $SzerokoscSrodek = '1200';
}

// moduly srodkowe nad czescia glowna sklepu z boxami   
//$tpl->dodaj('__MODULY_SRODKOWE_GORA', $Wyglad->SrodekSklepu( 'gora', (( $GLOBALS['stronaGlowna'] == true ) ? array(1,2) : array(1,3,4) ) ));
//!new!
$tpl->dodaj('__MODULY_SRODKOWE_GORA', '');

// moduly srodkowe pod czescia glowna sklepu z boxami  
$tpl->dodaj('__MODULY_SRODKOWE_DOL', $Wyglad->SrodekSklepu( 'dol', (( $GLOBALS['stronaGlowna'] == true ) ? array(1,2) : array(1,3,4) ) ));

// moduly srodkowe w czesci glownej sklepu na podstronach
$tpl->dodaj('__MODULY_SRODKOWE_PODSTRONA_GORA', '');
$tpl->dodaj('__MODULY_SRODKOWE_PODSTRONA_DOL', '');

if ($GLOBALS['stronaGlowna'] != true ) {
    //
    $tpl->dodaj('__MODULY_SRODKOWE_PODSTRONA_GORA', $Wyglad->SrodekSklepu( 'srodek', array(1,3,4), 'gora' ));
    $tpl->dodaj('__MODULY_SRODKOWE_PODSTRONA_DOL', $Wyglad->SrodekSklepu( 'srodek', array(1,3,4), 'dol' ));
    //
}

$tpl->dodaj('__LEWA_KOLUMNA', '');  

$LewaKolumnaWlaczona = false;
$PrawaKolumnaWlaczona = false;

// sprawdzi czy wywolana strona nie jest do wyswietlania bez boxow
$SprStrony = explode(';', (string)STRONY_KOLUMNY_BOX);
if ( isset($WywolanyPlik) && in_array((string)$WywolanyPlik, $SprStrony) ) {
     unset($GLOBALS['kolumny']);
     $GLOBALS['kolumny'] = 'srodkowa';
}         

// czy tylko na podstronach
if ($GLOBALS['stronaGlowna'] != true || ($GLOBALS['stronaGlowna'] == true && CZY_WLACZONA_LEWA_WSZEDZIE == 'nie')) {

    if (CZY_WLACZONA_LEWA_KOLUMNA == 'tak' && ($GLOBALS['kolumny'] == 'wszystkie' || $GLOBALS['kolumny'] == 'wszystkie_lewa')) {
        $SzerokoscSrodek = $SzerokoscSrodek - ((is_numeric(SZEROKOSC_LEWEJ_KOLUMNY)) ? SZEROKOSC_LEWEJ_KOLUMNY + 15 : 200);
        // boxy lewa kolumna
        $BoxyLewe = $Wyglad->KolumnaBoxu('lewa');
        if ( $BoxyLewe != '' ) {
             $tpl->dodaj('__LEWA_KOLUMNA', $BoxyLewe);
             $LewaKolumnaWlaczona = true;
          } else {
             $LewaKolumnaWlaczona = false;
        }
        unset($BoxyLewe);
    }
    
}

$tpl->dodaj('__PRAWA_KOLUMNA', '');

// czy tylko na podstronach
if ($GLOBALS['stronaGlowna'] != true || ($GLOBALS['stronaGlowna'] == true && CZY_WLACZONA_PRAWA_WSZEDZIE == 'nie')) {

    if (CZY_WLACZONA_PRAWA_KOLUMNA == 'tak' && ($GLOBALS['kolumny'] == 'wszystkie' || $GLOBALS['kolumny'] == 'wszystkie_prawa')) {
        $SzerokoscSrodek = $SzerokoscSrodek - ((is_numeric(SZEROKOSC_PRAWEJ_KOLUMNY)) ? SZEROKOSC_PRAWEJ_KOLUMNY + 15 : 200);
        // boxy prawa kolumna
        $BoxyPrawe = $Wyglad->KolumnaBoxu('prawa');
        if ( $BoxyPrawe != '' ) {
             $tpl->dodaj('__PRAWA_KOLUMNA', $BoxyPrawe);
             $PrawaKolumnaWlaczona = true;
          } else {
             $PrawaKolumnaWlaczona = false;
        }
        unset($BoxyPrawe);            
    }
    
}

if ( $LewaKolumnaWlaczona == false && $PrawaKolumnaWlaczona == false ) {
     $GLOBALS['kolumny'] = 'srodkowa';
}
if ( $LewaKolumnaWlaczona == false && $PrawaKolumnaWlaczona == true ) {
     $GLOBALS['kolumny'] = 'wszystkie_prawa';
}
if ( $LewaKolumnaWlaczona == true && $PrawaKolumnaWlaczona == false ) {
     $GLOBALS['kolumny'] = 'wszystkie_lewa';
}

$tpl->dodaj('__SZEROKOSC_SRODKOWEJ_KOLUMNY', $SzerokoscSrodek);

// uzywane w niektorych szablonach jezeli srodek ma miec margines w stosunku do sklepu
$tpl->dodaj('__SZEROKOSC_SRODKOWEJ_KOLUMNY_MINUS_10', $SzerokoscSrodek - 10);
$tpl->dodaj('__SZEROKOSC_SRODKOWEJ_KOLUMNY_MINUS_20', $SzerokoscSrodek - 20);

// ################# dodatkowy css dla szablonow wer 2.0

$DodatkowyCss = '';

$SzerokoscKolumn = 0;

if ( $LewaKolumnaWlaczona == true ) {
     //
     $DodatkowyCss .= '#LewaKolumna { width:' . ((is_numeric(SZEROKOSC_LEWEJ_KOLUMNY)) ? SZEROKOSC_LEWEJ_KOLUMNY : 200) . 'px; } ';
     $SzerokoscKolumn += ((is_numeric(SZEROKOSC_LEWEJ_KOLUMNY)) ? SZEROKOSC_LEWEJ_KOLUMNY : 200);
     //
}
if ( $PrawaKolumnaWlaczona == true ) {
     //
     $DodatkowyCss .= '#PrawaKolumna { width:' . ((is_numeric(SZEROKOSC_PRAWEJ_KOLUMNY)) ? SZEROKOSC_PRAWEJ_KOLUMNY : 200) . 'px; } ';
     $SzerokoscKolumn += ((is_numeric(SZEROKOSC_PRAWEJ_KOLUMNY)) ? SZEROKOSC_PRAWEJ_KOLUMNY : 200);
     //
}

if ( $SzerokoscKolumn > 0 ) {
     //
     $DodatkowyCss .= '#SrodekKolumna { width:calc(100% - ' . $SzerokoscKolumn . 'px); } ';
     //
}

if ( SZEROKOSC_SKLEPU_JEDNOSTKA == 'px' ) {
     //
     $DodatkowyCss .= '@media only screen and (min-width:1600px) { #Strona, .Strona { width:' . ((is_numeric(SZEROKOSC_SKLEPU)) ? SZEROKOSC_SKLEPU : 1200) . 'px; } }';
     //
}
if ( SZEROKOSC_SKLEPU_JEDNOSTKA == 'procent' ) {
     //
     $DodatkowyCss .= '.Strona { width:' . ((is_numeric(SZEROKOSC_SKLEPU)) ? SZEROKOSC_SKLEPU : 90) . '%; } ';
     //
}

// #################

unset($LewaKolumnaWlaczona, $PrawaKolumnaWlaczona);

// preloader obrazkow
$tpl->dodaj('__FUNKCJA_PRELOADERA', '');
if ( PRELOAD_OBRAZKOW == 'tak' ) {
    // ladowanie obrazkow
    $tpl->dodaj('__FUNKCJA_PRELOADERA', $Wyglad->PrzegladarkaJavaScript( "$.ZaladujObrazki(false);" ));   
}

// portale spolecznosciowe
$tpl->dodaj('__PORTALE_SPOLECZNOSCIOWE', $Wyglad->PortaleSpolecznisciowe());

// ikony jezykow
$tpl->dodaj('__ZMIANA_JEZYKA', $Wyglad->ZmianaJezyka());

// domyslny jezyk - ikona
$tpl->dodaj('__WYBRANY_JEZYK', '');
if ( isset($_SESSION['domyslnyJezyk']['ikona']) && $_SESSION['domyslnyJezyk']['ikona'] != '' ) {
     //
     if ( file_exists(KATALOG_ZDJEC . '/' . $_SESSION['domyslnyJezyk']['ikona']) ) {
          //
          list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $_SESSION['domyslnyJezyk']['ikona']);
          //
          $tpl->dodaj('__WYBRANY_JEZYK', '<img src="' . KATALOG_ZDJEC . '/' . $_SESSION['domyslnyJezyk']['ikona'] . '" width="' . $szerokosc . '" height="' . $wysokosc . '" alt="' . $_SESSION['domyslnyJezyk']['nazwa'] . '" title="' . $_SESSION['domyslnyJezyk']['nazwa'] . '" />');
          //
          unset($szerokosc, $wysokosc, $typ, $atrybuty);
     }
     //
}

// select zmiany waluty
$tpl->dodaj('__ZMIANA_WALUTY', $Wyglad->ZmianaWaluty());

// domyslny jezyk - ikona
$tpl->dodaj('__WYBRANA_WALUTA', '');
$tpl->dodaj('__WYBRANA_WALUTA_SYMBOL', '');
if ( isset($_SESSION['domyslnaWaluta']['nazwa']) ) {
     //
     $tpl->dodaj('__WYBRANA_WALUTA', $_SESSION['domyslnaWaluta']['nazwa']);
     $tpl->dodaj('__WYBRANA_WALUTA_SYMBOL', $_SESSION['domyslnaWaluta']['symbol']);
     //
}

// moduly stale
$ModulyStale = $Wyglad->ModulyStale();

// wyswietlanie wiadomosci z kto jest online
if ( WIADOMOSCI_POPUP == 'tak') {
     $ModulyStale .= "\n\n" . '<script>InformacjaOnline()</script>';
}

$tpl->dodaj('__MODULY_STALE', $ModulyStale);
unset($ModulyStale);

// logo/naglowek
if (NAGLOWEK_RODZAJ == 'kod') {
    $tpl->dodaj('__LOGO_SKLEPU', htmlspecialchars_decode(NAGLOWEK));
  } else {   
    $NaglowekSklepu = '<a id="LinkLogo" href="' . ((WLACZENIE_SSL == 'tak') ? ADRES_URL_SKLEPU : '/') . '">';
    $NaglowekSklepuTmp = '';
    //
    if ( NAGLOWEK != '' ) {
         //
         if ( file_exists(KATALOG_ZDJEC . '/' . NAGLOWEK) && is_file(KATALOG_ZDJEC . '/' . NAGLOWEK) ) {
              //
              list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . NAGLOWEK);
              //           
              $NaglowekSklepuTmp = '<img ' . ((NAGLOWEK_RWD_MOBILNY != '') ? 'class="RwdKomputer"' : '') . ' src="' . KATALOG_ZDJEC . '/' . NAGLOWEK . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . FunkcjeWlasnePHP::my_htmlentities(DANE_NAZWA_FIRMY_SKROCONA) . '" />';
              //
         }
         //
    }
    if ( NAGLOWEK_RWD_MOBILNY != '' ) {
         //
         if ( file_exists(KATALOG_ZDJEC . '/' . NAGLOWEK_RWD_MOBILNY) && is_file(KATALOG_ZDJEC . '/' . NAGLOWEK_RWD_MOBILNY) ) {
              //
              list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . NAGLOWEK_RWD_MOBILNY);
              //                     
              $NaglowekSklepu .= '<img class="RwdMobilny" src="' . KATALOG_ZDJEC . '/' . NAGLOWEK_RWD_MOBILNY . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . FunkcjeWlasnePHP::my_htmlentities(DANE_NAZWA_FIRMY_SKROCONA) . '" />';
              //
         }
         //
    }
    if ( NAGLOWEK_RWD_KONTRAST != '' && strpos((string)DOMYSLNY_SZABLON, '.rwd.v') > -1 && isset($_COOKIE['wcagk']) ) {          
         //
         if ( file_exists(KATALOG_ZDJEC . '/' . NAGLOWEK_RWD_KONTRAST) && is_file(KATALOG_ZDJEC . '/' . NAGLOWEK_RWD_KONTRAST) ) {
              //
              list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . NAGLOWEK_RWD_KONTRAST);
              //                     
              $NaglowekSklepuTmp = '<img ' . ((NAGLOWEK_RWD_MOBILNY != '') ? 'class="RwdKomputer"' : '') . ' src="' . KATALOG_ZDJEC . '/' . NAGLOWEK_RWD_KONTRAST . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="' . FunkcjeWlasnePHP::my_htmlentities(DANE_NAZWA_FIRMY_SKROCONA) . '" />';
              //
         }
         //
    }    
    $NaglowekSklepu .= $NaglowekSklepuTmp . '</a>';
    //
    $tpl->dodaj('__LOGO_SKLEPU', $NaglowekSklepu);
    //
    unset($NaglowekSklepu);
}

// gorne menu
$tpl->dodaj('__GORNE_MENU', '<ul class="GlowneGorneMenu" role="menubar">' . $Wyglad->Linki('gorne_menu', '<li role="menuitem">', '</li>') . '</ul>');

// dolne menu
$DolneMenu = $Wyglad->Linki('dolne_menu');
if ( !empty($DolneMenu) ) {
     $tpl->dodaj('__DOLNE_MENU', '<ul>' . $DolneMenu . '</ul>');
} else {
     $tpl->dodaj('__DOLNE_MENU', '');
}
unset($DolneMenu);

// szybkie linki
$tpl->dodaj('__SZYBKIE_LINKI', '');
$tpl->dodaj('__SZYBKIE_LINKI_CSS', '');
if ( Wyglad::TypSzablonu() == true ) {
     //      
     if ( SZYBKIE_MENU_MOBILE == 'nie' ) {
          //
          $tpl->dodaj('__SZYBKIE_LINKI_CSS', ' SzybkieLinkiMobile');
          //
     }  
     //
     if ( $Wyglad->Linki('szybkie_menu') != '' ) {
          //
          $tpl->dodaj('__SZYBKIE_LINKI', '<ul>' . $Wyglad->Linki('szybkie_menu') . '</ul>');
          //
     } else {
          //
          $tpl->dodaj('__SZYBKIE_LINKI_CSS', ' SzybkieLinkiBrak');
          //
     }
     //
}

// stopka

// dane kontaktowe w stopce
$KontaktStopka = '';
if ( Wyglad::TypSzablonu() == true && STOPKA_DANE_KONTAKTOWE_STATUS == 'tak' ) {
     //
     $KontaktStopka = $Wyglad->KontaktStopka();
     //
}
// portale spolecznosciowe
if ( STOPKA_PORTALE == 'tak' ) {
     //
     $KontaktStopka .= $Wyglad->PortaleSpolecznisciowe(); 
     //
}

// pierwsza kolumna stopki
$tpl->dodaj('__PIERWSZA_KOLUMNA_STOPKI_NAGLOWEK', ((isset($GLOBALS['tlumacz']['STOPKA_NAGLOWEK_PIERWSZA'])) ? $GLOBALS['tlumacz']['STOPKA_NAGLOWEK_PIERWSZA'] : ''));
$tpl->dodaj('__PIERWSZA_KOLUMNA_STOPKI_LINKI', (($GLOBALS['tlumacz']['STOPKA_TEKST_PIERWSZA'] != '') ? '<div class="OpisKolumnyStopki FormatEdytor">' . $GLOBALS['tlumacz']['STOPKA_TEKST_PIERWSZA'] . '</div>' : '') . ((STOPKA_DANE_KONTAKTOWE_KOLUMNA == 'pierwsza') ? $KontaktStopka : '') . '<ul class="LinkiStopki">' . $Wyglad->Linki('pierwsza_stopka') . '</ul>');

// druga kolumna stopki
$tpl->dodaj('__DRUGA_KOLUMNA_STOPKI_NAGLOWEK', ((isset($GLOBALS['tlumacz']['STOPKA_NAGLOWEK_DRUGA'])) ? $GLOBALS['tlumacz']['STOPKA_NAGLOWEK_DRUGA'] : ''));
$tpl->dodaj('__DRUGA_KOLUMNA_STOPKI_LINKI', (($GLOBALS['tlumacz']['STOPKA_TEKST_DRUGA'] != '') ? '<div class="OpisKolumnyStopki FormatEdytor">' . $GLOBALS['tlumacz']['STOPKA_TEKST_DRUGA'] . '</div>' : '') . ((STOPKA_DANE_KONTAKTOWE_KOLUMNA == 'druga') ? $KontaktStopka : '') . '<ul class="LinkiStopki">' . $Wyglad->Linki('druga_stopka') . '</ul>');

// trzecia kolumna stopki
$tpl->dodaj('__TRZECIA_KOLUMNA_STOPKI_NAGLOWEK', ((isset($GLOBALS['tlumacz']['STOPKA_NAGLOWEK_TRZECIA'])) ? $GLOBALS['tlumacz']['STOPKA_NAGLOWEK_TRZECIA'] : ''));
$tpl->dodaj('__TRZECIA_KOLUMNA_STOPKI_LINKI', (($GLOBALS['tlumacz']['STOPKA_TEKST_TRZECIA'] != '') ? '<div class="OpisKolumnyStopki FormatEdytor">' . $GLOBALS['tlumacz']['STOPKA_TEKST_TRZECIA'] . '</div>' : '') . ((STOPKA_DANE_KONTAKTOWE_KOLUMNA == 'trzecia') ? $KontaktStopka : '') . '<ul class="LinkiStopki">' . $Wyglad->Linki('trzecia_stopka') . '</ul>');

// czwarta kolumna stopki
$tpl->dodaj('__CZWARTA_KOLUMNA_STOPKI_NAGLOWEK', ((isset($GLOBALS['tlumacz']['STOPKA_NAGLOWEK_CZWARTA'])) ? $GLOBALS['tlumacz']['STOPKA_NAGLOWEK_CZWARTA'] : ''));
$tpl->dodaj('__CZWARTA_KOLUMNA_STOPKI_LINKI', (($GLOBALS['tlumacz']['STOPKA_TEKST_CZWARTA'] != '') ? '<div class="OpisKolumnyStopki FormatEdytor">' . $GLOBALS['tlumacz']['STOPKA_TEKST_CZWARTA'] . '</div>' : '') .((STOPKA_DANE_KONTAKTOWE_KOLUMNA == 'czwarta') ? $KontaktStopka : '') . '<ul class="LinkiStopki">' . $Wyglad->Linki('czwarta_stopka') . '</ul>');

// piata kolumna stopki
$tpl->dodaj('__PIATA_KOLUMNA_STOPKI_NAGLOWEK', ((isset($GLOBALS['tlumacz']['STOPKA_NAGLOWEK_PIATA'])) ? $GLOBALS['tlumacz']['STOPKA_NAGLOWEK_PIATA'] : ''));
$tpl->dodaj('__PIATA_KOLUMNA_STOPKI_LINKI', (($GLOBALS['tlumacz']['STOPKA_TEKST_PIATA'] != '') ? '<div class="OpisKolumnyStopki FormatEdytor">' . $GLOBALS['tlumacz']['STOPKA_TEKST_PIATA'] . '</div>' : '') . ((STOPKA_DANE_KONTAKTOWE_KOLUMNA == 'piata') ? $KontaktStopka : '') . '<ul class="LinkiStopki">' . $Wyglad->Linki('piata_stopka') . '</ul>');

// schowek
$tpl->dodaj('__ILOSC_PRODUKTOW_SCHOWKA', (isset($GLOBALS['schowekKlienta']->IloscProduktow) ? $GLOBALS['schowekKlienta']->IloscProduktow: ''));

$WartoscSchowka = 0;
if ( isset($GLOBALS['schowekKlienta']->IloscProduktow) ) {
     $ZawartoscSchowka = $GLOBALS['schowekKlienta']->WartoscProduktowSchowka();
     $WartoscSchowka = $ZawartoscSchowka['brutto'];
     unset($ZawartoscSchowka);
}
$tpl->dodaj('__WARTOSC_SCHOWKA_BRUTTO', $GLOBALS['waluty']->WyswietlFormatCeny($WartoscSchowka, $_SESSION['domyslnaWaluta']['id'], true, false));  
unset($WartoscSchowka);

// koszyk
$ZawartoscKoszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();

if (isset($WywolanyPlik) && $WywolanyPlik != 'zamowienie_podsumowanie' ) {
    if ( KOSZYK_SPOSOB_POKAZYWANIA_ILOSCI == 'ilosc jednostkowa' ) {
        $tpl->dodaj('__ILOSC_PRODUKTOW_KOSZYKA', $ZawartoscKoszyka['ilosc_jednostkowa']);
    } else {
        $tpl->dodaj('__ILOSC_PRODUKTOW_KOSZYKA', $ZawartoscKoszyka['ilosc']);
    }
    $tpl->dodaj('__WARTOSC_KOSZYKA_BRUTTO', $GLOBALS['waluty']->WyswietlFormatCeny($ZawartoscKoszyka['brutto'], $_SESSION['domyslnaWaluta']['id'], true, false));  
} else {
    $tpl->dodaj('__ILOSC_PRODUKTOW_KOSZYKA', '0');
    $tpl->dodaj('__WARTOSC_KOSZYKA_BRUTTO', $GLOBALS['waluty']->WyswietlFormatCeny(0, $_SESSION['domyslnaWaluta']['id'], true, false));  
}

unset($ZawartoscKoszyka);

// tylko dla nowego szablonu
if ( Wyglad::TypSzablonu() == true ) {

    // porownywarka produktow
    $tpl->dodaj('__ILOSC_PRODUKTOW_POROWNYWARKI', 0);
    $tpl->dodaj('__CSS_POROWNANIE_NAGLOWEK', 'style="display:none"');
    //
    if ( LISTING_POROWNYWARKA_PRODUKTOW == 'tak' && NAGLOWEK_POROWNYWARKA == 'tak' ) {
         //
         if ( isset($_SESSION['produktyPorownania']) && count($_SESSION['produktyPorownania']) > 0 ) {
               $tpl->dodaj('__CSS_POROWNANIE_NAGLOWEK', '');
               $tpl->dodaj('__ILOSC_PRODUKTOW_POROWNYWARKI', count($_SESSION['produktyPorownania']));
         }
         //
    }
    //
    // rozwijany koszyk i schowek
    ob_start();
    require('inne/rozwijany_koszyk.php');
    $DaneKoszykaRozwijanego = ob_get_contents();
    ob_end_clean();    
    //
    $tpl->dodaj('__ROZWIJANY_KOSZYK', $DaneKoszykaRozwijanego);
    //
    unset($DaneKoszykaRozwijanego);

    ob_start();
    require('inne/rozwijany_schowek.php');
    $DaneSchowkaRozwijanego = ob_get_contents();
    ob_end_clean();    
    //
    $tpl->dodaj('__ROZWIJANY_SCHOWEK', $DaneSchowkaRozwijanego);
    //
    unset($DaneSchowkaRozwijanego);
    
    // bannery stopka
    $tpl->dodaj('__BANNERY_STOPKA', '');
    if ( STOPKA_BANNERY == 'tak' && STOPKA_BANNERY_GRUPA != '' ) {
         //     
         if ( isset($GLOBALS['bannery']->info[STOPKA_BANNERY_GRUPA]) ) {
              //
              $TablicaBannerow = $GLOBALS['bannery']->info[STOPKA_BANNERY_GRUPA];
              //
              if ( count($TablicaBannerow) > 0 ) {
                   //
                   ob_start();
                   //
                   foreach ($TablicaBannerow as $Banner ) {
                       ///
                       $GLOBALS['bannery']->bannerWyswietlStatyczny($Banner);
                       //
                   }
                   //
                   $BanneryStopka = ob_get_contents();
                   ob_end_clean();    
                   //
                   $tpl->dodaj('__BANNERY_STOPKA', $BanneryStopka);
                   //
              }
              //
              unset($TablicaBannerow);
              //
         }
         //
    }

    // opis nad naglowkiem
    $tpl->dodaj('__OPIS_NAD_NAGLOWKIEM', ((isset($GLOBALS['tlumacz']['OPIS_NAD_NAGLOWKIEM'])) ? $GLOBALS['tlumacz']['OPIS_NAD_NAGLOWKIEM'] : ''));
    $tpl->dodaj('__OPIS_NAD_NAGLOWKIEM_CSS', '');
      
    if ( INFO_NAD_NAGLOWKIEM_MOBILE == 'nie' ) {
         //
         $tpl->dodaj('__OPIS_NAD_NAGLOWKIEM_CSS', ' OpisNaglowekMobile');
         //
    }

    // opis stopka
    $tpl->dodaj('__OPIS_STOPKA', ((isset($GLOBALS['tlumacz']['OPIS_TEKST_STOPKA'])) ? $GLOBALS['tlumacz']['OPIS_TEKST_STOPKA'] : ''));

}

// kompresja styli css
$tpl->dodaj('__KOMPRESJA_CSS', (( KOMPRESJA_CSS == 'tak' ) ? 'css' : 'ncss' ));

// jezeli aktywne opinie o sklepie
$tpl->dodaj('__CSS_OPINIE', ((OPINIE_STATUS == 'tak') ? ',opinie' : ''));

// lista modulow stalych wlaczonych w sklepie - do ladowania dodatkowych css - wyodrebnione css do osobnych plikow
$tpl->dodaj('__CSS_MODULY_STALE', '');
if ( isset($Wyglad->PlikiModulyStalePliki) ) {
     //
     if ( count($Wyglad->PlikiModulyStalePliki) > 0 ) {
          //
          $DostepneCss = array();
          //
          foreach ( $Wyglad->PlikiModulyStalePliki as $CssPlik ) {
              //
              if ( file_exists('szablony/' . DOMYSLNY_SZABLON . '/css/' . $CssPlik . '.css') ) {
                   $DostepneCss[] = $CssPlik;
              }
              //
          }
          //         
          $tpl->dodaj('__CSS_MODULY_STALE', ',' . implode(',', (array)$DostepneCss));
          //
          unset($DostepneCss);
          //
     }
     //
}

// liczniki odwiedzin
$tpl->dodaj('__ILOSC_ODWIEDZIN', $GLOBALS['licznikOdwiedzinSklepu']);
unset($GLOBALS['licznikOdwiedzinSklepu']);

if ( LICZNIK_ODWIEDZIN_DATA == '' ) {
    $GLOBALS['db']->open_query("UPDATE settings SET value = '" . time() . "' WHERE code = 'LICZNIK_ODWIEDZIN_DATA'");
    $tpl->dodaj('__DATA_LICZNIKA_ODWIEDZIN', date('d-m-Y',time()));
} else {
    $tpl->dodaj('__DATA_LICZNIKA_ODWIEDZIN', date('d-m-Y',LICZNIK_ODWIEDZIN_DATA));
}

$tpl->dodaj('__TAGI_OPEN_GRAPH', '');

// integracja z fb i google - logowanie
$InfoSocial = '';
//
if ( isset($_SESSION['informacja_social']) ) {  
     //
     $InfoSocial = '<div id="InfoSocial" style="display:none">' . $_SESSION['informacja_social'] . '</div>';
     unset($_SESSION['informacja_social']);
     //
}
if ( isset($_SESSION['social']) && isset($WywolanyPlik) && $WywolanyPlik != 'logowanie' && $WywolanyPlik != 'rejestracja' && $WywolanyPlik != 'dane_adresowe' ) {
     //
     unset($_SESSION['social']);
     //
} 
        
// integracje wlasne
$IntegracjeHead = ((KOD_HEAD != '') ? base64_decode(KOD_HEAD) : '') . ((WYGLAD_DODATKOWY_CSS != '') ? '<style>' . WYGLAD_DODATKOWY_CSS . '</style>' : '');

if (isset($_COOKIE['akceptCookie']) && $_COOKIE['akceptCookie'] == 'tak') {
    //
    if ( isset($_COOKIE['cookieAnalityczne']) && $_COOKIE['cookieAnalityczne'] == 'tak' ) {
         $IntegracjeHead .= ((KOD_HEAD_ANALITYCZNE != '') ? base64_decode(KOD_HEAD_ANALITYCZNE) : '');
    }
    if ( isset($_COOKIE['cookieFunkcjonalne']) && $_COOKIE['cookieFunkcjonalne'] == 'tak' ) {
         $IntegracjeHead .= ((KOD_HEAD_FUNKCJONALNE != '') ? base64_decode(KOD_HEAD_FUNKCJONALNE) : '');
    }
    if ( isset($_COOKIE['cookieReklamowe']) && $_COOKIE['cookieReklamowe'] == 'tak' ) {
         $IntegracjeHead .= ((KOD_HEAD_REKLAMOWE != '') ? base64_decode(KOD_HEAD_REKLAMOWE) : '');
    }
    //
}

$tpl->dodaj('__INTEGRACJE_WLASNE_HEAD', $IntegracjeHead);

$tpl->dodaj('__INTEGRACJE_WLASNE_BODY', ((KOD_BODY != '') ? base64_decode(KOD_BODY) : '') . $InfoSocial);

unset($IntegracjeHead);

// zmiana meta tytulu po opuszczeniu przez klienta zakladki skllepu
$tpl->dodaj('__META_TYTUL_WROC_DO_NAS', '');
if ( TYTUL_ZAKLADKI_WLACZONY == 'tak' ) {
     //
     $Meta = MetaTagi::ZwrocMetaTagi('');
     //
     $KodMeta = '';
     //
     if ( isset($Meta['tytul_zakladka']) && trim($Meta['tytul_zakladka']) != '' ) {
          //
          $KodMeta .= '<script>var timer = 0; var domyslny_tytul=document.title;window.onblur=function(){';
          //
          if ( TYTUL_ZAKLADKI_MIGANIE == 'tak' ) {
               $KodMeta .= 'timer=window.setInterval(function() { document.title = document.title == domyslny_tytul ? "' . str_replace('"', '', $Meta['tytul_zakladka']) . '" : domyslny_tytul; }, 1000);';
          } else {
               $KodMeta .= 'document.title="' . str_replace('"', '', $Meta['tytul_zakladka']) . '";';
          }
          //
          $KodMeta .= '};';
          $KodMeta .= 'window.onfocus=function(){document.title = domyslny_tytul;';
          //
          if ( TYTUL_ZAKLADKI_MIGANIE == 'tak' ) {
                $KodMeta .= 'clearInterval(timer);';
          }
          //
          $KodMeta .= '}</script>';
          //
     }
     //
     $tpl->dodaj('__META_TYTUL_WROC_DO_NAS', $KodMeta);
     //
     unset($Meta, $KodMeta);
     //
}

$tpl->dodaj('__INFO_SG',Funkcje::Sg('PGEgaHJlZj0iaHR0cHM6Ly93d3cuc2hvcGdvbGQucGwiIHRhcmdldD0iX2JsYW5rIj5Ta2xlcCBpbnRlcm5ldG93eSBzaG9wR29sZDwvYT4='));


$tpl->dodaj('__ZMIANA_COOKIES', '');
if ( isset($_SESSION['cookie_rozszerzone']) && $_SESSION['cookie_rozszerzone'] == 'tak' ) {
     //
     $tpl->dodaj('__ZMIANA_COOKIES', '<div style="padding:10px"><a href="ustawienia-cookies.html" title="' . $GLOBALS['tlumacz']['ZARZADZAJ_COOKIES'] . '">' . $GLOBALS['tlumacz']['ZARZADZAJ_COOKIES'] . '</a></div>');
     //
}

?>