<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (isset($_POST['plik']) && !empty($_POST['plik']) && isset($_POST['limit']) && (int)$_POST['limit'] > -1 && Sesje::TokenSpr()) {

    $NaglowekCsv = '';
    $CoDoZapisania = '';
    $Separator = ';';

    // uchwyt pliku, otwarcie do dopisania
    $fp = fopen($filtr->process($_POST['plik']), "a");
    // blokada pliku do zapisu
    flock($fp, 2);

    $warunki_szukania = '';
    $warunki_szukania_ilosc = '';
    
    if ( isset($_POST['status']) && $_POST['status'] != '0' ) {
        $szukana_wartosc = $filtr->process($_POST['status']);
        $warunki_szukania .= " and orders_status = '".$szukana_wartosc."'";
        $warunki_szukania_ilosc .= " and o.orders_status = '".$szukana_wartosc."'";
    }

    if ( $_POST['start'] != '0' && $_POST['koniec'] == '0') {
        $szukana_wartosc = $filtr->process($_POST['start']);
        $warunki_szukania .= " and orders_id >= '".$szukana_wartosc."'";
        $warunki_szukania_ilosc .= " and o.orders_id >= '".$szukana_wartosc."'";
    }

    if ( $_POST['start'] == '0' && $_POST['koniec'] != '0') {
        $szukana_wartosc = $filtr->process($_POST['koniec']);
        $warunki_szukania .= " and orders_id <= '".$szukana_wartosc."'";
        $warunki_szukania_ilosc .= " and o.orders_id <= '".$szukana_wartosc."'";
    }

    if ( $_POST['start'] != '0' && $_POST['koniec'] != '0') {
        $warunki_szukania .= " and orders_id >= '".$filtr->process($_POST['start'])."' and orders_id <= '".$filtr->process($_POST['koniec'])."'";
        $warunki_szukania_ilosc .= " and o.orders_id >= '".$filtr->process($_POST['start'])."' and o.orders_id <= '".$filtr->process($_POST['koniec'])."'";
    }


    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }
    
    // ile jest najwiecej produktow w zamowieniu - do naglowkow
    
    $IloscProduktow = 1;
    
    if ((int)$_POST['limit'] == 0 && isset($_POST['produkty']) && $_POST['produkty'] == '1') {
    
        $IloscProduktowZamowienia = "SELECT o.orders_id, count(op.orders_products_id) as ilosc FROM orders o, orders_products op WHERE o.orders_id = op.orders_id ".$warunki_szukania_ilosc . " GROUP BY o.orders_id ORDER by ilosc desc LIMIT 0,1";
        $sqlZamowieniaIlosc = $db->open_query($IloscProduktowZamowienia);
        $infoProdukty = $sqlZamowieniaIlosc->fetch_assoc(); 
        //
        $IloscProduktow = $infoProdukty['ilosc'];
        //
        $db->close_query($sqlZamowieniaIlosc);
        unset($IloscProduktowZamowien);
    
    }
    
    $ZapytanieZamowienia = "SELECT orders_id FROM orders ".$warunki_szukania." LIMIT ".(int)$_POST['limit'].",1";
    
    $sqlZamowienia = $db->open_query($ZapytanieZamowienia);
    $infc = $sqlZamowienia->fetch_assoc();      
    
    $zamowienie = new Zamowienie($infc['orders_id']);
    
    $db->close_query($sqlZamowienia);
    unset($ZapytanieZamowienia);    
    
    $SumaZamowienia = '';
    $KuponRabatowy = '';
    $KuponRabatowyOpis = '';
    $Punkty = '';
    $ZnizkaKlientow = '';
    $KosztWysylki = '';
    $DoZaplaty = '';
    
    foreach ( $zamowienie->podsumowanie as $suma ) {
    
        switch ($suma['klasa']) {
            case 'ot_subtotal':
                $SumaZamowienia = round($suma['wartosc'],2);
                break;
            case 'ot_discount_coupon':
                $KuponRabatowy = round($suma['wartosc'],2);
                if (($pos = strpos((string)$suma['tytul'], ":")) !== FALSE) {
                    $KuponRabatowyOpis = substr((string)$suma['tytul'], strpos((string)$suma['tytul'], ":") + 1);
                }
                break;
            case 'ot_redemptions':
                $Punkty = round($suma['wartosc'],2);
                break;
            case 'ot_loyalty_discount':
                $ZnizkaKlientow = round($suma['wartosc'],2);
                break;
            case 'ot_shipping':
                $KosztWysylki = round($suma['wartosc'],2);
                break;
            case 'ot_total':
                $DoZaplaty = round($suma['wartosc'],2);
                break;                    
        } 
        
    }

    // komentarz do zamowienia

    $Komentarz = '';
    foreach ( $zamowienie->statusy as $koment ) {
        $Komentarz = $koment['komentarz'];
        break;
    }       
    
    // tablica opiekunow zamowien
    $zapytanie = "select * from admin order by admin_lastname, admin_firstname";
    $sql = $db->open_query($zapytanie);
    //
    $TablicaOpiekunow = array();
    while ($info = $sql->fetch_assoc()) { 
        $TablicaOpiekunow[$info['admin_id']] = $info['admin_firstname'] . ' ' . $info['admin_lastname'];
    }
    $db->close_query($sql); 
    unset($zapytanie, $info);    
    //        
    
    $eksport = array( 'Nr_zamowienia'        => $infc['orders_id'],
                      'Data_zamowienia'      => date('m/d/Y', FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia'])),
                      'Opiekun_zamowienia'   => ((isset($TablicaOpiekunow[$zamowienie->info['opiekun']])) ? $TablicaOpiekunow[$zamowienie->info['opiekun']] : '-'),
                      'Status_zamowienia'    => strip_tags(Sprzedaz::pokazNazweStatusuZamowienia($zamowienie->info['status_zamowienia'])),
                      'Dokument_sprzedazy'   => $zamowienie->info['dokument_zakupu_nazwa'],
                      'Id_klienta_zew'       => $zamowienie->klient['id_klienta_magazyn'],
                      'Id_klienta'           => $zamowienie->klient['id'],
                      'Nazwa_klienta'        => $zamowienie->klient['nazwa'],
                      'Ulica'                => $zamowienie->klient['ulica'],
                      'Kod_pocztowy'         => $zamowienie->klient['kod_pocztowy'],
                      'Miasto'               => $zamowienie->klient['miasto'],      
                      'Wojewodztwo'          => $zamowienie->klient['wojewodztwo'],  
                      'Kraj'                 => $zamowienie->klient['kraj'],
                      'Firma'                => $zamowienie->klient['firma'],
                      'Nip'                  => $zamowienie->klient['nip'],
                      'Telefon'              => $zamowienie->klient['telefon'],
                      'Adres_email'          => $zamowienie->klient['adres_email'],
                      'Forma_dostawy'        => $zamowienie->info['wysylka_modul'],
                      'Punkt_odbioru'        => $zamowienie->info['wysylka_punkt_odbioru'],
                      'Forma_platnosci'      => $zamowienie->info['metoda_platnosci'],
                      'Dostawa_nazwa'        => $zamowienie->dostawa['nazwa'],
                      'Dostawa_firma'        => $zamowienie->dostawa['firma'],
                      'Dostawa_ulica'        => $zamowienie->dostawa['ulica'],
                      'Dostawa_miasto'       => $zamowienie->dostawa['miasto'],
                      'Dostawa_kod_pocztowy' => $zamowienie->dostawa['kod_pocztowy'],
                      'Dostawa_wojewodztwo'  => $zamowienie->dostawa['wojewodztwo'],
                      'Dostawa_kraj'         => $zamowienie->dostawa['kraj'],
                      'Faktura_nazwa'        => $zamowienie->platnik['nazwa'],
                      'Faktura_firma'        => $zamowienie->platnik['firma'],
                      'Faktura_nip'          => $zamowienie->platnik['nip'],
                      'Faktura_pesel'        => $zamowienie->platnik['pesel'],
                      'Faktura_ulica'        => $zamowienie->platnik['ulica'],
                      'Faktura_miasto'       => $zamowienie->platnik['miasto'],
                      'Faktura_kod_pocztowy' => $zamowienie->platnik['kod_pocztowy'],
                      'Faktura_wojewodztwo'  => $zamowienie->platnik['wojewodztwo'],
                      'Faktura_kraj'         => $zamowienie->platnik['kraj'],                            
                      'Komentarz'            => $Komentarz,
                      'Suma_zamowienia'      => $SumaZamowienia,
                      'Kupon_rabatowy_kod'   => $KuponRabatowyOpis,
                      'Kupon_rabatowy'       => $KuponRabatowy,
                      'Znizka_za_punkty'     => $Punkty,
                      'Znizka_klientow'      => $ZnizkaKlientow,
                      'Koszt_przesylki'      => $KosztWysylki,
                      'Do_zaplaty'           => $DoZaplaty );  
                        
    unset($Komentarz, $SumaZamowienia, $KuponRabatowy, $Punkty, $ZnizkaKlientow, $KosztWysylki, $DoZaplaty); 

    $wynik = $eksport;    
                        
    if ( isset($_POST['produkty']) && $_POST['produkty'] == '1' ) { 
    
        $produkty = array();

        foreach ( $zamowienie->produkty as $Klucz => $produkt ) {
      
            $produkty[] = array( 'START_PRODUKT'   => 'START_PRODUKT',
                                 'Id_produktu'     => $produkt['products_id'],
                                 'Id_produktu_zew' => $produkt['id_produktu_magazyn'],
                                 'Ilosc_produktow' => $produkt['ilosc'],
                                 'Nazwa_produktu'  => $produkt['nazwa'],
                                 'Nr_katalogowy'   => $produkt['model'],
                                 'Kod_producenta'  => $produkt['kod_producenta'],
                                 'Kod_EAN'         => $produkt['ean'],
                                 'Kod_PKWIU'       => $produkt['pkwiu'],
                                 'Cena_netto'      => $produkt['cena_koncowa_netto'],
                                 'Cena_brutto'     => $produkt['cena_koncowa_brutto'],
                                 'Podatek_Vat'     => $produkt['tax'],
                                 'Suma_netto'      => $produkt['cena_koncowa_netto'] * $produkt['ilosc'],
                                 'Suma_brutto'     => $produkt['cena_koncowa_brutto'] * $produkt['ilosc'],
                                 'Waga'            => $produkt['weight'] );
          
        }
        
        if ( count($produkty) > 0 ) {

            for ( $v = 1; $v <= $IloscProduktow; $v++ ) {
                  //
                  // dodaje nr kolejny produktu
                  $prodTmp = array();
                  foreach ( $produkty[0] as $keytmp => $warttmp ) {
                      //
                      $prodTmp[$keytmp . '_' . $v] = $warttmp;
                      //
                  }
                  //
                  $wynik = array_merge($wynik, $prodTmp);
                  //
                  unset($prodTmp);
            }
            
        }
        
    }

    if ((int)$_POST['limit'] == 0) {
    
        // najpierw doda naglowki
        foreach ( $wynik as $Key => $pola ) {
          $NaglowekCsv .= $Key . $Separator;
        }

        $CoDoZapisania = $NaglowekCsv . "\n";
        
    }    

    $WartosciPol = '';

    foreach ( $eksport as $Key => $pola ) {
      $WartosciPol .= '"' . Funkcje::CzyszczenieTekstu($pola) . '"' . $Separator;
    }    

    if ( isset($_POST['produkty']) && $_POST['produkty'] == '1' ) { 
    
        foreach ( $produkty as $produkt) {
        
            foreach ( $produkt as $Key => $pola ) {
              $WartosciPol .= '"' . Funkcje::CzyszczenieTekstu($pola) . '"' . $Separator;
            }

        }
        
    }
    
    $CoDoZapisania = $CoDoZapisania . $WartosciPol . "\n";
    
    unset($IloscProduktow, $WartoscPol, $NaglowekCsv);

    fwrite($fp, $CoDoZapisania);
    
    // zapisanie danych do pliku
    flock($fp, 3);
    // zamkniecie pliku
    fclose($fp);        
        
}
?>