<?php
// waluta
if (isset($TablicaDane['Waluta']) && trim((string)$TablicaDane['Waluta']) != '') {
    //
    $zapytanieWaluta = "select currencies_id, code from currencies where code = '" . addslashes((string)$filtr->process($TablicaDane['Waluta'])) . "'";
    $sqlw = $db->open_query($zapytanieWaluta); 
    //
    if ((int)$db->ile_rekordow($sqlw) > 0) {
        //
        $info = $sqlw->fetch_assoc();
        $pola[] = array('products_currencies_id',$info['currencies_id']);
        //   
        $JestPodatek = true;
        //
    }
    //
    $db->close_query($sqlw);
    unset($zapytanieWaluta);
    //
} else {
    //
    if ($CzyDodawanie == true) {
        //
        // jezeli nie ma waluty a jest dodawanie produktu przyjmuje domyslna
        $pola[] = array('products_currencies_id',$_SESSION['domyslna_waluta']['id']);
        //
    }
    //
}    
?>