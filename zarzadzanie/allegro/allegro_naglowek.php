<div class="info_content">

    <?php
    if ( Funkcje::SprawdzAktywneAllegro() ) {

        if ( isset($_POST['zmiana_logowania']) && $_POST['zmiana_logowania'] == 'zmiana' ) {

            $_SESSION['domyslny_uzytkownik_allegro'] = $_POST['login_allegro'];

            $zapytanie_user = "SELECT * FROM allegro_users WHERE allegro_user_id = '".$_POST['login_allegro']."'";
            $sql_user = $db->open_query($zapytanie_user);

            if ((int)$db->ile_rekordow($sql_user) > 0) {
              while ($info_user = $sql_user->fetch_assoc()) {
                    $_SESSION['domyslny_login_allegro'] = $info_user['allegro_user_login'];
                }
            }
            $db->close_query($sql_user);

            unset($zapytanie_user, $info_user);

        }

        if ( Funkcje::SprawdzAktywneAllegro() ) { ?>

            <div id="PozycjeIkonAllegro">
                <div class="Serwer">Serwer Allegro : <?php echo ( $AllegroRest->polaczenie['CONF_SANDBOX'] == 'nie' ? '<span class="zielony">RZECZYWISTY</span>' : '<span class="czerwony">TESTOWY</span>' ) ; ?></div>
            </div>

            <div class="rg">
                <div class="ZmianaKontaAllegroWystawianie">

                    <form action="" method="post" class="cmxform">
              
                        <em class="TipIkona" style="margin-left:0px"><b>Wybierz inne konto jeżeli dostęp do API ma być z innego konta Allegro</b></em>
                        Konto aktualnie połączone z Allegro: 


                        <input type="hidden" value="zmiana" name="zmiana_logowania" />
                        <?php
                            $tablica_uzytkownikow = Array();
                            $zapytanieUser = "SELECT * FROM allegro_users WHERE allegro_token_expires > '" . time() . "' AND allegro_user_status = '1' AND allegro_userid != '' AND allegro_user_clientid != '' ";
                            $sqlUser = $db->open_query($zapytanieUser);
                            
                            if ((int)$db->ile_rekordow($sqlUser) > 0) {
                            
                                while ($infoUser = $sqlUser->fetch_assoc()) {

                                    $tablica_uzytkownikow[] = array('id' => $infoUser['allegro_user_id'], 'text' => $infoUser['allegro_user_login']);

                                }
                            }
                            $db->close_query($sqlUser);
                            unset($zapytanieUser, $infoUser);
                        ?>

                        <?php
                            echo Funkcje::RozwijaneMenu('login_allegro', $tablica_uzytkownikow, $_SESSION['domyslny_uzytkownik_allegro']); 
                            unset($tablica_uzytkownikow);
                        ?>
                    </form>
                
                <script>
                $(document).ready(function() {
                    $('.ZmianaKontaAllegroWystawianie select').change(function() {
                        $(this).parents('form:first').submit()
                    });  
                });                       
                </script>
                
            </div>
            <?php
        }

    } else {

        echo '<div style="padding:10px"><div class="ostrzezenie" style="color:#ff0000;font-size:13px">Nie możną wyświetlić wszystkich danych Allegro. Brak skonfigurowanych użytkowników Allegro, użytkownicy nie są zalogowani lub wygasła ważność sesji użytkownika - <a href="allegro/konfiguracja_uzytkownicy.php">[SPRAWDŹ]</a></div></div>';

    }
    ?>

  </div>
  
  <div class="cl"></div>

</div>
