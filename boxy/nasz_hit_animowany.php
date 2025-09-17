<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_NASZ_HIT_ANIMOWANY_ILOSC_PRODUKTOW;Ilość wyświetlanych w boxie hitów;4;2,3,4,5,6,7,8}}
// {{BOX_NASZ_HIT_RODZAJ_ANIMACJI;W jaki sposób mają być animowane produkty;zanikanie;zanikanie,przenikanie,od lewej do prawej,od prawej do lewej,z gory na dol,z dolu do gory}}
// {{BOX_NASZ_HIT_CZAS_CO_ILE;Co ile sekund ma się zmieniać produkt;6;3,4,5,6,7,8,9,10,12,15}}
//

// zmienne bez definicji
$LimitZapytania = 4;
$RodzajAnimacji = 'zanikanie';
$CzasAnimacji = 5000;

if ( defined('BOX_NASZ_HIT_ANIMOWANY_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)BOX_NASZ_HIT_ANIMOWANY_ILOSC_PRODUKTOW;
}
if ( defined('BOX_NASZ_HIT_RODZAJ_ANIMACJI') ) {
    $RodzajAnimacji = BOX_NASZ_HIT_RODZAJ_ANIMACJI;
}
switch ($RodzajAnimacji) {
    case "zanikanie":
        $Animacja = 'fade';
        break;
    case "przenikanie":
        $Animacja = 'slide';
        break;        
    case "od lewej do prawej":
        $Animacja = 'scrollright';
        break;        
    case "od prawej do lewej":
        $Animacja = 'scrollleft';
        break;        
    case "z gory na dol":
        $Animacja = 'scrolldown';
        break;        
    case "z dolu do gory":
        $Animacja = 'scrollup';
        break;        
}
if ( defined('BOX_NASZ_HIT_CZAS_CO_ILE') ) {
   $CzasAnimacji = (int)BOX_NASZ_HIT_CZAS_CO_ILE * 1000;
}

$WybraneProdukty = Produkty::ProduktyModulowe($LimitZapytania, 'hity');

if (count($WybraneProdukty) > 1) { 
    //
    echo '<div id="BoxHityAnimowane" class="AnimSzer">';
    //
    $Licznik = 1;

    for ( $i = 0, $c = count($WybraneProdukty); $i < $c; $i++ ) {
        //
        // jezeli jest fade albo slide to musi pozycjonowanie absolute
        if ( $Animacja == 'fade' || $Animacja == 'slide') {
            echo '<div class="BoxAnimacja" id="nh'.$Licznik.'">';
        } else {
            echo '<div class="BoxAnimacjaScroll" id="nh'.$Licznik.'">';
        }
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
    
    unset($Licznik);
    //
    echo '</div>';
    echo '<div class="WszystkieKreska"><a href="hity.html">{__TLUMACZ:ZOBACZ_WSZYSTKIE}</a></div>';    
    //
    echo Wyglad::PrzegladarkaJavaScript( "$('#BoxHityAnimowane').BoxAnimacje( { modul: 'BoxHityAnimowane', id: 'nh', html: 'div', czas: " . $CzasAnimacji . ", szybkosc: 700, typ: '" . $Animacja . "' } );" );
    //
}

unset($WybraneProdukty, $LimitZapytania, $RodzajAnimacji, $Animacja, $CzasAnimacji);
?>