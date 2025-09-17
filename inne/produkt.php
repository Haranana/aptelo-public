<?php
chdir('../'); 

if (isset($_POST['id']) && isset($_POST['cechy'])) {

    $PodzielId = explode('_', (string)$_POST['id']);

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (!Sesje::TokenSpr() && (int)$PodzielId[1] > 0) {
        echo 'false';
        exit;
    }
    
    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KOSZYK') ), $GLOBALS['tlumacz'] );

    //
    $Produkt = new Produkt( (int)$PodzielId[1] );    
    $Produkt->ProduktDostepnosc();
    
    if ( !empty($_POST['cechy']) ) {
        $Produkt->ProduktKupowanie( $filtr->process($_POST['cechy']) ); 
      } else {
        $Produkt->ProduktKupowanie();
    }    

    // okresla czy ilosc jest ulamkowa zeby pozniej odpowiednio sformatowac wynik
    $Przecinek = 2;
    // jezeli sa wartosci calkowite to dla pewnosci zrobi int
    if ( $Produkt->info['jednostka_miary_typ'] == '1' ) {
        $Przecinek = 0;
    }    
    
    //
    // dostepnosc domyslna produktu
    $DostepnoscProduktu = '';
    
    if ( !empty($Produkt->dostepnosc['dostepnosc'])) {
         //
         $DostepnoscProduktu = $Produkt->dostepnosc['dostepnosc'];
         // 
    }      
    //
    
    // ogolny nr katalogowy produktu
    $NrKatalogowy = '';
    
    if ( $Produkt->info['nr_katalogowy'] != '' ) {
         //
         $NrKatalogowy = $Produkt->info['nr_katalogowy'];
         //
    }

    // ilosc magazynowa produktu
    if ( KARTA_PRODUKTU_MAGAZYN_FORMA == 'liczba' ) {
         $Ilosc = number_format( $Produkt->zakupy['ilosc_magazyn'], $Przecinek, '.', '' ) . ' ' . $Produkt->info['jednostka_miary'];   
       } else {
         $Ilosc = Produkty::PokazPasekMagazynu($Produkt->zakupy['ilosc_magazyn']);
    }
     
    $IloscLiczba = (float)$Produkt->zakupy['ilosc_magazyn'];
    
    // dostepnosc cechy
    if ( !empty($Produkt->zakupy['nazwa_dostepnosci']) ) {
         //
         $DostepnoscProduktu = $Produkt->zakupy['nazwa_dostepnosci'];
         //
    }
    
    // czas wysylki cechy
    $CzasWysylkiProduktu = '';
    //
    if ( !empty($Produkt->zakupy['nazwa_czasu_wysylki']) ) {
         //
         $CzasWysylkiProduktu = $Produkt->zakupy['nazwa_czasu_wysylki'];
         //
    }    
    
    // nr katalogowy cechy
    if ( !empty($Produkt->zakupy['nr_kat_cechy']) ) {
         //
         $NrKatalogowy = $Produkt->zakupy['nr_kat_cechy'];
         //
    }
    
    // ean cechy
    $KodEan = '';
    //
    if ( !empty($Produkt->zakupy['nr_ean_cechy']) ) {
         //
         $KodEan = $Produkt->zakupy['nr_ean_cechy'];
         //
    }    
    
    // waga cechy
    $Waga = (float)$Produkt->info['waga'];
    
    // komunikat cena 30 dni
    $Cena30dni = '';

    // sformatuj cechy
    if ( !empty($_POST['cechy']) ) {
        
         // komunikat cena 30 dni
         $Cena30dni = $Produkt->zakupy['info_cena_30_dni'];
         
         //
         // generuje tablice globalne z nazwami cech
         Funkcje::TabliceCech();  
         //
         $CechyProduktu = explode('x', (string)$filtr->process($_POST['cechy']));
         //
         foreach ( $CechyProduktu as $Tmp ) {
              //
              $Podzial = explode('-', (string)$Tmp);
              //
              if ( count($Podzial) == 2 ) {
                   //
                   // czy jest waga dla cechy danego produktu
                   $zapytanie = "SELECT DISTINCT options_values_weight
                                            FROM products_attributes
                                           WHERE products_id = '" . $Produkt->info['id'] . "' AND 
                                                 options_id = '" . $Podzial[0] . "' AND 
                                                 options_values_id = '" . $Podzial[1] . "'";

                   $sql = $GLOBALS['db']->open_query($zapytanie);
                   
                   if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
                   
                       $cecha = $sql->fetch_assoc();                     
                       
                       // wartosci domyslne cech - waga cechy
                       if ( $cecha['options_values_weight'] > 0 ) {
                            //
                            $Waga += $cecha['options_values_weight'];
                            //
                       } else if ( $GLOBALS['WartosciCech'][$Podzial[1]]['waga'] > 0 ) {
                            //
                            $Waga += $GLOBALS['WartosciCech'][$Podzial[1]]['waga'];
                            //
                       } 
                       //
                       unset($cecha);
                       //
                   }
                   //
                   unset($zapytanie);
                   //
                   $GLOBALS['db']->close_query($sql);                   
                   //
              }
              //
         }
         //
    }
    
    if ( (float)$Waga > 0 ) {
         //
         $WagaKoncowa = number_format($Waga, 2, ',', '') . ' ' . $GLOBALS['tlumacz']['KOSZYK_WAGA_PRODUKTOW_JM'];
         //
    } else {
         //
         $WagaKoncowa = '';
         //
    }
    
    // czy wogole produkt mozna kupic
    if ( $Produkt->zakupy['mozliwe_kupowanie'] == 'nie' ) {
         $Kupowanie = 'nie';
        } else {
         $Kupowanie = 'tak';
    }
    
    // czy wylaczona kontrola magazynu
    if ( $Produkt->info['kontrola_magazynu'] == 1 ) {
         $KontrolaMagazynu = 'tak';
    } else {
         $KontrolaMagazynu = 'nie';
    }
    
    $WyswietlZegar = 'nie';
    
    // czas wysylki - czy cecha spelnia warunki
    if ( PRODUKT_ZEGAR_WYSYLKI == 'tak' && $Kupowanie == 'tak' ) {    
         //
         $WyswietlZegar = 'tak';
         //
         // sprawdzanie magazynu
         if ( (float)$Produkt->zakupy['ilosc_magazyn'] <= 0 && PRODUKT_ZEGAR_WYSYLKI_STAN_MAGAZYNOWY == 'tak' ) {
               $WyswietlZegar = 'nie';
         }
         
         // sprawdzanie dostepnosci
         if ( $Produkt->zakupy['id_dostep_cechy'] == '99999' ) {
              $id_dostepnosci = $Produkt->PokazIdDostepnosciAutomatycznych($Produkt->zakupy['ilosc_magazyn']);
         } else {
              $id_dostepnosci = $Produkt->zakupy['id_dostep_cechy'];
         }          
         //
         if ( !in_array((int)$id_dostepnosci, (array)explode(',', (string)PRODUKT_ZEGAR_WYSYLKI_DOSTEPNOSCI)) ) {   
              $WyswietlZegar = 'nie';
         }
         // sprawdzanie czasu wysylki
         if ( !in_array((int)$Produkt->zakupy['id_czasu_wys_cechy'], (array)explode(',', (string)PRODUKT_ZEGAR_CZAS_WYSYLKI)) ) {   
              $WyswietlZegar = 'nie';
         }
         //
    }

    echo json_encode( array("kontrolamagazyn" => $KontrolaMagazynu, "kupowanie" => $Kupowanie, "dostepnosc" => $DostepnoscProduktu, "czaswysylki" => $CzasWysylkiProduktu, 'nrkat' => $NrKatalogowy, 'ean' => $KodEan, 'ilosc' => $Ilosc, 'ilosc_liczba' => $IloscLiczba, 'waga' => $WagaKoncowa, 'cena30dni' => $Cena30dni, 'zegar_czas_wysylka' =>  $WyswietlZegar ) );

    unset($KontrolaMagazynu, $DostepnoscProduktu, $Kupowanie, $NrKatalogowy, $Produkt, $Ilosc, $WyswietlZegar);
    
}

?>