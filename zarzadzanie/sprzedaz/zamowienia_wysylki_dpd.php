<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $api = 'DPD';

    $apiKurier = new DpdApi('', 'obj');

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $warunki_szukania = '';

    if ( isset($_GET['szukaj_status']) ) {
        $szukana_wartosc = $_GET['szukaj_status'];
        if ( $_GET['szukaj_status'] != '0' && $_GET['szukaj_status'] != '9999' ) {
            $warunki_szukania .= " and orders_shipping_status = '".$szukana_wartosc."'";
        }
        if ( $_GET['szukaj_status'] == '9999' ) {
            $warunki_szukania .= " and orders_shipping_status != '999'";
        }
    }
    if ( isset($_GET['szukaj_fid']) && $_GET['szukaj_fid'] != '0' ) {
        $warunki_szukania .= " and orders_shipping_misc = '".$_GET['szukaj_fid']."'";
    }

    $zapytanie = "SELECT *
    FROM orders_shipping WHERE orders_shipping_type = 'DPD' " . $warunki_szukania;
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
                                      array('FID', 'center'),
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

                  if ( $info['orders_shipping_status'] == '1' ) {
                      $status = 'Utworzona';
                  } elseif ( $info['orders_shipping_status'] == '2' ) {
                      $status = 'Etykieta wydrukowana';
                      //$zaznaczony = 'checked="checked"';
                  } elseif ( $info['orders_shipping_status'] == '3' ) {
                      $status = 'Protokół wydrukowany';
                  } elseif ( $info['orders_shipping_status'] == '999' ) {
                      $status = 'Kurier zamówiony';
                  }

                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['orders_shipping_id']) {
                     $tekst .= '<tr class="pozycja_on">';
                   } else {
                     $tekst .= '<tr class="pozycja_off">';
                  }        

                  $tablica[] = array('<input '.$zaznaczony.' type="checkbox" style="border:0px" name="opcja[]" id="id_'.$info['orders_shipping_id'].'" value="'.$info['orders_shipping_id'].'" /><label class="OpisForPustyLabel" for="id_'.$info['orders_shipping_id'].'"></label>','center');

                  $tablica[] = array('<a href="sprzedaz/zamowienia_szczegoly.php?id_poz='.$info['orders_id'].'" >'.$info['orders_id'].'</a>','center');

                  $tablica[] = array($info['orders_shipping_comments'],'center');
                  $tablica[] = array($info['orders_shipping_misc'],'center');
                  $tablica[] = array($info['orders_shipping_number'],'center');
                  $tablica[] = array($info['orders_parcels_quantity'],'center');

                  $tablica[] = array($status,'center');

                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['orders_shipping_date_created'])),'center');
                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['orders_shipping_date_modified'])),'center');

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['orders_id']; 
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_dpd_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=etykieta&amp;przesylka='.$info['orders_shipping_comments'].'&amp;destination='.$info['orders_shipping_to_country'].'" ><b>Pobierz etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykiety" /></a>';

                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_dpd_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=protokol&amp;przesylka='.$info['orders_shipping_comments'].'&amp;fid='.$info['orders_shipping_misc'].'" ><b>Pobierz protokół</b><img src="obrazki/proforma_pdf.png" alt="Pobierz protokół" /></a>';

                  if ( $info['orders_shipping_status'] != 999 ) {
                      $tekst.= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_dpd_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=usun&amp;przesylka='.$info['orders_shipping_comments'].'" ><b>Usuń przesyłkę</b><img src="obrazki/kasuj.png" alt="Usuń przesyłkę" /></a>';
                  } else {
                      $tekst.= '<img src="obrazki/kasuj_off.png" alt="Usuń przesyłkę" />';
                  }
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
                 $("#page").load('sprzedaz/zamowienia_dpd_zamow_kuriera.php<?php echo ( isset($_GET['szukaj_fid']) ? "?fid=".$_GET['szukaj_fid'] : "?fid=".INTEGRACJA_DPD_FID);?>', function() {
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
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Wysyłki DPD do zamówienia kuriera</div>

            <div id="wyszukaj">
                <form action="sprzedaz/zamowienia_wysylki_dpd.php" method="post" id="zamowieniaDPDForm" class="cmxform">

                <div class="wyszukaj_select">
                    <span>Status:</span>
                    <?php
                    $tablia_status = Array();
                    $tablia_status[] = array('id' => '0', 'text' => 'Dowolny');
                    $tablia_status[] = array('id' => '9999', 'text' => 'Do zamówienia kuriera');
                    $tablia_status[] = array('id' => '1', 'text' => 'Przesyłka utworzona');
                    $tablia_status[] = array('id' => '2', 'text' => 'Etykieta wydrukowana');
                    $tablia_status[] = array('id' => '3', 'text' => 'Protokół wydrukowany');
                    $tablia_status[] = array('id' => '999', 'text' => 'Kurier zamówiony');
                    echo Funkcje::RozwijaneMenu('szukaj_status', $tablia_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : '')); ?>
                    <span>FID:</span>
                    <?php
                    $tablia_fid = Array();
                    $tablia_fid[] = array('id' => '0', 'text' => 'Dowolny');
                    $tablia_fid[] = array('id' => $apiKurier->polaczenie['INTEGRACJA_DPD_FID'], 'text' => $apiKurier->polaczenie['INTEGRACJA_DPD_FID']);
                    if ( $apiKurier->polaczenie['INTEGRACJA_DPD_DRUGI_FID'] != '' ) {
                        $tablia_fid[] = array('id' => $apiKurier->polaczenie['INTEGRACJA_DPD_DRUGI_FID'], 'text' => $apiKurier->polaczenie['INTEGRACJA_DPD_DRUGI_FID']);
                    }
                    echo Funkcje::RozwijaneMenu('szukaj_fid', $tablia_fid, ((isset($_GET['szukaj_fid'])) ? $filtr->process($_GET['szukaj_fid']) : '')); ?>
                </div>  

                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true || isset($_GET['szukaj_status']) || isset($_GET['szukaj_fid']) ) {
                  echo '<div id="wyszukaj_ikona"><a href="sprzedaz/zamowienia_wysylki_dpd.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
            </div>

            <form class="cmxform" method="post" action="sprzedaz/zamowienia_dpd_akcja.php">

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
                        <option value="1">wydrukuj etykiety</option>
                        <option value="2">wydrukuj protokół</option>
                        <option value="3">zamów kuriera</option>
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

                <?php 
                if ( isset($_GET['szukaj_fid']) ) {
                    echo '<input type="hidden" name="fid" value="'.$_GET['szukaj_fid'].'" />';
                } else {
                    echo '<input type="hidden" name="fid" value="0" />';
                }
                ?>

                <div class="rg"><input type="submit" id="submitBut" class="przyciskBut" value="Wykonaj" style="display:none;" /></div>

                <?php //} ?>


                <div class="cl"></div>

            </form>

            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            <?php Listing::pokazAjax('sprzedaz/zamowienia_wysylki_dpd.php', $zapytanie, $ile_licznika, $ile_pozycji, 'orders_shipping_id'); ?>
            </script>              

        </div>
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
