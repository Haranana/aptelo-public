<?php

if (isset($GLOBALS['schowekKlienta']) && $GLOBALS['schowekKlienta']->IloscProduktow > 0 && PRODUKT_SCHOWEK_STATUS == 'tak') {
    //
    echo '<div class="BoxSchowek">';
    
    $WartoscSchowka = $GLOBALS['schowekKlienta']->WartoscProduktowSchowka();
   
    echo '{__TLUMACZ:SCHOWEK_LISTA_PRODUKTOW} <b>' . $GLOBALS['schowekKlienta']->IloscProduktow . '</b> <br />';

    echo '<div class="WartoscSchowka">';
                
        echo '<div>{__TLUMACZ:WARTOSC_PRODUKTOW}:</div>';
        echo '<div>' . $GLOBALS['waluty']->PokazCene($WartoscSchowka['brutto'], $WartoscSchowka['netto'], 0, $_SESSION['domyslnaWaluta']['id'], CENY_BRUTTO_NETTO, false) . '</div>';
        
    echo '</div>';
    
    unset($WartoscSchowka);
    
    echo '<a class="przycisk" href="' . Seo::link_SEO( 'schowek.php', '', 'inna' ) . '">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_SCHOWKA}</a>';

    echo '</div>';
    //
}

?>