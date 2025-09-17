<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_BANNERY_JEDEN_GRUPA_BANNEROW;Grupa wyświetlanych bannerów;MALE;BoxyModuly::ListaGrupBannerow()}}
//

if ( defined('BOX_BANNERY_JEDEN_GRUPA_BANNEROW') ) {
   $grupaBannerow = BOX_BANNERY_JEDEN_GRUPA_BANNEROW;
 } else {
   $grupaBannerow = 'MALE';
}

if ( isset($GLOBALS['bannery']->info[$grupaBannerow]) ) {

    $Tablica = $GLOBALS['bannery']->info[$grupaBannerow];

    if ( count($Tablica) > 0 ) {

      $wybrane_bannery = Funkcje::wylosujElementyTablicyJakoTablica($Tablica,1);
      
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

unset($grupaBannerow);

?>