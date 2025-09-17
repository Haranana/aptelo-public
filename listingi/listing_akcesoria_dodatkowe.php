<?php
// czy jest zapytanie
if ($IloscProduktow > 0) { 

    echo '<div class="OknaRwd Kol-' . KARTA_PRODUKTU_ILOSC_KOLUMN_AKCESORIA . ' ListingOknaKontener">';

    while ($info = $sql->fetch_assoc()) {
      
        $ProduktAkcesoria = new Produkt( $info['products_id'] );
        
        // elementy kupowania
        $ProduktAkcesoria->ProduktKupowanie();    
                
        echo '<div id="prd-' . rand(1,1000) . '-' . $info['products_id'] . '" class="Okno OknoRwd' . (($ProduktAkcesoria->zakupy['mozliwe_kupowanie'] == 'tak' || $ProduktAkcesoria->zakupy['pokaz_koszyk'] == 'tak') ? '' : ' ProduktBezZakupu') . (($GLOBALS['koszykKlienta']->SprawdzCzyDodanyDoKoszyka($ProduktAkcesoria->info['id'])) ? ' ProduktDodanyDoKoszyka' : '') . '">';
        
            echo '<div class="ElementListingRamka">';
     
                echo '<div class="Foto">' . $ProduktAkcesoria->fotoGlowne['zdjecie_link_ikony'] . '</div>';
                //
                echo '<div class="ProdCena">';
                
                    echo '<h3>' . $ProduktAkcesoria->info['link'] . '</h3>';
                    
                    echo '<div class="ProduktCena">' . $ProduktAkcesoria->info['cena'] . '</div>';
                
                echo '</div>';
                
                echo '<div class="ZakupKontener">';
                            
                    echo '<div class="Zakup">';
                    
                        // jezeli jest aktywne kupowanie produktow
                        if ($ProduktAkcesoria->zakupy['mozliwe_kupowanie'] == 'tak' || $ProduktAkcesoria->zakupy['pokaz_koszyk'] == 'tak') {
                            //
                            echo $ProduktAkcesoria->zakupy['input_ilosci'] . '<em>' . $ProduktAkcesoria->zakupy['jednostka_miary'] . '</em> ' . $ProduktAkcesoria->zakupy['przycisk_kup'];
                            //
                        } else {
                            //
                            echo $ProduktAkcesoria->info['zapytanie_o_produkt'];
                            //
                        }
                        
                    echo '</div>';  
                    
                echo '</div>';

            echo '</div>';
                
        echo '</div>';
        
        unset($ProduktAkcesoria);

    }

    echo '</div>';
    //
    echo '<div class="cl"></div>';

    unset($info);
      
}

$GLOBALS['db']->close_query($sql); 

unset($IloscProduktow, $zapytanie);  
?>