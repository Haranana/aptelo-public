<?php
// czy jest zapytanie
if (count($TablicaProducentow) > 0) { 

    echo '<div class="OknaRwd Kol-' . LISTING_ILOSC_KOLUMN_PRODUCENT . ' KolMobile-' . LISTING_ILOSC_KOLUMN_MOBILE . ' ListingProducenciKontener">';

    foreach ($TablicaProducentow As $IdProducenta => $TablicaDane) {
        //
        echo '<div class="Producent ProducentOknoListing OknoRwd">';
        
            echo '<div class="ElementListingRamka ElementProducentRamka">';

                echo '<div class="ElementProducentFoto">';
                //
                if ( !empty($TablicaDane['Foto']) ) {
                    //
                    echo '<a href="' . Seo::link_SEO( $TablicaDane['Nazwa'], $IdProducenta, 'producent' ) . '">' . Funkcje::pokazObrazek($TablicaDane['Foto'], $TablicaDane['Nazwa'], SZEROKOSC_MINIATUREK_KATEGORII_PRODUCENTOW, WYSOKOSC_MINIATUREK_KATEGORII_PRODUCENTOW, array(), '', 'maly', true, false, false) . '</a>';
                    //
                }
                //
                echo '</div>';
                
                // jezeli jest wlaczona opcja pokazywania ilosci produktow z kategorii
                $SumaProduktow = '';
                if (LISTING_ILOSC_PRODUKTOW == 'tak') {
                    $SumaProduktow = '<em>('.$TablicaDane['IloscProduktow'] . ')</em>';
                }            
                //
                echo '<h3><a href="' . Seo::link_SEO( $TablicaDane['Nazwa'], $IdProducenta, 'producent' ) . '">' . $TablicaDane['Nazwa'] . $SumaProduktow . '</a></h3>';
                
            echo '</div>';

        echo '</div>';

    }

    echo '</div>';
    //
    echo '<div class="cl"></div>';

} else {

    echo '<div id="BrakProduktow" class="Informacja">{__TLUMACZ:BLAD_BRAK_PRODUCENTOW}</div>';
  
}

?>