<?php
$DostepDoPobrania = false;

if ( !isset($_SESSION['pobranieZamowienia']) ) {
    chdir('../'); 

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    // zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
    $prot = new Dostep($db);
    
    $DostepDoPobrania = $prot->wyswietlStrone;
    
} else if ($_SESSION['pobranieZamowienia'] == 'tak') {

    $DostepDoPobrania = true;

}

if ($DostepDoPobrania) {

    $TablicaZamowien = array();

    if ( isset($_GET['id_poz']) && !isset($_GET['id']) ) {
    
        if ( (int)$_GET['id_poz'] == 0 ) {
             Funkcje::PrzekierowanieURL('zamowienia.php');
        }

        if ( !isset($_SESSION['pobranieZamowienia']) ) {
            $TablicaZamowien[] = new Zamowienie((int)$_GET['id_poz']);
          } else {
            $TablicaZamowien[] = new Zamowienie($id_dodanej_pozycji_zamowienia);
        }
        
    }
    
    if ( isset($_GET['id']) && !isset($_GET['id_poz']) ) {
    
        $_GET['id'] = base64_decode((string)$_GET['id']);
        
        $TablicaZamowienId = explode(',', ((isset($_GET['id']) ? (string)$_GET['id'] : '')));
        
        if ( count($TablicaZamowienId) == 1 ) {
             Funkcje::PrzekierowanieURL('zamowienia.php');
        }
        
        foreach ( $TablicaZamowienId as $Pozycja ) {
             //
             $TablicaZamowien[] = new Zamowienie((int)$Pozycja);
             //
        }

    }    
    
    $CoDoZapisania = '';
    $Separator = ';';
    $Naglowek = false;
    
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
        
    foreach ( $TablicaZamowien as $zamowienie ) {
    
        $Eksport = array();
    
        // podsumowanie zamowienia
            
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

        foreach ( $zamowienie->produkty as $Klucz => $Produkt ) {

            $Eksport[] = array( 'Nr_zamowienia'        => ((!isset($_SESSION['pobranieZamowienia'])) ? $zamowienie->info['id_zamowienia'] : $id_dodanej_pozycji_zamowienia),
                                'Opiekun_zamowienia'   => ((isset($TablicaOpiekunow[$zamowienie->info['opiekun']])) ? $TablicaOpiekunow[$zamowienie->info['opiekun']] : '-'),
                                'Data_zamowienia'      => date('m/d/Y', FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia'])),
                                'Id_produktu'          => $Produkt['id_produktu'],
                                'Id_produktu_zew'      => $Produkt['id_produktu_magazyn'],
                                'Ilosc_produktow'      => $Produkt['ilosc'],
                                'Nazwa_produktu'       => $Produkt['nazwa'],
                                'Nr_katalogowy'        => $Produkt['model'],
                                'Kod_EAN'              => $Produkt['ean'],
                                'Cena_netto'           => $Produkt['cena_koncowa_netto'],
                                'Cena_brutto'          => $Produkt['cena_koncowa_brutto'],
                                'Podatek_Vat'          => $Produkt['tax'],
                                'Suma_netto'           => $Produkt['cena_koncowa_netto'] * $Produkt['ilosc'],
                                'Suma_brutto'          => $Produkt['cena_koncowa_brutto'] * $Produkt['ilosc'],
                                'Waga'                 => $Produkt['weight'],
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
                                
        }
        
        unset($Komentarz, $SumaZamowienia, $KuponRabatowy, $KuponRabatowyOpis, $Punkty, $ZnizkaKlientow, $KosztWysylki, $DoZaplaty);

        foreach ( $Eksport as $Klucz => $Tablica ) {

            if ( $Klucz == 0 && $Naglowek == false ) {
            
                // najpierw doda naglowki
                foreach ( $Tablica as $Key => $pola ) {
                  $CoDoZapisania .= $Key . $Separator;
                }
                
                $CoDoZapisania = substr((string)$CoDoZapisania, 0, -1);
                
                $CoDoZapisania .= "\n";
                
                // lista produktow
                foreach ( $Tablica as $Key => $pola ) {
                  $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($pola) . '"' . $Separator;
                }
                
                $CoDoZapisania = substr((string)$CoDoZapisania, 0, -1);
                
                $CoDoZapisania .= "\n";        
                
                $Naglowek = true;
                
            } else {
            
                // lista produktow
                foreach ( $Tablica as $Key => $pola ) {
                  $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($pola) . '"' . $Separator;
                }
                
                $CoDoZapisania = substr((string)$CoDoZapisania, 0, -1);
                
                $CoDoZapisania .= "\n";    
            
            }

        }
        
        unset($Eksport);
          
    }
    
    unset($TablicaZamowien, $Separator, $Naglowek);
    
    if ( !isset($_SESSION['pobranieZamowienia']) ) {

        header("Content-Type: application/force-download; charset=utf-8\n");
        header("Cache-Control: cache, must-revalidate");   
        header("Pragma: public");
        if ( isset($_GET['id_poz']) ) {
             header("Content-Disposition: attachment; filename=zamowienie_nr_" . (int)$_GET['id_poz'] . ".csv");
           } else {
             header("Content-Disposition: attachment; filename=zamowienia.csv");
        }
        print $CoDoZapisania;
        exit;

      } else {
      
        echo $CoDoZapisania;
        unset($CoDoZapisania);
        
    }

}
?>

