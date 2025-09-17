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
    if (isset($_GET['szukaj']) && !empty($_GET['szukaj'])) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " and coupons_name like '%".$szukana_wartosc."%'";
        unset($szukana_wartosc);
    }
    
    if (isset($_GET['szukaj_opis']) && !empty($_GET['szukaj_opis'])) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_opis']);
        $warunki_szukania = " and coupons_description like '%".$szukana_wartosc."%'";
        unset($szukana_wartosc);
    }    
    
    // jezeli jest opcja
    if (isset($_GET['rodzaj_opcja']) && !empty($_GET['rodzaj_opcja'])) {
        switch ($filtr->process($_GET['rodzaj_opcja'])) {
            case "kwota":
                $warunki_szukania .= " and coupons_discount_type = 'fixed'";
                break;
            case "procent":
                $warunki_szukania .= " and coupons_discount_type = 'percent'";
                break;
            case "wysylka":
                $warunki_szukania .= " and coupons_discount_type = 'shipping'";
                break;                 
        }     
    }  
    
    // jezeli jest opcja
    if (isset($_GET['status_opcja']) && !empty($_GET['status_opcja'])) {
        switch ($filtr->process($_GET['status_opcja'])) {
            case "aktywne":
                $warunki_szukania .= " and coupons_status = '1'";
                break;
            case "uzyte":
                $warunki_szukania .= " and coupons_status = '0'";
                break;             
        }     
    }    

    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }
    
    $zapytanie = "select * from coupons" . $warunki_szukania;
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
                $sortowanie = 'coupons_name asc';
                break;
            case "sort_a2":
                $sortowanie = 'coupons_name desc';
                break;                 
            case "sort_a3":
                $sortowanie = 'coupons_discount_type';
                break;
            case "sort_a4":
                $sortowanie = 'coupons_date_start';
                break;
            case "sort_a5":
                $sortowanie = 'coupons_date_end';
                break; 
            case "sort_a6":
                $sortowanie = 'coupons_date_added desc';
                break;
            case "sort_a7":
                $sortowanie = 'coupons_date_added asc';
                break;                         
        }            
    } else { $sortowanie = 'coupons_name'; }

    $zapytanie .= " ORDER BY ".$sortowanie;    

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr']; 

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array();
            $tablica_naglowek[] = array('Info','center', '', 'class="ListingSchowaj"');
            $tablica_naglowek[] = array('Akcja','center');
            $tablica_naglowek[] = array('ID','center');
            $tablica_naglowek[] = array('Kod','center');  
            $tablica_naglowek[] = array('Opis', '', '', 'class="ListingRwd"');  
            $tablica_naglowek[] = array('Rodzaj','center');
            $tablica_naglowek[] = array('Zakres','center');
            $tablica_naglowek[] = array('Zniżka','center','white-space:nowrap');  
            $tablica_naglowek[] = array('Data utworzenia','center');
            $tablica_naglowek[] = array('Ważność od','center');
            $tablica_naglowek[] = array('Ważność do','center');            
            $tablica_naglowek[] = array('Wykorzystane','center', '', 'class="ListingRwd"');           
            $tablica_naglowek[] = array('Status','center');
            
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['coupons_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['coupons_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['coupons_id'].'">';
                  }       
                  
                  $tablica = array();
                  
                  $tablica[] = array('<div id="kupon_'.$info['coupons_id'].'" class="zmzoom_kupon"><div class="podglad_zoom"></div><img src="obrazki/info_duze.png" alt="Info" /></div>','','width:30px', 'class="ListingSchowaj"');
                  
                  $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" value="'.$info['coupons_id'].'" id="opcja_'.$info['coupons_id'].'" /><label class="OpisForPustyLabel" for="opcja_'.$info['coupons_id'].'"></label><input type="hidden" name="id[]" value="'.$info['coupons_id'].'" />','center');
                  
                  $tablica[] = array($info['coupons_id'],'center');                  
                  $tablica[] = array($info['coupons_name'],'center');
                  $tablica[] = array('<span class="OpisKuponu">Opis:</span>' . (($info['coupons_pp_id'] > 0) ? '<a href="klienci/klienci_edytuj.php?id_poz=' . $info['coupons_pp_id'] . '">' . $info['coupons_description'] . '</a>' : $info['coupons_description']), '', '', 'class="ListingRwd"');
                  
                  // rodzaj znizki
                  if ($info['coupons_discount_type'] == 'fixed') {
                      $tablica[] = array('kwota','center');
                  }
                  if ($info['coupons_discount_type'] == 'percent') {
                      $tablica[] = array('procent','center');
                  }  
                  if ($info['coupons_discount_type'] == 'shipping') {
                      $tablica[] = array('darmowa wysyłka','center');
                  }
                  
                  if ($info['coupons_exclusion'] == '') {
                      $tablica[] = array('ogólny','center');
                  } else {
                      $tablica[] = array(str_replace('_',', ',(string)$info['coupons_exclusion']),'center');
                  }
                  // wartosc znizki
                  if ($info['coupons_discount_type'] == 'fixed') {
                      //
                      $sym = $_SESSION['domyslna_waluta']['symbol'];
                      //
                      if ( $info['coupons_currency'] != '' ) {
                           foreach ($waluty->waluty_id as $wal) {
                              if ( $wal['code'] == $info['coupons_currency'] ) {
                                   $sym = $wal['symbol'];
                              }
                           }
                      }
                      $tablica[] = array($info['coupons_discount_value'] . ' ' . $sym,'center','white-space:nowrap');
                      unset($sym);
                      //
                  }
                  if ($info['coupons_discount_type'] == 'percent') {
                      $tablica[] = array($info['coupons_discount_value'] . ' %','center','white-space:nowrap');
                  }
                  if ($info['coupons_discount_type'] == 'shipping') {
                      $tablica[] = array('-','center');
                  }                    
                  
                  $tablica[] = array(((Funkcje::czyNiePuste($info['coupons_date_added'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['coupons_date_added'])) : '-'),'center','white-space:nowrap'); 

                  $tablica[] = array(((Funkcje::czyNiePuste($info['coupons_date_start'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['coupons_date_start'])) : '-'),'center','white-space:nowrap'); 
                  
                  $tablica[] = array(((Funkcje::czyNiePuste($info['coupons_date_end'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['coupons_date_end'])) : '-'),'center','white-space:nowrap'); 

                  // wykorzystany czy nie
                  $wykorzystanie = $db->open_query("select distinct count(*) as ile_wykorzystanych from coupons_to_orders where coupons_id = '".(int)$info['coupons_id']."'");
                  $ile_kuponow = $wykorzystanie->fetch_assoc();
                  
                  $tablica[] = array($ile_kuponow['ile_wykorzystanych'],'center', '', 'class="ListingRwd"');
                  
                  $db->close_query($wykorzystanie);
                  unset($ile_kuponow);   
                  
                  if ($info['coupons_status'] == '1') {
                      $obraz = 'aktywny_on.png'; $alt = 'Kupon jest aktywny';
                    } else {
                      $obraz = 'aktywny_off.png'; $alt = 'Wszystkie kupony zostały wykorzystane';
                  }
                  $tablica[] = array('<em class="TipChmurka"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></em>','center');  

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">'; 
                  
                  $zmienne_do_przekazania_statystyka = '?id_poz='.$info['coupons_id'].'&amp;kod_kuponu='.$info['coupons_name']; 
                  $zmienne_do_przekazania = '?id_poz='.$info['coupons_id']; 
                  
                  $tekst .= '<a class="TipChmurka" href="kupony/kupony_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="kupony/kupony_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="kupony/kupony_statystyka.php'.$zmienne_do_przekazania_statystyka.'"><b>Statystyki użycia</b><img src="obrazki/statystyka.png" alt="Statystyki użycia" /></a>';
                  
                  $tekst .= '</td></tr>';
                  
                  $tekst .= '<tr class="pozycjaRwd"><td class="WynikRwd" colspan="14" id="rwd_sk_'.$info['coupons_id'].'"></td></tr>';
                  
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
            
            <div id="naglowek_cont">Kupony rabatowe</div>  

            <div id="wyszukaj">
                <form action="kupony/kupony.php" method="post" id="kuponyForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj kupon:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? $filtr->process($_GET['szukaj']) : ''); ?>" size="30" />
                </div>  
                
                <div id="wyszukaj_text">
                    <span>Wyszukaj w opisach:</span>
                    <input type="text" name="szukaj_opis" id="szukaj_opis" value="<?php echo ((isset($_GET['szukaj_opis'])) ? $filtr->process($_GET['szukaj_opis']) : ''); ?>" size="20" />
                </div>                  
                
                <div class="wyszukaj_select">
                    <span>Rodzaj:</span>
                    <?php
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- wszystkie --');
                    $tablica[] = array('id' => 'kwota', 'text' => 'kwota');
                    $tablica[] = array('id' => 'procent', 'text' => 'procent');
                    $tablica[] = array('id' => 'wysylka', 'text' => 'darmowa wysyłka');
                    ?>                                          
                    <?php echo Funkcje::RozwijaneMenu('rodzaj_opcja', $tablica, ((isset($_GET['rodzaj_opcja'])) ? $filtr->process($_GET['rodzaj_opcja']) : '')); ?>
                </div> 

                <div class="wyszukaj_select">
                    <span>Status:</span>
                    <?php
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- wszystkie --');
                    $tablica[] = array('id' => 'aktywne', 'text' => 'tylko aktywne');
                    $tablica[] = array('id' => 'uzyte', 'text' => 'tylko wykorzystane (nieaktywne)');
                    ?>                                          
                    <?php echo Funkcje::RozwijaneMenu('status_opcja', $tablica, ((isset($_GET['status_opcja'])) ? $filtr->process($_GET['status_opcja']) : '')); ?>
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
                  echo '<div id="wyszukaj_ikona"><a href="kupony/kupony.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?> 

                <div style="clear:both"></div>
            </div>        
            
            <form action="kupony/kupony_akcja.php" method="post" class="cmxform">
            
            <div id="sortowanie">
            
                <span>Sortowanie:</span>
                
                <a id="sort_a1" class="sortowanie" href="kupony/kupony.php?sort=sort_a1">kody rosnąco</a>
                <a id="sort_a2" class="sortowanie" href="kupony/kupony.php?sort=sort_a2">kody malejąco</a>
                <a id="sort_a3" class="sortowanie" href="kupony/kupony.php?sort=sort_a3">wg rodzaju</a>
                <a id="sort_a4" class="sortowanie" href="kupony/kupony.php?sort=sort_a4">data ważności od</a>
                <a id="sort_a5" class="sortowanie" href="kupony/kupony.php?sort=sort_a5">data ważności do</a>
                <a id="sort_a6" class="sortowanie" href="kupony/kupony.php?sort=sort_a6">data utworzenia malejąco</a>
                <a id="sort_a7" class="sortowanie" href="kupony/kupony.php?sort=sort_a7">data utworzenia rosnąco</a>
            
            </div>               

            <div id="PozycjeIkon">
            
                <div class="lf">
                    <a class="dodaj" href="kupony/kupony_dodaj.php">dodaj nowy kupon</a>
                    <a class="dodaj" href="kupony/kupony_dodaj_seria.php">dodaj serię kuponów z prefixem</a>
                    <a class="dodaj" href="kupony/kupony_dodaj_seria_losowa.php">dodaj serię kuponów losowych</a>
                    
                    <br />
                    
                    <a class="dodaj" id="KuponyNaMail" href="kupony/kupony_dodaj_seria_losowa_mail.php">dodaj serię kuponów losowych i wyślij na maila</a>
                    
                </div>
                                
                <div class="rg">  
                
                    <?php if ($ile_pozycji > 0) { ?>
                    <a class="Export" href="kupony/kupony_export.php">eksportuj dane do pliku</a>
                    <?php } ?>
                    
                    <a class="Import" href="kupony/kupony_import.php">importuj dane</a>
                    
                </div>

            </div>
            
            <div style="clear:both;"></div>   
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            
            <script>
            $(document).ready(function() {
                $("#akcja_dolna").change( function () {
                    var va = $("#akcja_dolna").val();
                    if (va == '4') {
                        $("#wart").css('display','block');
                       } else {
                        $("#wart").css('display','none');
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
                
                <div id="wart" style="display:none">
                    Prefix: <input type="text" name="profix" size="6" value="" />
                </div>                
   
                <div id="akc">
                    Wykonaj akcje: 
                    <select name="akcja_dolna" id="akcja_dolna">
                        <option value="0"></option>
                        <option value="1">usuń zaznaczone kupony</option>
                        <option value="2">usuń wszystkie kupony</option>
                        <option value="3">usuń wszystkie nieaktywne kupony</option>
                        <option value="4">usuń wszystkie z prefixem</option>
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
            <?php Listing::pokazAjax('kupony/kupony.php', $zapytanie, $ile_licznika, $ile_pozycji, 'coupons_id'); ?>
            </script>              

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
