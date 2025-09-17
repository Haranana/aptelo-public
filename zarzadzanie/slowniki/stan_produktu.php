<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $zapytanie = "select * from products_condition cp, products_condition_description cpd where cp.products_condition_id = cpd.products_condition_id and cpd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
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
                                      array('Domyślny','center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['products_condition_id']) {
                   $tekst .= '<tr class="pozycja_on" id="sk_'.$info['products_condition_id'].'">';
                 } else {
                   $tekst .= '<tr class="pozycja_off" id="sk_'.$info['products_condition_id'].'">';
                }    

                $tablica = array(array($info['products_condition_id'],'center'),
                                 array($info['products_condition_name'],'center'));  

                // domyslny
                if ($info['products_condition_default'] == '1') { $obraz = '<em class="TipChmurka"><b>Ten stan jest domyślny</b><img src="obrazki/aktywny_on.png" alt="Ten stan jest domyślny" /></em>'; } else { $obraz = '-'; }              
                $tablica[] = array($obraz,'center');                                      

                $tekst .= $listing_danych->pozycje($tablica);
                
                $tekst .= '<td class="rg_right IkonyPionowo">';
                
                $zmienne_do_przekazania = '?id_poz='.$info['products_condition_id'];
                $tekst .= '<a class="TipChmurka" href="slowniki/stan_produktu_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                $tekst .= '<a class="TipChmurka" href="slowniki/stan_produktu_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                
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
            
            <div id="naglowek_cont">Stan produktów</div>     

            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="slowniki/stan_produktu_dodaj.php">dodaj nową pozycję</a>
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
            <?php Listing::pokazAjax('slowniki/stan_produktu.php', $zapytanie, $ile_licznika, $ile_pozycji, 'products_condition_id'); ?>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
