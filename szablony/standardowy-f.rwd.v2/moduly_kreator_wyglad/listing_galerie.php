<?php
$TablicaGalerii = array();

// dodatkowy warunek dla grup klientow
$warunekTmp = " and (g.gallery_customers_group_id = '0'";
//
if ( isset($_SESSION['customers_groups_id']) && (int)$_SESSION['customers_groups_id'] > 0 ) {
    $warunekTmp .= " or find_in_set(" . (int)$_SESSION['customers_groups_id'] . ", g.gallery_customers_group_id)";
}
$warunekTmp .= ") "; 
//    
$zapytanie = "SELECT * FROM gallery g, gallery_description gd WHERE g.id_gallery = gd.id_gallery AND g.id_gallery = '" . (int)$Konfiguracja['grupa_galerii'] . "' AND g.gallery_status = 1 AND gd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'" . $warunekTmp;
$sql = $GLOBALS['db']->open_query($zapytanie);

if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 

    // wyszukiwanie poszczegolnych pozycji galerii
    $zapytanie_grafiki = "SELECT * FROM gallery_image WHERE id_gallery = '" . (int)$Konfiguracja['grupa_galerii'] . "' AND language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' ORDER BY " . (($Konfiguracja['sortowanie_galerii'] == 'sort') ? 'gallery_image_sort' : 'rand()') . " LIMIT " . (int)$Konfiguracja['ilosc_galerii'];
    $sql_grafiki = $GLOBALS['db']->open_query($zapytanie_grafiki);    
    
    while ( $infg = $sql_grafiki->fetch_assoc() ) {
        //
        $TablicaGalerii[] = array('grafika' => $infg['gallery_image'],
                                  'alt' => $infg['gallery_image_alt'],
                                  'opis' => $infg['gallery_image_description']);
        //
    }
    
    $GLOBALS['db']->close_query($sql_grafiki); 
    unset($infg, $zapytanie_grafiki); 

}

$GLOBALS['db']->close_query($sql); 
unset($warunekTmp, $zapytanie);

if ( count($TablicaGalerii) > 0 ) {
  
     // jezeli jest tylko jedna pozycja to przejmuje tryb statyczny
     if ( count($TablicaGalerii) == 1 ) {
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
     
     $IdGrupyGalerii = '';

     // jezeli wyswietlanie statyczne lub animowane
     if ( $Konfiguracja['sposob_wyswietlania'] == 'statyczny' ) {
          //
          $IdGrupyGalerii = 'Statyczny-' . ucfirst($Konfiguracja['kolumna']) . '-' . $Konfiguracja['id_modulu'];
          //
          echo '<div class="OknaFlexRwd ' . $IdGrupyGalerii . '">';
          //
     } else if ( $Konfiguracja['sposob_wyswietlania'] == 'animowany' ) {
          //
          $IdGrupyGalerii = 'Animacja-' . ucfirst($Konfiguracja['kolumna']) . '-' . $Konfiguracja['id_modulu'];
          //
          echo '<div class="' . $IdCssDodatkowyKontener . '"><div class="AnimacjaKreator ' . $IdGrupyGalerii . $IdCssDodatkowy . '">';
          //
     }   
     
     unset($IdCssDodatkowyKontener, $IdCssDodatkowy);
  
     // listing galerii
     foreach ( $TablicaGalerii as $Grafika ) {

          echo '<article class="GaleriaOkno' . (($Konfiguracja['sposob_wyswietlania'] == 'statyczny') ? ' OknoFlex' : '') . '">';
          
              echo '<div class="ElementOknoRamka">';
              
                  if ( file_exists(KATALOG_ZDJEC . '/' . $Grafika['grafika']) || $Grafika['grafika'] == '' ) {
              
                       echo '<a class="Galeria-' . $Konfiguracja['id_modulu'] . '" data-jbox-image="gallery' . $Konfiguracja['id_modulu'] . '" href="' . KATALOG_ZDJEC . '/' . $Grafika['grafika'] . '" title="' . $Grafika['alt'] . '">';
      
                            // jezeli wyswietlanie w oryginalnym rozmiarze
                            if ( $Konfiguracja['rozmiar_grafik_galerii'] == 'brak' ) {
                                 //
                                 $image_info = getimagesize(KATALOG_ZDJEC . '/' . $Grafika['grafika']);
                                 //
                                 echo '<span class="GrafikaGaleria"><img src="' . KATALOG_ZDJEC . '/' . $Grafika['grafika'] . '" alt="' . $Grafika['alt'] . '" width="'.$image_info['0'].'"  height="'.$image_info['1'].'" /></span>';
                                 //
                                 unset($image_info);
                                 //
                            } else {
                                 //
                                 echo '<span class="GrafikaGaleria">' . Funkcje::pokazObrazek($Grafika['grafika'], $Grafika['alt'], $Konfiguracja['rozmiar_grafik_galerii'], $Konfiguracja['rozmiar_grafik_galerii'], array(), '', 'maly', true, false, false) . '</span>';
                                 //
                            }

                            // czy wyswietlac opis galerii
                            if ( $Konfiguracja['opis_grafik_galerii'] == 'tak' ) {
                                 //
                                 echo '<span class="OpisGaleria">' . $Grafika['opis'] . '</span>';
                                 //
                            }
                  
                       echo '</a>';
                      
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
     
     echo '<script>
           $(document).ready(function() {
              $(\'.Galeria-' . $Konfiguracja['id_modulu'] . '\').jBox(\'Image\', {
                id: \'Galeria-' . $Konfiguracja['id_modulu'] . '\',
                imageSize: \'auto\',
                imageCounter: true,
                imageCounterSeparator: \' / \'
              });
           });
           </script>';

     // jezeli wyswietlanie statyczne
     if ( $Konfiguracja['sposob_wyswietlania'] == 'statyczny' ) {
          //
          $StylCss = '';
          //
          for ( $tr = 0; $tr < count($Konfiguracja['ilosc_kolumn_galerii']); $tr++ ) {
            
                $StylCss .= '@media only screen and (min-width:' . $Konfiguracja['ilosc_kolumn_galerii'][$tr]['rozdzielczosc'] . 'px)' . (($tr > 0) ? ' and (max-width:' . ($Konfiguracja['ilosc_kolumn_galerii'][$tr - 1]['rozdzielczosc'] - 1) . 'px)' : '') . ' { 
                              .' . $IdGrupyGalerii . ' article { width:calc(' . ((count($TablicaGalerii) > (int)$Konfiguracja['ilosc_kolumn_galerii'][$tr]['kolumny']) ? '(100% / '.(int)$Konfiguracja['ilosc_kolumn_galerii'][$tr]['kolumny'].') - ((('.(int)$Konfiguracja['ilosc_kolumn_galerii'][$tr]['kolumny'].' - 1) / '.(int)$Konfiguracja['ilosc_kolumn_galerii'][$tr]['kolumny'].') * var(--okna-odstep))' : '(100% / '.count($TablicaGalerii).') - ((('.count($TablicaGalerii).' - 1) / '.count($TablicaGalerii).') * var(--okna-odstep))') . ') }
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
                    $StylCss .= '.' . $IdGrupyGalerii . ' .slick-prev { position:fixed; left:initial; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px !important; }';
                    $StylCss .= '.' . $IdGrupyGalerii . ' .slick-next { position:fixed; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:0px !important; }';      
                    $StylCss .= '@media only screen and (max-width:779px) { .' . $IdGrupyGalerii . ' .slick-prev { position:fixed; left:initial; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 10) . 'px !important; }}';
                    //
               } else {
                    //
                    $StylCss .= '.' . $IdGrupyGalerii . ' .slick-list { margin:0px ' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px 0px ' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px; }';
                    $StylCss .= '.' . $IdGrupyGalerii . ' .slick-prev { left:0px; }';
                    $StylCss .= '.' . $IdGrupyGalerii . ' .slick-next { right:0px; }';
                    //
               }
               //
          }
          
          // sprawdzi czy nie przesunac strzalek jak sa kropki
          if ( $Konfiguracja['nawigacja_przyciski'] == 'tak' && $Konfiguracja['nawigacja_strzalki_polozenie'] == 'nie' ) {
               //
               $StylCss .= '.' . $IdGrupyGalerii . ' .slick-prev, .' . $IdGrupyGalerii . ' .slick-next { margin-top:-' . ( $Konfiguracja['nawigacja_przyciski_rozmiar'] / 2 ) . 'px; }';
               //
          }
               
          $StylCss .= '.' . $IdGrupyGalerii . ' { text-align:center; }    
                       .' . $IdGrupyGalerii . ' > .GaleriaOkno:not(:first-child) { display: none; }
                       ' . (($Konfiguracja['nawigacja_przyciski'] == 'tak') ? '.' . $IdGrupyGalerii . ' .slick-dots li:only-child { display: none; }' : '') . '
                       .' . $IdGrupyGalerii . ' .slick-prev, .' . $IdGrupyGalerii . ' .slick-next { width:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px; height:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px; }
                       .' . $IdGrupyGalerii . ' .slick-prev:before, .' . $IdGrupyGalerii . ' .slick-next:before { font-size:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px' . (($Konfiguracja['nawigacja_strzalki_kolor'] != '') ? '; color:#' . $Konfiguracja['nawigacja_strzalki_kolor'] : '') . '; }
                       .' . $IdGrupyGalerii . ' .slick-prev:before { content:"\\' . $Konfiguracja['nawigacja_strzalki_czcionka_wstecz'] . '" }
                       .' . $IdGrupyGalerii . ' .slick-next:before { content:"\\' . $Konfiguracja['nawigacja_strzalki_czcionka_naprzod'] . '" }
                       .' . $IdGrupyGalerii . ' .slick-dots li button:before { content:"\\' . $Konfiguracja['nawigacja_przyciski_czcionka'] . '"; font-size:' . $Konfiguracja['nawigacja_przyciski_rozmiar'] . 'px' . (($Konfiguracja['nawigacja_przyciski_kolor'] != '') ? '; color:#' . $Konfiguracja['nawigacja_przyciski_kolor'] : '') . '; }
                       ' . (($Konfiguracja['nawigacja_przyciski_kolor_aktywna'] != '') ? '.' . $IdGrupyGalerii . ' .slick-dots li.slick-active button:before { color:#' . $Konfiguracja['nawigacja_przyciski_kolor_aktywna'] . '; }' : '');
                
          $GLOBALS['css'] .= $StylCss;
          //
          unset($StylCss);
          //
          $NazwaFunkcjiJs = 'Przelicz_' . $Konfiguracja['id_modulu'] . '_' . $Konfiguracja['kolumna'] . '()';
          //
          echo '<script>
                   function ' . $NazwaFunkcjiJs . ' {
                      var max_wysokosc = 0;
                      $(\'.' . $IdGrupyGalerii . ' .slick-slide\').css({ \'height\' : \'auto\' });
                      $(\'.' . $IdGrupyGalerii . ' .slick-slide\').each(function() {
                          if ( $(this).outerHeight() > max_wysokosc ) {
                               max_wysokosc = $(this).outerHeight();
                          }
                      });
                      $(\'.' . $IdGrupyGalerii . ' .slick-slide\').css({ \'height\' : max_wysokosc });';
  
                      if ( $Konfiguracja['nawigacja_strzalki_polozenie'] == 'tak' && $Konfiguracja['nawigacja_strzalki'] == 'tak' ) {
                           //
                           if ( $Konfiguracja['nawigacja_strzalki_miejsce_wyswietlania'] == 'boki' ) {
                                //
                                echo 'if ($(\'.' . $IdGrupyGalerii . '\').find(\'.slick-arrow\').length === 0) { $(\'.' . $IdGrupyGalerii . ' .slick-list\').css({ \'margin\' : \'0\' }); }';
                                //
                           }
                           //
                      }
                      
                   echo '}
                   $(document).ready(function() {
                     $(\'.' . $IdGrupyGalerii . '\').on(\'setPosition\', function(event, slick, direction){ ' . $NazwaFunkcjiJs . ' }).slick({
                     infinite: true,
                     ' . (($Konfiguracja['nawigacja_przyciski'] == 'tak') ? 'dots: true,' : 'dots: false,') . '
                     ' . (($Konfiguracja['nawigacja_strzalki'] == 'tak') ? 'arrows: true,' : 'arrows: false,') . '
                     ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplay: true,' : '') . '
                     ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplaySpeed: ' . ($Konfiguracja['czas_zmiany_animacji'] * 1000) . ',' : '') . '
                     ' . (($Konfiguracja['sposob_animacji'] == 'przenikanie') ? 'fade: true,' : '') . '
                     speed: ' . ($Konfiguracja['czas_przejscia_efektu_animacji'] * 1000) . ', 
                     pauseOnHover: false,
                     pauseOnFocus: false,
                     slidesToShow: ' . ((count($TablicaGalerii) > (int)$Konfiguracja['ilosc_kolumn_galerii'][0]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_galerii'][0]['kolumny'] : count($TablicaGalerii)) . ',
                     slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_galerii'][0]['kolumny']) . ',                   
                     responsive: [';
                       
                          for ( $tr = 1; $tr < count($Konfiguracja['ilosc_kolumn_galerii']); $tr++ ) {
                            
                                echo '{ 
                                        breakpoint: ' . $Konfiguracja['ilosc_kolumn_galerii'][$tr - 1]['rozdzielczosc'] . ',
                                        settings: {
                                          slidesToShow: ' . ((count($TablicaGalerii) > (int)$Konfiguracja['ilosc_kolumn_galerii'][$tr]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_galerii'][$tr]['kolumny'] : count($TablicaGalerii)) . ',
                                          slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_galerii'][$tr]['kolumny']) . '
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

unset($TablicaGalerii);
?>