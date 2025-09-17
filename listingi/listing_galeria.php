<?php
// czy jest zapytanie
if ($IloscObrazkow > 0) { 

    echo '<div class="OknaRwd Kol-' . $IloscKolumn . ' ListingGaleriaKontener">';

    while ($info = $sql->fetch_assoc()) {
        //
        echo '<article class="KomorkaTbl OknoRwd">';
        
            echo '<div class="ElementListingRamka">';

                if (is_file(KATALOG_ZDJEC . '/' . $info['gallery_image'])) {
                  
                    echo '<div class="KontenerZdjecieGalerii">';

                    if ( TEKST_COPYRIGHT_POKAZ == 'tak' || OBRAZ_COPYRIGHT_POKAZ == 'tak' ) {
                         //
                         $zdjecie = Funkcje::pokazObrazekWatermark($info['gallery_image']);
                         //
                    } else {
                         //
                         $zdjecie = KATALOG_ZDJEC . '/' . $info['gallery_image'];
                         //
                    }                
                    
                    if ( $Miniatury == '1' ) { 
                         //
                         if ( $Kadrowanie == '1' ) {
                              //
                              echo '<a class="ZdjecieGalerii" href="' . $zdjecie . '" title="' . $info['gallery_image_alt'] . '" data-jbox-image="gallery">' . Funkcje::pokazObrazekKadrowany($info['gallery_image'], $info['gallery_image_alt'], $SzeImg, $WysImg) . '</a>';
                              //
                         } else {
                              //
                              echo '<a class="ZdjecieGalerii" href="' . $zdjecie . '" title="' . $info['gallery_image_alt'] . '" data-jbox-image="gallery">' . Funkcje::pokazObrazek($info['gallery_image'], $info['gallery_image_alt'], $SzeImg, $WysImg) . '</a>';   
                              //
                         }
                         //
                    } else {
                         //
                         echo '<a class="ZdjecieGalerii"  href="' . $zdjecie . '" data-jbox-image="gallery"><img src="' . KATALOG_ZDJEC . '/' . $info['gallery_image'] . '" alt="' . $info['gallery_image_alt'] . '" title="' . $info['gallery_image_alt'] . '" /></a>';
                         //
                    }
                    
                    unset($zdjecie);
                    
                    echo '</div>';
                }

                echo '<div class="OpisGaleria">' . $info['gallery_image_description'] . '</div>';
                
             echo '</div>';

        echo '</article>';

    }
    
    echo '</div>';
    //
    echo '<div class="cl"></div>';
    
    unset($info);
    
} else {

    echo '<div id="BrakProduktow" class="Informacja">{__TLUMACZ:BLAD_ZDJEC_GALERII}</div>';
  
}

unset($WysImg, $SzeImg, $IloscKolumn, $Kadrowanie, $Miniatury); 
   
?>
