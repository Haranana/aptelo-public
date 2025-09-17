<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $request = $filtr->process(trim((string)$_REQUEST['kod']));
    $valid = 'true';

    $zapytanie = "select coupons_name from coupons where coupons_name = '".$request."'";

    $sql = $db->open_query($zapytanie);
    if ((int)$db->ile_rekordow($sql) > 0) {
      $valid = 'false';
    }

    $db->close_query($sql);

    echo $valid;

}
?>