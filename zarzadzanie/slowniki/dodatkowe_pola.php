<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
    
    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && $_GET['szukaj'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " where products_extra_fields_name LIKE '%".$szukana_wartosc."%'";
    }    

    $zapytanie = "select * from products_extra_fields " . $warunki_szukania;
    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }
    $db->close_query($sql);
    
    // jezeli jest sortowanie
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case "sort_a1":
                $sortowanie = 'products_extra_fields_order, products_extra_fields_name';
                break;
            case "sort_a2":
                $sortowanie = 'products_extra_fields_name asc, products_extra_fields_order';
                break; 
            case "sort_a3":
                $sortowanie = 'products_extra_fields_name desc, products_extra_fields_order';
                break;                  
            case "sort_a4":
                $sortowanie = 'languages_id';
                break;
            case "sort_a5":
                $sortowanie = 'products_extra_fields_filter desc';
                break; 
            case "sort_a6":
                $sortowanie = 'products_extra_fields_search desc';
                break;         
            case "sort_a7":
                $sortowanie = 'products_extra_fields_location';
                break;                  
        }            
    } else { $sortowanie = 'products_extra_fields_order, products_extra_fields_name'; }    
    
    $zapytanie .= " order by ".$sortowanie;        

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr'];
            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID','center'),
                                      array('Nazwa pola'),                                      
                                      array('Ikona','center'),                                      
                                      array('Dostępne dla języka','center'),
                                      array('Wyświetlane jako obrazek','center'),
                                      array('Tylko format liczbowy', 'center','','class="ListingSchowajMobile"'),                                      
                                      array('Filtry', 'center','','class="ListingSchowajMobile"'),                                      
                                      array('Wyszukiwanie','center','','class="ListingSchowajMobile"'),
                                      array('Porównywarka','center','','class="ListingSchowajMobile"'),
                                      array('Położenie', 'center','','class="ListingSchowajMobile"'),
                                      array('Widoczny na karcie produktu','center'),
                                      array('Allegro','center'),
                                      array('Sort','center'),
                                      array('Status','center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['products_extra_fields_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['products_extra_fields_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['products_extra_fields_id'].'">';
                  }    

                  $tablica = array(array($info['products_extra_fields_id'] . '<input type="hidden" name="id[]" value="'.$info['products_extra_fields_id'].'" />','center'),
                                   array($info['products_extra_fields_name'])); 

                  // ikonka            
                  $tablica[] = array(((file_exists('../' . KATALOG_ZDJEC . '/' . $info['products_extra_fields_icon']) && !empty($info['products_extra_fields_icon'])) ? Funkcje::pokazObrazek($info['products_extra_fields_icon'], $info['products_extra_fields_name'], '30', '30', ' class="Reload"', true) : '-'),'center', '', 'class="ListingSchowaj"');

                  $jaki_jezyk = 'wszystkie dostępne';
                  $jezyki = Funkcje::TablicaJezykow();
                  for ($w = 0, $c = count($jezyki); $w < $c; $w++) {
                       if ($jezyki[$w]['id'] == $info['languages_id']) {
                           $jaki_jezyk = $jezyki[$w]['text'];
                       }
                  }
                  $tablica[] = array($jaki_jezyk,'center');
                  
                  // czy jako obrazek
                  if ($info['products_extra_fields_image'] == '1') { $obraz = '<em class="TipChmurka"><b>Dodatkowe pole w formie obrazka</b><img src="obrazki/image_cechy.png" alt="Dodatkowe pole w formie obrazka" /></em>'; } else { $obraz = '-'; }              
                  $tablica[] = array($obraz,'center');  
                  
                  // tylko w formie liczb
                  if ($info['products_extra_fields_number'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'To pole jest wyświetlane tylko w formie numerycznej'; } else { $obraz = 'aktywny_off.png'; $alt = 'To pole może zawierać dowolne znaki'; }               
                  $tablica[] = array('<em class="TipChmurka"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></em>','center','','class="ListingSchowajMobile"');                    

                  // do filtrow
                  if ($info['products_extra_fields_filter'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'To pole jest wyświetlane w filtrach w listingu produktów'; } else { $obraz = 'aktywny_off.png'; $alt = 'To pole nie jest wyświetlane w filtrach w listingu produktów'; }               
                  $tablica[] = array('<em class="TipChmurka"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></em>','center','','class="ListingSchowajMobile"');                    

                  // do wyszukiwania
                  if ($info['products_extra_fields_search'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'To pole jest wyświetlane w wyszukiwaniu zaawansowanym'; } else { $obraz = 'aktywny_off.png'; $alt = 'To pole nie jest wyświetlane w wyszukiwaniu zaawansowanym'; }               
                  $tablica[] = array('<em class="TipChmurka"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></em>','center','','class="ListingSchowajMobile"');                    

                  // do porownywarki
                  if ($info['products_extra_fields_compare'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'To pole jest wyświetlane w porównywarce produktów'; } else { $obraz = 'aktywny_off.png'; $alt = 'To pole nie jest wyświetlane w porównywarce produktów'; }               
                  $tablica[] = array('<em class="TipChmurka"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></em>','center','','class="ListingSchowajMobile"');                                      
                  
                  // polozenie
                  if ($info['products_extra_fields_view'] == '1') {
                      switch ($info['products_extra_fields_location']) {
                          case "foto":                  
                              $polozenie = 'Obok zdjęcia';
                              break;
                          case "opis":                  
                              $polozenie = 'Pod opisem produktu';
                              break;
                          default:
                              $polozenie = 'Pod opisem produktu';
                              break;
                      }
                    } else {
                      $polozenie = '-';
                  }
                  $tablica[] = array($polozenie, 'center');                   
                  unset($polozenie);
                  
                  // widoczny na karcie produktu czy nie
                  if ($info['products_extra_fields_view'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'To pole jest widoczne na karcie produktu'; } else { $obraz = 'aktywny_off.png'; $alt = 'To pole nie jest widoczne na karcie produktu'; }               
                  $tablica[] = array('<a class="TipChmurka" href="slowniki/dodatkowe_pola_widocznosc.php?id_poz='.$info['products_extra_fields_id'].'"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></a>','center','','class="ListingSchowajMobile"');                                      
                  
                  // przekazywany do allegro
                  if ($info['products_extra_fields_allegro'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'To pole jest widoczne na aukcjach Allegro'; } else { $obraz = 'aktywny_off.png'; $alt = 'To pole nie jest widoczne na aukcjach Allegro'; }               
                  $tablica[] = array('<a class="TipChmurka" href="slowniki/dodatkowe_pola_widocznosc_allegro.php?id_poz='.$info['products_extra_fields_id'].'"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></a>','center');                                      
                  
                  // sort
                  $tablica[] = array('<input type="text" name="sort_'.$info['products_extra_fields_id'].'" value="'.$info['products_extra_fields_order'].'" class="sort_prod" />','center');  

                  // aktywana czy nieaktywna
                  if ($info['products_extra_fields_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'To pole jest aktywne'; } else { $obraz = 'aktywny_off.png'; $alt = 'To pole jest nieaktywne'; }               
                  $tablica[] = array('<a class="TipChmurka" href="slowniki/dodatkowe_pola_status.php?id_poz='.$info['products_extra_fields_id'].'"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></a>','center');                    
                                    
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['products_extra_fields_id'];
                  $tekst .= '<a class="TipChmurka" href="slowniki/dodatkowe_pola_slowniki.php'.$zmienne_do_przekazania.'"><b>Słownik nazw dodatkowego pola</b><img src="obrazki/lista_wojewodztw.png" alt="Słownik nazw dodatkowego pola" /></a>';
                  $tekst .= '<a class="TipChmurka" href="slowniki/dodatkowe_pola_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="slowniki/dodatkowe_pola_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  
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
            
            <div id="naglowek_cont">Dodatkowe pola do produktów</div> 
            
            <div id="wyszukaj">
                <form action="slowniki/dodatkowe_pola.php" method="post" id="poForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj pole:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="60" />
                </div>  
                
                <?php 
                // tworzy ukryte pola hidden do wyszukiwania - filtra
                if (isset($_GET['sort'])) { 
                    echo '<div><input type="hidden" name="sort" value="'.$filtr->process($_GET['sort']).'" /></div>';
                }                
                ?>                 
                
                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="slowniki/dodatkowe_pola.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 

                <div style="clear:both"></div>
            </div>              

            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="slowniki/dodatkowe_pola.php?sort=sort_a1">wg sortowania</a>
                <a id="sort_a2" class="sortowanie" href="slowniki/dodatkowe_pola.php?sort=sort_a2">nazwy rosnąco</a>
                <a id="sort_a3" class="sortowanie" href="slowniki/dodatkowe_pola.php?sort=sort_a3">nazwy malejąco</a>
                <a id="sort_a4" class="sortowanie" href="slowniki/dodatkowe_pola.php?sort=sort_a4">przypisany język</a>
                <a id="sort_a5" class="sortowanie" href="slowniki/dodatkowe_pola.php?sort=sort_a5">przypisanie do filtra</a>
                <a id="sort_a6" class="sortowanie" href="slowniki/dodatkowe_pola.php?sort=sort_a6">przypisanie do wyszukiwania</a>
                <a id="sort_a7" class="sortowanie" href="slowniki/dodatkowe_pola.php?sort=sort_a7">położenie</a>
            
            </div>              

            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="slowniki/dodatkowe_pola_dodaj.php">dodaj nową pozycję</a>
                </div>            
            </div>
            
            <div style="clear:both;"></div>      

            <form action="slowniki/dodatkowe_pola_akcja.php" method="post" class="cmxform">            
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('slowniki/dodatkowe_pola.php', $zapytanie, $ile_licznika, $ile_pozycji, 'products_extra_fields_id'); ?>
            </script>             

            <?php if ($ile_pozycji > 0) { ?>
            <div>
            <input type="submit" style="float:right" class="przyciskNon" value="Zapisz zmiany" />
            </div>
            <?php } ?>            
            
            <div class="cl"></div>
            
            </form>
            
        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
