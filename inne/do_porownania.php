<?php
chdir('../');            

if (isset($_POST['id']) && (int)$_POST['id'] > 0) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    $text = '';
    
    if (Sesje::TokenSpr()) {
    
        //
        if ($_POST['akcja'] == 'wl') {
            //
            if ( isset($_SESSION['produktyPorownania']) && count((array)$_SESSION['produktyPorownania']) < LISTING_POROWNYWARKA_PRODUKTOW_MAXIMUM ) {
                $_SESSION['produktyPorownania'][(int)$_POST['id']] = (int)$_POST['id'];
            } else {
                echo json_encode( array("info" => 'MAX', "tekst" => $GLOBALS['tlumacz']['PRODUKTY_POROWNANIA_MAKSYMALNA_ILOSC'] . ' ' . LISTING_POROWNYWARKA_PRODUKTOW_MAXIMUM) );
                exit;
            }
            //
        }
        //
        if ($_POST['akcja'] == 'wy') {
            //
            unset($_SESSION['produktyPorownania'][(int)$_POST['id']]);
            //
        }
        //
        // wyswietla produkty
        if (isset($_SESSION['produktyPorownania']) && count($_SESSION['produktyPorownania']) > 0) {
            //
            $DoPorownaniaId = '';
            foreach ($_SESSION['produktyPorownania'] AS $Id) {
                $DoPorownaniaId .= $Id . ',';
            }
            $DoPorownaniaId = substr((string)$DoPorownaniaId, 0, -1);
            //
            $zapNazwy = Produkty::SqlPorownanieProduktow($DoPorownaniaId); 
            $sqlNazwy = $db->open_query($zapNazwy);
            //
            if ((int)$GLOBALS['db']->ile_rekordow($sqlNazwy) > 0) {
                //
                while ($infc = $sqlNazwy->fetch_assoc()) {
                    //
                    // ustala jaka ma byc tresc linku
                    $linkSeo = ((!empty($infc['products_seo_url'])) ? $infc['products_seo_url'] : $infc['products_name']);
                    //
                    // jezeli jest uruchomione z boxu
                    if (isset($_POST['box'])) {
                        //
                        $text .= '<li class="PozycjaDoPorownania"><span role="button" tabindex="0" onclick="PorownajBox(' . $infc['products_id'] . ')" style="user-select:none"></span><a href="' . Seo::link_SEO( $linkSeo, $infc['products_id'], 'produkt' ) . '">' . $infc['products_name'] . '</a></li>';
                        //    
                      } else {
                        //
                        $text .= '<div class="PozycjaDoPorownania"><span role="button" tabindex="0" onclick="Porownaj(' . $infc['products_id'] . ',\'wy\')" style="user-select:none"></span><a href="' . Seo::link_SEO( $linkSeo, $infc['products_id'], 'produkt' ) . '">' . $infc['products_name'] . '</a></div>';
                        //    
                    }
                    unset($linkSeo);
                    //
                }
                //
                unset($infc);
                //
            }
            //
            $db->close_query($sqlNazwy); 
            unset($zapNazwy, $DoPorownaniaId);      
            //
        }
    
    }
    
    echo json_encode( array("info" => $text, 'ilosc' => ((isset($_SESSION['produktyPorownania'])) ? count($_SESSION['produktyPorownania']) : 0) ) );
    
}

?>