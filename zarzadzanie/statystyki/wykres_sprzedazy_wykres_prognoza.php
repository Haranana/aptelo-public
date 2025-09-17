<?php
if ( isset($prot) ) {
  
    if ( $prot->wyswietlStrone) {
    
        $zapytanieWaluty = "select symbol, code, title from currencies";
        $sqlWaluta = $db->open_query($zapytanieWaluty);
        
        $TabWynik = array();
        $Lb = 0;
        
        while ($infr = $sqlWaluta->fetch_assoc()) {

            $zapytanie = "select o.orders_id,
                                 o.currency,
                                 o.date_purchased, 
                                 ot.orders_id,
                                 ot.value, 
                                 ot.class
                            from orders o, orders_total ot
                            where o.orders_id = ot.orders_id and ot.class = 'ot_total' and o.currency = '" . $infr['code'] . "'
                                 and year(o.date_purchased) = '".date('Y',time())."' and month(o.date_purchased) = '".date('m',time())."'";                                    
                                 
                                 
            $sql = $db->open_query($zapytanie);
            
            $WartoscZamowien = 0;
            
            while ($info = $sql->fetch_assoc()) {
                //
                $WartoscZamowien = $WartoscZamowien + $info['value'];
                //
            }            
            $db->close_query($sql);

            $TabWynik[$Lb]['waluta'] = $infr['title'];
            $TabWynik[$Lb]['walutaKod'] = $infr['symbol'];
            $TabWynik[$Lb]['wartosc'] = $WartoscZamowien;
            
            $Lb++;

        }  
        
        $Legenda = array();
        $Wartosci = array();

        for ($v = 0, $c = count($TabWynik); $v < $c; $v++) {
            //                    
            $dzisiejszyDzien = date("j");
            $iloscDniMiesiac = date("t");
            $m_amt = round( $TabWynik[$v]['wartosc'], 0 );
            $prognoza = round((($m_amt / $dzisiejszyDzien) * $iloscDniMiesiac), 0);                           
            //
            $Legenda[] = '"' . $TabWynik[$v]['walutaKod'] . '"';
            $Wartosci[] = $prognoza;    
            //
            unset($prognoza);
            //
        }   
        //
        ?>
        
        <script>
        var lineChartDataPrognoza = {
          labels : [ <?php echo implode(',', (array)$Legenda); ?> ],
          datasets : [
            {
              label: "Prognoza sprzedaży",
              fillColor : "rgba(131,188,37,0.3)",
              strokeColor : "rgba(131,188,37,0.6)",    
              data : [ <?php echo implode(',', (array)$Wartosci); ?> ]
            }
          ]
        }
        </script>          
        
        <?php
        
        unset($Legenda, $Wartosci);
    }
}
?>
