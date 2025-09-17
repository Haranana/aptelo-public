<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_POLECANE_Z_OPISEM_ILOSC_PRODUKTOW;Ilość wyświetlanych w boxie produktów polecanych;1;1,2,3,4,5,6,7,8,9,10}}
// {{BOX_POLECANE_Z_OPISEM_ROZMIAR_IMG;Rozmiar zdjęcia produktu w treści;70;50,70,90,100,120,150,170}}
//

if ( defined('BOX_POLECANE_Z_OPISEM_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)BOX_POLECANE_Z_OPISEM_ILOSC_PRODUKTOW;
 } else {
   $LimitZapytania = 1;
}

if ( defined('BOX_POLECANE_Z_OPISEM_ROZMIAR_IMG') ) {
   $RozmiarImg = (int)BOX_POLECANE_Z_OPISEM_ROZMIAR_IMG;
 } else {
   $RozmiarImg = 70;
}

$WybraneProdukty = Produkty::ProduktyModulowe($LimitZapytania, 'polecane');

if (count($WybraneProdukty) > 0) { 
    //
    for ( $i = 0, $c = count($WybraneProdukty); $i < $c; $i++ ) {
    
        echo '<div class="BoxImgTekst">';
        //
        $Produkt = new Produkt( $WybraneProdukty[$i], $RozmiarImg, $RozmiarImg );
        //
        echo $Produkt->fotoGlowne['zdjecie_bez_css'] . '<h3>' . $Produkt->info['link'] . '</h3>' . $Produkt->info['cena'] . '<br />' . $Produkt->info['opis_krotki'];
        //
        unset($Produkt);
        //
        echo '</div>';
        
    }
    
    echo '<div class="Wszystkie"><a href="polecane.html">{__TLUMACZ:ZOBACZ_WSZYSTKIE}</a></div>';
    //
}

unset($LimitZapytania, $RozmiarImg, $WybraneProdukty);
?>