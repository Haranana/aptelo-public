<?php
if ( isset($_SESSION['koszyk']) && count($_SESSION['koszyk']) > 0 && $WywolanyPlik != 'zamowienie_podsumowanie' ) {

    // generuje tablice globalne z nazwami cech
    Funkcje::TabliceCech();  
    
    echo '<div class="RozwinietaWersjaPelna RozwinietaWersja">';

        foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
          
            echo '<div class="ZawartoscKoszykaSchowkaRozwijane">';
        
                $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ) );

                echo '<div class="ZawartoscKoszykaSchowkaFoto">' . Funkcje::pokazObrazek($TablicaZawartosci['zdjecie'], ( isset($Produkt->info['nazwa']) ? $Produkt->info['nazwa'] : '' ), 150, 150, array()) . '</div>';

                echo '<div class="ZawartoscKoszykaSchowkaDane">';
                
                    echo '<div class="NazwaProduktuIlosc">' . $TablicaZawartosci['ilosc'] . ' ' . ( isset($Produkt->info['jednostka_miary']) ? $Produkt->info['jednostka_miary'] : '' ) . ' x ' . ( isset($Produkt->info['link']) ? $Produkt->info['link'] : '' ) . '</div>';

                    // czy produkt ma cechy
                    $CechaPrd = Funkcje::CechyProduktuPoId( $TablicaZawartosci['id'] );
                    
                    $JakieCechy = '';
                    
                    if ( count($CechaPrd) > 0 ) {
                        //
                        for ( $a = 0, $c = count($CechaPrd); $a < $c; $a++ ) {
                              //
                              echo '<span class="Cecha">' . $CechaPrd[$a]['nazwa_cechy'] . ': ' . $CechaPrd[$a]['wartosc_cechy'] . '</span>';
                              //
                        }
                        //
                    }        
                    
                    unset($JakieCechy, $CechaPrd);
                
                    echo '<div class="CenyRozwinietyKoszykSchowek">';

                        echo $GLOBALS['waluty']->PokazCene($TablicaZawartosci['cena_brutto'] * $TablicaZawartosci['ilosc'], $TablicaZawartosci['cena_netto'] * $TablicaZawartosci['ilosc'], 0, $_SESSION['domyslnaWaluta']['id']);
                        
                    echo '</div>';
                
                echo '</div>';
                
                unset($Produkt);
                
            echo '</div>';

        }

        $ZawartoscKoszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();

        echo '<div class="ZawartoscKoszykaSchowkaSumaFixed">
          
                <div class="ZawartoscKoszykaSchowkaSuma">';
                
                    if ( CENY_BRUTTO_NETTO == 'nie' ) {
          
                         echo '<div class="ZawartoscKoszykaSchowkaLacznie">{__TLUMACZ:WARTOSC_PRODUKTOW}: <b>' . $GLOBALS['waluty']->WyswietlFormatCeny($ZawartoscKoszyka['brutto'], $_SESSION['domyslnaWaluta']['id'], true, false) . '</b></div>';
                         
                    } else {
                      
                         echo '<div class="ZawartoscKoszykaSchowkaLacznie">';
                      
                            echo '<div>{__TLUMACZ:WARTOSC_PRODUKTOW} {__TLUMACZ:BRUTTO}: <b>' . $GLOBALS['waluty']->WyswietlFormatCeny($ZawartoscKoszyka['brutto'], $_SESSION['domyslnaWaluta']['id'], true, false) . '</b></div>';
                         
                            echo '<div>{__TLUMACZ:WARTOSC_PRODUKTOW} {__TLUMACZ:NETTO}: <b>' . $GLOBALS['waluty']->WyswietlFormatCeny($ZawartoscKoszyka['netto'], $_SESSION['domyslnaWaluta']['id'], true, false) . '</b></div>';
                            
                        echo '</div>';
                      
                    }                

                    echo '<div class="ZawartoscKoszykaSchowkaDoKasy">
                    
                        <a href="koszyk.html" class="przycisk">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_KOSZYKA}</a>
                        
                    </div>
                    
                </div>
                    
              </div>';

    echo '</div>';
    
} else {
  
    echo '<div class="RozwinietaWersja">
 
            <div class="PustyKoszykSchowek">{__TLUMACZ:KOSZYK_JEST_PUSTY}</div>
        
          </div>';
  
}
?>