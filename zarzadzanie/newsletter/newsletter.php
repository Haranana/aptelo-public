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
        $warunki_szukania = " and (title like '%".$szukana_wartosc."%')";
    }
    
    // jezeli jest opcja
    if (isset($_GET['opcja']) && !empty($_GET['opcja']) && (int)$_GET['opcja'] > 0) {
        $warunki_szukania .= " and destination = '".(int)$_GET['opcja']."'";
    }      
    
    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }    

    $zapytanie = "select distinct * from newsletters ".$warunki_szukania;
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
                $sortowanie = 'title asc';
                break; 
            case "sort_a2":
                $sortowanie = 'title desc';
                break;
            case "sort_a3":
                $sortowanie = 'date_added asc';
                break; 
            case "sort_a4":
                $sortowanie = 'date_added desc';
                break; 
            case "sort_a5":
                $sortowanie = 'date_sent asc';
                break; 
            case "sort_a6":
                $sortowanie = 'date_sent desc';
                break;                        
        }            
    } else { $sortowanie = 'title asc'; }    
    
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
                                      array('Tytuł'),
                                      array('Odbiorcy newslettera', '', '', 'class="ListingRwd"'),
                                      array('Data dodania', 'center', 'white-space: nowrap'),
                                      array('Dodatkowe warunki', '', 'white-space: nowrap'), 
                                      array('Ilość maili', 'center', 'white-space: nowrap'),
                                      array('Data wysłania', 'center', 'white-space: nowrap'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $TabTmpGrup = array();
            $TablicaGrup = Newsletter::GrupyNewslettera();
            //
            foreach ($TablicaGrup as $Grupa) {
                $TabTmpGrup[$Grupa['id']] = $Grupa['text'];
            }
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['newsletters_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['newsletters_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['newsletters_id'].'">';
                  }       

                  $tablica = array();
                  
                  $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" value="'.$info['newsletters_id'].'" id="opcja_'.$info['newsletters_id'].'" /><label class="OpisForPustyLabel" for="opcja_'.$info['newsletters_id'].'"></label><input type="hidden" name="id[]" value="'.$info['newsletters_id'].'" />','center');
                  
                  $tablica[] = array($info['newsletters_id'],'center');
                  
                  $tablica[] = array($info['title']);
                  
                  switch ($info['destination']) {
                    case "1":
                        $doKogo = 'do wszystkich zarejestrowanych klientów sklepu';
                        break; 
                    case "2":
                        $doKogo = 'tylko zarejestrowani klienci którzy wyrazili zgodę na newsletter';
                        break;                          
                    case "3":
                        $doKogo = 'tylko klienci którzy zapisali się do newslettera, a nie są klientami sklepu';
                        break;
                    case "4":
                        $doKogo = 'do wszystkich którzy zapisali się do newslettera';
                        break;                        
                    case "5":
                        $doKogo = 'mailing';
                        break;     
                    case "6":
                        $doKogo = 'tylko do określonej grupy klientów';
                        break;                        
                    case "7":
                        $doKogo = 'tylko zarejestrowani klienci z porzuconymi koszykami';
                        break; 
                    case "8":
                        $doKogo = 'tylko klienci bez rejestracji z porzuconymi koszykami';
                        break;     
                    case "9":
                        $doKogo = 'wszyscy klienci z porzuconymi koszykami (z kontem oraz bez rejestracji)';
                        break;                         
                  }                   
                  
                  $tablica[] = array($doKogo, '', '', 'class="ListingRwd"');
                  unset($doKogo);
                  
                  $tablica[] = array(((Funkcje::czyNiePuste($info['date_added'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['date_added'])) : '-'),'center');
                  
                  $dodatkoweWarunki = '';
                  if ((int)$info['destination'] == 1 || (int)$info['destination'] == 2 || (int)$info['destination'] == 6) {
                      //
                      // jezeli sa wypelnione daty zamowienia 
                      if (Funkcje::czyNiePuste($info['order_date_start']) || Funkcje::czyNiePuste($info['order_date_end'])) {
                          //
                          $dodatkoweWarunki .= 'Data zamówienia ';
                          //
                          if (Funkcje::czyNiePuste($info['order_date_start'])) {
                              $dodatkoweWarunki .= ' od <b>' . date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['order_date_start'])) . '</b><br />';
                          }
                          if (Funkcje::czyNiePuste($info['order_date_end'])) {
                              $dodatkoweWarunki .= ' do <b>' . date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['order_date_end'])) . '</b><br />';
                          } 
                          //
                      }                     
                      // jezeli jest status zamowienia
                      if (Funkcje::czyNiePuste($info['order_status'])) {
                          $dodatkoweWarunki .= 'Status zamówienia <b>' . strip_tags(Sprzedaz::pokazNazweStatusuZamowienia($info['order_status'], $_SESSION['domyslny_jezyk']['id'])) . '</b><br />';
                      }  
                      // jezeli sa wypelnione kwoty zamowienia
                      if (Funkcje::czyNiePuste($info['order_min']) || Funkcje::czyNiePuste($info['order_max'])) {
                          //
                          $dodatkoweWarunki .= 'Wartość zamówienia ';
                          //
                          if (Funkcje::czyNiePuste($info['order_min'])) {
                              $dodatkoweWarunki .= ' od <b>' . $info['order_min'] . '</b><br />';
                          }
                          if (Funkcje::czyNiePuste($info['order_max'])) {
                              $dodatkoweWarunki .= ' do <b>' . $info['order_max'] . '</b><br />';
                          } 
                          //
                      }
                      // jezeli jest grupa klientow
                      if (Funkcje::czyNiePuste($info['customers_group_id'])) {
                          $dodatkoweWarunki .= 'Tylko dla grupy klientów: <b>' . Klienci::pokazNazweGrupyKlientow($info['customers_group_id']) . '</b><br />';
                      }             

                      if ( (int)$info['destination'] == 2 && !empty($info['customers_newsletter_group']) ) {
                           //                           
                           $dodatkoweWarunkiGrupy = '';
                           //
                           $Grupy = explode(',', (string)$info['customers_newsletter_group']);
                           foreach ( $Grupy as $Grupa ) {
                                if ( isset($TabTmpGrup[(int)$Grupa]) ) {
                                     $dodatkoweWarunkiGrupy .= $TabTmpGrup[(int)$Grupa] . ', ';
                                }
                           }
                           unset($Grupy);
                           //
                           if ( $dodatkoweWarunkiGrupy != '' ) {
                                //
                                $dodatkoweWarunki .= 'Tylko dla grupy newslettera: <b>' . substr((string)$dodatkoweWarunkiGrupy, 0, -2) . ' </b><br />';
                                //
                           }
                           unset($dodatkoweWarunkiGrupy);
                           
                      }
                      
                  }
                  if ((int)$info['destination'] == 7 || (int)$info['destination'] == 8 || (int)$info['destination'] == 9) {
                      //                  
                      // jezeli sa wypelnione data porzucenia koszyka
                      if (Funkcje::czyNiePuste($info['basket_date_start']) || Funkcje::czyNiePuste($info['basket_date_end'])) {
                          //
                          $dodatkoweWarunki .= 'Data porzucenia koszyka ';
                          //
                          if (Funkcje::czyNiePuste($info['basket_date_start'])) {
                              $dodatkoweWarunki .= ' od <b>' . date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['basket_date_start'])) . '</b><br />';
                          }
                          if (Funkcje::czyNiePuste($info['basket_date_end'])) {
                              $dodatkoweWarunki .= ' do <b>' . date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['basket_date_end'])) . '</b><br />';
                          } 
                          //
                      }   
                      //
                  }
                  if ((int)$info['destination'] == 3) {
                      //
                      // jezeli jest wypelniona data aktywacji
                      if (Funkcje::czyNiePuste($info['activation'])) {
                          //
                          $dodatkoweWarunki .= 'Data aktywacji od: <b>' . date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['activation'])) . '</b>';
                          //
                      }
                      //
                  }
                  $tablica[] = array($dodatkoweWarunki,'', 'line-height:1.8');
                  
                  $iloscAdresow = count(Newsletter::AdresyEmailNewslettera($info['newsletters_id']));
                  $tablica[] = array($iloscAdresow,'center');
                  
                  $tablica[] = array(((Funkcje::czyNiePuste($info['date_sent'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['date_sent'])) : '-'),'center');
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['newsletters_id']; 
                  
                  $tekst .= '<a class="TipChmurka" href="newsletter/newsletter_wyslij.php'.$zmienne_do_przekazania.'&amp;test"><b>Wyślij testowy biuletyn</b><img src="obrazki/wyslij_mail_test.png" alt="Wyślij testowy biuletyn"/></a>';
                  if ( $iloscAdresow > 0 ) {
                      $tekst .= '<a class="TipChmurka" href="newsletter/newsletter_wyslij.php'.$zmienne_do_przekazania.'"><b>Wyślij</b><img src="obrazki/wyslij_mail.png" alt="Wyślij" /></a>';
                  }
                  $tekst .= '<a class="TipChmurka" href="newsletter/newsletter_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="newsletter/newsletter_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  
                  if ( INTEGRACJA_FRESHMAIL_WLACZONY == 'tak' && $info['destination'] != 5 ) {
                      $tekst .= '<a class="TipChmurka" href="newsletter/newsletter_freshmail_export.php'.$zmienne_do_przekazania.'"><b>Eksportuj adresy email tego newslettera do FreshMail</b><img src="obrazki/freshmail_logo.png" alt="Freshmail" /></a>';
                  }
                  
                  if ( INTEGRACJA_MAILERLITE_WLACZONY == 'tak' && $info['destination'] != 5 ) {
                      $tekst .= '<a class="TipChmurka" href="newsletter/newsletter_mailerlite_export.php'.$zmienne_do_przekazania.'"><b>Eksportuj adresy email tego newslettera do MailerLite</b><img src="obrazki/mailerlite_logo.png" alt="MailerLite" /></a>';
                  }                  
                  
                  if ( INTEGRACJA_ECOMAIL_WLACZONY == 'tak' && $info['destination'] != 5 ) {
                      $tekst .= '<a class="TipChmurka" href="newsletter/newsletter_ecomail_export.php'.$zmienne_do_przekazania.'"><b>Eksportuj adresy email tego newslettera do Ecomail</b><img src="obrazki/ecomail_logo.png" alt="Ecomail" /></a>';
                  }                  
                  
                  if ( INTEGRACJA_MAILJET_WLACZONY == 'tak' && $info['destination'] != 5 ) {
                      $tekst .= '<a class="TipChmurka" href="newsletter/newsletter_mailjet_export.php'.$zmienne_do_przekazania.'"><b>Eksportuj adresy email tego newslettera do Mailjet</b><img src="obrazki/mailjet_logo.png" alt="Mailjet" /></a>';
                  }                  
                  
                  $tekst .= '</td></tr>';
                  
                  $tekst .= '<tr class="pozycjaRwd"><td class="WynikRwd" colspan="10" id="rwd_sk_'.$info['newsletters_id'].'"></td></tr>';
                  
            } 
            $tekst .= '</table>';
            //
            echo $tekst;
            //
            $db->close_query($sql);
            unset($iloscAdresow, $listing_danych,$tekst,$tablica,$tablica_naglowek, $TabTmpGrup, $TablicaGrup);        

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
            
            <div id="naglowek_cont">Newsletter</div>

            <div id="wyszukaj">
                <form action="newsletter/newsletter.php" method="post" id="poForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj tytuł:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? $filtr->process($_GET['szukaj']) : ''); ?>" size="40" />
                </div>  
                
                <div class="wyszukaj_select">
                    <span>Pokaż tylko:</span>
                    <?php
                    //                  
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- wszyscy --');
                    $tablica[] = array('id' => '1', 'text' => 'Do wszystkich zarejestrowanych klientów sklepu');
                    $tablica[] = array('id' => '2', 'text' => 'Tylko zarejestrowani klienci którzy wyrazili zgodę na newsletter');
                    $tablica[] = array('id' => '3', 'text' => 'Tylko klienci którzy zapisali się do newslettera, a nie są klientami sklepu');
                    $tablica[] = array('id' => '4', 'text' => 'Do wszystkich którzy zapisali się do newslettera');
                    $tablica[] = array('id' => '5', 'text' => 'Mailing');
                    $tablica[] = array('id' => '6', 'text' => 'Tylko do określonej grupy klientów');                    
                    ?>                                          
                    <?php echo Funkcje::RozwijaneMenu('opcja', $tablica, ((isset($_GET['opcja'])) ? $filtr->process($_GET['opcja']) : '')); ?>
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
                  echo '<div id="wyszukaj_ikona"><a href="newsletter/newsletter.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 

                <div style="clear:both"></div>
                
            </div>        
            
            <form action="newsletter/newsletter_akcja.php" method="post" class="cmxform">
            
            <div id="sortowanie">
            
                <span>Sortowanie:</span>
                
                <a id="sort_a1" class="sortowanie" href="newsletter/newsletter.php?sort=sort_a1">tytuł rosnąco</a>
                <a id="sort_a2" class="sortowanie" href="newsletter/newsletter.php?sort=sort_a2">tytuł malejąco</a> 
                <a id="sort_a3" class="sortowanie" href="newsletter/newsletter.php?sort=sort_a3">data dodania rosnąco</a>
                <a id="sort_a4" class="sortowanie" href="newsletter/newsletter.php?sort=sort_a4">data dodania malejąco</a>
                <a id="sort_a5" class="sortowanie" href="newsletter/newsletter.php?sort=sort_a5">data wysłania rosnąco</a>
                <a id="sort_a6" class="sortowanie" href="newsletter/newsletter.php?sort=sort_a6">data wysłania malejąco</a>
            
            </div>             

            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="newsletter/newsletter_dodaj.php">dodaj nowy newsletter</a>
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
            <?php Listing::pokazAjax('newsletter/newsletter.php', $zapytanie, $ile_licznika, $ile_pozycji, 'newsletters_id'); ?>
            </script>                
 
        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
