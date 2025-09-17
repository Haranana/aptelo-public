<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_BANNER_ANIMACJA_MIESZANY_ILOSC_GRAFIK;Ilość wyświetlanych grafik w animacji;5;1,2,3,4,5,6,7,8,9,10}}
// {{MODUL_BANNER_ANIMACJA_MIESZANY_GRUPA;Grupa wyświetlanych bannerów;ANIMACJA_SRODKOWA;BoxyModuly::ListaGrupBannerow()}}
// {{MODUL_BANNER_ANIMACJA_MIESZANY_EFEKT;Efekt animacji;brak;brak,przewijanie,przenikanie,pomniejszanie,spadanie,pionowe prostokaty,pionowe prostokaty przemiennie,poziome prostokaty,poziome prostokaty przemiennie,kwadraty pomniejszanie,kwadraty zanikanie}}
// {{MODUL_BANNER_ANIMACJA_MIESZANY_NAWIGACJA;Czy pokazywać elementy nawigacyjne;tak;tak,nie}}
// {{MODUL_BANNER_ANIMACJA_MIESZANY_ANIMACJA;Czy bannery mają się same animować;nie;tak,nie}}
// {{MODUL_BANNER_ANIMACJA_MIESZANY_CZAS;Czas w sekundach pomiedzy kolejnymi grafikami;5;3,4,5,6,7,8,9,10,15,20}}
// {{MODUL_BANNER_ANIMACJA_MIESZANY_SORTOWANIE;Sposób sortowania bannerów;losowo;losowo,sortowanie}}
//

// zmienne bez definicji
$LimitZapytania = 5;
$GrupaBannerow = 'ANIMACJA_SRODKOWA';
$Nawigacja = 'tak';
$EfektAnimacji = 'brak';
$CzasAnimacji = 5;
$Animowac = 'nie';
$SortowanieBannerow = 'losowo';

if ( defined('MODUL_BANNER_ANIMACJA_MIESZANY_ILOSC_GRAFIK') ) {
   $LimitZapytania = (int)MODUL_BANNER_ANIMACJA_MIESZANY_ILOSC_GRAFIK;
}
if ( defined('MODUL_BANNER_ANIMACJA_MIESZANY_GRUPA') ) {
   $GrupaBannerow = MODUL_BANNER_ANIMACJA_MIESZANY_GRUPA;
}
if ( defined('MODUL_BANNER_ANIMACJA_MIESZANY_EFEKT') ) {
   $EfektAnimacji = str_replace(' ', '_', (string)MODUL_BANNER_ANIMACJA_MIESZANY_EFEKT);
}
if ( defined('MODUL_BANNER_ANIMACJA_MIESZANY_NAWIGACJA') ) {
   $Nawigacja = MODUL_BANNER_ANIMACJA_MIESZANY_NAWIGACJA;
}
if ( defined('MODUL_BANNER_ANIMACJA_MIESZANY_CZAS') ) {
   $CzasAnimacji = MODUL_BANNER_ANIMACJA_MIESZANY_CZAS * 1000;
}
if ( defined('MODUL_BANNER_ANIMACJA_MIESZANY_ANIMACJA') ) {
   $Animowac = MODUL_BANNER_ANIMACJA_MIESZANY_ANIMACJA;
}
if ( defined('MODUL_BANNER_ANIMACJA_MIESZANY_SORTOWANIE') ) {
   $SortowanieBannerow = MODUL_BANNER_ANIMACJA_MIESZANY_SORTOWANIE;
}

if ( isset($GLOBALS['bannery']->info[$GrupaBannerow]) ) {

    echo '<script type="text/javascript" src="programy/sliderJmk/slider_mieszany.js"></script>';

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
        //
        echo '<div id="AnimacjaMieszanaKontener">';

            //
            echo '<ul>';
            //
            foreach ( $WybraneBannery as $banner ) {

                if ( $banner['obrazek_bannera'] != '' ) {
                     //
                     echo '<li class="AnimacjaMieszana">';
                     //
                     $GLOBALS['bannery']->bannerWyswietlAnimowany($banner);
                     //
                     echo '</li>';
                     //
                }

            }
            //
            echo '</ul>';            
            //
 
        //
        echo '</div>';        
        //
        // jezeli jest tylko jeden banner to wylaczy animacje i przyciski
        if ( count($Tablica) == 1 ) {
             //
             $Nawigacja = 'nie';
             $Animowac = 'nie';
             //
        }
        
        echo '<div id="BanneryAnimacjaMieszanaPrzyciski" ' . (($Nawigacja == 'nie') ? 'style="display:none"' : '') . '></div>';
        //
        unset($WybraneBannery);
        //        
    }
    
    unset($Tablica);
    
    echo Wyglad::PrzegladarkaJavaScript( "$.BanneryMieszane({ kontener: '#AnimacjaMieszanaKontener', podkontener: '#AnimacjaMieszanaKontener ul', id: 'amie', pozycje: '.AnimacjaMieszana', przyciski: '#BanneryAnimacjaMieszanaPrzyciski', animacja_rodzaj: '" . $EfektAnimacji . "', animacja: '" . $Animowac . "', czas: '" . $CzasAnimacji . "' } )" );

}

unset($LimitZapytania, $GrupaBannerow, $Nawigacja, $Nawigacja_strzalki, $Nawigacja_kropki, $CzasAnimacji);

?>