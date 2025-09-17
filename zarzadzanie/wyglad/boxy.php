<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do boxu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
    
    $warunek = ' and (p.box_v2 = 0 or p.box_v2 = 2)';
    
    if ( Wyglad::TypSzablonu() == true ) {
         //
         $warunek = ' and (p.box_v2 = 1 or p.box_v2 = 2)';
         //
    }    
    
    if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) {
         //
         $warunek = ' and (p.box_v2 = 0 or p.box_v2 = 1 or p.box_v2 = 2)';
         //
    }        

    $zapytanie = "select * from theme_box p, theme_box_description pd where p.box_id = pd.box_id and language_id = '" . (int)$_SESSION['domyslny_jezyk']['id'] . "'" . $warunek . " order by p.box_display, pd.box_title asc";
    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / 300);
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
                                      array('Nazwa boxu'),
                                      array('Rodzaj boxu'),
                                      array('Nagłówek boxu', 'center', '', 'class="ListingSchowaj"'),
                                      array('Co wyświetla ?','center', 'white-space: nowrap'),
                                      array('Opis boxu', '', '', 'class="ListingSchowaj"'),
                                      array('Miejsce wyświetlania','center'),
                                      array('Wyświetlany w sklepie','center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['box_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['box_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['box_id'].'">';
                  }   

                  $tablica = array(array($info['box_id'],'center'),
                                   array($info['box_title']));
                                   
                  $tek = '';
                  // plik php czy strona informacyjna
                  if ($info['box_type'] == 'plik') { 
                      //
                      $tek = '<span class="Plik">'.$info['box_file'].'</span>'; 
                      //
                  }
                  if ($info['box_type'] == 'java') { 
                      //
                      $tek = '<span class="KodJava">Skrypt</span>'; 
                      //                  
                  }
                  if ($info['box_type'] == 'txt') { 
                      //
                      $tek = '<span class="Txt">Dowolny tekst</span>'; 
                      //                  
                  }                         
                  if ($info['box_type'] == 'strona') { 
                      //
                      // nazwa strony informacyjnej
                      $strony = $db->open_query("select distinct pd.pages_title from pages_description pd where pd.pages_id = '".(int)$info['box_pages_id']."' and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
                      $sql_strona = $strony->fetch_assoc();
                      $tek = '<span class="Strona">'.$sql_strona['pages_title'].'</span>'; 
                      
                      $db->close_query($strony);
                      unset($strony);
                      
                  }              
                  $tablica[] = array($tek);   
                  unset($tek);

                  // naglowek czy bez
                  if ($info['box_header'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Nagłówek jest wyświetlany w boxie'; } else { $obraz = 'aktywny_off.png'; $alt = 'Nagłówek nie jest wyświetlany w boxie'; }              
                  $tablica[] = array('<em class="TipChmurka"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></em>', 'center', '', 'class="ListingSchowaj"');      

                  $tablica[] = array($info['box_display'],'center');                 
                  
                  $tablica[] = array($info['box_description'], '',  '', 'class="ListingSchowaj"');
                  
                  // miejsce wyswietlania
                  $wyswietlanie = '';
                  
                  if ($info['box_status'] == '1') {
                    if ($info['box_column'] == 'lewa') { $wyswietlanie = '<em class="TipChmurka"><b>Box jest wyświetlany w lewej kolumnie z boxami</b><img src="obrazki/wyswietlanie_box_lewa_kolumna.png" alt="Box lewa kolumna" /></em> <br />'; }
                    if ($info['box_column'] == 'prawa') { $wyswietlanie = '<em class="TipChmurka"><b>Box jest wyświetlany w prawek kolumnie z boxami</b><img src="obrazki/wyswietlanie_box_prawa_kolumna.png" alt="Box prawa kolumna" /></em> <br />'; }
                  }
                  
                  if ($info['box_localization'] == '1') { 
                      $wyswietlanie .= 'wszędzie';
                  }
                  if ($info['box_localization'] == '3') { 
                      $wyswietlanie .= 'podstrony' . ((empty($info['box_localization_site'])) ? '<br />(wszystkie)' : '<br />(wybrane)');
                  }
                  if ($info['box_localization'] == '2') { 
                      $wyswietlanie .= 'strona główna';
                  } 
                  $tablica[] = array($wyswietlanie,'center');   
                  unset($wyswietlanie);     

                  // aktywany czy nieaktywny
                  if ($info['box_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Ten box jest wyświetlany w sklepie'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ten box nie jest wyświetlany w sklepie'; }              
                  $tablica[] = array('<em class="TipChmurka"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></em>','center');
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['box_id'];
                  $tekst .= '<a class="TipChmurka" href="wyglad/boxy_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="wyglad/boxy_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  
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
            
            <div id="naglowek_cont">Boxy</div>     

            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="wyglad/boxy_dodaj.php">dodaj nowy box</a>
                </div>            
            </div>
            
            <div style="clear:both;"></div>               
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <div class="Legenda">
                <span class="Txt"> box zawiera dowolny tekst</span>
                <span class="Plik"> box jest plikiem php</span>
                <span class="Strona"> box wyświetla zawartość strony informacyjnej</span>
                <span class="KodJava"> box wyświetla wynik działania skryptu</span>
            </div>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('wyglad/boxy.php', $zapytanie, $ile_licznika, $ile_pozycji, 'box_id', '300'); ?>
            </script>              
            
        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
