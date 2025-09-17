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
        $warunki_szukania = " and CONCAT(c.customers_firstname, ' ', c.customers_lastname, c.customers_email_address, a.entry_company, a.entry_nip) LIKE '%".$szukana_wartosc."%'";
    }

    $zapytanie = "select * from customers c
                    LEFT JOIN address_book a on c.customers_id = a.customers_id and c.customers_default_address_id = a.address_book_id
                    where c.customers_discount != 0" . $warunki_szukania;
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
                $sortowanie = 'c.customers_lastname desc';
                break;
            case "sort_a2":
                $sortowanie = 'c.customers_lastname asc';
                break;                 
            case "sort_a3":
                $sortowanie = 'c.customers_discount desc';
                break;
            case "sort_a4":
                $sortowanie = 'c.customers_discount asc';
                break;                                  
        }            
    } else { $sortowanie = 'c.customers_lastname desc'; }
    
    $zapytanie .= " ORDER BY ".$sortowanie;    

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
        
            $zapytanie .= " limit ".$_GET['parametr']; 
            
            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID','center'),
                                      array('Klient','center'),
                                      array('Adres email','center'),
                                      array('Rabat','center'),
                                      array('Status klienta','center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['customers_id']) {
                     $tekst .= '<tr class="pozycja_on">';
                   } else {
                     $tekst .= '<tr class="pozycja_off">';
                  }        
                  
                  $tablica = array();
                  
                  $tablica[] = array($info['customers_id'],'center');

                  $wyswietlana_nazwa = '<a href="klienci/klienci_edytuj.php?id_poz='.$info['customers_id'].'">';

                  if ( $info['entry_company'] != '' ) {
                    $wyswietlana_nazwa .= '<span class="Firma"">'.$info['entry_company'] . '</span><br />';
                  }
                  $wyswietlana_nazwa .= $info['customers_firstname']. ' ' . $info['customers_lastname'] . ', ';
                  $wyswietlana_nazwa .= $info['entry_street_address']. ', ';
                  $wyswietlana_nazwa .= $info['entry_postcode']. ' ' . $info['entry_city'].'</a>';
                  $tablica[] = array($wyswietlana_nazwa,'','line-height:15px');
                  
                  $tablica[] = array('<span class="MalyMail">' . $info['customers_email_address'] . '</span>','center');
                  
                  $tablica[] = array(( $info['customers_discount'] < 0 ? '<span class="zielony">'.$info['customers_discount'].'%</span>' : '<span class="czerwony">'.$info['customers_discount'].'%</span>'),'center');
                  
                  // aktywany czy nieaktywny
                  if ($info['customers_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Konto klienta jest aktywne'; } else { $obraz = 'aktywny_off.png'; $alt = 'Konto klienta jest nieaktywne'; }                                 
                  
                  $tablica[] = array('<em class="TipChmurka"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></em>','center');  
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">'; 
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['customers_id']; 
                  $tekst .= '<a class="TipChmurka" href="klienci/rabaty_klientow_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="klienci/rabaty_klientow_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  
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
            
            <div id="naglowek_cont">Rabaty indywidualne klientów</div>  

            <div id="wyszukaj">
                <form action="klienci/rabaty_klientow.php" method="post" id="klienciForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj klienta:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="40" />
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
                  echo '<div id="wyszukaj_ikona"><a href="klienci/rabaty_klientow.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>           
                
                <div style="clear:both"></div>
            </div>        
            
            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="klienci/rabaty_klientow.php?sort=sort_a1">nazwiska malejąco</a>
                <a id="sort_a2" class="sortowanie" href="klienci/rabaty_klientow.php?sort=sort_a2">nazwiska rosnąco</a>
                <a id="sort_a3" class="sortowanie" href="klienci/rabaty_klientow.php?sort=sort_a3">rabat malejąco</a>
                <a id="sort_a4" class="sortowanie" href="klienci/rabaty_klientow.php?sort=sort_a4">rabat rosnąco</a>
                
            </div>               

            <div style="clear:both;"></div>               
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            <?php Listing::pokazAjax('klienci/rabaty_klientow.php', $zapytanie, $ile_licznika, $ile_pozycji, 'customers_id'); ?>
            </script>              

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
