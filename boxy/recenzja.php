<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_RECENZJA_ILOSC_ZNAKOW_RECENZJI;Ilość wyświetlanych znaków tekstu recenzji;100;50,100,150,200}}
//

if ( RECENZJE_STATUS == 'tak' ) {
  
    if ( defined('BOX_RECENZJA_ILOSC_ZNAKOW_RECENZJI') ) {
       $LimitZnakow = (int)BOX_RECENZJA_ILOSC_ZNAKOW_RECENZJI;
     } else {
       $LimitZnakow = 100;
    }

    $WybraneProdukty = Produkty::ProduktyModuloweRecenzje(1);

    if (count($WybraneProdukty) > 0) {
        //   
        $Produkt = new Produkt( $WybraneProdukty[0] );
        $Produkt->ProduktRecenzje();
        //
        // szuka losowego id recenzji do wybranego produktu
        $TabliaRecenzjiProduktu = array();
        foreach ($Produkt->recenzje as $id => $wartosc) {
            $TabliaRecenzjiProduktu[] = $id;
        }
        $LosowaRecenzja = Funkcje::wylosujElementyTablicyJakoTekst($TabliaRecenzjiProduktu);
        //
        echo '<div class="BoxImgDuzy">';
        //
        echo $Produkt->recenzje[$LosowaRecenzja]['recenzja_zdjecie_link_ikony'];
        echo '<h3>' . $Produkt->recenzje[$LosowaRecenzja]['recenzja_link'] . '</h3>';
        //
        echo '</div>';
        //
        echo '<div class="OpisText">' . Funkcje::przytnijTekst(strip_tags((string)$Produkt->recenzje[$LosowaRecenzja]['recenzja_tekst']), $LimitZnakow) . '<br />';
        echo $Produkt->recenzje[$LosowaRecenzja]['recenzja_ocena_obrazek'] . '</div>';
        //
        echo '<div class="WszystkieKreska"><a href="recenzje.html">{__TLUMACZ:ZOBACZ_WSZYSTKIE}</a></div>';
        //        
        unset($Produkt, $TabliaRecenzjiProduktu, $LosowaRecenzja);
        //
    }

    unset($WybraneProdukty, $LimitZnakow);
    
}
?>