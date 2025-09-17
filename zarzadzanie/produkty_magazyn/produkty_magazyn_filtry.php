<?php
$warunki_szukania = ' and p.products_id > 0';
$nr_id_tmp = array();

// jezeli jest szukanie
if (isset($_GET['szukaj']) && !empty($_GET['szukaj'])) {
    //
    $szukana_wartosc = $filtr->process($_GET['szukaj']);
    $warunki_szukania .= " and (pd.products_name like '%".$szukana_wartosc."%'";
    //
    if ( isset($_GET['szukaj_opis']) ) {
         $warunki_szukania .= " or pd.products_description like '%".$szukana_wartosc."%'";
    }
    if ( isset($_GET['szukaj_nrkat']) ) {
         $warunki_szukania .= " or p.products_model like '%".$szukana_wartosc."%'";
         //
         // szuka tez czy nie ma nr katalogowego w cechach
         $sql_cechy = $db->open_query("SELECT products_id FROM products_stock WHERE (products_stock_model like '%".$szukana_wartosc."%')");
         while ($info_cechy = $sql_cechy->fetch_assoc()) {
             $nr_id_tmp[ $info_cechy['products_id'] ] = $info_cechy['products_id'];
         }
         $db->close_query($sql_cechy);          
    }        
    if ( isset($_GET['szukaj_kodprod']) ) {
         $warunki_szukania .= " or p.products_man_code like '%".$szukana_wartosc."%'";
    }                
    //
    $warunki_szukania .= ")";
    unset($szukana_wartosc);
}   

// jezeli jest nr kat lub id
if (isset($_GET['id_produktu']) && (int)$_GET['id_produktu'] > 0) {
    //
    $warunki_szukania .= " and p.products_id = " . (int)$_GET['id_produktu'];
    //
}

// jezeli jest kod ean
if (isset($_GET['ean']) && !empty($_GET['ean'])) { 
    $szukana_wartosc = $filtr->process($_GET['ean']);
    $warunki_szukania .= " and (p.products_ean like '%".$szukana_wartosc."%')";
    //
    // szuka tez czy nie ma kodu ean w cechach
    $sql_cechy = $db->open_query("SELECT products_id FROM products_stock WHERE (products_stock_ean like '%".$szukana_wartosc."%')");
    while ($info_cechy = $sql_cechy->fetch_assoc()) {
        $nr_id_tmp[ $info_cechy['products_id'] ] = $info_cechy['products_id'];
    }
    $db->close_query($sql_cechy);
    unset($szukana_wartosc);
    //
}  

// jezeli jest wybrany status
if (isset($_GET['status']) && !empty($_GET['status'])) {
    if ( $_GET['status'] != 'tak_listing' && $_GET['status'] != 'tak_nie_listing' ) {
         $warunki_szukania .= " and p.products_status = '".(($_GET['status'] == 'tak') ? '1' : '0')."'";
    } else if ( $_GET['status'] == 'tak_listing' ) { 
         $warunki_szukania .= " and p.products_status = '1' and p.listing_status = '0'";
    } else if ( $_GET['status'] == 'tak_nie_listing' ) { 
         $warunki_szukania .= " and p.products_status = '1' and p.listing_status = '1'";
    }
}     

// ilosc magazynu
if (isset($_GET['ilosc_od'])) {
    $ilosc = (float)$_GET['ilosc_od'];
    $warunki_szukania .= " and p.products_quantity >= '".$ilosc."'";
    unset($ilosc);
}
if (isset($_GET['ilosc_do'])) {
    $ilosc = (float)$_GET['ilosc_do'];
    $warunki_szukania .= " and p.products_quantity <= '".$ilosc."'";
    unset($ilosc);
}

// data dodania
if ( isset($_GET['szukaj_data_dodania_od']) && $_GET['szukaj_data_dodania_od'] != '' ) {
    $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_dodania_od'] . ' 00:00:00')));
    $warunki_szukania .= " and p.products_date_added >= '".$szukana_wartosc."'";
}
if ( isset($_GET['szukaj_data_dodania_do']) && $_GET['szukaj_data_dodania_do'] != '' ) {
    $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_dodania_do'] . ' 23:59:59')));
    $warunki_szukania .= " and p.products_date_added <= '".$szukana_wartosc."'";
}

// jezeli jest wybrana dostepnosc
if (isset($_GET['dostep']) && (int)$_GET['dostep'] > 0) {
    $id_dostepnosci = (int)$_GET['dostep'];
    $warunki_szukania .= " and p.products_availability_id = '".$id_dostepnosci."'";
    unset($id_dostepnosci);
}    

// jezeli jest wybrana wysylka
if (isset($_GET['wysylka']) && (int)$_GET['wysylka'] > 0) {
    $id_wysylka = (int)$_GET['wysylka'];
    $warunki_szukania .= " and p.products_shipping_time_id = '".$id_wysylka."'";
    unset($id_wysylka);
}     

// jezeli jest zakres cen
if (isset($_GET['cena_od']) && (float)$_GET['cena_od'] > 0) {
    $cena = (float)$_GET['cena_od'];
    $warunki_szukania .= " and p.products_price_tax >= '".$cena."'";
    unset($cena);
}
if (isset($_GET['cena_do']) && (float)$_GET['cena_do'] > 0) {
    $cena = (float)$_GET['cena_do'];
    $warunki_szukania .= " and p.products_price_tax <= '".$cena."'";
    unset($cena);
}    

// jezeli jest wybrana kategoria
if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
    $id_kategorii = (int)$_GET['kategoria_id'];
    $warunki_szukania .= " and pc.categories_id = '".$id_kategorii."'";
    unset($id_kategorii);
}

// jezeli jest wybrany producent
if (isset($_GET['producent']) && (int)$_GET['producent'] > 0) {
    $id_producenta = (int)$_GET['producent'];
    $warunki_szukania .= " and p.manufacturers_id = '".$id_producenta."'";
    unset($id_producenta);
} 

// jezeli sa dodatkowe opcje
if (isset($_GET['dodatkowe_opcje']) && !empty($_GET['dodatkowe_opcje'])) {
    switch ($_GET['dodatkowe_opcje']) {
        case "bez_magazynu":
            $warunki_szukania .= " and p.products_control_storage = 0";
            break;                                                    
    }     
}

$warunki_cech = '';
if ( isset($_GET['cecha_nazwa']) ) {
     $warunki_cech .= ' and pa.options_id = "' . (int)$_GET['cecha_nazwa'] . '"';
}
if ( isset($_GET['cecha_wartosc']) ) {
     $warunki_cech .= ' and pa.options_values_id = "' . (int)$_GET['cecha_wartosc'] . '"';
}

// jezeli jest wybrany stan alarmowy
if (isset($_GET['ilosc_alarm']) && (int)$_GET['ilosc_alarm'] > 0) {
    if ( (int)$_GET['ilosc_alarm'] == 1 ) {
         $warunki_szukania .= " and IF(p.products_quantity_alarm > 0, p.products_quantity <= p.products_quantity_alarm, p.products_quantity <= " . MAGAZYN_STAN_MINIMALNY . ")";
    }
    if ( (int)$_GET['ilosc_alarm'] == 2 ) {
         $warunki_szukania .= " and IF(p.products_quantity_alarm > 0, p.products_quantity > p.products_quantity_alarm, p.products_quantity > " . MAGAZYN_STAN_MINIMALNY . ")";
    } 
    if ( (int)$_GET['ilosc_alarm'] == 3 ) {
         $warunki_szukania .= " and p.products_quantity < p.products_quantity_max_alarm";
    }     
}

if ( count($nr_id_tmp) > 0 ) {
     $warunki_szukania .= " or p.products_id IN (" . implode(',', (array)$nr_id_tmp) . ")";
}

unset($nr_id_tmp);

if ( $warunki_szukania != '' ) {
  $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
}
 ?>