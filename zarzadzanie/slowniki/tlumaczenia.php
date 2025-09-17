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
        $warunek = 'AND ';
        $warunki_szukania = " ".$warunek." (t.translate_value LIKE '%".$szukana_wartosc."%' or w.translate_constant LIKE '%".$szukana_wartosc."%')";
    }

    if ( isset($_GET['szukaj_sekcja']) && $_GET['szukaj_sekcja'] != '0' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_sekcja']);
        $warunek = 'AND';
        $warunki_szukania .= " ".$warunek." s.section_id = '".$szukana_wartosc."'";
    }

    $JezykID = $_SESSION['domyslny_jezyk']['id'];

    if ( strtolower((string)$_SESSION['domyslny_jezyk']['kod']) != 'pl' && strtolower((string)$_SESSION['domyslny_jezyk']['kod'] != 'en') ) {
        $JezykID = '1';
    }

    $zapytanie = "SELECT
                    w.translate_constant_id AS id, 
                    w.translate_constant AS wyrazenie, 
                    s.section_name AS sekcja, 
                    t.translate_value AS tresc
                FROM
                    translate_section AS s,
                    translate_constant AS w,
                    translate_value AS t
                WHERE
                    t.language_id = '".(int)$JezykID."' AND
                    w.section_id = s.section_id AND
                    t.translate_constant_id = w.translate_constant_id";
                
    $zapytanie .= $warunki_szukania;
    
    $zapytanie .= ' order by s.section_name, w.translate_constant';

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
            
            $tablica_naglowek = array(array('Sekcja','center'),
                                      array('Nazwa','center'),
                                      array('Tłumaczenie','center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['id'].'">';
                  }      

                  $tablica = array(array($info['sekcja']),
                                   array($info['wyrazenie']),
                                   array($info['tresc']));  

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right">';
                                  
                  $zmienne_do_przekazania = '?id_poz='.$info['id'];
                  $tekst .= '<a class="TipChmurka" href="slowniki/tlumaczenia_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';

                  if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) {
                    $tekst .= '<a class="TipChmurka" href="slowniki/tlumaczenia_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
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

        <script>
        $(document).ready(function() {
          <?php
          $sekcja = '';
          if ( isset($_GET['szukaj_sekcja']) && $_GET['szukaj_sekcja'] != '0' ) {
              $sekcja = '?sekcja=' . (int)$_GET['szukaj_sekcja'];
          }
          ?>
          $.AutoUzupelnienie( 'szukaj', 'PodpowiedziMale', 'ajax/autouzupelnienie_tlumaczenia.php<?php echo $sekcja; ?>', 50, 400 );
          <?php unset($sekcja); ?>
        });
        </script>     

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Tłumaczenia</div>     

            <div id="wyszukaj">
                <form action="slowniki/tlumaczenia.php" method="post" id="poForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj tekst :</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? $filtr->process($_GET['szukaj']) : ''); ?>" size="60" />
                </div>  

                <div class="wyszukaj_select">
                    <span>Sekcja:</span>
                    <?php
                    $tablica = Tlumaczenia::ListaSekcjiTlumaczen();
                    echo Funkcje::RozwijaneMenu('szukaj_sekcja', $tablica, ((isset($_GET['szukaj_sekcja'])) ? $filtr->process($_GET['szukaj_sekcja']) : '')); ?>
                </div>  

                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="slowniki/tlumaczenia.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 

                <div style="clear:both"></div>
            </div>        

            <div id="PozycjeIkon">
            
                <div class="lf">
                    <a class="dodaj" href="slowniki/tlumaczenia_dodaj.php<?php echo Funkcje::Zwroc_Get(array('id_poz','zakres')); ?>">dodaj nową pozycję</a>
                </div>      
                
                <div class="rg">
                    <a class="Export" href="slowniki/tlumaczenia_export.php">eksportuj dane do pliku</a>
                    <a class="Import" href="slowniki/tlumaczenia_import.php">importuj dane</a>
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
            <?php Listing::pokazAjax('slowniki/tlumaczenia.php', $zapytanie, $ile_licznika, $ile_pozycji, 'id'); ?>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
