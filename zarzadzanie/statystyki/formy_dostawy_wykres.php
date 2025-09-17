<?php
if ( isset($prot) ) {
  
    if ( $prot->wyswietlStrone) {
      
      $TablicaKolorowJasne = array('#F7464A', '#46BFBD', '#FDB45C', '#949FB1', '#4D5360');
      $TablicaKolorow = array('#FF5A5E', '#5AD3D1', '#FFC870', '#A8B3C5', '#616774');
      $TablicaZamowien = array();

      // oblicza ogolna ilosc zamowien wg parametrow
      $zapytanieCalosc = "select shipping_module, date_purchased from orders where shipping_module != '' " . $warunki_szukania;
      $sqlc = $db->open_query($zapytanieCalosc);
      $SumaZamowien = (int)$db->ile_rekordow($sqlc);
      unset($zapytanieCalosc);

      $zapytanie = "select distinct shipping_module from orders";
      $sql = $db->open_query($zapytanie);
      while ($info = $sql->fetch_assoc()) {
          //
          $zapytanieJedn = "select shipping_module, date_purchased from orders where shipping_module = '".$info['shipping_module']."'" . $warunki_szukania;
          $sqlc = $db->open_query($zapytanieJedn);
          //
          if ((int)$db->ile_rekordow($sqlc) > 0) {
              //
              $Kolor = rand(0, count($TablicaKolorow) - 1);
              //
              $TablicaZamowien[] = array( $info['shipping_module'] . ' ('.round((((int)$db->ile_rekordow($sqlc) / $SumaZamowien) * 100), 2).'%)',
                                          (int)$db->ile_rekordow($sqlc),
                                          $TablicaKolorowJasne[ $Kolor ],
                                          $TablicaKolorow[ $Kolor ] );
              
              unset($Kolor);
          }
          //                    
      }
      $db->close_query($sql);
      unset($info, $zapytanie, $TablicaKolorow, $TablicaKolorowJasne);

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
      
      <script src="statystyki/formy_dostawy_wykres.js"></script> 

      <?php 
      
      unset($TablicaZamowien); 
    } 

}
?>