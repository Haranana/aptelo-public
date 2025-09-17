<?php
// /* dodatkowe ustawienia konfiguracyjne */ 
//
// {{MODUL_KATEGORIE_OKNA_ILOSC_KOLUMN;W ilu kolumnach mają być wyświetlane kategorie;1;1,2,3,4,5}}
// {{MODUL_KATEGORII_OKNA_ZDJECIE;Rozmiar obrazka kategorii (szerokość w pikselach);100;50,80,100,120,150,170,180,190,200,220}}
//

// zmienne bez definicji
$ZdjecieRozmiar = 100;
$IloscKolumn = 2;

if ( defined('MODUL_KATEGORIE_OKNA_ILOSC_KOLUMN') ) {
   $IloscKolumn = (int)MODUL_KATEGORIE_OKNA_ILOSC_KOLUMN;
}
if ( defined('MODUL_KATEGORII_OKNA_ZDJECIE') ) {
   $ZdjecieRozmiar = MODUL_KATEGORII_OKNA_ZDJECIE;
}

$IloscParentZero = count(Kategorie::TablicaKategorieParent('0'));

if ($IloscParentZero > 0) { 

    echo '<div class="OknaRwd Kol-' . $IloscKolumn . '">';

    foreach ($GLOBALS['tablicaKategorii'] as $IdKat => $TablicaWartosci) {

        if ( isset($TablicaWartosci['Parent']) && $TablicaWartosci['Parent'] == 0 && isset($TablicaWartosci['Widocznosc']) && $TablicaWartosci['Widocznosc'] == '1') {
            //
            echo '<div class="ProduktProsty OknoRwd">';

                echo '<div class="Foto">';
                echo '<a href="' . Seo::link_SEO($TablicaWartosci['NazwaSeo'], $TablicaWartosci['IdKat'], 'kategoria') . '">' . Funkcje::pokazObrazek($TablicaWartosci['Foto'], $TablicaWartosci['Nazwa'], $ZdjecieRozmiar, $ZdjecieRozmiar, array(), '', 'maly', true, false, false) . '</a>';
                echo '</div>';
                //
                
                echo '<h3><a href="' . Seo::link_SEO($TablicaWartosci['NazwaSeo'], $TablicaWartosci['IdKat'], 'kategoria') . '">' . $TablicaWartosci['Nazwa'] . '</a></h3>';

            echo '</div>';

        }
        
    }
    
    echo '</div>';
    //
    echo '<div class="cl"></div>';

}

unset($IloscPodkategorii, $IloscParentZero, $ZdjecieRozmiar, $IloscKolumn);
?>