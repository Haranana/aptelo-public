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
        $warunki_szukania .= " and (pd.products_name like '%".$szukana_wartosc."%')";
        unset($szukana_wartosc);
    }
    
    if (isset($_GET['szukaj_email'])) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_email']);
        $warunki_szukania .= " and (pn.customers_email_address like '%".$szukana_wartosc."%')";
        unset($szukana_wartosc);
    }    

    $zapytanie = "SELECT pn.products_notifications_id,
                         pn.customers_email_address,
                         pn.products_id,
                         pn.products_stock_attributes,
                         pn.date_added,
                         pd.products_name,
                         p.products_image,
                         p.products_status,
                         p.products_quantity,
                         p.products_availability_id,
                         p.products_buy,
                         pj.products_jm_quantity_type, 
                         ps.products_stock_quantity,
                         ps.products_stock_availability_id
                    FROM products_notifications pn
               LEFT JOIN products p ON p.products_id = pn.products_id
               LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'
               LEFT JOIN products_jm pj ON p.products_jm_id = pj.products_jm_id
               LEFT JOIN products_stock ps ON ps.products_id = pn.products_id AND ps.products_stock_attributes = pn.products_stock_attributes WHERE pn.products_notifications_id > 0 " . $warunki_szukania;
                   
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
                $sortowanie = 'pn.date_added desc';
                break; 
            case "sort_a2":
                $sortowanie = 'pn.date_added asc';
                break;              
            case "sort_a3":
                $sortowanie = 'pd.products_name asc';
                break;
            case "sort_a4":
                $sortowanie = 'pd.products_name desc';
                break;    
        }            
    } else { $sortowanie = 'pn.date_added desc'; }    
    
    $zapytanie .= " order by ".$sortowanie;    
    
    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
          
            // tablica dostepnosci
            $dostepnosci = array();
            $dostepnosci_automatyczne = '';
            //
            $dostepnosci_zapytanie = "SELECT distinct * FROM products_availability p, products_availability_description pd WHERE p.products_availability_id = pd.products_availability_id AND pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
            $sqls = $db->open_query($dostepnosci_zapytanie);
            //
            while ($infs = $sqls->fetch_assoc()) { 
                $dostepnosci[$infs['products_availability_id']] = array('kupowanie' => $infs['shipping_mode'], 'nazwa' => $infs['products_availability_name']);
            }
            $db->close_query($sqls); 
            unset($dostepnosci_zapytanie, $infs);           

            // tablica dostepnosci automatycznych
            $dostepnosci_zapytanie = "SELECT GROUP_CONCAT(CONVERT(quantity, CHAR(8)),':', CONVERT(products_availability_id, CHAR(8)) ORDER BY quantity DESC SEPARATOR ',') as wartosc FROM products_availability WHERE mode = '1'";
            $sqls = $db->open_query($dostepnosci_zapytanie);
            //
            while ($infs = $sqls->fetch_assoc()) {
                $dostepnosci_automatyczne = $infs['wartosc'];
            }
            $db->close_query($sqls);   
            unset($dostepnosci_zapytanie, $infs);             
            
            $zapytanie .= " limit ".$_GET['parametr'];   

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Info','center'),
                                      array('Akcja','center'),
                                      array('ID', 'center'),
                                      array('ID produktu', 'center'),
                                      array('Zdjęcie', 'center'),
                                      array('Nazwa produktu'),
                                      array('Status produktu','center'),
                                      array('Stan magazynowy','center','','class="ListingSchowajMobile"'),
                                      array('Dostępność','center','','class="ListingSchowajMobile"'),
                                      array('Kupowanie','center','','class="ListingSchowajMobile"'),
                                      array('Adres email', 'center'),
                                      array('Data dodania', 'center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
              
                  $mozna_kupic = true;
              
                  $ilosc_magazyn = $info['products_quantity'];
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['products_notifications_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['products_notifications_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['products_notifications_id'].'">';
                  }         

                  $tablica = array();
                  
                  $tablica[] = array('<div class="BrakZakupu"></div>','center');
                  
                  $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" value="'.$info['products_notifications_id'].'" id="opcja_'.$info['products_notifications_id'].'" /><label class="OpisForPustyLabel" for="opcja_'.$info['products_notifications_id'].'"></label><input type="hidden" name="id[]" value="'.$info['products_notifications_id'].'" />','center');
                  
                  $tablica[] = array($info['products_notifications_id'],'center');
                  
                  $tablica[] = array($info['products_id'],'center');
                  
                  $tgm = '<div id="zoom'.rand(1,99999).'" class="imgzoom" onmouseover="ZoomIn(this,event)" onmouseout="ZoomOut(this)">';
                  $tgm .= '<div class="zoom">' . Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '250', '250') . '</div>';
                  $tgm .= Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '40', '40', ' class="Reload"', true);
                  $tgm .= '</div>';                  

                  $tablica[] = array($tgm,'center');  
                  
                  $wyswietl_cechy = '';

                  if ( isset($info['products_stock_attributes']) && $info['products_stock_attributes'] != '' ) {

                    $tablica_kombinacji_cech = explode(',', (string)$info['products_stock_attributes']);
                    
                    for ( $t = 0, $c = count($tablica_kombinacji_cech); $t < $c; $t++ ) {
                    
                      $tablica_wartosc_cechy = explode('-', (string)$tablica_kombinacji_cech[$t]);

                      $nazwa_cechy = Funkcje::NazwaCechy( (int)$tablica_wartosc_cechy['0'] );
                      $nazwa_wartosci_cechy = Funkcje::WartoscCechy( (int)$tablica_wartosc_cechy['1'] );

                      $wyswietl_cechy .= '<span class="MaleInfoCecha">'.$nazwa_cechy . ': <b>' . $nazwa_wartosci_cechy . '</b></span>';
                      
                      unset($tablica_wartosc_cechy);
                      
                    }
                    
                    unset($tablica_kombinacji_cech);
                    
                    // jezeli jest powiazanie cech z magazynem
                    if ( CECHY_MAGAZYN == 'tak' ) {

                        if ( $info['products_stock_attributes'] != '' ) {
                          
                             $ilosc_magazyn = (float)$info['products_stock_quantity'];
                             
                        }
                        
                    }

                  }                  
                  
                  $tablica[] = array('<b><a href="produkty/produkty_edytuj.php?id_poz='.$info['products_id'].'">'.$info['products_name'].'</a></b>' . $wyswietl_cechy);
                  
                  // produkt aktywny czy nie
                  if ($info['products_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Ten produkt jest włączony'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ten produkt jest wyłączony'; }               
                  $tablica[] = array('<img src="obrazki/'.$obraz.'" alt="'.$alt.'" />','center'); 
                  
                  if ( $info['products_status'] == 0 ) {
                       //
                       $mozna_kupic = false;
                       //
                  } else {
                       //
                       // sprawdzi czy kategorie do jakich nalezy produkt sa wlaczone
                       $kategorie = $db->open_query("select ctc.categories_id from products_to_categories ctc, categories c where ctc.products_id = '" . (int)$info['products_id'] . "' and ctc.categories_id = c.categories_id and c.categories_status = '1'");
                       //
                       if ( (int)$db->ile_rekordow($kategorie) == 0 ) {
                           //
                           $mozna_kupic = false;
                           //
                       }
                       //
                       $db->close_query($kategorie);                    
                       //
                  }

                  // jezeli jednostka miary calkowita
                  if ( $info['products_jm_quantity_type'] == 1 ) {
                       $ilosc_magazyn = (int)$ilosc_magazyn;
                  }          
                  
                  // sprawdza jaki jest stan magazynu
                  $tgm = '';
                  if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && $ilosc_magazyn <= 0 ) {
                       //
                       $tgm = '<div class="NieMoznaKupowac">' . $ilosc_magazyn . '</div>';
                       $mozna_kupic = false;
                       //
                    } else {
                       //
                       $tgm = $ilosc_magazyn;                       
                       //
                  }

                  $tablica[] = array($tgm, 'center', '', 'class="ListingSchowajMobile"');
                  
                  unset($tgm);

                  // sprawdzi jaka jest dostepnosc produktu i czy mozna przy niej kupowac
                  $id_dostepnosci = $info['products_availability_id'];
                  
                  if ( isset($info['products_stock_attributes']) && $info['products_stock_attributes'] != '' ) {
                       //
                       if ( $info['products_stock_availability_id'] > 0 ) {
                            $id_dostepnosci = $info['products_stock_availability_id'];
                       }
                       //
                  }
                  
                  $dostepnosc_produktu = Produkt::ProduktDostepnoscNazwa($dostepnosci, $dostepnosci_automatyczne, $id_dostepnosci, $ilosc_magazyn);
                  
                  $tgm = '';
                  if ( $dostepnosc_produktu['kupowanie'] == '0' ) {
                       //
                       $tgm = '<div class="NieMoznaKupowac">' . $dostepnosc_produktu['nazwa'] . '</div>';
                       $mozna_kupic = false;
                       //
                    } else {
                       //
                       $tgm = $dostepnosc_produktu['nazwa'];                       
                       //
                  }                  
                  
                  $tablica[] = array($tgm, 'center', '', 'class="ListingSchowajMobile"');
                  
                  unset($tgm, $dostepnosc_produktu, $id_dostepnosci, $ilosc_magazyn);
                  
                  // sprawdzi czy produkt nie ma wylaczonej opcji kupowania
                  $tgm = '';
                  if ( $info['products_buy'] == '0' ) {
                       //
                       $tgm = '<em class="TipChmurka"><b>Produkt ma wyłączoną opcję kupowania</b><img src="obrazki/bez_kupowania.png" alt="Kupowanie" /></em>';
                       $mozna_kupic = false;
                       //
                    } else {
                       //
                       $tgm = '';
                       //
                  }  
                  
                  $tablica[] = array($tgm, 'center', '', 'class="ListingSchowajMobile"');
                  
                  unset($tgm);
                                  
                  $tablica[] = array($info['customers_email_address'], 'center');

                  $tablica[] = array(((Funkcje::czyNiePuste($info['date_added'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['date_added'])) : '-'),'center','white-space:nowrap'); 
                  
                  if ( PRODUKT_KUPOWANIE_STATUS == 'tak' && $mozna_kupic == true ) {
                       $tablica[0] = array('<em class="TipChmurka"><b>Ten produkt jest już dostępny w sprzedaży</b><img src="obrazki/tak.png" alt="Zakup" /></em>','center');
                  }

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">'; 
                  
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['products_notifications_id']; 
                  
                  $tekst .= '<a class="TipChmurka" href="klienci/klienci_powiadomienia_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';                  
                  $tekst .= '<a class="TipChmurka" href="klienci/klienci_wyslij_email.php?email=' . base64_encode((string)$info['customers_email_address']) . '"><b>Wyślij wiadomość e-mail</b><img src="obrazki/wyslij_mail.png" alt="Wyślij e-mail" /></a>';
                  
                  $tekst .= '</td></tr>';

            } 
            $tekst .= '</table>';
            //
            echo $tekst;
            //
            $db->close_query($sql);
            unset($listing_danych,$tekst,$tablica,$tablica_naglowek,$dostepnosci_automatyczne,$dostepnosci);        

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
            
            <div id="naglowek_cont">Powiadomienia klientów o dostępności produktów</div>

            <div id="wyszukaj">
                <form action="klienci/klienci_powiadomienia.php" method="post" id="poForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj produkt:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="40" />
                </div> 
                
                <div class="wyszukaj_select">
                    <span>Email:</span>
                    <input type="text" name="szukaj_email" id="szukaj_email" value="<?php echo ((isset($_GET['szukaj_email'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj_email'])) : ''); ?>" size="30" />
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
                  echo '<div id="wyszukaj_ikona"><a href="klienci/klienci_powiadomienia.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                       
                
                <div style="clear:both"></div>
                
            </div>        
            
            <form action="klienci/klienci_powiadomienia_akcja.php" method="post" class="cmxform">
            
            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="klienci/klienci_powiadomienia.php?sort=sort_a1">wg daty dodania malejąco</a>
                <a id="sort_a2" class="sortowanie" href="klienci/klienci_powiadomienia.php?sort=sort_a2">wg daty dodania rosnąco</a>                
                <a id="sort_a3" class="sortowanie" href="klienci/klienci_powiadomienia.php?sort=sort_a3">nazwy rosnąco</a>
                <a id="sort_a4" class="sortowanie" href="klienci/klienci_powiadomienia.php?sort=sort_a4">nazwy malejąco</a>
                
            </div>        

            <div id="PozycjeIkon">

                <?php if ($ile_pozycji > 0) { ?>
                <div class="rg">
                    <a class="WyslijPowiadomienie" href="klienci/klienci_powiadomienia_wyslij.php">wyślij powiadomienia do klientów</a>
                </div>                
                <?php } ?>

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
                        <option value="1">usuń zaznaczone pozycje</option>
                    </select>
                </div>
                
                <div style="clear:both;"></div>
                
            </div>             
            
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <?php if ($ile_pozycji > 0) { ?>
            <div style="text-align:right" id="zapisz_zmiany"><input type="submit" class="przyciskBut" value="Zapisz zmiany" /></div>
            <?php } ?>       

            </form> 

            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('klienci/klienci_powiadomienia.php', $zapytanie, $ile_licznika, $ile_pozycji, 'products_notifications_id'); ?>
            </script>              

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
