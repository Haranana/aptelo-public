<?php
// czy jest zapytanie
if ($IloscProduktow > 0) { 

    // okresla szerokosc pojedynczego produktu w listingu - w %
    $SzerokoscPola = (int)(100 / $IloscProduktow);
    //    
    
    echo '<div class="ProduktyPopUp LiniaGorna' . ((PRODUKT_OKNO_PRODUKTY_SPOSOB_WYSWIETLANIA == 'statyczny' || $IloscProduktow < 4) ? ' ProduktyPopUpStatyczny' : ' ProduktyPopUpAnimowany') . '">';
    
        echo '<strong>' . $NaglowekPopUp . '</strong>';
    
        echo '<div class="LiniaDolna TabelaTbl" id="ProduktyOknoPopUp">';

        while ($info = $sql->fetch_assoc()) {

            echo '<div class="ProduktPopUp' . (($Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak') ? '' : ' ProduktBezZakupu') . (($GLOBALS['koszykKlienta']->SprawdzCzyDodanyDoKoszyka($Produkt->info['id'])) ? ' ProduktDodanyDoKoszyka' : '') . '" ' . ((PRODUKT_OKNO_PRODUKTY_SPOSOB_WYSWIETLANIA == 'statyczny' || $IloscProduktow < 4) ? 'style="width:' . $SzerokoscPola . '%"' : '') . '>';    
            
                  $ProduktPopUp = new Produkt( $info['products_id'], 100, 100, '', false );
                  
                  // elementy kupowania
                  $ProduktPopUp->ProduktKupowanie();  
                      
                  echo '<div class="ProduktPopUpRamka">';

                      echo '<div class="Foto">' . $ProduktPopUp->fotoGlowne['zdjecie_link'] . '</div>';
                      //
                      echo '<div class="ProdCena">';
                      
                          echo '<h3>' . $ProduktPopUp->info['link'] . '</h3>';
                          
                          echo '<div class="ProduktCena">' . $ProduktPopUp->info['cena'] . '</div>';

                      echo '</div>';

                      echo '<div class="ZakupKontener">';
                  
                          echo '<div class="Zakup">';
                          
                              if ($ProduktPopUp->zakupy['mozliwe_kupowanie'] == 'tak' || $ProduktPopUp->zakupy['pokaz_koszyk'] == 'tak') {
                          
                                  if ( $ProduktPopUp->zakupy['ma_pola_tekstowe'] == '0' && $ProduktPopUp->zakupy['ma_cechy'] == '0' ) {
                                      echo '<span class="IloscProduktu">' . $ProduktPopUp->zakupy['input_ilosci'] . '<em>' . $ProduktPopUp->zakupy['jednostka_miary'] . '</em> </span>' . $ProduktPopUp->zakupy['przycisk_kup'];
                                  } else {
                                      echo $ProduktPopUp->zakupy['przycisk_szczegoly'];
                                  }
                              
                              } else {
                                
                                  echo $ProduktPopUp->info['zapytanie_o_produkt'];
                                
                              }

                          echo '</div>';  
                          
                      echo '</div>'; 
                       

                  echo '</div>';
                  
                  unset($ProduktPopUp);
                    
            echo '</div>';

        }
        
        echo '</div>'; 

    echo '</div>'; 

    unset($info, $SzerokoscPola);
      
}

$GLOBALS['db']->close_query($sql); 

unset($IloscProduktow, $zapytanie);  
?>