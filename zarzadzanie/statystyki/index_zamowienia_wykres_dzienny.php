<?php
if ( isset($prot) ) {
  
    if ( $prot->wyswietlStrone) {
      
        $TabOsX = array();
        $IloscZam = array();
        $WartoscZam = array();

        $Ilosc          = array();
        $Przedzial      = array();
        $Wynik          = array();

        $Okres          = '15';
        $dataBiezaca    = date('Y-m-d', FunkcjeWlasnePHP::my_strtotime("+1 day"));
        $dataPoczatkowa = date('Y-m-d', FunkcjeWlasnePHP::my_strtotime("-".$Okres." day"));

        // policzenie ilosci w zadanym okresie czasu
        $zapytanie = "SELECT DATE(o.date_purchased) AS data, SUM(ot.value/o.currency_value) AS wartosc, COUNT(o.date_purchased) AS ilosc
                          FROM orders o
                          LEFT JOIN orders_total ot ON ot.orders_id = o.orders_id AND ot.class = 'ot_total'
                          WHERE o.date_purchased BETWEEN '".$dataPoczatkowa."' AND '".$dataBiezaca."'
                          GROUP BY DATE(o.date_purchased)";

        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $Ilosc[$info['data']] = array($info['wartosc'], $info['ilosc']);
        }

        // utworzenie tablicy dni dla zadanego przedzialu czasu
        for ($z = 0; $z < $Okres; $z++) {
            $tmp = date('Y-m-d', FunkcjeWlasnePHP::my_strtotime("-".$z." day"));
            $Przedzial[] = $tmp;
        }
        unset($z, $tmp);

        // utworzenie tablicy dni z wartosciami i ilosciami w kazdym dniu
        for ( $i = 0, $c = count($Przedzial); $i < $c; $i++ ) {
            if ( isset($Ilosc[$Przedzial[$i]]) ) {
                $Wynik[$Przedzial[$i]] = array($Ilosc[$Przedzial[$i]][0], $Ilosc[$Przedzial[$i]][1]);
            } else {
                $Wynik[$Przedzial[$i]] = array(0, 0);
            }
        }

        unset($i, $c, $Przedzial);
        $db->close_query($sql);
        unset($info, $zapytanie);

        $WynikOdwrocony = array_reverse($Wynik);

        foreach ( $WynikOdwrocony as $key => $value) {

            // tablica do wartosci zamowien
            $dataZamowienia = date("d-m", FunkcjeWlasnePHP::my_strtotime($key));
            
            $dataZamowienia = str_replace('-01',' stycznia', (string)$dataZamowienia);
            $dataZamowienia = str_replace('-02',' lutego', (string)$dataZamowienia);
            $dataZamowienia = str_replace('-03',' marca', (string)$dataZamowienia);
            $dataZamowienia = str_replace('-04',' kwietnia', (string)$dataZamowienia);
            $dataZamowienia = str_replace('-05',' maja', (string)$dataZamowienia);
            $dataZamowienia = str_replace('-06',' czerwca', (string)$dataZamowienia);
            $dataZamowienia = str_replace('-07',' lipca', (string)$dataZamowienia);
            $dataZamowienia = str_replace('-08',' sierpnia', (string)$dataZamowienia);
            $dataZamowienia = str_replace('-09',' września', (string)$dataZamowienia);
            $dataZamowienia = str_replace('-10',' października', (string)$dataZamowienia);
            $dataZamowienia = str_replace('-11',' listopada', (string)$dataZamowienia);
            $dataZamowienia = str_replace('-12',' grudnia', (string)$dataZamowienia);
            
            $TabOsX[] = '"' . $dataZamowienia . '"';    

            $IloscZam[] = $value[1];
            unset($tmp);

            $WartoscZam[] = '"' . $value[0] . '"';
            unset($tmp);

        }

        ?>

        <script>
        function KwotaChart(nStr) {
          var ciag = format_zl(nStr);
          return ciag.replace('.', ',');
        }
        
        var lineChartDataZamowieniaWartosc = {
          labels : [ <?php echo implode(',', (array)$TabOsX); ?> ],
          datasets : [
            {
              label: "Zamówienia za ostatnie 15 dni",
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

        var lineChartDataZamowieniaIlosc = {
          labels : [ <?php echo implode(',', (array)$TabOsX); ?> ],
          datasets : [
            {
              label: "Zamówienia za ostatnie 15 dni",
              fillColor : "rgba(204,208,210,0.8)",
              strokeColor : "rgba(140,150,160,0.6)",           
              data : [ <?php echo implode(',', (array)$IloscZam); ?> ]
            }  
          ]
        }
        </script>  
        
        <script src="statystyki/index_zamowienia_wykres_dzienny.js"></script> 

        <?php
        unset($TabOsX, $WartoscZam, $IloscZam);
    } 

}
?>