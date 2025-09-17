<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $api = 'INPOST';

    $apiKurier = new InPostShipX();
    $akcja_dolna = 'false';

    $Statusy = $apiKurier->GetRequest('/v1/statuses', '');

    $Nadania = $apiKurier->SposobNadaniaTablica();

    $SposobyNadania = $apiKurier->SposobNadania();

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $warunki_szukania = '';

    if ( isset($_GET['szukaj_status']) ) {
        $szukana_wartosc = $_GET['szukaj_status'];
        if ( $_GET['szukaj_status'] != '0' ) {
            $warunki_szukania .= " and orders_shipping_status = '".$szukana_wartosc."'";
        }
    }
    if ( isset($_GET['szukaj_serwis']) ) {
        $szukana_wartosc = $_GET['szukaj_serwis'];
        if ( $_GET['szukaj_serwis'] != '0' ) {
            $warunki_szukania .= " and orders_shipping_comments = '".$szukana_wartosc."'";
        }
    }
    if ( isset($_GET['szukaj_nadanie']) ) {
        $szukana_wartosc = $_GET['szukaj_nadanie'];
        if ( $_GET['szukaj_nadanie'] != '0' ) {
            $warunki_szukania .= " and orders_shipping_packages = '".$szukana_wartosc."'";
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
    

    $zapytanie = "SELECT *
    FROM orders_shipping WHERE orders_shipping_type = 'INPOST' AND orders_shipping_misc = 'SHIPX' " . $warunki_szukania;
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

            $CzasUtworzenia = time() - ( 24*7*3600 );
            $Poczatek = explode(',', (string)$_GET['parametr']);
            if ( $Poczatek['0'] == '0' && $warunki_szukania == '' ) {

                $Przesylki = $apiKurier->GetRequest('v1/organizations', $apiKurier->polaczenie['INTEGRACJA_KURIER_INPOST_SHIPX_ORGANIZATION_ID'].'/shipments?created_at_gteq='.$CzasUtworzenia);

                if ( !isset($Przesylki->status) ) {
                    if ( is_object($Przesylki) && isset($Przesylki->items) & count($Przesylki->items) > 0 ) {
                        foreach ( $Przesylki->items as $Przesylka ) {
                            //echo $Przesylka->id . ' - ' . $Przesylka->status . '<br>';
                            $pola = array();
                            $pola = array(
                                          array('orders_shipping_number',$Przesylka->tracking_number),
                                          array('orders_shipping_status',$Przesylka->status),
                                          array('orders_shipping_date_modified',date('Y-m-d G:i:s', FunkcjeWlasnePHP::my_strtotime($Przesylka->updated_at)))
                            );

                            $db->update_query('orders_shipping' , $pola, " orders_shipping_protocol = '".$Przesylka->id."'");
                            unset($pola);
                        }
                    }
                //} else {
                //    print_r($Przesylki->status);
                }
            }

            $zapytanie .= " limit ".$_GET['parametr'];  

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Akcja', 'center'),
                                      array('Zamówienie', 'center'),
                                      array('Serwis', 'center'),
                                      array('Nadanie', 'center'),
                                      array('Numer przesyłki', 'center'),
                                      array('Ilość paczek', 'center'),
                                      array('Status/Zlecenie', 'center'),
                                      array('Data utworzenia', 'center'),
                                      array('Data aktualizacji', 'center')
            );
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';

            while ($info = $sql->fetch_assoc()) {

                  if ( $info['orders_dispatch_status'] == '' ) {
                    $status = $apiKurier->StatusPrzesylki($info['orders_shipping_status'], $Statusy);
                  } else {
                    $status = $apiKurier->StatusOdbioru( $info['orders_dispatch_status'] );
                  }

                  $tablica = array();
                  $zaznaczony = '';

                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['orders_shipping_id']) {
                     $tekst .= '<tr class="pozycja_on">';
                   } else {
                     $tekst .= '<tr class="pozycja_off">';
                  }        

                  $tablica[] = array('<input '.$zaznaczony.' type="checkbox" style="border:0px" name="opcja[]" id="id_'.$info['orders_shipping_id'].'" value="'.$info['orders_shipping_id'].'" /><label class="OpisForPustyLabel" for="id_'.$info['orders_shipping_id'].'"></label>','center');

                  $tablica[] = array('<a href="sprzedaz/zamowienia_szczegoly.php?id_poz='.$info['orders_id'].'&zakladka=1" >'.$info['orders_id'].'</a>','center');

                  $tablica[] = array($info['orders_shipping_comments'],'center');
                  $tablica[] = array($info['orders_shipping_packages'].'<em class="TipIkona"><b>'.$Nadania[$info['orders_shipping_packages']].'</b></em>','center');
                  $tablica[] = array($info['orders_shipping_number'],'center');
                  $tablica[] = array($info['orders_parcels_quantity'],'center');

                  $tablica[] = array($status,'center');

                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['orders_shipping_date_created'])),'center');
                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['orders_shipping_date_modified'])),'center');

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['orders_id']; 
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';

                  $StatusPozycja = array_search($info['orders_shipping_status'], array_column($Statusy->items, 'name'));

                  if  ( $StatusPozycja < 2 ) {
                    $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_inpost_shipx_akcja.php?id_poz='.(int)$info['orders_shipping_id'].'&amp;zakladka=1&amp;&amp;akcja=usun&amp;przesylka='.$info['orders_shipping_protocol'].'" ><b>Anuluj przesyłkę</b><img src="obrazki/kasuj.png" alt="Anuluj przesyłkę" /></a>';
                  }
                  if ( $StatusPozycja > 2 ) {
                    $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_inpost_shipx_akcja.php?id_poz='.(int)$info['orders_id'].'&amp;zakladka=1&amp;&amp;akcja=etykieta&amp;przesylka='.$info['orders_shipping_protocol'].'" ><b>Drukuj etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Drukuj etykietę" /></a>';
                  }
                  if ( $info['orders_shipping_packages'] != 'parcel_locker' && $StatusPozycja > 2 ) {
                    $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_inpost_shipx_akcja.php?id_poz='.(int)$info['orders_id'].'&amp;zakladka=1&amp;akcja=potwierdzenie&amp;przesylka='.$info['orders_shipping_protocol'].'" ><b>Drukuj potwierdzenie odbioru</b><img src="obrazki/proforma_pdf.png" alt="Drukuj potwierdzenie odbioru" /></a>';
                  }
                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_inpost_shipx_akcja.php?id_poz='.(int)$info['orders_shipping_id'].'&amp;zakladka=1&amp;akcja=usun_baza&amp;przesylka='.$info['orders_shipping_protocol'].'" ><b>Usuń informacje z bazy sklepu</b><img src="obrazki/kasuj_dysk.png" alt="Usuń informacje z bazy sklepu" /></a>';

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
                 $("#page").slideUp();
                 $("#page").load('sprzedaz/blank.php');
                 $("#submitBut").hide();
               }
               if ( this.value == '1' || this.value == '2' ) {
                 $("#page").slideUp();
                 $("#page").load('sprzedaz/blank.php');
                 $("#submitBut").show();
               }
               if ( this.value == '3' ) {
                    //
                    $("#page").slideDown();
                    $("#submitBut").show();
                    //
               }
            });

                $("#apiForm").validate({
                  rules: {
                    komentarz         : { required: true }
                  },
                  messages: {
                    komentarz: {
                      required: "Pole jest wymagane"
                    }
                  }
                });
        });
        
        </script>

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Wysyłki poprzez serwis INPOST</div>

            <div id="wyszukaj">
                <form action="sprzedaz/zamowienia_wysylki_inpost.php" method="post" id="zamowieniaDPDForm" class="cmxform">

                    <div class="wyszukaj_select">
                        <span>Status:</span>
                        <?php

                        $tablica_status = Array();

                        $tablica_status[] = array('id' => '0', 'text' => 'Dowolny');
                        foreach ( $Statusy->items as $Status ) {
                            $tablica_status[] = array('id' => $Status->name, 'text' => $Status->title);
                        }

                        echo Funkcje::RozwijaneMenu('szukaj_status', $tablica_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : ''));
                        ?>
                    </div>

                    <div class="wyszukaj_select">
                        <span>Serwis:</span>
                        <?php
                        $tablia_serwis = Array();
                        $tablia_serwis[] = array('id' => '0', 'text' => 'Dowolny');
                        $tablia_serwis[] = array('id' => 'Paczkomaty', 'text' => 'Paczkomaty');
                        $tablia_serwis[] = array('id' => 'Kurier', 'text' => 'Kurier');
                        echo Funkcje::RozwijaneMenu('szukaj_serwis', $tablia_serwis, ((isset($_GET['szukaj_serwis'])) ? $filtr->process($_GET['szukaj_serwis']) : '')); 
                        ?>
                    </div>

                    <div class="wyszukaj_select">
                        <span>Sposób nadania:</span>
                        <?php
                        $tablia_nadanie = Array();

                        $tablia_nadanie[] = array('id' => '0', 'text' => 'Dowolny');
                        foreach ( $SposobyNadania as $Rekord ) {
                            $tablia_nadanie[] = array('id' => $Rekord['id'], 'text' => $Rekord['text']);
                        }
                        echo Funkcje::RozwijaneMenu('szukaj_nadanie', $tablia_nadanie, ((isset($_GET['szukaj_nadanie'])) ? $filtr->process($_GET['szukaj_nadanie']) : '')); 
                        ?>
                    </div>

                    <div class="wyszukaj_select">
                        <span>Data utworzenia:</span>
                        <input type="text" id="data_dodania_od" name="szukaj_data_dodania_od" value="<?php echo ((isset($_GET['szukaj_data_dodania_od'])) ? $filtr->process($_GET['szukaj_data_dodania_od']) : ''); ?>" size="12" class="datepicker" /> do 
                        <input type="text" id="data_dodania_do" name="szukaj_data_dodania_do" value="<?php echo ((isset($_GET['szukaj_data_dodania_do'])) ? $filtr->process($_GET['szukaj_data_dodania_do']) : ''); ?>" size="12" class="datepicker" />
                    </div>   

                    <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true || isset($_GET['szukaj_status']) || isset($_GET['szukaj_serwis']) ) {
                  echo '<div id="wyszukaj_ikona"><a href="sprzedaz/zamowienia_wysylki_inpost.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
            </div>

            <form class="cmxform" method="post" id="apiForm" action="sprzedaz/zamowienia_inpost_akcja.php">

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
                        <option value="2">wydrukuj potwierdzenie odbioru dla zaznaczonych</option>
                        <option value="3">zamów odbiór przez kuriera dla zaznaczonych</option>
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
                                  <label class="required">Komentarz:</label>
                                  <textarea cols="100" rows="4" name="komentarz" id="komentarz"></textarea>
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

                <div class="cl"></div>

            </form>

            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            <?php Listing::pokazAjax('sprzedaz/zamowienia_wysylki_inpost.php', $zapytanie, $ile_licznika, $ile_pozycji, 'orders_shipping_id'); ?>
            </script>              

        </div>
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
