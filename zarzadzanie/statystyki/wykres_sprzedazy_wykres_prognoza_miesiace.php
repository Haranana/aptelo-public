<?php
if ( isset($prot) ) {
  
    if ( $prot->wyswietlStrone) {
    
        $zapytanieWaluty = "select symbol, code, title from currencies";
        $sqlWaluta = $db->open_query($zapytanieWaluty);
        
        $TabWynik = array();
        $Lb = 0;
        
        while ($infr = $sqlWaluta->fetch_assoc()) {
        
                        // data biezaca
                        $dateDo = new DateTime('now');
                        $dateOd = new DateTime('now');

                        // ostatni dzien daty biezacej
                        $dateDo->modify('last day of this month');
                        $DataDo = $dateDo->format('Y.m.d').' 23:59';

                        // pierwszy dzien daty biezacej - 3 miesiecy
                        $dateOd->modify('first day of -3 month');
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
            
            $WartoscZamowien = 0;
            
            while ($info = $sql->fetch_assoc()) {
                //
                $WartoscZamowien = $WartoscZamowien + $info['value'];
                //
            }            
            $db->close_query($sql);

            $TabWynik[$Lb]['waluta'] = $infr['title'];
            $TabWynik[$Lb]['walutaKod'] = $infr['symbol'];
            // przy 3 miesiacach podzielic na 3
            $TabWynik[$Lb]['wartosc'] = $WartoscZamowien / 3;
            
            $Lb++;

        }  

        $Legenda = array();
        $Wartosci = array();
        
        $WartoscMax = 0;
        
        for ($v = 0, $c = count($TabWynik); $v < $c; $v++) {
            //                    
            $prognoza = round( $TabWynik[$v]['wartosc'], 0 );                         
            //
            $Legenda[] = '"' . $TabWynik[$v]['walutaKod'] . '"';
            $Wartosci[] = $prognoza;        
            //
            unset($prognoza);
            //
        }   
        ?>
        
        <script>
        var lineChartDataPrognozaMiesiace = {
          labels : [ <?php echo implode(',', (array)$Legenda); ?> ],
          datasets : [
            {
              label: "Prognoza sprzedaży",
              fillColor : "rgba(154,219,237,0.6)",
              strokeColor : "rgba(154,219,237,0.8)",    
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
