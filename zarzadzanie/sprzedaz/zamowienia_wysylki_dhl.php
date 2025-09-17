<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $api = 'DPD';

    $apiKurier = new DhlApi();

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $warunki_szukania = '';

    if ( isset($_GET['szukaj']) ) {
        $szukana_wartosc = $_GET['szukaj'];
        $warunki_szukania .= " and ( orders_shipping_number = '".$szukana_wartosc."' OR orders_shipping_protocol = '".$szukana_wartosc."' )";
    }
    if ( isset($_GET['szukaj_status']) ) {
        $szukana_wartosc = $_GET['szukaj_status'];
        if ( $_GET['szukaj_status'] != '0' ) {
            $warunki_szukania .= " and orders_shipping_status = '".$szukana_wartosc."'";
        }
    }

    // data dodania
    if ( isset($_GET['szukaj_data_dodania_od']) && $_GET['szukaj_data_dodania_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_dodania_od'] . ' 00:00:00')));
        $warunki_szukania .= " and orders_shipping_date_created >= '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_data_dodania_do']) && $_GET['szukaj_data_dodania_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_dodania_do'] . ' 23:59:59')));
        $warunki_szukania .= " and orders_shipping_date_created <= '".$szukana_wartosc."'";
    }    
    
    // data odbioru
    if ( isset($_GET['szukaj_data_odbioru']) && $_GET['szukaj_data_odbioru'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_data_odbioru']);
        $warunki_szukania .= " and orders_shipping_misc = '".$szukana_wartosc."'";
    }

    $zapytanie = "SELECT *
    FROM orders_shipping WHERE orders_shipping_type = 'DHL' " . $warunki_szukania;
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
        
            $zapytanie .= " limit ".$_GET['parametr'];  

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Akcja', 'center'),
                                      array('Zamówienie', 'center'),
                                      array('Numer zlecenia', 'center'),
                                      array('Data zlecenia', 'center'),
                                      array('Numer paczki', 'center'),
                                      array('Ilość paczek', 'center'),
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

                  $tablica[] = array('<input '.$zaznaczony.' type="checkbox" style="border:0px" name="opcja[]" id="id_'.$info['orders_shipping_id'].'" value="'.$info['orders_shipping_id'].'" /><label class="OpisForPustyLabel" for="id_'.$info['orders_shipping_id'].'"></label>','center');

                  $tablica[] = array('<a href="sprzedaz/zamowienia_szczegoly.php?id_poz='.$info['orders_id'].'" >'.$info['orders_id'].'</a>','center');

                  $tablica[] = array($info['orders_shipping_protocol'],'center');
                  $tablica[] = array($info['orders_shipping_misc'],'center');
                  $tablica[] = array($info['orders_shipping_number'],'center');
                  $tablica[] = array($info['orders_parcels_quantity'],'center');

                  $tablica[] = array($info['orders_shipping_status'],'center');

                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['orders_shipping_date_created'])),'center');
                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['orders_shipping_date_modified'])),'center');

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['orders_id']; 
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_dhl_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=etykieta&amp;przesylka='.$info['orders_shipping_number'].'" ><b>Pobierz etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" /></a>';

                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_dhl_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=tracking&amp;przesylka='.$info['orders_shipping_number'].'"><b>Pobierz historię procesu doręczania</b><img src="obrazki/przesylka_tracking.png" alt="Pobierz historię procesu doręczania" /></a>';

                  if ( $info['orders_shipping_status'] != 'Kurier zamówiony' ) {
                      $tekst.= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_dhl_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=usun&amp;przesylka='.$info['orders_shipping_number'].'" ><b>Usuń przesyłkę w DHL</b><img src="obrazki/kasuj.png" alt="Usuń przesyłkę w DHL" /></a>';
                  }
                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_dhl_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=drop&amp;przesylka='.$info['orders_shipping_number'].'" ><b>Usuń informację z bazy</b><img src="obrazki/smietnik.png" alt="Usuń informację z bazy" /></a>';
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
              format: 'Y-m-d',
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
                 $("#page").show();
                 $("#page").html('<div style="margin:10px;"><span>Potwierdzenie odbioru na dzień:</span> <input type="text" name="data_odbioru" value="" size="12" class="datepicker1" /></div>');
                 $('input.datepicker1').Zebra_DatePicker({
                  format: 'Y-m-d',
                  inside: false,
                  readonly_element: false
                 });             
                 $("#submitBut").show();

               }
               if ( this.value == '3' ) {
                    $('#ekr_preloader').css('display','block');
                    event.preventDefault();
                    var searchIDs = $("input:checkbox:checked").map(function(){
                        return this.value;
                    }).toArray();
                    $("#page").load('sprzedaz/zamowienia_dhl_zamow_kuriera.php?IDs='+searchIDs+'', function() {
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
            
            <div id="naglowek_cont">Wysyłki DHL do zamówienia kuriera</div>

            <div id="wyszukaj">
                <form action="sprzedaz/zamowienia_wysylki_dhl.php" method="post" id="zamowieniaDHLForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj przesyłkę lub zlecenie:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? $filtr->process($_GET['szukaj']) : ''); ?>" size="30" />
                </div>  

                <div class="wyszukaj_select">
                    <span>Status:</span>
                    <?php
                    $tablia_status = Array();
                    $tablia_status[] = array('id' => '0', 'text' => 'Dowolny');
                    $tablia_status[] = array('id' => 'Utworzona', 'text' => 'Utworzona');
                    $tablia_status[] = array('id' => 'Etykieta wydrukowana', 'text' => 'Etykieta wydrukowana');
                    $tablia_status[] = array('id' => 'Kurier zamówiony', 'text' => 'Kurier zamówiony');
                    echo Funkcje::RozwijaneMenu('szukaj_status', $tablia_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : '')); ?>
                </div> 
                
                <div class="wyszukaj_select">
                        <span>Data utworzenia:</span>
                        <input type="text" id="data_dodania_od" name="szukaj_data_dodania_od" value="<?php echo ((isset($_GET['szukaj_data_dodania_od'])) ? $filtr->process($_GET['szukaj_data_dodania_od']) : ''); ?>" size="12" class="datepicker" /> do 
                        <input type="text" id="data_dodania_do" name="szukaj_data_dodania_do" value="<?php echo ((isset($_GET['szukaj_data_dodania_do'])) ? $filtr->process($_GET['szukaj_data_dodania_do']) : ''); ?>" size="12" class="datepicker" />
                </div>   

                <div class="wyszukaj_select">
                        <span>Data odbioru:</span>
                        <input type="text" id="data_odbioru" name="szukaj_data_odbioru" value="<?php echo ((isset($_GET['szukaj_data_odbioru'])) ? $filtr->process($_GET['szukaj_data_odbioru']) : ''); ?>" size="12" class="datepicker" />
                </div>   

                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true || isset($_GET['szukaj_status']) ) {
                  echo '<div id="wyszukaj_ikona"><a href="sprzedaz/zamowienia_wysylki_dhl.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
            </div>

            <form class="cmxform" method="post" id="DHLAkcjaDolna" action="sprzedaz/zamowienia_dhl_akcja.php">

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
                        <option value="3">zamów kuriera</option>
                        <option value="2">wydrukuj protokół</option>
                      </select>
                    </div>
                    
                    <div class="cl"></div>
                  
                </div>

                <div class="cl"></div>

                <div id="page" class="RamkaAkcji"></div>
                    
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
            <?php Listing::pokazAjax('sprzedaz/zamowienia_wysylki_dhl.php', $zapytanie, $ile_licznika, $ile_pozycji, 'orders_shipping_id'); ?>
            </script>              

        </div>
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
