<?php
if ( isset($klienci) && $klienci == 'eksport' ) {

    $warunki_szukania = '';

    $CzescPliku = '';
    
    if ( isset($_GET['rejestracja_data_od']) && $_GET['rejestracja_data_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['rejestracja_data_od'] . ' 00:00:00')));
        $CzescPliku .= '_rejestracja_od_' . date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['rejestracja_data_od'] . ' 00:00:00')));
        $warunki_szukania .= " AND ci.customers_info_date_account_created >= '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }

    if ( isset($_GET['rejestracja_data_do']) && $_GET['rejestracja_data_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['rejestracja_data_do'] . ' 23:59:59')));
        $CzescPliku .= '_rejestracja_do_' . date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['rejestracja_data_do'] . ' 23:59:59')));
        $warunki_szukania .= " AND ci.customers_info_date_account_created <= '".$szukana_wartosc."'";                        
        unset($szukana_wartosc);
    }  

    if ( isset($_GET['zamowienia_data_od']) && $_GET['zamowienia_data_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['zamowienia_data_od'] . ' 00:00:00')));
        $CzescPliku .= '_zamowienia_do_' . date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['zamowienia_data_od'] . ' 00:00:00')));
        $warunki_szukania .= " AND o.date_purchased >= '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }

    if ( isset($_GET['zamowienia_data_do']) && $_GET['zamowienia_data_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['zamowienia_data_do'] . ' 23:59:59')));
        $CzescPliku .= '_zamowienia_do_' . date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['zamowienia_data_do'] . ' 23:59:59')));
        $warunki_szukania .= " AND o.date_purchased <= '".$szukana_wartosc."'";                        
        unset($szukana_wartosc);
    }   

    // jezeli jest wybrana grupa klienta
    if (isset($_GET['klienci']) && (int)$_GET['klienci'] > 0) {
        $id_grupy = (int)$_GET['klienci'];
        $warunki_szukania .= " AND c.customers_groups_id = '" . $id_grupy . "'";        
        unset($id_grupy);
    }           
    
    $JakaWaluta = $_SESSION['domyslna_waluta']['kod'];
    if (isset($_GET['waluta'])) {
        $JakaWaluta = $filtr->process($_GET['waluta']);
    }    
    
    $zapytanie = "select c.customers_id, 
                         c.customers_firstname, 
                         c.customers_lastname,
                         c.customers_email_address,
                         c.customers_groups_id,
                         ci.customers_info_date_account_created,
                         c.customers_guest_account, 
                         count(DISTINCT o.orders_id) as ilosc_zamowien, 
                         sum(ot.value) as wartosc_zamowien,
                         o.currency
                    from customers c, 
                         orders_total ot, 
                         orders o, 
                         customers_info ci 
                   where c.customers_id = ci.customers_info_id AND 
                         c.customers_id = o.customers_id AND
                         o.orders_id = ot.orders_id AND
                         ot.class = 'ot_total' AND
                         o.currency = '" . $JakaWaluta . "' AND
                         c.customers_guest_account = '0' " . $warunki_szukania . "
                   group by c.customers_id, c.customers_firstname, c.customers_lastname order by " . ((isset($_GET['typ']) && $_GET['typ'] == 'ilosc') ? "ilosc_zamowien" : "wartosc_zamowien") . " DESC";
                   
    $sql = $db->open_query($zapytanie);

    unset($szukana_wartosc, $data);

    if ( (int)$db->ile_rekordow($sql) > 0 ) {

        $tabla_walut = array();
        $wartosc_calkowita = 0;
        $wartosc_calkowita_wysylki_platnosci = 0; 
        
        $ciag_do_zapisu = '';
        
        $ciag_do_zapisu .= 'Imię;Nazwisko;Adres email;Ilość zamówień;Wartość zamówień' ."\n";
        
        while ($info = $sql->fetch_assoc()) {
          
            $ciag_do_zapisu .= $info['customers_firstname'] . ';';
            $ciag_do_zapisu .= $info['customers_lastname'] . ';';
            $ciag_do_zapisu .= $info['customers_email_address'] . ';';
            $ciag_do_zapisu .= $info['ilosc_zamowien'] . ';';
            $ciag_do_zapisu .= $info['wartosc_zamowien'] . ';';
            
            $ciag_do_zapisu .= "\n";
            
        }     

        unset($info);

        header("Content-Type: application/force-download\n");
        header("Cache-Control: cache, must-revalidate");   
        header("Pragma: public");
        header("Content-Disposition: attachment; filename=klienci_zamowienia" . $CzescPliku . '_' . $JakaWaluta . ".csv");
        print $ciag_do_zapisu;
        exit;            
              
    } else {

        Funkcje::PrzekierowanieURL('klienci_zamowienia.php');

    }

    $db->close_query($sql);
    
}    
?>