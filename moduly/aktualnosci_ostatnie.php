<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_AKTUALNOSCI_OSTATNIE_ILOSC_ARTYKULOW;Ilość wyświetlanych artykułów;4;1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20}}
// {{MODUL_AKTUALNOSCI_OSTATNIE_ILOSC_KOLUMN;W ilu kolumnach mają być wyświetlane artykuły;2;1,2,3,4,5}}
// {{MODUL_AKTUALNOSCI_OSTATNIE_ILOSC_ODSLON_LISTING;Czy w pokazywać ilość odsłon artykułu;tak;tak,nie}}
// {{MODUL_AKTUALNOSCI_OSTATNIE_AUTOR_LISTING;Czy w pokazywać nazwę autora artykułu;nie;tak,nie}}
// {{MODUL_AKTUALNOSCI_OSTATNIE_DATA;Czy w pokazywać datę dodania artykułu;tak;tak,nie}}
//

// zmienne bez definicji
$LimitZapytania = 4;
$IloscKolumn = 2;
$IloscOdslon = 'tak';
$WyswietlDate = 'tak';
$WyswietlAutor = 'tak';

if ( defined('MODUL_AKTUALNOSCI_OSTATNIE_ILOSC_ARTYKULOW') ) {
   $LimitZapytania = (int)MODUL_AKTUALNOSCI_OSTATNIE_ILOSC_ARTYKULOW;
}
if ( defined('MODUL_AKTUALNOSCI_OSTATNIE_ILOSC_KOLUMN') ) {
   $IloscKolumn = (int)MODUL_AKTUALNOSCI_OSTATNIE_ILOSC_KOLUMN;
}
if ( defined('MODUL_AKTUALNOSCI_OSTATNIE_ILOSC_ODSLON_LISTING') ) {
   $IloscOdslon = MODUL_AKTUALNOSCI_OSTATNIE_ILOSC_ODSLON_LISTING;
}
if ( defined('MODUL_AKTUALNOSCI_OSTATNIE_DATA') ) {
   $WyswietlDate = MODUL_AKTUALNOSCI_OSTATNIE_DATA;
}
if ( defined('MODUL_AKTUALNOSCI_OSTATNIE_AUTOR_LISTING') ) {
   $WyswietlAutor = MODUL_AKTUALNOSCI_OSTATNIE_AUTOR_LISTING;
}

$TablicaArtykulow = Aktualnosci::TablicaAktualnosciLimit( $LimitZapytania );

$IloscArtykulow = count($TablicaArtykulow);

if ($IloscArtykulow > 0) {

    echo '<div class="OknaRwd Kol-' . $IloscKolumn . '">';

    foreach ( $TablicaArtykulow as $Artykul ) {
        //
        echo '<article class="AktProsta OknoRwd">';

            echo '<h2>' . $Artykul['link'] . '</h2>';
            //
            echo '<span class="DaneAktualnosci">';
            
                // czy pokazywac nazwe autora
                if ( $WyswietlAutor == 'tak' && $Artykul['autor'] != '' ) {
                    echo '<em class="AutorArtykulu">{__TLUMACZ:AUTOR} ' . $Artykul['autor'] . '</em>';
                }                 
            
                // czy pokazywac date dodania artykulu
                if ( $WyswietlDate == 'tak' ) {
                    echo '<em class="DataDodania">' . $Artykul['data'] . '</em>';
                }    
                
                // czy pokazywac ilosc odslon
                if ( $IloscOdslon == 'tak' ) {
                    echo '<em class="IloscOdslon">{__TLUMACZ:ILOSC_WYSWIETLEN} ' . $Artykul['wyswietlenia'] . '</em>';
                }    

            echo '</span>';   
            
            echo '<div class="TrescAktualnosci">';
                
                if ( $Artykul['foto_artykulu'] != '' && file_exists(KATALOG_ZDJEC . '/' . $Artykul['foto_artykulu']) ) {
                     
                     echo '<div class="FotoArtykul">';
                         echo '<a href="' . $Artykul['seo'] . '">';
                         if ( AKTUALNOSCI_MINIATURY == 'nie' ) {
                            echo '<img src="' . KATALOG_ZDJEC . '/' . $Artykul['foto_artykulu'] . '" alt="' . $Artykul['tytul'] . '" />';
                         } else {
                            if ( AKTUALNOSCI_MINIATURY_FORMAT == 'nie' ) {
                               echo Funkcje::pokazObrazek($Artykul['foto_artykulu'], $Artykul['tytul'], AKTUALNOSCI_MINIATURY_SZEROKOSC, AKTUALNOSCI_MINIATURY_WYSOKOSC);
                            } else {
                               echo Funkcje::pokazObrazekKadrowany($Artykul['foto_artykulu'], $Artykul['tytul'], AKTUALNOSCI_MINIATURY_SZEROKOSC, AKTUALNOSCI_MINIATURY_WYSOKOSC);
                            }
                         }
                         echo '</a>';
                     echo '</div>';
                     
                }
                
                echo '<div class="OpisArtykul">';

                    echo $Artykul['opis_krotki'];

                    // czy pokazywac przycisk szczegolow 
                    if (strlen((string)$Artykul['opis']) > 10) {
                        //
                        echo '<div class="cl"></div><a href="' . $Artykul['seo'] . '" class="przycisk MargPrzycisk">{__TLUMACZ:PRZYCISK_PRZECZYTAJ_CALOSC}</a>';
                        //
                    }
                    
                echo '</div>'; 
                
            echo '</div>';   
  
        echo '</article>';

    }
    
    echo '</div>';
    //
    echo '<div class="cl"></div>';
      
}

unset($IloscArtykulow, $LimitZapytania, $IloscOdslon, $WyswietlDate, $WyswietlAutor, $TablicaArtykulow);
?>