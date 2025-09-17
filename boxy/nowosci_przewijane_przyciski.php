<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_NOWOSCI_PRZEWIJANE_PRZYCISKI_ILOSC_PRODUKTOW;Ilość wyświetlanych w boxie nowości;4;2,3,4,5,6}}
//

// zmienne bez definicji
$LimitZapytania = 4;

if ( defined('BOX_NOWOSCI_PRZEWIJANE_PRZYCISKI_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)BOX_NOWOSCI_PRZEWIJANE_PRZYCISKI_ILOSC_PRODUKTOW;
}

$WybraneProdukty = Produkty::ProduktyModulowe($LimitZapytania, 'nowosci');

if (count($WybraneProdukty) > 1) {
    //
    echo '<div id="BoxNowosciPrzewijanePrzyciski" class="AnimSzer">';
    //
    $Licznik = 1;

    for ( $i = 0, $c = count($WybraneProdukty); $i < $c; $i++ ) {
        //
        echo '<div class="BoxAnimacjaScroll" id="nop'.$Licznik.'">';
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
    //
    echo '</div>';
    echo '<div id="NowosciPrzewijanePrzyciski" class="BoxPrzyciski">';
    //
    // generuje przyciski
    for ($f = 1, $g = count($WybraneProdukty); $f <= $g; $f++) {
      echo '<b id="n_op'.$f.'"' . (($f == 1) ? ' class="On"' : '') . '>' . $f . '</b>';
    }
    //
    echo '</div>';
    echo '<div class="WszystkieKreska"><a href="nowosci.html">{__TLUMACZ:ZOBACZ_WSZYSTKIE}</a></div>';    
    //
    echo Wyglad::PrzegladarkaJavaScript( "$('#BoxNowosciPrzewijanePrzyciski').BoxPrzyciski( { modul: 'BoxNowosciPrzewijanePrzyciski', przyciski: 'NowosciPrzewijanePrzyciski', id: 'n', html: 'div' } );" );
    //
}

unset($WybraneProdukty, $Licznik, $LimitZapytania);
?>