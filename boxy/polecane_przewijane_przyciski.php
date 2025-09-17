<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_POLECANE_PRZEWIJANE_PRZYCISKI_ILOSC_PRODUKTOW;Ilość wyświetlanych w boxie polecanych;4;2,3,4,5,6}}
//

// zmienne bez definicji
$LimitZapytania = 4;

if ( defined('BOX_POLECANE_PRZEWIJANE_PRZYCISKI_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)BOX_POLECANE_PRZEWIJANE_PRZYCISKI_ILOSC_PRODUKTOW;
}

$WybraneProdukty = Produkty::ProduktyModulowe($LimitZapytania, 'polecane');

if (count($WybraneProdukty) > 1) { 
    //
    echo '<div id="BoxPolecanePrzewijanePrzyciski" class="AnimSzer">';
    //
    $Licznik = 1;

    for ( $i = 0, $c = count($WybraneProdukty); $i < $c; $i++ ) {
        //
        echo '<div class="BoxAnimacjaScroll" id="fop'.$Licznik.'">';
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
    echo '<div id="PolecanePrzewijanePrzyciski" class="BoxPrzyciski">';
    //
    // generuje przyciski
    for ($f = 1, $g = count($WybraneProdukty); $f <= $g; $f++) {
      echo '<b id="f_op'.$f.'"' . (($f == 1) ? ' class="On"' : '') . '>' . $f . '</b>';
    }
    //
    echo '</div>';
    echo '<div class="WszystkieKreska"><a href="polecane.html">{__TLUMACZ:ZOBACZ_WSZYSTKIE}</a></div>';    
    //
    echo Wyglad::PrzegladarkaJavaScript( "$('#BoxPolecanePrzewijanePrzyciski').BoxPrzyciski( { modul: 'BoxPolecanePrzewijanePrzyciski', przyciski: 'PolecanePrzewijanePrzyciski', id: 'f', html: 'div' } );" );
    //       
}

unset($WybraneProdukty, $LimitZapytania);
?>