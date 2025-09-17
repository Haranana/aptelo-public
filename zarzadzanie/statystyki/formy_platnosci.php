<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Raporty</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Wybierane formy płatności</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport prezentuje wybierane podczas składania zamówień formy płatności</span>
                    
                    <?php
                    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
                    ?>
                    
                    <form action="statystyki/formy_platnosci.php" method="post" id="statForm" class="cmxform">
                    
                    <script>
                    $(document).ready(function() {
                      $('input.datepicker').Zebra_DatePicker({
                        format: 'd-m-Y',
                        inside: false,
                        readonly_element: false
                      });                
                    });
                    </script>                    
                    
                    <div id="zakresDat" style="margin-top:10px">
                        <span>Przedział czasowy wyników od:</span>
                        <input type="text" id="data_od" name="data_od" value="<?php echo ((isset($_GET['data_od'])) ? $filtr->process($_GET['data_od']) : ''); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                        <input type="text" id="data_do" name="data_do" value="<?php echo ((isset($_GET['data_do'])) ? $filtr->process($_GET['data_do']) : ''); ?>" size="10" class="datepicker" />
                    </div>    

                    <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                    
                    <?php
                    if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                      echo '<div id="wyszukaj_ikona"><a href="statystyki/formy_platnosci.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                    }
                    ?>                     

                    <div class="cl"></div>
                    
                    </form>
                    
                    <?php
                    $Wynik = '<div class="WykresStatystki"><div><canvas id="formy_platnosci" width="400" height="400"></canvas></div></div>';
                    //
                    $warunki_szukania = '';
                    if ( isset($_GET['data_od']) && $_GET['data_od'] != '' ) {
                        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['data_od'] . ' 00:00:00')));
                        $warunki_szukania .= " and date_purchased >= '".$szukana_wartosc."'";
                    }

                    if ( isset($_GET['data_do']) && $_GET['data_do'] != '' ) {
                        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['data_do'] . ' 23:59:59')));
                        $warunki_szukania .= " and date_purchased <= '".$szukana_wartosc."'";                      
                    }
                    //                    
                    $zapytanie = "select distinct payment_method from orders where payment_method != '' ";
                    $sql = $db->open_query($zapytanie);
                    
                    $Wynik .= '<br /> <div class="RamkaStatystyki"><table class="TabelaStatystyki">';
                    
                    $Wynik .= '<tr class="TyNaglowek">';
                    $Wynik .= '<td>Forma płatności</td>';
                    $Wynik .= '<td style="text-align:center">Ilość zamówień</td>';
                    $Wynik .= '</tr>';                     
                    
                    $SumaBylo = false;
                    
                    while ($info = $sql->fetch_assoc()) {
                        //
                        $zapytanieJedn = "select payment_method, date_purchased from orders where payment_method = '" . $info['payment_method'] . "'" . $warunki_szukania;
                        $sqlc = $db->open_query($zapytanieJedn);
                        //
                        if ((int)$db->ile_rekordow($sqlc) > 0) {
                        
                            $Wynik .= '<tr>';
                            $Wynik .= '<td class="linkProd"><a href="sprzedaz/zamowienia.php?szukaj_platnosc='.$info['payment_method'].'">' . $info['payment_method'] . '</a></td>';
                            
                            if ($db->ile_rekordow($sqlc) == 1) {
                                $TrescZam = ' <span>zamówienie</span>';
                            }
                            if ($db->ile_rekordow($sqlc) > 1 && $db->ile_rekordow($sqlc) < 5) {
                                $TrescZam = ' <span>zamówienia</span>';
                            }
                            if ($db->ile_rekordow($sqlc) > 4 || $db->ile_rekordow($sqlc) == 0) {
                                $TrescZam = ' <span>zamówień</span>';
                            }                            
                            $Wynik .= '<td class="wynikStat">' . $db->ile_rekordow($sqlc) . $TrescZam . '</td>';
                            $Wynik .= '</tr>'; 

                            $SumaBylo = true;

                        }
                        $db->close_query($sqlc);
                        unset($zapytanieJedn);

                        //                    
                    }
                    $db->close_query($sql);
                    unset($info, $zapytanie, $TrescZam);
                    
                    $Wynik .= '</table></div>';

                    if ($SumaBylo == true) {
                    
                        echo $Wynik;
                        unset($Wynik);
                        
                        include('statystyki/formy_platnosci_wykres.php');
                    
                    } else {
                        //
                        echo '<div style="margin:10px">Brak statystyk ...</div>';
                        //
                    }
                    ?>                    

                </div>

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}