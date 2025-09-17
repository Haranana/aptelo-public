<?php
chdir('../');            

if (isset($_POST['id']) && (int)$_POST['id'] > 0) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {

        $zapytanie = "SELECT products_options_description FROM products_options WHERE language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' and products_options_id = '" . (int)$_POST['id'] . "'";
        $sql = $GLOBALS['db']->open_query($zapytanie);
        //
        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
        
            $info = $sql->fetch_assoc();
            
            echo '<div id="PopUpInfo" class="PopUpOpisCecha">';

            echo '<div class="FormatEdytor">' . $info['products_options_description'] . '</div>';
            
            echo '</div>';
            
            unset($info);
        
        }
        
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie);        

    }
    
}
?>