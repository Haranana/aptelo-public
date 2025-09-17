<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_PRODUCENCI_LISTA_WYSOKOSC;Jaką maksymalnie wysokość w pixelach ma mieć okno z przewiajaną listą producentów;150;100,150,200,250,300,350,400,500}}
//

$WysokoscDiv = '150';

if ( defined('BOX_PRODUCENCI_LISTA_WYSOKOSC') ) {
   $WysokoscDiv = BOX_PRODUCENCI_LISTA_WYSOKOSC;
}

$Tablica = Producenci::TablicaProducenci();

if (count($Tablica) > 1) {

    echo '<div class="ProducenciLista" style="max-height:' . $WysokoscDiv . 'px">';

    foreach ( $Tablica as $Producent ) {
        //
        echo '<a href="' . Seo::link_SEO( $Producent['Nazwa'], $Producent['IdProducenta'], 'producent' ) . '">' . $Producent['Nazwa'] . '</a>';
        //
    }
    
    echo '</div>';

}

unset($Tablica, $WysokoscDiv);

?>