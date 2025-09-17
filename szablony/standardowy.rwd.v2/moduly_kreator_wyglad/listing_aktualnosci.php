<?php

$TablicaArtykulow = array();

if ( $Konfiguracja['sortowanie_aktualnosci'] == 'data' ) {
     //
     // jezeli jest wyswietlanie tylko ostatnio dodanych artykulow
     if ( (int)$Konfiguracja['aktualnosci_id_kategoria'] == 0 ) {
          //
          $TablicaArtykulow = Aktualnosci::TablicaAktualnosciLimit( $Konfiguracja['ilosc_aktualnosci'] );
          //
     } else {
          //
          if ( (int)$Konfiguracja['aktualnosci_id_kategoria'] == 999999 ) {
                //
                $TablicaArtykulow = Aktualnosci::TablicaAktualnosciSzukaj( $Konfiguracja['warunki_aktualnosci_tytul'], $Konfiguracja['warunki_aktualnosci_autor'], $Konfiguracja['ilosc_aktualnosci'] );
                //
          } else {
                //
                $TablicaArtykulow = Aktualnosci::TablicaAktualnosciKategoria( $Konfiguracja['aktualnosci_id_kategoria'], $Konfiguracja['ilosc_aktualnosci'] );
                //
          }
          //
     }
     //
}
if ( $Konfiguracja['sortowanie_aktualnosci'] == 'losowo' ) {
     //     
     $TablicaArtykulowTmp = array();
     // jezeli jest wyswietlanie tylko ostatnio dodanych artykulow
     if ( (int)$Konfiguracja['aktualnosci_id_kategoria'] == 0 ) {
          //
          $TablicaArtykulowTmp = Aktualnosci::TablicaAktualnosciLimit( 999 );
          //
     } else {
          //
          if ( (int)$Konfiguracja['aktualnosci_id_kategoria'] == 999999 ) {
                //
                $TablicaArtykulowTmp = Aktualnosci::TablicaAktualnosciSzukaj( $Konfiguracja['warunki_aktualnosci_tytul'], $Konfiguracja['warunki_aktualnosci_autor'], 999 );
                //
          } else {
                //
                $TablicaArtykulowTmp = Aktualnosci::TablicaAktualnosciKategoria( $Konfiguracja['aktualnosci_id_kategoria'], 999 );
          }
          //
     }
     //
     if (count($TablicaArtykulowTmp) > 0) {
         //
         $TablicaArtykulow = Funkcje::wylosujElementyTablicyJakoTablica($TablicaArtykulowTmp, $Konfiguracja['ilosc_aktualnosci']);
         //
     }    
     //
     unset($TablicaArtykulowTmp);
     //
}
     
if ( count($TablicaArtykulow) > 0 ) {
  
     // jezeli jest tylko jedna pozycja to przejmuje tryb statyczny
     if ( count($TablicaArtykulow) == 1 ) {
          //
          $Konfiguracja['sposob_wyswietlania'] = 'statyczny';
          //
     }  

     $IdCssDodatkowy = '';
    
     if ( $Konfiguracja['nawigacja_strzalki_urzadzenie'] == 'komputer' && $Konfiguracja['nawigacja_strzalki'] == 'tak' ) {
          //
          $IdCssDodatkowy .= ' slick-arrow-desktop';
          //
     } 
     if ( $Konfiguracja['nawigacja_strzalki_urzadzenie'] == 'mobile' && $Konfiguracja['nawigacja_strzalki'] == 'tak' ) {
          //
          $IdCssDodatkowy .= ' slick-arrow-mobile';
          //
     } 
     if ( $Konfiguracja['nawigacja_przyciski_urzadzenie'] == 'komputer' && $Konfiguracja['nawigacja_przyciski'] == 'tak' ) {
          //
          $IdCssDodatkowy .= ' slick-dots-desktop';
          //
     } 
     if ( $Konfiguracja['nawigacja_przyciski_urzadzenie'] == 'mobile' && $Konfiguracja['nawigacja_przyciski'] == 'tak' ) {
          //
          $IdCssDodatkowy .= ' slick-dots-mobile';
          //
     }        

     // sprawdzi czy polozenie strzalek jest poza elementami - jezeli tak to nie doda klasy konetnera z marginasami
     $IdCssDodatkowyKontener = 'AnimacjaKreatorKontener';
     
     if ( $Konfiguracja['nawigacja_strzalki_polozenie'] == 'tak' && $Konfiguracja['nawigacja_strzalki'] == 'tak' && $Konfiguracja['nawigacja_strzalki_miejsce_wyswietlania'] == 'boki' ) {
          //
          $IdCssDodatkowyKontener = 'AnimacjaKreatorKontenerStrzalki';
          //
     }
     
     $IdGrupyArtykulow = '';

     // jezeli wyswietlanie statyczne lub animowane
     if ( $Konfiguracja['sposob_wyswietlania'] == 'statyczny' ) {
          //
          $IdGrupyArtykulow = 'Statyczny-' . ucfirst($Konfiguracja['kolumna']) . '-' . $Konfiguracja['id_modulu'];
          //
          echo '<div class="OknaFlexRwd ' . $IdGrupyArtykulow . '">';
          //
     } else if ( $Konfiguracja['sposob_wyswietlania'] == 'animowany' ) {
          //
          $IdGrupyArtykulow = 'Animacja-' . ucfirst($Konfiguracja['kolumna']) . '-' . $Konfiguracja['id_modulu'];
          //
          echo '<div class="' . $IdCssDodatkowyKontener . '"><div class="AnimacjaKreator ' . $IdGrupyArtykulow . $IdCssDodatkowy . '">';
          //
     }   
     
     unset($IdCssDodatkowyKontener, $IdCssDodatkowy);
  
     // listing aktualnosci
     foreach ( $TablicaArtykulow as $Artykul ) {

          echo '<article class="ArtykulOkno' . (($Konfiguracja['sposob_wyswietlania'] == 'statyczny') ? ' OknoFlex' : '') . '">';
          
              echo '<div class="ElementOknoRamka">';

                  if ( $Konfiguracja['foto_aktualnosci'] == 'tak' && $Artykul['foto_artykulu'] != '' ) {
                       //
                       if ( file_exists(KATALOG_ZDJEC . '/' . $Artykul['foto_artykulu']) ) {
                            //
                            echo '<div class="FotoArtykulu">';
                           
                               echo '<a href="' . $Artykul['seo'] . '">';

                               if ( AKTUALNOSCI_MINIATURY == 'nie' ) {
                                 
                                    //
                                    list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $Artykul['foto_artykulu']);
                                    //                             
                                    echo '<img style="width:' . $szerokosc . 'px;height:' . $wysokosc . 'px" src="' . KATALOG_ZDJEC . '/' . $Artykul['foto_artykulu'] . '" alt="' . $Artykul['tytul'] . '" />';
                                    
                               } else {
                                 
                                   if ( AKTUALNOSCI_MINIATURY_FORMAT == 'nie' ) {
                                       echo Funkcje::pokazObrazek($Artykul['foto_artykulu'], $Artykul['tytul'], AKTUALNOSCI_MINIATURY_SZEROKOSC, AKTUALNOSCI_MINIATURY_WYSOKOSC);
                                   } else {
                                       echo Funkcje::pokazObrazekKadrowany($Artykul['foto_artykulu'], $Artykul['tytul'], AKTUALNOSCI_MINIATURY_SZEROKOSC, AKTUALNOSCI_MINIATURY_WYSOKOSC);
                                   }
                                   
                               }

                               echo '</a>';
                               
                            echo '</div>';
                            //       
                       }
                       //
                  }        
                  
                  echo '<h3>' . $Artykul['link'] . '</h3>';
              
                  echo '<div class="DaneAktualnosci">';

                      // czy pokazywac nazwe autora
                      if ( $Konfiguracja['autor_aktualnosci'] == 'tak' && $Artykul['autor'] != '' ) {
                           //
                           echo '<em class="AutorArtykulu">{__TLUMACZ:AUTOR} ' . $Artykul['autor'] . '</em>';
                           //
                      }            
                  
                      // czy pokazywac date dodania artykulu
                      if ( $Konfiguracja['data_dodania_aktualnosci'] == 'tak' ) {
                           //
                           echo '<em class="DataDodania">' . $Artykul['data'] . '</em>';
                           //
                      }    
                      
                      // czy pokazywac ilosc odslon
                      if ( $Konfiguracja['odslony_aktualnosci'] == 'tak' ) {
                           //
                           echo '<em class="IloscOdslon">{__TLUMACZ:ILOSC_WYSWIETLEN} ' . $Artykul['wyswietlenia'] . '</em>';
                           //
                      }      
                  
                  echo '</div>';   
          
                  echo '<div class="OpisArtykul">' . $Artykul['opis_krotki'] . '</div>';

                  // czy pokazywac przycisk szczegolow 
                  if ( strlen((string)$Artykul['opis']) > 10 ) {
                       //
                       echo '<div class="cl"></div><div class="LinkCalyArtykul"><a href="' . $Artykul['seo'] . '" class="przycisk">{__TLUMACZ:PRZYCISK_PRZECZYTAJ_CALOSC}</a></div>';
                       //
                  }
                  
              echo '</div>';

          echo '</article>';
        
     }
    
     echo '</div>';
    
     // jezeli wyswietlanie animowane
     if ( $Konfiguracja['sposob_wyswietlania'] == 'animowany' ) {
          //
          echo '</div>';
          //
     }       
    
     echo '<div class="cl"></div>';

     // jezeli wyswietlanie animowane
     if ( $Konfiguracja['sposob_wyswietlania'] == 'statyczny' ) {
          //
          $StylCss = '';
          //
          for ( $tr = 0; $tr < count($Konfiguracja['ilosc_kolumn_aktualnosci']); $tr++ ) {
            
                $StylCss .= '@media only screen and (min-width:' . $Konfiguracja['ilosc_kolumn_aktualnosci'][$tr]['rozdzielczosc'] . 'px)' . (($tr > 0) ? ' and (max-width:' . ($Konfiguracja['ilosc_kolumn_aktualnosci'][$tr - 1]['rozdzielczosc'] - 1) . 'px)' : '') . ' { 
                              .' . $IdGrupyArtykulow . ' article { width:calc(' . ((count($TablicaArtykulow) > (int)$Konfiguracja['ilosc_kolumn_aktualnosci'][$tr]['kolumny']) ? '(100% / '.(int)$Konfiguracja['ilosc_kolumn_aktualnosci'][$tr]['kolumny'].') - ((('.(int)$Konfiguracja['ilosc_kolumn_aktualnosci'][$tr]['kolumny'].' - 1) / '.(int)$Konfiguracja['ilosc_kolumn_aktualnosci'][$tr]['kolumny'].') * var(--okna-odstep))' : '(100% / '.count($TablicaArtykulow).') - ((('.count($TablicaArtykulow).' - 1) / '.count($TablicaArtykulow).') * var(--okna-odstep))') . ') }

                            }';
                
          }          
          //
          $GLOBALS['css'] .= $StylCss;
          //
          unset($StylCss);
          //                  
     }          

     // jezeli wyswietlanie animowane
     if ( $Konfiguracja['sposob_wyswietlania'] == 'animowany' ) {
          //
          $StylCss = '';
          //
          // jezeli strzalki maja byc wyswietlane poza obszarem tresci
          if ( $Konfiguracja['nawigacja_strzalki_polozenie'] == 'tak' && $Konfiguracja['nawigacja_strzalki'] == 'tak' ) {
               //
               if ( $Konfiguracja['nawigacja_strzalki_miejsce_wyswietlania'] == 'tytul' && $Konfiguracja['naglowek_kolumny'] == 'tak' ) {
                    //
                    $StylCss .= '.' . $IdGrupyArtykulow . ' .slick-prev { position:fixed; left:initial; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px !important; }';
                    $StylCss .= '.' . $IdGrupyArtykulow . ' .slick-next { position:fixed; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:0px !important; }';      
                    $StylCss .= '@media only screen and (max-width:779px) { .' . $IdGrupyArtykulow . ' .slick-prev { position:fixed; left:initial; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 10) . 'px !important; }}';
                    //
               } else {
                    //
                    $StylCss .= '.' . $IdGrupyArtykulow . ' .slick-list { margin:0px ' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px 0px ' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px; }';
                    $StylCss .= '.' . $IdGrupyArtykulow . ' .slick-prev { left:0px; }';
                    $StylCss .= '.' . $IdGrupyArtykulow . ' .slick-next { right:0px; }';
                    //
               }
               //
          }
          
          // sprawdzi czy nie przesunac strzalek jak sa kropki
          if ( $Konfiguracja['nawigacja_przyciski'] == 'tak' && $Konfiguracja['nawigacja_strzalki_polozenie'] == 'nie' ) {
               //
               $StylCss .= '.' . $IdGrupyArtykulow . ' .slick-prev, .' . $IdGrupyArtykulow . ' .slick-next { margin-top:-' . ( $Konfiguracja['nawigacja_przyciski_rozmiar'] / 2 ) . 'px; }';
               //
          }                
          
          $StylCss .= '.' . $IdGrupyArtykulow . ' { text-align:center; }          
                       .' . $IdGrupyArtykulow . ' > .ArtykulOkno:not(:first-child) { display: none; }
                       ' . (($Konfiguracja['nawigacja_przyciski'] == 'tak') ? '.' . $IdGrupyArtykulow . ' .slick-dots li:only-child { display: none; }' : '') . '
                       .' . $IdGrupyArtykulow . ' .slick-prev, .' . $IdGrupyArtykulow . ' .slick-next { width:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px; height:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px; }
                       .' . $IdGrupyArtykulow . ' .slick-prev:before, .' . $IdGrupyArtykulow . ' .slick-next:before { font-size:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px' . (($Konfiguracja['nawigacja_strzalki_kolor'] != '') ? '; color:#' . $Konfiguracja['nawigacja_strzalki_kolor'] : '') . '; }
                       .' . $IdGrupyArtykulow . ' .slick-prev:before { content:"\\' . $Konfiguracja['nawigacja_strzalki_czcionka_wstecz'] . '" }
                       .' . $IdGrupyArtykulow . ' .slick-next:before { content:"\\' . $Konfiguracja['nawigacja_strzalki_czcionka_naprzod'] . '" }
                       .' . $IdGrupyArtykulow . ' .slick-dots li button:before { content:"\\' . $Konfiguracja['nawigacja_przyciski_czcionka'] . '"; font-size:' . $Konfiguracja['nawigacja_przyciski_rozmiar'] . 'px' . (($Konfiguracja['nawigacja_przyciski_kolor'] != '') ? '; color:#' . $Konfiguracja['nawigacja_przyciski_kolor'] : '') . '; }
                       ' . (($Konfiguracja['nawigacja_przyciski_kolor_aktywna'] != '') ? '.' . $IdGrupyArtykulow . ' .slick-dots li.slick-active button:before { color:#' . $Konfiguracja['nawigacja_przyciski_kolor_aktywna'] . '; }' : '');
                       
          $GLOBALS['css'] .= $StylCss;
          //
          unset($StylCss);
          //
          $NazwaFunkcjiJs = 'Przelicz_' . $Konfiguracja['id_modulu'] . '_' . $Konfiguracja['kolumna'] . '()';
          //
          echo '<script>
                   function ' . $NazwaFunkcjiJs . ' {
                      var max_wysokosc = 0;
                      $(\'.' . $IdGrupyArtykulow . ' .slick-slide\').css({ \'height\' : \'auto\' });
                      $(\'.' . $IdGrupyArtykulow . ' .slick-slide\').each(function() {
                          if ( $(this).outerHeight() > max_wysokosc ) {
                               max_wysokosc = $(this).outerHeight();
                          }
                      });
                      $(\'.' . $IdGrupyArtykulow . ' .slick-slide\').css({ \'height\' : max_wysokosc });';
  
                      if ( $Konfiguracja['nawigacja_strzalki_polozenie'] == 'tak' && $Konfiguracja['nawigacja_strzalki'] == 'tak' ) {
                           //
                           if ( $Konfiguracja['nawigacja_strzalki_miejsce_wyswietlania'] == 'boki' ) {
                                //
                                echo 'if ($(\'.' . $IdGrupyArtykulow . '\').find(\'.slick-arrow\').length === 0) { $(\'.' . $IdGrupyArtykulow . ' .slick-list\').css({ \'margin\' : \'0\' }); }';
                                //
                           }
                           //
                      }
                      
                   echo '}
                   $(document).ready(function() {
                     $(\'.' . $IdGrupyArtykulow . '\').on(\'setPosition\', function(event, slick, direction){ ' . $NazwaFunkcjiJs . ' }).slick({
                     infinite: true,
                     ' . (($Konfiguracja['nawigacja_przyciski'] == 'tak') ? 'dots: true,' : 'dots: false,') . '
                     ' . (($Konfiguracja['nawigacja_strzalki'] == 'tak') ? 'arrows: true,' : 'arrows: false,') . '
                     ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplay: true,' : '') . '
                     ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplaySpeed: ' . ($Konfiguracja['czas_zmiany_animacji'] * 1000) . ',' : '') . '
                     ' . (($Konfiguracja['sposob_animacji'] == 'przenikanie') ? 'fade: true,' : '') . '
                     speed: ' . ($Konfiguracja['czas_przejscia_efektu_animacji'] * 1000) . ', 
                     pauseOnHover: false,
                     pauseOnFocus: false,
                     slidesToShow: ' . ((count($TablicaArtykulow) > (int)$Konfiguracja['ilosc_kolumn_aktualnosci'][0]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_aktualnosci'][0]['kolumny'] : count($TablicaArtykulow)) . ',
                     slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_aktualnosci'][0]['kolumny']) . ',                        
                     responsive: [';
                       
                          for ( $tr = 1; $tr < count($Konfiguracja['ilosc_kolumn_aktualnosci']); $tr++ ) {
                            
                                echo '{ 
                                        breakpoint: ' . $Konfiguracja['ilosc_kolumn_aktualnosci'][$tr - 1]['rozdzielczosc'] . ',
                                        settings: {
                                          slidesToShow: ' . ((count($TablicaArtykulow) > (int)$Konfiguracja['ilosc_kolumn_aktualnosci'][$tr]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_aktualnosci'][$tr]['kolumny'] : count($TablicaArtykulow)) . ',
                                          slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_aktualnosci'][$tr]['kolumny']) . '
                                        }   
                                      },';
                                
                          }                         
                        
                     echo ']                     
                     });
                   });
                </script>';
          //
          unset($NazwaFunkcjiJs);
          //
     }               
 
} else {
 
     if ( isset($Konfiguracja['wyswietlana_kolumna']) && $Konfiguracja['wyswietlana_kolumna'] == 'tak' ) {
          //
          echo '<div class="Informacja">{__TLUMACZ:BRAK_DANYCH_DO_WYSWIETLENIA}</div>';
          //
     }
  
}

unset($TablicaArtykulow);
?>