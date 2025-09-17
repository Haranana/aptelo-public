<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $api = 'APACZKA';

    $apiKurier = new ApaczkaApiV2();
    $akcja_dolna = 'false';

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $zapytanie = "SELECT *
    FROM orders_shipping WHERE orders_shipping_type = 'APACZKA'";
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

            if ( INTEGRACJA_APACZKAV2_WLACZONY == 'tak' ) {

                if ( $Poczatek['0'] == '0' ) {

                    $Interwal = 600;
                    if ( isset($_SESSION['ApaczkaUpd']) ) {
                        $Interwal = time() - $_SESSION['ApaczkaUpd'];
                    }

                    if ( !isset($_SESSION['ApaczkaUpd']) || ( $Interwal > 600 ) ) {

                        $zapytaniePrzesylki = "SELECT orders_shipping_id, orders_shipping_number, orders_shipping_status, orders_shipping_misc
                        FROM orders_shipping WHERE orders_shipping_type = 'APACZKA' AND DATE(orders_shipping_date_modified) > DATE_SUB(CURDATE(), INTERVAL 10 DAY) ORDER BY orders_shipping_date_created DESC";

                        $sqlPrzesylki = $db->open_query($zapytaniePrzesylki);

                        if ( (int)$db->ile_rekordow($sqlPrzesylki) > 0) {

                            for ( $i = 1, $c = 125; $i <= $c; $i=$i+25 ) {
                                $wynik = $apiKurier->orders( $i, 25 );
                                $Wynik = json_decode($wynik);
                                if ( isset($Wynik) && $Wynik->status == '200' && $Wynik->message == '' ) {
                                    foreach ( $Wynik->response->orders as $Przesylka ) {
                                        $pola = array(
                                                array('orders_shipping_status', $Przesylka->status),
                                                array('orders_shipping_protocol', $Przesylka->pickup_number),
                                                array('orders_dispatch_status', $Przesylka->delivered),
                                                array('orders_shipping_date_modified','now()'),
                                        );

                                        $db->update_query('orders_shipping' , $pola, " orders_shipping_misc = '".$Przesylka->id."'");
                                        unset($pola);
                                    }
                                }
                            }
                        }

                        $db->close_query($sqlPrzesylki);
                        unset($zapytaniePrzesylki, $infoPrzesylki);

                        $_SESSION['ApaczkaUpd'] = time();
                    }
                }
            }

            $zapytanie .= " limit ".$_GET['parametr'];  

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Akcja', 'center'),
                                      array('Zamówienie', 'center'),
                                      array('Kurier', 'center'),
                                      array('Numer przesyłki', 'center'),
                                      array('Data dostarczenia', 'center'),
                                      array('Status', 'center'),
                                      array('Data utworzenia', 'center'),
                                      array('Data aktualizacji', 'center')
            );
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';

            while ($info = $sql->fetch_assoc()) {
            
                  $tablica = array();
                  $zaznaczony = '';

                  $status = $info['orders_shipping_status'];

                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['orders_shipping_id']) {
                     $tekst .= '<tr class="pozycja_on">';
                   } else {
                     $tekst .= '<tr class="pozycja_off">';
                  }        

                  $tablica[] = array('<input '.$zaznaczony.' type="checkbox" style="border:0px" name="opcja[]" id="id_'.$info['orders_shipping_id'].'" value="'.$info['orders_shipping_id'].'" /><label class="OpisForPustyLabel" for="id_'.$info['orders_shipping_id'].'"></label>','center');

                  $tablica[] = array('<a href="sprzedaz/zamowienia_szczegoly.php?id_poz='.$info['orders_id'].'" >'.$info['orders_id'].'</a>','center');

                  $tablica[] = array($info['orders_shipping_comments'],'center');
                  $tablica[] = array($info['orders_shipping_number'],'center');
                  $tablica[] = array(($info['orders_dispatch_status'] != '' ? $info['orders_dispatch_status'] : '---'),'center');

                  $tablica[] = array($status,'center');

                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['orders_shipping_date_created'])),'center');
                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['orders_shipping_date_modified'])),'center');

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['orders_id']; 
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';

                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_apaczka_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=etykieta&amp;przesylka='.$info['orders_shipping_misc'].'" ><b>Pobierz etykietę i list przewozowy</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę i list przewozowy" /></a>';
                  if ( $status != 'CANCELLED' ) {
                    $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_apaczka_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=potwierdzenie&amp;przesylka='.$info['orders_shipping_misc'].'" ><b>Pobierz potwierdzenie nadania</b><img src="obrazki/proforma_pdf.png" alt="Pobierz potwierdzenie nadania" /></a>';
                  }
                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_apaczka_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=usun&amp;przesylka='.$info['orders_shipping_misc'].'" ><b>Usuń z bazy sklepu</b><img src="obrazki/smietnik.png" alt="Usuń z bazy sklepu" /></a>';


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
               if ( this.value == '1' || this.value == '2' || this.value == '3' ) {
                 $("#page").hide();
                 $("#page").load('sprzedaz/blank.php');
                 $("#submitBut").show();
               }
            });

        });
        
        </script>

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Wysyłki poprzez serwis apaczka</div>

            <form class="cmxform" method="post" action="sprzedaz/zamowienia_apaczka_akcja.php">

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
                        <!-- <option value="1">wydrukuj listy przewozowe dla zaznaczonych</option> -->
                        <option value="2">wydrukuj potwierdzenie nadania dla zaznaczonych</option>
                        <option value="3">usuń przesylki z bazy - nie usuwa w serwisie apaczka</option>
                      </select>
                    </div>
                    
                    <div class="cl"></div>
                  
                </div>

                <div class="cl"></div>

                <div id="dolny_pasek_stron"></div>
                <div id="pokaz_ile_pozycji"></div>
                <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>

                <div class="rg"><input type="submit" id="submitBut" class="przyciskBut" value="Wykonaj" style="display:none;" /></div>

                <div class="cl"></div>

            </form>

            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            <?php Listing::pokazAjax('sprzedaz/zamowienia_wysylki_apaczka.php', $zapytanie, $ile_licznika, $ile_pozycji, 'orders_shipping_id'); ?>
            </script>              

        </div>
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
