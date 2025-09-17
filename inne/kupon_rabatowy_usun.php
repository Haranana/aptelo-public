<?php
chdir('../');            

if (isset($_POST['akcja']) && $_POST['akcja'] == 'usun') {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KUPONY_RABATOWE') ), $GLOBALS['tlumacz'] );

        unset($_SESSION['kuponRabatowy']);

        echo '<div id="PopUpUsun" class="PopUpKuponUsuniecie" aria-live="assertive" aria-atomic="true">';
        echo $GLOBALS['tlumacz']['KUPON_ZOSTAL_USUNIETY_ZAMOWIENIA'] . ' <br />';
        echo '</div>';

        echo '<div id="PopUpPrzyciski" class="PopUpKuponPrzyciski PopUpKuponUsunieciePrzyciski">';

        echo '<span role="button" tabindex="0" onclick="stronaReload()" class="przycisk" style="user-select:none">'.$GLOBALS['tlumacz']['PRZYCISK_KONTYNUUJ_ZAKUPY'].'</span>';

        echo '</div>';


    }
    
}
?>