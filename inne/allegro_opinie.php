<?php
mb_internal_encoding("UTF-8");

chdir('../');

require_once('ustawienia/ustawienia_db.php');
 
define('POKAZ_ILOSC_ZAPYTAN', false);
define('DLUGOSC_SESJI', '9000');
define('NAZWA_SESJI', 'eGold');

include 'klasy/Bazadanych.php';
$db = new Bazadanych();

include 'klasy/Sesje.php';
$session = new Sesje((int)DLUGOSC_SESJI);

include 'klasy/CacheJs.php';
$cacheJs = new CacheJs();
$StaleDefinicjeJs = $cacheJs->CacheJsFunc();        

$TablicaOcen = @unserialize(ZAKLADKA_ALLEGRO_OPINIE_TABLICA);

if ( is_object($TablicaOcen) ) {

    if ( isset($TablicaOcen->recommended) ) {
      
        echo '<div class="NaglowekOpinieAllegro">
                Użytkownik <b>' . ZAKLADKA_ALLEGRO_OPINIE_NAZWA . '</b><br />' . 
                $TablicaOcen->recommendedPercentage . '% kupujących poleca tego sprzedającego
              </div>';
              
        echo '<div class="SredniaOcenAllegro">Średnia z ' . (int)$TablicaOcen->recommended->total . ' ocen sprzedaży</div>'; 
        
        echo '<div class="ListaAukcjiAllegro"><a href="https://allegro.pl/uzytkownik/' . $_POST['id'] . '/oceny" target="_blank">Zobacz listę aukcji</a></strong></div>';
        
    }
    
}

?>