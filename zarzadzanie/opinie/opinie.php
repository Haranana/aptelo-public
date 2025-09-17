<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ( isset($_GET['niezatwierdzone']) ) {
     //
     unset($_SESSION['filtry']['opinie.php']);
     $_SESSION['filtry']['opinie.php']['zatwierdzone'] = 2;
     //
     Funkcje::PrzekierowanieURL('opinie.php');
}

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && !empty($_GET['szukaj'])) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " and (rs.customers_name like '%".$szukana_wartosc."%' or rs.customers_email like '%".$szukana_wartosc."%')";
        unset($szukana_wartosc);
    }

    // jezeli jest wybrana grupa klienta
    if (isset($_GET['zatwierdzone']) && (int)$_GET['zatwierdzone'] > 0) {
        if ((int)$_GET['zatwierdzone'] == 1) {
            $warunki_szukania .= " and rs.approved = '1'";
        }
        if ((int)$_GET['zatwierdzone'] == 2) {
            $warunki_szukania .= " and rs.approved = '0'";
        }        
    }   

    // jezeli jest wybrana opcja odpowiedzi
    if (isset($_GET['odpowiedzi']) && (int)$_GET['odpowiedzi'] > 0) {
        if ((int)$_GET['odpowiedzi'] == 1) {
            $warunki_szukania .= " and rs.comments_answers != ''";
        }
        if ((int)$_GET['odpowiedzi'] == 2) {
            $warunki_szukania .= " and rs.comments_answers IS NULL";
        }        
    }          

    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }

    $zapytanie = 'SELECT rs.* FROM reviews_shop rs ' . $warunki_szukania . ' GROUP BY reviews_shop_id ';

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
                $sortowanie = 'date_added desc';
                break;
            case "sort_a2":
                $sortowanie = 'date_added asc';
                break;
            case "sort_a3":
                $sortowanie = 'customers_email desc';
                break;  
            case "sort_a4":
                $sortowanie = 'customers_email asc';
                break;                        
            case "sort_a5":
                $sortowanie = 'approved desc';
                break;
            case "sort_a6":
                $sortowanie = 'approved asc';
                break;    
            case "sort_a7":
                $sortowanie = 'average_rating asc';
                break;
            case "sort_a8":
                $sortowanie = 'average_rating desc';
                break;                                               
        }            
    } else { $sortowanie = 'date_added desc'; } 

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
            $tablica_naglowek[] = array('Nazwa klienta','center', '', 'class="ListingSchowaj"');
            $tablica_naglowek[] = array('Adres email','center', '', 'class="ListingSchowaj"');
            $tablica_naglowek[] = array('Nr zamówienia','center');
            $tablica_naglowek[] = array('Oceny','center');
            $tablica_naglowek[] = array('Średnia ocena','center');
            $tablica_naglowek[] = array('Polecanie zakupów','center');            
            $tablica_naglowek[] = array('Data dodania','center');
            $tablica_naglowek[] = array('Komentarz', 'left', '', 'class="ListingSchowaj"');
            
            if ( OPINIE_PRODUKTY == 'tak' ) {
                 $tablica_naglowek[] = array('Zgoda na produkty','center');
            }
            
            $tablica_naglowek[] = array('Zatwier- <br /> dzona','center');
            
            echo $listing_danych->naglowek($tablica_naglowek);

            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
                  
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['reviews_shop_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['reviews_shop_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['reviews_shop_id'].'">';
                  } 

                  $tablica = array();

                  $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" value="'.$info['reviews_shop_id'].'" id="opcja_'.$info['reviews_shop_id'].'" /><label class="OpisForPustyLabel" for="opcja_'.$info['reviews_shop_id'].'"></label><input type="hidden" name="id[]" value="'.$info['reviews_shop_id'].'" />','center');
                  
                  $tablica[] = array($info['reviews_shop_id'],'center'); 

                  // dane klienta
                  $klient = $info['customers_name'];
                  if ( $info['customers_id'] > 0 ) {
                       $klient = '<a href="klienci/klienci_edytuj.php?id_poz=' . $info['customers_id'] . '">' . $klient . '</a>';
                  }
                  $tablica[] = array($klient,'center', '', 'class="ListingSchowaj"');
                  
                  // dane email
                  $adres_email = $info['customers_email'];
                  if ( $info['customers_id'] > 0 ) {
                       $adres_email = '<a href="klienci/klienci_edytuj.php?id_poz=' . $info['customers_id'] . '">' . $adres_email . '</a>';
                  }
                  $tablica[] = array($adres_email,'center', '', 'class="ListingSchowaj"');
                  
                  $tablica[] = array((($info['orders_id'] > 0) ? '<a href="sprzedaz/zamowienia_szczegoly.php?id_poz=' . $info['orders_id'] . '">' . $info['orders_id'] . '</a>' : '-'),'center');
                  
                  $tbOcen = '<div class="TabelaOcen">
                                <div class="Wiersz"><div>Jakość obsługi</div><div><img src="obrazki/recenzje/star_' . $info['handling_rating'] . '.png" alt="Ocena ' . $info['handling_rating'] . '/5" /></div></div>
                                <div class="Wiersz"><div>Czas realizacji</div><div><img src="obrazki/recenzje/star_' . $info['lead_time_rating'] . '.png" alt="Ocena ' . $info['lead_time_rating'] . '/5" /></div></div>
                                <div class="Wiersz"><div>Ceny produktów</div><div><img src="obrazki/recenzje/star_' . $info['price_rating'] . '.png" alt="Ocena ' . $info['price_rating'] . '/5" /></div></div>
                                <div class="Wiersz"><div>Jakość produktów</div><div><img src="obrazki/recenzje/star_' . $info['quality_products_rating'] . '.png" alt="Ocena ' . $info['quality_products_rating'] . '/5" /></div></div>
                            </div>';
                  
                  $tablica[] = array($tbOcen);
                  unset($tbOcen);        

                  $srednia = '<div class="SredniaOcena">' . number_format(round($info['average_rating'],1), 1, ',', ' ') . '</div>';
                  $tablica[] = array($srednia, 'center');
                  unset($srednia);
                  
                  $tablica[] = array((($info['recommending'] == '1') ? 'TAK' : 'NIE'),'center');
                  $tablica[] = array(((Funkcje::czyNiePuste($info['date_added'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['date_added'])) : '-'),'center');
                  
                  // komentarz do opinii
                  $komentarz_opinia = '';
                  if ( !empty($info['comments_answers']) ) {
                       $komentarz_opinia = '<div class="OpiniaOdpowiedz">Odpowiedź: <br /> ' . $info['comments_answers'] . '<a href="opinie/opinie_usun.php?id_poz='.$info['reviews_shop_id'] .'&odpowiedz=tak" class="OdpowiedzSkasuj TipChmurka"><b>Skasuj odpowiedź</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a></div>';
                  }
                    
                  $tablica[] = array('<div class="Komentarz">' . $info['comments'] . $komentarz_opinia . '</div>' .
                                      ((!empty($info['reviews_shop_image']) && file_exists('../grafiki_inne/' . $info['reviews_shop_image'])) ? '<div class="FotoOpinie"><a class="Opinia-' . $info['reviews_shop_id'] . '" href="../grafiki_inne/' . $info['reviews_shop_image'] . '"><img src="../grafiki_inne/' . $info['reviews_shop_image'] . '" alt="" /></a></div>
                                                                          <script>$(document).ready(function() { $(\'.Opinia-' . $info['reviews_shop_id'] . '\').colorbox({ maxHeight:\'90%\', maxWidth:\'90%\' }) });</script>' : ''), 'left', '', 'class="ListingSchowaj"');

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.$info['reviews_shop_id'];
                  // jezeli jest klient
                  if ( $info['customers_id'] > 0 ) {
                      $zmienne_do_przekazania .= '&amp;id=' . $info['customers_id'];      
                  }
                  
                  if ( OPINIE_PRODUKTY == 'tak' ) {                    
                      if ($info['products_approved'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Klient wyraził zgodę'; } else { $obraz = 'aktywny_off.png'; $alt = 'Klient nie wyraził zgody'; }               
                      $tablica[] = array('<img src="obrazki/' . $obraz . '" alt="' . $alt . '" />','center');                  
                  }
                  
                  if ($info['approved'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Ta opinia jest zatwierdzona'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ta opinia nie jest zatwierdzona'; }               
                  $tablica[] = array('<a class="TipChmurka" href="opinie/opinie_status.php' . $zmienne_do_przekazania . '"><b>' . $alt . '</b><img src="obrazki/' . $obraz . '" alt="' . $alt . '" /></a>','center');

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['reviews_shop_id'];     

                  $tekst .= '<td class="rg_right IkonyPionowo">';  
                  $tekst .= '<a class="TipChmurka" href="opinie/opinie_odpowiedz.php'.$zmienne_do_przekazania.'"><b>Odpowiedz na opinie</b><img src="obrazki/powrot.png" alt="Odpowiedz" /></a>'; 
                  $tekst .= '<a class="TipChmurka" href="opinie/opinie_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>'; 
                  $tekst .= '<a class="TipChmurka" href="opinie/opinie_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
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
        
            <div id="naglowek_cont">Opinie o sklepie</div>
            
            <div id="wyszukaj">
                <form action="opinie/opinie.php" method="post" id="poForm" class="cmxform"> 
                
                <div id="wyszukaj_text">
                    <span>Wyszukaj email lub nazwę klienta:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="35" />
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

                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="opinie/opinie.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>            
                
                <div style="clear:both"></div>
                
            </div>        
            
            <form action="opinie/opinie_akcja.php" method="post" class="cmxform">
            
            <div id="sortowanie">
            
                <span>Sortowanie:</span>
                
                <a id="sort_a1" class="sortowanie" href="opinie/opinie.php?sort=sort_a1">data dodania rosnąco</a>
                <a id="sort_a2" class="sortowanie" href="opinie/opinie.php?sort=sort_a2">data dodania malejąco</a>
                <a id="sort_a3" class="sortowanie" href="opinie/opinie.php?sort=sort_a3">adres email rosnąco</a>
                <a id="sort_a4" class="sortowanie" href="opinie/opinie.php?sort=sort_a4">adres email malejąco</a>           
                <a id="sort_a5" class="sortowanie" href="opinie/opinie.php?sort=sort_a5">zatwierdzone</a>
                <a id="sort_a6" class="sortowanie" href="opinie/opinie.php?sort=sort_a6">niezatwierdzone</a>
                <a id="sort_a7" class="sortowanie" href="opinie/opinie.php?sort=sort_a7">średnia ocena rosnąco</a>
                <a id="sort_a8" class="sortowanie" href="opinie/opinie.php?sort=sort_a8">średnia ocena malejąco</a>                            

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
                    <a class="dodaj" href="opinie/opinie_dodaj.php">dodaj nową opinie</a>
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
                        <option value="1">usuń zaznaczone opinie</option>
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
            <?php Listing::pokazAjax('opinie/opinie.php', $zapytanie, $ile_licznika, $ile_pozycji, 'reviews_shop_id'); ?>
            </script>              
 
        </div>     

        <?php include('stopka.inc.php'); ?>

    <?php 
    } 
    
}?>
