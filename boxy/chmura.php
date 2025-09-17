<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_CHMURA_TAGOW_ILOSC_PRODUKTOW;Ilość wyświetlanych w boxie pozycji;10;5,10,15,20,25,30,40,50}}
//

$LimitZapytania = 4;
if ( defined('BOX_CHMURA_TAGOW_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = BOX_CHMURA_TAGOW_ILOSC_PRODUKTOW;
}
//

echo '<div id="tagCloud">' . TagiChmura::TagiGeneruj($LimitZapytania) . '</div>';
?>