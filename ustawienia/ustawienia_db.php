<?php

date_default_timezone_set('Europe/Warsaw');

define('DB_SERVER', 'localhost');
define('DB_PORT', '3306');
define('DB_SERVER_USERNAME', 'srv90823_test_db');
define('DB_SERVER_PASSWORD', 'B9UD4dNS6y2sMzTzt8Ep'); 
define('DB_DATABASE', 'srv90823_test_db');

require_once(str_replace(DIRECTORY_SEPARATOR.'zarzadzanie','',dirname(__FILE__)).DIRECTORY_SEPARATOR.'ustawienia_ssl.php');

if ( WLACZENIE_SSL == 'tak' ) {
define('ADRES_URL_SKLEPU', 'https://aptelo.pl');
} else {
define('ADRES_URL_SKLEPU', 'http://aptelo.pl');
}

define('ADRES_URL_SKLEPU_SSL', 'https://aptelo.pl');

define('KATALOG_SKLEPU', '/home/srv90823/domains/aptelo.pl/public_html/');
?>