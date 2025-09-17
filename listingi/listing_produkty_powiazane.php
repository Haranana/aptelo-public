<?php
// czy jest zapytanie
if ($IloscProduktow > 0) { 

    echo '<div class="OknaRwd Kol-' . KARTA_PRODUKTU_ILOSC_KOLUMN_AKCESORIA . ' ListingPowiazaneKontener">';

    while ($info = $sql->fetch_assoc()) {
      
        $ProduktPowiazany = new Produkt( $info['products_id'] );
        
        // elementy kupowania
        $ProduktPowiazany->ProduktKupowanie(); 
        
        echo '<div id="prd-' . rand(1,1000) . '-' . $info['products_id'] . '" class="Okno OknoRwd' . (($ProduktPowiazany->zakupy['mozliwe_kupowanie'] == 'tak' || $ProduktPowiazany->zakupy['pokaz_koszyk'] == 'tak') ? '' : ' ProduktBezZakupu') . (($GLOBALS['koszykKlienta']->SprawdzCzyDodanyDoKoszyka($ProduktPowiazany->info['id'])) ? ' ProduktDodanyDoKoszyka' : '') . '">';

            echo '<div class="ElementListingRamka">';
              
                echo '<div class="Foto">' . $ProduktPowiazany->fotoGlowne['zdjecie_link_ikony'] . '</div>';
                //
                echo '<div class="ProdCena">';
                
                    echo '<h3>' . $ProduktPowiazany->info['link'] . '</h3>';
                    
                    echo '<div class="ProduktCena">' . $ProduktPowiazany->info['cena'] . '</div>';

                echo '</div>';
                
                echo '<div class="ZakupKontener">';
            
                    echo '<div class="Zakup">';
                    
                        // jezeli jest aktywne kupowanie produktow
                        if ($ProduktPowiazany->zakupy['mozliwe_kupowanie'] == 'tak' || $ProduktPowiazany->zakupy['pokaz_koszyk'] == 'tak') {
                    
                            if ( $ProduktPowiazany->zakupy['ma_pola_tekstowe'] == '0' && $ProduktPowiazany->zakupy['ma_cechy'] == '0' ) {
                                echo $ProduktPowiazany->zakupy['input_ilosci'] . '<em>' . $ProduktPowiazany->zakupy['jednostka_miary'] . '</em> ' . $ProduktPowiazany->zakupy['przycisk_kup'];
                            } else {
                                echo $ProduktPowiazany->zakupy['przycisk_szczegoly'];
                            }
                
                        } else {

                            echo $ProduktPowiazany->info['zapytanie_o_produkt'];

                        }                          
                          
                    echo '</div>';  
                    
                echo '</div>'; 
             

            echo '</div>';
                
        echo '</div>';
        
        unset($ProduktPowiazany);

    }

    echo '</div>';
    //
    echo '<div class="cl"></div>';

    unset($info);
      
}

$GLOBALS['db']->close_query($sql); 

unset($IloscProduktow, $zapytanie);  
?>