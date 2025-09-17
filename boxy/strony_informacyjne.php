<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_STRONY_INFORMACYJNE_GRUPA;Z jakiej grupy mają być wyświetlone strony;STRONY_BOX_INFORMACJE;BoxyModuly::ListaGrupStronInfo()}}
// {{BOX_STRONY_INFORMACYJNE_CZY_POKAZYWAC_TRESC;Czy pokazywać opis skrócony strony;tak;tak,nie}}
//

if ( defined('BOX_STRONY_INFORMACYJNE_GRUPA') ) {
   $NazwaGrupy = BOX_STRONY_INFORMACYJNE_GRUPA;
 } else {
   $NazwaGrupy = 'STRONY_BOX_INFORMACJE';
}
if ( defined('BOX_STRONY_INFORMACYJNE_CZY_POKAZYWAC_TRESC') ) {
   $PokazywacTekst = BOX_STRONY_INFORMACYJNE_CZY_POKAZYWAC_TRESC;
 } else {
   $PokazywacTekst = 'tak';
}

$TablicaStron = StronyInformacyjne::TablicaStronInfoGrupa( $NazwaGrupy );

if (count($TablicaStron) > 0) {
    //
    echo '<ul class="Lista BezLinii">';
    //
    foreach ( $TablicaStron as $Strona ) {
        //
        echo '<li><h4>' . $Strona['link'] . '</h4>';
        
        if ( $PokazywacTekst == 'tak' && !empty($Strona['opis_krotki']) ) {
            echo '<div class="OpisText">' . $Strona['opis_krotki'] . '</div>';                
        }        
        
        echo '</li>';
        //
    }
    //
    echo '</ul>';
    //
}

unset($NazwaGrupy, $PokazywacTekst, $TablicaStron);
?>