<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_NASZ_HIT_ILOSC_PRODUKTOW;Ilość wyświetlanych w boxie hitów;1;1,2,3,4,5,6,7,8,9,10}}
//

if ( defined('BOX_NASZ_HIT_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)BOX_NASZ_HIT_ILOSC_PRODUKTOW;
 } else {
   $LimitZapytania = 1;
}

$WybraneProdukty = Produkty::ProduktyModulowe($LimitZapytania, 'hity');

if (count($WybraneProdukty) > 0) { 
    //
    for ( $i = 0, $c = count($WybraneProdukty); $i < $c; $i++ ) {
    
        echo '<div class="BoxImgDuzy">';
        //
        $Produkt = new Produkt( $WybraneProdukty[$i] );
        //
        echo $Produkt->fotoGlowne['zdjecie_link_ikony'] . '<br /><h3>' . $Produkt->info['link'] . '</h3>' . $Produkt->info['cena'];
        //
        unset($Produkt);
        //
        echo '</div>';
        
    }
    
    echo '<div class="Wszystkie"><a href="hity.html">{__TLUMACZ:ZOBACZ_WSZYSTKIE}</a></div>';
    //
}

unset($LimitZapytania, $WybraneProdukty);
?>