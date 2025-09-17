<?php
// sprawdza czy nie sa wyswietlane wyniki ankiety
if (!isset($_GET['ida'])) {
    //
    // dodatkowy warunek dla tylko zalogowanych
    $TylkoZalogowani = " and p.poll_login = '0'";
    if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {
        $TylkoZalogowani = "";
    }
    // sortowanie 
    $TrybSortowania = 'p.poll_date_added desc';
    if (ANKIETA_TRYB_WYSWIETLANIA == 'losowo') {
        $TrybSortowania = 'rand()';
    }

    $ankieta = "SELECT p.id_poll,
                       p.poll_date_added, 
                      pd.poll_name
                    FROM poll p
              INNER JOIN poll_description pd ON p.id_poll = pd.id_poll AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                   WHERE p.poll_status = '1' " . $TylkoZalogowani . "
                ORDER BY " . $TrybSortowania . " LIMIT 1";

    $sqla = $GLOBALS['db']->open_query($ankieta);

    unset($TylkoZalogowani, $TrybSortowania);

    if ((int)$GLOBALS['db']->ile_rekordow($sqla) > 0) { 

        $infoAnkieta = $sqla->fetch_assoc();
        $idAnkiety = $infoAnkieta['id_poll'];
        $NazwaAnkiety = $infoAnkieta['poll_name'];
        //
        unset($infoAnkieta, $ankieta);
        
        // szukanie pozycji ankiety
        $ankietaPozycje = "SELECT id_poll_unique, poll_field FROM poll_field WHERE id_poll = '" . $idAnkiety . "' AND language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' ORDER BY poll_field_sort";    
        $sqlPoz = $GLOBALS['db']->open_query($ankietaPozycje);
                
        // jezeli jest wiecej niz 1 pytanie
        if ((int)$GLOBALS['db']->ile_rekordow($sqlPoz) > 1) { 
        
            echo '<div class="Ankieta">';
            
            echo '<form action="' . Seo::link_SEO($NazwaAnkiety, $idAnkiety, 'ankieta') . '" method="post" class="cmxform" id="ankietaBox">';
            
            echo '<h4>' . $NazwaAnkiety . '</h4>';
            
            echo '<ul class="Pytania">';

            while ($pozycje = $sqlPoz->fetch_assoc()) {
                //
                echo '<li><label for="ankieta_' . $pozycje['id_poll_unique'] . '" aria-label="' . str_replace('"','',$pozycje['poll_field']) . '" tabindex="0"><input type="radio" name="ankieta" value="' . $pozycje['id_poll_unique'] . '" id="ankieta_' . $pozycje['id_poll_unique'] . '" /><b>' . $pozycje['poll_field'] . '</b><span class="radio" id="radio_' . $pozycje['id_poll_unique'] . '"></span></label></li>';
                //
            }

            echo '</ul>';
            
            echo '<br /><div id="BladAnkiety" style="display:none">{__TLUMACZ:BLAD_ZAZNACZ_JEDNA_OPCJE}</div>';    
            
            echo '<div>';
            echo '<input type="hidden" value="'.$idAnkiety.'" name="id" />';
            echo '<input type="submit" id="submitAnkieta" class="przyciskWylaczony" value="{__TLUMACZ:PRZYCISK_ZAGLOSUJ}" />';
            echo '</div>';
            
            echo '</form>';

            echo '</div>';
            echo '<div class="WszystkieKreska"><a href="' . Seo::link_SEO($NazwaAnkiety, $idAnkiety, 'ankieta') . '">{__TLUMACZ:ZOBACZ_WYNIKI_ANKIETY}</a></div>';
        
        }
        
        $GLOBALS['db']->close_query($sqlPoz); 
        
        unset($ankietaPozycje, $idAnkiety, $NazwaAnkiety); 
            
    }
    
    $GLOBALS['db']->close_query($sqla); 

    unset($random, $tablica, $LimitZnakow);
    //
}
?>