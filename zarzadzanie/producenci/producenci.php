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
        $warunki_szukania = " and (m.manufacturers_name like '%".$szukana_wartosc."%')";
    }

    $zapytanie = "select m.manufacturers_id, m.manufacturers_name, m.manufacturers_image, mi.manufacturers_url from manufacturers m, manufacturers_info mi where m.manufacturers_id = mi.manufacturers_id and mi.languages_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' ".$warunki_szukania;

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ZapytanieDlaPozycji = "SELECT m.manufacturers_id FROM manufacturers m, manufacturers_info mi WHERE m.manufacturers_id = mi.manufacturers_id AND mi.languages_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' ".$warunki_szukania;
    $sql = $db->open_query($ZapytanieDlaPozycji);
    $ile_pozycji = (int)$db->ile_rekordow($sql);

    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }

    $db->close_query($sql);
    
    // jezeli jest sortowanie
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case "sort_a1":
                $sortowanie = 'm.manufacturers_name asc';
                break;
            case "sort_a2":
                $sortowanie = 'm.manufacturers_name desc';
                break;                 
        }            
    } else { $sortowanie = 'm.manufacturers_name asc'; }    
    
    $zapytanie .= " order by ".$sortowanie;    
    
    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr']; 

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID', 'center'),
                                      array('Zdjęcie', 'center'),
                                      array('Nazwa'),
                                      array('Adres WWW'),
                                      array('Ilość produktów', 'center', 'white-space: nowrap'),
                                      array('Aktywnych produktów', 'center', 'white-space: nowrap'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['manufacturers_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['manufacturers_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['manufacturers_id'].'">';
                  }      

                  $tablica = array();
                  
                  $tablica[] = array($info['manufacturers_id'],'center');
                  
                  $tgm = Funkcje::pokazObrazek($info['manufacturers_image'], $info['manufacturers_name'], '40', '40', ' class="Reload"', true);

                  $tablica[] = array($tgm,'center');                  
                  
                  $tablica[] = array($info['manufacturers_name']);
                  
                  if (Funkcje::czyNiePuste($info['manufacturers_url'])) {
                    $tgm = $info['manufacturers_url'];
                  } else {
                    $tgm = '-';
                  }
                  $tablica[] = array($tgm);                   

                  // ile produktow do producenta
                  $kategorie = $db->open_query("select COUNT('products_id') as ile_pozycji from products where manufacturers_id = '".(int)$info['manufacturers_id']."'");
                  $infs = $kategorie->fetch_assoc();
                  if ((int)$infs['ile_pozycji'] > 0) {
                     $ile_produktow = $infs['ile_pozycji'];
                    } else {
                     $ile_produktow = '-';
                  }
                  $db->close_query($kategorie);
                  
                  $tablica[] = array($ile_produktow,'center');  
                  
                  // ile aktywnych produktow do producenta
                  $kategorie = $db->open_query("select COUNT('products_id') as ile_pozycji from products where manufacturers_id = '".(int)$info['manufacturers_id']."' and products_status = '1'");
                  $infs = $kategorie->fetch_assoc();
                  if ((int)$infs['ile_pozycji'] > 0) {
                     $ile_produktow_aktywnych = $infs['ile_pozycji'];
                    } else {
                     $ile_produktow_aktywnych = '-';
                  }                  
                  $db->close_query($kategorie);              

                  $tablica[] = array($ile_produktow_aktywnych,'center');                   
                  
                  unset($kategorie, $ile_produktow, $ile_produktow_aktywnych, $infs);
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right">';
                  
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['manufacturers_id']; 
                  
                  $tekst .= '<a class="TipChmurka" href="producenci/producenci_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="producenci/producenci_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  
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
          $.AutoUzupelnienie( 'szukaj', 'PodpowiedziMale', 'ajax/autouzupelnienie_producenci.php', 50, 200 );
        });
        </script>   

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Producenci</div>

            <div id="wyszukaj">
                <form action="producenci/producenci.php" method="post" id="poForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj producenta:</span>
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
                  echo '<div id="wyszukaj_ikona"><a href="producenci/producenci.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>         
                
                <div style="clear:both"></div>
                
            </div>        
            
            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="producenci/producenci.php?sort=sort_a1">nazwy rosnąco</a>
                <a id="sort_a2" class="sortowanie" href="producenci/producenci.php?sort=sort_a2">nazwy malejąco</a>
                
            </div>             

            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="producenci/producenci_dodaj.php">dodaj nowego producenta</a>
                </div>
                <div>
                    <a class="dodaj" href="producenci/producenci_usun_wszystkich.php">usuń wszystkich producentów</a>
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
            <?php Listing::pokazAjax('producenci/producenci.php', $zapytanie, $ile_licznika, $ile_pozycji, 'manufacturers_id'); ?>
            </script>
            
        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
