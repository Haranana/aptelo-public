<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_BANNER_STATYCZNY_ILOSC_PRODUKTOW;Ilość wyświetlanych bannerów;1;1,2,3,4,5,6,7,8,9}}
// {{MODUL_BANNER_STATYCZNY_ILOSC_GRUPA;Grupa wyświetlanych bannerów;STATYCZNE;BoxyModuly::ListaGrupBannerow()}}
// {{MODUL_BANNER_STATYCZNY_SORTOWANIE;Sposób sortowania bannerów;losowo;losowo,sortowanie}}
//

// zmienne bez definicji
$LimitZapytania = 1;
$grupaBannerow = 'STATYCZNE';
$SortowanieBannerow = 'losowo';

if ( defined('MODUL_BANNER_STATYCZNY_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)MODUL_BANNER_STATYCZNY_ILOSC_PRODUKTOW;
}
if ( defined('MODUL_BANNER_STATYCZNY_ILOSC_GRUPA') ) {
   $grupaBannerow = MODUL_BANNER_STATYCZNY_ILOSC_GRUPA;
}
if ( defined('MODUL_BANNER_STATYCZNY_SORTOWANIE') ) {
   $SortowanieBannerow = MODUL_BANNER_STATYCZNY_SORTOWANIE;
}

if ( isset($GLOBALS['bannery']->info[$grupaBannerow]) ) {

    $Tablica = $GLOBALS['bannery']->info[$grupaBannerow];

    if ( count($Tablica) > 0 ) {

      if ( $SortowanieBannerow == 'losowo' ) {
           //  
           $wybrane_bannery = Funkcje::wylosujElementyTablicyJakoTablica($Tablica,$LimitZapytania);
           //
      } else {
           //        
           $wybrane_bannery = array_slice($Tablica, 0, $LimitZapytania);
           //
      }

      foreach ($wybrane_bannery as $banner ) {

        echo '<div class="BanneryStatyczne">';

          $GLOBALS['bannery']->bannerWyswietlStatyczny($banner);

        echo '</div>';

      }
      
      unset($wybrane_bannery);

    }
    
    unset($Tablica);
    
}

unset($LimitZapytania, $grupaBannerow);

?>