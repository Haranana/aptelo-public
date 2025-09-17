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

    if ( isset($_GET['szukaj_status']) ) {
        if ( $_GET['szukaj_status'] != '0' ) {
            $szukana_wartosc = explode('|', (string)$_GET['szukaj_status']);
            if ( count($szukana_wartosc) > 1 ) {
                $warunki_szukania .= " and ( orders_shipping_status = '".$szukana_wartosc['0']."' OR orders_shipping_status = '".$szukana_wartosc['1']."' )";
            } else {
                $warunki_szukania .= " and orders_shipping_status = '".$szukana_wartosc['0']."'";
            }
        }
    }

    if ( isset($_GET['szukaj_data_przesylki_od']) && $_GET['szukaj_data_przesylki_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_przesylki_od'] . ' 00:00:00')));
        $warunki_szukania .= " AND orders_shipping_date_created >= '".$szukana_wartosc."' ";
    }

    if ( isset($_GET['szukaj_data_przesylki_do']) && $_GET['szukaj_data_przesylki_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_przesylki_do'] . ' 23:59:59')));
        $warunki_szukania .= " AND orders_shipping_date_created <= '".$szukana_wartosc."' ";
    }

    $zapytanie = "SELECT *
    FROM orders_shipping WHERE orders_shipping_type = 'GLS'" . $warunki_szukania;;
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
                                      array('Numer ID', 'center'),
                                      array('Numer przesyłki', 'center'),
                                      array('Ilość paczek', 'center'),
                                      array('Status', 'center'),
                                      array('Protokół', 'center'),
                                      array('Etykieta', 'center'),
                                      array('Data utworzenia', 'center'),
                                      array('Data aktualizacji', 'center')
            );
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';

            while ($info = $sql->fetch_assoc()) {
            
                  $tablica = array();
                  if ( $info['orders_shipping_status'] == '1' || $info['orders_shipping_status'] == '11' ) {
                      $status = 'W przygotowalni';
                  } elseif ( $info['orders_shipping_status'] == '2' || $info['orders_shipping_status'] == '21' ) {
                      $status = 'Zatwierdzona';
                  } elseif ( $info['orders_shipping_status'] == '9999' ) {
                      $status = 'Brak w bazie GLS';
                  }

                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['orders_shipping_id']) {
                     $tekst .= '<tr class="pozycja_on">';
                   } else {
                     $tekst .= '<tr class="pozycja_off">';
                  }        

                  $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" id="id_'.$info['orders_shipping_id'].'" value="'.$info['orders_shipping_id'].'" /><label class="OpisForPustyLabel" for="id_'.$info['orders_shipping_id'].'"></label>','center');

                  $tablica[] = array('<a href="sprzedaz/zamowienia_szczegoly.php?id_poz='.$info['orders_id'].'" >'.$info['orders_id'].'</a>','center');

                  $tablica[] = array($info['orders_shipping_comments'],'center');
                  if ( $info['orders_shipping_status'] == '2' || $info['orders_shipping_status'] == '21' ) {
                      $tablica[] = array(str_replace(',', '<br />', (string)$info['orders_shipping_number']),'center');
                  } else {
                      $tablica[] = array('---','center');
                  }
                  $tablica[] = array($info['orders_parcels_quantity'],'center');

                  $tablica[] = array($status,'center');

                  if ( $info['orders_shipping_protocol'] != '' ) {
                  $tablica[] = array($info['orders_shipping_protocol'],'center');
                  } else {
                    $tablica[] = array('---','center');
                  }

                  if ( $info['orders_shipping_status'] == '21' || $info['orders_shipping_status'] == '11' ) { $obraz = '<em class="TipChmurka"><b>Etykieta wydrukowana</b><img src="obrazki/aktywny_on.png" alt="Etykieta wydrukowana" /></em>'; } else { $obraz = '-'; }              
                  $tablica[] = array($obraz,'center');                                      

                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['orders_shipping_date_created'])),'center');
                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['orders_shipping_date_modified'])),'center');

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['orders_id']; 
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';

                  if ( $info['orders_shipping_status'] == '1' || $info['orders_shipping_status'] == '11' ) {
                      $tekst.= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_gls_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=usun&amp;przesylka='.$info['orders_shipping_comments'].'" ><b>Usuń przesyłkę z przechowalni</b><img src="obrazki/kasuj.png" alt="Usuń przesyłkę z przechowalni" /></a>';
                  }
                  if ( $info['orders_shipping_status'] == '1' || $info['orders_shipping_status'] == '11' ) {
                    $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_gls_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=etykieta&amp;przesylka='.$info['orders_shipping_comments'].'" ><b>Pobierz etykietę dla pojedynczej przesyłki</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę dla pojedynczej przesyłki" /></a>';
                  } elseif ( $info['orders_shipping_status'] == '2' || $info['orders_shipping_status'] == '21' ) {
                    $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_gls_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=etykietaNumer&amp;przesylka='.$info['orders_shipping_number'].'" ><b>Pobierz etykietę dla pojedynczej przesyłki</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę dla pojedynczej przesyłki" /></a>';
                  }

                  if ( $info['orders_shipping_status'] == '1' || $info['orders_shipping_status'] == '11' ) {
                     $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_gls_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=potwierdzenie&amp;przesylka='.$info['orders_shipping_comments'].'" ><b>Utwórz potwierdzenia nadania dla pojedynczej przesyłki</b><img src="obrazki/przesylka_dodaj.png" alt="Utwórz potwierdzenia nadania dla pojedynczej przesyłki" /></a>';
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
            });

        });
        
        </script>

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Wysyłki GLS</div>

            <div id="wyszukaj">
                <form action="sprzedaz/zamowienia_wysylki_gls.php" method="post" id="zamowieniaGLSForm" class="cmxform">

                <div class="wyszukaj_select">
                    <span>Status:</span>
                    <?php
                    $tablia_status = Array();
                    $tablia_status[] = array('id' => '0', 'text' => 'Dowolny');
                    $tablia_status[] = array('id' => '2|21', 'text' => 'Zatwierdzona');
                    $tablia_status[] = array('id' => '1|11', 'text' => 'W przygotowalni');
                    $tablia_status[] = array('id' => '9999', 'text' => 'Brak w bazie GLS');
                    echo Funkcje::RozwijaneMenu('szukaj_status', $tablia_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : '')); ?>
                </div>  

                <div class="wyszukaj_select">
                    <span>Data utworzenia:</span>
                    <input type="text" id="data_przesylki_od" name="szukaj_data_przesylki_od" value="<?php echo ((isset($_GET['szukaj_data_przesylki_od'])) ? $filtr->process($_GET['szukaj_data_przesylki_od']) : ''); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                    <input type="text" id="data_przesylki_do" name="szukaj_data_przesylki_do" value="<?php echo ((isset($_GET['szukaj_data_przesylki_do'])) ? $filtr->process($_GET['szukaj_data_przesylki_do']) : ''); ?>" size="10" class="datepicker" />
                </div>  

                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true || isset($_GET['szukaj_status']) || isset($_GET['szukaj_fid']) ) {
                  echo '<div id="wyszukaj_ikona"><a href="sprzedaz/zamowienia_wysylki_gls.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
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
                        <option value="1">utwórz potwierdzenie nadania dla zaznaczonych</option>
                        <option value="2">wydrukuj etykiety dla zaznaczonych (tylko dla zatwierdzonych)</option>
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
            <?php Listing::pokazAjax('sprzedaz/zamowienia_wysylki_gls.php', $zapytanie, $ile_licznika, $ile_pozycji, 'orders_shipping_id'); ?>
            </script>              

        </div>
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
