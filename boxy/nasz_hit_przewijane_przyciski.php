<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_NASZ_HIT_PRZEWIJANE_PRZYCISKI_ILOSC_PRODUKTOW;Ilość wyświetlanych w boxie hitów;4;1,2,3,4,5,6}}
//

// zmienne bez definicji
$LimitZapytania = 4;

if ( defined('BOX_NASZ_HIT_PRZEWIJANE_PRZYCISKI_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)BOX_NASZ_HIT_PRZEWIJANE_PRZYCISKI_ILOSC_PRODUKTOW;
}

$WybraneProdukty = Produkty::ProduktyModulowe($LimitZapytania, 'hity');

if (count($WybraneProdukty) > 1) { 
    //
    echo '<div id="BoxHityPrzewijanePrzyciski" class="AnimSzer">';
    //
    $Licznik = 1;

    for ( $i = 0, $c = count($WybraneProdukty); $i < $c; $i++ ) {
        //
        echo '<div class="BoxAnimacjaScroll" id="hop'.$Licznik.'">';
        //
        $Produkt = new Produkt( $WybraneProdukty[$i] );
        echo $Produkt->fotoGlowne['zdjecie_link_ikony'];
        //
        echo '<h3>' . $Produkt->info['link'] . $Produkt->info['cena'] . '</h3>';
        //
        unset($Produkt);
        //
        echo '</div>';
        //
        $Licznik++;
    }
    
    unset($Licznik);
    //
    echo '</div>';
    echo '<div id="HityPrzewijanePrzyciski" class="BoxPrzyciski">';
    //
    // generuje przyciski
    for ($f = 1, $g = count($WybraneProdukty); $f <= $g; $f++) {
      echo '<b id="h_op'.$f.'"' . (($f == 1) ? ' class="On"' : '') . '>' . $f . '</b>';
    }
    //
    echo '</div>';
    echo '<div class="WszystkieKreska"><a href="hity.html">{__TLUMACZ:ZOBACZ_WSZYSTKIE}</a></div>';    
    //
    echo Wyglad::PrzegladarkaJavaScript( "$('#BoxHityPrzewijanePrzyciski').BoxPrzyciski( { modul: 'BoxHityPrzewijanePrzyciski', przyciski: 'HityPrzewijanePrzyciski', id: 'h', html: 'div' } );" );
    //
}

unset($WybraneProdukty, $LimitZapytania);
?>