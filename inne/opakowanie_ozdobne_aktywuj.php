<?php
chdir('../');            

if (isset($_POST['akcja']) && $_POST['akcja'] == 'aktywuj') {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
      
        $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KOSZYK') ), $GLOBALS['tlumacz'] );
    
        echo '<div id="PopUpInfo" aria-live="assertive" aria-atomic="true">';

        echo $GLOBALS['tlumacz']['OPAKOWANIE_OZDOBNE_AKTYWOWANE'] . '<br />';
        
        echo '</div>';
        
        $_SESSION['opakowanieOzdobne'] = 'tak';

        echo '<div id="PopUpPrzyciski">';

        echo '<span role="button" tabindex="0" onclick="stronaReload()" class="przycisk" style="user-select:none">'.$GLOBALS['tlumacz']['PRZYCISK_KONTYNUUJ_ZAKUPY'].'</span>';

        echo '</div>';

    }
    
}
?>