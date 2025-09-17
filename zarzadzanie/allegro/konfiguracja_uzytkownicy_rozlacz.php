<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //

        $CzasSesji = time()-3600;
        $pola = array(
                array('allegro_user_authorizationtoken',''),
                array('allegro_user_refreshtoken',''),
                array('allegro_token_expires',$CzasSesji),
        );

        $db->update_query('allegro_users' , $pola, " allegro_user_id = '".(int)$_POST["id"]."'");
        unset($pola);

        Funkcje::PrzekierowanieURL('konfiguracja_uzytkownicy.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Rozłączenie konta z Allegro</div>
    <div id="cont">
          
          <form action="allegro/konfiguracja_uzytkownicy_rozlacz.php" method="post" class="cmxform">          

            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from allegro_users where allegro_user_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                while ($info = $sql->fetch_assoc()) {
                    ?>
                    <div class="poleForm">
                        <div class="naglowek">Edytowane konto Allegro - <?php echo $info['allegro_user_login']; ?></div>
                            <div class="pozycja_edytowana">
                        
                            <input type="hidden" name="akcja" value="zapisz" />
                        
                            <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                             <div class="maleInfo">
                                    Rozłączenie konta nie powoduje usunięcia aplikacji w Allegro - możliwe jest dalsze logowanie do API
                             </div>

                            <p>
                              Czy rozłączyć to konto z Allegro ?
                            </p>   
                         
                        </div>

                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Rozłącz konto" />
                          <button type="button" class="przyciskNon" onclick="cofnij('konfiguracja_uzytkownicy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
                        </div>
                    </div>                      
                    <?php
                }
            } else {
                ?>
                <div class="poleForm">
                    <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
                    <div class="przyciski_dolne">
                    <button type="button" class="przyciskNon" onclick="cofnij('konfiguracja_uzytkownicy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button>
                    </div>
                </div>
                <?php
            
            }
            $db->close_query($sql);
            unset($zapytanie);               
            ?>

          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}