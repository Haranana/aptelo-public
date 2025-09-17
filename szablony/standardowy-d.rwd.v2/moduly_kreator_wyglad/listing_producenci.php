<?php
// zakres producentow
$TablicaProducentow = Producenci::TablicaProducenci();

if ( $Konfiguracja['zakres_producenci'] == 'wybrane' ) {
    //
    $TablicaProducentowTmp = array();
    //
    foreach ($TablicaProducentow as $IdProducent => $ProducentWartosc) {
         //
         if ( in_array($IdProducent, $Konfiguracja['id_zakres_producenci']) ) {
              //
              $TablicaProducentowTmp[$IdProducent] = $ProducentWartosc;
              //
         }
         //
    }
    //
    $TablicaProducentow = $TablicaProducentowTmp;
    //
    unset($TablicaProducentowTmp);
    //
}

if ( $Konfiguracja['sortowanie_producentow'] == 'losowo' ) {
     //     
     $TablicaProducentowTmp = $TablicaProducentow;
     //
     if (count($TablicaProducentowTmp) > 0) {
         //
         $TablicaProducentow = array();
         $Indeksy = array_keys($TablicaProducentowTmp);
         //
         shuffle($Indeksy);
         //
         foreach ( $Indeksy as $Indeks ) {
            //
            $TablicaProducentow[$Indeks] = $TablicaProducentowTmp[$Indeks];
            //
         }
         //
     }    
     //
     unset($TablicaProducentowTmp);
     //
}

// jezeli okreslona ilosc producentow
if ( $Konfiguracja['ilosc_producentow'] != 9999 && count($TablicaProducentow) > 0 ) {
     //
     $TablicaProducentow = Funkcje::wylosujElementyTablicyJakoTablica($TablicaProducentow, $Konfiguracja['ilosc_producentow']);
     //
}

if ( count($TablicaProducentow) > 0 ) {
  
     // jezeli jest tylko jedna pozycja to przejmuje tryb statyczny
     if ( count($TablicaProducentow) == 1 ) {
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
     
     $IdGrupyProducentow = '';

     // jezeli wyswietlanie statyczne lub animowane
     if ( $Konfiguracja['sposob_wyswietlania'] == 'statyczny' ) {
          //
          $IdGrupyProducentow = 'Statyczny-' . ucfirst($Konfiguracja['kolumna']) . '-' . $Konfiguracja['id_modulu'];
          //
          echo '<div class="OknaFlexRwd ' . $IdGrupyProducentow . '">';
          //
     } else if ( $Konfiguracja['sposob_wyswietlania'] == 'animowany' ) {
          //
          $IdGrupyProducentow = 'Animacja-' . ucfirst($Konfiguracja['kolumna']) . '-' . $Konfiguracja['id_modulu'];
          //
          echo '<div class="' . $IdCssDodatkowyKontener . '"><div class="AnimacjaKreator ' . $IdGrupyProducentow . $IdCssDodatkowy . '">';
          //
     }   
     
     unset($IdCssDodatkowyKontener, $IdCssDodatkowy);
  
     // listing producentow
     foreach ( $TablicaProducentow as $Producent ) {

          echo '<article class="ProducentOkno' . (($Konfiguracja['sposob_wyswietlania'] == 'statyczny') ? ' OknoFlex' : '') . '">';
          
              echo '<div class="ElementOknoRamka">';
              
                  echo '<div class="ProducentChmura">';

                      echo '<a href="' . Seo::link_SEO( $Producent['Nazwa'], $Producent['IdProducenta'], 'producent' ) . '">';
                      
                      // czy wyswietlac logo producenta
                      if ( $Konfiguracja['logo_producentow'] == 'tak' ) {
                        
                           if ( !file_exists(KATALOG_ZDJEC . '/' . $Producent['Foto']) || $Producent['Foto'] == '' ) {
                                //
                                $Producent['Foto'] = 'domyslny.webp';
                                //
                           }

                           if ( file_exists(KATALOG_ZDJEC . '/' . $Producent['Foto']) && $Producent['Foto'] != '' ) {
                        
                                // jezeli wyswietlanie w oryginalnym rozmiarze
                                if ( $Konfiguracja['logo_rozmiar_producentow'] == 'brak' ) {
                                     //
                                     $image_info = getimagesize(KATALOG_ZDJEC . '/' . $Producent['Foto']);
                                     //
                                     echo '<span class="LogoProducent"><img src="' . KATALOG_ZDJEC . '/' . $Producent['Foto'] . '" alt="' . $Producent['Nazwa'] . '" width="'.$image_info['0'].'"  height="'.$image_info['1'].'" /></span>';
                                     unset($image_info);
                                     //
                                } else {
                                     //
                                     echo '<span class="LogoProducent">' . Funkcje::pokazObrazek($Producent['Foto'], $Producent['Nazwa'], $Konfiguracja['logo_rozmiar_producentow'], $Konfiguracja['logo_rozmiar_producentow'], array(), '', 'maly', true, false, false) . '</span>';
                                     //
                                }
                                
                           }
                      
                      }
                      
                      // czy wyswietlac nazwe producenta
                      if ( $Konfiguracja['nazwa_producentow'] == 'tak' ) {
                           //
                           echo '<span class="NazwaProducent">' . $Producent['Nazwa'] . '</span>';
                           //
                      }
                      
                      echo '</a>';
                      
                  echo '</div>';
                  
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
          $StylCss = '.' . $IdGrupyProducentow . ' { text-align:center; justify-content:center; }  
                      .' . $IdGrupyProducentow . ' .ProducentChmura { padding:' . $Konfiguracja['margines_producentow_statycznych'] . 'px; }';       
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
                    $StylCss .= '.' . $IdGrupyProducentow . ' .slick-prev { position:fixed; left:initial; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px !important; }';
                    $StylCss .= '.' . $IdGrupyProducentow . ' .slick-next { position:fixed; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:0px !important; }';      
                    $StylCss .= '@media only screen and (max-width:779px) { .' . $IdGrupyProducentow . ' .slick-prev { position:fixed; left:initial; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 10) . 'px !important; }}';
                    //
               } else {
                    //
                    $StylCss .= '.' . $IdGrupyProducentow . ' .slick-list { margin:0px ' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px 0px ' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px; }';
                    $StylCss .= '.' . $IdGrupyProducentow . ' .slick-prev { left:0px; }';
                    $StylCss .= '.' . $IdGrupyProducentow . ' .slick-next { right:0px; }';
                    //
               }
               //
          }
          
          // sprawdzi czy nie przesunac strzalek jak sa kropki
          if ( $Konfiguracja['nawigacja_przyciski'] == 'tak' && $Konfiguracja['nawigacja_strzalki_polozenie'] == 'nie' ) {
               //
               $StylCss .= '.' . $IdGrupyProducentow . ' .slick-prev, .' . $IdGrupyProducentow . ' .slick-next { margin-top:-' . ( $Konfiguracja['nawigacja_przyciski_rozmiar'] / 2 ) . 'px; }';
               //
          }
               
          $StylCss .= '.' . $IdGrupyProducentow . ' { text-align:center; }    
                       .' . $IdGrupyProducentow . ' > .ProducentOkno:not(:first-child) { display: none; }
                       ' . (($Konfiguracja['nawigacja_przyciski'] == 'tak') ? '.' . $IdGrupyProducentow . ' .slick-dots li:only-child { display: none; }' : '') . '
                       .' . $IdGrupyProducentow . ' .slick-prev, .' . $IdGrupyProducentow . ' .slick-next { width:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px; height:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px; }
                       .' . $IdGrupyProducentow . ' .slick-prev:before, .' . $IdGrupyProducentow . ' .slick-next:before { font-size:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px' . (($Konfiguracja['nawigacja_strzalki_kolor'] != '') ? '; color:#' . $Konfiguracja['nawigacja_strzalki_kolor'] : '') . '; }
                       .' . $IdGrupyProducentow . ' .slick-prev:before { content:"\\' . $Konfiguracja['nawigacja_strzalki_czcionka_wstecz'] . '" }
                       .' . $IdGrupyProducentow . ' .slick-next:before { content:"\\' . $Konfiguracja['nawigacja_strzalki_czcionka_naprzod'] . '" }
                       .' . $IdGrupyProducentow . ' .slick-dots li button:before { content:"\\' . $Konfiguracja['nawigacja_przyciski_czcionka'] . '"; font-size:' . $Konfiguracja['nawigacja_przyciski_rozmiar'] . 'px' . (($Konfiguracja['nawigacja_przyciski_kolor'] != '') ? '; color:#' . $Konfiguracja['nawigacja_przyciski_kolor'] : '') . '; }
                       ' . (($Konfiguracja['nawigacja_przyciski_kolor_aktywna'] != '') ? '.' . $IdGrupyProducentow . ' .slick-dots li.slick-active button:before { color:#' . $Konfiguracja['nawigacja_przyciski_kolor_aktywna'] . '; }' : '');
                
          $GLOBALS['css'] .= $StylCss;
          //
          unset($StylCss);
          //
          $NazwaFunkcjiJs = 'Przelicz_' . $Konfiguracja['id_modulu'] . '_' . $Konfiguracja['kolumna'] . '()';
          //
          echo '<script>
                   function ' . $NazwaFunkcjiJs . ' {
                      var max_wysokosc = 0;
                      $(\'.' . $IdGrupyProducentow . ' .slick-slide\').css({ \'height\' : \'auto\' });
                      $(\'.' . $IdGrupyProducentow . ' .slick-slide\').each(function() {
                          if ( $(this).outerHeight() > max_wysokosc ) {
                               max_wysokosc = $(this).outerHeight();
                          }
                      });
                      $(\'.' . $IdGrupyProducentow . ' .slick-slide\').css({ \'height\' : max_wysokosc });';
  
                      if ( $Konfiguracja['nawigacja_strzalki_polozenie'] == 'tak' && $Konfiguracja['nawigacja_strzalki'] == 'tak' ) {
                           //
                           if ( $Konfiguracja['nawigacja_strzalki_miejsce_wyswietlania'] == 'boki' ) {
                                //
                                echo 'if ($(\'.' . $IdGrupyProducentow . '\').find(\'.slick-arrow\').length === 0) { $(\'.' . $IdGrupyProducentow . ' .slick-list\').css({ \'margin\' : \'0\' }); }';
                                //
                           }
                           //
                      }
                      
                   echo '}
                   $(document).ready(function() {
                     $(\'.' . $IdGrupyProducentow . '\').on(\'setPosition\', function(event, slick, direction){ ' . $NazwaFunkcjiJs . ' }).slick({
                     infinite: true,
                     ' . (($Konfiguracja['nawigacja_przyciski'] == 'tak') ? 'dots: true,' : 'dots: false,') . '
                     ' . (($Konfiguracja['nawigacja_strzalki'] == 'tak') ? 'arrows: true,' : 'arrows: false,') . '
                     ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplay: true,' : '') . '
                     ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplaySpeed: ' . ($Konfiguracja['czas_zmiany_animacji'] * 1000) . ',' : '') . '
                     ' . (($Konfiguracja['sposob_animacji'] == 'przenikanie') ? 'fade: true,' : '') . '
                     speed: ' . ($Konfiguracja['czas_przejscia_efektu_animacji'] * 1000) . ', 
                     pauseOnHover: false,
                     pauseOnFocus: false,
                     slidesToShow: ' . ((count($TablicaProducentow) > (int)$Konfiguracja['ilosc_kolumn_producentow'][0]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_producentow'][0]['kolumny'] : count($TablicaProducentow)) . ',
                     slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_producentow'][0]['kolumny']) . ',                   
                     responsive: [';
                       
                          for ( $tr = 1; $tr < count($Konfiguracja['ilosc_kolumn_producentow']); $tr++ ) {
                            
                                echo '{ 
                                        breakpoint: ' . $Konfiguracja['ilosc_kolumn_producentow'][$tr - 1]['rozdzielczosc'] . ',
                                        settings: {
                                          slidesToShow: ' . ((count($TablicaProducentow) > (int)$Konfiguracja['ilosc_kolumn_producentow'][$tr]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_producentow'][$tr]['kolumny'] : count($TablicaProducentow)) . ',
                                          slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_producentow'][$tr]['kolumny']) . '
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

unset($TablicaProducentow);
?>