<?php
chdir('../'); 

// ************** czesc kodu wymagana w przypadku zadan harmonogramu zadan **************

// zmienna zeby nie odczytywalo ponownie crona
$BrakCron = true;

// wczytanie ustawien inicjujacych system
//require_once('ustawienia/init.php');
define('POKAZ_ILOSC_ZAPYTAN', false);
define('DLUGOSC_SESJI', '9000');
define('NAZWA_SESJI', 'eGold');
define('WLACZENIE_CACHE', 'tak');

require_once('ustawienia/ustawienia_db.php');
include('klasy/Bazadanych.php');
$db = new Bazadanych();
include('klasy/Funkcje.php');
include('klasy/CacheSql.php');

// ************** koniec **************

$db->delete_query('session_data_customers' , " session_expire < (UNIX_TIMESTAMP() - 56700) ");

// ************** koniec **************

?>