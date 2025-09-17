<?php
$warunki_szukania = '';

// jezeli jest szukanie
if (isset($_GET['szukaj'])) {
    $szukana_wartosc = $filtr->process($_GET['szukaj']);
    $warunki_szukania = " and (s.subscribers_email_address like '%".$szukana_wartosc."%')";
}

// jezeli jest opcja
if (isset($_GET['opcja']) && !empty($_GET['opcja'])) {
    switch ($filtr->process($_GET['opcja'])) {
        case "1":
            $warunki_szukania .= " and s.customers_id > 0";
            break;
        case "2":
            $warunki_szukania .= " and s.customers_id = 0";
            break;               
    }     
}     

// data zapisania
if ( isset($_GET['szukaj_data_zapisania_od']) && $_GET['szukaj_data_zapisania_od'] != '' ) {
    $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_zapisania_od'] . ' 00:00:00')));
    $warunki_szukania .= " and s.date_added >= '".$szukana_wartosc."'";
}

if ( isset($_GET['szukaj_data_zapisania_do']) && $_GET['szukaj_data_zapisania_do'] != '' ) {
    $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_zapisania_do'] . ' 23:59:59')));
    $warunki_szukania .= " and s.date_added <= '".$szukana_wartosc."'";
}     

// jezeli jest wybrany status
if (isset($_GET['status']) && !empty($_GET['status'])) {
    if ( $_GET['status'] == 'zapisany' ) {
         $warunki_szukania .= " and s.customers_newsletter = '1'";
    }
    if ( $_GET['status'] == 'nie zapisany' ) {
         $warunki_szukania .= " and s.customers_newsletter = '0'";
    }
} 

// jezeli jest grupa klienta lub newslettera
if (( isset($_GET['grupa']) && (int)$_GET['grupa'] > 0 ) || ( isset($_GET['grupa_newslettera']) && (string)$_GET['grupa_newslettera'] != '' )) {
    $warunki_szukania .= " and c.customers_id = s.customers_id";
}

// jezeli jest wybrana grupa
if ( isset($_GET['grupa']) && (int)$_GET['grupa'] > 0 ) {
    $warunki_szukania .= " and c.customers_groups_id > 0 and c.customers_groups_id = " . (int)$_GET['grupa'];
}     

// jezeli jest wybrana grupa newslettera
if ( isset($_GET['grupa_newslettera']) && (string)$_GET['grupa_newslettera'] != '' ) {
     foreach ( explode(',', (string)$_GET['grupa_newslettera']) as $tmp) {
        $warunki_szukania .= " and c.customers_newsletter_group LIKE '%," . (int)$tmp . ",%'";
     }
}     

if ( $warunki_szukania != '' ) {
  $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
}    
?>