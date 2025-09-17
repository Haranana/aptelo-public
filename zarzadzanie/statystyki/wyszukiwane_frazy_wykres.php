<?php
if ( isset($prot) ) {
  
    if ( $prot->wyswietlStrone) {
      
        $TabOsX = array();
        $TablicaWartosci = array();      
      
        $zapytanieWykres = "select * from customers_searches where language_id = '".$IdJezyka."' order by freq desc limit 20";
        $sqlWykres = $db->open_query($zapytanieWykres);
        
        while ($infoWykres = $sqlWykres->fetch_assoc()) {
            //
            if ($infoWykres['freq'] > 0) {
                //
                $TabOsX[] = '"' . str_replace('"','\"',$infoWykres['search_key']) . '"';
                $TablicaWartosci[] = (int)$infoWykres['freq'];
                //              
            }
            // 
        }
        
        $db->close_query($sqlWykres);
        unset($infoWykres, $zapytanieWykres);
        
        ?>
        
        <script>
        var lineChartDataFrazy = {
          labels : [ <?php echo implode(',', (array)$TabOsX); ?> ],
          datasets : [
            {
              label: "20 najczęściej wyszukiwanych fraz w sklepie",
              fillColor : "rgba(154,198,83,0.4)",
              strokeColor : "rgba(154,198,83,0.8)",           
              data : [ <?php echo implode(',', (array)$TablicaWartosci); ?> ]
            }  
          ]
        }
        </script>          
        
        <?php
        
        unset($TabOsX, $TablicaWartosci);

    } 

}