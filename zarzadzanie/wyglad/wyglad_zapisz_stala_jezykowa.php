<?php
chdir('../');     

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr()) {

    // pobieranie id stalej jezykowej
    $zapytanie_jezyk = "select distinct * from translate_constant where translate_constant = '" . strtoupper((string)$filtr->process($_POST['stala']). "'");
    $sqls = $db->open_query($zapytanie_jezyk);
    $id_stalej = $sqls->fetch_assoc();   
    
    $db->close_query($sqls);

    $pola = array(
            array('translate_value',$filtr->process($_POST['wart'])));
            
    $sql = $db->update_query('translate_value', $pola, " translate_constant_id = '".$id_stalej['translate_constant_id']."' and language_id = '" .$filtr->process($_POST['jezyk'])."'");	
    unset($pola); 
    
}
?>