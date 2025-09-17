<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $api = 'FURGONETKA';
    $Statusy = array();

    if ( INTEGRACJA_FURGONETKA_WLACZONY == 'tak' ) {
       $apiKurier       = new FurgonetkaRestApi(true, '', '');

    }
    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $warunki_szukania = '';

    if ( isset($_GET['szukaj']) ) {
        $szukana_wartosc = $_GET['szukaj'];
        if ( $_GET['szukaj_operator'] != '' ) {
            $warunki_szukania .= " and ( orders_shipping_number = '".$szukana_wartosc."' OR orders_shipping_misc = '".$szukana_wartosc."' )";
        }
    }
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
    FROM orders_shipping WHERE orders_shipping_type = 'FURGONETKA' " . $warunki_szukania;
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

            if ( INTEGRACJA_FURGONETKA_WLACZONY == 'tak' ) {

                if ( $Poczatek['0'] == '0' && $warunki_szukania == '' ) {

                    $Interwal = 600;
                    if ( isset($_SESSION['FurgonetkaUpd']) ) {
                        $Interwal = time() - $_SESSION['FurgonetkaUpd'];

                    }

                    if ( !isset($_SESSION['FurgonetkaUpd']) || ( $Interwal > 600 ) ) {

                        $zapytaniePrzesylki = "SELECT orders_shipping_id, orders_shipping_number, orders_shipping_status, orders_shipping_misc, orders_shipping_uuid_pickup 
                        FROM orders_shipping WHERE orders_shipping_type = 'FURGONETKA' AND DATE(orders_shipping_date_modified) > DATE_SUB(CURDATE(), INTERVAL 10 DAY)";

                        $sqlPrzesylki = $db->open_query($zapytaniePrzesylki);

                        if ( (int)$db->ile_rekordow($sqlPrzesylki) > 0) {
                          
                            while ( $infoPrzesylki = $sqlPrzesylki->fetch_assoc() ) {

                                // jezeli przesylka ma zapisany UUID zamowienie kuriera
                                if ( $infoPrzesylki['orders_shipping_uuid_pickup'] != '' ) {
                                    $Wynik = $apiKurier->commandGet('pickup-commands/'.$infoPrzesylki['orders_shipping_uuid_pickup'], true, '', false);

                                    if ( $Wynik && $Wynik->status == 'successful' ) {
                                        $pola = array();
                                        $pola = array(
                                                array('orders_shipping_uuid_pickup',''),
                                                array('orders_shipping_protocol','Oczekuje na aktualizację'),
                                                array('orders_shipping_date_modified',date('Y-m-d G:i:s', FunkcjeWlasnePHP::my_strtotime($Wynik->datetime_change)))
                                        );

                                        $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".(int)$infoPrzesylki['orders_shipping_id']."'");
                                        unset($pola);
                                    }
                                    unset($Wynik);
                                }

                                // aktualizacja statusu
                                $WynikFurgonetka = $apiKurier->commandGet('packages/'.$infoPrzesylki['orders_shipping_misc'], true, '', true);
                                    
                                if ( $WynikFurgonetka ) {

                                    $NumeryPrzesylek = '';

                                    if ( is_array($WynikFurgonetka->parcels) ) {
                                        foreach ( $WynikFurgonetka->parcels as $Przesylka ) {
                                            if ( $Przesylka->package_no != '' ) {
                                                $NumeryPrzesylek .= $Przesylka->package_no . ',';
                                            }
                                        }
                                    }
                                    if ( isset($WynikFurgonetka->state) && ( $WynikFurgonetka->state != 'waiting' && $WynikFurgonetka->state != 'cancelled' ) ) {

                                        $pola = array();
                                        $pola = array(
                                                array('orders_shipping_number',substr((string)$NumeryPrzesylek, 0, -1)),
                                                array('orders_shipping_status',$WynikFurgonetka->state)
                                        );

                                        if ( isset($WynikFurgonetka->pickup_number) ) {
                                            $pola[] = array('orders_shipping_protocol',$WynikFurgonetka->pickup_number);
                                        }
                                        if ( $WynikFurgonetka->state == 'ordered' ) {
                                            $pola[] = array('orders_shipping_uuid_order','');
                                        }

                                        $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".(int)$infoPrzesylki['orders_shipping_id']."'");
                                        unset($pola);

                                    }

                                   if ( (isset($WynikFurgonetka->state) && $WynikFurgonetka->state == 'cancelled') ) {
                                        $pola = array(
                                                array('orders_shipping_uuid_cancel',''),
                                                array('orders_shipping_uuid_pickup',''),
                                                array('orders_shipping_status',$WynikFurgonetka->state),
                                                array('orders_shipping_protocol','')
                                        );

                                        $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".(int)$infoPrzesylki['orders_shipping_id']."'");
                                        unset($pola);

                                   }
                                   unset($WynikFurgonetka);

                                }

                            }

                        }
                        $db->close_query($sqlPrzesylki);
                        unset($zapytaniePrzesylki, $infoPrzesylki);

                        $_SESSION['FurgonetkaUpd'] = time();

                    }
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
                  $tablica[] = array($info['orders_shipping_number'],'center');
                  $tablica[] = array($info['orders_shipping_misc'],'center');

                  if ( $info['orders_shipping_uuid_pickup'] == '' ) {
                    $tablica[] = array($info['orders_shipping_protocol'],'center');
                  } else {
                    $tablica[] = array('Oczekuje na aktualizację','center');
                  }
                  $tablica[] = array(( isset($Statusy[$info['orders_shipping_status']]) ? $Statusy[$info['orders_shipping_status']]: $info['orders_shipping_status'] ),'center');

                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['orders_shipping_date_created'])),'center');
                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['orders_shipping_date_modified'])),'center');

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['orders_shipping_id']; 
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  if ( INTEGRACJA_FURGONETKA_WLACZONY == 'tak' ) {

                    $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=tracking&amp;przesylka='.$info['orders_shipping_misc'].'" ><b>Pokaż historię</b><img src="obrazki/historia.png" alt="Pokaż historię" /></a>';
                    
                    $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=szczegoly&amp;przesylka='.$info['orders_shipping_misc'].'" ><b>Pokaż szczegóły</b><img src="obrazki/przesylka_tracking.png" alt="Pokaż szczegóły" /></a>';

                    if ( $info['orders_shipping_status'] == 'waiting' ) {

                        $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=usun&amp;przesylka='.$info['orders_shipping_misc'].'" ><b>Usuń przesyłkę z koszyka do wysłania</b><img src="obrazki/kasuj.png" alt="Usuń przesyłkę z koszyka do wysłania" /></a>';

                        if ( $info['orders_shipping_uuid_order'] == '' ) {
                              $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=zamow&amp;przesylka='.$info['orders_shipping_misc'].'" ><b>Zamów paczkę bez podjazdu kuriera</b><img src="obrazki/przesylka_dodaj.png" alt="Zamów paczkę bez podjazdu kuriera" /></a>';
                        } else {
                              $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=StatusZamowienia&amp;przesylka='.$info['orders_shipping_misc'].'&amp;uuid='.$info['orders_shipping_uuid_order'].'" ><b>Status zamówienia<br />UUID : '.$info['orders_shipping_uuid_order'].'</b><img src="obrazki/allegro_trwa.png" alt="Status zamówienia" /></a>';
                        }

                    } elseif ( $info['orders_shipping_status'] == 'ordered' ) {

                        if ( $info['orders_shipping_uuid_cancel'] == '' && $info['orders_shipping_uuid_order'] == '' ) {
                            $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=anuluj&amp;przesylka='.$info['orders_shipping_misc'].'" ><b>Anuluj z zamówionych</b><img src="obrazki/powrot.png" alt="Anuluj z zamówionych" /></a>';
                        } elseif ( $info['orders_shipping_uuid_cancel'] != '' && $info['orders_shipping_uuid_order'] == '' ) {
                            $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=StatusAnulowania&amp;przesylka='.$info['orders_shipping_misc'].'&amp;uuid='.$info['orders_shipping_uuid_cancel'].'" ><b>Sprawdź status anulowania</b><img src="obrazki/allegro_czeka.png" alt="Sprawdź status anulowania" /></a>';
                        }

                        if ( $info['orders_shipping_uuid_cancel'] == '' ) {
                            $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=etykieta&amp;przesylka='.$info['orders_shipping_misc'].'" ><b>Pobierz etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" /></a>';

                            if ( $info['orders_dispatch_status'] == 'D2D' || $info['orders_dispatch_status'] == 'D2P' || $info['orders_dispatch_status'] == 'P2D' ) {
                                $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=protokol&amp;przesylka='.$info['orders_shipping_misc'].'" ><b>Pobierz protokół</b><img src="obrazki/pdf_manifest.png" alt="Pobierz protokół" /></a>';
                            }
                        }

                    } elseif ( $info['orders_shipping_status'] == 'cancelled' ) {

                        $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php'.$zmienne_do_przekazania.'&amp;akcja=usunBaza&amp;przesylka='.$info['orders_shipping_misc'].'" ><b>Usuń przesyłkę z bazy sklepu</b><img src="obrazki/kasuj.png" alt="Usuń przesyłkę z bazy sklepu" /></a>';
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
                    $("#page").load('sprzedaz/zamowienia_furgonetka_zamow_kuriera.php?IDs='+searchIDs+'', function() {
                    //
                    $("#page").show();
                    $("#submitBut").show();
                    pokazChmurki();
                    $('#ekr_preloader').css('display','none');
                    //
                 });                 
               }
            });

            $("#submitBut").click(function() {
                $('#ekr_preloader').css('display','block');
            });

        });
        
        </script>

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Wysyłki FURGONETKA</div>
            <div id="wyszukaj">
                <form action="sprzedaz/zamowienia_wysylki_furgonetka.php" method="post" id="zamowieniaFURGONETKAForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj przesyłkę:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? $filtr->process($_GET['szukaj']) : ''); ?>" size="30" />
                </div>  

                <div class="wyszukaj_select">
                    <span>Operator</span>
                    <?php
                    $tablica_operator = Array();
                    $tablica_operator[] = array('id' => '0', 'text' => 'Dowolny');
                    $tablica_operator[] = array('id' => 'RUCH', 'text' => 'Ruch');
                    $tablica_operator[] = array('id' => 'POCZTA', 'text' => 'Poczta Polska');
                    $tablica_operator[] = array('id' => 'INPOST', 'text' => 'Inpost');
                    $tablica_operator[] = array('id' => 'INPOSTKURIER', 'text' => 'Inpost Kurier');
                    $tablica_operator[] = array('id' => 'DPD', 'text' => 'DPD');
                    $tablica_operator[] = array('id' => 'UPS', 'text' => 'UPS');
                    $tablica_operator[] = array('id' => 'UPSAP', 'text' => 'UPS Access point');
                    $tablica_operator[] = array('id' => 'FEDEX', 'text' => 'FedEx');
                    $tablica_operator[] = array('id' => 'GLS', 'text' => 'GLS');
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
                    $tablia_status[] = array('id' => 'waiting', 'text' => 'waiting');
                    $tablia_status[] = array('id' => 'ordered', 'text' => 'ordered');
                    $tablia_status[] = array('id' => 'cancelled', 'text' => 'cancelled');

                    echo Funkcje::RozwijaneMenu('szukaj_status', $tablia_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : '')); ?>
                </div>  

                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true || isset($_GET['szukaj_status']) ) {
                  echo '<div id="wyszukaj_ikona"><a href="sprzedaz/zamowienia_wysylki_furgonetka.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
            </div>

            <form class="cmxform" method="post" action="sprzedaz/zamowienia_furgonetka_akcja.php">

                <div id="wynik_zapytania"></div>
                <div id="aktualna_pozycja">1</div>

                <?php if ( INTEGRACJA_FURGONETKA_WLACZONY == 'tak' ) { ?>
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
                            <option value="1">pobierz etykiety</option>
                            <option value="2">pobierz protokoły</option>
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
            <?php Listing::pokazAjax('sprzedaz/zamowienia_wysylki_furgonetka.php', $zapytanie, $ile_licznika, $ile_pozycji, 'orders_shipping_id'); ?>
            </script>              

        </div>
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
