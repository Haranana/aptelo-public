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
        $warunki_szukania = " and (pd.gallery_name like '%".$szukana_wartosc."%')";
    }
    
    // jezeli jest wybrana grupa klienta
    if (isset($_GET['klienci']) && (int)$_GET['klienci'] > 0) {
        $id_klienta = (int)$_GET['klienci'];
        $warunki_szukania .= " and find_in_set(" . (int)$id_klienta . ", p.gallery_customers_group_id) ";        
        unset($id_klienta);
    }     

    $zapytanie = "select distinct * from gallery p, gallery_description pd where p.id_gallery = pd.id_gallery and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' ".$warunki_szukania;
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
                $sortowanie = 'pd.gallery_name asc';
                break;
            case "sort_a2":
                $sortowanie = 'pd.gallery_name desc';
                break;                   
        }            
    } else { $sortowanie = 'pd.gallery_name asc'; }    

    $zapytanie .= " order by ".$sortowanie;
    
    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr'];

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID', 'center'),
                                      array('Nazwa galerii'),
                                      array('Grupa klientów', 'center'),
                                      array('Status', 'center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['id_gallery']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['id_gallery'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['id_gallery'].'">';
                  }        

                  $tablica = array();
                  
                  $tablica[] = array($info['id_gallery'],'center');
                  
                  $tablica[] = array($info['gallery_name']);
                  
                  $tgm = '';
                  $tabGrup = explode(',', (string)$info['gallery_customers_group_id']);
                  if ( count($tabGrup) > 0 && $info['gallery_customers_group_id'] != 0 ) {
                       foreach ( $tabGrup as $idGrupy ) {
                          $tgm .= '<span class="GrupaKlientow">' . Klienci::pokazNazweGrupyKlientow($idGrupy) . '</span><br />';
                       }
                  }      
                  $tablica[] = array( (($tgm != '') ? $tgm : '-'),'center');
                  unset($tabGrup, $tgm);                    
                  
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['id_gallery'];            
                  
                  // aktywany czy nieaktywny
                  if ($info['gallery_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Ta galeria jest aktywna'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ta galeria jest nieaktywna'; }              
                  $tablica[] = array('<a class="TipChmurka" href="galerie/galerie_status.php'.$zmienne_do_przekazania.'"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></a>','center');                  
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';

                  $tekst .= '<a class="TipChmurka" href="galerie/galerie_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="galerie/galerie_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  
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
            
            <div id="naglowek_cont">Galerie</div>

            <div id="wyszukaj">
                <form action="galerie/galerie.php" method="post" id="pogallery" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj galerię:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="60" />
                </div> 

                <div class="wyszukaj_select">
                    <span>Grupa klientów:</span>
                    <?php                         
                    echo Funkcje::RozwijaneMenu('klienci', Klienci::ListaGrupKlientow(true), ((isset($_GET['klienci'])) ? $filtr->process($_GET['klienci']) : ''), ' style="width:130px"'); 
                    unset($tablica);
                    ?>
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
                  echo '<div id="wyszukaj_ikona"><a href="galerie/galerie.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                

                <div style="clear:both"></div>
            </div>        
            
            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="galerie/galerie.php?sort=sort_a1">nazwy rosnąco</a>
                <a id="sort_a2" class="sortowanie" href="galerie/galerie.php?sort=sort_a2">nazwy malejąco</a>
            
            </div>             

            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="galerie/galerie_dodaj.php">dodaj nową galerię</a>
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
            <?php Listing::pokazAjax('galerie/galerie.php', $zapytanie, $ile_licznika, $ile_pozycji, 'id_gallery'); ?>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
