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
         include('zamowienia_wg_zrodel_export.php');
         
         Funkcje::PrzekierowanieURL('zamowienia_wg_zrodel.php');
    
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Raporty</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Raport stron z których klient trafił do sklepu</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport prezentuje strony z jakich klient został przekierowany na stronę sklepu</span>
                    
                    <?php
                    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
                    ?>                      
                    
                    <form action="statystyki/zamowienia_wg_zrodel.php" method="post" id="statForm" class="cmxform">
                    
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
                      echo '<div id="wyszukaj_ikona" style="margin-top:8px"><a href="statystyki/zamowienia_wg_zrodel.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
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

                    $zapytanie = "SELECT reference FROM orders WHERE " . $warunki_szukania . " ORDER BY orders_id desc";
                    $sql = $db->open_query($zapytanie);

                    unset($szukana_wartosc, $data);
                    
                    $tablica_zrodla = array();
                    
                    if ( (int)$db->ile_rekordow($sql) > 0 ) {

                        while ($info = $sql->fetch_assoc()) {
                            //
                            if ( !empty($info['reference']) ) {
                                 //
                                 $info['reference'] = base64_encode(str_replace( array('http://','https://'), '', $info['reference']));
                                 //
                                 if ( !isset($tablica_zrodla[$info['reference']]) ) {
                                      //
                                      $tablica_zrodla[$info['reference']] = 1;
                                      //
                                 } else {
                                      //
                                      $tablica_zrodla[$info['reference']] = $tablica_zrodla[$info['reference']] + 1;
                                      //
                                 }
                                 //
                            }
                            //
                        }

                        unset($info, $wartosc_calkowita, $wartosc_calkowita_wysylki_platnosci);

                    }
                    
                    if ( count($tablica_zrodla) > 0 ) {
                      
                         arsort($tablica_zrodla);
                         //
                         ?>
                         
                         <div class="cl"></div> 
                         
                         <div class="rg" style="margin:0px 10px 5px 0px">
                             <a class="Export" href="statystyki/zamowienia_wg_zrodel.php?eksport">eksportuj dane do pliku</a>
                         </div>
                         
                         <div class="cl"></div>
                         
                         <?php
                         
                         echo '<div class="RamkaStatystyki" style="margin-top:10px"><table class="TabelaStatystyki">';
                         
                         echo '<tr class="TyNaglowek" style="text-align:center">
                                 <td>Strona</td>
                                 <td>Ilość zamówień</td>
                               </tr>';
                               
                         foreach ( $tablica_zrodla as $zrodlo => $ilosc ) {
                           
                             echo '<tr>
                                     <td style="word-break:break-word">' . base64_decode($zrodlo) . '</td>
                                     <td class="inne">' . $ilosc . '</td>
                                   </tr>';
                           
                         }
                                 
                         echo '</table></div>';   
                         //
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