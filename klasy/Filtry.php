<?php

class Filtry {
  
    static public function FiltryAktywne( $wartosc = '' ) {

        if ( !isset($GLOBALS['filtry']) ) {
             //
             $GLOBALS['filtry'] = array();
             //
        }
        
        if ( !empty($wartosc) ) {
            // 
            $GLOBALS['filtry'][ $wartosc[1] . '-' . $wartosc[0] ] = $wartosc;
            //
        }

    }
    
    static public function LinkiFiltryAktywne() {
                
        $LinkiWynik = array();

        if ( isset($GLOBALS['filtry']) ) {

            $NazwyPolDodatkowych = array();

            // cache nazwy pold dodaktowych
            $WynikCachePola = $GLOBALS['cache']->odczytaj('DodatkowePolaNazwy_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);
            
            if ( !$WynikCachePola ) {
                //
                $zapytanie = "SELECT products_extra_fields_id AS IdPola, 
                                     products_extra_fields_name AS NazwaPola
                                FROM products_extra_fields 
                               WHERE products_extra_fields_status = '1' AND
                                     products_extra_fields_filter = '1' AND
                                     (languages_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' OR languages_id = '0')
                            ORDER BY products_extra_fields_order";            

                $sql = $GLOBALS['db']->open_query($zapytanie);
                //
                if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
                    //           
                    while ($info = $sql->fetch_assoc()) {
                        $NazwyPolDodatkowych[ $info['IdPola'] ] = $info['NazwaPola'];
                    }
                    //
                    unset($info);  
                    //
                }
                
                $GLOBALS['cache']->zapisz('DodatkowePolaNazwy_' . $_SESSION['domyslnyJezyk']['kod'], $NazwyPolDodatkowych, CACHE_INNE);
                //
                $GLOBALS['db']->close_query($sql);
                //     
              } else {
                //
                $NazwyPolDodatkowych = $WynikCachePola;
                //
            }
            //
            // generuje tablice globalne z nazwami cech
            Funkcje::TabliceCech();               

            $PathInfo = explode('/', ltrim(trim(Funkcje::RequestURI(), '/'), '/'));
            
            $PodstawowyAdres = $PathInfo[0];
            
            unset($PathInfo[0]);

            foreach ( $GLOBALS['filtry'] as $KluczGet => $PozGet ) {
              
                $PodzielKlucz = explode('-', (string)$KluczGet);
                
                $TmpGet = $_GET;
                
                foreach ( $TmpGet as $KluczTmp => $Tmp ) {
                    //
                    if ( strpos((string)$KluczTmp, '-o') > -1 || strpos((string)$KluczTmp, '-d') > -1 ) {
                         //                        
                         if ( $KluczTmp == $KluczGet ) {
                              //
                              $TmpGet[$KluczTmp] = '';
                              //
                         }
                         //
                    } else {
                         //
                         if ( $KluczTmp == $PodzielKlucz[0] ) {
                              //
                              $WartoscTmp = explode(',', (string)$Tmp);
                              //
                              foreach ( $WartoscTmp as $UsunKlucz => $UsunTmp ) {
                                 //
                                 $PodzielKluczTmp = $PodzielKlucz[1];
                                 //
                                 if ( isset($PodzielKlucz[2]) ) {  
                                      $PodzielKluczTmp = $PodzielKlucz[1] . '-' . $PodzielKlucz[2];
                                 }
                                 //
                                 if ( $UsunTmp == $PodzielKluczTmp ) {
                                      unset($WartoscTmp[$UsunKlucz]);
                                 }
                                 //
                                 unset($PodzielKluczTmp);
                                 //
                              }
                              //
                              $TmpGet[$KluczTmp] = implode(',', (array)$WartoscTmp);
                              //
                              unset($WartoscTmp);
                              //
                         }
                         //
                    }
                    //
                }
                
                $DodatkoweParametry = array();

                foreach ( $TmpGet as $KtG => $TmG ) {
                    //
                    if ( !empty($TmG) && $KtG != 's' && $KtG != 'idkat' && $KtG != 'idproducent' ) {
                         //                        
                         $DodatkoweParametry[] = $KtG . '=' . $TmG;
                         //
                    }
                    //
                }
                
                // tytul
                $NazwaFiltra = '';
                
                switch ($PozGet[1]) {
                    case "producent":
                        $NazwaFiltra = '<small>' . $GLOBALS['tlumacz']['PRODUCENT'] . ':</small> ';
                        break;
                    case "kategoria":
                        $NazwaFiltra = '<small>' . $GLOBALS['tlumacz']['KATEGORIA'] . ':</small> ';
                        break;                        
                    case "dostepnosc":
                        $NazwaFiltra = '<small>' . $GLOBALS['tlumacz']['DOSTEPNOSC'] . ':</small> ';
                        break;    
                    case "wysylka":
                        $NazwaFiltra = '<small>' . $GLOBALS['tlumacz']['CZAS_WYSYLKI'] . ':</small> ';
                        break;                        
                }
                
                // dodatkowe pola
                if ( substr((string)$PodzielKlucz[0], 0, 1) == 'p' && $PodzielKlucz[0] != 'producent' ) {
                     //
                     $WartoscOd = (((string)$PodzielKlucz[1] == 'o')? ' ' . $GLOBALS['tlumacz']['ZAKRES_OD'] : '');
                     $WartoscDo = (((string)$PodzielKlucz[1] == 'd') ? ' ' . $GLOBALS['tlumacz']['ZAKRES_DO'] : '');
                     //
                     $IdPoleDodatkowe = str_replace(array('-o','-d'), '', (int)str_replace('p', '', (string)$PodzielKlucz[0]));
                     //
                     if ( isset($NazwyPolDodatkowych[$IdPoleDodatkowe]) ) {
                          $NazwaFiltra = '<small>' . $NazwyPolDodatkowych[$IdPoleDodatkowe] . $WartoscOd . $WartoscDo . ':</small> ';
                     }
                     //
                     unset($IdPoleDodatkowe, $WartoscOd, $WartoscDo);
                     //
                }       
                
                // cechy
                if ( substr((string)$PodzielKlucz[0], 0, 1) == 'c' ) {
                     //
                     $IdCechy = (int)str_replace('c', '', (string)$PodzielKlucz[0]);
                     // 
                     if ( isset($GLOBALS['NazwyCech']) && isset($GLOBALS['NazwyCech'][$IdCechy]) ) {
                          $NazwaFiltra = '<small>' . $GLOBALS['NazwyCech'][$IdCechy]['nazwa'] . ':</small> ';
                     }
                     //
                     unset($IdCechy);
                     //
                }                 
                //
                $LinkiWynik[] = '<a href="' . ADRES_URL_SKLEPU . '/' . $PodstawowyAdres . ((!empty($DodatkoweParametry)) ? '/' . implode('/', (array)$DodatkoweParametry) : '') . '">' . $NazwaFiltra . $PozGet[2] . '</a>';
                //
                
                unset($DodatkoweParametry, $PodzielKlucz, $NazwaFiltra);

            }

            unset($PathInfo); 

        }
        
        // tylko nowosci
        if ( isset($_GET['nowosci']) && $_GET['nowosci'] == 'tak' ) {
             //
             $LinkiWynik[] = '<a href="' .  Filtry::ZwrocAdresFiltra('nowosci') . '"><small>' . $GLOBALS['tlumacz']['LISTING_TYLKO_NOWOSCI'] . ':</small> ' . strtoupper((string)$GLOBALS['tlumacz']['TAK']) . '</a>';
             //
        }
        // tylko promocje
        if ( isset($_GET['promocje']) && $_GET['promocje'] == 'tak' ) {
             //
             $LinkiWynik[] = '<a href="' .  Filtry::ZwrocAdresFiltra('promocje') . '"><small>' . $GLOBALS['tlumacz']['LISTING_TYLKO_PROMOCJE'] . ':</small> ' . strtoupper((string)$GLOBALS['tlumacz']['TAK']) . '</a>';
             //
        }
        // cena od
        if ( isset($_GET['ceno']) && (float)$_GET['ceno'] > 0 ) {
             //
             $LinkiWynik[] = '<a href="' .  Filtry::ZwrocAdresFiltra('ceno') . '"><small>' . $GLOBALS['tlumacz']['CENA_OD'] . ':</small> ' . number_format((float)$_GET['ceno'], CENY_MIEJSCA_PO_PRZECINKU, $GLOBALS['waluty']->waluty[$_SESSION['domyslnaWaluta']['kod']]['separator'], ' ') . ' ' . $GLOBALS['waluty']->waluty[$_SESSION['domyslnaWaluta']['kod']]['symbol'] . '</a>';
             //
        }
        // cena do
        if ( isset($_GET['cend']) && (float)$_GET['cend'] > 0 ) {
             //
             $LinkiWynik[] = '<a href="' .  Filtry::ZwrocAdresFiltra('cend') . '"><small>' . $GLOBALS['tlumacz']['CENA_DO'] . ':</small> ' . number_format((float)$_GET['cend'], CENY_MIEJSCA_PO_PRZECINKU, $GLOBALS['waluty']->waluty[$_SESSION['domyslnaWaluta']['kod']]['separator'], ' ') . ' ' . $GLOBALS['waluty']->waluty[$_SESSION['domyslnaWaluta']['kod']]['symbol'] . '</a>';
             //
        }

        return $LinkiWynik;
      
    }
    
    static public function ZwrocAdresFiltra( $filtr = '' ) {
      
        $DodatkoweParametry = array();
        
        foreach ( $_GET as $KtG => $TmG ) {
            //
            if ( !empty($TmG) && $KtG != 's' && $KtG != 'idkat' && $KtG != 'idproducent' && $KtG != $filtr ) {
                 //                        
                 $DodatkoweParametry[] = $KtG . '=' . $TmG;
                 //
            }
            //
        }  
        
        $PathInfo = explode('/', ltrim(trim(Funkcje::RequestURI(), '/'), '/'));
        
        $PodstawowyAdres = $PathInfo[0];
        
        unset($PathInfo[0]);        
          
        return ADRES_URL_SKLEPU . '/' . $PodstawowyAdres . ((!empty($DodatkoweParametry)) ? '/' . implode('/', (array)$DodatkoweParametry) : '');

    }             

    static public function IdProduktowDlaFiltrow( $typ = 'kategoria', $id = 0 ) {
    
        // jezeli jest kategoria
        if ($typ == 'kategoria') {
            $ZapytanieWarunkowe = "SELECT p.products_id
                                              FROM products p
                                         LEFT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                                         LEFT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1' AND c.categories_view = '1'
                                             WHERE p.products_status = '1' and p.listing_status = '0'" . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . "" . $GLOBALS['warunekProduktu'] . " AND c.categories_id in (" . $id . ")";
        }
        
        // jezeli jest producent
        if ($typ == 'producent') {
            $ZapytanieWarunkowe = "SELECT p.products_id
                                              FROM products p
                                         LEFT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                                         LEFT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1' AND c.categories_view = '1'
                                             WHERE p.products_status = '1' and p.listing_status = '0'" . ( LISTING_PRODUKTY_ZERO == 'nie' ? ' AND p.products_quantity > 0 ' : '' ) . "" . $GLOBALS['warunekProduktu'] . " AND p.manufacturers_id = '" . $id . "'";
        }
        
        // jezeli sa promocje
        if ($typ == 'promocje') {
            //
            // cache zapytania
            $WynikCache = $GLOBALS['cache']->odczytaj('PromocjeProste', CACHE_PROMOCJE, true);
            
            if ( !$WynikCache ) {
                 $ZapytanieWarunkowe = Produkty::SqlPromocjeProste();
               } else {
                 $WynikCache = array_unique($WynikCache);
                 $ZapytanieWarunkowe = implode(',', (array)$WynikCache);
            }
            
            unset($WynikCache);
        }    

        // jezeli sa wyprzedaze
        if ($typ == 'wyprzedaz') {
            //
            // cache zapytania
            $WynikCache = $GLOBALS['cache']->odczytaj('WyprzedazProste', CACHE_PROMOCJE, true);
            
            if ( !$WynikCache ) {
                 $ZapytanieWarunkowe = Produkty::SqlWyprzedazProste();
               } else {
                 $WynikCache = array_unique($WynikCache);
                 $ZapytanieWarunkowe = implode(',', (array)$WynikCache);
            }
            
            unset($WynikCache);
        }           

        // jezeli sa nowosci
        if ($typ == 'nowosci') {
            //
            // cache zapytania
            $WynikCache = $GLOBALS['cache']->odczytaj('NowosciProste', CACHE_NOWOSCI, true);
            
            if ( !$WynikCache ) {
                 $ZapytanieWarunkowe = Produkty::SqlNowosciProste();
               } else {
                 $WynikCache = array_unique($WynikCache);
                 $ZapytanieWarunkowe = implode(',', (array)$WynikCache);
            }
            
            unset($WynikCache);        
        }   

        // jezeli sa polecane
        if ($typ == 'polecane') {
            //
            // cache zapytania
            $WynikCache = $GLOBALS['cache']->odczytaj('PolecaneProste', CACHE_POLECANE, true);
            
            if ( !$WynikCache ) {
                 $ZapytanieWarunkowe = Produkty::SqlPolecaneProste();
               } else {
                 $WynikCache = array_unique($WynikCache);
                 $ZapytanieWarunkowe = implode(',', (array)$WynikCache);
            }
            
            unset($WynikCache);          
        }      

        // jezeli sa hity
        if ($typ == 'hity') {
            //
            // cache zapytania
            $WynikCache = $GLOBALS['cache']->odczytaj('NaszHitProste', CACHE_HITY, true);
            
            if ( !$WynikCache ) {
                 $ZapytanieWarunkowe = Produkty::SqlNaszHitProste();
               } else {
                 $WynikCache = array_unique($WynikCache);
                 $ZapytanieWarunkowe = implode(',', (array)$WynikCache);
            }
            
            unset($WynikCache);         
        }    

        // jezeli sa bestsellery
        if ($typ == 'bestsellery') {
            $ZapytanieWarunkowe = Produkty::SqlBestselleryProste();
        }          
        
        // jezeli sa oczekiwane
        if ($typ == 'oczekiwane') {
            //
            // cache zapytania
            $WynikCache = $GLOBALS['cache']->odczytaj('OczekiwaneProste', CACHE_OCZEKIWANE, true);
            
            if ( !$WynikCache ) {
                 $ZapytanieWarunkowe = Produkty::SqlOczekiwaneProste();
               } else {
                 $WynikCache = array_unique($WynikCache);
                 $ZapytanieWarunkowe = implode(',', (array)$WynikCache);
            }
            
            unset($WynikCache);             
        }         
        
        // jezeli sa produkty
        if ($typ == 'produkty') {
            //
            $ZapytanieWarunkowe = Produkty::SqlProduktyProsteStatystyka();
            //
        }         

        return $ZapytanieWarunkowe;

    }

    // zwraca tablice z cechami dla danych id kategorii lub producenta
    static public function FiltrCech( $id = 0, $typ = 'kategoria' ) {  
        //
        $TablicaWyniku = array();
        //
        // cachowanie filtrow cech
        $WynikCache = $GLOBALS['cache']->odczytaj('CechyFiltr_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_PRODUKTY, true);
        //
        if ( !$WynikCache || !is_array($WynikCache) || (is_array($WynikCache) && !isset($WynikCache[ $typ . '-' . $id ])) ) {
            // 
            $TablicaCech = "SELECT DISTINCT pa.options_id AS IdCechy,
                                            po.products_options_name AS NazwaCechy,
                                            po.products_options_images_enabled
                                       FROM products_attributes pa, 
                                            products_options po
                                      WHERE po.products_options_id = pa.options_id AND 
                                            po.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' AND
                                            po.products_options_filter = '1' AND 
                                            pa.products_id in (" . Filtry::IdProduktowDlaFiltrow($typ, $id) . ")
                                   ORDER BY po.products_options_sort_order";

            $TablicaWartosci = "SELECT DISTINCT pa.options_id AS IdCechy,
                                                pa.options_values_id AS IdWartosci,
                                                pov.products_options_values_name AS Wartosc,
                                                pov.products_options_values_thumbnail AS ObrazekCechy
                                           FROM products_attributes pa, 
                                                products_options po,
                                                products_options_values pov,
                                                products_options_values_to_products_options ptp
                                          WHERE pov.products_options_values_id = pa.options_values_id AND
                                                pa.options_id = po.products_options_id AND
                                                po.products_options_filter = '1' AND
                                                pov.products_options_values_id = ptp.products_options_values_id AND
                                                pov.products_options_values_status = '1' AND 
                                                pov.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' AND 
                                                pa.products_id in (" . Filtry::IdProduktowDlaFiltrow($typ, $id) . ")
                                       ORDER BY po.products_options_sort_order, ptp.products_options_values_sort_order";                          

            $sql = $GLOBALS['db']->open_query($TablicaWartosci);
            
            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
                //
                if ( POKAZUJ_FILTRY_ILOSCI == 'tak' ) {
                
                    // obliczanie ilosci produktow w pozycji
                    $ZapytanieIlosc = "SELECT options_id, options_values_id, products_id FROM products_attributes WHERE products_id in (" . Filtry::IdProduktowDlaFiltrow($typ, $id) . ")";
                    $sqlIlosc = $GLOBALS['db']->open_query($ZapytanieIlosc);
                    
                    $TablicaIlosciTmp = array();                
                    while ($info = $sqlIlosc->fetch_assoc()) {
                        //
                        $TablicaIlosciTmp[] = $info['options_id'] . '-' . $info['options_values_id'];
                        //
                    }
                    $GLOBALS['db']->close_query($sqlIlosc);
                    unset($ZapytanieIlosc, $info);

                    $TablicaIlosci = array_count_values($TablicaIlosciTmp);
                    
                    // jezeli jest magazyn cech to wyswietli tylko produkty ktore maja ta ceche w magazynie
                    if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && CECHY_MAGAZYN == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' ) {
                         //
                         foreach ( $TablicaIlosci as $Klucz => $Tmp ) {
                              //
                              $DodatkoweZapytanie = "SELECT distinct pst.products_id FROM products_stock pst, products p WHERE pst.products_id = p.products_id and find_in_set('" . $Klucz . "', pst.products_stock_attributes) and (pst.products_stock_quantity > 0 || p.products_control_storage = '0') and p.products_id in (" . Filtry::IdProduktowDlaFiltrow($typ, $id) . ")";                                  
                              $sqlDodatkowe = $GLOBALS['db']->open_query($DodatkoweZapytanie);
                              //
                              $IleTmp = 0;
                              //
                              while ($infi = $sqlDodatkowe->fetch_assoc()) {
                                     $IleTmp++;
                              }
                              //
                              $TablicaIlosci[ $Klucz ] = $IleTmp;
                              //
                              $GLOBALS['db']->close_query($sqlDodatkowe);
                              unset($DodatkoweZapytanie, $infi, $IleTmp); 
                              //
                         }
                         //
                    }                

                }
                
                //
                // tworzenie tablicy z wartosciami cech
                while ($info = $sql->fetch_assoc()) {
                    //
                    $IloscPozycji = 0;
                    //
                    if ( POKAZUJ_FILTRY_ILOSCI == 'tak' ) {
                        //
                        if ( isset($TablicaIlosci[ $info['IdCechy'] . '-' . $info['IdWartosci'] ]) ) {
                             //
                             $IloscPozycji = $TablicaIlosci[ $info['IdCechy'] . '-' . $info['IdWartosci'] ];
                             //
                        }
                        //
                    }
                    //
                    $TablicaWyniku[ $info['IdCechy'] ][ $info['IdWartosci'] ] = array( $info['Wartosc'] . ((POKAZUJ_FILTRY_ILOSCI == 'tak') ? ' (' . $IloscPozycji . ')' : ''), $info['ObrazekCechy'], $info['Wartosc'] );              
                    //
                    unset($IloscPozycji);
                    //
                }  
                //
                $GLOBALS['db']->close_query($sql);
                unset($zapytanie, $info);             
                //
                $sql = $GLOBALS['db']->open_query($TablicaCech);
                //
                // dodawanie do tablicy z wartosciami cech nazwy cechy
                while ($info = $sql->fetch_assoc()) {
                    $TablicaWyniku[ $info['IdCechy'] ][ 'nazwa' ] = $info['NazwaCechy']; 
                    // jezeli jest obrazkowa cecha
                    if ( $info['products_options_images_enabled'] == 'true' ) {
                         $TablicaWyniku[ $info['IdCechy'] ][ 'obrazek' ] = 'tak'; 
                       } else {
                         $TablicaWyniku[ $info['IdCechy'] ][ 'obrazek' ] = 'nie'; 
                    }
                    //
                }  
                //
                $GLOBALS['db']->close_query($sql);
                unset($zapytanie, $info);             
                //                
            }

            unset($TablicaCech, $TablicaWartosci);

            // sprawdzanie pustych wpisow
            foreach ( $TablicaWyniku as $Klucz => $PozycjaCecha ) {
                //
                if ( count($PozycjaCecha) == 2 || !isset($PozycjaCecha['nazwa']) ) {
                     unset($TablicaWyniku[ $Klucz ]);
                }
                //
            }

            if ( is_array($WynikCache) ) {
                 $Wynik = $WynikCache;
              } else {
                 $Wynik = array();
            }
            //
            $Wynik[ $typ . '-' . $id ] = $TablicaWyniku;
            //
            $GLOBALS['cache']->zapisz('CechyFiltr_' . $_SESSION['domyslnyJezyk']['kod'], $Wynik, CACHE_PRODUKTY, true);
            //                
        
        } else {
         
            if ( isset($WynikCache[ $typ . '-' . $id ]) ) {
                //
                $TablicaWyniku = $WynikCache[ $typ . '-' . $id ];
                //
            }
            //             
          
        }

        return $TablicaWyniku;
    }
    
    // zwraca tablice z dodatkowymi polami dla produktow dla danych id kategorii lub producenta
    static public function FiltrDodatkowePola( $id = 0, $typ = 'kategoria' ) {   
        //
        $TablicaWyniku = array();
        //
        // cachowanie filtrow cech
        $WynikCache = $GLOBALS['cache']->odczytaj('DodatkowePolaFiltr_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_PRODUKTY, true);
        //
        if ( !$WynikCache || !is_array($WynikCache) || (is_array($WynikCache) && !isset($WynikCache[ $typ . '-' . $id ])) ) {
            //                 
            $TablicaPola = "SELECT products_extra_fields_id AS IdPola, 
                                   products_extra_fields_name AS NazwaPola,
                                   products_extra_fields_number as FormatPola
                              FROM products_extra_fields 
                             WHERE products_extra_fields_status = '1' AND
                                   products_extra_fields_filter = '1' AND
                                   (languages_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' OR languages_id = '0')
                          ORDER BY products_extra_fields_order";

            $TablicaPolaWartosci = "SELECT DISTINCT pepf.products_extra_fields_id AS IdPola, 
                                                    pepf.products_id AS IdProduktu, 
                                                    pepf.products_extra_fields_value AS WartoscNr1, 
                                                    pepf.products_extra_fields_value_1 AS WartoscNr2, 
                                                    pepf.products_extra_fields_value_2 AS WartoscNr3, 
                                                    pef.products_extra_fields_order AS SortPola,
                                                    pef.products_extra_fields_number AS FormatPola
                                               FROM products_to_products_extra_fields pepf
                                         INNER JOIN products_extra_fields pef ON pef.products_extra_fields_id = pepf.products_extra_fields_id
                                              WHERE pepf.products_extra_fields_id in (
                                                        SELECT products_extra_fields_id 
                                                          FROM products_extra_fields 
                                                         WHERE products_extra_fields_status = '1' AND
                                                               products_extra_fields_filter = '1' AND
                                                               (languages_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' OR languages_id = '0') ) AND
                                                               pepf.products_id in (" . Filtry::IdProduktowDlaFiltrow($typ, $id) . ")
                                           ORDER BY pef.products_extra_fields_order, IdProduktu";

            $sql = $GLOBALS['db']->open_query($TablicaPolaWartosci);

            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
                //
                $TablicaWynikuIlosc = array();
                //
                // tworzenie tablicy z wartosciami pol          
                while ($info = $sql->fetch_assoc()) {
                    //
                    if ( (int)$info['FormatPola'] == 0 ) {
                          //
                          for ( $r = 1; $r < 4; $r++ ) {
                                //
                                if ( !empty($info['WartoscNr' . $r]) ) {
                                     //
                                     // sprawdza czy juz jest taka wartosc zeby nie robic duplikatow - wartosc nr 1
                                     if ( (isset($TablicaWyniku[ $info['IdPola'] ]) && !in_array($info['WartoscNr' . $r], $TablicaWyniku[ $info['IdPola'] ])) || !isset($TablicaWyniku[ $info['IdPola'] ]) ) {
                                           //                      
                                           $TablicaWyniku[ $info['IdPola'] ][ $info['IdProduktu'] . '-' . $r ] = $info['WartoscNr' . $r]; 
                                           //
                                     }
                                     //
                                     if ( POKAZUJ_FILTRY_ILOSCI == 'tak' ) {                        
                                          //
                                          $TablicaWynikuIlosc[ base64_encode($info['IdProduktu'] . '-' . $info['IdPola'] . '-' . $info['WartoscNr' . $r]) ] = $info['IdPola'] . '-' . $info['WartoscNr' . $r];
                                          //
                                     }
                                     //             
                                }
                                //
                          }
                          //
                    } else {
                          //
                          $TablicaWyniku[ $info['IdPola'] ][ '99999999' ] = 0; 
                          $TablicaWyniku[ $info['IdPola'] ][ 'liczba' ] = 'tak'; 
                          //
                    }
                    //
                }
                //
                $GLOBALS['db']->close_query($sql);
                unset($info); 
          
                // usuwanie duplikatow
                $TablicaWyniku_tmp = array();
                
                foreach($TablicaWyniku as $k => $v) {
                    natcasesort($v);
                    $TablicaWyniku_tmp[$k] = $v;
                }
                unset($TablicaWyniku);
                $TablicaWyniku = $TablicaWyniku_tmp;

                if ( POKAZUJ_FILTRY_ILOSCI == 'tak' ) {

                    // wstawianie ilosci produktow w polach
                    $TablicaWynikuIlosc = array_count_values($TablicaWynikuIlosc);
                    //
                    foreach ( $TablicaWyniku as $Klucz => $Wartosc ) {
                        //
                        foreach ( $Wartosc as $KluczProd => $TabTmp ) {
                            //
                            if ( isset($TablicaWynikuIlosc[ $Klucz . '-' . $TabTmp ]) ) {
                                 //
                                 $TablicaWyniku[ $Klucz ][ $KluczProd ] = array( $TabTmp . ((POKAZUJ_FILTRY_ILOSCI == 'tak') ? ' (' . $TablicaWynikuIlosc[ $Klucz . '-' . $TabTmp ] . ')' : ''), '', $TabTmp );
                                 //
                            }
                            //
                        }
                        //
                    }
                    
                }

                // cache zapytania
                $WynikCachePola = $GLOBALS['cache']->odczytaj('DodatkowePolaNazwy_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);
                
                if ( !$WynikCachePola ) {
                    //
                    $sql = $GLOBALS['db']->open_query($TablicaPola);
                    //           
                    $Nazwy = array();
                    while ($info = $sql->fetch_assoc()) {
                        $Nazwy[ $info['IdPola'] ] = $info['NazwaPola'];
                    }
                    //
                    $GLOBALS['cache']->zapisz('DodatkowePolaNazwy_' . $_SESSION['domyslnyJezyk']['kod'], $Nazwy, CACHE_INNE);
                    //
                    $GLOBALS['db']->close_query($sql);
                    unset($info);  
                    //     
                  } else {
                    //
                    $Nazwy = $WynikCachePola;
                    //
                }
                //
                unset($info, $WynikCachePola);             
                // 
                foreach ( $Nazwy as $Id => $Nazwa ) {
                    $TablicaWyniku[ $Id ][ 'nazwa' ] = $Nazwa; 
                }

            }
        
            unset($TablicaPola, $TablicaPolaWartosci, $TablicaWyniku_tmp, $TablicaWynikuIlosc);
    
            if ( is_array($WynikCache) ) {
                 $Wynik = $WynikCache;
              } else {
                 $Wynik = array();
            }
            //
            $Wynik[ $typ . '-' . $id ] = $TablicaWyniku;
            //
            $GLOBALS['cache']->zapisz('DodatkowePolaFiltr_' . $_SESSION['domyslnyJezyk']['kod'], $Wynik, CACHE_PRODUKTY, true);
            //                
        
        } else {
         
            if ( isset($WynikCache[ $typ . '-' . $id ]) ) {
                //
                $TablicaWyniku = $WynikCache[ $typ . '-' . $id ];
                //
            }
            //             
          
        }        

        return $TablicaWyniku;
    }    
    
    // generuje selecty dla tablicy z w/w funkcji
    static public function FiltrSelect( $tablica, $prefix = '' ) { 
        //
        $DoWyniku = '';
        //
        foreach ($tablica as $klucz => $wartosc) {
            //
            // jezeli cecha ma jakies wartosci
            if ( count($wartosc) > 1 ) {

                if ( isset($wartosc['liczba']) ) {
                  
                     $DoWyniku .= '<div class="FiltryPolaNumeryczne">';
                  
                     $DoWyniku .= '<div class="ZakresNumeryczny">';
                     $DoWyniku .= '<label class="formSpan" for="' . $prefix . $klucz . '-o"><b>' . $wartosc['nazwa'] . ' ' . $GLOBALS['tlumacz']['ZAKRES_OD'] . '</b></label> ';
                     $DoWyniku .= '<input type="text" size="4" value="' . ((isset($_GET[$prefix . $klucz . '-o']) && (float)$_GET[$prefix . $klucz . '-o'] >= 0) ? (float)$_GET[$prefix . $klucz . '-o'] : '') . '" class="ulamek" id="' . $prefix . $klucz . '-o" name="' . $prefix . $klucz . '-o"> <label class="formSpan" for="' . $prefix . $klucz . '-d">' . $GLOBALS['tlumacz']['ZAKRES_DO'] . '</label> ';
                     $DoWyniku .= '<input type="text" size="4" value="' . ((isset($_GET[$prefix . $klucz . '-d']) && (float)$_GET[$prefix . $klucz . '-d'] >= 0) ? (float)$_GET[$prefix . $klucz . '-d'] : '') . '" class="ulamek" id="' . $prefix . $klucz . '-d" name="' . $prefix . $klucz . '-d">';
                     $DoWyniku .= '</div>';
                  
                     if ( isset($_GET[$prefix . $klucz . '-o']) && (float)$_GET[$prefix . $klucz . '-o'] >= 0 ) {
                          //
                          Filtry::FiltryAktywne( array('o', $prefix . $klucz, (float)$_GET[$prefix . $klucz . '-o'] ) );
                          //
                     }
                     if ( isset($_GET[$prefix . $klucz . '-d']) && (float)$_GET[$prefix . $klucz . '-d'] >= 0 ) {
                          //
                          Filtry::FiltryAktywne( array('d', $prefix . $klucz, (float)$_GET[$prefix . $klucz . '-d'] ) );
                          //
                     }
                     
                     $DoWyniku .= '</div>';
                  
                } else {
                    //
                    $DoWyniku .= '<div class="Multi Filtry' . (($prefix == 'c') ? 'Cechy' . (($wartosc['obrazek'] == 'tak') ? 'Obrazek' : 'Tekst') : 'Pola') . '">';
                    //
                    $ZaznaczonePozycje = array();
                    //
                    if ( isset($_GET[$prefix . $klucz]) ) {
                         //
                         $ZaznaczonePozycje = Filtry::WyczyscFiltr($_GET[$prefix . $klucz], true);
                         //
                         if ( count($ZaznaczonePozycje) == 1 && $ZaznaczonePozycje[0] == -1 ) {
                              $ZaznaczonePozycje = array();
                         }
                         //
                    }
                    
                    if (count($ZaznaczonePozycje) > 0) {
                        $DoWyniku .= '<span class="FiltrNaglowek" tabindex="0" role="button" aria-expanded="false" aria-controls="wybor_inne_' . $prefix . '_' . $klucz . '" arial-label="' . $GLOBALS['tlumacz']['FILTR'] . ' - ' . str_replace('"','',$wartosc['nazwa']) . '"><b class="Wlaczony">' . $wartosc['nazwa'] . '</b></span>';
                        
                      } else {
                        $DoWyniku .= '<span class="FiltrNaglowek" tabindex="0" role="button" aria-expanded="false" aria-controls="wybor_inne_' . $prefix . '_' . $klucz . '" arial-label="' . $GLOBALS['tlumacz']['FILTR'] . ' - ' . str_replace('"','',$wartosc['nazwa']) . '"><b>' . $wartosc['nazwa'] . '</b></span>';
                    }
                    //
                    $DoWyniku .= '<ul class="Wybor" id="wybor_inne_' . $prefix . '_' . $klucz . '" tabindex="-1">';
                    //
                    foreach ($wartosc as $kluczWartosci => $nazwaWartosci) {
                        //
                        $TmpPodzial = explode('-', (string)$kluczWartosci);
                        //                 
                        if ( isset($TmpPodzial[0]) && (int)$TmpPodzial[0] > 0 ) {
                             //
                             //$TabTmp[] = array('id' => $kluczWartosci, 'text' => ((is_array($nazwaWartosci)) ? $nazwaWartosci[0] : $nazwaWartosci)); 
                             //
                             $Wlacz = '';
                             $WlaczLabel = '';
                             //
                             if ( in_array($kluczWartosci, $ZaznaczonePozycje) ) {
                                  //
                                  $Wlacz = 'checked="checked"';
                                  $WlaczLabel = ' class="Wlaczony"';
                                  //
                                  Filtry::FiltryAktywne( array($kluczWartosci, $prefix . $klucz, ((is_array($nazwaWartosci)) ? $nazwaWartosci[2] : $nazwaWartosci)) );
                                  //                                 
                             }
                             //
                             $DoWyniku .= '<li>';
                             
                             // jezeli filtr jest obrazkowy
                             if ( isset($wartosc['obrazek']) && $wartosc['obrazek'] == 'tak' && is_array($nazwaWartosci) ) {
                                 //
                                 $DoWyniku .= '<div>' . Funkcje::pokazObrazek($nazwaWartosci[1], $nazwaWartosci[0], SZEROKOSC_OBRAZEK_FILTRY, WYSOKOSC_OBRAZEK_FILTRY, array(), '', 'maly', true, false, false) . '</div>';
                                 //
                             }
                             
                             $DoWyniku .= '<input type="checkbox" id="filtr_' . $prefix . $klucz . '_' . $kluczWartosci . '" name="' . $prefix . $klucz . '[' . $kluczWartosci . ']" ' . $Wlacz . ' /> <label role="button" tabindex="0" aria-label="' . $GLOBALS['tlumacz']['FILTR'] . ' - ' . preg_replace('/\s*\([^\)]+\)$/', '', ((is_array($nazwaWartosci)) ? str_replace('"','',$wartosc['nazwa'] . ': ' . $nazwaWartosci[0]) : str_replace('"','',$wartosc['nazwa'] . ': ' . $nazwaWartosci))) . '" id="label_filtr_' . $prefix . $klucz . '_' . $kluczWartosci . '" for="filtr_' . $prefix . $klucz . '_' . $kluczWartosci . '"' . $WlaczLabel . '><a data-id="filtr_' . $prefix . $klucz . '_' . $kluczWartosci . '">' . ((is_array($nazwaWartosci)) ? $nazwaWartosci[0] : $nazwaWartosci) . '</a></label></li>';                        
                            
                        }
                        //
                        unset($TmpPodzial);
                        //
                    }
                    //
                    $DoWyniku .= '</ul>';
                    $DoWyniku .= '</div>';
                    
                }
                //
                
                unset($Wlacz, $ZaznaczonePozycje);
                //
            }
        }
        //
        return $DoWyniku;
    }
    
    // generuje select z producentami dla danych kategorii id
    static public function FiltrProducentaSelect( $id, $typ = '' ) { 
    
        $DoWyniku = '';
        //    
        $data = date('Y-m-d');
        
        $WstawTyp = "p2c.categories_id in (" . $id . ")";
        switch ($typ) {
            case 'polecane':
                $WstawTyp = "p.featured_status = '1'";
                break;
            case 'nowosci':
                $WstawTyp = "p.new_status = '1'";
                break;   
            case 'promocje':
                $WstawTyp = "p.specials_status = '1' AND (p.specials_date = '0000-00-00 00:00:00' OR now() > p.specials_date) AND (p.specials_date_end = '0000-00-00 00:00:00' OR now() < p.specials_date_end)";
                break; 
            case 'wyprzedaz':
                $WstawTyp = "p.sale_status = '1' AND p.specials_status = '0'";
                break;                     
            case 'hity':
                $WstawTyp = "p.star_status = '1'";
                break; 
            case 'bestsellery':
                $WstawTyp = "p.products_ordered > 0";
                break;   
            case 'oczekiwane':
                $WstawTyp = "p.products_date_available > '" . $data . "'";
                break;                 
        }

        unset($data);
        //

        // jezeli nie ma id produktow 
        $zapytanie = "SELECT DISTINCT m.manufacturers_id, 
                                      m.manufacturers_name
                                      " . ((POKAZUJ_FILTRY_ILOSCI == 'tak') ? ", GROUP_CONCAT(DISTINCT p.products_id) as IdProduktow" : "") . "
                                  FROM products p
                            INNER JOIN products_to_categories p2c ON p.products_id = p2c.products_id
                            INNER JOIN manufacturers m ON p.manufacturers_id = m.manufacturers_id
                                 WHERE p.products_status = '1'
                                   AND p.listing_status = '0'
                                   " . (LISTING_PRODUKTY_ZERO == 'nie' ? 'AND p.products_quantity > 0 ' : '') . "
                                   " . $GLOBALS['warunekProduktu'] . " AND
                                   " . $WstawTyp . "
                              GROUP BY m.manufacturers_id
                              ORDER BY m.manufacturers_name";

        $sql = $GLOBALS['db']->open_query($zapytanie);
        
        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
            //
            $DoWyniku .= '<div class="Multi FiltryProducent">';
            //
            
            $ZaznaczonePozycje = array();
            if (isset($_GET['producent'])) {
                $ZaznaczonePozycje = Filtry::WyczyscFiltr($_GET['producent']);
                //
                if ( count($ZaznaczonePozycje) == 1 && $ZaznaczonePozycje[0] == -1 ) {
                     $ZaznaczonePozycje = array();
                }
                //                    
            }                
            
            if (count($ZaznaczonePozycje) > 0) {
                $DoWyniku .= '<span class="FiltrNaglowek" tabindex="0" role="button" aria-expanded="false" aria-controls="wybor_producent" arial-label="' . $GLOBALS['tlumacz']['FILTR'] . ' - ' . $GLOBALS['tlumacz']['PRODUCENT'] . '"><b class="Wlaczony">' . $GLOBALS['tlumacz']['PRODUCENT'] . '</b></span>';
              } else {
                $DoWyniku .= '<span class="FiltrNaglowek" tabindex="0" role="button" aria-expanded="false" aria-controls="wybor_producent" arial-label="' . $GLOBALS['tlumacz']['FILTR'] . ' - ' . $GLOBALS['tlumacz']['PRODUCENT'] . '"><b>' . $GLOBALS['tlumacz']['PRODUCENT'] . '</b></span>';
            }
            //
            $DoWyniku .= '<ul class="Wybor" id="wybor_producent" tabindex="-1">';
            //
            while ($info = $sql->fetch_assoc()) {
              
                if ( !empty($info['manufacturers_name']) ) {
                    //
                    $Wlacz = '';
                    $WlaczLabel = '';
                    if (in_array($info['manufacturers_id'], $ZaznaczonePozycje)) {
                        $Wlacz = 'checked="checked"';
                        $WlaczLabel = ' class="Wlaczony"';
                        //
                        Filtry::FiltryAktywne( array($info['manufacturers_id'], 'producent', $info['manufacturers_name']) );
                        //
                    }
                    //
                    $DoWyniku .= '<li><input type="checkbox" id="filtr_producent_' . $info['manufacturers_id'] . '" name="producent[' . $info['manufacturers_id'] . ']" ' . $Wlacz . ' /> <label role="button" tabindex="0" aria-label="' . $GLOBALS['tlumacz']['FILTR'] . ' - ' . str_replace('"','',$GLOBALS['tlumacz']['PRODUCENT'] . ': ' . $info['manufacturers_name']) . '" id="label_filtr_producent_' . $info['manufacturers_id'] . '" for="filtr_producent_' . $info['manufacturers_id'] . '"' . $WlaczLabel . '><a data-id="filtr_producent_' . $info['manufacturers_id'] . '">' . $info['manufacturers_name'] . ((POKAZUJ_FILTRY_ILOSCI == 'tak') ? ' (' . count(explode(',', (string)$info['IdProduktow'])) . ')' : '') . '</a></label></li>';
                    //
                }
                
            }
            
            $DoWyniku .= '</ul>';
            $DoWyniku .= '</div>';
            unset($Wlacz, $ZaznaczonePozycje);
            //
        }
        //
        unset($zapytanie);
        $GLOBALS['db']->close_query($sql);
        //
        return $DoWyniku;
    }      

    // generuje select z kategoriami dla danego producenta id
    static public function FiltrKategoriiSelect( $id, $typ = '' ) {  
    
        $DoWyniku = '';
        //    
        $data = date('Y-m-d');
        
        $WstawTyp = "p.manufacturers_id = '" . $id . "'";
        switch ($typ) {
            case 'polecane':
                $WstawTyp = "p.featured_status = '1'";
                break;
            case 'nowosci':
                $WstawTyp = "p.new_status = '1'";
                break; 
            case 'promocje':
                $WstawTyp = "p.specials_status = '1' AND (p.specials_date = '0000-00-00 00:00:00' OR now() > p.specials_date) AND (p.specials_date_end = '0000-00-00 00:00:00' OR now() < p.specials_date_end)";
                break;   
            case 'wyprzedaz':
                $WstawTyp = "p.sale_status = '1' AND p.specials_status = '0'";
                break;                        
            case 'hity':
                $WstawTyp = "p.star_status = '1'";
                break; 
            case 'bestsellery':
                $WstawTyp = "p.products_ordered > 0";
                break;    
            case 'oczekiwane':
                $WstawTyp = "p.products_date_available > '" . $data . "'";
                break;                
        }    

        unset($data);        
        //      
        $zapytanie = "SELECT DISTINCT c.categories_id
                                      " . ((POKAZUJ_FILTRY_ILOSCI == 'tak') ? ", GROUP_CONCAT(DISTINCT p.products_id) AS IdProduktow" : "") . "
                                 FROM products p
                           INNER JOIN products_to_categories p2c ON p.products_id = p2c.products_id
                           INNER JOIN categories c ON p2c.categories_id = c.categories_id
                                WHERE p.products_status = '1' AND
                                      p.listing_status = '0'
                                      " . (LISTING_PRODUKTY_ZERO == 'nie' ? 'AND p.products_quantity > 0 ' : '') . "
                                      " . $GLOBALS['warunekProduktu'] . " AND
                                      c.categories_status = '1' AND
                                      c.categories_view = '1' AND
                                      " . $WstawTyp . "
                                  GROUP BY c.categories_id
                                  ORDER BY c.parent_id, c.sort_order";

        $sql = $GLOBALS['db']->open_query($zapytanie);
        
        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
            //
            $DoWyniku .= '<div class="Multi FiltryKategoria">';
            //
            
            $ZaznaczonePozycje = array();
            if (isset($_GET['kategoria'])) {
                $ZaznaczonePozycje = Filtry::WyczyscFiltr($_GET['kategoria']);
                //
                if ( count($ZaznaczonePozycje) == 1 && $ZaznaczonePozycje[0] == -1 ) {
                     $ZaznaczonePozycje = array();
                }
                //                    
            }                
            
            if (count($ZaznaczonePozycje) > 0) {
                $DoWyniku .= '<span class="FiltrNaglowek" tabindex="0" role="button" aria-expanded="false" aria-controls="wybor_kategoria" arial-label="' . $GLOBALS['tlumacz']['FILTR'] . ' - ' . $GLOBALS['tlumacz']['KATEGORIA'] . '"><b class="Wlaczony">' . $GLOBALS['tlumacz']['KATEGORIA'] . '</b></span>';
              } else {
                $DoWyniku .= '<span class="FiltrNaglowek" tabindex="0" role="button" aria-expanded="false" aria-controls="wybor_kategoria" arial-label="' . $GLOBALS['tlumacz']['FILTR'] . ' - ' . $GLOBALS['tlumacz']['KATEGORIA'] . '"><b>' . $GLOBALS['tlumacz']['KATEGORIA'] . '</b></span>';
            }
            //
            $DoWyniku .= '<ul class="Wybor" id="wybor_kategoria" tabindex="-1">';
            //
            while ($info = $sql->fetch_assoc()) {
                //
                $Wlacz = '';
                $WlaczLabel = '';
                if (in_array($info['categories_id'], $ZaznaczonePozycje)) {
                    $Wlacz = 'checked="checked"';
                    $WlaczLabel = ' class="Wlaczony"';
                    //
                    Filtry::FiltryAktywne( array($info['categories_id'], 'kategoria', Kategorie::SciezkaKategoriiId($info['categories_id'], 'nazwy', ' / ')) );
                    //                        
                }
                //
                $NazwaTmpKat = Kategorie::SciezkaKategoriiId($info['categories_id'], 'nazwy', ' / ');
                $DoWyniku .= '<li><input type="checkbox" id="filtr_kategoria_' . $info['categories_id'] . '" name="kategoria[' . $info['categories_id'] . ']" ' . $Wlacz . ' /> <label role="button" tabindex="0" aria-label="' . $GLOBALS['tlumacz']['FILTR'] . ' - ' . str_replace('"','',$GLOBALS['tlumacz']['KATEGORIA'] . ': ' . $NazwaTmpKat) . '" id="label_filtr_kategoria_' . $info['categories_id'] . '" for="filtr_kategoria_' . $info['categories_id'] . '"' . $WlaczLabel . '><a data-id="filtr_kategoria_' . $info['categories_id'] . '">' . $NazwaTmpKat . ((POKAZUJ_FILTRY_ILOSCI == 'tak') ? ' (' . count(explode(',', (string)$info['IdProduktow'])) . ')' : '') . '</a></label></li>';
                unset($NazwaTmpKat);
                //
            }
            $DoWyniku .= '</ul>';
            $DoWyniku .= '</div>';
            unset($Wlacz, $ZaznaczonePozycje);
            //
        }        
        
        unset($zapytanie);
        $GLOBALS['db']->close_query($sql);

        return $DoWyniku;
    }     

    // generuje select filtrem nowosci
    static public function FiltrNowosciSelect( $id = 0, $typ = 'kategoria' ) { 
    
        // okreslanie ilosci nowosci
        $IloscNowosci = 0;
        
        $WynikCache = $GLOBALS['cache']->odczytaj('NowosciFiltr', CACHE_PRODUKTY, true);
        
        if ( !$WynikCache || !is_array($WynikCache) || (is_array($WynikCache) && !isset($WynikCache[ $typ . '-' . $id ])) ) {
            //
            $IloscNowosci = 0;
            //
            if ( POKAZUJ_FILTRY_ILOSCI == 'tak' ) {
                //
                $IloscNowosci = Filtry::FiltrNowosciIlosc( $id, $typ );
                //
            }
            //
            if ( is_array($WynikCache) ) {
                 $Nowosci = $WynikCache;
              } else {
                 $Nowosci = array();
            }
            //
            $Nowosci[ $typ . '-' . $id ] = $IloscNowosci;
            //
            $GLOBALS['cache']->zapisz('NowosciFiltr', $Nowosci, CACHE_PRODUKTY, true);
            //
        } else {
            //
            $Nowosci = $WynikCache;
            //
            if ( isset($Nowosci[ $typ . '-' . $id ]) ) {
                //
                $IloscNowosci = $Nowosci[ $typ . '-' . $id ];
                //
            }
            //
        }
        
        unset($WynikCache, $Nowosci);
             
        $DoWyniku = '<div class="Multi FiltryNowosci">';
        //
        $ZaznaczonaPozycja = '';
        if (isset($_GET['nowosci']) && $_GET['nowosci'] == 'tak') {
            $DoWyniku .= '<span class="FiltrNaglowek" tabindex="0" role="button" aria-expanded="false" aria-controls="wybor_nowosci" arial-label="' . $GLOBALS['tlumacz']['FILTR'] . ' - ' . $GLOBALS['tlumacz']['LISTING_TYLKO_NOWOSCI'] . '"><b class="Wlaczony">' . $GLOBALS['tlumacz']['LISTING_TYLKO_NOWOSCI'] . '</b></span>';
            $ZaznaczonaPozycja = $_GET['nowosci'];
          } else {
            $DoWyniku .= '<span class="FiltrNaglowek" tabindex="0" role="button" aria-expanded="false" aria-controls="wybor_nowosci" arial-label="' . $GLOBALS['tlumacz']['FILTR'] . ' - ' . $GLOBALS['tlumacz']['LISTING_TYLKO_NOWOSCI'] . '"><b>' . $GLOBALS['tlumacz']['LISTING_TYLKO_NOWOSCI'] . '</b></span>';
        }
        //
        $DoWyniku .= '<ul class="Wybor" id="wybor_nowosci" tabindex="-1">';
        //
        $DoWyniku .= '<li><input type="checkbox" name="nowosci" id="filtr_nowosci" value="tak" ' . (($ZaznaczonaPozycja != '') ? 'checked="checked"' : '') . ' /> <label role="button" tabindex="0" aria-label="' . $GLOBALS['tlumacz']['FILTR'] . ' - ' . str_replace('"','',$GLOBALS['tlumacz']['LISTING_TYLKO_NOWOSCI'] . ': ' . $GLOBALS['tlumacz']['TAK']) . '" id="label_filtr_nowosci" for="filtr_nowosci"' . (($ZaznaczonaPozycja != '') ? ' class="Wlaczony"' : '') . '><a data-id="filtr_nowosci">' . $GLOBALS['tlumacz']['TAK'] . ((POKAZUJ_FILTRY_ILOSCI == 'tak') ? ' (' . $IloscNowosci . ')' : '') . '</a></label></li>';
        //
        $DoWyniku .= '</ul>';
        $DoWyniku .= '</div>';
        unset($ZaznaczonaPozycja);    
        
        return $DoWyniku;
    }   
    
    static private function FiltrNowosciIlosc( $id = 0, $typ = 'kategoria' ) { 
        //
        $ZapytanieWarunkowe = Produkty::SqlNowosciProste( 'AND p.products_id in (' . Filtry::IdProduktowDlaFiltrow($typ, $id) . ')' );
        $sql = $GLOBALS['db']->open_query($ZapytanieWarunkowe);
        //
        $IloscNowosci = (int)$GLOBALS['db']->ile_rekordow($sql);
        //
        $GLOBALS['db']->close_query($sql);
        //   
        return $IloscNowosci;
        //
    }

    // generuje select filtrem promocji
    static public function FiltrPromocjeSelect( $id = 0, $typ = 'kategoria' ) { 
    
        // okreslanie ilosci promocji
        $IloscPromocji = 0;
        
        $WynikCache = $GLOBALS['cache']->odczytaj('PromocjeFiltr', CACHE_PRODUKTY, true);
        
        if ( !$WynikCache || !is_array($WynikCache) || (is_array($WynikCache) && !isset($WynikCache[ $typ . '-' . $id ])) ) {
            //
            $IloscPromocji = 0;
            //
            if ( POKAZUJ_FILTRY_ILOSCI == 'tak' ) {
                //
                $IloscPromocji = Filtry::FiltrPromocjeIlosc( $id, $typ );
                //
            }
            //
            if ( is_array($WynikCache) ) {
                 $Promocje = $WynikCache;
              } else {
                 $Promocje = array();
            }
            //
            $Promocje[ $typ . '-' . $id ] = $IloscPromocji;
            //
            $GLOBALS['cache']->zapisz('PromocjeFiltr', $Promocje, CACHE_PRODUKTY, true);
            //
        } else {
            //
            $Promocje = $WynikCache;
            //
            if ( isset($Promocje[ $typ . '-' . $id ]) ) {
                //
                $IloscPromocji = $Promocje[ $typ . '-' . $id ];
                //
            }
            //
        }

        unset($WynikCache, $Promocje);    
           
        $DoWyniku = '<div class="Multi FiltryPromocje">';
        //
        $ZaznaczonaPozycja = '';
        if (isset($_GET['promocje']) && $_GET['promocje'] == 'tak') {
            $DoWyniku .= '<span class="FiltrNaglowek" tabindex="0" role="button" aria-expanded="false" aria-controls="wybor_promocje" arial-label="' . $GLOBALS['tlumacz']['FILTR'] . ' - ' . $GLOBALS['tlumacz']['LISTING_TYLKO_PROMOCJE'] . '"><b class="Wlaczony">' . $GLOBALS['tlumacz']['LISTING_TYLKO_PROMOCJE'] . '</b></span>';
            $ZaznaczonaPozycja = $_GET['promocje'];
          } else {
            $DoWyniku .= '<span class="FiltrNaglowek" tabindex="0" role="button" aria-expanded="false" aria-controls="wybor_promocje" arial-label="' . $GLOBALS['tlumacz']['FILTR'] . ' - ' . $GLOBALS['tlumacz']['LISTING_TYLKO_PROMOCJE'] . '"><b>' . $GLOBALS['tlumacz']['LISTING_TYLKO_PROMOCJE'] . '</b></span>';
        }
        //
        $DoWyniku .= '<ul class="Wybor" id="wybor_promocje" tabindex="-1">';
        //
        $DoWyniku .= '<li><input type="checkbox" name="promocje" id="filtr_promocje" value="tak" ' . (($ZaznaczonaPozycja != '') ? 'checked="checked"' : '') . ' /> <label role="button" tabindex="0" aria-label="' . $GLOBALS['tlumacz']['FILTR'] . ' - ' . str_replace('"','',$GLOBALS['tlumacz']['LISTING_TYLKO_PROMOCJE'] . ': ' . $GLOBALS['tlumacz']['TAK']) . '" id="label_filtr_promocje" for="filtr_promocje"' . (($ZaznaczonaPozycja != '') ? ' class="Wlaczony"' : '') . '><a data-id="filtr_promocje">' . $GLOBALS['tlumacz']['TAK'] . ((POKAZUJ_FILTRY_ILOSCI == 'tak') ? ' (' . $IloscPromocji . ')' : '') . '</a></label></li>';
        //
        $DoWyniku .= '</ul>';
        $DoWyniku .= '</div>';
        unset($ZaznaczonaPozycja);  

        return $DoWyniku;
    }     
    
    static private function FiltrPromocjeIlosc( $id = 0, $typ = 'kategoria' ) { 
        //
        $ZapytanieWarunkowe = Produkty::SqlPromocjeProste( 'AND p.products_id in (' . Filtry::IdProduktowDlaFiltrow($typ, $id) . ')' );
        $sql = $GLOBALS['db']->open_query($ZapytanieWarunkowe);
        //
        $IloscPromocji = (int)$GLOBALS['db']->ile_rekordow($sql);
        //
        $GLOBALS['db']->close_query($sql);
        //   
        return $IloscPromocji;
        //
    }    
    
    // generuje select z dostepnosciami
    static public function FiltrDostepnoscSelect( $id = 0, $typ = 'kategoria' ) {  
    
        $DoWyniku = '';
        //    
        $TablicaWyniku = array();
        //
        $WynikCache = $GLOBALS['cache']->odczytaj('DostepnosciFiltr', CACHE_PRODUKTY, true);
        
        if ( !$WynikCache || !is_array($WynikCache) || (is_array($WynikCache) && !isset($WynikCache[ $typ . '-' . $id ])) ) { 
        
            //
            $data = date('Y-m-d');
            
            $WstawTyp = "";
            switch ($typ) {
                case 'polecane':
                    $WstawTyp = "AND p.featured_status = '1'";
                    break;
                case 'nowosci':
                    $WstawTyp = "AND p.new_status = '1'";
                    break; 
                case 'promocje':
                    $WstawTyp = "AND p.specials_status = '1' AND (p.specials_date = '0000-00-00 00:00:00' OR now() > p.specials_date) AND (p.specials_date_end = '0000-00-00 00:00:00' OR now() < p.specials_date_end)";
                    break;  
                case 'wyprzedaz':
                    $WstawTyp = "AND p.sale_status = '1' AND p.specials_status = '0'";
                    break;                           
                case 'hity':
                    $WstawTyp = "AND p.star_status = '1'";
                    break; 
                case 'bestsellery':
                    $WstawTyp = "AND p.products_ordered > 0";
                    break;    
                case 'oczekiwane':
                    $WstawTyp = "AND p.products_date_available > '" . $data . "'";
                    break;                
            }    

            unset($data);        
            //      
            $zapytanie = "SELECT DISTINCT p.products_availability_id,
                                          GROUP_CONCAT(DISTINCT CONCAT(p.products_id, ';', p.products_quantity)) AS IdProduktow
                                     FROM products p
                               INNER JOIN products_to_categories p2c ON p.products_id = p2c.products_id
                               INNER JOIN categories c ON p2c.categories_id = c.categories_id
                                   WHERE p.products_status = '1' AND 
                                         p.listing_status = '0'
                                          " . (LISTING_PRODUKTY_ZERO == 'nie' ? 'AND p.products_quantity > 0 ' : '') . "
                                          " . $GLOBALS['warunekProduktu'] . " AND 
                                          c.categories_status = '1' AND 
                                          c.categories_view = '1' AND 
                                          p.products_availability_id > 0 AND 
                                          p.products_id IN (" . Filtry::IdProduktowDlaFiltrow($typ, $id) . ")
                                          " . $WstawTyp . "
                                      GROUP BY p.products_availability_id";

            $sql = $GLOBALS['db']->open_query($zapytanie);
            
            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
                //                  
                $TablicaWynikuAutomatyczne = array();
                //
                while ($info = $sql->fetch_assoc()) {
                    //
                    // jezeli jest automatyczna to sprawdzi jaki ma id w zaleznosci od ilosci produktu
                    if ( $info['products_availability_id'] == 99999 ) {
                         //
                         $TablicaAutomatyczna = explode(',', (string)$info['IdProduktow']);
                         //
                         foreach ( $TablicaAutomatyczna as $Produkt ) {
                            //
                            $Tmp = explode(';', (string)$Produkt);
                            //                              
                            if ( isset($Tmp[1]) ) {
                                 $TablicaWynikuAutomatyczne[] = Filtry::FiltrDostepnosciAutomatyczne( $Tmp[1] );
                            }
                            //
                            unset($Tmp);
                            //
                         }
                         //
                    } else {
                         //
                         $TablicaWyniku[ $info['products_availability_id'] ] = count(explode(',', (string)$info['IdProduktow']));
                         //
                    }
                    //
                }

                // dostepnosci automatyczne
                if ( is_array($TablicaWynikuAutomatyczne) ) { 
                     //
                     $TablicaWynikuAutomatyczne = array_count_values($TablicaWynikuAutomatyczne);
                     
                     foreach ( $TablicaWynikuAutomatyczne as $Klucz => $Ilosc ) {
                         //
                         $TablicaWyniku[ 100 + $Klucz ] = $Ilosc;
                         //
                     }
                     //
                }
                
                unset($TablicaWynikuAutomatyczne);

            }
            
            unset($zapytanie);
            $GLOBALS['db']->close_query($sql);
                
            if ( is_array($WynikCache) ) {
                 $TablicaWynikuTmp = $WynikCache;
              } else {
                 $TablicaWynikuTmp = array();
            }
            //
            $TablicaWynikuTmp[ $typ . '-' . $id ] = $TablicaWyniku;
            //
            $GLOBALS['cache']->zapisz('DostepnosciFiltr', $TablicaWynikuTmp, CACHE_PRODUKTY, true);
            //        
            unset($TablicaWynikuTmp);
            
        } else {

            $TablicaWynikuTmp = $WynikCache;
            //
            if ( isset($TablicaWynikuTmp[ $typ . '-' . $id ]) ) {
                //
                $TablicaWyniku = $TablicaWynikuTmp[ $typ . '-' . $id ];
                //
            }
            //
            unset($TablicaWynikuTmp);
            //
        }
        
        if ( count($TablicaWyniku) > 0 ) {
          
            $DoWyniku .= '<div class="Multi FiltryDostepnosc">';
            //
            
            $ZaznaczonePozycje = array();
            if (isset($_GET['dostepnosc'])) {
                $ZaznaczonePozycje = Filtry::WyczyscFiltr($_GET['dostepnosc']);
                //
                if ( count($ZaznaczonePozycje) == 1 && $ZaznaczonePozycje[0] == -1 ) {
                     $ZaznaczonePozycje = array();
                }
                //                    
            }                
            
            if (count($ZaznaczonePozycje) > 0) {
                $DoWyniku .= '<span class="FiltrNaglowek" tabindex="0" role="button" aria-expanded="false" aria-controls="wybor_dostepnosc" arial-label="' . $GLOBALS['tlumacz']['FILTR'] . ' - ' . $GLOBALS['tlumacz']['DOSTEPNOSC'] . '"><b class="Wlaczony">' . $GLOBALS['tlumacz']['DOSTEPNOSC'] . '</b></span>';
              } else {
                $DoWyniku .= '<span class="FiltrNaglowek" tabindex="0" role="button" aria-expanded="false" aria-controls="wybor_dostepnosc" arial-label="' . $GLOBALS['tlumacz']['FILTR'] . ' - ' . $GLOBALS['tlumacz']['DOSTEPNOSC'] . '"><b>' . $GLOBALS['tlumacz']['DOSTEPNOSC'] . '</b></span>';
            }
            //
            $DoWyniku .= '<ul class="Wybor" id="wybor_dostepnosc" tabindex="-1">';
            //                
            
            // tworzenie wyboru
            foreach ( $TablicaWyniku as $IdDostepnosci => $Ilosc ) {

                if ( isset($GLOBALS['dostepnosci'][ (($IdDostepnosci > 100) ? $IdDostepnosci - 100 : $IdDostepnosci) ]['dostepnosc']) ) {
                  
                    $Wlacz = '';
                    $WlaczLabel = '';
                    if (in_array($IdDostepnosci, $ZaznaczonePozycje)) {
                        $Wlacz = 'checked="checked"';
                        $WlaczLabel = ' class="Wlaczony"';
                        //
                        Filtry::FiltryAktywne( array($IdDostepnosci, 'dostepnosc', $GLOBALS['dostepnosci'][ (($IdDostepnosci > 100) ? $IdDostepnosci - 100 : $IdDostepnosci) ]['dostepnosc']) );
                        //                              
                    }
                    //
                    $DoWyniku .= '<li><input type="checkbox" id="filtr_dostepnosc_' . $IdDostepnosci . '" name="dostepnosc[' . $IdDostepnosci . ']" ' . $Wlacz . ' /> <label role="button" tabindex="0" aria-label="' . $GLOBALS['tlumacz']['FILTR'] . ' - ' . str_replace('"','',$GLOBALS['tlumacz']['DOSTEPNOSC'] . ': ' . $GLOBALS['dostepnosci'][ (($IdDostepnosci > 100) ? $IdDostepnosci - 100 : $IdDostepnosci) ]['dostepnosc']) . '" id="label_filtr_dostepnosc_' . $IdDostepnosci . '" for="filtr_dostepnosc_' . $IdDostepnosci . '"' . $WlaczLabel . '><a data-id="filtr_dostepnosc_' . $IdDostepnosci . '">' . $GLOBALS['dostepnosci'][ (($IdDostepnosci > 100) ? $IdDostepnosci - 100 : $IdDostepnosci) ]['dostepnosc'] . ((POKAZUJ_FILTRY_ILOSCI == 'tak') ? ' (' . $Ilosc . ')' : '') . '</a></label></li>';
                    //
            
                }
                
            }
            
            $DoWyniku .= '</ul>';
            $DoWyniku .= '</div>';
            unset($Wlacz, $ZaznaczonePozycje);
            //
 
        }
        
        return $DoWyniku;
    }       
    
    static private function FiltrDostepnosciAutomatyczne( $iloscProduktu ) {

        $TablicaDostepnosci = '';

        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('DostepnosciAutomatyczne', CACHE_INNE);   
        
        if ( !$WynikCache && !is_array($WynikCache) ) {

            $zapytanie = "SELECT GROUP_CONCAT(CONVERT(quantity, CHAR(8)),':', CONVERT(products_availability_id, CHAR(8)) ORDER BY quantity DESC SEPARATOR ',') as wartosc FROM products_availability WHERE mode = '1'";
            $sql = $GLOBALS['db']->open_query($zapytanie);
            
            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

                while ($info = $sql->fetch_assoc()) {
                    $TablicaDostepnosci = $info['wartosc'];
                }
                
                unset($info);
                
            }
            
            $GLOBALS['db']->close_query($sql); 
            
            unset($zapytanie, $info);
            
            $GLOBALS['cache']->zapisz('DostepnosciAutomatyczne', $TablicaDostepnosci, CACHE_INNE);

          } else {
         
            $TablicaDostepnosci = $WynikCache;
        
        }
        
        $DostepnoscId = '0';

        $TablicaTmp = explode(',', (string)$TablicaDostepnosci);
        
        if ( count( $TablicaTmp ) > 0 ) {

            $Tablica = preg_split("/[:,]/" , (string)$TablicaDostepnosci);
            
            for ( $i = 0, $c = count($Tablica); $i < $c; $i += 2 ) {
              
                if ($iloscProduktu >= $Tablica[$i]) {
                  
                    if ( isset($Tablica[$i+1]) ) {
                         $DostepnoscId = $Tablica[$i+1];
                    }
                    
                    break;
                  
                }              
              
            }
            
        }
        
        unset($WynikCache, $TablicaDostepnosci, $Tablica, $TablicaTmp);    
        
        return $DostepnoscId;
     
    }
    
    // generuje select z czasami wysylek
    static public function FiltrCzasWysylkiSelect( $id = 0, $typ = 'kategoria' ) {  
    
        $DoWyniku = '';
        //    
        $TablicaWyniku = array();
        //
        $WynikCache = $GLOBALS['cache']->odczytaj('CzasyWysylekFiltr', CACHE_PRODUKTY, true);
        
        //if ( !$WynikCache || !is_array($WynikCache) || (is_array($WynikCache) && !isset($WynikCache[ $typ . '-' . $id ])) ) { 
        if ( 1 != 2 ) {
        
            //
            $data = date('Y-m-d');
            
            $WstawTyp = "";
            switch ($typ) {
                case 'polecane':
                    $WstawTyp = "AND p.featured_status = '1'";
                    break;
                case 'nowosci':
                    $WstawTyp = "AND p.new_status = '1'";
                    break; 
                case 'promocje':
                    $WstawTyp = "AND p.specials_status = '1' AND (p.specials_date = '0000-00-00 00:00:00' OR now() > p.specials_date) AND (p.specials_date_end = '0000-00-00 00:00:00' OR now() < p.specials_date_end)";
                    break;      
                case 'wyprzedaz':
                    $WstawTyp = "AND p.sale_status = '1' AND p.specials_status = '0'";
                    break;                        
                case 'hity':
                    $WstawTyp = "AND p.star_status = '1'";
                    break; 
                case 'bestsellery':
                    $WstawTyp = "AND p.products_ordered > 0";
                    break;    
                case 'oczekiwane':
                    $WstawTyp = "AND p.products_date_available > '" . $data . "'";
                    break;                
            }    

            unset($data);        
            //      
            $zapytanie = "SELECT DISTINCT CASE 
                                          WHEN p.products_quantity > 0 THEN p.products_shipping_time_id
                                          WHEN p.products_quantity <= 0 AND p.products_shipping_time_zero_quantity_id > 0 THEN p.products_shipping_time_zero_quantity_id
                                          ELSE p.products_shipping_time_id
                                      END AS shipping_time,
                                      GROUP_CONCAT(DISTINCT p.products_id) AS IdProduktow
                                  FROM products p
                                  INNER JOIN products_to_categories p2c ON p.products_id = p2c.products_id
                                  INNER JOIN categories c ON p2c.categories_id = c.categories_id
                                  INNER JOIN products_shipping_time pst ON 
                                      pst.products_shipping_time_id = (
                                          CASE 
                                              WHEN p.products_quantity > 0 THEN p.products_shipping_time_id
                                              WHEN p.products_quantity <= 0 AND p.products_shipping_time_zero_quantity_id > 0 THEN p.products_shipping_time_zero_quantity_id
                                              ELSE p.products_shipping_time_id
                                          END
                                      )
                                  WHERE
                                      p.products_status = '1'
                                      AND p.listing_status = '0'
                                      " . (LISTING_PRODUKTY_ZERO == 'nie' ? 'AND p.products_quantity > 0 ' : '') . "
                                      " . $GLOBALS['warunekProduktu'] . "
                                      AND c.categories_status = '1'
                                      AND c.categories_view = '1'
                                      AND p.products_id IN (" . Filtry::IdProduktowDlaFiltrow($typ, $id) . ")
                                      " . $WstawTyp . "
                                  GROUP BY shipping_time
                                  ORDER BY pst.products_shipping_time_day ASC";

            $sql = $GLOBALS['db']->open_query($zapytanie);
            
            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
                //                  
                while ($info = $sql->fetch_assoc()) {
                    //
                    $TablicaWyniku[ $info['shipping_time'] ] = count(explode(',', (string)$info['IdProduktow']));
                    //
                }

            }
            
            unset($zapytanie);
            $GLOBALS['db']->close_query($sql);

            if ( is_array($WynikCache) ) {
                 $TablicaWynikuTmp = $WynikCache;
              } else {
                 $TablicaWynikuTmp = array();
            }
            //
            $TablicaWynikuTmp[ $typ . '-' . $id ] = $TablicaWyniku;
            //
            $GLOBALS['cache']->zapisz('CzasyWysylekFiltr', $TablicaWynikuTmp, CACHE_PRODUKTY, true);
            //        
            unset($TablicaWynikuTmp);
            
        } else {

            $TablicaWynikuTmp = $WynikCache;
            //
            if ( isset($TablicaWynikuTmp[ $typ . '-' . $id ]) ) {
                //
                $TablicaWyniku = $TablicaWynikuTmp[ $typ . '-' . $id ];
                //
            }
            //
            unset($TablicaWynikuTmp);
            //
        }

        if ( count($TablicaWyniku) > 0 ) {
          
            $TablicaCzasWysylki = array();
            //
            // cache zapytania
            $WynikCacheWysylka = $GLOBALS['cache']->odczytaj('ProduktCzasWysylki_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);      

            if ( !$WynikCacheWysylka && !is_array($WynikCacheWysylka) ) { 

                $zapytanie = "select s.products_shipping_time_id, s.products_shipping_time_day, sd.products_shipping_time_name from products_shipping_time s, products_shipping_time_description sd where s.products_shipping_time_id = sd.products_shipping_time_id and sd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' order by s.products_shipping_time_day";
                $sqls = $GLOBALS['db']->open_query($zapytanie);
                //
                if ((int)$GLOBALS['db']->ile_rekordow($sqls) > 0) {
                    //
                    while ($infs = $sqls->fetch_assoc()) {
                        //
                        $TablicaCzasWysylki[$infs['products_shipping_time_id']] = array( 'nazwa' => $infs['products_shipping_time_name'],
                                                                                         'ilosc_dni' => $infs['products_shipping_time_day'] );
                        //
                    }
                    //
                    unset($infs);
                    //
                }
                //
                $GLOBALS['db']->close_query($sqls);  
                unset($zapytanie);
                
                $GLOBALS['cache']->zapisz('ProduktCzasWysylki_' . $_SESSION['domyslnyJezyk']['kod'], $TablicaCzasWysylki, CACHE_INNE);

              } else {

                $TablicaCzasWysylki = $WynikCacheWysylka;     
                
            }                        
          
            $DoWyniku .= '<div class="Multi FiltryCzasWysylki">';
            //
            
            $ZaznaczonePozycje = array();
            if (isset($_GET['wysylka'])) {
                $ZaznaczonePozycje = Filtry::WyczyscFiltr($_GET['wysylka']);
                //
                if ( count($ZaznaczonePozycje) == 1 && $ZaznaczonePozycje[0] == -1 ) {
                     $ZaznaczonePozycje = array();
                }
                //                    
            }                
            
            if (count($ZaznaczonePozycje) > 0) {
                $DoWyniku .= '<span class="FiltrNaglowek" tabindex="0" role="button" aria-expanded="false" aria-controls="wybor_wysylka" arial-label="' . $GLOBALS['tlumacz']['FILTR'] . ' - ' . $GLOBALS['tlumacz']['CZAS_WYSYLKI'] . '"><b class="Wlaczony">' . $GLOBALS['tlumacz']['CZAS_WYSYLKI'] . '</b></span>';
              } else {
                $DoWyniku .= '<span class="FiltrNaglowek" tabindex="0" role="button" aria-expanded="false" aria-controls="wybor_wysylka" arial-label="' . $GLOBALS['tlumacz']['FILTR'] . ' - ' . $GLOBALS['tlumacz']['CZAS_WYSYLKI'] . '"><b>' . $GLOBALS['tlumacz']['CZAS_WYSYLKI'] . '</b></span>';
            }
            //
            $DoWyniku .= '<ul class="Wybor" id="wybor_wysylka" tabindex="-1">';
            //                
            
            // tworzenie wyboru
            foreach ( $TablicaWyniku as $IdCzasuWysylki => $Ilosc ) {

                if ( isset($TablicaCzasWysylki[ $IdCzasuWysylki ]) ) {
                  
                    $Wlacz = '';
                    $WlaczLabel = '';
                    if (in_array($IdCzasuWysylki, $ZaznaczonePozycje)) {
                        $Wlacz = 'checked="checked"';
                        $WlaczLabel = ' class="Wlaczony"';
                        //
                        Filtry::FiltryAktywne( array($IdCzasuWysylki, 'wysylka', $TablicaCzasWysylki[$IdCzasuWysylki]['nazwa']) );
                        //                                
                    }
                    //
                    $DoWyniku .= '<li><input type="checkbox" id="filtr_czaswysylki_' . $IdCzasuWysylki . '" name="wysylka[' . $IdCzasuWysylki . ']" ' . $Wlacz . ' /> <label role="button" tabindex="0" aria-label="' . $GLOBALS['tlumacz']['FILTR'] . ' - ' . str_replace('"','',$GLOBALS['tlumacz']['CZAS_WYSYLKI'] . ': ' . $TablicaCzasWysylki[$IdCzasuWysylki]['nazwa']) . '" id="label_filtr_czaswysylki_' . $IdCzasuWysylki . '" for="filtr_czaswysylki_' . $IdCzasuWysylki . '"' . $WlaczLabel . '><a data-id="filtr_czaswysylki_' . $IdCzasuWysylki . '">' . $TablicaCzasWysylki[$IdCzasuWysylki]['nazwa'] . ((POKAZUJ_FILTRY_ILOSCI == 'tak') ? ' (' . $Ilosc . ')' : '') . '</a></label></li>';
                    //
            
                }
                
            }
            
            $DoWyniku .= '</ul>';
            $DoWyniku .= '</div>';
            unset($Wlacz, $ZaznaczonePozycje);
            //
 
        }

        return $DoWyniku;
    }    

    // czysci GET id z prob wlaman
    static public function WyczyscFiltr( $get, $text = false ) {
        //
        $Wartosci = explode(',', (string)$get);
        //
        $Tablica = array();
        //
        // wartosc bezpieczenstwa - zeby przy braku danych nie pokazywalo bledu
        foreach ( $Wartosci AS $Wartosc ) {
            //
            $TmpPodzial = explode('-', (string)$Wartosc);
            //                 
            if ( isset($TmpPodzial[0]) && (int)$TmpPodzial[0] > 0 ) {
                 //
                 if ( $text == false ) {
                      $Tablica[] = (int)$TmpPodzial[0];
                 } else {
                      $Tablica[] = (string)$Wartosc;
                 }
                 //
            }
            //
            unset($TmpPodzial);
            //
        }
        //
        if ( count($Tablica) == 0 ) {
             //
             $Tablica[] = -1;
             //
        }
        //
        return $Tablica;
        //
    }
        
    
}

?>