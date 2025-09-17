<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $api = 'BLISKAPACZKA';
    $Statusy = array();

    if ( INTEGRACJA_BLISKAPACZKA_WLACZONY == 'tak' ) {
        $apiKurier = new BliskapaczkaApi();

        $Statusy = $apiKurier->bliskapaczka_status_array();

    }
    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $warunki_szukania = '';

    if ( isset($_GET['szukaj_operator']) ) {
        $szukana_wartosc = $_GET['szukaj_operator'];
        if ( $_GET['szukaj_operator'] != '0' ) {
            $warunki_szukania .= " and orders_shipping_comments = '".$szukana_wartosc."'";
        }
    }
    if ( isset($_GET['szukaj_typ']) && $_GET['szukaj_typ'] != '0' ) {
        $warunki_szukania .= " and orders_dispatch_status = '".$_GET['szukaj_typ']."'";
    }
    if ( isset($_GET['szukaj_status']) && $_GET['szukaj_status'] != '0' ) {
        $warunki_szukania .= " and orders_shipping_status = '".$_GET['szukaj_status']."'";
    }

    $zapytanie = "SELECT *
    FROM orders_shipping WHERE orders_shipping_type = 'BLISKAPACZKA' " . $warunki_szukania;
    $zapytanie .= " ORDER BY orders_shipping_date_created DESC";    

    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }

    $db->close_query($sql);
         
    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {

            $Poczatek = explode(',', (string)$_GET['parametr']);

            if ( INTEGRACJA_BLISKAPACZKA_WLACZONY == 'tak' ) {
                if ( $Poczatek['0'] == '0' && $warunki_szukania == '' ) {

                    $Interwal = 600;
                    if ( isset($_SESSION['BliskapaczkaUpd']) ) {
                        $Interwal = time() - $_SESSION['BliskapaczkaUpd'];

                    }

                    if ( !isset($_SESSION['BliskapaczkaUpd']) || ( $Interwal > 600 ) ) {

                        $CzasUtworzenia = time() - ( 24*3*3600 );
                        $CzasUtworzenia = date('Y-m-d H:i:s', $CzasUtworzenia);

                        $zapytanieStatus = "SELECT orders_shipping_id, orders_shipping_number 
                        FROM orders_shipping WHERE orders_shipping_type = 'BLISKAPACZKA' AND orders_shipping_date_modified < '".$CzasUtworzenia."'";    
                        $sqlStatus = $db->open_query($zapytanieStatus);
                        $PrzesylkiDoSprawdzenia = '';
                        if ( (int)$db->ile_rekordow($sqlStatus) > 0) {
                          
                            while ( $info_Status = $sqlStatus->fetch_assoc() ) {

                                $PrzesylkiDoSprawdzenia .= $info_Status['orders_shipping_number'] .',';
                            }

                        }
                        $db->close_query($sqlStatus);
                        unset($zapytanieStatus, $info_Status);
                        $PrzesylkiDoSprawdzenia = substr((string)$PrzesylkiDoSprawdzenia, 0, -1);

                        $Wynik = $apiKurier->commandGet('v2/order?size=100&page=0&numbers='.$PrzesylkiDoSprawdzenia);

                        if ( is_object($Wynik) ) {
                        if ( is_array($Wynik->content) && count($Wynik->content) > 0 ) {

                            foreach ( $Wynik->content as $Paczka ) {

                                if ( isset($Paczka) && $Paczka->changes ) {

                                    $AktualnyStatus = end($Paczka->changes);
                                    $pola = array();
                                    $pola = array(
                                                  array('orders_shipping_status',$AktualnyStatus->status),
                                                  array('orders_shipping_date_modified',date('Y-m-d G:i:s', FunkcjeWlasnePHP::my_strtotime($AktualnyStatus->dateTime)))
                                    );

                                    $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$Paczka->number."'");
                                    unset($pola);

                                }
                            }

                        }
                        }

                    }

                   $_SESSION['BliskapaczkaUpd'] = time();

                }
            }
        
            $zapytanie .= " limit ".$_GET['parametr'];  

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Akcja', 'center'),
                                      array('Zamówienie', 'center'),
                                      array('Operator', 'center'),
                                      array('Rodzaj', 'center'),
                                      array('Numer przesyłki', 'center'),
                                      array('Numer zlecenia', 'center'),
                                      array('Numer podjazdu', 'center'),
                                      array('Status', 'center'),
                                      array('Data utworzenia', 'center'),
                                      array('Data aktualizacji', 'center')
            );
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  $tablica = array();
                  $zaznaczony = '';

                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['orders_shipping_id']) {
                     $tekst .= '<tr class="pozycja_on">';
                   } else {
                     $tekst .= '<tr class="pozycja_off">';
                  }        

                  $tablica[] = array('<input '.$zaznaczony.' type="checkbox" style="border:0px" name="opcja[]" id="id_'.$info['orders_shipping_id'].'" value="'.$info['orders_shipping_id'].'" class="theClass" /><label class="OpisForPustyLabel" for="id_'.$info['orders_shipping_id'].'"></label>','center');

                  $tablica[] = array('<a href="sprzedaz/zamowienia_szczegoly.php?id_poz='.$info['orders_id'].'" >'.$info['orders_id'].'</a>','center');

                  $tablica[] = array($info['orders_shipping_comments'],'center');
                  $tablica[] = array($info['orders_dispatch_status'],'center');
                  $tablica[] = array($info['orders_shipping_misc'],'center');
                  $tablica[] = array($info['orders_shipping_number'],'center');

                  $tablica[] = array($info['orders_shipping_protocol'],'center');

                  $tablica[] = array(( isset($Statusy[$info['orders_shipping_status']]) ? $Statusy[$info['orders_shipping_status']]: $info['orders_shipping_status'] ),'center');

                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['orders_shipping_date_created'])),'center');
                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['orders_shipping_date_modified'])),'center');

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['orders_shipping_id']; 
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  if ( INTEGRACJA_BLISKAPACZKA_WLACZONY == 'tak' ) {

                      if ( $info['orders_shipping_status'] == 'READY_TO_SEND' || $info['orders_shipping_status'] == 'SAVED' || $info['orders_shipping_status'] == 'WAITING_FOR_PAYMENT' || $info['orders_shipping_status'] == 'PAYMENT_CONFIRMED' || $info['orders_shipping_status'] == 'PAYMENT_REJECTED' || $info['orders_shipping_status'] == 'PROCESSING' || $info['orders_shipping_status'] == 'ERROR') {
                          $tekst.= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_bliskapaczka_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=usun&amp;przesylka='.$info['orders_shipping_number'].'" ><b>Anuluj przesyłkę</b><img src="obrazki/kasuj.png" alt="Anuluj przesyłkę" /></a>';
                      } else {
                          $tekst.= '<img src="obrazki/kasuj_off.png" alt="Usuń przesyłkę" />';
                      }

                      if ( $info['orders_shipping_status'] != 'NEW' && $info['orders_shipping_status'] != 'SAVED' && $info['orders_shipping_status'] != 'WAITING_FOR_PAYMENT' && $info['orders_shipping_status'] != 'PAYMENT_CONFIRMED' && $info['orders_shipping_status'] != 'PAYMENT_REJECTED' && $info['orders_shipping_status'] != 'PROCESSING' && $info['orders_shipping_status'] != 'ERROR'  && $info['orders_shipping_status'] != 'PAYMENT_CANCELLATION_ERROR'  && $info['orders_shipping_status'] != 'ADVISING'  && $info['orders_shipping_status'] != 'CANCELED') {
                        $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_bliskapaczka_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=etykieta&amp;przesylka='.$info['orders_shipping_number'].'" ><b>Pobierz etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykiety" /></a>';
                     }

                     if ( ($info['orders_dispatch_status'] == 'D2D' || $info['orders_dispatch_status'] == 'D2P') && $info['orders_shipping_status'] == 'READY_TO_SEND' ) {

                        $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_bliskapaczka_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=potwierdzenie&amp;przesylka='.$info['orders_shipping_number'].'" ><b>Pobierz protokół</b><img src="obrazki/proforma_pdf.png" alt="Pobierz protokół" /></a>';
                     }

                 }
                 
                 $tekst .= '</td></tr>';

                 unset($info_tmp);        
                 
                  
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
               if ( this.value == '0' ) {
                 $("#page").hide();
                 $("#page").load('sprzedaz/blank.php');
                 $("#submitBut").hide();
               }
               if ( this.value == '1' ) {
                 $("#page").hide();
                 $("#page").load('sprzedaz/blank.php');
                 $("#submitBut").show();
               }
               if ( this.value == '2' ) {
                 $("#page").hide();
                 $("#page").load('sprzedaz/blank.php');
                 $("#submitBut").show();
               }
               if ( this.value == '3' ) {
                    $('#ekr_preloader').css('display','block');
                    event.preventDefault();
                    var searchIDs = $("input:checkbox:checked").map(function(){
                        return this.value;
                    }).toArray();
                    $("#page").load('sprzedaz/zamowienia_bliskapaczka_zamow_kuriera.php?IDs='+searchIDs+'', function() {
                    //
                    $("#page").show();
                    $("#submitBut").show();
                    pokazChmurki();
                    $('#ekr_preloader').css('display','none');
                    //
                 });                 
               }
            });

        });
        
        </script>

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Wysyłki Bliskapaczka</div>
            <div id="wyszukaj">
                <form action="sprzedaz/zamowienia_wysylki_bliskapaczka.php" method="post" id="zamowieniaBLISKAPACZKAForm" class="cmxform">

                <div class="wyszukaj_select">
                    <span>Operator</span>
                    <?php
                    $tablica_operator = Array();
                    $tablica_operator[] = array('id' => '0', 'text' => 'Dowolny');
                    $tablica_operator[] = array('id' => 'RUCH', 'text' => 'Ruch');
                    $tablica_operator[] = array('id' => 'POCZTA', 'text' => 'Poczta Polska');
                    $tablica_operator[] = array('id' => 'INPOST', 'text' => 'Inpost');
                    $tablica_operator[] = array('id' => 'DPD', 'text' => 'DPD');
                    $tablica_operator[] = array('id' => 'UPS', 'text' => 'UPS');
                    $tablica_operator[] = array('id' => 'FEDEX', 'text' => 'FedEx');
                    $tablica_operator[] = array('id' => 'GLS', 'text' => 'GLS');
                    $tablica_operator[] = array('id' => 'XPRESS', 'text' => 'X-press Couriers');
                    echo Funkcje::RozwijaneMenu('szukaj_operator', $tablica_operator, ((isset($_GET['szukaj_operator'])) ? $filtr->process($_GET['szukaj_operator']) : '')); ?>
                </div>
                <div class="wyszukaj_select">
                    <span>Rodzaj dostawy:</span>
                    <?php
                    $tablia_typ = Array();
                    $tablia_typ[] = array('id' => '0', 'text' => 'Dowolny');
                    $tablia_typ[] = array('id' => 'P2P', 'text' => 'Z punktu do punktu');
                    $tablia_typ[] = array('id' => 'D2P', 'text' => 'Od drzwi do punktu');
                    $tablia_typ[] = array('id' => 'P2D', 'text' => 'Z punktu do drzwi');
                    $tablia_typ[] = array('id' => 'D2D', 'text' => 'Od drzwi do drzwi');
                    echo Funkcje::RozwijaneMenu('szukaj_typ', $tablia_typ, ((isset($_GET['szukaj_typ'])) ? $filtr->process($_GET['szukaj_typ']) : '')); ?>
                </div>
                <div class="wyszukaj_select">
                    <span>Status:</span>
                    <?php
                    $tablia_status = Array();
                    $tablia_status[] = array('id' => '0', 'text' => 'Dowolny');
                    $tablia_status[] = array('id' => 'SAVED', 'text' => $Statusy['SAVED']);
                    $tablia_status[] = array('id' => 'WAITING_FOR_PAYMENT', 'text' => $Statusy['WAITING_FOR_PAYMENT']);
                    $tablia_status[] = array('id' => 'PAYMENT_CONFIRMED', 'text' => $Statusy['PAYMENT_CONFIRMED']);
                    $tablia_status[] = array('id' => 'PROCESSING', 'text' => $Statusy['PROCESSING']);
                    $tablia_status[] = array('id' => 'ADVISING', 'text' => $Statusy['ADVISING']);
                    $tablia_status[] = array('id' => 'ERROR', 'text' => $Statusy['ERROR']);
                    $tablia_status[] = array('id' => 'READY_TO_SEND', 'text' => $Statusy['READY_TO_SEND']);
                    $tablia_status[] = array('id' => 'POSTED', 'text' => $Statusy['POSTED']);
                    $tablia_status[] = array('id' => 'ON_THE_WAY', 'text' => $Statusy['ON_THE_WAY']);
                    $tablia_status[] = array('id' => 'READY_TO_PICKUP', 'text' => $Statusy['READY_TO_PICKUP']);
                    $tablia_status[] = array('id' => 'DELIVERED', 'text' => $Statusy['DELIVERED']);
                    $tablia_status[] = array('id' => 'REMINDER_SENT', 'text' => $Statusy['REMINDER_SENT']);
                    $tablia_status[] = array('id' => 'PICKUP_EXPIRED', 'text' => $Statusy['PICKUP_EXPIRED']);
                    $tablia_status[] = array('id' => 'AVIZO', 'text' => $Statusy['AVIZO']);
                    $tablia_status[] = array('id' => 'CLAIMED', 'text' => $Statusy['CLAIMED']);
                    $tablia_status[] = array('id' => 'RETURNED', 'text' => $Statusy['RETURNED']);
                    $tablia_status[] = array('id' => 'ARCHIVED', 'text' => $Statusy['ARCHIVED']);
                    $tablia_status[] = array('id' => 'OTHER', 'text' => $Statusy['OTHER']);
                    $tablia_status[] = array('id' => 'MARKED_FOR_CANCELLATION', 'text' => $Statusy['MARKED_FOR_CANCELLATION']);
                    $tablia_status[] = array('id' => 'CANCELED', 'text' => $Statusy['CANCELED']);
                    $tablia_status[] = array('id' => 'WAITING_FOR_PAYOUT', 'text' => $Statusy['WAITING_FOR_PAYOUT']);
                    $tablia_status[] = array('id' => 'PAYOUT_SENT', 'text' => $Statusy['PAYOUT_SENT']);
                    $tablia_status[] = array('id' => 'PAYMENT_REJECTED', 'text' => $Statusy['PAYMENT_REJECTED']);
                    $tablia_status[] = array('id' => 'OUT_FOR_DELIVERY', 'text' => $Statusy['OUT_FOR_DELIVERY']);

                    echo Funkcje::RozwijaneMenu('szukaj_status', $tablia_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : '')); ?>
                </div>  

                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true || isset($_GET['szukaj_status']) ) {
                  echo '<div id="wyszukaj_ikona"><a href="sprzedaz/zamowienia_wysylki_bliskapaczka.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
            </div>

            <form class="cmxform" method="post" action="sprzedaz/zamowienia_bliskapaczka_akcja.php">

                <div id="wynik_zapytania"></div>
                <div id="aktualna_pozycja">1</div>

                <?php if ( INTEGRACJA_BLISKAPACZKA_WLACZONY == 'tak' ) { ?>
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
                            <!-- <option value="1">wydrukuj etykiety</option> -->
                            <option value="2">wydrukuj protokół</option>
                            <option value="3">zamów kuriera</option>
                          </select>
                        </div>
                        
                        <div class="cl"></div>
                      
                    </div>

                    <div class="cl"></div>

                    <div id="page" class="RamkaAkcji"></div>
                <?php } ?>
                    
                <div class="cl"></div>

                <div id="dolny_pasek_stron"></div>
                <div id="pokaz_ile_pozycji"></div>
                <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>


                <?php //if ($ile_pozycji > 0 && ( isset($_GET['szukaj_status']) && ( $_GET['szukaj_status'] == '9999' || $_GET['szukaj_status'] != '999') ) ) { ?>

                <div class="rg"><input type="submit" id="submitBut" class="przyciskBut" value="Wykonaj" style="display:none;" /></div>

                <?php //} ?>


                <div class="cl"></div>

            </form>

            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            <?php Listing::pokazAjax('sprzedaz/zamowienia_wysylki_bliskapaczka.php', $zapytanie, $ile_licznika, $ile_pozycji, 'orders_shipping_id'); ?>
            </script>              

        </div>
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
