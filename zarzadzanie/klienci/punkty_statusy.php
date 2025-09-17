<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $zapytanie = "select * from customers_points_status s, customers_points_status_description sd where s.points_status_id = sd.points_status_id and sd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' order by s.points_status_id";
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
            
            // informacje o produktach - zakres
            $zapytanie .= " limit ".$_GET['parametr'];
            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID','center'),
                                      array('Nazwa','center'),
                                      array('Kolor','center'),
                                      array('Domyślny','center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['points_status_id']) {
                     $tekst .= '<tr class="pozycja_on">';
                   } else {
                     $tekst .= '<tr class="pozycja_off">';
                  }        

                  $tablica = array(array($info['points_status_id'],'center'),
                                   array($info['points_status_name'])); 

                  // kolor      
                  $tablica[] = array('<span class="StatusPunktowKolor" style="background:#'.$info['points_status_color'].'">&nbsp;</span>','center');                                   

                  // domyslny
                  $domyslny = '-';
                  if ( $info['points_status_id'] == '1') {
                      $domyslny = 'Domyślny status dla nowo nabytych punktów';
                  }
                  if ( $info['points_status_id'] == '2') {
                      $domyslny = 'Domyślny status dla zatwierdzonych punktów';
                  }                  
                  if ( $info['points_status_id'] == '4') {
                      $domyslny = 'Domyślny status dla punktów wydanych na zakupy';
                  }                  
                  $tablica[] = array($domyslny,'center');                    

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['points_status_id'];
                  $tekst .= '<a class="TipChmurka" href="klienci/punkty_statusy_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  
                  if ( $info['points_status_id'] != '1' && $info['points_status_id'] != '2' && $info['points_status_id'] != '3' && $info['points_status_id'] != '4') {
                      $tekst .= '<a class="TipChmurka" href="klienci/punkty_statusy_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
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
            
            <div id="naglowek_cont">Statusy punktów</div>     

            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="klienci/punkty_statusy_dodaj.php">dodaj nową pozycję</a>
                </div>            
            </div>
            
            <div style="clear:both;"></div>               
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            <?php Listing::pokazAjax('klienci/punkty_statusy.php', $zapytanie, $ile_licznika, $ile_pozycji, 'points_status_id'); ?>
            </script>              

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
