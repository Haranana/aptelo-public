<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if ( isset($_POST['nazwy']) && $_POST['nazwy'] == 'tak' ) {

    if ( isset($_POST['jezyk']) && (int)$_POST['jezyk'] > 0 && isset($_POST['id']) && (int)$_POST['id'] > 0 ) {

        $zapytanie = "SELECT soc.comments_id, soc.comments_id, soc.comments_name
                        FROM standard_complaints_comments soc 
                       WHERE soc.status_id = '" . (int)$_POST['id'] . "'
                    ORDER BY soc.sort_order";
        
        $sql = $db->open_query($zapytanie);
        
        echo '<option value="0">--- wybierz z listy ---</option>';
        
        while ($info = $sql->fetch_assoc()) {
        
          echo '<option value="' . $info['comments_id'] . '">' . $info['comments_name'] . '</option>';

        }

        $db->close_query($sql);
        unset($zapytanie, $info);
        
    } else {
    
        echo '<option selected="selected" value="0">--- najpierw wybierz status reklamacji ---</option>';
    
    }
    
}

if ( isset($_POST['nazwy']) && $_POST['nazwy'] == 'nie' ) {

    if ( isset($_POST['jezyk']) && (int)$_POST['jezyk'] > 0 && isset($_POST['id']) && (int)$_POST['id'] > 0 ) {

        $zapytanie = "SELECT socd.comments_id, socd.comments_text 
                        FROM standard_complaints_comments_description socd
                       WHERE socd.languages_id = '" . (int)$_POST['jezyk'] . "' and socd.comments_id = '" . (int)$_POST['id'] . "'";
        
        $sql = $db->open_query($zapytanie);
        $info = $sql->fetch_assoc();
        
        echo $info['comments_text'];

        $db->close_query($sql);
        unset($zapytanie, $info);
        
    }
    
}    
?>

