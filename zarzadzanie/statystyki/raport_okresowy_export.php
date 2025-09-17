<?php  
if ( isset($zamowienia) && $zamowienia == 'eksport' ) {

    // przedzial poczatkowy czasu - zawsze miesiac wczesniej
    $dzien = '01';
    $miesiac = date('m');
    $rok = date('Y');
    //
    $dzien_od = '01';
    $miesiac_od = (($miesiac > 1) ? $miesiac - 1 : '12');
    //
    if ( $miesiac_od < 10 ) {
         $miesiac_od = '0' . $miesiac_od;
    }
    //
    $rok_od = (($miesiac > 1) ? date('Y') : date('Y') - 1);                        
    //
    $data_poczatkowa = $rok_od . "-" . $miesiac_od . "-" . $dzien_od;
    $data_koncowa = $rok . "-" . $miesiac . "-" . $dzien;
    // dla kalendarza w postaci dd-mm-rr

    if ( isset($_GET['data_od']) && $_GET['data_od'] != '' ) {
        $data_poczatkowa_kalendarz = $_GET['data_od'];
    } else {
        $data_poczatkowa_kalendarz = $dzien_od . "-" . $miesiac_od . "-" . $rok_od;
    }
    if ( isset($_GET['data_do']) && $_GET['data_do'] != '' ) {
        $data_koncowa_kalendarz = $_GET['data_do'];             
    } else {
        $data_koncowa_kalendarz = $dzien . "-" . $miesiac . "-" . $rok;
    }
    //
    unset($dzien, $miesiac, $rok);

    $warunki_szukania = '';
    if ( isset($_GET['data_od']) && $_GET['data_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['data_od'] . ' 00:00:00')));
        $warunki_szukania .= " and date_purchased >= '".$szukana_wartosc."'";
    }

    if ( isset($_GET['data_do']) && $_GET['data_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['data_do'] . ' 23:59:59')));
        $warunki_szukania .= " and date_purchased <= '".$szukana_wartosc."'";                     
    }
    
    // jezeli nic nie wypelnione przyjmuje dziesiejsza date
    if ($warunki_szukania == '') {
        $warunki_szukania = " and date_purchased >= '". $data_poczatkowa . " 00:00:00' and date_purchased <= '". $data_koncowa . " 23:59:59'";
    }
    
    if ( isset($_GET['szukaj_status']) && (int)$_GET['szukaj_status'] > 0 ) {
        $warunki_szukania .= " and o.orders_status = " . (int)$_GET['szukaj_status'] . " ";
    }                    
    
    if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
        $warunki_szukania .= " and ptc.categories_id = '" . (int)$_GET['kategoria_id'] . "'";
    }                    
    
    if (isset($_GET['producent_id']) && (int)$_GET['producent_id'] > 0) {
        $warunki_szukania .= " and p.manufacturers_id = '" . (int)$_GET['producent_id'] . "'";
    }  

    $zapytanie = "SELECT op.final_price_tax AS wartosc_brutto, 
                         op.final_price AS wartosc_netto,
                         o.date_purchased, 
                         o.currency,
                         o.currency_value,
                         op.products_name, 
                         op.products_id,
                         op.products_quantity AS ilosc, 
                         op.products_model, 
                         op.orders_products_id,
                         p.manufacturers_id,
                         p.products_purchase_price,
                         IF ((SELECT distinct orders_id FROM orders_products_attributes WHERE orders_products_id = op.orders_products_id limit 1), GROUP_CONCAT(DISTINCT ap.products_options ,'#', ap.products_options_values ORDER BY ap.products_options, ap.products_options_values SEPARATOR '|' ), '') as cechy
                    FROM orders o 
                    LEFT JOIN orders_products op ON o.orders_id = op.orders_id
                    LEFT JOIN orders_products_attributes ap ON o.orders_id = ap.orders_id
                    LEFT JOIN products_to_categories ptc ON ptc.products_id = op.products_id
                    LEFT JOIN products p ON p.products_id = op.products_id
                    WHERE o.orders_id = op.orders_id AND 
                         IF ((SELECT distinct orders_id FROM orders_products_attributes WHERE orders_products_id = op.orders_products_id limit 1), op.orders_products_id = ap.orders_products_id, o.orders_id = o.orders_id)
                         " . $warunki_szukania . "
                GROUP BY op.orders_products_id ORDER BY " . ((isset($_GET['typ']) && $_GET['typ'] == 'data') ? 'o.date_purchased, ' : '') . "op.products_name, cechy";

    $sql = $db->open_query($zapytanie);

    if ((int)$db->ile_rekordow($sql) > 0) {

        $ciag_do_zapisu = 'Nr katalogowy;Nazwa produktu;Cena_zakupu;Ilość sprzedanych;Wartość netto;Wartość brutto' . "\n";                  
        
        if ( isset($_GET['typ']) && $_GET['typ'] == 'data' ) {
             //
             $ciag_do_zapisu = 'Data;' . $ciag_do_zapisu;
             //
        }
                     
        // tworzenie tymczasowej tablicy do usuwania duplikatow
        $produkty_duplikat = array();
        while ($info = $sql->fetch_assoc()) {
            //
            $produkty_duplikat[] = array('id' => $info['products_id'],
                                        'data_zamowienia' => date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['date_purchased'])),
                                        'model' => $info['products_model'],
                                        'nazwa' => $info['products_name'],
                                        'cechy' => $info['cechy'],
                                        'ilosc' => $info['ilosc'],
                                        'cena_zakupu' => $info['products_purchase_price'],
                                        'wartosc_netto' => $info['wartosc_netto'] * $info['ilosc'],
                                        'wartosc_brutto' => $info['wartosc_brutto'] * $info['ilosc'],
                                        'waluta' => $info['currency'],
                                        'przelicznik' => $info['currency_value'],
                                        'nazwa_cecha' => trim((string)$info['products_name']) . trim((string)$info['cechy']));
            //
        }
        
        // usuwanie duplikatow
        $produkty_bez_duplikatow = Statystyki::UsunDuplikaty($produkty_duplikat, ((isset($_GET['typ']) && $_GET['typ'] == 'data') ? 'data' : ''));

        // tworzenie tablicy koncowej z produktami bez duplkatow - dupliaty polaczne i zsumowane
        if ( isset($_GET['typ']) && $_GET['typ'] == 'data' ) {
            $typ = 'data';
        } else {
            $typ = '';
        }
        $produkty_koncowe = Statystyki::TablicaKoncowa($produkty_bez_duplikatow, $produkty_duplikat, $typ);

        unset($produkty_duplikat, $produkty_bez_duplikatow);
        
        $poprzednia_wartosc = '';

        foreach ($produkty_koncowe as $Produkt) {
        
            if ( isset($_GET['typ']) && $_GET['typ'] == 'data') {
                 //
                 $ciag_do_zapisu .= '"' . $Produkt['data_zamowienia'] . '";';                        
                 //
            }
            
            $ciag_do_zapisu .= '"' . $Produkt['model'] . '";';  
            
            $podzial_cech = explode('|', (string)$Produkt['cechy']);
            $tablica_cech = array();
        
            foreach ($podzial_cech as $Cecha) {
                //
                $PodzielCeche = explode('#', (string)$Cecha);
                //
                if ( isset($PodzielCeche[0]) && $PodzielCeche[0] != '' && isset($PodzielCeche[1]) && $PodzielCeche[1] != '' ) {
                     $tablica_cech[] = $PodzielCeche[0] . ': ' . $PodzielCeche[1];
                }
                //
            }
                
            $ciag_do_zapisu .= '"' . $Produkt['nazwa'] . ((count($tablica_cech) > 0) ? ', ' . implode(', ', (array)$tablica_cech) : '') . '";';  

            unset($podzial_cech, $tablica_cech);
            
            $ciag_do_zapisu .= '"' . $Produkt['cena_zakupu'] . '";';  
            
            $ciag_do_zapisu .= '"' . $Produkt['ilosc'] . '";';  
            $ciag_do_zapisu .= '"' . $Produkt['wartosc_netto'] . '";';  
            $ciag_do_zapisu .= '"' . $Produkt['wartosc_brutto'] . '"' . "\n";                           
            
            if (isset($_GET['typ']) && $_GET['typ'] == 'data') {
                $poprzednia_wartosc = $Produkt['data_zamowienia'];
              } else {
                $poprzednia_wartosc = $Produkt['nazwa'];
            }

        } 

        header("Content-Type: application/force-download\n");
        header("Cache-Control: cache, must-revalidate");   
        header("Pragma: public");
        header("Content-Disposition: attachment; filename=raport_okresowy_" . $data_poczatkowa_kalendarz . "_" . $data_koncowa_kalendarz . ".csv");
        print $ciag_do_zapisu;
        exit;            
              
    } else {

        Funkcje::PrzekierowanieURL('raport_okresowy.php');

    }

    $db->close_query($sql);
    
}    
?>