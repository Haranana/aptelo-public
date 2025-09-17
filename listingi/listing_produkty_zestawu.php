<?php

$z = 0;
      
foreach ( $zestawyProduktu as $zestaw ) {
  
    echo '<div class="ProduktZestawPozycja">';

        echo '<div class="ProduktZestaw">';
        
            echo '<div class="ProduktZestawFoto">';
                
                foreach ( $zestaw['produkty'] as $produkt_zestawu ) {
                  
                    echo '<div>' . $produkt_zestawu['foto'] . '</div>';

                }

            echo '</div>';
        
            echo '<div class="ProduktZestawLinki">';
            
                echo '<h4>' . $zestaw['link'] . '</h4>';
                
                foreach ( $zestaw['produkty'] as $produkt_zestawu ) {
                  
                    echo '<div>
                    
                              <div>' . $produkt_zestawu['ilosc'] . ' x ' . $produkt_zestawu['link'] . '</div>
                              
                              <div>' . $produkt_zestawu['cena'] . '</div>
                              
                          </div>';

                }
                
                echo '<div class="ProduktyZestawuSuma">
                
                          <div><a class="przycisk" href="' . $zestaw['adres_seo'] . '">' . $GLOBALS['tlumacz']['ZOBACZ_SZCZEGOLY'] . '</a></div>
                          
                          <div>';
                          
                              echo $zestaw['cena'];
                              
                              if ( (float)$zestaw['taniej_wartosc'] > 0 ) {
                                
                                    echo '<div class="ProduktyZestawuTaniej">' . $GLOBALS['tlumacz']['W_ZESTAWIE_TANIEJ'] . ' <b>' . $zestaw['taniej_kwota'] . '</b></div>';
                                
                              }
                              
                          echo '</div>
                
                      </div>';

            echo '</div>';
            
        echo '</div>';
        
    echo '</div>';
    
    $z++;
            
}

if ( $z > 1 ) {
     
     echo '<div class="WszystkieZestawy">
     
               <label for="CheckboxZestaw">' . $GLOBALS['tlumacz']['ZOBACZ_WSZYSTKIE'] . '</label>
             
           </div>';
     
}
?>