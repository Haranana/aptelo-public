<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $api = 'GEIS';
    $ListaStatusow = array();
    $TablicaStatusow = array();
    $TablicaStatusowTmp = array();

    $apiKurier = new GeisApi();

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    if ( INTEGRACJA_GEIS_WLACZONY == 'tak' ) {
        $TablicaStatusow = $apiKurier->getGClientStatus();
        $TablicaStatusowTmp = $apiKurier->getGClientStatusTablica();
    }

    $warunki_szukania = '';

    if (isset($_GET['parametr'])) {
        $Poczatek = explode(',', (string)$_GET['parametr']);
        if ( $Poczatek['0'] == '0' ) {

            if ( INTEGRACJA_GEIS_WLACZONY == 'tak' ) {

                $zapytanieStatus = "SELECT orders_shipping_id, orders_shipping_number 
                                    FROM orders_shipping WHERE orders_shipping_type = 'GEIS' AND orders_shipping_comments != 'reczna' ORDER BY orders_shipping_date_created DESC LIMIT 100";    
                $sqlStatus = $db->open_query($zapytanieStatus);
                $PrzesylkiDoSprawdzenia = array();
                if ( (int)$db->ile_rekordow($sqlStatus) > 0) {
                    while ( $info_Status = $sqlStatus->fetch_assoc() ) {
                        $PrzesylkiDoSprawdzenia[] = $info_Status['orders_shipping_number'];
                    }
                }
                $db->close_query($sqlStatus);
                unset($zapytanieStatus, $info_Status);

                $AktualneStatusyPrzesylek = $apiKurier->doShipmentStatus($PrzesylkiDoSprawdzenia);

                if ( isset($AktualneStatusyPrzesylek->ShipmentStatusResult->ResponseObject->ShipmentStatusResponse) && count((array)$AktualneStatusyPrzesylek->ShipmentStatusResult->ResponseObject->ShipmentStatusResponse) > 0 ){
                    foreach ( $AktualneStatusyPrzesylek->ShipmentStatusResult->ResponseObject->ShipmentStatusResponse as $przesylka ) {
                        if ( isset($przesylka->StatusCode) && isset($przesylka->ShipmentNumber) ) {
                            $pola = array(
                                        array('orders_dispatch_status', $przesylka->StatusCode)
                                    );
                            $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$przesylka->ShipmentNumber."'");
                             unset($pola);
                        }
                    }
                }

            }
        }
    }

    $DataBiezaca = date('Y-m-d');
    if ( !isset($_GET['szukaj_data_od']) ) {
        $_GET['szukaj_data_od'] = date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($DataBiezaca . '- 1 day'));
    }
    if ( !isset($_GET['szukaj_data_do']) ) {
        $_GET['szukaj_data_do'] = date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($DataBiezaca . '+ 1 day'));
    }

    if ( isset($_GET['szukaj_status']) ) {
        $szukana_wartosc = $_GET['szukaj_status'];
        if ( $_GET['szukaj_status'] != '0' ) {
            $warunki_szukania .= " and orders_dispatch_status = '".$szukana_wartosc."'";
        }
        //if ( $_GET['szukaj_status'] == '9999' ) {
        //    $warunki_szukania .= " and orders_dispatch_status != '999'";
        //}
    }
    // data odbioru
    if ( isset($_GET['szukaj_data_od']) && $_GET['szukaj_data_od'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_data_od']);
        $warunki_szukania .= " and STR_TO_DATE(orders_shipping_protocol, '%Y-%m-%d') >= '".$szukana_wartosc."'";
    }
    if ( isset($_GET['szukaj_data_do']) && $_GET['szukaj_data_do'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_data_do']);
        $warunki_szukania .= " and STR_TO_DATE(orders_shipping_protocol, '%Y-%m-%d') <= '".$szukana_wartosc."'";
    }
    if ( isset($_GET['szukaj_rodzaj']) && $_GET['szukaj_rodzaj'] != '0' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_rodzaj']);
        $warunki_szukania .= " and orders_shipping_comments = '".$szukana_wartosc."'";
    }

    $zapytanie = "SELECT *
    FROM orders_shipping WHERE orders_shipping_type = 'GEIS' AND orders_shipping_comments != 'reczna' " . $warunki_szukania;
    $zapytanie .= " ORDER BY STR_TO_DATE(orders_shipping_protocol, '%Y-%m-%d') DESC, orders_shipping_date_created DESC";    

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
        
            $zapytanie .= " limit ".$_GET['parametr'];  

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Akcja', 'center'),
                                      array('Zamówienie', 'center'),
                                      array('Rodzaj usługi', 'center'),
                                      array('Numer paczki', 'center'),
                                      array('Ilość paczek', 'center'),
                                      array('Status GClient', 'center'),
                                      array('Data utworzenia', 'center'),
                                      array('Data odbioru', 'center')
            );
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  $tablica = array();
                  $zaznaczony = '';
                  $status = '';
/*
                  if ( $info['orders_shipping_status'] != 'Inserted' ) {
                    $status = $TablicaStatusow[$info['orders_shipping_status']];
                  } else {
                    $status = $info['orders_shipping_status'];
                  }
*/
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['orders_shipping_id']) {
                     $tekst .= '<tr class="pozycja_on">';
                   } else {
                     $tekst .= '<tr class="pozycja_off">';
                  }        

                  $tablica[] = array('<input '.$zaznaczony.' type="checkbox" style="border:0px" name="opcja[]" id="id_'.$info['orders_shipping_id'].'" value="'.$info['orders_shipping_id'].'" /><label class="OpisForPustyLabel" for="id_'.$info['orders_shipping_id'].'"></label>','center');

                  $tablica[] = array('<a href="sprzedaz/zamowienia_szczegoly.php?id_poz='.$info['orders_id'].'&amp;zakladka=1" >'.$info['orders_id'].'</a>','center');

                  $tablica[] = array($info['orders_shipping_comments'],'center');
                  $tablica[] = array($info['orders_shipping_number'],'center');
                  $tablica[] = array($info['orders_parcels_quantity'],'center');

                  if ( isset($TablicaStatusowTmp[$info['orders_dispatch_status']]) ) {
                    $tablica[] = array($TablicaStatusowTmp[$info['orders_dispatch_status']],'center');
                  } else {
                    $tablica[] = array($info['orders_dispatch_status'],'center');
                  }

                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['orders_shipping_date_created'])),'center');
                  $tablica[] = array(date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['orders_shipping_protocol'])),'center');

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['orders_id']; 
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  if ( $info['orders_shipping_comments'] != 'order' && $info['orders_shipping_status'] != 'Anulowana' ) {
                    $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_geis_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=etykieta&amp;przesylka='.$info['orders_shipping_number'].'" ><b>Pobierz etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykiety" /></a>';
                  }
                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_geis_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=status&amp;przesylka='.$info['orders_shipping_number'].'" ><b>Sprawdź status przesyłki</b><img src="obrazki/przesylka_tracking.png" alt="Sprawdź status przesyłki" /></a>';

                  $tekst.= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_geis_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=usunbaza&amp;przesylka='.$info['orders_shipping_number'].'" ><b>Usuń przesyłkę</b><img src="obrazki/kasuj.png" alt="Usuń przesyłkę z bazy sklepu" /></a>';

                  $tekst .= '</td></tr>';
                  unset($info_tmp, $zamowienie);        
                 
                  
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
              format: 'Y-m-d',
              inside: false,
              readonly_element: false
            });             

            $('#akcja_dolna').change(function() {
               if ( this.value == '0' ) {
                 $("#page").slideUp();
                 //$("#page").load('sprzedaz/blank.php');
                 $("#submitBut").hide();
               }
               if ( this.value == '1' ) {
                 $("#page").slideUp();
                 //$("#page").load('sprzedaz/blank.php');
                 $("#submitBut").show();
               }
               if ( this.value == '2' ) {
                 $("#page").slideUp();
                 //$("#page").load('sprzedaz/blank.php');
                 $("#submitBut").show();
               }
               if ( this.value == '3' ) {
                    //
                    $("#page").slideDown();
                    $("#submitBut").show();
                    //
               }
            });

        });
        
        </script>

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Wysyłki GEIS</div>

            <div id="wyszukaj_geis">
                <form action="sprzedaz/zamowienia_wysylki_geis.php" method="post" id="zamowieniaGEISForm" class="cmxform">

                <div class="wyszukaj_select">
                        <span>Data odbioru od:</span>
                        <input type="text" id="data_odbioru_od" name="szukaj_data_od" value="<?php echo ((isset($_GET['szukaj_data_od'])) ? $filtr->process($_GET['szukaj_data_od']) : ''); ?>" size="12" class="datepicker" />
                </div>   
                <div class="wyszukaj_select">
                        <span>Data odbioru do:</span>
                        <input type="text" id="data_odbioru_do" name="szukaj_data_do" value="<?php echo ((isset($_GET['szukaj_data_do'])) ? $filtr->process($_GET['szukaj_data_do']) : ''); ?>" size="12" class="datepicker" />
                </div>   
                <div class="wyszukaj_select">
                    <span>Rodzaj usługi:</span>
                    <?php
                    $tablia_rodzaj = Array();
                    $tablia_rodzaj[] = array('id' => '0', 'text' => 'Dowolny');
                    $tablia_rodzaj[] = array('id' => 'export', 'text' => 'Export');
                    $tablia_rodzaj[] = array('id' => 'order', 'text' => 'Order');
                    echo Funkcje::RozwijaneMenu('szukaj_rodzaj', $tablia_rodzaj, ((isset($_GET['szukaj_rodzaj'])) ? $filtr->process($_GET['szukaj_rodzaj']) : '')); ?>
                </div>  
                <div class="wyszukaj_select">
                    <span>Status GClient:</span>
                    <?php
                    echo Funkcje::RozwijaneMenu('szukaj_status', $TablicaStatusow, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : ''), 'style="width:170px;"');
                    ?>
                </div>  

                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true || isset($_GET['szukaj_status']) || isset($_GET['szukaj_data_odbioru']) ) {
                  echo '<div id="wyszukaj_ikona" style="display:none;"><a href="sprzedaz/zamowienia_wysylki_geis.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
            </div>

            <form class="cmxform" method="post" action="sprzedaz/zamowienia_geis_akcja.php">

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
                        <option value="1">wydrukuj etykiety dla zaznaczonych</option>
                        <option value="2">wydrukuj protokół dla zaznaczonych</option>
                        <option value="3">wydrukuj protokół dla wybranej daty</option>
                      </select>
                    </div>
                    
                    <div class="cl"></div>
                  
                </div>

                <div class="cl"></div>

                <div id="page" class="RamkaAkcji">
                    <div class="EdycjaOdstep">
                        <div class="pozycja_edytowana">
                            <div class="info_content">
                                <input type="hidden" name="akcja" value="zapisz" />
                                <p>
                                  <label class="required">Data odbioru:</label>
                                  <input type="text" id="data_odbioru_kuriera" name="data_odbioru_kuriera" value="<?php echo date('Y-m-d'); ?>" size="12" class="datepicker" />
                                </p>        
                            </div>
                        </div>
                    </div>
                </div>
                    
                <div class="cl"></div>

                <div id="dolny_pasek_stron"></div>
                <div id="pokaz_ile_pozycji"></div>
                <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>


                <div class="rg"><input type="submit" id="submitBut" class="przyciskBut" value="Wykonaj" style="display:none;" /></div>

                <?php //} ?>


                <div class="cl"></div>

            </form>

            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            <?php Listing::pokazAjax('sprzedaz/zamowienia_wysylki_geis.php', $zapytanie, $ile_licznika, $ile_pozycji, 'orders_shipping_id'); ?>
            </script>              

        </div>
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
