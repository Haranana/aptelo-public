<?php

if ( count($Konfiguracja['filmy_youtube']) > 0 ) {
  
     // jezeli jest tylko jedna pozycja to przejmuje tryb statyczny
     if ( count($Konfiguracja['filmy_youtube']) == 1 ) {
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
     
     $IdGrupyFilmow = '';

     // jezeli wyswietlanie statyczne lub animowane
     if ( $Konfiguracja['sposob_wyswietlania'] == 'statyczny' ) {
          //
          $IdGrupyFilmow = 'Statyczny-' . ucfirst($Konfiguracja['kolumna']) . '-' . $Konfiguracja['id_modulu'];
          //
          echo '<div class="OknaFlexRwd ' . $IdGrupyFilmow . '">';
          //
     } else if ( $Konfiguracja['sposob_wyswietlania'] == 'animowany' ) {
          //
          $IdGrupyFilmow = 'Animacja-' . ucfirst($Konfiguracja['kolumna']) . '-' . $Konfiguracja['id_modulu'];
          //
          echo '<div class="' . $IdCssDodatkowyKontener . '"><div class="AnimacjaKreator ' . $IdGrupyFilmow . $IdCssDodatkowy . '">';
          //
     }   
  
     unset($IdCssDodatkowyKontener, $IdCssDodatkowy);
  
     // listing filmow
     for ($v = 0, $cs = count($Konfiguracja['filmy_youtube']); $v < $cs; $v++) {

          echo '<article class="FilmOkno' . (($Konfiguracja['sposob_wyswietlania'] == 'statyczny') ? ' OknoFlex' : '') . '">';
          
              echo '<div class="ElementOknoRamka">';

                  echo '<div class="FilmFrame" data-video="https://www.youtube.com/embed/' . $Konfiguracja['filmy_youtube'][$v] . '">';
                  
                  if ( $Konfiguracja['miniaturka_youtube'] == 'film' ) {
                       //
                       echo '<iframe allowfullscreen="" frameborder="0" src="https://www.youtube.com/embed/' . $Konfiguracja['filmy_youtube'][$v] . '"></iframe>';
                       //
                  } else {
                       //
                       $MiniaturkaYoutube = '';
                       //
                       // sprawdzi czy jest plik
                       if ( $MiniaturkaYoutube == '' ) {
                            //
                            $url = 'https://img.youtube.com/vi/' . $Konfiguracja['filmy_youtube'][$v] . '/hqdefault.jpg';
                            if ( get_headers($url)[0] == 'HTTP/1.0 200 OK' ) {
                                 //
                                 $MiniaturkaYoutube = 'hqdefault.jpg';
                                 //
                            }
                            //
                       }                    
                       //
                       if ( $MiniaturkaYoutube == '' ) {
                            //
                            $url = 'https://img.youtube.com/vi/' . $Konfiguracja['filmy_youtube'][$v] . '/default.jpg';
                            if ( get_headers($url)[0] == 'HTTP/1.0 200 OK' ) {
                                 //
                                 $MiniaturkaYoutube = 'default.jpg';
                                 //
                            }
                            //
                       }
                       //
                       if ( $MiniaturkaYoutube != '' ) {
                            //
                            echo '<div class="YouTubeScreen" style="background:url(\'https://img.youtube.com/vi/' . $Konfiguracja['filmy_youtube'][$v] . '/' . $MiniaturkaYoutube . '\') center no-repeat; background-size:100.5% auto;"></div>';
                            //
                       }
                       //
                  }
                            
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
          $StylCss = '';
          //
          for ( $tr = 0; $tr < count($Konfiguracja['ilosc_kolumn_youtube']); $tr++ ) {
            
                $StylCss .= '@media only screen and (min-width:' . $Konfiguracja['ilosc_kolumn_youtube'][$tr]['rozdzielczosc'] . 'px)' . (($tr > 0) ? ' and (max-width:' . ($Konfiguracja['ilosc_kolumn_youtube'][$tr - 1]['rozdzielczosc'] - 1) . 'px)' : '') . ' { 
                               .' . $IdGrupyFilmow . ' article { width:calc(' . ((count($Konfiguracja['filmy_youtube']) > (int)$Konfiguracja['ilosc_kolumn_youtube'][$tr]['kolumny']) ? '(100% / '.(int)$Konfiguracja['ilosc_kolumn_youtube'][$tr]['kolumny'].') - ((('.(int)$Konfiguracja['ilosc_kolumn_youtube'][$tr]['kolumny'].' - 1) / '.(int)$Konfiguracja['ilosc_kolumn_youtube'][$tr]['kolumny'].') * var(--okna-odstep))' : '(100% / '.count($Konfiguracja['filmy_youtube']).') - ((('.count($Konfiguracja['filmy_youtube']).' - 1) / '.count($Konfiguracja['filmy_youtube']).') * var(--okna-odstep))') . ') }
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
                    $StylCss .= '.' . $IdGrupyFilmow . ' .slick-prev { position:fixed; left:initial; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px !important; }';
                    $StylCss .= '.' . $IdGrupyFilmow . ' .slick-next { position:fixed; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:0px !important; }';      
                    $StylCss .= '@media only screen and (max-width:779px) { .' . $IdGrupyFilmow . ' .slick-prev { position:fixed; left:initial; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 10) . 'px !important; }}';
                    //
               } else {
                    //
                    $StylCss .= '.' . $IdGrupyFilmow . ' .slick-list { margin:0px ' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px 0px ' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px; }';
                    $StylCss .= '.' . $IdGrupyFilmow . ' .slick-prev { left:0px; }';
                    $StylCss .= '.' . $IdGrupyFilmow . ' .slick-next { right:0px; }';
                    //
               }
               //
          }
          
          // sprawdzi czy nie przesunac strzalek jak sa kropki
          if ( $Konfiguracja['nawigacja_przyciski'] == 'tak' && $Konfiguracja['nawigacja_strzalki_polozenie'] == 'nie' ) {
               //
               $StylCss .= '.' . $IdGrupyFilmow . ' .slick-prev, .' . $IdGrupyFilmow . ' .slick-next { margin-top:-' . ( $Konfiguracja['nawigacja_przyciski_rozmiar'] / 2 ) . 'px; }';
               //
          }                
          
          $StylCss .= '.' . $IdGrupyFilmow . ' { text-align:center; }      
                       .' . $IdGrupyFilmow . ' > .FilmOkno:not(:first-child) { display: none; }
                       ' . (($Konfiguracja['nawigacja_przyciski'] == 'tak') ? '.' . $IdGrupyFilmow . ' .slick-dots li:only-child { display: none; }' : '') . '
                       .' . $IdGrupyFilmow . ' .slick-prev, .' . $IdGrupyFilmow . ' .slick-next { width:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px; height:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px; }
                       .' . $IdGrupyFilmow . ' .slick-prev:before, .' . $IdGrupyFilmow . ' .slick-next:before { font-size:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px' . (($Konfiguracja['nawigacja_strzalki_kolor'] != '') ? '; color:#' . $Konfiguracja['nawigacja_strzalki_kolor'] : '') . '; }
                       .' . $IdGrupyFilmow . ' .slick-prev:before { content:"\\' . $Konfiguracja['nawigacja_strzalki_czcionka_wstecz'] . '" }
                       .' . $IdGrupyFilmow . ' .slick-next:before { content:"\\' . $Konfiguracja['nawigacja_strzalki_czcionka_naprzod'] . '" }
                       .' . $IdGrupyFilmow . ' .slick-dots li button:before { content:"\\' . $Konfiguracja['nawigacja_przyciski_czcionka'] . '"; font-size:' . $Konfiguracja['nawigacja_przyciski_rozmiar'] . 'px' . (($Konfiguracja['nawigacja_przyciski_kolor'] != '') ? '; color:#' . $Konfiguracja['nawigacja_przyciski_kolor'] : '') . '; }
                       ' . (($Konfiguracja['nawigacja_przyciski_kolor_aktywna'] != '') ? '.' . $IdGrupyFilmow . ' .slick-dots li.slick-active button:before { color:#' . $Konfiguracja['nawigacja_przyciski_kolor_aktywna'] . '; }' : '');
                
          $GLOBALS['css'] .= $StylCss;
          //
          unset($StylCss);
          //                
          echo '<script>
                   $(document).ready(function() {
                     $(\'.' . $IdGrupyFilmow . '\').slick({
                     infinite: true,
                     ' . (($Konfiguracja['nawigacja_przyciski'] == 'tak') ? 'dots: true,' : 'dots: false,') . '
                     ' . (($Konfiguracja['nawigacja_strzalki'] == 'tak') ? 'arrows: true,' : 'arrows: false,') . '
                     ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplay: true,' : '') . '
                     ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplaySpeed: ' . ($Konfiguracja['czas_zmiany_animacji'] * 1000) . ',' : '') . '                     
                     ' . (($Konfiguracja['sposob_animacji'] == 'przenikanie') ? 'fade: true,' : '') . '
                     speed: ' . ($Konfiguracja['czas_przejscia_efektu_animacji'] * 1000) . ', 
                     pauseOnHover: false,
                     pauseOnFocus: false,
                     slidesToShow: ' . ((count($Konfiguracja['filmy_youtube']) > (int)$Konfiguracja['ilosc_kolumn_youtube'][0]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_youtube'][0]['kolumny'] : count($Konfiguracja['filmy_youtube'])) . ',
                     slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_youtube'][0]['kolumny']) . ',                      
                     responsive: [';
                       
                          for ( $tr = 1; $tr < count($Konfiguracja['ilosc_kolumn_youtube']); $tr++ ) {
                            
                                echo '{ 
                                        breakpoint: ' . $Konfiguracja['ilosc_kolumn_youtube'][$tr - 1]['rozdzielczosc'] . ',
                                        settings: {
                                          slidesToShow: ' . ((count($Konfiguracja['filmy_youtube']) > (int)$Konfiguracja['ilosc_kolumn_youtube'][$tr]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_youtube'][$tr]['kolumny'] : count($Konfiguracja['filmy_youtube'])) . ',
                                          slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_youtube'][$tr]['kolumny']) . '
                                        }   
                                      },';
                                
                          }                         
                        
                     echo ']                     
                     });
                   });
                </script>';
          //
     }

     // jezeli sa najpierw wyswietlanie miniatury to po kliknieciu w miniature wyswietli film
     if ( $Konfiguracja['miniaturka_youtube'] != 'film' ) {
          //
          echo '<script>
                   $(document).ready(function() {          
                     $(\'.FilmFrame\').click(function(){
                        video = \'<iframe allowfullscreen="" frameborder="0" src="\' + $(this).attr(\'data-video\') + \'?autoplay=1"></iframe>\';
                        $(this).html(video);
                     });
                   });
                </script>';
          //
     }

} else {
 
     if ( isset($Konfiguracja['wyswietlana_kolumna']) && $Konfiguracja['wyswietlana_kolumna'] == 'tak' ) {
          //
          echo '<div class="Informacja">{__TLUMACZ:BRAK_DANYCH_DO_WYSWIETLENIA}</div>';
          //
     }
  
}
?>