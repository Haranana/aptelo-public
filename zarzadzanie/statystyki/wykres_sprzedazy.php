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
            <div class="naglowek">Wykres sprzedaży w okresach miesięcznych</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport prezentuje wartość sprzedaży w okresach miesięcznych</span>
                    
                    <div>
                    
                        <div class="MalyWykres lf">
                        
                            <h3>
                                <?php
                                echo 'Prognoza sprzedaży na ' . date('m',time()) . '.' . date('Y',time()) .' na podstawie sprzedaży w ' . date('m',time()) . '.' . date('Y',time());
                                ?>
                            </h3>
                            
                            <canvas id="canvas_prognoza" width="500" height="220"></canvas> 
                            
                        </div>
                        
                        <?php
                        include("statystyki/wykres_sprzedazy_wykres_prognoza.php");
                        ?>   

                        <div class="MalyWykres rg">
                        
                            <h3>
                                <?php
                                echo 'Prognoza sprzedaży na ' . date('m',time()) . '.' . date('Y',time()) .' na podstawie sprzedaży poprzednich 3 miesięcy';
                                ?>
                            </h3>
                            
                            <canvas id="canvas_prognoza_miesiace" width="500" height="220"></canvas>   
                            
                        </div>
                        
                        <?php
                        include("statystyki/wykres_sprzedazy_wykres_prognoza_miesiace.php");
                        ?>        

                        <div class="cl"></div>
                    
                    </div>

                    <?php
                    $zapytanieWaluty = "select symbol, code, title from currencies";
                    $sqlWaluta = $db->open_query($zapytanieWaluty);
                    
                    $KodJs = '';
                    
                    while ($infr = $sqlWaluta->fetch_assoc()) {
                    
                        // data biezaca
                        $dateDo = new DateTime('now');
                        $dateOd = new DateTime('now');

                        // ostatni dzien daty biezacej
                        $dateDo->modify('last day of this month');
                        $DataDo = $dateDo->format('Y.m.d').' 23:59';

                        // pierwszy dzien daty biezacej - 16 miesiecy
                        $dateOd->modify('first day of -16 month');
                        $DataOd = $dateOd->format('Y.m.d').' 00:01';

                        $ObliczRokDo = $dateDo->format('Y');
                        $ObliczRokOd = $dateOd->format('Y');

                        $ObliczMiesiacDo = $dateDo->format('m');
                        $ObliczMiesiacOd = $dateOd->format('m');

                        $zapytanie = "select o.orders_id,
                                             o.currency,
                                             o.date_purchased, 
                                             ot.orders_id,
                                             ot.value, 
                                             ot.class
                                        from orders o, orders_total ot
                                        where o.orders_id = ot.orders_id and ot.class = 'ot_total' and o.currency = '".$infr['code']."'
                                             and o.date_purchased >= '".$DataOd."' 
                                             and o.date_purchased <= '".$DataDo."'";

                        $sql = $db->open_query($zapytanie);

                        $IloscZamowien = 0;
                        $WartoscZamowien = 0;

                        while ($info = $sql->fetch_assoc()) {
                            //
                            $IloscZamowien++;
                            $WartoscZamowien = $WartoscZamowien + $info['value'];
                            //
                        }                                              
                    
                        $db->close_query($sql);
                        unset($zapytanie, $info);
                        
                        if ($IloscZamowien > 0 && $WartoscZamowien > 0) {

                            ?>
                            
                            <div>
                            
                                <div class="MalyWykres lf">
                                
                                    <h3>
                                        <?php
                                        echo 'Wykres sprzedaży od ' . $ObliczMiesiacDo . '.' . $ObliczRokDo . ' do ' . $ObliczMiesiacOd . '.' . $ObliczRokOd . ' w walucie: ' . mb_convert_case($waluty->ZwrocSymbolWalutyKod($infr['code']), MB_CASE_UPPER, "UTF-8") . '<br /> <small>(wg wartości zamówień)</small>';
                                        ?>
                                    </h3>
                                    
                                    <canvas id="canvas_wykres_wartosc_<?php echo $infr['code']; ?>" width="500" height="220"></canvas> 
                                    
                                </div>

                                <div class="MalyWykres rg">
                                
                                    <h3>
                                        <?php
                                        echo 'Wykres sprzedaży od ' . $ObliczMiesiacDo . '.' . $ObliczRokDo . ' do ' . $ObliczMiesiacOd . '.' . $ObliczRokOd . ' w walucie: ' . mb_convert_case($waluty->ZwrocSymbolWalutyKod($infr['code']), MB_CASE_UPPER, "UTF-8") . '<br /> <small>(wg ilości zamówień)</small>';
                                        ?>
                                    </h3>
                                    
                                    <canvas id="canvas_wykres_ilosc_<?php echo $infr['code']; ?>" width="500" height="220"></canvas>   
                                    
                                </div>
                                
                                <?php
                                include("statystyki/wykres_sprzedazy_wykres.php");
                                ?>        

                                <div class="cl"></div>
                            
                            </div>   

                        <?php 
                        }
                        
                        unset($DataOd, $DataDo, $ObliczMiesiacDo, $ObliczRokDo, $ObliczMiesiacOd, $ObliczRokOd, $IloscZamowien, $WartoscZamowien);                        
                        
                    }    
                    $db->close_query($sqlWaluta);
                    unset($infr, $zapytanieWaluty);                    
                    ?>
                    
                    <script>
                    function KwotaChart(nStr) {
                      var ciag = format_zl(nStr);
                      return ciag.replace('.', ',');
                    }                                      

                    <?php echo $KodJs; ?>
                    </script>
                    
                    <script src="statystyki/wykres_sprzedazy.js"></script> 
                    
                    <?php
                    unset($KodJs);
                    ?>

                </div>

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}