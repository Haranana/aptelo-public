<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_NOWOSCI_PRZEWIJANE_ILOSC_PRODUKTOW;Ilość wyświetlanych w boxie nowości;4;2,3,4,5,6,7,8,9,10}}
//

// zmienne bez definicji
$LimitZapytania = 4;

if ( defined('BOX_NOWOSCI_PRZEWIJANE_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)BOX_NOWOSCI_PRZEWIJANE_ILOSC_PRODUKTOW;
}

$WybraneProdukty = Produkty::ProduktyModulowe($LimitZapytania, 'nowosci');

if (count($WybraneProdukty) > 1) {
    //
    echo '<div id="BoxNowosciPrzewijane" class="AnimSzer">';
    echo '<span class="AktLicz">1</span>';
    echo '<span class="strzalkaLewa"></span><span class="strzalkaPrawa"></span>';
    //
    $Licznik = 1;
    for ( $i = 0, $c = count($WybraneProdukty); $i < $c; $i++ ) {
        //
        echo '<div class="BoxAnimacjaScroll" id="np'.$Licznik.'">';
        //
        $Produkt = new Produkt( $WybraneProdukty[$i] );
        //
        echo $Produkt->fotoGlowne['zdjecie_link_ikony'];
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
    echo '<div class="WszystkieKreska"><a href="nowosci.html">{__TLUMACZ:ZOBACZ_WSZYSTKIE}</a></div>';    
    //
    echo Wyglad::PrzegladarkaJavaScript( "$('#BoxNowosciPrzewijane').BoxPrzewijanie( { modul: 'BoxNowosciPrzewijane', id: 'np', html: 'div' } );" );
    //
}

unset($WybraneProdukty, $Licznik, $LimitZapytania);
?>