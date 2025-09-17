<?php
// czy jest zapytanie
if (count($TablicaKategorii) > 0) { 

    echo '<div class="OknaRwd Kol-' . LISTING_ILOSC_KOLUMN_PRODUCENT . ' KolMobile-' . LISTING_ILOSC_KOLUMN_MOBILE . ' ListingKategorieKontener">';

    foreach ($TablicaKategorii As $IdKategorii => $TablicaDane) {
      
        echo '<div class="Producent KategoriaOknoListing OknoRwd">';
        
            echo '<div class="ElementListingRamka ElementKategoriaRamka">';

                echo '<div class="ElementKategoriaFoto">';
                //
                if ( !empty($TablicaDane['Foto']) ) {
                     //
                     echo '<a href="' . Seo::link_SEO( $TablicaDane['Nazwa'], $IdKategorii, 'kategoria' ) . '">' . Funkcje::pokazObrazek($TablicaDane['Foto'], $TablicaDane['Nazwa'], SZEROKOSC_MINIATUREK_PODKATEGORII, WYSOKOSC_MINIATUREK_PODKATEGORII, array(), '', 'maly', true, false, false) . '</a>';
                     //
                }
                //
                echo '</div>';
                
                // jezeli jest wlaczona opcja pokazywania ilosci produktow z kategorii
                $SumaProduktow = '';
                if ( LISTING_ILOSC_PRODUKTOW == 'tak' ) {
                     //
                     $SumaProduktow = '<em>('.$TablicaDane['WszystkichProduktow'] . ')</em>';
                     //
                }            
                //
                echo '<h3><a href="' . Seo::link_SEO( $TablicaDane['Nazwa'], $IdKategorii, 'kategoria' ) . '">' . $TablicaDane['Nazwa'] . $SumaProduktow . '</a></h3>';
                
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