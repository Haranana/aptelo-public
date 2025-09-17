<?php

class Producenci {

    public static function TablicaProducenci($Ilosc = false) {
    
        $TablicaProducenci = array();
        
        // pobiera dane z bazy
        
        if (LISTING_ILOSC_PRODUKTOW == 'tak' && $Ilosc == false) {
        
            // cache zapytania
            $WynikCache = $GLOBALS['cache']->odczytaj('ProducenciIlosc', CACHE_PRODUCENCI, true);

        } else {
            
            // cache zapytania
            $WynikCache = $GLOBALS['cache']->odczytaj('Producenci', CACHE_PRODUCENCI, true);
            
        }
        
        if ( !$WynikCache && !is_array($WynikCache) ) {
              
            $sql = $GLOBALS['db']->open_query("SELECT m.manufacturers_id as IdProducenta,
                                                      m.manufacturers_name as Nazwa, 
                                                      m.manufacturers_image as Foto,
                                                      m.manufacturers_full_name,
                                                      m.manufacturers_street,
                                                      m.manufacturers_post_code,
                                                      m.manufacturers_city,
                                                      m.manufacturers_country,
                                                      m.manufacturers_email,
                                                      m.manufacturers_phone,
                                                      m.importer_name,
                                                      m.importer_street,
                                                      m.importer_post_code,
                                                      m.importer_city,
                                                      m.importer_country,
                                                      m.importer_email,
                                                      m.importer_phone,
                                                      m.importer_unchanged,
                                                      mi.manufacturers_url
                                                   FROM manufacturers m 
                                                INNER JOIN manufacturers_info mi ON m.manufacturers_id = mi.manufacturers_id AND mi.languages_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                                                INNER JOIN products p ON p.manufacturers_id = m.manufacturers_id AND p.products_status = '1' AND p.listing_status = '0' " . $GLOBALS['warunekProduktu'] . "
                                                INNER JOIN products_to_categories p2c ON p2c.products_id = p.products_id 
                                                INNER JOIN categories c ON c.categories_id = p2c.categories_id AND c.categories_status = '1' 
                                                   GROUP BY m.manufacturers_id 
                                                   ORDER BY m.manufacturers_name");
        }
        
        if ( !$WynikCache && !is_array($WynikCache) ) {

            while ($info = $sql->fetch_assoc()) {
                //
                $TablicaProducenci[$info['IdProducenta']] = $info;
                //
                // rozporzadzenie gpsr
                if ( (int)$info['IdProducenta'] > 0 ) {
                  
                    $TablicaProducenci[$info['IdProducenta']]['Url'] = $info['manufacturers_url'];
                
                    $TablicaProducenci[$info['IdProducenta']]['Producent'] = array('ProducentId' => $info['IdProducenta'],
                                                                                   'ProducentNazwa' => ((!empty($info['manufacturers_full_name'])) ? $info['manufacturers_full_name'] : $info['Nazwa']),
                                                                                   'ProducentUlica' => $info['manufacturers_street'],
                                                                                   'ProducentKodPocztowy' => $info['manufacturers_post_code'],
                                                                                   'ProducentMiasto' => $info['manufacturers_city'],
                                                                                   'ProducentKraj' => $info['manufacturers_country'],
                                                                                   'ProducentEmail' => $info['manufacturers_email'],
                                                                                   'ProducentTelefon' => $info['manufacturers_phone']);
                                                                 
                    if ( (int)$info['importer_unchanged'] == 0 ) {
                         //
                         $TablicaProducenci[$info['IdProducenta']]['ProducentImporter'] = 0;
                         //
                         $TablicaProducenci[$info['IdProducenta']]['Importer'] = array('ImporterNazwa' => $info['Nazwa'],
                                                                                       'ImporterUlica' => $info['manufacturers_street'],
                                                                                       'ImporterKodPocztowy' => $info['manufacturers_post_code'],
                                                                                       'ImporterMiasto' => $info['manufacturers_city'],
                                                                                       'ImporterKraj' => $info['manufacturers_country'],
                                                                                       'ImporterEmail' => $info['manufacturers_email'],
                                                                                       'ImporterTelefon' => $info['manufacturers_phone']);
                         //
                    } else {
                         //
                         $TablicaProducenci[$info['IdProducenta']]['ProducentImporter'] = 1;
                         //
                         $TablicaProducenci[$info['IdProducenta']]['Importer'] = array('ImporterNazwa' => $info['importer_name'],
                                                                                       'ImporterUlica' => $info['importer_street'],
                                                                                       'ImporterKodPocztowy' => $info['importer_post_code'],
                                                                                       'ImporterMiasto' => $info['importer_city'],
                                                                                       'ImporterKraj' => $info['importer_country'],
                                                                                       'ImporterEmail' => $info['importer_email'],
                                                                                       'ImporterTelefon' => $info['importer_phone']);
                         //
                    }
                    
                }                
                //
                if ( LISTING_ILOSC_PRODUKTOW == 'tak' && $Ilosc == false )  {
                     //
                     $sqlIlosc = $GLOBALS['db']->open_query("SELECT count( DISTINCT px.products_id ) as IloscProduktow
                                                               FROM products px 
                                                          LEFT JOIN products_to_categories p2x ON px.products_id = p2x.products_id 
                                                          LEFT JOIN categories cx ON p2x.categories_id = cx.categories_id 
                                                              WHERE px.manufacturers_id = " . $info['IdProducenta'] . "
                                                                AND cx.categories_status = '1' 
                                                                AND px.products_status = '1' AND px.listing_status = '0' " . str_replace('p.', 'px.', (string)$GLOBALS['warunekProduktu']));
                     //
                     $infs = $sqlIlosc->fetch_assoc();
                     //
                     $TablicaProducenci[$info['IdProducenta']]['IloscProduktow'] = $infs['IloscProduktow'];
                     //
                     $GLOBALS['db']->close_query($sqlIlosc); 
                     //
                     unset($infs);
                     //
                }
                //
            }   
            
            if (LISTING_ILOSC_PRODUKTOW == 'tak' && $Ilosc == false) {
                $GLOBALS['cache']->zapisz('ProducenciIlosc', $TablicaProducenci, CACHE_PRODUCENCI, true);
              } else {
                $GLOBALS['cache']->zapisz('Producenci', $TablicaProducenci, CACHE_PRODUCENCI, true);
            }
            
            $GLOBALS['db']->close_query($sql); 

          } else {
          
            $TablicaProducenci = $WynikCache;
          
        }
        
        unset($WynikCache);
    
        return $TablicaProducenci;
    
    }
    
    // zwraca nazwa lub logo producenta
    public static function NazwaProducenta($id) {

        $zapytanie_tmp = "select distinct * from manufacturers where manufacturers_id = '" . $id . "'";
        $sqls = $GLOBALS['db']->open_query($zapytanie_tmp);
        //
        if ( (int)$GLOBALS['db']->ile_rekordow($sqls) > 0 ) { 
              //
              $infs = $sqls->fetch_assoc();
              $Tablica = array('id' => $infs['manufacturers_id'], 'nazwa' => $infs['manufacturers_name']);
              //              
              unset($infs);    
              //
        } else {
              //
              $Tablica = array('id' => 0, 'nazwa' => '');
              //
        }
        //
        $GLOBALS['db']->close_query($sqls); 
        //  
        return $Tablica;
        
    }    

  
    // zwraca tablice z producentami - tylko id i nazwe - do selectow
    public static function TablicaProducenciSelect($brak = '') {
    
        $TablicaProducentow = Producenci::TablicaProducenci();
        //
        $Tablica = array();

        $Tablica[] = array('id' => 0, 'text' => $GLOBALS['tlumacz']['LISTING_WYBIERZ_OPCJE']);

        foreach ( $TablicaProducentow as $Producent ) {
            $Tablica[] = array('id' => $Producent['IdProducenta'], 'text' => $Producent['Nazwa']);
        }

        unset($TablicaProducentow);
        //  
        return $Tablica;
        
    }   

    // zwraca id producenta to jakiego nalezy produkt
    static function ProduktProducent($id = '0') {
        //
        $zapytanie = "SELECT manufacturers_id FROM products WHERE products_id = " . (int)$id;
        $sql = $GLOBALS['db']->open_query($zapytanie);
        //
        $id_producenta = 0;
        //
        if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
              //
              $info = $sql->fetch_assoc();
              $id_producenta = $info['manufacturers_id'];
              //
        } else {
              //
              $id_producenta = 0;
              //
        }
        //
        $GLOBALS['db']->close_query($sql); 
        //
        unset($zapytanie);
        
        return $id_producenta;
        //
    } 
    
    // zwraca tablice z danymi do gpsr
    public static function TablicaProducentGpsr($id) {
    
        $TablicaProducentow = Producenci::TablicaProducenci();
        //
        $Tablica = array();

        foreach ( $TablicaProducentow as $Producent ) {
          
            if ( $Producent['IdProducenta'] == $id ) {
                 
                 $Tablica = array('Producent' => $Producent['Producent'], 
                                  'Importer' => $Producent['Importer'],
                                  'Url' => $Producent['Url'],
                                  'TakieSame' => $Producent['ProducentImporter']);
                 
            }
            
        }

        unset($TablicaProducentow);
        //  
        return $Tablica;
        
    }    
  
} 

?>