<?php
// wczytanie ustawien inicjujacych system
require_once(dirname(__FILE__).'/ustawienia/init.php');

$expiration = time() - 36000;

if ( isset($_COOKIE['akceptCookie']) ) {
     //
     setcookie("akceptCookie", "", $expiration);
     unset($_COOKIE['akceptCookie']);
     //
}
if ( isset($_COOKIE['cookieFunkcjonalne']) ) {
     //
     setcookie("cookieFunkcjonalne", "", $expiration);
     unset($_COOKIE['cookieFunkcjonalne']);
     //
}  
if ( isset($_COOKIE['cookieAnalityczne']) ) {
     //
     setcookie("cookieAnalityczne", "", $expiration);
     unset($_COOKIE['cookieAnalityczne']);
     //
}  
if ( isset($_COOKIE['cookieReklamowe']) ) {
     //
     setcookie("cookieReklamowe", "", $expiration);
     unset($_COOKIE['cookieReklamowe']);
     //
} 

header("Location: /");
exit(); 
?>