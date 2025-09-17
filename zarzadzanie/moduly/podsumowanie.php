<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $zapytanie = "SELECT * FROM modules_total ORDER BY status DESC, sortowanie";

    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / 200);
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
                                      array('Nazwa modułu','center'),
                                      array('Działanie','center'),
                                      array('Sort','center'),
                                      array('Status','center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['id']) {
                     $tekst .= '<tr class="pozycja_on">';
                   } else {
                     $tekst .= '<tr class="pozycja_off">';
                  }        

                  switch ($info['prefix']) {
                    case '1':
                      $dzialanie = '<span class="zielony">zwiększa wartość</span>';
                      break;
                    case '0':
                      $dzialanie = '<span class="czerwony">zmniejsza wartość</span>';
                      break;
                    case '9':
                      $dzialanie = 'nie wpływa';
                      break;
                  }

                  $tablica = array(array($info['id'],'center'),
                                   array($info['nazwa'],'left'),
                                   array($dzialanie,'center'),
                                   array($info['sortowanie'],'center')
                  );  

                  // domyslny
                  if ($info['status'] == '1') { $obraz = '<em class="TipChmurka"><b>Moduł jest włączony</b><img src="obrazki/aktywny_on.png" alt="Moduł jest włączony" /></em>'; } else { $obraz = '<em class="TipChmurka"><b>Moduł jest wyłączony</b><img src="obrazki/aktywny_off.png" alt="Moduł jest wyłączony" /></em>'; }              
                  $tablica[] = array($obraz,'center');                                    
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['id'];
                  $tekst .= '<a class="TipChmurka" href="moduly/podsumowanie_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  
                  if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) {
                    $tekst .= '<a class="TipChmurka" href="moduly/podsumowanie_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
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
            
            <div id="naglowek_cont">Moduły podsumowania zamówienia</div>     

            <?php if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) { ?>
            
            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="moduly/podsumowanie_dodaj.php">dodaj nową pozycję</a>
                </div>            
            </div>
            
            <?php } ?>            
            
            <div style="clear:both;"></div>               
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            <?php Listing::pokazAjax('moduly/podsumowanie.php', $zapytanie, $ile_licznika, $ile_pozycji, 'id', '200'); ?>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
