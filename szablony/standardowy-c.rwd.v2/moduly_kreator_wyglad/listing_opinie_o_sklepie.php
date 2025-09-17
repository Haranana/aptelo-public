<?php
switch ($Konfiguracja['sortowanie_opinii']) {
    case "losowo":
        $sortowanie = ' ORDER BY rand() LIMIT ' . $Konfiguracja['ilosc_opinii'];
        break;
    case "data":
        $sortowanie = ' ORDER BY r.date_added DESC LIMIT ' . $Konfiguracja['ilosc_opinii'];
        break;
} 

$sqlo = $GLOBALS['db']->open_query("SELECT * FROM reviews_shop r WHERE approved = '1'" . $sortowanie);

$IloscProduktow = (int)$GLOBALS['db']->ile_rekordow($sqlo);
$TablicaOpinii = array();

if ( $GLOBALS['db']->ile_rekordow($sqlo) > 0 ) {
     //
     while ( $infc = $sqlo->fetch_assoc() ) {
             //
             $TablicaOpinii[] = $infc;
             //           
     }
     //
}

$GLOBALS['db']->close_query($sqlo); 

if ( count($TablicaOpinii) > 0 ) {
  
     // jezeli jest tylko jedna pozycja to przejmuje tryb statyczny
     if ( count($TablicaOpinii) == 1 ) {
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
     
     $IdGrupyOpinii = '';

     // jezeli wyswietlanie statyczne lub animowane
     if ( $Konfiguracja['sposob_wyswietlania'] == 'statyczny' ) {
          //
          $IdGrupyOpinii = 'Statyczny-' . ucfirst($Konfiguracja['kolumna']) . '-' . $Konfiguracja['id_modulu'];
          //
          echo '<div class="OknaFlexRwd ' . $IdGrupyOpinii . '">';
          //
     } else if ( $Konfiguracja['sposob_wyswietlania'] == 'animowany' ) {
          //
          $IdGrupyOpinii = 'Animacja-' . ucfirst($Konfiguracja['kolumna']) . '-' . $Konfiguracja['id_modulu'];
          //
          echo '<div class="' . $IdCssDodatkowyKontener . '"><div class="AnimacjaKreator ' . $IdGrupyOpinii . $IdCssDodatkowy . '">';
          //
     }   
     
     unset($IdCssDodatkowyKontener, $IdCssDodatkowy);
  
     // listing opinii
     for ($v = 0, $cs = count($TablicaOpinii); $v < $cs; $v++) {

          echo '<article class="OpiniaOkno' . (($Konfiguracja['sposob_wyswietlania'] == 'statyczny') ? ' OknoFlex' : '') . '">';
          
              echo '<div class="ElementOknoRamka">';

                  echo '<ul class="Ocena">
                  
                            <li class="Autor">{__TLUMACZ:AUTOR_OPINII}: <span>' . $TablicaOpinii[$v]['customers_name'] . '</span></li>
                            <li class="DataNapisania">{__TLUMACZ:DATA_NAPISANIA_RECENZJI}: <span>' . date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($TablicaOpinii[$v]['date_added'])) . '</span></li>
                            <li class="OpisText">' . Funkcje::przytnijTekst(strip_tags((string)$TablicaOpinii[$v]['comments']), 100) . '</li>
                            
                        </ul>
                        
                        <ul class="OcenyGwiazdki">
                        
                            <li><b>{__TLUMACZ:OCENA_JAKOSC_OBSLUGI}</b><span class="Gwiazdki" id="radio_' .  uniqid() . '" style="--ocena: ' . $TablicaOpinii[$v]['handling_rating'] . ';"></span></li>
                            <li><b>{__TLUMACZ:OCENA_CZAS_REALIZACJI}</b><span class="Gwiazdki" id="radio_' .  uniqid() . '" style="--ocena: ' . $TablicaOpinii[$v]['lead_time_rating'] . ';"></span></li>
                            <li><b>{__TLUMACZ:OCENA_CENY}</b><span class="Gwiazdki" id="radio_' .  uniqid() . '" style="--ocena: ' . $TablicaOpinii[$v]['price_rating'] . ';"></span></li>
                            <li><b>{__TLUMACZ:OCENA_PRODUKTOW}</b><span class="Gwiazdki" id="radio_' .  uniqid() . '" style="--ocena: ' . $TablicaOpinii[$v]['quality_products_rating'] . ';"></span></li>
                            
                        </ul>';                          

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
          for ( $tr = 0; $tr < count($Konfiguracja['ilosc_kolumn_opinii']); $tr++ ) {
            
                $StylCss .= '@media only screen and (min-width:' . $Konfiguracja['ilosc_kolumn_opinii'][$tr]['rozdzielczosc'] . 'px)' . (($tr > 0) ? ' and (max-width:' . ($Konfiguracja['ilosc_kolumn_opinii'][$tr - 1]['rozdzielczosc'] - 1) . 'px)' : '') . ' { 
                         .' . $IdGrupyOpinii . ' article { width:calc(' . ((count($TablicaOpinii) > (int)$Konfiguracja['ilosc_kolumn_opinii'][$tr]['kolumny']) ? '(100% / '.(int)$Konfiguracja['ilosc_kolumn_opinii'][$tr]['kolumny'].') - ((('.(int)$Konfiguracja['ilosc_kolumn_opinii'][$tr]['kolumny'].' - 1) / '.(int)$Konfiguracja['ilosc_kolumn_opinii'][$tr]['kolumny'].') * var(--okna-odstep))' : '(100% / '.count($TablicaOpinii).') - ((('.count($TablicaOpinii).' - 1) / '.count($TablicaOpinii).') * var(--okna-odstep))') . ') }
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
                    $StylCss .= '.' . $IdGrupyOpinii . ' .slick-prev { position:fixed; left:initial; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px !important; }';
                    $StylCss .= '.' . $IdGrupyOpinii . ' .slick-next { position:fixed; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:0px !important; }';      
                    $StylCss .= '@media only screen and (max-width:779px) { .' . $IdGrupyOpinii . ' .slick-prev { position:fixed; left:initial; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 10) . 'px !important; }}';
                    //
               } else {
                    //
                    $StylCss .= '.' . $IdGrupyOpinii . ' .slick-list { margin:0px ' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px 0px ' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px; }';
                    $StylCss .= '.' . $IdGrupyOpinii . ' .slick-prev { left:0px; }';
                    $StylCss .= '.' . $IdGrupyOpinii . ' .slick-next { right:0px; }';
                    //
               }
               //
          }
          
          // sprawdzi czy nie przesunac strzalek jak sa kropki
          if ( $Konfiguracja['nawigacja_przyciski'] == 'tak' && $Konfiguracja['nawigacja_strzalki_polozenie'] == 'nie' ) {
               //
               $StylCss .= '.' . $IdGrupyOpinii . ' .slick-prev, .' . $IdGrupyOpinii . ' .slick-next { margin-top:-' . ( $Konfiguracja['nawigacja_przyciski_rozmiar'] / 2 ) . 'px; }';
               //
          }                
          
          $StylCss .= '.' . $IdGrupyOpinii . ' { text-align:center; }      
                       .' . $IdGrupyOpinii . ' > .OpiniaOkno:not(:first-child) { display: none; }
                       ' . (($Konfiguracja['nawigacja_przyciski'] == 'tak') ? '.' . $IdGrupyOpinii . ' .slick-dots li:only-child { display: none; }' : '') . '
                       .' . $IdGrupyOpinii . ' .slick-prev, .' . $IdGrupyOpinii . ' .slick-next { width:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px; height:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px; }
                       .' . $IdGrupyOpinii . ' .slick-prev:before, .' . $IdGrupyOpinii . ' .slick-next:before { font-size:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px' . (($Konfiguracja['nawigacja_strzalki_kolor'] != '') ? '; color:#' . $Konfiguracja['nawigacja_strzalki_kolor'] : '') . '; }
                       .' . $IdGrupyOpinii . ' .slick-prev:before { content:"\\' . $Konfiguracja['nawigacja_strzalki_czcionka_wstecz'] . '" }
                       .' . $IdGrupyOpinii . ' .slick-next:before { content:"\\' . $Konfiguracja['nawigacja_strzalki_czcionka_naprzod'] . '" }
                       .' . $IdGrupyOpinii . ' .slick-dots li button:before { content:"\\' . $Konfiguracja['nawigacja_przyciski_czcionka'] . '"; font-size:' . $Konfiguracja['nawigacja_przyciski_rozmiar'] . 'px' . (($Konfiguracja['nawigacja_przyciski_kolor'] != '') ? '; color:#' . $Konfiguracja['nawigacja_przyciski_kolor'] : '') . '; }
                       ' . (($Konfiguracja['nawigacja_przyciski_kolor_aktywna'] != '') ? '.' . $IdGrupyOpinii . ' .slick-dots li.slick-active button:before { color:#' . $Konfiguracja['nawigacja_przyciski_kolor_aktywna'] . '; }' : '');
                
          $GLOBALS['css'] .= $StylCss;
          //
          unset($StylCss);
          //
          $NazwaFunkcjiJs = 'Przelicz_' . $Konfiguracja['id_modulu'] . '_' . $Konfiguracja['kolumna'] . '()';
          //
          echo '<script>
                   function ' . $NazwaFunkcjiJs . ' {
                      var max_wysokosc = 0;
                      $(\'.' . $IdGrupyOpinii . ' .slick-slide\').css({ \'height\' : \'auto\' });
                      $(\'.' . $IdGrupyOpinii . ' .slick-slide\').each(function() {
                          if ( $(this).outerHeight() > max_wysokosc ) {
                               max_wysokosc = $(this).outerHeight();
                          }
                      });
                      $(\'.' . $IdGrupyOpinii . ' .slick-slide\').css({ \'height\' : max_wysokosc });';
  
                      if ( $Konfiguracja['nawigacja_strzalki_polozenie'] == 'tak' && $Konfiguracja['nawigacja_strzalki'] == 'tak' ) {
                           //
                           if ( $Konfiguracja['nawigacja_strzalki_miejsce_wyswietlania'] == 'boki' ) {
                                //
                                echo 'if ($(\'.' . $IdGrupyOpinii . '\').find(\'.slick-arrow\').length === 0) { $(\'.' . $IdGrupyOpinii . ' .slick-list\').css({ \'margin\' : \'0\' }); }';
                                //
                           }
                           //
                      }
                      
                   echo '}
                   $(document).ready(function() {
                     $(\'.' . $IdGrupyOpinii . '\').on(\'setPosition\', function(event, slick, direction){ ' . $NazwaFunkcjiJs . ' }).slick({
                     infinite: true,
                     ' . (($Konfiguracja['nawigacja_przyciski'] == 'tak') ? 'dots: true,' : 'dots: false,') . '
                     ' . (($Konfiguracja['nawigacja_strzalki'] == 'tak') ? 'arrows: true,' : 'arrows: false,') . '
                     ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplay: true,' : '') . '
                     ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplaySpeed: ' . ($Konfiguracja['czas_zmiany_animacji'] * 1000) . ',' : '') . '
                     ' . (($Konfiguracja['sposob_animacji'] == 'przenikanie') ? 'fade: true,' : '') . '
                     speed: ' . ($Konfiguracja['czas_przejscia_efektu_animacji'] * 1000) . ', 
                     pauseOnHover: false,
                     pauseOnFocus: false,
                     slidesToShow: ' . ((count($TablicaOpinii) > (int)$Konfiguracja['ilosc_kolumn_opinii'][0]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_opinii'][0]['kolumny'] : count($TablicaOpinii)) . ',
                     slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_opinii'][0]['kolumny']) . ',                    
                     responsive: [';
                       
                          for ( $tr = 1; $tr < count($Konfiguracja['ilosc_kolumn_opinii']); $tr++ ) {
                            
                                echo '{ 
                                        breakpoint: ' . $Konfiguracja['ilosc_kolumn_opinii'][$tr - 1]['rozdzielczosc'] . ',
                                        settings: {
                                          slidesToShow: ' . ((count($TablicaOpinii) > (int)$Konfiguracja['ilosc_kolumn_opinii'][$tr]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_opinii'][$tr]['kolumny'] : count($TablicaOpinii)) . ',
                                          slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_opinii'][$tr]['kolumny']) . '
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

unset($TablicaOpinii);
?>