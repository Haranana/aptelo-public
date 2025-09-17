<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // id waluty PLN
    $IdPLN = 1;
    //

    if ( isset($_POST['zmiana_logowania']) && $_POST['zmiana_logowania'] == 'zmiana' ) {
        //
        $_SESSION['domyslny_uzytkownik_allegro'] = $_POST['login_allegro'];
        $zapytanie_user = "SELECT * FROM allegro_users WHERE allegro_user_id = '".$_POST['login_allegro']."'";
        $sql_user = $db->open_query($zapytanie_user);

        if ((int)$db->ile_rekordow($sql_user) > 0) {
            //
            while ($info_user = $sql_user->fetch_assoc()) {
                $_SESSION['domyslny_login_allegro'] = $info_user['allegro_user_login'];
            }
            //
        }
        $db->close_query($sql_user);
        //
        unset($zapytanie_user, $info_user);
        //
    }
    
    if ( isset($_POST['zmiana_logowania']) ) {
         unset($_POST['zmiana_logowania']);
    }
    if ( isset($_POST['login_allegro']) ) {
         unset($_POST['login_allegro']);
    }    

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
    
    $TablicaUzytkownikow = array();
    $TablicaUzytkownikowFiltry = array(array('id' => '0', 'text' => '-- dowolne --'));
    
    $zapytanieUser = "SELECT * FROM allegro_users";
    $sqlUser = $db->open_query($zapytanieUser);
                      
    if ((int)$db->ile_rekordow($sqlUser) > 0) {
                      
      while ($infoUser = $sqlUser->fetch_assoc()) {

          $TablicaUzytkownikow[$infoUser['allegro_user_id']] = $infoUser['allegro_user_login'];
          $TablicaUzytkownikowFiltry[] = array('id' => $infoUser['allegro_user_id'], 'text' => $infoUser['allegro_user_login']);

      }
      
    }
    
    $db->close_query($sqlUser);
    unset($zapytanieUser, $infoUser);    

    if ( Funkcje::SprawdzAktywneAllegro() ) {
      
         $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );
         
         $StatusyTransakcji = array();
         $StatusyTransakcji = $AllegroRest->TablicaStatusowTransakcji();

    }

    if ( isset($_GET['filtr']) ) {
         //
         $_GET['szukaj'] = floatval($_GET['filtr']);
         $_SESSION['filtry']['allegro_sprzedaz.php']['szukaj'] = floatval($_GET['filtr']);
         //
    }

    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && $_GET['szukaj'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " AND ( transaction_id LIKE '%".$szukana_wartosc."%' ) ";
        unset($szukana_wartosc);
    }
    
    if (isset($_GET['szukaj_nick']) && $_GET['szukaj_nick'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_nick']);
        $warunki_szukania = " AND ( buyer_name LIKE '%".$szukana_wartosc."%' ) ";
        unset($szukana_wartosc);
    }    

    if ( isset($_GET['szukaj_data_zakonczenia_od']) && $_GET['szukaj_data_zakonczenia_od'] != '' ) {
        $szukana_wartosc = FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_zakonczenia_od']));
        $warunki_szukania .= " and post_buy_form_created_date >= '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }

    if ( isset($_GET['szukaj_data_zakonczenia_do']) && $_GET['szukaj_data_zakonczenia_do'] != '' ) {
        $szukana_wartosc = FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_zakonczenia_do']));
        $warunki_szukania .= " and post_buy_form_created_date <= '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }
    
    if ( isset($_GET['szukaj_zamowienie']) && $_GET['szukaj_zamowienie'] != '0' ) {
        $szukana_wartosc = (int)$_GET['szukaj_zamowienie'];
        if ( $szukana_wartosc == 1 ) {
             $warunki_szukania .= " and (orders_id != '' and orders_id != '0')";
        }
        if ( $szukana_wartosc == 2 ) {
             $warunki_szukania .= " and (orders_id = '' or orders_id = '0')";
        }        
        unset($szukana_wartosc);
    }    
    
    if ( isset($_GET['szukaj_status']) && $_GET['szukaj_status'] != '0' ) {
        $szukana_wartosc = $_GET['szukaj_status'];
        $warunki_szukania .= " and (post_buy_form_pay_status = '".$szukana_wartosc."')";
        unset($szukana_wartosc);
    }    

    // jezeli jest wybrane konto allegro
    if (isset($_GET['login_aukcji']) && (int)$_GET['login_aukcji'] > 0) {
        $warunki_szukania .= " and auction_seller = '" . (int)$_GET['login_aukcji'] . "'";    
    }        

    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }

    $zapytanie = "
      SELECT * FROM allegro_transactions
        " . $warunki_szukania;

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
                $sortowanie = 'post_buy_form_created_date DESC';
                break;
            case "sort_a2":
                $sortowanie = 'post_buy_form_created_date ASC';
                break;                 
            case "sort_a3":
                $sortowanie = 'buyer_id desc';
                break;
            case "sort_a4":
                $sortowanie = 'buyer_id asc';
                break;                 
        }            
    } else { $sortowanie = 'post_buy_form_created_date DESC'; }    

    $zapytanie .= " ORDER BY ".$sortowanie;    

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {

            $zapytanie .= " limit ".$_GET['parametr'];   

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Akcja','center'),
                                      array('Sprzedający','center'),
                                      array('Numer transakcji','center', '', 'class="ListingSchowaj"'),
                                      array('Kupujący','center'),
                                      array('E-mail','center', '', 'class="ListingSchowaj"'),
                                      array('Wartość','center', '', 'class="ListingSchowaj"'),
                                      array('Dostawa','center'),
                                      array('Płatność','center'),
                                      array('Data zakupu','center'),
                                      array('Status','center'),
                                      array('Numer zam.','center'));
                                      
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['allegro_transaction_id']) {
                   $tekst .= '<tr class="pozycja_on" id="sk_'.$info['allegro_transaction_id'].'">';
                 } else {
                   $tekst .= '<tr class="pozycja_off" id="sk_'.$info['allegro_transaction_id'].'">';
                }        
                $link = '';

                $numer_zamowienia = '---';
                if ( $info['orders_id'] != '' && $info['orders_id'] != '0' ) {
                  $numer_zamowienia = '<a href="sprzedaz/zamowienia_szczegoly.php?id_poz='.$info['orders_id'].'" >'.$info['orders_id'].'</a>';
                }

                $tablica = Array();

                $dane = array( 'buyer_id' => $info['buyer_id'],
                               'id_poz' => $info['allegro_transaction_id'],
                               'transaction_id' => $info['transaction_id'] );
                               
                $salt = rand (101,999);

                if ( $info['orders_id'] != '' && $info['orders_id'] != '0' ) {
                    $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" data-id="' . $info['allegro_transaction_id'] . '" value="'.htmlspecialchars(serialize($dane), ENT_QUOTES, 'UTF-8').'" disabled="disabled" id="opcja_'.$info['auction_id'].'_'.$salt.'" /><label class="OpisForPustyLabel" for="opcja_'.$info['auction_id'].'_'.$salt.'"></label>','center');
                } else {
                    $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" data-id="' . $info['allegro_transaction_id'] . '" value="'.htmlspecialchars(serialize($dane), ENT_QUOTES, 'UTF-8').'" id="opcja_'.$info['auction_id'].'_'.$salt.'" /><label class="OpisForPustyLabel" for="opcja_'.$info['auction_id'].'_'.$salt.'"></label>','center');
                }

                unset($dane);
                
                $link = '';
                if ( Funkcje::SprawdzAktywneAllegro() ) {
                  
                    if ( $AllegroRest->polaczenie['CONF_SANDBOX'] == 'nie' ) {
                      $link = '<a href="http://allegro.pl/moje-allegro/sprzedaz/zamowienia/'.$info['transaction_id'].' target="_blank">' . $info['transaction_id'] . '</a>';
                    } else {
                      $link = '<a href="http://allegro.pl.allegrosandbox.pl/moje-allegro/sprzedaz/zamowienia/'.$info['transaction_id'].' target="_blank">' . $info['transaction_id'] . '</a>';
                    }
                    
                } else {
                    
                    $link = $info['transaction_id'];
                    
                }

                $tablica[] = array(((isset($TablicaUzytkownikow[$info['auction_seller']])) ? $TablicaUzytkownikow[$info['auction_seller']] : ''),'center', '', 'class="ListingSchowaj"');

                $tablica[] = array($link,'center', '', 'class="ListingSchowaj"');

                $tablica[] = array($info['buyer_name'],'center');
                $tablica[] = array('<a href="mailto:'.$info['buyer_email_address'].'" >'.substr((string)$info['buyer_email_address'], 0, strpos((string)$info['buyer_email_address'], '@')).'...</a>','center', '', 'class="ListingSchowaj"');


                $tablica[] = array($waluty->FormatujCene($info['post_buy_form_it_amount'], false, $info['post_buy_form_currency']),'center', 'white-space:nowrap', 'class="ListingSchowaj"');
                $tablica[] = array($waluty->FormatujCene($info['post_buy_form_postage_amount'], false, $info['post_buy_form_currency']),'center', 'white-space:nowrap');

                $FormaPlatnosci = '---';
                if ( $info['post_buy_form_pay_type'] == 'ONLINE' ) {
                   $FormaPlatnosci = 'przedpłata';
                } elseif ( $info['post_buy_form_pay_type'] == 'CASH_ON_DELIVERY' ) {
                   $FormaPlatnosci = 'przy odbiorze';
                } else {
                   $FormaPlatnosci = 'inna';
                }

                $tablica[] = array($FormaPlatnosci,'center');

                $tablica[] = array( date('d-m-Y H:i:s',$info['post_buy_form_created_date']),'center');

                $StatusTransakcji = 'Brak statusu';
                $StatusTransakcjiOpis = 'Brak danych';
                $kolorTransakcji = '#323232';
                if ( isset($StatusyTransakcji[$info['post_buy_form_pay_status']]['status']) ) {
                    $StatusTransakcji = $StatusyTransakcji[$info['post_buy_form_pay_status']]['status'];
                    if ( $StatusTransakcji == 'Płatność rozpoczęta' || $StatusTransakcji == 'Anulowane' || $StatusTransakcji == 'Nie opłacone' ) {
                        $kolorTransakcji = '#ff0000';
                    }
                    $StatusTransakcjiOpis = $StatusyTransakcji[$info['post_buy_form_pay_status']]['info'];
                }
                $tablica[] = array('<em class="TipChmurka"><span style="color:'.$kolorTransakcji.'">'.$StatusTransakcji.'</span><em class="TipIkona"><b>'.$StatusTransakcjiOpis.'</b></em>','center');

                $tablica[] = array($numer_zamowienia,'center');
                                 
                $tekst .= $listing_danych->pozycje($tablica);
                
                $tekst .= '<td class="rg_right IkonyPionowo">';

                $zmienne_do_przekazania = '?id_poz='.$info['allegro_transaction_id'];

                if ( Funkcje::SprawdzAktywneAllegro() ) {
                  
                    if ( $info['orders_id'] != '' && $info['orders_id'] != '0' ) {
                        $tekst .= '<em class="TipChmurka"><b>Zamówienie już było utworzone</b><img src="obrazki/import_off.png" alt="Zamówienie już było utworzonee" /></em>';
                    } else {
                        $tekst .= '<a class="TipChmurka" href="allegro/allegro_utworz_zamowienie.php'.$zmienne_do_przekazania.'"><b>Utwórz zamówienie</b><img src="obrazki/import.png" alt="Utwórz zamówienie" /></a>';
                    }
                    
                }
                
                $tekst .= '<em class="TipChmurka" style="cursor:pointer" id="widok_' . $info['allegro_transaction_id'] . '" onclick="produkty(' . $info['allegro_transaction_id'] . ')"><b>Rozwiń listę <br /> zakupionych <br /> produktów</b><img src="obrazki/rozwin.png" alt="Lista produktów" /></em>'; 

                $tekst .= '</td></tr>';
                  
                  $ColSpan = 12;
                  
                  $tekst .= '<tr><td colspan="' . $ColSpan . '"><div id="produkty_' . $info['allegro_transaction_id'] . '"></div></td></tr>';
                  
                  unset($ColSpan);
            } 
            $tekst .= '</table>';
            //
            echo $tekst;
            //
            $db->close_query($sql);
            unset($listing_danych,$tekst,$tablica,$tablica_naglowek, $numer_zamowienia);        

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
          $('#akcja_dolna').change(function() {
            if ( this.value == '0' || this.value == '2' ) {
              $("#page").load('allegro/blank.php');
            }
          });

          $('input.datepicker').Zebra_DatePicker({
            format: 'd-m-Y',
            inside: false,
            readonly_element: false
          });                
        });

        function produkty(id) {
            //
            if ( $('#produkty_' + id).html() != '' ) {
                 //
                 $('#widok_' + id).find('img').attr('src','obrazki/rozwin.png');
                 $('#widok_' + id).find('b').html('Rozwiń listę <br /> zakupionych <br /> produktów');
                 //              
                 $('#produkty_' + id).slideUp('fast', function() {
                    $('#produkty_' + id).html('');
                 });
              } else {
                $('#produkty_' + id).html('<div class="TloObramowania"><img src="obrazki/_loader_small.gif" alt="" /></div>');
                $.post("ajax/transakcja_szczegoly.php?tok=" + $('#tok').val(),
                    { id: id },
                    function(data) { 
                      //
                      $('#widok_' + id).find('img').attr('src','obrazki/zwin.png');
                      $('#widok_' + id).find('b').html('Zwiń listę <br /> zakupionych <br /> produktów');
                      //
                      $('#produkty_' + id).hide()
                      $('#produkty_' + id).html(data);
                      $('#produkty_' + id).slideDown('fast');
                      //
                      $(".ZdjecieProduktu").colorbox({ maxWidth:'90%', maxHeight:'90%' });
                    }           
                );  
            }
            //
        }          
        </script>

        <div id="caly_listing">
        
            <div class="poleForm cmxform" style="margin-bottom:10px">
            
              <div class="naglowek">Ustawienia konfiguracji połączenia z Allegro</div>

              <div class="pozycja_edytowana">
              
                <?php require_once('allegro_naglowek.php'); ?>
                
              </div>    
              
            </div>
        
            <div id="ajax"></div>
            
            
            <?php
            $NazwaKonta = '';
            if ( isset($_SESSION['domyslny_uzytkownik_allegro']) ) {
                 $NazwaKonta = $_SESSION['domyslny_login_allegro'];
            }

            if ( Funkcje::SprawdzAktywneAllegro() ) {
                ?>
                
                <div id="BrakSynchronizacji" style="margin-right:20px">      
                    
                    <form action="allegro/allegro_synchronizuj_transakcje.php<?php echo Funkcje::Zwroc_Get(array('id_poz')); ?>" method="post">
                        <input type="hidden" name="akcja" value="synchronizuj" />
                        <input type="hidden" name="powrot" value="allegro_sprzedaz" />
                        <input type="hidden" name="strona" value="<?php echo str_replace(".php", "", (string)basename($_SERVER["SCRIPT_NAME"])); ?>" />
                        <input type="submit" class="przyciskSynchronizacja" value="Synchronizuj dane z Allegro<?php echo (($NazwaKonta != '') ? ' dla konta ' . $NazwaKonta : ''); ?>" />
                    </form>     

                </div>
                <?php
            }
            ?>
  
            <?php if ( Funkcje::SprawdzAktywneAllegro() ) { ?>
            
            <div id="naglowek_cont">Obsługa sprzedaży - data ostatniej synchronizacji : <?php echo date("d-m-Y H:i:s", $AllegroRest->polaczenie['CONF_LAST_SYNCHRONIZATION_ORDERS']); ?></div>
            
            <?php } else { ?>
            
            <div id="naglowek_cont">Obsługa sprzedaży - brak połączenia z Allegro</div>
            
            <?php } ?>

            <div class="cl"></div>

            <div id="wyszukaj">
                <form action="allegro/allegro_sprzedaz.php" method="post" id="allegroForm" class="cmxform">

                      <div id="wyszukaj_text">
                          <span>Wyszukaj transakcję:</span>
                          <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? $filtr->process($_GET['szukaj']) : ''); ?>" size="35" />
                      </div>  
                      
                      <div class="wyszukaj_select">
                          <span>Data sprzedaży:</span>
                          <input type="text" id="data_zakonczenia_od" name="szukaj_data_zakonczenia_od" value="<?php echo ((isset($_GET['szukaj_data_zakonczenia_od'])) ? $filtr->process($_GET['szukaj_data_zakonczenia_od']) : ''); ?>" size="14" class="datepicker" />&nbsp;do&nbsp;
                          <input type="text" id="data_zakonczenia_do" name="szukaj_data_zakonczenia_do" value="<?php echo ((isset($_GET['szukaj_data_zakonczenia_do'])) ? $filtr->process($_GET['szukaj_data_zakonczenia_do']) : ''); ?>" size="14" class="datepicker" />
                      </div>  

                      <div class="wyszukaj_select">
                          <span>Nick kupującego:</span>
                          <input type="text" name="szukaj_nick" id="szukaj_nick" value="<?php echo ((isset($_GET['szukaj_nick'])) ? $filtr->process($_GET['szukaj_nick']) : ''); ?>" size="25" />
                      </div> 
                      
                      <div class="wyszukaj_select">
                          <span>Zamówienie:</span>
                          <?php
                          $tablica = Array();
                          $tablica[] = array('id' => '0', 'text' => 'wszystkie');
                          $tablica[] = array('id' => '1', 'text' => 'tak');
                          $tablica[] = array('id' => '2', 'text' => 'nie');
                          echo Funkcje::RozwijaneMenu('szukaj_zamowienie', $tablica, ((isset($_GET['szukaj_zamowienie'])) ? $filtr->process($_GET['szukaj_zamowienie']) : ''));
                          unset($tablica);
                          ?>
                      </div> 

                      <div class="wyszukaj_select">
                          <span>Status:</span>
                          <?php
                          $tablica = Array();
                          $tablica[] = array('id' => '0', 'text' => 'wszystkie');
                          $tablica[] = array('id' => 'BOUGHT', 'text' => 'Nie opłacone');
                          $tablica[] = array('id' => 'FILLED_IN', 'text' => 'Płatność rozpoczęta');
                          $tablica[] = array('id' => 'READY_FOR_PROCESSING', 'text' => 'Płatność zakończona');
                          $tablica[] = array('id' => 'CANCELLED', 'text' => 'Zakup anulowany');
                          $tablica[] = array('id' => 'FULFILLMENT_STATUS_CHANGED', 'text' => 'Zmienione przez sprzedającego');
                          echo Funkcje::RozwijaneMenu('szukaj_status', $tablica, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : ''));
                          unset($tablica);
                          ?>
                      </div> 
                      
                      <div class="wyszukaj_select">
                          <span>Sprzedaż dla konta:</span>
                          <?php
                          $Migracja = false;
                          echo Funkcje::RozwijaneMenu('login_aukcji', $TablicaUzytkownikowFiltry, ((isset($_GET['login_aukcji'])) ? $filtr->process($_GET['login_aukcji']) : '')); 
                          ?>
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
                  echo '<div id="wyszukaj_ikona"><a href="allegro/allegro_sprzedaz.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                

                <div style="clear:both"></div>
            </div> 

            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="allegro/allegro_sprzedaz.php?sort=sort_a1">daty transakcji malejąco</a>
                <a id="sort_a2" class="sortowanie" href="allegro/allegro_sprzedaz.php?sort=sort_a2">daty transakcji rosnąco</a>
                <a id="sort_a3" class="sortowanie" href="allegro/allegro_sprzedaz.php?sort=sort_a3">kupujący malejąco</a>
                <a id="sort_a4" class="sortowanie" href="allegro/allegro_sprzedaz.php?sort=sort_a4">kupujący rosnąco</a>
                
            </div>           

            <?php
            if ( $Migracja && $ile_pozycji > 0 && $_SESSION['domyslny_uzytkownik_allegro'] != '' ) {
                echo '<div style="margin-left:10px; padding:5px 0px 5px 0px; font-size:120%; color:#ff0000; font-weight:bold;">';
                echo '<a style="color:#ff0000;" href="allegro/przypisanie_zamowien.php">Przypisz zamówienia do transakcji po migracji API!!!</a>';
                echo '</div>';
            }
            ?>

            <div id="PrzyciskiAukcji" style="margin-bottom:5px">

                <?php
                $NazwaKonta = '';
                if ( isset($_SESSION['domyslny_uzytkownik_allegro']) ) {
                     $NazwaKonta = $_SESSION['domyslny_login_allegro'];
                }
                ?> 

                <div style="clear:both"></div>
            
            </div> 

            <form action="allegro/allegro_akcja.php" method="post" class="cmxform">

              <div class="sprzedazAllegro">

                  <div id="wynik_zapytania"></div>
                  <div id="aktualna_pozycja">1</div>
                  
                  <?php if ( Funkcje::SprawdzAktywneAllegro() ) { ?>

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
                        <option value="2">utwórz zamówienia dla zaznaczonych aukcji</option>
                      </select>
                      
                    </div>
                    
                    <div style="clear:both;"></div>
                    
                  </div>
                  
                  <?php } ?>
                  
                  <div id="page"></div>

                  <div id="dolny_pasek_stron"></div>
                  <div id="pokaz_ile_pozycji"></div>
                  <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
              
              </div>
              
              <?php if ( Funkcje::SprawdzAktywneAllegro() ) { ?>
              
              <?php if ($ile_pozycji > 0) { ?>
              <div style="text-align:right" id="zapisz_zmiany"><input type="submit" class="przyciskBut" value="Wykonaj" /></div>
              <?php } ?>                
              
              <?php } ?>

            </form>

            <div class="cl"></div>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('allegro/allegro_sprzedaz.php', $zapytanie, $ile_licznika, $ile_pozycji, 'allegro_transaction_id'); ?>
            </script>                

        </div>

        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
