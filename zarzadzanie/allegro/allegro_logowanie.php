<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_GET['id_poz']) && $_GET['id_poz'] != '' ) {
        $_SESSION['AllegroUser'] = (int)$_GET['id_poz'];
    }

    if ( isset($_SESSION['AllegroUser']) && $_SESSION['AllegroUser'] != '' ) {

        $JestUser = false;

        $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['AllegroUser']) );

        if ( !empty($_GET['code']) ) {
            try
            {
                $wynik = $AllegroRest->tokenRequest($_GET['code']);

                if ( is_object($wynik) ) {
                    $UserId = '';
                    $DataWaznosciSesji = time() + $wynik->expires_in;

                    if ( $wynik->access_token != '' ) {
                        $TablicaAuthorisationToken = array();
                        $TablicaAuthorisationToken = explode('.', (string)$wynik->access_token);
                        $Payload = json_decode(base64_decode((string)$TablicaAuthorisationToken['1']));
                        
                        $UserId  = $Payload->user_name;
                        $ClientId = $Payload->client_id;
                    
                    }

                    $zapytanie = "SELECT *
                                  FROM allegro_users WHERE allegro_userid = '".$UserId."' AND allegro_user_clientid = '".$ClientId."'";
                    $sql = $db->open_query($zapytanie);

                    if ( $db->ile_rekordow($sql) > 0 ) {
                        $pola = array(
                                array('allegro_user_authorizationtoken',$filtr->process($wynik->access_token)),
                                array('allegro_user_refreshtoken',$filtr->process($wynik->refresh_token)),
                                array('allegro_token_expires',$DataWaznosciSesji)
                        );

                        //
                        $db->update_query('allegro_users' , $pola, " allegro_user_id = '".(int)$_SESSION['AllegroUser']."' AND allegro_user_clientid = '".$ClientId."'");	

                        $komunikat = 'Logowanie przebiegło poprawnie';
                        $JestUser = true;

                    } else {
                        $komunikat = 'Zalogowano się na konto w Allegro inne niż wybrane w sklepie<br>sprawdź UserId użytkownika : ' . $UserId;
                    }
                    $db->close_query($sql);
                    unset($zapytanie);

                    if ( $JestUser == false ) {
                        $zapytanie = "SELECT *
                                      FROM allegro_users WHERE ( allegro_userid = '' OR allegro_userid IS NULL ) AND allegro_user_clientid = '".$ClientId."'";
                        $sql = $db->open_query($zapytanie);

                        if ( $db->ile_rekordow($sql) > 0 ) {
                            $pola = array(
                                    array('allegro_userid',$UserId),
                                    array('allegro_user_authorizationtoken',$filtr->process($wynik->access_token)),
                                    array('allegro_user_refreshtoken',$filtr->process($wynik->refresh_token)),
                                    array('allegro_token_expires',$DataWaznosciSesji)
                            );

                            //
                            $db->update_query('allegro_users' , $pola, " allegro_user_id = '".(int)$_SESSION['AllegroUser']."' AND allegro_user_clientid = '".$ClientId."'");	

                            $komunikat = 'Logowanie przebiegło poprawnie';
                        }

                        $db->close_query($sql);
                        unset($zapytanie);
                    }

                    unset($_SESSION['AllegroUser']);

                    // wczytanie naglowka HTML
                    include('naglowek.inc.php');

                    ?>

                    <script>
                      $(document).ready(function() {
                          $.colorbox( { html:"<div id='PopUpInfo'><div class='Tytul'>Połączenie z Allegro</div><?php echo $komunikat; ?></div>", maxWidth:"90%", maxHeight:"90%", open:true, initialWidth:50, initialHeight:50, speed: 200, overlayClose:false, escKey:false, onLoad: function() {
                              $("#cboxClose").show();
                          }});
                          window.setTimeout(function() { window.location.href = "/zarzadzanie/allegro/konfiguracja_uzytkownicy.php" }, 2500);
                      });
                    </script>

                    <?php

                    }

                    include('stopka.inc.php');

            }
            catch(Exception $e)
            {
                echo $e->getMessage();
            }
        } else {
            
            Funkcje::PrzekierowanieURL($AllegroRest->getAuthorizationUri());
        }


    }

}

?>
