<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_PROMOCJE_ANIMOWANE_ILOSC_PRODUKTOW;Ilość wyświetlanych w produktów;4;2,3,4,5,6,7,8,9,10}}
// {{MODUL_PROMOCJE_ANIMOWANE_ANIMACJA;Czy produkty mają się same animować;nie;tak,nie}}
// {{MODUL_PROMOCJE_ANIMOWANE_CZAS_CO_ILE;Co ile sekund ma się zmieniać animacja;4;3,4,5,6,7,8,9,10,12,15}}
//

// zmienne bez definicji
$LimitZapytania = 4;
$Animowac = 'nie';
$CzasAnimacji = 5000;

if ( defined('MODUL_PROMOCJE_ANIMOWANE_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)MODUL_PROMOCJE_ANIMOWANE_ILOSC_PRODUKTOW;
}
if ( defined('MODUL_PROMOCJE_ANIMOWANE_ANIMACJA') ) {
   $Animowac = MODUL_PROMOCJE_ANIMOWANE_ANIMACJA;
}
if ( defined('MODUL_PROMOCJE_ANIMOWANE_CZAS_CO_ILE') ) {
   $CzasAnimacji = (int)MODUL_PROMOCJE_ANIMOWANE_CZAS_CO_ILE * 1000;
}

$WybraneProdukty = Produkty::ProduktyModulowe($LimitZapytania, 'promocje');

if (count($WybraneProdukty) > 1) { 
      
    $LicznikWierszy = 1;
    
    echo '<div id="ModulPromocjeAnimowane">';
     
    echo '<ul class="AnimModulJeden">';
      
    for ($v = 0, $cs = count($WybraneProdukty); $v < $cs; $v++) {
        //
        echo '<li id="npa' . $LicznikWierszy . '">';
         
        //
        $Produkt = new Produkt( $WybraneProdukty[$v] , SZEROKOSC_OBRAZEK_MALY * 1.5, WYSOKOSC_OBRAZEK_MALY * 1.5, '', false );      
        //       
        //
        // ************************ wyglad produktu - poczatek **************************
        //
        
        echo '<div class="FotoPrawe">';
        
        echo '<div class="Skakanie">'.$Produkt->fotoGlowne['zdjecie_link_ikony'].'</div>';  
        
        echo '</div>';
        
        echo '<div class="ProdOpisSrodek" style="margin-right:' . ((SZEROKOSC_OBRAZEK_MALY * 1.5) + 45) . 'px">';
        
            echo '<div class="Znikanie">';

            echo '<h3>' . $Produkt->info['link'] . '</h3>';
            //
            echo '<div class="OpisKrotki">' . $Produkt->info['opis_krotki'] . '</div>';
            //
            echo $Produkt->info['cena'];
            
            echo '</div>';
            
        //
        echo '</div>';  

        echo '<div class="cl"></div>';
          
        unset($Produkt);
        //

        //
        // ************************ wyglad produktu - koniec **************************
        //   
        $LicznikWierszy++;
        //
        echo '</li>';
          
    }
    
    echo '</ul>';
    
    echo '<div id="ModulPromocjeAnimowanePrzyciski" class="ModulPrzyciskiSrodek">';
    
    //
    // generuje przyciski
    for ($f = 1, $g = count($WybraneProdukty); $f <= $g; $f++) {
      echo '<b id="p_npa'.$f.'"' . (($f == 1) ? ' class="On"' : '') . '></b>';
    }
    //
    echo '</div>';            
    
    echo '</div>';
    
    echo Wyglad::PrzegladarkaJavaScript( "$('#ModulPromocjeAnimowane').ModulAnimacjaSpadanie( { modul: 'ModulPromocjeAnimowane', przyciski: 'ModulPromocjeAnimowanePrzyciski', id: 'npa', html: 'li', czas: " . $CzasAnimacji . ", animacja: '" . $Animowac . "' } );" ); 

    unset($LicznikWierszy);
      
}

unset($LimitZapytania, $WybraneProdukty, $CzasAnimacji, $Animowac, $MoznaKupic); 
?>