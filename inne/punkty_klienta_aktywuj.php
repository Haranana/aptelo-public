<?php
chdir('../');            

if (isset($_POST['id']) && isset($_POST['akcja']) && $_POST['akcja'] == 'aktywuj') {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('PUNKTY') ), $GLOBALS['tlumacz'] );

        $punkty = (int)$_POST['id'];
        //

        if ( $punkty > 0 ) {

            $kwota_rabatu = $GLOBALS['waluty']->PokazCeneBezSymbolu((int)$punkty/(float)SYSTEM_PUNKTOW_WARTOSC_PRZY_KUPOWANIU,'',false);

            $wartosc_zamowienia_do_punktow = 0;
            foreach ( $_SESSION['podsumowanieZamowienia'] as $podsumowanie ) {
              if ( $podsumowanie['prefix'] == '1' ) {
                if ( $podsumowanie['klasa'] == 'ot_shipping' ) {
                  $wartosc_zamowienia_do_punktow;
                } else {
                  $wartosc_zamowienia_do_punktow += $podsumowanie['wartosc'];
                }
              } elseif ( $podsumowanie['prefix'] == '0' ) {
                $wartosc_zamowienia_do_punktow -= $podsumowanie['wartosc'];
              }
            }

            if ( $kwota_rabatu > $wartosc_zamowienia_do_punktow ) {
              $kwota_rabatu = $wartosc_zamowienia_do_punktow;
            }

            $tablica_punktow = array('punkty_ilosc' => $punkty,
                                     'punkty_status' => true,
            );

            $kwota_rabatu = $GLOBALS['waluty']->WyswietlFormatCeny( $GLOBALS['waluty']->PokazCeneBezSymbolu((int)$punkty/(float)SYSTEM_PUNKTOW_WARTOSC_PRZY_KUPOWANIU,'',true) , $_SESSION['domyslnaWaluta']['id'], true, false);

            if ( $tablica_punktow['punkty_status'] ) {
              echo '<div id="PopUpInfo" aria-live="assertive" aria-atomic="true">';
              echo $GLOBALS['tlumacz']['PUNKTY_AKTYWOWANE'] .': ' . $punkty . '<br />';
              echo '<h3> ' . $GLOBALS['tlumacz']['WARTOSC_RABATU'] .': ' . $kwota_rabatu . '</h3>';
              echo '</div>';
              $_SESSION['punktyKlienta'] = $tablica_punktow;
            }

        }

        echo '<div id="PopUpPrzyciski">';

        echo '<span role="button" tabindex="0" onclick="stronaReload()" class="przycisk" style="user-select:none">'.$GLOBALS['tlumacz']['PRZYCISK_KONTYNUUJ_ZAKUPY'].'</span>';

        echo '</div>';


    }
    
}
?>