<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_GET['eksport']) ) {
    
         $klienci = 'eksport';
         Listing::postGet(basename($_SERVER['SCRIPT_NAME']));         
         include('klienci_zamowienia_export.php');
         
         $zmienne = Funkcje::Zwroc_Get(array('typ','waluta'), false);
         
         Funkcje::PrzekierowanieURL('klienci_zamowienia.php' . $zmienne);
    
    }
    
    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Raporty</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Klienci wg zamówień</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport wyświetla listę klientów sklepu wg ilości i wartości zamówień</span>
                    
                    <?php
                    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
                    ?>                      
                    
                    <form action="statystyki/klienci_zamowienia.php" method="post" id="statForm" class="cmxform">
                    
                    <script>
                    $(document).ready(function() {
                      $('input.datepicker').Zebra_DatePicker({
                        format: 'd-m-Y',
                        inside: false,
                        readonly_element: false
                      });                
                    });
                    </script> 

                    <div class="wyszukaj_select" style="margin-left:12px;margin-top:6px">
                        <span>Data rejestracji klientów:</span>
                        <input type="text" id="rejestracja_data_od" name="rejestracja_data_od" value="<?php echo ((isset($_GET['rejestracja_data_od'])) ? $filtr->process($_GET['rejestracja_data_od']) : ''); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                        <input type="text" id="rejestracja_data_do" name="rejestracja_data_do" value="<?php echo ((isset($_GET['rejestracja_data_do'])) ? $filtr->process($_GET['rejestracja_data_do']) : ''); ?>" size="10" class="datepicker" />
                    </div>
                    
                    <div class="wyszukaj_select" style="margin-top:6px">
                        <span style="margin-left:20px">Data zamowień:</span>
                        <input type="text" id="zamowienia_data_od" name="zamowienia_data_od" value="<?php echo ((isset($_GET['zamowienia_data_od'])) ? $filtr->process($_GET['zamowienia_data_od']) : ''); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                        <input type="text" id="zamowienia_data_do" name="zamowienia_data_do" value="<?php echo ((isset($_GET['zamowienia_data_do'])) ? $filtr->process($_GET['zamowienia_data_do']) : ''); ?>" size="10" class="datepicker" />
                    </div>
                    
                    <div class="wyszukaj_select" style="margin-top:6px">
                        <span>Grupa klientów:</span>
                        <?php                         
                        echo Funkcje::RozwijaneMenu('klienci', Klienci::ListaGrupKlientow(true), ((isset($_GET['klienci'])) ? $filtr->process($_GET['klienci']) : '')); 
                        unset($tablica);
                        ?> 
                    </div>
                    
                    <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>

                    <?php
                    if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                      echo '<div id="wyszukaj_ikona"><a href="statystyki/klienci_zamowienia.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                    }
                    ?>        
                    
                    <div class="cl"></div>  
                    
                    <div class="rg" style="margin:0px 15px 5px 0px">
                        <a class="Export" href="statystyki/klienci_zamowienia.php?eksport<?php echo Funkcje::Zwroc_Get(array('rejestracja_data_od','rejestracja_data_do','zamowienia_data_od','zamowienia_data_do'), true); ?>">eksportuj dane do pliku</a>
                    </div> 

                    <div class="cl"></div>  
                    
                    </form>       

                    <div id="wybor">
                    
                    <?php
                    $zmienne = Funkcje::Zwroc_Get(array('str','typ','rejestracja_data_od','rejestracja_data_do','zamowienia_data_od','zamowienia_data_do'), true);
                    ?>
                  
                    <span>Sortowanie:</span>
                    <a class="sortowanie<?php echo ((!isset($_GET['typ']) || (isset($_GET['typ']) && $_GET['typ'] == 'wartosc')) ? '_zaznaczone' : ''); ?>" href="statystyki/klienci_zamowienia.php?typ=wartosc<?php echo $zmienne; ?>">wg wartości zamówień</a>
                    <a class="sortowanie<?php echo ((isset($_GET['typ']) && $_GET['typ'] == 'ilosc') ? '_zaznaczone' : ''); ?>" href="statystyki/klienci_zamowienia.php?typ=ilosc<?php echo $zmienne; ?>">wg ilości zamówień</a>

                    <span style="margin-left:20px">Dla waluty:</span>
                    
                    <?php
                    $zapytanieWaluty = "select code, title from currencies";
                    $sqlWaluta = $db->open_query($zapytanieWaluty);
                    
                    $zmienne = Funkcje::Zwroc_Get(array('str','waluta','rejestracja_data_od','rejestracja_data_do','zamowienia_data_od','zamowienia_data_do'), true);
                    
                    while ($infr = $sqlWaluta->fetch_assoc()) {      
                        if (!isset($_GET['waluta']) && $infr['code'] == $_SESSION['domyslna_waluta']['kod']) {
                            echo '<a class="sortowanie_zaznaczone" href="statystyki/klienci_zamowienia.php?waluta='.$infr['code'].$zmienne.'">'.$infr['title'].'</a>';
                        } else if (isset($_GET['waluta']) && $infr['code'] == $_GET['waluta']) {
                            echo '<a class="sortowanie_zaznaczone" href="statystyki/klienci_zamowienia.php?waluta='.$infr['code'].$zmienne.'">'.$infr['title'].'</a>';
                        } else {
                            echo '<a class="sortowanie" href="statystyki/klienci_zamowienia.php?waluta='.$infr['code'].$zmienne.'">'.$infr['title'].'</a>';
                        }
                    }
                    $db->close_query($sqlWaluta);
                    unset($zapytanieWaluty);                    
                    ?>
                    
                    </div>

                    <?php
                    // warunki formularza
                    $warunki_szukania = '';
                    
                    if ( isset($_GET['rejestracja_data_od']) && $_GET['rejestracja_data_od'] != '' ) {
                        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['rejestracja_data_od'] . ' 00:00:00')));
                        $warunki_szukania .= " AND ci.customers_info_date_account_created >= '".$szukana_wartosc."'";
                        unset($szukana_wartosc);
                    }

                    if ( isset($_GET['rejestracja_data_do']) && $_GET['rejestracja_data_do'] != '' ) {
                        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['rejestracja_data_do'] . ' 23:59:59')));
                        $warunki_szukania .= " AND ci.customers_info_date_account_created <= '".$szukana_wartosc."'";                        
                        unset($szukana_wartosc);
                    }  

                    if ( isset($_GET['zamowienia_data_od']) && $_GET['zamowienia_data_od'] != '' ) {
                        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['zamowienia_data_od'] . ' 00:00:00')));
                        $warunki_szukania .= " AND o.date_purchased >= '".$szukana_wartosc."'";
                        unset($szukana_wartosc);
                    }

                    if ( isset($_GET['zamowienia_data_do']) && $_GET['zamowienia_data_do'] != '' ) {
                        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['zamowienia_data_do'] . ' 23:59:59')));
                        $warunki_szukania .= " AND o.date_purchased <= '".$szukana_wartosc."'";                        
                        unset($szukana_wartosc);
                    }        

                    // jezeli jest wybrana grupa klienta
                    if (isset($_GET['klienci']) && (int)$_GET['klienci'] > 0) {
                        $id_grupy = (int)$_GET['klienci'];
                        $warunki_szukania .= " AND c.customers_groups_id = '" . $id_grupy . "'";        
                        unset($id_grupy);
                    }                      
                    
                    // ile na stronie
                    $IleNaStronie = 50;
                    
                    $PoczatekLimit = 0;
                    if (isset($_GET['str']) && (int)$_GET['str'] > 0) {
                        $PoczatekLimit = (int)$_GET['str'] * $IleNaStronie;
                    }
                    
                    // waluta
                    $JakaWaluta = $_SESSION['domyslna_waluta']['kod'];
                    if (isset($_GET['waluta'])) {
                        $JakaWaluta = $filtr->process($_GET['waluta']);
                    }
                    
                    // zapytanie bez limitu - ogolna ilosc 
                    $zapytanie = "select c.customers_id, 
                                         c.customers_firstname, 
                                         c.customers_lastname,
                                         c.customers_discount, 
                                         c.customers_groups_id,
                                         ci.customers_info_number_of_logons, 
                                         ci.customers_info_date_account_created,
                                         c.customers_guest_account, 
                                         count(DISTINCT o.orders_id) as ilosc_zamowien, 
                                         sum(ot.value) as wartosc_zamowien,
                                         o.currency
                                    from customers c, 
                                         orders_total ot, 
                                         orders o, 
                                         customers_info ci 
                                   where c.customers_id = ci.customers_info_id AND 
                                         c.customers_id = o.customers_id AND
                                         o.orders_id = ot.orders_id AND
                                         ot.class = 'ot_total' AND
                                         o.currency = '".$JakaWaluta."' AND
                                         c.customers_guest_account = '0' " . $warunki_szukania . "
                                   group by c.customers_id, c.customers_firstname, c.customers_lastname order by " . ((isset($_GET['typ']) && $_GET['typ'] == 'ilosc') ? "ilosc_zamowien" : "wartosc_zamowien") . " DESC";
                    $sql = $db->open_query($zapytanie);
                    $OgolnaIlosc = (int)$db->ile_rekordow($sql);
                    
                    $db->close_query($sql);
                    unset($zapytanie);
                    
                    
                    // zapytanie z limitem
                    $zapytanie = "select c.customers_id, 
                                         c.customers_firstname, 
                                         c.customers_lastname,
                                         c.customers_discount, 
                                         c.customers_groups_id,
                                         ci.customers_info_number_of_logons, 
                                         c.customers_guest_account, 
                                         count(DISTINCT o.orders_id) as ilosc_zamowien, 
                                         sum(ot.value) as wartosc_zamowien,
                                         o.currency
                                    from customers c, 
                                         orders_total ot, 
                                         orders o, 
                                         customers_info ci 
                                   where c.customers_id = ci.customers_info_id AND 
                                         c.customers_id = o.customers_id AND
                                         o.orders_id = ot.orders_id AND
                                         ot.class = 'ot_total' AND
                                         o.currency = '".$JakaWaluta."' AND
                                         c.customers_guest_account = '0' " . $warunki_szukania . "
                                   group by c.customers_id, c.customers_firstname, c.customers_lastname order by " . ((isset($_GET['typ']) && $_GET['typ'] == 'ilosc') ? "ilosc_zamowien" : "wartosc_zamowien") . " DESC limit " . $PoczatekLimit . "," . $IleNaStronie;
                    
                    $sql = $db->open_query($zapytanie);
                    
                    if ((int)$db->ile_rekordow($sql) > 0) {
                        ?>

                        <div class="RamkaStatystyki" style="margin-top:8px">
                        
                            <table class="TabelaStatystyki">
                            
                            <tr class="TyNaglowek">
                                <td style="text-align:center">Lp</td>
                                <td style="text-align:center">Id</td>
                                <td>Imię i nazwisko</td>
                                <td style="text-align:center">Grupa klientów</td>
                                <td style="text-align:center">Ilość logowań</td>
                                <td style="text-align:center">Ilość zamówień</td>
                                <td style="text-align:center">Wartość zamówień</td>
                                <td style="text-align:center">Zniżki klienta</td>
                            </tr>                       
                            
                            <?php
                            $poKolei = 1 + $PoczatekLimit;
                            while ($info = $sql->fetch_assoc()) {
                            
                                echo '<tr>';
                                
                                echo '<td class="poKolei">' . $poKolei . '</td>'; 
                                echo '<td class="inne">' . $info['customers_id'] . '</td>';
                                echo '<td class="linkProd" style="width:20%"><a href="klienci/klienci_edytuj.php?id_poz='.$info['customers_id'].'">' . $info['customers_firstname'] . ' ' . $info['customers_lastname'] . '</a></td>';
                                echo '<td class="inne">' . Klienci::pokazNazweGrupyKlientow($info['customers_groups_id']) . '</td>';
                                echo '<td class="inne">' . $info['customers_info_number_of_logons'] . '</td>';
                                echo '<td class="inne">' . $info['ilosc_zamowien'] . '</td>';
                                echo '<td class="wynikStat">' . $waluty->FormatujCene($info['wartosc_zamowien'], false, $info['currency']) . '</td>';
                                
                                // znizki klienta
                                $ZnizkiKlienta = '';
                                $TblZnizki = Klienci::ZnizkiKlienta($info['customers_id'], $info['customers_discount']);
                                //
                                if (count($TblZnizki) > 0) {
                                    //
                                    $ZnizkiKlienta .= '<table>';
                                    //
                                    for ($j = 0, $c = count($TblZnizki); $j < $c; $j++) {
                                        if ($TblZnizki[$j][2] != 0) {                                    
                                            if ($TblZnizki[$j][0] == $TblZnizki[$j][1]) {
                                                //
                                                $ZnizkiKlienta .= '<tr><td><strong>' . $TblZnizki[$j][0] . '</strong>:</td><td style="width:60px"><span>' . $TblZnizki[$j][2] . ' %</span></td></tr>';
                                                //
                                              } else {
                                                //
                                                $ZnizkiKlienta .= '<tr><td>' . $TblZnizki[$j][0] . ' <strong>' . $TblZnizki[$j][1] . '</strong>:</td><td style="width:60px"><span>' . $TblZnizki[$j][2] . ' %</span></td></tr>';
                                                //
                                            }
                                        }
                                    }
                                    //
                                    $ZnizkiKlienta .= '</table>';
                                    //
                                }
                                echo '<td class="znizki">' . $ZnizkiKlienta . '</td>';

                                echo '</tr>';
                                
                                $poKolei++;
                            
                            }            
                            unset($poKolei);
                            $db->close_query($sql);
                            ?>
                            
                            </table>
                            
                        </div>
                        
                        <div id="DolneStrony">
                            <?php
                            $limit = $OgolnaIlosc / $IleNaStronie;
                            if ($limit < ($OgolnaIlosc / $IleNaStronie)) {
                                $limit++;
                            }
                            //
                            for ($c = 0; $c < $limit; $c++) {
                                //
                                $Rozszerzenie = 'Nieaktywny';
                                if ((!isset($_GET['str']) || (int)$_GET['str'] == 0) && $c == 0) {
                                    $Rozszerzenie = 'Aktywny';
                                }
                                if (isset($_GET['str']) && (int)$_GET['str'] == $c) {
                                    $Rozszerzenie = 'Aktywny';
                                }
                                //
                                // sprawdzanie czy są jakies zmienne GET
                                $zmienne = Funkcje::Zwroc_Get(array('str'),(((isset($_GET['waluta']) || isset($_GET['typ'])) && $c > 0) ? true : false));
                                //
                                if ($c == 0) {
                                    echo '<a class="Przycisk' . $Rozszerzenie . '" href="statystyki/klienci_zamowienia.php'.$zmienne.'">1</a>';
                                  } else {
                                    echo '<a class="Przycisk' . $Rozszerzenie . '" href="statystyki/klienci_zamowienia.php?str='.$c.$zmienne.'">' . ($c + 1) . '</a>';
                                }
                            }
                            //
                            echo '<span id="IleStrona">Wyświetlanie: ' . ($PoczatekLimit + 1) . ' do ' . (($PoczatekLimit + $IleNaStronie) > $OgolnaIlosc ? $OgolnaIlosc : ($PoczatekLimit + $IleNaStronie)) . ' z ' . $OgolnaIlosc . '</span>';
                            ?>
                        </div>
                        
                        <div class="cl"></div>
                        
                        <?php
                                                
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