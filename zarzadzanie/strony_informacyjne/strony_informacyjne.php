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
        $warunki_szukania = " and (pd.pages_title like '%".$szukana_wartosc."%')";
        unset($szukana_wartosc);
    }
    
    if (isset($_GET['grupa']) && !empty($_GET['grupa'])) {
        $szukana_wartosc = $filtr->process($_GET['grupa']);
        $warunki_szukania = " and p.pages_group = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    } 
    
    if (isset($_GET['link'])) {
        if ( $_GET['link'] == 'tak' ) {
             $warunki_szukania = " and p.link != ''";
        }
        if ( $_GET['link'] == 'nie' ) {
             $warunki_szukania = " and p.link = ''";
        }        
    }     
    
    if (isset($_GET['miejsce']) && (int)($_GET['miejsce']) > 0) {
        $szukana_wartosc = (int)$_GET['miejsce'];
        $warunki_szukania = " and p.pages_modul = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }    

    // jezeli jest wybrana grupa klienta
    if (isset($_GET['klienci']) && (int)$_GET['klienci'] > 0) {
        $id_klienta = (int)$_GET['klienci'];
        $warunki_szukania .= " and find_in_set(" . $id_klienta . ", p.pages_customers_group_id) ";        
        unset($id_klienta);
    }      

    $zapytanie = "select distinct * from pages p, pages_description pd where p.pages_id = pd.pages_id and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' ".$warunki_szukania;
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
                $sortowanie = 'pd.pages_title asc';
                break;
            case "sort_a2":
                $sortowanie = 'pd.pages_title desc';
                break;                 
            case "sort_b1":
                $sortowanie = 'p.sort_order asc';
                break;
            case "sort_b2":
                $sortowanie = 'p.sort_order desc';
                break;                 
        }            
    } else { $sortowanie = 'p.sort_order asc'; }    
    
    $zapytanie .= " order by ".$sortowanie;    
    
    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
        
            $zapytanie .= " limit ".$_GET['parametr'];    
            
            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID', 'center'),
                                      array('Tytuł strony', 'center'),
                                      array('Grupa', 'center'),
                                      array('Sort', 'center'),
                                      array('Link zewnętrzny', 'center'),
                                      array('Indeksowanie przez wyszukiwarki', 'center', '', 'class="ListingRwd"'),
                                      array('Miejsce wyświetlania', 'center'),
                                      array('Grupa klientów', 'center'),
                                      array('Status', 'center', 'white-space: nowrap'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['pages_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['pages_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['pages_id'].'">';
                  }       

                  $tablica = array();
                  
                  $tablica[] = array($info['pages_id'],'center');
                  
                  $tek = '';
                  // plik php czy strona informacyjna
                  if (!empty($info['link'])) { 
                      //
                      $tek = '<span class="Link">'.$info['pages_title'].'</span>'; 
                      //
                    } else {
                      //
                      $tek = '<span class="Strona">'.$info['pages_title'].'</span>'; 
                      //                  
                  }              
                  $tablica[] = array($tek);                  
                  $tablica[] = array( ($info['pages_group'] != '' ? $info['pages_group'] : '---' ),'center');

                  $tablica[] = array( $info['sort_order'],'center');

                  // zew czy wew
                  if (!empty($info['link'])) { $obraz = 'aktywny_on.png'; $alt = 'Ta strona jest jako link zewnętrzny'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ta strona jest jako tekst'; }              
                  $tablica[] = array('<em class="TipChmurka"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></em>','center');
                  
                  // indeksowanie
                  if ($info['nofollow'] == 0) { $obraz = 'aktywny_on.png'; $alt = 'Ta strona będzie indeksowana przez wyszukiwarki'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ta strona NIE będzie indeksowana przez wyszukiwarki'; }              
                  $tablica[] = array('<em class="TipChmurka"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></em>','center', '', 'class="ListingRwd"');                  
                  
                  // wyswietlanie w boxie czy w module
                  $wyswietlanie = '-';
                  if ($info['pages_modul'] == 1) { $wyswietlanie = '<em class="TipChmurka"><b>Treść tej strony jest wyświetlana w module środkowym</b><img src="obrazki/wyswietlanie_modul.png" alt="Moduł" /></em>'; }
                  if ($info['pages_modul'] == 2) { $wyswietlanie = '<em class="TipChmurka"><b>Treść tej strony jest wyświetlana w boxie</b><img src="obrazki/wyswietlanie_box.png" alt="Box" /></em>'; }
                  
                  $tablica[] = array($wyswietlanie,'center');
                  unset($wyswietlanie);
                  
                  $tgm = '';
                  $tabGrup = explode(',', (string)$info['pages_customers_group_id']);
                  if ( isset($tabGrup) && count($tabGrup) > 0 && $info['pages_customers_group_id'] != 0 ) {
                       foreach ( $tabGrup as $idGrupy ) {
                          $tgm .= '<span class="grupa_klientow">' . Klienci::pokazNazweGrupyKlientow($idGrupy) . '</span><br />';
                       }
                  }      
                  $tablica[] = array( (($tgm != '') ? $tgm : '-'),'center');
                  unset($tabGrup, $tgm);

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['pages_id']; 
                  
                  // aktywana czy nieaktywna
                  if ($info['status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Ta strona jest aktywna'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ta strona jest nieaktywna'; }              
                  $tablica[] = array('<a class="TipChmurka" href="strony_informacyjne/strony_informacyjne_status.php' . $zmienne_do_przekazania . '"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></a>','center'); 
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $tekst .= '<a class="TipChmurka" href="strony_informacyjne/strony_informacyjne_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="strony_informacyjne/strony_informacyjne_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  
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
            
            <div id="naglowek_cont">Strony informacyjne</div>

            <div id="wyszukaj">
                <form action="strony_informacyjne/strony_informacyjne.php" method="post" id="poForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="30" />
                </div>  
                
                <div class="wyszukaj_select">
                    <span>Wyświetl strony tylko dla grupy:</span>                
                    <?php
                    $zapytanie_tmp = "select distinct * from pages_group order by pages_group_code asc";
                    $sqls = $db->open_query($zapytanie_tmp);
                    //
                    $tablica = array();
                    $tablica[] = array('id' => 0, 'text' => '-- dowolna --');
                    while ($infs = $sqls->fetch_assoc()) { 
                        $tablica[] = array('id' => $infs['pages_group_code'], 'text' => $infs['pages_group_code'] . ' - ' . $infs['pages_group_title']);
                    }
                    $db->close_query($sqls); 
                    unset($zapytanie_tmp, $infs);                   

                    echo Funkcje::RozwijaneMenu('grupa', $tablica, ((isset($_GET['grupa'])) ? $filtr->process($_GET['grupa']) : ''));                  
                    ?>                    
                </div> 
                
                <div class="wyszukaj_select">
                    <span>Grupa klientów:</span>
                    <?php                         
                    echo Funkcje::RozwijaneMenu('klienci', Klienci::ListaGrupKlientow(true), ((isset($_GET['klienci'])) ? $filtr->process($_GET['klienci']) : '')); 
                    unset($tablica);
                    ?>
                </div>                  
                
                <div class="wyszukaj_select">
                    <span>Link zewnętrzny:</span>
                    <?php
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- dowolny --');
                    $tablica[] = array('id' => 'tak', 'text' => 'tak');
                    $tablica[] = array('id' => 'nie', 'text' => 'nie');
                    ?>                                          
                    <?php echo Funkcje::RozwijaneMenu('link', $tablica, ((isset($_GET['link'])) ? $filtr->process($_GET['link']) : '')); ?>
                </div> 

                <div class="wyszukaj_select">
                    <span>Miejsce wyświetlania:</span>
                    <?php
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '0', 'text' => '-- dowolne --');
                    $tablica[] = array('id' => '2', 'text' => 'w boxie');
                    $tablica[] = array('id' => '1', 'text' => 'w module');
                    ?>                                          
                    <?php echo Funkcje::RozwijaneMenu('miejsce', $tablica, ((isset($_GET['miejsce'])) ? $filtr->process($_GET['miejsce']) : '')); ?>
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
                  echo '<div id="wyszukaj_ikona"><a href="strony_informacyjne/strony_informacyjne.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 

                <div style="clear:both"></div>
            </div>        
            
            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="strony_informacyjne/strony_informacyjne.php?sort=sort_a1">tytuł rosnąco</a>
                <a id="sort_a2" class="sortowanie" href="strony_informacyjne/strony_informacyjne.php?sort=sort_a2">tytuł malejąco</a>
                <a id="sort_b1" class="sortowanie" href="strony_informacyjne/strony_informacyjne.php?sort=sort_b1">sortowanie rosnąco</a>
                <a id="sort_b2" class="sortowanie" href="strony_informacyjne/strony_informacyjne.php?sort=sort_b2">sortowanie malejąco</a>
                
            </div>             

            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="strony_informacyjne/strony_informacyjne_dodaj.php<?php echo Funkcje::Zwroc_Wybrane_Get(array('grupa')); ?>">dodaj nową stronę</a>
                </div>            
            </div>
            
            <div style="clear:both;"></div>               
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <div id="Legenda">
                <span class="Link"> strona jest linkiem zewnętrznym</span>
                <span class="Strona"> strona zawiera tekst</span>
            </div>       

            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('strony_informacyjne/strony_informacyjne.php', $zapytanie, $ile_licznika, $ile_pozycji, 'pages_id'); ?>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
