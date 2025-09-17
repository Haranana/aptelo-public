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
            <div class="naglowek">Koszty wysyłek</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport prezentuje koszty wysyłek</span>
                    
                    <?php
                    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
                    ?>                    
                    
                    <form action="statystyki/koszty_wysylek.php" method="post" id="statForm" class="cmxform">
                    
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
                      echo '<div id="wyszukaj_ikona"><a href="statystyki/koszty_wysylek.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                    }
                    ?>                    

                    <div class="cl"></div>
                    
                    </form>
                    
                    <?php
                    //
                    $warunki_szukania = '';
                    $przekazGet = '?data=';
                    if ( isset($_GET['data_od']) && $_GET['data_od'] != '' ) {
                        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['data_od'] . ' 00:00:00')));
                        $warunki_szukania .= " and o.date_purchased >= '".$szukana_wartosc."'";
                        $przekazGet .= FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['data_od'] . ' 00:00:00')) . ',';
                      } else {
                        $przekazGet .= '0,';
                    }

                    if ( isset($_GET['data_do']) && $_GET['data_do'] != '' ) {
                        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['data_do'] . ' 23:59:59')));
                        $warunki_szukania .= " and o.date_purchased <= '".$szukana_wartosc."'";
                        $przekazGet .= FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['data_do'] . ' 23:59:59')) . ',';
                      } else {
                        $przekazGet .= '0,';                        
                    }
                    
                    $zapytanieWaluty = "select code, title, symbol from currencies";
                    $sqlWaluta = $db->open_query($zapytanieWaluty);
                    
                    $SumaStatystyk = false;
                    
                    while ($infr = $sqlWaluta->fetch_assoc()) {                    
                    
                        //                    
                        $zapytanie = "select distinct sum(ot.value) as wartosc, shipping_module 
                                                 from orders o, orders_total ot 
                                                where o.orders_id = ot.orders_id and 
                                                      o.shipping_module != '' and 
                                                      ot.class = 'ot_shipping' and currency = '" . $infr['code'] . "'" . $warunki_szukania . " group by o.shipping_module order by wartosc desc";
                                                                  
                        $sql = $db->open_query($zapytanie);
                        
                        if ((int)$db->ile_rekordow($sql) > 0) {
                        
                            echo '<div class="NaglowekWaluta">Dostawa w walucie: ' .  mb_convert_case($infr['symbol'], MB_CASE_UPPER, "UTF-8") . '</div> <div class="RamkaStatystyki"><table class="TabelaStatystyki">';
                            
                            echo '<tr class="TyNaglowek">';
                            echo '<td>Forma dostawy</td>';
                            echo '<td style="text-align:center">Wartość</td>';
                            echo '</tr>';   
                            
                            $Bylo = false;
                            $Suma = 0;

                            while ($info = $sql->fetch_assoc()) {
                                //
                                if ( $info['wartosc'] > 0 ) {
                                    //
                                    echo '<tr>';
                                    echo '<td class="linkProd"><a href="sprzedaz/zamowienia.php?szukaj_wysylka='.$info['shipping_module'].'">' . $info['shipping_module'] . '</a></td>';
                                    echo '<td class="wynikStat"><span>' . $waluty->FormatujCene($info['wartosc'], false, $infr['code']) . '</span></td>';
                                    echo '</tr>'; 
                                    
                                    $Suma = $Suma + $info['wartosc'];

                                    $Bylo = true;
                                    $SumaStatystyk = true;
                                    //
                                }

                            }
                            
                            echo '<tr class="TyNaglowek">';
                            echo '<td style="text-align:right; padding:10px;" colspan="2">' . $waluty->FormatujCene($Suma, false, $infr['code']) . '</td>';
                            echo '</tr>';                             

                            echo '</table></div>';
                            
                        }
                        
                        $db->close_query($sql);
                        unset($info, $zapytanie);                           
                        
                    }    
                    
                    $db->close_query($sqlWaluta);
                    unset($infr, $zapytanieWaluty);                     
                    ?>
                    
                    <?php
                    if ($SumaStatystyk == false) {
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