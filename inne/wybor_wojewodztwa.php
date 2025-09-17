<?php
chdir('../');            

if (isset($_POST['data']) && !empty($_POST['data'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    if (Sesje::TokenSpr()) {

        $wojewodztwa = "SELECT zone_id, zone_country_id, zone_name 
                          FROM zones 
                         WHERE zone_country_id = '" . (int)$_POST['data'] . "'
                      ORDER BY zone_name";

        $sql = $db->open_query($wojewodztwa);
        
        $tablicaWojewodztw = array();

        while ($wojewodztwa_wartosci = $sql->fetch_assoc()) {
          
            $tablicaWojewodztw[] = array( 'id' => $wojewodztwa_wartosci['zone_id'],
                                          'text' => $wojewodztwa_wartosci['zone_name']);
                                          
        }

        $db->close_query($sql);
        unset($wojewodztwa, $wojewodztwa_wartosci);

        if ( count($tablicaWojewodztw) > 1 ) {

             echo Funkcje::RozwijaneMenu('wojewodztwo', $tablicaWojewodztw, '', ' id="wybor_wojewodztwo" style="width:80%"'); 
             
           } else {

             $tablicaWojewodztw[] = array( 'id' => '0',
                                           'text' => '----');

             echo Funkcje::RozwijaneMenu('wojewodztwo', $tablicaWojewodztw, '', ' id="wybor_wojewodztwo" style="width:80%"'); 
            
        }
        
    }
    
}

?>