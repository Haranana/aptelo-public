<?php
// czy jest zapytanie
if ($IloscArtykulow > 0) { 

    echo '<div class="OknaRwd Kol-' . AKTUALNOSCI_ILOSC_KOLUMN . ' ListingArtykulyKontener">';
    
    foreach ( $TablicaArtykulow as $Artykul ) {
        //
        echo '<article class="KomorkaTbl OknoRwd" ' . $KategoriaArtykulu['dane_strukturalne_itemprop'] . '>';
        
            echo '<div class="ElementListingRamka">';

                if ( $KategoriaArtykulu['dane_strukturalne_status'] == 'tak' ) {
                  
                     echo '<link itemprop="mainEntityOfPage" href="' . ADRES_URL_SKLEPU . '/' . $Artykul['seo'] . '" />';
                
                     echo '<meta itemprop="position" content="' . $Artykul['pozycja'] . '" />';
                     echo '<meta itemprop="url" content="' . ADRES_URL_SKLEPU . '/' . $Artykul['seo'] . '" />';

                     echo '<div itemprop="author" itemscope itemtype="https://schema.org/Person" style="display:none">
                                <meta itemprop="name" content="' . ((!empty($Artykul['autor'])) ? $Artykul['autor'] : DANE_NAZWA_FIRMY_SKROCONA) . '" />
                                <meta itemprop="url" content="' . ADRES_URL_SKLEPU . '" />
                           </div>
                           
                           <meta itemprop="datePublished" content="' . date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($Artykul['data'])) . '" />
                           <meta itemprop="dateModified" content="' . date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($Artykul['data'])) . '" />
                           <meta itemprop="headline" content="' . str_replace('"', '', (string)$Artykul['tytul']) . '" />
                           
                           <div itemprop="publisher" itemscope itemtype="https://schema.org/Organization" style="display:none">
                               <div itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
                                   <meta itemprop="url" content="' . $Artykul['dane_strukturalne_wydawca_foto'] . '" />
                               </div>
                               <meta itemprop="name" content="' . $Artykul['dane_strukturalne_wydawca_nazwa'] . '" />
                           </div>';    

                     echo '<div itemprop="image" itemscope itemtype="https://schema.org/ImageObject" style="display:none">
                               <meta itemprop="url" content="' . KATALOG_ZDJEC . '/' . ((!empty($Artykul['foto_artykulu'])) ? $Artykul['foto_artykulu'] : 'domyslny.webp') . '" />
                           </div>';
                           
                }
                
                //
                echo '<h2>' . $Artykul['link'] . '</h2>';
                //
                echo '<span class="DaneAktualnosci">';
                
                    // czy pokazywac date dodania artykulu
                    if ( AKTUALNOSCI_AUTOR_LISTING == 'tak' && $Artykul['autor'] != '' ) {
                        //
                        echo '<em class="AutorArtykulu">{__TLUMACZ:AUTOR} ' . $Artykul['autor'] . '</em>';
                        //
                    }            
                    // czy pokazywac date dodania artykulu
                    if ( AKTUALNOSCI_DATA_LISTING == 'tak' ) {
                        //
                        echo '<em class="DataDodania">' . $Artykul['data'] . '</em>';
                        //
                    }    
                    // czy pokazywac ilosc odslon
                    if ( AKTUALNOSCI_ILOSC_ODSLON_LISTING == 'tak' ) {
                        //
                        echo '<em class="IloscOdslon">{__TLUMACZ:ILOSC_WYSWIETLEN} ' . $Artykul['wyswietlenia'] . '</em>';
                        //
                    }    
                
                echo '</span>';   
                //        
                echo '<div class="TrescAktualnosci">';
                    
                    if ( $Artykul['foto_artykulu'] != '' && file_exists(KATALOG_ZDJEC . '/' . $Artykul['foto_artykulu']) ) {
                         
                         echo '<div class="FotoArtykul"><a href="' . $Artykul['seo'] . '">';

                         if ( AKTUALNOSCI_MINIATURY == 'nie' ) {
                              //
                              list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $Artykul['foto_artykulu']);
                              //                            
                              echo '<img src="' . KATALOG_ZDJEC . '/' . $Artykul['foto_artykulu'] . '" alt="' . $Artykul['tytul'] . '" title="' . $Artykul['tytul'] . '"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' />';
                              //
                         } else {
                              //
                              if ( AKTUALNOSCI_MINIATURY_FORMAT == 'nie' ) {
                                   //
                                   echo Funkcje::pokazObrazek($Artykul['foto_artykulu'], $Artykul['tytul'], AKTUALNOSCI_MINIATURY_SZEROKOSC, AKTUALNOSCI_MINIATURY_WYSOKOSC);
                                   //
                              } else {
                                   //
                                   echo Funkcje::pokazObrazekKadrowany($Artykul['foto_artykulu'], $Artykul['tytul'], AKTUALNOSCI_MINIATURY_SZEROKOSC, AKTUALNOSCI_MINIATURY_WYSOKOSC);
                                   //
                              }
                              //
                         }

                         echo '</a></div>';
                         
                    }
                    
                    echo '<div class="OpisArtykul">';

                        echo $Artykul['opis_krotki'];
                        
                        echo '<div class="cl"></div>';

                    echo '</div>'; 
                    
                echo '</div>';    

                // czy pokazywac przycisk szczegolow 
                if (AKTUALNOSCI_PRZYCISK_ZOBACZ_LISTING == 'tak' && strlen((string)$Artykul['opis']) > 10) {
                    //
                    echo '<div class="LinkCalyArtykul" style="text-align:left"><a href="' . $Artykul['seo'] . '" class="przycisk MargPrzycisk">{__TLUMACZ:PRZYCISK_PRZECZYTAJ_CALOSC}</a></div>';
                    //
                }
                        
            echo '</div>'; 
        
        echo '</article>';
        
    }
    
    echo '</div>';
    //
    echo '<div class="cl"></div>';
    
} else {

    echo '<div id="BrakProduktow" class="Informacja">{__TLUMACZ:BLAD_ARTYKULOW}</div>';
  
}

unset($IloscArtykulow);    
?>