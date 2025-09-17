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
        $warunki_szukania = " and ( CONCAT(cg.customers_groups_name) LIKE '%".$szukana_wartosc."%' 
                                 or CONCAT(cu.customers_firstname,' ', cu.customers_lastname, ' ' , cu.customers_email_address) LIKE '%".$szukana_wartosc."%' 
                                 or a.entry_company LIKE '%".$szukana_wartosc."%' 
                                 or pd.products_name LIKE '%".$szukana_wartosc."%')";
    }
    
    if ( isset($_GET['szukaj_grupa']) && $_GET['szukaj_grupa'] != '0' ) {
        $szukana_wartosc = (int)$_GET['szukaj_grupa'];
        $warunki_szukania .= " and cp.cp_groups_id = '".$szukana_wartosc."'";
    }    
    
    // jezeli jest wybrany producent
    if (isset($_GET['producent']) && (int)$_GET['producent'] > 0) {
        $id_producenta = (int)$_GET['producent'];
        $warunki_szukania .= " and cp.cp_products_id = '".(int)$id_producenta."'";
        unset($id_producenta);
    }    
    
    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }    

    $zapytanie = "select distinct
                         cp.cp_id,
                         cp.cp_groups_id,
                         cp.cp_customers_id,
                         cp.cp_products_id,
                         cp.cp_price,
                         cp.cp_price_tax,
                         cp.cp_tax,
                         cg.customers_groups_id,
                         cg.customers_groups_name,
                         p.products_id,
                         p.products_price_tax, 
                         p.products_old_price, 
                         p.specials_status,
                         p.specials_date,
                         p.specials_date_end, 
                         p.products_points_only,
                         p.products_points_value,
                         p.products_points_value_money,  
                         p.products_currencies_id,
                         pd.products_name,
                         cu.customers_id,
                         cu.customers_firstname,
                         cu.customers_lastname,
                         cu.customers_email_address,
                         cu.customers_default_address_id,
                         a.entry_company
                    FROM customers_price cp
                         LEFT JOIN customers_groups cg ON cp.cp_groups_id = cg.customers_groups_id
                         LEFT JOIN products_description pd ON cp.cp_products_id = pd.products_id AND pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'
                         LEFT JOIN products p ON cp.cp_products_id = p.products_id
                         LEFT JOIN customers cu ON cp.cp_customers_id = cu.customers_id
                         LEFT JOIN address_book a on cu.customers_id = a.customers_id and cu.customers_default_address_id = a.address_book_id " . $warunki_szukania;
                        
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
                $sortowanie = 'pd.products_name desc';
                break;
            case "sort_a2":
                $sortowanie = 'pd.products_name asc';
                break;              
            case "sort_a3":
                $sortowanie = 'cg.customers_groups_name asc';
                break;
            case "sort_a4":
                $sortowanie = 'cg.customers_groups_name desc';
                break; 
            case "sort_a5":
                $sortowanie = 'cu.customers_lastname asc';
                break;
            case "sort_a6":
                $sortowanie = 'cu.customers_lastname desc';
                break;
        }            
    } else { $sortowanie = 'pd.products_name asc'; }
    
    $zapytanie .= " ORDER BY ".$sortowanie;    

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr']; 
            
            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID','center'),
                                      array('Id produktu','center'),
                                      array('Nazwa produktu','center'),
                                      array('Cena standardowa','center'),
                                      array('Cena indywidualna','center'),
                                      array('Grupa klientów','center'),
                                      array('Klient','center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['cp_id']) {
                     $tekst .= '<tr class="pozycja_on">';
                   } else {
                     $tekst .= '<tr class="pozycja_off">';
                  }        
                  
                  $tablica = array();
                  
                  $tablica[] = array($info['cp_id'],'center');
                  
                  $tablica[] = array($info['cp_products_id'],'center');
                  
                  $tablica[] = array('<span class="NazwaKatProd">' .$info['products_name'].'</span>','center');
                  
                  $status_promocja = '';
                  if ( ((FunkcjeWlasnePHP::my_strtotime($info['specials_date']) > time() && $info['specials_date'] != '0000-00-00 00:00:00') || (FunkcjeWlasnePHP::my_strtotime($info['specials_date_end']) < time() && $info['specials_date_end'] != '0000-00-00 00:00:00') ) && $info['specials_status'] == '1' ) {                             
                      $status_promocja = '<div class="wylaczonaPromocja TipChmurka"><b>Produkt nie jest wyświetlany jako promocja ze względu na datę rozpoczęcia lub zakończenia promocji</b></div>';
                  }                                     
                  
                  $tablica[] = array( $status_promocja . (((float)$info['products_old_price'] == 0) ? '' : '<div class="cena_promocyjna">' . $waluty->FormatujCene($info['products_old_price'], false, $info['products_currencies_id']) . '</div>') . 
                                     '<div class="cena">'.$waluty->FormatujCene($info['products_price_tax'], false, $info['products_currencies_id']).'</div>'.
                                     (($info['products_points_only'] == 1) ? '<div class="TylkoPkt">' . $info['products_points_value'] . ' pkt + ' . $waluty->FormatujCene($info['products_points_value_money'],false) . '</div>' : ''),'center', 'white-space: nowrap'); 
                                     
                  unset($status_promocja);                  
                  
                  $tablica[] = array( '<div class="cena" style="font-size:110%;font-weight:bold">'.$waluty->FormatujCene($info['cp_price_tax'], false, $info['products_currencies_id']).'</div>','center', 'white-space: nowrap'); 
                                                       
                  $tablica[] = array(((!empty($info['customers_groups_name'])) ? $info['customers_groups_name'] : '-'),'center');

                  $wyswietlana_nazwa = '';
                  if (!empty($info['customers_lastname'])) {
                     $wyswietlana_nazwa = '';
                     if ( $info['entry_company'] != '' ) {
                        $wyswietlana_nazwa = '<span class="Firma"">'.$info['entry_company'] . '</span><br />';
                     }                  
                     $tablica[] = array( $wyswietlana_nazwa . $info['customers_firstname'] . ' ' . $info['customers_lastname'] . '<br /><span class="Maili">' . $info['customers_email_address'] . '</span>','center');
                     unset($wyswietlana_nazwa);
                    } else {
                     $tablica[] = array('-','center');
                  }

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['cp_id']; 
                  $tekst .= '<a class="TipChmurka" href="klienci/indywidualne_ceny_produktow_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="klienci/indywidualne_ceny_produktow_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="produkty/produkty_edytuj.php?id_poz='.$info['products_id'].'"><b>Przejdź do edycji produktu</b><img src="obrazki/domek.png" alt="Przejdź do edycji produktu" /></a>';
                  
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
            
            <div id="naglowek_cont">Indywidualne ceny produktów dla klientów</div>  

            <div id="wyszukaj">
                <form action="klienci/indywidualne_ceny_produktow.php" method="post" id="klienciForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="40" />
                </div> 

                <div class="wyszukaj_select">
                    <span>Grupa:</span>
                    <?php
                    $tablica = Klienci::ListaGrupKlientow();
                    echo Funkcje::RozwijaneMenu('szukaj_grupa', $tablica, ((isset($_GET['szukaj_grupa'])) ? $filtr->process($_GET['szukaj_grupa']) : '')); ?>
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
                  echo '<div id="wyszukaj_ikona"><a href="klienci/indywidualne_ceny_produktow.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                
                
                <div style="clear:both"></div>
            </div>        
            
            <div id="sortowanie">
            
                <span>Sortowanie: </span>

                <a id="sort_a1" class="sortowanie" href="klienci/indywidualne_ceny_produktow.php?sort=sort_a1">nazwa produktu malejąco</a>
                <a id="sort_a2" class="sortowanie" href="klienci/indywidualne_ceny_produktow.php?sort=sort_a2">nazwa produktu rosnąco</a>                
                <a id="sort_a3" class="sortowanie" href="klienci/indywidualne_ceny_produktow.php?sort=sort_a3">grupa klientów malejąco</a>
                <a id="sort_a4" class="sortowanie" href="klienci/indywidualne_ceny_produktow.php?sort=sort_a4">grupa klientów rosnąco</a>
                <a id="sort_a5" class="sortowanie" href="klienci/indywidualne_ceny_produktow.php?sort=sort_a5">nazwiska klientów malejąco</a>
                <a id="sort_a6" class="sortowanie" href="klienci/indywidualne_ceny_produktow.php?sort=sort_a6">nazwiska klientów rosnąco</a>             
                
            </div>               

            <div style="clear:both;"></div> 
            
            <?php
            $zapytanie_produktow = "select * from products";
            $sqlm = $db->open_query($zapytanie_produktow);
            if ((int)$db->ile_rekordow($sqlm) > 0) {         
            ?>
            
            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="klienci/indywidualne_ceny_produktow_dodaj.php">dodaj nową pozycję</a>
                </div>  
                <div class="rg">
                    <a class="Export" href="klienci/indywidualne_ceny_produktow_export.php">eksportuj do pliku CSV</a>               
                    <a class="Import" href="klienci/indywidualne_ceny_produktow_import.php">importuj z pliku CSV</a>
                </div>               
                            
            </div>

            <div style="clear:both;"></div>

            <?php
            }
            $db->close_query($sqlm);
            unset($zapytanie_produktow);
            ?>
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            <?php Listing::pokazAjax('klienci/indywidualne_ceny_produktow.php', $zapytanie, $ile_licznika, $ile_pozycji, 'cp_id'); ?>
            </script>              

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
