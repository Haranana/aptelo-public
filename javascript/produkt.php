<?php
chdir('../');

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init_ajax.php');

if ( !isset($_SESSION['domyslnyJezyk']['kod']) ) { $_SESSION['domyslnyJezyk']['kod'] = 'pl'; }
if ( !isset($_SESSION['domyslnyJezyk']['id']) ) { $_SESSION['domyslnyJezyk']['id'] = '1'; }
if ( !isset($_SESSION['domyslnaWaluta']['symbol']) ) { $_SESSION['domyslnaWaluta']['symbol'] = 'zł'; }
if ( !isset($_SESSION['domyslnaWaluta']['separator']) ) { $_SESSION['domyslnaWaluta']['separator'] = ','; }
if ( !isset($_SESSION['domyslnaWaluta']['przelicznik']) ) { $_SESSION['domyslnaWaluta']['przelicznik'] = '1'; }

$PlikCacheJs = 'cache/js/produkt_' . $_SESSION['domyslnyJezyk']['kod'] . '.jcs';

if (!file_exists($PlikCacheJs) || CACHE_JS == 'nie') {

    include 'klasy/Jezyki.php';
    include 'klasy/Translator.php';
    include 'klasy/jsMin.php';

    $kod = '';
    $kod .= file_get_contents('programy/zebraDatePicker/zebra_datepicker.js');
    $kod .= file_get_contents('javascript/produkt.jcs');

    // tlumaczenia
    $i18n = new Translator($_SESSION['domyslnyJezyk']['id']);
    $tlumacz = $i18n->tlumacz( array('WYGLAD','PRODUKT','SYSTEM_PUNKTOW','FORMULARZ') );
    
    // konwersja danych jezykowych
    $preg = preg_match_all('|{__TLUMACZ:([0-9A-Z_]+?)}|', $kod, $matches);
    foreach ($matches[1] as $WartoscJezykowa) {
        $kod = str_replace('{__TLUMACZ:' . $WartoscJezykowa . '}', str_replace("'", "&apos;", (string)nl2br($tlumacz[$WartoscJezykowa])), (string)$kod);
    }    
    
    unset($i18n, $tlumacz);
    
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

    // jezeli jest wlaczona kontrola stanu magazynowego cech
    if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && CECHY_MAGAZYN == 'tak' ) {
        $kod = str_replace('{STAN_MAGAZYNOWY_CECH}','tak', (string)$kod);
      } else {
        $kod = str_replace('{STAN_MAGAZYNOWY_CECH}','nie', (string)$kod);
    }
    if ( MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'tak' ) {
        $kod = str_replace('{MAGAZYN_SPRZEDAJ_MIMO_BRAKU}','tak', (string)$kod);
      } else {
        $kod = str_replace('{MAGAZYN_SPRZEDAJ_MIMO_BRAKU}','nie', (string)$kod);
    }
    $kod = str_replace('{KARTA_PRODUKTU_CENA_KATALOGOWA_TYP}', (string)KARTA_PRODUKTU_CENA_KATALOGOWA_TYP, (string)$kod);
    $kod = str_replace('{KARTA_PRODUKTU_CENA_KATALOGOWA_TYP_ZAOKRAGLENIE}', (string)KARTA_PRODUKTU_CENA_KATALOGOWA_TYP_ZAOKRAGLENIE, (string)$kod);
    $kod = str_replace('{PRODUKT_KUPOWANIE_STATUS}', (string)PRODUKT_KUPOWANIE_STATUS, (string)$kod);

    // system punktow
    $kod = str_replace( '{WARTOSC_PUNKTOW}', (string)SYSTEM_PUNKTOW_WARTOSC, (string)$kod );
    $kod = str_replace( '{WALUTA_PRZELICZNIK}', (string)$_SESSION['domyslnaWaluta']['przelicznik'], (string)$kod );
    
    // ilosc miejsc po przecinku w cenach
    $kod = str_replace( '{ILOSC_MIEJSC_PRZECINEK}', (string)CENY_MIEJSCA_PO_PRZECINKU, (string)$kod );

    $kod = str_replace( '{KATALOG_ZDJEC}', (string)KATALOG_ZDJEC, (string)$kod );

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

// zakladka z cookie
if (isset($_COOKIE['zakladka']) && $_COOKIE['zakladka'] != 'brak') {
    $kod = str_replace('{ZAKLADKA}', (string)$_COOKIE['zakladka'], (string)$kod);
  } else {
    $kod = str_replace('{ZAKLADKA}', '', (string)$kod);
}

// podstawia znak waluty i separatora dziesietnego
$kod = str_replace('{SYMBOL}', (string)$_SESSION['domyslnaWaluta']['symbol'], (string)$kod);
$kod = str_replace('{SEPARATOR_DZIESIETNY}', (string)$_SESSION['domyslnaWaluta']['separator'], (string)$kod);
$kod = str_replace('{OMNIBUS_KATALOGOWE}', (string)HISTORIA_CEN_CENY_KATALOGOWE, (string)$kod);
$kod = str_replace('{OMNIBUS_PROMOCJE}', (string)HISTORIA_CEN_PROMOCJE, (string)$kod);

// zamiana tokenu bezpieczenstwa
$kod = str_replace( '{__DOMYSLNY_SZABLON}', (string)DOMYSLNY_SZABLON, (string)$kod );
$kod = str_replace( '{__TOKEN_PRODUKT}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_ZNIZKI}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_CECHA}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_SZYBKI_ZAKUP}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_SZYBKI_ZAKUP_ZAMOW}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_NAPISZ_RECENZJE}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__MINIATURKI_MOBILE}', (string)MINIATURKI_KARTA_PRODUKTU_MOBILE, (string)$kod);

echo $kod;

unset($kod, $db, $session);

?> 