<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if ( isset($_GET['zakladka']) ) unset($_GET['zakladka']);
if ( isset($_SESSION['waluta_zwroty']) ) unset($_SESSION['waluta_zwroty']);

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

// wyswietla tylko nowe zamowienia
if ( isset($_GET['nowe']) ) {
     //
     unset($_SESSION['filtry']['zwroty.php']);
     //
     $_SESSION['filtry']['zwroty.php']['szukaj_status'] = Zwroty::PokazDomyslnyStatusZwrotu();
     //
     Funkcje::PrzekierowanieURL('zwroty.php');
}

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
    
    $warunki_szukania = '';

    if (isset($_GET['komentarze'])) {
      
        $id_zwrotu = array();
        
        $zapytanie_komentarze = "SELECT return_id, return_status_id FROM return_status_history WHERE return_customers_info = '1' AND return_customers_info_view = '0'";
        $sql_komentarze = $db->open_query($zapytanie_komentarze);    
        
        if ((int)$db->ile_rekordow($sql_komentarze) > 0) {
            //
            while ($infs = $sql_komentarze->fetch_assoc()) {
                  $id_zwrotu[$infs['return_id']] = $infs['return_id'];
            }
            //
        }
        unset($zapytanie_komentarz, $infs);  
        $db->close_query($sql_komentarze);   

        if ( count($id_zwrotu) > 0 ) {
             $warunki_szukania = " and cu.return_id IN (" . implode(',', (array)$id_zwrotu) . ")";
             $_SESSION['filtry']['zwroty.php']['komentarze'] = 'tak';
        } else {
             unset($_SESSION['filtry']['zwroty.php']['komentarze']);
        }
        
        unset($id_zwrotu);

    }

    // jezeli jest szukanie
    if (isset($_GET['nr_zwrotu']) && $_GET['nr_zwrotu'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['nr_zwrotu']);
        $warunki_szukania .= " and cu.return_rand_id LIKE '%".$szukana_wartosc."%'";
    }
    
    if (isset($_GET['szukaj']) && $_GET['szukaj'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania .= " and (c.customers_firstname LIKE '%".$szukana_wartosc."%' OR c.customers_lastname LIKE '%".$szukana_wartosc."%' OR c.customers_email_address LIKE '%".$szukana_wartosc."%')";
    }
    
    if ( isset($_GET['szukaj_data_od']) && $_GET['szukaj_data_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_od'] . ' 00:00:00')));
        $warunki_szukania .= " and cu.return_date_created >= '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_data_do']) && $_GET['szukaj_data_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_do'] . ' 23:59:59')));
        $warunki_szukania .= " and cu.return_date_created <= '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_status']) && $_GET['szukaj_status'] != '0' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_status']);
        $warunki_szukania .= " and cu.return_status_id = '".$szukana_wartosc."'";
    }

    if ( isset($_GET['opiekun']) && $_GET['opiekun'] > 0 ) {
        $szukana_wartosc = $filtr->process($_GET['opiekun']);
        $warunki_szukania .= " and cu.return_service = '".$szukana_wartosc."'";
    }    

    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }
    
    // statusy zwrotu - tylko zamkniete
    $zapytanie_zwrot = "SELECT return_status_id FROM return_status WHERE return_status_type = 3 or return_status_type = 4";
    $sql_zwrot = $db->open_query($zapytanie_zwrot);

    $statusy_zwrotu = array(99);
    
    while ($id_statusu = $sql_zwrot->fetch_assoc()) {
         //
         $statusy_zwrotu[] = $id_statusu['return_status_id'];
         //
    }
    $db->close_query($sql_zwrot);
    unset($zapytanie_zwrot);        

    $zapytanie = "SELECT * FROM return_list cu
                    LEFT JOIN customers c ON cu.return_customers_id = c.customers_id
                    LEFT JOIN address_book a ON c.customers_id = a.customers_id and c.customers_default_address_id = a.address_book_id " . $warunki_szukania;

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
                $sortowanie = 'cu.return_date_created desc';
                break;
            case "sort_a2":
                $sortowanie = 'cu.return_date_created asc';
                break;    
            case "sort_a3":
                $sortowanie = 'cu.return_date_created desc';
                break;
            case "sort_a4":
                $sortowanie = 'cu.return_date_created asc';
                break;                  
            case "sort_a7":
                $sortowanie = 'cu.return_customers_orders_id desc';
                break;
            case "sort_a8":
                $sortowanie = 'cu.return_customers_orders_id asc';
                break; 
            case "sort_a9":
                $sortowanie = 'cu.return_date_end desc';
                break;   
            case "sort_a10":
                $sortowanie = 'cu.return_date_end asc';
                break;                   
        }            
    } else { $sortowanie = 'cu.return_date_created desc'; }    
    
    $zapytanie .= " ORDER BY ".$sortowanie;    
    
    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        // opiekun zamowienia - tablica
        $tablica_opiekunow = array();
        //
        $zapytanie_tmp = "select distinct * from admin";
        $sqls = $db->open_query($zapytanie_tmp);
        //
        if ((int)$db->ile_rekordow($sqls) > 0) {
            //
            while ($infs = $sqls->fetch_assoc()) {
                  $tablica_opiekunow[ $infs['admin_id'] ] = $infs['admin_firstname'] . ' ' . $infs['admin_lastname'];
            }
            //
        }
        unset($zapytanie_tmp, $infs);  
        $db->close_query($sqls);
        //   
        
        if ($ile_pozycji > 0) {

            $zapytanie .= " limit ".$_GET['parametr'];    

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID', 'center'),
                                      array('Akcja', 'center'),
                                      array('Nr zgłoszenia', 'center'),
                                      array('Klient', 'center'),
                                      array('Produkty do zwrotu', '', '', 'class="ListingSchowaj"'),
                                      array('Data zgłoszenia', 'center'),
                                      array('Data ostatniej zmiany statusu', 'center','','class="ListingSchowajMobile"'),
                                      array('Data rozpatrzenia', 'center'),
                                      array('Nr zamówienia', 'center'),
                                      array('Status', 'center'));

            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['return_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['return_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['return_id'].'">';
                  }        

                  $tablica = array();
                  
                  // sprawdzi czy nie ma informacji od klienta - nieprzyczytanej
                  $info_nieprzeczytane = '';
                  //
                  $zapytanie_info_klient = "SELECT return_status_id FROM return_status_history WHERE return_customers_info = '1' AND return_customers_info_view = '0' AND return_id = '" . (int)$info['return_id'] . "' ORDER BY date_added LIMIT 1";
                  $sql_info_klient = $db->open_query($zapytanie_info_klient);
                  //
                  if ((int)$db->ile_rekordow($sql_info_klient) > 0) {
                      $info_nieprzeczytane = '<div style="margin-bottom:10px"><a class="TipChmurka" href="zwroty/zwroty_szczegoly.php?id_poz=' . (int)$info['return_id'] . '&zakladka=1"><b>Nieprzeczytane informacje od klienta</b><img src="obrazki/ochrona.png" alt="Informacje" /></a></div>';
                  }
                  
                  $db->close_query($sql_info_klient);

                  $tablica[] = array($info['return_id'],'center');
                  
                  $tablica[] = array('<input type="checkbox" name="opcja[]" id="opcja_'.$info['return_id'].'" value="'.$info['return_id'].'" /><label class="OpisForPustyLabel" for="opcja_'.$info['return_id'].'"></label><input type="hidden" name="id[]" value="'.$info['return_id'].'" />','center');
                  
                  $tablica[] = array($info_nieprzeczytane . $info['return_rand_id'],'center');
                  
                  unset($info_nieprzeczytane);
                  
                  $wyswietlana_nazwa = '';
                  //
                  if ($info['entry_company'] != '') {
                    $wyswietlana_nazwa .= '<span class="Firma">'.$info['entry_company'] . '</span>';
                  }
                  $wyswietlana_nazwa .= $info['entry_firstname'] . ' ' . $info['entry_lastname'] . '<br />';
                  $wyswietlana_nazwa .= $info['entry_street_address']. '<br />';
                  $wyswietlana_nazwa .= $info['entry_postcode']. ' ' . $info['entry_city'];
                  //
                  // email
                  if (!empty($info['customers_email_address'])) {
                      $wyswietlana_nazwa .= '<span class="MalyMail ListingSchowaj">' . $info['customers_email_address'] . '</span>';
                  }
                  
                  $tablica[] = array($wyswietlana_nazwa,'','line-height:17px');
                  
                  $produkty_zwrot = '';
                                    
                  $zapytanie_produkty = "SELECT return_products_quantity, return_products_orders_id FROM return_products WHERE return_id = '" . $info['return_id'] . "'";
                  $sql_produkty = $db->open_query($zapytanie_produkty);    

                  if ((int)$db->ile_rekordow($sql_produkty) > 0) {
                      //
                      $symbol = '';
                      $zamowienie = new Zamowienie((int)$info['return_customers_orders_id']);
                      //
                      if ( count($zamowienie->info) > 0 ) {
                           //
                           $zapytanie_symbol = "select symbol from currencies where code = '" . $zamowienie->info['waluta'] . "'";
                           $sql_symbol = $db->open_query($zapytanie_symbol);
                           //
                           if ((int)$db->ile_rekordow($sql_symbol) > 0) {
                               //
                               $wynik = $sql_symbol->fetch_assoc();
                               $symbol = $wynik['symbol'];
                               unset($wynik);
                               //
                           }
                           //
                           $db->close_query($sql_symbol);                          
                           unset($zapytanie_symbol);
                           //
                           while ($infs = $sql_produkty->fetch_assoc()) {
                                 //
                                 $ilosc = $infs['return_products_quantity'];
                                 $nazwa_produktu = '<b>Brak nazwy</b>';
                                 $id_produktu = 0;
                                 //
                                 // sprawdzi czy calkowita wartosc
                                 foreach ( $zamowienie->produkty as $id_klucz => $prod ) {
                                     //
                                     if ( $id_klucz == $infs['return_products_orders_id'] ) {
                                          //
                                          if ( $prod['wartosc_calkowita'] == true ) {
                                               //
                                               $ilosc = (int)$infs['return_products_quantity'];  
                                               //
                                          }
                                          //
                                          $nazwa_produktu = '<b>' . $prod['nazwa'] . '</b>';
                                          //
                                          if ( isset($prod['model']) && $prod['model'] != '' ) {  
                                               //
                                               $nazwa_produktu .= '<div class="MaleNrKatalogowy">Nr kat: <b>' . $prod['model'] . '</b></div>';
                                               //
                                          } 
                                          //
                                          if ( isset($prod['attributes']) && (count($prod['attributes']) > 0) ) {  
                                               //
                                               $nazwa_produktu .= '<div class="ListaCechZwrotu">';
                                               foreach ($prod['attributes'] as $cecha ) {
                                                   $nazwa_produktu .= '<div>'.$prod['attributes'][$cecha['id_cechy']]['cecha'] . ': <b>' . $prod['attributes'][$cecha['id_cechy']]['wartosc'] . '</b></div>';
                                               }
                                               $nazwa_produktu .= '</div>';
                                          }                                       
                                          //
                                          $id_produktu = (int)$prod['id_produktu'];                                                          
                                          //
                                     }
                                     //
                                 }
                                 //                  
                                 $produkty_zwrot .= '<div class="ProduktZwrotu"><div>' . $ilosc . '&nbsp;x</div><div>';
                                 
                                 if ( $id_produktu > 0 ) {
                                      $produkty_zwrot .= '<a href="produkty/produkty_edytuj.php?id_poz=' . $id_produktu . '">' . $nazwa_produktu . '</a>';
                                 } else {
                                      $produkty_zwrot .= $nazwa_produktu;
                                 }                                    
                                 
                                 $produkty_zwrot .= '</div></div>';
                                 //
                                 unset($ilosc, $nazwa_produktu, $id_produktu);
                                 //
                           }
                           //
                      }
                      //
                      $produkty_zwrot .= '<div class="SumaZwrotuListing">Zwrot: ' . number_format($info['return_value'], 2, ',', '') . ' ' . $symbol . '</div>';
                      //
                      unset($infs, $zamowienie, $symbol);
                      //
                  }
                  
                  unset($zapytanie_produkty);  
                  $db->close_query($sql_produkty);                    
                  
                  $tablica[] = array($produkty_zwrot, '', '', 'class="ListingSchowaj"');
                  
                  unset($produkty_zwrot);

                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['return_date_created'])),'center');
                  
                  if ( Funkcje::CzyNiePuste($info['return_date_modified']) ) {
                       $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['return_date_modified'])),'center','','class="ListingSchowajMobile"');
                    } else {
                       $tablica[] = array('-','center','','class="ListingSchowajMobile"');
                  }
                  
                  if ( Funkcje::CzyNiePuste($info['return_date_end']) && !in_array((string)$info['return_status_id'], $statusy_zwrotu) ) {
                       $tgm = date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['return_date_end']));
                       //
                       $temu = '';
                       $sek = (FunkcjeWlasnePHP::my_strtotime($info['return_date_end']) - time());
                       if ( $sek > 0 ) {
                           if ( $sek < 3660 ) {
                                $temu = $sek . ' min. ';
                                $css = 'style="color:#ff0000"';                            
                           }
                           if ( $sek < 86400 ) {
                                $temu = date('G',$sek) . ' godz. ' . date('i',$sek) . ' min. ';
                                $css = 'style="color:#ff0000"';
                           }
                           if ( $sek > 86400 ) {
                                $temu = (date('j',$sek) - 1) . ' dni ';
                                $css = 'style="color:#248c0c"';
                           }
                       } else {
                           $temu = 'PRZETERMINOWANY';
                           $css = 'style="color:#ff0000"';
                       }
                       if ( $temu != '' ) {
                            $tgm .= '<div class="Pozostalo">Pozostało: <b ' . $css . '>' . $temu . '</b></div>';
                       }                   
                       //
                       $tablica[] = array($tgm,'center');
                       unset($tgm);
                    } else {
                       $tablica[] = array('-','center');
                  }                  

                  $tablica[] = array('<a href="sprzedaz/zamowienia_szczegoly.php?id_poz=' . $info['return_customers_orders_id'] . '">' . $info['return_customers_orders_id'] . '</a>','center');
                  
                  // opiekun zamowienia
                  if (isset($tablica_opiekunow[(int)$info['return_service']])) {
                      $opiekun = '<span class="Opiekun">Opiekun:<span>' . $tablica_opiekunow[(int)$info['return_service']] . '</span></span>';
                     } else {
                      $opiekun = '';
                  }   
                              
                  $tablica[] = array(Zwroty::pokazNazweStatusuzwrotu($info['return_status_id'], $_SESSION['domyslny_jezyk']['id']) . $opiekun, 'center');

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['return_id']; 
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $tekst .= '<a class="TipChmurka" href="zwroty/zwroty_zwrot_pdf.php'.$zmienne_do_przekazania.'"><b>Wydruk zwrotu</b><img src="obrazki/pdf_2.png" alt="Wydruk zwrotu" /></a>';
                  $tekst .= '<a class="TipChmurka" href="zwroty/zwroty_szczegoly.php'.$zmienne_do_przekazania.((isset($_GET['komentarze'])) ? '&zakladka=1' : '').'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="zwroty/zwroty_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  
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
            $('input.datepicker').Zebra_DatePicker({
              format: 'd-m-Y',
              inside: false,
              readonly_element: false
            });                
            
            $('#akcja_dolna').change(function() {
               $('#potwierdzenie_usuniecia').hide();
               if ( this.value == '1' ) { 
                    $('#potwierdzenie_usuniecia').show();                  
               }               
            });            
        });
        </script>

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Zwroty</div>

            <div id="wyszukaj">
                <form action="zwroty/zwroty.php" method="post" id="poForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Nr zwrotu:</span>
                    <input type="text" name="nr_zwrotu" id="nr_zwrotu" value="<?php echo ((isset($_GET['nr_zwrotu'])) ? $filtr->process($_GET['nr_zwrotu']) : ''); ?>" size="30" />
                </div>                  
                
                <div id="wyszukaj_text">
                    <span>Wyszukaj wg klienta:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? $filtr->process($_GET['szukaj']) : ''); ?>" size="30" />
                </div>  
                
                <div class="wyszukaj_select">
                    <span>Data zgłoszenia:</span>
                    <input type="text" id="data_zwroty_od" name="szukaj_data_od" value="<?php echo ((isset($_GET['szukaj_data_od'])) ? $filtr->process($_GET['szukaj_data_od']) : ''); ?>" size="12" class="datepicker" />&nbsp;do&nbsp;
                    <input type="text" id="data_zwroty_do" name="szukaj_data_do" value="<?php echo ((isset($_GET['szukaj_data_do'])) ? $filtr->process($_GET['szukaj_data_do']) : ''); ?>" size="12" class="datepicker" />
                </div>

                <div class="wyszukaj_select">
                    <span>Status:</span>
                    <?php
                    $tablia_status = Array();
                    $tablia_status = Zwroty::ListaStatusowZwrotow(true);
                    echo Funkcje::RozwijaneMenu('szukaj_status', $tablia_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : ''), ' style="max-width:250px"'); ?>
                </div>

                <div class="wyszukaj_select">
                    <span>Opiekun:</span>
                    <?php
                    // pobieranie informacji od uzytkownikach
                    $zapytanie_tmp = "select distinct * from admin order by admin_lastname, admin_firstname";
                    $sqls = $db->open_query($zapytanie_tmp);
                    //
                    $tablica_user = array();
                    $tablica_user[] = array('id' => 0, 'text' => 'dowolny');
                    while ($infs = $sqls->fetch_assoc()) { 
                           $tablica_user[] = array('id' => $infs['admin_id'], 'text' => $infs['admin_firstname'] . ' ' . $infs['admin_lastname']);
                    }
                    $db->close_query($sqls); 
                    unset($zapytanie_tmp, $infs);    
                    //
                    echo Funkcje::RozwijaneMenu('opiekun', $tablica_user, ((isset($_GET['opiekun'])) ? $filtr->process($_GET['opiekun']) : ''), ' style="width:150px"'); ?>
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
                  echo '<div id="wyszukaj_ikona"><a href="zwroty/zwroty.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
            </div>        
            
            <form action="zwroty/zwroty_akcja.php" method="post" class="cmxform">

            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="zwroty/zwroty.php?sort=sort_a1">data zgłoszenia malejąco</a>
                <a id="sort_a2" class="sortowanie" href="zwroty/zwroty.php?sort=sort_a2">data zgłoszenia rosnąco</a>
                <a id="sort_a3" class="sortowanie" href="zwroty/zwroty.php?sort=sort_a3">data zmiany statusu malejąco</a>
                <a id="sort_a4" class="sortowanie" href="zwroty/zwroty.php?sort=sort_a4">data zmiany statusu rosnąco</a>                
                <a id="sort_a9" class="sortowanie" href="zwroty/zwroty.php?sort=sort_a9">data rozpatrzenia malejąco</a>
                <a id="sort_a10" class="sortowanie" href="zwroty/zwroty.php?sort=sort_a10">data rozpatrzenia rosnąco</a>                 
                <a id="sort_a7" class="sortowanie" href="zwroty/zwroty.php?sort=sort_a7">nr zamówienia malejąco</a>
                <a id="sort_a8" class="sortowanie" href="zwroty/zwroty.php?sort=sort_a8">nr zamówienia rosnąco</a> 
            
            </div>             

              <div id="PozycjeIkon">
              
                  <div>
                      <a class="dodaj" href="zwroty/zwroty_dodaj.php<?php echo ( isset($_GET['klient_id']) ? '?klient_id='.(int)$_GET['klient_id'] : ''); ?>">dodaj nowy zwrot</a>
                  </div>            
                  
                  <?php
                  $zapytanie_komentarze = "SELECT return_id, return_status_id FROM return_status_history WHERE return_customers_info = '1' AND return_customers_info_view = '0'";
                  $sql_komentarze = $db->open_query($zapytanie_komentarze);
                  //
                  if ((int)$db->ile_rekordow($sql_komentarze) > 0) {
                      echo '<div><a class="miganie KomentarzeZwroty" href="zwroty/zwroty.php?komentarze=tak">nieprzeczytane informacje od klientów</a></div>';
                      ?>
                      <script>
                      $(document).ready(function() {
                          $('.miganie').each(function() {
                              var elem = $(this);
                              setInterval(function() {
                                  elem.fadeTo('fast', 0, function() {
                                      elem.fadeTo('slow', 1)
                                  });
                              }, 1000);
                          }); 
                      });            
                      </script>                      
                      <?php
                  }                 
                  ?>
                  
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
                    <option value="1">usuń wybrane zwroty</option>
                  </select>
                </div>
                
                <div class="cl"></div>
              
            </div>
            
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <?php if ($ile_pozycji > 0) { ?>

                <div id="potwierdzenie_usuniecia" style="padding-bottom:10px;display:none">
                
                    <div class="RamkaAkcji" style="display:block;padding:15px">
                
                        <div class="rg">
                          
                            <p style="padding-right:0px">
                                <label style="width:auto">Czy na pewno chcesz usunąć wybrane zwroty ?</label>
                                <input type="radio" value="0" name="usuniecie_zwrotu" id="usuniecie_zwrotu_nie" checked="checked" /><label class="OpisFor" for="usuniecie_zwrotu_nie">nie</label>
                                <input type="radio" value="1" name="usuniecie_zwrotu" id="usuniecie_zwrotu_tak" /><label class="OpisFor" style="padding-right:0px !important" for="usuniecie_zwrotu_tak">tak</label>
                            </p>
                            
                        </div>
                        
                        <div class="cl"></div>
                        
                        <div class="ostrzezenie rg">Operacja usunięcia jest nieodracalna ! zwrotów po usunięciu nie będzie można przywrócić !</div>
                        
                        <div class="cl"></div>
                   
                    </div>
                    
                </div>

                <div class="rg"><input type="submit" class="przyciskBut" value="Zapisz zmiany" /></div>

            <?php } ?> 
            
            <div class="cl"></div>
            
            </form>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('zwroty/zwroty.php', $zapytanie, $ile_licznika, $ile_pozycji, 'return_id'); ?>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
