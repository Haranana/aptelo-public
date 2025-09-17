<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_GET['eksport']) ) {
    
         $zamowienia = 'eksport';
         Listing::postGet(basename($_SERVER['SCRIPT_NAME']));         
         include('raport_zamowienia_export.php');
         
         Funkcje::PrzekierowanieURL('raport_zamowienia.php');
    
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Raporty</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Raport wg zamówień</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport prezentuje zestawienie sprzedaży wg zamówień</span>
                    
                    <?php
                    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
                    ?>                      
                    
                    <form action="statystyki/raport_zamowienia.php" method="post" id="statForm" class="cmxform">
                    
                    <script>
                    $(document).ready(function() {
                      $('input.datepicker').Zebra_DatePicker({
                        format: 'd-m-Y',
                        inside: false,
                        readonly_element: false
                      });                
                    });
                    </script> 
                    
                    <?php
                    // przedzial poczatkowy czasu
                    $data_poczatkowa = date('Y') . "-" . date('m') . "-01";
                    $data_koncowa = date('Y') . "-" . date('m') . "-" . date('d');
                    
                    // dla kalendarza w postaci dd-mm-rr
                    $data_poczatkowa_kalendarz = "01-" . date('m') . "-" . date('Y');
                    $data_koncowa_kalendarz = date('d') . "-" . date('m') . "-" . date('Y');           
                    //
                    ?>

                    <div id="zakresDat">
                    
                        <span>Przedział czasowy wyników od:</span>
                        <input type="text" id="data_od" name="data_od" value="<?php echo ((isset($_GET['data_od'])) ? $filtr->process($_GET['data_od']) : $data_poczatkowa_kalendarz); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                        <input type="text" id="data_do" name="data_do" value="<?php echo ((isset($_GET['data_do'])) ? $filtr->process($_GET['data_do']) : $data_koncowa_kalendarz); ?>" size="10" class="datepicker" />

                        <span style="margin-left:20px">Status:</span>
                        <?php
                        $tablia_status= Array();
                        $tablia_status = Sprzedaz::ListaStatusowZamowien(true);
                        echo Funkcje::RozwijaneMenu('szukaj_status', $tablia_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : ''), ' style="width:170px"'); ?>
                        
                    </div>    

                    <div class="wyszukaj_przycisk" style="margin-top:5px !important"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                    
                    <?php
                    if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                      echo '<div id="wyszukaj_ikona" style="margin-top:8px"><a href="statystyki/raport_zamowienia.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                    }
                    ?>                     

                    </form>        

                    <?php
                    $warunki_szukania = 'orders_id > 0 ';
                    $data = false;

                    if ( isset($_GET['data_od']) && $_GET['data_od'] != '' ) {
                        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['data_od'] . ' 00:00:00')));
                        $warunki_szukania .= " and date_purchased >= '".$szukana_wartosc."'";
                        $data = true;
                        unset($szukana_wartosc);
                    }

                    if ( isset($_GET['data_do']) && $_GET['data_do'] != '' ) {
                        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['data_do'] . ' 23:59:59')));
                        $warunki_szukania .= " and date_purchased <= '".$szukana_wartosc."'";                        
                        $data = true;
                        unset($szukana_wartosc);
                    }       
                    
                    // jezeli nic nie wypelnione przyjmuje dziesiejsza date
                    if ($data == false) {
                        $warunki_szukania .= " and date_purchased >= '". $data_poczatkowa . " 00:00:00' and date_purchased <= '". $data_koncowa . " 23:59:59'";
                    }                    

                    if ( isset($_GET['szukaj_status']) && (int)$_GET['szukaj_status'] > 0 ) {
                        $warunki_szukania .= " and orders_status = " . (int)$_GET['szukaj_status'] . " ";
                    }                              

                    $zapytanie = "SELECT orders_id FROM orders WHERE " . $warunki_szukania . " ORDER BY orders_id desc";
                    $sql = $db->open_query($zapytanie);

                    unset($szukana_wartosc, $data);
                    
                    if ( (int)$db->ile_rekordow($sql) > 0 ) {
                    
                        ?>
                        
                        <div class="cl"></div> 
                        
                        <div class="rg" style="margin:0px 10px 5px 0px">
                            <a class="Export" href="statystyki/raport_zamowienia.php?eksport">eksportuj dane do pliku</a>
                        </div>
                        
                        <div class="cl"></div>
                        
                        <?php
                        
                        $tabla_walut = array();
                        $wartosc_calkowita = array();
                        $wartosc_calkowita_wysylki_platnosci = array();
                        
                        echo '<div class="RamkaStatystyki" style="margin-top:10px"><table class="TabelaStatystyki">';
                        
                        echo '<tr class="TyNaglowek" style="text-align:center">
                                <td>Nr zamówienia</td>
                                <td>Dane klienta</td>
                                <td>Liczba <br /> zamówionych <br /> produktów</td>
                                <td>Wartość zamówienia</td>
                                <td>Koszt wysyłki + <br /> dopłata za <br /> rodzaj płatności</td>
                                <td>Wartość towaru</td>
                                <td>Forma płatności</td>
                                <td>Dostawa</td>
                              </tr>';

                        while ($info = $sql->fetch_assoc()) {

                            $zamowienie = new Zamowienie($info['orders_id']);

                            echo '<tr>';
                            
                            $wartosc_zamowienia = array( 'kwota' => '-', 'wartosc' => 0 );
                            $wartosc_wysylki_platnosci = 0;
                            
                            foreach ( $zamowienie->podsumowanie as $suma ) {
                                //
                                // wartosc zamowienia
                                if ( $suma['klasa'] == 'ot_total' ) {
                                     $wartosc_zamowienia = array( 'kwota' => $suma['tekst'], 'wartosc' => $suma['wartosc'] );
                                }
                                //
                                // koszt wysylki i platnosci
                                if ( $suma['klasa'] == 'ot_shipping' || $suma['klasa'] == 'ot_payment' ) {
                                     $wartosc_wysylki_platnosci += $suma['wartosc'];
                                }
                                //
                            }

                            echo '<td class="inne">' . $info['orders_id'] . '</td>';
                            echo '<td class="inne" style="text-align:left">';
                            
                            echo ((!empty($zamowienie->klient['firma'])) ? '<span class="Firma">' . $zamowienie->klient['firma'] . '</span><br /> ' : '') . 
                                                   $zamowienie->klient['nazwa'] . '<br />'.
                                                   $zamowienie->klient['ulica'] . '<br />'.
                                                   $zamowienie->klient['kod_pocztowy'] . ' '. $zamowienie->klient['miasto'];                            
                            
                            echo '</td>';
                            
                            $suma_produktow = 0;
                            foreach ( $zamowienie->produkty as $produkty ) {
                                $suma_produktow += $produkty['ilosc'];
                            }                            
                            
                            echo '<td class="inne">' . $suma_produktow . '</td>';
                            
                            unset($suma_produktow);
                            
                            echo '<td class="inne" style="text-align:right">' . $wartosc_zamowienia['kwota'] . '</td>';
                            echo '<td class="inne" style="text-align:right">' . $waluty->FormatujCene($wartosc_wysylki_platnosci, false, $zamowienie->info['waluta']) . '</td>';
                            echo '<td class="inne" style="text-align:right">' . $waluty->FormatujCene($wartosc_zamowienia['wartosc'] - $wartosc_wysylki_platnosci, false, $zamowienie->info['waluta']) . '</td>';
                            echo '<td class="inne">' . $zamowienie->info['metoda_platnosci'] . '</td>';
                            echo '<td class="inne">' . $zamowienie->info['wysylka_modul'] . '</td>';
                            
                            if ( !isset($wartosc_calkowita[ $zamowienie->info['waluta'] ]) ) {
                                 $wartosc_calkowita[ $zamowienie->info['waluta'] ] = 0;
                            }
                            if ( !isset($wartosc_calkowita_wysylki_platnosci[ $zamowienie->info['waluta'] ]) ) {
                                 $wartosc_calkowita_wysylki_platnosci[ $zamowienie->info['waluta'] ] = 0;
                            }
                            
                            $wartosc_calkowita[ $zamowienie->info['waluta'] ] += $wartosc_zamowienia['wartosc'];
                            $wartosc_calkowita_wysylki_platnosci[ $zamowienie->info['waluta'] ] += $wartosc_wysylki_platnosci;
                            
                            $tabla_walut[ $zamowienie->info['waluta'] ][ 'wartosc_calkowita' ] = $wartosc_calkowita[ $zamowienie->info['waluta'] ];
                            $tabla_walut[ $zamowienie->info['waluta'] ][ 'wartosc_calkowita_wysylki_platnosci' ] = $wartosc_calkowita_wysylki_platnosci[ $zamowienie->info['waluta'] ];
                            
                            unset($zamowienie, $wartosc_zamowienia, $wartosc_wysylki_platnosci);                            

                        }
                        
                        unset($info, $wartosc_calkowita, $wartosc_calkowita_wysylki_platnosci);
                        
                        foreach ( $tabla_walut as $klucz => $waluta_wartosc ) {
                        
                            echo '<tr class="TyNaglowek">';
                            
                            $zapytanieWaluty = "select code, title, symbol from currencies where code = '" . $klucz . "'";
                            $sqlWaluta = $db->open_query($zapytanieWaluty);                            
                            $infr = $sqlWaluta->fetch_assoc();
                            
                            $nazwaWaluty = $infr['title'];
                            
                            $db->close_query($sqlWaluta);
                            unset($zapytanieWaluty); 
                            
                            echo '<td class="inne" colspan="3" style="text-align:right">' . $nazwaWaluty . '</td>';
                            echo '<td class="inne" style="text-align:right">' . $waluty->FormatujCene($tabla_walut[$klucz]['wartosc_calkowita'], false, $klucz) . '</td>';
                            echo '<td class="inne" style="text-align:right">' . $waluty->FormatujCene($tabla_walut[$klucz]['wartosc_calkowita_wysylki_platnosci'], false, $klucz) . '</td>';
                            echo '<td class="inne" style="text-align:right">' . $waluty->FormatujCene($tabla_walut[$klucz]['wartosc_calkowita'] - $tabla_walut[$klucz]['wartosc_calkowita_wysylki_platnosci'], false, $klucz) . '</td>';                            
                            echo '<td class="inne" colspan="2">-</td>';
                            
                            echo '</tr>';
                            
                            unset($nazwaWaluty);
                        
                        }                        

                        echo '</table></div>';   

                    } else {
                        //
                        echo '<div class="cl"></div><div class="RamkaStatystyki" style="padding:10px;margin:10px;width:auto">Brak statystyk ...</div>';
                        //
                    }
                    
                    $db->close_query($sql);
                    unset($zapytanie);
                    ?>                    

                </div>

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}