<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_PRODUKTY_Z_RECENZJAMI_ILOSC_PRODUKTOW;Ilość wyświetlanych w produktów;4;1,2,3,4,5,6,7,8,9,10}}
// {{MODUL_PRODUKTY_Z_RECENZJAMI_ILOSC_KOLUMN;W ilu kolumnach mają być wyświetlane produkty;2;1,2,3,4,5}}
//

if ( RECENZJE_STATUS == 'tak' ) {

    // zmienne bez definicji
    $LimitZapytania = 4;
    $IloscKolumn = 2;

    if ( defined('MODUL_PRODUKTY_Z_RECENZJAMI_ILOSC_PRODUKTOW') ) {
       $LimitZapytania = (int)MODUL_PRODUKTY_Z_RECENZJAMI_ILOSC_PRODUKTOW;
    }
    if ( defined('MODUL_PRODUKTY_Z_RECENZJAMI_ILOSC_KOLUMN') ) {
       $IloscKolumn = (int)MODUL_PRODUKTY_Z_RECENZJAMI_ILOSC_KOLUMN;
    }

    $WybraneProdukty = Produkty::ProduktyModuloweRecenzje($LimitZapytania);

    if (count($WybraneProdukty) > 0) {
          
        echo '<div class="OknaRwd Kol-' . $IloscKolumn . '">';

        for ($v = 0, $cs = count($WybraneProdukty); $v < $cs; $v++) {

            echo '<article class="ProduktZlozony OknoRwd">';

                $Produkt = new Produkt( $WybraneProdukty[$v] );
                $Produkt->ProduktRecenzje();
                //                      
                echo '<div class="Foto">'.$Produkt->fotoGlowne['zdjecie_link_ikony'].'</div>';
                //
                echo '<div class="ProdCena" style="margin-left:' . (SZEROKOSC_OBRAZEK_MALY+40) . 'px">';
                
                echo '<h3>' . $Produkt->info['link'] . '</h3>';
                //
                echo '<div class="Ocena">' . $Produkt->recenzjeSrednia['srednia_ocena_obrazek'];
                
                echo '<span>{__TLUMACZ:SREDNIA_OCENA_PRODUKTU}: <strong>' .$Produkt->recenzjeSrednia['srednia_ocena'] . '/5 </strong> <br /> ({__TLUMACZ:ILOSC_GLOSOW}: ' . $Produkt->recenzjeSrednia['ilosc_glosow'] . ')</span>';
                
                echo '</div>';          
                //
                echo '</div>';
                
                echo '<div class="cl"></div>';
                
                echo '<div class="Opis LiniaGorna">' . $Produkt->info['opis_krotki'] . '</div>';
                //
                unset($Produkt);

            echo '</article>';

        }

        echo '</div>';
        //
        echo '<div class="cl"></div>';

    }

    unset($WybraneProdukty, $IloscKolumn, $LimitZapytania);
    
}
?>