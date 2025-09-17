<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_SESSION['AllegroUser']) ) {
        unset($_SESSION['AllegroUser']);
    }

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $zapytanie = "SELECT *
                    FROM allegro_users";

    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }
    $db->close_query($sql);

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr'];
            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID','center'),
                                      array('Login'),
                                      array('UserId'),
                                      array('Klient ID'),
                                      array('Ważność sesji','center'),
                                      array('Domyślny','center'),
                                      array('Status','center')
                );
                                      
            echo $listing_danych->naglowek($tablica_naglowek);

            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['allegro_user_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['allegro_user_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['allegro_user_id'].'">';
                  }       

                  $tablica = array(array($info['allegro_user_id'] . '<input type="hidden" name="id[]" value="'.$info['allegro_user_id'].'" />','center'),
                                   array($info['allegro_user_login'],'left'),
                                   array($info['allegro_userid'],'left'),
                                   array($info['allegro_user_clientid'],'left'),
                                   array(date('d-m-Y H:i:s', $info['allegro_token_expires']),'center')
                  ); 

                  // domyslny
                  if ($info['allegro_user_default'] == '1') { $obraz = '<em class="TipChmurka"><b>To konto jest domyślne</b><img src="obrazki/aktywny_on.png" alt="To konto jest domyślne" /></em>'; } else { $obraz = '-'; }              
                  $tablica[] = array($obraz,'center');
                  unset($obraz);

                  // aktywana czy nieaktywna
                  if ($info['allegro_user_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'To konto jest nieaktywne'; } else { $obraz = 'aktywny_off.png'; $alt = 'To konto jest aktywne'; }               
                  $tablica[] = array('<img src="obrazki/'.$obraz.'" alt="'.$alt.'" />','center');
                  unset($obraz);

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['allegro_user_id'];

                  if ( $info['allegro_token_expires'] <= time() && $info['allegro_user_status'] == '1' && $info['allegro_user_clientid'] != '' ) {
                    $tekst .= '<a class="TipChmurka" href="allegro/allegro_logowanie.php'.$zmienne_do_przekazania.'"><b>Zaloguj</b><img src="obrazki/gosc_tak.png" alt="Zaloguj" /></a>';
                  } else {
                    $tekst .= '<a class="TipChmurka" href="allegro/konfiguracja_uzytkownicy_rozlacz.php'.$zmienne_do_przekazania.'"><b>Rozłącz z Allegro</b><img src="obrazki/blad.png" alt="Rozłącz z Allegro" /></a>';
                  }
                  $tekst .= '<a class="TipChmurka" href="allegro/konfiguracja_uzytkownicy_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';

                  if ( $info['allegro_user_default'] != '1' ) {
                    $tekst .= '<a class="TipChmurka" href="allegro/konfiguracja_uzytkownicy_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  } else {
                    $tekst .= '<em class="TipChmurka"><b>Nie można usunąć domyślnego użytkownika</b><img src="obrazki/kasuj_off.png" alt="Nie można usunąć domyślnego użytkownika" /></em>';
                  }                   
                  
                  $tekst .= '</td></tr>';
                  
            } 
            $tekst .= '</table>';
            //
            echo $tekst;
            //
            $db->close_query($sql);
            unset($listing_danych,$tekst,$tablica,$tablica_naglowek);        

        }
    }  
    
    // ******************************************************************************************************************************************************************
    // wyswietlanie listingu
    if (!isset($_GET['parametr'])) { 

        // wczytanie naglowka HTML
        include('naglowek.inc.php');
        ?>

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Zdefiniowani użytkownicy z kontami Allegro</div>     

            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="allegro/konfiguracja_uzytkownicy_dodaj.php">dodaj nową pozycję</a>
                </div>            
            </div>
            
            <div style="clear:both;"></div>      

            <form action="allegro/konfiguracja_uzytkownicy_akcja.php" method="post" class="cmxform">            
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            <?php Listing::pokazAjax('allegro/konfiguracja_uzytkownicy.php', $zapytanie, $ile_licznika, $ile_pozycji, 'allegro_user_id'); ?>
            </script>             

            <div class="cl"></div>
            
            </form>
            
        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
