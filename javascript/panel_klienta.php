<?php
chdir('../');

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init_ajax.php');

$PlikCacheJs = 'cache/js/panel_klienta.jcs';

if (!file_exists($PlikCacheJs) || CACHE_JS == 'nie') {

    include 'klasy/jsMin.php';

    $kod = '';
    $kod .= file_get_contents('javascript/panel_klienta.jcs');

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
$kod = str_replace( '{__TOKEN_WCZYTAJ_KOSZYK}', (string)Sesje::Token(), (string)$kod );

echo $kod;

unset($kod, $db, $session);

?>