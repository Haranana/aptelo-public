<?php
// zakres kategorii
$IdKategorie = array();
//
if ( $Konfiguracja['zakres_kategorie'] == 'wszystkie' ) {
    //
    foreach ($GLOBALS['tablicaKategorii'] as $IdKat => $KategoriaWartosc) {
         //
         if ( $KategoriaWartosc['Parent'] == 0 && $KategoriaWartosc['Widocznosc'] == '1' ) {
              //
              $IdKategorie[$IdKat] = $KategoriaWartosc;
              //
         }
         //
    }
    //
} else {
    //
    foreach ($GLOBALS['tablicaKategorii'] as $IdKat => $KategoriaWartosc) {
         //
         if ( $KategoriaWartosc['Widocznosc'] == '1' && in_array($IdKat, $Konfiguracja['id_zakres_kategorii']) ) {
              //
              $IdKategorie[$IdKat] = $KategoriaWartosc;
              //
         }
         //
    }
    //
}

if ( count($IdKategorie) > 0 ) {

     // jezeli jest tylko jedna pozycja to przejmuje tryb statyczny
     if ( count($IdKategorie) == 1 ) {
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
     
     $IdGrupyKategorii = '';

     // jezeli wyswietlanie statyczne lub animowane
     if ( $Konfiguracja['sposob_wyswietlania'] == 'statyczny' ) {
          //
          $IdGrupyKategorii = 'Statyczny-' . ucfirst($Konfiguracja['kolumna']) . '-' . $Konfiguracja['id_modulu'];
          //
          echo '<div class="OknaFlexRwd ' . $IdGrupyKategorii . '">';
          //
     } else if ( $Konfiguracja['sposob_wyswietlania'] == 'animowany' ) {
          //
          $IdGrupyKategorii = 'Animacja-' . ucfirst($Konfiguracja['kolumna']) . '-' . $Konfiguracja['id_modulu'];
          //
          echo '<div class="' . $IdCssDodatkowyKontener . '"><div class="AnimacjaKreator ' . $IdGrupyKategorii . $IdCssDodatkowy . '">';
          //
     }   
     
     unset($IdCssDodatkowyKontener, $IdCssDodatkowy);

     // listing kategorii
     foreach ($IdKategorie as $IdKat => $KategoriaWartosc) {

        echo '<article class="KategoriaOkno' . (($Konfiguracja['sposob_wyswietlania'] == 'statyczny') ? ' OknoFlex' : '') . '">';
        
            echo '<div class="ElementOknoRamka">';

                echo '<a href="' . Seo::link_SEO($KategoriaWartosc['NazwaSeo'], (($Konfiguracja['zakres_kategorie'] == 'wszystkie') ? $KategoriaWartosc['IdKat'] : Kategorie::SciezkaKategoriiId($KategoriaWartosc['IdKat'])), 'kategoria') . '">';
                
                // czy wyswietlac grafike kategorii
                if ( $Konfiguracja['grafika_kategorii'] != 'brak' ) {
                  
                     if ( $Konfiguracja['grafika_kategorii'] == 'ikonka' ) {
                          //
                          $GrafikaKategorii = $KategoriaWartosc['Ikona'];
                          //
                     } else {
                          //
                          $GrafikaKategorii = $KategoriaWartosc['Foto'];
                          //
                     }
                     
                     if ( file_exists(KATALOG_ZDJEC . '/' . $GrafikaKategorii) && $GrafikaKategorii != '' ) {
                       
                          // jezeli wyswietlanie w oryginalnym rozmiarze
                          if ( $Konfiguracja['grafika_rozmiar_kategorii'] == 'brak' ) {
                               //
                               list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $GrafikaKategorii);
                               //
                               echo '<span class="GrafikaKategoria"><img src="' . KATALOG_ZDJEC . '/' . $GrafikaKategorii . '" width="' . $szerokosc . '" height="' . $wysokosc . '" alt="' . $KategoriaWartosc['Nazwa'] . '" title="' . $KategoriaWartosc['Nazwa'] . '" /></span>';
                               //
                          } else {
                               //
                               echo '<span class="GrafikaKategoria">' . Funkcje::pokazObrazek($GrafikaKategorii, $KategoriaWartosc['Nazwa'], $Konfiguracja['grafika_rozmiar_kategorii'], $Konfiguracja['grafika_rozmiar_kategorii'], array(), '', 'maly', true, false, false) . '</span>';
                               //
                          }
                          
                     }
                     
                     unset($GrafikaKategorii);
                
                }
                
                // czy wyswietlac nazwe kategorii
                if ( $Konfiguracja['nazwa_kategorii'] == 'tak' ) {
                     //
                     echo '<span class="KategoriaWyrownanie NazwaKategoria">' . $KategoriaWartosc['Nazwa'] . '</span>';
                     //
                }
                
                echo '</a>';
                
                if ( $Konfiguracja['podkategorie_kategorii'] == 'tak' ) {
                
                     // lista podkategorii
                     
                     $PodkategorieTablica = Kategorie::TablicaKategorieParent($KategoriaWartosc['IdKat']);
                     
                     if ( count($PodkategorieTablica) > 0 ) {
                                   
                          echo '<ul class="KategoriaWyrownanie">';
                          
                          $LicznikPodkategorii = 1;
                          
                          foreach($PodkategorieTablica as $TablicaPodkategorii) {
                              //
                              if ( $LicznikPodkategorii <= $Konfiguracja['ilosc_podkategorii'] ) {
                                   //
                                   echo '<li><a href="' . Seo::link_SEO($TablicaPodkategorii['seo'], Kategorie::SciezkaKategoriiId($TablicaPodkategorii['id']), 'kategoria') . '">' . $TablicaPodkategorii['text'] . '</a></li>';
                                   //
                              }
                              
                              $LicznikPodkategorii++;
                              //
                          }
                          
                          echo '</ul>';    

                          unset($LicznikPodkategorii);

                    }
                    
                    unset($PodkategorieTablica);
                    
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
          $StylCss = '.' . $IdGrupyKategorii . ' { justify-content:center; }';
          //
          // wyrownanie kategorii w wierszu
          if ( $Konfiguracja['wyrownanie_podkategorii'] == 'srodek' ) {
               $StylCss .= '.' . $IdGrupyKategorii . ' .KategoriaWyrownanie { text-align:center; }';
          }
          if ( $Konfiguracja['wyrownanie_podkategorii'] == 'lewa' ) {
               $StylCss .= '.' . $IdGrupyKategorii . ' .KategoriaWyrownanie { text-align:left; }';
          }
          if ( $Konfiguracja['wyrownanie_podkategorii'] == 'prawa' ) {
               $StylCss .= '.' . $IdGrupyKategorii . ' .KategoriaWyrownanie { text-align:right; }';
          }          
          //
          for ( $tr = 0; $tr < count($Konfiguracja['ilosc_kolumn_kategorii']); $tr++ ) {
            
                $StylCss .= '@media only screen and (min-width:' . $Konfiguracja['ilosc_kolumn_kategorii'][$tr]['rozdzielczosc'] . 'px)' . (($tr > 0) ? ' and (max-width:' . ($Konfiguracja['ilosc_kolumn_kategorii'][$tr - 1]['rozdzielczosc'] - 1) . 'px)' : '') . ' { 
                                 .' . $IdGrupyKategorii . ' article { width:calc(' . ((count($IdKategorie) > (int)$Konfiguracja['ilosc_kolumn_kategorii'][$tr]['kolumny']) ? '(100% / '.(int)$Konfiguracja['ilosc_kolumn_kategorii'][$tr]['kolumny'].') - ((('.(int)$Konfiguracja['ilosc_kolumn_kategorii'][$tr]['kolumny'].' - 1) / '.(int)$Konfiguracja['ilosc_kolumn_kategorii'][$tr]['kolumny'].') * var(--okna-odstep))' : '(100% / '.count($IdKategorie).') - ((('.count($IdKategorie).' - 1) / '.count($IdKategorie).') * var(--okna-odstep))') . ') }
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
                    $StylCss .= '.' . $IdGrupyKategorii . ' .slick-prev { position:fixed; left:initial; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px !important; }';
                    $StylCss .= '.' . $IdGrupyKategorii . ' .slick-next { position:fixed; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:0px !important; }';      
                    $StylCss .= '@media only screen and (max-width:779px) { .' . $IdGrupyKategorii . ' .slick-prev { position:fixed; left:initial; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 10) . 'px !important; }}';
                    //
               } else {
                    //
                    $StylCss .= '.' . $IdGrupyKategorii . ' .slick-list { margin:0px ' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px 0px ' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px; }';
                    $StylCss .= '.' . $IdGrupyKategorii . ' .slick-prev { left:0px; }';
                    $StylCss .= '.' . $IdGrupyKategorii . ' .slick-next { right:0px; }';
                    //
               }
               //
          }
          
          // sprawdzi czy nie przesunac strzalek jak sa kropki
          if ( $Konfiguracja['nawigacja_przyciski'] == 'tak' && $Konfiguracja['nawigacja_strzalki_polozenie'] == 'nie' ) {
               //
               $StylCss .= '.' . $IdGrupyKategorii . ' .slick-prev, .' . $IdGrupyKategorii . ' .slick-next { margin-top:-' . ( $Konfiguracja['nawigacja_przyciski_rozmiar'] / 2 ) . 'px; }';
               //
          }                  
          
          // wyrownanie kategorii w wierszu
          if ( $Konfiguracja['wyrownanie_podkategorii'] == 'srodek' ) {
               $StylCss .= '.' . $IdGrupyKategorii . ' .KategoriaWyrownanie { text-align:center; }';
          }
          if ( $Konfiguracja['wyrownanie_podkategorii'] == 'lewa' ) {
               $StylCss .= '.' . $IdGrupyKategorii . ' .KategoriaWyrownanie { text-align:left; }';
          }
          if ( $Konfiguracja['wyrownanie_podkategorii'] == 'prawa' ) {
               $StylCss .= '.' . $IdGrupyKategorii . ' .KategoriaWyrownanie { text-align:right; }';
          }                         
                
          $StylCss .= '.' . $IdGrupyKategorii . ' { text-align:center; }        
                       .' . $IdGrupyKategorii . ' > .KategoriaOkno:not(:first-child) { display: none; }
                       ' . (($Konfiguracja['nawigacja_przyciski'] == 'tak') ? '.' . $IdGrupyKategorii . ' .slick-dots li:only-child { display: none; }' : '') . '
                       .' . $IdGrupyKategorii . ' .slick-prev, .' . $IdGrupyKategorii . ' .slick-next { width:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px; height:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px; }
                       .' . $IdGrupyKategorii . ' .slick-prev:before, .' . $IdGrupyKategorii . ' .slick-next:before { font-size:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px' . (($Konfiguracja['nawigacja_strzalki_kolor'] != '') ? '; color:#' . $Konfiguracja['nawigacja_strzalki_kolor'] : '') . '; }
                       .' . $IdGrupyKategorii . ' .slick-prev:before { content:"\\' . $Konfiguracja['nawigacja_strzalki_czcionka_wstecz'] . '" }
                       .' . $IdGrupyKategorii . ' .slick-next:before { content:"\\' . $Konfiguracja['nawigacja_strzalki_czcionka_naprzod'] . '" }
                       .' . $IdGrupyKategorii . ' .slick-dots li button:before { content:"\\' . $Konfiguracja['nawigacja_przyciski_czcionka'] . '"; font-size:' . $Konfiguracja['nawigacja_przyciski_rozmiar'] . 'px' . (($Konfiguracja['nawigacja_przyciski_kolor'] != '') ? '; color:#' . $Konfiguracja['nawigacja_przyciski_kolor'] : '') . '; }
                       ' . (($Konfiguracja['nawigacja_przyciski_kolor_aktywna'] != '') ? '.' . $IdGrupyKategorii . ' .slick-dots li.slick-active button:before { color:#' . $Konfiguracja['nawigacja_przyciski_kolor_aktywna'] . '; }' : '');
                      
          $GLOBALS['css'] .= $StylCss;
          //
          unset($StylCss);
          //
          $NazwaFunkcjiJs = 'Przelicz_' . $Konfiguracja['id_modulu'] . '_' . $Konfiguracja['kolumna'] . '()';
          //
          echo '<script>
                   function ' . $NazwaFunkcjiJs . ' {
                      var max_wysokosc = 0;
                      $(\'.' . $IdGrupyKategorii . ' .slick-slide\').css({ \'height\' : \'auto\' });
                      $(\'.' . $IdGrupyKategorii . ' .slick-slide\').each(function() {
                          if ( $(this).outerHeight() > max_wysokosc ) {
                               max_wysokosc = $(this).outerHeight();
                          }
                      });
                      $(\'.' . $IdGrupyKategorii . ' .slick-slide\').css({ \'height\' : max_wysokosc });';
  
                      if ( $Konfiguracja['nawigacja_strzalki_polozenie'] == 'tak' && $Konfiguracja['nawigacja_strzalki'] == 'tak' ) {
                           //
                           if ( $Konfiguracja['nawigacja_strzalki_miejsce_wyswietlania'] == 'boki' ) {
                                //
                                echo 'if ($(\'.' . $IdGrupyKategorii . '\').find(\'.slick-arrow\').length === 0) { $(\'.' . $IdGrupyKategorii . ' .slick-list\').css({ \'margin\' : \'0\' }); }';
                                //
                           }
                           //
                      }
                      
                   echo '}
                   $(document).ready(function() {
                     $(\'.' . $IdGrupyKategorii . '\').on(\'setPosition\', function(event, slick, direction){ ' . $NazwaFunkcjiJs . ' }).slick({
                     infinite: true,
                     ' . (($Konfiguracja['nawigacja_przyciski'] == 'tak') ? 'dots: true,' : 'dots: false,') . '
                     ' . (($Konfiguracja['nawigacja_strzalki'] == 'tak') ? 'arrows: true,' : 'arrows: false,') . '
                     ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplay: true,' : '') . '
                     ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplaySpeed: ' . ($Konfiguracja['czas_zmiany_animacji'] * 1000) . ',' : '') . '
                     ' . (($Konfiguracja['sposob_animacji'] == 'przenikanie') ? 'fade: true,' : '') . '
                     speed: ' . ($Konfiguracja['czas_przejscia_efektu_animacji'] * 1000) . ', 
                     pauseOnHover: false,
                     pauseOnFocus: false,
                     slidesToShow: ' . ((count($IdKategorie) > (int)$Konfiguracja['ilosc_kolumn_kategorii'][0]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_kategorii'][0]['kolumny'] : count($IdKategorie)) . ',
                     slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_kategorii'][0]['kolumny']) . ',                     
                     responsive: [';
                       
                          for ( $tr = 1; $tr < count($Konfiguracja['ilosc_kolumn_kategorii']); $tr++ ) {
                            
                                echo '{ 
                                        breakpoint: ' . $Konfiguracja['ilosc_kolumn_kategorii'][$tr - 1]['rozdzielczosc'] . ',
                                        settings: {
                                          slidesToShow: ' . ((count($IdKategorie) > (int)$Konfiguracja['ilosc_kolumn_kategorii'][$tr]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_kategorii'][$tr]['kolumny'] : count($IdKategorie)) . ',
                                          slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_kategorii'][$tr]['kolumny']) . '
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

unset($IdKategorie);
?>