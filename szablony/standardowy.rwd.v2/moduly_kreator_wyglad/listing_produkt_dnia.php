<?php
if ( PRODUKT_DNIA_STATUS == 'tak' ) {

    $ProduktDnia = array();
    $ProduktDniaNastepny = array();
    
    $RandCzas = rand(1,10000);

    // sprawdzi czy byl jakis produkt poprzednio
    
    if ( !isset($GLOBALS['produkt_dnia'][date('Y-m-d', time())]) ) { 
         //
         // szuka najblizszej daty
         for ( $x = 1; $x < 100; $x++ ) {
               //
               if ( isset($GLOBALS['produkt_dnia'][ date('Y-m-d', time() - (86400 * $x)) ]) ) {
                    //
                    $ProduktDnia = $GLOBALS['produkt_dnia'][ date('Y-m-d', time() - (86400 * $x)) ];
                    break;
                    //
               }
               //
         }
         //
         // szuka nastepnej daty
         for ( $x = 1; $x < 100; $x++ ) {
               //
               if ( isset($GLOBALS['produkt_dnia'][ date('Y-m-d', time() + (86400 * $x)) ]) ) {
                    //
                    $ProduktDniaNastepny = $GLOBALS['produkt_dnia'][ date('Y-m-d', time() + (86400 * $x)) ];
                    break;
                    //
               }
               //
         }         
         //
    } else {
         //
         $ProduktDnia = $GLOBALS['produkt_dnia'][date('Y-m-d', time())];
         //
    }

    if ( count($ProduktDnia) > 0 ) { 

        $Produkt = new Produkt( $ProduktDnia['id_produktu'] );
        //       
        if ( $Produkt->CzyJestProdukt == true ) {
      
              $IloscSekund = FunkcjeWlasnePHP::my_strtotime(date('Y-m-d', time()) . ' 23:59:59') - time();  
              
              // czy jest produktem dnia
              $CssNieaktywny = '';
              //
              if ( $IloscSekund < 0 || $Produkt->info['produkt_dnia'] == 'nie' ) {
                   //
                   $CssNieaktywny = ' ProduktDniaNieaktywny';
                   //
              }

              echo '<article class="ProduktDnia OknoFlex' . $CssNieaktywny . '">';
              
                  echo '<div class="ElementOknoRamka">';
              
                      echo '<div class="Foto">' . $Produkt->fotoGlowne['zdjecie_link'] . '</div>';
                      //
                      echo '<h3>' . $Produkt->info['link'] . '</h3>';
                                          
                      // opis krotki
                      if ( $Konfiguracja['opis_krotki_produkt_dnia'] == 'tak' ) {
                           //
                           echo '<div class="Opis">' . $Produkt->info['opis_krotki'] . '</div>';
                           //
                      }                                  
                      
                      // jezeli jest na dany dzien aktywny produktu dnia
                      if ( $IloscSekund > 0 && $Produkt->info['produkt_dnia'] == 'tak' ) {

                            echo '<div class="ProduktDniaCena">' . $Produkt->info['cena'] . '</div>';

                            echo '<div class="OfertaKonczy">{__TLUMACZ:PRODUKT_DNIA_INFO}</div>';   
                        
                            echo '<div class="Odliczanie"><span id="sekundy_produkt_dnia_' . $Produkt->info['id'] . '_' . $RandCzas . '"></span></div>';
                            
                            echo Wyglad::PrzegladarkaJavaScript( 'odliczaj("sekundy_produkt_dnia_' . $Produkt->info['id'] . '_' . $RandCzas . '",' . $IloscSekund . ',\'{__TLUMACZ:LICZNIK_PROMOCJI_DZIEN}\',\'{__TLUMACZ:LICZNIK_PROMOCJI_JEDEN_DZIEN}\')' );  
                            
                      } else {
                          
                            echo '<div class="ProduktDniaZakonczono">{__TLUMACZ:PROMOCJA_ZAKONCZONA}</div>';
                            
                            if ( count($ProduktDniaNastepny) > 0 && $Konfiguracja['nastepny_produkt_dnia'] == 'tak' ) {
                              
                                 $IloscSekundNastepny = FunkcjeWlasnePHP::my_strtotime(date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($ProduktDniaNastepny['data'])) . ' 23:59:59') - time(); 
                                 
                                 echo '<div class="OfertaKonczy">{__TLUMACZ:NASTEPNA_PROMOCJA_ZA}</div>';   
                            
                                 echo '<div class="Odliczanie"><span id="sekundy_produkt_dnia_' . $Produkt->info['id'] . '_' . $RandCzas . '"></span></div>';
                                
                                 echo Wyglad::PrzegladarkaJavaScript( 'odliczaj("sekundy_produkt_dnia_' . $Produkt->info['id'] . '_' . $RandCzas . '",' . $IloscSekundNastepny . ',\'{__TLUMACZ:LICZNIK_PROMOCJI_DZIEN}\',\'{__TLUMACZ:LICZNIK_PROMOCJI_JEDEN_DZIEN}\')' );  
                                 
                                 unset($IloscSekundNastepny);
                                 
                            }
                            
                      }

                      if ( $IloscSekund > 0 && $Produkt->info['produkt_dnia'] == 'tak' ) {

                           if ( $Konfiguracja['kupowanie_produktu_dnia'] == 'tak' ) {

                               // elementy kupowania
                               $Produkt->ProduktKupowanie();                    
                               
                               // jezeli jest aktywne kupowanie produktow
                               if ( $Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak' ) {
                                    //
                                    echo '<div class="Zakup">';
                                    
                                        if ( $Produkt->zakupy['ma_pola_tekstowe'] == '0' && $Produkt->zakupy['ma_cechy'] == '0' ) {
                                            echo $Produkt->zakupy['input_ilosci'] . '<em>' . $Produkt->zakupy['jednostka_miary'] . '</em> ' . $Produkt->zakupy['przycisk_kup'];
                                        } else {
                                            echo str_replace('class="DoKoszyka Wybor"', 'class="DoKoszyka Wybor tooltip" title="{__TLUMACZ:PRODUKT_WYBIERZ_OPCJE}"', (string)$Produkt->zakupy['przycisk_szczegoly']);
                                        }
                                    
                                    echo '</div>'; 
                                    //
                               }   

                           }            

                      }

                      if ( $Konfiguracja['oszczedzasz_produkt_dnia'] == 'tak' ) {
                                                      
                           echo '<div class="ProduktDniaOszczedzasz"><span>{__TLUMACZ:OSZCZEDZASZ}<b>' . $ProduktDnia['rabat'] . '%</b></span></div>';  
                           
                      }

                      unset($Produkt);
                          
                  echo '</div>';

              echo '</article>';
            
        }
        
        unset($Produkt);

    }
    
    unset($ProduktDnia, $ProduktDniaNastepny, $RandCzas);
    
}
?>