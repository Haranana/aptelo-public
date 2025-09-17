<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['opcja'])) {
        //
        if (count($_POST['opcja']) > 0) {
    
            foreach ($_POST['opcja'] as $pole) {
    
                switch ((int)$_POST['akcja_dolna']) {
                    case 1:
                        // kasowanie ------------ ** -------------
                        $db->delete_query('mailing' , " mailing_id = '".$pole."'");   
                        //                             
                }          

            }
        
        }
        //
    }
    
    Funkcje::PrzekierowanieURL('newsletter_mailing.php');
    
}
?>