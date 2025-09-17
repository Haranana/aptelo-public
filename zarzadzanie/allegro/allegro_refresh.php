<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $uzytkownik = '4';

    $AllegroRest = new AllegroRest( array('allegro_user' => $uzytkownik) );

    try
        {
            $wynik = $AllegroRest->tokenRefresh($AllegroRest->ParametryPolaczenia['RefreshToken']);
            
            $DataWaznosciSesji = time() + $wynik->expires_in;

            $pola = array(
                    array('allegro_user_authorizationtoken',$filtr->process($wynik->access_token)),
                    array('allegro_user_refreshtoken',$filtr->process($wynik->refresh_token)),
                    array('allegro_token_expires',$DataWaznosciSesji)
            );

            //
            $db->update_query('allegro_users' , $pola, " allegro_user_id = '".(int)$uzytkownik."'");	

        }
        catch(Exception $e)
        {
            echo $e->getMessage();
    }

}

?>
