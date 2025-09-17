<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_OCZEKIWANE_ILOSC_PRODUKTOW;Ilość wyświetlanych w produktów;4;2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20}}
// {{MODUL_OCZEKIWANE_ILOSC_KOLUMN;W ilu kolumnach mają być wyświetlane produkty;2;1,2,3,4,5}}
//

// zmienne bez definicji
$LimitZapytania = 4;
$IloscKolumn = 2;

if ( defined('MODUL_OCZEKIWANE_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)MODUL_OCZEKIWANE_ILOSC_PRODUKTOW;
}
if ( defined('MODUL_OCZEKIWANE_ILOSC_KOLUMN') ) {
   $IloscKolumn = (int)MODUL_OCZEKIWANE_ILOSC_KOLUMN;
}

$WybraneProdukty = Produkty::ProduktyModulowe($LimitZapytania, 'oczekiwane');

if (count($WybraneProdukty) > 0) { 
      
    echo '<div class="OknaRwd Kol-' . $IloscKolumn . '">';

    for ($v = 0, $cs = count($WybraneProdukty); $v < $cs; $v++) {

        echo '<article class="ProduktProsty OknoRwd">';

            $Produkt = new Produkt( $WybraneProdukty[$v] );
            //              
            echo '<div class="Foto">'.$Produkt->fotoGlowne['zdjecie_link_ikony'].'</div>';
            //
            echo '<h3>' . $Produkt->info['link'] . '</h3>' . $Produkt->info['cena'];
            //
            echo '<p class="DataDostepnosci LiniaGorna">{__TLUMACZ:DOSTEPNY_OD_DNIA} <b>' . $Produkt->info['data_dostepnosci'] . '</b></p>';
            //
            unset($Produkt);

        echo '</article>';

    }
    
    echo '</div>';
    //
    echo '<div class="cl"></div>';

}

unset($IloscKolumn, $LimitZapytania);
?>