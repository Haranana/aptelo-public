<?php

// wyglad srodkowy
$srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik));  

// wspolne stale
$srodek->dodaj('__DOMYSLNY_SZABLON', DOMYSLNY_SZABLON);

// style css
$tpl->dodaj('__CSS_PLIK', ',listingi');

//
$SposobWyswietlania = 1;

// klasa css dla aktywnej formy wyswietlania 
for ($k = 1; $k <= 3; $k++) {
    $srodek->dodaj('__CSS_WYGLAD_' . $k, ''); 
}

if (isset($_SESSION['wyswietlanie'])) {
    $srodek->dodaj('__CSS_WYGLAD_' . $_SESSION['wyswietlanie'], 'class="Tak"');
    $SposobWyswietlania = (int)$_SESSION['wyswietlanie'];
  } else {
    $srodek->dodaj('__CSS_WYGLAD_1', 'class="Tak"');
}

if (LISTING_JAKIE_WYSWIETLANIE == 'tylko okna') {
    $srodek->dodaj('__CSS_WYGLAD_2', 'style="display:none"');
    $srodek->dodaj('__CSS_WYGLAD_3', 'style="display:none"');
} 
if (LISTING_JAKIE_WYSWIETLANIE == 'tylko wiersze') {
    $srodek->dodaj('__CSS_WYGLAD_1', 'style="display:none"');
    $srodek->dodaj('__CSS_WYGLAD_3', 'style="display:none"');
} 
if (LISTING_JAKIE_WYSWIETLANIE == 'tylko lista') {
    $srodek->dodaj('__CSS_WYGLAD_1', 'style="display:none"');
    $srodek->dodaj('__CSS_WYGLAD_2', 'style="display:none"');
}
if (LISTING_JAKIE_WYSWIETLANIE == 'okna i wiersze') {
    $srodek->dodaj('__CSS_WYGLAD_3', 'style="display:none"');
}
if (LISTING_JAKIE_WYSWIETLANIE == 'okna i lista') {
    $srodek->dodaj('__CSS_WYGLAD_2', 'style="display:none"');
}
if (LISTING_JAKIE_WYSWIETLANIE == 'wiersze i lista') {
    $srodek->dodaj('__CSS_WYGLAD_1', 'style="display:none"');
}

if (LISTING_JEDNO_WYSWIETLANIE == 'nie' && (LISTING_JAKIE_WYSWIETLANIE == 'tylko okna' || LISTING_JAKIE_WYSWIETLANIE == 'tylko wiersze' || LISTING_JAKIE_WYSWIETLANIE == 'tylko lista')) {
    $srodek->dodaj('__CSS_WYGLAD_1', 'style="display:none"');
    $srodek->dodaj('__CSS_WYGLAD_2', 'style="display:none"');
    $srodek->dodaj('__CSS_WYGLAD_3', 'style="display:none"');    
}

// jezeli domyslne sortowanie wg kolejnosci sort 
if ( LISTING_DOMYSLNE_SORTOWANIE == 'domyślnie malejąco - wg sortowania' && $WywolanyPlik != 'nowosci' ) {
     //
     $DomyslneDol = 'p.sort_order desc, pd.products_name';
     $DomyslneGora = 'p.sort_order asc, pd.products_name';
     //
     $DomyslnyDolTekst = 'domyślnie malejąco - wg sortowania';
     $DomyslnyGoraTekst = 'domyślnie rosnąco - wg sortowania';
     //
} elseif ( LISTING_DOMYSLNE_SORTOWANIE == 'domyślnie rosnąco - wg sortowania' && $WywolanyPlik != 'nowosci' ) {
     //
     $DomyslneGora = 'p.sort_order asc, pd.products_name';
     $DomyslneDol = 'p.sort_order desc, pd.products_name';
     //
     $DomyslnyGoraTekst = 'domyślnie rosnąco - wg sortowania';
     $DomyslnyDolTekst = 'domyślnie malejąco - wg sortowania';
     //
} else {
     //
     $DomyslneDol = 'p.products_date_added desc, pd.products_name';
     $DomyslneGora = 'p.products_date_added asc, pd.products_name';
     //
     $DomyslnyDolTekst = 'domyślnie malejąco - wg daty dodania';
     $DomyslnyGoraTekst = 'domyślnie rosnąco - wg daty dodania';
     //
}

// inne sortowanie dla nowosci
if ( NOWOSCI_SORTOWANIE == 'wg daty dodania rosnąco' && $WywolanyPlik == 'nowosci' ) {
     //
     $DomyslneDol = 'p.products_date_added asc, pd.products_name';
     $DomyslneGora = 'p.products_date_added desc, pd.products_name';
     //
     $DomyslnyDolTekst = 'wg daty dodania rosnąco';
     $DomyslnyGoraTekst = 'wg daty dodania malejąco';
} elseif ( NOWOSCI_SORTOWANIE == 'wg daty dodania malejąco' && $WywolanyPlik == 'nowosci' ) {
     //
     $DomyslneDol = 'p.products_date_added desc, pd.products_name';
     $DomyslneGora = 'p.products_date_added asc, pd.products_name';
     //
     $DomyslnyDolTekst = 'wg daty dodania malejąco';
     $DomyslnyGoraTekst = 'wg daty dodania rosnąco';
} elseif ( NOWOSCI_SORTOWANIE == 'wg sortowania rosnąco' && $WywolanyPlik == 'nowosci' ) {
     //
     $DomyslneDol = 'p.sort_order desc, pd.products_name';
     $DomyslneGora = 'p.sort_order asc, pd.products_name';
     //
} elseif ( NOWOSCI_SORTOWANIE == 'wg sortowania malejąco' && $WywolanyPlik == 'nowosci' ) {
     //
     $DomyslneDol = 'p.sort_order asc, pd.products_name';
     $DomyslneGora = 'p.sort_order desc, pd.products_name';
     //
}


$TablicaSortowania = array( '1' => array($DomyslneDol, $DomyslnyDolTekst),
                            '2' => array($DomyslneGora, $DomyslnyGoraTekst),
                            '3' => array('cena desc, pd.products_name desc', 'wg ceny malejąco'),
                            '4' => array('cena asc, pd.products_name asc', 'wg ceny rosnąco'),
                            '5' => array('pd.products_name desc, cena desc', 'wg nazwy malejąco'),
                            '6' => array('pd.products_name asc, cena asc', 'wg nazwy rosnąco'),
                            '7' => array('p.products_ordered desc', 'wg największej popularności') );
                            
unset($DomyslneDol, $DomyslneGora, $DomyslnyDolTekst, $DomyslnyGoraTekst);

// domyslne sortowanie
if ( $WywolanyPlik != 'nowosci' ) {
  
    if ( !isset($_SESSION['sortowanie']) ) {

        if ( LISTING_SORTOWANIE_POPULARNOSC == 'nie' ) { 
             //
             $_SESSION['sortowanie'] = 1;
             //
        }
        //
        foreach ( $TablicaSortowania as $Klucz => $WartoscTablica ) {
            //
            if ( $WartoscTablica[1] == LISTING_DOMYSLNE_SORTOWANIE ) {
                 $_SESSION['sortowanie'] = $Klucz;
            }
            //
        }
        
    }
    
} else {
  
    if ( !isset($_SESSION['sortowanie_nowosci']) ) {
      
        if ( LISTING_SORTOWANIE_POPULARNOSC == 'nie' ) { 
             //
             $_SESSION['sortowanie_nowosci'] = 1;
             //
        }
        //
        foreach ( $TablicaSortowania as $Klucz => $WartoscTablica ) {
            //
            if ( $WartoscTablica[1] == NOWOSCI_SORTOWANIE ) {
                 $_SESSION['sortowanie_nowosci'] = $Klucz;
            }
            //
        }

    }
    
}

// dla aktualnego sortowania i dodawanie do zapytania sortowania
$NrSortowania = 1;

if ( $WywolanyPlik != 'nowosci' ) {

     if (isset($_SESSION['sortowanie'])) {
         if ( isset($TablicaSortowania[(int)$_SESSION['sortowanie']]) ) {
              $Sortowanie = $TablicaSortowania[(int)$_SESSION['sortowanie']][0];
              $NrSortowania = $_SESSION['sortowanie'];
         }
       } else {
         $Sortowanie = $TablicaSortowania[2][0];  
     }  
     
} else {

     if (isset($_SESSION['sortowanie_nowosci'])) {
         if ( isset($TablicaSortowania[(int)$_SESSION['sortowanie_nowosci']]) ) {
              $Sortowanie = $TablicaSortowania[(int)$_SESSION['sortowanie_nowosci']][0];
              $NrSortowania = $_SESSION['sortowanie_nowosci'];
         }
       } else {
         $Sortowanie = $TablicaSortowania[2][0];  
     }  
  
}

$SelectSortowania = '<select name="sortowanie' . (($WywolanyPlik == 'nowosci') ? '_nowosci' : '') . '" id="sortowanie">
                        <option value="1" ' . (($NrSortowania == 1) ? 'selected="selected"' : '') . '>' . $GLOBALS['tlumacz']['LISTING_SORTOWANIE_WG_DOMYSLNE'] . ' ' . mb_strtolower((string)$GLOBALS['tlumacz']['LISTING_SORTOWANIE_MALEJACO']) . '</option>
                        <option value="2" ' . (($NrSortowania == 2) ? 'selected="selected"' : '') . '>' . $GLOBALS['tlumacz']['LISTING_SORTOWANIE_WG_DOMYSLNE'] . ' ' . mb_strtolower((string)$GLOBALS['tlumacz']['LISTING_SORTOWANIE_ROSNACO']) . '</option>                                                              
                        <option value="4" ' . (($NrSortowania == 4) ? 'selected="selected"' : '') . '>' . $GLOBALS['tlumacz']['LISTING_SORTOWANIE_WG_CENY'] . ' ' . $GLOBALS['tlumacz']['LISTING_SORTOWANIE_OD_NAJNIZSZEJ'] . '</option>
                        <option value="3" ' . (($NrSortowania == 3) ? 'selected="selected"' : '') . '>' . $GLOBALS['tlumacz']['LISTING_SORTOWANIE_WG_CENY'] . ' ' . $GLOBALS['tlumacz']['LISTING_SORTOWANIE_OD_NAJWYZSZEJ'] . '</option>
                        <option value="6" ' . (($NrSortowania == 6) ? 'selected="selected"' : '') . '>' . $GLOBALS['tlumacz']['LISTING_SORTOWANIE_WG_NAZWY'] . ' ' . $GLOBALS['tlumacz']['LISTING_SORTOWANIE_NAZWA_A_Z'] . '</option>
                        <option value="5" ' . (($NrSortowania == 5) ? 'selected="selected"' : '') . '>' . $GLOBALS['tlumacz']['LISTING_SORTOWANIE_WG_NAZWY'] . ' ' . $GLOBALS['tlumacz']['LISTING_SORTOWANIE_NAZWA_Z_A'] . '</option>';   
                                          
if ( LISTING_SORTOWANIE_POPULARNOSC == 'tak' ) {                                          
     $SelectSortowania .= '<option value="7" ' . (($NrSortowania == 7) ? 'selected="selected"' : '') . '>' . $GLOBALS['tlumacz']['LISTING_SORTOWANIE_POPULARNOSC_NAJWIEKSZA'] . '</option>';
}

$SelectSortowania .= '</select>';

$srodek->dodaj('__WYBOR_SORTOWANIE', $SelectSortowania);

unset($NrSortowania, $SelectSortowania);                    

// klasa css dla ilosci produktow na stronie
for ($k = 1; $k <= 3; $k++) {
    $srodek->dodaj('__CSS_PRODSTR_' . $k, '');
    $srodek->dodaj('__LISTA_ILOSC_PROD_' . $k, LISTING_PRODUKTOW_NA_STRONIE * $k);     
}
$srodek->dodaj('__CSS_PRODSTR_' . ( $_SESSION['listing_produktow'] / LISTING_PRODUKTOW_NA_STRONIE ), 'class="Tak"');

// *****************************
// opcje filtrowania do 
// zapytania sql
// *****************************

// dla wyszykiwania sa oddzielne warunki 
if (!isset($_GET['szukaj'])) {
    //
    // cechy produktu
    $WarunkiFiltrowania = '';
    //
    // okresli jaki jest max nr id cechy
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('CechyIlosc', CACHE_INNE);
    
    if ( !$WynikCache ) {
        //
        $sqlIlosc = $GLOBALS['db']->open_query('select max(products_options_id) as nr_id from products_options');
        $infoIlosc = $sqlIlosc->fetch_assoc();
        //
        $GLOBALS['cache']->zapisz('CechyIlosc', $infoIlosc['nr_id'], CACHE_INNE);
        $IloscCech = $infoIlosc['nr_id'];
        //
        $GLOBALS['db']->close_query($sqlIlosc);
        unset($infoIlosc);  
        //     
      } else {
        //
        $IloscCech = $WynikCache;
        //
    }
    //
    unset($WynikCache);     
    //
    if ( (int)$IloscCech > 0 ) {
        //
        for ($p = 1; $p < $IloscCech + 1; $p++) {
            if (isset($_GET['c'.$p]) && Funkcje::czyNiePuste($_GET['c'.$p])) {  

              // jezeli jest magazyn cech to wyswietli tylko produkty ktore maja ta ceche w magazynie
              if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && CECHY_MAGAZYN == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' ) {
              
                  $WarunkiFiltrowania .= " AND (p.products_id in (SELECT distinct pst.products_id FROM products_stock pst, products pct WHERE pst.products_id = pct.products_id and (";
                  
                  $WartosciFiltra = Filtry::WyczyscFiltr($_GET['c'.$p]);
                  foreach ( $WartosciFiltra as $WartFiltra ) {
                      //
                      $WarunkiFiltrowania .= " find_in_set('" . $p . "-" . (int)$WartFiltra . "', pst.products_stock_attributes) or ";
                      //
                  }
                  unset($WartosciFiltra);

                  $WarunkiFiltrowania = substr((string)$WarunkiFiltrowania, 0, -3) . ") and (pst.products_stock_quantity > 0 || pct.products_control_storage = '0')))";

                } else {
                
                  $WarunkiFiltrowania .= " AND p.products_id in (SELECT products_id FROM products_attributes WHERE options_id = '" . $p . "' AND options_values_id in (" . implode(',', Filtry::WyczyscFiltr($_GET['c'.$p])) . ") )";
                
              }

              unset($Podziel);
            }  
        }
        //
    }
    unset($IloscCech);

    //
    // dodatkowe pola
    
    // okresli jaki jest max nr id dodatkowych pol
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('DodatkowePolaIlosc', CACHE_INNE);
    
    if ( !$WynikCache ) {
        //
        $sqlIlosc = $GLOBALS['db']->open_query('select max(products_extra_fields_id) as nr_id from products_extra_fields');
        $infoIlosc = $sqlIlosc->fetch_assoc();
        //
        $GLOBALS['cache']->zapisz('DodatkowePolaIlosc', $infoIlosc['nr_id'], CACHE_INNE);
        $IloscDodPol = $infoIlosc['nr_id'];
        //
        $GLOBALS['db']->close_query($sqlIlosc);
        unset($infoIlosc);  
        //     
      } else {
        //
        $IloscDodPol = $WynikCache;
        //
    }
    //
    unset($WynikCache);     
    //    
    if ( (int)$IloscDodPol > 0 ) {
        //
        $IdProduktowZapytanie = array();
        $ByloPole = false;
        //
        for ($p = 1; $p < $IloscDodPol + 1; $p++) {
            //
            if (isset($_GET['p'.$p]) && Funkcje::czyNiePuste($_GET['p'.$p])) { 
              //
              $ByloPole = true;
              //
              $TablicaPol = Filtry::WyczyscFiltr($_GET['p'.$p], true);
              //
              foreach ( $TablicaPol as $IdTmp ) {
                    //
                    $PodzialTmp = explode('-', $IdTmp);
                    //
                    $IdProduktowZapytanie[0][$p][0] = 0;
                    //
                    if ( count($PodzialTmp) == 2 ) {
                         //
                         $NrPola = '';
                         //
                         if ( (int)$PodzialTmp[1] == 1 ) {
                              $NrPola = '';
                         }
                         if ( (int)$PodzialTmp[1] == 2 ) {
                              $NrPola = '_1';
                         }
                         if ( (int)$PodzialTmp[1] == 3 ) {
                              $NrPola = '_2';
                         }                         
                         //
                         $zapytanie_wartosc = "SELECT DISTINCT p.products_extra_fields_value" . $NrPola . " as wartosc 
                                                          FROM products_to_products_extra_fields p
                                                          JOIN products_extra_fields e
                                                            ON p.products_extra_fields_id = e.products_extra_fields_id
                                                         WHERE p.products_id  = ". (int)$PodzialTmp[0] . " 
                                                           AND p.products_extra_fields_id = '" . $p . "'
                                                           AND (e.languages_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' or e.languages_id = '0')";
                                                           
                         $pola_sql = $GLOBALS['db']->open_query($zapytanie_wartosc);
                         //
                         while ($infw = $pola_sql->fetch_assoc()) {
                              //
                              $zapytanie_id = "SELECT products_id FROM products_to_products_extra_fields WHERE products_extra_fields_id = '" . $p . "' AND (products_extra_fields_value = '" . addslashes($infw['wartosc']) . "' or products_extra_fields_value_1 = '" . addslashes($infw['wartosc']) . "' or products_extra_fields_value_2 = '" . addslashes($infw['wartosc']) . "')";
                              $pola_id = $GLOBALS['db']->open_query($zapytanie_id);
                              //
                              while ($infe = $pola_id->fetch_assoc()) {
                                  //
                                  if ( (int)$infe['products_id'] > 0 ) {
                                        $IdProduktowZapytanie[0][$p][$infe['products_id']] = $infe['products_id'];
                                  }
                                  //
                              }
                              //
                              $GLOBALS['db']->close_query($pola_id); 
                              unset($zapytanie_id, $infe);
                              //
                         }
                         //
                         unset($NrPola);
                         //
                         $GLOBALS['db']->close_query($zapytanie_wartosc); 
                         unset($zapytanie_wartosc, $infw);
                         //
                    }
                    //
              }
              //
            }  
            //
            if (isset($_GET['p'.$p.'-o']) && (float)$_GET['p'.$p.'-o'] >= 0 && !isset($_GET['p'.$p.'-d'])) { 
               //
               $ByloPole = true;
               //
               $zapytanie_id = "SELECT DISTINCT p.products_id
                                           FROM products_to_products_extra_fields p
                                           JOIN products_extra_fields e
                                             ON p.products_extra_fields_id = e.products_extra_fields_id
                                          WHERE p.products_extra_fields_id = '" . $p . "'
                                            AND CASE 
                                                WHEN p.products_extra_fields_value REGEXP '^[0-9]+(\\.[0-9]+)?$' 
                                                THEN CAST(p.products_extra_fields_value AS DECIMAL(10, 2))
                                                ELSE 0 END >= " . (float)$_GET['p'.$p.'-o'] . "
                                            AND CASE 
                                                WHEN p.products_extra_fields_value REGEXP '^[0-9]+(\\.[0-9]+)?$' 
                                                THEN CAST(p.products_extra_fields_value AS DECIMAL(10, 2))
                                                ELSE 0 END > 0     
                                            AND (e.languages_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' or e.languages_id = '0')";
                                            
               $IdProduktowZapytanie[1][$p][0] = 0;
               
               $pola_id = $GLOBALS['db']->open_query($zapytanie_id);
               //
               while ($infw = $pola_id->fetch_assoc()) {
                    //
                    $IdProduktowZapytanie[1][$p][$infw['products_id']] = $infw['products_id'];
                    //
               }
               //
               $GLOBALS['db']->close_query($zapytanie_id); 
               unset($zapytanie_id, $infw);
               //
            }
            //
            if (isset($_GET['p'.$p.'-d']) && (float)$_GET['p'.$p.'-d'] >= 0 && !isset($_GET['p'.$p.'-o'])) { 
               //
               $ByloPole = true;
               //
               $zapytanie_id = "SELECT DISTINCT p.products_id
                                           FROM products_to_products_extra_fields p
                                           JOIN products_extra_fields e
                                             ON p.products_extra_fields_id = e.products_extra_fields_id
                                          WHERE p.products_extra_fields_id = '" . $p . "'
                                            AND CASE 
                                                WHEN p.products_extra_fields_value REGEXP '^[0-9]+(\\.[0-9]+)?$' 
                                                THEN CAST(p.products_extra_fields_value AS DECIMAL(10, 2))
                                                ELSE 0 END <= " . (float)$_GET['p'.$p.'-d'] . "
                                            AND CASE 
                                                WHEN p.products_extra_fields_value REGEXP '^[0-9]+(\\.[0-9]+)?$' 
                                                THEN CAST(p.products_extra_fields_value AS DECIMAL(10, 2))
                                                ELSE 0 END > 0                                                
                                            AND (e.languages_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' or e.languages_id = '0')";

               $IdProduktowZapytanie[2][$p][0] = 0;

               $pola_id = $GLOBALS['db']->open_query($zapytanie_id);
               //
               while ($infw = $pola_id->fetch_assoc()) {
                    //
                    $IdProduktowZapytanie[2][$p][$infw['products_id']] = $infw['products_id'];
                    //
               }
               //
               $GLOBALS['db']->close_query($zapytanie_id); 
               unset($zapytanie_id, $infw);
               //
            }
            if (isset($_GET['p'.$p.'-o']) && (float)$_GET['p'.$p.'-o'] >= 0 && isset($_GET['p'.$p.'-d']) && (float)$_GET['p'.$p.'-d'] >= 0) { 
               //
               $ByloPole = true;
               //
               $zapytanie_id = "SELECT DISTINCT p.products_id
                                           FROM products_to_products_extra_fields p
                                           JOIN products_extra_fields e
                                             ON p.products_extra_fields_id = e.products_extra_fields_id
                                          WHERE p.products_extra_fields_id = '" . $p . "'
                                            AND CASE 
                                                WHEN p.products_extra_fields_value REGEXP '^[0-9]+(\\.[0-9]+)?$' 
                                                THEN CAST(p.products_extra_fields_value AS DECIMAL(10, 2))
                                                ELSE 0 END >= " . (float)$_GET['p'.$p.'-o'] . "
                                            AND CASE 
                                                WHEN p.products_extra_fields_value REGEXP '^[0-9]+(\\.[0-9]+)?$' 
                                                THEN CAST(p.products_extra_fields_value AS DECIMAL(10, 2))
                                                ELSE 0 END <= " . (float)$_GET['p'.$p.'-d'] . "
                                            AND CASE 
                                                WHEN p.products_extra_fields_value REGEXP '^[0-9]+(\\.[0-9]+)?$' 
                                                THEN CAST(p.products_extra_fields_value AS DECIMAL(10, 2))
                                                ELSE 0 END > 0 
                                            AND (e.languages_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' or e.languages_id = '0')";

               $IdProduktowZapytanie[3][$p][0] = 0;
               
               $pola_id = $GLOBALS['db']->open_query($zapytanie_id);
               //
               while ($infw = $pola_id->fetch_assoc()) {
                    //
                    $IdProduktowZapytanie[3][$p][$infw['products_id']] = $infw['products_id'];
                    //
               }
               //
               $GLOBALS['db']->close_query($zapytanie_id); 
               unset($zapytanie_id, $infw);
               //
            } 
            //
        }
        //

        if ( count($IdProduktowZapytanie) > 0 ) { 
             //
             $WszystkiePodtablice = array(); 
             //
             foreach ($IdProduktowZapytanie as $grupa) {
                  //
                  foreach ($grupa as $podtablica) { 
                      $WszystkiePodtablice[] = $podtablica; 
                  }
                  //
             }
             //
             if (is_array($WszystkiePodtablice) && count($WszystkiePodtablice) > 1) {
                 //
                 $IdProduktowZapytanie = call_user_func_array('array_intersect', $WszystkiePodtablice);
                 //
             } else {
                 //
                 $IdProduktowZapytanie = $WszystkiePodtablice[0];
                 //
             }
             //
             unset($WszystkiePodtablice);
             //
        }
        //
        if ( count($IdProduktowZapytanie) > 0 ) {          
             //
             $WarunkiFiltrowania .= " AND p.products_id in (" . implode(',',$IdProduktowZapytanie) . ")";
             //
        } else if ( $ByloPole == true ) {
             //
             $WarunkiFiltrowania .= " AND p.products_id = 0";             
             //
        }
        //
    }
    unset($IloscDodPol);
    //
    // tylko promocje
    if (isset($_GET['promocje']) && $_GET['promocje'] == 'tak') {
        $WarunkiFiltrowania .= " AND p.specials_status = '1' AND (p.specials_date = '0000-00-00 00:00:00' OR now() > p.specials_date) AND (p.specials_date_end = '0000-00-00 00:00:00' OR now() < p.specials_date_end)";
    }
    // tylko nowosci
    if (isset($_GET['nowosci']) && $_GET['nowosci'] == 'tak') {
        $WarunkiFiltrowania .= " AND p.new_status = '1'";
    }
    // zakres cenowy
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         if ( NARZUT_NIEZALOGOWANI != '' && floatval(NARZUT_NIEZALOGOWANI) != 0 ) {
              //
              if ( NARZUT_NIEZALOGOWANI_PROMOCJE == 'nie' ) {
                   //
                   $DodWarunekCen = '( IF( p.specials_status = 1, (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100), ((p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ') )';     
                   //
                } else {
                   //
                   $DodWarunekCen = '(((p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)) * ' . ((100 + NARZUT_NIEZALOGOWANI) / 100) . ')';
                   //
              }
            } else {
              $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
         }         
         
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }

    //
    $Przelicznik = $_SESSION['domyslnaWaluta']['przelicznik'] * ( 1 + ( $_SESSION['domyslnaWaluta']['marza']/100 ) );

    if (isset($_GET['ceno']) && (float)$_GET['ceno'] > 0) {

        $WarunkiFiltrowania .= " AND " . $DodWarunekCen . " >= " . (float)$_GET['ceno']/$Przelicznik;
        
    }
    if (isset($_GET['cend']) && (float)$_GET['cend'] > 0) {
        $WarunkiFiltrowania .= " AND " . $DodWarunekCen . " <= " . (float)$_GET['cend']/$Przelicznik;
    }
    //
    unset($DodWarunekCen, $Przelicznik);
    //
    // producent
    if (isset($_GET['producent'])) {
        $WarunkiFiltrowania .= " AND p.manufacturers_id in (" . implode(',', Filtry::WyczyscFiltr($_GET['producent'])) . ")";
    }
    // kategoria
    if (isset($_GET['kategoria'])) {
        $WarunkiFiltrowania .= " AND c.categories_id in (" . implode(',', Filtry::WyczyscFiltr($_GET['kategoria'])) . ")";
    }
    // dostepnosc
    if (isset($_GET['dostepnosc'])) {
        //
        $WarunekIn = array();
        $WarunekAnd = array();
        //
        $FiltrDostepnosc = Filtry::WyczyscFiltr($_GET['dostepnosc']);
        //
        foreach ( $FiltrDostepnosc as $GetDostepnosc ) {
            //
            if ( $GetDostepnosc > 100 ) {
                 //
                 if ( isset($GLOBALS['dostepnosci'][$GetDostepnosc - 100]) ) {
                      //
                      $WarunekAnd[] = "(p.products_quantity >= " . $GLOBALS['dostepnosci'][$GetDostepnosc - 100]['od_ilosci'] . ((isset($GLOBALS['dostepnosci'][$GetDostepnosc - 100]['do_ilosci'])) ? " AND p.products_quantity < " . $GLOBALS['dostepnosci'][$GetDostepnosc - 100]['do_ilosci'] : "") . ")";
                      //
                 }
                 //
            } else {
                 //
                 $WarunekIn[] = $GetDostepnosc;
                 //
            }
            //
        }
        
        if ( count($WarunekAnd) > 0 || count($WarunekIn) > 0 ) {
             $WarunkiFiltrowania .= " AND (";
             //
             $TmpDost = array();
             //
             if ( count($WarunekAnd) > 0 ) {
                 $TmpDost[] = "(p.products_availability_id = '99999' AND (" . implode(' OR ', (array)$WarunekAnd) . "))";
             }
             if ( count($WarunekIn) > 0 ) {
                 $TmpDost[] = "(p.products_availability_id in (" . implode(',', (array)$WarunekIn) . "))";
             }  
             //
             $WarunkiFiltrowania .= implode(' OR ', (array)$TmpDost) . ")";
             //
             unset($TmpDost);
        }      
        //
    }    
    //
    // czaswysylki
    if (isset($_GET['wysylka'])) {
        $WarunkiFiltrowania .= " AND ( p.products_shipping_time_id in (" . implode(',', Filtry::WyczyscFiltr($_GET['wysylka'])) . ") or ( p.products_shipping_time_zero_quantity_id in (" . implode(',', Filtry::WyczyscFiltr($_GET['wysylka'])) . ") and p.products_quantity <= 0 ) )";
    }
    //   
}

// *****************************

// filtry wspolne
$srodek->dodaj('__CENA_OD_WARTOSC', ((isset($_GET['ceno']) && (float)$_GET['ceno'] > 0) ? (float)$_GET['ceno'] : ''));
$srodek->dodaj('__CENA_DO_WARTOSC', ((isset($_GET['cend']) && (float)$_GET['cend'] > 0) ? (float)$_GET['cend'] : ''));

// porownywanie produktow
$srodek->dodaj('__PRODUKTY_DO_POROWNANIA', '');
$srodek->dodaj('__CSS_POROWNANIE', 'style="display:none"');
$srodek->dodaj('__CSS_PRZYCISK_POROWNANIE', 'style="display:none"');
if ( isset($_SESSION['produktyPorownania']) && count($_SESSION['produktyPorownania']) > 0 && LISTING_POROWNYWARKA_PRODUKTOW == 'tak' ) {
    //
    $DoPorownaniaId = '';
    foreach ($_SESSION['produktyPorownania'] AS $Id) {
        $DoPorownaniaId .= $Id . ',';
    }
    $DoPorownaniaId = substr((string)$DoPorownaniaId, 0, -1);
    //
    $zapNazwy = Produkty::SqlPorownanieProduktow($DoPorownaniaId);
    //
    $sqlNazwy = $GLOBALS['db']->open_query($zapNazwy);
    //
    $DoPorownaniaLinki = '';
    while ($infc = $sqlNazwy->fetch_assoc()) {
        //
        // ustala jaka ma byc tresc linku
        $linkSeo = ((!empty($infc['products_seo_url'])) ? $infc['products_seo_url'] : $infc['products_name']);
        //
        $DoPorownaniaLinki .= '<div class="PozycjaDoPorownania"><span onclick="Porownaj(' . $infc['products_id'] . ',\'wy\')"></span><a href="' . Seo::link_SEO( $linkSeo, $infc['products_id'], 'produkt' ) . '">' . $infc['products_name'] . '</a></div>';
        //    
        unset($linkSeo);
        //
        // sprawdza czy produkt nie zostal wylaczony - jezeli tak usunie go z porownania
        if ( $infc['products_status'] == '0' ) {
             unset($_SESSION['produktyPorownania'][$infc['products_id']]);
             Funkcje::PrzekierowanieURL($_SERVER['REQUEST_URI']);
        }
        //
    }
    $GLOBALS['db']->close_query($sqlNazwy); 
    unset($zapNazwy, $DoPorownaniaId, $infc);      
    //
    $srodek->dodaj('__PRODUKTY_DO_POROWNANIA', $DoPorownaniaLinki);
    $srodek->dodaj('__CSS_POROWNANIE', '');
    //
    unset($DoPorownaniaLinki);
    //
    // jezeli jest wiecej niz 1 produkt do porownania to pokaze przycisk
    if (count($_SESSION['produktyPorownania']) > 1) {
        $srodek->dodaj('__CSS_PRZYCISK_POROWNANIE', 'style="display:block"');
    }
    //
}

$srodek->dodaj('__OPIS_LISTINGU_DOL', '');

/* inne wyrownanie dla ilosci na stronie */
$srodek->dodaj('__CSS_LISTING_POZYCJI_NA_STRONIE', '');

if ( LISTING_WYSWIETLAC_SPOSOB_WYSWIETLANIA == 'nie' && LISTING_WYSWIETLAC_SORTOWANIE == 'tak' ) {
     $srodek->dodaj('__CSS_LISTING_POZYCJI_NA_STRONIE', ' IloscProdStronieCalaLinia');  
}
if ( LISTING_WYSWIETLAC_SPOSOB_WYSWIETLANIA == 'tak' && LISTING_WYSWIETLAC_SORTOWANIE == 'nie' ) {
     $srodek->dodaj('__CSS_LISTING_POZYCJI_NA_STRONIE', ' IloscProdStronieCalaLinia');  
}

/* inne wyrownanie dla sortowania */
$srodek->dodaj('__CSS_LISTING_SORTOWANIE', '');

if ( LISTING_WYSWIETLAC_SPOSOB_WYSWIETLANIA == 'nie' ) {
     $srodek->dodaj('__CSS_LISTING_SORTOWANIE', ' SortowanieDoPrawej');  
}
?>