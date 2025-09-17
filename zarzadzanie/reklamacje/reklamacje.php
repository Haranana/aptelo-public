<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if ( isset($_GET['zakladka']) ) unset($_GET['zakladka']);
if ( isset($_SESSION['waluta_reklamacje']) ) unset($_SESSION['waluta_reklamacje']);

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

// wyswietla tylko nowe zamowienia
if ( isset($_GET['nowe']) ) {
     //
     unset($_SESSION['filtry']['reklamacje.php']);
     //
     $_SESSION['filtry']['reklamacje.php']['szukaj_status'] = Reklamacje::PokazDomyslnyStatusReklamacji();
     //
     Funkcje::PrzekierowanieURL('reklamacje.php');
}

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
    
    $warunki_szukania = '';

    if (isset($_GET['komentarze'])) {
      
        $id_reklamacji = array();
        
        $zapytanie_komentarze = "SELECT complaints_id, complaints_status_id FROM complaints_status_history WHERE complaints_customers_info = '1' AND complaints_customers_info_view = '0'";
        $sql_komentarze = $db->open_query($zapytanie_komentarze);    
        
        if ((int)$db->ile_rekordow($sql_komentarze) > 0) {
            //
            while ($infs = $sql_komentarze->fetch_assoc()) {
                  $id_reklamacji[$infs['complaints_id']] = $infs['complaints_id'];
            }
            //
        }
        unset($zapytanie_komentarz, $infs);  
        $db->close_query($sql_komentarze);   

        if ( count($id_reklamacji) > 0 ) {
             $warunki_szukania = " and cu.complaints_id IN (" . implode(',', (array)$id_reklamacji) . ")";
             $_SESSION['filtry']['reklamacje.php']['komentarze'] = 'tak';
        } else {
             unset($_SESSION['filtry']['reklamacje.php']['komentarze']);
        }
        
        unset($id_reklamacji);

    }

    // jezeli jest szukanie
    if (isset($_GET['nr_reklamacji']) && $_GET['nr_reklamacji'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['nr_reklamacji']);
        $warunki_szukania .= " and cu.complaints_rand_id LIKE '%".$szukana_wartosc."%'";
    }
    
    if (isset($_GET['szukaj']) && $_GET['szukaj'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania .= " and (cu.complaints_customers_name LIKE '%".$szukana_wartosc."%' OR cu.complaints_customers_email LIKE '%".$szukana_wartosc."%' OR cu.complaints_customers_email LIKE '%".$szukana_wartosc."%')";
    }
    
    if (isset($_GET['szukaj_tytul']) && $_GET['szukaj_tytul'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_tytul']);
        $warunki_szukania .= " and (cu.complaints_subject LIKE '%".$szukana_wartosc."%')";
    }    

    if ( isset($_GET['szukaj_data_od']) && $_GET['szukaj_data_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_od'] . ' 00:00:00')));
        $warunki_szukania .= " and cu.complaints_date_created >= '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_data_do']) && $_GET['szukaj_data_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_do'] . ' 23:59:59')));
        $warunki_szukania .= " and cu.complaints_date_created <= '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_status']) && $_GET['szukaj_status'] != '0' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_status']);
        $warunki_szukania .= " and cu.complaints_status_id = '".$szukana_wartosc."'";
    }

    if ( isset($_GET['opiekun']) && $_GET['opiekun'] > 0 ) {
        $szukana_wartosc = $filtr->process($_GET['opiekun']);
        $warunki_szukania .= " and cu.complaints_service = '".$szukana_wartosc."'";
    }    

    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }
    
    // statusy reklamacji - tylko zamkniete
    $zapytanie_reklamacja = "SELECT complaints_status_id FROM complaints_status WHERE complaints_status_type = 3 or complaints_status_type = 4";
    $sql_reklamacja = $db->open_query($zapytanie_reklamacja);

    $statusy_reklamacji = array(99);
    
    while ($id_statusu = $sql_reklamacja->fetch_assoc()) {
         //
         $statusy_reklamacji[] = $id_statusu['complaints_status_id'];
         //
    }
    $db->close_query($sql_reklamacja);
    unset($zapytanie_reklamacja);        

    $zapytanie = "SELECT * FROM complaints cu
                    LEFT JOIN customers c ON cu.complaints_customers_id = c.customers_id
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
                $sortowanie = 'cu.complaints_date_created desc';
                break;
            case "sort_a2":
                $sortowanie = 'cu.complaints_date_created asc';
                break;    
            case "sort_a3":
                $sortowanie = 'cu.complaints_date_created desc';
                break;
            case "sort_a4":
                $sortowanie = 'cu.complaints_date_created asc';
                break;                  
            case "sort_a5":
                $sortowanie = 'cu.complaints_customers_name desc';
                break;
            case "sort_a6":
                $sortowanie = 'cu.complaints_customers_name asc';
                break; 
            case "sort_a7":
                $sortowanie = 'cu.complaints_customers_orders_id desc';
                break;
            case "sort_a8":
                $sortowanie = 'cu.complaints_customers_orders_id asc';
                break; 
            case "sort_a9":
                $sortowanie = 'cu.complaints_date_end desc';
                break;   
            case "sort_a10":
                $sortowanie = 'cu.complaints_date_end asc';
                break;                   
        }            
    } else { $sortowanie = 'cu.complaints_date_created desc'; }    
    
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
                                      array('Tytuł zgłoszenia', 'center', '', 'class="ListingSchowaj"'),
                                      array('Data zgłoszenia', 'center'),
                                      array('Data ostatniej zmiany statusu', 'center','','class="ListingSchowajMobile"'),
                                      array('Data rozpatrzenia', 'center'),
                                      array('Nr zamówienia', 'center'),
                                      array('Status', 'center'));

            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['complaints_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['complaints_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['complaints_id'].'">';
                  }        

                  $tablica = array();
                  
                  // sprawdzi czy nie ma informacji od klienta - nieprzyczytanej
                  $info_nieprzeczytane = '';
                  //
                  $zapytanie_info_klient = "SELECT complaints_status_id FROM complaints_status_history WHERE complaints_customers_info = '1' AND complaints_customers_info_view = '0' AND complaints_id = '" . (int)$info['complaints_id'] . "' ORDER BY date_added LIMIT 1";
                  $sql_info_klient = $db->open_query($zapytanie_info_klient);
                  //
                  if ((int)$db->ile_rekordow($sql_info_klient) > 0) {
                      $info_nieprzeczytane = '<div style="margin-bottom:10px"><a class="TipChmurka" href="reklamacje/reklamacje_szczegoly.php?id_poz=' . (int)$info['complaints_id'] . '&zakladka=1"><b>Nieprzeczytane informacje od klienta</b><img src="obrazki/ochrona.png" alt="Informacje" /></a></div>';
                  }
                  
                  $db->close_query($sql_info_klient);

                  $tablica[] = array($info['complaints_id'],'center');
                  
                  $tablica[] = array('<input type="checkbox" name="opcja[]" id="opcja_'.$info['complaints_id'].'" value="'.$info['complaints_id'].'" /><label class="OpisForPustyLabel" for="opcja_'.$info['complaints_id'].'"></label><input type="hidden" name="id[]" value="'.$info['complaints_id'].'" />','center');
                  
                  $tablica[] = array($info_nieprzeczytane . $info['complaints_rand_id'],'center');
                  
                  unset($info_nieprzeczytane);
                  
                  $wyswietlana_nazwa = '';
                  // jezeli klient jest z bazy
                  if ($info['complaints_customers_id'] > 0) {
                      //
                      if ($info['entry_company'] != '') {
                        $wyswietlana_nazwa .= '<span class="Firma"">'.$info['entry_company'] . '</span>';
                      }
                      $wyswietlana_nazwa .= $info['entry_firstname'] . ' ' . $info['entry_lastname'] . '<br />';
                      $wyswietlana_nazwa .= $info['entry_street_address']. '<br />';
                      $wyswietlana_nazwa .= $info['entry_postcode']. ' ' . $info['entry_city'];
                      //
                    } else {
                      //
                      $wyswietlana_nazwa = nl2br($info['complaints_customers_name'] . ' <br />' . $info['complaints_customers_address']);
                      //
                  }
                  // email
                  if (!empty($info['complaints_customers_email'])) {
                      $wyswietlana_nazwa .= '<span class="MalyMail ListingSchowaj">' . $info['complaints_customers_email'] . '</span>';
                  }
                  if (!empty($info['complaints_customers_telephone'])) {
                      $wyswietlana_nazwa .= '<span class="MalyTelefon ListingSchowaj">' . $info['complaints_customers_telephone'] . '</span>';
                  }
                  
                  $tablica[] = array($wyswietlana_nazwa,'','line-height:17px');
                  
                  $tablica[] = array($info['complaints_subject'], '', '', 'class="ListingSchowaj"');

                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['complaints_date_created'])),'center');
                  
                  if ( Funkcje::CzyNiePuste($info['complaints_date_modified']) ) {
                       $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['complaints_date_modified'])),'center','','class="ListingSchowajMobile"');
                    } else {
                       $tablica[] = array('-','center','','class="ListingSchowajMobile"');
                  }
                  
                  if ( Funkcje::CzyNiePuste($info['complaints_date_end']) && !in_array((string)$info['complaints_status_id'], $statusy_reklamacji) ) {
                       $tgm = date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['complaints_date_end']));
                       //
                       $temu = '';
                       $sek = (FunkcjeWlasnePHP::my_strtotime($info['complaints_date_end']) - time());
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
                           $temu = 'PRZETERMINOWANA';
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

                  $tablica[] = array('<a href="sprzedaz/zamowienia_szczegoly.php?id_poz=' . $info['complaints_customers_orders_id'] . '">' . $info['complaints_customers_orders_id'] . '</a>','center');
                  
                  // opiekun zamowienia
                  if (isset($tablica_opiekunow[(int)$info['complaints_service']])) {
                      $opiekun = '<span class="Opiekun">Opiekun:<span>' . $tablica_opiekunow[(int)$info['complaints_service']] . '</span></span>';
                     } else {
                      $opiekun = '';
                  }   
                              
                  $tablica[] = array(Reklamacje::pokazNazweStatusuReklamacji($info['complaints_status_id'], $_SESSION['domyslny_jezyk']['id']) . $opiekun, 'center');

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['complaints_id']; 
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $tekst .= '<a class="TipChmurka" href="reklamacje/reklamacje_reklamacja_pdf.php'.$zmienne_do_przekazania.'"><b>Wydruk reklamacji</b><img src="obrazki/pdf_2.png" alt="Wydruk reklamacji" /></a>';
                  $tekst .= '<a class="TipChmurka" href="reklamacje/reklamacje_szczegoly.php'.$zmienne_do_przekazania.((isset($_GET['komentarze'])) ? '&zakladka=1' : '').'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="reklamacje/reklamacje_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  
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
            $.AutoUzupelnienie( 'szukaj', 'Podpowiedzi', 'ajax/autouzupelnienie_reklamacje.php', 50, 400 );

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
            
            <div id="naglowek_cont">Reklamacje</div>

            <div id="wyszukaj">
                <form action="reklamacje/reklamacje.php" method="post" id="reklamacjeForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Nr reklamacji:</span>
                    <input type="text" name="nr_reklamacji" id="nr_reklamacji" value="<?php echo ((isset($_GET['nr_reklamacji'])) ? $filtr->process($_GET['nr_reklamacji']) : ''); ?>" size="30" />
                </div>                  
                
                <div id="wyszukaj_text">
                    <span>Wyszukaj wg klienta:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? $filtr->process($_GET['szukaj']) : ''); ?>" size="30" />
                </div>  
                
                <div id="wyszukaj_text">
                    <span>Wyszukaj po tytule:</span>
                    <input type="text" name="szukaj_tytul" id="szukaj_tytul" value="<?php echo ((isset($_GET['szukaj_tytul'])) ? $filtr->process($_GET['szukaj_tytul']) : ''); ?>" size="30" />
                </div>                 
                
                <div class="wyszukaj_select">
                    <span>Data zgłoszenia:</span>
                    <input type="text" id="data_reklamacje_od" name="szukaj_data_od" value="<?php echo ((isset($_GET['szukaj_data_od'])) ? $filtr->process($_GET['szukaj_data_od']) : ''); ?>" size="12" class="datepicker" />&nbsp;do&nbsp;
                    <input type="text" id="data_reklamacje_do" name="szukaj_data_do" value="<?php echo ((isset($_GET['szukaj_data_do'])) ? $filtr->process($_GET['szukaj_data_do']) : ''); ?>" size="12" class="datepicker" />
                </div>

                <div class="wyszukaj_select">
                    <span>Status:</span>
                    <?php
                    $tablia_status= Array();
                    $tablia_status = Reklamacje::ListaStatusowReklamacji(true);
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
                  echo '<div id="wyszukaj_ikona"><a href="reklamacje/reklamacje.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
            </div>        
            
            <form action="reklamacje/reklamacje_akcja.php" method="post" class="cmxform">

            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="reklamacje/reklamacje.php?sort=sort_a1">data zgłoszenia malejąco</a>
                <a id="sort_a2" class="sortowanie" href="reklamacje/reklamacje.php?sort=sort_a2">data zgłoszenia rosnąco</a>
                <a id="sort_a3" class="sortowanie" href="reklamacje/reklamacje.php?sort=sort_a3">data zmiany statusu malejąco</a>
                <a id="sort_a4" class="sortowanie" href="reklamacje/reklamacje.php?sort=sort_a4">data zmiany statusu rosnąco</a>                
                <a id="sort_a9" class="sortowanie" href="reklamacje/reklamacje.php?sort=sort_a9">data rozpatrzenia malejąco</a>
                <a id="sort_a10" class="sortowanie" href="reklamacje/reklamacje.php?sort=sort_a10">data rozpatrzenia rosnąco</a>                 
                <a id="sort_a5" class="sortowanie" href="reklamacje/reklamacje.php?sort=sort_a5">klient malejąco</a>
                <a id="sort_a6" class="sortowanie" href="reklamacje/reklamacje.php?sort=sort_a6">klient rosnąco</a>
                <a id="sort_a7" class="sortowanie" href="reklamacje/reklamacje.php?sort=sort_a7">nr zamówienia malejąco</a>
                <a id="sort_a8" class="sortowanie" href="reklamacje/reklamacje.php?sort=sort_a8">nr zamówienia rosnąco</a> 
            
            </div>             

              <div id="PozycjeIkon">
              
                  <div>
                      <a class="dodaj" href="reklamacje/reklamacje_dodaj.php<?php echo ( isset($_GET['klient_id']) ? '?klient_id='.(int)$_GET['klient_id'] : ''); ?>">dodaj nową reklamacje</a>
                  </div>            
                  
                  <?php
                  $zapytanie_komentarze = "SELECT complaints_id, complaints_status_id FROM complaints_status_history WHERE complaints_customers_info = '1' AND complaints_customers_info_view = '0'";
                  $sql_komentarze = $db->open_query($zapytanie_komentarze);
                  //
                  if ((int)$db->ile_rekordow($sql_komentarze) > 0) {
                      echo '<div><a class="miganie KomentarzeReklamacje" href="reklamacje/reklamacje.php?komentarze=tak">nieprzeczytane informacje od klientów</a></div>';
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
                    <option value="1">usuń wybrane reklamacje</option>
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
                                <label style="width:auto">Czy na pewno chcesz usunąć wybrane reklamacje ?</label>
                                <input type="radio" value="0" name="usuniecie_reklamacji" id="usuniecie_reklamacji_nie" checked="checked" /><label class="OpisFor" for="usuniecie_reklamacji_nie">nie</label>
                                <input type="radio" value="1" name="usuniecie_reklamacji" id="usuniecie_reklamacji_tak" /><label class="OpisFor" style="padding-right:0px !important" for="usuniecie_reklamacji_tak">tak</label>
                            </p>
                            
                        </div>
                        
                        <div class="cl"></div>
                        
                        <div class="ostrzezenie rg">Operacja usunięcia jest nieodracalna ! Reklamacji po usunięciu nie będzie można przywrócić !</div>
                        
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
            <?php Listing::pokazAjax('reklamacje/reklamacje.php', $zapytanie, $ile_licznika, $ile_pozycji, 'complaints_id'); ?>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
