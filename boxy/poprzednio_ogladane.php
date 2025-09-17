<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_POPRZEDNIO_OGLADANE_ROZMIAR_IMG;Rozmiar zdjęcia produktu w pixelach;50;50,70,90,100,120,150,170}}
//

if ( defined('BOX_POPRZEDNIO_OGLADANE_ROZMIAR_IMG') ) {
   $RozmiarImg = (int)BOX_POPRZEDNIO_OGLADANE_ROZMIAR_IMG;
 } else {
   $RozmiarImg = 50;
}

if (count($_SESSION['produktyPoprzednioOgladane']) > 0) {
    //
    $licz = 1;
    //
    echo '<ul class="BoxImgMaly BezLinii">';
    //
    $OstatnioOgladane = array_reverse($_SESSION['produktyPoprzednioOgladane']);
    //
    foreach ($OstatnioOgladane AS $Id) {
        //
        if ( $licz < 11 ) {
            //
            $Produkt = new Produkt( $Id, $RozmiarImg, $RozmiarImg );

            if ( isset($Produkt->info['id']) && $Produkt->info['id'] != '' ) {

                //
                echo '<li>
                          <div class="Img" style="width:' . $RozmiarImg . 'px">' . $Produkt->fotoGlowne['zdjecie_link'] . '</div>
                          <h3 class="PrdDane" style="width:calc(100% - ' . ($RozmiarImg + 20) . 'px)">' . $Produkt->info['link'] . $Produkt->info['cena'] . '</h3>
                      </li>';

                unset($Produkt);

            }
            //
            $licz++;
            //
        }
    }
    //
    echo '</ul>';
    //
    unset($licz, $OstatnioOgladane);
    //
}

unset($RozmiarImg);
?>