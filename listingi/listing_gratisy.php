<?php
foreach ( $Gratisy As $ProduktGratisowy ) {
    //
    // ************************ wyglad produktu - poczatek **************************
    //

    $Produkt = new Produkt( $ProduktGratisowy['id_gratisu'], (SZEROKOSC_OBRAZEK_MALY / 2), (WYSOKOSC_OBRAZEK_MALY / 2) );

    // elementy kupowania
    $Produkt->ProduktKupowanie();           

    // bedzie wyswietlony tylko jezeli mozna kupic
    
    if ($Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak') {
      
        echo '<div class="Wiersz LiniaDolna">';
        
            echo '<div class="ElementGratisRamka">';

                // okreslanie ceny gratisu
                $CenaGratisu = $GLOBALS['waluty']->FormatujCene($ProduktGratisowy['cena_gratisu'], 0, 0, $Produkt->info['id_waluty'], false);
            
                $CenaBruttoProduktu = $GLOBALS['waluty']->WyswietlFormatCeny($Produkt->info['cena_brutto_bez_formatowania'], $_SESSION['domyslnaWaluta']['id'], true, false);
                $CenaBruttoGratisu = $GLOBALS['waluty']->WyswietlFormatCeny($CenaGratisu['brutto'], $_SESSION['domyslnaWaluta']['id'], true, false);
                        
                $Oszczedzasz = $GLOBALS['waluty']->WyswietlFormatCeny($Produkt->info['cena_brutto_bez_formatowania'] - $CenaGratisu['brutto'], $_SESSION['domyslnaWaluta']['id'], true, false);
                
                // pola ukryte - obowiazkowe
                echo '<input type="hidden" id="produkt_cena_' . $Produkt->zakupy['id_unikat'] . $Produkt->info['id'] . '" value="' . $CenaGratisu['brutto'] . '" />';
                
                echo $Produkt->zakupy['input_ilosci_gratis'];             
                        
                echo '<div class="ProdCena">';
                
                    echo '<div class="Foto">'.$Produkt->fotoGlowne['zdjecie_link'].'</div>';     
                
                    echo '<div class="NazwaKoszyk">';
                    
                        echo '<h3>' . $Produkt->info['link'] . '</h3>';

                        echo $Produkt->zakupy['przycisk_kup_gratis'];
                        
                    echo '</div>';
                    
                    echo '<div class="InfoCena">';

                        $InfoCena = str_replace('{ILOSC_GRATISOW}', $Produkt->zakupy['ilosc_gratisu'] . ' ' . $Produkt->zakupy['jednostka_miary'], (string)$GLOBALS['tlumacz']['GRATIS_CENA']);
                        $InfoCena = str_replace('{CENA_BRUTTO_PRODUKTU}', '<strong>' . $CenaBruttoProduktu . '</strong>', (string)$InfoCena);
                        $InfoCena = str_replace('{CENA_BRUTTO_GRATISU}', '<strong>' . $CenaBruttoGratisu . '</strong>', (string)$InfoCena);
                    
                        echo $InfoCena . '<div style="height:20px" class="GratisOdstep"></div>';

                        // jezeli kwota oszczedzasz jest dodatnia
                        if ( $Produkt->info['cena_brutto_bez_formatowania'] - $CenaGratisu['brutto'] >= 0 ) {

                             echo '{__TLUMACZ:GRATIS_OSZCZEDZASZ} <strong>' . $Oszczedzasz . '</strong>';
                             
                        }

                    echo '</div>';
                
                echo '</div>';
                //
                unset($InfoCena, $CenaGratisu, $Oszczedzasz, $CenaBruttoProduktu, $CenaBruttoGratisu);
            
            echo '</div>';
            
        echo '</div>';

    }
    
    unset($Produkt);

    //
    // ************************ wyglad produktu - koniec **************************
    //

}   
?>