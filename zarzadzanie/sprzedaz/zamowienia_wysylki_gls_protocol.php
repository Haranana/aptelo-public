<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $api = 'GLS';

    $apiKurier = new GlsApi();
    $akcja_dolna = 'false';

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && $_GET['szukaj'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " AND gls_protocol_number = '".$szukana_wartosc."' ";
    }
    
    if ( isset($_GET['szukaj_data_przesylki_od']) && $_GET['szukaj_data_przesylki_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_przesylki_od'] . ' 00:00:00')));
        $warunki_szukania .= " AND gls_protocol_date_added >= '".$szukana_wartosc."' ";
    }

    if ( isset($_GET['szukaj_data_przesylki_do']) && $_GET['szukaj_data_przesylki_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_przesylki_do'] . ' 23:59:59')));
        $warunki_szukania .= " AND gls_protocol_date_added <= '".$szukana_wartosc."' ";
    }

    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }

    $zapytanie = "SELECT *
    FROM orders_shipping_gls_protocol " . $warunki_szukania;    

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
                $sortowanie = 'gls_protocol_date_added DESC';
                break;
            case "sort_a2":
                $sortowanie = 'gls_protocol_date_added ASC';
                break;                 
            case "sort_a3":
                $sortowanie = 'gls_protocol_number DESC';
                break;
            case "sort_a4":
                $sortowanie = 'gls_protocol_number ASC';
                break;
        }            
    } else { $sortowanie = 'gls_protocol_date_added DESC'; }    
    
    $zapytanie .= " ORDER BY ".$sortowanie;    

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
        
            $zapytanie .= " limit ".$_GET['parametr'];  

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Akcja', 'center'),
                                      array('ID', 'center'),
                                      array('Numer protokołu', 'center'),
                                      array('Ilość paczek', 'center'),
                                      array('Waga paczek', 'center'),
                                      array('Data podjazdu', 'center'),
                                      array('Data utworzenia', 'center')
            );
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';

            while ($info = $sql->fetch_assoc()) {
            
                  $tablica = array();
                  $zaznaczony = '';

                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['gls_id']) {
                     $tekst .= '<tr class="pozycja_on">';
                   } else {
                     $tekst .= '<tr class="pozycja_off">';
                  }        

                  $tablica[] = array('<input ' . ( $info['gls_protocol_date_order'] != '0000-00-00 00:00:00' ? 'disabled="disabled"' : '' ) . ' type="checkbox" style="border:0px" name="opcja[]" id="id_'.$info['gls_id'].'" value="'.$info['gls_id'].'" /><label class="OpisForPustyLabel" for="id_'.$info['gls_id'].'"></label>','center');

                  $tablica[] = array($info['gls_id'],'center');
                  $tablica[] = array($info['gls_protocol_number'],'center');
                  $tablica[] = array($info['gls_protocol_quantity'],'center');
                  $tablica[] = array($info['gls_protocol_weight'],'center');

                  if ( $info['gls_protocol_date_order'] != '0000-00-00 00:00:00' ) {
                    $tablica[] = array(date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['gls_protocol_date_order'])),'center');
                  } else {
                    $tablica[] = array('---','center');
                  }
                  $tablica[] = array(date('d-m-Y H:i:s',FunkcjeWlasnePHP::my_strtotime($info['gls_protocol_date_added'])),'center');
                  //$tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['orders_shipping_date_modified'])),'center');

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['gls_id']; 
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';

                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_gls_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=etykietaMulti&amp;przesylka='.$info['gls_protocol_number'].'" ><b>Pobierz etykiety dla protokołu</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykiety dla protokołu" /></a>';

                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_gls_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=potwierdzenieDruk&amp;przesylka='.$info['gls_protocol_number'].'" ><b>Wydrukuj potwierdzenie nadania</b><img src="obrazki/proforma_pdf.png" alt="Wydrukuj potwierdzenie nadania" /></a>';

                  //$tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylki_gls_parcels.php?protokol_id='.$info['gls_id'].'&amp;protokol_numer='.$info['gls_protocol_number'].'" ><b>Wykaz paczek</b><img src="obrazki/lista_wojewodztw.png" alt="Wykaz paczek" /></a>';

                  //$tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_gls_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=protokolInfo&amp;przesylka='.$info['gls_protocol_number'].'" ><b>TEST</b><img src="obrazki/proforma_pdf.png" alt="TEST" /></a>';

                  
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

               if ( this.value == '10' ) {
                 $("#page").load('sprzedaz/zamowienia_gls_zamow_kuriera.php', function() {
                    //
                    $("#page").show();
                    $("#submitBut").show();
                    pokazChmurki();
                    //
                 });                 
               }
            });

        });
        </script>

        <div id="caly_listing">
        
            <div id="naglowek_cont">Protokoły wysyłek GLS</div>

            <div id="ajax"></div>

            <div id="wyszukaj">
                <form action="sprzedaz/zamowienia_wysylki_gls_protocol.php" method="post" id="przesylkiForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj protokół</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? $filtr->process($_GET['szukaj']) : ''); ?>" size="25" />
                </div>  
                
                <div class="wyszukaj_select">
                    <span>Data utworzenia:</span>
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
                  echo '<div id="wyszukaj_ikona"><a href="sprzedaz/zamowienia_wysylki_gls_protocol.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
            </div>
            
            <div id="sortowanie">
                
                    <span>Sortowanie: </span>
                    
                    <a id="sort_a1" class="sortowanie" href="sprzedaz/zamowienia_wysylki_gls_protocol.php?sort=sort_a1">data wysłania malejąco</a>
                    <a id="sort_a2" class="sortowanie" href="sprzedaz/zamowienia_wysylki_gls_protocol.php?sort=sort_a2">data wysłania rosnąco</a>
                    <a id="sort_a3" class="sortowanie" href="sprzedaz/zamowienia_wysylki_gls_protocol.php?sort=sort_a3">numer protokołu malejąco</a>
                    <a id="sort_a4" class="sortowanie" href="sprzedaz/zamowienia_wysylki_gls_protocol.php?sort=sort_a4">numer protokołu rosnąco</a>
                
            </div>             

            <form class="cmxform" method="post" action="sprzedaz/zamowienia_gls_akcja.php">

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
                        <option value="10">zamów podjazd kuriera</option>
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

                <div class="rg"><input type="submit" id="submitBut" class="przyciskBut" value="Wykonaj" style="display:none;" /></div>

                <div class="cl"></div>

            </form>

            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            <?php Listing::pokazAjax('sprzedaz/zamowienia_wysylki_gls_protocol.php', $zapytanie, $ile_licznika, $ile_pozycji, 'gls_id'); ?>
            </script>              

        </div>
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
