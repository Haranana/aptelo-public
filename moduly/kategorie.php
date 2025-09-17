<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_KATEGORII_ILOSC_KOLUMN;W ilu kolumnach mają być wyświetlane produkty;1;1,2,3,4,5}}
// {{MODUL_KATEGORII_PODKATEGORIE;Czy wyświetlać podkategorie;tak;tak,nie}}
// {{MODUL_KATEGORII_ZDJECIE;Czy wyświetlać zdjęcie kategorii;tak;tak,nie}}
// {{MODUL_KATEGORII_PODKATEGORIE_ILOSC;Ile podkategorii ma być wyświetlanych;5;3,5,8,10,15,20,30,40}}
//

// zmienne bez definicji
$Podkategorie = 'tak';
$Zdjecie = 'tak';
$IloscKolumn = 2;
$IloscPodkategorii = 10;

if ( defined('MODUL_KATEGORII_ILOSC_KOLUMN') ) {
   $IloscKolumn = (int)MODUL_KATEGORII_ILOSC_KOLUMN;
}
if ( defined('MODUL_KATEGORII_PODKATEGORIE') ) {
   $Podkategorie = MODUL_KATEGORII_PODKATEGORIE;
}
if ( defined('MODUL_KATEGORII_PODKATEGORIE') ) {
   $Zdjecie = MODUL_KATEGORII_ZDJECIE;
}
if ( defined('MODUL_KATEGORII_PODKATEGORIE_ILOSC') ) {
   $IloscPodkategorii = (int)MODUL_KATEGORII_PODKATEGORIE_ILOSC;
}

$IloscParentZero = count(Kategorie::TablicaKategorieParent('0'));

if ($IloscParentZero > 0) { 

    echo '<div class="OknaRwd Kol-' . $IloscKolumn . '">';     

    foreach ($GLOBALS['tablicaKategorii'] as $IdKat => $TablicaWartosci) {
        //
        if (isset($TablicaWartosci['Parent']) && $TablicaWartosci['Parent'] == 0 && $TablicaWartosci['Widocznosc'] == '1') {
            //
            echo '<article class="KategoriaGl OknoRwd">';

                if ( !empty($TablicaWartosci['Foto']) && $Zdjecie == 'tak' ) {
                    //
                    echo '<div class="Foto">';
                    echo Funkcje::pokazObrazek($TablicaWartosci['Foto'], $TablicaWartosci['Nazwa'], 50, 50, array(), '', 'maly', true, false, false);
                    echo '</div>';
                    //
                }
                //

                echo '<div class="Kategoria"' . (($Zdjecie == 'nie') ? ' style="margin:0px"' : '') . '>';

                echo '<h3><a href="' . Seo::link_SEO($TablicaWartosci['NazwaSeo'], $TablicaWartosci['IdKat'], 'kategoria') . '">' . $TablicaWartosci['Nazwa'] . '</a></h3>';
                        
                    if ( $Podkategorie == 'tak' ) {
                    
                        // podkategorie
                        
                        $PodkategorieTablica = Kategorie::TablicaKategorieParent($TablicaWartosci['IdKat']);
                        if (count($PodkategorieTablica) > 0) {
                                      
                            echo '<ul>';
                            $LicznikPodkategorii = 1;
                            foreach($PodkategorieTablica as $Tablica) {
                              //
                              if ($LicznikPodkategorii <= $IloscPodkategorii) {
                                  echo '<li><a href="' . Seo::link_SEO($Tablica['seo'], $IdKat . '_' . $Tablica['id'], 'kategoria') . '">' . $Tablica['text'] . '</a></li>';
                              }
                              $LicznikPodkategorii++;
                              //
                            }
                            echo '</ul>';             

                        }
                        unset($PodkategorieTablica, $LicznikPodkategorii);
                        
                    }
                        
                echo '</div>';

            echo '</article>';

        }
        
    }

    echo '</div>';
    //
    echo '<div class="cl"></div>';

}

unset($IloscPodkategorii, $IloscParentZero, $Podkategorie, $Zdjecie, $IloscKolumn);
?>