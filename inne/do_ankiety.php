<?php
chdir('../');            

if (isset($_POST['data']) && !empty($_POST['data'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    // rozdziela serializowane dane z ajaxa na tablice POST
    parse_str($_POST['data'], $PostTablica);
    unset($_POST['data']);
    $_POST = $PostTablica;
    
    if ((isset($_POST['id']) && (int)$_POST['id'] > 0) && (isset($_POST['ankieta']) && (int)$_POST['ankieta'] > 0) && Sesje::TokenSpr()) {

        if (!isset($_SESSION['id_ankiet'])) { $_SESSION['id_ankiet'] = 0; }

        // glosowanie wiecej niz jeden raz przez klienta
        if ( ANKIETA_TRYB_GLOSOWANIA == 'nie' ) {
            if (isset($_SESSION['id_ankiet'])) {
                $_SESSION['id_ankiet'] = $_SESSION['id_ankiet'] . ',' . (int)$_POST['id'];
              } else {
                $_SESSION['id_ankiet'] = (int)$_POST['id'];
            }
        }
        //

        $KomunikatSukces = '<div id="PopUpDodaj" class="PopUpAnkietaPodziekowanie">' . $GLOBALS['tlumacz']['ANKIETA_PODZIEKOWANIE'];
        
        $DodajGlos = true;
        
        if ( ANKIETA_TRYB_GLOSOWANIA == 'nie' ) {
            //
            $TabTmp = explode(',', (string)$_SESSION['id_ankiet']);
            array_count_values($TabTmp);
            //
            if ((int)Funkcje::arrayIloscWystapien((int)$_POST['id'], explode(',', (string)$_SESSION['id_ankiet'])) > 1) {
                $KomunikatSukces = '<div id="PopUpInfo" class="PopUpAnkietaPodwojnyGlos" aria-live="assertive" aria-atomic="true">' . $GLOBALS['tlumacz']['ANKIETA_PODWOJNY_GLOS'];
                $DodajGlos = false;
            }
            //
        }
        
        // ile jest w glosow w polu
        $ile_glosow = $GLOBALS['db']->open_query("select pf.poll_result, pd.poll_name, pd.id_poll from poll_field pf, poll_description pd where pd.id_poll = pf.id_poll and pd.id_poll = '" . (int)$_POST['id'] . "' and pf.id_poll_unique = '" . (int)$_POST['ankieta'] . "' and pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'");
        $infr = $ile_glosow->fetch_assoc();
        $GLOBALS['db']->close_query($ile_glosow);     
        //        
        
        if ( $DodajGlos == true ) {
        
            $pola = array(array('poll_result', $infr['poll_result'] + 1));
            $GLOBALS['db']->update_query('poll_field' , $pola, " id_poll_unique = '" . (int)$_POST['ankieta'] . "'");	
            unset($pola);    
            //
        
        }
        
        unset($ile_glosow);  
        
        echo $KomunikatSukces;

        echo '</div>';
        
        echo '<div id="PopUpPrzyciski" class="PopUpAnkietaPrzyciski">';
        
            if ( WLACZENIE_SSL == 'tak' ) {
              $link = ADRES_URL_SKLEPU;
            } else {
              $link = '/';
            }                
            echo '<a href="' . $link . '" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_DO_STRONY_GLOWNEJ'].'</a>'; 
            unset($link);
            
            echo '<a href="' . Seo::link_SEO($infr['poll_name'], (int)$_POST['id'], 'ankieta') . '" class="przycisk">' . $GLOBALS['tlumacz']['PRZYCISK_DO_ANKIETY'] . '</a>';
            
        echo '</div>';
        
        unset($KomunikatSukces, $infr, $DodajGlos);    
         
    }
    
}
?>