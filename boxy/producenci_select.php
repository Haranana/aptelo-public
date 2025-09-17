<?php

$Tablica = Producenci::TablicaProducenciSelect();

if (count($Tablica) > 1) {

    echo '<div class="SrodekCentrowany cmxform">';
    
    // tworzenie tablicy producentow
    $Producenci = array();
    foreach ( $Tablica as $Producent ) {
        //
        if ( $Producent['id'] > 0 ) {
             $Producenci[] = array( 'id' => Seo::link_SEO( $Producent['text'], $Producent['id'], 'producent' ), 'text' => $Producent['text'] );
           } else { 
             $Producenci[] = array( 'id' => '', 'text' => $Producent['text'] );
        }
        //
    }
    
    // aktywny
    $Aktywny = '';
    if ((isset($_GET['idproducent']) && (int)$_GET['idproducent'] > 0)) {
         if ( $Producent['id'] > 0 ) {
              //
              $NazwaProducenta = Producenci::NazwaProducenta((int)$_GET['idproducent']);
              $Aktywny = Seo::link_SEO( $NazwaProducenta['nazwa'], (int)$_GET['idproducent'], 'producent' );
              unset($NazwaProducenta);
              //
         }
    }
    echo Funkcje::RozwijaneMenu('producent', $Producenci, $Aktywny, 'style="width:90%;" id="WybierzProducenta"');
    
    unset($Producenci, $Aktywny);
    
    echo '</div>';

}

unset($Tablica);

?>