<?php
// czy jest zapytanie
if ($IloscProduktow > 0) { 

    echo '<div class="OknaRwd Kol-' . KARTA_PRODUKTU_ILOSC_KOLUMN_POZOSTALE . ' KolMobile-' . LISTING_ILOSC_KOLUMN_MOBILE . ' ListingWierszeKartaProduktuKontener">';

    while ($info = $sql->fetch_assoc()) {
      
        $ProduktLista = new Produkt( $info['products_id'] );
        
        // elementy kupowania
        $ProduktLista->ProduktKupowanie();    
                
        echo '<div id="prd-' . rand(1,1000) . '-' . $info['products_id'] . '" class="Okno OknoRwd' . (($ProduktLista->zakupy['mozliwe_kupowanie'] == 'tak' || $ProduktLista->zakupy['pokaz_koszyk'] == 'tak') ? '' : ' ProduktBezZakupu') . (($GLOBALS['koszykKlienta']->SprawdzCzyDodanyDoKoszyka($ProduktLista->info['id'])) ? ' ProduktDodanyDoKoszyka' : '') . '">';
        
            echo '<div class="ElementListingRamka">';
    
                echo '<div class="Foto">'.$ProduktLista->fotoGlowne['zdjecie_link_ikony'].'</div>';
                //
                echo '<div class="ProdCena">';
                
                    echo '<h3>' . $ProduktLista->info['link'] . '</h3>';
                    
                    echo '<div class="ProduktCena">' . $ProduktLista->info['cena'] . '</div>';

                echo '</div>';
                
                echo '<div class="ZakupKontener">';
            
                    echo '<div class="Zakup">';
                    
                        // jezeli jest aktywne kupowanie produktow
                        if ($ProduktLista->zakupy['mozliwe_kupowanie'] == 'tak' || $ProduktLista->zakupy['pokaz_koszyk'] == 'tak') {
                          
                            if ( $ProduktLista->zakupy['ma_pola_tekstowe'] == '0' && $ProduktLista->zakupy['ma_cechy'] == '0' ) {
                                echo $ProduktLista->zakupy['input_ilosci'] . '<em>' . $ProduktLista->zakupy['jednostka_miary'] . '</em> ' . $ProduktLista->zakupy['przycisk_kup'];
                            } else {
                                echo $ProduktLista->zakupy['przycisk_szczegoly'];
                            }
                
                        } else {

                            echo $ProduktLista->info['zapytanie_o_produkt'];

                        }    
                        
                    echo '</div>';  
                    
                echo '</div>'; 

            echo '</div>';
                
        echo '</div>';
        
        unset($ProduktLista);

    }

    echo '</div>';
    //
    echo '<div class="cl"></div>';

    unset($info);
 
}

$GLOBALS['db']->close_query($sql); 

unset($IloscProduktow, $zapytanie);  
?>