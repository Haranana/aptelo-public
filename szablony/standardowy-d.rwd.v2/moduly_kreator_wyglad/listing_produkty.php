<?php

$TablicaProduktow = Produkty::ProduktyKreatorModulow($Konfiguracja['ilosc_produktow'], $Konfiguracja['grupa_produktow'], $Konfiguracja['sortowanie_produktow'], $Konfiguracja['produkty_id_kategoria'], $Konfiguracja['produkty_id_producenta'], $Konfiguracja['warunki_produktow'], $Konfiguracja['tylko_dostepne_produkty'] );

if ( count($TablicaProduktow) > 0 ) {
  
     // jezeli jest tylko jedna pozycja to przejmuje tryb statyczny
     if ( count($TablicaProduktow) == 1 ) {
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
     
     $IdGrupyProduktow = '';

     // jezeli wyswietlanie statyczne lub animowane
     if ( $Konfiguracja['sposob_wyswietlania'] == 'statyczny' ) {
          //
          $IdGrupyProduktow = 'Statyczny-' . ucfirst($Konfiguracja['kolumna']) . '-' . $Konfiguracja['id_modulu'];
          //
          echo '<div class="OknaFlexRwd ' . $IdGrupyProduktow . '">';
          //
     } else if ( $Konfiguracja['sposob_wyswietlania'] == 'animowany' ) {
          //
          $IdGrupyProduktow = 'Animacja-' . ucfirst($Konfiguracja['kolumna']) . '-' . $Konfiguracja['id_modulu'];
          //
          echo '<div class="' . $IdCssDodatkowyKontener . '"><div class="AnimacjaKreator ' . $IdGrupyProduktow . $IdCssDodatkowy . '">';
          //
     }   
     
     unset($IdCssDodatkowyKontener, $IdCssDodatkowy);

     // listing produktow
     for ($v = 0, $cs = count($TablicaProduktow); $v < $cs; $v++) {
       
          $Produkt = new Produkt( $TablicaProduktow[$v] );
                
          // elementy kupowania
          $Produkt->ProduktKupowanie();   

          echo '<article id="prd-' . rand(1,1000) . '-' . $TablicaProduktow[$v] . '" class="ProduktOkno ListaProduktyKreator' . (($Konfiguracja['sposob_wyswietlania'] == 'statyczny') ? ' OknoFlex' : '') . (($Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak') ? '' : ' ProduktBezZakupu') . (($GLOBALS['koszykKlienta']->SprawdzCzyDodanyDoKoszyka($Produkt->info['id'])) ? ' ProduktDodanyDoKoszyka' : '') . '">';
          
              echo '<div class="ElementOknoRamka" data-product-id="'.$Produkt->info['id'].'">';

                  echo '<div class="Foto">' . $Produkt->fotoGlowne['zdjecie_link_ikony'] . '</div>';

                  echo '<h3>' . $Produkt->info['link'] . '</h3>';
                  
                  echo '<div class="CenaProduktu">' . $Produkt->info['cena'] . '</div>';
                  
                  $ListaPol = '';

                  // czy jest wyswietlanie producenta
                  if ( $Konfiguracja['producent_produktow'] == 'tak' ) { 
                       //
                       $Produkt->ProduktProducent();
                       //
                       if ( !empty($Produkt->producent['nazwa'])) {
                            //
                            $ListaPol .= '<li>{__TLUMACZ:PRODUCENT}: <b>' . $Produkt->producent['link'] . '</b></li>';
                            //
                       }
                       //
                  }               
                  
                  // czy jest wyswietalnei numeru katalogowego
                  if ( $Konfiguracja['nr_kat_produktow'] == 'tak' && !empty($Produkt->info['nr_katalogowy'])) {
                       //
                       $ListaPol .= '<li>{__TLUMACZ:NUMER_KATALOGOWY}: <b>' . $Produkt->info['nr_katalogowy'] . '</b></li>';
                       //
                  }      

                  // czy jest dostepnosc produktu
                  if ( $Konfiguracja['dostepnosc_produktow'] == 'tak' ) {
                       //
                       $Produkt->ProduktDostepnosc();
                       //
                       if ( !empty($Produkt->dostepnosc['dostepnosc']) ) {
                            //
                            // jezeli dostepnosc jest obrazkiem wyswietli tylko obrazek
                            if ( $Produkt->dostepnosc['obrazek'] == 'tak' ) {
                                 //
                                 $ListaPol .= '<li>' . $Produkt->dostepnosc['dostepnosc'] . '</li>';
                                 //
                              } else {
                                 //
                                 $ListaPol .= '<li>{__TLUMACZ:DOSTEPNOSC}: <b> ' . $Produkt->dostepnosc['dostepnosc'] . '</b></li>';
                                 //
                            }
                      }            
                      //
                  }        

                  if ( $ListaPol != '' ) {
                       //
                       echo '<ul class="ListaOpisowa">' . $ListaPol . '</ul>';                
                       //
                  }
                  
                  unset($ListaPol);              
                  
                  // data dostepnosci
                  if ( $Konfiguracja['data_dostepnosci_produktow'] == 'tak' && Funkcje::CzyNiePuste($Produkt->info['data_dostepnosci']) ) {
                       //
                       echo '<div class="DataDostepnosci">{__TLUMACZ:DOSTEPNY_OD_DNIA} <b>' . $Produkt->info['data_dostepnosci'] . '</b></div>';
                       //
                  }
                  
                  // opis krotki
                  if ( $Konfiguracja['opis_krotki_produktow'] == 'tak' ) {
                       //
                       echo '<div class="Opis">' . $Produkt->info['opis_krotki'] . '</div>';
                       //
                  }                  
                    
                  // dla promocji czasowych z zegarem
                  if ( $Konfiguracja['grupa_produktow'] == 'promocje_czasowe' ) {
                       
                       $IloscSekund = ($Produkt->ikonki['promocja_data_do'] - time());            
                      
                       if ( $IloscSekund > 0 ) {
                         
                            $WartoscLosowa = rand(1,1000000);
                          
                            echo '<div class="Odliczanie"><span id="sekundy_' . $Produkt->info['id'] . '_' . $WartoscLosowa . '"></span>{__TLUMACZ:CZAS_DO_KONCA_PROMOCJI}</div>';
                            //
                            echo Wyglad::PrzegladarkaJavaScript( 'odliczaj("sekundy_' . $Produkt->info['id'] . '_' . $WartoscLosowa . '",' . $IloscSekund . ',\'{__TLUMACZ:LICZNIK_PROMOCJI_DZIEN}\',\'{__TLUMACZ:LICZNIK_PROMOCJI_JEDEN_DZIEN}\')' );                 
                            
                            unset($WartoscLosowa);
                            
                       }
                       
                       unset($IloscSekund);

                  }
                  
                  if ( ( $Konfiguracja['schowek_produktow'] == 'tak' && PRODUKT_SCHOWEK_STATUS == 'tak' ) || $Konfiguracja['kupowanie_produktow'] == 'tak' ) {

                       echo '<div class="ZakupKontener">';                   
                  
                           // jezeli jest aktywne dodawanie do schowka
                           if ( $Konfiguracja['schowek_produktow'] == 'tak' && PRODUKT_SCHOWEK_STATUS == 'tak' ) {
                                //
                                echo '<div class="SchowekKontener">';

                                    if ($GLOBALS['schowekKlienta']->SprawdzCzyDodanyDoSchowka($Produkt->info['id'])) {                                    
                                        echo '<span onclick="DoSchowka(' . $Produkt->info['id'] . ')" class="Schowek SchowekDodany ToolTip" title="{__TLUMACZ:LISTING_PRODUKT_DODANY_DO_SCHOWKA}">{__TLUMACZ:LISTING_PRODUKT_DODANY_DO_SCHOWKA}</span>';
                                    } else {
                                        echo '<span onclick="DoSchowka(' . $Produkt->info['id'] . ')" class="Schowek ToolTip" title="{__TLUMACZ:LISTING_DODAJ_DO_SCHOWKA}">{__TLUMACZ:LISTING_DODAJ_DO_SCHOWKA}</span>';
                                    }      

                                echo '</div>';
                                //
                           }                   
                           
                           if ( $Konfiguracja['kupowanie_produktow'] == 'tak' ) {
                             
                                echo '<div class="Zakup">';

                                    // jezeli jest aktywne kupowanie produktow
                                    if ( $Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak' ) {
                                         //
                                         if ( $Produkt->zakupy['ma_pola_tekstowe'] == '0' && $Produkt->zakupy['ma_cechy'] == '0' ) {
                                             echo $Produkt->zakupy['input_ilosci'] . '<em>' . $Produkt->zakupy['jednostka_miary'] . '</em> ' . $Produkt->zakupy['przycisk_kup'];
                                         } else {
                                             echo $Produkt->zakupy['przycisk_szczegoly'];
                                         }
                                         //
                                    } else {
                                         //
                                         echo $Produkt->info['zapytanie_o_produkt'];
                                         //
                                    }

                                echo '</div>';                                 
                                
                           }

                      echo '</div>'; 

                  }       

              echo '</div>';

          echo '</article>';
          
          unset($Produkt);
        
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
          for ( $tr = 0; $tr < count($Konfiguracja['ilosc_kolumn_produktow']); $tr++ ) {
            
                $StylCss .= '@media only screen and (min-width:' . $Konfiguracja['ilosc_kolumn_produktow'][$tr]['rozdzielczosc'] . 'px)' . (($tr > 0) ? ' and (max-width:' . ($Konfiguracja['ilosc_kolumn_produktow'][$tr - 1]['rozdzielczosc'] - 1) . 'px)' : '') . ' { 
                               .' . $IdGrupyProduktow . ' article { width:calc(' . ((count($TablicaProduktow) > (int)$Konfiguracja['ilosc_kolumn_produktow'][$tr]['kolumny']) ? '(100% / '.(int)$Konfiguracja['ilosc_kolumn_produktow'][$tr]['kolumny'].') - ((('.(int)$Konfiguracja['ilosc_kolumn_produktow'][$tr]['kolumny'].' - 1) / '.(int)$Konfiguracja['ilosc_kolumn_produktow'][$tr]['kolumny'].') * var(--okna-odstep))' : '(100% / '.count($TablicaProduktow).') - ((('.count($TablicaProduktow).' - 1) / '.count($TablicaProduktow).') * var(--okna-odstep))') . ') }
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
                    $StylCss .= '.' . $IdGrupyProduktow . ' .slick-prev { position:fixed; left:initial; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px !important; }';
                    $StylCss .= '.' . $IdGrupyProduktow . ' .slick-next { position:fixed; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:0px !important; }';      
                    $StylCss .= '@media only screen and (max-width:779px) { .' . $IdGrupyProduktow . ' .slick-prev { position:fixed; left:initial; top:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] / 2) . 'px !important; right:' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 10) . 'px !important; }}';
                    //
               } else {
                    //
                    $StylCss .= '.' . $IdGrupyProduktow . ' .slick-list { margin:0px ' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px 0px ' . ($Konfiguracja['nawigacja_strzalki_rozmiar'] + 5) . 'px; }';
                    $StylCss .= '.' . $IdGrupyProduktow . ' .slick-prev { left:0px; }';
                    $StylCss .= '.' . $IdGrupyProduktow . ' .slick-next { right:0px; }';
                    //
               }
               //
          }
          
          // sprawdzi czy nie przesunac strzalek jak sa kropki
          if ( $Konfiguracja['nawigacja_przyciski'] == 'tak' && $Konfiguracja['nawigacja_strzalki_polozenie'] == 'nie' ) {
               //
               $StylCss .= '.' . $IdGrupyProduktow . ' .slick-prev, .' . $IdGrupyProduktow . ' .slick-next { margin-top:-' . ( $Konfiguracja['nawigacja_przyciski_rozmiar'] / 2 ) . 'px; }';
               //
          }                
          
          $StylCss .= '.' . $IdGrupyProduktow . ' { text-align:center; }
                       .' . $IdGrupyProduktow . ' > .ProduktOkno:not(:first-child) { display: none; }
                       ' . (($Konfiguracja['nawigacja_przyciski'] == 'tak') ? '.' . $IdGrupyProduktow . ' .slick-dots li:only-child { display: none; }' : '') . '
                       .' . $IdGrupyProduktow . ' .slick-prev, .' . $IdGrupyProduktow . ' .slick-next { width:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px; height:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px; }
                       .' . $IdGrupyProduktow . ' .slick-prev:before, .' . $IdGrupyProduktow . ' .slick-next:before { font-size:' . $Konfiguracja['nawigacja_strzalki_rozmiar'] . 'px' . (($Konfiguracja['nawigacja_strzalki_kolor'] != '') ? '; color:#' . $Konfiguracja['nawigacja_strzalki_kolor'] : '') . '; }
                       .' . $IdGrupyProduktow . ' .slick-prev:before { content:"\\' . $Konfiguracja['nawigacja_strzalki_czcionka_wstecz'] . '" }
                       .' . $IdGrupyProduktow . ' .slick-next:before { content:"\\' . $Konfiguracja['nawigacja_strzalki_czcionka_naprzod'] . '" }
                       .' . $IdGrupyProduktow . ' .slick-dots li button:before { content:"\\' . $Konfiguracja['nawigacja_przyciski_czcionka'] . '"; font-size:' . $Konfiguracja['nawigacja_przyciski_rozmiar'] . 'px' . (($Konfiguracja['nawigacja_przyciski_kolor'] != '') ? '; color:#' . $Konfiguracja['nawigacja_przyciski_kolor'] : '') . '; }
                       ' . (($Konfiguracja['nawigacja_przyciski_kolor_aktywna'] != '') ? '.' . $IdGrupyProduktow . ' .slick-dots li.slick-active button:before { color:#' . $Konfiguracja['nawigacja_przyciski_kolor_aktywna'] . '; }' : '');
                
          $GLOBALS['css'] .= $StylCss;
          //
          unset($StylCss);
          //
          $NazwaFunkcjiJs = 'Przelicz_' . $Konfiguracja['id_modulu'] . '_' . $Konfiguracja['kolumna'] . '()';
          //
          // funkcja do ustawienia stalej wysokosci okna
          echo '<script>
                   function ' . $NazwaFunkcjiJs . ' {
                      var max_wysokosc = 0;
                      $(\'.' . $IdGrupyProduktow . ' .slick-slide\').css({ \'height\' : \'auto\' });
                      $(\'.' . $IdGrupyProduktow . ' .slick-slide\').each(function() {
                          if ( $(this).outerHeight() > max_wysokosc ) {
                               max_wysokosc = $(this).outerHeight();
                          }
                      });
                      $(\'.' . $IdGrupyProduktow . ' .slick-slide\').css({ \'height\' : max_wysokosc });';
  
                      if ( $Konfiguracja['nawigacja_strzalki_polozenie'] == 'tak' && $Konfiguracja['nawigacja_strzalki'] == 'tak' ) {
                           //
                           if ( $Konfiguracja['nawigacja_strzalki_miejsce_wyswietlania'] == 'boki' ) {
                                //
                                echo 'if ($(\'.' . $IdGrupyProduktow . '\').find(\'.slick-arrow\').length === 0) { $(\'.' . $IdGrupyProduktow . ' .slick-list\').css({ \'margin\' : \'0\' }); }';
                                //
                           }
                           //
                      }
                      
                   echo '}
                   $(document).ready(function() {
                     $(\'.' . $IdGrupyProduktow . '\').on(\'setPosition\', function(event, slick, direction){ ' . $NazwaFunkcjiJs . ' }).slick({
                     infinite: true,
                     ' . (($Konfiguracja['nawigacja_przyciski'] == 'tak') ? 'dots: true,' : 'dots: false,') . '
                     ' . (($Konfiguracja['nawigacja_strzalki'] == 'tak') ? 'arrows: true,' : 'arrows: false,') . '
                     ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplay: true,' : '') . '
                     ' . (($Konfiguracja['auto_animacja'] == 'tak') ? 'autoplaySpeed: ' . ($Konfiguracja['czas_zmiany_animacji'] * 1000) . ',' : '') . '
                     ' . (($Konfiguracja['sposob_animacji'] == 'przenikanie') ? 'fade: true,' : '') . '
                     speed: ' . ($Konfiguracja['czas_przejscia_efektu_animacji'] * 1000) . ', 
                     pauseOnHover: false,
                     pauseOnFocus: false,
                     slidesToShow: ' . ((count($TablicaProduktow) > (int)$Konfiguracja['ilosc_kolumn_produktow'][0]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_produktow'][0]['kolumny'] : count($TablicaProduktow)) . ',
                     slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_produktow'][0]['kolumny']) . ',                      
                     responsive: [';
                       
                          for ( $tr = 1; $tr < count($Konfiguracja['ilosc_kolumn_produktow']); $tr++ ) {
                            
                                echo '{ 
                                        breakpoint: ' . $Konfiguracja['ilosc_kolumn_produktow'][$tr - 1]['rozdzielczosc'] . ',
                                        settings: {
                                          slidesToShow: ' . ((count($TablicaProduktow) > (int)$Konfiguracja['ilosc_kolumn_produktow'][$tr]['kolumny']) ? (int)$Konfiguracja['ilosc_kolumn_produktow'][$tr]['kolumny'] : count($TablicaProduktow)) . ',
                                          slidesToScroll: ' . (($Konfiguracja['ilosc_przewiniec'] == 'jeden') ? 1 : $Konfiguracja['ilosc_kolumn_produktow'][$tr]['kolumny']) . '
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

unset($TablicaProduktow);
?>