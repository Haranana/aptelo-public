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
        $warunki_szukania .= " and pd.products_name like '%".$szukana_wartosc."%'";
        unset($szukana_wartosc);
    }
    
    $nr_id_tmp = array();
    
    // jezeli jest nr kat lub id
    if (isset($_GET['nrkat']) && !empty($_GET['nrkat'])) {
        $szukana_wartosc = $filtr->process($_GET['nrkat']);
        $warunki_szukania .= " and (p.products_model like '%".$szukana_wartosc."%' or p.products_man_code like '%".$szukana_wartosc."%' or p.products_id = ".(int)$szukana_wartosc.")";
        //
        // szuka tez czy nie ma nr katalogowego w cechach
        $sql_cechy = $db->open_query("SELECT products_id FROM products_stock WHERE (products_stock_model like '%".$szukana_wartosc."%')");
        while ($info_cechy = $sql_cechy->fetch_assoc()) {
            $nr_id_tmp[ $info_cechy['products_id'] ] = $info_cechy['products_id'];
        }
        $db->close_query($sql_cechy);
        unset($szukana_wartosc);
        // 
    }
    
    // jezeli jest wybrana kategoria
    if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
        $id_kategorii = (int)$_GET['kategoria_id'];
        $warunki_szukania .= " and pc.categories_id = '".(int)$id_kategorii."'";
        unset($id_kategorii);
    }
    
    // jezeli jest wybrany producent
    if (isset($_GET['producent']) && (int)$_GET['producent'] > 0) {
        $id_producenta = (int)$_GET['producent'];
        $warunki_szukania .= " and p.manufacturers_id = '".(int)$id_producenta."'";
        unset($id_producenta);
    }    
    
    if ( count($nr_id_tmp) > 0 ) {
         $warunki_szukania .= " or p.products_id IN (" . implode(',', (array)$nr_id_tmp) . ")";
    }
    
    unset($nr_id_tmp);    
    
    $zapytanie = "SELECT p.products_id, 
                         p.products_image, 
                         p.products_model, 
                         p.products_man_code,
                         p.products_status,
                         p.specials_status,
                         p.specials_date,
                         p.specials_date_end,
                         p.manufacturers_id,                        
                         pd.products_id, 
                         pd.language_id, 
                         pd.products_name, 
                         s.pacc_products_id_master,
                         m.manufacturers_name                         
                    FROM products p
               ".((isset($_GET['kategoria_id'])) ? 'LEFT JOIN products_to_categories pc ON pc.products_id = p.products_id' : '')."                        
               LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'
               LEFT JOIN manufacturers m ON m.manufacturers_id = p.manufacturers_id
              RIGHT JOIN products_accesories s ON p.products_id = s.pacc_products_id_master AND s.pacc_type = 'produkt' WHERE p.products_id > 0 " . $warunki_szukania . ' GROUP BY p.products_id '; 
    
    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }

    $db->close_query($sql);

    $sortowanie = $GLOBALS['DomyslneSortowanie'];
    //
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case "sort_a17":
                $sortowanie = 'pd.products_name asc, p.products_id';
                break;
            case "sort_a2":
                $sortowanie = 'pd.products_name desc, p.products_id';
                break;
            case "sort_a7":
                $sortowanie = 'p.products_model asc, p.products_id';
                break;
            case "sort_a8":
                $sortowanie = 'p.products_model desc, p.products_id';
                break;  
            case "sort_a9":
                $sortowanie = 'p.products_price_tax asc, p.products_id';
                break;
            case "sort_a10":
                $sortowanie = 'p.products_price_tax desc, p.products_id';
                break;                          
            case "sort_a3":
                $sortowanie = 'p.products_status desc, pd.products_name, p.products_id';
                break;  
            case "sort_a4":
                $sortowanie = 'p.products_status asc, pd.products_name, p.products_id';
                break;
            case "sort_a13":
                $sortowanie = 'p.products_id desc';
                break;
            case "sort_a14":
                $sortowanie = 'p.products_id asc';
                break;    
            case "sort_a15":
                $sortowanie = 'p.sort_order desc, p.products_id';
                break;
            case "sort_a16":
                $sortowanie = 'p.sort_order asc, p.products_id';
                break;                        
        }            
    }  
    
    $zapytanie .= (($sortowanie != '') ? " order by ".$sortowanie : '');   

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
            $tablica_naglowek[] = array('Zdjęcie','center');  
            $tablica_naglowek[] = array('Nazwa produktu');
            $tablica_naglowek[] = array('Ilość przypisanych produktów','center');  
            $tablica_naglowek[] = array('Status produktu','center');
            
            echo $listing_danych->naglowek($tablica_naglowek);

            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
                  
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['products_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['products_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['products_id'].'">';
                  } 

                  $tablica = array();

                  $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" value="'.$info['products_id'].'" id="opcja_'.$info['products_id'].'" /><label class="OpisForPustyLabel" for="opcja_'.$info['products_id'].'"></label><input type="hidden" name="id[]" value="'.$info['products_id'].'" />','center');
                  
                  $tablica[] = array($info['products_id'],'center');
                  
                  // czyszczenie z &nbsp; i zbyt dlugiej nazwy
                  $info['products_name'] = Funkcje::PodzielNazwe($info['products_name']);
                  $info['products_model'] = Funkcje::PodzielNazwe($info['products_model']);

                  if ( !empty($info['products_image']) ) {
                       //
                       $tgm = '<div id="zoom'.rand(1,99999).'" class="imgzoom" onmouseover="ZoomIn(this,event)" onmouseout="ZoomOut(this)">';
                       $tgm .= '<div class="zoom">' . Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '250', '250') . '</div>';
                       $tgm .= Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '40', '40', ' class="Reload"', true);
                       $tgm .= '</div>';
                       //
                     } else { 
                       //
                       $tgm = '-';
                       //
                  }

                  $tablica[] = array($tgm,'center');    

                  // dodatkowa zmienna do wylaczania mozliwosci zmiany statusu produktu jezeli kategoria
                  // do ktorej nalezy jest wylaczona
                  $wylacz_status = true;
                  
                  // nazwa produktu i kategorie do jakich jest przypisany
                  $do_jakich_kategorii_przypisany = '<span class="MaleNrKatalogowy">Kategoria: ';
                  $kategorie = $db->open_query("select distinct categories_id from products_to_categories where products_id = '".(int)$info['products_id']."'");
                  //
                  if ( (int)$db->ile_rekordow($kategorie) > 0 ) {
                      while ($id_kategorii = $kategorie->fetch_assoc()) {
                          // okreslenie nazwy kategorii
                          if ((int)$id_kategorii['categories_id'] == '0') {
                              $do_jakich_kategorii_przypisany .= 'Bez kategorii, ';
                              $wylacz_status = false;
                            } else {
                              //
                              if ( isset($TablicaKategorii[(int)$id_kategorii['categories_id']]) ) {
                                  //
                                  $do_jakich_kategorii_przypisany .= '<span style="color:#ff0000">'.$TablicaKategorii[(int)$id_kategorii['categories_id']]['text'].'</span>, ';
                                  //
                                  if ($TablicaKategorii[(int)$id_kategorii['categories_id']]['status'] == '1') {
                                     $wylacz_status = false;
                                  }
                                  //
                              }
                              //
                          }
                      }
                    } else {
                      $do_jakich_kategorii_przypisany .= 'Bez kategorii, ';
                      $wylacz_status = false;
                  }
                  $do_jakich_kategorii_przypisany = substr((string)$do_jakich_kategorii_przypisany,0,-2);
                  $do_jakich_kategorii_przypisany .= '</span>';
                  
                  $db->close_query($kategorie);
                  unset($kategorie);
                  
                  $nr_kat = '';
                  if (trim((string)$info['products_model']) != '') {
                      $nr_kat = '<span class="MaleNrKatalogowy">Nr kat: <b>'.$info['products_model'].'</b></span>';
                  }
                  
                  $kod_producenta = '';
                  if (trim((string)$info['products_man_code']) != '') {
                      $kod_producenta = '<span class="MaleNrKatalogowy">Kod prod: <b>'.$info['products_man_code'].'</b></span>';
                  }

                  // pobieranie danych o producencie
                  $prd = '';
                  if (trim((string)$info['manufacturers_name']) != '') {                     
                      //
                      $prd = '<span class="MaleProducent">Producent: <b>'.$info['manufacturers_name'].'</b></span>';
                      //
                  }                         
                  
                  $tgm = '<b>'.$info['products_name'].'</b>' . $do_jakich_kategorii_przypisany . $nr_kat . $kod_producenta . $prd;
                  $tablica[] = array($tgm);
                  
                  unset($do_jakich_kategorii_przypisany, $nr_kat, $kod_producenta, $prd);
                  
                  // wyswietlanie ilosci produktow powiazanych
                  $zapytanie_powiazane = "select count(*) as ile_produktow from products_accesories where pacc_products_id_master = '".$info['products_id']."' and pacc_type = 'produkt'";
                  $sql_akcesoria_dodatkowe = $db->open_query($zapytanie_powiazane);
                  $infs = $sql_akcesoria_dodatkowe->fetch_assoc();
                  //
                  $tablica[] = array('<b>'.$infs['ile_produktow'].'</b>','center');
                  //
                  $db->close_query($sql_akcesoria_dodatkowe);
                  //
                  unset($infs);

                  // jezeli promocja ma date i data poczatkowa jest wieksza od dzisiejszej lub koncowa wczesniejsza od dzisiejszej to wylacza checkboxa zmiany statusu - produkt musi byc wylaczony
                  $Wylacz = '';
                  $TekstWylacz = '';
                  if ( ((FunkcjeWlasnePHP::my_strtotime($info['specials_date']) > time() && $info['specials_date'] != '0000-00-00 00:00:00') || (FunkcjeWlasnePHP::my_strtotime($info['specials_date_end']) < time()  && $info['specials_date_end'] != '0000-00-00 00:00:00') ) && $info['products_status'] == '0') {
                     $Wylacz = ' disabled="disabled"';
                     $TekstWylacz = ' ';
                  }
                  
                  $tablica[] = array((($wylacz_status == true) ? '<div class="wylKat TipChmurka"><b>Kategoria do której należy produkt jest wyłączona</b>' : '') . '<input type="checkbox" style="border:0px" name="status_'.$info['products_id'].'" value="1" '.(($info['products_status'] == '1') ? 'checked="checked"' : '').' ' . $TekstWylacz . $Wylacz .  ' id="status_'.$info['products_id'].'" /><label class="OpisForPustyLabel" for="status_'.$info['products_id'].'"></label>' . (($wylacz_status == true) ? '</div>' : ''),'center');                                  

                  $tekst .= $listing_danych->pozycje($tablica);
                    
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.$info['products_id'];      
                                      
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  $tekst .= '<a class="TipChmurka" href="akcesoria_dodatkowe/akcesoria_dodatkowe_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>'; 
                  $tekst .= '<a class="TipChmurka" href="akcesoria_dodatkowe/akcesoria_dodatkowe_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="produkty/produkty_edytuj.php?id_poz='.$info['products_id'].'"><b>Przejdź do edycji produktu</b><img src="obrazki/domek.png" alt="Przejdź do edycji produktu" /></a>';
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

        <script>
        $(document).ready(function() {
          $.AutoUzupelnienie( 'szukaj', 'Podpowiedzi', 'ajax/autouzupelnienie_produkty.php?typ=akcesoria', 50, 350 );
        });
        </script> 

        <div id="caly_listing">
        
            <div id="ajax"></div>
        
            <div id="naglowek_cont">Akcesoria dodatkowe dla pojedynczych produktów</div>
            
            <div id="wyszukaj">
                <form action="akcesoria_dodatkowe/akcesoria_dodatkowe.php" method="post" id="poForm" class="cmxform"> 
                
                <div id="wyszukaj_text">
                    <span>Wyszukaj produkt:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="35" />
                </div>  
                
                <div class="wyszukaj_select">
                    <span>ID lub nr kat:</span>
                    <input type="text" name="nrkat" value="<?php echo ((isset($_GET['nrkat'])) ? $filtr->process($_GET['nrkat']) : ''); ?>" size="25" />
                </div>                 
                
                <div class="wyszukaj_select">
                    <span>Producent:</span>                                     
                    <?php echo Funkcje::RozwijaneMenu('producent', Funkcje::TablicaProducenci('-- brak --'), ((isset($_GET['producent'])) ? $filtr->process($_GET['producent']) : '')); ?>
                </div>
 
                <?php 
                // tworzy ukryte pola hidden do wyszukiwania - filtra 
                if (isset($_GET['kategoria_id'])) { 
                    echo '<div><input type="hidden" name="kategoria_id" value="'.(int)$_GET['kategoria_id'].'" /></div>';
                }   
                if (isset($_GET['sort'])) { 
                    echo '<div><input type="hidden" name="sort" value="'.$filtr->process($_GET['sort']).'" /></div>';
                }                
                ?>
                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="akcesoria_dodatkowe/akcesoria_dodatkowe.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>             
                
                <div style="clear:both"></div>
            </div>        
            
            <form action="akcesoria_dodatkowe/akcesoria_dodatkowe_akcja.php" method="post" class="cmxform">
            
            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="akcesoria_dodatkowe/akcesoria_dodatkowe.php?sort=sort_a1">brak</a>
                <a id="sort_a17" class="sortowanie" href="akcesoria_dodatkowe/akcesoria_dodatkowe.php?sort=sort_a17">nazwy rosnąco</a>
                <a id="sort_a2" class="sortowanie" href="akcesoria_dodatkowe/akcesoria_dodatkowe.php?sort=sort_a2">nazwy malejąco</a>
                <a id="sort_a7" class="sortowanie" href="akcesoria_dodatkowe/akcesoria_dodatkowe.php?sort=sort_a7">nr katalogowy rosnąco</a>
                <a id="sort_a8" class="sortowanie" href="akcesoria_dodatkowe/akcesoria_dodatkowe.php?sort=sort_a8">nr katalogowy malejąco</a> 
                <a id="sort_a9" class="sortowanie" href="akcesoria_dodatkowe/akcesoria_dodatkowe.php?sort=sort_a9">cena rosnąco</a>
                <a id="sort_a10" class="sortowanie" href="akcesoria_dodatkowe/akcesoria_dodatkowe.php?sort=sort_a10">cena malejąco</a>             
                <a id="sort_a3" class="sortowanie" href="akcesoria_dodatkowe/akcesoria_dodatkowe.php?sort=sort_a3">aktywne</a>
                <a id="sort_a4" class="sortowanie" href="akcesoria_dodatkowe/akcesoria_dodatkowe.php?sort=sort_a4">nieaktywne</a> 
                <a id="sort_a13" class="sortowanie" href="akcesoria_dodatkowe/akcesoria_dodatkowe.php?sort=sort_a13">ID malejąco</a>
                <a id="sort_a14" class="sortowanie" href="akcesoria_dodatkowe/akcesoria_dodatkowe.php?sort=sort_a14">ID rosnąco</a>
                <a id="sort_a15" class="sortowanie" href="akcesoria_dodatkowe/akcesoria_dodatkowe.php?sort=sort_a15">sortowanie malejąco</a>
                <a id="sort_a16" class="sortowanie" href="akcesoria_dodatkowe/akcesoria_dodatkowe.php?sort=sort_a16">sortowanie rosnąco</a>
                
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
                    <a class="dodaj" href="akcesoria_dodatkowe/akcesoria_dodatkowe_dodaj.php">dodaj nową pozycję</a>
                </div>         
            </div>
            
            <div style="clear:both;"></div>            

            <div class="GlownyListing">

                <div class="GlownyListingKategorie">
                
                    <div class="OknoKategoriiKontener">
                    
                        <div class="OknoNaglowek"><span class="RozwinKategorie">Kategorie</span></div>
                        <?php
                        echo '<div class="OknoKategorii"><table class="pkc">';
                        $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                        for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                            $podkategorie = false;
                            if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                            // sprawdza czy nie jest wybrana
                            $style = '';
                            if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
                                if ((int)$_GET['kategoria_id'] == $tablica_kat[$w]['id']) {
                                    $style = ' style="color:#ff0000"';
                                }
                            }
                            //
                            echo '<tr>
                                    <td class="lfp"><a href="akcesoria_dodatkowe/akcesoria_dodatkowe.php?kategoria_id='.$tablica_kat[$w]['id'].'" '.$style.'>'.$tablica_kat[$w]['text'].'</a></td>
                                    <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'\',\'\',\'akcesoria_dodatkowe\')" />' : '').'</td>
                                  </tr>
                                  '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                        }
                        if ( count($tablica_kat) == 0 ) {
                             echo '<tr><td colspan="9" style="padding:10px">Brak wyników do wyświetlania</td></tr>';
                        }                            
                        echo '</table></div>';
                        unset($tablica_kat,$podkategorie,$style);
                        ?>        

                        <?php 
                        if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
                            $sciezka = Kategorie::SciezkaKategoriiId((int)$_GET['kategoria_id'], 'categories');
                            $cSciezka = explode("_", (string)$sciezka);                    
                            if (count($cSciezka) > 1) {
                                //
                                $ostatnie = strRpos($sciezka,'_');
                                $analiza_sciezki = str_replace("_", ",", substr((string)$sciezka,0,(int)$ostatnie));
                                ?>
                                
                                <script>           
                                podkat('<?php echo $analiza_sciezki; ?>', '<?php echo $cSciezka[count($cSciezka)-1]; ?>','','','akcesoria_dodatkowe');
                                </script>
                                
                            <?php
                            unset($sciezka,$cSciezka);
                            }
                        } ?>
                        
                    </div>
                    
                </div>
                
                <div style="GlownyListingProdukty">
                
                    <div id="wynik_zapytania" class="WynikZapytania"></div>
                    <div id="aktualna_pozycja">1</div>

                    <div id="akcja" class="AkcjaOdstep">
                    
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
                    
                    <div id="dolny_pasek_stron" class="AkcjaOdstep"></div>
                    <div id="pokaz_ile_pozycji" class="AkcjaOdstep"></div>
                    <div id="ile_rekordow" class="AkcjaOdstep"><?php echo $ile_pozycji; ?></div>
                    
                    <?php if ($ile_pozycji > 0) { ?>
                    <div id="zapis"><input type="submit" class="przyciskBut" value="Zapisz zmiany" /></div>
                    <?php } ?>                          
                        
                </div>

            </div>
            
            </form>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('akcesoria_dodatkowe/akcesoria_dodatkowe.php', $zapytanie, $ile_licznika, $ile_pozycji, 'products_id', ILOSC_WYNIKOW_NA_STRONIE, ADMIN_DOMYSLNE_SORTOWANIE); ?>
            </script>              

        </div>     

        <?php include('stopka.inc.php'); ?>

    <?php 
    } 
    
}?>
