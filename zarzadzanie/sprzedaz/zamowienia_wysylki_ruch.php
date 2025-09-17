<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $api = 'ORLEN PACZKA';

    $apiKurier = new PaczkaRuchApi();

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $warunki_szukania = '';

    
    if ( isset($_GET['szukaj_status']) ) {
        $szukana_wartosc = $_GET['szukaj_status'];
        if ( $_GET['szukaj_status'] != '0' ) {
            $warunki_szukania .= " and orders_shipping_status = '".$szukana_wartosc."'";
        }
    }
    

    $zapytanie = "SELECT *
    FROM orders_shipping WHERE orders_shipping_type = 'PACZKA W RUCHU' OR orders_shipping_type = 'ORLEN PACZKA' " . $warunki_szukania;
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
                                      array('Numer paczki', 'center'),
                                      array('Etykieta', 'center'),
                                      array('Status', 'center'),
                                      array('Data utworzenia', 'center'),
                                      array('Data aktualizacji', 'center')
            );
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  $tablica = array();
                  $zaznaczony = '';

                  $status = Funkcje::PokazStatusRuch($info['orders_shipping_status']);

                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['orders_shipping_id']) {
                     $tekst .= '<tr class="pozycja_on">';
                   } else {
                     $tekst .= '<tr class="pozycja_off">';
                  }        

                  $tablica[] = array('<input '.$zaznaczony.' type="checkbox" style="border:0px" name="opcja[]" id="id_'.$info['orders_shipping_id'].'" value="'.$info['orders_shipping_id'].'" /><label class="OpisForPustyLabel" for="id_'.$info['orders_shipping_id'].'"></label>','center');

                  $tablica[] = array('<a href="sprzedaz/zamowienia_szczegoly.php?id_poz='.$info['orders_id'].'" >'.$info['orders_id'].'</a>','center');

                  $tablica[] = array($info['orders_shipping_number'],'center');

                  if (is_dir(KATALOG_SKLEPU . 'zarzadzanie/tmp/RUCH/'.$info['orders_shipping_number'])) {
                      if ($handle = opendir(KATALOG_SKLEPU . 'zarzadzanie/tmp/RUCH/'.$info['orders_shipping_number'])) {
                        while (false !== ($entry = readdir($handle))) {
                            if ($entry != "." && $entry != "..") {
                                $tablica[] = array('<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_paczka_ruch_akcja.php?id_poz='.(int)$info['orders_id'].'&amp;zakladka=1&amp;akcja=pobierz&amp;przesylka='.$info['orders_shipping_number'].'&amp;paczka='.$entry.'" ><b>Pobierz plik etykiety PDF</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz plik etykiety PDF" /></a>','center');
                            }
                        }
                        closedir($handle);
                      }
                  } else {
                    $tablica[] = array('---','center');
                  }

                  $tablica[] = array($status,'center');

                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['orders_shipping_date_created'])),'center');
                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['orders_shipping_date_modified'])),'center');

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['orders_id']; 
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  if ( $info['orders_shipping_status'] != '201' ) {
                    $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_paczka_ruch_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=etykieta&amp;przesylka='.$info['orders_shipping_number'].'" ><b>Pobierz duplikat etykiety</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz duplikat etykiety" /></a>';

                    $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_paczka_ruch_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=protokol&amp;przesylka='.$info['orders_shipping_number'].'" ><b>Pobierz protokół</b><img src="obrazki/proforma_pdf.png" alt="Pobierz książkę nadawczą" /></a>';
                  }

                  if ( $info['orders_shipping_status'] == '200' ) {
                            $tekst.= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_paczka_ruch_akcja.php?id_poz='.(int)$info['orders_id'].'&amp;zakladka=1&amp;akcja=usun&amp;przesylka='.$info['orders_shipping_number'].'" ><b>Anuluj przesyłkę</b><img src="obrazki/przesylka_anuluj.png" alt="Anuluj przesyłkę" /></a>';
                  }

                  if ( $info['orders_shipping_status'] == '201' ) {
                      $tekst.= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_paczka_ruch_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=usun&amp;przesylka='.$info['orders_shipping_number'].'" ><b>Usuń przesyłkę</b><img src="obrazki/kasuj.png" alt="Usuń przesyłkę" /></a>';
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
               if ( this.value != '0' ) {
                 $("#page").hide();
                 $("#page").load('sprzedaz/blank.php');
                 $("#submitBut").show();
               }
            });

        });
        
        </script>

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Wysyłki ORLEN Paczka</div>

            <div id="wyszukaj">
                <form action="sprzedaz/zamowienia_wysylki_ruch.php" method="post" id="zamowieniaRUCHForm" class="cmxform">

                <div class="wyszukaj_select">
                    <span>Status:</span>
                    <?php
                    $tablia_status = Array();

                    $tablia_status[] = array('id' => '0', 'text' => 'Dowolny');
                    $tablia_status[] = array('id' => '200', 'text' => 'W Transporcie od Nadawcy');
                    $tablia_status[] = array('id' => '100', 'text' => 'W sortowni regionalnej');
                    $tablia_status[] = array('id' => '110', 'text' => 'W Transporcie do SC z Ekspedycji');
                    $tablia_status[] = array('id' => '201', 'text' => 'Anulowane awizo');
                    $tablia_status[] = array('id' => '210', 'text' => 'Nadana w Kiosku');
                    $tablia_status[] = array('id' => '230', 'text' => 'W Transporcie do Ekspedycji z Kiosku');
                    $tablia_status[] = array('id' => '300', 'text' => 'W sortowni centralnej');
                    $tablia_status[] = array('id' => '400', 'text' => 'W sortowni centralnej');
                    $tablia_status[] = array('id' => '450', 'text' => 'W Transporcie do Ekspedycji z SC');
                    $tablia_status[] = array('id' => '653', 'text' => 'W Ekspedycji');
                    $tablia_status[] = array('id' => '680', 'text' => 'W Transporcie do Kiosku');
                    $tablia_status[] = array('id' => '690', 'text' => 'W Kiosku');
                    $tablia_status[] = array('id' => '695', 'text' => 'W kiosku SMS wyslany');
                    $tablia_status[] = array('id' => '700', 'text' => 'Nieodebrana w Terminie');
                    $tablia_status[] = array('id' => '709', 'text' => 'Powrót - Nieodebrana w Terminie');
                    $tablia_status[] = array('id' => '729', 'text' => 'Powrót - Niepoprawny Kiosk');
                    $tablia_status[] = array('id' => '749', 'text' => 'Reklamacja');
                    $tablia_status[] = array('id' => '790', 'text' => 'Zwrot do Ekspedycji');
                    $tablia_status[] = array('id' => '800', 'text' => 'Zwrot do Sortowni');
                    $tablia_status[] = array('id' => '900', 'text' => 'Zwrot do Nadawcy');
                    $tablia_status[] = array('id' => '1000', 'text' => 'Odebrana przez Klienta');
                    $tablia_status[] = array('id' => '1100', 'text' => 'Odebrana');

                    echo Funkcje::RozwijaneMenu('szukaj_status', $tablia_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : '')); ?>
                </div>  

                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true || isset($_GET['szukaj_status']) ) {
                  echo '<div id="wyszukaj_ikona"><a href="sprzedaz/zamowienia_wysylki_ruch.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
            </div>

            <form class="cmxform" method="post" action="sprzedaz/zamowienia_ruch_akcja.php">

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
                        <option value="1">wydrukuj protokół</option>
                        <option value="3">usuń wpisy z bazy</option>
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
            <?php Listing::pokazAjax('sprzedaz/zamowienia_wysylki_ruch.php', $zapytanie, $ile_licznika, $ile_pozycji, 'orders_shipping_id'); ?>
            </script>              

        </div>
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
