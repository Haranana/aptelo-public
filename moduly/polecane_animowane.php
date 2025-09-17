<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_POLECANE_ANIMOWANE_ILOSC_PRODUKTOW;Ilość wyświetlanych w produktów;4;2,3,4,5,6,7,8,9,10}}
// {{MODUL_POLECANE_ANIMOWANE_ANIMACJA;Czy produkty mają się same animować;nie;tak,nie}}
// {{MODUL_POLECANE_ANIMOWANE_CZAS_CO_ILE;Co ile sekund ma się zmieniać animacja;4;3,4,5,6,7,8,9,10,12,15}}
// {{MODUL_POLECANE_ANIMOWANE_KUPOWANIE;Czy wyświetać możliwość zakupu produktu;tak;tak,nie}}
//

// zmienne bez definicji
$LimitZapytania = 4;
$Animowac = 'nie';
$CzasAnimacji = 5000;
$MoznaKupic = 'tak';

if ( defined('MODUL_POLECANE_ANIMOWANE_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)MODUL_POLECANE_ANIMOWANE_ILOSC_PRODUKTOW;
}
if ( defined('MODUL_POLECANE_ANIMOWANE_ANIMACJA') ) {
   $Animowac = MODUL_POLECANE_ANIMOWANE_ANIMACJA;
}
if ( defined('MODUL_POLECANE_ANIMOWANE_CZAS_CO_ILE') ) {
   $CzasAnimacji = (int)MODUL_POLECANE_ANIMOWANE_CZAS_CO_ILE * 1000;
}
if ( defined('MODUL_POLECANE_ANIMOWANE_KUPOWANIE') ) {
   $MoznaKupic = MODUL_POLECANE_ANIMOWANE_KUPOWANIE;
}

$WybraneProdukty = Produkty::ProduktyModulowe($LimitZapytania, 'polecane');

if (count($WybraneProdukty) > 1) { 

    $LicznikWierszy = 1;
    
    echo '<div id="ModulPolecaneAnimowane">';
    
    echo '<div id="ModulPolecaneAnimowanePrzyciski" class="ModulPrzyciski">';
    //
    // generuje przyciski
    for ($f = 1, $g = count($WybraneProdukty); $f <= $g; $f++) {
      echo '<b id="p_mpa'.$f.'"' . (($f == 1) ? ' class="On"' : '') . '></b>';
    }
    //
    echo '</div>';
     
    echo '<ul class="AnimModulJeden">';
      
    for ($v = 0, $cs = count($WybraneProdukty); $v < $cs; $v++) {
        //
        echo '<li id="mpa' . $LicznikWierszy . '">';
         
        //
        $Produkt = new Produkt( $WybraneProdukty[$v] , SZEROKOSC_OBRAZEK_MALY * 1.5, WYSOKOSC_OBRAZEK_MALY * 1.5 );      
        //       
        //
        // ************************ wyglad produktu - poczatek **************************
        //
        echo '<article class="Kont">';

            echo '<div class="Foto">'.$Produkt->fotoGlowne['zdjecie_link_ikony'].'</div>';
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
              
            unset($Produkt, $SredniaOcena);
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

    echo Wyglad::PrzegladarkaJavaScript( "$('#ModulPolecaneAnimowane').ModulAnimacja( { modul: 'ModulPolecaneAnimowane', przyciski: 'ModulPolecaneAnimowanePrzyciski', id: 'mpa', html: 'li', czas: " . $CzasAnimacji . ", animacja: '" . $Animowac . "' } );" );

    unset($LicznikWierszy, $WybraneProdukty);
      
}

unset($LimitZapytania, $CzasAnimacji, $Animowac, $MoznaKupic); 
?>