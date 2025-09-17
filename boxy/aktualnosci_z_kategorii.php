<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_AKTUALNOSCI_Z_KATEGORII_KATEGORIA;Z jakiej kategorii mają być wyświetlone artykuły;0;BoxyModuly::ListaKategoriiAktualnosci()}}
// {{BOX_AKTUALNOSCI_Z_KATEGORII_CZY_POKAZYWAC_TRESC;Czy pokazywać opis skrócony aktualności;tak;tak,nie}}
// {{BOX_AKTUALNOSCI_Z_KATEGORII_LISTA_ILOSC_POZYCJI;Ilość wyświetlanych w boxie artykułów;4;1,2,3,4,5,6,7,8,9,10}}
// {{BOX_AKTUALNOSCI_Z_KATEGORII_CZY_POKAZYWAC_DATE;Czy pokazywać datę dodania artykułu;tak;tak,nie}}
//

if ( defined('BOX_AKTUALNOSCI_Z_KATEGORII_KATEGORIA') ) {
   $IdKategorii = BOX_AKTUALNOSCI_Z_KATEGORII_KATEGORIA;
 } else {
   $IdKategorii = '0';
}
if ( defined('BOX_AKTUALNOSCI_Z_KATEGORII_CZY_POKAZYWAC_TRESC') ) {
   $PokazywacTekst = BOX_AKTUALNOSCI_Z_KATEGORII_CZY_POKAZYWAC_TRESC;
 } else {
   $PokazywacTekst = 'tak';
}
if ( defined('BOX_AKTUALNOSCI_Z_KATEGORII_CZY_POKAZYWAC_DATE') ) {
   $PokazywacDate = BOX_AKTUALNOSCI_Z_KATEGORII_CZY_POKAZYWAC_DATE;
 } else {
   $PokazywacDate = 'tak';
}
if ( defined('BOX_AKTUALNOSCI_Z_KATEGORII_LISTA_ILOSC_POZYCJI') ) {
   $LimitZapytania = (int)BOX_AKTUALNOSCI_Z_KATEGORII_LISTA_ILOSC_POZYCJI;
 } else {
   $LimitZapytania = 4;
}

$TablicaArtykulow = Aktualnosci::TablicaAktualnosciKategoria( $IdKategorii, $LimitZapytania );

if (count($TablicaArtykulow) > 0) {
    //
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
    $TablicaKategoriiArtykulow = Aktualnosci::TablicaKategorieAktualnosci();
    //
    echo '<div class="Wszystkie"><a href="' . $TablicaKategoriiArtykulow[$IdKategorii]['seo'] . '">{__TLUMACZ:ZOBACZ_WSZYSTKIE}</a></div>';    
    //
    unset($TablicaKategoriiArtykulow);
    //
}
    
unset($PokazywacDate, $PokazywacTekst, $IdKategorii, $LimitZapytania, $TablicaArtykulow);
?>