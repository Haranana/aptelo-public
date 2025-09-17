<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_BANNERY_GRUPA_BANNEROW;Grupa wyświetlanych bannerów;MALE;BoxyModuly::ListaGrupBannerow()}}
// {{BOX_BANNERY_ILOSC;Czy wyświetlać tylko jeden banner czy wszystkie z grupy;jeden;jeden,wszystkie}}
//

if ( defined('BOX_BANNERY_GRUPA_BANNEROW') ) {
   $grupaBannerow = BOX_BANNERY_GRUPA_BANNEROW;
 } else {
   $grupaBannerow = 'MALE';
}

if ( defined('BOX_BANNERY_ILOSC') ) {
   $LimitZapytania = BOX_BANNERY_ILOSC;
 } else {
   $LimitZapytania = 'jeden';
}
if ( $LimitZapytania == 'jeden' ) { $LimitZapytania = 1; } else { $LimitZapytania = 999; }


if ( isset($GLOBALS['bannery']->info[$grupaBannerow]) ) {

    $Tablica = $GLOBALS['bannery']->info[$grupaBannerow];

    if ( count($Tablica) > 0 ) {

      $wybrane_bannery = Funkcje::wylosujElementyTablicyJakoTablica($Tablica,$LimitZapytania);
      
      echo '<ul class="Reklamy">';

      foreach ($wybrane_bannery as $banner ) {

            echo '<li>';

            echo $GLOBALS['bannery']->bannerWyswietlStatyczny($banner);

            echo '</li>';
      }
      
      echo '</ul>';
      
      unset($wybrane_bannery);

    }    
    
    unset($Tablica);

}

unset($grupaBannerow, $LimitZapytania);

?>