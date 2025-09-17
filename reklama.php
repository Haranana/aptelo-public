<?php
//
if (isset($_GET['id']) && $_GET['id'] != '' ) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    $id = (int)$_GET['id'];

    $link = '/';
    
    $zapytanie = "SELECT banners_url, banners_clicked FROM banners WHERE banners_id = '".(int)$id."'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

        $info = $sql->fetch_assoc();

        $klikniecia = $info['banners_clicked'] + 1;
            
        $link = $info['banners_url'];

        unset($info);
        
        $pola = array(array('banners_clicked',(int)$klikniecia));

        $GLOBALS['db']->update_query('banners' , $pola, " banners_id = '".(int)$id."'");
        unset($pola);
    
    }

    $GLOBALS['db']->close_query($sql);  
    unset($zapytanie);

    header('Location: ' . $link);
    exit();

}

?>