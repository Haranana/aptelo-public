<?php
if ( isset($zamowienia) && $zamowienia == 'eksport' ) {

    $warunki_szukania = 'orders_id > 0 ';
    $data = false;

    $data_poczatkowa = date('Y') . "-" . date('m') . "-01";
    $data_koncowa = date('Y') . "-" . date('m') . "-" . date('d');    
    
    $data_od = $data_poczatkowa;
    $data_do = $data_koncowa;
    
    if ( isset($_GET['data_od']) && $_GET['data_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['data_od'] . ' 00:00:00')));
        $data_od = date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['data_od'] . ' 00:00:00')));
        $warunki_szukania .= " and date_purchased >= '".$szukana_wartosc."'";
        $data = true;
        unset($szukana_wartosc);
    }

    if ( isset($_GET['data_do']) && $_GET['data_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['data_do'] . ' 23:59:59')));
        $data_do = date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['data_do'] . ' 23:59:59')));
        $warunki_szukania .= " and date_purchased <= '".$szukana_wartosc."'";                        
        $data = true;
        unset($szukana_wartosc);
    }       
    
    // jezeli nic nie wypelnione przyjmuje dziesiejsza date
    if ($data == false) {
        $warunki_szukania .= " and date_purchased >= '". $data_poczatkowa . " 00:00:00' and date_purchased <= '". $data_koncowa . " 23:59:59'";
    }                    

    if ( isset($_GET['szukaj_status']) && (int)$_GET['szukaj_status'] > 0 ) {
        $warunki_szukania .= " and orders_status = " . (int)$_GET['szukaj_status'] . " ";
    }                              

    $zapytanie = "SELECT reference FROM orders WHERE " . $warunki_szukania . " ORDER BY orders_id desc";
    $sql = $db->open_query($zapytanie);

    unset($szukana_wartosc, $data);
    
    $tablica_zrodla = array();
    
    if ( (int)$db->ile_rekordow($sql) > 0 ) {

        while ($info = $sql->fetch_assoc()) {
            //
            if ( !empty($info['reference']) ) {
                 //
                 $info['reference'] = base64_encode(str_replace( array('http://','https://'), '', $info['reference']));
                 //
                 if ( !isset($tablica_zrodla[$info['reference']]) ) {
                      //
                      $tablica_zrodla[$info['reference']] = 1;
                      //
                 } else {
                      //
                      $tablica_zrodla[$info['reference']] = $tablica_zrodla[$info['reference']] + 1;
                      //
                 }
                 //
            }
            //
        }

        unset($info, $wartosc_calkowita, $wartosc_calkowita_wysylki_platnosci);

    }
    
    if ( count($tablica_zrodla) > 0 ) {
      
         arsort($tablica_zrodla);    
         
         $ciag_do_zapisu = 'Strona;Zamówienia' ."\n";
         
         foreach ( $tablica_zrodla as $zrodlo => $ilosc ) {
           
             $ciag_do_zapisu .= base64_decode($zrodlo) . ';';
             $ciag_do_zapisu .= $ilosc . "\n";
           
         }
                         
         header("Content-Type: application/force-download\n");
         header("Cache-Control: cache, must-revalidate");   
         header("Pragma: public");
         header("Content-Disposition: attachment; filename=raport_zamowienia_wg_zrodel_" . $data_od . "_" . $data_do . ".csv");
         print $ciag_do_zapisu;
         exit;            
              
    } else {

        Funkcje::PrzekierowanieURL('zamowienia_wg_zrodel.php');

    }

    $db->close_query($sql);
    
}    
?>