<?php

if (isset($_SESSION['produktyPorownania']) && count($_SESSION['produktyPorownania']) > 0 && LISTING_POROWNYWARKA_PRODUKTOW == 'tak' && $GLOBALS['stronaGlowna'] == true) {
    //
    echo '<div class="BoxPorownywarka">';
    
        echo '<ul id="ListaProduktowPorownanieBox">';
        //
        $DoPorownaniaId = array();
        //
        foreach ($_SESSION['produktyPorownania'] AS $Id) {
            //
            $DoPorownaniaId[] = $Id;
            //
        }
        $DoPorownaniaId = implode(',', (array)$DoPorownaniaId);
        //
        $zapNazwy = Produkty::SqlPorownanieProduktow($DoPorownaniaId); 
        $sqlNazwy = $GLOBALS['db']->open_query($zapNazwy);
        //
        if ((int)$GLOBALS['db']->ile_rekordow($sqlNazwy) > 0) { 
        
            while ($infc = $sqlNazwy->fetch_assoc()) {
                //
                // ustala jaka ma byc tresc linku
                $linkSeo = ((!empty($infc['products_seo_url'])) ? $infc['products_seo_url'] : $infc['products_name']);
                //
                echo '<li><span role="button" tabindex="0" onclick="PorownajBox(' . $infc['products_id'] . ')"></span><a href="' . Seo::link_SEO( $linkSeo, $infc['products_id'], 'produkt' ) . '">' . $infc['products_name'] . '</a></li>';
                //    
                unset($linkSeo);
                //
            }
            
            unset($infc);      
            
        }
        
        $GLOBALS['db']->close_query($sqlNazwy); 
        unset($zapNazwy, $DoPorownaniaId);      
        //
        echo '</ul>';
        
        echo '<div id="przyciskPorownywaniaBox"' . ((count($_SESSION['produktyPorownania']) < 2) ? ' style="display:none"' : '') . '><span id="oknoPorownywarki" class="przycisk oknoAjax">{__TLUMACZ:PRZYCISK_POROWNAJ_PRODUKTY}</span></div> ';
        
    echo '</div>';
    //
}

?>