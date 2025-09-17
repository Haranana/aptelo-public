<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_RECENZJE_MINIATURKI_ILOSC_PRODUKTOW;Ilość wyświetlanych w boxie recenzji;4;1,2,3,4,5,6,7,8,9,10}}
// {{BOX_RECENZJE_MINIATURKI_CZY_POKAZYWAC_TRESC_RECENZJI;Czy pokazywać fragment treści recenzji;tak;tak,nie}}
// {{BOX_RECENZJE_MINIATURKI_ILOSC_ZNAKOW_RECENZJI;Ilość wyświetlanych znaków tekstu recenzji;50;50,100,150,200}}
// {{BOX_RECENZJE_MINIATURKI_ROZMIAR_IMG;Rozmiar zdjęcia produktu w pixelach;50;50,70,90,100,120,150,170}}
//

if ( RECENZJE_STATUS == 'tak' ) {
  
    if ( defined('BOX_RECENZJE_MINIATURKI_ILOSC_PRODUKTOW') ) {
         $LimitZapytania = (int)BOX_RECENZJE_MINIATURKI_ILOSC_PRODUKTOW;
     } else {
         $LimitZapytania = 4;
    }
    if ( defined('BOX_RECENZJE_MINIATURKI_CZY_POKAZYWAC_TRESC_RECENZJI') ) {
         $PokazywacTekst = BOX_RECENZJE_MINIATURKI_CZY_POKAZYWAC_TRESC_RECENZJI;
     } else {
         $PokazywacTekst = 'tak';
    }
    if ( defined('BOX_RECENZJE_MINIATURKI_ILOSC_ZNAKOW_RECENZJI') ) {
         $LimitZnakow = (int)BOX_RECENZJE_MINIATURKI_ILOSC_ZNAKOW_RECENZJI;
     } else {
         $LimitZnakow = 50;
    }
    if ( defined('BOX_RECENZJE_MINIATURKI_ROZMIAR_IMG') ) {
         $RozmiarImg = (int)BOX_RECENZJE_MINIATURKI_ROZMIAR_IMG;
     } else {
         $RozmiarImg = 50;
    }

    $WybraneProdukty = Produkty::ProduktyModuloweRecenzje($LimitZapytania);

    if (count($WybraneProdukty) > 0) {
        //
        echo '<ul class="BoxImgMaly BezLinii">';
        //
        for ( $i = 0, $c = count($WybraneProdukty); $i < $c; $i++ ) {
            //         
            $Produkt = new Produkt( $WybraneProdukty[$i], $RozmiarImg, $RozmiarImg );
            $Produkt->ProduktRecenzje();
            //
            /// szuka losowego id recenzji do wybranego produktu
            $TabliaRecenzjiProduktu = array();
            foreach ($Produkt->recenzje as $id => $wartosc) {
                    $TabliaRecenzjiProduktu[] = $id;
            }
            $LosowaRecenzja = Funkcje::wylosujElementyTablicyJakoTekst($TabliaRecenzjiProduktu);
            //
            echo '<li>
                    <p class="Img" style="width:' . $RozmiarImg . 'px">' . $Produkt->recenzje[$LosowaRecenzja]['recenzja_zdjecie_link'] . '</p>
                    <div class="PrdDane" style="width:calc(100% - ' . ($RozmiarImg + 20) . 'px)">
                    
                        <h3>' . $Produkt->recenzje[$LosowaRecenzja]['recenzja_link'] . $Produkt->recenzje[$LosowaRecenzja]['recenzja_ocena_obrazek'] . '</h3>';
                                
                        if ( $PokazywacTekst == 'tak' ) {
                                echo '<div class="OpisText">' . Funkcje::przytnijTekst(strip_tags((string)$Produkt->recenzje[$LosowaRecenzja]['recenzja_tekst']), $LimitZnakow) . '</div>';                                
                        }
                    
                    echo '</div>';
                            
            echo '</li>'; 
            //
            unset($Produkt, $TabliaRecenzjiProduktu, $LosowaRecenzja);
        }
        //
        echo '</ul>';
        //
        echo '<div class="WszystkieKreska"><a href="recenzje.html">{__TLUMACZ:ZOBACZ_WSZYSTKIE}</a></div>';
        // 
    }

    unset($RozmiarImg, $WybraneProdukty, $LimitZapytania, $LimitZnakow, $PokazywacTekst);
    
}
?>