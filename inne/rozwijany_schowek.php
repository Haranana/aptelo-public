<?php
if ( isset($GLOBALS['schowekKlienta']) && $GLOBALS['schowekKlienta']->IloscProduktow > 0 ) {

    // generuje tablice globalne z nazwami cech
    Funkcje::TabliceCech();  
    
    echo '<div class="RozwinietaWersjaPelna RozwinietaWersja">';
    
        foreach ( $GLOBALS['schowekKlienta']->IloscProduktowTablicaId AS $IdSchowka ) {
        
            echo '<div class="ZawartoscKoszykaSchowkaRozwijane">';

                $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $IdSchowka ) );

                echo '<div class="ZawartoscKoszykaSchowkaFoto">' . Funkcje::pokazObrazek($Produkt->fotoGlowne['plik_zdjecia'], $Produkt->info['nazwa'], 150, 150, array()) . '</div>';

                echo '<div class="ZawartoscKoszykaSchowkaDane">';
                
                    echo $Produkt->info['link'];

                    echo '<div class="CenyRozwinietyKoszykSchowek">';
                    
                        echo $GLOBALS['waluty']->PokazCene($Produkt->info['cena_brutto_bez_formatowania'], $Produkt->info['cena_netto_bez_formatowania'], 0, $_SESSION['domyslnaWaluta']['id']);

                    echo '</div>';
                    
                echo '</div>';
                
                unset($Produkt);

            echo '</div>';

        }

        $ZawartoscSchowka = $GLOBALS['schowekKlienta']->WartoscProduktowSchowka();

        echo '<div class="ZawartoscKoszykaSchowkaSumaFixed">
          
                <div class="ZawartoscKoszykaSchowkaSuma">';
                
                    if ( CENY_BRUTTO_NETTO == 'nie' ) {
          
                         echo '<div class="ZawartoscKoszykaSchowkaLacznie">{__TLUMACZ:WARTOSC_PRODUKTOW}: <b>' . $GLOBALS['waluty']->WyswietlFormatCeny($ZawartoscSchowka['brutto'], $_SESSION['domyslnaWaluta']['id'], true, false) . '</b></div>';
                         
                    } else {
                      
                         echo '<div class="ZawartoscKoszykaSchowkaLacznie">';
                      
                            echo '<div>{__TLUMACZ:WARTOSC_PRODUKTOW} {__TLUMACZ:BRUTTO}: <b>' . $GLOBALS['waluty']->WyswietlFormatCeny($ZawartoscSchowka['brutto'], $_SESSION['domyslnaWaluta']['id'], true, false) . '</b></div>';
                         
                            echo '<div>{__TLUMACZ:WARTOSC_PRODUKTOW} {__TLUMACZ:NETTO}: <b>' . $GLOBALS['waluty']->WyswietlFormatCeny($ZawartoscSchowka['netto'], $_SESSION['domyslnaWaluta']['id'], true, false) . '</b></div>';
                            
                        echo '</div>';
                      
                    }
                    
                    echo '<div class="ZawartoscKoszykaSchowkaDoKasy">
                    
                        <a href="schowek.html" class="przycisk">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_SCHOWKA}</a>
                        
                    </div>
                    
                </div>
                    
              </div>';

    echo '</div>';

} else {
 
    echo '<div class="RozwinietaWersja">
    
              <div class="PustyKoszykSchowek">{__TLUMACZ:SCHOWEK_JEST_PUSTY}</div>
        
          </div>';
  
}
?>