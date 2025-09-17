<?php
if ( isset($prot) ) {
  
    if ( $prot->wyswietlStrone) {
   
        // ilosc miesiacy w dacie dzisiejszej
        $miesiac = (date('Y',time()) * 12) + date('m',time());

        $TabOsX = array();
        $IloscZam = array();
        $WartoscZam = array();

        for ($n = 1; $n <= 17; $n++) {
            //
            $ObliczRok = (int)($miesiac/12);
            if ( $ObliczRok == ($miesiac/12) ) {
                 $ObliczRok = $ObliczRok - 1;
            }
            
            $ObliczMiesiac = $miesiac - ((int)($miesiac/12) * 12);
            
            if ( $ObliczMiesiac == 0 ) {
                 $ObliczMiesiac = 12;
            }
            
            //
            $zapytanie = "select o.orders_id,
                                 o.currency,
                                 o.date_purchased, 
                                 ot.orders_id,
                                 ot.value, 
                                 ot.class
                            from orders o, orders_total ot
                            where o.orders_id = ot.orders_id and ot.class = 'ot_total' and o.currency = '".$infr['code']."'
                                 and year(o.date_purchased) = '".$ObliczRok."' and month(o.date_purchased) = '".$ObliczMiesiac."'"; 

            // zmniejszanie ilosci miesiacy
            $miesiac--;
                                 
            $sql = $db->open_query($zapytanie);

            $IloscZamowien = 0;
            $WartoscZamowien = 0;

            while ($info = $sql->fetch_assoc()) {
                //
                $IloscZamowien++;
                $WartoscZamowien = number_format(($WartoscZamowien + $info['value']), 2, '.', '');
                //
            }            
            $db->close_query($sql);
            
            // tablica do wartosci zamowien
            $dataZamowienia = $ObliczMiesiac . '.' . $ObliczRok;
            
            $dataZamowienia = str_replace('10.','październik ', (string)$dataZamowienia);
            $dataZamowienia = str_replace('11.','listopad ', (string)$dataZamowienia);
            $dataZamowienia = str_replace('12.','grudzień ', (string)$dataZamowienia);             
            $dataZamowienia = str_replace('1.','styczeń ', (string)$dataZamowienia);
            $dataZamowienia = str_replace('2.','luty ', (string)$dataZamowienia);
            $dataZamowienia = str_replace('3.','marzec ', (string)$dataZamowienia);
            $dataZamowienia = str_replace('4.','kwiecień ', (string)$dataZamowienia);
            $dataZamowienia = str_replace('5.','maj ', (string)$dataZamowienia);
            $dataZamowienia = str_replace('6.','czerwiec ', (string)$dataZamowienia);
            $dataZamowienia = str_replace('7.','lipiec ', (string)$dataZamowienia);
            $dataZamowienia = str_replace('8.','sierpień ', (string)$dataZamowienia);
            $dataZamowienia = str_replace('9.','wrzesień ', (string)$dataZamowienia);           

            $TabOsX[] = '"' . $dataZamowienia . '"';
            
            $IloscZam[] = $IloscZamowien;
            $WartoscZam[] = $WartoscZamowien;
            //
            
            unset($IloscZamowien, $WartoscZamowien, $dataZamowienia, $ObliczRok, $ObliczMiesiac);
            
        }     

        $KodJs .= 'var ctxWykresIlosc' . $infr['code'] . ' = document.getElementById("canvas_wykres_ilosc_' . $infr['code'] . '").getContext("2d");' . "\n" . 
                  ' window.myLineWykresIlosc = new Chart(ctxWykresIlosc' . $infr['code'] . ').Bar(lineChartDataWykresIlosc' . $infr['code'] . ', {' . "\n" . 
                  '  responsive: true,' . "\n" . 
                  '  barStrokeWidth : 1,' . "\n" . 
                  '  tooltipTemplate: "Ilość zamówień: <%= value %>",' . "\n" . 
                  '  tooltipFillColor: "rgba(0,0,0,0.7)"' . "\n" . 
                  ' });' . "\n";  

        $KodJs .= 'var ctxWykresWartosc' . $infr['code'] . ' = document.getElementById("canvas_wykres_wartosc_' . $infr['code'] . '").getContext("2d");' . "\n" . 
                  ' window.myLineWykresWartosc = new Chart(ctxWykresWartosc' . $infr['code'] . ').Line(lineChartDataWykresWartosc' . $infr['code'] . ', {' . "\n" . 
                  '  responsive: true,' . "\n" . 
                  '  datasetStrokeWidth : 3,' . "\n" . 
                  '  pointDotRadius : 5,' . "\n" . 
                  '  tooltipTemplate: "Wartość zamówień: <%= KwotaChart(value) %> ' . $infr['symbol'] . '",' . "\n" . 
                  '  tooltipFillColor: "rgba(0,0,0,0.7)"' . "\n" . 
                  ' });' . "\n";                   
        ?>
        
        <script>
        var lineChartDataWykresIlosc<?php echo $infr['code']; ?> = {
          labels : [ <?php echo implode(',', (array)$TabOsX); ?> ],
          datasets : [
            {
              label: "Wykres sprzedaży",
              fillColor : "rgba(204,208,210,0.8)",
              strokeColor : "rgba(140,150,160,0.6)",   
              data : [ <?php echo implode(',', $IloscZam); ?> ]
            }
          ]
        }
        
        var lineChartDataWykresWartosc<?php echo $infr['code']; ?> = {
          labels : [ <?php echo implode(',', (array)$TabOsX); ?> ],
          datasets : [
            {
              fillColor : "rgba(220,220,220,0.3)",
              strokeColor : "rgba(160,188,215,0.8)",
              pointColor : "rgba(255,255,255,1)",
              pointStrokeColor : "#707070",
              pointHighlightFill : "#707070",       
              pointHighlightStroke : "rgba(220,220,220,1)",      
              data : [ <?php echo implode(',', (array)$WartoscZam); ?> ]              
            }
          ]
        }        
        </script>          
         
        <?php
        unset($TabOsX, $WartoscZam, $IloscZam, $miesiac);
        
    }
}
?>
