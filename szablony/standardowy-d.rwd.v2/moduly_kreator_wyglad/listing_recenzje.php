<?php

$TablicaRecenzji = Produkty::ProduktyKreatorModulow($Konfiguracja['ilosc_recenzji'], 'recenzje', $Konfiguracja['sortowanie_recenzji'] );

if ( count($TablicaRecenzji) > 0 ) {

     // jezeli jest tylko jedna pozycja to przejmuje tryb statyczny
     if ( count($TablicaRecenzji) == 1 ) {
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
     
     $IdGrupyRecenzji = '';

     // jezeli wyswietlanie statyczne lub animowane
     if ( $Konfiguracja['sposob_wyswietlania'] == 'statyczny' ) {
          //
          $IdGrupyRecenzji = 'Statyczny-' . ucfirst($Konfiguracja['kolumna']) . '-' . $Konfiguracja['id_modulu'];
          //
          echo '<div class="OknaFlexRwd ' . $IdGrupyRecenzji . '">';
          //
     } else if ( $Konfiguracja['sposob_wyswietlania'] == 'animowany' ) {
          //
          $IdGrupyRecenzji = 'Animacja-' . ucfirst($Konfiguracja['kolumna']) . '-' . $Konfiguracja['id_modulu'];
          //
          echo '<div class="' . $IdCssDodatkowyKontener . '"><div class="AnimacjaKreator ' . $IdGrupyRecenzji . $IdCssDodatkowy . '">';
          //
     }   
     
     unset($IdCssDodatkowyKontener, $IdCssDodatkowy);
  
     // listing recenzji
     for ($v = 0, $cs = count($TablicaRecenzji); $v < $cs; $v++) {

          echo '<article class="ProduktOkno ListaRecenzjeKreator' . (($Konfiguracja['sposob_wyswietlania'] == 'statyczny') ? ' OknoFlex' : '') . '">';
          
              echo '<div class="ElementOknoRamka">';

                  $Produkt = new Produkt( $TablicaRecenzji[$v] );
                  
                  // wczytanie danych o recenzjach produktu
                  $Produkt->ProduktRecenzje();
                  //              
                  echo '<div class="Foto">' . $Produkt->fotoGlowne['zdjecie_link_ikony'] . '</div>';
                  //
                  echo '<h3>' . $Produkt->info['link'] . '</h3>';
                  
                  echo '<div class="CenaProduktu">' . $Produkt->info['cena'] . '</div>';
                  
                  // tablica recenzji produktu
                  $TablicaRecenzjiProduktu = array();
                  //
                  foreach ($Produkt->recenzje as $id => $wartosc) {
                       //
                       $TablicaRecenzjiProduktu[] = $id;
                       //
                  }
                  
                  if ( count($TablicaRecenzjiProduktu) > 0 ) {

                      if ( $Konfiguracja['sortowanie_recenzji'] == 'losowo' ) {
                           //
                           // szuka losowego id recenzji do wybranego produktu
                           $LosowaRecenzja = Funkcje::wylosujElementyTablicyJakoTekst($TablicaRecenzjiProduktu);
                           //              
                      } else {
                           //
                           $LosowaRecenzja = $TablicaRecenzjiProduktu[0];
                           //
                      }
                      
                      echo '<ul class="Ocena">
                      
                                <li>' . $Produkt->recenzje[$LosowaRecenzja]['recenzja_ocena_obrazek'] . '</li>                  
                                <li class="Autor">{__TLUMACZ:AUTOR_RECENZJI}: <span>' . $Produkt->recenzje[$LosowaRecenzja]['recenzja_oceniajacy'] . '</span></li>
                                <li class="DataNapisania">{__TLUMACZ:DATA_NAPISANIA_RECENZJI}: <span>' . $Produkt->recenzje[$LosowaRecenzja]['recenzja_data_dodania'] . '</span></li>
                                <li class="OpisText">' . Funkcje::przytnijTekst(strip_tags((string)$Produkt->recenzje[$LosowaRecenzja]['recenzja_tekst']), 100) . '</li>
                                
                            </ul>';
                            
                  }

                  unset($Produkt, $TablicaRecenzjiProduktu);
                  
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
          for ( $tr = 0; $tr < count($Konfiguracja['ilosc_kolumn_recenzji']); $tr++ ) {
            
                $StylCss .= '@media only screen and (min-width:' . $Konfiguracja['ilosc_kolumn_recenzji'][$tr]['rozdzielczosc'] . 'px)' . (($tr > 0) ? ' and (max-width:' . ($Konfiguracja['ilosc_kolumn_recenzji'][$tr - 1]['rozdzielczosc'] - 1) . 'px)' : '') . ' { 
                               .' . $IdGrupyRecenzji . ' article { width:calc(' . ((count($TablicaRecenzji) > (int)$Konfiguracja['ilosc_kolumn_recenzji'][$tr]['kolumny']) ? '(100% / '.(int)$Konfiguracja['ilosc_kolumn_recenzji'][$tr]['kolumny'].') - ((('.(int)$Konfiguracja['ilosc_kolumn_recenzji'][$tr]['kolumny'].' - 1) / '.(int)$Konfiguracja['ilosc_kolumn_recenzji'][$tr]['kolumny'].') * var(--okna-odstep))' : '(100% / '.count($TablicaRecenzji).') - ((('.count($TablicaRecenzji).' - 1) / '.count($TablicaRecenzji).') * var(--okna-odstep))') . ') }
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
                    $StylCss .= '.' . $IdGrupyRecenzji . ' .slick-prev { position:fixed; left:initial; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px !important; }';
                    $StylCss .= '.' . $IdGrupyRecenzji . ' .slick-next { position:fixed; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:0px !important; }';      
                    $StylCss .= '@media only screen and (max-width:779px) { .' . $IdGrupyRecenzji . ' .slick-prev { position:fixed; left:initial; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 10) . 'px !important; }}';
                    //
               } else {
                    //
                    $StylCss .= '.' . $IdGrupyRecenzji . ' .slick-list { margin:0px ' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px 0px ' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px; }';
                    $StylCss .= '.' . $IdGrupyRecenzji . ' .slick-prev { left:0px; }';
                    $StylCss .= '.' . $IdGrupyRecenzji . ' .slick-next { right:0px; }';
                    //
               }
               //
          }
          
          // sprawdzi czy nie przesunac strzalek jak sa kropki
          if ( $Konfiguracja['nawigacja_przyciski'] == 'tak' && $Konfiguracja['nawigacja_strzalki_polozenie'] == 'nie' ) {
               //
               $StylCss .= '.' . $IdGrupyRecenzji . ' .slick-prev, .' . $IdGrupyRecenzji . ' .slick-next { margin-top:-' . ( $Konfiguracja['nawigacja_przyciski_rozmiar'] / 2 ) . 'px; }';
               //
          }                
          
          $StylCss .= '.' . $IdGrupyRecenzji . ' { text-align:center; }      
                       .' . $IdGrupyRecenzji . ' > .ProduktOkno:not(:first-child) { display: none; }
                       ' . (($Konfiguracja['nawigacja_przyciski'] == 'tak') ? '.' . $IdGrupyRecenzji . ' .slick-dots li:only-child { display: none; }' : '') . '
                       .' . $IdGrupyRecenzji . ' .slick-prev, .' . $IdGrupyRecenzji . ' .slick-next { width:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px; height:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px; }
                       .' . $IdGrupyRecenzji . ' .slick-prev:before, .' . $IdGrupyRecenzji . ' .slick-next:before { font-size:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px' . (($Konfiguracja['nawigacja_strzalki_kolor'] != '') ? '; color:#' . $Konfiguracja['nawigacja_strzalki_kolor'] : '') . '; }
                       .' . $IdGrupyRecenzji . ' .slick-prev:before { content:"\\' . $Konfiguracja['nawigacja_strzalki_czcionka_wstecz'] . '" }
                       .' . $IdGrupyRecenzji . ' .slick-next:before { content:"\\' . $Konfiguracja['nawigacja_strzalki_czcionka_naprzod'] . '" }
                       .' . $IdGrupyRecenzji . ' .slick-dots li button:before { content:"\\' . $Konfiguracja['nawigacja_przyciski_czcionka'] . '"; font-size:' . $Konfiguracja['nawigacja_przyciski_rozmiar'] . 'px' . (($Konfiguracja['nawigacja_przyciski_kolor'] != '') ? '; color:#' . $Konfiguracja['nawigacja_przyciski_kolor'] : '') . '; }
                       ' . (($Konfiguracja['nawigacja_przyciski_kolor_aktywna'] != '') ? '.' . $IdGrupyRecenzji . ' .slick-dots li.slick-active button:before { color:#' . $Konfiguracja['nawigacja_przyciski_kolor_aktywna'] . '; }' : '');
                
          $GLOBALS['css'] .= $StylCss;
          //
          unset($StylCss);
          //
          $NazwaFunkcjiJs = 'Przelicz_' . $Konfiguracja['id_modulu'] . '_' . $Konfiguracja['kolumna'] . '()';
          //
          echo '<script>
                   function ' . $NazwaFunkcjiJs . ' {
                      var max_wysokosc = 0;
                      $(\'.' . $IdGrupyRecenzji . ' .slick-slide\').css({ \'height\' : \'auto\' });
                      $(\'.' . $IdGrupyRecenzji . ' .slick-slide\').each(function() {
                          if ( $(this).outerHeight() > max_wysokosc ) {
                               max_wysokosc = $(this).outerHeight();
                          }
                      });
                      $(\'.' . $IdGrupyRecenzji . ' .slick-slide\').css({ \'height\' : max_wysokosc });';
  
                      if ( $Konfiguracja['nawigacja_strzalki_polozenie'] == 'tak' && $Konfiguracja['nawigacja_strzalki'] == 'tak' ) {
                           //
                           if ( $Konfiguracja['nawigacja_strzalki_miejsce_wyswietlania'] == 'boki' ) {
                                //
                                echo 'if ($(\'.' . $IdGrupyRecenzji . '\').find(\'.slick-arrow\').length === 0) { $(\'.' . $IdGrupyRecenzji . ' .slick-list\').css({ \'margin\' : \'0\' }); }';
                                //
                           }
                           //
                      }
                      
                   echo '}
                   $(document).ready(function() {
                     $(\'.' . $IdGrupyRecenzji . '\').on(\'setPosition\', function(event, slick, direction){ ' . $NazwaFunkcjiJs . ' }).slick({
                     infinite: true,
                     ' . (($Konfiguracja['nawigacja_przyciski'] == 'tak') ? 'dots: true,' : 'dots: false,') . '
                     ' . (($Konfiguracja['nawigacja_strzalki'] == 'tak') ? 'arrows: true,' : 'arrows: false,') . '
                     ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplay: true,' : '') . '
                     ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplaySpeed: ' . ($Konfiguracja['czas_zmiany_animacji'] * 1000) . ',' : '') . '
                     ' . (($Konfiguracja['sposob_animacji'] == 'przenikanie') ? 'fade: true,' : '') . '
                     speed: ' . ($Konfiguracja['czas_przejscia_efektu_animacji'] * 1000) . ', 
                     pauseOnHover: false,
                     pauseOnFocus: false,
                     slidesToShow: ' . ((count($TablicaRecenzji) > (int)$Konfiguracja['ilosc_kolumn_recenzji'][0]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_recenzji'][0]['kolumny'] : count($TablicaRecenzji)) . ',
                     slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_recenzji'][0]['kolumny']) . ',                    
                     responsive: [';
                       
                          for ( $tr = 1; $tr < count($Konfiguracja['ilosc_kolumn_recenzji']); $tr++ ) {
                            
                                echo '{ 
                                        breakpoint: ' . $Konfiguracja['ilosc_kolumn_recenzji'][$tr - 1]['rozdzielczosc'] . ',
                                        settings: {
                                          slidesToShow: ' . ((count($TablicaRecenzji) > (int)$Konfiguracja['ilosc_kolumn_recenzji'][$tr]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_recenzji'][$tr]['kolumny'] : count($TablicaRecenzji)) . ',
                                          slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_recenzji'][$tr]['kolumny']) . '
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

unset($TablicaRecenzji);
?>