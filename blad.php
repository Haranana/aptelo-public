<?php

// wczytanie ustawien inicjujacych system
require_once(dirname(__FILE__).'/ustawienia/init.php');

// sprawdza czy nie bylo przekierowania adresow
Przekierowania::SprawdzPrzekierowania();

if ( strpos((string)$_SERVER['REDIRECT_URL'], 'favicon') === false ) {

    switch($_SERVER['REDIRECT_STATUS']) {
      case 400:
          header('HTTP/1.1 400 Bad Request');
          header("Location: " . ADRES_URL_SKLEPU . "/blad-400.html");
          break;
      case 401:
          header('HTTP/1.1 401 Unauthorized');
          header("Location: " . ADRES_URL_SKLEPU . "/blad-401.html");
          break;
      case 403:
          header('HTTP/1.1 403 Forbidden');
          header("Location: " . ADRES_URL_SKLEPU . "/blad-403.html");
          break;
      case 404:
          header('HTTP/1.1 404 Not Found');
          header("Location: " . ADRES_URL_SKLEPU . "/brak-strony.html");
          break;
      case 500:
          header('HTTP/1.1 500 Internal Server Error');
          header("Location: " . ADRES_URL_SKLEPU . "/blad-500.html");
          break;
      case 503:
          header('HTTP/1.1 503 Service Unavailable');
          header("Location: " . ADRES_URL_SKLEPU . "/blad-503.html");
          break;
      default:
          header('HTTP/1.1 404 Not Found');
          header("Location: " . ADRES_URL_SKLEPU . "/brak-strony.html");
        break;
    }

}

exit();
?>