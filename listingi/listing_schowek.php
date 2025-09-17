<?php
// czy jest zapytanie
if ($IloscProduktow > 0) { 

    echo '<div class="ListingSchowekKontener">';

    foreach ( $GLOBALS['schowekKlienta']->IloscProduktowTablicaId AS $IdSchowka ) {
    
        $Produkt = new Produkt( $IdSchowka );
           
        if ( $Produkt->CzyJestProdukt ) {
          
            // elementy kupowania 
            $Produkt->ProduktKupowanie();
            
            echo '<div id="prd-' . rand(1,1000) . '-' . $IdSchowka . '" class="SchowekPrd LiniaDolna' . (($Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak') ? '' : ' ProduktBezZakupu') . (($GLOBALS['koszykKlienta']->SprawdzCzyDodanyDoKoszyka($Produkt->info['id'])) ? ' ProduktDodanyDoKoszyka' : '') . '">';
           
                echo '<div class="Foto" style="width:' . ((int)SZEROKOSC_OBRAZEK_MALY + 50) . 'px">'.$Produkt->fotoGlowne['zdjecie_link_ikony'].'</div>';
                //
                echo '<div class="ProdCena LiniaPrawa" style="width:calc(100% - ' . ((int)SZEROKOSC_OBRAZEK_MALY + 170) . 'px)">';
                
                    echo '<h3>' . $Produkt->info['link'] . '</h3>';
                    
                    echo '<div class="ProduktCena">' . $Produkt->info['cena'] . '</div>';
                    
                    echo '<div class="Opis LiniaOpisu">' . $Produkt->info['opis_krotki'] . '</div>'; 
                    
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
                            echo '<div class="cls"></div>';
                            //
                            //
                        } else {
                            //
                            echo $Produkt->info['zapytanie_o_produkt'];
                            //
                        }
                        
                        // jezeli jest wlaczona porownywarka produktow
                        if (LISTING_POROWNYWARKA_PRODUKTOW == 'tak' && isset($_SESSION['produktyPorownania'])) {
                            //
                            // jezeli produkt byl dodany do porownania
                            if (in_array($Produkt->info['id'], (array)$_SESSION['produktyPorownania'])) {
                                echo '<span onclick="Porownaj(' . $Produkt->info['id'] . ',\'wy\')" id="id' . $Produkt->info['id'] . '" class="PorownajWlaczone ToolTip" title="{__TLUMACZ:LISTING_DODANY_DO_POROWNANIA}">{__TLUMACZ:LISTING_DODAJ_DO_POROWNANIA}</span>';
                              } else {
                                echo '<span onclick="Porownaj(' . $Produkt->info['id'] . ',\'wl\')" id="id' . $Produkt->info['id'] . '" class="Porownaj ToolTip" title="{__TLUMACZ:LISTING_DODAJ_DO_POROWNANIA}">{__TLUMACZ:LISTING_DODAJ_DO_POROWNANIA}</span>';
                            }
                            //
                        }
                        
                    echo '</div>';
                
                echo '</div>';
                
                echo '<div class="UsunSchowek" style="width:120px"><span onclick="UsunZeSchowka(' . $Produkt->info['id'] . ')" class="Schowek">{__TLUMACZ:SCHOWEK_USUN_ZE_SCHOWKA}</span></div>';

            echo '</div>';

        } else {
        
            $GLOBALS['schowekKlienta']->UsunZeSchowka( $IdSchowka );
        
        }

        unset($Produkt);
   
    }
    
    echo '</div>';

    unset($info);
      
} else {

    echo '<div id="BrakProduktow" class="Informacja">{__TLUMACZ:BLAD_BRAK_PRODUKTOW}</div>';
  
} 
?>