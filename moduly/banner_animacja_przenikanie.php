<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_BANNER_ANIMACJA_PRZENIKANIE_ILOSC_GRAFIK;Ilość wyświetlanych grafik w animacji;5;1,2,3,4,5,6,7,8,9,10}}
// {{MODUL_BANNER_ANIMACJA_PRZENIKANIE_GRUPA;Grupa wyświetlanych bannerów;ANIMACJA_SRODKOWA;BoxyModuly::ListaGrupBannerow()}}
// {{MODUL_BANNER_ANIMACJA_PRZENIKANIE_NAWIGACJA;Czy pokazywać elementy nawigacyjne;tak;tak,nie}}
// {{MODUL_BANNER_ANIMACJA_PRZENIKANIE_NAWIGACJA_STRZALKI;Czy pokazywać strzałki nawigacyjne;tak;tak,nie}}
// {{MODUL_BANNER_ANIMACJA_PRZENIKANIE_NAWIGACJA_KROPKI;Czy pokazywać kropki nawigacyjne;tak;tak,nie}}
// {{MODUL_BANNER_ANIMACJA_PRZENIKANIE_CZAS;Czas w sekundach pomiedzy kolejnymi grafikami;5;3,4,5,6,7,8,9,10,15,20}}
// {{MODUL_BANNER_ANIMACJA_PRZENIKANIE_SORTOWANIE;Sposób sortowania bannerów;losowo;losowo,sortowanie}}
//

// zmienne bez definicji
$LimitZapytania = 5;
$GrupaBannerow = 'ANIMACJA_SRODKOWA';
$Nawigacja = 'tak';
$NawigacjaStrzalki = 'tak';
$NawigacjaKropki = 'tak';
$CzasAnimacji = 5;
$SortowanieBannerow = 'losowo';

if ( defined('MODUL_BANNER_ANIMACJA_PRZENIKANIE_ILOSC_GRAFIK') ) {
   $LimitZapytania = (int)MODUL_BANNER_ANIMACJA_PRZENIKANIE_ILOSC_GRAFIK;
}
if ( defined('MODUL_BANNER_ANIMACJA_PRZENIKANIE_GRUPA') ) {
   $GrupaBannerow = MODUL_BANNER_ANIMACJA_PRZENIKANIE_GRUPA;
}
if ( defined('MODUL_BANNER_ANIMACJA_PRZENIKANIE_NAWIGACJA') ) {
   $Nawigacja = MODUL_BANNER_ANIMACJA_PRZENIKANIE_NAWIGACJA;
}
if ( defined('MODUL_BANNER_ANIMACJA_PRZENIKANIE_NAWIGACJA_STRZALKI') ) {
   $NawigacjaStrzalki = MODUL_BANNER_ANIMACJA_PRZENIKANIE_NAWIGACJA_STRZALKI;
}
if ( defined('MODUL_BANNER_ANIMACJA_PRZENIKANIE_NAWIGACJA_KROPKI') ) {
   $NawigacjaKropki = MODUL_BANNER_ANIMACJA_PRZENIKANIE_NAWIGACJA_KROPKI;
}
if ( defined('MODUL_BANNER_ANIMACJA_PRZENIKANIE_CZAS') ) {
   $CzasAnimacji = MODUL_BANNER_ANIMACJA_PRZENIKANIE_CZAS;
}
if ( defined('MODUL_BANNER_ANIMACJA_PRZENIKANIE_SORTOWANIE') ) {
   $SortowanieBannerow = MODUL_BANNER_ANIMACJA_PRZENIKANIE_SORTOWANIE;
}

if ( isset($GLOBALS['bannery']->info[$GrupaBannerow]) ) {

    echo '<script type="text/javascript" src="programy/sliderJmk/slider_przenikanie.js"></script>';

    $Tablica = $GLOBALS['bannery']->info[$GrupaBannerow];
    //
    if ( count($Tablica) > 0 ) {
        //
        if ( $SortowanieBannerow == 'losowo' ) {
             //  
             $WybraneBannery = Funkcje::wylosujElementyTablicyJakoTablica($Tablica,$LimitZapytania);
             //
        } else {
             //        
             $WybraneBannery = array_slice($Tablica, 0, $LimitZapytania);
             //
        }
        
        // jezeli jest tylko jeden banner to wylaczy animacje i przyciski
        if ( count($Tablica) == 1 ) {
             //
             $Nawigacja = 'nie';
             $Animowac = 'nie';
             //
        }              
        
        $Nawigacja_bannerow = '';
        //
        echo '<div id="AnimacjaPrzenikanieKontener">';
            
            echo '<div id="BanneryAnimacjaPrzenikanieLewaStrzalka"></div>';
            echo '<div id="BanneryAnimacjaPrzenikaniePrawaStrzalka"></div>';
            
            $LicznikBannerow = 0;
            
            //
            echo '<ul id="BanneryAnimacjaPrzenikanie">';
            //
            foreach ( $WybraneBannery as $banner ) {

                if ( $banner['obrazek_bannera'] != '' ) {
                     //
                     echo '<li id="fadnr' . $LicznikBannerow . '">';
                     //
                     $GLOBALS['bannery']->bannerWyswietlAnimowany($banner);
                     $Nawigacja_bannerow .= '<span' . (( $LicznikBannerow == 0 ) ? ' class="On"' : '') . '>' . $LicznikBannerow . '</span>';
                     //
                     $LicznikBannerow++;
                     //
                     echo '</li>';
                     //
                }

            }
            //
            echo '</ul>';
            //
            unset($LicznikBannerow);
            //
        //
        echo '</div>';
        //
        echo '<div id="BanneryAnimacjaPrzenikaniePrzyciski">' . $Nawigacja_bannerow . '</div>';
        //
        unset($Nawigacja_bannerow, $WybraneBannery);
        //
        echo '<div id="BanneryAnimacjaPrzenikanieNumer">0</div>';
        //
    }
    
    unset($Tablica);
    
    echo Wyglad::PrzegladarkaJavaScript( "$.BanneryPrzenikanie({ czas:" . $CzasAnimacji . ", nawigacja:'" . $Nawigacja . "', strzalki:'" . $NawigacjaStrzalki . "', kropki:'" . $NawigacjaKropki . "'})" );

}

unset($LimitZapytania, $GrupaBannerow, $Nawigacja, $NawigacjaStrzalki, $NawigacjaKropki, $CzasAnimacji);

?>