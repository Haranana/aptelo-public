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
    if (isset($_GET['szukaj'])) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " and (pd.poll_name like '%".$szukana_wartosc."%')";
    }

    $zapytanie = "select distinct p.id_poll, p.poll_login, p.poll_status, p.poll_date_added, pd.poll_name from poll p, poll_description pd where p.id_poll = pd.id_poll and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' ".$warunki_szukania;
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
                $sortowanie = 'p.poll_date_added desc';
                break;
            case "sort_a2":
                $sortowanie = 'p.poll_date_added asc';
                break;                 
        }            
    } else { $sortowanie = 'p.poll_date_added desc'; }    
    
    $zapytanie .= " order by ".$sortowanie;  
    
    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr'];     

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID', 'center'),
                                      array('Nazwa ankiety'),
                                      array('Data dodania', 'center', 'white-space: nowrap'),
                                      array('Odpowiedzi dla języka domyślnego', '', '', 'class="ListingSchowaj"'),
                                      array('Widoczna dla wszystkich', 'center'),
                                      array('Status', 'center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['id_poll']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['id_poll'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['id_poll'].'">';
                  }      

                  $tablica = array();
                  
                  $tablica[] = array($info['id_poll'],'center');
                  
                  $tablica[] = array($info['poll_name']);
                  
                  $tablica[] = array(((Funkcje::czyNiePuste($info['poll_date_added'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['poll_date_added'])) : '-'),'center','white-space:nowrap'); 
                  
                  // ile jest w sumie glosow
                  $ile_glosow = $db->open_query("select SUM(poll_result) as ile_glosow, COUNT('poll_result') as ile_pozycji from poll_field where id_poll = '".(int)$info['id_poll']."' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
                  $infr = $ile_glosow->fetch_assoc();
                  $db->close_query($ile_glosow);               

                  // odpowiedzi
                  $wyniki_ankiety = '<div class="OdpowiedziTlo"><table class="Odpowiedzi">';
                  $odpowiedzi = $db->open_query("select * from poll_field where id_poll = '".(int)$info['id_poll']."' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' order by poll_field_sort");
                  $poz = 1;
                  while ($infs = $odpowiedzi->fetch_assoc()) {
                        //
                        // szerokosc pixela w slupku
                        $szerokosc_slupka = 0;
                        $ilosc_procent = 0;
                        if ($infs['poll_result'] > 0) {
                            $szerokosc_slupka = ((int)(($infs['poll_result'] / $infr['ile_glosow']) * 145) + 1);
                            $ilosc_procent = (int)(($infs['poll_result'] / $infr['ile_glosow']) * 100);
                        }
                        //
                        $czyDacPadding = '';
                        if ($poz == $infr['ile_pozycji']) {
                            $czyDacPadding = ' style="padding-bottom:30px"';
                        }
                        //
                        $wyniki_ankiety .= '<tr>
                                                <td class="Odpowiedz" '.$czyDacPadding.'>'.$infs['poll_field'].'</td>
                                                <td class="Slupek" '.$czyDacPadding.'><div style="width:'.$szerokosc_slupka.'px"></div></td>
                                                <td class="Procent" '.$czyDacPadding.'>'.$infs['poll_result'].' głosów <span>('.$ilosc_procent.'%)</span></td>
                                            </tr>';
                        $poz++;
                  }
                  $db->close_query($odpowiedzi);
                  $wyniki_ankiety .= '</table></div>';
                  
                  $tablica[] = array($wyniki_ankiety, '', '', 'class="ListingSchowaj"');  
                  
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['id_poll'];                
                  
                  // czy tylko dla zalogowanych czy dla wszystkich
                  if ($info['poll_login'] == '1') { $obraz = 'aktywny_off.png'; $alt = 'Ta ankieta jest dostępna tylko dla zalogowanych klientów'; } else { $obraz = 'aktywny_on.png'; $alt = 'Ta ankieta jest dostępna dla wszystkich klientów'; }              
                  $tablica[] = array('<a class="TipChmurka" href="ankiety/ankiety_klienci.php'.$zmienne_do_przekazania.'"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></a>','center');                   
                  
                  // aktywana czy nieaktywna
                  if ($info['poll_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Ta ankieta jest aktywna'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ta ankieta jest nieaktywna'; }              
                  $tablica[] = array('<a class="TipChmurka" href="ankiety/ankiety_status.php'.$zmienne_do_przekazania.'"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></a>','center');                  
                  
                  unset($infr, $infs, $ile_glosow_wynik, $ile_max_glosow, $wyniki_ankiety, $szerokosc_slupka, $ilosc_procent);
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';

                  $tekst .= '<a class="TipChmurka" href="ankiety/ankiety_info.php'.$zmienne_do_przekazania.'"><b>Szczegóły</b><img src="obrazki/info_klient.png" alt="Szczegóły" /></a>';
                  $tekst .= '<a class="TipChmurka" href="ankiety/ankiety_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="ankiety/ankiety_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  
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
            
            <div id="naglowek_cont">Ankiety</div>

            <div id="wyszukaj">
                <form action="ankiety/ankiety.php" method="post" id="poForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj ankietę:</span>
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
                  echo '<div id="wyszukaj_ikona"><a href="ankiety/ankiety.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 

                <div style="clear:both"></div>
            </div>        
            
            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="ankiety/ankiety.php?sort=sort_a1">data dodania rosnąco</a>
                <a id="sort_a2" class="sortowanie" href="ankiety/ankiety.php?sort=sort_a2">data dodania malejąco</a>
            
            </div>             

            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="ankiety/ankiety_dodaj.php">dodaj nową ankietę</a>
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
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('ankiety/ankiety.php', $zapytanie, $ile_licznika, $ile_pozycji, 'id_poll'); ?>
            </script>                

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
