<?php
chdir('../');           

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php'); 

if (Sesje::TokenSpr() && isset($_POST['id']) && $_POST['id'] == 'porownanie') {  

    if (isset($_SESSION['produktyPorownania']) && count($_SESSION['produktyPorownania']) > 0 && LISTING_POROWNYWARKA_PRODUKTOW == 'tak') {

        echo '<div class="RozwinietaWersjaPelna RozwinietaWersja">';
            
            echo '<ul class="ZawartoscPorownania">';
            //
            $DoPorownaniaId = array();
            foreach ($_SESSION['produktyPorownania'] as $Id) {
                //
                $DoPorownaniaId[] = $Id;
                //
            }
            //
            $zapytaniePorownanie = Produkty::SqlPorownanieProduktow(implode(',', (array)$DoPorownaniaId)); 
            $sqlPorownanie = $GLOBALS['db']->open_query($zapytaniePorownanie);
            //
            if ((int)$GLOBALS['db']->ile_rekordow($sqlPorownanie) > 0) { 
            
                while ($infc = $sqlPorownanie->fetch_assoc()) {
                    //
                    // ustala jaka ma byc tresc linku
                    $linkSeo = ((!empty($infc['products_seo_url'])) ? $infc['products_seo_url'] : $infc['products_name']);
                    //
                    echo '<li><a href="' . Seo::link_SEO( $linkSeo, $infc['products_id'], 'produkt' ) . '">' . $infc['products_name'] . '</a><span role="button" tabindex="0" onclick="Porownaj(' . $infc['products_id'] . ', \'wy\')" style="user-select:none"></span></li>';
                    //    
                    unset($linkSeo);
                    //
                }
                
                unset($infc);
                
            }
            
            $GLOBALS['db']->close_query($sqlPorownanie); 
            unset($zapytaniePorownanie, $DoPorownaniaId);      
            //
            echo '</ul>';

        echo '</div>';

    }
    
}
?>