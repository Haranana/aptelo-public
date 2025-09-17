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
            <div class="naglowek">Klienci wg zamówień</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport wyświetla wartość średniego koszyka wybranego klieta oraz sumę wszystkich zamówień klienta</span>
                    
                    <?php
                    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
                    ?>                      
                    
                    <form action="statystyki/klienci_sredni_koszyk.php" method="post" id="statForm" class="cmxform">
                    
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
                        <span>Podaj adres e-mail klienta:</span>
                        <input type="text" id="adres_email" name="adres_email" value="<?php echo ((isset($_GET['adres_email'])) ? $filtr->process($_GET['adres_email']) : ''); ?>" size="30" />
                    </div>
                    
                    <div class="wyszukaj_select" style="margin-top:6px">
                        <span style="margin-left:20px">Data zamowień:</span>
                        <input type="text" id="zamowienia_data_od" name="zamowienia_data_od" value="<?php echo ((isset($_GET['zamowienia_data_od'])) ? $filtr->process($_GET['zamowienia_data_od']) : ''); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                        <input type="text" id="zamowienia_data_do" name="zamowienia_data_do" value="<?php echo ((isset($_GET['zamowienia_data_do'])) ? $filtr->process($_GET['zamowienia_data_do']) : ''); ?>" size="10" class="datepicker" />
                    </div>

                    <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>

                    <?php
                    if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                      echo '<div id="wyszukaj_ikona"><a href="statystyki/klienci_sredni_koszyk.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                    }
                    ?>        

                    <div class="cl"></div>  
                    
                    </form>       

                    <?php
                    $zapytanie = "select o.orders_id, o.currency, o.currency_value, o.date_purchased,
                                         ot.value as wartosc_zamowienia
                                    from orders o, orders_total ot
                                   where o.customers_email_address = '" . ((isset($_GET['adres_email'])) ? $filtr->process($_GET['adres_email']) : '--') . "' AND o.orders_id = ot.orders_id AND ot.class = 'ot_total'";

                    $sql = $db->open_query($zapytanie);
                    
                    $data_od = strtotime("-30 year");
                    $data_do = time();
                    
                    if ( isset($_GET['zamowienia_data_od']) ) {
                         $data_od = FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['zamowienia_data_od'] . ' 00:00:00'));
                    }
                    if ( isset($_GET['zamowienia_data_do']) ) {
                         $data_do = FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['zamowienia_data_do'] . ' 23:59:59'));
                    }
                    
                    if ((int)$db->ile_rekordow($sql) > 0) { ?>
                    
                        <?php
                        $suma_wszystkich_ilosc = 0;
                        $suma_wszystkich_wartosc = 0;
                        //
                        $suma_wszystkich_data_ilosc = 0;
                        $suma_wszystkich_data_wartosc = 0;
                        //
                        while ($info = $sql->fetch_assoc()) {
                            //
                            $suma_wszystkich_ilosc++;
                            //
                            if ( $_SESSION['domyslna_waluta']['kod'] == $info['currency'] ) {
                                $suma_wszystkich_wartosc += $info['wartosc_zamowienia'];
                            } else {
                                $suma_wszystkich_wartosc += $info['wartosc_zamowienia'] / $info['currency_value'];
                            }
                            //
                            if ( FunkcjeWlasnePHP::my_strtotime($info['date_purchased']) >= $data_od && FunkcjeWlasnePHP::my_strtotime($info['date_purchased']) <= $data_do ) {
                                 //
                                 $suma_wszystkich_data_ilosc++;
                                 //
                                 if ( $_SESSION['domyslna_waluta']['kod'] == $info['currency'] ) {
                                     $suma_wszystkich_data_wartosc += $info['wartosc_zamowienia'];
                                 } else {
                                     $suma_wszystkich_data_wartosc += $info['wartosc_zamowienia'] / $info['currency_value'];
                                 }
                                 //
                            }
                        }
                        ?>

                        <div class="RamkaStatystyki" style="margin-top:8px">
                        
                            <table class="TabelaStatystyki">
                            
                                <tr class="TyNaglowek">
                                    <td>Adres e-mail</td>
                                    <td style="text-align:center">Średnia wartość koszyka ze wszystkich zamówień</td>
                                    <td style="text-align:center">Średnia wartość koszyka wg przedziału czasowego</td>
                                </tr>    

                                <tr>
                                    <td><?php echo $_GET['adres_email']; ?></td>
                                    <td><?php echo '<div style="text-align:center;font-size:110%;padding:10px">
                                                        <div style="padding-bottom:10px">Zamówień: <b>' . $suma_wszystkich_ilosc . '</b></div>                                                        
                                                        <div style="padding-bottom:10px">Suma zamówień: <b>' . $waluty->FormatujCene($suma_wszystkich_wartosc, false, $_SESSION['domyslna_waluta']['kod']) . '</b></div>
                                                        <div>Średnia wartość koszyka: <b>' . $waluty->FormatujCene(($suma_wszystkich_wartosc / $suma_wszystkich_ilosc), false, $_SESSION['domyslna_waluta']['kod']) . '</b></div>
                                                    </div>'; ?>
                                    </td>
                                    <td><?php echo '<div style="text-align:center;font-size:110%;padding:10px">
                                                        <div style="padding-bottom:10px">Zamówień: <b>' . $suma_wszystkich_data_ilosc . '</b></div>                                                        
                                                        <div style="padding-bottom:10px">Suma zamówień: <b>' . $waluty->FormatujCene($suma_wszystkich_data_wartosc, false, $_SESSION['domyslna_waluta']['kod']) . '</b></div>
                                                        <div>Średnia wartość koszyka: <b>' . (($suma_wszystkich_data_ilosc > 0) ? $waluty->FormatujCene(($suma_wszystkich_data_wartosc / $suma_wszystkich_data_ilosc), false, $_SESSION['domyslna_waluta']['kod']) : $waluty->FormatujCene(0, false, $_SESSION['domyslna_waluta']['kod'])) . '</b></div>
                                                    </div>'; ?>
                                    </td>
                                </tr>

                            </table>
                            
                        </div>

                        <div class="cl"></div>
                        
                        <?php
                                                
                    } else {
                        //
                        echo '<div class="cl"></div><div class="RamkaStatystyki" style="padding:10px;margin:10px;width:auto">Brak statystyk ...</div>';
                        //
                    }
                    
                    $db->close_query($sql);
                    ?>

                </div>

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}