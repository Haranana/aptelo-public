<?php

// cache zapytania
$WynikCache = $GLOBALS['cache']->odczytaj('Jezyki', CACHE_INNE);                     
                  
if ( !$WynikCache ) {

    $zapytanie_box = "SELECT languages_id, name, code, image 
                      FROM languages
                      WHERE status = '1' ORDER BY sort_order";

    $sql_box = $GLOBALS['db']->open_query($zapytanie_box);
    $IleRekordow = (int)$GLOBALS['db']->ile_rekordow($sql_box);
    
    unset($zapytanie_box);
    
  } else {
  
    $IleRekordow = count($WynikCache);
    
}                  

if ($IleRekordow > 1) { 
    //
    $Tablica = array();
    //
    echo '<div class="SrodekCentrowany">';
    //
    if ( !$WynikCache ) {
        while ($info_box = $sql_box->fetch_assoc()) {
            $Tablica[] = $info_box;
        }
        //
        $GLOBALS['cache']->zapisz('Jezyki', $Tablica, CACHE_INNE);      
    } else {
        $Tablica = $WynikCache;
    }
    
    foreach ($Tablica as $info_box) {
      if ( file_exists(KATALOG_ZDJEC . '/' . $info_box['image']) ) {
        list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/' . $info_box['image']);
        echo '<span class="Flaga" id="JezykBox'.$info_box['languages_id'].'"><img '.( $info_box['languages_id'] == (int)$_SESSION['domyslnyJezyk']['id'] ? '' : 'class="FlagaOff"').' src="' . KATALOG_ZDJEC . '/'.$info_box['image'].'"' . (((int)$szerokosc > 0 ) ? ' width="' . $szerokosc . '"' : '') . (((int)$wysokosc > 0 ) ? ' height="' . $wysokosc . '"' : '') . ' alt="'.$info_box['name'].'" title="'.$info_box['name'].'" /></span>';
      }
    }
    //
    echo '</div>';
    
    unset($Tablica);
    //
}

if ( !$WynikCache ) {
    $GLOBALS['db']->close_query($sql_box); 
}

?>