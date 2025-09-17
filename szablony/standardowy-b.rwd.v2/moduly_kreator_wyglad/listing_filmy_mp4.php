<?php

if ( count($Konfiguracja['filmy_filmmp4']) > 0 ) {
  
     // jezeli jest tylko jedna pozycja to przejmuje tryb statyczny
     if ( count($Konfiguracja['filmy_filmmp4']) == 1 ) {
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
     for ($v = 0, $cs = count($Konfiguracja['filmy_filmmp4']); $v < $cs; $v++) {

          echo '<article class="FilmMp4Okno' . (($Konfiguracja['sposob_wyswietlania'] == 'statyczny') ? ' OknoFlex' : '') . '">';
          
              echo '<div class="ElementOknoRamka">';

                  // szerokosc i wysokosc filmu
                  $szerokosc = (((int)$Konfiguracja['filmy_filmmp4'][$v]['szerokosc'] > 0) ? ';max-width:' . (int)$Konfiguracja['filmy_filmmp4'][$v]['szerokosc'] . 'px' : '');
                  $wysokosc = (((int)$Konfiguracja['filmy_filmmp4'][$v]['wysokosc'] > 0) ? ';max-height:' . (int)$Konfiguracja['filmy_filmmp4'][$v]['wysokosc'] . 'px' : '');
                  
                  // nawigacja
                  $nawigacja = (($Konfiguracja['przyciski_kontrolne'] == 'tak') ? ' controls' : ''); 
                  
                  // jezeli jest autoodtwarzanie na nie to ustawi nawigacje na te - bo inaczej jest pusto
                  if ( $Konfiguracja['autoodtwarzanie'] == 'nie' ) {
                       $nawigacja = ' controls';
                  }
                  
                  // autostart
                  $autostart = (($Konfiguracja['autoodtwarzanie'] == 'tak') ? ' autoplay' : '');

                  echo '<video style="width:100%;height:auto' . $szerokosc . $wysokosc . '"' . $nawigacja . $autostart . ' muted loop="true"><source src="' . KATALOG_ZDJEC . '/' . $Konfiguracja['filmy_filmmp4'][$v]['film'] . '" type="video/mp4"></video>';
                  
                  if ( !empty($Konfiguracja['filmy_filmmp4'][$v]['nazwa']) ) {
                       //
                       echo '<div class="FilmNazwa"><div>';
                       //
                       if ( !empty($Konfiguracja['filmy_filmmp4'][$v]['link']) ) {
                            //
                            echo '<a href="' . $Konfiguracja['filmy_filmmp4'][$v]['link'] . '">' ; 
                            //
                       }
                       //
                       echo $Konfiguracja['filmy_filmmp4'][$v]['nazwa'];
                       //
                       if ( !empty($Konfiguracja['filmy_filmmp4'][$v]['link']) ) {
                            //
                            echo '</a>' ; 
                            //
                       }
                       //
                       echo '</div></div>';
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
          for ( $tr = 0; $tr < count($Konfiguracja['ilosc_kolumn_filmmp4']); $tr++ ) {
            
                $StylCss .= '@media only screen and (min-width:' . $Konfiguracja['ilosc_kolumn_filmmp4'][$tr]['rozdzielczosc'] . 'px)' . (($tr > 0) ? ' and (max-width:' . ($Konfiguracja['ilosc_kolumn_filmmp4'][$tr - 1]['rozdzielczosc'] - 1) . 'px)' : '') . ' { 
                               .' . $IdGrupyFilmow . ' article { width:calc(' . ((count($Konfiguracja['filmy_filmmp4']) > (int)$Konfiguracja['ilosc_kolumn_filmmp4'][$tr]['kolumny']) ? '(100% / '.(int)$Konfiguracja['ilosc_kolumn_filmmp4'][$tr]['kolumny'].') - ((('.(int)$Konfiguracja['ilosc_kolumn_filmmp4'][$tr]['kolumny'].' - 1) / '.(int)$Konfiguracja['ilosc_kolumn_filmmp4'][$tr]['kolumny'].') * var(--okna-odstep))' : '(100% / '.count($Konfiguracja['filmy_filmmp4']).') - ((('.count($Konfiguracja['filmy_filmmp4']).' - 1) / '.count($Konfiguracja['filmy_filmmp4']).') * var(--okna-odstep))') . ') }
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
                     $(\'.' . $IdGrupyFilmow . '\').on(\'beforeChange\', function() { ' . (($Konfiguracja['autoodtwarzanie'] == 'tak') ? 'StartFilm()' : '') . ' }).slick({
                     infinite: true,
                     ' . (($Konfiguracja['nawigacja_przyciski'] == 'tak') ? 'dots: true,' : 'dots: false,') . '
                     ' . (($Konfiguracja['nawigacja_strzalki'] == 'tak') ? 'arrows: true,' : 'arrows: false,') . '
                     ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplay: true,' : '') . '
                     ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplaySpeed: ' . ($Konfiguracja['czas_zmiany_animacji'] * 1000) . ',' : '') . '                     
                     ' . (($Konfiguracja['sposob_animacji'] == 'przenikanie') ? 'fade: true,' : '') . '
                     speed: ' . ($Konfiguracja['czas_przejscia_efektu_animacji'] * 1000) . ', 
                     pauseOnHover: false,
                     pauseOnFocus: false,
                     slidesToShow: ' . ((count($Konfiguracja['filmy_filmmp4']) > (int)$Konfiguracja['ilosc_kolumn_filmmp4'][0]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_filmmp4'][0]['kolumny'] : count($Konfiguracja['filmy_filmmp4'])) . ',
                     slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_filmmp4'][0]['kolumny']) . ',                      
                     responsive: [';
                       
                          for ( $tr = 1; $tr < count($Konfiguracja['ilosc_kolumn_filmmp4']); $tr++ ) {
                            
                                echo '{ 
                                        breakpoint: ' . $Konfiguracja['ilosc_kolumn_filmmp4'][$tr - 1]['rozdzielczosc'] . ',
                                        settings: {
                                          slidesToShow: ' . ((count($Konfiguracja['filmy_filmmp4']) > (int)$Konfiguracja['ilosc_kolumn_filmmp4'][$tr]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_filmmp4'][$tr]['kolumny'] : count($Konfiguracja['filmy_filmmp4'])) . ',
                                          slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_filmmp4'][$tr]['kolumny']) . '
                                        }   
                                      },';
                                
                          }                         
                        
                     echo ']                     
                     });
                   });
                </script>';
          //
     }

     // jezeli jest ustawione autoodtwarzanie
     if ( $Konfiguracja['autoodtwarzanie'] == 'tak' ) {
       
          echo "<script>
                   $(document).ready(function() {                   
                     video = $('." . $IdGrupyFilmow . " .slick-actived video');
                     video.each(function(index) {
                        this.play();
                     });
                   });

                   function StartFilm() {
                     video = $('." . $IdGrupyFilmow . " article video');
                     video.each(function() {
                        this.pause();
                        this.currentTime = 0;
                     });
                     video = $('." . $IdGrupyFilmow . " article video');
                     video.each(function() {
                        this.play();
                     });
                   }
                </script>";
    
     }

} else {
 
     if ( isset($Konfiguracja['wyswietlana_kolumna']) && $Konfiguracja['wyswietlana_kolumna'] == 'tak' ) {
          //
          echo '<div class="Informacja">{__TLUMACZ:BRAK_DANYCH_DO_WYSWIETLENIA}</div>';
          //
     }
  
}
?>