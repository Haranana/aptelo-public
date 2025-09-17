<?php
chdir('../');

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init_ajax.php');

if ( !isset($_SESSION['domyslnyJezyk']['kod']) ) { $_SESSION['domyslnyJezyk']['kod'] = 'pl'; }
if ( !isset($_SESSION['domyslnyJezyk']['id']) ) { $_SESSION['domyslnyJezyk']['id'] = '1'; }

$PlikCacheJs = 'cache/js/przypomnienie_hasla_' . $_SESSION['domyslnyJezyk']['kod'] . '.jcs';

if (!file_exists($PlikCacheJs) || CACHE_JS == 'nie') {

    include 'klasy/Jezyki.php';
    include 'klasy/Translator.php';
    include 'klasy/jsMin.php';

    $kod = '';
    $kod .= file_get_contents('javascript/przypomnienie_hasla.jcs');

    // tlumaczenia
    $i18n = new Translator($_SESSION['domyslnyJezyk']['id']);
    $tlumacz = $i18n->tlumacz( array('FORMULARZ') );

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
$kod = str_replace( '{__TOKEN_PRZYPOMNIENIE}', (string)Sesje::Token(), (string)$kod );
$kod = str_replace( '{__TOKEN_PRZYPOMNIENIE_OKNO}', (string)Sesje::Token(), (string)$kod );

echo $kod;

unset($kod, $db, $session);

?>