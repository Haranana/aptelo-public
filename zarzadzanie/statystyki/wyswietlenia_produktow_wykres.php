<?php
if ( isset($prot) ) {
  
    if ( $prot->wyswietlStrone) {

        $zapytanieWykres = "select p.products_id, pd.products_name, pd.products_viewed from products p, products_description pd where p.products_id = pd.products_id and pd.language_id = '".$IdJezyka."' and pd.products_viewed > 0 order by pd.products_viewed desc, pd.products_name DESC limit 20";
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
            $TablicaWartosci[] = (float)$infoWykres['products_viewed'];
            //
        }

        $db->close_query($sqlWykres);
        unset($infoWykres, $zapytanieWykres);
        ?>
        
        <script>
        var lineChartDataProdukty = {
          labels : [ <?php echo implode(',', (array)$TabOsX); ?> ],
          datasets : [
            {
              label: "20 najczęściej oglądanych produktów",
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