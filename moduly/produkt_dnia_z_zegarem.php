<?php
if ( PRODUKT_DNIA_STATUS == 'tak' ) {

    if (isset($GLOBALS['produkt_dnia'][date('Y-m-d', time())])) { 

        $Produkt = new Produkt( $GLOBALS['produkt_dnia'][date('Y-m-d', time())]['id_produktu'] );
        //       
        if ($Produkt->CzyJestProdukt == true) {
          
            if ($Produkt->info['produkt_dnia'] == 'tak' ) {
          
                $IloscSekund = FunkcjeWlasnePHP::my_strtotime(date('Y-m-d', time()) . ' 23:59:59') - time();            
                //      
                if ( $IloscSekund > 0 ) {
                    //
                    // ************************ wyglad produktu - poczatek **************************
                    //
                    echo '<article class="ProduktWiersz">';
                   
                        echo '<div class="Foto">' . $Produkt->fotoGlowne['zdjecie_link_ikony'] . '</div>';
                        //
                        echo '<div class="ProdOpis" style="margin-left:' . (SZEROKOSC_OBRAZEK_MALY+50) . 'px">';
                        
                        echo '<h3>' . $Produkt->info['link'] . '</h3>' . $Produkt->info['cena'];
                        
                        echo '<div style="font-size:120%; margin:10px 0px 20px 0px"><b>{__TLUMACZ:OSZCZEDZASZ}: ' . $Produkt->info['rabat_produktu'] . '%</b></div>';

                        // elementy kupowania
                        $Produkt->ProduktKupowanie();                    

                        // jezeli jest aktywne kupowanie produktow
                        if ($Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak') {
                            //
                            echo '<div class="Zakup">';
                            
                                if ( $Produkt->zakupy['ma_pola_tekstowe'] == '0' && $Produkt->zakupy['ma_cechy'] == '0' ) {
                                    echo $Produkt->zakupy['input_ilosci'] . '<em>' . $Produkt->zakupy['jednostka_miary'] . '</em> ' . $Produkt->zakupy['przycisk_kup'];
                                } else {
                                    echo $Produkt->zakupy['przycisk_szczegoly'];
                                }
                            
                            echo '</div>'; 
                            //
                            echo '<div class="cl"></div>';
                            //
                        }            
                        
                        //
                        echo '<div class="OpisKrotki">' . $Produkt->info['opis_krotki'] . '</div>';
                        //
                        
                        echo '</div>';

                        echo '<div class="Odliczanie" style="margin-left:' . (SZEROKOSC_OBRAZEK_MALY+50) . 'px"><div style="margin-bottom:8px">{__TLUMACZ:PRODUKT_DNIA_INFO}</div><span id="sekundy_produkt_dnia_'.$Produkt->info['id'].'"></span></div>';

                        echo Wyglad::PrzegladarkaJavaScript( 'odliczaj("sekundy_produkt_dnia_' . $Produkt->info['id'] . '",' . $IloscSekund . ',\'{__TLUMACZ:LICZNIK_PROMOCJI_DZIEN}\',\'{__TLUMACZ:LICZNIK_PROMOCJI_JEDEN_DZIEN}\')' );           
                        
                        unset($Produkt, $SredniaOcena);
                        //
                        
                    echo '</article>';
                    //
                    // ************************ wyglad produktu - koniec **************************
                    //
                }
                
            }
            
        }
        
        unset($Produkt);

    }
    
}
?>