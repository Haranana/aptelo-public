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
        $warunki_szukania = " and (pd.products_name like '%".$szukana_wartosc."%')";
    }

    $zapytanie = "select distinct 
                         g.id_gift,
                         g.gift_status,
                         g.gift_value_of,
                         g.gift_value_for,
                         g.gift_value_exclusion,
                         g.gift_products_id,
                         g.gift_price,
                         g.customers_group_id,
                         g.gift_min_quantity,
                         g.gift_exclusion,
                         g.gift_exclusion_id,
                         g.gift_only_one,
                         pd.products_id,
                         pd.products_name,
                         p.products_image,
                         p.products_status
                    from products_gift g, products_description pd, products p
                   where p.products_id = g.gift_products_id and p.products_id = pd.products_id and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' ".$warunki_szukania;
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
                $sortowanie = 'pd.products_name asc';
                break;
            case "sort_a2":
                $sortowanie = 'pd.products_name desc';
                break;    
            case "sort_a3":
                $sortowanie = 'g.customers_group_id';
                break; 
            case "sort_a4":
                $sortowanie = 'g.gift_value_of asc';
                break;
            case "sort_a5":
                $sortowanie = 'g.gift_value_of desc';
                break;                        
        }            
    } else { $sortowanie = 'pd.products_name asc'; }    
    
    $zapytanie .= " order by ".$sortowanie;    
    
    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr'];   

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Akcja','center'),
                                      array('ID', 'center'),
                                      array('ID produktu', 'center'),
                                      array('Zdjęcie', 'center'),
                                      array('Nazwa produktu'),
                                      array('Cena brutto', 'center', 'white-space: nowrap'),
                                      array('Dostępny dla ...', '', '', 'class="ListingRwd"'),
                                      array('Tylko jeden', 'center'),
                                      array('Grupa klientów', 'center'),
                                      array('Status produktu','center'),
                                      array('Status gratisu','center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['id_gift']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['id_gift'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['id_gift'].'">';
                  }         

                  $tablica = array();
                  
                  $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" value="'.$info['id_gift'].'" id="opcja_'.$info['id_gift'].'" /><label class="OpisForPustyLabel" for="opcja_'.$info['id_gift'].'"></label><input type="hidden" name="id[]" value="'.$info['id_gift'].'" />','center');
                  
                  $tablica[] = array($info['id_gift'],'center');
                  
                  $tablica[] = array($info['gift_products_id'],'center');
                  
                  $tgm = '<div id="zoom'.rand(1,99999).'" class="imgzoom" onmouseover="ZoomIn(this,event)" onmouseout="ZoomOut(this)">';
                  $tgm .= '<div class="zoom">' . Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '250', '250') . '</div>';
                  $tgm .= Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '40', '40', ' class="Reload"', true);
                  $tgm .= '</div>';                  

                  $tablica[] = array($tgm,'center');                  
                  
                  $tablica[] = array('<b>' . $info['products_name'] . '</b>');
                  
                  if ($info['gift_price'] == 0) {
                      $tablica[] = array('produkt darmowy', 'center');
                    } else {
                      $tablica[] = array($info['gift_price']. ' ' . $_SESSION['domyslna_waluta']['symbol'], 'center');
                  }
                  
                  $tgm = '<span class="warunek">wartość ' . (((int)$info['gift_value_exclusion'] == 0) ? 'zamówienia' : 'produktów (wg warunków)') . ' od <b>'.$info['gift_value_of']. ' ' . $_SESSION['domyslna_waluta']['symbol'].'</b> do <b>'.$info['gift_value_for']. ' ' . $_SESSION['domyslna_waluta']['symbol'].'</b></span>';              
                  
                  $rodzaj = '';
                  $warunki = '';
                  
                  if ( $info['gift_exclusion'] == 'kategorie' && $info['gift_exclusion_id'] != '' ) {
                       $warunki = '<span class="warunek">tylko produktów z kategorii: ';
                       //
                       $kategoria_nazwa = $db->open_query("select distinct categories_id, categories_name from categories_description where categories_id in (".$info['gift_exclusion_id'].") and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
                       while ($nazwa = $kategoria_nazwa->fetch_assoc()) {
                              $warunki .= '<b>' . $nazwa['categories_name'] . '</b>, ';                               
                       }
                       $db->close_query($kategoria_nazwa);    
                       unset($kategoria_nazwa, $nazwa);                               
                       //
                       $warunki = substr((string)$warunki, 0, -2) . '</span>';
                       $rodzaj = ' z w/w kategorii ';
                       //
                  }
                  if ( $info['gift_exclusion'] == 'producenci' && $info['gift_exclusion_id'] != '' ) {
                       $warunki = '<span class="warunek">tylko produktów producentów: ';
                       //
                       $producent_nazwa = $db->open_query("select distinct manufacturers_name from manufacturers where manufacturers_id in (".$info['gift_exclusion_id'].")");
                       while ($nazwa = $producent_nazwa->fetch_assoc()) {
                              $warunki .= '<b>' . $nazwa['manufacturers_name'] . '</b>, ';                                
                       }
                       $db->close_query($producent_nazwa);    
                       unset($producent_nazwa, $nazwa);                               
                       //
                       $warunki = substr((string)$warunki, 0, -2) . '</span>';
                       $rodzaj = ' z w/w producentów ';
                       //                               
                  }  
                  if ( $info['gift_exclusion'] == 'kategorie_producenci' && $info['gift_exclusion_id'] != '' ) {
                       //
                       $podzial = explode('|', (string)$info['gift_exclusion_id']);
                       //
                       $warunki = '<span class="warunek">tylko produktów z kategorii: ';
                       //
                       $kategoria_nazwa = $db->open_query("select distinct categories_id, categories_name from categories_description where categories_id in (".$podzial[0].") and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
                       while ($nazwa = $kategoria_nazwa->fetch_assoc()) {
                              $warunki .= '<b>' . $nazwa['categories_name'] . '</b>, ';                               
                       }
                       $db->close_query($kategoria_nazwa);    
                       unset($kategoria_nazwa, $nazwa);                               
                       //
                       $warunki = substr((string)$warunki, 0, -2) . '</span>';
                       $rodzaj = ' z w/w kategorii ';
                       //
                       $warunki .= '<span class="warunek">oraz tylko produktów producentów: ';
                       //
                       $producent_nazwa = $db->open_query("select distinct manufacturers_name from manufacturers where manufacturers_id in (".$podzial[1].")");
                       while ($nazwa = $producent_nazwa->fetch_assoc()) {
                              $warunki .= '<b>' . $nazwa['manufacturers_name'] . '</b>, ';                                
                       }
                       $db->close_query($producent_nazwa);    
                       unset($producent_nazwa, $nazwa);                               
                       //
                       $warunki = substr((string)$warunki, 0, -2) . '</span>';
                       $rodzaj .= ' oraz z w/w producentów ';
                       //
                       unset($podzial);                       
                       //
                  }                   
                  if ( $info['gift_exclusion'] == 'produkty' && $info['gift_exclusion_id'] != '' ) {
                       $warunki = '<span class="warunek">tylko wybranych produktów: ';
                       //

                       $produkt_nazwa = $db->open_query("select distinct products_id, products_name from products_description where products_id in (".$info['gift_exclusion_id'].") and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
                       while ($nazwa = $produkt_nazwa->fetch_assoc()) {
                              $warunki .= '<b>' . $nazwa['products_name'] . '</b>, ';                               
                       }
                       $db->close_query($produkt_nazwa);    
                       unset($produkt_nazwa, $nazwa);                               
                       //
                       $warunki = substr((string)$warunki, 0, -2) . '</span>';
                       $rodzaj = ' z w/w produktów';
                       //                               
                  }    
                  $tgm .= $warunki;
                  unset($warunki);  

                  if ( $info['gift_min_quantity'] > 0 ) {
                       $tgm .= '<span class="warunek">minimalna ilości produktów w koszyku ' . $rodzaj . ': <b>'.$info['gift_min_quantity']. '</b></span>';              
                     } else {
                       $tgm .= '<span class="warunek">dowolna ilość produktów w koszyku ' . $rodzaj . '</span>';              
                  }
                  
                  unset($rodzaj);

                  $tablica[] = array( (($tgm != '') ? $tgm : '-'), '', '', 'class="ListingRwd"');
                  
                  // tylko jeden
                  if ($info['gift_only_one'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Tylko jeden'; } else { $obraz = 'aktywny_off.png'; $alt = 'Wszystkie gratisy'; }               
                  $tablica[] = array('<img src="obrazki/'.$obraz.'" alt="'.$alt.'" />','center');                 
                                    
                  // do jakiej grupy klientow
                  $tgm = '';
                  if ( $info['customers_group_id'] != '' ) {
                       $tabGrup = explode(',', (string)$info['customers_group_id']);
                       foreach ( $tabGrup as $idGrupy ) {
                          $tgm .= '<span class="grupa_klientow">' . Klienci::pokazNazweGrupyKlientow($idGrupy) . '</span><br />';
                       }
                       unset($tabGrup);
                  }      
                  $tablica[] = array( (($tgm != '') ? $tgm : '-'),'center');
                  unset($tgm);

                  // produkt aktywny czy nie
                  if ($info['products_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Ten produkt jest włączony'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ten produkt jest wyłączony'; }               
                  $tablica[] = array('<img src="obrazki/'.$obraz.'" alt="'.$alt.'" />','center');                    
                                    
                  // gratis aktywany czy nieaktywny
                  $tablica[] = array('<input type="checkbox" style="border:0px" name="status_'.$info['id_gift'].'" value="1" '.(($info['gift_status'] == '1') ? 'checked="checked"' : '').' id="status_'.$info['id_gift'].'" /><label class="OpisForPustyLabel" for="status_'.$info['id_gift'].'"></label>','center');                                     
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">'; 
                  
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['id_gift']; 
                  
                  $tekst .= '<a class="TipChmurka" href="gratisy/gratisy_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="gratisy/gratisy_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  
                  $tekst .= '</td></tr>';
                  
                  $tekst .= '<tr class="pozycjaRwd"><td class="WynikRwd" colspan="11" id="rwd_sk_'.$info['id_gift'].'"></td></tr>';
                  
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
          $.AutoUzupelnienie( 'szukaj', 'Podpowiedzi', 'ajax/autouzupelnienie_gratisy.php', 50, 350 );
        });
        </script>     
        
        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Gratisy</div>

            <div id="wyszukaj">
                <form action="gratisy/gratisy.php" method="post" id="poForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj produkt gratisu:</span>
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
                  echo '<div id="wyszukaj_ikona"><a href="gratisy/gratisy.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                       
                
                <div style="clear:both"></div>
                
            </div>        
            
            <form action="gratisy/gratisy_akcja.php" method="post" class="cmxform">
            
            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="gratisy/gratisy.php?sort=sort_a1">nazwy rosnąco</a>
                <a id="sort_a2" class="sortowanie" href="gratisy/gratisy.php?sort=sort_a2">nazwy malejąco</a>
                <a id="sort_a3" class="sortowanie" href="gratisy/gratisy.php?sort=sort_a3">wg grup klientów</a>
                <a id="sort_a4" class="sortowanie" href="gratisy/gratisy.php?sort=sort_a4">poziom kwotowy od rosnąco</a>
                <a id="sort_a5" class="sortowanie" href="gratisy/gratisy.php?sort=sort_a5">poziom kwotowy od malejąco</a>
                
            </div>             

            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="gratisy/gratisy_dodaj.php">dodaj nowy gratis</a>
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
                        <option value="1">usuń zaznaczone gratisy</option>
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
            <?php Listing::pokazAjax('gratisy/gratisy.php', $zapytanie, $ile_licznika, $ile_pozycji, 'id_gift'); ?>
            </script>              

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
