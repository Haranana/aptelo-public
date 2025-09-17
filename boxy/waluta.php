<?php
$tablica = array();

if (count($GLOBALS['waluty']->waluty) > 0) { 

    foreach ($GLOBALS['waluty']->waluty as $key => $value) {
        $tablica[] = array('id' => $value['id'], 'text' => $value['nazwa']);
    }
    //
    echo '<div class="SrodekCentrowany cmxform">';
    echo Funkcje::RozwijaneMenu('waluta', $tablica, $_SESSION['domyslnaWaluta']['id'] , 'style="width:120px;" id="WybierzWalute"');
    echo '</div>';
    //
    
}

unset($tablica);
?>