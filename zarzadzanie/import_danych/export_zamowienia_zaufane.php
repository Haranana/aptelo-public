<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (isset($_POST['plik']) && !empty($_POST['plik']) && isset($_POST['limit']) && (int)$_POST['limit'] > -1 && Sesje::TokenSpr()) {

    $NaglowekCsv = '';
    $CoDoZapisania = '';
    $Separator = ';';
    $Naglowek = true;

    // uchwyt pliku, otwarcie do dopisania
    $fp = fopen($filtr->process($_POST['plik']), "a");
    // blokada pliku do zapisu
    flock($fp, 2);

    $warunki_szukania = '';

    if ( isset($_POST['status']) && $_POST['status'] != '0' ) {
        $szukana_wartosc = $filtr->process($_POST['status']);
        $warunki_szukania .= " and o.orders_status IN (".$szukana_wartosc.") and osh.orders_status_id IN (".$szukana_wartosc.")";
    } elseif ( $_POST['status'] == '0' ) {
        $warunki_szukania .= " ";
    }

    if ( $_POST['start'] != '0' && $_POST['koniec'] == '0') {
        $szukana_wartosc = date('Y-m-d H:i:s', $filtr->process($_POST['start']));
        $warunki_szukania .= " and osh.date_added >= '".$szukana_wartosc."'";
    }

    if ( $_POST['start'] == '0' && $_POST['koniec'] != '0') {
        $szukana_wartosc = date('Y-m-d H:i:s', $filtr->process($_POST['koniec']) + 86399);
        $warunki_szukania .= " and osh.date_added <= '".$szukana_wartosc."'";
    }

    if ( $_POST['start'] != '0' && $_POST['koniec'] != '0') {
        $szukana_wartosc_start = date('Y-m-d H:i:s', $filtr->process($_POST['start']));
        $szukana_wartosc_koniec = date('Y-m-d H:i:s', $filtr->process($_POST['koniec']) + 86399);
        $warunki_szukania .= " and osh.date_added >= '".$szukana_wartosc_start."' and osh.date_added <= '".$szukana_wartosc_koniec."'";
    }


    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }
    
    $ZapytanieZamowienia = "SELECT * FROM orders o
                    LEFT JOIN orders_status_history osh ON osh.orders_id = o.orders_id
                    ".$warunki_szukania." GROUP BY o.orders_id LIMIT ".(int)$_POST['limit'].",1";

    $sqlZamowienia = $db->open_query($ZapytanieZamowienia);
    $infc = $sqlZamowienia->fetch_assoc();      
    
    $zamowienie = new Zamowienie($infc['orders_id']);

    $db->close_query($sqlZamowienia);
    //unset($ZapytanieZamowienia);

    $klient = explode(' ', (string)$zamowienie->klient['nazwa']);

    $eksport = array( 'DATE'                 => FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia']),
                      'ORDER_ID'             => $infc['orders_id'],
                      'EMAIL'                => $zamowienie->klient['adres_email'],
                      'FIRST_NAME'           => $klient['0'],
                      'LAST_NAME'             => $klient['1']
    );  

    $wynik = array();

    foreach ( $zamowienie->produkty as $Klucz => $produkt ) {

        $CoDoZapisania = '';

        $produkt = array( 'PRODUCT_ID'      => $produkt['products_id'],
                          'PRODUCT_NAME'    => $produkt['nazwa'] );

        $wynik = array_merge($eksport, $produkt);

        if ((int)$_POST['limit'] == 0 && $Naglowek) {
        
            // najpierw doda naglowki
            foreach ( $wynik as $Key => $pola ) {
              $NaglowekCsv .= $Key . $Separator;
            }
            $CoDoZapisania = $NaglowekCsv . "\n";
            $Naglowek = false;

        }    

        $WartosciPol = '';
        foreach ( $wynik as $Key => $pola ) {
            $WartosciPol .= '"' . Funkcje::CzyszczenieTekstu($pola) . '"' . $Separator;
        }    

        $CoDoZapisania = $CoDoZapisania . $WartosciPol . "\n";
        fwrite($fp, $CoDoZapisania);
        // zapisanie danych do pliku
        flock($fp, 3);
        
        unset($CoDoZapisania, $WartosciPol, $NaglowekCsv, $wynik, $produkt);
    }


    // zamkniecie pliku
    fclose($fp);        

    unset($klient, $zamowienie, $eksport);

}
?>