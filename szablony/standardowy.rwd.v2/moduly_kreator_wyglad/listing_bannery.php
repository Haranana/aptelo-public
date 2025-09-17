<?php
if ( isset($GLOBALS['bannery']->info[$Konfiguracja['grupa_bannerow']]) ) {
  
     $TablicaBannerow = $GLOBALS['bannery']->info[$Konfiguracja['grupa_bannerow']];
     
     if ( count($TablicaBannerow) > 0 ) {
       
          // jezeli jest tylko jedna pozycja to przejmuje tryb statyczny
          if ( count($TablicaBannerow) == 1 ) {
               //
               $Konfiguracja['sposob_wyswietlania'] = 'statyczny';
               $Konfiguracja['animowane_bannery_ladowanie'] = 'nie';
               //
          }         

          $IdGrupyBannerow = '';
          
          // jezeli wyswietlanie statyczne lub animowane
          if ( $Konfiguracja['sposob_wyswietlania'] == 'statyczny' ) {
               //
               $IdGrupyBannerow = 'Statyczny-' . ucfirst($Konfiguracja['kolumna']) . '-' . $Konfiguracja['id_modulu'];
               //
          } else if ( $Konfiguracja['sposob_wyswietlania'] == 'animowany' ) {
               //
               $IdGrupyBannerow = 'Animacja-' . ucfirst($Konfiguracja['kolumna']) . '-' . $Konfiguracja['id_modulu'];
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
          
          $StylCss = '';
          $FunkcjaDodatkowaJs = '';
          //
          if ( $Konfiguracja['sposob_wyswietlania'] == 'animowany' ) {
               //
               if ( $Konfiguracja['auto_animacja'] == 'tak' && $Konfiguracja['pasek_animacji'] == 'tak' ) {
                    //
                    echo '<div class="PasekPostepuGrafikiKontener"><div class="PasekPostepu-' . $Konfiguracja['id_modulu'] . ' PasekPostepuGrafiki PasekPostepuGrafikiAnimacjaPierwsza"></div></div>';
                    //
                    $StylCss .= '.ModulId-' . $Konfiguracja['id_modulu'] . ' .PasekPostepuGrafiki { background:#' . $Konfiguracja['pasek_animacji_kolor'] . '; }';
                    $StylCss .= '.ModulId-' . $Konfiguracja['id_modulu'] . ' .PasekPostepuGrafikiKontener { height:' . $Konfiguracja['pasek_animacji_wysokosc'] . 'px; background:#' . $Konfiguracja['pasek_animacji_tlo_kolor'] . '; }';
                    $StylCss .= '.ModulId-' . $Konfiguracja['id_modulu'] . ' .PasekPostepuGrafikiAnimacjaPierwsza { animation:PostepAnimacji ' . ((float)$Konfiguracja['czas_zmiany_animacji'] - (float)$Konfiguracja['czas_przejscia_efektu_animacji']) . 's linear forwards; }';
                    $StylCss .= '.ModulId-' . $Konfiguracja['id_modulu'] . ' .PasekPostepuGrafikiAnimacja { animation:PostepAnimacji ' . (float)$Konfiguracja['czas_zmiany_animacji'] . 's linear forwards; }';
                    //
                    $FunkcjaDodatkowaJs = '$(\'.PasekPostepu-' . $Konfiguracja['id_modulu'] . '\').removeClass(\'PasekPostepuGrafikiAnimacjaPierwsza\').removeClass(\'PasekPostepuGrafikiAnimacja\');$(\'.PasekPostepu-' . $Konfiguracja['id_modulu'] . '\')[0].offsetWidth;$(\'.PasekPostepu-' . $Konfiguracja['id_modulu'] . '\').addClass(\'PasekPostepuGrafikiAnimacja\');';
                    //
               }
               //
          }
          
          echo '<div class="' . $IdGrupyBannerow . $IdCssDodatkowy . '">';
  
              if ( $Konfiguracja['sortowanie_bannerow'] == 'losowo' ) {
                   //
                   $WybraneBannery = Funkcje::wylosujElementyTablicyJakoTablica($TablicaBannerow, $Konfiguracja['ilosc_bannerow']);
                   //
              } else {
                   //
                   $WybraneBannery = array_slice($TablicaBannerow, 0, $Konfiguracja['ilosc_bannerow']);
                   //
              }
              
              $tx = 1;
              
              foreach ($WybraneBannery as $Banner ) { 

                  echo $GLOBALS['bannery']->bannerWyswietlKreatorModulow($Banner, true, $tx, (($Konfiguracja['animowane_bannery_ladowanie'] == 'tak') ? true : false), $Konfiguracja['id_modulu'] . '-' . $t, ((count($TablicaBannerow) == 1) ? true : false));

                  $tx++;

              }    

              unset($WybraneBannery);

          echo '</div>';

          // jezeli wyswietlanie animowane
          if ( $Konfiguracja['sposob_wyswietlania'] == 'animowany' ) {
               //
               // jezeli strzalki maja byc wyswietlane poza obszarem tresci
               if ( $Konfiguracja['nawigacja_strzalki_polozenie'] == 'tak' && $Konfiguracja['nawigacja_strzalki'] == 'tak' ) {
                    //
                    if ( $Konfiguracja['nawigacja_strzalki_miejsce_wyswietlania'] == 'tytul' && $Konfiguracja['naglowek_kolumny'] == 'tak' ) {
                         //
                         $StylCss .= '.' . $IdGrupyBannerow . ' .slick-prev { position:fixed; left:initial; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px !important; }';
                         $StylCss .= '.' . $IdGrupyBannerow . ' .slick-next { position:fixed; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:0px !important; }';      
                         $StylCss .= '@media only screen and (max-width:779px) { .' . $IdGrupyBannerow . ' .slick-prev { position:fixed; left:initial; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 10) . 'px !important; }}';
                         //
                    } else {
                          //
                         $StylCss .= '.' . $IdGrupyBannerow . ' .slick-list { margin:0px ' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px 0px ' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px; }';
                         $StylCss .= '.' . $IdGrupyBannerow . ' .slick-prev { left:0px; }';
                         $StylCss .= '.' . $IdGrupyBannerow . ' .slick-next { right:0px; }';
                         //
                    }
                    //
               }
               
               // sprawdzi czy nie przesunac strzalek jak sa kropki
               if ( $Konfiguracja['nawigacja_przyciski'] == 'tak' && $Konfiguracja['nawigacja_strzalki_polozenie'] == 'nie' ) {
                    //
                    $StylCss .= '.' . $IdGrupyBannerow . ' .slick-prev, .' . $IdGrupyBannerow . ' .slick-next { margin-top:-' . ( $Konfiguracja['nawigacja_przyciski_rozmiar'] / 2 ) . 'px; }';
                    //
               }
                    
               $StylCss .= '.' . $IdGrupyBannerow . ' { text-align:center; }  
                            .' . $IdGrupyBannerow . ' > .GrafikaKreator:not(:first-child) { display: none; }
                            ' . (($Konfiguracja['nawigacja_przyciski'] == 'tak') ? '.' . $IdGrupyBannerow . ' .slick-dots li:only-child { display: none; }' : '') . '
                            .' . $IdGrupyBannerow . ' .GrafikaKreator { padding:' . $Konfiguracja['margines_bannerow_animowanych'] . 'px; }
                            .' . $IdGrupyBannerow . ' .slick-prev, .' . $IdGrupyBannerow . ' .slick-next { width:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px; height:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px;}
                            .' . $IdGrupyBannerow . ' .slick-prev:before, .' . $IdGrupyBannerow . ' .slick-next:before { font-size:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px' . (($Konfiguracja['nawigacja_strzalki_kolor'] != '') ? '; color:#' . $Konfiguracja['nawigacja_strzalki_kolor'] : '') . '; }
                            .' . $IdGrupyBannerow . ' .slick-prev:before { content:"\\' . $Konfiguracja['nawigacja_strzalki_czcionka_wstecz'] . '" }
                            .' . $IdGrupyBannerow . ' .slick-next:before { content:"\\' . $Konfiguracja['nawigacja_strzalki_czcionka_naprzod'] . '" }
                            .' . $IdGrupyBannerow . ' .slick-dots li button:before { content:"\\' . $Konfiguracja['nawigacja_przyciski_czcionka'] . '"; font-size:' . $Konfiguracja['nawigacja_przyciski_rozmiar'] . 'px' . (($Konfiguracja['nawigacja_przyciski_kolor'] != '') ? '; color:#' . $Konfiguracja['nawigacja_przyciski_kolor'] : '') . '; }
                            ' . (($Konfiguracja['nawigacja_przyciski_kolor_aktywna'] != '') ? '.' . $IdGrupyBannerow . ' .slick-dots li.slick-active button:before { color:#' . $Konfiguracja['nawigacja_przyciski_kolor_aktywna'] . '; }' : '');

               if ( $Konfiguracja['nawigacja_strzalki_polozenie'] == 'nie' && $Konfiguracja['nawigacja_strzalki'] == 'tak' ) {

                    $StylCss .= '.' . $IdGrupyBannerow . ' .slick-prev { left:0; opacity:0; transition:all 0.3s linear 0s; }';
                    $StylCss .= '.' . $IdGrupyBannerow . ' .slick-next { right:0; opacity:0; transition:all 0.3s linear 0s; }';
                    $StylCss .= '.' . $IdGrupyBannerow . ':hover .slick-prev { transform: translate('.($Konfiguracja['nawigacja_strzalki_rozmiar']/2).'px, -'.($Konfiguracja['nawigacja_strzalki_rozmiar']/2).'px); opacity: 1; }';
                    $StylCss .= '.' . $IdGrupyBannerow . ':hover .slick-next { transform: translate(-'.($Konfiguracja['nawigacja_strzalki_rozmiar']/2).'px, -'.($Konfiguracja['nawigacja_strzalki_rozmiar']/2).'px); opacity: 1; }';
               }

               $GLOBALS['css'] .= $StylCss;
               //
               unset($StylCss);
               //
               echo '<script>
                        $(document).ready(function() {
                          $(\'.' . $IdGrupyBannerow . '\').on(\'beforeChange\', function() { ' . $FunkcjaDodatkowaJs .  ' AnimujTekst(\'' . $IdGrupyBannerow . '\') }).slick({
                          infinite: true,
                          ' . (($Konfiguracja['nawigacja_przyciski'] == 'tak') ? 'dots: true,' : 'dots: false,') . '
                          ' . (($Konfiguracja['nawigacja_strzalki'] == 'tak') ? 'arrows: true,' : 'arrows: false,') . '
                          ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplay: true,' : '') . '
                          ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplaySpeed: ' . ($Konfiguracja['czas_zmiany_animacji'] * 1000) . ',' : '') . '
                          ' . (($Konfiguracja['sposob_animacji'] == 'przenikanie') ? 'fade: true,' : '') . '
                          speed: ' . ($Konfiguracja['czas_przejscia_efektu_animacji'] * 1000) . ', 
                          pauseOnHover: false,
                          pauseOnFocus: false,
                          slidesToShow: ' . ((count($TablicaBannerow) > (int)$Konfiguracja['ilosc_kolumn_bannerow'][0]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_bannerow'][0]['kolumny'] : count($TablicaBannerow)) . ',
                          slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_bannerow'][0]['kolumny']) . ',                     
                          responsive: [';
                            
                              for ( $tr = 1; $tr < count($Konfiguracja['ilosc_kolumn_bannerow']); $tr++ ) {
                                
                                    echo '{ 
                                            breakpoint: ' . $Konfiguracja['ilosc_kolumn_bannerow'][$tr - 1]['rozdzielczosc'] . ',
                                            settings: {
                                              slidesToShow: ' . ((count($TablicaBannerow) > (int)$Konfiguracja['ilosc_kolumn_bannerow'][$tr]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_bannerow'][$tr]['kolumny'] : count($TablicaBannerow)) . ',
                                              slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_bannerow'][$tr]['kolumny']) . '
                                            }   
                                          },';
                                    
                              }                         
                            
                          echo '] 
                          });
                          AnimujTekst(\'' . $IdGrupyBannerow . '\');
                        });
                     </script>';
               //
          }   

          // jezeli wyswietlanie statyczne
          if ( $Konfiguracja['sposob_wyswietlania'] == 'statyczny' ) {
               //
               $StylCss = '.' . $IdGrupyBannerow . ' { text-align:center; }';
                     
                     // jezeli wyswietlanie bannerow obok siebie
                     if ( $Konfiguracja['sposob_wyswietlania_bannerow_statycznych'] != 'osobno' ) {
                          //
                          $StylCss .= '.' . $IdGrupyBannerow . ' .GrafikaKreator { display:inline-block; vertical-align:middle; padding:' . $Konfiguracja['margines_bannerow_statycznych'] . 'px; }';
                          //
                     } else {
                          //
                          $StylCss .= '.' . $IdGrupyBannerow . ' .GrafikaKreator { padding:' . $Konfiguracja['margines_bannerow_statycznych'] . 'px 0px ' . $Konfiguracja['margines_bannerow_statycznych'] . 'px 0px; }';
                          //
                     }
                     
               //
               $GLOBALS['css'] .= $StylCss;
               //
          }            

          unset($StylCss);          
       
     }
     
     unset($TablicaBannerow);
  
}
?>