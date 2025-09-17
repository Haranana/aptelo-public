<?php
if ( isset($prot) ) {
  
    if ( $prot->wyswietlStrone) {
      
        $TablicaKolorowJasne = array('#F7464A', '#46BFBD', '#FDB45C', '#949FB1', '#4D5360');
        $TablicaKolorow = array('#FF5A5E', '#5AD3D1', '#FFC870', '#A8B3C5', '#616774');      
        $TablicaZamowien = array();
    
        // oblicza ogolna ilosc zamowien wg parametrow
        $zapytanieCalosc = "select orders_id, date_purchased from orders where (orders_source = '1' || orders_source = '2' || orders_source = '3' || orders_source = '4') " . $warunki_szukania;
        $sqlc = $db->open_query($zapytanieCalosc);
        $SumaZamowien = (int)$db->ile_rekordow($sqlc);
        unset($zapytanieCalosc);    
        
        // zamowienia bez rejestracji
        $zapytanie = "select orders_id, date_purchased from orders where orders_source = '2' " . $warunki_szukania;
        $sql = $db->open_query($zapytanie);
        //     
        $TablicaZamowien[] = array( 'Zamówienia bez rejestracji konta ' . ' ('.round((((int)$db->ile_rekordow($sql) / $SumaZamowien) * 100), 2).'%)',
                                    (int)$db->ile_rekordow($sql),
                                    $TablicaKolorowJasne[ 0 ],
                                    $TablicaKolorow[ 0 ] );
        //
        $db->close_query($sql);
        
        // zamowienia z rejestracja
        $zapytanie = "select orders_id, date_purchased from orders where orders_source = '1' " . $warunki_szukania;
        $sql = $db->open_query($zapytanie);                  
        //
        $TablicaZamowien[] = array( 'Zamówienia z rejestracją konta ' . ' ('.round((((int)$db->ile_rekordow($sql) / $SumaZamowien) * 100), 2).'%)',
                                    (int)$db->ile_rekordow($sql),
                                    $TablicaKolorowJasne[ 1 ],
                                    $TablicaKolorow[ 1 ] );
        //
        $db->close_query($sql);      

        // zamowienia reczne
        $zapytanie = "select orders_id, date_purchased from orders where orders_source = '3' " . $warunki_szukania;
        $sql = $db->open_query($zapytanie);                  
        //
        $TablicaZamowien[] = array( 'Zamówienia z Allegro ' . ' ('.round((((int)$db->ile_rekordow($sql) / $SumaZamowien) * 100), 2).'%)',
                                    (int)$db->ile_rekordow($sql),
                                    $TablicaKolorowJasne[ 2 ],
                                    $TablicaKolorow[ 2 ] );
        //
        $db->close_query($sql);   

        // zamowienia z Allegro
        $zapytanie = "select orders_id, date_purchased from orders where orders_source = '4' " . $warunki_szukania;
        $sql = $db->open_query($zapytanie);                    
        //
        $TablicaZamowien[] = array( 'Zamówienia ręczne ' . ' ('.round((((int)$db->ile_rekordow($sql) / $SumaZamowien) * 100), 2).'%)',
                                    (int)$db->ile_rekordow($sql),
                                    $TablicaKolorowJasne[ 3 ],
                                    $TablicaKolorow[ 3 ] );
        //
        $db->close_query($sql);   
        
        unset($zapytanie, $TablicaKolorow, $TablicaKolorowJasne);
        
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