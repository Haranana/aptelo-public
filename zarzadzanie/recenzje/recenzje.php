<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ( isset($_GET['niezatwierdzone']) ) {
     //
     unset($_SESSION['filtry']['recenzje.php']);
     $_SESSION['filtry']['recenzje.php']['zatwierdzone'] = 2;
     //
     Funkcje::PrzekierowanieURL('recenzje.php');
}

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && !empty($_GET['szukaj'])) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " and pd.products_name like '%".$szukana_wartosc."%'";
        unset($szukana_wartosc);
    }
    
    // jezeli jest nr kat lub id
    if (isset($_GET['nrkat']) && !empty($_GET['nrkat'])) {
        $szukana_wartosc = $filtr->process($_GET['nrkat']);
        $warunki_szukania = " and (p.products_model like '%".$szukana_wartosc."%' or p.products_id = ".(int)$szukana_wartosc.")";
        unset($szukana_wartosc);
    }
    
    // jezeli jest wybrane czy recenzja zatwierdzona czy nie
    if (isset($_GET['zatwierdzone']) && (int)$_GET['zatwierdzone'] > 0) {
        if ((int)$_GET['zatwierdzone'] == 1) {
            $warunki_szukania .= " and r.approved = '1'";
        }
        if ((int)$_GET['zatwierdzone'] == 2) {
            $warunki_szukania .= " and r.approved = '0'";
        }        
    }      
    
    // jezeli jest wybrana opcja odpowiedzi
    if (isset($_GET['odpowiedzi']) && (int)$_GET['odpowiedzi'] > 0) {
        if ((int)$_GET['odpowiedzi'] == 1) {
            $warunki_szukania .= " and r.comments_answers != ''";
        }
        if ((int)$_GET['odpowiedzi'] == 2) {
            $warunki_szukania .= " and r.comments_answers IS NULL";
        }        
    }          

    // jezeli jest wybrana kategoria
    if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
        $id_kategorii = (int)$_GET['kategoria_id'];
        $warunki_szukania .= " and pc.categories_id = '".$id_kategorii."'";
        unset($id_kategorii);
    }

    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }
    
    $zapytanie = 'SELECT p.products_id, 
                         p.products_image, 
                         p.products_model, 
                         p.products_man_code,
                         p.products_ean,
                         p.products_status,
                         pd.products_id, 
                         pd.language_id, 
                         pd.products_name,
                         p.specials_status,
                         p.specials_date,
                         p.specials_date_end,                         
                         r.reviews_id,
                         r.products_id,
                         r.customers_id,
                         r.customers_name,
                         r.reviews_rating,
                         r.date_added,
                         r.approved,
                         r.comments_answers,
                         r.reviews_image,
                         r.reviews_confirm,
                         rd.reviews_text,
                         m.manufacturers_id,
                         m.manufacturers_name                         
                  FROM reviews r
                         LEFT JOIN reviews_description rd ON rd.reviews_id = r.reviews_id
                         LEFT JOIN products p ON p.products_id = r.products_id
                         LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = "' . (int)$_SESSION['domyslny_jezyk']['id'] . '"
                         LEFT JOIN manufacturers m ON m.manufacturers_id = p.manufacturers_id
                         '.((isset($_GET['kategoria_id'])) ? 'LEFT JOIN products_to_categories pc ON pc.products_id = p.products_id' : '') . $warunki_szukania . ' GROUP BY r.reviews_id '; 


    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }

    $db->close_query($sql);
    
    // jezeli jest sortowanie
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case "sort_a11":
                $sortowanie = 'pd.products_name asc, p.products_id';
                break;
            case "sort_a2":
                $sortowanie = 'pd.products_name desc, p.products_id';
                break;
            case "sort_a3":
                $sortowanie = 'p.products_status desc, pd.products_name, p.products_id';
                break;  
            case "sort_a4":
                $sortowanie = 'p.products_status asc, pd.products_name, p.products_id';
                break;                        
            case "sort_a7":
                $sortowanie = 'p.products_model asc, p.products_id';
                break;
            case "sort_a8":
                $sortowanie = 'p.products_model desc, p.products_id';
                break;    
            case "sort_a1":
                $sortowanie = 'r.date_added desc, pd.products_name, p.products_id';
                break;
            case "sort_a12":
                $sortowanie = 'r.date_added asc, pd.products_name, p.products_id';
                break;                            
            case "sort_a13":
                $sortowanie = 'r.approved asc, pd.products_name, p.products_id';
                break;
            case "sort_a14":
                $sortowanie = 'r.approved desc, pd.products_name, p.products_id';
                break; 
            case "sort_a15":
                $sortowanie = 'p.products_id desc';
                break;
            case "sort_a16":
                $sortowanie = 'p.products_id asc';
                break;                          
        }            
    } else { $sortowanie = 'r.date_added desc, pd.products_name, p.products_id'; } 

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
            $tablica_naglowek[] = array('ID opinii','center');
            $tablica_naglowek[] = array('ID prod','center');
            $tablica_naglowek[] = array('Zdjęcie','center');  
            $tablica_naglowek[] = array('Nazwa produktu');
            $tablica_naglowek[] = array('Data dodania / Wystawił / Ocena / Treść recenzji');
            $tablica_naglowek[] = array('Zatwierdzona','center');
            $tablica_naglowek[] = array('Status produktu','center', '', 'class="ListingSchowaj"'); 
            
            echo $listing_danych->naglowek($tablica_naglowek);

            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
                  
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['reviews_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['reviews_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['reviews_id'].'">';
                  } 

                  $tablica = array();

                  $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" value="'.$info['reviews_id'].'" id="opcja_'.$info['reviews_id'].'" /><label class="OpisForPustyLabel" for="opcja_'.$info['reviews_id'].'"></label><input type="hidden" name="id[]" value="'.$info['reviews_id'].'" /><input type="hidden" name="id_prod[]" value="'.$info['products_id'].'" />','center');
                  
                  $tablica[] = array($info['reviews_id'],'center');
                  
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
                  $do_jakich_kategorii_przypisany = '<span class="MaleInfoKat"> ';
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
                  
                  $kod_ean = '';
                  if (trim((string)$info['products_ean']) != '') {
                      $kod_ean = '<span class="MaleNrKatalogowy">EAN: <b>'.$info['products_ean'].'</b></span>';
                  }                  

                  // pobieranie danych o producencie
                  $prd = '';
                  if (trim((string)$info['manufacturers_name']) != '') {                     
                      //
                      $prd = '<span class="MaleProducent">Producent: <b>'.$info['manufacturers_name'].'</b></span>';
                      //
                  }                        
                  
                  $tgm = '<b>'.$info['products_name'].'</b>' . $do_jakich_kategorii_przypisany . $nr_kat . $kod_producenta . $kod_ean . $prd;
                  $tablica[] = array($tgm);
                  
                  unset($do_jakich_kategorii_przypisany, $nr_kat, $kod_ean, $prd);
                  
                  // sprawdzi czy klient kupowal produkt
                  $kupowal = false;
                      
                  // jezeli wystawiajacy to klient sklepu to bedzie to link do klienta
                  if ($info['customers_id'] > 0) {
                      //
                      // sprawdzi czy klient kupowal produkt
                      $zapytanie_produkty = "SELECT o.orders_id FROM orders o RIGHT JOIN orders_products op ON o.orders_id = op.orders_id and op.products_id = '" . $info['products_id'] . "' WHERE o.customers_id = '" . $info['customers_id'] . "' LIMIT 1";
                      $sql_produkty = $GLOBALS['db']->open_query($zapytanie_produkty);
                      //
                      if ((int)$GLOBALS['db']->ile_rekordow($sql_produkty) > 0) {
                          $kupowal = true;
                      }
                      //
                      $GLOBALS['db']->close_query($sql_produkty);
                      unset($zapytanie_produkty);
                      //
                      // sprawdzi czy z rejestracja czy bez
                      $zapytanie_klient = "SELECT customers_guest_account FROM customers WHERE customers_id = '" . $info['customers_id'] . "'";
                      $sql_klient = $GLOBALS['db']->open_query($zapytanie_klient);
                      //
                      if ((int)$GLOBALS['db']->ile_rekordow($sql_klient) > 0) {
                         //
                         $Klient = '<a href="klienci/klienci_edytuj.php?id_poz='.$info['customers_id'].'">' . $info['customers_name'] . '</a>';
                         //
                      } else {
                         //
                         $Klient = '<span>' . $info['customers_name'] . '</span>';
                         //
                      }
                      //
                      $GLOBALS['db']->close_query($sql_klient);
                      unset($zapytanie_klient);
                      //
                    } else {
                      //
                      $Klient = '<span>' . $info['customers_name'] . '</span>';
                      //
                  }
                  
                  // komentarz do opinii
                  $komentarz_recenzja = '';
                  if ( !empty($info['comments_answers']) ) {
                       $komentarz_recenzja = '<div class="RecenzjaOdpowiedz">Odpowiedź: <br /> ' . $info['comments_answers'] . '<a href="recenzje/recenzje_usun.php?id_poz='.$info['reviews_id'] .'&odpowiedz=tak" class="OdpowiedzSkasuj TipChmurka"><b>Skasuj odpowiedź</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a></div>';
                  }                  
                  
                  // potwierdzona zakupem
                  $zakup = '<div style="margin-top:20px"><a class="RecenzjaBezZakupu" href="recenzje/recenzje_zakup.php?id_poz='.$info['reviews_id'].'">recenzja niepotwierdzona zakupem</a></div>';
                  if ( (int)$info['reviews_confirm'] == 1 ) {
                        $zakup = '<div style="margin-top:20px"><a class="RecenzjaZakup" href="recenzje/recenzje_zakup.php?id_poz='.$info['reviews_id'].'">recenzja potwierdzona zakupem</a></div>';
                  }
                  
                  $kupowal_info = '';
                  if ( $kupowal == true ) {
                       $kupowal_info = '<div class="KlientKupowal">klient kupował ten produkt</div>';
                  }
                  //                  
                  
                  // data dodania recenzji
                  $tablica[] = array('<div class="DataDodania">Data dodania: <span>'.((Funkcje::czyNiePuste($info['date_added'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['date_added'])) : '-').'</span></div>' .
                                     '<div class="Wystawil">Wystawił: '.$Klient.'</div>' .
                                     '<div class="Ocena">Ocena: <em class="TipChmurka"><b>Ocena '.$info['reviews_rating'].'/5</b><img src="obrazki/recenzje/star_'.$info['reviews_rating'].'.png" alt="Ocena '.$info['reviews_rating'].'/5" /></em></div>' .
                                     '<div class="Komentarz">'.$info['reviews_text'].$komentarz_recenzja.'</div>'.
                                     ((!empty($info['reviews_image']) && file_exists('../grafiki_inne/' . $info['reviews_image'])) ? '<div class="FotoRecenzji"><a class="Recenzja-' . $info['reviews_id'] . '" href="../grafiki_inne/' . $info['reviews_image'] . '"><img src="../grafiki_inne/' . $info['reviews_image'] . '" alt="" /></a></div>
                                                                          <script>$(document).ready(function() { $(\'.Recenzja-' . $info['reviews_id'] . '\').colorbox({ maxHeight:\'90%\', maxWidth:\'90%\' }) });</script>' : '') . $zakup . $kupowal_info);
                        
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.$info['reviews_id'];
                  // jezeli jest klient
                  if ( $info['customers_id'] > 0 ) {
                      $zmienne_do_przekazania .= '&id=' . $info['customers_id'];      
                  }
                  
                  // zatwierdzona czy nie
                  if ($info['approved'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Ta recenzja jest zatwierdzona'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ta recenzja nie jest zatwierdzona'; }               
                  $tablica[] = array('<a class="TipChmurka" href="recenzje/recenzje_status.php'.$zmienne_do_przekazania.'"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></a>','center');                    
                  
                  // jezeli promocja ma date i data poczatkowa jest wieksza od dzisiejszej lub koncowa wczesniejsza od dzisiejszej to wylacza checkboxa zmiany statusu - produkt musi byc wylaczony
                  $Wylacz = '';
                  $TekstWylacz = '';
                  if ( ((FunkcjeWlasnePHP::my_strtotime($info['specials_date']) > time() && $info['specials_date'] != '0000-00-00 00:00:00') || (FunkcjeWlasnePHP::my_strtotime($info['specials_date_end']) < time()  && $info['specials_date_end'] != '0000-00-00 00:00:00') ) && $info['products_status'] == '0') {
                     $Wylacz = ' disabled="disabled"';
                     $TekstWylacz = ' ';
                  }
                  
                  $tablica[] = array((($wylacz_status == true) ? '<div class="wylKat TipChmurka"><b>Kategoria do której należy produkt jest wyłączona</b>' : '') . '<input type="checkbox" style="border:0px" name="status_'.$info['products_id'].'" value="1" '.(($info['products_status'] == '1') ? 'checked="checked"' : '').' ' . $TekstWylacz . $Wylacz .  ' id="status_'.$info['products_id'].'" /><label class="OpisForPustyLabel" for="status_'.$info['products_id'].'"></label>' . (($wylacz_status == true) ? '</div>' : ''), 'center', '', 'class="ListingSchowaj"');  

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['reviews_id'];     

                  $tekst .= '<td class="rg_right IkonyPionowo">';  
                  $tekst .= '<a class="TipChmurka" href="recenzje/recenzje_odpowiedz.php'.$zmienne_do_przekazania.'"><b>Odpowiedz na recenzję</b><img src="obrazki/powrot.png" alt="Odpowiedz" /></a>'; 
                  $tekst .= '<a class="TipChmurka" href="recenzje/recenzje_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a> <br />'; 
                  $tekst .= '<a class="TipChmurka" href="recenzje/recenzje_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
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
          $.AutoUzupelnienie( 'szukaj', 'Podpowiedzi', 'ajax/autouzupelnienie_produkty.php?typ=recenzje', 50, 350 );
        });
        </script>    

        <div id="caly_listing">
        
            <div id="ajax"></div>
        
            <div id="naglowek_cont">Recenzje produktów</div>
            
            <div id="wyszukaj">
                <form action="recenzje/recenzje.php" method="post" id="poForm" class="cmxform"> 
                
                <div id="wyszukaj_text">
                    <span>Wyszukaj produkt:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="35" />
                </div>  
                
                <div class="wyszukaj_select">
                    <span>ID lub nr kat:</span>
                    <input type="text" name="nrkat" value="<?php echo ((isset($_GET['nrkat'])) ? $filtr->process($_GET['nrkat']) : ''); ?>" size="25" />
                </div>                 
                
                <div class="wyszukaj_select">
                    <span>Zatwierdzone:</span>   
                    <select name="zatwierdzone">
                        <option value="0" <?php echo ((!isset($_GET['zatwierdzone'])) ? 'selected="selected"' : ''); ?>>-- wszystkie --</option>
                        <option value="1" <?php echo ((isset($_GET['zatwierdzone']) && (int)$_GET['zatwierdzone'] == 1) ? 'selected="selected"' : ''); ?>>zatwierdzone</option>
                        <option value="2" <?php echo ((isset($_GET['zatwierdzone']) && (int)$_GET['zatwierdzone'] == 2) ? 'selected="selected"' : ''); ?>>niezatwierdzone</option>
                    </select>
                </div>
 
                <div class="wyszukaj_select">
                    <span>Odpowiedzi:</span>   
                    <select name="odpowiedzi">
                        <option value="0" <?php echo ((!isset($_GET['odpowiedzi'])) ? 'selected="selected"' : ''); ?>>-- wszystkie --</option>
                        <option value="1" <?php echo ((isset($_GET['odpowiedzi']) && (int)$_GET['odpowiedzi'] == 1) ? 'selected="selected"' : ''); ?>>z odpowiedziami sklepu</option>
                        <option value="2" <?php echo ((isset($_GET['odpowiedzi']) && (int)$_GET['odpowiedzi'] == 2) ? 'selected="selected"' : ''); ?>>bez odpowiedzi sklepu</option>
                    </select>
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
                  echo '<div id="wyszukaj_ikona"><a href="recenzje/recenzje.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>            
                
                <div style="clear:both"></div>
                
            </div>        
            
            <form action="recenzje/recenzje_akcja.php" method="post" class="cmxform">
            
            <div id="sortowanie">
            
                <span>Sortowanie:</span>
                
                <a id="sort_a11" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a11">nazwy rosnąco</a>
                <a id="sort_a2" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a2">nazwy malejąco</a>
                <a id="sort_a7" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a7">nr katalogowy rosnąco</a>
                <a id="sort_a8" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a8">nr katalogowy malejąco</a>           
                <a id="sort_a3" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a3">aktywne</a>
                <a id="sort_a4" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a4">nieaktywne</a>
                <a id="sort_a1" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a1">daty dodania rosnąco</a>
                <a id="sort_a12" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a12">daty dodania malejąco</a>             
                <a id="sort_a13" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a13">zatwierdzone</a>
                <a id="sort_a14" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a14">niezatwierdzone</a>                 
                <a id="sort_a15" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a15">ID malejąco</a>
                <a id="sort_a16" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a16">ID rosnąco</a>                

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
            // przycisk dodania nowej recenzji
            ?>
            <div id="PozycjeIkon">
            
                <div>
                    <a class="dodaj" href="recenzje/recenzje_dodaj.php">dodaj nową recenzję</a>                  
                </div>
                
                <div id="Legenda" class="rg">
                    <?php if ($ile_pozycji > 0) { ?>
                    <a class="ExportXml" href="recenzje/recenzje_export_xml.php">eksportuj do pliku Google XML Product Review Feeds</a>
                    <?php } ?>                  
                    <a class="Import" href="recenzje/recenzje_import.php">importuj recenzje z pliku CSV</a>
                    <span class="Klient"> klient jest zarejestrowany w sklepie</span>
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
                                    <td class="lfp"><a href="recenzje/recenzje.php?kategoria_id='.$tablica_kat[$w]['id'].'" '.$style.'>'.$tablica_kat[$w]['text'].'</a></td>
                                    <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'\',\'\',\'recenzje\')" />' : '').'</td>
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
                                $analiza_sciezki = str_replace("_", ",", substr((string)$sciezka, 0, (int)$ostatnie));
                                ?>
                                
                                <script>           
                                podkat('<?php echo $analiza_sciezki; ?>', '<?php echo $cSciezka[count($cSciezka)-1]; ?>','','','recenzje');
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
                                <option value="1">usuń zaznaczone recenzje</option>
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
            <?php Listing::pokazAjax('recenzje/recenzje.php', $zapytanie, $ile_licznika, $ile_pozycji, 'reviews_id'); ?>
            </script>              
 
        </div>     

        <?php include('stopka.inc.php'); ?>

    <?php 
    } 
    
}?>
