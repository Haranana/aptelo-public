<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_PROMOCJE_Z_ZEGAREM_ILOSC_PRODUKTOW;Ilość wyświetlanych w produktów;1;1,2,3}}
// {{MODUL_PROMOCJE_Z_ZEGAREM_KUPOWANIE;Czy wyświetać możliwość zakupu produktu;tak;tak,nie}}
//

// zmienne bez definicji
$LimitZapytania = 3;
$MoznaKupic = 'tak';

if ( defined('MODUL_PROMOCJE_Z_ZEGAREM_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)MODUL_PROMOCJE_Z_ZEGAREM_ILOSC_PRODUKTOW;
}
if ( defined('MODUL_PROMOCJE_Z_ZEGAREM_KUPOWANIE') ) {
   $MoznaKupic = MODUL_PROMOCJE_Z_ZEGAREM_KUPOWANIE;
}

$Tablica = array();  

$sqlLosowe = $GLOBALS['db']->open_query( Produkty::SqlPromocjeProste( ' AND p.specials_date_end > "' . date('Y-m-d') . '"' ) );

if ((int)$GLOBALS['db']->ile_rekordow($sqlLosowe) > 0) { 

      while ($info_random = $sqlLosowe->fetch_assoc()) {
          //
          $Tablica[] = $info_random['products_id'];
          //
      }

      //wybranie tylko unikalnych rekordow w tablicy;
      $Tablica = array_unique($Tablica);

      $WybraneProdukty = explode(',', (string)Funkcje::wylosujElementyTablicyJakoTekst($Tablica, $LimitZapytania));
      
      $LicznikWierszy = 1;
      
      for ($v = 0, $cs = count($WybraneProdukty); $v < $cs; $v++) {
          //
          $Produkt = new Produkt( $WybraneProdukty[$v] );
          //       
          $IloscSekund = ($Produkt->ikonki['promocja_data_do'] - time());            
          //      
          if ( $IloscSekund > 0 ) {
              //
              // ************************ wyglad produktu - poczatek **************************
              //
              echo '<article class="ProduktWiersz">';
             
                  // ikona bestselleru
                  echo '<div class="Bestseller_' . $_SESSION['domyslnyJezyk']['kod'] . '" style="z-index:99 !important;"></div>';
                  
                  echo '<div class="Foto">'.$Produkt->fotoGlowne['zdjecie_link_ikony'].'</div>';
                  //
                  echo '<div class="ProdOpis" style="margin-left:' . (SZEROKOSC_OBRAZEK_MALY+50) . 'px">';
                  echo '<h3>' . $Produkt->info['link'] . '</h3>' . $Produkt->info['cena'];
                  //
                  echo '<div class="OpisKrotki">' . $Produkt->info['opis_krotki'] . '</div>';
                  //
                  
                  if ( $MoznaKupic == 'tak' ) {
                  
                      // elementy kupowania
                      $Produkt->ProduktKupowanie();                    

                      // jezeli jest aktywne kupowanie produktow
                      if ($Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak') {
                          //
                          echo '<div class="Zakup">';
                          
                              if ( $Produkt->zakupy['ma_pola_tekstowe'] == '0' && $Produkt->zakupy['ma_cechy'] == '0' ) {
                                  echo $Produkt->zakupy['input_ilosci'] . '<em>' . $Produkt->zakupy['jednostka_miary'] . '</em> ' . $Produkt->zakupy['przycisk_kup'];
                              } else {
                                  echo $Produkt->zakupy['przycisk_szczegoly'];
                              }
                          
                          echo '</div>'; 
                          //
                          echo '<div class="cl"></div>';
                          //
                      }            

                  }                    
                  
                  echo '</div>';

                  echo '<div class="Odliczanie" style="margin-left:' . (SZEROKOSC_OBRAZEK_MALY+50) . 'px"><span id="sekundy_'.$Produkt->info['id'].'"></span>{__TLUMACZ:CZAS_DO_KONCA_PROMOCJI}</div>';

                  echo Wyglad::PrzegladarkaJavaScript( 'odliczaj("sekundy_'.$Produkt->info['id'].'",' . $IloscSekund . ',\'{__TLUMACZ:LICZNIK_PROMOCJI_DZIEN}\',\'{__TLUMACZ:LICZNIK_PROMOCJI_JEDEN_DZIEN}\')' );           
                  
                  unset($Produkt, $SredniaOcena);
                  //
                  
                  echo '<div class="cl"></div>';
                  
              echo '</article>';
              //
              // ************************ wyglad produktu - koniec **************************
              //   
              $LicznikWierszy++;
              //
          }
          
      }

      unset($LicznikWierszy, $WybraneProdukty);
      
}

$GLOBALS['db']->close_query($sqlLosowe); 
unset($Tablica, $IloscKolumn, $LimitZapytania, $MoznaKupic);
?>