<?php
$zapytanie = "select ap.* from allegro_auctions ap left join products p ON p.products_id = ap.products_id where ap.auction_uuid = '' order by ap.synchronization desc, ap.products_date_end desc limit " . (int)$_POST['limit'] . "," . $WskaznikPrzeskoku;  

// jezeli sa warunki
if (isset($_POST['filtr']) && $_POST['filtr_rodzaj'] == 'producent') {
    $zapytanie = "select ap.* from allegro_auctions ap left join products p ON p.products_id = ap.products_id where ap.auction_uuid = '' and p.manufacturers_id = '" . (int)$_POST['filtr'] . "' order by ap.synchronization desc, ap.products_date_end desc limit " . (int)$_POST['limit'] . "," . $WskaznikPrzeskoku;  
}
if (isset($_POST['filtr']) && $_POST['filtr_rodzaj'] == 'kategoria') {
    $zapytanie = "select ap.* from allegro_auctions ap, products_to_categories pc where ap.auction_uuid = '' and ap.products_id = pc.products_id and pc.categories_id = '" . (int)$_POST['filtr'] . "' order by ap.synchronization desc, ap.products_date_end desc limit " . (int)$_POST['limit'] . "," . $WskaznikPrzeskoku;  
}   

$sql = $db->open_query($zapytanie);

if ((int)$db->ile_rekordow($sql) > 0) {
  
    $TablicaUzytkownikow = Array();
    $zapytanieUser = "SELECT * FROM allegro_users";
    $sqlUser = $db->open_query($zapytanieUser);
                      
    if ((int)$db->ile_rekordow($sqlUser) > 0) {
                      
      while ($infoUser = $sqlUser->fetch_assoc()) {

          $TablicaUzytkownikow[$infoUser['allegro_user_id']] = $infoUser['allegro_user_login'];

      }
      
    }
    
    $db->close_query($sqlUser);
    unset($zapytanieUser, $infoUser);

    // id waluty PLN
    $IdPLN = 1;
    //
    if ( isset($_SESSION['tablica_walut_kod']) ) {
        //
        $IdWalut = $_SESSION['tablica_walut_kod'];
        foreach ( $IdWalut as $WalutaSklepu ) {
            //
            if ( $WalutaSklepu['kod'] == 'PLN' ) {
                 $IdPLN = $WalutaSklepu['id'];
            }
            //
        }
        unset($IdWalut);  
        //
    }
    
    $CoDoZapisania = '';

    // uchwyt pliku, otwarcie do dopisania
    $fp = fopen($filtr->process($_POST['plik']), "a");
    // blokada pliku do zapisu
    flock($fp, 2);
    
    $Suma = $_POST['limit'];

    while ($info = $sql->fetch_assoc()) {
    
        $NaglowekCsv = '';
        
        $NaglowekCsv .= 'Id_aukcji;';
        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($info['auction_id']) . '";';         
        
        $NaglowekCsv .= 'Login;';
        $CoDoZapisania .= '"' . ((isset($TablicaUzytkownikow[$info['auction_seller']])) ? $TablicaUzytkownikow[$info['auction_seller']] : '') . '";';   
        
        $NaglowekCsv .= 'Nazwa_produktu_allegro;';
        
        $Cechy = array();

        if ( isset($info['products_stock_attributes']) && $info['products_stock_attributes'] != '' ) {

            $TablicaKombinacjiCech = explode('x', (string)$info['products_stock_attributes']);
            
            for ( $t = 0, $c = count($TablicaKombinacjiCech); $t < $c; $t++ ) {
            
              $TablicaWartoscCechy = explode('-', (string)$TablicaKombinacjiCech[$t]);

              $NazwaCechy = Funkcje::NazwaCechy( (int)$TablicaWartoscCechy['0'] );
              $NazwaWartosciCechy = Funkcje::WartoscCechy( (int)$TablicaWartoscCechy['1'] );

              $Cechy[] = $NazwaCechy . ': ' . $NazwaWartosciCechy;
              
              unset($TablicaWartoscCechy);
              
            }        
          
        }
        
        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($info['products_name'] . ((count($Cechy) > 0) ? ' - ' . implode(', ', (array)$Cechy) : '')) . '";';         
        
        $NaglowekCsv .= 'Data_rozpoczecia;';
        $CoDoZapisania .= '"' . date('d-m-Y H:i:s',FunkcjeWlasnePHP::my_strtotime($info['products_date_start'])) . '";';         
        
        $NaglowekCsv .= 'Data_zakonczenia;';
        
        if ( !empty($info['auction_date_end']) && FunkcjeWlasnePHP::my_strtotime($info['auction_date_end']) > 0 ) {
            //
            $CoDoZapisania .= '"' . date('d-m-Y H:i:s',FunkcjeWlasnePHP::my_strtotime($info['auction_date_end'])) . '";';         
            //
          } else {
            //
            $CoDoZapisania .= '"do wyczerpania";';         
            //
        }
        
        $NaglowekCsv .= 'Opcje_aukcji;';
        $Opcje = array();

        $TablicaOpcji = explode(',', (string)$info['allegro_options']);
        
        if ( count($TablicaOpcji) > 0 ) {
          
            foreach ( $TablicaOpcji as $Opcja ) {
              
                if ( $Opcja != '' ) {

                      switch ($Opcja) {
                          case 'emphasized10d':
                              $Opcje[] = 'W 10d';
                              break;
                          case 'emphasized1d':
                              $Opcje[] = 'W 1d';
                              break;                 
                          case 'promoPackage':
                              $Opcje[] = 'P';
                              break;
                          case 'departmentPage':
                              $Opcje[] = 'D';
                              break;
                      } 
                           
                }
                
            }

        }
        
        $CoDoZapisania .= '"' . implode(',', (array)$Opcje) . '";'; 
        
        unset($TablicaOpcji, $Opcje);        
        
        $NaglowekCsv .= 'Ilosc_na_aukcji;';
        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($info['auction_quantity']) . '";';  

        $NaglowekCsv .= 'Prowizja_allegro_kwota;';
        if ( $info['auction_cost'] > -1 ) {
             $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu(number_format($info['auction_cost'], 2, '.', '')) . '";';          
        } else {
             $CoDoZapisania .= '"";';          
        }
        
        $NaglowekCsv .= 'Prowizja_allegro_procent;';
        if ( $info['auction_cost'] > -1 ) {
             $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu(number_format((($info['auction_cost'] / $info['products_buy_now_price']) * 100), 2, '.', '')) . '";';          
        } else {
             $CoDoZapisania .= '"";';          
        }
        
        // ---------

        $zapytanieProdukt = "select p.products_id, 
                                    p.products_status,
                                    p.products_quantity,
                                    p.products_model,
                                    p.products_ean,
                                    p.products_man_code,
                                    p.options_type,
                                    p.products_price_tax,
                                    p.products_retail_price,
                                    p.products_purchase_price,
                                    p.products_currencies_id,
                                    p.products_pack_type,
                                    p.shipping_cost,
                                    pd.products_id, 
                                    pd.products_name,
                                    m.manufacturers_name,
                                    pai.products_price_allegro
                               from products p
                          left join products_description pd on p.products_id = pd.products_id and pd.language_id = '" . $_SESSION['domyslny_jezyk']['id'] . "'
                          left join manufacturers m ON p.manufacturers_id = m.manufacturers_id
                          left join products_allegro_info pai ON p.products_id = pai.products_id
                              where p.products_id = '" . $info['products_id'] . "'";
                                    
        $sqlProdukt = $db->open_query($zapytanieProdukt);
        $infc = $sqlProdukt->fetch_assoc();
        
        // ---------
                
        $NaglowekCsv .= 'Ilosc_magazyn;';
        
        if ( $info['products_stock_attributes'] != '' ) {
          
             $zapytanieStanCechy = "select products_stock_quantity, products_stock_model, products_stock_ean from products_stock where products_id = " . $info['products_id'] . " and products_stock_attributes = '" . str_replace('x', ',', (string)$info['products_stock_attributes']) . "'";
             $sqlStanCechy = $db->open_query($zapytanieStanCechy);
             $infr = $sqlStanCechy->fetch_assoc();
             
             if ( CECHY_MAGAZYN == 'tak' ) {
           
                  $CoDoZapisania .= '"' . (int)$infr['products_stock_quantity'] . '";';
                  
             } else {
               
                  $CoDoZapisania .= '"' . (int)$infc['products_quantity'] . '";';
                  
             }
             
             // nr katalogowy cechy
             if ( $infr['products_stock_model'] != '' ) {
                  $infc['products_model'] = $infr['products_stock_model'];
             }
             
             // kod ean cechy
             if ( $infr['products_stock_ean'] != '' ) {
                  $infc['products_ean'] = $infr['products_stock_ean'];
             }          
             
             $db->close_query($sqlStanCechy);
             unset($infr, $zapytanieStanCechy); 
             
        } else {
          
             $CoDoZapisania .= '"' . (int)$infc['products_quantity'] . '";';
             
        }        
        
        $NaglowekCsv .= 'Ilosc_sprzedana_na_aukcji;';
        $CoDoZapisania .= '"' . (int)$info['products_sold'] . '";';

        $NaglowekCsv .= 'Cena_aukcja;';
        
        if ( $info['products_buy_now_price'] > 0 ) {
             $CoDoZapisania .= '"' . $waluty->FormatujCeneBezSymbolu($info['products_buy_now_price'], false, '', '', 2, $IdPLN) . '";';
        } else {
             $CoDoZapisania .= '"0.00";';
        }
        
        $NaglowekCsv .= 'Cena_sklep;';
        
        // podstawowa cena produktu
        $CenaProduktu = $waluty->FormatujCeneBezSymbolu($infc['products_price_tax'], false, '', '', 2, $infc['products_currencies_id']);
        
        if ( $info['products_stock_attributes'] != '' ) {

            if ( $infc['options_type'] == 'ceny' ) {
                 //
                 // sprawdzi jaka jest cena cechy
                 //
                 $zapytanieCenaCechy = "select products_stock_price_tax from products_stock where products_id = " . $info['products_id'] . " and products_stock_attributes = '" . str_replace('x', ',', (string)$info['products_stock_attributes']) . "'";
                 $sqlCenaCechy = $db->open_query($zapytanieCenaCechy);
                 $infr = $sqlCenaCechy->fetch_assoc();
               
                 if ( $infr['products_stock_price_tax'] > 0 ) {
                      //
                      $CenaProduktu = $waluty->FormatujCeneBezSymbolu($infr['products_stock_price_tax'], false, '', '', 2, $infc['products_currencies_id']);
                      //
                 }
                 
                 $db->close_query($sqlCenaCechy);
                 unset($infr, $zapytanieCenaCechy);              
                 //
            } 
            
            if ( $infc['options_type'] == 'cechy' ) {
                 //
                 $CenaProduktu = $waluty->FormatujCeneBezSymbolu(Produkt::ProduktCenaCechy($info['products_id'], $infc['products_price_tax'], str_replace('x', ',', (string)$info['products_stock_attributes'])), false, '', '', 2, $infc['products_currencies_id']);
                 //
            }             

        }

        $CoDoZapisania .= '"' . $CenaProduktu . '";';  

        unset($CenaProduktu);
        
        $NaglowekCsv .= 'Cena_dla_allegro;';
        
        if ( $infc['products_price_allegro'] > 0 ) {
             $CoDoZapisania .= '"' . $waluty->FormatujCeneBezSymbolu($infc['products_price_allegro'], false, '', '', 2, $IdPLN) . '";';
        } else {
             $CoDoZapisania .= '"0.00";';
        }         
        
        $NaglowekCsv .= 'Cena_katalogowa;';
        
        if ( $infc['products_retail_price'] > 0 ) {
             $CoDoZapisania .= '"' . $waluty->FormatujCeneBezSymbolu($infc['products_retail_price'], false, '', '', 2, $infc['products_currencies_id']) . '";';
        } else {
             $CoDoZapisania .= '"0.00";';
        } 

        $NaglowekCsv .= 'Cena_zakupu;';
        
        if ( $infc['products_retail_price'] > 0 ) {
             $CoDoZapisania .= '"' . $waluty->FormatujCeneBezSymbolu($infc['products_purchase_price'], false, '', '', 2, $infc['products_currencies_id']) . '";';
        } else {
             $CoDoZapisania .= '"0.00";';
        }    

        $NaglowekCsv .= 'Status_produktu;';
        
        if ($infc['products_status'] == 1) {
            $CoDoZapisania .= '"tak";';
          } else {
            $CoDoZapisania .= '"nie";';
        }     

        $NaglowekCsv .= 'Status_aukcji;';
        
        $StatusAukcji = '"";';
        
        if ( $info['auction_status'] == 'ACTIVE' ) {
          $StatusAukcji = '"Aukcja trwa";';
        } elseif ( $info['auction_status'] == 'ENDED' ) {
          $StatusAukcji = '"Aukcja zakończona";';
        } elseif ( $info['auction_status'] == 'ACTIVATING' ) {
          $StatusAukcji = '"Aukcja czeka na wystawienie";';
        } elseif ( $info['auction_status'] == 'NOT_FOUND' ) {
          $StatusAukcji = '"Aukcja nie odnaleziona w Allegro";';
        } elseif ( $info['auction_status'] == 'ARCHIVED' ) {
          $StatusAukcji = '"Aukcja przeniesiona do archiwum Allegro";';
        }
        
        $CoDoZapisania .= $StatusAukcji;
        
        unset($StatusAukcji);
        
        $NaglowekCsv .= 'Id_produktu_sklep;';
        $CoDoZapisania .= '"' . $infc['products_id'] . '";';         
        
        $NaglowekCsv .= 'Nazwa_produktu_sklep;';
        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['products_name']) . '";';          

        $NaglowekCsv .= 'Kod_EAN;';
        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['products_ean']) . '";';          

        $NaglowekCsv .= 'Numer_katalogowy;';
        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['products_model']) . '";';          

        $NaglowekCsv .= 'Kod_producenta;';
        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['products_man_code']) . '";';                  

        $NaglowekCsv .= 'Producent;';
        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu(Funkcje::CzyszczenieTekstu($infc['manufacturers_name'])) . '";';         

        $NaglowekCsv .= 'Gabaryt;';
        
        if ($infc['products_pack_type'] == 1) {
            $CoDoZapisania .= '"tak";';
          } else {
            $CoDoZapisania .= '"nie";';
        }           
        
        $NaglowekCsv .= 'Indywidualny_koszt_wysylki;';
        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['shipping_cost']) . '";';        
        
        $KategorieProduktu = array();
        
        $NaglowekCsv .= 'Kategoria_sklep;';
        
        $zapytanieKategorie = $db->open_query("select distinct categories_id from products_to_categories where products_id = '" . (int)$info['products_id'] . "'");
        
        while ($inft = $zapytanieKategorie->fetch_assoc()) {
            //
            if ((int)$inft['categories_id'] == '0') {
                //
                $KategorieProduktu[] = 'Bez kategorii';
                //
              } else {
                //
                if ( isset($TablicaKategorii[(int)$inft['categories_id']]) ) {
                    //
                    $KategorieProduktu[] = $TablicaKategorii[(int)$inft['categories_id']]['text'];
                    //
                }
                //
            } 
            //
        }
        
        $db->close_query($zapytanieKategorie);        
        
        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu(implode(', ', (array)$KategorieProduktu)) . '";';          
        
        unset($KategorieProduktu);
        
        $db->close_query($sqlProdukt);
        unset($infc, $zapytanieProdukt); 
        
        $NaglowekCsv .= 'Kategoria_allegro;';
        
        $SciezkaAllegro = implode(' / ', explode(';', (string)$info['allegro_category_name']));
        $NazwaKategoriiAllegro = '';
        
        if ( $SciezkaAllegro != 'brak_kategorii' ) {
        
            if ( $SciezkaAllegro != 'brak_wyniku' && $SciezkaAllegro != 'brak_kategorii' && $SciezkaAllegro != 'brak_id' ) {
            
                $NazwaKategoriiAllegro = $SciezkaAllegro;

            }

        }        
        
        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($NazwaKategoriiAllegro) . '";';          

        $CoDoZapisania .= '"KONIEC"' . "\r\n";

        $Suma++;

    }
    
    if ($_POST['format'] == 'csv') {
                      
        // jezeli poczatek pliku
        if ( (int)$_POST['limit'] == 0 ) {
            $CoDoZapisania = $NaglowekCsv . 'KONIEC' . "\r\n" . $CoDoZapisania;
        }
        //
    }      

    fwrite($fp, $CoDoZapisania);
    
    // zapisanie danych do pliku
    flock($fp, 3);
    // zamkniecie pliku
    fclose($fp);       

}    
?>