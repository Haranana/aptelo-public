<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_NOWOSCI_PRZEWIJANE_ILOSC_PRODUKTOW;Ilość wyświetlanych w produktów;4;4,6,8,9,10,12,15,18,20}}
// {{MODUL_NOWOSCI_PRZEWIJANE_ILOSC_KOLUMN;W ilu kolumnach mają być wyświetlane produkty;2;2,3,4}}
// {{MODUL_NOWOSCI_PRZEWIJANE_ANIMACJA;Czy produkty mają się same animować;nie;tak,nie}}
// {{MODUL_NOWOSCI_PRZEWIJANE_RODZAJ_ANIMACJI;W jaki sposób mają być animowane produkty;zanikanie;zanikanie,animacja w pionie,animacja w poziomie}}
// {{MODUL_NOWOSCI_PRZEWIJANE_CZAS_CO_ILE;Co ile sekund ma się zmieniać animacja;4;3,4,5,6,7,8,9,10,12,15}}
// {{MODUL_NOWOSCI_PRZEWIJANE_KUPOWANIE;Czy wyświetać możliwość zakupu produktu;tak;tak,nie}}
// {{MODUL_NOWOSCI_PRZEWIJANE_PRZYCISKI;W jakiej formie wyświetlać nawigację modułu;przyciski;przyciski,strzałki}}
//

// zmienne bez definicji
$LimitZapytania = 6;
$Animowac = 'nie';
$RodzajAnimacji = 'zanikanie';
$IloscKolumn = 2;
$CzasAnimacji = 5000;
$MoznaKupic = 'tak';
$FormaNawigacji = 'przyciski';

if ( defined('MODUL_NOWOSCI_PRZEWIJANE_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)MODUL_NOWOSCI_PRZEWIJANE_ILOSC_PRODUKTOW;
}
if ( defined('MODUL_NOWOSCI_PRZEWIJANE_ILOSC_KOLUMN') ) {
   $IloscKolumn = (int)MODUL_NOWOSCI_PRZEWIJANE_ILOSC_KOLUMN;
}
if ( defined('MODUL_NOWOSCI_PRZEWIJANE_ANIMACJA') ) {
   $Animowac = MODUL_NOWOSCI_PRZEWIJANE_ANIMACJA;
}
if ( defined('MODUL_NOWOSCI_PRZEWIJANE_RODZAJ_ANIMACJI') ) {
    $RodzajAnimacji = MODUL_NOWOSCI_PRZEWIJANE_RODZAJ_ANIMACJI;
}
switch ($RodzajAnimacji) {
    case "zanikanie":
        $RodzajAnimacji = 'fade';
        break;
    case "animacja w pionie":
        $RodzajAnimacji = 'scrolltop';
        break;        
    case "animacja w poziomie":
        $RodzajAnimacji = 'scrollleft';
        break;             
}
if ( defined('MODUL_NOWOSCI_PRZEWIJANE_CZAS_CO_ILE') ) {
   $CzasAnimacji = (int)MODUL_NOWOSCI_PRZEWIJANE_CZAS_CO_ILE * 1000;
}
if ( defined('MODUL_NOWOSCI_PRZEWIJANE_KUPOWANIE') ) {
   $MoznaKupic = MODUL_NOWOSCI_PRZEWIJANE_KUPOWANIE;
}
if ( defined('MODUL_NOWOSCI_PRZEWIJANE_PRZYCISKI') ) {
   $FormaNawigacji = MODUL_NOWOSCI_PRZEWIJANE_PRZYCISKI;
}

$WybraneProdukty = Produkty::ProduktyModulowe($LimitZapytania, 'nowosci');

if (count($WybraneProdukty) > 1) {

    echo '<div id="ModulNowosciPrzewijane" class="AnimModul">';

    // jezeli forma nawigacji to przyciski    
    if ( $FormaNawigacji == 'przyciski' ) {
    
        echo '<div class="StronyAnim">';

        for ( $f = 1; $f <= ceil(count($WybraneProdukty) / $IloscKolumn); $f++ ) {
             echo '<b ' . (($f == 1) ? 'class="On"' : '') . '>' . $f . '</b>';
        }
        
        echo '</div>';
        
        echo '<div class="cl"></div>';
    
    }
    
    // jezeli forma nawigacji to strzalki  
    if ( $FormaNawigacji == 'strzałki' ) {
      
        echo '<div class="LewaStrzalka"></div>';
        echo '<div class="PrawaStrzalka"></div>';
      
        echo '<div class="StronyStrzalki">';
        
    }    
    
    echo '<ul><li>';
    
        echo '<div class="ElementyAnimacji Kol-' . $IloscKolumn . '">';
        
        for ($v = 0, $cs = count($WybraneProdukty); $v < $cs; $v++) {
        
            echo '<article class="ProduktProsty">';
            
                $Produkt = new Produkt( $WybraneProdukty[$v] );
                //              
                echo '<div class="Foto">'.$Produkt->fotoGlowne['zdjecie_link_ikony'].'</div>';
                //
                echo '<h3>' . $Produkt->info['link'] . '</h3>' . $Produkt->info['cena'];
                //
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
                  
                unset($Produkt);

            echo '</article>';
            
            //
            // ************************ wyglad produktu - koniec **************************
            //

        }
        
        echo '</div>';
    
    echo '</li></ul>';   
    
    echo '<div class="cl"></div>';
    
    // jezeli forma nawigacji to strzalki  
    if ( $FormaNawigacji == 'strzałki' ) {
      
        echo '</div>';
        
    }
    
    echo '</div>';
    
    echo Wyglad::PrzegladarkaJavaScript( "$('#ModulNowosciPrzewijane').ModulPrzewijanie( { modul: 'ModulNowosciPrzewijane', id: 'mnp', typ: '" . $RodzajAnimacji . "', czas: " . $CzasAnimacji . ", animacja: '" . $Animowac . "', kolumny: " . $IloscKolumn . " } );" );

}

unset($CzasAnimacji, $WybraneProdukty, $IloscKolumn, $LimitZapytania, $RodzajAnimacji, $Animowac, $CzasAnimacji, $FormaNawigacji);
?>