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

    $zapytanie = "SELECT orders_id FROM orders WHERE " . $warunki_szukania . " ORDER BY orders_id desc";
    $sql = $db->open_query($zapytanie);

    unset($szukana_wartosc, $data);

    if ( (int)$db->ile_rekordow($sql) > 0 ) {

        $tabla_walut = array();
        $wartosc_calkowita = 0;
        $wartosc_calkowita_wysylki_platnosci = 0; 
        
        $ciag_do_zapisu = '';
        
        $ciag_do_zapisu .= 'Nr zamówienia;Imię i nazwisko;Liczba zamówionych produktów;Wartość zamówienia;Waluta;Koszt wysyłki + dopłata za rodzaj płatności;Wartość towaru;Forma płatności;Dostawa' ."\n";
        
        while ($info = $sql->fetch_assoc()) {

            $zamowienie = new Zamowienie($info['orders_id']);

            $wartosc_zamowienia = array( 'kwota' => '-', 'wartosc' => 0 );
            $wartosc_wysylki_platnosci = 0;
            
            foreach ( $zamowienie->podsumowanie as $suma ) {
                //
                // wartosc zamowienia
                if ( $suma['klasa'] == 'ot_total' ) {
                     $wartosc_zamowienia = array( 'kwota' => $suma['tekst'], 'wartosc' => $suma['wartosc'] );
                }
                //
                // koszt wysylki i platnosci
                if ( $suma['klasa'] == 'ot_shipping' || $suma['klasa'] == 'ot_payment' ) {
                     $wartosc_wysylki_platnosci += $suma['wartosc'];
                }
                //
            }

            $ciag_do_zapisu .= $info['orders_id'] . ';';
 
            $ciag_do_zapisu .= ((!empty($zamowienie->klient['firma'])) ? $zamowienie->klient['firma'] . ', ' : '') . 
                                   $zamowienie->klient['nazwa'] . ', '.
                                   $zamowienie->klient['ulica'] . ', '.
                                   $zamowienie->klient['kod_pocztowy'] . ' '. $zamowienie->klient['miasto'] . ';';              
            
            $suma_produktow = 0;
            foreach ( $zamowienie->produkty as $produkty ) {
                $suma_produktow += $produkty['ilosc'];
            }                 
            
            $ciag_do_zapisu .= $suma_produktow . ';';
            
            unset($suma_produktow);
            
            $ciag_do_zapisu .= $wartosc_zamowienia['wartosc'] . ';';
            $ciag_do_zapisu .= $zamowienie->info['waluta'] . ';';
            $ciag_do_zapisu .= $wartosc_wysylki_platnosci . ';';
            $ciag_do_zapisu .= ($wartosc_zamowienia['wartosc'] - $wartosc_wysylki_platnosci) . ';';
            $ciag_do_zapisu .= $zamowienie->info['metoda_platnosci'] . ';';
            $ciag_do_zapisu .= $zamowienie->info['wysylka_modul'] . ';';
            
            $ciag_do_zapisu .= "\n";

            unset($zamowienie);                            

        }     

        unset($info);

        header("Content-Type: application/force-download\n");
        header("Cache-Control: cache, must-revalidate");   
        header("Pragma: public");
        header("Content-Disposition: attachment; filename=raport_zamowienia_" . $data_od . "_" . $data_do . ".csv");
        print $ciag_do_zapisu;
        exit;            
              
    } else {

        Funkcje::PrzekierowanieURL('raport_zamowienia.php');

    }

    $db->close_query($sql);
    
}    
?>