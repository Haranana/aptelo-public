<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_NASZ_HIT_LISTA_ILOSC_PRODUKTOW;Ilość wyświetlanych w boxie produktów;4;1,2,3,4,5,6,7,8,9,10}}
//

if ( defined('BOX_NASZ_HIT_LISTA_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)BOX_NASZ_HIT_LISTA_ILOSC_PRODUKTOW;
 } else {
   $LimitZapytania = 4;
}

$WybraneProdukty = Produkty::ProduktyModulowe($LimitZapytania, 'hity');

if (count($WybraneProdukty) > 0) { 
    //
    echo '<ul class="Lista">';
    //
    for ( $i = 0, $c = count($WybraneProdukty); $i < $c; $i++ ) {
        //
        $Produkt = new Produkt( $WybraneProdukty[$i] );
        //    
        echo '<li><h3>' . $Produkt->info['link'] . '</h3></li>';
        //
        unset($Produkt);
        //
    }
    //
    echo '</ul>';
    //
    echo '<div class="Wszystkie"><a href="hity.html">{__TLUMACZ:ZOBACZ_WSZYSTKIE}</a></div>';     
}

unset($WybraneProdukty);
?>