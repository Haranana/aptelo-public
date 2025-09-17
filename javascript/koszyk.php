<?php
chdir('../');

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init_ajax.php');

if ( !isset($_SESSION['domyslnyJezyk']['kod']) ) { $_SESSION['domyslnyJezyk']['kod'] = 'pl'; }
if ( !isset($_SESSION['domyslnyJezyk']['id']) ) { $_SESSION['domyslnyJezyk']['id'] = '1'; }

$PlikCacheJs = 'cache/js/koszyk_' . $_SESSION['domyslnyJezyk']['kod'] . '.jcs';

if (!file_exists($PlikCacheJs) || CACHE_JS == 'nie') {

    include 'klasy/Jezyki.php';
    include 'klasy/Translator.php';
    include 'klasy/jsMin.php';

    $kod = '';
    $kod .= file_get_contents('javascript/koszyk.jcs');

    // tlumaczenia
    $i18n = new Translator($_SESSION['domyslnyJezyk']['id']);
    $tlumacz = $i18n->tlumacz( array('PRODUKT','FORMULARZ','KUPONY_RABATOWE','PRZYCISKI') );

    // konwersja danych jezykowych
    $preg = preg_match_all('|{__TLUMACZ:([0-9A-Z_]+?)}|', $kod, $matches);
    foreach ($matches[1] as $WartoscJezykowa) {
        $kod = str_replace('{__TLUMACZ:' . $WartoscJezykowa . '}', str_replace("'", "&apos;", (string)nl2br($tlumacz[$WartoscJezykowa])), (string)$kod);
    }

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
$kod = str_replace( '{__AKCJA_KOSZYKA}', (string)PRODUKT_OKNO_POPUP_USUN, (string)$kod );
$kod = str_replace( '{__AKCJA_SCHOWKA}', (string)PRODUKT_OKNO_SCHOWEK_POPUP, (string)$kod ); 
$kod = str_replace( '{__TOKEN_KOSZYK}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_KOSZYK_KOMENTARZ}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_KOSZYK_USUN}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_KOSZYK_USUN_PRZELICZ}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_KUPON_AKTYWUJ}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_OPAKOWANIE_OZDOBNE_AKTYWUJ}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_PUNKTY_AKTYWUJ}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_ZAPISZ_KOSZYK}', (string)Sesje::Token(), (string)$kod );

$SystemyRatalne = array();
$SystemyRatalne = AktywneSystemyRatalne();

// system ratalny Santander
if ( isset($SystemyRatalne['platnosc_santander']) && count($SystemyRatalne['platnosc_santander']) > 0 ) {
    $kod = str_replace( '{__SANTANDER_NUMER_SKLEPU}', (string)$SystemyRatalne['platnosc_santander']['PLATNOSC_SANTANDER_NUMER_SKLEPU'], (string)$kod );
    $kod = str_replace( '{__SANTANDER_WARIANT_SKLEPU}', (string)$SystemyRatalne['platnosc_santander']['PLATNOSC_SANTANDER_WARIANT_SKLEPU'], (string)$kod );
} else {
    $kod = str_replace( '{__SANTANDER_NUMER_SKLEPU}', '13010005', (string)$kod );
    $kod = str_replace( '{__SANTANDER_WARIANT_SKLEPU}', '1', (string)$kod );
}

// system ratalny LUKAS
if ( isset($SystemyRatalne['platnosc_lukas']) && count($SystemyRatalne['platnosc_lukas']) > 0 ) {
    $kod = str_replace( '{__LUKAS_NUMER_SKLEPU}', (string)$SystemyRatalne['platnosc_lukas']['PLATNOSC_LUKAS_NUMER_SKLEPU'], (string)$kod );
} else {
    $kod = str_replace( '{__LUKAS_NUMER_SKLEPU}', 'PSP1013102', (string)$kod );
}

// system ratalny MBANK
if ( isset($SystemyRatalne['platnosc_mbank']) && count($SystemyRatalne['platnosc_mbank']) > 0 ) {
    $kod = str_replace( '{__MBANK_NUMER_SKLEPU}', (string)$SystemyRatalne['platnosc_mbank']['PLATNOSC_MBANK_NUMER_SKLEPU'], (string)$kod );
} else {
    $kod = str_replace( '{__MBANK_NUMER_SKLEPU}', '', (string)$kod );
}

// system ratalny iLeasing
if ( isset($SystemyRatalne['platnosc_ileasing']) && count($SystemyRatalne['platnosc_ileasing']) > 0 ) {
    $kod = str_replace( '{__ILEASING_PARTNERID}', (string)$SystemyRatalne['platnosc_ileasing']['PLATNOSC_ILEASING_PARTNERID'], (string)$kod );
} else {
    $kod = str_replace( '{__ILEASING_PARTNERID}', '', (string)$kod );
}

// system ratalny iRaty
if ( isset($SystemyRatalne['platnosc_iraty']) && count($SystemyRatalne['platnosc_iraty']) > 0 ) {
    $kod = str_replace( '{__IRATY_PARTNERID}', (string)$SystemyRatalne['platnosc_iraty']['PLATNOSC_IRATY_PARTNERID'], (string)$kod );
} else {
    $kod = str_replace( '{__IRATY_PARTNERID}', '', (string)$kod );
}

// system ratalny BGZ
if ( isset($SystemyRatalne['platnosc_bgz']) && count($SystemyRatalne['platnosc_bgz']) > 0 ) {
    $kod = str_replace( '{__BGZ_NUMER_SKLEPU}', (string)$SystemyRatalne['platnosc_bgz']['PLATNOSC_BGZ_NUMER_SKLEPU'], (string)$kod );
    $kod = str_replace( '{__BGZ_NUMER_KREDYTU}', str_replace('|', ';', (string)$SystemyRatalne['platnosc_bgz']['PLATNOSC_BGZ_NUMER_KREDYTU']), (string)$kod );
} else {
    $kod = str_replace( '{__BGZ_NUMER_SKLEPU}', '', (string)$kod );
    $kod = str_replace( '{__BGZ_NUMER_KREDYTU}', '', (string)$kod );
}
echo $kod;

unset($kod, $db, $session, $SystemyRatalne);

// funkcja zwracajaca tablice aktywnych systemow ratalnych
function AktywneSystemyRatalne() {

    $SystemyRatalne = array();

    $zapSystemyRatalne = "
                             SELECT p.id, p.klasa, pp.kod, pp.wartosc FROM modules_payment p
                             LEFT JOIN modules_payment_params pp ON p.id = pp.modul_id WHERE p.status = '1' AND (p.klasa = 'platnosc_santander' OR p.klasa = 'platnosc_lukas' OR p.klasa = 'platnosc_mbank' OR p.klasa = 'platnosc_bgz' OR p.klasa = 'platnosc_ileasing' OR p.klasa = 'platnosc_iraty')";
    $sql = $GLOBALS['db']->open_query($zapSystemyRatalne);
    //
    while ($info = $sql->fetch_assoc()) {
        $SystemyRatalne[$info['klasa']][$info['kod']] = $info['wartosc'];      
    }
    //
    $GLOBALS['db']->close_query($sql);
    //        
    unset($zapSystemyRatalne, $info, $sql);    
    
    return $SystemyRatalne; 

}

?>  