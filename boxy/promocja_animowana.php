<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_PROMOCJA_ANIMOWANA_ILOSC_PRODUKTOW;Ilość wyświetlanych w boxie promocji;4;2,3,4,5,6,7,8}}
// {{BOX_PROMOCJA_ANIMOWANA_RODZAJ_ANIMACJI;W jaki sposób mają być animowane produkty;zanikanie;zanikanie,przenikanie,od lewej do prawej,od prawej do lewej,z gory na dol,z dolu do gory}}
// {{BOX_PROMOCJA_ANIMOWANA_CZAS_CO_ILE;Co ile sekund ma się zmieniać produkt;5;3,4,5,6,7,8,9,10,12,15}}
//

// zmienne bez definicji
$LimitZapytania = 4;
$RodzajAnimacji = 'zanikanie';
$CzasAnimacji = 5000;

if ( defined('BOX_PROMOCJA_ANIMOWANA_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)BOX_PROMOCJA_ANIMOWANA_ILOSC_PRODUKTOW;
}
if ( defined('BOX_PROMOCJA_ANIMOWANA_RODZAJ_ANIMACJI') ) {
    $RodzajAnimacji = BOX_PROMOCJA_ANIMOWANA_RODZAJ_ANIMACJI;
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
if ( defined('BOX_PROMOCJA_ANIMOWANA_CZAS_CO_ILE') ) {
   $CzasAnimacji = (int)BOX_PROMOCJA_ANIMOWANA_CZAS_CO_ILE * 1000;
}

$WybraneProdukty = Produkty::ProduktyModulowe($LimitZapytania, 'promocje');

if (count($WybraneProdukty) > 1) { 
    //
    echo '<div id="BoxPromocjeAnimowane" class="AnimSzer">';
    //
    $Licznik = 1;
    for ( $i = 0, $c = count($WybraneProdukty); $i < $c; $i++ ) {
      //
      // jezeli jest fade albo slide to musi pozycjonowanie absolute
      if ( $Animacja == 'fade' || $Animacja == 'slide') {
         echo '<div class="BoxAnimacja" id="pa'.$Licznik.'">';
      } else {
         echo '<div class="BoxAnimacjaScroll" id="pa'.$Licznik.'">';
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
    echo '<div class="WszystkieKreska"><a href="promocje.html">{__TLUMACZ:ZOBACZ_WSZYSTKIE}</a></div>';    
    //
    echo Wyglad::PrzegladarkaJavaScript( "$('#BoxPromocjeAnimowane').BoxAnimacje( { modul: 'BoxPromocjeAnimowane', id: 'pa', html: 'div', czas: " . $CzasAnimacji . ", szybkosc: 700, typ: '" . $Animacja . "' } );" );
    //
}

unset($WybraneProdukty, $LimitZapytania, $RodzajAnimacji, $Animacja, $CzasAnimacji);
?>