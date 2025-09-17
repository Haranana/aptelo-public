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
    if (isset($_GET['szukaj']) && $_GET['szukaj'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " AND SUBSTRING_INDEX(orders_shipping_comments,':',-1) = '".$szukana_wartosc."' ";
    }
    
    if ( isset($_GET['szukaj_data_przesylki_od']) && $_GET['szukaj_data_przesylki_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_przesylki_od'] . ' 00:00:00')));
        $warunki_szukania .= " AND orders_shipping_date_modified >= '".$szukana_wartosc."' ";
    }

    if ( isset($_GET['szukaj_data_przesylki_do']) && $_GET['szukaj_data_przesylki_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_przesylki_do'] . ' 23:59:59')));
        $warunki_szukania .= " AND orders_shipping_date_modified <= '".$szukana_wartosc."' ";
    }

    //if ( $warunki_szukania != '' ) {
    //  $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    //}

    $zapytanie = "SELECT DISTINCT SUBSTRING_INDEX(orders_shipping_comments,':',-1) AS paczka, orders_shipping_date_modified
    FROM orders_shipping
    WHERE orders_shipping_type = 'Elektroniczny Nadawca' AND orders_shipping_status != '0'
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
                $sortowanie = 'orders_shipping_date_modified desc';
                break;
            case "sort_a2":
                $sortowanie = 'orders_shipping_date_modified asc';
                break;                 
            case "sort_a3":
                $sortowanie = 'orders_shipping_number desc';
                break;
            case "sort_a4":
                $sortowanie = 'orders_shipping_number asc';
                break;
        }            
    } else { $sortowanie = 'orders_shipping_date_created desc'; }    
    
    $zapytanie .= " ORDER BY ".$sortowanie;    


    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
        
            $zapytanie .= " limit ".$_GET['parametr'];  

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(
                                      array('Numer paczki', 'center'),
                                      array('Ilość przesyłek', 'center'),
                                      array('Data wysłania', 'center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['orders_shipping_id']) {
                     $tekst .= '<tr class="pozycja_on">';
                   } else {
                     $tekst .= '<tr class="pozycja_off">';
                  }        

                  $zapytanie_tmp = "SELECT COUNT(SUBSTRING_INDEX(orders_shipping_comments,':',-1)) AS ilosc FROM orders_shipping WHERE orders_shipping_type = 'Elektroniczny Nadawca' AND SUBSTRING_INDEX(orders_shipping_comments,':',-1) = '".$info['paczka']."'";

                  $sql_tmp = $db->open_query($zapytanie_tmp);
                  $info_tmp = $sql_tmp->fetch_assoc();
                  $db->close_query($sql_tmp);
                  unset($zapytanie_tmp);        

                  $tablica = array();

                  $tablica[] = array($info['paczka'],'center');
                  $tablica[] = array($info_tmp['ilosc'],'center');
                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['orders_shipping_date_modified'])),'center');

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['paczka']; 
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_enadawca_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=etykiety&amp;idEnvelope='.$info['paczka'].'" ><b>Pobierz etykiety</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykiety" /></a>';

                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_enadawca_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=ksiazka&amp;idEnvelope='.$info['paczka'].'" ><b>Pobierz książkę nadawczą</b><img src="obrazki/ksiazka_pdf.png" alt="Pobierz książkę nadawczą" /></a>';

                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_enadawca_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=firmowa&amp;idEnvelope='.$info['paczka'].'" ><b>Zestawiania dla Firmowej Poczty</b><img src="obrazki/zamowienie_pdf.png" alt="Zestawiania dla Firmowej Poczty" /></a>';

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
            
            $.AutoUzupelnienie( 'szukaj', 'Podpowiedzi', 'ajax/autouzupelnienie_enadawca.php', 50, 180 );

            $('input.datepicker').Zebra_DatePicker({
              format: 'd-m-Y',
              inside: false,
              readonly_element: false
            });             

        });
        </script>

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Zestawienie wysyłek</div>

            <div id="wyszukaj">
                <form action="sprzedaz/zamowienia_wysylki_zestawienie.php" method="post" id="przesylkiForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj paczkę</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? $filtr->process($_GET['szukaj']) : ''); ?>" size="25" />
                </div>  
                
                <div class="wyszukaj_select">
                    <span>Data wysłania:</span>
                    <input type="text" id="data_przesylki_od" name="szukaj_data_przesylki_od" value="<?php echo ((isset($_GET['szukaj_data_przesylki_od'])) ? $filtr->process($_GET['szukaj_data_przesylki_od']) : ''); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                    <input type="text" id="data_przesylki_do" name="szukaj_data_przesylki_do" value="<?php echo ((isset($_GET['szukaj_data_przesylki_do'])) ? $filtr->process($_GET['szukaj_data_przesylki_do']) : ''); ?>" size="10" class="datepicker" />
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
                  echo '<div id="wyszukaj_ikona"><a href="sprzedaz/zamowienia_wysylki_zestawienie.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
            </div>
            
            <form action="sprzedaz/zamowienia_wysylka_enadawca_akcja.php" method="post" class="cmxform">

                <div id="sortowanie">
                
                    <span>Sortowanie: </span>
                    
                    <a id="sort_a1" class="sortowanie" href="sprzedaz/zamowienia_wysylki_zestawienie.php?sort=sort_a1">data wyslania malejąco</a>
                    <a id="sort_a2" class="sortowanie" href="sprzedaz/zamowienia_wysylki_zestawienie.php?sort=sort_a2">data wyslania rosnąco</a>
                    <a id="sort_a3" class="sortowanie" href="sprzedaz/zamowienia_wysylki_zestawienie.php?sort=sort_a3">numer nadania malejąco</a>
                    <a id="sort_a4" class="sortowanie" href="sprzedaz/zamowienia_wysylki_zestawienie.php?sort=sort_a4">numer nadania rosnąco</a>
                
                </div>             

                <div id="wynik_zapytania"></div>
                <div id="aktualna_pozycja">1</div>

                <div id="page"></div>

                <div id="dolny_pasek_stron"></div>
                <div id="pokaz_ile_pozycji"></div>
                <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>

            </form>

            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            <?php Listing::pokazAjax('sprzedaz/zamowienia_wysylki_zestawienie.php', $zapytanie, $ile_licznika, $ile_pozycji, 'orders_shipping_id'); ?>
            </script>              

        </div>
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
