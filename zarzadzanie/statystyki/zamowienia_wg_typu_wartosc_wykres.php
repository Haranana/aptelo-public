<?php
if ( isset($prot) ) {
  
    if ( $prot->wyswietlStrone) {
    
        $zapytanieWaluty = "select code, title, symbol from currencies";
        $sqlWaluta = $db->open_query($zapytanieWaluty);
        
        $TabWynik = array();
        $Lb = 0;
        
        while ($infr = $sqlWaluta->fetch_assoc()) {
        
            $TabWynik[$Lb]['waluta'] = $infr['title'];
            $TabWynik[$Lb]['walutaKod'] = $infr['code'];

            for ($n = 1; $n < 5; $n++) {
            
                $zapytanie = "select sum(value) as suma_zamowien , o.currency
                                from orders o, orders_total ot
                               where o.orders_source = '".$n."' and o.orders_id = ot.orders_id and ot.class = 'ot_total' and o.currency = '" . $infr['code'] . "'" . $warunki_szukania;       
                 
                $sql = $db->open_query($zapytanie);
                
                $info = $sql->fetch_assoc();
                $WartoscZamowien = $info['suma_zamowien'];           
                $db->close_query($sql);

                $TabWynik[$Lb]['wartosc_' . $n] = (int)$WartoscZamowien;
            
            }
            
            $Lb++;

        }  
        
        $Legenda = array();
        $WartoscZam_1 = array();
        $WartoscZam_2 = array();
        $WartoscZam_3 = array();
        $WartoscZam_4 = array();

        for ($v = 0, $c = count($TabWynik); $v < $c; $v++) {
            //                    
            $Legenda[] = '"' . $TabWynik[$v]['waluta'] . '"';
            //
            // zamowienia z rejestracja klienta
            $WartoscZam_1[] = $TabWynik[$v]['wartosc_1'];
            
            // zamowienia bez rejestracji klienta
            $WartoscZam_2[] = $TabWynik[$v]['wartosc_2'];
      
            // zamowienia z allegro
            $WartoscZam_3[] = $TabWynik[$v]['wartosc_3'];
       
            // zamowienia reczne
            $WartoscZam_4[] = $TabWynik[$v]['wartosc_4'];       
            //        
        }   

        ?>
        
        <script>
        function KwotaChart(nStr) {
          var ciag = format_zl(nStr);
          return ciag.replace('.', ',');
        }
        
        var lineChartDataZamowieniaWartosc = {
          labels : [ <?php echo implode(',', (array)$Legenda); ?> ],
          datasets : [
            {
              label: "Zamówienia z rejestracją klienta",
              fillColor : "rgba(247,70,74,0.8)",
              strokeColor : "rgba(255,255,255,1)",      
              data : [ <?php echo implode(',', (array)$WartoscZam_1); ?> ]
            },
            {
              label: "Zamówienia bez rejestracji klienta",
              fillColor : "rgba(70,191,189,0.8)",
              strokeColor : "rgba(255,255,255,1)",       
              data : [ <?php echo implode(',', (array)$WartoscZam_2); ?> ]
            },
            {
              label: "Zamówienia z Allegro",
              fillColor : "rgba(253,180,92,0.8)",
              strokeColor : "rgba(255,255,255,1)",       
              data : [ <?php echo implode(',', (array)$WartoscZam_3); ?> ]
            },
            {
              label: "Zamówienia ręczne",
              fillColor : "rgba(148,159,177,0.8)",
              strokeColor : "rgba(255,255,255,1)",       
              data : [ <?php echo implode(',', (array)$WartoscZam_4); ?> ]
            }              
          ]
        }

        </script>          
        
        <?php

        unset($TablicaZamowien); 
    } 

}
?>
