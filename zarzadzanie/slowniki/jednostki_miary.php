<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $zapytanie = "select * from products_jm s, products_jm_description sd where s.products_jm_id = sd.products_jm_id and sd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' order by sd.products_jm_name";
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
                                      array('Nazwa','center'),
                                      array('Typ pola ilość','center'),
                                      array('Domyślny','center'));
                                      
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['products_jm_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['products_jm_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['products_jm_id'].'">';
                  }      

                  $tablica = array(array($info['products_jm_id'],'center'),
                                   array($info['products_jm_name'],'center'));  
                                   
                  if ($info['products_jm_quantity_type'] == '1') {
                      $tablica[] = array('Liczba całkowita','center');
                    } else {
                      $tablica[] = array('Liczba ułamkowa','center');
                  }

                  // domyslny
                  if ($info['products_jm_default'] == '1') { $obraz = '<em class="TipChmurka"><b>Ta jednostka jest domyślna</b><img src="obrazki/aktywny_on.png" alt="Ta jednostka jest domyślna" /></em>'; } else { $obraz = '-'; }              
                  $tablica[] = array($obraz,'center');                                      

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['products_jm_id'];
                  $tekst .= '<a class="TipChmurka" href="slowniki/jednostki_miary_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  if ( $info['products_jm_default'] != '1' ) {
                    $tekst .= '<a class="TipChmurka" href="slowniki/jednostki_miary_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  } else {
                    $tekst .= '<em class="TipChmurka"><b>Nie można usunąć domyślnej jednostki miary</b><img src="obrazki/kasuj_off.png" alt="Nie można usunąć domyślnej jednostki miary" /></em>';
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
            
            <div id="naglowek_cont">Jednostki miary</div>     

            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="slowniki/jednostki_miary_dodaj.php">dodaj nową pozycję</a>
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
            <?php Listing::pokazAjax('slowniki/jednostki_miary.php', $zapytanie, $ile_licznika, $ile_pozycji, 'products_jm_id'); ?>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
