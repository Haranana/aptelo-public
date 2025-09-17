<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_PRODUCENCI_CHMURA_RODZAJ;Czy nazwy mają być w formie obrazków czy tekstu;tekst;tekst,obrazki}}
// {{BOX_PRODUCENCI_CHMURA_ROZMIAR_LOGO;Jaki rozmiar w pixelach mają mieć logotypy producentów;40;40,50,70,90,100,120,150,200}}
//

$Rodzaj = 'tekst';
$Rozdzielczosc = '30x30';

if ( defined('BOX_PRODUCENCI_CHMURA_RODZAJ') ) {
   $Rodzaj = BOX_PRODUCENCI_CHMURA_RODZAJ;
}
if ( defined('BOX_PRODUCENCI_CHMURA_ROZMIAR_LOGO') ) {
   $Rozdzielczosc = BOX_PRODUCENCI_CHMURA_ROZMIAR_LOGO;
}

$Tablica = Producenci::TablicaProducenci();

if (count($Tablica) > 1) {

    echo '<div class="SrodekCentrowany ProducenciChmura">';

    foreach ( $Tablica as $Producent ) {
        //
        if ( $Rodzaj == 'tekst' ) {
            //
            echo '<a href="' . Seo::link_SEO( $Producent['Nazwa'], $Producent['IdProducenta'], 'producent' ) . '">' . $Producent['Nazwa'] . '</a>';
            //
          } else { 
            //
            echo '<a href="' . Seo::link_SEO( $Producent['Nazwa'], $Producent['IdProducenta'], 'producent' ) . '">' . Funkcje::pokazObrazek($Producent['Foto'], $Producent['Nazwa'], $Rozdzielczosc, $Rozdzielczosc, array(), '', 'maly', true, false, false) . '</a>';
            //
        }
    }
    
    echo '</div>';

}

unset($Tablica, $Rodzaj, $Rozdzielczosc);

?>