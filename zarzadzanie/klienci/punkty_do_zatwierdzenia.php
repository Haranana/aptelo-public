<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_GET['id']) ) {
        Funkcje::PrzekierowanieURL('punkty_do_zatwierdzenia.php?id_poz=' . (int)$_GET['id'] . Funkcje::Zwroc_Get(array('id','x','y'), true));
    }
    
    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && $_GET['szukaj'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " and CONCAT(c.customers_firstname, ' ', c.customers_lastname, c.customers_email_address) LIKE '%".$szukana_wartosc."%'";
    }
    
    if ( isset($_GET['szukaj_status']) && $_GET['szukaj_status'] != '0' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_status']);
        $warunki_szukania .= " and o.orders_status = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    } 

    if ( isset($_GET['szukaj_status']) && $_GET['szukaj_status'] != '0' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_status']);
        $warunki_szukania .= " and o.orders_status = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    } 

    if (isset($_GET['zatwierdzone']) && (int)$_GET['zatwierdzone'] > 0) {
        if ((int)$_GET['zatwierdzone'] == 1) {
            $warunki_szukania .= " and r.approved = '1'";
        }
        if ((int)$_GET['zatwierdzone'] == 2) {
            $warunki_szukania .= " and r.approved = '0'";
        }        
    }        
    
    $zapytanie = "SELECT cp.unique_id,
                         cp.points_comment,
                         cp.orders_id,
                         cp.points_type,
                         cp.points_status,
                         cp.reviews_id,
                         cp.points,
                         cp.customers_id,
                         cp.date_added,
                         c.customers_firstname,
                         c.customers_lastname,
                         c.customers_email_address
                    FROM customers_points cp
              RIGHT JOIN customers c ON c.customers_id = cp.customers_id
               LEFT JOIN orders o ON o.orders_id = cp.orders_id
               LEFT JOIN reviews r ON r.reviews_id = cp.reviews_id
                   WHERE cp.points_status != '2' AND cp.points_status != '4' AND cp.points_status != '3' ".$warunki_szukania;

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
                $sortowanie = 'cp.date_added desc';
                break;
            case "sort_a2":
                $sortowanie = 'cp.date_added asc';
                break;    
            case "sort_a3":
                $sortowanie = 'c.customers_lastname, c.customers_firstname asc';
                break; 
            case "sort_a4":
                $sortowanie = 'c.customers_lastname, c.customers_firstname desc';
                break;
            case "sort_a5":
                $sortowanie = 'cp.points_status asc';
                break;
            case "sort_a6":
                $sortowanie = 'cp.points_comment asc';
                break;
            case "sort_a7":
                $sortowanie = 'cp.points desc';
                break;                        
        }            
    } else { $sortowanie = 'cp.date_added desc'; }    
    
    // informacje o produktach - zakres
    $zapytanie .= " order by ".$sortowanie;    
    
    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr']; 

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Info','center', '', 'class="ListingSchowajMobile"'),
                                      array('Akcja','center'),
                                      array('ID', 'center'),
                                      array('Tytuł punktów'),
                                      array('Data dodania','center'),
                                      array('Klient','center'),
                                      array('Adres email','center', '', 'class="ListingSchowaj"'),
                                      array('Punkty','center'),
                                      array('Status','center'),
                                      array('Status zamówienia&nbsp;/ <br /> recenzji','center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['unique_id']) {
                     $tekst .= '<tr class="pozycja_on">';
                   } else {
                     $tekst .= '<tr class="pozycja_off">';
                  }        

                  $tablica = array();
                  
                  $Okienko = '';
                  if ( $info['orders_id'] > 0 && ($info['points_type'] == "SP" || $info['points_type'] == "PP")) {
                     //
                     $Okienko = '<div id="zamowienie_'. $info['unique_id'] . '_' .$info['orders_id'].'" class="zmzoom_punkty_zamowienie"><div class="podglad_zoom"></div><img src="obrazki/info_duze.png" alt="Szczegóły" /></div>';
                     //
                  }
                  if ( $info['reviews_id'] > 0 && $info['points_type'] == "RV") {
                     //
                     $Okienko = '<div id="recenzja_'.$info['reviews_id'].'" class="zmzoom_punkty_recenzje"><div class="podglad_zoom"></div><img src="obrazki/info_duze.png" alt="Szczegóły" /></div>';
                     //
                  }                  
                  
                  $tablica[] = array($Okienko, '', 'width:30px', 'class="ListingSchowajMobile"');
                  
                  $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" value="'.$info['unique_id'].'" id="opcja_'.$info['unique_id'].'" /><label class="OpisForPustyLabel" for="opcja_'.$info['unique_id'].'"></label><input type="hidden" name="id[]" value="'.$info['unique_id'].'" />','center');
                  
                  $tablica[] = array($info['unique_id'],'center');
                  
                  $tgm = '';
                  $recenzja_akceptacja = false;
                  
                  switch ($info['points_type']) {
                    case "RV":
                        //
                        $zapytanie_recenzja = "SELECT r.reviews_id, r.approved, pd.products_name FROM reviews r, products_description pd WHERE r.products_id = pd.products_id and reviews_id = '" . (int)$info['reviews_id'] . "' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
                        $sql_recenzja = $db->open_query($zapytanie_recenzja);
                        $infr = $sql_recenzja->fetch_assoc();
                        //
                        if ((int)$db->ile_rekordow($sql_recenzja) > 0) {
                            $tgm = '<a href="recenzje/recenzje_edytuj.php?id_poz=' . $info['reviews_id'] . '">';
                            $recenzja_akceptacja = ((($infr['approved']) == 1) ? true : false);
                            //
                            $tgm .= 'Punkty za recenzję produktu ' . '<strong>' . $infr['products_name'] . '</strong>';
                            //
                            $tgm .= '</a>';
                        } else {
                            $tgm = 'Punkty za recenzję produktu';
                        }
                        //
                        $db->close_query($sql_recenzja);
                        unset($infr);
                        //
                        break;
                    case "SP":
                        $tgm = '<a href="sprzedaz/zamowienia_szczegoly.php?id_poz=' . $info['orders_id'] . '">Punkty za zamówienie nr <strong>' . $info['orders_id'] . '</strong></a>';
                        break;
                    case "PP":
                        $tgm = '<a href="sprzedaz/zamowienia_szczegoly.php?id_poz=' . $info['orders_id'] . '">Program partnerski - punkty za zamówienie nr <strong>' . $info['orders_id'] . '</strong></a>';
                        break;                           
                    case "RJ":
                        $tgm = 'Punkty za rejestrację';
                        break;                         
                    default:
                        $tgm = $info['points_comment'];
                        break;                 
                  }              

                  $tablica[] = array($tgm); 
                  unset($tgm);
                  
                  $tablica[] = array(((Funkcje::czyNiePuste($info['date_added'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['date_added'])) : '-'),'center','white-space:nowrap'); 
                  
                  $tablica[] = array($info['customers_firstname'] . ' ' . $info['customers_lastname'],'center');  
                  
                  $tablica[] = array($info['customers_email_address'], 'center', '', 'class="ListingSchowaj"');  
                  
                  $tablica[] = array($info['points'],'center');  
                  
                  $tablica[] = array(Klienci::pokazNazweStatusuPunktow($info['points_status'], $_SESSION['domyslny_jezyk']['id']),'center'); 

                  // jezeli za zamowienie
                  $tgm = '';
                  if ( $info['orders_id'] > 0 && ($info['points_type'] == "SP" || $info['points_type'] == "PP") ) {
                      //
                      $zamowienie = new Zamowienie($info['orders_id']);
                      //
                      if ( isset($zamowienie->info) ) {
                           if ( isset($zamowienie->statusy) && count($zamowienie->statusy) > 0 ) {
                                $zamowienie_status = end($zamowienie->statusy);                  
                                $tgm = Sprzedaz::pokazNazweStatusuZamowienia($zamowienie_status['status_id'], $_SESSION['domyslny_jezyk']['id']);
                           }
                      }
                      //
                      unset($zamowienie);
                  }
                  
                  // jezeli za recenzje
                  if ( $info['reviews_id'] > 0 && $info['points_type'] == "RV" ) {
                      //
                      if ( $recenzja_akceptacja == true ) {
                          $tgm = '<span style="color:#1d9918">Zaakceptowana</span>';
                        } else {
                          $tgm = '<span style="color:#ff0000">Niezaakceptowana</span>';
                      }
                      //
                  }    
                  $tablica[] = array($tgm, 'center');
                  unset($tgm);

                  unset($recenzja_akceptacja);
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?pkt=1&amp;id='.(int)$info['unique_id'].'&amp;id_poz='.(int)$info['customers_id']; 
                  
                  $tekst .= '<a class="TipChmurka" href="klienci/klienci_punkty_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="klienci/klienci_punkty_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="klienci/klienci_punkty_status.php'.$zmienne_do_przekazania.'"><b>Zmień status</b><img src="obrazki/zatwierdz.png" alt="Zmień status" /></a>';
                  
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
             
        <script>
        $(document).ready(function() {
          $.AutoUzupelnienie( 'szukaj', 'PodpowiedziMale', 'ajax/autouzupelnienie_punkty_do_zatwierdzenia.php', 50, 400 );
        });
        </script>         
        
        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Punkty klientów do zatwierdzenia</div>

            <div id="wyszukaj">
                <form action="klienci/punkty_do_zatwierdzenia.php" method="post" id="poForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj klienta:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? $filtr->process($_GET['szukaj']) : ''); ?>" size="40" />
                </div>  
                
                <div class="wyszukaj_select">
                    <span>Status zamówienia:</span>
                    <?php
                    $tablica_status= Array();
                    $tablica_status = Sprzedaz::ListaStatusowZamowien(true);
                    echo Funkcje::RozwijaneMenu('szukaj_status', $tablica_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : ''), ' style="max-width:200px"'); ?>
                </div>         

                <div class="wyszukaj_select">
                    <span>Status recenzji:</span>   
                    <select name="zatwierdzone">
                        <option value="0" <?php echo ((!isset($_GET['zatwierdzone'])) ? 'selected="selected"' : ''); ?>>dowolny</option>
                        <option value="1" <?php echo ((isset($_GET['zatwierdzone']) && (int)$_GET['zatwierdzone'] == 1) ? 'selected="selected"' : ''); ?>>zatwierdzone</option>
                        <option value="2" <?php echo ((isset($_GET['zatwierdzone']) && (int)$_GET['zatwierdzone'] == 2) ? 'selected="selected"' : ''); ?>>niezatwierdzone</option>
                    </select>
                </div>                
                
                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php 
                // tworzy ukryte pola hidden do wyszukiwania - filtra 
                if (isset($_GET['sort'])) { 
                    echo '<div><input type="hidden" name="sort" value="'.$filtr->process($_GET['sort']).'" /></div>';
                }                
                ?>    

                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="klienci/punkty_do_zatwierdzenia.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 

                <div style="clear:both"></div>
            </div>        
            
            <form action="klienci/punkty_do_zatwierdzenia_akcja.php" method="post" class="cmxform">
            
            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="klienci/punkty_do_zatwierdzenia.php?sort=sort_a1">daty dodania malejąco</a>
                <a id="sort_a2" class="sortowanie" href="klienci/punkty_do_zatwierdzenia.php?sort=sort_a2">daty dodania rosnąco</a>
                <a id="sort_a3" class="sortowanie" href="klienci/punkty_do_zatwierdzenia.php?sort=sort_a3">nazwy klienta rosnąco</a>
                <a id="sort_a4" class="sortowanie" href="klienci/punkty_do_zatwierdzenia.php?sort=sort_a4">nazwy klienta malejąco</a>
                <a id="sort_a5" class="sortowanie" href="klienci/punkty_do_zatwierdzenia.php?sort=sort_a5">wg statusu</a>
                <a id="sort_a6" class="sortowanie" href="klienci/punkty_do_zatwierdzenia.php?sort=sort_a6">wg tytułu punktów</a>
                <a id="sort_a7" class="sortowanie" href="klienci/punkty_do_zatwierdzenia.php?sort=sort_a7">wg ilości punktów rosnąco</a>
            
            </div>             

            <div style="clear:both;"></div>               
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            
            <script>
            $(document).ready(function() {
                $("#akcja_dolna").change( function () {
                    var va = $("#akcja_dolna").val();
                    if (va == '2') {
                        $("#statusy").css('display','block');
                       } else {
                        $("#statusy").css('display','none');
                    }                             
                });
            });
            </script>             
            
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
                        <option value="2">zmień status zaznaczonych</option>
                    </select>
                </div>
                
                <div style="clear:both;"></div>
                
                <div id="statusy" class="StatusyPunktow" style="display:none">
                    Nowy status: <?php echo Funkcje::RozwijaneMenu('status', Klienci::ListaStatusowPunktow(false)); ?>
                    <br /><br /><input type="checkbox" checked="checked" value="tak" name="dodajPkt" id="dodajPkt" /><label class="OpisFor" for="dodajPkt">Dodaj punkty klientowi</label>
                     <input type="checkbox" checked="checked" value="tak" name="mail" id="mail" /><label class="OpisFor" for="mail">Poinformuj klienta e-mail</label>
                    <br /><br />W jakim języku wysłać email: <?php echo Funkcje::RadioListaJezykow(); ?>                    
                </div>                
                
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
            <?php Listing::pokazAjax('klienci/punkty_do_zatwierdzenia.php', $zapytanie, $ile_licznika, $ile_pozycji, 'unique_id'); ?>
            </script>              

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
