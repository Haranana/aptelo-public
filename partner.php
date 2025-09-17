<?php
// id z linku
$Partner = $_GET['id'];

// tablice do podzialu
$Litery = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 's');
$Cyfry = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0', ',');

// dzieli na dni i id i tworzy tablice
$Podzial = explode(',', str_replace($Litery, $Cyfry, (string)$Partner));

if ( count($Podzial) == 2 && (int)$Podzial[0] > 0 && (int)$Podzial[1] > 0 ) {
     //
     define('POKAZ_ILOSC_ZAPYTAN', false);
     define('DLUGOSC_SESJI', '9000');
     define('NAZWA_SESJI', 'eGold');
     //
     require_once('ustawienia/ustawienia_db.php');
     //      
     include 'klasy/Bazadanych.php';
     $db = new Bazadanych();
     include 'klasy/Sesje.php';
     $session = new Sesje((int)DLUGOSC_SESJI);     
     //
     // dodaje do statystyki partnera
     // musi sprawdzic ilosc wyswietlen
     $zapytanie = 'SELECT pp_statistics FROM customers WHERE customers_id = "' . (int)$Podzial[0] . '"';
     $sql = $db->open_query($zapytanie);
     
     if ( (int)$db->ile_rekordow($sql) > 0 ) {
     
         $info = $sql->fetch_assoc();
         //
         // aktualizuje statystyki
         $pola = array(array('pp_statistics', $info['pp_statistics'] + 1));		
         $GLOBALS['db']->update_query('customers' , $pola, " customers_id = '" . (int)$Podzial[0] . "'");	
         unset($pola);      
         //
         $IdKlienta = (int)$Podzial[0];
         $IloscDni = (int)$Podzial[1];
         //
         // tworzy ciasteczko
         setcookie("pp", $IdKlienta, time() + ($IloscDni * 86400), '/');
         //
         
         unset($info);
         
     }
     
     $db->close_query($sql);
     unset($zapytanie);
     //     
}
    
header("Location: /");
?>