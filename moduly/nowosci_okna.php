<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_NOWOSCI_OKNA_ILOSC_PRODUKTOW;Ilość wyświetlanych w produktów;4;2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20}}
// {{MODUL_NOWOSCI_OKNA_ILOSC_KOLUMN;W ilu kolumnach mają być wyświetlane produkty;2;1,2,3,4,5}}
// {{MODUL_NOWOSCI_OKNA_KUPOWANIE;Czy wyświetać możliwość zakupu produktu;tak;tak,nie}}
// {{MODUL_NOWOSCI_OKNA_OPIS;Czy wyświetać opis produktu;tak;tak,nie}}
//

// zmienne bez definicji
$LimitZapytania = 4;
$IloscKolumn = 2;
$MoznaKupic = 'tak';
$WyswietlOpis = 'tak';

if ( defined('MODUL_NOWOSCI_OKNA_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)MODUL_NOWOSCI_OKNA_ILOSC_PRODUKTOW;
}
if ( defined('MODUL_NOWOSCI_OKNA_ILOSC_KOLUMN') ) {
   $IloscKolumn = (int)MODUL_NOWOSCI_OKNA_ILOSC_KOLUMN;
}
if ( defined('MODUL_NOWOSCI_OKNA_KUPOWANIE') ) {
   $MoznaKupic = MODUL_NOWOSCI_OKNA_KUPOWANIE;
}
if ( defined('MODUL_NOWOSCI_OKNA_OPIS') ) {
   $WyswietlOpis = MODUL_NOWOSCI_OKNA_OPIS;
}

$WybraneProdukty = Produkty::ProduktyModulowe($LimitZapytania, 'nowosci');

if (count($WybraneProdukty) > 0) {
      
    echo '<div class="OknaRwd Kol-' . $IloscKolumn . '">';

    for ($v = 0, $cs = count($WybraneProdukty); $v < $cs; $v++) {
        //
        echo '<article class="ProduktProsty OknoRwd">';

            // klasa do wysuwanego przycisku animacji
            echo '<div class="AnimacjaZobacz">';

                $Produkt = new Produkt( $WybraneProdukty[$v] );
                //              
                echo '<div class="Zobacz"><strong>' . $Produkt->info['link_szczegoly'] . '</strong></div>'; 
                //
                echo '<div class="Foto">'.$Produkt->fotoGlowne['zdjecie_link_ikony'].'</div>';
                //
                echo '<h3>' . $Produkt->info['link'] . '</h3>' . $Produkt->info['cena'];
                
                if ( $MoznaKupic == 'tak' ) {
              
                  // elementy kupowania
                  $Produkt->ProduktKupowanie();                  
              
                  echo '<div class="Zakup">';
                  
                      // jezeli jest aktywne kupowanie produktow
                      if ($Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak') {
                          //
                          if ( $Produkt->zakupy['ma_pola_tekstowe'] == '0' && $Produkt->zakupy['ma_cechy'] == '0' ) {
                            echo $Produkt->zakupy['input_ilosci'] . '<em>' . $Produkt->zakupy['jednostka_miary'] . '</em> ' . $Produkt->zakupy['przycisk_kup'];
                          } else {
                            echo $Produkt->zakupy['przycisk_szczegoly'];
                          }
                          //
                      }            
                      
                  echo '</div>'; 

                }  
                
                if ( $WyswietlOpis == 'tak' ) {
                     echo '<div class="Opis LiniaGorna">' . $Produkt->info['opis_krotki'] . '</div>';
                }
                
                //
                unset($Produkt);

            echo '</div>';
        
        echo '</article>';

    }

    echo '</div>';
    //
    echo '<div class="cl"></div>';

}

unset($WybraneProdukty, $IloscKolumn, $LimitZapytania, $MoznaKupic, $WyswietlOpis);
?>