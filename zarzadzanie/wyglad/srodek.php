<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
    
    if ( isset($_GET['usun_filtr']) ) {
         //
         unset($_GET[$_GET['usun_filtr']]);
         unset($_SESSION['filtry'][basename($_SERVER['SCRIPT_NAME'])][$_GET['usun_filtr']]);
         //
         Funkcje::PrzekierowanieURL('srodek.php');
         //
    }      
    
    $warunek = ' and (p.modul_v2 = 0 or p.modul_v2 = 2)';
    
    if ( Wyglad::TypSzablonu() == true) {
         //
         $warunek = ' and (p.modul_v2 = 1 or p.modul_v2 = 2)';
         //
    }
    
    if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) {
         //
         $warunek = ' and (p.modul_v2 = 0 or p.modul_v2 = 1 or p.modul_v2 = 2)';
         //
    }

    // jezeli jest szukanie po nazwie
    if (isset($_GET['szukaj']) && !empty($_GET['szukaj'])) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunek .= " and pd.modul_title like '%".$szukana_wartosc."%'";
        unset($szukana_wartosc);
    }   

    // jezeli jest szukanie po pliku
    if (isset($_GET['szukaj_plik']) && !empty($_GET['szukaj_plik'])) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_plik']);
        $warunek .= " and p.modul_file like '%".$szukana_wartosc."%'";
        unset($szukana_wartosc);
    }   

    // jezeli jest szukanie po opisie
    if (isset($_GET['szukaj_opis']) && !empty($_GET['szukaj_opis'])) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_opis']);
        $warunek .= " and p.modul_description like '%".$szukana_wartosc."%'";
        unset($szukana_wartosc);
    }     
    
    if ( isset($_GET['szukaj_status']) && $_GET['szukaj_status'] != '0' ) {
        $szukana_wartosc = ( $_GET['szukaj_status'] == '1' ? '1' : '0' );
        $warunek .= " and p.modul_status = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }    
    
    $zapytanie = "select * from theme_modules p, theme_modules_description pd where p.modul_id = pd.modul_id and language_id = '" . (int)$_SESSION['domyslny_jezyk']['id'] . "'" . $warunek . " order by p.modul_display, pd.modul_title asc";
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
            
            $tablica_naglowek = array(array('Akcja','center'),
                                      array('ID','center'),
                                      array('Nazwa modułu'),
                                      array('Rodzaj modułu'),
                                      array('Nagłówek modułu', 'center', '', 'class="ListingSchowaj"'),
                                      array('Co wyświetla ?','center', 'white-space: nowrap'),
                                      array('Opis modułu', '', '', 'class="ListingSchowaj"'),
                                      array('Miejsce wyświetlania','center'),
                                      array('Wyświetlany w sklepie','center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['modul_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['modul_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['modul_id'].'">';
                  }      
                  
                  $tablica = array();
                  
                  if ($info['modul_type'] == 'kreator' && ( Wyglad::TypSzablonu() == true || ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ))) {                  
                      $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" value="'.$info['modul_id'].'" id="opcja_'.$info['modul_id'].'" /><label class="OpisForPustyLabel" for="opcja_'.$info['modul_id'].'"></label><input type="hidden" name="id[]" value="'.$info['modul_id'].'" />','center');
                  } else {
                      $tablica[] = array('-','center');
                  }

                  $tablica[] = array($info['modul_id'],'center');
                  $tablica[] = array($info['modul_title']);
                                   
                  $tek = '';
                  // plik php czy strona informacyjna
                  if ($info['modul_type'] == 'plik') { 
                      //
                      $tek = '<span class="Plik">'.$info['modul_file'].'</span>'; 
                      //
                  }
                  if ($info['modul_type'] == 'java') { 
                      //
                      $tek = '<span class="KodJava">Skrypt</span>'; 
                      //                  
                  }
                  if ($info['modul_type'] == 'txt') { 
                      //
                      $tek = '<span class="Txt">Dowolny tekst</span>'; 
                      //                  
                  } 
                  if ($info['modul_type'] == 'kreator') { 
                      //
                      $tek = '<span class="Kreator">Wygenerowany w kreatorze modułów</span>'; 
                      //                  
                  }                   
                  if ($info['modul_type'] == 'strona') { 
                      //
                      // nazwa strony informacyjnej
                      $strony = $db->open_query("select distinct pd.pages_title from pages_description pd where pd.pages_id = '".(int)$info['modul_pages_id']."' and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
                      $sql_strona = $strony->fetch_assoc();
                      $tek = '<span class="Strona">'.$sql_strona['pages_title'].'</span>'; 
                      
                      $db->close_query($strony);
                      unset($strony);
                      
                  }              
                  $tablica[] = array($tek);    

                  // naglowek czy bez
                  if ($info['modul_type'] != 'kreator') { 
                      if ($info['modul_header'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Nagłówek jest wyświetlany w module'; } else { $obraz = 'aktywny_off.png'; $alt = 'Nagłówek nie jest wyświetlany w module'; }              
                      $tablica[] = array('<em class="TipChmurka"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></em>', 'center', '', 'class="ListingSchowaj"');  
                  } else {
                      $tablica[] = array('-', 'center', '', 'class="ListingSchowaj"');  
                  }

                  $tablica[] = array($info['modul_display'],'center');                  
                  
                  $tablica[] = array($info['modul_description'], '',  '', 'class="ListingSchowaj"');

                  // miejsce wyswietlania
                  $wyswietlanie = '';
                  
                  if ($info['modul_status'] == '1') {
                    if ($info['modul_position'] == 'gora') { $wyswietlanie = '<em class="TipChmurka"><b>Moduł jest wyświetlany w części górnej sklepu</b><img src="obrazki/wyswietlanie_modul_gora.png" alt="Moduł góra" /></em> <br />'; }
                    if ($info['modul_position'] == 'srodek') { $wyswietlanie = '<em class="TipChmurka"><b>Moduł jest wyświetlany w części środkowej sklepu</b><img src="obrazki/wyswietlanie_modul_srodek.png" alt="Moduł środek" /></em> <br />'; }
                    if ($info['modul_position'] == 'dol') { $wyswietlanie = '<em class="TipChmurka"><b>Moduł jest wyświetlany w części dolnej sklepu</b><img src="obrazki/wyswietlanie_modul_dol.png" alt="Moduł dół" /></em> <br />'; }
                  }
                  
                  if ($info['modul_localization'] == '1') { 
                      $wyswietlanie .= 'wszędzie';
                  }
                  if ($info['modul_localization'] == '3') { 
                      $wyswietlanie .= 'podstrony' . ((empty($info['modul_localization_site'])) ? '<br />(wszystkie)' : '<br />(wybrane)');
                  }
                  if ($info['modul_localization'] == '4') { 
                      $wyswietlanie .= 'podstrony (wybrane)';
                  }                  
                  if ($info['modul_localization'] == '2') { 
                      $wyswietlanie .= 'strona główna';
                  } 
                  $tablica[] = array($wyswietlanie,'center');   
                  unset($wyswietlanie);     
                    
                  // aktywany czy nieaktywny
                  if ($info['modul_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Ten moduł jest wyświetlany w sklepie'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ten moduł nie jest wyświetlany w sklepie'; }              
                  $tablica[] = array('<em class="TipChmurka"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></em>','center');
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['modul_id'];
                  if ($info['modul_type'] != 'kreator') { 
                      $tekst .= '<a class="TipChmurka" href="wyglad/srodek_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  } else {
                      $tekst .= '<a class="TipChmurka" href="wyglad/srodek_kreator_modulow.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                      $tekst .= '<a class="TipChmurka" href="wyglad/srodek_kreator_modulow_duplikuj.php'.$zmienne_do_przekazania.'"><b>Duplikuj moduł kreatora</b><img src="obrazki/duplikuj.png" alt="Duplikuj moduł kreatora" /></a>';                   
                  }
                  $tekst .= '<a class="TipChmurka" href="wyglad/srodek_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  
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
            
            <div id="naglowek_cont">Moduły środkowe</div>     
            
            <div id="wyszukaj">
                <form action="wyglad/srodek.php" method="post" id="poForm" class="cmxform">

                <div class="wyszukaj_select">
                    <span>Wyszukaj moduł po nazwie:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="30" />
                </div>  
                
                <div class="wyszukaj_select">
                    <span>Wyszukaj moduł po nazwie pliku:</span>
                    <input type="text" name="szukaj_plik" id="szukaj_plik" value="<?php echo ((isset($_GET['szukaj_plik'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj_plik'])) : ''); ?>" size="30" />
                </div>

                <div class="wyszukaj_select">
                    <span>Wyszukaj moduł po opisie:</span>
                    <input type="text" name="szukaj_opis" id="szukaj_opis" value="<?php echo ((isset($_GET['szukaj_opis'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj_opis'])) : ''); ?>" size="30" />
                </div>             

                <div class="wyszukaj_select">
                    <span>Status:</span>
                    <?php
                    $tablica_status= Array();
                    $tablica_status[] = array('id' => '', 'text' => '--- dowolny ---');
                    $tablica_status[] = array('id' => '1', 'text' => 'wyświetlany w sklepie');
                    $tablica_status[] = array('id' => '2', 'text' => 'nie wyświetlany w sklepie');
                    echo Funkcje::RozwijaneMenu('szukaj_status', $tablica_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : '')); ?>
                </div>  

                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="wyglad/srodek.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 

                <div style="clear:both"></div>
            </div>               

            <div id="PozycjeIkon">
            
                <div class="lf">
            
                    <a class="dodaj" href="wyglad/srodek_dodaj.php">dodaj nowy moduł</a>

                    <?php if ( Wyglad::TypSzablonu() == true || ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' )) { ?>
                    <a class="dodaj" href="wyglad/srodek_kreator_modulow.php">kreator nowych modułów</a>                          
                    <?php } ?>
                    
                </div>
                
                <?php if ( Wyglad::TypSzablonu() == true || ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' )) { ?>
                <div class="rg">  
                
                    <a class="Import" href="wyglad/srodek_kreator_modulow_import.php">importuj dane modułów kreatora</a>
                    
                </div>    
                <?php } ?>
                
            </div>
            
            <div style="clear:both;"></div>   

            <form action="wyglad/srodek_kreator_modulow_eksport.php" method="post" class="cmxform"> 
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            
            <?php if ( Wyglad::TypSzablonu() == true || ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' )) { ?>
            
            <div id="akcja">

                <div id="akc">
                    Wykonaj akcje: 
                    <select name="akcja_dolna" id="akcja_dolna">
                        <option value="0"></option>
                        <option value="1">eksportuj wybrane moduły kreatora</option>                        
                    </select>
                </div>
                
                <div style="clear:both;"></div>
                
            </div>           

            <?php } ?>
                        
            <?php if ($ile_pozycji > 0 && ( Wyglad::TypSzablonu() == true || ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ))) { ?>
            <div><input type="submit" style="float:right" class="przyciskNon" value="Eksportuj dane" /></div>
            <?php } ?>        

            <div class="cl"></div>
            
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>

            <div class="cl"></div>

            <div class="Legenda">
                <span class="Kreator">moduł wygenerowany w kreatorze modułów</span>
                <span class="Txt"> moduł zawiera dowolny tekst</span>
                <span class="Plik"> moduł jest plikiem php</span>
                <span class="Strona"> moduł wyświetla zawartość strony informacyjnej</span>
                <span class="KodJava"> moduł wyświetla wynik działania skryptu</span>
            </div>            
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('wyglad/srodek.php', $zapytanie, $ile_licznika, $ile_pozycji, 'modul_id', '300'); ?>
            </script>   

            </form>

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
