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
        $warunki_szukania .= " where (offers_name like '%".$szukana_wartosc."%')";
        unset($szukana_wartosc);
    }
    if (isset($_GET['nr_oferty'])) {
        $szukana_wartosc = $filtr->process($_GET['nr_oferty']);
        $warunki_szukania .= " where (offers_nr like '%".$szukana_wartosc."%')";
        unset($szukana_wartosc);
    }    
    if (isset($_GET['klient'])) {
        $szukana_wartosc = $filtr->process($_GET['klient']);
        $warunki_szukania .= " where (offers_customer like '%".$szukana_wartosc."%')";
        unset($szukana_wartosc);
    }
    
    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }
    
    $zapytanie = "select * from offers ".$warunki_szukania;

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ZapytanieDlaPozycji = "SELECT offers_id FROM offers" . $warunki_szukania;
    $sql = $db->open_query($ZapytanieDlaPozycji);
    $ile_pozycji = (int)$db->ile_rekordow($sql);

    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }

    $db->close_query($sql);
    
    // jezeli jest sortowanie
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case "sort_a1":
                $sortowanie = 'offers_nr asc';
                break;
            case "sort_a2":
                $sortowanie = 'offers_nr desc';
                break;         
            case "sort_a3":
                $sortowanie = 'offers_name asc';
                break;
            case "sort_a4":
                $sortowanie = 'offers_name desc';
                break; 
            case "sort_a5":
                $sortowanie = 'offers_date desc';
                break;
            case "sort_a6":
                $sortowanie = 'offers_date asc';
                break;                  
        }            
    } else { $sortowanie = 'offers_nr asc'; }    
    
    $zapytanie .= " order by ".$sortowanie;    

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr']; 

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID', 'center'),
                                      array('Nr oferty', 'center'),
                                      array('Tytuł'),
                                      array('Adresat', '', '', 'class="ListingRwd"'),
                                      array('Data utworzenia', 'center'),
                                      array('Ilość pozycji', 'center', 'white-space: nowrap', 'class="ListingRwd"'),
                                      array('Wartość produktów (brutto)', 'center'),
                                      array('Wartość produktów (netto)', 'center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['offers_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['offers_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['offers_id'].'">';
                  }      

                  $tablica = array();
                  
                  $tablica[] = array($info['offers_id'],'center');
                  
                  $tablica[] = array($info['offers_nr'],'center');

                  $tablica[] = array($info['offers_name']);
                  
                  $tablica[] = array(nl2br($info['offers_customer']),'','line-height:1.8', 'class="ListingRwd"');

                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['offers_date'])),'center');

                  // ile produktow do producenta
                  $produkty = $db->open_query("select COUNT('products_id') as ile_pozycji from offers_products where offers_id = '".(int)$info['offers_id']."'");
                  $infs = $produkty->fetch_assoc();
                  if ((int)$infs['ile_pozycji'] > 0) {
                     $ile_produktow = $infs['ile_pozycji'];
                    } else {
                     $ile_produktow = '-';
                  }
                  $db->close_query($produkty);
                  
                  $tablica[] = array($ile_produktow,'center', '', 'class="ListingRwd"'); 

                  unset($ile_produktow);
                  
                  // wartosc produktow - sprawdzi czy wszystkie maja cene brutto i netto
                  $produkty = $db->open_query("select products_price, products_price_tax, products_quantity from offers_products where offers_id = '".(int)$info['offers_id']."'");
                  $wartosc_brutto = 0;
                  $wszystkie_ceny_brutto = true;
                  //
                  $wartosc_netto = 0;
                  $wszystkie_ceny_netto = true;
                  //
                  while ($infs = $produkty->fetch_assoc()) {
                      //
                      if ( $infs['products_price_tax'] > 0 && $infs['products_quantity'] > 0 ) {
                           $wartosc_brutto += $infs['products_price_tax'] * $infs['products_quantity'];
                        } else {
                           $wszystkie_ceny_brutto = false;
                      }
                      if ( $infs['products_price'] > 0 && $infs['products_quantity'] > 0 ) {
                           $wartosc_netto += $infs['products_price'] * $infs['products_quantity'];
                        } else {
                           $wszystkie_ceny_netto = false;
                      }                      
                      //
                  }
                  $db->close_query($produkty);
                  
                  $tablica[] = array((($wszystkie_ceny_brutto == false) ? '<em class="TipChmurka"><b>Nie wszystkie produkty mają ceny brutto lub podaną ilość produktów</b><img src="obrazki/tip.png" alt="Brak danych" /></em>' : $waluty->FormatujCene($wartosc_brutto, false, $_SESSION['domyslna_waluta']['id'])),'center'); 
                  $tablica[] = array((($wszystkie_ceny_netto == false) ? '<em class="TipChmurka"><b>Nie wszystkie produkty mają ceny netto lub podaną ilość produktów</b><img src="obrazki/tip.png" alt="Brak danych" /></em>' : $waluty->FormatujCene($wartosc_netto, false, $_SESSION['domyslna_waluta']['id'])),'center'); 

                  unset($ile_produktow);
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['offers_id']; 
                  
                  $tekst .= '<a class="TipChmurka" href="oferty/oferty_produkty.php?oferta_id='.$info['offers_id'].'"><b>Produkty oferty</b><img src="obrazki/lista_wojewodztw.png" alt="Produkty oferty" /></a>';
                  $tekst .= '<a class="TipChmurka" href="oferty/oferty_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="oferty/oferty_duplikuj.php'.$zmienne_do_przekazania.'"><b>Duplikuj</b><img src="obrazki/duplikuj.png" alt="Duplikuj" /></a>';                   
                  $tekst .= '<a class="TipChmurka" href="oferty/oferty_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="oferty/oferty_pdf.php?id_poz='.$info['offers_id'].'"><b>Wygeneruj ofertę PDF</b><img src="obrazki/pdf.png" alt="Wygeneruj ofertę PDF" /></a>';
                  
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
            
            <div id="naglowek_cont">Oferty PDF</div>

            <div id="wyszukaj">
                <form action="oferty/oferty.php" method="post" id="poForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Tytuł oferty:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="40" />
                </div>  
                
                <div id="wyszukaj_text">
                    <span>Nr oferty:</span>
                    <input type="text" name="nr_oferty" value="<?php echo ((isset($_GET['nr_oferty'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['nr_oferty'])) : ''); ?>" size="30" />
                </div>                  
                
                <div id="wyszukaj_text">
                    <span>Nazwa klienta:</span>
                    <input type="text" name="klient" value="<?php echo ((isset($_GET['klient'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['klient'])) : ''); ?>" size="30" />
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
                  echo '<div id="wyszukaj_ikona"><a href="oferty/oferty.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>         
                
                <div style="clear:both"></div>
            </div>        
            
            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="oferty/oferty.php?sort=sort_a1">numeru rosnąco</a>
                <a id="sort_a2" class="sortowanie" href="oferty/oferty.php?sort=sort_a2">numeru malejąco</a>
                <a id="sort_a3" class="sortowanie" href="oferty/oferty.php?sort=sort_a3">nazwy rosnąco</a>
                <a id="sort_a4" class="sortowanie" href="oferty/oferty.php?sort=sort_a4">nazwy malejąco</a>
                <a id="sort_a5" class="sortowanie" href="oferty/oferty.php?sort=sort_a5">daty utworzenia malejąco</a>
                <a id="sort_a6" class="sortowanie" href="oferty/oferty.php?sort=sort_a6">daty utworzenia rosnąco</a>                
            
            </div>             

            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="oferty/oferty_dodaj.php">dodaj nową ofertę</a>
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
            <?php Listing::pokazAjax('oferty/oferty.php', $zapytanie, $ile_licznika, $ile_pozycji, 'offers_id'); ?>
            </script>
            
        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
