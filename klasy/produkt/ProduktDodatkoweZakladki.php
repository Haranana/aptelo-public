<?php

if ( isset($pobierzFunkcje) ) {

    $zapytanie = "SELECT products_info_id, products_info_name, products_info_description FROM products_info WHERE products_id = '" . $this->id_produktu . "' AND language_id = '" . $this->jezykDomyslnyId . "' ORDER BY products_info_id";    
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
    
        while ($info = $sql->fetch_assoc()) {
            //
            if ( !empty($info['products_info_name']) && !empty($info['products_info_description']) ) {
                //
                $this->dodatkoweZakladki[] = array( 'nazwa' => $info['products_info_name'],
                                                    'tresc' => '<div class="FormatEdytor">' . $info['products_info_description'] . '</div>',
                                                    'strona' => 'produkt',
                                                    'id_zakladki' => $info['products_info_id']);
                //
            }            
        }
        
        unset($info);
        
    }
    
    $GLOBALS['db']->close_query($sql); 
    unset($zapytanie);
    
    // dodatkowe zakladki producenta i kategorii
    
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('Producent_Id_' . $this->info['id_producenta'] . '_info_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_PRODUCENCI);
    
    $zakladkaProducenta = array( 'nazwa' => '',
                                 'tresc' => '' );
    
    if ( !$WynikCache ) {
        //
        $zapytanie = "SELECT manufacturers_info_text, manufacturers_info_name FROM manufacturers_info WHERE manufacturers_id = '" . $this->info['id_producenta'] . "' and languages_id = '" . $this->jezykDomyslnyId . "'";  
        $sql = $GLOBALS['db']->open_query($zapytanie);
        //
        if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
            //
            $info = $sql->fetch_assoc();
            //
            if ( trim((string)$info['manufacturers_info_name']) != '' && trim((string)$info['manufacturers_info_text']) != '' ) {
                //
                $zakladkaProducenta = array( 'nazwa' => $info['manufacturers_info_name'],
                                             'tresc' => '<div class="FormatEdytor">' . $info['manufacturers_info_text'] . '</div>',
                                             'strona' => 'producent',
                                             'id_zakladki' => '');
                //
            }
            //
            unset($info);
            //
        }
        //
        $GLOBALS['cache']->zapisz('Producent_Id_' . $this->info['id_producenta'] . '_info_' . $_SESSION['domyslnyJezyk']['kod'], $zakladkaProducenta, CACHE_PRODUCENCI);
        //
        $GLOBALS['db']->close_query($sql);
        //
        unset($zapisz, $zapytanie);
        //
        
    } else {
    
        $zakladkaProducenta = $WynikCache;
        
    }     
    
    if ( $zakladkaProducenta['nazwa'] != '' && $zakladkaProducenta['tresc'] != '' ) {
    
         $this->dodatkoweZakladki[] = array( 'nazwa' => $zakladkaProducenta['nazwa'],
                                             'tresc' => '<div class="FormatEdytor">' . $zakladkaProducenta['tresc'] . '</div>',
                                             'strona' => $zakladkaProducenta['strona'],
                                             'id_zakladki' => '');
    }
    
    unset($zakladkaProducenta);
    
    // do jakich kategorii nalezy produkt
    
    $TablicaKategorii = Kategorie::ProduktKategorie( $this->id_produktu );
    
    foreach ( $TablicaKategorii as $KategoriaProduktu ) {
    
        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('Kategoria_Id_' . $KategoriaProduktu . '_info_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_KATEGORIE);
        
        $zakladkaKategorii = array( 'nazwa' => '',
                                    'tresc' => '',
                                    'strona' => '',
                                    'id_zakladki' => '');
        
        if ( !$WynikCache ) {
            //
            $zapytanie = "SELECT categories_info_text, categories_info_name FROM categories_description WHERE categories_id = '" . $KategoriaProduktu . "' and language_id = '" . $this->jezykDomyslnyId . "'";  
            $sql = $GLOBALS['db']->open_query($zapytanie);
            //
            if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
                //
                $info = $sql->fetch_assoc();
                //
                if ( trim((string)$info['categories_info_name']) != '' && trim((string)$info['categories_info_text']) != '' ) {
                    //
                    $zakladkaKategorii = array( 'nazwa' => $info['categories_info_name'],
                                                'tresc' => '<div class="FormatEdytor">' . $info['categories_info_text'] . '</div>',
                                                'strona' => 'kategoria',
                                                'id_zakladki' => '');
                    //
                }
                //
                unset($info);
                //
            }
            //
            $GLOBALS['cache']->zapisz('Kategoria_Id_' . $KategoriaProduktu . '_info_' . $_SESSION['domyslnyJezyk']['kod'], $zakladkaKategorii, CACHE_KATEGORIE);
            //
            $GLOBALS['db']->close_query($sql);
            //
            unset($zapisz, $zapytanie);
            //
            
        } else {
        
            $zakladkaKategorii = $WynikCache;
            
        }     
        
        if ( $zakladkaKategorii['nazwa'] != '' && $zakladkaKategorii['tresc'] != '' ) {
        
             $this->dodatkoweZakladki[] = array( 'nazwa' => $zakladkaKategorii['nazwa'],
                                                 'tresc' => '<div class="FormatEdytor">' . $zakladkaKategorii['tresc'] . '</div>',
                                                 'strona' => $zakladkaKategorii['strona'],
                                                 'id_zakladki' => '');
        
        }
        
        unset($zakladkaKategorii);    
        
    }
    
    unset($TablicaKategorii);
}
       
?>