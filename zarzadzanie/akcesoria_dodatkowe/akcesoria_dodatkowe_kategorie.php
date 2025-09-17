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
    if (isset($_GET['szukaj']) && !empty($_GET['szukaj'])) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " and cd.categories_name like '%".$szukana_wartosc."%'";
        unset($szukana_wartosc);
    }

    $zapytanie = "SELECT c.categories_id,   
                         c.categories_status,
                         cd.categories_name, 
                         s.pacc_products_id_master                        
                    FROM categories c                     
               LEFT JOIN categories_description cd ON cd.categories_id = c.categories_id AND cd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'
              RIGHT JOIN products_accesories s ON c.categories_id = s.pacc_products_id_master AND s.pacc_type = 'kategoria' WHERE c.categories_id > 0 " . $warunki_szukania . ' GROUP BY c.categories_id '; 
    
    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich pozycji
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }

    $db->close_query($sql);

    // jezeli jest sortowanie
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case "sort_a1":
                $sortowanie = 'cd.categories_name asc, c.categories_id';
                break;
            case "sort_a2":
                $sortowanie = 'cd.categories_name desc, c.categories_id';
                break;
            case "sort_a3": 
                $sortowanie = 'c.categories_status asc, cd.categories_name, c.categories_id';
                break;
            case "sort_a4":
                $sortowanie = 'c.categories_status desc, cd.categories_name, c.categories_id';
                break;    
            case "sort_a5":
                $sortowanie = 'c.categories_id desc';
                break;
            case "sort_a6":
                $sortowanie = 'c.categories_id asc';
                break;                            
        }            
    } else { $sortowanie = 'cd.categories_name asc, c.categories_id'; }    
    
    // informacje o pozycjach - zakres
    $zapytanie .= " order by ".$sortowanie;    

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
        
            $zapytanie .= " limit ".$_GET['parametr'];    

            $sql = $db->open_query($zapytanie);
            
            $listing_danych = new Listing();
            
            $tablica_naglowek = array();
            $tablica_naglowek[] = array('Akcja','center');
            $tablica_naglowek[] = array('ID','center');
            $tablica_naglowek[] = array('Nazwa kategorii');
            $tablica_naglowek[] = array('Ilość przypisanych produktów','center');  
            $tablica_naglowek[] = array('Status kategorii','center');
            
            echo $listing_danych->naglowek($tablica_naglowek);

            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
                  
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['categories_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['categories_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['categories_id'].'">';
                  } 

                  $tablica = array();

                  $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" value="'.$info['categories_id'].'" id="opcja_'.$info['categories_id'].'" /><label class="OpisForPustyLabel" for="opcja_'.$info['categories_id'].'"></label><input type="hidden" name="id[]" value="'.$info['categories_id'].'" />','center');
                  
                  $tablica[] = array($info['categories_id'],'center');

                  $wyswietl = '';
                  
                  // jakie sa kategorie parent
                  $KatId = explode('_', (string)Kategorie::SciezkaKategoriiId($info['categories_id']));
                  
                  $pelna_sciezka_kategorii = '';
                  if (count($KatId) > 1) {
                      $pelna_sciezka_kategorii = '<span class="SciezkaKategorii">';
                      //
                      for ($s = 0, $c = count($KatId) - 1; $s < $c; $s++) {
                          //
                          if ( isset($TablicaKategorii[(int)$KatId[$s]]) ) {
                               $pelna_sciezka_kategorii .= $TablicaKategorii[(int)$KatId[$s]]['text'] . ' / ';
                          }
                          //
                      }
                      $pelna_sciezka_kategorii .= '</span>';
                  }
                  
                  if ( isset($TablicaKategorii[$info['categories_id']]) ) {
                       $wyswietl .= $pelna_sciezka_kategorii . '<span class="NazwaKatProd">' . $TablicaKategorii[$info['categories_id']]['text'] . '</span>';
                  }
                  
                  unset($pelna_sciezka_kategorii);
                      
                  $tablica[] = array($wyswietl);                  
                  
                  // wyswietlanie ilosci produktow powiazanych
                  $zapytanie_powiazane = "select count(*) as ile_produktow from products_accesories where pacc_products_id_master = '".$info['categories_id']."' and pacc_type = 'kategoria'";
                  $sql_akcesoria_dodatkowe = $db->open_query($zapytanie_powiazane);
                  $infs = $sql_akcesoria_dodatkowe->fetch_assoc();
                  //
                  $tablica[] = array('<b>'.$infs['ile_produktow'].'</b>','center');
                  //
                  $db->close_query($sql_akcesoria_dodatkowe);
                  //
                  unset($infs);

                  // aktywana czy nieaktywna
                  if ($info['categories_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Ta kategoria jest aktywna'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ta kategoria jest nieaktywna'; }               
                  $tablica[] = array('<em class="TipChmurka"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" />','center');                   

                  $tekst .= $listing_danych->pozycje($tablica);
                    
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.$info['categories_id'];      
                                      
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  $tekst .= '<a class="TipChmurka" href="akcesoria_dodatkowe/akcesoria_dodatkowe_kategorie_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>'; 
                  $tekst .= '<a class="TipChmurka" href="akcesoria_dodatkowe/akcesoria_dodatkowe_kategorie_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  $tekst .= '</td></tr>';                  

                  unset($tablica);
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
        
            <div id="naglowek_cont">Akcesoria dodatkowe dla kategorii</div>
            
            <div id="wyszukaj">
                <form action="akcesoria_dodatkowe/akcesoria_dodatkowe_kategorie.php" method="post" id="poForm" class="cmxform"> 
                
                <div id="wyszukaj_text">
                    <span>Wyszukaj kategorię:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="35" />
                </div>  

                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="akcesoria_dodatkowe/akcesoria_dodatkowe_kategorie.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>             
                
                <div style="clear:both"></div>
            </div>        
            
            <form action="akcesoria_dodatkowe/akcesoria_dodatkowe_kategorie_akcja.php" method="post" class="cmxform">
            
            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="akcesoria_dodatkowe/akcesoria_dodatkowe_kategorie.php?sort=sort_a1">nazwy rosnąco</a>
                <a id="sort_a2" class="sortowanie" href="akcesoria_dodatkowe/akcesoria_dodatkowe_kategorie.php?sort=sort_a2">nazwy malejąco</a>       
                <a id="sort_a3" class="sortowanie" href="akcesoria_dodatkowe/akcesoria_dodatkowe_kategorie.php?sort=sort_a3">aktywne</a>
                <a id="sort_a4" class="sortowanie" href="akcesoria_dodatkowe/akcesoria_dodatkowe_kategorie.php?sort=sort_a4">nieaktywne</a>              
                <a id="sort_a5" class="sortowanie" href="akcesoria_dodatkowe/akcesoria_dodatkowe_kategorie.php?sort=sort_a5">ID malejąco</a>
                <a id="sort_a6" class="sortowanie" href="akcesoria_dodatkowe/akcesoria_dodatkowe_kategorie.php?sort=sort_a6">ID rosnąco</a>   
                
            </div>        
            
            <div style="clear:both;"></div>               
            
            <?php 
            if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
                $sciezka = Kategorie::SciezkaKategoriiId((int)$_GET['kategoria_id'], 'categories');
                $cSciezka = explode("_", (string)$sciezka);
               } else {
                $cSciezka = array();
            }
            ?>
            
            <?php
            // przycisk dodania nowej pozycji
            ?>
            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="akcesoria_dodatkowe/akcesoria_dodatkowe_kategorie_dodaj.php">dodaj nową pozycję</a>
                </div>         
            </div>
            
            <div style="clear:both;"></div>            

            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>

            <div id="akcja">
            
                <div class="lf"><img src="obrazki/strzalka.png" alt="" /></div>
                
                <div class="lf" style="padding-right:20px">
                    <span onclick="akcja(1)">zaznacz wszystkie</span>
                    <span onclick="akcja(2)">odznacz wszystkie</span>
                </div>
      
                <div id="akc">
                    Wykonaj akcje: 
                    <select name="akcja_dolna" id="akcja_dolna">
                        <option value="0"></option>
                        <option value="1">usuń z zaznaczonych akcesoria dodatkowe</option>
                    </select>
                </div>
                
                <div style="clear:both;"></div>
                
            </div>                          
            
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <?php if ($ile_pozycji > 0) { ?>
            <div id="zapis"><input type="submit" class="przyciskBut" value="Zapisz zmiany" /></div>
            <?php } ?>                          
                        
            </form>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('akcesoria_dodatkowe/akcesoria_dodatkowe_kategorie.php', $zapytanie, $ile_licznika, $ile_pozycji, 'categories_id'); ?>
            </script>              

        </div>     

        <?php include('stopka.inc.php'); ?>

    <?php 
    } 
    
}?>
