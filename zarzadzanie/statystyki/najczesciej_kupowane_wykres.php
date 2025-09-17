<?php
if ( isset($prot) ) {
  
    if ( $prot->wyswietlStrone) {

        // szukanie produktow
        $zapytanieWykres = "select o.date_purchased, 
                             op.products_name, 
                             op.products_id, 
                             sum(op.products_quantity) as ilosc
                        from orders as o, orders_products as op 
                        where o.orders_id = op.orders_id ".$warunki_szukania."
                        GROUP by products_id ORDER BY ilosc DESC, op.products_name limit 20";
                        
        $sqlWykres = $db->open_query($zapytanieWykres);                

        $TabOsX = array();
        $TablicaWartosci = array();

        while ($infoWykres = $sqlWykres->fetch_assoc()) {
            //
            if ($infoWykres['products_name'] == '') {
                $infoWykres['products_name'] = '-- brak nazwy --';
            }
            //
            $TabOsX[] = '"' . FunkcjeWlasnePHP::my_htmlentities($infoWykres['products_name']) . '"';
            $TablicaWartosci[] = (float)$infoWykres['ilosc'];
            //
        }

        $db->close_query($sqlWykres);
        
        ?>
        
        <script>
        var lineChartDataProdukty = {
          labels : [ <?php echo implode(',', (array)$TabOsX); ?> ],
          datasets : [
            {
              label: "20 najczęściej kupowanych produktów",
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
?>