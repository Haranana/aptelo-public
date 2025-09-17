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
        $warunki_szukania = " and (pd.form_name like '%".$szukana_wartosc."%')";
    }
    
    // jezeli jest wybrana grupa klienta
    if (isset($_GET['klienci']) && (int)$_GET['klienci'] > 0) {
        $id_klienta = (int)$_GET['klienci'];
        $warunki_szukania .= " and find_in_set(" . (int)$id_klienta . ", p.form_customers_group_id) ";        
        unset($id_klienta);
    }    

    $zapytanie = "select distinct p.id_form, p.form_customers_group_id, p.form_status, pd.form_name from form p, form_description pd where p.id_form = pd.id_form and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' ".$warunki_szukania;
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
                $sortowanie = 'pd.form_name asc';
                break;
            case "sort_a2":
                $sortowanie = 'pd.form_name desc';
                break;                       
        }            
    } else { $sortowanie = 'pd.form_name asc'; }    
    
    $zapytanie .= " order by ".$sortowanie;    
    
    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr'];    

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID', 'center'),
                                      array('Nazwa formularza'),
                                      array('Grupa klientów', 'center'),
                                      array('Status', 'center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['id_form']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['id_form'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['id_form'].'">';
                  }     

                  $tablica = array();
                  
                  $tablica[] = array($info['id_form'],'center');
                  
                  $tablica[] = array($info['form_name']);

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['id_form'];                 
                  
                  $tgm = '';
                  $tabGrup = explode(',', (string)$info['form_customers_group_id']);
                  if ( count($tabGrup) > 0 && $info['form_customers_group_id'] != 0 ) {
                       foreach ( $tabGrup as $idGrupy ) {
                          $tgm .= '<span class="GrupaKlientow">' . Klienci::pokazNazweGrupyKlientow($idGrupy) . '</span><br />';
                       }
                  }      
                  $tablica[] = array( (($tgm != '') ? $tgm : '-'),'center');
                  unset($tabGrup, $tgm);                   
                  
                  // aktywany czy nieaktywny
                  if ($info['form_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Ten formularz jest aktywny'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ten formularz jest nieaktywny'; }              
                  $tablica[] = array('<a class="TipChmurka" href="formularze/formularze_status.php'.$zmienne_do_przekazania.'"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></a>','center');                  
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $tekst .= '<a class="TipChmurka" href="formularze/formularze_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  
                  if ( $info['id_form'] > 4 ) {
                      $tekst .= '<a class="TipChmurka" href="formularze/formularze_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
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
            
            <div id="naglowek_cont">Formularze</div>

            <div id="wyszukaj">
                <form action="formularze/formularze.php" method="post" id="poForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj formularz:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="60" />
                </div>

                <div class="wyszukaj_select">
                    <span>Grupa klientów:</span>
                    <?php                         
                    echo Funkcje::RozwijaneMenu('klienci', Klienci::ListaGrupKlientow(true), ((isset($_GET['klienci'])) ? $filtr->process($_GET['klienci']) : '')); 
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
                  echo '<div id="wyszukaj_ikona"><a href="formularze/formularze.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
            </div>        
            
            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="formularze/formularze.php?sort=sort_a1">nazwy rosnąco</a>
                <a id="sort_a2" class="sortowanie" href="formularze/formularze.php?sort=sort_a2">nazwy malejąco</a>   
            
            </div>             

            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="formularze/formularze_dodaj.php">dodaj nowy formularz</a>
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
            <?php Listing::pokazAjax('formularze/formularze.php', $zapytanie, $ile_licznika, $ile_pozycji, 'id_form'); ?>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
