<?php
// Obsluga bledow
    ini_set('display_errors', 0);
    error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Ustawienia parametrow PHP
    ini_set('upload_max_filesize', '100M');
    ini_set('default_charset', 'utf-8');

// Ustawienie kodowania Apache
    if (!headers_sent())
      header('Content-Type: text/html; charset=utf-8');

// Te dane trzeba bedzie umiescic w bazie
    define('DLUGOSC_SESJI', '9000');  // nie wieksze niz 1140
    define('NAZWA_SESJI', 'aGold');

// Rozszerzenia plikow, ktore moga byc zaladowane
    spl_autoload_extensions('.php, .class.php, .lib.php');

// Ladowanie klas
    function zaladujKlasy($klasa) {
      $nazwapliku = $klasa . '.php';
      $plik ='klasy'.DIRECTORY_SEPARATOR.$nazwapliku;
      if (!file_exists($plik)) {
        return false;
      }
      include $plik;
    }

// Zarejestrowanie funkcji ladujacej klasy
    spl_autoload_register('zaladujKlasy');

// Wczytanie danych do polaczenia z baza mySQL
    require_once(str_replace(DIRECTORY_SEPARATOR.'zarzadzanie','',dirname(__FILE__)).DIRECTORY_SEPARATOR.'ustawienia_db.php');
?>