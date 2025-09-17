<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_OSTATNIE_AKTUALNOSCI_LISTA_ILOSC_POZYCJI;Ilość wyświetlanych w boxie artykułów;4;1,2,3,4,5,6,7,8,9,10}}
// {{BOX_OSTATNIE_AKTUALNOSCI_CZY_POKAZYWAC_TRESC;Czy pokazywać opis skrócony artykułu;tak;tak,nie}}
// {{BOX_OSTATNIE_AKTUALNOSCI_CZY_POKAZYWAC_DATE;Czy pokazywać datę dodania artykułu;tak;tak,nie}}
//

if ( defined('BOX_OSTATNIE_AKTUALNOSCI_LISTA_ILOSC_POZYCJI') ) {
   $LimitZapytania = (int)BOX_OSTATNIE_AKTUALNOSCI_LISTA_ILOSC_POZYCJI;
 } else {
   $LimitZapytania = 4;
}
if ( defined('BOX_OSTATNIE_AKTUALNOSCI_CZY_POKAZYWAC_TRESC') ) {
   $PokazywacTekst = BOX_OSTATNIE_AKTUALNOSCI_CZY_POKAZYWAC_TRESC;
 } else {
   $PokazywacTekst = 'tak';
}
if ( defined('BOX_OSTATNIE_AKTUALNOSCI_CZY_POKAZYWAC_DATE') ) {
   $PokazywacDate = BOX_OSTATNIE_AKTUALNOSCI_CZY_POKAZYWAC_DATE;
 } else {
   $PokazywacDate = 'tak';
}

$TablicaArtykulow = Aktualnosci::TablicaAktualnosciLimit( $LimitZapytania );

if (count($TablicaArtykulow) > 0) {             
               
    echo '<ul class="Lista">';
    //
    foreach ( $TablicaArtykulow as $Artykul ) {
        //
        echo '<li><h4><a href="' . $Artykul['seo'] . '">' . $Artykul['tytul'];

        if ( $PokazywacDate == 'tak' ) {
            echo '<em class="Data">' . $Artykul['data'] . '</em>';                
        }         
        
        echo '</a></h4>';
        
        if ( $PokazywacTekst == 'tak' && !empty($Artykul['opis_krotki']) ) {
            echo '<div class="OpisText">' . $Artykul['opis_krotki'] . '</div>';                
        }        
        
        echo '</li>';
        //
    }
    //
    echo '</ul>';
    //  
}

unset($PokazywacTekst, $LimitZapytania, $PokazywacDate, $TablicaArtykulow);
?>