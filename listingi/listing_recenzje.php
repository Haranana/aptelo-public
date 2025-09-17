<?php
// czy jest zapytanie
if ($IloscRecenzji > 0) { 

    echo '<div class="ListingRecenzjeKontener">';

    while ($info = $sql->fetch_assoc()) {
        //
        // ************************ wyglad produktu - poczatek **************************
        //
        echo '<article class="Recenzje LiniaDolna">';
            //
            $Produkt = new Produkt( $info['products_id'] );
            $Produkt->ProduktRecenzje();
            //             
            echo '<div class="Foto" style="width:' . ((int)SZEROKOSC_OBRAZEK_MALY + 50) . 'px">'.$Produkt->recenzje[$info['reviews_id']]['recenzja_zdjecie_link_ikony'].'</div>';
            //  
            echo '<div class="ProdRecenzja" style="width:calc(100% - ' . ((int)SZEROKOSC_OBRAZEK_MALY + 50) . 'px)">';
                          
                echo '<h3>' . $Produkt->recenzje[$info['reviews_id']]['recenzja_link'] . '</h3>'; 
                
                echo '<div class="RecenzjaGwiazdki">' . $Produkt->recenzje[$info['reviews_id']]['recenzja_ocena_obrazek'] . '</div>';
                
                echo '<p class="RecenzjaTresc LiniaOpisu">';
                
                    echo $Produkt->recenzje[$info['reviews_id']]['recenzja_tekst_krotki'];
                    
                    // komentarz do opinii
                    if ( !empty($Produkt->recenzje[$info['reviews_id']]['recenzja_odpowiedz']) ) {
                         echo '<br /><br /><span style="font-style:italic">{__TLUMACZ:ODPOWIEDZ_SKLEPU} <br /> ' . $Produkt->recenzje[$info['reviews_id']]['recenzja_odpowiedz'] . '</span>';
                    }                    
                    
                echo '</p>';
                
                echo '<p class="AutorData">';
                echo '{__TLUMACZ:AUTOR_RECENZJI}: <b>' . $Produkt->recenzje[$info['reviews_id']]['recenzja_oceniajacy'] . '</b> <br />';
                echo '{__TLUMACZ:DATA_NAPISANIA_RECENZJI}: <b>' . $Produkt->recenzje[$info['reviews_id']]['recenzja_data_dodania'] . '</b>';
                echo '</p>';
                
                if ( $Produkt->recenzje[$info['reviews_id']]['potwierdzony_zakup'] == 'tak' ) {                      
                
                     echo '<div class="RecenzjaPotwierdzonyZakupListing" style="margin-top:10px;font-weight:bold"><span class="InformacjaOk">' . $GLOBALS['tlumacz']['RECENZJA_POTWIERDZONA'] . '</span></div>';
      
                }
            
            echo '</div>';
            
            echo '<div class="cl"></div>';
            //
            unset($Produkt);
            //
        echo '</article>';
        //
        // ************************ wyglad produktu - koniec **************************
        //
    }
    
    echo '</div>';

    unset($info);
      
} else {

    echo '<div id="BrakProduktow" class="Informacja">{__TLUMACZ:BLAD_BRAK_RECENZJI}</div>';
  
}

$GLOBALS['db']->close_query($sql); 

unset($IloscRecenzji, $zapytanie);  
?>