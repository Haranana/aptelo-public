<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_HITY_ANIMOWANE_ILOSC_PRODUKTOW;Ilość wyświetlanych w produktów;4;2,3,4,5,6,7,8,9,10}}
// {{MODUL_HITY_ANIMOWANE_ANIMACJA;Czy produkty mają się same animować;nie;tak,nie}}
// {{MODUL_HITY_ANIMOWANE_CZAS_CO_ILE;Co ile sekund ma się zmieniać animacja;4;3,4,5,6,7,8,9,10,12,15}}
// {{MODUL_HITY_ANIMOWANE_KUPOWANIE;Czy wyświetać możliwość zakupu produktu;tak;tak,nie}}
//

// zmienne bez definicji
$LimitZapytania = 4;
$Animowac = 'nie';
$CzasAnimacji = 5000;
$MoznaKupic = 'tak';

if ( defined('MODUL_HITY_ANIMOWANE_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)MODUL_HITY_ANIMOWANE_ILOSC_PRODUKTOW;
}
if ( defined('MODUL_HITY_ANIMOWANE_ANIMACJA') ) {
   $Animowac = MODUL_HITY_ANIMOWANE_ANIMACJA;
}
if ( defined('MODUL_HITY_ANIMOWANE_CZAS_CO_ILE') ) {
   $CzasAnimacji = (int)MODUL_HITY_ANIMOWANE_CZAS_CO_ILE * 1000;
}
if ( defined('MODUL_HITY_ANIMOWANE_KUPOWANIE') ) {
   $MoznaKupic = MODUL_HITY_ANIMOWANE_KUPOWANIE;
}

$WybraneProdukty = Produkty::ProduktyModulowe($LimitZapytania, 'hity');

if (count($WybraneProdukty) > 1) { 
      
    $LicznikWierszy = 1;
    
    echo '<div id="ModulHityAnimowane">';
    
    echo '<div id="ModulHityAnimowanePrzyciski" class="ModulPrzyciski">';
    //
    // generuje przyciski
    for ($f = 1, $g = count($WybraneProdukty); $f <= $g; $f++) {
      echo '<b id="p_mha'.$f.'"' . (($f == 1) ? ' class="On"' : '') . '></b>';
    }
    //
    echo '</div>';
     
    echo '<ul class="AnimModulJeden">';
      
    for ($v = 0, $cs = count($WybraneProdukty); $v < $cs; $v++) {
        //
        echo '<li id="mha' . $LicznikWierszy . '">';
         
        //
        $Produkt = new Produkt( $WybraneProdukty[$v] , SZEROKOSC_OBRAZEK_MALY * 1.5, WYSOKOSC_OBRAZEK_MALY * 1.5 );      
        //       
        //
        // ************************ wyglad produktu - poczatek **************************
        //
        echo '<article class="Kont">';

            echo '<div class="Foto" style="width:' . (SZEROKOSC_OBRAZEK_MALY * 1.6) . 'px">'.$Produkt->fotoGlowne['zdjecie_link_ikony'].'</div>';
            //
            echo '<div class="ProdOpis">';

                echo '<h3>' . $Produkt->info['link'] . '</h3>';
                //
                echo '<div class="OpisKrotki">' . $Produkt->info['opis_krotki'] . '</div>';
                //
                echo $Produkt->info['cena'];
                
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
                
            //
            echo '</div>';              
              
            unset($Produkt);
            //
              
        echo '</article>';
        //
        // ************************ wyglad produktu - koniec **************************
        //   
        $LicznikWierszy++;
        //
        echo '</li>';
          
    }
    
    echo '</ul>';
    
    echo '</div>';

    echo Wyglad::PrzegladarkaJavaScript( "$('#ModulHityAnimowane').ModulAnimacja( { modul: 'ModulHityAnimowane', przyciski: 'ModulHityAnimowanePrzyciski', id: 'mha', html: 'li', czas: " . $CzasAnimacji . ", animacja: '" . $Animowac . "' } );" );   

    unset($LicznikWierszy);
      
}

unset($LimitZapytania, $WybraneProdukty, $CzasAnimacji, $Animowac, $MoznaKupic); 
?>