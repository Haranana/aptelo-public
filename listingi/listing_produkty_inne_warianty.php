<?php
if ( count($Produkt->InneWarianty) > 1 ) { 

    echo '<div class="ListaInneWarianty' . (($Produkt->info['inne_warianty']['nazwa'] == 'tak' || $Produkt->info['inne_warianty']['cena'] == 'tak') ? ' OknoWariantNazwa' : '') . '">';

    foreach ( $Produkt->InneWarianty as $InnyWariant ) {
      
        $ProduktWariant = new Produkt( $InnyWariant['id'] );
        
        if ( $ProduktWariant->CzyJestProdukt ) {
          
             echo '<div class="OknoWariant' . (($InnyWariant['id'] == $Produkt->info['id']) ? ' Aktywny' : '') . '" title="' . str_replace('"', "", (string)$ProduktWariant->info['nazwa']) . '">';
             
                  echo '<a href="' . $ProduktWariant->info['adres_seo'] . '">';
                  
                      if ( $Produkt->info['inne_warianty']['foto'] == 'tak' ) {
                           //
                           echo '<span class="Foto">' . $ProduktWariant->fotoGlowne['zdjecie'] . '</span>';
                           //
                      }
                      
                      if ( $Produkt->info['inne_warianty']['nazwa'] == 'tak' ) {
                           //
                           if ( $Produkt->info['inne_warianty']['nazwa_typ'] == 'krotka' && !empty($ProduktWariant->info['nazwa_krotka']) ) {
                                //                           
                                echo '<span class="ProduktNazwa">' . $ProduktWariant->info['nazwa_krotka'] . '</span>';
                                //
                           } else {
                                //                           
                                echo '<span class="ProduktNazwa">' . $ProduktWariant->info['nazwa'] . '</span>';
                                //
                           }                                
                           //
                      }
                      
                      if ( $Produkt->info['inne_warianty']['cena'] == 'tak' ) {
                           //
                           echo '<span class="ProduktCena">' . $ProduktWariant->info['cena'] . '</span>';
                           //
                      }
                  
                  echo '</a>';

             echo '</div>';
          
        }

        unset($ProduktWariant);

    }

    echo '</div>';

}
?>