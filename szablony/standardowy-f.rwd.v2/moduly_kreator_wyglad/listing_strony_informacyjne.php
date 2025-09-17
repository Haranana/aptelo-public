<?php

$TablicaStronInfo = array();

$TablicaStronInfoTmp = StronyInformacyjne::TablicaStronInfoGrupa( $Konfiguracja['grupa_stron_info'] );

if ( count($TablicaStronInfoTmp) > 0 ) {
     //
     foreach ( $TablicaStronInfoTmp as $Tmp ) {
         //
         if ( empty($Tmp['url']) ) {
              //
              $TablicaStronInfo[] = $Tmp;
              //
         }
         //
     }
     //
     if ( count($TablicaStronInfo) > 0 ) {
          //
          $TablicaStronInfo = Funkcje::wylosujElementyTablicyJakoTablica($TablicaStronInfo, $Konfiguracja['ilosc_stron_info']);
          //
     }
     //
}

if ( count($TablicaStronInfo) > 0 ) {
  
     // jezeli jest tylko jedna pozycja to przejmuje tryb statyczny
     if ( count($TablicaStronInfo) == 1 ) {
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
     
     $IdGrupyStronInfo = '';

     // jezeli wyswietlanie statyczne lub animowane
     if ( $Konfiguracja['sposob_wyswietlania'] == 'statyczny' ) {
          //
          $IdGrupyStronInfo = 'Statyczny-' . ucfirst($Konfiguracja['kolumna']) . '-' . $Konfiguracja['id_modulu'];
          //
          echo '<div class="OknaFlexRwd ' . $IdGrupyStronInfo . '">';
          //
     } else if ( $Konfiguracja['sposob_wyswietlania'] == 'animowany' ) {
          //
          $IdGrupyStronInfo = 'Animacja-' . ucfirst($Konfiguracja['kolumna']) . '-' . $Konfiguracja['id_modulu'];
          //
          echo '<div class="' . $IdCssDodatkowyKontener . '"><div class="AnimacjaKreator ' . $IdGrupyStronInfo . $IdCssDodatkowy . '">';
          //
     }   
     
     unset($IdCssDodatkowyKontener, $IdCssDodatkowy);
  
     // listing stron info
     foreach ( $TablicaStronInfo as $StronaInfo ) {

          echo '<article class="ArtykulOkno StronaInfoOkno' . (($Konfiguracja['sposob_wyswietlania'] == 'statyczny') ? ' OknoFlex' : '') . '">';
          
              echo '<div class="ElementOknoRamka">';
              
                  if ( $Konfiguracja['foto_strony'] == 'tak' && $StronaInfo['foto_strony'] != '' ) {
                       //
                       if ( file_exists(KATALOG_ZDJEC . '/' . $StronaInfo['foto_strony']) ) {
                            //
                            list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $StronaInfo['foto_strony']);
                            //
                            echo '<div class="FotoArtykulu">';
                           
                               echo '<a href="' . $StronaInfo['seo'] . '"><img src="' . KATALOG_ZDJEC . '/' . $StronaInfo['foto_strony'] . '" alt="' .  $StronaInfo['tytul'] . '" title="' .  $StronaInfo['tytul'] . '" ' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' /></a>';
                               
                            echo '</div>';
                            //       
                       }
                       //
                  }                

                  echo '<h3>' . $StronaInfo['link'] . '</h3>';
                  
                  echo '<div class="OpisArtykul OpisStronaInfo">' . $StronaInfo['opis_krotki'] . '</div>';

                  // czy pokazywac przycisk szczegolow 
                  if ( strlen((string)$StronaInfo['opis']) > 10 ) {
                       //
                       echo '<div class="cl"></div><div class="LinkCalyArtykul LinkCalyArtykuStronaInfo"><a href="' . $StronaInfo['seo'] . '" class="przycisk">{__TLUMACZ:PRZYCISK_PRZECZYTAJ_CALOSC}</a></div>';
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
          for ( $tr = 0; $tr < count($Konfiguracja['ilosc_kolumn_stron_info']); $tr++ ) {
            
                $StylCss .= '@media only screen and (min-width:' . $Konfiguracja['ilosc_kolumn_stron_info'][$tr]['rozdzielczosc'] . 'px)' . (($tr > 0) ? ' and (max-width:' . ($Konfiguracja['ilosc_kolumn_stron_info'][$tr - 1]['rozdzielczosc'] - 1) . 'px)' : '') . ' { 
                              .' . $IdGrupyStronInfo . ' article { width:calc(' . ((count($TablicaStronInfo) > (int)$Konfiguracja['ilosc_kolumn_stron_info'][$tr]['kolumny']) ? '(100% / '.(int)$Konfiguracja['ilosc_kolumn_stron_info'][$tr]['kolumny'].') - ((('.(int)$Konfiguracja['ilosc_kolumn_stron_info'][$tr]['kolumny'].' - 1) / '.(int)$Konfiguracja['ilosc_kolumn_stron_info'][$tr]['kolumny'].') * var(--okna-odstep))' : '(100% / '.count($TablicaStronInfo).') - ((('.count($TablicaStronInfo).' - 1) / '.count($TablicaStronInfo).') * var(--okna-odstep))') . ') }

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
                    $StylCss .= '.' . $IdGrupyStronInfo . ' .slick-prev { position:fixed; left:initial; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px !important; }';
                    $StylCss .= '.' . $IdGrupyStronInfo . ' .slick-next { position:fixed; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:0px !important; }';      
                    $StylCss .= '@media only screen and (max-width:779px) { .' . $IdGrupyStronInfo . ' .slick-prev { position:fixed; left:initial; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 10) . 'px !important; }}';
                    //
               } else {
                    //
                    $StylCss .= '.' . $IdGrupyStronInfo . ' .slick-list { margin:0px ' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px 0px ' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px; }';
                    $StylCss .= '.' . $IdGrupyStronInfo . ' .slick-prev { left:0px; }';
                    $StylCss .= '.' . $IdGrupyStronInfo . ' .slick-next { right:0px; }';
                    //
               }
               //
          }
          
          // sprawdzi czy nie przesunac strzalek jak sa kropki
          if ( $Konfiguracja['nawigacja_przyciski'] == 'tak' && $Konfiguracja['nawigacja_strzalki_polozenie'] == 'nie' ) {
               //
               $StylCss .= '.' . $IdGrupyStronInfo . ' .slick-prev, .' . $IdGrupyStronInfo . ' .slick-next { margin-top:-' . ( $Konfiguracja['nawigacja_przyciski_rozmiar'] / 2 ) . 'px; }';
               //
          }                
          
          $StylCss .= '.' . $IdGrupyStronInfo . ' { text-align:center; }          
                       .' . $IdGrupyStronInfo . ' > .ArtykulOkno:not(:first-child) { display: none; }
                       ' . (($Konfiguracja['nawigacja_przyciski'] == 'tak') ? '.' . $IdGrupyStronInfo . ' .slick-dots li:only-child { display: none; }' : '') . '
                       .' . $IdGrupyStronInfo . ' .slick-prev, .' . $IdGrupyStronInfo . ' .slick-next { width:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px; height:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px; }
                       .' . $IdGrupyStronInfo . ' .slick-prev:before, .' . $IdGrupyStronInfo . ' .slick-next:before { font-size:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px' . (($Konfiguracja['nawigacja_strzalki_kolor'] != '') ? '; color:#' . $Konfiguracja['nawigacja_strzalki_kolor'] : '') . '; }
                       .' . $IdGrupyStronInfo . ' .slick-prev:before { content:"\\' . $Konfiguracja['nawigacja_strzalki_czcionka_wstecz'] . '" }
                       .' . $IdGrupyStronInfo . ' .slick-next:before { content:"\\' . $Konfiguracja['nawigacja_strzalki_czcionka_naprzod'] . '" }
                       .' . $IdGrupyStronInfo . ' .slick-dots li button:before { content:"\\' . $Konfiguracja['nawigacja_przyciski_czcionka'] . '"; font-size:' . $Konfiguracja['nawigacja_przyciski_rozmiar'] . 'px' . (($Konfiguracja['nawigacja_przyciski_kolor'] != '') ? '; color:#' . $Konfiguracja['nawigacja_przyciski_kolor'] : '') . '; }
                       ' . (($Konfiguracja['nawigacja_przyciski_kolor_aktywna'] != '') ? '.' . $IdGrupyStronInfo . ' .slick-dots li.slick-active button:before { color:#' . $Konfiguracja['nawigacja_przyciski_kolor_aktywna'] . '; }' : '');
                       
          $GLOBALS['css'] .= $StylCss;
          //
          unset($StylCss);
          //
          $NazwaFunkcjiJs = 'Przelicz_' . $Konfiguracja['id_modulu'] . '_' . $Konfiguracja['kolumna'] . '()';
          //
          echo '<script>
                   function ' . $NazwaFunkcjiJs . ' {
                      var max_wysokosc = 0;
                      $(\'.' . $IdGrupyStronInfo . ' .slick-slide\').css({ \'height\' : \'auto\' });
                      $(\'.' . $IdGrupyStronInfo . ' .slick-slide\').each(function() {
                          if ( $(this).outerHeight() > max_wysokosc ) {
                               max_wysokosc = $(this).outerHeight();
                          }
                      });
                      $(\'.' . $IdGrupyStronInfo . ' .slick-slide\').css({ \'height\' : max_wysokosc });';
  
                      if ( $Konfiguracja['nawigacja_strzalki_polozenie'] == 'tak' && $Konfiguracja['nawigacja_strzalki'] == 'tak' ) {
                           //
                           if ( $Konfiguracja['nawigacja_strzalki_miejsce_wyswietlania'] == 'boki' ) {
                                //
                                echo 'if ($(\'.' . $IdGrupyStronInfo . '\').find(\'.slick-arrow\').length === 0) { $(\'.' . $IdGrupyStronInfo . ' .slick-list\').css({ \'margin\' : \'0\' }); }';
                                //
                           }
                           //
                      }
                      
                   echo '}
                   $(document).ready(function() {
                     $(\'.' . $IdGrupyStronInfo . '\').on(\'setPosition\', function(event, slick, direction){ ' . $NazwaFunkcjiJs . ' }).slick({
                     infinite: true,
                     ' . (($Konfiguracja['nawigacja_przyciski'] == 'tak') ? 'dots: true,' : 'dots: false,') . '
                     ' . (($Konfiguracja['nawigacja_strzalki'] == 'tak') ? 'arrows: true,' : 'arrows: false,') . '
                     ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplay: true,' : '') . '
                     ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplaySpeed: ' . ($Konfiguracja['czas_zmiany_animacji'] * 1000) . ',' : '') . '
                     ' . (($Konfiguracja['sposob_animacji'] == 'przenikanie') ? 'fade: true,' : '') . '
                     speed: ' . ($Konfiguracja['czas_przejscia_efektu_animacji'] * 1000) . ', 
                     pauseOnHover: false,
                     pauseOnFocus: false,
                     slidesToShow: ' . ((count($TablicaStronInfo) > (int)$Konfiguracja['ilosc_kolumn_stron_info'][0]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_stron_info'][0]['kolumny'] : count($TablicaStronInfo)) . ',
                     slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_stron_info'][0]['kolumny']) . ',                        
                     responsive: [';
                       
                          for ( $tr = 1; $tr < count($Konfiguracja['ilosc_kolumn_stron_info']); $tr++ ) {
                            
                                echo '{ 
                                        breakpoint: ' . $Konfiguracja['ilosc_kolumn_stron_info'][$tr - 1]['rozdzielczosc'] . ',
                                        settings: {
                                          slidesToShow: ' . ((count($TablicaStronInfo) > (int)$Konfiguracja['ilosc_kolumn_stron_info'][$tr]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_stron_info'][$tr]['kolumny'] : count($TablicaStronInfo)) . ',
                                          slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_stron_info'][$tr]['kolumny']) . '
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

unset($TablicaStronInfo);
?>