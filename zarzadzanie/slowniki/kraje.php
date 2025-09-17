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
        $warunki_szukania = " ".$warunek." sd.countries_name LIKE '%".$szukana_wartosc."%'";
    }

    $zapytanie = "SELECT * FROM countries s, countries_description sd WHERE s.countries_id = sd.countries_id AND sd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' " . $warunki_szukania. " order by sd.countries_name";

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
                                      array('Nazwa kraju','center'),
                                      array('Kod ISO-2','center'),
                                      array('Kod ISO-3','center'),
                                      array('Domyślny','center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['countries_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['countries_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['countries_id'].'">';
                  }     

                  $tablica = array(array($info['countries_id'],'center'),
                                   array($info['countries_name'],'center'),
                                   array($info['countries_iso_code_2'],'center'),
                                   array($info['countries_iso_code_3'],'center')
                  );  

                  // domyslny
                  if ($info['countries_default'] == '1') { $obraz = '<em class="TipChmurka"><b>Ten kraj jest domyślny</b><img src="obrazki/aktywny_on.png" alt="Ten kraj jest domyślny" />'; } else { $obraz = '-'; }              
                  $tablica[] = array($obraz,'center');                                    
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['countries_id'];
                  $tekst .= '<a class="TipChmurka" href="slowniki/kraje_wojewodztwa.php?kraj_id='.$info['countries_id'].'"><b>Lista województw/regionów</b><img src="obrazki/lista_wojewodztw.png" alt="Lista województw" /></a>';
                  $tekst .= '<a class="TipChmurka" href="slowniki/kraje_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  if ( $info['countries_default'] != '1' ) {
                    $tekst .= '<a class="TipChmurka" href="slowniki/kraje_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  } else {
                    $tekst .= '<em class="TipChmurka"><b>Nie można usunąć domyślnego kraju</b><img src="obrazki/kasuj_off.png" alt="Nie można usunąć domyślnego kraju" /></em>';
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
          $.AutoUzupelnienie( 'szukaj', 'PodpowiedziMale', 'ajax/autouzupelnienie_kraje.php', 50 );
        });
        </script>          

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Kraje</div>     

            <div id="wyszukaj">
                <form action="slowniki/kraje.php" method="post" id="poForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj kraj:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="60" />
                </div>  
                
                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="slowniki/kraje.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 

                <div style="clear:both"></div>
            </div>        

            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="slowniki/kraje_dodaj.php">dodaj nową pozycję</a>
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
            <?php Listing::pokazAjax('slowniki/kraje.php', $zapytanie, $ile_licznika, $ile_pozycji, 'countries_id'); ?>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
