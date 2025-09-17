<?php
if ( isset($prot) ) {
  
    if ( $prot->wyswietlStrone) {
      
        $TablicaKolorowJasne = array('#F7464A', '#46BFBD', '#FDB45C', '#949FB1');
        $TablicaKolorow = array('#FF5A5E', '#5AD3D1', '#FFC870', '#A8B3C5');      
        $TablicaZamowien = array();
    
        // oblicza ogolna ilosc zamowien wg parametrow
        $zapytanieCalosc = "select orders_id, date_purchased from orders where device != '' " . $warunki_szukania;
        $sqlc = $db->open_query($zapytanieCalosc);
        $SumaZamowien = (int)$db->ile_rekordow($sqlc);
        unset($zapytanieCalosc);    
        
        // komputer
        $zapytanie = "select orders_id, date_purchased from orders where device LIKE '%laptop%' " . $warunki_szukania;
        $sql = $db->open_query($zapytanie);
        //     
        $TablicaZamowien[] = array( 'Komputer stacjonarny / laptop ' . ' ('.round((((int)$db->ile_rekordow($sql) / $SumaZamowien) * 100), 2).'%)',
                                    (int)$db->ile_rekordow($sql),
                                    $TablicaKolorowJasne[ 0 ],
                                    $TablicaKolorow[ 0 ] );
        //
        $db->close_query($sql);
        
        // tablet
        $zapytanie = "select orders_id, date_purchased from orders where device LIKE '%ablet%' " . $warunki_szukania;
        $sql = $db->open_query($zapytanie);                  
        //
        $TablicaZamowien[] = array( 'Tablet ' . ' ('.round((((int)$db->ile_rekordow($sql) / $SumaZamowien) * 100), 2).'%)',
                                    (int)$db->ile_rekordow($sql),
                                    $TablicaKolorowJasne[ 1 ],
                                    $TablicaKolorow[ 1 ] );
        //
        $db->close_query($sql);      

        // telefon
        $zapytanie = "select orders_id, date_purchased from orders where device LIKE '%phone%' " . $warunki_szukania;
        $sql = $db->open_query($zapytanie);                  
        //
        $TablicaZamowien[] = array( 'Smartphone ' . ' ('.round((((int)$db->ile_rekordow($sql) / $SumaZamowien) * 100), 2).'%)',
                                    (int)$db->ile_rekordow($sql),
                                    $TablicaKolorowJasne[ 2 ],
                                    $TablicaKolorow[ 2 ] );
        //
        $db->close_query($sql);   

        ?>
        
        <script>
        var pieData = [
        <?php
        foreach ( $TablicaZamowien as $Zamow ) {
            //
            echo '{ value: ' . $Zamow[1] . ', color:"' . $Zamow[2] . '", highlight: "' . $Zamow[3] . '", label: "' . $Zamow[0] . '" },';
            //
        }
        ?>
        ];
        </script>        
        
        <?php
        
        unset($TablicaZamowien); 
    } 

}
?>