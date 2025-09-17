<?php
chdir('../');     

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr() && isset($_POST['dane'])) {

    $PodzielDane = explode(';', (string)$filtr->process($_POST['dane']));
    
    $DozwoloneStale = array('SZEROKOSC_SKLEPU',
                            'CZY_WLACZONA_LEWA_KOLUMNA',
                            'CZY_WLACZONA_PRAWA_KOLUMNA',
                            'CZY_WLACZONA_LEWA_WSZEDZIE',
                            'CZY_WLACZONA_PRAWA_WSZEDZIE',
                            'SZEROKOSC_LEWEJ_KOLUMNY',
                            'SZEROKOSC_PRAWEJ_KOLUMNY',
                            'TLO_SKLEPU',
                            'TLO_SKLEPU_RODZAJ',
                            'NAGLOWEK_RODZAJ',
                            'NAGLOWEK',
                            'NAGLOWEK_RWD_KONTRAST',
                            'SZEROKOSC_SKLEPU',
                            'SZEROKOSC_SKLEPU_JEDNOSTKA');
    
    foreach ( $PodzielDane as $Stala ) {
    
        $PodzielStala = explode(':', (string)$Stala);
        
        if ( isset($PodzielStala) && count($PodzielStala) == 2 ) {
        
            if ( in_array( $filtr->process(trim((string)$PodzielStala[0])), $DozwoloneStale) ) {
        
                $pola = array(
                        array('value',$filtr->process(trim((string)$PodzielStala[1])))); 

                $sql = $db->update_query('settings', $pola, " code = '" . $filtr->process(trim((string)$PodzielStala[0])) . "'");	
                
                unset($pola); 

            }
        
        }
    
    }
    
    unset($DozwoloneStale, $PodzielDane);

}
?>