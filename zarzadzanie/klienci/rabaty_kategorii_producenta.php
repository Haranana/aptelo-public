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
        $warunki_szukania = " and ( c.discount_name LIKE '%".$szukana_wartosc."%' 
                                 or CONCAT(cu.customers_firstname,' ', cu.customers_lastname, ' ' , cu.customers_email_address) LIKE '%".$szukana_wartosc."%' 
                                 or a.entry_company LIKE '%".$szukana_wartosc."%')";
    }
    
    if ( isset($_GET['szukaj_grupa']) && $_GET['szukaj_grupa'] != '0' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_grupa']);
        $warunki_szukania .= " and c.discount_groups_id LIKE '%,".$szukana_wartosc.",%'";
    }    
    
    // jezeli jest wybrany producent
    if (isset($_GET['producent']) && (int)$_GET['producent'] > 0) {
        $id_producenta = (int)$_GET['producent'];
        $warunki_szukania .= " and c.discount_categories_id = '".$id_producenta."'";
        unset($id_producenta);
    }    
    
    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }    

    $zapytanie = "select distinct
                        c.discount_id,
                        c.discount_name,
                        c.discount_groups_id,
                        c.discount_customers_id,
                        c.discount_categories_id,
                        c.discount_manufacturers_id,
                        c.discount_discount,
                        cu.customers_id,
                        cu.customers_firstname,
                        cu.customers_lastname,
                        cu.customers_email_address,
                        cu.customers_default_address_id,
                        a.entry_company
                    FROM discount_categories_manufacturers c
                        LEFT JOIN customers cu ON c.discount_customers_id = cu.customers_id
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
                $sortowanie = 'c.discount_name asc';
                break;
            case "sort_a2":
                $sortowanie = 'c.discount_name desc';
                break;                 
            case "sort_a3":
                $sortowanie = 'c.discount_discount desc';
                break;
            case "sort_a4":
                $sortowanie = 'c.discount_discount asc';
                break;
            case "sort_a7":
                $sortowanie = 'cu.customers_lastname asc';
                break;
            case "sort_a8":
                $sortowanie = 'cu.customers_lastname desc';
                break;                        
        }            
    } else { $sortowanie = 'c.discount_name asc'; }
    
    $zapytanie .= " ORDER BY ".$sortowanie;    

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr'];    
            
            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID','center'),
                                      array('Nazwa','center'),
                                      array('Domyślny rabat','center'),
                                      array('Grupa klientów','center'),
                                      array('Klient','center'),
                                      array('Kategoria','center'),
                                      array('Producent','center'));
            echo $listing_danych->naglowek($tablica_naglowek);                    
            
            // stworzy tablice z nazwami producentow
            $TablicaTmp = Funkcje::TablicaProducenci();
            $TablicaProducenci = array();
            foreach ($TablicaTmp as $Tmp) {
                $TablicaProducenci[$Tmp['id']] = $Tmp['text'];
            }
            unset($TablicaTmp);
            
            // stworzy tablice z grupami klientow
            $TablicaTmp = Klienci::ListaGrupKlientow(false);
            $TablicaGrupyKlientow = array();
            foreach ($TablicaTmp as $Tmp) {
                $TablicaGrupyKlientow[$Tmp['id']] = $Tmp['text'];
            }                 
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['discount_id']) {
                     $tekst .= '<tr class="pozycja_on">';
                   } else {
                     $tekst .= '<tr class="pozycja_off">';
                  }        
                  
                  $tablica = array();
                  
                  $tablica[] = array($info['discount_id'],'center');
                  
                  $tablica[] = array($info['discount_name'],'center');
                  
                  $tablica[] = array($info['discount_discount'] < 0 ? '<span class="zielony">'.$info['discount_discount'].'%</span>' : '<span class="czerwony">'.$info['discount_discount'].'%</span>','center');
                  
                  // grupy klientow
                  if ( $info['discount_groups_id'] != '' ) {
                       //
                       $WynikGrupa = array();
                       $TmpGrupa = explode(',', (string)$info['discount_groups_id']);
                       //
                       foreach ( $TmpGrupa as $Tmp ) {
                          //
                          if ( isset($TablicaGrupyKlientow[(int)$Tmp]) && (int)$Tmp > 0 ) {
                               //
                               $WynikGrupa[] = $TablicaGrupyKlientow[(int)$Tmp];
                               //
                          }
                          //
                       }
                       //
                       $tablica[] = array(implode(', ', (array)$WynikGrupa),'center');
                       //
                  } else {
                       //                       
                       $tablica[] = array('-','center');
                       //
                  }
                  
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
                  
                  $wyswietl = '';
                  $KatId = explode('_', (string)Kategorie::SciezkaKategoriiId($info['discount_categories_id']));
                  
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
                  
                  if ( isset($TablicaKategorii[$info['discount_categories_id']]) ) {
                       $wyswietl = $pelna_sciezka_kategorii . '<span class="NazwaKatProd">' . $TablicaKategorii[$info['discount_categories_id']]['text'] . '</span>';
                  }
                  
                  unset($pelna_sciezka_kategorii);

                  $tablica[] = array($wyswietl,'center');
                  
                  unset($wyswietl);
                  
                  $wyswietl = '';
                  
                  if ( isset($TablicaProducenci[$info['discount_manufacturers_id']]) ) {
                       $wyswietl .= '<span class="NazwaKatProd">' . $TablicaProducenci[$info['discount_manufacturers_id']] . '</span>';
                  }
                  
                  $tablica[] = array($wyswietl,'center');   

                  unset($wyswietl);                  

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['discount_id']; 
                  $tekst .= '<a class="TipChmurka" href="klienci/rabaty_kategorii_producenta_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="klienci/rabaty_kategorii_producenta_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  
                  $tekst .= '</td></tr>';
                  
            } 
            $tekst .= '</table>';
            //
            echo $tekst;
            //
            $db->close_query($sql);
            unset($listing_danych,$tekst,$tablica,$tablica_naglowek, $TablicaProducentow, $TablicaGrupyKlientow);               

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
            
            <div id="naglowek_cont">Rabaty dla kategorii oraz producenta</div>  

            <div id="wyszukaj">
                <form action="klienci/rabaty_kategorii_producenta.php" method="post" id="klienciForm" class="cmxform">

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
                  echo '<div id="wyszukaj_ikona"><a href="klienci/rabaty_kategorii_producenta.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                          
                
                <div style="clear:both"></div>
            </div>        
            
            <div id="sortowanie">
            
                <span>Sortowanie: </span>

                <a id="sort_a1" class="sortowanie" href="klienci/rabaty_kategorii_producenta.php?sort=sort_a1">nazwa malejąco</a>
                <a id="sort_a2" class="sortowanie" href="klienci/rabaty_kategorii_producenta.php?sort=sort_a2">nazwa rosnąco</a>
                <a id="sort_a3" class="sortowanie" href="klienci/rabaty_kategorii_producenta.php?sort=sort_a3">rabat malejąco</a>
                <a id="sort_a4" class="sortowanie" href="klienci/rabaty_kategorii_producenta.php?sort=sort_a4">rabat rosnąco</a>
                <a id="sort_a7" class="sortowanie" href="klienci/rabaty_kategorii_producenta.php?sort=sort_a7">nazwiska klientów malejąco</a>
                <a id="sort_a8" class="sortowanie" href="klienci/rabaty_kategorii_producenta.php?sort=sort_a8">nazwiska klientów rosnąco</a>             
            
            </div>               

            <div style="clear:both;"></div> 
            
            <?php
            $zapytanie_kategorie = "select * from categories";
            $sqlm = $db->open_query($zapytanie_kategorie);
            if ((int)$db->ile_rekordow($sqlm) > 0) {         
            ?>
            
            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="klienci/rabaty_kategorii_producenta_dodaj.php">dodaj nową pozycję</a>
                </div>            
            </div>
            
            <div style="clear:both;"></div>

            <?php
            }
            $db->close_query($sqlm);
            unset($zapytanie_kategorie);
            ?>
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            <?php Listing::pokazAjax('klienci/rabaty_kategorii_producenta.php', $zapytanie, $ile_licznika, $ile_pozycji, 'discount_id'); ?>
            </script>              

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
