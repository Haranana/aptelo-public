<?php
if ( isset($prot) ) {
  
    if ( $prot->wyswietlStrone) {
      
        $TabOsX = array();
        $IloscKlientow = array();

        $Ilosc          = array();
        $Przedzial      = array();
        $Wynik          = array();

        $dataBiezaca    = date('Y-m-d') . ' 23:59:59';
        $dataPoczatkowa = date('Y-m-d', FunkcjeWlasnePHP::my_strtotime("-".$Okres." month")) . ' 00:00:00';

        // policzenie ilosci w zadanym okresie czasu
        $zapytanie = "SELECT EXTRACT(YEAR FROM ci.customers_info_date_account_created) AS rok, EXTRACT(MONTH FROM ci.customers_info_date_account_created) AS miesiac, COUNT( ci.customers_info_date_account_created ) AS ilosc
                          FROM customers_info ci
                          RIGHT JOIN customers c ON c.customers_id = ci.customers_info_id
                          WHERE ci.customers_info_date_account_created BETWEEN '".$dataPoczatkowa."' AND '".$dataBiezaca."' AND c.customers_guest_account = '0'
                          GROUP BY MONTH(ci.customers_info_date_account_created), YEAR(ci.customers_info_date_account_created)";

        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $Ilosc[$info['rok'].'-'.($info['miesiac'] < 10 ? '0'.$info['miesiac'] : $info['miesiac'])] = $info['ilosc'];
        }

        // utworzenie tablicy dni dla zadanego przedzialu czasu
        for ($z = 0; $z < $Okres; $z++) {
            $tmp = date('Y-m', FunkcjeWlasnePHP::my_strtotime("-".$z." month"));
            $Przedzial[] = $tmp;
        }
        unset($z, $tmp);

        // utworzenie tablicy dni z ilosciami w kazdym dniu
        for ( $i = 0, $c = count($Przedzial); $i < $c; $i++ ) {
            if ( isset($Ilosc[$Przedzial[$i]]) ) {
                $Wynik[$Przedzial[$i]] = $Ilosc[$Przedzial[$i]];
            } else {
                $Wynik[$Przedzial[$i]] = 0;
            }
        }

        unset($i, $c, $Przedzial);
        $db->close_query($sql);
        unset($info, $zapytanie);

        $WynikOdwrocony = array_reverse($Wynik);

        foreach ( $WynikOdwrocony as $key => $value) {

            $dataOsX = date("m-Y", FunkcjeWlasnePHP::my_strtotime($key));
            
            $dataOsX = str_replace('01-','styczeń ', (string)$dataOsX);
            $dataOsX = str_replace('02-','luty ', (string)$dataOsX);
            $dataOsX = str_replace('03-','marzec ', (string)$dataOsX);
            $dataOsX = str_replace('04-','kwiecień ', (string)$dataOsX);
            $dataOsX = str_replace('05-','maj ', (string)$dataOsX);
            $dataOsX = str_replace('06-','czerwiec ', (string)$dataOsX);
            $dataOsX = str_replace('07-','lipiec ', (string)$dataOsX);
            $dataOsX = str_replace('08-','sierpień ', (string)$dataOsX);
            $dataOsX = str_replace('09-','wrzesień ', (string)$dataOsX);
            $dataOsX = str_replace('10-','październik ', (string)$dataOsX);
            $dataOsX = str_replace('11-','listopad ', (string)$dataOsX);
            $dataOsX = str_replace('12-','grudzień ', (string)$dataOsX);
            
            $TabOsX[] = '"' . $dataOsX . '"';
            $IloscKlientow[] = (int)$value;
            
            unset($dataOsX);

        }    
        
        ?>
        
        <script>
        var lineChartDataKlienciIloscMiesiace = {
          labels : [ <?php echo implode(',', (array)$TabOsX); ?> ],
          datasets : [
            {
              label: "Rejestracje klientów za ostatnie 24 miesiące",
              fillColor : "rgba(220,220,220,0.3)",
              strokeColor : "rgba(131,188,37,0.6)",
              pointColor : "rgba(255,255,255,1)",
              pointStrokeColor : "#707070",
              pointHighlightFill : "#707070",    
              pointHighlightStroke : "rgba(220,220,220,1)",       
              data : [ <?php echo implode(',', (array)$IloscKlientow); ?> ]
            }  
          ]
        }
        </script>          
        
        <?php
        
        unset($TabOsX, $IloscKlientow);

    } 

}
?>
